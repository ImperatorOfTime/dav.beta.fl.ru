<?
require_once("../classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/firstpage.php");



$mail = new smail();

// 1
$f_user_admin = users::GetUid($err,"admin");

$user['uname'] = "����";
$user['usurname'] = "������";
$user['login'] = "vp";
$user['email'] = "vishna-v-sahare@mail.ru";
$prof['name'] = "nnnn";
$prof['id'] = 10;
$prof['cost'] = 15;
$days = 2;

$mail->subject = "������������ ������� ��� ��������������� ��������� �� Free-lance.ru";  
$mail->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>"; 
	        
	        $html = "";
	           $prof_name  = $prof['name'];
    	       if($prof['id'] == 0)  $prof_name  = "��� ����������";
    	       
	           $html .= "-&nbsp;<a href=\"{$GLOBALS['host']}/firstpage/?prof={$prof['id']}\">{$prof_name}</a> ({$prof['cost']} FM)<br/>";

	        
	        $dev  = 111;
                $date_dest = strtotime('+'.$days.' days');
                $date = date('d '.monthtostr(date('m', $date_dest)).' Y ����', $date_dest);
	        $body = "�� ��������� ������� ������������� ".ending($days, "�������", "��������", "��������")." ".number2string($days, 1)." ".ending($days, "����", "���", "����").". ����� $days ".ending($days, "����", "���", "����").", {$date}, ������ ���� ������������� �������� ���������� � ��������� �������� ����� Free-lance.ru:<br/>
{$html}
����� � ������ ����� ������ ���� ������� {$val['sum_cost']} FM.<br/>
������ �� ����� ������ ����� {$val['sum']} FM. ��� ������������ ��������������� ��������� ������������ �������.<br/><br/>
���������� ���, ��� �������������� ��������� ���������� � ������, ����� �� ����� ������ ����� ���������� ������� ��� ������ ��������� ���� ��������� ��������.<br/> 
����������, ��������� ���� ��� �������� ��������� ��������������� ���������.<br/>
<br/>
���� ����� ��������� �� ��������� ��������: <a href=\"{$GLOBALS['host']}/bill/\">{$GLOBALS['host']}/bill/</a><br/>
������� ������������� ����� ��������� ��� ��������� �����: <a href=\"{$GLOBALS['host']}/firstpage/\">{$GLOBALS['host']}/firstpage/</a>";
	        
	        $mail->message = $mail->GetHtml($user['uname'], $body, 'simple');
echo $mail->message;
	        $mail->SmtpMail('text/html');



?>
