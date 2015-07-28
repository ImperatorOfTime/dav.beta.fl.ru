<?php

require_once(__DIR__ . '/ReservesPayException.php');


class ReservesPaybackException extends ReservesPayException
{
    const INSERT_FAIL_MSG       = '�� ������� �������� ������ �� ������� �������.';
    const ALREADY_PAYBACK_MSG   = '�������� ������� ��� ���� ����������.';
    const PAYBACK_INPROGRESS    = '������ � �������� �������� �������.';
    const PAYBACK_NOTFOUND      = '������ �� ������� ������� �� ������.';
    const UNDEFINED_STATUS      = '�� ������ �������� ������� ��� ������� �� ��������� ������.';
    const CANT_CHANGE_SUBSTATUS = '�� ������ �������� ������� �� ������� ������� ��������� �������.';
}