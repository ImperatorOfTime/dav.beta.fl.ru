<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/users.common.php");
$xajax->printJavascript('/xajax/');

require_once($_SERVER['DOCUMENT_ROOT'].'/classes/sms_services.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');

$u = new users;
$o_only_phone = $u->GetField($uid,$ee,'safety_only_phone');
$bind_ip_current = $bind_ip;
if($_POST['action']!='safety_update') {
    $phone = $u->GetField($uid,$ee,'safety_phone');
    $only_phone = $u->GetField($uid,$ee,'safety_only_phone');
    $bind_ip_current = $bind_ip = $u->GetField($uid,$ee,'safety_bind_ip');
    $array_ip_addresses = $u->GetSafetyIP($uid);
    while(list($k,$v)=each($array_ip_addresses)) {
        $ip_addresses .= $v."\r\n";
    }
} else if ( $error_flag ) {
    $bind_ip_current = $u->GetField($uid,$ee,'safety_bind_ip');
}
$reqv = sbr_meta::getUserReqvs($uid);
$ureqv = $reqv[$reqv['form_type']];
if($_SESSION['alert']) {
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']);
}
if($_SESSION['info_msg']) {
    $info_msg = $_SESSION['info_msg'];
    unset($_SESSION['info_msg']);
}
?>

<div class="b-layout b-layout_padtop_20">
	<h2 class="b-layout__title b-layout__title_padbot_30">������������ ��������</h2>
	<? if ($info_msg) print(view_info($info_msg)."<br />") ?>
    
    <h3 class="b-layout__h3">����������� �������������� ����� �������</h3>
    <?php if ($social_multivel && is_array($social_multivel)): ?>
        <div class="b-layout__txt b-layout__txt_padbot_40">
            �������� ����������� �������������� � ������� <?=$social_multivel['name']?>. 
            <a id="multilevel_switchoff" href="javascript:void(0);">���������</a>.
        </div>
    <?php else: ?>
        <div class="b-layout__txt b-layout__txt_padbot_15">
            ��������� ����������� ��������������, � ����� ����� ������ � ������ ��� 
            ����� ����� ����� �������������� ����� ��������� ������� �� ������ ����. 
            <a href="http://feedback.fl.ru/topic/683170-dvuhetapnaya-autentifikatsiya-cherez-sotsseti/" target="_blank">���������</a>
        </div>
        <?php if (isset($social_links) && !empty($social_links)): ?>
            <form action="." method="post" class="b-form b-form_padbot_40">
                <input type="hidden" name="action" value="safety_social" />
                <table class="b-layout__table">
                    <tr class="b-layout__tr">
                        <td class="b-layout__td b-layout__td_width_140">
                            <div class="b-layout__txt b-layout__txt_padtop_5">�������� �������:</div>
                        </td>

                        <td class="b-layout__td b-layout__td_padbot_20">
                            <?php foreach (array(1 => 'facebook', 2 => 'vk', 3 => 'odnoklassniki') as $key => $code): ?>
                                <?php if (isset($social_links[$key])): ?>
                                    <div class="b-radio b-radio_inline-block b-radio__item_padright_20">
                                        <input class="b-radio__input b-radio__input_top_5" 
                                               type="radio" 
                                               id="provider_<?=$key?>" 
                                               name="type" 
                                               value="<?=$key?>"
                                               <?=isset($provider_type) && $provider_type == $key ? ' checked="checked"' : ''?> />
                                        <label class="b-radio__label" for="provider_<?=$key?>">
                                            <span class="b-auth_btn b-auth_mini b-auth_btn_<?=$code?> g-float_left g-margtop_0"></span>
                                        </label>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if ($alert[4]): ?>
                                <?=view_error($alert[4])?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="b-layout__tr">
                        <td class="b-layout__td b-layout__td_width_140">
                            <div class="b-layout__txt b-layout__txt_padtop_5">������� ������:</div>
                        </td>
                        <td class="b-layout__td b-layout__td_padbot_20">
                            <div class="b-combo">
                                <div class="b-combo__input b-combo__input_width_300">
                                    <input class="b-combo__input-text"  type="password" name="oldpwd" />
                                    <?php if ($alert[5]): ?>
                                        <?=view_error($alert[5])?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
                <button class="b-button b-button_flat b-button_flat_green" type="submit">�������� ����������� ��������������</button>
            </form>
        <?php else: ?>
            <div class="b-layout__txt b-layout__txt_padbot_15">��������� ��� ������� �� �������, ������� �� ������ ������������ ��� ����������� ��������������.</div>
            <div class="b-layout b-layout_padbot_30">
                <?php if (isset($social_bind_error) && !empty($social_bind_error)): ?>
                    <div class="b-layout__txt b-layout__txt_color_c4271f">
                        <?=$social_bind_error?>
                    </div>
                <?php endif; ?>
                <div class="b-layout__txt">
                    <a href="/auth/?param=vkontakte&multilevel=1"
                       class="b-layout__link b-layout__link_lineheight_34 b-layout__link_valign_top">
                        <span class="b-auth_btn b-auth_mini b-auth_btn_vk b-auth_margright_5 b-auth_btn_float_left"></span>��������� VKontakte-������� � �������
                    </a>
                </div>
                <div class="b-layout__txt">
                    <a href="/auth/?param=facebook&multilevel=1"
                       class="b-layout__link b-layout__link_lineheight_34 b-layout__link_valign_top">
                        <span class="b-auth_btn b-auth_mini b-auth_btn_facebook b-auth_margright_5 b-auth_btn_float_left"></span>��������� Facebook-������� � �������
                    </a>
                </div>
                <div class="b-layout__txt">
                    <a href="/auth/?param=odnoklassniki&multilevel=1"
                       class="b-layout__link b-layout__link_lineheight_34 b-layout__link_valign_top">
                        <span class="b-auth_btn b-auth_mini b-auth_btn_odnoklassniki b-auth_margright_5 b-auth_btn_float_left"></span>��������� Odnoklassniki-������� � �������
                    </a>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
	<a name="safety_ip"></a>
	<h3 class="b-layout__h3">�������� �������� � IP-������</h3>
	<form action='.' method='POST' id="safetyform">
		<div class="b-form">
			<label for="ip_addresses" class="b-form__name b-form__name_fontsize_13 b-form__name_padbot_10">������� IP-������, ��� ������� ����� �������� ���� � �������. ���� � ������ IP-������� ����� ����������.</label>
			<div class="b-textarea">
				<textarea id="ip_addresses" class="b-textarea__textarea b-textarea__textarea_width_750" cols="20" rows="5" name="ip_addresses"><?=$ip_addresses?></textarea>
			</div>
			<input type="hidden" name="action" value="safety_update" />			
			<div class="b-form__txt b-form__txt_fontsize_11 b-form__txt_padtop_3 b-form__txt_block b-form__txt_width_full">IP-������ ������� ��������� ����� �������, ��� ����� ��������� ������� ����������� ����� ��� ����.<br/>� �������,  10.10.10.1, 10.10.10.5-10.10.10.10 ��� 10.10.10.0/24</div>
		</div><?php if ($alert[1]) { ?>
				<?=view_error($alert[1]);?>
			<?php } ?>
		<div class="b-fon b-fon_width_full b-fon_padbot_30">
			<div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb b-fon__body_bordbot_edddda">
				�������� ��������: �� ����������� �������� � IP, ���� � ��� ������������ IP-����� (���������� ����� �������� � ������ ����������).
			</div>
		</div>
		<h3 class="b-layout__h3">�������� ����������� �� ����� � IP-������ <? if ($bind_ip_current=='t') { ?><span class="b-layout__txt b-layout__txt_color_6bb336 b-layout__txt_fontsize_15">��������</span><? } else { ?><span class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_fontsize_15">���������</span><? } ?></h3>
		<div class="b-layout__txt b-layout__txt_padbot_15">����������� ��� ������� � ��������� ������ �������� &laquo;��������� ����&raquo;, �� ������ ������������ �� ����� �� ��� ���, ���� �� ��������� ��� IP-�����. ������� ������� ��� ���, ��� ����������� � ������������ ������ ��������: �������� ����������� � IP �� �������� �������������� �������������� � ����� �������� � ������� IP-������, ����������� ������, ���������� �� ������ �������� (����� cookies).</div>
		<div class="b-check">
			<input id="bind_ip" class="b-check__input" type="checkbox" name="bind_ip" value="t" <?=($bind_ip=='t'?' checked="checked"':'')?> /> <label class="b-check__label b-check__label_fontsize_13" for="bind_ip">�������� ��������</label>
		</div>
		<div class="b-fon b-fon_width_full b-fon_padbot_30 b-fon_padtop_10">
			<div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb b-fon__body_bordbot_edddda">
				�������� ��������: ������� ����� ��������� ������������ ���������� ����������� ������������� IP &mdash; � ����� �� ������ IP-������, ������� ���������� ����� ��������� ���������� �������, ��� ����� ���������� �������� ������� ����� � ������ �� ��������.
			</div>
		</div>
        <? /* #0019359
		<h3 class="b-layout__h3">�������� � ��������</h3>
		<div class="b-layout__txt b-layout__txt_padbot_5">� ������ ����� ����� ������ ������ �� ������� ������������ ������ � �������� ����������� ���������� �������� &mdash; ������ ������ � ���� SMS-��������� �� ��������� ���� ����� ��������. <br /><br />��� �����:</div>
		<div class="b-form b-form_width_490">
			<div class="b-input b-input_width_160 b-input_inline-block">
				<input id="phone" class="b-input__text" type="text" autocomplete="off"  name="phone" value="<?=$phone?>" maxlength="30" <?=(($o_only_phone=='t')?'disabled="disabled"':'')?> onKeyPress="return submitEnter(this,event)"  /> 
			</div>
			<div class="b-check b-check_inline-block b-check_padleft_10 b-check_padtop_3">
				<input id="only_phone" class="b-check__input" type="checkbox" name="only_phone" value="t" <?=($only_phone=='t'?' checked="checked"':'')?> <?=(($o_only_phone=='t')?'disabled="disabled"':'')?> /> <label class="b-check__label b-check__label_fontsize_13" for="only_phone">��������������� ������ ������ �� �������</label>
			</div>
			<div class="b-form__txt b-form__txt_fontsize_11 b-form__txt_padtop_3 b-form__txt_width_full">��������, +79266543210</div>
			
		</div>
		<?php if ($alert[2]) { ?>
				<?=view_error($alert[2])?>
			<?php } ?>
		<div class="b-fon b-fon_width_full b-fon_padbot_10 b-fon_padtop_10">
			<div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb b-fon__body_bordbot_edddda">
				�������� ��������: ���� �� �������� ����� �������������� ������ ������ �� �������, ������������ �������� ��������� ���������� ������ �������� ����� ��������� �<a class="b-layout__link" href="/help/?all" target="_blank">������ ���������</a>.
			</div>
		</div>
		<h4 class="b-layout__h4"><a class="b-layout__link b-layout__toggler b-layout__link_bordbot_dot_0f71c8" href="#">������ �������������� ������ ����� SMS � ��������� ������</a></h4>
		<div class="b-layout__slider">
			<div class="b-layout__txt b-layout__txt_padbot_10">��� �������������� ������ ��� ���������� ��������� SMS � ������� <span class="b-layout__txt b-layout__txt_color_6bb336">free 2+<?=htmlspecialchars($_SESSION['login'])?></span> �� ����� <span class="b-layout__txt b-layout__txt_color_6bb336">4446</span>.<br/>��������� ���������� �� �������������� ������ ����� SMS-��������� ��������� � ��������������� <a class="b-layout__link" href="/help/?q=882">������� ������</a>.</div>
			<div class="b-layout__txt b-layout__txt_padbot_10">������ �������� ��� ������� ������, ������� � ����������.</div> 
    		<div class="b-layout__txt b-layout__txt_padbot_10"><?=sms_services::$tariffs['4446']['descr']?></div>
		</div>
        
        <h3 class="b-layout__h3 b-layout__h3_padtop_30">�������� ���������� �������� <span class="b-layout__txt <?= ( $reqv['is_activate_mob'] == 't' ? 'b-layout__txt_color_6bb336' : 'b-layout__txt_color_c10600' );?> b-layout__txt_fontsize_15" id="safety_status"><?= ( $reqv['is_activate_mob'] == 't' ? '��������' : '���������' );?></span></h3>
        <div class="b-combo b-combo_inline-block b-combo_valign_mid">
            <div class="b-combo__input b-combo__input_width_170 <?= ( ($reqv['is_activate_mob'] == 't' || $_SESSION['is_verify'] == 't') ? 'b-combo__input_disabled' : '');?>" id="safety_mob_phone">
                <input class="b-combo__input-text b-combo__input-text_fontsize_18" <?= ( $reqv['is_activate_mob'] == 't' ? 'disabled' : '');?> name="mob_phone" type="text" size="12" maxlength="15" value="<?= $ureqv['mob_phone'];?>" onChange="savePhoneChage(this);" onBlur="savePhoneChage(this);"/>
            </div>
        </div>
        <span class="c_sms_main">
            <?php if($reqv['is_activate_mob'] == 't') { ?>
            &#160;&#160;
            <div class="b-layout__txt b-layout__txt_inline-block">
                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)">��������</a>
            </div>
            <script>bindLinkUnativateAuth('<?= $_SESSION['uid'];?>');</script>
            <?php } else {//if?>
            <a href="javascript:void(0)" class="b-button b-button_rectangle_color_transparent b-button_margtop_-2" data-send="safety" data-phone="<?= $ureqv['mob_phone'];?>">
                <span class="b-button__b1">
                    <span class="b-button__b2">
                        <span class="b-button__txt">������������</span>
                    </span>
                </span>
            </a>
            <?php }//else?>
        </span>
        <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11">�������� +79201234567</div>
        <div class="b-check b-check_padbot_10 safety_phone_checks">
            <input class="b-check__input" name="only_phone" value="t" type="checkbox" <?= ( $o_only_phone == 't' ? 'checked="checked"' : '' );?> <?= ( $reqv['is_activate_mob'] == 't' ? '' : 'disabled');?>/> <label class="b-check__label b-check__label_fontsize_13" >��������������� ������ ������ �� ��������� �������</label>
        </div>
        <div class="b-check safety_phone_check safety_phone_checks">
            <input class="b-check__input" id="finance_safety_phone" name="finance_safety_phone" value="t" type="checkbox" <?= ( $reqv['is_safety_mob'] == 't' ? 'checked="checked"' : '' );?> <?= ( $reqv['is_activate_mob'] == 't' ? '' : 'disabled');?>/> <label class="b-check__label b-check__label_fontsize_13" >���� �� �������� &laquo;�������&raquo; &mdash; ������ ����� ��� �� ���-���������</label>
        </div>*/ ?>
 
        <a name="safety_password"></a>
		<h3 class="b-layout__h3 b-layout__h3_padtop_30">������������� ���������</h3>
		<div class="b-form b-form_padbot_40 b-form_width_full">
			<label class="b-form__name b-form__name_fontsize_13 b-form__name_padbot_10">��� ���������� ��������� ��������� ������� ��� ������� ������:</label>
			<div class="b-input b-input_width_160">
				<input id="password" class="b-input__text b-input__text_width_160" type="password" name="password" onKeyPress="return submitEnter(this,event)" />
			</div>
			<?php if ($alert[3]) { ?>
                    <?=view_error($alert[3])?>
            <?php } ?>
		</div>
		<a class="b-button b-button_flat b-button_flat_green" onclick="safetyForm('f');" href="javascript:void(0)">��������� ���������</a>
</form>	
</div>
<div class="b-shadow b-shadow_zindex_11 b-shadow_center b-shadow_width_450" id="auth_popup" style="display:none"></div>
<?php
if($alert[1] || $alert[3]) { 
	?>
	<script type="text/javascript">
	<?php
	if($alert[1]) {
		?>
		window.location = '#safety_ip';
		<?php
	} elseif ($alert[3]) {
		?>
		window.location = '#safety_password';
		<?php
	}
	?>
	</script>
	<?php
} 
?>
