    <h1 class="b-page__title">������ �����</h1>
    <table class="b-layout__table b-layout__table_margbot_20 b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_padtop_15 b-layout__td_width_140 b-layout__td_width_null_ipad">
                <img class="b-layout__pic b-layout__pic_center b-page__desktop" src="/images/promo-icons/big/11.png" alt="" width="82" height="90" />
            </td>
            <td class="b-layout__td b-layout__td_padright_20">
                <h2 class="b-layout__title"><a class="b-layout__link" href="/promo/bezopasnaya-sdelka/">���������� ������</a></h2>
                <ul class="b-promo__list">
                    <?php if($forEmp && !$guest) {?>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_1"></span>�� ��������� ������������ �� ���������������� ������������.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_2"></span>��� �������������� ������� ������ &mdash; ��� �������� ��������.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_3"></span>����������� ���������� �������� ������.</li> 
                    <?php } elseif($forFrl && !$guest) {?>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_1"></span>�� ��������� ������������ �� ���������������� ����������.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_2"></span>��� ���������� � ����� �� ������� ������������� ������ ������.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_3"></span>��� �������������� ������� ������ &mdash; ��� �������� ��������.</li> 
                    <?php } else {//else?>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_1"></span>�� ��������� ������������ �� ���������������� ���������� � ������������.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_2"></span>��� �������������� ������� ������ &mdash; ��� �������� ��������.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_3"></span>����������� ���������� �������� ������.</li>
                    <?php }//else?>
                </ul>
                <?php if ($forEmp && !$guest) { ?><div class="b-buttons b-buttons_padtop_15"> 
                    <?php /*<a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?site=new" class="b-button b-button_flat b-button_flat_green">������ ����� ������</a>*/ ?>
                </div> <?php } else { //if?><div class="b-buttons b-buttons_padtop_15"> 
                    <a href="/promo/bezopasnaya-sdelka/" class="b-button b-button_flat b-button_flat_green">���������</a>
                </div><?php } ?>
            </td>                       
            <td class="b-layout__td b-layout__td_padtop_15 b-layout__td_width_140 b-layout__td_width_null_ipad">
                <?php if($forEmp && !$guest) { ?>
                <span title="������� �������" class="b-icon b-icon__mpro b-icon__mpro_e b-page__desktop"></span>
                <?php } elseif($forFrl && !$guest) {//if?>
                <span title="������� �������" class="b-icon b-icon__mpro b-icon__mpro_f b-page__desktop"></span>
                <?php } else {//elseif?>
                <span title="������� �������" class="b-icon b-icon__mpro b-icon__mpro_fe b-page__desktop"></span>
                <?php }//else?>
            </td>
            <td class="b-layout__td">
                <h2 class="b-layout__title">
                    <?php if(!$guest) {?>
                    <a class="b-layout__link" href="<?= $forFrl ? '/payed/' : '/payed-emp/'?>">���������������� �������</a>
                    <?php }else {//if?>
                    ���������������� �������
                    <?php }//else?>
                </h2>
                <ul class="b-promo__list">
                    <?php if($forEmp && !$guest) {?>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_1"></span>�� ������ �������� ���� ������������.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_2"></span>�� ��������� ������ �� �������<br />������.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_3"></span>�� ������ ������� ������ ���������� � ����.</li>
                    <?php } elseif($forFrl && !$guest) { //elseif?>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_1"></span>�� ������ �������� ���� <br />�������������.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_2"></span>�� ������ ������������� �������� <br />�� �������.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_3"></span>�� ������ ������� 4 �������������� �������������.</li> 
                    <?php } else {//if?>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_1"></span>�� ������ �������� ���� <br />�������������.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_2"></span>�� ������ �������� ���� ������������.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_3"></span>�������������� ������ � ������ �� ������� ������.</li>
                    <?php }//else?>
                </ul>
                <div class="b-buttons b-buttons_padtop_15"> 
                    <a href="<?= $forFrl ? '/payed/' : '/payed-emp/'?>" class="b-button b-button_flat b-button_flat_green">������ PRO</a>
                </div>
            </td>
        </tr>
    </table>
    <div class=" b-promo__line b-promo__line_padbot_30  b-page__desktop"></div>

<?php if ($forEmp) { ?>
    <?php if ($guest) { ?>
    <h2 class="b-layout__title">��� �������������</h2>
    <?php }//if?>
    
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block b-promo__servis b-promo__servis_cont">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_margleft_null_iphone">
            <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="/public/?step=1&kind=7">������� ��������</a></h3>
            <div class="b-layout__txt b-layout__txt_padbot_20">�������� ������� ����� ����� ������������. ���������� �������� ���������� �������, ����� ���� ����� ������ ����������.</div>
        </div>
    </div>
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block b-promo__servis b-promo__servis_let">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_margleft_null_iphone">
            <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="/masssending/">�������� �� ��������</a></h3>
            <div class="b-layout__txt b-layout__txt_padbot_20">����������� ���������� ����� � �������� ���������� ����������� �� ��������� �������� ��������.</div>
        </div>
    </div>
	<?php if($guest) { ?>
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_relative b-layout__txt_margleft_null_iphone">
            <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="/payed-emp/">���������������� �������</a></h3>
            <div class="b-layout__txt b-layout__txt_padbot_20">����������� ������������� �������� ���� �������������, ������ �� �������������� ������, ���������� � ������ ���� PRO �������� ������������� � ������ ������.</div>
            <span class="b-page__desktop b-page__ipad"><span title="PRO" class="b-icon b-icon__spro b-icon__spro_e" style="position:absolute; left:-60px; top:10px;"></span></span>
        </div>
    </div>
	<?php }//if?>
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block b-promo__servis b-promo__servis_prj">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_margleft_null_iphone">
            <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="/public/?step=1&kind=1">���������� �������</a></h3>
            <div class="b-layout__txt b-layout__txt_padbot_20">���������� ������� � ��� ���������� ������, ��������� ���� ������������������ �������������.</div>
        </div>
    </div>
<?php }//if?>
<?php if ($forFrl) { ?>
    <?php if ($guest) { ?>
    <h2 class="b-layout__title b-layout__title_padtop_40">��� �����������</h2>
    <?php }//if?>
    
    
    <?php if($guest) { ?>
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_relative b-layout__txt_margleft_null_iphone">
            <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="/payed/">���������������� �������</a></h3>
            <div class="b-layout__txt b-layout__txt_padbot_20">�������� ���-������ ����� ���� ������������� � �����������, ���������� � ������ ���� �������� �����������, �������������� ������ �� ������� � ���������� �������� �� ������� ����������</div>
            <span class="b-page__desktop b-page__ipad"><span title="PRO" class="b-icon b-icon__spro b-icon__spro_f" style="position:absolute; left:-60px; top:10px;"></span></span>
        </div>
    </div>
    <?php }//if?>
    
    <?php
    
    //@todo: ������� �� ������ ���� ������������ ���� � ��������
    
    /*
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block b-promo__servis b-promo__servis_pl-car">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_margleft_null_iphone">
            <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="#">����� �� ���������</a></h3>
            <div class="b-layout__txt b-layout__txt_padbot_20">��������� ����� ������� ��������� �����. ���� ���������� ������ ������ �������������.</div>
        </div>
    </div>
     */
    ?>
<?php }//if ?>
    <h2 class="b-layout__title b-layout__title_padtop_40"><?= ($guest ? "����� ���������� ������" : "���������� ������")?></h2>
    
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block b-promo__servis b-promo__servis_blog">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_margleft_null_iphone">
            <? if (BLOGS_CLOSED == false) { ?>
                <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="/commune/">����������</a> � <a class="b-layout__link b-layout__link_bold" href="/blogs/">�����</a></h3>
                <div class="b-layout__txt b-layout__txt_padbot_20">���������� �������, ��������������� ��� ������� �������������, ������ ������, ������������ � ���������������� ����������.</div>
            <? } else { ?>
                <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="/commune/">����������</a></h3>
                <div class="b-layout__txt b-layout__txt_padbot_20">���������� ������, ��������������� ��� ������� �������������, ������ ������, ������������ � ���������������� ����������.</div>
            <? } ?>
        </div>
    </div>
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block b-promo__servis b-promo__servis_help">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_margleft_null_iphone">
            <h3 class="b-layout__h3"><noindex><a rel="nofollow" class="b-layout__link b-layout__link_bold" href="https://feedback.fl.ru/">������</a></noindex></h3>
            <div class="b-layout__txt b-layout__txt_padbot_20">������ �� �������, ������ � ��������������� ���������, ���������� �� ���� �������� �����.</div>
        </div>
    </div>