<?php
/**
 * ������ ������ ����������� ����������� �� ��������� ������ (�-3)
 * ��� �������� ������� (����� ������) ����� �������� <br/>
 */

/**
 * ���� ������
 */
$smail->subject = "����� �� ������ �{$order['title']}� �������";

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);

$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$accept_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'accept', $order['frl_id']);
$decline_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'decline', $order['frl_id']);

?>
�������� <?=$emp_fullname?> ������� ��������� ������ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;:

<?php include 'change2_order.tpl.php'; ?>

<a href="<?=$order_url?>">������� � ������</a> / <a href="<?=$accept_url?>">����������� ���</a> / <a href="<?=$decline_url?>">���������� �� ����������</a><?=PHP_EOL?>