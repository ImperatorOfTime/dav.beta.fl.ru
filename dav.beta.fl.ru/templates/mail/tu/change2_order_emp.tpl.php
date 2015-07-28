<?php
/**
 * ������ ������ ����������� ��������� �� ��������� ������ (�-4)
 * ��� �������� ������� (����� ������) ����� �������� <br/>
 */

/**
 * ���� ������
 */
$smail->subject = "��� ����� �{$order['title']}� ������� �������";

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);

$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$cancel_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'cancel', $order['emp_id']);

$is_new_reserve = tservices_helper::isOrderReserve($order['pay_type']);

?>
�� �������� ��������� ������ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;:

<?php include 'change2_order.tpl.php'; ?>

<?php if($is_new_reserve): ?>
����������� ������� ����������� �� ��������� ���������� ������. ��� ������ �� ��������� � ���� ������� �������������� � ���������� �����, �� ������� ��������������� ������ ������. ����� ����� �������� ���������� ������. ��������, ����������.
<?php else: ?>
����������� ������� ����������� �� ��������� ���������� ������. ��� ������ �� ��������� � ���� ������� �������������� � ���������� �����, �������� ���������� ������. ��������, ����������.
<?php endif; ?>

<a href="<?=$order_url?>">������� � ������</a> / <a href="<?=$cancel_url?>">�������� ���</a><?=PHP_EOL?>