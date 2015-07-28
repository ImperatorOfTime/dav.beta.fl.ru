<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");

/**
 * ����� ������� ��� ����
 */
if(!defined("SMS_GATE_DEBUG")) {
    define("SMS_GATE_DEBUG", false);
}

/**
 * ������ �����������
 */
if(!defined("SMS_GATE_AUTH")) {
    define("SMS_GATE_AUTH", 'freelance:w83457hhn');
}

/**
 * ����� ��� ������ � ��� ������
 */
class sms_gate
{
    /**
     * �� ����� ���� ����������
     */
    const STATUS_REJECTED = 'REJECTED';
    
    /**
     * �������� ���������
     */
    const STATUS_SUBMIT_ACKNOWLEGED = 'SUBMIT ACKNOWLEGED';
    
    /**
     * ���������� ��������
     */
    const STATUS_DELIVERED = 'DELIVERED';
    
    /**
     * �� ����� ���� ���������� ��������
     */
    const STATUS_UNDELIVERED = 'UNDELIVERED';
    
    /**
     * �������� ����� � �������� ������������ ���������
     */
    const ISNN = 'Free-lance';
    
    /**
     * ����� � ������, c �������� ������������ ���������
     */
    const ISNN_NUMERIC = 79010101000;
    
    /**
     * ������� ����� ��������� ��������� ���������
     */
    const TIMEOUT_SEND = '1 min';
    
    /**
     * ������ ���� �������������
     */
    const LENGTH_CODE = 4;
    
    /**
     * ������� ��� ����� ��������� � ����� �� ���� ����� (0024839)
     */
    const SMS_ON_NUMBER_PER_24_HOURS = 50;
    
    /**
     * ��������� � ���, ��� ���������� ��� �� ����� ������ ������ (0024839)
     */
    const LIMIT_EXCEED_LINK_TEXT = "� ���������, �������� ����� SMS � ����� ��������";
    /**
     * ����� ������� ��� ������� ���������
     * 
     * @var string
     */
    protected $_request_url = 'http://81.177.1.226';
    
    /**
     * ����
     * @var integer 
     */
    protected $_request_port = 2780;
    
    /**
     * ���������� � ���� ������
     * @var DB
     */
    protected $_db;
    
    /**
     * ����� ��������� ������ 
     * 
     * @var string 
     */
    protected $_error;
    
    /**
     * ����� ��� ������ � ������
     * 
     * @var Log
     */
    protected $_log;
    
    /**
     * ��� ������ ������� (200,400,401,404,500)
     * 
     * @var integer 
     */
    protected $_http_code;
    
    /**
     * ��������� ������� ��������
     * 
     * @var integer 
     */
    protected $_msisdn;
    
    /**
     * ��������� �� ���������� ���������� ���������
     * @var string 
     */
    protected  $_limit_message;
    
    /**
     * ���������� ��� ������������ �� ����� ���������
     * @var int 
     */
    protected  $_count_sent_message;

    /**
     * ��� ��������� (��������� ��������)
     */
    const TYPE_ACTIVATE = 1;
    
    /**
     * ��� ��������� (���� � �������)
     */
    const TYPE_AUTH     = 2;
    
    /**
     * ��� ��������� (������������� ������)
     */
    const TYPE_PASS     = 3;

    /**
     * ��� ��������� (�������� ������)
     */
    const TYPE_CLOSE_SBR = 4;
    
    
    /**
     * ������ ��������� ������ �� ���� ���������
     * %s -- ���������� �� ���(������)
     * 
     * @var array
     */
    public $text_messages = array(
        self::TYPE_ACTIVATE  => '�����������, ��� ��� ��� �������: ������� ��� �� FL.ru - %s',
        self::TYPE_AUTH      => '��� ����� �� �������� ��������� ������� ��� �� Free-lance.ru - %s',
        self::TYPE_PASS      => '�������������� ������� � �������� �� FL.ru. ����� -LOGIN-, ����� ������ %s',
        self::TYPE_CLOSE_SBR => '���������� ����� ������ �� ����� FL.ru. ��� �������������: %s'
    );
    
    /**
     * �������������� �� � ����� ����
     */
    public function __construct($msisdn = false) {
        if (SMS_GATE_DEBUG) {
            $_host = !defined('IS_LOCAL') ? str_replace('http://', 'https://', $GLOBALS['host']) : $GLOBALS['host'];
            $this->_request_url = $_host . '/sms/sms_gate_server.php';
        }
        
        if($msisdn) $this->setCell($msisdn);
        
        $this->_db  = new DB('master');
        $this->_log = new Log("sms_gate/".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
    }
    
    /**
     * ������ ����� ��������
     * 
     * @param integer $msisdn
     */
    public function setCell($msisdn) {
        $this->_msisdn = str_replace('+', '', $msisdn);
    }
    
    /**
     * ����� ��������
     * 
     * @return integer
     */
    public function getCell() {
        return $this->_msisdn;
    }
    
    /**
     * ������������� ��������
     * 
     * @param string  $str         ������������� ��������
     * @param boolean $revert      �������� ���������
     * @return string
     */
    protected function _enc($str, $revert = false) {
        return ( $revert ? iconv('utf8', 'cp1251', $str) : iconv('cp1251', 'utf8', $str) );
    }
    
    /**
     * ��������� ����������� ���� ��� �������� ������ ��������
     * 
     * @param integer $length  ������ ����
     * @return integer
     */
    public function generateCode($length = self::LENGTH_CODE) {
        $min = intval('1' . str_repeat('0', $length-1));
        $max = intval(str_repeat('9', $length));
        $this->setAuthCode(rand($min, $max));
        return $this->getAuthCode();
    }
    
    /**
     * ������ ��� ������������� ��������
     * 
     * @param string|integer $code
     */
    public function setAuthCode($code) {
        $this->_code = $code;
    }
    
    /**
     * ���������� ��� ������������� ��������
     * @return integer
     */
    public function getAuthCode() {
        return $this->_code;
    }
    
    /**
     * ����� ����� ���������� ������� ����� ����� �������� ��������� ���������
     * 
     * @param type $date        ����
     * @param type $msisdn      ������� ��������
     * @return type
     */
    public function nextTimeSend($date = false) {
        if(!$date) {
            $sql = "SELECT date_send FROM sms_gate WHERE msisdn = ? ORDER by date_send DESC LIMIT 1";
            $date = $this->_db->val($sql, $this->getCell());
            if(!$date) return false;
        }
        $this->next_time_send =  strtotime($date . ' + ' . self::TIMEOUT_SEND);
        return $this->next_time_send;
    }
    
    /**
     * ���������� �� �������� ��������� �� ��������������� �����
     * 
     * @return array
     */
    public function getInfoSend() {
        return $this->_db->row("SELECT id, data, dlr_status, date_send, is_auth FROM sms_gate WHERE msisdn = ? AND user_id = ? ORDER by date_send DESC", $this->getCell(), $_SESSION['uid']);
    }
    
    /**
     * ��������� ����� �� ��������� ���������
     * 
     * @param string $date
     * @return boolean
     */
    public function isNextSend($date = false) {
        return (time() < $this->nextTimeSend($date));
    }
    
    /**
     * ���������� ��� ������
     * @return type
     */
    public function getHTTPCode() {
        return $this->_http_code;
    }
    
    public function getTextMessage($type, $code) {
        return sprintf($this->text_messages[$type], $code);
        //return iconv("cp1251", "utf-8", sprintf($this->text_messages[$type], $code));
    }
    /**
     * �������� ���� ��� ��������� ������ ��������
     * 
     * @return boolean
     */
    public function sendAuthCellCode($type = sms_gate::TYPE_ACTIVATE) {
        $info  = $this->getInfoSend();
        
        if($this->isNextSend($info['date_send'])) {
            return false;
        }
        $code    = $this->generateCode();    
        $text    = $this->getTextMessage($type, $code);
        $sms_id  = intval($this->sendSMS($text));
            
        if($this->_http_code == 200) {
            $data = array(
                'sms_id'     => $sms_id,
                'msisdn'     => $this->getCell(),
                'isnn'       => $this->getISNN(),
                'type'       => 'text',
                'data'       => $code,
                'user_id'    => $_SESSION['uid'],
                'dlr_status' => SMS_GATE_DEBUG ? self::STATUS_DELIVERED : null
            );
            
            if($info['id']> 0) {
                $data['date_send'] = 'NOW()';
                $this->_db->update('sms_gate', $data, "id = {$info['id']}");
            } else {
                $this->_db->insert('sms_gate', $data);
            }
            
            return $sms_id;
        }
        
        return false;
    }
    
    /**
     * ��������� ����, ��������� ��������
     * 
     * @param integer $id      �� ������
     * @param boolean $auth    ���� ��������� 
     * @return boolean
     */
    public function setIsAuth($id, $auth = false) {
        if(!$id) return false;
        return $this->_db->update('sms_gate', array('is_auth' => $auth), "id = {$id}");
    }
    
    /**
     * �������� ��������� ��������
     * 
     * @param string  $message  ����� ��������� ��������
     * @param string  $type     ��� ��������� ���������: text � ��������� ���������, push � wap-push ��������� � ������� <��������>;<������>
     * @param integer $sms_id   ������������� ��������� � ������� �������, �� ������� ������������ ����� (���� null �� ��������� �� �������� � �����)
     * @return boolean
     */
    public function sendSMS($message = "", $type= "text", $sms_id = null) {
        $message = $this->translit($message);
        $params = array(
            'sms_id'  => $sms_id,
            'msisdn'  => $this->getCell(),
            'isnn'    => $this->getISNN(),
            'type'    => $type,
            'data'    => $message
        );
        
        return $this->_send($params);
    }
    
    /**
     * �������� ������ ��������� ����� ���-����
     * 
     * @param type $params
     * @return boolean
     */
    protected function _send($params) {
        $ch = curl_init();
        
        if (is_array($params)) {
            foreach ($params as $k => $v) {
                if($v === null) continue;
                $params[$k] = $this->_enc($v);
            }
            $build_query = http_build_query($params);
        } else {
            ob_start();
            var_dump($params);
            $out = ob_get_clean();
            $this->_log->writeln($this->_enc($out, true));
            $this->_log->writeln('������ ���������� ��� �������');
            $this->_setError('������ ���������� ��� �������');
            return false;
        }
        
        if (!SMS_GATE_DEBUG) {
            curl_setopt($ch, CURLOPT_USERPWD, SMS_GATE_AUTH);
            curl_setopt($ch, CURLOPT_URL, ( is_release() ? $this->_request_url : "localhost" ) . "?" . $build_query );
            if($this->_request_port) curl_setopt($ch, CURLOPT_PORT, $this->_request_port);
        } else {
            if(defined('BASIC_AUTH')) {
                curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
            }
            curl_setopt($ch, CURLOPT_URL, $this->_request_url . "?" . $build_query);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $res = curl_exec($ch);
        $this->_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        ob_start();
        var_dump($this->_request_url . "?" . $build_query);
        var_dump($params);
        var_dump($res);
        var_dump($this->_http_code);
        $out = ob_get_clean();
        $this->_log->writeln($this->_enc($out, true));
        
        return $res;
    }
    
    /**
     * ��������� ������
     * 
     * @param type $msg ��������� ������
     */
    protected function _setError($msg) {
        $this->_error = $msg;
    }
    
    /**
     * ���������� ������
     * 
     * @return string
     */
    public function getError() {
        return $this->_error;
    }
    /**
     * ��������� ������ �� �������������� ��� ����������� ������������ ���������� ������
     * @param string $phone ����� �������� ��������
     * @param string $isnn  �������� �����, �� ������� �������� ������
     * @param string $data  ����� ��������� 
     * @param string $date_send  ����� �������� ��������� 
     * @param string $uid   ������������� ������������ �� users 
     */
    static public function saveSmsInfo($phone, $isnn, $data, $date_send, $uid) {
        global $DB;
        if ( strtolower( mb_detect_encoding($data, array("Windows-1251") ) )== "windows-1251") {
            $data = mb_convert_encoding($data, "UTF-8", "Windows-1251");
        }
        $DB->insert("sms_gate", array("msisdn"=>$phone, "isnn"=>$isn, "type"=>"text", "data"=>$data, "dlr_status" => "DELIVERED", "date_send" => $date_send, "user_id" => $uid, "is_auth" =>true ));
        $DB->insert("sbr_reqv", array("_1_mob_phone"=>$phone, "_2_mob_phone"=>$phone, "user_id"=>$uid, "is_activate_mob"=>true ));
    }
     /**
     * ���� �� ����� �������������� ������ ���� ������?
     * @param  string $phone ����� �������� ��������
     * @return bool   true ���� ����  � false ���� ��� 
     */
    public function phoneIsExistsAndVerify($phone) {
        global $DB;
        $phone = preg_replace("#[\D]#", '', $phone);
        if ( strlen($phone) ) {
            $val = $DB->val("SELECT id FROM sms_gate WHERE is_auth = TRUE AND msisdn = '{$phone}' LIMIT 1");
            if ($val) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * ��������� ����� ��������
     * ���� ��� ��������� �����, �� �������� ��������������
     * ��������������� ������ �������
     * @return string ������������������� ������
     */
    function translit ($str) {
        $tr = array(
            "�"=>"A","�"=>"B","�"=>"V","�"=>"G","�"=>"D",
            "�"=>"E","�"=>"E","�"=>"J","�"=>"Z","�"=>"I",
            "�"=>"Y","�"=>"K","�"=>"L","�"=>"M","�"=>"N",
            "�"=>"O","�"=>"P","�"=>"R","�"=>"S","�"=>"T",
            "�"=>"U","�"=>"F","�"=>"H","�"=>"TS","�"=>"CH",
            "�"=>"SH","�"=>"SCH","�"=>"","�"=>"YI","�"=>"",
            "�"=>"E","�"=>"YU","�"=>"YA","�"=>"a","�"=>"b",
            "�"=>"v","�"=>"g","�"=>"d","�"=>"e","�"=>"e",
            "�"=>"j","�"=>"z","�"=>"i","�"=>"y","�"=>"k",
            "�"=>"l","�"=>"m","�"=>"n","�"=>"o","�"=>"p",
            "�"=>"r","�"=>"s","�"=>"t","�"=>"u","�"=>"f",
            "�"=>"h","�"=>"ts","�"=>"ch","�"=>"sh","�"=>"sch",
            "�"=>"y","�"=>"yi","�"=>"","�"=>"e","�"=>"yu",
            "�"=>"ya",
        );

        $cell = $this->getCell();
        // ��� �������� ������ �������������� ���������
        if ($cell{0} != 7) {
            $str = strtr($str, $tr);
        }

        return $str;
    }
    
    /**
     * ���������� ����� ISNN ����� ��������
     * 
     * @return string|integer
     */
    public function getISNN() {
        switch(true) {
            // ��� ������������ (�������� Azercell)
            case ( strpos((string)$this->getCell(), '99451') === 0 ):
            case ( strpos((string)$this->getCell(), '99450') === 0 ):
            case ( strpos((string)$this->getCell(), '90') === 0 ): //#0024762
            case ( strpos((string)$this->getCell(), '373') === 0 ): // ��������
                $isnn = self::ISNN_NUMERIC;
                break;
            default:
                $isnn = self::ISNN;
                break;
        }
        return $isnn;
    }
}

/**
 * ����� ��� ��������� �������� ��������� �� �������� ���-�����
 */
class sms_gate_listener
{
    /**
     * ���������� � ���� ������
     * @var DB
     */
    private $_db;
    
    /**
     * ����� ��� ������ � ������
     * 
     * @var Log
     */
    private $_log;
    
    /**
     * �������������� �� � ���
     */
    public function __construct() {
        $this->_db  = new DB('master');
        $this->_log = new Log("sms_gate/listener-".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
    }
    
    /**
     * ������������ �������� �������
     * 
     * @param array  $request     ������ �������
     * @param string $path        ����� ��� ���� ���������
     */
    public function listener($request, $path) {
        $this->_request = $request;
        
        ob_start();
        var_dump($this->_request);
        $out = ob_get_clean();
        $this->_log->writeln($out);
        
        switch($path) {
            case 'sms': // ����� ��� ��������� �� ���������
                $this->_SMSListener();
                break;
            case 'dlr': // ����� ��� ������� � ��������
                $this->_DLRListener();
                break;
            default:
                $this->_log->writeln('HTTP/1.0 400 Bad Request');
                header('HTTP/1.0 400 Bad Request');
                break;
        }
        exit;
    }
    
    /**
     * ������������ ��������� �� �������� 
     * @todo �� ������ ������ � ������ ������� �� �� �����������
     * 
     * @return type
     */
    protected function _SMSListener() {
        return;
    }
    
    /**
     * ��������� ������� � ��������
     * 
     * @return boolean
     */
    protected function _DLRListener() {
        $this->_log->writeln('DLRListener');
        
        $sms_id = __paramValue('integer', $this->_request['sms_id']);
        $status = __paramValue('string', $this->_request['dlr_status']);
        if($sms_id <= 0) {
            $this->_log->writeln('HTTP/1.0 400 Bad Request');
            header('HTTP/1.0 400 Bad Request');
            return;
        }
        
        $update = array(
            'dlr_status' => $status
        );
        
        $ok = $this->_db->update('sms_gate', $update, "sms_id = {$sms_id}");
        
        if($ok) {
            $this->_log->writeln('HTTP/1.0 200 OK');
            header('HTTP/1.0 200 OK');
            return true;
        }
        $this->_log->writeln('HTTP/1.0 400 Bad Request');
        header('HTTP/1.0 400 Bad Request');
        return false;
    }
}

/**
 * ����� ��� �������� ������� �������� ���-�����
 */
class sms_gate_server
{
    /**
     * ����� ������� ��� ������� ���
     */
    protected $_request_url;
    
    /**
     * ���������� � ���� ������
     * @var DB
     */
    private $_db;
    
    /**
     * �������������� ��
     */
    public function __construct() {
        if (SMS_GATE_DEBUG) {
            $_host = !defined('IS_LOCAL') ? str_replace('http://', 'https://', $GLOBALS['host']) : $GLOBALS['host'];
            $this->_request_url = $_host;
        }
        $this->_db  = new DB('master');
    }
    
    /**
     * ������������ �������� �������
     * � ��������� ������ �������� ������ ���� 
     * 
     * @param type $request
     */
    public function listener($request) {
        if($request['msisdn'] == '') {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
        
        $insert = array(
            'msisdn'     => __paramValue('string', $request['msisdn']),
            'data'       => __paramValue('string', $request['data']),
            'dlr_status' => sms_gate::STATUS_DELIVERED
        );
        $sms_id = $this->_db->insert('sms_gate_server', $insert, 'id');
        
        echo $sms_id;
        
//        $params = array(
//            'sms_id'     => $sms_id,
//            'dlr_status' => $insert['dlr_status']
//        );
//        $this->report($params, 'dlr');
    }
    
    /**
     * �������� �������
     * 
     * @param array  $request ������ �������
     * @param string $type    ��� ������� (@see sms_gate_listener::listener())
     * @return integer ��� ������ �������
     */
    public function report($request, $type = 'sms') {
        $ch = curl_init();
        
        foreach ($request as $k => $v) {
            $request[$k] = iconv('cp1251', 'utf8', $v);
        }
        $build_query = http_build_query($request);
        
        if(defined('BASIC_AUTH')) {
            curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
        }
        curl_setopt($ch, CURLOPT_URL, $this->_request_url . "/" . $type . "/?" . $build_query);
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $res = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        return $http_code;
    }
}

?>