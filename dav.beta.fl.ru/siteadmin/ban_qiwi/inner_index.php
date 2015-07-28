<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if (!hasPermissions('banners')) {
    exit;
}


$count = banner_qiwi::getCountStat();
$stats = banner_qiwi::getStat();
?>

<strong>���������� ������� QIWI</strong>

<br><br><br>
<strong>����� �������:</strong> <?=$count['views']?>
<br/>
<strong>����� ������:</strong> <?=$count['clicks']?>

<? if($stats) { ?>
    <br><br>
    <table border="1">
        <tr>
            <td width="100">&nbsp;<strong>����</strong></td>
            <td width="50">&nbsp;<strong>������</strong></td>
            <td width="50">&nbsp;<strong>�����</strong></td>
        </tr>
        <? foreach($stats as $stat ) { ?>
            <tr>
                <td>&nbsp;<?=$stat['c_date']?></td>
                <td>&nbsp;<?=$stat['views']?></td>
                <td>&nbsp;<?=$stat['clicks']?></td>
            </tr>
        <? } ?>
    </table>
<? } ?>


