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

// ������ �����������
$sql = "SELECT uid, email, login, uname, usurname, subscr FROM freelancer WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0'";

$pHost = str_replace("http://", "", $GLOBALS['host']);
$pMessage = "
������������!

� ������� ������������ �� ����� ����� ���� �������, � ����-�� �� ������, � ����-�� ������. � ����� �� �� �����? �� �������: ��� ���� ��� �������, ��� ������ ������ ����� ���������� ������!

������������, ���������� ������������ �� ���� �������, ������������� �� ������� ���������� � ����������� ������ �������� ������������� � ������� ��������� � ���� ��� ������� �� ����� � ���������������� ���������. 

������� ���������� ������������ �� ��������� ����������. � �������, ���������� ����� ������, ���� �� ������ ���� �������� �� ����, ��������� http:/{���� �������}/{$pHost}/users/%USER_LOGIN%/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating<span>,</span> �������� ������ � ���������. ����������� ������������ � ����������� ������������� � �� ����������� �������������� � ��� ������ � ��� ������������� ������������, ��� ���� �������. ������������� ��������� �������� � ������� � ��� �������� http:/{PRO}/{$pHost}/payed/?utm_source=newsleter4&utm_medium=rassilka&utm_campaign=ratingPRO<span>,</span> ������� �� �������, ������� � ����� ������ ������� � ��������� ����� ����� � �������� http:/{�������}/{$pHost}/articles/?utm_source=newsleter4&utm_medium=rassilka&utm_campaign=rating<span>,</span> ������ � ��������� �� ������������� � ������ ������. ����� ����, �� ������ �������� ����� ��������. ������ ��� ���� ����� �������� ����� http:/{�����}/{$pHost}/help/?q=812&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating<span>.</span> 

http:/{������� ���� �������!}/{$pHost}/users/%USER_LOGIN%/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating

�� ���� ����������� �������� �� ������ ���������� � ���� http:/{������ ���������}/$pHost/help/?all&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating<span>.</span>
�� ������ ��������� ����������� �� http:/{�������� ������������/��������}/{$pHost}/users/%USER_LOGIN%/setup/mailer/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating ������ ��������.

�������� ������,
������� Free-lance.ru";

$eSubject = "��� �������� ��������� �� Free-lance.ru";

$eMessage = "<p>������������!</p>

<p>
� ������� ������������ �� ����� ����� ���� �������, � ����-�� �� ������, � ����-�� ������. � ����� �� �� �����? �� �������: ��� ���� ��� �������, ��� ������ ������ ����� ���������� ������!
</p>

<p>
������������, ���������� ������������ �� ���� �������, ������������� �� ������� ���������� � ����������� ������ �������� ������������� � ������� ��������� � ���� ��� ������� �� ����� � ���������������� ���������. 
</p>

<p>
������� ���������� ������������ �� ��������� ����������. � �������, ���������� ����� ������, ���� �� ������ ���� �������� �� ����, ��������� <a href='{$GLOBALS['host']}/users/%USER_LOGIN%/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating'>���� �������</a>, �������� ������ � ���������. ����������� ������������ � ����������� ������������� � �� ����������� �������������� � ��� ������ � ��� ������������� ������������, ��� ���� �������. 
������������� ��������� �������� � ������� � ��� �������� <a href='{$GLOBALS['host']}/payed/?utm_source=newsleter4&utm_medium=rassilka&utm_campaign=rating'>PRO</a>, ������� �� �������, ������� � ����� ������ ������� � ��������� ����� ����� � �������� <a href='{$GLOBALS['host']}/articles/?utm_source=newsleter4&utm_medium=rassilka&utm_campaign=rating'>�������</a>, ������ � ��������� �� ������������� � ������ ������. ����� ����, �� ������ �������� ����� ��������. ������ ��� ���� ����� �������� ����� <a href='{$GLOBALS['host']}/help/?q=812&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating'>�����</a>. 
</p>

<p><a href='{$GLOBALS['host']}/users/%USER_LOGIN%/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating'>������� �� ����</a> � ������� ���� �������!</p>

<p>
�� ���� ����������� �������� �� ������ ���������� � ���� <a href='{$GLOBALS['host']}/help/?all&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating'>������ ���������</a>.<br/>
�� ������ ��������� ����������� �� <a href='{$GLOBALS['host']}/users/%USER_LOGIN%/setup/mailer/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating'>�������� ������������/��������</a> ������ ��������.
</p>

<p>
�������� ������,<br/>
������� <a href='{$GLOBALS['host']}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rating'>Free-lance.ru</a>
</p>";

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