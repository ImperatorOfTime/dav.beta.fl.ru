<div id="ov-notice" class="b-shadow b-shadow_width_540 b-shadow_hide b-shadow_center b-shadow_pad_10 b-shadow_zindex_11">
                <a class="close" style="float: right;" href="javascript:void(0);" onclick="banned.commit(banned.banUid,(banned.buffer[banned.banUid].action='close'));$('ov-notice').addClass('b-shadow_hide');return false;"><img height="21" width="21" alt="" src="/images/btn-close.png"></a>
                
                <h3 class="b-layout__h3">�������������� ��� <a id="warn_name" class="b-layout__link" target="_blank" href="#"></a></h3>
                
                <div class="form-el">
                    <label class="form-l">��������:</label>
                    <div class="form-radios">
                        <div class="form-value" style="padding: 3px 10px 0 10px;">
                            <label id="warn_label">�����</label>
                        </div>
                    </div>
				</div>
                
                <div class="form-el">
                    <label class="form-l" style="padding-top:0;">�������:</label>
                    <div class="form-value reason" id="warn_div" style="padding: 3px 10px 0 10px;">
                        <select disabled><option>���������...</option></select>
                        <textarea class="b-textarea__textarea" name="" cols="" rows=""></textarea>
                    </div>
                </div>
                <h3 id="warn_delreason_title" class="b-layout__h3" style="display: none;">������� ��������</h3>

                <div class="form-el" style="display: none;">
                    <label class="form-l">�������:</label>
                    <div class="form-value reason" id="warn_div_stream" style="padding: 3px 10px 0 10px;">
                        <select disabled><option>���������...</option></select>
                        <textarea class="b-textarea__textarea" name="" cols="" rows=""></textarea>
                    </div>
                </div>

                <div class="ov-btns">
                    <button type="button" id="warn_btn" onclick="banned.commit(banned.banUid,$('bfrm_'+banned.banUid).get('value') );" class="b-button b-button_flat b-button_flat_green">���������</button>&#160;&#160;&#160;
                    <a id="warn_close" href="javascript:void(0);" onclick="banned.commit(banned.banUid,(banned.buffer[banned.banUid].action='close'));$('ov-notice').addClass('b-shadow_hide');return false;" class="b-buttons__link">������</a>
                </div>
                <style>#ov-notice textarea{ width:100%; margin-top:5px !important;}#warn_div{ width:400px;}</style>
</div>