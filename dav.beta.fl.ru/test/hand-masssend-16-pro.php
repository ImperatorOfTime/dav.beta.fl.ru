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
//http://www.free-lance.ru/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended
// ������ �����������
$sql = "SELECT f.uid, f.email, f.login, f.uname, f.usurname, f.subscr
            FROM freelancer f 
            WHERE is_banned = B'0' AND substr(f.subscr::text,8,1) = '1'
            AND is_pro = false";

$pHost = str_replace("https://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];
$pMessage = "������������!

���-���� � ��� �� ������ ��������� ������ � ����������� ������, �� � ���������� ��������� �����������. �� ����� ������� ���������������� ������� ���������� �����������, ������� ��������� ���� ��� ���� ������. ��� ���� ����� �� ����� ������� ���������� �� �����������, �� ����� ���������� https:/{������� PRO}/{$pHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended.

� https:/{��������� PRO}/{$pHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended �� ������� ���������� �������� �� �������, �������� ����������� �������� �� ������� ������ ��� PRO, ����������� �������, ����������� ��������� � ������ ������. � ���������� � ����� ������ �� ������� ��� �������� ��� ����������: ���������� ����������������� �������� ������������ ������������� � �������� � �������� ��������������� ������������. ���������� ���������� � ��������� ������� �� ���� ������� https:/{��������}/{$pHost}/freelancers/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended, � ������� ����������� ������.

������ �������� � ���������� ������ ������ ��� ���� � ��� ����� ���� ������� �� ������������� ����������� ����� �������� https:/{������ �������� PRO}/{$pHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended!

��� https:/{�������� ������� PRO}/{$pHost}/help/?q=789&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended<span>?</span>

�� ���� ����������� �������� �� ������ ���������� � ���� https:/{������ ���������}/{$pHost}/help/?all.
�� ������ ��������� ����������� �� https:/{�������� ������������/��������}/{$pHost}/users/%USER_LOGIN%/setup/mailer/ ������ ��������.

�������� ������,
������� https:/{Free-lance.ru}/{$pHost}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended";


$eSubject = "������ �� ������� ��� �������� ��� ������������� �� Free-lance.ru";

$eMessage = "<p>������������!</p>

<p>
���-���� � ��� �� ������ ��������� ������ � ����������� ������, �� � ���������� ��������� �����������. �� ����� ������� ���������������� ������� ���������� �����������, ������� ��������� ���� ��� ���� ������. ��� ���� ����� �� ����� ������� ���������� �� �����������, �� ����� ���������� <a href='{$eHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended' target='_blank'>������� PRO</a>.
</p>

<p>
� <a href='{$eHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended' target='_blank'>��������� PRO</a> �� ������� ���������� �������� �� �������, �������� ����������� �������� �� ������� ������ ��� PRO, ����������� �������, ����������� ��������� � ������ ������. � ���������� � ����� ������ �� ������� ��� �������� ��� ����������: ���������� ����������������� �������� ������������ ������������� � �������� � �������� ��������������� ������������. ���������� ���������� � ��������� ������� �� ���� ������� <a href='{$eHost}/freelancers/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended' target='_blank'>��������</a>, � ������� ����������� ������.
</p>

<p>
������ �������� � ���������� ������ ������ ��� ���� � ��� ����� ���� ������� �� ������������� ����������� ����� �������� <a href='{$eHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended' target='_blank'>������ �������� PRO</a>!
</p>

<p>
��� <a href='{$eHost}/help/?q=789&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended' target='_blank'>�������� ������� PRO</a>?
</p>

<p>
�� ���� ����������� �������� �� ������ ���������� � ���� <a href='{$eHost}/help/' target='_blank'>������ ���������</a>.<br/>
�� ������ ��������� ����������� <a href='{$eHost}/unsubscribe?ukey=%UNSUBSCRIBE_KEY%' target='_blank'>�� ���� ��������</a> ������ ��������.
</p>

�������� ������!<br/>
������� <a href='{$eHost}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=pro_recommended' target='_blank'>Free-lance.ru</a>";

// ----------------------------------------------------------------------------------------------------------------
// -- �������� ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$plproxy = new DB('plproxy');
$DB = new DB('master');
$cnt = 0;


$sender = $DB->row("SELECT * FROM users WHERE login = ?", $sender);
if (empty($sender)) {
    die("Unknown Sender\n");
}

echo "Send personal messages\n";

// �������������� ��������
$msgid = $plproxy->val("SELECT masssend(?, ?, '{}', '')", $sender['uid'], $pMessage);
if (!$msgid) die('Failed!');

// ��������, �� �������� ��������� � ������-�� �������
$i = 0;
while ($users = $DB->col("{$sql} LIMIT 5000 OFFSET ?", $i)) {
    $plproxy->query("SELECT masssend_bind(?, {$sender['uid']}, ?a)", $msgid, $users);
    $i = $i + 5000;
}
// �������� �������� � �����
$plproxy->query("SELECT masssend_commit(?, ?)", $msgid, $sender['uid']); 
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
$res = $DB->query($sql);
while ($row = pg_fetch_assoc($res)) {
    $mail->recipient[] = array(
        'email' => $row['email'],
        'extra' => array('first_name' => $row['uname'], 'last_name' => $row['usurname'], 'USER_LOGIN' => $row['login'], 'UNSUBSCRIBE_KEY' => users::GetUnsubscribeKey($row["login"]) )
    );
    if (++$i >= 5000) {
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