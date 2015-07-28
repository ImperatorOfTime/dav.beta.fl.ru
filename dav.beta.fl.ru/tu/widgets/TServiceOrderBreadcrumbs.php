<?php
/**
 * Class TServiceOrderBreadcrumbs
 *
 * ������ - ���� ������� ������
 */
class TServiceOrderBreadcrumbs extends CWidget 
{
    const ORDER_TEXT = '����� �%d %s(%s)';
    const TEXT_TSERVICE = '<a class="b-layout__link_no-decorat" href="%s">������</a> ';
    const TEXT_PROJECT = '<a class="b-layout__link_no-decorat" href="/projects/%d">�� �������</a> ';
    
    const PAY_DIRECT = '� ������ �������';
    const PAY_RESERVE = '� ������� ����� ���������� ������';
    
    protected $order;
    protected $is_emp;

    public function run() 
    {
        //�������� ������
        $this->render('t-service-order-breadcrumbs', array(
            'url_all' => $this->is_emp ? $this->getEmpUrlAll() : '/tu-orders/',
            'order_text' => $this->getOrderText()
        ));
	}
    
    public function setOrder($order)
    {
        $this->order = $order;
    }
    
    private function getEmpUrlAll()
    {
        return '/users/' . $this->order['employer']['login'] . '/tu-orders/';
    }
    
    private function getOrderText()
    {
        switch ($this->order['type']) {
            case TServiceOrderModel::TYPE_TSERVICE:
                $service = sprintf(self::TEXT_TSERVICE, 
                        tservices_helper::card_link($this->order['tu_id'], $this->order['title']));
                break;

            case TServiceOrderModel::TYPE_PROJECT:
                $service = sprintf(self::TEXT_PROJECT, $this->order['tu_id']);
                break;
            
            case TServiceOrderModel::TYPE_PERSONAL:
                $service = '';
                break;
        }

        $pay_text = isset($this->order['reserve']) ? self::PAY_RESERVE : self::PAY_DIRECT;
        
        return sprintf(self::ORDER_TEXT, $this->order['id'], $service, $pay_text);
    }
}