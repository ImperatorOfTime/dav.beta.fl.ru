<?php
/**
 * ���������� ������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/admin_parent.php");

/**
 * ����� ��� ������� � ����� � ������ ����������
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class messages_spam extends admin_parent {    
    /**
     * ��������� ���������
     */
    const COMPLAINT_PENDING_TXT = '���� ������ �� ������������';
    
    public $resolve = array(
        0 => '��� �� ������', 
        1 => '��� �� ����',
        2 => '��������������',
        3 => '������������',
    );
    
    /**
     * ����������� ������
     * 
     * @param int $items_pp ���������� ������������� �� ��������
     */
    function __construct( $items_pp = 0 ) {
        parent::__construct($items_pp);
    }
    
    /**
     * ��������� ������ �� ���� � ������ ����������
     * 
     * @param  string $sSpamerId UID �������
     * @param  string $sUserId UID ��������������� ������������
     * @param  string $aParams ������ ���������� ������ �� ����
     * @return bool true - �����, false - ������
     */
    function addSpamComplaint( $sSpamerId = '', $sUserId = '', $aParams = array() ) {
        $DB      = new DB('plproxy'); // plproxy
        $bRet    = false;
        $sMsgMd5 = md5( $aParams['msg'] );
        
        if ( $sSpamerId && $sUserId && $aParams ) {
            $DB->val( "SELECT messages_spam_add(?i, ?i, ?i, ?, ?)", 
                $sSpamerId, $sUserId, $aParams['id'], $sMsgMd5, change_q($aParams['txt']) 
            );
            
            if ( !$DB->error ) {
                $bRet    = true;
                $oMemBuf = new memBuff();
                $oMemBuf->delete( 'messages_spam_count' );
            }
        }
        
        return $bRet;
    }
    
    /**
     * �������� ��� ������ �� ������� ���������� (������� ������)
     * 
     * @param  array $aSpamerId ������ UID �������� ��� ������ � �����
     * @param  int $nResolve ������� ������: 0 - ��� �� �����, 1 - ��� �� ����, 2 - ��������������, 3 - ���
     * @return bool true - �����, false - ������
     */
    function deleteSpamBySpamer( $aSpamerId = array(), $nResolve = 0 ) {
        $bRet = false;
        
        if ( $aSpamerId ) {
            if ( !is_array($aSpamerId) ) {
            	$aSpamerId = array( $aSpamerId );
            }
            
            $DB   = new DB('plproxy');
            $DB->query( "SELECT messages_spam_del_spamer(?a, ?i)", $aSpamerId, $nResolve );
        	
            if ( !$DB->error ) {
                $bRet    = true;
                $oMemBuf = new memBuff();
                $oMemBuf->delete( 'messages_spam_count' );
            }
        }
        
        return $bRet;
    }
    
    /**
     * �������� ��� ������ �� ��������� ���������� (������� ������)
     * 
     * @param  int $nSpamerId UID �������
     * @param  string $sMsgMd5 MD5 ��� ������ ���������
     * @param  int $nResolve ������� ������: 0 - ��� �� �����, 1 - ��� �� ����, 2 - ��������������, 3 - ���
     * @return bool true - �����, false - ������
     */
    function deleteSpamByMsg( $nSpamerId = 0, $sMsgMd5 = '', $nResolve = 0 ) {
        $bRet = false;
        
        if ( $nSpamerId && $sMsgMd5 ) {
            $DB   = new DB('plproxy');
            $DB->query( "SELECT messages_spam_del_msg(?i, ?, ?i)", $nSpamerId, $sMsgMd5, $nResolve );
            
        	if ( !$DB->error ) {
                $bRet    = true;
                $oMemBuf = new memBuff();
                
                if ( ($nCount = $oMemBuf->get('messages_spam_count')) !== false ) {
                    $nCount = $nCount - 1;
                    if ($nCount < 0) {
                        $nCount = 0;
                    }
                    $oMemBuf->set( 'messages_spam_count', $nCount, 3600 );
                }
                else {
                    $oMemBuf->delete( 'messages_spam_count' );
                    $this->getSpamCount();
                }
            }
        }
        
        return $bRet;
    }
    
    /**
     * ���������� ������ ����� �� ������������� ������������.
     * 
     * @param  string $sUid UID ��������������� ������������
     * @return array
     */
    function getComplaintsByUser( $sUid = '' ) {
        $DB = new DB; // plproxy
        return $DB->rows( 'SELECT * FROM messages_spam_get_user(?i)', $sUid );
    }
    
    /**
     * ���������� ������ ����� �� ���� ��� ������� ���������.
     * 
     * @param  int $nSpamerId UID �������
     * @param  string $sMsgMd5 MD5 ��� ������ ���������
     * @return array
     */
    function getSpamComplaints( $nSpamerId = 0, $sMsgMd5 = '' ) {
        $DB = new DB; // plproxy
        return $DB->rows( 'SELECT * FROM messages_spam_get_msg(?i, ?)', $nSpamerId, $sMsgMd5 );
    }
    
    /**
     * ���������� ������ ����� � �����, ��������������� �������� �������
     * 
     * ���������� �������
     *
     * @param  int $count ���������� ���������� ������� �������������� �������� �������
     * @param  array $filter ������
     * @param  int $page ����� ������� ��������
     * @return array
     */
    function getSpam( &$count, $filter, $page = 1 ) {
        $this->filter = $filter;
        return $this->_getSpam( $count, $page );
    }
    
    /**
     * ���������� ���������� ����� � �����, ��������������� �������� �������
     * 
     * @param  array $filter ������
     * @return int
     */
    function getSpamCount( $filter = array() ) {
        $DB      = new DB; // plproxy
        $aFilter = array();
        $oMemBuf = new memBuff();
        $nCount  = 0;
        
        if ( is_array($filter) && count($filter) ) {
        	foreach ( $filter as $sKey => $sVal ) {
        		$aFilter[] = array( $sKey, $sVal );
        	}
        }
        
        if ( empty($aFilter) && ($nCount = $oMemBuf->get('messages_spam_count')) !== false ) {
        	return $nCount;
        }
        else {
            $sQuery = 'SELECT messages_spam_get_count(?a)';
            $nCount = $DB->val( $sQuery, $aFilter );
            
            if ( empty($aFilter) && !$DB->error ) {
            	$oMemBuf->set( 'messages_spam_count', $nCount, 3600 );
            }
        }
                
        return $nCount;
    }
    
    /**
     * ���������� ������ ����� � �����, ��������������� �������� �������
     * 
     * ���������� �������
     * 
     * @param  int $count ���������� ���������� ������� �������������� �������� �������
     * @param  int $page ����� ������� ��������
     * @param  string order ��� ����������
     * @param  int $direction ������� ����������: 0 - �� ��������, �� 0 - �� �����������
     * @param  bool $unlimited �����������. ���������� � true ���� ����� �������� ��� ������ (��� ������������� ������)
     * @return array
     */
    function _getSpam( &$count, $page = 1, $order = 'general', $direction = 0, $unlimited = false ) {
        $DB         = new DB; // plproxy
        $aFilter    = array();
        $this->aSQL = array();
        $offset     = $this->items_pp * ($page - 1);
        
        // ������ ������
        $this->_setSpamOrderBy( $order, $direction );
        
        if ( is_array($this->filter) && count($this->filter) ) {
        	foreach ( $this->filter as $sKey => $sVal ) {
        		$aFilter[] = array( $sKey, $sVal );
        	}
        }
        
        $sQuery = 'SELECT * FROM messages_spam_get_list(?a) ' 
            . ' ORDER BY ' . implode( ', ', $this->aSQL['order_by'] ) 
            . ( !$unlimited ? ' LIMIT ' . $this->items_pp . ' OFFSET ' . $offset : '' );
        
        $aSpam  = $DB->rows( $sQuery, $aFilter );
        
        if ( $DB->error || !$aSpam ) {
            return array();
        }
        
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        
        // ������������� ����� 
        messages::getMessagesAttaches( $aSpam, 'msg_id' );
        
        $sQuery = 'SELECT messages_spam_get_count(?a)';
        
        $count = $DB->val( $sQuery, $aFilter );
        
        return $aSpam;
    }
    
    /**
     * �������� ORDER BY ����� SQL �������
     *
     * @param string $order ��� ����������
     * @param int $direction ������� ����������: 0 - �� ��������, �� 0 - �� �����������
     */
    function _setSpamOrderBy( $order = 'general', $direction = 0 ) {
        $dirSql = ( !$direction ? 'DESC' : 'ASC' );
        
        switch ( $order ) {
            case 'general':
            case 'time':
            default:
                $this->aSQL['order_by'][] = "complaint_time $dirSql";
                break;
        }
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
     * @param  string $prefix ������� � ����� �������
     * @param  string $fromD ���� 
     * @param  string $fromM ����� 
     * @param  string $fromY ��� 
     * @param  string $toD ���� 
     * @param  string $toM ����� 
     * @param  string $toY ��� 
     * @return array
     */
    function getDatePeriod( &$error, $prefix = '', $fromD = '', $fromM = '', $fromY = '', $toD = '', $toM = '', $toY = '' ) {
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
            $sCurrDate = date('Y-m-d');
            $aRet = array( $prefix.'_date_from' => $fromDate, $prefix.'_date_to' => ($toDate < $sCurrDate ? $toDate : null) );
        }
        
        return $aRet;
    }
}