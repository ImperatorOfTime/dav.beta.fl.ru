<?php

/**
 * ������������ ����� ��� ����� �������������.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class admin_parent {
    /**
     * ���������� ��������� �� �������� � �������
     */
    public $items_pp = 20;
    
    /**
     * ��������� �������
     *
     * @var array
     */
    protected $filter = array();
    
    /**
     * ��������� ����� SQL ������� 
     * 
     * @var array
     */
    protected $aSQL = array();
    
    /**
     * UID �������� ������������
     *
     * @var int
     */
    protected $curr_uid = 0;
    
    /**
     * ����� ������������
     * 
     * @var array
     */
    protected $user_permissions = array();
    
    /**
     * ����������� ������
     * 
     * @param int $items_pp ���������� ��������� �� ��������
     */
    function __construct( $items_pp ) {
        $items_pp = intval( $items_pp );
        
        if ( $items_pp > 0 ) {
        	$this->items_pp = $items_pp;
        }
    }
    
    /**
     * ��������� ���������� �� ������������ ������
     * 
     * @param  string $key ���� � ������� �������
     * @return bool true - ����������, false - ���
     */
    function isFilter( $key ) {
        return ( isset($this->filter[$key]) && $this->filter[$key] );
    }
    
    /**
     * ���������� �������� IP �������
     * 
     * ����� IP ������� ����� �������� * ����� � ��������� IP ������������� 0, � �������� - 255
     * ���������� ������ IP ������ � �����
     * 
     * ��������� �������� �� ���������� �������� (� ��������� IP):
     * 255.255.255.255 = 255.255.255.255
     * 255.*.255.255   = 255.0.255.255
     * 255.255.*.255   = 255.255.0.255
     * 255.255.255.*   = 255.255.255.0
     * 255.*.255.*     = 255.0.255.0
     * 255.*.*.255     = 255.0.0.255
     * 255.*.255       = 255.0.0.255
     * 255.255.*.*     = 255.255.0.0
     * 255.255.*       = 255.255.0.0
     * 255.*.*.*       = 255.0.0.0
     * 255.*.*         = 255.0.0.0
     * 255.*           = 255.0.0.0
     * 255             = 255.0.0.0
     * 
     * @param  string $error ��������� ��������� �� ������ ��� ������ ������
     * @param  string $fromIp ��������� IP �����
     * @param  string $toIp �������� IP �����
     * @return array 
     */
    function getIpRange( &$error, $fromIp = '', $toIp = '' ) {
        $aRet  = array();
        $error = '';
        
        $fromIp = trim( $fromIp );
        if ( $error = $this->validIp($fromIp, 'fromIP') ) {
            return array();
        }
        
        $toIp = trim( $toIp );
        if ( $error = $this->validIp($toIp, 'toIP') ) {
            return array();
        }
        
        $sIpFrom = $this->getIp( $fromIp, '0' );
        $sIpTo   = $this->getIp( $toIp, '255' );
        
        return array( 'ip_from' => $sIpFrom, 'ip_to' => $sIpTo );
    }
    
    /**
     * ��������� ����� IP ������
     * 
     * @param  string $ip IP �����
     * @param  string $errMsg ��������� �� ������
     * @return string ��������� �� ������ ��� ������ ������
     */
    function validIp( $ip, $errMsg ) {
        if ( trim($ip) ) {
            if ( !preg_match('/^([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\.(\*|[1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]|)){0,3}$/', $ip) ) {
                return $errMsg;
            }
        }
        return '';
    }
    
    /**
     * ��������� IP ����� �� �������
     * 
     * @param  string $sIp ������ IP ������
     * @param  string $sReplace �� ��� �������� ������ (������ 0 ��� 255)
     * @return string
     */
    function getIp( $sIp, $sReplace = '0' ) {
        $ip = '';
        
        if ( trim($sIp) ) {
            $ip = preg_replace('/(\.\*)+$/', '.*', $sIp);
            $cnt = substr_count( $ip, '.' );
            
            if ( !$cnt ) {
            	$ip .= '.*';
            	$cnt++;
            }
            
            $replace = str_repeat( '.'.$sReplace, 4 - $cnt );
            $ip      = str_replace( '.*', $replace, $ip );
        }
        
        return $ip;
    }
    
    
    /**
     * ����� �� �����
     * 
     * @param  string $permission ������
     * @param  array $user_permissions ���� ����
     * @return bool
     */
    function isAllowed( $permission,  $user_permissions ) {
        return ( in_array($permission, $user_permissions) || in_array('all', $user_permissions) );
    }
    
}