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
?>
<div class="body">
    <div class="main">
        <div class="norisk" style="border:0px">
            <div class="tabs-in nr-tabs-in">
            <?include ($included);?>
            </div>
        </div>
    </div>
</div>