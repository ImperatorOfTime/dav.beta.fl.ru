<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/events.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/atservices_model.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_helper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');

/**
 * ������ ������� ��������� ������ ������� �����
 */
class tservices_order_history extends atservices_model 
{
    
    private $TABLE = 'tservices_order_history';
    
    /**
     * Id ������ ��
     * 
     * @var int
     */
    private $order_id;
    
    /*
     * ������� ��������������
     */
    const TEXT_RESERVE = "������ ����� ���������� ������";
    const TEXT_NORESERVE = "������ ������";
    
    const MES_CREATE = "������ ����� \"%s\", ���� %s, ������ %s (%s).";
    const MES_UPD_PRICE = "������ ������: %s >> %s.";
    const MES_UPD_DAYS = "���� ���������� ������: %s >> %s.";
    const MES_UPD_RESERVE = "������ ������ ������: %s >> %s.";
    
    const MES_UPD_STATUS_ACCEPT = "����������� ���������� �����.";
    const MES_UPD_STATUS_CANCEL = "�������� ������� �����.";
    const MES_UPD_STATUS_DECLINE = "����������� ��������� �� ������.";
    
    const MES_RESERVE = "����� %s ��������������� (�������� �������� %s%).";//???

    const MES_ARB_START = "%s ��������� � ��������.";
    const MES_ARB_CANCEL = "�������� �������.";
    const MES_ARB_DESIDE = "������ ����� �������: %s.";
    const MES_ARB_DESIDE_FRL = "%s ��������� �����������";
    const MES_ARB_DESIDE_EMP = "%s ������� ���������";
    
    const MES_DOCUMENT = "�������� �������� \"%s\".";
    
    //const MES_DONE = "�������� ������ ������ � ���������� ������� 100% ������� �����������";
    const MES_FEEDBACK = "%s ������� %s ����� � ��������������.";
    const MES_CLOSE = "����� ������.";
    const MES_DONE = "����������� �������� � ����������� ������.";
    const MES_FIX = "�������� �������� �� ���������.";
    
    const MES_RESERVE_SUCCESS = "�������� ������� �������������� ����� %s.";
    const MES_RESERVE_INPORGRESS = "�������� ����� ������ �� �������������� �����.";
    const MES_RESERVE_ERR = "�������������� ����� ���������� �������� ��������������.";
    const MES_RESERVE_DONE = "�������� �������� ��������������, ����������: %s ��������� �����������.";
    
    const MES_RESERVE_PAYOUT_REQ = "����������� ����� ������ �� ������� �����%s.";
    const MES_RESERVE_PAYOUT = "����� %s ����������� �����������%s%s";
    const MES_RESERVE_PAYOUT_NDFL = ", ���� %s ������� � ���������� � ������ ��.";
    const MES_RESERVE_PAYOUT_ERR = "������� ����� ����������� �������� ��������������.";
    const MES_RESERVE_PAYBACK_REQ = "�������� ����� ������ �� ������� �����.";
    const MES_RESERVE_PAYBACK = "����� %s ����������� ���������.";
    const MES_RESERVE_PAYBACK_ERR = "������� ����� ��������� �������� �������������.";
    
    const MES_ADMIN_CHANGE_STATUS = "������ ������ �������.";
    
    
    
    public function __construct($order_id) 
    {
        $this->order_id = (int)$order_id;
        
        //��� ���������� �����
        Events::register('generateInvoice2', array($this, 'reservePriceInprogress'));
        //������ ��������� ������� ��� ��������� �����
        Events::register('generate_file', array($this, 'saveFileForEvents'));
    }
    
    

    
    /*
     * ���������� ������ ������� ��������� ���������� ������
     */
    public function getHistory() 
    {
        return $this->db()->rows("SELECT * FROM {$this->TABLE} WHERE order_id = ?i ORDER BY date DESC", $this->order_id);
    }
    
    
    
    /**
     * ��������� ������� ������
     * 
     * @param type $new_order
     * @param type $old_order
     */
    public function save($new_order, $old_order = null) 
    {
        if (!$old_order) 
        {
            $this->addEvent(sprintf(self::MES_CREATE, 
                    htmlspecialchars($new_order['title']), 
                    tservices_helper::days_format($new_order['order_days']), 
                    tservices_helper::cost_format($new_order['order_price'], true), 
                    (tservices_helper::isOrderReserve($new_order['pay_type']))?self::TEXT_RESERVE:self::TEXT_NORESERVE
            ));
        } 
        else 
        {
            if ($old_order['order_price'] != $new_order['order_price']) 
            {
                $this->addEvent(sprintf(self::MES_UPD_PRICE, 
                        tservices_helper::cost_format($old_order['order_price'], true), 
                        tservices_helper::cost_format($new_order['order_price'], true)
                ));
            }

            if ($old_order['order_days'] != $new_order['order_days']) 
            {
                $this->addEvent(sprintf(self::MES_UPD_DAYS, 
                        tservices_helper::days_format($old_order['order_days']), 
                        tservices_helper::days_format($new_order['order_days'])
                ));
            }
            
            if ($old_order['pay_type'] != $new_order['pay_type']) 
            {
                $is_reserve = tservices_helper::isOrderReserve($new_order['pay_type']);
                $str_from = ($is_reserve)?self::TEXT_NORESERVE:self::TEXT_RESERVE;
                $str_to = (!$is_reserve)?self::TEXT_NORESERVE:self::TEXT_RESERVE;
                $this->addEvent(sprintf(self::MES_UPD_RESERVE, $str_from, $str_to));
            }
        }
    }
    
    
    
    public function saveFeedback($is_emp, $fbtype) {
        $message = sprintf(self::MES_FEEDBACK, 
            ($is_emp?'��������':'�����������'), 
            ($fbtype>0?'�������������':'�������������')
        );
        $this->addEvent($message);
    }
    
    public function saveStatus($status) {
        switch ($status) {
            case TServiceOrderModel::STATUS_ACCEPT:
                $message = self::MES_UPD_STATUS_ACCEPT;
                break;
            
            case TServiceOrderModel::STATUS_CANCEL:
                $message = self::MES_UPD_STATUS_CANCEL;
                break;
            
            case TServiceOrderModel::STATUS_DECLINE:
                $message = self::MES_UPD_STATUS_DECLINE;
                break;
            
            case TServiceOrderModel::STATUS_FRLCLOSE:
                $message = self::MES_DONE;
                break;
            
            case TServiceOrderModel::STATUS_EMPCLOSE:
                $message = self::MES_CLOSE;
                break;
            
            case TServiceOrderModel::STATUS_FIX:
                $message = self::MES_FIX;
                break;
        }
        
        if($message) {
            $this->addEvent($message);
        }        
    }
    
    
    public function saveFile($fname) 
    {
        $message = sprintf(self::MES_DOCUMENT, $fname);
        $this->addEvent($message);
    }
    
    
    /**
     * ��� ��������� ������� ��������� �����
     * ��������� ��� ������� � �� ��������� � ������
     * ��� ���������������� ����� 
     * 
     * @param CFile $file
     */
    public function saveFileForEvents(CFile $file)
    {
        $this->saveFile($file->original_name);
    }
    
    
    /**
     * ���������� �� � 100% �������� �����������
     * 
     * @param type $price
     */
    public function reserveDone($price)
    {
        $message = sprintf(self::MES_RESERVE_DONE, tservices_helper::cost_format($price, true, false, false));
        $this->addEvent($message);
    }
    

    /**
     * �������� � ������� ��������� �������������� ����� ����������
     * 
     * @param type $price
     */
    public function reservePriceSuccess($price)
    {
        $message = sprintf(self::MES_RESERVE_SUCCESS, tservices_helper::cost_format($price, true, false, false));
        $this->addEvent($message);
    }

    /**
     * �������� � ������� ������� �� �������������� ������� ����������
     */
    public function reservePriceInprogress()
    {
        $this->addEvent(self::MES_RESERVE_INPORGRESS);
    }

    /**
     * �������� � ������� ������ � �������� ��������������
     */
    public function reservePriceErr()
    {
        $this->addEvent(self::MES_RESERVE_ERR);
    }

    /**
     * ��������� � ��������
     * 
     * @param type $is_emp
     */
    public function reserveArbitrageNew($is_emp)
    {
        $this->addEvent(sprintf(self::MES_ARB_START,($is_emp)?'��������':'�����������'));
    }

    /**
     * �������� �������
     */
    public function reserveArbitrageCancel()
    {
        $this->addEvent(self::MES_ARB_CANCEL);
    }

    /**
     * ������� �������
     * 
     * @param type $frl_price
     * @param type $emp_price
     */
    public function reserveArbitrageDecide($frl_price, $emp_price)
    {
        $frl_pay = ($frl_price > 0)?sprintf(self::MES_ARB_DESIDE_FRL, tservices_helper::cost_format($frl_price, true, false, false)):'';
        $emp_back = ($emp_price > 0)?sprintf(self::MES_ARB_DESIDE_EMP, tservices_helper::cost_format($emp_price, true, false, false)):'';
        $str = (empty($frl_pay) || empty($emp_back))?$frl_pay . $emp_back:$frl_pay . ', ' . $emp_back;
        $this->addEvent(sprintf(self::MES_ARB_DESIDE, $str));
    }

    /**
     * ������ �� �������
     */
    public function reservePayoutReq($type_text = null)
    {
        $this->addEvent(sprintf(self::MES_RESERVE_PAYOUT_REQ,($type_text)?' �� ' . $type_text:''));
    }

    /**
     * ������ ��� �������
     */
    public function reservePayoutErr()
    {
        $this->addEvent(self::MES_RESERVE_PAYOUT_ERR);
    }
    

    /**
     * ����� ��������� �����������
     * 
     * @param type $price
     * @param type $ndfl
     */
    public function reservePayout($price, $ndfl = 0, $type_text = null)
    {
        $type_text = ($type_text)?" �� {$type_text}":'';
        $str = ($ndfl > 0)?sprintf(self::MES_RESERVE_PAYOUT_NDFL, 
                tservices_helper::cost_format($ndfl, true, false, false)):'.';
        $this->addEvent(sprintf(self::MES_RESERVE_PAYOUT,
                tservices_helper::cost_format($price, true, false, false),
                $type_text,
                $str));
    }


    /**
     * ������ �� ������� �����
     */
    public function reservePaybackReq()
    {
        $this->addEvent(self::MES_RESERVE_PAYBACK_REQ);
    }

    
    /**
     * ������ ��� ��������
     */
    public function reservePaybackErr()
    {
        $this->addEvent(self::MES_RESERVE_PAYBACK_ERR);
    }
    
    
    /**
     * ����� ���������� ���������
     * 
     * @param type $price
     */
    public function reservePayback($price)
    {
        $this->addEvent(sprintf(self::MES_RESERVE_PAYBACK,
                tservices_helper::cost_format($price, true, false, false)));
    }
    

    /**
     * ��������� � ����� ������� �������
     */
    public function adminChangeStatus()
    {
        $this->addEvent(self::MES_ADMIN_CHANGE_STATUS);
    }

    
    /**
     * �������� ��������� � �������
     * 
     * @param string $message
     */
    private function addEvent($message) 
    {
        $this->db()->insert($this->TABLE, array(
            'order_id' => $this->order_id,
            'date' => 'NOW()',
            'description' => $message
        ));
    }
}


/*
 * � ������� ��������� ����������� ��������� ����������/���������� ��������, ��������� �����, ����, ��������, ������������� ��������.
 */

/*
 * ������� ��������� ������
 * 
30.10.2014 - �������������� - ����� 100 000 ������ ��������������� (�������� �������� 10%)
30.10.2014 - ��������� � �������� - ��������/����������� ��������� � ��������
30.10.2014 - ������ ��������� - �������� �������
30.10.2014 - ��������� ������� - ������ ����� �������: ���������� �������, 50% �����������, 50% ��������� / ������ ����� �������: ������� 100% ������� ��������� / ������ ����� �������: ������� 100% ������� �����������
30.10.2014 - ������� ���� - ����� 50 000 ������ ��������� ����������� (�������� �������� 10%) / ����� 50 000 ������ ���������� ���������
30.10.2014 - �������� ���������� - �������� �������� "�������� ���������"
30.10.2014 - �������� ������ - �������� ������ ������ � ���������� ������� 100% ������� �����������
 */