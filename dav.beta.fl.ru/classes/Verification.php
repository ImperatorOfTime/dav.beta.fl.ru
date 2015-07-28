<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/payment_keys.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/account.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/pskb.php';
require_once 'HTTP/Request2.php';

/**
 * ����� ��� ����������� �������������
 * 
 */
class Verification {
    /**
     * ����������� ������� ��������� webmoney ����������� ��� �����������
     * 
     */
    const WM_ATTESTAT_LEVEL = 120;
    /**
     * ����������� url ��� OAuth �� FF.RU
     * 
     */
    const FF_REDIRECT_URI = 'https://www.free-lance.ru/income/ff.php';
    /**
     * ��� �������� ������ ������ �� ����������� ����� FF.RU
     * 
     */
    const FF_OP_CODE = 117;
    
    /**
     * ����������� ����� ������.������. URI �����������
     */
    const YD_URI_AUTH = 'https://sp-money.yandex.ru';
    
    /**
     * ����������� ����� ������.������. URI API
     */
    const YD_URI_API = 'https://money.yandex.ru/api';
    
    /**
     * ����������� ����� ������.������. ������������� ����������
     */
    const YD_CLIENT_ID = '9297F3ADF2F2079458C8E61313433DC30DFAFB0C159BCE9326C8316E2562726D';
    
    /**
     * ����������� ����� ������.������. URI ��� �������� ���������� ����������� ����������
     */
    const YD_REDIRECT_URI = 'https://www.free-lance.ru/income/wm_verify.php';
    
    /**
     * ����������� ����� ������.������. ��������� ����� ��� �������� ����������� ����������
     */
    const YD_CLIENT_SECRET = '7C2E413B2DD451DE61C5D9667A5BD0225A74A719488F39984BB884F88DD8A378075D65A55C029BBD6849AE603688D833172ADC36C44B133808BDDD791D9A6A72'; 

    /**
     * ����������� ����� OKPAY. URI API
     */
    const OKPAY_URI_API = 'https://api.okpay.com/OkPayAPI?wsdl';

    /**
     * ����������� ����� OKPAY. ID ��������
     */
    const OKPAY_WALLETID = 'OK460571733';

    /**
     * ����������� ����� OKPAY. ��������� ����� ��� �������� ����������� ����������
     */
    const OKPAY_CLIENT_SECRET = 'o8M5TtFk93Yme7RCa64Ayb2SK';
    
    /**
     * ��������� �� ������ ���� ������������ �� ���������
     */
    const ERROR_NO_AUTH = '����� ������ ����������� ��� ����� <a href="/login/">��������������</a> ��� <a href="/registration/">������������������</a>.';
    
    /**
     * ����� ������ ����� ������ �� ���������� �����
     */
    const YKASSA_AC_OP_CODE = 191;
    

    
    /**
     * ������ ��� WebMoney �����������
     */
    const WMLOGIN_URL = 'https://login.wmtransfer.com/GateKeeper.aspx?RID=%s';
    


    
    const ERROR_DEFAULT = '��������� ������ ��� �����������. ���������� ��� ���.';
    
    
    
    /**
     * �������� ���� ������ � ������ �������
     * 
     * @var string
     */
    public $error = '';
    /**
     * ���� ������ ���������� ��������� ������� ����� �������� $this->verify(int)
     * 
     * @var array
     */
    public $data = array (
        'fio'         => '',  // ������� ��� ��������
        'birthday'    => '',  // ���� �������� (������ YYYY-MM-DD)
        'idcard_name' => '',  // �������� ���������
        'idcard'      => '',  // ����� � ����� ���������
        'idcard_from' => '',  // ���� ������ ��������� (������ YYYY-MM-DD)
        'idcard_to'   => '',  // ���� ��������� �������� ��������� (������ YYYY-MM-DD)
        'idcard_by'   => '',  // �����, �������� �������� 
        'mob_phone'   => ''   // ����� ���������� ��������
    );
    
    
    /**
     * ����������� ����� FF.RU.
     * ��� 1. ������ ����������� � ������
     * 
     * @param  integer  $uid   uid ��������������� ������������
     * @return boolean         �����
     */
    public function ffBegin($uid) {
        global $DB;
        $user    = new users;
        $account = new account;
        $billId  = NULL;
        $user->GetUserByUID($uid);
        if ( empty($user->uid) ) {
            $this->error = '�� �� ������������';
            return false;
        }
        if ( $user->is_verify == 't' ) {
            $this->error = '�� ��� ��������������';
            return false;
        }
        $prev = $DB->val("SELECT result FROM verify_ff WHERE user_id = ? ORDER BY req_time DESC LIMIT 1", $uid);
        if ( empty($prev) || $prev == 't' ) {
//            if ( $user->is_pro != 't' ) {
//                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/op_codes.php");
//                $op_codes = new op_codes();
//                $sum = round($op_codes->GetField(self::FF_OP_CODE, $err, "sum"), 2);
//                $ac_sum = round( (float)$_SESSION["ac_sum"], 2);
//                if ( $sum > $ac_sum ) {
//                    $this->error = "������������ ������� �� �����.";
//                    return false;
//                }
//            }
            $DB->insert('verify_ff', array('user_id'=>$uid, 'is_pro'=>$user->is_pro, 'bill_id'=>$billId));
        }
        return true;
    }
    
    
    /**
     * ����������� ����� FF.RU.
     * ��� 2. ��������� ���� ����������� � ������� � ff.ru
     * 
     * @global type $DB
     * @param type $uid
     * @param type $code
     * @return boolean
     */
    public function ffCommit($uid, $code) {
        global $DB;
        $requestConfig = array (
            'adapter'           => 'HTTP_Request2_Adapter_Curl',
            'connect_timeout'   => 20,
            'protocol_version'  => '1.1',
            'ssl_verify_peer'   => false,
            'ssl_verify_host'   => false,
            'ssl_cafile'        => null,
            'ssl_capath'        => null,
            'ssl_passphrase'    => null
        );
        $user = new users;
        $user->GetUserByUID($uid);
        if ( empty($user->uid) ) {
            $this->error = '�� �� ������������';
            return false;
        }
        $prev = $DB->row("SELECT * FROM verify_ff WHERE user_id = ? ORDER BY req_time DESC LIMIT 1", $uid);
        if ( $prev['result'] != 'f' ) {
            $this->error = '��� ���������� ������������ ����� �� ������� ������ � ������� �� �������� �����������.';
            return false;
        }
        // ��� ������������ �� ����/�����
        if(is_release()) { 
            // ������ ��� ����������� �� �����
            $request = new HTTP_Request2('https://ff.ru/oauth/token', HTTP_Request2::METHOD_POST);
            $request->setConfig($requestConfig);
            $request->setHeader('Content-Type', 'application/x-www-form-urlencoded');
            $request->addPostParameter('client_id', FF_CLIENT_ID);
            $request->addPostParameter('client_secret', md5(FF_CLIENT_ID . FF_CLIENT_SECRET));
            $request->addPostParameter('grant_type', 'authorization_code');
            $request->addPostParameter('code', $code);
            $request->addPostParameter('redirect_uri', self::FF_REDIRECT_URI);
            $resp = $request->send();
            //var_dump($resp); // del
            $body = json_decode(iconv('UTF-8', 'CP1251', $resp->getBody()));
            if ( $resp->getStatus() == 200 ) {
                // ������ ����� �� ���������� ������
                $request = new HTTP_Request2('https://ff.ru/oauth/userinfo', HTTP_Request2::METHOD_GET);
                $request->setConfig($requestConfig);
                $request->setHeader('Authorization', 'Bearer ' . $body->access_token);
                $url = $request->getUrl();
                $url->setQueryVariable('scope', 'passport question account video');
                $resp = $request->send();
                $body = json_decode($resp->getBody());
                $DB->query("UPDATE verify_ff SET body = ? WHERE id = ?", $resp->getBody(), $prev['id']);
                if ( $resp->getStatus() == 200 ) {
                    if ( empty($body->passport_sn) ) {
                        $this->error = '���������� ����������� �������� � ������ �������� ����� FF.RU.';
                        return false;
                    }
                    $fio = $body->last_name . ' ' . $body->first_name . ' ' . $body->patronimic;
                    $this->data = array(
                        'fio'         => iconv('UTF-8', 'CP1251', htmlentities($fio, ENT_QUOTES, "UTF-8")),
                        'birthday'    => dateFormat('Y-m-d', (string) $body->birth_date),
                        'idcard_name' => '�������',
                        'idcard'      => $body->passport_sn,
                        'idcard_from' => dateFormat('Y-m-d', (string) $body->passport_date),
                        'idcard_to'   => NULL,
                        'idcard_by'   => iconv('UTF-8', 'CP1251', htmlentities($body->passport_issuer, ENT_QUOTES, "UTF-8")),
                        'mob_phone'   => '+7' . $body->cellular
                    );
                    //var_dump($this->data);
                } else {
                    if ( empty($body->error) ) {
                        $this->error = '������ ��� ��������� ������ � FF.RU.';
                    } else {
                        $this->error = '������ ��� ��������� ������ � FF.RU (' . $body->error . ' / ' . $body->error_description . '). ';
                    }
                    $this->error .= $resp->getStatus() . '.';
                    return false;
                }
            } else {
                if ( empty($body->error) ) {
                    $this->error = '������ ��� ����������� � ������� FF.RU.';
                } else {
                    $this->error = '������ ��� ����������� � ������� FF.RU (' . $body->error . ' / ' . $body->error_description . '). ';
                }
                $this->error .= $resp->getStatus() . '.';
                return false;
            }
        } else {
            $this->data = array(
                'fio'         => '������� ��� ��������',
                'birthday'    => dateFormat('Y-m-d', (string) '1950-01-01'),
                'idcard_name' => '�������',
                'idcard'      => '1900 100001',
                'idcard_from' => dateFormat('Y-m-d', (string) '2000-01-01'),
                'idcard_to'   => NULL,
                'idcard_by'   => '��� �. ������',
                'mob_phone'   => '+79' . rand(100000000, 900000000)
            );
        }
        $this->is_pro = true;
        if ( $user->is_pro != 't' && empty($prev['bill_id']) ) {
                //��������� ���� �������� �������
                $account = new account;
//                $billId  = NULL;
//                $transactionId = $account->start_transaction($uid);
//                $description   = '����������� ����� ������ FF.RU';
//                $buyResult     = $account->Buy($billId, $transactionId, self::FF_OP_CODE, $uid, $description, $description, 1, 0);
//                if ( $buyResult ) {
//                    $this->error .= $buyResult;
//                    return false;
//                }
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
                $bill      = new billing($uid);
                $bill->setOptions(array('prev' => $prev, 'data' => $this->data));
                $create_id = $bill->create(self::FF_OP_CODE);
                $this->is_pro = false;
                if(!$create_id) {
                    $this->error .= '������ �������� ������';
                    return false;
                } else {
                    return true;
                    //header("Location: /bill/orders/");
                    exit;
                }
        }
        $DB->query("UPDATE verify_ff SET is_pro = ?, bill_id = ?  WHERE id = ?", $user->is_pro, $billId, $prev['id']);
        if ( $this->verify($uid) ) {
            $DB->query("UPDATE verify_ff SET result = TRUE WHERE id = ?", $prev['id']);
            //$account->commit_transaction($transactionId, $uid);
            return true;
        }
            
    }
    
    
    /**
     * ���������� ������ ����������� ������������ ����� FF.ru
     * 
     * @param  integer  $uid  uid ������������
     * @return boolean|int    FALSE - �� �������� ����������������, 0 - ��������, �� ��� �� ��������, 1 - �������������
     */
    public function ffStatus($uid) {
        global $DB;
        $row = $DB->row("SELECT * FROM verify_ff WHERE user_id = ? ORDER BY req_time DESC LIMIT 1", $uid);
        if ( empty($row) ) {
            return FALSE;
        } else if ( $row['result'] == 't' ) {
            return 1;
        }
        return 0;
    }
    
    
    
    
    /**
     * �������� ����������� � ����������� ��� ������ WebMoney
     * 
     * @global type $DB
     * @param type $uid
     * @return boolean
     */
    public function webmoney($uid)
    {
        global $DB;

        require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/WMXI/WMXILogin.php');
        
        $siteHolder = defined('WM_VERIFY_AUTHCHECK_WMID')? 
                WM_VERIFY_AUTHCHECK_WMID : 
                WM_VERIFY_WMID;
                
        $wmxi = new WMXILogin(
                WM_VERIFY_URL_UD, 
                $siteHolder, 
                realpath(ABS_PATH . '/classes/WMXI/WMXI.crt'));
  
        if ($wmid = $wmxi->AuthorizeWMID()) {
            if ($res = $this->webmoneyCheckWMID($wmid, $uid)) {
                if ($this->verify($uid)) {
                    
                    $ret = $DB->insert('verify_webmoney', array(
                        'user_id' => $uid, 
                        'wmid' => $wmid, 
                        'log' => $res->asXML(),
                        'result' => true
                    ));
                    
                    return $ret;
                }
            }
        }
        
        if (empty($this->error)) {
            $this->error = '��������� ������ �� ����� �����������. ���������� ��� ���.';
        }
        
        return false;
    }

    
    
    /**
     * �������� WMID:
     * - �������� ����� WMID
     * - ������������� �� ��� ���������� WMID
     * - �������� ��������� � WMID
     * 
     * @global type $DB
     * @param type $wmid
     * @param type $uid
     * @return boolean
     */
    public function webmoneyCheckWMID($wmid, $uid)
    {
        global $DB;
        
        //�������� ����� WMID
        if (!preg_match('/^[0-9]{12}$/', $wmid)) {
            $this->error = '����������� ������ WMID.';
            
            return false;
        }

        
        //������������� �� ��� ���������� WMID
        $ret = $DB->val("
            SELECT 1 
            FROM verify_webmoney 
            WHERE wmid = ? AND user_id <> ?i AND result", 
            $wmid, $uid);
        
        if ($ret) {
            $this->error = '������ WMID ��� ������������ ��� ����������� ������ ������������� �����.';
            
            return false;
        }
        
        require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/WMXI/WMXI.php');
        
        //�������� ��������� � WMID
        $wmxi = new WMXI;
        $key  = array( 'file' => WM_VERIFY_KEYFILE, 'pass' => WM_VERIFY_KEYPASS );
        $wmxi->Classic(WM_VERIFY_WMID, $key);
        $res = $wmxi->X11($wmid, 0, 1, 0);
        $res = $res->toObject();
        $retval = (int)$res['retval'];
        
        if ($retval > 0) {
            $this->error = '��������� ������ ��� �������� ���������. ���������� ��� ���.';
            
            return false;
        }
        
        $tid = (int) $res->certinfo->attestat->row['tid'];
        
        if ($tid < self::WM_ATTESTAT_LEVEL) {
            $this->error = '��������� �������� �� ���� ����������. �������� <a class="b-layout__link underline" href="https://wiki.webmoney.ru/projects/webmoney/wiki/���������" target="_blank">��������� ��������</a> ��� �������� ������ ������ �����������.';
            
            return false;
        }
        
        return $res;        
    }


    
    /**
     * ������� ������ �� ����������� ����� WebMoney
     * 
     * @return type
     */
    public function getWMLoginUrl()
    {
        return sprintf(self::WMLOGIN_URL, WM_VERIFY_URL_UD);
    }


    
    /**
     * ������� ������
     * 
     * @return type
     */
    public function getError()
    {
        return $this->error;
    }




    
    
    /**
     * ������������� ����������� ����� ������.������
     * 
     * @param  int $uid UID ������������
     * @return bool true - �����, false - ������
     */
    public function ydBegin( $uid ) {
        $user    = new users;
        $user->GetUserByUID($uid);
        
        if ( empty($user->uid) ) {
            $this->error = self::ERROR_NO_AUTH;
            return false;
        }
        
        if ( $user->is_verify == 't' ) {
            $this->error = '�� ��� ��������������';
            return false;
        }
        
        $prev = $GLOBALS['DB']->val( 'SELECT result FROM verify_yd WHERE user_id = ? ORDER BY req_time DESC LIMIT 1', $uid );
        
        if ( empty($prev) || $prev == 't' ) {
            $sIsEmp = is_emp() ? 't' : 'f';
            
            $GLOBALS['DB']->insert( 'verify_yd', array('user_id' => $uid, 'is_emp' => $sIsEmp) );
        }
        
        return true;
    }


    /**
     * ��������� ������������ ������ ������������, ����������� ��� ����������� ����� ������.������
     * ������������ ����� � self::pskb()
     * 
     * @param  int $uid UID ������������
     * @return bool true - �����, false - ������
     */
    public function ydCheckUserReqvs( $uid = 0 ) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php';
        
        $this->error = '';
        $nError      = 0; // ��� ������ �� $aError � ���� ������� ����. 0 - ��� ������
        $aFields     = array(
            array( 'fio', 'birthday' ),                                  // ��� � ���� ��������
            array( 'idcard_name', 'idcard', 'idcard_from', 'idcard_by' ) // ���������� ������
        );
        
        return empty( $nError );
    }
    
    /**
     * ���������� URI ����������� ��� ����������� ����� ������.������
     * 
     * @param  string $scope
     * @return string
     */
    public function ydAuthorizeUri( $scope = null ) {
        if ( empty($scope) ) {
            $scope = 'account-info operation-history';
        }
        
        $scope = trim( strtolower($scope) );
        
        $res = self::YD_URI_AUTH . '/oauth/authorize?client_id='. self::YD_CLIENT_ID .'&response_type=code&scope=' 
            . urlencode($scope) . "&redirect_uri=" . urlencode(self::YD_REDIRECT_URI);
        
        return $res;
    }

    /**
     * ����������� ����� ��������� �����
     * 
     * @param  int $uid UID ������������
     * @param  string $card ����� �����
     * @return bool true - �����, false - ������
     */
    public function card( $uid, $card ) {
        global $DB;
        
        $DB->query("INSERT INTO verify_card(user_id, card, result, req_time) VALUES(?i, ?, TRUE, NOW())", get_uid(false), $card);
        $DB->query("UPDATE users SET is_verify='t' WHERE uid=?i", get_uid(false));
        $antiuid = $DB->val("SELECT anti_uid FROM users WHERE uid=?i", get_uid(false));
        if($antiuid) { $DB->query("UPDATE users SET is_verify='t' WHERE uid=?i", $antiuid); }
        $_SESSION['verifyStatus'] = array( 'status' => 1 );
        $_SESSION['is_verify']    = 't';
    }
    
    /**
     * ����������� ����� ��������� ����� (����� ������)
     * 
     * @param  int $uid UID ������������
     * @return bool true - �����, false - ������
     */
    public function cardYK($uid) {
        global $DB;

        $DB->query("INSERT INTO verify_card(user_id, card, result, req_time) VALUES(?i, ?, TRUE, NOW())", $uid, "yandex.kassa");
        $DB->query("UPDATE users SET is_verify='t' WHERE uid=?i", $uid);
        $antiuid = $DB->val("SELECT anti_uid FROM users WHERE uid=?i", $uid);
        if($antiuid) { 
            $DB->query("UPDATE users SET is_verify='t' WHERE uid=?i", $antiuid); 
        }
        return true;
    }

    /**
     * ����������� ����� ������.������
     * 
     * @param  int $uid UID ������������
     * @param  string $is_emp �������� �� ������������ �������������: 't' ��� 'f'
     * @param  string $code ��������� �����, ���������� � ����� �� ������ ����������� � ������.������
     * @return bool true - �����, false - ������
     */
    public function ydVerification( $uid = null, $is_emp = 'f', $code = '', $fname='', $lname='' ) {
        $prev = $GLOBALS['DB']->row("SELECT * FROM verify_yd WHERE user_id = ? ORDER BY req_time DESC LIMIT 1", $uid);
        
        
        $nError        = 0;  // ��� ������ �� $aError � ���� ������� ����. 0 - ��� ������
        $requestConfig = array (
            'adapter'           => 'HTTP_Request2_Adapter_Curl',
            'connect_timeout'   => 20,
            'protocol_version'  => '1.1',
            'ssl_verify_peer'   => false,
            'ssl_verify_host'   => false,
            'ssl_cafile'        => null,
            'ssl_capath'        => null,
            'ssl_passphrase'    => null
        );
        
        // ������ ��� ����������� �� �����
        $request = new HTTP_Request2( self::YD_URI_AUTH . '/oauth/token', HTTP_Request2::METHOD_POST );
        $request->setConfig( $requestConfig );
        $request->setHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' );
        $request->setHeader( 'Expect', '' );
        $request->addPostParameter( 'code', $code );
        $request->addPostParameter( 'client_id', self::YD_CLIENT_ID );
        $request->addPostParameter( 'grant_type', 'authorization_code' );
        $request->addPostParameter( 'redirect_uri', self::YD_REDIRECT_URI );
        $request->addPostParameter( 'client_secret', self::YD_CLIENT_SECRET );
        
        $resp = $request->send();
        $body = json_decode( iconv('UTF-8', 'CP1251', $resp->getBody()) );
        
        $GLOBALS['DB']->query( 'UPDATE verify_yd SET log = ? WHERE id = ?', $resp->getBody(), $prev['id'] );
        
        if ( $resp->getStatus() == 200 ) {
            // �������� ���������� � ��������� ����� ������������
            $request = new HTTP_Request2( self::YD_URI_API . '/account-info', HTTP_Request2::METHOD_POST );
            $request->setConfig( $requestConfig );
            $request->setHeader( 'Authorization', 'Bearer ' . $body->access_token );
            $request->setHeader( 'Expect', '' );
            
            $resp = $request->send();
            $body = json_decode($resp->getBody());
            
            $GLOBALS['DB']->query( 'UPDATE verify_yd SET log = ? WHERE id = ?', $resp->getBody(), $prev['id'] );
            
            if ( $resp->getStatus() == 200 ) {
                $bTestServer = ( (defined('SERVER') && SERVER != 'release') || (defined('IS_LOCAL') && IS_LOCAL === TRUE) );
                
                if ( $bTestServer || $body->identified ) {
                    $aVerifyYd = $GLOBALS['DB']->rows( 'SELECT is_emp FROM verify_yd WHERE account = ? AND result = true', $body->account );
                    
                    if ( count($aVerifyYd) > 1 ) {
                        $nError = 3;
                    }
                    elseif ( count($aVerifyYd) && $aVerifyYd[0]['is_emp'] == $is_emp ) {
                        $nError = 4;
                    }
                    
                    if ( !$nError ) {
                        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php';

                        $aFields    = array( 'fio', 'birthday', 'idcard_name', 'idcard', 'idcard_from', 'idcard_by', 'mob_phone' );
                        $this->data = array();

                        if ( empty($this->aUserReqvs) ) {
                            $this->aUserReqvs = sbr_meta::getUserReqvs($uid);
                        }

                        if ( is_array($this->aUserReqvs) && $this->aUserReqvs ) {
                            foreach ( $aFields as $sField ) {
                                $this->data[$sField] = $this->aUserReqvs[1][$sField];
                            }

                            $this->data['el_yd'] = $body->account;

                            if ( $this->verify($uid) ) {
                                $GLOBALS['DB']->update(
                                    'verify_yd', 
                                    array( 'account' => $body->account, 'result' => true ),
                                    'id = ?', $prev['id']
                                );
                            }
                            else {
                                return false; // ��������� �� ������� �� $this->verify($uid)
                            }
                        }
                        else {
                            $nError = 1;
                        }
                    }
                }
                else {
                    $nError = 2;
                }
            }
            else {
                $nError = 1;
            }
        }
        else {
            $nError = 1;
        }
        
        if ( $nError ) {
            $aError = array(
                1 => '��������� ������ �� ����� �����������.',
                2 => '��� ����������� � ��� ������ ���� ��������������� �������.',
                3 => '������ ������� ��� ��� ����������� ��� ����������� ���-�� �� �������������.', // ��� ��������: � ��������� � ������������
                4 => '������ ������� ��� ��� ����������� ��� ����������� ���-�� �� �������������.' // ���� ������� � ��� �� �����
            );
            
            $this->error = $aError[$nError];
        }
        
        return empty( $nError );
    }

    /**
     * ����������� ����� OKPAY. 
     * 
     * @param  integer $uid  uid ������������
     * @return boolean       ��������� ��������
     */
    public function okpay($uid) {
        global $DB;
        if ( empty($uid) ) {
            $this->error = '�� �� ������������.';
            return false;
        }

        $logId = $DB->insert('verify_okpay', array('user_id'=>$uid), 'id');

        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php';
        $this->aUserReqvs = sbr_meta::getUserReqvs($uid);

        if ( empty($this->aUserReqvs[1]['mob_phone']) || $this->aUserReqvs['is_activate_mob'] == 'f' ) {
            $this->error = '��� ����������� � ��� ������ ���� ����������� ����� �������� � <a href="/users/'. $_SESSION['login'] .'/setup/main/">�������� ����������</a> ��������.';
            return false;
        }

        $is_verify = false;
        $phone = str_replace("+", "", $this->aUserReqvs[1]['mob_phone']);

        $sql = "SELECT COUNT(user_id) FROM sbr_reqv WHERE _1_mob_phone=?";
        $foundPhones = $DB->val($sql, "+".$phone);
        if($foundPhones>1) {
            $this->error = '������ ����� �������� ��� ��� ����������� ��� ����������� ���-�� �� �������������.';
            return false;
        }


        $datePart = gmdate("Ymd");
        $timePart = gmdate("H");
        $authString = self::OKPAY_CLIENT_SECRET.":".$datePart.":".$timePart;
        $secToken = hash('SHA256', $authString);
        $secToken = strtoupper($secToken);

        try {
            $client = new SoapClient(self::OKPAY_URI_API);
        } catch (Exception $e) {
            header('Location: /promo/verification/?service=okpay&error=1');
            exit;
        }
        $obj = new stdClass();
        $obj->WalletID = self::OKPAY_WALLETID;
        $obj->SecurityToken = $secToken;
        $obj->Account = $phone;

        $webService = $client->Account_Check($obj);
        $res = $webService->Account_CheckResult;
        $DB->update('verify_okpay', array('phone'=>$phone, 'log'=>$res), "id = ?", $logId);
        if($res) {
            $is_verify = true;
        } else {
            $this->error = '��� ����������� � ��� ������ ���� ���������������� �������.';
            return false;

        }

        if ( $is_verify ) {
            $DB->update('verify_okpay', array('result'=>true), "id = ?", $logId);
            $DB->query("UPDATE users SET is_verify = TRUE WHERE uid = ?", $uid);
            return true;
        }


        return false;
    }    
    
    /**
     * ����������� ����� ���-������� ����. 
     * ��������� ������������� ���, ��������� � ��������������� ������ ��������
     * 
     * @param  integer $uid  uid ������������
     * @return boolean       ��������� ��������
     */
    public function pskb($uid) {
        global $DB;
        if ( empty($uid) ) {
            $this->error = '�� �� ������������.';
            return false;
        }
        $logId = $DB->insert('verify_pskb', array('user_id'=>$uid), 'id');
        // ���������� �������� �� ������������ ����� �� �������, ��� ��������
        if ( empty($this->aUserReqvs) ) {
            if ( !$this->ydCheckUserReqvs($uid) ) {
                return false;
            }
        }
        $phone = $this->aUserReqvs[1]['mob_phone'];
        $pskb  = new pskb;
        $res   = $pskb->checkOrCreateWallet($phone);
        $DB->update('verify_pskb', array('phone'=>$phone, 'log'=>$res), "id = ?", $logId);
        if ( empty($res) ) {
            $this->error = '������ ���������� � ���-���������.';
            return false;
        }
        $res = json_decode(iconv('cp1251', 'utf8', $res), 1);
        if ( empty($res['state']) || !in_array($res['state'], array('EXIST', 'COMPLETE')) ) {
            $this->error = '���-������� �� ������.';
            return false;
        }
        if ( !$res['verified'] ) {
            $this->error = '��� ����������� � ��� ������ ���� ������������������ �������.';
            return false;
        }
        $aFields    = array( 'fio', 'birthday', 'idcard_name', 'idcard', 'idcard_from', 'idcard_by', 'mob_phone' );
        $this->data = array();
        foreach ( $aFields as $sField ) {
            $this->data[$sField] = $this->aUserReqvs[1][$sField];
        }
        if ( $this->verify($uid) ) {
            $DB->update('verify_pskb', array('result'=>true), "id = ?", $logId);
            return true;
        }
        return false;
    }
    
    
    /**
     * ����� ����� �����������. ����� ��������� ������ � ���������� $this->data ����� ������ webmoney ��� ff 
     * ��� ������ ������� ���� �����, ����� ��������� ������ � �������������� ������������ �� ����� �����
     * 
     * @param  integer  $uid - uid ��������������� ������������
     * @return boolean       - �����
     */
    public function verify($uid) 
    {
        global $DB;
        $user = new users;
        $user->GetUserByUID($uid);
        if ( empty($user->uid) ) {
            $this->error = '�� �� ������������';
            return false;
        }
        if ( $user->is_verify == 't' ) {
            $this->error = '�� ��� ��������������';
            return false;
        }
        $DB->hold()->query("UPDATE users SET is_verify = TRUE WHERE uid = ?", $user->uid);
        $antiuid = $DB->val("SELECT anti_uid FROM users WHERE uid=?i", $user->uid);
        if($antiuid) { $DB->hold()->query("UPDATE users SET is_verify='t' WHERE uid=?i", $antiuid); }

        if ( !$DB->query() ) {
            //@todo: ����� ������ � UI �� �����
            //$this->error = '��������� ������.';
            return false;
        }
        
        if (isset($_SESSION['uid']) && 
            $_SESSION['uid'] == $uid) {
            
            $_SESSION['is_verify'] = 't';
        }
        
        return true;
    }
    
    
    /**
     * ���������� �������������, ��������� �����������
     * 
     * @return integer  ���-�� ���������������� �������������
     */
    public function verifyCount() {
        return (int) $GLOBALS['DB']->cache(3600)->val("
            WITH verifys as (
                        SELECT COUNT(v.user_id) as cnt FROM verify_ff v 
                        WHERE v.result = true
                        UNION 
                        SELECT COUNT(v.user_id) as cnt FROM verify_webmoney v
                        WHERE v.result = true 
                        UNION 
                        SELECT COUNT(v.user_id) as cnt FROM verify_yd v
                        WHERE v.result = true 
                        UNION 
                        SELECT COUNT(v.user_id) as cnt FROM verify_pskb v
                        WHERE v.result = true 
                        UNION 
                        SELECT COUNT(v.user_id) as cnt FROM verify_okpay v
                        WHERE v.result = true 
                        UNION 
                        SELECT COUNT(v.user_id) as cnt FROM verify_card v
                        WHERE v.result = true                  
                    )
            SELECT SUM(cnt)
            FROM verifys 
        ");
    }
    
    
    /**
     * ���������� ����� ����� ������������ ���������������
     * 
     * @param  integer  $user_id  uid ������������
     * @return string             ����� ����������� � ������� ���������
     */
    static public function verifyLast($user_id) {
        global $DB;
        return $DB->value("
            SELECT
                *
            FROM (
                SELECT req_time FROM verify_webmoney WHERE user_id = ? AND result
                UNION ALL
                SELECT req_time FROM verify_ff WHERE user_id = ? AND result
                UNION ALL
                SELECT req_time FROM verify_yd WHERE user_id = ? AND result
                UNION ALL
                SELECT req_time FROM verify_pskb WHERE user_id = ? AND result
                UNION ALL
                SELECT req_time FROM verify_okpay WHERE user_id = ? AND result
            ) v
            ORDER BY
                req_time DESC
            LIMIT
                1
        ", $user_id, $user_id, $user_id, $user_id);
    }
    
    /**
     * ���������� ���������� �� ����������� �������������
     * 
     * @global type $DB 
     * @param string  $fromDate    ��������� ���������� ���� �������
     * @param string  $toDate      �������� ���������� ���� �������
     * @param string  $type        ��� �������
     * @param boolean $is_verify  ������ ����������� ��� ���
     * @param boolean $role       true - ���������, false - �����������
     * @return array
     */
    static public function getStatVerify($fromDate, $toDate, $type = 'wm', $is_verify = false, $role = null) {
        global $DB;
        
        $inner = "";
        if($role !== null) {
            $tbl   = $role ? "freelancer":"employer";
            $inner = "INNER JOIN {$tbl} u ON u.uid = v.user_id";
        }
        
        
        switch($type) {
            case 'wm':
                $sql = "SELECT COUNT(v.*) as cnt FROM verify_webmoney v 
                        {$inner}
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ?";
                break;
            case 'ffpro':
                $sql = "SELECT COUNT(v.*) as cnt FROM verify_ff v
                        {$inner}
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.is_pro = true AND v.result = ?";
                break;
            case 'ffnopro':
                $sql = "SELECT COUNT(v.*) as cnt FROM verify_ff v
                        {$inner}
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.is_pro = false AND v.result = ?";
                break;
            case 'yd':
                $sql = "SELECT COUNT(v.*) as cnt FROM verify_yd v
                        {$inner}
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ?";
                break;
            case 'pskb':
                $sql = "SELECT COUNT(v.*) as cnt FROM verify_pskb v
                        {$inner}
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ?";
                break;
            case 'okpay':
                $sql = "SELECT COUNT(v.*) as cnt FROM verify_okpay v
                        {$inner}
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ?";
                break;
            case 'country':
                $sql = "
                    WITH verifys as (
                        SELECT v.user_id FROM verify_ff v 
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = true
                        UNION 
                        SELECT v.user_id FROM verify_webmoney v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = true 
                        UNION 
                        SELECT v.user_id FROM verify_yd v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = true 
                        UNION 
                        SELECT v.user_id FROM verify_pskb v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = true 
                    )
                    SELECT COUNT(*) as cnt, c.country_name FROM  verifys v
                    INNER JOIN users u ON u.uid = v.user_id
                    INNER JOIN country c ON c.id = u.country
                    GROUP BY c.country_name
                    ORDER by cnt DESC
                    LIMIT 10";
                return $DB->rows($sql, $fromDate, $toDate, $fromDate, $toDate, $fromDate, $toDate, $fromDate, $toDate);
                break;
            case 'ff_country':
                $sql = "
                    WITH verifys as (
                        SELECT v.user_id, v.is_pro FROM verify_ff v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ?
                    ), country_pro AS (
                        SELECT COUNT(*) as cnt, c.country_name, true::boolean as is_pro FROM verifys v
                        INNER JOIN users u ON u.uid = v.user_id
                        INNER JOIN country c ON c.id = u.country
                        WHERE v.is_pro = true
                        GROUP BY c.country_name
                        LIMIT 10
                    ), country_notpro AS (
                        SELECT COUNT(*) as cnt, c.country_name, false::boolean as is_pro FROM verifys v
                        INNER JOIN users u ON u.uid = v.user_id
                        INNER JOIN country c ON c.id = u.country
                        WHERE v.is_pro = false
                        GROUP BY c.country_name
                        LIMIT 10
                    )
                    SELECT * FROM country_pro
                    UNION
                    SELECT * FROM country_notpro
                    ORDER by cnt DESC";
                return $DB->rows($sql, $fromDate, $toDate, $is_verify);
                break;
            case 'yd_country':
                $sql = "
                    WITH verifys as (
                        SELECT v.user_id FROM verify_yd v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ?
                    )
                    SELECT COUNT(*) as cnt, c.country_name FROM  verifys v
                    INNER JOIN users u ON u.uid = v.user_id
                    INNER JOIN country c ON c.id = u.country
                    GROUP BY c.country_name
                    ORDER by cnt DESC
                    LIMIT 10";
                return $DB->rows($sql, $fromDate, $toDate, $is_verify);
                break;
            case 'wm_country':
                $sql = "
                    WITH verifys as (
                        SELECT v.user_id FROM verify_webmoney v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ? 
                    )
                    SELECT COUNT(*) as cnt, c.country_name FROM  verifys v
                    INNER JOIN users u ON u.uid = v.user_id
                    INNER JOIN country c ON c.id = u.country
                    GROUP BY c.country_name
                    ORDER by cnt DESC
                    LIMIT 10";
                return $DB->rows($sql, $fromDate, $toDate, $is_verify);
                break;
            case 'pskb_country':
                $sql = "
                    WITH verifys as (
                        SELECT v.user_id FROM verify_pskb v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ? 
                    )
                    SELECT COUNT(*) as cnt, c.country_name FROM  verifys v
                    INNER JOIN users u ON u.uid = v.user_id
                    INNER JOIN country c ON c.id = u.country
                    GROUP BY c.country_name
                    ORDER by cnt DESC
                    LIMIT 10";
                return $DB->rows($sql, $fromDate, $toDate, $is_verify);
                break;
            case 'okpay_country':
                $sql = "
                    WITH verifys as (
                        SELECT v.user_id FROM verify_okpay v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ? 
                    )
                    SELECT COUNT(*) as cnt, c.country_name FROM  verifys v
                    INNER JOIN users u ON u.uid = v.user_id
                    INNER JOIN country c ON c.id = u.country
                    GROUP BY c.country_name
                    ORDER by cnt DESC
                    LIMIT 10";
                return $DB->rows($sql, $fromDate, $toDate, $is_verify);
                break;
        }
        
        return $DB->row($sql, $fromDate, $toDate, $is_verify);
    }
    /**
    * @desc ��������� ���������� �������� �����������
    * @param int $uid
    **/
    static public function decrementStat($uid) {
        $uid = intval($uid);
        global $DB;
        $query = "SELECT ff_0.id AS n, pskb.id AS pskb_id, wm.id AS wm_id, yd.id AS yd_id, ff.id AS ff_id, okpay.id as okpay_id  
                FROM verify_ff AS ff_0 
                LEFT JOIN verify_pskb AS pskb ON pskb.user_id = {$uid}
                LEFT JOIN verify_webmoney AS wm ON wm.user_id = {$uid}
                LEFT JOIN verify_yd       AS yd ON yd.user_id = {$uid}
                LEFT JOIN verify_ff       AS ff ON ff.user_id = {$uid}
                LEFT JOIN verify_okpay       AS okpay ON okpay.user_id = {$uid}
                WHERE ff_0.user_id = {$uid}
                OR     pskb.user_id = {$uid}
                OR    wm.user_id = {$uid}
                OR    yd.user_id = {$uid}
                OR    ff.user_id = {$uid}
                OR    okpay.user_id = {$uid}";
        //��� ��� �� ���� ����� �������� ���������, � ������� ���� ���������� � ����������� ������ � ���� �� ������������ ����� ������ �������
        // � ����� �������� �������� � �������� � �� ���
        // ������ ��� �������������� ����� ������������ �� ������ verify_*  
        $data = $DB->rows($query);
        $pskb_ids = array();
        $wm_ids   = array();
        $yd_ids   = array();
        $ff_ids   = array();
        $okpay_ids   = array();
        foreach ($data as $row) {
            if ( intval($row["pskb_id"]) ) {
                $pskb_ids[intval($row["pskb_id"])] = 1;
            }
            if ( intval($row["wm_id"]) ) {
                $wm_ids[intval($row["wm_id"])] = 1;
            }
            if ( intval($row["yd_id"]) ) {
                $yd_ids[intval($row["yd_id"])] = 1;
            }
            if ( intval($row["ff_id"]) ) {
                $ff_ids[intval($row["ff_id"])] = 1;
            }
            if ( intval($row["okpay_id"]) ) {
                $okpay_ids[intval($row["okpay_id"])] = 1;
            }
        }
        if ( count( array_keys($pskb_ids) ) ) {
            $ids = join(",", array_keys($pskb_ids));
            $query = "DELETE FROM verify_pskb WHERE id IN ({$ids}) AND result = 't'";
            $DB->query($query);
        }
        if ( count( array_keys($wm_ids) ) ) {
            $ids = join(",", array_keys($wm_ids));
            $query = "DELETE FROM verify_webmoney WHERE id IN ({$ids}) AND result = 't'";
            $DB->query($query);
        }
        if ( count( array_keys($yd_ids) ) ) {
            $ids = join(",", array_keys($yd_ids));
            $query = "DELETE FROM verify_yd WHERE id IN ({$ids}) AND result = 't'";
            $DB->query($query);
        }
        if ( count( array_keys($ff_ids) ) ) {
            $ids = join(",", array_keys($ff_ids));
            $query = "DELETE FROM verify_ff WHERE id IN ({$ids}) AND result = 't'";
            $DB->query($query);
        }
        if ( count( array_keys($okpay_ids) ) ) {
            $ids = join(",", array_keys($okpay_ids));
            $query = "DELETE FROM verify_okpay WHERE id IN ({$ids}) AND result = 't'";
            $DB->query($query);
        }
    }
    
    
    
    public static function getYDUriAuth($project_id = null)
    {
        return sprintf("%s/oauth/authorize?client_id=%s&response_type=code&scope=%s&redirect_uri=%s", 
                self::YD_URI_AUTH,
                self::YD_CLIENT_ID, 
                urlencode('account-info'),
                urlencode(self::YD_REDIRECT_URI . ($project_id ? "?type=project&id={$project_id}" : "?type=promo")));
    }
    
}