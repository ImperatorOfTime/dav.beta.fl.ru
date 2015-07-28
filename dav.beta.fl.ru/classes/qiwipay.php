<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
/**
 *
 * ���������� ����� ����� QIWI.�������
 *
 */
class qiwipay
{
    /**
     * ����������� ����� �����.
     */
    const MIN_SUM = 1;

    /**
     * ������������ ����� �����.
     */
    const MAX_SUM = 15000;

    /**
     * ��� ��������� ������� (��. account_operations.payment_sys).
     */
    const PAYMENT_SYS = 9;
    
    /**
     * ���������� ���������� ������� � ������� ������� ����� ��������� ���� (0 ���� �� ����������).
     */
    const MAX_PHONE_NUM = 5;

    
    const STATUS_ACCEPTED = 50;
    const STATUS_PROCESS  = 52;
    const STATUS_COMPLETED = 60;
    const STATUS_TERMINAL_ERROR = 150;
    const STATUS_CANCELED = 160;
    const STATUS_EXPIRED  = 161;
    
    /**
     * �������������� �������, ���� �� ��� �� ��� ���������������?
     * @var integer
     */
    public $create_agt = 1;

    /**
     * ����� ����� ����� � ����� (0 -- ��������=45 �����).
     * @var integer
     */
    public $ltime = 0;

    /**
     * ���������� ������� �� SMS �� ������� ������������ �����?
     * @var integer
     */
    public $alarm_sms = 0;

    /**
     * ���������� ������� ������� �� ������� ������������ �����?
     * @var integer
     */
    public $accept_call = 0;

    /**
     * ������ � ������ ��������� ������ (�� html-�����)
     * @var array
     */
    public $form;
    
    /**
     * ��. ������������ ��� ����������� �����.
     * @var integer
     */
    public $uid;


    public $login  = '7458';
    public $passwd = QIWI_PASSWD;
    
    public $encode = 'windows-1251'; 
    public $url = 'https://ishop.qiwi.ru/xmlcp';
    

    private $DB;
    private $_cookie_key;

    private $_errors = array(
      300 => '����������� ������',
      13  => '������ �����. ��������� ������ �����',
      150 => '�������� ����� ��� ������',
      215 => '���� � ����� ������� ��� ����������',
      278 => '���������� ������������� ��������� ��������� ������ ������',
      298 => '����� �� ���������� � �������',
      330 => '������ ����������',
      370 => '��������� ����. ���-�� ������������ ����������� ��������'
    );
    
    /**
     * ������������ ����� ��������, ����������� � ������ ������ �������� ������������� ���������.
     * ID ��������� => ������������ ����� �������� � ��� 
     * ��� ������ ������ ���� ��� �����������.
     * 
     * @var array
     */
    private $aOperatorLimit = array(
        3 => 20
    );
    
    /**
     * ����������� ������
     * 
     * @param int $uid ��. ������������ ��� ����������� �����.
     */
    function __construct($uid = NULL) {
        $this->DB = $GLOBALS['DB'];
        $this->uid = $uid;
        if($this->uid) {
            $this->_cookie_key = 'QIWI' . $this->uid;
            $this->getBillForm();
        }
    }
    
    /**
     * ��������� ��������� ������� ��� ����������� ����� � �������.
     *
     * @param  array $request ��������� ($_POST).
     * @param  int $account_id ID �����.
     * @return array $error ������ ������.
     */
    function validate( $request, $account_id ) {
        $error = NULL;
        foreach($request as $f=>$v) {
            $err = NULL;
            $v = htmlspecialchars(stripslashes(trim($v)));
            switch($f) {
                case 'phone' :
                    if(!preg_match('/^\d{10}$/', $v))
                        $err = '�������� ������';
                    break;
                case 'sum' :
                    setlocale(LC_ALL, 'en_US.UTF-8');
                    $v = floatval($v);
                    if($v > self::MAX_SUM)
                        $err = '������� ������� �����';
                    else if($v < self::MIN_SUM)
                        $err = '����������� ����� &mdash; ' . self::MIN_SUM . ' ���.';
                    break;
                case 'comment' :
                    $v = substr($v, 0, 255);
                    break;
//                case 'rndnum':
//                    $cap = new captcha();
//                    if(!$cap->checkNumber($v)) $err = '��� ������ �������';
//                    break;
            }
            if($err) $error[$f] = $err;
            $this->form[$f] = $v;
        }
        
        // ��������� ����������� �� ����������� ������
        if ( !$error['phone'] ) {
            $bFound = false;
        	$aPhone = $this->DB->rows( 'SELECT * FROM qiwi_phone WHERE account_id = ?i', $account_id );
    	    
    		foreach ( $aPhone as $aCurrPhone ) {
    			if ( $request['phone'] == $aCurrPhone['phone'] ) {
    				$bFound = true;
    				break;
    			}
    		}
        	
        	// 1. ������������ ���������� ������������ ���������� �������
        	if ( self::MAX_PHONE_NUM > 0 && count($aPhone) >= self::MAX_PHONE_NUM ) {
        		if ( !$bFound ) {
        			$error['max_phone_num'] = 1;
        		}
        	}
        	
        	// 2. ������������ ���������� �������� � ������ ������
        	$memBuff = new memBuff();
        	$sKey    = 'qiwiPhone'.$account_id.'_'.$aCurrPhone['phone'];
        	$aData   = $memBuff->get( $sKey );
        	$nStamp  = time();
            
        	if ( !$error['max_phone_num'] && count($this->aOperatorLimit) && $aData && $bFound && $aCurrPhone['operator_id'] ) {
        	    if ( isset($aData['wait']) && $nStamp < $aData['wait'] ) {
        	    	$nLast = $aData['wait'] - $nStamp;
        	    	$sLast = '';
        	    	
        	    	if ( $nLast > 60 ) {
        	    		$nTime  = ceil( $nLast / 60 );
        	    		$nLast %= 60;
        	    		$sLast .= $nTime > 0 ? $nTime . ' ' . ending( $nTime, '������', '������', '�����' ) : '';
        	    	}
        	    	
        	    	if ( $nLast >= 1 ) {
        	    		$sLast .= ($sLast ? ' � ' : '') . $nLast . ' ' . ending( $nLast, '�������', '�������', '������' );
        	    	}
        	    	
        	    	$error['max_pay_num'] = '���������� �������� � ������ '. $aCurrPhone['phone'] .' �� ��������� ���<br/> ��������� ���������� �����. ��������� ������� ����� '.$sLast;
        	    }
        	    else {
                	foreach ($this->aOperatorLimit as $nOpID => $nMaxPay) {
                		if ( $aCurrPhone['operator_id'] == $nOpID && $nStamp - $aData['time'] <= 3600 && $aData['cnt'] >= $nMaxPay ) {
                		    $aData['wait'] = $nStamp + 3540;
                		    $memBuff->set( $sKey, $aData, 3600 );
                		    $error['max_pay_num'] = '���������� �������� � ������ '. $aCurrPhone['phone'] .' �� ��������� ���<br/> ��������� ���������� �����. ��������� ������� ����� 1 ���';
                    	}
                    }
        	    }
        	}
        }
        //---------------------------------------
        
        return $error;
    }

    /**
     * ������� ����� ����, ���������� �� ����������� � ��������� �������.
     *
     * @param integer $uid    ��. ������������.
     * @param array $request    ��������� ($_POST).
     * @return array $error   ������ ������.
     */
    function createBill($request) {
        if ( !$this->uid ) return '������������ �� ���������';
        
        $account = new account();
        $account->GetInfo( $this->uid, true );
        
        if ( $error = $this->validate($request, $account->id) ) return $error;
        
		$this->DB->start();
		
		$aData = array(
			'account_id' => $account->id,
			'phone'      => $this->form['phone'],
			'sum'        => $this->form['sum']
		);
		
		$id = $this->DB->insert("qiwi_account", $aData, "id");
        $oper_xml = '';
        switch($request['oper_code']) {
            case 'megafon':
                $oper_xml = '<extra name="megafon2-acc">1</extra>';
                break;
            case 'mts':
                $oper_xml = '<extra name="mts-acc">1</extra>';
                break;
            case 'beeline':
                $oper_xml = '<extra name="beeline-acc">1</extra>';
                break;
        }
		
        if ($id) {
            $xml = '<?xml version="1.0" encoding="' . $this->encode . '"?>'
                 . '<request>'
                 . '<protocol-version>4.00</protocol-version>'
                 . '<request-type>30</request-type>'
                 . '<extra name="password">' . $this->passwd . '</extra>'
                 . '<terminal-id>' . $this->login . '</terminal-id>'
                 . '<extra name="txn-id">' . $id . '</extra>'
                 . '<extra name="to-account">' . $this->form['phone'] . '</extra>'
                 . '<extra name="amount">' . $this->form['sum'] . '</extra>'                 
                 . '<extra name="comment">' . $this->form['comment'] . '</extra>'
                 . '<extra name="create-agt">' . $this->create_agt . '</extra>'
                 . '<extra name="ltime">' . $this->ltime . '</extra>'
                 . '<extra name="ALARM_SMS">' . $this->alarm_sms . '</extra>'
                 . '<extra name="ACCEPT_CALL">' . $this->accept_call . '</extra>'
                 . $oper_xml
                 . '</request>';
            if($this->passwd=='debug') {
                $result = '<response><result-code fatal="false">0</result-code></response>';
            }
            else {
                $result = $this->_request($xml);
            }
            if($err = $this->_checkResultError($result)) {
                $error['qiwi'] = $err;
                $this->DB->rollback();
                die;
                return $error;
            }
            
            // ��������� ����������� �� ����������� ������
            unset( $aData['sum'] );
            
            $sCode = substr( $aData['phone'], 0, 3 );
    		$sNum  = substr( $aData['phone'], 3 );
    		$sOper = $this->DB->val( 'SELECT COALESCE(operator_id, 0) FROM mobile_operator_codes 
                WHERE code = ? AND ? >= start_num AND ? <= end_num', 
                $sCode, $sNum, $sNum 
    		);
            
    		$aData['operator_id'] = $sOper;
    		
            $this->DB->insert( 'qiwi_phone', $aData );
            
        	$memBuff = new memBuff();
        	$nStamp  = time();
        	$sKey    = 'qiwiPhone' . $account->id . '_' . $aData['phone'];
        	
        	if ( !$aData = $memBuff->get($sKey) ) {
        		$aData = array( 'time' => $nStamp, 'cnt' => 0 );
        	}
        	
        	$aData['time'] = ( $aData['time'] + 3600 > $nStamp ) ? $aData['time']    : $nStamp;
        	$aData['cnt']  = ( $aData['time'] + 3600 > $nStamp ) ? $aData['cnt'] + 1 : 1;
        	
        	$memBuff->set( $sKey, $aData, 3600 );
        	//-----------------------------------
        }
        $this->DB->commit();
        $this->saveBillForm();
        return 0;
    }


    /**
     * ��������� ��������� ������ � ����.
     */
    function saveBillForm() {
        foreach($this->form as $key=>$val)
            setcookie("{$this->_cookie_key}[{$key}]", $val, time()+60*60*24*60, "/", $GLOBALS['domain4cookie'], COOKIE_SECURE);
    }

    /**
     * ����� �� ���� ������ ��� �����.
     */
    function getBillForm() {
        $this->form = $_COOKIE[$this->_cookie_key];
        $this->form['sum'] = '';
    }
    

    /**
     * ��������� ������� ������������ ������. ��������� ���� ��� ����������.
     *
     * @param string $error   ���� ����� ������.
     * @return integer   ���������� ����������� ������; 
     */
    function checkBillsStatus(&$error = NULL) {
        $offset = 0;
        $limit = 300;
        $completed_cnt = 0;

        libxml_disable_entity_loader();
        $error = NULL;
        $sql = 'SELECT * FROM qiwi_account OFFSET '.$offset.' LIMIT '.$limit;
        if(!($res = $this->DB->query($sql)) || !pg_num_rows($res)) return 0;

        while(pg_num_rows($res)) {
            $curr_bills = array();
            $xml = '<?xml version="1.0" encoding="' . $this->encode . '"?>'
                 . '<request>'
                 . '<protocol-version>4.00</protocol-version>'
                 . '<request-type>33</request-type>'
                 . '<extra name="password">' . $this->passwd . '</extra>'
                 . '<terminal-id>' . $this->login . '</terminal-id>'
                 . '<bills-list>';


            while($row = pg_fetch_assoc($res)) {
                $xml .= '<bill txn-id="' . $row['id'] . '" />';
                $curr_bills[$row['id']] = $row;
            }

            $log = new log('qiwipay/qiwi-%d%m%Y.log');
            $log->writeln();
            $log->writeln(date('c'));
            $log->writeln('===============================');

            $xml .= '</bills-list></request>';
            
            $log->writeln($xml);
            
            $result = $this->_request($xml);
            
            $log->writeln($result);

            if(!$result || ($error = $this->_checkResultError($result)))
                return 0;

            $xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?>'.$result);
            $bills = array();

            foreach ($xml->{'bills-list'}->children() as $bill) {
                $status = (string)$bill['status'];
                $id = (string)$bill['id'];
                switch($status) {
                    case self::STATUS_ACCEPTED :
                    case self::STATUS_PROCESS :
                        continue 2;
                    case self::STATUS_TERMINAL_ERROR :
                    case self::STATUS_EXPIRED :
                    case self::STATUS_CANCELED :
                        $this->deleteBill($error, $id);
                        break;
                    case self::STATUS_COMPLETED :
                        $this->completeBill($error, $curr_bills[$id], $bill['sum']);
                        if(!$error) $completed_cnt++;
                        break;
                    default :
                        $this->updateBillStatus($error, $id, $status);
                        break;
                }
            }


            $offset = $offset+$limit;
            $sql = 'SELECT * FROM qiwi_account OFFSET '.$offset.' LIMIT '.$limit;    
            $res = $this->DB->query($sql);
        }


        return $completed_cnt;
    }

    /**
     * ��������� ������ ����� �������� �������, ��������� ��������� ��������.
     *
     * @param string $error   ���� ����� ������.
     * @param integer $id   ��. ����� (qiwi_account.id)
     * @param integer $status   ������
     * @return boolean   �����?
     */
    function updateBillStatus(&$error, $id, $status) {
        if (!$this->DB->query("UPDATE qiwi_account SET status = ? WHERE id = ?", $status, $id)) {
            $error = $this->DB->error;
            return FALSE;
        }
        return TRUE;
    }

    /**
     * ��������� FM-����.
     *
     * @param string $error   ���� ����� ������.
     * @param array $bill   qiwi-����
     * @param float $sum   ����� �����������
     * @return boolean   �����?
     */
    function completeBill(&$error, $bill, $sum) {
        $account = new account();
        $descr = "���������� ����� ������� QIWI.������� -- �����: {$sum} ���., �������: {$bill['phone']}, ���� #{$bill['id']}";
        $error = $account->deposit($op_id, $bill['account_id'], $sum, $descr, self::PAYMENT_SYS, $sum, 12);
        if($error) return false;
        return $this->deleteBill($error, $bill['id']);
    }

    /**
     * ������� ������ �� ������.
     *
     * @param string $error   ���� ����� ������.
     * @param integer $id   ��. ����� (qiwi_account.id)
     * @return boolean   �����?
     */
    function deleteBill(&$error, $id) {
        if (!$this->DB->query("DELETE FROM qiwi_account WHERE id = ?", $id)) {
            $error = $this->DB->error;
            return false;
        }
        return true;
    }

    /**
     * ���� ��������� ������� �������� �� ������, �� ��������� ����� ������
     *
     * @param string $result   ����� ������� (xml-������)
     * @return string   ����� ��� ����� ������.
     */
    function _checkResultError($result) {
        libxml_disable_entity_loader();
    	if ($result){
            libxml_use_internal_errors(true);
            $rxml = simplexml_load_string('<?xml version="1.0" encoding="' . $this->encode . '"?>' . $result);
            
            if ($rxml === FALSE) {
                $this->_log_errors(libxml_get_errors(), $result);
                libxml_clear_errors();
                
                $rc = "";

            } else {
                $rc = $rxml->{'result-code'};
            }
            
    	} else $rc = "";
        return $this->_errors[(string)$rc];
    }

    /**
     * ���������� ������ � ��������� �������
     *
     * @param string $xml   ���� ������� (xml-������)
     * @return string   ����� ������� (xml-������)
     */
    function _request($xml) {
    	$body = $this->_encrypt($xml);

    	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'text/xml; encoding=' . $this->encode));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
    
    /**
     * ���������� ������������� ������ ������� ��� �������� � ��������� �������, �������� ���������.
     *
     * @param string $xml   ���� ������� (xml-������)
     * @return string   ������� ������ �������.
     */
    function _encrypt($xml) { 
        $passwordMD5 = md5($this->passwd, TRUE);
    	$salt = md5($this->login . bin2hex($passwordMD5), TRUE);
    	$key = str_pad($passwordMD5, 24, '\0');

    	for ($i = 8; $i < 24; $i++) {
    		if ($i >= 16) {
    			$key[$i] = $salt[$i-8];
    		} else {
    			$key[$i] = $key[$i] ^ $salt[$i-8];
    		}
    	}

    	$n = 8 - strlen($xml) % 8;
    	$pad = str_pad($xml, strlen($xml) + $n, ' ');
    	$crypted = mcrypt_encrypt(MCRYPT_3DES, $key, $pad, MCRYPT_MODE_ECB, "\0\0\0\0\0\0\0\0");
    	$result = "qiwi" . str_pad($this->login, 10, "0", STR_PAD_LEFT) . "\n";
    	$result .= base64_encode($crypted);
    	
    	return $result;
    }
    
}
