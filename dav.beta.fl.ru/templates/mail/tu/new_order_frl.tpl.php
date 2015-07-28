<?php
/**
 * ������ ������ ����������� ����������� � �������� ������ (��-2)
 * ��� �� ������������ ��� �������� �� ������� ��� �������� ������� (\n) ����� �������� <br/> ��� ������ ��������� � ��� �������� ������
 */

/**
 * ���� ������
 */
$smail->subject = "����� �� ������ �{$order['title']}�";

$tax_price = tservices_helper::cost_format($order['tax_price'], true, false, false);
$order_price = tservices_helper::cost_format($order['order_price'], true, false, false);
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$order_days = tservices_helper::days_format($order['order_days']);
$tax = $order['tax'] * 100;

$accept_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'accept', $order['frl_id']);
$decline_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'decline', $order['frl_id']);

?>
������������.

�������� <?=$emp_fullname?> ���������� ��� ����� �� ������ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;
<?php if($order['order_extra']){ ?>
� �������������:
<?php 
    foreach($order['order_extra'] as $idx )
    {
        if(!isset($order['extra'][$idx])) continue; 
        echo '- ' . reformat(htmlspecialchars($order['extra'][$idx]['title']), 30, 0, 1).PHP_EOL;
    }
 } 
?>
�� ����� <?php echo $order_price ?> �� ������ ���������� <?=$order_days?>.

<a href="<?=$order_url?>">������� � ������</a> / <a href="<?=$accept_url?>">����������� ���</a> / <a href="<?=$decline_url?>">���������� �� ����������</a>

<i>��� �������� � �������:
1. ������� ����� �������� � ���������� ��� ������� �������������� � ������ ������;
2. ����� ����������� ����� <?php if($tax > 0): ?>(��� ���� � ������ ������� ����� �� ����� ����� �������� �������� <?=$tax_price?> �� �������������� �������)<? endif; ?>;
3. �������� �� ��������� ���������� (���� ����������) � ������ ���������� ������;
4. �� ��������� ����� ������������ ��������� ��������� � �������� �� ���� ������;
5. ��������� ����� � ���������� ��������.
</i>

�������� ��������, ��� � �������� �������������� �� �������������� ��������������� � ���������� � ������ � ������� ������. � �������������� ������ ��� �����, ��������� � ������� ������ � ����������� �� �������� �� ����������.

� ���������, 
������� <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>