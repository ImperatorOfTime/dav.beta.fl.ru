{{include "header.tpl"}}
<div class="body c">
	<div class="main c">
					<h1 class="b-page__title">��� ����</h1>
		<div class="rcol-big">
			{{include "bill/bill_menu.tpl"}}

			
			<div class="tabs-in bill-t-in bill-sms-info">
    			<h3>������ � ������� SMS</h3>
    			<div class="bill-info">
    				<p>������ ��� ������, �������� � ������� (<a href="http://rates.planet3.ru/Ext.aspx" target="_blank">������ ���������</a>)</p>
    				<p class="bill-mobile c">
    					<img src="/images/mobile/beeline.png" alt="������" height="49" />
    					<img src="/images/mobile/mts.png" alt="���" height="49" />
    					<img src="/images/mobile/megafon.png" alt="�������" height="49" />
    					<img src="/images/mobile/velcom.png" alt="VELCOM" height="49" />
    					<img src="/images/mobile/life.png" alt="life:)" height="49" />
    				</p>
    				<div class="form fs-o form-sms-txt">
    					<b class="b1"></b>
    					<b class="b2"></b>
    					<div class="form-in">
    						��������� SMS � ����� &nbsp;
    						<span class="sms-code-big">
    							<span class="sms-l">
    								<span class="sms-in">
    									<strong>free 1+<span id="lp-acc-value"><?=$_SESSION['login']?></span></strong>
    								</span>
    							</span>
    						</span>
    						�� ������ �����:
    						<span class="form-space bill-sms-space">����� ������, �����������!</span>
    					</div>
    					<b class="b2"></b>
    					<b class="b1"></b>
    				</div>
    				<div class="bill-sms-tbl">
    					<b class="b1"></b>
    					<b class="b2"></b>
    					<div class="bill-sms-tbl-in">
							<table cellpadding="0" cellspacing="0"> 
								<thead>
									<tr>
										<th class="col1">�����&nbsp;����������</th>
										<th class="col2">�����&nbsp;SMS</th>
										<th class="cols">������</th>
										<th class="cols">��������</th>
										<th class="cols">�������</th>
										<th class="col7">�����������</th>
									</tr>
								</thead>
								<tbody> 
								    <? foreach(sms_services::$services['1'] as $phone=>$aOne):$i++ ?>
								    <tr <?if($aOne==end(sms_services::$services['1'])):?>class="last"<? endif; ?>> 
										<td class="col1"><?=$aOne['fm_sum']?> FM</td>
										<td class="col2"><span class="sms-code-r"><span class="sms-code-l"><span class="sms-code-m"><strong><?=$phone?></strong></span></span></span></td>
										<td class="cols"><?=$aOne['rur_sum']?>&nbsp;RUR</td>
										<td class="cols"><?= "{$aOne['byr_sum']}&nbsp;BYR" ?></td>
										<td class="cols"><?=$aOne['uah_sum']?>&nbsp;UAH</td>
										<td class="col7"><?=sms_services::$tariffs[$phone]['descr']?></td>
									</tr>
									<? endforeach; ?>
								</tbody> 
							</table> 
						</div>
						<b class="b2"></b>
						<b class="b1"></b>
					</div>
					<p>��������� ������� � ������� �������-���������� ��������������� ����� ����������. ��������� ���������� ����� ������:</p>
					<p>&ndash; � ������� &laquo;������ �� �������� �������&raquo; �� ����� <a href="http://www.mts.ru" target="_blank">www.mts.ru</a></p>
					<p>&ndash; � ���������� ������ �� �������� 8 800 333 0890 (0890 ��� ��������� ���)</p>
				</div>
			</div>
			
			
			
		</div>
	</div>
</div>
{{include "footer.tpl"}}