<?php

/*
 * ���������:
 * 0 0 * * 1,3,5
 * 
 * �������� ��:
 * 
 * - ���������� �� �������
 * - ������������ ��������
 * - ������������� ��������
 * 
 * �������� ����������� �� ��������� (3���)
 * ������ ������ �� 3 ��� � �������
 * ��. ������ � ����� � ������� projects_spam_interval
 * ���������� ��� ���� ��� �����, ������ ��� projects_spam_is_send
 * 
 */

//ini_set('display_errors',1);
//error_reporting(E_ALL ^ E_NOTICE);

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    //@todo: ������� ������ '' ������������� ��������� doc_root �������� '/../' 
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(dirname(__FILE__) . ''), '/');
} 

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/multi_log.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");

$log = new log('hourly_projects_newsletter/'.SERVER.'-%d%m%Y[%H].log', 'w');
$log->writeln('------------ BEGIN hourly (start time: ' . date('d.m.Y H:i:s') . ') -----');


//if((int)date('H') == 1) {
    
    //$mail = new smail();
    //$log->TRACE();

//}

//------------------------------------------------------------------------------

$mail = new smail();

//------------------------------------------------------------------------------

//���������� �� ������� (���� ����� �������)
$log->TRACE( $mail->sendFrlOffer() );

//------------------------------------------------------------------------------

//������������ ��������
$log->TRACE( $mail->sendFrlProjectsExec() );

//------------------------------------------------------------------------------

//������������� ��������
$log->TRACE( $mail->sendEmpPrjFeedback() );

//------------------------------------------------------------------------------



$log->writeln('------------ END hourly    (total time: ' . $log->getTotalTime() . ') ---------------');