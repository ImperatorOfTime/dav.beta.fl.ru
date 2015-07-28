<?php

/**
 *  ������ ������ ������������� ������ ��� ����������
 *  ����������� ������� � ��������������� �������
 */

$title = reformat(htmlspecialchars($title), 30, 0, 1);
$price = tservices_helper::cost_format($price, true, false, false);
$days = $days . ' ' . ending($days, '����', '���', '����');
$accept_url = tservices_helper::getOrderStatusUrl($idx, 'accept');
$is_reserve = tservices_helper::isOrderReserve($pay_type);
$tax = $tax*100;

?>
<div id="tservices_orders_status_popup_<?=$idx?>" class="b-shadow b-shadow_center b-shadow_width_580 b-shadow_hide b-shadow__quick __tservices_orders_status_popup_hide" style="display:block;">
    <div class="b-shadow__body b-shadow__body_pad_20">
        <h2 class="b-layout__title">
            ������������� ������
        </h2>
        <div class="b-layout__txt b-layout__txt_padbot_10">
            <b><?=$title?></b>
            <br/><br/>
            ����� ������: <b><?=$price?></b><br/>
            ���� ����������: <b><?=$days?></b>
            <?php if($tax > 0): ?>
            <br/><br/>
                <?php if($is_reserve):?>
                    <b>�������� ��������:</b> ��� ���������� �������������� � ������ ������� 
                    ����� �� ����� ����� ������� �������� (<?=$tax?>% �� ����������� ��� �����). 
                <?php else: ?>
                    <b>�������� ��������:</b> ��� �������� ���������� �������������� � �������� 
                    ������ � ������ ������� ����� �� ����� ����� ������� �������� (<?=$tax?>% �� ����� � ������).
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="b-buttons b-buttons_padtop_20">
            <a href="<?=$accept_url?>" class="b-button b-button_flat b-button_flat_green">
                <span class="__tservices_orders_feedback_submit_label">
                    ����������� �����
                </span>
            </a>
            <span class="b-layout__txt b-layout__txt_fontsize_11">&#160; ��� 
                <a class="b-layout__link" href="javascript:void(0);" onclick="TServices_Order.closeAcceptPopup(<?=$idx?>);">�� ������������</a>
            </span>
        </div>
<?php if($is_reserve): ?>        
        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_13 b-layout__txt_padtop_10">
            ������� ������ "����������� �����", � �������� ������� <a href="http://st.fl.ru/about/documents/reserve_offer_contract.pdf">��������</a> 
            � ���������� �� �������������� � ��� ������.
        </div>
<?php endif; ?>        
   </div>    
   <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>