<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ������ � �������������� ������������� ���� ����� (������ ���������)
 *
 */
class ignor{
    
    protected $DB;
	
	/**
     * ��������� ������������ � ������ �������������
     *
     * @param integer $user_id           id ������������, ������������ ������� � �����-����
     * @param string $target_login       ����� ������������, ����������� � �����-����
     *
     * @return string                    ������ ������ ��� ��������� �� ������ � ������ ��������
     */    
    function Add($user_id, $target_login) {
        global $usersNotBeIgnored;
        if ( empty($user_id) || empty($target_login) || in_array($target_login, $usersNotBeIgnored) ) {
            return false;
        }
        $user = new users();
        $user->login = $target_login;
        $target_id = $user->GetUid($error);
        $DB = new DB;
		$r = $DB->val("SELECT ignor_add(?i, ?i)", $user_id, $target_id);
        return '';
    }


    
    /**
     * ��������� ������������� � ������ �������������
     *
     * @param integer $user_id           id ������������, ������������ ������ � �����-����
     * @param array $selected            id �������������, ����������� � �����-����
     *
     * @return string                    ������ ������ ��� ��������� �� ������ � ������ ��������
     */    
    function AddEx($user_id, $selected){
        $DB = new DB;
		if (!empty($user_id) && is_array($selected) && count($selected)) {
			$DB->query("SELECT ignor_add(?i, ?a)", $user_id, $selected);
			$error = '';
		} else {
			$error = "���������� ������� ���� �� ���� �������";
		}
		return $errors;
    }


    /**
     * ������� ������������ �� ������ �������������
     *
     * @param integer $user_id           id ������������, ���������� ������� �� �����-�����
     * @param array $selected            id �������������, ���������� �� �����-�����
     *
     * @return string                    ������ ������ ��� ��������� �� ������ � ������ ��������
     */    
    function Del(){
        $DB = new DB;
        if ( empty($this->user_id) || empty($this->target_id) ) {
            return '�� �� ������� �������';
        }
		$DB->query("SELECT ignor_del(?i, ?)", $this->user_id, $this->target_id);
        return '';
    }
	
    
    /**
     * ������� ������������� �� ������ �������������
     *
     * @param integer $user_id           id ������������, ���������� ������ �� �����-�����
     * @param array $selected            id �������������, ��������� �� �����-�����
     *
     * @return string                    ������ ������ ��� ��������� �� ������ � ������ ��������
     */    
    function DeleteEx($user_id, $selected){
        if (is_numeric($selected)) $selected = array($selected);
		$DB = new DB;
		if ( !empty($user_id) && is_array($selected) && count($selected) ) {
			$DB->query("SELECT ignor_del(?i, ?a)", $user_id, $selected);
			$error = '';
		} else {
			$error = "���������� ������� ���� �� ���� �������";
		}
        return $error;
    }


    
    /**
     * �������� ����������� �� ����������� � �����-�����
     *
     * @param integer $from_id           id ������������, ��������� �����-�����
     * @param array $tar_id              id �������������, �������� ���������
     *
     * @return integer                   0 - ���, 1 - ����
     */    
    function CheckIgnored($from_id, $tar_id){
        $DB = new DB;
		$r = $DB->val("SELECT ignor_check(?i, ?i)", $from_id, $tar_id);
		return $r;
    }


    
    /**
     * �������� ����������� �� ����������� � �����-�����.
     * � ������ ���������� ������� ������������ �� ������, ����� ���������
     *
     * @param integer $login             ����� �������������, �������� ���������
     *
     * @return string                    ������ ������ ��� ��������� �� ������ � ������ ��������
     */    
    function Change($login){
        $DB = new DB;
		$r = $DB->val("SELECT ignor_check(?, ?)", $this->user_id, $login);
		if ($r) {
			$this->target_id = $login;
			$this->Del();
		} else {
			$this->Add($this->user_id, $login);
		}
		return $r;
    }


    
}

?>