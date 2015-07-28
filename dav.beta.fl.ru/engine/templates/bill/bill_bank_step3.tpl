{{include "header.tpl"}}
<div class="body c">
	<div class="main c">
					<h1 class="b-page__title">��� ����</h1>
		<div class="rcol-big">
			{{include "bill/bill_menu.tpl"}}
			<div class="tabs-in bill-t-in c">
			<h3>����������, ���������, ��� �� ��������� ��� ������</h3>
				<div class="bill-pay-tbl">
					<b class="b1"></b>
					<b class="b2"></b>
					<div>
						<table>
							<thead>
								<tr>
									<th>������������ ������</th>

									<th>����������</th>
<!--									<th>��. �����.</th>
									<th>����, ���.</th>-->
									<th>�����, ���.</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<th colspan="2">�����:</th>
									<td><?=($$sum-round($$sum*18/118, 2))?></td>
								</tr>
								<tr>
									<th colspan="2">���:</th>
									<td><?=round($$sum*18/118, 2)?></td>

								</tr>
								<tr class="bpt-sum">
									<th colspan="2">����� � ������:</th>
									<td><?=$$sum?></td>
								</tr>
							</tfoot>
							<tbody>
								<tr>
									<td>������ ����� ����� www.Free-lance.ru (�-1849-2)</td>
									<td><?=(!$$norisk_id?round($$sum):$$sum)?></td>
<!--									<td>�.�.</td>
									<td><?=(($$norisk_id)?1:EXCH_TR)?></td>-->
									<td><?=$$sum?></td>
								</tr>

							</tbody>
						</table>
					</div>
					<b class="b2"></b>
					<b class="b1"></b>
				</div>
				<div class="bill-left-col2">
					<div class="form bill-form">
						<b class="b1"></b>
						<b class="b2"></b>
						<div class="form-in">
						<form method="POST" name="gg">
							<input type="hidden" name="editing" value="1">
							<input type="hidden" name="address_grz" value="<?=stripslashes(($$reqv->address_grz))?>">
							<input type="hidden" name="bank_rs" value="<?=stripslashes(($$reqv->bank_rs))?>">
							<input type="hidden" name="bank_name" value="<?=stripslashes(($$reqv->bank_name))?>">
							<input type="hidden" name="bank_city" value="<?=stripslashes(($$reqv->bank_city))?>">
							<input type="hidden" name="bank_ks" value="<?=stripslashes(($$reqv->bank_ks))?>">
							<div class="form-block first">
								<div class="form-el">
									<label class="form-label3" for="">�������� �����������</label>
									<span class="form-input-value">
										<input type="hidden" name="org_name" value="<?=stripslashes(($$reqv->org_name))?>">
										<?=reformat(stripslashes(($$reqv->org_name)), 28)?>
									</span>
								</div>

								<div class="form-el">
									<label class="form-label3" for="">�������</label>
									<span class="form-input-value">
										<input type="hidden" name="phone" value="<?=stripslashes(($$reqv->phone))?>">
										<?=stripslashes(($$reqv->phone))?>
									</span>
								</div>
								<div class="form-el">
									<label class="form-label3" for="">����</label>
									<span class="form-input-value">
										<input type="hidden" name="fax" value="<?=stripslashes(($$reqv->fax))?>">
										<?=stripslashes(($$reqv->fax))?>
									</span>
								</div>
								<div class="form-el">
									<label class="form-label3" for="">����������� �����</label>
									<span class="form-input-value">
										<input type="hidden" name="email" value="<?=stripslashes(($$reqv->email))?>">
										<?=stripslashes(($$reqv->email))?>
									</span>
								</div>
								<div class="form-el">
									<label class="form-label3" for="">������</label>
									<span class="form-input-value">
										<input type="hidden" name="country" value="<?=stripslashes(($$reqv->country))?>">
										<?=stripslashes(($$reqv->country))?>
									</span>
								</div>
								<div class="form-el">
									<label class="form-label3" for="">�����</label>
									<span class="form-input-value">
										<input type="hidden" name="city" value="<?=stripslashes(($$reqv->city))?>">
										<?=stripslashes(($$reqv->city))?>
									</span>
								</div>
								<div class="form-el">
									<label class="form-label3" for="">�������� ������</label>
									<span class="form-input-value">
										<input type="hidden" name="index" value="<?=stripslashes(($$reqv->index))?>">
										<?=stripslashes(($$reqv->index))?>
									</span>
								</div>
								<div class="form-el">
									<label class="form-label3" for="">�������� �����</label>
									<span class="form-input-value">
										<input type="hidden" name="address" value="<?=stripslashes(($$reqv->address))?>">
										<?= stripslashes(($$reqv->address))?>
									</span>
								</div>
								<div class="form-el">

									<label class="form-label3" for="">���</label>
									<span class="form-input-value">
										<input type="hidden" name="inn" value="<?=stripslashes(($$reqv->inn))?>">
										<?=stripslashes(($$reqv->inn))?>
									</span>
								</div>
								<div class="form-el">
									<label class="form-label3" for="">���</label>
									<span class="form-input-value">
										<input type="hidden" name="kpp" value="<?=stripslashes(($$reqv->kpp))?>">
										<?=stripslashes(($$reqv->kpp))?>
									</span>
								</div>
								<div class="form-el">
									<label class="form-label3" for="">������ �������� �����������</label>
									<span class="form-input-value">
										<input type="hidden" name="full_name" value="<?=stripslashes(($$reqv->full_name))?>">
										<?= reformat(stripslashes(($$reqv->full_name)), 28)?>
									</span>
								</div>

								<div class="form-el">
									<label class="form-label3" for="">�������������</label>
									<span class="form-input-value">
										<input type="hidden" name="fio" value="<?=stripslashes(($$reqv->fio))?>">
										<?=stripslashes(($$reqv->fio))?>
									</span>
								</div>
								<div class="form-el">
									<label class="form-label3" for="">��. �����</label>
									<span class="form-input-value">
										<input type="hidden" name="address_jry" value="<?=stripslashes(($$reqv->address_jry))?>">
										<?= reformat(stripslashes(($$reqv->address_jry)), 28)?>
									</span>
								</div>
							</div>
							<div class="form-block last">
								<div class="form-btn">
									<input type="submit" name="send" value="�������������" class="i-btn" /> <input type="submit" name="next" value="����� &raquo;" class="i-btn" />
								</div>
							</div>
						</div>
						<b class="b2"></b>
						<b class="b1"></b>
					</form>	
					</div>
				</div>

				<div class="bill-right-col2 bill-info bill-rform">
					<p>������ ������:<br /><strong>���� ��� ����������� ���</strong></p>
					<p>������ ���������� ��������� �� ���� ��� &laquo;����&raquo;.<br />��� ����������� ���� ����������� ��������� ���� � �������� ��� � �����.</p>
					<p>��� ������ ������ ������ �� ��� (�� ���������� ���� ��<br />���������� ������), ��� ����� ������ �����������.</p>
				</div>
			</div>
		</div>
	</div>
</div>
{{include "footer.tpl"}}