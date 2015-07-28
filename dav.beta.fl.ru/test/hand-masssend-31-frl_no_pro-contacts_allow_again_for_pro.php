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

// ������ �� pro �����������, �������������� (active = true), ������������ (is_banned = B'0'), � ����������� ����������

$sql = "SELECT DISTINCT(uid), email, login, uname, usurname, subscr FROM freelancer AS u LEFT JOIN
                orders o ON o.from_id = u.uid WHERE is_banned = B'0'
        AND (substring(subscr from 8 for 1)::integer = 1) 
        AND                 
                (o.payed IS NULL                
                 OR o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval <= now()
                 OR o.active='false'
                 OR (NOW() <= freeze_from_time::date AND NOW() > freeze_to_time)
                )
        "; //freelancer


//$sql = "SELECT uid, email, login, uname, usurname FROM users WHERE email IN('jusoft@yandex.ru', 'lamzin80@mail.ru')"; // !! 

if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // ������� � ������ ���� ����������� ��������� HTTPS �� �������� (��� ����� � ��)
} else {
    $pHttp = 'http';
}
$pHost = str_replace("$pHttp://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];

$pMessage = "������������!

� 5 ������� ������������ � ����������� � ���������������� ��������� ����� �������� ������ ���������� (e-mail, ICQ, Skype � �.�.), ������� ������ ���������� ����������� ��� ���� ������� PRO.
��� �������� PRO ���� �������� �� ����� �������������, �� ������� ������� � �������� �����������, �� ��������� �� ������� � �������� ������� ��� PRO�, ���������� ������� ����� 5 ������� ����� ����������� � ���������� 40% �� ������ ���������� ��������. ��� ������, ��� �� ������� ������������� ���������� � ������.

��������� � ������������ �������� PRO � � ��� �� ��������:
<ul><li>�������� ��������: ���������� � ��� ����� ���� ������������� � ������������ ������ ��������� � ����, ���� ���� �� ������ ����� �� �������� �� ����;</li><li>����������� ��������� ���� �������� � �������� � ����������;</li><li>����������� ������: �� ������� �������� �� ��� �������������� �������;</li><li>������ � �������� ������� ��� PRO�: �� ����������, �� ������ ����, ��� ������ ������� ��������, �� 30%;</li><li>���������� ���������: ����� ���������� ����� � ��������� ����� ���������;</li><li>�������������� �������������: ����������� ������ ������� ����� � ���������� �������� �������� �����������;</li><li>�������� �������;</li></ul> � ������ ������������. 

{$pHttp}:/{���������� ������� PRO}/{$pHost}/payed/?utm_source=newsletter4&utm_medium=email&utm_campaign=you_should_PRO
�� ���� ����������� �������� �� ������ ���������� � ���� http:/{������ ���������}/{$pHost}/help/?all<i>.</i>
�� ������ ��������� ����������� ��http:/{�������� &laquo;�����������/��������&raquo;}/{$pHost}/users/%USER_LOGIN%/setup/mailer/<span> ������</span> ��������.

�������� ������,
������� http:/{Free-lance.ru}/{$pHost}/
";

$eSubject = "Free-lance.ru: �� �������� ���� �������";

$eMessage = "<p>������������!</p>
<p>� 5 ������� ������������ � ����������� � ���������������� ��������� ����� �������� ������ ���������� (e-mail, ICQ, Skype � �.�.), ������� ������ ���������� ����������� ��� ���� ������� PRO. </p>

<p>��� �������� PRO ���� �������� �� ����� �������������, �� ������� ������� � �������� �����������, �� ��������� �� ������� � �������� ������� ��� PRO�, ���������� ������� ����� 5 ������� ����� ����������� � ���������� 40% �� ������ ���������� ��������. ��� ������, ��� �� ������� ������������� ���������� � ������.</p>

<p>��������� � ������������ �������� PRO � � ��� �� ��������:</p>
<ul>
<li>�������� ��������: ���������� � ��� ����� ���� ������������� � ������������ ������ ��������� � ����, ���� ���� �� ������ ����� �� �������� �� ����;</li>
<li>����������� ��������� ���� �������� � �������� � ����������;</li>
<li>����������� ������: �� ������� �������� �� ��� �������������� �������;</li>
<li>������ � �������� ������� ��� PRO�: �� ����������, �� ������ ����, ��� ������ ������� ��������, �� 30%;</li>
<li>���������� ���������: ����� ���������� ����� � ��������� ����� ���������;</li>
<li>�������������� �������������: ����������� ������ ������� ����� � ���������� �������� �������� �����������;</li>
<li>�������� �������;</li>
</ul>
<p>� ������ ������������. </p>

<p><a href=\"{$eHost}/payed/?utm_source=newsletter4&utm_medium=email&utm_campaign=you_should_PRO\" target=\"_blank\">���������� ������� PRO</a></p>
<br/>
<p>��������� ���������� ��������� � ��������������� <a href=\"{$eHost}/help/?q=1037&utm_source=newsletter4&utm_medium=rassylka&utm_campaign=rating_freelancer\" target=\"_blank\">������� �������</a>.</p>

<p>�� ���� ����������� �������� �� ������ ���������� � ���� <a href=\"{$eHost}/help/?all\" target=\"_blank\">������ ���������</a>.</p>
<br/><p>�� ������ ��������� ����������� �� <a href=\"{$eHost}/users/%USER_LOGIN%/setup/mailer/\" target=\"_blank\">�������� ������������/��������</a> ������ ��������.</p>
<br/>
�������� ������!<br/>
������� <a href='{$eHost}/' target='_blank'>Free-lance.ru</a>";
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
