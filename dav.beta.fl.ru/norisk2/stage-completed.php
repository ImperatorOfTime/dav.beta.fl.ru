<?
$need_payouts = $credit_sum && ($is_arb_outsys || !$sbr->isEmp()) && !$stage->payouts[$sbr->uid];
$norez_block = $sbr->scheme['taxes'][sbr::FRL][sbr::TAX_NP] && $need_payouts && !$sbr->isEmp() && $sbr->user_reqvs['form_type']==sbr::FT_JURI && $sbr->user_reqvs['rez_type']==sbr::RT_UABYKZ; // !!! ������ ������, ����� ����� ������ � ��
?>
<script type="text/javascript">
var SBR = new Sbr();
window.addEvent('domready', function() { SBR = new Sbr('completeFrm'); <?=$need_payouts ? 'SBR.changeRecSys();' : ''?>});
<? if($need_payouts) { ?>
Sbr.prototype.WM_SYS=<?=exrates::WMR?>;    
Sbr.prototype.YM_SYS=<?=exrates::YM?>; 
Sbr.prototype.RUR_SYS=<?=exrates::BANK?>;
Sbr.prototype.EXCODES={<?
$i=0;
foreach($EXRATE_CODES as $exc=>$exn) {
    if(!$stage->checkPayoutSys($exc, $only_reserved_sys)) continue;
    echo ($i++?',':'') . "$exc:['{$exn[1]}'";
    $sum1 = round($stage->getPayoutSum(NULL, $exc, NULL, $exc, FALSE),2);
    $sum2 = $norez_block ? round($stage->getPayoutSum(NULL, $exc, NULL, $exc, TRUE),2) : $sum1;
    echo ",$sum1,$sum2]";
}
?>};
<? } ?>
Sbr.prototype.EXIDX=<?=($norez_block && $notnp ? 2 : 1)?>;
Sbr.prototype.LOGIN='<?=$sbr->login?>';
Sbr.prototype.ERRORS={<?=($js1=sbr_meta::jsInputErrors($sbr->error['feedback'], 'sbr_feedback[', ']', false))
?><?=($js1 ? ',' : '').($js2=(sbr_meta::jsInputErrors($stage->error['feedback'], 'feedback[', ']', false)))
?><?=$stage->error['credit_sys'] ? ($js1||$js2 ? ',' : '').sbr_meta::jsInputErrors($stage->error['credit_sys'], '', '', false) : ''?>};
</script>

<div class="tabs-in nr-tabs-in2">
    <? include('tpl.stage-header.php') ?>
    <div class="form form-complite<?=($is_arb_outsys ? ' form-ac' : '')?>">
		<div class="form-h">
			<b class="b1"></b>
			<b class="b2"></b>
			<div class="form-h-in">
              <? if($is_arb_outsys) { ?>
                <h3>������� ���������</h3>
              <? } else { ?>
                <h3>�����������, <?=$sbr->status == sbr::STATUS_COMPLETED ? '������ ��������' : '������ ���������'?>!</h3>
              <? } ?>
			</div>
		</div>
		<div class="form-in">
            <form action="?site=Stage&id=<?=$stage->data['id']?>" method="post" id="completeFrm">
                <? if($is_arb_outsys) { $fbc++; ?>
                    <div class="form-block first">
                        <div class="form-el">
                            <p>��������� ��������� ���������, �������� ��� � �������� �������</p>
                            <?=$stage->view_arb_descr_full()?>
                        </div>
                    </div>
                <? } ?>
                <? if(($stage->status == sbr_stages::STATUS_COMPLETED || $stage->status == sbr_stages::STATUS_ARBITRAGED) && !$stage->data[$sbr->upfx.'feedback_id']) { ?>
                    <div class="form-block<?=($fbc++?'':' first')?>">
                        <div class="form-el">
                            <p><strong>����������, �������� ������������ ��� <a href="/users/<?=$sbr->data[$sbr->apfx.'login']?>/" class="<?=$sbr->anti_tbl?>-name"><?=($sbr->data[$sbr->apfx.'uname'].' '.$sbr->data[$sbr->apfx.'usurname'].' ['.$sbr->data[$sbr->apfx.'login'].']')?></a>:</strong></p>
                            <div class="form-el">
                                <ul class="ops-type" id="ops-type">
                                    <li class = "ops-plus"><label><input type = "radio" value = "1" name = "ops_type" <?= $stage->feedback['ops_type'] == 1 ? 'checked="checked"' : '' ?> onClick="$('ops_type').set('value', 1); $('ops_type_error').set('html', '');" />�������������</label></li>
                                    <li class = ""><label><input type = "radio" value = "0" name = "ops_type" <?= $stage->feedback['ops_type'] == 0 && $stage->feedback['ops_type'] !== null &&  $stage->feedback['ops_type'] != ""? 'checked="checked"' : '' ?> onClick="$('ops_type').set('value', 0); $('ops_type_error').set('html', '');" />�����������</label></li>
                                    <li class = "ops-minus"><label><input type = "radio" value = "-1" name = "ops_type" <?= $stage->feedback['ops_type'] == -1 ? 'checked="checked"' : '' ?> onClick="$('ops_type').set('value', -1); $('ops_type_error').set('html', '');" />�������������</label></li>
                                </ul>
                                <span><input type="hidden"  name="feedback[ops_type]" id="ops_type" value="<?= $stage->feedback['ops_type']?>" /></span>
                                <div class="tip" id="ops_type_error" style="left:455px;z-index:2"></div>
                            </div>
                            <div class="form-complite-txt">
                                <span>
                                    <textarea rows="5" cols="10" name="feedback[descr]"><?=$stage->feedback['descr']?></textarea>
                                </span>
                                <div class="tip tip-t2" style="top:145px;left:0px;z-index:1"></div>
                            </div>
                        </div>
                    </div>
                <? } ?>
                <? if($sbr->status == sbr::STATUS_COMPLETED && !$sbr->data[$sbr->upfx.'feedback_id']) { ?>
                    <div class="form-block<?=($fbc++?'':' first')?>">
                        <div class="form-el">
                            <p><strong>����������, �������� ����� ������� &laquo;���������� ������&raquo;:</strong></p>
                            <div class="form-complite-txt">
                                <span>
                                    <textarea rows="5" cols="10" name="sbr_feedback[descr]"><?=$sbr->feedback['descr']?></textarea>
                                </span>
                                <div class="tip tip-t2" style="top:117px;left:0px;z-index:1"></div>
                            </div>
                            <div class="form-complite-stars">
                                <p></p>
                                <?/*<p><strong>�� ����� �������� ��������� � ������� �� ������ � ��������� �������� �������� �������.</strong></p>*/?>
                                    <ul class="vote ops-nr-vote">
                                        <li class="c">
                                            <label>���������������</label>
                                            <span class="stars-vote stars-vote-a vote-<?= (int) $sbr->feedback['p_rate'] ?>">
                                                <input type="hidden" name="sbr_feedback[p_rate]" value="<?= (int) $sbr->feedback['p_rate'] ?>" />
                                                <span>
                                                    <a href=""></a>
                                                    <span>
                                                        <a href=""></a>
                                                        <span>
                                                            <a href=""></a>
                                                            <span>
                                                                <a href=""></a>
                                                                <span>
                                                                    <a href=""></a>
                                                                    <span>
                                                                        <a href=""></a>
                                                                        <span>
                                                                            <a href=""></a>
                                                                            <span>
                                                                                <a href=""></a>
                                                                                <span>
                                                                                    <a href=""></a>
                                                                                    <span>
                                                                                        <a href=""></a>
                                                                                        <span>

                                                                                        </span>
                                                                                    </span>
                                                                                </span>
                                                                            </span>
                                                                        </span>
                                                                    </span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </span>
                                                </span>
                                            </span>
                                        </li>
                                        <li class="c">
                                            <label>����������</label>
                                            <span class="stars-vote stars-vote-a vote-<?= (int) $sbr->feedback['n_rate'] ?>">
                                                <input type="hidden" name="sbr_feedback[n_rate]" value="<?= (int) $sbr->feedback['n_rate'] ?>" />
                                                <span>
                                                    <a href=""></a>
                                                    <span>
                                                        <a href=""></a>
                                                        <span>
                                                            <a href=""></a>
                                                            <span>
                                                                <a href=""></a>
                                                                <span>
                                                                    <a href=""></a>
                                                                    <span>
                                                                        <a href=""></a>
                                                                        <span>
                                                                            <a href=""></a>
                                                                            <span>
                                                                                <a href=""></a>
                                                                                <span>
                                                                                    <a href=""></a>
                                                                                    <span>
                                                                                        <a href=""></a>
                                                                                        <span>

                                                                                        </span>
                                                                                    </span>
                                                                                </span>
                                                                            </span>
                                                                        </span>
                                                                    </span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </span>
                                                </span>
                                            </span>
                                        </li>
                                        <li class="c">
                                            <label>������������</label>
                                            <span class="stars-vote stars-vote-a vote-<?= (int) $sbr->feedback['a_rate'] ?>">
                                                <input type="hidden" name="sbr_feedback[a_rate]" value="<?= (int) $sbr->feedback['a_rate'] ?>" />
                                                <span>
                                                    <a href=""></a>
                                                    <span>
                                                        <a href=""></a>
                                                        <span>
                                                            <a href=""></a>
                                                            <span>
                                                                <a href=""></a>
                                                                <span>
                                                                    <a href=""></a>
                                                                    <span>
                                                                        <a href=""></a>
                                                                        <span>
                                                                            <a href=""></a>
                                                                            <span>
                                                                                <a href=""></a>
                                                                                <span>
                                                                                    <a href=""></a>
                                                                                    <span>
                                                                                        <a href=""></a>
                                                                                        <span>

                                                                                        </span>
                                                                                    </span>
                                                                                </span>
                                                                            </span>
                                                                        </span>
                                                                    </span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </span>
                                                </span>
                                            </span>
                                <div class="tip tip-t2" style="top:215px;left:0px"></div>
                                        </li>
                                    </ul>
                        </div>
                    </div>
                    </div>
                <? } ?>
                <? if( $need_payouts ) { ?>
                    <? if( $norez_block ) { ?>
                        <div class="form-block<?=($fbc++?'':' first')?>">
                            <div class="form-el">
                                <p><strong>����������� ���������� ��������� ����� ���� ���� ������ ��������� �������� �������:</strong><br />����������� ������ <span class="dred">� ������������ ����� � <?=sbr_meta::view_cost($sbr->maxNorezCost(), exrates::BANK)?></span> (����������� <?=sbr::MAX_COST_USD?> �������� ���).</p>
                                <? if($sbr->user_reqvs['rezdoc_status']!=sbr::RS_ACCEPTED) { ?>
                                  <p>�� ��������� ��������������� ������ (������ �� �������), ��� ���������� ������� ������� � �������� ���� ������ � �����, ��� �� �������� ���������� ����� ������.<br /><a href="/docs/dokumenty_na_rezidentstvo.docx" target="_blank">�������� � �����������</a></p>
                                <? } ?>
                                <div><label><input type="checkbox" name="notnp" value="1" class="i-chk" onclick="SBR.setNoNP(this)"<?=($notnp || !$action && in_array($sbr->user_reqvs['rezdoc_status'], array(sbr::RS_ACCEPTED,sbr::RS_WAITING)) ? ' checked="checked"' : '')?> />
                                  ����� ������ �� ���� ����� &laquo;����� �� �������&raquo;
                                  <?=($sbr->user_reqvs['rezdoc_status']==sbr::RS_ACCEPTED ? '(������� � ����������� ��������)' : '������ �� �������� ������� � �����������')?>.</label>
                                </div>
                            </div>
                        </div>
                    <? } ?>
                    <div class="form-block<?=($fbc++?'':' first')?>">
                        <div class="form-el">
                            <p><strong>�������� ������� ��� ��� ������ ��������� �����:</strong> <a href="/users/<?=$sbr->login?>/setup/finance/">��������/�������� �������</a></p>
                            <span><input type="hidden" name="act" /></span>
                            <div class="tip tip-t2" id="act_error"  style="top:auto;margin-top:-14px;margin-left:390px"></div>


                            <ul class="nr-money-type c">
                                <?
                                  foreach($EXRATE_CODES as $ex_code=>$ex_name) { 
                                    if(!$stage->checkPayoutSys($ex_code)) continue;
                                    $dsbl[$ex_code] = 0;
                                ?>
                                  <li><label>
                                    <input type="radio" name="credit_sys" value="<?=$ex_code?>" 
                                           onclick="SBR.changeRecSys(this.value);SBR.adErrCls(SBR.form.act); <?= ($ex_code == exrates::WMR ? "checkWMDoc();" : "clearCheckWMDoc();")?>"
                                           <? if(!$stage->checkPayoutReqvs($ex_code)) { echo ' disabled="disabled"'; $dsbl[$ex_code] = 1; } ?>
                                           <?=(!$dsbl[$ex_code] && ($sbr->cost_sys==$ex_code || $stage->request['credit_sys']==$ex_code) ? ' checked="checked"' : '')?> />
                                        <?=$ex_name[0]?>
                                  </label></li>
                                <? } ?>
                            </ul>
                        </div>
                    </div>
                <? } ?> 
                <? foreach($EXRATE_CODES as $ex_code=>$ex_name) {
                       if(!$stage->checkPayoutSys($ex_code)) continue;
                       if($need_payout && !$stage->checkPayoutReqvs($ex_code)) { $dsbl[$ex_code] = 1; }
                       
                   } 
                if($stage->request['credit_sys'] == exrates::WMR && sbr_meta::checkWMDoc($sbr->user_reqvs)) $disable_submit = true;   ?>
                <div class="form-block last">
                    <div class="form-el">
                        <div class="nr-prj-btns c">
                            <span class="btn-o-green">
                                <a href="javascript:;" onclick="<?= !$dsbl || in_array(0, $dsbl) ? 'if(!$(this).hasClass(\'btnr-disabled\')) { SBR.sendForm() }' : '' ?>" class="btnr <?= ( $dsbl && !in_array(0, $dsbl) ) || $disable_submit ? 'btnr-disabled' : '' ?> btnr-green2" id="submit_btn"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">���������</span></span></span></a>
                            </span>
                        </div>
                        <? if($dsbl && !in_array(0, $dsbl)) { ?>
                        <div class="form fs-w form-acw">
                            <b class="b1"></b>
                            <b class="b2"></b>
                            <div class="form-in">
                                <strong>������ ��� ����������, ����������, ��������� ������ �� <a href="/users/<?=$sbr->login?>/setup/finance/">�������� �������</a></strong>
                            </div>
                            <b class="b2"></b>
                            <b class="b1"></b>
                        </div>
                        <? } ?>
                        <strong class="nr-csum" id="credit_sum">&nbsp;</strong>
                    </div>
										<input type="hidden" name="site" value="<?=$site?>" />
										<input type="hidden" name="id" value="<?=$stage->id?>" />
										<input type="hidden" name="action" value="complete" />
                </div>
            </form>
		</div>
		<b class="b2"></b>
		<b class="b1"></b>
	</div>
    <?=$sbr->view_sign_alert()?>
    <?php if($stage->request['credit_sys'] == exrates::WMR && sbr_meta::checkWMDoc($sbr->user_reqvs)) {?>
    <div class="nr-block-imp" id="wmdoc_alert">
        <b class="b1"></b>
        <b class="b2"></b>
        <div class="form-in">
            ��� ������ Webmoney � �������� ������ ������ ��������� ��������� ���� "<a href="/users/<?=$sbr->login?>/setup/finance/#WMDOC">���������� ������</a>" � ����� "����������� ��������" �� �������� "<a href="/users/<?=$sbr->login?>/setup/finance/">�������</a>"
        </div>
        <b class="b2"></b>
        <b class="b1"></b>
    </div>
    <?php } ?>
    <? include('tpl.stage-msgs.php'); ?>

</div>