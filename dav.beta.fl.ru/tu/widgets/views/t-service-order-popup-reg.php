<?php

/**
 * ����� ��� ������ �� ��� ���������
 */

$title = reformat($title, 30, 0, 1);

$show_popup = (isset($_POST['popup']));

?>
<div class="b-shadow b-shadow_center b-shadow_width_520 <?php if(!$show_popup){ ?>b-shadow_hide <?php } ?>b-shadow__quick"  id="tesrvices_order_auth_popup" style="display:block;">
  <div class="b-shadow__body b-shadow__body_pad_15_20">

    <h2 class="b-layout__title">����� ������</h2>
    <div class="b-layout__txt b-layout__txt_padbot_20">��� ������ ������ "<?=$title?>", ��� ���������� ������������������ (������ ��� � e-mail) ��� ��������������.</div>
    <table class="b-layout__table">
        <tbody><tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_70 b-layout__td_padbot_15 b-layout__td_width_null_iphone"><div class="b-layout__txt b-layout__txt_padtop_4 b-page__desktop b-page__ipad">���</div></td>
                <td class="b-layout__td b-layout__td_padbot_15">
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-page__iphone">���</div>
                    <div class="b-combo">
                        <div class="b-combo__input b-combo__input_width_260">
                            <input class="b-combo__input-text b-combo__input-text_italic" type="text" placeholder="���� ���, �� ����� 21 �������" size="21" maxlength="21" id="reg_name">
                        </div>
                    </div>         
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_70 b-layout__td_padbot_15 b-layout__td_width_null_iphone"><div class="b-layout__txt b-layout__txt_padtop_4 b-page__desktop b-page__ipad">�������</div></td>
                <td class="b-layout__td b-layout__td_padbot_15">
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-page__iphone">�������</div>
                    <div class="b-combo">
                        <div class="b-combo__input b-combo__input_width_260">
                            <input class="b-combo__input-text b-combo__input-text_italic" type="text" placeholder="���� �������, �� ����� 21 �������" size="21" maxlength="21" id="reg_surname">
                        </div>
                    </div>         
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_70 b-layout__td_padbot_30 b-layout__td_width_null_iphone"><div class="b-layout__txt b-layout__txt_padtop_4 b-page__desktop b-page__ipad">E-mail</div></td>
                <td class="b-layout__td b-layout__td_padbot_30">
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-page__iphone">E-mail</div>
                    <div class="b-combo">
                        <div class="b-combo__input b-combo__input_width_260">
                            <input class="b-combo__input-text b-combo__input-text_italic" type="text" onblur="TServices_Order_Auth.checkEmail(1);" onkeyup="TServices_Order_Auth.checkEmail(0);" placeholder="������� ��� e-mail" size="80" id="reg_email" name="email">
                        </div>
                    </div>         
                </td>
                <td class="b-layout__td">
                    <div class="i-shadow">
                        <div style="z-index: 40" class="b-shadow b-shadow_m b-shadow_top_0 b-shadow_hide" id="error_email">
                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
                                <div class="b-layout__txt b-layout__txt_nowrap b-layout__txt_padright_15 b-layout__txt_color_c4271f" id="error_txt_email"><span class="b-form__error"></span></div>
                            </div>
                            <span class="b-shadow__icon b-shadow__icon_close b-shadow__icon_right_12 b-shadow__icon_top_12"></span>
                            <span class="b-shadow__icon b-shadow__icon_nosik-left b-shadow__icon_top_10 b-shadow__icon_left_-4"></span>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody></table>
    <div class="b-buttons b-buttons_padleft_70 b-buttons_padleft_null_iphone">
        <a href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green" onclick="TServices_Order_Auth.submitForm(); return false;">������������������<span class="b-page__ipad b-page__desktop"> � �������� ������</span></a>
        <span class="b-layout__txt b-layout__txt_fontsize_11">&nbsp;<span class="b-page__iphone"> � �������� ������</span> ��� <a class="b-layout__link" href="/registration/?type=empl&user_action=tu">��������������</a></span>
        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_10">����� ������� �� ������ "������������������ � �������� ������" �� ��������� ����� ����� ���������� ������ �� �������.<br>������� �� ���, �� ����������� ����������� � ����� ������.</div>
    </div>
  </div>
  <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>