<?
/**
 * ���������� ���� � ��������� ���������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ������ � ��
 *
 */
class db_access 
{ 
	
	/**
	* 
	* ���������� ������ � �������
	* 
	* @desc ����� ������� ��������� ���������� ������!
	* ������ ���� ���������� ������ ��, ������� ���� ��������!
	* 
	* @param integer $fid      ������������� ��������� ���� (�������� ��������)
	* @param string  $eddition �������������� ������� ������� ��� ��������������
	* @return string $error ��������� �� ������
	
	*/
	function Update($fid, $eddition = ""){
		$current = get_class($this);
		$class_vars = get_class_vars(get_class($this));
		$fields = array();
		foreach ($class_vars as $name => $value) {
    		if (isset($this->$name) && $name != "pr_key"){
				if ($this->$name == 'null') $fields[] = $name."= ".$this->$name."";
				else $fields[] = $name."= '".str_replace("'","&#039;",$this->$name)."'";
    		}
		}
		$fld = implode(", ",$fields);
		if ($fld){
			$sql .= "UPDATE $current SET $fld WHERE (".$this->pr_key." = '$fid' ".$eddition.")";
			if (!$GLOBALS['DB']->squery($sql)) {
				$error = 'DB error';
			}
		}
		return ($error);
	}
	
	/**
	 * ����� ������ ������������� ���� �� �����
	 *
	 * @param integer $uid       �� ����
	 * @param string  $error     ���������� ��������� �� ������
	 * @param string  $fieldname ���� �������
	 * @return string ������ ����
	 */
	function GetField($uid, &$error, $fieldname){
		$current = get_class($this);
		return $GLOBALS['DB']->val("SELECT {$fieldname} FROM {$current} WHERE {$this->pr_key} = ?", $uid);
	}
	
	/**
	 * ����������� ��� ���������� ������� � ���������� ��������������� ������, 
	 * ������� ������������ � ��������� ������ ��� $this->[���� �� ��] = [�������� ��]
	 *
	 * @param integer $id    ������������� ��������� ����
	 * @param string  $addit ������� �������
	 * @param string  $order ����������
	 * @return integer ������ ���������� 1
	 */
	function GetRow($id = "", $addit = "", $order = ""){
		$current = get_class($this);
		if ($id) $addit = $this->pr_key."='$id'" . $addit;
		if ($order) $order = " ORDER BY ".$order;
		$out = $GLOBALS['DB']->row("SELECT * FROM $current WHERE ($addit)".$order);
		foreach ($out as $key => $value){
			$this->$key = $value;
		}
		return 1;
	}
	
	/**
	 * ����� ��� ���� ������������ ������� (�� ������� ��� ���, � ����������� ��� ���)
	 *
	 * @param string $orderby ����������, �� ��������� �� �����
	 * @param string $filter  ������� �������, �� ��������� �� �����
	 * @return array ������ �������
	 */
	function GetAll($orderby = "", $filter=""){
		$current = get_class($this);
		$sql = "SELECT * FROM $current";
		if ($filter) $sql .= " WHERE ( $filter )";
		if ($orderby) $sql .= " ORDER BY $orderby";
		else $sql .= " ORDER BY ".$this->pr_key;
		return $GLOBALS['DB']->rows($sql);
	}
	
	/**
	 * �������� ����� ���������� � �������
	 *
	 * @param mixed   $error     ���������� ��������� �� �������
	 * @param integer $return_id ���������� ��� ��� �� ��������� ������ (0 - �� ����������, 1 - ����������)
	 * @return integer -1 - ���� �������� ������, 0 - ���� ����� �������� $return_id = 0 � ��� ������ �������, �� ��������� ������
	 */
	function Add(&$error, $return_id = 0){
		$current = get_class($this);
		$class_vars = get_class_vars(get_class($this));
		$fields = array();
		$vals = array();
		foreach ($class_vars as $name => $value) {
    		if (isset($this->$name) && $name != "pr_key"){
    			$fields[] = $name;
				$vals[] = $this->$name;
    		}
		}
		$fld = implode(", ",$fields);
		$vls = "'".implode("', '",$vals)."'";
		$sql = "INSERT INTO $current($fld) VALUES ($vls)";
		if ($return_id) $sql .= " RETURNING $this->pr_key";
		$res = $GLOBALS['DB']->query($sql);
		if ($GLOBALS['DB']->error)
			return -1;
		else{
			if ($return_id) {
				list($out) = pg_fetch_row($res);
				return $out;
			}
		 return 0;
		}
	}
	
	/**
	 * ������� ������ �� �������
	 *
	 * @param integer $id     �� ��������� ����
	 * @param string  $addit  ������� �������� (�� ��������� ��� ���, �� ��� �����������)
	 * @return string ��������� �� ������
	 */
	function Del($id, $addit = ""){
		$current = get_class($this);
		if ($id) $addit = $this->pr_key."='$id'" . $addit;
		if ($GLOBALS['DB']->query("DELETE FROM $current WHERE $addit")) {
			return '';
		} else {
			return 'DB Error';
		}
	}
	
	/**
	 * ���������������� ����� ������ ����������� �� �������
	 * ������ ������ ��������� ���������� � ������ �� �������, ��� � ����� ������
	 *
	 * @param array $arr  ������ ����������
	 * @return integer ������ ���������� 0
	 */
	function BindRequest($arr, $force = false){
		$class_vars = get_class_vars(get_class($this));
		foreach ($class_vars as $name => $value) {
				if ($force || isset($arr[$name])){
	   				$this->$name = ($force && !isset($arr[$name])) ? '' : $arr[$name];
				}
		}
		return 0;
	}
	
	/**
	 * �������� ������������������� �� ����������� ����
	 *
	 * @param array $reqvs 	������ � ������� ����������� �����
	 * @return array 		��������� �� �������
	 */
	function check_required($reqvs){
		foreach($reqvs as $varname){
			if (!isset($this->$varname) || !$this->$varname)
				$error[$varname] = "���� ��������� �����������";
		}
		return $error;
	}
	
}
?>