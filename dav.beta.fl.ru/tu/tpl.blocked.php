<?php
if(!defined('IN_STDF')) 
{ 
    header("HTTP/1.0 404 Not Found");
    exit;
}
?>
<div class="b-layout__right b-layout__right_float_right b-layout__right_width_240">
    <!-- Banner 240x400 -->
    <?= printBanner240(false); ?>
    <!-- end of Banner 240x400 -->
</div>
<div class="b-layout__left b-layout__left_margright_270">
    <h1 class="b-page__title">������� ������ ������������� ��������������</h1>
    <div class="b-layout__txt b-layout__txt_padbot_20">
        ������ ������� ������ �������� ������� ����� � ���� ������������� ��������������.<br/>
        ���� �� �������, ��� ��� ������, �������� ��� �� ����.
    </div>
    <div class="b-layout__txt b-layout__txt_padbot_20">
        ����������� �����: <a href="mailto:info@free-lance.ru">info@free-lance.ru</a>
    </div>
    <div class="b-layout__txt">
        �������!
    </div>
</div>