<?
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
	$user = new freelancer();
	$user->GetUser($_SESSION['login']);

	if (strtotime($_SESSION['pro_last']) > time() && $_SESSION['pro_test'] == "t")	$is_pro_test = true;
	else										$is_pro_test = false;

?>
<style>
.tarif {
	color: #333333;
	font-size: 13px;
}
</style>
<h1>������</h1>
<? if ($profs) { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="19">
<tr valign="top">
	<td height="400" valign="top" bgcolor="#FFFFFF" class="box2" style="color: #333333;">
		<div style="color: #000000; font-size: 35px; margin-bottom: 25px;">��������������� ������</div>
		<table width="290" border="0" cellspacing="0" cellpadding="0" style="background-image: url(/images/bg_pro.gif); background-repeat: no-repeat;">
			<tr valign="top">
				<td width="50" rowspan="2" style="height: 112; padding-top:35px; padding-left:8px;padding-right:3px;"><?=view_avatar($user->login, $user->photo)?></td>
				<td class="frlname" style="height: 112; padding-top:35px;"><?=view_pro2($is_pro_test)?> <?=$user->uname?> <?=$user->usurname?> [<?=$user->login?>]</td>
			</tr>
		</table><br><br>
		������� &laquo;PRO&raquo;<br>
		���� �������� &ndash; <?=pro_days($_SESSION['pro_last'])?><br>
		<br>
		<a class="blue" href="/bill/">������� � ������ ����</a>
	</td>
</tr>
</table>
<? } else { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="19">
<tr valign="top">
	<td height="400" valign="top" bgcolor="#FFFFFF" class="box2" style="color: #333333;">
		<div align="center" style="color: #000000; font-size: 35px; margin-bottom: 25px;">��������������� ������:</div>

		������� &laquo;PRO&raquo;<br>
		
		��������� ������ �� �������������! ��������� ������� ������� �� ����� �����.<br><br>
		<a class="blue" href="/bill/">��������� ����</a>
	</td>
</tr>
</table>
<? } ?>