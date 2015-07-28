<?
/**
 * ���������� ����� ��� ������ � ��������� ������������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
/**
 * ���������� ���� � ������� ������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");

/**
 * ����� ��� ���������� ����� ����� WebMoney
 *
 * @see /income/wm.php
 */
class wmpay extends account
{
	
	/**
	 * �������� �� ������� ��������� ����
	 *
	 * @var array
	 */
	public $wmzr = array('Z801604194058','R199396491834','R109922555324');
	
	/**
	 * ���� ������
	 * 
	 * @link /classes/payment_keys.php
	 * @var string
	 */
	public $key  = WM_KEY;
	
	/**
	 * ������ ������ �� � �������
	 *
	 * @link /classes/payment_keys.php
	 * @var string
	 */
	public $exchR = EXCH_WMR;
	
	/**
	 * ������ ������ �� Z �������
	 *
	 * @link /classes/payment_keys.php
	 * @var string
	 */
	public $exchZ = EXCH_WMZ;
	
	/**
	 * ��������� ������� �� ������
	 *
	 * @param string  $wmzr           ����� ��������
	 * @param integer $billing_no     ����� ��������
	 * @param integer $ammount        ����� ��������
	 * @param integer $operation_type ��� �������� (���. op_codes)
	 * @param integer $operation_id   �� �������� 
	 * @return string ��������� �� ������
	 */
	function prepare($wmzr, $billing_no, $ammount, $operation_type, $operation_id){
		if (!in_array($wmzr, $this->wmzr)) $error = "�������� �������!";
		if (!$this->is_dep_exists($billing_no)) $error = "�������� ���� �� �����!";
		switch ($operation_type){
			case "1":		//������ ����� �� ���
				
				break;
			default:		//������� ����� �� ������ ����
				
		}
		return $error;
	}
	
	/**
	 * �������� ��������
	 *
	 * @see /income/wm.php 
	 * @param string  $wmzr        ����� �������� 
	 * @param inetger $ammount     ����� ��������
	 * @param inetger $payment_num ����� ������
	 * @param inetger $wm_invs_no  ����� ��� 
	 * @param inetger $wm_trans_no ����� ���������� ���
	 * @param inetger $payer_wmzr  ����� �������� �����������
	 * @param inetger $payer_wmid  ����� ���� ����������� 
	 * @param date    $wm_date     ���� ������
	 * @param char    $hash        ���
	 * @param inetger $mode        ����� ������
	 * @param inetger $billing_no  ����� ��������
	 * @param inetger $operation_type ��� �������� (�� ��������� � �������)
	 * @param inetger $operation_id   �� ��������
	 * @return string ��������� �� ������
	 */
	function checkdeposit($wmzr, $ammount, $payment_num,
			$wm_invs_no, $wm_trans_no, $payer_wmzr, $payer_wmid, $wm_date,
			$hash, $mode, $billing_no, $operation_type, $operation_id){
		
		if (!in_array($wmzr, $this->wmzr)) return "�������� ������� ��������!";
		
		if (floatval($ammount) <= 0) return "�������� �����!";
		
		$hash_str = $wmzr . $ammount . $payment_num . $mode . $wm_invs_no . $wm_trans_no . $wm_date
					 . $this->key . $payer_wmzr . $payer_wmid;
		if (strtoupper(md5($hash_str)) != $hash) return "�������� ���!";
		
		$descr = "WM #$payment_num �� ������� $wmzr � �������� $payer_wmzr (wmid:$payer_wmid) ����� - $ammount,";
		$descr .= " ��������� $wm_date, ����� ������� - $payment_num, ����� ������� - $wm_trans_no";
		
		$op_id = 0;
		switch ($operation_type){
			case "1":		//������ ����� �� ���
				$op_code = 36;
				$amm = 0;
				$descr .= " ��� #".$operation_id;
				break;
			case sbr::OP_RESERVE: // ������ ����� �� ��� (�����)
				$op_code = sbr::OP_RESERVE;
				$amm = 0;
				$descr .= " ��� #".$operation_id;
				break;
			default:		//������� ����� �� ������ ����
				$op_code = 12;
				if (substr($wmzr,0,1) == "R") {$amm = $ammount;}
				if (substr($wmzr,0,1) == "Z") {$amm = $ammount * $this->exchZ;}
		}

		if (substr($wmzr,0,1) == "R") {
			if ($wmzr == "R109922555324") $ps = 10; else $ps = 2;}
		if (substr($wmzr,0,1) == "Z") {$ps = 1;}
		
		$error = $this->deposit($op_id, $billing_no, $amm, $descr, $ps, $ammount, $op_code, $operation_id);
		return $error;
	}
	
}
?>