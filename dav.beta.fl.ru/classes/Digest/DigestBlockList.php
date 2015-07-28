<?php

require_once 'DigestBlock.php';

/**
 * ����� ��� ������ � ������ �� ��������
 */
class DigestBlockList extends DigestBlock {
    
    /**
     * �������� �� ������ �������������� ������
     * 
     * @var boolean 
     */
    const AUTO_COMPLETE = false;
    
    /**
     * ����� ��������� � �������� ������
     * 
     */
    const MASK_LINK = '';
    
    /**
     * ���������� ������
     * 
     * @var integer 
     */
    protected $_list_size = 3;
    
    /**
     * ��� �������� ������ ������ ��� �������������� ����������, ��������� ������ ����������
     * @var type 
     */
    protected $_is_replace    = true;
    
    /**
     * �������� ��������� ����� �����
     * 
     * @var string
     */
    public $title_field = "������";
    
    /**
     * ��������� � ����
     * 
     * @var string 
     */
    public $hint  = '';
    
    /**
     * ������������������ ������ �����
     * 
     * @var array
     */
    public $links = array();
    
    /**
     * ����� ������ � ������������� �������
     * 
     * @var array 
     */
    public $linked = array();
    
    /**
     * ����������� ��������� ������ ������ � ���������� ������ �����
     * 
     * @param integer $size    ���������� ������ �����
     * @param mixed   $link    ������(�)
     */
    public function __construct($size = null, $link = null) {
        if($size !== null) {
            $this->setListSize($size);
        }
        if($link !== null) {
            $this->initBlock($link);
        }
    }
    
    /**
     * ������ ���������� ������ � �����
     * 
     * @param integer $size
     */
    public function setListSize($size) {
        $this->_list_size = (int) $size;
    }
    
    /**
     * ���������� ���������� ���������� ������ � �����
     * 
     * @return integer
     */
    public function getListSize() {
        return $this->_list_size;
    }
    
    /**
     * �������� �� ���������� �������� ��������� ������ ���� ���� ��� ��������
     * 
     * @return boolean
     */
    public function isReplace() {
        return $this->_is_replace;
    }
    
    /**
     * ������������� ������ �����
     * 
     * @param mixed $link       ������
     */
    public function initBlock($link = null) {
        if(is_array($link)) {
            $link = array_map('stripslashes', $link);
            if(count($link) > $this->getListSize()) {
                $link = current(array_chunk($link, $this->getListSize()));
            }
            $this->links = $link;
        } elseif(count($this->links) < $this->getListSize()) {
            $link = stripslashes($link);
            array_push($this->links, $link);
        } elseif($this->isReplace()) {
            $link = stripslashes($link);
            array_pop($this->links); // ����������� ���������
            array_push($this->links, $link);
        }
    }
    
    /**
     * ����������� �����
     */
    public function displayBlock() {
        include ($_SERVER['DOCUMENT_ROOT'] . self::TEMPLATE_PATH . "/tpl.digest_list.php");
    }
    
    /**
     * �������� �� ����������� ������������� �����
     * 
     * @return boolean
     */
    public function isAutoComplete() {
        return constant(get_class($this) . '::AUTO_COMPLETE');
        //return $this::AUTO_COMPLETE;
    }
    
    /**
     * ������ �������� ��������� ����� �����
     * 
     * @param string $title
     */
    public function setTitleField($title) {
        if(func_num_args() > 1) {
            $args = func_get_args();
            array_shift($args);
            $this->title_field = vsprintf($title, $args);
        } else {
            $this->title_field = $title;
        }
    }
    
    /**
     * ���������� �������� ��������� ����� �����
     * 
     * @return string
     */
    public function getTitleField() {
        return $this->title_field;
    }
    
    /**
     * ������������� �����
     * 
     * @param array $data
     */
    public function initialize($data) {
        $class = $this->__toString();
        
        $this->setPosition($data['position'][$class]);
        $this->setListSize(count($data[$class.'Link']));
        $this->_check = ($data[$class.'Check'] == 1);
        $this->initBlock($data[$class.'Link']);
        $this->parseLinks();
    }
    
    /**
     * ������� ��� �������������� �����
     * 
     * @return boolean
     */
    public function setFieldAutoComplete() {
        return false;
    }
    
    /**
     * ��������� ��������� ������
     * 
     * @return array
     */
    public function parseLinks() {
        $parse = array();
        foreach($this->links as $i=>$link) {
            if($link == '') continue;
            if(preg_match(constant(get_class($this) . '::MASK_LINK'), $link, $match)) {
                $parse[] = stripslashes(__paramValue('string', $match[1]));
                $this->linked[$match[1]] = $i;
            } else {
                $this->_error[$i] = '������ �� �������';
            }
        }
        
        return $parse;
    }
    
    /**
     * ������ ������ �� ����������� ������
     * 
     * @param mixed $id  �� ������
     * @return string ��������� ������
     */
    public function getLinkById($id) {
        return $this->links[$this->linked[$id]];
    }
}