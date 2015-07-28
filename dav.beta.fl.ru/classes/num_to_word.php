<?php 
/**
 * ���� ������������ ��� ���� ����� �������� ����� � ��������� ���� (��� ������ ���������� ���������)
 */
$GLOBALS['_1_2']=array(1=>
"���� ", 
"��� ");  
$GLOBALS['_1_19']=array(1=>
"���� ",
"��� ",
"��� ",
"������ ",
"���� ",
"����� ",
"���� ",
"������ ",
"������ ",
"������ ",
"����������� ",
"���������� ",
"���������� ",
"������������ ",
"���������� ",
"����������� ",
"���������� ",
"������������ ",
"������������ ");

$GLOBALS['des']=array(2=>
"�������� ",
"�������� ",
"����� ",
"��������� ",
"���������� ",
"��������� ",
"����������� ",
"��������� ");

$GLOBALS['hang']=array(1=>
"��� ",
"������ ",
"������ ",
"��������� ",
"������� ",
"�������� ",
"������� ",
"��������� ",
"��������� ");

$GLOBALS['namerub']=array(1=>
"����� ���������� ��������� ",
"����� ���������� ��������� ",
"������ ���������� ��������� ");

$GLOBALS['nametho']=array(1=>
"������ ",
"������ ",
"����� ");

$GLOBALS['namemil']=array(1=>
"������� ",
"�������� ",
"��������� ");

$GLOBALS['namemrd']=array(1=>
"�������� ",
"��������� ",
"���������� ");

$GLOBALS['kopeek']=array(1=>
"������� ",
"������� ",
"������ ");

$GLOBALS['num_name'] = array(
    1 => '������',
    2 => '����',
    3 => '����',
    4 => '�������',
    5 => '����',
    6 => '�����',
    7 => '����',
    8 => '������',
    9 => '������',
    10 => '������',
    11 => '����������',
    12 => '����������',
    13 => '����������',
    14 => '������������',
    15 => '����������',
    16 => '�����������',
    17 => '����������',
    18 => '������������',
    19 => '������������',
    20 => '��������'
);
/**
 * ������� ����������� ����� � ������� �����
 *
 * @todo ��� ���� ���, ��� ��� � ����?
 * 
 * @global $_l_2    ������ ������ �� �������� �����
 * @global $_l_19   ������ ������ �� �������� �����
 * @global $des     ������ ������ �� �������� �����
 * @global $hang    ������ ������ �� �������� �����
 * @global $namerub ������ ������ �� ��������� ������
 * @global $nametho ������ ������ �� �������� �����
 * @global $namemil ������ ������ �� �������� �����
 * @global $namemrd ������ ������ �� �������� �����  
 * 
 * @param integer $i     �����
 * @param string  $words ���������� ���������������� �� �����  
 * @param integer $fem   ������ ��� ���������. ������: 1 - ������, 2 - ������, 3 - �����
 * @param integer $f     ������ ��� ���� ��� "����" � "���".
 */
function semantic($i,&$words,&$fem,$f){  
global $_1_2, $_1_19, $des, $hang, $namerub, $nametho, $namemil, $namemrd;  
$words="";  
if($i >= 100){  
$jkl = intval($i / 100);  
$words.=$hang[$jkl];  
$i%=100;  
}  
if($i >= 20){  
$jkl = intval($i / 10);  
$words.=$des[$jkl];  
$i%=10;
}  
switch($i){  
case 1: $fem=1; break;  
case 2:  
case 3:  
case 4: $fem=2; break;  
default: $fem=3; break;  
}  
if( $i ){  
if( $i < 3 && $f > 0 ){  
if ( $f >= 2 ) {  
$words.=$_1_19[$i];  
}  
else {  
$words.=$_1_2[$i];  
}  
}  
else {  
$words.=$_1_19[$i];  
}  
}  
}  

/**
 * ������� �������� ����� � ��������� �������� 
 * 
 * @example num2str(1234) -> "������ ������ �������� ������"
 *
 * @global $_l_2    ������ ������ �� �������� �����
 * @global $_l_19   ������ ������ �� �������� �����
 * @global $des     ������ ������ �� �������� �����
 * @global $hang    ������ ������ �� �������� �����
 * @global $namerub ������ ������ �� ��������� ������ (�������� �����, �������)
 * @global $nametho ������ ������ �� �������� �����
 * @global $namemil ������ ������ �� �������� �����
 * @global $namemrd ������ ������ �� �������� ����� 
 * @global $kopeek  ������ ������ �� ��������� ������ ������ (�������� ������, ������)
 * 
 * @param integer $L ������������� �����
 * @return string ��������������
 */
function num2str($L, $up = false){  
global $_1_2, $_1_19, $des, $hang, $namerub, $nametho, $namemil, $namemrd, $kopeek;  

$s=" ";  
$s1=" ";  
$s2=" ";
$L = round($L, 2);
$kop = 100*(string)round($L-(int)$L, 2);
$L=intval($L);  
if($L>=1000000000){  
$many=0;  
semantic(intval($L / 1000000000),$s1,$many,3);  
$s.=$s1.$namemrd[$many];  
$L%=1000000000;  
}  

if($L >= 1000000){  
$many=0;  
semantic(intval($L / 1000000),$s1,$many,2);  
$s.=$s1.$namemil[$many];  
$L%=1000000;  
if($L==0){  
$s.="������ ���������� ���������";  
}  
}  

if($L >= 1000){  
$many=0;  
semantic(intval($L / 1000),$s1,$many,1);  
$s.=$s1.$nametho[$many];  
$L%=1000;  
if($L==0){  
$s.="������ ���������� ���������";  
}  
}  

if($L != 0){  
$many=0;  
semantic($L,$s1,$many,0);  
$s.=$s1.$namerub[$many];  
}  

if($kop > 0){  
$many=0;  
semantic($kop,$s1,$many,1);  
$s.=$s1.$kopeek[$many];  
}  
else {  
$s.=" 00 ������";  
}  
if($up) {
    $s = trim($s);
    $s{0} = mb_strtoupper($s{0});
}
return trim($s);  
}  

/**
 * ����������� ����� � ���� � ������ � ��������
 * 
 * @param  float $sum �����
 * @return string
 */
function num2strL($sum) {
   $sum = round($sum,2);
   $rub = (int)$sum;
   $pad = 100*round($sum-$rub, 2);
   $kop = str_pad($pad, 2, '0', $pad < 10 ? STR_PAD_LEFT : STR_PAD_RIGHT);
   return $rub.ending($rub, ' �����', ' �����', ' ������').' '.$kop.ending($kop, ' �������', ' �������', ' ������');
}

/**
 * ����������� ����� � ���� � ������ � �������� � ������� �����-�������
 * 
 * @param  float $sum �����
 * @return string
 */
function num2strD($sum) {
   $sum = round($sum,2);
   $rub = (int)$sum;
   $pad = 100*round($sum-$rub, 2);
   $kop = str_pad($pad, 2, '0', $pad < 10 ? STR_PAD_LEFT : STR_PAD_RIGHT);
   return $rub.'-'.$kop;
}

/**
 * ����������� ����� � ���� � ������ � ��������
 * 
 * @param  float $L �����
 * @return string
 */
function num2strEx($L) {
    include_once dirname(__FILE__).'/sbr.php';
    global $_1_2, $_1_19, $des, $hang, $namerub, $nametho, $namemil, $namemrd, $kopeek;
    
    $L = round($L,2);
    $source = $L;
    $kop = 100*(string)round($L-(int)$L, 2);
    $L = intval($L);
    
    $s = " ";
    $s1 = " ";
    $s2 = " ";
    if($L == 0){
        $s.= '���� ������ ���������� ��������� ';
    }
    
    if ($L >= 1000000000) {
        $many = 0;
        semantic(intval($L / 1000000000), $s1, $many, 3);
        $s.=$s1 . $namemrd[$many];
        $L%=1000000000;
    }

    if ($L >= 1000000) {
        $many = 0;
        semantic(intval($L / 1000000), $s1, $many, 2);
        $s.=$s1 . $namemil[$many];
        $L%=1000000;
        if ($L == 0) {
            $s = rtrim($s)." ������ ���������� ��������� ";
        }
    }

    if ($L >= 1000) {
        $many = 0;
        semantic(intval($L / 1000), $s1, $many, 1);
        $s.=$s1 . $nametho[$many];
        $L%=1000;
        if ($L == 0) {
            $s = rtrim($s)." ������ ���������� ��������� ";
        }
    }

    if ($L != 0) {
        $many = 0;
        semantic($L, $s1, $many, 0);
        $s .= rtrim($s1). ' ' . trim($namerub[$many]).' ';
    }
    
    if ($kop > 0) {
        $s .= str_pad($kop, 2, '0', STR_PAD_LEFT).ending($kop, ' �������', ' �������', ' ������');
    } else {
        $s .= "00 ������";
    }
    setlocale(LC_ALL, "ru_RU.CP1251"); 
    $s = ucfirst(trim($s));
    setlocale(LC_ALL, "en_US.UTF-8"); 
    return trim(sbr_meta::view_cost((float)$source, NULL, false, ',', ' ').' ('.trim($s).')');
}

function numStringName($num) {
    global $num_name;
    
    return $num_name[$num];
}
?>