<?php

$current_uid = get_uid(false);

//�� ���������� ���� ��� �������
if ($current_uid <= 0 || !is_emp() || (isset($kind) && ($kind == 4 || $kind == 2)) ||
    (isset($g_page_id) && in_array($g_page_id, array('0|991', '0|992')))) {
    
    return;
}

//���� ���� ������ �� �� ����������
require_once(ABS_PATH . "/classes/projects.php");
if (projects::isExistProjects($current_uid)) {
    return;
}

//�������� ��� ���� ������������
$new_project_button_is_visible = true;

?>
<div class="b-layout b-layout__one_center b-layout_padbot_30">
    <div class="b-page__title b-page__title_center b-page__title_padbot_10_ipad">
        ����������� ������� � �������� ������� �����������
    </div>
    <a class="b-button b-button_flat b-button_inline-block b-button_flat_orange2" href="/public/?step=1&kind=1">
        ��������� ������������ �������
    </a>
</div>