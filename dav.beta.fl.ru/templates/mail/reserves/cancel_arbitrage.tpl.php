<?php
/**
 * ������ ������ ����������� �� ������ ��������� (�-16, �-17)
 */

/**
 * ���� ������
 */
$smail->subject = "�������� �� ������ �{$order['title']}� �������";

$role = $is_emp ? '����������� ���������' : '�� ������ ����������';
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
?>
������������.
<br/>
<br/>
�� ������������ ������ ����������� ������������ ������ �<?=$title?>� ���� ��������. <?=$role?> ���������� ������.
<br/><br/>
<a href="<?=$order_url?>">������� � ������</a>
<br/>
<br/>
-----
<br/>
<br/>
� ���������, ������� <a href="<?php echo "{$GLOBALS['host']}/{$params}"; ?>">FL.ru</a>