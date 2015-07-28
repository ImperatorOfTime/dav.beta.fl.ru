<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
  if(!(hasPermissions('adm') && hasPermissions('users'))) { exit; }
?>
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/users.common.php");
$xajax->printJavascript('/xajax/');
?>
<style>
.bt, tr.bt td   {border-top:1px solid #d0d0d0}
.br, tr.br td   {border-right:1px solid #d0d0d0}
.bb, tr.bb td   {border-bottom:1px solid #d0d0d0}
.bl, tr.bl td   {border-left:1px solid #d0d0d0}
.ba, tr.ba td   {border:1px solid #d0d0d0}
.ac, tr.ac td   {text-align:center}
.ar, tr.ar td   {text-align:right;padding-right:2px}
</style>
<script>
</script>
<strong>�������</strong>
<br/><br/><br/>
<? if ($_GET['result']=='success') { ?>
  <div>
    <img src="/images/ico_ok.gif" alt="" border="0" height="18" width="19"/>&nbsp;&nbsp;��������� �������.
  </div>
  <br/><br/>
<? } if ($error) print(view_error($error).'<br/>');?>
<form action="/siteadmin/rating/" method="post" onSubmit="this.btn.value='���������'; this.btn.disabled=true;">
    �����&nbsp;&nbsp;<input type="text" name="login" value="<?=$login?>"/><br/><br/>
    <fieldset style="width:30%;text-align:right;padding:10px">
      <legend>�������� ��� �&nbsp;</legend><br/>
      �������&nbsp;&nbsp;<input type="text" name="o_oth_plus" value="<?=$o_oth_plus?>" style="width:50px;text-align:right"/>&nbsp;&nbsp;�����
      <br/>
    </fieldset><br/><br/><br/>
    <input type="hidden" name="action" value="oth_plus" />
    <input type="submit" id="btn" value="���������" />
</form>
<br />
<br />
<br />
���������� ����� ������:
<form action="/siteadmin/rating/" method="post" onSubmit="this.btn.value='���������'; this.btn.disabled=true;">
    �����&nbsp;&nbsp;&nbsp;<input type="text" name="login" value="<?=$login?>"/><br/>
    ������&nbsp;<input type="password" name="pwd" value=""/><br/><br/>
   <br/>
    <input type="hidden" name="action" value="setpwd" />
    <input type="submit" id="btn" value="���������" />
</form>
<br />
<br />
<br />
<?php if ($sError) { print(view_error($sError).'<br/>'); } ?>
��������� ��������:
<form action="/siteadmin/rating/" method="post" onSubmit="this.btn.value='���������'; this.btn.disabled=true;">
    �����&nbsp;&nbsp;<input type="text" name="login" value="<?=$login?>"/><br/>
    ���-�� ����&nbsp;&nbsp;<input type="text" name="days" value="<?=$days?>"/><br/>
    <fieldset style="width:80%;padding:10px">
      <legend>��� ������&nbsp;</legend><br/>
      <input type="radio" name="type" value="1" id="type_pro"> <label for="type_pro">PRO</label><br/>
      <input type="radio" name="type" value="2" id="type_where"> <label for="type_where">���������� �</label> &nbsp;&nbsp;<select name="where">
      	<option value="-1">�������</option>
      	<option value="0">�������</option>
      	<?php foreach ($profs as $prof) { ?>
      	<option value="<?=$prof['id']?>"><?=$prof['groupname']?>: <?=$prof['profname']?></option>
      	<?php } ?>
      </select>
      <br/>
    </fieldset><br/><br/><br/>
    <input type="hidden" name="action" value="addserv" />
    <input type="submit" id="btn" value="���������" />
</form>
<hr/>
�������� �������� �� ������ � ���������
<br/><br/>
<table>
<tr>
    <td style="padding:5px">�����: </td>
    <td><input type="text" id="recalc_portfolio_rating_login" style="margin:5px"/></td>
</tr>
<tr>
    <td colspan="2" align="right"><input type="button" onclick="xajax_recalcUserPortfolioRating($('recalc_portfolio_rating_login').value)" value="����������� ��� ����"/> </td>
</tr>
</table>