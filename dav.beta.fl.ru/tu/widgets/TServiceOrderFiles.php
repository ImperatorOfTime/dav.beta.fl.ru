<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_order_history.php');

/**
 * Class TServiceOrderHistory
 *
 * ������ - ���� ���������� �� ������� ��������� ������
 */
class TServiceOrderFiles extends CWidget 
{
        protected $order_files;

        public function run() 
        {
            //�������� ������
            $this->render('t-service-order-files', array(
                'files' => $this->order_files
            ));
	}
}