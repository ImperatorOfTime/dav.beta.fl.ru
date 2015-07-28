<?php

/*
 * ������ ������ ����������� ����������� � ������ ����� � �������� �����, ��� �������������. (��-4)
 */

$smail->subject = "������������� ������ �� ������ �{$order['title']}�";

$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_days = tservices_helper::days_format($order['order_days']);
$order_end_date = date('d.m.Y', strtotime("+ {$order['order_days']} days",strtotime($order['accept_date'])));
$tax_price = tservices_helper::cost_format($order['tax_price'], true, false, false);
$order_price = tservices_helper::cost_format($order['order_price'], true, false, false);
$tax = $order['tax']*100;

?>
������������.
<br/>
<br/>
������ ��� �� ����������� ����� &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; (������������ ���������� <?=$emp_fullname?>) � ������ ��� ����������. 
����� ������ � <?=$order_price ?>. 
���� ���������� ������ � <?=$order_days?> (�� <?=$order_end_date ?>).
<br/>
<?php if($tax > 0): ?>
�� �������������� ������� �� ����� FL.ru � ������ ������� ����� ���� �������� �������� � ������� <?=$tax_price ?> (<?=$tax ?>% �� ����� ������).
<?php endif; ?>
������ �������� ��������������!
<br/>
<br/>
<a href="<?=$order_url?>">������� � ������</a> / 
<a href="<?=$order_url?>">��������� � ����������</a>
<br/>
<br/>
<i>
��� �������� � �������:<br/>
1. �������� �� ��������� ���������� (���� ����������) � ������ ���������� ������;<br/>
2. �� ��������� ����� ������������ ��������� ��������� � �������� �� ���� ������;<br/>
3. ��������� ����� � ���������� ��������.
</i>
<br/>
<br/>
����������, ��� � �������� �������������� �� �������������� ��������������� � ���������� � ������ � ������� ������. 
� �������������� ������ ��� �����, ��������� � ������� ������ � ����������� �� �������� �� ����������.
<br/>
<br/>
� ���������, 
<br/>
������� <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>

