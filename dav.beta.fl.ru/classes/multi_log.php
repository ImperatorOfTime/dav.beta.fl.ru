<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/classes/log.php');

/**
 * ��������� ������
 * @see log::addAlternativeMethodSave();
 */
interface LogSave 
{
    function setLogName($name);
    function write($str);
    function setStr($str);
    function __toString();
}

/**
 * ����� ��� ������ ����� � ���� ������ (stat)
 */
class log_pskb //implements LogSave
{
    private $_db; // ����������� � ���� ������
    
    /**
     * �������� ���� @see log::$_logname;
     * @var string
     */
    private $_name;
    
    /**
     * ������������ ��������� � ���, ���� ���������� �� ��� ������ ������� ������ ��
     * 
     * @var string 
     */
    private $_message;
    
    /**
     * ������������ ������ ��� ������� � ���� �� ��������� � ������ log
     * @var string
     */
    public  $alias = 'log_pskb';
    
    /**
     * �������� �������� ����� � ���� ������, ��� ���� ������� ������ 2 ������� ���������
     */
    CONST INTERVAL_DATA = '2 month';
    
    /**
     * ����������� ������
     */
    public function __construct() {
        $this->_db = new DB('stat');
    }
    
    /**
     * ���������� ������������ ������
     * 
     * @return string
     */
    public function __toString() {
        return $this->alias;
    }
    
    /**
     * ������ ��� ����  @see log::$_logname;
     * 
     * @param string $name  �������� ����
     */
    public function setLogName($name) {
        $this->_name = current(explode("-", $name));
    }
    
    /**
     * ���������� ��� ����
     * 
     * @return type
     */
    public function getLogName() {
        return $this->_name;
    }
    
    /**
     * ��������� ���� �� �������������� ������ ��� ������
     * 
     * @return boolean
     */
    public function isStr() {
        return (trim($this->_message) != '');
    }
    
    /**
     * ������ ������ ��� ������ � ���
     * 
     * @param string $str ��������� ��� ������
     */
    public function setStr($str) {
        $this->_message = $str;
    }
    
    /**
     * ���������� ������ ��� ������ � ���
     * 
     * @return string
     */
    public function getStr() {
        return $this->_message;
    }
    
    /**
     * ���������� ������ � �������
     * 
     * @param string $str   ��������� ��� ������
     * @return boolean
     */
    public function write($str = '') {
        if(is_array($str)) {
            $str = serialize($str);
        }
        
        if($this->isStr()) { // ������ ������������
            switch(basename($this->getLogName())) {
                case 'income':
                    $content    = unserialize($str);
                    $logs       = $content['response'];
                    $logs['id'] = $logs['nickname'];
                    break;
                default:
                    $content = unserialize($str);
                    $logs    = json_decode(iconv('cp1251', 'utf8', $content['response']), 1);
                    break;
            }
        } else {
            $logs['id'] = 1;
        }
        
        if(!$logs['id']) {
            $sql = "INSERT INTO _log_pskb (date_created, link_id, logname, log) VALUES ";
            if(!$logs) return false;
            $cnt = $content;
            foreach($logs as $log) {
                $cnt['param']    = '{"id":[' . $log['id'] . ']}';
                $cnt['response'] = json_encode($log);
                $a_sql[] = $this->_db->parse("(NOW(), ?, ?, ?)", $log['id'], $this->getLogName(), iconv('utf8', 'cp1251', serialize($cnt)) );
            }
            $sql = $sql . implode(", ", $a_sql);
            $res = $this->_db->query($sql);
        } else {
            $sql = "INSERT INTO _log_pskb (date_created, link_id, logname, log) VALUES (NOW(), ?, ?, ?)";
            $res = $this->_db->query($sql, $logs['id'], $this->getLogName(), $str);
        }
        $this->getStr(null); // ������� ������
        return $res;
    }
    
    /**
     * ������ �� ������������� ������� ����, ��������� ������ � ��������� ������
     * ����� ����� ����� ������ ��� �������� �������� � ����� ���������
     * 
     * @todo �� ���� �� ������� ������� ����� ������ ��� ������� ����� ������� ����� ������ 100�, ����� ���� ����� ��������� ���-�� ����� ������������
     * 
     * @return type
     */
    public function clearCloneData($lc_id = false) {
        if($lc_id) {
            $sWhere = $this->_db->parse('WHERE link_id = ?i', $lc_id);
        }
        
        $sql = "DELETE FROM _log_pskb
                USING (
                    SELECT MAX(id) as max_id, MIN(id) as min_id, link_id, logname, log
                    FROM _log_pskb 
                    {$sWhere}
                    GROUP BY link_id, logname, log
                ) as _tbl
                WHERE _log_pskb.link_id = _tbl.link_id
                AND _log_pskb.logname = _tbl.logname
                AND _log_pskb.log = _tbl.log
                AND _log_pskb.id <> _tbl.min_id
                AND _log_pskb.id < _tbl.max_id";
        
        return $this->_db->query($sql);
    }
    
    /**
     * ������ ������ ������ � ���� � ���� ���������� ������� �� �� �������
     * 
     * @param boolean $is_delete    ������� ��� ��� �� ����� ������ ������
     * @return boolean
     */
    public function packOldData($is_delete = false) {
        $sql  = "SELECT * FROM _log_pskb WHERE date_created < NOW() - interval ?";
        $rows = $this->_db->rows($sql, self::INTERVAL_DATA);
        if(!$rows) return false;
        $pack = serialize($rows); 
        
        $log = new log("stat_save/stat_{$this->alias}-".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        $log->writeln($pack);
        
        if($is_delete) {
            return $this->clearOldData();
        }
        return true;
    }
    
    /**
     * ������ ������ ������
     */
    public function clearOldData() {
        $sql = "DELETE FROM _log_pskb WHERE date_created < NOW() - interval ?";
        return $this->_db->query($sql, self::INTERVAL_DATA);
    }
    
    /**
     * ����� ��� ������ ����������� � ������� �����
     * 
     * @param integer $link_id ���� ����� ��, ����� ������ �� ������ ������� ������� ��� ������� ��
     * @return array
     */
    public function getNameGroupLog($link_id = false) {
        if(!$link_id) {
            $sql = "SELECT logname FROM _log_pskb WHERE link_id = ? GROUP BY logname";
        } else {
            $sql = "SELECT logname FROM _log_pskb GROUP BY logname";
        }
        return $this->_db->col($sql, $link_id); 
    }
    
    /**
     * ����� �� �����
     * 
     * @param type $param       ��������� ������
     * @return type
     */
    public function findLogs($param, $limit = 100) {
        $limit = intval($limit);
        if(trim($param['query']) != '') { // ������ ������
            $param['query'] = trim($param['query']);
            $aWhere[] = $this->_db->parse("log LIKE ?", "%{$param['query']}%");
        }
        if($param['link_id']) { // �� ������
            $aWhere[] = $this->_db->parse("link_id = ?i", $param['link_id']);
        }
        if(trim($param['logname']) != '') { // ������ ������
            $aWhere[] = $this->_db->parse("logname = ?", trim($param['logname']));
        }
        
        if($aWhere) {
            $sWhere = "WHERE " . implode(" AND ", $aWhere);
        }
        
        $sql  = "SELECT * FROM _log_pskb {$sWhere} ORDER BY date_created DESC LIMIT {$limit};";
        return $this->_db->rows($sql);
    }
}