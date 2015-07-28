<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_answers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/op_codes.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
global $session;
session_start();
//    	$user = new freelancer();
$uid = get_uid();
//	$user->GetUser($_SESSION['login']);
//	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
//	$account = new account();
//    $ok = $account->GetInfo($uid, true);

$tr_id = intval($_REQUEST['transaction_id']);

$account = new account();
$transaction_id = $account->start_transaction($uid, $tr_id);
$op_codes = new op_codes();
$opcodes = $op_codes->getCodes('80,16,65');

if ($paid_specs = professions::getPaidSpecs($uid))
    $paid_spec_cnt = count($paid_specs);
$free_spec_cnt = is_pro() ? 5 : 1;
$spec_cnt = $paid_spec_cnt + $free_spec_cnt;
$paid_spec_price = $opcodes[professions::OP_PAID_SPEC]['sum'] * $paid_spec_cnt;

$poa = new projects_offers_answers();
$poa->GetInfo($uid);
$poa_codes = $poa->GetOpCodes();

$user = new freelancer();
// �������� ���� ��������� PRO, ���� �����
if (strtolower($_GET['pro_auto_prolong']) == 'on') {
    $user->setPROAutoProlong('on', $uid);
}
if (strtolower($_GET['pro_auto_prolong']) == 'off') {
    $user->setPROAutoProlong('off', $uid);
}

$user->GetUser($_SESSION['login']);
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
$account = new account();
$ok = $account->GetInfo($uid, true);

$u_is_pro_auto_prolong = $user->GetField($uid, $e, 'is_pro_auto_prolong', false); // �������� �� � ����� �������������� ��������� PRO

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/professions.common.php");
$xajax->printJavascript('/xajax/');
?>

<script type="text/javascript">
    var account_sum = <?= round($account->sum,2) ?>;
    var op = [];
<? foreach ($poa_codes as $ammount => $sum) { ?>
        op[<?= $ammount ?>] = <?= round($sum,2); ?>;
<? } ?>
    var SPARAMS={<?
if ($paid_specs)
    foreach ($paid_specs as $i => $prof) {
        echo ($i ? ',' : '') . $i . ':[' . (int) $prof['paid_id'] . ',' . (int) $prof['prof_id'] . ']';
    }
?>};

query = location.href.split('#');
if ( query[1] && query[1] != 'undefined' ) {
    pid = query[1].split('=');
    var exdate=new Date();
    exdate.setDate(exdate.getDate()+365);
    document.cookie="proonly_pid=" + pid[1] + ";expires=" + exdate.toGMTString() + ";path=/";
}

</script>

        <input type="hidden" name="mnth" value="1">
        <input type="hidden" name="week" id="week" value="1">
        <input type="hidden" name="transaction_id" value="<?= $transaction_id ?>">
        <input type="hidden" name="action" value="buy">
        <input type="hidden" id="week_payed" name="oppro" value="76"/>
        
            <h1 class="b-page__title">������� �������� ������ ��� <span title="PRO" class="b-icon b-icon__spro b-icon__spro_f"></span></h1>
            <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20">���������� � ��������� <a class="b-layout__link" href="/payed/"><span class="b-icon b-icon__pro b-icon__pro_f b-icon_top_4" alt="������� �������" title="������� �������"></span></a> ��������� �� ������������ � ������������ �������������� � �������������� � ���������� ������� �������� � �� �������. ������� PRO � ��� ���������� ������� ���������� � ���������� ��������� � ����� ������.</div>
            <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20">���������������� ������� ������������� ����������� ����� ����� ����������� � ������������, ������� ��������� �������� �������� ���������� � �������� ����� �������� �������� � ������������� ���������� �������. ����� �������� ����� � ��PRO�����!</div>
            <div class="payed-block payed-block-proonly">
                <b class="b1"></b>
                <b class="b2"><b class="b4"></b></b>
                <b class="b3"></b>
                <div class="payed-block-in">
                    <form action="/payed/buy.php" method="post" name="frmbuy" id="frmbuy">
            		<input type="hidden" name="mnth" value="1">
            		<input type="hidden" name="week" id="week" value="1">
            		<input type="hidden" name="transaction_id" value="<?=$transaction_id?>">
            		<input type="hidden" name="action" value="buy">
                    <h3 class="b-layout__h3">������� <span class="b-icon b-icon__pro b-icon__pro_f b-icon_top_3" title="�������� ��������"></span> ��������:</h3>
                    <? if($_SESSION['pro_last']): ?>
                    <?
                    $last_time = $_SESSION['pro_last'];
                    if(floor((strtotime($last_time)-time())/(60*60*24)) > 0) {
                        $last_ending = floor((strtotime($last_time)-time())/(60*60*24));
                        $last_string1 = '����';
                        $last_string2 = '���';
                        $last_string3 = '����';
                    } else if (floor((strtotime($last_time)-time())/(60*60)) > 0) {
                        $last_ending = floor((strtotime($last_time)-time())/(60*60));
                        $last_string1 = '���';
                        $last_string2 = '����';
                        $last_string3 = '�����';
                    } else {
                        $last_ending = floor((strtotime($last_time)-time())/(60));
                        $last_string1 = '������';
                        $last_string2 = '������';
                        $last_string3 = '�����';
                    }
                    ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10">��� <span class="b-icon b-icon__pro b-icon__pro_f b-icon_top_3" alt="������� �������" title="������� �������"></span> ������� �������� ����� <?=$last_ending?> <?=ending($last_ending, $last_string1, $last_string2, $last_string3)?></div>
                    <? endif; ?>
                    <table class="buy-pro-tbl">
                        <col width="22" />
                        <col width="70" />
                        <col width="15" />
                        <col width="75" />
                        <col width="15" />
                        <col width="70" />
                        <tr class="">
                            <td class="buy-pro-tbl__radio"><input type="radio" name="oppro" value="132"/></td>
							<td><span class="payed-price"><b class="b1"></b><b class="b2"></b><span class="payed-price-in">99 ���.</span><b class="b2"></b><b class="b1"></b></span></td>
							<td class="sign">�</td>
							<td>1 ����</td>
							<td class="sign">=</td>
							<td><strong>99 ���.</strong></td>
                        </tr>
                        <tr class="">
                            <td class="buy-pro-tbl__radio"><input type="radio" name="oppro" value="131"/></td>
							<td><span class="payed-price"><b class="b1"></b><b class="b2"></b><span class="payed-price-in">299 ���.</span><b class="b2"></b><b class="b1"></b></span></td>
							<td class="sign">�</td>
							<td>1 ������</td>
							<td class="sign">=</td>
							<td><strong>299 ���.</strong></td>
                        </tr>
                        <tr>
                            <td class="buy-pro-tbl__radio"><input type="radio" name="oppro" value="48" onClick="if(this.checked) noSumAmmount(780, 'block_pro_pay', 'pro_pay_sum');"/></td>
							<td><span class="payed-price"><b class="b1"></b><b class="b2"></b><span class="payed-price-in">780 ���.</span><b class="b2"></b><b class="b1"></b></span></td>
							<td class="sign">�</td>
							<td>1 �����</td>
							<td class="sign">=</td>
							<td><strong>780 ���.</strong></td>
                        </tr>
                        <tr>
                            <td class="buy-pro-tbl__radio"><input type="radio" name="oppro" value="49" onClick="if(this.checked) noSumAmmount(2220, 'block_pro_pay', 'pro_pay_sum');"/></td>
                            <td><span class="payed-price"><b class="b1"></b><b class="b2"></b><span class="payed-price-in">740 ���.</span><b class="b2"></b><b class="b1"></b></span></td>
                            <td class="sign">�</td>
							<td>3 ������</td>
							<td class="sign">=</td>
							<td><strong>2220 ���.</strong></td>
						</tr>
						<tr>
                            <td class="buy-pro-tbl__radio"><input type="radio" name="oppro" value="50" onClick="if(this.checked) noSumAmmount(4200, 'block_pro_pay', 'pro_pay_sum');"/></td>
							<td><span class="payed-price"><b class="b1"></b><b class="b2"></b><span class="payed-price-in">700 ���.</span><b class="b2"></b><b class="b1"></b></span></td>
							<td class="sign">�</td>
							<td>6 �������</td>
							<td class="sign">=</td>
							<td><strong>4200 ���.</strong></td>
						</tr>
						<tr>
                            <td class="buy-pro-tbl__radio"><input type="radio" name="oppro" value="51" onClick="if(this.checked) noSumAmmount(7500, 'block_pro_pay', 'pro_pay_sum');"/></td>
							<td><span class="payed-price"><b class="b1"></b><b class="b2"></b><span class="payed-price-in">625 ���.</span><b class="b2"></b><b class="b1"></b></span></td>
							<td class="sign">�</td>
							<td>12 �������</td>
							<td class="sign">=</td>
							<td><strong>7500 ���.</strong></td>
					    </tr>
					</table>
                    <div>
                        <a href="javascript:void(0);" class="btn btn-blue" onClick="if(!$(this).hasClass('btn-disabled')) { $(this).addClass('btn-disabled'); checkBalance('block_pro_pay', 'frmbuy'); }"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">������</span></span></span></a>
					</div>
					</form>
                </div>
                <b class="b3"></b>
                <b class="b2"><b class="b4"></b></b>
                <b class="b1"></b>
            </div>

            <h3 class="b-layout__h3">������ ��� ����������� � PRO:</h3>
            
<div class="b-promo">
	<ul class="b-promo__list">
			<li class="b-promo__item b-promo__item_fontsize_15"><span class="b-promo__item-number b-promo__item-plus"></span>�������������� ���������� ������� �� ������� �� ���� ��������������</li>
			<li class="b-promo__item b-promo__item_fontsize_15"><span class="b-promo__item-number b-promo__item-plus"></span>�������� ��������: ����� ��.�����, Skype, ICQ, ������ �� ��������� ������� ��������� � ������� ���������� � ����� ���� ������������� �����</li>
			<li class="b-promo__item b-promo__item_fontsize_15"><span class="b-promo__item-number b-promo__item-plus"></span>����������� ����������� �� ������� � �������� ������� ��� <a class="b-layout__link" href="/payed/"><span class="b-icon b-icon__pro b-icon__pro_f b-icon_top_3" alt="������� �������" title="������� �������"></span></a>�</li>
			<li class="b-promo__item b-promo__item_fontsize_15"><span class="b-promo__item-number b-promo__item-plus"></span>���������� �������� �� 20%</li>
			<li class="b-promo__item b-promo__item_fontsize_15"><span class="b-promo__item-number b-promo__item-plus"></span>���������� ������� �������� � �������� �������������</li>
			<li class="b-promo__item b-promo__item_fontsize_15"><span class="b-promo__item-number b-promo__item-plus"></span>������ �������������� ������������� � �������� �����������</li>
			<li class="b-promo__item b-promo__item_fontsize_15"><span class="b-promo__item-number b-promo__item-plus"></span>���������� ������� �� ������� ���� ��������� (� ���� <a class="b-layout__link" href="/payed/"><span class="b-icon b-icon__pro b-icon__pro_f b-icon_top_3" alt="������� �������" title="������� �������"></span></a>)</li>
			<li class="b-promo__item b-promo__item_fontsize_15"><span class="b-promo__item-number b-promo__item-plus"></span>����������� ��������� ����������</li>
			<li class="b-promo__item b-promo__item_fontsize_15"><span class="b-promo__item-number b-promo__item-plus"></span>����������� ������ ����� � ���������</li>
			<li class="b-promo__item b-promo__item_fontsize_15"><span class="b-promo__item-number b-promo__item-plus"></span>������� ������� ������� � ��������� � ���������</li>
	</ul>
</div>            
            
            <div class="b-layout__txt"><a class="b-layout__link" href="/payed/">� ������ �������� ������������</a></div>

