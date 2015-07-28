<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_dialogue.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceMsgModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');

/**
 * ����� ��� ��������� ������������ ��������� ��� �������� 
 */
class notifications
{
    
    
    
    
    /**
     * ��������� ��������� �� ���������� ����������
     * ��� ����������� � ������������ ������ "������� � ������"
     * ��������� � ������ �������.
     * 
     * @return array
     */
    static public function getFrlGroupTip()
    {
        //�� ��������� ��������� �� ������ �������� ��� �������
        $default = array(
            'count' => 0,
            'tip' => '������ �������� � �������',
            'link' => (@$_SESSION['po_count'])?'/proj/?p=list':'/tu-orders/'
        );
        
        //����
        $projectsTip = self::getProjectsTipFrl();
        //@todo: ����� ���������� ����� ����
        if(isset($projectsTip['count']) && $projectsTip['count'] > 0)
        {
            $default = $projectsTip;
        }
        
        //���� �� ������� �� ������� ��
        $tservicesOrdersTip = self::getTServicesOrdersTip();
        if(isset($tservicesOrdersTip['count']) && $tservicesOrdersTip['count'] > 0)
        {
            $default['tip'] = ($default['count'] > 0)?$default['tip'] . PHP_EOL . $tservicesOrdersTip['tip']:$tservicesOrdersTip['tip'];
            $default['count'] += $tservicesOrdersTip['count'];
            $default['link'] = $tservicesOrdersTip['link'];
        }
        
        return $default;
    }

    
    static public function getEmpGroupTip()
    {
        //�� ��������� ��������� �� ������ ��������
        $default = array(
            'count' => 0,
            'tip' => '������ �������� � �������',
            'link' => "/users/{$_SESSION['login']}/setup/projects/"
        );
        
        //����
        $projectsTip = self::getProjectsTipEmp();
        //@todo: ����� ���������� ����� ����
        if(isset($projectsTip['count']) && $projectsTip['count'] > 0)
        {
            $default = $projectsTip;
        }
        
        //���� �� ������� �� ������� ��
        $tservicesOrdersTip = self::getTServicesOrdersTip();
        if(isset($tservicesOrdersTip['count']) && $tservicesOrdersTip['count'] > 0)
        {
            $default['tip'] = ($default['count'] > 0)?$tservicesOrdersTip['tip'].PHP_EOL.$default['tip']:$tservicesOrdersTip['tip'];
            $default['count'] += $tservicesOrdersTip['count'];
            $default['link'] = $tservicesOrdersTip['link'];
        }
        
        return $default;
    }    
    
    
    
    
    
    
    

    /**
     * ������� � ������ ��
     * 
     * @return array
     */
    static public function getTServicesOrdersTip()
    {
        $uid = get_uid(FALSE); 
        $is_emp = is_emp();
        
        $tips = array(
            "����� ��������� � ������",
            "� ������� %d %s",
            "����� ������� � ������",
            "� ������� %d %s � %d %s"
        );
        
        $msg_ending = array("����� ���������", "����� ���������", "����� ���������");
        $event_ending = array("����� �������", "����� �������", "����� �������");
        
        $tip = '��� ������';
        $link = '';
        
        //@todo: ����� ������������ ������ ��� ���� 
        //�� ���� ���������� �� ��� ����� ���������. ����� ����������!
        $tserviceMsgModel = TServiceMsgModel::model();
        $newTserviceMsgCount = $tserviceMsgModel->countNew($uid);
        
        
        $tserviceOrderModel = TServiceOrderModel::model();
        $newTserviceOrderEventCount = $tserviceOrderModel->getCountEvents($uid, $is_emp);
        
        $total = $newTserviceMsgCount + $newTserviceOrderEventCount;
        
        $code = ($newTserviceMsgCount > 0)?1:0;
        $code .= ($newTserviceOrderEventCount > 0)?1:0;
        
        switch($code)
        {
            case '10':
                $tip = ($newTserviceMsgCount == 1)?sprintf($tips[0]):
                sprintf($tips[1], $newTserviceMsgCount, ending($newTserviceMsgCount, $msg_ending[0], $msg_ending[1], $msg_ending[2])); 
                break;
            
            case '01':
                $tip = ($newTserviceOrderEventCount == 1)?sprintf($tips[2]):
                sprintf($tips[1], $newTserviceOrderEventCount, ending($newTserviceOrderEventCount, $event_ending[0], $event_ending[1], $event_ending[2]));
                $link = $tserviceOrderModel->getLastEventOrderURL($uid, $is_emp);
                break;
            
            case '11':
                $tip = sprintf($tips[3], 
                        $newTserviceMsgCount, ending($newTserviceMsgCount, $msg_ending[0], $msg_ending[1], $msg_ending[2]), 
                        $newTserviceOrderEventCount, ending($newTserviceOrderEventCount, $event_ending[0], $event_ending[1], $event_ending[2]));
                break;
        }

        return array(
            'count' => $total,
            'tip' => $tip,
            'link' => (!empty($link))?$link:($is_emp ? "/users/" . $_SESSION['login'] : '') . "/tu-orders/"
        );
    }
    
    

    
    /**
     * ���������� ��������� ��� ������ "�������" ��� ������������
     * � ���� ������� ((int)count, (string)tip)
     * null - � ������ ������
     */
    static public function getProjectsTipEmp ()
    {
        $uid = get_uid(0);
        if (!$uid) {
            return null;
        }
        $newMessCount = $newOffersCount = $newPrjEvents = 0;

        // ���������� ������� � ��������� � ��������
        $complexCount = projects_offers_dialogue::CountMessagesForEmp($uid, true, true);
        
        $newOffersCount = (int)$complexCount['offers'];
        $newMessCount = (int)$complexCount['messages'];
        
        // ����� �������
        $newPrjEvents = 0; //projects_offers::CountNewPrjEventsForEmp($_SESSION['uid']); #0020922

        if (($newOffersCount + $newMessCount) == 1) {
            $last_emp_new_messages_pid = projects_offers_dialogue::FindLastMessageProjectForEmp($uid);
            $lastPrjLink = "/projects/" . $last_emp_new_messages_pid;
        } else {
            $lastPrjLink = "/users/" . $_SESSION['login'] . "/projects/";
        }
        $_SESSION['lst_emp_new_messages']['cnt'] = $complexCount['all'];
        
        if ($newMessCount === null || $newPrjEvents === null) {
            return array(
                'count'     => 0,
                'tip'       => '������ ��������',
                'link'  => "/users/" . $_SESSION['login'] . "/projects/"
            );
        }
        
        //$news = $newMessCount + $newPrjEvents;
        
        /*if ((int)$newMessCount === 0 && (int)$newPrjEvents === 1) {
            $tip = '����� ������� � ����� �������';
        } elseif ((int)$newMessCount === 1 && (int)$newPrjEvents === 0) {
            $tip = "����� ����� �� ��� ������";
        } else*/if (($newOffersCount + $newMessCount + $newPrjEvents) > 0) {
            $tip = "� ����� �������� ";
            $tip .= $newOffersCount > 0 ? $newOffersCount . ending($newOffersCount, " ����� �����", " ����� ������", " ����� �������") : "";
            $tip .= ($newOffersCount > 0 && $newMessCount > 0) ? " � " : "";
            $tip .= $newMessCount > 0 ? $newMessCount . ending($newMessCount, " ����� ���������", " ����� ���������", " ����� ���������") : "";
            
            $tip .= $newPrjEvents > 0 ? $newPrjEvents . ending($newPrjEvents, " ����� �������", " ����� �������", " ����� �������") : "";
        }
        return array(
            'count'     => $newOffersCount + $newMessCount + $newPrjEvents,
            'tip'       => $tip,
            'link'      => $lastPrjLink
        );
    }
    
    /**
     * ���������� ��������� ��� ������ "�������" ��� ����������
     * � ���� ������� ((int)count, (string)tip)
     * null - � ������ ������
     */
    static public function getProjectsTipFrl ()
    {
        $uid = get_uid(0);
        if (!$uid) {
            return null;
        }
        $newEventsCount = $newMessCount = 0;
        // ���������� ����� �������
        $newEventsCount = projects_offers::GetNewFrlEventsCount($uid, false);
        // ������� ����� ���������
        $newMessCount = projects_offers_dialogue::CountMessagesForFrl($uid, true, false);
        
        if ($newEventsCount === null || $newMessCount === null) {
            return array(
                'count' => 0,
                'tip'   => '������ ��������',
                'link' => '/proj/?p=list'
            );
        }
        
        
        
        $newAnsCount = $newEventsCount + $newMessCount;
        
        if ((int)$newMessCount === 0 && (int)$newEventsCount === 1) {
            $tip = '����� ������� � ������ ������ � �������';
        } elseif ((int)$newMessCount === 1 && (int)$newEventsCount === 0) {
            $tip = "����� ��������� � ������ ������ � �������";
        } elseif (($newMessCount + $newEventsCount) > 0) {
            $tip = "";
            $tip .= $newMessCount > 0 ? $newMessCount . ending($newMessCount, " ����� ���������", " ����� ���������", " ����� ���������") : "";
            $tip .= ($newMessCount > 0 && $newEventsCount > 0) ? " � " : "";
            $tip .= $newEventsCount > 0 ? $newEventsCount . ending($newEventsCount, " ����� �������", " ����� �������", " ����� �������") : "";
            $tip .= $newEventsCount > 0 ? " � ����� ������� � ��������" : " �� ���� ������ � ��������";
        }
        
        return array(
            'count' => $newAnsCount,
            'tip'   => $tip,
            'link' => '/proj/?p=list'
        );
    }
    
    
    /**
     * ���������� ��������� ��� ������ "���������"
     * � ���� ������� ((int)count, (string)tip)
     * null - � ������ ������
     * 
     * @param boolean $ajax ����� ������� ajax'��
     */
    static public function getMessTip ($ajax = false)
    {
        $mem = new memBuff();
        
        $uid = get_uid(0);
        if (!$uid) {
            return null;
        }
        
        if ($ajax) {
            $newMessCount = messages::GetNewMsgCount($uid, true);
        } else {
            $newMessCount = $_SESSION['newmsgs'];
        }
        if ($newMessCount === null) {
            return null;
        } elseif ((int)$newMessCount === 0) {
            $tip = '��� ��������� � ���������';
        } elseif ((int)$newMessCount === 1) {
            /*$mess = new messages;
            if ( empty($_SESSION['newMsgSender']) ) {
                $user = $mess->GetLastMessageContact($uid);
                $_SESSION['newMsgSender'] = $user['uname'] . ' ' . $user['usurname'] . ' [' . $user['login'] . ']';
            }
            $tip = '����� ��������� �� ������������ ' . $_SESSION['newMsgSender'];*/
            $newMsgSender = $mem->get("msgsNewSender{$uid}");
            if ($newMsgSender === false || trim($newMsgSender) == '[]') {
                $mess = new messages;
                $sender = $mess->GetLastMessageContact($uid);
                if(trim($sender['login']) != '') {
                    $newMsgSender = $sender['uname'] . ' ' . $sender['usurname'] . ' [' . $sender['login'] . ']';
                    $mem->set("msgsNewSender{$uid}", $newMsgSender, 3600, 'msgsNewSenderID' . $sender['uid']);
                }
            }
            $tip = '����� ��������� �� ������������ ' . $newMsgSender;
        } else {
            $tip = $newMessCount . ' ' . ending($newMessCount, '������������� ���������', '������������� ���������', '������������� ���������');
        }
        
        return array(
            'count' => $newMessCount,
            'tip'   => $tip
        );
    }
    
    /**
     * ���������� ��������� ��� ������ "���������"
     * � ���� ������� ((int)count, (string)tip)
     * null - � ������ ������
     * 
     * @param  string  $interface ������� ������ ���������� ����� (������ ���. ��� ����� ���) @todo ������ ����� ���������� ������ ���
     * @param boolean $ajax ����� ������� ajax'��
     */
    static public function getSbrTip ($interface = 'new')
    {
        $uid = get_uid(0);
        if (!$uid) {
            return null;
        }
        $name_session = $interface == 'old' ? 'sbr_tip_old' : 'sbr_tip';
        $eventCount = sbr_meta::getNewEventCount($uid, true, $interface);
        //$messCount = sbr_meta::getNewMsgCount($uid, true);
        if ($eventCount === null) {
            if(isset($_SESSION[$name_session])) {
                $tip = $_SESSION[$name_session];
                unset($_SESSION[$name_session]);
                return $tip;
            }
            return null;
        }
        
        $totalCount = $eventCount; // + $messCount;
        if ((int)$totalCount === 0) {
            if(isset($_SESSION[$name_session])) { // ��� ���� ����� ������� ���� �� ���� ��� ���� ������������ ��������� ����� �� �������� ��� � ��������� ��������
                $tip = $_SESSION[$name_session];
                unset($_SESSION[$name_session]);
                return $tip;
            }
            $tip = '������ ���������� ������';
            $alert = false;
        } elseif ((int)$totalCount === 1) {
            $tip = '����� ������� � ����������� ������';
            $alert = true;
        } else {
            $tip = $totalCount . ' ' . ending($totalCount, '����� �������', '����� �������', '����� �������') . ' � ����� ����������� ��������';
            $alert = false;
        }
        
        return array(
            'count' => $totalCount,
            'tip'   => $tip,
            'alert' => $alert
        );
    }
    
    
    
    /**
     * ���������� ������� �� ������ � ����� ���������� ������
     * 
     * @return string
     */
    static public function getAllSbrTip()
    {
        $default = array(
            'count' => 0,
            'tip'   => '������ ���������� ������',
            'alert' => FALSE
        );
        
        $sbrTip = notifications::getSbrTip();
        if(isset($sbrTip['count']) && $sbrTip['count'] > 0)
            $default['count'] = $sbrTip['count'];
        
        
        $sbrOldTip = notifications::getSbrTip('old');
        if(isset($sbrOldTip['count']) && $sbrOldTip['count'] > 0)
            $default['count'] += $sbrOldTip['count'];
        
        if ((int)$default['count'] === 1) 
        {
            $default['tip'] = '����� ������� � ����������� ������';
            $default['alert'] = TRUE;
        } 
        elseif($default['count'] > 1) 
        {
            $default['tip'] = $default['count'] . ' ' . ending($default['count'], '����� �������', '����� �������', '����� �������') . ' � ����� ����������� ��������';
        }
        
        return $default;
    }
    
    
    
}

?>