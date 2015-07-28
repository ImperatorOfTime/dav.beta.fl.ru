<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");


class quickPaymentPopupFrlbind extends quickPaymentPopup
{
    const PAYMENT_TYPE_ACCOUNT = 'account';
    
    protected $UNIC_NAME = 'frlbind';
    
    public function __construct()
    {
        parent::__construct();
        
        //��������� ������ � ������� �����
        $this->options['payments'][self::PAYMENT_TYPE_ACCOUNT] = array();
    }
    
    public function init($options) 
    {
        parent::init($options);

        $this->setBuyPopupTemplate('buy_popup_frlbind.tpl.php');

        $input_id = $this->ID . '_service';
        
        $is_prolong = (boolean) $options['date_stop'];
        
        $promoCodes = new PromoCodes();
        
        $options = array(
            'popup_title_class_bg'      => 'b-fon_bg_po',
            'popup_title_class_icon'    => 'b-icon__po',
            'popup_title'               => $is_prolong ? '��������� ����������� � ��������' : '����������� � ��������',
            'popup_subtitle'            => $is_prolong ? '���� ��������� �����������' : '���� ����������� �������',
            'popup_id'                  => $this->ID,
            'unic_name'                 => $this->UNIC_NAME,
            'payments_title'            => '����� � ������ ������',
            'payments_exclude'          => array(self::PAYMENT_TYPE_BANK),
            'ac_sum'                    => round($_SESSION['ac_sum'], 2),
            'payment_account'           => self::PAYMENT_TYPE_ACCOUNT,
            'profession'                => $this->getProfessionText(),
            'date_stop'                 => $options['date_stop'],
            //�����? - � ��� �� ���� ���������� � �������� �� ID
            'is_show'                  => $options['autoshow'],
            'addprof'                  => $options['addprof'],
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
    
    private function getProfessionText()
    {
        $prof_text = '';
        $prof_group_id = $this->options['prof_group_id'];
        $prof_id = $this->options['prof_id'];
        if ($prof_group_id) {
            $prof_text = professions::GetProfGroupTitle($prof_group_id);
        } elseif ($prof_id) {
            $group_id = professions::GetGroupIdByProf($prof_id);
            $prof_text = professions::GetProfGroupTitle($group_id);
            $prof_text .= ' &mdash; ';
            $prof_text .= professions::GetProfName($prof_id);
        } else {
            $prof_text = '������� �����������';
        }
        return $prof_text;
    }
    
    public function getPrice()
    {
        return $this->options['ammount'];
    }
        
}
