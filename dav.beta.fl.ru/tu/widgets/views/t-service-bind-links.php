<div class="b-pay-tu__hider b-layout_padleft_20">
    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10 b-layout__txt_fontsize_15">
        ������ ����������<br>�� <?=$date_stop?>
    </div>
    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_6db335 b-layout__txt_padbot_5 b-layout__txt_fontsize_15">
        �������� �����������<br>�� 7 � ����� ����
    </div>
    <a class="b-button b-button_flat b-button_flat_green" href="#"
       data-popup="<?= quickPaymentPopupTservicebind::getInstance()->getPopupId($tservice_id) ?>">��������</a>
       <?php if ($allow_up): ?>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_6db335 b-layout__txt_padbot_5 b-layout__txt_fontsize_15 b-layout__txt_padtop_20">
            ��������� ������ ��<br>1 ����� �� <?= view_cost_format($bind_up_price, false) ?> ������
        </div>
        <a class="b-button b-button_flat b-button_flat_green" href="#"
           data-popup="<?= quickPaymentPopupTservicebindup::getInstance()->getPopupId($tservice_id) ?>">�������</a>
       <?php endif; ?>
</div>