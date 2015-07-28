<?php
ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp.php';

// ----------------------------------------------------------------------------------------------------------------
// -- ���� �������� -----------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------

/**
 * ���� TRUE - �������� �������� (to_id = 0), ���� FALSE - �������� (������� ������������ ���� ���������)
 * 
 */
$mass = TRUE;

/**
 * �������� ������ ��� pro ( ������ ��� $mass = TRUE )
 * TRUE - Pro, FALSE - �� Pro, NULL - ����
 * 
 */
$pro = NULL;

/**
 * ����� ������������ �� ���� �������������� ��������
 * 
 */
$sender = 'admin';

/**
 * ���� ���������
 * all - ����, employers - �������������, freelancers - ����������� ( <- ������ ��� $mass = TRUE )
 * ������ ������ - ���� ������ ( <- ��� ����� $mass )
 * �����! ��� $mass == FALSE ������ ������ ���� ������ ������
 */
$recipients = 'freelancers';

/**
 * ������ ��� ��������� ������ �������������, ���� $recipients == ''
 * ������������ �������: uid, login, uname, usurname, email, subscr
 * �����! uid ������ ���� ������ ��������
 */
$sql = "SELECT uid, login, uname, usurname, email, subscr FROM users WHERE login = 'jb_admin'";

/**
 * ����� ��� ������� ��������� (������ HTML)
 * ���� ������ ������, �� � ����� �� ����
 * {{name}} ���������� �� ������� �� $sql (���� $mass == FALSE)
 * ��� $mass == TRUE ����� ������������ ����.����������, ��. http://www.free-lance.ru/siteadmin/admin/
 */
$pMessage = "<p>������, ������!</p>

<p>� ��� ���� ����� ������� ������� ��� ���.</p>

<p>�� ������ ���������� � ������� ���������� ������ �� ������ ������ hh.ru. ������ ������������ �� hh.ru � ������ ������ ����� ���� ����������� � ������������ � ���������� � ��������� ��������� �������.</p>

<p>����� ����, ������ �� Free-lance.ru ������ ��������� �� ������� �������� hh.ru.</p>

<p>����� �������� �������� hh.ru � �������� ���������� � ������������������ ������, ��� ����������:</p>

<p>1. �������� ��������� <a href='{$GLOBALS['host']}/help/?q=850?&utm_source=newsletter4&utm_medium=rassillka&utm_campaign=integration_hh.ru'>��������� � �������</a></p>

<p>2. ����������� �������� <a href='{$GLOBALS['host']}/help/?q=948?&utm_source=newsletter4&utm_medium=rassillka&utm_campaign=integration_hh.ru'>�������� ����� (����)</a>, 
�� ������� ������������ hh.ru ������ �������� ��� (��������, &laquo;��������&raquo;, &laquo;PHP-�����������&raquo; � �.�.) 
������ ��� ������� �������� ��� ����, � �� ������ ��� ����������� � 
<a href='{$GLOBALS['host']}/payed/?utm_source=newsletter4&utm_medium=rassillka&utm_campaign=integration_hh.ru'>��������� PRO</a>.</p>

<p><a href='{$GLOBALS['host']}/?utm_source=newsletter4&utm_medium=rassillka&utm_campaign=integration_hh.ru'>������� �� ����</a></p>

<p>���� � ��� �������� �������, ���������� <a href='{$GLOBALS['host']}/help/?all'>� ������ ���������</a> Free-lance.ru</p>

<p>�� ������ ��������� ����������� �� �������� ������������/�������� ������ ��������.</p>

<p>������� Free-lance.ru ���������� ��� �� ������� � ����� ������ �������.</p>

<p>
������� ������!<br/>
������� Free-lance.ru
</p>
";

/**
 * ������ � id ������������� ������ �� ������� file ��� �����. ������ ���� ��� ������ �� webdav.
 * NULL - ��� ������
 */
$pFiles = NULL;

/**
 * ��������� ��� ����������� �� �����
 * ���� ������ ������, �� �� ����� �� ����
 * {{name}} ���������� �� ������� �� $sql (��� ����� $mass)
 */
$eSubject = "���������� � hh.ru � ������ ������ � ������� ��������!";

/**
 * ����� ��� ����������� �� ����� (������ HTML)
 * ���� ������ ������, �� �� ����� �� ����
 * {{name}} ���������� �� ������� �� $sql (��� ����� $mass)
 */
$eMessage = $pMessage;

/**
 * ���� �������� (����� �����) ������� subscr �� ������� users, ������� ������� ��������� ��� �������� �����
 * ���� NULL - ����� ����
 * ��� "������� �� ������� Free-lance.ru" ���� == 7
 * 
 */
$eSubscr = 7;

/**
 * ������ � id ������������� ������ �� ������� file ��� �����. ������ ���� ��� ������ �� webdav.
 * NULL - ��� ������
 */
$eFiles = NULL;

/**
 * ����� ����� ���������� ���������� ��������� �������� ���������� � ���
 * (��� �������� �������� � email ��������)
 * 
 */
$printStatus = 200;


// ----------------------------------------------------------------------------------------------------------------
// -- �������� ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------

$master  = new DB('master');
$plproxy = new DB('plproxy');
$count   = NULL;

$sender = $master->row("SELECT * FROM users WHERE login = ?", $sender);
if ( empty($sender) ) {
    die("Unknown Sender\n");
}

echo "Send personal messages\n";

if ( $mass ) {
    
    $count = 0;
    
    switch ( $recipients ) {
        case 'all': {
            $message_id = $plproxy->val("SELECT messages_masssend_all(?, ?, ?, ?a)", $sender['uid'], $pro, $pMessage, $pFiles);
            break;
        }
        case 'freelancers': {
            $message_id = $plproxy->val("SELECT messages_masssend_freelancers(?, ?, ?, ?a)", $sender['uid'], $pro, $pMessage, $pFiles);
            break;
        }
        case 'employers': {
            $message_id = $plproxy->val("SELECT messages_masssend_employers(?, ?, ?, ?a)", $sender['uid'], $pro, $pMessage, $pFiles);
            break;
        }
        case '': {
            $users = $master->col($sql);
            if ( empty($users) ) {
                die("No users\n");
            }
            $count = count($users);
            $message_id = $plproxy->val("SELECT messages_masssend(?, ?a, ?, ?a)", $sender['uid'], $users, $pMessage, $pFiles);
            unset($users);
            break;
        }
        default: {
            die("Unknown mode\n");
        }
    }
        
} else {
    
    $count = 0;
    
    $res = $master->query($sql);
    while ( $user = pg_fetch_assoc($res) ) {
        
        $msg = preg_replace("/\{\{([-_A-Za-z0-9]+)\}\}/e", "\$user['\\1']", $pMessage);
        $plproxy->query("SELECT messages_add(?, ?, ?, ?, ?a)", $sender['uid'], $user['uid'], $msg, TRUE, $pFiles);
        
        if ( ($count > 0) && ($count % $printStatus == 0) ) {
            echo "Working... {$count} emails sended\n";
        }
        
        $count++;
        
    }
    
}

$memBuff = new memBuff();
$memBuff->set("msgsCnt_updated", time());

if ( is_null($count) ) {
    die("Settings error\n");
} else if ( $count ) {
    echo "OK. Total: {$count} users\n";
} else {
    echo "OK.\n";
}



if ( $mass ) {
    while ( !$plproxy->val("SELECT COUNT(*) FROM messages(?) WHERE id = ?", $sender['uid'], $message_id) ) {
        echo "Wait PGQ (10 seconds)...\n";
        sleep(10);
    }
    $res = $plproxy->query("SELECT * FROM messages_zeros_userdata(?, ?)", $sender['uid'], $message_id);
} else {
    $res = $master->query($sql);
}

echo "Send email messages\n";

$count = 0;
$smtp  = new SMTP;
if ( !$smtp->Connect() ) {
    die("Don't connect to SMTP\n");
}
    
while ( $user = pg_fetch_assoc($res) ) {
        
    if ( empty($user['email']) || (!is_null($eSubscr) && substr($user['subscr'], $eSubscr, 1) == '0') ) {
        continue;
    }
        
    $smtp->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
    $smtp->subject   = preg_replace("/\{\{([-_A-Za-z0-9]+)\}\}/e", "\$user['\\1']", $eSubject);
    $smtp->message   = preg_replace("/\{\{([-_A-Za-z0-9]+)\}\}/e", "\$user['\\1']", $eMessage);
    
    if ( ($count > 0) && ($count % $printStatus == 0) ) {
        echo "Working... {$count} emails sended\n";
    }
    
    $smtp->SmtpMail('text/html');
    
    $count++;
        
}

echo "OK. Total: {$count} users\n";
