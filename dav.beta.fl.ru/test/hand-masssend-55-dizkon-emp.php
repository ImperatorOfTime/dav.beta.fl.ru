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

// ������ �����������, �������������� � ����������������, ������������ (is_banned = B'0'), � ����������� ����������

$sql = "SELECT uid, email, login, uname, usurname, subscr FROM employer WHERE is_banned = B'0'
        "; //employer

//$sql = "SELECT uid, email, login, uname, usurname FROM users WHERE login = 'land_e2'"; 

$pHost = str_replace("http://", "", $GLOBALS['host']);
if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // ������� � ������ ���� ����������� ��������� HTTPS �� �������� (��� ����� � ��)
} else {
    $pHttp = 'http';
}
$eHost = $GLOBALS['host'];


$eSubject = "��� 3000 ���������� � ����� 100 ���������!";

$eMessage = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<title></title>
</head>
<body bgcolor="#ffffff" marginwidth="0" marginheight="0" link="#e74c3c" bottommargin="0" topmargin="0" rightmargin="0" leftmargin="0" style="margin:0">

<table bgcolor="#ffffff" width="100%">
<tbody><tr>
<td bgcolor="#ffffff">
<center>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
    <tbody><tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td height="20" width="20"></td>
        <td height="20" width="20"></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
    <tbody>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td align="right" valign="middle"><font color="#9a9a9a" size="3" face="arial">�� � ���������� �����</font> &#160; </td>
        <td valign="middle" width="120">
           <a href="https://www.facebook.com/dizkon.ru" target="_blank"><img src="http://gallery.mailchimp.com/3723ed50f1f494db19fd7ca04/images/f.png" width="30" height="30" border="0"></a> &#160;
           <a href="http://vk.com/dizkon" target="_blank"><img src="http://gallery.mailchimp.com/3723ed50f1f494db19fd7ca04/images/v.png" width="31" height="30"></a> &#160;
           <a href="https://twitter.com/dizkon_ru" target="_blank"><img src="http://gallery.mailchimp.com/3723ed50f1f494db19fd7ca04/images/t.png" width="30" height="30"></a></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="10" colspan="4"></td>
    </tr>
</tbody>
</table>


<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
    <tbody><tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td align="right" valign="middle"><a href="http://www.dizkon.ru/?utm_source=fl_emp_19_02&utm_medium=email&utm_campaign=clients" target="_blank"><img src="https://gallery.mailchimp.com/3723ed50f1f494db19fd7ca04/images/dizkon_header_mail.png" width="600" height="70" border="0"></a></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>

<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
    <tbody>
    <tr>
        <td bgcolor="#ffffff" width="20" height="20" colspan="3"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td><b><font color="#000000" size="4" face="arial">������������!</font></b></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="20" colspan="3"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td><font color="#000000" size="3" face="arial">���� ��������, ��� ����� �� ��� ������ �� ������� ����� 3000 ���������� � ����� 100 �������� � ���� �������� ���������. ��� ���� ��������� ������ ����� ����� ����������:</font></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="10" colspan="3"></td>
    </tr>
</tbody>
</table>


<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
    <tbody>
    <tr>
        <td bgcolor="#ffffff" width="20" height="10" colspan="5"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td valign="top"><a href="http://www.dizkon.ru/contests/2132/winner?utm_source=fl_emp_19_02&utm_medium=email&utm_campaign=clients"><img src="http://cdn.joxi.ru/uploads/prod/2014/02/13/c5f/5c8/a8713d98f717cd5e7ac62b7c9af7ca7cd9d29251.jpg" width="265" height="150" border="0" align="top"></a>
        <br><br>
              <font color="#000000" size="3" face="arial">������� ��� �������� "Retail Master"<br>
              <b>������ �������� <font color="#e54f3b" size="3" face="arial">9000 ������</font></b></font>
        </td>
        <td bgcolor="#ffffff" width="20"></td>
        <td valign="top"><a href="http://www.dizkon.ru/contests/1161/work?utm_source=fl_emp_19_02&utm_medium=email&utm_campaign=clients#work-1892"><img src="http://cdn.joxi.ru/uploads/prod/2014/02/13/008/3e1/17538364c5c7eae21731e78de0f48455c9e77ded.jpg" width="265" height="150" border="0" align="top"></a>
        <br><br>
              <font color="#000000" size="3" face="arial">������ ������� �������� ����� web-������ "Will Day"<br>
              <b>������ �������� <font color="#e54f3b" size="3" face="arial">13500 ������</font></b></font>
        </td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="40" colspan="5"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td valign="top"><a href="http://www.dizkon.ru/contests/2135/winner?utm_source=fl_emp_19_02&utm_medium=email&utm_campaign=clients"><img src="http://cdn.joxi.ru/uploads/prod/2014/02/13/053/5e3/b89d2437c6e927f64862d1df706d1edcd1d0e0d8.jpg" width="265" height="149" border="0" align="top"></a>
        <br><br>
              <font color="#000000" size="3" face="arial">������� ���������� <br>
              <b>������ �������� <font color="#e54f3b" size="3" face="arial">6300 ������</font></b></font>
        </td>
        <td bgcolor="#ffffff" width="20"></td>
        <td valign="top"><a href="http://www.dizkon.ru/contests/1381/winner?utm_source=fl_emp_19_02&utm_medium=email&utm_campaign=clients"><img src="http://cdn.joxi.ru/uploads/prod/2014/02/13/33e/bdd/4ecea80c8be82c3a2a794ae05b4aed180a9f7298.jpg" width="265" height="149" border="0" align="top"></a>
        <br><br>
              <font color="#000000" size="3" face="arial">������� ��� �������� ������������ "��������"<br>
              <b>������ �������� <font color="#e54f3b" size="3" face="arial">6300 ������</font></b></font>
        </td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="40" colspan="5"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" align="center" colspan="5"><a href="http://www.dizkon.ru/contests/create/?utm_source=fl_emp_19_02&utm_medium=email&utm_campaign=clients" target="_blank"><img src="http://gallery.mailchimp.com/3723ed50f1f494db19fd7ca04/images/btn.png?utm_source=fl_emp_19_02&utm_medium=email&utm_campaign=clients" width="386" height="50" border="0"></a></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="40" colspan="5"></td>
    </tr>
</tbody>
</table>



<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
    <tbody>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td align="center"><b><font color="#000000" size="4" face="arial">������ DizKon �������?</font></b></td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="40" colspan="3"></td>
    </tr>
</tbody>
</table>



<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
    <tbody>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td valign="top" width="80"><img src="http://gallery.mailchimp.com/3723ed50f1f494db19fd7ca04/images/11.png" width="55" height="74" align="top"></td>
        <td valign="top">
              <b><font color="#000000" size="3" face="arial">��������</font></b><br><br>
              <font color="#000000" size="3" face="arial">������ ����� ������ ��������� ���������� ���������, ������� ��������� ����������� ����� �������.</font>
        </td>
        <td bgcolor="#ffffff" width="20"></td>
        <td valign="top" width="80"><img src="http://gallery.mailchimp.com/3723ed50f1f494db19fd7ca04/images/12.png" width="60" height="48" align="top"></td>
        <td valign="top">
              <b><font color="#000000" size="3" face="arial">����������� ����������</font></b><br><br>
              <font color="#000000" size="3" face="arial">��� ���������� �������� ����������� ������� �� ���������� ��������������� ����� �� ������������� ������ ���������.</font>
        </td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="40" colspan="6"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td valign="top" width="80"><img src="http://gallery.mailchimp.com/3723ed50f1f494db19fd7ca04/images/13.png" width="59" height="49" align="top"></td>
        <td valign="top">
              <b><font color="#000000" size="3" face="arial">����������� ������</font></b><br><br>
              <font color="#000000" size="3" face="arial">���������� ���� � �������� ����������� ��������� ��� ����� �����������.</font>
        </td>
        <td bgcolor="#ffffff" width="20"></td>
        <td valign="top" width="80"><img src="http://gallery.mailchimp.com/3723ed50f1f494db19fd7ca04/images/14.png" width="55" height="55" align="top"></td>
        <td valign="top">
              <b><font color="#000000" size="3" face="arial">�������������</font></b><br><br>
              <font color="#000000" size="3" face="arial">����������� ���� ��� ���������� �������� &mdash; 4 ���.</font>
        </td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="40" colspan="6"></td>
    </tr>
</tbody>
</table>

<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
    <tbody>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td valign="top" width="80"><img src="http://gallery.mailchimp.com/3723ed50f1f494db19fd7ca04/images/15.png" width="58" height="52" align="top"></td>
        <td valign="top">
              <b><font color="#000000" size="3" face="arial">���������� �����������</font></b><br><br>
              <font color="#000000" size="3" face="arial">������� � DizKon.ru ������������� ����������� � ��� ����� �������� <a href="https://www.fl.ru/?utm_source=fl_emp_19_02&utm_medium=email&utm_campaign=clients" target="_blank" style="color:#e74c3c">FL.ru</a>, ��� ��� ������ ��� ������ ������������.</font>
        </td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="50" colspan="4"></td>
    </tr>
</tbody>
</table>






</center>
</td>
</tr>
</tbody></table>

            </body>
</html>
';

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