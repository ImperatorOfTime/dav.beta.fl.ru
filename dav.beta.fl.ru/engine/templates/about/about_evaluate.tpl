{{include "header.tpl"}}
<script type="text/javascript">
	var userId = <?=(empty($_SESSION['uid'])? 0: $_SESSION['uid'])?>;
	var pagename = 'evaluate';
	var MAX_WISH_CHARS = <?=feedback::MAX_WISH_CHARS?>;
</script>
 
<div class="b-layout b-layout__page">
    <div class="b-layout__txt"><a class="b-layout__link" href="/about/">� �������</a> &rarr;</div>
    <? if (empty($$error)) { ?>
    	<h1 class="b-page__title b-page__title_padbot_30">������� ������ �������</h1>
    <? } else { ?>
        <h1 class="b-page__title b-page__title_padbot_30"><?=$$error?></h1>
    <? } ?>
 
 
				<? if (empty($$error)) { ?>
				<div class="b-layout__right b-layout__right_relative b-layout__right_width_72ps b-layout__right_float_right">
					<? if ($$evtype == 'webim') { ?>
					<p class="vote-title"><? if (empty($$operator)) {?>������ �<?=$$thread?><? } else { ?>����������� <?=$$operator?>, ������ �<?=$$thread?><? } ?></p>
					<? } else { ?>
					<p class="vote-title">������ ���������, ����� "<?=($$kind_name? $$kind_name: '����� �������')?>", ������ �<?=$$code['id']?></p>
					<? } ?>
					<p>�� ������ �� ���������, ����� ������� ������ ����������� ������� ��� �������������. ����������, �������� ��� ����������� � �������� ������ ����� ������ ���������.</p>
					<div id="err" class="fw" style="display: none">
						<b class="b1"></b>
						<b class="b2"></b>
						<div class="fw-in">����������, �������� ��������������� ���� �������.</div>
						<b class="b2"></b>
						<b class="b1"></b>
					</div>
					<form id="e-form" action="<?=$$page?>" method="post">
					<input type="hidden" name="evaluate" value="1" />
					<ul class="vote feedback-vote">
						<li class="c">
							<label>�������� ������</label> 
							<input type="hidden" id="e1" name="e1" value="0" />
							<span id="stars-1" class="stars-vote stars-vote-a vote-0">
								<span>
									<a href="" onclick="return StarsSet(1, 1)"></a>
									<span>
										<a href="" onclick="return StarsSet(1, 2)"></a>
										<span>
											<a href="" onclick="return StarsSet(1, 3)"></a>
											<span>
												<a href="" onclick="return StarsSet(1, 4)"></a>
												<span>
													<a href="" onclick="return StarsSet(1, 5)"></a>
												</span>
											</span>
										</span>
									</span>
								</span>
							</span>
						</li>
						<li class="c">
							<input type="hidden" id="e2" name="e2" value="0" />
							<label>��������� ����������</label>
							<span id="stars-2" class="stars-vote stars-vote-a vote-0">
								<span>
									<a href="" onclick="return StarsSet(2, 1)"></a>
									<span>
										<a href="" onclick="return StarsSet(2, 2)"></a>
										<span>
											<a href="" onclick="return StarsSet(2, 3)"></a>
											<span>
												<a href="" onclick="return StarsSet(2, 4)"></a>
												<span>
													<a href="" onclick="return StarsSet(2, 5)"></a>
												</span>
											</span>
										</span>
									</span>
								</span>
							</span>
						</li>
						<li class="c">
							<input type="hidden" id="e3" name="e3" value="0" />
							<label>����� �����������</label>
							<span id="stars-3" class="stars-vote stars-vote-a vote-0">
								<span>
									<a href="" onclick="return StarsSet(3, 1)"></a>
									<span>
										<a href="" onclick="return StarsSet(3, 2)"></a>
										<span>
											<a href="" onclick="return StarsSet(3, 3)"></a>
											<span>
												<a href="" onclick="return StarsSet(3, 4)"></a>
												<span>
													<a href="" onclick="return StarsSet(3, 5)"></a>
												</span>
											</span>
										</span>
									</span>
								</span>
							</span>
						</li>
					</ul>
					<div class="form form-vote-txt">
						<div class="form-el">
							<label>��������� (�������������)</label>
							<textarea id="wish" name="wish" rows="5" cols="20"></textarea>
							<div id="warnmess" class="feedback-warnmess" style="display:none">����� ������ ��������� �� ����� <?=feedback::MAX_WISH_CHARS?> ��������</div>
						</div>
						<div class="form-btn">
							<a href="" id="evaluate-send" onclick="return Evaluate()" class="btnr btnr-green2"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">�������� �����</span></span></span></a>
							<span id="evaluate-waiting" style="display:none" class="form-feedback-waiting"><img src="/images/load_fav_btn.gif" /></span>
							<span id="evaluate-success" style="display:none" class="form-feedback-complete">�������! ��� ����� ��������.</span>
						</div>
					</div>
					</form>
				</div>
				<? } ?>
                
				{{include "press_center/press_menu.tpl"}}

</div>

{{include "footer.tpl"}}