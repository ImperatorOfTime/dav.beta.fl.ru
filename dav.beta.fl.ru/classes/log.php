<?php

if(!defined('LOG_DIR'))
    define('LOG_DIR', '/var/tmp/');
    
/**
 * ����� ��� ������� �����
 */
class log {

    private $_files = array();
    private $_fp = false;
    private $_beginTime;
    private $_logname;
    private $_mode;
    
    /**
     * �������������� ������ ������ 
     * 
     * @see LogSave
     * @var array
     */
    private $_alternative_methods = array(); // �������������� ������ ������ ����
    
    /**
     * ���������/���������� �������������� ������� ������
     * 
     * @var array
     */
    private $_is_use_alternative_methods = false;
    public  $linePrefix = ''; // ����������� � ������ ������ ������, ����� � ������� strftime.
    
    /**
     * ����������� ������
     * 
     * @param string $logname ��� ������������ �����
     * @param string $mode ������ ������� � ����� (��. fopen)
     */
    function __construct($logname, $mode = 'a', $line_prefix = '') {
        if(strpos($logname, '../') !== false) {
            return;
        }
        date_default_timezone_set('Europe/Moscow');
        $this->_logname = $logname;
        $this->_mode = $mode;
        $this->_beginTime = microtime(true);
        $this->linePrefix = $line_prefix;
    }
    
    /**
     * ���������� ������
     */
    function __destruct() {
        if($this->_fp) {
            fclose($this->_fp);
        }
    }
    
    /**
     * ��������� �������������� ������ ������ ������ � ����
     * 
     * @param LogSave $obj  ������ ����������� ������ � ��
     * @param boolean $use  �������� �������������� ������ ��� ���
     */
    function addAlternativeMethodSave($obj, $use = null) {
        $obj->setLogName($this->_logname);
        $this->_alternative_methods[$obj->__toString()] = $obj;
        if($use !== null) $this->setUseAlternativeMethod($use);
    }
    
    /**
     * ��������� ��������������� ������� ������ ������, 
     * ���� �������� false - �������������� ������� �������������� �� �����
     * 
     * @param boolean $use
     */
    function setUseAlternativeMethod($use) {
        $this->_is_use_alternative_methods = $use;
    }
    
    /**
     * ���-������������� ������ ��� ������ ����� �������������� ������
     * 
     * @param string $str             ������ ��� ������
     * @param string $method_alias    �������� ������
     */
    function setAlternativeWrite($str, $method_alias) {
        $this->_alternative_methods[$method_alias]->setStr($str);
    }
    
    /**
     * ���������� ��� ����� ����.
     * @param boolean $root   ������ � ���������� �����.
     * @return string
     */
    function getLogname($root = false) {
        if($this->_logname) {
            return ($root ? LOG_DIR : '') . strftime($this->_logname);
        }
        return NULL;
    }
    
    /**
     * ���������� ����� ����������
     *
     * @param  string $fmt �����������. ������ ������� ��� NULL ����� �������� ����� ���������� � ��������
     * @return mixed
     */
    function getTotalTime($fmt = '%H:%M:%S', $msecs = 0) {
        $diff = microtime(true) - $this->_beginTime;
        $s = floor($diff);
        if($msecs) {
            $ms = '.' . round(pow(10,$msecs)*($diff - $s));
        }
        return $fmt === NULL ? $diff : gmstrftime($fmt, $s).$ms;
    }
    
    /**
     * ��������� ���� ����.
     *
     */
    private function _open() {
        if (!$this->_fp) {
            $logname = $this->getLogname();
            if( $logname && !($this->_fp = @fopen(LOG_DIR.$logname, $this->_mode)) ) {
                $dirs = explode('/', dirname($logname));
                $pth = LOG_DIR;
                foreach($dirs as $d) {
                    $pth .= $d.'/';
                    if(!file_exists($pth))
                        mkdir($pth, 0777);
                }
                $this->_fp = fopen(LOG_DIR.$logname, $this->_mode);
            }
            if($this->_fp && !isset($_SERVER['REQUEST_METHOD'])) {
                chmod(LOG_DIR.$logname, 0666);
            }
        }
        return !!$this->_fp;
    }
    
    /**
     * ���������� ���������� ����� � ���� ������� �����
     * 
     * @param  string $name ���� � �����
     * @return array
     */
    function _getFile($name) {
        if(!$this->_files[$name])
            $this->_files[$name] = file($name);
        return $this->_files[$name];
    }
    
    /**
     * ���������� ������ � ������� ���
     * 
     * @param string $str ������ ��� ������
     */
    function write($str) {
        if($this->_open()) {
            fwrite($this->_fp, $str);
        }
        
        if($this->_is_use_alternative_methods) { // ���� ���������� �������������� ����� ������ ����� �����
            foreach($this->_alternative_methods as $method) {
                if(!$method->isStr()) { // ���� ������ �� ������, ����� �� ��� ����
                    $method->write($str);
                } else{
                    $method->write($method->getStr());
                }
            }
        }
    }
    
    /**
     * ���������� ����������������� ������ � ������� ���
     *
     * @param string $str ������ ��� ������
     */
    function writeln($str = '') {
        $this->write(strftime($this->linePrefix).$str."\n");
    }
    
    /**
     * ���������� �������� ���������� � ���
     *
     * @param string $var ������ ��� ������
     */
    function writevar($var) {
        $this->writeln(var_export($var, true));
    }
    
    /**
     * ���������� � ��� ���������� ���������� ������������ ��������.
     * 
     * @param mixed $res ��������� ������ ���� �������� (������� ��������).
     */
    function trace($res) {
        $tre = '/^\$\w+\s*->\s*trace\s*\((.*?)(?:,[^\)]+)*\)\s*;\s*$/i';
        $bt = current(debug_backtrace());
        $ln = $bt['line'];
        $file = $this->_getFile($bt['file']);
        $fn = trim(preg_replace($tre, '$1',trim($file[$ln-1])));
        $dt = date('d.m.Y H:i:s');
        $ln = str_pad($ln, 4, '0', STR_PAD_LEFT);
        ob_start();
        var_dump($res);
        $res = trim(ob_get_clean());
        $this->writeln("{$dt}, ln: {$ln}, {$fn} = {$res}");
    }
    
    function writedump($param, $title = '') {
        ob_start();
        var_dump($param);
        $out = ob_get_clean();
        if($title != '') {
            $this->write($title."\r\n");
        }
        $this->writeln($out);
    }
} 


