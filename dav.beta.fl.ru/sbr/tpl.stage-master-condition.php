<script type="text/javascript">
    var WMR_SYS = <?= exrates::WMR;?>;
    var YM_SYS = <?= exrates::YM;?>;
    var BANK_SYS = <?= exrates::BANK;?>;
    var FM_SYS = <?= exrates::FM;?>;
    
    var TAX_WM = <?= ($sbr->cost * 0.03);?>;
    var TAX_YM = <?= ($sbr->cost * 0.03);?>;
</script>
<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
    <tr class="b-layout__tr">
        <td class="b-layout__td">
            <h2 class="b-layout__title b-layout__title_padbot_15 ">������� ����� ������</h2>
            <div class="b-layout">
                <div class="b-layout__txt b-layout__txt_color_a0763b b-layout__txt_padbot_30 b-layout__txt_padleft_20 b-layout__txt_width_72ps">
                    <span class="b-icon b-icon_top_2 b-icon_margleft_-20 b-icon_sbr_oattent"></span>
                    <? if($sbr->data['scheme_type'] == sbr::SCHEME_LC) { ?>
                    �������� ����� ��������� � ���� ������� � ������������� ������ ��������. ��������, �� ����� �������� �� �������� ��������. ��� ������ �� ������ ������ ��������� ��������������.
                    <? } elseif($sbr->data['scheme_type'] == sbr::SCHEME_PDRD2) {//if?>
                    �������� ����� ��������� � ���� ������� �������. ��������, �� ����� �������� �� �������� ��������.
                    <? }//elseif?>
                </div>

                <? if ($sbr->scheme_type == sbr::SCHEME_LC) include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.pskb-psys.php"); ?>
                <? if ($sbr->scheme_type == sbr::SCHEME_PDRD2) include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.pdrd-psys.php"); ?>
                
                <?// $sbr->getScheme(); ?>
                <? if ($sbr->scheme_type == sbr::SCHEME_LC) { ?>             
                    <div class="b-layout__txt b-layout__txt_color_a0763b b-layout__txt_padbot_10 b-layout__txt_padleft_20 b-layout__txt_width_72ps"><span class="b-icon b-icon_top_2 b-icon_margleft_-20 b-icon_sbr_oattent"></span>���������� � ������ ����������� �������� ����� ������� ������ ������������ �� ������, �� ���������� ���������� � ���������� ������ �/��� �������� ������ � ������������� ������ �������� ��� ������������� ������ ������� ����������� ������. ����� ���������� ���������� �� ����� Free-lance.ru � ���� �������� �� ������: <a class="b-layout__link" href="/agreement_lc.pdf" target="_blank"><?=HTTP_PREFIX?>www.free-lance.ru/agreement_lc.pdf</a>.</div>
                    <div class="b-layout__txt b-layout__txt_color_a0763b b-layout__txt_padleft_20 b-layout__txt_width_72ps">��������� ���� Free-lance.ru (��� �����) � ��� ������ ���������� ������ �� ���������� �������� �� ������������� ����������� �������� ��� ������������� ������ ������� ����������� ������ � ����� �������� �� �����������. ����� ������ �� ���������� �������� �� ������������� ������ ������� ����������� ������ ���������� �� ����� Free-lance.ru � ���� �������� �� ������: <a class="b-layout__link" href="/offer_lc.pdf" target="_blank"><?=HTTP_PREFIX?>www.free-lance.ru/offer_lc.pdf</a>. ������� ������ ������������ �� ������, �� ���������� ��� �����-���� ������� � ����������� ������� ������ �� ���������� �������� �� ������������� ����������� �������� ��� ������������� ������ ������� ����������� ������ � ����� �������� �� �����������.</div>				 
                <? }//if?>
            </div>
            <div class="b-buttons b-buttons_padtop_40">
            
                    <div class="b-check b-check_padbot_20">
                       <input id="sbr_agree_frl" class="b-check__input" type="checkbox" />
                        <? if ($_SESSION['sex'] === null) {
                            $agreeText = '� ��������� �������� ����������(-�) � ��������(-���)';
                        } else {
                            $agreeText = '� ��������� �������� ����������' . ($_SESSION['sex'] === 't' ? '' : '�') . ' � ������' . ($_SESSION['sex'] === 't' ? '��' : '��');
                        } ?>
                        <label for="sbr_agree_frl" class="b-check__label b-check__label_fontsize_13"><?= $agreeText ?> &#160; &#160;<i class="b-icon b-icon_attach_pdf b-icon_pad_null"></i> <a class="b-layout__link" target="_blank" href="/offer_lc.pdf">������� �����������.pdf</a></label>
                    </div>
            
            
            
                <?
                // ����� �� �������������� ������
                $disableButton = false;
                // ���� �� ��������� ������ � ��������
                if (!$isReqvsFilled[$sbr->user_reqvs['form_type']] && $sbr->scheme_type == sbr::SCHEME_PDRD2) {
                    $disableButton = true;
                }
                // ���� �� ������� ������������ ��� �������� �� ��������� ������������� ����������� ��
//                if (!$sbr->user_reqvs['rez_type']) {
//                    $disableButton = true;
//                }
                // ���� �� ������� ����� �����������
                if(!$sbr->user_reqvs['form_type']) {
                    $disableButton - true;
                } 
                ?>
                <a class="b-button b-button_flat b-button_flat_green b-button_disabled <?//= ($disableButton ? "b-button_disabled" : "")?>" id="agree_btn" href="javascript:void(0)" onclick="if(!$(this).hasClass('b-button_disabled'))submitForm(document.getElementById('currentsFrm<?= $sbr->id;?>'),{ok:1})">����������� �� ������
                            <img width="26" height="6" alt="" src="/css/block/b-button/b-button__load.gif" class="b-button__load b-layout_hide"></a>
                <span class="b-buttons__txt b-buttons__txt_padleft_10">���</span> <a href="javascript:void(0)" onclick="$('rrbox<?=$sbr->data['id']?>').toggleClass('b-shadow_hide'); return false;"  class="b-buttons__link b-buttons__link_dot_c10601">����������</a>	
            </div>
        </td>
    </tr>
</table>
<?
$frlReqvs = sbr_meta::getUserReqvs($sbr->data['emp_id']);
?>
<script type="text/javascript">
    var finance = new Finance({form_type: '<?=$sbr->user_reqvs['form_type']?>'});
    <? if(!$sbr->is_diff_method) { ?>
    var taxes   = new Taxes({
        'cost' :        '<?= $sbr->cost;?>',
        'rating':       '<?= $RT ?>',
        'schemes_jury' : <?= $sbr_schemes_jury; ?>,
        'schemes_phys' : <?= $sbr_schemes_phys; ?>,
        'scheme_type':  '<?= $sbr->data['scheme_type'];?>',
        'form_type':    '<?= $sbr->user_reqvs['form_type']?>'
    });
    <? } //if?>
    
    var sbrDisableButton = <?= (int)$disableButton ?>;

    var _SBR = {};
    _SBR.cost = '<?= $sbr->cost ?>',// ������ ������
    _SBR.maxcost = <?= $sbr->maxNorezCost()?>, // ������������ ������ ��� ������ � ������������(���)
    _SBR.maxcostPhys = <?= (int)$sbr->usd2rur(sbr::MAX_COST_USD_FIZ) ?>, // ������������ ������ ��� ������ � ������������� ����� ��� ���������� ����
    _SBR.empFormType = <?= (int)$frlReqvs['form_type'] ?>, // ����������� ����� ���������
    _SBR.empNotRes = <?= (int)($frlReqvs['rez_type'] == sbr::RT_UABYKZ) ?>, // ������������ ���������
    _SBR.frlFormType = <?= (int)$sbr->frl_reqvs['form_type'] ?>, // ����������� ����� �����������
    _SBR.frlNotRes = <?= (int)($sbr->frl_reqvs['rez_type'] == sbr::RT_UABYKZ) ?>; // ������������ �����������
    
</script>