<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_element.php";

/**
 * ����� ��� ������ �� ��
 * @todo: ����� �� ���������� ��������� � ��� ����������� ��� ������������ Sphinx
 */
class searchElementTservices extends searchElement
{
    protected $_sort = SPH_SORT_EXTENDED;
    protected $_sortby = '@weight DESC';
    
    /*
    public function isAllowed() 
    {
        return false;
    }
     */
    
}