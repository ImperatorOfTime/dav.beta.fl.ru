<?
/**
 * ���������� ����� �� �������
 * 
 */

// ������� ������� �� �������
$bounds = array(500, 2000, 5000, 10000, 20000, 50000);

define('IS_SITE_ADMIN', 1);
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_complains.php");

hasPermissions('stats') || hasPermissions('users') || header_location_exit('/404.php');
$rpath = "../../";

$menu_item   = 24; // ����� ������ ���� - ���������.
$header      = $rpath.'header.new.php';
$inner_page  = "index_inner.php";
$content     = '../content.php';
$footer      = $rpath.'footer.new.html';
$template    = 'template3.php';
$css_file    = array( 'moderation.css', 'new-admin.css', 'nav.css' );

// ����� ���������� � �������� "�� ��� / �� ���"
$complains_all = projects_complains::GetComplainsStats('from');

// ������� �� �������
$bcnt = count($bounds);
$complains_by_cost = projects_complains::GetComplainsStats('cost', $bounds);

// �� ����������
$complains_categ = projects_complains::GetComplainsStats('category');

include($rpath . $template);