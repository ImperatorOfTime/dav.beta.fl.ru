<?php

/**
 * Class �Model
 * 
 * ������� ����� ������ ������
 */
abstract class CModel 
{
    /**
     * ��������� ���������
     * @var int
     */
    protected $limit = 0;
    protected $offset;
    
    
    /**
     * ���������� ��������� ���������
     * 
     * @param int $limit
     * @param int $page
     * @return $this
     */
    public function setPage($limit, $page = 1) 
    {
        $page = ($page > 0) ? $page : 1;
        $this->limit = $limit;
        $this->offset = ($page - 1) * $limit;

        return $this;
    }
    
    
    /**
     * ��������� SQL ������ ������������ �� ���-�� � ��������
     * 
     * @param string $sql
     * @return string
     */
    protected function _limit($sql)
    {
        if ( $this->limit ) 
        {
            $sql .= ' LIMIT ' . $this->limit . ($this->offset? ' OFFSET ' . $this->offset: '');
        }        
        
        return $sql;
    } 
    
    
    /**
     * @return DB
     */
    public function db()
    {
        return $GLOBALS['DB'];
    }    
    
    
    
    /**
     * ������� ���� ����
     * @return �Model
     */
    public static function model(array $options = array()) 
    {
        $class = get_called_class();
        return new $class($options);
    }
}