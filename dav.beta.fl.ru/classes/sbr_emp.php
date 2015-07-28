<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';

/**
 * ����� ��� ������ � ��� �� ������� ������������. �.�. ����� ��������� �������� �� ������������ ������ ������ ���� ������ ����� ����. �� �� � ����������.
 */
class sbr_emp extends sbr
{
    public $uid_col = 'emp_id';
    public $anti_uid_col = 'frl_id';
    public $anti_tbl = 'freelancer';
    public $upfx = 'emp_';
    public $apfx = 'frl_';
    public $uclass = 'employer';


    /**
     * ������ ��� �������� ���������� �����, ��������������� ����� ���� (self::FT_JURI|self::FT_PHYS).
     * @var array
     */
    public $reqv = array();

    /**
     * ����� � ��������������.
     * @var array
     */
    public $reserve_sum;

    /**
     * ��������� ����� � �������� ��������������.
     * @var array
     */
    private $_delstages;




    /**
     * ��������� ���� �� � ������������ ���������.
     * @return boolean   ����/���.
     */
    function draftExists() {
        $sql = "SELECT 1 FROM sbr WHERE emp_id = ?i AND is_draft = true LIMIT 1";
        $sql = $this->db()->parse($sql, $this->uid);
        if($res = pg_query(self::connect(), $sql))
            return !!pg_num_rows($res);
        return false;
    }

    /**
     * ���������, ����� �� �� ������� ������� (������� projects) ������ ���.
     * �������������� $this->projects ����������� � �������.
     * 
     * @param integer $project_id   ��. �������.
     * @return array   ���������� � �������.
     */
    function checkProject($project_id) {
        $project_id = intvalPgSql($project_id);
        if($this->project && $this->project['id'] == $project_id)
            return $this->project;
        if($this->project = new_projects::getPrj($project_id)) {
            if($this->project['uid'] != $this->uid)
                $this->error['project_id'] = '�� �� ������ ������ ����������� ������ � ����� �������';
            else if($this->project['is_blocked']=='t')
                $this->error['project_id'] = '������ ������������! ���������� ������ ����������� ������ � ��������������� �������';
            else if($this->project['no_risk']=='t') // !!! ������� ������� + ��������� �������� �� ���� � ������ ���
                $this->error['project_id'] = '������ ������ ��� ��������� � ������ ����������� ������'; // !!! ���, �����, ������ �� ����������� � �������...
            elseif ($this->project['kind'] == 7 && !$this->project['exec_id']) { // ���� ��������� ������ �� ��������, �� ����������� ������ ���� ������ ����������
                $this->error['project_id'] = '������ ����� ������� ����������';
            } else {
                if($this->project['attach'] = projects::getAllAttach($this->project['id']))
                    array_walk($this->project['attach'], create_function('&$m, $k', '$m["source_type"] = '.sbr_stages::ATTACH_SOURCE_PRJ.';'));
            }
        }
        else {
            $this->error['project_id'] = '������ �� ������';
        }
        //print_r($this->error);
        if($this->error)
            $this->project = NULL;
        return $this->project;
    }

    /**
     * �������������� ����� ����� ��� �� ��������� ������� (�� ������� projects).
     * @param integer $project_id   ��. �������.
     * @param inetger $exec_id      �� ����������� ���
     */
    function initFromProject($project_id, $exec_id = false) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/professions.php';
        $this->checkProject($project_id);
        if(!$this->error['project_id']) {
            $this->data['project_id'] = $this->project['id'];
            $this->data['name'] = $this->project['name'];
            $this->data['frl_id'] =  $exec_id ? $exec_id : $this->project['exec_id'];
            $stage = new sbr_stages($this);
            $this->stages = array($stage);
            $stage->data['name'] = $this->project['name'];
            $stage->data['descr'] = $this->project['descr'];

            $allSpecs = projects::getProjectCategories($this->project['id']);
            $this->data['professions'] = $allSpecs;
            foreach ($this->data['professions'] as &$spec) {
                $spec['prof_name'] = $spec['subcategory_id'] ? professions::GetProfNameWP($spec['subcategory_id'], ': ', '', false) : professions::GetGroupName($spec['category_id']);
            }
            unset($spec);
            
            $stage->data['category'] = $allSpecs[0]['category_id'];//$this->project['category'];
            $stage->data['sub_category'] = $allSpecs[0]['subcategory_id'];//$this->project['subcategory'];
            $cost = $this->project['cost'];
            $cex = array(project_exrates::USD, project_exrates::EUR, project_exrates::RUR, project_exrates::FM); // ��������� ���� projects.currency � ���� project_exrates.
            $ccex = $cex[$this->project['currency']];
            switch($ccex) {
                case project_exrates::RUR : $cost_sys = exrates::BANK; break;
                case project_exrates::FM  :
                case project_exrates::EUR :
                case project_exrates::USD :
                    $prj_exrates = project_exrates::GetAll(false);
                    $cost_sys = exrates::BANK;
                    $cost *= $prj_exrates[$ccex.project_exrates::RUR];
                    break;

            }
            $stage->data['cost'] = (int)$cost === 0 ? "" : $cost;
            $this->data['cost_sys'] = $cost_sys;
            if($this->project['attach']) {
                $stage->data['attach'] = $this->project['attach'];
            }
            
            return true;
        } 
        
        return false;
    }

    
    
    
    
    
    /**
     * �������� ����� �� �� ������ ������� ������ ������ ��� ������
     * ���������� ��������� �������
     * 
     * @global type $DB
     * @param type $service_id
     * @return type
     */
    function checkTService($service_id, $user_id = 0)
    {
        global $DB;

        $sql = "
            SELECT 
                s.id,
                s.user_id,
                s.title,
                s.price,
                s.days,
                s.category_id,
                s.description,
                s.requirement,
                s.extra,
                s.is_express,
                s.express_price,
                s.express_days,
                c1.pid AS subcategory_id,
                c1.title AS subcategory_title,
                c2.gid AS category_id,
                c2.title AS category_title
            FROM tservices AS s 
            LEFT JOIN users AS u ON u.uid = s.user_id 
            LEFT JOIN tservices_categories AS c1 ON c1.id = s.category_id  
            LEFT JOIN tservices_categories AS c2 ON c2.id = c1.parent_id 
            LEFT JOIN tservices_blocked AS sb ON sb.src_id = s.id 
            WHERE 
                sb.src_id IS NULL AND 
                s.active = TRUE AND 
                s.deleted = FALSE AND 
                u.is_banned = B'0' AND 
                u.self_deleted = FALSE AND 
                s.id = ?i" . (($user_id > 0)?" AND u.uid = {$user_id}":""); 
        
        return $DB->row($sql, $service_id);
    }

    
    
    /**
     * ���������� ��� ��� �������� �� ������� id
     * 
     * @param type $service_id
     * @return type
     */
    function tserviceHash($service_id)
    {
        return md5( 'C}U5BOQLXG9f' . $service_id );
    }

    
    /**
     * ��������� ����� ������ � ������� �������
     * 
     * @param type $stage_id
     * @return boolean
     */
    function addRefTService($stage_id)
    {
        if(!isset($this->data['tservice_id']) || $this->data['tservice_id'] <= 0 || $stage_id <= 0)
            return false;
        
        $sql = "INSERT INTO tservices_sbr(service_id, sbr_id, stage_id) VALUES (?i, ?i, ?i);";
        $sql = $this->db()->parse($sql, $this->data['tservice_id'], $this->data['id'], $stage_id);
            
        if(!($res = pg_query(self::connect(false), $sql))) {
            $this->_abortXact();
            return false;
        }    

        return true;
    }


    /**
     * �������������� ����� ����� ��� �� ������� ������ (������� tservices)
     * 
     * 
     * @global type $DB
     * @param type $service_id
     * @param type $request
     * @return boolean
     */
    function initFromTService($service_id, $request)
    {
        if($service = $this->checkTService($service_id))
        {
            $cost_format = function($cost){return str_replace(',00', '', number_format(round($cost, 2), 2, ',', ' ')) . ' p.';};
            
            $this->data['tservice_id'] = $service['id'];
            //��� ����� �� ��������� ������ ��������
            $this->data['tservice_hash'] = $this->tserviceHash($service['id']);
            
            $this->data['name'] = $service['title'];
            $this->data['frl_id'] = $service['user_id'];

            
            $stage = new sbr_stages($this);
            $this->stages = array($stage);
            
            $this->data['cost_sys'] = exrates::BANK;
            
            //�����������
            $stage->data['work_days'] = intval($service['days']);
            $stage->data['cost'] = intval($service['price']);
            
            //��������
            $stage->data['name'] = $service['title'];
            $stage->data['descr'] = "��� �� ��������:\n" . 
                                    $service['description'] . 
                                    "\n\n��� �����, ����� ������:\n" . 
                                    $service['requirement'] . 
                                    "\n\n��������� ������� ������: " . 
                                    $cost_format($service['price']);
            
            
            //$stage->data['category'] = $service['category_id'];
            //$stage->data['sub_category'] = $service['subcategory_id'];
            $this->data['professions'][] = array(
                'category_id'       => $service['category_id'],
                'subcategory_id'    => $service['subcategory_id'],
                'prof_name'         => ($service['category_id'])?$service['category_title'] . ': ' . 
                                                                 $service['subcategory_title'] :
                                                                 $service['subcategory_title']
            );
            

            

            $is_express = FALSE;
            //���� ������ �������� �������� �� ���������
            if(isset($request['is_express']) && 
               $request['is_express'] == 1 && 
               $service['is_express'] == 't')
            {
                $stage->data['work_days'] = intval($service['express_days']);
                $stage->data['cost'] += intval($service['express_price']);
                
                $is_express = TRUE;
            }            
            
            
            $extras = $service['extra'];
            $is_extras = FALSE;
            //���� ������ ��������� �������������� �����
            if(isset($request['extra']) && 
               is_array($request['extra']) && 
               $extras)
            {
                $extras = unserialize(iconv('CP1251', 'UTF-8//IGNORE', $service['extra']));
                $_txt = '';
                $_days = 0;
                $_cost = 0;
                
                if(count($extras)) 
                foreach($request['extra'] as $_value)
                {
                    $_value = intval($_value);
                    if(!isset($extras[$_value])) continue;
                    
                    $_extra = $extras[$_value];
                    
                    $_price = intval($_extra['price']);
                    $is_negative = ($_price < 0);
                    $_price = abs($_price);                    

                    $_txt_days = ($_extra['days'] > 0 && !$is_express)?
                            '+ ' . $_extra['days'] . ' ' . ending($_extra['days'], '����', '���', '����') . ' � ' . (($is_negative)?'- ':''):
                            '� ��� �� ���� '.(($is_negative)?'- ':'+ ');
                    
                    $_txt_price = $cost_format($_price);

                    $_txt_title = iconv('UTF-8', 'CP1251', $_extra['title']);
                    
                    $_txt .= "\n" . $_txt_title . " (" . $_txt_days . $_txt_price . ");";
                    $_days += intval($_extra['days']);
                    $_cost += intval($_extra['price']);
                }
                
                if(!empty($_txt)) 
                {
                    $stage->data['descr'] .= "\n\n�������������:" . $_txt;
                    if(!$is_express) $stage->data['work_days'] += $_days;
                    $stage->data['cost'] += $_cost;
                    
                    $is_extras = TRUE;
                }
            }
            
            //����������� ����� �� ��������� ��� �����������
            if($is_express)
            {
                $stage->data['descr'] .= "\n\n���������: + " . $cost_format($service['express_price']);
            }

            
            //�����
            if($is_extras || $is_express) $stage->data['descr'] .= "\n\n�����: " . $cost_format($stage->data['cost']);
            //������ �����
            $stage->data['descr'] .= "\n����: " . $stage->data['work_days'] . ' ' . ending($stage->data['work_days'], '����', '���', '����');
            

            return true;
        }
        else
        {
            //��� � �������� ��� �� ��������������?
            $this->error['project_id'] = '������� ������ �� �������';
        }
        


        return false;     
    }












    /**
     * ��������� �������� �� ������� ������ ����������.
     * @return boolean   ��/���.
     */
    function isDraft() {
        return $this->data['is_draft'] == 't';
    }

    /**
     * ��������� ���������������� ������ �� ��������/�������������� ���. �������������� ������ � ������������ � ����������� ����������.
     * 
     * @param array $request   ������ ������� (���, ����).
     * @param array $files   ������ $_FILES � ���������� � ������� �����.
     * @param sbr $old   ������ ��������� ������. ���������� ��� ��������������.
     * @return boolean   ��� ������?
     */
    function initFromRequest($request, $files = NULL, $old = NULL) {
        $this->data['is_draft'] = $request['draft'] ? 't' : 'f';
        if(!isset($request['frl_login_added']))
            $request['frl_login_added'] = '';
        if($request['project_id'])
            $this->checkProject($request['project_id']);
        if(!isset($request['scheme_type']))
            $request['scheme_type'] = '';

        if($old) {
            if(!$request['cost_sys']) $request['cost_sys'] = array($old->cost_sys);
            if(!$request['scheme_type']) $request['scheme_type'] = $old->scheme_type;
        }

        foreach($request as $field=>$value) {
            if(is_scalar($value)) {
                $value = trim(stripslashes($value));
            }
            switch($field) {
                
                case 'name' :
                    if(is_empty_html($value))
                        $this->error[$field] = '����������, ��������� ��� ����';
                    $value = substr($value, 0, self::NAME_LENGTH);
                    break;

                case 'frl_login_added' :
                    if(!$this->isDraft()) {
                        if(!$value || $value=='�����')
                            $this->error['frl_login'] = '���������� �������� �����������';
                    }
                    if(!$this->error['frl_login']) {
                        $frl = new freelancer();


                        $this->data['frl_id'] = $frl->GetUid($err, $value);
                    }
                    break;

                case 'cost_sys' :
                    $value = is_array($value) ? (int)current($value) : NULL;
                    $reqvs = $this->getUserReqvs();
                    if( !in_array($value, array(exrates::YM, exrates::WMR, exrates::BANK)) ) {
                        $this->error['cost_sys_err'] = '������������ ������ ��������������';
                        $value = exrates::BANK;
                    }
                    if(($value == exrates::YM || $value == exrates::WMR) && $reqvs['form_type'] == sbr::FT_JURI)
                        $this->error['cost_sys_err'] = '�������������� ����� ������.������ ��� WebMoney �������� ������ ���������� �����';
                    break;

                case 'project_id' :
                    $value = $this->project['id'];
                    break;

                case 'id' :
                    $value = intvalPgSql($value);
                    break;

                case 'scheme_type' :
                    $value = intvalPgSql($value);
                    if($value && (!self::$scheme_types[$value] || $value == self::SCHEME_OLD && (!$old || $old->scheme_type != self::SCHEME_OLD))) {
                        $this->error['scheme_type_err'] = '�������� ��� ��������';
                    }
                    break;

                case 'version' :
                    $value = (int)$value > 32767 ? 32767 : (int)$value;
                    break;

                case 'frl_refuse_reason' :
                    $value = substr($value, 0, self::SBR_REASONS_LENGTH);
                    break;

                default :
                    break;
            }

            $this->data[$field] = $value;
        }

        if($request['stages']) {
            $this->_initStagesFromRequest($request['stages'], $files['stages'], ($request['action']=='create' ? 0 : ($request['action']=='edit' ? 1 : 2)));

            $this->getUserReqvs();
            if($this->user_reqvs['rez_type']==sbr::RT_UABYKZ) {
                $cost = 0;
                foreach($this->stages as $s)
                    $cost += $s->cost;
                $cost_rur = $cost * $this->cost2rur();
                if($cost_rur > $this->maxNorezCost())
                    $this->error['cost_sys_err_tbl'] = '��������� ������������ ����� ������ &mdash; ' . sbr::MAX_COST_USD . ' USD (��� ' . sbr_meta::view_cost($this->maxNoRezCost(), exrates::BANK) .')';
            }

        }

        return ( !$this->error );
    }
    
    /**
     * ���� �� �� ������ ������ � ������ ������� ��� ����������
     * 
     * @param integer $id   �� ������
     * @return object sbr_stages
     */
    function getStageByIdForData($id) {
        foreach($this->stages as $pos => $stage) {
            if($stage->data['id'] == $id) return $stage;
        }
    }

    /**
     * ����� ���
     * ��������� ���������������� ������ �� ��������/�������������� ���. �������������� ������ � ������������ � ����������� ����������.
     * 
     * @param array $request   ������ ������� (���, ����).
     * @param array $files   ������ $_FILES � ���������� � ������� �����.
     * @param sbr $old   ������ ��������� ������. ���������� ��� ��������������.
     * @return boolean   ��� ������?
     */
    function _new_initFromRequest($request, $files = NULL, $old = NULL) {
        $is_now_draft = $this->data['is_draft'];
        $this->data['is_draft'] = $request['draft'] ? 't' : 'f';
        if(!isset($request['frl_db_id']))
            $request['frl_db_id'] = '';
        $request['frl_db_id'] = intval($request['frl_db_id']);
        
        if(isset($request['tuid'],$request['tuhash']))
        {
            if($request['tuhash'] === $this->tserviceHash($request['tuid']))
            {
                $service = $this->checkTService($request['tuid'], $request['frl_db_id']); 
                if($service)
                {
                    $this->data['tservice_id'] = $service['id'];
                    $this->data['tservice_hash'] = $this->tserviceHash($service['id']);
                }
            }
        }
        else if($request['project_id'])
                $this->checkProject($request['project_id']);

        // ���� ������ ��������� �� ��������, �� ������������ ����� ���� ������ ���������� ��������
        if ($request['project_id'] && isset($request['frl_db_id']) && $this->project['kind'] == 7 && $this->project['exec_id'] != $request['frl_db_id']) {
            $request['frl_db_id'] = $this->project['exec_id'];
            $this->error['frl'] = "������������ ���� ������ ����� ���� ������ ���������� ��������";
        }

        
        
        
        if(!isset($request['scheme_type']))
            $request['scheme_type'] = '';

        if($old) {
            if(!$request['cost_sys']) $request['cost_sys'] = $old->cost_sys;
            if(!$request['scheme_type']) $request['scheme_type'] = $old->scheme_type;
        }

        foreach($request as $field=>$value) {
            if(is_scalar($value)) {
                $value = trim(stripslashes($value));
            }
            switch($field) {
                case 'stages':
                    foreach($value as $pos=>$stage) {
                        $nowStage = $this->getStageByIdForData($stage['id']);
                        $value[$pos]['descr'] = htmlspecialchars($stage['descr']);
                        if( ($request['scheme_type'] == sbr::SCHEME_PDRD || $request['scheme_type'] == sbr::SCHEME_PDRD2 ) && $stage['cost'] < sbr_stages::MIN_COST_RUR_PDRD) {
                            $this->error['cost'][$nowStage->data['num']] = '���� ���������� ����������';
                        }

                        if($request['scheme_type'] == sbr::SCHEME_LC && $is_now_draft != 't' && $stage['cost'] < sbr_stages::MIN_COST_RUR && $stage['cost'] != $nowStage->data['cost']) {
                            $this->error['cost'][$nowStage->data['num']] = '���� ���������� ����������';
                        }
                        if($request['scheme_type'] == sbr::SCHEME_LC && $is_now_draft == 't' && $stage['cost'] < sbr_stages::MIN_COST_RUR) {
                            $this->error['cost'][$nowStage->data['num']] = '���� ���������� ����������';
                        }
                        
                        if($this->data['reserved_id'] && (int) $stage['work_time_add'] == 0) {
                            $value[$pos]['work_time'] = $nowStage->data['work_time'];
                            $request[$field][$pos]['work_time'] = $nowStage->data['work_time'];
                        } else if($this->data['reserved_id'] && (int) $stage['work_time_add'] > 0) {
                            $start_time = $nowStage->data['start_time'] ? $nowStage->data['start_time'] : $nowStage->data['first_time'];
                            $endDate  = strtotime( $start_time . ' + ' . $nowStage->data['int_work_time'] . 'days');
                            $nextDate = $endDate < time() ? strtotime("+{$stage['work_time_add']} days") : strtotime(date('d.m.Y H:i', $endDate) . "+{$stage['work_time_add']} days");
                            $workTime = ceil( ( $nextDate - strtotime($start_time) ) / 86400 );
                            $value[$pos]['work_time'] = $workTime;
                            $request[$field][$pos]['work_time'] = $workTime; 
                        }
                    }
                    break;
                case 'name' :
                    if(is_empty_html($value))
                        $this->error[$field] = '����������, ��������� ��� ����';
                    $value = substr($value, 0, self::NAME_LENGTH);
                    break;

                case 'frl_db_id' :
                    if(!$this->isDraft()) {
                        if(!$value || $value=='�����')
                            $this->error['frl_db_id'] = '���������� �������� �����������';
                    }
                    if(!$this->error['frl_db_id'] && $value != '') {
                        $frl = new freelancer();
                        $frl->GetUserByUID($value);
                        if($frl->is_banned == 1) {
                            $this->error['frl_db_id'] = '������ ������������ ������������';
                            $this->error['frl_ban'] = '������ ������������ ������������';
                        }
                        $this->data['frl_id'] = $frl->uid;
                    }
                    break;
                    
                // �������������
                case 'profession0_db_id':
                    if (!$value) {
                        $this->error['profession0_db_id'] = '���������� ������� �������������';
                    }
                    break;

                case 'cost_sys' :
                    $reqvs = $this->getUserReqvs();
                    if( !in_array($value, array(exrates::YM, exrates::WMR, exrates::BANK)) ) {
                        $this->error['cost_sys_err'] = '������������ ������ ��������������';
                        $value = exrates::BANK;
                    }
                    if(($value == exrates::YM || $value == exrates::WMR) && $reqvs['form_type'] == sbr::FT_JURI)
                        $this->error['cost_sys_err'] = '�������������� ����� ������.������ ��� WebMoney �������� ������ ���������� �����';
                    break;

                case 'project_id' :
                    $value = $this->project['id'];
                    break;

                case 'id' :
                    $value = intvalPgSql($value);
                    break;

                case 'scheme_type' :
                    $value = intvalPgSql($value);
                    if($value && (!self::$scheme_types[$value] || $value == self::SCHEME_OLD && (!$old || $old->scheme_type != self::SCHEME_OLD))) {
                        $this->error['scheme_type_err'] = '�������� ��� ��������';
                    }
                    break;

                case 'version' :
                    $value = (int)$value > 32767 ? 32767 : (int)$value;
                    break;

                case 'frl_refuse_reason' :
                    $value = substr($value, 0, self::SBR_REASONS_LENGTH);
                    break;

                default :
                    break;
            }

            $this->data[$field] = $value;
        }
        
        $this->data['professions'] = array();
        if ($_POST['profession0']) {
            $this->data['professions'][] = array(
                'category_id' => __paramInit('int', null, 'profession0_column_id', 0),
                'subcategory_id' => __paramInit('int', null, 'profession0_db_id', 0),
                'prof_name' => __paramInit('string', null, 'profession0', 0),
            );
        }
        if ($_POST['profession1']) {
            $this->data['professions'][] = array(
                'category_id' => __paramInit('int', null, 'profession1_column_id', 0),
                'subcategory_id' => __paramInit('int', null, 'profession1_db_id', 0),
                'prof_name' => __paramInit('string', null, 'profession1', 0),
            );
        }
        if ($_POST['profession2']) {
            $this->data['professions'][] = array(
                'category_id' => __paramInit('int', null, 'profession2_column_id', 0),
                'subcategory_id' => __paramInit('int', null, 'profession2_db_id', 0),
                'prof_name' => __paramInit('string', null, 'profession2', 0),
            );
        }
        

        if($request['stages']) {
            foreach ($request['stages'] as $num => $stage) {
                if (isset($stage['attaches']) && is_array($stage['attaches'])) {
                    foreach ($stage['attaches'] as $anum => $att_id) {
                        if (!isset($files[$att_id])) continue;
    //                    $attached[$att_id]['id'] = md5($attached[$att_id]['id']);
                        if ($files[$att_id]['status'] == 1) {
                            $request['stages'][$num]['attached'][$anum] = $files[$att_id];
                        }
                    }
                }
            }
            if ($GLOBALS['action'] === 'editstage') {
                foreach ($files as $key => $file) {
                    if ($file['status'] == 4) {
                        $this->stages[0]->data['_new_del_attach'][] = $file;
                    }
                }
            }
            $this->_new_initStagesFromRequest($request['stages'], $files, ($request['action']=='create' ? 0 : ($request['action']=='edit' ? 1 : 2)));
            
            $this->getUserReqvs();
            $this->getFrlReqvs();
            if($this->user_reqvs['rez_type']==sbr::RT_UABYKZ || $this->frl_reqvs['rez_type'] == sbr::RT_UABYKZ) {
                $cost = 0;
                foreach($this->stages as $s)
                    $cost += $s->cost;
                $cost_rur = $cost * $this->cost2rur();
                if($cost_rur > $this->maxNorezCost()) {
                    foreach($this->stages as $p=>$s) {
                        $this->error['cost'][$pos] = '���� ���������� ����������';
                    }
                    $this->error['cost_sys_err_tbl'] = '��������� ������������ ����� ������ &mdash; ' . sbr::MAX_COST_USD . ' USD (��� ' . sbr_meta::view_cost($this->maxNoRezCost(), exrates::BANK) .')';
                }
            }

        }
        
//        var_dump($this->error);
//        die();

        return ( !$this->error );
    }

    /**
     * �������������� ����� ������ ��� ��������������/�������� �� ����������������� �������.
     * ��������� �������� �� ������. ��������� �������� ������.
     * 
     * @param array $tstages   ������ ������. ����� ��������� ����� ����� � ������, �����������������.
     * @param array $files   ������ $_FILES � ���������� � ������� �����.
     * @param integer $mode   0: ���������, 1: ������������� ��� ������, 2: ������������� ���� ����.
     */
    private function _initStagesFromRequest($tstages, $files, $mode) {
        $tstages_attach = array();
        $stages = array();
        $stages_attach = array();
        $fcnt = self::MAX_FILES;

        // ��������� ������ ��������, ��������������� ������� �����.
        if($files) {
            foreach($files['name'] as $num=>$attarray) {
                foreach($attarray['attach'] as $idx=>$aname) {
                    if($aname) {
                        foreach($files as $key=>$a)
                            $tstages_attach[$num][$idx][$key] = $a[$num]['attach'][$idx];
                        if(--$fcnt < 0) break 2;
                    }
                }
            }
        }

        // ��������� ������ ��������� ������. ��������� ����� ������ ������.
        if($this->data['delstages']) {
            foreach($this->data['delstages'] as $id=>$ds) {
                if($dds = $this->getStageById($id)) {
                    $this->_delstages[$id] = clone $dds;
                    $this->data['cost'] -= $dds->cost;
                }
            }
        }

        // ������������� ������ ������ � ����� ������������������, �� ������ ���� ���� ��������/���������� � ��� ������������.
        // ���� ������������� ������ ���� ����, �� �� �� ������ ����� �� ��� ����� ���������������.
        $num=0;
        foreach($tstages as $rnum=>$ts) {
            if($ts['id']) {
                if($mode == 2 && ($tts = $this->getStageById($ts['id'])))
                    $num = $tts->num;
            }
            $stages[$num] = $ts;
            if($tstages_attach[$rnum])
                $stages_attach[$num] = $tstages_attach[$rnum];
            if($mode == 2) break;
            $num++;
        }
        unset($tstages, $tstages_attach);

        // �������������� $this->stages.
        $stage_cnt = count($stages);
        $new_stages = 0;
        $dnum = 0;
        foreach($stages as $num=>$stg) {
            $num = $num - $dnum;
            if($stg['id'] && ($tts = $this->getStageById($stg['id']))) {
                $this->stages[$num] = $tts;
            }
            else {
                $this->stages[$num] = new sbr_stages($this);
                $new_stages++;
            }
            $this->stages[$num]->data['num'] = $num;
            $this->data['cost'] -= $this->stages[$num]->cost;
            $data_exists = $this->stages[$num]->initFromRequest($stg) || $stages_attach[$num];
            $this->data['cost'] += $this->stages[$num]->cost;
            // ���� ������� ������ ���� �� ������, ��� � ��� ������:
            if( !$data_exists && $stage_cnt > 1 && ($mode==0 || $mode==1 && $this->isDraft()) ) {
                array_splice($this->stages, $num, 1);
                array_splice($stages_attach, $num, 1);
                ++$dnum;
                --$stage_cnt;
            } else if($this->stages[$num]->error) {
                $this->error['stages'][$num] = $this->stages[$num]->error;
            }
        }
        $d = $new_stages - count($this->_delstages);
        $this->data['stages_cnt'] += $d;
        while(++$d<=0)array_pop($this->stages);

        // ��������� �������� �� ������.
        if(!$this->error) {
            $this->getUploadDir();
            foreach($stages_attach as $num=>$atts) {
                foreach($atts as $idx=>$att) {
                    $file = new CFile($att);
                    if($err = $this->uploadFile($file, self::MAX_FILE_SIZE)) {
                        if($err == -1) continue;
                        else {
                            $this->error['stages'][$num]['err_attach'] = $err;
                            break 2;
                        }
                    }
                    $this->stages[$num]->uploaded_files[$idx] = $file;
                }

            }
        }
    }


    /**
     * ����� ���
     * �������������� ����� ������ ��� ��������������/�������� �� ����������������� �������.
     * ��������� �������� �� ������. ��������� �������� ������.
     * 
     * @param array $tstages            ������ ������. ����� ��������� ����� ����� � ������, �����������������.
     * @param array $attached_session   �� ������ ��������������
     * @param integer $mode   0: ���������, 1: ������������� ��� ������, 2: ������������� ���� ����.
     */
    private function _new_initStagesFromRequest($tstages, $tstages_attach, $mode) {
//        $tstages_attach = array();
        $stages = array();
        $stages_attach = array();
        $fcnt = self::MAX_FILES;
        
        // ��������� ������ ��������� ������. ��������� ����� ������ ������.
        if($this->data['delstages']) {
            foreach($this->data['delstages'] as $id=>$ds) {
                if($dds = $this->getStageById($id)) {
                    $this->_delstages[$id] = clone $dds;
                    $this->data['cost'] -= $dds->cost;
                }
            }
        }

        // ������������� ������ ������ � ����� ������������������, �� ������ ���� ���� ��������/���������� � ��� ������������.
        // ���� ������������� ������ ���� ����, �� �� �� ������ ����� �� ��� ����� ���������������.
        $num=0;
        foreach($tstages as $rnum=>$ts) {
            if($ts['id']) {
                if($mode == 2 && ($tts = $this->getStageById($ts['id'])))
                    $num = $tts->num;
            }
            $stages[$num] = $ts;
//            if($tstages_attach[$rnum])
//                $stages_attach[$num] = $tstages_attach[$rnum];
            if($mode == 2) break;
            $num++;
        }
        unset($tstages, $tstages_attach);

        // �������������� $this->stages.
        $stage_cnt = count($stages);
        $new_stages = 0;
        $dnum = 0;
        
        foreach($stages as $num=>$stg) {
            $num = $num - $dnum;
            if($stg['id'] && ($tts = $this->getStageById($stg['id']))) {
                $this->stages[$num] = $tts;
            }
            else {
                $this->stages[$num] = new sbr_stages($this);
                $new_stages++;
            }
            $this->stages[$num]->data['num'] = $num;
            $this->data['cost'] -= $this->stages[$num]->cost;
            $data_exists = $this->stages[$num]->initFromRequest($stg) || $stages[$num]['attached'];
            $this->data['cost'] += $this->stages[$num]->cost;
            // ���� ������� ������ ���� �� ������, ��� � ��� ������:
            if( !$data_exists && $stage_cnt > 1 && ($mode==0 || $mode==1 && $this->isDraft()) ) {
                array_splice($this->stages, $num, 1);
//                array_splice($stages_attach, $num, 1);
                ++$dnum;
                --$stage_cnt;
            } else if($this->stages[$num]->error) {
                $this->error['stages'][$num] = $this->stages[$num]->error;
            }
            // @todo ����� ��� ��������������
        }
        $d = $new_stages - count($this->_delstages);
        $this->data['stages_cnt'] += $d;
        while(++$d<=0)array_pop($this->stages);
        
        // ��������� �������� �� ������.
        if(!$this->error) {
            $dest = $this->getUploadDir();
            foreach($stages as $num=>$stage) {
                if(!$stage['attached']) continue;
                foreach($stage['attached'] as $idx=>$att) {
                    $file = new CFile($att['id']);
                    $file->table = 'file_sbr';
                    $file->_remoteCopy($dest . $file->name);
                    $this->stages[$num]->uploaded_files[$idx] = $file;
                }
                if($stage['new_del_attach']) {
                     foreach($stage['new_del_attach'] as $idx=>$att) {
                        $this->stages[$num]->data['new_del_attach'][$att['id']] = $att;
                     }
                }
            }
        }
    }



    /**
     * ������������ ������ ������ ����� ������� � ����.
     * @return array   ������������ ����� $this->data
     */
    function _preSql() {
        $data = $this->data;
        array_walk($data, array($this, '_preSqlCallback'));
        $data['project_id'] = $data['project_id'] ? $data['project_id'] : 'NULL';
        $data['frl_id'] = $data['frl_id'] ? $data['frl_id'] : 'NULL';
        return $data;
    }

    /**
     * @see sbr_emp::_preSql()
     */
    function _preSqlCallback(&$value, $field) {
        if(is_string($value)) {
            $value = pg_escape_string(change_q_x($value, $field!='descr', false, 'b|br|i|p|cut|s|h[1-6]'));
        }
    }


    /**
     * ������� ����� ������ �� ������ ���������������� �������.
     * 
     * @param array $request   ������ ������� (���, ����).
     * @param array $files   ������ $_FILES � ���������� � ������� �����.
     * @return boolean   �������?
     */
    function create($request, $files) {
        if(!$this->initFromRequest($request, $files))
            return false;

        if( !$this->_openXact(TRUE) )
            return false;


        $sql_data = $this->_preSql();

        $sql = "
          INSERT INTO sbr(emp_id, frl_id, project_id, name, cost_sys, is_draft, scheme_type)
          VALUES ({$this->uid}, {$sql_data['frl_id']}, {$sql_data['project_id']}, '{$sql_data['name']}', {$sql_data['cost_sys']}, '{$sql_data['is_draft']}', {$sql_data['scheme_type']} )
          RETURNING id;
        ";

        if(!($res = pg_query(self::connect(false), $sql))) {
            $this->_abortXact();
            return false;
        }

        $this->data['id'] = pg_fetch_result($res,0,0);

        foreach($this->stages as $num=>$stage) {
            if(!$stage->create()) {
                $this->_abortXact();
                unset($this->data['id']);
                return false;
            }
        }
        

        $this->_commitXact();

        return true;
    }


    /**
     * ������� ����� ������ �� ������ ���������������� �������.
     * 
     * @param array $request   ������ ������� (���, ����).
     * @param attachedfiles $files   
     * @return boolean   �������?
     */
    function _new_create($request, attachedfiles $files) {
        if($request['scheme_type'] == sbr::SCHEME_PDRD2) return false;
        $attached = $files->getFiles(array(1,3), true);
        
        foreach ($request['stages'] as $num => $stage) {
            if (isset($stage['attaches']) && is_array($stage['attaches'])) {
                foreach ($stage['attaches'] as $anum => $att_id) {
                    if (!isset($attached[$att_id])) continue;
//                    $attached[$att_id]['id'] = md5($attached[$att_id]['id']);
                    $request['stages'][$num]['attached'][$anum] = $attached[$att_id];
                }
            }
        }
            
        if(!$this->_new_initFromRequest($request))
            return false;
        
        if( !$this->_openXact(TRUE) )
            return false;

        $sql_data = $this->_preSql();

        $sql = "
          INSERT INTO sbr(emp_id, frl_id, project_id, name, cost_sys, is_draft, scheme_type)
          VALUES ({$this->uid}, {$sql_data['frl_id']}, " . ($sql_data['is_draft'] === 't' ? 'NULL' : $sql_data['project_id']) . ", '{$sql_data['name']}', {$sql_data['cost_sys']}, '{$sql_data['is_draft']}', {$sql_data['scheme_type']} )
          RETURNING id;
        ";

        if(!($res = pg_query(self::connect(false), $sql))) {
            $this->_abortXact();
            return false;
        }

        $this->data['id'] = pg_fetch_result($res,0,0);

        foreach($this->stages as $num=>$stage) {
            if(!$stage->_new_create()) {
                $this->_abortXact();
                unset($this->data['id']);
                return false;
            }
            //��������� ����� 1 ����� � ������� ������� ���� ��� ��������
            else if($num == 0){
                $this->addRefTService($stage->id);
            }
                
        }
        
        if($this->scheme_type == sbr::SCHEME_LC) {
            if($doc_file = $this->generateAgreement($err)) {
                $doc = array ('file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_SIGN, 'access_role' => sbr::DOCS_ACCESS_ALL, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_OFFER, 'subtype' => 2);
                $this->addDocR($doc);
            }
            if(!$err) {
                if($doc_file = $this->generateContract($err)) {
                    $doc = array ('file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_SIGN, 'access_role' => sbr::DOCS_ACCESS_ALL, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_OFFER, 'subtype' => 1);
                    $this->addDocR($doc);
                }
            }
            if($err) {
                $this->_abortXact();
                unset($this->data['id']);
                return false;
            }
        }
        
        // ���������� ��������� ������
        $this->saveProfessions();
        
        $this->_commitXact();
        
        $files->clear();

        return true;
    }

    /**
     * ����������� ������ �� ������ ����������������� �������.
     * ��� ������ ���������� ������ ������ � ��������, ���� �������������� ��������.
     * 
     * @param array $request   ������ ������� (���, ����).
     * @param array $files   ������ $_FILES � ���������� � ������� �����.
     * @return boolean   �������?
     */
    function edit($request, $files) {
        if(!$ret = $this->_edit($request, $files, $old)) {
            $this->data['is_draft'] = $old->data['is_draft'];
        }
        return $ret;
    }
    
    /**
     * ����������� ������ �� ������ ����������������� �������.
     * ��� ������ ���������� ������ ������ � ��������, ���� �������������� ��������.
     * 
     * @param array $request   ������ ������� (���, ����).
     * @param array $files   ������ $_FILES � ���������� � ������� �����.
     * @return boolean   �������?
     */
    function _new_edit($request, attachedfiles $files) {
        if(!$ret = $this->__new_edit($request, $files, $old)) {
            $this->data['is_draft'] = $old->data['is_draft'];
        }
        return $ret;
    }
    
    /**
     * ����������� ������ �� ������ ����������������� �������.
     * @see sbr_emp::initFromRequest()
     * 
     * @param array $request   ������ ������� (���, ����).
     * @param array $files   ������ $_FILES � ���������� � ������� �����.
     * @return boolean   �������?
     */
    private function __new_edit($request, attachedfiles $files, &$old) {
        $old = clone $this;
        
        $attached   = $files->getFiles(array(1, 4), true);
        
        if(!$this->_new_initFromRequest($request, $attached, $old))
            return false;

        if(!$this->_openXact(TRUE))
            return false;

        if($this->_delstages) {
            foreach($this->_delstages as $dstage) {
                if(!$dstage->delete($old->isDraft())) {
                    $this->_abortXact();
                    return false;
                }
            }
        }

        $sql_data = $this->_preSql(true);
        $sql = "
          UPDATE sbr
             SET name = '{$sql_data['name']}',
                 frl_id = {$sql_data['frl_id']},
                 cost_sys = {$sql_data['cost_sys']},
                 is_draft = '{$sql_data['is_draft']}',
                 scheme_type = {$sql_data['scheme_type']}
           WHERE id = {$this->data['id']}
             AND emp_id = {$this->uid}
        ";

        if(!($res = pg_query(self::connect(false), $sql)) || !pg_affected_rows($res)) {
            $this->_abortXact();
            return false;
        }
        // �������� ����� ����������� ������ (���������� �� ����������) � � ��� ��� ������, ����� ������ ���������� ��
        if($this->scheme_type == sbr::SCHEME_LC) {
            $docs = $this->getDocs();
            foreach($docs as $doc) {
                if($doc['type'] == sbr::DOCS_TYPE_OFFER) {
                    $offers_doc[] = $doc['file_id'];
                }
            }
            
            if(count($offers_doc) != 2) {
                $doc_delete = current($offers_doc);
                // �������, ���� ���� ������ ��������������� ���, � ������ ���
                if($doc_delete) {
                    $doc_file = CFile($doc_delete);
                    $doc_file->delete();
                }
                
                if($doc_file = $this->generateAgreement($err)) {
                    $doc = array ('file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_SIGN, 'access_role' => sbr::DOCS_ACCESS_ALL, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_OFFER, 'subtype' => 2);
                    $this->addDocR($doc);
                }
                if(!$err) {
                    if($doc_file = $this->generateContract($err)) {
                        $doc = array ('file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_SIGN, 'access_role' => sbr::DOCS_ACCESS_ALL, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_OFFER, 'subtype' => 1);
                        $this->addDocR($doc);
                    }
                }
                if($err) {
                    $this->_abortXact();
                    unset($this->data['id']);
                    return false;
                }
            }
        }
        
        ////////////////////////
        pg_query(self::connect(false), "SELECT sbr_trigger_fvrs_gt_vrs('sbr', {$this->data['id']})");
        ////////////////////////
        
        foreach($this->stages as $stage) {
            if ($this->scheme_type == sbr::SCHEME_LC && ( $this->data['state'] == pskb::STATE_NEW || $this->data['state'] == pskb::STATE_FORM || $this->data['status'] >= sbr::STATUS_CHANGED)) {
                $cur_stage = $this->initFromStage($stage->id, false);
                $stage->data['cost'] = $cur_stage->cost;
            }
            
            if($this->data['delstages'][$stage->id] || ($this->data['stage_id'] && $stage->id != $this->data['stage_id']) || $stage->isFixedState()) {
                continue;
            }
            if(!($stage->id ? $stage->edit() : $stage->_new_create())) {
                $this->_abortXact();
                return false;
            }
        }
        
        // ���������� ��������� ������
        //$this->saveProfessions(); ��� �������������� ������ �� ����������

        $this->_commitXact();
        
        return true;
    }

    /**
     * ����������� ������ �� ������ ����������������� �������.
     * @see sbr_emp::initFromRequest()
     * 
     * @param array $request   ������ ������� (���, ����).
     * @param array $files   ������ $_FILES � ���������� � ������� �����.
     * @return boolean   �������?
     */
    private function _edit($request, $files, &$old) {
        $old = clone $this;
        
        if(!$this->initFromRequest($request, $files, $old))
            return false;

        if(!$this->_openXact(TRUE))
            return false;

        if($this->_delstages) {
            foreach($this->_delstages as $dstage) {
                if(!$dstage->delete($old->isDraft())) {
                    $this->_abortXact();
                    return false;
                }
            }
        }

        $sql_data = $this->_preSql(true);
        $sql = "
          UPDATE sbr
             SET name = '{$sql_data['name']}',
                 frl_id = {$sql_data['frl_id']},
                 cost_sys = {$sql_data['cost_sys']},
                 is_draft = '{$sql_data['is_draft']}',
                 scheme_type = {$sql_data['scheme_type']}
           WHERE id = {$this->data['id']}
             AND emp_id = {$this->uid}
        ";

        if(!($res = pg_query(self::connect(false), $sql)) || !pg_affected_rows($res)) {
            $this->_abortXact();
            return false;
        }

        ////////////////////////
        pg_query(self::connect(false), "SELECT sbr_trigger_fvrs_gt_vrs('sbr', {$this->data['id']})");
        ////////////////////////

        foreach($this->stages as $stage) {
            if($this->data['delstages'][$stage->id] || ($this->data['stage_id'] && $stage->id != $this->data['stage_id']))
                continue;
            if(!($stage->id ? $stage->edit() : $stage->create())) {
                $this->_abortXact();
                return false;
            }
        }

        $this->_commitXact();
        
        return true;
    }


    /**
     * ��������� ��� ���������.
     * @return array   ������ �������� sbr, ��������������� ��. ������.
     */
    function getDrafts() {
        return $this->_getAllCommon(NULL, true, false, true);
    }
    
    /**
     * ��������� ��� ���������.
     * @return array   ������ �������� sbr, ��������������� ��. ������.
     */
    function _new_getDrafts($limit = false) {
        return $this->_new_getAllCommon(NULL, true, false, true, false, false, false, false, $limit);
    }
    
    /**
     * ��������� ������ ����������.
     * @param integer $limit �����
     * @param integer $excludeID ID ��������� ������� �� ������ ������� � ������
     * @return array ������ �������� sbr, ��������������� ��. ������.
     */
    function getDraftsList($limit = false, $excludeID = false) {
        global $DB;
        $limitSql = $limit ? $DB->parse('LIMIT ?i', $limit) : '';
        $excludeSql = $excludeID ? $DB->parse('AND s.id != ?i', $excludeID) : '';
        $empID = get_uid(0);
        $sql = "
            SELECT s.*
            FROM sbr s 
            WHERE s.emp_id = ?i
                AND s.is_draft = '1'
                AND (s.scheme_type = 4 OR s.scheme_type = 5 OR s.scheme_id IS NULL)
                $excludeSql
            ORDER BY s.last_event_id DESC
            $limitSql";
        $res = $DB->rows($sql, $empID);
        return $res;
    }

    /**
     * ���������, ����� �� ��������� �������� �� ����������� �����������.
     * @return boolean   ��/���.
     */
    function checkSendReady() {
        if(!$this->frl_id) return false;
        $exrur = $this->cost2rur();
        foreach($this->stages as $stage) {
            if($stage->cost * $exrur < sbr_stages::MIN_COST_RUR) return false;
            if(is_empty_html($stage->descr)) return false;
            if($stage->work_time <= 0) return false;
        }
        return true;
    }


    /**
     * ������ ���������� ����� �����������/���������� ������ �� ����������� �����������.
     * @param integer $sbr_id   ��. ������.
     * @return boolean   �������?
     */
    function resendCanceled($sbr_id) {
        $this->_cleaningStages($sbr_id);
        // !!! � �������� ���� �������� ���������. ���� ���� ���������, �� ��� ���� � ����������, ������� �� ��� �������.
        $sql = "UPDATE sbr SET status = " . self::STATUS_NEW . " WHERE id = {$sbr_id} AND emp_id = {$this->uid} AND status IN (" . self::STATUS_REFUSED . ',' . self::STATUS_CANCELED . ')';
        return $this->_eventQuery($sql);
    }


    /**
     * �������� �������� ������.
     * @param integer $sbr_id   ��. ������.
     * @return boolean   �������?
     */
    function cancel($sbr_id) {
        $this->initFromId($sbr_id, false, false);
        $project_null = '';
        if($this->data['project_id'] > 0) {
            //������ ������
            require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects.php';
            projects::SetExecutor($this->data['project_id'], null, $this->uid);
            projects::SwitchStatusPrj($this->uid, $this->data['project_id'], false); // ������ ���������
            $project_null = ', project_id = NULL';
        }
        $sql = "UPDATE sbr SET status = " . self::STATUS_CANCELED . " {$project_null} WHERE id = {$sbr_id} AND emp_id = {$this->uid} AND reserved_id IS NULL";
        return $this->_eventQuery($sql);
    }

    /**
     * ��������� ���� ��� ��������� ���������� �����������.
     * ���� ���� �� ���� �� ������ � ��������, �������.
     *
     * @param array $ids   ��. ����������.
     * @return boolean   �������?
     */
    function send($ids) {
        foreach($ids as $id) {
            $csbr = new sbr_emp($this->uid, $this->login, $this->session_uid);
            $csbr->initFromId($id, true, false);
            if(!$csbr->checkSendReady()) return false;
        }
        return $this->_draft($ids, FALSE);
    }

    /**
     * ����������� ������ (��� ���������) � ���������.
     * 
     * @param integer|array $ids   ��. ������.
     * @return boolean   �������?
     */
    function draft($ids) {
        $draft = $this->_draft($ids, TRUE);
        if($draft) {
            $this->_cleaningStages($ids);
            return $draft;
        }
        return false;
    }

    /**
     * ������ ������ �� ������ (���� ����������� ����� ��������� ������ ��� �������� �� ���� ������) 
     * 
     * @todo ����� ������ �������� ���� ������� �� �������
     */
    private function _cleaningStages($ids) {
        $ids = intarrPgSql($ids);
        $fst_id = $ids[0];
        if($this->scheme_type == sbr::SCHEME_LC) {
            $pskb = new pskb($this);
            $pskb->removeLC();
        }
        $ids = implode(',', $ids);
        $sql = "UPDATE sbr_stages SET frl_agree = false, type_payment = 5 WHERE sbr_id IN({$ids});";
        if($this->_eventQuery($sql))
            return $fst_id;
         return false;
    }
    
    /**
     * ����������� � �������� ��� �������.
     * 
     * @param integer|array $ids   ��. ������.
     * @param boolean $is_draft   true: � ��������, false:��������� �� �����������.
     * @return boolean   �������?

     */
    private function _draft($ids, $draft = false) {
        $ids = intarrPgSql($ids);
        $fst_id = $ids[0];

        $ids = implode(',', $ids);
        $draft = (int)$draft;
        $sql = "UPDATE sbr SET is_draft = '{$draft}', project_id = NULL WHERE id IN ({$ids}) AND emp_id = {$this->uid}";
        if($this->_eventQuery($sql))
            return $fst_id;
        return false;
    }

    /**
     * ������� ���������.
     * 
     * @param integer|array $ids   ��. ������.
     * @return boolean   true, ���� ������� � ������ ������������� ���� ������� �� ���� (������ ������� �� ���������).
     */
    function delete($ids) {
        if ($this->uid != get_uid(false)) {
            return false;
        }
        $ids = intarrPgSql($ids);
        $ids = implode(',', $ids);

        $sql = "DELETE FROM sbr WHERE id IN ({$ids}) AND emp_id = {$this->uid}";
        return ( ($res=pg_query(self::connect(), $sql)) && pg_affected_rows($res) );
    }

    /**
     * �������������� ����� � ������� ��� ��� ��������.
     * 
     * @param account $account   ������������������ ��������� ������ account.
     * @return boolean   �������?
     */
    function testReserve($account) {
        if($this->getReserveSum()) {
            $err = $account->deposit($op_id, $account->id, 0, "�������� ������������� �� ���� ���, {$GLOBALS['EXRATE_CODES'][$this->cost_sys][2]}", $this->cost_sys-1, $this->reserve_sum, self::OP_RESERVE, $this->id);
        }
        if($err) echo $err;
        return !$err;
    }


    /**
     * ������������� ������ � ��������� ����������������� ����� �������� �������� �������������� �� �����.
     * ��������� � �������� ��������� �����������.
     * @see account::deposit().
     * 
     * @param integer $reserved_id   ��. �������� �������������� (account_operations.id).
     * @return boolean   �������?
     */
    function reserve($reserved_id) {
        if(!$this->_openXact(TRUE))
            return false;
        
        $tax_emp = 0;
        $pskb = new pskb($this);
        $lc = $pskb->getLC();
        $dvals = array('P' => pskb::$exrates_map[$lc['ps_emp']]);
        foreach($this->scheme['taxes'][sbr::EMP] as $tax) {
            $tax_emp += sbr_meta::calcAnyTax($tax['tax_id'], $tax['scheme_id'], $this->cost, $dvals);
        }
        $comments = sbr_meta::view_cost($this->getReserveSum(), $this->cost_sys)
                  . ', ' . $this->getContractNum() . ', '
                  . round(($tax_emp / $this->cost) * 100)
                  . '% �� ������ ������� ��� � ����� - '
                  . sbr_meta::view_cost($tax_emp, $this->cost_sys);
        $sql = "
          UPDATE sbr SET reserved_id = ?i WHERE id = ?i;
          UPDATE account_operations SET comments = '{$comments}' WHERE id = ?i;
        ";
          
        $sql = $this->db()->parse($sql, $reserved_id, $this->id, $reserved_id); 
          
        if(!pg_query(self::connect(false), $sql)) {
            $this->_abortXact();
            return false;
        }

        if($this->scheme_type == sbr::SCHEME_AGNT) {
            if($doc_file = $this->generateAgreement($err)) {
                $doc = array ('file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_SIGN, 'access_role' => sbr::DOCS_ACCESS_ALL, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_OFFER, 'subtype' => 2);
                $this->addDocR($doc);
            }
            if(!$err) {
                if($doc_file = $this->generateContract($err)) {
                    $doc = array ('file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_SIGN, 'access_role' => sbr::DOCS_ACCESS_ALL, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_OFFER, 'subtype' => 1);
                    $this->addDocR($doc);
                }
            }
        } else if($this->scheme_type == sbr::SCHEME_PDRD || $this->scheme_type == sbr::SCHEME_PDRD2) {
            if($doc_file = $this->genereteBailmentEmp($err)) {
                $doc = array ('file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_SIGN, 'access_role' => sbr::DOCS_ACCESS_EMP, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_OFFER);
                $this->addDocR($doc);
            }
            if(!$err) {
                if($doc_file = $this->genereteBailmentFrl($err)) {
                    $doc = array ('file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_SIGN, 'access_role' => sbr::DOCS_ACCESS_FRL, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_OFFER);
                    $this->addDocR($doc);
                }
            }
        }
        
        if($err)
            $this->error['fatal'] = $err;
        if($this->error) {
            $this->_abortXact();
            return false;
        }

        $this->_commitXact();
        
        if($this->scheme_type == sbr::SCHEME_LC) {
            if($XACT_ID = $this->_openXact(true)) {
                $result = sbr_notification::sbr_add_event($XACT_ID, $this->id, $this->stages[0]->id, "sbr_stages.STARTED_WORK", $this->version, null, null);
                if(!$result) {
                    $this->_abortXact();
                }
                $this->_commitXact();
            }
        }
        
        return true;
    }

    /**
     * �������������� ��������� ������������, ���� ����� ���������� � ����������� � ������� ����� ���� (��/���), �����
     * ������������� ������� � ����������� ������ ���������������� �� �����.
     * 
     * @param integer $form_type   1:��. ����, 2:���. ����. ���� NULL, �� ���������� � ����� ���������� �� ��������� �������� �� �������.
     * @param integer $reqv_mode   ������ ���������������� ����� ��������� (-1:��������� ������ �����, 1:�� ��������, 2:����� ���������).
     */
    function getInvoiceReqv(&$form_type, &$reqv_mode = -1) { // -1:��������� ������ �����, 1:�� ��������, 2:����� ���������.
        if($reqv_mode != 1) {
            if(!$this->reqv[sbr::FT_JURI]) {
                $this->reqv[sbr::FT_JURI] = new reqv_ordered();
                $this->reqv[sbr::FT_JURI]->GetRow('', "user_id = {$this->uid}", "(sbr_id IS NOT NULL) DESC, (sbr_id = {$this->id}) DESC, payed_time DESC, op_date DESC LIMIT 1");
                if($this->reqv[sbr::FT_JURI]->id) {
                    if($this->id != $this->reqv[sbr::FT_JURI]->sbr_id)
                        unset($this->reqv[sbr::FT_JURI]->id);
                }
                else {
                    $reqv = new reqv();
                    if(($reqvs = $reqv->GetByUid($this->uid)) && ($reqvs = end($reqvs))) {
                        $this->reqv[sbr::FT_JURI]->BindRequest($reqvs, true);
                        unset($this->reqv[sbr::FT_JURI]->id);
                    }
                }
            }
            if(!$this->reqv[sbr::FT_PHYS]) {
                $this->reqv[sbr::FT_PHYS] = new bank_payments();
                $this->reqv[sbr::FT_PHYS]->GetRow('', "user_id = {$this->uid}", "(sbr_id IS NOT NULL) DESC, (sbr_id = {$this->id}) DESC, accepted_time, invoiced_time DESC LIMIT 1");
                if($this->reqv[sbr::FT_PHYS]->id) {
                    if($this->id != $this->reqv[sbr::FT_PHYS]->sbr_id)
                        unset($this->reqv[sbr::FT_PHYS]->id);
                }
            }
        }

        if($reqv_mode != 2) {
            if(!$this->reqv[sbr::FT_PHYS] && !$this->reqv[sbr::FT_JURI]) {
                $form_type = self::FT_JURI;
                $this->reqv[sbr::FT_JURI] = new reqv_ordered();
                $this->reqv[sbr::FT_PHYS] = new bank_payments();
                if($this->getUserReqvs()) {
                    $this->reqv[sbr::FT_JURI]->BindRequest($this->user_reqvs[sbr::FT_JURI], true);
                    $this->reqv[sbr::FT_PHYS]->BindRequest($this->user_reqvs[sbr::FT_PHYS], true);
                    $form_type = $this->user_reqvs['form_type'];
                    unset($this->reqv[sbr::FT_PHYS]->id);
                    unset($this->reqv[sbr::FT_JURI]->id);
                }
                $reqv_mode = 1;
                return;
            }
        }

        $reqv_mode = 2;
        if($form_type === NULL) {
            // ��������� ��������� ��������� (��� ��� ��).
            $form_type = self::FT_JURI;
            if(!$this->reqv[sbr::FT_PHYS]->user_id) $form_type = self::FT_JURI;
            else if(!$this->reqv[sbr::FT_JURI]->user_id) $form_type = self::FT_PHYS;
            else if($this->reqv[sbr::FT_PHYS]->sbr_id == $this->id) {
                if($this->reqv[sbr::FT_JURI]->sbr_id != $this->id || strtotime($this->reqv[sbr::FT_JURI]->op_date) < strtotime($this->reqv[sbr::FT_PHYS]->invoiced_time))
                    $form_type = self::FT_PHYS;
            }
        }
    }

    /**
     * �������� ���� ��� �������������� �� �������.
     * 
     * @param integer $form_type   1:��. ����, 2:���. ����.
     * @param account $account   ������������������ ��������� ������ account �������� ������������.
     */
    function showInvoiced($form_type, $account) {
        $this->getInvoiceReqv($form_type);
        $contract_num = $this->getContractNum($this->id, $this->scheme_type);
		$show_ex_code = false;
        if($form_type==self::FT_JURI) {
		    $rq = $this->getUserReqvs();
			$show_ex_code = $rq['rez_type'] == sbr::RT_UABYKZ; 
            $reqv = $this->reqv[sbr::FT_JURI];
            $ord_num = $reqv->id;
            $sum = $reqv->ammount;
            $sbr_nds = $this->getCommNds($sbr_comm);
            $billCode = "�-{$contract_num}";
            include($_SERVER['DOCUMENT_ROOT'].'/engine/templates/bill/bill_transfer.tpl');
        }
        else {
            $uid = $this->uid;
            $id = $this->reqv[sbr::FT_PHYS]->id;

            include($_SERVER['DOCUMENT_ROOT'].'/engine/templates/bill/bill_bank_print.tpl');
        }
        exit;
    }
    
    function showInvoicedAgnt($account, $tpl = '') {
        $form_type = sbr::FT_JURI;
        $this->getInvoiceReqv($form_type);
        $contract_num = $this->getContractNum($this->id, $this->scheme_type);
        
        $rq = $this->getUserReqvs();
        $reqv = $rq[$form_type];
        
        
        $sbr_comm = 
        $billCode = "�-{$contract_num}";
        
        $pskb = new pskb($this);
        $lc = $pskb->getLC();
        $dvals = array('P' => pskb::$exrates_map[$lc['ps_emp']]);
        $tax_total = 0;
        
        foreach($this->stages as $stage) {
            foreach($this->scheme['taxes'][sbr::EMP] as $tax) {
                if($tax['tax_code'] != 'TAX_FL') continue;
                $tax_total += $stage->calcTax($tax, $dvals, $outsys);
                if (!floatval($tax_total)) continue;
            }
        }
        $sum = $lc['sum'] + $tax_total;
        
        $ord_num = $lc['id'];
        // �������� ������� �� ������� ���������� ����������
        $reqv['full_name'] = $lc['nameCust']; 
        $reqv['phone']     = $lc['numCust'];
        $reqv['invoiced_time'] = $lc['created'];
        
        if($tpl == 'print') {
            include($_SERVER['DOCUMENT_ROOT'].'/engine/templates/bill/bill_transfer_agnt_print.tpl');
        } else {
            include($_SERVER['DOCUMENT_ROOT'].'/engine/templates/bill/bill_transfer_agnt.tpl');
        }
    }

    /**
     * ���������� ���� ��� �������������� �� �������. ���� ���� �� ������ ������ ��� ���������� � ��� �� ������, �� ��������� ������ �����������.
     * 
     * @param integer $form_type   1:��. ����, 2:���. ����.
     * @param array $request   ������ � ����������� �����.
     * @param account $account   ������������������ ��������� ������ account �������� ������������.
     * @return boolean   �������?
     */
    function invoiceBank($form_type, $request, $account) {
        if($form_type==self::FT_JURI) {
            $reqv = new reqv_ordered();
            $reqv->BindRequest(array_map('stripslashes', $request['ft'.self::FT_JURI]));
            if(!($this->error['reqv'] = $reqv->CheckInput(true))) {
                $reqv->user_id = $this->uid;
                $reqv->ammount = $this->reserve_sum;
                $reqv->op_code = self::OP_RESERVE;
                $reqv->op_date = 'now()';
                $reqv->sbr_id = $this->id;
                if($reqv->id) {
                    $reqv->Update($reqv->id, "AND user_id = {$this->uid} AND payed_time IS NULL");
                } else {
                    unset($reqv->id);
                    $reqv->id = $reqv->Add($err, TRUE);
                }
            }
        }
        else if($form_type == self::FT_PHYS) {
            $reqv = new bank_payments();
            $reqv->BindRequest(array_map('stripslashes', $request['ft'.self::FT_PHYS]));
            $reqv->sum = $this->reserve_sum;

            if(!($this->error['reqv'] = $reqv->CheckInput(true))) {
                $bank = $reqv->GetBank($reqv->bank_code);
                $reqv->bill_num = $bank['prefix'].'-'.$this->getContractNum(); // ��������� �����, �.�. ����� ����� ����������.
                $done = false;
                if(!$reqv->id) {

                    $reqv->user_id = $this->uid;
                    $reqv->op_code = self::OP_RESERVE;
                    $reqv->sbr_id = $this->id;
                    @$reqv->id = $reqv->Add($error, TRUE);
                    if($error || $reqv->id <= 0) { // �������� ������, �������� �������� (� ���� ��������� ����� �������������).

                        $reqv->id = NULL;
                        $this->getInvoiceReqv($form_type);
                        if($this->reqv[sbr::FT_PHYS] && $this->reqv[sbr::FT_PHYS]->sbr_id == $this->id)
                            $reqv->id = $this->reqv[sbr::FT_PHYS]->id;
                        $this->reqv = array();
                    } else {
                        $done = true;
                    }
                }
                if($reqv->id && !$done) {
                    $reqv->bank_code = NULL;
                    $reqv->invoiced_time = 'now()';
                    $reqv->Update($reqv->id, " AND user_id = {$this->uid} AND accepted_time IS NULL");
                }
            }
        }
        $this->reqv[(int)$form_type] = $reqv;
        if($this->error['reqv']['address']) {
            $this->error['reqv']['address'] = array ('�� ���� ����� ����� ������� ��� ���������', '������: ��. ����������, 1, ���. 21');
        }
        if(!$this->error['reqv'] && $request['save_finance']) {
            //@todo: ��������� �������� ������� � ������ ��� #29196
            $err = '���������� ��������� ���.';//parent::setUserReqv($this->uid, NULL, $form_type, $request['ft'.$form_type], $this->checkChangeFT());
            if($err) {
                $this->error['reqv'] = $err;
            }
        }
        return !$this->error['reqv'];
    }


    /**
     * �������� ����� ��� ���������� ���������� ��� �������������� �� �������.
     * 
     * @param integer $stage_id   ��. �����, � ������� ����� ������� �����.
     * @param integer $form_type   ������� ��� ���� ����� (1:��. ����, 2:���. ����).

     * @param integer $reqv_mode   ������ ���������������� ����� ��������� (-1:��������� ������ �����, 1:�� ��������, 2:����� ���������).
     * @param boolean $save_finance   ���������� ����� "������ ��������� � �������� ���������"?
     * @return string   html-����� � ������.
     */
    function view_invoice_form($stage_id, $form_type, $reqv_mode=1, $save_finance = false) {
        $sbr = $this;
        sbr_meta::getReqvFields();
        $sbr->getInvoiceReqv($form_type, $reqv_mode);
        ob_start();
        include($_SERVER['DOCUMENT_ROOT'].'/norisk2/employer/tpl.stage-reserve-bn-form.php');
        return ob_get_clean();
    }



    /**
     * ���������, ����� �� � ������ ������ ������������ ������� ���� (��/���).
     *
     * @return integer   0: �����,
     *                   1: �� �����, ������ ��� ���� ������������� ����������������� ������,
     *                   2: �� �����, ������ ��� � ����� �� ��������� �� ������ ������ ��, ��������� ������ �������.
     */
    function checkChangeFT() {
        if(($sbr_info = sbr_meta::getUserInfo($this->uid)) && $sbr_info['all_cnt']) {
            if($this->getProcessings())
                return 1;                              // !!! �������� ������� ��������, ������� � ����� ����������.
            if($sbr_actives = $this->getActives()) {
                foreach($sbr_actives as $s) {
                    if($s->cost_sys == exrates::YM || $s->cost_sys == exrates::WMR)
                        return 2;
                }
            }
        }
        return 0;
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
                freelancer u ON sbr.frl_id = u.uid
            WHERE 
                (emp_id = {$this->uid} AND frl_id IS NOT NULL)
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
                    DISTINCT frl_id
                FROM 
                    sbr 
                WHERE 
                    emp_id = ?
                    AND frl_id IS NOT NULL
                    AND status <= ?
            ) s
            INNER JOIN
                freelancer u ON u.uid = s.frl_id AND u.is_banned = B'0'
        ";
        return $DB->rows($sql, $this->uid, sbr::STATUS_PROCESS);
    }
    
    function setCostSys($cost_sys) {
        global $DB;
        if( ($cost_sys == exrates::WMR || $cost_sys == exrates::YM) && $this->user_reqvs['form_type'] == sbr::FT_JURI ) {
            return false;
        }
        $sql = "UPDATE sbr SET cost_sys = ?i WHERE id = ?i AND emp_id = ?i";
        return $DB->query($sql, $cost_sys, $this->data['id'], $this->uid);
    }
    
    /**
     * ��� � �������� ��������������
     * 
     * @return boolean
     */
    function isReserveProcess() {
        return ($this->state == pskb::STATE_NEW || $this->state == pskb::STATE_FORM);
    }
    
    /**
     * ��������� ������ � ���������� ��� � ������� sbr_to_spec
     * �� ������� ��������� ��� ������ ��� ���� ������
     * ������ ������� �� $this->data['professions'][x]['category_id']
     * ��������� ������� �� $this->data['professions'][x]['subcategory_id']
     * @global type $DB
     */
    function saveProfessions(){
        global $DB;
        $delSql = 'DELETE FROM sbr_to_spec WHERE sbr_id = ?i';
        $DB->query($delSql, $this->data['id']);
        
        $insert = array();
        foreach ($this->data['professions'] as $prof) {
            $insert[] = array(
                'sbr_id'            => $this->data['id'],
                'category_id'       => $prof['category_id'],
                'subcategory_id'    => $prof['subcategory_id'],
            );
        }
        $DB->insert('sbr_to_spec', $insert);
    }
    
    /**
     * ������� ��������� �� sbr_to_spec � �������� �� � $this->data['professions']
     */
    function retrieveProfession () {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/professions.php';
        global $DB;
        if (!$this->data['id']) {
            return;
        }
        $sql = 'SELECT category_id, subcategory_id FROM sbr_to_spec WHERE sbr_id = ?i ORDER BY id DESC';
        $res = $DB->rows($sql, $this->data['id']);
        $this->data['professions'] = array();
        if (is_array($res)) {
            foreach($res as $prof) {
                $prof['prof_name'] = $prof['subcategory_id'] ? professions::GetProfNameWP($prof['subcategory_id'], ': ', '', false) : professions::GetGroupName($prof['category_id']);
                if ($prof['subcategory_id']) {
                    $prof['default'] = $prof['subcategory_id'];
                    $prof['default_column'] = 1;
                } else {
                    $prof['default'] = $prof['category_id'];
                    $prof['default_column'] = 0;
                }
                
                $this->data['professions'][] = $prof;
            }
        } else {
            $this->data['professions'] = array();
        }
    }
    
    
}

?>