<?
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
	session_start();
	$i = trim($_GET['i']);
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
	<title>Free-lance</title>
</head>
<script type="text/javascript">
<!--
	window.returnValue = false;
	
<? 
	$num = intval($_GET['num']);
	if ($num){ ?>
var	num = <?=$num?>
<?	} else { ?>
var num = window.dialogArguments;
<? } ?>
	
	function subm(i){
		window.returnValue = i;
		window.close(i);
	}
	
	switch (num){
		case 1: tx="�� ������������� ������ ������� ���������?"; break;
		case 2: tx="�� ������������� ������ ������� ������/�����������?"; break;
		case 3: tx="�� ������������� ������ ������� ��������?"; break;
		case 4: tx="�� ������������� ������ ������� ������?"; break;
		case 5: tx="�� ������������� ������ ������� ������?"; break;
		case 6: tx="�� ������������� ������ ������� ����������?"; break;
		case 7: tx="���� ��������� �����������."; break;
		case 8: tx="�� ������������� ������ ������� ��� �������?"; break;
		case 9: tx="�������� ������ �����. �������� ������������ �&nbsp;����� &laquo;���&raquo;."; break;
		case 10: tx="�� ������������� ������ ������� ����?"; break;
		case 11: tx="�� ������������� ������ ������� �������?"; break;
		case 12: tx="�� ������������� ������ ������� ��� ������ � ���� � ������?"; break
		case 13: tx="�� ������������� ������ ������� ����� ��������?"; break
		case 14: tx="�� ������������� ������ ������������ ���������?"; break
		default: tx="�� �������?";
	}
	
//-->
</script>

<style type="text/css">
.s {
	font-family: Tahoma;
	font-size: 11px;
}
#btn {
	width: 80px;
}
</style>

<body text="#666666">
<table cellspacing="19" cellpadding="0" border="0" height="100%">
<tr>
	<td colspan="2">
	<table cellspacing="0" cellpadding="4" border="0" class="s">
	<tr>
		<td><img src="images/ico_error.gif" alt="" width="22" height="18" border="0"></td>
		<td><script type="text/javascript">
			<!--
		 		document.write(tx);
		 	//-->
			</script>
		</td>
	</tr>
	</table>
	</td>
</tr>
<tr>
	<td valign="bottom"><input type="submit" id="btn" value="Ok" class="s" onClick="subm(true);"></td>
	<td valign="bottom"><input type="submit" id="btn" value="������" class="s" onClick="subm(false);"></td>
</tr>
</table>

</body>
</html>
