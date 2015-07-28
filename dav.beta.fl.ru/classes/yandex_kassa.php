<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");

/*
 * ����� ��� ������ � �������� ������.�����
 * https://money.yandex.ru/start/#1
 * 
 * ������ �� �������� https://money.yandex.ru/doc.xml?id=526537
 * scid=52128
 * ShopID=17004
 * 
 * �������� ���� ��� �������� �������� ������.�����:
 * �����: testkunchenko
 * ������: 123456
 * ��������� ������: 123456789
 * ����: 4100322324227
 * ��������� �������� ���� - https://demomoney.yandex.ru/shop.xml?scid=50215
 * 
 * �������� ������ ��� �������� �������� �� ������:
 * ����� ����� 4268 0337 0354 5624, 
 * ���� �������� - ����� � �������, 
 * cvv 123, 
 * ������� ����� ������� ��������� �����.
 * 
 * �������� shopid � scid � ���� ����� �� ������, ��� �� ������ ��������.
 */

class yandex_kassa {
    
    
    /**
     * ������������� �������� ��� ������� ��������
     */
    const SHOPID_DEPOSIT        = 17004;
    const SCID_DEPOSIT_MAIN     = 8420;
    const SCID_DEPOSIT_TEST     = 52128;
    
    
    
    /**
     * ������������� �������� ��� �������� ����� ��
     */
    const SHOPID_SBR     = 17233;
    const SCID_SBR_MAIN  = 9283;
    const SCID_SBR_TEST  = 52249;
    
    /**
     * Url-����� ��������� ������� ��� �������� ��������
     */
    const URL_MAIN = "https://money.yandex.ru/eshop.xml";
    
    /**
     * Url-����� ��������� ������� ��� �������� ��������
     */
    const URL_TEST = "https://demomoney.yandex.ru/eshop.xml";
    
    /**
     * Url-����� ��������� ������� ��� �������� �� ��������� �������
     */
    const URL_LOCAL = "/bill/test/ykassa.php";
    
    /**
     * ������ �� �������� � ������.�������.
     */
    const PAYMENT_YD = "PC";
    
    /**
     * ������ � ������������ ���������� �����.
     */
    const PAYMENT_AC = "AC";
    
    /**
     * ������ �� �������� � ������� WebMoney
     */
    const PAYMENT_WM = "WM";
    
    /**
     * ������ � �����-����
     */
    const PAYMENT_AB = "AB";
    
    /**
     * ������ �� �������� ������
     */
    const PAYMENT_SB = "SB";
    
    /**
     * ������������ ����� ��� ������
     */
    const MAX_PAYMENT_ALFA = 15000;
    const MAX_PAYMENT_SB = 10000;
    
    /**
     * ��������� �������, ��������� ��� ������ demomoney.yandex.ru
     */
    protected $test_payments = array(
        self::PAYMENT_YD,
        self::PAYMENT_AC
    );
    
    /**
     * ������ ��������� � ��������������� �� ������ ������
     * @var array
     */
    protected $shops_main = array(
        self::SHOPID_DEPOSIT => self::SCID_DEPOSIT_MAIN,
        self::SHOPID_SBR     => self::SCID_SBR_MAIN
    );

    
    /**
     * ������ ��������� � ��������������� �� �������� ������
     * @var array 
     */
    protected $shops_test = array(
        self::SHOPID_DEPOSIT => self::SCID_DEPOSIT_TEST,
        self::SHOPID_SBR     => self::SCID_SBR_TEST        
    );

    
    /**
     * ������� ������ ��������� ��������� � ������ 
     * � ����������� �� ������ ������
     * @var array
     */
    protected $shops = array();
    
    /**
     * ���� ��������� ������
     * 
     * @var bool
     */
    protected $is_test = false;
    
    /**
     * ������ ����� ��������� �������
     * 
     * @var string
     */
    protected $url;
    
    /**
     * ������ ��������� �������� ������
     */
    protected $payments;
    
    protected $shopid;
    protected $scid;

    protected $url_success;
    protected $url_fail;
    
    protected $ip_real = array(
        '77.75.157.168',
        '77.75.157.169',
        '77.75.159.166',
        '77.75.159.170'
    );
    
    protected $ip_test = array(
        '77.75.157.166',
        '77.75.157.170',
        '127.0.0.1',
        '2.92.3.100',
        '62.213.65.100'
    );
    
    /**
     * ������������� ���������� � �� ���������. ������ ����������� ���� invoiceId �������.
     */
    public $params;
    
    /**
     * ��������� ��������� � ������ ������ ������� ������.
     */
    public $message = "";
    
    /**
     * �������������� ��������� ��������� ������ �����������. 
     * ��� �������, ������������ ��� �������������� ���������� �� �������. 
     * �������������� ����
     */
    public $techMessage = "";


    
    
    
    public function __construct() 
    {
        $this->payments = array(
            3 => self::PAYMENT_YD,
            6 => self::PAYMENT_AC,
            10 => self::PAYMENT_WM,
            16 => self::PAYMENT_AB,
            17 => self::PAYMENT_SB
        );
        
        
        $this->setTest(!is_release());
    }
    
    
    
    /**
     * ������������� ����������� �������� ����� ���������� �� �������
     * true ��� ��������, false ��� �������� ��������
     * 
     * @param bool $value
     */
    public function setTest($value) 
    {
        $this->is_test = (bool) $value;
        $this->init();
    }
    
    
    
    /**
     * ������� �������
     * 
     * @param int $shopid
     * @return boolean
     */
    public function setShop($shopid)
    {
        if(!isset($this->shops[$shopid])) return false;

        $this->shopid = $shopid;
        $this->scid = $this->shops[$shopid];
        
        return true;
    }

    

    /**
     * ��������� ���� ������ ������� � ����������� �� ��������
     */
    protected function init() 
    {
        if($this->is_test) 
        {
            $this->url = is_local() ? self::URL_LOCAL : self::URL_TEST;
            $this->shops = $this->shops_test;
        } 
        else 
        {
            $this->url = self::URL_MAIN;
            $this->shops = $this->shops_main;
        }
        
        
        //������� � ������� �����������
        $this->shopid = self::SHOPID_DEPOSIT;
        $this->scid = $this->shops[self::SHOPID_DEPOSIT];
    }
    
    
    
    
    /**
     * ���������� � ���������� ����� ������
     * 
     * @param int $ammount ����� � ������
     * @param int $bill_id ����� ����� � ������
     * @param int $payment ��� ������� ������ �� payments
     * @param int $billReserveId ID ������ � bill_reserve
     * 
     * @return string Html-����� ������
     */
    public function render($ammount, $bill_id, $payment, $billReserveId = null) 
    {
        //����� ������ ���� �������������
        if ($ammount <= 0) {
            return false;
        }
        
        //����������� ������ ������. ���������� ������.������
        if (!in_array($payment, $this->payments)) {
            $payment = self::PAYMENT_YD;
        }
        
        if ($this->is_test && !in_array($payment, $this->test_payments)) {
            $this->url = self::URL_LOCAL;
        }
        
        $data = array(
            'url' => $this->url,
            'scid' => $this->scid,
            'shopId' => $this->shopid,
            'ammount' => $ammount,
            'customerNumber' => $bill_id,
            'payment' => $payment//,
            //'cps_email',
            //'cps_phone'
        );
        
        //���������� ��������� � ������ �� �������
        if($billReserveId > 0) {
            $data['billReserveId'] = $billReserveId;
        }
        
        $form = Template::render(ABS_PATH . '/templates/yandex.kassa.php', $data);
        return str_replace("\n", '', $form);
    }
    
    
    
    
    /**
     * �������� ������
     */
    public function order($pay = false) {
        
        $this->initParams();

        $code = $this->validateParams($pay);
        
        if ($code == 0) {
            if (!$pay) {
                $code = $this->insertTemp(); 
            } else {
                $code = $this->addOperation();
            }
        }
        
        return $this->getResult($code);
    }
    
    
    
    
    private function initParams() {
        $post = array(
            'requestDatetime' => __paramInit('string', null, 'requestDatetime'),
            'action' =>	__paramInit('string', null, 'action'),
            'shopId' => __paramInit('int', null, 'shopId'),
            'invoiceId' => __paramInit('string', null, 'invoiceId'),
            'customerNumber' => __paramInit('string', null, 'customerNumber'),
            'orderCreatedDatetime' => __paramInit('string', null, 'orderCreatedDatetime'),
            'orderSumAmount' => __paramInit('string', null, 'orderSumAmount'),
            'orderSumCurrencyPaycash' => __paramInit('string', null, 'orderSumCurrencyPaycash'),
            'orderSumBankPaycash' => __paramInit('string', null, 'orderSumBankPaycash'),
            'shopSumAmount' => __paramInit('string', null, 'shopSumAmount'),
            'shopSumCurrencyPaycash' => __paramInit('string', null, 'shopSumCurrencyPaycash'),
            'shopSumBankPaycash' => __paramInit('string', null, 'shopSumBankPaycash'),
            'paymentPayerCode' => __paramInit('string', null, 'paymentPayerCode'),
            'paymentType' => __paramInit('string', null, 'paymentType'),
            'md5' => __paramInit('string', null, 'md5'),
            'orderId' => __paramInit('int', null, 'orderId', null)
        );
        
        $this->params = $post;
    }
    
    
    
    
    /**
     * ��������� ��������� ������� � ���������� ���
     * 0 - �������, 1 - ������ �����������, 200 - ������ ������� �������
     */
    private function validateParams($pay) {
        if ($this->isErrorIP()) {
            $this->message = "������������� IP: ".getRemoteIp();
            return 200;            
        }        
        if ($this->isErrorMd5()) {
            $this->message = "�������� ���-�����";
            return 1;
        }        
        if ($this->isErrorShop()) {
            $this->message = "�������� �������";
            return 200;            
        }        
        if ($this->isErrorAmmount()) {
            $this->message = "�������� �����";
            return 200;
        }        
        if ($pay && $this->isErrorInvoiceId()) {
            $this->message = "������ �� ������";
            return 200;
        }        
        return 0;
    }
    
    private function isErrorIP() {
        $ip = getRemoteIp();        
        $allowedIPs = $this->is_test ? $this->ip_test : $this->ip_real;        
        return !in_array($ip, $allowedIPs);    
    }
    
    private function isErrorMd5() {
        $data = array(
            $this->params['action'],
            $this->params['orderSumAmount'],
            $this->params['orderSumCurrencyPaycash'],
            $this->params['orderSumBankPaycash'],
            $this->params['shopId'],
            $this->params['invoiceId'],
            $this->params['customerNumber'],
            YK_KEY
        );
        
        $hash = md5(implode(';', $data));
        
        return strtoupper($hash) != $this->params['md5'];
    }
    
    private function isErrorShop() {
        return !isset($this->shops[$this->params['shopId']]);
    }
    
    private function isErrorAmmount() {
        return $this->params['orderSumAmount'] <= 0;
    }
    
    private function isErrorInvoiceId() {
        global $DB;
        $tmp_payment = $DB->val('SELECT id FROM account_operations_yd WHERE invoice_id = ?', $this->params['invoiceId']);
        return !$tmp_payment;
    }
    

    
    private function insertTemp() 
    {
        global $DB;

        $uid = $DB->val('SELECT uid FROM account WHERE id = ?i', 
                $this->params['customerNumber']);

        if (!$uid) {
            $this->message = 'Not found user account (customerNumber)';
            return 200;
        }
        
        
        //�������� ����������� ���������� �� ID ������
        //� ���� ���������� ���� ������ �� ����������� � �� ��� �� ������ �����
        //���� ������ �� ��� �� ���� ��� � ������ �������� checkOrder
        if ($this->params['orderId']) {
            $billing = new billing($uid);
            if (!$billing->checkOrder($this->params)) {
                $billing->cancelReserveById($this->params['orderId']);
                $this->message = 'Failed check order';
                return 200;
            }
        }
        
        
        $dups = $DB->val('SELECT id FROM account_operations_yd WHERE invoice_id = ?', 
                $this->params['invoiceId']);
        
        if (!$dups) {
            $shopParams = $this->getShopParams();
        
            $descr = "������ ����� ������.�����. ����� - {$this->params['orderSumAmount']}, ����� ������� - {$this->params['invoiceId']}";
            $descr .= $shopParams['op_descr'];
            
            $DB->insert('account_operations_yd', array(
                'billing_id'  => $this->params['customerNumber'],
                'op_date'     => $this->params['requestDatetime'],
                'op_code'     => $shopParams['op_code'],
                'ammount'     => $shopParams['ammount'],
                'trs_sum'     => $this->params['orderSumAmount'],
                'descr'       => $descr,
                'invoice_id'  => $this->params['invoiceId'],
            ), 'id');
        
            if($DB->error) {
                //@todo: ����� ����� ����� techMessage ? 
                //message � �� ����� �� ������������ �������?
                $this->message = $DB->error;
                return 200;
            }
        }
        
        
        //���� ��� ��
        return 0;
    }
    
    
    
    
    
    /**
     * ��������� ������
     */
    private function addOperation() 
    {
        global $DB;

        $DB->error_output = false;
        $shopParams = $this->getShopParams();
        
        $payment = $DB->row('
            SELECT 
                aoy.id,            
                aoy.descr,
                ao.id AS acc_op_id 
            FROM account_operations_yd AS aoy 
            LEFT JOIN account_operations AS ao ON ao.id = aoy.acc_op_id AND ao.billing_id = aoy.billing_id
            WHERE invoice_id = ?', 
            $this->params['invoiceId']);
        
        //���������� � ������� ��� ���� �������� �������
        if ($payment['acc_op_id'] > 0) {
            return 0;
        }
        
        
        $DB->start();
        
        $op_id = 0;
        $data = array();
        $billing = null;
        
        $account = new account();       
        //������� ������ �� ��
        $error = $account->deposit(
                $op_id,
                $this->params['customerNumber'], 
                $shopParams['ammount'], 
                $payment['descr'], 
                array_search($this->params['paymentType'], $this->payments), 
                $this->params['orderSumAmount'], 
                $shopParams['op_code']);     
        
        //���� ��� ��� ������ � �� ��������� �� ������� ������ �����
        if (!$error && $op_id > 0) {
            
            $success = true;
            $data['acc_op_id'] = $op_id;
            
            //������� ������ ����� �� ������� ������� ������ ����
            //���� ����� ��� ������ ��� ������� �� ������ �� ������ �� ������� �����
            if ($this->params['orderId']) {
                $billing = new billing($account->uid);
                if($success = $billing->buyOrder(
                        $this->params['orderId'], 
                        $shopParams['op_code'],//@todo: ������������ ������������� ���������
                        $this->params)) {
                    
                    $data['bill_reserve_id'] = $this->params['orderId'];
                }
            }
            
            //��������� ID �������� ���������� �� � ID ���������� ������ ��� �������
            if ($success) {
                $DB->update('account_operations_yd', $data, 'id = ?i', $payment['id']);
                $DB->commit();
                return 0;
            } else {
                $this->message = sprintf('Failed to purchase order #%s', $this->params['orderId']);
            }
            
        } else {
            $this->message = 'Failed deposit to account';
        }        
        
        //�� ������� ���������� ����� ���������� ���������� 
        //� ���������� ������ ��� �������� � �������� �������        
        $DB->rollback();
        
        //���� ����� ������� ������ �� � �������� �����
        //����� �� ����� � �������
        if ($billing && $this->params['orderId'] > 0) {
            $billing->cancelReserveById($this->params['orderId']);
        }
        
        //������, ������� �������
        return 100;
    }
    
    
    
    
    
    
    /**
     * �������� ������ �� ������������
     *
     * @return integer id ���������� ��������, false ���� �������� �� �������
     */
    private function checkDups($str) {
        global $DB;

        $sql = "SELECT id FROM account_operations WHERE descr = ?";
        $out = $DB->val($sql, $str);
        if ($out !== null)
            return $out;
        return false;
    }
    
        
        
        
        
        
    /**
     * �������� ��� ��������, ��������� �� ��������
     */
    private function getShopParams() 
    {
        $data = array();
        
        switch ($this->params['shopId']) 
        {
            //@todo: � ������ ��������� ���������� ��� ������� �������������, �������� ���� ����.
            //���� ���� ������ ���������� ������ � ������ ������ ����� �� ��� ����������.
            
            /*
            case self::SHOPID_SBR : // ������ ����� �� ��
                $data['op_code'] = sbr::OP_RESERVE;
                $data['ammount'] = 0;
                $data['op_descr'] = ", ��� #000"; //�� �����, ��� ���������� �� �������� ��
                break;
            */
            
            //case self::SHOPID_DEPOSIT : // ������� ����� �� ������ ����
            
            default:
                $data['op_code'] = 12;
                $data['ammount'] = $this->params['orderSumAmount'];
                $data['op_descr'] = '';
                break;
        }
        
        return $data;
    }

    
    
    
    
    /**
     * ���������� ��������� ������
     */
    private function getResult($code) {
        $result = array(
            'performedDatetime' => date('c'),
            'code' => $code,
            'invoiceId' => $this->params['invoiceId'],
            'shopId' => $this->params['shopId']
        );
        if ($this->message) {
            $result['message'] = $this->message;
        }
        if ($this->techMessage) {
            $result['techMessage'] = $this->techMessage;
        }
        return $result;
    }
    
}