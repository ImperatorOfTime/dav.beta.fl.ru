<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/projects_helper.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/employer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/template.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_sms.php");


/**
 * ���������� �������� �����
 */
define('PROJECTS_TPL_MAIL_PATH', $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/projects/');

/**
 * Class tservices_smail
 * ����� ��� ������ � ��������� ����� ��� ��
 */
class projects_smail extends smail {

    protected $is_local = FALSE;

    public function __construct() {
        parent::__construct();

        $server = defined('SERVER') ? strtolower(SERVER) : 'local';
        $this->is_local = ($server == 'local');
    }

    /**
     * �������� ����� ��������� ������� ����� ��� �� ������ ��������� 
     * � ����� ��������� ����������� �������� � ���� �� ������ �� ������� ��
     * 
     * @todo: ���� ������ ���������� ����������� �������� �� ����������� ;)
     * 
     * @param string $method
     * @param type $arguments
     * @return boolean
     */
    public function __call($method, $arguments) {
        if ($this->is_local) return FALSE;

        $method = '_' . $method;
        if (method_exists($this, $method)) {
            call_user_func_array(array($this, $method), $arguments);
        }

        return TRUE;
    }

    /**
     * ����������� ���������� � ������ ��� ������������
     * 
     * @param type $project
     */
    public function _onSetExecutorFrl($project) {
        $frl = new freelancer();
        $frl->GetUserByUID($project['exec_id']);

        $emp = new employer();
        $emp->GetUserByUID($project['user_id']);

        $this->subject = "��� ���������� ����� ������������ �� ������� �� ����� FL.ru";
        $this->recipient = $this->_formatFullname($frl, true);
        $this->message = Template::render(
            PROJECTS_TPL_MAIL_PATH . 'set_executor_frl.tpl.php', array(
                'project_title' => $project['name'],
                'project_url' => $GLOBALS['host'] . getFriendlyURL("project", $project),
                'accept_url' => $GLOBALS['host'] . projects_helper::getStatusUrl($project['id'], 'accept', $frl->uid),
                'decline_url' => $GLOBALS['host'] . projects_helper::getStatusUrl($project['id'], 'decline', $frl->uid),
                'emp_fullname' => $this->_formatFullname($emp)
            )
        );

        return $this->send('text/html');
    }
    
    /**
     * ����������� ��������� � �������� ������ ������
     * 
     * @param type $project
     */
    public function _onSetExecutorEmp($project) {
        $frl = new freelancer();
        $frl->GetUserByUID($project['exec_id']);

        $emp = new employer();
        $emp->GetUserByUID($project['user_id']);

        $this->subject = "��������� ����� �� ���������� ���� ����������� � �������";
        $this->recipient = $this->_formatFullname($emp, true);
        $this->message = Template::render(
            PROJECTS_TPL_MAIL_PATH . 'set_executor_emp.tpl.php', array(
                'project_title' => $project['name'],
                'project_url' => $GLOBALS['host'] . getFriendlyURL("project", $project),
                'cancel_url' => $GLOBALS['host'] . projects_helper::getStatusUrl($project['id'], 'cancel', $emp->uid),
                'frl_fullname' => $this->_formatFullname($frl)
            )
        );

        return $this->send('text/html');
    }
    
    /**
     * ����������� ����������� � ������ �����
     * 
     * @param type $project
     */
    public function _onStartWorkingFrl($project, $offer) {
        
        //$frl = new freelancer();
        //$frl->GetUserByUID($project['exec_id']);

        //$emp = new employer();
        //$emp->GetUserByUID($project['user_id']);

        $this->subject = "������ ����� �� �������";
        $this->recipient = $this->_formatFullname(&$offer, true);
        $this->message = Template::render(
            PROJECTS_TPL_MAIL_PATH . 'start_working_frl.tpl.php', array(
                'project_title' => $project['name'],
                'project_url' => $GLOBALS['host'] . getFriendlyURL("project", $project),
                'emp_login' => $project['login'],
                'emp_fullname' => $this->_formatFullname(&$project)
            )
        );
        return $this->send('text/html');
    }
    
    /**
     * ����������� ��������� � ������������� ������� � ������ �����
     * 
     * @param type $project
     */
    public function _onStartWorkingEmp($project, $offer) {
        
        //$frl = new freelancer();
        //$frl->GetUserByUID($project['exec_id']);

        //$emp = new employer();
        //$emp->GetUserByUID($project['user_id']);

        $this->subject = "����������� ����� ���������� ����� �� �������";
        $this->recipient = $this->_formatFullname(&$project, true);
        $this->message = Template::render(
            PROJECTS_TPL_MAIL_PATH . 'start_working_emp.tpl.php', array(
                'project_title' => $project['name'],
                'project_url' => $GLOBALS['host'] . getFriendlyURL("project", $project),
                'frl_login' => $offer['login'],
                'frl_fullname' => $this->_formatFullname(&$offer)
            )
        );
        return $this->send('text/html');
    }
    
    
    
    /**
     * ��������� ����������� � ������ ������ ��� ��������
     * 
     * @param type $project
     * @param type $offer
     */
    public function onStartWorking($project, $offer)
    {
        $ret_frl = $this->onStartWorkingFrl($project, $offer);
        $ret_emp = $this->onStartWorkingEmp($project, $offer);
        
        //��������� ��� ��������� � ������������� � ������ ����� ������������
        ProjectsSms::model($project['user_id'])->sendStatus($project['status'], $project['id'], $project['kind']);
        
        return $ret_frl && $ret_emp;
    }

    

    /**
     * ����������� ��������� �� ������ �� ������� �� ������� �����������
     * 
     * @param type $project
     */
    public function _onRefuseEmp($project, $offer) {
        
        //$frl = new freelancer();
        //$frl->GetUserByUID($project['exec_id']);

        //$emp = new employer();
        //$emp->GetUserByUID($project['user_id']);

        $this->subject = "����������� ��������� �� ���������� ������ �������";
        $this->recipient = $this->_formatFullname(&$project, true);
        $this->message = Template::render(
            PROJECTS_TPL_MAIL_PATH . 'refuse_emp.tpl.php', array(
                'project_title' => $project['name'],
                'project_url' => $GLOBALS['host'] . getFriendlyURL("project", $project),
                'frl_login' => $offer['login'],
                'frl_fullname' => $this->_formatFullname(&$offer)
            )
        );
        
        $ret = $this->send('text/html');
        
        //��������� ��� ���������
        ProjectsSms::model($project['user_id'])->sendStatus($offer['status'], $project['id'], $project['kind']);
        
        return $ret;
    }
    
    /**
     * ����������� ����������� �� ������ ������� �� ������� ���������
     * 
     * @param type $project
     */
    public function _onRefuseFrl($project, $offer) {
        
        //@todo: ��� ������������� �� ���� � ��������� ���������� � project � � ����������� � $offer
        
        //$frl = new freelancer();
        //$frl->GetUserByUID($project['exec_id']);
        
        //$emp = new employer();
        //$emp->GetUserByUID($project['user_id']);
        
        $this->subject = "�������� ������� ���� ����������� �� �������";
        $this->recipient = $this->_formatFullname(&$offer, true);
        $this->message = Template::render(
            PROJECTS_TPL_MAIL_PATH . 'refuse_frl.tpl.php', array(
                'project_title' => $project['name'],
                'project_url' => $GLOBALS['host'] . getFriendlyURL("project", $project),
                'emp_login' => $project['login'],
                'emp_fullname' => $this->_formatFullname(&$project)
            )
        );
                
        $ret = $this->send('text/html');
        
        //��������� ��� ����������
        ProjectsSms::model($offer['user_id'])->sendStatus($offer['status'], $project['id'], $project['kind']);
        
        return $ret;
    }
    
    /**
     * ����������� ������ ������� � ���������� �������
     * 
     * @param type $project
     */
    public function _onFinish($project, $to_frl = true) {
        $params = array(
            'project_title' => $project['name'],
            'project_url' => $GLOBALS['host'].'/projects/' . $project['id']
        );
        
        $frl = new freelancer();
        $frl->GetUserByUID($project['exec_id']);

        $emp = new employer();
        $emp->GetUserByUID($project['user_id']);
        
        if ($to_frl) { //������ ���������� �����������
            $recipient = $this->_formatFullname($frl, true);
            $params['emp_login'] = $emp->login;
            $params['emp_fullname'] = $this->_formatFullname($emp);
            $params['opinions_url'] = $GLOBALS['host'].'/users/'.$emp->login.'/opinions/';
            
            $subject = "�������� �������� �������������� �� �������";
            $template = 'finish_no_fb_frl.tpl.php'; //��� ������
            if (isset($project['emp_feedback']) && isset($project['emp_rating'])) {
                $params['rating'] = $project['emp_rating'];
                $params['opinions_url'] = $GLOBALS['host'].'/users/'.$frl->login.'/opinions/';
                $params['text'] = $project['emp_feedback'];
                $subject = "�������� �������� �������������� �� ������� � ������� ��� �����";
                $template = 'finish_fb_frl.tpl.php'; //� �������
                if ($project['emp_rating'] == 1 && $frl->is_pro != 't') {
                    $template = 'finish_pos_fb_frl.tpl.php'; //��-��� � ������������� �������
                }
            }
        } else {
            $recipient = $this->_formatFullname($emp, true);
            $params['frl_login'] = $frl->login;
            $params['frl_fullname'] = $this->_formatFullname($frl);
            $params['opinions_url'] = $GLOBALS['host'].'/users/'.$frl->login.'/opinions/';
            
            $subject = "����������� �������� ������ �� ������ �������";
            $template = 'finish_no_fb_emp.tpl.php'; //��� ������
            if (isset($project['frl_feedback']) && isset($project['frl_rating'])) {//� �������
                $params['rating'] = $project['frl_rating'];
                $params['opinions_url'] = $GLOBALS['host'].'/users/'.$emp->login.'/opinions/';
                $params['text'] = $project['frl_feedback'];
                $subject = "����������� �������� ������ �� ������ ������� � ������� ��� �����";
                $template = 'finish_fb_emp.tpl.php'; 
            }
        }
        
        $this->subject = $subject;
        $this->recipient = $recipient;
        $this->message = Template::render(PROJECTS_TPL_MAIL_PATH.$template, $params);
        $ret = $this->send('text/html');
        
        
        //���������� ���
        $status = $project['status'];
        $user_id = ($to_frl) ? $project['exec_id'] : $project['user_id'];

        if ($to_frl && !empty($project['emp_feedback'])) 
        {
            if (($frl->is_pro == 't' && $project['emp_rating'] > 0) || $project['emp_rating'] < 0) $status = 100;
            elseif ($frl->is_pro == 'f' && $project['emp_rating'] > 0) $status = 101;
        }

        ProjectsSms::model($user_id)->sendStatus($status, $project['id']);
        
        
        return $ret;
    }
    
    /**
     * ����������� ������ ������� � ����� ������
     * 
     * @param type $project
     */
    public function _onFeedback($project, $to_frl = true) {
        $params = array(
            'project_title' => $project['name'],
            'project_url' => $GLOBALS['host'].'/projects/' . $project['id']
        );
        
        $frl = new freelancer();
        $frl->GetUserByUID($project['exec_id']);

        $emp = new employer();
        $emp->GetUserByUID($project['user_id']);
        
        if ($to_frl) { //������ ���������� �����������
            $recipient = $this->_formatFullname($frl, true);
            $subject = "�������� ������� ��� ����� � �������������� � �������";

            $params['emp_login'] = $emp->login;
            $params['emp_fullname'] = $this->_formatFullname($emp);
            $params['rating'] = $project['emp_rating'];
            $params['opinions_url'] = $GLOBALS['host'].'/users/'.$frl->login.'/opinions/';
            $params['text'] = $project['emp_feedback'];
            $template = 'fb_frl.tpl.php'; //� �������
            if ($project['emp_rating'] == 1 && $frl->is_pro != 't') {
                $template = 'pos_fb_frl.tpl.php'; //��-��� � ������������� �������
            }
        } else {
            $recipient = $this->_formatFullname($emp, true);
            $subject = "����������� ������� ��� ����� � �������������� � �������";
            
            $params['frl_login'] = $frl->login;
            $params['frl_fullname'] = $this->_formatFullname($frl);
            $params['rating'] = $project['frl_rating'];
            $params['opinions_url'] = $GLOBALS['host'].'/users/'.$emp->login.'/opinions/';
            $params['text'] = $project['frl_feedback'];

            $template = 'fb_emp.tpl.php'; 
        }
        
        $this->subject = $subject;
        $this->recipient = $recipient;
        $this->message = Template::render(PROJECTS_TPL_MAIL_PATH.$template, $params);
        $ret = $this->send('text/html');
        
        
        
        //���������� ���
        if($to_frl && isset($project['emp_feedback']))
        {
            $status = null;
            
            if(($frl->is_pro == 't' && $project['emp_rating'] > 0) || $project['emp_rating'] < 0) 
            {
                $status = (@$project['frl_feedback_id'] > 0)?102:100;
            }
            elseif($frl->is_pro == 'f' && $project['emp_rating'] > 0) 
            {
                $status = 101;
            }
            
            if($status) ProjectsSms::model($frl->uid)->sendStatus($status,$project['id']);
        }
        

        return $ret;
    }
    
    /**
     * ����������� ����������� �� �������� ���������� ����� ������� �������
     * 
     * @param type $frl_id
     */
    public function _onPublicFrl($frl_id) {
        $frl = new freelancer();
        $frl->GetUserByUID($frl_id);

        $this->subject = "������� ������������ ����� ������� ������ � �������������� � ��������";
        $this->recipient = $this->_formatFullname($frl, true);
        $this->message = Template::render(
            PROJECTS_TPL_MAIL_PATH . 'public_frl.tpl.php', array(
                'opinions_url' => $GLOBALS['host'].'/users/'.$frl->login.'/opinions/'
            )
        );
        return $this->send('text/html');
    }

    /**
     * ��������� ����� �����
     * @todo �� ������ ����� ��� �����?
     * 
     * @param type $user
     * @param type $with_email
     * @return type
     */
    protected function _formatFullname(&$user, $with_email = false) {
        $u = (is_object($user)) ? array(
            'uname' => $user->uname,
            'usurname' => $user->usurname,
            'login' => $user->login,
            'email' => $user->email
                ) : $user;

        $fullname = "{$u['uname']}";
        $fullname .= ((empty($fullname)) ? "" : " ") . "{$u['usurname']}";
        $fullname .= (empty($fullname)) ? "{$u['login']}" : " [{$u['login']}]";
        if ($with_email)
            $fullname .= " <{$u['email']}>";
        return $fullname;
    }

}
