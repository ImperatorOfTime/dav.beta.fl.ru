<?
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
	$user = new employer();

    // �������� ���� ��������� PRO, ���� �����
    if(strtolower($_GET['pro_auto_prolong'])=='on') {
        $user->setPROAutoProlong('on',get_uid());
    }
    if(strtolower($_GET['pro_auto_prolong'])=='off') {
        $user->setPROAutoProlong('off',get_uid());
    }

	$user->GetUser($_SESSION['login']);
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
	$account = new account();
	$ok = $account->GetInfo($_SESSION['uid'], true);

    $u_is_pro_auto_prolong = $user->GetField($uid, $e, 'is_pro_auto_prolong', false); // �������� �� � ����� �������������� ��������� PRO
?>
<script type="text/javascript">
tr = true;

	function chang(t){
		var amm = <?=$account->sum?>;
		var s = t;
		var re = /^[0-9]*$/i;
		if ( s.match(re) == null) { tr = false; return (false); document.getElementById('buy').disabled = true;}
		v = t * 10;

		if (v > amm) { document.getElementById('error').className = 'error vis'; document.getElementById('buy').disabled = true;
			}else{
				document.getElementById('buy').disabled = false;document.getElementById('error').className = 'error';
			}
		document.getElementById('it').innerHTML='����� � ������: <span>' + v + '</span> FM';
	
		return (true);
	}

</script>



					<h2>������</h2>
					<div class="promo-page c">
						<h3 class="emp-payed-title">������� ��������������� ���<br />��������� ������������</h3>
						<div class="emp-payed-left-col">
							<div class="emp-promo">
								<b class="b1"></b>
								<b class="b2"></b>
								<div class="emp-promo-in c">
									<img src="../images/emp-payed-promo1.png" alt="" class="ep-left" />
									<div class="ep-txt">
										<strong>���������� ��������� ������ �������</strong> � ����� �����. ����� �������� ����������� ������� � ����� �����.
									</div>
								</div>
								<b class="b2"></b>
								<b class="b1"></b>
							</div>
							<div class="emp-promo">
								<b class="b1"></b>
								<b class="b2"></b>
								<div class="emp-promo-in c">
									<img src="../images/emp-payed-promo2.png" alt="" class="ep-right" />
									<div class="ep-txt2">
										�������� ����������� <strong>������� ������ ���������� � ����</strong> � �������� &ndash; ��������� ������� � �������� ��������.
									</div>
								</div>
								<b class="b2"></b>
								<b class="b1"></b>
							</div>
						</div>
						<div class="emp-payed-right-col">
						
							<p>������������ c ��������� PRO � �������� �������� �������, ����������� ������� ����� ������� �� �����, ����������� ����������� � ������������ ������ � �������� ���������� ����� �������.</p>

							<p>��������� ������ ������������ � ������ �������� ��������� <nobr>����������</nobr> ���� ����� ��������� � ������������ ������ ������ �����, � ����� � ��������� � ����������� ������.</p>

							<p>��������� �������� ������� ���������� ������� � ������� ������������ � ��������� PRO ������ ���c�������� �� ��������� ������ � ���� �� ������� �����������.</p>

							<p>� ������ ������� Free-lance.ru ������������� ��� ����������� �������� � �������� ������������� � ��������� PRO � ��������� �� �������� ���������.</p>

							<p>��� ������������� ����������� ��������, ����������, ���������� � ��� �� ������ <a href="mailto:info@free-lance.ru">info@free-lance.ru</a>, �� ����������� ������� ���.</p>

							<p><span>*��� ������������� �� ��������, ��� ����� ���, ��� �� ���������� �������� ���������, ��� ��������� �������������. ������� ������ � ��������� ���.</span></p>
							<div class="pay-block">
                                <form action="./buy.php" method="post" name="frmbuy" id="frmbuy">
                                <div>
								<div class="pay-inpt">���������� �������: <input type="text" size="3"  name="mnth" id="mnth" value="<?=floor($account->sum/10)?>" onKeyUp="return (chang(this.value));" /></div>
								<div class="pay-inpt" id="it">����� � ������: <span>10</span> FM</div>
                                <div id="error" class="error <? if ($error) { ?>vis<? } ?>"><?=view_error3("������������ �������. � ������ ������ �� ����� ".$account->sum."&nbsp;FM<br /> <a href=\"/bill/\" class=\"blue\">��������� ����</a>")?><br /></div>
								<div>
                                    <a href="javascript:void(0);" class="btn btn-blue" name="buy" id="buy" onClick="$('frmbuy').submit();"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">��������</span></span></span></a>
                                    &nbsp;<a href="/bill/webmoney/" class="btn btn-green"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">�������� ���������</span></span></span></a></div>

                				<input type="hidden" name="transaction_id" value="<?=$transaction_id?>" />
                				<input type="hidden" name="action" value="buy" />
                                </div>
                                </form>
							</div>

                            <? if($user->is_pro=='t') {?>
							<div class="pay-block">
                                <a name="pro_autoprolong"></a>
								<h4>�������������&nbsp;&nbsp;<span class="b-icon b-icon__pro b-icon__pro_e8"></span></h4>
								<p>������ ��� �� ����� ������� �� ������ ��������<br />�������� PRO.<br />���� � ��� ���� ������ �� �����, �� ������� ��� �����,<br />���������� � ������ ����� ����� ����������� 10FM.</p>
								<div>
                                    <? if($u_is_pro_auto_prolong=='t') { ?>
                                        <a href="javascript:void(0);" class="btn btn-pink" onClick="location='/payed/?pro_auto_prolong=off#pro_autoprolong';"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">���������</span></span></span></a>
                                    <? } else { ?>
                                        <a href="javascript:void(0);" class="btn btn-green" onClick="location='/payed/?pro_auto_prolong=on#pro_autoprolong';"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">��������</span></span></span></a>
                                    <? } ?>
                                </div>
							</div>
                            <? } ?>
       
						</div>
					</div>




<script type="text/javascript">
<!--
chang(document.getElementById('mnth').value);
//-->
</script>


