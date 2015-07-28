<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_helper.php');

/**
 * Class TServiceOrderChangeCostPopup
 * ������ ���������� ����� � ������ �� ��� ��������� ���������, ������ � ���� ������� ��� ���������
 */

class TServiceOrderChangeCostPopup extends CWidget 
{
        public $order;
        
        /**
         * ����� ����� �������� � ����� ������ ������
         * �� render
         * 
         * @return boolean
         */
        public function run() 
        {            
            //����������� ��� ����� ����� � ��������� �� ����� �� � �������� ��� ���
            $sufix = ((tservices_helper::isAllowOrderReserve($this->order['category_id']))?'-reserve':'');
            $this->render("t-service-order-change-cost{$sufix}-popup", array('order' => $this->order));
	}
}