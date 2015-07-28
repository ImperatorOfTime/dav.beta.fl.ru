<?php
/**
 * ������ ����� ����� �������� �������������� ������ � �����������
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IN_STDF') ) { 
    header("HTTP/1.0 404 Not Found"); // ��� �����
    exit();
}
?>
<div class="b-menu b-menu_rubric b-menu_padbot_10">
    <ul class="b-menu__list">
        <li id="adm_edit_tab_i1" class="b-menu__item b-menu__item_active"><span class="b-menu__b1"><span class="b-menu__b2">��������</span></span></li>
        <li id="adm_edit_tab_i2" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(2); return false;">�����</a></li>
        <li id="adm_edit_tab_i3" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(3); return false;">�����</a></li>
        <li id="adm_edit_tab_i4" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(4); return false;">������� ��������������</a></li>
    </ul>
</div>

<input type="hidden" name="commune_id" value="<?=$mess['commune_id']?>">
<input type="hidden" name="user_id" value="<?=$mess['user_id']?>">
<input type="hidden" name="user_login" value="<?=$mess['user_login']?>">
<input type="hidden" name="user_uname" value="<?=$mess['user_uname']?>">
<input type="hidden" name="user_usurname" value="<?=$mess['user_usurname']?>">

<?=_parseHiddenParams($aParams)?>

<?php // �������� ?>
<div id="adm_edit_tab_div1">
    <?php // ��������� ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_title">���������</label>
        <div class="b-input b-input_inline-block b-input_width_545">
            <input type="text" id="adm_edit_title" name="title" value="<?=$mess['title']?>" class="b-input__text" size="80" onfocus="adm_edit_content.hideError('title')">
        </div>
        
        <div id="div_adm_edit_err_title" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_title"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <?php // ����� ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_msg">�����</label>
        <div class="b-textarea_inline-block b-textarea_width_550">
            <textarea id="adm_edit_msg_source" style="display:none" cols="50" rows="20"><?=input_ref($mess['msgtext'])?></textarea>
            <textarea id="adm_edit_msg" name="msgtext" class="<?= commune::IS_NEW_WYSIWYG ? "ckeditor" : "wysiwyg"?>" conf="insertcode" cols="77" rows="5"></textarea>
        </div>

        <div id="div_adm_edit_err_msg" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_msg"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <?php // YouTube ?>
    <div class="b-form b-form_padtop_10">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_youtube_link">YouTube</label>
        <div class="b-input b-input_inline-block b-input_width_545">
            <input type="text" id="adm_edit_youtube_link" name="youtube_link" value="<?=$mess['youtube_link']?>" class="b-input__text" size="80" onfocus="adm_edit_content.hideError('youtube_link')">
        </div>

        <div id="div_adm_edit_err_youtube_link" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_youtube_link"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <?php // ������ ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">������</label>
        <div class="b-input_inline-block b-input_width_545">
            <select id="b-select__select" name="category_id" class="b-select__select b-select__select_width_180">
                <option>��� �������</option>
                <?php foreach($sub_cat as $sc){ if($sc['is_only_for_admin'] == 't' && !($is_author || $is_comm_admin)) continue;?>
                <option <?= $mess['category_id'] == $sc['id'] ? 'selected="selected"' : '';?> value="<?= $sc['id'];?>"><?= LenghtFormatEx($sc['name'],commune::MAX_CATEGORY_NAME_SIZE);?></option>
                <? } ?>
            </select>
        </div>
    </div>
    
    <?php // ��������� ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">���������</label>
        <div class="b-input_inline-block b-input_width_545">
            <div class="b-check b-check_padtop_3">
                <input id="adm_edit_ch_close_comments" class="b-check__input" type="checkbox" name="close_comments" value="1" <?=( $mess['close_comments'] == 't' ? 'checked="checked"' : '')?> />
                <label class="b-check__label" for="adm_edit_ch_close_comments" id="label_close_comments">��������� ���������������</label>
            </div>
            <div class="b-check b-check_padtop_3">
                <input id="adm_edit_ch_is_private" class="b-check__input" type="checkbox" name="is_private" value="1" <?=( $mess['is_private'] == 't' ? 'checked="checked"' : '')?> />
                <label class="b-check__label" for="adm_edit_ch_is_private" id="label_is_private">���������� ������ ���</label>
            </div>
        </div>
    </div>
</div>

<?php // ����� ?>
<div id="adm_edit_tab_div2" style="display: none;">
    <div class="b-form">
        <div id="adm_edit_attachedfiles" class="b-fon" style="width:635px"></div>
    </div>
</div>

<?php // ����� ?>
<div id="adm_edit_tab_div3" style="display: none;">
    <?php // ������ ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_question">������</label>
        <div class="b-textarea_inline-block b-textarea_width_550">
            <textarea cols="50" rows="20" id="adm_edit_question_source" style="display: none"><?=$mess['question']?></textarea>
            <textarea id="adm_edit_question" name="question" class="b-textarea__textarea_width_full b-textarea__textarea_height_70" cols="77" rows="5" onfocus="adm_edit_content.hideError('question')"></textarea>
            <div id="adm_edit_question_warn">&nbsp;</div>
        </div>

        <div id="div_adm_edit_err_question" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_question"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <?php // ��� ������ ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">��� ������</label>
        <div class="b-input_inline-block b-input_width_545">
            <div class="b-radio  b-radio_layout_horizontal">
                <div class="b-radio__item">
                    <input id="fmultiple0" class="b-radio__input" type="radio" name="multiple" value="0" <?=($mess['multiple'] != 't' ? "checked='checked'": "")?> />
                    <label class="b-radio__label" for="fmultiple0">���� ������� ������&nbsp;&nbsp;&nbsp;</label>
                </div>
                <div class="b-radio__item">
                    <input id="fmultiple1" class="b-radio__input" type="radio" name="multiple" value="1" <?=($mess['multiple'] == 't' ? "checked='checked'": "")?> />
                    <label class="b-radio__label" for="fmultiple1">��������� ��������� ������</label>
                </div>
            </div>
        </div>
    </div>
    
    <div class="b-form">
        <table border="0" cellspacing="0" cellpadding="0" width="635px;">
    <?php
    $i = 0;
    $c = count($answers);
    
    if ( $c ) {
        foreach ($answers as $answer) { ?>
            <tr valign="top" class="poll-line" id="poll-<?=$i?>">
                <td class="b-form__name_width_80">
                    <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">
                        ����� #<span class="poll-num"><?=($i + 1)?></span>
                    </label>
                </td>
                <td>
                    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tr>
                        <td><input maxlength="<?=commune::POLL_ANSWER_CHARS_MAX?>" class="poll-answer" type="text" value="<?=$answer['answer']?>" <?=($answer['id'] ? "name='answers_exists[{$answer['id']}]'" : "name='answers[]'")?> tabindex="20<?=$i?>" style="width: 99%;" onfocus="adm_edit_content.hideError('question')" /></td>
                        <td class="poll-btn" style="width: 20px; text-align: right; padding-top: 4px;"><a class="poll-del" href="javascript: return false" onclick="poll.del('Blogs', <?=($i++)?>); return false;"><img src="/images/delpoll.png" width="15" height="15"  alt="������� �����" title="������� �����" /></a></td>
                        <td class="poll-btn" style="width: 20px; text-align: right; padding-top: 4px;"><span class="poll-add">&nbsp;</span></td>
                    </tr>
                    </table>
                </td>
            </tr><?php
        }
    }
    ?>
            <tr>
                <td></td>
            </tr>
        </table>
    </div>
</div>