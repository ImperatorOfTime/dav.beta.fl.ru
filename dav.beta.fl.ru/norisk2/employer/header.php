<?
if(hasPermissions('sbr')  && $_SESSION['access']=='A') {
    include('admin/header.php');
    return;
}
?>
<div class="nr-h c">
	<div class="nr-start">
        <a href="/sbr/?site=new"><img src="/images/norisk-start.png" alt="������ ����� ����������� ������" class="lnk-nr-start" width="176" height="28" /></a>
        <p>���� � ��� ��� ���� �������� �������, �� �� ������ ������ ����������� ������ ����� ������. ��� ����� ��������� � ������ &laquo;<a href="/users/<?=$sbr->login?>/setup/projects/">�������</a>&raquo;.</p>
	</div>
	<div class="nr-docs">
		<ul>
            <li><a href="<?=sbr::$scheme_types[sbr::SCHEME_AGNT][1]?>" target="_blank">��������� �������</a></li>
            <li class="first"><a href="/offer_work_employer.pdf" target="_blank">������� �������</a></li>
            <li><a href="/help/?c=41">������ �� ����������� ������</a></li>
            <? if(hasPermissions('sbr') || hasPermissions('sbr_finance')) { ?>
              <li><a href="?site=admin">�����������������</a></li>
            <? } ?>
		</ul>
	</div>
    <? include('tpl.header-manager.php') ?>
</div>
