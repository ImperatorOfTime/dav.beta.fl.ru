<?
/**
 * ����� ��� ��������, ��������� � ��������� ����� uuid 
 *
 */
class codes
{
	/**
	 * ID ������������
	 *
	 * @var integer
	 */
	public $user_id;
	/**
	 * ��������������� ���
	 *
	 * @var string
	 */
	public $code;
	
	/**
	 * ��� ���� (1)
	 *
	 * @var integer
	 */
	public $type;
	
	/**
	 * ���� ��������
	 *
	 * @var string
	 */
	public $cdate;
	
	
	public $pr_key = "code";
	
	/**
	 * ������� ���
	 *
	 * @param stting  $error	  ���������� ������
	 * @param integer $return_id  ���������� �� �� ��������� ������ (0 - ���, 1 - ��)
	 * @return string ��������������� ���
	 */
	function Add( $error, $return_id = 0 ) {
	    if( !$this->cdate ) {
			$this->cdate = date("c");
		}
		
		if ( !$this->code ){
            mt_srand();
			$this->code = md5( $this->user_id.$this->cdate.uniqid(mt_rand(), true) );
		}
		
		$aData = array( 'user_id' => $this->user_id, 'type' => $this->type, 'code' => $this->code, 'cdate' => $this->cdate );
		
		$GLOBALS['DB']->insert( get_class($this), $aData );
		
		return $this->code;
	}
	
	/**
	 * �������� ������ ���� � ������������� ���������� ������.
	 * 
	 * @param  integer $id ������������� ��������� ����
	 * @return bool true - �����, false - ������
	 */
	function GetRow( $id = '' ) {
	    global $DB;
	    
	    $bRet = true;
	    $aRow = $DB->row( 'SELECT * FROM '. get_class($this) .' WHERE '. $this->pr_key .' = ?', $id );
	    
	    if ( is_array($aRow) && count($aRow) ) {
    	    foreach ( $aRow as $key => $val ) {
    			$this->$key = $val;
    		}
	    }
	    else {
	        $bRet = false;
	    }
	    
	    return $bRet;
	}
	
	/**
	 * ������� ���
	 *
	 * @param integer $uid  �� �����
	 * @param integer $type ��� ���� (� ���� ������� ������ ���� �������� � ��� ��������� �������� 1)
	 * @return string ��������� �� ������
	 */
    function DelByUT( $uid, $type ) {
        global $DB;
        $DB->query( 'DELETE FROM codes WHERE type = ? AND user_id = ?', $type, $uid );
		return $DB->error;
    }
}
?>