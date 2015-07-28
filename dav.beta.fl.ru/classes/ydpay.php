<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");

/**
 * ���������� ���� ��� ������ � ������� ������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");

/**
 * ����� ��� ������ � ������� ����� ������ ������
 *
 */
class ydpay extends account
{
	
    const SHOP_DEPOSIT     = 4551;
    const SHOP_SBR_RESERVE = 12445;
    
    // IP, � ������� ��������� ��������� �������� �������.
    protected $_allowed_ips = array('77.75.157.168', '77.75.157.169', '77.75.159.166', '77.75.159.170', '77.75.159.196');
    
	/**
	 * �� �������� 
	 *
	 * @var integer
	 */
	public $shopid = array(self::SHOP_DEPOSIT, self::SHOP_SBR_RESERVE);
	
	/**
	 * ���� ������
	 *
	 * @var integer
	 */
	public $key = YD_KEY;
	
	/**
	 * ������ ������
	 *
	 * @var string
	 */
	public $exchR = EXCH_YM;
	
	/**
	 * �� ����� ��� ������
	 *
	 * @var integer
	 */
	public $curid = 643;
	
	/**
	 * �� �����
	 *
	 * @var integer
	 */
	public $bank = 1001;
	
	/**
	 * �������� ������ �� ������������
	 *
	 * @param string $str	������ �������� ��������
	 * @return integer		id ���������� ��������, false ���� �������� �� �������
	 */
	function checkDups($str){
	    global $DB;
	    
		$sql = "SELECT id FROM account_operations WHERE descr = ?";
		$out = $DB->val($sql, $str);
		if($out !== null) return $out;
		return false;
	}
	
	/**
	 * �������� �������� ������
	 *
	 * @param integer $shopid     		�� ��������
	 * @param integer $billing_no 		����� ��������
	 * @param integer $ammount    		����� ������
	 * @param string  $operation_type 	��� ��������
	 * @param integer $operation_id   	�� ��������  (op_codes)
	 * @return string ��������� �� ������
	 */
	function prepare($shopid, $billing_no, $ammount, $operation_type, $operation_id){
		if (!in_array($shopid, $this->shopid)) $error = '�������� �������!';
		if (!$this->is_dep_exists($billing_no)) $error = '�������� ���� �� �����!';
		return $error;
	}
	
	/**
	 * �������� � �������� ��������
	 *
	 * @param integer $shopid       	�� ��������
	 * @param integer $ammount      	����� �������� 
	 * @param integer $orderIsPaid  	�������� ��� ���
	 * @param integer $orderNumber  	����� ������
	 * @param integer $billing_no   	����� ��������
	 * @param integer $hash         	��� ������ (�� ��� �� ������ ������� � ������� �� ������ ������ ������ ��������� � ���� �����)
	 * @param integer $fromcode     	������� � �������� ��������� ������
	 * @param integer $paymentDateTime  ���� ������
	 * @param string  $operation_type   ��� �������� (��. � �������)
	 * @param integer $operation_id     �� �������� (op_codes)
	 * @return string ��������� �� ������
	 */
	function checkdeposit($shopid, $ammount, $orderIsPaid,
        $orderNumber, $billing_no, $hash, $fromcode, $paymentDateTime, $operation_type, $operation_id)
    {
        if (floatval($ammount) <= 0)
            return '�������� �����!';
            
        $hash_str = $orderIsPaid . ';' . $ammount . ';' . $this->curid . ';'
                  . $this->bank . ';' . $shopid . ';' . $orderNumber . ';' . $billing_no . ';' . $this->key;
                  
        if (strtoupper(md5($hash_str)) != $hash)
            return '�������� ���!';
        
        $op_id = 0;
        $descr = "�� � �������� $fromcode ����� - $ammount, ��������� $paymentDateTime, ����� ������� - $orderNumber";
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
        
        if($operation_type == sbr::OP_RESERVE) { // ������ �������� ����� ������������.
            $shopid = ydpay::SHOP_SBR_RESERVE;
        }
        
        switch ($shopid) {
            case ydpay::SHOP_SBR_RESERVE : // ������ ����� �� ��� (�����)
                $op_code = sbr::OP_RESERVE;
                $amm = 0;
                $descr .= " ��� #".$operation_id;
                break;
            case ydpay::SHOP_DEPOSIT : // ������� ����� �� ������ ����
                $op_code = 12;
                $amm = $ammount/$this->exchR;
                break;
            default :
                return '�������� �������!';
        }
        
        $dups = $this->checkDups($descr);
        if (!$dups) {
            $error = $this->deposit($op_id, $billing_no, $amm, $descr, 3, $ammount, $op_code, $operation_id);
        }
        
        return $error;
    }
    
    
    /**
     * �������� � ������������� �������, �������� ��������
     * 
     * @global DB $DB
     * @param type $request     ������ � ������� ������� (����� ���� $_POST)
     * @return type             ������, ���� ������, ����� NULL
     */
    function process_payment($request) {
        global $DB;
        
        $action = $request['action'];
        $ip = getRemoteIp();
        
        if (!in_array($ip, $this->_allowed_ips)) {
            return "������������� IP: {$ip}";
        }
        
        if (!in_array($action, array('Check', 'PaymentSuccess'))) {
            return '������������ ������';
        }

        $shopid = $request['shopId'];
        $ammount = $request['orderSumAmount'];
        $orderIsPaid = $request['orderIsPaid'];
        $orderNumber = $request['invoiceId'];
        $billing_no = $request['customerNumber'];
        $hash = $request['md5'];
        $fromcode = $request['paymentPayerCode'];
        $paymentDateTime = $request['paymentDateTime'];
        $orderCreatedDatetime = $request['orderCreatedDatetime'];
        $operation_type = $request['OPERATION_TYPE'];
        $operation_id = $request['OPERATION_ID'];

        if (floatval($ammount) <= 0)
            return '�������� �����!';

        $hash_str = $orderIsPaid . ';' . $ammount . ';' . $this->curid . ';'
            . $this->bank . ';' . $shopid . ';' . $orderNumber . ';' . $billing_no . ';' . $this->key;

        var_dump(strtoupper(md5($hash_str)));
        if (strtoupper(md5($hash_str)) != $hash)
            return '�������� ���!';
        
        $op_id = 0; 
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
        if($operation_type == sbr::OP_RESERVE) { // ������ �������� ����� ������������.
            $shopid = ydpay::SHOP_SBR_RESERVE;
        }
        $op_descr = '';
        switch ($shopid) {
            case ydpay::SHOP_SBR_RESERVE : // ������ ����� �� ��� (�����)
                $op_code = sbr::OP_RESERVE;
                $amm = 0;
                $op_descr = " ��� #".$operation_id;
                break;
            case ydpay::SHOP_DEPOSIT : // ������� ����� �� ������ ����
                $op_code = 12;
                $amm = $ammount;
                break;
            default :
                return '�������� �������!';
        }
        
        if ($action == 'Check') {
            
            $descr = "�� � �������� $fromcode ����� - $ammount, ����� ������� - $orderNumber";
            $descr .= $op_descr;
            
            $dups = $DB->val('SELECT id FROM account_operations_yd WHERE descr = ?', $descr);
            if (!$dups) {
                $op_id = $DB->insert('account_operations_yd', array(
                    'billing_id'  => $billing_no,
                    'op_date'     => $orderCreatedDatetime,
                    'op_code'     => $op_code,
                    'ammount'     => $amm,
                    'trs_sum'     => $ammount,
                    'descr'       => $descr,
                    'invoice_id'  => $orderNumber,
                    ), 'id');

                $error = $DB->error;
            }
        } elseif ($action == 'PaymentSuccess') {
            $descr = "�� � �������� $fromcode ����� - $ammount, ��������� $paymentDateTime, ����� ������� - $orderNumber";
            $descr .= $op_descr;
        
            $tmp_payment = $DB->row('SELECT * FROM account_operations_yd WHERE invoice_id = ?', $orderNumber);
            if (!$tmp_payment) {
                return '������ �� ������';
            }
            
            $dups = $this->checkDups($descr);
            if ($dups) {
                return;
            }
            
            $error = $this->deposit($op_id, $billing_no, $amm, $descr, 3, $ammount, $op_code, $operation_id);

            if (!$error) {
                $DB->query('DELETE FROM account_operations_yd WHERE invoice_id = ?', $orderNumber);
            }
        } else {
            $error = '������������ ������';
        }

        return $error;
    }

    

}
