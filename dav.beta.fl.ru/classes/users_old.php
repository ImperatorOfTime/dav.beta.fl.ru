<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

/**
 * ����� ��� ��������� ����� ������ �������������
 *
 */
class users_old extends users {
	/**
	 * �������� ����� � ������� "������" �������.
	 * ���������� ��� ������� ����� ������ ����� ����� ��������������� ��� ������ �����
	 *
	 * @param string $login    ����� ������������
	 * @return string ������ ���� ����
	 */
	function Add( $login ) {
	    global $DB;
	    
		$sql = "INSERT INTO users_old (login, is_active, subscr, active) VALUES ( ?, false, '0'::bit(".$GLOBALS['subscrsize']."), true)";
		$DB->query( $sql, $login );
		return $DB->error;
	}
}
?>