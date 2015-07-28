<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search/search.php");

class searchExt extends search
{
    /**
     * �������� ����� �� ���������� ��������.
     * 
     * @param string $type �������� ��������
     * @param string $string   ������ ������.
     * @param integer $page   ����� ������� �������� (������������ ��� ������ �� ����������� ��������).
     */
    function searchByType($type, $string, $page = 0, $filter = false) 
    {
        if(!($elm = $this->getElement($type))) return false;
        if($filter && !in_array($type, array('projects','users_test'))) $elm->setAdvancedSearch($page, $filter);
        $elm->search($string, $page, in_array($type, array('projects','users_test','users_simple'))?$filter:false);
    }
    
}