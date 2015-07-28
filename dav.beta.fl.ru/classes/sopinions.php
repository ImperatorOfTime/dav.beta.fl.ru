<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ������ � �������� ��������� � ���
 */
class sopinions{
	/**
	 * ���������� ������ ��������� � ���
	 * 
	 * @return array
	 */
	function GetMsgs() {
		return $GLOBALS['DB']->rows("SELECT id, msgtext, sign, logo, link FROM sopinions ORDER BY post_time DESC");
	}
	
	/**
	 * ���������� ������������ ����� 
	 *
	 * @param  int $msg_id ID ������
	 * @return array
	 */
	function GetMsgInfo( $msg_id ) {
		return $GLOBALS['DB']->row("SELECT msgtext, sign, link, logo, id FROM sopinions WHERE id = ?", $msg_id);
	}
	
	/**
	 * �������� �����
	 *
	 * @param  string $msg ����� ������
	 * @param  string $sign �������
	 * @param  object $file CFile ���� � ���������
	 * @param  string $link ������ �� ���� ���� ��� ������� �����
	 * @param  string $from_ip IP ����� ���� ��� ����� �����
	 * @return array ��������� �� ������� (����, ����)
	 */
	function Add( $msg, $sign, $file, $link, $from_ip ) {
	    if ($file->tmp_name){
    	    $file->max_size = 1048576;
            $file->proportional = 1;
            $file->max_image_size = array('width'=>120, 'height'=>120, 'less'=>1);
            $file->resize = 1;
            $file->proportional = 1;
            $file->topfill = 1;
            $file->server_root = 1;
        
            $f_name = $file->MoveUploadedFile("about/opinions/");
    	    if (!isNulArray($file->error)) { $alert[3] = "���� �� ������������� �������� ��������"; $error_flag = 1;}
	    }
	    if (!$error_flag){
			$GLOBALS['DB']->insert('sopinions', array(
				'msgtext' => $msg,
				'sign'    => $sign,
				'logo'    => $f_name,
				'link'    => $link,
				'from_id' => $from_ip
			));
	    }
		return array($alert, $DB->error);
	}
	
	/**
	 * ������� �����
	 *
	 * @param  int $msg ID ������
	 * @param  int $admin �������� �� ������������ ��������������� 1 - ��, 0 - ��� (���� is_admin �� stdf)
	 * @return array ��������� �� ������
	 */
	function Del( $msg, $admin = 0 ) {
		if (!$admin) {
			return 0;
	    }
		if ($ret = $GLOBALS['DB']->val("DELETE FROM sopinions WHERE (id = ?) RETURNING logo", $msg)) {
		    $file = new CFile();
		    $file->Delete(0,"about/opinions/", $ret);
		}
		return $DB->error;
	}
	
	/**
	 * �������� �����
	 *
	 * @param  string $msg ����� ������
	 * @param  string $sign �������
	 * @param  object $file CFile ���� � ���������
	 * @param  string $link ������ �� ���� ���� ��� ������� �����
	 * @param  string $from_ip IP ����� ���� ��� ����� �����
	 * @param  int $msgid ID ������
	 * @return array ��������� �� ������� (����, ����)
	 */
	function Edit( $msg, $sign, $file, $link, $from_ip, $msgid ) {
		if ($file) {
		    $file->max_size = 1048576;
            $file->proportional = 1;
            $file->max_image_size = array('width'=>120, 'height'=>120, 'less'=>1);
            $file->resize = 1;
            $file->proportional = 1;
            $file->topfill = 1;
            $file->server_root = 1;
        
            $f_name = $file->MoveUploadedFile("about/opinions/");
    	    if (!isNulArray($file->error)) { $alert[3] = "���� �� ������������� �������� ��������"; $error_flag = 1;}
		    if (!$error_flag) {
				$GLOBALS['DB']->query(
					"UPDATE sopinions SET msgtext = ?, sign = ?, logo = ?, link = ?, from_ip = ?, modified = NOW() WHERE id = ?",
					$msg, $sign, $f_name, $link, $from_id, $msgid
				);
			}
		} else {
			$GLOBALS['DB']->query(
				"UPDATE sopinions SET msgtext = ?, sign = ?, link = ?, from_ip = ?, modified = NOW() WHERE id=?",
				$msg, $sign, $link, $from_id, $msgid
			);
		}
		return array($alert, $DB->error);
	}
	
}
?>