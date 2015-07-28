<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/card_account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
/**
 *
 * ����� ��� ������� FM � ������� ���������� ����.
 *
 */
class cardpay extends account {
	
	const MERCHANT_ID = '691486';
	const TESTMODE = 0;
	
	// ������ ��������.
	const URL_RESULTBYDATE = 'https://payments.paysecure.ru/resultbydate/resultbydate.cfm'; // ��� ������� �������� �� ������.
	const URL_ORDER        = 'https://payments100.paysecure.ru/pay/order.cfm'; // ��� ������� �������� �� ������ ������.
	
	// ���� ������ (��� ���).
    const ERR_MERCHANT_ID = 1;
    const ERR_HASH        = 2;
    const ERR_ORDERNUM    = 3;
    const ERR_DEPOSIT     = 4;
    
	/**
	 * ����� ��� ����������� � ASSIST
	 *
	 * @var string
	 */
	private $_login = 'freelance_sale';

	/**
	 * ������ ��� ����������� � ASSIST
	 * @var string
	 */
	private $_password = ASSIST_PASSWD;
	
	/**
	 * ���� ��� �������� ���� �������� �������� �� assist.
	 * @var string
	 */
	private $_secret = ASSIST_SECRET;
	
	/**
	 * ���
	 * @var log
	 */
	public $log;
	
	
	function __construct() {
	    $this->log = new log('assist/assist-%d%m%Y.log');
	    $this->log->linePrefix = '%d.%m.%Y %H:%M:%S : ';
	}

	/**
	 * ��������� �� assist ���������� �������, ���������� ����������.
	 *
	 * @param array $req   ������ $_POST � �������.
	 */
	function checkdeposit($req) {
	    $this->log->writeln('����������� �������.');
	    $this->log->writevar($req);
        if($req['merchant_id'] != self::MERCHANT_ID) {
            $this->fail(self::ERR_MERCHANT_ID);
        }
        $hash_x = $req['merchant_id'].$req['ordernumber'].$req['amount'].$req['currency'].$req['orderstate'];
        $hash = strtoupper(md5(strtoupper(md5($this->_secret).md5($hash_x))));
        if($hash != $req['checkvalue']) {
            $this->fail(self::ERR_HASH);
        }
	    
	    if($req['responsecode'] == 'AS000' && $req['orderstate'] == 'Approved') {
            $card_account = new card_account();
            $billing_no = $card_account->checkPayment($req['ordernumber']);
            if(!$billing_no) {
                $this->fail(self::ERR_ORDERNUM);
            }

            $amm   = $req['orderamount'];
            $descr = "CARD ����� ����� � ������� {$req['billnumber']} � ����� {$req['meantypename']} {$req['meannumber']} "
                   . "����� - {$req['orderamount']} {$req['ordercurrency']}, "
                   . "��������� {$req['packetdate']}, ����� ������� - {$req['ordernumber']}";
            if($error = $this->deposit($op_id, $billing_no, $amm, $descr, 6, $req['orderamount'])) {
                $this->fail(self::ERR_DEPOSIT, $error);
            }
	    }
	    $this->success($req['billnumber'], $req['packetdate']);
	}
	
	/**
	 * �������� ������� ��� ������� � assist �������� �� ������������ ������.
	 * @todo ����������� ��������� ��� ����� ������ ��������, ������ �������� � �.�.
	 *
	 * @return string
	 */
	function checkResultsByDate() {
	    $this->log->writeln('�������� ����������� ��������.');
        $card_account = new card_account();
        $req['Merchant_ID'] = self::MERCHANT_ID;
        $req['Login'] = $this->_login;
        $req['Password'] = $this->_password;
        $req['TestMode'] = self::TESTMODE;
        $req['Operationstate'] = 'S';
        echo $this->request($req, self::URL_RESULTBYDATE);
	}
	
	/**
	 * ��������� ������ � assist
	 *
	 * @param array $req   ��������� ������� [����:��������]
	 * @param string $url   ����� �������.
	 * @return string   �����.
	 */
	function request($req, $url) {
	    echo http_build_query($req, '', '&');
        $context = array (
            'http' => array (
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($req, '', '&')
        ) );
        return file_get_contents($url, false, stream_context_create($context));
	}
	
	/**
	 * ��������� ��������� ����� �� ����������� ������.
	 *
	 * @param integer $code   ���������� (���) ��� ������.
	 * @param string $msg   �������� ������ (���� ��� ���).
	 */
	function fail($code, $msg = NULL) {
	    switch($code) {
	        case self::ERR_MERCHANT_ID : $fc = 5; $sc = 100; break;
	        case self::ERR_HASH        : $fc = 9; $sc = 0; break;
	        case self::ERR_ORDERNUM    : $fc = 5; $sc = 107; break;
	        case self::ERR_DEPOSIT     : $fc = 2; $sc = 1; break;
	    }
	    
        $ret = '<?xml version="1.0" encoding="UTF-8"?>'
             . '<pushpaymentresult firstcode="' . $fc . '" secondcode="' . $sc . '" />';
        $this->log->writeln("������: code={$code} firstcode={$fc} secondcode={$sc} msg={$msg}");
        die($ret);
	}
	
	/**
	 * ��������� �������� ����� �� ������.
	 *
	 * @param integer $billnumber   ����� �������� � ������� Assist.
	 * @param string $packetdate   ���� �������� �� Assist.
	 */
	function success($billnumber, $packetdate) {
        $ret = '<?xml version="1.0" encoding="UTF-8"?>'
             . '<pushpaymentresult firstcode="0" secondcode="0">'
             . '<order>'
             . '<billnumber>'.$billnumber.'</billnumber>'
             . '<packetdate>'.$packetdate.'</packetdate>'		
             . '</order>'
             . '</pushpaymentresult>';
	    $this->log->writeln('��');
        die($ret);
	}
	
    function getSecret() {
        if(is_release()) return false;
        return $this->_secret;
    }
}
