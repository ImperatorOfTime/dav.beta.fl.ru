<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_answers.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/sms_services.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php";
/**
 *
 * ������ � ������� SMS ����� ������ IFree.
 *
 */
class ifreepay extends account
{
    
	/**
	 * ������ ��������� ���� ��� �������� ����, ��� ������ ������ �� ifree
	 *
	 */
	const SECRETKEY      = IFREE_KEY;
	/**
	 * ������ ��������� ���� ��� ������
	 *
	 */
    const DEBUGSECRETKEY = IFREE_DEBUG_KEY;
	/**
	 * ID ������� ������ ����� SMS
	 *
	 */
	const PAYMENT_SYS    = 7;

	
    /**
	 * ���������� �� ifree ������
	 *
	 * @var string
	 */
	private $_request;
    /**
     * ��� ������������� �������
     *
     * @var integer
     */
    private $_type;
    /**
     * ������������ ������� �������� ������
     *
     * @var object
     */
    private $_user;
    /**
     * ��� ������� ������� �������� �� op_codes
     *
     * @var integer
     */
    private $_opcode;
    /**
	 * �������, ������� ��������� ������������ � SMS ��� ������ ����� ��������
	 *
	 * @var string
	 */
	private $_smsPrefix = 'free';
	/**
	 * ������ ������� - ��� ������������� �������, ������� ��������� ������������ � SMS
	 * �������� - ��� ������ � ������� op_codes
	 *
	 * @var array
	 */
	private $_opcodes = array(1=>12, 2=>71); // �������� ������� ������� �� �������: 3=>62
	/**
	 * SMS ����� ���������� �������������.
	 *
	 * @var string
	 */
	private $_smsDecoded = '';
	/**
	 * ������ � ������ �� ������������ ������.
	 * @see sms_services::tariffs
	 *
	 * @var array
	 */
	private $_tariff = array();
	/**
	 * ������ �� �������� ������ �� ifree
	 *
	 * @var boolean
	 */
	private $_isValidated = false;

 /**
  * �� ������������� ��������. ������������ ��� ����������
  * 
  * @var string 
  */
 private $_oplock;

    /**
	 * �����������. ��������� ��� ����������� ���������� �� �������� ��������� ������ � ������ ��������.
	 * 
	 * @param   string   $request          ��������� �� ifree ������
	 * @param   boolean  $validate         ��������� ��������� ������?
	 * @param   boolean  $processRequest   ���������� ������, ���� ������ ������ ��������?
	 */
	function __construct($request, $validate = false, $processRequest = false)
    {
        $this->_request = $request;
        if($validate)
            $this->validate();
        if($processRequest)
            $this->processRequest();
    }


    /**
	 * ��������� �� ������������ ������ �� ifree � ��������� �������� ���������� �� ���� �������.
	 * � ������ ������ ������ ������� �����������.
	 */
	public function validate()
    {
        // ��������� #0019358
        $this->_errorif(true, false, '������ ����������.');
        
        if(isset($this->_request['test']))
            $this->_response( !trim($this->_request['test']) ? 'OK' : $this->_request['test']);
            
        $this->_errorif(!$this->_request['evtId'], '�������� ������.');

        $add_value = "";
        if($this->_request['retry'])
            $add_value = $this->_request['retry'];
        if($this->_request['debug'])
            $add_value .= $this->_request['debug'].self::DEBUGSECRETKEY;
        
        $valid = md5($this->_request['serviceNumber'].$this->_request['smsText'].$this->_request['country'].$this->_request['abonentId'].self::SECRETKEY.$this->_request['now'].$add_value);
        $this->_errorif(strcasecmp($valid, $this->_request['md5key']) != 0, "�������� ������.", "������������ md5key.");

        $this->_smsDecoded = base64_decode($this->_request['smsText']);

        list($pfx, $this->_type, $login) = preg_split('/[\s+]+/', $this->_smsDecoded);

        $this->_errorif(strtolower($pfx) != $this->_smsPrefix, "�������� ������ �������.");
        $this->_errorif(!$this->_type, "�� ������ ��� ������.");
        $this->_errorif(!($this->_opcode = $this->_opcodes[$this->_type]), "��� ������ �� ������.");
        $this->_tariff = sms_services::checkTariff($this->_type, $this->_request['serviceNumber'], $this->_request['country'], $err);
        $this->_errorif($err == 1, "��� ������ �� ������.");
        $this->_errorif($err == 2, "�������� ��������� �����.");
        $this->_errorif($err == 3, "��������� ������.", "������ �������� �� ����������.");
        $this->_errorif(!$login, "�� ������ ����� ������������.");
        $this->_user = new users();
        $this->_user->GetUser($login);
        $this->_errorif(!$this->_user->uid, "������������ � ������� {$login} �� ������.");
    
        $this->_isValidated = true;
    }

	/**
	 * ������ ���������� ������� � ����� ������������ �� ������ ��� ������.
	 */
    public function processRequest()
    {
        if(!$this->_isValidated)
            $this->validate();
        
        // ��������� �������� ������� � ������ ��., ���� ������� �������� �� ���������� (��. self::_response()) 
        $mcache = new memBuff();
        $mkey = 'ifreepay.evtId'.$this->_request['evtId'];
        if ($mcache->get($mkey)) {
            $this->_errorif(TRUE, '���������� ������ � �������� ���������.');
        }
        $mcache->set($mkey, 1, 60);
        $this->_oplock = $mkey;
        
        $op_id = 0;
        $dup = 0;
        $profit = floatval($this->_request['profit']);
        $currency_str = trim(strtoupper($this->_request['profitCurrency']));
        // ��������! ������ ��� ������ ����� �������� ��������, ��������� � account::getSmsInfo() � sms_service::checkEvtId().
        $descr = "SMS #{$this->_request['evtId']} � ������ {$this->_request['phone']} ({$this->_request['country']})"
               . " �� ����� {$this->_request['serviceNumber']}, ID �������� {$this->_request['abonentId']},"
               . " �������� {$this->_request['operator']}, �����: {$this->_smsDecoded}, ��������� {$this->_request['now']},"
               . " ������ {$profit} {$currency_str},"
               . " ����� �������: ".intval($this->_request['retry']);
               
        // ��� ��������� ��������� �������� (� ������ ����� �� ����� �� ������).
        if(intval($this->_request['retry']) > 0) {
            $dup = sms_services::checkEvtId($this->_request['evtId'], $op_id);
        }
               
        switch($this->_type) {
            case 1:
                if (!$dup && $operator != 'i-Free') {
                    $this->GetInfo($this->_user->uid);
                    $this->_errorif(!$this->id, '���� ������������ �� ������.');
                    $error = $this->deposit($op_id, $this->id, $this->_tariff['fm_sum'], $descr, self::PAYMENT_SYS, $this->_tariff['usd_sum'], $this->_opcode);
                    $this->_errorif(!!$error, $error);
                }
                $res_text = "��� ���� �������� �� {$this->_tariff['fm_sum']} FM";
            case 2:
                $new_password = users::ResetPasswordSMS($this->_user->uid,$this->_request['phone']);
                $this->_errorif(!$new_password, "�������� ����� ��� ������� �� �������� � ��������.");
                if (!$dup) {
                    $this->_errorif(!($tr_id = $this->start_transaction($this->_user->uid)), "������ ��� ���������� �������� �� �����.");
                    $this->_errorif($this->BuyFromSMS($op_id, $tr_id, $this->_opcode, $this->_user->uid, $descr, '', $this->_tariff['usd_sum'], 1, self::PAYMENT_SYS), "������ ��� ���������� �������� ��������.");
                }
                $res_text = "��� ����� ������: {$new_password}";
            case 3:
                if (!$dup) {
                    $answers = new projects_offers_answers;
                    $this->_errorif(!$answers->AddPayAnswers($this->_user->uid, 1), "������ ���������� ������.");
                    $this->_errorif(!($tr_id = $this->start_transaction($this->_user->uid)), "������ ��� ���������� �������� �� �����.");
                    $this->_errorif($this->BuyFromSMS($op_id, $tr_id, $this->_opcode, $this->_user->uid, $descr, '', $this->_tariff['usd_sum'], 1, self::PAYMENT_SYS), "������ ��� ���������� �������� ��������.");
                }
                $res_text = '������� �� �������. ������ �� ������ �������� �� ������.';
            default:
                $this->_errorif(true, "��� ������ �� ������.");
        }
        
        if(!$dup || $dup == sms_services::DUP_OP_NOTSAVED) {
            $sms_opid = sms_services::saveEvtId($op_id, $profit, $currency_str, $this->_request['evtId']);
        }
        
        $this->_response($res_text);
    }


    /**
	 * ��������� ������
	 * @param   boolean   $assert    ���� ������. ���� TRUE, �� ��������� ������ ������� ifree � ������ �����������. ���� FALSE - ������ �� ������.
	 * @param   string    $userErr   ��������� �� ������, ������� ������������ � ���� SMS ������������. ���� ��������� �� �������, �� ������ �� ������������.
	 * @param   string    $ifreeErr  ��������� �� ������, ������� ������������ ������� ifree. ���� ��������� �� �������, �� ������������ $userErr
	 */
	private function _errorif($assert, $userErr, $ifreeErr = NULL)
    {
        if(!$assert)
            return;

        if($this->_oplock) {
            $mcache = new memBuff();
            $mcache->delete($this->_oplock);
        }
        
        if(!$ifreeErr)
            $ifreeErr = $userErr;

        $response = '<Response><ErrorText><![CDATA['.iconv('windows-1251', 'UTF-8', 'Free-lance.ru. '.$ifreeErr).']]></ErrorText>';
        if($userErr)
          $response .= '<SmsText><![CDATA['.iconv('windows-1251', 'UTF-8', 'Free-lance.ru. '.$userErr).']]></SmsText>';
        $response .= '</Response>';

        die($response);
    }


    /**
	 * �������� ����� ������� ifree, ������� � ���� ������� ���������� �� � ���� SMS ��������, � ��������� ������.
	 * @param   string   $sms   ����� ���������
	 */
	private function _response($sms)
    {

        if($this->_oplock) {
            $mcache = new memBuff();
            $mcache->delete($this->_oplock);
        }
        
        die('<Response><SmsText><![CDATA['.iconv('windows-1251', 'UTF-8', 'Free-lance.ru. '.$sms).']]></SmsText></Response>');
    }
}
?>
