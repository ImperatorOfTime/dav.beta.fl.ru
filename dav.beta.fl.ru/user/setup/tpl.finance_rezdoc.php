<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
/* deprecated: �����, ����� ����� ������ � ������� �� ������� */ ?>
<div class="form form-rez">
    <b class="b1"></b>
    <b class="b2"></b>
    <div class="form-in">
        <div class="form-block first last">
            <div class="form-el">
                <label class="form-l"><strong>������� � ������������:</strong></label>
                <div class="form-value">
                    <? if($reqvs['rezdoc_status']==sbr::RS_WAITING) { ?>
                      <span>���������</span>
                    <? } else if($reqvs['rezdoc_status']==sbr::RS_DENIED) { ?>
                      <span class="form-merr">������������</span>
                    <? } else if($reqvs['rezdoc_status']==sbr::RS_ACCEPTED) { ?>
                      <span class="form-mvalid">��������</span>
                    <? } else { ?>
                      <span>��� ������</span>
                    <? } ?>
                    <span id="rezdoc_comment_out"><?=($reqvs['rezdoc_comment'] ? '('.reformat($reqvs['rezdoc_comment'], 40, 0, 1).')' : '')?></span>
                    <? if(hasPermissions('users')) { ?>
                        <? if($reqvs['rezdoc_status']) { ?>
                          <a href="javascript:;" class="lnk-dot-blue" onclick="$('rezdoc_comment').innerHTML=$('rezdoc_comment_out').innerHTML.replace(/^\(/,'').replace(/\)$/,'');SBR.rezDocOpenWin()">�������� �����������</a>
                        <? } ?>
                        <br /><br />
                        <div>
                            <input type="button" onclick="SBR.rezDocOpenWin(<?=sbr::RS_WAITING?>)" <?=$reqvs['rezdoc_status']==sbr::RS_WAITING ? ' disabled="disabled"' : ''?> value="������� ���������" />
                            <input type="button" onclick="SBR.rezDocOpenWin(<?=sbr::RS_ACCEPTED?>)" <?=$reqvs['rezdoc_status']==sbr::RS_ACCEPTED ? ' disabled="disabled"' : ''?> value="������� �������" />
                            <input type="button" onclick="SBR.rezDocOpenWin(<?=sbr::RS_DENIED?>)" <?=$reqvs['rezdoc_status']==sbr::RS_DENIED ? ' disabled="disabled"' : ''?> value="������� ���������" />
                        </div>
                    <? } ?>
                </div>
            </div>
        </div>
    </div>
    <b class="b2"></b>
    <b class="b1"></b>
</div>
