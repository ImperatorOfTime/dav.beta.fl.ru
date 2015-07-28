<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/op_codes_price.php");

/**
 * ����� ��� ������ � ������ �������� ��������
 *
 */
class op_codes
{
	/**
	 * �� ��������
	 *
	 * @var integer
	 */
	public $id;
	
	/**
	 * �������� ��������
	 *
	 * @var integer
	 */
	public $op_name;
	
	/**
	 * ��������� ��������
	 *
	 * @var integer
	 */
	public $sum;
	
	/**
	 * �������� ���� �������
	 *
	 * @var string
	 */
	public $pr_key="id";
	
    
    
    /**
     * ��� �� ������ ������� �� ������ ������ �������
     * ��������� ������������ ��������� � �� � ������ ����� ����� ������ �������
     * 
     * @var type 
     */
    static protected $_cache_data = array();


    const OP_CODES_MEMCACHE_TAG     = 'getAllOpCodes';
    const OP_CODES_MEMCACHE_LIFE    = 86400; //�� �����


    /**
	 * ���������� ���� �������� ��������
	 * 
	 * @param  string|array $codes ���� ��� ��������� ����� �������� ��������
	 * @return array ��������������� ������ ��������
	 */
	function getCodes($codes) {
	    if(is_array($codes)) $codes = implode(',', $codes);
        if(!$codes) $codes = '0';
        $ret = array();
        if ( $rows = $GLOBALS['DB']->rows("SELECT * FROM op_codes WHERE id IN ({$codes})") ) {
            foreach($rows as $row)
                $ret[$row['id']] = $row;
        }
	    return $ret;
	}

	/**
	 * ����� ������ ������������� ���� �� �����
	 *
	 * @param  integer $uid �� ����
	 * @param  string $error ���������� ��������� �� ������
	 * @param  string $fieldname ���� �������
	 * @return string ������ ����
	 */
	function GetField($uid, &$error, $fieldname){
		$current = get_class($this);
		return $GLOBALS['DB']->val("SELECT {$fieldname} FROM {$current} WHERE {$this->pr_key} = ?", $uid);
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
     * ������ ��� ������ � �������
     * 
     * @return type
     */
    public static function clearCache()
    {
        $memBuff = new memBuff;
        return $memBuff->flushGroup(self::OP_CODES_MEMCACHE_TAG);
    }
    
    
    /**
     * ��������� ���� ������ �������
     * 
     * @return boolean
     */
    static function getAllOpCodes($refresh = false)
    {
        if (!empty(self::$_cache_data) && !$refresh) {
            return self::$_cache_data;
        }
        
        $error = null;
        
        $memBuff = new memBuff();
        $data = $memBuff->getSql($error, " 
                SELECT * FROM ". get_class($this) ."
            ", 
            self::OP_CODES_MEMCACHE_LIFE, 
            true, 
            self::OP_CODES_MEMCACHE_TAG);
        
        if($data && !$error) {
            
            foreach ($data as $el) {
                self::$_cache_data[$el['id']] = $el;
            }
            
            return self::$_cache_data;
        }
        
        return false;
    }
   


    /**
     * �������� ������ ���������� ������
     * 
     * @param type $opCode
     * @return boolean
     */
    static function getDataByOpCode()
    {
        $args = func_get_args();
        $opCode = $args[0];
        unset($args[0]);
        $param = @$args;
        
        //��������� ���� �� ���.���� ��� ������
        $price = op_codes_price::getOpCodePrice($opCode, $param);

        if ($price) {
            return array('sum' => $price);
        }
        
        //���� ��� �� ������������ ������� ���� �� op_codes
        self::getAllOpCodes();
        
        if (isset(self::$_cache_data[$opCode])) {
           return self::$_cache_data[$opCode]; 
        }
        
        return false;
    }
    
    
    
    /**
     * �������� ������� ���� ���������� ������
     * 
     * @param type $opCode
     * @return boolean
     */
    public static function getPriceByOpCode($opCode)
    {
        $opCodeData = self::getDataByOpCode($opCode);
        return $opCodeData['sum'];
    }
    
    
    
    
    /**
     * �������� ���� �� ������ ��� ������������� ���.���
     * 
     * @param type $opCode
     * @return boolean
     */
    public static function getPriceByOpCodeWithoutDiscount($opCode)
    {
        self::getAllOpCodes();
        
        if (isset(self::$_cache_data[$opCode])) {
           return self::$_cache_data[$opCode]['sum']; 
        }
        
        return false;
    }

        
    
    
    /**
     * ���������� ����� ������ ��� ���������
     * @param type $opCode
     * @return type
     */
    public static function getLabel($opCode)
    {
        self::getAllOpCodes();
        
        if (isset(self::$_cache_data[$opCode])) {
            $opCodeData = self::$_cache_data[$opCode];
            return isset($opCodeData['ga_label']) ? $opCodeData['ga_label'] : '';
        }
    }
        
    
       
}