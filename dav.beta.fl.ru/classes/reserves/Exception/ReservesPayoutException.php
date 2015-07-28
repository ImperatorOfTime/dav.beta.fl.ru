<?php

require_once(__DIR__ . '/ReservesPayException.php');


class ReservesPayoutException extends ReservesPayException
{
    //����������� ��������� �� ������� �������� � �������
    const RQST_EMPTY        = '������ ������ ����� �� �������';
    const WRONG_SUM         = '������������ ����� �������';
    const REQV_FAIL         = '������ ������� "%s" �� �������� ��� ������������ uid = %s';
    const INS_FAIL          = '�� ������� �������� ������ �� �������';
    const CARD_SYNONIM_FAIL = '�� ������� �������� ������� ������ �����';
    const LAST_PAYED_FAIL   = 'Payout Id = %s ������ ������ ������ %s%s';
    const REQV_INVALID      = '��������� ����������� ��� ����������';
    const RQST_ACTIVE       = '��� ���� ������ �� ������� ������ ��������';
    const TYPE_INVALID      = '���������������� ������ �������: "%s"';
    const PHONE_FAIL        = '��������� ����� ����� ��������';
    
    public function __construct() 
    {
        $args = func_get_args();
        $cnt = count($args);
        
        if ($cnt > 0) {
            $message = current($args);
            if ($cnt > 1) {
                unset($args[0]);
                $message = (count($args))?vsprintf($message, $args):$message;
            }
            
            parent::__construct($message);
        }
    }
}