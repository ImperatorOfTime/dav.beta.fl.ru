<?php

/**
 * ����� ��� ������ �� ���������� ���-������� ��� �������� � �������������
 *
 * @author danil
 */
class SeoValues {

    /**
     * ���������� ����������
     */
    const SIZE_TITLE = 4;
    
    /**
     * ���������� �������� ����
     */
    const SIZE_KEY = 10;
    const SIZE_TEXT = 4;
    
    const TABLE = 'seo_tags';

    /*
     * ��������� � seo-������� � �������� �������� �����
     */
    protected $tu_titles;

    /*
     * ��������� � seo-������� � �������� �������� �����������
     */
    protected $f_titles;

    /*
     * �������� ����� � �������� ��������
     */
    protected $keys;

    /*
     * seo-������ � �������� �������� �����
     */
    protected $tu_texts;

    /*
     * seo-������ � �������� �������� �����������
     */
    protected $f_texts;
    
    
    /**
     * ��������� ����� ������
     * @param integer $id �� �������� 
     * @param bool $is_spec ������������� ��� ������
     */
    function initCard($id, $is_spec = true) {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE parent_id = ?i AND is_spec = ?;";
        $row = $this->db()->cache(900)->row($sql, (int)$id, (bool)$is_spec);
        if($row) {
            for($i = 1; $i <= self::SIZE_KEY; $i++) {
                $this->keys[$i] = $row['key_'.$i];
            }
            
            for($i = 1; $i <= self::SIZE_TITLE; $i++) {
                $this->tu_titles[$i] = $row['tu_title_'.$i];
                $this->f_titles[$i] = $row['f_title_'.$i];
            }
            
            for($i = 1; $i <= self::SIZE_TEXT; $i++) {
                $this->tu_texts[$i] = $row['tu_text_'.$i];
                $this->f_texts[$i] = $row['f_text_'.$i];
            }
        }
    }
    
    /**
     * ��������� ������ ������� ��� �������
     */
    public function getList() {
        $sql = "SELECT st.*, p.name as prof_title, pg.name as prof_group_title 
            FROM " . self::TABLE . " st
                LEFT JOIN professions p ON p.id = st.parent_id
                LEFT JOIN prof_group pg ON pg.id = st.parent_id
                ORDER BY st.id;";      
        return $this->db()->rows($sql);
    }
    
    /**
     * ��������� ����� ������ ��� �������
     */
    public function getCardById($id) {
        $sql = "SELECT st.*, p.name as prof_title, pg.name as prof_group_title 
            FROM " . self::TABLE . " st
            LEFT JOIN professions p ON p.id = st.parent_id
            LEFT JOIN prof_group pg ON pg.id = st.parent_id
            WHERE st.id = ?i
            LIMIT 1;";      
        return $this->db()->row($sql, (int)$id);
    }
    
    public function save($id, $post) {
        return $this->db()->update(self::TABLE, $post, 'id = ?i', (int)$id);
    }
    
    /**
     * ���������� ���� �������� ����� �� �����
     * @param int $num ����
     * @return string
     */
    public function getKey($num) {
        return $this->keys[$num];
    }
    
    /**
     * ���������� ������ �� ���� �������� ����
     * @param int $count ���������� ������������ �������� ����
     * @return string ��������� ������
     */
    public function getKeysString($count = self::SIZE_KEY) {
        if ($this->keys) {
            $keys = array_diff($this->keys, array(''));
            $keys = array_slice($keys, 0, $count);
            return implode(', ', $keys);
        }
        return '';
    }
    
    /**
     * ���������� ���� �� ���������� ����������� �� �����
     * @param int $num ����
     * @return string ���������
     */
    public function getFTitle($num) {
        return isset($this->f_titles[$num]) ? $this->f_titles[$num] : '';
    }
    
    /**
     * ���������� ���� �� ������� ����������� �� �����
     * @param int $num ����
     * @return string �����
     */
    public function getFText($num) {
        return isset($this->f_texts[$num]) ? $this->f_texts[$num] : '';
    }
    
    /**
     * ���������� ���� �� ���������� ����� �� �����
     * @param int $num ����
     * @return string ���������
     */
    public function getTUTitle($num) {
        return isset($this->tu_titles[$num]) ? $this->tu_titles[$num] : '';
    }
    
    /**
     * ���������� ���� �� ������� ����� �� �����
     * @param int $num ����
     * @return string �����
     */
    public function getTUText($num) {
        return isset($this->tu_texts[$num]) ? $this->tu_texts[$num] : '';
    }
    
    /**
     * @return $DB
     */
    function db() {
        return $GLOBALS['DB'];
    }

}
