<?php
/**
 * ������ ����� ����� �������� �������������� ��������� � �������� � ���������
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IN_STDF') ) { 
    header("HTTP/1.0 404 Not Found"); // ��� �����
    exit();
}

$prj['cost_hour']      = floatval( $prj['cost_hour'] );
$prj['cost_1000']      = floatval( $prj['cost_1000'] );
$prj['cost_type_hour'] = intval( $prj['cost_type_hour'] );
$prj['cost_type']      = intval( $prj['cost_type'] );
$prj['cost_from']      = floatval( $prj['cost_from'] );
$prj['cost_to']        = floatval( $prj['cost_to'] );
$prj['time_type']      = intval( $prj['time_type'] );
$prj['time_from']      = intval( $prj['time_from'] );
$prj['time_to']        = intval( $prj['time_to'] );
?>
<div class="b-menu b-menu_rubric b-menu_padbot_10">
    <ul class="b-menu__list">
        <li id="adm_edit_tab_i1" class="b-menu__item b-menu__item_active"><span class="b-menu__b1"><span class="b-menu__b2">��������</span></span></li>
        <li id="adm_edit_tab_i2" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(2); return false;">������� ��������������</a></li>
    </ul>
</div>

<input type="hidden" id="adm_edit_user_id" name="user_id" value="<?=$user_id?>">
<input type="hidden" id="adm_edit_prof_id" name="prof_id" value="<?=$prof_id?>">

<?=_parseHiddenParams($aParams)?>

<? if ( $prj['proftext'] == 't' ) { ?>
<input type="hidden" name="prof_cost_type" value="<?=$prj['cost_type']?>" />
<input type="hidden" name="prof_cost_type_hour" value="<?=$prj['cost_type_hour']?>" />
<input type="hidden" name="prof_cost_hour" value="<?=$prj['cost_hour']?>" />
<input type="hidden" name="prof_cost_from" value="<?=$prj['cost_from']?>" />
<input type="hidden" name="prof_cost_to" value="<?=$prj['cost_to']?>" />
<input type="hidden" name="prof_time_type" value="<?=$prj['time_type']?>" />
<input type="hidden" name="prof_time_from" value="<?=$prj['time_from']?>" />
<input type="hidden" name="prof_time_to" value="<?=$prj['time_to']?>" />
<?php } else { ?>
<input type="hidden" name="prof_cost_1000" value="<?=$prj['cost_1000']?>" />
<?php } ?>

<div id="adm_edit_tab_div1">
    <?php if ( $prj['proftext'] == 't' ) { ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="cost_1000">1000 ������</label>
        <div class="b-input b-input_inline-block b-input_width_60">
            <input type="text" id="adm_edit_cost_1000" name="prof_cost_1000" value="<?=$prj['cost_1000']?>" maxlength="6" class="b-input__text">
        </div>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3">&nbsp;</label>
        <div class="b-input_inline-block b-input_width_60">
            <select id="adm_edit_cost_type" name="prof_cost_type" class="b-select__select b-select__select_width_full">
                <option value="0" <?=($prj['cost_type'] == 0 ? "selected='selected'" : "")?>>USD</option>
                <option value="1" <?=($prj['cost_type'] == 1 ? "selected='selected'" : "")?>>Euro</option>
                <option value="2" <?=($prj['cost_type'] == 2 ? "selected='selected'" : "")?>>���</option>
                <option value="3" <?=($prj['cost_type'] == 3 ? "selected='selected'" : "")?>>FM</option>
            </select>
        </div>
    </div>
    <?php } else { ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80">��������� �����</label>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3" for="adm_edit_cost_from">��&nbsp;</label>
        <div class="b-input b-input_inline-block b-input_width_60">
            <input type="text" id="adm_edit_cost_from" name="prof_cost_from" value="<?=$prj['cost_from']?>" class="b-input__text" maxlength="10">
        </div>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3" for="adm_edit_cost_to">&nbsp;��&nbsp;</label>
        <div class="b-input b-input_inline-block b-input_width_60">
            <input type="text" id="adm_edit_cost_to" name="prof_cost_to" value="<?=$prj['cost_to']?>" class="b-input__text" maxlength="10">
        </div>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3">&nbsp;</label>
        <div class="b-input_inline-block b-input_width_60">
            <select id="adm_edit_cost_type" name="prof_cost_type" class="b-select__select b-select__select_width_full">
                <option value="0" <?=($prj['cost_type'] == 0 ? "selected='selected'" : "")?>>USD</option>
                <option value="1" <?=($prj['cost_type'] == 1 ? "selected='selected'" : "")?>>Euro</option>
                <option value="2" <?=($prj['cost_type'] == 2 ? "selected='selected'" : "")?>>���</option>
                <option value="3" <?=($prj['cost_type'] == 3 ? "selected='selected'" : "")?>>FM</option>
            </select>
        </div>
    </div>
    
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80">�����</label>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3" for="adm_edit_time_from">��&nbsp;</label>
        <div class="b-input b-input_inline-block b-input_width_60">
            <input type="text" id="adm_edit_time_from" name="prof_time_from" value="<?=$prj['time_from']?>" class="b-input__text" maxlength="10">
        </div>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3" for="adm_edit_time_to">&nbsp;��&nbsp;</label>
        <div class="b-input b-input_inline-block b-input_width_60">
            <input type="text" id="adm_edit_time_to" name="prof_time_to" value="<?=$prj['time_to']?>" class="b-input__text" maxlength="10">
        </div>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3">&nbsp;</label>
        <div class="b-input_inline-block b-input_width_100">
            <select id="adm_edit_time_type" name="prof_time_type" class="b-select__select b-select__select_width_full">
                <option value='0'<? if ($prj['time_type']==0) { ?> selected="selected"<? } ?>>� �����</option>
                <option value='1'<? if ($prj['time_type']==1) { ?> selected="selected"<? } ?>>� ����</option>
                <option value='2'<? if ($prj['time_type']==2) { ?> selected="selected"<? } ?>>� �������</option>
                <option value='3'<? if ($prj['time_type']==3) { ?> selected="selected"<? } ?>>� �������</option>
            </select>
        </div>
    </div>
    <?php } ?>
    
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="cost_hour">��� ������</label>
        <div class="b-input b-input_inline-block b-input_width_60">
            <input type="text" id="adm_edit_cost_hour" name="prof_cost_hour" value="<?=$prj['cost_hour']?>" class="b-input__text" maxlength="5">
        </div>
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_padtop_3">&nbsp;</label>
        <div class="b-input_inline-block b-input_width_60">
            <select id="adm_edit_cost_type_hour" name="prof_cost_type_hour" class="b-select__select b-select__select_width_full">
                <option value="0" <?=($prj['cost_type_hour'] == 0 ? "selected='selected'" : "")?>>USD</option>
                <option value="1" <?=($prj['cost_type_hour'] == 1 ? "selected='selected'" : "")?>>Euro</option>
                <option value="2" <?=($prj['cost_type_hour'] == 2 ? "selected='selected'" : "")?>>���</option>
                <option value="3" <?=($prj['cost_type_hour'] == 3 ? "selected='selected'" : "")?>>FM</option>
            </select>
        </div>
    </div>
    
    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_msg">���������</label>
        <div class="b-textarea_inline-block b-textarea_width_550">
            <textarea id="adm_edit_msg_source" style="display:none" cols="50" rows="20"><?=input_ref($prj['portf_text'])?></textarea>
            <textarea id="adm_edit_msg" name="prof_text" onfocus="adm_edit_content.hideError('msg')" class="b-textarea__textarea_width_full b-textarea__textarea_height_70" cols="77" rows="5"></textarea>
        </div>
        <label class="b-check__label b-fon_padleft_80">����� ������������ &lt;b&gt;&lt;i&gt;&lt;p&gt;&lt;ul&gt;&lt;li&gt;</label>

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
    
    <?php if( $prj['prof_id'] > 0 ) { ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_msg">�������� �����</label>
        <div class="b-textarea_inline-block b-textarea_width_550">
            <textarea id="adm_edit_keys" name="prof_keys" class="b-textarea__textarea_width_full b-textarea__textarea_height_70" cols="77" rows="5"><?=stripcslashes(implode(", ", $keys))?></textarea>
        </div>
        <label class="b-check__label b-fon_padleft_80">�������� ����� �������� ����� �������.</label>
    </div>
    <?php } ?>
    
    <?php if ( $user->is_pro == 't' ) { ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">&nbsp;</label>
        <div class="b-input_inline-block b-input_width_545">
            <div class="b-check b-check_padtop_3">
                <input id="adm_edit_show_preview" class="b-check__input" type="checkbox" name="show_preview" value="1" <?=(($prj['gr_prevs'] == 't')?"checked='checked'":"")?> />
                <label class="b-check__label" for="adm_edit_show_preview">�������� ������ � �������</label>
            </div>
        </div>
    </div>
    <?php } ?>
</div>