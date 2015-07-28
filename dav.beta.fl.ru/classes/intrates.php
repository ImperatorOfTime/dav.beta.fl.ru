<?
/**
 * ����� ��� ������ � ���������� �� ������� �� ����� ��������� ������� � ������ � ���.
 * 
 */
class intrates {
	/**
	 * ID ������.
	 *
	 * @var integer
	 */
	public $id;
	/**
	 * ������� ��� ������.
	 *
	 * @var float
	 */
	public $val;
	/**
	 * Primary key ������� intrates.
	 *
	 * @var string
	 */
	public $pr_key = "id";

	
	/**
	 * �������� ��� ��������
	 * @param   array   $arr    ������ � ������ �������, � �������:
	 *                          ������ - id ������; �������� - �������
	 * @return  integer         1 - � ������ ������, ����� 0
	 */
	function BatchUpdate( $arr ) {
		foreach ($arr as $ikey => $val){
			$vals[] = "INSERT INTO intrates (id, val) VALUES ('".$ikey."','".$val."')";
		}
		
		global $DB;
		$sql = "DELETE FROM intrates; ".implode('; ', $vals).";";
		$DB->squery( $sql );
		return 0;
	}
	
	/**
	 * ���������� ������� �������� �� ���������
	 * @return  array         ������ � �������� ���������� �� �������, � �������:
	 *                        ������ - id ������; �������� - �������
	 */
	function GetAll() {
	    global $DB;
		$res = $DB->squery( "SELECT * FROM intrates" );
		$ret = pg_fetch_all($res);
		if ($ret)
			foreach($ret as $val){
				$out[$val['id']] = $val['val'];
			}
		return $out;
	}
	
	/**
	 * ����� ������ ������������� ���� �� �����
	 * 
	 * @param  integer $uid �� ����
	 * @param  string $error ���������� ��������� �� ������
	 * @param  string $fieldname ���� �������
	 * @return string ������ ����
	 */
	function GetField( $uid, &$error, $fieldname ) {
		$current = get_class($this);
		return $GLOBALS['DB']->val( "SELECT {$fieldname} FROM {$current} WHERE {$this->pr_key} = ?", $uid );
	}
}

?>