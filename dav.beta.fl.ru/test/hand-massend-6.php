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
$pro = FALSE;

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
$recipients = 'employers';

/**
 * ������ ��� ��������� ������ �������������, ���� $recipients == ''
 * ������������ �������: uid, login, uname, usurname, email, subscr
 * �����! uid ������ ���� ������ ��������
 */
// ���� �������� - "������� �� ������� Free-lance.ru"
$sw = $ew = str_repeat('0', $subscrsize);
$sw{7} = '1';
// ---
$sql = "SELECT u.* FROM freelancer u WHERE subscr & B'{$sw}' <> B'{$ew}' AND is_banned = B'0'";

/**
 * ����� ��� ������� ��������� (������ ������� reformat)
 * ���� ������ ������, �� � ����� �� ����
 * {{name}} ���������� �� ������� �� $sql (���� $mass == FALSE)
 * ��� $mass == TRUE ����� ������������ ����.����������, ��. http://www.free-lance.ru/siteadmin/admin/
 * �������: ������ ������� � ���� http:/{������}/{$h}/quiz/form/ (�������� ������ ��� �������� �� ������ � ����������)
 */
$h = preg_replace("/http\:\/\//", "", $GLOBALS['host']);

$pMessage = "������������!

����� ���������� ��� � ����������� ���������� �� ������� ������ �����. ����� ������� ������� Free-lance.ru ������� �� 10 FM ��� �������� � http:/{��������� PRO}/{$h}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_pro<span>.</span> ��� ���������� ������ �� ����� �� ������ ���������� �� ������ �������� �� 100 FM!

�� ����� ����������, ���������� ����������� �� ������������ �� �������, ����������� ����������� http:/{�������� PRO}/{$h}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_pro<span>,</span> � ���� ������. 
������� PRO � ��� ������� � ������. http:/{��������� ����}/{$h}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_pro<span>!</span>

�� ���� ����������� �������� �� ������ ���������� � ���� http:/{������ ���������}/{$h}/help/?all<span>.</span>
�� ������ ��������� ����������� ��http:/{�������� &laquo;�����������/��������&raquo;}/{$h}/users/%USER_LOGIN%/setup/mailer/<span> ������</span> ��������.

�������� ������,
������� http:/{Free-lance.ru}/{$h}/
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
$eSubject = "������ �� ������ Free-lance.ru";

/**
 * ����� ��� ����������� �� ����� (������ HTML)
 * ���� ������ ������, �� �� ����� �� ����
 * {{name}} ���������� �� ������� �� $sql (��� ����� $mass)
 */
$eMessage = "<p>������������!</p>

<p>
����� ���������� ��� � ����������� ���������� �� ������� ������ �����. ����� ������� ������� Free-lance.ru ������� �� 10 FM ��� �������� � <a href='{$GLOBALS['host']}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_pro'>��������� PRO</a>. ��� ���������� ������ �� ����� �� ������ ���������� �� ������ �������� �� 100 FM!
</p>

<p>������ ����!
    <table cellpadding='0' cellspacing='0' border='0' width='370'>
        <tbody>
            <tr>

                <td class='pad_null' colspan='4' height='40' valign='top'><font color='#000000' size='2' face='tahoma,sans-serif'><b>��������� ����� �� ��������:</b></font></td>
            </tr>
            <tr>
                <td class='pad_null' height='25' colspan='2'><font color='#000000' size='2' face='tahoma,sans-serif'>��� ���������� ��������</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>��� PRO, FM</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>� PRO, FM</font></td>
            </tr>

            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>����������� ������� �����</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>35</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>25</font></td>
            </tr>
            <tr>

                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>��������� ������</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>20</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>0</font></td>
            </tr>
            <tr>
            <tr>

                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>��������� ������</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>20</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>10</font></td>
            </tr>
            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>

                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>�������� ��������</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>30</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>20</font></td>
            </tr>
            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>�������� �������</font></td>

                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>20</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>10</font></td>
            </tr>
            <tr>
                <td class='pad_null' colspan='4' height='40'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>�������� ��� ���������� ������� ��&nbsp;<font color='#fd6c30' size='3' face='tahoma,sans-serif'><b>60 FM</b></font></font></td>
            </tr>
            <tr>

                <td class='pad_null' colspan='4' height='20'>&#160;</td>
            </tr>
        </tbody>
        </table>
        <table cellpadding='0' cellspacing='0' border='0' width='370'>
        <tbody>
            <tr>
                <td class='pad_null' height='25' colspan='2'><font color='#000000' size='2' face='tahoma,sans-serif'>��� ���������� ���������</font></td>

                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>��� PRO, FM</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>� PRO, FM</font></td>
            </tr>
            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>���������� ��������</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>110</font></td>

                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>100</font></td>
            </tr>
            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>����������� ������� �����</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>45</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>35</font></td>

            </tr>
            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>��������� ������</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>20</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>0</font></td>
            </tr>

            <tr>
            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>��������� ������</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>20</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>10</font></td>
            </tr>

            <tr>
                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>�������� ��������</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>30</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>20</font></td>
            </tr>
            <tr>

                <td class='pad_null' height='25' valign='top'>&#160;&#160;&#160;</td>
                <td class='pad_null' height='25'><font color='#000000' size='2' face='tahoma,sans-serif'>�������� ��������</font></td>
                <td class='pad_null' width='90' align='center'><font color='#000000' size='2' face='tahoma,sans-serif'>35</font></td>
                <td class='pad_null' width='90' align='center'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>25</font></td>
            </tr>
            <tr>
                <td class='pad_null' colspan='4' height='40'><font color='#fd6c30' size='2' face='tahoma,sans-serif'>�������� ��� ���������� �������� ��&nbsp;<font color='#fd6c30' size='3' face='tahoma,sans-serif'><b>70 FM</b></font></font></td>

            </tr>
            <tr>
                <td class='pad_null' colspan='4' height='20'>&#160;</td>
            </tr>
            <tr>
                <td class='pad_null' colspan='4' height='40'><font color='#6DB335' size='2' face='tahoma,sans-serif'>� ��� ��� ���� �������� PRO ����� <font color='#6DB335' size='3' face='tahoma,sans-serif'><b>10 FM</b></font> � �����.</font></td>
            </tr>

        </tbody>
    </table>
</p>
                
<p>
�� ����� ����������, ���������� ����������� �� ������������ �� �������, ����������� ����������� <a href='{$GLOBALS['host']}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_pro'>�������� PRO</a>, � ���� ������.<br/> 
������� PRO � ��� ������� � ������. <a href='{$GLOBALS['host']}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_pro'>��������� ����</a>!
</p>
                             
<p>
�� ���� ����������� �������� �� ������ ���������� � ���� <a href='{$GLOBALS['host']}/help/?all'>������ ���������</a>.<br />
�� ������ ��������� ����������� <a href='{$GLOBALS['host']}/users/{{login}}/setup/mailer/'>���������� &laquo;�����������/��������&raquo;</a>������� ��������.
</p>

<p>
�������� ������,<br/>
������� <a href='{$GLOBALS['host']}/'>Free-lance.ru</a>
</p>
";


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

if ( $mass ) {
    while ( !$plproxy->val("SELECT COUNT(*) FROM messages(?) WHERE id = ?", $sender['uid'], $message_id) ) {
        echo "Wait PGQ (10 seconds)...\n";
        sleep(10);
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
