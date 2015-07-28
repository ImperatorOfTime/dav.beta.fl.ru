<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/pgq/api/PGQConsumer.php');

if(!defined('COMPRESS_STATIC')) {
    define('COMPRESS_STATIC', false);
}

define('JAVA_PATH', $_SERVER['DOCUMENT_ROOT'] . '/classes/java');

/**
 * ����� ��� ������ � ����������� .css � .js ������
 * 		  
 */
class static_compress {
	
	/**
	 * ������������ ����� ����� ������ � �������.
	 */
    const GC_LIFE = 14400;
    
    const MEM_LOCK_LIFE = 25; // ����� ���������� �������� ������������ ������.
    const MEM_BATCHES_VERSION_KEY = 'static_batch_version';
    const STATIC_PATH = '/static'; // (deprecated) ����� ��� �������� ������ .js �.css �� ��������� ������� 
    const STATIC_WDPATH = 'wdstatic'; // ����� ��� �������� ������ .js �.css �� WebDAV
    const BEM_SRC_PATH = '/css/block/style.css'; // ����� ����� � ��������� ���.
    const BEM_SRC_PATH_MAIN = '/css/block/style-main.css';
    const BEM_DEST_PATH = '/css/block/_compressed.css'; // �������� (��������� � ����) ���� ���-������.
    const MAX_CSSSIZE_IE = 240; // ������������ ������ .css ����� ��� IE � ����������.

    /**
     * ����������� ���� ������ � $seed (��������������� base64 ���������� ��������� ?t).
     */
    const SEED_SEP = ':';

    /**
     * ������ ���� ����� CSS.
     */
    const TYPE_CSS = 0;

    /**
     * ������ ���� ����� JS.
     */
    const TYPE_JS  = 1;
    
    
    /**
     * ������ ����� JS ���������� � php
     */
    const TYPE_PHP_JS = 2;
    
    /**
     * ������ ���� ����� JS ������� ���������� ������ � ��������� UTF-8.
     */
    const TYPE_JS_UTF8  = 3;
    
    /**
     * ������ ��� ��������, ��� �� �������� ���� � ����� �� ����������� ������, ����� ��������� �����.
     * @var array
     */
    private static $_allAddedFiles = array();
    
    /**
     * ���� ������.
     * 
     * @var array
     */
    public $types = array( self::TYPE_CSS => "css",
                           self::TYPE_JS => "js",
                           self::TYPE_PHP_JS => "js",
                           self::TYPE_JS_UTF8 => "js"
                          );

    public $isMSIE = false;


    /**
     *  ������� ���� ������ (�� �����), ��������������� ����� ������.
     * 
     * @var array
     */
    private $files;
	
	/**
	 * ���������� ��� ����������� �������
	 * 
	 * @var memBuff
	 */
	private $memBuff;
	
	/**
	 * ���� �� ���� �����
	 * 
	 * @var memBuff
	 */
	private $enabled;

    /**
     *
     * @var log 
     */
    private $_log;

    private $_cssSize = 0;
    private $_addWorker;

    private $_batches;
 
 /**
  * ������� � �������� ��������� ������, ��������������� �����.
  * 
  * @var array 
  */
 private $mtime;

 private $_root;
	
	/**
	 * �����������. ������������� ����������
	 */
	function static_compress($enabled = COMPRESS_STATIC, $options = array()){
        if (isset($options['bem']) && $options['bem']) {
            $this->bem_src_path = $options['bem'];
        } else {
            $this->bem_src_path = self::BEM_SRC_PATH;
        }
        $this->_root = $options['root'];
		$this->memBuff = new memBuff();
		$this->enabled = $enabled;
        $this->_log = new log('static/'.SERVER.'-%d.log');
        $this->_log->linePrefix = '%d.%m.%Y %H:%M:%S : ' . str_pad(getRemoteIP(),15) . ' ';
        $this->isMSIE = stripos($_SERVER['HTTP_USER_AGENT'], 'msie ') !== false;
	}

	function root($file) {
	    if($file=trim($file)) {
  	        if(strpos($file, '/static') === 0) {
  	            $root = $this->_root;
            } else {
      	        $root = ABS_PATH;
      	    }
  	    }
  	    return $root.$file;
	}
	
	/**
     * ���������� ������ ����� (������, ���-�������, ��) ����� ������� static_compress::send().
     * @see static_compress::send()
	 * 
     * @param string $fname   ���� � �����.
	 */
    function add($fname, $utf8 = false) {
        if($this->isAdded($fname)) {
            return;
        }

        if (strstr($fname, '.php') !== false && preg_match('/(kword_js|kword_search_js|professions_js|cities_js|tservices_categories_js)\.php/', $fname)) {
            $this->_add(self::TYPE_PHP_JS, $fname);
        }
        
        else if (strstr($fname, '.js') !== false) {
            $this->_add($utf8 ? self::TYPE_JS_UTF8 : self::TYPE_JS, $fname);
        }

        else if (strstr($fname, '.css') !== false) {
            if($this->isMSIE && COMPRESS_STATIC) {
                if(!$this->_addWorker) {
                    $fsize = @filesize($this->root($fname));
                    $maxsize = self::MAX_CSSSIZE_IE * 1024;
                    if($fsize > $maxsize) {
                        $this->_log->writeln("ERROR! {$fname} size is {$fsize} bytes (limit is $maxsize)");
                        if(!is_release()) {
                            die("static_compress: ERROR! {$fname} size is {$fsize} bytes (limit is $maxsize). ���������� ������� ���� �� ����� ������.");
                        }
                    }
                    if($this->_cssSize && $this->_cssSize + $fsize > $maxsize) {
                        $this->_addWorker = new static_compress($this->enabled);
                    }
                    $this->_cssSize += $fsize;
                }
                if($this->_addWorker) {
                    return $this->_addWorker->add($fname);
                }
            }
            $this->_add(self::TYPE_CSS, $fname);
        }
    }

    /**
     * ������������ ���� � �������� ������.
     *
     * @param int $type   ��� �����.
     * @param string $fname   ���� � �����.
     */
    private function _add($type, $fname) {
        $this->files[$type][] = $fname;
        static_compress::$_allAddedFiles[$fname] = 1;
    }

    /**
     * ���������, ��� �� �������� ���� � ����� �� ����������� ������.
     * @param string $fname   ���� � �����.
     * @return boolean
     */
    function isAdded($fname) {
        return isset(static_compress::$_allAddedFiles[$fname]);
    }

    /**
     * ��������� �������� ?t �� ���� ������ � �� ������.
     * 
     * @param int $type   ��� �����.
     * @return string   �������������� ������.
     */
    private function _encodeSeed($type) {
        return base64_encode($type . self::SEED_SEP . $this->_root . self::SEED_SEP . implode(self::SEED_SEP, $this->files[$type]));
    }

    /**
     * ����������� $seed (�������� ?t). �������������� $this->files.
     * @see static_compress::_encodeSeed()
     * 
     * @param string $seed   ������������� ������.
     * @param boolean $seed_expired   ���� ����������� $seed (��������, ������������� ��� ��������� ����).
     * @return integer|boolean $type   ��� ����� ��� FALSE.
     */
    private function _decodeSeed($seed, &$seed_expired = false) {
        $seed_expired = false;
        $arr = explode(self::SEED_SEP, base64_decode($seed));
        $type = array_shift($arr);
        $this->_root = array_shift($arr);
        $parse = array();
        if($this->types[$type]) {
            $this->files[$type] = array();
            foreach($arr as $file) {
                if($type == self::TYPE_PHP_JS) {
                    $parse = parse_url($file);
                    $file = $parse['path'];
                }

                if (strpos($file, '../') !== false
                   || !(preg_match('/\.(css|js)$/', $file) || 
                        preg_match('/^\/(kword_js|professions_js|cities_js|kword_search_js|tservices_categories_js)\.php$/',$file))
                   || !file_exists($this->root($file))
                  )
                {
                	$this->_log->writeln("Error. File not exists or wrong path: " . $this->root($file));
                    $seed_expired = true;
                    continue;
                }
                $this->files[$type][] = $file . (isset($parse['query']) && $parse['query'] ? '?'.$parse['query'] : "");
            }
            if($this->files[$type])
                return $type;
        }
        return false;
    }
    
    /**
     * �������� ���� � ������� �� ����.
     * ��� ������ ���� ��� ���������? 
     * @todo ������� ������������ � ��.
     *
     * @return array
     */ 
    function getBatchesInfo() {
        if (!$this->_batches) {
            if (!$this->_batches = $this->memBuff->get(self::MEM_BATCHES_VERSION_KEY)) {
                $this->_batches = array(
                    'version'   => time(),                  // ������ ����
                    'batches'   => array(),                 // ������ � �������� ������ (id ������ => ������ ����)
                    'bem_count' => 0                        // ���-�� ������������ ���-������ (��� IE).
                );
            }
        }
        return $this->_batches;
    }

    /**
     * ��������� ���� � ������� � ������.
     * @param array $batches   ��. getBatchesInfo()
     */ 
    function setBatchesInfo($batches) {
        $this->_batches = NULL;
        if($this->memBuff->set(self::MEM_BATCHES_VERSION_KEY, $batches, 0)) {
            $this->_batches = $batches;
        }
        return !!$this->_batches;
    }
    
    
    /**
     * �������� ������ ����������� ������. ��� ������������� �������� � ����� ������, ����� �� ������ ������ ��������� � ������.
     *
     * @param integer $batch_id   ��. ������.
     * @return integer   ������.
     */ 
    function getBatchVersion($batch_id) {
        $batch_version = (int)$this->_batches[$batch_id];
        if($batch_version < $this->_batches['version']) {
            $batch_version = (int)$this->memBuff->get(self::MEM_BATCHES_VERSION_KEY.$batch_id);
            if($batch_version >= $this->_batches['version']) { // ����� �����������.
                $this->_batches[$batch_id] = $batch_version;
                $this->setBatchesInfo($this->_batches);
            }
        }
        return $batch_version;
    }

    /**
     * ��������� ������ ����������� ������. ������������� �������� � ����� ������,
     * ����� �� ������ ������ ��������� � ������.
     *
     * @param integer $batch_id   ��. ������.
     * @param integer $batch_version   ������.
     * @return boolean   �������?
     */ 
    function setBatchVersion($batch_id, $batch_version) {
        if($this->memBuff->set(self::MEM_BATCHES_VERSION_KEY.$batch_id, $batch_version, 0)) {
            $batches = $this->getBatchesInfo();
            $batches[$batch_id] = $batch_version;
            $this->setBatchesInfo($batches); // ��� ���� ����������� ����, ��� ������������ ������ ������ ������, ����� ������ ����� � getBatchVersion()
            return true;
        }
        return false;
    }

    
    /**
     * ��������� ������ ������� ���������.
     * ����� ���� ����� ����������� ������.
     * ����������� ��������� (��� ������� �� ����/�����, ��� ����� �� ������)
     * @todo ������� ������ �� ������ ������.
     *
     * @param integer $version   ������ (timestamp), ���� NULL, �� ������� �����.
     */
    function updateBatchesVersion($version = NULL) {
        $log = $this->_log;
        $ret = 0;
        $version = $version === NULL ? time() : $version;
        $log->writeln("update batches version to {$version}");
        $batches = $this->getBatchesInfo();
        $batches['version'] = $version;
        $bcnt = $this->createBemBatchFiles();
        $ret |= !$bcnt;
        $batches['bem_count'] = $bcnt;
        if(!$this->setBatchesInfo($batches)) {
            $log->writeln("ERROR: failed to save batch info!\n");
            $ret |= 2;
        } else {
            $log->writeln("ok\n");
        }
        return $ret;
    }

    /**
     * ��������� ��� ���-�����
     * @see static_compressor::_lock()
     * @deprecated
     *
     * @param string $batch_id    ��. ������ (md5 ���� ������, ��. send()) 
     * @param string $type        ��� ������ � ������
     * @return boolean   ��?
     */ 
    private function _lfname($batch_id, $type) {
        return "/{$batch_id}.{$this->types[$type]}.lock";
    }

    /**
     * ��������� ����� �� ������ ���������.
     * info: LOCK_EX �� ����� �� ������, �.�. ��� �������� ���� � ��� �� ��������� ����������, ������� ����� ������.
     *       (���� ��� ����� � ��������.)
     * @see static_compressor::send()
     * @deprecated
     *
     * @param string $batch_id    ��. ������ (md5 ���� ������, ��. send()) 
     * @param string $type        ��� ������ � ������
     * @return boolean   ��?
     */ 
    private function _lock($batch_id, $type) {
        // info: LOCK_EX �� ����� �� ������, �.�. ��� �������� ���� � ��� �� ��������� ����������.
        $this->_lock = NULL;
        $lfname = $this->_lfname($batch_id, $type);
        if($f = @fopen($lfname, 'x')) {
            $this->_lock = array($f, $lfname);
        }
        return !!$this->_lock;
    }

    /**
     * ���������, ������������ �� �����. ���� ������� ���������� �� ����, �� ������ ��������� ����� �������� sendUncompress().
     * @see static_compressor::send()
     * @deprecated
     *
     * @param string $batch_id    ��. ������ (md5 ���� ������, ��. send()) 
     * @param string $type        ��� ������ � ������
     * @return boolean   ��?
     */ 
    function _islock($batch_id, $type) {
        return file_exists($this->_lfname($batch_id, $type));
    }

    /**
     * ������� ���� � ���������� ���������������� ������.
     * @see static_compressor::_lock()
     * @deprecated
     * @return boolean   ��?
     */ 
    private function _unlock() {
        if($this->_lock) {
            fclose($this->_lock[0]);
            if($ok = unlink($this->_lock[1]))
                $this->_lock = NULL;
        }
        return !$this->_lock;
    }
    
    /**
     * ������ ��� ����� ��� ���������� �������� ��������� ������.
     * @param string $batch_id   �� (���) ������
     * @param string $batch_version   ������� ������ �������, ���� ��������� ������ � ���� ������.
     * @return string
     */ 
    private function _createBatchLockKey($batch_id, $batch_version) {
        return md5($batch_id.$batch_version.'.lock');
    }

    /**
     * �������� html-����� (<script ...>, <link ...>, ��.) ��� ��������� � ������������ ��������.
     * �������� ������� ������� ���� (js|css) � ���� ����, ����:
     * %batchid%_%version%.%type%
     * ������� ��������� �� �������. ��� ��������� ���������, ���� ������ �� ��������, �� ������ �������� ��� � ������� ����� �����.
     * ���� ������ ��������, �� ������� ���������� ���� ������. ��� ���� ��������� ������ ����� �� ������ ���������.
     * ���� ������� ���������� �� ����, �� ������ ��� ������� "online" -- ����� static_compressor::output().
     */
    function send() {
        global $DB;
        
        if (!$this->enabled)
            return $this->sendUncomressed();

        $log = $this->_log;
        $this->getBatchesInfo();

        foreach($this->types as $type=>$name) {
            if(!$this->files[$type]) continue;

            $batch_id = md5(implode(self::SEED_SEP, $this->files[$type]));
            $batch_version = $this->getBatchVersion($batch_id);
                
            $ext = $this->types[$type];
            $filename = self::STATIC_WDPATH . '/' . $this->createFileName($batch_id, $batch_version, $ext);
            $fileurl = WDCPREFIX . '/';
            $expired = false;
            $file_not_exists = false;
            $batch_locked = 0;
            
            if ( $expired = $batch_version < $this->_batches['version'] ) {
                if(isset($_SERVER['REQUEST_URI'])) {
                    // $log->writeln("ref: {$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']}");
                }
                
                $old_filename = $filename;
                $old_batch_version = $batch_version;
                
                // $log->writeln("batch file {$filename} expired");

                $lock_key = $this->_createBatchLockKey($batch_id, $this->_batches['version']);
                // $this->memBuff->delete($lock_key);
                if( !$batch_locked && !($batch_locked = $this->memBuff->get($lock_key)) ) {
                    $log->writeln('lock not exist, try set it...');
                    if($batch_locked = !$this->memBuff->add($lock_key, 1, self::MEM_LOCK_LIFE)) {
                        $log->writeln('lock already added');
                    } else if($batch_locked = !$this->memBuff->set($lock_key, 1, self::MEM_LOCK_LIFE)) {  // �����-�� ����� � add(), �� ���� ��������� ������ add().
                        $log->writeln('lock setting failed');
                    }
                }

                if( !$batch_locked ) { // �.�. ������ ���� ������� ����� ����������� �����.
                    $lock_cnt = (int)$this->memBuff->get($lock_key.'.counter');
                    if( $lock_cnt > 0
                        || !$DB->query("SELECT pgq.insert_event('share', 'static_compress.createBatchBySeed', ?)",
                                       'seed='.$this->_encodeSeed($type)) )
                    {
                        // ������� ������� (�� ����):
                        // �) ���� ������ ��� ���������� ������� ��� ������������ ������, �� ��� ��� � ��� -- �������, ��� ��������� pgq
                        //    (���� $lock_cnt ������, ��� ������ self::MEM_LOCK_LIFE ������);
                        // �) � ������, ���� pgq ��������, �� �������� ������ � createBatch() (��������, ��� ������);
                        // �) � ������ ������� ������� � �������.
                        $batch_locked = $this->_createBatch($type, $batch_id, $this->_batches['version'], $filename, true);
                    } else {
                        $batch_locked = 100; // ������ ������� ���� ������ ������.
                    }
                    
                    $this->memBuff->set($lock_key.'.counter', $lock_cnt + 1, self::MEM_LOCK_LIFE * 10);
                }
                
                if( $batch_locked ) {
                    if($old_batch_version) { // ������ ���� ����� ����.
                        // 1. ������ ������ ������.
                        // $log->writeln("sending old version: batch file {$filename} is locked/failed ($batch_locked)\n");
                        $filename = $old_filename;
                    } else {
                        // 2. ���� ������������ ������ ���� �������, �� �� ����� ������ ����� ��� �������������.
                        // ������� ������ � ������ ���������� ������� �����.
                        // $log->writeln("sending uncompressed: batch file {$filename} is locked/failed ($batch_locked)\n");
                        $filename = '/static.php?t='.$this->_encodeSeed($type);
                        $fileurl = '';
                    }
                    
                    // 3. ���� ����� �������. �� � ����� ������ ������� ������ ����� ��������� ���������� ���. 
                    // 08.2012: ��� �� ����� ������ ��-�� IE+���.
                    // $this->sendUncomressed($type, $this->_batches['version']);
                    // continue;
                }
                // $log->write("\n");
            }

            $this->printTags($fileurl.$filename, $type);
        }

        if($this->_addWorker) {
            $this->_addWorker->send();
        }
    }
    
    /**
     * ���������� ��������� �� �����
     * 
     * @param integer $type    ��� �����
     * @return string
     */
    public static function getCharsetType($type) {
        switch($type) {
            case self::TYPE_JS:
            case self::TYPE_CSS:
            case self::TYPE_PHP_JS:
                return 'windows-1251';
                break;
            case self::TYPE_JS_UTF8:
                return 'utf-8';
                break;
        }
    }

    /**
     * �������� html-����� ��� ������� ��������� ��������.
     */
    function sendUncomressed($onlytype = -1, $version = NULL) {
        if(!$version) {
            $version = $GLOBALS['RELEASE_VERSION'];
        }
        foreach($this->types as $type=>$name) {
            if($this->files[$type] && ($onlytype == -1 || $type == $onlytype)) {
                foreach ($this->files[$type] as $file)
                    $this->printTags($file, $type, $version);
            }
        }
    }

    /**
     * �������� ���������� ������, ��� ������� /static.php?t=$seed.
     * ������� ����������, ������������ ����������� �� ������� � �������.
     *
     * @param string $seed   ������������� ������ (�������� ?t).
     */
    function output($seed) {
        $log = $this->_log;
        if(($type = $this->_decodeSeed($seed, $seed_expired)) === false) {
            $log->writeln("\n\nstatic_compressor::output()\n");
            $log->writeln("Error _decodeSeed - seed:{$seed}\n\n");
            exit;
        }
        if($seed_expired)
            $seed = $this->_encodeSeed($type);
        $last_mod = $this->getLastModified($type);
        $mem_key = md5('static_compress.output'.$seed);
        $mem_data = $this->memBuff->get($mem_key);
        if(!$mem_data || $last_mod != $mem_data['last_mod']) {
            $mem_data['body'] = $this->_compress($type, true);
            $mem_data['etag'] = '"' . md5($mem_data['body']) . '"';
            $mem_data['last_mod'] = $last_mod;
            $mem_data['length'] = strlen($mem_data['body']);
            $this->memBuff->set($mem_key, $mem_data, self::GC_LIFE);
        }
        header('Content-Type: text/' . ($this->types[$type]=='js' ? 'javascript' : 'css') . '; charset=' . self::getCharsetType($type));
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('ETag: ' . $mem_data['etag']);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mem_data['last_mod']). ' GMT');
        if( isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $mem_data['etag']
            && (!isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $mem_data['last_mod']) )
        {
            header('HTTP/1.1 304 Not Modified');
            $mem_data['length'] = 0;
            $mem_data['body'] = NULL;
        }
        header('Content-Length: ' . $mem_data['length']);
        exit($mem_data['body']);
    }

    /**
     * �������� ����� ���������� ��������� ������� ������ ������ ��������� ����.
     *
     * @param int $type   ��� ������.
     * @return int   ����� ���������.
     */
    function getLastModified($type) {
        $lastmod = 0;
        if($this->files[$type]) {
            foreach ($this->files[$type] as $file) {
                $lastmod = max($lastmod, @filemtime($this->root($file))); // @ - #0015499 stat failed
            }
        }
        return $lastmod;
    }
    
    /**
     * ������� ����� �� $seed (������ ���� ������). ���������� ���������� ����� PgQ.
     *
     * @param string $seed   �������������� ������ ���� ������ (��. self::_encodeSeed()).
     * @param boolean $light   true, ���� ������� �� ������������ �������� (������ �������, �� �� ������).
     * @return int   ��� ������ ��� 0.
     */
    function createBatchBySeed($seed, $light = false) {
        $type = $this->_decodeSeed($seed, $seed_expired);
        $this->getBatchesInfo();
        $batch_version = $this->_batches['version'];
        $batch_id = md5(implode(self::SEED_SEP, $this->files[$type]));
        return $this->_createBatch($type, $batch_id, $batch_version, $filename, $light);
    }
    
    /**
     * ������� �����.
     *
     * @param integer $type   ��� ������ (TYPE_CSS|TYPE_JS|TYPE_PHP_JS|TYPE_JS_UTF8)
     * @param string $batch_id   ��. (���) ������
     * @param string $batch_version   ��������������� ������ ������ (������� ������ ���� �������).
     * @param string $filename   �������� ��� ����� ������
     * @param boolean $light   true, ���� ������� �� ������������ �������� (������ �������, �� �� ������).
     * @return int   ��� ������ ��� 0.
     */
    private function _createBatch($type, $batch_id, $batch_version, &$filename, $light = false) {
        $log = $this->_log;
        $err = 0;
        $cfile = new CFile();
        $filename = self::STATIC_WDPATH . '/' . $this->createFileName($batch_id, $batch_version, $this->types[$type]);
        $lock_key = $this->_createBatchLockKey($batch_id, $batch_version);

        if( !$light || !$cfile->CheckPath($filename, false) ) { // �������� �� ������, ���� pgq ������������.
            $log->writeln("creating new batch file {$filename}, compressing content...");
            if($content = $this->_compress($type, $light)) {
                $cfile->exclude_reserved_wdc = true;
                if($cfile->putContent($filename, $content)) {
                    $log->writeln('saving batch info to memcached...');
                    if(!$this->setBatchVersion($batch_id, $batch_version)) {
                        $err = 3;
                    }
                } else {
                    $err = 2;
                }
            } else {
                $err = 1;
            }
        }
        
        if($err) {
            $log->writeln("failed ({$err})");
            $filename = NULL;
        }
        
        $log->writeln('unset lock...');
        $ok = $this->memBuff->delete($lock_key);
        $log->writeln($ok ? 'ok' : 'failed');
        
        return $err;
    }
    

    /**
     * ������� ���������� ������ �� ��������� ����.
     *
     * @param int $type   ��� ������.
     * @param boolean $light   true, ���� ������� �� ������������ �������� (������ �������, �� �� ������).
     * @return string   ������ � ������������ �������.
     */
    private function _compress($type, $light = false) {
        if($func = $this->_getTypeFunc('_compress', $type))
            return $this->$func($light, self::getCharsetType($type));
    }
	
	/**
	 * ������� ���-������
	 * 
     * @param boolean $light   true, ���� ������� �� ������������ �������� (������ �������, �� �� ������).
	 * @return string	������ � ������������ ���� ��������
	 */
    private function _compressJs($light = false, $charset = 'windows-1251') {
        if($charset == 'windows-1251') {
            return $this->compressJsFiles($this->files[self::TYPE_JS], NULL, $light, $charset);
        } else {
            return $this->compressJsFiles($this->files[self::TYPE_JS_UTF8], NULL, $light, $charset);
        }
	}
	

    /**
     * ���������� ������ ���������� ���������� ������.
     * 
     * @param array $files   ������ ���� ������ (� ������ �� �����).
     * @param string $root   ���� �� �����.
     * @param boolean $light   true, ���� ������� �� ������������ �������� (������ �������, �� �� ������).
     * @return string   ������ js.
     */
    function compressJsFiles($files, $root = NULL, $light = false, $charset='windows-1251') {
        $out = '';
        if($light) {
            foreach($files as $file) {
                $contents = @file_get_contents($root ? $root.$file : $this->root($file));
                $out .= preg_replace('/([\r\n]){2,}/', '$1', $contents)."\n";
            }
        }
        else {
            $js = '';
            foreach($files as $file) {
                $js .= ' --js '.escapeshellarg($root ? $root.$file : $this->root($file));
            }
            $cmd = 'java -jar ' . JAVA_PATH . "/compiler.jar --language_in ECMASCRIPT5 --charset {$charset} {$js}";
            ob_start();
            system($cmd);
            $out = ob_get_clean();
            if(!$out) {
                $this->_log->writeln("compress failed: {$cmd}");
            }
        }
        return trim($out);
	}

	/**
     * ������� ���-������
     * 
     * @return string	������ � ������������ ���� ��������
     */
    private function _compressPHP($light = false) {
        $out = '';
        $files = array();
        $tmp_path = '/var/tmp/static/';
        foreach ($this->files[self::TYPE_PHP_JS] as $file) {
            $parse = parse_url($file);
            $exp = explode('=', $parse['query']);
            $_GET[$exp[0]] = $exp[1];
            ob_start();
            //@todo: ���������� ��� ����������� ����� static.php
            //���������� � ������ ������� ���������� �� ��� �������������
            //include($this->root($parse['path']));
            $contents = ob_get_clean();
            if($light) {
                $out .= preg_replace('/([\r\n]){2,}/', '$1', $contents)."\n";
            } else {
                $tmp_js = basename($parse['path']).'.'.md5($parse['query']).'.tmp.js';
                file_put_contents($tmp_path.$tmp_js, $contents);
                $files[] = $tmp_js;
            }
        }
        return $light ? trim($out) : $this->compressJsFiles($files, $tmp_path, $light);
    }
	
	/**
	 * ������� ����� ������
	 * 
	 * @return string	������ � ������������ ���� ������	
	 */
    private function _compressCss() {
        $out='';
        foreach($this->files[self::TYPE_CSS] as $file){
            //$out .= exec('java -jar ' . JAVA_PATH . '/yuicompressor-2.4.7.jar --charset=windows-1251 ' . $this->root($file));
            //continue;
			$contents = file_get_contents($this->root($file));
            $this->compressCssContent($contents);
			$out .= $contents;
		}
        return trim($out);
	}


    function compressCssContent(&$contents) {
        $contents = preg_replace('~/\*.*\*/~Uis', '', $contents);
        $contents = preg_replace('/[\r\n]+/', '', $contents);
        $contents = preg_replace('/\s{2,}/', ' ', $contents);
        $contents = preg_replace('~\s([}{;:+=/,])~', '$1', $contents);
        $contents = preg_replace('~([}{;:+=/,])\s~', '$1', $contents);
        return $contents;
    }
	

    /**
     * �������� html-����, ������������� ������� ����� �� ��������� ����.
     *
     * @param string $file   ��� �����.
     * @param string $type   ��� �����.
     */
    function printTags($file, $type, $version = NULL) {
        if($func = $this->_getTypeFunc('printTags', $type)) {
            $file = $file . ($version ? "?v={$version}" : '');
            $this->$func($file, self::getCharsetType($type));
        }
    }
	
    /**
     * ������� ��� HTML ����������� CSS �����
     * 
     * @param string $file ���� � �����
     */
    function printTagsCss($file, $charset) {
        print "<link type=\"text/css\" href=\"{$file}\" rel=\"stylesheet\" charset=\"{$charset}\"/>\n";
	}
	
	/**
     * ������� ��� HTML ����������� JS �����
     * 
     * @param string $file ���� � �����
     */
    function printTagsJs($file, $charset){
        print "<script type=\"text/javascript\" src=\"{$file}\" charset=\"{$charset}\"></script>\n";
	}
	
	/**
     * ������� ��� HTML ����������� JS ����� ���������� ����� PHP
     * 
     * @param string $file ���� � �����
     */
    function printTagsPHP($file, $charset){
        print "<script type=\"text/javascript\" src=\"{$file}\" charset=\"{$charset}\"></script>\n";
	}


    /**
     * ���������� ��� ������ ������� ������, ��� ��������� ���� ������.
     * 
     * @param string $pfx   ������� ������.
     * @param int $type   ��� �����.
     * @return string   ��� ������.
     */
    private function _getTypeFunc($pfx, $type) {
        $func = $pfx . ucwords($this->types[$type]);
        if($type == self::TYPE_PHP_JS ) $func = $pfx."PHP";
        if(method_exists($this, $func))
            return $func;
        return NULL;
    }
    
    /**
     * ������� �������� �� ���� CSS ������ ����� (�������� ��� ������� �� ���������� �����)
     *
     * @param string $path_style ���� �� ����� ������ (���� - "/css/block/style.css" - ������������ ���� � ������ ����)
     * @param boolean $unique ������ ����� ���������� ������� (��������� ������������� ������) 
     * @return strine ������ CSS ��� @import 
     */
    public function collectBem($path_style, $unique = false) {
        $glob_dir =  dirname($path_style);
        $path = $_SERVER['DOCUMENT_ROOT'].$path_style;
        $css  = file_get_contents($path);
        $exp = explode("/", $path_style);
        foreach($exp as $i=>$k) if($k == ".." && isset($exp[$i-1])) unset($exp[$i-1], $exp[$i]);
        $dir = dirname(implode("/", $exp));
        $css = preg_replace("/url\(((?!(data:))[^\"|^\/].*?)\)/mix", "url(\"{$dir}/$1\")", $css);
        $css = preg_replace("/@import\s*url\(\"(.*?)\"\);/mix", "@import url(\"$glob_dir/$1\");", $css); // �������� ��� ���� �� ������
        $css = preg_replace_callback("/(@import\s*url\(\"(.*?)\"\);)/mix", create_function('$matches','return static_compress::collectBem($matches[2]);'), $css); // �������� ������ �� ����
        return $unique?self::getUniqueBemSource($css):$css;
    }
    
    /**
     * ������ ���������� CSS �� ����������� �������
     *
     * @param string $css_source    ���������� CSS  
     * @return string ��������� ������ 
     */
    public function getUniqueBemSource($css_source) {
        // ������� �� ������ ��� ������, ��� ����� ������ ��� ������ � ���������
        $css_source = str_replace(array("\r", "\n"), "", $css_source);
        $css_source = str_replace(array("\t"), " ", $css_source);
        $css_source = trim(preg_replace("/\/\*.*?\*\//", "", $css_source));
        if($css_source == "") return false;
        return $css_source; // 0024809

        if(preg_match_all("/(\s*(@media)?.+?)\{(.*?)(?(2)\}\s*?\}|\})/mix", $css_source, $matches)) {
            foreach($matches[1] as $u=>$value) {
                $value = trim($value);
                if(isset($result[$value])) {
                    if(strpos($value, '@media') !== false) {
                        if($result[$value] != trim($matches[3][$u])) {
                            $result[$value][] = trim($matches[3][$u]);
                        }
                    } else {
                        $style  = implode(";", array_map("trim", $result[$value]));
                        $rstyle = implode(";", array_map("trim", explode(";", trim($matches[3][$u]))));
                        if($style != $rstyle) {
                            $result[trim($value)] = explode(";", trim($style.$rstyle));
                        }
                    }
                } else {
                    if(strpos($value, '@media') !== false) {
                        $result[$value][] = trim($matches[3][$u]);
                    } else {
                        $result[$value] = explode(";", trim($matches[3][$u]));
                    }
                }
            }
            $css = "";
            foreach($result as $name=>$style) {
                if(strpos($name, '@media') !== false) {
                    $style = $name . "{\r". implode("}\r", $style) . "}\r}\r";
                    $css  .= $style;
                } else {
                    $style = $name . "{\r". implode(";\r", $style)."}\r";
                    $css  .= $style;
                }
            }
            return $css; 
        }
        return false;   
    }

    /**
     * ��������� ���� ���-������� � ����(�). ��� �� �������� �����, ��� ����� ���������� ����� ����� Add() � ����� � ���� (��� ��������� ��� IE).
     * @see static_compress::collectBem()
     *
     * @param boolean $unique   ��� collectBem(): ������ ����� ���������� ������� (��������� ������������� ������) 
     * @return integer   ���������� ���-�� ������������ ������.
     */
    function createBemBatchFiles($unique = true) {
        $this->_log->writeln('generating BEM batch file...');
        $cnt = 0;
        if($content = $this->collectBem($this->bem_src_path, $unique)) {
            $this->compressCssContent($content);
            $parts = array();
            $maxsize = self::MAX_CSSSIZE_IE * 1024;
            while( ($lpos = @strpos($content, '}', $maxsize)) !== false ) {
                $rpos = strrpos(substr($content, 0, $lpos), '}');
                $parts[] = substr($content, 0, $rpos + 1);
                $content = substr($content, $rpos + 1);
            }
            $parts[] = $content;
            ob_start();
            foreach($parts as $part) {
                $pname = $this->bemFilePath($cnt);
                if(!file_put_contents($pname, $part, LOCK_EX)) {
                    break;
                } else if(!$_SERVER['REQUEST_METHOD']) {
                    @chmod($pname, 0666);
                }
                $cnt++;
            }
            // ������� ������ �����, ���� ��������, ����� ����� �� ���������� ������ (��. addBem())
            $i = $cnt;
            while(file_exists($this->bemFilePath($i))) {
                unlink($this->bemFilePath($i++));
            }
            $err = ob_get_clean();
        } else {
            $err = 'content is empty';
        }
        
        if(!$cnt && $err) {
            $this->_log->writeln("WARNING: bem failed: {$err}");
        } else {
            $this->_log->writeln('ok');
        }
        
        return $cnt;
    }

    /**
     * ��������� ������� � ����� ���-����� � ����������� �� ������ ����� � ������.
     *
     * @param intger $num   ����� �����
     * @return string   ������� ��� �����
     */
    function bemFilePath($num = 0, $abs = true) {
        $sfx = $num ? '-'.$num : '';
        $pfx = $abs ? preg_replace('~[\\\/]$~', '', ABS_PATH) : '';
        return $pfx.str_replace('.css', $sfx.'.css', self::BEM_DEST_PATH);
    }

    /**
     * ����� add() ��� ���. ��������� � ���, ��� ��� ����� ���� ������ ������������� �� ��������� ������.
     * @see static_compress::createBemBatchFiles()
     * @see static_compress::add()
     */
    function addBem() {
        if(!$this->enabled) {
            return $this->add($this->bem_src_path);
        }
        $i = 0;
        $this->getBatchesInfo();
        $bcnt = (int)$this->_batches['bem_count'];
        if(!$bcnt && !file_exists($this->bemFilePath())) { // �� ���� �� ���������� updateBatchesVersion()
            $this->createBemBatchFiles();
        }
        do {
            $this->add($this->bemFilePath($i++, FALSE));
        } while( $i < $bcnt || !$bcnt && file_exists($this->bemFilePath($i)) );
    }
    
    /**
     * ������� ������, ������� ��� ����� ��������� � ���������� ������ 
     * ����������� � 6 ����� ���� ������ ���� @see hourly.php 
     *
     * @return boolean
     */
    public function cleaner() {
        global $DB;
        
        $path_static = self::STATIC_WDPATH;
        $this->_log->writeln('garbage collecting...');
        
        $dcnt = 0;
        $batches = $this->getBatchesInfo();
        $cur_version = $batches['version'];
        if(!$cur_version) {
            $this->_log->writeln('failed: batch version is undefined');
            return false;
        }
        
        $sql = "SELECT id, fname FROM file WHERE path = '{$path_static}/'";
        if($result = $DB->rows($sql)) { 
            $prev_version = 0;
            foreach($result as $key=>$value) {
                $file_version = $this->parseFileName($value['fname'], 1);
                $result[$key]['version'] = $file_version;
                if($file_version > $prev_version && $file_version < $cur_version) {
                    $prev_version = $file_version;
                }
            }
            $cfile = new CFile();
            foreach($result as $value) {
                if($value['version'] < $prev_version) {
                    $dcnt++;
                    $cfile->Delete($value['id']);
                }
            }
        }
        $this->_log->writeln("ok: {$dcnt} file(s) deleted");
    }
    
    
    /**
     * ��������� ��� ������ �� �����
     * 
     * @param string $filename    ��� ����� ������
     * @param integer $ret_mode   ����� ������������ �������: -1:��� � �������, 0:��. ������ (md5), 1:������ ������, 2:����������
     * @return mixed
     */
    function parseFileName($filename, $ret_mode = -1) {
        $ret = preg_split('/[_.]/', $filename);
        return $ret_mode < 0 ? $ret : $ret[$ret_mode];
    }
    
    function createFileName($batch_id, $version, $ext) {
        return $batch_id . '_' . $version . '.' . $ext;
    }

}
