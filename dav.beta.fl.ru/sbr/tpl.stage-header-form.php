<?
$reqv = sbr_meta::getUserReqvs(get_uid(false));
?>
<? if ($stage->data['lc_state'] && $stage->sbr->scheme_type == sbr::SCHEME_LC && $stage->status != sbr_stages::STATUS_�LOSED) { ?>
    <? if ($stage->sbr->isFrl() && $stage->data['lc_state'] == pskb::STATE_PASSED && $stage->data[$sbr->upfx . 'feedback_id']) {
        include ($_SERVER['DOCUMENT_ROOT'].'/sbr/tpl.pskb-stage-header-sms.php');
    } ?>
    <? if ($stage->data['lc_state'] != pskb::STATE_PASSED && ($stage->sbr->isFrl() || ($stage->sbr->isEmp() && $stage->arbitrage['frl_percent'] <1 ))) { ?>
    <div class="b-layout b-layout_padtop_10 b-layout_padbot_15">
        <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_padleft_35 b-layout__left_padright_20">&nbsp;</td>
                <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                    <?= pskb::$state_messages[$stage->data['lc_state']] ?> 
                    <? if ($stage->data['lc_state'] == pskb::PAYOUT_END || $stage->data['lc_state'] == pskb::STATE_COVER) { ?>
                    <?= date('d.m.Y � H:i', strtotime($stage->data['lc_date'])) . '.' ?>
                    <? } ?>
                    <? if ( ($stage->sbr->data['ps_frl'] == onlinedengi::BANK_FL || $stage->sbr->data['ps_frl'] == onlinedengi::BANK_YL) &&
                            ($stage->data['lc_state'] == pskb::PAYOUT_END || $stage->data['lc_state'] == pskb::STATE_COVER || $stage->data['lc_state'] == pskb::STATE_TRANS)
                            ) { ?>
                    <?= ' �������� �������� �� ��� ���� � ������� 3-� ������� ����.' ?>
                    <? } ?>
                </td>
            </tr>
        </table>
    </div>
    <? } ?>
<? } ?>
<? if($stage->isAccessComplete()) { ?>

<form action="?site=Stage&id=<?= $stage->data['id'] ?>&event=complete" method="post" id="completeFrm">
    <input type="hidden"  name="feedback[ops_type]" value="<?= $ops_type !== null ? $ops_type : '' ?>" id="ops_type">
    <input type="hidden" name="site" value="<?= $site ?>" />
    <input type="hidden" name="id" value="<?= $stage->id ?>" />
    <?php
    if( ($sbr->isFrl() && $stage->getPayoutSum(sbr::FRL) > 0) ||  ($sbr->isEmp() && $stage->getPayoutSum(sbr::EMP) > 0)) {
        if($sbr->isEmp()) {
            foreach($EXRATE_CODES as $ex_code=>$ex_name) {
                if(!$stage->checkPayoutSys($ex_code)) continue;
                $stage->type_payment = $ex_code;
            }
        }
        ?>
        <input type="hidden" name="credit_sys" value="<?= ($stage->type_payment ? $stage->type_payment : exrates::BANK );?>">
    <?php }//if?>
    <input type="hidden" name="status" value="<?= sbr_stages::STATUS_COMPLETED?>" />
    <input type="hidden" name="action" value="change_status" />

    <div class="b-layout b-layout_padtop_7 b-layout_bordbot_dedfe0 b-layout_marglr_15">
        <div class="b-layout__inner b-layout__inner_padbot_20 b-layout__inner_marglr_-15">

            <? if ($stage->error['feedback']['ops_type']) { ?>
            <table class="b-layout__table b-layout__table_width_full"  cellpadding="0" cellspacing="0" border="0">
                <tr class="b-layout__tr">
                    <td class="b-layout__left">&#160;</td>
                    <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                        <div id="feedback_ops_type_error" class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                            <span class="b-form__error"></span> �������� ��� ������
                        </div>
                    </td>
                </tr>
            </table>
            <? }//if?>
            <table class="b-layout__table b-layout__table_width_full"  cellpadding="0" cellspacing="0" border="0">
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padleft_35 b-layout__left_padright_20">
                        <div class="b-layout__txt">��� ����� <?= $sbr->isEmp() ? '�����������' : '���������' ?> �� ������ �������������� �� �����</div>
                    </td>
                    <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                        <div class="b-estimate b-estimate_padbot_15">
                            <div class="b-estimate__item b-estimate__item_green b-estimate__item_margright_20 <?= ($ops_type == 1 && isset($_POST['feedback']['ops_type']) ? 'b-estimate__item_active' : ''); ?>">
                                <span class="b-estimate__left"><span class="b-estimate__right"><a class="b-estimate__link " href="javascript:void(0)" onclick="$('ops_type').set('value', '1'); sbr_check_enter_sms_code(); if($('feedback_ops_type_error')) $('feedback_ops_type_error').dispose();"><span class="b-button b-button_margtop_1 b-button_margright_5 b-button_float_left b-button_poll_plus"></span>�������������</a></span></span>
                            </div>
                            <div class="b-estimate__item b-estimate__item_grey b-estimate__item_margright_20 <?= ($ops_type == 0 && $ops_type !== null && isset($_POST['feedback']['ops_type']) ? 'b-estimate__item_active' : ''); ?>">
                                <span class="b-estimate__left"><span class="b-estimate__right"><a class="b-estimate__link " href="javascript:void(0)" onclick="$('ops_type').set('value', '0'); sbr_check_enter_sms_code(); if($('feedback_ops_type_error'))$('feedback_ops_type_error').dispose();"><span class="b-button b-button_margright_5 b-button_float_left b-button_poll_multi"></span>�����������</a></span></span>
                            </div>
                            <div class="b-estimate__item b-estimate__item_red b-estimate__item_margright_20 <?= ($ops_type == -1 && isset($_POST['feedback']['ops_type']) ? 'b-estimate__item_active' : ''); ?>">
                                <span class="b-estimate__left"><span class="b-estimate__right"><a class="b-estimate__link " href="javascript:void(0)" onclick="$('ops_type').set('value', '-1'); sbr_check_enter_sms_code(); if($('feedback_ops_type_error')) $('feedback_ops_type_error').dispose();"><span class="b-button b-button_margright_5 b-button_float_left b-button_poll_minus"></span>�������������</a></span></span>
                            </div>
                        </div>

                        <div class="b-textarea <?= $stage->error['feedback']['descr'] ? 'b-textarea_error' : '' ?>">
                            <textarea id="sbr_feedback_text" class="b-textarea__textarea b-textarea__textarea_height_100 tawl" name="feedback[descr]" rel="<?= sbr_meta::FEEDBACK_MAX_LENGTH ?>" cols="" rows="" onfocus="$(this).getParent('.b-textarea').removeClass('b-textarea_error'); if($('feedback_descr_error')) $('feedback_descr_error').dispose();" onkeydown="sbr_check_enter_sms_code();" onkeyup="sbr_check_enter_sms_code();"><?= $_POST['feedback']['descr'] ? $_POST['feedback']['descr'] : $stage->feedback['descr'] ?></textarea>
                        </div>
                        <? if ($stage->error['feedback']['descr']) { ?>
                        <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padtop_10" id="feedback_descr_error">
                            <span class="b-form__error"></span> ����������, �������� �����
                        </div>
                        <? }//if?>
                        <? if ($stage->error['credit_sys']) { ?>
                        <? foreach ($stage->error['credit_sys'] as $key => $name) { ?>
                            <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padtop_10" id="sbr_descr_error">
                                <span class="b-form__error"></span><?= $name ?>
                            </div>
                            <? }//foreach?>
                        <? }//if?>
                        <? if ( $sbr->data['stages_cnt'] == ($stage->data['num'] + 1) ) { // ��������� ���� ?>
                        <div class="b-layout__txt b-layout__txt_padtop_15"><a class="b-layout__link b-layout__link_color_0f71c8 b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="$('_sbr_feedback').removeClass('b-layout__txt_hide'); $(this).getParent().hide();">�������� ����� � ������� ���������� ������</a></div>
                        <? } //if ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <? if ( $sbr->data['stages_cnt'] == ($stage->data['num'] + 1) ) { // ��������� ���� ?>
    <div class="b-layout b-layout_padtop_20 b-layout_bordbot_dedfe0 b-layout_marglr_15 b-layout__txt_hide" id="_sbr_feedback">
        <div class="b-layout__inner b-layout__inner_padbot_20 b-layout__inner_marglr_-15">
            <table class="b-layout__table b-layout__table_width_full"  cellpadding="0" cellspacing="0" border="0">
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padleft_35 b-layout__left_padright_20">
                        <div class="b-layout__txt">����� ������� ����������� ������</div>
                    </td>
                    <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                        <div class="b-textarea <?= $stage->error['sbr_feedback'] || $stage->sbr->error['feedback'] ? 'b-textarea_error' : '' ?>">
                            <textarea class="b-textarea__textarea b-textarea__textarea_height_100 tawl" name="sbr_feedback[descr]" rel="<?= sbr_meta::FEEDBACK_MAX_LENGTH ?>" cols="" rows="" onfocus="$(this).getParent('.b-textarea').removeClass('b-textarea_error'); if($('sbr_feedback_descr_error')) $('sbr_feedback_descr_error').dispose();"><?= $sbr->feedback['descr'] ?></textarea>
                        </div>
                        <? if ($stage->error['sbr_feedback'] || $stage->sbr->error['feedback']) { ?>
                        <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padtop_10" id="sbr_feedback_descr_error">
                            <span class="b-form__error"></span> ����������, �������� �����
                        </div>
                        <? }//if?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <? }//if ?>

    <div class="b-layout b-layout_padtop_7 b-layout_bordbot_dedfe0 b-layout_marglr_15">
        <div class="b-layout__inner b-layout__inner_padbot_20 b-layout__inner_marglr_-15">
            <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
                <tbody>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_padleft_35 b-layout__left_padright_20">
                            <div class="b-layout__txt">��� �������������</div>
                        </td>
                        <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                            <div class="b-layout__txt b-layout__txt_padbot_10">�� ����� <?=$reqv[$reqv['form_type']]['mob_phone']?> � ������� 5 ����� ������ ��� �������������. ������� ���������� ���, ����� ��������� ���� ������.</div>
                            <div class="b-combo">
                                <div class="b-combo__input b-combo__input_width_110 b-combo__input_height_60 <?=($stage->error['feedback']['sms']==1 ? 'b-combo__input_error' : '')?>">
                                    <input type="text" value="" name="sbr_sms_code" class="b-combo__input-text b-combo__input-text_center" id="sbr_sms_code" onkeydown="sbr_check_enter_sms_code();" onkeyup="sbr_check_enter_sms_code();" onkeypress="sbr_check_num_only(event);">
                                </div>

                                <div class="b-shadow b-shadow_m b-shadow_left_120 <?=($stage->error['feedback']['sms']==1 ? '' : 'b-shadow_hide')?>">
                                    <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
                                        <div class="b-layout__txt b-layout__txt_nowrap b-layout__txt_padright_15 b-layout__txt_color_c4271f"><span class="b-form__error"></span>�������� ���. ���������� ��� ���</div>
                                    </div>
                                    <span class="b-shadow__icon b-shadow__icon_close b-shadow__icon_right_12 b-shadow__icon_top_12"></span> <span class="b-shadow__icon b-shadow__icon_nosik-left b-shadow__icon_top_10 b-shadow__icon_left_-4"></span> 
                                </div>

                            </div>                                                             
                            <div id="sbr_send_sms_link" class="b-layout__txt b-layout__txt_padtop_5"><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="" onClick="xajax_sendFeedbackSMSCode(); return false;">������� ��� ��������</a></div>
                            <div id="sbr_send_sms_link_disabled" class="b-layout__txt b-layout__txt_padtop_5 b-layout_hide">��� ���������</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <script type="text/javascript">xajax_sendFeedbackSMSCode();</script>

    <div class="b-layout b-layout_marglr_15">
        <div class="b-layout__inner b-layout__inner_padbot_20 b-layout__inner_marglr_-15">
            <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                <tbody>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padleft_35 b-layout__left_padright_20">
                    </td>
                    <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                        <div class="b-layout__txt b-layout__txt_padtop_15 b-layout__txt_padbot_20 b-layout__txt_color_a0763b">
                            �������� ��������: �������� �������� ����� ����������.<br>����������� ������� ������ �� ������ ���� ���������� ������.
                        </div>
                        <div class="b-buttons">
                            <a id="sbr_btn" href="javascript:void(0)" onclick="$('completeFrm').submit();" class="b-button b-button_flat b-button_flat_green b-button_disabled">>������� ������ � ��������� <?= $sbr->data['stages_cnt'] == ($stage->data['num'] + 1) ? '������' : '����' ?></a>
                            <span class="b-buttons__txt b-buttons__txt_padleft_10">���</span>
                            <a class="b-buttons__link b-buttons__link_dot_c10601" href="/bezopasnaya-sdelka/?site=Stage&id=<?= $stage->id; ?>">������� ���� � ������</a>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</form>
<div class="i-shadow i-shadow_zindex_110" id="completed_confirm">
    <div class="b-shadow b-shadow_hide b-shadow_center" >
        <div class="b-shadow__right">
            <div class="b-shadow__left">
                <div class="b-shadow__top">
                    <div class="b-shadow__bottom">
                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20 b-layout">
                            <div class="b-shadow__txt b-shadow__txt_padbot_5">�� �������, ��� ������ ������� ������?</div>
                            <div class="b-shadow__txt b-shadow__txt_padbot_10">�������� ��������: �������� �������� ����� ����������.<br />����������� ������� ������ �� ������ ���� ����������� ������.</div>
                            <div class="b-buttons ">
                                <a href="javascript:void(0)" onclick="$('completeFrm').submit();" class="b-button b-button_flat b-button_flat_green">������� ������</a>
                                <span class="b-buttons__txt">&#160;&#160;&#160;���</span>
                                <a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript:void(0)" onclick="$('completed_confirm').getElement('.b-shadow').addClass('b-shadow_hide'); $('b-shadow_sbr__overlay').dispose(); return false;">��������</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-shadow__tl"></div>
        <div class="b-shadow__tr"></div>
        <div class="b-shadow__bl"></div>
        <div class="b-shadow__br"></div>
        <a href="javascript:void(0);" onclick="$('completed_confirm').getElement('.b-shadow').addClass('b-shadow_hide'); $('b-shadow_sbr__overlay').dispose(); return false;"><span class="b-shadow__icon b-shadow__icon_close"></span></a>
    </div>
</div>

<? } elseif ($stage->isTransferMoneyCompleted() && !$stage->data['lc_state'] && !$sbr->isEmp()) { ?>
    <div class="b-fon__txt b-fon__txt_pad_15_15_15_35 b-fon__txt_color_a0763b">
        <? $add_txt = ( ( $sbr->isEmp() ?  ( pskb::$exrates_map[$sbr->data['ps_emp']] == exrates::CARD  ) : ($stage->data['type_payment'] == exrates::CARD) )  ? "���� " : "��� " );?>
        � ������� ��� ������ ����� ���������� �� <?= sbr_meta::view_type_payment( $sbr->isEmp() ? pskb::$exrates_map[$sbr->data['ps_emp']] : $stage->data['type_payment'], $add_txt)?>. 
        <? if($stage->type_payment == exrates::BANK && $sbr->scheme_type == sbr::SCHEME_PDRD2) { ?>����� ������� ����� �� ��� ���� � �� 5 �����.<? }//if?>
        ���� �� ��������� ����� ������� �� ��� ��� �� �������� ������, <a class="b-fon__link b-fon__link_bordbot_dot_0f71c8" href="/about/feedback/">���������� � ������ ���������</a>.
    </div>
<? } elseif ( !($sbr->isEmp() && $sbr->data['emp_feedback_id'] > 0) && $stage->isStageCompleted() && !$stage->data[$sbr->upfx . 'feedback_id'] && !($sbr->isAdmin() || $sbr->isAdminFinance()) ) {//if?>
    
    <? if ($stage->status == sbr_stages::STATUS_ARBITRAGED) { ?>
    <div class="b-layout b-layout_padtop_10 b-layout_bordbot_dedfe0">
        <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_padleft_35 b-layout__left_padright_20" rowspan="3">
                    <img class="b-layout__pic b-layout__pic_float_left b-layout__pic_margtop_10" src="/images/temp/arbitration.png" alt="" width="50" height="50" />
                </td>
                <td class="b-layout__middle b-layout__middle_padright_15 b-layout__middle_width_210">
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_padtop_2">����������� ���������</div>
                </td>
                <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_15"><?= reformat($stage->arbitrage['descr_arb'], 40, 0, 0, 1) ?></div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__middle b-layout__middle_padright_15 b-layout__middle_width_210">
                    <div class="b-layout__txt b-layout__txt_padbot_10">��������� ������</div>
                </td>
                <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_color_a0763b"><span class="b-post__bold">��������� ������� <?= 100 * (1 - $stage->arbitrage['frl_percent']) ?>%</span> ������� �������, <?= sbr_meta::view_cost($stage->getPayoutSum(sbr::EMP), $stage->sbr->cost_sys) ?></div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_color_a0763b"><span class="b-post__bold">����������� ��������� <?= 100 * $stage->arbitrage['frl_percent'] ?>%</span> ������� �������, <?= sbr_meta::view_cost($stage->getPayoutSum(sbr::FRL), $stage->sbr->cost_sys) ?></div>
                </td>
            </tr>
            <? /* @todo #0018802 */?>
            <tr class="b-layout__tr">
                <td class="b-layout__middle b-layout__middle_padright_15 b-layout__middle_width_210">
                    <div class="b-layout__txt b-layout__txt_padbot_10">�������</div>
                </td>
                <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_color_a0763b"><?= $stage->arbitrage['result'] == '' ? '����������� ��������' : reformat(str_replace(array('e%', 'f%'), array((100 * (1 - $stage->arbitrage['frl_percent'])) . "%", ( 100 * $stage->arbitrage['frl_percent']) . "%" ), $stage->arbitrage['result']), 40, 0, 0, 1) ?></div>
                </td>
            </tr>
        </table>
    </div>
    <? }//if?>

    <?php 
    if(!$isReqvsFilled[$sbr->user_reqvs['form_type']] || !$stage->checkPayoutReqvs(($stage->type_payment ? $stage->type_payment : exrates::BANK ))) { 
       // sbr_meta::view_finance_popup("/sbr/?site=Stage&id={$stage->id}");
    }
    ?>

    <form action="?site=Stage&id=<?= $stage->data['id'] ?>" method="post" id="completeFrm">
        <input type="hidden"  name="feedback[ops_type]" value="<?= $ops_type !== null ? $ops_type : '' ?>" id="ops_type">
        <input type="hidden" name="site" value="<?= $site ?>" />
        <input type="hidden" name="id" value="<?= $stage->id ?>" />
        <?php 
        if( ($sbr->isFrl() && $stage->getPayoutSum(sbr::FRL) > 0) ||  ($sbr->isEmp() && $stage->getPayoutSum(sbr::EMP) > 0)) {
            if($sbr->isEmp()) { 
                foreach($EXRATE_CODES as $ex_code=>$ex_name) {
                    if(!$stage->checkPayoutSys($ex_code)) continue;
                    $stage->type_payment = $ex_code;
                }
            }
        ?>
        <input type="hidden" name="credit_sys" value="<?= ($stage->type_payment ? $stage->type_payment : exrates::BANK );?>">
        <?php }//if?>
        <input type="hidden" name="action" value="complete" />
        <?php if(
                $stage->isAccessOldFeedback() &&

             ( !($sbr->isFrl() && $stage->arbitrage['id'] > 0 && strtotime($stage->arbitrage['resolved']) <= strtotime(date('2013-09-17 00:00')) && $stage->arbitrage['frl_percent'] == 0) && // ��� ������ ����������, ����� ����� ������ ����� �����-���
               !($sbr->isEmp() && $stage->arbitrage['id'] > 0 && strtotime($stage->arbitrage['resolved']) <= strtotime(date('2013-09-17 00:00')) &&  $stage->arbitrage['frl_percent'] == 1) ) // ��� ������ ����������, ����� ����� ������ ����� �����-���
                &&
             ( !($stage->arbitrage['id'] > 0 && ($stage->arbitrage['result_id'] == 1) ) && !($sbr->isFrl() && $stage->arbitrage['id'] > 0 && ($stage->arbitrage['result_id'] == 5 || $stage->arbitrage['result_id'] == 6)) && !($sbr->isEmp() && $stage->arbitrage['id'] > 0 && $stage->arbitrage['result_id'] == 7) ) ) { ?>
        <div class="b-layout b-layout_padtop_7 b-layout_bordbot_dedfe0 b-layout_marglr_15">
            <div class="b-layout__inner b-layout__inner_padbot_20 b-layout__inner_marglr_-15">
                <? if ($stage->error['feedback']['ops_type']) { ?>
                <table class="b-layout__table b-layout__table_width_full"  cellpadding="0" cellspacing="0" border="0">
                    <tr class="b-layout__tr">
                        <td class="b-layout__left">&#160;</td>
                        <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                            <div id="feedback_ops_type_error" class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                                <span class="b-form__error"></span> �������� ��� ������
                            </div>
                		</td>
                     </tr>
                </table>
                <? }//if?>
                
                <? if ($sbr->scheme_type != sbr::SCHEME_LC) { // ��� ����������� �� ��������� ?>
                <? if(!$stage->checkPayoutReqvs(($stage->type_payment ? $stage->type_payment : exrates::BANK )) || !$isReqvsFilled[$sbr->user_reqvs['form_type']]) {
                    $disable_btn = true;?>
                <div class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_padbot_10 b-layout__txt_padleft_50"><span class="b-icon b-icon_top_2 b-icon_margleft_-20 b-icon_sbr_rattent"></span>��� �� ������� ������ �� �������� �<a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 finance-open" href="javascript:void(0)">�������</a>�. ����������, ��������� ��� ����������� ����, ����� �� �� ������� ��������������� �������� ����������� ������.</div>
                <? }//if?>
                
                <? } ?>

                <table class="b-layout__table b-layout__table_width_full"  cellpadding="0" cellspacing="0" border="0">
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_padleft_35 b-layout__left_padright_20">
                            <div class="b-layout__txt">��� ����� <?= $sbr->isEmp() ? '�����������' : '���������' ?> �� ������ �������������� �� �����</div>
                        </td>
                        <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                            <div class="b-estimate b-estimate_padbot_15">
                                <div class="b-estimate__item b-estimate__item_green b-estimate__item_margright_20 <?= ($ops_type == 1 && isset($_POST['feedback']['ops_type']) ? 'b-estimate__item_active' : ''); ?>">
                                    <span class="b-estimate__left"><span class="b-estimate__right"><a class="b-estimate__link " href="javascript:void(0)" onclick="$('ops_type').set('value', '1'); if($('feedback_ops_type_error')) $('feedback_ops_type_error').dispose();"><span class="b-button b-button_margtop_1 b-button_margright_5 b-button_float_left b-button_poll_plus"></span>�������������</a></span></span>
                                </div>
                                <div class="b-estimate__item b-estimate__item_grey b-estimate__item_margright_20 <?= ($ops_type == 0 && $ops_type !== null && isset($_POST['feedback']['ops_type']) ? 'b-estimate__item_active' : ''); ?>">
                                    <span class="b-estimate__left"><span class="b-estimate__right"><a class="b-estimate__link " href="javascript:void(0)" onclick="$('ops_type').set('value', '0'); if($('feedback_ops_type_error'))$('feedback_ops_type_error').dispose();"><span class="b-button b-button_margright_5 b-button_float_left b-button_poll_multi"></span>�����������</a></span></span>
                                </div>
                                <div class="b-estimate__item b-estimate__item_red b-estimate__item_margright_20 <?= ($ops_type == -1 && isset($_POST['feedback']['ops_type']) ? 'b-estimate__item_active' : ''); ?>">
                                    <span class="b-estimate__left"><span class="b-estimate__right"><a class="b-estimate__link " href="javascript:void(0)" onclick="$('ops_type').set('value', '-1'); if($('feedback_ops_type_error')) $('feedback_ops_type_error').dispose();"><span class="b-button b-button_margright_5 b-button_float_left b-button_poll_minus"></span>�������������</a></span></span>
                                </div>
                            </div>

                            <div class="b-textarea <?= $stage->error['feedback']['descr'] ? 'b-textarea_error' : '' ?>">
                                <textarea class="b-textarea__textarea b-textarea__textarea_height_100 tawl" name="feedback[descr]" rel="<?= sbr_meta::FEEDBACK_MAX_LENGTH ?>" cols="" rows="" onfocus="$(this).getParent('.b-textarea').removeClass('b-textarea_error'); if($('feedback_descr_error')) $('feedback_descr_error').dispose();"><?= $stage->feedback['descr'] ?></textarea>
                            </div>
                            <? if ($stage->error['feedback']['descr']) { ?>
                            <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padtop_10" id="feedback_descr_error">
                                <span class="b-form__error"></span> ����������, �������� �����
                            </div>
                            <? }//if?>
                            <? if ($stage->error['credit_sys']) { ?> 
                                <? foreach ($stage->error['credit_sys'] as $key => $name) { ?>
                                    <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padtop_10" id="sbr_descr_error">
                                        <span class="b-form__error"></span><?= $name ?>
                                    </div>
                                <? }//foreach?>
                            <? }//if?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php } else {//if?>
        <div class="b-fon__txt b-fon__txt_pad_15_15_15_35 b-fon__txt_color_a0763b">
            � ���������, ���� ���������� ������ � �������������� ��� �����. ������ �� ������ �������� ����� � ������� ���������� ������ � ������� ����.
        </div>
        <?php }//else?>
        
        <? if ($sbr->status == sbr::STATUS_COMPLETED) { ?>
        <div class="b-layout b-layout_padtop_20 b-layout_bordbot_dedfe0 b-layout_marglr_15">
            <div class="b-layout__inner b-layout__inner_padbot_20 b-layout__inner_marglr_-15">
                <table class="b-layout__table b-layout__table_width_full"  cellpadding="0" cellspacing="0" border="0">
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_padleft_35 b-layout__left_padright_20">
                            <div class="b-layout__txt">����� ������� ����������� ������</div>
                        </td>
                        <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                            <div class="b-textarea <?= $stage->error['sbr_feedback'] || $stage->sbr->error['feedback'] ? 'b-textarea_error' : '' ?>">
                                <textarea class="b-textarea__textarea b-textarea__textarea_height_100 tawl" name="sbr_feedback[descr]" rel="<?= sbr_meta::FEEDBACK_MAX_LENGTH ?>" cols="" rows="" onfocus="$(this).getParent('.b-textarea').removeClass('b-textarea_error'); if($('sbr_feedback_descr_error')) $('sbr_feedback_descr_error').dispose();"><?= $sbr->feedback['descr'] ?></textarea>
                            </div>
                            <? if ($stage->error['sbr_feedback'] || $stage->sbr->error['feedback']) { ?>
                            <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padtop_10" id="sbr_feedback_descr_error">
                                <span class="b-form__error"></span> ����������, �������� �����
                            </div>
                            <? }//if?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <? }//if ?>
        
        <? if ($sbr->scheme_type != sbr::SCHEME_LC && ( ( $stage->getPayoutSum(sbr::FRL) > 0 && $sbr->isFrl() ) || (  $stage->getPayoutSum(sbr::EMP) > 0 && $sbr->isEmp() && $sbr->data['ps_emp'] == onlinedengi::WMR )  ) ) {?>
            <? // @todo ���������� ����� ���������� � �������� ?>
            <? 
            if($sbr->isEmp()) {
                $dvals = array('P' => pskb::$exrates_map[$sbr->data['ps_emp']]);
                $type_payment = pskb::$exrates_map[$sbr->data['ps_emp']];
            } else {
                $dvals = array('P' => $stage->type_payment);
                $type_payment = $stage->type_payment;
            }
            $taxes = $stage->_new_getTaxInfo(NULL, $dvals, null, false); ?>
        <script type="text/javascript">
                SYS_WMR = <?= exrates::WMR; ?>;
                SYS_YM  = <?= exrates::YM; ?>;
                SYS_FM  = <?= exrates::FM; ?>;
                var CURR_SYS  = '<?= $stage->sbr->cost_sys;?>';
                var BUDGET    = '<?= sbr_meta::view_cost($stage->data['cost'], $stage->sbr->cost_sys)?>';
                var BUDGET_FM = '<?= sbr_meta::view_cost(round($stage->data['cost'] / 30, 2), exrates::FM);?>';
                var COST_FM = '<?= sbr_meta::view_cost($stage->total_sum_stagefm, exrates::FM);?>';
            </script>
            <?php if ($stage->total_sum_stage > 0) { ?>
            <div class="b-layout b-layout_padtop_20 b-layout_marglr_15">
                <div class="b-layout__inner b-layout__inner_padbot_20 b-layout__inner_marglr_-15">
                    <table class="b-layout__table b-layout__table_width_full"  cellpadding="0" cellspacing="0" border="0">
                        <tr class="b-layout__tr">
                            <td class="b-layout__left b-layout__left_padleft_35 b-layout__left_padright_20">
                                <div class="b-layout__txt">������ ������ �����</div>
                            </td>
                            <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                                <?/*<div class="b-radio b-radio_layout_vertical">
                                    <?
                                    foreach ($EXRATE_CODES as $ex_code => $ex_name) {
                                        if (!$stage->checkPayoutSys($ex_code))
                                            continue;
                                        $dsbl[$ex_code] = 0;
                                        ?>
                                        <div class="b-radio__item b-radio__item_padbot_5">
                                            <input type="radio" id="ex_<?= $ex_code ?>" class="b-radio__input" name="credit_sys" value="<?= $ex_code ?>" 
                                                    onclick="<?= ($ex_code == exrates::WMR ? "_new_checkWMDoc();" : "_new_clearCheckWMDoc();") ?> changeCostSys(this.value, '<?= round($stage->total_sum_stage * 0.03, 2); ?>',  '<?= sbr_meta::view_cost($stage->total_sum_stage - ($ex_code == exrates::WMR || $ex_code == exrates::YM ? ($stage->total_sum_stage * 0.03) : 0 ), $sbr->cost_sys); ?>');"
                                                    <? if (!$stage->checkPayoutReqvs($ex_code)) {
                                                        echo ' disabled="disabled"';
                                                        $dsbl[$ex_code] = 1;
                                                    } ?>
                                    <?= (!$dsbl[$ex_code] && ($sbr->cost_sys == $ex_code || $stage->request['credit_sys'] == $ex_code) ? ' checked="checked"' : '') ?> />
                                            <label class="b-radio__label b-radio__label_fontsize_13" for="ex_<?= $ex_code ?>"><?= $ex_name[0] ?></label>
                                        </div>
                                    <? } //foreach?>
                                </div>*/?>
                                <?= $taxes ?>
                                <div class="b-layout__txt b-layout__txt_color_a0763b b-layout__txt_padleft_20">
                                    <span class="b-icon b-icon_top_2 b-icon_margleft_-20 b-icon_sbr_oattent"></span>
                                        ����� ��������� ���� ������������ ���� ���� � 2-� ����������� � ������������ ������� �� �������� ������� <?= sbr_meta::view_cost($stage->type_payment != exrates::FM ? $stage->total_sum_stage : $stage->total_sum_stagefm, $stage->type_payment) ?> �� <?= sbr_meta::view_type_payment($type_payment, '���� ');?>, 
                                        <?php if($stage->type_payment != exrates::FM) { ?>
                                        ��������� �� �������� �<a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="/users/<?= $sbr->data[($sbr->isFrl() ? 'frl_' : 'emp_' ) . 'login'] ?>/setup/finance/">�������</a>�.
                                        <?php } else { echo "."; }//if?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php }//if ?>
        <? }//if ?>
        <? if ($sbr->isFrl() && $sbr->scheme_type == sbr::SCHEME_LC && $stage->getPayoutSum(sbr::FRL) > 0) { ?>
            <div class="b-layout b-layout_padtop_20 b-layout_marglr_15">
                <div class="b-layout__inner b-layout__inner_marglr_-15">
                    <table class="b-layout__table b-layout__table_width_full"  cellpadding="0" cellspacing="0" border="0">
                        <tr class="b-layout__tr">
                            <td class="b-layout__left b-layout__left_padleft_35 b-layout__left_padright_20">
                                <div class="b-layout__txt"></div>
                            </td>
                            <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                                <?= $stage->_new_getTaxInfo(NULL, array('P' => pskb::$exrates_map[$stage->data['ps_frl']]), null, false); ?>
                                <div class="b-layout__txt b-layout__txt_color_a0763b b-layout__txt_padleft_20">
                                    <span class="b-icon b-icon_top_2 b-icon_margleft_-20 b-icon_sbr_oattent"></span>����� ������� �� ������ "��������� ����" ��� ������ ��� � ����� ������������� �� ����� <strong><?= pskb::phone($lc['numPerf']) ?></strong>.
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>            
        <? } ?>
        
        <div class="b-layout b-layout_padtop_20 b-layout_marglr_15">
            <div class="b-layout__inner b-layout__inner_padbot_20 b-layout__inner_marglr_-15">
                <table class="b-layout__table b-layout__table_width_full"  cellpadding="0" cellspacing="0" border="0">
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_padleft_35 b-layout__left_padright_20">
                        </td>
                        <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                            <div class="b-buttons">
                                <? if ($sbr->scheme_type != sbr::SCHEME_LC) { ?>
                                <a href="javascript:void(0)" onclick="if(!$(this).hasClass('b-button_disabled')) { submitForm($('completeFrm')); }" class="b-button b-button_flat b-button_flat_green <?= !$isReqvsFilled[$sbr->user_reqvs['form_type']] || $disable_btn ? "b-button_disabled" : ""?>" id="submit_btn">��������� ����</a>
                                <? } else { ?>
                                <a href="javascript:void(0)" onclick="if(!$(this).hasClass('b-button_disabled')) { submitForm($('completeFrm')); }" class="b-button b-button_flat b-button_flat_green" id="submit_btn">��������� ����</a>
                                <? } ?>
                                <? if ($stage->status == sbr_stages::STATUS_ARBITRAGED) { ?>
                                    <span class="b-buttons__txt b-buttons__txt_padleft_10">���</span>
                                    <a class="b-buttons__link b-buttons__link_dot_0f71c8" href="https://feedback.fl.ru/">���������� � ������ ���������</a>
                                <? }//if ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </form>
    
    <?php if(!$isReqvsFilled[$sbr->user_reqvs['form_type']] || !$stage->checkPayoutReqvs(($stage->type_payment ? $stage->type_payment : exrates::BANK ))) { ?>
    <script type="text/javascript">
        var finance = new Finance({form_type: '<?=$sbr->user_reqvs['form_type']?>'});
    </script>
    <?php }?>
<? } elseif($stage->isTransferMoneyCompleted() && $sbr->isEmp()) {
    $type_payment = $sbr->scheme_type == sbr::SCHEME_LC ? ( $sbr->isEmp() ? pskb::$exrates_map[$sbr->data['ps_emp']] : $stage->data['type_payment'] ) : $sbr->cost_sys;
    ?>
    <div class="b-fon__txt b-fon__txt_pad_15_15_15_35 b-fon__txt_color_a0763b">
        <? $add_txt = ( ( $sbr->isEmp() ?  ( pskb::$exrates_map[$sbr->data['ps_emp']] == exrates::CARD  ) : ($stage->data['type_payment'] == exrates::CARD) )  ? "���� " : "��� " );?>
        <? $pskb ?>
        ������ ����� ���������� �� <?= sbr_meta::view_type_payment( $type_payment, $add_txt)?> � ������� 1 �������� ��� ����� �������� �����������. ���� �������� ����������� - <?= date('d.m.Y', strtotime($sbr->data['dateEndLC'])) ?>.
        <? if($stage->type_payment == exrates::BANK && $sbr->scheme_type == sbr::SCHEME_PDRD2) { ?>����� ������� ����� �� ��� ���� � �� 5 �����.<? }//if?>
        ���� �� ��������� ����� ������� �� ��� ��� �� �������� ������, ���������� � <a class="b-fon__link b-fon__link_bordbot_dot_0f71c8" href="/about/feedback/">������ ���������</a>.<br>
        ����� �������� �������� ������������ ����������� ����������� ����� ������ ���, ���������� ��� ����� ����� �� ����� ������� ������ ��� ��� ����� (� ����������� �� ������� ���������). � ���� ������ ������ ����� ���������� ��� � ������� 1 �������� ��� ����� ���������� ������ ������������. ��������� <a class="b-fon__link b-fon__link_bordbot_dot_0f71c8" href="https://feedback.free-lance.ru/article/details/id/1130">�����</a>.
        
    </div>    
<?php }//elseif?>
