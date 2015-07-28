<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php";

/**
 * ����������� ����� �������� ������.
 * ����� �������� ����� �������������� ����� ���������� � ������, ����� ��� ������������:
 * ����������:
 * - $name
 * ������:
 * - setHtml()
 */
abstract class searchElement
{
    /**
     * ����� ������ �����������.
     * ���������� ����� - �� ������ �������� � ������ � ���������� �����.
     */
    const LAYOUT_LINE = 1;

    /**
     * ����� ������ �����������.
     * ������� ����� - �� ��� �������� � ������.
     */
    const LAYOUT_BLOCK = 2;

    /**
     * ����� ������ �����������.
     * ���������� ����� - �� ������ �������� � ������ ��� ��������� �����.
     */
    const LAYOUT_ROW = 3;


    /**
     * ������ �������� ��������. ������ ���� ������� ������ ���� ������� ��������!
     * @var array
     */
    protected $_indexes = array();

    /**
     * ������� ������� (������������ ��������)
     * @see searchElement::setIndexes()
     * @var string
     */
    protected $_indexPfx = '';

    /**
     * ������� ������� (������������ ��������)
     * @see searchElement::setIndexes()
     * @var string
     */
    protected $_indexSfx = '';

    /**
     * ������ ������ ��� ��������� �� ����.
     * @see search
     * @var object
     */
    protected $_engine;

    /**
     * ����� �� ����������� ����� �� ������� �������� ������ (��������, ������� �� ��� ���� � ����������).
     * @var boolean
     */
    protected $_active = true;

    /**
     * ����� ������ �����������.
     * @var int
     */
    public $layout = self::LAYOUT_LINE;

    /**
     * �������������� ��������� ��������� ��������� ����.
     * @var array
     */
    protected $_opts = array (
        "before_match"      => "<em>",
        "after_match"       => "</em>",
        "limit"             => 250,
        "exact_phrase"      => false
    );

    /**
     * ������������ ��� �������� ������, �������� "������ �������".
     * @var string
     */
    public $name = '';

    /**
     * ����� ���� ��� ������ ����� ��������� ����������.
     * @var array
     */
    public $totalwords = array('����������', '����������', '����������');

    /**
     * ����� ��������� �����, ����������� ���������� ������ ��� ��������� ��������� ����������.
     * @var string
     */
    public $words = '';

    /**
     * ����� ���������� ��������� ����������.
     * @var integer
     */
    public $total = 0;

    /**
     * ���������������� ������ ��� ������ ����� ��������� ����������.
     * @var string
     */
    public $totalStr = '';

    /**
     * ������ ��������������� ��������� ����������.
     * @var array
     */
    public $matches = array();

    /**
     * ������ ��������� ���������� � ������� HTML-������ ��� ������ � �������.
     * @var array
     */
    public $html = array();



    /**
     * ��� ��������� ���������� ��������� ������������ ����������� ���������� ������ {@link SphinxClient}
     * ������� ������ ����� ������ �������������� ������ ����������:
     * �) ����� � ����������;
     * �) �����������, � ������������� ������ searchElement::setEngine() � ����� ������.
     */
    protected $_host = SEARCHHOST;
    protected $_port = SEARCHPORT;
    protected $_offset = 0;
    protected $_limit = 5;
    protected $_mode = SPH_MATCH_ALL;
    protected $_weights = array();
    protected $_sort = SPH_SORT_ATTR_DESC;
    protected $_sortby = 'post_time';
    protected $_min_id = 0;
    protected $_max_id = 0;
    protected $_filtersV = array(); // array( array( "attr"=> $attribute, "values"=>$values) )
    protected $_filtersR = array(); // array( array( "attr"=> $attribute, "min"=>$min, "max"=>$max) )
    protected $_filtersRF = array(); // array( array( "attr"=> $attribute, "min"=>$min, "max"=>$max) )
    protected $_groupby = '';
    protected $_groupfunc = SPH_GROUPBY_DAY;
    protected $_groupsort   = '@group desc';
    protected $_groupdistinct = '';
    protected $_maxmatches  = 1000;
    protected $_cutoff      = 0;
    protected $_retrycount  = 0;
    protected $_retrydelay  = 0;
    protected $_anchor      = array(); // ( "attrlat"=>$attrlat, "attrlong"=>$attrlong, "lat"=>$lat, "long"=>$long )
    protected $_indexweights = array();
    protected $_ranker       = SPH_RANK_PROXIMITY_BM25;
    protected $_maxquerytime = 0;
    protected $_fieldweights = array();
    protected $_overrides   = array(); // ( "attr"=>$attrname, "type"=>$attrtype, "values"=>$values )
    protected $_select      = '*';
    protected $_arrayresult = false;
    protected $_advanced    = false; // ��� ���������� ������ ��� ���������� ���������
    protected $_advanced_page = 0;
    protected $_advanced_limit = 5;


    /**
     * �����������.
     * @param object $engine   ������ ������ search, ���������� SphinxClient.
     * @param boolean $active   ����� �� ����������� ����� �� ������� �������� ������ (��������, ������� �� ��� ���� � ����������).
     */
    function __construct($engine, $active = true) {
        $this->_active = $active;
        $this->_engine = $engine;
    }
    
    function setUserLimit($limit) {
        $this->_limit = (int)$limit;
    }

    
    public function setServer($host, $port = 0)
	{
        $this->_host = $host;
        $this->_port = $port;
    }
    
    /**
     * �������������� ��������� ������ ������� �������� � ���������� ����� �� �������� �����.
     *
     * @param string $string   ��������� �����.
     * @param integer $page   ����� ������� �������� (������������ ��� ������ �� ����������� ��������).
     */
    function search($string, $page = 0) {
        if(!$this->isActive() || !$this->isAllowed()) return;
        // ������������ ����� ��� ������� ���� ����������� ������, ����������� ��� ������������ ������
        if($this->isAdvanced() !== false) {
            $this->_advanced_limit = $this->_limit;
            $this->_limit = $this->_maxmatches;
        }
        $this->setPage($page);
        $this->setEngine();
        $this->setIndexes();
        $this->resetResult();
        
        //print_r($this->_mode);exit;
        
        $this->setResult($this->_engine->Query($string, implode(';',$this->_indexes)));
        $this->setWords($string);
        // ���������� ��� �� �����
        if($this->isAdvanced() !== false) {
            $this->_limit = $this->_advanced_limit;
        }
    }

    /**
     * ������ ��������� limit � offset � ����������� � ������� ������� ��������.
     * $this->isAdvanced() - ���� ���� ����������� ����� �� � setPage ���� ������, ��� ��� ����� �������� �������� 
     * ��������� �����������, ��� ����������� ������ ���� ������ @see $this->_advanced_page, $this->_advanced_limit
     * @param integer $page   ����� ������� �������� (������������ ��� ������ �� ����������� ��������).
     */
    function setPage($page) {
        if($page > 0 && !$this->isAdvanced()) {
            $this->_limit *= $this->_layout==self::LAYOUT_BLOCK ? 3 : 1;
            $this->_offset = ($page - 1) * $this->_limit;
        }
    }

    /**
     * ������������� �������� ��������, ���� ��� �� ������ ����.
     *
     * @param integer $page   ����� ������� �������� (������������ ��� ������ �� ����������� ��������).
     */
    function setIndexes() {
        if(!$this->_indexes) {
            $this->_indexes[0] = $this->_indexPfx.$this->_engine->getElementKey($this).$this->_indexSfx;
            $this->_indexes[1] = 'delta_'.$this->_indexes[0];
        }
    }

    /**
     * �������� ������� ��������� �������� ������ API SphinxClient.
     * ���������� ���������� ��� ���������� �� ������ ���� ������� (��������, � ������������� ������).
     */
    function setEngine() {
        $this->_engine->SetServer($this->_host, $this->_port);
        $this->_engine->SetLimits($this->_offset, $this->_limit, $this->_maxmatches, $this->_cutoff);
        $this->_engine->SetMaxQueryTime($this->_maxquerytime);
        $this->_engine->SetRankingMode($this->_ranker);
        $this->_engine->SetMatchMode($this->_mode);
        $this->_engine->SetFieldWeights($this->_fieldweights);
        $this->_engine->SetIndexWeights($this->_indexweights);
        $this->_engine->SetRetries($this->_retrycount, $this->_retrydelay);
        $this->_engine->SetArrayResult($this->_arrayresult);
        $this->_engine->ResetFilters();
        $this->_engine->ResetGroupBy();
        $this->_engine->ResetOverrides();
        $this->_engine->SetIDRange($this->_min_id, $this->_max_id);
        $this->_engine->SetSelect($this->_select);
        if($this->_overrides)
            call_user_func_array(array($this->_engine, 'SetOverride'), $this->_overrides);
        if($this->_anchor)
            call_user_func_array(array($this->_engine, 'SetGeoAnchor'), $this->_anchor);
        foreach($this->_filtersV  as $f) call_user_func_array(array($this->_engine, 'SetFilter'), $f);
        foreach($this->_filtersR  as $f) call_user_func_array(array($this->_engine, 'SetFilterRange'), $f);
        foreach($this->_filtersRF as $f) call_user_func_array(array($this->_engine, 'SetFilterFloatRange'), $f);
        $this->_engine->SetGroupBy($this->_groupby, $this->_groupfunc, $this->_groupsort);
        $this->_engine->SetGroupDistinct($this->_groupdistinct);
        $this->_engine->SetSortMode($this->_sort, $this->_sortby);
    }

    
   /**
    * ������� ������ API Sphinx
    * 
    * @return type
    */
    public function getEngine()
    {
        return $this->_engine;
    }
    
    
    /**
     * ���������� ���������� ������.
     */
    function resetResult() {
        $this->words = '';
        $this->total = 0;
        $this->totalStr = '';
        $this->matches = array();
        $this->html = array();
    }

    /**
     * ��������� ���������� ������.
     *
     * @param array $result  ������ �����������, ���������� ������� SphinxClient::Query().
     */
    function setResult($result) {
        if($result && $result['total']) {
            //$this->words = str_replace('*', '', @implode(' ', @array_keys($result['words'])));
            $this->words = @implode(' ', @array_keys($result['words']));
            if($result['matches']) $this->matches = array_keys($result['matches']);
            $this->total = $result['total'];
            $this->totalStr = ending((int)$result['total'], $this->totalwords[0], $this->totalwords[1], $this->totalwords[2]);
            // $this->setHtml(); // @todo ���� ����� ������ �������, ��� ���� ������ ����� self::getRecords();
            $this->setResults();
        }
    }

    /**
     * ����� �� ����������� ����� �� ������� �������� ������ (��������, ������� �� ��� ���� � ����������).
     *
     * @return boolean
     */
    function isActive() {
        return $this->_active;
    }

    /**
     * ���������, �������� �� ����� �� ������� �������� ��� ������� ���������� ��������� (��������, �������� �� ����������������).
     *
     * @return boolean
     */
    function isAllowed() {
        return true;
    }


    /**
     * ���������� �������� ���������� ������, ������������ � ��������� '_'.
     * ����� ������������ ��� ��������� ��������� �������� �������.
     *
     * @return mixed
     */
    function getProperty($name) {
        $pname = '_'.$name;
        if(isset($this->$pname))
            return $this->$pname;
        return NULL;
    }

    /**
     * ����� ���������� �� ��������� �����������
     *
     * @return array
     */
    function getRecords($order_by = NULL) {
        if ($this->matches && $this->active_search) {
            if($this->_indexes[0]=='blogs') { // 0014900. ����� ����� ����� �� ���� VIEW �����������.
                $set_sql = 'SET join_collapse_limit = 1;';
            }
            $sql = "{$set_sql}SELECT * FROM search_{$this->_indexes[0]} WHERE id IN (" . implode(', ', $this->matches) . ')';
            if($order_by)
                $sql .= " ORDER BY {$order_by}";
            else if($this->_sortby && (($desc=$this->_sort==SPH_SORT_ATTR_DESC) || $this->_sort==SPH_SORT_ATTR_ASC))
                $sql .= " ORDER BY {$this->_sortby}".($desc ? ' DESC' : '');
            if($res = pg_query(DBConnect(), $sql))
                return pg_fetch_all($res);
        }
        return NULL;
    }

    /**
     * ��������� ��������� � HTML-����� � ���������� �� � $this->html.
     * ���������� ����������� � ������ ������� ��������.
     */
    function setHtml() {
    }
    
    function setResults() {
        return true;
    }
    
    public function setAdvancedSearch($page=0, $filter) {
        $this->_advanced       = $filter; 
        $this->_advanced_page  = $page;
        $this->_advanced_limit = $this->_limit;  
    }
    
    public function isAdvanced() {
        return $this->_advanced;
    }

    /**
     * ��������� ��������� ���������� � ���������.
     *
     * @param array $data   ������ �������� ������ (��������, ��������� �������).
     * @return array
     */
    function mark($data) {
        return $this->_engine->BuildExcerpts($data, $this->_indexes[0], $this->words, $this->_opts);
    }
    function setWords($words) {
        $this->words = $words;//str_replace('*', '', $words);
    }
    
    /**
     * �������� �������� �������� �� ������� _opts �� ��� �����
     * 
     * @param string $key
     * @return string 
     */
    function getOpts($key) {
        if (!isset($this->_opts[$key])) {
            return null;
        }
        
        return $this->_opts[$key];
    }
    
    /**
     * ������������� �������� �������� � _opts
     * 
     * @param string $key       ����
     * @param string $value     ��������
     */
    function setOpts($key, $value) {
        $this->_opts[$key] = $value;
    }
    
    
    /**
     * ���������� ����� ������
     * 
     * @param type $mode
     */
    function setMode($mode)
    {
        $this->_mode = $mode;
    }
    
}
