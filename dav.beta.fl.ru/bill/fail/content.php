<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/bill.common.php");
$xajax->printJavascript('/xajax/');
?>
<a name="top"></a>
<div class="b-layout b-layout__page" style="margin-top:0px">
    <div class="body">
        <div class="main"> <a name="top"></a>
            <div class="b-layout b-layout__page">
                <div class="b-menu b-menu_crumbs">
                    <ul class="b-menu__list">
                        <li class="b-menu__item"><a class="b-menu__link" href="/bill/">��� ������</a>&nbsp;&rarr;&nbsp;</li>
                    </ul>
                </div>
                <h1 class="b-page__title">������ ������ �� ����� <span class="b-layout__txt b-layout__txt_color_fd6c30 b-layout__txt_fontsize_34"><?= to_money($reserveData['res_ammount']) ?> ���.</span></h1>

                <? include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.right_column.php"); ?>

                <div class="b-layout__one b-layout__one_width_72ps">
                    <h2 class="b-layout__title">������ ������� �� �������</h2>
                    <!--<div class="b-layout__txt b-layout__txt_fontsize_15">������: ����������� ������.</div>-->

                    <div class="b-buttons b-buttons_padtop_40">
                        <a href="javascript:void(0)" class="b-button b-button_rectangle_color_green" onclick="$('pay_form').submit();">
                            <span class="b-button__b1">
                                <span class="b-button__b2">
                                    <span class="b-button__txt">�������� <span class="b-button__colored b-button__colored_fd6c30"><?= to_money($reserveData['res_ammount']) ?> ���.</span></span>
                                </span>
                            </span>
                        </a>&nbsp;&nbsp;&nbsp;<span class="b-buttons__txt">���</span> <a href="/bill/" class="b-buttons__link">��������� � ������ �������</a>
                    </div>
                </div>

                <form method="post" id="pay_form" action="/bill/fail/">
                    <input type="hidden" name="action" value="pay" />
                    <input type="hidden" name="reserve_id" value="<?= $reserveData['res_id'] ?>" />
                </form>

            </div>
        </div>
    </div>
</div>