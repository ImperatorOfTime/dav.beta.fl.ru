<?php

/**
 * ����� ��� ������ � ������ ���������
 * 
 */
class DigestBlock {
    
    
    /**
     * ���� � ��������
     */
    const TEMPLATE_PATH = '/siteadmin/mailer/digest';
    
    /**
     * ����������� ��������� �������������� ����
     * 
     * @var boolean 
     */
    const IS_CREATED = false;
    
    /**
     * ����������� ��������� �������������� ����
     * 
     * @var boolean 
     */
    const ADD_FIELD = false;
    
    /**
     * ����� ����� (���������� ��� ������������� ���� �� ���������)
     * 
     * @var integer
     */
    protected $num = 0;
    
    /**
     * ������� �����
     * 
     * @var integer 
     */
    protected $_position = 0;
    
    /**
     * ������ ���� ��� ����������� ��� ���
     * 
     * @var boolean
     */
    protected $_check    = false;
    
    /**
     * ������� ���� ��� ���
     * 
     * @var boolean 
     */
    protected $is_main   = true;
    
    /**
     * ������ ��� ������ ������ � ����
     * 
     * @var mixed 
     */
    protected $_error    = false;
    
    /**
     * ������ ��� ����������� ����� � HTML
     * 
     * @var mixed
     */
    public $html_data = false;
    
    /**
     * ��������� �����
     * 
     * @var string
     */
    public $title = "����";
    
    /**
     * ������������� ������ �����
     */
    public function initBlock() {}
    
    /**
     * ����������� �����
     */
    public function displayBlock() {}
    
    /**
     * ������������� �����
     */
    public function initialize() {}
    
    /**
     * �������� ������
     * 
     * @return string
     */
    public function __toString() {
        return get_class($this);
    }
    
    /**
     * ������ ����� �����
     * 
     * @param integer $num
     */
    public function setNum($num) {
        $this->num = $num;
    }
    
    /**
     * ���������� ����� �����
     * 
     * @return integer
     */
    public function getNum() {
        return $this->num;
    }
    
    /**
     * ��������� ������� �����
     */
    public function setUpPosition() {
        $this->_position++;
    }
    
    /**
     * ��������� ������� �����
     */
    public function setDownPosition() {
        if($this->_position > 0) {
            $this->_position--;
        }
    }
    
    /**
     * ������ ������� �����
     * 
     * @param integer $pos
     */
    public function setPosition($pos) {
        $this->_position = $pos;
    }
    
    /**
     * ������� ������� �����
     * @return type
     */
    public function getPosition() {
        return $this->_position;
    }
    
    /**
     * ���� �� ����������� ��������� ����� �����
     * 
     * @return boolean
     */
    public function isCreated() {
        return constant(get_class($this) . '::IS_CREATED');
        //return $this::IS_CREATED; // ������� � ������ 5.3.0
    }
    
    /**
     * ������ ����������� ����� � HTML
     * 
     * @param boolean $bool
     */
    public function setCheck($bool) {
        $this->_check = $bool;
    }
    
    /**
     * ���������� ��� ��� ���� � HTMl
     * 
     * @return boolean
     */
    public function isCheck() {
        return $this->_check;
    }
    
    /**
     * ���� �� ����������� ��������� ����� ����
     * 
     * @return boolean
     */
    public function isAdditionFields() {
        return constant(get_class($this) . '::ADD_FIELD');
        //return $this::ADD_FIELD; // ������� � ������ 5.3.0
    }
    
    /**
     * ���� �������� ������� ��� ���
     * 
     * @return boolean
     */
    public function isMain() {
        return $this->is_main;
    }
    
    /**
     * ������ �������� �����, ������� ���� ��� ���
     * 
     * @param boolean $bool
     */
    public function setMain($bool) {
        $this->is_main = $bool;
    }
    
    /**
     * ������ ��������� �����
     * 
     * @param string $title
     * @param ...
     */
    public function setTitle($title) {
        if(func_num_args() > 1) {
            $args = func_get_args();
            array_shift($args);
            $this->title = vsprintf($title, $args);
        } else {
            $this->title = $title;
        }
    }
    
    /**
     * ��������� �����
     * 
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }
    
    /**
     * ������ ��� ������������� ����� ��� ���
     * 
     * @return boolean
     */
    public function isError() {
        return ($this->_error != false);
    }
    
    /**
     * �������������� ������ ��� HTML 
     */
    public function initHtmlData() { }
    
    /**
     * ������ HTML ����
     * 
     * @return string
     */
    public function htmlBlock() {
        $this->host = $GLOBALS['host'];
        $this->initHtmlData();
        if(!$this->html_data) return ''; // ������ ��� ����� ���
        include ($_SERVER['DOCUMENT_ROOT'] . self::TEMPLATE_PATH . "/tpl.{$this->__toString()}.php");
    }
    
    public function isWysiwyg() {
        return false;
    }
}