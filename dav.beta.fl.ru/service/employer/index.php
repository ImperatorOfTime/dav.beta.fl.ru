<?php
$grey_service = 1;
$g_page_id = "0|9";
$stretch_page = true;
$showMainDiv  = true;
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';

session_start();
get_uid();

$guest = false;
$forFrl = false;
$forEmp = true;

$page_title = "������ - �������, ��������� ������ �� FL.ru";

$header   = '../../header.php';
$footer   = '../../footer.php';
$content  = '../content_new.php';

include '../../template2.php';