<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';

if ((!$s_login && $_GET['rnd'] && $_SESSION['rand'] == $_GET['rnd']) || hasPermissions('users')) {
    $s_login = __paramInit('string', 'login');
}

$user = new users();
if ($s_login) {
    $user->GetUser($s_login, null, true);
    $uid = $user->uid;
} else {
    $uid = $GLOBALS['already_banned'];
}

if ($uid && ($ban = $user->GetBan($uid))) {
    
?>
<div class="b-layout__right b-layout__right_float_right b-layout__right_width_240">
    <!-- Banner 240x400 -->
    <?= printBanner240(false); ?>
	<!-- end of Banner 240x400 -->
</div>
<div class="b-layout__left b-layout__left_margright_270">
<? if ($ban['reason'] == 4) {?>
<h1 class="b-page__title">������� ��� ������</h1>
<? } else {?>
<h1 class="b-page__title">������� ������������ <?=($ban["to"] ? "�� ".date("d.m.Y  H:i",strtotimeEx($ban["to"])) : "")?></h1>
<div class="b-layout__txt b-layout__txt_padbot_20">������� ����������: <? $data = admin_log::getAdminReason($ban["reason"]); print strlen($data['reason_name']) > 0?$data['reason_name']:$ban["comment"]; }?></div>
<? } ?>
<div class="b-layout__txt b-layout__txt_padbot_20">���� �� ������ ���������� ������ �� ����� � ������������ ������� &mdash; ���������� � <a href="http://feedback.fl.ru" target="_blank">������ ���������</a></div>
</div>
