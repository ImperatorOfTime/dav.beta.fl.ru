<?php

/**
 * ��������� ��������� ��� ������ � ������������
 */
interface AutoModeration {
    public static function actionByRate($rate, $scale);
    public static function getScale($name = 'comment');
}

/**
 * ����� ��� ������ � �������������� ���������
 * 
 */
class commune_carma implements AutoModeration 
{
    
    /**
     * ���������� ������� ��� ���� ����� ������� ����������� �����
     */
    const CARMA_COMMENT_BLUR = 5; 
    
    /**
     * ���������� ������� ��� ���� ����� ������ �����������
     */
    const CARMA_COMMENT_HIDE = 10;
    
    /**
     * ���������� ������� ��� ���� ����� ������� ���� �����
     */
    const CARMA_POST_BLUR = 5;
    
    /**
     * ���������� ������� ��� ���� ����� ������ ����
     */
    const CARMA_POST_HIDE = 20;
    
    /**
     * ���������� ��������������� ������ � ������������ ��� ���������� ������������ � ����������
     * ��� ������������ ���������� � ���������. ���� ���������� �������� ����������, ��. ������� � ������� commune_members
     */
    const COUNT_BLOCKED_POST = 10;
    
    /**
     * �������� �������� � ����������� �� ���������� ��������
     * 
     * @param string $name  ��� ��������
     * @return array
     */
    public static function getScale($name = 'comment') {
        $scale['comment'] = array(
            'hide' => self::CARMA_COMMENT_HIDE,
            'blur' => self::CARMA_COMMENT_BLUR
        );
        
        $scale['post'] = array(
            'banned' => self::CARMA_POST_HIDE,
            'blur'   => self::CARMA_POST_BLUR
        );
        
        return $scale[$name];
    }
    
    /**
     * ��������� ���� �� ����������� ���������� � ������������ � ����������� �� ���� �����������
     * 
     * @param integer $uid �� ������������
     */
    public static function isAllowedVote($uid = false) {
        static $is_accept_vote;
        
        if(isset($is_accept_vote[$uid])) {
            return $is_accept_vote[$uid];
        }
        
        if ($uid == false) $uid = get_uid(false);
        if (!$uid)         return false;
        
        if($uid == $_SESSION['uid']) {
            $is_accept_vote[$uid] = (strtotime("{$_SESSION['reg_date']} + 1 month") < time());
            return $is_accept_vote[$uid];
        } else {
            require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/users.php";
            $user = new users();
            $user->GetUserByUID($uid);
            $is_accept_vote[$uid] = (strtotime("{$user->reg_date} + 1 month") < time());
            return $is_accept_vote[$uid];
        }
    }
    
    /**
     * �������� �������� ������� ���������� ���������� � ��������
     * 
     * @param integer $rate �������
     * @param array  $scale �������� �������� �� ������� �������� ��� ����� ������
     * @return string �������� ��������
     */
    public static function actionByRate($rate, $scale) {
        foreach($scale as $action => $R) {
            if($rate <= $R * -1) {
                return $action;
                break;
            }
        } 
    }
    
    /**
     * ���������������� ��������� � �������, ������ ������ �������������
     * 
     * @staticvar array $is_immunity
     * @param integer $uid �� ������������
     * @param array   $data ������ ������������ ���� �������
     * @param integer $msg_id ID ���������
     * @return boolean
     */
    public static function isImmunity($uid, $data = array(), $msg_id = null) {
        static $is_immunity;
        
        if(isset($is_immunity[$uid])) {
            return $is_immunity[$uid];
        }

        $commune_id = commune::getCommuneIDByMessageID($msg_id);
        $status = commune::GetUserCommuneRel($commune_id, $uid);

        if(empty($data)) {
            require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/users.php";
            $user = new users();
            $user->GetUserByUID($uid);
            $is_immunity[$uid] = ( $user->is_team == 't' || strtolower($user->login) == 'admin' || $status['is_moderator']==1 || $status['is_admin']==1 || $status['is_author']==1 );
        } else {
            $is_immunity[$uid] = ( $data['is_team'] == 't' || strtolower($data['login']) == 'admin' || $status['is_moderator']==1 || $status['is_admin']==1 || $status['is_author']==1 );
        }

        return $is_immunity[$uid];
    }
}

?>