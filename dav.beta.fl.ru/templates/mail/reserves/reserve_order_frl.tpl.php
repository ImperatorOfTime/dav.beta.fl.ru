<?php

/**
 * �-21 - ����������� �� ������� ����������������� �����
 */

$smail->subject = "����� �� ������ �{$order['title']}� ���������������";

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$reserve_price = tservices_helper::cost_format($order['reserve_data']['price'], true, false, false);

?>
�������� �������������� ����� <?=$reserve_price?> � ������ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;. ����� �� ������ ������ ���������� ������ �� ������.
��������� ��� ��������������!

<a href="<?=$order_url?>">������� � ������</a>
