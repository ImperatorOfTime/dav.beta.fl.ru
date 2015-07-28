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

$sql = "SELECT uid, email, login, uname, usurname, subscr FROM users WHERE login = 'jb_work'";
$sql = "SELECT uid, email, login, uname, usurname, subscr FROM freelancer WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' AND country IN (2, 10, 38)";

$pHost = str_replace("http://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];
$pMessage = "
������������!

�� ���������������� ������ � �������� ����� Free-lance.ru �� ��������� 3 ���� � �������, ��� 30% ��������� ������ �������� ���: &laquo;�������� ������ � �� �������� �������&raquo;. ������������ ������ �������� ����� �������� &ndash; �������� �� �������� ����� ������ http:/{&laquo;������ ��� �����&raquo;}/{$pHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF.

������ ������ �������� ��� ���� ������������� ��� ����������� �� ������ ����������. �������� ��������, ���������� �� http:/{&laquo;������� ��� �����&raquo;}/{$pHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF<i>,</i> ����� �������� �� ������ �� ���� � �����, �� � �� ����� � ����������� ��������� �������� ������.������ � WebMoney.

������� ����� http:/{&laquo;������ ��� �����&raquo;}/{$pHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF, �� �� �������� � ���������� ��������, ��������� � ��������� ����������� ���� ������ � ��������� &laquo;��������&raquo; ����������, ��������������� ������������ � �� ������� �������� � ���������� ������. ��� ������� �������������� � �� ����� ��������� � ��������.

����� �������������� ����� ������ http:/{&laquo;������ ��� �����&raquo;}/{$pHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF �� �������� ������������ �� �������������, ������� �������� ������� � ��� ��� � �������������. ��� ������ �� ����� ������������ � ���, ��� �� ���������� ��������� � �� ������� �������, &ndash; ����� ����� ������� �� ���������� ����� �������. ����� ����, �������, ����������� ����� ������ ������, ����������� �������� ��� ������� �� Free-lance.ru.

http:/{������ ������ � ������� &laquo;������ ��� �����&raquo;.}/{$pHost}/help/?c=41&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF

�� ���� ����������� �������� �� ������ ���������� � ���� http:/{������ ���������}/{$pHost}/help/?all<i>.</i>
�� ������ ��������� ����������� ��http:/{�������� &laquo;�����������/��������&raquo;}/{$pHost}/users/%USER_LOGIN%/setup/mailer/<span> ������</span> ��������.

�������� ������,
������� http:/{Free-lance.ru}/{$pHost}/";


$eSubject = "��������� ��� �����!";

$eMessage = "<p>������������!</p>

<p>
�� ���������������� ������ � �������� ����� Free-lance.ru �� ��������� 3 ���� � �������, ��� 30% ��������� ������ �������� ���: &laquo;�������� ������ � �� �������� �������&raquo;. ������������ ������ �������� ����� �������� &ndash; �������� �� �������� ����� ������ <a href='{$eHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF'>&laquo;������ ��� �����&raquo;</a>. 
</p>

<p>
������ ������ �������� ��� ���� ������������� ��� ����������� �� ������ ����������. �������� ��������, ���������� �� <a href='{$eHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF'>&laquo;������� ��� �����&raquo;</a>, ����� �������� �� ������ �� ���� � �����, �� � �� ����� � ����������� ��������� �������� ������.������ � WebMoney.
</p>

<p>
������� ����� <a href='{$eHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF'>&laquo;������ ��� �����&raquo;</a>, �� �� �������� � ���������� ��������, ��������� � ��������� ����������� ���� ������ � ��������� &laquo;��������&raquo; ����������, ��������������� ������������ � �� ������� �������� � ���������� ������. ��� ������� �������������� � �� ����� ��������� � ��������.
</p>

<p>
����� �������������� ����� ������ <a href='{$eHost}/promo/sbr/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF'>&laquo;������ ��� �����&raquo;</a> �� �������� ������������ �� �������������, ������� �������� ������� � ��� ��� � �������������. ��� ������ �� ����� ������������ � ���, ��� �� ���������� ��������� � �� ������� �������, &ndash; ����� ����� ������� �� ���������� ����� �������. ����� ����, �������, ����������� ����� ������ ������, ����������� �������� ��� ������� �� Free-lance.ru.
</p>

<p>
<a href='{$eHost}/help/?c=41&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=SBR_Freelancer_ne_RF'>������ ������ � ������� &laquo;������ ��� �����&raquo;.</a>
</p>

<p>
�� ���� ����������� �������� �� ������ ���������� � ���� <a href='{$eHost}/help/?all' target='_blank'>������ ���������</a>.<br/>
�� ������ ��������� ����������� �� <a href='{$eHost}/users/%USER_LOGIN%/setup/mailer/' target='_blank'>�������� ������������/��������</a> ������ ��������.
</p>

�������� ������!<br/>
������� <a href='{$eHost}' target='_blank'>Free-lance.ru</a>";


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


if ( $pMessage != '' ) {
    echo "Send personal messages\n";
    $msgid = $DB->val("SELECT masssend(?, ?, '{}', '')", $sender['uid'], $pMessage);
    if (!$msgid) {
        die("Failed!\n");
    }
    $i = 0;
    while ( $users = $master->col("{$sql} LIMIT 30000 OFFSET ?", $i) ) {
        $DB->query("SELECT masssend_bind(?, {$sender['uid']}, ?a)", $msgid, $users);
        $i = $i + 30000;
        echo "{$i} users\n";
    }
    $DB->query("SELECT masssend_commit(?, ?)", $msgid, $sender['uid']); 
    echo "Send email messages\n";
}


if ( $eMessage != '' ) {
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