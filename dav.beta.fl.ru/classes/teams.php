<?
/**
 * ���������� ���� � ��������� ���������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

/**
 * ����� ��� ������ � ������� ������������� (���������, � ����������, ���� �����������)
 *
 */
class teams {
	/**
	 * �� ������������
	 *
	 * @var integer
	 */
	public $user_id;
	/**
	 * ��
	 *
	 * @var integer
	 */
	public $target_id;
	
	/**
	 * �������� ������������ � ���������
	 *
	 * @param integer $user_id       �� ������������, � �������� ���������
	 * @param string  $target_login  ����� ��� uid ������������ ������������
	 * @return string ��������� �� ������
	 */
	function teamsAddFavorites($user_id, $target, $by_login = true) {
		$DB = new DB;
		$error = '';

		$user = new users;
		if($by_login) {
			$user->GetUser($target);
			$target = $user->uid;
		} else {
			$user->GetUserByUID($target);
		}

		if ($user_id && $target && $user_id != $target) {
			if ($DB->val("SELECT teams_check(?i, ?i)", $user_id, $target)) {
				$error = '������������ ��� ��������';
			} else {
                $DB->val("SELECT teams_add(?i, ?i)", $user_id, $target);
                if($user->subscr[9]) {
					require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/pmail.php";
                    $mail = new pmail;
                    $mail->addTeamPeople($user_id, $target);
                }
            }
		} else {
			$error = "������������ �� ���������";
		}
		return $error;
	}
	
	/**
	 * �������� ����������� � ������� ���� � ���������
	 *
	 * @param string $for_whom �����
	 * @param string $error ���������� ��������� �� ������
	 * @return array 
	 */
	function teamsInFrlFavorites($for_whom, &$error) {
		$users = new users;
		$uid = $users->GetUid($err, $for_whom);
		$DB = new DB;
		$rows = $DB->rows("SELECT * FROM teams_recom_freelancers(?i)", $uid);
		return $rows;
	}


	/**
	 * �������� ������������� � ������� ���� � ���������
	 *
	 * @param string $whom ����� ������������
	 * @param string $error ���������� ��������� �� ������
	 * @return array
	 */
	function teamsInEmpFavorites($whom, &$error) {
		$users = new users;
		$uid = $users->GetUid($err, $whom);
		$DB = new DB;
		$rows = $DB->rows("SELECT * FROM teams_recom_employers(?i)", $uid);
		return $rows;
	}

	
	/**
	 * �������� ���� ��� � ����� � ���������
	 *
	 * @param string $whom  ����� ��� uid
	 * @param string $error ���������� ��������� �� ������
	 * @param bool $bIsLogin �������� �� $whom �������
	 * @return array
	 */
	function teamsFavorites( $whom, &$error, $bIsLogin = false ) { // � ����������.
		$DB = new DB;
		
        if ( $whom === NULL ) {
            $whom = get_uid(false);
        }
        
        if ( $bIsLogin ) {
            $users = new users;
            $whom = $users->GetUid($err, $whom);
        }
        
		$users = $DB->rows("SELECT * FROM teams_get_users(?i)", $whom);
		return $users;
	}


	/**
	 * �������� ���������� ���� ��� � ����� � ���������
	 *
	 * @param string $whom  ����� ��� uid
	 * @param string $error ���������� ��������� �� ������
	 * @param bool $bIsLogin �������� �� $whom �������
	 * @return array
	 */
	function teamsFavoritesCount( $whom, &$error, $bIsLogin = false ) { // � ����������.
		$DB = new DB;
		
		if ( $bIsLogin ) {
			$users = new users;
			$whom = $users->GetUid($err, $whom);
		}
		
		return $DB->val("SELECT COUNT(*) FROM teams(?i)", $whom);
	}

	/**
	 * ������� ������ �� ���������� �� ���������� �������������
	 *
	 * @param integer $user_id  �� �����, � �������� ����������� ���������.
	 * @param array   $selected �� ������, ������� �� ����� ������� (������ ������ -- ������� ����).
	 * @return string ��������� �� ������
	 */
	function teamsDelFavoritesExcept($user_id, $selected) {
		$DB = new DB;
        if ( empty($user_id) ) {
            return '������ ��� ��������';
        }
		if (!$selected) $selected = array();
		$DB->val("SELECT teams_leave(?i, ?ai)", $user_id, $selected);
		return '';
	}
	
	/**
	 * �������� ����� � ���������, ���� ��� ��� �� ����, ��� �������, ���� ���
	 *
	 * @param string $login ����� �����, �������� ����� �������/��������.
	 * @return integer ���������� ��������� ��� ��������� ������
	 */
	function teamsInverseFavorites($login) {
		$r = 1;
		$DB = new DB;
		$this->target_id = users::GetUid($err, $login);
		if ($this->user_id && $this->target_id) {
			$r = $DB->val("SELECT teams_check(?i, ?i)", $this->user_id, $this->target_id);
			if ($r) {
				$this->teamsDelFavorites();
			} else {
				$this->teamsAddFavorites($this->user_id, $login);
			}
		}
		return $r;
	}
	
	
	/**
	 * ������� ����� �� ����������
	 *
	 * @return string ��������� �� ������
	 */
	function teamsDelFavorites() {
		$DB = new DB;
        if ( empty($this->user_id) || empty($this->target_id) ) {
            return '������ ��� �������� ������������';
        }
		$DB->query("SELECT teams_del(?i, ?i)", $this->user_id, $this->target_id);

		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/pmail.php";
        $mail = new pmail;
        $mail->delTeamPeople($this->user_id, $this->target_id);

		return '';
	}

    /**
     * �������� ������������ �� ���������� �� ��� ������
     *
     * @param integer $user_id     id ������������ � ��������� �������� ����� ������� ������������
     * @param string  $target_login   login ������������, �������� ����� �������
     * @return string  ����� ������ �������� ��� ������ ������
     */    
	function teamsDelFavoritesByLogin($user_id, $target_login) {
		$DB = new DB;
		$error = '';
		if ($user_id && ($target_id = users::GetUid($error, $target_login))) {
			$DB->query("SELECT teams_del(?i, ?i)", $user_id, $target_id);

			require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/pmail.php";
        	$mail = new pmail;
	        $mail->delTeamPeople($user_id, $target_id);

		} else {
			$error = "���� �� ���������";
		}
		return $error;
	}    
    
    /**
     * ���������, ��������� �� ���� ������������ � ��������� � �������
     *
     * @param integer $user_id     id ������������ � ��������� �������� ����� ���������
     * @param integer $target_id   id ������������, �������� ���������
     * @return integer  1 - ���� ���� � ���������, 0 - ���� ���
     */
    function teamsIsInFavorites($user_id, $target_id) {
        $DB = new DB;
		return $DB->val("SELECT teams_check(?i, ?i)", $user_id, $target_id);
    }
    
}

?>
