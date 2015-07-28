{{include "header.tpl"}}
<script type="text/javascript">
    var IS_EMP = <?= ( is_emp()? 'true' : 'false' ) ;?>;
	function chCurName(val, obj) {
		if(val == 1) {
			$$('#curname').set('text', '���.');
			$$('#purse').set('value', '<?=$$wmr_purse?>');
		}
		
		billing.cur2FM(1, obj);
	}
	
	function getMoney(v) {
		$$('#paysum').set('value', v);
		billing.cur2FM(0, $('paysum'));
		document.getElementById('paysum').focus();
	}
    
	billing.init();
    billing.exch=<?=EXCH_WMR?>;
	
</script>
<div class="body c">
	<div class="main c">
					<h1 class="b-page__title">��� ����</h1>
		<div class="rcol-big">
			{{include "bill/bill_menu.tpl"}}
		<div class="tabs-in bill-t-in c">
		<? if($$is_paymaster) { ?>
				<form id="wmpay" name="wmpay" method="post" action="https://paymaster.ru/Payment/Init">
				<div>
				<input type="hidden" name="LMI_MERCHANT_ID" value="<?=$$wmr_purse?>" />
				<input type="hidden" name="LMI_PAYMENT_AMOUNT" id="ammount" value="0" />
				<input type="hidden" name="LMI_CURRENCY" value="RUB" />
				<input type="hidden" name="LMI_PAYMENT_DESC_BASE64" value="<?=base64_encode(iconv('CP1251', 'UTF-8', "������ �� ������ ����� www.free-lance.ru, � ��� ����� ��� - 18%. ���� #" . $$account->id . ", ����� " . $$_user->login))?>" />
				<input type="hidden" name="LMI_PAYMENT_NO" value="<?=$$payment_number?>" />
				<input type="hidden" name="PAYMENT_BILL_NO" value="<?=$$account->id?>" />
				<input type="hidden" name="LMI_SIM_MODE" value="0" />
		<? } else { ?>
				<form id="wmpay" name="wmpay" method="POST" action="https://merchant.webmoney.ru/lmi/payment.asp">
				<div>
				<input type="hidden" name="LMI_PAYMENT_AMOUNT" id="ammount" value="0" />
				<input type="hidden" name="LMI_PAYMENT_DESC" value="������ �� ������ ����� www.free-lance.ru, � ��� ����� ��� - 18%. ���� #<?=$$account->id?>, ����� <?= $$_user->login ?>" />
				<input type="hidden" name="LMI_PAYEE_PURSE" id="purse" value="<?=$$wmr_purse?>" />
				<input type="hidden" name="LMI_PAYMENT_NO" value="<?=$$payment_number?>" />
				<input type="hidden" name="PAYMENT_BILL_NO" value="<?=$$account->id?>" />
				<input type="hidden" name="LMI_SIM_MODE" value="0" />
		<? } ?>
				
				<h3>������ � ������� WebMoney</h3>
				
				<div class="bill-left-col2">
					
					<div class="form bill-form">
						<b class="b1"></b>
						<b class="b2"></b>
						<div class="form-in">
                                                        <div class="form-block first">
								<div class="form-el" id="paysum_parent">
									<label class="form-label" for="paysum">����� ����������:</label>
									<span class="form-input form-input2">
										<input type="text" value="" maxlength="12" id="paysum" class="i-bold" style="text-align:right;" onchange="billing.cur2FM(0, this); " /> <span id="curname">���.</span>
    									<span class="form-nds">� ��� ����� ��� &mdash; 18%</span>
									</span>
								</div>
							</div>
                                                    <?/*
							<div class="form-block first">
								<div class="form-el" id="paysumfm_parent">
									<label class="form-label" for="paysumfm">����� ����������:</label>
									<span class="form-input form-input2">
										<input type="text" maxlength="12" value="" id="paysumfm" class="i-bold" onchange="billing.cur2FM(1, this); isGiftShow(this.value, true);"  style="text-align:right;" /> FM
									</span>
								</div>
							</div>*/?>
							<div class="form-block">
								<div class="form-el">
									<label class="form-label3" for="curtype">������� ��� ������:</label>
									<span class="form-select">
										<select onchange="chCurName(this.value, $('paysum'));" id="curtype">
											<option value="1">WMR (���������� �����)</option>
											<?php /*
											<option value="2">WMZ (�������)</option>
											*/?>
										</select>
									</span>
								</div>
								<?/*
								<div class="form-el">
									<label class="form-label3" >������� ������:</label>
									<span class="form-radio">
										<label><input type="radio" name="mode" value="0" checked="checked"/> web-���������</label>
										<label><input type="radio" name="mode" value="1" /> ������� �����</label>
									</span>
								</div>*/?>
							</div>
                            
							<div class="form-block last">
								<div class="form-btn">
									<input type="submit" value="��������" onClick="return billing.checkSend($('paysum').value);" id="pay" class="i-btn" />
								</div>
							</div>
						</div>
						<b class="b2"></b>
						<b class="b1"></b>
					</div>

                    <?php
                    $need_paysum = (float) $_COOKIE['need_paysum'];
                    if($need_paysum>0) {
                        ?>
                        <script type="text/javascript">
                        $('paysum').set('value', '<?=$need_paysum?>');
                        billing.cur2FM(0, $('paysum'));
                        </script>
                        <?
                    }
                    unset($_COOKIE['need_paysum']);
                    ?>

				</div>
				<div class="bill-right-col2 bill-info" style="width:420px;">
				    <p>�� ������ ��������� ���� ���� �� Free-lance.ru � �������:</p>
					<ul>
						<li><span><strong>WebMoney Keeper Classic</strong><br /><a href="https://feedback.free-lance.ru/article/details/id/122">��������� � ���������� �����</a> � ������� "Keeper Classic"</span></li>
						<li><span><strong>WebMoney Keeper Light</strong><br /><a href="https://feedback.free-lance.ru/article/details/id/128">��������� � ���������� �����</a> � ������� "Keeper Light"</span></li>
						<li><span><strong>WebMoney Keeper Mini</strong><br /><a href="https://feedback.free-lance.ru/article/details/id/132">��������� � ���������� �����</a> � ������� "Keeper Mini"</span></li>
						<li><span><strong>WebMoney Keeper Mobile</strong><br /><a href="https://feedback.free-lance.ru/article/details/id/138">��������� � ���������� �����</a> � ������� "Keeper Mobile"</span></li>
						<li><span><strong>WebMoney E-num</strong><br /><a href="https://feedback.free-lance.ru/article/details/id/146">��������� � ���������� �����</a> � ������� "E-num"</span></li>
						<li><span><strong>WebMoney Keeper ��� ���������� �����</strong><br /><a href="https://feedback.free-lance.ru/article/details/id/143">��������� � ���������� �����</a> � ������� "Keeper ��� ���������� �����"</span></li>
					</ul>
				</div>
				</div>
			</form>	
			</div>
		</div>
	</div>
</div>


<?php
if ( SERVER === 'local' || SERVER === 'beta' || SERVER === 'alpha' ) {
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////                                                                                                               ////
////                               ������ ��� ������������ ������� �� WebMoney                                     ////
////                                  �� ������ ��������� �� ����� �������                                         ////
////                                                                                                               ////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/account.php');
    $test_account = new account();
    $test_account->GetInfo( $_SESSION['uid'], true) 
?>
<script type="text/javascript">
function testFormSubmit() {
    var test_summ = $('LMI_PAYMENT_AMOUNT').get('value');
    var regexp    = /\d+/;
    if (!regexp.test(test_summ)) {
        alert('������� ����� �����');
        return false;
    }
    
    var hash_str = 'R109922555324' + test_summ + $('LMI_PAYMENT_NO').get('value') + $('LMI_MODE').get('value') + $('LMI_SYS_INVS_NO').get('value') + $('LMI_SYS_TRANS_NO').get('value') + $('LMI_SYS_TRANS_DATE').get('value') + 'R123456789098' + $('LMI_PAYER_WM').get('value');
    hash_str = md5( hash_str );
    hash_str = hash_str.toUpperCase();
    $('LMI_HASH').set('value', hash_str);
    $('test_form').submit();
    
    return true;
}
</script>
<div class="body c">
	<div class="main c">
        <h2>������ ��������, �� ���� � �����!</h2>
        <h3>������� ���� WebMoney �� ���� - �� ��� � ������ ����� ��������!</h3>
        <form id="test_form" name="test_form" action="/income/wm.php" method="POST">
        <input type="hidden" name="LMI_PAYEE_PURSE" id="LMI_PAYEE_PURSE" value="R109922555324">
        �����, ���.<input type="text"   name="LMI_PAYMENT_AMOUNT" id="LMI_PAYMENT_AMOUNT" value=""><br>
        <input type="hidden" name="LMI_PAYMENT_NO" id="LMI_PAYMENT_NO" value="<?=rand(999,999999)?>">
        <input type="hidden" name="LMI_SYS_INVS_NO" id="LMI_SYS_INVS_NO" value="<?=rand(999,999999)?>">
        <input type="hidden" name="LMI_SYS_TRANS_NO" id="LMI_SYS_TRANS_NO" value="<?=rand(999,999999)?>">
        <input type="hidden" name="LMI_PAYER_PURSE" id="LMI_PAYER_PURSE" value="R123456789098">
        <input type="hidden" name="LMI_PAYER_WM" id="LMI_PAYER_WM" value="<?=rand(999,999999)?>">
        <input type="hidden" name="LMI_SYS_TRANS_DATE" id="LMI_SYS_TRANS_DATE" value="<?=date('Y-m-d H:i:s')?>">
        <input type="hidden" name="LMI_HASH" id="LMI_HASH" value="">
        <input type="hidden" name="LMI_MODE" id="LMI_MODE" value="1">
        <input type="hidden" name="PAYMENT_BILL_NO" id="PAYMENT_BILL_NO" value="<?=$test_account->id?>">
        <input type="hidden" name="OPERATION_TYPE" id="OPERATION_TYPE" value="12">
        <input type="hidden" name="OPERATION_ID" id="OPERATION_ID" value="0">
        <input type="button" value=" ��������� " onclick="return testFormSubmit();">
        </form>
	</div>
</div>
<?php
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
?>

{{include "footer.tpl"}}