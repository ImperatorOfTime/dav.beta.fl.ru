<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
$g_page_id = "0|21";

$project_url = 'https://www.fl.ru/projects/2174791/marketpleys-na-wordpress.html';

$project = array(
    'id' => '174',
    'name' => '�������� ������',
    'price_display' => '12000 ������ / ������',
    //'url' => $project_url
);

$content = "../../projects/tpl.popup_share.php";

$header = "../../header.new.php";
$footer = "../../footer.new.html";
include ("../../template3.php");
