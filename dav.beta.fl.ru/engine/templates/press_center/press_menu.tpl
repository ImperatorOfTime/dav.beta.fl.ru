<style type="text/css">
a.active_menu {
	color:#b2b2b2;
	text-decoration:none	
}

a.active_menu:hover {
	text-decoration:underline;
}
</style>
<div class="b-layout__left b-layout__left_width_25ps">
    <div class="pc-menu">
        <h3 style="margin-left:0">�����-�����</h3>
        <ul>
            <li><?=($$page == "press" && ($$action == "advAction") ? '<a href="/promo/adv/" class="active_menu">�������</a>' : '<a href="/promo/adv/">�������</a>');?></li>
            <li><?=($$page == "press" && ($$action == "contactsAction") ? '<a href="/press/contacts/" class="active_menu">��������</a>' : '<a href="/press/contacts/">��������</a>');?></li>
            <li><a href="/about/politika_po_obrabotke_pdn.pdf" target="_blank">�������� �� ��������� ������������ ������</a></li>
            <li><a href="/about/polozhenie_po_obespecheniu_bezopasnosti_pdn.pdf" target="_blank">��������� �� ����������� ������������ ������������ ������</a></li>
        </ul>
    </div>
    <div class="pc-menu">
        <h3 style="margin-left:0">� �������</h3>
        <ul>
            <li><?=($$page == "about" && ($$action == "teamAction") ? '<span>�������</span>' : '<a href="/about/team/">�������</a>');?></li>
            <li><?=($$page == "about" && ($$action == "servicesAction") ? '<span>������</span>' : '<a href="/service/">������</a>');?></li>
            <? if(0) {?> <li><?=sprintf($$page == "about" && ($$action == "faqAction") ? '<a href="%s" class="active_menu">%s</a>' : '<a href="%s">%s</a>', "/about/faq/", "������");?></li><? } ?>
            <li><?=($$page == "about" && ($$action == "rulesAction") ? '<span>������� �����</span>' : '<a href="/about/appendix_2_regulations.pdf">������� �����</a>');?></li>
            <li><?=($$page == "about" && ($$action == "offerAction") ? '<span>���������������� ����������</span>' : '<a href="/about/agreement_site.pdf">���������������� ����������</a>');?></li>
            <li><?=sprintf('<a href="%s">%s</a>', "/about/appendix_1_price.pdf", "�������� ������� �����");?></li>
            <li><?=sprintf('<a href="%s">%s</a>', "/about/appendix_4_service.pdf", "�������� ���������� �����");?></li>
            <li><?=($$page == "about" && ($$action == "tpoAction") ? '<span>���������� � ��</span>' : '<a href="/about/appendix_3_software_requirements.pdf">���������� � ��</a>');?></li>
            <li><?=($$page == "about" && ($$action == "feedbackAction") ? '<span>����� �������� �����</span>' : '<a href="/about/feedback/">����� �������� �����</a>');?></li>
        </ul>
    </div>

    <?= printBanner240(false, false, false); ?>

</div>
