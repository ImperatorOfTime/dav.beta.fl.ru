<?php
ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_categories.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_tags.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices.php");

$tservices_categories = new tservices_categories();
$result = $tservices_categories->getTitleAndSubtitle(10);
assert('$result == array("spec_title" => "���-����������������", "group_title" => "���������� ������")');


$result = $tservices_categories->getCategoryParentId(10);
assert('$result == 7');


$tservices_tags = new tservices_tags();
$result = $tservices_tags->getsByTServiceId(39);
assert('$result == array( 0 => "������", 1 => "�����������", 2 => "�������������", 3 => "��������", 4 => "����������", 5 => "���������������" )');


$tservices = new tservices(2);
$result = $tservices->isExistFeedbacks(27);
assert('$result == 2');


$result = $tservices->getTotalCount();
assert('$result == array("plus" => 2, "minus" => 2)');


$result = $tservices->getNearBy('next',38);
$result = $tservices->getNearBy('prev',38);


$result = $tservices->getCountCompleteSbrServices();
assert('$result == 5');


$result = $tservices->deleteById(750);
assert('$result == TRUE');

$result = $tservices->isExists(749);


$result = $tservices->getFeedbacks(36);


$result = $tservices->getCard(36);


$result = $tservices->setPage(5)->getShortList();


$result = $tservices->initProps();
assert('$tservices->user_id == 2');

$result = $tservices->fieldsPropsToArray();




$stop_words    = new stop_words();
print_r($stop_words);




//print_r($cfile);

//var_dump($result);
exit;