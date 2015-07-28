<?php
/**
 * ���������� ������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/admin_parent.php");

/**
 * ����� ��� ������ � ����� ������� IP
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class gray_ip extends admin_parent {
    /**
     * ��������� �� �������
     * 
     * @var array
     */
    static $error = array(
        'fromIP' => '<div style="color: red; padding-top: 10px;">��������� IP ������ �������� �� ����� �� 0 �� 255.<br/>���������� � �����. ������ ����������� ����� ����� ����������� 0</div>',
        'toIP'   => '<div style="color: red; padding-top: 10px;">�������� IP ������ �������� �� ����� �� 0 �� 255.<br/>���������� � �����. ������ ����������� ����� ����� ����������� 255</div>'
    );
    
    /**
     * ����������� ������
     * 
     * @param int $items_pp ���������� ��������� �� ��������
     */
    function __construct( $items_pp ) {
        parent::__construct( $items_pp );
    }
    
    /**
     * ���������� ������ �������, ������� ��������� IP � ����� ������
     * 
     * @param  int $objCode �����������. ��� �������
     * @return array
     */
    function getAdmins() {
        return $GLOBALS['DB']->rows( 'SELECT u.uid, u.login FROM users u 
            INNER JOIN gray_ip_primary p ON p.admin_id = u.uid
            GROUP BY u.uid, u.login
            ORDER BY u.login' 
        );
    }
    
    /**
     * ���������� ������ ������������ �� ������ ������ IP
     * 
     * @param  int $nUid UID ������������
     * @return array
     */
    function getPrimaryIpByUid( $nUid = 0 ) {
        return $GLOBALS['DB']->rows( 'SELECT admin_id, ip, user_login FROM gray_ip_primary WHERE user_id = ?i', $nUid );
    }
    
    /**
     * ��������� ������ ������������ � ����� ������ IP
     * 
     * @param  int $nUserId UID ������������ �������� ��������� � ����� ������
     * @param  string $sUserLogin ����� ������������ �������� ��������� � ����� ������
     * @param  int $nAdminId UID ������
     * @param  array $aIp ������ IP ������� ��� ������ � ����� IP �������
     * @return bool true - �����, false - ������
     */
    function addPrimaryIp( $nUserId = 0, $sUserLogin = '', $nAdminId = 0, $aIp = array() ) {
        $bRet  = true;
        $aData = array();
        
        if ( $nUserId && $nAdminId && $aIp ) {
        	if ( !is_array($aIp) ) {
        		$aIp = array( $aIp );
        	}
        	
        	foreach ( $aIp as $sIp ) {
        		$aData[] = array( 
                    'user_id'    => $nUserId, 
                    'user_login' => $sUserLogin, 
                    'admin_id'   => $nAdminId, 
                    'ip'         => $sIp 
        		);
        	}
        	
        	$GLOBALS['DB']->insert( 'gray_ip_primary', $aData );
        	$bRet = ( !$GLOBALS['DB']->error ) ? true : false;
        }
        
        return $bRet;
    }
    
    /**
     * ������� ������ ������������ �� ������ ������ IP
     * 
     * @param  int $nUserId UID ������������
     * @param  array $aIp ������ IP ������� ��� ������ � ����� IP �������
     * @return bool true - �����, false - ������
     */
    function deletePrimaryIp( $nUserId = 0, $aIp = array() ) {
        $bRet = true;
        
        if ( $nUserId && $aIp ) {
            if ( !is_array($aIp) ) {
        		$aIp = array( $aIp );
        	}
        	
        	$GLOBALS['DB']->query( 'DELETE FROM gray_ip_primary WHERE user_id = ?i AND ip IN (?l)', $nUserId, $aIp );
        	$bRet = ( !$GLOBALS['DB']->error ) ? true : false;
        }
        
        return $bRet;
    }
    
    /**
     * ��������� ������ ������������ � ����� ������ IP
     * 
     * ���� ���������� ����� ������ �������. ������� ���������� ����� ������ �� ������.
     * �� ������ ������ �� ������� ������ ��������� - ������ �� �������������, ����� �����������.
     * ����� ��� ������� � ������ ������� - �� ��������� ��� ��� �� ���� ��� ����� ��������� �������������� �����������.
     * 
     * @param  int $nUserId UID ������������
     * @param  string $sUserLogin ����� ������������
     * @param  int $nAdminId UID ������
     * @param  array $aIp ������ IP ������� ��� ������ � ����� IP �������
     * @param  bool $bDel ���������� ���� �� �������� �������
     * @return bool true - �����, false - ������
     */
    function updatePrimaryIp( $nUserId = 0, $sUserLogin = '', $nAdminId = 0, $aIp = array(), &$bDel ) {
        $bRet = true;
        $bDel = false;
        
        if ( $nUserId && $nAdminId ) {
            if ( !is_array($aIp) ) {
        		$aIp = array( $aIp );
        	}
        	
        	$aOldIp = $GLOBALS['DB']->col( 'SELECT ip FROM gray_ip_primary WHERE user_id = ?i', $nUserId );
        	
        	if ( !$GLOBALS['DB']->error ) {
            	if ( $aAdd = array_diff($aIp, $aOldIp) ) {
            	    $bRet = self::addPrimaryIp( $nUserId, $sUserLogin, $nAdminId, $aAdd );
            	}
            	
            	if ( $bRet && $aDel = array_diff($aOldIp, $aIp) ) {
            	    $bDel = true;
            	    $bRet = self::deletePrimaryIp( $nUserId, $aDel );
            	}
        	}
        }
        
        return $bRet;
    }
    
    /**
     * ������ ��� IP ������������ �� ������ ������ � ������ ��� ��� ������������� �����������
     * 
     * @param  int $nUserId UID ������������
     * @return bool true - �����, false - ������
     */
    function deletePrimaryUser( $nUserId = 0 ) {
        return self::updatePrimaryIp( $nUserId, '', -1, array() );
    }
    
    /**
     * ���������� ������ ID ������� �� ������ ������ ������� ��������� � IP ����������� ������������
     * 
     * @param  string $sRegIp IP ����� ��� ����������� ������������
     * @return array
     */
    function getGrayListByRegIp( $sRegIp ) {
        $aRet = $GLOBALS['DB']->col( 'SELECT id FROM gray_ip_primary WHERE ip = ?', $sRegIp );
        
        return $aRet;
    }
    
    /**
     * �������� ������������ ������������������� � IP �� ������ ������
     * 
     * @param  int $nUserId UID ������������
     * @param  string $sUserLogin ����� ������������
     * @param  int $sUserRole ���� ������������: 1 - ������������, 0 - ���������
     * @param  array $aPrimaryId ������ ID ������� �� ������ ������ @see self::getGrayListByRegIp
     * @return bool true - �����, false - ������
     */
    function addSecondaryIp( $nUserId = 0, $sUserLogin = '', $sUserRole = '', $aPrimaryId = array() ) {
        $bRet  = true;
        $sDate = date('Y-m-d H:i:s');
        
        if ( $nUserId && $aPrimaryId ) {
            if ( !is_array($aPrimaryId) ) {
        		$aPrimaryId = array( $aPrimaryId );
        	}
        	
        	foreach ( $aPrimaryId as $sId ) {
        		$aData[] = array( 
                    'user_id'    => $nUserId, 
                    'user_login' => $sUserLogin, 
                    'is_emp'     => $sUserRole, 
                    'primary_id' => $sId, 
                    'reg_date'   => $sDate 
        		);
        	}
        	
        	$GLOBALS['DB']->insert( 'gray_ip_secondary', $aData );
        	$bRet = ( !$GLOBALS['DB']->error ) ? true : false;
        }
        
        return $bRet;
    }
    
    /**
     * ������� ������������ ������������� �����������
     * 
     * @param  array $aSecondaryId ������ UID ������������� �� gray_ip_secondary ��� ������ � ����� UID
     * @return bool true - �����, false - ������
     */
    function deleteSecondaryIp( $aSecondaryId = array() ) {
        $bRet = true;
        
        if ( $aSecondaryId ) {
        	if ( !is_array($aSecondaryId) ) {
        		$aSecondaryId = array( $aSecondaryId );
        	}
            
        	$GLOBALS['DB']->query( 'DELETE FROM gray_ip_secondary WHERE user_id IN (?l)', $aSecondaryId );
        	$bRet = ( !$GLOBALS['DB']->error ) ? true : false;
        }
        
        return $bRet;
    }
    
    /**
     * ������� ��� ������������� ����������� ������������� ���������� IP
     * 
     * @param  array $aSecondaryId ������ UID ������������� �� gray_ip_secondary
     * @return bool true - �����, false - ������
     */
    function deleteSecondaryIpByPrimary( $aSecondaryId = 0 ) {
        $GLOBALS['DB']->query( 'DELETE FROM gray_ip_secondary WHERE user_id IN (?l)', $aSecondaryId );
        return ( !$GLOBALS['DB']->error ) ? true : false;
    }
    
    /**
     * ���������� ����� ������ IP
     * 
     * @param  int $count ���������� ���������� ������� �������������� �������� �������
     * @param  array $filter ��������� ������� ����
     * @param  int $page ����� ������� ��������
     * @return array
     */
    function getGrayIpList( &$count, $filter, $page = 1 ) {
        $this->filter = $filter;
        
        return $this->getGrayIp( $count, $page );
    }
    
    /**
     * ���������� ����� ������ IP
     * 
     * @param  int $count ���������� ���������� ������� �������������� �������� �������
     * @param  int $page ����� ������� ��������
     * @param  string order ��� ����������
     * @param  int $direction ������� ����������: 0 - �� ��������, �� 0 - �� �����������
     * @param  bool $unlimited �����������. ���������� � true ���� ����� �������� ��� ������ (��� ������������� ������)
     * @return array
     */
    function getGrayIp( &$count, $page = 1, $order = 'general', $direction = 0, $unlimited = false ) {
        $this->aSQL = array();
        $offset     = $this->items_pp * ($page - 1);
        
        // ������ ������
        $this->_setWhere();
        $this->_setOrderBy( $order, $direction );
        
        // �������� ������� ��������� ��������
        $sQuery = 'SELECT p.id AS p_id, p.user_id AS p_uid, p.ip, p.user_login AS p_login, 
            s.id AS s_id, s.user_id AS s_uid, date_trunc(\'day\', s.reg_date) AS reg_date, s.user_login AS s_login, s.is_emp 
            FROM ( SELECT gp.id, MAX(gs.reg_date) AS max_date FROM gray_ip_primary gp 
                INNER JOIN gray_ip_secondary gs ON gs.primary_id = gp.id '
            . ( $this->aSQL['where'] ? ' WHERE ' . implode(' AND ', $this->aSQL['where']) : '' ) 
            . ' GROUP BY gp.id ORDER BY max_date DESC '
            . ( $unlimited ? '' : ' LIMIT ' . $this->items_pp . ' OFFSET ' . $offset)
            .') AS p1 
            INNER JOIN gray_ip_primary as p ON p1.id = p.id
            INNER JOIN gray_ip_secondary s ON s.primary_id = p.id'
            . ' ORDER BY ' . implode(', ', $this->aSQL['order_by']);
//echo $GLOBALS['DB']->parse( $sQuery );
        $log = $GLOBALS['DB']->rows( $sQuery );
        
        if ( $GLOBALS['DB']->error || !$log ) {
            return array();
        }
        
        // �������� ����� ���������� ��������� ��������
        $sQuery = 'SELECT COUNT(gp.id) FROM gray_ip_primary gp '
            . ( $this->aSQL['where'] ? ' WHERE ' . implode(' AND ', $this->aSQL['where']) : '' ) ;
            
        $count = $GLOBALS['DB']->val( $sQuery );
//echo $GLOBALS['DB']->parse( $sQuery );
        
        return $log;
    }
    
    /**
     * �������� WHERE ����� SQL ������� ������ ������ IP
     */
    function _setWhere() {
        $this->aSQL['where'][] = 'secondary_cnt > 0';
        
        if ( self::isFilter('primary_id') ) {
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'gp.id  = ?i', $this->filter['primary_id'] );
        }
        
        if ( self::isFilter('admin_id') ) {
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'gp.admin_id = ?i', $this->filter['admin_id'] );
        }
        
        if ( self::isFilter('search_name') ) {
            $sSearch = pg_escape_string( $this->filter['search_name'] );
            $this->aSQL['where'][] = "(gp.user_login ILIKE '%{$sSearch}%' OR EXISTS (SELECT 1 FROM gray_ip_secondary gs WHERE gs.primary_id = gp.id AND gs.user_login ILIKE '%{$sSearch}%'))";
        }
        
        if ( $this->isFilter('ip_from') || $this->isFilter('ip_to') ) {
            $nLongIpF = $this->isFilter('ip_from') ? ip2long($this->filter['ip_from']) : 0;
            $nLongIpT = $this->isFilter('ip_to')   ? ip2long($this->filter['ip_to'])   : 0;
            
            $this->aSQL['where'][] = '('
                . ($nLongIpF ? $GLOBALS['DB']->parse('gp.ip >= ?', $this->filter['ip_from']) : '') 
                . ($nLongIpT ? ($nLongIpF ? ' AND ' : '') . $GLOBALS['DB']->parse('gp.ip <= ?', $this->filter['ip_to']) : '') 
                . ')';
        }
    }
    
    /**
     * �������� ORDER BY ����� SQL ������� ������ ������ IP
     *
     * @param string $order ��� ���������� - �� ������������
     * @param int $direction ������� ����������: 0 - �� ��������, �� 0 - �� ����������� - �� ������������
     */
    function _setOrderBy( $order = "general", $direction = 0 ) {
        $dirSql = ( !$direction ? 'DESC' : 'ASC' );
        
        switch ( $order ) {
            default:
                $this->aSQL['order_by'][] = "p1.max_date DESC";
                $this->aSQL['order_by'][] = "p.ip ASC";
                $this->aSQL['order_by'][] = "p.id ASC";
                $this->aSQL['order_by'][] = "s.reg_date DESC NULLS LAST";
                $this->aSQL['order_by'][] = "s.user_id ASC";
                break;
        }
    }
}