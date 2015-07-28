<?php

//$rpath = "../";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_helper.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/tservices.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_auth_smail.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');


function more_feedbacks($tuid, $page, $total_cnt) 
{
    $per_page = 5;
    $objResponse = &new xajaxResponse();
    
    $tservices = new tservices();
    $feedbacks = $tservices->setPage($per_page,$page)->getFeedbacks($tuid);
    
    $sHtml = Template::render(ABS_PATH . '/tu/tpl.feedbacks-items.php', array('feedbacks' => $feedbacks));

    $local_cnt = $per_page*$page;
    $objResponse->call('ap_feedbacks.setContent',$sHtml,($local_cnt >= $total_cnt));
    
    return $objResponse;
}

function tservices_order_auth($email, $name, $surname, $options) 
{
    $objResponse = &new xajaxResponse();

    $name    = substr(strip_tags(trim(stripslashes($name))),0,21); //��� �����������
    $surname = substr(strip_tags(trim(stripslashes($surname))),0,21); //��� �����������
    $email = substr(strip_tags(trim(stripslashes($email))),0,64); //��� ����������� � �����������
    
    $tu_id = intval(@$options['tu_id']);

    $tservices = new tservices();
    $tService = $tservices->getCard($tu_id);
    if(!$tService) return $objResponse;
    
    if (is_email($email)) 
    {

        //�������� ������ ������ ��� �����
        $options = array_intersect_key($options, array('extra' => '','is_express' => '','paytype' => ''));
        //�������� ������� ����������
        $is_valid_extra = !isset($options['extra']) || (isset($options['extra']) && (count(array_intersect(array_keys($tService['extra']), $options['extra'])) == count($options['extra'])));
        $is_valid_express = !isset($options['is_express']) || (isset($options['is_express']) && $options['is_express'] == '1' && $tService['is_express'] == 't');
        $is_valid_paytype = isset($options['paytype']) && in_array($options['paytype'], array('0','1'));
        if(!($is_valid_extra && $is_valid_express && $is_valid_paytype)) return $objResponse;

        $tservices_auth_smail = new tservices_auth_smail();
        
        $user = new users();
        $user->GetUser($email, true, true);
        //��������� �� ������ ������ ��� ����� ���� ������� � �� ����� �����
        $is_email = ($user->email == $email);

        //������� ��� ��� ������ ���������
        $code = TServiceOrderModel::model()->newOrderActivation(array(
            'user_id' => ($user->uid > 0)?$user->uid:NULL,
            'tu_id' => $tService['id'],
            'uname' => !empty($name)?$name:NULL,
            'usurname' => !empty($surname)?$surname:NULL,
            'email' => $email,
            'options' => $options
        ));
        
        // ������������ ������, ���� � ���� ���� email. � ��� ��� ���������?
        if (($user->uid > 0) && $is_email) 
        { 
            
            if (is_emp($user->role)) 
            {
                $tservices_auth_smail->orderByOldUser($email, $tService, $code);
                $objResponse->call('TServices_Order_Auth.showSuccess', "�� ��������� ���� ����� ���������� ������ �� �������-��������������. ����������, ��������� �� ��� ��� ���������� �������� ������ ������.");
            } 
            else 
            {
                $objResponse->call('TServices_Order_Auth.showError', 'email', '������ e-mail ����������� ����������');
            }
            
        } 
        else 
        {
            $tservices_auth_smail->orderByNewUser($email, $tService, $code);
            $objResponse->call('TServices_Order_Auth.showSuccess', "�� ��������� ���� ����� ���������� ������ �� �������-��������������. ����������, ��������� �� ��� ��� ���������� �������� ������ ������.");
        }
        
    }
    else 
    {
        $objResponse->call('TServices_Order_Auth.showError', 'email', '������� ������� �����');
    }
    
    return $objResponse;
}

$xajax->processRequest();