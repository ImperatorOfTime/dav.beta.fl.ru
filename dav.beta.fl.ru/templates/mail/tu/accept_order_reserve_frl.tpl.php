<?php

/*
 * ������ ������ ����������� ����������� � �������������� ������ � �������� ������. (�-6)
 * ��� �������� ������� (\n) ����� �������� <br/> ��� ������ ��������� � ��� �������� ������
 */

$smail->subject = "������������� ������ �� ������ �{$order['title']}�";

$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_days = tservices_helper::days_format($order['order_days']);
//@todo: ��� ���� ������ ������ ���� �� �� ��������������� ������!
//$order_end_date = date('d.m.Y', strtotime("+ {$order['order_days']} days",strtotime($order['accept_date'])));
//$tax_price = tservices_helper::cost_format($order['tax_price'], true, false, false);
$order_price = tservices_helper::cost_format($order['order_price'], true, false, false);
$tax = $order['tax']*100;

?>
������������.

������ ��� �� ����������� ����� &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; (������������ ���������� <?=$emp_fullname?>). 
������ ������ � <?=$order_price ?>. 
���� ���������� ������ � <?=$order_days?><?php if(false): ?> (�� <?=$order_end_date ?>)<?php endif; ?>.

������ ����� �������������� �� ����� FL.ru � �������� ��� ����� ���������� ���� ����� �� ������. ����������, �� ��������� ���������� ������, ���� �������� �� ������������� �����.

<a href="<?=$order_url?>">������� � ������</a> / <a href="<?=$order_url?>">��������� � ����������</a>

<i>
��� �������� � �������:
1. ��������� �������������� ����� ���������� � ������ ���������� ������;
2. �� ��������� ����� ������������ ��������� ���������;
3. ��� ������ �������� ���������� �������� ������, �� �������� ����������������� ����� � ������� ���������� ��������.
</i>

<?php if($tax > 0): ?>
�������� ��������, ��� ��� ���������� ������ � ������ ������� ����� �� ����� ����� ������� �������� � ������� <?=$tax ?>% �� ����������� ��� ����� �� �����.
<?php endif; ?>

� ���������, 
������� <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>