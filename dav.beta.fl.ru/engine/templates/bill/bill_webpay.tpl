{{include "header.tpl"}}
<script type="text/javascript">
	
	billing.init();
    
    function checkSend() {
        if ( billing.checkSend($('paysum').value) ) {
            setTimeout("$('paysum').value = ''", 500);
            return true;
        }
        return false;
    }
    
</script>
<div class="body c">
    <div class="main c">
        <h1 class="b-page__title">��� ����</h1>
        <div class="rcol-big">
            {{include "bill/bill_menu.tpl"}}
            <div class="tabs-in bill-t-in c">
            <form action="http://www.onlinedengi.ru/wmpaycheck.php" method="post" id="webpay" target="_blank">
            <input type="hidden" name="project" value="3097">
            <input type="hidden" name="mode_type" value="204">
            <input type="hidden" name="nickname" value="<?=$_SESSION['login']?>">
            <input type="hidden" name="nick_extra" value="<?=$_SESSION['login']?>">
                <h3 id="scroll_to">������ ����� ���-������</h3>
                <div class="bill-left-col2">
                    <div class="form bill-form"> <b class="b1"></b> <b class="b2"></b>
                        <div class="form-in">
                            <div>
                                <div class="form-block first">
                                    <div class="form-el" id="paysum_parent">
                                        <label class="form-label" for="paysum">����� ����������:</label>
                                        <span class="form-input form-input2">
                                        <input type="text" name="amount" value="" maxlength="12" id="paysum" class="i-bold" style="text-align:right;" onchange="billing.cur2FM(0, this); " /> <span id="curname">���.</span>
                                    </div>
                                </div>
										<div class="form-block last">
											<div class="form-btn">
												<input type="submit" value="��������" onClick="return checkSend();" id="pay" class="i-btn" />
											</div>

										</div>
                            </div>
                        </div>
                        <b class="b2"></b>
                        <b class="b1"></b> 
                    </div>
                </div>
                </form>
                    <div class="bill-right-col2 bill-info" style="width: 420px;">
                      <div class="pay-qiwi-logo c"> <img src="/images/veb-koshelek.png" width="277" height="56" alt="" /> <a class="color-006a4f" target="_blank" href="http://webpay.pscb.ru">webpay.pscb.ru</a> </div>
                      <p><a class="color-006a4f" target="_blank" href="http://webpay.pscb.ru">���-������� ����</a> � ��� ��������� ������� ��� ���������� ������ ��������� ����� � ������������� ���������� ��������� ��� ���������� � ����������� ���.</p>
                      <p>�� ������ ������ � ��� �������� ��������� ������ ���� �� Free-lance.ru ����� <a class="color-006a4f" target="_blank" href="http://webpay.pscb.ru">����</a> ���-��������.</p>
                      
                      <div class="b-fon b-fon_pad_10 b-fon_bg_fff9bf b-fon_padleft_35"><span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span><span class="b-layout__txt b-layout__txt_fontsize_11">�������� ��������!<br />&ndash; ���������� ����� ������������ � ������� 2-3 �����.<br />&ndash; ������� ������ ����� 15&nbsp;000 ������ �� ������������.</span></div>                      
                      
                      
                    </div>

                <span id="ammount" style="display:none"></span>
            </div>
        </div>
    </div>
</div>



{{include "footer.tpl"}}