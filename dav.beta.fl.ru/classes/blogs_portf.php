<?
/**
 * ���������� ���� ��� ������ � �������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs_proto.php");
/**
 * ����� ��� ������ � ����������� ���������
 *
 */
class blogs_portf extends blogs_proto
{
	/**
     * ������� ����� ���������
     *
     * @todo ������� ���������� ��������� ������� �� ���������� � ����� �������, ����� ��������� ��� ���������� � ������.
     * 
     * @param integer $portf_id  �� ����
     * @param string  $error     ���������� ��������� �� ������
     * @return array 
     */
	function GetThread($portf_id, &$error){
        global $DB;
		$curname = get_class($this);
		$sql = "SELECT id, fromuser_id, reply_to, post_time, msgtext, attach, title, uname, usurname, login, photo, role, modified, modified_id, deluser_id, deleted, small, payed
		FROM 
		(SELECT $curname.item_id, $curname.fromuser_id, $curname.id, $curname.reply_to, $curname.post_time, $curname.msgtext, $curname.attach, $curname.title, $curname.modified, $curname.modified_id, $curname.deleted, $curname.deluser_id, $curname.small, 1 as t FROM $curname 
		UNION ALL 
		SELECT id, user_id, 0, NULL, NULL, descr, pict, name, NULL, NULL, NULL, NULL, NULL, 0
		FROM portfolio WHERE id=?i) as blg
		LEFT JOIN users ON fromuser_id=uid 
		LEFT JOIN (SELECT DISTINCT from_id, payed FROM orders 
             WHERE payed=true AND from_date<=now() AND from_date+to_date+COALESCE(freeze_to, '0')::interval >= now() AND orders.active='true'
             AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)) as pay
		 ON pay.from_id=uid
		WHERE item_id=?i ORDER BY blg.t, reply_to, post_time";

        $this->thread = $DB->rows($sql, $portf_id, $portf_id);

		$error .= $DB->error;
		if ($error) $error = parse_db_error($error);
		 else {
		 	$this->msg_num = count($this->thread);
		 	if ($this->msg_num > 0) $this->SetVars(0);
		 }
		return array($name, $id_gr, 100);
	}
	/**
	 * �������� ����������� � ���������
	 *
	 * @param integer $fid    ID ������������
	 * @param integer $reply  ������������� ��������� ������� �� ������� �������� ������ ���������
	 * @param integer $thread ����
	 * @param string  $msg    ���������
	 * @param string  $name   �������� ���������
	 * @param mixed   $attach �������� ������
	 * @param string  $ip     �� �����������
	 * @param mixed   $error  ���������� ��������� �� ������
	 * @param mixed   $small  ����� ������
	 * @return integer ���������� �� ������������ ����������
	 */
	function Add($fid, $reply, $thread, $msg, $name, $attach, $ip, &$error, $small){
        global $DB;
		$curname = get_class($this);
		$sql = "SELECT show_comms FROM portfolio WHERE portfolio.id = ?i";
		$portf_comments = $DB->val($sql, $thread);
        $error = $DB->error;
		if ($portf_comments != 't') {$error = "������������ �������� ��������� �����������"; return 0;}
		return parent::Add($fid, $reply, $thread, $msg, $name, $attach, $ip, $error, $small);
	}
	
}
?>
