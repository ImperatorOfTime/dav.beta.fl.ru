<?php
/**
 * ������ ����� ����� �������� �������������� ������������ � ������������ �� �������� (!�� �������)
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IN_STDF') ) { 
    header("HTTP/1.0 404 Not Found"); // ��� �����
    exit();
}
?>
<input type="hidden" name="po_id" id="adm_edit_po_id" value="<?=$msg['po_id']?>">
<input type="hidden" name="project_id" id="adm_edit_project_id" value="<?=$msg['project_id']?>">
<input type="hidden" name="user_id" id="adm_edit_user_id" value="<?=$msg['user_id']?>">

<?=_parseHiddenParams($aParams)?>

<div id="adm_edit_tab_div1">
    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_msg">�����</label>
        <div class="b-textarea_inline-block b-textarea_width_550">
            <textarea id="adm_edit_msg_source" style="display:none" cols="50" rows="20"><?=input_ref($msg['post_text'])?></textarea>
            <textarea id="adm_edit_msg" name="post_text" onfocus="adm_edit_content.hideError('msg')" class="b-textarea__textarea_width_full b-textarea__textarea_height_70" cols="77" rows="5"></textarea>
        </div>

        <div id="div_adm_edit_err_msg" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_msg">���� ��������� �����������</div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
</div>