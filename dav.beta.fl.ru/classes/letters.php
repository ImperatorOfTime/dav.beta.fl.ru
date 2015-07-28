<?php
/**
 * ���������� ���� � ��������� ��������� �������
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';
/**
 * ����� ��� ������ � �������� � �������
 */
class letters {

    /**
     * ����������� ���������� ������ ��������� ������
     *
     */
    const MAX_FILE_SIZE = 100097152;

    /**
     * ������������ �������� ������ �������� ��� ������������
     *
     * @var array
     */
    public static $status_colors = array('0'=>'41', '1'=>'6db335', '2'=>'6db335', '3'=>'6db335', '4'=>'6db335', '5'=>'c7271e', '6'=>'c7271e', '7'=>'c7271e', '8'=>'6db335', '9'=>'41', '10'=>'c7271e', '11'=>'6db335', '12'=>'41');

    /**
     * ������������ �������� ������ �������� ��� ������������
     *
     * @var array
     */
    public static $status_icons = array('0'=>'sbr_sempty', '1'=>'sbr_glet', '2'=>'sbr_glet', '3'=>'sbr_glet', '4'=>'sbr_glet', '5'=>'sbr_rlet', '6'=>'sbr_rtime', '7'=>'sbr_rtime', '8'=>'sbr_sok', '9'=>'sbr_sempty', '10'=>'sbr_rlet', '11'=>'sbr_sok', '12'=>'sbr_sempty');

    /**
     * ���� ��� ������������ ������� ���������
     *
     * @var array
     */
    public static $history_fields = array(1=>'��������', 2=>'������', 3=>'������� 1', 4=>'������� 2', 5=>'������� 3', 6=>'������ ������� 1', 7=>'������ ������� 2', 8=>'������ ������� 3', 9=>'��� ��������', 10=>'��������� ��������', 11=>'����������� ��������', 12=>'����������');

    
    /**
     * ���������� ���������� (�����, ��� LIMIT � OFFSET) ����� ���������� self::getLetters
     * 
     * @var integer
     */
    public $numsLetters = 0;
    

    /**
     * �������� ������ ����������
     *
     * @param  integer  $type     ��� ��������������� (���, ���������, ��������, � ���������, �����)
     * @param  array    $filter   ������ �������
     * @param  integer  $limit    ���������� ����������, ������� ����� �������� (0 - ���)
     * @param  ingeter  $offset   �������� �� ������ ������
     * @return array              ������ ����������
     */
    function getLetters($type = 0, $filter = null, $limit = '', $offset = '') {
        global $DB;

        $statuses = array();
        if($type) {
            $sql = "SELECT * FROM letters_status WHERE tab = ?i";
            $qStatus = $DB->rows($sql, $type);
            if($qStatus) {
                $status_ids = '';
                foreach($qStatus as $status) {
                    $statuses[] = $status['id'];
                    $status_ids .= $status['id'].',';
                }
                $status_ids = preg_replace("/,$/", "", $status_ids);
                $status_sql = '';
                switch($type) {
                    case 2:
                        $status_sql = "(l.user_status_1 IN ({$status_ids}) OR l.user_status_2 IN ({$status_ids}) OR l.user_status_3 IN ({$status_ids})) ";
                        break;
                    case 3:
                        $status_sql = "(l.user_status_1 IN ({$status_ids}) OR l.user_status_2 IN ({$status_ids}) OR l.user_status_3 IN ({$status_ids})) ";
                        break;
                    case 4:
                        $status_sql = "(l.user_status_1 IN ({$status_ids}) OR l.user_status_2 IN ({$status_ids}) OR l.user_status_3 IN ({$status_ids})) ";
                        break;
                    case 5:
                        $status_sql = "( (l.user_status_1 IN ({$status_ids}) AND l.user_1 IS NOT NULL) AND l.user_status_2 IN ({$status_ids}) AND (l.user_status_3 IN ({$status_ids}) OR l.user_status_3 IS NULL) )  
                                       OR ( (l.user_status_1=9 AND l.user_status_2 IS DISTINCT FROM 6 AND l.user_status_3 IS DISTINCT FROM 6) OR (l.user_status_1 IS DISTINCT FROM 6 AND l.user_status_2=9 AND l.user_status_3 IS DISTINCT FROM 6) OR (l.user_status_1 IS DISTINCT FROM 6 AND l.user_status_2 IS DISTINCT FROM 6 AND l.user_status_3=9) )
                                      ";
                        break;
                    case 6:
                        $status_sql = "(l.user_status_1 IN ({$status_ids}) OR l.user_status_2 IN ({$status_ids}) OR l.user_status_3 IN ({$status_ids})) ";
                        break;
                }
            }
        }

        if($filter['letters_filter_search_fld2']=='�������� ������, ��������� ��� ID') {
            $filter['letters_filter_search_fld2'] = '';
        }
            
        if($filter['letters_filter_search_fld']) {
            $t = $filter['letters_filter_search_fld'];
            $filter = array();
            $filter['letters_filter_search_fld'] = $t;
        }
        if($filter) {
            if($filter['letters_filter_id']) {
                $filter_sql .= "AND l.id = ".intval($filter['letters_filter_id'])." ";
            }
            if($filter['letters_filter_group_db_id']) {
                $filter_sql .= "AND l.group_id = {$filter['letters_filter_group_db_id']} ";
            }
            if($filter['letters_filter_delivery_db_id']) {
                $filter_sql .= "AND l.delivery = {$filter['letters_filter_delivery_db_id']} ";
            }
            if($filter['letters_filter_status']) {
                $filter_sql .= "AND (l.user_status_1 = {$filter['letters_filter_status']} OR l.user_status_2 = {$filter['letters_filter_status']} OR l.user_status_3 = {$filter['letters_filter_status']}) ";
            }
            if($filter['letters_filter_add_user_db_id']) {
                $filter_sql .= "AND l.user_add = {$filter['letters_filter_add_user_db_id']} ";
            }
            if($filter['letters_filter_status']) {
                if($filter['letters_filter_status']==2 || $filter['letters_filter_status']==3) {
                    if($filter['letters_filter_status_date_'.$filter['letters_filter_status'].'_eng_format']) {
                        $filter_sql .= "AND ( (l.user_status_1 = {$filter['letters_filter_status']} AND l.user_status_date_1::date = '".$filter['letters_filter_status_date_'.$filter['letters_filter_status'].'_eng_format']."')  OR (l.user_status_2 = {$filter['letters_filter_status']} AND l.user_status_date_2::date = '".$filter['letters_filter_status_date_'.$filter['letters_filter_status'].'_eng_format']."') OR (l.user_status_3 = {$filter['letters_filter_status']} AND l.user_status_date_3::date = '".$filter['letters_filter_status_date_'.$filter['letters_filter_status'].'_eng_format']."')) ";
                    }
                }
            }
            if($filter['letters_filter_add_date_s_eng_format']) {
                $filter_sql .= "AND l.date_add::date >= '{$filter['letters_filter_add_date_s_eng_format']}' ";
            }
            if($filter['letters_filter_add_date_e_eng_format']) {
                $filter_sql .= "AND l.date_add::date <= '{$filter['letters_filter_add_date_e_eng_format']}' ";
            }
            if($filter['letters_filter_change_date_s_eng_format']) {
                $filter_sql .= "AND l.date_change_status::date >= '{$filter['letters_filter_change_date_s_eng_format']}' ";
            }
            if($filter['letters_filter_change_date_e_eng_format']) {
                $filter_sql .= "AND l.date_change_status::date <= '{$filter['letters_filter_change_date_e_eng_format']}' ";
            }
            if($filter['letters_filter_get_user_db_id']) {
                $t = ($filter['letters_filter_get_user_section']=='1' ? 'IS true' : 'IS NOT true');
                $filter_sql .= "AND ((l.user_1 = {$filter['letters_filter_get_user_db_id']} AND l.is_user_1_company {$t}) OR (l.user_2 = {$filter['letters_filter_get_user_db_id']} AND l.is_user_2_company {$t}) OR (l.user_3 = {$filter['letters_filter_get_user_db_id']} AND l.is_user_3_company {$t}))";
            }

            if($filter['letters_filter_search_fld2'] && $filter['letters_filter_search_fld2']!='null') {
                $filter['letters_filter_search_fld'] = $filter['letters_filter_search_fld2'];
            }

            if($filter['letters_filter_search_fld']) {
                $sql = "SELECT * FROM letters_group WHERE lower(title) ILIKE ('%".strtolower($filter['letters_filter_search_fld'])."%')";
                $qgroups = $DB->rows($sql);
                if($qgroups) {
                    foreach($qgroups as $qgroup) {
                        $groups_ids .= $qgroup['id'].',';
                    }
                    $groups_ids = preg_replace("/,$/", "", $groups_ids);
                }
                $filter_sql .= "AND (l.id::text ILIKE ('".$filter['letters_filter_search_fld']."%') OR lower(l.title) ILIKE('%".strtolower($filter['letters_filter_search_fld'])."%') ".($groups_ids ? "OR l.group_id IN (".$groups_ids.")" : "")." ) ";
            }

            if($filter['ids']) {
                $filter_sql .= $DB->parse("AND l.id IN (?l)", $ids);
            }

            if($filter['letters_filter_withoutourdoc']=="1") {
                $filter_sql .= "AND l.withoutourdoc = 't' ";
            }

            $filter_sql = (!$status_sql ? preg_replace("/^AND/", "", $filter_sql) : $filter_sql);
        }

        $where_sql  = $status_sql.$filter_sql;
        if ( $type != 2 && $type != 6 ) {
            $limit_sql  = $limit? "LIMIT {$limit}": "";
            $offset_sql = $offset? "OFFSET {$offset}": "";
        }
        /*if($type==1 || $type==5) {
            if($where_sql) {
                //$where_sql = $where_sql." AND l.date_add > NOW()-interval '7 days' ";
                $limit = 'LIMIT 50';
            } else {
                //$where_sql = " l.date_add > NOW()-interval '7 days' ";
                $limit = 'LIMIT 50';
            }
        }*/
        $sql = "SELECT l.*, 
                       l_g.title as group_title, 
                       l_d.title as delivery_title,
                       l_p.title as parent_title 
                FROM letters AS l 
                LEFT JOIN letters_group AS l_g ON l_g.id=l.group_id 
                LEFT JOIN letters_delivery AS l_d ON l_d.id=l.delivery  
                LEFT JOIN letters AS l_p ON l_p.id=l.parent 
                ".($where_sql ? "WHERE {$where_sql}" : "")."
                ORDER BY l.date_add DESC {$limit_sql} {$offset_sql}";
        $letters = $DB->rows($sql);

        if ( $limit_sql ) {
            $sql = "SELECT COUNT(*)
                    FROM letters AS l 
                    LEFT JOIN letters_group AS l_g ON l_g.id=l.group_id 
                    LEFT JOIN letters_delivery AS l_d ON l_d.id=l.delivery  
                    LEFT JOIN letters AS l_p ON l_p.id=l.parent 
                    ".($where_sql ? "WHERE {$where_sql}" : "");
            $this->numsLetters =  $DB->val($sql);
        }

        if($type==2 || $type==6) {
            $o_letters = $letters;
            $letters = array();
            if($o_letters) {
                foreach($o_letters as $letter) {
                    if($type==2) {
                        if(in_array($letter['user_status_1'], array(1))) {
                            $letters[$letter['user_1'].'-'.$letter['delivery'].'-'.$letter['is_user_1_company']][] = $letter;
                        }
                    }
                    if($type==6) {
                        if(in_array($letter['user_status_1'], array(11))) {
                            $letters[$letter['user_1'].'-'.$letter['delivery'].'-'.$letter['is_user_1_company']][] = $letter;
                        }
                    }
                }
                foreach($o_letters as $letter) {
                    if($type==2) {
                        if(in_array($letter['user_status_2'], array(1))) {
                            $letters[$letter['user_2'].'-'.$letter['delivery'].'-'.$letter['is_user_2_company']][] = $letter;
                        }
                    }
                    if($type==6) {
                        if(in_array($letter['user_status_2'], array(11))) {
                            $letters[$letter['user_2'].'-'.$letter['delivery'].'-'.$letter['is_user_2_company']][] = $letter;
                        }
                    }
                }
                foreach($o_letters as $letter) {
                    if($type==2) {
                        if(in_array($letter['user_status_3'], array(1))) {
                            $letters[$letter['user_3'].'-'.$letter['delivery'].'-'.$letter['is_user_3_company']][] = $letter;
                        }
                    }
                    if($type==6) {
                        if(in_array($letter['user_status_3'], array(11))) {
                            $letters[$letter['user_3'].'-'.$letter['delivery'].'-'.$letter['is_user_3_company']][] = $letter;
                        }
                    }
                }
                /*
                foreach($o_letters as $letter) {
                    if(in_array($letter['user_status_1'], array(1,2,3,4))) {
                        $letters[$letter['user_1'].'-'.$letter['delivery']][] = $letter;
                    } else if(in_array($letter['user_status_2'], array(1,2,3,4))) {
                        $letters[$letter['user_2'].'-'.$letter['delivery']][] = $letter;
                    } else if(in_array($letter['user_status_3'], array(1,2,3,4))) {
                        $letters[$letter['user_3'].'-'.$letter['delivery']][] = $letter;
                    }
                }
                */
            }

            if ( $limit ) {
                $this->numsLetters = count($letters);
                $letters = array_slice($letters, $offset, $limit);
            }
        }

        return $letters;
    }

    /**
     * ������� ��������
     *
     * @param    integer    $id    ID ���������
     */
    function delDocument($id) {
        global $DB;

        $sql = "SELECT file_id FROM letters WHERE id = ?i";
        $file_id = $DB->val($sql, $id);
        if($file_id) {
            $cFile = new CFile();
            $cFile->table = 'file';
            $cFile->GetInfoById($file_id);

            $cFile->delete($file_id);
        }
        $sql = "DELETE FROM letters WHERE id=?i";
        $DB->query($sql, $id);
        $sql = "UPDATE letters SET parent=NULL WHERE parent=?i";
        $DB->query($sql, $id);
    }

    /**
     * ������������ ����������
     *
     * @param    array    $ids    ID ����������
     */
    function processDocs($ids) {
        global $DB;

        $sql = "UPDATE letters SET is_out=true WHERE ( user_status_1=1 OR user_status_2=1 OR user_status_3=1 ) AND id IN (?l)";
        $DB->query($sql, $ids);
    }

    /**
     * ������������ ���������� ��� ��������
     *
     * @param    array    $d_ids    ID ����������
     * @param    array    $u_ids    ID �����������
     * @param    array    $delivery_ids    ID ��������� ��������
     */
    function processSendDocs($d_ids, $u_ids, $delivery_ids) {
        global $DB;

        $filtes['ids'] = $d_ids;
        $docs_list = letters::getLetters(6, $filter);

        $docs_without_delivery = array();
        if($docs_list) {

            foreach($docs_list as $k_l=>$v_l) {
                if(!in_array(preg_replace("/-.*$/", "", $k_l),$u_ids)) {
                    unset($docs_list[$k_l]);
                } else {
                    foreach($v_l as $k_d=>$v_d) {
                        if(!in_array($v_d['id'],$d_ids) || !in_array($v_d['delivery'],$delivery_ids)) {
                            unset($docs_list[$k_l][$k_d]);
                        }
                    }
                }
                if(!$docs_list[$k_l]) {
                    unset($docs_list[$k_l]);
                }
            }

            foreach($docs_list as $v) {
                foreach($v as $v1) {
                    if(intval($v1['delivery'])==0) {
                        $docs_without_delivery[] = array('id'=>$v1['id'], 'title'=>$v1['title']);
                    }
                }
            }
            if(!$docs_without_delivery) {
                $_SESSION['admin_letters_data'] = $docs_list;
            }
        }
        return $docs_without_delivery;
    }

    /**
     * ��������� ��������� �������� ����������
     *
     * @param    array    $ids    ID ����������
     * @return   float            ��������� ��������
     */
    function calcDeliveryCost($ids) {
        global $DB;
        $cost = 0;
        if($ids) {
            $sql = "SELECT id, delivery_cost FROM letters WHERE id IN (?l) AND delivery_cost IS NOT NULL";
            $qdocs = $DB->rows($sql, $ids);
            if($qdocs) {
                foreach($qdocs as $doc) {
                    $docs[$doc['id']] = $doc['delivery_cost'];
                }
                foreach($ids as $id) {
                    $cost = $cost + sprintf("%01.2f", floatval($docs[$id]));
                }
            }
        }
        return $cost;
    }

    /**
     * �������� ������� ����������(��������)
     *
     * @param    array    $ids_docs        ID
     * @param    array    $ids_statuses    �������
     */
    function updateMassStatus($ids_docs, $ids_statuses) {
        global $DB;

        if($ids_docs && $ids_statuses) {
            for($n=1; $n<=3; $n++) {
                $docs = $ids_docs;

                foreach($ids_statuses as $old_s=>$new_s) {
                    $sql = "SELECT id FROM letters WHERE user_{$n} IS NOT NULL AND user_status_{$n} ".($old_s ? "=" : "IS")." ? AND id IN (?l)";
                    $tdocs = $DB->rows($sql, ($old_s ? $old_s : NULL), $docs);

                    $sql = "SELECT * FROM letters WHERE user_{$n} IS NOT NULL AND user_status_{$n} ".($old_s ? "=" : "IS")." ? AND id IN (?l)";
                    $qhistory = $DB->rows($sql, ($old_s ? $old_s : NULL), $docs);
                    if($qhistory) {
                        foreach($qhistory as $history) {
                            $data = array();
                            $data["id"] = $history['id'];
                            $data["user_status_{$n}"] = ($new_s['id'] ? $new_s['id'] : NULL);
                            $data["user_status_date_{$n}"] = ($new_s['date'] ? $new_s['date'] : NULL);
                            letters::saveHistory($data['id'], $data);
                        }
                    }

                    $sql = "UPDATE letters SET user_status_{$n} = ?, user_status_date_{$n} = ? WHERE user_{$n} IS NOT NULL AND user_status_{$n} ".($old_s ? "=" : "IS")." ? AND id IN (?l)";
                    $DB->query($sql, ($new_s['id'] ? $new_s['id'] : NULL), ($new_s['date'] ? $new_s['date'] : NULL), ($old_s ? $old_s : NULL), $docs);

                    if($tdocs) {
                        foreach($tdocs as $k=>$v) {
                            $key = array_search($v['id'], $docs);
                            if($key!=false) {
                                unset($docs[$key]);
                            }
                        }
                    }
                }
            }
        }
        $sql = "UPDATE letters SET is_out=false WHERE (user_status_1 IS DISTINCT FROM 1 AND user_status_2 IS DISTINCT FROM 1 AND user_status_3 IS DISTINCT FROM 1) AND id IN (?l)";
        $DB->query($sql, $ids_docs);
        $sql = "UPDATE letters SET date_change_status = NOW() WHERE id IN (?l)";
        $DB->query($sql, $ids_docs);
    }

    /**
     * ��������� �������� "�������� ��� ������ ����������"
     *
     * @param     integer    $id            ID ���������
     * @param     boolean    $is_checked    ������ ��������
     */
    function changeWithoutourdocs($id, $is_checked) {
        global $DB;

        $sql = "UPDATE letters SET withoutourdoc = ?b WHERE id=?i";
        $DB->query($sql, $is_checked, $id);
    }

    /**
     * �������� ��������� �������� ����������(��������)
     *
     * @param    array    $ids_docs        ID ����������
     * @param    float    $cost            ��������� ��������
     */
    function updateMassDeliveryCost($ids_docs, $cost) {
        global $DB;

        if($ids_docs) {
            foreach($ids_docs as $id) {
                $data = array();
                $data["id"] = $id;
                $data["delivery_cost"] = $cost;
                letters::saveHistory($data['id'], $data);
            }
        }

        $sql = "UPDATE letters SET delivery_cost=? WHERE id IN (?l)";
        $DB->query($sql, $cost, $ids_docs);
    }

    /**
     * �������� ���� ����������(��������)
     *
     * @param    array    $ids_docs        ID ����������
     * @param    atring   $date            ����
     */
    function updateMassDate($ids_docs, $date) {
        global $DB;

        $sql = "UPDATE letters SET date_change_status=? WHERE id IN (?l)";
        $DB->query($sql, $date, $ids_docs);
    }

    /**
     * �������� ������ �������� ����������
     *
     * @param    array    $ids    ID ����������
     * @return   array            �������
     */
    function getDocumentsStatuses($ids) {
        global $DB;
        $statuses = array();
        if($ids) {
            $sql = "SELECT user_1, user_2, user_3, user_status_1, user_status_2, user_status_3 FROM letters WHERE id IN (?l)";
            $qstatuses = $DB->rows($sql, $ids);
            if($qstatuses) {
                $qtstatuses = letters::getStatuses();
                foreach($qtstatuses as $tstatus) { 
                    $tstatuses[$tstatus['id']] = $tstatus['title'];
                }
                $tstatuses[0] = '�� �������';
                foreach($qstatuses as $status) {
                    if($status['user_1']) { $statuses[intval($status['user_status_1'])] = $tstatuses[intval($status['user_status_1'])]; }
                    if($status['user_2']) { $statuses[intval($status['user_status_2'])] = $tstatuses[intval($status['user_status_2'])]; }
                    if($status['user_3']) { $statuses[intval($status['user_status_3'])] = $tstatuses[intval($status['user_status_3'])]; }
                }
            }
        }
        return $statuses;
    }

    /**
     * �������� ��������
     *
     * @param    integer    $id    ID ���������
     * @return   array             ���������� � ���������
     */
    function getDocument($id) {
        global $DB;

        $sql = "SELECT l.*, 
                       l_g.title as group_title, 
                       l_d.title as delivery_title,
                       l_p.title as parent_title 
                FROM letters AS l 
                LEFT JOIN letters_group AS l_g ON l_g.id=l.group_id 
                LEFT JOIN letters_delivery AS l_d ON l_d.id=l.delivery  
                LEFT JOIN letters AS l_p ON l_p.id=l.parent 
                WHERE l.id = ?i";
        $doc = $DB->row($sql, $id);

        return $doc;
    }

    /**
     * �������� ��������� �� IS
     *
     * @param    integer    $ids   ID ����������
     * @return   array             ���������� � ����������
     */
    function getDocumentsByID($ids) {
        global $DB;

        $sql = "SELECT l.*, 
                       l_g.title as group_title, 
                       l_d.title as delivery_title,
                       l_p.title as parent_title 
                FROM letters AS l 
                LEFT JOIN letters_group AS l_g ON l_g.id=l.group_id 
                LEFT JOIN letters_delivery AS l_d ON l_d.id=l.delivery  
                LEFT JOIN letters AS l_p ON l_p.id=l.parent 
                WHERE l.id IN (?l)";
        $doc = $DB->rows($sql, $ids);

        return $doc;
    }

    /**
     * �������� ������ ��������� ��������
     *
     * @return    array    ������ � ��������
     */
    function getStatuses() {
        global $DB;

        $sql = "SELECT * FROM letters_status ORDER BY id";
        $statuses = $DB->rows($sql);

        return $statuses;
    }

    /**
     * �������� ���� ���������
     *
     * @param    integer    $id    ID ���������
     * @param    array      $data  ������ ��� ����������
     */
    function updateFields($id, $data) {
        global $DB;

        letters::saveHistory($id, $data);
        $DB->update('letters', $data, 'id=?i', $id);

        $sql = "UPDATE letters SET is_out=false WHERE (user_status_1 IS DISTINCT FROM 1 AND user_status_2 IS DISTINCT FROM 1 AND user_status_3 IS DISTINCT FROM 1) AND id=?i";
        $DB->query($sql, $id);
    }

    /**
     * �������� ������ ��������� �������� ��������
     *
     * @return    array    ������ � �������� ��������
     */
    function getDeliveries() {
        global $DB;

        $sql = "SELECT * FROM letters_delivery ORDER BY id";
        $deliveries = $DB->rows($sql);

        return $deliveries;
    }

    /**
     * �������� ������ ����
     *
     * @param     string   ������ ��� ������ ������ �� ��������
     * @param     integer  ������� ���� ��������
     * @return    array    ������ � �������
     */
    function getGroups($word = null, $limit = 'ALL') {
        global $DB;

        $word = iconv("UTF-8", "WINDOWS-1251//IGNORE", $word);

        $sql = "SELECT * FROM letters_group ".($word ? "WHERE lower(title) ILIKE (?)" : "")." ORDER BY title LIMIT {$limit}";
        $groups = $DB->rows($sql, ($word ? '%' . strtolower($word) . '%' : null) );

        return $groups;
    }

    /**
     * �������� �����
     *
     * @param     integer $id    ID ������
     * @return    array    ������ � ������
     */
    function getGroup($id) {
        global $DB;

        $sql = "SELECT * FROM letters_group WHERE id = ?i";
        $group = $DB->row($sql,$id);

        return $group;
    }

    /**
     * �������� ������ ����������
     *
     * @param     string   ������ ��� ������ ��������� �� ID
     * @param     integer  ������� ���������� ��������
     * @return    array    ������ � ����������
     */
    function getDocuments($word = null, $limit = 'ALL') {
        global $DB;

        $word = iconv("UTF-8", "WINDOWS-1251//IGNORE", $word);

        $sql = "SELECT * FROM letters ".($word ? "WHERE id::text ILIKE (?)" : "")." ORDER BY id LIMIT {$limit}";
        $documents = $DB->rows($sql, ($word ? $word . '%' : null));

        if($documents) {
            $qgroups = $DB->rows("SELECT * FROM letters_group");
            foreach($qgroups as $val) {
                $groups[$val['id']] = $val['title'];
            }
            foreach($documents as $key=>$document) {
                $documents[$key]['group_title'] = $groups[$document['group_id']];
            }
        }

        return $documents;
    }

    /**
     * �������� ������ ��������
     *
     * @param     string   ������ ��� ������ ��������
     * @param     integer  ������� �������� ��������
     * @return    array    ������ � ���������
     */
    function getCompanies($word = null, $limit = 'ALL') {
        global $DB;

        $sql = "SELECT c.*, country.country_name AS country_title, city.city_name AS city_title FROM letters_company AS c LEFT JOIN country ON country.id=c.country LEFT JOIN city ON city.id=c.city ".($word ? "WHERE name ILIKE ('{$word}%')" : "")." ORDER BY name LIMIT {$limit}";
        $companies['data'] = $DB->rows($sql);

        $sql = "SELECT COUNT(id) FROM letters_company ".($word ? "WHERE name ILIKE ('{$word}%')" : "");
        $companies['count'] = $DB->val($sql);

        return $companies;
    }

    /**
     * ����� ����������
     *
     * @param     string   ������ ��� ������ ���������
     * @param     integer  ������� ���������� ��������
     * @return    array    ������ � ����������
     */
    function getSearchDocuments($word = null, $limit = 'ALL') {
        global $DB;

        $word = iconv("UTF-8", "WINDOWS-1251//IGNORE", $word);

        $sql = "SELECT * FROM letters ".($word ? "WHERE id::text ILIKE (?)" : "")." ORDER BY id LIMIT {$limit}";
        $documents = $DB->rows($sql, ($word ? '%' . $word . '%' : null));

        if(!$documents) {
            $sql = "SELECT * FROM letters ".($word ? "WHERE group_id IN (SELECT id FROM letters_group WHERE lower(title) ILIKE (?))" : "")." ORDER BY id LIMIT {$limit}";
            $documents = $DB->rows($sql, ($word ? '%' . strtolower($word) . '%' : null));
            if(!$documents) {
                $sql = "SELECT * FROM letters ".($word ? "WHERE lower(title) ILIKE (?)" : "")." ORDER BY id LIMIT {$limit}";
                $documents = $DB->rows($sql, ($word ? '%' . strtolower($word) . '%' : null));
            }
        }

        if($documents) {
             $qgroups = $DB->rows("SELECT * FROM letters_group");
            foreach($qgroups as $val) {
                $groups[$val['id']] = $val['title'];
            }
            foreach($documents as $key=>$document) {
                $documents[$key]['group_title'] = $groups[$document['group_id']];
            }
        }
        return $documents;
    }

    /**
     * �������� � ���� ������ ����� ���, �� ��������
     *
     * @param    string    $title    �������� ������
     * @return   integer             ID ������
     */
    function checkCreateGroup($title) {
        global $DB;
        $sql = "SELECT id FROM letters_group WHERE title = ?";
        $group_id = $DB->val($sql, $title);
        if(!$group_id) {
            $sql = "INSERT INTO letters_group(title) VALUES(?) RETURNING id";
            $group_id = $DB->val($sql, $title);
        }
        return $group_id;
    }

    /**
     * ���������� ������ ���������
     *
     * @param    array    $data    ������ ���������
     * @return   integer           ID ���������
     */
    function addDocument($data) {
        global $DB;

        if(!$data['letters_doc_frm_user_3_db_id'] || $data['letters_doc_frm_user_3_db_id']=='null') {
            $data['letters_doc_frm_user_3_db_id'] = null;
            $data['letters_doc_frm_user3_status_data'] = null;
            $data['letters_doc_frm_user3_status_date_data'] = null;
        }
        if(!$data['letters_doc_frm_user3_status_date_data']) $data['letters_doc_frm_user3_status_date_data'] = null;
        if(!$data['letters_doc_frm_user2_status_date_data']) $data['letters_doc_frm_user2_status_date_data'] = null;
        if(!$data['letters_doc_frm_user1_status_date_data']) $data['letters_doc_frm_user1_status_date_data'] = null;
        if(!$data['letters_doc_frm_user3_status_data']) $data['letters_doc_frm_user3_status_data'] = null;
        if(!$data['letters_doc_frm_user2_status_data']) $data['letters_doc_frm_user2_status_data'] = null;
        if(!$data['letters_doc_frm_user1_status_data']) $data['letters_doc_frm_user1_status_data'] = null;

        if(!$data['letters_doc_frm_parent_db_id'] || $data['letters_doc_frm_parent_db_id']=='null') $data['letters_doc_frm_parent_db_id'] = null;
        if(!$data['letters_doc_frm_delivery_cost']) $data['letters_doc_frm_delivery_cost'] = null;
        if(!$data['letters_doc_frm_comment']) $data['letters_doc_frm_comment'] = null;

        if(!$data['letters_doc_frm_group'] || $data['letters_doc_frm_group']=='null') $data['letters_doc_frm_group'] = null;
        if(!$data['letters_doc_frm_group_db_id'] || $data['letters_doc_frm_group_db_id']=='null') $data['letters_doc_frm_group_db_id'] = null;


        if(!$data['letters_doc_frm_group_db_id'] || $data['letters_doc_frm_group_db_id']=='null') {
            if($data['letters_doc_frm_group'] && $data['letters_doc_frm_group']!='' && $data['letters_doc_frm_group']!='null') {
                $data['letters_doc_frm_group_db_id'] = letters::checkCreateGroup($data['letters_doc_frm_group']);
            }
        }

        if(!$data['letters_doc_frm_delivery_db_id'] || $data['letters_doc_frm_delivery_db_id']=='null') {
            $data['letters_doc_frm_delivery_db_id'] = null;
        }

        if($data['letters_doc_frm_user_1_section']=='1') {
            $data['letters_doc_frm_user_1_section'] = true;
        } else {
            $data['letters_doc_frm_user_1_section'] = false;
        }
        if($data['letters_doc_frm_user_2_section']=='1') {
            $data['letters_doc_frm_user_2_section'] = true;
        } else {
            $data['letters_doc_frm_user_2_section'] = false;
        }
        if($data['letters_doc_frm_user_3_section']=='1') {
            $data['letters_doc_frm_user_3_section'] = true;
        } else {
            $data['letters_doc_frm_user_3_section'] = false;
        }
        if($data['letters_doc_frm_withoutourdoc']=='1') {
            $data['letters_doc_frm_withoutourdoc'] = true;
        } else {
            $data['letters_doc_frm_withoutourdoc'] = false;
        }


        $sql = "INSERT INTO letters(
                                     title, 
                                     date_add, 
                                     user_add, 
                                     user_1, 
                                     user_2, 
                                     user_3, 
                                     group_id, 
                                     delivery_cost, 
                                     comment, 
                                     delivery,
                                     date_change_status, 
                                     parent,
                                     user_status_1, 
                                     user_status_2, 
                                     user_status_3, 
                                     user_status_date_1, 
                                     user_status_date_2, 
                                     user_status_date_3,
                                     is_user_1_company, 
                                     is_user_2_company, 
                                     is_user_3_company,
                                     withoutourdoc   
                                   ) VALUES (
                                     ?,
                                     ".($data['letters_doc_frm_dateadd_eng_format'] ? "'{$data['letters_doc_frm_dateadd_eng_format']}'" : "NOW()").",
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ".($data['letters_doc_frm_dateadd_eng_format'] ? "'{$data['letters_doc_frm_dateadd_eng_format']}'" : "NOW()").",
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?  
                                   ) RETURNING id;";
        $id = $DB->val($sql, 
                    $data['letters_doc_frm_title'],
                    get_uid(false),
                    $data['letters_doc_frm_user_1_db_id'],
                    $data['letters_doc_frm_user_2_db_id'],
                    $data['letters_doc_frm_user_3_db_id'],
                    $data['letters_doc_frm_group_db_id'],
                    $data['letters_doc_frm_delivery_cost'],
                    $data['letters_doc_frm_comment'], 
                    $data['letters_doc_frm_delivery_db_id'],
                    $data['letters_doc_frm_parent_db_id'],
                    $data['letters_doc_frm_user1_status_data'],
                    $data['letters_doc_frm_user2_status_data'],
                    $data['letters_doc_frm_user3_status_data'],
                    $data['letters_doc_frm_user1_status_date_data'],
                    $data['letters_doc_frm_user2_status_date_data'],
                    $data['letters_doc_frm_user3_status_date_data'],
                    $data['letters_doc_frm_user_1_section'], 
                    $data['letters_doc_frm_user_2_section'], 
                    $data['letters_doc_frm_user_3_section'],
                    $data['letters_doc_frm_withoutourdoc']
                  );
        if($id) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
            $attachedfiles = new attachedfiles($data['attachedfiles_session']);
            $attachedfiles_files = $attachedfiles->getFiles();

            if($attachedfiles_files) {
                foreach($attachedfiles_files as $attachedfiles_file) {
                    $cFile = new CFile();
                    $cFile->table = 'file';
                    $cFile->GetInfoById($attachedfiles_file['id']);

                    $ext = $cFile->getext();
                    $tmp_dir = "letters/";
                    $tmp_name = $cFile->secure_tmpname($tmp_dir, '.'.$ext);
                    $tmp_name = substr_replace($tmp_name,"",0,strlen($tmp_dir));
                    $cFile->_remoteCopy($tmp_dir.$tmp_name, true);
                }
                $sql = "UPDATE letters SET file_id = ?i WHERE id = ?i";
                $DB->query($sql, $cFile->id, $id);
            }
            $attachedfiles->clear();
        }
        return $id;
    }

    /**
     * �������� �������
     *
     * @param    integer    $id    ID �������
     */
    function delTemplate($id) {
        global $DB;
        $sql = "DELETE FROM letters_template_doc WHERE template_id = ?i";
        $DB->query($sql, $id);
        $sql = "DELETE FROM letters_template WHERE id = ?i";
        $DB->query($sql, $id);
    }

    /**
     * �������� ���������� � �������
     *
     * @param    integer    $id     ID �������
     * @return   array              ������
     */
    function getTemplate($id) {
        global $DB;
        $sql = "SELECT * FROM letters_template WHERE id=?i";
        $template = $DB->row($sql, $id);
        $sql = "SELECT * FROM letters_template_doc WHERE template_id=?i ORDER BY id ASC";
        $template['docs'] = $DB->rows($sql, $id);
        return $template;
    }

    /**
     * �������� ���������� � �������
     *
     * @param    array    $data     ������
     */
    function updateTemplate($data) {
        global $DB;
        $sql = "UPDATE letters_template SET title = ? WHERE id=?i";
        $DB->query($sql, $data['template_title'], $data['template_id']);
    }

    /**
     * ������� ��������� �� �������
     *
     * @param    integer    $id     ������
     */
    function delTemplateDocs($id) {
        global $DB;
        $sql = "DELETE FROM letters_template_doc WHERE template_id=?i";
        $DB->query($sql, $id);
    }

    /**
     * ���������� ������ ��������� � ������
     *
     * @param    integer  $tpl     ID �������
     * @param    array    $data    ������ �������
     * @return   integer           ID ���������
     */
    function addTemplateDoc($data) {
        global $DB;

        if(!$data['letters_doc_frm_user_3_db_id'] || $data['letters_doc_frm_user_3_db_id']=='null') {
            $data['letters_doc_frm_user_3_db_id'] = null;
            $data['letters_doc_frm_user3_status_data'] = null;
            $data['letters_doc_frm_user3_status_date_data'] = null;
        }
        if(!$data['letters_doc_frm_user_2_db_id'] || $data['letters_doc_frm_user_2_db_id']=='null') {
            $data['letters_doc_frm_user_2_db_id'] = null;
            $data['letters_doc_frm_user2_status_data'] = null;
            $data['letters_doc_frm_user2_status_date_data'] = null;
        }
        if(!$data['letters_doc_frm_user_1_db_id'] || $data['letters_doc_frm_user_1_db_id']=='null') {
            $data['letters_doc_frm_user_1_db_id'] = null;
            $data['letters_doc_frm_user1_status_data'] = null;
            $data['letters_doc_frm_user1_status_date_data'] = null;
        }

        if(!$data['letters_doc_frm_user3_status_date_data']) $data['letters_doc_frm_user3_status_date_data'] = null;
        if(!$data['letters_doc_frm_user2_status_date_data']) $data['letters_doc_frm_user2_status_date_data'] = null;
        if(!$data['letters_doc_frm_user1_status_date_data']) $data['letters_doc_frm_user1_status_date_data'] = null;
        if(!$data['letters_doc_frm_user3_status_data']) $data['letters_doc_frm_user3_status_data'] = null;
        if(!$data['letters_doc_frm_user2_status_data']) $data['letters_doc_frm_user2_status_data'] = null;
        if(!$data['letters_doc_frm_user1_status_data']) $data['letters_doc_frm_user1_status_data'] = null;

        if(!$data['letters_doc_frm_parent_db_id'] || $data['letters_doc_frm_parent_db_id']=='null') $data['letters_doc_frm_parent_db_id'] = null;
        if(!$data['letters_doc_frm_delivery_cost']) $data['letters_doc_frm_delivery_cost'] = null;
        if(!$data['letters_doc_frm_comment']) $data['letters_doc_frm_comment'] = null;

        if(!$data['letters_doc_frm_group'] || $data['letters_doc_frm_group']=='null') $data['letters_doc_frm_group'] = null;
        if(!$data['letters_doc_frm_group_db_id'] || $data['letters_doc_frm_group_db_id']=='null') $data['letters_doc_frm_group_db_id'] = null;


        if(!$data['letters_doc_frm_group_db_id'] || $data['letters_doc_frm_group_db_id']=='null') {
            if($data['letters_doc_frm_group'] && $data['letters_doc_frm_group']!='' && $data['letters_doc_frm_group']!='null') {
                $data['letters_doc_frm_group_db_id'] = letters::checkCreateGroup($data['letters_doc_frm_group']);
            }
        }

        if(!$data['letters_doc_frm_delivery_db_id'] || $data['letters_doc_frm_delivery_db_id']=='null') {
            $data['letters_doc_frm_delivery_db_id'] = null;
        }

        if($data['letters_doc_frm_user_1_section']=='1') {
            $data['letters_doc_frm_user_1_section'] = true;
        } else {
            $data['letters_doc_frm_user_1_section'] = false;
        }
        if($data['letters_doc_frm_user_2_section']=='1') {
            $data['letters_doc_frm_user_2_section'] = true;
        } else {
            $data['letters_doc_frm_user_2_section'] = false;
        }
        if($data['letters_doc_frm_user_3_section']=='1') {
            $data['letters_doc_frm_user_3_section'] = true;
        } else {
            $data['letters_doc_frm_user_3_section'] = false;
        }
        if($data['letters_doc_frm_withoutourdoc']=='1') {
            $data['letters_doc_frm_withoutourdoc'] = true;
        } else {
            $data['letters_doc_frm_withoutourdoc'] = false;
        }


        $sql = "INSERT INTO letters_template_doc(
                                     template_id,
                                     title, 
                                     date_add, 
                                     user_1, 
                                     user_2, 
                                     user_3, 
                                     group_id, 
                                     delivery_cost, 
                                     comment, 
                                     delivery,
                                     date_change_status, 
                                     parent,
                                     user_status_1, 
                                     user_status_2, 
                                     user_status_3, 
                                     user_status_date_1, 
                                     user_status_date_2, 
                                     user_status_date_3,
                                     is_user_1_company, 
                                     is_user_2_company, 
                                     is_user_3_company,
                                     withoutourdoc   
                                   ) VALUES (
                                     ?,
                                     ?,
                                     ".($data['letters_doc_frm_dateadd_eng_format'] ? "'{$data['letters_doc_frm_dateadd_eng_format']}'" : "NOW()").",
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ".($data['letters_doc_frm_dateadd_eng_format'] ? "'{$data['letters_doc_frm_dateadd_eng_format']}'" : "NOW()").",
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?,
                                     ?  
                                   ) RETURNING id;";

        $id = $DB->val($sql, 
                    $data['letters_doc_frm_template_id'],
                    $data['letters_doc_frm_title'],
                    $data['letters_doc_frm_user_1_db_id'],
                    $data['letters_doc_frm_user_2_db_id'],
                    $data['letters_doc_frm_user_3_db_id'],
                    $data['letters_doc_frm_group_db_id'],
                    $data['letters_doc_frm_delivery_cost'],
                    $data['letters_doc_frm_comment'], 
                    $data['letters_doc_frm_delivery_db_id'],
                    $data['letters_doc_frm_parent_db_id'],
                    $data['letters_doc_frm_user1_status_data'],
                    $data['letters_doc_frm_user2_status_data'],
                    $data['letters_doc_frm_user3_status_data'],
                    $data['letters_doc_frm_user1_status_date_data'],
                    $data['letters_doc_frm_user2_status_date_data'],
                    $data['letters_doc_frm_user3_status_date_data'],
                    $data['letters_doc_frm_user_1_section'], 
                    $data['letters_doc_frm_user_2_section'], 
                    $data['letters_doc_frm_user_3_section'],
                    $data['letters_doc_frm_withoutourdoc']
                  );

        return $id;
    }

    /**
     * ���������� ������ �������
     *
     * @param    string    $title    ��������
     * @return   integer             ID �������
    */
    function addTemplate($title) {
        global $DB;
        $sql = "INSERT INTO letters_template(title) VALUES(?) RETURNING id";
        $id = $DB->val($sql, $title);
        return $id;
    }

    /**
     * ��������� ���������
     *
     * @param    integer  $id      ID ���������
     * @param    array    $data    ������ ���������
     */
    function updateDocument($id, $data) {
        global $DB;

        if(!$data['letters_doc_frm_user_3_db_id'] || $data['letters_doc_frm_user_3_db_id']=='null') {
            $data['letters_doc_frm_user_3_db_id'] = null;
            $data['letters_doc_frm_user3_status_data'] = null;
            $data['letters_doc_frm_user3_status_date_data'] = null;
        }
        if(!$data['letters_doc_frm_user3_status_date_data']) $data['letters_doc_frm_user3_status_date_data'] = null;
        if(!$data['letters_doc_frm_user2_status_date_data']) $data['letters_doc_frm_user2_status_date_data'] = null;
        if(!$data['letters_doc_frm_user1_status_date_data']) $data['letters_doc_frm_user1_status_date_data'] = null;
        if(!$data['letters_doc_frm_user3_status_data']) $data['letters_doc_frm_user3_status_data'] = null;
        if(!$data['letters_doc_frm_user2_status_data']) $data['letters_doc_frm_user2_status_data'] = null;
        if(!$data['letters_doc_frm_user1_status_data']) $data['letters_doc_frm_user1_status_data'] = null;
        
        if(!$data['letters_doc_frm_parent_db_id'] || $data['letters_doc_frm_parent_db_id']=='null') $data['letters_doc_frm_parent_db_id'] = null;
        if(!$data['letters_doc_frm_group'] || $data['letters_doc_frm_group']=='null') $data['letters_doc_frm_group'] = null;
        if(!$data['letters_doc_frm_group_db_id'] || $data['letters_doc_frm_group_db_id']=='null') $data['letters_doc_frm_group_db_id'] = null;

        if(!$data['letters_doc_frm_group_db_id'] && 
           !empty($data['letters_doc_frm_group'])) {
            
            $data['letters_doc_frm_group_db_id'] = letters::checkCreateGroup($data['letters_doc_frm_group']);
        }
        
        if($data['letters_doc_frm_user_1_section']=='1') {
            $data['letters_doc_frm_user_1_section'] = true;
        } else {
            $data['letters_doc_frm_user_1_section'] = false;
        }
        if($data['letters_doc_frm_user_2_section']=='1') {
            $data['letters_doc_frm_user_2_section'] = true;
        } else {
            $data['letters_doc_frm_user_2_section'] = false;
        }
        if($data['letters_doc_frm_user_3_section']=='1') {
            $data['letters_doc_frm_user_3_section'] = true;
        } else {
            $data['letters_doc_frm_user_3_section'] = false;
        }
        if($data['letters_doc_frm_withoutourdoc']=='1') {
            $data['letters_doc_frm_withoutourdoc'] = true;
        } else {
            $data['letters_doc_frm_withoutourdoc'] = false;
        }

        $doc = self::getDocument($id);

        $doc_data['title'] = $data['letters_doc_frm_title'];
        $doc_data['user_1'] = $data['letters_doc_frm_user_1_db_id'];
        $doc_data['user_2'] = $data['letters_doc_frm_user_2_db_id'];
        $doc_data['user_3'] = $data['letters_doc_frm_user_3_db_id'];
        $doc_data['group_id'] = $data['letters_doc_frm_group_db_id'];
        $doc_data['parent'] = $data['letters_doc_frm_parent_db_id'];
        $doc_data['user_status_1'] = $data['letters_doc_frm_user1_status_data'];
        $doc_data['user_status_2'] = $data['letters_doc_frm_user2_status_data'];
        $doc_data['user_status_3'] = $data['letters_doc_frm_user3_status_data'];
        $doc_data['user_status_date_1'] = $data['letters_doc_frm_user1_status_date_data'];
        $doc_data['user_status_date_2'] = $data['letters_doc_frm_user2_status_date_data'];
        $doc_data['user_status_date_3'] = $data['letters_doc_frm_user3_status_date_data'];
        $doc_data['is_user_1_company'] = ($data['letters_doc_frm_user_1_section'] ? 't' : 'f');
        $doc_data['is_user_2_company'] = ($data['letters_doc_frm_user_2_section'] ? 't' : 'f');
        $doc_data['is_user_3_company'] = ($data['letters_doc_frm_user_3_section'] ? 't' : 'f');
        $doc_data['withoutourdoc'] = ($data['withoutourdoc'] ? 't' : 'f');
        

        if (isset($data['letters_doc_frm_comment']) && 
            $data['letters_doc_frm_comment']) {
            
            $doc_data['comment'] = $data['letters_doc_frm_comment'];
        } else {
            $data['letters_doc_frm_comment'] = $doc['comment'];
        }
        
        if($doc_data['user_status_1']!=$doc['user_status_1'] || $doc_data['user_status_2']!=$doc['user_status_2'] || $doc_data['user_status_3']!=$doc['user_status_3']) {
            letters::updateDateStatusChange($id);
        }
        
        letters::saveHistory($id, $doc_data);

        $sql = "UPDATE letters SET
                                     date_add = ".($data['letters_doc_frm_dateadd_eng_format'] ? "'{$data['letters_doc_frm_dateadd_eng_format']}'" : "NOW()").",
                                     title = ?,
                                     user_1 = ?,
                                     user_2 = ?,
                                     user_3 = ?,
                                     group_id = ?,
                                     parent = ?,
                                     user_status_1 = ?, 
                                     user_status_2 = ?, 
                                     user_status_3 = ?, 
                                     user_status_date_1 = ?, 
                                     user_status_date_2 = ?, 
                                     user_status_date_3 = ?,
                                     is_user_1_company = ?,  
                                     is_user_2_company = ?, 
                                     is_user_3_company = ?, 
                                     withoutourdoc = ?,
                                     comment = ?
                WHERE id = ?i;";
        $DB->query($sql, 
                    $data['letters_doc_frm_title'],
                    $data['letters_doc_frm_user_1_db_id'],
                    $data['letters_doc_frm_user_2_db_id'],
                    $data['letters_doc_frm_user_3_db_id'],
                    $data['letters_doc_frm_group_db_id'],
                    $data['letters_doc_frm_parent_db_id'],
                    $data['letters_doc_frm_user1_status_data'],
                    $data['letters_doc_frm_user2_status_data'],
                    $data['letters_doc_frm_user3_status_data'],
                    $data['letters_doc_frm_user1_status_date_data'],
                    $data['letters_doc_frm_user2_status_date_data'],
                    $data['letters_doc_frm_user3_status_date_data'],
                    $data['letters_doc_frm_user_1_section'],
                    $data['letters_doc_frm_user_2_section'],
                    $data['letters_doc_frm_user_3_section'],
                    $data['letters_doc_frm_withoutourdoc'],
                    $data['letters_doc_frm_comment'],
                    $id
                  );

        $sql = "UPDATE letters SET is_out=false WHERE (user_status_1 IS DISTINCT FROM 1 AND user_status_2 IS DISTINCT FROM 1 AND user_status_3 IS DISTINCT FROM 1) AND id=?i";
        $DB->query($sql, $id);

            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
            $attachedfiles = new attachedfiles($data['attachedfiles_session']);
            $attachedfiles_files = $attachedfiles->getFiles();

            if($attachedfiles_files) {
                foreach($attachedfiles_files as $attachedfiles_file) {
                    $cFile = new CFile();
                    $cFile->table = 'file';
                    $cFile->GetInfoById($attachedfiles_file['id']);
                    if($cFile->id!=$doc['file_id']) {
                        $ext = $cFile->getext();
                        $tmp_dir = "letters/";
                        $tmp_name = $cFile->secure_tmpname($tmp_dir, '.'.$ext);
                        $tmp_name = substr_replace($tmp_name,"",0,strlen($tmp_dir));
                        $cFile->_remoteCopy($tmp_dir.$tmp_name, true);
                        $sql = "UPDATE letters SET file_id = ?i WHERE id = ?i";
                        $DB->query($sql, $cFile->id, intval($id));
                        $cFile->delete($doc['file_id']);
                    }
                }
            }
            $attachedfiles->clear();

        return $id;
    }

    /**
     * �������� ������� ��������� ���������
     *
     * @param    integer    $id          ID ���������
     * @return   array                   ������� ���������
     */
    function getHistory($id) {
        global $DB;
        $sql = "SELECT h.*, u.login as user_login, u.uname as user_uname, u.usurname as user_usurname FROM letters_history h INNER JOIN users u ON u.uid=h.user_id WHERE doc_id=?i ORDER BY change_date ASC;";
        $history = $DB->rows($sql, $id);
        return $history;
    }

    /**
     * ���������� ���� ��������� �������
     *
     * @param    integer    $id          ID ���������
     */
    function updateDateStatusChange($id) {
        global $DB;
        $sql = "UPDATE letters SET date_change_status = NOW() WHERE id = ?i;";
        $DB->query($sql, $id);
    }

    /**
     * ������ ������� ��������� ���������
     *
     * @param    integer    $id          ID ���������
     * @param    array      $new_data    ����� ������ ���������
     */
    function saveHistory($id, $new_data) {
        global $DB;
        $old_data = letters::getDocument($id);
        $changed = array();
        if($new_data) {
            foreach($new_data as $key => $new_val) {
                if($key=='user_status_date_1' || $key=='user_status_date_2' || $key=='user_status_date_3') {
                    $old_data = preg_replace("/ .*$/","",$old_data);
                }
                if($key=='is_user_1_company' || $key=='is_user_2_company' || $key=='is_user_3_company') continue;
                if($key=='user_1' || $key=='user_2' || $key=='user_3') {
                    $oldIsCompany = ($old_data["is_{$key}_company"] === 't') ? 't' : 'f'; // ����������� �� null
                    $newIsCompany = ($new_data["is_{$key}_company"] === 't') ? 't' : 'f';
                    if($new_val!=$old_data[$key] || (($new_val || $old_data[$key]) && $newIsCompany != $oldIsCompany)) {
                        $changed[] = $key;
                    }
                } else {
                    if($new_val!=$old_data[$key]) {
                        $changed[] = $key;
                    }
                }
            }
        }
        if($changed) {
            $qgroups = $DB->rows("SELECT * FROM letters_group");
            foreach($qgroups as $v) {
                $groups[$v['id']] = $v['title'];
            }
            $qdeliveries = $DB->rows("SELECT * FROM letters_delivery");
            foreach($qdeliveries as $v) {
                $deliveries[$v['id']] = $v['title'];
            }
            $qstatuses = $DB->rows("SELECT * FROM letters_status");
            foreach($qstatuses as $v) {
                $statuses[$v['id']] = $v['title'];
            }
            $statuses[0] = '�� �������';

            foreach($changed as $change_type) {
                switch($change_type) {
                    case 'title':
                        $type_field = 1;
                        $old_val = $old_data['title'];
                        $new_val = $new_data['title'];
                        break;
                    case 'group_id':
                        $type_field = 2;
                        $old_val = $groups[$old_data['group_id']];
                        $new_val = $groups[$new_data['group_id']];
                        break;
                    case 'user_1':
                        $type_field = 3;
                        $old_val = $old_data['user_1'].'-'.($old_data['is_user_1_company'] === 't' ? '1' : '0');
                        $new_val = $new_data['user_1'].'-'.($new_data['is_user_1_company'] === 't' ? '1' : '0');
                        break;
                    case 'user_2':
                        $type_field = 4;
                        $old_val = $old_data['user_2'].'-'.($old_data['is_user_2_company'] === 't' ? '1' : '0');
                        $new_val = $new_data['user_2'].'-'.($new_data['is_user_2_company'] === 't' ? '1' : '0');
                        break;
                    case 'user_3':
                        $type_field = 5;
                        $old_val = $old_data['user_3'].'-'.($old_data['is_user_3_company'] === 't' ? '1' : '0');
                        $new_val = $new_data['user_3'].'-'.($new_data['is_user_3_company'] === 't' ? '1' : '0');
                        break;
                    case 'user_status_1':
                    case 'user_status_date_1':
                        $type_field = 6;
                        $old_val = $statuses[$old_data['user_status_1']];
                        $new_val = $statuses[$new_data['user_status_1']];
                        if($old_data['user_status_1']==2 || $old_data['user_status_1']==3) {
                            $old_val .= ' '.dateFormat("d.m.Y", $old_data['user_status_date_1']);
                        }
                        if($new_data['user_status_1']==2 || $new_data['user_status_1']==3) {
                            $new_val .= ' '.dateFormat("d.m.Y", $new_data['user_status_date_1']);
                        }
                        break;
                    case 'user_status_2':
                    case 'user_status_date_2':
                        $type_field = 7;
                        $old_val = $statuses[$old_data['user_status_2']];
                        $new_val = $statuses[$new_data['user_status_2']];
                        if($old_data['user_status_2']==2 || $old_data['user_status_2']==3) {
                            $old_val .= ' '.dateFormat("d.m.Y", $old_data['user_status_date_2']);
                        }
                        if($new_data['user_status_2']==2 || $new_data['user_status_2']==3) {
                            $new_val .= ' '.dateFormat("d.m.Y", $new_data['user_status_date_2']);
                        }
                        break;
                    case 'user_status_3':
                    case 'user_status_date_3':
                        $type_field = 8;
                        $old_val = $statuses[$old_data['user_status_3']];
                        $new_val = $statuses[$new_data['user_status_3']];
                        if($old_data['user_status_3']==2 || $old_data['user_status_3']==3) {
                            $old_val .= ' '.dateFormat("d.m.Y", $old_data['user_status_date_3']);
                        }
                        if($new_data['user_status_3']==2 || $new_data['user_status_3']==3) {
                            $new_val .= ' '.dateFormat("d.m.Y", $new_data['user_status_date_3']);
                        }
                        break;
                    case 'delivery':
                        $type_field = 9;
                        $old_val = $deliveries[$old_data['delivery']];
                        $new_val = $deliveries[$new_data['delivery']];
                        break;
                    case 'delivery_cost':
                        $type_field = 10;
                        $old_val = $old_data['delivery_cost'];
                        $new_val = $new_data['delivery_cost'];
                        break;
                    case 'parent':
                        $type_field = 11;
                        if($old_data['parent']) {
                            $old_doc = letters::getDocument($old_data['parent']);
                            $old_val = "ID{$old_doc['id']} {$old_doc['title']}";
                        } else {
                            $old_val = "���";
                        }
                        if($new_data['parent']) {
                            $new_doc = letters::getDocument($new_data['parent']);
                            $new_val = "ID{$new_doc['id']} {$new_doc['title']}";
                        } else {
                            $new_val = "���";
                        }
                        break;
                    case 'comment':
                        $type_field = 12;
                        $old_val = $old_data['comment'];
                        $new_val = $new_data['comment'];
                        break;
                    default:
                        $type_field = null;
                        break;
                }
                
                if($type_field) {
                    $sql = "INSERT INTO letters_history(
                                                        doc_id, 
                                                        type_field, 
                                                        val_old, 
                                                        val_new, 
                                                        change_date, 
                                                        user_id
                                                       ) VALUES (
                                                        ?i,
                                                        ?i,
                                                        ?,
                                                        ?,
                                                        NOW(),
                                                        ?i
                                                       ); ";
                    $DB->query($sql, $id, $type_field, $old_val, $new_val, get_uid(false));
                }
            }
        }
    }

    /**
     * �������� ����� � ������� ���� ��������
     *
     * @return    ������ ����
     */
    function getCompaniesSymbols() {
        global $DB;
        $en = "abcdefghijklmnopqrstuvwxyz";
        $rus = "����������������������������";

        $sql = "SELECT DISTINCT(lower(substring(name from 1 for 1))) as sym FROM letters_company ORDER BY sym";
        $res = $DB->rows($sql);
        $ret['ru'] = array();
        $ret['en'] = array();
        if($res) {
            foreach($res as $sym) {
                if(stripos($rus, $sym['sym'])!==FALSE) {
                    array_push($ret['ru'], $sym['sym']);
                } elseif(stripos($en, $sym['sym'])!==FALSE) {
                    array_push($ret['en'], $sym['sym']);
                } else {
                    $ret['num'] = 1;
                }
            }
        }
        return $ret;
    }

    /**
     * ������� �������� ������� ���������� �� ������������ �����
     *
     * @param    string     $s           �����
     * @return   array                   ������ ��������
     */
    function getCompaniesBySym($s) {
        global $DB;

        if($s=='#') {
            $sql = "SELECT letters_company.*, country.country_name AS country_title, city.city_name AS city_title FROM letters_company LEFT JOIN country ON country.id=letters_company.country LEFT JOIN city ON city.id=letters_company.city WHERE lower(name) NOT SIMILAR TO '(�|�|�|�|�|�|�|�|�|�|�|�|�|�|�|�|�|�|�|�|�|�|�|�|�|�|�|�|a|b|c|d|e|f|g|h|i|j|k|l|m|n|o|p|q|r|s|t|u|v|w|x|y|z)%'";
        } else {
            $sql = "SELECT letters_company.*, country.country_name AS country_title, city.city_name AS city_title FROM letters_company LEFT JOIN country ON country.id=letters_company.country LEFT JOIN city ON city.id=letters_company.city WHERE lower(name) ILIKE '{$s}%' ORDER BY name";
        }
        return $DB->rows($sql);
    }

    /**
     * ���������� ��������
     *
     * @param    array    $frm    ������ � ��������
     */
    function addCompany($frm) {
        global $DB;
        $sql = "INSERT INTO letters_company(name, country, city, index, address, fio, frm_type) VALUES(?, ?i, ?i, ?, ?, ?, ?) RETURNING id";
        return $DB->val($sql, $frm['frm_company_name'], $frm['country_columns'][0], $frm['country_columns'][1], $frm['frm_company_index'], $frm['frm_company_address'], $frm['frm_company_fio'], $frm['frm_company_type']);
    }

    /**
     * ��������� ��������
     *
     * @param    array    $frm    ������ � ��������
     */
    function updateCompany($frm) {
        global $DB;
        $sql = "UPDATE letters_company SET
                    name = ?, 
                    country = ?i, 
                    city = ?i, 
                    index = ?, 
                    address = ?, 
                    fio = ?, 
                    frm_type = ? 
                    WHERE id = ?i";
        $DB->query($sql, $frm['frm_company_name'], $frm['country_columns'][0], $frm['country_columns'][1], $frm['frm_company_index'], $frm['frm_company_address'], $frm['frm_company_fio'], $frm['frm_company_type'], $frm['frm_company_id']);
    }

    /**
     * �������� ������ � ��������
     *
     * @param    integer    $id     ID ��������
     * @return   array              ���������� � ��������
     */
    function getCompany($id) {
        global $DB;
        $sql = "SELECT c.*, country.country_name as country_title, city.city_name as city_title   FROM letters_company c LEFT JOIN country ON country.id=c.country LEFT JOIN city ON city.id=c.city WHERE c.id=?i";
        return $DB->row($sql, $id);
    }
    
    
    /**
     * �������� ������ � ������������. ������� ��� sbr_meta::getUserReqvs()
     * 
     * @param  type $uid  uid ������������
     * @return array      ������ ������������
     */
    function getUserReqvs($uid) {
        $user = sbr_meta::getUserReqvs($uid);
        if ( $user && $user['form_type'] == 2 && trim($user[2]['address']) == '' ) {
            if ( trim($user[2]['address_fct']) != '' ) {
                $user[2]['address'] = trim($user[2]['address_fct']);
            } else if ( trim($user[2]['address_jry']) != '' ) {
                $user[2]['address'] = trim($user[2]['address_jry']);
            }
        }
        return $user;
    }

    /**
     * �������� ������ ��������
     *
     * @return array    ���������� � ��������
     */
    function getTemplatesList() {
        global $DB;
        $sql = "SELECT * FROM letters_template ORDER BY id DESC";
        return $DB->rows($sql);
    }
    
    
    
    /**
     * ����� ID �������� �� ��������� 
     * � ��������� ������� ������
     * 
     * @global type $DB
     * @param type $data
     * @return boolean/int
     */
    public function findCompanyId($data)
    {
        global $DB;
        
        if (empty($data)) {
            return false;
        }
        
        $where_list = array();
        foreach ($data as $key => $value) {
            if (empty($value)) {
                continue;
            }
            
            $where_list[] = $DB->parse("({$key} = ?)", $value);
        }
        
        if (empty($where_list)) {
            return false;
        }
        
        $where = implode(' AND ', $where_list);
        return $DB->val("SELECT id FROM letters_company WHERE {$where} LIMIT 1");
    }
    
    
}