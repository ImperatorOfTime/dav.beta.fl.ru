<div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right  b-layout__right_padbot_20">
    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_padbot_40">����� ���� ��������� �������� �������. ������� ���, ��� ����� ��������, � �������� ���� �����������, ����� �������������� ���.</div>
    <div class="b-layout__txt b-layout__txt_padbot_10"><a href="/wizard/registration/" class="b-layout__link">��������� � ������ ��������</a></div>
    <div class="b-post b-post_margleft_-60">
        <div class="b-post__body">
            <div class="b-post__avatar b-post__avatar_margright_10">
                <a class="b-post__link" href="javascript:void(0)"><img width="50" height="50" src="/images/no_foto.gif" alt="" class="b-post__userpic"></a>
            </div>
            <div class="b-post__content b-post__content_margleft_60">
                <div class="b-username b-username_padbot_40">
                    <div class="b-username__txt b-username__txt_padbot_15">
                        <span class="b-username__login b-username__login_bold b-username__login_color_6db335">������������&nbsp;&nbsp;</span>
                        <?php if ($project['is_pro'] == 't') { ?><?=(is_emp($project['role'])?view_pro_emp():view_pro()); }?>
                        <span class="b-username__txt b-username__txt_fontsize_11"><?= $user->getOnlineStatus4Profile()?></span>
                    </div>
                    <div class="b-username__txt b-username__txt_padbot_5 b-username__txt_fontsize_11">
                        ���������������<?= ($user->sex == 'f' ? '�' : '')?> �� ����� <?=$registered?>
                    </div>
                    <div class="b-username__txt b-username__txt_fontsize_11">
                        <?= $op_data['total']['a']?> <?= ending($op_data['total']['a'], '�����', '������', '�������')?> �� �������������: <span class="b-username__txt b-username__txt_color_6db335">+<?=$op_data['total']['p']?></span>  &#160;<span class="b-username__txt"><?= (int) $op_data['total']['n']?></span> <span class="b-username__txt b-username__txt_color_c10600">&minus;<?= (int) $op_data['total']['m']?></span>
                    </div>
                </div>			
                <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
                    <tbody>
                        <tr class="b-layout__tr">
                            <td class="b-layout__left">
                                <h3 class="b-post__title b-post__title_padbot_10"><?= reformat($project['name'], 30)?></h3>
                            </td>
                            <td class="b-layout__right b-layout__right_padleft_10">
                                <div class="b-post__price b-post__price_fontsize_15 b-post__price_bold b-post__price_center b-post__price_margtop_-26">
                                    <?php
                                    if($project['cost'] > 0) {
                                    switch ($project['budget_type']) {
                                        case 1:
                                            $budget_price_str = '������� ������';
                                            $budget_price_class = 'fl-form-p';
                                            break;
                                        case 2:
                                            $budget_price_str = '�������� ������';
                                            $budget_price_class = 'fl-form-o';
                                            break;
                                        case 3:
                                            $budget_price_str = '�������� ������';
                                            $budget_price_class = 'fl-form-lg';
                                            break;
                                        default:
                                            $budget_price_str = '';
                                            $budget_price_class = 'fl-form-grey';
                                            break;
                                    }
                                    ?>
                                    <div class="b-post__klass">��� ������������<br /><?= $budget_price_str?></div>
                                    <?php }//if?>
                                    <?php if ($project['cost'] == 0) { ?>
                                        <div class="b-post__price-inner">�� ��������������</div>
                                    <?php } else { ?>
                                        <div class="b-post__price-inner">
                                            <?$priceby_str = getPricebyProject($project['priceby']); //if?>
                                            <?= CurToChar($project['cost'], $project['currency']) ?><?= $priceby_str ?>
                                            <span class="b-post__price-nosik"></span>
                                        </div>
                                    <?php }//else ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="i-prompt">
                    <div class="b-prompt b-prompt_left_-260 b-prompt_width_200">
                        <div class="b-prompt__txt b-prompt__txt_color_fd6c30 b-prompt__txt_italic">�������� ����, ��� ����� �������</div>
                        <div class="b-prompt__arrow b-prompt__arrow_left_60 b-prompt__arrow_3"></div>
                    </div>
                </div>
                <div class="b-post__txt b-post__txt_padbot_10"><?= reformat($project['descr'], 50);?></div>
                <?php /*if ($project['attach']) {
                	$str = viewattachLeft( $project['login'], $project['attach'], "upload", $file, 1000, 600, 307200, $project['attach'], 0, 0, 1 );
                	print("<tr><td>&nbsp;</td><td><br>".$str."<br></td></tr>");
                } elseif ( isset($project_attach) && is_array($project_attach) ) {
                    ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td style="font-size:11px;padding-top:8px;vertical-align:middle;">
                            <div class="attachments attachments-p">
                    <?php
                    $nn = 1;
                	foreach ( $project_attach as $attach )
                	{
                		$str = viewattachLeft( NULL, $attach["name"], $attach['path'], $file, 0, 0, 0, 0, 0, 0, $nn );
                		echo '<div class = "flw_offer_attach">', $str, '</div>';
                        $nn++;
                	}
                	?>
                	       </div>
                        </td>
                	</tr>
                <?php } //elseif */?>
                <div class="b-menu b-menu_crumbs b-menu_padtop_30">
                    <ul class="b-menu__list">
                        <li class="b-menu__item b-menu__item_fontsize_11">������: <?= projects::getSpecsStr($project['id'],'&#160;&rarr;&#160;', ', ', 'b-menu__link');?></li>
                    </ul>
                </div>
                
                <?php if(!$is_offer && ($count_offer < $max_offers || $project['kind'] == 7)) {?>
                <div class="i-prompt">
                    <div class="b-prompt b-prompt_left_-260 b-prompt_top_35 b-prompt_width_240">
                        <div class="b-prompt__txt b-prompt__txt_color_fd6c30 b-prompt__txt_italic">���� ������ ��� �������������, �������� ��������� ���� �����������</div>
                        <div class="b-prompt__arrow b-prompt__arrow_left_70 b-prompt__arrow_3"></div>
                    </div>
                </div>
                
                <h2 class="b-layout__title b-layout__title_padtop_30">���� �����������</h2>
                
                <?php if($count_offer >= 3 && $project['pro_only'] != 't' && $project['kind'] != 7) {?>
                <div class="b-fon b-fon_width_full b-fon_padbot_20">
                    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                        <span class="b-fon__ok"></span>��� ����������� �������, ��� ����� ��������� ������ ��� �������, ��� �� �������� ������ �������� ������� ��� ������ ������� PRO.
                    </div>
                </div>	
                <?php }//if?>
                
                <?php if($project['pro_only'] == 't') {?>
                <div class="b-fon b-fon_width_full b-fon_padbot_20">
                    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                        <span class="b-fon__attent_pink"></span>������ ������ ������������ ��� ������������� � ��������� PRO, ���� ����������� ����� ��������� ������ ��� �������, ��� �� ������ ������� PRO.<br/>
                        ������������ � ��������� ��������� ����� ����������� ��������� �������� �� 3 ������� � �����. ������������ � ��������� PRO ����� �������������� ���������� �������.
                    </div>
                </div>	
                <?php } else {//if?>
                <div class="b-fon b-fon_width_full b-fon_padbot_20">
                     <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                         <span class="b-fon__attent_pink"></span>������������ � ��������� ��������� ����� ����������� ��������� �������� �� 3 ������� � �����. ������������ � ��������� PRO ����� �������������� ���������� �������.
                     </div>
                </div>	
                <?php } //else?>
                
                
                <form method="post" name="frm" id="frm">
                <input type="hidden" name="action" value="create_offer">
                <div class="b-textarea">
                    <textarea class="b-textarea__textarea b-textarea__textarea_height_120 tawl" name="answer" cols="80" rows="5" rel="1000"></textarea>
                </div>
                <? if ( $error['answer'] ) { ?>
                <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                    <span class="b-form__error"></span> <?= $error['answer']?>
                </div>
                <? } ?>
                
                <div class="i-prompt" id="left_hint_option">
                    <div class="b-prompt b-prompt_left_-260 b-prompt_top_-40 b-prompt_width_240">
                        <div class="b-prompt__txt b-prompt__txt_color_fd6c30 b-prompt__txt_italic">������� ���� � �����,<br /> � ����� �������� �������<br /> ����� �����, ����� ��������<br /> ����� �� ���������<br /> ���� ������.</div>
                        <div class="b-prompt__arrow b-prompt__arrow_left_70 b-prompt__arrow_3"></div>
                    </div>
                </div>
                <?php if($project['kind'] != 7) {?>
                <div class="b-layout__txt b-layout__txt_padtop_10 i-button">
                    <a class="b-button b-button_poll_plus b-button_margright_5" href="javascript:void(0)" onclick="view_toggle_blocks('option_content', this);"></a><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle b-layout__link_lineheight_15" href="javascript:void(0)" onclick="view_toggle_blocks('option_content', this);">������� ������ � �����</a>
                </div>
                <?php }//if?>
                <div class="b-layout__txt b-layout__txt_padtop_10 i-button">
                    <a class="b-button b-button_poll_plus b-button_margright_5" href="javascript:void(0)" onclick="view_toggle_blocks('portfolio_content', this);"></a><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle b-layout__link_lineheight_15 b-layout__link_margright_1" href="javascript:void(0)" onclick="view_toggle_blocks('portfolio_content', this);">�������� ������� �����</a> <span class="b-icon b-icon_main_fpro b-icon_valign_middle" title="PRO"></span> 
                </div>
                
                <div class="b-layout b-layout_padtop_20 b-layout_margleft_-80 b-layout_hide" id="option_content">
                    <table class="b-layout__table" border="0" cellpadding="0" cellspacing="0">
                        <tr class="b-layout__tr">
                            <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_80"><div class="b-layout__txt b-layout__txt_padtop_5">������ &#160;&#160; ��</div></td>
                            <td class="b-layout__right b-layout__right_padbot_20">
                                <div class="b-combo b-combo_inline-block">
                                    <div class="b-combo__input b-combo__input_width_70">
                                        <input  class="b-combo__input-text" name="from_budget" type="text" size="80" value="" maxlength="6" />
                                    </div>
                                </div><div 
                                class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;��&#160;</div><div
                                 class="b-combo b-combo_margright_10 b-combo_inline-block">
                                    <div class="b-combo__input b-combo__input_width_70">
                                        <input  class="b-combo__input-text" name="to_budget" type="text" size="80" value="" maxlength="6" />
                                    </div>
                                </div><div
                                 class="b-combo b-combo_inline-block b-combo_margright_20">
                                    <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_60  b-combo__input_arrow_yes b-combo__input_init_currency_data drop_down_default_0 multi_drop_down_default_column_0 green_arrow_off">
                                        <input id="currency" readonly="readonly" class="b-combo__input-text b-combo__input-text_fontsize_15" name="currency" type="text" size="80" value="���" />
                                    </div>
                                </div>
                                
                            </td>
                        </tr>
                        <tr class="b-layout__tr">
                            <td class="b-layout__left b-layout__left_width_80"><div class="b-layout__txt b-layout__txt_padtop_5">����� &#160;&#160;&#160;&#160;&#160; ��</div></td>
                            <td class="b-layout__right">
                                <div class="b-combo b-combo_inline-block">
                                    <div class="b-combo__input b-combo__input_width_70">
                                        <input  class="b-combo__input-text" name="from_time" type="text" size="80" value="" maxlength="3" />
                                    </div>
                                </div><div
                                 class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;��&#160;</div><div
                                 class="b-combo b-combo_margright_10 b-combo_inline-block">
                                    <div class="b-combo__input b-combo__input_width_70">
                                        <input  class="b-combo__input-text" name="to_time" type="text" size="80" value="" maxlength="3" />
                                    </div>
                                </div><div 
                                    class="b-combo b-combo_inline-block b-combo_margright_20 b-combo_margbot_20">
                                    <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_60  b-combo__input_arrow_yes b-combo__input_init_worktime drop_down_default_0 multi_drop_down_default_column_0 green_arrow_off">
                                        <input id="time" class="b-combo__input-text b-combo__input-text_fontsize_15" name="time" type="text" size="80" value="�����" />
                                    </div>
                                </div>                        
                            </td>
                        </tr>
                    </table>                     
                </div>
                
                <?php foreach(range(1,3) as $i=>$k) { ?>
                <input type="hidden" name="work_idfile[<?=$k?>]" value="" id="work_idfile_<?=$k?>">
                <input type="hidden" name="work_namefile[<?=$k?>]" value="" id="work_namefile_<?=$k?>">
                <?php }?>
               	</form>
                
                <div class="b-layout b-layout_padtop_20 b-layout_hide" id="portfolio_content">
                    <div class="b-fon b-fon_width_full b-fon_padbot_20">
                        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                            <span class="b-fon__ok"></span>������� ����� �������� ������ � ��� ������, ���� �� �������� ���� PRO-�������.
                        </div>
                    </div>
                    
                    <table class="b-layout__table" border="0" cellpadding="0" cellspacing="0">
                        <tr class="b-layout__tr">
                        <?php foreach(range(1,3) as $i=>$k) { ?>
                            <td id="boxWork-<?=$k?>" class="b-layout__left b-layout__left_width_240">
                                <script type="text/javascript">
                                    var boxWork<?=$k?> = new boxWork($('boxWork-<?=$k?>'), '<?=$k?>', '<?=$_SESSION['rand']?>');
                                </script>
                            </td>
                        <?php }//foreach?>
                        </tr>
                    </table>
                    <iframe style="width:1px;height:1px;visibility: hidden;" scrolling="no" id="fupload" name="fupload" src="about:blank" frameborder="0"></iframe>
                </div>   
                <?php } elseif($count_offer >= $max_offers && !$is_offer)  { ?>
                <? if ( $error ) { ?>
                <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                    <span class="b-form__error"></span> <?= $error?>
                </div>
                <? } ?>
                <h2 class="b-layout__title b-layout__title_padtop_30">� ��� �� �������� ���������� �������</h2>
                <div class="b-layout__txt">�� ������������� 3 ���������� ������, ������� ������ �� �����<?= ( $count_offer>3?" � ".($count_offer-3). " ������� ������":"" )?>. ��� ����, ����� �������� �� ���� ������, ��� ���� ������ ������� PRO.</div>
                <?php } else {//if?>
                <h2 class="b-layout__title b-layout__title_padtop_30">�� ��� �������� �� ���� ������</h2>
                <?php } //else?>
            </div>

        </div>
    </div>
    
    <div class="b-buttons b-buttons_padtop_40">
        <?php if(!$is_offer) {?>
        <a href="javascript:void(0)" onclick="$('frm').submit();" class="b-button b-button_rectangle_color_green">
            <span class="b-button__b1">
                <span class="b-button__b2 b-button__b2_padlr_15">
                    <span class="b-button__txt">����������</span>
                </span>
            </span>
        </a>&#160;&#160;
        <?php }//if?>
        <a href="/wizard/registration/" class="b-buttons__link">��������� � ������ ��������</a><span class="b-buttons__txt">,</span> 
        <a href="/wizard/registration/?action=next&complited=1" class="b-buttons__link">���������� ���� ���</a>
        <span class="b-buttons__txt">&#160;���&#160;</span>
        <a href="/wizard/registration/?action=exit" class="b-buttons__link b-buttons__link_color_c10601">����� �� �������</a>
    </div>
</div>