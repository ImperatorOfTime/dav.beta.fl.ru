{{include "header.tpl"}}
<div class="body c">
	<div class="main c">
					<h1 class="b-page__title">��� ����</h1>
		<div class="rcol-big c">
			{{include "bill/bill_menu.tpl"}}
			<div class="tabs-in bill-t-in c">
    			<h3>�������� �� ���������.</h3>
    			<div class="form bill-form-tc">
                    <b class="b1"></b>
                    <b class="b2"></b>
                    <div class="form-in">
                        <div class="form-block first last">
                            <div class="form-el">
                                <label class="form-label3" for="">������:</label>
        						<span class="form-input-value"><?=($$error ? $$error : '����������� ������.')?></span>
                            </div>
    			         </div>
    			    </div>
    			    <b class="b2"></b>
    			    <b class="b1"></b>
    			</div>
    			<? if($$back) { ?><p><a href="<?=$$back?>">���������</a></p></br><? } ?>
    			<? if($$addinfo) { ?><p><?=$$addinfo?></p></br><? } ?>
				<p>���� � ��� �������� ������� &mdash; ����������� � <a href="//feedback.free-lance.ru" target="_blank">������ ���������</a>. � ������������� �������.</p>
            </div>
		</div>
	</div>
</div>			
	
{{include "footer.tpl"}}