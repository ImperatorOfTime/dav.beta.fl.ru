<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';

/**
 * ����� ��� ������ � ��� �� ������� ����������. ������� �������� ������ ����������.
 */
class sbr_frl extends sbr
{
    public $uid_col = 'frl_id';
    public $anti_uid_col = 'emp_id';


    public $anti_tbl = 'employer';
    public $upfx = 'frl_';
    public $apfx = 'emp_';
    public $uclass = 'freelancer';

    /**
     * ��������� ����������� � ��������� ���.
     * 
     * @param integer $version   ������ ���, ������� �� ����� ��� �������� �������. ���� �������� ������ �������� �������, ������ ����� ������� � "����������".
     * @return boolean   �������?
     */
    function agree($version) {
        if($this->status != self::STATUS_NEW) {
            // !!! ���� �������� ����� �������� ����, �� ����� �������������� ������ ������? ��� ����� �������� ���������.
            // !!! ������� ����� �������/�������� ����������/��������/����������� ������.
            // �� �� � � ����������� ������ ����� (���� ����� �������� ������ ���������)... 

            $this->error[$this->id]['canceled'] = true;  // �������� ����� ��������.
            return false;
        }

        if(!$this->_openXact(TRUE))
            return false;

        $sql = "UPDATE sbr SET status = " . self::STATUS_PROCESS . ", frl_version = ?i WHERE id = ?i";
        $sql = $this->db()->parse($sql, $version, $this->id);
        if(!($res = pg_query(self::connect(false), $sql))) {

            $this->_abortXact();
            return false;
        }

        // ������ � �������, �.�. ����������� � ������������ �������.
        foreach($this->stages as $num=>$stage) {
            if(!$stage->agreeChanges($stage->data['version'])) {
                $this->_abortXact();
                return false;
            }

        }
        
        $this->_commitXact();

        return true;
    }

    /**
     * ��������� ������������ �� ������. ������ �������� � "�����������".
     * 
     * @param integer $id   ��. ������.
     * @param string $reason   ������� ������
     * @return boolean   �������?
     */
    function refuse($reason) {
        $sql = "
          UPDATE sbr
             SET status = " . self::STATUS_REFUSED . ",
                 frl_refuse_reason = '{$reason}',
                 project_id = NULL
           WHERE id = {$this->id}
             AND frl_id = {$this->uid}
             AND reserved_id IS NULL -- !!!������ ������.
        ";
        return $this->_eventQuery($sql, true, 1);

    }
    
    /**
     * ���������� uid ������������� � �������� ���� ������ � �������� ($this->uid) ������������
     * 
     * @return array  ������ � uid ��������
     */
    function getPartersId() {
        global $DB;
        $sql = "
            SELECT 
                DISTINCT u.uid
            FROM 
                sbr 
            INNER JOIN
                freelancer u ON sbr.emp_id = u.uid
            WHERE 
                (frl_id = {$this->uid} AND emp_id IS NOT NULL)
        ";
        return $DB->col($sql);
    }

    
    /**
     * ���������� ������� ��� ��� ����������
     * 
     * @return array    ������ � ������������ (���� ������� users)
     */
    function getContacts() {
        global $DB;
        $sql = "
            SELECT
                u.*
            FROM (
                SELECT 
                    DISTINCT emp_id
                FROM 
                    sbr 
                WHERE 
                    frl_id = ?
                    AND emp_id IS NOT NULL
                    AND status <= ?
            ) s
            INNER JOIN
                employer u ON u.uid = s.emp_id AND u.is_banned = B'0'
        ";
        return $DB->rows($sql, $this->uid, sbr::STATUS_PROCESS);
    }

    
    
}

?>