<h1 class="b-page__title">���������</h1>
<div class="b-menu b-menu_tabs b-menu_padbot_20">
    <ul class="b-menu__list b-menu__list_padleft_20">
        <? if(is_emp()) { ?>
        <li class="b-menu__item <?=($p=='projects'?'b-menu__item_active':'')?>"><a class="b-menu__link" href="/drafts/?p=projects" title="�������"><span class="b-menu__b1">�������</span></a></li>
        <? } ?>
        <li class="b-menu__item <?=($p=='contacts'?'b-menu__item_active':'')?>"><a class="b-menu__link" href="/drafts/?p=contacts" title="���������"><span class="b-menu__b1">���������</span></a></li>
        <? if(BLOGS_CLOSED == false) { ?><li class="b-menu__item <?=($p=='blogs'?'b-menu__item_active':'')?>"><a class="b-menu__link" href="/drafts/?p=blogs" title="�����"><span class="b-menu__b1">�����</span></a></li> <?}//if?>
        <li class="b-menu__item b-menu__item_last <?=($p=='communes'?'b-menu__item_active':'')?>"><a class="b-menu__link" href="/drafts/?p=communes" title="���� � �����������"><span class="b-menu__b1">���� � �����������</span></a></li>
    </ul>
</div>

