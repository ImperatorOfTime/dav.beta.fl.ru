<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
 require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/rating.common.php");
$xajax->printJavascript('/xajax/');

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");

if (!$rating || !($rating instanceof rating) || $rating->data['user_id'] != $user->uid)
    $rating = new rating($user->uid, $user->is_pro, $user->is_verify, $user->is_profi);

$r_data = $rating->data;
$r_data['kis'] = projects_offers::GetFrlOffersSummary($r_data['user_id']);
$r_data['kis']['refused_3'] = (int) $r_data['kis']['refused'] - (int) $r_data['kis']['refused_1'] - (int) $r_data['kis']['refused_0'] - (int) $r_data['kis']['refused_2'] - (int) $r_data['kis']['refused_4'];
if(!$r_data['max']) {
    $r_data['max'] = $rating->get_max_of('total', false);
}

$sbr_ratings = sbr_meta::getUserRatings($user->uid, is_emp($user->role), 5, 0, $sbr_info['success_cnt']);
//$sbr_info['success_cnt'] = sbr_meta::getCountSuccessRatingSbr($user->uid, is_emp($user->role));
if (!($prjs = projects_offers::GetFrlOffers($r_data['user_id'], 'marked', NULL)))
    $prjs = array();

$kis_per_refused = round($r_data['kis']['total'] ? 100 * $r_data['kis']['refused'] / $r_data['kis']['total'] : 0, 2);
$kis_per_frl_refused = round($r_data['kis']['total'] ? 100 * $r_data['kis']['frl_refused'] / $r_data['kis']['total'] : 0, 2);
$kis_per_selected = round($r_data['kis']['total'] ? 100 * $r_data['kis']['selected'] / $r_data['kis']['total'] : 0, 2);
$kis_per_executor = round($r_data['kis']['total'] ? 100 * $r_data['kis']['executor'] / $r_data['kis']['total'] : 0, 2);
$kis_unknown = (int) $r_data['kis']['total'] - ((int) $r_data['kis']['refused'] + (int) $r_data['kis']['selected'] + (int) $r_data['kis']['executor']) - (int) $r_data['kis']['frl_refused'];
$kis_per_unknown = 100 - ($kis_per_refused + $kis_per_selected + $kis_per_executor + $kis_per_frl_refused);

$o_contest_rating = round($r_data['o_contest_1'] + $r_data['o_contest_2'] + $r_data['o_contest_3']);
$o_contest_ban_rating = round($r_data['o_contest_ban']);
$is_owner = ($user->uid == $_SESSION['uid']);
?>

<style type="text/css">
  .rating .big-s {font-size:17px}
  .rating .lgray-c {color:#b2b2b2}
  .rating .table td {padding:15px 0 15px 0}
  .ac, tr.ac td   {text-align:center}
  .bt, tr.bt td   {border-top:1px solid}
  .br, tr.br td   {border-right:1px solid}
  .bb, tr.bb td   {border-bottom:1px solid}
  .bl, tr.bl td   {border-left:1px solid}
  .ba, tr.ba td   {border:1px solid}
  .gray-bc,  tr.gray-bc  td {border-color: #c6c6c6}
</style>
<div class="rating">
  <table width="100%" cellspacing="0" cellpadding="0">
    <tr>
      <td>
          
<? if ($user->uid == get_uid(false)) { ?>
    <script>
        window.addEvent('domready', function() {
            
            xajax_GetRating('month', '<?= $user->login ?>', <?= !is_pro() ? '600' : 'null' ?>);
            document.getElement('select[name=ratingmode]').addEvent('change', function() {
                xajax_GetRating(this.get('value'), '<?= $user->login ?>', <?= !is_pro() ? '600' : 'null' ?>);
            });
            
        });
    </script>
<? } ?>
            
<div class="rate-page">
    
    <div class="month-rate-graph">
        
        
<!-- ������� � ���������� -->
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_15 b-layout__table_margbot_40">
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__td_width_240 b-layout__one_padbot_20">
        			<p class="b-layout__txt_fontsize_20"><?= ( !$is_owner ? "����� �������" : "��� �������")?></p></td>
        		<td colspan="2" class="b-layout__one_padbot_20">
        			<p class="b-layout__txt_float_right b-layout__mail-icon_top_4"><noindex><a rel="nofollow" href="https://feedback.fl.ru/topic/397654-opisanie-sistemyi-rejtinga-frilanser/" class="b-layout__link" target="_blank">��������� � ��������</a></noindex></p>
        			<p class="b-layout__txt_float_left b-layout__txt_fontsize_20 b-text__bold"><?= rating::round($r_data['total']) ?></p>
        			<p class="b-layout_clear_both b-layout__title_color_4e"><?= rating::round($r_data['max']) ?> ������������</p>
        		</td>
        	</tr>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">������������� �������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <? if(get_uid(false)!=$user->uid && !hasPermissions('users')) { ?>
                    �������� ����������
                    <? } else { $feature_inf_factor = 100 - rating::round($r_data['o_inf_factor']); ?>
                    <?= rating::round($r_data['o_inf_factor']) ?>
                    <? } ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    <a href="http://feedback.fl.ru/topic/397551-zakladka-informatsiya-opisanie-razdelov-instruktsiya-po-zapolneniyu-frilanser-i-rabotodatel/" class="b-layout__link">��������� � ���������� �������</a>
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">������ �� �������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= rating::round($r_data['o_kis_factor']) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    1 ���� �� ���������� � ��������� ��� �����������
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">���������� ������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= rating::round($r_data['o_sbr_factor']) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle">
                </td>
        	</tr>
            <?php if($r_data['o_opi_factor'] > 0) { ?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">������ �������������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= rating::round($r_data['o_opi_factor']) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    +1/-1 ���� �� �������������/������������� ������
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
            <?php } ?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">��������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= $o_contest_rating ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    30 ������ �� &Iota; �����, 20 ������ �� &Iota;&Iota; �����, 10 ������ �� &Iota;&Iota;&Iota; 3 �����
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">��������� ����� fl.ru</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <? if(get_uid(false)!=$user->uid && !hasPermissions('users')) { ?>
                    �������� ����������
                    <? } else { ?>
                    <?= rating::round($r_data['o_act_factor']) ?>
                    <? } ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) {?>
                    1 ���� � ����
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">������, ����������� �� ������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <? if(get_uid(false)!=$user->uid && !hasPermissions('users')) { ?>
                    �������� ����������
                    <? } else { ?>
                    <?= rating::round($r_data['o_mny_factor']) ?>
                    <? } ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    1 ���� �� 30 ������
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
            <?php if($r_data['o_articles_factor'] > 0) { ?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= rating::round($r_data['o_articles_factor'])?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    30 ������ �� ���������� � ������� ������
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
            <?php } ?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">����������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= rating::round($r_data['o_commune_entered'])?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    50 ������ �� ���������� &gt; 500 ���������� � ���� ����������
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">������ � ���������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <? if(get_uid(false)!=$user->uid && !hasPermissions('users')) { ?>
                    �������� ����������
                    <? } else { $feature_portf = 500 - rating::round($r_data['o_wrk_factor']); ?>
                    <?= rating::round($r_data['o_wrk_factor']) ?>
                    <? } ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">&nbsp;</td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">������ �������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= rating::round($r_data['o_oth_factor']) + $o_contest_ban_rating; ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">&nbsp;</td>
        	</tr>
            <?php if($user->isProfi()){ ?>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">
                    ������� &nbsp;<span class="b-icon b-icon__lprofi b-icon_top_2" title="PROFI"></span> x 1.4
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= abs(rating::round(abs(($r_data['f_total']*rating::PROFI_FACTOR)) - abs($r_data['f_total']))) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">&nbsp;</td>
        	</tr>            
            <?php } elseif ($user->is_pro=='t' || $user->is_pro_test=='t') { ?>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">
                    ������� &nbsp;<span class="b-icon b-icon__pro b-icon__pro_f b-icon_top_4" title="PRO"></span> x 1.2
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= abs(rating::round(abs(($r_data['f_total']*rating::PRO_FACTOR)) - abs($r_data['f_total']))) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">&nbsp;</td>
        	</tr>
            <? } ?>
            <? if ($user->is_verify=='t') { ?>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">
                    ����������� &nbsp;<span class="b-icon b-icon__ver b-icon_top_2"></span> x 1.2
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= abs(rating::round(abs(($r_data['f_total']*rating::VERIFY_FACTOR)) - abs($r_data['f_total']))) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">&nbsp;</td>
        	</tr>
            <? } ?>
        </table>
        <?php
        if($is_owner) {
            $feature_total = rating::round($r_data['total']);
            if($feature_portf != 0) {
                $feature_total += $feature_portf;
            }
            if($feature_inf_factor != 0) {
                $feature_total += $feature_inf_factor;
            }
            $feature_total_after_pro_verify = $feature_total;
            if ($user->is_pro != 't' && $user->is_pro_test != 't') {
                $feature_total += abs(rating::round(abs(($feature_total_after_pro_verify*rating::PRO_FACTOR)) - abs($feature_total_after_pro_verify)));
            }
            if($user->is_verify != 't') {
                $feature_total += abs(rating::round(abs(($feature_total_after_pro_verify*rating::VERIFY_FACTOR)) - abs($feature_total_after_pro_verify)));
            }
        ?>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_15 b-layout__table_margbot_40">
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__td_width_240 b-layout__one_padbot_20">
        			<p class="b-layout__txt_fontsize_20">� ������ &rarr;</p></td>
        		<td colspan="2" class="b-layout__one_padbot_20">
        			<p class="b-layout__txt_float_left b-layout__txt_fontsize_20 b-text__bold b-layout__txt_color_6db335"><?= $feature_total;?></p>
        			<p class="b-layout_clear_both b-layout__title_color_4e">����� ������� ��� �������</p>
        		</td>
        	</tr>
            <? if ($user->is_pro != 't' && $user->is_pro_test != 't') { ?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">������� &nbsp;<span class="b-icon b-icon__pro b-icon__pro_f b-icon_top_4" title="PRO"></span> x 1.2</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold b-layout__txt_color_6db335">
                    <?= abs(rating::round(abs(($feature_total_after_pro_verify*rating::PRO_FACTOR)) - abs($feature_total_after_pro_verify))) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/payed/" target="_blank" class="b-layout__link">������</a> &nbsp;<?= view_pro(); ?></td>
        	</tr>
            <? }//if?>
            <? if($user->is_verify != 't') { ?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">����������� &nbsp;<span class="b-icon b-icon__ver b-icon_top_2"></span> x 1.2</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold b-layout__txt_color_6db335">
                    <?= abs(rating::round(abs(($feature_total_after_pro_verify*rating::VERIFY_FACTOR)) - abs($feature_total_after_pro_verify))) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/promo/verification/" target="_blank" class="b-layout__link">������ �����������</a></td>
        	</tr>
            <? }//if?>
            <? if($feature_portf != 0) {?> 
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">������ � ���������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold b-layout__txt_color_6db335">
                    <?= $feature_portf;?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/users/<?= $user->login; ?>/portfolio" target="_blank" class="b-layout__link">�������� ������</a></td>
        	</tr>
            <? }//if?>
            <? if($feature_inf_factor != 0) {?>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">������������� �������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold b-layout__txt_color_6db335">
                    <?= $feature_inf_factor;?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/users/<?=$user->login; ?>/setup/info/" target="_blank" class="b-layout__link">��������� �������</a></td>
        	</tr>
            <? }//if?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">���������� ������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"></td>
        	</tr>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">��������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/konkurs/" target="_blank" class="b-layout__link">������� � ������ ��������</a></td>
        	</tr>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">��������� ����� fl.ru</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">1 ���� � ����</td>
        	</tr>

        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">����������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/commune/" target="_blank" class="b-layout__link">������� � ������ ����������</a></td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">������, ����������� �� ������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"></td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">������ �� �������</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/" target="_blank" class="b-layout__link">������� � ������ ������</a></td>
        	</tr>
        </table>
<!-- // ������� � ���������� -->
        <? }//if?>
                 
            
        <? if ($user->uid == get_uid(false)) { ?>
            <select name="ratingmode">
                <option value="month">� ���� ������</option>
                <option value="prev">� ������� ������</option>
                <option value="year">�� ���</option>
            </select>
            <h3>������ ��������� ��������</h3>
                
            <div id="raph"></div>
        <? } ?>
    </div>
        
        
    <div>
        <div class="page-rate-info">
            <p>� ������� ������ �������� �������, <noindex><a rel="nofollow" href="https://feedback.fl.ru/topic/397654-opisanie-sistemyi-rejtinga-frilanser/" target="_blank">��� ��������� �������</a></noindex>.</p>
            <p>���� � ��� �������� ������� � ���������� � <noindex><a rel="nofollow" href="https://feedback.fl.ru/" target="_blank">������ ���������</a></noindex>. � ������������� �������.</p>
        </div>
    </div>
        
</div>
          
          
      </td>
    </tr>
    <? if($sbr_info['success_cnt'] && $sbr_ratings) { ?>
      <tr>
        <td class="brdtop" style="padding:0px 20px 0px 20px;height:20px">
          <b>��������� �� ����������� �������</b> (<?=(int)$sbr_info['success_cnt']?>)
        </td>
      </tr>
      <tr>
        <td style="padding:10px 0 0 0" id="sbr_list">
            <? $i = 0; include ($_SERVER['DOCUMENT_ROOT'] ."/user/tpl.rating-sbr.php"); ?>
            <span id="more_sbr_content"></span>
            <? if((int)$sbr_info['success_cnt']>5) { ?>
                    <p class="last-sbr"><a href="" onClick="xajax_GetMoreSBR(<?=$user->uid?>, <?=$i?>); $(this).hide(); return false;" class="lnk-dot-666">�������� ���������� <?=((int)$sbr_info['success_cnt']-$i)?> ����������� ������</a></p>
            <? } ?>
        </td>
      </tr>
    <? } ?>
    <? if($r_data['kis']['total']) { ?>
      <tr>
        <td class="brdtop" style="padding:0px 20px 0px 20px;height:20px">
          <b>��������� �� ������� �� �������</b> (<?=$r_data['kis']['total']?>)
        </td>
      </tr>
      <tr>
        <td style="padding:10px 20px 15px">
			<div class="tbl-ratinginfo">
				<table>
					<colgroup>
						<col width="205" />
						<col />
					</colgroup>
					<tbody><tr>
						<th>�� ����������� (<?=$kis_unknown?$kis_unknown:'���'?>)</th>
						<td><?=$kis_unknown?"({$kis_per_unknown}%)":'&nbsp;'?></td>
					</tr>
					<tr>
						<th>��������� (<?= (int)$r_data['kis']['frl_refused']?(int)$r_data['kis']['frl_refused']:'���'?>)</th>
						<td><?=(int)$r_data['kis']['frl_refused']?"({$kis_per_frl_refused}%)":'&nbsp;'?></td>
					</tr>
					<tr class="line">
						<th>������� (<?=(int)$r_data['kis']['refused']?(int)$r_data['kis']['refused']:'���'?>)</th>
						<td><?=(int)$r_data['kis']['refused']?"({$kis_per_refused}%)":'&nbsp;'?></td>
					</tr>
					<?/*<tr>
						<td colspan="2">
							<table class="tbl-in">
								<tbody><tr>
									<td>- �� �������� ������: (<?=(int)$r_data['kis']['refused_1']?(int)$r_data['kis']['refused_1']:'���'?>) <? if((int)$r_data['kis']['refused_1']) {?><span><?=round($r_data['kis']['refused'] ? 100 * $r_data['kis']['refused_1'] / $r_data['kis']['refused'] : 0, 2)?>%</span><? } ?></td>
								</tr>
								<tr>
									<td>- �����������: (<?=(int)$r_data['kis']['refused_0']?(int)$r_data['kis']['refused_0']:'���'?>) <? if((int)$r_data['kis']['refused_0']) { ?><span><?=round($r_data['kis']['refused'] ? 100 * $r_data['kis']['refused_0'] / $r_data['kis']['refused'] : 0, 2)?>%</span><? } ?></td>
								</tr>
								<tr>
									<td>- �� �������� ����: (<?=(int)$r_data['kis']['refused_2']?(int)$r_data['kis']['refused_2']:'���'?>) <? if((int)$r_data['kis']['refused_2']) { ?><span><?=round($r_data['kis']['refused'] ? 100 * $r_data['kis']['refused_2'] / $r_data['kis']['refused'] : 0, 2)?>%</span><? } ?></td>
								</tr>
								<tr>
									<td>- ������ ������ �����������: (<?=(int)$r_data['kis']['refused_4']?(int)$r_data['kis']['refused_4']:'���'?>) <? if((int)$r_data['kis']['refused_4']) { ?><span><?=round($r_data['kis']['refused'] ? 100 * $r_data['kis']['refused_4'] / $r_data['kis']['refused'] : 0, 2)?>%</span><? } ?></td>
								</tr>
								<tr>
									<td>- ������ �������: (<?=$r_data['kis']['refused_3']?$r_data['kis']['refused_3']:'���'?>) <? if($r_data['kis']['refused_3']) { ?><span><?=round($r_data['kis']['refused'] ? 100 * $r_data['kis']['refused_3'] / $r_data['kis']['refused'] : 0, 2)?>%</span><? } ?></td>
								</tr>
							</tbody></table>
						</td>
					</tr>*/?>
					<tr class="line">
						<th>�������� (<?=(int)$r_data['kis']['selected']?(int)$r_data['kis']['selected']:'���'?>)</th>
						<td><?=(int)$r_data['kis']['selected']?"({$kis_per_selected}%)":'&nbsp;'?></td>
					</tr>
					<tr class="line">
						<th>����������� (<?=(int)$r_data['kis']['executor']?(int)$r_data['kis']['executor']:'���'?>)</th>
						<td><?=(int)$r_data['kis']['executor']?"({$kis_per_executor}%)":'&nbsp;'?></td>
					</tr>
				</tbody></table>
			</div>
        </td>
      </tr>
      <? if($prjs) { /*$prjs = projects_offers::GetFrlOffers($r_data['user_id'], 'marked', NULL); // ������ ��� ���������� �������*/ ?>
      <tr>
        <td class="brdtop" style="padding:0px 20px 0px 20px;height:20px">
          <b>������ ��������, ���������� �� �������</b> (<?=count($prjs)?>)
        </td>
      </tr>
      <tr>
        <td style="padding:10px 20px 15px">
			<div class="list-ratinginfo">
                <?
                $i=0;
                $prj_sum_rating = 0;
                foreach($prjs as $p) {
                    $prj_sum_rating += $p['rating'];
                }
                ?>
				<div>����� ������� ��������: <span class="ops-<?=$prj_sum_rating<0?'minus':'plus'?>"><?=$prj_sum_rating<0?'':'+'?><?=$prj_sum_rating?></span></div>
				<ol id="prj_list">
                    <?php 
                        $uid = get_uid(FALSE);
                        $is_adm = hasPermissions('users');
                        
                        foreach($prjs as $p) 
                        { 
                            $i++;
                            $is_link = (($uid > 0) && (in_array($uid, array($p['exec_id'],$p['project_user_id'],$p['offer_user_id'])) || $is_adm));
                    ?>
					<li>
					   <span class="prj_list_number"><?=$i?>.</span> 
                                           <?php if($p['kind'] == 9): ?>
                                                <?php if($is_link): ?>
                                                <a href="<?=getFriendlyURL("project", $p['project_id'])?>"><?=$p['project_name']?></a>
                                                <?php else: ?>
                                                <?=$p['project_name']?>
                                                <?php endif; ?>
                                           <?php else: ?>
                                                <a href="<?=getFriendlyURL("project", $p['project_id'])?>"><?=$p['project_name']?></a> 
                                           <?/*if($p['position']>0 && $p['is_executor']=='t'){?>(<?=$p['position']?>-� �����)<?}*/?>
                                           <?php endif; ?>
                          <? if($p['refused']=='t') { ?>
                            <p>�����: <span class="ops-minus"><?=$p['rating']?></span></p>
                          <? } if($p['selected']=='t') { ?>
                            <p>��������: <span class="ops-plus">+<?=$p['rating']?></span></p>
                          <? } if($p['is_executor']=='t' && $p['position'] <= 0) { ?>
                            <p>�����������: <span class="ops-plus">+<?=$p['rating']?></span></p>
                         <? } if($p['position'] > 0) { ?>
                            <p><?=$p['position']?>-� �����: <span class="ops-plus">+<?=$p['rating']?></span></p>
                          <? } if($p['blocked'] > 0) { ?>
                            <p>������� �������������: <span class="ops-plus"><?=rating::A_KIS_R_BONUS * 10?></span></p>
                          <? } ?>
					</li>
                    <?
                    if($i>4) break;
                    }
                    ?>
				</ol> 
                <? if(count($prjs)>5) { ?>
				<p id="lnk_more_prj"><a href="" class="lnk-dot-666" onClick="$('lnk_more_prj').setStyle('display', 'none'); xajax_GetMorePrj(<?=$r_data['user_id']?>); return false;">�������� ���������� <?=(count($prjs)-5)?> ��������</a></p>
                <? } ?>
			</div>
        </td>
      </tr>
      <? } ?>





    <? } ?>
  </table>
</div>
