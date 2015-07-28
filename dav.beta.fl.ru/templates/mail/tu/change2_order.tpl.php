<?php

/**
 * ����� ������� ������ ����������� ����������� �� ��������� ������
 * ���������� ��������� � ������
 */

$templ = '%s >> %s';

$order_price_txt = isset($order['old_order_price'])?sprintf($templ,
        tservices_helper::cost_format($order['old_order_price'], false),
        tservices_helper::cost_format($order['order_price'], true, false, false)):
        tservices_helper::cost_format($order['order_price'], true, false, false);       


$order_days_txt = isset($order['old_order_days'])?sprintf($templ,
        $order['old_order_days'],
        tservices_helper::days_format($order['order_days'])):
        tservices_helper::days_format($order['order_days']);

$is_new_reserve = tservices_helper::isOrderReserve($order['pay_type']);
$order_paytype_txt = $is_new_reserve?"� ���������������":"��� ��������������";

if(isset($order['old_pay_type']))
{
    $is_old_reserve = tservices_helper::isOrderReserve($order['old_pay_type']);
    $from_txt = $is_old_reserve?"� ���������������":"��� ��������������";
    $to_txt = !$is_old_reserve?"� ���������������":"��� ��������������";
    $order_paytype_txt = sprintf($templ,$from_txt,$to_txt);
}

?>
������: <?=$order_price_txt . PHP_EOL?>
����: <?=$order_days_txt . PHP_EOL?>
��� ������: <?=$order_paytype_txt . PHP_EOL?>