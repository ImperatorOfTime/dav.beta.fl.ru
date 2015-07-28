<?php

/**
 * ����� ������ � ���.������ �� ��������� ������
 */
class op_codes_price 
{
    /**
     * ��� �� ������ �� ������ ������ �������
     * ��������� ������������ ��������� � �� � ������ ����� ����� ������ �������
     * 
     * @var type 
     */
    static protected $_cache_data = array();


    const OP_CODES_PRICE_MEMCACHE_TAG     = 'getAllOpCodesPrice';
    //�� �����, �� ���� ���� ������ ���� ������ �������� ����� ��. ����
    const OP_CODES_PRICE_MEMCACHE_LIFE    = 0; 
    
 
    
    /**
     * ������ � �������
     * 
     * @return type
     */
    public static function clearCache()
    {
        $memBuff = new memBuff;
        return $memBuff->delete(self::OP_CODES_PRICE_MEMCACHE_TAG);
    }
    
    
    
    /**
     * �������� ��� ���
     * 
     * @global type $DB
     * @return boolean
     */
    public static function updateCache()
    {
        global $DB;
        
        self::$_cache_data = null;
        $data = $DB->rows("SELECT * FROM ". get_class($this));
        
        if ($data) {
            
            //����������� � ������� ����� �����������
            foreach($data as $el) {
                self::$_cache_data[$el['op_code']][$el['param']] = $el['sum'];
            }
            
            $memBuff = new memBuff;
            return $memBuff->set(
                    self::OP_CODES_PRICE_MEMCACHE_TAG, 
                    self::$_cache_data, 
                    self::OP_CODES_PRICE_MEMCACHE_LIFE);
        }
        
        return false;
    }


    /**
     * ��������� ���� ������ ���
     * 
     * @return boolean / array
     */
    public static function getAllOpCodesPrice($refresh = false)
    {
        if (!empty(self::$_cache_data) && !$refresh) {
            return self::$_cache_data;
        }
        
        self::updateCache();
        
        return self::$_cache_data;
    }
    
    
    /**
     * �������� ���.���� ������ �� ������ � ����������
     * 
     * @param type $op_code - �����
     * @param type $param - �������� ��� ������ ����������
     * @return type
     */
    public static function getOpCodePrice($op_code, $param = array())
    {
        $param = !is_array($param)?array($param):$param;
        $param_key = (!$param || empty($param))?'0':implode('_', $param);
        
        $data = self::getAllOpCodesPrice();
        
        if (!isset($data[$op_code][$param_key])) {
            //������� �������� ��������� ����������� ��� ���������� ������
            $price = @$data[$op_code]['0'];
        } else {
            //�������� ���.����
            $price = $data[$op_code][$param_key];
        }
        
        return $price;
    }
    
    
}