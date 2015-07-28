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

// ������ �����������, �������������� (active = true), ������������ (is_banned = B'0'), � ����������� ���������� (substring(subscr from 8 for 1)::integer = 1)
$sql = "SELECT uid, email, login, uname, usurname, subscr FROM freelancer WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' AND active = true"; 

$pHost = str_replace("http://", "", $GLOBALS['host']);
if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // ������� � ������ ���� ����������� ��������� HTTPS �� �������� (��� ����� � ��)
} else {
    $pHttp = 'http';
}
$eHost = $GLOBALS['host'];

$pMessage = "
������������!

�� �����, ��� ������� ������ ����� ����� ������ � �������������, �������� � ������ ������ � � ���������. � ������ ������� ����� ��������� �����, ������� ���������� � ������ ����������� �����, &mdash; �������� ������ ���� � ����! 
   
� 27 ���� �� 12 ������� ��� ������� �������� PRO �� ����� ����� {$pHttp}:/{����������� �� ��������}/{$pHost}/olimp/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp � �������� ����������� ����� �� 1 FM. �� ������� ����� ��� ���� ����� ����������, ���������� � �������� �������, �� �������� ������� ������, &mdash; ��� �� ��� ������� ������� � ��������.

{$pHttp}:/{������ � ������!}/{$pHost}/olimp/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp

�� ���� ����������� �������� �� ������ ���������� � ���� {$pHttp}:/{������ ���������}/{$pHost}/help/?all&utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp<i>.</i>
�� ������ ��������� ����������� �� {$pHttp}:/{�������� ������������/��������}/{$pHost}/users/%USER_LOGIN%/setup/mailer/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp ������ ��������.

�������� ������!
������� {$pHttp}:/{Free-lance.ru}/{$pHost}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=olimp
";

$eSubject = "����������� �������� �� 1FM";

$eMessage = "<p>������������!</p>

<p>
�� �����, ��� ������� ������ ����� ����� ������ � �������������, �������� � ������ ������ � � ���������. � ������ ������� ����� ��������� �����, ������� ���������� � ������ ����������� �����, &mdash; �������� ������ ���� � ����!
</p>

<p>
� 27 ���� �� 12 ������� ��� ������� �������� PRO �� ����� ����� <a href='{$eHost}/olimp/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp'>����������� �� ��������</a> � �������� ����������� ����� �� 1 FM. �� ������� ����� ��� ���� ����� ����������, ���������� � �������� �������, �� �������� ������� ������, &mdash; ��� �� ��� ������� ������� � ��������.
</p>

<p>
<a href='{$eHost}/olimp/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp'>������ � ������!</a>
</p>

<p>
�� ���� ����������� �������� �� ������ ���������� � ���� <a href='{$eHost}/help/?all&utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp' target='_blank'>������ ���������</a>.<br/>
�� ������ ��������� ����������� �� <a href='{$eHost}/users/%USER_LOGIN%/setup/mailer/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp' target='_blank'>�������� ������������/��������</a> ������ ��������.
</p>

�������� ������!<br/>
������� <a href='{$eHost}/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=olimp' target='_blank'>Free-lance.ru</a>";

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
}
// �������� �������� � �����
$DB->query("SELECT masssend_commit(?, ?)", $msgid, $sender['uid']); 
echo "Send email messages\n";

$mail = new smtp;
$mail->subject = $eSubject;  // ��������� ������
$mail->message = $eMessage; // ����� ������
$mail->recipient = ''; // �������� '����������' ��������� ������
$spamid = $mail->send('text/html');
if (!$spamid) die('Failed!');
// � ����� ������� �������� �������, �� ��� ������ �� ����������!
// �������� ��� ����� �������� ������ ����������� � ������-���� �������
$i = 0;
$mail->recipient = array();
$res = $master->query($sql);
while ($row = pg_fetch_assoc($res)) {
    $mail->recipient[] = array(
        'email' => $row['email'],
        'extra' => array('first_name' => $row['uname'], 'last_name' => $row['usurname'], 'USER_LOGIN' => $row['login'])
    );
    if (++$i >= 30000) {
        $mail->bind($spamid);
        $mail->recipient = array();
        $i = 0;
    }
    $cnt++;
}
if ($i) {
    $mail->bind($spamid);
    $mail->recipient = array();
}

echo "OK. Total: {$cnt} users\n";