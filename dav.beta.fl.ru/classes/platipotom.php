<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff2.php");

/**
 * Class platipotom
 * � ����� ������ PRO, ��������, �����������, ����������� ������� � ����������� ����� �������� ������ "������ � ������".
 */
class platipotom {
    
    /**
     * ����� ��������� �������
     */
    const SERVER_URL = 'https://pay.platipotom.ru';
    const SERVER_LOCAL = '/bill/test/platipotom.php';
    
    /**
     * ��� ��������� ������� ��� ����������� �����
     */
    const PAYMENT_CODE = 15;
    
    /**
     * ������������� ��������. �������� ��� ���������� ��������.
     * @todo �������� �������� ������� � ������� ��� � SHOP_ID_TEST
     */
    const SHOP_ID = '10100';
    const SHOP_ID_TEST = '10100';
    
    
    /**
     * ������������ ����� ������� �������
     */
    const PRICE_MAX = 1000;
    
    
    /**
     * ������������ ����� ������� � ����������� �������� ������������
     */
    const PRICE_MAX_MORE = 1000;
    
    
    /**
     * ������������ ����� ������� ��� ������� ���
     */
    const PRICE_MAX_PRO = 1000;
    
    
    /**
     * ������ ����� ��������� �������
     * 
     * @var string
     */
    protected $url;
    
    protected $shop_id;
    
    public $TABLE = 'account_operations_pp';
    
    private $is_pro = false;


    public function __construct($is_pro = false)
    {
        $this->url = !is_release() ? self::SERVER_LOCAL : self::SERVER_URL;
        $this->shop_id = is_release() ? self::SHOP_ID : self::SHOP_ID_TEST;
        $this->is_pro = $is_pro;
    }
    
    
    /**
     * ���������� � ���������� ����� ������
     * orderid - ���������� ������������� ������ � ���� ��������.
     * subid - ���������� ������������� ������������.
     * price - ��������� ��������� ������� � ������.
     * subtitle � ��������� �������, ������� ������������ � ���� ������ � ����� 
     *     �������������� ������ ������� �������� ������������ � �������� � ������� ������ �����. 
     *     �������� ����� ������ ������������ � ��������� UTF8
     * sig � ������� ������������� �������, �������� MD5-������ ������ shopid + orderid + key, 
     *     ��� key � ��������� ���� ��������, ������� ����� ���������� � ������ ��������
     *     �������� � ������� ������ �����.
     * @todo � ������� sig ����������� ��-�������
     * 
     * @param int $price ����� � ������
     * @param int $bill_id ����� ����� � ������
     * @param int $bill_reserve_id �� �� ������� bill_reserve
     * 
     * @return string Html-����� ������
     */
    public function render($price, $bill_id, $bill_reserve_id) 
    {
        //����� ������ ���� �������������
        if ($price <= 0) {
            return false;
        }
        
        //����� �� ������ ���� ������ ����������
        if ($price > $this->getMaxPrice($bill_id)) {
            return false;
        }
        
        $order_id = $this->savePayment($bill_id, $price, $bill_reserve_id);
        
        $user = new users();
        $user->GetUserByUID(get_uid(false));
        $reg_date = dateFormat('U', $user->reg_date);
        
        $formData = array(
            'shopid' => $this->shop_id,
            'orderid' => $order_id,
            'subid' => $bill_id,
            'price' => (int)$price,
            'subtitle' => iconv('cp1251', 'utf-8', '���������� �����'),
            'sig' => md5($this->shop_id . $order_id . PP_KEY . $user->login . $reg_date),
            'data[subid_register_date]' => $reg_date,
            'data[nickname]' => $user->login
        );
        
        $form = Template::render(ABS_PATH . '/templates/platipotom.php', array(
            'url' => $this->url,
            'formData' => $formData            
        ));
        return str_replace("\n", '', $form);
    }
    
    
    /**
     * ��������� �������
     * ������� ������� � ��� ������ �������������� ���������� ���������� MD5 �� ������
     * ���������� �������. ������ ��������� �������:
     * price = 30000, orderid = �5276ahe�, subid = 211637383, key = �__long_secret_passphrase__�
     * sig = md5(price + orderid + subid + key) = md5(�300005276ahe211637383__long_secret_passphrase__�) = 
     * �f52085ee39d017d958e78b6c652e539d�
     * 
     * @param int $price
     * @param int $order_id �� ������
     * @param int $sub_id �� ����� ������������
     * @param array $data ������ � ��������������� �����������
     */
    public function getSig($price, $order_id, $sub_id, $data = array())
    {
        $priceKop = $price * 100; // ���� � ��������
        ksort($data);
        $extraDataString = implode('', $data);
        $sig = md5($priceKop . $order_id . $sub_id . PP_KEY . $extraDataString);
        return $sig;
    }

    
    /**
     * ���������� ������������ ����� �������, ��������, ������� �� ���� 
     * ���-������ ����� ����� ����� �����
     * @param type $bill_id �� �����
     * @return int
     */
    public function getMaxPrice($bill_id = 0)
    {
        $maxPrice = self::PRICE_MAX;
        
        if ($this->is_pro) {
            $maxPrice = self::PRICE_MAX_PRO;
        } else {
            $uid = get_uid(false);

            if ($uid > 0) {
                $memBuff = new memBuff();
                if ($maxPriceSaved = $memBuff->get('platipotom_max_price_'.$uid)) {
                    return $maxPriceSaved;
                } else {
                    if(!$bill_id) {
                        $account = new account();
                        $account->GetInfo($uid, true);
                        $bill_id = $account->id;
                    }

                    $sql = "SELECT id FROM account_operations WHERE op_code = 12 AND payment_sys = ?i AND billing_id = ?i";
                    $operation_id = $this->db()->val($sql, self::PAYMENT_CODE, $bill_id);

                    if ($operation_id) {
                        $maxPrice = self::PRICE_MAX_MORE;
                    }
                    $memBuff->set('platipotom_max_price_'.$uid, $maxPrice);
                }
            }
        }
        
        return $maxPrice;
    }
    
    /**
     * ��������� ������ ������� � ����
     * @param type $data
     */
    public function savePayment($billing_id, $price, $bill_reserve_id)
    {
        return $this->db()->insert($this->TABLE, array(
            'billing_id' => $billing_id, 
            'price' => $price,
            'bill_reserve_id' => $bill_reserve_id
        ), 'id');
    }
    
    public function getPayment($order_id)
    {
        return $this->db()->row("SELECT * FROM {$this->TABLE} WHERE id = ?i", $order_id);
    }
    
    /**
     * ��������� �������
     */
    public function order()
    {
        //��������� ����� ������
        $this->db()->error_output = false;
        
        $orderid = $_GET['orderid'];
        if (!$orderid) exit;
        
        $json_data = array(
            'status' => '0',
            'time' => time()
        );
        
        $payment = $this->getPayment($orderid);
        
        if ($payment) {
            $data = isset($_REQUEST['data']) && is_array($_REQUEST['data']) ? $_REQUEST['data'] : array();
            $sig = $this->getSig($payment['price'], $orderid, $payment['billing_id'], $data);

            if ($sig == $_GET['sig']) {
                $json_data['status'] = '1';
                $op_id = 0;

                //������� ������
                $account = new account();
                $error = $account->deposit(
                    $op_id, 
                    $payment['billing_id'], 
                    $payment['price'], 
                    //@todo: ��� ������ ������ ���� ������ � ���������� ������� �������� �����!
                    "������ ����� \"����� �����\". ����� - {$payment['price']}, ����� ������� - {$orderid}", 
                    self::PAYMENT_CODE, 
                    $payment['price']
                );

                if (!$error) {
                    
                    //������� ������
                    $billing = new billing($account->uid);
                    $billing->buyOrder(
                        $payment['bill_reserve_id'], 
                        12,//@todo: ������������ ������������� ���������
                        array()//@todo: ���� ��� ����������
                    );

                    $this->db()->query("DELETE FROM {$this->TABLE} WHERE id = ?", $orderid);
                    
                    $memBuff = new memBuff();
                    $memBuff->delete('platipotom_max_price_'.$account->uid);
                }
            }
        }
        
        
        
        return $json_data;
    }
    
    
    /**
     * ���� �� � ������������ ������� ����� ����������
     * 
     * @param type $uid
     * @return boolean
     */
    public function isWasPlatipotom($bill_id = 0)
    {
        $value = $this->getMaxPrice($bill_id);
        return $value > self::PRICE_MAX;        
    }


    /**
     * @return DB
     */
    public function db()
    {
        return $GLOBALS['DB'];
    }
    
    
}
