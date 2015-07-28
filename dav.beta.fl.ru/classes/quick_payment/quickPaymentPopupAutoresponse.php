<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');


class quickPaymentPopupAutoresponse extends quickPaymentPopup
{
    const PAYMENT_TYPE_ACCOUNT = 'account';
    
    protected $UNIC_NAME = 'autoresponse';
    
    public function __construct()
    {
        parent::__construct();
        
        //��������� ������ � ������� �����
        $this->options['payments'][self::PAYMENT_TYPE_ACCOUNT] = array();
    }
    
    public function init() 
    {
        //@todo: ����� ����� �������� ������ 
        //����� ��� ������ ������ ������
        
        $this->setBuyPopupTemplate('buy_popup_autoresponse.tpl.php');

        $input_id = $this->ID . '_service';
        
        $promoCodes = new PromoCodes();
        
        $options = array(
            'popup_title_class_bg'      => 'b-fon_bg_po',
            'popup_title_class_icon'    => 'b-icon__po',
            'popup_title'               => '������ ������ �����������',
            'popup_subtitle'            => '',
            'items_title'               => '������������ ���',
            'popup_id'                  => $this->ID,
            'unic_name'                 => $this->UNIC_NAME,
            'payments_title'            => '����� � ������ ������',
            'payments_exclude'          => array(self::PAYMENT_TYPE_BANK),
            'ac_sum'                    => round($_SESSION['ac_sum'], 2),
            'payment_account'           => self::PAYMENT_TYPE_ACCOUNT,
            'promo_code' => $promoCodes->render(PromoCodes::SERVICE_AUTORESPONSE)
        );
        
        //����������� �������� ��������
        parent::init($options);
        
        
        //��������� �������� � ������ ������� ������
        $this->options['payments'][self::PAYMENT_TYPE_CARD]['wait'] = '����� ....';
        
        $this->options['payments'][self::PAYMENT_TYPE_PLATIPOTOM]['content_after'] = sprintf(
            $this->options['payments'][self::PAYMENT_TYPE_PLATIPOTOM]['content_after'],
            '����������'
        );
        
    }
    
    
}
