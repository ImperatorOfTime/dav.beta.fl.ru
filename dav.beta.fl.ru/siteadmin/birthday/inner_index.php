<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } 
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/settings.php");
  if (!hasPermissions('birthday')) { exit; }
	$users = $birthday->GetAll();
  $i = 0;
?>
<style>
  .rbx {background:#d9efff;padding:15px;width:337px}
</style>
<strong>���� �������� <?=$year?> (���������)</strong><br><br>
<br/>
<? if ($error) print(view_error($error).'<br/>'); ?>
<? if (!$birthday->isClosed) { ?>
		������ �����������:&nbsp;&nbsp;<B>�������</B> &nbsp;&nbsp;&nbsp;&raquo; <A href="?year=<?=$year?>&action=close" class="blue">�������</A>
<? } else	{ ?>
		������ �����������:&nbsp;&nbsp;<B>�������</B> &nbsp;&nbsp;&nbsp;&raquo; <A href="?year=<?=$year?>&action=open" class="blue">�������</A>
<? } ?>
<br/><br/><br/>

<div class="rbx">
  <form action="." method="post">
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
  		<tr>
  		  <td width="60">�����:</td>
  		  <td><input type="text" name="login" value="<?=($login ? $login : '')?>"/></td>
  		</tr>
  		<tr>
  		  <td>���:</td>
  		  <td><input type="text" name="name" value="<?=($user['uname'] ? $user['uname'] : '')?>"/></td>
  		</tr>
  		<tr>
  		  <td>�������:</td>
  		  <td><input type="text" name="surname" value="<?=($user['usurname'] ? $user['usurname'] : '')?>"/></td>
  		</tr>
		</table>
    <br/><br/>
    <input type="radio" name="type" id="rtype1" value="1"<?=($user['utype']==1 ? ' checked' : '')?>/><label for="rtype1">���������</label>&nbsp;
    <input type="radio" name="type" id="rtype2" value="2"<?=($user['utype']==2 ? ' checked' : '')?>/><label for="rtype2">������������</label>&nbsp;
    <input type="radio" name="type" id="rtype3" value="3"<?=($user['utype']==3 ? ' checked' : '')?>/><label for="rtype3">������</label>
    <br/><br/>
    <input type="hidden" name="action" value="add"/>
    <input type="submit" value="�������� ������������"/>
  </form>
</div>
<br/><br/>
<table width="100%" border="0" cellspacing="2" cellpadding="2">
  <? if ($users) foreach($users as $user){ $i++; ?>
    <tr class="qpr">
    	<td>
    		<table width="100%" cellspacing="0" cellpadding="0" border="0">
    		<tr valign="top" class="n_qpr">
    			<td>
    			  <?=$i?>. <a href="/users/<?=$user['login']?>"><?=$user['uname']." ".$user['usurname']." [".$user['login']."]"?></a> 
    			  <a href="mailto:<?=$user['email']?>"><?=$user['email']?></a> 
    		    <?=($user['utype'] == 1 ? '���������' : ($user['utype'] == 2 ? '������������' : '������'))?>
    			</td>
    			<td align="right">
    			  <? if($user['is_accepted']!='t') { ?>
              [<a href="?year=<?=$year?>&action=accept&id=<?=$user['id']?>"> ������� </a>]
    			  <? } else  { ?>
              [<a href="?year=<?=$year?>&action=unaccept&id=<?=$user['id']?>" style="color:red"> ����� ������ </a>]
    			  <? } ?>
            &nbsp;&nbsp;&nbsp;[<a href="?year=<?=$year?>&action=del&id=<?=$user['id']?>" onclick="return warning()"> ������� </a>]
    			</td>
    		</tr>
    		</table>
    	</td>
    </tr>
  <? } ?>
</table>

