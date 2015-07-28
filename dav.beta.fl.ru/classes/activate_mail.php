<?
/**
 * ���������� ���� � ��������� ���������
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ��������� ������ e-mail ������������
 *
 */
class activate_mail
{
	/**
	 * ������� ��� ��������� ��� ������ e-mail ������������
	 *
	 * @param integer $uid		ID ������������ (users.uid)
	 * @param string  $email	����� e-mail �����
	 * @param string  $error	���������� ������
	 * @return string|integer $code	���������� ��������������� ��� ���������, ��� 0 ���� �� ���������������
	 */
	function Create( $uid, $email, &$error ) {
		if ( $uid && $email ) {
			// ��������� ���� �� � ���� �����
			global $DB;
		    $sql = "SELECT uid FROM users WHERE lower(email) = ?";
            $res = $DB->query( $sql, strtolower($email) );
            if ( pg_num_rows($res) != 0 ) {
                $error = "������������ � ����� e-mail ��� ����������!";
            }
            else {
    			$code = md5(crypt($email));
    			$DB->insert( 'activate_mail', array('user_id'=>$uid, 'code'=>$code, 'email'=>$email) );
    			$error .= pg_errormessage();
            }
		} 
		else {
		    $code = 0;
		}
		
		return ($code);
	}
	
	/**
	 * ���������� ����� e-mail ����� �� ���� ���������
	 *
	 * @param string $code	��� ���������
	 * @return integer		1 - ������������, 0 - �� ������������
	 */
	function Activate ($code) {
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
		global $DB;
		$sql = "SELECT user_id, email FROM activate_mail WHERE code = ?";
		$res = $DB->query( $sql, $code );
		list($fid, $email) = pg_fetch_row($res);
		if ($fid) {
			$usr = new users();
			$usr->email = $email;
			$usr->Update($fid, $res);
            $usr->SaveChangeEmailLog($fid,$email);
			$out = 1;
			$this->Delete($fid);
		} else $out = 0;
		return $out;
	}
	
	/**
	 * ������� ��� ���������
	 *
	 * @param integer $fid	ID ������������, � �������� ������� ��� ���������
	 * @return string	��������� �� ������
	 */
	function Delete( $fid ) {
	    global $DB;
		$sql = "DELETE FROM activate_mail WHERE user_id = ?";
		$DB->query( $sql, $fid );
		return pg_errormessage();
	}
}
?>
