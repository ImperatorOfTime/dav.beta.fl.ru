<?php

$smail->subject = "�������������� ������ �� FL.ru";
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order_id);

if ($date_reserve) {
    $date_reserve = date('d.m.Y H:i:s',  strtotime($date_reserve));
}

$date_payout = date('d.m.Y H:i:s');

$price = tservices_helper::cost_format($price);

?>
����� ������: <a href="<?=$order_url?>"><?=$num?></a><br/>
����� � ��� ���������: <?=$emp?><br/>
����� � ��� �����������: <?=$frl?><br/>
Invoice ID: <?=$invoiceId?><br/>
���� � ����� ��������������: <?=$date_reserve?><br/>
���� � ����� ������� �� �������: <?=$date_payout?><br/>
����� �������: <?=$price?>