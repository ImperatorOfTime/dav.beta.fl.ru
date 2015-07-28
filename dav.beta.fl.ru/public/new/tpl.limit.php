<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/professions.common.php");
$xajax->printJavascript('/xajax/');

?>
<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">

    <tr class="b-layout__tr">
        <td class="b-layout__td">

            <h1 id="project_title" class="b-page__title">
                ����� ������
            </h1>

            <div class="b-layout__txt b-layout__txt_padbot_20">
                �� ��� ��������� ����� �� ���������� ����������� �������� � �����.<br/>
                ��������� ������ �� ������ ������������ ������ (<a href="/payed-emp/">����� ������� ���</a>) ��� ����� <?php echo $last_prj_date ?>.
            </div>
            
            <a title="������ PRO" href="/payed/"  class="b-button b-button_flat b-button_flat_green">
                ������ PRO
            </a>
            
        </td>
        <td class="b-layout__td b-layout__td_width_340 b-layout__td_padleft_20 b-layout__td_padtop_10">
           <div class="b-layout__title">�� ����� ������</div>
           <? if ($project['kind'] == 7) { ?>
           <div class="b-layout b-layout_pad_20 b-layout_2bord_e6 b-layout_bordrad_3 b-layout_margbot_10">
              <table class="b-layout__table b-layout__table_width_full">
                 <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_60 b-layout__td_center"><img class="b-pic" src="/images/project-logo.png" width="46" height="62"></td>
                    <td class="b-layout__td b-layout__td_padleft_20">
                       <div class="b-layout__txt b-layout__txt_padbot_20">�������� ������, ���� ��� ����� ������-����������� ��� �������� ���������� �����-���� ������ �� ����� �������.</div>
                       <a class="b-button b-button_flat b-button_flat_green" href="?step=1&kind=1">�������� ������</a>
                    </td>
                 </tr>
              </table>
           </div>
           <? } else {//if?>
           <div class="b-layout b-layout_pad_20 b-layout_2bord_e6 b-layout_bordrad_3 b-layout_margbot_10">
              <table class="b-layout__table b-layout__table_width_full">
                 <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_60 b-layout__td_center"><img class="b-pic" src="/images/contest-logo.png" width="60" height="58"></td>
                    <td class="b-layout__td b-layout__td_padleft_20">
                       <div class="b-layout__txt b-layout__txt_padbot_20">���� ��� ������� �� ����� ����������� - �������� �������, ����� ������� ������ ������� ������ �� ����� ��������������.</div>
                       <a class="b-button b-button_flat b-button_flat_green" href="?step=1&kind=7">�������� �������</a>
                    </td>
                 </tr>
              </table>
           </div>
           <?php } ?>
           <? if ($project['kind'] == 4) { ?>
           <div class="b-layout b-layout_pad_20 b-layout_2bord_e6 b-layout_bordrad_3 b-layout_margbot_10">
              <table class="b-layout__table b-layout__table_width_full">
                 <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_60 b-layout__td_center"><img class="b-pic" src="/images/project-logo.png" width="46" height="62"></td>
                    <td class="b-layout__td b-layout__td_padleft_20">
                       <div class="b-layout__txt b-layout__txt_padbot_20">�������� ������, ���� ��� ����� ������-����������� ��� �������� ���������� �����-���� ������ �� ����� �������.</div>
                       <a class="b-button b-button_flat b-button_flat_green" href="?step=1&kind=1">�������� ������</a>
                    </td>
                 </tr>
              </table>
           </div>
           <? } else {//if?>
           <div class="b-layout b-layout_pad_20 b-layout_2bord_e6 b-layout_bordrad_3">
              <table class="b-layout__table b-layout__table_width_full">
                 <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_60 b-layout__td_center"><img class="b-pic" src="/images/vacancy-logo.png" width="52" height="60"></td>
                    <td class="b-layout__td b-layout__td_padleft_20">
                       <div class="b-layout__txt b-layout__txt_padbot_20">���������� ��������  � ������� ����������� � ����, ���� ��� ����� ��������� � �������� �� ���������� ������� ������.</div>
                       <a class="b-button b-button_flat b-button_flat_green" href="?step=1&kind=4">���������� ��������</a>
                    </td>
                 </tr>
              </table>
           </div>
           <?php } ?>
        </td>
     </tr>
 </table>   