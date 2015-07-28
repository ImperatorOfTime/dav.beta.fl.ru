<?php

/**
 * ���� ������ �����, ��������� �����������, � � ������� 3� ����� �� ���� �� ���� ������� �������� 
 * (������ ��� �������������), �� ���������� ����������� �������� �����������:
 */


$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);

$order_price = tservices_helper::cost_format($order['order_price'], true, false, false);
$order_days = tservices_helper::days_format($order['order_days']);

$accept_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'accept', $order['frl_id']);
$decline_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'decline', $order['frl_id']);

?>
����������, ��� 3 ��� ����� �������� <?=$emp_fullname?> ��������� ��� ����� �� ������ 
&laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; �� ����� <?php echo $order_price ?> �� ������ ���������� <?=$order_days?>.<br/>
�� ������ ������� � ������ � ���������� ������� ��������������, ������ ���������� ������ ��� ���������� �� ����.
<br/>
<br/> 
<a href="<?=$order_url?>">������� � ������</a> / 
<a href="<?=$accept_url?>">������ ��� ����������</a> / 
<a href="<?=$decline_url?>">���������� �� ����������</a>
<br/>
<br/>