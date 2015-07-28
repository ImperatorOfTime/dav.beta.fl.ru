<?php

/**
 * ������� ����� ��� ���� API.
 */
abstract class externalApi extends externalBase {

    const OBJTYPE_TABLE = 1;
    
    const METHOD_PREFIX = 'x____';

    static $apis = array();
    
    protected $_sess;

    protected $_mName;
    protected $_mCfg;


    /**
     * �������� ��������� ������������ API-������ � ����������� � �������� ������������� ����.
     *
     * @param string $ns   ������������ ���� (��-������� ������������� ������ API -- uri, ��. �����), �������������� ������� ������.
     * @param externalSession $sess   ������ ������.
     * @return externalApi   ������������������ ���������.
     */
    static function getInst($ns, $sess) {
        $api = NULL;
        if(isset(externalApi::$apis[$ns]))
            $api = externalApi::$apis[$ns];
        if(!$api) {
            $api_name = basename($ns);
            $api_path = EXTERNAL_API_PATH . "/{$api_name}.php";
            if(file_exists($api_path)) {
                require_once($api_path);
                $api_cls = 'externalApi_'.$api_name;
                if(class_exists($api_cls)) {
                    $api = new $api_cls($sess);
                    // @here �������� ���� ������� ��� �������� api.
                    if($api->getNamespase() !== $ns)
                        unset($api);
                }
            }
        }
        if($api) {
            externalApi::$apis[$ns] = $api;
        }
        return $api;
    }

    /**
     * ������� �����������.
     *
     * @param externalSession $sess   ������ ������.
     */
    function __construct($sess) {
        $this->_sess = $sess;
    }

    /**
     * ������� ������������ ���� (uri) ������� API-������.
     * @return string
     */
    function getNamespase() {
        return $this->API_NAMESPACE;
    }

    /**
     * ������� ������� ������������ ���� ������� API-������.
     * @return string
     */
    function getDefaultPrefix() {
        return $this->API_DEFAULT_PREFIX;
    }
    
    /**
     * ��������� �������� �����. ���� ������������ ���� ����� ����� ����������� �� ���� ������, �� �������� ������ � NULL � ����������.
     * @see externalApi::_methodsDenied()
     *
     * @param string $method   ��� ������, �������� ����� ��������� ������ (���������� ��� ����������, ����������� ������� � �.�.)
     * @param array $args   ���������, ������� ����� �������� ������.
     * @return mixed    ��������� ������ ������.
     */
    function invoke($method, $args) {
        $xmethod = self::METHOD_PREFIX.$method;
        if(!method_exists($this, $xmethod))
            return $this->warning( EXTERNAL_WARN_UNDEFINED_METHOD );
        $this->_mName = $method;
        $this->_mCfg = $this->_methodsCfg ? $this->_methodsCfg[$this->_mName] : NULL;
        $denied = false;
        if(!method_exists(__CLASS__, $xmethod))
            $denied = $this->_methodsDenied();
        return $denied ? NULL : $this->$xmethod($args);
    }


    /**
     * �������� ��� ����������� ����������� �������� ������.
     */
    private function _decriptPasswd($passwd) {
        return $passwd;
    }
    

    /**
     * ���������� ��� �������� ����������� ����������� ������� ������������.
     * ������������� � ��������� ������������� ����, ���� �� ������� ���. ��������.
     *
     * @param object $user   ������������ (������������������ ��������� ������ users).
     * @return integer   ��� ������ ��� 0 -- ����� ��������������.
     */
    protected function _authDenied($user) {
        if(!$user->uid)
            return EXTERNAL_ERR_USER_NOTFOUND;
        if($user->is_banned)
            return EXTERNAL_ERR_USER_BANNED;
        if($user->active != 't')
            return EXTERNAL_ERR_USER_NOTACTIVE;
        return 0;
    }

    /**
     * ���������� ����� ������ ������� ������ ������ ������� ������������ ���� (����� ������� externalApi) ��� 
     * �������� ���� �� ����� ������.
     * �������� $this->_mName � $this->_mCfg.
     * ��������, � ������� freetray ��������� ������ ��� �����������, � ����� ���������������� ���������.
     *
     * @return integer   ��� ������ ��� 0 -- ����� ��������.
     */
    protected function _methodsDenied() {
        return 0;
    }



    /////// external protocol public functions //////////////////////////////////////////


    /**
     * �������� �������.
     */
    protected function x____test($args)
    {
        list($arg) = $args;
        return $arg;
    }

    /**
     * �������� �������.
     */
    protected function x____testError($args)
    {
        list($err_code) = $args;
        $this->error( $err_code, 'You have been fucking testError()' );
    }

    /**
     * �������� �������.
     */
    protected function x____testWarning($args)
    {
        list($err_code) = $args;
        $this->warning( $err_code, 'You have been fucking testWarning()' );
    }

    /**
     * ������������ ������������, � ������ ������ �������������� ������.
     * ���� ������� ��� ���� ����������� ����, �.�. ����� �������������� �� ������ �� ���.
     * ������ ������������ ������������ ���� ��� ������ ����� ������������, ��������, �� ������� ������ ���� ������ � ����������.
     * ����������� ����������� � $this->_authDenied().
     * Note: ����� ��������� ���, ��� ������ �����������, ������ auth() �� ������� ������������ ����. �� � ����� ������
     * ������ (� ������������ NS) ��� ����� ����� ����������, ���� ��������� ������� $this->_methodsDenied().
     *
     * @param string $login   ����� ������������.
     * @param string $passwd   ������ ������������ � md5.
     * @return int   EXTERNAL_TRUE, ���� ��� ��.
     */
    final
    protected function x____auth($args)
    {
        list($login, $passwd) = $args;
        if(!isset($passwd) || !isset($login))
            $this->error( EXTERNAL_ERR_INVALID_METHOD_ARG, 'Use auth(string login, string passwd)' );

        require_once(ABS_PATH.'/classes/users.php');
        $user = new users();
        $user->GetUserByLoginPasswd($login, users::hashPasswd($this->_decriptPasswd($passwd), 1));
        if(!$user->uid)
            $this->error( EXTERNAL_ERR_WRONG_AUTH );
        if($err = $this->_authDenied($user))
            $this->error( $err );

        $this->_sess->fillU($user);

        return EXTERNAL_TRUE;
    }

    /**
     * ��������� ����������� ���� �����/������.
     *
     * @param string $login   ����� ������������.
     * @param string $passwd   ������ ������������ � md5.
     * @return int   0:��� ��; N:��� ������.
     */
    final
    protected function x____checkAuth($args)
    {
        list($login, $passwd) = $args;
        if(!isset($passwd) || !isset($login))
            $this->error( EXTERNAL_ERR_INVALID_METHOD_ARG, 'Use checkAuth(string login, string passwd)' );

        require_once(ABS_PATH.'/classes/users.php');
        $user = new users();
        $user->GetUserByLoginPasswd($login, users::hashPasswd($this->_decriptPasswd($passwd), 1));
        return $this->_authDenied($user);
    }

    /**
     * ��������� ������ ������.
     *
     * @return int   �������?
     */
    final
    protected function x____refresh()
    {
        if(!$this->_sess->id)
            $this->error( EXTERNAL_ERR_NEED_AUTH );
        $this->_sess->refresh();
        return EXTERNAL_TRUE;
    }

    /**
     * ���������� ��� ���� ������ � ������� ���������.
     *
     * @return array   ���� ������.
     */
    protected function x____getErrCodes()
    {
        return $this->getErrCodes();
    }
    
    /**
     * ���������� ����� ���� ������� ������� API ������������ �������.
     *
     * @return array   ����� �������.
     */
    protected function x____getMethods()
    {
        $mm = get_class_methods($this);
        $rm = array();
        foreach($mm as $m) {
            if(strpos($m, self::METHOD_PREFIX) === 0)
                $rm[] = preg_replace('/^'.self::METHOD_PREFIX.'(.*)$/', '$1', $m);
        }
        return $rm;
    }
}
