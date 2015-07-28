<?php

require_once('quickExtPaymentPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');

class quickPaymentPopupBillInvoice extends quickExtPaymentPopup
{
    const TPL_BUY_POPUP_DEFAULT_LAYOUT  = 'buy_ext_popup_billinvoice.tpl.php';
    
    public function initJS() 
    {
        parent::initJS();
        
        global $js_file;
        $js_file['billinvoice_quick_ext_payment'] = 'quick_payment/billinvoice_quick_ext_payment.js';
    }    
    
    public function init() 
    {
        $uid = get_uid(false);
        $reqvs = sbr_meta::getUserReqvs($uid);

        $form_type = @$reqvs['form_type'];
        $rez_type = @$reqvs['rez_type'];

        if ($form_type) {
            $reqvs = $reqvs[$form_type];
            
            if($rez_type == sbr::RT_RU){
                $reqvs_data = array(
                    array('label' => '���', 'value' => @$reqvs['inn']),
                    array('label' => '���', 'value' => @$reqvs['kpp'], 'padbot' => 10),
                    array('label' => '��������� ����', 'value' => @$reqvs['bank_rs']),
                    array('label' => '����', 'value' => @$reqvs['bank_name']),
                    array('label' => '����.����', 'value' => @$reqvs['bank_ks']),
                    array('label' => '��� �����', 'value' => @$reqvs['bank_bik']),
                    array('label' => '��� �����', 'value' => @$reqvs['bank_inn'])
                );                
            } else {
                $reqvs_data = array(
                    array('label' => '��������� ����', 'value' => @$reqvs['bank_rs']),
                    array('label' => '����', 'value' => @$reqvs['bank_name']),
                    array('label' => '�������������� ����', 'value' => @$reqvs['bank_rf_name']),
                    array('label' => '����.���� ������ ����� � �������������� �����', 'value' => @$reqvs['bank_rf_ks']),
                    array('label' => '��� ��������������� �����', 'value' => @$reqvs['bank_rf_bik']),
                    array('label' => '��� ��������������� �����', 'value' => @$reqvs['bank_rf_inn'])
                );
            }
            
        } else {
            $this->stopRender();
            return;
        }
        
        $options = array(
            'popup_title' => '������������ �����',
             //��������� ������ ������������ ����� �� ������
            'payments' => array(
                self::PAYMENT_TYPE_BANK => array(
                    'title' => '������������ ����',
                    'class' => '',
                    'wait' => '���� �������� �����'
                )                
            ),
            'reqvs' => $reqvs_data,
            'rt_ru' => ($rez_type == sbr::RT_RU)
        );
        
        
        require_once('forms/BillInvoiceForm.php');
        $form = new BillInvoiceForm();
        $this->setContent($form->render());
        
        
        
        /*
        $this->addWaitMessageForAll(
            //������ ���������
        );
        */
        
        
        
        parent::init($options);
    }    
}