<?
$dlnk = $curr_sbr->id ? "<a href=\"?site=docs&id={$curr_sbr->id}\">" : '';
if($dlnk) $dclnk = '</a>';
?>
<div class="nr-block-imp">
	<b class="b1"></b>
	<b class="b2"></b>
	<div class="form-in">
        ������ ����� ��������� ����������� ������ ����� ���������� <?=$dlnk?>����<?=$dclnk?> ������� �� ���������� ������.
        <? if(!$curr_sbr->checkUserReqvs()) { ?>
          ��� ���������� ��������� ��������� �� �������� <a href="/users/<?=$curr_sbr->login?>/setup/finance/">�������</a>.
        <? } ?>
	</div>
	<b class="b2"></b>
	<b class="b1"></b>
</div>
