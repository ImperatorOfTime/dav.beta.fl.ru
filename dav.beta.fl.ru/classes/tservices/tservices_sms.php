<?php

require_once $_SERVER['DOCUMENT_ROOT']."/classes/sms_gate_a1.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/tu/models/TServiceOrderModel.php';
require_once $_SERVER['DOCUMENT_ROOT']."/classes/sbr.php";

/**
 * ��� ����������� �� ��
 */
class tservices_sms extends sms_gate_a1
{
    /**
     * �������������� �������
     */
    const STATUS_NEW_RESERVE        = 100;
    const STATUS_CHANGE_ORDER       = 101;
    const STATUS_RESERVE_ACCEPT     = 102;
    
    /**
     * ��������� ��������� ������ ��
     * 
     * @var type 
     */
    public $txt_order_status = array(
        //TServiceOrderModel::STATUS_NEW      => '��� �������� ������ �� FL.ru. ����������, ����������� ����� #%d ��� ���������� �� ����.',
        self::STATUS_NEW_RESERVE            => '��� ��������� ����� �� FL.ru � ��������������� �����. ����������, ����������� ����� #%d ��� ���������� �� ����.',
        //self::STATUS_CHANGE_ORDER           => '�������� ������� ��������� ������ �� ������. ����������, ����������� ����� #%d ��� ���������� �� ����.',
        //TServiceOrderModel::STATUS_CANCEL   => '� ���������, �������� ������� ���� ����� ������ #%d �� FL.ru.',
        //TServiceOrderModel::STATUS_ACCEPT   => '����������� ���������� ��� ����� ������ #%d �� FL.ru � ����� ��� ����������.',
        //self::STATUS_RESERVE_ACCEPT         => '����������� ���������� ����� #%d. ����������, �������������� ������ �� ����� FL.ru, ����� ����������� ����� ���������� ������.',
        //TServiceOrderModel::STATUS_DECLINE  => '� ���������, ����������� ��������� �� ���������� ������ ������ #%d �� FL.ru.',
        //TServiceOrderModel::STATUS_FRLCLOSE => '�������������� �� ������ #%d ���������. ����������, �������� ����� � ������ �� FL.ru.',
        //TServiceOrderModel::STATUS_FRLCLOSE => '����������� �������� ������ �� ������. ����������, ��������� � ����� #%d ��� ������������ � ����������� ������.',
        //TServiceOrderModel::STATUS_EMPCLOSE => '�������������� �� ������ #%d ���������. ����������, �������� ����� � ������ �� FL.ru.'
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
     * ��������� ��� �� ��������� ������ ��
     * 
     * @param int $status
     * @param int $num
     * @return boolean
     */
    public function sendOrderStatus($status, $id)
    {
        if(!isset($this->txt_order_status[$status]) || !$this->isPhone()) return FALSE;
        $message = sprintf($this->txt_order_status[$status], $id);
        return $this->sendSMS($message);
    }

    



    /**
     * ������� ���� ����
     * @return TServiceModel
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