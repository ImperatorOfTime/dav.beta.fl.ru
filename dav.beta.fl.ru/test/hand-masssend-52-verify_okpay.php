<?php

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

$eHost = $GLOBALS['host'];

$eSubject = "�� ����� ��� ��� ������� ������ �����������";

$eMessage = "<p>������������!</p>

<p>
�������� ����� ���������� ������ ����������� �� 10 ����� ����� Okpay.com. ����������� �������� ��� ���������� ���� �����, ��� ������� ���������, ��������� ������ �������� � �������� � ������ ������.
</p>

<p>
����������� �������� � �������� ��� ������������ ����������������� ������������:
</p>
<ul>
<li>����������� ������ �� ������� (�� ����� �������������);</li>
<li>�������  +20%;</li>
<li>������ � �������� ������� ��� �����������������;</li>
<li>������� ��������� ����������.</li>
</ul>
<p>
<a href='{$eHost}/promo/verification/?service=okpay&utm_source=newsletter4&utm_medium=email&utm_campaign=unverif_okpay' target='_blank'>������� � ���������� �� �����������</a>
</p>

<p>
�� ���� ����������� �������� ����������� � ���� <a href='http://feedback.free-lance.ru/' target='_blank'>������ ���������</a>.<br/>
�� ������ ��������� ����������� <a href='{$eHost}/unsubscribe?ukey=%UNSUBSCRIBE_KEY%' target='_blank'>�� ���� ��������</a>.
</p>

�������� ������ � <a href='{$eHost}/' target='_blank'>Free-lance.ru</a>!";

// ----------------------------------------------------------------------------------------------------------------
// -- �������� ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$DB = new DB('master');
$cnt = 0;


// ������ ����, ������������ (is_banned = B'0'), � ����������� ���������� (substring(subscr from 8 for 1)::integer = 1)
//sql = "SELECT uid, email, login, uname, usurname, subscr FROM users WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0'";
//���� �����������, ��� ������� ���������������� ����� ��������� ����� okpay � �� ����
$limit = 8000;  
$sql = "
(SELECT DISTINCT ff.user_id AS uid, u.login, u.email, u.uname, u.usurname, usk.key AS ukey FROM verify_ff AS ff 
   INNER JOIN users AS u ON ff.user_id = u.uid AND u.is_verify = false
   LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
   WHERE result = false AND is_banned = B'0'
)

UNION 

(SELECT DISTINCT wm.user_id AS uid, u.login, u.email, u.uname, u.usurname, usk.key AS ukey FROM verify_webmoney AS wm 
   INNER JOIN users AS u ON wm.user_id = u.uid AND u.is_verify = false
   LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
   WHERE result = false AND is_banned = B'0'
)

UNION 

(SELECT DISTINCT pskb.user_id AS uid, u.login, u.email, u.uname, u.usurname, usk.key AS ukey FROM verify_pskb AS pskb 
   INNER JOIN users AS u ON pskb.user_id = u.uid AND u.is_verify = false
   LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
   WHERE result = false AND is_banned = B'0'
)

UNION 

(SELECT DISTINCT yd.user_id AS uid, u.login, u.email, u.uname, u.usurname, usk.key AS ukey FROM verify_yd AS yd 
   INNER JOIN users AS u ON yd.user_id = u.uid AND u.is_verify = false
   LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
   WHERE result = false AND is_banned = B'0'
)
";

$sender = $DB->row("SELECT * FROM users WHERE login = ?", $sender);
if (empty($sender)) {
    die("Unknown Sender\n");
}

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
    if($row['email'] == '') continue;
    if ( strlen($row['ukey']) == 0 ) {
        $row['ukey'] = users::writeUnsubscribeKey($row["uid"], true);
    }
    $mail->recipient[] = array(
        'email' => $row['email'],
        'extra' => array('first_name' => $row['uname'], 'last_name' => $row['usurname'], 
                         'USER_LOGIN' => $row['login'],
                         'UNSUBSCRIBE_KEY' => $row['ukey'])
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