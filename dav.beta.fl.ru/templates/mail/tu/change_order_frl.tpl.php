<?php
/**
 * ������ ������ ����������� ����������� �� ��������� ���� � ����� ������
 */

/**
 * ���� ������
 */
$smail->subject = "������ � ����� ������ �{$order['title']}� ��������";

$order_price = tservices_helper::cost_format($order['order_price'], true, false, false);
$order_days = tservices_helper::days_format($order['order_days']);

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);

?>
������������.<br /><br />
�������� <?=$emp_fullname?> �������������� ������ � ����� �� ������ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;<br /><br />
������ � <?=$order_price?><br />
���� � <?=$order_days?>.<br /><br />
<a href="<?=$order_url?>">������� � ������</a><br /><br />

� ���������, 
������� <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>