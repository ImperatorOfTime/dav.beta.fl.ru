<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/log.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/payment_keys.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/account.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/WMXI/WMXI.php');

define('WMXI_LOCALE', 'ru_RU');        

class WMXIPayouts extends WMXI {

    public $basic_auth;
    public $debug_url;
    private $_log;
    
    function _request($url, $xml, $scope = '') {
        if(!$this->_log) {
            $this->_log = new log('wm_payments/wm_payments-%d%m%Y.log');
        }
        $this->_log->linePrefix = '%d.%m.%Y %H:%M:%S : ' . getRemoteIP() . " : {$scope} : ";
        
        $res = parent::_request($url, $xml, $scope);
        $this->_log->writeln('REQUEST:');
        $this->_log->writeln($res->GetRequest());
        $this->_log->writeln('RESPONSE:');
        $this->_log->writeln($res->GetResponse());
        
        return $res;
    }

    public function UKG1($name, $passport_serie, $passport_number, $passport_date, $purse, $price) {
        $req = new SimpleXMLElement('<w3s.request/>');

        if ($this->classic) {
            $req->wmid = $this->wmid;
            $req->sign = $this->_sign($this->wmid.$name.$passport_serie.$passport_number.$passport_date.$purse.$price);
            $req->sign->addAttribute('type', 1);
        }
        $group = 'payment';
        $req->$group->name = iconv('CP1251', 'UTF-8', $name);
        $req->$group->passport_serie = iconv('CP1251', 'UTF-8', $passport_serie);
        $req->$group->passport_number = iconv('CP1251', 'UTF-8', $passport_number);
        $req->$group->passport_date = date('Ymd', strtotime($passport_date));
        $req->$group->purse = $purse;
        $req->$group->price = $price;
        $url = $this->debug_url ? $this->debug_url : 'https://transfer.guarantee.ru/AgentInPreXml.aspx';

        return $this->_request($url, $req->asXML(), __FUNCTION__);
    }

    public function UKG2($payment_id, $payment_test, $name, $passport_serie, $passport_number, $passport_date, $purse, $price, $cheque, $date, $kiosk_id, $phone) {
        $req = new SimpleXMLElement('<w3s.request/>');

        if ($this->classic) {
            $req->wmid = $this->wmid;
            $req->sign = $this->_sign($this->wmid.$payment_id.$payment_test.$name.$passport_serie.$passport_number.$passport_date.$phone.$purse.$price.$cheque.$date.$kiosk_id);
            $req->sign->addAttribute('type', 1);
        }
        $group = 'payment';
        $req->$group = NULL;
        $req->$group->addAttribute('id', $payment_id);
        $req->$group->addAttribute('test', $payment_test);
        $req->$group->name = iconv('CP1251', 'UTF-8', $name);
        $req->$group->passport_serie = iconv('CP1251', 'UTF-8', $passport_serie);
        $req->$group->passport_number = iconv('CP1251', 'UTF-8', $passport_number);
        $req->$group->passport_date = date('Ymd', strtotime($passport_date));
        $req->$group->purse = $purse;
        $req->$group->price = $price;
        $req->$group->date = date('Ymd H:i:s', strtotime($date));
        $req->$group->cheque = $cheque;
        $req->$group->kiosk_id = $kiosk_id;
        $req->$group->phone = $phone;
        $url = $this->debug_url ? $this->debug_url : 'https://transfer.guarantee.ru/AgentInXml.aspx';

        return $this->_request($url, $req->asXML(), __FUNCTION__);
    }

    public function UKG3($startdate, $enddate, $test = NULL) {
        $req = new SimpleXMLElement('<w3s.request/>');

        if ($this->classic) {
            $req->wmid = $this->wmid;
            $req->sign = $this->_sign($this->wmid.$startdate.$enddate);
            $req->sign->addAttribute('type', 1);
        }
        $group = 'payment';
        $req->startdate = $startdate;
        $req->enddate= $enddate;
        if($test !== NULL) {
            $req->test= (int)$test;
        }
        $url = $this->debug_url ? $this->debug_url : 'https://transfer.guarantee.ru/AgentInHistory.aspx';

        return $this->_request($url, $req->asXML(), __FUNCTION__);
    }
}

class wm_payments {
    
    // ��� ��������� �������
    const SRC_SBR = 1; // ������ ��� �����
    
    // ���������� ���������� ��������
    const RES_OK = 0;  // �����. ��������� ���������. �������� ��������� �������
    
    const WMID = '200477354071';
    const TEST_MODE = 0;
    
    private $_wmkey = array('nkey'=>WM_NKEY, 'ekey'=>WM_EKEY);

    private $_src = array();
    private $_pmt = array();
    private $_tr = array();
    private $_ptry = 0;
    private $_pdata;
    private $_answer;
    private $_maxAmt = 0;

    private $_performedAmt      = 0;
    private $_performedAmtFixed = 0;
    
    private $_isPmtLocked = false;
    
    private $_wmxi;

    public $ignoreLimit = false;
    public $reqConfirm = false;

    // ��� ������� ����.
    public $pmt;
    public $tr;

    public $DEBUG;

    public $logOn = false;

    
    /**
     * ����������� ������
     *
     * @param int $src_id ID ��������� �������
     * @param int $src_type ��� ��������� �������
     */
    function __construct($src_id = NULL, $src_type = NULL) {
        $this->DB = new DB('master');
        $this->setSrc($src_id, $src_type);
    }
    
    /**
     * ������������� �������� ������
     * 
     * @param int $src_id ID ��������� �������
     * @param int $src_type ��� ��������� �������
     */
    function setSrc($src_id, $src_type) {
        $this->_src = array('id'=>(int)$src_id, 'type'=>(int)$src_type);
    }
    
    /**
     * ���������� ����������������� �����. ���� �����, �� ��� �����, ����� ����� ����� ������� ��� �����.
     * (� �������� � ��� ������, �����, ��� "10.50 - �� �����". �� ������� ��� ��� "10.5".)
     * 
     * @param  float $amt �����
     * @return string
     */
    private function _amtFmt($amt) {
        $amt = (string)$amt;
        $amti = (string)(int)$amt;
        if($amt == $amti) {
            return $amti;
        }
        return sprintf('%01.2f', $amt);
    }
    
    
    private function _initData() {
        $ok = true;
        foreach ($this->_pdata as $field=>&$val) {
            switch ($field) {
                case 'cheque' :
                case 'kiosk_id' :
                    break;
                case 'name'            : $ok = $val ? $ok : $this->error('���������� ������� ��� ����������.'); break;
                case 'passport_serie'  : $ok = $val ? $ok : $this->error('���������� ������� ����� �������� ����������.'); break;
                case 'passport_number' : $ok = $val ? $ok : $this->error('���������� ������� ����� �������� ����������.'); break;
                case 'passport_date' :
                    if($time = strtotime($val)) {
                        $val = date('Ymd', $time);
                    } else {
                        $ok = $this->error('���������� ������� ���� ������ �������� ����������.');
                    }
                    break;
                case 'purse' : $ok = $val ? $ok : $this->error('���������� ������� ����� WMR-�������� ����������.'); break;
                case 'price' :
                    $ok = $this->_safeAmt($val) && $ok;
                    $ok = $val ? $ok : $this->error('�� ������ ����� �������.');
                    break;
                case 'phone' : 
                    $val = preg_replace('/\D/', '', $val);
                    $ok = $val ? $ok : $this->error('���������� ������� ����� �������� ����������.');
                    break;
            }
        }
        return $ok;
    }
    
    /**
     * ������� ����� ������ �������
     * 
     * @return array ������ ������� (��. ������� wm_payments), ��� bool false - ������
     */
    private function _createPayment() {
        if(!$this->_src['id'] || !$this->_src['type'])
            return false;
        $this->_pmt = $this->DB->row(
          'INSERT INTO wm_payments (src_type, src_id, in_amt, is_locked) VALUES (?i, ?i, ?f, true) RETURNING *',
           $this->_src['type'], $this->_src['id'], (float)$this->_pdata['price']);
        $this->_isPmtLocked = ($this->_pmt['is_locked'] == 't');
        return $this->_pmt;
    }
    
    /**
     * ��������� ������ �������
     * 
     * @param  bool $lock �������� ����� ����������
     * @return array ������ ������� (��. ������� wm_payments), ��� bool false - ������
     */
    private function _lockPayment($lock = true) {
        if(!$this->_src['id'] || !$this->_src['type'])
            return false;
        $lw = $lock ? '�������������' : '��������������';
        $pmt = $this->DB->row('UPDATE wm_payments SET is_locked = ?b WHERE src_type = ?i AND src_id = ?i RETURNING *', $lock, $this->_src['type'], $this->_src['id']);
        if(!$pmt)
            return $this->error("�� ������� {$lw} ������ #{$pmt['id']}! ���������� � ���. �����.");
        $this->_isPmtLocked = $lock;
        return ($this->_pmt = $pmt);
    }
    
    /**
     * ���������� ������ ������� �� ��������� �������
     * @see wm_payments::setSrc
     * 
     * @param  bool $lock ����� �� ��������� ����������� ������ � ���������
     * @return array ������ ������� (��. ������� wm_payments), ��� NULL ���� ������ �� �������
     */
    function getPayment($lock = false) {
        if(!$this->_src['id'] || !$this->_src['type'])
            return NULL;
        $pmt = $this->DB->row('SELECT * FROM wm_payments WHERE src_type = ?i AND src_id = ?i'.($lock ? ' FOR UPDATE' : ''), $this->_src['type'], $this->_src['id']);
        $this->_isPmtLocked = ( $lock && $this->DB->error || $pmt['is_locked'] == 't' );
        return $pmt;
    }
    
    /**
     * ��������� ������������� �� ������
     *
     * @return bool
     */
    function isPmtLocked() {
        return $this->_isPmtLocked;
    }
    
    
    /**
     * ������������� ����� ��� ������ �������. ����� ��������������� �����������, � ����� ������������� ��� ��������� ���� limit � ������.
     *
     * @param  int $user_id UID ������������
     * @param  int $limit    �����.
     * @return boolean 
     */
    function setLimit($limit) {
        $limit = (int)$limit;
        if($limit < 0) {
            return false;
        }
        
        $this->_initPayment(false);
        
        if($limit != $this->_pmt['amt_limit']) {
            $sql = 'UPDATE wm_payments SET amt_limit = ?i WHERE id = ?i';
            if($this->DB->query($sql, $limit, $this->_pmt['id'])) {
                $this->_pmt['limit'] = $limit;
            }
        }
        
        $this->_maxAmt = $this->_pmt['amt_limit'];
        if($this->_tr) {
            $this->_safeAmt($this->_tr['price']);
        }
        
        return true;
    }
    
    /**
     * �������������� ��������
     * 
     * @return array ������ �������
     */
    private function _initPayment($lock = true) {
        if($this->_pmt)
            return $this->_pmt;

        if($this->DB->start()) {
            $this->_pmt = $this->getPayment(TRUE);
            if($this->isPmtLocked()) {
                $this->DB->rollback();
                return $this->error('�������� �� ������ ������� ��� �����������...');
            }

            if(!$this->_pmt) {
                $this->_createPayment();
            } else if($lock) {
                $this->_lockPayment(TRUE);
            }
            
            
            if($ok = ($this->_pmt && !$this->errors)) {
                $this->setLimit($this->_pmt['amt_limit']);
                $ok = $this->DB->commit();
            }
            if(!$ok) {
                $this->DB->rollback();
                return false;
            }
        }

        return $this->_pmt;
    }
    
    /**
     * ��������� ��������
     */
    private function _commitPayment() {
        $this->_lockPayment(FALSE);
    }
    
    /**
     * �������������� ������ WMXI
     */
    private function _initWMXI() {
        if(!$this->_wmxi) {
            $this->_wmxi = new WMXIPayouts($this->DEBUG ? '' : $_SERVER['DOCUMENT_ROOT'] . '/classes/WMXI/WMXI.crt', 'windows-1251');
            if($this->DEBUG) {
                $this->_wmxi->debug_url = $this->DEBUG['address'];
                if(defined('BASIC_AUTH')) {
                    $this->_wmxi->basic_auth = BASIC_AUTH;
                }
            }
            $this->_wmxi->Classic(self::WMID, $this->_wmkey);
        }
    }
    
    /**
     * ������������ �������� ����������
     *
     * @param  float $price ����� �������
     * @param  string $purse ����� ����� (��������)
     * @param  string $cont ��������� ��� ���������� �������
     * @return float ����������� �����
     */
    function pay($name, $passport_serie, $passport_number, $passport_date, $purse, $price, $cheque, $kiosk_id, $phone) {
        $this->_initWMXI();
        $this->_pdata = compact(
          'name', 'passport_serie', 'passport_number', 'passport_date', 'purse', 'price', 'cheque', 'kiosk_id', 'phone'
        );
        if($this->_initPayment()) {
            if($this->_initData() && $this->_initTr()) {
                $this->_analyzeTr();
            }
            $this->_commitPayment();
        }
        $this->log("���������:\t{$this->_performedAmt}");
        $this->pmt = $this->_pmt;
        $this->tr = $this->_tr ? $this->_tr : $this->_pdata;
        return $this->_performedAmt;
    }
    
    /**
     * ������������ �������� ������ ������� ������
     *
     * @return string ����� �������
     */
    function balance() {
        // $this->_initWMXI();
        // return $this->_getLstBalance();
    }

    
    /**
     * �� �����.
     */
    function history($from_date, $to_date, $test = NULL) {
        $this->_initWMXI();
        $from_date = date('Ymd', strtotime($from_date));
        $to_date = date('Ymd', strtotime($to_date));
        $res = $this->_wmxi->UKG3($from_date, $to_date, $test);
    }
    
    /**
     * ���������� ��������� ��������������� ������
     * 
     * @return float
     */
    function _getLstBalance() {
        if($this->_tr && $this->_tr['rest'])
            return $this->_tr['rest'];
       return $this->DB->val('SELECT rest FROM wm_trs ORDER BY id DESC WHERE rest IS NOT NULL LIMIT 1');
    }
    
    /**
     * ��������� �������� �� ������� ���������� �����������
     * 
     * @return bool
     */
    private function _isTrPerformed() {
        return ($this->_tr['dateupd'] && $this->_tr['retval'] == self::RES_OK);
    }
    
    /**
     * ���������� ������� ����� ������ �������� � ������������ �������.
     * 
     * @return float
     */
    private function _remAmt() {
        return $this->_amtFmt($this->_pmt['in_amt'] - ($this->_pmt['out_amt'] + $this->_performedAmt));
    }
    
    /**
     * �������� ������������ ����� ����������.
     * 
     * @param  float $amt ����� ����������
     * @return bool true - ���������, false - �� ���������
     */
    private function _safeAmt(&$amt) {
        if( $this->_performedAmtFixed != $this->_performedAmt )
            return $this->error('��������� �������� ����� (����� ���������� ���������� != ����� ��������������� ����������).');
        if( ($amt = $this->_amtFmt(min($amt, $this->_maxAmt > 0 ? $this->_maxAmt : 99999999999, $this->_remAmt()))) <= 0 ) {
            return $this->error('������� ������������ � ���������� ������� �����.');
        }
        return true;
    }

    /**
     * ���������� ����������
     * 
     * @param  int $tr_id ID ����������
     * @return array
     */
    function getTr($tr_id) {
        if($tr_id)
            return $this->DB->row('SELECT * FROM wm_trs WHERE id = ?i', $tr_id);
        return NULL;
    }
    
    /**
     * �������������� ����������
     * 
     * @return array
     */
    private function _initTr() {
        if($this->_pmt['ltr_id']) {
            $this->_tr = $this->getTr($this->_pmt['ltr_id']);
            foreach($this->_pdata as $f=>$v) {
                $this->_tr[$f] = $v;
            }
        } else {
            $this->_tr = $this->_createTr();
        }
        if($this->_tr) {
            $this->_tr['req_date'] = date('Ymd H:i:s', strtotime($this->_tr['req_date']));
            $this->_tr['passport_date'] = date('Ymd', strtotime($this->_tr['passport_date']));
        }
        return $this->_tr;
    }
    
    /**
     * ������� ����������
     *
     * @return array ���������� ��� false � ������ ������
     */
    private function _createTr() {
        if( !$this->_pdata )
            return false;
        $this->_tr = $this->DB->row(
          'INSERT INTO wm_trs (payment_id, name, passport_serie, passport_number, passport_date, purse, price, cheque, kiosk_id, phone)
           VALUES (?i, ?, ?, ?, ?, ?, ?f, ?, ?, ?) RETURNING *',
           $this->_pmt['id'], $this->_pdata['name'], $this->_pdata['passport_serie'], $this->_pdata['passport_number'],
           $this->_pdata['passport_date'], $this->_pdata['purse'], $this->_pdata['price'], $this->_pdata['cheque'], $this->_pdata['kiosk_id'],
           $this->_pdata['phone']
        );
        if($this->_tr) {
            $this->_pmt['ltr_id'] = $this->_tr['id'];
            return $this->_tr;
        }
        return $this->error('������ ��� �������� ����������.');
    }
    
    /**
     * �������� ����������� ����������
     * 
     * @param  array $answer ���������� ����� �������
     * @return bool true - �����, false - ������
     */
    private function _checkPayTr(&$answer) {
        $res = $this->_wmxi->UKG1($this->_tr['name'], $this->_tr['passport_serie'], $this->_tr['passport_number'], $this->_tr['passport_date'], $this->_tr['purse'], $this->_tr['price']);
        $answer = $res->toArray();
        if($res->ErrorCode()) {
            return $this->error($res);
        }
        return !!$answer;
    }
    
    /**
     * ����������
     * 
     * @param  bool $new_tr ����� �� ��������� ����� ����������
     * @return bool false
     */
    private function _payTr($new_tr = false) {
        if( $new_tr && !$this->_createTr() )
            return false;
        if( !$this->_tr )
            return false;
        if( $this->_isTrPerformed() )
            return $this->error("������� ��������� ������������ ���������� {$this->_tr['id']}. ���������� � ���. �����.");
        if( ! $this->_safeAmt($this->_tr['price']) )
            return false;

        if($this->_ptry > 10 && $this->DEBUG) {
            return $this->error('������. ��������� ������.');
        }

        $this->_ptry++;
        $pay_checked = $this->_checkPayTr($answer);
        $limit = $answer['payment']['limit'];
        if($limit && $limit < $this->_tr['price']) {
            $this->setLimit($limit);
            if(!$this->ignoreLimit) {
                $this->reqConfirm = true;
                return $this->error("������������� ����� ��������� ������� ����� ��� ������� ��������: <b>{$limit} WMR</b>. ����� ��������� ����� �������.<br />" . 
                                    '������ ����� ���������� �����: <a href="http://www.guarantee.ru/services/users/addfunds" target="_blank">http://www.guarantee.ru/services/users/addfunds</a><br />'
                                    );
            }
        }
        
        if($pay_checked) {
            $this->_tr['cheque'] = $this->_tr['cheque'] ? $this->_tr['cheque'] : $this->_tr['id'];
            $this->_tr['kiosk_id'] = $this->_tr['kiosk_id'] ? $this->_tr['kiosk_id'] : $this->_tr['id'];
            $res = $this->_wmxi->UKG2(
              $this->_tr['id'], self::TEST_MODE, $this->_tr['name'], $this->_tr['passport_serie'],
              $this->_tr['passport_number'], $this->_tr['passport_date'], $this->_tr['purse'], $this->_tr['price'],
              $this->_tr['cheque'], $this->_tr['req_date'], $this->_tr['kiosk_id'], $this->_tr['phone']
            );
            if($res->ErrorCode()) {
                $this->error($res);
            }
            $answer = $res->toArray();
        }
        if($answer) {
            if($answer['payment']) {
                foreach($answer['payment'] as $f=>$v) {
                    $this->_tr[$f] = iconv('UTF-8', 'CP1251//TRANSLIT', $v);
                }
            }
            $this->_tr['retval'] = $answer['retval'];
            if($this->_commitTr()) {
                // $this->_analyzeTr();
            }
        }

        return false;
    }
    
    /**
     * ������ ������ �������� � �������� ��������� �������.
     * ����� ������� ��� � ��: ��� ���������� ��������� ������� ����� ��������� �������, ��������� � ��. �������. ����� ������������� ��������� ����� commitTr().
     * �� ���� ����� �� ��������� -- �� ���� �������� ���� �������.
     * 
     * @return bool true - �����, false - ������
     */
    private function _analyzeTr() {
        if(!$this->_tr)
            return false;
        
        // ���� ��� ���������, �� �������.
        if($this->_remAmt() <= 0)
            return true;

        // ���� ��� ���-�� ��������� �� ������� ��������, �� �� ����� ����� �������, �� �������.
        if($this->_remAmt() < $this->_pmt['in_amt'] && $this->_pdata['purse'] != $this->_tr['purse'])
            return $this->error("������� ����� �������� {$this->_pdata['purse']} ������������ �� ��������� � ���, �� ������� ��� ���� ����������� �� ������ �������: {$this->_tr['purse']}.<br/>���������� ��������� �������� � ���������� � ���. �����.");

        if(!$this->_ptry) {
            // ������ ���� �� �������� ��� �����. ������ ���� ���������:
            // �) ���� ��� ����� ������ ������ �� �������;
            // �) ��������� ���������� �� ���� ������������� (dateupd is null), �������� �� ���������;
            // �) �������������, �� ��� �� ��� ���������. ����� ������� ����� � �����������.
            return $this->_payTr( $this->_isTrPerformed() );
        }
        
    }
    
        
    /**
     * ��������� ����������
     * 
     * @return bool true - �����, false - ������
     */
    private function _commitTr() {
        if($this->_isTrPerformed()) {
            $this->_performedAmt += $this->_tr['price'];
        }
        
        $this->_tr = $this->DB->row('
           UPDATE wm_trs
              SET retval = ?i, wmtranid = ?, dateupd = ?::timestamp without time zone, rest = ?f,
                  req_cnt = req_cnt + 1, req_date = now(), price = ?f, purse = ?,
                  name = ?, passport_serie = ?, passport_number = ?, passport_date = ?::date,
                  cheque = ?, kiosk_id = ?, phone = ?
            WHERE id = ?i
              AND dateupd IS NULL
           RETURNING *
          ',
          $this->_tr['retval'], $this->_tr['wmtranid'], $this->_tr['dateupd'], $this->_tr['rest'],
          $this->_tr['price'], $this->_tr['purse'],
          $this->_tr['name'], $this->_tr['passport_serie'], $this->_tr['passport_number'], $this->_tr['passport_date'],
          $this->_tr['cheque'], $this->_tr['kiosk_id'], $this->_tr['phone'],
          $this->_tr['id']
        );

        if($this->_tr) {
            if($this->_isTrPerformed()) {
                $this->_performedAmtFixed += $this->_tr['price'];
            }
            return true;
        }

        return $this->error('�� ������� ������������� ����������! ���������� � ���. �����.');
    }

    /**
     * ��������� ��������� � ���
     *
     * @param string $str ���������
     * @param string $pfx �������
     */
    function log($str, $pfx = '') {
        if($this->logOn)
            echo $pfx . date('c') . "\t{$str}\r\n";
    }
    
    /**
     * ��������� ������.
     * ��������� ��������� �� ������ � ������ $this->errors � � ���
     * 
     * @param  string $err ��������� �� ������
     * @return bool false
     */
    function error($err, $encoding = NULL) {
        if($err instanceof WMXIResult) {
            $r = $err->toObject();
            $this->error("������: {$r->retval}");
            $this->error($r->retdesc, 'UTF-8');
            if($r->description) {
                $this->error($r->description, 'UTF-8');
            }
            return FALSE;
        }
    
        if($encoding) {
            $err = iconv($encoding, 'CP1251//IGNORE', $err);
        }
        
        $this->errors[] = $err;
        $this->log("������:\t{$err}");
        return FALSE;
    }
}
