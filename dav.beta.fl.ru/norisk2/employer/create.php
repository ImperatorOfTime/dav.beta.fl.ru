<?
$pdrd_disabled = ($sbr->scheme_type != sbr::SCHEME_PDRD && time() < strtotime('2011-01-01'));
$categories = professions::GetAllGroupsLite(true, true);
$sub_categories = professions::GetProfList();
$frl_ftype = sbr::FT_PHYS;
if($sbr->frl_id) {
    $frl = new freelancer();
    $frl->GetUserByUID($sbr->frl_id);
    if(!$sbr->frl_login) $sbr->data['frl_login'] = $frl->login;
    if($frl_reqvs = sbr_meta::getUserReqvs($frl->uid)) {
        $frl_ftype = (int)$frl_reqvs['form_type'];
        $frl_rtype = $frl_reqvs['rez_type'];
    }
}
?>
<script type="text/javascript">
Sbr.prototype.DEBUG=0;
var SBR = new Sbr('createFrm');
window.addEvent('domready', function() { SBR = new Sbr('createFrm'); } );
Sbr.prototype.CATEGORIES={<? // ���������/������������: {��_���:{���_���:{��_������:���_������,��_������:...}},��_���:...}
foreach($sub_categories as $sc) {
    $cc = $sc['prof_group'];
    $ccname = str_replace("'", "\\'", $categories[$cc]['name']);
    $scname = str_replace("'", "\\'", $sc['name']);
    if($lcc!=$cc) {
        echo ($lcc ? '}},' : '') . "$cc:{'$ccname':{";
        $lcc = $cc;
        $j=0;
    }
    echo ($j++ ? ',' : '') . "{$sc['id']}:'{$scname}'";
}
if($lcc) echo '}}';
?>};
Sbr.prototype.ERRORS={<?
$i=0;
foreach($sbr->error as $f=>$m) {
    if($f!='stages')
        echo ($i++ ? ',' : '') . "'$f':'$m'";
    else {
        foreach($m as $num=>$errs) {
            foreach($errs as $sf=>$sm)
                echo ($i++ ? ',' : '') . "'stages[$num][$sf]':'$sm'";
        }
    }
}
?>};
Sbr.prototype.FRL_LOGIN='<?=$sbr->data['frl_login']?>';
Sbr.prototype.DYN_SEND=<?=(int)($sbr->status==sbr::STATUS_CANCELED || $sbr->status==sbr::STATUS_REFUSED)?>;
Sbr.prototype.ATTACH_SOURCE_PRJ=<?=sbr_stages::ATTACH_SOURCE_PRJ?>;
Sbr.prototype.MAX_FILES=<?=sbr::MAX_FILES?>;
Sbr.prototype.STAGE_FILES_COUNT={<?
$j=0;
foreach($sbr->stages as $i=>$s) {
    if($site=='editstage' && $s->id != $stage_id) continue;
    echo ($j++ ? ',' : '') . $i . ':' . ($s->attach ? count($s->attach) : 0);
}
?>};
Sbr.prototype.STAGE_DEADLINES={<?
$j=0;
foreach($sbr->stages as $i=>$s) {
    if($site=='editstage' && $s->id != $stage_id) continue;
    echo ($j++ ? ',' : '') . $i . ':' . ($s->dead_time ? 'new Date(' . date('Y,n-1,j',strtotime($s->dead_time)) . ')' : 'null');
}
?>};
Sbr.prototype.COST=<?
if($cst = (float)$sbr->cost) {
    foreach($sbr->stages as $s) {
        if($site=='editstage' && $s->id != $stage_id) continue;
        $cst -= $s->cost;
    }
}
echo $cst;
?>;
Sbr.prototype.RUR_SYS=<?=exrates::BANK?>;
Sbr.prototype.SCHEME_TYPE=<?=(isset($sbr->data['scheme_type']) ? $sbr->data['scheme_type'] : 'null')?>;
Sbr.prototype.SCHEMES=<?=sbr_meta::jsSchemeTaxes($sbr_schemes, $frl_reqvs, $sbr->getUserReqvs())?>;
Sbr.prototype.STAGES_COSTS=[<?
$i=0;
foreach($sbr->stages as $s) {
    echo ($i++?',':'').$s->cost;
}
?>];
SbrStage.prototype.HTML_FILE_ITEM=function(){return '<li><input name="stages['+this.num+'][attach][]" type="file" size="23" class="i-file" /></li>'};
SbrStage.prototype.MAX_WORK_TIME=<?=sbr_stages::MAX_WORK_TIME?>;
Sbr.prototype.FT_FRL=<?=$frl_ftype?>;
Sbr.prototype.RT_FRL=<?=(int)$frl_rtype?>;
Sbr.prototype.PDRD_DISABLED=<?=($sbr->reserved_id || $pdrd_disabled ? 'true' : 'false')?>;
</script>
<div class="tabs-in">
	<div class="lnk-nr-back">
        <a href=".">��������� � ������� �� ����������� �������</a>
	</div>
    <h3><?=($sbr->id ? '' : '����� &laquo;���������� ������&raquo;')?></h3>
    <form action="?site=<?=$site?>" method="post" enctype="multipart/form-data" id="createFrm">
        <div class="form nr-form-name">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <div class="form-block first last">
                    <div class="form-el">
                        <label class="form-label" for="sbr_name">�������� �������</label>
                        <span><input type="text" class="nr-i-name" id="sbr_name" name="name" value="<?=html_attr($sbr->data['name'])?>" maxlength="<?=sbr::NAME_LENGTH?>" onfocus="SBR.adErrCls(this)" onkeydown="return SBR.cancelEnter(event)" /></span>
                        <div class="tip tip-t2 tip7"></div>
                    </div>
                </div>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
        <div class="form nr-form-frl">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <div class="form-block first last">
                    <div class="form-el c">
                        <div class="nr-frl-info" id="frlbx">
                          <?=sbr_meta::view_frl($frl)?>
                        </div>
                        <? if($site=='create' || $sbr->isDraft() || $sbr->data['status']==sbr::STATUS_CANCELED || $sbr->data['status']==sbr::STATUS_REFUSED) { ?>
                        <label class="form-label" for="frl_login">�����������</label>
                        <span><input type="text" id="frl_login" name="frl_login" value="<?=($sbr->data['frl_login_added'] ? $sbr->data['frl_login_added'] : ($sbr->data['frl_login'] ? html_attr($sbr->data['frl_login']) : '�����'))?>" onfocus="SBR.onfrlfocus(this);this.select()" onkeydown="if(event.keyCode==13){SBR.addFrl();return false;}" onblur="SBR.onfrlblur(this)" class="nr-i-login" />
                        <input type="button" class="i-btn" value="<?=($frl->uid ? '�������' : '��������')?>" onclick="SBR.addFrl()"/></span>
                        <div class="tip tip-t2" style="left:160px;top:14px;z-index:1"></div>
                        <? } ?>
                    </div>
                </div>
                <div class="form-block last"<?=($frl && !$frl_rtype ? '' : ' style="display:none"')?> id="unknown_frl_rez">
                    <div class="form-el">
                        <span class="dred">�������� ��������, ����������� �� ������ ���� �����������. ��� ���������� ���������� ��������, ���������� ��������� ��� ������� ��������� ������ ����������� &mdash;
                        ������������ ������ ������ �� ����� ��������� <?=sbr_meta::view_cost($sbr->maxNorezCost(), exrates::BANK)?> (���������� <?=sbr::MAX_COST_USD?> USD). ��� ��������� ������������ ���������� ��������� ����������� ������ ����������.</span>
                    </div>
                </div>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
        <? 
          foreach($sbr->stages as $num=>$stage)
          { 
              if($site=='editstage' && $stage->id != $stage_id) continue;
        ?>
        <fieldset class="nr-task">
            
            <legend>������ �<a class="nr-task-anchor" name="stage<?=($num+1)?>" innum="<?=$num?>"><?=($num+1)?></a> <? // !!! ������������� ����� ?>
              <? if($site=='create' || $sbr->isDraft()) { // !!! !$sbr->reserved_id || ?>
                <span id="delstage_box<?=$num?>"<?=($num || $sbr->stages_cnt > 1 ? '' : ' style="display:none"')?>>(<a href="javascript:;">�������</a>)</span>
              <? } ?>
            </legend>
            <div class="form">
                <b class="b1"></b>
                <b class="b2"></b>
                <div class="form-in">
                    <div class="form-block first">
                        <div class="form-el">
                            <label for="name_task" class="form-label">�������� ������:</label>
                            <span class="fprm-p"><input type="text" name="stages[<?=$num?>][name]" id="name_task" class="nr-i-taskname" value="<?=html_attr($stage->data['name'])?>" maxlength="<?=sbr_stages::NAME_LENGTH?>" onfocus="SBR.adErrCls(this)" onkeydown="return SBR.cancelEnter(event)"/></span>
                            <div class="tip tip-t2 tip5"></div>
                        </div>
                        <div class="form-el">
                            <label for="razdel-choose" class="form-label">������:</label>
                            <span class="nr-task-cat">
                                <span>
                                  <select id="razdel-choose" name="stages[<?=$num?>][category]" onchange="SBR.getStageByItem(this).changeCat(this.value)">
                                  <option value="0">&lt;�������� ������&gt;</option>
                                  <? foreach($categories as $cc) { ?>
                                     <option value="<?=$cc['id']?>"<?=($cc['id']==$stage->data['category'] ? ' selected="selected"' : '')?>><?=$cc['name']?></option>
                                  <? } ?>
                                  </select>
                                </span>
                                <span>
                                  <select name="stages[<?=$num?>][sub_category]" id="<?=$stage->data['sub_category']?>">111<?/* JS */?></select>
                                </span>
                            </span>
                        </div>
                        <div class="form-el">
                            <label for="descr-task" class="form-label">�������� ������:</label>
                            <div class="nr-task-info">
                                <span><textarea id="descr-task" rows="5" cols="5" name="stages[<?=$num?>][descr]" onfocus="try{SBR.adErrCls(this)}catch(e){}"><?=$stage->data['descr']?></textarea></span>
                                <div class="tip tip-t2 tip4"></div>
                                <!-- ������������� ����� -->
                                <div class="form form-files">
                                    <b class="b1"></b>
                                    <b class="b2"></b>
                                    <div class="form-in">
                                        <div class="form-block first last">
                                            <div class="form-el">
                                                <div class="flt-<?=($stage->data['attach']||$sbr->error['stages'][$num]['err_attach'] ? 'show' : 'hide')?>" id="nr-files1">
                                                    <div class="form-files-tglbar">
                                                        <a href="javascript: void(0);" class="flt-tgl-lnk lnk-dot-blue">������������� ����� (<?=($stage->data['attach'] ? '�' : '���')?>�������)</a>
                                                    </div>
                                                    <div class="flt-cnt �">
																												
																														<ul class="form-files-added">
																																<? if($stage->data['attach']) foreach($stage->data['attach'] as $id=>$a) { ?>
																																		<? if($a['source_type']==sbr_stages::ATTACH_SOURCE_PRJ) { ?>
																																				<input type="hidden" name="stages[<?=$num?>][project_attach][<?=$id?>]" value="<?=$a['file_id']?>"/>
																																		<? } if($a['source_type']==sbr_stages::ATTACH_SOURCE_OLD) { ?>
																																				<input type="hidden" name="stages[<?=$num?>][del_attach][<?=$id?>]" value="<?=$a['is_deleted']?>"/>
																																		<? } if($a['is_deleted']!='t') { ?>
																																				<li>
																																						<a href="javascript:;" onclick="SBR.getStageByItem(this).delAttach(this, <?=(int)$id?>)">
																																							<img src="/images/btn-remove2.png" alt="�������">
																																						</a>
																																						<a href="<?=WDCPREFIX.'/'.$a['path'].$a['name']?>" target="_blank"><?=($a['orig_name'] ? $a['orig_name'] : $a['name'])?></a>
																																				</li>
																																		<? } ?>
																																<? } ?>
																														</ul>
                                                        
                                                        
                                                        <ul class="form-files-list"><?/* JS */?></ul>
                                                        <div class="form-files-inf" style="position: relative">
                                                            <span><input type="hidden" name="stages[<?=$num?>][err_attach]" /></span><div class="tip tip-t2" style="top:2px;left:0px;z-index:1"></div>
                                                            <p>
                                                            �� ������ ���������� � ���������:<br />
                                                            <strong>����:</strong> <?=sbr::MAX_FILE_SIZE/1024/1024?> ��.<br />
                                                            <strong>��������</strong>: 600x1000 ��������, 300 ��.<br />
                                                            ����� ��������� �������� ��������� � ��������: <?=implode(', ', $GLOBALS['disallowed_array'])?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <b class="b2"></b>
                                    <b class="b1"></b>
                                </div>
                                <!-- ����� ������������� ����� -->
                            </div>
                        </div>
                    </div>
                    <div class="form-block last" style="height:auto">
                        <div class="nr-imp">
                            <h4>����������� ������ � <?=sbr_stages::MIN_COST_RUR?> ���.</h4>
                        </div>
                        <div class="form-el form-s-el">
                            <label for="budjet-prj" class="form-label">��������� ������, � �.�. ���:</label>
                            <span>
                                <input id="budjet-prj" name="stages[<?=$num?>][cost]" type="text" style="width:120px"<?=($sbr->reserved_id ? ' disabled="disabled"' : ' onchange="SBR.getStageByItem(this).changeCost(this.value)"')?> value="<?=html_attr($stage->data['cost'])?>" maxlength="12" onfocus="SBR.adErrCls(this); SBR.adErrCls($('cost_sys_err_tbl'))" onkeydown="return SBR.cancelEnter(event)"/>
                                <? if($num == 0) { ?>
                                  <input type="hidden" name="cost_sys_err" />
                                <? } ?>
                                <select name="cost_sys[<?=$num?>]"<?=($num>0 || $sbr->reserved_id ? ' disabled="disabled"' : ' onchange="SBR.changeSys()" onfocus="SBR.adErrCls(this);"')?>>
                                <? foreach($EXRATE_CODES as $id=>$ex) { 
                                       if($id==exrates::FM || $id==exrates::WMZ) continue;
                                       if(($id==exrates::YM || $id==exrates::WMR) && $sbr->user_reqvs['form_type']==sbr::FT_JURI) continue;
                                ?>
                                   <option value="<?=$id?>"<?=($sbr->cost_sys==$id ? ' selected="selected"' : '')?>><?=$ex[0]?></option>
                                <? } ?>
                                </select><span></span>
                            </span>
                            <div class="tip" style="left:393px"></div>
                            <div class="nr-imp norez_maxcost_block"<?=($frl_rtype!=sbr::RT_UABYKZ ? ' style="display:none"' : '')?>>
                                <h4>������������ ������ &mdash; <?=sbr_meta::view_cost($sbr->maxNorezCost(), exrates::BANK)?>, ��������� ��������� ����������� �� �������� ���������� ���������� ���������</h4>
                            </div>
                        </div>
                        <div class="form-el">
                            <label for="itogo-pay" class="form-label3">����� � ������:</label>
                            <span>
                                <input id="itogo-pay" type="text" style="width:120px" name="stages[<?=$num?>][cost_total]"<?=($sbr->reserved_id ? ' disabled="disabled"' : ' onchange="SBR.getStageByItem(this).changeCostTotal(this.value)"')?> value="<?=html_attr($stage->data['cost_total'])?>" maxlength="12" onfocus="SBR.adErrCls(this); SBR.adErrCls($('cost_sys_err_tbl'))" onkeydown="return SBR.cancelEnter(event)"/>
                                
                                <span>������</span>
                            </span>
                        </div>
                        <div class="nr-imp" style="margin-top:-20px">
                            <h4>������ ������� ���������� � ������� �������������� �������� �������</h4>
                        </div>
                        <div class="form-el">
                            <? if(!$sbr->data['reserved_id'] || !$stage->data['dead_time']) { ?>
                                <label for="time-lavel" class="form-label">����� �� ����:</label>
                                <span>
                                    <input id="time-lavel" name="stages[<?=$num?>][work_time]" type="text" size="7" value="<?=($stage->data['work_days'] ? html_attr($stage->data['work_days']) : '')?>" maxlength="3" onfocus="SBR.adErrCls(this)" onkeydown="return SBR.cancelEnter(event)"/>
                                    (����)
                                </span>
                                <div class="tip tip-t2" style="top:12px;left:160px"></div>
                            <? } else { ?>
                                <label for="srok-task" class="form-label">���� ������:</label>
                                <span class="nr-diedline">
                                    <input id="srok-task" type="text" size="2" name="stages[<?=$num?>][dead_day]" value="<?=date('j',strtotime($stage->data['dead_time']))?>" maxlength="2" onchange="SBR.getStageByItem(this).setWTime()" onkeydown="return SBR.cancelEnter(event)"/>
                                    <select name="stages[<?=$num?>][dead_month]" onchange="SBR.getStageByItem(this).setWTime()">
                                        <? foreach($MONTHA as $idx=>$m) { ?>
                                          <option value="<?=$idx-1?>"<?=($idx==date('n',strtotime($stage->data['dead_time'])) ? ' selected="selected"' : '')?>><?=$m?></option>
                                        <? } ?>
                                    </select>
                                    <input name="stages[<?=$num?>][dead_year]" type="text" size="4" value="<?=date('Y',strtotime($stage->data['dead_time']))?>" maxlength="4" onchange="SBR.getStageByItem(this).setWTime()" onkeydown="return SBR.cancelEnter(event)" />
                                    <br /><br />
                                    <select style="width: 121px" name="stages[<?=$num?>][add_wt_switch]" onchange="SBR.getStageByItem(this).setWTime(null,1)">
                                      <option value="+"<?=($stage->data['add_wt_switch']=='+' ? ' selected="selected"' : '')?>>��������</option>
                                      <option value="-"<?=($stage->data['add_wt_switch']=='-' ? ' selected="selected"' : '')?>>������</option>
                                    </select>
                                    <input name="stages[<?=$num?>][add_work_time]" type="text" size="4" value="<?=html_attr($stage->data['add_work_time'])?>" maxlength="3" onfocus="SBR.adErrCls(this)" onchange="SBR.getStageByItem(this).setWTime(this.value)" onkeyup="SBR.getStageByItem(this).setWTime(this.value, null, true)" onkeydown="return SBR.cancelEnter(event)"/> (����)
                                </span>
                            <? } ?>
                        </div>
                    </div>
                </div>
                <b class="b2"></b>
                <b class="b1"></b>
            <input type="hidden" name="stages[<?=$num?>][id]" value="<?=$stage->data['id']?>" />
            </div>
                        <? if($site!='editstage' && ($site=='create' || $sbr->isDraft())) { // !!! ?>
                            <div class="form-el-btn">
                                <input type="button" class="i-btn" value="+ �������� ��� ���� ������" onclick="SBR.addStage()"/>
                            </div>
                        <? } ?>
        </fieldset>
        <? } ?>
        <div class="form form-nr-scheme">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <div class="form-block first">
                    <div class="form-el form-el-resident">
                        <ul class="form-list">
                            <li>
                                <label><input name="rez_type" type="radio" value="<?=sbr::RT_RU?>" class="i-radio"<?=($rt_disabled && $rez_type && $rez_type != sbr::RT_RU ? ' disabled="disabled"' : '' )?><?=($rt_checked && $rez_type == sbr::RT_RU ? ' checked="checked"' : '' )?>
                                onclick="SBR.changeEmpRezType(<?=sbr::RT_RU?>)"/>
                                  � �����������, ��� ������� ���������� ���������� ���������
                                </label>
                            </li>
                            <li>
                                <label><input name="rez_type" type="radio" value="<?=sbr::RT_UABYKZ?>" class="i-radio"<?=($rt_disabled && $rez_type && $rez_type != sbr::RT_UABYKZ ? ' disabled="disabled"' : '' )?><?=($rt_checked && $rez_type == sbr::RT_UABYKZ ? ' checked="checked"' : '' )?>
                                onclick="SBR.changeEmpRezType(<?=sbr::RT_UABYKZ?>)"/>
                                  � �����������, ��� ������� ���������� ������ ������� �����������, ����� ���������� ���������
                                </label>
                                <div class="form fs-o form-resident-inf"<?=($rt_checked && $rez_type == sbr::RT_UABYKZ ? '' : ' style="display:none"' )?> id="norez_info">
                                    <b class="b1"></b>
                                    <b class="b2"></b>
                                    <div class="form-in">
                                        ������������ ����� ������ ���������� <?=sbr::MAX_COST_USD?> USD (<?=sbr_meta::view_cost($sbr->maxNorezCost(), exrates::BANK)?>)<br />
                                    </div>
                                    <b class="b2"></b>
                                    <b class="b1"></b>
                                </div>
                                
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="form-block last">
                    <div class="form-el c">
                        <ul class="form-nr-scheme-ul">
                            <? if($sbr->scheme_type==sbr::SCHEME_OLD) { ?>
                              <li><input type="radio" name="scheme_type" value="<?=sbr::SCHEME_OLD?>" onclick="SBR.changeSchemeType(this.value)"<?=($sbr->reserved_id ? ' disabled="disabled"' : '')?> checked="checked" /><?=sbr::$scheme_types[sbr::SCHEME_OLD][0]?></li>
                            <? } ?>
                            <li><input type="radio" id="scheme_type<?=sbr::SCHEME_AGNT?>" name="scheme_type" value="<?=sbr::SCHEME_AGNT?>" onclick="SBR.changeSchemeType(this.value)"<?=($sbr->reserved_id ? ' disabled="disabled"' : '')?><?=($sbr->scheme_type==sbr::SCHEME_AGNT ? ' checked="checked"' : '')?> /><a href="<?=sbr::$scheme_types[sbr::SCHEME_AGNT][1]?>" target="_blank"><?=sbr::$scheme_types[sbr::SCHEME_AGNT][0]?></a></li>
                            <li><input type="radio" id="scheme_type<?=sbr::SCHEME_PDRD?>" name="scheme_type"<?=($site == 'create' || $sbr->scheme_type==sbr::SCHEME_OLD)?> value="<?=sbr::SCHEME_PDRD?>" onclick="SBR.changeSchemeType(this.value)"<?=($sbr->reserved_id || $pdrd_disabled ? ' disabled="disabled"' : '')?><?=($sbr->scheme_type==sbr::SCHEME_PDRD ? ' checked="checked"' : '')?> /><a href="<?=sbr::$scheme_types[sbr::SCHEME_PDRD][1]?>" target="_blank"><?=sbr::$scheme_types[sbr::SCHEME_PDRD][0]?></a>
                            <span><input type="hidden" name="scheme_type_err" /></span>
                            <div class="tip tip-t2" style="left:17px;top:20px;z-index:1"></div>
                            <? if($pdrd_disabled) { ?>
                              <span style="color:gray">(� ����� �� ������ ������ ������� ��������� ������� ����� ����� ��������������� � 1 ������)</span>
                            <? } ?>
                            </li>
                        </ul>
                        <div class="form-nr-scheme-tbl">
                            
                            <? foreach($sbr_schemes as $sch) { ?>
                            <table style="display:none" id="sch_<?=$sch['type']?>">
                                <col width="406" />
                                <col width="125" />
                                <col width="100" />
                                <tr>
                                    <th>��������� ������, � �.�. ���</th>
                                    <td style="width: 125px;">&mdash;</td>
                                    <td class="col-sum" id="sch_<?=$sch['type']?>_f"><?=(float)$sbr->data['cost']?></td>
                                </tr>
                                <? foreach($sch['taxes'][1] as $id=>$tax) { $s=$e=''; if($id==sbr::TAX_NDS) {$s='<strong>';$e='</strong>';}  ?>
                                    <tr id="taxrow_<?=$sch['type'].'_'.$id?>">
                                        <th<?=$id=='t' ? '  class="nr-sheme-sum"' : ''?>><?=$s?><?=$tax['name']?><?=$e?></th>
                                        <td><?=$s?><?=($tax['percent'] ? '<span id="taxper_'.$sch['type'].'_'.$id.'"></span>%' : '&mdash;')?><?=$e?></td>
                                        <td class="col-sum <?=$id=='t' ? ' nr-sheme-sum' : ''?>"  id="taxsum_<?=$sch['type']?>_<?=$id?>"<?=$s ? ' style="font-weight:800"' : ''?>>&nbsp;</td>
                                    </tr>
                                <? } ?>
                            </table>
                            <? } ?>
                            <span><input type="hidden" name="cost_sys_err_tbl" id="cost_sys_err_tbl"/></span>
                            <div class="tip tip-t2 tip-t2-r" style="right:92px;top:20px;z-index:10;top:"></div>
                        </div>
                    </div>
                </div>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
        <?=$sbr->view_sign_alert()?>
        <div class="form nr-form-budjet">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <div class="form-block first last">
                    <div class="form-el">
                        <h4>�������� ��������!</h4>
                        <p>�������������� �������� ������� ���������� ����� ������������ ������� � ������������. �������������� �������� ������� ��� ����������� ������ �� ����� ���� ������������ � ������� ������� ����� � FM.</p>
                    </div>
                </div>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
        <div class="form nr-form-btns">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <div class="form-block first last">
                    <div class="form-el">
                        <p id="schalert<?=sbr::SCHEME_AGNT?>" style="display:none">��������� ������ ����������� ������� �� ����������� ����������� ����� ������� �� ������ &laquo;��������� �� ����������� �����������&raquo;, �� ����������� ����������� ��������� ���������� � ���������� ������ �/��� �������� ������ � �������������� ������ ������� &laquo;���������� ������&raquo;. ����� ���������� ���������� �� ����� Free-lance.ru � ���� �������� �� ������: <a href="/agreement_escrow.pdf" target="_blank"><nobr><?=HTTP_PREFIX?>www.free-lance.ru/agreement_escrow.pdf</nobr></a>.<br/><br/>
                          ��������� ���� Free-lance.ru (��� "����") ���������� ������ �� ���������� �������� �� ������������� ������ ������� &laquo;���������� ������&raquo;. ����� ������ �� ���������� �������� �� ������������� ������ ������� &laquo;����������� ������&raquo; ���������� �� ����� Free-lance.ru � ���� �������� �� ������: <a href="<?=sbr::$scheme_types[sbr::SCHEME_AGNT][1]?>" target="_blank"><nobr><?=sbr::$scheme_types[sbr::SCHEME_AGNT][1]?></nobr></a>. ������� �� ������ &laquo;��������� �� ����������� �����������&raquo;, �� ���������� ������� ������ �� ���������� �������� �� ������������� ������ ������� &laquo;���������� ������&raquo;.</p>
                        <p id="schalert<?=sbr::SCHEME_PDRD?>" style="display:none">��������� ������ ����������� ������� �� ����������� ����������� ����� ������� �� ������ &laquo;��������� �� ����������� �����������&raquo;, �� ���������� ���������� � ���������� ������ �/��� �������� ������ � �������������� ������ ������� &laquo;���������� ������&raquo;. ����� ���������� ���������� �� ����� Free-lance.ru � ���� �������� �� ������: <a href="/offer_work_employer.pdf" target="_blank"><nobr><?=HTTP_PREFIX?>www.free-lance.ru/offer_work_employer.pdf</nobr></a>. </p>
                        <input type="submit" name="send" class="i-btn nr-btn-send" value="��������� �� ����������� �����������" <?=(!$rt_checked ? ' disabled="disabled"' : '')?> />
                        <? if($sbr->status==sbr::STATUS_CANCELED || $sbr->status==sbr::STATUS_REFUSED) { ?>
                          <input type="submit" name="save" class="i-btn nr-btn-send" value="&nbsp;���������&nbsp;" <?=(!$rt_checked ? ' disabled="disabled"' : '')?> />
                        <? } if($site!='editstage' && ($site == 'create' || $sbr->isDraft())) { ?>
                          <input type="submit" name="draft" class="i-btn" value="��������� ��� ��������"<?=(!$rt_checked ? ' disabled="disabled"' : '')?> />
                        <? } else { ?>
                          <input type="submit" name="cancel" class="i-btn" value="��������" />
                        <? } ?>
                    </div>
                </div>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        <? if($sbr->data['delstages']) { foreach ($sbr->data['delstages'] as $id=>$d)?>
          <input type="hidden" name="delstages[<?=$id?>]" value="<?=$id?>" />
        <? } ?>
        <? if($site == 'create') { ?>
          <input type="hidden" name="project_id" value="<?=$sbr->project_id?>" />
        <? } ?>
        <? if($site != 'create') { ?>
          <input type="hidden" name="id" value="<?=$sbr->id?>" />
        <? } ?>
        <? if($site == 'editstage') { ?>
          <input type="hidden" name="stage_id" value="<?=$stage_id?>" />
        <? } ?>
        <? if($version) { ?>
          <input type="hidden" name="v" value="<?=$version?>" />
        <? } ?>
        <input type="hidden" name="site" value="<?=$site?>" />
        <input type="hidden" name="action" value="<?=$site?>" />
        </div>
    </form>
</div>
