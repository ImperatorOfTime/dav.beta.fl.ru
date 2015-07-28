<? if($sbr->status == sbr::STATUS_PROCESS && !$sbr->data['reserved_id']) {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
			<span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>�� ��������� ������, ���� �������� �� ������������� ������ ��� ��� ������!
	</div>
</div>	
<? } ?>

<? if($stage->status == sbr_stages::STATUS_PROCESS && $sbr->data['reserved_id']) {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
			<span class="b-icon b-icon_sbr_brur b-icon_margleft_-20"></span>�������� �������������� ������ �� ������, ����� ���������� � ������.
	</div>
</div>	
<? } ?>

<? if($stage->status == sbr_stages::STATUS_COMPLETED && $stage->notification['ntype'] == 'sbr_stages.FRL_FEEDBACK') {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
        <span class="b-icon b-icon_sbr_gattent b-icon_margleft_-20"></span>
        ����� �������� ������������ ������, �� ������ ������� <a class="b-fon__link b-fon__link_bordbot_dot_0f71c8" href="javascript:void(0);" onclick="JSScroll($('head_docs'));">����������� ���������</a>.
	</div>
</div>
<? } ?>

<? if($stage->status == sbr_stages::STATUS_INARBITRAGE) {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
        <span class="b-icon b-icon_sbr_avesy b-icon_margleft_-20"></span>
        ������� ����� �������� �� <?= $stage->getStrOvertimeArbitrage()?>, ����� ���� ���� ����� ��������. ����������� �������� ��������� ��� ������������� �������� �� �������� �������������� � ������ ��������. � ������, ���� �������������� �� ����� ����������, �������� ������ ���� �� �������, ��������� � ������ 9.9 <a class="b-layout__link" href="<?= $sbr->getDocumentLink('contract'); ?>">��������</a>.
        </div>
</div>	
<? } ?>

<? if($stage->status == sbr_stages::STATUS_ARBITRAGED) {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
			<span class="b-icon b-icon_sbr_aok b-icon_margleft_-20"></span>
            <?
            $frlPercent = (float)$stage->arbitrage['frl_percent'];
            $byConsent = $stage->arbitrage['by_consent'] === 't';
            $byAward   = $stage->isByAward();
            if ($frlPercent === (float)1) { ?>
                �������� ������ ������� � ������� ��� 100% �������.
            <? } elseif ($frlPercent === (float)0) { ?>
                �������� ������ ������� � ����������� 100% ������� ���������.
            <? } else { ?>
                �������� �������� ���� ����. 
                <? if($byAward) {?>
                �� ������� ��������� ������ ��� ��������.
                <? } elseif ($byConsent) { ?>
                �� ���������� ������ ������ ��� ��������.
                <? }?>
            <? } ?>
	</div>
</div>	
<? } ?>

<? if($stage->status == sbr_stages::STATUS_COMPLETED && !$stage->data['frl_feedback_id'] && $stage->sbr->scheme_type == sbr::SCHEME_LC) { 
    // $completed_time -- ����� ���������� ������ ������� �� ����� tpl.stage-history-event.php -- ����� ������ ��� �� �������� ���
    $cdate = new LocalDateTime($completed_time);
    $cdate->getWorkForDay(pskb::PERIOD_FRL_EXEC);
    $pskb_created = $cdate->getTimestamp();
    $overtime_completed = strtotime($completed_time . ' + ' . pskb::PERIOD_FRL_EXEC . 'day');?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
        <span class="b-icon b-icon_sbr_gattent b-icon_margleft_-20"></span>
        ����� �������� ������������ ������, ��� ���������� ������ ������ ���������� ���� �� <?= date('d', $overtime_completed)?> <?= monthtostr(date('n', $overtime_completed), true)?> <?= date('Y', $overtime_completed)?>.
	</div>
</div>	
<? }//if?>
