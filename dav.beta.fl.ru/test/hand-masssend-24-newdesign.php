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

// ���� �������������, �������������� (active = true), ������������ (is_banned = B'0'), � ����������� ���������� (substring(subscr from 8 for 1)::integer = 1)
$sql = "SELECT uid, email, login, uname, usurname, subscr FROM users WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' AND active = true"; 

$pHost = str_replace("http://", "", $GLOBALS['host']);
if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // ������� � ������ ���� ����������� ��������� HTTPS �� �������� (��� ����� � ��)
} else {
    $pHttp = 'http';
}
$eHost = $GLOBALS['host'];


$eSubject = "����������� ������ � �������������� ������� Free-lance.ru";

$eMessage = "<p>������������!</p>

<p>
������ ���������� � ���, ��� �� �������� ������ ������� �������� ����� � �������� ������� ���� ������������. ����� ����, � ��� �������� ��������� ������, ������� ������� ����� ������������� ������������������ � �������� ��������� ����������� �� �������� �������� �����. ����� �������� ��� ���� ����������� ������� � �<a href='{$pHttp}://www.free-lance.ru/blogs/free-lanceru/704501/obnovleniya-na-sayte.html?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=new_desigh' target='_blank'>������</a>�.
</p>

<p>
<a href='{$eHost}/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=new_desig' target='_blank'>������� �� ���� � ��� ������� ������ �������!</a>
</p>

<p>
�� ���� ����������� �������� �� ������ ���������� � ���� <a href='{$eHost}/help/?all&utm_source=newsletter4&utm_medium=rassylka&utm_campaign=new_design' target='_blank'>������ ���������</a>.<br/>
�� ������ ��������� ����������� �� <a href='{$eHost}/users/%USER_LOGIN%/setup/mailer/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=new_design' target='_blank'>�������� ������������/��������</a> ������ ��������.
</p>

�������� ������!<br/>
������� <a href='{$eHost}/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=new_design' target='_blank'>Free-lance.ru</a>";

// ----------------------------------------------------------------------------------------------------------------
// -- �������� ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$DB = new DB('plproxy');
$master = new DB('master');

if ( $eMessage != '' ) {
    echo "Send email messages\n";
    $mail = new smtp;
    $mail->subject   = $eSubject;
    $mail->message   = $eMessage;
    $mail->recipient = '';
    $spamid = $mail->send('text/html');
    if ( !$spamid ) {
        die("Failed!\n");
    }

    $i = 0;
    $c = 0;
    $mail->recipient = array();
    $res = $master->query($sql);
    while ($row = pg_fetch_assoc($res)) {
        $mail->recipient[] = array(
            'email' => "{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>",
            'extra' => array('USER_NAME' => $row['uname'], 'USER_SURNAME' => $row['usurname'], 'USER_LOGIN' => $row['login'])
        );
        if (++$i >= 30000) {
            $mail->bind($spamid);
            $mail->recipient = array();
            $i = 0;
            echo "{$c} users\n";
        }
        $c++;
    }
    if ($i) {
        $mail->bind($spamid);
        $mail->recipient = array();
    }
}

echo "OK. Total: {$c} users\n";