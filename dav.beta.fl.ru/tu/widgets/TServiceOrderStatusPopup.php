<?php

/**
 * Class TServiceOrderStatusPopup
 * ������ ���������� ����� ���������� ����� ������ ������� � ������ ��
 */

class TServiceOrderStatusPopup extends CWidget 
{
    public $data = array();

    public function init($data = array()) 
    {
        parent::init();
        if(!empty($data)) $this->data = $data;
    }

    public function run() 
    {
        //�������� ������
        $this->render("t-service-order-status-frl-popup", $this->data);
    }
}