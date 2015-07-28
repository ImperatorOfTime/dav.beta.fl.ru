<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } 
$s = 'style="color: #666;"'; 

if ( !isset($aPermissions) ) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/permissions.php");
    $aPermissions = permissions::getUserPermissions( $uid );
}

foreach ( $aPermissions as $sPermission ) {
	$sVar  = 'bHas' . ucfirst( $sPermission );
	$$sVar = true;
}
?>
<?php if ( $bHasAll || $bHasAdm ) { ?>

    <?php if ( $bHasAll || $bHasUsers || $bHasProjects || $bHasBlogs || $bHasCommunes ) { ?>
    <div class="admin-menu">
    	<h3>��������</h3>
    	<ul>
            <?php if ( $bHasAll || $bHasUsers || $bHasProjects || $bHasBlogs || $bHasCommunes ) { ?>
    		<li><a <?=($menu_item == 1 ? $s : '')?> href="/siteadmin/admin_log/?site=log">����� ���� ��������</a></li>
    		<?php } ?>
    		<?php if ( $bHasAll || $bHasUsers ) { ?>
    		<li><a <?=($menu_item == 2 ? $s : '')?> href="/siteadmin/admin_log/?site=user">���������� (��� � ����)</a></li>
    		<?php } ?>
    		<?php if ( $bHasAll || $bHasProjects ) { ?>
    		<li><a <?=($menu_item == 3 ? $s : '')?> href="/siteadmin/admin_log/?site=proj">������� � ��������</a></li>
    		<li><a <?=($menu_item == 10 ? $s : '')?> href="/siteadmin/admin_log/?site=offer">�����������</a></li>
    		<?php } ?>
    	</ul>
    </div>
    <?php } ?>
    
    <?php if ( $bHasAll || $bHasUsers ) { ?>
    <div class="admin-menu">
    	<h3>IP-������</h3>
    	<ul>
    		<li><a <?=($menu_item == 4 ? $s : '')?> href="/siteadmin/user_search/">����� �������������</a></li>
    		<li><a <?=($menu_item == 5 ? $s : '')?> href="/siteadmin/gray_ip">����� ������ IP</a></li>
    	</ul>
    </div>
    <?php } ?>
    
    <?php if ( $bHasAll || $bHasProjects || $bHasUsers ) { ?>
    <div class="admin-menu">
    	<h3>������</h3>
    	<ul>
            <?php if ( $bHasAll || $bHasProjects ) { ?>
    		<li><a <?=($menu_item == 7  ? $s : '')?> href="/siteadmin/ban-razban/?mode=complain">������ �� �������</a></li>
    		<?php } ?>
    		<?php if ( $bHasAll || $bHasUsers ) { ?>
    		<li><a <?=($menu_item == 11 ? $s : '')?> href="/siteadmin/messages_spam">������ �� ����</a></li>
    		<?php } ?>
    	</ul>
    </div>
    <?php } ?>
    
    <?php if ( $bHasAll ) { ?>
    <div class="admin-menu">
    	<h3>����������</h3>
    	<ul>
    		<li><a <?=($menu_item == 8 ? $s : '')?> href="/siteadmin/admin_log/?site=stat">��� ����������</a></li>
    	</ul>
    </div>
    <?php } ?>
    
    <?php if ( $bHasAll || $bHasUsers || $bHasProjects ) { ?>
    <div class="admin-menu">
    	<h3>���������</h3>
    	<ul>
    		<li><a <?=($menu_item == 9 ? $s : '')?> href="/siteadmin/admin_log/?site=notice">�����������</a></li>
    	</ul>
    </div>
    <?php } ?>
<?php } ?>