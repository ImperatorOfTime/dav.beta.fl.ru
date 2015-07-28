<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesHelper.php');


class quickPaymentPopupReserve extends quickPaymentPopup
{
    protected $UNIC_NAME = 'reserve';
    
    //@todo: #28021 ������� �� ��������� ������ ��������� ����� ����� 15000�.
    const MAX_PAYMEN_WM = 15000;


    //��������� ������� ������ � �����������
    //�� ���� ������ ��� �����
    protected $payments_exclude = array(
        sbr::FT_PHYS => array(
            self::PAYMENT_TYPE_BANK,
            //@todo: �� ������� ���������� ������� ��������� ����
            //������� �������� ��������� �� �� �������� �������������� ��� ����
            self::PAYMENT_TYPE_ALFACLICK,
            self::PAYMENT_TYPE_SBERBANK
        ),
        sbr::FT_JURI => array(
            self::PAYMENT_TYPE_YA,
            self::PAYMENT_TYPE_CARD,
            self::PAYMENT_TYPE_WM,
            self::PAYMENT_TYPE_ALFACLICK,
            self::PAYMENT_TYPE_SBERBANK)
    );

    /**
     * ������ ������� � ����������� �� ��� ����
     * �� ��� ���������� ReservesModel
     * @example ����� ���� ������ ���� ReservesTServiceOrderModel
     * 
     * @todo: ������ ������ ������ ��-�� ���� ���������� ����� ������, 
     * ���������� ���� ���� �������� � init ����������
     * 
     * @var ReservesModel 
     */
    public $reserveInstance;
    
    //@todo: ���������� �������� �� ������ �� ������� ����
    public $opt = array();
    public $uid;
    public $reserve_id;

    public function init() 
    {
        $this->setBuyPopupTemplate('buy_popup_reserve.tpl.php');
        
        $uid = $this->uid;
        $reserve_id = $this->reserve_id;
        
        $reqvs = ReservesHelper::getInstance()->getUserReqvs($uid);
        $form_type = $reqvs['form_type'];
        $rez_type = $reqvs['rez_type'];
        
        $form_id = $this->ID . '_form';
        $rez_id  = $this->ID . '_rez';
        
        $form_name = $form_type == sbr::FT_PHYS ? '���������� ����' : '����������� ����';
        //$rez_name = $rez_type == sbr::RT_RU ? '�������� ��' : '���������� ��';
        $rez_name = sbr::getRezTypeText($rez_type);

        
        $options = array(
            'popup_title_class_bg'      => 'b-fon_bg_po',
            'popup_title_class_icon'    => 'b-icon__po',
            'popup_title'               => '�������������� �������',
            'popup_subtitle'            => '',
            'items_title'               => '����� ������',
            'popup_id'                  => $this->ID,
            'unic_name'                 => $this->UNIC_NAME,
            'form_name'                 => $form_name,
            'rez_name'                  => $rez_name,
            'items' => array(
                array(
                    'value' => $reserve_id,
                    'name' => $form_id
                ),
                array(
                    'value' => $reserve_id,
                    'name' => $rez_id
                )
            ),
            'payments_title'            => '������ ��������������', 
            'payments_exclude'          => $this->payments_exclude[$form_type]
        );
        
        
        if ($form_type == sbr::FT_JURI) {
            $options['items'][] = array(
                'value' => 1,
                'name' => 'is_reserve_send_docs'
            );
        } 
        
        
        if ($this->reserveInstance->getReservePrice() >= self::MAX_PAYMEN_WM) {
            $options['payments_exclude'][] = self::PAYMENT_TYPE_WM;
        }
        if ($this->reserveInstance->getReservePrice() >= parent::MAX_PAYMENT_ALFA) {
            $options['payments_exclude'][] = self::PAYMENT_TYPE_ALFACLICK;
        }
        if ($this->reserveInstance->getReservePrice() >= parent::MAX_PAYMENT_SB) {
            $options['payments_exclude'][] = self::PAYMENT_TYPE_SBERBANK;
        }
        $options['payments_exclude'][] = self::PAYMENT_TYPE_PLATIPOTOM;
        
        parent::init($options);
    }
    
    
    /**
     * ����� ��� ��������� ���������� ������� � ����������� Yii
     */
    public function run()
    {
        echo $this->render($this->opt);
    }
    
    
    public function initJS()
    {
        global $js_file;
        
        parent::initJS();
        
        $js_file['quick_payment_reserve'] = 'quick_payment/reserve_quick_payment.js';        
    }
    
}
