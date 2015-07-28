<?
/**
 * ���������� ���� � ��������� ���������
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
/**
 * ����� ��� �������� � ��������� ���� ��������� ����� �������������.
 *
 */
class activate_code 
{	
	/**
	 * ������� ��� ��������� ��� ������ ����� � �������� ��� � ���� ������
	 *
	 * @param  integer $uid ID ������������ (users.uid)
	 * @param  string  $login ����� ������������
	 * @param  string  $sSuspectPwd ����������. �������� ������, ���� ������������ ��������� ��������������
	 * @param  string  $error ���������� ������, ���� ��� ����.
	 * @return string|integer $code ��������������� ��� ���������, ���������� ���� ���� ��� ��������� �� ��� ������������
	 */
	function Create( $uid, $login, $sSuspectPwd = '', &$error ) {
		if( $login && $uid ) {
		    global $DB;
			$code = md5( crypt($login) ); // �������� ������ ����
			$data = array( 'user_id' => $uid, 'code' => $code );
			
			if ( $sSuspectPwd ) { // ������ ������ ���� �����. ����� ����� null �����
				$data['suspect_plain_pwd'] = $sSuspectPwd;
			}
			
			$DB->insert( 'activate_code', $data );
			$error .= pg_errormessage();
		} 
		else $code = 0;
		
		return ($code);
	}
	
	/**
	 * ���������� ������� ����� �� $code, ���������� ����� � ������ ������������
	 *
	 * @param string $code		��� ���������
	 * @param string $login		���������� ����� ������������
	 * @param string $pass		���������� ������ ������������
	 * @return integer			1 - ��������� ������ �������, 0 - ��������� �� ������
	 */
	function Activate ( $code, &$login, &$pass ) {
        define('IS_USER_ACTION', 1);
		/**
		 * ��������� ���� ��� ������ � �������������
		 */
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/wizard_registration.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_employer.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_freelancer.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        
		global $DB;
		$sql = "SELECT user_id, login, passwd FROM activate_code LEFT JOIN users ON user_id=uid WHERE code = ?";
		$res = $DB->query( $sql, $code );
		list($fid, $login, $pass) = pg_fetch_row($res);
		if ($fid) {
			$usr = new users();
			$usr->active = 1;
			$usr->Update($fid, $res);
            $usr->GetUserByUID($fid);
            // #0017513
            if($usr->role[0] == 1) {
                $wiz_user = wizard::isUserWizard($fid, step_employer::STEP_REGISTRATION_CONFIRM, wizard_registration::REG_EMP_ID);
            } else {
                $wiz_user = wizard::isUserWizard($fid, step_freelancer::STEP_REGISTRATION_CONFIRM, wizard_registration::REG_FRL_ID);
            }
			$out = 1;
			$this->Delete($fid);
            if($wiz_user['id'] > 0) {
                $mail = new smail(); 
                if ($usr->role[0] == 1) {                
                    $mail->employerQuickStartGuide($fid);
                } else {
                    $mail->freelancerQuickStartGuide($fid);
                }
                step_wizard::setStatusStepAdmin(step_wizard::STATUS_COMPLITED, $fid, $wiz_user['id']);
                $role = ($usr->role[0] == 1) ? wizard_registration::REG_EMP_ID : wizard_registration::REG_FRL_ID ;
                login($login, $pass, 0, true);
                header("Location: /registration/activated.php?role=".$role);
                exit;
            }
		} else $out = 0;
		return $out;
	}
	
	/**
	 * ������� ��� ��������� ������� ��� �����������.
	 *
	 * @param integer $fid ID ������������
	 * @return mixed	��������� �� ������
	 */
	function Delete( $fid ) {
	    global $DB;
		$sql = "DELETE FROM activate_code WHERE user_id = ?";
		$DB->query( $sql, $fid );
		return pg_errormessage();
	}
    
    /**
     * ����� ��� ��������� ��� ���������� ����������� ������������ �� �����
     * 
     * @global type $DB
     * @param integer $uid �� ������������
     * @return string 
     */
    function getActivateCodeByUID($uid) {
        global $DB;
        return $DB->val("SELECT code FROM activate_code WHERE user_id = ?", $uid);
    }
    
    function isActivateCode($code) {
        global $DB;
		return $DB->val("SELECT user_id FROM activate_code WHERE code = ?", $code );
    }
}
?>