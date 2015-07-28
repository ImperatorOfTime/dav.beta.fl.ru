<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/commune.common.php");
$xajax->printJavascript('/xajax/');


global $id, $uid, $page, $om, $g_page_id, $user_mod, $rating;

$group_id = __paramInit('int', 'gr', NULL);

$gr_prm = $group_id === NULL ? '' : "gr={$group_id}&";
$s_prm = !$search ? '' : "&search={$search}";


$allCommCnt  = 0; // ����� ���������.
$pageCommCnt = 0; // ���������� ��������� �� ������ ��������.
// �������.
if (!($commune_groups = commune::GetGroups()))
    $commune_groups = array();

// ������ ��������� ��������� ��� ����� ����������
$start_position = ($page - 1) * $limit;

$pageCommCnt = count($communes);

// ������ ��������.
$bmCls = getBookmarksStyles(commune::OM_CM_COUNT, $om);
?>

<div class="b-community">
<? include ('in_out_dialog.php');?>
<? seo_start();?>  
<a class="b-button b-button_flat b-button_flat_green b-button_float_right b-button_margbot_-10"  href="?site=Create">������� ����������</a>
<?= seo_end();?>  


<?php
$crumbs = array();
if(!$gr_id) {
    //$crumbs[] = array("title"=>"����������", "url"=>"");
} else {
    $crumbs[] = array("title"=>"����������", "url"=>"/commune/");
    $crumbs[] = array("title"=>$sGroup, "url"=>"");
}
?>
<? /*= $gr_id ? getCrumbs($crumbs, "commune") : '<h1 class="b-page__title">���������� �����������</h1>' */?>
<?= getCrumbs($crumbs, "commune")?>
<h1 class="b-page__title">���������� �����������</h1>

<? ob_start(); ?>
<div class="b-menu b-menu_line b-menu_clear_both">
    <ul class="b-menu__list b-menu__list_padleft_28ps" data-menu="true" data-menu-descriptor="community-list">
        <li class="b-menu__item<?= $bmCls[commune::OM_CM_BEST] ?>" <?=((!$bmCls[commune::OM_CM_BEST] || $page > 1) ? '' : 'data-menu-opener="true" data-menu-descriptor="community-list" ')?>>
            <a href="?om=" class="b-menu__link "><span class="b-menu__b1">������</span></a>
        </li>
        <? seo_start();?>  
        <li class="b-menu__item<?= $bmCls[commune::OM_CM_POPULAR] ?>" <?=((!$bmCls[commune::OM_CM_POPULAR] || $page > 1) ? '' : 'data-menu-opener="true" data-menu-descriptor="community-list" ')?>>
    		<a href="?om=<?= commune::OM_CM_POPULAR ?>" class="b-menu__link "><span class="b-menu__b1">����������</span></a>
        </li>
        <li class="b-menu__item<?= $bmCls[commune::OM_CM_ACTUAL] ?>" <?=((!$bmCls[commune::OM_CM_ACTUAL] || $page > 1) ? '' : 'data-menu-opener="true" data-menu-descriptor="community-list" ')?>>
           	<a href="?om=<?= commune::OM_CM_ACTUAL ?>" class="b-menu__link "><span class="b-menu__b1">����������</span></a>
        </li>
        <li class="b-menu__item<?= $bmCls[commune::OM_CM_NEW] ?>" <?=((!$bmCls[commune::OM_CM_NEW] || $page > 1) ? '' : 'data-menu-opener="true" data-menu-descriptor="community-list" ')?>>
           	<a href="?om=<?= commune::OM_CM_NEW ?>" class="b-menu__link "><span class="b-menu__b1">�����</span></a>
        </li>
        <?php if(get_uid(false)) { ?>
        <li class="b-menu__item<?= $bmCls[commune::OM_CM_MY] ?>" <?=((!$bmCls[commune::OM_CM_MY] || $page > 1) ? '' : 'data-menu-opener="true" data-menu-descriptor="community-list" ')?>>
            <a href="?om=<?= commune::OM_CM_MY ?>" class="b-menu__link "><span class="b-menu__b1">� ������</span></a>
        </li>
        <li class="b-menu__item<?= $bmCls[commune::OM_CM_JOINED] ?>" <?=((!$bmCls[commune::OM_CM_JOINED] || $page > 1) ? '' : 'data-menu-opener="true" data-menu-descriptor="community-list" ')?>>
            <a href="?om=<?= commune::OM_CM_JOINED ?>" class="b-menu__link "><span class="b-menu__b1">� �������</span></a>
        </li>
        <?php } ?>
        <li class="b-menu__item b-menu__item_promo b-page__desktop"><?php require_once($_SERVER['DOCUMENT_ROOT'] . "/banner_promo.php"); ?></li>
        <?= seo_end();?>  
    </ul>
</div>


<div class="b-layout b-layout_padtop_20">    
    <div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
		<? seo_start();?>  
        <div class="b-search b-layout b-layout_padbot_30">
            <form id="search_frm" method="get" action=".">
            <table cellspacing="0" cellpadding="0" class="b-search__table">
                <tr class="b-search__tr">
                    <td class="b-search__input">
                            <div class="b-input b-input_height_24">
                                <input type="text" name="search" class="b-input__text" id="b-input" value="<?= htmlspecialchars(stripslashes($_GET['search'])); ?>" placeholder="<?php if(!$_GET['search']) { ?>����� ����������, ����, �����������<?php } else {  htmlspecialchars(stripslashes($_GET['search']));  } ?>">
                                <input type="hidden" name="om" value="<?= $om;?>">
                            </div>
                    </td>
                    <td class="b-search__button b-search__button_padleft_10">
                        <a href="javascript:void(0)" onclick="$('search_frm').submit()" class="b-button b-button_flat b-button_flat_grey">�����</a>
                    </td>
                </tr>
            </table>
            </form>
        </div>
        <?= seo_end();?> 
         
            <? $is_empty_commune = (count($communes) == 0);
            if ($om) { ?>
            <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_bold">
            <?php
                switch ($om) {
                    case commune::OM_CM_BEST : print(!$is_empty_commune ? '������� ����� ���������� � ����� ������� ���������' : '��������� ���');
                        break;
                    case commune::OM_CM_POPULAR : print(!$is_empty_commune ? '������� ����� ���������� � ���������� ����������� ����������' : '��������� ���');
                        break;
                    case commune::OM_CM_ACTUAL: print(!$is_empty_commune ? '������� ����� ����������, � ������� ������� ����������� ����������' : '��������� ���');
                        break;
                    case commune::OM_CM_NEW : print(!$is_empty_commune ? '������� ����� ����������, ��������� �����' : '��������� ���');
                        break;
                    case commune::OM_CM_MY : print(!$is_empty_commune ? '����������, ������� �� �������' : '�� ��� �� ������� �� ������ ����������' );
                        break;
                    case commune::OM_CM_JOINED : print(!$is_empty_commune ? '������� ����� ����������, � ������� �� �������� �����' : '�� ��� �� �������� �� � ���� ����������');
                        break;
                }
			?>
           </div>
            <? } ?>
        
        <?php if ($om == commune::OM_CM_JOINED && !$is_empty_commune) { 
            $href = '/commune/?'.$gr_prm.($om ? 'om='.$om : ''); ?>
            <div class="b-layout__txt b-layout__txt_padbot_20">
                �������������
                <div class="b-filter" style="z-index: 10; ">
                    <div class="b-filter__body">
                        <a href="#" class="b-filter__link b-filter__link_ie7_top_3 b-filter__link_dot_0f71c8 b-layout__link_fontsize_13">
                            <?
                                switch ($sub_om) {
                                    case commune::OM_CM_JOINED_ACCEPTED : print('�� ���� ����������');
                                        break;
                                    case commune::OM_CM_JOINED_CREATED : print('�� ���� �������� ����������');
                                        break;
                                    case commune::OM_CM_JOINED_BEST : print('�� �������� ����������');
                                        break;
                                    case commune::OM_CM_JOINED_LAST : print('�� ���� ��������� ���� � ����������');
                                        break;
                                    case commune::OM_CM_JOINED_MY : print('�� ���� �������������');
                                        break;
                                }
                            ?>
                        </a>
                    </div>
                    <div class="b-shadow b-shadow_marg_-32 b-filter__toggle b-filter__toggle_hide">
                                        <div class="b-shadow__body b-shadow__body_pad_15 b-shadow__body_bg_fff">
                                            <ul class="b-filter__list">
                                                <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15">
                                                    <a class="b-filter__link<? if ($sub_om != commune::OM_CM_JOINED_ACCEPTED) { ?> b-filter__link_dot_0f71c8<? } else { ?> b-filter__link_no<? } ?>" onclick="window.location='<?=$href?>&sub_om=<?=commune::OM_CM_JOINED_ACCEPTED?>'">�� ���� ����������</a>
                                                    <span class="b-filter__marker b-filter__marker_top_4 b-filter__marker_galka<? if ($sub_om != commune::OM_CM_JOINED_ACCEPTED) { ?> b-filter__marker_hide<? } ?>"></span>
                                                </li>
                                                <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15">
                                                    <a class="b-filter__link<? if ($sub_om != commune::OM_CM_JOINED_CREATED) { ?> b-filter__link_dot_0f71c8<? } else { ?> b-filter__link_no<? } ?>" onclick="window.location='<?=$href?>&sub_om=<?=commune::OM_CM_JOINED_CREATED?>'">�� ���� �������� ����������</a>
                                                    <span class="b-filter__marker b-filter__marker_top_4 b-filter__marker_galka<? if ($sub_om != commune::OM_CM_JOINED_CREATED) { ?> b-filter__marker_hide<? } ?>"></span>
                                                </li>
                                                <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15">
                                                    <a class="b-filter__link<? if ($sub_om != commune::OM_CM_JOINED_BEST) { ?> b-filter__link_dot_0f71c8<? } else { ?> b-filter__link_no<? } ?>" onclick="window.location='<?=$href?>&sub_om=<?=commune::OM_CM_JOINED_BEST?>'">�� �������� ����������</a>
                                                    <span class="b-filter__marker b-filter__marker_top_4 b-filter__marker_galka<? if ($sub_om != commune::OM_CM_JOINED_BEST) { ?> b-filter__marker_hide<? } ?>"></span>
                                                </li>
                                                <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15">
                                                    <a class="b-filter__link<? if ($sub_om != commune::OM_CM_JOINED_LAST) { ?> b-filter__link_dot_0f71c8<? } else { ?> b-filter__link_no<? } ?>"onclick="window.location='<?=$href?>&sub_om=<?=commune::OM_CM_JOINED_LAST?>'">�� ���� ��������� ���� � ����������</a>
                                                    <span class="b-filter__marker b-filter__marker_top_4  b-filter__marker_galka<? if ($sub_om != commune::OM_CM_JOINED_LAST) { ?> b-filter__marker_hide<? } ?>"></span>
                                                </li>
                                                <li class="b-filter__item b-filter__item_padbot_3 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15">
                                                    <a class="b-filter__link<? if ($sub_om != commune::OM_CM_JOINED_MY) { ?> b-filter__link_dot_0f71c8<? } else { ?> b-filter__link_no<? } ?>"onclick="window.location='<?=$href?>&sub_om=<?=commune::OM_CM_JOINED_MY?>'">�� ���� �������������</a>
                                                    <span class="b-filter__marker b-filter__marker_top_4  b-filter__marker_galka<? if ($sub_om != commune::OM_CM_JOINED_MY) { ?> b-filter__marker_hide<? } ?>"></span>
                                                </li>
                                            </ul>
                                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
                
        <? //__commPrintPage( $page, $communes, $groupCommCnt, $sub_om, $search )?>
        <? include(ABS_PATH . "/commune/tpl.communes_list.php"); ?>
        <?//= ($is_empty_commune && $om !== commune::OM_CM_MY ? '<div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_bold">��������� ���</div>' : '') ?>

        <?
        $uq = array();
        if($om) $uq[] = "om={$om}";
        if($rating) $uq[] = "rating={$rating}";
        if($gr_prm) $uq[] = $gr_prm;
        if($s_prm) $uq[] = $s_prm;
        $url_p = "%s/commune/?".implode("&", $uq).(count($uq) ? '&' : '')."page=%d%s";
        echo new_paginator($page, $pages, 3, $url_p);
        ?>
    </div>
    
    
    <div class="b-layout__left b-layout__left_width_25ps">
        <div class="b-menu b-menu_vertical b-menu_padbot_20">
            <? if (!$om) { ?>
            <? seo_start();?>  
            <ul class="b-menu__list">
                <li class="b-menu__item b-menu__item_padbot_5">
                    <div class="b-menu__number b-menu__number_fontsize_11">
                    </div>
                    <? if ($rating) { ?>
                        <a class="b-menu__link" href="?rating=">C ����� ���������</a>
                    <? } else { ?>
                        <a class="b-menu__link b-menu__h" style="color: #000;" href="?rating=">C ����� ���������</a>
                    <? } ?>
                </li>
                <li class="b-menu__item b-menu__item_padbot_5">
                    <div class="b-menu__number b-menu__number_fontsize_11">
                    </div>
                    <? if ($rating != 'bronze') { ?>
                        <a class="b-menu__link" href="?rating=bronze">���������</a>
                        <span class="b-menu__txt b-menu__txt_fontsize_11">&nbsp;� ��������� �� 50</span>
                    <? } else { ?>
                        <a class="b-menu__link b-menu__h" style="color: #000;" href="?rating=bronze">���������</a>
                    <? } ?>
                </li>
                <li class="b-menu__item b-menu__item_padbot_5">
                    <div class="b-menu__number b-menu__number_fontsize_11">
                    </div>
                    <? if ($rating != 'silver') { ?>
                        <a class="b-menu__link" href="?rating=silver">����������</a>
                        <span class="b-menu__txt b-menu__txt_fontsize_11">&nbsp;�� 200</span>
                    <? } else { ?>
                        <a class="b-menu__link b-menu__h" style="color: #000;" href="?rating=silver">����������</a>
                    <? } ?>
                </li>
                <li class="b-menu__item b-menu__item_padbot_5">
                    <div class="b-menu__number b-menu__number_fontsize_11">
                    </div>
                    <? if ($rating != 'gold') { ?>
                        <a class="b-menu__link" href="?rating=gold">�������</a>
                        <span class="b-menu__txt b-menu__txt_fontsize_11">&nbsp;�� 1000</span>
                    <? } else { ?>
                        <a class="b-menu__link b-menu__h" style="color: #000;" href="?rating=gold">�������</a>
                    <? } ?>
                </li>
            </ul>
            <?= seo_end();?>  
            <? } ?>
        </div>
        <div class="b-menu b-menu_width_240 b-menu_vertical b-menu_padbot_20">
            <ul class="b-menu__list">
                <?
                $html = '';
                $i = 0;
                $gCnt = count($commune_groups);
                for ($i; $i < $gCnt; $i++) {
                    $grp = $commune_groups[$i];
                    $allCommCnt += (int) $grp['a_count'];
                    $cnt = $grp['a_count'] ? " {$grp['a_count']}" : '';
                    $cls = $i == $gCnt - 1 ? ' class="last"' : '';
                    $html .= '<li class="b-menu__item b-menu__item_padbot_5">' .
                            (
                            //$group_id != $grp['id'] || ($group_id == $grp['id'] && $page > 1) ? "<a ". (($group_id == $grp['id'] && $page > 1) ? ' style="font-weight: bolder; color: #666;"' : '') ." href='".getFriendlyURL('commune_group', $grp['id']).'?'.($om ? "&om={$om}" : '') . ($rating ? '&rating=' . $rating : '') . "'>{$grp['name']}{$cnt}</a>" : "<strong>{$grp['name']}{$cnt}</strong>"
                            $group_id != $grp['id'] || ($group_id == $grp['id'] && $page > 1) ?
                                '<div class="b-menu__number b-menu__number_fontsize_11">'.$cnt.'</div>'.
                                '<a class="b-menu__link"'.
                                " href='".getFriendlyURL('commune_group', $grp['id']).
                                (($om || $rating)? ('?'.($om ? "&om={$om}" : '').($rating ? '&rating=' . $rating : '')): '').
                                "'>{$grp['name']}</a>" 
                                : 
                                '<div class="b-menu__number b-menu__number_fontsize_11">'.$cnt.'</div>'.
                                '<a class="b-menu__link b-menu__h" style="color: #000;"'.
                                " href='".getFriendlyURL('commune_group', $grp['id']).
                                (($om || $rating)? ('?'.($om ? "&om={$om}" : '').($rating ? '&rating=' . $rating : '')): '').
                                "'>{$grp['name']}</a>" 
                            ) .
                            "</li>";
                }
                $cnt = $allCommCnt ? " {$allCommCnt}" : '';
                ?>
                <li class="b-menu__item b-menu__item_padbot_5">
                    <div class="b-menu__number b-menu__number_fontsize_11"><?= $cnt ?></div>
                    <? if ( $group_id === NULL && $page == 1 ) { ?>
                        <a class="b-menu__link b-menu__h" style="color: #000;" href="/commune/<?= ($om ? '?om='.$om : '') ?>">��� ����������</a>
                    <? } else { ?>
                        <a class="b-menu__link" href="/commune/<?= ($om ? '?om='.$om : '') ?>">��� ����������</a>
                    <? } ?>
                </li>
                <?= $html ?>
            </ul>
        </div>
        
        <div class="b-layout b-layout_width_240">
            <!-- Banner 240x400 -->
            <?= printBanner240(false, true);?>
            <!-- end of Banner 240x400 -->
        </div>
    </div>
            
</div>
<a id="upper" class="b-page__up" href="#" style=" visibility:hidden;"></a>

</div>