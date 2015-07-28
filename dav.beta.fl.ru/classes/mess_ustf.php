<?
/**
 * ����� ���������� ���������� ������������ � ������ ���������������� ������
 * ������������ ������� mess_ustf
 * @see class mess_folders.php
 */
class mess_ustf {

    /**
     * id ������
     *
     * @var integer
     */
    var $id;

    /**
     * id ������������, ���������� �����
     *
     * @var integer
     */
    var $from_id;

    /**
     * id ������������ � ����� � ������� ��������
     *
     * @var integer
     */
    var $to_id;

    /**
     * id ����� � ������� ��������
     *
     * @var integer
     */
    var $folder;

    /**
     * ��� ����, ������� mess_ustf, ����������� id serial
     *
     * @var string
     */
    var $pr_key="id";



    /**
     * ���������/������� ������������ � ������ �����
     *
     * @param string $login             ����� ������������, ��� ������� ���������� ��������
     *
     * @return integer
     */
    function Change($login){
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
		$DB = new DB;
		$ok = FALSE;
        $this->to_id = users::GetUid($error, $login);
		$res = $DB->query("SELECT * FROM messages_folders(?i)", $this->from_id);
		while ($row = pg_fetch_assoc($res)) {
			if ($row['id'] == $this->folder) {
				$ok = TRUE;
				break;
			}
		}
		if ($this->to_id && $this->from_id) {
			$id = $DB->val("SELECT messages_folders_user_move(?i, ?i, ?i)", $this->from_id, $this->to_id, $this->folder);
		}
		return $id;
    }
    
}

?>