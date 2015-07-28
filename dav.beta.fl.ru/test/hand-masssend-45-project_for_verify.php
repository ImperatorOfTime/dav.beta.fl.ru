<?php
/**
 * ����������� �������������
 * */
ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp.php';
require_once '../classes/users.php';

/**
 * ����� ������������ �� ���� �������������� ��������
 * 
 */
$sender = 'admin';

// ������ ���������� �������������, ������������ (is_banned = B'0'), � ����������� ����������

$sql = "SELECT u.uid, u.email, u.login, u.uname, u.usurname, usk.key AS ukey  FROM users AS u
LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
WHERE  u.is_banned = B'0' AND (substring(subscr from 8 for 1)::integer = 1)
ORDER BY u.uid"; //��� ����������� ���������� � ������ ����� "�������� �������� �� �������������" � users

//$sql = "SELECT u.uid, email, login, uname, usurname, usk.key AS ukey FROM users AS u LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid WHERE login IN('land_f', 'land_e2')"; // !! 

if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // ������� � ������ ���� ����������� ��������� HTTPS �� �������� (��� ����� � ��)
} else {
    $pHttp = 'http';
}
$pHost = str_replace("$pHttp://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];

$eSubject = "��������� ������� ��� ���������������� �������������";
//<a href=\"{$eHost}/promo/verification?utm_source=newsletter4&utm_medium=email&utm_campaign=verification\" target=\"_blank\">������ �����������</a>
$eMessage = "<p>������������!</p>
<p>� ������ �� ��������� ����������� � ��������� ������������� ���������� ������, ������� �������� ��� ����������� � �������������. ����� ��������� ����������� ����������� ������� ��� ���������������� �������������. 
</p><p>� ���������, ������� ��� ���� ������� ����: �� ��� ������ ��������� ����������� ������ ��������� ���������� �����������, ������� �� ������������� ���������� ����� ��������. ������ ����� ������������ ����� ������ ����������� ������� ��� ���������������� �������������.</p>
<p>����������, ��� ���������������� ������������ �������� �������������� ����������� �� ����� � ������� ������� �� ������� ��� �������������, ��� � �����������. </p>
<p><a href=\"http://feedback.free-lance.ru/article/details/id/1270?utm_source=newsletter4&utm_medium=email&utm_campaign=prverific\" target=\"_blank\">������ ��������� ��� �����������</a></p>
<p>�� ���� ����������� �������� �� ������ ���������� � ���� <a href=\"http://feedback.free-lance.ru?utm_source=newsletter4&utm_medium=email&utm_campaign=prverific\" target=\"_blank\">������ ���������</a>.</p>
<br/><p>�� ������ ��������� ����������� �� <a href=\"{$eHost}/unsubscribe/?ukey=%UNSUBSCRIBE_KEY%&utm_source=newsletter4&utm_medium=email&utm_campaign=prverific\" target=\"_blank\">���� ��������</a>.</p>

�������� ������!<br/>
������� <a href='{$eHost}/' target='_blank'>Free-lance.ru</a>";

// ----------------------------------------------------------------------------------------------------------------
// -- �������� ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$proxy = new DB('plproxy');
$DB = new DB('master');
$cnt = 0;

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
    if ( strlen($row['ukey']) == 0 ) {
        $row['ukey'] = users::writeUnsubscribeKey($row["uid"]);
    }
    if ( is_email($row['email']) ) {
        $mail->recipient[] = array(
            'email' => $row['email'],
            'extra' => array('first_name' => $row['uname'], 'last_name' => $row['usurname'], 'UNSUBSCRIBE_KEY' => $row['ukey'])
        );
        if (++$i >= 30000) {
            $mail->bind($spamid);
            $mail->recipient = array();
            $i = 0;
        }
    }
    $cnt++;
}
if ($i) {
    $mail->bind($spamid);
    $mail->recipient = array();
}

echo "OK. Total: {$cnt} users\n";
