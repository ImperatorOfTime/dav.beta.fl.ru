<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/codes.php");

$stretch_page = 1;

$cuid = htmlspecialchars($_REQUEST['c']);
$action = trim($_REQUEST['action']);

$codes = new codes;

if (!$cuid){
    $master_error = "�������� �� ������ � ������ ��� ���������� �� � �������� ������ ��������";
} else {
    $codes -> GetRow($cuid);
    if (!$codes->code) $master_error = "
        ��� ��� �������������� ������ �� ������ ��� �������. 
        �������� �� ��� �������� ������, ���� ��� � ��������� ��������� �������������� ������� ��� ���.";
    else $uuid = $cuid;
}


if ($action == "change" && !$master_error) {

	$pwd =  strip_tags(stripslashes(trim($_POST['pwd'])));
	$pwd2 = strip_tags(stripslashes(trim($_POST['pwd2'])));

	if (!preg_match('/^[a-zA-Z\d\!\@\#\$\%\^\&\*\(\)\_\+\-\=\;\,\.\/\?\[\]\{\}]+$/', $pwd)) {
        $error = "������ �������� ������������ �������.<br>"
                . "����������, ����������� ������ ��������� �����, �����<br>"
                . "� ��������� �����������: !@#$%^&*()_+-=;,./?[]{}";
    }
	elseif (strcmp($pwd,$pwd2)) $error = "��������� ������ �� ���������";
    elseif ((strlen($pwd) < 6))  { $error = "������� �������� ������ (������� � 6 ��������)";}
    elseif ((strlen($pwd) > 24)) { $error = "������� ������� ������ (�������� � 24 �������)";}
    
	if (!$error && $codes->user_id) {
	    require_once(ABS_PATH . "/classes/users.php");
	    $user = new users();
	    $user->passwd = $pwd;
	    $err = $user->Update($codes->user_id,$res);
	    $u_id = $codes->user_id;
	    $codes->DelByUT($codes->user_id, 1);
	    if (!$err) {
			$info = "��������� �������";
			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
			$smail = new smail();
			$smail->ChangePwd($codes->user_id, $pwd);

            // ����� � ��� ����� �������
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/restorepass_log.php");
            restorepass_log::SaveToLog($codes->user_id, getRemoteIP(), 1);

            $pwd = users::hashPasswd(trim(stripslashes($pwd)));
            $user->getUserByUID($u_id);
        	login($user->login, $pwd);
        	session_write_close();
        	header("Location: /");
        	exit;

		}
		
	}
}

$content = "changepwd_inner.php";
include ("template3.php");