<h2 class="b-layout__title b-layout__title_padtop_50"><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="toggleServices('sbr', this)">������ ��� �����</a></h2>
<div class="b-layout__txt b-layout__txt_fontsize_11 services-sbr-default">��� ������ ����������� ������ ������� ��� �� ���������������� ���������� � ������� ����� ������ �������� � �������. ��������� ����������� ������, �� ������� �� �������� ��� ��������� �����, ������� ����� ���������� � �������� �������������� � �������������.</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_relative b-layout_hide services-sbr"><span class="b-icon b-icon_absolute b-icon_left_-140 b-icon_big_sbr"></span>����������� ������ � ��� ������, ������� ��������� ������� ��� �����, ����������� � �������� �������������� ����������� � �������������, �� ��������. ���������������� ����������� �������, ������������ ����� ���� ������ � ���, ��� ��� ������� ����� ��������� �������� ������������� ����������� � � ����, � ���������� ������������� ������ � ������ ������ � ��� ������, ���� �� ������� ����������� ��������� ����������� ������.</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout_hide services-sbr">������������ �������������� ������������ ����������� �������������� ������������� � � ������ ������������ � ����������������� ����������� �������� � ��������������� ��� ����������� ������������ ���������:</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout_hide services-sbr">&mdash; ������ �� ���������� �������� �� ������������� ����������� ������;</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout_hide services-sbr">&mdash; ��������� ������� ��� ������� �������;</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout_hide services-sbr">&mdash; ��� � ����-������� �� ��������.</div>
<div class="b-layout__txt b-layout_hide services-sbr"><a class="b-layout__link" href="/promo/sbr/" target="_blank">��������� � &laquo;���������� ������&raquo;</a></div>

<input type="hidden" name="pro-frl" value="<?= $pro_frl ? $pro_frl : "0" ?>" id="pro-frl"> 
<h2 class="b-layout__title b-layout__title_padtop_50"><a class="b-layout__link <?= $pro_frl == 1 ? "b-layout__link_bordbot_dot_000" : "b-layout__link_bordbot_dot_0f71c8" ?>" href="javascript:void(0)" onclick="toggleServices('pro', this); if($(this).hasClass('b-layout__link_bordbot_dot_0f71c8')) { $('pro-frl').set('value', 0); } else { $('pro-frl').set('value', 1); }">���������������� �������</a></h2>
<div class="b-layout__txt b-layout__txt_fontsize_11 services-pro-default <?= $pro_frl == 1 ? "b-layout_hide" : "" ?>">�������� ������� � ��� � ������ ��� �������������� � ������ �������������. � ��������� pro �� �������� ����������� ������� �� �����, � ����� ������� �������� �� ����������� ������� ��� <span class="b-icon b-icon__pro b-icon__pro_f"></span>.</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_relative <?= $pro_frl == 0 ? "b-layout_hide" : "" ?> services-pro"><span class="b-icon b-icon_absolute b-icon_left_-140 b-icon_big_fpro"></span>��������� ��������  <span class="b-icon b-icon__pro b-icon__pro_f"></span> &mdash; ��� �������� �������� � ��������� ����� ��������� Free-lance.ru.</div>
<div class="b-layout__txt b-layout__txt_padbot_10 <?= $pro_frl == 0 ? "b-layout_hide" : "" ?> services-pro">��� ��������� ������������ ����������������� ��������:</div>
<div class="b-layout__txt b-layout__txt_padbot_10 <?= $pro_frl == 0 ? "b-layout_hide" : "" ?> services-pro">&mdash; ����������� �������� �� ������� &laquo;������ ��� <span class="b-icon b-icon__pro b-icon__pro_f"></span>&raquo;;</div>
<div class="b-layout__txt b-layout__txt_padbot_10 <?= $pro_frl == 0 ? "b-layout_hide" : "" ?> services-pro">&mdash; ���������� ������ �� ����� �������;</div>
<div class="b-layout__txt b-layout__txt_padbot_20 <?= $pro_frl == 0 ? "b-layout_hide" : "" ?> services-pro">&mdash; ��������� �������� � ���������� � ����������� ���� ��������.</div>
<div class="b-radio b-radio_layout_vertical <?= $pro_frl == 0 ? "b-layout_hide" : "" ?> services-pro">
    <div class="b-radio__item b-radio__item_padbot_20">
        <input type="radio" value="1week" name="pro" class="b-radio__input" id="b-radio__answer1" <?= ($op_code == 76?"checked":"")?>>
        <label for="b-radio__answer1" class="b-radio__label b-radio__label_fontsize_15 b-radio__label_lineheight_1"><span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_bold b-pay-answer__txt_inline-block b-pay-answer__txt_width_90">1 ������</span><span class="b-pay-answer__fm b-pay-answer__fm_fontsize_15">210 ���.</span></label>
    </div>
    <div class="b-radio__item b-radio__item_padbot_20">
        <input type="radio" value="1" name="pro" class="b-radio__input" id="b-radio__answer2" <?= ($op_code == 48?"checked":"")?>>
        <label for="b-radio__answer2" class="b-radio__label b-radio__label_fontsize_15 b-radio__label_lineheight_1"><span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_bold b-pay-answer__txt_inline-block b-pay-answer__txt_width_90">1 �����</span><span class="b-pay-answer__fm b-pay-answer__fm_fontsize_15">570 ���.</span><span class="b-pay-answer__economy">�������� 36%</span></label>
    </div>
    <div class="b-radio__item b-radio__item_padbot_20">
        <input type="radio" value="3" name="pro" class="b-radio__input" id="b-radio__answer3" <?= ($op_code == 49?"checked":"")?>>
        <label for="b-radio__answer3" class="b-radio__label b-radio__label_fontsize_15 b-radio__label_lineheight_1"><span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_bold b-pay-answer__txt_inline-block b-pay-answer__txt_width_90">3 ������</span><span class="b-pay-answer__fm b-pay-answer__fm_fontsize_15">1620 ���.</span><span class="b-pay-answer__economy">�������� 40%</span></label>
    </div>
    <div class="b-radio__item b-radio__item_padbot_20">
        <input type="radio" value="6" name="pro" class="b-radio__input" id="b-radio__answer3" <?= ($op_code == 50?"checked":"")?>>
        <label for="b-radio__answer3" class="b-radio__label b-radio__label_fontsize_15 b-radio__label_lineheight_1"><span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_bold b-pay-answer__txt_inline-block b-pay-answer__txt_width_90">6 �������</span><span class="b-pay-answer__fm b-pay-answer__fm_fontsize_15">3060 ���.</span><span class="b-pay-answer__economy">�������� 43%</span></label>
    </div>
    <div class="b-radio__item b-radio__item_padbot_20">
        <input type="radio" value="12" name="pro" class="b-radio__input" id="b-radio__answer3" <?= ($op_code == 51?"checked":"")?>>
        <label for="b-radio__answer3" class="b-radio__label b-radio__label_fontsize_15 b-radio__label_lineheight_1"><span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_bold b-pay-answer__txt_inline-block b-pay-answer__txt_width_90">1 ���</span><span class="b-pay-answer__fm b-pay-answer__fm_fontsize_15">5400 ���.</span><span class="b-pay-answer__economy">�������� 50%</span></label>
    </div>
    <div class="b-radio__item b-radio__item_padbot_20">
        <input type="radio" value="-1" name="pro" class="b-radio__input" id="b-radio__answer1" <?= ($op_code > 0?"":"checked")?>>
        <label for="b-radio__answer1" class="b-radio__label b-radio__label_valign_top b-radio__label_fontsize_13">�� �������� ������� PRO <span class="b-radio__txt b-radio__txt_valign_top b-radio__txt_inline-block b-radio__txt_color_c10600">&mdash; ������������ ���� ������� ����� � ��������������<br />&#160;&#160;&#160;&#160;������ �� ������� �� ������������</span></label>
    </div>
</div>
<div class="b-layout__txt <?= $pro_frl == 0 ? "b-layout_hide" : "" ?> services-pro"><a class="b-layout__link" href="/payed/" target="_blank">��������� � ���������������� ��������</a></div>

<h2 class="b-layout__title b-layout__title_padtop_50"><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="toggleServices('adv', this)">������� ��������</a></h2>
<div class="b-layout__txt b-layout__txt_fontsize_11 services-adv-default">�� ����� ����� ���������������� ������� ���������� �����������, ������� ��������� ����������� ��� ���� ����� ������. ��� ���� ����� ���������� ����� ����������� � ����� �������� ��� �������������, �� ������ ��������������� ��������������� ���������.</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_relative b-layout_hide services-adv"><span class="b-icon b-icon_absolute b-icon_left_-140 b-icon_big_reclam"></span>�������� ���������� � ������ �������� ����� ����������� ���������:</div>
<table class="b-layout__table b-layout_hide services-adv" border="0" cellpadding="0" cellspacing="0">
    <tr class="b-layout__tr">
        <td class="b-layout__left">
            <h3 class="b-layout__h3">������� ����� � �������� �� ������� ��������</h3>
            <div class="b-layout__txt b-layout__txt_padbot_10">�� ����������� ��������� ������ � ��������� ��������� �����, ����� � ������. � ���������� �� ���� ����� �� ��� ���, ���� ��������� ������������ �� ������� ����� �� ������. � ����� ��������� ����� ����������� ���� ����������, ������������� � �������� ��������� � ������������ �������.</div>
            <div class="b-layout__txt b-layout__txt_padbot_20"><a class="b-layout__link" href="/pay_place/top_payed.php" target="_blank">�������� ���������� ������� ������� ��������</a></div>
        </td>
        <td class="b-layout__right  b-layout__right_padbot_20 b-layout__right_padleft_20">
            <div class="b-shadow b-shadow_m b-shadow_inline-block">
                <div class="b-shadow__right">
                    <div class="b-shadow__left">
                        <div class="b-shadow__top">
                            <div class="b-shadow__bottom">
                                <div class="b-shadow__body b-shadow__body_bg_fff">
                                    <img class="b-layout__pic" src="/images/master/tmp3.png" alt="" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-shadow__tl"></div>
                <div class="b-shadow__tr"></div>
                <div class="b-shadow__bl"></div>
                <div class="b-shadow__br"></div>
            </div>
        </td>
    </tr>
</table>
<table class="b-layout__table b-layout_hide services-adv" border="0" cellpadding="0" cellspacing="0">
    <tr class="b-layout__tr">
        <td class="b-layout__left">
            <h3 class="b-layout__h3">������� ����� � �������� � �������� �����������</h3>
            <div class="b-layout__txt b-layout__txt_padbot_10">� ������� ����� ������� �� ������ ���������� ��������� ��������� ����������, ���������� ���� ����������, ������������� � �������� ���������, � ����������� ������ ������� �������� �����������. �� ���� ������ ����� �� ������ ������� �������������� ���� ���������� ����� ���������� ���������� ������.</div>
            <div class="b-layout__txt b-layout__txt_padbot_20"><a class="b-layout__link" href="/pay_place/top_payed.php?catalog" target="_blank">�������� ���������� ������� ��������</a></div>
        </td>
        <td class="b-layout__right b-layout__right_padleft_20">
            <div class="b-shadow b-shadow_m b-shadow_inline-block">
                <div class="b-shadow__right">
                    <div class="b-shadow__left">
                        <div class="b-shadow__top">
                            <div class="b-shadow__bottom">
                                <div class="b-shadow__body b-shadow__body_bg_fff">
                                    <img class="b-layout__pic" src="/images/master/tmp4.png" alt="" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-shadow__tl"></div>
                <div class="b-shadow__tr"></div>
                <div class="b-shadow__bl"></div>
                <div class="b-shadow__br"></div>
            </div>
        </td>
    </tr>
</table>

<h2 class="b-layout__title b-layout__title_padtop_50"><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="toggleServices('blog', this)">����� � ����������</a></h2>
<div class="b-layout__txt b-layout__txt_fontsize_11 services-blog-default">� ��� ���� �� ���������� ��������� ����������� � ������������� � ������. ������ ���� � �������� ����������� ����� ��������� �� ����� ��������� ����. �� ������ ������ ����� ������ ������������� ������ �����.</div>
<div class="b-layout__txt b-layout__txt_padtop_10 b-layout__txt_fontsize_11 services-blog-default">����� � ��� ���� ���������� &ndash; ��� ������ ������������� �� ���������. ��������� ������������� ��� ����������, ���������, ��������� � ����� ����������������.</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_relative b-layout_hide services-blog"><span class="b-icon b-icon_absolute b-icon_left_-140 b-icon_big_blog"></span>�� ����� ����� ���� �� ���������� ��������� ����������� � ����������. ������ ���� ����������� ����� 1000 ����� ������, ������ ���� � ������� ������������ 10 �������.</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout_hide services-blog">
    ������������ �������� �� ��������� ����, ��� ����������������, ��� � ����������������: <?php foreach($themes_blogs as $key=>$theme) { ?> <a class="b-layout__link" href="/blogs/<?=$theme['link']?>/" target="_blank"><?= ($theme['t_name'])?></a><?= ($key+1 != count($themes_blogs)) ? ",":""?><?php }//foreach?> � ������.
</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout_hide services-blog">
    ����� ������ ���� ���������� ����� ������ ������������� �� ���������. ���� ��������� ���������� ��������� � ������� �� ������� ����������������: <?php foreach($themes_commune as $key=>$ctheme) { ?> <a class="b-layout__link" href="/commune/?id=<?=$ctheme['id']?>/" target="_blank"><?= ($ctheme['name'])?></a><?= ($key+1 != count($themes_commune)) ? ",":""?><?php }//foreach?> � ������ ������.
</div>