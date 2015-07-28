<?php
/**
 * �������� ����� ��� ���������������� ������
 */
final class front
{
    static private $_object = array();
    /**
     * ���������� ��� �������� ������ REQUEST � ������� ����� ����������� ����������
     * @var
     */
	static public $_req = array();
    static $d = false;    
    static $map = false;    
    private function __construct(){}
    
	/**
	 * ����� � ����� �������� ������
	 * @param string $str [optional] ������ ������ ������
	 * @return 
	 */
    public function error($str='') {
        self::exec_page(array("class"=>"error404", "after_uri"=>array($str)));   
        die();
    }
    
	/**
	 * ��������� ����� �������� ������ - ���������
	 * @param object $map �����
	 * @return 
	 */
    public function setMap($map) {
        self::$map = $map;
    }
    
	/**
	 * �������������� � ���8
	 * @param string $in ������ win1251
	 * @return ������
	 */
    public function toUtf($in) {
        if(is_array($in)) {
            foreach($in as &$i) {
                $i = self::toUtf($i);
            }
        } else {
            $in = iconv("windows-1251",'utf-8',  $in);
        }
        return $in;
    }
	/**
	 * ����������� � win1251
	 * @param string $in ������ ���8
	 * @return ������
	 */
    public function toWin($in) {
        if(is_array($in)) {
            foreach($in as &$i) {
                $i = self::toWin($i);
            }
        } else {
            $in = iconv('utf-8', "windows-1251",  $in);
        }
        return $in;
    }
	/**
	 * ������� ��� ������ � ��������� �� ���� ������ �� ����������� ������� 
	 * @param array $arr
	 * @param object $key_name [optional] ��� ���������� ��� �����, ���� �� ����� �������� �� 0 ...
	 * @param object $val_name [optional] ��� ���������� ��� ��������
	 * @return �������������� ������
	 */
    public function get_hash($arr, $key_name=false, $val_name='id') {
        if (!is_array($arr)) return array();
        $ret = array();
        $i=0;
        foreach ($arr as $item) {
            if($key_name<>false) {
            $ret[$item[$key_name]] = $item[$val_name]; } else {
                $ret[$i] = $item[$val_name];
                $i++;
            }
        }
        return $ret;
    }
	/**
	 * ��������� ������� ������ �-�� � ������ ������
	 * @param object $class �������� ������
	 * @param object $die [optional] ������� � ���������� ������
	 * @return 
	 */
    public function exec_page($class, $die=false) {
		//var_export($class);
        $class2 = "page_".$class['class'];
        self::$d = new $class2();
        self::$d->page = $class['class'];
        if(front::og("tpl")) front::og("tpl")->set("pageClass", self::$d->page);
		if(!$class['method']) {
			$afteruri_wothout_action = $class['after_uri'];
			$action = array_shift($afteruri_wothout_action);
		} else {
			$action = $class['method'];
		}
        if(!$action) {            
            $afteruri_wothout_action = $class['after_uri'];
            $action = "index";
        }

        $method = strtolower($action) . "Action";
        if(method_exists(self::$d, $method)) {
            self::$d->uri = $afteruri_wothout_action;
            self::$d->action = $method;
            //die(self::$d->action);
            if(front::og("tpl")) front::og("tpl")->set("action", self::$d->action);
            self::$d->$method();    
        } elseif((method_exists(self::$d, "indexAction") && $method == "")) {
            self::$d->uri = $class['after_uri'];
            self::$d->action = "indexAction";
            if(front::og("tpl")) front::og("tpl")->set("action", self::$d->action);
            self::$d->indexAction();     
        } else {
            self::error();
        }
       
        if($die) die();
    }
    
	/**
	 * ���������� ������ ������ ����� ����� ��������
	 * @param string $uri_ ������ ������� ������
	 * @return 
	 */
    public function exec_uri($uri_) {
        $map = self::$map;
        $uri_input = $uri_;
        $uri_ = explode("?", $uri_);
        $uri_ = $uri_[0];
        $uri = explode("/", $uri_);
        
        $doc_root = getcwd();
        
        if($uri_ == '/') {
            if(isset($map["index"])) {
                self::exec_page(array("class"=>$map["index"]["class"], "after_uri"=>$uri)); 
            }
            else if(file_exists($doc_root . DIR_SEP . 'index.php')) {
                return;
            }
            else {
                self::error();
            }
            return 0;   
        }
        
        array_shift($uri);
        $end_slash = array_pop($uri);
        if($end_slash !== '') {
            header_location_exit($uri_input . '/');
            exit();
            //self::error();
        }

        $i = 0;
        
        $class = array();
        if(empty($class["class"]))
        while(sizeof($uri) && $i<6) {
            $i++;
            $dat = array_shift($uri);
            
            if($dat == "adminback") {
                if(!hasPermissions('adm')) {
                    self::error("��� ����");    
                }
            }
            
            //vardump();
            $error = true;
            
            if(isset($map[$dat])) {
                $map = $map[$dat]; 
                $error = false;
            } else {
                break;
            }
            
            if(isset($map[(array_shift($temp_sub = $uri))])) {
                continue;
            }
            if(isset($map["class"])) {
					$class=array("class"=>$map["class"], "method"=>$map["method"], "after_uri"=>$uri);
                break;       
            }
        }
        
       // vardump($class);

        if(!isset($class["class"])) {
            self::error();
        } else {
            self::exec_page($class);
//            exit();
        }
    }
	
	/**
	 * ���������, ���� �� ����� � ����������� ����������� ��������
	 * @param string $name ����� ������
	 * @return 
	 */
    static public function oc($name) {
        return isset(self::$_object[$name]);
    }
    
	/**
	 * ���������� ����� �� ������������ ����������� ��������, ���� ����������
	 * @param string $name ����� ������
	 * @return 
	 */
    static public function og($name) {
        if (!is_string($name) || !array_key_exists($name, self::$_object)) {
            return false;
        }
        return self::$_object[$name];
    }
    /**
     * ��������� ����� � ����������� ����������� ��������
     * @param string $name ����� ������
     * @param object $obj �����
     * @return 
     */
    static public function os($name, &$obj) {
        if (!is_string($name) || array_key_exists($name, self::$_object) || !is_object($obj)) {
            return false;
        }
        
        self::$_object[$name] = $obj;
        return true;
    }
    /**
     * ���� ������ �������
     * @param string $class ��� ������
     * @return 
     */
    static public function load_class($class) {
        if(class_exists($class)){
            return 1;
        }
        $class = strtolower($class);    
        if(($v5_substr = substr($class, 0,5)) && $v5_substr === 'page_' && file_exists(ROOT_DIR.'engine/page/'.$class.'.class.php')) {
            require_once(ROOT_DIR.'engine/page/'.$class.'.class.php');   
            if(class_exists($class)) { return true; }       
        } elseif(($v7_substr = substr($class, 0,7)) && $v7_substr === 'system_' && file_exists(ROOT_DIR.'engine/system/'.$class.'.class.php')) {
            require_once(ROOT_DIR.'engine/system/'.$class.'.class.php');   
            if(class_exists($class)) { return true; }    
        } elseif(file_exists(ROOT_DIR.'engine/'.$class.'.class.php')) {
            require_once(ROOT_DIR.'engine/'.$class.'.class.php');   
            if(class_exists($class)) { return true; }    
        } elseif(file_exists(ROOT_DIR.'engine/classes/'.$class.'.php')) {
            require_once(ROOT_DIR.'engine/classes/'.$class.'.php');   
            if(class_exists($class)) { return true; }    
        } elseif(file_exists(ROOT_DIR.'classes/'.$class.'.php')) {
            require_once(ROOT_DIR.'classes/'.$class.'.php');   
            if(class_exists($class)) { return true; }    
        }
        return false;
    }
    public function __destruct(){}
}
?>
