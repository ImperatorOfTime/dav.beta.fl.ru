<a name="page"></a>

<?php /* if($sbr->isEmp()) {?>
<a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?site=<?= $projects_cnt['open'] == 0 ? 'create' : 'new';?>" class="b-button b-button_flat b-button_flat_green b-button_float_right">������ ����� ������</a>
<?php }//if */?>
<h1 class="b-page__title">��� ������</h1>

<? 
// ���� ������
include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.help.php");
// ����� ���
include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.header.php");
// ������ ���
include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.sbr-content.php");
?>
<?php if($count_sbr > 0 && ( $filter == '' || $filter == 'complete') ) {?>
<div class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_fontsize_22 b-layout__txt_padtop_30 b-layout__txt_padbot_20" id="button_load_currents">
    <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" id="load_link" href="javascript:void(0)" onclick="xajax_loadCurrents('<?=$filter?>', <?= $now_count?>)"><?= ( $sbr_currents ? "��� " : "" )?><?= $count_sbr?> <?= ending($count_sbr, "����������� ������", "����������� ������", "����������� ������")?></a>
    <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout_hide" id="show_link" href="javascript:void(0)" onclick="toggle_currents(this);">�������� ����������� ������</a>
</div>
<?php } else {//if?>
<span id="button_load_currents">&nbsp;</span>
<?php } ?>
