<?
/**
 * ���������� ���� ��� ������ � ���������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
/**
 * ���������� ���� ��� ������ � �������������
 */
require_once ($_SERVER['DOCUMENT_ROOT']."/classes/users.php");

/**
 * ����� ��� ������ � ���������� ����� ����� ����
 *
 */
class osmppay extends account
{
	/**
	 * ���� ������ �� ��������� � FM.
	 *
	 * @var string
	 */
	public $exch = EXCH_OSMP;
	
	/**
	 * �������� ������ ������
	 *
	 * @param integer $err_code      ���������� ��� ������ 
	 * @param string  $login         ����� �����������
	 * @param integer $operation_id  �� ��������
	 * @param integer $ammount       ����� ������
	 * @return string ��������� �� ������
	 */
	function prepare(&$err_code, $login, $operation_id, $ammount){
		if (floatval($ammount) <= 0) { $err_code = 241; return "�������� �����!";}
		if (!$operation_id) {$err_code = 300; return "�������� ������������� ��������!";}
	    if (!preg_match("/^[a-zA-Z0-9]+[-a-zA-Z0-9_]{2,}$/", $login)) {$err_code = 4; return "�������� ����� �� �����!";}
		$user = new users();
		$uid = $user->GetUid($error, $login);
		if (!$uid) {$err_code = 5; $error = "�������� ����� �� �����!";}
		elseif (!$this->GetInfo($uid)) {$err_code = 79; $error = "���� �������� �� �������.";}
		return $error;
	}
	
	/**
	 * �������� �������� � ���������� ����� �� ����.
	 *
	 * @param integer $op_id        ���������� ��� ��������
	 * @param integer $err_code     ���������� ��� ������
	 * @param integer $ammount      ���������� ����� ��������
	 * @param string $login         ����� �����������
	 * @param integer $operation_id �� ��������
	 * @param string $op_date       ���� ��������
	 * @return string ��������� �� ������
	 */
	function checkdeposit(&$op_id, &$err_code, &$ammount, $login, $operation_id, $op_date){
		
		if (floatval($ammount) <= 0) { $err_code = 241; return "�������� �����!";}
		
		if (!$operation_id) return "�������� ������������� ��������!";
		
		if (!$op_date) { $err_code = 300; return "�������� ���� ��������!";}

		$date_arr=strptime($op_date,"%Y%m%d%H%M%S");
		$date = ($date_arr['tm_year']+1900)."-".($date_arr['tm_mon']+1)."-".$date_arr['tm_mday']." ".$date_arr['tm_hour'].
			":".$date_arr['tm_min'].":".$date_arr['tm_sec'];
		if (strtotime($date) == -1) { $err_code = 300; return "�������� ���� ��������!";}
		
		$user = new users();
		$uid = $user->GetUid($error, $login);
		if (!$uid) {$err_code = 5; $error = "�������� ���� �� �����!";}
		elseif (!$this->GetInfo($uid)) {$err_code = 79; $error = "���� �������� �� �������.";}
		
		$descr = "���� �� $date ����� - $ammount, ����� ������� ���� $operation_id";
		
		$op_id = 0;
		$op_code = 12;
		$amm = $ammount;
		
		$old_payment = $this->SearchPaymentByDescr("����� ������� ���� $operation_id");
		if ($old_payment){
			$op_id = $old_payment['id'];
			$ammount = $old_payment['trs_sum'];							
		} else {
			$error = $this->deposit($op_id, $this->id, $amm, $descr, 8, $ammount, $op_code, 0, $date);
			if ($error) {
			    $error = "���������� ��������� ������. ��������� �����";
			    $err_code = 1;
			}
		}
		return $error;
	}
	
}
?>