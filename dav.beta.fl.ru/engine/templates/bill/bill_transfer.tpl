<?
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/num_to_word.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
  session_start();
  if(!defined('IN_SBR')) {
      $rpath = "../";
      require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reqv_ordered.php");
      require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
      require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
      
      if (!$_SESSION['login']) {header ("Location: /403.php"); exit;}
      $account = new account();
      $ok = $account->GetInfo($_SESSION['uid']);

      if(isset($_POST['send_id'])) {
          $tid = current(array_keys($_POST['send_id']));
      } else {
         $tid = intval($_POST['id']);
      }
      $sum = trim($_POST['sum']);
      $tid = intval($tid);
      if (!$tid) {header ("Location: /bill/"); exit;}
    	$no_risk = intval(trim($_REQUEST['noriskId']));
    	$op_code = ($no_risk)? 36:12;
    	$uid = get_uid(false);
    	$reqv = new reqv();
    	$reqv->GetRow($tid, " AND user_id='{$uid}'");
    	$reqv_ordered = new reqv_ordered($reqv);
    	$reqv_ordered->ammount = $sum;
    	$reqv_ordered->op_code = $op_code;
    	$reqv_ordered->norisk_id = $no_risk;
        $reqv_ordered->is_gift = false;
    	if ($tid) $ord_num = $reqv_ordered->SetOrdered($tid);
	    $billCode = '�-'.$account->id.'-'.sizeof($reqv_ordered->GetByUid($uid));
  }
  $sum = round($sum,2);
  if($sbr_nds) {
      $sbr_nds = round($sbr_nds,2);
      $sbr_comm = round($sbr_comm,2);
  }
  $stc = new static_compress;
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML xmlns:math = "http://exslt.org/math" xmlns:date = 
"http://exslt.org/dates-and-times"><HEAD><TITLE>Free-lance.ru: ����</TITLE>
<META http-equiv=Content-Type content="text/html; charset=windows-1251">
<?php $stc->Send(); ?>
</HEAD>
<BODY text=#000000 bottomMargin=10 vLink=#0033cc aLink=#cc0033 link=#0033cc 
bgColor=#ffffff topMargin=10 marginheight="20" marginwidth="20">
<TABLE class=operations cellSpacing=0 cellPadding=4 width="80%" border=0>
  <TBODY>
  <TR>
    <TD vAlign=bottom><A href="/"><?= PrintSiteLogo(); ?></A>
    </TD>
    <TD vAlign=bottom align=left>&nbsp;</TD>
    <TD vAlign=bottom align=right>
      <DIV class=header><?=(date("d ").strtolower(monthtostr(date("m"))).date(" Y �."))?><BR><BR></DIV></TD></TR>
      <? if($contract_num) { ?>
        <TR>
          <TD class=th colSpan=3>
             <a class="blue" href="/norisk2/?site=Stage&id=<?=(int)$_GET['id']?>&bank=1&ft=<?=(int)$_GET['ft']?>">��������� � ����������� ������</a>
          </TD>
        </TR>
     <? } ?>
  <TR align=middle>
    <TD class=th colSpan=3>
      <H2 class=title>����</H2>
      </TD></TR>
  <TR>
    <TD colSpan=3>
      <TABLE class=filter cellSpacing=0 cellPadding=10 border=0>
        <TBODY>
        <TR>
          <TD bgColor=#f2f2f2>���� �: <B><?=$billCode?></B> �� <B><?=(date("d ").strtolower(monthtostr(date("m"))).date(" Y �."))?>
            </B>, ������ ����� <B>���� ��� ����������� ���</B> </TD>
          <TD class=user>
            <TABLE cellSpacing=4 cellPadding=0 align=left border=0>
              <TBODY>
              <TR>
                <TD><img src="/images/ico_printer.gif" alt="�����������" width="22" height="19" border="0" title="�����������"></TD>
                <TD><A href="/bill/transfer/<?=$ord_num?>/1/<?= $show_ex_code ? 'show_ex_code/' : '';?>" target=blank class="org">�������� �����</A></TD>
                </TR></TBODY></TABLE></TD></TR></TBODY></TABLE>
      <TABLE cellSpacing=0 cellPadding=10 width="100%" border=0>
        <TBODY>
        <TR>
          <TD 
          style="BORDER-RIGHT: #cccccc 1px solid; BORDER-TOP: #cccccc 1px solid; BORDER-LEFT: #cccccc 1px solid; BORDER-BOTTOM: #cccccc 1px solid"><BR>
            <TABLE width="100%" border=0 xmlns:str="http://exslt.org/strings">
              <TBODY>
              <TR>
                <TD>&nbsp;</TD>
                <TD vAlign=top align=right>
                  <DIV style="FONT-SIZE: 10pt"><B>129223, ������, �/� 33</B></DIV></TD></TR></TBODY></TABLE>
            <DIV style="FONT-SIZE: 11pt" align=center 
            xmlns:str="http://exslt.org/strings"><B>������� ���������� 
            ���������� ���������</B></DIV><BR 
            xmlns:str="http://exslt.org/strings">
            <TABLE class=invoice cellSpacing=0 cellPadding=3 width="100%" 
            border=0 xmlns:str="http://exslt.org/strings">
              <TBODY>
              <TR>
                <TD>����������<BR>��� 7805399430 / ��� 771401001 ��� &laquo;����&raquo;</TD>
                <TD align=middle><BR>��. �</TD>
                <TD><BR>40702810787880000803</TD></TR>
              <TR>
                <TD rowSpan=2>���� ����������<BR>� ���������� ������ ��� ��� �������ʻ �. ������
</TD>
                <TD align=middle>���</TD>
                <TD rowSpan=2>044583272<BR>30101810000000000272</TD></TR>
              <TR>
                <TD align=middle>��. �</TD></TR></TBODY></TABLE><BR 
            xmlns:str="http://exslt.org/strings"><BR 
            xmlns:str="http://exslt.org/strings">
            <DIV style="FONT-SIZE: 12pt" align=center 
            xmlns:str="http://exslt.org/strings"><B>���� � <?=$billCode?> �� <?=(date("d ").strtolower(monthtostr(date("m"))).date(" Y �."))?></B></DIV><BR xmlns:str="http://exslt.org/strings">
            <TABLE width="100%" border=0 xmlns:str="http://exslt.org/strings">
              <TBODY>
              <TR>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">��������: <?= reformat($reqv->full_name, 28)?></DIV></TD>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">��������: <?=$reqv->phone?></DIV></TD></TR>
              <TR>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">������������� ���������: <?=$reqv->fio?>
</DIV></TD>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">����: <?=$reqv->fax?>
            </DIV></TD></TR></TBODY></TABLE><BR 
            xmlns:str="http://exslt.org/strings">
            <TABLE class=invoice cellSpacing=0 cellPadding=3 width="100%" 
            border=0>
<? include(ABS_PATH.'/engine/templates/bill/bill_transfer_form.tpl'); ?>
