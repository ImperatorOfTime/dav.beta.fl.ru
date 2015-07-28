<?php
/**
 * ������ ����� ����� �������� �������������� ����������� ����������� ������
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IN_STDF') ) { 
    header("HTTP/1.0 404 Not Found"); // ��� �����
    exit();
}
?>

<input type="hidden" name="user_id" id="adm_edit_user_id" value="<?=$offer['user_id']?>">
<input type="hidden" name="login" id="adm_edit_login" value="<?=$objUser->login?>">
<input type="hidden" name="uname" id="adm_edit_uname" value="<?=$objUser->uname?>">
<input type="hidden" name="usurname" id="adm_edit_usurname" value="<?=$objUser->usurname?>">

<?=_parseHiddenParams($aParams)?>

<div class="b-menu b-menu_rubric b-menu_padbot_10">
    <ul class="b-menu__list">
        <li id="adm_edit_tab_i1" class="b-menu__item b-menu__item_active"><span class="b-menu__b1"><span class="b-menu__b2">��������</span></span></li>
        <li id="adm_edit_tab_i2" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(2); return false;">������� ��������������</a></li>
    </ul>
</div>

<div id="adm_edit_tab_div1">
    <?php // ��������� ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_name">���������</label>
        <div class="b-input b-input_inline-block b-input_width_545">
            <input type="text" id="adm_edit_name" name="name" value="<?=$offer['title']?>" class="b-input__text" size="80" onfocus="adm_edit_content.hideError('name')">
            <label class="b-check__label">�������� <?=freelancer_offers::MAX_SIZE_TITLE?> ��������.</label>
        </div>
        
        <div id="div_adm_edit_err_name" class="b-fon b-fon_bg_ff6d2d b-fon_margtop_10 b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_name"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    
    <?php // ����� ?>
    <div class="b-form b-form_padtop_10">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_msg">�����</label>
        <div class="b-textarea_inline-block b-textarea_width_550">
            <textarea id="adm_edit_msg_source" style="display:none" cols="50" rows="20"><?=input_ref($offer['descr'])?></textarea>
            <textarea id="adm_edit_msg" name="msg" onfocus="adm_edit_content.hideError('msg')" class="b-textarea__textarea_width_full b-textarea__textarea_height_70" cols="77" rows="5"></textarea>
            <label class="b-check__label">�������� <?=freelancer_offers::MAX_SIZE_DESCRIPTION?> ��������. ����� ������������ &lt;b&gt;&lt;i&gt;&lt;ul&gt;&lt;li&gt;&lt;s&gt;</label>
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
    
    <?php // ������ ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">������</label>
        <div class="b-input_inline-block b-input_width_545" id="adm_edit_professions">
            <div id="category_line">
                <select name="categories" class="b-select__select b-select__select_width_180" onchange="adm_edit_content.prjSubCategory(this);adm_edit_content.hideError('categories');">
                    <option value="0">�������� ������</option>
                <?php
                foreach ( $categories as $cat ) {
                    if ( $cat['id'] <=0 ) {
                        continue;
                    }
                    ?>
                    <option value="<?=$cat['id']?>" <?=($offer['category_id']==$cat['id'] ? ' selected' : '')?>><?=$cat['name']?></option><?php
                }
                ?>
                    </select>
                    <select name="subcategories" class="b-select__select b-select__select_width_180" onchange="adm_edit_content.hideError('categories')">
                        <option value='0' <?=($project_category['subcategory_id']==0 ? ' selected' : '')?>>��� �������������</option>
                <?php                    
                $categories_specs = $professions[$offer['category_id']];

                for ( $i=0; $i<sizeof($categories_specs); $i++ ) {
                    ?><option value="<?=$categories_specs[$i]['id']?>" <?=($categories_specs[$i]['id'] == $offer['subcategory_id'] ? ' selected' : '')?>><?=$categories_specs[$i]['profname']?></option>
                <?php
                }
                ?>
                </select>
            </div>
        </div>
        
        <div id="div_adm_edit_err_categories" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80" style="display: none">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_categories"></div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
</div>