<?php

$crumbs = 
array(
    0 => array(
        'href' => '/' . sbr::NEW_TEMPLATE_SBR . '/', 
        'name' => '���� ������'
    ),
    1 => array(
        'href' => '', 
        'name' => '����� ������'
    )
);
$css_selector_crumbs = "b-page__title_padbot_30";
// ������� ������
include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.sbr-crumbs.php"); 
?>

<div class="b-layout__txt b-layout__txt_fontsize_22">&mdash; <a class="b-layout__link" href="/users/<?=$sbr->login?>/setup/projects/">� �������� �������</a></div>
<div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_30 b-layout__txt_padbot_40">
    <?php if($projects_cnt['open'] == 1) {?>
    �� ������ ������ � ��� 1 �������� ������. �� ������ ������ � ��� ����������� ������.
    <?php } else { //if?>
    �������� ����� �� <?= $projects_cnt['open']; ?> �������� ���� �������� � ������� �� ���� ����������� ������.
    <?php } //else?>
</div>
<div class="b-layout__txt b-layout__txt_fontsize_22">&mdash; <a class="b-layout__link" href="?site=create">��� ���������� �������</a></div>
<div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_30 b-layout__txt_padbot_40">���� �� ������������ � ������������, �������� ������, ������� �� ����� ����������� �� ������� ��������, � ����� ��������� �� ���� ����������� ������. </div>
