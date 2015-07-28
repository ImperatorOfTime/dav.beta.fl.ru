<?php
/**
 * �������� �������� ������� ����� ��� ������� ��������
 */

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' ); //???
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/seo/SeoTags.php");

$g_page_id = "0|992";
$rpath="../";

// begin ��������� layout

$grey_tservice = true; // �������� � ������� ���� "b-menu_head" ����� "������� ������" (@see ../header.new.php)
$stretch_page = true;
$showMainDiv  = true;

// ��������� JS ����� ��������
define('JS_BOTTOM', true);
//$js_file[] = "/tservices_categories_js.php";
$js_file[] = "tservices/tservices_catalog.js";
//$css_file    = array('portable.css');

$content = $_SERVER['DOCUMENT_ROOT']."/tu/tpl.catalog.php";
$header = "../header.php";
$footer = "../footer.html";

// /end ��������� layout

session_start();

// begin ������ �������� ����� ��������
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/controllers/TServiceCatalogController.php');

$module = new CModule('tu');
$module->setBasePath(dirname(__FILE__));
$controller = new TServiceCatalogController('t-service-catalog', $module);
$controller->init(); // ������������� ����������
$controller->run('index');
// /end ������ �������� ����� ��������


//���������� � ����� ������� ������� ����
$main_page = true;

$page_title = SeoTags::getInstance()->getTitle();
$page_descr = SeoTags::getInstance()->getDescription();
$page_keyw = SeoTags::getInstance()->getKeywords();


// ��������� ��������
include ($_SERVER['DOCUMENT_ROOT']."/template3.php");