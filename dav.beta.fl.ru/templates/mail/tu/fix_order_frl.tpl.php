<?php

/*
 * ������ ������ ����������� ����������� � ����������� ������ � ������
 * ��� �������� ������� (\n) ����� �������� <br/> ��� ������ ��������� � ��� �������� ������
 */

$smail->subject = "����� �{$order['title']}� ��������� � ������";

$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);

?>
�������� <?=$emp_fullname?> ������ ����� &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; � ������. 
����������, ���������� � ���������� ������� ����������� �������������� � ���������� ���������� ������. 

<a href="<?=$order_url?>">������� � ������</a>