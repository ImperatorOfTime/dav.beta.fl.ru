<?php

ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp.php';

 
if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // ������� � ������ ���� ����������� ��������� HTTPS �� �������� (��� ����� � ��)
} else {
    $pHttp = 'http';
}
$pHost = str_replace("$pHttp://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];

$pMessage = "
������������!

�� ������������ ��� ��������� ��������� � ��������, ��� ��� ��� � ������ � �������. 

��� �� �������� �����, �������� ������� ������������� ��������� � ������� ��������, ������ ����������� ��������� ����������� �������� PRO � ������� ��� �����. 

������ � ��� ���� ����������� ���������� �� �����������, ������� �������� �� ���� ����������� �����. ��� ����� ���������� ������� �� ������ �� ������ ������ � ������ ����������� ��� � �������� ��������� �� Free-lance.ru ����� ������������� ���������.

� 14 �� 21 ������� �� �������� ����� {$pHttp}:/{&laquo;�������� PRO �� 1 FM&raquo;}/{$pHost}/promo/testpro/?utm_source=newsletter4&utm_medium=email&utm_campaign=ottepel<i>.</i> ����������, ������� ������� �� �������� PRO, ������ ��������������� ����� ����������� ������������ � ������������� � �������������� ����������������� ��������.

{$pHttp}:/{����������}/{$pHost}/blogs/free-lanceru/725656/identifikatsiya-veb-koshelka-cherez-sistemu-contact.html?utm_source=newsletter4&utm_medium=email&utm_campaign=ottepel, ��� ��� ���������� �� � ������������ ��� �������� ������������� � ���-�������� ����� ������� Contact.

����� ��������� ���������� � ������������� �� ����� � ������ ������� �� ��������� ������� ����� ������ � {$pHttp}:/{&laquo;������&raquo;}/{$pHost}/blogs/free-lanceru/726198/ottepel-prodoljaetsya.html?utm_source=newsletter4&utm_medium=email&utm_campaign=ottepel<i>.</i>

�������� ������ � {$pHttp}:/{Free-lance.ru}/{$pHost}/!
";

$DB = new DB('plproxy');
$M  = new DB('master');
 
// �������������� ��������
$msgid = $DB->val("SELECT masssend(103, '$pMessage', '{}', '')");  
$i = 0;
// ������ ���� ������������ (is_banned = B'0') 
//$testloginlist = " AND login IN ('land_f', 'bolvan1', 'vg_rabot1') ";
$testloginlist = "";
$sql = "SELECT uid FROM users WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' {$testloginlist} LIMIT 3000 OFFSET ?";
while ( $users = $M->col($sql, $i) ) {
    $DB->query("SELECT masssend_bind(?, 103, ?a)", $msgid, $users);
    $i = $i + 3000;
}
$DB->query("SELECT masssend_commit(?, 103)", $msgid);
echo "OK";