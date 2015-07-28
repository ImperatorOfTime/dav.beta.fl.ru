<?php
/**
 * ���������� ���� � ��������� ���������
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");

class dav_file_upload {
    /**
     * ������������ ���������� ������
     */
    const MAX_FILE_COUNT    = 1;
    
    /**
     * ������������ ������ �������� �����
     */
    const MAX_FILE_SIZE     = 15728640;
    
    /**
     * ������� ��� �������� ������ � ������
     */
    const FILE_TABLE       = "file";
    
    /**
     * ������� ���������� ���������� �� ��������
     */
    const RECORDS_PER_PAGE  = 12;
    
    /**
     * ������� ���������� ��������� � ������ ������������ ���������
     */
    const MAX_ITEMS_IN_PAGING_LINE  = 8;
    
    
    /**
     * ������� ��� �������� ������ � ����������
     */
    const TABLE     = "replace_file_log";
    
    /**
     * ������� ������
     * @param $fid            ������������� �����
     * @param $file_name      ��� �����
     * @param $old_file_name  ��� �����, ������� ��� ������������ ��� ������ ����� ����������
     * @return int ������������� ������
     **/
    static public function addRecord( $fid, $file_name, $old_file_name) {
        global $DB;
        return $DB->insert(self::TABLE, array( "filename"=>$file_name, "fid"=>$fid, "old_file_name" => $old_file_name, "ip" => getRemoteIP(), "admin_id" => get_uid( false ) ), "id");
    }
}
