<script type="text/javascript">
    window.addEvent('domready', function() {
        if(window.finance) {
            finance.options.form_type = '<?= $form_type;?>';
          <?php if($error) { ?>
            finance.setErrors(<?= json_encode($error) ?>);
            finance.viewErrors();
          <?php } //if?>
        }
    });
</script>
<div class="i-shadow">
    <div class="b-shadow b-shadow_zindex_4 b-shadow_width_950" id="finance_popup" <?= $popup_open ? '' : 'style="display:none"'?>>
        <form action="" method="post" enctype="multipart/form-data" id="financeFrm" onsubmit="return checkexts()">
        <input type="hidden" name="action" value="updfin" />
        <input type="hidden" name="id" value="<?=$reqv->id?>" />
        <input type="hidden" name="form_type" id="form_type" value="<?= $form_type;?>" />
        <div class="b-shadow__right">
            <div class="b-shadow__left">
                <div class="b-shadow__top">
                    <div class="b-shadow__bottom">
                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
                            <h1 class="b-shadow__title b-shadow__title_fontsize_34 b-shadow__title_padbot_15">�������</h1>
                            <div class="b-layout__txt b-layout__txt_padbot_20">��� ����� ������� �������� ��������� � ����� �������. ��� ����������, ������� �� ������� ������, ������� �� ���� �������� �<a class="b-layout__link" href="/users/<?=$_SESSION['login']?>/setup/finance/" target="_blank">�������</a>�. ����� ��� � ������������� ��� ������ ����� �� ������.</div>
                            
                            <span class="ft<?=sbr::FT_PHYS?>_set" <?=$form_type==sbr::FT_JURI ? ' style="display:none"' : ''?>>
                                <?php 
                                sbr::view_finance_tbl(
                                    $reqvs, 
                                    sbr::FT_PHYS, 
                                    'MOBILE', 
                                    '��������� �������', 
                                    '� ����� � ������������� �������. ��������, +7, +3', 
                                    array(), 
                                    array(
                                        'theme' => 'new',
                                        'fon'   => true,
                                        'field' => 'phone',
                                        'table' => 'phone',
                                        'auth'  => ( $reqvs['is_activate_mob'] == 't' ? true : false ),
                                        'combo_css' => 'b-combo_inline-block'
                                    )
                                );
                                ?>
                            </span>
                            
                            <span class="ft<?=sbr::FT_JURI?>_set" <?=$form_type!=sbr::FT_JURI ? ' style="display:none"' : ''?>>
                                <?php 
                                sbr::view_finance_tbl(
                                    $reqvs, 
                                    sbr::FT_JURI, 
                                    'MOBILE', 
                                    '��������� �������', 
                                    '� ����� � ������������� �������. ��������, +7, +3', 
                                    array(), 
                                    array(
                                        'theme' => 'new',
                                        'fon'   => true,
                                        'field' => 'phone',
                                        'table' => 'phone',
                                        'auth'  => ( $reqvs['is_activate_mob'] == 't' ? true : false ),
                                        'combo_css' => 'b-combo_inline-block'
                                    )
                                );
                                ?>
                            </span>
                            
                            <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
                                <tbody><tr class="b-layout__tr">
                                        <td class="b-layout__left b-layout__left_width_175">
                                            <div class="b-layout__txt">������������</div>
                                        </td>
                                        <td class="b-layout__right b-layout__right_padbot_20">
                                            <div class="b-radio b-radio_layout_vertical">
                                                <div class="b-radio__item b-radio__item_padbot_10">
                                                    <input type="radio" name="rez_type" class="b-radio__input" onclick="finance.switchReqvRT(<?=sbr::RT_RU?>)" value="-1" id="_rt1" <?=(int)$rez_type <= 0 ? ' checked="checked"' : ''?><?=$rt_disabled && !$stage ? ' disabled="disabled"' : ''?> />
                                                    <label for="_rt1" class="b-radio__label b-radio__label_fontsize_13">�� �������</label>
                                                </div>
                                                <div class="b-radio__item b-radio__item_padbot_10">
                                                    <input type="radio" name="rez_type" class="b-radio__input" onclick="finance.switchReqvRT(<?=sbr::RT_RU?>)" value="<?=sbr::RT_RU?>" id="_rt2" <?=$rez_type == sbr::RT_RU ? ' checked="checked"' : ''?><?=$rt_disabled  && !$stage ? ' disabled="disabled"' : ''?> />
                                                    <label for="_rt2" class="b-radio__label b-radio__label_fontsize_13">�������� ���������� ���������</label>
                                                </div>
                                                <div class="b-radio__item b-radio__item_padbot_10">
                                                    <input type="radio" name="rez_type" class="b-radio__input" onclick="finance.switchReqvRT(<?=sbr::RT_UABYKZ?>)" value="<?=sbr::RT_UABYKZ?>" id="_rt3" class="i-radio"<?=$rez_type == sbr::RT_UABYKZ ? ' checked="checked"' : ''?><?=$rt_disabled && !$stage ? ' disabled="disabled"' : ''?> />
                                                    <label for="_rt3" class="b-radio__label b-radio__label_fontsize_13">���������� ���������� ��������� <span style="color: #999">(������ ����������� ������ �� <?=sbr::MAX_COST_USD_STR?> $)</span></label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody></table>

                            <div class="b-menu b-menu_tabs b-menu_padbot_20" id="fiz_yuri_tabs">
                                <ul class="b-menu__list b-menu__list_padleft_10">
                                    <li class="b-menu__item <?= $form_type != sbr::FT_JURI ? "b-menu__item_active" : "" ;?>" id="lnk_<?=sbr::FT_PHYS?>_set"><a href="javascript:void(0)" class="b-menu__link" onclick="finance.switchReqvFT(<?=sbr::FT_JURI?>,<?=sbr::FT_PHYS?>)"><span class="b-menu__b1">���������� ����</span></a></li>
                                    <li class="b-menu__item b-menu__item_last <?= $form_type==sbr::FT_JURI ? "b-menu__item_active" : "" ;?>" id="lnk_<?=sbr::FT_JURI?>_set"><a href="javascript:void(0)" class="b-menu__link" onclick="finance.switchReqvFT(<?=sbr::FT_PHYS?>,<?=sbr::FT_JURI?>)"><span class="b-menu__b1">����������� ���� ��� ��</span></a></li>
                                </ul>
                            </div>

                            <div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_color_c10600">����������, ��������� ������ ���� ���� �����. �������������� ���� ��������� ��������.</div>
                            
                            <span class="ft<?=sbr::FT_PHYS?>_set" <?=$form_type==sbr::FT_JURI ? ' style="display:none"' : ''?>>
                                <?php

                                sbr::view_finance_tbl(
                                    $reqvs, 
                                    sbr::FT_PHYS, 
                                    NULL, 
                                    '', 
                                    '',
                                    array(),
                                    array(
                                        'theme' => 'new',
                                        'group' => array(1,2),
                                        'subdescr' => array(1 => '����������� ������� ���� �������� ���, ��� ������� � ' . (is_emp() ? '��������������� � ���������' : '����������') . ' ����� �� �������'),
                                    )
                                ); 
                                sbr::view_finance_tbl(
                                    $reqvs, 
                                    sbr::FT_PHYS, 
                                    NULL, 
                                    '��������, �������������� ��������', 
                                    '',
                                    array(),
                                    array(
                                        'theme' => 'new',
                                        'group' => array(3,7),
                                        'abbr_block' => 'docs',
                                        'caption_expand' => true
                                        //'caption_expand' => true,
                                        //'caption_descr'  => '&mdash; ����� �� ���������'
                                    )
                                ); 

                                // ���������� ����
                                $params = array(
                                    'file_description' => '����� � ������������ ��������������� ������� ������ ���������, ��������������� ��������:<br/>�������� � ����� ����������� � ���������, ��� � ����� �����.',
                                    'button_title'  => '���������� ����',
                                    'new_interface' => true
                                );
                                sbr::view_finance_files('finance_doc', $attachedFilesDoc, $attachDoc, $params);

                                sbr::view_finance_tbl(
                                    $reqvs, 
                                    sbr::FT_PHYS, 
                                    NULL, 
                                    '���������� ����� ����������', 
                                    '',
                                    array(),
                                    array(
                                        'theme' => 'new',
                                        'group' => array(9,13),
                                        'caption_expand' => true
                                    )
                                );
                                
                                // ���������� ����
                                ob_start();
                                $params = array(
                                    'file_description' => '���� � ������������ ���������������� ������������� � ���������� �����������.',
                                    'button_title'  => '���������� ����',
                                    'new_interface' => true,
                                    'css_class'     => '  b-file_padtop_5'
                                );
                                sbr::view_finance_files('finance_other', $attachedFilesOther, $attachOther, $params);
                                $file_html = ob_get_clean();
                                
                                sbr::view_finance_tbl(
                                    $reqvs, 
                                    sbr::FT_PHYS, 
                                    NULL, 
                                    '������ ���������', 
                                    '',
                                    array(),
                                    array(
                                        'theme' => 'new',
                                        'group' => array(15,16),
                                        'caption_expand' => true,
                                        'file'  => array(16 => $file_html)
                                    )
                                );
                                
                                sbr::view_finance_tbl(
                                    $reqvs, 
                                    sbr::FT_PHYS, 
                                    NULL, 
                                    '���������� ����������', 
                                    '',
                                    array(),
                                    array(
                                        'theme' => 'new',
                                        'group' => array(17,17)
                                    )
                                );

                                //sbr::view_finance_files();

                                ?>

                                <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_color_c10600">������� ���� �� ���� ������ ������ �����, � ������� �������� �� ���������� �������� ������.</div>

                                <?php

                                sbr::view_finance_tbl(
                                    $reqvs, 
                                    sbr::FT_PHYS, 
                                    'BANK', 
                                    '���������� ���������', 
                                    '',
                                    array(),
                                    array(
                                        'theme' => 'new',
                                        'subdescr' => array(
                                            20 => ( $rez_type == sbr::RT_UABYKZ ? '�������� 20 ��������. �������� ��������: �/� ���������� �� 30111810' : '' ),
                                            29 => '�������� � ������ ����� � ������������� ����� ����',
                                            32 => '���������, ������ ���� ��� ������ ����������� ��� �������� ������� � ����� �����.', 33 => '���������, ������ ���� ��� ������ ����������� ��� �������� ������� � ����� �����.'),
                                        'caption_expand' => true
                                    )
                                );

                                sbr::view_finance_tbl (
                                    $reqvs, 
                                    sbr::FT_PHYS, 
                                    'EL', 
                                    '����������� ��������', 
                                    '',
                                    array('pos' => 3, 'title' => '��� ���������� ������ Webmoney ����������� ���������� ������'),
                                    array(
                                        'theme'      => 'new',
                                        'caption_expand' => true
                                        //'name_descr' => array(31 => 'R')
                                    )
                                ); 

                                ?> 

                                <div class="b-layout__txt b-layout__txt_padleft_180 b-layout__txt_color_c10600">
                                    ���� �� ���������� �������� �� ����� ��� ����������� ����, �� �������� ��������� ����� ��� <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="finance.switchReqvFT(<?=sbr::FT_PHYS?>,<?=sbr::FT_JURI?>)">������������ ����</a> (��� �������� �� ������ ������ ����������� ���� �� ����������).
                                </div>
                            </span>
                            
                            <span class="ft<?=sbr::FT_JURI?>_set" <?=$form_type!=sbr::FT_JURI ? ' style="display:none"' : ''?>>
                                <?
                                sbr::view_finance_tbl(
                                    $reqvs, 
                                    sbr::FT_JURI, 
                                    NULL, 
                                    '', 
                                    '',
                                    array(),
                                    array(
                                        'theme' => 'new',
                                        'group' => array(1,1)
                                    )
                                ); 

                                sbr::view_finance_tbl(
                                    $reqvs, 
                                    sbr::FT_JURI, 
                                    NULL, 
                                    '����������� �����������', 
                                    '',
                                    array(),
                                    array(
                                        'theme' => 'new',
                                        'group' => array(2,9),
                                        'caption_expand' => true
                                    )
                                ); 

                                sbr::view_finance_tbl(
                                    $reqvs, 
                                    sbr::FT_JURI, 
                                    NULL, 
                                    '��������� �����������', 
                                    '',
                                    array(),
                                    array(
                                        'theme' => 'new',
                                        'group' => array(10,17),
                                        'subdescr' => array(
                                            12 => '���������, ������ ���� ��� ������ ����������� ��� �������� ������� � ����� �����.',
                                            17 => '���������, ������ ���� ��� ������ ����������� ��� �������� ������� � ����� �����.'
                                        ),
                                        'caption_expand' => true

                                    )
                                ); 
                                ?>

                                <?php
                                sbr::view_finance_tbl(
                                    $reqvs, 
                                    sbr::FT_JURI, 
                                    'BANK', 
                                    '���������� ���������', 
                                    '',
                                    array(),
                                    array(
                                        'theme' => 'new',
                                        'caption_expand' => true,
                                        'subdescr' => array(
                                            20 => ( $rez_type == sbr::RT_UABYKZ ? '�������� 20 ��������. �������� ��������: �/� ���������� �� 30111810' : '' ),
                                            28 => '��� � ���������� ����������������� ���, ������� �� 9 ����. �������� � ������ ����� � ������������� ����� ����',
                                            29 => '�������� � ������ ����� � ������������� ����� ����'
                                        )
                                    )
                                ); 
                                ?>
                                <?php
                                sbr::view_finance_tbl(
                                    $reqvs, 
                                    sbr::FT_JURI, 
                                    NULL, 
                                    '���������� ����������', 
                                    '',
                                    array(),
                                    array(
                                        'theme' => 'new',
                                        'group' => array(30,33),
                                        'subdescr' => array(
                                            32 => '�������, ���� ����'
                                        ),
                                        'caption_expand' => true
                                    )
                                ); 
                                ?>
                                <div class="b-layout__txt b-layout__txt_padleft_180 b-layout__txt_color_c10600">
                                    ���� �� ���������� �������� �� ����� ��� ���������� ����, �� �������� ��������� ����� ��� <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="finance.switchReqvFT(<?=sbr::FT_JURI?>,<?=sbr::FT_PHYS?>)">����������� ����</a> (��� �������� �� ������ ������ ������������ ���� �� ����������).
                                </div>
                            </span>
                            
                            <div class="b-buttons b-buttons_padtop_40 b-buttons_padbot_20 b-buttons_padleft_180">
                                <a class="b-button b-button_flat b-button_flat_green finance-save"  href="javascript:void(0)">���������</a>
                                <span class="b-buttons__txt b-buttons__txt_padleft_10">���</span>
                                <a class="b-buttons__link b-buttons__link_dot_c10601 finance-close" href="javascript:void(0)">�������, �� ��������</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-shadow__tl"></div>
        <div class="b-shadow__tr"></div>
        <div class="b-shadow__bl"></div>
        <div class="b-shadow__br"></div>
        <span class="b-shadow__icon b-shadow__icon_close1 finance-close"></span>
        </form>
    </div>
</div>
<div class="b-shadow b-shadow_zindex_11 b-shadow_center b-shadow_width_450" id="auth_popup" style="display:none"></div>