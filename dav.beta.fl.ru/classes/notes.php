<?
/**
 * ��������� ���� � ��������� ���������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ������ ���� �� ����� � ��������� ��� �������
 *
 */
define(TPL_DIR_NOTES, $_SERVER['DOCUMENT_ROOT']."/user");

/**
 * ����� ��� ������ � ��������� �� �������� ������������
 *
 */
class notes
{
    /**
	 * ���������� ����� �������
	 *
	 * @param integer $user_id       �� ������������ (��� �������)
	 * @param string  $target_login  ���� ������� (�����)
	 * @param string  $text          ����� ������� 
	 * @return string ��������� �� ������
	 */
	function Add($user_id, $target_login, $text, $rating = 0, $old="?i"){
		$DB = new DB;
		if ( empty($user_id) || empty($target_login) || empty($text) ) {
            return '������ ���������� �������';
        }
		$id = $DB->val("SELECT notes_add(?i, {$old}, ?, ?i)", $user_id, $target_login, $text, $rating);
		return '';
	}
	
	/**
	 * �������� �������
	 *
	 * @param integer $user_id      �� ������������ 
	 * @param string  $target_login ���� �������
	 * @param string  $text         ����� �������
	 * @return string ��������� �� ������
	 */
	function Update($user_id, $target_login, $text, $rating = 0, $old="?i"){
		$DB = new DB;
		if ( empty($user_id) || empty($target_login) || empty($text) ) {
            return '������ ���������� �������';
        }
		$res = $DB->val("SELECT notes_update(?i, {$old}, ?, ?i)", $user_id, $target_login, $text, (int)$rating);
		return '';
	}
	
	/**
	 * ����� ��� �������
	 *
	 * @param integer $from_id   �� ������������ ��� �������
	 * @param array   $to_login  ���� ������� (������ � ��������) 
	 * @param string  $error  ���������� ��������� �� ������
	 * @return array ������ �������
	 */
	function GetNotes($from_id, $to_login=false, &$error){
		$DB = new DB;
		if(!$from_id) return false;
		if (empty($to_login)) {
			$rows = $DB->rows("SELECT * FROM notes(?)", $from_id);
		} elseif (is_array($to_login)) {
			$rows = $DB->rows("SELECT * FROM notes_get(?, ?a)", $from_id, $to_login);
		} else {
			$rows = $DB->row("SELECT * FROM notes_get(?i, ?i)", $from_id, $to_login);
        }
		return $rows;
	}
	
	/**
	 * ������� �������
	 *
	 * @param integer $from_id   �� ������������ ��� �������
	 * @param string  $to_login  ���� ������� (�����)
	 * @param string  $error     ���������� ��������� �� ������
	 * @return array ������ �������
	 */
	function GetNote($from_id, $to_id, &$error=false){
		$DB = new DB;
		$rows = $DB->row("SELECT * FROM notes_get(?i, ?)", $from_id, $to_id);
		return $rows;
	}
	
	/**
	 * ������� �������
	 *
	 * @param integer $from_id   �� ������������ ��� �������
	 * @param string  $to_login  ���� ������� (�����)
	 * @param string  $error     ���������� ��������� �� ������
	 * @return array ������ �������
	 */
	function GetNoteInt($from_id, $to_id, &$error=false){
		$DB = new DB;
		$rows = $DB->row("SELECT * FROM notes_get(?i, ?i)", $from_id, $to_id);
		return $rows;
	}
	
	/**
	 * �������� �������
	 *
	 * @param inetger  $user_id    �� ������������
	 * @param integer  $to_uid     ���� ������� (�����)
	 * @return string  $error      ���������� ��������� �� ������
	 */
	function DeleteNote($user_id, $to_uid, $old="?i"){
		$DB = new DB;
		if ( empty($user_id) || empty($to_uid)) {
            return '������ �������� �������';
        }
		$DB->query("SELECT notes_del(?i, {$old})", $user_id, $to_uid);
		return '';
	}
    
	/**
	 * ������� ���� �������������� � ���������� �������
	 *
	 * @param array $req ������ ��� �����
	 * @return string $html ������ � ������� HTML
	 */
	public function getNotesForm($req, $type = 1) {
	    ob_start();
        include_once($_SERVER['DOCUMENT_ROOT'].'/user/tpl.notes_form.php');
        $html = ob_get_clean();
        return $html;
	}
	
	/**
	 * ������� ������� ������������� ������� ��������� � ���������
	 *
	 * @param array   $recs  ������������ ����������� � ���������
	 * @param array   $notes ������� ������������
	 * @param integer $start ������ ������� ������ ������� ������������� (� ������ ������������ � ������� recs)
	 * @param integer $stop  ����� ������� ������ ������� ������������� (�� ������ ������������ � ������� recs)
 	 */
	public function getNotesUsers($recs, $notes, $start, $stop, $type=1) {
	    global $session, $recsProfi;
	    
    	for($i=$start;$i<$stop;$i++) { 
            $rec = $recs[$i];
            
            if(isset($recsProfi[$rec['uid']])) {
                $rec['is_profi'] = $recsProfi[$rec['uid']];
            } 
            
            $clsNote = "";
            if(count($notes[$rec['uid']]) > 0) {
                $note = $notes[$rec['uid']];
                switch($note['rating']) {
                    /*case 1:
                        $clsNote = "fs-g";
                        break;        
                    case 0:
                        $clsNote = "";
                        break;
                    case -1:
                        $clsNote ="fs-p";
                        break;*/
                    default:
                        $clsNote ="fs-o";
                        break;
                }
            } else {
                $note = false;
            }
            include (TPL_DIR_NOTES."/tpl.notes_item.php");
        } 
	}
}

?>
