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
$eHost = $GLOBALS['host'];

$eSubject = "�����������: ������ ��� �������";
$eMessage = "<p>������������!</p>
<p>�� ������ ������ ����������� �� Free-lance.ru �������� ����� ������, ��� ��� ���������������� � ���-�������� (� ������� %PHONE%). ������ ������� ��� �� ����� ��������� �������������� ��������.
</p><p>������� �� <a href='{$eHost}/promo/verification/?utm_source=newsletter4&utm_medium=email&utm_campaign=pscb_verif' target='_blank'>�����-�������� �����������</a> � ������� ������ ������ ���-�������. ����� ��������� ����� �� �������� ������ ��������������. </p>
<p>����������, ��� ����������� � ��� ������� �������, ����������� ������ � ����������� ��� �����������, ������� � ��� �� ������� ������ �������������. ������������ � <a href=\"http://feedback.free-lance.ru/article/details/id/1713\" target=\"_blank\">����������� �� �����������</a> ����� ���-������� ����. </p>
<br/>
<p>������� Free-lance.ru</p>
";

//������������ �� csv �����
$csv_users = array("jusoft@yandex.ru" => '+71111112222', "milkusik@gmail.com" => '+71111112223', "mywwork@gmail.com" => '+71111112224'//�����e - ������ ��� ���� !!
,"nvs@pscb.ru" => '79219188356',"kurbatova2@gmail.com" => '79046330950',"ka@pscb.ru" => '79095800040',"leaderdv@mail.ru" => '79052001616',"office@gekko.by" => '375445725555',"nvs@pscb.ru" => '79219188356',"comedie@rambler.ru" => '79034731235',"nikita.terehov@gekko.by" => '375291621010',"grigor007@mail.ru" => '79297166063',"rya-ira@ya.ru" => '79199987714',"martimar@mail.ru" => '79032407467',"ka@pscb.ru" => '70111111111',"den.fitshopspb@gmail.com" => '79062475207',"komp-w@yandex.ru" => '79818930206',"ksiowork@gmail.com" => '79627035793',"n1003@yandex.ru" => '79199880940',"ramina1987@mail.ru" => '79516607065',"info@okospace.com" => '79260150011',"iappstee@gmail.com" => '79189179629',"krylya77@gmail.com" => '79507235075',"creationis@yandex.ru" => '79096440985',"shipiloff@gmail.com" => '79268008183',"lifestyle.91@mail.ru" => '79051365324',"wokkamsk@yandex.ru" => '79192815301',"igeltsov@gmail.com" => '79292192015',"sunway.supply@gmail.com" => '79241121283',"ivgrun@gmail.com" => '79525946082',"mborovkov@gmail.com" => '79200299987',"pevnevv@mail.com" => '79119784081'
);
// ----------------------------------------------------------------------------------------------------------------
// -- �������� ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------

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
foreach ($csv_users as $email=>$phone) {
    if ( is_email($email) ) {
        $mail->recipient[] = array(
            'email' => $email,
            'extra' => array('PHONE' => $phone)
        );
        $mail->bind($spamid);
        $mail->recipient = array();
        $cnt++;
    }
}

echo "OK. Total: {$cnt} users\n";
