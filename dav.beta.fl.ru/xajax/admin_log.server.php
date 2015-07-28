<?php
$rpath = '../';
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/admin_log.common.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php' );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/permissions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");

/**
 * ���������� ������ �������������� ������������ ��� ����� ����
 * 
 * @param  int $uid UID ������������
 * @param  array $contextId �������� (��� ���� ��������� ��������)
 * @param  string $draw_func ������ �����������
 * @return object xajaxResponse
 */
function getUserWarns( $uid = 0, $contextId = '', $draw_func = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/permissions.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $user = new users();
        $user->GetUserByUID( $uid );
        
        if ( $user->uid ) {
            $aPermissions = permissions::getUserPermissions( $_SESSION['uid'] );
            $admin_log    = new admin_log( 'user', $_SESSION['uid'], $aPermissions );
        	$aWarns       = $admin_log->getUserWarns( $nCount, $uid );
        	$sCount       = $nCount ? $nCount : '0';
        	$sWarns       = $user->warn ? $user->warn : '0';
        	
        	$objResponse->assign('a_user_warns', 'href', '/users/' . $user->login );
        	$objResponse->assign('s_user_warns', 'innerHTML', $user->uname . ' ' . $user->usurname . ' [' . $user->login . ']' );
        	$objResponse->assign('e_user_warns', 'innerHTML', $sWarns );
        	$objResponse->assign('n_user_warns', 'innerHTML', $sCount );
        	
        	if ( $nCount ) {
        	    $sTable = '<table id="t_user_warns" class="notice-table">';
        	    $nCount = 1;
        	    
        	    foreach ( $aWarns as $aOne ) {
        	        $sReason = $aOne['admin_comment'] ? hyphen_words($aOne['admin_comment'], true) : '&lt;��� �������&gt;';
        	        $sAdmin  = $aOne['adm_login'] ? '<a target="_blank" href="/users/'. $aOne['adm_login'] .'">'. $aOne['adm_login'] .'</a>' : '�� ��������';
        	        $sDate   = $aOne['act_time'] ? date('d.m.Y H:i', strtotime($aOne['act_time'])) : '�� ��������';
        	    	$sTable .= '<tr>
                    	<td class="cell-number">'. $nCount .'.</td>
                    	<td class="cell-uwarn">'. $sReason .'</td>
                    	<td class="cell-who">�����: ['. $sAdmin .']
                    	<td class="cell-date">'. $sDate .'</td>
                        <td'.( $aOne['src_id'] ? ' id="i_user_warns_'. $aOne['src_id'] .'"' : '' ).'>'. ( $aOne['src_id'] ? '<a href="javascript:void(0);" onclick="banned.warnUser('.$uid.','.$aOne['src_id'].',\''.$draw_func.'\',\''.$contextId.'\',0);"><img src="/images/btn-remove2.png" alt="" width="11" height="11" /></a>' : '' ) .'</td>
                    </tr>';
        	    	
        	    	$nCount++;
        	    }
        	    
        	    $sTable .= '</table>';
        	    
        		$objResponse->assign('d_user_warns', 'innerHTML', $sTable );
        	}
        	else {
        	    $objResponse->assign('d_user_warns', 'innerHTML', '&nbsp;' );
        	}
        	
        	$sBanTitle = ( $user->is_banned || $user->ban_where ) ? '���������' : '��������';
        	
        	$objResponse->script( "adminLogOverlayClose();" );
        	$objResponse->script( "$('ov-notice4').setStyle('display', '');" );
        	$objResponse->script( "adjustUserWarnsHTML();" );
        	$objResponse->assign( 'b_user_warns', 'innerHTML', '<button onclick="adminLogOverlayClose();banned.userBan('.$uid.', \''.$contextId.'\',0)">'.$sBanTitle.'</button><a class="lnk-dot-grey" href="javascript:void(0);" onclick="adminLogOverlayClose();">������</a>' );
        }
    }
    
    return $objResponse;
}

/**
 * ��������� ���������� �������
 * 
 * @param  int $project_id ID �������
 * @param  int $act_id ID ������ �������� (admin_actions)
 * @param  int $src_id ID ��������� �������� (projects_blocked)
 * @param  string $reason �������
 * @param  int $reason_id ID �������, ���� ��� ������� �� ������ (������� admin_reasons, ��� act_id = 9)
 * @return object xajaxResponse
 */
function updatePrjBlock( $project_id, $act_id, $src_id, $reason = '', $reason_id = null ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('projects') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
        
        $projects  = new projects;
        $project   = $projects->GetPrjCust( $project_id );
        $sObjLink  = '/projects/?pid=' . $project_id; // ��� ��������� ��������
        $reason_id = ($reason_id) ? $reason_id : null;
        $reason    = str_replace('%USERNAME%', $project['uname'] . ' ' .$project['usurname'], $reason);
        $reason    = change_q( $reason, FALSE, 0, TRUE );
        
        if ( $act_id == 10 && $src_id ) { 
            // ������������ ������
        	$projects->UnBlocked( $project_id );
    		
    		// ����� ��� ��������� ��������
    		admin_log::addLog( admin_log::OBJ_CODE_PROJ, 10, $project['user_id'], $project_id, $project['name'], $sObjLink, 0, '', $reason_id, $reason );
    		
    		// ��� ��� ��������� ����� �������� � ����?
    		$objResponse->script( 'window.location="/siteadmin/admin_log/?site=proj";' );
        }
        elseif ( $act_id == 9 && $src_id ) { 
            // ����������� ������� ���������� � projects_blocked, admin_log ��������� ���������
            admin_log::updateProjBlock( $src_id, $reason, $reason_id );
            
            $reason = reformat($project['blocked_reason'], 24, 0, 0, 1, 24);
            
            $objResponse->script( 'window.location.reload(true)' );
        }
        elseif ( $act_id == 9 && !$src_id ) { 
            // ��������� ������
    		$sBlockId = $projects->Blocked( $project_id, $reason, $reason_id, $_SESSION['uid'] );
    		$project  = $projects->GetPrjCust( $project_id );
    		
    		// ����� ��� ��������� ��������
    		admin_log::addLog( admin_log::OBJ_CODE_PROJ, 9, $project['user_id'], $project_id, $project['name'], $sObjLink, 0, '', $reason_id, $reason, $sBlockId );
    		
    		// ��� ��� ��������� ����� �������� � ����?
    		$objResponse->script( 'window.location="/siteadmin/admin_log/?site=proj";' );
        }
    }
    
    return $objResponse;
}

/**
 * ������������� ���� � ����� �������������� ���������� �������
 * 
 * @param  int $obj_id ID ��������� �������
 * @param  int $last_act ������� ��������� ������� (ID �������� �� admin_actions)
 * @param  int $src_id ID ��������� �������� (projects_blocked)
 * @param  int $edit ���� �������������� ������� ����������
 * @return object xajaxResponse
 */
function setPrjBlockForm( $obj_id, $last_act, $src_id = 0, $edit = 0 ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('projects') ) {
        $sReason  = $customReason = '';
        $reasonId = 0;
        
        if ( $last_act == 10 ) {
            // �������������� ����������� �� ���������
            $nActId = 9;
            $objResponse->assign( 'lr1', 'innerHTML', '�������������' );
        }
        else {
            if ( $edit ) {
                // �������������� ������� ����������
                $nActId   = 9;
                $aBlock   = admin_log::getProjBlock( $src_id );
                $sReason  = $aBlock['reason'];
                $reasonId = $aBlock['reason_id'];
                
                $objResponse->assign( 'lr1', 'innerHTML', '������������� ����������' );
            }
            else {
                // �������������� �������������� �� ���������
                $nActId = 10;
                $objResponse->assign( 'lr1', 'innerHTML', '��������������' );
            }
        }
        
        $customReason = $reasonId ? ''   : $sReason;
        $readonly     = $reasonId ? true : false;
        
        $sBanDiv = '<div id="bfrm_div_sel_0"><select><option>���������...</option></select></div>' 
            . '<textarea id="bfrm_0" name="bfrm_0" cols="" rows="">' . clearTextForJS( html_entity_decode($sReason, ENT_QUOTES, 'cp1251')) . '</textarea>';
        
        $objResponse->assign( 'prj_ban_div', 'innerHTML', $sBanDiv );
        $objResponse->script( "banned.buffer[0] = new Object();");
        $objResponse->script( "banned.buffer[0].customReason = new Array();");
        $objResponse->script( "banned.buffer[0].reasonId = new Array();");
        $objResponse->script( "banned.buffer[0].act_id = '$nActId';");
        $objResponse->script( "banned.buffer[0].objectId = '$obj_id';");
        $objResponse->script( "banned.buffer[0].srcId = '$src_id';" );
        $objResponse->script( "banned.buffer[0].customReason[$nActId] = '$customReason';" );
        $objResponse->script( "banned.buffer[0].reasonId[$nActId] = '$reasonId';" );
        $objResponse->script( "xajax_getAdminActionReasons( $nActId, '0', '$reasonId' );" );
        $objResponse->script( "$('ov-notice3').setStyle('display', '');" );
    }
    
    return $objResponse;
}

/**
 * ��������� ���������� ����������� ����������
 * 
 * @param  int $offer_id ID ����������� ����������
 * @param  int $act_id ID ������ �������� (admin_actions)
 * @param  int $src_id ID ��������� �������� (� ������ ������ ����� $obj_id ��� 0 - ������ ���������)
 * @param  string $reason �������
 * @param  int $reason_id ID �������, ���� ��� ������� �� ������ (������� admin_reasons, ��� act_id = 13)
 * @return object xajaxResponse
 */
function updateOfferBlock( $offer_id, $act_id, $src_id, $reason = '', $reason_id = null ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('projects') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer_offers.php' );
        
        $frl_offers = new freelancer_offers();
        $offer_id   = intval( $offer_id );
        $offer      = $frl_offers->getOfferById( $offer_id );
        
        if ( $offer ) {
            $objUser = new users();
            $objUser->GetUserByUID( $offer['user_id'] );
            
            $sObjName  = $offer['title'];
            $sObjLink  = ''; // ��� ������ �� ���������� �����������
            $reason_id = ( $reason_id ) ? $reason_id : 0;
    	    $reason    = str_replace( '%USERNAME%', $objUser->uname . ' ' . $objUser->usurname, $reason );
            $reason    = change_q( $reason, FALSE, 0, TRUE );
            
            if ( $act_id == 14 && $src_id ) { 
                // ������������ �����������
                $update = array( 'is_blocked' => 'f', 'reason'=> '', 'reason_id' => 0, 'admin' => 0 );
                $frl_offers->Update( $offer_id, $update );
                
                // ����� ��� ��������� ��������
                admin_log::addLog( admin_log::OBJ_CODE_OFFER, 14, $offer['user_id'], $offer_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason );
                
                // ��� ��� ��������� ����� �������� � ����
                $objResponse->script( 'window.location="/siteadmin/admin_log/?site=offer";' );
            }
            elseif ( $act_id == 13 && $src_id ) { 
                // ����������� ������� ���������� �����������
                admin_log::updateOfferBlock( $src_id, $reason, $reason_id );
                
                $objResponse->script( 'window.location.reload(true)' );
            }
            elseif ( $act_id == 13 && !$src_id ) { 
                // ��������� �����������
                $update = array( 'is_blocked' => 't', 'reason' => $reason, 'reason_id' => $reason_id, 'admin' => $_SESSION['uid'] );
                $frl_offers->Update( $offer_id, $update );
                
                // ����� ��� ��������� ��������
                admin_log::addLog( admin_log::OBJ_CODE_OFFER, 13, $offer['user_id'], $offer_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason, $offer_id );
                
                // ��� ��� ��������� ����� �������� � ����
                $objResponse->script( 'window.location="/siteadmin/admin_log/?site=offer";' );
            }
        }
        else {
            $objResponse->script( 'adminLogOverlayClose();' );
            $objResponse->alert('�������������� �����������');
        }
    }
    
    return $objResponse;
}

/**
 * ������������� ���� � ����� �������������� ���������� ����������� ����������
 * 
 * @param  int $obj_id ID �����������
 * @param  int $last_act ������� ��������� ����������� (ID �������� �� admin_actions)
 * @param  int $src_id ID ��������� �������� (� ������ ������ ����� $obj_id ��� 0 - ������ ���������)
 * @param  int $edit ���� �������������� ������� ����������
 * @return object xajaxResponse
 */
function setOfferBlockForm( $obj_id, $last_act, $src_id = 0, $edit = 0 ) {
    session_start();
    $objResponse  = new xajaxResponse();
    
    if ( hasPermissions('projects') ) {
        $sReason  = $customReason = '';
        $reasonId = 0;
        
        if ( $last_act == 14 ) {
            // �������������� ����������� �� ���������
            $nActId = 13;
            $objResponse->assign( 'lr1', 'innerHTML', '�������������' );
        }
        else {
            if ( $edit ) {
                // �������������� ������� ����������
                $nActId   = 13;
                $aBlock   = admin_log::getOfferBlock( $src_id );
                $sReason  = $aBlock['reason'];
                $reasonId = $aBlock['reason_id'];
                
                $objResponse->assign( 'lr1', 'innerHTML', '������������� ����������' );
            }
            else {
                // �������������� �������������� �� ���������
                $nActId = 14;
                $objResponse->assign( 'lr1', 'innerHTML', '��������������' );
            }
        }
        
        $customReason = $reasonId ? ''   : $sReason;
        $readonly     = $reasonId ? true : false;
        
        $sBanDiv = '<div id="bfrm_div_sel_0"><select><option>���������...</option></select></div>' 
            . '<textarea id="bfrm_0" name="bfrm_0" cols="" rows="">' . clearTextForJS( html_entity_decode($sReason, ENT_QUOTES, 'cp1251')) . '</textarea>';
        
        $objResponse->assign( 'offer_ban_div', 'innerHTML', $sBanDiv );
        $objResponse->script( "banned.buffer[0] = new Object();");
        $objResponse->script( "banned.buffer[0].customReason = new Array();");
        $objResponse->script( "banned.buffer[0].reasonId = new Array();");
        $objResponse->script( "banned.buffer[0].act_id = '$nActId';");
        $objResponse->script( "banned.buffer[0].objectId = '$obj_id';");
        $objResponse->script( "banned.buffer[0].srcId = '$src_id';" );
        $objResponse->script( "banned.buffer[0].customReason[$nActId] = '$customReason';" );
        $objResponse->script( "banned.buffer[0].reasonId[$nActId] = '$reasonId';" );
        $objResponse->script( "xajax_getAdminActionReasons( $nActId, '0', '$reasonId' );" );
        $objResponse->script( "$('ov-notice3').setStyle('display', '');" );
    }
    
    return $objResponse;
}

/**
 * ���������� ������ ��������� IP � ������� ������� ������������
 * 
 * @param  int $sUid UID ������������
 * @param  int $nCount �����������. ����������, 0 - �� ����������
 * @return object xajaxResponse
 */
function getLastIps( $sUid = '', $nCount = 10 ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $sTable = '<table id="t_last_ten" class="notice-table">';
        $user = new users();
        $user->GetUserByUID( $sUid );
        
        $objResponse->script( "adminLogOverlayClose();" );
        
        if ( $aRows = $user->getLastIps($sUid, $nCount) ) {
            $nCount = 1;
        	
        	foreach ( $aRows as $aOne ) {
        		$sTable .= '<tr>
                    <td class="cell-number">'. $nCount .'.</td>
                    <td><a href="https://www.nic.ru/whois/?query='. long2ip($aOne['ip']) .'" target="_blank">'. long2ip($aOne['ip']) .'</a></td>
                    <td class="cell-date">'. date('d.m.Y H:i:s', strtotime($aOne['date'])) .'</td>
                </tr>';
        		
        		$nCount++;
        	}
        }
        else {
            $sIp     = $user->GetField( $sUid, $error, 'last_ip' );
            $sTable .= '<tr>
                    <td class="cell-number">1.</td>
                    <td><a href="https://www.nic.ru/whois/?query='. $sIp .'" target="_blank">'. $sIp .'</a></td>
                    <td class="cell-date">'. date('d.m.Y H:i:s', strtotime($user->last_time)) .'</td>
                </tr>';
        }
        
        $sTable .= '</table>';
        
        $objResponse->assign( 'a_last_ten', 'href', '/users/' . $user->login );
        $objResponse->assign( 's_last_ten', 'innerHTML', $user->uname . ' ' . $user->usurname . ' [' . $user->login . ']' );
        $objResponse->assign( 'w_last_ten', 'innerHTML', 'IP' );
        $objResponse->assign( 'd_last_ten', 'innerHTML', $sTable );
        $objResponse->script( "$('ov-notice5').setStyle('display', '');" );
        $objResponse->script( "adjustLastTenHTML();" );
    }
    
    return $objResponse;
}

/**
 * ���������� ������ ��������� email ������� ������������ ������������
 * 
 * @param  int $sUid UID ������������
 * @param  int $nCount �����������. ����������, 0 - �� ����������
 * @return object xajaxResponse
 */
function getLastEmails(  $sUid = '', $nCount = 10 ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $sTable = '<table id="t_last_ten" class="notice-table"><tr><td>������������ �� ����� email</td></td></table>';
        $user = new users();
        $user->GetUserByUID( $sUid );
        
        $objResponse->script( "adminLogOverlayClose();" );
        
        $aRows = $user->getLastEmails($sUid, $nCount);
        if (!$aRows) {
            $aRows[] = array(
                'email' => $user->email,
                'date' => $user->reg_date
            );
        }

        $nCount = 1;
        $sTable = '<table id="t_last_ten" class="notice-table">';

        foreach ($aRows as $aOne) {
            $sTable .= '<tr>
                <td class="cell-number">'. $nCount .'.</td>
                <td>'. $aOne['email'] .'</td>
                <td class="cell-date">'. date('d.m.Y H:i:s', strtotime($aOne['date'])) .'</td>
            </tr>';

            $nCount++;
        }

        $sTable .= '</table>';
        
        $objResponse->assign( 'a_last_ten', 'href', '/users/' . $user->login );
        $objResponse->assign( 's_last_ten', 'innerHTML', $user->uname . ' ' . $user->usurname . ' [' . $user->login . ']' );
        $objResponse->assign( 'w_last_ten', 'innerHTML', 'email' );
        $objResponse->assign( 'd_last_ten', 'innerHTML', $sTable );
        $objResponse->script( "$('ov-notice5').setStyle('display', '');" );
        $objResponse->script( "adjustLastTenHTML();" );
    }
    
    return $objResponse;
}

/**
 * �������� ������� ������������.
 * 
 * @param  string $sUid UID ������������
 * @return object xajaxResponse
 */
function nullRating( $sUid = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('all') ) { // !!! ������ ������
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        $bRet = users::NullRating( $sUid, true );
        
        if ( $bRet ) {
            $objResponse->alert( '������� ������� �������' );
        }
        else {
            $objResponse->alert( '������ ��������� ��������' );
        }
    }
    
    return $objResponse;
}

/**
 * �������������/������� ���������� ����� ������������
 * 
 * @param  string $sUsers JSON ������ � �������� UID �������������
 * @param  string $sAction ��������: block - �������������, unblock - �������
 * @return object xajaxResponse
 */
function updateMoneyBlock(  $sUsers = '', $sAction = 'block' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('payments') ) {
        $aUsers = _jsonArray( $sUsers );
        
        if ( $aUsers ) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
            $bBlock  = ( $sAction == 'block' );
            $sTitle  = ( $bBlock ) ? '�������������� ������' : '������������� ������'; 
            $sAction = ( $bBlock ) ? 'unblock' : 'block';
            
        	foreach ($aUsers as $sUid) {
        		account::setBlockMoney( $sUid, $bBlock );
        		$objResponse->assign( "money_$sUid", 'innerHTML', '<a onclick="if (confirm(\'�� �������, ��� ������ '. mb_strtolower($sTitle).'?\')) xajax_updateMoneyBlock(JSON.encode(['.$sUid.']),\''.$sAction.'\')" href="javascript:void(0);">'.$sTitle.'</a>' );
        	}
        	
        	$objResponse->script( 'adminLogCheckUsers(false)' );
        	$objResponse->script( '$("chk_all").checked=false;' );
        }
    }
    
    return $objResponse;
}

/**
 * ���������� �������������
 * 
 * @param  string $sUsers JSON ������ � �������� UID �������������
 * @param  int $nReload 1 - ���� ����� ������������� ��������
 * @return object xajaxResponse
 */
function activateUser( $sUsers = '', $nReload = 0 ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        $aUsers = _jsonArray( $sUsers );
        
        if ( $aUsers ) {
            require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/users.php');
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/wizard_registration.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_employer.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_freelancer.php");
            
            foreach ($aUsers as $sUid) {
                if ( users::SetActiveByUid($sUid) ) {
                    $user = new users();
                    $user->GetUserByUID($sUid);
                    if($user->role[0] == 1) {
                        $wiz_user = wizard::isUserWizard($sUid, step_employer::STEP_REGISTRATION_CONFIRM, wizard_registration::REG_EMP_ID);
                    } else {
                        $wiz_user = wizard::isUserWizard($sUid, step_freelancer::STEP_REGISTRATION_CONFIRM, wizard_registration::REG_FRL_ID);
                    }
                    step_wizard::setStatusStepAdmin(step_wizard::STATUS_COMPLITED, $sUid, $wiz_user['id']);
                	$objResponse->script("$('activate_$sUid').set('html','');");
                }
            }
            
            $objResponse->script( 'adminLogCheckUsers(false)' );
        	$objResponse->script( '$("chk_all").checked=false;' );
        }
        
        if ( $nReload ) {
        	$objResponse->script( 'window.location.reload(true)' );
        }
    }
    
    return $objResponse;
}

/**
 * �������� ������ �������� �������� � ��������
 * 
 * @param  int $sUid UID ������������
 * @param  string $sPhone �������
 * @param  string $sPhoneOnly ���������� �������������� ������ ������ �� ������� - 't' ��� 'f'
 * @param  string $sSafetyMob ������� � ������� ������ �� ��� - 't' ��� 'f'
 * @return object xajaxResponse
 */
function updateSafetyPhone( $sUid = 0, $sPhone = '', $sPhoneOnly = 'f', $sSafetyMob = 'f' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php' );
        $sPhone = "+" . str_replace("+", "", $sPhone);
        $reqv  = sbr_meta::getUserReqvs($sUid);
        if($reqv[$reqv['form_type']]['mob_phone'] != $sPhone) {
            $nreqv['mob_phone'] = $sPhone;
            $cnt = 0;
            $filter = array(
                'search_phone_exact' => true,
                'search_phone'       => $nreqv['mob_phone']
            );
            sbr_meta::searchUsersPhone($cnt, $filter);
            if($cnt > 0) {
                $res = "������� {$sPhone} ��� ��������������� � �������.";
                $objResponse->assign( "safety_phone$sUid", 'value', $reqv[$reqv['form_type']]['mob_phone'] );
            } else {
            	sbr_meta::$reqv_fields[$reqv['form_type']]['mob_phone']['maxlength'] = 15;
                $error = sbr_meta::setUserReqv($sUid, $reqv['rez_type'], $reqv['form_type'], $nreqv);
            }
        }
        $res   = users::ChangeSafetyPhone( $sUid, $sPhone, $sPhoneOnly );
        $error = sbr_meta::safetyMobPhone($sUid, $sSafetyMob);
        if ( $res) {
            $objResponse->alert($res);
            $objResponse->script( "$('safety_phone_show$sUid').setStyle('display', '');" );
        } else {
            $sChecked = ( $sPhoneOnly == 't' ) ? 'true' : 'false';
            $sDisplay = ( $sPhoneOnly == 't' ) ? ''     : 'none';
            $sSafetyMobDisplay = ( $sSafetyMob == 't' ) ? '' : 'none';
            $objResponse->assign( "safety_phone_value$sUid", 'innerHTML', $sPhone );
            $objResponse->assign( "safety_phone_hidden$sUid", 'value', $sPhone );
            $objResponse->script( "$('safety_only_phone_show$sUid').setStyle('display', '$sDisplay');" );
            $objResponse->script( "$('is_safety_mob_show{$sUid}').setStyle('display', '$sSafetyMobDisplay');" );
            
            $sDisplay = ( trim($sPhone) ) ? '' : 'none';
            $objResponse->script( "$('safety_phone_show$sUid').setStyle('display', '$sDisplay');" );
        }
        
        $objResponse->script( "$('safety_phone_edit$sUid').setStyle('display', 'none');" );
    }
    
    return $objResponse;
}

/**
 * �������� ������ �������� �������� � IP
 * 
 * @param  int $sUid UID ������������
 * @param  string $sIp IP ����� �������, ����� ��� ���� �������� 10.10.10.1, 10.10.10.5 � 10.10.10.10 ��� 10.10.10.0/24
 * @return object xajaxResponse
 */
function updateSafetyIp( $sUid = 0, $sIp = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $res = users::ChangeSafetyIP( $sUid, $sIp );
        
        if ( $res['error_flag'] ) {
            $objResponse->alert($res['alert']);
            $objResponse->script( "$('safety_ip_show$sUid').setStyle('display', '');" );
        }
        else {
            $sDisplay = ( trim($sIp) ) ? '' : 'none';
            $objResponse->assign( "safety_ip_value$sUid", 'innerHTML', $sIp );
            $objResponse->script( "$('safety_ip_show$sUid').setStyle('display', '$sDisplay');" );
        }
        
        $objResponse->script( "$('safety_ip_edit$sUid').setStyle('display', 'none');" );
    }
        
    return $objResponse;
}

/**
 * �������� Email ������������
 * 
 * @param  int $sUid UID ������������
 * @param  string $sEmail ����� Email ������������
 * @return object xajaxResponse
 */
function updateEmail( $sUid = 0, $sEmail = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $res = users::ChangeMail( trim($sUid), trim($sEmail) );
        
        if ( $res ) {
            $objResponse->alert( $res );
        }
        else {
            $sDisplay = ( trim($sEmail) ) ? '' : 'none';
            $objResponse->assign( "email_value$sUid", 'innerHTML', $sEmail );
        }
        
        $objResponse->script( "$('email_show$sUid').setStyle('display', '');" );
        $objResponse->script( "$('email_edit$sUid').setStyle('display', 'none');" );
    }
    
    return $objResponse;
}

/**
 * �������� ��������� �������������
 * 
 * @param  int $sUid UID ������������
 * @param  int $nValue ����� �������� ��������� �������������
 * @return object xajaxResponse
 */
function updatePop( $sUid = 0, $nValue = 0 ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $mRes       = null;
        $oUser      = new users();
        $nValue     = intval( $nValue );
        $oUser->pop = $nValue;
        $sError     = $oUser->Update( $sUid, $mRes );
        
        if ( $sError ) {
            $objResponse->alert( $sError );
        }
        else {
            $sClass = $nValue < 0  ? 'b-voting__link_dot_red' : 'b-voting__link_dot_green';
            $sPop   = $nValue != 0 ? $nValue : '0';
            
            $objResponse->assign( "pop$sUid", 'innerHTML', $sPop );
            $objResponse->assign( "pop_input_$sUid", 'value', $sPop );
            $objResponse->script( "\$('pop$sUid').removeClass('b-voting__link_dot_red').removeClass('b-voting__link_dot_green').addClass('$sClass')" );
        }
        
        $objResponse->script( "$('pop_show$sUid').setStyle('display', '');" );
        $objResponse->script( "$('pop_edit$sUid').setStyle('display', 'none');" );
    }
    
    return $objResponse;
}

/**
 * ��������� ��� �����������
 * 
 * @param  int $uid UID ������������
 * @return object xajaxResponse
 */
function stopNotifications( $uid = 0, $role = 'flr' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        $sClass = $role == 'flr' ? 'freelancer' : 'employer';
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/' . $sClass . '.php' );
        
        $users = new $sClass();
        $users->subscr = str_repeat( '0', $GLOBALS['subscrsize'] );
        
        if ( $role == 'flr' ) {
            $users->mailer     = 0;
            $users->mailer_str = '';
        }
        
        $sError = $users->Update( $uid, $res );
        commune::clearSubscription($uid);
        
        if ( empty($sError) ) {
            $objResponse->alert( '����������� ���������' );
        }
        else {
            $objResponse->alert( '������ ���������� ������' );
        }
    }
    
    return $objResponse;
}

function saveExcDate($date, $type) {
    session_start();
    $objResponse = new xajaxResponse();
    if ( !hasPermissions('admin') ) {
        return $objResponse;
    }
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/LocalDateTime.php' );
    $year = substr($date, 0, 4);
    $odate = new LocalDateTime();
    $edate = $odate->getExcDaysInit($year, false, false);
    // ����� ���
    if(!$edate) {
        $edate['year'] = $year;
        switch($type) {
            case 1:
                $edate['holidays'] = $date;
                break;
            case 2:
                $edate['workdays'] = $date;
                break;
        }
        $odate->updateExcDays($edate, 'insert');
        return $objResponse;
    }
    $edit_date = $edate;
    
    $hdate = (strpos($edate['holidays'], $date) != 0 ? ",{$date}" : $date );
    $wdate = (strpos($edate['workdays'], $date) != 0 ? ",{$date}" : $date );
            
    switch($type) {
        case 0:
            $edit_date['holidays'] = str_replace($hdate, '', $edate['holidays']); // ������� ����
            $edit_date['workdays'] = str_replace($wdate, '', $edate['workdays']); // ������� ����
            break;
        case 1:
            $edit_date['workdays'] = str_replace($wdate, '', $edate['workdays']); // ������� ����
            $edit_date['holidays'] .= ($edate['holidays'] == '' ? '' : ',') . $date; 
            break;
        case 2:
            $edit_date['holidays'] = str_replace($hdate, '', $edate['holidays']); // ������� ����
            $edit_date['workdays'] .= ($edate['workdays'] == '' ? '' : ',') . $date; 
            break;
    }
    
    $edit_date['holidays'] = $edit_date['holidays'] != '' ? implode(",", $odate->initCollectionDate( $edit_date['holidays'])) : "";
    $edit_date['workdays'] = $edit_date['workdays'] != '' ? implode(",", $odate->initCollectionDate( $edit_date['workdays'])) : "";
    
    $is_changed = false;
    if($edate['holidays'] != $edit_date['holidays']) {
        $edate['holidays'] = $edit_date['holidays'];
        $is_changed = true;
    }
    
    if($edate['workdays'] != $edit_date['workdays']) {
        $edate['workdays'] = $edit_date['workdays'];
        $is_changed = true;
    }
    
    if($is_changed) {
        $odate->updateExcDays($edate);
    }
    return $objResponse;
}

function getLoadExcDate($year) {
    session_start();
    $objResponse = new xajaxResponse();
    if ( !hasPermissions('admin') ) {
        return $objResponse;
    }
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/LocalDateTime.php' );
    
    $odate = new LocalDateTime();
    $edate = $odate->getExcDaysInit($year, false, false);
    
    $resp['success'] = true;
    $resp['holidays'] = iconv("windows-1251", "UTF-8", $edate['holidays']);
    $resp['workdays'] = iconv("windows-1251", "UTF-8", $edate['workdays']);
    echo json_encode( $resp );
}

/**
 * �������� ��������� ������ ������� ������� ������.
 * 
 * @param  int $id ID ������� ������� ������.
 * @param  string $is_bold �������� ������ t/f
 * @return obj xajaxResponse
 */
function setReasonBold( $sId = 0, $sBold = 'f' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( !hasPermissions('adm') ) {
        return $objResponse;
    }
    
    admin_log::setReasonBold( $sId, $sBold == 't' ? $sBold : 'f' );
    $objResponse->script( "$('is_bold_$sId').set( 'disabled', false );" );
    return $objResponse;
}


/**
 * ���������/�������� ����������� �������������
 * 
 * @param integer $uid     �� ������������
 * @param boolean $type    �������/���������
 * @return \xajaxResponse
 */
function setVerification( $uid = 0, $type = false ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $users = new users();
        $users->is_verify = $type;
        
        $sError = $users->Update( $uid, $res );
        if ($type == false) {
        	require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/Verification.php' );
            Verification::decrementStat($uid);
        }
        
        if ( empty($sError) ) {
            $text = $type ? '����� �����������' : '���� �����������';
            $html = '<a href="javascript:void(0);" onclick="user_search.setVerification(' . $uid . ', ' . ( $type ? 'false' : 'true' ) . ');" class="lnk-dot-666" title="' . $text . '"><b>' . $text . '</b></a>';
            $objResponse->assign("verify{$uid}", 'innerHTML', $html);
            if($type) {
                $objResponse->script("$$('#user{$uid} a.user-name').grab(new Element('span', {class:'b-icon b-icon__ver b-icon_valign_middle'}), 'before')");
            } else {
                $objResponse->script("$$('#user{$uid} .b-icon__ver').dispose();");
            }
            $objResponse->alert( $type ? '����������� ����' : '����������� �����' );
        } else {
            $objResponse->alert( '������ ���������� ������' );
        }
    }
    
    return $objResponse;
}

$xajax->processRequest();
