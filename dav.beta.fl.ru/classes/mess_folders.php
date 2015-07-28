<?
/**
 * ����� ���������� �����������������, ������� ������������ ������ ��������������, ������� � ������ ����������
 * ������������ ������� mess_folders
 */
class mess_folders
{
    /**
     * id �����
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
     * ��� �����
     *
     * @var string
     */
    var $fname;

    /**
     * ���������� ������������� � �����
     *
     * @var integer
     */
    var $users_cont;

    /**
     * ��� ����, ������� mess_folders, ����������� id serial
     *
     * @var string
     */
    var $pr_key="id";
	
    /**
     * ���������� ������ �����.
     * 
     * @return array
     */
	function GetAll() {
		$DB = new DB;
		return $DB->rows("SELECT * FROM messages_folders(?i)", $this->from_id);
	}
	
	/**
	 * ������� �����
	 * 
	 * @return string ������ ������ - �����, ��� ��������� �� ������
	 */
	public function Add() {
		$DB = new DB;
		if ($DB->val("SELECT COUNT(*) FROM messages_folders(?i) WHERE fname = ?", $this->from_id, $this->fname)) {
			return '����� � ����� ������ ��� ����������';
		} else {
			$id = $DB->val("SELECT messages_folders_add(?i, ?)", $this->from_id, $this->fname);
		}
		return '';
	}
	
	/**
	 * ������� �����
	 */
	public function Del() {
		$DB = new DB;
		$DB->val("SELECT messages_folders_del(?i, ?i)", $this->id, $this->from_id);
	}
	
	/**
	 * ������������� �����
	 * 
	 * @return string ������ ������ - �����, ��� ��������� �� ������
	 */
	public function Rename() {
		$DB = new DB;
		if (!($r = $DB->row("SELECT * FROM messages_folders(?i) WHERE id = ?", $this->from_id, $this->id))) {
			return '��������� ����� �� ����������';
		}
		if ($DB->val("SELECT COUNT(*) FROM messages_folders(?i) WHERE fname = ? AND id <> ?", $this->from_id, $this->fname, $this->id)) {
			return '����� � ����� ������ ��� ����������';
		}
		$DB->query("SELECT messages_folders_rename(?, ?, ?)", $this->id, $this->from_id, $this->fname);
		return '';

	}
	
}

?>
