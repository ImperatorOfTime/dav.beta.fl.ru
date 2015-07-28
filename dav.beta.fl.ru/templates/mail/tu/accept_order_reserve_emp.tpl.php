<?php

/*
 * ������ ������ ����������� ��������� � ������������� ������ � �������� ������. (�-5)
 * ��� �������� ������� (\n) ����� �������� <br/> ��� ������ ��������� � ��� �������� ������
 */


$smail->subject = "����������� ���������� ����� �� ������ �{$order['title']}�";

$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_price = tservices_helper::cost_format($order['order_price'], true, false, false);
$order_days = tservices_helper::days_format($order['order_days']);
//@todo: ��� ���� ������ ������ ���� �� �� ��������������� ������!
//$order_end_date = date('d.m.Y', strtotime("+ {$order['order_days']} days",strtotime($order['accept_date'])));

$reserve_price = tservices_helper::cost_format($order['reserve_data']['reserve_price'], true, false, false);
$reserve_tax = $order['reserve_data']['tax']*100;

?>
������������.

����������� <?=$frl_fullname?> ���������� ��� ����� &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; � ������ ��� ����������, ��� ������ �� �������������� �����. 
������ ������ � <?=$order_price ?>. 
����� �������������� � <?=$reserve_price?> (������ + <?=$reserve_tax?>% ��������).

���� ���������� ������ � <?=$order_days ?><?php if(false): ?> (�� <?=$order_end_date?>)<?php endif; ?>.
����������� ��� ��� ��������� � ������������ ����� ��������������� � ������. 

<a href="<?=$order_url?>">������� � ������ � ��������������� �����</a> / <a href="<?=$order_url?>">��������� � ������������</a>

<i>��� �������� � �������:
1. � �������� �������������� ������������ �������������� ���������� �����, ���������� � ������������;
2. �� ��������� ����� �������� �� ����������� �������� ���������, ��������� ��� � ����� ��������� �����, ���������� ������� �����;
3. ���������� ��������.</i>

-----
� ���������,
������� <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>