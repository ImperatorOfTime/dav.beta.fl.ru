<?php


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/profiler.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reqv.php');
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/YandexMoney3/Utils/Array2XML.php');
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/YandexMoney3/YandexMoney3.php');

//------------------------------------------------------------------------------


$results = array();
//$profiler = new profiler();


//------------------------------------------------------------------------------




//use YandexMoney3\Request\DepositionRequest;
//use YandexMoney3\Request\BalanceRequest;
//use YandexMoney3\YandexMoney3;

use YandexMoney3\Utils\Array2XML;


//------------------------------------------------------------------------------



$params = array(
    '@attributes' => array(
        'agentId' => 200385,
        'clientOrderId' => 272517,
        'requestDT' => date('c'),//'2013-04-12T00:01:54.000Z',
        'dstAccount' => '25700130535186',
        'amount' => '249.00',
        'currency' => 10643,
        'contract'=> ''
    )
    ,
    'identification' => array(
        '@attributes' => array(
            'docType' => "21",
            'docNumber' => "4004 123987",
            'issueDate' => "1976-01-01",
            'authorityName' => "25 �/� ����������� �-�� �. �����-����������",
            'authorityCode' => "780-025",
            'residence' => "�.�����-���������, 3-� ����� ����������, �.25, ��.12"
        )
    )
    ,
    'paymentParams' => array(
        'skr_destinationCardSynonim' => '79052075556',
        'pdr_firstName' => '��������',
        'pof_offerAccepted' => 1,
        'pdr_secondName' => '������������',
        'pdr_lastName' => '����������',
        'cps_phoneNumber' => 79052075556,
        'pdr_docType' => 21,
        'pdr_docNum' => 4002109067,
        'pdr_postcode' => 194044,
        'pdr_country' => '�����-���������',
        'pdr_city' => '',
        'pdr_address' => '������� ��, ��, �.12'
    )
);

$converter = new Array2XML();
$converter->setConvertFromEncoding('windows-1251');
$converter->setTopNodeName('test');
$converter->importArray($params);
$data = $converter->saveXml();

print_r($data);
exit;



//------------------------------------------------------------------------------

//$profiler->start('fill_frl_mem');

//------------------------------------------------------------------------------




//------------------------------------------------------------------------------

//$profiler->stop('fill_frl_mem');

//------------------------------------------------------------------------------


//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;