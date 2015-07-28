<?
/**
 * ���������� ���� ��� ������ � �������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs_proto.php");
/**
 * ����� ��� ������ � ����������� ������ �� ������������� ����
 * @deprecated ������ ����� ���� � �����
 */
class blogs_tray extends blogs_proto
{
	/**
     * ������� ��� ���������
     *
     * @todo ���������� �������������� ����������, ��������� � �������
     * 
     * @param integer $nrsk_id  �� �����
     * @param mixed   $error    ���������� ��������� �� ������
     * @return array
     */
	function GetThread($nrsk_id, &$error){
        global $DB;
		$curname = get_class($this);
		$sql = "SELECT id, fromuser_id, reply_to, post_time, msgtext, attach, title, uname, usurname, login, photo, role, modified, modified_id, deluser_id, deleted, small, is_pro as payed
		FROM  $curname
		LEFT JOIN users ON fromuser_id=uid 
		WHERE item_id=?i ORDER BY reply_to, post_time";

        $this->thread = $DB->rows($sql, $nrsk_id);	
        $error .= $DB->error;

		if ($error) $error = parse_db_error($error);
		 else {
		 	$this->msg_num = count($this->thread);
		 	if ($this->msg_num > 0) $this->SetVars($this->msg_num-1);
		 }
		return array($name, $id_gr, 101);
	}
	/**
	 * ��������� �������������� ���������� � ���������
	 *
	 * @param integer $msg_id  �� ���������
	 * @param string  $error   ���������� ��������� �� ������
	 * @return array ���������� �� �������
	 */
	function GetMsgInfo($msg_id, &$error){
        global $DB;
		$curname = get_class($this);
        $sql = "SELECT * FROM $curname LEFT JOIN users ON users.uid=$curname.fromuser_id WHERE id=?i";

        $ret = $DB->row($sql, $msg_id);

        $error = $DB->error;
        if (!$error && $ret){
           //���������� $kind
			$ret['kind'] = $kind;
        }
        return $ret;
     }
}
?>
