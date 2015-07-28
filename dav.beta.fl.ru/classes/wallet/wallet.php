<?php

/**
 * ��� ���������� ������
 */
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/JWS.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Crypt/DES.php";
/**
 * ���������� ���� ��� ������ � ������� ������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");

/**
 * ����� ��� ������ � ���������� � �����������
 */
abstract class Wallet
{
    /**
     * ��� ������ ������ (������������ ��������) @see WalletTypes::WALLET_* WalletTypes::getAllTypes();
     *
     * @integer
     */
    protected $_type;

    /**
     * �� ������������
     *
     * @var int|null
     */
    public $uid;

    /**
     * ��� ������ � �����
     *
     * @var DB
     */
    protected $_db;

    /**
     * ������ ����� ��������� ��� ���������� ��� ���
     *
     * @var bool
     */
    public $isNotNewAcessToken = false;


    /**
     * ��� ������� ������� ������ (����� �������� ������ ����� ���� ��� � ���� ������������ ������ �� ����������� ����)
     *
     */
    const PIN_CODE        = TOKEN_PIN;

    /**
     * ������� ��� ������
     *
     * @return mixed
     */
    abstract function payment($sum);

    /**
     * ����������� ������
     *
     * @param integer $uid   �� ������������
     */
    public function __construct($uid = null) {
        global $DB;
        if($uid === null) {
            $uid = get_uid(false);
        }
        $this->uid = $uid;
        $account = new account();
        $account->GetInfo($uid, true);
        $this->account = $account;
        $this->_db  = $DB;

        $this->initWallet();
    }

    /**
     * �������������� ����� ��� �������� ������ ����� DES
     *
     * @return Crypt_DES
     */
    static public function des() {
        $des = new Crypt_DES();
        $des->setKey(Wallet::PIN_CODE);
        return $des;
    }

    /**
     * �������������� ���� �������� ����� (� ������ ������� �� ����, �� ��������� 3 ����)
     */
    public function initValidity() {
        $this->data['validity'] = '3 years';
    }

    /**
     * �������������� ������ ��������
     */
    public function initWallet() {
        $sql = "SELECT *, (access_time + validity) as validity_time FROM bill_wallet WHERE type = ?i AND uid = ?i";
        $this->data = $this->_db->row($sql, $this->_type, $this->uid);
    }

    /**
     * ��������� ������ �������� (��� ���������� ������ ���� ���������� ������ � ��������� $this->data
     * �������� ������� bill_wallet
     *
     * @return integer ���������� �� ������ � �������
     */
    public function saveWallet() {
        if(empty($this->data))  {
            return false; // ������ ��� ���������� �� ����������
        }

        if($this->data['access_token'] === null) {
            $this->data['validity']    = null;
            $this->data['access_time'] = null;
            $this->data['active']      = false;
        } else {
            // ������������ ������� ������� ����������� � ��������� ������ � ������������
            Wallet::clearActiveWallet($this->uid);
            if(!$this->isNotNewAcessToken) {
                $this->initValidity();
                $this->data['access_time'] = 'now';
            }
            $this->data['active']      = true;
        }

        foreach($this->data as $name=>$value) {
            if($name == 'validity_time') continue;
            $fields[] = $this->_db->parse("{$name} = ?", $value);
        }
        $fields_sql = implode(", ", $fields);

        $sql = "UPDATE bill_wallet SET {$fields_sql} WHERE type = ?i AND uid = ?i RETURNING id";
        $res = $this->_db->row($sql, $this->_type, $this->uid);

        // �������� ��� ��� ������ ����� ������� �� ������ ������� � ��� ����
        if(empty($res)) {
            $data = $this->data;
            unset($data['validity_time']);
            return $this->_db->insert('bill_wallet', $data, 'id');
        }

        return $res['id'];
    }

    /**
     * ������� ������ ������ ��������
     *
     * @todo ������ �������� ������� �� �����, ����� ���� ��������
     */
    public function removeWallet() {
        $this->_db->query("DELETE FROM bill_wallet WHERE type = ?i AND uid = ?i", $this->_type, $this->uid);
    }

    /**
     * ���������� ���� ������� ��� ������� ���� ������ �� ���������������� �������� �� ���������������� �� ��
     *
     * @return bool|int|string
     */
    public function getAccessToken() {
        if(empty($this->data)) {
            $this->initWallet();
        }

        if($this->data['access_token'] == null || strtotime($this->data['validity_time']) < time()) {
            return false;
        } else {
            return Wallet::des()->decrypt(JWS_Base64::urlDecode($this->data['access_token']));
        }
    }

    /**
     * ���������� ����� ������ �������� (��������� �� ���� �������)
     *
     * @param integer $len      ������� ������ ���������� ������� � � �����
     * @param string  $char     ������ ������� ��������
     */
    public function getWalletBySecure() {
        if(empty($this->data)) {
            $this->initWallet();
        }
        $wallet = $this->data['wallet'];

        return self::secureString($wallet);
    }

    /**
     * �������� ������� ��� ������ � ������
     *
     *
     * @param string  $string   ������ � ������� ��������
     * @param integer $len      ������� ������ ���������� ������� � � �����
     * @param string  $char     ������ ������� ��������
     * @return bool|string
     */
    static function secureString($string, $len = 4, $char = '*') {
        if($string == '') return false;
        if($len*2 > strlen($string)) $len = strlen($string) / 2;
        if($len <= 0) $len = 4;
        $repeat = ( strlen($string) - $len*2 );
        // ���� �������� ������ 3 �������� ��������� ������ � 2 ����
        if($repeat < 3 ) {
            $len = round($len/2);
            $repeat = ( strlen($string) - $len*2 );
        }

        return substr($string, 0, $len) . ' ' . chunk_split( str_repeat($char, $repeat), 4, ' ') .substr($string, $len*-1);
    }

    /**
     * ������� ���� ������� �������� (��� ������������ ���������� � ��)
     *
     * @param string $token �� ������������� ����
     */
    public function setAccessToken($token) {
        $this->data['access_token'] = JWS_Base64::urlEncode(Wallet::des()->encrypt($token));
    }

    /**
     * ��������������� �������, �������� ��� ������ ������ ������� ��������
     * �� ���� ������� ������ ���� ����� ������, ������� ��������� ������ 1 ����� �� �� ������������
     *
     * @param integer $uid  �� ������������
     * @return mixed
     */
    static public function clearActiveWallet($uid) {
        global $DB;
        $sql = "UPDATE bill_wallet SET active = false WHERE uid = ?i AND active = true";
        return $DB->query($sql, $uid);
    }

    /**
     * ���������� ����� ������� �� ��� ���� � �� ������������
     *
     * @param integer $type     ��� ������ �������  @see WalletTypes::getAllTypes();
     * @param integer $uid      �� ������������
     * @return mixed
     */
    static public function setActiveWallet($type, $uid) {
        global $DB;
        if(!WalletTypes::isValidType($type)) return false;

        Wallet::clearActiveWallet($uid);
        $sql = "UPDATE bill_wallet SET active = true WHERE type = ?i AND uid = ?i";
        return $DB->query($sql, $type, $uid);
    }

    /**
     * ����������� � ������� ��� ����������� ��������
     *
     * @return mixed
     */
    public function authorize() {
        return $this->api->getAuthorizeUri();
    }
}

/**
 * ����� ��� ������������� ������������� �������� ��� ������
 *
 * ��� ���������� ������ ���� �������� ���������� �� ������ �������� ���� ��� � ������� getAllTypes();
 */
class WalletTypes
{

    /**
     * ��� ������ ������.������
     */
    const WALLET_YANDEX   = 1;

    /**
     * ��� ������ WebMOney
     */
    const WALLET_WEBMONEY = 2;

    /**
     * ��� ������ ���������� ������ ���
     */
    const WALLET_DOL      = 3;

    /**
     * ��� ������ ���������� ������ (�����-����)
     */
    const WALLET_ALPHA    = 4;

    /**
     *
     * �������������� ����� ��� ������ � ������� ������
     *
     * @param integer $uid  �� ��������� ������� ������������
     * @param integer $type ���� �� ������ ����� �������� ����� ������ � ���������� ������������������ ������
     *
     * @return bool|walletYandex|walletWebMoney
     */
    static function initWalletByType($uid = null, $type = null) {
        if($uid === null) {
            $uid = get_uid(false);
        }

        if($type === null) {
            $type = WalletTypes::getTypeWalletActive($uid);
        }

        switch($type) {
            case self::WALLET_YANDEX:
                require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/walletYandex.php";
                $wallet = new walletYandex($uid);
                return $wallet;
                break;
            case self::WALLET_WEBMONEY:
                require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/walletWebmoney.php";
                $wallet = new walletWebmoney($uid);
                return $wallet;
            case self::WALLET_ALPHA:
                require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/walletAlpha.php";
                $wallet = new walletAlpha($uid);
                return $wallet;
            case self::WALLET_DOL:
            default:
                return false;
                break;
        }
    }

    /**
     * ��������� ���� �� �������� ����� ������
     *
     * @param integer $uid  �� ��������� ������� ������������
     * @param integer $type ���� �� ������ ����� �������� ����� ������ � ���������� ������������������ ������
     * @return bool
     */
    static function isWalletActive($uid = null, $type = null) {
        static $isWalletActive;
        if($isWalletActive !== null) {
            return $isWalletActive;
        }

        $wallet = self::initWalletByType($uid, $type);
        return ( $isWalletActive = self::checkWallet($wallet) );
    }

    /**
     * ���������
     *
     * @param $wallet
     * @return bool
     */
    static function checkWallet($wallet) {
        return !( $wallet == false || (is_object($wallet) && $wallet->getAccessToken() == false) );
    }

    /**
     * ������ ���� ��������� ��������� ��������� � ������������
     *
     * @param null $uid
     * @return mixed
     */
    static function getListWallets($uid = null) {
        global $DB;

        if($uid === null) {
            $uid = get_uid(false);
        }

        $sql = "SELECT * FROm bill_wallet WHERE uid = ?i ORDER BY type";
        return $DB->rows($sql, $uid);
    }

    /**
     * ����� �������������� ��� ������ �� UID ������������
     *
     * @param integer $uid     �� ������������
     * @return mixed
     */
    static function getTypeWalletActive($uid = null) {
        global $DB;

        if($uid === null) {
            $uid = get_uid(false);
        }

        $sql = "SELECT type FROM bill_wallet WHERE uid = ? AND active = true";
        return $DB->val($sql, $uid);
    }

    /**
     * �������� ���� �� ���������� (���������� �� � �������)
     *
     * @param $type
     * @return bool
     */
    static function isValidType($type) {
        $system_types = self::getAllTypes();
        return in_array($type, $system_types);
    }

    /**
     * ���������� ��� ���� ��������� ������� ������� � �������
     *
     * @return array
     */
    static function getAllTypes() {
        return array(
            self::WALLET_YANDEX,
            self::WALLET_WEBMONEY,
            //self::WALLET_DOL,
            self::WALLET_ALPHA
        );
    }

    /**
     * ���������� �������� ���������� ������
     *
     * @param $type
     * @return string
     */
    static public function getNameWallet($type, $n=0, $accountId = 0) {
        if( $n<0 && $n>3 ) return false;

        switch($type) {
            case self::WALLET_YANDEX:
                $name = array('������� ������.������', '������.������', '������� %WALLET% ������.������', '������ �������� ������.�����');
                break;
            case self::WALLET_WEBMONEY;
                $name = array('������� WebMoney', 'WebMoney', '������� %WALLET% WebMoney', '������ �������� WebMoney');
                break;
            case self::WALLET_ALPHA:
            case self::WALLET_DOL:
                $name = array('���������� �����', 'VISA', '����������� ����� %WALLET%', '����� ����������� �����');
                break;
            default:
                $name = array('������ ����', '������ ����', '���� %WALLET% �� �����', '������ ����� �' . $accountId . ' �� �����');
                break;
        }

        return $name[$n];
    }
}


?>