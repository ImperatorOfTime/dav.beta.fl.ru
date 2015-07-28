<?php
/**
 * ������ ������ ����������� ��������� � �������� ������ ������ c ��������������� ����� (�-2)
 */

/**
 * ���� ������
 */
$smail->subject = "��� ����� �{$order['title']}� ������� ������";

$order_price = tservices_helper::cost_format($order['order_price'], true, false, false);
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$tu_url = $GLOBALS['host'] . tservices_helper::card_link($order['tu_id'], $order['title']);
$order_days = tservices_helper::days_format($order['order_days']);
$cancel_url = $GLOBALS['host'] . tservices_helper::getOrderStatusUrl($order['id'], 'cancel', $order['emp_id']);

?>
������������.
<br/>
<br/>
��� ����� &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; ������� ������, � ����������� <?=$frl_fullname?> ������� ����������� � ���. 
<br/>
��� ������ ����������� ��������� � ���� ������� �������������� � ���������� �����, �� ������� ��������������� ������ ������. 
����� ����� �������� ���������� ������. ��������, ����������.
<br/><br/>
<?php if($order['type'] == TServiceOrderModel::TYPE_TSERVICE): ?>
����������, ��� �� �������� ������ &laquo;<a href="<?=$tu_url?>"><?=$title?></a>&raquo; 
<?php if($order['order_extra']){ ?>
� �������������:
<br/>
    <?php foreach($order['order_extra'] as $idx ){ ?>
        <?php if(!isset($order['extra'][$idx])) continue; ?>
        - <?php echo reformat(htmlspecialchars($order['extra'][$idx]['title']), 30, 0, 1); ?><br/>
    <?php } ?>
<?php } ?>
�� ����� <?=$order_price ?> �� ������ ���������� <?=$order_days?>.
<?php else: ?>
����������, ��� ����� �� ����� <?=$order_price ?> �� ������ ���������� <?=$order_days?>.
<?php endif; ?>
<br/><br/>
<a href="<?=$order_url?>">������� � ������</a> / 
<a href="<?=$cancel_url?>">�������� ���</a>
<br/><br/>
<i>��� �������� � �������:
<br/>
1. ������� ����� �������� � ������������ ��� ������� ��������������;<br/>
2. ��������� ������������� ������ ������������ � ��������������� �� ����� ����������� �����;<br/>
3. � �������� �������������� ������������ �������������� ���������� �����, ���������� � ������������;<br/>
4. �� ��������� ����� �������� �� ����������� �������� ���������, ��������� ��� � ����� ��������� ����� (��� ����� ���������� ������� �����);<br/>
5. ���������� ��������.
</i>
<br/>
<br/>
�������� ��������, ��� ��� ������������ � �������� �������������� �� ������ ������ ���������� � ��������. 
� ������� ����������������� �����, ���� ������ ��������� ������������� ��� �� � ����.
<br/>
<br/>
� ���������, 
<br/>
������� <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>