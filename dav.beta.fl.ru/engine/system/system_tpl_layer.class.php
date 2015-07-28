<?php
/**
 * ����� ��� ��������� � ���������� ��������
 */
class system_tpl_layer {
	/**
	 * ����� ��������
	 * @var string
	 */
	private $templates_dir = "";
	/**
	 * ����� ��� ����������������� ������
	 * @var string
	 */  
	private $cache_dir = "";
	/**
	 * ������������� ������������� ������ ���
	 * @var bool
	 */  
	private $force_compile = 0; 
    private $template_data = false;
	/**
	 * ������������� ������ - ������
	 * @var integer
	 */   
	private $cache_time = 20; 
	private $ti = false; 
	/**
	 * �����������
	 * @param object $template [optional] ����� �� �������
	 * @return 
	 */
    function __construct($template=false) {
        $this->templates_dir = getcwd() . "/engine/templates/";
        $this->cache_dir = getcwd() . "/temp/complied/"; 
      //  $this->force_compile = 1; 
        if($_REQUEST['tplcompile'] || IS_LOCAL) $this->force_compile = 1;
        if($template) $this->parse_template($template);
        $this->ti = new Tpl_inst();
    }
	/**
	 * ���������� ����� ����� ��������
	 * @return 
	 */
    function getTemplatesDir() {return $this->templates_dir; }
	/**
	 * ���������� ����� ����� ����
	 * @return 
	 */
    function getCacheDir() {return $this->cache_dir; }
	/**
	 * ���������� ���������� �� �������
	 * @param object $name ���
	 * @return ��������
	 */
	function &get($name) {
		return $this->ti->{$name};
	}
	/**
	 * ������������� ���������� � ���������� ������ ����� ������
	 * @param object $arr ������ ����������
	 * @return 
	 */
    function sets($arr) {
        if(!is_array($arr)) return false;
	    foreach($arr as $k=>$v) {
            $this->set($k, $v);                                
        }
    }
    function gets() {
        return get_object_vars($this->ti);
    }
	/**
	 * ������������� ���������� � �������
	 * @param object $name ���
	 * @param object $val ��������
	 * @return 
	 */
    function set($name, $val) {
		$this->ti->{$name} = $val;
	}
	/**
	 * ������������� ���������� � �������
	 * @param object $name ���
	 * @param object $val ��������
	 * @return 
	 */
    public function __set($name, $val) {
        $this->set($name, $val);
    }
    /**
	 * ���������� ���������� �� ���������� �������
	 * @param object $name ���
	 * @return ��������
	 */
    public function &__get($name) {
        return $this->get($name);
    }
	/**
	 * ������� ���������� �� ���������� �������
	 * @param object $name
	 * @return 
	 */
	function delete($name) {
		unset($this->ti->{$name});
	}
    private function postfilter() {
        
    }
	/**
	 * ���������� ������� ������ �������
	 * @param object $template ���� � �������
	 * @return 
	 */
	private function parse_template($template) {
        if(!($file = $this->get_cache_template($template)) || $this->force_compile) {
            $file = $this->get_template($template);
            $this->set_cache_template($template,$file);
        }
        $this->template_data = true;
	}
	/**
	 * ���������� ���� ��������� ����������
	 * @param object $matches
	 * @return 
	 */
    function replace_callback($matches) {
        $matches[0] = preg_replace('#(?<!\\\)((?:\\\{2})*)\\$\\$#', "\\1\$this->", $matches[0]); 
        $matches[0] = preg_replace('#(?<!\\\)((?:\\\{2})*)\\%\\%#', "\\1\$this->misc()->", $matches[0]); 
        return $matches[0]; 
    }
	/**
	 * ������ �������
	 * @param object $file ����
	 * @return 
	 */
    private function get_template($file) {
        if(!file_exists($this->templates_dir.$file)) {
            user_error("Template $file - not exist.", E_USER_WARNING); 
            return '';
        }
        $file = file_get_contents($this->templates_dir.$file);
        $file = preg_replace_callback("#<\?.*?\?>#is", array('self', "replace_callback"), $file); 
        $file = preg_replace_callback('/\{\{include "([^{}]*)\.tpl"\}\}/i', array('self', 'include_callback'), $file);
        return $file;
    }
    /**
     * ��������� ��������������� ������� � ���
     * @param object $file ����
     * @param object $data ������
     * @return 
     */
    private function set_cache_template($file,$data) {        
        return file_put_contents($this->get_path_cache_template($file), $data);
    }
    /**
     * ��������� ���� ������� �� ����
     * @param object $file ����
     * @return ���� �� ��� �����
     */
    private function get_path_cache_template($file) {
        $file = str_replace("/", "__", $file);
        return $this->cache_dir.'%tpl_complied_'.$file.'%.tmp';
    }
	/**
	 * ���������� ������ ������� �� ����
	 * @param object $template ����
	 * @return 
	 */
    private function get_cache_template($template) {
        $path = $this->get_path_cache_template($template);
		if(!file_exists($path)) {
            return false;
        }
       // echo vardump(time() - filemtime($path));
        if(time() - filemtime($path) > $this->cache_time) {
            return false;
        }
        return file_get_contents($path);
    }
    /**
     * 
     * @param object $matches
     * @return 
     */
    private function this_callback($matches) {
        if(@$matches[1]) $file = "";
        return $file;
    }
    
	/**
	 * ������� ��������� ������.
	 * @param object $matches
	 * @return 
	 */
    private function include_callback($matches) {
        if(@$matches[1]) $file = $this->get_template($matches[1].".tpl");
        return $file;
    }
	/**
	 * ����� ������� �� �����
	 * @param object $template
	 * @return 
	 */
	function clear($template) {
		echo $this->fetch($template);
	}
	
	/**
	 * ����� ������� �� �����, ������� ����������
	 * @param string $template ���� �� �������
	 * @return 
	 */
	function display($template) {
		if(!$this->template_data) $this->parse_template($template);
        $this->ti->display($this->get_path_cache_template($template));
        unset($this->ti);
	}
	
	/**
	 * ����� ������� � ����������
	 * @param string $template ���� �� �������
	 * @return 
	 */
	function fetch($template) {
		if(!$this->template_data) $this->parse_template($template);
		$f = $this->ti->fetch($this->get_path_cache_template($template));
		//unset($this->ti);
		return $f;
	}
}
/**
 * ����� �������
 */
class Tpl_inst {	
    /**
     * ���������� ��������� �������
     * @var
     */
	private $misc_class = false; 
	/**
	 * ���������� ������ �� ������
	 * @param object $file ��� �������
	 * @return 
	 */
	function display($file) {
		include($file);
	}
	/**
	 * ��������� ������ � ����������
	 * @param object $file ��� �����
	 * @return ����������� ������
	 */
	function fetch($file) {
		ob_start();
		$this->display($file);
		$data = ob_get_contents();
		ob_clean();
		return $data;
	}
	/**
	 * ������ � ���������� ����� ��������� �������
	 * @return �����
	 */
    public function misc() {
        if(!$this->misc_class) {
            $this->misc_class = new system_tpl_helper();
        }
        return $this->misc_class;
    }
}

?>
