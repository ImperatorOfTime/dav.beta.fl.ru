<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/buffer.php');

class quickPaymentPopupFrlbindup extends quickPaymentPopup
{
    const PAYMENT_TYPE_ACCOUNT = 'account';
    const PAYMENT_TYPE_BUFFER = 'buffer';
    
    protected $UNIC_NAME = 'frlbindup';
    
    public function __construct()
    {
        parent::__construct();
        
        //��������� ������ � ������� �����
        $this->options['payments'][self::PAYMENT_TYPE_ACCOUNT] = array();
        
        //��������� ������ � ������
        $this->options['payments'][self::PAYMENT_TYPE_BUFFER] = array();
    }
    
    public function init($options) 
    {
        parent::init($options);

        $this->setBuyPopupTemplate('buy_popup_frlbindup.tpl.php');
        
        $promoCodes = new PromoCodes();
        
        $buffer = new buffer();

        $options = array(
            'popup_title_class_bg'      => 'b-fon_bg_po',
            'popup_title_class_icon'    => 'b-icon__po',
            'popup_title'               => '�������� ����������� �� 1 �����',
            'popup_id'                  => $this->ID,
            'unic_name'                 => $this->UNIC_NAME,
            'payments_title'            => '����� � ������ ������',
            'payments_exclude'          => array(self::PAYMENT_TYPE_BANK),
            'ac_sum'                    => round($_SESSION['ac_sum'], 2),
            'payment_account'           => self::PAYMENT_TYPE_ACCOUNT,
            'is_show'                   => $options['autoshow'],
            'buffer'                    => $buffer->getSum(),
            'promo_code' => $promoCodes->render(PromoCodes::SERVICE_FRLBIND)
        );
        
        //����������� �������� ��������
        parent::init($options);
        
        
        //��������� �������� � ������ ������� ������
        $this->options['payments'][self::PAYMENT_TYPE_CARD]['wait'] = '����� ....';
        
        $this->options['payments'][self::PAYMENT_TYPE_PLATIPOTOM]['content_after'] = sprintf(
            $this->options['payments'][self::PAYMENT_TYPE_PLATIPOTOM]['content_after'],
            '�����������'
        );
    }
}
