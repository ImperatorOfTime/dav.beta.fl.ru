<?php

if($uid > 0) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/quick_payment.common.php");
    $xajax->printJavascript('/xajax/');
?>
<script type="text/javascript">
var account_sum = <?= round($account->sum, 2)?>;
var role = 'EMP';
</script>
<? } ?>

<div class="b-layout b-layout_padtop_15 g-txt_center">
    
    <h1 id="header_payed_pro" class="b-layout__title b-layout__title_bold b-layout__title_fs30 b-layout__title_color_56b824 b-layout__title_padbot_30">
        <?php if ($pro_last): ?>
        ���������������� �������
        <div class="b-layout__txt b-layout__txt_center b-layout__txt_fontsize_20">
            ��������� �� <?= date('d.m.Y', strtotime($pro_last)) ?>
        </div>
        <?php else: ?>
        ������ PRO �������,<br/> 
        ����� ����� ������� ����������� �������!        
        <?php endif; ?>
    </h1>    
    
    <div class="b-layout__txt b-layout__txt_fontsize_25 b-layout__txt_color_333 b-layout__txt_padbot_80">
        � PRO ��������� ���� ������ ������ �������� � ���������<br/>
        <strong>������ ������� �����������. </strong>����� �� �������� ������ � �� ���������<br/>
        ��� ������� ������� �� ���������� ������.
    </div>
    
<?php 

include_once($_SERVER['DOCUMENT_ROOT'] . "/payed/tpl.setting.pro.php"); 
$is_emp_plans = true;
include_once('plans.php');


if ($uid > 0):
    //����� ������ ������
    echo quickPaymentPopupPro::getInstance()->render();
    
    //@todo: ��������� ������� ��������� �� �������� ������� ��� ���������� �� ������ �������, 
    //@todo: ����� ����� ��������� � quickPaymentPopupPro
    if (isset($_GET['quickpro_ok'])):
        require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_buy_pro_win.php");
    endif;
endif;

?> 
</div>


<?php

/**
 * ���� ��������� ���� ����
 */
if (false):
?> 
<h1 class="b-layout__title b-layout__title_center b-layout__title_padtop_30">
    �������� �� ��������� ����� � ��������� 
    <span title="PRO" class="b-icon b-icon__spro b-icon__spro_e"></span>
</h1>

<div class="b-promo_overflow_hidden">
    <ul class="b-promo__specify">
        <li class="b-promo__specify_name">���������� �������</li>
        <li class="b-promo__specify_price"><em>�� <b>800</b> ���.</em>��������</li>
        <li class="b-promo__specify_items">
            <p class="padding_28_0_36_0">
                <span class="b-promo__specify_items_left">1000 ���. <strike>1500 ���.</strike></span>
                <span>����������� �������<br>�����</span>
            </p>
            <p class="border_none padding_28_0_36_0">
                <span class="b-promo__specify_items_left">600 ���. <strike>900 ���.</strike></span>
                <span>�������� ��������</span>
            </p>			
        </li>
    </ul>
    <ul class="b-promo__specify margin_lr_p5">
        <li class="b-promo__specify_name">���������� ��������</li>
        <li class="b-promo__specify_price"><em>�� <b>1100</b> ���.</em>��������</li>
        <li class="b-promo__specify_items">
            <p>
                <span class="b-promo__specify_items_left">3000 ���. <strike>3300 ���.</strike></span>
                <span>���������� ��������</span>
            </p>
            <p>
                <span class="b-promo__specify_items_left">1000 ���. <strike>1500 ���.</strike></span>
                <span>����������� �������<br>�����</span>
            </p>
            <p class="border_none">
                <span class="b-promo__specify_items_left">600 ���. <strike>900 ���.</strike></span>
                <span>�������� ��������</span>
            </p>	
        </li>
    </ul>
    <ul class="b-promo__specify">
        <li class="b-promo__specify_name">���������� ��������</li>
        <li class="b-promo__specify_price"><em>�� <b>1400</b> ���.</em>��������</li>
        <li class="b-promo__specify_items">
            <p>
                <span class="b-promo__specify_items_left">
                    <?=$prices['pro']['vacancy']?> ���. <strike><?=$prices['nopro']['vacancy']?> ���.</strike>
                </span>
                <span>���������� ��������</span>
            </p>
            <p>
                <span class="b-promo__specify_items_left">1000 ���. <strike>1500 ���.</strike></span>
                <span>����������� �������<br>�����</span>
            </p>
            <p class="border_none">
                <span class="b-promo__specify_items_left">600 ���. <strike>900 ���.</strike></span>
                <span>�������� ��������</span>
            </p>
        </li>
    </ul>
</div>

<style type="text/css">
@media screen and (max-width: 1000px){
.b-layout__page .body .b-button.b-button_flat-size_medium { width:auto !important; padding: 0 14px !important;}
.b-layout__page .body .b-button.b-button_flat-size_medium.b-button_flat_green { display:block; width:100px !important;}
}
@media screen and (max-width: 700px){
.b-layout__page .body .b-button.b-button_flat-size_medium { width:200px !important;  float:none !important; margin:0 auto !important; display:block}
}
</style>

<?php 

endif;