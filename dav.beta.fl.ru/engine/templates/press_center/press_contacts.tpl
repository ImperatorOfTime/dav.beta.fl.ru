{{include "header.tpl"}}
<h1 class="b-page__title">�����-�����</h1>
<div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
    <h2 class="b-layout__title">���� ��������</h2>
    <div class="b-layout__txt">��� �������� �����:<br>129223, ������, �/� 33;<br>����������� ������� ������������ �����������-���������� &mdash; ��� ����ͻ.<br><br>
    <a class="b-layout__link" target="_blank" href="/promo/adv/">���������� ������� �� �����</a><br>
    <a class="b-layout__link" href="mailto:adv@fl.ru">adv@fl.ru</a> &mdash; �� �������� �������������� �� ��� � ����������� ������������<br>
    </div>                            

    <? /* if(hasPermissions('about')) { ?><div style="float:right;">[<a href="javascript:void(0);" onclick="admin.openPopup('staticPages', '<?=$$text["alias"];?>');">�������������</a>]</div><? } */ ?>
    <?php /* =$$text["n_text"]; */ ?>
</div>
<style type="text/css">
@media screen and (max-width: 960px){
.b-layout__page .b-layout__left, .b-layout__right {
    display: block;
    width: 100% !important;
}
}
@media screen and (max-width: 640px){
.b-layout__right .b-layout__txt img{ width:100%;}
}
</style>
{{include "press_center/press_menu.tpl"}}
{{include "footer.tpl"}}