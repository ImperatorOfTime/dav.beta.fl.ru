<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
	$rpath = "../../";
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
	session_start();
	get_uid();
	
	if (!(hasPermissions('adm') && (hasPermissions('stats') || hasPermissions('tmppayments')) ))
		{header ("Location: /404.php"); exit;}
	
$content = "../content.php";


$inner_page = trim($_GET['page']);
if (!$inner_page) $inner_page = "charts";

$aMonthes[1] = '������';
$aMonthes[2] = '�������';
$aMonthes[3] = '����';
$aMonthes[4] = '������';
$aMonthes[5] = '���';
$aMonthes[6] = '����';
$aMonthes[7] = '����';
$aMonthes[8] = '������';
$aMonthes[9] = '��������';
$aMonthes[10] = '�������';
$aMonthes[11] = '������';
$aMonthes[12] = '�������';

$inner_page = "inner_".$inner_page.".php";

$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath."template.php");

?>
