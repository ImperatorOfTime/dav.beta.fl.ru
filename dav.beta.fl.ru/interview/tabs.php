<div class="b-menu b-menu_tabs">
    <ul class="b-menu__list b-menu__list_overflow_hidden b-menu__list_padleft_20">
        <li class="b-menu__item">
						<a class="b-menu__link" href="/articles/" title="������">
								<span class="b-menu__b1">������</span>
						</a>
				</li>
        <? if($articles_unpub) { ?>
        <li class="b-menu__item">
						<a class="b-menu__link" href="/articles/?page=unpublished" title="�� ���������">
								<span class="b-menu__b1">�� ���������</span>
						</a>
				</li>
        <? } ?>
        <li class="b-menu__item b-menu__item_last b-menu__item_active"><a class="b-menu__link" href="/interview/" title="��������"><span class="b-menu__b1">��������</span></a></li>
    </ul>
</div>
