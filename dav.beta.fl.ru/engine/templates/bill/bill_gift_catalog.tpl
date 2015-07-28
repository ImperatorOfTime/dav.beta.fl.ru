{{include "header.tpl"}}

<? $transaction_id = $$account->start_transaction($$uid, $$tr_id); ?>
<?=$$xajax->printJavascript('/xajax/');?>
<script type="text/javascript">
var curFBulletsBox = 2;
var maxCostBlock = 12;
var filter_user_specs = new Array();
var filter_specs = new Array();
var filter_specs_ids = new Array();

<?
if (!sizeof($profs)) {$all_specs = professions::GetAllProfessions("", 0, 1);} else {$all_specs = $profs;}
$spec_now = 0;
for ($i=0; $i<sizeof($all_specs); $i++)
{
  if ($all_specs[$i]['groupid'] != $spec_now) {
    $spec_now = $all_specs[$i]['groupid'];
    echo "filter_specs[".$all_specs[$i]['groupid']."]=[";
  }


  echo "[".$all_specs[$i]['id'].",'".$all_specs[$i]['profname']."']";

  if ($all_specs[$i+1]['groupid'] != $spec_now) {echo "];";}
  else {echo ",";}
}

$spec_now = 0;
for ($i=0; $i<sizeof($all_specs); $i++)
{
  if ($all_specs[$i]['groupid'] != $spec_now) {
    $spec_now = $all_specs[$i]['groupid'];
    echo "filter_specs_ids[".$all_specs[$i]['groupid']."]={";
  }


  echo "".$all_specs[$i]['id'].":1";

  if ($all_specs[$i+1]['groupid'] != $spec_now) {echo "};";}
  else {echo ",";}
}
?>
        billing.init();

        window.onload = function(){
        <? if($$error): ?>
                <? foreach($$error as $key=>$val): ?>
                billing.tipView({id:'<?=$key?>'}, '<?=$val?>');
                <? endforeach; ?>
        <? endif; ?>
        };

        function loginCheck(obj) {
            var myLogin = '<?=$_SESSION['login']?>';
            billing.clearEvent(obj);

                if(myLogin == obj.value) {
                    billing.tipView(obj, '�� �� ������ ������� ������� ������ ����');
                        return false;
                }

                if(billing.isNull(obj.value) == true) {
                        billing.tipView(obj, '������ ���� �������� ������������');
                        return false;
                }

                xajax_CheckUserType(obj.value, 1);
        }

        function weekCheck(obj) {
 		var ammount = <?=intval($$account->sum);?>;
 		var payweek = Number(document.getElementById('pf_subcategory').value) == 0 ? <?php echo $$price_top;?> : <?php echo $$price_inside;?>;
 		billing.clearEvent(obj);
 		obj.value = obj.value.replace(/\,/, '.');
 		obj.value = obj.value.replace(/\s/gi, '');

 		if(billing.isNull(obj.value) == true) {
 			billing.tipView(obj, '������ ���� �������� ������������');
 			return false;
 		}

 		if(billing.isNull(obj.value, 1) == true) {
 			billing.tipView(obj, '�������� ������ ���� ������ ����');
 			return false;
 		}

 		if(billing.isNumeric(obj.value, 1) == false) {
 			billing.tipView(obj, '����������, ������� ����� �������� ��������');
 			return false;
 		}

 		var pay = obj.value*payweek;

 		if(pay > ammount) {
 		    var wtf = Math.round((pay-ammount)*100)/100;
			billing.tipView(obj, '�� ����� ����� �� ������� ' + wtf + ' FM');
			return false;
		}

		if(pay > 0) $('pay').set('text', pay);
		else $('pay').set('text', payweek);
                $$('input[name=pay_val]').set('value',pay);
 	}

        function isNumb(obj){
            return bill.isNumeric(ob)
        }
    <? if (count($$error)) { ?>
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
                <h3 id="scroll_to" class="bill-gifts-h3">�������� ���������� � ��������</h3>
                <div class="bill-left-col2">
                    <div class="form bill-form">
                        <b class="b1"></b>
                        <b class="b2"></b>
                        <form method="post" action=".">
                            <input type="hidden" name="pay_val" value="0"/>
                            <input type="hidden" name="transaction_id" value="<?=$transaction_id?>">
                            <input type="hidden" value="<?=$_SESSION["rand"] ?>" name="u_token_key" />
                        <div class="form-in">
                            <div class="form-block first">
                                <div class="form-el">
                                    <label class="form-label" for="">����� ����������:</label>
                                    <span class="form-input" id="login_parent">
                                        <input type="text" value="<?=htmlspecialchars($$login)?>"  id="login" name="login" class="i-bold" onblur="loginCheck(this); " />
                                    </span>
                                </div>
                                <div class="form-el">
                                    <label class="form-label" for="">���������� ������:</label>
                                    <span class="form-input" id="week_parent">
                                        <input onchange="weekCheck(this)" type="text" value="" id="week" name="weeks" class="i-bold"/>
                                    </span>
                                </div>
                            </div>
                            <div class="form-block">
                                <div class="form-el">
                                    <span class="form-hint fhr"></span>
                                    <label for="" class="form-label2">��������������� �������</label>
                                    <span class="form-txt" id="descr_parent">
                                        <textarea onblur="billing.isMaxLen(this);" onkeyup="billing.isMaxLen(this);" name="msg" rows="5" cols="40" id="descr"><?=$$msg;?></textarea>
                                    </span>
                                    <span class="form-hint">�� ������� <span id="count_length">0 ��������</span>. ��������� �� ����� 300</span>
                                </div>
                            </div>
                            <div class="form-block">
                                <div class="form-el">
                                    <label class="form-label" for="">������ ��������:</label>
                                    <span class="form-bill-input">
                                        <div class="form-bill-cat">
                                            <select name="pf_category" id="pf_category" onChange="FilterSubCategory(this.value,true); weekCheck(document.getElementById('week'))"/>
                                            <option selected="selected" value="0">��� ����������</option>
                                                <? foreach($$filter_categories as $cat) { if($cat['id']<=0 || $cat['id']==15) continue; ?>
                                                 <option value="<?=$cat['id']?>"><?=$cat['name']?></option>
                                                 <? } ?>
                                            </select>
                                        </div>
                                    </span>
                                </div>
                                <div class="form-el">
                                    <label class="form-label" for="">���������:</label>
                                    <span class="form-bill-input">
                                        <div class="form-bill-cat">
                                            <select disabled="disabled" name="pf_subcategory" id="pf_subcategory" onchange="weekCheck(document.getElementById('week'))""/>
                                           </select>
                                        </div>
                                    </span>
                                </div>
                            </div>
                            <div class="form-block">
                                <div class="form-el">
                                    <label class="form-label" for="">����� � ������:</label>
                                    <span>
                                        <span id="pay">0</span> FM
                                    </span>
                                </div>
                            </div>
                            <div class="form-block last">
                                <div class="form-btn">
                                    <input  name="act" type="submit" value="��������" class="i-btn"/>
                                </div>
                            </div>
                        </div>
                        </form>
                        <b class="b2"></b>
                        <b class="b1"></b>
                    </div>
                </div>
                <div class="bill-right-col2 bill-info">
			         �������� ������� ����� ������� � �������� &mdash; ��������� �� �������.
                </div>
            </div>
        </div>
    </div>
</div>
{{include "footer.tpl"}}
