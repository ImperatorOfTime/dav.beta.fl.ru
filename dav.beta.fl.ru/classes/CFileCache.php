<?php
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/memBuff.php');

/**
 * �������� ���������� � ������.
 * ��� ���������� ������������ � �������� CFile::GetInfo() � CFile::GetInfoById(), 
 * �.�. ��� ������������� ����� ����� ����������� CFile.
 * ��� ��������� �������� ������ �������� � ��, ����� ���� ����� ��������� ���������� � �����,
 * �� ����� �������� CFile (��������, ��. stdf.php, viewAttachLeft())
 * ����� ���������� ������� � ������ �������� ��������, � �� ����������
 * ����������� � ������.
 */
class CFileCache {
    
    const MEM_PREFIX = 'CFileCache.';
    const MEM_LIFE   = 1200; // ����� ����� ����.
    
    /**
     * @see CFileCache::$_memAllowedForceLag
     */
    const MEM_ALLOWED_FORCE_COEFF = 10;

    /**
     * @var CFileCache   ��������� ������
     */
    private static $_inst = NULL;
    
    /**
     * ����� � �������� � ������� ������ �����, �� ��������� �������� ��� �� ����� ��� �� �������� ���� �����������
     * ��� ���. � ����� ������ ������ ��� � ���.
     * @see CFileCache::MEM_ALLOWED_FORCE_COEFF
     * @see CFileCache::__construct()
     *
     * @var integer
     */
    private $_memAllowedForceLag;

    /**
     * ���, �������� ���������� � ������.
     * ����� �������� �� ������ [path+fname] ��� [id].
     * 
     * @var array
     */
    private $_cache = array();
    
    /**
     * @var memBuff
     */
    private $_memBuff;

    /**
     * ����� ��� ��������� ����������� ����������.
     * ��������� ��������� ��������� ����� ����� ����������� ��������
     * @return CFileCache
     */
    static function getInstance() {
        if(!self::$_inst)
            self::$_inst = new CFileCache();
        return self::$_inst;
    }

    /**
     * �����������
     * @see CFileCache::getInstance()
     */
    private function __construct() {
        $this->_memBuff = new memBuff();
        $this->_memAllowedForceLag = self::MEM_LIFE * self::MEM_ALLOWED_FORCE_COEFF;
    }
    
    /**
     * ��������, ����� �� ���������� ����.
     * ���������, ����� ���� ������� ���������� � ��� �� ��� �������� ����������� (����� ���� ������� ������ ����������).
     *
     * @return boolean   ��|���
     */
    private function _memAllowed(&$row) {
        return ( !is_null($row['virus']) || strtotime($row['modified']) < time() - $this->_memAllowedForceLag );
    }

    /**
     * ����������.
     * @see CFileCache::getInstance()
     */
    function __destruct() {
        foreach($this->_cache as $key=>$row) {
            if ($this->_memAllowed($row)) {
                $this->_memBuff->add($this->_memkey($key), $row, self::MEM_LIFE);
            }
        }
    }

    /**
     * ������ ����(�) � ���.
     * @param array $rows   ���� ��� ��������� ������ (�������� � ������).
     */
    function put($rows) {
        if (!$rows) return;
        if (isset($rows['fname'])) {
            $rows = array($rows);
        }
        foreach ($rows as $r) {
            $k1 = self::_k($r, 1);
            $this->_cache[$k1] = $r;
            $this->_cache[self::_k($r, 2)] = &$this->_cache[$k1];
        }
    }

    /**
     * ������ ���� �� ����. 
     *
     * @param string|integer $key   ���� [path+fname] ��� [id]
     * @return array   ���������� � �����.
     */
    function get($key) {
        if( !($row = $this->_cache[$key]) ) {
            $row = $this->_memBuff->get(self::_memkey($key));
        }
        return $row;
    }

    /**
     * ������� ���� �� ����. 
     * @param string|integer $key   ���� [path+fname] ��� [id]
     */
    function del($key) {
        if($r = $this->_cache[$key]) {
            unset($this->_cache[self::_k($r, 1)]);
            unset($this->_cache[self::_k($r, 2)]);
        }
        if($r = $this->_memBuff->get(self::_memkey($key))) {
            $this->_memBuff->delete(self::_memkey(self::_k($r, 1)));
            $this->_memBuff->delete(self::_memkey(self::_k($r, 2)));
        }
    }

    /**
     * ���������� ���� ���� ��� ������� ���� �� ������ ����� ��� ���������� � ��� ($this->_cache).
     *
     * @param array $r   ���� (������ � ������)
     * @param integer $t   ��������� ��� ����� (1:��; 2:path+fname).
     * @return string   ����.
     */
    private static function _k(&$r, $t) {
        return $t == 2 ? $r['path'].$r['fname'] : $r['id'];
    }

    
    /**
     * ���������� ���� ��� ���������� � ������.
     *
     * @param string $key    ���� ����� (��. ������� _k()).
     * @return string   ����.
     */
    private static function _memkey($key) {
        return md5(self::MEM_PREFIX.$key);
    }
}

// ������������.
$GLOBALS['CFileCache'] = CFileCache::getInstance();
