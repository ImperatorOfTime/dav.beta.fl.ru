<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");





if(isset($_GET['user'])) {
	$pp = new pay_place();
	$usr = new Users();
	
	$uid = $usr->GetUid($error, pg_escape_string($_GET['user']));
	
	if($uid == null) {
		echo "������� ����� �� ����������"; 
		die();
	}
	//$role = $usr->GetRole(pg_escape_string($_GET['user']), $error);
	
	$r = $pp->addUser($uid);
	if(!$r) { echo "������"; die(); }
	
	echo "�� ������ �������� ����� ".$pp->getTimeShow()." ���";
}



?>