<?php

/*
 * ���� ����� ������, � � ������� 3� ����� ����� �������� ������� 
 * �� �������� �����, �� ���������� ������� �������� �����������.
 */

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);

?>
���������� ���, ��� 3 ��� ����� ���� ��������� �������������� �� ������ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;. 
� ������� 4 ���� �� ������ �������� ����� � ��������������.
<br/>
<br/>
<a href="<?=$order_url?>">������� � ������</a> / 
<a href="<?=$order_url?>">�������� �����</a>
<br/>
<br/>