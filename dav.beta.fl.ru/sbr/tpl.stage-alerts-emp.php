<? if($sbr->status == sbr::STATUS_PROCESS && !$sbr->data['reserved_id'] && $sbr->state != 'new') {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
        <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>
        ����������� �� ��������� � ������, ���� �� �� <a class="b-fon__link" href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?site=reserve&id=<?= $sbr->id?>">�������������� ������</a> ��� ������.
	</div>
</div>	
<? } elseif($sbr->status == sbr::STATUS_PROCESS && !$sbr->data['reserved_id'] && $sbr->state == 'new') {
    $cdate = new LocalDateTime($sbr->pskb_created);
    $cdate->getWorkForDay(pskb::PERIOD_RESERVED);
    $pskb_created = $cdate->getTimestamp();
?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
        <span class="b-icon b-icon_sbr_gattent b-icon_margleft_-20"></span>
        ��� ���������� ��������������� ������ �� ������ �� <?= date('d', $pskb_created)?> <?= monthtostr(date('n', $pskb_created), true)?> <?= date('Y', $pskb_created)?>. � ��������� ������ ������ ����� �������� (�������� ������� 4.3 � 15.8 <a class="b-layout__link" href="<?= $sbr->getDocumentLink('contract') ?>">��������</a>).
	</div>
</div>	
<? }//elseif?>

<? /*
 * 
 * <? if($sbr->scheme_type == sbr::SCHEME_LC) { ?>
        ��� ���������� <a href="/sbr/?site=reserve&id=<?= $sbr->id?>">��������������� ������</a> �� ������ �� 16 �������� 2012, 10:09. � ��������� ������ ������ ����� ��������.
        <? } else { //if?>
 * if($stage->status == sbr_stages::STATUS_PROCESS && $sbr->data['reserved_id']) {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
			<span class="b-icon b-icon_sbr_brur b-icon_margleft_-20"></span>�������� �������������� ������ �� ������, ����� ���������� � ������. ����� :)
	</div>
</div>	
<? } */?>

<? if($stage->status == sbr_stages::STATUS_INARBITRAGE) {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
	<span class="b-icon b-icon_sbr_avesy b-icon_margleft_-20"></span>
        ������� ����� �������� �� <?= $stage->getStrOvertimeArbitrage()?>, ����� ���� ���� ����� ��������. ����������� �������� ��������� ��� ������������� �������� �� �������� �������������� � ������ ��������. � ������, ���� �������������� �� ����� ����������, �������� ������ ���� �� �������, ��������� � ������ 9.9 <a class="b-layout__link" href="<?= $sbr->getDocumentLink('contract'); ?>">��������</a>.
        </div>
</div>	
<? } ?>

<? if($stage->status == sbr_stages::STATUS_COMPLETED && !$stage->data['emp_feedback_id']) {?>
<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
        <? if ($sbr->status == sbr::STATUS_COMPLETED) { ?>
        <span class="b-icon b-icon_sbr_gattent b-icon_margleft_-20"></span>���� �� ����� ������ �� ��� ���, ���� �� �� �������� ����� ����������� � ����� ������� ����������� ������.
        <? } else { ?>
        <span class="b-icon b-icon_sbr_gattent b-icon_margleft_-20"></span>���� �� ����� ������ �� ��� ���, ���� �� �� �������� ����� �����������.
        <? } ?>
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
                �������� ������ ������� � ������� 100% ������� �����������
            <? } elseif ($frlPercent === (float)0) { ?>
                �������� ������ ������� � ����������� ��� 100% �������
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

