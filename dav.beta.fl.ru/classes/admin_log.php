<?php
/**
 * ���������� ������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/admin_parent.php");

/**
 * ����� ��� ������ � ���������� ����������
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class admin_log extends admin_parent {
    // ��� ������� ��������
    const OBJ_CODE_USER  = 1; // ������������
    const OBJ_CODE_BLOG  = 2; // ����
    const OBJ_CODE_PROJ  = 3; // ������
    const OBJ_CODE_COMM  = 4; // ����������
    const OBJ_CODE_ART   = 6; // ������
    const OBJ_CODE_OFFER = 8; // ����������� ���-��������
    const OBJ_CODE_PMSG  = 9; // ������ ���������
    const OBJ_CODE_FPAGE = 10; // �������� ����� �� ������� � � ��������
    const OBJ_CODE_TSERVICES = 11;// ������� ������
    
    // id �������� �� ������� admin_actions
    const ACT_ID_DEL_ACC            = 18; // �������� ������ �������� �������������
    const ACT_ID_RESTORE_ACC        = 19; // ��������������, ���������� �������������, ��������
    const ACT_ID_BLOG_CH_GR         = 20; // ����� ������� �����
    const ACT_ID_PRJ_CH_SPEC        = 21; // ����� ������������� ������
    const ACT_ID_PRJ_DEL_OFFER      = 22; // �������� ����������� � �������
    const ACT_ID_PRJ_RST_OFFER      = 23; // �������������� ����������� � �������
    const ACT_ID_BLOG_DEL_COMM      = 24; // �������� ����������� � �����
    const ACT_ID_BLOG_RST_COMM      = 25; // �������������� ����������� � �����
    const ACT_ID_USR_CH_RATING      = 26; // ��� ����������� � ���������
    const ACT_ID_PRJ_BLOCK_OFFER    = 27; // ���������� ����������� � �������
    const ACT_ID_PRJ_UNBLOCK_OFFER  = 28; // ������������� ����������� � �������
    const ACT_ID_PORTFOLIO_BLOCK    = 29; // ���������� ������ � ���������
    const ACT_ID_PORTFOLIO_UNBLOCK  = 30; // ������������� ������ � ���������
    const ACT_ID_PRJ_DIALOG_BLOCK   = 31; // ���������� ����������� � ����������� �� �������
    const ACT_ID_PRJ_DIALOG_UNBLOCK = 32; // ������������� ����������� � ����������� �� �������
    const ACT_ID_EDIT_MSSAGES       = 33; // �������������� ������ ���������
    const ACT_ID_EDIT_BLOGS         = 34; // �������������� ������: ����� � �����������
    const ACT_ID_EDIT_COMMUNITY     = 35; // �������������� c��������: ����� � �����������
    const ACT_ID_EDIT_PROJECTS      = 36; // �������������� �������� � ���������
    const ACT_ID_EDIT_PRJ_OFFERS    = 37; // �������������� ����������� � �������� � ���������
    const ACT_ID_EDIT_ART_COM       = 40; // �������������� ����������� � �������
    const ACT_ID_EDIT_PROFILE       = 41; // �������������� ��������� � ��������
    const ACT_ID_EDIT_PRJ_DIALOG    = 42; // �������������� ����������� � ������������ �� ��������
    const ACT_ID_EDIT_CONTEST_COM   = 43; // �������������� ����������� � ������������ ���������
    const ACT_ID_EDIT_PORTF_CHOISE  = 44; // �������������� ��������� � �������� � ���������
    const ACT_ID_EDIT_PORTFOLIO     = 45; // �������������� ������ � ���������
    const ACT_ID_EDIT_SDELAU        = 46; // �������������� ����������� ����������� "������"
    const ACT_ID_DEL_MESSAGES       = 47; // �������� ������ ���������
    const ACT_ID_RST_MESSAGES       = 48; // �������������� ������ ���������
    const ACT_ID_DEL_BLOG           = 49; // �������� �����
    const ACT_ID_RST_BLOG           = 50; // �������������� �����
    const ACT_ID_DEL_COMMUNITY_COMM = 51; // �������� ����������� � c���������
    const ACT_ID_RST_COMMUNITY_COMM = 52; // �������������� ����������� � c���������
    const ACT_ID_DEL_CONTEST_OFFER  = 53; // �������� ���������� ������
    const ACT_ID_RST_CONTEST_OFFER  = 54; // �������������� ���������� ������
    const ACT_ID_DEL_ART_COM        = 59; // �������� ����������� � �������
    const ACT_ID_RST_ART_COM        = 60; // �������������� ����������� � �������
    const ACT_ID_CANCEL_PROFILE     = 61; // ������ ��������� � �������
    const ACT_ID_CANCEL_PORT_CHOISE = 62; // ������ ��������� � �������� ���������
    const ACT_ID_EDIT_FIRST_PAGE    = 63; // �������������� ������� ����
    
    /**
     * ������� ��������
     * 
     * name  - ������ �������� �������
     * short - ������� �������� �������
     * 
     * @var array
     */
    static $aObj = array(
        self::OBJ_CODE_USER => array( 'name' => '������������',     'short' => '�����.' ), 
        self::OBJ_CODE_BLOG => array( 'name' => '����',             'short' => '����' ), 
        self::OBJ_CODE_PROJ => array( 'name' => '������',           'short' => '������' ), 
        self::OBJ_CODE_COMM => array( 'name' => '����������',       'short' => '�����.' ), 
        self::OBJ_CODE_ART  => array( 'name' => '������',           'short' => '������' ), 
        self::OBJ_CODE_OFFER => array( 'name' => '����������� ���-�������', 'short' => '�����.' ), 
        self::OBJ_CODE_PMSG => array( 'name' => '������ ���������', 'short' => '�����' ), 
        self::OBJ_CODE_FPAGE => array( 'name' => '�������� �����', 'short' => '��������' ),
        self::OBJ_CODE_TSERVICES => array( 'name' => '������� ������', 'short' => '��' )
    );
    
    /**
     * ������� ��������, ������� �� ������������ � ������ ������.
     * �������� ��� ���� ���������� � ����� ��������, �������������� ������ ��������, ���������� � �.�.
     * 
     * @var array 
     */
    static $aObjInactive = array( self::OBJ_CODE_OFFER, self::OBJ_CODE_PMSG );


    /**
     * ���������� ��������� �������� �� ��������
     */
    private $log_pp = 20;
    
    /**
     * � ����� ���� ���������� ���: log - ������ ��������, proj - � ���������, user - � �������
     * �� ��� ������ ������ ���� �������� ��������� ��������
     * 
     * @var string
     */
    private $mode = 'log';
    
    /**
     * ���������� �������� $mode
     * 
     * @var array
     */
    static $mode_allow = array( 'log', 'user', 'proj', 7 => 'stat', 'notice', 'offer' );
    
    /**
     * �����, ����������� ��� ������� � ���������� ������
     * 
     * @var array
     */
    static $mode_permissions = array( 'log' => 'adm', 'user' => 'users', 'proj' => 'projects', 'stat' => 'all', 'notice' => 'adm', 'offer' => 'projects' );
    
    /**
     * ����� $mode � ���������
     * 
     * @var array
     */
    static $mode_code = array( 'log' => 0, 'user' => self::OBJ_CODE_USER, 'proj' => self::OBJ_CODE_PROJ, 'stat' => 0 );
    
    /**
     * ����� ���������� ��������� �������������
     * 
     * @var string
     */
    private $last_visited;
    
    /**
     * ����� ����������� ��������� �������������
     * 
     * @var string
     */
    private $prev_visited;
    
    /**
     * ����������� ������
     * 
     * @param  string $mode ������ ����: log - ������ ��������, proj - � ���������, user - � �������
     * @param  int $uid UID �������� ������������
     * @param  array $user_permissions ����� ������������
     * @return object admin_log
     */
    function __construct( $mode = 'log', $uid = 0, $user_permissions = array() ) {
        $this->mode     = $mode;
        $this->curr_uid = $uid;
        $this->user_permissions = $user_permissions;
        $this->last_visited     = date('Y-m-d H:i:s');
        $this->prev_visited     = date('Y-m-d H:i:s', 0);
        
        if ( $mode == 'log' ) {
            $aAdminUsers = $GLOBALS['DB']->row( 'SELECT last_visited, prev_visited FROM admin_users WHERE user_id = ?i', $uid );
            
            if ( !$aAdminUsers ) {
            	$GLOBALS['DB']->insert( 'admin_users', array('user_id' => $uid, 'prev_visited' => $this->prev_visited ) );
            }
            else {
                $sUpdate            = ( !isset($_COOKIE['admin_log_session']) ) ? 'prev_visited = last_visited, ' : '';
                $this->prev_visited = ( !isset($_COOKIE['admin_log_session']) ) ? $aAdminUsers['last_visited']    : $aAdminUsers['prev_visited'];
                $GLOBALS['DB']->query( "UPDATE admin_users SET $sUpdate last_visited = NOW() WHERE user_id = ?i", $uid );
            }
            
            setcookie( 'admin_log_session', $this->last_visited );
        }
    }
    
    /**
     * ������������� ���������� ��������� �������� �� ��������
     * 
     * @param  int $log_pp
     * @return bool true - �����, false - ������
     */
    function setLogPerPage( $log_pp = 20 ) {
        $log_pp = intval( $log_pp );
        
        if ( $log_pp > 0 ) {
        	$this->log_pp = $log_pp;
        	return true;
        }
        
        return false;
    }
    
    /**
     * ���������� ���������� ��������� �������� �� ��������
     * 
     * @return int ���������� ��������� �������� �� ��������
     */
    function getLogPerPage() {
        return $this->log_pp;
    }
    
    /**
     * ������������� email ��� �������� �� ����� ��������.
     * 
     * @param  int $nAdminId UID ��������������
     * @param  string $sEmail �������� �����
     * @param  string $sError ���������� ��������� �� ������ ��� ������ ������
     * @return bool true - �����, false - ������
     */
    function setAdminNoticeEmail( $nAdminId = 0, $sEmail = '', &$sError ) {
        $sError = ( !is_email($sEmail) ) ? '������� ���������� Email' : '';
        
        if ( $nAdminId && !$sError ) {
        	$GLOBALS['DB']->insert( 'admin_log_notice', array('admin_id' => $nAdminId, 'email' => $sEmail) );
        }
        
        return ( !$sError && !$GLOBALS['DB']->error );
    }
    
    /**
     * ���������� email ��� �������� �� ����� ��������.
     * 
     * @param  int $nAdminId UID ��������������
     * @return string 
     */
    function getAdminNoticeEmail( $nAdminId = 0 ) {
        return $GLOBALS['DB']->val( 'SELECT email FROM admin_log_notice WHERE admin_id = ?', $nAdminId );
    }
    
    /**
     * ������������� �������� ������ �� ����� ��������.
     * 
     * @param  int $nAdminId UID ��������������
     * @param  array $aActions ������ ID �������� �� ������� ������ ���� �������� �����.
     * @return bool true - �����, false - ������
     */
    function setAdminNotices( $nAdminId = 0, $aActions = array() ) {
        if ( !intval($nAdminId) ) {
        	return false;
        }
        
        $GLOBALS['DB']->query( 'DELETE FROM admin_log_notice_act WHERE admin_id = ?', $nAdminId );
        
        if ( !$GLOBALS['DB']->error && is_array($aActions) && count($aActions) ) {
            $aData = array();
            
        	foreach ( $aActions as $sOne ) {
        		$aData[] = array( 'admin_id' => $nAdminId, 'act_id' => $sOne );
        	}
        	
        	$GLOBALS['DB']->insert( 'admin_log_notice_act', $aData );
        }
        
        return ( !$GLOBALS['DB']->error );
    }
    
    /**
     * ���������� �������� ������ �� ����� ��������.
     * 
     * @param  int $nAdminId UID ��������������
     * @return array
     */
    function getAdminNotices( $nAdminId = 0 ) {
        // ���� �� ����� - �� ���������� ������ �� �������� �� ������� ���� ����� �����
        if ( !in_array('all', $this->user_permissions) ) {
            $sWhere = " WHERE position('|'||rights||'|' in '|". implode('|', $this->user_permissions) ."|' ) > 0";
        }
        
        return $GLOBALS['DB']->rows( 'SELECT a.id, a.act_name, a.obj_code, s.act_id AS is_subscr 
            FROM admin_actions a 
            LEFT JOIN admin_log_notice_act s ON s.act_id = a.id AND s.admin_id = ?' . $sWhere, $nAdminId );
    }
    
    /**
     * ���������� ��������� � ���������� �������
     * 
     * @param  int $sId ID ���������� (projects_blocked)
     * @return array
     */
    function getProjBlock( $sId = 0 ) {
        return $GLOBALS['DB']->row( 'SELECT * FROM projects_blocked WHERE id = ?i', $sId );
    }
    
    /**
     * ���������� ��������� � ���������� ����������� ���-�������
     * 
     * @param  int $sId ID ����������� (freelance_offers)
     * @return array
     */
    function getOfferBlock( $sId = 0 ) {
        return $GLOBALS['DB']->row( 'SELECT reason_id, reason FROM freelance_offers WHERE id = ?i', $sId );
    }
    
    /**
     * ���������� ��������� � ��������������
     * 
     * @param  int $sId ID �������������� (users_warns)
     * @return array
     */
    function getUserWarn( $sId = 0 ) {
        return $GLOBALS['DB']->row( 'SELECT * FROM users_warns WHERE id = ?i', $sId );
    }
    
    /**
     * �������� ��� ���������� ��������.
     * 
     * !!! ������������ ������ ��� �������������� ������� ������������� (��� �������������� � �����).
     * ��� ������������� ������� ���������� ������������ ��������� �������, 
     * ����� ���� ������������� � ��������� ��������� ���� ���������� @see updateProjBlock � �.�.
     * 
     * @param  int $sId ID ���������� �������� (admin_log)
     * @param  string $sReason �������
     * @param  string $sReasonId id �������, ���� ��� ������� �� ������
     * @param  int $sUid UID �������������� (���� 0, ������������ $_SESSION['uid'])
     * @return bool true - �����, false - ������
     */
    function updateLog( $sId = 0, $sReason, $sReasonId = null, $sUid = 0 ) {
        if ( !$sUid ) {
            if ( !($sUid = $_SESSION['uid']) ) {
            	return false;
            }
        }
        
        $aData = array(
            'admin_id'      => $sUid,
            'admin_comment' => $sReason,
            'reason_id'     => $sReasonId
        );
        
        return $GLOBALS['DB']->update( 'admin_log', $aData, 'id = ?i', $sId );
    }
    
    /**
     * �������� ��������� � ���������� �������
     * 
     * @param  int $sId ID ���������� (projects_blocked)
     * @param  string $sReason �������
     * @param  string $sReasonId id �������, ���� ��� ������� �� ������
     * @param  int $sUid UID �������������� (���� 0, ������������ $_SESSION['uid'])
     * @return bool true - �����, false - ������
     */
    function updateProjBlock( $sId = 0, $sReason, $sReasonId = null, $sUid = 0 ) {
        if ( !$sUid ) {
            if ( !($sUid = $_SESSION['uid']) ) {
            	return false;
            }
        }
        
        $aData = array(
            'admin'     => $sUid,
            'reason'    => $sReason,
            'reason_id' => $sReasonId
        );
        
        return $GLOBALS['DB']->update( 'projects_blocked', $aData, 'id = ?i', $sId );
    }
    
    /**
     * �������� ��������� � ���������� ����������� ���-�������
     * 
     * @param  int $sId ID �����������
     * @param  string $sReason �������
     * @param  string $sReasonId id �������, ���� ��� ������� �� ������
     * @param  int $sUid UID �������������� (���� 0, ������������ $_SESSION['uid'])
     * @return bool true - �����, false - ������
     */
    function updateOfferBlock( $sId = 0, $sReason, $sReasonId = null, $sUid = 0 ) {
        if ( !$sUid ) {
            if ( !($sUid = $_SESSION['uid']) ) {
            	return false;
            }
        }
        
        $aData = array(
            'admin'     => $sUid,
            'reason'    => $sReason,
            'reason_id' => $sReasonId
        );
        
        return $GLOBALS['DB']->update( 'freelance_offers', $aData, 'id = ?i', $sId );
    }
    
    /**
     * �������� ��������� � ��������������
     * 
     * @param  int $sId ID ���������� (projects_blocked)
     * @param  string $sReason �������
     * @param  string $sReasonId id �������, ���� ��� ������� �� ������
     * @param  int $sUid UID �������������� (���� 0, ������������ $_SESSION['uid'])
     * @return bool true - �����, false - ������
     */
    function updateUserWarn( $sId = 0, $sReason, $sReasonId = null, $sUid = 0 ) {
        if ( !$sUid ) {
            if ( !($sUid = $_SESSION['uid']) ) {
            	return false;
            }
        }
        
        $aData = array(
            'admin'     => $sUid,
            'reason'    => $sReason,
            'reason_id' => $sReasonId
        );
        
        return $GLOBALS['DB']->update( 'users_warns', $aData, 'id = ?i', $sId );
    }
    
    /**
     * ���������� ������ ��������� �������� ����������
     * 
     * @param  int $objCode �����������. ��� �������
     * @return array
     */
    function getAdminActions( $objCode = 0 ) {
        $sRights   = '';
        $sInactive = '';
        
        // ���� �� ����� - �� ���������� ������ �� �������� �� ������� ���� ����� �����
        if ( !in_array('all', $this->user_permissions) ) {
            $sRights = " position('|'||rights||'|' in '|". implode('|', $this->user_permissions) ."|' ) > 0 ";
        }
        
        // ���� ����� ��������� ����� ��������
        if (  is_array(self::$aObjInactive) && self::$aObjInactive ) {
            $sInactive = ' obj_code NOT IN ('. implode(', ', self::$aObjInactive) .') ';
        }
        
        if ( $objCode ) {
            $sWhere = $sRights ? ' AND ' . $sRights : '';
            
        	return $GLOBALS['DB']->rows( 'SELECT * FROM admin_actions WHERE obj_code = ?i '. $sWhere .' ORDER BY id', $objCode );
        }
        else {
            $sWhere = $sInactive ? (' WHERE ' . $sInactive . ($sRights ? ' AND ' . $sRights : '')) : ($sRights ? ' WHERE ' . $sRights : '');
            
            return $GLOBALS['DB']->rows( 'SELECT * FROM admin_actions '. $sWhere .' ORDER BY obj_code, id' );
        }
    }

    /**
     * ���������� ������ ���� 
     * 
     * @return array
     */
    function getPermissionsRights() {
        $rights = array();
        $qrights = $GLOBALS['DB']->rows( 'SELECT DISTINCT rights FROM admin_actions' );
        if($qrights) {
            foreach($qrights as $right) {
                array_push($rights, $right['rights']);
            }
        }
        return $rights;
    }
    
    /**
     * ���������� ������ �������, ������� ���� ������� ��������
     * 
     * @param  int $objCode �����������. ��� �������
     * @return array
     */
    function getAdminsInLog( $objCode = 0 ) {
        $sJoin = $sWhere = '';
        
        if ( $objCode ) {
        	$sJoin  = 'INNER JOIN admin_actions a ON a.id=l.act_id';
        	$sWhere = $GLOBALS['DB']->parse( 'WHERE a.obj_code = ?i', $objCode );
        }
        
        return $GLOBALS['DB']->rows( "SELECT u.uid, u.login FROM users u 
            INNER JOIN admin_log l ON l.admin_id = u.uid AND l.act_id != ?i
            $sJoin 
            $sWhere 
            GROUP BY u.uid, u.login
            ORDER BY u.login" 
            , self::ACT_ID_DEL_ACC);
    }
    
    /**
     * ������������� ����� ���������� ��������� ������������ � �������� ���������� ��� �������� ������������
     * 
     * @param  int $userId UID �������� ������������
     * @param  int $logId ID �������� ����������
     * @return bool true - �����, false - ������
     */
    function setLogCLV( $userId = 0, $logId = 0 ) {
        $GLOBALS['DB']->query( 'INSERT INTO admin_log_users(user_id, log_id, last_comment_view) VALUES (?i, ?i, NOW());', $userId, $logId );

        return ( $GLOBALS['DB']->error ? false : true );
    }
    
    /**
     * ���������� ������ ��������� ����/�������� �� admin_log_users
     * 
     * @param unknown_type $userId
     * @param unknown_type $logId
     */
    function getUserToLogInfo( $userId = 0, $logId = 0 ) {
        return $GLOBALS['DB']->row("SELECT hidden_threads, COALESCE(last_comment_view, 'epoch') AS last_comment_view 
            FROM admin_log_users WHERE user_id = ?i AND log_id = ?i", $userId, $logId );
    }
    
    /**
     * ���������� ������� ����� ������������ �������
     *
     * @param  int $userId UID �������� ������������
     * @param  int $logId ID �������� ����������
     * @return array
     */
    function getHiddenThreads( $userId = 0, $logId = 0 ) {
        return $GLOBALS['DB']->val( 'SELECT hidden_threads FROM admin_log_users WHERE user_id = ?i AND log_id = ?i', $userId, $logId );
    }
    
    /**
     * ���������� ���������� � ���������� �������� �� ID
     * 
     * @param  int $logId ID �������� ���������� �� admin_log
     * @return array
     */
    function getLogById( $logId = 0 ) {
        $this->filter = array( 'in_id' => $logId );
        $aRes = $this->getLog( $count );
        
        return $aRes[0];
    }
    
    /**
     * ���������� ����� ��������� ��������
     * 
     * @param  int $count ���������� ���������� ������� �������������� �������� �������
     * @param  array $filter ��������� ������� ����
     * @param  int $page ����� ������� ��������
     * @return array
     */
    function getLogAll( &$count, $filter, $page = 1 ) {
        $this->filter = $filter;
        
        return $this->getLog( $count, $page );
    }
    
    /**
     * ���������� ������� �������� �� ����������� �������.
     * 
     * @param  int $count ���������� ���������� ������� �������������� �������� �������
     * @param  int $sPrjId ID �������
     * @return array
     */
    function getLogProjById( &$count, $sPrjId ) {
        $this->filter = array( 'obj_code' => self::OBJ_CODE_PROJ, 'object_id' => $sPrjId );
        $this->mode   = 'proj';
        
        return $this->getLog( $count, 1, 'general', 0, true );
    }
    
    /**
     * ���������� ������� �������� �� ����������� ����������� ���-�������
     * 
     * @param  int $count ���������� ���������� ������� �������������� �������� �������
     * @param  int $sPrjId ID �������
     * @return array
     */
    function getLogOfferById( &$count, $sOfferId ) {
        $this->filter = array( 'obj_code' => self::OBJ_CODE_OFFER, 'object_id' => $sOfferId );
        $this->mode   = 'offer';
        
        return $this->getLog( $count, 1, 'general', 0, true );
    }
    
    /**
     * ���������� ������� �������� �� ����������� ������������.
     * 
     * @param  int $count ���������� ���������� ������� �������������� �������� �������
     * @param  int $sUserId UID ������������
     * @param  int $sActId �����������. ID ��������
     * @return array
     */
    function getLogUserById( &$count, $sUserId, $sActId = '' ) {
        $this->filter = array( 'obj_code' => self::OBJ_CODE_USER, 'object_id' => $sUserId, 'act_id' => $sActId );
        $this->mode   = 'user';
        
        return $this->getLog( $count, 1, 'general', 0, true );
    }
    
    /**
     * ���������� ����� ��������� �������� ��� ���������
     * 
     * @param  int $count ���������� ���������� ������� �������������� �������� �������
     * @param  array $filter ��������� ������� ����
     * @param  int $page ����� ������� ��������
     * @param  string order ��� ����������: num - �� ������ �������, name - �������� �������, date - ���� ��������
     * @param  string $direction ������� ����������: asc, desc
     * @return array
     */
    function getLogProj( &$count, $filter, $page = 1, $order = 'date', $direction = 'desc' ) {
        $filter['obj_code'] = self::OBJ_CODE_PROJ;
        $this->filter       = $filter;
        $this->mode         = 'proj';
        $direction          = $direction == 'desc' ? 0 : 1;
        
        switch ( $order ) {
            case 'num':
                $order = 'object_id';
                break;
            case 'name':
                $order = 'object_name';
                break;
            case 'date':
            default:
                $order = 'general';
                break;
        }
        
        return $this->getLog( $count, $page, $order, $direction );
    }
    
    /**
     * ���������� ����� ��������� �������� ��� ������������� ���-��������
     * 
     * @param  int $count ���������� ���������� ������� �������������� �������� �������
     * @param  array $filter ��������� ������� ����
     * @param  int $page ����� ������� ��������
     * @param  string order ��� ����������: num - �� ������ �����������, name - �������� �����������, date - ���� ��������
     * @param  string $direction ������� ����������: asc, desc
     * @return array
     */
    function getLogOffer( &$count, $filter, $page = 1, $order = 'date', $direction = 'desc' ) {
        $filter['obj_code'] = self::OBJ_CODE_OFFER;
        $this->filter       = $filter;
        $this->mode         = 'offer';
        $direction          = $direction == 'desc' ? 0 : 1;
        
        switch ( $order ) {
            case 'num':
                $order = 'object_id';
                break;
            case 'name':
                $order = 'object_name';
                break;
            case 'date':
            default:
                $order = 'general';
                break;
        }
        
        return $this->getLog( $count, $page, $order, $direction );
    }
    
    /**
     * ���������� ����� ��������� �������� ��� ��������������
     * 
     * @param  int $count ���������� ���������� ������� �������������� �������� �������
     * @param  array $filter ��������� ������� ����
     * @param  int $page ����� ������� ��������
     * @param  string order ��� ����������: act - �� ��������, name - �������� ��������������, date - ���� ��������
     * @param  string $direction ������� ����������: asc, desc
     * @return array
     */
    function getLogUser( &$count, $filter, $page = 1, $order = 'date', $direction = 'desc' ) {
        $filter['obj_code'] = self::OBJ_CODE_USER;
        $filter['not-act_id'] = array(self::ACT_ID_DEL_ACC, self::ACT_ID_RESTORE_ACC);
        $this->filter       = $filter;
        $this->mode         = 'user';
        $direction          = $direction == 'desc' ? 0 : 1;
        
        switch ( $order ) {
            case 'act':
                $order = 'act_id';
                break;
            case 'name':
                $order = 'object_name';
                break;
            case 'date':
            default:
                $order = 'general';
                break;
        }
        
        return $this->getLog( $count, $page, $order, $direction );
    }
    
    /**
     * ���������� ������ �������������� ������������
     * 
     * @param  int $count ���������� ���������� ������� �������������� �������� �������
     * @param  int $sUid UID ������������
     * @return array
     */
    function getUserWarns( &$count, $sUid ) {
        $this->filter = array( 'act_id' => 1, 'object_id' => $sUid );
        
        return $this->getLog( $count, 1, 'general', 0, true );
    }
    
    /**
     * ���������� ���������� �������� ��� ��������.
     * 
     * @param  int $sObjCode ��� ������� �������� (1 - ������������, 2 - ����, 3 - ������, 4 - ����������, 6 - ������, 7 - �������
     * @param  int $sObjId ID ������� ��������
     * @return array 
     */
    function getLogCounts( $sObjCode = 0, $sObjId = 0 ) {
        $sQuery = 'SELECT l.act_id, a.act_name, COUNT(l.act_id) AS cnt FROM admin_log l 
            INNER JOIN admin_actions a ON a.id = l.act_id 
            WHERE l.object_code = ? AND l.object_id = ? 
            GROUP BY l.act_id, a.act_name 
            ORDER BY l.act_id';
        
        return $GLOBALS['DB']->rows( $sQuery, $sObjCode, $sObjId );
    }
    
    /**
     * ���������� ������� ��������� ��������
     * 
     * @param  int $count ���������� ���������� ������� �������������� �������� �������
     * @param  int $page ����� ������� ��������
     * @param  string order ��� ����������
     * @param  int $direction ������� ����������: 0 - �� ��������, �� 0 - �� �����������
     * @param  bool $unlimited �����������. ���������� � true ���� ����� �������� ��� ������ (��� ������������� ������)
     * @return array
     */
    function getLog( &$count, $page = 1, $order = 'general', $direction = 0, $unlimited = false ) {
        $this->aSQL = array();
        $offset     = $this->log_pp * ($page - 1);
        
        // ������ ������
        $this->_setLogSelect();
        $this->_setLogJoin();
        $this->_setLogWhere();
        $this->_setLogOrderBy( $order, $direction );
        
        // �������� ������� ��������� ��������
        $sQuery = 'SELECT ' . implode(', ', $this->aSQL['select']) 
            . ' FROM admin_log l ' . ( $this->aSQL['join'] ? ' ' . implode(' ', $this->aSQL['join']) : '' ) 
            . ( $this->aSQL['where'] ? ' WHERE ' . implode(' AND ', $this->aSQL['where']) : '' ) 
            . ' ORDER BY ' . implode(', ', $this->aSQL['order_by']) 
            . ( $unlimited ? '' : ' LIMIT ' . $this->log_pp . ' OFFSET ' . $offset);
//echo $GLOBALS['DB']->parse( $sQuery );
        $log = $GLOBALS['DB']->rows( $sQuery );
        
        if ( $GLOBALS['DB']->error || !$log ) {
            return array();
        }
        
        // �������� ����� ���������� ��������� ��������
        $aCountJoin = array_intersect_key( $this->aSQL['join'], $this->aSQL['count_join'] );
        $sQuery = 'SELECT COUNT(l.id) FROM admin_log l '
            . ( $aCountJoin ? ' ' . implode(' ', $aCountJoin) : '' ) 
            . ( $this->aSQL['where'] ? ' WHERE ' . implode(' AND ', $this->aSQL['where']) : '' ) ;
            
        $count = $GLOBALS['DB']->val( $sQuery );
//echo $GLOBALS['DB']->parse( $sQuery );
        
        return $log;
    }
    
    /**
     * �������� SELECT ����� SQL ������� ������� ��������� ��������
     */
    function _setLogSelect() {
        $this->aSQL['select'] = array( 'l.*, a.act_name, a.obj_code, adm.login AS adm_login, 
            COALESCE(l.last_comment, \'epoch\') AS last_comment, COALESCE(w.last_comment_view, \'epoch\') AS last_comment_view, 
            CASE WHEN l.act_time >= \''.$this->prev_visited.'\'::timestamp without time zone AND l.admin_id <> '.$this->curr_uid.' AND w.last_comment_view is NULL THEN 1 ELSE NULL END AS is_new' 
        );
        
        if ( $this->mode == 'proj' || $this->mode == 'offer' || $this->mode == 'log' ) {
        	$this->aSQL['select'][] = 'au.login AS aut_login, au.uname AS aut_uname, au.usurname AS aut_usurname, au.warn AS warn_cnt';
        }
        
        if ( $this->mode == 'offer' ) {
        	$this->aSQL['select'][] = 'fo.descr, p.name as profname, pg.name as cat_name';
        }
        
        if ( $this->mode == 'user' ) {
        	$this->aSQL['select'][] = 'u.login AS user_login, u.reg_date, u.last_ip, u.photo, u.role, u.is_pro, u.is_pro_test, u.is_team, u.warn AS warn_cnt, uw.log_warn_cnt, l.context_code, l.admin_comment';
        }
    }
    
    /**
     * �������� JOIN ����� SQL ������� ������� ��������� ��������
     */
    function _setLogJoin() {
        $this->aSQL['count_join'] = array();
        $this->aSQL['join']       = array( 
            'admin_actions' => 'INNER JOIN admin_actions a ON a.id = l.act_id',
        );
        
        if ( !in_array('all', $this->user_permissions) ) {
        	$this->aSQL['count_join']['admin_actions'] = 1;
        }
        
        if ( $this->mode == 'proj' || $this->mode == 'offer' || $this->mode == 'log' ) {
        	$this->aSQL['join']['author'] = 'LEFT JOIN users au ON au.uid = l.user_id';
        	
        	if ( self::isFilter('search') ) {
        	    $this->aSQL['count_join']['author'] = 1;
        	}
        }
        
        if ( $this->mode == 'offer' ) {
            $this->aSQL['join']['offer'] = 'LEFT JOIN freelance_offers fo ON fo.id = l.object_id';
            $this->aSQL['join']['offer_pg'] = 'LEFT JOIN prof_group pg ON pg.id = fo.category_id';
            $this->aSQL['join']['offer_p']  = 'LEFT JOIN professions p ON p.id = fo.subcategory_id';
            
            if ( self::isFilter('category') ) {
                $this->aSQL['count_join']['offer'] = 1;
                $this->aSQL['count_join']['offer_pg'] = 1;
                $this->aSQL['count_join']['offer_p']  = 1;
            }
        }
        
        if ( $this->mode == 'user' ) {
        	$this->aSQL['join']['user'] = 'LEFT JOIN users u ON u.uid = l.object_id';
        	$this->aSQL['join']['warn'] = 'LEFT JOIN (SELECT COUNT(id) AS log_warn_cnt, object_id FROM admin_log WHERE act_id = 1 GROUP BY object_id) AS uw ON uw.object_id = l.object_id';
        }
        
        $this->aSQL['join']['admin']     = 'LEFT JOIN users adm ON adm.uid = l.admin_id';
        $this->aSQL['join']['log_users'] = 'LEFT JOIN admin_log_users w ON w.log_id = l.id AND w.user_id = ' . $this->curr_uid;
    }
    
    /**
     * �������� WHERE ����� SQL ������� ������� ��������� ��������
     */
    function _setLogWhere() {
        $this->aSQL['where'] = array();
        $sCurrDate = date('Y-m-d');
        
        // ���� �� ����� - �� ���������� ������ �� �������� �� ������� ���� ����� �����
        if ( !in_array('all', $this->user_permissions) ) {
            $sSearch = '|'. implode('|', $this->user_permissions) .'|';
            $this->aSQL['where'][] = "position('|'||a.rights||'|' in '$sSearch' ) > 0";
        }
        
        if ( self::isFilter('in_id') ) {
            $ids = is_array($this->filter['in_id']) ? $this->filter['in_id'] : array( $this->filter['in_id'] );
        	$this->aSQL['where'][] = $GLOBALS['DB']->parse( 'l.id IN (?l)', $ids );
        }
        
        if ( self::isFilter('date_from') ) {
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'l.act_time >= ?::timestamp without time zone', $this->filter['date_from'] );
        }
        
        if ( self::isFilter('date_to') && $this->filter['date_to'] < $sCurrDate ) {
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'l.act_time <= ?::timestamp without time zone', date('Y-m-d', strtotime($this->filter['date_to'].'+1 day')) );
        }
        
        if ( self::isFilter('act_id') ) {
            $ids = is_array($this->filter['act_id']) ? $this->filter['act_id'] : array( $this->filter['act_id'] );
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'l.act_id  IN (?l)', $ids );
        }
        
        if ( self::isFilter('not-act_id') ) {
            $ids = is_array($this->filter['not-act_id']) ? $this->filter['not-act_id'] : array( $this->filter['not-act_id'] );
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'l.act_id NOT IN (?l)', $ids );
        }
        
        if ( self::isFilter('obj_code') ) {
            $this->aSQL['count_join']['admin_actions'] = 1;
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'a.obj_code = ?i', $this->filter['obj_code'] );
        }
        
        if ( self::isFilter('object_id') ) {
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'l.object_id = ?i', $this->filter['object_id'] );
        }
        
        if ( self::isFilter('admin_id') ) {
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'l.admin_id = ?i', $this->filter['admin_id'] );
        }
        
        if ( self::isFilter('search') ) {
            $sSearch = pg_escape_string( $this->filter['search'] );
            $sAdd    = ( $this->mode == 'proj' && preg_match('/^[\d]+$/', $sSearch) ) ? " OR l.object_id = '{$sSearch}'" : '';
            $sAdd2   = '';
            
            if ( $this->mode == 'proj' || $this->mode == 'offer' ) {
            	$sAdd2 = " OR au.\"login\" ILIKE '%{$sSearch}%' OR au.uname ILIKE '%{$sSearch}%' OR au.usurname ILIKE '%{$sSearch}%'";
            }
            
            if ( $this->mode == 'log') {
                $user = new users();
                $uid = (int)$user->GetUid($err, $sSearch);
                if ($uid) {
                    $sAdd2 = " OR l.user_id = $uid";
                }
            }
            
            $this->aSQL['where'][] = "(l.object_name ILIKE '%{$sSearch}%' OR l.admin_comment ILIKE '%{$sSearch}%' $sAdd $sAdd2)";
        }
        
        if ( self::isFilter('category') ) {
            if ( self::isFilter('sub_category') ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php' );
                //$ids = professions::GetMirroredProfs( $this->filter['sub_category'] );
                if ( $this->mode == 'proj' ) {
                    //$this->aSQL['where'][] = $GLOBALS['DB']->parse( 'EXISTS (SELECT 1 from project_to_spec WHERE project_id = l.object_id AND subcategory_id IN (?l))', $ids );
                    $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'EXISTS (SELECT 1 from project_to_spec WHERE project_id = l.object_id AND subcategory_id = ?i)', $this->filter['sub_category'] );
                }
                elseif ( $this->mode == 'offer' ) {
                    //$this->aSQL['where'][] = $GLOBALS['DB']->parse('fo.subcategory_id IN (?l)', $ids );
                    $this->aSQL['where'][] = $GLOBALS['DB']->parse('fo.subcategory_id = ?i', $this->filter['sub_category'] );
                }
            }
            else {
                if ( $this->mode == 'proj' ) {
                    $this->aSQL['where'][] = $GLOBALS['DB']->parse('EXISTS (SELECT 1 from project_to_spec WHERE project_id = l.object_id AND category_id = ?i)', $this->filter['category'] );
                }
                elseif ( $this->mode == 'offer' ) {
                    $this->aSQL['where'][] = $GLOBALS['DB']->parse('fo.category_id = ?i', $this->filter['category'] );
                }
            }
        }
        
        // ������ �� ����� (����� ������ �����������)
        if ( self::isFilter('time') ) {
            $time = array();
            
            foreach ( $this->filter['time'] as $sTime ) {
                $aTime = $this->getTimePeriod( $sTime, $div );
                
                if ( !$div ) {
                    $time[] = $GLOBALS['DB']->parse( "date_trunc('hour', l.act_time)::time without time zone >= ?::time without time zone 
                        AND date_trunc('hour', l.act_time)::time without time zone < ?::time without time zone", 
                    	$aTime['from'], $aTime['to'] );
                }
                else {
                    $time[] = $GLOBALS['DB']->parse( "(date_trunc('hour', l.act_time)::time without time zone >= ?::time without time zone 
                        AND date_trunc('hour', l.act_time)::time without time zone < ?::time without time zone
                        OR date_trunc('hour', l.act_time)::time without time zone >= ?::time without time zone 
                        AND date_trunc('hour', l.act_time)::time without time zone < ?::time without time zone)", 
                    	$aTime[0]['from'], $aTime[0]['to'], $aTime[1]['from'], $aTime[1]['to'] );
                }
            }
            
            $this->aSQL['where'][] = '('. implode(' OR ', $time) .')';
        }
    }
    
    /**
     * �������� ORDER BY ����� SQL ������� ������� ��������� ��������
     *
     * @param string $order ��� ����������
     * @param int $direction ������� ����������: 0 - �� ��������, �� 0 - �� �����������
     */
    function _setLogOrderBy( $order = "general", $direction = 0 ) {
        $dirSql = ( !$direction ? 'DESC' : 'ASC' );
        
        switch ( $order ) {
            case 'act_id':
                $order = 'act_id';
                $this->aSQL['order_by'][] = "l.act_id $dirSql";
                break;
            case 'object_id':
                $this->aSQL['order_by'][] = "l.object_id $dirSql";
                break;
            case 'object_name':
                if ( $this->mode == 'user' ) {
                    $this->aSQL['order_by'][] = "u.login $dirSql";
                }
                else {
                    $this->aSQL['order_by'][] = "l.object_name $dirSql";
                }
                break;
            case 'general':
            case 'act_time':
            default:
                $this->aSQL['order_by'][] = "l.act_time $dirSql NULLS LAST";
                break;
        }
    }
    
    /**
     * ���������� ������ ����� ������� ������.
     *
     * @param  integer $reasonId ID ������� ������.
     * @return string ������ ����� ������� ������� ������.
     */
    function getAdminReasonText( $reasonId ) {
        return $GLOBALS['DB']->val( 'SELECT reason_text FROM admin_reasons WHERE id = ?', $reasonId );
    }
    
    /**
     * ���������� ������� �������� �����������
     * 
     * @param  int $nActId ��� �������� �� admin_actions
     * @param  bool $bSortBold ����� �� ���������� �� ���� "�������� ������ � ������"
     * @return array
     */
    function getAdminReasons( $nActId = 0, $bSortBold = true ) {
        $sSortBold = $bSortBold ? ' is_bold DESC, ' : '';
        return $GLOBALS['DB']->rows('SELECT * FROM admin_reasons WHERE act_id = ? ORDER BY '. $sSortBold .' reason_name ASC', $nActId );
    }
    
    /**
     * �������� ������� ������� ������.
     * 
     * @param  int $act_id ��� �������� �� admin_actions
     * @param  string $name ������� �������� ������� ������� ������.
     * @param  string $reason ������ ����� ������� ������� ������.
     * @param  string $is_bold �������� ������ t/f
     * @return bool true - �����, false - ������
     */
    function addAdminReason( $act_id = 0, $reason_name = '', $reason_text = '', $is_bold = 'f' ) {
        $data = compact( 'act_id', 'reason_name', 'reason_text', 'is_bold' );
        
        $GLOBALS['DB']->insert( 'admin_reasons', $data );
        
        return ( $GLOBALS['DB']->error ? false : true );
    }
    
    /**
     * ���������� ��� ������ ���������� ������� ������� ������.
     *
     * @param  integer $reasonId ID ������� ������� ������.
     * @return arrary
     */
    function getAdminReason( $reasonId ) {
        $sql = 'SELECT * FROM admin_reasons WHERE id = ?';
        return $GLOBALS['DB']->row( $sql, $reasonId );
    }
    
    /**
     * �������� ������� ������� ������.
     *
     * @param  int $id ID ������� ������� ������.
     * @param  string $name ������� �������� ������� ������� ������.
     * @param  string $reason ������ ����� ������� ������� ������.
     * @param  string $is_bold �������� ������ t/f
     * @return bool true - �����, false - ������
     */
    function updateAdminReason( $id = 0, $reason_name = '', $reason_text = '', $is_bold = 'f' ) {
        $data = compact( 'reason_name', 'reason_text', 'is_bold' );
        
        $GLOBALS['DB']->update( 'admin_reasons', $data, 'id = ?', $id );
        
        return ( $GLOBALS['DB']->error ? false : true );
    }
    
    /**
     * �������� ��������� ������ ������� ������� ������.
     * 
     * @param  int $id ID ������� ������� ������.
     * @param  string $is_bold �������� ������ t/f
     * @return bool true - �����, false - ������
     */
    function setReasonBold( $id = 0, $is_bold = 'f' ) {
        $GLOBALS['DB']->update( 'admin_reasons', array('is_bold' => $is_bold), 'id = ?', $id );
        return ( $GLOBALS['DB']->error ? false : true );
    }
    
    /**
     * ������� ������� ���������� �������.
     *
     * @param  int $id ID ������� ���������� �������
     * @return bool true - �����, false - ������
     */
    function deleteAdminReason( $id = 0 ) {
        $GLOBALS['DB']->query( 'DELETE FROM admin_reasons WHERE id = ?', $id );
        
        return ( $GLOBALS['DB']->error ? false : true );
    }
    
    /**
     * ��������� �������� � ��� ��������� ��������.
     * 
     * @param  int $object_code ��� �������
     * @param  int $act_id ��� �������� �� admin_actions
     * @param  int $user_id UID ������������, ���������� ������
     * @param  int $object_id ID �������, ��� ������� ��������� ��������
     * @param  string $object_name �������� �������
     * @param  string $object_link ������ �� ������
     * @param  int $context_code ��� �������� �������� (�� admin_actions.ojb_code, -1 - �������)
     * @param  string $context_link ������ �� ��������
     * @param  string $context_name �������� ���������
     * @param  int $reason_id ID ������� ��������
     * @param  string $sComment ����� ����������� ����������
     * @param  int $admin_id �����������: UID ����������. ����� ������������ $_SESSION['uid']
     * @return bool true - �����, false - ������
     */
    function addLog( $object_code, $act_id, $user_id, $object_id, $object_name, $object_link, $context_code, $context_link, $reason_id, $admin_comment, $src_id = null, $context_name = '', $admin_id = 0 ) {
        $GLOBALS['DB']->update( 'admin_log', array('last_act' => 0), 'object_code = ?i AND object_id = ?i', 
    	   $object_code, $object_id
    	);
        
        if ( !$GLOBALS['DB']->error ) {
            $admin_id = $admin_id ? $admin_id : $_SESSION['uid'];
        	$aData = compact( 'object_code', 'act_id', 'user_id', 'object_id', 'object_name', 'object_link', 'context_code', 'context_link', 'reason_id', 'admin_comment', 'src_id', 'context_name', 'admin_id' );
            
            $aData['last_act'] = $act_id;
            
            $GLOBALS['DB']->insert( 'admin_log', $aData );
        }
        
        return ( $GLOBALS['DB']->error ? false : true );
    }
    
    /**
     * ������� ����������� � �������� ������ �� ��� ��� �������� �����������.
     * 
     * ����� ��������� ���� �������, ���������� ������������� ��������� /classes/pgq/mail_cons.php �� �������.
     * ���� ��� �����������, �� �������� ������.
     * @see pmail::AdminLogCommentsMail()
     * @see PGQMailSimpleConsumer::finish_batch()
     * 
     * @param  string|array $message_ids �������������� ������������.
     * @param  resource $connect ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return array ������ ������������ ��� ������ ������
     */
    function GetComments4Sending( $message_ids, $connect = NULL ) {
        $aRet = array();
        
        if ( $message_ids ) {
            if( is_array($message_ids) ) {
                $message_ids = implode( ',', array_unique($message_ids) );
        	}
        	
        	// c - �����������, u - ����� ����������, s - ����� ������������� ����������, a - �����, t - ��������
        	$sQuery = "SELECT 
                    t.*, c.id AS comment_id, c.msgtext, act.act_name, act.obj_code, 
                    u.uid, u.login, u.uname, u.usurname, u.email, u.subscr, u.is_banned, 
                    s.uid AS s_uid, s.login AS s_login, s.uname AS s_uname, s.usurname AS s_usurname, s.email AS s_email, s.subscr AS s_subscr, s.is_banned AS s_banned, 
                    a.uid AS a_uid, a.login AS a_login, a.uname AS a_uname, a.usurname AS a_usurname, a.email AS a_email, a.subscr AS a_subscr, a.is_banned AS a_banned 
                FROM ( 
                    SELECT * FROM admin_log_comments WHERE id IN ($message_ids) LIMIT ALL 
                ) AS c 
                LEFT JOIN users u ON u.uid = c.from_id 
                LEFT JOIN admin_log_comments par ON par.id = c.reply_to 
                LEFT JOIN users s ON s.uid = par.from_id 
                LEFT JOIN admin_log t ON t.id = c.log_id 
                LEFT JOIN users a ON a.uid = t.admin_id
                LEFT JOIN admin_actions act ON act.id = t.act_id";
        	
        	$aRet = $GLOBALS['DB']->rows( $sQuery );
        }
        
        return $aRet;
    }
    
    /**
     * �������� ������ �� ��� ��� �������� ����������� � ����� ��������� �����������
     * 
     * ����� ��������� ���� �������, ���������� ������������� ��������� /classes/pgq/mail_cons.php �� �������.
     * ���� ��� �����������, �� �������� ������.
     * @see pmail::AdminLogNotice()
     * @see PGQMailSimpleConsumer::finish_batch()
     * 
     * @param  string|array $log_ids �������������� ��������.
     * @param  resource $connect ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return array ������ ������ ��� ������ ������
     */
    function GetNotices4Sending( $log_ids, $connect = NULL ) {
        $aRet = array();
        
        if ( $log_ids ) {
        	if ( is_array($log_ids) ) {
                $log_ids = implode( ',', array_unique($log_ids) );
        	}
        	
        	$sQuery = "SELECT l.*, n.email, n.admin_id AS notice_uid, act.act_name, act.obj_code, act.rights, 
        	   u.uid, u.login, u.uname, u.usurname, a.uid AS a_uid, a.login AS a_login, a.uname AS a_uname, a.usurname AS a_usurname 
        	FROM (
                SELECT * FROM admin_log WHERE id IN ($log_ids) LIMIT ALL 
        	) AS l 
        	INNER JOIN admin_log_notice_act na ON na.act_id = l.act_id 
        	INNER JOIN admin_log_notice n ON na.admin_id = n.admin_id 
        	LEFT JOIN users u ON n.admin_id = u.uid
        	LEFT JOIN users a ON l.admin_id = a.uid
        	LEFT JOIN admin_actions act ON act.id = l.act_id";
        	
        	$aRet = $GLOBALS['DB']->rows( $sQuery );
        }
        
        return $aRet;
    }
    
    ////////////////////////////////////////////////////////
    //                                                    //
    //                STATISTICS FUNCTIONS                //
    //                                                    //
    ////////////////////////////////////////////////////////
    
    /**
     * ���������� ���������� �� ����������� �����
     * 
     * @param  int $nUserId UID ������
     * @return array
     */
    function getStatByAdmin( $nUserId = 0 ) {
        // ���� �� ����� - �� ���������� ������ �� �������� �� ������� ���� ����� �����
        if ( !in_array('all', $this->user_permissions) ) {
            $sRights = "position('|'||rights||'|' in '|". implode('|', $this->user_permissions) ."|' ) > 0 AND ";
        }
        
        return $GLOBALS['DB']->rows( "SELECT a.id, a.act_name, a.obj_code, 
            COUNT(CASE WHEN l.act_time >= date_trunc('day', NOW())::date - 30 THEN 1 ELSE NULL END) AS month, 
            COUNT(CASE WHEN l.act_time >= date_trunc('day', NOW())::date - 7 THEN 1 ELSE NULL END) AS week, 
            COUNT(CASE WHEN l.act_time >= date_trunc('day', NOW())::date - 1 AND l.act_time < date_trunc('day', NOW())::date THEN 1 ELSE NULL END) AS yesterday, 
            COUNT(CASE WHEN l.act_time >= date_trunc('day', NOW())::date THEN 1 ELSE NULL END) AS today, 
            COUNT(*) AS total 
        FROM admin_actions a 
        LEFT JOIN admin_log l ON a.id = l.act_id 
        WHERE $sRights l.admin_id = ?i 
        GROUP BY l.admin_id, a.id, a.act_name, a.obj_code
        ORDER BY a.id", $nUserId );
    }
    
    /**
     * ���������� ���������� ��������� ��������
     * 
     * @param  array $filter ������
     * @param  string order ��� ����������
     * @param  int $direction ������� ����������: 0 - �� ��������, �� 0 - �� �����������
     * @return array
     */
    function getAllStat( $filter = array(), $order = 'general', $direction = 1 ) {
        $this->filter = $filter;
        $this->aSQL   = array();
        
        // ������ ������
        $this->_setAllStatSelect();
        $this->_setAllStatJoin();
        $this->_setStatWhere();
        $this->_setAllStatOrderBy( $order, $direction );
        
        // �������� ������� ��������� ��������
        $sQuery = 'SELECT ' . implode(', ', $this->aSQL['select']) 
            . ' FROM admin_log l ' 
            . ( $this->aSQL['join'] ? ' ' . implode(' ', $this->aSQL['join']) : '' ) 
            . ( $this->aSQL['where'] ? ' WHERE ' . implode(' AND ', $this->aSQL['where']) : '' ) 
            . ' GROUP BY l.admin_id, u.login, u.uname, u.usurname '
            . ' ORDER BY ' . implode(', ', $this->aSQL['order_by']);
//echo $GLOBALS['DB']->parse( $sQuery );
        $stat = $GLOBALS['DB']->rows( $sQuery );
        
        if ( $GLOBALS['DB']->error || !$stat ) {
            return array();
        }
        
        return $stat;
    }
    
    /**
     * �������� SELECT ����� SQL ������� ���������� ��������� ��������
     */
    function _setAllStatSelect() {
        $this->aSQL['select'] = array( 'l.admin_id, u.login, u.uname, u.usurname' );
        
        if ( self::isFilter('act_id') ) {
            $ids = is_array($this->filter['act_id']) ? $this->filter['act_id'] : array( $this->filter['act_id'] );
            
            foreach ( $ids as $sOne ) {
            	$this->aSQL['select'][] = $GLOBALS['DB']->parse( "count(CASE WHEN l.act_id=?i THEN 1 ELSE NULL END) AS cnt?i", 
            	   $sOne, $sOne );
            }
        }
    }
    
    /**
     * �������� JOIN ����� SQL ������� ���������� ��������� ��������
     */
    function _setAllStatJoin() {
        $this->aSQL['join']['admin_actions'] = 'INNER JOIN admin_actions a ON a.id = l.act_id';
        $this->aSQL['join']['user'] = 'LEFT JOIN users u ON u.uid = l.admin_id';
    }
    
    /**
     * �������� WHERE ����� SQL ������� ���������� ��������� ��������
     */
    function _setStatWhere() {
        $sCurrDate = date('Y-m-d');
        $this->aSQL['where'] = array();
        
        // ���� �� ����� - �� ���������� ������ �� �������� �� ������� ���� ����� �����
        if ( !in_array('all', $this->user_permissions) ) {
            $sSearch = '|'. implode('|', $this->user_permissions) .'|';
            $this->aSQL['where'][] = "position('|'||a.rights||'|' in '$sSearch' ) > 0";
        }
        
        // ������ �� ������
        if ( self::isFilter('admin_id') ) {
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'l.admin_id = ?i', $this->filter['admin_id'] );
        }
        
        // ������ �� ���� ��������
        if ( self::isFilter('act_id') ) {
            $ids = is_array($this->filter['act_id']) ? $this->filter['act_id'] : array( $this->filter['act_id'] );
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'l.act_id  IN (?l)', $ids );
        }
        
        // ������ �� ���� (������)
        if ( self::isFilter('date_from') ) {
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( "l.act_time::date >= ?::date", $this->filter['date_from'] );
        }
        
        if ( self::isFilter('date_to') && $this->filter['date_to'] < $sCurrDate ) {
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( "l.act_time::date <= ?::date", $this->filter['date_to'] );
        }
        
        // ������ �� ����� (����� ������ �����������)
        if ( self::isFilter('time') ) {
            $time = array();
            
            foreach ( $this->filter['time'] as $sTime ) {
                $aTime = $this->getTimePeriod( $sTime, $div );
                
                if ( !$div ) {
                    $time[] = $GLOBALS['DB']->parse( "date_trunc('hour', l.act_time)::time without time zone >= ?::time without time zone 
                        AND date_trunc('hour', l.act_time)::time without time zone < ?::time without time zone", 
                    	$aTime['from'], $aTime['to'] );
                }
                else {
                    $time[] = $GLOBALS['DB']->parse( "(date_trunc('hour', l.act_time)::time without time zone >= ?::time without time zone 
                        AND date_trunc('hour', l.act_time)::time without time zone < ?::time without time zone
                        OR date_trunc('hour', l.act_time)::time without time zone >= ?::time without time zone 
                        AND date_trunc('hour', l.act_time)::time without time zone < ?::time without time zone)", 
                    	$aTime[0]['from'], $aTime[0]['to'], $aTime[1]['from'], $aTime[1]['to'] );
                }
            }
            
            $this->aSQL['where'][] = '('. implode(' OR ', $time) .')';
        }
        
        // ������ �� ������
        if ( self::isFilter('search') ) {
            $sSearch = pg_escape_string( $this->filter['search'] );
            $this->aSQL['where'][] = "(u.login ILIKE '%{$sSearch}%' OR u.uname ILIKE '%{$sSearch}%' OR u.usurname ILIKE '%{$sSearch}%')";
        }
        
        if ( self::isFilter('admin_id') ) {
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'l.admin_id = ?i', $this->filter['admin_id'] );
        }
    }
    
    /**
     * �������� ORDER BY ����� SQL ������� ���������� ��������� ��������
     * 
     * @param string $order ��� ����������
     * @param int $direction ������� ����������: 0 - �� ��������, �� 0 - �� �����������
     */
    function _setAllStatOrderBy( $order = "general", $direction ) {
        $dirSql = ( !$direction ? 'DESC' : 'ASC' );
        
        $sGeneral = "u.usurname $dirSql NULLS LAST, u.uname $dirSql NULLS LAST ";
        
        switch ( $order ) {
            case 'general':
            case 'moder':
                $this->aSQL['order_by'][] = $sGeneral;
                break;
            default:
                $bFound = false;
                
                if ( self::isFilter('act_id') ) {
                    $ids = is_array($this->filter['act_id']) ? $this->filter['act_id'] : array( $this->filter['act_id'] );
                    
                    foreach ( $ids as $sOne ) {
                        if ( $sOne == $order ) {
                        	$bFound = true;
                        	break;
                        }
                    }
                }
                
                if ( $bFound ) {
                	$this->aSQL['order_by'][] = "cnt$order $dirSql, u.usurname ASC NULLS LAST, u.uname ASC NULLS LAST ";
                }
                else {
                    $this->aSQL['order_by'][] = $sGeneral;
                }
                break;
        }
    }
    
    /**
     * ���������� ������ ��� �������� ���������� ��������� ��������
     * 
     * @param  array $filter ������
     * @return array
     */
    function getGrafikStat( $filter = array() ) {
    	$this->filter = $filter;
        $this->aSQL   = array();
        
        $this->_setGrafikSelectI();
        $this->_setGrafikSelectO();
        $this->_setAllStatJoin();
        $this->_setStatWhere();
        
        $sQuery = 'SELECT ' . implode(', ', $this->aSQL['selectO']) 
            . ' FROM ( SELECT '. implode(', ', $this->aSQL['selectI']) . ' FROM admin_log l '
            . ( $this->aSQL['join'] ? ' ' . implode(' ', $this->aSQL['join']) : '' ) 
            . ( $this->aSQL['where'] ? ' WHERE ' . implode(' AND ', $this->aSQL['where']) : '' ) 
            . ' )  AS i GROUP BY i.fld_time ORDER BY i.fld_time';
//echo $GLOBALS['DB']->parse( $sQuery );
        $stat = $GLOBALS['DB']->rows( $sQuery );
        
        if ( $GLOBALS['DB']->error || !$stat ) {
            return array();
        }
        
        return $stat;
    }
    
    /**
     * �������� SELECT ����� SQL ����������� ������� ��� �������� ���������� ��������� ��������
     */
    function _setGrafikSelectI() {
        $this->aSQL['selectI'] = array( "date_trunc('day', act_time)::date AS fld_time" );
        
        if ( self::isFilter('time') ) {
            foreach ( $this->filter['time'] as $key => $sTime ) {
                $aTime = $this->getTimePeriod( $sTime, $div );
                
                if ( !$div ) {
                    $this->aSQL['selectI'][] = 
                    $GLOBALS['DB']->parse( "CASE WHEN date_trunc('hour', l.act_time)::time without time zone >= ?::time without time zone 
                        AND date_trunc('hour', l.act_time)::time without time zone < ?::time without time zone 
                        THEN 1 ELSE 0 END AS fld$key", 
                    	$aTime['from'], $aTime['to'] );
                }
                else {
                    $this->aSQL['selectI'][] = 
                    $GLOBALS['DB']->parse( "CASE WHEN date_trunc('hour', l.act_time)::time without time zone >= ?::time without time zone 
                        AND date_trunc('hour', l.act_time)::time without time zone < ?::time without time zone
                        OR date_trunc('hour', l.act_time)::time without time zone >= ?::time without time zone 
                        AND date_trunc('hour', l.act_time)::time without time zone < ?::time without time zone 
                        THEN 1 ELSE 0 END AS fld$key", 
                    	$aTime[0]['from'], $aTime[0]['to'], $aTime[1]['from'], $aTime[1]['to'] );
                }
            }
        }
    }
    
    /**
     * �������� SELECT ����� SQL �������� ������� ��� �������� ���������� ��������� ��������
     */
    function _setGrafikSelectO() {
        $this->aSQL['selectO'] = array( 'i.fld_time' );
        
        if ( self::isFilter('time') ) {
            $time = array();
            
            foreach ( $this->filter['time'] as $key => $val ) {
                $this->aSQL['selectO'][] = "SUM(i.fld$key) AS sum$key";
            }
        }
    }
    
    /**
     * ��������� Excel ����� �� ��������� ���� �������
     * 
     * @param array $actions ������ ��������� ��������
     * @param array $filter ������
     * @param string order ��� ����������
     * @param int $direction ������� ����������: 0 - �� ��������, �� 0 - �� �����������
     */
    function printReportAll( $actions = array(), $filter = array(), $order = 'general', $direction = 1 ) {
        $sCurrDate = date('Y-m-d');
        
        // ������
        $this->filter  = $filter;
        $stat          = $this->getAllStat( $filter, $order, $direction );
        $sDateInterval = '';
        $sModerator    = '';
        
        // admin
        if ( self::isFilter('admin_id') ) {
        	require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        	
            $oUser = new users();
            $oUser->GetUserByUID( $this->filter['admin_id'] );
            
            $sModerator = $oUser->uname . ' ' . $oUser->usurname . ' [' . $oUser->login . ']';
        }
        
        // ������
        if ( self::isFilter('date_from') ) {
            $sDateInterval .= 'c' . $this->filter['date_from'];
        }
        
        if ( self::isFilter('date_to') && $this->filter['date_to'] < $sCurrDate ) {
            $sDateInterval .= ($sDateInterval ? ' ' : '') . '�� ' . $this->filter['date_to'];
        }
        
        if ( !$sDateInterval ) {
        	$sDateInterval = '�� ��� �����';
        }
        
        // ��� ��������� �����
        $sWorkTitle  = 'moderators.'.$sCurrDate.'.xls';
        
        // ���������� pear
        require_once( 'Spreadsheet/Excel/Writer.php' );
        
        // ������� ��������
        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->setVersion( 8 );
        
        // ������� ����
        $worksheet =& $workbook->addWorksheet( '1' );
        $worksheet->setInputEncoding( 'CP1251' );
        
        $th_sty     = array('FontFamily'=>'Arial', 'Size'=>10, 'Align'=>'center', 'Border'=>1, 'BorderColor'=>'black', 'Bold'=>1);
        $format_top =& $workbook->addFormat( $th_sty );
        $total_sty  = array('FontFamily'=>'Arial', 'Size'=>10, 'Bold'=>1);
        $format_tot =& $workbook->addFormat( $total_sty );
        
        $worksheet->write( 0, 0, $sDateInterval );
        $worksheet->write( 1, 0, '���������: '. ($sModerator ? $sModerator : '���') );
        
        // �����
        if ( self::isFilter('time') ) {
            $col = 1;
            
            foreach ( $this->filter['time'] as $sTime ) {
                $worksheet->write( 1, $col, $sTime );
                $col++;
            }
        }
        
        $row = 3;
        
        // ����� - ��������
        if ( self::isFilter('act_id') ) { 
            $col = 1;
            
            $worksheet->write( $row, 0, '���������', $format_top );
            
            foreach ( $this->filter['act_id'] as $idx ) { 
                foreach ( $actions as $aOne ) {
                    if ( $aOne['id'] == $idx ) {
                        $sName  = 'nCnt'.$idx;
                        $$sName = 0;
                        $worksheet->write( $row, $col, preg_replace('~<br ?/?>~', ' ', $aOne['act_short_name']), $format_top );
                        $col++;
                    }
                }
            }
            
            $row++;
        }
        
        // ��������
        if ( $stat ) {
        	foreach ( $stat as $aOne ) {
        	    $col = 1;
        	    $sModerator = $aOne['login'] ? "{$aOne['uname']} {$aOne['usurname']} [{$aOne['login']}]" : '[�� ��������]';
        	    $worksheet->write( $row, 0, $sModerator );
        	    
        	    if ( self::isFilter('act_id') ) {
        	        foreach ( $this->filter['act_id'] as $idx ) {
        	            $sName   = 'nCnt'.$idx;
                        $$sName += $aOne['cnt'.$idx];
        	            $worksheet->write( $row, $col, $aOne['cnt'.$idx] );
        	            $col++;
        	        }
        	    }
        	    
        	    $row++;
        	}
        }
        
        // �����
        $worksheet->write( $row, 0, '�����', $format_tot );
        
        if ( self::isFilter('act_id') ) { 
            $col = 1;
            
            foreach ( $this->filter['act_id'] as $idx ) {
                $sName = 'nCnt'.$idx;
                $worksheet->write( $row, $col, $$sName, $format_tot );
                $col++;
            }
        }
        
        // ���������� �� ����������
        $workbook->send( $sWorkTitle );
        
        // ��������� ��������
        $workbook->close();
    }
    
    /**
     * ��������� Excel ����� �� ��������� ����������� ������
     * 
     * @param string $sAdminName ��� ������
     * @param string $sAdminSurname ������� ������
     * @param array $filter ������
     */
    function printReport( $sAdminName = '', $sAdminSurname = '', $filter = array() ) {
        // ��� ��������� �����
        $sWorkTitle  = 'Admin report.xls';
        
        // ���������� pear
        require_once( 'Spreadsheet/Excel/Writer.php' );
        
        // ������� ��������
        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->setVersion( 8 );
        
        // ������� ����
        $worksheet =& $workbook->addWorksheet( '1' );
        $worksheet->setInputEncoding( 'CP1251' );
        
        $worksheet->write( 0, 0, '������������� '. $sAdminName .' '. $sAdminSurname .' ['. $log[0]['adm_login'] .']' );
        $worksheet->write( 1, 0, $GLOBALS['host'].'/users/'. $log[0]['adm_login'] );
        
        $d_sty  = array('NumFormat' => 'DD MMM, YYYY HH:MM:SS' );
        $td_sty = array('FontFamily'=>'Calibri', 'VAlign'=>'vequal_space', 'Align'=>'center', 'Border'=>1, 'BorderColor'=>'black');
        $th_sty = array('FontFamily'=>'Arial', 'Size'=>10, 'Align'=>'center', 'Border'=>1, 'BorderColor'=>'black', 'Bold'=>1);
        
        $format_top  =& $workbook->addFormat( $th_sty );
        $format_td   =& $workbook->addFormat( $td_sty );
        $format_date =& $workbook->addFormat( array_merge($td_sty, $d_sty) );
        
        $format_top->setTextWrap( 1 );
        
        $aHeader = array( '��������', '������', '������', '����',  );
        
        for ( $i = 0; $i<count($aHeader); $i++ ) {
            $worksheet->write( 3, $i, $aHeader[$i], $format_top );
        }
        
        // ������
        $this->filter = $filter;
        
        $log = $this->getLog( $count, 1, 'general', 0, true );
        
        if ( $log ) {
            $nCnt = 1;
            
            foreach ( $log as $aOne ) {
                $sObjName = $aOne['object_name'] ? $aOne['object_name'] : '<��� ��������>';
                $sObjLink = $aOne['object_link'] ? getAbsUrl($aOne['object_link']) : '';
                $sDate    = $aOne['act_time'] ? date( 'Y-m-d H:i:s', strtotime($aOne['act_time']) ) : '�� ��������';
                
                $worksheet->write( $nCnt+3, 0, $aOne['act_name'] . ' '.admin_log::$aObj[$aOne['obj_code']]['short'], $format_td );
                $worksheet->write( $nCnt+3, 1, $sObjName, $format_td );
                $worksheet->write( $nCnt+3, 2, $sObjLink, $format_td );
                $worksheet->write( $nCnt+3, 3, $sDate, $format_date );
                
                $nCnt++;
            }
        }
        
        // ���������� �� ����������
        $workbook->send( $sWorkTitle );
        
        // ��������� ��������
        $workbook->close();
    }
    
    ////////////////////////////////////////////////////////
    //                                                    //
    //                  UTILITY FUNCTIONS                 //
    //                                                    //
    ////////////////////////////////////////////////////////
    
    /**
     * ���������� ���� (������)
     * 
     * @param  string $error ��������� ��������� �� ������ ��� ������ ������
     * @param  string $fromD ���� 
     * @param  string $fromM ����� 
     * @param  string $fromY ��� 
     * @param  string $toD ���� 
     * @param  string $toM ����� 
     * @param  string $toY ��� 
     * @return array
     */
    function getDatePeriod( &$error, $fromD = '', $fromM = '', $fromY = '', $toD = '', $toM = '', $toY = '' ) {
        $aRet  = array();
        $error = '';
        
        if ( $fromD == '' && $fromM == '' && $fromY == '' ) {
        	$fromDate = null;
        }
        else {
            if ( $fromD == '' || $fromM == '' || $fromY == '' ) {
            	$error = '������� ��������� ����';
            }
            else {
                $fromDate = $fromY.'-'.$fromM.'-'.(strlen($fromD) > 1 ? $fromD : '0'.$fromD);
                
                if ( ($fromRes = strtotime($fromDate)) === false ) {
                    $error = '������� ���������� ��������� ����';
                }
            }
        }
        
        if ( !$error ) {
            if ( $toD == '' && $toM == '' && $toY == '' ) {
            	$toDate = null;
            }
            else {
                if ( $toD == '' || $toM == '' || $toY == '' ) {
                	$error = '������� �������� ����';
                }
                else {
                    $toDate = $toY.'-'.$toM.'-'.(strlen($toD) > 1 ? $toD : '0'.$toD);
                    
                    if ( ($toRes = strtotime($toDate)) === false ) {
                        $error = '������� ���������� �������� ����';
                    }
                }
            }
            
            if ( !$error && $fromDate && $toDate ) {
                if ( $toRes < $fromRes ) {
                	$error = '�������� ���� �� ����� ���� ������ ���������';
                }
            }
        }
        
        if ( !$error ) {
            $aRet = array( 'date_from' => $fromDate, 'date_to' => $toDate );
        }
        
        return $aRet;
    }
    
    /**
     * ��������� ������������ ���������� �������.
     * 
     * @param  bool $bIsNull ���������� true ���� ������ ������� �� ������
     * @param  string $fromH ��� ������ �������
     * @param  string $fromI ������ ������ �������
     * @param  string $toH ��� ��������� �������
     * @param  string $toI ������ ��������� �������
     * @return string ��������� �� ������ ��� ������ ������
     */
    function checkTimePeriod( &$bIsNull, $fromH = '', $fromI = '', $toH = '', $toI = '' ) {
        $bIsNull = false;
        $sError  = '';
        
        if ( $fromH && $toH && $fromI && $toI ) {
            $pattern = '/^(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9])(:([0-5]?[0-9]))?$/';
            
            if ( 
                !preg_match( $pattern, $fromH . ':' . $fromI . ':00' ) 
                || !preg_match( $pattern, $toH . ':' . $toI . ':00' ) 
            ) {
            	$sError = '������� ���������� ������ �������';
            }
        }
        elseif ( $fromH || $toH || $fromH || $toH ) {
            $sError = '������� ��� ��������� ������� ������� ��� �� ������';
        }
        else {
            $bIsNull = true;
        }
        
        return $sError;
    }
    
    /**
     * ���������� ������ �� ������� �������
     * 
     * @param  string $sTimePeriod ������ ������� '14:00 - 20:00'
     * @param  bool $div ���������� true ���� ������ ������ �������� �� 2 ����� ��������� ����� �����
     * @return array
     */
    function getTimePeriod( $sTimePeriod, &$div ) {
        $aParts = explode( ' - ', $sTimePeriod );
        $sFrom  = $aParts[0].':00';
        $sTo    = $aParts[1].':00';
        
    	if ( $sFrom <= $sTo ) {
    	    $div = false;
    		return array('from' => $sFrom, 'to' => $sTo);
    	}
    	else {
    	    $div = true;
    	    return array( 
                array('from' => $sFrom, 'to' => '23:59:59'), 
                array('from' => '00:00:00', 'to' => $sTo) 
    	    );
    	}
    }
    
}