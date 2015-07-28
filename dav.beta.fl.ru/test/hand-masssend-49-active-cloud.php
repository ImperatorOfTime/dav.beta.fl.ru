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

if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // ������� � ������ ���� ����������� ��������� HTTPS �� �������� (��� ����� � ��)
} else {
    $pHttp = 'http';
}
$pHost = str_replace("$pHttp://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];

$eSubject = "���� ������ �� ActiveCloud?";
$eMessage = "<p>������������!</p>
<p>�� ��������� � ������ ����������� � ��������� ActiveCloud, ������� �������� �������-�����������. � ����� ����� ������� ActiveCloud ����� ���� ������������� ����� Free-lance.ru ������ �� ������� � ������� 40% � ������ �� �������� ������ � ������� 10%.</p>
<p>������� � ��������: ActiveCloud ������������� �������� ������� � ���������������� �������� ������� ������� � 2003 ���� ��� ����� ��� 25 000 ����� �������� � �� � ���� ������ ������������ ���. ActiveCloud ������ � ������ Softline � ������� ������������� ��������, ������������������ �� �������������� �� � ����������� ������� ������ ��������� IT-�����.</p>
<p>��� ��������� ������ ������� ��� ������ �����-��� Freelance � ������� ������ �����������. ��������, ��� ����������! ����� ��������� ���������� �� ����� ��������� �� ����� 
<a href='http://www.activecloud.ru/ru/freelance/?utm_source=newsletter4&utm_medium=email&utm_campaign=activcloud' target='_blank'>ActiveCloud</a>
</p>
<p>�� ���� ����������� �������� ����������� � ���� <a href=\"http://feedback.free-lance.ru?utm_source=newsletter4&utm_medium=email&utm_campaign=activcloud\" target=\"_blank\">������ ���������</a>.</p>
<br/><p>�� ������ ��������� ����������� �� <a href=\"{$eHost}/unsubscribe/?ukey=%UNSUBSCRIBE_KEY%&utm_source=newsletter4&utm_medium=email&utm_campaign=activcloud\" target=\"_blank\">���� ��������</a>.</p>

<p>������� <a href=\"{$eHost}/?utm_source=newsletter4&utm_medium=email&utm_campaign=activcloud\" target=\"_blank\">Free-lance.ru</a>.</p>";

//���� ���������� � �����������
$sql = "SELECT u.uid, email, login, uname, usurname, usk.key AS ukey 
        FROM users AS u
        LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid 
        WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' 
        LIMIT 3000 OFFSET ?";
//die($sql);
$sql = "SELECT u.uid, email, login, uname, usurname, usk.key AS ukey 
        FROM users AS u
        LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid 
        WHERE login = 'land_f'";
// ----------------------------------------------------------------------------------------------------------------
// -- �������� ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$M  = new DB('master');
$cnt = 0;

$mail = new smtp;
$mail->subject = $eSubject;  // ��������� ������
$mail->message = $eMessage; // ����� ������
$mail->recipient = ''; // �������� '����������' ��������� ������
$spamid = $mail->send('text/html');
if (!$spamid) die('Failed!');
// � ����� ������� �������� �������, �� ��� ������ �� ����������!

$mail->recipient = array();
$i = 0;
//��������� ���������
while ($rows = $M->rows($sql, $i)) {
    $pm_users = array();
	foreach ($rows as $row) {
		unset( $csv_users[ $row["fromuser_id"] ] );
	    if ( strlen($row['ukey']) == 0 ) {
	        $row['ukey'] = users::writeUnsubscribeKey($row["uid"]);
	    }
	    if ( is_email($row['email']) ) {
	        $mail->recipient[] = array(
	            'email' => $row['email'],
	            'extra' => array('first_name' => $row['uname'], 'last_name' => $row['usurname'], 'UNSUBSCRIBE_KEY' => $row['ukey'])
	        );
	   
	        $mail->bind($spamid);
	         $mail->recipient = array();
	   
	        $cnt++;
	    }
	    $pm_users[] = $row["uid"];
	    $i++;
	}
}
echo "OK. Total: {$cnt} users\n";
