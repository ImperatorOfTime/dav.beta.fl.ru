{{include "header.tpl"}}
<div class="body c">
	<div class="main c">
					<h1 class="b-page__title">��� ����</h1>
		<div class="rcol-big c">
			{{include "bill/bill_menu.tpl"}}
			<div class="tabs-in bill-t-in c">
    			<h3>�������� ������ �������!</h3>
       <? if ($$is_pending) { ?>
    				<div class="bill-info">
          				<div class="warning">
          					<b class="b1"></b>
          					<b class="b2"></b>
          					<p><strong>��������:</strong> � ������ ������ ���� �������������� ���� �������� ���������� ������ �����. ����������, ��������� ��������� ������ � �������� ��������.</p>
          					<b class="b2"></b>
          					<b class="b1"></b>
          				</div>
        </div>
       <? } ?>
    			<? if($$success_type == 'card') { ?>
    				<div class="bill-info">
          				<div class="warning">
          					<b class="b1"></b>
          					<b class="b2"></b>
          					<p><strong>��������:</strong> ���������� ����� � ������� ����������� ����� ����� ������ ������������ �����.</p>
          					<b class="b2"></b>
          					<b class="b1"></b>
          				</div>
                    </div>

    			<? } else if(is_array($$success)) foreach($$success as $info) { $fullsum += $info['sum']; ?>
    			<div class="form bill-form-tc">
                    <b class="b1"></b>
                    <b class="b2"></b>
                    <div class="form-in">
                        <div class="form-block first last">
                            <div class="form-el">
                                <label class="form-label3" for="">������:</label>
        						<span class="form-input-value"> <?=$info['name']?> <? if($info['descr'] && $info['descr'] != -1): ?>(<?=reformat($info['descr'],60,0,1)?>)<? endif; ?></span>
                            </div>
            				<div class="form-el">
                				<label class="form-label3" for="">�����:</label>
                				<span class="form-input-value"> <?= $info['sum'] ?></span>
            				</div>
            				<div class="form-el">
                				<label class="form-label3" for="">����:</label>
                				<span class="form-input-value"> <?=date('d.m.Y H:i (P \G\M\T)', strtotime($info['date']))?> </span>
            				</div>
    			         </div>
    			    </div>
    			    <b class="b2"></b>
    			    <b class="b1"></b>
    			</div>
    			<? } ?>
    			<? if($$back) { ?><p><a href="<?=$$back?>">���������</a></p></br><? } ?>
    			<? if($$addinfo) { ?><p><?=$$addinfo?></p></br><? } ?>
                <?
                  if($$tmpPrj && $$tmpPrj->getProject()) {
                  	if ($$account->sum >= $$tmpPrj->getPrice()) {
                  		?><p>� ��� ���� �������������� � ���������� ������ � ���������� ����� ��� ��� ������.</p><?
                  	} else {
                  		?><p>� ��� ���� �������������� � ���������� ������, �� ������������ ����� ��� ��� ������.</p><?
                  	}
             		?><p><a href="/public/?step=2&pk=<?=$$tmpkey?>">������� � �������</a></p><br/><?
                  }

                  if($_SESSION['masssending'])
                  {
                    require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
                    if($sss=$_SESSION['masssending']['freelancers']) {
                      $prof_ids = array_keys($sss['sel_profs']);
                      if($prof_ids[0] && ($prof_name = professions::GetProfName($prof_ids[0]))) 
                        $prof_name = " � ������� \"{$prof_name}\"";
                      else
                        $prof_name = ' � ������� "��� ����������"';
                      ?><br/>� ��� ���� <a href="/freelancers/?prof=<?=$prof_ids[0]?>" class="blue">������������� ��������<?=$prof_name?></a>.<?
                    }
                    if($sss=$_SESSION['masssending']['masssending']) {
                      ?><br/>� ��� ���� <a href="/masssending/" class="blue">������������� �������� �� ��������</a>.<?
                    }
                  }
                ?>
				<p>���� � ��� �������� ������� &mdash; ����������� � ������ <?= webim_button(2, '������-������������', '')?> ��� � <a href="//feedback.free-lance.ru" target="_blank">������ ���������</a>. � ������������� �������.</p>
            </div>
            <?include $_SERVER['DOCUMENT_ROOT']."/engine/templates/bill/bill_promo".$$rand.".tpl"?>
            
		</div>
	</div>
</div>			
	
{{include "footer.tpl"}}
