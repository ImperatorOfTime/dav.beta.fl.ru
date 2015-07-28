<?php

/**
 * ���������� ���� �������� ������� �������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ������ � ���������
 *
 */
class clients
{
    /**
     * ������� ������ �������
     *
     * @param string $name  �������� �������
     * @param string $link  ������ �� ���� �������   
     * @param object $logo  ������� �������
     * @param string $error ���������� ��������� �� ������ ���� ��� ����
     */
    function newClient($name, $link, $logo, &$error) {
        global $DB;
        if ($logo) {
            $logo->max_size       = 100000;
            $logo->max_image_size = array('width'=>140, 'height'=>100);
            $logo->resize         = 1;
            $logo->topfill        = 1;
            $logo->server_root    = 1;
            
            $logo_client = $logo->MoveUploadedFile("clients/");
            $error = $logo->StrError('<br />');
        }    
        
        if(!$error) {
            $sql   = "INSERT INTO clients (name_client, link_client, logo) VALUES(?, ?, ?); ";
            $error = $DB->query($sql, $name, $link, $logo_client);
        }
    }
    
    /**
     * �������������� �������
     *
     * @param string $name  �������� �������
     * @param string $link  ������ �� ���� �������   
     * @param object $logo  ������� �������
     * @param string $error ���������� ��������� �� ������ ���� ��� ����
     */
    function editClient($name, $link, $logo, $id, &$error) {
        global $DB;
        if(!$id) { $error = "������"; return false; }
        
        if ($logo) {
            $logo->max_size       = 100000;
            $logo->max_image_size = array('width'=>140, 'height'=>100);
            $logo->resize         = 1;
            $logo->topfill        = 1;
            $logo->server_root    = 1;
            
            $logo_client = $logo->MoveUploadedFile("clients/");
            $error = $logo->StrError('<br />');
        }    
        
        if(!$error) {
            if($logo) $logo_client =  ", logo = '{$logo_client}'";
            $sql   = "UPDATE clients SET name_client = ?, link_client = ? {$logo_client} WHERE id = ?i;";
            $ret = $DB->query($sql, $name, $link, $id);
            if($ret == null) $error = "������ ��������� ����������";
        }    
    }
    
    /**
     * �������� ������� �� ��� ��
     *
     * @param integer $cid �� �������
     * @return string ������ ���� ����
     */
    function deleteClient($cid) {
        global $DB;
        if(!$cid) return false;
        return $DB->query("DELETE FROM clients WHERE id = ?i", $cid);
    }
    
    /**
     * ����� ���� ��������
     *
     * @param string  $rand    ��� ���������� 
     * @param integer $limit   ����� ������
     * @return array ������ �������
     */
    function getClients($rand = "RANDOM()", $limit = 90) {
        global $DB;
        $sql  = "SELECT * FROM clients ORDER BY {$rand} LIMIT {$limit} OFFSET 0;";  
        return $DB->cache(180)->rows($sql);
    }
    
    /**
     * ����� �������� ��� ������� (��� ��������� ����������, � ���������� � ����������� ��������)
     *
     * @param integer $page     ������� ��������
     * @param integer $count    ���������� ����� ���������� ��������
     * @param integer $limit    ����� ������
     * @return array ������ ��������
     */
    function getAdminClients($page=0, &$count, $limit=10) {
        global $DB;
        $page--;
        if($page<0) $page = 0;
        if($limit<=0) $limit = 10;
        
        $page = $page*$limit;
        
        $sql  = "SELECT * FROM clients ORDER BY id DESC LIMIT {$limit} OFFSET {$page};"; 
        $ret  = $DB->rows($sql);
        
        $csql  = "SELECT COUNT(*) FROM clients;";
        $count = $DB->val($csql);
          
        return $ret;
    }
}

?>