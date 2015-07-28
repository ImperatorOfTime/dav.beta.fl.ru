<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/wizard.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/step_wizard.php';

/**
 * ����� ��� ������ � �������� ���������� ����������� � �������
 *  
 */
class wizard_billing
{
    function __construct($uid = false) {
        if(!$uid) $uid = $_SESSION['uid'];
        $this->uid = intval($uid);
    }
    
    /**
     * ��������� ������ ��������
     * 
     * @param type $operations  �������� � ������
     * @return boolean 
     */
    function paymentOptions($operations) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/step_freelancer.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/step_employer.php';
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_answers.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/log.php";
        $this->log  = new log('wizard/payed-'.SERVER.'-%d.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        $payed_operation = $this->getDraftAccountOperationsByIds($operations, $this->uid);
        $account = new account;
        if($payed_operation) {
            // ������ �� ��� ��� ������ ������� ���
            $is_pro = is_pro();
            if (!$is_pro) {
                foreach($payed_operation as $option) {
                    if ($option['op_code'] == 15) {
                        $is_pro = true;
                        break;
                    }
                }
            }
            // ���������� ��� ������� � ������������ � ������ ���
            if ($is_pro) {
                foreach($payed_operation as &$option) {
                    switch($option['op_code']) {
                        case new_projects::OPCODE_KON_NOPRO:
                            $option['op_code'] = new_projects::OPCODE_KON;
                            break;
                        case new_projects::OPCODE_PAYED:
                            $option['ammount'] = $option['ammount'] - new_projects::PRICE_ADDED;
                            break;
                        default:
                            break;
                    }
                }
            }
            unset($option);
            
            $transaction_id = $account->start_transaction($this->uid);
            foreach($payed_operation as $option) {
                $ok[$option['id']] = $this->billingOperation($option, $transaction_id);
                if($ok[$option['id']]) {
                    $delete = $this->deleteDraftAccountOperation($option['id']);
                    if(!$delete) {
                        $this->log->writeln("Error delete draft account operation - user (" . wizard::getUserIDReg() . ") - option #{$option['id']}");
                    }
                } else {
                    $this->log->writeln("Error billing operation - user (" . wizard::getUserIDReg() . ") - option #{$option['id']}");
                }
            }
            $account->commit_transaction($transaction_id, $this->uid, null);
            return true;
        }
        
        return false;
    }
    
    /**
     * ��������� � ������ ��������
     * 
     * @global type $DB
     * @param type $option
     * @return boolean 
     */
    function billingOperation($option, $transaction_id) {
        global $DB;
        $ok = false;
        $account = new account();
        switch($option['op_code']) {
            // ������� ��� � ����������
            case 48:
            case 49:
            case 50:
            case 51:
            case 76:
                // ������� �������� �� ������� ������� - ��������� ������
                $prof = new payed();
                $ok   = $prof->SetOrderedTarif($this->uid, $transaction_id, 1, "������� PRO", $option['op_code'], $error);
                if($ok) {
                    $_SESSION['pro_last'] = payed::ProLast($_SESSION['login']);
                    $_SESSION['pro_last'] = $_SESSION['pro_last']['freeze_to'] ? false : $_SESSION['pro_last']['cnt'];
                    $userdata = new users();
                    $_SESSION['pro_test'] = $userdata->GetField($this->uid, $error2, 'is_pro_test', false);
                    
                    $this->clearBlockedOperations(step_freelancer::OFFERS_OP_CODE);
                    $step_frl = new step_freelancer();
                    $offers   = $step_frl->getWizardOffers($this->uid, 'all', false);
                    if($offers) {
                        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
                        $step_frl->log  = $this->log;
                        $step_frl->user = new users();
                        $step_frl->user->GetUserByUID($this->uid);
                        $step_frl->transferOffers($offers);
                    }
                    $this->showProjectsFeedbacks();
                }
                break; 
            // ������� ��� � ������������
            case 15:
                $prof = new payed();
                $ok   = $prof->SetOrderedTarif($this->uid, $transaction_id, 1, "������� PRO", $option['op_code'], $error);
                if($ok) {
                    $_SESSION['pro_last'] = payed::ProLast($_SESSION['login']);
                    $_SESSION['pro_last'] = $_SESSION['pro_last']['freeze_to'] ? false : $_SESSION['pro_last']['cnt'];
                    $userdata = new users();
                    $_SESSION['pro_test'] = $userdata->GetField($this->uid, $error2, 'is_pro_test', false);
                }
                // ��������� ����� ����� ��� �������� �� �� ��� ��� ����������
                $colorProjects = $this->updateColorProject();
                $prj = new new_projects();
                foreach($colorProjects as $k=>$project) {
                    $delete_color[] = $project['op_id'];
                    if($project['country'] == null) $project['country'] = 'null';
                    if($project['city'] == null) $project['city'] = 'null';
                    $project['name'] = addslashes($project['name']);
                    $project['descr'] = addslashes($project['descr']);
                    if($project['logo_id'] <= 0) $project['logo_id'] = 'null';
                    $project['payed_items'] = $project['payed_items'] | '010';
                    $project['is_color']    = 't';
                    $prj->editPrj($project, false);
                }
                // ������� ������ ��������
                if($delete_color) {
                    $this->deleteDraftAccountOperation($delete_color);
                }
                break;
            // ���������� ��������
            case new_projects::OPCODE_KON:
            case new_projects::OPCODE_KON_NOPRO:
                require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/step_wizard_registration.php';
                
                $drafts  = new drafts();
                $draft   = $drafts->getDraft($option['parent_id'], $this->uid, 1);
                // ���� ��� �� �����������
                if(!$draft['prj_id']) {
                    $project_id = $draft['id'];
                    $error = $account->Buy($bill_id, $transaction_id, $option['op_code'], $this->uid, $option['descr'], $option['comment'], 1, 0);
                    $ok = ($bill_id > 0);
                    if($bill_id) {
                        $color = $DB->val("SELECT id FROM draft_account_operations WHERE parent_id = ? AND op_type = 'contest' AND option = 'color' AND uid = ?", $project_id, wizard::getUserIDReg());
                        $draft['billing_id'] = $bill_id;
                        $draft['folder_id']  = 'null';
                        $draft['payed']      = '0';
                        $draft['payed_items']= '000';
                        if(is_pro() && $color > 0) {
                            $draft['is_color']   = 't';
                        } else {
                            $draft['is_color']   = 'f';
                        }
                        $draft['win_date']   = date('d-m-Y', strtotime($draft['win_date']));
                        $draft['end_date']   = date('d-m-Y', strtotime($draft['end_date']));
                        $draft['is_bold']    = 'f';
                        $draft['user_id']    = $this->uid;
                        if($draft['country'] == null) $draft['country'] = 'null';
                        if($draft['city'] == null) $draft['city'] = 'null';
                        $draft['name'] = addslashes($draft['name']);
                        $draft['descr'] = addslashes($draft['descr']);
                        if($draft['logo_id'] <= 0) $draft['logo_id'] = 'null';
                        $prj = new new_projects();
                        $attachedfiles_tmpdraft_files = drafts::getAttachedFiles($option['parent_id'], 4);
                        
                        if ($attachedfiles_tmpdraft_files) {
                            $attachedfiles_tmpdraft_files = array_map(create_function('$a', 'return array("id" => $a);'), $attachedfiles_tmpdraft_files);
                        }
                        if($attachedfiles_tmpdraft_files) {
                            $month = date('Ym');
                            $dir = 'projects/upload/' . $month . '/';
                            $files = step_wizard_registration::transferFiles($attachedfiles_tmpdraft_files, 'file_projects', $dir);
                        }
                        $spec = $draft["categories"];
                        $spec = explode("|", $spec);
                        $spec = array(array('category_id'=>$spec[0], 'subcategory_id'=>$spec[1]));
                        
                        $prj->addPrj($draft, $files);                        
                        $prj->saveSpecs($draft["id"], $spec);
                        // ������� ���� �� ������� ������� ����� ��� ��������������� ��������
                        if($draft['id'] != $project_id && $draft['id'] > 0) {
                            if($this->sleep[$project_id]) {
                                foreach($this->sleep[$project_id] as $k=>$opt) {
                                    $opt['parent_id'] = $draft['id'];
                                    $this->billingOperation($opt);
                                }
                            } else {
                                //��������� �������� �� ������ ������
                                $update = array("parent_id" => $draft['id']);
                                $DB->update("draft_account_operations", $update, "parent_id = ? AND op_type = 'contest' AND uid = ?", $project_id, wizard::getUserIDReg()); 
                                $this->sleep_parent[$project_id] = $draft['id'];
                            }
                            $DB->update("draft_projects", array('prj_id' => $draft['id']), "id = ? AND uid = ?", $project_id, wizard::getUserIDReg());
                            
                        }
                    }
                }
                break;
            // ������� ������/�������
            case 53:
                $prj = new new_projects();
                if($this->sleep_parent[$option['parent_id']]) {
                    $option['parent_id'] = $this->sleep_parent[$option['parent_id']];
                }
                $project = $prj->getProject($option['parent_id']);
                if(!$project['id']) {
                    $this->sleep[$option['parent_id']][$option['id']] = $option;
                    return true;
                } else {
                    unset($this->sleep[$option['parent_id']]);
                }
                if($project['country'] == null) $project['country'] = 'null';
                if($project['city'] == null) $project['city'] = 'null';
                $project['name'] = addslashes($project['name']);
                $project['descr'] = addslashes($project['descr']);
                if($project['logo_id'] <= 0) $project['logo_id'] = 'null';
                $project['folder_id']  = 'null';
                
                $items = array();
                switch($option['option']) {
                    case 'top':
                        $project['top_days'] = $option['op_count'];
                        break;
                    case 'color':
                        $is_pay  = ($project['payed_items'] & '010');
                        if($is_pay != '010') { 
                            $project['payed_items'] = $project['payed_items'] | '010';
                            $project['is_color']    = 't';
                            $items['color']         = true;
                            if(is_pro()) {
                                $is_payed = true;
                                $prj->SavePayedInfo($items, $project['id'], null, $project['top_days']);
                                $prj->editPrj($project, false);
                            }
                        } else {
                            $is_payed = true;
                        }
                        break;
                    case 'bold':
                        $is_pay  = ($project['payed_items'] & '001');
                        if($is_pay != '001') { 
                            $project['payed_items'] = $project['payed_items'] | '001';
                            $project['is_bold']     = 't';
                            $items['bold']          = true;
                        } else {
                            $is_payed = true;
                        }
                        break;
                    case 'logo':
                        $is_pay  = ($project['payed_items'] & '100');
                        if($is_pay != '100') {
                            $key = md5(microtime());
                            $prj = new tmp_project($key);
                            $prj->init(1);
                            $fu = new CFile($option['src_id']);
                            $ext = $fu->getext();
                            $tmp_dir  = $prj->getDstAbsDir();
                            $tmp_name = $fu->secure_tmpname($tmp_dir, '.'.$ext);
                            $tmp_name = substr_replace($tmp_name,"",0,strlen($tmp_dir));
                            $fu->table = 'file_projects';
                            $r = $fu->_remoteCopy($tmp_dir.$tmp_name);
                            $project['payed_items'] = $project['payed_items'] | '100';
                            $project['logo_id']     = $fu->id;
                            $items['logo']          = true;
                            if ( $option['extra'] ) {
                                $project['link'] = $option['extra'];
                            }
                        } else {
                            $is_payed = true;
                        }
                        break;
                }
                
                if(!$is_payed) {
                    $error = $account->Buy($bill_id, $transaction_id, $option['op_code'], $this->uid, $option['descr'], $option['comment'], $option['ammount'], 0);
                    $ok = ($bill_id > 0);
                    $project['billing_id'] = $bill_id;

                    $prj->SavePayedInfo($items, $project['id'], $bill_id, $project['top_days']);
                    $prj->editPrj($project, false);
                } else {
                    $ok = true;
                }
                
                break;
            // ������� ������ �� �������
            case 61:
                $answers = new projects_offers_answers();
                $error = $answers->BuyByFM($this->uid, $option['op_count'], $transaction_id, 0);
                if (!$error) {
                    $ok = true;
                    $_SESSION['answers_ammount'] = $option['op_count'];
                    // ��������� ������
                    $step_frl = new step_freelancer();
                    $offers   = $step_frl->getWizardOffers($this->uid, $option['op_count']);
                    if($offers) {
                        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
                        $step_frl->log  = $this->log;
                        $step_frl->user = new users();
                        $step_frl->user->GetUserByUID($this->uid);
                        $step_frl->transferOffers($offers);
                    }
                }
                break;
        }
        
        return $ok;
    }
    
    /**
     * ��������� ������� ���� ���� ������� ����� ��������� ������ � ������ ��� �������
     * 
     * @global type $DB
     * @return type 
     */
    function updateColorProject() {
        global $DB;
        $sql = "SELECT p.*, dao.id as op_id 
                FROM draft_account_operations dao
                INNER JOIN projects p ON p.id = dao.parent_id AND p.user_id = dao.uid
                WHERE dao.op_code = 53 AND dao.uid = ?i AND dao.option = 'color'";
        return $DB->rows($sql, $this->uid);
    }
    
    /**
     * ������� �������� ������� ����������� ��� ������� ���
     * 
     * @global type $DB
     * @param type $op_code ��� ��������
     * @return type 
     */
    function clearBlockedOperations($op_code, $option = false) {
        global $DB;
        if($option) $option = "option = '{$option}'";
        return $DB->query("DELETE FROM draft_account_operations WHERE op_code = ?i AND uid = ?i " . ( $option? " AND {$option}" : "" ) , $op_code, $this->uid);
    }
    
    /**
     * ������� ���������� ��������
     * 
     * @global type $DB
     * @param type $id
     * @return type 
     */
    function deleteDraftAccountOperation($id) {
        global $DB;
        if(is_array($id)) {
            $where = "id IN (?l)";
        } else {
            $where = "id = ?i";
        }
        return $DB->query("DELETE FROM draft_account_operations WHERE {$where} AND uid = ?i", $id, $this->uid);
    }
    
    /**
     * ���������� �������� �� �� ������������
     * 
     * @global type $DB
     * @param type $uid �� ������������
     * @return boolean 
     */
    public function getDraftAccountOperations($uid = false) {
        global $DB;
        if(!$uid) $uid = $_SESSION['uid'];
        if(!$uid) return false;
        
       return $DB->rows("SELECT dao.*, p.name as project_name, dp.name as contest_name
                          FROM draft_account_operations dao
                          LEFT JOIN projects p ON p.id = dao.parent_id AND dao.op_type = 'project' AND p.user_id = dao.uid 
                          LEFT JOIN draft_projects dp ON dp.id = dao.parent_id AND dao.op_type = 'contest' AND dp.uid = dao.uid
                          WHERE dao.uid = ?", $uid);
    }
    
    /**
     * ���������� �������� �� �� ��������
     * 
     * @global type $DB
     * @param array|integer $operations  �� ��������, ����� ���� ������ ��
     * @param integer $uid
     * @return array 
     */
    public function getDraftAccountOperationsByIds($operations, $uid = false) {
        global $DB;
        if(!$uid) $uid = $_SESSION['uid'];
        if(!$uid) return false;
        
        $sql = "SELECT dao.*, p.name as project_name, dp.name as contest_name
                FROM draft_account_operations dao
                LEFT JOIN projects p ON p.id = dao.parent_id AND dao.op_type = 'project' AND p.user_id = dao.uid
                LEFT JOIN draft_projects dp ON dp.id = dao.parent_id AND dao.op_type = 'contest' AND dp.uid = dao.uid
                WHERE dao.uid = ?";
        
        if(is_array($operations)) {
            $sql .= " AND dao.id IN (?l)";
            
        } else {
            $sql .= " AND dao.id = ?";
        }
        
        return $DB->rows($sql, $uid, $operations);
    }
    
    /**
     * ��������� ������� ��������
     * 
     * @global type $DB
     * @param array $insert  ������ ��� ������
     * @return integer ID ����� 
     */
    public function addPaidOption($insert) {
        global $DB;
        
        return $DB->insert("wizard_billing", $insert, "id");
    }
    
    /**
     * ����������� ������� �����
     * 
     * @global type $DB
     * @param array   $update  ������ ��� ��������������
     * @param integer $id      �� ��������
     * @return boolean
     */
    public function editPaidOption($update, $id) {
        global $DB;
        
        return $DB->update("wizard_billing", $update, "id = ?i", $id);
    }
    
    /**
     * ����� ������� �������� ������������ �� �� ��������
     * 
     * @global type $DB
     * @param integer|array $id  �� �������� ��� ������ �� ��������
     * @return array 
     */
    public function getPaidOptionById($id) {
        global $DB;
        
        if(is_array($id)) {
            return $DB->rows("SELECT * FROM wizard_billing WHERE id IN (?l) AND wiz_uid = ?", $id, step_wizard::getWizardUserID());
        } else {
            return $DB->rows("SELECT * FROM wizard_billing WHERE id = ? AND wiz_uid = ?", $id, step_wizard::getWizardUserID());
        }
    }
    
    /**
     * ������� ������� ��������
     * 
     * @global type $DB
     * @param array $delete ������ �� ��� ��������
     * @return boolean 
     */
    public function deletePaidOptions($delete) {
        global $DB;
        if(!$delete) return false;
        return $DB->query("DELETE FROM wizard_billing WHERE id IN (?l) AND wiz_uid = ?", $delete, step_wizard::getWizardUserID());
    }
    
    /**
     * �������� ������� ����� ������� ����� ����������, ������� �� ������� �� ��������
     *  
     * @param array $option     ������� �������� ������������
     * @param array $selected   ��������� � ������ ������� �������� ������������
     */
    public function selectedPaidOption($options, $selected) {
        if($options) {
            $select = false;
            foreach($options as $payID => $val) {
                $payID = intval($payID);
                if(!$selected[$payID]) {
                    $delete[] = (int) $payID;
                } else {
                    $select[] = (int) $payID;
                }
            }
            
            if($delete) {
                // ������� �� ��������� �����
                $this->updateParentsOptions($delete);
                $this->deletePaidOptions($delete);
            }
            
            return $select;
        }
        
        return true;
    }
    
    /**
     * ��������� ��������� ������� ����� ��� �� ��������
     * 
     * @param array $selected    ������ �� ��������� �� �������� �����
     */
    public function updateParentsOptions($selected) {
        global $DB;
        
        $options = $this->getPaidOptionById($selected);
        if($options) {
            foreach ($options as $key => $option) {
                switch ($option['op_code']) {
                    // ������� ������
                    case 53:
                        switch ($option['option']) {
                            // ��������� �� �����
                            case 1:
                                $sql = "UPDATE wizard_projects SET top_count = null, payed = payed - {$option['ammount']} 
                                        WHERE id = ? AND wiz_uid = ?";
                                break;
                            // ��������� ������
                            case 2:
                                $sql = "UPDATE wizard_projects SET is_color = false, payed = payed - {$option['ammount']} 
                                        WHERE id = ? AND wiz_uid = ?";
                                break;
                            // ��������� ������
                            case 3:
                                $sql = "UPDATE wizard_projects SET is_bold = false, payed = payed - {$option['ammount']} 
                                        WHERE id = ? AND wiz_uid = ?";
                                break;
                            // �������
                            case 4:
                                $logo = $DB->val("SELECT logo_id FROM wizard_projects WHERE id = ? AND wiz_uid = ?", $option['parent'], step_wizard::getWizardUserID());
                                $cfile = new CFile();
                                $cfile->Delete($logo);
                                $sql = "UPDATE wizard_projects SET logo_id = null, logo_link = null, payed = payed - {$option['ammount']} 
                                        WHERE id = ? AND wiz_uid = ?";
                                break;
                        }
                        
                        $DB->query($sql, $option['parent'], step_wizard::getWizardUserID());
                        break;
                    // ������� -- ������� �� ����� ������� �� ���� �� �� ������� ��� ����� ����� � �������� � ����� ������������ ����� ��� ������������ � �������� 
                    case 9:
                        //$this->_db->query("DELETE FROM wizard_projects WHERE id = ?i AND wiz_uid = ?", $option['parent'], step_wizard::getWizardUserID());
                        break;
                }
            }
        }
    }
    
    /**
     * ��������� ��� ��������� ������� �������� � ��������� ��������
     * 
     * @param array $selected ������� ��������
     */
    public function transferPaidOptionsToDraft($selected) {
        // �� ������ ������ ����� ����������� ����� �� ���� ����� �� ���� ��������
        $options = $this->getPaidOptionById($selected);
        $log  = new log('wizard/transfer-'.SERVER.'-%d.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        if($options) {
            foreach($options as $option) {
                $id = $this->createDraftAccountOperation($option);
                if($id) {
                    $delete[] = $id;
                } else {
                    $log->writeln("Error transfer paid option to draft - user (" . wizard::getUserIDReg() . "|" . step_wizard::getWizardUserID() . ") - option #{$option['id']} (wizard_billing)");
                }
            }
            return $delete;
        }
        
        return false;
    }
    
    /**
     * �������� ���������� ������� ����� �� ������ ����� ��������� � �������
     *  
     * @param type $option  ������ ����� ��������� � ������� @see table - wizad_billing
     * @return null|boolean     
     */
    public function createDraftAccountOperation($option) {
        global $DB;
        switch($option['op_code']) {
            // ���������� ��������
            case 9:
            case 106:
                $descr     = "���������� ��������";
                $count     = 1;
                $op_type   = 'contest';
                $parent_id = $option['parent'];
                $src_id = $str_option = null;
                break;
            // ������� ������/�������
            case 53:
                $step_emp = new step_employer();
                $project   = $step_emp->getProjectById($option['parent']);
                $parent_id = $option['parent'];
                if($project['kind'] == 7) {
                    $title   = "�������";
                    $op_type = 'contest';
                } else {
                    $title   = "������";
                    $op_type = 'project';
                }
                $count  = 1;
                $src_id = $str_option = null;
                $descr  = "������� {$title} / ";
                switch($option['option']) {
                    case step_employer::PROJECT_OPTION_TOP:
                        $str_option  = 'top';
                        $count   = $project['top_count'];
                        $descr  .= "����������� ������� �� " . (int)$project['top_count'] . " ". ending($project['top_count'], "����", "���", "����");
                        break;
                    case step_employer::PROJECT_OPTION_COLOR:
                        $str_option  = 'color';
                        $descr  .= "��������� �����";
                        break;
                    case step_employer::PROJECT_OPTION_BOLD:
                        $str_option  = 'bold';
                        $descr  .= "������ �����";
                        break;
                    case step_employer::PROJECT_OPTION_LOGO: 
                        $str_option  = 'logo';
                        $descr  .= "�������";
                        $src_id  = $project['logo_id'];
                        break;
                }
                break;
            // ������� �������� ���
            case 48:
            case 49:
            case 50:
            case 51:
            case 76:
            case 15:
                $descr = "������� PRO";
                $count = 1;
                $src_id = $parent_id = $str_option = $op_type = null;
                break;
            // ������� ������� �������
            case step_freelancer::OFFERS_OP_CODE:
                $descr  = "������� ������� �� ������� (���-��: {$option['option']})";
                $count  = $option['option'];
                $src_id = $parent_id = $str_option = $op_type = null;
                break;
        }
        
        $pay_options = array(
            "uid"       => wizard::getUserIDReg(),
            "op_code"   => $option['op_code'],
            "op_type"   => $op_type,
            "option"    => $str_option,
            "parent_id" => $parent_id,
            "src_id"    => $src_id,
            "op_count"  => $count,
            "ammount"   => $option['ammount'],
            "descr"     => $descr,
            "comment"   => $descr,
            "status"    => null
        );
        
        $id = $DB->insert("draft_account_operations", $pay_options, 'id');
        
        if($id) {
            $this->draft[] = $id;
            return $option['id'];
        }
        
        return false;
    }
    
    /**
     * ������� ����� ��������� �������������
     * 
     * @return array 
     */
    public function getPayedOptions() {
        global $DB;
        $sql = "SELECT wb.*,  oc.op_name, wp.name as project_name, wp.logo_id, wp.is_color, wp.is_bold, wp.top_count
                FROM wizard_billing wb
                INNER JOIN op_codes oc ON oc.id = wb.op_code
                LEFT JOIN wizard_projects wp ON (wp.id = wb.parent AND wp.wiz_uid = wb.wiz_uid AND (wb.type = ? OR wb.type = ?))
                WHERE wb.wiz_uid = ?
                ORDER by wb.type DESC, wb.parent ASC";
        return  $DB->rows($sql, step_employer::BILL_TYPE_CONTEST, step_employer::BILL_TYPE_PROJECT, step_wizard::getWizardUserID()); 
    }
    
    /**
     * ������� ��� ������� ����� ������������ ��� ������ ��� ����������� �������
     * @param integer $parent = null : �������� ������� ����� (�������� id �������)
     */
    function clearPayedOptions ($parent = null) {
        global $DB;
        if ($parent) {
            $par = " AND parent = " . $parent;
        } else {
            $par = "";
        }
        $sql = "DELETE FROM wizard_billing WHERE wiz_uid = ?" . $par;
        return $DB->query($sql, step_wizard::getWizardUserID());
    }
    
    /**
     * ���������� ������ � ��������, ������� �� �������, ���� �� ��� ���
     * 
     * @global type $DB
     * @return type 
     */
    function showProjectsFeedbacks() {
        global $DB;
        return $DB->query("UPDATE projects_feedbacks SET show = TRUE WHERE user_id = ?i;", $this->uid);
    }
}

?>