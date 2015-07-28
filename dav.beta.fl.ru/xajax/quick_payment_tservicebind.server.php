<?php

/**
 * ����������� / ��������� ����������� � �������� ��.
 * ������ ������.
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yandex_kassa.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/platipotom.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupFactory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_binds.php");


//------------------------------------------------------------------------------


/**
 * ������ � ������� �����
 * @param type $type
 * @param array $data (weeks, prof_id, is_spec)
 * @return type
 */
function quickPaymentTservicebindAccount($type, $data)
{
    $is_error = true;

    $uid = get_uid(false);

    $objResponse = &new xajaxResponse();

    $kind = (int)@$data['kind'];
    $tservice_id = (int)@$data['tservice_text_db_id'];
    $prof_id = (int)@$data['prof_id'];
    $is_prolong = (bool)@$data['is_prolong'];
    $promo_code = (string)@$data['promo'];
    
    $tservices_binds = new tservices_binds($kind);
    $tservices = new tservices($uid);
    
    $allow = $tservices_binds->isAllowBind($uid, $tservice_id, $kind, $prof_id);
    
    $valid = $tservices->isExists($tservice_id) && ($is_prolong ? !$allow : $allow);

    if ($valid) {

        $bill = new billing($uid);
        //��������� ������������� ����������
        $bill->setPromoCodes('SERVICE_TSERVICEBIND', $promo_code); 

        $op_code = $tservices_binds->getOpCode();
        $option = array(
            'weeks' => (int)@$data['weeks'],
            'prof_id' => $prof_id,
            'tservice_id' => $tservice_id,
            'is_prolong' => $is_prolong
        );
        
        $ok = $bill->addServiceAndPayFromAccount($op_code, $option);
        
        if ($ok) {
            $is_error = false;
            $objResponse->script("window.location.reload();");
        }
    }

    // ���������� �������������� � ������ ������
    if ($is_error) {
        $idx = quickPaymentPopupTservicebind::getPopupId($is_prolong?$tservice_id:0);
        $action = $is_prolong ? '��������� �����������' : '�����������';
        $objResponse->script("
            var qp = window.quick_payment_factory.getQuickPaymentById('tservicebind', '".$idx."');
            if(qp) qp.show_error('�������� ������ ��� {$action} ������!');
        ");
    }
        
    return $objResponse;
}


//------------------------------------------------------------------------------


/**
 * ��� ������ ��� ������ ����� ������ �� ��������������� � ������ �����
 * 
 * @param type $type
 * @param type $data
 */
function quickPaymentTservicebindDolcard($type, $data)
{
    return quickPaymentTservicebindYandexKassa($type, $data);
}

function quickPaymentTservicebindYa($type, $data)
{
    return quickPaymentTservicebindYandexKassa($type, $data);
}

function quickPaymentTservicebindWebmoney($type, $data)
{
    return quickPaymentTservicebindYandexKassa($type, $data);
}

function quickPaymentTservicebindAlfaclick($type, $data)
{
    return quickPaymentTservicebindYandexKassa($type, $data);
}

function quickPaymentTservicebindSberbank($type, $data)
{
    return quickPaymentTservicebindYandexKassa($type, $data);
}


//------------------------------------------------------------------------------


/**
 * �������������� ������� ����� ������ �����
 * 
 * @param type $type - ��� ������
 * @param type $data - ������ �� ���������� ���������� ������
 * @return \xajaxResponse
 */
function quickPaymentTservicebindYandexKassa($type, $data)
{
    $is_error = true;

    $uid = get_uid(false);

    $objResponse = &new xajaxResponse();

    $kind = (int)@$data['kind'];
    $tservice_id = (int)@$data['tservice_text_db_id'];
    $prof_id = (int)@$data['prof_id'];
    $is_prolong = (bool)@$data['is_prolong'];
    $promo_code = (string)@$data['promo'];
    
    $pay_methods = array(
        quickPaymentPopup::PAYMENT_TYPE_CARD => yandex_kassa::PAYMENT_AC,
        quickPaymentPopup::PAYMENT_TYPE_YA => yandex_kassa::PAYMENT_YD,
        quickPaymentPopup::PAYMENT_TYPE_WM => yandex_kassa::PAYMENT_WM,
        quickPaymentPopup::PAYMENT_TYPE_ALFACLICK => yandex_kassa::PAYMENT_AB,
        quickPaymentPopup::PAYMENT_TYPE_SBERBANK => yandex_kassa::PAYMENT_SB
    );
    
    if(!isset($pay_methods[$type])) return $objResponse;
    
    $tservices_binds = new tservices_binds($kind);
    $tservices = new tservices($uid);
    
    $allow = $tservices_binds->isAllowBind($uid, $tservice_id, $kind, $prof_id);
    $valid = $tservices->isExists($tservice_id) && ($is_prolong ? !$allow : $allow);
    
    if ($valid) {
        $is_error = false;

        $bill = new billing($uid);
        //��������� ������������� ����������
        $bill->setPromoCodes('SERVICE_TSERVICEBIND', $promo_code);        

        $op_code = $tservices_binds->getOpCode();
        $option = array(
            'weeks' => (int)@$data['weeks'],
            'prof_id' => $prof_id,
            'tservice_id' => $tservice_id,
            'is_prolong' => $is_prolong
        );
        
        //��������� �����
        $billReserveId = $bill->addServiceAndCheckout($op_code, $option);
        $payed_sum = $bill->getRealPayedSum();
        
        $payment = $pay_methods[$type];
        $yandex_kassa = new yandex_kassa();
        $html_form = $yandex_kassa->render(
                $payed_sum, 
                $bill->account->id, 
                $payment, 
                $billReserveId);

        $idx = quickPaymentPopupTservicebind::getPopupId($is_prolong?$tservice_id:0);
        $objResponse->script("
            var qp_form_wrapper = $$('#".$idx." .__quick_payment_form');
            if(qp_form_wrapper){    
                qp_form_wrapper.set('html','{$html_form}');
                qp_form_wrapper.getElement('form')[0].submit();
            }
        ");

                
        //��������� � ������ ���� ������� ��� �������� �������        
        $redirect = (string)@$data['redirect'];
        $_SESSION[quickPaymentPopup::QPP_REDIRECT] = $redirect;
    }

    // ���������� �������������� � ������ ������
    if ($is_error) {
        $idx = quickPaymentPopupTservicebind::getPopupId($is_prolong?$tservice_id:0);
        $action = $is_prolong ? '��������� �����������' : '�����������';
        $objResponse->script("
            var qp = window.quick_payment_factory.getQuickPaymentById('tservicebind', '".$idx."');
            if(qp) qp.show_error('�������� ������ ��� {$action} ������!');
        ");
    }
        
    return $objResponse;
}


//------------------------------------------------------------------------------


/**
 * ������ ����� ����� �����
 * 
 * @param type $type - ��� ������
 * @param type $data - ������ �� ���������� ���������� ������
 * @return \xajaxResponse
 */
function quickPaymentTservicebindPlatipotom($type, $data)
{
    $is_error = true;

    $uid = get_uid(false);

    $objResponse = &new xajaxResponse();

    $kind = (int)@$data['kind'];
    $tservice_id = (int)@$data['tservice_text_db_id'];
    $prof_id = (int)@$data['prof_id'];
    $is_prolong = (bool)@$data['is_prolong'];
    $promo_code = (string)@$data['promo'];
    
    $tservices_binds = new tservices_binds($kind);
    $tservices = new tservices($uid);
    
    $allow = $tservices_binds->isAllowBind($uid, $tservice_id, $kind, $prof_id);
    $valid = $tservices->isExists($tservice_id) && ($is_prolong ? !$allow : $allow);
    
    if ($valid) {
        $is_error = false;

        $bill = new billing($uid);
        //��������� ������������� ����������
        $bill->setPromoCodes('SERVICE_TSERVICEBIND', $promo_code); 

        $op_code = $tservices_binds->getOpCode();
        $option = array(
            'weeks' =>  (int)@$data['weeks'],
            'prof_id' => $prof_id,
            'tservice_id' => $tservice_id,
            'is_prolong' => $is_prolong
        );
        
        //��������� �����
        $billReserveId = $bill->addServiceAndCheckout($op_code, $option);
        $payed_sum = $bill->getRealPayedSum();
        
        $platipotom = new platipotom();
        $html_form = $platipotom->render(
                $payed_sum, 
                $bill->account->id, 
                $billReserveId);

        if($html_form) {
            $idx = quickPaymentPopupTservicebind::getPopupId($is_prolong?$tservice_id:0);
            $objResponse->script("
                var qp_form_wrapper = $$('#".$idx." .__quick_payment_form');
                if(qp_form_wrapper){    
                    qp_form_wrapper.set('html','{$html_form}');
                    qp_form_wrapper.getElement('form')[0].submit();
                }
            ");


            //��������� � ������ ���� ������� ��� �������� �������        
            $redirect = (string)@$data['redirect'];
            $_SESSION[quickPaymentPopup::QPP_REDIRECT] = $redirect;
        }
    }

    // ���������� �������������� � ������ ������
    if ($is_error) {
        $idx = quickPaymentPopupTservicebind::getPopupId($is_prolong?$tservice_id:0);
        $action = $is_prolong ? '��������� �����������' : '�����������';
        $objResponse->script("
            var qp = window.quick_payment_factory.getQuickPaymentById('tservicebind', '".$idx."');
            if(qp) qp.show_error('�������� ������ ��� {$action} ������!');
        ");
    }
        
    return $objResponse;
}