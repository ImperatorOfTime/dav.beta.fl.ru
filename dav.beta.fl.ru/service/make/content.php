<div class="b-promo b-layout__right b-layout__right_relative b-layout__right_width_72ps b-layout__right_float_right">
    <div class="b-menu b-menu_crumbs">
        <ul class="b-menu__list">
            <li class="b-menu__item"><a href="/service/" class="b-menu__link">��� ������ �����</a>&#160;&rarr;&#160;</li>
        </ul>
    </div>
    <h1 class="b-page__title b-page__title_padbot_17">������ ��������: ������� ��� ����������� � �������� ����������� ��� �������������</h1>
    <a name="top"></a>
    <p class="b-layout__txt b-layout__txt_padbot_40">������ ���������� ������� ���������� �������� ������� ������ ����������� 
    � ������ ����������. ������� �������� ��������? 
        ���������� ����������� ������ � ����������? ����������� ������������ 
        � ���������� �����? ������ ����������� ���������� � ����� �������� �� �������������.</p>

    <h2 class="b-promo__h2 b-promo__h2_padbot_40">��������� ���������� &mdash; <span class="b-promo__txt b-promo__txt_fontsize_22 b-promo__txt_color_fd6c30">1 FM</span></h2>

    <h2 class="b-promo__h2 b-promo__h2_padbot_14">������ ��� �������</h2>
    <ul class="b-promo__list b-promo__list_padbot_37">
        <li class="b-promo__item b-promo__item_margbot_10"><span class="b-promo__item-number b-promo__item-plus"></span>��������� ���� ���, � �� �� &mdash; ����������.</li>
        <li class="b-promo__item b-promo__item_margbot_10"><span class="b-promo__item-number b-promo__item-plus"></span>�� ���� ����������� ������� ������ � ���� �� ���� ������.</li>
        <li class="b-promo__item b-promo__item_margbot_10"><span class="b-promo__item-number b-promo__item-plus"></span>�� ��������� ������ �� ������, ������� ������ ���������.</li>
    </ul>
    <h2 class="b-promo__h2 b-promo__h2_padbot_14">��� ��� ��������</h2>
    <ul class="b-promo__list b-promo__list_padbot_37">
        <li class="b-promo__item b-promo__item_margbot_10"><span class="b-promo__item-number b-promo__item-number_1"></span><a class="b-promo__link" href="/public/offer/">��������</a> ���� ���������� � ������ ��������.</li>
        <li class="b-promo__item b-promo__item_margbot_10"><span class="b-promo__item-number b-promo__item-number_2"></span>����� ������� ���������������� ����������.</li>
        <li class="b-promo__item b-promo__item_margbot_10"><span class="b-promo__item-number b-promo__item-number_3"></span>��������� ��������!</li>
    </ul>
    <div class="b-buttons b-buttons_overflow_hidden b-buttons_padbot_40 b-promo__buttons"> <a class="b-button b-button_round_green" href="/public/offer/"> <span class="b-button__b1"> <span class="b-button__b2"><span class="b-button__txt">�������� ����������</span></span> </span> </a></div>
    <span class="b-promo__megafon"></span>
															
    <? include("../tpl.help.php"); ?>	
    
    <?if($f_offers) {
        echo '<h2 class="b-promo__h2 b-promo__h2_padbot_20">��������� ����������</h2>';
        include($_SERVER['DOCUMENT_ROOT'] . '/public/offer/tpl.offers-item.php');  
        echo '<div class="b-promo__txt"><a class="b-promo__link" href="/projects/?kind=8">��������� ����������</a></div>';
    } ?>														
</div>