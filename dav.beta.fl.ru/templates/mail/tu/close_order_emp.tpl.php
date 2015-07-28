<?php

/*
 * ������ ����������� � ���������� ������ ������������ � ��������� ������ (��-9)
 * ��� �� ������������ ��� �������� �� ������� ��� �������� ������� (����� ������) ����� �������� <br/> ��� ������ ��������� � ��� �������� ������
 */

$smail->subject = "���������� ������ �{$order['title']}�";

$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order['id']);
$frl_feedback = reformat(htmlspecialchars($order['frl_feedback']), 30);
$frl_is_good = ($order['frl_rating'] > 0);
//$feedback_url = $GLOBALS['host'] . "/users/{$order['freelancer']['login']}/opinions/";

if(empty($frl_feedback))
{
    
?>
����������� <?=$frl_fullname?> �������� �������������� � ������ ����� &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;.
<a href="<?=$order_url?>">�� ������ �������� �����.</a>
<?php

}
else
{
        
?>
����������� <?=$frl_fullname?> �������� �������������� � ���� �� ������ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo; � ������� <?php if($frl_is_good){ ?>�������������<?php }else{ ?>�������������<?php } ?> �����:

<i><?=$frl_feedback?></i>

������������ � ��� � �������� �������� ����� �� ������ � ������ &laquo;<a href="<?=$order_url?>"><?=$title?></a>&raquo;.

<a href="<?=$order_url?>">������� � ������</a> / <a href="<?=$order_url?>">�������� �������� �����</a> 
<?php

}