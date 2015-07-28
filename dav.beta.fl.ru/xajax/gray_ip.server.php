<?php
/**
 * ����� ������ IP
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
$rpath = '../';
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/gray_ip.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/gray_ip.common.php' );

/**
 * ��������� ������ ������������ � ����� ������ IP
 * 
 * @param  int $nUserId UID ������������
 * @param  string $sUserLogin ����� ������������
 * @param  int $nAdminId UID ������
 * @param  string $sIp ������ IP �������
 * @return object xajaxResponse
 */
function addPrimaryIp( $nUserId = 0, $sUserLogin = '', $nAdminId = 0, $sIp = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    $sIp = change_q( trim(stripslashes($sIp)), true );
    $aIp = users::CheckSafetyIP( $sIp );
    
    if ( !$aIp['error_flag'] ) {
    	$bRes = gray_ip::addPrimaryIp( $nUserId, $sUserLogin, $nAdminId, $aIp['ip_addresses'] );
    	
    	if ( $bRes ) {
    	    $objResponse->script( 'gray_ip.clearAdd();' );
    		$objResponse->alert( 'IP ������ ������� ���������' );
    	}
    	else {
    	    $objResponse->alert( '������ ���������� IP �������' );
    	}
    }
    else {
        $objResponse->alert( $aIp['alert'][1] );
    }
    
    return $objResponse;
}

/**
 * ����� ���������� ������� ������������ � ����� ������ IP
 * 
 * @param  int $nNum ����� html ����� ���� ������������� �����.
 * @param  int $nUserId UID ������������
 * @param  int $nAdminId UID ������
 * @return object xajaxResponse
 */
function getPrimaryIpForm( $nNum = 0, $nUserId = 0, $nAdminId = 0 ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    $aIp = gray_ip::getPrimaryIpByUid( $nUserId );
    
    if ( $aIp ) {
        $aTmp = array();
        
        foreach ( $aIp as $aOne ) {
    		$aTmp[] = $aOne['ip'];
    	}
        
    	$sOut = '<div class="form fs-o">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                    <input type="hidden" name="uid_edit_'.$nNum.'" id="uid_edit_'.$nNum.'" value="'.$nUserId.'" />
                    <input type="hidden" name="adm_edit_'.$nNum.'" id="adm_edit_'.$nNum.'" value="'.$nAdminId.'" />
                    <input type="hidden" name="log_edit_'.$nNum.'" id="log_edit_'.$nNum.'" value="'.$aIp[0]['user_login'].'" />
                    <h4>������������� ������ IP-������� ��� ['. $aIp[0]['user_login'] .']</h4>
                    <div class="form-el">
                        <textarea name="txt_edit_'.$nNum.'" id="txt_edit_'.$nNum.'" cols="" rows="">'. implode( "\n", $aTmp ) .'</textarea>
                    </div>
                    <div class="form-el form-btns">
                        <input name="btn_edit_'.$nNum.'" id="btn_edit_'.$nNum.'" onclick="gray_ip.submitEdit('.$nNum.')" type="button" value="���������" />
                        <a href="javascript:void(0);" onclick="gray_ip.clearEdit('.$nNum.')" class="lnk-dot-666">��������</a>
                    </div>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>';
        
    	$objResponse->script('gray_ip.clearEditAll();');
        $objResponse->assign( 'edit_ip_'.$nNum, 'innerHTML', $sOut );
    }
    
    return $objResponse;
}

/**
 * ��������� ������ ������������ � ����� ������ IP
 * 
 * @param  int $nUserId UID ������������
 * @param  string $sUserLogin ����� ������������
 * @param  int $nAdminId UID ������
 * @param  string $sIp ������ IP �������
 * @return object xajaxResponse
 */
function setPrimaryIp( $nUserId = 0, $sUserLogin = '', $nAdminId = 0, $sIp = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    $objResponse->script('gray_ip.clearEditAll();');
    
    $sIp = change_q( trim(stripslashes($sIp)), true );
    $aIp = users::CheckSafetyIP( $sIp );
    
    if ( !$aIp['error_flag'] ) {
        $bRes = gray_ip::updatePrimaryIp( $nUserId, $sUserLogin, $nAdminId, $aIp['ip_addresses'], $bDel );
        
        if ( $bRes ) {
        	$objResponse->alert( 'IP ������ ������� ���������' . ($bDel ? "\n�������� ����� �������������" : '') );
        	
        	if ( $bDel ) {
        		$objResponse->script( 'window.location.reload(true)' );
        	}
        }
        else {
    	    $objResponse->alert( '������ ���������� IP �������' );
    	}
    }
    else {
        $objResponse->alert( $aIp['alert'][1] );
    }
    
    return $objResponse;
}

$xajax->processRequest();