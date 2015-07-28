<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/op_codes.php');

/**
 * ������ � ������������� ����������� � ��������
 *
 * @author danil
 */
class freelancer_binds {
    
    private $TABLE = 'freelancer_binds';
    
    /**
     * ��������� ����������
     */
    const PRICE_CATALOG = 5000;
    const PRICE_PROFGROUP = 3000;
    const PRICE_PROF = 1000;
    
    const OP_CODE_CATALOG = 142;
    const OP_CODE_PROFGROUP = 143;
    const OP_CODE_PROF = 144;
    const OP_CODE_PROLONG_CATALOG = 148;
    const OP_CODE_PROLONG_PROFGROUP = 149;
    const OP_CODE_PROLONG_PROF = 150;
    
    /**
     * ��������� ���������
     * @todo: �� ������������ � � ���������� ������
     */
    const PRICE_UP_CATALOG = 1000;
    const PRICE_UP_PROFGROUP = 600;
    const PRICE_UP_PROF = 200;
    
    const OP_CODE_UP_CATALOG = 151;
    const OP_CODE_UP_PROFGROUP = 152;
    const OP_CODE_UP_PROF = 153;
    const OP_CODE_UP_BUFFER = 194;
    
    /**
     * ������ ��� ������� �������
     */
    const DESCR = '����������� � %s �������� �����������';
    const DESCR_PROLONG = '��������� ����������� � %s �������� �����������';
    const DESCR_UP = '�������� ����������� �� 1 ����� � %s � �������� �����������';
    const DESCR_CATALOG = '����� �������';
    const DESCR_PROFGROUP = '������� %s';
    const DESCR_PROF = '������� %s &mdash; %s';
    const COMMENT = '�� %s';
    
    /**
     * ��������� ���� ������
     */
    private $db = null;
    
    /**
     * ���� ��������, ��������� ��� ����������� �� ��� ����
     * @var type 
     */
    private $op_code_groups = array(
        array(
            self::OP_CODE_CATALOG,
            self::OP_CODE_PROLONG_CATALOG,
            self::OP_CODE_UP_CATALOG
        ),
        array(
            self::OP_CODE_PROFGROUP,
            self::OP_CODE_PROLONG_PROFGROUP,
            self::OP_CODE_UP_PROFGROUP
        ),
        array(
            self::OP_CODE_PROF,
            self::OP_CODE_PROLONG_PROF,
            self::OP_CODE_UP_PROF
        )
    );
    
    /**
     * ������ ��� �������
     * @var type 
     */
    private $bind_data = array();

    /**
     * ������ ��� ��������
     * @var type 
     */
    public $bind_info = array();
    
    public function __construct()
    {
        global $DB;
        $this->db = $DB;
    }
    
    
    
    
    /**
     * ������� �������� ��� �������������
     * 
     * @var type 
     */
    static protected $_bills = array();


    
    /**
     * �������� ������ �������� ��� ���������� ������������
     * 
     * @param type $uid
     * @return type
     */
    static protected function getBilling($uid)
    {
        if (!isset(self::$_bills[$uid])) {
            self::$_bills[$uid] = new billing($uid);
        }
        
        return self::$_bills[$uid];
    }
    
    
    
    
    
    /**
     * ����������, �������� �� ������������ ����������� � ��������� �������
     * 
     * @param type $uid �� ������������
     * @param type $prof_id �� �������
     * @param type $is_spec ���� ������������� ��� ������
     */
    public function isAllowBind($uid, $prof_id, $is_spec, $check_exist = true) 
    {
        
        $allow = true;
        
        if (!$uid || is_emp()) {
            $allow = false; 
            return $allow;
        }
        
        if ($prof_id) {
            //������������� ������ ���� �� ������ ��������� ������������� ������������
            $user = new freelancer();
            $user->GetUserByUID($uid);
            
            //��� ������������� � ���������
            $user_profs = professions::GetProfessionsByUser($user->uid, true, true);
            
            if ($is_spec) {
                if ($this->needAddProf($uid, $prof_id)) {
                    $allow &= true;
                } else {
                    $allow &= in_array($prof_id, $user_profs);
                }
            } else {
                $prof_groups = array();
                foreach ($user_profs as $prof) {
                    $prof_groups[] = professions::GetGroupIdByProf($prof);
                }
                $allow &= in_array($prof_id, $prof_groups);
            }
        }

        if ($check_exist) {
            // �� ����������� ��������� �������
            $allow &= !$this->isUserBinded($uid, $prof_id, $is_spec);
        }

        return $allow;
    }
    
    
    /**
     * ����������, ����� �� ������������ ����������� � ��������� �������
     * 
     * @param type $uid �� �����
     * @param type $prof_id �� �������
     * @param type $is_spec ���� ������������� ��� ������
     */
    public function isUserBinded ($uid, $prof_id, $is_spec)
    {
        if (!$uid) return false;
        
        $sql = "SELECT id FROM {$this->TABLE} WHERE user_id = ?i AND prof_id = ?i AND is_spec = ?b AND date_stop > NOW()";
        $id = $this->db->val($sql, $uid, $prof_id, $is_spec);
        return $id;
    }
    
    public function getBindDate ($uid, $prof_id, $is_spec = true)
    {
        if (!$uid) return false;
        
        $sql = "SELECT date_start FROM {$this->TABLE} WHERE user_id = ?i AND prof_id = ?i 
            AND is_spec = ?b AND date_stop > NOW() ORDER BY id DESC";
        return $this->db->val($sql, $uid, $prof_id, $is_spec);
    }
    
    /**
     * ���������� ���� ��������� �����������
     * @param type $uid
     * @param type $prof_id
     * @param type $is_spec
     * @return boolean
     */
    public function getBindDateStop ($uid, $prof_id, $is_spec = true)
    {
        if (!$uid) return false;
        
        $sql = "SELECT date_stop FROM {$this->TABLE} WHERE user_id = ?i AND prof_id = ?i 
            AND is_spec = ?b AND date_stop > NOW() ORDER BY id DESC";
        return $this->db->val($sql, $uid, $prof_id, $is_spec);
    }
    
    /**
     * ���������� ���� �� ������
     * 
     * @param type $prof_id
     * @param type $is_spec
     * @return type
     */
    public function getPrice($prof_id, $is_spec, $is_prolong = false, $uid = 0)
    {
        $opCode = $this->getOpCode($prof_id, $is_spec, $is_prolong);
        
        //������� �������� ������ ��� ����������� ������������
        if($uid > 0) {
            $bill = self::getBilling($uid);
            $opCode = $bill->getDiscountOpCode($opCode);
        }
        
        $data = op_codes::getDataByOpCode($opCode, $prof_id);
        return @$data['sum'];
    }
    
    
    /**
     * ���������� ���� �� ��������
     * 
     * @param type $prof_id
     * @param type $is_spec
     * @return type
     */
    public function getPriceUp($prof_id, $is_spec, $uid = 0)
    {
        $opCode = $this->getOpCodeUp($prof_id, $is_spec);
        
        //������� �������� ������ ��� ����������� ������������
        if($uid > 0) {
            $bill = self::getBilling($uid);
            $opCode = $bill->getDiscountOpCode($opCode);
        }
        
        $data = op_codes::getDataByOpCode($opCode, $prof_id);
        return @$data['sum'];
    }
    
    
    public function getOpCode($prof_id, $is_spec, $is_prolong)
    {
        if ($is_prolong) {
            if ($prof_id) {
                return $is_spec ? self::OP_CODE_PROLONG_PROF : self::OP_CODE_PROLONG_PROFGROUP;
            } else {
                return self::OP_CODE_PROLONG_CATALOG;
            }
        } else {
            if ($prof_id) {
                return $is_spec ? self::OP_CODE_PROF : self::OP_CODE_PROFGROUP;
            } else {
                return self::OP_CODE_CATALOG;
            }
        }
    }
    
    public function getOpCodeUp($prof_id, $is_spec)
    {
        if ($prof_id) {
            return $is_spec ? self::OP_CODE_UP_PROF : self::OP_CODE_UP_PROFGROUP;
        } else {
            return self::OP_CODE_UP_CATALOG;
        }
    }
    
    /**
     * ���������� ������ ��� ������� � ������� � ��������� ���������� ��� ������� �� �����������
     * @param type $uid
     * @param type $prof_id
     * @param type $is_spec
     * @param type $weeks
     * @return boolean
     */
    public function prepare($uid, $prof_id, $is_spec, $weeks)
    {
        $date_stop = time() + $weeks * 7 * 24 * 60 * 60;
        $this->bind_data = array(
            'user_id' => $uid,
            'prof_id' => $prof_id,
            'is_spec' => $is_spec,
            'date_start' => 'NOW()',
            'date_stop' => date('Y-m-d H:i:s', $date_stop),
            'status' => true
        );

        $this->bind_info = array(
            'descr' => sprintf(self::DESCR, $this->getProfessionText($prof_id, $is_spec)),
            'comment' => sprintf(self::COMMENT, date('d.m.Y', $date_stop))
        );
        return true;
    }
    
    /**
     * ��������� ���������� ��� ������� �� ���������
     * @param type $uid
     * @param type $prof_id
     * @param type $is_spec
     * @param type $weeks
     * @return boolean
     */
    public function getProlongInfo($uid, $prof_id, $is_spec, $weeks)
    {
        $days = $weeks * 7;
        $date_stop = $this->getBindDateStop($uid, $prof_id, $is_spec);
        
        $dateTime = new DateTime($date_stop);
        
        $dateTime->modify("+{$days} day");
        
        $this->bind_info = array(
            'descr' => sprintf(self::DESCR_PROLONG, $this->getProfessionText($prof_id, $is_spec)),
            'comment' => sprintf(self::COMMENT, $dateTime->format('d.m.Y'))
        );
        return true;
    }
    
    /**
     * ��������� ���������� ��� ������� �� ��������
     * @param type $uid
     * @param type $prof_id
     * @param type $is_spec
     * @return boolean
     */
    public function getUpInfo($uid, $prof_id, $is_spec)
    {
        $this->bind_info = array(
            'descr' => sprintf(self::DESCR_UP, $this->getProfessionText($prof_id, $is_spec)),
        );
        return true;
    }
    
    /**
     * ������� ������ � �������
     * @return type
     */
    public function create()
    {
        if ($this->bind_data['is_spec']) {
            $add_prof = $this->needAddProf($this->bind_data['user_id'], $this->bind_data['prof_id']);
            if ($add_prof == 1) { //��������� ���.�������������
                professions::UpdateProfsAddSpec($this->bind_data['user_id'], 0, $this->bind_data['prof_id'], 0);
            } elseif ($add_prof == 2) { //������������� �������� �������������
                $frl = new freelancer;
                $frl->spec = $this->bind_data['prof_id'];
                $frl->spec_orig = $this->bind_data['prof_id'];
                professions::setLastModifiedSpec($this->bind_data['user_id'], $this->bind_data['prof_id']);
                $frl->Update($this->bind_data['user_id'], $res);
            }
        }
        
        $ok = $this->db->insert($this->TABLE, $this->bind_data);
        
        if ($ok) {
            freelancer::clearCacheFromProfIdNow($this->bind_data['prof_id'], $this->bind_data['is_spec']);
            return true;
        }
        return false;
    }
    
    /**
     * ����������, ����� �� ��������� ������������� � �������
     * @param type $uid �� ������������
     * @param type $prof_id �� �������������
     * @return int 0 ���� �� �����, 1 ���� ���. �������������, 2 ���� �������� ����-�
     */
    public function needAddProf($uid, $prof_id) {
        $user_profs = professions::GetProfessionsByUser($uid, true, true);
        $selected_profs_count = count(professions::GetProfessionsByUser($uid, false));
        $has_free_spec_slot = $selected_profs_count < (1 + (is_pro(true, $uid) ? PROF_SPEC_ADD : 0));
        
        if (!in_array($prof_id, $user_profs) && $has_free_spec_slot) {
            $user = new freelancer();
            $user->GetUserByUID($uid);
            return $user->spec == 0 ? 2 : 1;
        }
        return 0;
    }
    
    /**
     * ���������� �������� ������� ��� ������� ��������
     * @param type $prof_id
     * @param type $is_spec
     * @return type
     */
    private function getProfessionText($prof_id, $is_spec)
    {
        $prof_text = '';
        if ($prof_id) {
            if ($is_spec) {
                $group_id = professions::GetGroupIdByProf($prof_id);
                $prof_group_title = professions::GetProfGroupTitle($group_id);
                $prof_title = professions::GetProfName($prof_id);
                $prof_text = sprintf(self::DESCR_PROF, $prof_group_title, $prof_title);
            } else {
                $prof_group_title = professions::GetProfGroupTitle($prof_id);
                $prof_text = sprintf(self::DESCR_PROFGROUP, $prof_group_title);
            }
        } else {
            $prof_text = self::DESCR_CATALOG;
        }
        return $prof_text;
    }
    
    /**
     * ����������, ��������� �� ����������� �� ������ �����
     * @param type $uid
     * @param type $prof_id
     * @param type $is_spec
     */
    public function isBindFirst($uid, $prof_id, $is_spec)
    {
        if (!$uid) return false;
        
        $date_start = $this->getBindDate($uid, $prof_id, $is_spec);
        
        if (!$date_start) {
            return false;
        }
        
        $sql = "SELECT id
            FROM {$this->TABLE} 
            WHERE user_id != ?i AND prof_id = ?i AND is_spec = ?b AND date_stop > NOW() AND date_start > ?::timestamp
            ORDER BY id DESC";
        $bind_later = $this->db->val($sql, $uid, $prof_id, $is_spec, $date_start);
            
        return !$bind_later;
    }
    
    /**
     * �������� �����������
     * @param int $id �� �����������
     * @param int $weeks ���������� ������
     */
    public function prolong($id, $weeks, $prof_id, $is_spec)
    {
        $days = $weeks * 7;
        $ok = $this->db->query("UPDATE {$this->TABLE} 
            SET date_start = NOW(), date_stop = date_stop + '?i days',
            sent_up = FALSE, sent_prolong = FALSE
            WHERE id = ?i", $days, $id);
        if ($ok) {
            freelancer::clearCacheFromProfIdNow($prof_id, $is_spec);
            return true;
        }
        return false;
        
    }
    
    /**
     * ��������� ���� ������ ����������� �� �������
     * @param int $id �� �����������
     */
    public function up($id, $prof_id, $is_spec)
    {
        $ok = $this->db->query("UPDATE {$this->TABLE} 
            SET date_start = NOW(), sent_up = FALSE
            WHERE id = ?i", $id);
        if ($ok) {
            freelancer::clearCacheFromProfIdNow($prof_id, $is_spec);
            return true;
        }
        return false;
    }
    
    /**
     * �������� �������������, � ������� � ������� ����� �������� ���� ����������
     * @global type $DB
     * @param type $param
     * @return boolean
     */
    public static function getExpiring()
    {
        global $DB;

         $sql = "SELECT fb.prof_id, fb.is_spec, 
            u.uid, u.login, u.email, u.uname, u.usurname, u.subscr, fb.date_stop as to_date
            FROM (
                SELECT user_id, prof_id, is_spec, date_stop
                FROM freelancer_binds
                WHERE date_stop > NOW()
                AND (sent_prolong IS NULL OR sent_prolong = FALSE)
            ) as fb
            INNER JOIN users u ON u.uid = fb.user_id
            WHERE fb.date_stop + '-2 day' <= now()::date AND 
            u.is_banned = '0' AND substr(u.subscr::text,16,1) = '1'
        ";
        $ret = $DB->rows($sql);

        return $ret;
        
    }
    
    /**
     * ���������� ������, ������� ���������� �� 4 ����� � ����
     * @global type $DB
     * @return type
     */
    public static function getDowned()
    {
        global $DB;
        
        $sql = "SELECT fb.prof_id, fb.is_spec, fb.date_stop, u.uid, u.login, u.email, u.uname, u.usurname, u.subscr
            FROM freelancer_binds as fb
            INNER JOIN users u ON u.uid = fb.user_id
            WHERE u.is_banned = '0' AND substr(u.subscr::text,16,1) = '1'  AND (sent_up IS NULL OR sent_up = FALSE)
            AND fb.date_stop > now()
            AND (SELECT COUNT(DISTINCT(fb2.user_id)) FROM freelancer_binds as fb2 
                WHERE fb.user_id != fb2.user_id AND fb.date_start < fb2.date_start
                AND fb.prof_id = fb2.prof_id AND fb.is_spec = fb2.is_spec
            ) >= 3;";
        $ret = $DB->rows($sql);
        return $ret;
    }
    
    
    /**
     * �������� ���� ����� �������� ���������������� ����������� (� ��������� ��� ��������)
     * @global type $DB
     * @param string $type ��� ����������� prolong|up
     * @param type $uid �� �����
     * @param type $profession �� ���������
     * @param type $tarif ����� (��� ����������� ����������� �������)
     * @return boolean true ���� �������
     */
    public static function markSent($type, $uid, $profession, $is_spec)
    {
        if (!in_array($type, array('prolong', 'up'))) {
            return false;
        }
        
        global $DB;
        
        return $DB->update(
            'freelancer_binds', 
            array('sent_'.$type => true), 
            'user_id = ?i AND prof_id = ?i AND is_spec = ?b', 
            $uid, $profession, $is_spec=='t'
        );        
    }
    
    /** �������� ����������� �� id � account_operations
     * @see account::DelByOpid()
     *
     * @param  intr $uid uid ������������
     * @param  int $opid id �������� � ��������
     * @return int 0
     */
    public function DelByOpid($uid, $opid)
    {
        //�������� ���������� �� ��������� �������
        $sql = "SELECT bq.id, bq.op_count, bq.src_id, ao.op_code
            FROM account_operations ao
            INNER JOIN bill_reserve br ON br.uid = ?i
                AND br.ammount = -(ao.ammount) 
                AND br.complete_time::timestamp = ao.op_date::timestamp
            INNER JOIN bill_queue bq ON bq.reserve_id = br.id AND bq.op_code = ao.op_code
            WHERE ao.id = ?i
                AND ao.billing_id = (SELECT id FROM account WHERE uid = ?i);";
        $operation = $this->db->row($sql, $uid, $opid, $uid);
        if (!$operation) return 0;
        
        $operation['op_code'] = billing::getOpCodeByDiscount($operation['op_code']);
        
        $is_spec = false;
        $ok = false;        
        switch ($operation['op_code']) {
            case self::OP_CODE_PROF:
                $is_spec = true;
            case self::OP_CODE_CATALOG:
            case self::OP_CODE_PROFGROUP:
                //������� ������ � ����������� �� ����
                $ok = $this->db->query("DELETE FROM {$this->TABLE} WHERE user_id = ?i AND prof_id = ?i AND is_spec = ?b", 
                        $uid, $operation['src_id'], $is_spec);
                break;
            
            case self::OP_CODE_PROLONG_PROF:
            case self::OP_CODE_UP_PROF:
                $is_spec = true;
            case self::OP_CODE_PROLONG_CATALOG:
            case self::OP_CODE_PROLONG_PROFGROUP:
            case self::OP_CODE_UP_CATALOG:
            case self::OP_CODE_UP_PROFGROUP:
                //����������� ����� ������ ����� �������� ����������� � ������ 
                //�������� ������� - ����� ����������� ��������� 
                //�� �� ����� � ��������, � �������� ��� ������
                
                $bind_id = $this->isUserBinded($uid, $operation['src_id'], $is_spec);
                if ($bind_id) {
                    $dates = $this->recalcBindDates($operation, $uid);
                    $this->db->update($this->TABLE, array(
                        'date_start' => $dates['start'],
                        'date_stop' => $dates['stop']
                    ), 'id = ?i', $bind_id);
                }
                break;
        }
        
        if ($ok) {
            freelancer::clearCacheFromProfIdNow($operation['src_id'], $is_spec);
        }
        
        return 0;
    }
    
    /**
     * ������������� ���� ������ � ��������� ����������� �� ��������
     * @param type $operation
     * @param type $uid
     * @return type
     */
    private function recalcBindDates($operation, $uid)
    {
        //�������� ���� ��������, ������� ����� ���� ��������� �� ������� �����������
        $op_codes_allow = array();
        foreach ($this->op_code_groups as $group) {
            $op_codes_in_group = billing::extendOpCodes($group);
            if (in_array($operation['op_code'], $op_codes_in_group)) {
                $op_codes_allow = $op_codes_in_group;
            }
        }

        //�������� ���������� �� ��������� �������� �� ������� �����������
        $sql2 = "SELECT bq.op_code, bq.op_count, br.complete_time::timestamp as date
            FROM bill_queue bq
            INNER JOIN bill_reserve br ON bq.reserve_id = br.id
            INNER JOIN account_operations ao ON br.ammount = -(ao.ammount) 
                AND br.complete_time::timestamp = ao.op_date::timestamp
                AND ao.billing_id = (SELECT id FROM account WHERE uid = ?i)
            WHERE bq.uid = ?i AND bq.service IN ('frlbind', 'frlbindup')
                AND bq.src_id = ?i AND bq.op_code IN (?l)
                AND bq.status = 'complete' AND bq.id != ?i
            ORDER BY br.complete_time ASC;";
        $operations = $this->db->rows($sql2, $uid, $uid, $operation['src_id'], $op_codes_allow, $operation['id']);

        foreach ($operations as $operation){
            //������������� ���� ������ ��� ����� ��������
            $date_start = DateTime::createFromFormat("Y-m-d H:i:s.u", $operation['date']);
            $operation['op_code'] = billing::getOpCodeByDiscount($operation['op_code']);
            
            if (in_array($operation['op_code'], 
                    array(self::OP_CODE_CATALOG, self::OP_CODE_PROFGROUP, self::OP_CODE_PROF)
                ) || !isset($date_stop)) {
                //���� �������, �� ���� ��������� ������� �� ���� �������
                $date_stop = clone $date_start;
                $date_stop->add(new DateInterval('P'.($operation['op_count']*7).'D'));
            } else {
                //���� ��������� - �������� ���� ���������
                if (in_array($operation['op_code'], 
                        array(self::OP_CODE_PROLONG_CATALOG, self::OP_CODE_PROLONG_PROFGROUP, self::OP_CODE_PROLONG_PROF)
                )) {
                    $date_stop->add(new DateInterval('P'.($operation['op_count']*7).'D'));
                }                
            }
        }
        
        if (!isset($date_start)) {
            $date_start = new DateTime('NOW');
        }
        
        if (!isset($date_stop)) {
            $date_stop = clone $date_start;
        }
        
        return array(
            'start' => $date_start->format('Y-m-d H:i:s'),
            'stop' => $date_stop->format('Y-m-d H:i:s')
        );
    }
    
}
