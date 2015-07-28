<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����������� ������������ ���������� ����� MemCache
 *
 */
class memBuff extends Memcache
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
	 * �����������. ������������ � ������� �������
	 */
	function __construct() {
		if (sizeof($GLOBALS['memcachedServers']) == 1){
		    $this->bIsConnected = $this->connect($GLOBALS['memcachedServers'][0], 11211);
	    } elseif (sizeof($GLOBALS['memcachedServers']) > 1) {
	        foreach ($GLOBALS['memcachedServers'] as $server){
	            $this->bIsConnected = $this->addServer($server);
	        }
	    }
	    else die("�� ������� ������� Memcache");
	    $this->server = (defined('SERVER')?SERVER:'');
        $this->setOption(Memcached::OPT_COMPRESSION, false);
	}
	
	/**
	 * ����������. ����������� �� ������� �������
	 */
	function __destruct() {
		if ($this->bIsConnected) $this->close();
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
	 * @param string $key			���� ��� ������
	 * @return array			���������. false, ���� �� �������
	 */
	function get($key){
		if ($this->bIsConnected)
			$output = parent::get($key.$this->server);
		return $output;
	}
	
	/**
	 * ����������� ������ �� �������. ���� �� ������� ����, �� �������� ��������� ������� �
	 * ������� pg_fetch_all()
	 *
	 * @param string $error			���������� ��������� �� ������ ��� ������� � ���������
	 * @param string $sql			������ � ���������
	 * @param integer $expire		����� ����� ���� (� ��������)
	 * @param boolean $read_only	������ ������ �� ������?
	 * @param mixed $group			��� ������, false ���� ��� ������
	 * @return array			��������� ������� �� ���� ��� ���� � ������� ������� pg_fetch_all()
	 */
	function getSql(&$error, $sql, $expire = 600, $read_only = false, $group = false){
		$output = $this->get(md5($sql));
		//print "Buffer";
		if (!$output){
			//print "NoBuffer!";
			$res = pg_query_Ex($sql, $read_only);
			$output = pg_fetch_all($res);
			$this->bWasMqUsed = false;
			$error = pg_errormessage();
			if (!$error){
				$this->set(md5($sql),$output, $expire, $group);
			}
		}
		return $output;
	}
	
	/**
	 * ������ ������ � ������.
	 *
	 * @param string $key			���� ��� �����������
	 * @param string $data			������
	 * @param integer $data			����� ����� ������
	 * @param string $data			������ ��� ������ (false - �� ��������������)
	 * @return boolean				true - ���� ��� ��			
	 */
	function set($key, $data, $expire = 600, $group = false){
//print "Buffer_SET!";
		if ($this->bIsConnected)
			$output = parent::set($key.$this->server,$data, false, $expire);
		if ($group){
			$items = $this->get($group);
			$items[] = $key;
			parent::set($group.$this->server,$items, false, 0);
		}
		return $output;
	}
	
	/**
	 * ������� ������ �� ���� �� �� ����
	 *
	 * @param string $key	���� ������
	 * @return boolean				true - ���� ��� ��	
	 */
	function delete($key) {
		if ($this->bIsConnected)
			$output = parent::delete($key.$this->server);
		return $output;
	}
	
	/**
	 * ������� ������ ������� �� �������
	 *
	 * @param string $group		������ �������
	 * @return boolean			true - ���� ��� ��	
	 */
	function flushGroup($group){
		$items = $this->get($group);
		if ($items)
			foreach ($items as $item){
				if ($this->get($item)!==false)	$this->delete($item);
			}
		 parent::set($group.$this->server, false, 0);
	}
	
	function touchTag() {}
	
	/**
	 * �������� ���� ���
	 *
	 */
	function flush(){
		parent::flush();
	}

}
