<?php


require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesTServiceOrderModel.php');


class OrderStatusIndicator extends CWidget
{
    public $is_ajax = false;
    protected $stages_list = array();
    protected $active_status = NULL;
    public $order = NULL;
    protected $paytype = TServiceOrderModel::PAYTYPE_RESERVE;

    public function init() 
    {
        $_arbitrage = false;
        
        if($this->order){
            $this->paytype = $this->order['pay_type'];
            switch($this->paytype){
                case TServiceOrderModel::PAYTYPE_DEFAULT:
                    $this->active_status = $this->order['status'];
                    break;
                
                case TServiceOrderModel::PAYTYPE_RESERVE:
                    $reserve = clone $this->order['reserve'];
                    $reserve->setReserveDataByKey('src_status', $this->order['status']);
                    $this->active_status = $reserve->getReserveOrderStatus();
                    $_arbitrage = $reserve->isArbitrage();
                    break;
            }
        }

        $this->active_status = ($this->active_status !== NULL)?
                intval($this->active_status):
                $this->active_status;
        
        //������ ������
        if ($this->active_status === NULL || 
            $this->paytype == TServiceOrderModel::PAYTYPE_DEFAULT) {
        
            $_default_stages = array(
                array(
                    'status' => NULL,
                    'title' => '�������� ������',
                    'texts' => '������������ ��������� �������, �������, ������ ���������� ������ � ������ ���� ������.'
                ),

                array(
                    'status' => NULL,
                    'adv' => true,
                    'title' => '�������� ��������',
                    'texts' => array(
                        '��� ������ � ����� ������ � <b>������ ������</b>, 
                         ��� ������� �� �������������� ����������� ��� ��������� 
                         �� ��������, ������ � ������ ���������� ������.',

                        '��� ���������� ������ � ������ ����������� ������ ����� 
                         �<a href="/promo/bezopasnaya-sdelka/" target="_blank"><b>���������� ������</b></a>�'
                    )
                ),

                array(
                    'status' => TServiceOrderModel::STATUS_NEW,
                    'title' => '������������ �������',
                    'texts' => '���������� ������� � ������� ���������� ������. ������������� ������ ������������'
                ),

                array(
                    'status' => TServiceOrderModel::STATUS_NEW,
                    'adv' => true,
                    'title' => '�������� ��������',
                    'texts' => array(
                        '��� ������ � ����� ������ � <b>������ ������</b>, 
                         ��� ������� �� �������������� ����������� ��� ��������� 
                         �� ��������, ������ � ������ ���������� ������.',

                        '��� ���������� ������ � ������ ����������� ������ ����� 
                         �<a href="/promo/bezopasnaya-sdelka/" target="_blank"><b>���������� ������</b></a>�'
                    )
                ),

                array(
                    'status' => array(
                        TServiceOrderModel::STATUS_DECLINE,
                        TServiceOrderModel::STATUS_CANCEL
                     ),
                    'title' => '������ ������',
                    'break' => true
                ),

                array(
                    'status' => array(
                        TServiceOrderModel::STATUS_ACCEPT,
                        TServiceOrderModel::STATUS_FIX
                    ),
                    'title' => '���������� ������',
                    'texts' => '������� ���������� ������� � ������ �� ��������� ���������� ��������� ���������� ������.'
                ),

                array(
                    'status' => TServiceOrderModel::STATUS_FRLCLOSE,
                    'title' => '���������� ������',
                    'texts' => '��������� ���������� ���������� ������, �������� ������ � ������� � ��������������.'
                )
            );
        
            $this->stages_list[TServiceOrderModel::PAYTYPE_DEFAULT] = $_default_stages;
        }    
            
        
        //����� ���������� ������
        if ($this->active_status === NULL || 
            $this->paytype == TServiceOrderModel::PAYTYPE_RESERVE) {        
        
            $_reserve_stages = array(            
                array(
                    'status' => NULL,
                    'title' => '�������� ������',
                    'texts' => '������������ ��������� �������, �������, ������ ���������� ������ � ������ ���� ������.'
                ),

                array(
                    'status' => ReservesTServiceOrderModel::ReserveOrderStatus_Negotiation,
                    'title' => '������������ �������',
                    'texts' => '���������� ������� � ������� ���������� ������. ������������� ������ ������������'
                ),            

                array(
                    'status' => ReservesTServiceOrderModel::ReserveOrderStatus_Cancel,
                    'title' => '������ ������',
                    'break' => true
                ),            

                array(
                    'status' => ReservesTServiceOrderModel::ReserveOrderStatus_Reserve,
                    'title' => '��������������',
                    'texts' => '�������������� ���������� ����� ������ �� ������. ������ ������������� � �������� �� �����.'
                ),

                array(
                    'status' => ReservesTServiceOrderModel::ReserveOrderStatus_InWork,
                    'title' => '���������� ������',
                    'texts' => '������� ���������� ������� � ������ �� ��������� ���������� ��������� ���������� ������.'
                ),

                array(
                    'status' => ReservesTServiceOrderModel::ReserveOrderStatus_Arbitrage,
                    'exclude' => !$_arbitrage,
                    'title' => '��������',
                    'texts' => '������������ ������ ��������, ��������� ������� � ������� ��� �������� �����.'
                ),

                array(
                    'status' => ReservesTServiceOrderModel::ReserveOrderStatus_Done,
                    'exclude' => $_arbitrage,
                    'title' => '���������� ������',
                    'texts' => '��������� ���������� ���������� ������, �������� ������ � ������� � ��������������.'
                ),

                array(
                    'status' => ReservesTServiceOrderModel::ReserveOrderStatus_Pay,
                    'title' => '������� �����',
                    'texts' => '������������ ����� ������ ����������� (�/��� �� ������� ��������� �� ������� �������).'
                )
            );
        
            $this->stages_list[TServiceOrderModel::PAYTYPE_RESERVE] = $_reserve_stages;
        }    
    }
    
    
    
    public function run($ret = false) 
    {
        return $this->render('order-status-indicator', array(
            'stages_list' => $this->stages_list,
            'active_status' => $this->active_status,
            'active_paytype' => $this->paytype,
            'is_ajax' => $this->is_ajax
        ), $ret);
    }
    
    
    public function getAjaxRender()
    {
        $this->is_ajax = true;
        $this->init();
        return $this->run(true);
    }
    
}
