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

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/YandexMoney3Tmp/YandexMoney3.php');


//------------------------------------------------------------------------------


$results = array();
//$profiler = new profiler();


//------------------------------------------------------------------------------


/*<makeDepositionRequest agentId="200225"
                       clientOrderId="272517"
                       requestDT="2013-04-12T00:01:54.000Z"
                       dstAccount="25700130535186"
                       amount="249.00"
                       currency="643"
                       contract="">
         <paymentParams>                         <skr_destinationCardSynonim>79052075556</skr_destinationCardSynonim>
                  <pdr_firstName>��������</pdr_firstName>
                  <pof_offerAccepted>1</pof_offerAccepted> 
                  <pdr_secondName>������������</pdr_secondName>
                  <pdr_lastName>����������</pdr_lastName>
                  <cps_phoneNumber>79052075556</cps_phoneNumber>
                  <pdr_docType>21</pdr_docType>
                  <pdr_docNum>4002109067</pdr_docNum>
                  <pdr_postcode>194044</pdr_postcode>
                  <pdr_country>�����-���������</pdr_country>
                  <pdr_city></pdr_City>
                  <pdr_address>������� ��, ��, �.12</pdr_address>
         </paymentParams>
</makeDepositionRequest>

 */




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
    /*
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
    )*/
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


//use YandexMoney3\YandexMoney3;


$ym = new YandexMoney3();

$data = $ym->request('testDeposition', $params);


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