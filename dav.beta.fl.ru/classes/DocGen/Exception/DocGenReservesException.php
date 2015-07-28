<?php

require_once('DocGenException.php');

class DocGenReservesException extends DocGenException
{
    const BANK_INVOICE_ERROR_MSG       = '����� �� ������';
    
    const ACT_COMPLETED_FRL_ERROR_MSG  = '���� � ���������� ������ ������������';
    const ACT_SERVICE_EMP_ERROR_MSG    = 'a��a �� �������� ����� ���������';
    const AGENT_REPORT_ERROR_MSG       = '������ ������ �� ��������';
    
    const RESERVE_OFFER_CONTRACT_ERROR_MSG  = '��������� ��������';
    const RESERVE_OFFER_AGREEMENT_ERROR_MSG = '��������� ����������';
    
    const LETTER_FRL_ERROR_MSG       = '��������������� ������ �����������';
    
    const ARBITRAGE_REPORT_ERROR_MSG    = '������ �� ����������� ������������';
    
    const RESERVE_FACTURA_ERROR_MSG     = '����-�������';
    
    const RESERVE_SPECIFICATION_ERROR_MSG     = '������������ �������';
    
    /**
     * @todo: � ���� ������� ����� � ������ ������ ��� ������������ ���������: "��� ���".?
     * ����� ��� ����� �������� ����������� � �������� ��� ������ ������ � � ������ ����������
     * ������ �������� ���������.
     */
}