<div class="b-layout b-layout_pad_10 b-layout_bord_e6 b-layout_relative b-layout_margbot_10 b-promo__servis <?= $service['option'] == 'contest' ? "b-promo__servis_cont1 b-promo__servis_margtop_-5" : "b-promo__servis_prj1 b-promo__servis_margtop_-2"?>">
    <span class="b-layout__txt b-layout__txt_float_right b-layout__txt_fontsize_15 b-layout__txt_color_fd6c30 b-layout__txt_padtop_2 b-layout__txt_padleft_10"><?= to_money(($bill->pro_exists_in_list_service? $service['pro_ammount']: $service['ammount']))?> ���.</span>
    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padleft_70 b-layout__txt_padtop_2 b-layout__txt_padleft_null_iphone">
        <? 
        switch($service['option']) {
            case 'contest':
                ?>���������� ��������<?
                break;
            case 'office':
                ?>���������� ��������<?
                break;
            case 'logo':
                ?>�������� �������� � ������ ��� �������<?
                break;
            case 'urgent':
                ?>������� ������ ��� �������<?
                break;
            case 'hide':
                ?>������� ������ ��� �������<?
                break;
            case 'top':
                ?>����������� ������� ��� �������� � ������ �� <?= $service['op_count'] . " ". ending($service['op_count'], '����', '���', '����')?> <?
                break;
        }
        ?>
                
    </div>
</div>