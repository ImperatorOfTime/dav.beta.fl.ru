<?php

/**
 * @uses pear install HTTP_Request2
 */
require_once 'HTTP/Request2.php';

/**
 * ����� ��� ������ � OAuth (����� ������������� � ������ ��� ������ ������� �������� �� ������ ��� oauth)
 *
 */
abstract class API_OAuth
{
    /**
     * ��������� �� ��������� ��� �������
     *
     * @var array
     */
    protected $_requestConfig = array(
        'adapter'           => 'HTTP_Request2_Adapter_Curl',
        'connect_timeout'   => 20,
        'protocol_version'  => '1.1',
        'ssl_verify_peer'   => false,
        'ssl_verify_host'   => false,
        'ssl_cafile'        => null,
        'ssl_capath'        => null,
        'ssl_passphrase'    => null
    );

    /**
     * ��������� ��� �����������
     *
     * @var null
     */
    protected $_code;

    /**
     * ���� ��� ���������� ��������
     *
     * @var
     */
    protected $_access_token;

    /**
     * ��������� �����
     *
     * @var bool
     */
    protected $_debug;

    /**
     * ����� ��� �� �������� ����������� ��� ���������� ������ � ���
     */
    const AUTH_URI = '';

    /**
     * ����� API � ������� ��������������� ����� �����������
     */
    const API_URI  = '';

    /**
     * �����, ��� ���������� ����������� OAUTH
     */
    const OAUTH_URI = '';

    /**
     * �������� ������ (������������ ��� ������������ �� ����, �����)
     */
    const CLIENT_BETA_ID     = '';
    const CLIENT_BETA_SECRET = '';
    const REDIRECT_BETA_URI  = '';

    /**
     * ������ ������ @see classes/payment_keys.php
     */
    const CLIENT_ID     = '';
    const CLIENT_SECRET = '';
    const REDIRECT_URI  = '';

    /**
     * ��������� ������������ � �������
     */
    const SERVER_ENCODING = 'CP1251';

    /**
     * ��������� ������������ ��� ����������� ��������
     */
    const SEND_ENCODING   = 'UTF-8';

    /**
     * ���������� �� ������ ����� �� ��� ����������� OAuth � �������
     *
     * @param $uri      ����� �������
     * @return bool     true - �����, false - �� �����
     */
    abstract public function isOAuth($uri);

    /**
     * �������� ������ � ����� �������
     *
     * @param HTTP_Request2 $resp     ������ �������
     * @return array
     */
    abstract public function getBodyArray($resp);

    /**
     * ���������� ����� �����������
     *
     * @param string $scope   ������ ������������� ����. ����������� ��������� ������ - ������. �������� ������ ������������� � ��������.
     * @return string
     */
    abstract static public function getAuthorizeUri( $scope = null );

    /**
     * ������ ������� ������ ��������� ����������������� ������
     *
     * @return mixed
     */
    abstract public function checkToken();

    /**
     * ����������� ������
     *
     * @param string $code            ��������� ����
     * @param string $accessToken     ���� �������
     */
    public function __construct($code = null, $accessToken = null) {
        $this->setAuthCode($code);
        $this->setAccessToken($accessToken);
        $this->log = new log("wallet/api-oauth-".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
    }

    /**
     * ������ ��������� ������� ������
     *
     * @param $debug
     */
    public function setDebug($debug) {
        $this->_debug = $debug;
    }

    /**
     * ��������� ������ �� ��������� �������
     *
     * @return mixed
     */
    public function isDebug() {
        return $this->_debug;
    }

    /**
     * ������ ��������� ��� �����������
     *
     * @param string $code
     */
    public function setAuthCode($code) {
        $this->_code = $code;
    }

    /**
     * ���������� ��������� ��� �����������
     *
     * @return null
     */
    public function getAuthCode() {
        return $this->_code;
    }

    /**
     * ������ ��� ��� ���������� ��������
     *
     * @param $accessToken
     */
    public function setAccessToken($accessToken) {
        $this->_access_token = $accessToken;
    }

    /**
     * ���������� ��� ��� ���������� ��������
     *
     * @param $accessToken
     */
    public function getAccessToken() {
        return $this->_access_token;
    }

    /**
     * ���������� ������������ �������
     *
     * @return array
     */
    public function getRequestConfig() {
        return $this->_requestConfig;
    }

    /**
     * ��������� ��� �������� ������ � ������������
     *
     * @param string $name     �������� ������������
     * @param mixed  $value    ��������
     */
    public function setRequestConfig($name, $value) {
        $this->_requestConfig[$name] = $value;
    }

    /**
     * ���������� �� ����������
     *
     * @return string
     */
    static public function getClientID() {
        return ( is_release() ? self::CLIENT_ID : self::CLIENT_BETA_ID );
    }


    /**
     * ���������� �������� ��� ����������
     *
     * @return string
     */
    static public function getClientSecret() {
        return ( is_release() ? self::CLIENT_SECRET : self::CLIENT_BETA_SECRET );
    }

    /**
     * ���������� ����� ��������� ����������
     *
     * @return string
     */
    static public function getRedirectURI() {
        return ( is_release() ? self::REDIRECT_URI : self::REDIRECT_BETA_URI );
    }


    /**
     * ������������� � ���������� ������ ��� �������
     *
     * @param $uri          ������ �������
     * @param $method       ����� ������� (POST, GET) @see http://pear.php.net/package/HTTP_Request2/
     * @return HTTP_Request2
     */
    public function initRequest($uri, $method = HTTP_Request2::METHOD_POST) {
        $request = new HTTP_Request2($uri, $method);
        $request->setConfig($this->getRequestConfig());
        $request->setHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=' . self::SEND_ENCODING );
        $request->setHeader( 'Expect', '' );
        if($this->isOAuth($uri)) {
            $request->setHeader( 'Authorization', 'Bearer ' . $this->getAccessToken() );
        }
        return $request;
    }

    /**
     * ������ ������
     *
     * @param string $uri    ������ �������
     * @param array  $req    POST ������ ���� ����
     * @param $method        ����� ������� (�� ��������� POST)
     * @return mixed
     */
    public function request($uri, $req = array(), $method = HTTP_Request2::METHOD_POST) {
        $request = $this->initRequest($uri, $method);
        if($method == HTTP_Request2::METHOD_POST) {
            $request->addPostParameter($req);
        }
        $this->last_request = $request;
        $this->sended       = $request->send();
        if( $this->sended->getStatus() != 200) {
            $status = $this->sended->getStatus();
            ob_start();
            var_dump($req);
            $content = ob_get_clean();
            $this->log->writeln("FAIL Request({$status}):\nuri:{$uri}\n");
            $this->log->write("Request:\n " . $content);
            $this->log->write("Result:\n ". $this->sended->getBody());
        }
        return $this->sended;
    }


}