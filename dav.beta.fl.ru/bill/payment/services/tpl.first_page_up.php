<div class="b-layout b-layout_pad_10 b-layout_bord_e6 b-layout_relative b-layout_margbot_10 b-promo__servis b-promo__servis_pl-bar1 b-promo__servis_margtop_-8">
    <span class="b-layout__txt b-layout__txt_float_right b-layout__txt_fontsize_15 b-layout__txt_color_fd6c30 b-layout__txt_padtop_2 b-layout__txt_padleft_10"><?= to_money($service['ammount'])?> ���.</span>
    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padleft_70 b-layout__txt_padleft_null_iphone b-layout__txt_padtop_2">
        �������� �������� ����� 
        <?php if($service['src_id'] == 0) { ?>
            � ����� ��������
        <?php } else if($service['src_id'] == -1) { //if?>
            �� ������� ��������
        <?php } else { //elseif?>
           � �������� �<?= professions::GetProfName($service['src_id'])?>�
        <?php }//else?>
    </div>
</div>