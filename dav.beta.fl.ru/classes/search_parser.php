<?php

/**
 * 
 * ����� ��� ������ � ������ �������. 
 * ���������� ������ ������������ �� �������� ������ /search ��� ���������
 * (http://beta.free-lance.ru/mantis/view.php?id=13466)
 * 
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");


class search_parser {
    
    const MCACHE_KEY_NAME = 'kword_search_keys_js';
    
    const MCACHE_TIME = 1800;

    private static $instance;
    /**
     * ���� ������ � �����
     * 
     * @var string 
     */
    public $sphinxLog = '/var/www/_sphinx/log/query.log.1.gz';

    /**
     * ��������� �� �������� ����
     * 
     * @var resource 
     */
    private $_resource = NULL;

    /**
     * ���� ���� ����������
     * 
     * @var string 
     */
    private $_logfile = '/search_parser/error.log';

    /**
     * @var log 
     */
    private $_log;

    /**
     * ������ ���� ([query-date] query-time [match-mode/filters-count/sort-mode
     *               total-matches (offset,limit) @groupby-attr] [index-name] query)
     * ����� ���, ����� ���
     * 
     * @var string 
     */
    private $_sphinxLogFormat = "/\[[a-z]+\/\d+\/.*? (\d+) \(\d+,\d+\)\] \[(.*?)\] (.*?)$/si";
    
    /**
     * ������ �� �����������
     * 
     * @var DB 
     */
    private $_db;
    

    private function __construct($debug) {        
        if (SERVER == 'beta') {
            $this->sphinxLog = '/var/www/_sphinx/beta/log/query.log.1.gz';
        } else if (SERVER == 'alpha') {
            $this->sphinxLog = '/var/www/_sphinx/alpha/log/query.log.1.gz';
        }

//        if (IS_LOCAL) {
////            $this->sphinxLog = '/var/lib/sphinx/log/query.1.log.gz';
//            $this->sphinxLog = '/home/sergey/Documents/Projects/FREE-LANCE.ru/tmp/query.log.1.gz';
//        }
        
        $this->_db = new DB('stat');
        
        if ($debug) {
            $this->_log = new log($this->_logfile, 'a');
        }
    }
    
    /**
     *
     * @return search_parser 
     */
    public static function factory($debug = FALSE) {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c($debug);
        }

        return self::$instance;
    }

    public function __destruct() {
        if ($this->_resource) {
            fclose($this->_resource);
        }
    }

    /**
     * 
     * ����� ������������� ��� � ����������� ������ �� ���� �������� 
     * (� ������������ � ��������� ������)
     * 
     */
    public function parseRaw() {
        $file = $this->_getFile();

        if (!$file) {
            return;
        }
        $this->_log("��������� ���� '$this->sphinxLog'.");
        
        $lastmod = @filemtime($this->sphinxLog); // @ - #0015499 stat failed
        if (date('Y-m-d', $lastmod) != date('Y-m-d')) {
            $this->_log("����� ������ ���.");
            return;
        }
        
        $a_sql = array();
        $cnt = 0;
        $cnt_all = 0;
        while ($str = fgets($file)) {
            if (preg_match($this->_sphinxLogFormat, $str, $data)) {
                if (!$data[1]) continue;
                
                //��� ������� �������
                $index = explode(";", $data[2]);
                $index = trim($index[0]);
                
//                $words = explode(" ", $data[3]);
//                foreach ($words as $word) {
//                    $word = trim($word);
                    $word = trim($data[3]);
                    if (!$word || strlen($word) < 3) continue;
                    
                    $table_name = 'search_kwords_raw';
                    
                    if (in_array($index, array('users', 'users_na', 'projects'))) {
                        $_index = $index;
                        if ($index == 'users_na') {
                            $_index = 'users';
                        }
                        $table_name .= '_' . $_index;
                    }
                    
                    $a_sql[$table_name][] = array(
                        'query' => change_q_x($word, 1, 0),
//                        'query' => $word,
                        'match_cnt' => $data[1],
                        'index_name' => $index,
                    );
                    
                    $cnt++;
                    $cnt_all++;
//                }

                
                if ($cnt > 1000) {
                    
                    foreach ($a_sql as $table => $data) {
                        $ret = $this->_db->insert($table, $data);
                        if ($ret === FALSE) {
                            $this->_log("������ ������ � ����: {$this->_db->error}", "error");
                            return;
                        }
                    }
                    
                    $a_sql = array();
                    $cnt = 0;
                }
            }
        }
        
        if ($cnt) {
            foreach ($a_sql as $table => $data) {
                $ret = $this->_db->insert($table, $data);
                if ($ret === FALSE) {
                    $this->_log("������ ������ � ����: {$this->_db->error}", "error");
                    return;
                }
            }
        }
        $this->_log("������ ���� ���������. $cnt_all ����� ���������.");
    }
    
    /**
     * ��������� "�����" ������ � ��������� � ������� search_kwords_tmp
     * 
     * @param type $from 
     */
    public function filterRaw($from = '') {
        $type = "�� ���� �������� (����� projects, users, users_na)";
        if ($from) {
            $type = "�� ������� '$from'";
        }
        $this->_log("���������� �������� {$type}.");
        
        $sql = "SELECT * FROM search_kwords_parser('$from')";
        $res = $this->_db->query($sql);
        
        if ($this->_db->error) {
            $this->_log("������ ������ � ����: {$this->_db->error}", "error");
        }
    }
    
    /**
     * ���������� � �������� ������� �� �������� ��������
     * 
     * @param type $rule_id     �� ������� ���������� (search_kwords_filters_rules)
     * @param type $query       ����� �������
     * @return type 
     */
    public function filterByRule($rule_id, $query) {
        $query = change_q_x($query, 1, 0);
        $sql = "UPDATE search_kwords 
                SET query = RTRIM(search_kwords_filter_query(query, ' word '||replace(rr.pattern, '%s', '$query')))
                FROM search_kwords_filters_rules rr
                WHERE rr.id = $rule_id AND query LIKE '%{$query}%';
                DELETE FROM search_kwords WHERE LENGTH(RTRIM(query)) = 0;";
                
        $sql .= "UPDATE search_kwords_top 
                SET query = RTRIM(search_kwords_filter_query(query, ' word '||replace(rr.pattern, '%s', '$query')))
                FROM search_kwords_filters_rules rr
                WHERE rr.id = $rule_id AND query LIKE '%{$query}%';
                DELETE FROM search_kwords_top WHERE LENGTH(RTRIM(query)) = 0;";
                
        $mem = new memBuff();
        $mem->delete(self::MCACHE_KEY_NAME);
        
        return $this->_db->query($sql);
    }
    
    /**
     * ������� �������, ��� (cnt*match_cnt) ������� ������ �������� �������� search_kwords_settings.min_cnt
     * 
     * @return type 
     */
    public function cleanByLimit() {
        $sql = "DELETE FROM search_kwords WHERE cnt*match_cnt < (SELECT min_cnt FROM search_kwords_settings LIMIT 1);";
        return $this->_db->query($sql);
        
    }
    
    /**
     * 
     */
    public function cleanup() {
        
        $sql = "TRUNCATE search_kwords_top RESTART IDENTITY;";
        
        $sql .= "INSERT INTO search_kwords_top (index_type, query, match_cnt, cnt, rate)
                SELECT 0, query, SUM(match_cnt), SUM(cnt), SUM(cnt*match_cnt) as rate FROM ONLY search_kwords_users
                GROUP BY query
                ORDER BY rate DESC;";
        
        $sql .= "INSERT INTO search_kwords_top (index_type, query, match_cnt, cnt, rate)
                SELECT 1, query, match_cnt, cnt, cnt*match_cnt as rate FROM ONLY search_kwords_projects
                ORDER BY rate DESC;";
        
        $sql .= "INSERT INTO search_kwords_top (index_type, query, match_cnt, cnt, rate)
                SELECT 2, query, SUM(match_cnt), SUM(cnt), SUM(cnt*match_cnt) as rate FROM ONLY search_kwords
                GROUP BY query
                ORDER BY rate DESC;";
                
        $mem = new memBuff();
        $mem->delete(self::MCACHE_KEY_NAME);
        
        $res = $this->_db->query($sql);
    }
    
    
    public function getFirstChars() {
        $sql = "SELECT LOWER(SUBSTRING(query, 1, 1)) as chr FROM search_kwords GROUP BY chr";
        $res = $this->_db->rows($sql);
        
        if (!$res) {
            return array();
        }
        
        $out = array();
        foreach ($res as $k => $v) {
            $out[] = $v['chr'];
        }
        
        return $out;
    }

    /**
     * �������� ������ ���������������� ��������
     * 
     * @param type $start   ������ �� ������� �������
     * @param type $limit   
     * @param type $offset
     * @param type $count   ���������� ���-�� ����� ��� �����������
     * @return array             
     */
    public function getQueries($start = '', $limit = 40, $offset = 0, &$count = 0) {
        
        $where = "";
        $limit = " LIMIT $limit OFFSET $offset ";
        
        if ($start != '') {
            $where = " WHERE query LIKE '$start%' ";
        }
        
        if ($start == 'others') {
            $where = " WHERE substring(lower(query) from E'^[a-z�-�0-9]{1}') IS NULL ";
        }
        
        if ($start == 'num') {
            $where = " WHERE substring(lower(query) from E'^[0-9]{1}') IS NOT NULL ";
        }
        
        if ($start == 'all') {
            $where = "";
        }
        
        if (in_array($start, array('users', 'projects'))) {
            $where = '';
            if ($start == 'users') {
                $start = "'users', 'users_na'";
            } else {
                $start = "'projects'";
            }
            $join = "INNER JOIN search_kwords_indexes i ON i.id = k.index_id AND i.idname IN ({$start})";
        } elseif($start == 'more') {
            $where = '';
            $join = "INNER JOIN search_kwords_indexes i ON i.id = k.index_id AND i.idname NOT IN ('users', 'users_na', 'projects')";
        } else {
            $join = "INNER JOIN search_kwords_indexes i ON i.id = k.index_id";
        }
        
        $sql = "SELECT k.*, i.idname FROM search_kwords k 
                {$join}
                $where $limit";
        $res = $this->_db->rows($sql);
        
        if (!$res) {
            return array();
        }
        
        $sql = "SELECT COUNT(*) as cnt FROM search_kwords k {$join} $where";
        $count = $this->_db->col($sql);
        $count = $count[0];
        
        return $res;
    }
    
    /**
     * ������� ������ �� ID
     * 
     * @param int $id       �� �������
     * @return type 
     */
    public function removeQuery($id) {
        return $this->_db->query('DELETE FROM search_kwords WHERE id = ?', $id);
    }
    
    /**
     * �������� ������ ������ ����������
     * 
     * @return array 
     */
    public function getRules() {
        return $this->_db->rows("SELECT * FROM search_kwords_filters_rules");
    }
    
    /**
     * ��������� ������� ����������
     * 
     * @param string $name      ���
     * @param string $pattern   �������
     * @return type 
     */
    public function addRule ($name, $pattern) {
        return $this->_db->insert('search_kwords_filters_rules', array(
            'rule_name' => $name,
            'pattern' => $pattern
        ));
    }
    
    /**
     * ������� ������� ���������
     * 
     * @param int $id       �� �������
     * @return type 
     */
    public function deleteRuleById($id) {
        if (!intval($id)) {
            return FALSE;
        }
        return $this->_db->query("DELETE FROM search_kwords_filters_rules WHERE id = ?", $id);
    }
    
    /**
     * �������� ������ ��������
     * 
     * @return type 
     */
    public function getFilters() {
        $sql = "SELECT f.*, r.rule_name FROM search_kwords_filters f
                INNER JOIN search_kwords_filters_rules r ON r.id = f.filter_rule";
        return $this->_db->rows($sql);
    }
    
    /**
     * ���������� ������ � ������� �������
     * 
     * @param int $id       �� �������
     * @return type 
     */
    public function getFilter($id) {
        return $this->_db->row('SELECT f.*, rr.pattern FROM search_kwords_filters f 
                                INNER JOIN search_kwords_filters_rules rr ON rr.id = f.filter_rule
                                WHERE f.id = ?', $id);
    }
    
    /**
     * ������� ����� ������
     * 
     * @param string $word          ������ ����������
     * @param int $rule             �� ������� ����������
     * @param bool $filterBase      ��������� ���������� ������ � �������� ������� � ������ ���������?
     * @return type 
     */
    public function addFilter($word, $rule, $filterBase = FALSE) {
        if (!$word || !$rule) {
            return false;
        }
        
        $data = array(
            'word' => change_q_x($word, 1, 0),
//            'word' => $word,
            'filter_rule' => $rule
        );
        
        $res = $this->_db->insert('search_kwords_filters', $data, 'id');
        if (!$res) {
            return FALSE;
        }
        
        if ($filterBase) {
            $this->filterByRule($rule, $word);
        }
        
        return $res;
    }
    
    /**
     * �������� ������� �� ��
     * 
     * @param int $id       �� �������
     * @return type 
     */
    public function deleteFilterById($id) {
        if (!intval($id)) {
            return FALSE;
        }
        $sql = "DELETE FROM search_kwords_filters WHERE id = ?";
        
        return $this->_db->query($sql, $id);
    }
    
    /**
     * ���������� ������ � �����������
     * 
     * @return array 
     */
    public function getSettings() {
        return $this->_db->row("SELECT * FROM search_kwords_settings");
    }
    
    /**
     * ������������� ���������
     * 
     * @param array $data       
     * @return type 
     */
    public function setSettings($data) {
        $set = array();
        foreach ($data as $k => $row) {
            $set[] = "$k = E'$row'";
        }
        if (!count($set)) {
            return FALSE;
        }
        $set = implode(", ", $set);
        return $this->_db->query("UPDATE search_kwords_settings SET $set");
    }
    
    public function getAsJS($type, $limit = 1000) {
        $mem = new memBuff();
        
        $cache_name = self::MCACHE_KEY_NAME;
        if (in_array($type, array('users', 'projects'))) {
            $cache_name .= $type;
        }
        //$mem->delete($cache_name);
        
        if (!($kdata = $mem->get($cache_name))) {
//            $js = "var search_kwords = [".(implode(",", $this->getTopQueries($type, $limit)))."];";
            $js = "var search_kwords = " . json_encode($this->getTopQueries($type, $limit)). ";";
            $etag = md5($js);
            $kdata = array('js' => $js, 'etag' => $etag);
            $mem->set($cache_name, $kdata, self::MCACHE_TIME);
        }
        
        return $kdata;
    }
    
    public function getTopQueiesAdmin($type, $limit=40, $offset, &$count) {
        $where = "";
        $limit = " LIMIT $limit OFFSET $offset ";
        
        $types = array('users', 'projects');
        
        $index_type = 2;
        if (in_array($type, array('users', 'projects'))) {
            $index_type = intval(array_search($type, $types));
            $where = "WHERE index_type = {$index_type}";
        } else if($type == 'more') {
            $where = "WHERE index_type = 2";
        } 
        
        $sql = "SELECT s.query, rate as weight, match_cnt as match_cnt, cnt as cnt 
                FROM search_kwords_top s
                {$where}
                ORDER BY weight DESC {$limit}";
        $res = $this->_db->rows($sql);
        if (!$res) {
            $res = array();
        }
        
        $sql = "SELECT COUNT(*) FROM search_kwords_top {$where}";
        $count = $this->_db->val($sql);
        
        return $res;    
    }
    
    public function getTopQueries($type, $limit = 1000, &$se_info = array()) {
        
        $limit = " LIMIT $limit";
        
        $types = array('users', 'projects');
        
        $index_type = 2;
        if (in_array($type, array('users', 'projects'))) {
            $index_type = intval(array_search($type, $types));
        }
        
        $sql = "SELECT s.query, rate as weight 
                FROM search_kwords_top s
                WHERE index_type = {$index_type}
                ORDER BY weight DESC $limit";
        $res = $this->_db->rows($sql);
        
        if (!$res) {
            $res = array();
        }
        $se_info = $res;
        $out = array();
        foreach ($res as $row) {
            $out[] = $row['query'];
        }
        
        $time = time().time();
        $out = implode($time, $out);
        $out = iconv('CP1251', 'UTF8', $out);
        $out = explode($time, $out);
        
        return $out;
    }

    /**
     * ������������� ���� ������
     * 
     * @return resource  ���������� ��������� �� ����.
     */
    private function _getFile() {
        if (is_file($this->sphinxLog)) {
            $file = "compress.zlib://$this->sphinxLog";
            $this->_resource = fopen($file, 'r');
            
            if (!$this->_resource) {
                $this->_log("������ ��� �������� ����� '$file'.", 'error');
            }
            
            return $this->_resource;
        } else {
            $this->_log("���� '$this->sphinxLog' �� ������.", 'error');
        }
    }
    
    
    private function _log($message, $type = 'info') {
        if (!$this->_log) {
            return;
        }
        $msg = date("Y-m-d H:i:s") . " [$type] " . $message;
        $this->_log->writeln($msg);
    }
    
    

}

