<?php

/*
 * ������ ����������� ����������� � ���, ��� �������� ������� �������� ����� (��-12)
 * ��� �� ������������ ��� �������� �� ������� ��� �������� ������� (����� ������) ����� �������� <br/> ��� ������ ��������� � ��� �������� ������
 */

$smail->subject = "����� � ������ �{$order['title']}�";

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$tu_url = $order['tu_id'] ? $GLOBALS['host'] . tservices_helper::card_link($order['tu_id'], $order['title']) : '';
$emp_feedback = reformat(htmlspecialchars($order['emp_feedback']), 30);
$emp_is_good = ($order['emp_rating'] > 0);
$feedback_url = $GLOBALS['host'] . "/users/{$order['freelancer']['login']}/opinions/";

?>
�� ����������� �������������� � ������ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; 
�������� ������� ��� <?php if($emp_is_good){ ?>�������������<?php }else{ ?>�������������<?php } ?> �����:

<i><?=$emp_feedback?></i>

������������ � ������� �� ������ � ������ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;, � ������� &laquo;<a href="<?= $feedback_url ?>">������</a>&raquo; �������<?php if ($tu_url): ?> ��� � �������� ������ &laquo;<a href="<?=$tu_url?>"><?=$title?></a>&raquo;<?php endif; ?>.

<a href="<?=$order_url?>">������� � ������</a> / <a href="<?=$order_url?>">�������� ����������� � ������</a>
