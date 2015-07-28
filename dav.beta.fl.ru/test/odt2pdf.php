<?php

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../'), '/');
} 

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/num_to_word.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/odt2pdf.php");

$sum_frl = 10000;
$sum_emp = 5000;
$work_cost = 15000;

/*
$descr_tz = '
               <text:h text:outline-level="1"> A Table (Heading 1)</text:h>
               <table:table table:name="Table1">
                 <table:table-column table:number-columns-repeated="3"/>
                 <table:table-row>
                   <table:table-cell office:value-type="string">
                     <text:p>Website</text:p>
                   </table:table-cell>
                   <table:table-cell office:value-type="string">
                     <text:p>Description</text:p>
                   </table:table-cell>
                   <table:table-cell office:value-type="string">
                     <text:p>URL</text:p>
                   </table:table-cell>
                 </table:table-row>
                 <table:table-row>
                   <table:table-cell office:value-type="string">
                     <text:p>Flickr</text:p>
                   </table:table-cell>
                   <table:table-cell office:value-type="string">
                     <text:p>A social photo sharing site</text:p>
                   </table:table-cell>
                   <table:table-cell office:value-type="string">
                     <text:p>
                       <text:a xlink:type="simple" xlink:href="http://www.flickr.com/"
                         >http://www.flickr.com</text:a>
                     </text:p>
                   </table:table-cell>
                 </table:table-row>
                 <table:table-row>
                   <table:table-cell office:value-type="string">
                     <text:p>Google Maps</text:p>
                   </table:table-cell>
                   <table:table-cell office:value-type="string">
                     <text:p>An online map</text:p>
                   </table:table-cell>
                   <table:table-cell office:value-type="string">
                     <text:p>
                       <text:a xlink:type="simple" xlink:href="http://maps.google.com/"
                         >http://maps.google.com</text:a>
                     </text:p>
                   </table:table-cell>
                 </table:table-row>
               </table:table>
';
*/

$descr_tz = '
               111
               <text:h text:outline-level="1"> A Table (Heading 1)</text:h>
               <text:p>222</text:p>
               <text:a xlink:type="simple" xlink:href="http://www.flickr.com/services/api/">http://www.flickr.com/services/api/</text:a>
';


$act_new = array(
    '$sbr_num'      => "���-109-�/�-1",
    '$date_act'     =>  "12.09.2012",
    '$date_sbr'     => "12.09.2012",
    '$efio'         => "�������� ���������� ��������",
    '$ffio'         => "��������� ��������� �����",
    '$sum_frl'      => num2strEx($sum_frl, '������ ���������� ���������'),
    '$sum_emp'      => num2strEx($sum_emp, '������ ���������� ���������'),
    '$tz_descr'     => $descr_tz,
    //'<text:p text:style-name="P9">{$tz_descr}</text:p>' => $descr_tz,
    '$work_time'    => '3 ��� �� ����',
    '$work_type'    => '������������� ��������� ������',
    '$work_cost'    => num2strEx($work_cost, '������ ���������� ���������'),
    '$is_arb_emp'   => false,
    '$is_arb_frl'   => false,
    '$user_arb'     => '��������',
    '$result_arb'   => '��������� ��������� ����� ������ ��� �� ������� ����� �� �������'
);


$t = new odt2pdf('arb_frl_soglashenie.odt');
$t->convert($act_new);
$content = $t->Output(NULL, 'S');

$file = new CFile();
//$file->table = 'file_sbr';
$file->path = 'uploader/';
$file->name = basename($file->secure_tmpname($file->path,'.pdf'));
$file->size = strlen($content);
$file->putContent($file->path . $file->name, $content);



//echo strlen($t->output);

?>