<?php

/*
 * ������ ����������� ����������� � ���������� ������ ���������� � ��������� ������ (��-10)
 * ��� �� ������������ ��� �������� �� ������� ��� �������� ������� (����� ������) ����� �������� <br/> ��� ������ ��������� � ��� �������� ������
 */


$smail->subject = "���������� ������ �{$order['title']}�";


$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
//$tu_url = $GLOBALS['host'] . tservices_helper::card_link($order['tu_id'], $order['title']);
$emp_feedback = reformat(htmlspecialchars($order['emp_feedback']), 30);
$emp_is_good = ($order['emp_rating'] > 0);
//$feedback_url = $GLOBALS['host'] . "/users/{$order['employer']['login']}/opinions/";

if(empty($emp_feedback))
{
    
?>
�������� <?=$emp_fullname?> �������� �������������� � ������ ����� &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;.
<a href="<?=$order_url?>">�� ������ �������� �����.</a>
<?php

}
else
{
    
?>
�������� <?=$emp_fullname?> �������� �������������� � ���� �� ������ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; � ������� <?php if($emp_is_good){ ?>�������������<?php }else{ ?>�������������<?php } ?> �����:

<i><?=$emp_feedback?></i>

������������ � ��� � �������� �������� ����� �� ������ � ������ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;.

<a href="<?=$order_url?>">������� � ������</a> / <a href="<?=$order_url?>">�������� �������� �����</a> 
<?php

}