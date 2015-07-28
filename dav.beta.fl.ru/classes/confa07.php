<?
/**
 * ���������� ���� � ��������� ���������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ������ � ������������ 07 ����
 *
 */
class confa07 
{
	/**
	 * �������� ������������ �� �����������
	 *
	 * @param string   $name    ��� ������������
	 * @param string   $surname ������� ������������
	 * @param integer  $type    ���
	 * @param string   $mess    ���������
	 * @param integer  $uid     �� ����������
	 * @return string ������ ���� ����
	 */
    function AddNew($name, $surname, $type, $mess, $uid){
        if (!confa07::CheckUid($uid)) {
            $sql = "INSERT INTO confa07 (uid,name,surname,type,message) VALUES ('$uid', '$name', '$surname', ".intval($type).", '$mess')";
            pg_query(DBConnect(),$sql);
            return true;
        } else $error[1] = "�� ��� ����������������";

        return $error;
    }
    
	/**
	 * �������� ������ � ������������
	 *
	 * @param string   $name    ��� ������������
	 * @param string   $surname ������� ������������
	 * @param integer  $type    ���
	 * @param string   $mess    ���������
	 * @param integer  $uid     �� ����������
	 * @return boolean true - ���� ��� ������ �������, ����� false
	 */
    function Update($name, $surname, $type, $mess, $uid){
        if (confa07::CheckUid($uid)) {
            $sql = "UPDATE confa07 SET name='$name',surname='$surname',type=".intval($type).",message='$mess' where uid='$uid'";
           pg_query(DBConnect(),$sql);
            return true;
        }
        return false;
    }
    
	/**
	 * ���������� � ������������ ������������������ �� �����������
	 *
	 * @param integer $uid �� ������������
	 * @return array|boolan ���������� �������, ���� false ���� ��� ����������
	 */
    function GetInfo($uid){
        $sql = "SELECT id,uid,name,surname,type,message FROM confa07 WHERE uid='".intval($uid)."' ";
        $res = @pg_query(DBConnect(),$sql);
        if (@pg_num_rows($res)) {return pg_fetch_assoc($res);  }
        else {  return false; }
    }
    
	/**
	 * ������� �����������
	 *
	 * @param integer $id �� �����������
	 * @return string ��������� �� ������
	 */
    function Delete($id) {
        $sql = "DELETE FROM confa07 WHERE id='$id'";
       pg_query(DBConnect(),$sql);
        $error .= pg_errormessage();
        return $error;
    }
    
	/**
	 * ��������� � ��������� �����
	 *
	 * @param integer $sw   0 - �������, 1 - �������
	 * @return string ��������� �� ������
	 */
    function Swch($sw){
        $sql = "UPDATE confa07 SET uid='$sw' WHERE id='0'";
        pg_query(DBConnect(),$sql);
        $error .= pg_errormessage();
        return $error;
    }
    
	/**
	 * ������ ���������� � ������������
	 *
	 * @param integer $uid �� ������������
	 * @return array|boolan ���������� �������, ���� false ���� ��� ����������
	 */
    function GetAllUinfo($uid){

        $sql = "SELECT users.role, users.uname, users.usurname, professions.name  FROM users LEFT JOIN freelancer ON fid=uid LEFT JOIN professions ON professions.id=freelancer.spec   WHERE uid='".$uid."'";
        $res = pg_query(DBConnect(),$sql);

        if (pg_num_rows($res)) {return pg_fetch_row($res);  }
        else {  return false; }
    }
    
	/**
	 * ����� ��� �����������
	 *
	 * @return array|boolan ���������� �������, ���� false ���� ��� ����������
	 */
    function GetAll(){

        $sql = "SELECT confa07.id,confa07.uid,confa07.name,confa07.surname,confa07.type,confa07.message, professions.name as prof, users.login,users.email FROM confa07 LEFT JOIN freelancer ON fid=uid LEFT JOIN professions ON professions.id=freelancer.spec LEFT JOIN users ON users.uid=confa07.uid ORDER BY confa07.id ASC ";
        $res = pg_query(DBConnect(),$sql);

        if (pg_num_rows($res)) {return @pg_fetch_all($res);  }
        else {  return false; }
    }
    
	/**
	 * �������� ����������� ����� � ���������� (������� ��� ��� ���)
	 * ���� uid = 0, �� ��������� ������� ����� ��� ���.
	 *
	 * @param integer $uid �� �����������
	 * @return integer ID ������������ ���� ����, ����� null
	 */
    function Check($uid=0){
        $sql = "SELECT uid FROM confa07 WHERE uid='".$uid."'";
        $res = pg_query(DBConnect(),$sql);
        list($ch) = pg_fetch_row($res);
        return $ch;
    }
    
	/**
	 * �������� ����������� ����� � ���������� (������� ��� ��� ���)
	 *
	 * @param integer $uid �� �����������
	 * @return integer ���������� ������� � �������
	 */
    function CheckUid($uid){
        $sql = "SELECT uid FROM confa07 WHERE uid='$uid'";
        $res = pg_query(DBConnect(),$sql);
        return pg_numrows($res);
    }
    
	/**
	 * ����� ����� �����������
	 *
	 * @return integer|boolan ���������� �������� �������, ���� false ���� ��� ����������
	 */
    function GetCount(){
        $sql = "SELECT id from confa07";
        $res = pg_query(DBConnect(),$sql);
        if (pg_num_rows($res)) { return pg_num_rows($res); }
            else {  return false; }
    }
    
	/**
	 * ������� ������������ � �����������
	 *
	 * @param integer $uid �� ������������
	 * @param  $opid -> ������ ���������� ���������� �������
 	 * @return string ��������� �� ������
	 */
	function DelByOpid($uid, $opid){
		$sql = "DELETE FROM confa07 WHERE uid='$uid'";
        pg_query(DBConnect(),$sql);
        $error .= pg_errormessage();
        return $error;
	}

}
?>
