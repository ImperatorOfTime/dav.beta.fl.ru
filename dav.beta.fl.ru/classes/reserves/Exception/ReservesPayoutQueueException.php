<?php

require_once(__DIR__ . '/ReservesPayException.php');


class ReservesPayoutQueueException extends ReservesPayException
{
    const NOTFOUND                  = '������ �� ������� ������� �� ������.';
    const PAYED                     = '������ ��� ��� �������� �����.';
    const CANT_CHANGE_SUBSTATUS     = '�� ������� ������� ������ �������.';
}