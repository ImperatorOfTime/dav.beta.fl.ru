<?php

/**
 * 2�� ���� 2�-������� �����������
 */

define('IS_AUTH_SECOND', true);

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opauth/OpauthModel.php");

$uid = get_uid(false);

if ($uid > 0) {
    //���� ��� ����������� �� ���������� 
    //�� ������ ������������ � �������    
    header("Location: /users/{$_SESSION['login']}/setup/safety/");
    exit;
} elseif (!isset($_SESSION['2fa_provider'])) {
    //���� ��� �� 2�� ���� �� �� �����������
    header("Location: /registration/");
    exit;    
}

//�������� �� ������ ��� 2��� �����
//0 - �������
//1... - �� ���� �������
$_2fa_provider = $_SESSION['2fa_provider']['type'];
$_2fa_login = $_SESSION['2fa_provider']['login'];

//�������� ���������� ������ ���� ����� 
//����� ����������� ������������ ��������� ���� �����
if (isset($_SESSION['2fa_redirect'])) {
    $redirectUri = $_SESSION['2fa_redirect']['redirectUri'];
    $_user_action = $_SESSION['2fa_redirect']['_user_action'];
}

//��������� �� ������
$alert_message = session::getFlashMessages('/auth/second/');

$hide_banner_top = true;
//������ ����� ����������� � ����
$registration_page = true;
$js_file[] = "/css/block/b-eye/b-eye.js";
$js_file[] = 'registration/login.js';
$content = "content.php";
include ("../../template3.php");