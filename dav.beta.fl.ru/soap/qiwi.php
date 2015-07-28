<?php
/**
 * �� ���� ������ �������� ����������� �� QIWI ��������.
 * SoapServer ������ �������� SOAP-������, ��������� �������� ����� login, password, txn, status,
 * �������� �� � ������ ������ Param � �������� ������� updateBill ������� ������ TestServer.
 *
 * ������ ��������� ��������� ����������� ������ ���� � updateBill.
 */

require_once($_SERVER['DOCUMENT_ROOT']."/classes/qiwi_soap.php");

$soap = new SoapServer('IShopClientWS.wsdl', array('classmap' => array('tns:updateBill' => 'Param', 'tns:updateBillResponse' => 'Response')));

$soap->setClass('QiwiServer');
$soap->handle();

class Response {
    public $updateBillResult;
}

class Param {
    public $login;
    public $password;
    public $txn;      
    public $status;
}

class QiwiServer 
{
    function updateBill($param) {
    	// � ����������� �� ������� ����� $param->status ������ ������ ������ � ��������
    	if ($param->status = 60) {
    		// ����� �������
    		// ����� ����� �� ������ ����� ($param->txn), �������� ��� ����������
    	} else if ($param->status > 100) {
    		// ����� �� ������� (������� �������������, ������������ ������� �� ������� � �.�.)
    		// ����� ����� �� ������ ����� ($param->txn), �������� ��� ������������
    	} else if ($param->status >= 50 && $param->status < 60) {
    		// ���� � �������� ����������
    	} else {
    		// ����������� ������ ������
    	}

    	// ��������� ����� �� �����������
    	// ���� ��� �������� �� ���������� ������� ������ � �������� ������ �������, �������� ����� 0
    	// $temp->updateBillResult = 0
    	// ���� ��������� ��������� ������ (��������, ������������� ��), �������� ��������� �����
    	// � ���� ������ QIWI ������ ����� ������������ �������� ��������� ����������� ���� �� ������� ��� 0
    	// ��� �� ������� 24 ����
    	$temp = new Response();
    	$temp->updateBillResult = 0;
    	return $temp;
    }
}
?>
