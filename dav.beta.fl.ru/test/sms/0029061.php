<?php

require_once $_SERVER['DOCUMENT_ROOT']."/classes/sms_gate_a1.php";

if (isset($_GET['phone'])) {
    $phone = (int)$_GET['phone'];

    $sms_gate = new sms_gate_a1($phone);

    echo $sms_gate->sendSMS('�������� ���������');
} else {
    echo '�� ������ ����� ��������. GET-�������� phone';
}