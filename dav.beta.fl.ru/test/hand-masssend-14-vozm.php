<?php


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


$sql = "SELECT u.uid, u.email, u.login, u.uname, u.usurname, u.subscr FROM (
            SELECT DISTINCT uid as user_id FROM __tmp_PRO_vozm20120523
            UNION
            SELECT DISTINCT user_id FROM __tmp_FPAGE_vozm20120523
            UNION
            SELECT DISTINCT user_id FROM __tmp_PROJECTS_vozm20120523) q
            INNER JOIN users u ON u.uid = q.user_id AND u.is_banned = '0' 
                AND substr(u.subscr::text,8,1) = '1' AND u.uid != 103";

$pHost = str_replace("http://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];
$pMessage = "������������.

� ����� � ������������ �������� 22-23 ���, ���� �������� ��� ����������.

�� �������� ���� ��������� �� ��������� ���������� � ���������� ������� ������ �� 10 ����� (�������� �������� PRO ��� ������������� � �����������, ���������� �� ������� �������� � � ��������, ����������� �������). ������ ��������� ��������� � ������, ���� ����������������� ������ ���� ������� � ������ ���������� ����������� �����.

�� ���� ����������� �������� �� ������ ���������� � ���� http:/{������ ���������}/{$pHost}/help/?all.
�� ������ ��������� ����������� �� http:/{�������� ������������/��������}/{$pHost}/users/%USER_LOGIN%/setup/mailer/ ������ ��������.

� ���������, http:/{Free-lance.ru}/{$pHost}/";

// ----------------------------------------------------------------------------------------------------------------
// -- �������� ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$DB = new DB('plproxy');
$master = new DB('master');
$cnt = 0;


$sender = $master->row("SELECT * FROM users WHERE login = ?", $sender);
if (empty($sender)) {
    die("Unknown Sender\n");
}

echo "Send personal messages\n";

// �������������� ��������
$msgid = $DB->val("SELECT masssend(?, ?, '{}', '')", $sender['uid'], $pMessage);
if (!$msgid) die('Failed!');

// ��������, �� �������� ��������� � ������-�� �������
$i = 0;
while ($users = $master->col("{$sql} LIMIT 30000 OFFSET ?", $i)) {
    $DB->query("SELECT masssend_bind(?, {$sender['uid']}, ?a)", $msgid, $users);
    $i = $i + 30000;
    $cnt++;
}
// �������� �������� � �����
$DB->query("SELECT masssend_commit(?, ?)", $msgid, $sender['uid']); 


echo "OK. Total: {$cnt} users\n";