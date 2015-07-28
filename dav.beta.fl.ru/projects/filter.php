<? 
  // ������ ��������. ����������� � ������ �����. �� �����:
  // $uid -- get_uid().
  // $filter -- ������ � ����������� �������.
  // $filter_page -- ��� �������� (��. ������� projects_filters).
  // $filter_show -- 1: ������ ���������, 0: �������. /�������� ������ �� ������������ - ������, 8.10.2009/
  // $filter_inputs -- �������������� INPUT-� � �����.
  // $kind -- ��. �������� (���� ������ �� ������� ��������).
  // $page -- ����� �������� (���� ������ �� ������� ��������).
  // ���� ������ ���� �������� ������� ��� xajax �������, ������� ��� ������������.

  if (!$uid || is_emp())
    return 0;

  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");

  $has_hidd = TRUE;
  $filter_apply = ($filter['active'] == "t");
  $filter_categories = professions::GetAllGroupsLite(TRUE);
  
  //$filter_countries = country::GetCountries();
  //if ($filter['country']) {$filter_cities = city::GetCities($filter['country']);}
  
  if($filter['city']) {
      $location_selector = "drop_down_default_{$filter['city']} multi_drop_down_default_column_1";
      $location_value    = city::GetCountryName($filter['city']).": ".city::getCityName($filter['city']);
  } elseif($filter['country']) {
      $location_selector = "drop_down_default_{$filter['country']} multi_drop_down_default_column_0";
      $location_value    = country::getCountryName($filter['country']) . ": ��� ������";
  }
  
  
  switch($filter_page) {
    case 1:
      $frm_action = '/proj/?p=list';
      $prmd='&amp;';
      $has_hidd = FALSE;
      break;
    default:
      $frm_action = '/';
      $prmd='?';
  }

  if(!$_SESSION['ph'] && !$_SESSION['top_payed']) {
      $has_hidd = false; // �������� ���� ���� ������ ��������
  }
  
  if(!$filter) {
    $filter = array(
         'user_id' => $uid,
         'cost_from' => '',
         'cost_to' => '',
         'currency' => 0,
         'wo_cost' => 't',
         'country' => 0,
         'city' => 0,
         'keywords' => '',
         'categories' => array());
  }

  if($filter_params && is_array($filter_params)) {
    $filter_inputs = '';
    $filter_query = '';
    foreach($filter_params as $pn=>$pv) {
      $filter_inputs .= '<input type="hidden" name="'.$pn.'" value="'.$pv.'" />';
      $filter_query .= "&amp;{$pn}={$pv}";
    }
  }

  $all_mirrored_specs = professions::GetAllMirroredProfsId();
  $mirrored_specs = array();
  for ($is=0; $is<sizeof($all_mirrored_specs); $is++)
  {
    $mirrored_specs[$all_mirrored_specs[$is]['main_prof']] = $all_mirrored_specs[$is]['mirror_prof'];
    $mirrored_specs[$all_mirrored_specs[$is]['mirror_prof']] = $all_mirrored_specs[$is]['main_prof'];
  }


  $_SESSION['ph_categories'] = $filter['categories'];

  //������� ������ ������������� (��� ������� �� ������� �� ��� ���� � $prfs, ��� ������� � �������� ���������� ��� ���, ������� ������ �������� �� �������������
  if (!sizeof($profs)) {$all_specs = professions::GetAllProfessions("", 0, 1);}
  else                 {$all_specs = $profs;}

?>
<script type="text/javascript">
//1 = ������ ��������
//2 = ������ �����������
var curFBulletsBox = 1;

var filter_user_specs={<?
if ($filter['user_specs']) {
  $i=0;
  foreach($filter['user_specs'] as $ms)
    print(($i++?',':'').$ms.':1'); 
}
?>};

var filter_specs = new Array();
var filter_specs_ids = new Array();
<?
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

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/freelancers_filter.php";?>
var filter_mirror_specs = <?=freelancers_filters::getMirroredSpecsJsObject($all_mirrored_specs); ?>;
var filter_bullets = [[],[]];
<?
if (sizeof($_SESSION['ph_categories'])) {
  for ($ci=0; $ci<2; $ci++) {
    $ph_categories[$ci] = array();
    if (sizeof($_SESSION['ph_categories'][$ci])) {
      foreach ($_SESSION['ph_categories'][$ci] as $fkey => $fvalue) {
       if ($fkey) {
        if ( !freelancers_filters::mirrorExistsInArray($fkey, $ph_categories[$ci], $mirrored_specs) )
        {
          if (!$fvalue)
          {
            $proftitle = professions::GetGroup($fkey, $error);
            $proftitle = $proftitle['name'];
          } else {
            $proftitle = professions::GetProfName($fkey);
            $prof_group = professions::GetProfField($fkey, 'prof_group');
          }

?>
filter_bullets[<?=$fvalue?>][<?=$fkey?>] = new Array();
filter_bullets[<?=$fvalue?>][<?=$fkey?>]['type'] = <?=$fvalue?>;
filter_bullets[<?=$fvalue?>][<?=$fkey?>]['title'] = '<?=$proftitle?>';
filter_bullets[<?=$fvalue?>][<?=$fkey?>]['parentid'] = '<?=(!($fvalue)?0:$prof_group)?>';
<?
          if ($mirrored_specs[$fkey]) {
            ?>filter_bullets[<?=$fvalue?>][<?=$fkey?>]['mirror'] = <?=$mirrored_specs[$fkey]?>;<?
          } else {
            ?>filter_bullets[<?=$fvalue?>][<?=$fkey?>]['mirror'] = 0;<?
          }
        }
        $ph_categories[$ci][] = $fkey;
       }
      }
    }
  }
}
?>

</script>
<div class="b-frm-filtr" id="flt-pl" page="<?=$filter_page?>">
  <script type="text/javascript">

                          function togF(r){
            var d = new Date();
            d.setMonth(d.getMonth() + 1);
                            if(!$('filtrToggle').hasClass('b-layout_hide')) {
                              $('filtrToggle').addClass('b-layout_hide');
                              r.set('text', '����������');
                              $('mainFrmFltr').addClass('b-frm-filtr__item_reset');
                              
              document.cookie='new_pf'+$('b_ext_filter').get('page')+'='+''+'; expires='+d.toGMTString() + '; path=/';
                            } else {
                              $('filtrToggle').removeClass('b-layout_hide');
                              r.set('text', 'C�������');
                              $('mainFrmFltr').removeClass('b-frm-filtr__item_reset');
              document.cookie='new_pf'+$('b_ext_filter').get('page')+'='+'1'+'; expires='+d.toGMTString() + '; path=/';
                              }
                          }
function FilterCatalogAddCategoryType() {
    if ($('comboe_column_id').value == 0) {
        //��������� ���������
        if(Number($('comboe_db_id').value) > 0) {
            tl = $('comboe').get("value");
            /*tl = tl.replace(/: ?/, ''); � ����� ��� ����???*/ 
            tlf = tl;
            if (tl.length > 28) {
                tl = tl.substr(0, 28) + '...';
            }
            FilterAddBulletNew(0, $('comboe_db_id').value, tl, undefined, tlf);
            ComboboxManager.setDefaultValue('comboe', '��� �������������', 0);            
        }
    } else {
        //��������� ������������
        //if(Number($('comboe_db_id').value) > 0) {
            tl = $('comboe').get("value");
            tlf = tl;
            if (tl.length > 28) {
                tl = tl.substr(0, 28) + '...';
            }
            for(var i = 1;i<=filter_specs_ids.length;i++) {
                if(filter_specs_ids[i] && filter_specs_ids[i][$('comboe_db_id').value] == 1) {
                    var category_id = i;
                    break;
                }
            }
            var type  = 1;
            var value = $('comboe_db_id').value;
            var combo = ComboboxManager.getInput("comboe");
            if ((value == 0)&&(parseInt(combo.breadCrumbs[0]) )) {
                type =  0;
                value = parseInt(combo.breadCrumbs[0]);
            }
            FilterAddBulletNew(type, value, tl, category_id, tlf);
            ComboboxManager.setDefaultValue('comboe', '��� �������������', 0);            
        //}
    }
}

  </script>
  <div id="mainFrmFltr" class="b-frm-filtr__item <?=(($filter_show)?"":"b-frm-filtr__item_reset")?>">
     <div class="b-layout__txt b-layout__txt_float_right b-layout__txt_relative"><a onClick="togF(this);" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-filter-toggle-link" href="javascript:void(0)"><?=(($filter_show)?"��������":"����������")?></a></div>
     <div class="b-layout__txt">
      <? if ($filter_apply) { ?>
         <a class="b-layout__link b-layout__link_color_55b12e b-layout__link_bold b-layout__link_no-decorat" href="/projects<?=$frm_action?><?=$prmd?>action=deletefilter<?=$filter_query?>"><span class="b-icon b-icon__filtr b-icon__filtr_on"></span> ������ �������</a>
      <? } else { ?>
         <a class="b-layout__link b-layout__link_color_969696 b-layout__link_no-decorat" href="/projects<?=$frm_action?><?=$prmd?>action=activatefilter<?=$filter_query?>"><span class="b-icon b-icon__filtr b-icon__filtr_off"></span> ������ ��������</a>
      <? } ?>
      </div>
  </div>
  
  <div id="filtrToggle" class="b-layout <?= !$filter_show?"b-layout_hide":""?>">
      
      <form action="<?=$frm_action?>" method="post" id="frm">
      <div id="b_ext_filter" page="<?=$filter_page?>">
      <input type="hidden" name="action" value="postfilter" />
      <?=$filter_inputs?>

          <div class="b-frm-filtr__item">
          
           <table class="b-layout__table b-layout__table_width_full">
                <tr class="b-layout__tr">
                   <td class="b-layout__td b-layout__td_width_70">
                      <div class="b-layout__txt b-layout__txt_padtop_5">������ ��</div>
                   </td>
                   <td class="b-layout__td b-layout__td_padright_10">
                      <div class="b-combo">
                          <div class="b-combo__input b-combo_valign_mid">
                              <input id="pf_cost_from" class="b-combo__input-text b-combo__input-text_fontsize_15" name="pf_cost_from" value="<?=$filter['cost_from']?>" maxlength="6" type="text" size="80"  />
                              <label class="b-combo__label" for="pf_cost_from"></label>
                          </div>
                      </div>
                   </td>
                   <td class="b-layout__td b-layout__td_width_60">
                                                                                              <script type="text/javascript"> var currencyList = {0:"USD", 1:"����", 2:"���"}</script><div
                       class="b-combo b-combo_inline-block b-combo_zindex_4 b-combo_valign_mid">
                          <div class="b-combo__input b-combo__input_width_65 	b-combo__input_multi_dropdown b-combo__input_min-width_40 b-combo__input_arrow_yes b-combo__input_init_currencyList drop_down_default_2 reverse_list" >
                              <input id="pf_currency" type="hidden" name="pf_currency" value="<?= $filter['currency'] === null ? 2 : (int)$filter['currency'] ?>" />
                              <input id="currency_text" class="b-combo__input-text b-combo__input-text_fontsize_15" name="" type="text" size="80" onchange="$('pf_currency').value = $('currency_text_db_id').value" readonly="readonly"/>
                          </div>                    
                   </div>
                   </td>
                </tr>
             </table>
           <? if ($kind != 2) {?>
           <div class="b-check b-check_padtop_15">
                  <input id="pf_wo_budjet" class="b-check__input" type="checkbox" name="pf_wo_budjet" value="1" <?= ($filter['wo_cost'] == 't' || $_SESSION['wo_cost_check'])? 'checked="checked"' : '' ?>/>
                  <label for="pf_wo_budjet" class="b-check__label b-check__label_fontsize_13">������ &laquo;�� �������������&raquo;</label>
            </div>
			<? $_SESSION['wo_cost_check'] = false;} else {$_SESSION['wo_cost_check'] = ($filter['wo_cost'] == 't');}?>
          </div>

          <div class="b-frm-filtr__item">
                <input id="pf_category" name="pf_category" type="hidden" />
                <input id="pf_subcategory" name="pf_subcategory" type="hidden" />
                <div class="b-frm-fltr__title">������������� <a onclick="this.getParent('.b-frm-fltr__title').getNext('.b-layout').toggleClass('b-layout_hide'); return false;" class="b-button b-button_content_plus" href="#"></a></div>
               <div class="b-layout b-layout_hide">
                   <table class="b-layout__table b-layout__table_margbot_10 b-layout__table_width_full">
                      <tbody><tr class="b-layout__tr">
                         <td class="b-layout__td">
                              <div class="b-combo b-combo_margright_5 b-combo_zindex_3">
                                  <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_resize b-combo__input_max-width_450 b-combo__input_visible_height_200 b-combo__input_arrow_yes b-combo__input_init_professionsList sort_cnt drop_down_default_0 multi_drop_down_default_column_0 exclude_value_0_0">
                                      <input id="comboe" class="b-combo__input-text" name="" type="text" size="80" value="��� �������������" />
                                      <span class="b-combo__arrow"></span>
                                  </div>
                              </div>
                         </td>
                         <td class="b-layout__td">
                             <a class="b-button b-button_flat b-button_flat_grey" href="javascript:void(0)" onclick="FilterCatalogAddCategoryType();">��������</a>
                         </td>
                      </tr>
                   </tbody></table>
               </div>

                <? if(!(is_emp() || !get_uid(false))) { ?>
                    <div class="b-check b-check_padtop_10"> 		 
                        <table class="b-layout__table b-layout__table_width_full"> 		 
                            <tr class="b-layout__tr"> 	 	 
                                <td class="b-layout__td b-layout__td_width_20"> 		 
                                    <input id="pf_my_specs" class="b-check__input" type="checkbox" name="pf_my_specs" value="1" <?= ($filter['my_specs']=='t') ? 'checked="checked"': '' ?> /> 	 	 
                                </td> 
                                <td class="b-layout__td"> 
                                  <? if ($kind == 2 || $kind == 7) {
                                        $kindTitle = '��������';
                                    } elseif ($kind == 4) {
                                        $kindTitle = '��������';
                                    } else {
                                        $kindTitle = '�������';
                                    } ?>
                                    <label for="pf_my_specs" class="b-check__label b-check__label_fontsize_13"> <?= $kindTitle ?> ������ �� ���� �������������</label> 	
                                </td> 	 	 
                            </tr> 		 
                        </table>   	 
                    </div>                 
                      
                     <?php /* <div class="b-layout__txt"><span class="i-shadow">
                    <div id="choose-my-spec" class="b-shadow b-shadow_hide b-shadow_width_400 b-shadow_pad_20 b-shadow_zindex_3 b-shadow_right_20 b-shadow_top_-5">
                       <h2 class="b-layout__title">������� �������������</h2>
                       <div class="b-layout__txt b-layout__txt_padbot_10">������� �������������, �� ������� �� ������<br>������������� ������� � �������� ������</div>
                       <table class="b-layout__table">
                          <tbody><tr class="b-layout__tr">
                             <td class="b-layout__td">
                                  <div class="b-combo b-combo_margright_5 b-combo_zindex_3">
                                      <div class="b-combo__input b-combo__input_width_240">
                                          <input  class="b-combo__input-text" name="" type="text" size="80" value="��� �������������" />
                                          <span class="b-combo__arrow"></span>
                                      </div>
                                  </div>
                             </td>
                             <td class="b-layout__td">
                                 <a class="b-button b-button_flat b-button_flat_grey" href="javascript:void(0)">��������</a>
                             </td>
                          </tr>
                       </tbody></table>
                       <ul class="b-ext-filter__list"></ul>
                       
                       <div class="b-layout__txt b-layout__txt_padtb_10">������ ��������� ������������� ����� ���������<br>�������� � ����� �������.</div>
                       <div class="b-buttons">
                          <a class="b-button b-button_flat b-button_flat_green" href="javascript:void(0)">���������</a>
                          &#160;&#160;&#160;<span class="b-layout__txt b-layout__txt_fontsize_11"><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)"  onClick="$('choose-my-spec').addClass('b-shadow_hide');">��� �������, �� ��������</a></span>
                       </div>
                       
                       
                       <span class="b-shadow__icon b-shadow__icon_close"></span>
                       <span class="b-shadow__icon b-shadow__icon_nosik-right b-shadow__icon_top_10"></span>
                    </div>
                </span><a id="only-my-spec" class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onClick="$('choose-my-spec').toggleClass('b-shadow_hide');"> <?= $kindTitle ?> �� ���� ��������������</a></div> */ ?>
                <? } ?>
                <ul id="pf_specs" class="b-ext-filter__list"></ul>
            </div>
            
          <? if($kind != 1 && $kind != 2) { ?>
            <div class="b-frm-filtr__item">
                 <div class="b-layout__txt b-layout__txt_padbot_5">�����������������:</div>
                 
                 
                 
                 
                 
                <div class="b-combo  b-combo_zindex_2">
                    <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_orientation_left b-combo__input_arrow_yes b-combo__input_init_citiesList b-combo__input_on_click_request_id_getcities <?=$location_selector?> override_value_id_0_0_���+������ override_value_id_1_0_���+������">
                        <input id="location" class="b-combo__input-text" name="" type="text" size="80" value="<?= ($location_value ? $location_value : "��� ������")?>" />
                        <label class="b-combo__label" for="location"></label>
                        <span class="b-combo__arrow"></span>
                    </div>
                </div>
                 
                 
                 
                 <?php if(FALSE){ ?>
                 
                 <div class="b-select b-select_padbot_10">
                   <select class="b-select__select" id="pf_country" name="pf_country" onChange="FilterCityUpd(this.value)">
                     <option value="0">��� ������</option>
                     <?foreach ($filter_countries as $countid => $country) { ?>
                     <option value="<?=$countid?>"<? if ($countid == $filter['country']) echo(" selected") ?>><?=$country?></option>
                     <?}?>
                   </select>
                 </div>
                 <div id="frm_city" class="b-select">
                   <select class="b-select__select" name="pf_city">
                     <option value="0">��� ������</option>
                     <?if (sizeof($filter_cities)) foreach ($filter_cities as $cityid => $city) { ?>
                     <option value="<?=$cityid?>"<? if ($cityid == $filter['city']) echo(" selected") ?>><?=$city?></option>
                     <? } ?>
                   </select>
                 </div>
                 
                 <?php } ?>
                 
            </div>
          <? } ?>
            <div class="b-frm-filtr__item">
                        <div class="b-combo b-combo_static">
                                <div class="b-combo__input b-combo__input_static">
                                    <input id="pf_keywords" class="b-combo__input-text" placeholder="�������� �����" type="text" name="pf_keywords" value="<?=htmlspecialchars($filter['keywords'], ENT_QUOTES, 'cp1251')?>" maxlength="255" />
                                </div>
                        </div>

            </div>
            
            
            <button class="b-button b-button_flat b-button_flat_green" type="button" onclick="submit();">���������</button>&nbsp;&nbsp;&nbsp;<a href="javascript: void(0);" onclick="FilterClearForm()" class="b-buttons__link b-buttons__link_margleft_10 b-buttons__link_dot_0f71c8">��������</a>
      </div>
      </form>
      
  </div>
</div>

<script type="text/javascript">
FilterAddBullet(0,0,0,0);
</script>

<?

if ($has_hidd)
{

?>

<div class="flt-out <?=(($filter2_show)?"flt-show":"flt-hide")?>" id="flt-ph" page="10">
    <b class="b1"></b>
    <b class="b2"></b>
     <div class="flt-bar">
          <a href="javascript: void(0);" class="flt-tgl-lnk"><?=(($filter2_show)?"��������":"����������")?></a>
          <h3>������� ������� ������� <span id="flt-hide-cnt"><?=((sizeof($_SESSION['ph']) && $_SESSION['uid'])?" (".sizeof($_SESSION['ph']).")":"")?></span></h3>
     </div>
     <div class="flt-cnt" id="flt-hide-content" <?=(($filter2_show)?"style='display:block;'":"")?>>
      <?=projects_filters::ShowClosedProjects($kind, $page, (int)($filter['active']=='t'))?>
     </div>
    <b class="b2"></b>
    <b class="b1"></b>
</div>

<?
}

?>
