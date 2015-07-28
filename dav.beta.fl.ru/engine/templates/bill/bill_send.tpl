{{include "header.tpl"}}
<? $transaction_id = $$account->start_transaction($$uid, $$tr_id); ?>
<?=$$xajax->printJavascript('/xajax/');?>
<script type="text/javascript">

	billing.init();
	
	window.onload = function() {
	<? if($$alert): ?>
        <? foreach($$alert as $key=>$val): ?>
        billing.tipView({id:'<?=$key?>'}, '<?=$val?>');
        <? endforeach; ?>
    <? endif; ?>   
    <? if($$login) { ?>
        xajax_CheckUser('<?= $$login?>');
    <? }//if?>
	}
	

 	function sumBlur(obj) {
 	    billing.clearEvent(obj);
 	    var val = obj.value;
 		var ammount = <?= round($$account->sum, 2);?>;
 		
 		if(val == 0) {
 			billing.tipView(obj, '�������� ������ ���� ������ ����');
			return false;	
 		}
 		
 		if(billing.isNumeric(val) == false) {
 			billing.tipView(obj, '����������, ������� �������� ��������');
			
			return false;
 		}
 		
 		if(val>ammount) {
 		    var wtf = Math.round((val-ammount)*100)/100;
 			billing.tipView(obj, '�� ����� ����� �� ������� ' + wtf + ' ���.');
			return false;		
 		}
 	}
 	
 	function loginCheck(obj) {
	    var myLogin = '<?=$_SESSION['login']?>';
 		billing.clearEvent(obj); 
 		
 		if(myLogin == obj.value) {
 		    billing.tipView(obj, '�� �� ������ ��������� ������ ������ ����');
 			return false;	    
 		}
 		
 		if(billing.isNull(obj.value) == true) {
 			billing.tipView(obj, '������ ���� �������� ������������');
 			return false;	
 		}
 		
 		xajax_CheckUser(obj.value);
 	}
    <? if (count($$alert)) { ?>
        window.addEvent('domready', function(){
            window.scrollTo(0, $('scroll_to').getPosition().y - 40)
        })
    <? } ?>
</script> 	

<div class="body c">
				<div class="main c">
					<h1 class="b-page__title">��� ����</h1>
					<div class="rcol-big">
						{{include "bill/bill_menu.tpl"}}
						<div class="tabs-in bill-t-in c">
							<h3 id="scroll_to">��������� ������</h3>
							<?php if($$canTransfer) { ?>
								<form  method="post" name="frm" id="frm">
								<input type="hidden" name="error_scr" id="error_scr" value="0">
								<input type="hidden" name="transaction_id" value="<?=$transaction_id?>">
								<input type="hidden" name="action" value="sendm">
								<div class="bill-left-col2">
									<div class="form bill-form">
										<b class="b1"></b>
										<b class="b2"></b>
										<div class="form-in">
											<div class="form-block first send-fm">
												<div class="form-el">
													<label class="form-label" for="">����� ��������:</label>
													<span class="form-input form-input2" id="sum_parent">
														<input type="text" value="<?=$$sum?>" id="sum" name="sum" maxlength="12" <?=($$alert[1]?'class="i-bold invalid"':'class="i-bold"')?> style="text-align:right" onkeyup="sumBlur(this);" onBlur=" this.value = this.value.replace(/\,/, '.'); this.value = this.value.replace(/\s/gi, ''); sumBlur(this);"/> ���.
														<input type="hidden" value="<?=$_SESSION["rand"] ?>" name="u_token_key" />
													</span>
												</div>
												<div class="form-el">
													<label class="form-label" for="">����� ����������:</label>
													<span class="form-input" id="login_parent">
														<input type="text" onfocus="billing.clearEvent(this)" onblur="loginCheck(this);" value="<?=htmlspecialchars(stripslashes($$login));?>"  id="login" name="login" <?=($$alert[0]?'class="i-bold invalid"':'class="i-bold"')?>/> 
	                                                    <button type="button">��������</button>
													</span>
												</div>
	                                            <div class="b-username b-username_overflow_hidden b-username_padbot_15 b-username_padleft_140" id="get_user_info"></div>	
											</div>
											<div class="form-block">
												<div class="form-el">
													<span class="form-hint fhr"></span>
													<label for="" class="form-label2">�����������:</label>
													<span class="form-txt" id="msg_parent">
														<textarea rows="5" cols="40" id="msg" name="msg" onBlur="billing.isMaxLen(this);" onKeyUp="billing.isMaxLen(this);"><?=htmlspecialchars(stripslashes($$msg));?></textarea>
													</span>
													<span class="form-hint">�� ������� <span id="count_length"><?=(strlen($$msg))?> <?=ending(strlen($$msg), "������", "�������", "��������")?></span>. ��������� �� ����� 300</span>
												</div>
											</div>
											<div class="form-block last">
												<div class="form-btn">
													<input type="submit" name="last_act" value="���������" class="i-btn" onClick="return billing.checkSend($('sum').get('value')); "/>
												</div>
											</div>
										</div>
										<b class="b2"></b>
										<b class="b1"></b>
									</div>
								</div>
								<div class="bill-right-col2 bill-info">
									<p>� ������� ���� ����� �� ������ ��������� ������ ������� ������������.</p>
									<div class="informer">
										<b class="b1"></b>
										<b class="b2"></b>
										<div class="informer-in">
	                                        <p>������� ����� ���������� � �����, ������� �� ����������� ���������. <br/>�� ����� ������ �������� ��������� ����������� � ��������.</p>

											<p>������ ����� ���������� ��������� � <strong>��� ��������</strong>.</p> 
										</div>
										<b class="b2"></b>
										<b class="b1"></b>
									</div>
								</div>
								</form>
							<?php } else { ?>
								<div class="b-fon b-fon_width_430">
									<b class="b-fon__b1"></b>
									<b class="b-fon__b2"></b>
									<div class="b-fon__body b-fon__body_pad_15">
										� ���������, �� ������ ������ ������ �������� FM ��� ��� ���������� � ����� � ���, ��� ���������� ������� ����� ������������� ���� ������ ����������� ����������� �����. <a class="b-fon__link" href="/bill/">��������� ����</a> ����� ������ �������� (��������� �������, ���������, ���, ��������-����, ����������� ������), ����� ���� �� ������� ��������������� ������ �������.
									</div>
									<b class="b-fon__b2"></b>
									<b class="b-fon__b1"></b>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
{{include "footer.tpl"}}										