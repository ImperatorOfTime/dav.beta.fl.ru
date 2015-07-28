<script type="text/javascript">
var SBR;
window.addEvent('domready', function() { SBR = new Sbr('arbitrageFrm'); } );
Sbr.prototype.ERRORS=<?=sbr_meta::jsInputErrors($stage->error['arbitrage'])?>;
</script>
<div class="tabs-in">
	<div class="nr-arb-send">
        <form action="." method="post" enctype="multipart/form-data" id="arbitrageFrm">
            <h3>��������� � ����������� ��������</h3>
            <p>������ �������� �������� �� ��, ��� �������� ��������� �� �������� ������ ��������� � ������������ � �������.<br />�� ��������������� ��������� � ������ ������, �������� Skype, ICQ, ����� � ������.</p>
            <div class="form nr-arb-send-agree">
                <b class="b1"></b>
                <b class="b2"></b>
                <div class="form-in">
                    <span><input type="checkbox" name="iagree" id="f1" onclick="SBR.form.send.disabled=!this.checked"/><label for="f1"> � �������, ��� ��� ����������� �������</label></span>
                    <div class="tip tip-t2" style="top:auto"></div>
                </div>
                <b class="b2"></b>
                <b class="b1"></b>
            </div>
            <div class="nr-arb-send-prj">
                <h4>������:</h4>
                <div>
                    <a href="?site=Stage&id=<?=$stage->id?>"><?=reformat($stage->name,55,0,1)?></a><br/>
                    <span><strong>#<?=$stage->id?></strong></span>
                </div>
            </div>
            <div class="nr-arb-send-form">
                <label for="f2">������� ��������� ��������:</label>
                <span><textarea cols="140" rows="5" name="descr" id="f2"><?=htmlspecialchars($descr)?></textarea></span>
                <div class="tip tip-t2" style="top:auto;margin-top:-6px"></div>
                <ul class="cl-form-o c">
                    <li><a href="javascript:;" id="arb_fs_toggler">���������� ���� � ���������</a></li>
                </ul>
                <div class="cl-form-files" id="arb_files_box">
                    <ul class="form-files-list" id="arb_files_list"><li class="c"><input type="file" size="23" class="i-file" name="attach[]" /></li></ul>
                    <div class="form-files-inf">
                        <strong class="form-files-max">������������ ������ �����: <?=sbr_stages::ARB_FILE_MAX_SIZE/1024/1024?> ��</strong><br/>
                        ����� ��������� �������� ��������� � ��������: <?=implode(', ', $GLOBALS['disallowed_array'])?>
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <div class="tip-largefile">
                    <span><input type="hidden" name="err_attach" /></span><div class="tip tip-t2" style="top:-35px; left: 0; z-index:100;"></div>
                </div>
                <div class="nr-send-btns">
                    <input type="submit" name="send" value="��������� � ��������" disabled="disabled" class="i-btn" />
                    <input type="submit" name="cancel" value="��������" class="i-btn" />
                </div>
								<input type="hidden" name="id" value="<?=$stage->id?>" />
								<input type="hidden" name="site" value="<?=$site?>" />
								<input type="hidden" name="action" value="arbitration" />
            </div>
        </form>
	</div>
</div>
