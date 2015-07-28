<?php

require_once $_SERVER['DOCUMENT_ROOT']."/classes/sms_gate_a1.php";
require_once $_SERVER['DOCUMENT_ROOT']."/classes/sbr.php";

/**
 * ��� ����������� �� �������������� � ���������
 */
class ReservesSms extends sms_gate_a1
{
    /**
     * �������������� �������
     */
    const STATUS_NEW_ARBITRAGE_FRL = 9;
    const STATUS_NEW_ARBITRAGE_EMP = 10;
    const STATUS_CANCEL_ARBITRAGE_EMP = 12;
    const STATUS_CANCEL_ARBITRAGE_FRL = 13;
    const STATUS_APPLY_ARBITRAGE_EMP = 14;
    const STATUS_APPLY_ARBITRAGE_FRL = 15;
    
    const STATUS_RESERVE_DONE_EMP = 16;
    const STATUS_RESERVE_DONE_FRL = 17;
    
    
    protected $PAY_EMP = '%g ���. ��������� ����������� � %g ���. ������� ���';
    protected $PAY_FRL = '%g ���. ��������� ��� � %g ���. ������� ���������';
    protected $PAY_ALL_EMP = '��� ����� ������� ��������� �����������';
    protected $PAY_ALL_FRL = '��� ����� ������� ��������� ���';
    protected $BACK_ALL_EMP = '��� ����� ������� ������� ���';
    protected $BACK_ALL_FRL = '��� ����� ������� ������� ���������';
    
    /**
     * ��������� ��������� ������ ��
     * 
     * @var type 
     */
    public $text_templates = array(
        //self::STATUS_NEW_ARBITRAGE_FRL => '����� #%d ������� ���������� � ��������. � ��������� ����� ������ ���������� �������� �� ������ � ������� ������� � �������, �������� ��� ���������� ����� �������.',
        //self::STATUS_NEW_ARBITRAGE_EMP => '����� #%d ������� ������������ � ��������. � ��������� ����� ������ ���������� �������� �� ������ � ������� ������� � �������, �������� ��� ���������� ����� �������.',
        //self::STATUS_CANCEL_ARBITRAGE_EMP => '�������� �� ������ #%d �������, ����������� ��������� ���������� ������.',
        //self::STATUS_CANCEL_ARBITRAGE_FRL => '�������� �� ������ #%d �������, �� ������ ���������� ���������� ������.',
        //self::STATUS_APPLY_ARBITRAGE_EMP => '�� ������ #%d �������� �������� �������: %s.',
        //self::STATUS_APPLY_ARBITRAGE_FRL => '�� ������ #%d �������� �������� �������: %s.',
        
        //self::STATUS_RESERVE_DONE_EMP => '����� %s ���. �� ������ #%d ���������������, ����������� ����� ���������� ������.',
        //self::STATUS_RESERVE_DONE_FRL => '����� %s ���. �� ������ #%d ���������������, �� ������ ������ ���������� ������.'
    );

    /**
     * ���������� �� ����?
     * 
     * @return type
     */
    public function isPhone()
    {
        return !empty($this->_msisdn);
    }

    
    
    /**
     * ��������� ��� �� ������� ���������
     * 
     * @param int $status
     * @param int $num
     * @return boolean
     */
    public function sendByStatus()
    {
        $args = func_get_args();
        $cnt = count($args);
        if(!$cnt) return FALSE;
        
        $status = $args[0];
        unset($args[0]);
        
        if (!isset($this->text_templates[$status]) || !$this->isPhone())  {
            
            return FALSE;
        }
        
        $message = vsprintf($this->text_templates[$status], $args);
        return $this->sendSMS($message);
    }
    
    /**
     * ��������� ��� �� ��������� ��������� c ����������� � ��������
     * 
     * @param int $status
     * @param int $num
     * @return boolean
     */
    public function sendByStatusAndPrice($status, $pricePay, $priceBack, $id)
    {
        if (!isset($this->text_templates[$status]) ||
           !in_array($status, array(self::STATUS_APPLY_ARBITRAGE_EMP, self::STATUS_APPLY_ARBITRAGE_FRL)) || 
           !$this->isPhone())  {
            
            return FALSE;
        }
           
        $payBoth = $pricePay && $priceBack;
        $priceTemplateCode = $payBoth ? 'PAY' : ($priceBack ? 'BACK_ALL' : 'PAY_ALL');        
        $priceTemplateCode .= '_' . ($status == self::STATUS_APPLY_ARBITRAGE_EMP ? 'EMP' : 'FRL');
        $priceTemplate = $this->{$priceTemplateCode};
        $priceText = $payBoth ? sprintf($priceTemplate, $pricePay, $priceBack) : $priceTemplate;
        
        $message = sprintf($this->text_templates[$status], $id, $priceText);
        return $this->sendSMS($message);
    }

    



    /**
     * ������� ���� ����
     * @return TServiceModel
     */
    public static function model($uid) 
    {
        $phone = '';
        $reqv = sbr_meta::getUserReqvs($uid);
        
        if($reqv)
        {
            $ureqv = $reqv[$reqv['form_type']];
            $phone = $ureqv['mob_phone'];
        }

        $class = get_called_class();
        return new $class($phone);
    }
}