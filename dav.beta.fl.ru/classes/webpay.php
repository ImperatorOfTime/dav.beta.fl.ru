<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/onlinedengi_cards.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/account.php';
/**
 * ����� ��� ���������� ����� ����� ���������� ����
 * @see http://dengionline.com/
 * 
 */
class webpay {

    /**
     * ��� ������� �����
     */
    const PAYMENT_SYS_CODE = 13;
    
    /**
     * ��� ������. ������������ ������.
     */
    const ERR_DATA   = 1;
    /**
     * ��� ������. �� �������� ��������� ����.
     */
    const ERR_SECRET = 2;
    /**
     * ��� ������. ������� ��� ������������� ����� ����������.
     */
    const ERR_AMOUNT = 3;
    /**
     * ��� ������. �������� �������������� �����.
     */
    const ERR_RETRY  = 4;
    /**
     * ��� ������. ������������ �� ����������
     */
    const ERR_USER  = 5;
    /**
     * ��� ������. ������ ��� ����������� account::deposit()
     */
    const ERR_DEPOSIT = 6;
    
    /**
     * ������ ��� ������� ������� � ������ (webpay_log)
     * 
     * @var array
     */
    protected $_fields = array();
    
    
    /**
     * �������� ������� ��� ����������. � ��� ����� �������� POST �����, ������� ������ �� ���-��������
     * 
     * @param  array    ������ � ������ �� webpay
     * @return �����
     */
    public function income($data) {
        global $DB;
        $this->_fields = array();
        $id = $DB->insert('webpay_log', array('request' => serialize($data)), 'id');
        if ( 
            empty($data['amount']) || empty($data['userid']) || empty($data['userid_extra'])
            || empty($data['paymentid']) || empty($data['key']) || empty($data['paymode'])
        ) {
            $this->_error($id, self::ERR_DATA);
            return false;
        }
        $amount = floatval($data['amount']);
        $login  = (string) $data['userid_extra'];
        $this->_fields['payment_id'] = $paymentid = (string) $data['paymentid'];
        if ( $amount <= 0 ) {
            $this->_error($id, self::ERR_AMOUNT);
            return false;
        }
        $this->_fields['amount'] = $amount;
        if ( $data['key'] != md5($data['amount'] . $data['userid'] .$data['paymentid'] . onlinedengi_cards::SECRET) ) {
            $this->_error($id, self::ERR_SECRET);
            return false;
        }
        $user = new users;
        $user->GetUser($login);
        if ( empty($user->uid) ) {
            $this->_error($id, self::ERR_USER);
            return false;
        }
        $this->_fields['user_id'] = $user->uid;
        if ( $DB->val("SELECT COUNT(*) FROM webpay_log WHERE payment_id = ?", $paymentid) ) {
            $this->_success($id, true);
        } else {
            $account = new account;
            $account->GetInfo($user->uid);
            $comment = "���������� ����� ���-�������";
            if ( $account->deposit($op_id, $account->id, $amount, $comment, self::PAYMENT_SYS_CODE, $amount) ) {
                $this->_error($id, self::ERR_DEPOSIT);
                return false;
            }
            $this->_fields['billing_id'] = $op_id;
            $this->_success($id);
        }
        return true;
    }
    
    
    /**
     * ���������� � ���������� ������ � ����� � ��� � ���� � � ������
     * 
     * @param integer $id    id ������ � �����
     * @param integer $errno ����� ������
     */
    protected function _error($id, $errno) {
        global $DB;
        $this->_fields['result'] = $errno;
        $DB->update('webpay_log', $this->_fields, "id = {$id}");
        switch ( $errno ) {
            case self::ERR_USER: {
                $comment = '�� ������ ������������';
                break;
            }
            default: {
                $comment = '������ ��� ���������� �����';
            }
        }
        $xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
        $xml .= "<result>\r\n<id>{$id}</id>\r\n<code>NO</code>\r\n<comment>{$comment}</comment>\r\n</result>";
        echo iconv('CP1251', 'UTF-8', $xml);
    }
    
    
    /**
     * ���������� � ���������� ����� �����������
     * 
     * @param type $id     id ������ � �����
     * @param type $retry  ���� �� ����� �������� ����� (@see http://dengionline.com/dev/protocol/notification)
     */
    protected function _success($id, $retry=false) {
        global $DB;
        $this->_fields['result'] = $retry? self::ERR_RETRY: 0;
        $DB->update('webpay_log', $this->_fields, "id = {$id}");
        $xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
        $xml .= "<result>\r\n<id>{$id}</id>\r\n<code>YES</code>\r\n<comment>��� ���� ��������</comment>\r\n</result>";
        echo iconv('CP1251', 'UTF-8', $xml);
    }
    
    
}

