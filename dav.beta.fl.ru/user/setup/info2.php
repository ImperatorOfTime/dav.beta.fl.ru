<?
  // ���� ��� template2.php
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
	$user = new freelancer();
	$user->GetUser($login);
?>
<a name="page"></a>
<div class="acc-h c">
					<div style="float:right"><span class="del-icon"></span> <a class="del-user-lnk blue" href="/users/<?=$user->login?>/setup/delete/">������� �������</a></div>
    <a href="/users/<?= $user->login ?>/setup/foto/">
        <?=view_avatar($user->login, $user->photo, 0, 'acc-userpic')?>
    </a>
	<div class="acc-h-i">
 	<div>
 	
  
   
 	  <a href="/users/<?=$user->login?>/" class="<?=(is_emp($user->role) ? 'employer' : 'freelancer')?>-name"><?=$user->uname?> <?=$user->usurname?> [<?=$user->login?>]</a> <? /*($user->is_pro=='t' ? (is_emp($user->role) ? view_pro_emp() : view_pro2($user->is_pro_test=='t')) : '')*/?>
 	  <?=view_mark_user(array("login"      => $user->login, "is_pro"      => $user->is_pro,
									"is_pro_test" => $user->is_pro_test,
									"is_team"     => $user->is_team,
									"role"        => $user->role))?> <?//=$session->view_online_status($user->login)?></div>
		
		
		
		<table cellspacing="2" cellpadding="2" class="config-link-table" style="margin-bottom:0;" >
		<tr>
			<td style="width:17px; height:17px; vertical-align:middle"><img src="/images/dot_black.gif" alt="" width="3" height="3" /></td>
			<td style="height:17px">
			
			<? if ($inner == "main_inner.php") {?>�������� ���������<? } else {?><a href="/users/<?=$user->login?>/setup/main/" class="blue">�������� ���������</a><? } ?>
			</td>
		</tr>
		<tr>
			<td style="width:17px; height:17px; vertical-align:middle"><img src="/images/dot_black.gif" alt="" width="3" height="3" /></td>
			<td style="height:17px">
			<? if ($inner == "foto_inner.php") {?>��� ����������<? } else {?><a href="/users/<?=$user->login?>/setup/foto/" class="blue">��� ����������</a><? } ?>
			</td>
		</tr>
		<tr>
			<td style="width:17px; height:17px; vertical-align:middle"><img src="/images/dot_black.gif" alt="" width="3" height="3" /></td>
			<td style="height:17px">
			<? if ($inner == "mailer_inner.php") {?>�����������/��������<? } else {?><a href="/users/<?=$user->login?>/setup/mailer/" class="blue">�����������/��������</a><? } ?>
			</td>
		</tr>
		<tr>
			<td style="width:17px; height:17px; vertical-align:middle"><img src="/images/dot_black.gif" alt="" width="3" height="3" /></td>
			<td style="height:17px">
			<? if ($inner == "list_inner.php") {?>��������� ��������<? } else {?><a href="/users/<?=$user->login?>/setup/tabssetup/" class="blue">��������� ��������</a><? } ?>
			</td>
		</tr>
		<tr>
			<td style="width:17px; height:17px; vertical-align:middle"><img src="/images/dot_black.gif" alt="" width="3" height="3" /></td>
			<td style="height:17px">
			<? if ($inner == "safety_inner.php") {?>������������<? } else {?><a href="/users/<?=$user->login?>/setup/safety/" class="blue">������������</a><? } ?>
			</td>
		</tr>
		</table>
		
		
<!--		
		<ul>
			<li><? if ($inner == "main_inner.php")   { ?>�������� ���������<? } else { ?><a href="/users/<?=$user->login?>/setup/main/">�������� ���������</a><? } ?></li>
			<li><? if ($inner == "foto_inner.php")   { ?>��� ����������<? } else { ?><a href="/users/<?=$user->login?>/setup/foto/">��� ����������</a><? } ?></li>
			<li><? if ($inner == "mailer_inner.php") { ?>�����������/��������<? } else { ?><a href="/users/<?=$user->login?>/setup/mailer/">�����������/��������</a><? } ?></li>
			<li><? if ($inner == "list_inner.php")   { ?>��������� ��������<? } else { ?><a href="/users/<?=$user->login?>/setup/tabssetup/">��������� ��������</a><? } ?></li>
			<li><? if ($inner == "safety_inner.php") { ?>������������<? } else { ?><a href="/users/<?=$user->login?>/setup/safety/">������������</a><? } ?></li>
			<li><span class="del-icon"></span> <a class="del-user-lnk blue" href="#">������� �������</a></li>
		</ul>
-->
	</div>
</div>
 		