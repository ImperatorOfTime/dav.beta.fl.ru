<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td align="left"><strong>����������</strong></td>
	<td align="right"><a href="/siteadmin/stats/">�����</a></td>
</tr>
</table>
<br>














<table width="100%" cellspacing="2" cellpadding="2" border="0">
<tr>
	<td>������</td>
	<td>������</td>
    <td>�������</td>
</tr>
<tr>
	<td valign="top">
		<table width="100%" cellspacing="2" cellpadding="2" border="0">
		<?
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
		$countr = country::CountAll();
		if ($countr)
			foreach($countr as $ikey=>$cntr){
		?>
		<tr>
			<td width="130"><?=$cntr['country_name']?></td>
			<td><?=$cntr['cnt']?></td>
		</tr>
		<? } ?>
		</table>
	</td>
	<td valign="top">
		<table width="100%" cellspacing="2" cellpadding="2" border="0">
	<?
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
		$citys = city::CountAll();
		if ($citys)
			foreach($citys as $ikey=>$city){
		?>
		<tr>
			<td width="130"><?=$city['city_name']?></td>
			<td><?=$city['cnt']?></td>
		</tr>
		<? } ?>
		</table>
	</td>
	<td valign="top">
		<table width="100%" cellspacing="2" cellpadding="2" border="0">
	<?
        $DB = new DB('master');
        $sql = "select count(*) as cnt, to_char(birthday,'YYYY') as _year from freelancer GROUP BY to_char(birthday,'YYYY') order BY cnt desc";
        $ages = $DB->rows($sql);
			foreach($ages as $ikey=>$age){
                if($age['_year']=='') {
                    $tage = '�� �������';
                } else {
                    $tage = date('Y')-$age['_year'];
                }
		?>
		<tr>
			<td width="130"><?=$tage?></td>
			<td><?=$age['cnt']?></td>
		</tr>
		<? } ?>
		</table>
	</td>
</tr>
</table>

<a href="geo.php">��� ������ � ������</a>
