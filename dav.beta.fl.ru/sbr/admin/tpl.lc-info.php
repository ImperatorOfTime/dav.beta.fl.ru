<div style="float: right;">
    <input type="button" value="����" onclick="xajax_aGetLogPSKBInfo(<?= $lc['lc_id'] ?>);"/>
    <input style="margin-left: 10px !important;" type="button" value="��������" onclick="xajax_aGetLCInfo(<?= $lc['sbr_id'] ?>);"/>
    <input style="margin-left: 10px !important;" type="button" value="�������" onclick="$$('#lc-info-popup, #lc-info-popup .b-shadow').addClass('b-shadow_hide');"/>
</div>

<div id="log_pskb_<?= $lc['lc_id']?>" class="i-shadow_center b-shadow_hide" style="z-index:10000">																						
    <div class="b-shadow b-shadow_width_950 b-shadow_zindex_11">
        <div class="b-shadow__right">
            <div class="b-shadow__left">
                <div class="b-shadow__top">
                    <div class="b-shadow__bottom">
                        <div id="log_pskb_<?= $lc['lc_id']?>-body" class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-shadow__tl"></div>
        <div class="b-shadow__tr"></div>
        <div class="b-shadow__bl"></div>
        <div class="b-shadow__br"></div>
        <div class="b-shadow__icon b-shadow__icon_close" onclick="$('log_pskb_<?= $lc['lc_id']?>').addClass('b-shadow_hide');"></div>
    </div>
</div>

<h1>���-<?= $sbr->id ?>-�/�#<?= $lc['lc_id'] ?> (DOL#<?= (int)$lc['dol_paymentid']?>)</h1>
<p>&nbsp;</p>
<p>&nbsp;</p>
<h3>������ �����������</h3>
<table class="nr-a-opinions" cellspacing="0" style="width: 100%">
    <col width="50" />
    <col width="80" />
    <col width="80" />
    <col width="80" />
    <col width="80" />
    <col width="100" />
    <col width="100" />
    <col width="150" />
    <col />
    <thead>
        <tr>
            <th></th>
            <th>�����</th>
            <th>����� (�����.)</th>
            <th>���� ��������</th>
            <th>���� ����������</th>
            <th>������</th>
            <th>������</th>
            <th>������</th>
            <th>��������� �����</th>
        </tr>
    </thead>
    <tbody>
        <tr class="odd">
            <td><strong>����</strong></td>
            <td><?= $lc['sum'] ?></td>
            <td> - </td>
            <td><?= date('d.m.Y', strtotime($lc['dateCoverLC'])) ?></td>
            <td><?= date('d.m.Y', strtotime($lc['dateEndLC'])) ?></td>
            <td><?= $lc['covered'] ? date('d.m.Y H:i', strtotime($lc['covered'])) : ' - ' ?></td>
            <td><?= $lc['ended'] ? date('d.m.Y H:i', strtotime($lc['ended'])) : ' - ' ?></td>
            <td><?= pskb::$state_adm_messages[$lc['state']] ?></td>
            <td><?= $lc['stateReason'] ? $lc['stateReason'] : ' - ' ?></td>
        </tr>
        <tr class="even">
            <td><strong>����</strong></td>
            <td><?= $pskb_lc->sum ?></td>
            <td><?= $pskb_lc->sumOpen ?></td>
            <td></td>
            <td></td>
            <td><?= $pskb_lc->cover ?></td>
            <td></td>
            <td><?= pskb::$state_adm_messages[$pskb_lc->state] ?></td>
            <td><?= $pskb_lc->stateReason ?></td>
        </tr>
    </tbody>
</table>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<h3>��������� ������</h3>
<table class="nr-a-opinions" cellspacing="0" style="width: 100%">
    <col />
    <col width="180" />
    <col width="30" />
    <col width="60" />
    <col width="100" />
    <col width="120" />
    <col width="80" />
    <col width="60" />
    <col width="130" />
    <thead>
        <tr>
            <th>�����</th>
            <th>���</th>
            <th>�����</th>
            <th>���.</th>
            <th>�����</th>
            <th>����</th>
            <th>���</th>
            <th>���/id-��</th>
            <th>����. ��������� �����������</th>
        </tr>
    </thead>
    <tbody>
        <tr class="odd">
            <td><span class="b-username__login b-username__login_color_6db335"><a href="/users/<?= $sbr->data['emp_login'] ?>/" target="_blank" class="b-username__link">[<?= $sbr->data['emp_login'] ?>]</a></span></td>
            <td><?= $lc['nameCust'] ?></td>
            <td><?= $lc['tagCust'] ? '��.' : '���.' ?></td>
            <td><?= $lc['alienCust'] ? '��-��' : '��' ?></td>
            <td><?= $lc['numCust'] ?></td>
            <td><?= $lc['accCust'] ?></td>
            <td><?= $lc['innCust'] ?></td>
            <td><?= $lc['psCust'] ?></td>
            <td>
                <? if ($lc['alienCust'] && $ls['ps_emp'] == onlinedengi::BANK_YL) { ?>
                ����: <?= $lc['nameBankCust'] ?><br/>
                SWIFT: <?= $lc['swiftCust'] ?><br/>
                �/�: <?= $lc['corAccbankCust'] ?><br/>
                <? } ?>
            </td>
        </tr>
        <tr class="even">
            <td><span class="b-username__login b-username__login_color_f2922a"><a href="/users/<?= $sbr->data['frl_login'] ?>/" target="_blank" class="b-username__link">[<?= $sbr->data['frl_login'] ?>]</a></span></td>
            <td><?= $lc['namePerf'] ?></td>
            <td><?= $lc['tagPerf'] ? '��.' : '���.' ?></td>
            <td><?= $lc['alienPerf'] ? '��-��' : '��' ?></td>
            <td><?= $lc['numPerf'] ?></td>
            <td><?= $lc['accPerf'] ?></td>
            <td><?= $lc['innPerf'] ?></td>
            <td><?= $lc['psPerf'] ?></td>
            <td>
                <? if ($lc['alienPerf'] && $ls['ps_frl'] == onlinedengi::BANK_YL) { ?>
                ����: <?= $lc['nameBankPerf'] ?><br/>
                SWIFT: <?= $lc['swiftPerf'] ?><br/>
                �/�: <?= $lc['corAccbankPerf'] ?><br/>
                <? } ?>
            </td>
        </tr>
    </tbody>
</table>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<h3>�������</h3>

<? foreach($sbr->getStages() as $stage) { $sbr_uid = $stage->getOuterNum();?>
<p>&nbsp;</p>
<h3>���� <a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?access=A&site=Stage&id=<?= $stage->id ?>"><?= $stage->getOuterNum() ?></a>&nbsp;&nbsp;<input type="button" onclick="xajax_aGetHistoryLC('<?= $lc['lc_id']?>', '<?= $sbr_uid?>')" value="�������"/></h3> 
<div id="history_lc_<?= $sbr_uid?>" class="i-shadow_center  b-shadow_hide" style="z-index:10000">																						
    <div class="b-shadow b-shadow_width_950 b-shadow_zindex_11">
        <div class="b-shadow__right">
            <div class="b-shadow__left">
                <div class="b-shadow__top">
                    <div class="b-shadow__bottom">
                        <div id="history_lc_<?= $sbr_uid?>-body" class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-shadow__tl"></div>
        <div class="b-shadow__tr"></div>
        <div class="b-shadow__bl"></div>
        <div class="b-shadow__br"></div>
        <div class="b-shadow__icon b-shadow__icon_close" onclick="$('history_lc_<?= $sbr_uid?>').addClass('b-shadow_hide');"></div>
    </div>
</div>
<? if (count($payouts[$stage->id])) { ?>
<?
    $emp_percent = 0;
    $frl_percent = 1;
    
    if ($stage->arbitrage === false) {
        $stage->getArbitrage(false, false);
    }
    
    $doc_act = $sbr->getDocs(NULL, NULL, true, $stage->id, true);
    
    if($stage->arbitrage && $stage->arbitrage['resolved']) {
        $emp_percent = abs(sbr::EMP - $stage->arbitrage['frl_percent']);
        $frl_percent = abs(sbr::FRL - $stage->arbitrage['frl_percent']);
    }
?>
<table class="nr-a-opinions" cellspacing="0" style="width: 100%">
    <thead>
        <tr>
            <th></th>
            <th>����� ���������</th>
            <th>����� (����� %%)</th>
            <th>���� ������������</th>
            <th>��������� � ���</th>
            <th>��������� (� �����)</th>
            <th>��������</th>
            <th>������</th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($payouts[$stage->id] as $i => $po_row) { $target = ($po_row['user_id'] == $sbr->data['emp_id'] ? 0 : 1); ?>
        <tr class="<?= (++$i % 2 == 0 ? 'even' : 'odd') ?>">
            <td>
                <a href="javascript:void(0)" onclick="xajax_aGetHistoryLC('<?= $lc['lc_id']?>', '<?= $sbr_uid?>', <?= $target;?>)"><?= $po_row['user_id'] == $sbr->data['emp_id'] ? '��������' : '�����������' ?></a>
                
                <div id="user<?= $target; ?>_history_lc_<?= $sbr_uid;?>" class="i-shadow_center  b-shadow_hide" style="z-index:10000">																						
                    <div class="b-shadow b-shadow_width_950 b-shadow_zindex_11">
                        <div class="b-shadow__right">
                            <div class="b-shadow__left">
                                <div class="b-shadow__top">
                                    <div class="b-shadow__bottom">
                                        <div id="user<?= $target; ?>_history_lc_<?= $sbr_uid?>-body" class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="b-shadow__tl"></div>
                        <div class="b-shadow__tr"></div>
                        <div class="b-shadow__bl"></div>
                        <div class="b-shadow__br"></div>
                        <div class="b-shadow__icon b-shadow__icon_close" onclick="$('user<?= $target; ?>_history_lc_<?= $sbr_uid?>').addClass('b-shadow_hide');"></div>
                    </div>
                </div>
            </td>
            <td><?= round($stage->data['cost'] * ($po_row['user_id'] == $sbr->data['emp_id'] ? $emp_percent : $frl_percent), 2) ?></td>
            <td><?= $po_row['credit_sum'] ?></td>
            <td><?= date('d.m.Y H:i', strtotime($po_row['requested'])) ?></td>
            <td><?= $po_row['completed'] ? date('d.m.Y H:i', strtotime($po_row['completed'])) : ' - ' ?></td>
            <td><?= $po_row['bank_completed'] ? date('d.m.Y H:i', strtotime($po_row['bank_completed'])) : ' - ' ?></td>
            <td><?= ( $po_row['executed'] ? date('d.m.Y H:i', strtotime($po_row['executed'])) : "--" ); ?></td>
            <td><?= pskb::$state_po_messages[$po_row['state']] ?></td>
        </tr>
        <? } ?>
    </tbody>
</table>



<? } else { ?>
��� ���������� � �������� �� ������� �����.
<? } ?>
<? } ?>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<h3>���������</h3>
<?php 
foreach($sbr->getStages() as $stage) { 
    ?>
    <p>&nbsp;</p>
    <h3>��������� ����� <a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?access=A&site=Stage&id=<?= $stage->id ?>"><?= $stage->getOuterNum() ?></a></h3>
    <?
    if (count($payouts[$stage->id])) {
        $doc_act = $sbr->getDocs(NULL, NULL, true, $stage->id, true);
        if($doc_act) { ?>
        
        <table class="nr-a-opinions" cellspacing="0" style="width: 100%">
            <colgroup>
                <col width="30%" />
                <col width="20%" />
                <col width="15%" />
                <col width="35%" />
            </colgroup>
            <thead>
                <tr>
                    <th>�������� ���������</th>
                    <th>���� ������������</th>
                    <th>���������</th>
                    <th>��������</th>
                </tr>
            </thead>
            <tbody id="doc_content_<?=$stage->id?>">
                <? include($_SERVER['DOCUMENT_ROOT'].'/sbr/admin/tpl.lc-docinfo.php'); ?>
            </tbody>
        </table>
        <?php 
        }
    } else {
        ?>��������� �� ������������<?
    }
}
?>