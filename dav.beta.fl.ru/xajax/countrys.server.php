<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/countrys.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");

/**
 * ���������� select �� ������� ������� ��������� ������ � ���� � id="frm_city"
 * 
 * @param  int $country_id ID ������
 * @param  array $attr �����������. �������� select ��������: array('name'=>'pf_city', 'class'=>'flt-p-sel',...);
 * @return unknown
 */
function GetCitysByCid( $country_id, $attr = array() ){
	$objResponse = new xajaxResponse();
	
	if ( !$attr ) {
		$attr = array( 'name'=>'pf_city', 'class'=>'b-select__select' );
	}
	
	$sAttr = '';
	
	foreach ( $attr as $key => $val ) {
		$sAttr .= ' ' . $key . '="' . $val . '"';
	}
	
	if ($country_id){
		$cities = city::GetCities($country_id);
	}
	
	$out_text = "<select $sAttr><option value=\"0\">��� ������</option>";
	if($cities) foreach ($cities as $cityid => $city)
		$out_text .= "<option value=".$cityid.">".$city."</option>";
	$out_text .= "</select>";
	$objResponse->assign("frm_city","innerHTML",$out_text);
    $objResponse->script("$('pf_country').erase('disabled')");
	return $objResponse;
}

/**
 * ���������� select �� ������� ������� ��������� ������ � ��� ������� �������� � �������� �����������
 * 
 * @param  int $country �������� ������ ��������
 * @param  array $attr �����������. �������� select ��������: array('name'=>'pf_city', 'class'=>'flt-p-sel',...);
 * @return unknown
 */
function RFGetCitysByCid( $country, $attr = array() ){
	$objResponse = new xajaxResponse();
	
	if ( !$attr ) {
		$attr = array( 'name'=>'pf_city', 'class'=>'b-select__select' );
	}
	
	$sAttr = '';
	
	foreach ( $attr as $key => $val ) {
		$sAttr .= ' ' . $key . '="' . $val . '"';
	}
	
	if ($country){
		$cities = city::GetCities(country::getCountryIDByTranslit($country));
	}

	$objResponse->script('$("b-select__city").set("html","");');
	$objResponse->script('new Element("option", { value: "0", text: "��� ������" }).inject($("b-select__city"));');
	$js = '';
	if($cities) foreach ($cities as $cityid => $city)
		$js .= 'new Element("option", { value: "'.translit(strtolower($city)).'", text: "'.$city.'" }).inject($("b-select__city"));'."\n";
	if($js) $objResponse->script($js);
	return $objResponse;
}

$xajax->processRequest();
?>
