<?php

require_once $_SERVER['DOCUMENT_ROOT']."/classes/sms_gate_a1.php";
require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects_status.php";
require_once $_SERVER['DOCUMENT_ROOT']."/classes/sbr.php";

/**
 * ��� ����������� �� ��������
 */
class ProjectsSms extends sms_gate_a1
{
    
    /**
     * ��������� ��������� �������
     * ���� ������ �� ����� ��������� �� ������������� ��������� ��� ������������� ���� �������
     * 0 - ������� ������, 9 - ������������ ������
     * 
     * @var type 
     */
    public $txt_project_status = array(
        projects_status::STATUS_NEW => array(
            0 => '�������� ������ ��� ������������ ������� �� FL.ru. ����������, ����������� ������ %s/projects/%d/ ��� ���������� �� ����.',
            9 => '��� ��������� ������������ ������ �� FL.ru. ����������, ����������� ������ %s/projects/%d/ ��� ���������� �� ����.'
        ),
        projects_status::STATUS_ACCEPT => array( 
            0 => '��������� ���� ����������� ���������� ������ %s/projects/%d/ � ����� ��� ����������. �� �������� ��������� ������ �� ��������� ��������������.',
            9 => '����������� ���������� ������� � ������������ ��� ������� %s/projects/%d/ � ����� ��� ����������. �� �������� ��������� ������ �� ��������� ��������������.'
        ),
        projects_status::STATUS_EMPCLOSE => '�������� �������� �������������� � ���� �� ������� %s/projects/%d/. �� �������� ��� �����.',
        projects_status::STATUS_FRLCLOSE => '����������� �������� �������������� � ���� �� ������� %s/projects/%d/. �� �������� ��� �����.',
        
        projects_status::STATUS_DECLINE => '� ���������, ����������� ��������� �� ���������� ������ ������� %s/projects/%d/.',
        projects_status::STATUS_CANCEL => '� ���������, �������� ������� ���� ������ %s/projects/%d/.',
        
        //��� ��������� �������������� ������ �� ������� (���� ��������� � ���) ��� �������������� ������ (� � ���, � ��� ���)
        100 => '�������� ������� ��� ����� �� ������� %s/projects/%d/. �� �������� �������� �������� �����.',
        102 => '�������� ������� ��� ����� �� ������� %s/projects/%d/.',
        //��� ��������� �������������� ������ �� ������� (���� ��������� ��� ���)
        101 => '�������� ������� ��� ������� ����� �� ������� %s/projects/%d/. �� ������ ������ PRO https://www.fl.ru/payed/ � ������� ����� ������� ����.'
    );


    /**
     * ���������� �� ����?
     * 
     * @return type
     */
    public function isPhone()
    {
        return !empty($this->_msisdn);
    }

    

    /**
     * ��������� ��� �� ��������� �������
     * 
     * @param int $status - ������ �������
     * @param int $id - ID �������
     * @return boolean
     */
    public function sendStatus($status, $id, $kind = 0)
    {
        if(!isset($this->txt_project_status[$status]) || !$this->isPhone()) return FALSE;
        $kind = ($kind == 9)?$kind:0;//���� ������ ���� �� ������������
        $txt = is_array($this->txt_project_status[$status])?$this->txt_project_status[$status][$kind]:$this->txt_project_status[$status];
        $message = sprintf($txt, $GLOBALS['host'], $id);
        
        return $this->sendSMS($message);
    }

    



    /**
     * ������� ���� ����
     * @return projects_sms
     */
    public static function model($uid) 
    {
        $phone = '';
        $reqv = sbr_meta::getUserReqvs($uid);
        
        if($reqv)
        {
            $ureqv = $reqv[$reqv['form_type']];
            $phone = $ureqv['mob_phone'];
        }

        $class = get_called_class();
        return new $class($phone);
    }
}