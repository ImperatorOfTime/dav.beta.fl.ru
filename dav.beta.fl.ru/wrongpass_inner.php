<table width="100%" border="0" cellspacing="0" cellpadding="15">
<tr valign="top">
	<td height="400" valign="top" bgcolor="#FFFFFF" class="box2">
	<h1>������������ ����� ��� ������</h1>
			<? 
				if ($action == "send" && !$error) { ?>
			<table cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td width="25" height="20"><img src="/images/ico_ok.gif" alt="" width="19" height="18" border="0"></td>
				<td>�� ��� ����������� ���� ���� ������� ����� � ������</td>
			</tr>
			</table>
			<? }  else { ?>
			�� ����� ������������ ����� ��� ������.<br><br>
			���� �� ������ ����� ��� ������, ������� ��� ����������� �����, ��������� ��� �����������, � ���� ����, � ����� � ������� ����� ������ �� ����.
			<form action="/wrongpass.php" method="post">
			<input type="hidden" name="action" value="send">
			<table cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td width="115">����������� �����:</td>
				<td><input type="text" name="email" value="<?=$s_email?>" size="33"> &nbsp;<input type="submit" name="btn" class="btn" value="�������"></td>
			</tr>
			<? if ($error) { ?>
			<tr>
				<td>&nbsp;</td>
				<td><?=view_error($error)?></td>
			</tr>
			<? } ?>
			</table>
			</form>
			<? } ?>
	</td>
</tr>
</table>
