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
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');


/**
��0010271
��#0010324
��#0010554
��#0010454
��0010359
��0010401
��#0010900
� ���� ������� ����� ����������� ������ � ����� "����� ������� ���������������
 */



$order_ids = array(
10271,
10324,
10554,
10454,
10359,
10401,
10900    
);


