<? $need_payouts = $credit_sum && ($is_arb_outsys || !$sbr->isEmp()) && !$stage->payouts[$sbr->uid]; ?>
<script type="text/javascript">
CKEDITOR.config.customConfig = '/scripts/ckedit/config_nocut.js';
window.addEvent('domready', 
    function() {
//        initWysiwyg();
        var anchor_doc = <?= intval($stage_doc);?>;
        if($('doc_' + anchor_doc)) {
            JSScroll($('doc_'+anchor_doc));
        }
        
        <? if (($stage->status == sbr_stages::STATUS_COMPLETED || $stage->status == sbr_stages::STATUS_ARBITRAGED) && !$stage->data[$sbr->upfx.'feedback_id']) {?>
        window.addEvent('domready', function() { SBR = new Sbr('completeFrm'); });
        <? }//if?>
    }
);
<? if($need_payouts) { ?>
Sbr.prototype.WM_SYS=<?=exrates::WMR?>;    
Sbr.prototype.YM_SYS=<?=exrates::YM?>; 
Sbr.prototype.RUR_SYS=<?=exrates::BANK?>;
Sbr.prototype.EXCODES={<?
$i=0;
foreach($EXRATE_CODES as $exc=>$exn) {
    if(!$stage->checkPayoutSys($exc, $only_reserved_sys)) continue;
    echo ($i++?',':'') . "$exc:['{$exn[1]}'";
    $sum1 = round($stage->getPayoutSum(NULL, $exc, NULL, $exc, FALSE),2);
    $sum2 = $norez_block ? round($stage->getPayoutSum(NULL, $exc, NULL, $exc, TRUE),2) : $sum1;
    echo ",$sum1,$sum2]";
}
?>};
<? } ?>
</script>
<?php 

$crumbs = 
array(
    0 => array(
        'href' => '/' . sbr::NEW_TEMPLATE_SBR . '/', 
        'name' => '���� ������'
    ),
    1 => array(
        'href' => '/' . sbr::NEW_TEMPLATE_SBR . '/?id=' . $sbr->id, 
        'name' => $sbr->data['name']
    ),
    2 => array(
        'href' => '',
        'name' => $stage->data['name'] . ' ' . $sbr->getContractNum()
    )
);
// ������� ������
include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.sbr-crumbs.php"); 

// �������� ��� �����������
include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-user.php");

// ���������� ���� ������ � ����� ���
include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.help.php");

// ����� �����
include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-header.php");

// ������� �� ����
include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-tz.php"); 

// ������� �����
include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-history.php");

// �������������� �� �����
if($sbr->isEmp()) {
    include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-alerts-emp.php");
} elseif($sbr->isFrl()) {
    include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-alerts-frl.php");
}

// ���������������
if($stage->status != sbr_stages::STATUS_ARBITRAGED && $sbr->status != sbr::STATUS_CANCELED && $sbr->status != sbr::STATUS_REFUSED && $sbr->reserved_id && $stage->orders == 'ASC') {
    include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-comment.php");
}
// ����� �����
if($sbr->all_docs) {
    ?><div id="doc_content"><?
    include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-files.php");
    ?></div><?
}
?>