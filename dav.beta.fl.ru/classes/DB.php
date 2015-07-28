<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/memBuff.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/londiste/londiste.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/log.php';

/**
 * ����� ��� ������ � ������ ������.
 *
 * �������������� ������������:
 * ?   - ������������� �����������. ����� ���������� ��� boolean � �������� NULL, ��������� ������ ������������ ��� ������
 * ?u  - ���� ��� � ����������, �� ��������� ������������ ������ �������������� ��������� addslashes, ������� ������ � _GET � _POST
 * ?b  - ������������ ����� ������ php � �������� ���� boolean
 * ?l  - ������������ ������ php � �������� ������ ��� IN. �������� ������� �������������� �� �������� ������������ ?
 * ?lu - ������������ ������ php � �������� ������ ��� IN. �������� ������� �������������� �� �������� ������������ ?u
 * ?a  - ������������ ������ php � �������� ������� postgres. �������� ������� �������������� �� �������� ������������ ?
 * ?au - ������������ ������ php � �������� ������� postgres. �������� ������� �������������� �� �������� ������������ ?u
 * ?i  - ������������ ������ ��� ��� integer
 * ?f  - ������������ ������ ��� ��� float
 * ?t  - ������������ ������ ��� ��� 'text'::text
 * ?ts - ����������� ������ � �������� ������ ��� ������������� � to_tsquery()
 */
class DB {

	/**
	 * ������� ��� �������� ��� ������ � ��������.
	 * ������������ $_SERVER['DOCUMENT_ROOT'].
	 * 
	 */
	const LOGDIR = "/classes/log";
	
	/**
	 * ������� ��� ������ � memcached ��� ������������� ������ ���� � �������
	 * 
	 */
    const MPREFIX = 'DBCache';
    
	const DEFAULT_TIMEOUT = 0; // �������� 'statement_timeout' �� ��������� (�� �������).

    const STBY_SUFFIX = '_standby'; // �������, ������� ����������� � ����� �������� ($this->alias) ������� ��� ��������� ���������� ��� Standby-�������, ��������, 'master_standby'
	

    /**
     * ���� ������� ������������� Standby.
     */
    const STBY_OFF     = 0;          // ������ ������.
    const STBY_CACHED  = 0x00000001; // ���� ������ ������������ ����������� (���������� ����� ������ DB->cache()).
    const STBY_NOACT   = 0x00000002; // ���� ��������� (��������������) �������� ����� ���� N ������ �����.
    const STBY_NOAUTH  = 0x00000004; // ���� ���� �������������.
    const STBY_NOSHELL = 0x00000008; // ���� ������ ������� �� �� �������
    const STBY_ANY     = 0x7FFFFFFF; // ����� ������������ ������.
    
    /**
     * ������� ���������� ������� ������������� Standby.
     */
    const STBY_OPTS_ANY_MASK  = 1; // ���� �������, ��� �������� ������ �� �������, ����� ������������ �������.
    const STBY_OPTS_ALL_MASK  = 2; // ���� �������, ��� �������� ������� �� �������, ����� ������������ �������.
    const STBY_OPTS_NOACT_LAG = 3; // ������ ����� STBY_NOACT ��� ��������� �������.
    const STBY_OPTS_CAPACITY  = 4; // �������� ������������� ������ (�����, �� 0 �� 1; <=0:����� ��������; �� ���������:1). ��� ����� ������ ����������� ��������.
    const STBY_OPTS_LOGSTAT   = 5; // �������� ���������� (������ ���� ����. �������, ��. ����������)?

    /**
     * ���� ������ ���������.
     */
    const PG_ERR_READ_ONLY         = 25006;
    const PG_ERR_RECOVERY_CONFLICT = 40001;

    /**
     * ���� ��������.
     */
    const SQLTYPE_MOD = 0x01; // ����� ������� ������.
    
	/**
	 * ������ � ��������� ������������� �����������. ������ ������ � ��������
	 * ���������� ������, � �� �������. ��� ����� ��� ����������� �����������
	 * ����� �������������� �� ���� ����������� ������.
	 * 
	 * @var <type> 
	 */
	public static $connections = array();
	
	
	/**
	 * �������� 'statement_timeout' (-1:�� ������������� (����������� �� ���������), N(�����������):���������� ����� ��������).
	 * ����� ������������� ����� ���������� � 0. 
	 *
	 * @var integer
	 */
	public static $timeout = -1;
	
    /**
     * ������ ������������� Standby-��������.
     * @var array
     */
    protected static $_standbyOpts = array();

    /**
     * ������ ������ ��� ������������ ������ �� ������������� standby. ��������.
     * ��. protected $_stby_log_row;
     * @var array
     */
    static protected $_stby_log = array();

    /**
     * ������ �� ������� ������ self::$_stby_log
     * @var array
     */
    protected $_stby_log_row;

	/**
	 * ������ ��������� ��� ���������� ���������� �������.
	 *
	 * @var string
	 */
	public $error = '';

	/**
	 * ��� �����������. � �������� ����� ������������ ���� ������� $pg_db �� config.php.
	 * ����� �������� � ����� ��� ����� ������������ � ��������� ����� ��������� ����
	 * ��������� ������� DB.
	 *
	 * @var string
	 */
	public $alias = '';

    /**
     * �������� ��� �����������, ������������ � ������� DB::connect(). ��� ����� ���� ����� ��� standby-������.
     * @var string
     */
    public $realAlias;

	/**
	 * ����� ����������� ��� �������. ��������� ������ �� 1-�� � ����������.
	 *
	 * @var array
	 */
	public $slaves;
	
	/**
	 * ��������� ������������ SQL ������
	 *
	 * @var string
	 */
	public $sql = '';

    /**
     * �������� SQL (�� ������������).
     *
     * @var string
     */
    public $origSql;

    /**
     * ��� �������. SQLTYPE_MOD -- ������ �������� DML-���������� (update, insert, delete).
     *
     * @var integer
     */
    protected $_sqlType = 0;

	/**
	 * ����� �������� ��� $this->mode == -1
	 * 
	 * @var string
	 */
	public $sqls = '';

	/**
	 * ��������� ����������� ���������� �������:
	 * string ��� $this->mode == -2;
	 * boolean ��� $this->mode == -1;
	 * resource ��� $this->mode == 0;
	 * array ��� $this->mode == 1;
	 *
	 * @var mixed
	 */
	public $res = FALSE;

	/**
	 * ������ ������������ ������� ��� ������������� memcache, ���� �� �������
	 * �������� ���������� � ���� ��� FALSE ���� ������ �������� �� memcache.
	 *
	 * @var resource
	 */
	public $oRes = FALSE;

	/**
	 * ����� ���������� ����������� ������� � ��������. ��������� ������ ��� $this->mode >= 0.
	 * 
	 * @var integer
	 */
	public $time = 0;
	
	/**
	 * �������� ������ ���� ��� ������ �� � ��� ����
	 * 
	 * @var string
	 */
	protected $log = '';

	/**
	 * ���������� ����������� �������� ������ DB
	 *
	 * @var integer
	 */
	protected static $objects = 0;

	/**
	 * ������� �������� ����������
	 * @var resource
	 */
	protected $_transaction;

    /**
     * ������� ���������� (�����������������)
     * @var integer
     */
    protected $_transactionCount = 0;

	/**
	 * ���� ��������� ������.
	 * TRUE - ������ ��������������, FALSE - ������������
	 * 
	 * @var boolean
	 */
	public $errors = TRUE;

	/**
	 * ���� ��� ������ ������.
	 * ���� � ������ �������� ������� ��� �����, �� ��� �����������
	 * ������� ����� ������������ � ���� ���� ������ � �������� �� ����������
	 * 
	 * @var string
	 */
	public $debug = '';

	/**
	 * ������ ��� ��������� ��� ����������� ������ � $this->debug ����
	 *
	 * @var array
	 */
	protected $debugLog = array();

	/**
	 * ����� ��������������� � ������ ������ ������������.
	 * ������������ � ������ parse.
	 *
	 * @var integer
	 */
	protected $pcnt = 1;

	/**
	 * ������ ��������� �� ����� ������ �������������.
	 * ������������ � ������ parse.
	 *
	 * @var string
	 */
	protected $perr = '';
	
	/**
	 * ������ ��� ������������� � �������.
	 * ������������ � ������ parse.
	 * 
	 * @var array
	 */
	protected $args = array();

	/**
	 * ������� ����������� ��� debug_backtrace(), �������
	 * ����� ������������ ����� squery
	 * 
	 * @var integer
	 */
	protected $traceLevel = 1;
	
	/**
	 * ����� � ������� ����� ����������� ����������� ������.
	 * -2 - ������� ����� ������� ������ ��� ����������
	 * -1 - ��������� ������ � $this->sqls ��� ������������ ����������
	 *  0 - ����������� �����, ��������� ������
	 *  1 - ��������� ������ � �������������� memcache
	 *
	 * @var integer
	 */
	protected $mode = 0;

	/**
	 * ����� ����� ���������� ������� � memcahce.
	 * ������������ ��� $this->mode == 1
	 *
	 * @var integer
	 */
	protected $ttl = 0;
	
	/**
	 * ��� ������, � ������� ������ ���� �������� ��� �������.
	 * ������������ ��� $this->mode == 1
	 *
	 * @var string
	 */
	protected $mgkey = NULL;

	/**
     * ����������� � memcached
     * 
     * @var resource
     */
    protected $memcached = NULL;

    /**
     * ���� ����������, �� ����������� ���� �� ��������� ��������.
     * �������� ������ ��� ���������� magic_quotes_gpc.
     *
     * @var boolean
     */
    public $checkAutoSlashes = true;

    /**
     * ������ ����� ��� ������������ �������, � ������ ����������� � �������
     * 
     * @var array
     */
    public $keys = array ();
    
    
    /**
     * ����� ������ ���. ���������� � ���� ������.
     * ��������, sql-������� �� ������� � ���, ���� ����� 'NOSQL'.
     * 
     * @var string
     */
    public $loglevel = 'DEFAULT';
    
    /**
     * �������� �� ������ � �����
     * 
     * @var boolean
     */
    public $error_output = true;
    
	/**
	 * �����������. ������� ����������� � ���� ������, ���� ��� ��� �� �������.
	 *
	 * @param string $alias   ���� ������� $pg_db �� config.php � ������� �����������
	 */
	public function __construct($alias='plproxy') {
	    $this->checkAutoSlashes = true;
	    $this->slaves = preg_split('/\s+/', trim($alias));
		$this->alias = array_shift($this->slaves);
		++self::$objects;
	}

    /**
     * ���������� �������������� �������, ���� 'mquery', 'mrow' � �.�. ��� read-only ��������.
     * ������������ ��� ����������� �������� ��� ������������� Standby.
     * @see DB::query()
     * @see DB::checkStandby()
     */
    public function __call($name, $args) {
        if ( in_array($name, array('mquery', 'mrows', 'mrow', 'mcol', 'mval')) ) {
            $this->_sqlType = DB::SQLTYPE_MOD;
            $this->traceLevel = ($name != 'mquery') + 5;
            return call_user_func_array(array($this, substr($name, 1)), $args);
        }
    }

	/**
	 * ����������. ��������� �������� ���������� � ���������� � ����(�) debug ����������.
	 *
	 */
	public function __destruct() {
		--self::$objects;
		if ($this->debugLog) {
		    $log = new log("db/".$this->alias."/debug/".$this->debug);
			for ($i=0; $i<count($this->debugLog); $i++) {
				$log->writeln($this->debugLog[$i]['text']);
			}
		}
        $log = new log("db/".$this->alias.'/'.date('Y-m-d').".log");
		if ($this->log) {
            $log->writeln($this->log);
		}

        if($this->_transaction) {
            $rollback = false;
            $xstat = pg_transaction_status($this->_transaction);
            $xcodes = array(
                PGSQL_TRANSACTION_UNKNOWN => 'PGSQL_TRANSACTION_UNKNOWN',
                PGSQL_TRANSACTION_IDLE => 'PGSQL_TRANSACTION_IDLE',
                PGSQL_TRANSACTION_INTRANS => 'PGSQL_TRANSACTION_INTRANS',
                PGSQL_TRANSACTION_INERROR => 'PGSQL_TRANSACTION_INERROR',
                PGSQL_TRANSACTION_ACTIVE => 'PGSQL_TRANSACTION_ACTIVE',
            );

            switch($xstat) {
                case PGSQL_TRANSACTION_INTRANS :
                case PGSQL_TRANSACTION_INERROR :
                case PGSQL_TRANSACTION_ACTIVE :
                    $rollback = true;
                    break;
            }

            if($rollback) {
                $err = "Transaction status is BAD and it rollbacked: {$xcodes[$xstat]}, name=" . $this->alias;
                $this->rollback();
            } else {
                $err = "Transaction counter is BAD on DESTRUCT: status {$xcodes[$xstat]}";
                $this->_transaction = NULL;
            }
            $this->err($err);
        }


        if(!self::$objects) {
            setLastUserAction();

            if(DB::$_stby_log) { // ����� ������, ����������.
                $stby_db = new DB('stat');
                setlocale(LC_ALL, 'en_US.UTF-8');

                foreach(DB::$_stby_log as $key=>$val) {
                    list($val['day'], $val['real_mask'], $val['opts']) = explode('=', $key);
                    $sql = "
                      UPDATE stby_log2
                         SET master_cnt = master_cnt + ?i, standby_cnt = standby_cnt + ?i,
                             master_time = master_time + interval ?, standby_time = standby_time + ?, ro_errors_cnt = ro_errors_cnt + ?i
                       WHERE day = ? AND opts = ? AND real_mask = ?i
                    ";
                    $res = $stby_db->query($sql, (int)$val['master_cnt'], (int)$val['standby_cnt'], (float)$val['master_time'].' seconds', (float)$val['standby_time'].' seconds',
                                                 (int)$val['ro_errors_cnt'], $val['day'], $val['opts'], (int)$val['real_mask']  );
                    if(!pg_affected_rows($res)) {
                        $sql = "
                          INSERT INTO stby_log2 (master_cnt, standby_cnt, master_time, standby_time, ro_errors_cnt, day, opts, real_mask)
                          VALUES (?i, ?i, ?, ?, ?i, ?, ?, ?i)
                        ";
                        $stby_db->query($sql, (int)$val['master_cnt'], (int)$val['standby_cnt'], (float)$val['master_time'].' seconds', (float)$val['standby_time'].' seconds',
                                              (int)$val['ro_errors_cnt'], $val['day'], $val['opts'], (int)$val['real_mask']  );
                    }
                }
            }
            DB::$_stby_log = array();
        }
	}
	

	/**
	 * ������������ ��� ������ ����������� ������ ������, ������� ������ ��� ���������� ��������.
	 *
	 * @param string $error         ����� ������
	 * @param string $sql           sql ������
	 * @param integer $traceLevel   ������� ����������� ��� debug_backtrace()
	 */
    protected function err($error, $sql='', $traceLevel=FALSE, &$pgcode = -1) {
		$this->error = $error;
        if($pgcode !== -1) {
            if (strpos($this->error, 'read-only transaction') !== false) {
                $pgcode = DB::PG_ERR_READ_ONLY;
            } else if (strpos($this->error, 'conflict with recovery') !== false) {
                $pgcode = DB::PG_ERR_RECOVERY_CONFLICT;
            }
            // ...
        }

		if ($this->errors) {
			$message = "";
			if ($traceLevel !== FALSE) {
				$trace = debug_backtrace();
				$message = "FILE: {$trace[$traceLevel]['file']}, LINE: {$trace[$traceLevel]['line']}\n";
			}
			$message .= $error . ($sql && $this->loglevel != 'NOSQL' ? "\nSQL: {$sql}" : '');
			$this->log .= "-- " . date('Y-m-d H:i:s') . " --------------------------------------------------\n{$message}\n";
			if ((defined('IS_LOCAL') || SERVER == 'beta' || SERVER == 'alpha') && $this->error_output) {
				if (empty($argv)) {
					echo "<pre style='color: red'>{$message}</pre>";
				} else {
					echo "{$message}\n";
				}
			}
		}
	}

	/**
     * ��������� ����������� � ���� ������. �������� �������������.
     * � ������ ������� ��� ����������� ������������� ���������� ������� ������� die.
     * @param  int  $force_new     ����  $force_new == TRUE, ������������� ��������� ����� �����������
     *
     * @return resource   ������ �����������
     */
    public function connect($force_new = false, $only_master = false) {
        $alias = $this->alias;
        if($this->_transaction) {
            return $this->_transaction;
        }

        if (!$only_master) {
            if($this->slaves) {
                $alias = $this->slaves[mt_rand(0, count($this->slaves) - 1)];
                $is_slave = true;
            }
            if($this->checkStandby($alias)) {
                $alias = $alias.DB::STBY_SUFFIX;
                $is_standby = true;
            }
        }
        
        if($conf = $GLOBALS['pg_db'][$alias]) {
            if ($force_new || !self::$connections[$alias] || !is_resource(self::$connections[$alias])) {
                self::$connections[$alias] = pg_connect("host={$conf['host']} port={$conf['port']} dbname={$conf['name']} user={$conf['user']} password={$conf['pwd']}");
            }
        }
        
        if (!self::$connections[$alias]) {
            if($is_slave || $is_standby) {
                return $this->connect($force_new, true);
            }
            $this->err("Could not connect to database {$alias}");
			if($this->alias == 'master') {
    			die;
    	    }
        }

        $this->realAlias = $alias;

        return self::$connections[$alias];
    }

	
	/**
	 * ��������� sql ������. ������������ �� ������������.
	 *
     * @param  string  $sql  sql ������ ��� ������ ������ ���� ���������� ��������� ���������� �������
     * @param  string  $orig_sql  �������� ������ sql-������ (� ��������������� ��������������).
	 * @return mixed         ��� sql ������� - ������ ������������ �������
	 *                       ��� ������������� memcache - ��������� ������ � ����������� �������
	 *                       ��� ���������� ������� - TRUE
	 *                       ��� ������� ������ ������� - ������ � ��������
	 *                       ��� ������ - FALSE
	 */
    public function &squery($sql = '', $orig_sql = NULL) {
		$this->error = '';
        $this->res   = NULL;
        $this->oRes  = NULL;
        $sto = '';

        if(isset($this->_stby_log_row)) {
            unset($this->_stby_log_row);
        }
        
		if ($sql === '') {
			$this->sql  = $this->sqls;
			$this->sqls = '';
		} else {
			$this->sql = $sql;
		}
        $this->origSql = $orig_sql ? $orig_sql : $this->sql;
		
		if (self::$timeout >= 0) {
		    $sto = 'SET statement_timeout = '. self::$timeout . ';';
		    if (self::$timeout == self::DEFAULT_TIMEOUT) {
		        $this->setTimeout(-1); // �.�. �������� � ���������, � ����. ��� �� ����� �������������.
		    }
		}

		if ($this->mode >= 0) {
			$exec = TRUE;
            $this->time = microtime(TRUE);
            if ($this->mode == 1) {
				$md5 = md5($this->sql);
				$this->res = $this->mem->get($md5);
				if (!empty($this->res)) {
					$exec = FALSE;
				}
			}
            if ($this->mode == 2) {
                // stats temp
                $trace = debug_backtrace();
                $line  = $trace[$this->traceLevel-1]['line'];
                $stats = $this->memcached->get(self::MPREFIX . 'S');
                if ( empty($stats) ) {
                    $stats = array();
                }
                // stats temp
                $hash = self::MPREFIX . 'D_' . md5($this->sql);
                $keys = array();
                $exec = TRUE;
                $version = (int) ($this->time * 100);
                if ( $this->keys ) {
                    $this->res = $this->memcached->getMulti(array_merge($this->keys, array($hash)));
                    $exec = !((bool) $this->res[$hash]);
                    foreach ( $this->keys as $v ) {
                        if ( empty($this->res[$v]) ) {
                            $keys[$v] = $version;
                            $exec = TRUE;
                        } else if ( !$rebuild && $this->res[$v] > $this->res[$hash]['ver'] ) {
                            $exec = TRUE;
                        }
                    }
                }
                if ( !$exec ) {
                    $this->res =& $this->res[$hash]['data'];
                    // stats temp
                    $stats[$line]['mem'] = (int) $stats[$line]['mem'] + 1;
                    $this->memcached->set(self::MPREFIX . 'S', $stats);
                    // stats temp
                }
            }
			if ( $exec ) {
                
                for($i=0,$only_master=false; $i<2; $i++) {
                    $ts = microtime(true);
                    $cn = $this->connect(false, $only_master);
                    pg_set_client_encoding($cn, 'WIN1251');
                    $this->res = @pg_query($cn, $sto . $this->sql);
                    $tt = microtime(true) - $ts;
                    if (!$this->res) {
                        $tt = 0;
                        $this->err(pg_last_error($cn), $this->sql, $this->traceLevel, $pgcode);
                        if(!$i && ($pgcode == DB::PG_ERR_READ_ONLY || $pgcode == DB::PG_ERR_RECOVERY_CONFLICT)) {
                            $only_master = true;
                            continue;
                        }
                    }
                    if(isset($this->_stby_log_row)) {
                        $pfx = strpos($this->realAlias, DB::STBY_SUFFIX) ? 'standby_' : 'master_';
                        $this->_stby_log_row[$pfx.'time'] += $tt;
                        $this->_stby_log_row[$pfx.'cnt']++;
                        $this->_stby_log_row['ro_errors_cnt'] += (int)$only_master;
                    }
                    break;
                }

                if ($this->mode == 1 && ($this->res !== FALSE)) {
                    $this->oRes = $this->res;
                    $this->res  = pg_fetch_all($this->res);
                    $this->mem->set($md5, $this->res, $this->ttl, $this->mgkey);
                }
                if ($this->mode == 2 && ($this->res !== FALSE)) {
                    $this->oRes = $this->res;
                    $this->res  = pg_fetch_all($this->res);
                    if ( $keys ) {
                        $this->memcached->setMulti($keys);
                    }
                    $this->memcached->set($hash, array('ver'=>$version, 'data'=>$this->res), $this->ttl);
                    // stats temp
                    $stats[$line]['db'] = (int) $stats[$line]['db'] + 1;
                    $this->memcached->set(self::MPREFIX . 'S', $stats);
                    // stats temp
                }
            }
			$this->time = microtime(TRUE) - $this->time;
			if ($this->debug) {
				$c = count($this->debugLog);
				$trace = debug_backtrace();
				$this->debugLog[$c]['file']  = $this->debug;
				$this->debugLog[$c]['text'] .= "\n-- [" . date("Y-m-d H:i:s") . "] "; 
                $this->debugLog[$c]['text'] .= "------------------------------------------------------------------\n";
				$this->debugLog[$c]['text'] .= "FILE: {$trace[$this->traceLevel-1]['file']}\n";
                $this->debugLog[$c]['text'] .= "LINE: {$trace[$this->traceLevel-1]['line']}\n";
                $this->debugLog[$c]['text'] .= "TIME: {$this->time} sec\n";
				$this->debugLog[$c]['text'] .= "MODE: {$this->mode}\n";
                $this->debugLog[$c]['text'] .= "SOURCE: " . ($exec? "DB": "CACHE") . "\n";
                if ( $this->mode == 2 ) {
                    $this->debugLog[$c]['text'] .= "KEYS: " . var_export($this->keys, TRUE) . "\n";
                }
                $this->debugLog[$c]['text'] .= "RESULT: ".($this->res !== FALSE? "OK": "ERROR")."\n";
                $this->debugLog[$c]['text'] .= "{$this->sql}\n";
			}
            $this->keys = array();
			$this->traceLevel = 1;
            $this->mode = 0;
		} else if ($this->mode == -1) {
			$this->sqls .= preg_replace("/;\s*$/", "", $this->sql) . "; ";
			$this->mode = 0;
			$this->res = TRUE;
		} else if ($this->mode == -2) {
			$this->mode = 0;
			$this->res = $this->sql;
		}

        $this->_sqlType = 0;
        return $this->res;
	}

	
	/**
	 * ��������� sql ������.
	 * ������ � ����������� ��������� ������ ����� �������������� � �������� ������ ��� �������������.
     * ���� ������ �� ������ �� ������, ����������� DB::mquery().
     * @see DB::__call()
	 *
	 * @param  string  $sql  sql ������ ��� ������ ������ ���� ���������� ��������� ���������� �������.
	 *                       sql ������ ����� ��������� ������������
	 * @return mixed         ��� sql ������� - ������ ������������ �������
	 *                       ��� ������������� memcache - ��������� ������ � ����������� �������
	 *                       ��� ���������� ������� - TRUE
	 *                       ��� ������� ������ ������� - ������ � ��������
	 *                       ��� ������ - FALSE
	 */
	public function &query($sql = '') {
		$this->traceLevel = ($this->traceLevel > 2)? $this->traceLevel: 2;
		if ($sql !== '') {
			$this->pcnt = 1;
			$this->perr = '';
			$this->args = func_get_args();
			$count = count($this->args);
			if ($count) {
				$parsed = preg_replace_callback("/\?(?:lu|au|ai|i|f|u|b|l|a|t|x)?/", array($this, '_parse'), $sql, $count-1);
			}
			if ($this->perr) {
				$this->err($this->perr, $sql, $this->traceLevel-1);
				return FALSE;
			}
		} else {
			$parsed = '';
		}
        $res =& $this->squery($parsed, $sql);
		return $res;
	}
	
	/**
	 * ��������� sql ������ � ���������� ������ ���������� �������.
	 * ������ � ����������� ��������� ������ ����� �������������� � �������� ������ ��� �������������.
     * ���� ������ �� ������ �� ������, ����������� DB::mrows().
     * @see DB::__call()
     *
	 * @param  string  $sql  sql ������ ��� ������ ������ ���� ���������� ��������� ���������� �������.
	 * @return mixed         ��� sql ������� (� ��� ����� memcache)
	 *                           - ������ ���������� ������� � �������� ���������� �������.
	 *                       ��� ���������� ������� - TRUE
	 *                       ��� ������� ������ ������� - ������ � ��������
	 *                       ���� ��� ������ ��� ������ - ������ ������
	 */
	public function rows($sql = '') {
		$mode = $this->mode;
        $this->traceLevel = max($this->traceLevel, 4);
		$args = func_get_args();
		call_user_func_array(array($this, 'query'), $args);
		if ($mode == 0 && $this->res) {
			$r = pg_fetch_all($this->res);
		} else {
            $r =& $this->res;
		}
		return ($r === FALSE)? array(): $r;
	}
	
	/**
	 * ��������� sql ������ � ���������� ������ ������ ���������� �������.
	 * ������ � ����������� ��������� ������ ����� �������������� � �������� ������ ��� �������������.
     * ���� ������ �� ������ �� ������, ����������� DB::mrow().
     * @see DB::__call()
     *
	 * @param  string  $sql  sql ������ ��� ������ ������ ���� ���������� ��������� ���������� �������.
	 * @return mixed         ��� sql ������� (� ��� ����� memcache)
	 *                           - ������ ������ ������ ���������� ������� � �������� �������������� �������.
	 *                       ��� ���������� ������� - TRUE
	 *                       ��� ������� ������ ������� - ������ � ��������
	 *                       ���� ��� ������ ��� ������ - ������ ������
	 */
	public function row($sql = '') {
		$mode = $this->mode;
        $this->traceLevel = max($this->traceLevel, 4);
		$args = func_get_args();
		call_user_func_array(array($this, 'query'), $args);
		if ($mode == 0 && $this->res) {
			$r = pg_fetch_assoc($this->res);
		} else if ($mode > 0 && isset($this->res[0])) {
			$r =& $this->res[0];
		} else {
			$r =& $this->res;
		}
		return ($r === FALSE)? array(): $r;
	}
	
	/**
	 * ��������� sql ������ � ���������� �������� ������ ������ � ������ ������� ���������� �������.
	 * ������ � ����������� ��������� ������ ����� �������������� � �������� ������ ��� �������������.
     * ���� ������ �� ������ �� ������, ����������� DB::mval().
     * @see DB::__call()
     *
	 * @param  string  $sql  sql ������ ��� ������ ������ ���� ���������� ��������� ���������� �������.
	 * @return mixed         ��� sql ������� (� ��� ����� memcache)
	 *                           - �������� ������ ������ � ������ �������.
	 *                       ��� ���������� ������� - TRUE
	 *                       ��� ������� ������ ������� - ������ � ��������
	 *                       ���� ��� ������ ��� ������ - NULL
	 */
	public function val($sql = '') {
		$mode = $this->mode;
        $this->traceLevel = max($this->traceLevel, 4);
		$args = func_get_args();
		call_user_func_array(array($this, 'query'), $args);
		if ($mode == 0 && $this->res) {
			$r = pg_fetch_assoc($this->res);
			$r = @current($r);
		} else if ($mode > 0 && isset($this->res[0])) {
			$r =& $this->res[0];
			$r = @current($r);
		} else {
			$r =& $this->res;
		}
		return ($r === FALSE)? NULL: $r;
	}

	/**
	 * ��������� sql ������ � ���������� ������ ������ ������� ���������� �������.
	 * ������ � ����������� ��������� ������ ����� �������������� � �������� ������ ��� �������������.
     * ���� ������ �� ������ �� ������, ����������� DB::mcol().
     * @see DB::__call()
     *
	 * @param  string  $sql  sql ������ ��� ������ ������ ���� ���������� ��������� ���������� �������.
	 * @return mixed         ��� sql ������� (� ��� ����� memcache)
	 *                           - ������ ������ ������� ���������� ������� � �������� ���������� �������.
	 *                       ��� ���������� ������� - TRUE
	 *                       ��� ������� ������ ������� - ������ � ��������
	 *                       ���� ��� ������ ��� ������ - ������ ������
	 */
	public function col($sql = '') {
		$mode = $this->mode;
        $this->traceLevel = max($this->traceLevel, 4);
		$args = func_get_args();
		call_user_func_array(array($this, 'query'), $args);
		$r = FALSE;
		if ($mode == 0 && $this->res) {
			while ($row = pg_fetch_row($this->res)) {
				$r[] = $row[0];
			}
		} else if ($mode > 0 && is_array($this->res)) {
			$c = count($this->res);
			for ($i=0; $i<$c; $i++) {
				$r[] = current($this->res[$i]);
			}
		} else {
			$r =& $this->res;
		}
		return ($r === FALSE)? array(): $r;
	}

	/**
	 * ��������� ������� INSERT � ������� $table ��������� � �������� ���� ������� ����� ������� $data,
	 * � � �������� ������ �������� �� $data. ����� ������������ ��������� ������, ����� ������� ���������
	 * ����� ������ ����� ��������.
	 * 
	 * @param  string $table      ������� ��� ������ ����� ������� UPDATE
	 * @param  array  $data       ������ ��� ������: ����� ������� - �������, �������� - ������.
	 *                            ��� ����������� ������, ����� ������ ������� ������� ������ ����� ����� ���������
	 * @param  string $returning  �������, �������� ������� ����� ������� ����� ���������� �������
	 * @return mixed              � ������� ������ ��� $returning - ������ ������������ �������
	 *                            � ������� ������ c $returning - ������ ������� ����� ���������� �������
	 *                            ��� ���������� ������� - TRUE
	 *                            ��� ������� ������ ������� - ������ � ��������
	 *                            ��� ������ - FALSE
	 */
	public function insert($table, $data, $returning = '') {
        $this->_sqlType = DB::SQLTYPE_MOD;
        $mode = ($this->mode == 1)? 0: $this->mode;
		$columns = '';
		$values  = '';
		if (isset($data[0]) && is_array($data[0])) {
			$j = 0;
			$vals = array();
			for ($i=0,$max=count($data); $i<$max; $i++) {
				$vals[$j] = '';
				foreach ($data[$i] as $column=>$value) {
					if ($i == 0) $columns .= '"'.$column.'",';
					if ($value === NULL) {
						$vals[$j] .= 'NULL,';
					} else if ($value === TRUE) {
						$vals[$j] .= 'TRUE,';
					} else if ($value === FALSE) {
						$vals[$j] .= 'FALSE,';
					} else {
					    if($this->hasAutoSlashes($value))
					        $value = stripslashes($value);
						$vals[$j] .= "'".pg_escape_string((string) $value)."',";
					}
				}
				if ($vals[$j]) {
					$vals[$j] = substr($vals[$j], 0, strlen($vals[$j])-1);
				} else {
					$this->err("No data for insert to table {$table}", "", 1);
					return FALSE;
				}
				++$j;
			}
			$values = '('.implode('),(', $vals).')';
		} else {
			foreach ($data as $column=>$value) {
				$columns .= '"'.$column.'",';
				if ($value === NULL) {
					$values .= 'NULL,';
				} else if ($value === TRUE) {
					$values .= 'TRUE,';
				} else if ($value === FALSE) {
					$values .= 'FALSE,';
				} else {
  				    if($this->hasAutoSlashes($value))
  				        $value = stripslashes($value);
					$values .= "'".pg_escape_string((string) $value)."',";
				}
			}
			if ($values) {
				$values = '('.substr($values, 0, strlen($values)-1).')';
			} else {
				$this->err("No data for insert to table {$table}", "", 1);
				return FALSE;
			}
		}
		$sql = "INSERT INTO {$table}(".substr($columns, 0, strlen($columns)-1).") VALUES{$values}".($returning? " RETURNING {$returning}": "");
		$this->traceLevel = 2;
		$res =& $this->squery($sql);
		if ($returning && $mode == 0 && $res) {
			$row = pg_fetch_row($res);
			return $row[0];
		} else {
			return $res;
		}
	}


	/**
	 * ��������� ������� UPDATE ������� $table ��������� � �������� ���� ������� ����� ������� $data,
	 * � � �������� ������ �������� �� $data. ��������� � ����������� ��������� ������ ����� ��������������
	 * � �������� ������ ��� ������������� ��������� � $where.
	 * 
	 * @param  string $table   ������� ��� ������ ����� ������� UPDATE
	 * @param  array  $data    ������ ��� ������: ����� ������� - �������, �������� - ������
	 * @param  string $where   ��� ������ ����� ����������� ����� WHERE � �������. ����� ������������ ������������
	 * @return mixed           � ������� ������ - ������ ������������ �������
	 *                         ��� ���������� ������� - TRUE
	 *                         ��� ������� ������ ������� - ������ � ��������
	 *                         ��� ������ - FALSE
	 */
	public function update($table, $data, $where) {
        $this->_sqlType = DB::SQLTYPE_MOD;
        $sql = '';
		foreach ($data as $column=>$value) {
			$sql .= '"'.$column.'"=';
			if ($value === NULL) {
				$sql .= 'NULL,';
			} else if ($value === TRUE) {
				$sql .= 'TRUE,';
			} else if ($value === FALSE) {
				$sql .= 'FALSE,';
			} else {
   			    if($this->hasAutoSlashes($value))
   			        $value = stripslashes($value);
				$sql .= "'".pg_escape_string((string) $value)."',";
			}
		}
		if ($sql) {
			$this->pcnt = 3;
			$this->args = func_get_args();
			$where = preg_replace_callback("/\?(?:lu|au|ai|i|f|u|b|l|a|t|x)?/", array($this, '_parse'), $where, count($this->args)-3);
			if ($this->perr) {
				$this->err("Placeholders contain errors in UPDATE command for table {$table}", $where, 1);
				return FALSE;
			}
			$sql = "UPDATE {$table} SET ".substr($sql, 0, strlen($sql)-1)." WHERE {$where}";
			$this->traceLevel = 2;
			$res =& $this->squery($sql);
			return $res;
		}
		$this->err("No data for update table {$table}", "", 1);
		return FALSE;
	}

    /**
     * ������ ����������.
     * @return resource   �������, ������������ �����������
     */
    function start() {
        if(!$this->_transaction) {
            $this->_transaction = $this->connect(false, true);
            if (!$this->squery('START TRANSACTION')) {
                $this->_transaction = NULL;
                $this->_transactionCount = 0;
                return false;
            }
        }
        $this->_transactionCount++;
        return $this->_transaction;
    }


    /**
     * ������������� ����������.
     * @param boolean $force   ������ ����� ����������, ���� ���� ���������� ������� ���������� (� ����� ������� ���������)
     * @return resource   ������ ������������ ������� ��� FALSE � ������ �������
     */
    function commit($force = false) {
        $res = true;
        $err = false;
        $debug = true; // ���� �������, ������� �� ����� ��������, ����� ������ ���������� ������ � ���. ���� �������� �
                       // ������� ������ ���������� (���� �����, �� ��� �������, ���� ������ ����� ������ � �.�.). ����� �����
                       // ���������.
        --$this->_transactionCount;
        if($force) {
            $this->_transactionCount = 0;
        }
        if($this->_transaction && ($this->_transactionCount <= 0 || $debug)) {
            //������ ��������� ������ ��� ��������� ����������
            if($this->_transactionCount) {
                $err = 'bad count: ' . $this->_transactionCount;
            }
            if ($res = (bool)$this->squery("COMMIT")) {
                $this->_transaction = NULL;
                $this->_transactionCount = 0;
            } else {
                $err = pg_last_error($this->_transaction);
            }
        } else {
            $err = 'bad count: ' . $this->_transactionCount;
        }
        if($err) {
            $this->err("Transaction error on COMMIT: {$err}", NULL, 1);
        }
        return $res;
    }


    /**
     * ����� ����������.
     * @param boolean $force   ����� ����� ����������, ���� ���� ���������� ������� ���������� (� ����� ������� ���������)
     * @return resource   ������ ������������ ������� ��� FALSE � ������ �������
     */
    function rollback($force = false) {
        $res = true;
        $err = false;
        $debug = true; // ���� �������, ������� �� ����� ��������, ����� ������ ���������� ������ � ���. ���� �������� �
                       // ������� ������ ���������� (���� �����, �� ��� �������, ���� ������ ����� ������ � �.�.). ����� �����
                       // ���������.
        --$this->_transactionCount;
        if($force) {
            $this->_transactionCount = 0;
        }
        if($this->_transaction && ($this->_transactionCount <= 0 || $debug)) {
            if($this->_transactionCount) {
                $err = 'bad count: ' . $this->_transactionCount;
            }
            if ($res = (bool)$this->squery("ROLLBACK")) {
                $this->_transaction = NULL;
                $this->_transactionCount = 0;
            } else {
                $err = pg_last_error($this->_transaction);
            }
        } else {
            $err = 'bad count: ' . $this->_transactionCount;
        }
        if($err) {
            $this->err("Transaction error on ROLLBACK: {$err}", NULL, 1);
        }
        return $res;
    }


	/**
	 * ����������� � ����� ���������� ���������� ��������.
	 * ������ ��� ������������ �������.
	 *
	 * @return DB  ��� ������ ��� ����������� ������������� � �������
	 */
	public function hold() {
		$this->mode = -1;
		return $this;
	}


	/**
	 * ������� ������ ���������� �������� + ����� ���� ������ � ������ ����������� � �������
	 *
	 * @return DB  ��� ������ ��� ����������� ������������� � �������
	 */
	public function clear() {
		$this->sqls = '';
        $this->keys = array();
	}


	/**
	 * ����������� � ����� ������ ������ ������� ������ ��� ����������.
	 * ������ ��� ������������ �������.
	 *
	 * @return DB  ��� ������ ��� ����������� ������������� � �������
	 */
	public function text() {
		$this->mode = -2;
		return $this;
	}


	/**
	 * ����������� � ����� ���������� �������� � �������������� memcache.
	 * ������ ��� ������������ �������.
	 *
	 * @param  integer $ttl  ����� ����� � �������� ���������� � memcache
	 * @param  string  $grp_key  ��� ������, � ������� ����� �������� ��� �������.
	 * @return DB            ��� ������ ��� ����������� ������������� � �������
	 */
	public function cache($ttl, $grp_key = NULL) {
		$this->mem  = new memBuff;
		$this->ttl  = $ttl;
		$this->mgkey = $grp_key;
		$this->mode = 1;
		return $this;
	}

    
	/**
	 * ����������� � ����� ���������� �������� � �������������� memcached � ��������� � ������.
	 * ������ ��� ������������ �������.
	 *
	 * @param  integer $ttl  ����� ����� � �������� ���������� � memcached
	 * @return DB            ��� ������ ��� ����������� ������������� � �������
	 */
    public function incache($ttl = 0) {
        if ( $this->_connMemcached() ) {
            $this->ttl  = $ttl;
            $this->mode = 2;
        }
        return $this;
    }


	/**
	 * ��������� ���� key/id ��� ������������� � ������ ����������� � �������
	 *
	 * @param  string $key   ��� �����
	 * @param  string $key   ID ������ �����
	 */
    public function bind($key, $id=FALSE) {
        $this->keys[] = self::MPREFIX . 'K_' . $key . ($id !== FALSE? ":{$id}": '');
    }
    
    
	/**
	 * ���������� ���� key/id, ����� �������� ��� ���������� � ���� �������
	 *
	 * @param  string $key   ��� �����
	 * @param  string $key   ID ������ �����
	 */
    public function flush($key, $id=FALSE) {
        if ( $this->_connMemcached() ) {
            $version = (int) (microtime(TRUE) * 100);
            $keys[self::MPREFIX . 'K_' . $key . ($id !== FALSE? ":{$id}": '')] = $version;
            if ( $id !== FALSE ) {
                $keys[self::MPREFIX . 'K_' . $key] = $version;
            }
            $this->memcached->setMulti($keys);
			if ($this->debug) {
				$c = count($this->debugLog);
				$trace = debug_backtrace();
				$this->debugLog[$c]['file']  = $this->debug;
				$this->debugLog[$c]['text'] .= "\n-- [" . date("Y-m-d H:i:s") . "] "; 
                $this->debugLog[$c]['text'] .= "------------------------------------------------------------------\n";
				$this->debugLog[$c]['text'] .= "FILE: {$trace[$this->traceLevel-1]['file']}\n";
                $this->debugLog[$c]['text'] .= "LINE: {$trace[$this->traceLevel-1]['line']}\n";
				$this->debugLog[$c]['text'] .= "KEY FLUSH: " . var_export($keys, TRUE) . "\n";
			}
        }
    }
    
	
	/**
	 * ��������/��������� ����� �����
	 * 
	 * @param  string $file  ���� � ������� ����� ������������ debug.
	 *                       ������ ������ ��������� debug
	 */
	public function debug($file = '') {
		$this->debug = $file;
	}
	
	
	/**
	 * ������������� ������� ���������� �������.
	 * ������� � �������� ����������.
	 * 
	 * @param  integer $timeout   ������� � �������������
	 */
	function setTimeout($timeout = DB::DEFAULT_TIMEOUT) {
	    DB::$timeout = (int)$timeout;
	}
	

	/**
	 * ������������ ������������ ��� ������� ��� ����� �������.
	 * ������ �� ��������� � �� ���������� ������, ������ ���������� ������������ ������.
	 * 
	 * @param  string $text  sql ������ ��� ��� �����
	 * @return string        ������������ ������
	 */
	public function parse($text) {
		$this->pcnt = 1;
		$this->perr = '';
		$this->args = func_get_args();
		$count = count($this->args);
		if ($count) {
			$parsed = preg_replace_callback("/\?(?:lu|au|ai|i|f|u|b|l|a|ts|t|x)?/", array($this, '_parse'), $text, $count-1);
		}
		return $parsed;
	}
	

	/**
	 * ��������������� ������ ���������� �� postgres � ������ php. �������� � ���������
	 * ����� �����������.
	 * 
	 * @param  string $str   ������ � postgres ��������
	 * @return array         php ������
	 */
	public function array_to_php($str) {
		$res = array();
		$link = array(&$res);
		$deep = 0;
		$deepflg = FALSE;
		$tmp = '';
		$flg = FALSE;
		if (!preg_match("/^\s*?(?:\{|\[|ARRAY\[)\s*(.*)(?:\}|\])\s*$/i", $str, $o)) return FALSE;
        $o[1] = rtrim($o[1]);
        if ( $o[1] == '' ) {
            return $res;
        }
        $elements = preg_split("/\s*,\s*/", $o[1]);
		foreach ($elements as $v) {
			while ($v{0} == '{' || $v{0} == '[') {
				$link[$deep][] = array();
				$link[$deep+1] = &$link[$deep][count($link[$deep])-1];
				++$deep;
				$v = ltrim(substr($v, 1));
			}
			$diff = 0;
			while ($deep && ($v{strlen($v)-1} == '}' || $v{strlen($v)-1} == ']')) {
				$v = rtrim(substr($v, 0, strlen($v)-1));
				--$deep;
				++$diff;
			}
			if ($flg || preg_match("/^([\"\'])(.*)$/", $v, $o)) {
				if (preg_match("/([\\\\]*){$o[1]}$/", $v, $out) && (strlen($out[1]) % 2 == 0)) {
					$link[$deep + $diff][] = $flg? ($tmp . ',' . stripslashes(substr($v, 0, strlen($v)-1))): stripslashes(substr($o[2], 0, strlen($o[2])-1));
					$tmp = '';
					$flg = FALSE;
				} else {
					$tmp = $tmp . stripslashes($o[2]);
					$flg = TRUE;
				}
			} else if ($v == 'NULL') {
				$link[$deep + $diff][] = NULL;
			} else if ($v !== '') {
				$link[$deep + $diff][] = $v;
			}
		}
		return $res;
	}
	
    
    
    /**
     * ������ array_to_php ������ �������� ���������
     * �� ����� ����������� ������������ ���
     * https://github.com/DmitryKoterov/db_type
     * 
     * @param type $str
     * @return type
     */
    public function array_to_php2($str) 
    {
        return $this->_pgarr2arr($str);
    }
    
    
    
    /*
     * ������ Postgres �������� � PHP �������
     */
    protected function _pgarr2arr($str, $start=0)
    {
        static $p;
        if ($start==0) $p=0;
        $result = array();
  
        $c = $this->_charAfterSpaces($str, $p);
        if ($c != '{') 
        {
            return;
        }
        $p++;

        while (1) 
        {
            $c = $this->_charAfterSpaces($str, $p);
      
            if ($c == '}') 
            {
                $p++;
                break;
            }
      
            if ($c == ',') 
            {
                $p++;
                continue;
            }
      
            if ($c == '{') 
            {
                $result[] = $this->_pgarr2arr($str, $p);
                continue;
            }
      
            if ($c != '"') 
            {
                $len = strcspn($str, ",}", $p);
                $v = stripcslashes(substr($str, $p, $len));
                if (!strcasecmp($v, "null")) 
                {
                    $result[] = null;
                } 
                else 
                {
                    $result[] = $v;
                }
                $p += $len;
                continue;
            }
      
            $m = null;
            if (preg_match('/" ((?' . '>[^"\\\\]+|\\\\.)*) "/Asx', $str, $m, 0, $p)) 
            {
                $result[] = stripcslashes($m[1]);
                $p += strlen($m[0]);
                continue;
            }
        }
  
        return $result;
    }


    protected function _charAfterSpaces($str, &$p)
    {
        $p += strspn($str, " \t\r\n", $p);
        return substr($str, $p, 1);
    }
    

    
    
    

	/**
	 * ������������� ���������� � �������� memcached � ��������� ������ ����������� � $this->memcached
     * ���� ���������� ����������, �� ��������������� �� ����������.
	 * 
	 * @return boolean    TRUE - ���������� �����������, FALSE - �� �����������
	 */
	protected function _connMemcached() {
        if ( !empty($this->memcached) ) {
            return TRUE;
        }
        if ( class_exists('Memcached') ) {
            $this->memcached = new Memcached;
            if ( count($GLOBALS['memcachedServers']) == 1 ) {
                if ( $this->memcached->addServer($GLOBALS['memcachedServers'][0], 11211) ) {
                    return TRUE;
                }
            } else if ( count($GLOBALS['memcachedServers']) > 1 ) {
                if ( $this->memcached->addServers($GLOBALS['memcachedServers']) ) {
                    return FALSE;
                }
            }
        }
        return FALSE;
    }

    function setCheckAutoSlashes($mode = true) {
        $this->checkAutoSlashes = $mode;
    }
    
    /**
	 * ���������� �����, ������������ ������������.
	 * 
	 * @param  array $p   �������� �������� preg_replace_callback ���������
	 * @return string     ��������� � �������� ������� �������������
	 */
	protected function _parse($p) {
        setlocale(LC_NUMERIC, 'en_US.UTF-8');
		$value = $this->args[$this->pcnt++];
		if ($this->perr) return $p[0];
		if ($value === NULL) return 'NULL';
		
		if ($p[0]=='?' && $this->hasAutoSlashes($value)) {
		    $p[0] = '?u';
		}

		switch ($p[0]) {
			case '?':
				if (is_string($value) || is_numeric($value)) {
					return "'".pg_escape_string((string) $value)."'";
				} else if ($value === TRUE) {
					return 'TRUE';
				} else if ($value === FALSE) {
					return 'FALSE';
				} else {
					$this->perr = 'Placeholder '.($this->pcnt-1).' should be string, numeric or boolean.';
					return $p[0];
				}
				break;
            case '?x':
                if (is_string($value) || is_numeric($value)) {
                    return (string)$value;
                } else if ($value === TRUE) {
                    return 'TRUE';
                } else if ($value === FALSE) {
                    return 'FALSE';
                }
                break;
            case '?t':
                if (is_string($value) || is_numeric($value)) {
					return "'".pg_escape_string((string) $value)."'::text";
                } else {
					$this->perr = 'Placeholder '.($this->pcnt-1).' should be string or numeric';
					return $p[0];
				}
				break;
            case '?ts':
                if (is_string($value) || is_numeric($value)) {
					return "'".DB::inputto_tsquery($value)."'";
                } else {
					$this->perr = 'Placeholder '.($this->pcnt-1).' should be string or numeric';
					return $p[0];
				}
				break;
			case '?u':
				if (is_string($value) || is_numeric($value)) {
					return "'".pg_escape_string(stripslashes((string) $value))."'";
				} else if ($value === TRUE) {
					return 'TRUE';
				} else if ($value === FALSE) {
					return 'FALSE';
				} else {
					$this->perr = 'Placeholder '.($this->pcnt-1).' should be string, numeric or boolean.';
					return $p[0];
				}
				break;
			case '?b':
				if ($value) {
                    return $value === 'f' ? 'FALSE' : 'TRUE';
				} else {
					return 'FALSE';
				}
				break;
			case '?l':
				if (is_array($value)) {
					$vals = '';
					foreach ($value as $val) {
						if ($val === NULL) {
							$vals .= 'NULL,';
						} else if (is_string($val) || is_numeric($val)) {
							$vals .= "'".pg_escape_string((string) $val)."',";
						} else if ($val === TRUE) {
							$vals .= 'TRUE,';
						} else if ($val === FALSE) {
							$vals .= 'FALSE,';
						} else {
							$this->perr = 'All elements in placeholder '.($this->pcnt-1).' should be strings, numerics or booleans.';
							return $p[0];
						}
					}
					if ($vals) {
						return substr($vals, 0, strlen($vals)-1);
					}
				} else {
					$this->perr = 'Placeholder '.($this->pcnt-1).' should be array.';
					return $p[0];
				}
				break;
			case '?lu':
				if (is_array($value)) {
					$vals = '';
					foreach ($value as $val) {
						if ($val === NULL) {
							$vals .= 'NULL,';
						} else if (is_string($val) || is_numeric($val)) {
							$vals .= "'".pg_escape_string(stripslashes((string) $val))."',";
						} else if ($val === TRUE) {
							$vals .= 'TRUE,';
						} else if ($val === FALSE) {
							$vals .= 'FALSE,';
						} else {
							$this->perr = 'All elements in placeholder '.($this->pcnt-1).' should be strings, numerics or booleans.';
							return $p[0];
						}
					}
					if ($vals) {
						return substr($vals, 0, strlen($vals)-1);
					}
				} else {
					$this->perr = 'Placeholder '.($this->pcnt-1).' should be array.';
					return $p[0];
				}
				break;
			case '?ai':
				$suffix = '::integer[]';
			case '?a':
				if (is_array($value)) {
					$vals = '';
					$deep = 0;
					$idx = array(0);
					$link = array(&$value);
					$count = array(count($link[0]));
					reset($value);
					$val = current($link[0]);
					while (TRUE) {
						if ($idx[$deep] >= $count[$deep]) {
							$vals = substr($vals, 0, strlen($vals)-1).'},';
							if (!$deep) break;
							$val = current($link[--$deep]);
							$idx[$deep]++;
							continue;
						}
						if (is_array($val)) {
							$k = each($link[$deep]);
							$link[$deep+1] = &$link[$deep][$k['key']];
							$idx[++$deep]  = 0;
							$count[$deep]  = count($link[$deep]);
							$val = current($link[$deep]);
							$vals .= '{';
							if ($val === FALSE) continue;
						} 
						if ($val === NULL) {
							$vals .= 'NULL,';
						} else if (is_string($val) || is_numeric($val)) {
                            $vals .= '"'.str_replace(array('\\', '"', '\''), array('\\\\\\\\', '\\\\"', '\'\''), (string) $val).'",';
						} else if ($val === TRUE) {
							$vals .= 'TRUE,';
						} else if ($val === FALSE) {
							$vals .= 'FALSE,';
						} else {
							$this->perr = 'All elements in placeholder '.($this->pcnt-1).' should be strings, numerics, booleans or arrays.';
							return $p[0];
						}
						$idx[$deep]++;
						$val = next($link[$deep]);
					}
					if ($vals) {
						$vals = substr($vals, 0, strlen($vals)-1);
					}
					return "'{".$vals."'" . (isset($suffix)? $suffix: '');
				} else {
					$this->perr = 'Placeholder '.($this->pcnt-1).' should be array.';
					return $p[0];
				}
				break;
			case '?au':
				if (is_array($value)) {
					$vals = '';
					$deep = 0;
					$idx = array(0);
					$link = array(&$value);
					$count = array(count($link[0]));
					reset($value);
					$val = current($link[0]);
					while (TRUE) {
						if ($idx[$deep] >= $count[$deep]) {
							$vals = substr($vals, 0, strlen($vals)-1).'},';
							if (!$deep) break;
							$val = current($link[--$deep]);
							$idx[$deep]++;
							continue;
						}
						if (is_array($val)) {
							$k = each($link[$deep]);
							$link[$deep+1] = &$link[$deep][$k['key']];
							$idx[++$deep]  = 0;
							$count[$deep]  = count($link[$deep]);
							$val = current($link[$deep]);
							$vals .= '{';
							if ($val === FALSE) continue;
						} 
						if ($val === NULL) {
							$vals .= 'NULL,';
						} else if (is_string($val) || is_numeric($val)) {
                            $vals .= '"'.str_replace(array('\\', '"', '\''), array('\\\\\\\\', '\\\\"', '\'\''), stripslashes((string) $val)).'",';
						} else if ($val === TRUE) {
							$vals .= 'TRUE,';
						} else if ($val === FALSE) {
							$vals .= 'FALSE,';
						} else {
							$this->perr = 'All elements in placeholder '.($this->pcnt-1).' should be strings, numerics, booleans or arrays.';
							return $p[0];
						}
						$idx[$deep]++;
						$val = next($link[$deep]);
					}
					if ($vals) {
						$vals = substr($vals, 0, strlen($vals)-1);
					}
					return "'{".$vals."'";
				} else {
					$this->perr = 'Placeholder '.($this->pcnt-1).' should be array.';
					return $p[0];
				}
				break;
			case '?i':
				if (preg_match('/^[-0-9]+$/', (string) $value)) {
					return $value;
				} else {
					$this->perr = 'Placeholder '.($this->pcnt-1).' should be integer.';
					return $p[0];
				}
				break;
			case '?f':
				if (is_numeric($value)) {
					return $value;
				} else {
					$this->perr = 'Placeholder '.($this->pcnt-1).' should be float.';
					return $p[0];
				}
				break;
		}
	}
	
	
	/**
	 * �������� ���� �� ��������� ��������� (������� 0010311).
	 * ������� ��������, ����� ����������������, �� 100% ��� ����� �� ������.
	 * ����� ����� �������� magic_quotes_gpc.
	 *
	 * @param string $str   �������� ������.
	 * @return boolean
	 */
    function hasAutoSlashes($str) {
        if($this->checkAutoSlashes) {
            if (strpos($str, '\\') !== false) {
                return ( preg_match('/(\\\+)(?:[\\\\\'"]|&quot|&#039)/', $str, $m)
                         && (strlen($m[1]) % 2 == 1)               // ���� �� ������, �� ������ ���� ���� ���������������� �������.
                         && !preg_match('/(?<!\\\)[\'"\0]/', $str) // ���� ���� ���� ���������������� �������, �� �������, ��� �� ���� ����������.
                       ); 
            }
        }
        return false;
    }
    
    
	/**
     * ������������� ����� ������������� Standby-������� (�������������� ������ � ��������� � N(1..) ������).
     * @example
     * DB::setStandby('master', array(DB::STBY_OPTS_ANY_MASK=>DB::STBY_OFF)) -- ������ ������������ �� ��� ����� ��������.
     * DB::setStandby('master', array(DB::STBY_OPTS_ANY_MASK=>DB::STBY_ANY)) -- ������������ ��� ����� ��������.
     * DB::setStandby('master', array(DB::STBY_OPTS_ANY_MASK=>DB::STBY_ANY, DB::STBY_OPTS_ALL_MASK=>DB::STBY_CACHED)) -- ������������ ������ ����� ������ ����� ������������.
     * DB::setStandby('master', array(DB::STBY_OPTS_ANY_MASK=>DB::STBY_CACHED)) -- �� ��, ��� ����������.
     * DB::setStandby('master', array(DB::STBY_OPTS_ANY_MASK=>DB::STBY_NOAUTH|DB::STBY_CACHED,
     *                    DB::STBY_OPTS_ALL_MASK=>DB::STBY_NOACT, DB::STBY_OPTS_NOACT_LAG => 10)) -- ��������� �������� (��������, �.�. �� ���. ������) ����� ������ ���� ����
     *                                                                          -- �� ����� 10 ���. �����, ��� ����, �� ������ ���� ���� �����������, ����
     *                                                                          -- ������ ������ ������������ �����������.
     * @see DB::checkStandby()
     *
     * @param string $alias   ����� �������� � ��, ������� ����� �������.
     * @param array $opts   ��������� ������� (��������, DB::STBY_OPTS_NOACT_LAG).
     *
     * @return array   ��������� ����������� ������.
	 */
    static function setStandby($alias, $opts = array()) {
        $old_opts = DB::$_standbyOpts[$alias];
        DB::$_standbyOpts[$alias] = $opts;
        return $old_opts;
    }

    /**
     * ������ �������� �� �� �������� �� ������ �� ��������� ������ (read-only) ��� �������������� ������, ���
     * �������, ����� �� ������������ Standby.
     * ������ ������ ��������� ��� �� ���������.
     *
     * @return boolean   true, ���� read-only
     */
    function checkSqlReadonly() {
        return !preg_match(
            '/[\s;](?:(?:INSERT|UPDATE|DELETE|INTO|TRUNCATE|CREATE|DROP|FOR\s+SHARE|ALTER|COPY\s+FROM|COMMENT)[\s;]|(?:nextval|setval|pgq.insert_event)[(])/i',
            ';'.$this->origSql.';');
    }

    /**
     * ������� ��������� ��� ��������� � ���������, � �������, ����� �� ������������ Standby ��� ���������� �������� �������.
     *
     * @param string $alias   ����� �������� � ��.
     * @return boolean   �����?
     */
    function checkStandby($alias = NULL) {
        $alias = $alias ? $alias : $this->alias;
        if(isset(DB::$_standbyOpts[$alias])) { // !!! && !$this->_transaction
            $opts = DB::$_standbyOpts[$alias];
            $any_mask = (int)$opts[DB::STBY_OPTS_ANY_MASK];
            $all_mask = (int)$opts[DB::STBY_OPTS_ALL_MASK];
            $capacity = (isset($opts[DB::STBY_OPTS_CAPACITY]) ? $opts[DB::STBY_OPTS_CAPACITY] : 1);
            $mask = 0
                  | DB::STBY_CACHED  * ($this->mode == 1)
                  | DB::STBY_NOACT   * (!defined('IS_USER_ACTION') && isset($_SESSION['last_user_action']) + max($opts[DB::STBY_OPTS_NOACT_LAG], 0) <= $_SERVER['REQUEST_TIME'])
                  | DB::STBY_NOAUTH  * (!isset($_SESSION['uid']))
                  | DB::STBY_NOSHELL * (!isset($_SERVER['SHELL']) || isset($_SERVER['REQUEST_URI']))
                  ;

            if($opts[DB::STBY_OPTS_LOGSTAT]) {
                $key = date('Y-m-d').'='.$mask.'='.$any_mask.','.$all_mask.','.$capacity.','.(int)$opts[DB::STBY_OPTS_NOACT_LAG];
                DB::$_stby_log[$key] = (array)DB::$_stby_log[$key];
                $this->_stby_log_row = &DB::$_stby_log[$key];
            }

            if($this->_sqlType != DB::SQLTYPE_MOD && !$this->_transaction) { // !!! ��� ������� ����� ���������� � ������ �������.
                if($capacity >= rand(1, 1000)/1000 && ($any_mask & $mask) != 0 && ($all_mask & $mask) == $all_mask) {
                    return $this->checkSqlReadonly();
                }
            }
        }
        
        return false;
    }

    static function londiste($type, $master_alias = 'master') {
        return londiste::instance($type, $master_alias);
    }
    
    /**
     * ����������� �������� ���� � �������� ������ ��� ������������� � to_tsquery()
     * (��. http://www.postgresql.org/docs/9.1/static/datatype-textsearch.html)
     *
     * @param string $str  �������� ������
     * @param string $as   �������� ��������� &
     * @param string $os   �������� ��������� |
     * @param string $ns   �������� ��������� !
     * @param string $pg_escape   ��������� pg_escape_string()?
     * @return string
     */
    static function inputto_tsquery($str, $as = '\\s', $os = ',', $ns = '', $pg_escape = true) {
        $os .= '|)(:*\''; // �� ��� ����. ������� �����������, ������ �������� �� �� |.
        $as .= '&';
        $ns .= '!';
        
        $str = preg_replace('/(\s*[' . $ns . '][\s' . $os.$as . ']*)+/', ' !', $str);             // ����� ! ����� ���� | ��� &
        $str = preg_replace('/([\s' . $as.$ns . ']*[' . $os . '][\s' . $as . ']*)+/', '|', $str); // ����� | ����� ���� !
        $str = preg_replace('/([\s' . $os.$ns . ']*[' . $as . '][\s' . $os . ']*)+/', '&', $str); // ����� & ����� ���� !
        
        $str = preg_replace('/^[\s' . $os.$as . ']+/', '', $str);
        $str = preg_replace('/[\s' . $os.$as . ']+$/', '', $str);
        
        if ($pg_escape) {
            $str = pg_escape_string($str);
        }
        return $str;
    }
	
}
