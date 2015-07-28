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

// ��������� ���� ������� (������������ � ����������) � ������������� � �� ����������� �� �������
$sql = "
SELECT u.uid, u.email, u.login, u.uname, u.usurname
FROM sbr_reqv r 
INNER JOIN users u ON u.uid = r.user_id
WHERE 
u.is_banned = B'0'
AND r.form_type = 2 
AND (r._2_address_jry IS NULL OR r._2_address_jry = '')
AND ( 
  (SELECT id FROM sbr s WHERE s.emp_id = r.user_id AND s.scheme_type = 4 LIMIT 1) IS NOT NULL 
    OR
  (SELECT id FROM sbr s WHERE s.frl_id = r.user_id AND s.scheme_type = 4 LIMIT 1) IS NOT NULL 
)"; 

$eHost = $GLOBALS['host'];

$eSubject = "Free-lance.ru: �� �� ������� ������������ ���������� �� �������� ���������";

$eMessage = "<p>������������!</p>

<p>�� ��������, ��� �� �� ������� ���� ����������� ����� � �������. ��� ���������� �������� ����� ������ � ������������ ��� ������������ ����������� ���������� ��� �����������. ����������� ������ ��� ��������� ���� ������������ ����� �� �������� ��������� �� 20 ������ 2013 ����.</p>

<p>���������� ���, ��� ��� ����������� ������ ����� ������ ������� ��� ����� ���������� ��������� ��� ������������ ���� �� �������� ���������. ��������� ���������� ��������� � <a href=\"{$eHost}/help/?q=1034\" target=\"_blank\">����</a> ������� �������.</p><br/>

<p>�� ���� ����������� �������� �� ������ ���������� � ���� <a href=\"{$eHost}/help/?all\" target=\"_blank\">������ ���������</a>.</p>
<p>�� ������ ��������� ����������� �� <a href=\"{$eHost}/users/%USER_LOGIN%/setup/mailer/\" target=\"_blank\">�������� ������������/��������</a> ������ ��������.</p>
<br/>
�������� ������!<br/>
������� <a href='{$eHost}/' target='_blank'>Free-lance.ru</a>";

// ----------------------------------------------------------------------------------------------------------------
// -- �������� ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$master = new DB('master');
$cnt = 0;

$sender = $master->row("SELECT * FROM users WHERE login = ?", $sender);
if (empty($sender)) {
    die("Unknown Sender\n");
}

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
