<?php

/*
 * ���� ����� ������, � � ������� 6� ����� ����� �������� ������� 
 * �� �������� �����, �� ���������� ������� �������� �����������.
 */

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);

?>
���������� ���, ��� 6 ���� ����� ���� ��������� �������������� �� ������ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;. 
� ������� ����� �� ������ �������� ����� � ��������������.
<br/>
<br/>
<a href="<?=$order_url?>">������� � ������</a> / 
<a href="<?=$order_url?>">�������� �����</a>
<br/>
<br/>