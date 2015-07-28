<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/globals.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");

/**
 * ����������� ������������ ���������� ����� MemCache
 *
 */
class memBuff extends Memcached
{
    const SERVERS_VARKEY = 'MEMCACHED_SERVERS_VARKEY';
	
	/**
	 * ���� �� ���������� � �������� ���-����
	 *
	 * @var boolean
	 */
	private $bIsConnected = false;
	
	/**
	 * ��� �������. �������� � config.php ��� SERVER
	 * 
	 * @var string
	 */
	private $server = '';
	
	/**
	 * ������ ������ �� ���� ��� �� ����. ������ ��� �-��� getSql()
	 * true - �� ����
	 *
	 * @var boolean
	 */
	private $bWasMqUsed = true;
	
    /**
     * ���������� �������� (� ����������� � ���� �������) ��������� ������������� ������ � �������
     * � ������� ���� �� ��� ���, �� �� ����������� ����� ��������� ��������. ��� ��������� dogpile ������
     * ������ ���� ��� ���� ���������� ����� �������
     * 
     * @var integer
     */
    private static $__edwCycles = 2;
    
    /**
     * �� ������������ ���������� ��� ������� ������
     * 
     * @var boolean 
     */
    public $noLock = FALSE;
    
	private $_log;
	
	/**
	 * �����������. ������������ � ������� �������
	 */
	public function __construct($noLock=FALSE) {
   		parent::__construct();
		$this->_log = new log('memcached/error-%d%m%Y.log', 'a');
        $this->setOption(Memcached::OPT_NO_BLOCK, true);
        $this->setOption(Memcached::OPT_COMPRESSION, false);
        
        $svk = $GLOBALS[memBuff::SERVERS_VARKEY];
        if(!$svk || !$GLOBALS[$svk]) {
            $svk = 'memcachedServers';
        }
        $servers = $GLOBALS[$svk] OR die('Server error: 121');
        foreach ($servers as $s){
            $this->bIsConnected = $this->addServer($s, 11211);
        }
	    
	    if (!$this->bIsConnected) {
	        $this->_error('connect');
	    }
	    $this->server = (defined('SERVER')?SERVER:'');
        $this->noLock = $noLock || (SERVER==='local' || defined('IS_LOCAL') && IS_LOCAL);
	}
	
	/**
	 * ����������. ����������� �� ������� �������
	 */
	public function __destruct() {
		//if ($this->bIsConnected) $this->close();
	}
	
	/**
	 * ���������� false ���� ������ ������ �� ����
	 * 
	 * @return boolean		false - �� ����, true - �� ����
	 */
	public function getBWasMqUsed() {
		return $this->bWasMqUsed;
	}
	
    /**
     * ����������� ������ �� �������.
     *
     * @param string $key		���� ��� ������
     * @return array			���������. false, ���� �� �������
     */
    public function get($key) {
        $output = FALSE;
        $chLock = FALSE;
        
        if ($this->bIsConnected) {
            $fKey = $key . $this->server;            
            $output = parent::get($fKey);
            
            if ( is_array($output) && isset($output['__expire']) ) {
                if ( $output['__expire'] < time() && ($this->noLock || (parent::get($fKey . '_lock') === FALSE)) ) {
                    $chLock = TRUE;
                    $output = FALSE;
                }
            }
                
            if ( is_array($output) && array_key_exists('__data', $output) && array_key_exists('__tags', $output) ) {
                if ( count($output['__tags']) ) {
                    $tags = parent::getMulti(array_keys($output['__tags']));
                    if ( !$tags ) {
                        $tags = array();
                    }
                    if ( array_sum(array_values($output['__tags'])) != array_sum(array_values($tags)) ) {
                        if ( $this->noLock || $chLock || (parent::get($fKey . '_lock') === FALSE) ) {
                            $chLock = TRUE;
                            $output = FALSE;
                        }
                    }
                }
            }
                
            if ( isset($output['__data']) ) {
                $output = $output['__data'];
            }
                
            if ( !$this->noLock && $output === FALSE ) {
                if ( $chLock || (parent::get($fKey . '_lock') === FALSE) ) {
                    parent::set($fKey . '_lock', 1, 30);
                } else {
                    if ( self::$__edwCycles > 0 ) {
                        self::$__edwCycles--;
                        sleep(1);
                        $output = $this->get($key);
                    }
                }
            }
                
        }
        
        if ($output === FALSE) {
            $this->_error('get', $key);
        }
        
        return $output;
    }

    
    /**
     * ����������� ����� ������ �� �������
     *
     * @param  array  $keys  ������ �� ������� ������ ��� ���������
     * @return array         ������ � �����������
     */
    public function gets($keys) {
        if ( $this->bIsConnected ) {
            $this->_error('get', implode(',', $keys));
        }
            
        $output   = array();
        $fullKeys = array();
        $locks    = array();
        $keyLocks = array();
        $setLocks = array();
        $waiting  = FALSE;
            
        foreach ( $keys as $k => $v ) {
            $fullKeys[$k] = $v . $this->server;
        }
            
        $res = parent::getMulti($fullKeys);
        if ( !is_array($res) ) {
            $this->_error('get', implode(',', $keys));
        }
        
        foreach ( $keys as $k ) {
            if ( isset($res[$k . $this->server]) ) {
                $output[$k] = &$res[$k . $this->server];
                if ( is_array($output[$k]) && isset($output[$k]['__expire']) && ($output[$k]['__expire'] < time()) ) {
                    $keyLocks[] = $k . $this->server . '_lock';
                }
            } else {
                $keyLocks[] = $k . $this->server . '_lock';
            }
        }
                
        if ( !$this->noLock && $keyLocks ) {
            $locks = parent::getMulti($keyLocks);
        }
                
        foreach ( $keys as $key ) {
                    
            $out = isset($output[$key])? $output[$key]: FALSE;
                    
            if ( is_array($out) && isset($out['__expire']) ) {
                if ( ($out['__expire'] < time()) && !isset($locks[$key . $this->server . '_lock']) ) {
                    $out = FALSE;
                }
            }
                    
            if ( is_array($out) && isset($out['__data']) && isset($out['_tags']) ) {
                if ( count($out['__tags']) ) {
                    $tags = parent::getMulti(array_keys($out['__tags']));
                    if ( !$tags ) {
                        $tags = array();
                    }
                    if ( array_sum(array_values($out['__tags'])) != array_sum(array_values($tags)) ) {
                        if ( !isset($locks[$key . $this->server . '_lock']) ) {
                            $out = FALSE;
                        }
                    }
                }
            }
                    
            if ( !$this->noLock && $out === FALSE ) {
                if ( !isset($locks[$key . $this->server . '_lock']) ) {
                    $setLocks[$key . $this->server . '_lock'] = 1;
                } else {
                    $waiting = TRUE;
                }
            }
                    
            if ( isset($out['__data']) ) {
                $output[$key] = $out['__data'];
            } else {
                $output[$key] = $out;
            }
                    
        }
        
        if ( $waiting && self::$__edwCycles > 0 ) {
            self::$__edwCycles--;
            if(SERVER!=='local') sleep(1);
            $output = $this->gets($keys);
        }
                
        if ( $setLocks ) {
            parent::setMulti($setLocks, 30);
        }
                
        return $output;
    }
        
	
	/**
	 * ����������� ������ � ������.
	 *
	 * @param string $key			���� ��� �����������
	 * @param string $data			������
	 * @param integer $data			����� ����� ������
	 * @param string|array $tags			������ � ������ ���� ��� ������, ���� ����� �������� ��������� �����
	 * @return boolean				true - ���� ��� ��			
	 */
	public function set($key, $data, $expire = 600, $tags = '') {
        $output = FALSE;
        
        if ( $this->bIsConnected ) {
            
            $key = $key . $this->server;
            $this->_initSetData($data, $expire, $tags);
            $output = parent::set($key, $data, $expire > 0 ? $expire + 900 : 0);
            
        }
            
        if ( $output === FALSE ) {
            $this->_error('set', $key);
        }
            
        return $output;

	}
    
	
    /**
	 * ����������� ������ ������ � ������.
	 *
	 * @param string $datas	        ������ � ������
	 * @param integer $data	        ����� ����� ������
	 * @param string|array $tags    ������ � ������ ���� ��� ������, ���� ����� �������� ��������� �����
	 * @return boolean              true - ���� ��� ��			
	 */
    public function sets($datas, $expire = 600, $tags = '') {
        $output = FALSE;
        
        if ( $this->bIsConnected ) {
            
            foreach ( $datas as $key => $data ) {

                $key = $key . $this->server;
                $this->_initSetData($data, $expire, $tags);
                $datas[$key] = $data;
            }
            
            $output = parent::setMulti($datas, $expire > 0 ? $expire + 900 : 0);
            
        }
        
        if ( $output === FALSE ) {
            $this->_error('set', $key);
        }
            
        return $output;
        
    }
    
    
	/**
	 * �������������� ������ ��� set-���������.
	 *
	 * @param array $data			������ � �������
	 * @param integer $expire		����� ����� ������
	 * @param string|array $tags			������ � ������ ���� ��� ������, ���� ����� �������� ��������� �����
	 */
    private function _initSetData(&$data, $expire, $tags) {
        $data = array(
            '__data'   => $data
        );
        
        if($expire) {
            $data['__expire'] = time() + $expire;
        }
    
        if ( $tags ) {
        
            $data['__tags'] = array();
            if ( is_array($tags) ) {
                $_tags = array();
                foreach ( $tags as $tag ) {
                    $_tags[] = '__tag_version_' . $tag . $this->server;
                }
            } else {
                $_tags[] = '__tag_version_' . $tags . $this->server;
            }
        
            $_versions = parent::getMulti($_tags);
        
            foreach ( $_tags as $tag_name ) {
                $tag_version = isset($_versions[$tag_name])? floatval($_versions[$tag_name]): 0;
                $data['__tags'][$tag_name] = $tag_version;
            }
        
        }
    }
	
	/**
	 * ������ ������ � ������. �� ������, ���� ���� �����.
	 *
	 * @param string $key			���� ��� �����������
	 * @param array $data			������
	 * @param integer $expire		����� ����� ������
	 * @param string|array $tags	������ � ������ ���� ��� ������, ���� ����� �������� ��������� �����
	 * @return boolean				true - ���� ��� ��			
	 */
	public function add($key, $data, $expire = 600, $tags = '') {
        $output = FALSE;
        if ( $this->bIsConnected ) {
            $key = $key . $this->server;
            $this->_initSetData($data, $expire, $tags);
            $output = parent::add($key, $data, $expire); // + 900);
        }
            
        if ( $output === FALSE ) {
            $this->_error('add', $key);
        }
            
        return $output;

	}
	
	/**
	 * ������� ������ �� ���� �� �� ����
	 *
	 * @param string $key	���� ������
	 * @return boolean				true - ���� ��� ��	
	 */
	function delete($key, $time = 0) {
		if ($this->bIsConnected) {
			$output = parent::delete($key.$this->server);
			if(!$output) {
			    $this->_error('delete', $key);
			}
	    }
		return $output;
	}
	
    /**
     * ������� ������ ������� �� �������
     * 
     * @deprecated
     * @param string $group		������ �������
     * @return boolean			true - ���� ��� ��	
     */
	function _flushGroup($group){
		$items = parent::get($group.$this->server);
		if ($items)
			foreach ($items as $item){
				if (parent::get($item.$this->server)!==false){ $this->delete($item); }
			}
		 if(parent::set($group.$this->server, false, 0) === false) {
		     $this->_error('flushGroup', $group);
		 }
	}
	
	/**
	 * �������� ���� ���
	 *
	 */
	function flush($delay = 0){
		parent::flush();
	}

    
	/**
	 * ����������� ������ �� �������. ���� �� ������� ����, �� �������� ��������� ������� �
	 * ������� pg_fetch_all()
	 *
	 * @param string $error			���������� ��������� �� ������ ��� ������� � ���������
	 * @param string $sql			������ � ���������
	 * @param integer $expire		����� ����� ���� (� ��������)
	 * @param boolean $read_only		������ ������ �� ������?
	 * @return array			��������� ������� �� ���� ��� ���� � ������� ������� pg_fetch_all()
	 */
	function getSql(&$error, $sql, $expire = 600, $read_only = false, $group = false){
	    $key = md5($sql);
		$output = $this->get($key);
		//print "Buffer";
		if (!$output){
			//print "NoBuffer!";
			$res = pg_query_Ex($sql, $read_only);
			$output = pg_fetch_all($res);
			$this->bWasMqUsed = false;
			$error = pg_errormessage();
			if (!$error){
				$this->set($key,$output, $expire, $group);
			}
		}
		return $output;
	}
    
    
    /**
     * ����� � Memcached::touchTag(), ��� �������������
     * 
     * @see Memcached::touchTag()
     * 
     * @param type $tag_name    ��� ����
     * @return type 
     */
    public function flushGroup($tag_name) {
        return $this->touchTag($tag_name);
    }
 
    /**
     * ��������� ������ ����
     * ��������� � ����� ������ ��������� ���� �����������.
     * 
     * @param string $tag_name  ��� ����
     * @return type 
     */
    public function touchTag ($tag_name) {
        $new_version = microtime(1);
        return parent::set('__tag_version_' . $tag_name . $this->server, $new_version, 0);
    }
	
    /**
     * ��������� ��� �� ����� � ��������� ��������� ���.
     * 
     * @param type $key
     * @param string $tag_name
     * @param type $expire
     * @return type 
     */
    public function addKeyTag ($key, $tag_name, $expire = 600) {
        $tag_name = '__tag_version_' . $tag_name . $this->server;
        
        $items = parent::getMulti(array($key, $tag_name));
        
        if (!$items) {
            $this->_error('addKeyTag');
            return false;
        }
        
        if (!isset($items[$key])) {
            $this->_error('addKeyTag');
            return false;
        }
        
        $item = $items[$key];
        
        if (!is_array($item) || !isset($item['__data']) || !isset($item['__tags'])) {
            $data = array(
                '__data' => $item,
                '__tags' => array(),
            );

            $tag_version = floatval(parent::get($tag_name));
            $data['__tags'][] = array($tag_name => $tag_version);
        } else {
            if (isset($item['__tags'][$tag_name])) {
                $this->_error('addKeyTag');
                return false;
            }
            
            $item['__tags'][] = $tag_name;
            $data = $item;
        }
        
        if (!$data) {
            $this->_error('addKeyTag');
            return false;
        }
        
        $output = parent::set($key, $data, $expire);
        
        if ($output === false) {
            $this->_error('set');
        }
        return $output;
    }
    
    /**
     * ��������� ��� �� ����� � ������� ��������� ���.
     * 
     * @param type $key
     * @param string $tag_name
     * @param type $expire
     * @return type 
     */
    public function dropKeyTag ($key, $tag_name, $expire = 600) {
        $tag_name = '__tag_version_' . $tag_name . $this->server;
        
        $item = parent::get($key);
        
        if (!$item) {
            $this->_error('dropKeyTag');
            return false;
        }
        
        if (!is_array($item) || !isset($item['__data']) || !isset($item['__tags'])) {
            $this->_error('dropKeyTag');
            return false;
        }
        
        if (isset($item['__tags'][$tag_name])) {
            unset($item['__tags'][$tag_name]);
        } else {
            $this->_error('dropKeyTag');
            return false;
        }
        
        $output = parent::set($key, $data, $expire);
        
        if ($output === false) {
            $this->_error('set');
        }
        return $output;
    }


    private function _error($optype = NULL, $key = NULL) {
	    if(!$this->_log->linePrefix) {
    		$this->_log->linePrefix = '%d.%m.%Y %H:%M:%S - ' . getRemoteIP()
    		                        . ' - "'
    		                        . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI']
    		                        . '" : ';
		}
	    $rcode = $this->getResultCode();
	    $rmsg = $this->getResultMessage();
	    $ttime = $this->_log->getTotalTime('%H:%M:%S', 3);
	    if($rcode == Memcached::RES_NOTFOUND
	       || $rcode == Memcached::RES_SUCCESS
	       || ($optype == 'add' && $rcode == Memcached::RES_NOTSTORED)
	      )
	    {
	        return;
	    }
	    $this->_log->writeln("[error: {$rcode}, method: {$optype}, key: {$key}, time: {$ttime}] {$rmsg}");
	}

}
?>