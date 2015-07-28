<?
/**
 * ���������� ���� � ��������� ��������� �������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");

/**
 * ����� ��� ������ � ���������
 *
 */
class news 
{
	/**
	 * ����� ������� �� ������������ ����
	 *
	 * @param string $date   ����
	 * @param string $error  ���������� ��������� �� ������
	 * @return array �������
	 */
	function GetNews($date, &$error){
	    global $DB;
		$d = intval(substr($date,0,2));
		$m = intval(substr($date,2,2));
		$y = intval(substr($date,4,2));
		$num = intval(substr($date,6));
		$date = sprintf("20%02d-%02d-%02d", $y, $m, $d);
		$sql = "SELECT post_date, header, n_text FROM news WHERE post_date=? ORDER BY id DESC LIMIT 1 OFFSET ?i";
		
		$ret = $DB->row( $sql, $date, $num );
		
		if ($DB->error) $error = $DB->error;
        
		return (!$DB->error ? $ret : null);
	}
	
	/**
	 * ����� ������� �� ��� ��
	 *
	 * @param integer $id    �� �������
	 * @param string  $error ���������� ��������� �� ������
	 * @return array �������
	 */
	function GetNewsById($id, &$error){
	    global $DB;
		$sql = "SELECT post_date, header, n_text FROM news WHERE id = '$id'";
		$ret = $DB->row( $sql, $date, $num );
		
		if ($DB->error) $error = $DB->error;
        
		return (!$DB->error ? $ret : null);
	}
	
	/**
	 * ����� ��������� �������
	 *
	 * @return array �������
	 */
	function GetLastNews(){
		$sql = "SELECT post_date, header FROM news ORDER BY post_date DESC, id DESC LIMIT 1";
		$memBuff = new memBuff();
	  	$headers = $memBuff->getSql($error, $sql, 1800);
		if ($error) $error = parse_db_error($error);
			else $ret = $headers[0];
		return ($ret);
	}
	
	/**
	 * ����� ��� �������
	 *
	 * @param string $error ���������� ��������� �� ������
	 * @return array ��� �������
	 */
	function GetAllNews(&$error){
	    global $DB;
		$sql = "SELECT post_date, header, id FROM news ORDER BY post_date DESC, id DESC";
		$ret = $DB->rows( $sql );
		
		if ($DB->error) $error = $DB->error;
        
		return (!$DB->error ? $ret : null);
	}
	
	/**
	 * �������� �������
	 *
	 * @param string $post_date ���� �������
	 * @param string $header ��������� �������
	 * @param string $n_text ����� �������
	 * @return string ��������� �� ������
	 */
	function Add( $post_date, $header, $n_text ) {
	    global $DB;
	    $data = compact( 'post_date', 'header', 'n_text' );
	    
		$DB->insert( 'news', $data );
		
		if ($DB->error) $error = parse_db_error( $DB->error );
		
		return ($error);
	}
	
	/**
	 * �������������� �������
	 *
	 * @param string $date ���� �������
	 * @param string $name ���������
	 * @param string $text �����
	 * @param integer $id  �� �������
	 * @return string ��������� �� ������
	 */
	function Edit( $post_date, $header, $n_text, $id ) {
	    global $DB;
	    $data = compact( 'post_date', 'header', 'n_text' );
	    
		$DB->update('news', $data, 'id = ?i', $id);
		
		if ($DB->error) $error = parse_db_error( $DB->error );
		
		return ($error);
	}
	
	/**
	 * ������� �������
	 *
	 * @param integer $id �� �������
	 * @return string ��������� �� ������
	 */
	function Delete($id){
	    global $DB;
	    
		$DB->query('DELETE FROM news WHERE id = ?', $id);
		
		if ($DB->error) $error = parse_db_error( $DB->error );
		
		return ($error);
	}
}

?>