{{include "header.tpl"}}
<div class="body c">
				<div class="main c">
					<h1 class="b-page__title">��� ����</h1>
					<div class="rcol-big">
						{{include "bill/bill_menu.tpl"}}
						<div class="tabs-in bill-t-in c">
							<h3>�������� ������ ������</h3>
                                                        <? if(is_emp()){ ?>
							<div class="form bill-norisk-imp fs-o">
								<b class="b1"></b>
								<b class="b2"></b>
								<div class="form-in">
                                    �������� � ������� ����� �� ����� ���� ������������ ��� �������������� � ����������� ������. ����������� ����� ����� ����� ��������� ������ �� ������������ ������� �������� �����.
								</div>
								<b class="b2"></b>
								<b class="b1"></b>
							</div>
                                                        <? } ?>
							<div class="bill-v c">

								<h4>��������� �������</h4>
								<div class="bill-v-in">
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/webmoney/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                          <img class="b-button__pic" src="/images/bill-wm1.png" alt="WebMoney" title="WebMoney"/>
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/yandex/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-yad.png" alt="Yandex ������" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/qiwipurse/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-qp.png" alt="QIWI �������" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/webpay/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/veb-koshel.png" alt="���-�������" style="margin:15px 10px;" />
                                            </span>
                                        </span>
                                    </a>
								</div>
							</div>
							<div class="bill-v c">
								<h4>��������� ������ � ��������</h4>
								<div class="bill-v-in">
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/qiwi/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-osmp.png" alt="����" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/qiwi/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-qiwi.png" alt="QIWI" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/svyasnoy/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/cvyaznoy.png" alt="�������" style="margin-bottom:1px" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/euroset/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/euroset.png" alt="��������" style="margin-bottom:1px" />
                                            </span>
                                        </span>
                                    </a>
                                
										<?/*<li><a href="/<?=$$name_page?>/elecsnet"><span><img src="/images/bill-elecsnet.png" alt="��������" width="151" height="51"></span></a></li>*/?>

								</div>
							</div>
							<div class="bill-v c">
								<h4>����������� �����</h4>
								<div class="bill-v-in">
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/card/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-mc.png" alt="MasterCard"  />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/card/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-visa.png" alt="Visa" />
                                            </span>
                                        </span>
                                    </a>
								</div>
							</div>
                            <? /* �������� ��������� #0019358
							<div class="bill-v c">
								<h4>� ������� SMS</h4>
								<div class="bill-v-in">
                                
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/sms/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-mts.png" alt="���" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/sms/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-beeline.png" alt="������" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/sms/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-megafon.png" alt="�������" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/sms/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
												<div class="b-button__txt b-button__txt_color_0f71c8  b-button__txt_padlr_15 b-button__txt_padtop_17">������ ��������</div>
                                            </span>
                                        </span>
                                    </a>
								</div>
							</div>*/?>
							
							<div class="bill-v c">
								<h4>��������-����</h4>
								<div class="bill-v-in">
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/alphabank/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-alfa.png" alt="�����-����" />
                                            </span>
                                        </span>
                                    </a>
								</div>
							</div>
							<div class="bill-v c">
								<h4>����������� ������</h4>
								<div class="bill-v-in">
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/bank/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <div class="b-button__txt b-button__txt_padlr_25 b-button__txt_color_0f71c8 b-button__txt_padtop_17">���� ��� ����������� ��� � ��</div>
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/sber/">        
                                        <span class="b-button__b1">
                                            <span class="b-button__b2">
                                                <div class="b-button__txt b-button__txt_padlr_25 b-button__txt_color_0f71c8 b-button__txt_padtop_17">��������� ��� ���������� ���</div>
                                            </span>
                                        </span>
                                    </a>
								</div>
							</div>
							
						</div>
					</div>

				</div>
			</div>
{{include "footer.tpl"}}	
