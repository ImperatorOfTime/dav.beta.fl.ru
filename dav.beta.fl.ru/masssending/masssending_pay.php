<?
$g_page_id = "0|56";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/masssending.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupMasssending.php');

session_start();

$id = __paramInit('int', 'id');

//������ ���� id ��������
if(!$id)
{
    include ABS_PATH . '/404.php'; 
    exit;
}

$uid = get_uid(false);
if(!$uid)
{
    include ABS_PATH . '/404.php'; 
    exit;
}

$masssending = new masssending();
$params = $masssending->getAccepted($id, $uid);

if (!$params) {
    //������ ������������ ���������� ������������ ��������, ������������� �������� ������������.
    include ABS_PATH . '/404.php'; 
    exit;
}

$text = reformat($params['msgtext'], 1000, false, true);

$calc = $masssending->Calculate(get_uid(false), array($params));
//print_r($calc);exit;

quickPaymentPopupMasssending::getInstance()->init(array(
    'count' => $params['all_count'],
    'count_pro' => $params['pro_count'],
    'price' => $params['pre_sum'],
    'send_id' => $id
));

$stretch_page = true;
$header  = "../header.php";
$footer  = "../footer.html";
$content = "tpl.masssending_pay.php";

include ("../template2.php");
?>