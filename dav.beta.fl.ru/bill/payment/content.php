<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/bill.common.php");
$xajax->printJavascript('/xajax/');
?>
<div class="b-layout b-layout__page">
    <div class="b-menu b-menu_crumbs">
        <ul class="b-menu__list">
            <li class="b-menu__item"><a class="b-menu__link" href="/bill/">��� ������</a>&nbsp;&rarr;&nbsp;</li>
        </ul>
    </div>
    <h1 class="b-page__title">������ ������ �� ����� <span class="b-layout__txt b-layout__txt_color_fd6c30 b-layout__txt_fontsize_34"><?= to_money( $payed_sum > 10 ? $payed_sum : 10 , 2 )?> ���.</span></h1>
    <div class="b-layout__one b-layout__one_width_25ps b-layout__one_padbot_30 b-layout__right_float_right b-layout__one_width_full_ipad b-layout_padbot_10_ipad">
       <?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.score.php"); ?>
    </div>


    
    <div class="b-layout__one b-layout__one_float_left b-layout__one_width_72ps b-layout__one_width_full_ipad">
        <?php
        $pro_payed = payed::getPayedPROList( is_emp($bill->user['role'])? 'emp' : 'frl' );
        foreach($pro_payed as $p) {
            $pro_type[$p['opcode']] = $p;
        }
        foreach($bill->list_service as $service) { 
            include ($_SERVER['DOCUMENT_ROOT'] . "/bill/payment/services/" . billing::getTemplateByService($service['service']));
        }
        $payment_sum = $bill->payed_sum['pay'];  //@todo ����� �������� ���� �����
        ?>

        <h2 class="b-layout__title b-layout__title_padtop_30">������ �������</h2>
        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_padbot_20">
            <?php if($bill->type_menu_block == 'psys') { ?>
            <span id="active-systems" data-system="psys_systems">��������� �������</span> &nbsp;&nbsp;&nbsp;
            <?php } else { ?>
            <a class="b-layout__link b-layout__link_bold b-layout__link_bordbot_dot_0f71c8" data-system="psys_systems" href="/bill/payment/?type=webmoney" >��������� �������</a> &nbsp;&nbsp;&nbsp;
            <?php }//if?>

            <?php if($bill->type_menu_block == 'mobilesys') { ?>
            <span id="active-systems" data-system="mobilesys_systems">��������� �������</span> &nbsp;&nbsp;&nbsp;
            <?php } else { ?>
            <a class="b-layout__link b-layout__link_bold b-layout__link_bordbot_dot_0f71c8" data-system="mobilesys_systems" href="/bill/payment/?type=megafon_mobile">��������� �������</a> &nbsp;&nbsp;&nbsp;
            <?php }//if?>

            <?php if($bill->type_menu_block == 'terminal') { ?>
            <span id="active-systems" data-system="terminal_systems">��������� ������</span> &nbsp;&nbsp;&nbsp;
            <?php } else { ?>
            <a class="b-layout__link b-layout__link_bold b-layout__link_bordbot_dot_0f71c8" data-system="terminal_systems" href="/bill/payment/?type=qiwi">��������� ������</a> &nbsp;&nbsp;&nbsp;
            <?php }//if?>
            <?php if($bill->type_menu_block == 'card') { ?>
            <span id="active-systems" data-system="card_systems">����������� �����</span> &nbsp;&nbsp;&nbsp;
            <?php } else { ?>
            <a class="b-layout__link b-layout__link_bold b-layout__link_bordbot_dot_0f71c8" data-system="card_systems" href="/bill/payment/?type=card">����������� �����</a> &nbsp;&nbsp;&nbsp;
            <?php }//if?>
            <?php if($bill->type_menu_block == 'bank') { ?>
            <span id="active-systems" data-system="bank_systems">���������� ����</span> &nbsp;&nbsp;&nbsp;
            <?php } else {//if ?>
            <a class="b-layout__link b-layout__link_bold b-layout__link_bordbot_dot_0f71c8" data-system="bank_systems" href="/bill/payment/?type=alphabank">���������� ����</a>
            <?php }//else?>
        </div>

        <? include ($_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/tpl.mobilesys.php"); ?>        
        <? include ($_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/tpl.psys.php"); ?>
        <? include ($_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/tpl.terminal.php"); ?>
        <? include ($_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/tpl.card.php"); ?>
        <? include ($_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/tpl.bank.php"); ?>
        
        <? include ($_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/{$bill->payment_template}"); //@see billing::setPaymentMethod()?>
    </div>
    <div class="b-layout__one b-layout__one_width_25ps b-layout__one_float_left b-layout__one_margleft_3ps b-layout__one_width_full_ipad">
        <?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.right_column.php"); ?>
    </div>
</div>
    