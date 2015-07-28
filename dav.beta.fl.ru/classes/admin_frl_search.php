<?php
/**
 * ���������� ������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/paid_advices.php");

define(DEFAULT_ITEMS_PER_PAGE, 20);
define(MAX_ITEMS_PER_PAGE, 100);
/**
 * ����� ��� ������ �����������
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class admin_frl_search {
    //��������� �������
    /**
     * ������� �������������
     */
    private $prof;
    /**
     * ������� ������� 3-� �������������� ������
     */
    private $opinions;
    /**
     * ������� ������� 10 ����� � ���������
     */
    private $portfolio;
    /**
     * ������� 5 ������� �� ����� � 1 �� ������
     */
    private $visits;
    /**
     * ������� 5 �������� �� ������� 
     */
    private $projects;
    /**
     * ����� �������� 
     */
    public $page;
    /**
     * ���������� ����������� �� �������� 
     */
    private $items;
    /**
     * �������� 
     */
    private $offset;
    /**
     * ���� �� ��������� ����������
     */
    public $is_filter;
    /**
     * ������� ��� ����� ������� 
     */
    public $full_filter;
    
    /**
     * ���������� ��������� �����������
     */
    public $count;
    /**
     * ���������� ������� ����� ���������� ������, ������� �� $items 
     */
    public $pages;
    /**
     * ������ �������� ����������� 
     */
    public $totalFrls = array();
    /**
     * ������ ����������� ����� ���������? �� ���� ��� ������� ��������
     */
    public $pageFrls = array();
    
    public function __construct ($filter) {
        $this->setFilter($filter);
    }
    
    /**
     * ������������� ������ ��� ������
     * @param $filter - ������ � ���������� �������
     * @key prof - ����������� �� �������������
     * @key opinions - ���� �� ��� ������� 3 �������������� ������
     * @key portfolio - ���� �� ��� ������� 10 �����
     * @key visits - 5 ��������� �� ��������� ����� � 1 ��������� �� ��������� ������
     * @key projects - 5 �������� �� ��������� �����
     * @key page - ��������
     * @key items - ����������� �� ��������
     */
    public function setFilter ($filter) {
        if (!is_array($filter)) {
            return false;
        }
        foreach ($filter as $key=>$value) {
            $this->$key = $value;
        }
        if (!$this->page) $this->page = 1;
        if (!$this->items) $this->items = DEFAULT_ITEMS_PER_PAGE;
        if ($this->items > 100) $this->items = MAX_ITEMS_PER_PAGE;
        $this->offset = ($this->page - 1) * $this->items;
        
        if ($this->prof || $this->opinions || $this->portfolio || $this->visits || $this->projects) {
            $this->is_filter = true;
        } else {
            $this->is_filter = false;
        }
        if ($this->prof && $this->opinions && $this->portfolio && $this->visits && $this->projects) {
            $this->full_filter = true;
        } else {
            $this->full_filter = false;
        }
    }
    
    /**
     * ���������� ���������� �������������, ��������������� �������� �������
     * ������ ������������� ����������� � $frls, $pageFrls
     * 
     * @param  int $count ���������� ���������� ������� �������������� �������� �������
     * @param  array $filter ��������� �������
     * @param  int $page ����� ������� ��������
     * @return array
     */
    function searchFrls() {
        global $DB;
        
        if ($this->is_filter) {
            $sql_total = $this->getResTotal() . $this->getFrom() . $this->getExt() . $this->getCond();
        } else {
            $sql_total = $this->getResTotal() . $this->getFrom();
        }
        $totalFrls = $DB->col($sql_total);
        if (!is_array($totalFrls) || $DB->error) {
            return 0;
        }
        $this->totalFrls = $totalFrls;
        $this->count = count($totalFrls);
        $this->pages = ceil($this->count / $this->items);
        
        // ������� ����������� ��� ������ ��������
        $sql_limit = ' LIMIT ' . $this->items . ' OFFSET ' . $this->offset;
        $sql = $this->getRes() . $this->getFrom() . $this->getExt() . $this->getCond() . $this->getLimit();
        $pageFrls = $DB->rows($sql);
        
        if (!is_array($pageFrls) || $DB->error) {
            return 0;
        }
        
        $this->frls = $frls;
        $this->pageFrls = $pageFrls;
        
        return count($this->pageFrls);
    }
    
    /**
     * ���������� (������) ������ ������ �����������, ��� excel-������
     */
    private function searchFrlsTotal () {
        global $DB;
        
        $sql = $this->getResExcel() . $this->getFrom() . $this->getExt() . $this->getCond();
        $res = $DB->squery($sql);
        return $res;
    }
            
    
    /**
     * ���������� ����� � Excel, ������ ����� �� $frls - ������ ������ ����������� 
     */
    public function generateReport () {
        
        require_once( 'Spreadsheet/Excel/Writer.php' );
        
        // ����� �����������
        $res = $this->searchFrlsTotal();
        
        // ��� �����
        $fileName = '���������� (';
        if ($this->is_filter) {
            $fileName .= '� ��������';
        } else {
            $fileName .= '��� �������';
        }
        $fileName .= ')';
        $fileName .= '.xls';
        
        // ������� ��������
        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->setVersion( 8 );
        
        // ������� ����
        $worksheet =& $workbook->addWorksheet( '1' );
        $worksheet->setInputEncoding( 'CP1251' );
        
        // ������ �����
        $worksheet->setColumn(1, 2, 20);
        $worksheet->setColumn(3, 6, 25);
        $worksheet->setColumn(7, 7, 30);
        
        // �����
        $th_sty = array('FontFamily'=>'Arial', 'Size'=>10, 'Align'=>'center', 'Border'=>1, 'BorderColor'=>'black', 'Bold'=>1, 'Text_wrap'=>true);
        $format_top   =& $workbook->addFormat( $th_sty );
        
        // ��������� �����
        $worksheet->write( 0, 0, '��� "����"' );
        $worksheet->write( 2, 1, '����������' );
        
        $line = 4;
        
        if ($this->is_filter) {
            $worksheet->write($line++, 1, '��������� �������:');
            if ($this->prof) {
                $worksheet->write($line++, 1, '������ �� ��������������');
            }
            if ($this->opinions) {
                $worksheet->write($line++, 1, '������ � 3-�� � ����� ��������/��������������');
            }
            if ($this->portfolio) {
                $worksheet->write($line++, 1, '������ � 10-� � ����� �������� � ���������');
            }
            if ($this->visits) {
                $worksheet->write($line++, 1, '������ � 5-� � ����� �������� �� ���� �� ��������� ����� � 1 � ����� - �� ��������� ������');
            }
            if ($this->projects) {
                $worksheet->write($line++, 1, '������ � 5-� � ����� �������� �� ������� �� ��������� �����');
            }
        }
        
        $line = $line + 2;
       
        // ��������� �������
        $aHeader = array('� �/�', '���������', '�������������', '������/������������', '����� � ���������', '��������� �� �����', '��������� �� ������', '������� �� ������� �� �����');
        
        for ( $i = 0; $i<count($aHeader); $i++ ) {
            $worksheet->write( $line, $i, $aHeader[$i], $format_top );
        }
        
        if (!res) {
            $worksheet->write($line, 0, '�� ������ ���������� �� �������');
        }
        
        $num = 1;
        while ($frl = pg_fetch_assoc($res)) {
            
            $line++;
            
            $name = $frl['uname'] .' '. $frl['usurname'] .' ['. $frl['login'].']';
            $rowData = array(
                $num,
                $name,
                $frl['param_spec'] ? '����' : '���',
                $frl['param_opinions'] ? $frl['param_opinions'] : 0,
                $frl['param_jobs'] ? $frl['param_jobs'] : 0,
                $frl['param_m_visits'] ? $frl['param_m_visits'] : 0,
                $frl['param_w_visits'] ? $frl['param_w_visits'] : 0,
                $frl['param_projects'] ? $frl['param_projects'] : 0
            );

            $worksheet->writeRow($line, 0, $rowData);
            $num++;
        }
        
        
        // ���������� �� ����������
        $workbook->send($fileName);
        
        // ��������� ��������
        $workbook->close();
    }
    
    // ��������� ����� �������
    private function getRes () {
        // ������� ����������
        $sql_res  = 'SELECT DISTINCT frl.uid, frl.uname, frl.usurname, frl.login, frl.role, frl.is_pro, frl.is_pro_test, frl.is_team, frl.photo, frl.warn, 
            frl.email, frl.reg_ip, frl.last_ip, frl.is_banned, frl.ban_where, frl.self_deleted, frl.safety_phone, frl.safety_only_phone, 
            frl.safety_bind_ip, frl.active, frl.pop, frl.phone, frl.phone_1, frl.phone_2, frl.phone_3';
        $sql_res .= ', frl.spec_orig param_spec';
        // ������������
        $sql_res .= ', (uc.ops_emp_null + uc.ops_emp_plus + uc.ops_emp_minus + uc.sbr_opi_null + uc.sbr_opi_plus + uc.sbr_opi_minus) param_opinions';
        // ���������
        $sql_res .= ', po.jobs param_jobs';
        // ������
        $sql_res .= ', vi.m_visits param_m_visits, vi.w_visits param_w_visits';
        // �������
        $sql_res .= ', p_o.projects param_projects';
        
        return $sql_res;
    }
    private function getResExcel () {
        // ������� ����������
        $sql_res  = 'SELECT DISTINCT frl.uid, frl.uname, frl.usurname, frl.login';
        $sql_res .= ', frl.spec_orig param_spec';
        // ������������
        $sql_res .= ', (uc.ops_emp_null + uc.ops_emp_plus + uc.ops_emp_minus + uc.sbr_opi_null + uc.sbr_opi_plus + uc.sbr_opi_minus) param_opinions';
        // ���������
        $sql_res .= ', po.jobs param_jobs';
        // ������
        $sql_res .= ', vi.m_visits param_m_visits, vi.w_visits param_w_visits';
        // �������
        $sql_res .= ', p_o.projects param_projects';
        
        return $sql_res;
    }
    private function getResTotal () {
        $sql_res_total = 'SELECT DISTINCT frl.uid';
        return $sql_res_total;
    }
    private function getFrom () {
        $sql_from = ' FROM freelancer frl';
        return $sql_from;
    }
    private function getExt () {
        // ������
        $sql_ext = " LEFT JOIN users_counters uc
                    ON uc.user_id = frl.uid";
        // ���������
        $sql_ext .= " LEFT JOIN
                    (SELECT DISTINCT prt.user_id user_id, count(*) jobs
                    FROM portfolio prt
                    GROUP BY prt.user_id) po
                    ON po.user_id = frl.uid";
        
        // ������
        $sql_ext .= " LEFT JOIN
                    (SELECT DISTINCT    r2m.user_id uid, 
                                        count(r2m.*) m_visits, 
                                        sum(CASE WHEN r2m._date > (NOW() - interval '1 week') THEN 1 ELSE 0 END) w_visits
                    FROM rating_2month_log r2m
                    WHERE r2m._date > (NOW() - interval '1 month')
                        AND r2m.factor = B'000000000001000000000000000000000'
                    GROUP BY r2m.user_id) vi
                    ON vi.uid = frl.uid";

        // ���������
        $sql_ext .= " LEFT JOIN
                    (SELECT DISTINCT prj.user_id user_id, count(*) projects
                    FROM projects_offers prj
                    WHERE post_date > (NOW() - interval '1 month')
                    GROUP BY prj.user_id) p_o
                    ON p_o.user_id = frl.uid";
        return $sql_ext;
    }
    private function getCond () {
        // �������
        $sql_cond = " WHERE frl.uid > 0
                    AND frl.is_banned = B'0'
                    AND frl.active = TRUE";
        
        if ($this->prof) { // ������� �������������
            $sql_cond .= ' AND spec_orig > 0';
        }        
        if ($this->opinions) { // ������� ������� 3-� �������������� ������
            $sql_cond .= ' AND (uc.sbr_opi_plus + uc.paid_advices_cnt) >= 3';
        }
        if ($this->portfolio) { // ������� ����� � ���������
            $sql_cond .= ' AND po.jobs >= 10';
        }
        if ($this->visits) { // ���������� ���������
            $sql_cond .= ' AND vi.m_visits >= 5 AND vi.w_visits >= 1';
        }
        if ($this->projects) { // ������� �� �������
            $sql_cond .= ' AND p_o.projects >= 5';
        }
        return $sql_cond;
    }
    private function getLimit () {
        $sql_limit = ' LIMIT ' . $this->items . ' OFFSET ' . $this->offset;
        return $sql_limit;
    }
}
