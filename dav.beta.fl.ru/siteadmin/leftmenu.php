<?php 
// !!! ��������� ��������� ������� ����: ������ ������ 1-24 � 100

if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } 
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/masssending.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/paid_advices.php");
  $paid_advice = new paid_advices();
  $stat_advice = $paid_advice->getStatAdvices();
  $mass_sending_new_cnt = masssending::GetCount(masssending::OM_NEW);
  $s = 'style="color: #666;"';
  $c = 'class="blue"';
  
  // ���������� ����� � �����
  require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages_spam.php' );
  $nMessagesSpamCount = messages_spam::getSpamCount();
  
  // ���������� ����� �� �������
  if ( !isset($nComplainProjectsCount) ) {
      require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects.php';
      $nComplainProjectsCount = projects::GetComplainPrjsCount();
  }
?>

<div class="admin-menu">

    <h3>�������������</h3>
    <?php  

    if ( !isset($aPermissions) ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/permissions.php");
        $aPermissions = permissions::getUserPermissions( get_uid(false) );
    }

    foreach ( $aPermissions as $sPermission ) {
        $sVar  = 'bHas' . ucfirst( $sPermission );
        $$sVar = true;
    }

    ?>
    <?php if ( $bHasAll || $bHasAdm ) { ?>

        <?php if ( $bHasAll || $bHasUsers || $bHasProjects || $bHasBlogs || $bHasCommunes ) { ?>
        - ��������<br/>
        <?php if ( $bHasAll || $bHasUsers || $bHasProjects || $bHasBlogs || $bHasCommunes ) { ?>
        -- <a <?=($menu_item == 1 ? $s : $c)?> href="/siteadmin/admin_log/?site=log">����� ��������</a><br/>
        <?php } ?>
        <?php if ( $bHasAll || $bHasUsers ) { ?>
        -- <a <?=($menu_item == 2 ? $s : $c)?> href="/siteadmin/admin_log/?site=user">����������</a><br/>
        <?php } ?>
        <?php if ( $bHasAll || $bHasProjects ) { ?>
        -- <a <?=($menu_item == 3 ? $s : $c)?> href="/siteadmin/admin_log/?site=proj">������� � ��������</a><br/>
        <?php } ?>
        <?php } ?>

        <?php if ( $bHasAll || $bHasUsers ) { ?>
        <br/>- <a <?=($menu_item == 4 ? $s : $c)?> href="/siteadmin/user_search/">������������</a><br/>
        <? } ?>
        <?php if ( $bHasAll || $bHasGrayip ) { ?>
        -- <a <?=($menu_item == 5 ? $s : $c)?> href="/siteadmin/gray_ip">����� ������ IP</a><br/>
        <? } ?>
        <?php if ( $bHasAll || $bHasUsers ) { ?>
        -- <a href="/siteadmin/ban-razban/?mode=users" class="blue">������������</a><br/>
        <? } ?>
        <?php if ( $bHasAll || $bHasSuspicioususers ) { ?>
        -- <a href="/siteadmin/suspicious-users/" class="blue">�������������� ������������<? $countSuspiciousUsers=users::GetCountSuspiciousUsers(); echo ($countSuspiciousUsers?" ({$countSuspiciousUsers})":'') ?></a><br/>
        <? } ?>
        <?php if ( $bHasAll || $bHasSuspiciousip ) { ?>
        -- <a href="/siteadmin/suspicious-ip/" class="blue">�������������� IP</a><br/>
        <? } ?>
        <?php if ( $bHasAll || $bHasUsers ) { ?>
        -- <a href="/siteadmin/users/" class="blue">������������ (��� ����)</a><br/>
        <? } ?>
        <?php if ( $bHasAll || $bHasUnreadsmsg ) { ?>
        -- <a href="/siteadmin/unreads/" class="blue">������������� ���������</a><br/>
        <? } ?>
        <?php if ( $bHasAll || $bHasUserphone ) { ?>
        -- <a href="/siteadmin/user_phone/" class="blue">��������� �������� (�������)</a><br/>
        <? } ?>

        <?php if ( $bHasAll || $bHasProjects || $bHasUsers ) { ?>
        <br/>- ������<br/>
        <?php if ( $bHasAll || $bHasProjects ) { ?>
        -- <a <?=($menu_item == 12  ? $s : $c)?> href="/siteadmin/ban-razban/?mode=complain">������ �� �������<?=( !empty($nComplainProjectsCount) ? " ($nComplainProjectsCount)" : '' )?></a><br/>
        -- <a <?=($menu_item == 23  ? $s : $c)?> href="/siteadmin/ban-razban/?mode=complain_types">���� ����� �� �������</a><br/>
        <?php } ?>
        <?php if ( $bHasAll || $bHasUsers ) { ?>
        -- <a <?=($menu_item == 11 ? $s : $c)?> href="/siteadmin/messages_spam">������ �� ����<?=( !empty($nMessagesSpamCount) ? " ($nMessagesSpamCount)" : '' )?></a><br/>
        -- <a <?=($menu_item == 20 ? $s : $c)?> href="/siteadmin/messages_archive_spam/">����� ����� �� ����</a><br/>
        -- <a <?=($menu_item == 24  ? $s : $c)?> href="/siteadmin/complaints_stats/">���������� �����</a><br/>
        <?php } ?>
        <?php } ?>
        
        <?php if ( $bHasAll || $bHasUsers || $bHasProjects || $bHasBlogs || $bHasCommunes  || $bHasArticles ) { ?>
        <br/>- ���������������� �������<br/>
        -- <a <?=($menu_item == 15 ? $s : $c)?> href="/siteadmin/user_content/?site=choose">������� �����</a><br/>
            <?php if ( $bHasAll || $bHasUsers ) { ?>
        -- <a <?=($menu_item == 18 ? $s : $c)?> href="/siteadmin/user_content/?site=blocked">���������������</a><br/>
            <?php } ?>
            <?php if ( $bHasAll ) { ?>
        -- <a <?=($menu_item == 16 ? $s : $c)?> href="/siteadmin/user_content/?site=shifts">�����</a><br/>
        -- <a <?=($menu_item == 17 ? $s : $c)?> href="/siteadmin/user_content/?site=streams">��������� �������</a><br/>
        -- <a <?=($menu_item == 14 ? $s : $c)?> href="/siteadmin/stop_words">����-�����</a><br/>
            <?php } ?>
        <?php } ?>
        
        <br/>
    <?php } ?>
       
    <? if (hasPermissions('communes')) { ?>- <a href="/siteadmin/ban-razban/?mode=commune" class="blue">����������</a><br/><? } ?>
    <br/>

    
    <h3>�����������������</h3>
    <? if ( $bHasAll || $bHasChangelogin ) { ?>- <a href="/siteadmin/login/" class="blue">��������� ������</a><br/><? } ?>
    <? if ( $bHasAll || $bHasUsers || $bHasProjects || $bHasBlogs || $bHasCommunes ) { ?>- <a href="/siteadmin/proj_reasons/" class="blue">������� �������� ���.</a><br/><br/><? } ?>
    
    <?php if ($bHasAll) { ?>- <a href="/siteadmin/promo_codes/" class="blue">�����-����</a><br/><br/><?php } ?>
    
    <?php if (hasPermissions('adm') && hasPermissions('meta')) { ?>
    - SEO-������<br/>
    -- <a href="/siteadmin/seo/">����-���� ��������</a><br/><br/>
    <?php } ?>

    <?if(hasPermissions('adm') && hasPermissions('ratinglog')){?>
    - <a href="/siteadmin/rating_log/" class="blue">������� ����</a><br/>
    <?}?>
    
    <? if (hasPermissions('adm') && hasPermissions('permissions')) { ?>
    <br/>- ����� �������<br/>
    -- <a href="/siteadmin/permissions/?action=group_list" class="blue">������</a><br/>
    -- <a href="/siteadmin/permissions/?action=user_list" class="blue">������������</a><br/>
    <? } ?>
    
    <? if (hasPermissions('adm') && (hasPermissions('sbr') || hasPermissions('sbr_finance') || hasPermissions('tmppayments') )) { ?>
	  <br/>- ��������������� (���)<br/>
    <? } ?>
      
    <? if (hasPermissions('adm') && (hasPermissions('sbr') || hasPermissions('sbr_finance')  )) { ?>
        --- <a href="/siteadmin/norisk2/?site=docsflow&scheme=1" class="<?=htmlspecialchars($_GET['site'])==docsflow&&$_GET['scheme']==1 ? 'inherit' : 'blue'?>">�����</a><br/>
        --- <a href="/siteadmin/norisk2/?site=docsflow&scheme=2" class="<?=htmlspecialchars($_GET['site'])==docsflow&&$_GET['scheme']==2 ? 'inherit' : 'blue'?>">������</a><br/>
        --- <a href="/siteadmin/norisk2/?site=docsflow&scheme=0" class="<?=htmlspecialchars($_GET['site'])==docsflow&&!$_GET['scheme'] ? ' inherit' : 'blue'?>">���</a><br/>
        --- <a href="/siteadmin/norisk2/?site=stat" class="<?=htmlspecialchars($_GET['site'])=='stat' ? 'inherit' : 'blue'?>">����������</a><br/>
        --- <a href="/siteadmin/norisk2/?site=arbitrage" class="<?=htmlspecialchars($_GET['site'])=='arbitrage' ? 'inherit' : 'blue'?>">��������</a><br/>
        --- <a href="/siteadmin/norisk2/?site=docsflow&scheme=-1" class="<?=htmlspecialchars($_GET['site'])==docsflow&&$_GET['scheme']==-1 ? ' inherit' : 'blue'?>">�����</a><br/>
        --- <a href="/siteadmin/norisk2/?site=1c" class="<?=htmlspecialchars($_GET['site'])=='1c' ? 'inherit' : 'blue'?>">������� � CSV</a><br/>
        --- <a href="/siteadmin/sbr_ito/">���</a><br/>
        --- <a href="/siteadmin/norisk2/?site=invoice" class="<?=htmlspecialchars($_GET['site'])=='invoice' ? 'inherit' : 'blue'?>">���� � ����-�������</a><br/>
    <? } ?>

    <? if (hasPermissions('adm') && (hasPermissions('sbr') || hasPermissions('sbr_finance') || hasPermissions('tmppayments') )) { ?>
      -- <a href="/siteadmin/sbr_stat/" class="blue">���������� �� ���</a><br/>
    <? } ?>
    <? if (hasPermissions('adm') && (hasPermissions('sbr') || hasPermissions('sbr_finance') )) { ?>
      -- <a href="/siteadmin/sbr_reestr" class="blue">������� ��� 1�</a><br/>
      -- <a href="/siteadmin/sbr_reestr?action=import" class="blue">������� ��� �����</a><br/>
	  <br/>
	<? } ?>
	
	<? if(hasPermissions('adm') && hasPermissions('teamfl')) {?>
	  - <a href="/siteadmin/team/" class="blue">������� Free-lance.ru</a><br/><br/>
    <? } ?>
    <? if (hasPermissions('communes')) { ?>
      - <a href="/siteadmin/commune/" class="blue">����������</a><br/><br/>
    <? } ?>

	<? if (hasPermissions('adminspam')) { ?>
	- <a href="/siteadmin/admin/" class="blue">������������� (����)</a><br/>
    <? } ?>
    <? if (hasPermissions('mailer')) { ?>
    - <a href="/siteadmin/mailer/" class="blue">����� ��������</a><br/><br/>
    <? } ?>
    <? if (hasPermissions('stats') || hasPermissions('tmppayments')) { ?>
	- <a href="/siteadmin/stats/" class="blue">����������</a><br/><br/>
	<? } ?>

	<? if (hasPermissions('masssending')) { ?>		
	- <a href="/siteadmin/masssending/" class="blue">������ �� �������� �� ��������<?=($mass_sending_new_cnt ? " ($mass_sending_new_cnt)" : '')?></a><br/><br/>
	<? } ?>

	<? if (hasPermissions('adm') && hasPermissions('seo')) { ?>
	- <a href="/siteadmin/search_kwords/" class="blue">����� �� �����</a><br/><br/>
	<? } ?>
	
    <? if (hasPermissions('adm')  && (hasPermissions('statsaccounts') || hasPermissions('tmppayments')) ) { ?>
	- <a href="/siteadmin/account/" class="blue">���������� (�����)</a><br/>
    <? } ?>
    <? if (hasPermissions('adm')  && hasPermissions('ouraccounts')) { ?>
	- <a href="/siteadmin/staff/" class="blue">���� ��������</a><br/><br/>
	<? } ?>
	

<? if ($bHasAll || $bHasBank) { ?>
- ������<br/>
<? } ?>	
<? if ($bHasAll || $bHasBankalpha) { ?>
-- <a href="/siteadmin/alpha/" class="blue">�����-����</a><br/>
<? } ?> 
<? if ($bHasAll || $bHasPayments) { ?>
-- <a href="/siteadmin/billinvoices/" class="blue">��������� ���������� ��</a><br/>
<? } ?>
<? if ($bHasAll || $bHasBank || $bHasPayments || $bHasBankalpha) { ?>
<br/>
<? } ?>


<? if ($bHasAll || $bHasPayservices) { ?>
- <a href="/siteadmin/rating/" class="blue">�������</a><br/>
<br/>
<? } ?>

<? if ($bHasAll || $bHasAdvstat) { ?>
- ��������� ��-��<br/>
-- <a href="/siteadmin/ban_promo/" class="blue">����� �������</a><br/>
<br/>
<? } ?>


<? if ($bHasAll || $bHasLetters) { ?>
- <a href="/siteadmin/letters/" class="blue">���������������</a><br/>
-- <a href="/siteadmin/letters/?mode=company" class="blue">�������</a><br/>
-- <a href="/siteadmin/letters/?mode=templates" class="blue">�������</a><br/>
<br/>
<? } ?>


<? if ($bHasAll || $bHasOffdocuments) { ?>
<a href="/siteadmin/davupload/?mode=files" class="blue">�������� ������ �� DAV</a><br/>
<br/>
<? }//if?>


<? if ($bHasAll || $bHasTservices) { ?>
- ������� ������<br/>
-- <a href="/siteadmin/tservices/?mode=orders" class="blue">������ ��</a><br/>
<br/>
<? } ?>

<? if ($bHasAll || $bHasNewsletter ) { ?>
- <a href="/siteadmin/newsletter/" class="blue">������� ��� ���������� �������� � ����� ��������</a>
<br/>
<? } ?>

<? if ($bHasAll || $bHasUsers ) { ?>
<br/>
- <a href="/siteadmin/adriver/" class="blue">���������� �������� ���� ��� AdRiver</a>
<br/>
<? } ?>

</div>