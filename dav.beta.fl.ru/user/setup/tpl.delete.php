<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/users.common.php");
$xajax->printJavascript('/xajax/');
//require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/account.common.php");
//$xajax->printJavascript('/xajax/');
//$a_count = $attach ? count($attach) : 0;
?>
<div class="b-layout b-layout_padtop_20">
    <h2 class="b-layout__title">�������� ��������</h2>
    <div class="b-layout__txt b-layout__txt_padbot_10">����� ���� ��� �� ����������� �������� ������ �������, �� ���������� ��������� ������������ ������, �� ��������� ��������� ����� ����� ������ �������� �. 4.8 � �. 4.11 ����������������� ����������.</div>
    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_padleft_50 b-layout__txt_italic"><span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_width_35 b-layout__txt_margleft_-35">4.8.</span>������������ ������ ��������� �������������� ������������ � ������� ����� �� ���������� �� �����. ���� ������������ ������, ����������� �� ����� ��� � ������� ���������, ����� �������, �� ����������� ���������� �� ���������. ������ ����������� ����� ����� ��������� ��������� ����� ������������� ������ ��������� �� ���������� �����������.</div>
    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_padleft_50 b-layout__txt_italic"><span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_width_35 b-layout__txt_margleft_-35">4.11.</span>����������� ����� ����� ��������� �������� ����� � ��� ������ ��� ���� ��������� ���������� ��� �������� ��������� �������� ������ � ���������:</div>
    <ul class="b-layout__list b-layout__list_padleft_50">
        <li class="b-layout__item b-layout__item_padbot_10 b-layout__item_lineheight_15 b-layout__item_italic b-layout__item_style-type_disc">��������������� �������, � ��� ����� ������� �������� � ���������, � ������� �������� �������������� �� �� ��������������� �������;</li>
        <li class="b-layout__item b-layout__item_padbot_10 b-layout__item_lineheight_15 b-layout__item_italic b-layout__item_style-type_disc">�� ��������� ��������� ����;</li>
        <li class="b-layout__item b-layout__item_lineheight_15 b-layout__item_italic b-layout__item_style-type_disc">� ���� ��������������� ����������� ����������������� �� �������.</li>
    </ul>
    <div class="b-layout__txt b-layout__txt_padbot_10">������� �� ������ ��������, ��� �� ������ �� ������ ����� �������������� ����� ��������, �������� �. 1.1 <a href="https://st.fl.ru/about/documents/appendix_2_regulations.pdf" class="b-layout__link">������ �����</a>.</div>
    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_padleft_50 b-layout__txt_italic"><span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_width_35 b-layout__txt_margleft_-35">1.1.</span>��������� ������������� ����������� �������� ����� ������������� (���� ������������ ����� ���������������� ���� ������� ���������� � ���� ������� ������������). �������� ��� ���������� �������� �� ���� ����� �� ����������� ������.</div>
    <div class="b-layout__txt b-layout__txt_padbot_10">���� �� ������ ����������� ������ �� �����, ������ �������� � <a class="b-layout__link b-layout__link_underline" href="http://feedback.fl.ru/">������ ���������</a>, � �� ����������� ��� �������.</div>
    
    
    
    <form name="del_acc_form" method="post" id="del_acc_form">
    <div class="b-check b-check_padbot_40">
        <input type="checkbox" id="b-check1" class="b-check__input" name="" value="">
        <label for="b-check1" class="b-check__label b-check__label_fontsize_13">� �������� � ���������� ��������</label>
    </div>
    <a href="" class="b-button b-button_flat b-button_flat_red b-button_disabled" id="del_acc">������� �������</a>
    <input type="hidden" value="delete" name="action">
    <input type="hidden" value="ba091ffc43a78382662535d87b6317f5" name="u_token_key"></form>
</div>