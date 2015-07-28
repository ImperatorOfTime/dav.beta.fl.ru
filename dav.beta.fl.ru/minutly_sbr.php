<?php

ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

define('IS_OPENED', true); 

if (!$_SERVER['DOCUMENT_ROOT']) {
    $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__);
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
//require_once("classes/log.php");

//$log = new log('minutly/minutly-sbr-'.SERVER.'-%d%m%Y[%H].log', 'w');

require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/pskb.php');

/**
 * �������� ������� ����� ������������ � ��������
 */
pskb::checkStatus(null, $in, $out);

/**
 * �������� ������������ � ������� trans (������������ �����)
 */
if (date('i') % 5 == 0) {
    pskb::checkStagePayoputForSuperCheck(null, $in, $out);
}

if(pskb::PSKB_SUPERCHECK && date('i') % 2 == 0 ) { // �������� �� ��� � ��� ������, ������ ������ ���. ����� �� ���� ����������� �� 5 ����� 
    pskb::checkStagePayouts(null, $in, $out);
} elseif(!pskb::PSKB_SUPERCHECK) { // ���� �������� �������� �� �������
    pskb::checkStagePayouts(null, $in, $out);
}

if (date('i') % 2 == 0) {
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_adm.php');
    sbr_adm::processInvoiceData();
}