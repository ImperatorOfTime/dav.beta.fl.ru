<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
$cnt = users::CountAll();
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
$pro = payed::CountPro();
$DB = new DB('master');
?>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td align="left"><strong>����������</strong></td>
	<td align="right"><a href="/siteadmin/stats/charts.php">������</a></td>
</tr>
</table>


<br><br>
<?php $mFull = true; require_once ("top_menu.php"); ?>
<br><br>

<table width="100%" border="1" cellspacing="2" cellpadding="2" class="brd-tbl">
<tr>
	<td>������� PRO (����� ������ ������ � PRO (�� ��� �����))</td>
	<td><?=$pro['cur']?> (<?=$pro['all']?>)</td>
</tr>
<tr>
	<td>������������� PRO FL:</td>
	<td><?=$cnt['autopro_fl']?></td>
</tr>
<tr>
	<td>������������� PRO EMP:</td>
	<td><?=$cnt['autopro_emp']?></td>
</tr>
<tr>
	<td>����� ������ (������� �����)</td>
	<td><?=$cnt['all']?> (<?=$cnt['frl_today']+$cnt['emp_today']?>)</td>
</tr>
<tr>
	<td>- ����������� (������� �����)</td>
	<td><?=$cnt['frl']?> (<?=$cnt['frl_today']?>)</td>
</tr>
<tr>
	<td>- ������������� (������� �����)</td>
	<td><?=$cnt['emp']?> (<?=$cnt['emp_today']?>)</td>
</tr>
<tr>
	<td>����� �����</td>
	<td><?=$cnt['live_emp_today']+$cnt['live_frl_today']?></td>
</tr>
<tr>
	<td>- ����������� �����</td>
	<td><?=$cnt['live_frl_today']?></td>
</tr>
<tr>
	<td>- ������������� �����</td>
	<td><?=$cnt['live_emp_today']?></td>
</tr>
<tr>
	<td>- �������� ������� (�����)</td>
	<td><?=$cnt['prjt']?> (<?=$cnt['prjy']?>)</td>
</tr>
<tr>
	<td>- ���������� �����������</td>
	<td><?=$cnt['mess']?></td>
</tr>
<tr>
	<td>- ���������� ��������</td>
	<td><?=$cnt['notes']?></td>
</tr>
<tr>
	<td>- ���������� ��������</td>
	<td><?=$cnt['teams']?></td>
</tr>
<tr>
	<td>- ����������� � ����� ����������</td>
	<td><?=$cnt['mcont']?></td>
</tr>
<tr>
	<td>- ����������� �� �������������� �� ������� �������� ��������/������������</td>
	<td><?=$cnt['mvac']?></td>
</tr>
<tr>
	<td>- ����������� � ����������/������������ � ������</td>
	<td><?=$cnt['mblog']?></td>
</tr>
<tr>
	<td>- ����������� �� ������ �� �������������� ������/�����������</td>
	<td><?=$cnt['mprj']?></td>
</tr>
<tr>
	<td>- �������� ���������</td>
	<td><?=$cnt['tportf']?></td>
</tr>
<tr>
	<td>- �������� ������</td>
	<td><?=$cnt['tserv']?></td>
</tr>
<tr>
	<td>- �������� ����</td>
	<td><?=$cnt['tinfo']?></td>
</tr>
<tr>
	<td>- �������� ������</td>
	<td><?=$cnt['tjour']?></td>
</tr>
</table>



