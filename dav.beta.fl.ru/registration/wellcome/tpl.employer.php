<div class="b-layout">
    <div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
        <h1 class="b-page__title b-page__title_padbot_30">����� ���������� �� FL.ru</h1>
    </div>

    <div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
        <? if (!$_COOKIE['master_auth']) { ?>
        <div class="b-fon b-fon_inline-block b-fon_padbot_50">
            <div class="b-fon__body b-fon__body_pad_15  b-fon__body_padleft_30 b-fon__body_padright_40 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                <span class="b-icon b-icon_sbr_gattent b-icon_margleft_-20 b-icon_top_8"></span>����������� ��� � �������� ������������ �� ����� FL.ru!<br>�� e-mail, ��������� ��� �����������, ���������� ������ � �������, ������� � ������� ��� ��������� ��������. ����������, ��������� ��� ������� ������ � ����������� �������.
            </div>
        </div>
        <? } ?>
        <div class="b-layout__txt b-layout__txt_fontsize_22 b-layout__txt_padbot_40"><a href="/public/?step=1&kind=1" class="b-layout__link">����������� ������</a>, <a href="/public/?step=1&kind=7" class="b-layout__link">�������</a> ��� <a href="/public/?step=1&kind=4" class="b-layout__link">��������</a></div>
        <div class="b-layout__txt b-layout__txt_fontsize_22 b-layout__txt_padbot_40">��������� <a href="/users/<?= $_SESSION['login']?>/setup/info/" class="b-layout__link">�������</a></div>
        <div class="b-layout__txt b-layout__txt_fontsize_22">�����������  <a href="/payed-emp/" class="b-layout__link">�������</a> <a href="/payed-emp/" class="b-layout__link"><span title="PRO" class="b-icon b-icon__spro b-icon__spro_e"></span></a></div>
        <div class="b-layout__txt b-layout__txt_padbot_40">� �������� ������ �� ������ � ������ ������ </div>
        <div class="b-layout__txt b-layout__txt_fontsize_22 b-layout__txt_padbot_40">��������� <a href="/masssending/" class="b-layout__link">����������</a> �� �������� �����������</div>
        <div class="b-layout__txt b-layout__txt_fontsize_22">����������� ����   <a href="/promo/verification/" class="b-layout__link">��������</a>,</div>
        <div class="b-layout__txt b-layout__txt_padbot_40">����� ��� �������� ��������</div>
    </div>
</div>

<?
if ( !empty($_SESSION['is_new_user']) ) {
    unset($_SESSION['is_new_user']);
?>
<script language="javascript" src="http://www.everestjs.net/static/st.v2.js"></script>
<script language="javascript">
var ef_event_type="transaction";
var ef_transaction_properties = "ev_reg_worker=0&ev_reg_employer=0&ev_reg_worker_master=0&ev_reg_employer_master=1&ev_transid=<?=md5($_SESSION['uid'])?>";
/*
 * Do not modify below this line
 */
var ef_segment = "";
var ef_search_segment = "";
var ef_userid="3208";
var ef_pixel_host="pixel.everesttech.net";
var ef_fb_is_app = 0;
effp();
</script>
<noscript><img src='http://pixel.everesttech.net/3208/t?ev_reg_worker=0&ev_reg_employer=0&ev_reg_worker_master=0&ev_reg_employer_master=1&ev_transid=<?=md5($_SESSION['uid'])?>' width='1' height='1'/></noscript>
<?
}
?>
