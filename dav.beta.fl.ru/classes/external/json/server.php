<?php
/**
 * ������ ��� ������ � API ����� �������� JSON
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class externalServer_JSON extends externalBase {
    /**
     * ���������� ���
     * 
     * @var string 
     */
    private $_sApi;
    
    /**
     * ���������� �����
     * 
     * @var string 
     */
    private $_sMethod;
    
    /**
     * ��������� ������
     * 
     * @var array
     */
    private $_aParams;
    
    /**
     * ����� ������
     * 
     * @var int 
     */
    private $_nErrorNum = EXTERNAL_NO_ERROR;
    
    /**
     * ������������ ������
     * 
     * @var array 
     */
    private $_aRespData = array();


    /**
     * ��� ������, ��������������� ������
     * 
     * @var string 
     */
    public $eHandler = 'setError';
    
    /**
     * ��� ������, ��������������� ��������������.
     * 
     * @var string 
     */
    public $wHandler = 'setError';

    /**
     * ���������� ��������� ������ � ����������� �� ������ ���������.
     * 
     * @param array $req   ��������� �������:
     *                       'protocol-version' => 1.0, -- ������ ���������
     *                       'data' => file_get_contents('php://input') -- ���� �������.
     * @return object
     */
    static function getInst( $req ) {
        if ( 1 == (int)$req['protocol-version'] )
            return new externalServer_JSON( $req['data'] );
    }
    
    /**
     * ����������� ������
     * 
     * @param string $json   ����� JSON-�������
     */
    function __construct( $json ) {
        $this->regErrorHandler();
        $this->regWarnHandler();
        $aParams = json_decode( $json, true );
        
        if ( is_array($aParams) && $aParams ) {
            if ( $aParams['api'] ) {
                $this->_sApi = $aParams['api'];
                unset($aParams['api']);
            }

            if ( $aParams['method'] ) {
                $this->_sMethod = $aParams['method'];
                unset($aParams['method']);
            }
            
            $this->_aParams = $aParams;
        }
    }
    
    /**
     * ��������� �������, �������� ������ �������.
     */
    function handle() {
        $sName = 'http://www.free-lance.ru/external/api/' . $this->_sApi;
        
        if ( $api = externalApi::getInst($sName, $this->_sess) ) {
            $this->_aRespData = $api->invoke( $this->_sMethod, $this->_aParams );
            $this->response();
        }
        else {
            $this->error( EXTERNAL_WARN_UNDEFINED_API );
        }
    }
    
    /**
     * ������������ ������, ����������� ������.
     * 
     * @param mixed $err �������� ������ (������ -- ����������������� ��������).
     */
    function setError( $err ) {
        $this->_nErrorNum = $err['code'];
        $this->_aRespData = array();
        $this->response();
    }
    
    /**
     * ��������� �����, ����������� ������.
     */
    function response() {
        $aResult = array( 
            'error'      => $this->_nErrorNum, 
            'error_text' => self::$_aError[$this->_nErrorNum], 
            'data'       => $this->_aRespData
        );
        
        die( json_encode($aResult, empty($this->_aRespData) ? JSON_FORCE_OBJECT : 0) );
    }
}