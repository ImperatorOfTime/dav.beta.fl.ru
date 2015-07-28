<?php

class BillPaybackException extends Exception
{
    const INSERT_FAIL_MSG       = '�� ������� �������� ������ �� ������� �������.';
    const ALREADY_PAYBACK_MSG   = '�������� ������� ��� ���� ����������.';
    const PAYBACK_INPROGRESS    = '������ � �������� �������� �������.';
    const PAYBACK_NOTFOUND      = '������ �� ������� ������� �� ������.';
    const UNDEFINED_STATUS      = '�� ������ �������� ������� ��� ������� �� ��������� ������.';
    const REQUEST_LIMIT         = '�������� ����� � ����� 999 ��������.';
    const API_CRITICAL_FAIL     = '���������� ��������� ������. ��� ������ API: %d.';
    
    protected $repeat = false;

    public function __construct() 
    {
        $args = func_get_args();
        $cnt = count($args);
        
        if($cnt > 0)
        {
            $message = current($args);
            if($cnt > 1) 
            {
                $this->repeat = (end($args) === true);
                unset($args[$cnt-1],$args[0]);
                $message = (count($args))?vsprintf($message, $args):$message;
            }
            
            parent::__construct($message);
        }
    }
    
    
    public function isRepeat()
    {
        return $this->repeat;
    }
    
}