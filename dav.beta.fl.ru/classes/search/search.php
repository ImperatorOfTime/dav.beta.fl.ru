<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search/sphinxapi.php");

/**
 * �����-��������� API SphinxClient, ��� ���������� ����� ����������� ���������� ������.
 * ������� ����������. ����� ������, �������, VIEW, ������ ��������� ������ �������������� ��� ��������� � ����
 * �������� ���������������� �������. ��������:
 * 1. ������ 'blogs'.
 * 2. ����� 'searchElementBlogs' {@link search::ELEMENT_BASE_CLASS}.
 * 3. ���� �������� (������������ ������, ��� ������������� � ������������� ��������) 'blogs'.
 * 4. VIEW 'blogs'.
 * 5. ���� � ������� �������� 'search_element_blogs.php' {@link search::ELEMENT_FILE_PFX}.
 */
class search extends SphinxClient
{
    const EXAMPLE_PHRASE = '������';
    const ELEMENT_FILE_PFX = 'search_element_';
    const ELEMENT_BASE_CLASS = 'searchElement';

    /**
     * �������� ������, ������� ������ {@link searchElement}
     * @var array
     */
    private $_elements = array();

    /**
     * ��. �������� ����� (�������)
     * @var integer
     */
    public $uid;


    /**
     * @param integer $uid   ��. �������� ����� (�������)
     */
    function __construct($uid) {
        parent::__construct();
        $this->uid = $uid;
    }

    function setUserLimit($limit) {
        $this->_limit = (int)$limit;
    }
    /**
     * ��������� ������� � ���������.
     *
     * @param string $key   ����-������������� ��������. ������������� ������������ ��� �������� ������� ��������.
     * @param string $active   ����� �� ����������� ����� �� ������� �������� ������ (��������, ������� �� ��� ���� � ����������).
     * @return object   ������������ �������.
     */
    function addElement($key, $active = true, $limit = 10) {
        if($class = $this->getElementClass($key)) {
            $cls = ($this->_elements[$key] = new $class($this, $active));
            $cls->setUserLimit($limit);
            return $cls;
        }
        return NULL;
    }

    /**
     * �������� ��� ������ �������� �� �����.
     *
     * @param string $key   ����-������������� ��������.
     * @return string   ��� ������.
     */
    function getElementClass($key) {
        $file = dirname(__FILE__) . '/' . self::ELEMENT_FILE_PFX . $key . '.php';
        if(file_exists($file)) {
            require_once($file);
            $class = self::ELEMENT_BASE_CLASS.ucfirst($key);
            if(class_exists($class) && is_subclass_of($class, self::ELEMENT_BASE_CLASS))
                return $class;
        }
        return NULL;
    }

    /**
     * �������� ���� �������� �� ���������� ������.
     *
     * @param object $elm   �������
     * @return string   ��� �����.
     */
    function getElementKey($elm) {
        return strtolower(str_replace(self::ELEMENT_BASE_CLASS, '', get_class($elm)));
    }

    /**
     * ���������� ��������� ���������.
     *
     * @return array
     */
    function getElements() {
        return $this->_elements;
    }

    
    /**
     * ������� ��������� �������
     * 
     * @param type $type
     */
    function getElement($type) {
        return (isset($this->_elements[$type]))?$this->_elements[$type]:false;
    }



    /**
     * ���������� (���� ����� ����������) ����� ������ ��� ������� (������������ ��� ������ ������).
     *
     * @return string
     */
    function getExample() {
        return self::EXAMPLE_PHRASE;
    }

    /**
     * �������� ����� �� ���� ���������.
     *
     * @param string $string   ������ ������.
     * @param integer $page   ����� ������� �������� (������������ ��� ������ �� ����������� ��������).
     */
    function search($string, $page = 0, $filter=false) {
        if(!$string && !$filter) return;
        foreach($this->_elements as $name=>$elm) {
            if($filter && $name != "projects" && $name != "users_test") $elm->setAdvancedSearch($page, $filter);
            if(strtolower($name) != strtolower($_SESSION['search_tab_active'])) {
                $elm->active_search = false;
            } else {
                $elm->active_search = true;
            }
            $elm->search($string, $page, ($name == "projects" || $name == "users_test" || $name == "users_simple" ) ? $filter : false); // #0014689 #0016532
        }
           
    }
}
