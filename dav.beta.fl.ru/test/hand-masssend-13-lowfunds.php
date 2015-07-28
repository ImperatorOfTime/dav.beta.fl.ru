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
$sql = "SELECT f.uid, f.email, f.login, f.uname, f.usurname, f.subscr
            FROM account ac 
        INNER JOIN freelancer f ON f.uid = ac.uid AND is_banned = B'0' AND substr(f.subscr::text,8,1) = '1'
        WHERE ac.sum >= 3 AND ac.sum <= 6.99
        AND id NOT IN (SELECT billing_id FROM account_operations WHERE op_date::date >= NOW()-'1 month'::interval
                        AND op_code NOT IN (12, 23)
                        AND  NOT (op_code IN (16, 52, 66, 67, 68, 17, 69, 83, 84, 85) AND ammount >= 0)  )";

$pHost = str_replace("http://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];
$pMessage = "������������!

������� ������� �� http:/{����� ������ �����}/{$pHost}/bill/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm ��������� ��� ���������������� ���� ������ �� ����� � �������� ��� ������ �������. �� ������ ��������� ���������� FM ��������� �������:

<ul>";
$pMessage .= "<li>���������� http:/{�������������� �������������}/{$pHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm, ����� �������� ������ �� ����� ������������ ������������; </li>";
$pMessage .= "<li>����������� �� ��������� http:/{�� ������� ��������}/{$pHost}/pay_place/top_payed.php?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm � �� ������� � ���� � ����� ������� �������� ��� ������������� ���������� � ������ �����������, � �� ��������� http:/{� �������� �����������}/{$pHost}/pay_place/top_payed.php?catalog&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm � �� ������� ����������, ����� ������������ ������� �� ����� ������ ����������� � ��������;  </li>";
$pMessage .= "<li>��������� ���������� � ������������ ����� ����� � ������� http:/{��������}/{$pHost}/public/offer/?kind=8&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm � ��������� ������ ��� ����. </li>";
$pMessage .= "</ul>
�� ��������� ����������� �������� ������������� � ��������� ���� ������!

�� ���� ����������� �������� �� ������ ���������� � ���� http:/{������ ���������}/{$pHost}/help/.
�� ������ ��������� ����������� �� http:/{�������� ������������/��������}/{$pHost}/users/%USER_LOGIN%/setup/mailer/ ������ ��������.

�������� ������,
������� http:/{Free-lance.ru}/{$pHost}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm";


$eSubject = "������� ����� ���������� �� Free-lance.ru ����� ������";

$eMessage = "<p>������������!</p>

<p>
������� ������� �� <a href='{$eHost}/bill/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm'>����� ������ �����</a> ��������� ��� ���������������� ���� ������ �� ����� � �������� ��� ������ �������. �� ������ ��������� ���������� FM ��������� �������:
</p>

<ul>
<li>���������� <a href='{$eHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm'>�������������� �������������</a>, ����� �������� ������ �� ����� ������������ ������������; </li>
<li>����������� �� ��������� <a href='{$eHost}/pay_place/top_payed.php?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm'>�� ������� ��������</a> � �� ������� � ���� � ����� ������� �������� ��� ������������� ���������� � ������ �����������, � �� ��������� <a href='{$eHost}/pay_place/top_payed.php?catalog&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm'>� �������� �����������</a> � �� ������� ����������, ����� ������������ ������� �� ����� ������ ����������� � ��������;  </li>
<li>��������� ���������� � ������������ ����� ����� � ������� <a href='{$eHost}/public/offer/?kind=8&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm'>��������</a> � ��������� ������ ��� ����. </li>
</ul>

<p>
�� ��������� ����������� �������� ������������� � ��������� ���� ������!
</p>

<p>
�� ���� ����������� �������� �� ������ ���������� � ���� <a href='{$eHost}/help/' target='_blank'>������ ���������</a>.<br/>
�� ������ ��������� ����������� �� <a href='{$eHost}/users/%USER_LOGIN%/setup/mailer/' target='_blank'>�������� ������������/��������</a> ������ ��������.
</p>

�������� ������!<br/>
������� <a href='{$eHost}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=ostatki_fm' target='_blank'>Free-lance.ru</a>";

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