<?php

if (isset($uid) && $uid > 0) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/quickver.common.php");
    $xajax->printJavascript('/xajax/');
}

?>
<div class="main">
    <div class="b-anchor"><a name="top" class="b-anchor__link"></a></div>
    <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-verification">
        <tbody>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_center  ">
                    <span class="b-icon b-icon__ver-big"></span>
                    <div class="b-layout__txt b-layout__txt_padtop_20 b-layout__txt_padbot_10 b-layout__txt_fontsize_15">��� ����������������</div>
                    <div class="b-layout__txt b-layout__txt_padbot_60">
                        <span id="count_subscribe"><?=preg_replace("/(\d{1})/", '<span class="b-promo__digital b-promo__digital_margright_3">$1</span>', $verifyCount)?></span>
                        <div class="b-layout__txt b-layout__txt_fontsize_11" id="count_subscribe_text"><?= ending($verifyCount, '������������', '������������', '�������������')?><br />(����������� ��� � 60 �����)</div>
                    </div>
                </td>
                <td class="b-layout__right b-layout__right_padbot_15 b-layout__right_width_72ps b-promo">
                    <h1 class="b-page__title">�����������</h1>
                    <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_15">������ &laquo;�������������&raquo; �������� ������������, ��������� ������� ������ �� ����� fl.ru  � ������� ������� � ����� �� ��������� ������, ��� �������� �������������� ���������� � ��� ��������. <span class="b-layout__bold">����� ����������� ������� ���������� ������������� �� 20%!</span></div>
                    <div class="b-layout__txt b-layout__txt_padbot_30 b-layout__txt_fontsize_15">�����: ��� ��������� ������� &laquo;�������������&raquo; ������������ �� ����� ���������� ������ ����� ���� ������������ ������.</div>

<div class="b-buttons">

    <? if(isset($uid) && $uid > 0) { ?>
        <? if($_SESSION['is_verify']=='t') { ?>
            <a href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green b-button_flat_big b-button_disabled">�� ��� ��������������</a> 
        <? } else { ?>
            <? $quick_verification = 1; ?>
            <a href="javascript:quickVerShow();" class="b-button b-button_flat b-button_flat_green b-button_flat_big">������ �����������</a>             
        <? } ?>
    <? } else { ?>
            <a href="/registration/?user_action=promo_verification" class="b-button b-button_flat b-button_flat_green b-button_flat_big">������ �����������</a>             
    <? } ?>

</div>                  
                    
            </tr>
        </tbody>
    </table>
</div>

<? if($quick_verification==1 || $_GET['vok']) { $quick_verification_type = 'promo'; require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_verification.php"); } ?>