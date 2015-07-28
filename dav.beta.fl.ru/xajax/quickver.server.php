<?php

/**
 * �����������. ������ ������.
 */


require_once ($_SERVER['DOCUMENT_ROOT'] . "/xajax/quickver.common.php");
require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/Verification.php");

session_start();

/**
 * �������� ������� ����������� � ���������� ���
 * @todo: ���������� ��� ����������� ��� ��� ��� ����������� ��������� ��������� ���� ��� ����������
 * 
 * @param type $fname
 * @param type $lname
 * @param type $type
 * @return \xajaxResponse
 */
function checkIsVerify($fname, $lname, $type) 
{
    require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
    
    $objResponse = new xajaxResponse();
    
    $uid = get_uid(false);
    if ($uid <= 0) {
        return $objResponse;
    }
    
    $user = new users();
    $user->GetUserByUID($uid);
    
    if ($user->uid > 0 && 
        $user->IsVerified()) {
        
        $update_fio = false;
        $fname = change_q(substr(trim($fname),0,21));
        if ($fname) {
            $user->uname = $fname;
            $update_fio = true;
        }
        
        $lname = change_q(substr(trim($lname),0,21));
        if ($lname) {
            $user->usurname = $lname;
            $update_fio = true;
        }

        if ($update_fio) {
            $user->Update($user->uid, $err);
        }
        
        $_SESSION['is_verify'] = 't';
        unset($_SESSION['quick_ver_fname']);
        unset($_SESSION['quick_ver_lname']);
        $objResponse->script("window.location = '?vok=1".($type=='card' ? '&vuse=card' : '')."';");
    } else {
        $error = session::getFlashMessages('verify_error');
        $error = empty($error)?Verification::ERROR_DEFAULT:$error;
        $objResponse->script("window.verification_popup.showError('{$error}');");
    }
    
    return $objResponse;
}


/**
 * �������� WebMoney WMID
 * 
 * @param type $wmid
 * @return \xajaxResponse
 */
function checkWebmoneyWMID($wmid) 
{
    $objResponse = new xajaxResponse();
    
    $uid = get_uid(false);
    $error = false;
    
    if ($uid > 0) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Verification.php");
        
        $verification = new Verification;
        
        if (!$verification->webmoneyCheckWMID($wmid, $uid)) {
            $error = $verification->getError();
        }

        if ($error) {
            $objResponse->script("window.verification_popup.showError('{$error}');");
        } else {
            $objResponse->script("window.verification_popup.openWindowWM('{$verification->getWMLoginUrl()}');");
        }
    }
    
    return $objResponse;
}


/**
 * ��������� �������� ���
 * @todo: ����������� �������������
 * 
 * @param type $fname
 * @param type $lname
 * @return \xajaxResponse
 */
function storeFIO($fname, $lname) 
{
    $objResponse = new xajaxResponse();
    
    $_SESSION['quick_ver_fname'] = change_q(substr(trim($fname),0,21));
    $_SESSION['quick_ver_lname'] = change_q(substr(trim($lname),0,21));
    
    return $objResponse;
}



/**
 * ������� ������ ����������� �� ���������� ����� ����� ������
 * � ������ �������� ������� �������� ������ �� ������� 10 ������
 * 
 * @return \xajaxResponse
 */
function quickYandexKassaAC($fname, $lname) 
{
    $objResponse = new xajaxResponse();
    
    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yandex_kassa.php');
    
    $uid = get_uid(false);
    $bill = new billing($uid);
    
    //@todo: ����� �������� ������� �����������
    
    $option = array(
        'uname' => change_q(substr(trim($fname),0,21)), 
        'usurname' => change_q(substr(trim($lname),0,21))
    ); 
    
    $billReserveId = $bill->addServiceAndCheckout(
            Verification::YKASSA_AC_OP_CODE, 
            $option);
    $sum = $bill->getOrderPayedSum();
    
    $yandex_kassa = new yandex_kassa();
    $html_form = $yandex_kassa->render(
            $sum, 
            $bill->account->id, 
            yandex_kassa::PAYMENT_AC, 
            $billReserveId);

    $objResponse->script("
        if (window.verification_popup) {
            window.verification_popup.openWindowYandexKassaAC('{$html_form}');
        }
    ");

    $_SESSION['quickver_is_begin'] = 1;
    
    return $objResponse;
}


$xajax->processRequest();