<?php
/**
 * ���������� ������
 */
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_parent.php' );

/**
 * 0023233: �������� �������, ���� ����������. ������.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class traffic_stat extends admin_parent {
    /**
     * ������ � ������� �� ����� ����������
     * 
     * @var array 
     */
    private $aDomains = array();
    
    /**
     * ������ ������ memBuff
     * 
     * @var object
     */
    private $oMemBuff = null;
    
    /**
     * ������ ���� ������ ����������
     * 
     * @var object 
     */
    private $oStatDB = null;
    
    /**
     * ������ �������. ID ������
     * 
     * @var string 
     */
    private $sDomainId = '';
    
    /**
     * ������ �������. ������ ��������� ID ������ $sDomainId
     * 
     * @var string 
     */
    private $sDomainIdError = '';
    
    /**
     * ������ �������. �����
     * 
     * @var string 
     */
    private $sDomainName = '';
    
    /**
     * ������ �������. ������ ��������� ������ $sDomainName
     * 
     * @var string 
     */
    private $sDomainNameError = '';
    
    /**
     * ������ �������. ������������ �� ����� � ������ ������
     * 
     * @var string 
     */
    private $sDomainActive = 't';

    /**
     * ������ �������. ������� ����, ��� ���� ������ ����������� ��� ����������
     * 
     * @var type 
     */
    private $bDomainSet = false;
    
    /**
     * ������ ����������. ID ������. 0 - ��� ������
     * 
     * @var int 
     */
    private $nFilterDomainId = 0;
    
    /**
     * ������ ����������. ������ ID ������
     * 
     * @var int 
     */
    private $sFilterDomainIdError = '';

    /**
     * ������ ����������. �� ����� ������ �������� ����������
     * 
     * @var string
     */
    private $sPeriod = 'today';
    
    /**
     * ������ ����������. ��������� ���� �������
     * 
     * @var string 
     */
    private $sDateFrom = 0;
    
    /**
     * ������ ����������. �������� ���� �������
     * 
     * @var string 
     */
    private $sDateTo = 0;
    
    /**
     * ������ ����������. ������ ��������� ��� � �������
     * 
     * @var string 
     */
    private $sDateError = '';
    
    /**
     * ������ ����������. ������ ��������� ��� � �������. ���������
     * 
     * @var string 
     */
    private $sDateFromError = '';
    
    /**
     * ������ ����������. ������ ��������� ��� � �������. ��������
     * 
     * @var string 
     */
    private $sDateToError = '';
    
    /**
     * ������ �� �����������
     * 
     * @var array 
     */
    private $aStats = array();
    
    /**
     * ����� ������� � UID ������ ������� �� ����� ��������� � ����������
     * 
     * @var string 
     */
    private $sIgnoreInStats = '';
    
    /**
     * ���� �������� �� op_codes ��� PRO
     * 
     * @var array 
     */
    static $aProCode = array( 1, 2, 3, 4, 5, 6, 15, 48, 49, 50, 51, 76 );

    /**
     * ����� ����� ������� � ��������
     */
    const MEMBUFF_TTL = 86400;
    
    /**
     * ����� ����� cookie, �� ������� ������������ ����������� � ������, � ��������: 7 ����
     */
    const COOKIE_TTL = 604800;

    /**
     * ����������� ������
     */
    function __construct() {
        parent::__construct( 0 );
        
        $this->oStatDB  = new DB( 'stat' );
        $this->oMemBuff = new memBuff();
        //$this->oMemBuff->delete( 'traffic_stat_domains' );
        $this->aDomains = $this->oMemBuff->get( 'traffic_stat_domains' );
        
        // ���� � ������� ����������, ������ � ����
        if ( $this->aDomains === false ) {
            $aDomains = $GLOBALS['DB']->rows('SELECT * FROM traffic_stat_domains ORDER BY id');
            
            if ( $aDomains ) {
                foreach ( $aDomains as $aOne ) {
                    $this->aDomains[$aOne['id']] = $aOne;
                }
            }
            
            $this->oMemBuff->set( 'traffic_stat_domains', $this->aDomains, self::MEMBUFF_TTL );
        }
    }
    
    /**
     * ���������� ����������
     * 
     * @return array
     */
    function getStats() {        
        if ( $this->aDomains && !$this->sFilterDomainIdError && !$this->sDateError 
            && !$this->sDateFromError && !$this->sDateToError 
        ) {
            $this->_getIgnoreInStats();                    // ��������������� ��������
            $this->_getStatsIp();                          // ��������
            $this->_getStatsRegistration();                // ����������
            $this->_getStatOPProject( '' );                // ������� �������. ���
            $this->_getStatOPProject( 0 );                 // ������� �������. ������� � ������
            //$this->_getStatOPProject( 1 );               // ������� �������. ��������� ������
            //$this->_getStatOPProject( 2 );               // ������� �������. ��������� ������
            $this->_getStatOPProject( 3 );                 // ������� �������. ��������� ������� �����
            $this->_getStatOPProject( 4 );                 // ������� �������. � ����
            //$this->_getStatOPProject( '', false, true ); // ������� ������� (�����). ��� 
            //$this->_getStatOPProject( 0, false, true );  // ������� ������� (�����). ������� � ������
            //$this->_getStatOPProject( 1, false, true );  // ������� ������� (�����). ��������� ������
            //$this->_getStatOPProject( 2, false, true );  // ������� ������� (�����). ��������� ������
            //$this->_getStatOPProject( 3, false, true );  // ������� ������� (�����). ��������� ������� �����
            //$this->_getStatOPProject( 4, false, true );  // ������� ������� (�����). � ����
            $this->_getStatOPProject( '', true );          // ������� ��������. ��� 
            $this->_getStatOPProject( 0, true );           // ������� ��������. ������� � ������
            //$this->_getStatOPProject( 1, true );         // ������� ��������. ��������� ������
            //$this->_getStatOPProject( 2, true );         // ������� ��������. ��������� ������ 
            $this->_getStatOPProject( 3, true );           // ������� ��������. ��������� ������� �����
            //$this->_getStatOPProject( '', true, true );  // ������� �������� (�����). ���
            //$this->_getStatOPProject( 0, true, true );   // ������� �������� (�����). ������� � ������
            //$this->_getStatOPProject( 1, true, true );   // ������� �������� (�����). ��������� ������
            //$this->_getStatOPProject( 2, true, true );   // ������� �������� (�����). ��������� ������
            //$this->_getStatOPProject( 3, true, true );   // ������� �������� (�����). ��������� ������� �����
            $this->_getStatIncome();                       // ������ ������� �� ���� ��������� ��������
            
            $this->_getStatByOpCode( self::$aProCode, 'pro', true ); // pro ����������, pro ������������
            //$this->_getStatByOpCode( array(114), 'testpro1fm' );   // test-pro 1fm ����������
            $this->_getStatByOpCode( array(47), 'testpro' );         // test-pro ����������
            $this->_getStatByOpCode( array(117), 'verify' );         // ����������� ����� ff
            $this->_getStatByOpCode( array(10, 11), 'first' );       // ����� �� ������
            $this->_getStatByOpCode( array(19), 'first_cat' );       // ����� � ����� ��������
            $this->_getStatByOpCode( array(20), 'first_cat_in' );    // ����� ������ ��������
            $this->_getStatByOpCode( array(21), 'first_cho' );       // ��������� ������� �������� ����������
            $this->_getStatByOpCode( array(65), 'ppfm' );            // �������� �� ������� (���)
            $this->_getStatByOpCode( array(55), 'ppsms' );           // �������� �� ������� (sms)
            $this->_getStatByOpCode( array(73,109, 111), 'ppcfm' );  // �������� � ��������
            $this->_getStatByOpCode( array(70), 'login' );           // ������� ��������� ������
            $this->_getStatByOpCode( array(74), 'unlock' );          // ������� �������������
            $this->_getStatByOpCode( array(45), 'mass_accept' );     // ������� ��������. ��������
            $this->_getStatByOpCode( array(46), 'mass_reject', false, false ); // ������� ��������. ����������
            $this->_getStatByOpCode( array(45), 'mass_new', false, false ); // ������� ��������. �����
            $this->_getStatByOpCode( array(71), 'pwsms', false, false ); // �������������� ������ (sms)
        }
        
        return $this->aStats;
    }
    
    /**
     * ������� ���������� ��������� �� ������� ���� � ������ �� � ���� ����� �������
     */
    function calculateStatsIp() {
        $this->sDateFrom = strtotime('-1 day');
        $this->sDateTo   = strtotime('-1 day');
        
        $this->_getStatsIp();
        
        $this->oStatDB->query( 'DELETE FROM traffic_stat ' . $this->_getStatsWhere() );
        
        if ( $this->aStats ) { 
            foreach ( $this->aStats as $sId => $aOne ) {
                $aSqlData = array( 
                    'domain_id' => $sId, 
                    'cnt'       => $aOne['ip'], 
                    'reg_date'  => date( 'Y-m-d H:i:s', $this->sDateFrom ) 
                );
                
                $this->oStatDB->insert( 'traffic_stat', $aSqlData );
            }
        }
    }
    
    /**
     * ������� ���������� �� ���������
     * 
     * @return array
     */
    private function _getStatsIp() {
        $sQuery = 'SELECT d AS id, COALESCE(s.cnt, 0) AS cnt 
            FROM unnest(array[' . $this->_getStatsFrom() . ']) d 
            LEFT JOIN (
                SELECT domain_id, SUM(cnt) AS cnt
                FROM traffic_stat 
                ' . $this->_getStatsWhere() . '
                GROUP BY domain_id
            ) AS s ON s.domain_id = d';
        
        $aRows = $this->oStatDB->rows( $sQuery );
        
        if ( $aRows ) {
            foreach ( $aRows as $aOne ) {
                if ( !isset($this->aStats[$aOne['id']]) ) {
                    $this->aStats[$aOne['id']] = array();
                }
                
                $this->aStats[$aOne['id']]['ip'] = intval( $aOne['cnt'] );
            }
        }
    }
    
    /**
     * ������� ���������� �� ������������
     * 
     * @return array
     */
    private function _getStatsRegistration() {
        $sQuery = 'SELECT d AS id, e AS emp, COALESCE(u.cnt, 0) AS cnt
            FROM unnest(array[' . $this->_getStatsFrom() . ']) d 
            INNER JOIN unnest(array[true, false]) e ON true 
            LEFT JOIN (
                SELECT domain_id, COUNT(1) AS cnt, is_emp 
                FROM traffic_stat_uids
                ' . $this->_getStatsWhere() . '
                GROUP BY domain_id, is_emp 
            ) AS u ON u.domain_id = d AND u.is_emp = e';
        
        $aRows = $GLOBALS['DB']->rows( $sQuery );
        
        if ( $aRows ) {
            foreach ( $aRows as $aOne ) {
                if ( !isset($this->aStats[$aOne['id']]) ) {
                    $this->aStats[$aOne['id']] = array();
                }
                
                if ( !isset($this->aStats[$aOne['id']]['reg']) ) {
                    $this->aStats[$aOne['id']]['reg'] = array();
                }
                
                $this->aStats[$aOne['id']]['reg'][$aOne['emp']] = intval( $aOne['cnt'] );
            }
        }
    }
    
    /**
     * ������� ���������� �� ������� �������� (������� �������, ������� ������� �� ������, ��������, �������� �� ������)
     * 
     * @param  integer $type ��� ������� ������ (0 - �������, 1 - ���, 2 - ��������� ������, 3 - �������� �������)
     * @param  boolean $is_konkurs ����� ������ �� �������� ��� ���
     * @param  boolean $is_bonus ����� ������ �� ������� ��� ���
     * @return array [����� � FM, ���-�� ��������]
     */
    private function _getStatOPProject( $type = '', $is_konkurs = false, $is_bonus = false ) {
        $sWhere = $this->_getStatsWhere( 'ac.op_date' );
        $aWhere = array();
        
        if ( $type !== '' ) {
            $aWhere[] = 'pay_type = ' . $type;
            $sSelect  = ' SUM(round(p.ammount, 2)) AS sum, COUNT(p.*) AS cnt ';
        } 
        else {
            $sSelect = ' SUM(round(ac.'. ( $is_bonus ? 'bonus_' : '' ) .'ammount,2)) AS sum, COUNT(ac.*) AS cnt ';
        }
        
        if ( $is_konkurs ) {
            $aWhere[] = 'ac.op_code IN (9, 86, 106) AND ac.bonus_ammount '. ( $is_bonus ? '<>' : '=' ) . ' 0';
        } 
        else {
            $aWhere[] = $is_bonus ? 'ac.op_code = 54' : 'ac.op_code IN (8,53)';
        }
        
        $sWhere .= ( $sWhere ? ' AND ' : ' WHERE ' ) . implode( ' AND ', $aWhere );
        
        if ( $type === '' ) {
            $sQuery = 'SELECT d AS id, COALESCE(s.sum, 0) AS sum, COALESCE(s.cnt, 0) AS cnt 
                FROM unnest(array[' . $this->_getStatsFrom() . ']) d 
                LEFT JOIN ( 
                    SELECT u.domain_id, '. $sSelect .' 
                    FROM traffic_stat_uids u 
                    INNER JOIN account a ON a.uid = u.uid 
                    INNER JOIN account_operations as ac ON a.id = ac.billing_id '. $this->sIgnoreInStats 
                    . $sWhere . '
                    GROUP BY u.domain_id 
                ) AS s ON s.domain_id = d';
        }
        else {
            $sQuery = 'SELECT d AS id, COALESCE(s.sum, 0) AS sum, COALESCE(s.cnt, 0) AS cnt 
                FROM unnest(array[' . $this->_getStatsFrom() . ']) d 
                LEFT JOIN ( 
                    SELECT u.domain_id, '. $sSelect .' 
                    FROM traffic_stat_uids u 
                    INNER JOIN projects prj ON prj.user_id = u.uid 
                    INNER JOIN projects_payments p ON prj.id = p.project_id 
                    INNER JOIN account_operations ac ON p.opid = ac.id 
                    INNER JOIN account a ON a.id = ac.billing_id '. $this->sIgnoreInStats 
                    . $sWhere . ' 
                    GROUP BY u.domain_id 
                ) AS s ON s.domain_id = d';
        }
        
        $aRows = $GLOBALS['DB']->rows( $sQuery );
        
        if ( $aRows ) {
            foreach ( $aRows as $aOne ) {
                if ( !isset($this->aStats[$aOne['id']]) ) {
                    $this->aStats[$aOne['id']] = array();
                }
                
                if ( !isset($this->aStats[$aOne['id']]['spent']) ) {
                    $this->aStats[$aOne['id']]['spent'] = 0;
                }
                
                $sKey = ($is_konkurs ? 'contest' : 'project') . '_'. ($is_bonus ? 'bonus_' : '') . ($type !== '' ? $type : 'all');
                $aOne['sum'] = abs( $aOne['sum'] );
                
                if ( $type === '' && !($is_konkurs == false && $is_bonus == true) || $type == '4' ) {
                    $this->aStats[$aOne['id']]['spent'] += $aOne['sum'];
                }
                
                $this->aStats[$aOne['id']][$sKey] = $aOne;
            }
        }
    }
    
    /**
     * ������� ���������� �� ������� ������� �� ���� ��������� ��������
     * 
     * @return array [����� � ������ ��������� �������, ����� � FM]
     */
    private function _getStatIncome() {
        $sWhere = $this->_getStatsWhere( 'ac.op_date' );
        $sQuery = 'SELECT d AS id, COALESCE(s.trsum, 0) AS trsum, COALESCE(s.sum, 0) AS sum, 
                COALESCE(s.cnt, 0) AS cnt, ps.ps, s.profit 
            FROM unnest(array[' . $this->_getStatsFrom() . ']) d 
            INNER JOIN unnest(array[1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]) ps ON true 
            LEFT JOIN ( 
                SELECT u.domain_id, SUM(trs_sum)::integer AS trsum, SUM(ammount) AS sum, 
                    COUNT(trs_sum) AS cnt, payment_sys AS ps, SUM(coalesce(so.profit, trs_sum*30*0.5)) as profit 
                FROM traffic_stat_uids u 
                INNER JOIN account a ON a.uid = u.uid 
                INNER JOIN account_operations ac ON a.id = ac.billing_id '. $this->sIgnoreInStats .' 
                LEFT JOIN sms_operations so ON so.operation_id = ac.id 
                '. $sWhere . ( $sWhere ? ' AND ' : ' WHERE ' ) .' op_code = 12 
                    AND (payment_sys <> 7 OR so.id IS NULL OR so.profit IS NOT NULL) 
                GROUP BY u.domain_id, ps 
            ) AS s ON s.domain_id = d AND s.ps = ps.ps';
        
        $aRows = $GLOBALS['DB']->rows( $sQuery );
        
        if ( $aRows ) {
            foreach ( $aRows as $aOne ) {
                if ( !isset($this->aStats[$aOne['id']]) ) {
                    $this->aStats[$aOne['id']] = array();
                }
                
                if ( !isset($this->aStats[$aOne['id']]['income']) ) {
                    $this->aStats[$aOne['id']]['income'] = 0;
                }
                
                $this->aStats[$aOne['id']]['income']             += $aOne['sum'];
                $this->aStats[$aOne['id']]['income_'.$aOne['ps']] = $aOne;
            }
        }        
    }
    
    /**
     * ������� ���������� �� ������������ ���������
     * 
     * @param mixed $mOpCode ������ ����� ������ ���� �������� �� op_codes ��� ������ �����
     * @param string $sKey ���� ��� ������� �������� ������ � ����� ������ �����������
     * @param bool $bSepFreeEmp ���������� � true ���� ����� ������� �������� �� ����������� � �������������
     * @param bool $bSpent ����������� ���� � ����� ������ �� ������
     */
    private function _getStatByOpCode( $mOpCode = '0', $sKey = '', $bSepFreeEmp = false, $bSpent = true ) {
        if ( !is_array($mOpCode) ) {
            $mOpCode = explode( ',', $mOpCode );
        }
        
        $sAnd = $sJoin = '';
        
        if ( $sKey == 'mass_new' ) {
            $sAnd  = 'm.is_accepted IS NULL';
            $sJoin = 'INNER JOIN mass_sending m ON ao.id = m.account_op_id';
        }
        
        if ( $sKey == 'mass_accept' ) {
            $sAnd  = 'm.is_accepted = true';
            $sJoin = 'INNER JOIN mass_sending m ON ao.id = m.account_op_id';
        }
        
        $sWhere = $this->_getStatsWhere( 'ao.op_date', $sAnd );
        $sQuery = 'SELECT d AS id, COALESCE(s.trsum, 0) AS trsum, COALESCE(s.sum, 0) AS sum, 
                COALESCE(s.cnt, 0) AS cnt '. ( $bSepFreeEmp ? ', e AS emp ' : '' ) .'
            FROM unnest(array[' . $this->_getStatsFrom() . ']) d 
            '. ( $bSepFreeEmp ? 'INNER JOIN unnest(array[true, false]) e ON true ' : '' ) .'    
            LEFT JOIN ( 
                SELECT u.domain_id, SUM(ao.trs_sum) as trsum, SUM(round(ao.ammount,2)) as sum, COUNT(ao.id) as cnt 
                '. ( $bSepFreeEmp ? ', u.is_emp  ' : '' ) .'
                FROM traffic_stat_uids u 
                INNER JOIN account a ON u.uid = a.uid 
                INNER JOIN account_operations ao ON a.id = ao.billing_id '. $this->sIgnoreInStats .' 
                '. $sJoin .'     
                '. $sWhere . ( $sWhere ? ' AND ' : ' WHERE ' ) . 'ao.op_code IN (?l)
                GROUP BY u.domain_id' . ( $bSepFreeEmp ? ', is_emp  ' : '' ) .' 
            ) AS s ON s.domain_id = d' . ( $bSepFreeEmp ? ' AND s.is_emp = e' : '' );
         
        $aRows = $GLOBALS['DB']->rows( $sQuery, $mOpCode );
        
        if ( $aRows ) {
            foreach ( $aRows as $aOne ) {
                $aOne['sum'] = abs( $aOne['sum'] );
                
                if ( !isset($this->aStats[$aOne['id']]) ) {
                    $this->aStats[$aOne['id']] = array();
                }
                
                if ( $bSpent ) {
                    if ( !isset($this->aStats[$aOne['id']]['spent']) ) {
                        $this->aStats[$aOne['id']]['spent'] = 0;
                    }
                    
                    $this->aStats[$aOne['id']]['spent'] += $aOne['sum'];
                }
                
                $this->aStats[$aOne['id']][$sKey.($bSepFreeEmp ? '_'.$aOne['emp'] : '')] = $aOne;
            }
        }
    }
    
    /**
     * ��������� ����� ������� � UID ������ ������� �� ����� ��������� � ����������
     */
    private function _getIgnoreInStats() {
        $aUids = $GLOBALS['DB']->col( 'SELECT uid FROM users WHERE ignore_in_stats = TRUE' );
        
        if ( $aUids ) {
            $this->sIgnoreInStats = " AND NOT (op_date >= '2011-01-01' AND a.uid IN (". implode( ',', $aUids ) .")) ";
        }
    }
    
    /**
     * ���������� ������ ������� ��� ������� �� ��������� ����������
     * 
     * @return type
     */
    private function _getStatsFrom() {
        return $this->nFilterDomainId ? $this->nFilterDomainId : implode(',', array_keys($this->aDomains));
    }
    
    /**
     * ���������� ����� WHERE ��� ������� �� ��������� ����������
     * 
     * @param  string $sDateField �������� ���� � �����
     * @param  string $sAnd �������� ���� � �����
     * @return string
     */
    private function _getStatsWhere( $sDateField = 'reg_date', $sAnd = '' ) {
        $aWhere = array();
        
        if ( $this->sDateFrom ) {
            $aWhere[] = $GLOBALS['DB']->parse( $sDateField . ' >= ?::date', date('Y-m-d', $this->sDateFrom) );
        }
        
        if ( $this->sDateTo ) {
            $aWhere[] = $GLOBALS['DB']->parse( "$sDateField < ?::date + '1 day'::interval", date('Y-m-d', $this->sDateTo) );
        }
        
        if ( $sAnd ) {
            $aWhere[] = $sAnd;
        }
        
        return $aWhere ? ' WHERE ' . implode(' AND ', $aWhere) : '';
    }
    
    /**
     * ��������� �� ���� �� �������� � ������ �� �������� ������� ����������
     * ���� ��� ������� - ������������ ���
     */
    function checkReferer() {
        preg_match( '#^(?:https?://)?([^/]+)#i', $_SERVER['HTTP_REFERER'], $aMatches );
        
        if ( $this->aDomains ) {
            foreach ( $this->aDomains as $aOne ) {
                if ( $aOne['name'] == $aMatches[1] && $aOne['is_active'] == 't' ) {
                    $sCookie = 'traffic_stat_reg_' . $aOne['id'];
                    
                    if ( !isset($_COOKIE[$sCookie]) ) {
                        setcookie( 'traffic_stat_reg_' . $aOne['id'], $aOne['name'], time() + self::COOKIE_TTL, "/", $GLOBALS['domain4cookie'] );
                        $this->oStatDB->insert( 'traffic_stat', array('domain_id' => $aOne['id']) );
                    }
                }
            }
        }
    }
    
    /**
     * ��������� �� ���� �� ����������� ����� �������� � ������ �� �������� ������� ����������
     * ���� ���� ����������� - ������������ ��
     * 
     * @param int $name Description
     */
    function checkRegistration( $nUid = 0, $nRole = 0 ) {
        if ( $this->aDomains ) {
            foreach ( $this->aDomains as $aOne ) {
                $sCookie = 'traffic_stat_reg_' . $aOne['id'];
                
                if ( isset($_COOKIE[$sCookie]) && $_COOKIE[$sCookie] == $aOne['name'] ) {
                    $aSqlData = array( 'domain_id' => $aOne['id'], 'uid' => $nUid, 'is_emp' => !empty($nRole) );
                    $GLOBALS['DB']->insert( 'traffic_stat_uids', $aSqlData );
                    setcookie( 'traffic_stat_reg_' . $aOne['id'], '', time() - 3600, "/", $GLOBALS['domain4cookie'] );
                }
            }
        }
    }
    
    /**
     * ������� ����� � ��� ���������� ��� ����
     * 
     * ����� ������� ����� ���������� ���� ������, �������� $traffic_stat->initDomainFromDB()
     * 
     * @return bool true - �����, false - ������
     */
    function deleteDomain() {
        $GLOBALS['DB']->query( 'DELETE FROM traffic_stat_domains WHERE id = ?i', $this->sDomainId );
        
        $bRet = empty($GLOBALS['DB']->error);
        
        if ( $bRet ) {
            $this->oStatDB->query( 'DELETE FROM traffic_stat WHERE domain_id = ?i', $this->sDomainId );
            $this->oMemBuff->delete( 'traffic_stat_domains' );
        }
        
        return $bRet;
    }


    /**
     * ��������� ����� � ���� ������.
     * 
     * ����� ������� ����� ���������� ���� ������, �������� $traffic_stat->initDomainFromParams()
     * 
     * @return bool true - �����, false - ������
     */
    function saveDomain() {
        $bRet = false;
        
        if ( $this->bDomainSet && empty($this->sDomainNameError) ) { // ���� ����������� ��� ������
            $aSqlData = array( 'name' => $this->sDomainName, 'is_active' => $this->sDomainActive );
            
            if ( !empty($this->sDomainId) ) {
                $GLOBALS['DB']->update( 'traffic_stat_domains', $aSqlData, 'id = ?i', $this->sDomainId );
            }
            else {
                $GLOBALS['DB']->insert( 'traffic_stat_domains', $aSqlData );
            }
            
            $bRet = empty($GLOBALS['DB']->error);
            
            if ( $bRet ) {
                $this->oMemBuff->delete( 'traffic_stat_domains' );
            }
        }
        
        $this->bDomainSet = false;
        
        return $bRet;
    }
    
    /**
     * �������������� ���� ������ ������� �� ����, ������� �� ���� ��� ���� � $this->aDomains
     * 
     * @param int $sDomainId ID ������ � ����
     */
    function initDomainFromDB( $sDomainId = 0 ) {
        $this->bDomainSet = false;
        
        if ( in_array($sDomainId, array_keys($this->aDomains) ) ) {
            $this->bDomainSet       = true;                                     // ���� ��������� �����
            $this->sDomainNameError = '';                                       // ��������� �� ������
            $this->sDomainIdError   = '';                                       // ��������� �� ������
            $this->sDomainId        = $sDomainId;                               // ID ������ ��� ��������������
            $this->sDomainName      = $this->aDomains[$sDomainId]['name'];      // �����
            $this->sDomainActive    = $this->aDomains[$sDomainId]['is_active']; // ������������ � ������ ������
        }
        else {
            $this->sDomainIdError = '����� �� ����������';
        }
    }
    
    /**
     * �������������� ���� ������ ��������� ����������� � ��������� ��������� ������
     */
    function initDomainFromParams() {
        $this->bDomainSet       = true;                                              // ���� ��������� �����
        $this->sDomainNameError = '';                                                // ��������� �� ������
        $this->sDomainIdError   = '';                                                // ��������� �� ������
        $this->sDomainId        = __paramInit( 'int', 'id', 'id', 0 );               // ID ������ ��� ��������������
        $this->sDomainName      = trim( __paramInit('string', 'name', 'name', '') ); // �����
        $this->sDomainActive    = __paramInit( 'int', 'is_active', 'is_active', 0 ); // ������������ � ������ ������
        $this->sDomainActive    = $this->sDomainActive ? 't' : 'f';
        
        preg_match( '#^(?:https?://)?([^/]+)#i', $this->sDomainName, $aMatches );
        
        $this->sDomainName = $aMatches[1];
        
        if ( empty($this->sDomainName) || !url_validate($this->sDomainName) ) {
            $this->sDomainNameError = '���� ��������� �����������';
        }
        else {
            $sQuery = 'SELECT id FROM traffic_stat_domains WHERE name = ? AND id <> ?i';
            
            if ( $GLOBALS['DB']->val($sQuery, $this->sDomainName, $this->sDomainId) ) {
                $this->sDomainNameError = '����� ����� ��� ����������';
            }
        }
    }
    
    /**
     * ������ ����������
     * �������������� ���� ������ ��������� ����������� � ��������� ��������� ������
     */
    function initFilterFromParams() {
        $this->nFilterDomainId = __paramInit( 'int', 'domain_id', null, 0 );
        $this->sPeriod         = __paramInit( 'string', 'period', null, 'today' );
        $this->sDateFromError  = '';
        $this->sDateToError    = '';
        $this->sDateError      = '';
        
        if ( empty($this->nFilterDomainId) || in_array($this->nFilterDomainId, array_keys($this->aDomains) ) ) {
            switch ( $this->sPeriod ) {
                case 'today':
                    $this->sDateFrom = time();
                    break;
                case 'week':
                    $this->sDateFrom = strtotime( '-1 week' );
                    break;
                case 'month':
                    $this->sDateFrom = strtotime( '-1 month' );
                    break;
                case 'year':
                    $this->sDateFrom = strtotime( '-1 year' );
                    break;
                case 'alltime': // ����� default �� ��������
                    break;
                case 'custom':
                    $aFrom = explode( '.', __paramInit('string', 'custom_period_from', null, '') );
                    $aTo   = explode( '.', __paramInit('string', 'custom_period_to', null, '') );

                    if ( count($aFrom) != 3 || ($this->sDateFrom = strtotime(implode('-', array_reverse($aFrom)))) === false ) {
                        $this->sDateFromError = '������� ���������� ��������� ����';
                    }

                    if ( count($aTo) != 3 || ($this->sDateTo = strtotime(implode('-', array_reverse($aTo)))) === false ) {
                        $this->sDateToError = '������� ���������� �������� ����';
                    }

                    if ( !$this->sDateFromError && !$this->sDateToError && $this->sDateFrom > $this->sDateTo ) {
                        $this->sDateError = '�������� ���� �� ����� ���� ������ ���������';
                    }
                    break;
                default:
                    $this->sDateError = '������� ������';
                    break;
            }
        }
        else {
            $this->sFilterDomainIdError = '����� �� ����������';
        }
    }
    
    /**
     * ���������� ������ � ������� �� ����� ����������
     * 
     * @return array 
     */
    function getDomains() {
        return $this->aDomains;
    }
    
    /**
     * ���������� ������ ��������� ID ������ $sDomainId
     * 
     * @return string
     */
    function getDomainIdError() {
        return $this->sDomainIdError;
    }
    
    /**
     * ���������� ���� ��� ������
     * 
     * @return string
     */
    function getDomainName() {
        return $this->sDomainName;
    }
    
    /**
     * ���������� ������ ��������� ������ $sDomainName
     * 
     * @return string
     */
    function getDomainNameError() {
        return $this->sDomainNameError;
    }
    
    /**
     * ���������� ���� ������������ �� ����� � ������ ������
     * 
     * @return string
     */
    function getDomainActive() {
        return $this->sDomainActive;
    }
    
    /**
     * ���������� ID ������ ��� ������� ����������
     * 
     * @return int 
     */
    function getFilterDomainId() {
        return $this->nFilterDomainId;
    }
    
    /**
     * ���������� �� ����� ������ �������� ����������
     * 
     * @return string
     */
    function getPeriod() {
        return $this->sPeriod;
    }
    
    /**
     * ���������� ��������� ���� ������� � ������� d.m.Y
     * 
     * @return string
     */
    function getDateFrom( $sFormat = 'd.m.Y' ) {
        $nTime = !$this->sDateFromError ? $this->sDateFrom : time();
        
        return date( $sFormat, $nTime );
    }
    
    /**
     * ���������� �������� ���� ������� � ������� d.m.Y
     * 
     * @return string
     */
    function getDateTo( $sFormat = 'd.m.Y' ) {
        $nTime = !$this->sDateToError && $this->sDateTo ? $this->sDateTo : time();
        
        return date( $sFormat, $nTime );
    }
    
    /**
     * ���������� ��������� �� ������ � ������� ��� javascript alert()
     * 
     * @return string
     */
    function getFilterErrorAlert() {
        $sError  = $this->sFilterDomainIdError;
        $sError .= $this->sDateError     ? ($sError ? '\n' : '') . $this->sDateError     : '';
        $sError .= $this->sDateFromError ? ($sError ? '\n' : '') . $this->sDateFromError : '';
        $sError .= $this->sDateToError   ? ($sError ? '\n' : '') . $this->sDateToError   : '';
        
        return $sError;
    }
}