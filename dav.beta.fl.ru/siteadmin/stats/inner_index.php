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
<br>





<?
$action = trim($_GET['action']);
if (!$action) $action = trim($_POST['action']);

$forms_cnt = intval(trim($_POST['forms_cnt']));
if (!$forms_cnt) $forms_cnt = 1;

switch ($action){
	case "inc_forms":
		$forms_cnt++;
		break;
}

for ($i = 0; $i < $forms_cnt; $i++){
	$fmnth[$i] = intval(trim($_POST['fmnth'][$i]));
	$fday[$i] = intval(trim($_POST['fday'][$i]));
	$fyear[$i] = intval(trim($_POST['fyear'][$i]));
	$tmnth[$i] = intval(trim($_POST['tmnth'][$i]));
	$tday[$i] = intval(trim($_POST['tday'][$i]));
	$tyear[$i] = intval(trim($_POST['tyear'][$i]));
	if (!checkdate($fmnth[$i], $fday[$i] , $fyear[$i]) || !checkdate($tmnth[$i], $tday[$i] , $tyear[$i])){
		$fday[$i] = $tday[$i] = date("d");
		$fmnth[$i] = $tmnth[$i] = date("m");
		$fyear[$i] = $tyear[$i] = date("Y");
	}

	$fdate = $fyear[$i] . "-". $fmnth[$i] ."-" .$fday[$i];
	$tdate = $tyear[$i] . "-". $tmnth[$i] ."-" .$tday[$i];

    // -----
}

?>
<form action="." method="post" name="frm" id="frm">
<input type="hidden" name="action" value="">
<input type="hidden" name="forms_cnt" value="<?=$forms_cnt?>">
	<? if ($error) print(view_error($error));?>

<? for ($i = 0; $i < $forms_cnt; $i++) {
	$fdate = $fyear[$i] . "-". $fmnth[$i] ."-" .$fday[$i];
	$tdate = $tyear[$i] . "-". $tmnth[$i] ."-" .$tday[$i];
?>
�&nbsp;&nbsp;
<input type="text" name="fday[]" size="2" maxlength="2" value="<?=$fday[$i]?>">
<select name="fmnth[]">
	<option value="1" <? if ($fmnth[$i] == 1) print "SELECTED"?>>������</option>
	<option value="2" <? if ($fmnth[$i] == 2) print "SELECTED"?>>�������</option>
	<option value="3" <? if ($fmnth[$i] == 3) print "SELECTED"?>>�����</option>
	<option value="4" <? if ($fmnth[$i] == 4) print "SELECTED"?>>������</option>
	<option value="5" <? if ($fmnth[$i] == 5) print "SELECTED"?>>���</option>
	<option value="6" <? if ($fmnth[$i] == 6) print "SELECTED"?>>����</option>
	<option value="7" <? if ($fmnth[$i] == 7) print "SELECTED"?>>����</option>
	<option value="8" <? if ($fmnth[$i] == 8) print "SELECTED"?>>�������</option>
	<option value="9" <? if ($fmnth[$i] == 9) print "SELECTED"?>>��������</option>
	<option value="10" <? if ($fmnth[$i] == 10) print "SELECTED"?>>�������</option>
	<option value="11" <? if ($fmnth[$i] == 11) print "SELECTED"?>>������</option>
	<option value="12" <? if ($fmnth[$i] == 12) print "SELECTED"?>>�������</option>
</select>
<input type="text" name="fyear[]" size="4" maxlength="4" value="<?=$fyear[$i]?>">&nbsp;&nbsp;
��&nbsp;&nbsp;
<input type="text" name="tday[]" size="2" maxlength="2" value="<?=$tday[$i]?>">
<select name="tmnth[]">
	<option value="1" <? if ($tmnth[$i] == 1) print "SELECTED"?>>������</option>
	<option value="2" <? if ($tmnth[$i] == 2) print "SELECTED"?>>�������</option>
	<option value="3" <? if ($tmnth[$i] == 3) print "SELECTED"?>>�����</option>
	<option value="4" <? if ($tmnth[$i] == 4) print "SELECTED"?>>������</option>
	<option value="5" <? if ($tmnth[$i] == 5) print "SELECTED"?>>���</option>
	<option value="6" <? if ($tmnth[$i] == 6) print "SELECTED"?>>����</option>
	<option value="7" <? if ($tmnth[$i] == 7) print "SELECTED"?>>����</option>
	<option value="8" <? if ($tmnth[$i] == 8) print "SELECTED"?>>�������</option>
	<option value="9" <? if ($tmnth[$i] == 9) print "SELECTED"?>>��������</option>
	<option value="10" <? if ($tmnth[$i] == 10) print "SELECTED"?>>�������</option>
	<option value="11" <? if ($tmnth[$i] == 11) print "SELECTED"?>>������</option>
	<option value="12" <? if ($tmnth[$i] == 12) print "SELECTED"?>>�������</option>
</select>
<input type="text" name="tyear[]" size="4" maxlength="4" value="<?=$tyear[$i]?>">
<input type="submit" value="���!"><br><br>



<? } ?>


</form>



<?php
$prop[$i] = account::GetPROStat($fdate, $tdate, 0);
$testpro[$i] = account::GetStatOP(47, $fdate, $tdate);
$prop2[$i] = account::GetPROStat($fdate, $tdate);
$ppp[$i] = account::GetStatOP(array(8), $fdate, $tdate);
$gpp[$i] = account::GetStatOP(array(16,17,18,34,35), $fdate, $tdate, "", "RIGHT JOIN present ON billing_from_id = account_operations.id");
$fpp[$i] = account::GetStatOP(array(10,11), $fdate, $tdate);
$fppc[$i] = account::GetStatOP(array(19), $fdate, $tdate);
$fppci[$i] = account::GetStatOP(array(20), $fdate, $tdate);
$cho[$i] = account::GetStatOP(array(21), $fdate, $tdate);
$konk[$i] = account::GetStatOP(array(9), $fdate, $tdate);
$upproj[$i] = account::GetStatOP(array(7), $fdate, $tdate);
$transf[$i] = account::GetStatOP(array(23), $fdate, $tdate);
$testbuypro[$i] = account::GetStatTestBuyPro($fdate,$tdate);
$bonuses[$i] = account::GetStatBonuses($fdate,$tdate);


list($frlpp, $emppp) = account::getStatsPRO($fdate, $tdate);
?>


<table  border="1" cellspacing="2" cellpadding="2" class="brd-tbl">
<tr>
	<td width=200><strong>�������:</strong></td>
	<td>
        <?php
        $sql = "SELECT COUNT(*) as cnt FROM projects WHERE post_date >= ? AND post_date - '1 day'::interval < ?";
        $s_project = $DB->rows($sql, $fdate, $tdate);
        ?>
        <?=$s_project[0]['cnt']?>
    </td>
</tr>
<tr>
	<td width=200><strong>���-�� ������� �� �������:</strong></td>
	<td>
        <?php
        $sql = "SELECT COUNT(*) as cnt FROM projects_offers WHERE post_date >= ? AND post_date - '1 day'::interval < ?";
        $s_project_offers = $DB->rows($sql, $fdate, $tdate);
        ?>
        <?=$s_project_offers[0]['cnt']?>
    </td>
</tr>
<tr>
	<td width=200><strong>������� ���-�� ��������:</strong></td>
	<td>
        <?
        list($fyd, $fmd, $fdd) = preg_split("/-/",$fdate);
        list($tyd, $tmd, $tdd) = preg_split("/-/",$tdate);
        $daysd = 1+(mktime(0,0,0,$tmd,$tdd,$tyd)-mktime(0,0,0,$fmd,$fdd,$fyd))/60/60/24;
        echo round($s_project[0]['cnt']/$daysd,2);
        ?>
    </td>
</tr>
<tr>
	<td width=200><strong>������� ���-�� ������� �� ������:</strong></td>
	<td>
        <?php
        $sql = "select count(1) as cnt from projects_offers where post_date >= ? AND post_date - '1 day'::interval < ?";
        $s_project_offers = $DB->rows($sql, $fdate, $tdate);

        ?>
        <?php if($s_project[0]['cnt']==0) { echo '0'; } else { echo round($s_project_offers[0]['cnt']/$s_project[0]['cnt'],2); } ?>
    </td>
</tr>
<tr><td colspan=2><strong>�����������</strong></td></tr>
<tr>
	<td>- ����������:</td>
	<td>
        <?php
        $sql = "SELECT count(*) as cnt FROM freelancer WHERE reg_date >= ? AND reg_date - '1 day'::interval < ?";
        $s_reg_f = $DB->rows($sql, $fdate, $tdate);
        ?>
        <?=$s_reg_f[0]['cnt']?>
    </td>
</tr>
<tr>
	<td>- ������������:</td>
	<td>
        <?php
        $sql = "SELECT count(*) as cnt FROM employer WHERE reg_date >= ? AND reg_date - '1 day'::interval < ?";
        $s_reg_e = $DB->rows($sql, $fdate, $tdate);
        ?>
        <?=$s_reg_e[0]['cnt']?>
    </td>
</tr>
<tr>
	<td>- �����:</td>
	<td>
        <?=$s_reg_e[0]['cnt']+$s_reg_f[0]['cnt']?>
    </td>
</tr>
<tr><td colspan=2><strong>�������������</strong></td></tr>
<tr>
	<td>- ����������:</td>
	<td>
        <?php
        $sql = "SELECT sum(b_frl) as cnt FROM stat_data WHERE date >= ? AND date - '1 day'::interval < ?";
        $s_ban_f = $DB->rows($sql, $fdate, $tdate);
        ?>
        <?=$s_ban_f[0]['cnt']?>
    </td>
</tr>
<tr>
	<td>- ������������:</td>
	<td>
        <?php
        $sql = "SELECT sum(b_emp) as cnt FROM stat_data WHERE date >= ? AND date - '1 day'::interval < ?";
        $s_ban_e = $DB->rows($sql, $fdate, $tdate);
        ?>
        <?=$s_ban_e[0]['cnt']?>
    </td>
</tr>
<tr>
	<td>- �����:</td>
	<td>
        <?=$s_ban_e[0]['cnt']+$s_ban_f[0]['cnt']?>
    </td>
</tr>
<tr><td colspan=2><strong>������� ��������</strong></td></tr>
<tr>
	<td>- TEST-PRO ����������:</td>
	<td><?=zin($testpro[$i]['cnt'])?></td>
</tr>
<tr>
	<td>- PRO ����������:</td>
	<td><?=zin($prop[$i]['cnt'])?></td>
</tr>
<tr>
	<td>- PRO ������������:</td>
	<td><?=zin($prop2[$i]['cnt'])?></td>
</tr>
<tr>
	<td>- ������� �������:</td>
	<td><?=zin($ppp[$i]['cnt'])?></td>
</tr>
<tr>
	<td>- �������:</td>
	<td><?=zin($gpp[$i]['cnt'])?></td>
</tr>
<tr>
	<td>- ����� �� �������:</td>
	<td><?=zin($fpp[$i]['cnt'])?></td>
</tr>
<tr>
	<td>- ����� � ����� ��������:</td>
	<td><?=zin($fppc[$i]['cnt'])?></td>
</tr>
<tr>
	<td>- ����� ������ ��������:</td>
	<td><?=zin($fppci[$i]['cnt'])?></td>
</tr>
<tr>
	<td>- �����������:</td>
	<td><?=zin($cho[$i]['cnt'])?></td>
</tr>
<tr>
	<td>- ��������:</td>
	<td><?=zin($konk[$i]['cnt'])?></td>
</tr>
<tr>
	<td>- ������� ������:</td>
	<td><?=zin($upproj[$i]['cnt'])?></td>
</tr>
<tr>
	<td>- �������� �����:</td>
	<td><?=zin($transf[$i]['cnt'])?></td>
</tr>
<tr><td colspan=2><strong>�����</strong></td></tr>
<tr>
	<td>- ����������:</td>
	<td>
        <?php
        $sql = "SELECT SUM(l_frl) as cnt FROM stat_data WHERE date >=? AND date<=?";
        $s_f_live['cnt'] = $DB->val($sql, $fdate, $tdate);
        ?>
        <?=(int) $s_f_live['cnt']?>
    </td>
</tr>
<tr>
	<td>- ������������:</td>
	<td>
        <?php
        $sql = "SELECT SUM(l_emp) as cnt FROM stat_data WHERE date >=? AND date<=?";
        $s_e_live['cnt'] = $DB->val($sql, $fdate, $tdate);
        ?>
        <?=(int) $s_e_live['cnt']?>
    </td>
</tr>
<tr>
	<td>- �����:</td>
	<td><?=$s_e_live['cnt']+$s_f_live['cnt']?></td>
</tr>
<?php
/*
?>
<tr>
	<td><strong>�������� ��������:</strong></td>
	<td>
        <?php
		$sql = "SELECT t2.views FROM ban_banners
			INNER JOIN ban_company ON ban_company.id = ban_banners.company_id 
			LEFT JOIN (SELECT SUM(views) as views, SUM(clicks) as clicks, banner_id FROM(
            SELECT SUM(views) as views, SUM(clicks) as clicks, banner_id FROM ban_stats1 GROUP BY banner_id
            UNION ALL SELECT COUNT(*), NULL, banner_id FROM ban_stats2 GROUP BY banner_id) as t
            GROUP BY t.banner_id) as t2
			ON t2.banner_id = ban_banners.id WHERE c_date>='".$fdate."' AND c_date - '1 day'::interval < '".$tdate."'";

        $ban_stat = pg_fetch_array(pg_query(DBConnect(),$sql));
        if(empty($ban_stat['views'])) $ban_stat['views'] = 0;
        ?>
        <?=$ban_stat['views']?>
    </td>
</tr>
<?php
*/
?>
<tr><td colspan=2><strong>�������� �����</strong></td></tr>
<tr>
	<td>- �������:</td>
	<td>
        <?php
        $sql = "SELECT SUM(count) as cnt FROM stat_feedback WHERE date >=? AND date - '1 day'::interval <? AND type=1";
        $sf1[0]['cnt'] = $DB->val($sql, $fdate, $tdate);
        if(empty($sf1[0]['cnt'])) $sf1[0]['cnt'] = 0;
        ?>
        <?=$sf1[0]['cnt']?>
    </td>
</tr>
<tr>
	<td>- ������:</td>
	<td>
        <?php
        $sql = "SELECT SUM(count) as cnt FROM stat_feedback WHERE date >=? AND date - '1 day'::interval <? AND type=2";
        $sf2[0]['cnt'] = $DB->val($sql, $fdate, $tdate);
        if(empty($sf2[0]['cnt'])) $sf2[0]['cnt'] = 0;
        ?>
        <?=$sf2[0]['cnt']?>
    </td>
</tr>
<tr>
	<td>- �������, ������:</td>
	<td>
        <?php
        $sql = "SELECT SUM(count) as cnt FROM stat_feedback WHERE date >=? AND date - '1 day'::interval <? AND type=3";
        $sf3[0]['cnt'] = $DB->val($sql, $fdate, $tdate);
        if(empty($sf3[0]['cnt'])) $sf3[0]['cnt'] = 0;
        ?>
        <?=$sf3[0]['cnt']?>
    </td>
</tr>
<tr>
	<td>- ������ �� �������������:</td>
	<td>
        <?php
        $sql = "SELECT SUM(count) as cnt FROM stat_feedback WHERE date >=? AND date - '1 day'::interval <? AND type=4";
        $sf4[0]['cnt'] = $DB->val($sql, $fdate, $tdate);
        if(empty($sf4[0]['cnt'])) $sf4[0]['cnt'] = 0;
        ?>
        <?=$sf4[0]['cnt']?>
    </td>
</tr>
<tr>
	<td>- �����������:</td>
	<td>
        <?php
        $sql = "SELECT SUM(count) as cnt FROM stat_feedback WHERE date >=? AND date - '1 day'::interval <? AND type=5";
        $sf5[0]['cnt'] = $DB->val($sql, $fdate, $tdate);
        if(empty($sf5[0]['cnt'])) $sf5[0]['cnt'] = 0;
        ?>
        <?=$sf5[0]['cnt']?>
    </td>
</tr>
<tr>
	<td>- ������ �� �������:</td>
	<td>
        <?php
        $sql = "SELECT SUM(count) as cnt FROM stat_feedback WHERE date >=? AND date - '1 day'::interval <? AND type=6";
        $sf6[0]['cnt'] = $DB->val($sql, $fdate, $tdate);
        if(empty($sf6[0]['cnt'])) $sf6[0]['cnt'] = 0;
        ?>
        <?=$sf6[0]['cnt']?>
    </td>
</tr>
<tr>
	<td>- �����:</td>
	<td><?=$sf1[0]['cnt']+$sf2[0]['cnt']+$sf3[0]['cnt']+$sf4[0]['cnt']+$sf5[0]['cnt']+$sf6[0]['cnt']?></td>
</tr>
<tr>
	<td><strong>���-�� PRO ����� ����-PRO:</strong></td>
	<td><?=$testbuypro[$i]?></td>
</tr>
<tr><td colspan=2><strong>������ ����������� �� ��</strong></td></tr>
<tr>
	<td>- PRO � �������:</td>
	<td><?=$bonuses[$i]['frl_pro']?></td>
</tr>
<tr>
	<td>- ���������� �� �������:</td>
	<td><?=$bonuses[$i]['frl_main']?></td>
</tr>
<tr><td colspan=2><strong>������ ������������� �� ��</strong></td></tr>
<tr>
	<td>- PRO � �������:</td>
	<td><?=$bonuses[$i]['emp_pro']?></td>
</tr>
<tr>
	<td>- 85 FM �� �������� ����:</td>
	<td><?=$bonuses[$i]['emp_fm']?></td>
</tr>
</table>


<br><br>



<table width="100%" border="1" cellspacing="2" cellpadding="2">
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


<br/><br/>
<table  width="100%" border="1" cellspacing="2" cellpadding="2">
    <tr>
        <td colspan="2"><strong>����������:</strong></td>
    </tr>
    <tr>
        <td width=500>- ������� ��������� pro:</td>
        <td><?=$frlpp['tp']?></td>
    </tr>
    <tr>
        <td>- �������� ������ pro ����� ��������� pro:</td>
        <td><?=$frlpp['fpp_tp']?></td>
    </tr>
    <tr>
        <td>- �������� ������ pro 2 ���� ����� ��������� pro:</td>
        <td><?=$frlpp['fpp2_tp']?></td>
    </tr>
    <tr>
        <td>- �������� ������ pro 3 ���� ����� ��������� pro:</td>
        <td><?=$frlpp['fpp3_tp']?></td>
    </tr>
    <tr>
        <td>- �������� ������ pro 4 ���� ����� ��������� pro:</td>
        <td><?=$frlpp['fpp4_tp']?></td>
    </tr>
    <tr>
        <td>- �������� ������ pro 5 ��� ����� ��������� pro:</td>
        <td><?=$frlpp['fpp5_tp']?></td>
    </tr>
    <!-- 
    <tr>
        <td colspan="2"><strong>����������:</strong></td>
    </tr>-->
    <tr>
        <td>- �������� ����������� pro:</td>
        <td><?=$frlpp['fpp']?></td>
    </tr>
    <tr>
        <td>- �������� ����������� pro 2 ����:</td>
        <td><?=$frlpp['fpp2']?></td>
    </tr>
    <tr>
        <td>- �������� ����������� pro 3 ����:</td>
        <td><?=$frlpp['fpp3']?></td>
    </tr>
    <tr>
        <td>- �������� ����������� pro 4 ����:</td>
        <td><?=$frlpp['fpp4']?></td>
    </tr>
    <tr>
        <td>- �������� ����������� pro 5 ���:</td>
        <td><?=$frlpp['fpp5']?></td>
    </tr>
    <!-- -->
    <tr>
        <td colspan="2"><strong>������������:</strong></td>
    </tr>
    <tr>
        <td>- �������� ����������� pro:</td>
        <td><?=$emppp['epp']?></td>
    </tr>
    <tr>
        <td>- �������� ����������� pro 2 ����:</td>
        <td><?=$emppp['epp2']?></td>
    </tr>
    <tr>
        <td>- �������� ����������� pro 3 ����:</td>
        <td><?=$emppp['epp3']?></td>
    </tr>
    <tr>
        <td>- �������� ����������� pro 4 ����:</td>
        <td><?=$emppp['epp4']?></td>
    </tr>
    <tr>
        <td>- �������� ����������� pro 5 ���:</td>
        <td><?=$emppp['epp5']?></td>
    </tr>
</table>





<br><br><br>
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
		$countr = country::CountAll(10);
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
		$citys = city::CountAll(10);
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
        $sql = "select count(*) as cnt, to_char(birthday,'YYYY') as _year from freelancer GROUP BY to_char(birthday,'YYYY') order BY cnt desc limit 10";
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

<a href="geo.php">��� ������, ������ � �������</a>
