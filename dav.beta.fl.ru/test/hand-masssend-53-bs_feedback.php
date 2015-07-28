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

$eSubject = "�� �� �������� ����� � ������";

$eMessage = "<p>������������, %USER_LOGIN%!</p>

<p>
���������� ���, ��� �� %DATE_SBR% ���������� �������� ����� � ���������� ������ ��-%SBR_ID%. ����� ��������� ���� ����������� ���������� ������ ����� �������.
</p>

<p>
���������� � ���������� ������� � ���������� ������, � ����� ������ �� ��� ������������ ������� �� ������ ����� � ����� <a href='https://feedback.fl.ru/knowledgebase/category/id/31' target='_blank'>���������� ���������</a>.
</p>

<p>
�� ������ ��������� ����������� <a href='{$eHost}/unsubscribe?ukey=%UNSUBSCRIBE_KEY%' target='_blank'>�� ���� ��������</a>.
</p>

�������� ������ � <a href='{$eHost}/' target='_blank'>Free-lance.ru</a>!";

// ----------------------------------------------------------------------------------------------------------------
// -- �������� ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$DB = new DB('master');
$cnt = 0;



$limit = 8000;  
$sql = "
SELECT u.uid, u.login, u.email, u.uname, u.usurname, usk.key AS ukey, ss.closed_time as closed, ss.sbr_id, ss.closed_time + interval '10 days' as done_feedback
FROM  sbr s
INNER join sbr_stages ss ON ss.sbr_id = s.id
INNER join employer u on u.uid = s.emp_id
LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
WHERE ss.status IN(4,7) AND ss.emp_feedback_id IS NULL AND ss.closed_time + interval '10 days' > NOW();
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
                         'SBR_ID' => $row['sbr_id'],
                         'DATE_SBR' => date('d.m.Y', strtotime($row['closed'])),
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