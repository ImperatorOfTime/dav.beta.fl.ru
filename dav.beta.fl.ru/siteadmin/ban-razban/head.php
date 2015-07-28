<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
<script type="text/javascript">

var hm_opened = false;
var hm_interval = null;

function hm_open(num) {
    if (hm_interval) {
        clearInterval(hm_interval);
        hm_interval = null;
    }
    if (!hm_opened) {
        hm_opened = true;
        document.getElementById('in-stat-menu'+num).style.display = '';
    }
}

function hm_close(num) {
    hm_interval = setTimeout("hm_opened = false; document.getElementById('in-stat-menu"+num+"').style.display = 'none';", 300);
}

</script>

<table cellpadding="0" cellspacing="0" border="0" class="razban-header">

<tr>
    <td class="caption">���-������</td>
    <td class="search" align="right">
    <form action="./" method="get">
    <input name="mode" value="<?=(in_array($mode, array('users', 'blogs', 'projects', 'commune', 'complain','offers','sdelau'))? $mode: 'users')?>" type="hidden">
    <input type="hidden" id="log_pp" name="log_pp" value="<?=$log_pp?>">
    <? if ($mode != 'moders') { ?><input name="ft" value="<?=$ft?>" type="hidden"><? } ?>
    <?=($admin? '<input name="admin" value="'.$admin.'" type="hidden">': '')?>
    <?=(($mode == 'complain') ? '<input name="group" value="'.$group.'" type="hidden">': '')?>
    <table cellpadding="0" cellspacing="0" border="0" align="right">
    <tr>
        <td>
        <input name="search" style="width: 300px" type="text" value="<?=($search? $search: '�����...')?>" class="search-str"<? if (!$search) { ?> onfocus="if(this.value=='�����...'){this.value=''}" onblur="if(this.value==''){this.value='�����...'}"<? } ?> />
		<input type="submit" value="�����" />
        </td>
    </tr>
    <tr>
        <td class="sort-str">
		<? if ($mode == 'moders') { ?>
            <span>��������</span>
            <span><? if ($ft == 2) { ?><strong>��������</strong><? } else { ?><a href="./?mode=moders&ft=2&log_pp=<?=$log_pp?>">��������</a><? } ?></span>
            <span><? if ($ft != 2) { ?><strong>�� ��������</strong><? } else { ?><a href="./?mode=moders&log_pp=<?=$log_pp?>">�� ��������</a><? } ?></span>
        <? } else { ?>
            <span>����������� ��</span>
            <span><? if ($sort == 'btime') { ?><strong>���� <?=(($mode == 'users' && ($ft == 3 || $ft == 4))? '����.��������������': '����������')?></strong><? } else { ?><a href="./?mode=<?=$mode?>&log_pp=<?=$log_pp?>&sort=btime<?=($ft? "&ft=$ft": "")?><?=($search? "&search=$search": "")?><?=($admin? "&admin=$admin": "")?><?=(($mode == 'complain')? "&group=$group": "")?>">���� <?=(($mode == 'users' && ($ft == 3 || $ft == 4))? '����.��������������': '����������')?></a><? } ?></span>
            <? if ($mode == 'users' && $ft != 3 && $ft != 4) { ?>
            <span><? if ($sort == 'utime') { ?><strong>���� �������������</strong><? } else { ?><a href="./?mode=<?=$mode?>&log_pp=<?=$log_pp?>&sort=utime<?=($ft? "&ft=$ft": "")?><?=($search? "&search=$search": "")?><?=($admin? "&admin=$admin": "")?><?=(($mode == 'complain')? "&group=$group": "")?>">���� �������������</a><? } ?></span>
            <? } ?>
            <span><? if ($sort == 'login') { ?><strong>������</strong><? } else { ?><a href="./?mode=<?=$mode?>&log_pp=<?=$log_pp?>&sort=login<?=($ft? "&ft=$ft": "")?><?=($search? "&search=$search": "")?><?=($admin? "&admin=$admin": "")?><?=(($mode == 'complain')? "&group=$group": "")?>">������</a><? } ?></span>
        <? } ?>
        </td>
    </tr>
    </table>
    </form>
    </td>
</tr>

<? $ustat = users::GetBannedStat() ?>

<tr>
    <td colspan="2" class="all-stat" style="padding-top: 10px;">
    <table cellpadding="0" cellspacing="0" border="0" class="in-stat">
    <tr>
        <td>
            <a href="/siteadmin/ban-razban/?mode=users" onmouseover="hm_open(1)" onmouseout="hm_close(1)">����������</a> <span><?=$ustat['all']?></span><br>
            <table cellpadding="0" cellspacing="0" border="0" class="in-stat-menu" style="display: none; width: 160px" id="in-stat-menu1" onmouseover="hm_open(1)" onmouseout="hm_close(1)">
            <tr><td><a href="/siteadmin/ban-razban/?mode=users&ft=1">���������� �� �����</a> <span><?=$ustat['site']?></span></td></tr>
            <tr><td><a href="/siteadmin/ban-razban/?mode=users&ft=2">���������� � ������</a> <span><?=$ustat['blogs']?></span></td></tr>
            <tr><td><a href="/siteadmin/ban-razban/?mode=users&ft=3">� ����������������</a> <span><?=$ustat['warns']?></span></td></tr>
            </table>
        </td>
        <td><? if ($mode == 'blogs') { ?><strong>�����</strong><? } else { ?><a href="/siteadmin/ban-razban/?mode=blogs">�����</a><? } ?> <span><?=blogs::NumsBlockedThreads()?></span></td>
        <td>
            <? if ($mode == 'projects') { ?><strong onmouseover="hm_open(2)" onmouseout="hm_close(2)">�������</strong><? } else { ?><a href="/siteadmin/ban-razban/?mode=projects" onmouseover="hm_open(2)" onmouseout="hm_close(2)">�������</a><? } ?> <span><?=projects::NumsBlockedProjects()?></span><br>
            <table cellpadding="0" cellspacing="0" border="0" class="in-stat-menu" style="display: none; width: 160px" id="in-stat-menu2" onmouseover="hm_open(2)" onmouseout="hm_close(2)">
            <tr><td><a href="/siteadmin/ban-razban/?mode=complain">������ �� ������</a></td></tr>
            </table>
        </td>
        <td>
            <? if ($mode == 'sdelau') { ?><strong onmouseover="hm_open(3)" onmouseout="hm_close(3)">����������� "������"</strong><? } else { ?><a href="/siteadmin/ban-razban/?mode=sdelau" onmouseover="hm_open(3)" onmouseout="hm_close(3)">����������� "������"</a><? } ?> <span><?=freelancer_offers::GetCountFreelancerBlockedOffers()?></span><br>
            <table cellpadding="0" cellspacing="0" border="0" class="in-stat-menu" style="display: none; width: 160px" id="in-stat-menu3" onmouseover="hm_open(3)" onmouseout="hm_close(3)">
            <tr><td><a href="/siteadmin/ban-razban/?mode=offers">������ �� �����������</a></td></tr>
            </table>
        </td>
        <td><? if ($mode == 'commune') { ?><strong>����������</strong><? } else { ?><a href="/siteadmin/ban-razban/?mode=commune">����������</a><? } ?> <span><?=commune::NumsBlockedCommunes()?></span></td>
    </tr>
    </table>
    </td>
</tr>

</table>
<?if($mode == 'complain'):?>
<!-- ������ "�����|��������|�����������" -->
<div class="b-menu b-menu_tabs b-menu_relative margtop_20 padtop_10 padbot_5">
	<ul class="b-menu__list">
		<li class="b-menu__item<?=(($group == 'new') ? ' b-menu__item_active' : '')?>"><a class="b-menu__link" href="<?=("/siteadmin/ban-razban/?mode=$mode".($page? "&p=$page": '').($search? "&search=$search": '').($admin? "&admin=$admin": '').($sort? "&sort=$sort": ''))?>&group=new"><span class="b-menu__b1">�����</span></a></li>
		<li class="b-menu__item<?=(($group == 'approved') ? ' b-menu__item_active' : '')?>"><a class="b-menu__link" href="<?=("/siteadmin/ban-razban/?mode=$mode".($page? "&p=$page": '').($search? "&search=$search": '').($admin? "&admin=$admin": '').($sort? "&sort=$sort": ''))?>&group=approved"><span class="b-menu__b1">��������</span></a></li>
		<li class="b-menu__item<?=(($group == 'refused') ? ' b-menu__item_active' : '')?>"><a class="b-menu__link" href="<?=("/siteadmin/ban-razban/?mode=$mode".($page? "&p=$page": '').($search? "&search=$search": '').($admin? "&admin=$admin": '').($sort? "&sort=$sort": ''))?>&group=refused"><span class="b-menu__b1">�����������</span></a></li>
	</ul>
</div>
<!--// ������ "�����|��������|�����������" -->
<?endif?>
