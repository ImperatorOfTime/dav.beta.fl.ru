
    <a name="contest-view"></a>
	<div class="contest-project-view ">

<!--
		<div class="cpv-budjet">
		<table>
		<tr class="cpv-budjet-main">
			<th>������:</th>
			<td><?=CurToChar($project['cost'], $project['currency'])?></td>
		</tr>
		<tr>
			<th rowspan="3">���:</th>
			<td>
				<?=(($project['currency'] != 0)? CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($project['cost'] * $project_exRates[trim($translate_exRates[$project['currency']]) . '2'], 2))), 0).'<br />': "")?>
				<?=(($project['currency'] != 1)? CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($project['cost'] * $project_exRates[trim($translate_exRates[$project['currency']]) . '3'], 2))), 1).'<br />': "")?>


				<?=(($project['currency'] != 2)? CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($project['cost'] * $project_exRates[trim($translate_exRates[$project['currency']]) . '4'], 2))), 2).'<br />': "")?>
				<?=(($project['currency'] != 3)? CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($project['cost'] * $project_exRates[trim($translate_exRates[$project['currency']]) . '1'], 2))), 3).'<br />': "")?>
			</td>
		</tr>
		</table>
		</div>
-->
		<span class="contest-ea"><?=($show_info? view_avatar($project['login'], $project['photo']): '<img src="/images/user-default-small.png" alt="" width="50" height="50" class="lpl-avatar">')?></span>
        <div class="const-head">
		<div class="contest-body">
    <?php $can_change_prj = hasPermissions("projects"); ?>

    <? if ($project['cost'] != 0) { ?>
  <div id="budget_block" class="b-layout__txt b-layout__txt_float_right b-layout__txt_fontsize_18 b-layout__txt_fontsize_13_iphone">������: 
                  <?php
                  switch ($project['priceby']) {
                      case '1':
                          $priceby_str = "/���";
                          break;
                      case '2':
                          $priceby_str = "/����";
                          break;
                      case '3':
                          $priceby_str = "/�����";
                          break;
                      case '4':
                          $priceby_str = "/������";
                          break;
                      default:
                          $priceby_str = "";
                          break;
                  }
                  if ($project['cost'] == '' || $project['cost'] == 0) {
                      $priceby_str = "";
                  }
                  $project['price_display'] = CurToChar($project['cost'], $project['currency']) . $priceby_str;
                  ?>
                     <? if($can_change_prj) { ?>
                        <a id="prj_budget_lnk_<?=$project['id']?>" class="b-layout__link b-layout__link_bordbot_dot_000 b-layout__link_bold" href="#" onClick="popupShowChangeBudget(<?=$project['id']?>, '<?=$project['cost']?>', <?=$project['currency']?>, <?=$project['priceby']?>, false, <?=$project['id']?>, 2); return false;"><?= CurToChar($project['cost'], $project['currency']) ?><?= $priceby_str ?></a>
                     <? } else { ?>
                        <span class="b-layout__bold"><?= CurToChar($project['cost'], $project['currency']) ?><?= $priceby_str ?></span>
                     <? } ?>
  </div>
  <? } else { ?>
  <?php if ($project['pro_only'] == 't' && $_SESSION["uid"]) {?>
      <div class="b-layout__txt b-layout__txt_padbot_20">������ ��� <span class="b-icon b-icon__pro b-icon__pro_f" alt="������� �������" title="������� �������"></span></div>
  <? }?>
 <div id="budget_block" class="b-layout__txt b-layout__txt_float_right b-layout__txt_fontsize_18 b-layout__txt_fontsize_13_iphone">������ 
                         <? if($can_change_prj) { ?>
                             <a id="prj_budget_lnk_<?=$project['id']?>" class="b-layout__link b-layout__link_bordbot_dot_000 b-layout__link_bold" href="#" onClick="popupShowChangeBudget(<?=$project['id']?>, '', 0, 1, true, <?=$project['id']?>, 2); return false;">�� ��������������</a>
                         <? } else { ?>
                             <span class="b-layout__bold">�� ��������������</span>
                         <? } ?>
 </div>
  <? } ?>
			<div class="contest-e">
                <span style="color: #6BB24B; font-weight: bold;"><a href="/users/<?=$project['login']?>" class="employer-name" title="<?=($project['uname']." ".$project['usurname'])?>"><?=($project['uname']." ".$project['usurname'])?></a> [<a href="/users/<?=$project['login']?>" class="employer-name" title="<?=$project['login']?>"><?=$project['login']?></a>] <?= view_mark_user($project)?> <?=($project['completed_cnt'] > 0 ?'<a class="b-layout__link" href="/promo/bezopasnaya-sdelka/" title="������������ ������� ����� ���������� ������" target="_blank"><span class="b-icon b-icon__shield"></span></a>':'' ) ?></span>
                <?=dateFormat("[d.m.Y | H:i]", $project['create_date'])?>
                <?=($project['post_date'] !== $project['create_date']) ? dateFormat("[������: d.m.Y | H:i]", $project['post_date']) : ""?>
                <?=(($project['edit_date'])?dateFormat("[������� ���������: d.m.Y | H:i]", $project['edit_date']):"")?>
                
                <? if(hasPermissions('projects')): ?><b style="color:#ff0000"><nobr>�������</nobr></b><? endif; ?>
			</div>
            <? if (get_uid(false) && $project['user_id'] != get_uid(0)) {
                include_once($_SERVER['DOCUMENT_ROOT'].'/projects/tpl.note.php');
            } ?>
<!-- ADD HTML -->
        <?
        setlocale(LC_ALL, 'ru_RU.CP1251');
        $registered    = strtolower(ElapsedMnths(strtotime($project['reg_date'])));
        setlocale(LC_ALL, 'en_US.UTF-8');
        ?>
		    <div class="margtop_5"><b>���������������<?= $user->sex === 'f' ? '�' : '' ?> �� ����� <?=$registered?></b></div>
		    <div class="margtop_5"><span class="margright_39">���������� ������:</span><?=(int)$project['completed_cnt']?></div>
		    <?php if(get_uid() && $show_info) { ?>
            <div class="margtop_2">
            	<span class="first margright_20">������ �����������:</span>
                <span class="r_positive"><a href="/users/<?=$project['login']?>/opinions/?sort=1#op_head">+&nbsp;<?= (int)($op_data['frl_total']['p'])?></a></span>
                <span class="r_neutral"><a href="/users/<?=$project['login']?>/opinions/?sort=2#op_head"><?= (int)($op_data['frl_total']['n'])?></a></span>
                <span class="r_negative"><a href="/users/<?=$project['login']?>/opinions/?sort=3#op_head">-&nbsp;<?= (int)($op_data['frl_total']['m'])?></a></span>
            </div>
            <?php } else {?>
            <div class="margtop_2">
            	<span class="first margright_20">������ �����������:</span>
                <span class="r_positive">+&nbsp;<?= (int)($op_data['frl_total']['p'])?></span>
                <span class="r_neutral"><?= (int)($op_data['frl_total']['n'])?></span>
                <span class="r_negative">-&nbsp;<?= (int)($op_data['frl_total']['m'])?></span>
            </div>
            <?php }?>
         </div>
      </div>
		<div class="contest-body">
    <? if ($project['cost'] != 0) { ?>
                 <?php 
                 $cfile = new cfile($project["logo_id"]);
                 if ( $cfile->id ) {?>
                         <?php if(trim($project["link"]) != '') { ?>
                         <a target="_blank" rel="nofollow" href="http://<?= ltrim($project["link"], 'http://');?>" class="b-post__link">
                             <img alt="" src="<?= WDCPREFIX."/".$cfile->path."/".$cfile->name ?>" class="b-post__pic b-post__pic_float_right b-post__pic_clear_right b-post__pic_margleft_10">
                         </a>
                         <?php } else {//if ?>
                         <img alt="" src="<?= WDCPREFIX."/".$cfile->path."/".$cfile->name ?>" class="b-post__pic b-post__pic_float_right b-post__pic_clear_right b-post__pic_margleft_10">
                         <?php }//else?>
                  <? }?>          
  <? } else { ?>
                                 
	<?php 
                 $cfile = new cfile($project["logo_id"]);
                 if ( $cfile->id && trim($project["link"]) ) {?>
                         <a target="_blank" rel="nofollow" href="<?=$project["link"]?>" class="b-post__link">
                             <img alt="" src="<?= WDCPREFIX."/".$cfile->path."/".$cfile->name ?>" class="b-post__pic b-post__pic_float_right b-post__pic_clear_right b-post__pic_margleft_10">
                         </a>
                  <? }?>
  <? } ?>
<!--// ADD HTML -->
<div class="b-layout__txt b-layout__txt_padbot_20">
			<?php $sTitle = $project['moderator_status'] === '0' && $project['is_pro'] != 't' ? $stop_words->replace($project['name']) : $project['name']; ?>
            <?
            // ��������� �������������� ���������
            $descr = $project['moderator_status'] === '0' && $project['is_pro'] != 't' ? $stop_words->replace($project['descr']) : $project['descr'];
            $descr = preg_replace("/^ /","\x07",$descr);
            $descr = preg_replace("/(\n) /","$1\x07",$descr);
            $descr = reformat($descr, 30, 0, 0, 1);
            $descr = preg_replace("/\x07/","&nbsp;",$descr);
            echo $descr;
            ?>
</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold">�������:</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_11 "><?=$isPreview ? $project['spec_txt'] : projects::_getSpecsStr($project_specs,' / ', ', ', true);?></div>
                        <?php if ($project['ico_payed']=='t' || $project['is_upped'] == 't'){?><div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20">�������</div><?php } //if?>
                        
                        
                        <?php if( get_uid(false) && ( is_pro() || $project['user_id'] == get_uid(false) || $project['is_pro'] == 't' || $isPreview) && trim($project['contacts']) != '' && !$is_contacts_employer_empty) {?>
                        <div class="b-layout <?= $isPreview ? "" : ""?> b-layout_padbot_20 b-layout_padtop_15">
                            <table class="b-layout__table b-layout__table_width_full b-project-contacts-collection  ">
                               <tr class="b-layout__tr">
                                  <td class="b-layout__one b-layout__one_padbot_10 b-layout__one_bordbot_ccc b-layout__one_width_130"><div class="b-layout__txt ">��������:</div></td>
                                  <td class="b-layout__one b-layout__one_padbot_10 b-layout__one_bordbot_ccc"></td>
                               </tr>
                               <?php foreach($contacts_employer as $name=>$contact) { if(trim($contact['value']) == '') continue;?>
                               <tr class="b-layout__tr">
                                  <td class="b-layout__one b-layout__one_padtb_5 b-layout__one_bordbot_ccc b-layout__one_width_130"><div class="b-layout__txt b-layout__txt_bold"><?= $contact['name']?>:</div></td>
                                  <td class="b-layout__one b-layout__one_padtb_5 b-layout__one_bordbot_ccc">
                                      <div class="b-layout__txt">
                                          <?php if($name == 'site') { ?>
                                          <a class="b-layout__link" target="_blank" href="<?= $contact['value']?>"><?= reformat($contact['value'],50)?></a>
                                          <?php } elseif($name == 'email') { ?>
                                          <a class="b-layout__link" target="_blank" href="mailto:<?= $contact['value']?>"><?= reformat($contact['value'], 50)?></a>
                                          <?php } else { //if?>
                                             <?= reformat($contact['value'], 50)?>
                                          <?php }//else?>
                                      </div>
                                  </td>
                               </tr>
                               <?php }//foreach?>
                            </table>
                        </div>
                        <?php }//if?>

                        <?php if ($project['kind'] == 4) { ?><span class="place">� ���� <?= (($project['country'])?" (".$project['country_name'].(($project['city'])?", ".$project['city_name']:"").")":"") ?></span><?php } //if?>
                        <? if ($project['prefer_sbr'] === 't') { ?>
						<div class="b-layout__txt <? if (!((hasPermissions('projects')||$contest->is_owner)&&($project["closed"] != "t"))) { ?>b-layout__txt_padbot_30<? } ?> b-layout__txt_fontsize_11">������� �������������� ����� ������ <a href="/promo/bezopasnaya-sdelka/" target="_blank" class="b-layout__link">���������� ������</a> <?= view_sbr_shield('', 'b-icon_top_2') ?></div>
                        <? } ?>

		<? if ((hasPermissions('projects')||$contest->is_owner)&&($project["closed"] != "t")) { ?>
		<div class="b-layout__txt b-layout__txt_padbot_30">
         <div class="b-layout__txt b-layout__txt_fontsize_11">
            ����������: �������� "<?=GetKind($project["kind"])?>" 
            <a class="b-layout__link" href="#" id="pos_link_<?=$project['id']?>" onclick="xajax_getStatProject(<?=$project['id']?>, '<?=$project['payed_to']?>', '<?=$project['now']?>', '<?=$project['payed']?>', '<?=$project['post_date']?>', '<?=$project['kind']?>', '<?=$project['comm_count']?>', '<?= $offcnt?>'); return false;">���������</a>
         </div>
			<span class="b-layout__txt b-layout__txt_fontsize_11" id="prj_pos_<?=$project['id']?>"></span>
		</div>
		<? } ?>


			<?
			// ������
			if ($project['attach']) {
				echo '<table cellpadding="2" cellspacing="0" border="0">';
				$str = viewattachLeft($project['login'], $project['attach'], "upload", $file, 1000, 600, 307200, $project['attach'], 0, 0);
				echo "<tr><td><br>$str<br></td></tr>";
				echo '</table>';
			} 
			elseif ( isset($project_attach) && is_array($project_attach) ) {
                ?>
                <table cellpadding="2" cellspacing="0" border="0">
                <tr>
                    <td>&nbsp;</td>
                    <td style="font-size:11px;padding-top:8px;vertical-align:middle;">
                        <div class="attachments attachments-p">
                <?php
                $nn = 1;
            	foreach ( $project_attach as $attach )
            	{
            		$str = viewattachLeft( NULL, $attach["name"], $attach['path'], $file, 0, 0, 0, 0, 0, 0, $nn );
            		echo '<div class = "flw_offer_attach">', $str, '</div>';
                    $nn++;
            	}
            	?>
            	       </div>
                    </td>
            	</tr>
            	</table>
            	<?php
            }
			?>

      
      
      
<div class="b-buttons">      
<?php if (is_emp()): ?>
    <a id="contest-add-button" class="b-button b-button_flat b-button_flat_green" href="<?="/public/?step=1&kind=".$project['kind']."&red="?>">���������� ����������� �������</a>
<?php else: ?>
    <a id="contest-add-button" class="b-button b-button_flat b-button_flat_green" style="display: <?=(($project['user_id'] != $_SESSION['uid'] && !$contest->has_offer && !$project['contest_end'] && $project['closed'] != 't' && !$contest->is_banned) ? 'inline-block': 'none')?>;" <? if($href=="#offer-edit") { ?>onclick="$('contest-comments').setStyle('display', 'block'); <?= (!$contest->offers || $all_offers_count==0)?"ShowHide('contest-comments');":"";?> ShowHide('add-offer'); var textarea = new resizableTextarea($$('div.rtextarea'), { handler: '.handler', modifiers: {x: false, y: true}, size: {y:[170, 1000]}});" <? } //if ?> href="<?=$href?>">������� ������� � ��������</a>
<?php endif; ?>
&#160;&#160;<span class="b-buttons__txt"><a class="b-layout__link b-layout__link_no-decorat b-layout__link_fontsize_13" href="/konkurs/">���������� ������ ��������</a></span>
</div>     
      
      
      
      
      
      
      
      
      
		<?
		if (($contest->is_owner && !$project['is_blocked']) || hasPermissions('projects')) {
			$offcnt = (int) count($contest->offers);
            foreach($contest->offers as $v) {
                if($v['is_deleted']=='t') $offcnt--;
            }
		?>


		<? if (strtotime($project['end_date']) > time() && $contest->is_owner) { ?>
		<table cellpadding="2" cellspacing="0" border="0" style="margin: 12px 0px 16px 0px">
		<tr valign="middle">
			<td style="padding:0 4px"><img src="/images/ico_setup.gif" border="0"></td>
			<td><a class="public_blue" href="/public/?step=1&amp;public=<?=$project["id"]?>&amp;red=<?=rawurlencode(getFriendlyURL("project", $project['id']))?>">�������������</a></td>
			<td></td>
            <td></td>
		</tr>
        </table>
		<table cellpadding="2" cellspacing="0" border="0" style="margin: 12px 0px 16px 0px">
            <tr valign="middle">
        <?
                require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
                $account = new account();
                $transaction_id = $account->start_transaction($uid);
        ?>
            <?php /*
												  <td style="padding: 30px 4px 4px 4px">
                <a href="/public/?step=2&public=<?=$project['id']?>&red=<?=rawurlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']); ?>" class="btn btn-orng"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">������ ������� �������</span></span></span></a>
                <div style="margin-top: 4px; color: #AC9999;font-size: 10px">
                    �� ������ <a href="/public/?step=2&public=<?=$project['id']?>&red=<?=rawurlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'])?>"  class="public_blue">��������� ��� ������� ������<br />� �������� ��� ����� ���������</a>
                </div>
              </td>
												*/ ?>
              <td style="padding: 30px 4px 4px 4px">
                <a href="/public/?step=1&public=<?= $project['id'] ?>" class="b-button b-button_flat b-button_flat_green">������ ������� �������</a>
                <div style="margin-top: 4px; color: #AC9999;font-size: 10px">
                    �� ������ <a href="/public/?step=1&public=<?= $project['id'] ?>"  class="public_blue">��������� ��� ������� ������� �����</a>
                </div>
              </td>
               <td width="20">&nbsp;</td>
              <td colspan="3" style="padding: 30px 4px 4px 4px">&nbsp;</td>
            </tr>
        
		</table>
		<? } ?> 
            
		<? } ?>
		
		</div>		
        <br clear=all>	
								
								<div style="overflow:hidden">
												<div style="font-size:11px; color:#666666; text-align:left; padding: 13px 10px 0 60px; margin-bottom:-17px;"></div>
                                                <?php $sTitle = $project['moderator_status'] === '0' && $project['is_pro'] != 't' ? $stop_words->replace($project['name'], 'plain', false) : $project['name']; ?>
                                                <?php $sDescr = $project['moderator_status'] === '0' && $project['is_pro'] != 't' ? $stop_words->replace($project['descr'], 'plain', false) : $project['descr']; ?>
								</div>
		<? if ($contest->is_moder) { ?>
		<br clear="all">
		<div style="margin: 10px 0 0 60px">
 
      <a href="/public/?step=1&amp;public=<?=$project['id']?>&amp;red=%2Fprojects%2Findex.php%3Fpid%3D<?=$project['id']?>" onClick="popupQEditPrjShow(<?=$project['id']?>, event); return false;">�������������</a> | 
			<span id="project-button-<?=$project['id']?>"><a style="color: red" href="." onclick="banned.<?=($project['is_blocked']? 'unblockedProject': 'blockedProject')?>(<?=$project['id']?>); return false;"><?=($project['is_blocked']? '��������������': '�������������')?></a></span> | 
			<? if ( $project['warn']<3 && !$project['is_banned'] && !$project['ban_where'] ) { ?>
			<span class="warnlink-<?=$project['user_id']?>"><a style="color: red" href="." onclick='banned.warnUser(<?=$project['user_id']?>, 0, "projects", "p<?= $project['id']?>", 0); return false;'>������� ��������������</a> (<span class='warncount-<?=$project['user_id']?>'><?=($project['warn'] ? $project['warn'] : 0)?></span>)</span>
			<? } else { 
			    $sBanTitle = (!$project['is_banned'] && !$project['ban_where']) ? '��������!' : '���������';
			    ?>
			<span class="warnlink-<?=$project['user_id']?>"><a style="color:red;" href="javascript:void(0);" onclick="banned.userBan(<?=$project['user_id']?>, 'p<?= $project['id']?>',0)"><?=$sBanTitle?></a></span>
			<? } ?>
		</div>
		<? } ?>

		<div id="project-reason-<?=$project['id']?>" style="margin-top: 10px;margin-left: 60px;<?=($project['is_blocked']? 'display: block': 'display: none')?>"><? 
		if ($project['is_blocked']) {
			$moder_login = (hasPermissions('projects'))? $project['admin_login']: '';
			print '<br clear=all><br>'.HTMLProjects::BlockedProject($project['blocked_reason'], $project['blocked_time'], $moder_login, "{$project['admin_name']} {$project['admin_uname']}");
		} else {
			print '&nbsp;';
		}
		?></div>

                    <? if(hasPermissions('projects') && $project_history) { ?>
                        <div class="prjh">
					    	<a href="#" class="lnk-dot-grey toggle-history" onClick="$('prjh_content').toggleClass('prjh_visible'); return false;">����������� �������������� ������ ������� (<?=dateFormat("d.m.Y H:i", $project['create_date'])?>)</a>
					    	<div id="prjh_content" class="prjh_content ">
					    		<div class="clear"></div>

                                <? if ($project_history['cost'] != 0) { ?>
                                    <?
                                    switch ($project_history['priceby']) {
                                        case '1':
                                            $priceby_str = "/���";
                                            break;
                                        case '2':
                                            $priceby_str = "/����";
                                            break;
                                        case '3':
                                            $priceby_str = "/�����";
                                            break;
                                        case '4':
                                            $priceby_str = "/������";
                                            break;
                                        default:
                                            $priceby_str = "";
                                            break;
                                    }
                                    ?>
                                    <div class="prj_cost">������: <?=CurToChar($project_history['cost'], $project_history['currency'])?><?=$priceby_str?></div>
                                <? } else { ?>
                                   <div class="prj_cost prj-dogovor" style="margin-top:-14px">
                                        <table cellspacing="0" cellpadding="0">
                                           <tr>
                                               <td>
                                                   <div class="form">
                                                   <b class="b1"></b>
                                                   <b class="b2"></b>
                                                   <div class="form-in">������ �� ��������������</div>
                                                   <b class="b2"></b>
                                                   <b class="b1"></b>
                                                   </div>
                                               </td>
                                           </tr>
                                       </table>                            
                                   </div>
                               <? } ?>

    							<h2 class="b-page__title"><?=reformat($project_history['name'], 30, 0, 1);?></h2>
    							<div class="prj_text">
                                    <?=reformat($project_history['descr'], 70, 0, 0, 1);?>
    							</div>
                                <?
                                if (isset($project_history['attach']) && is_array($project_history['attach'])) {
                                    ?><br/><div class="attachments attachments-p"><?
                                    $nn = 1;
        	                        foreach ($project_history['attach'] as $attach) {
                                        $str = viewattachLeft( NULL, $attach["name"], $attach['path'], $file, 0, 0, 0, 0, 0, 0, $nn );
        		                        echo '<div class = "flw_offer_attach">', $str, '</div>';
                                        $nn++;
        	                        }
                                    ?></div><?
                                }
        	                    ?>
    							<div class="std prjh_section">
                                    <table border="0" width="100%">
                                    <tr valign="top">
                                    <td align="left">
                                    <? if($project_history['pro_only']=='t') { ?><div class="prj-pro">������ ��� <img src="/images/icons/f-pro.png"></div><br clear="all"/><? } ?>
                                    </td>
                                    <td style="text-align:right !important;">
                                    �������: <?=$project_history['spec_txt']?>
                                    <p>������� ������ � <?=dateFormat("d.m.Y", $project['create_date'])?><? if ($project_history['end_date']) { ?> �� <?=dateFormat("d.m.Y", $project_history['end_date'])?><? } ?></p>
                                    <p>���������� ����� �������� <?=(((int) dateFormat("N", $project_history['win_date']))==2?'��':'�')?> <?=$daysOfWeek[(int) dateFormat("N", $project_history['win_date'])]?>, <?=dateFormat("d.m.Y", $project_history['win_date'])?></p>
                                    </td>
                                    </tr>
                                    </table>
                                </div>
    						</div>
    					</div>
                    <? } ?>                    

		
		<div id="warnreason-p<?=$project['id']?>" style="display:none; padding: 0 0 5px 60px;">&nbsp;</div>

	</div>

	
	<? if (!$project['is_blocked'] || $contest->is_moder) { ?>
	<div class="contest-info ">
    	<table class="b-layout__table b-layout__table_width_full">
        	<tr class="b-layout__tr">
            	<td class="b-layout__td b-layout__td_width_33ps b-layout__td_width_full_ipad">
		<div class="contest-ib contest-party">
			<h4>���������</h4>
			<ul>
				<li class="c-p1"><? if ($_GET['filter']) { ?><a href="<?=getFriendlyURL("project", $project['id'])?>">����� ����������</a><? } else { ?><b>����� ����������</b><? } ?>: <span id="stat-freelancers"><?=(int) $contest->stat['offers']?></span></li>
				<li class="c-p2"><? if ($_GET['filter'] != 'candidates') { ?><a href="<?=getFriendlyURL("project", $project['id'])?>?filter=candidates">����������</a><? } else { ?><b>����������</b><? } ?>: <span id="stat-candidates"><?=(int) $contest->stat['candidates']?></span></li>
				<li class="c-p3"><? if ($_GET['filter'] != 'banned') { ?><a href="<?=getFriendlyURL("project", $project['id'])?>?filter=banned">����������</a><? } else { ?><b>����������</b><? } ?>: <span id="stat-banned"><?=(int) $contest->stat['banned']?></span></li>
			</ul>
		</div>
        		</td>
            	<td class="b-layout__td b-layout__td_width_33ps b-layout__td_width_full_ipad">
		<div class="contest-ib contest-stat">
			<h4>���������� �� ��������</h4>
			<ul>
				<li>������� <?=ending($contest->stat['offers_today'], '������������', '������������', '������������')?> <?=intval($contest->stat['offers_today'])?> <?=ending($contest->stat['offers_today'], '������', '������', '�����')?> � <?=intval($contest->stat['comments_today'])?> <?=ending($contest->stat['comments_today'], '�����������', '�����������', '������������')?></li>
			</ul>
		</div>
        		</td>
                <td class="b-layout__td">
		<? if (strtotime($project['end_date']) < mktime()) { ?>
		<div class="contest-ib contest-period">
			<div class="contest-period-in">
				<h4><img src="/images/ico_closed.gif" alt="������ ������" width="21" height="21" class="ico-closed" />&nbsp;������� ������� <?=dateFormat("d.m.Y", $project['end_date'])?></h4>
				<p class="contest-end">������� ������ � <?=dateFormat("d.m.Y", $project['create_date'])?><? if ($project['end_date']) { ?> �� <?=dateFormat("d.m.Y", $project['end_date'])?><? } ?></p>
				<? if (empty($contest->positions) ) { ?>
					<p class="contest-end">����������� ����������� &mdash; <?=dateFormat("d.m.Y", $project['win_date']) ?> �.</p>
				<? } else { ?>
					<p class="contest-end">���������� ���������</p>
				<? } ?>
				<? if ((($contest->is_owner && !$project['is_blocked']) || hasPermissions('projects')) && empty($contest->positions)) { ?><p><a href="javascript:void(0)" onclick="setWinners(candidates); return false;">���������� �����������</a></p><? } ?>
			</div>
		</div>
		<? } else if ($project['closed'] == 't') { ?>
		<div class="contest-ib">
			<h4><img src="/images/ico_closed.gif" alt="������ ������" width="21" height="21" class="ico-closed" />&nbsp;������� ���� � ����������</h4>
		</div>
		<? } else if (($_GET['action'] == 'change-dates' || $dateAlert) && (($contest->is_owner && !$project['is_blocked']) || hasPermissions('projects'))) { ?>
			
				<div class="contest-ib contest-period">
				   
					<form action="/projects/index.php?pid=<?=$project['id']?>" id="daterange" name="daterange" onsubmit="return sendDates()" method="post">
					<div class="contest-period-in">
						<input type="hidden" name="action" value="change-dates">
						<div class="set-date-line">
							<label for="f1" class="label-set-date">��������� ��������</label> <div class="sel-set-date"><span><input id="ds" name="ds" value="<?=($_POST['ds']? dateFormat('d-m-Y', $_POST['ds']): ($project['end_date']? dateFormat('d-m-Y', $project['end_date']): ''))?>" style="width: 70px; border: 0 none"></span> <a href="." class="set-date-arrow" id="end_date_btn2"></a></div>
						</div>
						<div class="set-date-line">
							<label for="f2" class="label-set-date">���������� �����������</label> <div class="sel-set-date"><span><input id="de" name="de" value="<?=($_POST['de']? dateFormat('d-m-Y', $_POST['de']): ($project['win_date']? dateFormat('d-m-Y', $project['win_date']): ''))?>" style="width: 70px; border: 0 none"></span> <a href="." class="set-date-arrow"  id="win_date_btn2"></a></div>
						</div>
						
						<input type="submit" id="editDateButton" value="���������" style="margin-top: 20px" onclick="return $('ds').blur()">
					</div>
					</form>
				</div>

		<script type="text/javascript">
			new tcal ({ 'formname': 'daterange', 'controlname': 'ds', 'iconId': 'end_date_btn2', 'leftOffset': 4, 'topOffset': 7 });
			new tcal ({ 'formname': 'daterange', 'controlname': 'de', 'iconId': 'win_date_btn2', 'leftOffset': 4, 'topOffset': 7 });
			<? if ($dateAlert) { ?>alert('<?=$dateAlert?>');<? } ?>
		</script>
		<? } else { ?>
		<div class="contest-ib contest-period">
			<div class="contest-period-in">
				<h4>����� ���������� ��������</h4>
				<p>������� ������ � <?=dateFormat("d.m.Y", $project['create_date'])?><? if ($project['end_date']) { ?> �� <?=dateFormat("d.m.Y", $project['end_date'])?><? } ?></p>
				<? if ($project['end_date'] && $project['win_date']) { ?>
					<p>���������� ����� �������� <?=(((int) dateFormat("N", $project['win_date']))==2?'��':'�')?> <?=$daysOfWeek[(int) dateFormat("N", $project['win_date'])]?>, <?=dateFormat("d.m.Y", $project['win_date'])?></p>
					<? $lDays = floor((strtotime($project['end_date']) - mktime()) / 86400); ?>
					<p class="contest-end">�� ���������� �������� �������� <b><?=($lDays? $lDays: '')?> <?=($lDays? ending($lDays, '����', '���', '����'): '����� �����')?></b></p>
				<? } ?>
				<? if (hasPermissions('projects') || ($contest->is_owner && !$project['is_blocked'])) { ?><p class="lnk-cp-edit"><a href="/projects/index.php?pid=<?=$project['id']?>&action=change-dates#contest-view">������������� �����</a></p><? } ?>
			</div>
		</div>
		<? } ?>
        		</td>
              </tr>
           </table>
		</div>
		<? } ?>
