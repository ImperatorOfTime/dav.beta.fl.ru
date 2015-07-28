<?php

/**
 * ������� ����� ������
 *
 */
abstract class atservices_model 
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
     * @todo ��������� ��������� ��������� � ����������� ����� ������?
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
    
}