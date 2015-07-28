<?php

/**
 * ����� ������ ������� ��� �� ������ ��������
 * ������� ����� ��� �������, ��� �� ����� �����
 * ���������� xajax �������
 */

$current_uid = get_uid(false);

//�� ���������� ���� ��� �������
if ($current_uid <= 0 || is_pro() || is_emp() || 
   (isset($g_page_id) && in_array($g_page_id, array('0|9', '0|35', '0|26', '0|993'))) || 
   !isAllowTestPro()) {
    
    return;
}


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");

?>
<div class="b-layout b-layout__page">
    <div class="body">
        <div class="main">    
            <div id="header_payed_pro" class="b-page__title b-page__title_center b-page__title_padbot_5">
                ������ ������� ������� PRO �� ����� �� ������� 45%
            </div>
            <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_center b-layout__txt_padbot_20">
                ������� ����������. �������������� ����� �������� �� �������/��������/��������. ������ � ��������/���������/��������� � ������� ��� PRO�.
            </div>
<?php

include_once('plans.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_buy_pro.php");

?>
        </div>
    </div>
</div>