<?
/**
 * ���������� ���� � ��������� ���������
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ������ � ��������� � �������
 *
 */
class blogs_fav 
{	
	/**
	 * �������� ��������� � ������
	 *
	 * @param  integer $thread_id �� ����
	 * @param  integer $uid       ID ������������
	 * @return integer ��������� ���������: 0 - �������� � ���������, 1 - ������ �� ����������.
	 */
	function ChangeFav( $thread_id, $uid ) {
	    global $DB;
		$sql = "SELECT * FROM blogs_fav WHERE (thread_id = ? AND user_id = ?)";
		$res = $DB->query( $sql, $thread_id, $uid );
		
		if ( pg_numrows($res) == 0 ) {
			$sql = "INSERT INTO blogs_fav (thread_id, user_id) VALUES (?, ?)";
			$ret = 1;
		}
		else {
			$sql = "DELETE FROM blogs_fav WHERE (thread_id = ? AND user_id = ?)";
			$ret = 0;
		}
		
		$res = $DB->query( $sql, $thread_id, $uid );
		
		return $ret;
	}
}
?>