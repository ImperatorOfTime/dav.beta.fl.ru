<?php

/**
 * ����� ��� ������ �� ��� ��������� c ����������� ������� �� "����� ��"
 */

$title = reformat($title, 30, 0, 1);
$days = $days . ' ' . ending($days, '����', '���', '����');
$priceFormated = tservices_helper::cost_format($price,true, false, false);
$priceWithTaxFormated = tservices_helper::cost_format($priceWithTax,true, false, false);

$show_popup = (isset($_POST['popup']));

?>
<script type="text/javascript">
    var RESERVE_ALL_TAX = <?=$reserveAllTaxJSON?>;
</script>
<div id="tservices_orders_status_popup" class="b-shadow b-shadow_center b-shadow_width_520 <?php if(!$show_popup){ ?>b-shadow_hide <?php } ?>b-shadow__quick" style="display:block;">
    <div class="b-shadow__body b-shadow__body_pad_20">
        <h2 class="b-layout__title">
            ����� ������
        </h2>
        <div class="b-layout__txt b-layout__txt_padbot_20">
            ��� ������ ������ ��� ���������� ������� ������ ������ ������ (� ��������������� ����� ��� ��� ����).
        </div>
        <div class="b-layout b-layout_padleft_15">
            <div class="b-layout__txt b-layout__txt_padbot_20">
                ����������� <b><?=$frl_fullname?></b><br/>
                ������ &laquo;<b><?=$title?></b>&raquo; �� <b><span class="__tservice_days"><?=$days?></span></b><br/>
                ����� ������ <b>
                    <span class="__tservice_price3"><?=$priceWithTaxFormated?></span>
                    <span class="__tservice_price2" style="display: none"><?=$priceFormated?></span>
                </b> 
                <span class="__tservice_paytype"> (� ������ <strong><span class="__tservice_reserve_tax"><?=$reserveTax?></span>%</strong> �������� �������)</span>
            </div>
            <div class="b-radio b-radio_layout_vertical">
                <div class="b-radio__item b-radio__item_padbot_10">
                    <input data-hide-class=".__tservice_price2" data-show-class=".__tservice_paytype,.__tservice_price3" data-show-display="inline" tabindex="4" checked="checked" type="radio" value="1" name="paytype" class="b-radio__input" id="paytype1"/>
                    <label for="paytype1" class="b-radio__label b-radio__label_fontsize_13 b-radio__label_bold b-radio__label_margtop_-1">
                        ���������� ������ (� ��������������� �������) &#160;<a class="b-layout__link" href="/promo/bezopasnaya-sdelka/" target="_blank"><span class="b-shadow__icon b-shadow__icon_quest2 b-icon_top_2"></span></a>
                    </label>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        ���������� �������������� � ��������� �������� �������. �� ������������ ������ ������ �� ����� FL.ru - � �� ����������� ��� ������� �����, ���� ������ ����� ��������� ������������ ������������� ��� �� � ����.
                    </div>
                </div>
                <div class="b-radio__item">
                    <input data-hide-class=".__tservice_paytype,.__tservice_price3" data-show-class=".__tservice_price2" data-show-display="inline" tabindex="5" type="radio" value="0" name="paytype" class="b-radio__input" id="paytype0">
                    <label for="paytype0" class="b-radio__label b-radio__label_fontsize_13 b-radio__label_bold b-radio__label_margtop_-1">
                        ������ ������ ����������� �� ��� �������/����
                    </label>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        �������������� ��� ������� ����� � �������� ������. �� ���� ��������������� � ������������ � ������� � ������� ������. � �������������� ����������� ��� ���������, ��������� � ��������� � ������� ���������� ������.
                    </div>
                </div>
            </div>
            <div class="b-buttons b-buttons_padtop_20">
                <a href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green" onclick="yaCounter6051055.reachGoal('zakaz_tu'); TServices.onSendToCbr(this, '__form_tservice');">
                    <span class="__tservices_orders_feedback_submit_label">������� ����� � ������� � ����</span>
                </a>
                <span class="b-layout__txt b-layout__txt_fontsize_11">&#160; ��� 
                    <a class="b-layout__link" href="javascript:void(0);" onclick="TServices.closePopup();">�������� �����</a>
                </span>
            </div>
        </div>
   </div>    
   <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>