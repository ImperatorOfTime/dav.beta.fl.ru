<?php
/**
 * ����������� �������������
 * */
ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp.php';


/**
 * ����� ������������ �� ���� �������������� ��������
 * 
 */
$sender = 'admin';

// ������ �������������, �������������� � ����������������, ������������ (is_banned = B'0'), � ����������� ����������

$sql = "SELECT uid FROM employer AS u INNER JOIN (SELECT DISTINCT emp_id FROM sbr) AS dsbr ON dsbr.emp_id = u.uid
        WHERE is_banned = B'0'
        AND (substring(subscr from 8 for 1)::integer = 1) LIMIT 3000 OFFSET ?"; //employer

//$sql = "SELECT uid FROM users WHERE login IN ('land_e2', 'land_f')"; 

$pHost = str_replace("https://", "", $GLOBALS['host']);
if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // ������� � ������ ���� ����������� ��������� HTTPS �� �������� (��� ����� � ��)
} else {
    $pHttp = 'http';
}

$pMessage = "��������� ������������!

�������, ��� ��������������� \"������� ��� �����\" ��� ������ ����� ���������� �� ����� Free-lance.ru.

���������� ��� �������� ��������, ������ � ������������� {$pHttp}:/{�����}/feedback.free-lance.ru/topics?category=5268

���� ������ ��������� ������������� ������ ������ ��� � ���������� � ������������� ������, � ������ �����������, ����������� ��������� ������ � � ������ ����������� ��������.

���������� �� ��������������.

�������� ������ � http:/{Free-lance}/{$pHost}/<i></i>!
";
// ----------------------------------------------------------------------------------------------------------------
// -- �������� ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$DB = new DB('plproxy');
$M  = new DB('master');
 
// �������������� ��������
$msgid = $DB->val("SELECT masssend(103, '$pMessage', '{}', '')");  
$i = 0;
while ( $users = $M->col($sql, $i) ) {
    $DB->query("SELECT masssend_bind(?, 103, ?a)", $msgid, $users);
    $i = $i + 3000;
}
$DB->query("SELECT masssend_commit(?, 103)", $msgid);
echo "OK";
