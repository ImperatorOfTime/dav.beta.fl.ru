<?
/**
 * ���������� ���� � ��������� ���������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ������ � ������
 *
 */
class tags
{
	/**
	 * �������� ����� ����
	 * 
	 * 
	 * @param array $tags ���� [���1,���2,...]
	 * @return array ���������� ����������
	 */
	function Add( $tags ) {
	    global $DB;
	    
		foreach($tags as $ikey => $value){
			if (!$value) continue;
			$sql      = "SELECT inserttag('".change_q_new(substr(trim($value),0,20))."');";
			$tag[]    = $DB->val( $sql );
			$error[1] = parse_db_error( $DB->error );
		}
		
		return $tag;
	}
}
?>