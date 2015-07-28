<?php
/**
 * ����������� �������������
 * */
ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp2.php';


/**
 * ����� ������������ �� ���� �������������� ��������
 * 
 */
$sender = 'admin';

// ������ �� pro �����������, �������������� (active = true), ������������ (is_banned = B'0'), � ����������� ����������

$sql = "SELECT uid, email, login, uname, usurname, subscr FROM freelancer 
        WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0' AND is_pro = false"; //freelancer

if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // ������� � ������ ���� ����������� ��������� HTTPS �� �������� (��� ����� � ��)
} else {
    $pHttp = 'http';
}
$pHost = str_replace("$pHttp://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];

$pMessage = "������������!

� 5 ������� ������������ � ����������� � ���������������� ��������� ����� �������� ������ ���������� (e-mail, ICQ, Skype � �.�.), ������� ������ ���������� ����������� ��� ���� ������� PRO.

��� �������� PRO ���� ���������� ���������� �� ����� �������������, �� ������� ������� � �������� ����������� � �� ������ �������� �� ������� � �������� ������� ��� PRO�. ��� ������, ��� �� ������� ������������� ���������� � ������.

��������� � ������������ �������� PRO: ���� ������ ����� � �������� �������, � ������������ ������ ������ ��������� � ����, ���� ���� �� ����� �� �������� �� ����. �� ���������� ���������� � ������������������ �������!

{$pHttp}:/{��������� ��� ���� ������������� �������� PRO}/{$pHost}/payed/?utm_source=newsletter4&utm_medium=email&utm_campaign=you_should_PRO

�� ���� ����������� �������� �� ������ ���������� � ���� {$pHttp}:/{������ ���������}/{$pHost}/help/?all<i>.</i>
�� ������ ��������� ����������� ��{$pHttp}:/{�������� &laquo;�����������/��������&raquo;}/{$pHost}/users/%USER_LOGIN%/setup/mailer/<span> ������</span> ��������.

�������� ������,
������� {$pHttp}:/{Free-lance.ru}/{$pHost}/
";

$eSubject = "Free-lance.ru: �� �������� ���� �������";

$mail = new smtp2;

$cid1  = $mail->cid();
$cid2  = $mail->cid();
$cid3  = $mail->cid();

$mail->attach(ABS_PATH . '/images/letter/12.png', $cid1);
$mail->attach(ABS_PATH . '/images/letter/13.png', $cid2);
$mail->attach(ABS_PATH . '/images/letter/15.png', $cid3);

$eMessage = '
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title></title>
</head>
<body bgcolor="#ffffff" marginwidth="0" marginheight="0" link="#396ea9"  bottommargin="0" topmargin="0" rightmargin="0" leftmargin="0" style="margin:0">

<table bgcolor="#ffffff" width="100%">
<tbody><tr>
<td bgcolor="#ffffff">
<center>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700">
    <tbody><tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  height="20" width="20"></td>
        <td  height="20" width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700">
    <tbody><tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  width="20"></td>
        <td  align="left" ><font color="#000000" size="6" face="tahoma,sans-serif">������������!</font></td>
        <td ></td>
        <td width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700">
    <tbody>
    <tr>
        <td  bgcolor="#ffffff" width="20" height="10"></td>
        <td  width="20"></td>
        <td colspan="2" ></td>
        <td  width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  width="20"></td>
        <td width="602" align="left" ><font color="#444444" size="3" face="tahoma,sans-serif">� 5 ������� ������������ � ����������� � ���������������� ��������� ����� �������� ������ ���������� (e-mail, icq, skype � �.�.), ������� ������ ���������� ����������� ��� ���� ������� PRO. </font></td>
        <td width="18" rowspan="3" valign="top"  align="left"><a href="'.$eHost.'/payed/?utm_source=newsletter4&utm_medium=email&utm_campaign=you_should_PRO" target="_blank"><img src="cid:'.$cid1.'" width="104" height="45" alt="PRO" title="PRO" border="0"></a></td>
        <td  width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20" height="10"></td>
        <td  width="20"></td>
        <td ></td>
        <td  width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  width="20"></td>
        <td  align="left"><font color="#444444" size="3" face="tahoma,sans-serif">��� �������� PRO ���� ���������� ���������� �� ����� �������������, �� ������� ������� � �������� ����������� � �� ������ �������� �� ������� � �������� &laquo;������ ��� PRO&raquo;. ��� ������, ��� �� ������� ������������� ���������� � ������.</font></td>
        <td  width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20" height="30"></td>
        <td  width="20"></td>
        <td colspan="2" ></td>
        <td  width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
</tbody>
</table>

<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700">
    <tbody>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  width="20"></td>
        <td  align="left" ><img src="cid:'.$cid2.'" width="631" height="182" border="0"></td>
        <td ></td>
        <td width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20" height="30"></td>
        <td  width="20"></td>
        <td  ></td>
        <td ></td>
        <td width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700">
    <tbody>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  width="20"></td>
        <td  align="left" ><font color="#444444" size="3" face="tahoma,sans-serif">��������� � ������������ �������� PRO: ���� ������ ����� � �������� �������, � ������������ ������ ������ ��������� � ����, ���� ���� �� ����� �� �������� �� ����. �� ���������� ���������� � ������������������ �������!</font></td>
        <td ></td>
        <td width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20" height="30"></td>
        <td  width="20"></td>
        <td  ></td>
        <td ></td>
        <td width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  width="20"></td>
        <td align="left"><a href="'.$eHost.'/payed/?utm_source=newsletter4&utm_medium=email&utm_campaign=you_should_PRO" target="_blank"><img src="cid:'.$cid3.'" width="177" height="36" border="0" alt="������ ������� PRO" title="������ ������� PRO"></a></td>
        <td ></td>
        <td width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20" height="30"></td>
        <td  width="20"></td>
        <td  ></td>
        <td ></td>
        <td width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>

<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff;" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700">
    <tbody>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
        <td bgcolor="#ffffff">
            <font color="#4d4d4d" size="1" face="tahoma,sans-serif">�� ���� ����������� �������� �� ������ ���������� � ���� <a target="_blank" style="color:#0f71c8;" href="'.$eHost.'/help/?all">������ ���������</a>.<br>
�� ������ ��������� ����������� �� �������� �<a target="_blank" style="color:#0f71c8;" href="'.$eHost.'/users/%%%USER_LOGIN%%%/setup/mailer/">�����������/��������</a>� ������ ��������.</font>
        </td>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" height="20" width="20"></td>
        <td  bgcolor="#ffffff"></td>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff">
            <font color="#4d4d4d" size="1" face="tahoma,sans-serif">�������� ������!<br>������� <a target="_blank" style="color:#0f71c8;" href="'.$eHost.'">Free-lance.ru</a></font>
        </td>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" height="20" width="20"></td>
        <td  bgcolor="#ffffff"></td>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  bgcolor="#ffffff" width="20"></td>
  </tr>
</tbody>
</table>

</center>
</td>
</tr>
</tbody></table>

</body>
</html>';

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

$mail->subject = $eSubject;  // ��������� ������
$mail->message = $eMessage; // ����� ������
$mail->recipient = ''; // �������� '����������' ��������� ������
$spamid = $mail->masssend();
if (!$spamid) die('Failed!');
// � ����� ������� �������� �������, �� ��� ������ �� ����������!
// �������� ��� ����� �������� ������ ����������� � ������-���� �������
$i = 0;
$mail->recipient = array();
$res = $master->query($sql);
while ($row = pg_fetch_assoc($res)) {
    $mail->recipient[] = array(
        'email' => "{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>",
        'extra' => array('USER_LOGIN' => $row['login'])
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
