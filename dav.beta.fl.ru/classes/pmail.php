<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/smtp.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';

/**
 * ����� ��� �������� �����. �������������� PgQ
 *
 * ����� ��������� ���������� ������������� ���������� /classes/pgq/mail_cons.php � /classes/pgq/plproxy-mail.php �� ������� 
 * ���� ��� �����������, �� �������� ������.
 * @see PGQMailSimpleConsumer::finish_batch()
 */
class pmail extends SMTP {
    
    
    /**
     * ����� ������� ��� �������� ��������� �� �������� nsync
     * 
     * @param  integer $msgid       id ������� ���������
     * @param  integer $spamid      id �������� ��� NULL, ���� �������� ����� �������
     * @param  array   $recipients  ������ � uid ������������� ��� ��������
     * @param  string  $subject     ���� ������
     * @param  string  $message     ��������� (��� NULL ����� ������������ ��������� �� ������� messages)
     * @param  boolean $useVars     ������������� ����������
     * @return integer              id ���������, 0 - ������
     */
    protected function _nsyncMasssend( $msgid, $spamid, &$recipients, $subject, $message=NULL, $useVars=TRUE ) {
        $DB = new DB('master');
        $messages = new messages;
        if ( empty($spamid) && empty($recipients) ) {
            if ( is_null($message) ) {
                if ( !($message = $messages->GetMessage($msgid)) ) {
                    return 0;
                }
                $message = trim($message['msg_text']);
            } 
            $message = str_replace("\n", "\r\n", $message); // ��� ����� ���������� � ��������� � smtp::SendSmtp
            $text = reformat($message, 100, 0, -1);
            $this->subject   = $subject;
            $this->message   = $this->GetHtml('', $text, array('header'=>'none', 'footer'=>'none'));
            $this->recipient = '';
            return $this->send('text/html',  array());
        } else {
            if ( empty($recipients) ) {
                return 0;
            }
            $this->recipient = array();
            $res = $DB->query("SELECT * FROM users WHERE uid IN (?l)", $recipients);
            if ( $useVars ) {
                while ( $row = pg_fetch_assoc($res) ) {
                    $this->recipient[] = array(
                        'email' => "{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>",
                        'extra' => array(
                            'USER_NAME'    => $row['uname'],
                            'USER_SURNAME' => $row['usurname'],
                            'USER_LOGIN'   => $row['login']
                        )
                    );
                }
            } else {
                while ( $row = pg_fetch_assoc($res) ) {
                    $this->recipient[] = "{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>";
                }
            }
            return $this->bind($spamid);
        }
    }
    

	/**
     * �������� �� �������������� /siteadmin/admin
     * 
     * @param  integer $msgid       id ������� ���������
     * @param  integer $spamid      id �������� ��� NULL, ���� �������� ����� �������
     * @param  array   $recipients  ������ � uid ������������� ��� ��������
     * @return integer              0 -> ������
     */
    public function SpamFromAdmin($msgid, $spamid, $recipients) {
        $DB = new DB('master');
        $messages = new messages;
        if ( empty($spamid) && empty($recipients) ) {
            if ( !($message = $messages->GetMessage($msgid)) ) {
                return 0;
            }
            $text = reformat2($message['msg_text'], 100);
            $this->subject   = "����� ��������� �� ������� FL.ru";
            $this->message   = $this->GetHtml('', $text, array('header'=>'none', 'footer'=>'none'));
            $this->recipient = '';
            return $this->send('text/html', ($message['files'] == '{}'? array(): $DB->array_to_php($message['files'])));
        } else {
            if ( empty($recipients) ) {
                return 0;
            }
            $this->recipient = array();
            $res = $DB->query("SELECT * FROM users WHERE uid IN (?l)", $recipients);
            while ( $row = pg_fetch_assoc($res) ) {
                $this->recipient[] = array(
                    'email' => "{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>",
                    'extra' => array(
                        'USER_NAME'    => $row['uname'],
                        'USER_SURNAME' => $row['usurname'],
                        'USER_LOGIN'   => $row['login']
                    )
                );
            }
            return $this->bind($spamid);
        }
	}
    
    
	public function SpamFromMasssending($msgid, $spamid, $recipients) {
        $DB = new DB('master');
        $messages = new messages;
        if ( empty($spamid) && empty($recipients) ) {
            if ( !($message = $messages->GetMessage($msgid)) ) {
                return 0;
            }
            // �������� ������������� (����������)
            $this->recipient = '';
            $this->subject   = "����� ��������� �� FL.ru";
            $msg_text = "
<a href='{$GLOBALS['host']}/users/{$message['from_login']}{$this->_addUrlParams('b')}'>{$message['from_uname']} {$message['from_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$message['from_login']}{$this->_addUrlParams('b')}'>{$message['from_login']}</a>]
�������(�) ��� ����� ��������� �� ����� FL.ru.<br />
<br />
---------- 
<br />
".$this->ToHtml(LenghtFormatEx(strip_tags($message['msg_text']), 300))."
<br />
<br />
<br />
<a href='{$GLOBALS['host']}/contacts/?from={$message['from_login']}{$this->_addUrlParams('b', '&')}'>{$GLOBALS['host']}/contacts/?from={$message['from_login']}</a>
<br />
<br />
------------
";
            $this->message = $this->GetHtml('%USER_NAME%', $msg_text, array('header'=>'default', 'footer'=>'simple'));
            return $this->send('text/html', ($message['files'] == '{}'? array(): $DB->array_to_php($message['files'])));
        } else {
            if ( empty($recipients) ) {
                return 0;
            }
            $this->recipient = array();
            $res = $DB->query("SELECT u.*, usk.key AS unsubscribe_key FROM users AS u LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid WHERE u.uid IN (?l)", $recipients);
            while ( $row = pg_fetch_assoc($res) ) {
            	if (!$row['unsubscribe_key']) {
            		$row['unsubscribe_key'] = users::writeUnsubscribeKey($row["uid"]);
            	}
                $this->recipient[] = array(
                    'email' => "{$row['uname']} {$row['usurname']} [{$row['login']}] <{$row['email']}>",
                    'extra' => array(
                        'USER_NAME'       => $row['uname'],
                        'USER_SURNAME'    => $row['usurname'],
                        'USER_LOGIN'      => $row['login'],
                        'UNSUBSCRIBE_KEY' => $row['unsubscribe_key']
                    )
                );
            }
            return $this->bind($spamid);
        }

	}
    
    
    /**
     * ���������� ����������� � ����� ���������� � ����� ("��� ��������").
	 * ��������� plproxy-mail
     * 
     * @param   array      $params    ������ �� PgQ, TO-����� ����������; FROM-����� �����������
     * @param   string     $msg       ����� ���������
     *
     * @return  integer    ���������� ������������ �����������.
     */
	function NewMessage($from_uid, $to_uid, $msg) {

		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';

		$to = new users;
		$to->GetUserByUID($to_uid);

		if (substr($to->subscr, 0, 1) != '1' || !$to->email || $to->is_banned == '1') {
			return 0;
		}

		$from = new users;
		$from->GetUserByUID($from_uid);
                $msg = preg_replace("/\/\{\W+\}\//", "//", $msg); // ������� ����� ������ ������� ���� � ���������
		$this->message = $this->GetHtml($to->uname, "
<a href='{$GLOBALS['host']}/users/{$from->login}{$this->_addUrlParams('b')}'>{$from->uname} {$from->usurname}</a> [<a href='{$GLOBALS['host']}/users/{$from->login}{$this->_addUrlParams('b')}'>{$from->login}</a>]
�������(�) ��� ����� ��������� �� ����� FL.ru.<br />
<br />
---------- 
<br />
".$this->ToHtml(LenghtFormatEx(strip_tags($msg), 300))."
<br />
<br />
<br />
<a href='{$GLOBALS['host']}/contacts/?from={$from->login}{$this->_addUrlParams('b', '&')}'>{$GLOBALS['host']}/contacts/?from={$from->login}</a>
<br />
------------
", array('header' => 'default', 'footer' => 'default'), array('login'=>$to->login));
	
		$this->recipient = "{$to->uname} {$to->usurname} [{$to->login}] <{$to->email}>";
		$this->subject = "����� ��������� �� FL.ru";
		$this->send('text/html');
		
		return $this->sended;
	
	}

        
    /**
     * ���������� ����������� � ����� ���������� � ������� ������� �����.
     * ��������� plproxy-mail
     * 
     * @param   array      $params    ������ �� PgQ, TO-����� ����������; FROM-����� �����������
     * @param   string     $order     �����
     * @param   string     $msg       ����� ���������
     *
     * @return  integer    ���������� ������������ �����������.
     */
    function NewTserviceMessage($from_uid, $to_uid, $order, $msg) {

        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php';

        $to = new users;
        $to->GetUserByUID($to_uid);

        if (substr($to->subscr, 0, 1) != '1' || !$to->email || $to->is_banned == '1') {
            return 0;
        }

        $from = new users;
        $from->GetUserByUID($from_uid);
        $msg = preg_replace("/\/\{\W+\}\//", "//", $msg); // ������� ����� ������ ������� ���� � ���������
        $role = in_array($from_uid, array($order['frl_id'], $order['emp_id'])) 
                ? (is_emp() ? '��������' : '�����������')
                : '������';
        $this->message = $this->GetHtml($to->uname, "
            {$role} {$from->uname} {$from->usurname} [{$from->login}] ������� ��� ����� ��������� � ������ <br />
            �<a href='{$GLOBALS['host']}/tu/order/{$order['id']}/'>{$order['title']}</a>�:<br /><br />
            <em>" . $this->ToHtml(LenghtFormatEx(strip_tags($msg), 300)) . "</em><br /><br />"
            . "<a href='{$GLOBALS['host']}/tu/order/{$order['id']}/#messages'>������� � ���������</a> /
                <a href='{$GLOBALS['host']}/tu/order/{$order['id']}/#messages'>�������� �� ����</a>

", array('header' => 'default', 'footer' => 'default'), array('login' => $to->login));

        $this->recipient = "{$to->uname} {$to->usurname} [{$to->login}] <{$to->email}>";
        $this->subject = "����� ��������� � ������ �� FL.ru";
        $this->send('text/html');

        return $this->sended;
    }
    

    /**
     * ���������� ����������� � ����� ������������ � ����������.
     * 
     * @param   string|array   $message_ids  �������������� ������������.
     * @param   resource       $connect      ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return  integer                      ���������� ������������ �����������.
     */
    function CommuneNewComment($message_ids, $connect = NULL)
    {
        require_once($_SERVER['DOCUMENT_ROOT'].'/classes/commune.php');
        $commune = new commune();
        if(!($comments = $commune->GetComments4Sending($message_ids, $connect)))
            return NULL;

        $top_ids = array();
        foreach($comments as $cm) {
            $top_ids[] = $cm['top_id'];
        }

        $subscribers = array();
        if(count($top_ids)) {
            $top_ids = array_unique($top_ids);
            $subscr = $commune->getThemeSubscribers($top_ids);
            
            foreach($subscr as $row) {
                $subscribers[$row['message_id']][] = $row;
            }
        }

        foreach($comments as $comment) {
            $this->subject = '����� ����������� � ������ �'.$comment['top_title'].'� ���������� �'.$comment['commune_name'].'�';
            $userlink = $GLOBALS["host"]."/users/".$comment['login'];
            $friendly_url_topic = getFriendlyURL('commune', $comment["top_id"]); 
            $body_start = "
<a href=\"{$userlink}\">{$comment['uname']}</a> <a href=\"{$userlink}\">{$comment['usurname']}</a> [<a href=\"{$userlink}\">{$comment['login']}</a>] �������(�) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">�����������</a> � ������ ".($comment["parent_id"] != $comment["top_id"] ? "���������/�����������" : "�����" )." � ������ �<a href=\"{$GLOBALS['host']}{$friendly_url_topic}?{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['top_title']}</a>� ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['commune_name']}</a>�.
<br/><br/>
--------
";

            $body_subscr =
"<a href=\"{$userlink}\">{$comment['uname']}</a> <a href=\"{$userlink}\">{$comment['usurname']}</a> [<a href=\"{$userlink}\">{$comment['login']}</a>] �������(�) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">�����������</a> � ".($comment["parent_id"] != $comment["top_id"] ? "���������/�����������" : "�����" ).".
<br/><br/>
--------
";

            $body = 
"<br/>".reformat2($comment['title'],100)."
<br/>".reformat2($comment['msgtext'],100,0,1)."
<br/><br/>
--------
";
$p_body = 
reformat2($comment['title'],100)."
".str_replace(array("\r", "\n", "<br>", "<br/>"), array("__NEWLINE__", "__NEWLINE__", "__NEWLINE__", "__NEWLINE__" ), $comment['msgtext'])."
--------";

            $p_body = str_replace("__NEWLINE__", "\n", $p_body);
            $p_body = str_replace("<br/>", "\n", "\n--------\n".$p_body);
            $skip_users = array();
            $skip_users[] = $comment['user_id'];
            $link_commune = "<a href='{$GLOBALS['host']}/commune/?id={$comment['commune_id']}' target='_blank'>{$comment['commune_name']}</a>";
            $link_topic = "<a href='{$GLOBALS['host']}{$friendly_url_topic}' target='_blank'>{$comment['top_title']}</a>";
                    
            if($comment['p_user_id'] != $comment['user_id']
                 && $comment['p_email']
                 && substr($comment['p_subscr'],5,1)=='1'
                 && $comment['p_banned'] == '0')
            {
                // ���������� ��������.
                $this->recipient = $comment['p_uname']." ".$comment['p_usurname']." [".$comment['p_login']."] <".$comment['p_email'].">";
                $this->message = $this->GetHtml($comment['p_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['p_login']));
                $this->SmtpMail('text/html');
                $skip_users[] = $comment['p_user_id'];
                require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
                $msg = "������������, {$comment['p_uname']}.
<a href=\"{$userlink}\">{$comment['uname']}</a> <a href=\"{$userlink}\">{$comment['usurname']}</a> [<a href=\"{$userlink}\">{$comment['login']}</a>] �������(�) ���������� � ������ ".($comment["parent_id"] == $comment["top_id"] ? "�����" : "����������� � �����")." {$link_topic} � ���������� {$link_commune}. $p_body
��� ��������� ���� ���������� ������������� � �� ������� ������.
������� FL.ru.";
                //messages::Add( users::GetUid($err, 'admin'), $comment['p_login'], $msg, '', 1 );
            }

            if($comment['t_user_id']!=$comment['user_id']
                 && $comment['t_user_id']!=$comment['p_user_id']
                 && $comment['t_email']
                 && substr($comment['t_subscr'],5,1)=='1'
                 && $comment['t_banned'] == '0')
            {
                // ���������� ������ ������.
                $this->recipient = $comment['t_uname']." ".$comment['t_usurname']." [".$comment['t_login']."] <".$comment['t_email'].">";
                $this->message = $this->GetHtml($comment['t_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['t_login']));
                $this->SmtpMail('text/html');
                $skip_users[] = $comment['t_user_id'];
                require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
                $msg = "������������, {$comment['t_uname']}.
<a href=\"{$userlink}\">{$comment['uname']}</a> <a href=\"{$userlink}\">{$comment['usurname']}</a> [<a href=\"{$userlink}\">{$comment['login']}</a>] ������� ���������� ".($comment["parent_id"] == $comment["top_id"] ? "� ������ �����" : "� ����� ������")." {$link_topic} � ���������� {$link_commune}. $p_body
��� ��������� ���� ���������� ������������� � �� ������� ������.
������� FL.ru.";
                //messages::Add( users::GetUid($err, 'admin'), $comment['t_login'], $msg, '', 1 );
            }

            if(isset($subscribers[$comment['top_id']])) {
                // �������� ���� ����������� ������
                foreach($subscribers[$comment['top_id']] as $user) {
                    // ����� �������� � ������
                    if(in_array($user['user_id'], $skip_users)) continue;
                    $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
                    $this->message = $this->GetHtml($user['uname'], 
                        $body_subscr . $body,
                        array('header' => 'subscribe', 'footer' => 'subscribe'),
                        array('type' => 0, 'title' => $link_commune, 'topic_title' => $link_topic, 'login' => $user['login'], 'is_comment' => $user['parent_id']));
                    $this->SmtpMail('text/html');
                    $msg = "������������, {$user['uname']}.
<a href=\"{$userlink}\">{$comment['uname']}</a> <a href=\"{$userlink}\">{$comment['usurname']}</a> [<a href=\"{$userlink}\">{$comment['login']}</a>] �������(�) ���������� � ".($comment["parent_id"] == $comment["top_id"] ? "������" : "����� ������")." {$link_topic} ���������� {$link_commune} �� ������� �� ���������. $p_body";
                    require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
                    //messages::Add( users::GetUid($err, 'admin'), $user['login'], $msg, '', 1 );
                }
            }
        }

        return $this->sended;
    }
    
    /**
     * ���������� ����������� � ����� ������������ � ����������.
     * 
     * @param   string|array   $message_ids  �������������� ������������.
     * @param   resource       $connect      ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return  integer                      ���������� ������������ �����������.
     */
    function CommuneUpdateComment($message_ids, $connect = NULL)
    {
        require_once($_SERVER['DOCUMENT_ROOT'].'/classes/commune.php');
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
        $commune = new commune();
        if(!($comments = $commune->GetComments4Sending($message_ids, $connect))) {
        	$subscr = $commune->getThemeSubscribers(implode(',', $message_ids));
        	$this->CommuneUpdateTopic($subscr, (is_array($message_ids)? $message_ids[0] : $message_ids) );
           return NULL;
        }
        $top_ids = array();
        foreach($comments as $cm) {
            $top_ids[] = $cm['top_id'];
        }

        $subscribers = array();
        if(count($top_ids)) {
            $top_ids = array_unique($top_ids);
            $subscr = $commune->getThemeSubscribers(implode(',', $top_ids));
            
            foreach($subscr as $row) {
                $subscribers[$row['message_id']][] = $row;
            }
        }

        foreach($comments as $comment) {
            $this->subject = '����������� � ���������� �'.$comment['commune_name'].'� ��������������';
            $userlink = $GLOBALS["host"]."/users/".$comment['login'];
            $skip_users = array();
            $link_commune = "<a href='{$GLOBALS['host']}/commune/?id={$comment['commune_id']}' target='_blank'>{$comment['commune_name']}</a>";
            $friendly_url_topic = getFriendlyURL('commune', $comment["top_id"]); 
            $link_topic = ($comment["top_title"]? "<a href='{$GLOBALS['host']}{$friendly_url_topic}' target='_blank'>{$comment['top_title']}</a>" : '');
        
            $admin_userlink = $GLOBALS["host"]."/users/".$comment['admin_login'];
            if($comment['commune_id'] == commune::COMMUNE_BLOGS_ID && $comment['p_user_id'] != $comment['user_id']) {
                $admin_user = "��������� ����������";
            } else {
                $admin_user = "<a href='{$admin_userlink}'>{$comment['admin_uname']} {$comment['admin_usurname']}</a> [<a href='{$admin_userlink}'>{$comment['admin_login']}</a>]";
            }
            
            if($comment['p_user_id'] != $comment['user_id']
                 && $comment['p_email']
                 && substr($comment['p_subscr'],5,1)=='1'
                 && $comment['p_banned'] == '0')
            {
                // ���������� ��������.
                $body_start = "
{$admin_user} ��������������(�) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">�����������</a> � ������ ���������/����������� � ������ �<a href=\"{$GLOBALS['host']}{$friendly_url_topic}{$this->_addUrlParams('b', '?')}\" target=\"_blank\">{$comment['top_title']}</a>� ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['commune_name']}</a>�.
<br/><br/>
--------
";

            $body_subscr =
"<a href=\"{$userlink}\">{$comment['uname']}</a> <a href=\"{$userlink}\">{$comment['usurname']}</a> [<a href=\"{$userlink}\">{$comment['login']}</a>] ��������������(�) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">�����������</a> � ���������/�����������.
<br/><br/>
--------
";

            $body = 
"<br/>".reformat2($comment['title'],100)."
<br/>".reformat2($comment['msgtext'],100,0,1)."
<br/><br/>
--------
";
$p_body = 
reformat2($comment['title'],100)."
".str_replace(array("\r", "\n", "<br>", "<br/>"), array("__NEWLINE__", "__NEWLINE__", "__NEWLINE__", "__NEWLINE__" ), $comment['msgtext'])."
--------";
                $p_body = str_replace("__NEWLINE__", "\n", $p_body);
                $p_body = str_replace("<br/>", "\n", "\n--------\n".$p_body);
                $this->recipient = $comment['p_uname']." ".$comment['p_usurname']." [".$comment['p_login']."] <".$comment['p_email'].">";
                $this->message = $this->GetHtml($comment['p_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['p_login']));
                $this->SmtpMail('text/html');
                $skip_users[] = $comment['p_user_id'];
            }

            if(  $comment['t_email']
                 && substr($comment['t_subscr'],5,1)=='1'
                 && $comment['t_banned'] == '0'
                 && ! in_array($comment["t_user_id"], $skip_users) )
            {
                // ���������� ������ ������.
                $body_start = "
��������� ���������� �������������� <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">". ($comment["t_login"] == $comment["admin_login"] ? '����' : '') ." ���������/�����������</a> � ������ �<a href=\"{$GLOBALS['host']}{$friendly_url_topic}{$this->_addUrlParams('b', '?')}\" target=\"_blank\">{$comment['top_title']}</a>� ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['commune_name']}</a>�.
<br/><br/>
--------
";
                $body_subscr =
"��������� �������������� <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\"> ". ($comment["t_login"] == $comment["admin_login"] ? '����' : '') ." ��������� / �����������</a>.
<br/><br/>
--------
";
            
                $this->recipient = $comment['t_uname']." ".$comment['t_usurname']." [".$comment['t_login']."] <".$comment['t_email'].">";
                $this->message = $this->GetHtml($comment['t_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['t_login']));
                $this->SmtpMail('text/html');
                $skip_users[] = $comment['t_user_id'];
                $admin_userlink = $GLOBALS["host"]."/users/".$comment['admin_login'];
                $msg = "������������, {$comment['t_uname']} {$comment['t_usurname']}.<br/>
<a href=\"{$admin_userlink}\">{$comment['admin_uname']}</a> <a href=\"{$admin_userlink}\">{$comment['admin_usurname']}</a> [<a href=\"{$admin_userlink}\">{$comment['admin_login']}</a>] �������������� ���������� � ������ ��������� / ����������� {$link_topic} � ���������� {$link_commune}. $p_body";
                //messages::Add( users::GetUid($err, 'admin'), $comment['t_login'], $msg, '', 1 );
            }
            if ( ! in_array($comment["user_id"], $skip_users) ) {
	                // ���������� ������ �����������.
	                $body_start = ($comment["admin_login"] == $comment["t_login"] ? "����� ���� " : "��������� ���������� ")."
	�������������� <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\"> ���� ���������/�����������</a> � ������ �<a href=\"{$GLOBALS['host']}{$friendly_url_topic}{$this->_addUrlParams('b', '?')}\" target=\"_blank\">{$comment['top_title']}</a>� ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['commune_name']}</a>�.
	<br/><br/>
	--------
	";
	                $body_subscr = ($comment["admin_login"] == $comment["t_login"] ? "����� ���� " : "��������� ���������� ")."
	�������������� <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\"> ���� ��������� / �����������</a>.
	<br/><br/>
	--------
	";
	                $this->recipient = $comment['uname']." ".$comment['usurname']." [".$comment['login']."] <".$comment['email'].">";
	                $this->message = $this->GetHtml($comment['uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['login']));
	                $this->SmtpMail('text/html');
	                $msg = "������������, {$comment['uname']}.<br/>
<a href=\"{$admin_userlink}\">{$comment['admin_uname']}</a> <a href=\"{$admin_userlink}\">{$comment['admin_usurname']}</a> [<a href=\"{$admin_userlink}\">{$comment['admin_login']}</a>] �������������� ��� ���������� � ������ {$link_topic} � ���������� {$link_commune}. $p_body";
                    //messages::Add( users::GetUid($err, 'admin'), $comment['login'], $msg, '', 1 );
	                $skip_users[] = $comment['user_id'];
           }
//������ ����������
$body_start = "
{$admin_user} ��������������(�) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">�����������</a> � ������ ���������/����������� � ������ �<a href=\"{$GLOBALS['host']}/commune/".translit($comment['commune_name'])."/{$comment['top_id']}/".translit($comment['group_name'])."/".translit($comment['top_title'])."/{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['top_title']}</a>� ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['commune_name']}</a>�.
<br/><br/>
--------
";

            $body_subscr =
"{$admin_user} ��������������(�) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">�����������</a> � ���������/�����������.
<br/><br/>
--------
";

            $body = 
"<br/>".reformat2($comment['title'],100)."
<br/>".reformat2($comment['msgtext'],100,0,1)."
<br/><br/>
--------
";
$p_body = 
"<br/>".reformat2($comment['title'],100)."
<br/>".str_replace(array("\r", "\n", "<br>", "<br/>"), array("__NEWLINE__", "__NEWLINE__", "__NEWLINE__", "__NEWLINE__" ), $comment['msgtext'])."
--------";

            $p_body = str_replace("__NEWLINE__", "<br/>", $p_body);
            $p_body = str_replace("<br/>", "\n", "\n--------\n".$p_body);
            if(isset($subscribers[$comment['top_id']])) {
                // �������� ���� ����������� ������
                foreach($subscribers[$comment['top_id']] as $user) {
                    // ����� �������� � ������
                    if(in_array($user['user_id'], $skip_users)) continue;
                    
                    $link_commune = "<a href='{$GLOBALS['host']}/commune/?id={$comment['commune_id']}' target='_blank'>{$comment['commune_name']}</a>";
                    $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
                    $this->message = $this->GetHtml($user['uname'], 
                        $body_subscr . $body,
                        array('header' => 'subscribe_edit_comment', 'footer' => 'subscribe_edit_comment'),
                        array('type' => 0, 'title' => $link_commune, 'login' => $user['login']));
                    $this->SmtpMail('text/html'); 
                }
            }
        }

        return $this->sended;
    }
    /**
     * �������� ����������� � �������������� ������ � ����������
     * ���������� � ��� �������, ����� commune::GetComments4Sending ������� FALSE
     * @param $subscr - ������ �����������, ������������ commune::getThemeSubscribers
     * @param $msg_id - ������������� ���� ���������� ��� ����������� ����������
     */
    function CommuneUpdateTopic($subscr, $msg_id) {
        $subscribers = array();
        $info = commune::getMessageInfoByMsgID( $msg_id );
        $link_commune = "<a href='{$GLOBALS['host']}/commune/?id={$info['commune_id']}' target='_blank'>{$info['commune_name']}</a>";
        $friendly_url_topic = getFriendlyURL('commune', $info["top_id"]); 
        $link_topic = ($info["title"]? "<a href='{$GLOBALS['host']}{$friendly_url_topic}' target='_blank'>{$info['title']}</a>" : '');
        $skip_users = array();
        $admin_userlink = $GLOBALS["host"]."/users/".$info['editor_login'];
        if($info['commune_id'] == commune::COMMUNE_BLOGS_ID && $info["commentator_uid"] != $info["editor_id"]) {
            $admin_user = "��������� ����������";
        } else {
            $admin_user = "<a href='{$admin_userlink}'>{$info['editor_uname']} {$info['editor_usurname']}</a> [<a href='{$admin_userlink}'>{$info['editor_login']}</a>]";
        }
        //�������� ������ �����������
        if ($info["commentator_uid"] != $info["editor_id"] && $info["parent_id"]) {
        	$this->subject = ($info['parent_id']?'��� �����������':'��� ����').($info['title'] ? ' �'.$info['title'].'� � ����������':' � ����������').' �'.$info['commune_name'].'� ��������������.';

            $body_start = "
    {$admin_user} ��������������(�) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$info['top_id']}.{$msg_id}{$this->_addUrlParams('b', '&')}#c_{$msg_id}\">���� ���������/�����������</a> � ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>�.
    <br/><br/>
    --------
    ";
            $body_subscr =
    "{$admin_user} ��������������(�) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$info['top_id']}.{$msg_id}{$this->_addUrlParams('b', '&')}#c_{$msg_id}\">�����������</a> � ���������/�����������.
    <br/><br/>
    --------
    ";
            $body = 
    "<br/>".reformat2($info['msgtext'],100,0,1)."
    <br/><br/>
    --------
    ";
            $this->recipient = $info['commentator_uname']." ".$info['commentator_usurname']." [".$info['commentator_login']."] <".$info['commentator_email'].">";
            $this->message = $this->GetHtml($info['commentator_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$info['commentator_login']));
            $this->SmtpMail('text/html');
            $skip_users[] = $info['commentator_uid'];
        }
        
        //�������� ������ ������
        if ( $info && $info['topicstarter_uid'] && $info['topicstarter_uid'] != $info['editor_id'] && ! in_array($info['topicstarter_uid'], $skip_users) ) {
   /*2*/         $this->subject = ($info['parent_id']?'����������� � ������ �����':'��� ����').($info['title'] ? ' �'.$info['title'].'� � ����������':' ����������').' �'.$info['commune_name'].'� ��������������.';
            $body_start = "
	        <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$msg_id}.{$user['message_id']}{$this->_addUrlParams('b', '&')}\">�����������</a> � ������ ����� � ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>� �������������� ����������� ����������.
	        <br/><br/>
	        --------
	        ";
            if (!$info["parent_id"]) {
                $body_start = "
	            <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$msg_id}.{$user['message_id']}{$this->_addUrlParams('b', '&')}\">��� ����</a> � ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>� �������������� ����������� ����������.
	            <br/><br/>
	            --------
	            ";  
            }
            $body = 
	        "<br/>".reformat2($info['msgtext'],100,0,1)."
	        <br/><br/>
	        --------
	        ";
            $this->recipient = $info['topicstarter_uname']." ".$info['topicstarter_usurname']." [".$info['topicstarter_login']."] <".$info['topicstarter_email'].">";
            $this->message = $this->GetHtml($info['topicstarter_uname'], 
                        $body_start . $body,
                        array('header' => 'subscribe_edit_post', 'footer' => 'default'),
                        array('type' => 0, 'title' => $link_commune, 'topic_name' => $link_topic, 'is_comment' => $info['parent_id'], 'to_topicstarter' => true, 'login' => $info['topicstarter_login'],  'is_author' => ($info['deleter_uid'] == $info['topicstarter_uid']) ));
            $this->SmtpMail('text/html');
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
            $msg = "������������, {$info['topicstarter_uname']}.
��������� ���������� �������������� ".( $info["parent_id"] ? "����������� � ������ �����" : "��� ����" )." {$link_topic} � ���������� {$link_commune}.";
            //messages::Add( users::GetUid($err, 'admin'), $info['topicstarter_login'], $msg, '', 1 );
            $skip_users[] = $info['topicstarter_uid'];
        }
        foreach($subscr as $user) {
            if ( in_array($user["user_id"], $skip_users) ) continue;
            $this->subject = ($info['parent_id']?'� ������':'�����').($info['title'] ? ' �'.$info['title'].'� � ����������':' ����������').' �'.$info['commune_name'].'� ��������������'.($info['parent_id']?' �����������':'');
            $userlink = $GLOBALS["host"]."/users/".$info['editor_login'];
            $body_start = "
            {$admin_user} ��������������(�) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$msg_id}.{$user['message_id']}{$this->_addUrlParams('b', '&')}\">���������</a> � ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>�.
            <br/><br/>
            --------
            ";
            
            $body_subscr =
            "{$admin_user} ��������������(�) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$msg_id}.{$this->_addUrlParams('b', '&')}\">���������</a>.
            <br/><br/>
            --------
            ";
            
            $body = 
            "<br/>".reformat2($info['msgtext'],100,0,1)."
            <br/><br/>
            --------
            ";
            $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
            $this->message = $this->GetHtml($user['uname'], 
                            $body_start . $body,
                            array('header' => ($info['parent_id'] ? 'subscribe_edit_comment' : 'subscribe_edit_post'), 'footer' =>  'default' ),
                            array('type' => 0, 'title' => $link_commune, 'topic_name' => $link_topic, 'login' => $user['login'], 'is_admin' => ($info['editor_id'] == $info['topicstarter_uid']), 'to_subscriber' => true ));
            $this->SmtpMail('text/html');
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
            $msg = "������������, {$user['uname']}.
    �������������� ".($info["parent_id"] ? "����������� � ����� {$link_topic}" : '���� &laquo;'.$info["title"]."&raquo;"). " ���������� {$link_commune} �� ������� �� ���������.";
            //messages::Add( users::GetUid($err, 'admin'), $user['login'], $msg, '', 1 );
        }
    }
    /**
     * ���������� ����������� � ����� ������������ � ����������.
     * 
     * @param   string|array   $message_ids  �������������� ������������.
     * @param   resource       $connect      ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return  integer                      ���������� ������������ �����������.
     */
    function CommuneDeleteComment($message_ids, $connect = NULL)
    {
        require_once($_SERVER['DOCUMENT_ROOT'].'/classes/commune.php');
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
        $commune = new commune();
        if(!($comments = $commune->GetComments4Sending($message_ids, $connect))) {
            $subscr = $commune->getThemeSubscribers(implode(',', $message_ids));
            $this->CommuneDeleteTopic($subscr, (is_array($message_ids)? $message_ids[0] : $message_ids) );
            return NULL;
        }
        $top_ids = array();
        foreach($comments as $cm) {
            $top_ids[] = $cm['top_id'];
        }

        $subscribers = array();
        if(count($top_ids)) {
            $top_ids = array_unique($top_ids);
            $subscr = $commune->getThemeSubscribers(implode(',', $top_ids));
            
            foreach($subscr as $row) {
                $subscribers[$row['message_id']][] = $row;
            }
        }

        foreach($comments as $comment) {
            $this->subject = '����������� � ���������� �'.$comment['commune_name'].'� ������';
            $skip_users = array();
            $skip_users[] = $comment['user_id'];
            $userlink = $GLOBALS["host"]."/users/".$comment['login'];
            if($comment['commune_id'] == commune::COMMUNE_BLOGS_ID && $comment['p_user_id'] != $comment['user_id']) {
                $admin_user = "��������� ����������";
            } else {
                $admin_user = "<a href='{$userlink}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$userlink}'>{$comment['login']}</a>]";
            }
            $body_start = "
{$admin_user} ������(�) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">�����������</a> � ������ ���������/����������� � ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$comment['commune_name']}</a>�.
<br/><br/>
--------
";
            $body_subscr =
"{$admin_user} ������(�) <a href=\"{$GLOBALS['host']}/commune/?id={$comment['commune_id']}&site=Topic&post={$comment['top_id']}.{$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}\">�����������</a> � ���������/�����������.
<br/><br/>
--------
";
            $body = 
"<br/>".reformat2($comment['title'],100)."
<br/>".reformat2($comment['msgtext'],100,0,1)."
<br/><br/>
--------
";
$p_body = 
"<br/>".reformat2($comment['title'],100)."
<br/>".str_replace(array("\r", "\n", "<br>", "<br/>"), array("__NEWLINE__", "__NEWLINE__", "__NEWLINE__", "__NEWLINE__" ), $comment['msgtext'])."
--------";

            $p_body = str_replace("__NEWLINE__", "<br/>", $p_body);
            $p_body = str_replace("<br/>", "\n", "\n--------\n".$p_body);
            if($comment['p_user_id'] != $comment['user_id']
                 && $comment['p_email']
                 && substr($comment['p_subscr'],5,1)=='1'
                 && $comment['p_banned'] == '0')
            {
                // ���������� ��������.
                $this->recipient = $comment['p_uname']." ".$comment['p_usurname']." [".$comment['p_login']."] <".$comment['p_email'].">";
                $this->message = $this->GetHtml($comment['p_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['p_login']));
                $this->SmtpMail('text/html');
                $skip_users[] = $comment['p_user_id'];
            }

            if($comment['t_user_id']!=$comment['user_id']
                 && $comment['t_user_id']!=$comment['p_user_id']
                 && $comment['t_email']
                 && substr($comment['t_subscr'],5,1)=='1'
                 && $comment['t_banned'] == '0')
            {
                // ���������� ������ ������.
                $this->recipient = $comment['t_uname']." ".$comment['t_usurname']." [".$comment['t_login']."] <".$comment['t_email'].">";
                $this->message = $this->GetHtml($comment['t_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['t_login']));
                $this->SmtpMail('text/html');
                $skip_users[] = $comment['t_user_id'];
            }

            if(isset($subscribers[$comment['top_id']])) {
                // �������� ���� ����������� ������
                foreach($subscribers[$comment['top_id']] as $user) {
                    // ����� �������� � ������
                    if(in_array($user['user_id'], $skip_users)) continue;
                    
                    $link_commune = "<a href='{$GLOBALS['host']}/commune/?id={$comment['commune_id']}' target='_blank'>{$comment['commune_name']}</a>";
                    $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
                    $this->message = $this->GetHtml($user['uname'], 
                        $body_subscr . $body,
                        array('header' => 'subscribe', 'footer' => 'subscribe'),
                        array('type' => 0, 'title' => $link_commune, 'login' => $user['login']));
                    $this->SmtpMail('text/html');
                }
            }
        }

        return $this->sended;
    }

    /**
     * �������� ����������� �� �������� ������ � ����������
     * ���������� � ��� �������, ����� commune::GetComments4Sending ������� FALSE
     * @param $subscr - ������ �����������, ������������ commune::getThemeSubscribers
     * @param $msg_id - ������������� ���� ���������� ��� ����������� ����������
     */
    function CommuneDeleteTopic($subscr, $msg_id) {
        $subscribers = array();
        $info = commune::getMessageInfoByMsgID( $msg_id );
        $link_commune = "<a href='{$GLOBALS['host']}/commune/?id={$info['commune_id']}' target='_blank'>{$info['commune_name']}</a>";
        $friendly_url_topic = getFriendlyURL('commune', $info["top_id"]); 
        $link_topic = ($info["title"]? "<a href='{$GLOBALS['host']}{$friendly_url_topic}' target='_blank'>{$info['title']}</a>" : '');
        $skip_users = array();
        
        $admin_userlink = $GLOBALS["host"]."/users/".$info['deleter_login'];
        if($info['commune_id'] == commune::COMMUNE_BLOGS_ID && $info["commentator_uid"] != $info["deleter_uid"]) {
            $admin_user = "��������� ����������";
        } else {
            $admin_user = "<a href='{$admin_userlink}'>{$info['deleter_uname']} {$info['deleter_usurname']}</a> [<a href='{$admin_userlink}'>{$info['deleter_login']}</a>]";
        }
        
        //�������� ������ �����������
        if ($info["commentator_uid"] != $info["deleter_uid"]) {
        	$skip_users[] = $info['commentator_uid'];
	        $this->subject = '���e ��������� � ���������� �������.';
	        $body_start = "
	{$admin_user} ������(�) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$info['top_id']}.{$msg_id}{$this->_addUrlParams('b', '&')}#c_{$msg_id}\">���� ���������/�����������</a> � ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>�.
	<br/><br/>
	--------
	";
	        $body_subscr =
	"{$admin_user} ������(�) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$info['top_id']}.{$msg_id}{$this->_addUrlParams('b', '&')}#c_{$msg_id}\">���������</a> � ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>�.
	<br/><br/>
	--------
	";
	        $body = 
	"<br/>".reformat2($info['msgtext'],100,0,1)."
	<br/><br/>
	--------
	";
	        $this->recipient = $info['commentator_uname']." ".$info['commentator_usurname']." [".$info['commentator_login']."] <".$info['commentator_email'].">";
	        $this->message = $this->GetHtml($info['commentator_uname'], $body_start . $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$info['commentator_login']));
	        $this->SmtpMail('text/html');
        }
        
        //�������� ������ ������
        if ( $info && $info['topicstarter_uid'] && $info['topicstarter_uid'] != $info['deleted_id'] && ! in_array($info['topicstarter_uid'], $skip_users))  {
            $this->subject = ($info['parent_id']?'����������� � ������ �����':'��� ����').($info['title'] ? ' �'.$info['title'].'� � ����������':' ����������').' �'.$info['commune_name'].'� ������.';
            $body_start = "
        <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$msg_id}.{$user['message_id']}{$this->_addUrlParams('b', '&')}\">�����������</a> � ������ �����(1) � ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>� ������ ����������� ����������.
        <br/><br/>
        --------
        ";

            $body = 
        "<br/>".reformat2($info['msgtext'],100,0,1)."
        <br/><br/>
        --------
        ";
            $this->recipient = $info['topicstarter_uname']." ".$info['topicstarter_usurname']." [".$info['topicstarter_login']."] <".$info['topicstarter_email'].">";
            $this->message = $this->GetHtml($info['topicstarter_uname'], 
                        $body_start . $body,
                        array('header' => 'subscribe_delete_post', 'footer' => 'subscribe_delete_post'),
                        array('type' => 0, 'title' => $link_commune, 'topic_name' => $link_topic, 'is_comment' => $info['parent_id'], 'to_topicstarter' => true, 'login' => $info['topicstarter_login'],  'is_author' => ($info['deleter_uid'] == $info['topicstarter_uid']) ));
            $this->SmtpMail('text/html');
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
            $msg = "������������, {$info['topicstarter_uname']}.
��������� ���������� ������ ���������� � ������ ����� {$link_topic} � ���������� {$link_commune}.";
            //messages::Add( users::GetUid($err, 'admin'), $info['topicstarter_login'], $msg, '', 1 );
            $skip_users[] = $info['topicstarter_uid'];
        }
        foreach($subscr as $user) {
            if ( !in_array($user["user_id"], $skip_users) ) {
				$this->subject = ($info['parent_id']?'� ������':'�����').($info['title'] ? ' �'.$info['title'].'� � ����������':' ����������').' �'.$info['commune_name'].'� ������'.($info['parent_id']?' �����������':'');
				$userlink = $GLOBALS["host"]."/users/".$info['deleter_login'];
				$body_start = "
				{$admin_user} ������(-�) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$msg_id}.{$user['message_id']}{$this->_addUrlParams('b', '&')}\">���������</a> � ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}{$this->_addUrlParams('b', '&')}\" target=\"_blank\">{$info['commune_name']}</a>�.
				<br/><br/>
				--------
				";
				
				$body_subscr =
				"{$admin_user} ������(�) <a href=\"{$GLOBALS['host']}/commune/?id={$info['commune_id']}&site=Topic&post={$msg_id}.{$this->_addUrlParams('b', '&')}\">���������</a>.
				<br/><br/>
				--------
				";
				
				$body = 
				"<br/>".reformat2($info['msgtext'],100,0,1)."
				<br/><br/>
				--------
				";
				$this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
				$this->message = $this->GetHtml($user['uname'], 
								$body_start . $body,
								array('header' => 'subscribe_delete_post', 'footer' => 'subscribe_delete_post'),
								array('type' => 0, 'title' => $link_commune, 'topic_name' => $link_topic, 'is_comment' => $info['parent_id'], 'login' => $user['login'], 'is_admin' => ($info['deleter_id'] == $info['topicstarter_uid']) ));
				$this->SmtpMail('text/html');
				require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
			}
		}
	}
    
    /**
     * ���������� ����������� ��� ������������� � ������.
     *
     * @param  string|array $ids �������������� �������������
     * @param  resource $connect ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����
     * @return integer ���������� ������������ �����������
     */
    function UserRazban($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        
        $user          = new users();
        $this->subject = '������ � ���������� �� FL.ru �������������';
        $message       = '������� FL.ru �������������� ��� ������ � ������ ����������.';
        
        foreach ( $ids as $id ) {
            $user->GetUserByUID( $id );
            $to_user = array(
                'usurname' => $user->usurname, 
                'uname'    => $user->uname, 
                'login'    => $user->login, 
                'photo'    => $user->photo,
                'email'    => $user->email
            ); 
            
            $this->message   = $this->GetHtml( $to_user['uname'], $message, 'info' );
            $this->recipient = $to_user['uname'].' '.$to_user['usurname'].' ['.$to_user['login'].'] <'.$to_user['email'].'>';
            
            $this->SmtpMail('text/html');
        }
        
        return $this->sended; 
    }

    
    /**
     * ����������� � ���������� �����
     * 
     * @param  string|array $operation_ids �������������� �������� � ����������������� �������
     * @param  resource $connect ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����
     * @return integer ���������� ������������ �����������
     */
    function DepositMail( $operation_ids, $connect = NULL ) {
        return; //##0027187
        global $host;
        if ( !empty($operation_ids) ) {
            $operation_ids = is_array($operation_ids) ? array_unique($operation_ids) : array($operation_ids);
        	
        	$sQuery = "SELECT ao.ammount, ao.trs_sum, ao.balance, u.uname, u.usurname, u.login, u.email FROM account_operations ao
        	   INNER JOIN account a ON a.id = ao.billing_id 
        	   INNER JOIN users u ON u.uid = a.uid 
        	   WHERE ao.id IN (?l) AND u.is_banned = '0' AND substr(u.subscr::text,16,1) = '1' AND u.is_active = true";
        	
        	$mRes = $GLOBALS['DB']->query( $sQuery, $operation_ids );
        	
        	if ( !$GLOBALS['DB']->error && pg_num_rows($mRes) ) {
                    while ( $aOne = pg_fetch_assoc($mRes) ) {
                        $this->subject   = '���������� ������ ����� �� FL.ru';
                        $this->recipient = $aOne['uname']." ".$aOne['usurname']." [".$aOne['login']."] <".$aOne['email'].">";;

                        $message =
'�� ��� ������ ���� ���� ��������� ����� ' . number_format($aOne['trs_sum'], 2, ',', ' ') . ' ���.<br />
<br />
� ��������� ����������� �� ���������� �������� � ������ ������ �� FL.ru �� ������ ������������ � ����� <a href="https://feedback.fl.ru/'.$this->_addUrlParams('b', '?').'">���������� ���������</a>.<br />
<br />
�� ���� ����������� �������� ����������� � ���� <a href="https://feedback.fl.ru/' . $this->_addUrlParams('b', '?') . '">������ ���������</a>.';
                        $this->message = $this->GetHtml(($aOne['uname'] ? $aOne['uname'] : $aOne['login']), $message, array('header' => 'default', 'footer' => 'default'), array('login' => $aOne['login']));
                        $this->message = str_replace('%USER_NAME%', ($aOne['uname'] ? $aOne['uname'] : $aOne['login']), $this->message);
                        $this->send( 'text/html' );
                    }
                }
         }
    }
    
    /**
     * ���������� ����������� � ����� ������������ � ��������� �����������.
     * 
     * @param  string|array $message_ids �������������� ������������
     * @param  resource $connect ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����
     * @return integer ���������� ������������ �����������
     */
    function AdminLogCommentsMail( $message_ids, $connect = NULL ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
        
        $admin_log = new admin_log();
        $noSend    = array();
        
        if ( !$comments = $admin_log->GetComments4Sending($message_ids, $connect) ) {
            return NULL;
        }
        
        $this->subject = '����������� � �������� ���������� �� ����� FL.ru';
        
        foreach( $comments as $comment ) {
            $sObjEntity = admin_log::$aObj[$comment['obj_code']]['name'];
            $sObjName   = $comment['object_name'] ? $comment['object_name'] : '<��� ��������>';
            setlocale(LC_ALL, 'ru_RU.CP1251');
            $sObjName   = str_replace(array('<','>'), array('&lt;', '&gt;'), $sObjName );
            setlocale(LC_ALL, "en_US.UTF-8");
            $sObjLink   = $comment['object_link'] ? '<a href="'.$comment['object_link'].$this->_addUrlParams('b').'">'.$sObjName.'</a>' : $sObjName;
            
            // ���������� ������ ������������� ����������
            if ( 
                $comment['s_uid'] != $comment['uid']
                && $comment['s_email']
                && $comment['s_banned'] == '0'
            ) {
                $this->message = $this->GetHtml($comment['s_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['login']}</a>]
�������(�) ��� ����������� � �������� ���������� �� ����� FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(LenghtFormatEx(strip_tags($comment['title']), 300))."<br />---<br />"): "")
.$this->ToHtml(LenghtFormatEx(strip_tags($comment['msgtext']), 300))."
<br /> --------
<br />
$sObjEntity: $sObjLink<br />
<br />
<a href='{$GLOBALS['host']}/siteadmin/admin_log/?view={$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['comment_id']}'>{$GLOBALS['host']}/siteadmin/admin_log/?view={$comment['id']}#c_{$comment['comment_id']}</a>
<br />
<br />
", array('header'=>'simple', 'footer'=>'simple') );
                $this->recipient = $comment['s_uname']." ".$comment['s_usurname']." [".$comment['s_login']."] <".$comment['s_email'].">";
                $this->SmtpMail( 'text/html' );
                $noSend[ $comment['s_uid'] ] = $comment['s_uid'];
            }
            
            // ���������� ������ ��������
            if ( 
                $comment['a_uid'] != $comment['uid']
                && $comment['a_uid'] != $comment['s_uid']
                && $comment['a_email']
                && $comment['a_banned'] == '0' 
            ) {
                $this->message = $this->GetHtml($comment['s_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['login']}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['login']}</a>]
�������(�) ��� ����������� � �������� ���������� �� ����� FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(LenghtFormatEx(strip_tags($comment['title']), 300))."<br />---<br />"): "")
.$this->ToHtml(LenghtFormatEx(strip_tags($comment['msgtext']), 300))."
<br /> --------
<br />
$sObjEntity: $sObjLink<br />
<br />
<a href='{$GLOBALS['host']}/siteadmin/admin_log/?view={$comment['id']}{$this->_addUrlParams('b', '&')}#c_{$comment['comment_id']}'>{$GLOBALS['host']}/siteadmin/admin_log/?view={$comment['id']}#c_{$comment['comment_id']}</a>
<br />
<br />
", array('header'=>'simple', 'footer'=>'simple') );
                $this->recipient = $comment['a_uname']." ".$comment['a_usurname']." [".$comment['a_login']."] <".$comment['a_email'].">";
                $this->SmtpMail( 'text/html' );
                $noSend[ $comment['a_uid'] ] = $comment['a_uid'];
            }
            
            // �������� ���� �� �����������
        }
        
        return $this->sended;
    }
    
    /**
     * ���������� ����������� � ����� ��������� �����������
     * 
     * @param  string|array $message_ids �������������� ������������
     * @param  resource $connect ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����
     * @return integer ���������� ������������ �����������
     */
    function AdminLogNotice( $log_ids, $connect = NULL ) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/admin_log.php";
        
        $admin_log = new admin_log();
        
        if ( !$comments = $admin_log->GetNotices4Sending($log_ids, $connect) ) {
            return NULL;
        }
        
        $this->subject = '����� �������� ���������� �� ����� FL.ru';
        
        foreach( $comments as $aOne ) {
            if ( 
                hasPermissions($aOne['rights'], $aOne['notice_uid']) 
                && $aOne['notice_uid'] != $aOne['a_uid'] 
            ) {
                $sObjEntity = admin_log::$aObj[$aOne['obj_code']]['name'];
                $sObjName   = $aOne['object_name'] ? $aOne['object_name'] : '<��� ��������>';
                setlocale(LC_ALL, 'ru_RU.CP1251');
                $sObjName   = str_replace(array('<','>'), array('&lt;', '&gt;'), $sObjName );
                setlocale(LC_ALL, "en_US.UTF-8");
                
                if ( $aOne['object_link'] ) {
                	$sObjLink = '<a href="' . getAbsUrl( $aOne['object_link'] ) . '">' . $sObjName . '</a>';
                }
                else {
                    $sObjLink = $sObjName;
                }
                
            	$this->message = $this->GetHtml( $aOne['uname'], "
����� �������� ����������:<br/>
<a href='{$GLOBALS['host']}/users/{$aOne['a_login']}{$this->_addUrlParams('b')}'>{$aOne['a_uname']} {$aOne['a_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$aOne['a_login']}{$this->_addUrlParams('b')}'>{$aOne['a_login']}</a>]
<br/>
$sObjEntity: $sObjLink<br />
��������: {$aOne['act_name']}<br />
<br />
<a href='{$GLOBALS['host']}/siteadmin/admin_log/?view={$aOne['id']}{$this->_addUrlParams('b', '&')}'>{$GLOBALS['host']}/siteadmin/admin_log/?view={$aOne['id']}</a>
<br />
<br />
            	", array('header'=>'simple', 'footer'=>'simple') );
                
            	$this->recipient = $aOne['uname']." ".$aOne['usurname']." [".$aOne['login']."] <".$aOne['email'].">";
                $this->SmtpMail( 'text/html' );
            }
        }
        
        return $this->sended;
    }
    
    /**
     * ���������� ����������� � ����� ������������ � �����.
     * 
     * @param   string|array   $message_ids  �������������� ������������.
     * @param   resource       $connect      ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return  integer                      ���������� ������������ �����������.
     */
    function BlogNewComment($message_ids, $connect = NULL)
    {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/blogs.php";

        $blogs = new blogs();
        if(!($comments = $blogs->GetComments4Sending($message_ids, $connect))){
            return NULL;
        }
        
        $this->subject = "����������� � ���������� �� ����� FL.ru";
        
        $userSubscribe = $blogs->getUsersSubscribe($message_ids, $connect);
        foreach($comments as $comment)
        {
            // ���������� ��������.
            if( substr($comment['p_subscr'], 2, 1) == '1' 
                && $comment['p_uid'] != $comment['uid']
                && $comment['p_email']
                && $comment['p_banned'] == '0')
            {
                $this->message = $this->GetHtml($comment['p_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['login']}/{$this->_addUrlParams('b')}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['login']}</a>]
�������(-�) <a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>�����������</a> � ����� ����������/������������ � ���������� �� ����� FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(LenghtFormatEx(strip_tags($comment['title']), 300))."<br />---<br />"): "")
.$this->ToHtml(LenghtFormatEx(strip_tags($comment['msgtext']), 300))."
<br /> --------
<br />
", array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['p_login']));
                $this->recipient = $comment['p_uname']." ".$comment['p_usurname']." [".$comment['p_login']."] <".$comment['p_email'].">";
                $this->SmtpMail('text/html');
                $notSend[$comment['p_uid']] = $comment['p_uid'];
            }
            // ���������� ������ ������.
            if( substr($comment['t_subscr'], 2, 1) == '1' 
                    && $comment['t_uid'] != $comment['uid']
                    && $comment['t_uid'] != $comment['p_uid']
                    && $comment['t_email']
                    && $comment['t_banned'] == '0' )
            {
                $this->message = $this->GetHtml($comment['t_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['login']}</a>]
�������(-�) <a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>�����������</a> � ����� ����������/������������ � ���������� �� ����� FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(LenghtFormatEx(strip_tags($comment['title']), 300))."<br />---<br />"): "")
.$this->ToHtml(LenghtFormatEx(strip_tags($comment['msgtext']), 300))."
<br /> --------
<br />
", array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['t_login']));
                $this->recipient = $comment['t_uname']." ".$comment['t_usurname']." [".$comment['t_login']."] <".$comment['t_email'].">";
                $this->SmtpMail('text/html');
                $notSend[$comment['t_uid']] = $comment['t_uid'];
            }
        }

        // �������� ������������� �� ����  
        if($userSubscribe)
        foreach($userSubscribe as $comment) {
            $this->subject = "����������� � ������ �� ����� FL.ru";
           
            if( substr($comment['s_subscr'], 2, 1) == '1' 
                && !$notSend[$comment['s_uid']] 
                && $comment['s_uid'] != $comment['uid'] 
                && $comment['s_email'])
            {
                $link_title = "<a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}{$this->_addUrlParams('b', '&')}' target='_blank'>" . ( $comment['blog_title'] == ''? '��� ��������' : $comment['blog_title'] )  ."</a>";  
                $this->message = $this->GetHtml($comment['s_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['login']}/{$this->_addUrlParams('b')}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['login']}</a>]
�������(-�) <a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>����� �����������</a> � ����������/������������ � ���������� �� ����� FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(input_ref(LenghtFormatEx($comment['title'], 300), 1))."<br />---<br />"): "")
.$this->ToHtml(input_ref(LenghtFormatEx($comment['msgtext'], 300), 1))."
<br /> --------
<br />
", array('header' => 'subscribe', 'footer' => 'subscribe'), array('type' => 1, 'title' => $link_title));
                $this->recipient = $comment['s_uname']." ".$comment['s_usurname']." [".$comment['s_login']."] <".$comment['s_email'].">";
                $this->SmtpMail('text/html');  
            }
        }
          
        return $this->sended;
    }

/**
     * ���������� ����������� � �������������� ����������� � �����.
     * 
     * @param   string|array   $message_ids  �������������� ������������.
     * @param   resource       $connect      ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return  integer                      ���������� ������������ �����������.
     */
    function BlogUpdateComment($message_ids, $connect = NULL)
    {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/blogs.php";
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        $blogs = new blogs();
        if(!($comments = $blogs->GetComments4Sending($message_ids, $connect))) {
            return NULL;
        }
        $this->subject = "����������� � ���������� �� ����� FL.ru";
        
        $userSubscribe = $blogs->getUsersSubscribe($message_ids, $connect, true);
        foreach($comments as $comment)
        {
            // ���������� ��������.
            if( substr($comment['p_subscr'], 2, 1) == '1' 
                && ( $comment['p_uid'] != $comment['uid'] || $comment['uid'] != $comment['modified_id'] )
                && $comment['p_email']
                && $comment['p_banned'] == '0')
            {
                $this->message = $this->GetHtml($comment['p_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['m_login']}/{$this->_addUrlParams('b')}'>{$comment['m_uname']} {$comment['m_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['m_login']}{$this->_addUrlParams('b')}'>{$comment['m_login']}</a>]
��������������(�) <a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>�����������</a> � ����� ����������/������������ � ���������� �� ����� FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(LenghtFormatEx(strip_tags($comment['title']), 300))."<br />---<br />"): "")
.$this->ToHtml(LenghtFormatEx(strip_tags($comment['msgtext']), 300))."
<br /> --------
<br />
", array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['p_login']));
                $this->recipient = $comment['p_uname']." ".$comment['p_usurname']." [".$comment['p_login']."] <".$comment['p_email'].">";
                $this->SmtpMail('text/html');
                $notSend[$comment['p_uid']] = $comment['p_uid'];
            }
            // ���������� ������ ������.
            if( substr($comment['t_subscr'], 2, 1) == '1' 
                    && ( $comment['t_uid'] != $comment['uid'] || $comment['t_uid'] != $comment['modified_id'] )
                    && ( $comment['t_uid'] != $comment['p_uid'] || $comment['t_uid'] != $comment['modified_id'] )
                    && $comment['t_email']
                    && !$notSend[$comment['t_uid']]
                    && $comment['t_banned'] == '0' )
            {
                $post_type = "<a target='_blank' href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>�����������</a> � ����� ����������/������������";
                if ( $comment['reply_to'] == '' ) {
                    $post_type = "<a target='_blank' href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>���� ���������</a> ";
                    $this->subject = "����� FL.ru";
                }
                $this->message = $this->GetHtml($comment['t_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['m_login']}{$this->_addUrlParams('b')}'>{$comment['m_uname']} {$comment['m_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['m_login']}{$this->_addUrlParams('b')}'>{$comment['m_login']}</a>]
��������������(�) {$post_type} � ������ �� ����� FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(LenghtFormatEx(strip_tags($comment['title']), 300))."<br />---<br />"): "")
.$this->ToHtml(LenghtFormatEx(strip_tags($comment['msgtext']), 300))."
<br /> --------
<br />
", array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['t_login']));
                $this->recipient = $comment['t_uname']." ".$comment['t_usurname']." [".$comment['t_login']."] <".$comment['t_email'].">";
                $this->SmtpMail('text/html');
                $notSend[$comment['t_uid']] = $comment['t_uid'];
                $message = "<a href='{$GLOBALS['host']}/users/{$comment['m_login']}{$this->_addUrlParams('b')}'>{$comment['m_uname']} {$comment['m_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['m_login']}{$this->_addUrlParams('b')}'>{$comment['m_login']}</a>]
��������������(�) {$post_type} � ���������� �� ����� FL.ru.
 --------

"
.($comment['title']? ($this->ToHtml(LenghtFormatEx(strip_tags($comment['title']), 300))."
---
"): "")
.$this->ToHtml(LenghtFormatEx(strip_tags($comment['msgtext']), 300))."
 --------";
                messages::Add( users::GetUid($err, 'admin'), $comment['t_login'], $message, '', 1 );
            }
        }
        // �������� ������������� �� ����  
        if($userSubscribe)
        foreach($userSubscribe as $comment) {
            $this->subject = "����������� � ���������� �� ����� FL.ru";
            if( substr($comment['s_subscr'], 2, 1) == '1' 
                && !$notSend[$comment['s_uid']] 
                && $comment['s_email'])
            {
                $post_type = "<a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>�����������</a> � ����������/������������ � ����������";
                $message_template = "subscribe_edit_comment";
                if ( $comment['reply_to'] == '' ) {
                    $post_type = "<a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>���� � ����������</a> �� ������� �� ���������";
                    $message_template = "subscribe_edit_post";
                }
                $link_title = "<a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}{$this->_addUrlParams('b', '&')}' target='_blank'>" . ( $comment['blog_title'] == ''? '��� ��������' : $comment['blog_title'] )  ."</a>";  
                $this->message = $this->GetHtml($comment['s_uname'], "
<a href='{$GLOBALS['host']}/users/{$comment['m_login']}/{$this->_addUrlParams('b')}'>{$comment['m_uname']} {$comment['m_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['m_login']}</a>]
�������������(�) {$post_type} �� ����� FL.ru.
<br /> --------
<br />"
.($comment['title']? ($this->ToHtml(input_ref(LenghtFormatEx($comment['title'], 300), 1))."<br />---<br />"): "")
.$this->ToHtml(input_ref(LenghtFormatEx($comment['msgtext'], 300), 1))."
<br /> --------
<br />
", array('header' => $message_template, 'footer' => 'subscribe'), array('type' => 1, 'title' => $link_title));
                $this->recipient = $comment['s_uname']." ".$comment['s_usurname']." [".$comment['s_login']."] <".$comment['s_email'].">";
                $this->SmtpMail('text/html');
                $message = "������������, ".$comment['s_uname'].".                
<a href='{$GLOBALS['host']}/users/{$comment['m_login']}/{$this->_addUrlParams('b')}'>{$comment['m_uname']} {$comment['m_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['m_login']}</a>]
�������������(�) {$post_type} �� ����� FL.ru.
--------"
.($comment['title']? ($this->ToHtml(input_ref(LenghtFormatEx($comment['title'], 300), 1))."
---
"): "")
.$this->ToHtml(input_ref(LenghtFormatEx($comment['msgtext'], 300), 1))."
 --------
 ";
                messages::Add( users::GetUid($err, 'admin'), $comment['s_login'], $message, '', 1 );
            }
        }
          
        return $this->sended;
    }
	
    /**
     * ���������� ����������� � ����� ������ � ���������
     * 
     * @param   string|array    $ids        �������������� ���������
     * @param   resource        $connect    ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return  integer                     ���������� ������������ �����������.
     */
	function ContestChangeDates($ids, $connect = NULL) {
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';

		$contest = new contest(0, 0);
		if (!($prjs = $contest->GetContests4Sending($ids))) return NULL;

		$emp = new employer();
		$emp->GetUserByUID($prjs[0]['user_id']);
		
		foreach ($prjs as $prj) {
			if ($prj['email'] && substr($prj['subscr'], 8, 1) == '1' && $prj['is_banned'] == '0') {
                $prj['name'] = htmlspecialchars($prj['name'], ENT_QUOTES, 'CP1251', false);
				$userlink = HTTP_PREFIX."{$GLOBALS['host']}/users/{$emp->uname}";
				$this->message = $this->GetHtml($prj['uname'], "
					�������� <a href=\"{$userlink}\">{$emp->uname} {$emp->usurname}</a> [<a href=\"{$userlink}\">{$emp->login}</a>] �������(a) ����� ��������
					�<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('f')."\">".$prj['name']."</a>�.
                    �� ������ ������� � ����� <a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."?offer={$prj['offer_id']}{$this->_addUrlParams('f', '&')}#offer-{$prj['offer_id']}\">������</a>.
					<br /><br/>
					���� ���������� ��������: ".dateFormat("d.m.Y", $prj['end_date'])."<br />
					���� ���������� �����������: ".dateFormat("d.m.Y", $prj['win_date'])."<br />
                    ", array('header'=>'simple', 'footer'=>'frl_subscr_projects'), array('login'=>$prj['login']));
				$this->recipient = "{$prj['uname']} {$prj['usurname']} [{$prj['login']}] <{$prj['email']}>";
				$this->subject = '����� �������� �'.htmlspecialchars_decode($prj['name'], ENT_QUOTES).'� ���� ��������';
				$this->send('text/html');
				++$count;
			}
		}
		
		return $this->sended;

	}
	
	/**
     * ���������� ����������� ������ ������� � ����� �������.
     *
     * @param   string|array    $ids        �������������� ������� � �������
     * @param   resource        $connect    ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return  integer                     ���������� ������������ �����������.
     */
    function NewPrjOffer($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects_offers.php';
        $offers = new projects_offers();
        
        if (!($ofs = $offers->getNewProjectOffers($ids, $connect))) return NULL;
        
        //$this->subject = "��������� ������� �� ������";
        foreach($ofs as $offer) {            
            $offer['project_name'] = html_entity_decode($offer['project_name'], ENT_QUOTES);
            
            if($offer['kind'] == 7 OR $offer['kind'] == 2) {
                if (!$offer['to_email'] || substr($offer['to_subscr'], 8, 1) != '1') continue; // ���� �� ����� ����������� ���������� �������
                $this->subject = "����� ������ ��������� � ������� �{$offer['project_name']}�";
        		$this->recipient = " {$offer['to_uname']} {$offer['to_usurname']} [{$offer['to_login']}] <".$offer['to_email'].">";		
        		$userlink = $GLOBALS["host"]."/users/".$offer['from_login'];
        		$this->message = $this->GetHtml($offer['to_uname'], "
        		    <a href=\"{$userlink}\">{$offer['from_uname']} {$offer['from_usurname']}<a/> [<a href=\"{$userlink}\">{$offer['from_login']}</a>] �������(a) ����� ������
        			�&nbsp;������� �<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $offer['project_id'])."?offer={$offer['id']}{$this->_addUrlParams('e', '&')}\">" . $offer['project_name'] . "</a>�.
        			<br />", array('header' => 'default', 'footer' => 'default'), array('login'=>$offer['to_login']));
        		$this->SmtpMail('text/html');  
                //++$count;   
            } else {
                
                $_blocked_txt = '';
                
                //���� ��� ������������ �������� � ��� �� ��������
                //�� �������� ����������� �� ������
                if($offer['kind'] == 4 && 
                   $offer['state'] == 1 && 
                   $offer['payed'] == 0) {
                    
                    $url_vacancy = sprintf('%s/public/?step=1&kind=4&public=%s&popup=1', $GLOBALS['host'], $offer['project_id']);
                    
                    $_blocked_txt = '
                        ��������� ������� �� �������������� ���� ������ �<a href="'
                            . $GLOBALS['host'] 
                            . getFriendlyURL("project", $offer['project_id']) 
                            . $this->_addUrlParams('e') . '">'
                            . $offer['project_name'] . '</a>�.
                        <br/>
                        <br/>
                        ------------
                        <br/>
                        ����� ������ �������� �����.
                        <br/>
                        ------------
                        <br/>
                        <br/>
                        ��� ����, ����� ������ ������ ����������� � ����� ����������� ������� �����������, ����������, 
                        ��������� � �������� � �������� �� ����������.
                        <br/>
                        <br/>
                        <a href="'.$url_vacancy.'">�������� ���������� ��������</a>
                    ';
                }
                
                
                $userlink = $GLOBALS["host"]."/users/".$offer['from_login'];
                if (!$offer['to_email'] || substr($offer['to_subscr'], 1, 1) != '1') continue; // ���� �� ����� ����������� ���������� �������
                $this->subject = "��������� ������� �� ������ �".html_entity_decode($offer['project_name'], ENT_QUOTES)."�";
                
                $body = empty($_blocked_txt)?"��������� <a href=\"{$userlink}\">{$offer['from_uname']}</a> <a href=\"{$userlink}\">{$offer['from_usurname']}</a> [<a href=\"{$userlink}\">{$offer['from_login']}</a>] "."<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $offer['project_id']).$this->_addUrlParams('e')."#freelancer_".$offer['user_id']."\">"."�������</a> �� �������������� ���� ������
                �<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $offer['project_id']).$this->_addUrlParams('e')."\">" . $offer['project_name'] . "</a>�.
                <br/>
                <br/>
                ------------
                <br/>
                ".html_entity_decode(strip_tags(input_ref(LenghtFormatEx($offer['description'], 300), 1)), ENT_COMPAT, "CP1251")."
                <br/>
                ------------":$_blocked_txt;
                $this->recipient = "{$offer['to_uname']} {$offer['to_usurname']} [{$offer['to_login']}] <{$offer['to_email']}>";
                $this->message   = $this->GetHtml($offer['to_uname'], $body, array('header' => 'default', 'footer' => 'sub_emp_projects'), array('login'=>$offer['to_login']));
                $this->SmtpMail('text/html');  
                //++$count;
            }
        }
        
        return $this->sended;
    }

	/**
     * ���������� ����������� ������ ������� � ����� ��������� �� �����, ����� ����������� �� ������ ������.
     *
     * @param   string|array    $ids        �������������� ������� ������ �������
     * @param   resource        $connect    ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return  integer                     ���������� ������������ �����������.
     */
    function NewPrjMessageOnOffer($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects_offers.php';
        $offers = new projects_offers();
        
        if (!($dialog = $offers->getNewPrjMessageOnOffer($ids, $connect))) return NULL;
        
        foreach($dialog as $offer) { 
            $project_name = $offer['project_name'];
            $project_id   = $offer['project_id'];
            $msg          = $offer['msg'];
            
            if($offer['usr_dialog'] == $offer['emp_uid']) {
				if (!$offer['frl_email'] || substr($offer['frl_subscr'], 4, 1) != '1') continue; // ���� �� ����� ����������� ���������� �������
                $this->subject = "����� ��������� �� ������� �" . html_entity_decode($project_name) . "�";
                
                //���� �� ����������� � �� ��� �� �������� �������� ��������� � �����������
                $emp_contact = '';
                if (isset($offer['is_view_contacts']) && $offer['is_view_contacts'] == 't') {
                    $userlink = $GLOBALS["host"]."/users/".$offer['emp_login'];
                    $emp_contact = "<a href=\"{$userlink}\">{$offer['emp_name']}</a> <a href=\"{$userlink}\">{$offer['emp_uname']}</a> [<a href=\"{$userlink}\">{$offer['emp_login']}</a>] ";
                }
                
                $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
                
                $body = "�������� {$emp_contact}�������(�) ��� ����� ��������� �� ������� �<a href='{$GLOBALS['host']}".getFriendlyURL("project", $project_id).$this->_addUrlParams('f')."#freelancer_".$offer['frl_uid']."'>{$project_name}</a> �.
                        <br/><br/>
                        ------
                        <br/>
                        ".(html_entity_decode(strip_tags(input_ref(LenghtFormatEx($msg, 300), 1)), ENT_COMPAT, "CP1251")."\n")."
                        <br/>
                        ------";
                $this->recipient = "{$offer['frl_name']} {$offer['frl_uname']} [{$offer['frl_login']}] <".$offer['frl_email'].">";
                $this->message   = $this->GetHtml($offer['frl_name'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$offer['frl_login']));
                
				$this->SmtpMail('text/html');
                //++$count;   
            } else {
				if (!$offer['emp_email'] || substr($offer['emp_subscr'], 4, 1) != '1') continue; // ���� �� ����� ����������� ���������� �������
				$this->subject = "����� ��������� �� ������� �" . html_entity_decode($project_name) . "�";
				$userlink = $GLOBALS["host"]."/users/".$offer['frl_login'];
                
                $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
                
                $body = "��������� <a href=\"{$userlink}\">{$offer['frl_name']}</a> <a href=\"{$userlink}\">{$offer['frl_uname']}</a> [<a href=\"{$userlink}\">{$offer['frl_login']}</a>] �������(�) ��� <a href='{$GLOBALS['host']}".getFriendlyURL("project", $project_id).$this->_addUrlParams('e')."#comment".$offer['spoiler_id']."'>" . "����� ��������� </a> �� ��������������� ���� ������� �<a href='{$GLOBALS['host']}".getFriendlyURL("project", $project_id).$this->_addUrlParams('e')."'>{$project_name}</a>�.
                        <br/><br/>
                        ------
                        <br/>
                        ".(html_entity_decode(strip_tags(input_ref(LenghtFormatEx($msg, 300), 1)), ENT_COMPAT, "CP1251")."\n")."
                        <br/>
                        ------";
                $this->recipient = "{$offer['emp_name']} {$offer['emp_uname']} [{$offer['emp_login']}] <".$offer['emp_email'].">";
                $this->message = $this->GetHtml($offer['emp_name'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$offer['emp_login']));
				$this->SmtpMail('text/html');
                //++$count;
            }
        }
        
        return $this->sended;
    }
    
    /**
     * ���������� ����������� � ���������� � ���������.
     *
     * @param   integer    $from_id        ID ������������ ��� ���������
     * @param   integer    $target_id      ID ������������ ���� ���������
     * @return  integer                    ���������� ������������ �����������
     */
    function addTeamPeople($from_id, $target_id) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        
        $f_user = new users();
        $t_user = new users();
        
        $f_user->GetUserByUID($from_id);
        $t_user->GetUserByUID($target_id);

        if (!$t_user->email || substr($t_user->subscr, 9, 1) != '1' || $t_user->is_banned == '1') return 0; // ���� �� ����� ����������� ���������� �������
        $this->subject = "��� �������� � ���������� �� FL.ru";
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";		
        		
        if(is_emp($f_user->role)) $name = "������������"; 
        else $name = "���������";
            
        $message = $name." <a href='{$GLOBALS['host']}/users/{$f_user->login}/{$this->_addUrlParams('b')}' target='_blank'>{$f_user->uname} {$f_user->usurname} [{$f_user->login}]</a>  ������� ��� � ���������� �� ����� ������ �������� �� <a href=\"{$GLOBALS['host']}/{$this->_addUrlParams('b')}\">FL.ru</a>. 
        <br/><br/>
        --------
        <br/>
        <a href=\"{$GLOBALS['host']}/users/{$f_user->login}/info/{$this->_addUrlParams('b')}\">����������</a><br/>
        --------
        <br/><br/>
        ";
     	$this->message = $this->GetHtml($t_user->uname, $message, array('header' => 'default', 'footer' => 'default'), array('login'=>$t_user->login));  
        $this->send('text/html');
        
        return $this->sended;
    }
    
    /**
     * ���������� ����������� � �������� �� ���������.
     *
     * @param   integer    $from_id         ID ������������ ��� �������
     * @param   integer    $target_id       ID ������������ ���� �������
     * @return  integer                     ���������� ������������ �����������.
     */
    function delTeamPeople($from_id, $target_id) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        
        $f_user = new users();
        $t_user = new users();
        
        $f_user->GetUserByUID($from_id);
        $t_user->GetUserByUID($target_id);
            
        if (!$t_user->email || substr($t_user->subscr, 9, 1) != '1' || $t_user->is_banned == '1') return; // ���� �� ����� ����������� ���������� �������
        $this->subject = "��� ������� �� ����������� �� FL.ru";
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";		
        		
        if(is_emp($f_user->role)) $name = "������������"; 
        else $name = "���������";
            
        $message = $name." <a href='{$GLOBALS['host']}/users/{$f_user->login}/{$this->_addUrlParams('b')}' target='_blank'>{$f_user->uname} {$f_user->usurname} [{$f_user->login}]</a>  ������(�) ��� �� ����������� �� ����� ������ �������� �� ����� <a href=\"{$GLOBALS['host']}/{$this->_addUrlParams('b')}\">FL.ru</a><br/><br/>";
            
        $this->message = $this->GetHtml($t_user->uname, $message, array('header' => 'default', 'footer' => 'default'), array('login'=>$t_user->login));  
        $this->send('text/html');  
        
        return $this->sended; 
    }
    
    /**
     * �������� ��������� ���������� � ���������� ����������� � ��� ����������� � ��������
     *
     * @param   string|array    $ids        �������������� ����� ������������
     * @param   resource        $connect    ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return  integer                     ���������� ������������ �����������.
     */
    function ContestNewComment($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';
        
        $contest = new contest(0, 0);
		if (!($comments = $contest->getContestNewComment($ids, $connect))) return NULL;
        
        foreach($comments as $comment) {
            // ������ ������������ ��������, ���� �������� � �� �� ������� ������� 
            if ( substr($comment['p_subscr'], 8, 1) == '1' 
                && $comment['p_uid'] != $comment['uid'] 
                && $comment['p_email'] 
                && $comment['p_banned'] == '0' 
            ) {
                $this->subject = '����������� � ����� �������� �'.htmlspecialchars_decode($comment['project_name'], ENT_QUOTES).'� �� ����� FL.ru';
                
                $comment['project_name'] = htmlspecialchars($comment['project_name'], ENT_QUOTES, 'CP1251', false);
                
                $body = '<a href="'.$GLOBALS['host'].'/users/'.$comment['login'].$this->_addUrlParams('e').'">'.$comment['uname'].' '.$comment['usurname'].'</a> [<a href="'.$GLOBALS['host'].'/users/'.$comment['login'].$this->_addUrlParams('e').'">'.$comment['login'].'</a>] 
                �������(�) ����������� �� <a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).'?offer='.$comment['offer_id'].$this->_addUrlParams('e', '&').'#offer-'.$comment['offer_id'].'">������</a> 
                � ������������� �<a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).$this->_addUrlParams('e').'">'.$comment['project_name'].'</a>�. 
                ������������ � ������ <a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).'?comm='.$comment['comment_id'].$this->_addUrlParams('e', '&').'#comment-'.$comment['comment_id'].'">������������</a> ����� �� �������� ��������.';
                
                $this->message   = $this->GetHtml( $comment['p_uname'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['p_login']) );
                $this->recipient = $comment['p_uname']." ".$comment['p_usurname']." [".$comment['p_login']."] <".$comment['p_email'].">";
                
                $this->SmtpMail( 'text/html' );
            }
            
            // ������ ������ �����������, ���� �������� � �� �� ������� ������� 
            if ( substr($comment['o_subscr'], 8, 1) == '1' 
                && $comment['o_uid'] != $comment['uid'] 
                && $comment['o_email'] 
                && $comment['o_banned'] == '0' 
            ) {
            	$this->subject = '���� ������ � �������� �'.htmlspecialchars_decode($comment['project_name'], ENT_QUOTES).'� �����������������';
            	
                $comment['project_name'] = htmlspecialchars($comment['project_name'], ENT_QUOTES, 'CP1251', false);
                
                $body = '<a href="'.$GLOBALS['host'].'/users/'.$comment['login'].$this->_addUrlParams('f').'">'.$comment['uname'].' '.$comment['usurname'].'</a> [<a href="'.$GLOBALS['host'].'/users/'.$comment['login'].$this->_addUrlParams('f').'">'.$comment['login'].'</a>] 
                ����������������(a) ���� <a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).'?offer='.$comment['offer_id'].$this->_addUrlParams('f', '&').'#offer-'.$comment['offer_id'].'">������</a> 
                �&nbsp;�������� �<a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).$this->_addUrlParams('f').'">' . $comment['project_name'] . '</a>�.
                ������������ � ������ <a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).'?comm='.$comment['comment_id'].$this->_addUrlParams('f', '&').'#comment-'.$comment['comment_id'].'">������������</a> ����� �� �������� ��������.';
                
                $this->message   = $this->GetHtml( $comment['o_uname'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['o_login']) );
                $this->recipient = $comment['o_uname']." ".$comment['o_usurname']." [".$comment['o_login']."] <".$comment['o_email'].">";
                
                $this->SmtpMail( 'text/html' );
            }
            
            // ������ ������ ������������� �����������, ���� ����� 
            if ( substr($comment['m_subscr'], 8, 1) == '1' 
                && $comment['m_uid'] != $comment['uid'] 
                && $comment['m_uid'] != $comment['p_uid'] 
                && $comment['m_uid'] != $comment['o_uid'] 
                && $comment['m_email'] 
                && $comment['m_banned'] == '0' 
            ) {
            	$this->subject = '����������� � �������� "'.htmlspecialchars_decode($comment['project_name'], ENT_QUOTES).'" �� ����� FL.ru';
            	
                $comment['project_name'] = htmlspecialchars($comment['project_name'], ENT_QUOTES, 'CP1251', false);
                
            	$body = '<a href="'.$GLOBALS['host'].'/users/'.$comment['login'].$this->_addUrlParams('b').'">'.$comment['uname'].' '.$comment['usurname'].'</a> [<a href="'.$GLOBALS['host'].'/users/'.$comment['login'].$this->_addUrlParams('b').'">'.$comment['login'].'</a>] 
                �������(�) ��� ����������� � �������� <a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).$this->_addUrlParams('b').'">"'.$comment['project_name'].'"</a>. 
                <br/>�� ������ ��������� ������ <a href="'.$GLOBALS['host'].getFriendlyURL("project", $comment['project_id']).'?comm='.$comment['comment_id'].$this->_addUrlParams('b').'#comment-'.$comment['comment_id'].'">�����������</a>.';
                
                $this->message   = $this->GetHtml( $comment['m_uname'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['m_login']) );
                $this->recipient = $comment['m_uname']." ".$comment['m_usurname']." [".$comment['m_login']."] <".$comment['m_email'].">";
                
                $this->SmtpMail( 'text/html' );
            }
        }
        
        return $this->sended;    
    }
    
    /**
     * ���������� ����������� � ����� ������.
     *
     * @param   string|array    $ids        �������������� ����� �������
     * @param   resource        $connect    ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return  integer                     ���������� ������������ �����������.
     */
    function NewOpinion($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/opinions.php';
        
        $opin = new opinions(0, 0);
		if (!($opinions = $opin->getNewOpinion($ids, $connect))) return NULL;
        
        foreach($opinions as $opinion) {
            if (!$opinion['t_email'] || substr($opinion['t_subscr'],3,1) != '1' || $opinion['t_banned'] == '1') continue; // ���� �� ����� ����������� ���������� �������
            
            switch ($opinion['rating']) {
                case 0:
                    $type_text = "�����������";
                    break;
                case 1:
                    $type_text = "�������������";
                    break;
                case -1:
                    $type_text = "�������������";
                    break;
            }
            
            if (substr($opinion['f_role'],0,1)=='1') { $path= "/users/".$opinion["t_login"]."/opinions/"; }
            else { $path= "/users/".$opinion["t_login"]."/opinions/?from=frl"; }

            $body = "������������ <a href='{$GLOBALS['host']}/users/{$opinion['f_login']}{$this->_addUrlParams('b')}'>".$opinion["f_uname"]." ".$opinion["f_usurname"]."</a> [<a href='{$GLOBALS['host']}/users/{$opinion['f_login']}{$this->_addUrlParams('b')}'>".$opinion["f_login"]."</a>]
�������(�) $type_text ����� � ���.<br />
�� ������ ������������ � <a href='{$GLOBALS['host']}{$path}{$this->_addUrlParams('b', '&')}'>����� �������</a> �� �������� ������ ��������.";
            
            $this->message = $this->GetHtml($opinion["t_uname"], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$opinion['t_login']));
            $this->from = "FL.ru <administration@fl.ru>";
            $this->subject = "����� ����� �� FL.ru";
            $this->recipient = "{$opinion['t_uname']} {$opinion['t_usurname']} [{$opinion['t_login']}] <".$opinion['t_email'].">";
            
            $this->SmtpMail('text/html');
        }
        
        return $this->sended;    
    }
    
   /**
     * ���������� ����������� � �������������� ������.
     *
     * @param   string|array    $ids        ��������������  �������
     * @param   resource        $connect    ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return  integer                     ���������� ������������ �����������.
     */
    function EditOpinion($ids, $connect = NULL) {
         require_once $_SERVER['DOCUMENT_ROOT'].'/classes/opinions.php';
         
         $opin = new opinions(0, 0);
         if (!($opinions = $opin->getNewOpinion($ids, $connect))) return NULL;
         
         foreach($opinions as $opinion) {
            if (!$opinion['t_email'] || substr($opinion['t_subscr'],3,1) != '1' || $opinion['t_banned'] == '1') continue; // ���� �� ����� ����������� ���������� �������
            
            $path= "/users/{$opinion['t_login']}/opinions/?from=" . ( substr($opinion['f_role'],0,1)=='1' ? 'emp' : 'frl' ); 
            
            if ( !$opinion['modified_id'] || $opinion['modified_id'] == $opinion['f_uid'] ) { // ����� ����������� �����
                switch ($opinion['rating']) {
                    case 0:
                        $type_text = "�����������";
                        break;
                    case 1:
                        $type_text = "�������������";
                        break;
                    case -1:
                        $type_text = "�������������";
                        break;
                }
    
                $body = "������������ <a href='{$GLOBALS['host']}/users/{$opinion['f_login']}{$this->_addUrlParams('b')}'>".$opinion["f_uname"]." ".$opinion["f_usurname"]."</a> [<a href='{$GLOBALS['host']}/users/{$opinion['f_login']}{$this->_addUrlParams('b')}'>".$opinion["f_login"]."</a>]
�������(�) $type_text ����� � ���.<br />
�� ������ ��������� ��� �� �������� ������ �������� - <a href='{$GLOBALS['host']}{$path}{$this->_addUrlParams('b', '&')}'>".$GLOBALS["host"].$path."</a>";
            }
            else { // ����� ����������� �����
                $body = "��������� �������������� ����� �� ���������� ������.
<br />
<br />
�� ������ ��������� ��� �� �������� ������ �������� - <a href='{$GLOBALS['host']}{$path}{$this->_addUrlParams('b', '&')}'>".$GLOBALS["host"].$path."</a>";
            }
            
            $this->message   = $this->GetHtml($opinion["t_uname"], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$opinion['t_login']));
            $this->from      = "FL.ru <administration@fl.ru>";
            $this->subject   = "�������������� ������ �� FL.ru";
            $this->recipient = "{$opinion['t_uname']} {$opinion['t_usurname']} [{$opinion['t_login']}] <".$opinion['t_email'].">";
            
            $this->send( 'text/html' );
        }
        
        return $this->sended;  
    }

    /**
     * ���������� ���������� ��������� �� ������
     *
     * @param string|array $ids
     * @param resource $connect
     * @return  integer ���������� ������������ �����������.
     */
    function ProjectsOfferRefused($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects_offers.php';

        $offers = new projects_offers();
        if (!($data = $offers->getRefusedProjectOffers($ids, $connect))) return NULL;

        foreach($data as $offer) {
            if (substr($offer['subscr'], 4, 1) != '1' || $offer['is_banned'] == '1') continue; // ���� �� ����� ����������� ���������� �������

            $uname = $offer['uname'];
            $usurname = $offer['usurname'];
            $login = $offer['login'];
            $email = $offer['email'];
            $project_name = $offer['project_name'];

            $this->subject = "�� ������� �"  . html_entity_decode($project_name) . "� ��� ������� �����";
            
            $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
            
            $body = "� ���������, �� �������� ����� �� ��������� �� ������� �<a href=\"".$GLOBALS['host'] . getFriendlyURL("project", $offer['project_id']).$this->_addUrlParams('f')."\">".$project_name."</a>�.";
            
            $this->recipient = "$uname $usurname [$login] <".$email.">";
            $this->message = $this->GetHtml($uname, $body, array('header'=>'default', 'footer'=>"default"), array('login' => $login));
            $this->SmtpMail('text/html');

        }
        return $this->sended;
    }

    /**
     * ����������� ���������, ���� ��������/����������/����������� ������������ �� �����
     * 
     * ������/�������/��� - ����� 5 ����� ����������� @see pmail::_ExecutorCandidateBanMail
     * 
     * @param  string|array $ids �������������� ��������������� �������������
     * @param  resource $connect ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����
     * @return bool true - �����, false - ������
     */
    function ExecutorCandidateBanMail( $ids, $connect = NULL ) {
        if ( empty($ids) ) {
            return false;
        }
        $mRes = employer::GetEmployersBlockedCandidates($ids);
        if ( !$GLOBALS['DB']->error && pg_num_rows($mRes) ) {
            $nCurCnf    = 1;
            $aRecipient = array();
            
            while ( $aOne = pg_fetch_assoc($mRes) ) {
                
                $aOne['name'] = htmlspecialchars($aOne['name'], ENT_QUOTES, 'CP1251', false);
                
                if ( $nCurCnf != $aOne['cnf'] ) {
                    if ( $aRecipient ) {
                        $this->_ExecutorCandidateBanMail( $nCurCnf, $aRecipient );
                        $aRecipient = array();
                    }
                    
                    $nCurCnf = $aOne['cnf'];
                }
                
                if ( $aOne['lnk'] == 'project' ) {
                    $sLink = $GLOBALS['host'] . getFriendlyURL( 'project', $aOne['id'] );
                }
                elseif ( $aOne['lnk'] == 'sbr' ) {
                    $sLink = $GLOBALS['host'] . '/' . sbr::NEW_TEMPLATE_SBR . '/?id=' . $aOne['id'];
                }
                
                $sUlink = $GLOBALS['host'] . '/users/' . $aOne['login'];
                $sUname = $aOne['uname'] . ' ' . $aOne['usurname'] . ' [' . $aOne['login'] . ']';
                
                $aRecipient[] = array(
                    'email' => $aOne['e_name']." ".$aOne['e_surname']." <".$aOne['email'].">",
                    'extra' => array( 'name' => $aOne['name'], 'link' => $sLink, 'u_link' => $sUlink, 'u_name' => $sUname, 'USER_LOGIN' => $aOne['e_login'] )
                );
            }
            
            if ( $aRecipient ) {
                $this->_ExecutorCandidateBanMail( $nCurCnf, $aRecipient );
            }
        }
    }

    /**
     * ����������� ���������, ���� ��������/����������/����������� ������������ �� �����
     * 
     * ��������������� ������� @see pmail::ExecutorCandidateBanMail
     * 
     * @param int $nCnf ����� ����������� �� 1 �� 5 
     * @param array $aRecipient ������ ������ ��� �����������
     */
    function _ExecutorCandidateBanMail( $nCnf = 0, $aRecipient = array() ) {
        if ( !$nCnf || !$aRecipient ) return false;
        
        $aCnf = array(
            // ����������� � ������� 
            1 => array('sujb' => '����������� ������ ������� ������������ �� FL.ru', 'msg' => '� ������� <a href="%link%'.$this->_addUrlParams('e').'">%name%</a> �� ������� � �������� ����������� ������������ <a href="%u_link%'.$this->_addUrlParams('e').'">%u_name%</a>. �������� ���, ��� ������ ������������ ��� ������������ �� FL.ru.<br />'),
            // �������� � �������
            2 => array('sujb' => '�����������, ������������ ��� �������� � ����� ������� �� FL.ru, ������������', 'msg' => '� ������� <a href="%link%'.$this->_addUrlParams('e').'">%name%</a> �� ������� � �������� ��������� ������������ <a href="%u_link%'.$this->_addUrlParams('e').'">%u_name%</a>. �������� ���, ��� ������ ������������ ��� ������������ �� FL.ru.<br />'),
            // �������� � ��������, � ������ ��� ��� �����������
            3 => array('sujb' => '�����������, ������������ ��� �������� � ����� �������� �� FL.ru, ������������', 'msg' => '� �������� <a href="%link%'.$this->_addUrlParams('e').'">%name%</a> �� ������� � �������� ��������� ������������ <a href="%u_link%'.$this->_addUrlParams('e').'">%u_name%</a>. �������� ���, ��� ������ ������������ ��� ������������ �� FL.ru.<br />'),
            // ���������� � �������� (��� ��������� ��� �� �����)
            4 => array('sujb' => '���������� ��������, �������������� ���� �� FL.ru, ������������', 'msg' => '� �������� <a href="%link%'.$this->_addUrlParams('e').'">%name%</a> �� ������� � �������� ���������� ������������ <a href="%u_link%'.$this->_addUrlParams('e').'">%u_name%</a>. �������� ���, ��� ������ ������������ ��� ������������ �� FL.ru.<br />'),
            // ����������� � ������ ��� �����
            5 => array('sujb' => '����������� � ���������� ������ ������������ �� FL.ru', 'msg' => '�� ��������� ���������� ������ <a href="%link%'.$this->_addUrlParams('e').'">%name%</a> � ������������� <a href="%u_link%'.$this->_addUrlParams('e').'">%u_name%</a>. �������� ���, ��� ������ ������������ ��� ������������ �� FL.ru.<br />')
        );
        
        $this->subject   = $aCnf[ $nCnf ]['sujb'];
    	$this->recipient = array();
    	$this->message   = $this->GetHtml( 
    	   "%USER_LOGIN%", 
    	   $aCnf[ $nCnf ]['msg'] . '<br />�� ���� ����������� �������� �� ������ ���������� � ���� <a href="https://feedback.fl.ru/'.$this->_addUrlParams('e', '?').'">������ ���������</a>.', 
    	   array( 'header' => 'default', 'footer' => 'simple' ), 
    	   array( 'target_footer' => true ) 
        );
	    
        $sMsgId = $this->send( 'text/html' );
        
        $this->recipient = $aRecipient;
        
        $this->bind( $sMsgId );
        $this->recipient = array();
    }

    /**
     * ���������� ���������� ��������� � ���, ��� ��� ������� ����������
     *
     * @param string|array $ids
     * @param resource $connect
     * @return  integer ���������� ������������ �����������.
     */
    function ProjectsOfferSelected($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects_offers.php';

        $offers = new projects_offers();
        if (!($data = $offers->getSelectedProjectOffers($ids, $connect))) return NULL;

        foreach($data as $offer) {
            //if (substr($offer['subscr'], 4, 1) != '1' || $offer['is_banned'] == '1') continue; // ���� �� ����� ����������� ���������� �������

            $uname = $offer['uname'];
            $usurname = $offer['usurname'];
            $login = $offer['login'];
            $email = $offer['email'];
            $project_name = $offer['project_name'];
            $project_id = $offer['project_id'];

            $this->subject = "��� ������� ���������� � ������� �" . html_entity_decode($project_name)."�";

            $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
            
            $body  = "��� ������� ���������� � ������� �<a href=\"".$GLOBALS['host'] . getFriendlyURL("project", $project_id) . $this->_addUrlParams('f') . "\">".$project_name."</a>�.";
            $body .= "<br/><br/>������ ��� �����!<br/>";

            $this->recipient = "$uname $usurname [$login] <".$email.">";
            $this->message = $this->GetHtml($uname, $body, array('header'=>'simple', 'footer'=>'frl_simple_projects'), array('login' => $offer['login']));
            $this->SmtpMail('text/html');

        }
        return $this->sended;
    }

    /**
     * ���������� ���������� ��������� � ���, ��� ��� ������� ������������
     *
     * @param string|array $ids
     * @param resource $connect
     * @return  integer ���������� ������������ �����������.
     */
    function ProjectsExecSelected($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects.php';

        $projects = new projects();
        if (!($data = $projects->GetExecProjects($ids, $connect))) return NULL;

        foreach($data as $proj) {
            //if (substr($proj['subscr'], 4, 1) != '1' || $proj['is_banned'] == '1') continue; // ���� �� ����� ����������� ���������� �������

            $uname = $proj['uname'];
            $usurname = $proj['usurname'];
            $login = $proj['login'];
            $email = $proj['email'];
            $project_name = $proj['project_name'];
            $project_id = $proj['project_id'];

            $this->subject = "��� ������� ������������ � ������� �" . html_entity_decode($project_name)."�";
            
            $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
            
            $body = "��� ������� ������������ � ������� �<a href=\"".$GLOBALS['host'] . getFriendlyURL("project", $project_id) . $this->_addUrlParams('f') . "\">".$project_name."</a>�.";

            $this->recipient = "$uname $usurname [$login] <".$email.">";
            $this->message = $this->GetHtml($uname, $body, array('header'=>'simple', 'footer' => 'frl_simple_projects'), array('login'=>$login));
            echo $this->message;
            $this->SmtpMail('text/html');

        }
        return $this->sended;
    }



    /**
     * �������� ��������� ��������������� � �������� �������������
     *
     * @param string|array $ids ������������
     * @param resource $connect
     * @return  integer ���������� ������������ �����������.
     */
    function ContestUserBlocked($ids, $connect = NULL) {

        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';

        if (!($data = contest::getContestsBlockedUsers($ids, $connect))) return NULL;

        foreach($data as $proj) {
            if (!$proj['email'] || substr($proj['subscr'], 8, 1) != '1') continue;

            $uname = $proj['uname'];
            $usurname = $proj['usurname'];
            $login = $proj['login'];
            $email = $proj['email'];
            $project_name = $proj['project_name'];
            $project_id = $proj['project_id'];
            $userlink = $GLOBALS["host"]."/users/".$proj['emp_login'];
            $this->recipient = "$uname $usurname [$login] <".$email.">";
            $this->subject = '��� ������������� � �������� �'.htmlspecialchars_decode($project_name, ENT_QUOTES).'�';
            $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
            $this->message = $this->GetHtml($uname, "
       �������� <a href=\"{$userlink}\">{$proj['emp_name']} {$proj['emp_uname']}</a> [<a href=\"{$userlink}\">{$proj['emp_login']}</a>] ������������(�) ���
       �&nbsp;�������� �<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $project_id).$this->_addUrlParams('f')."\">".$project_name."</a>�.
       � ���������, ������ �� �� ������ ���������� ���� ������� � ���� ��������.<br />
            ", array('header' => 'default', 'footer' => 'default'), array('login'=>$login));
            $this->SmtpMail('text/html');
        }

        return $this->sended;
    }

    /**
     * �������� ��������� ���������������� � �������� �������������
     *
     * @param string|array $ids ������������
     * @param resource $connect
     * @return  integer ���������� ������������ �����������.
     */
    function ContestUserUnblocked($ids, $connect = NULL) {

        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';

        if (!($data = contest::getContestsUnblocked($ids, $connect))) return NULL;

        foreach($data as $row) {
            if (!$row['user']['email'] || substr($row['user']['subscr'], 8, 1) != '1'
                || !isset($row['projects'])) continue;
            
            $user = $row['user'];

            $uname = $user['uname'];
            $usurname = $user['usurname'];
            $login = $user['login'];
            $email = $user['email'];

            $this->recipient = "$uname $usurname [$login] <".$email.">";

            foreach($row['projects'] as $proj) {
                $project_name = $proj['project_name'];
                $project_id = $proj['project_id'];

                $this->subject = '��� �������������� � �������� �'.htmlspecialchars_decode($project_name, ENT_QUOTES).'�';
                $userlink = $GLOBALS["host"]."/users/".$proj['emp_login'];
                $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
                $this->message = $this->GetHtml($uname, "
                   �������� <a href=\"{$userlink}\">{$proj['emp_name']} {$proj['emp_uname']}</a> [<a href=\"{$userlink}\">{$proj['emp_login']}</a>] �������������(�) ���
                   �&nbsp;�������� �<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $project_id).$this->_addUrlParams('f')."\">".$project_name."</a>�.
                   ������ �� ������ ���������� ���� ������� � ���� ��������.
                   <br /><br />
                   ������ �����!
                   <br/>", 
                   array('header' => 'default', 'footer' => 'frl_subscr_projects'), array('login'=>$login));
                $this->SmtpMail('text/html');
            }
        }

        return $this->sended;
    }

    /**
     * �������� ��������� �������������, ������� ���������� ����������� � ���������
     *
     * @param string|array $ids �� ����������� �������������
     * @param resource $connect
     * @return  integer ���������� ������������ �����������.
     */
    function ContestAddCandidate($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';

        if (!($data = contest::getSelectedOffers($ids, $connect))) return NULL;

        foreach($data as $proj) {
            if (!$proj['email'] || substr($proj['subscr'], 8, 1) != '1' || $proj['is_banned'] == '1') continue;

            $uname = $proj['uname'];
            $usurname = $proj['usurname'];
            $login = $proj['login'];
            $email = $proj['email'];
            $project_name = $proj['project_name'];
            $project_id = $proj['project_id'];

            $this->recipient = "$uname $usurname [$login] <".$email.">";
            $this->subject = '��� �������� � ��������� � ���������� � �������� �'.htmlspecialchars_decode($project_name, ENT_QUOTES).'�';
            $userlink = $GLOBALS["host"]."/users/".$proj['emp_login'];
            $project_name = htmlspecialchars($project_name, ENT_QUOTES, 'CP1251', false);
            $this->message = $this->GetHtml($uname, "�������� <a href=\"{$userlink}\">{$proj['emp_name']} {$proj['emp_uname']}</a> [<a href=\"{$userlink}\">{$proj['emp_login']}</a>] �������(�) ��� � ��������� � ���������� �&nbsp;�������� �<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $project_id).$this->_addUrlParams('f')."\">".$project_name."</a>�.
               �� ������ ������� � ����� <a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $project_id)."?offer={$proj['id']}{$this->_addUrlParams('f', '&')}#offer-{$proj['id']}\">������</a>.
               <br /><br />
               ������ ��� �����!
               <br/>
              ", array('header' => 'default', 'footer' => 'frl_subscr_projects'), array('login'=>$login));
            $this->SmtpMail('text/html');
        }

        return $this->sended;
    }

    /**
     * �������� ��������� ����������� ��������
     *
     * @param string|array $ids �� ����������� �������������
     * @param resource $connect
     * @return  integer ���������� ������������ �����������.
     */
    function ContestWinners($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';

        if (!($data = contest::getWinnerOffers($ids, $connect))) return NULL;

        foreach($data as $proj) {
            if (!$proj['email'] || substr($proj['subscr'], 8, 1) != '1' || $proj['is_banned'] == '1') continue;

            $str = array(1 => '������', 2 => '������', 3 => '������');

            $this->recipient = "{$proj['uname']} {$proj['usurname']} [{$proj['login']}] <{$proj['email']}>";
            $userlink = $GLOBALS["host"]."/users/".$proj['emp_login'];
            $this->subject = '��� �������� ����� �� ����������� �������� �'.htmlspecialchars_decode($proj['project_name'], ENT_QUOTES).'�';
            
            $proj['project_name'] = htmlspecialchars($proj['project_name'], ENT_QUOTES, 'CP1251', false);
            
            $this->message = $this->GetHtml($proj['uname'], "����������� ���!<br/><br/>
                �������� <a href=\"{$userlink}\">{$proj['emp_name']}</a> <a href=\"{$userlink}\">{$proj['emp_uname']}</a> [<a href=\"{$userlink}\">{$proj['emp_login']}</a>] �������(a) ��� ����� �� ����������� �&nbsp;�������� �<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $proj['project_id']).$this->_addUrlParams('f')."\">".$proj['project_name']."</a>�. 
                �� ������ ".($str[$proj['position']]? $str[$proj['position']]: $position)." �����. �����������!
                <br /><br/>
                �� ������ ������� � ����� <a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $proj['project_id'])."?offer={$proj['id']}{$this->_addUrlParams('f', '&')}#offer-{$proj['id']}\">������</a>.
                <br />
                ", array('header' => 'default', 'footer' => 'frl_subscr_projects'), array('login'=>$proj['login']));
            $this->SmtpMail('text/html');
        }
        
        return $this->sended;
    }


    /**
     * �������� ��������� � ���, ��� ������/������� �����������
     *
     * @param string|array $ids �� ��������
     * @param resource $connect
     * @return  integer ���������� ������������ �����������.
     */
    function ProjectPosted($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";

        if (!($data = projects::getProjects4Sending($ids, $connect))) return NULL;

        foreach($data as $prj) {

            $prj['name'] = htmlspecialchars($prj['name'], ENT_QUOTES, 'CP1251', false);
            
            if($prj['kind'] == 7) {
                //�������
                $this->message = $this->GetHtml($prj['uname'],
                "��� ������� �<a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('e')."'>{$prj['name']}</a>� ��� ����������� �� ����� FL.ru.
                ���������� ���, ��� ������������ � <a href='{$GLOBALS['host']}/payed-emp/{$this->_addUrlParams('e')}'>��������� PRO</a> �������� �� ������� ������� �����.",
                array('header'=>'simple', 'footer'=>'simple'));
            } else {
                //������
                $this->message = $this->GetHtml($prj['uname'],
                "��� ������ �<a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('e')."'>{$prj['name']}</a>� ��� ����������� �� ����� FL.ru.", 
                array('header'=>'simple', 'footer'=>($prj['prefer_sbr']=='t' ? 'simple' : 'simple_projects')), array('project' => $prj));
            }

            $this->recipient = "{$prj['uname']} {$prj['usurname']} [{$prj['login']}] <". $prj['email'] .">";
            $item_name = ($prj['kind'] == 7 ? '�������' : '������' ) . " �" . html_entity_decode($prj['name'], ENT_QUOTES)."�"; 
            $this->subject = "��� $item_name ����������� �� FL.ru";
            $this->SmtpMail('text/html');
        }


        
        return $this->sended;
    }
    
    /**
     * ���������� ����������� �� �������� ������.
     *
     * @param   string|array    $ids        �������������� ����� �������
     * @param   resource        $connect    ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return  integer                     ���������� ������������ �����������.
     */
    function DeleteOpinion($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        
        $f_user = new users();
        $t_user = new users();
        
        foreach($ids as $id) {
            list($from_id, $to_id, $type) = explode("|", $id);
            $f_user->GetUserByUID($from_id);
            $t_user->GetUserByUID($to_id);
            switch ($type) {
                case "0":
                    $type_text = "�����������";
                    break;
                case "1":
                    $type_text = "�������������";
                    break;
                case "-1":
                    $type_text = "�������������";
                    break;
            }
            
            
            $from_user = array("usurname"=>$f_user->usurname, "uname"=>$f_user->uname, "login"=>$f_user->login, "photo"=>$f_user->photo); 
            $to_user   = array("usurname"=>$t_user->usurname, "uname"=>$t_user->uname, "login"=>$t_user->login, "photo"=>$t_user->photo); 
            $email     = $t_user->email; 
            $role      = $f_user->role;  
            $subscr    = $t_user->subscr; 
            
            if (substr($subscr, 3, 1) != '1' || $t_user->is_banned == '1') continue; // ���� �� ����� ����������� ���������� �������
            
            if (substr($role,0,1)=='1') { $path= "/users/".$to_user["login"]."/opinions/"; }
            else { $path= "/users/".$to_user["login"]."/opinions/?from=frl"; }
    
            /*
            $message = "������������ <a href='{$GLOBALS['host']}/users/{$from_user['login']}{$this->_addUrlParams('b')}'>".$from_user["uname"]." ".$from_user["usurname"]."</a> [<a href='{$GLOBALS['host']}/users/{$from_user['login']}{$this->_addUrlParams('b')}'>".$from_user["login"]."</a>]
    ������(a) ���� $type_text ����� �� ������ �������� ��� �� ��� ����� ��-�� ���������� ��� �������� �������� ������������.";
             */
            
            $message = "������������ <a href='{$GLOBALS['host']}/users/{$from_user['login']}{$this->_addUrlParams('b')}'>".$from_user["uname"]." ".$from_user["usurname"]."</a> [<a href='{$GLOBALS['host']}/users/{$from_user['login']}{$this->_addUrlParams('b')}'>".$from_user["login"]."</a>]
    ��� ������������ �� ��������� ������ ����� FL.ru, � ��� ����� �����.";

            
            $this->message = $this->GetHtml($to_user['uname'], $message, array('header' => 'default', 'footer' => 'default'), array('login'=>$to_user['login']));
    
            $this->recipient = $to_user["uname"]." ".$to_user["usurname"]." [".$to_user["login"]."] <".$email.">";
            $this->subject = "����� ����� �� FL.ru";
    
            $this->SmtpMail('text/html');
        }
        
        return $this->sended; 
    }

    /**
     * ���������� ����������� � ������������� ������.
     *
     * @param   string|array    $ids        �������������� ����� �������
     * @param   resource        $connect    ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return  integer                     ���������� ������������ �����������.
     */
    function RestoreOpinion($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        
        $f_user = new users();
        $t_user = new users();
        
        foreach($ids as $id) {
            list($from_id, $to_id) = explode("|", $id);
            $f_user->GetUserByUID($from_id);
            $t_user->GetUserByUID($to_id);
            
            $from_user = array("usurname"=>$f_user->usurname, "uname"=>$f_user->uname, "login"=>$f_user->login, "photo"=>$f_user->photo); 
            $to_user   = array("usurname"=>$t_user->usurname, "uname"=>$t_user->uname, "login"=>$t_user->login, "photo"=>$t_user->photo); 
            $email     = $t_user->email; 
            $role      = $f_user->role;  
            $subscr    = $t_user->subscr; 
            
            if (substr($subscr, 3, 1) != '1' || $t_user->is_banned == '1') continue; // ���� �� ����� ����������� ���������� �������
            
            if (substr($role,0,1)=='1') { $path= "/users/".$to_user["login"]."/opinions/"; }

            else { $path= "/users/".$to_user["login"]."/opinions/?from=frl"; }

            $message = "����� ������������  <a href='{$GLOBALS['host']}/users/{$from_user['login']}{$this->_addUrlParams('b')}'>".$from_user["uname"]." ".$from_user["usurname"]."</a> [<a href='{$GLOBALS['host']}/users/{$from_user['login']}{$this->_addUrlParams('b')}'>".$from_user["login"]."</a>] 
                        ������������ �� ����� �������� �� FL.ru � ����� � ���, ��� ������ ������������ ��� ������������� ����������� �����.";
    
            $this->message = $this->GetHtml($to_user['uname'], $message, array('header' => 'default', 'footer' => 'default'), array('login'=>$to_user['login']));
    
            $this->recipient = $to_user["uname"]." ".$to_user["usurname"]." [".$to_user["login"]."] <".$email.">";
            $this->subject = "����� ���c�������� �� FL.ru";
    
            $this->SmtpMail('text/html');
        }
        
        return $this->sended; 
    }

    /**
     * ���������� ������������ ����������� �� ������ �� ��� ���������
     *
     * @param string|array $ids
     * @param resource $connect
     * @return  integer ���������� ������������ �����������.
     */
    function ArticleNewComment($ids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/articles_comments.php';

        $c = new articles_comments();
        if (!($data = $c->getComments4Sending($ids, $connect))) return NULL;

        foreach($data as $comment) {
            $this->subject = "����������� � ������� ������� � ��������� �� ����� FL.ru";

            if(substr($comment['s_subscr'], 11, 1) == '1' && $comment['s_uid'] != $comment['uid']
                && $comment['s_email'] && $comment['parent_id'] && $comment['s_banned'] == '0')
            {
                $body = 
                "<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['login']}</a>]
                �������(-�) <a href='{$GLOBALS['host']}/articles/?id={$comment['article_id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}'>����� �����������</a> 
                � ����� ����������/������������ � ������� <a href='{$GLOBALS['host']}/articles/{$this->_addUrlParams('b')}'>������� � ���������</a> �� ����� FL.ru. 
                <br /> --------
                <br />"
                .repair_html(LenghtFormatEx($comment['msgtext'], 300))."
                <br /> --------<br />";
                
                $this->message = $this->GetHtml($comment['s_uname'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comment['s_login']));

                $this->recipient = $comment['s_uname']." ".$comment['s_usurname']." [".$comment['s_login']."] <".$comment['s_email'].">";
                $this->SmtpMail('text/html');
            }

            if(substr($comment['a_subscr'], 11, 1) == '1' && !$comment['parent_id']
                && $comment['a_uid'] != $comment['from_id']
                && $comment['a_email'] && $comment['a_banned'] == '0') {
                $body = 
                "<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['uname']} {$comment['usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$comment['login']}{$this->_addUrlParams('b')}'>{$comment['login']}</a>]
                �������(-�) <a href='{$GLOBALS['host']}/articles/?id={$comment['article_id']}{$this->_addUrlParams('b', '&')}#c_{$comment['id']}'>����� �����������</a> 
                � ����� ����������/������������ � ������� <a href='{$GLOBALS['host']}/articles/{$this->_addUrlParams('b')}'>������� � ���������</a> �� ����� FL.ru. 
                <br /> --------
                <br />"
                .repair_html(LenghtFormatEx($comment['msgtext'], 300))."
                <br /> --------<br />";
                
                $this->message = $this->GetHtml($comment['a_uname'], $body, array('header' => 'default', 'footer' => 'default'), array('login' => $comment['a_login']));

                $this->recipient = $comment['a_uname']." ".$comment['a_usurname']." [".$comment['a_login']."] <".$comment['a_email'].">";
                $this->SmtpMail('text/html');
            }
        }

        return $this->sended;
    }
    
    /**
     * �� �������������� ���������� �������� ������� ��� �������� �� ��� �����������.
     * @see sbr_meta::getEventsInfo4Sending()
     *
     * @param array $xids   ��. ����������.
     * @param resource $connect   ������� ������� � ��.
     * @return integer   ���������� ������������ �����������.
     */
    function SbrNewEvents($xids, $connect = NULL) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        if($info = sbr_meta::getEventsInfo4Sending($xids, $connect)) {
            foreach($info as $xacts) {
                foreach($xacts as $func=>$events) {
                    $this->$func($events);
                }   
            }
        }
        return $this->sended;
    }

    /**
     * ���������� ���������� �� �������� ���.
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrOpened($events) {
        $ev0 = $events[0];
        $this->subject = "����������� � ���������� ����� ���������� ������ �� �������  �{$ev0['sbr_name']}�";
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $userlink = $GLOBALS["host"]."/users/".$ev0['e_login'];
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        $msg = "
          �������� <a href=\"{$userlink}\">{$ev0['e_uname']} {$ev0['e_usurname']}</a> [<a href=\"{$userlink}\">{$ev0['e_login']}</a>] ���������� ��� ��������� � ��� ���������� ������ �� ������� �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f')}'>{$sbr_name}</a>�. 
          �� ������ �������� ��������� ���������� �� ���������� ������ � <a href='https://feedback.fl.ru/{$this->_addUrlParams('f', '?')}'>��������������� �������</a> �������. 
        ";
        $this->message = $this->splitMessage($this->GetHtml($ev0['f_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0['f_uname']." ".$ev0['f_usurname']." [".$ev0['f_login']."] <".$ev0['f_email'].">";
        $this->SmtpMail('text/html');
    }

    /**
     * ����������� ����� ���������� � ���, ��� ������ ���������������.
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrReserved($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        include_once(dirname(__FILE__).'/account.php');
        include_once(dirname(__FILE__).'/bank_payments.php');
        $reserved = account::getOperationInfo($ev0['reserved_id']);

        for($e=0;$e<2;$e++) {
            $r = $e ? 'e_' : 'f_';
            $rcls = $e ? 'sbr_emp' : 'sbr_frl';
            $sbr = new $rcls($ev0[$r.'uid'], $ev0[$r.'login']);
                $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
                $cnum = $sbr->getContractNum($ev0['sbr_id'], $ev0['scheme_type'], $ev0['posted']);
                $num = in_array((int)$reserved['payment_sys'], array(4,5))
                            ? ((int)$reserved['payment_sys'] == 4 ? '� �-'.$cnum : '� '.  bank_payments::GetBillNum($ev0['reserved_id']))
                            : '';
                $num_str = in_array((int)$reserved['payment_sys'], array(4,5))
                            ? '�� ����� '.$num : '';
                if($r == 'e_'){
                    $fuserlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
                    $msg_e = "����������� ��� � ���, ��� ������ � ������ �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>{$sbr_name}</a>� ������� ���������������. ����������� <a href='{$fuserlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$fuserlink}'>{$ev0['f_login']}</a>] ���������� ����������� � ���, ��� ��� ���������� ������ ���������� ������ �� �������.";
                    
                    $this->subject = "�������� �������� ��� $cnum ���������������";
                    $this->message = $this->splitMessage($this->GetHtml($ev0['e_uname'], $msg_e, array('header'=>'simple', 'footer'=>'norisk_robot')));
                    $this->recipient = $ev0['e_uname']." ".$ev0['e_usurname']." [".$ev0['e_login']."] <".$ev0['e_email'].">";
                    $this->SmtpMail('text/html');
                }else{
                    
                    $msg_f  = "����������� ��� � ���, ���  ������ � ������ �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>� ������� ���������������.<br/><br/>";
                    $msg_f .= "����������, ���������� � ���������� �������.";
                   
                    $this->subject = "�������������� ����� � ���������� ������ (������ �{$ev0['sbr_name']}�)";
                    $this->message = $this->splitMessage($this->GetHtml($ev0['f_uname'], $msg_f, array('header'=>'simple', 'footer'=>'norisk_robot')));
                    $this->recipient = $ev0['f_uname']." ".$ev0['f_usurname']." [".$ev0['f_login']."] <".$ev0['f_email'].">";
                    $this->SmtpMail('text/html');
                }
            /**
             * @deprecated 
             */
            /*
            if(!$sbr->checkUserReqvs()) {
                $msg =  "
                  ����������, ������� ��� ����������� ������ �� ������� �<a href='{$GLOBALS['host']}/users/{$ev0[$r.'login']}/setup/finance/{$this->_addUrlParams($e ? 'e' : 'f')}'>�������</a>�. ��������� �� ������� ��������� ��������� ��� ����������� ��������
                  �� �������� ����� � �������� ����������� �������� ��� ������ ����� ������ ������� ��� �����.
                ";//�� ������� �<a href='{$url}?id={$ev0['sbr_id']}'>{$ev0['sbr_name']}</a>�
                $this->subject = "���������� ������� ���������";
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }*/
        }
    }

    /**
     * �����������, ��� ��������� ���������� ���������� � ������� �� ���.
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrAgreed($events) {
        $this->subject = "��������� ���������� � ��������� ���������� ������";
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        $msg  = "����������� <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>] ���������� � ������������� ���� ��������� ������ �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>{$sbr_name}</a>�. ��� ���������� ��������������� ������.<br/><br/>";
        $msg .= "����������, ��������� � <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>������</a> � �������������� ������, ������ ���������� ����������. � ��������� ����������� �� �������������� ������� ����� ������������ <a href='https://feedback.fl.ru/{$this->_addUrlParams('e', '?')}'>�����</a>.";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0['e_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0['e_uname']." ".$ev0['e_usurname']." [".$ev0['e_login']."] <".$ev0['e_email'].">";
        $this->SmtpMail('text/html');
    }

    /**
     * ����������� ������������ �� ������ ���������� �� ���.
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrRefused($events) {
    	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr_meta.php'; 	
        $this->subject = "��������� ��������� �� ���������� ������";
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        $ev0['new_val'] = str_replace("\\", "", $ev0['new_val']);
        $msg  = "����������� <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>] ��������� �� ������ �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>{$sbr_name}</a>�";
        $msg .= $ev0['new_val'] ? " �� �������:<br/><br/> �{$ev0['new_val']}�. " : ' ��� �������� �������. ';
        $msg .= "<br>�� ������ ������� � <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>������</a>, �������� ������� � �������� ��������� �� ����������� �����������.";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0['e_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0['e_uname']." ".$ev0['e_usurname']." [".$ev0['e_login']."] <".$ev0['e_email'].">";
        $this->SmtpMail('text/html');
    }

    /**
     * ����������� ������������ � �������� ��������� � ��� �����������.
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrChangesAgreed($events) {
        $this->subject = "��������� ���������� � ���������� ������� ���������� ������";
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr = sbr_meta::getInstanceLocal($ev0['e_uid']);
        $stage = $sbr->initFromStage($ev0['stage_id']);
        $stage_name = sbr_meta::getNameForMail($ev0);
        $msg  = "����������� <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>] ���������� � ������������� ���� ����������� ������� � ������ �<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('e', '&')}'>{$stage_name}</a>�.<br/><br/>";
        if(!$sbr->reserved_id) {
            $msg .= "��� ���������� ��������������� ������. <br/>";
            $msg .= "����������, ��������� � <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>������</a> � �������������� ������, ������ ���������� ����������. � ��������� ����������� �� �������������� ������� ����� ������������ <a href='https://feedback.fl.ru/{$this->_addUrlParams('e', '?')}'>�����</a>.";
        }
        
        $this->message = $this->splitMessage($this->GetHtml($ev0['e_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0['e_uname']." ".$ev0['e_usurname']." [".$ev0['e_login']."] <".$ev0['e_email'].">";
        $this->SmtpMail('text/html');
    }

    /**
     * ����������� ������������ �� ������ ���������� �� ��������� � ���.
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrChangesRefused($events) {
        $this->subject = "��������� �� ���������� � ���������� ������� ���������� ������";
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
        $stage_name = sbr_meta::getNameForMail($ev0);
        $msg = "����������� <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>] ��������� �� ������������ ���� ��������� ������� � ������ �<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('e', '&')}'>{$stage_name}</a>�.<br/><br/>";
        $msg .= "�� ������ ������� � <a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('e', '&')}'>������</a>, �������� ������� � �������� ��������� �� ����������� ����������� ��� ��������� � ���������� ������ �������.";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0['e_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0['e_uname']." ".$ev0['e_usurname']." [".$ev0['e_login']."] <".$ev0['e_email'].">";
        $this->SmtpMail('text/html');
    }

    /**
     * ����������� ������ �� ���������� ��� � ���, ��� ������ ������ ���������� � ��������.
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrArb($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $sbr   = sbr_meta::getInstanceLocal($ev0['e_uid']);
        $sbr->initFromId($ev0['sbr_id']);
        $stage = new sbr_stages($sbr, array('id'=>$ev0['own_id']));
        $arb = $stage->getArbitrage(false, false);
        $stage_name = sbr_meta::getNameForMail($ev0);
        $sbr_num    = $stage->sbr->getContractNum();
        if($arb['user_id'] == $ev0['f_uid']) {
            $r = 'e_';
            $arb = 'f_';
            $this->subject = "��������� ��������� � �������� ������� ���������� ������";
            $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
            //$msg = "����������� ��� � ���, ��� �� ������� �<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>� ����������� <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>] ��������� � �������� �� �������:<br/><br/>";
        } else {
            $r = 'f_';
            $arb = 'e_';
            $this->subject = "�������� ��������� � �������� ������� ���������� ������";
            $userlink = $GLOBALS["host"]."/users/".$ev0['e_login'];
            //$msg = "����������� ��� � ���, ��� �� ������� �<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>� �������� <a href='{$userlink}'>{$ev0['e_uname']} {$ev0['e_usurname']}</a> [<a href='{$userlink}'>{$ev0['e_login']}</a>] ��������� � �������� �� �������:<br/><br/>";
        }
        $msg = "����������� ��� � ���, ��� ������������ <a href='{$userlink}'>{$ev0[$arb.'uname']} {$ev0[$arb.'usurname']}</a> [<a href='{$userlink}'>{$ev0[$arb.'login']}</a>] ��������� � �������� �� �������:<br/><br/>";
        $msg .= "�" . reformat($arb['descr']) . "�<br/><br/>";
        $msg .= "������ �� ����� <a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$ev0['stage_name']}</a> ����������� ������ <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$sbr_num}</a> ��������������. ���� ��������� ������� � �� " . sbr_stages::MAX_ARBITRAGE_DAYS . " ������� ���� � ������� ��������� � ��������.<br/><br/>";
        $msg .= "�� ������ �������� ���� ����������� �� ������ ����������� �������� � ������� ���� ������, � ������� ������������ � ������ �<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>�.";
        //$msg .= "�" . reformat($arb['descr']) . "�<br/><br/>";
        //$msg .= "����������, ��������� � <a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>������</a> � ���������������� ��������.";
            
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
    }

    /**
     * ����������� ����� ���������� ��� � ��������� ������� ���������.
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrArbResolved($events) {
        $ev0 = $events[0];
        $this->subject = "�������� ����� ������� �� ���������� ������ (������ �{$ev0['sbr_name']}�)";
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $sbr = new sbr(NULL);
        $stage = $sbr->getStage($ev0['own_id']);
        $arb = $stage->getArbitrage(false, false);
        for($e=0;$e<2;$e++) {
            $r = $e ? 'e_' : 'f_';
            
            $stage_name = sbr_meta::getNameForMail($ev0);
            
            if($r == 'f_') {
                $userlink = $GLOBALS["host"]."/users/".$ev0['e_login'];
                $usr = "��������� <a href='{$userlink}'>{$ev0['e_uname']} {$ev0['e_usurname']}</a> [<a href='{$userlink}'>{$ev0['e_login']}</a>]";
            } else {
                $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
                $usr = "����������� <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>]";
            }
            
            $msg  = "����������� ��� � ���, ��� �������� ����� ������� � ������ �<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>� � ������ ��. ";
            $msg .= "����������, ��������� � <a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>������</a>, ����� ������������ � �������� ��������� � �������� ����� {$usr}, � ����� ����� ������� ���������� ������.";
             
            //$msg =  "��������� ������� ������� ��� ����� ����� ������� �� ������ �<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$ev0['stage_name']}</a>� ������� <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$ev0['sbr_name']}</a>:<br/><br/>";
            //$msg .= "----<br/>";
            //$msg .= "�{$stage->arbitrage['descr_arb']}�";
            //$msg .= "<br/>----<br/>";
            //$msg .= '<br/><br/>�������� �� ������, ����� �������� ����� ��������� ����������.';
            $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
            $this->SmtpMail('text/html');
        }
    }

    /**
     * ���������� ����������� ����� ��������� ������� ������ �������������.
     * ����������� ������ ��������� �� ������ � ����� ���������.
     * ����� ���������� ����������� �� ������ ��������� (� ��� �� �������) � ������, ���� ��������� ��������� �� ���.
     * @see sbr_meta::parseEvents()
     *
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrTzChanged($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $sbr_link = " �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('b', '&')}'>{$ev0['sbr_name']}</a>�";
        $changes = '';
        $parse = sbr_meta::parseEvents($events);
        foreach($parse['events'] as $id=>$ev) {
            $changes .= '<br/>'.(++$i).'. '.$ev['ev_name'].($ev['note'] ? ' (<strong>'.trim($ev['note']).'</strong>)' : '') . ' &mdash; '
                     . ($ev['stage_name'] ? '������ �' : '')
                     . '<a href="' . $url . ($ev['stage_name'] ? "?site=Stage&id={$ev['own_id']}" : "?id={$ev['sbr_id']}") . $this->_addUrlParams('b', '&') . '">'
                     . ($ev['stage_name'] ? reformat($ev['stage_name'],40,0,1) : '���� ������')
                     . '</a>'
                     . ($ev['stage_name'] ? '�' : '')
                     . '.'
                     ;
        }

        if(!$changes) return;

        if($ev0['xtype'] == sbr::XTYPE_RLBK) { 
            $this->subject = "��������� � ���������� ������ �������� (������ �{$ev0['sbr_name']}�)";
            $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
            for($e=0;$e<2;$e++) {
                $r = $e ? 'e_' : 'f_';
                $msg = $e ? "� ����� � ������� ����������� <a href=\"{$userlink}\">{$ev0['f_uname']}</a> <a href=\"{$userlink}\">{$ev0['f_usurname']}</a> [<a href=\"{$userlink}\">{$ev0['f_login']}</a>] �� ���������, ������� ��������� ������� ������� {$sbr_link} � ���������� ������:<br/>"
                          : "� ����� � ���, ��� �� ���������� �� ��������� � ���������� ������, ������� ��������� ������� ������� {$sbr_link} � ���������� ������:<br/>";
                $msg .= "---<br/>";
                $msg .= $changes.'<br/>';
                $msg .= "---<br/><br/>";
                $msg .= $e ? "�� ������ ��������������� ������� � �������� ��������� �� ����������� �� ����������� ��� ���������� �� ���������, ��������� ������ � �������� ���������.".
                             " ����� ��������� ���������� �� ������������ ���������� ������ � ����������� ��������� <a href='https://feedback.fl.ru/{$this->_addUrlParams('e', '?')}'>�����</a>."
                           : "�� ������ ���������� ������ � �������� ���������. �� ������ ������������ � ����� ����������� <a href='https://feedback.fl.ru/{$this->_addUrlParams('f', '?')}'>�� ������� ���������� ���������� ������</a>.";
                $msg .= ' �������� �� ������, ����� �������� ����� ��������� ����������.';
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        }
        else {
            $this->subject = "�������� ���� ��������� � ������� ���������� ������ �� ������� �{$ev0['sbr_name']}�";
            $userlink = $GLOBALS["host"]."/users/".$ev0['e_login'];
            
            $msg  = "�������� <a href='{$userlink}'>{$ev0['e_uname']} {$ev0['e_usurname']} [{$ev0['e_login']}]</a> ���������� ��� �������� ������� ������ {$sbr_link}.<br/><br/>";
            $msg .= "��� ���������� ������� � <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('b', '&')}'>������</a> � ������������ � ������������� �����������. �� ������ ����������� �� ��������� ������� ��� ���������� �� ���, ������ �������.";
            
            /*$msg = "������������ <a href=\"{$userlink}\">{$ev0['e_uname']}</a> <a href=\"{$userlink}\">{$ev0['e_usurname']}</a> [<a href=\"{$userlink}\">{$ev0['e_login']}</a>] ����(-��) �������� � ������� ��� ����� �� �������";
            $msg .= $sbr_link.':<br/>';
            $msg .= '----<br/>';
            $msg .= $changes.'<br/><br/>';
            $msg .= '----<br/><br/>';
            $msg .= "��� ���������� ����������� ��� ��������� ������ ���������.<br/> �� ������ ������������ � ����� ����������� �� <a href='{$GLOBALS['host']}/help/?q=891{$this->_addUrlParams('f', '&')}'>������� ���������� ������� ��� �����</a>.";
            */
            $this->message = $this->splitMessage($this->GetHtml($ev0['f_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0['f_uname']." ".$ev0['f_usurname']." [".$ev0['f_login']."] <".$ev0['f_email'].">";
            $this->SmtpMail('text/html');
        }
    }
    
    /**
     * ����������� �� ��������� ������� ����� ���.
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrStatusChanged($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $sbr_link_e = "������ �<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('e', '&')}'>{$ev0['stage_name']}</a>� ����������� ������ � ������� �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>{$ev0['sbr_name']}</a>�";
        $sbr_link_f = "������ �<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('f', '&')}'>{$ev0['stage_name']}</a>� ����������� ������ � ������� �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$ev0['sbr_name']}</a>�";
        setlocale(LC_ALL, "ru_RU.CP1251");
        $changes = "c �" . ucfirst(sbr_stages::$nss_classes[$ev0['old_val']][1]) . "� �� �" . ucfirst(sbr_stages::$nss_classes[$ev0['new_val']][1]) . "�.";
        setlocale(LC_ALL, "en_US.UTF-8");
        if($ev0['xtype'] == sbr::XTYPE_RLBK) {
            $this->subject = "������ ���������� ������ ��������� � ����������� ���������";
            for($e=0;$e<2;$e++) {
                $r = $e ? 'e_' : 'f_';
                $userlink = $GLOBALS['host']."/users/{$ev0['f_login']}";
                $msg = $e ? "� ����� � ������� ����������� <a href=\"{$userlink}\">{$ev0['f_uname']} {$ev0['f_usurname']}</a> <a href=\"{$userlink}\">[{$ev0['f_login']}]</a> �� ���������, ������� ��������� ������� {$sbr_link_e} � ���������� ������:<br/>"
                          : "� ����� � ���, ��� �� ���������� �� ���������, ������� ��������� ������� {$sbr_link_f} � ���������� ������:<br/>";
                $msg .= "<br>��������� ������ ������: {$changes}<br/><br/>";
                $msg .= $e ? ""
                           : "";
                $msg .= '�������� �� ������, ����� �������� ����� ��������� ����������.';
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'simple')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        } else {
            $this->subject = "��������� ������ ������ � ���������� ������ �� ������� �{$ev0['sbr_name']}�";
            $userlink = $GLOBALS["host"]."/users/".$ev0['e_login'];
            $fuserlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
            $stage_name = sbr_meta::getNameForMail($ev0);
            if($ev0['new_val'] == sbr_stages::STATUS_COMPLETED) {
                $msg  = "����������� ��� � ���, ��� ������ �<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('e', '&')}'>{$stage_name}</a>�  ������� �������� ���������� <a href=\"{$userlink}\">{$ev0['e_uname']} {$ev0['e_usurname']}</a> [<a href=\"{$userlink}\">{$ev0['e_login']}</a>].<br/><br/>";
                $msg .= "����������, ��������� � <a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('f', '&')}'>������</a>, ����� �������� ����� ���������, ����� ������� ���������� ������ � ������� ������ ��������� �����.";
                
                // ��� ������������
                $e_msg  = "�� ������� ��������� ������ �<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('e', '&')}'>{$stage_name}</a>�.<br/>";
                $e_msg .= "������ ��� ���������� �������� ����� ����������� <a href=\"{$fuserlink}\">{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href=\"{$fuserlink}\">{$ev0['f_login']}</a>] � ����� ������� ���������� ������ � ���������� ����� <a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('e', '&')}'>������</a>.";
            
                $this->message = $this->splitMessage($this->GetHtml($ev0['e_uname'], $e_msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
                $this->recipient = $ev0['e_uname']." ".$ev0['e_usurname']." [".$ev0['e_login']."] <".$ev0['e_email'].">";
                $this->SmtpMail('text/html');
            } else {
                $msg  = "�������� <a href=\"{$userlink}\">{$ev0['e_uname']} {$ev0['e_usurname']}</a> [<a href=\"{$userlink}\">{$ev0['e_login']}</a>] ����� �������� ������ ������ �<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('e', '&')}'>{$stage_name}</a>� {$changes}<br/><br/>";
                $msg .= "����������, ��������� � <a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams('f', '&')}'>������</a>, ����� ������� ��� ��������� ������������ ���������� ���������.";  
            }
            //$msg .= "�� ������ ������������ � ����� ����������� �� <a href='{$GLOBALS['host']}/help/?q=891{$this->_addUrlParams('f', '&')}'>������� ���������� ������� ��� �����</a>.";
            $this->message = $this->splitMessage($this->GetHtml($ev0['f_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0['f_uname']." ".$ev0['f_usurname']." [".$ev0['f_login']."] <".$ev0['f_email'].">";
            $this->SmtpMail('text/html');
            
        }
    }


    /**
     * ����������� ���������� �� ������ ���.
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrCanceled($events) {
        $ev0 = $events[0];
        $this->subject = "�������� ������� ���������� ������ �� ������� �{$ev0['sbr_name']}�";
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $userlink = $GLOBALS["host"]."/users/".$ev0['e_login'];
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        
        $msg = "������ �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>� ���� �������� ����������. ��������, ��� ���� �������������� �� ����������. ������� � �������� <a href='{$url}'>���������� �������</a>."; 
        
        //$msg  = "����������� ��� � ���, ��� ������ �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>� ������� ���������� <a href=\"{$userlink}\">{$ev0['e_uname']} {$ev0['e_usurname']}</a> [<a href=\"{$userlink}\">{$ev0['e_login']}</a>].<br/>";
        //$msg .= "������� ������ ������ �� ������ ������ � ������������.";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0['f_uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0['f_uname']." ".$ev0['f_usurname']." [".$ev0['f_login']."] <".$ev0['f_email'].">";
        $this->SmtpMail('text/html');
    }

    /**
     * ����������� ����� ���������� �� ������ ��������� (������ � ��������).
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrArbCanceled($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $this->subject = "�������� �� ���������� ������ �������";
        for($e=0;$e<2;$e++) {
            $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
            $stage_name = sbr_meta::getNameForMail($ev0);
            $sbr_link = "������ �<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($e ? 'e' : 'f', '&')}'>{$stage_name}</a>� � ������� �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($e ? 'e' : 'f', '&')}'>{$sbr_name}</a>�";
            $r = $e ? 'e_' : 'f_';
            $msg = "�������� �� {$sbr_link} ��� �������.<br/><br/>
              ������ ������ ������������� ��������� �� �� ����������. 
              �� ������ ������ ����� �������� � �������� ���������� ������ � <a href='https://feedback.fl.ru/{$this->_addUrlParams($e ? 'e' : 'f', '?')}'>��������������� ������� �������</a>.";
            $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
            $this->SmtpMail('text/html');
        }
    }

    /**
     * ����������� ����� ���������� � ���������� ���� ���.
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrCompleted($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $this->subject = "���������� ������ �� ������� �{$ev0['sbr_name']}� ���������";
        for($e=0;$e<2;$e++) {
            $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
            $sbr_link = " �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($e ? 'e' : 'f', '&')}'>{$sbr_name}</a>�";
            $r = $e ? 'e_' : 'f_';
            $f = $e ? 'simple' : 'norisk_robot';
            $w = $e ? '����������' : '���������';
            $msg = "
              ���������� ������ �� ������� {$sbr_link} " .($e?"":"���������")." ���������.<br/><br/>
              ����������, �� �������� �������� ������ {$w}. 
              �� ������ �������� ��������� ���������� �� <a href='https://feedback.fl.ru/{$this->_addUrlParams($e ? 'e' : 'f', '?')}'>���������� ���������� ������</a>.
              <br/><br/>              
            ";
            $msg .= $e? "���������� ��� �� ������������� ������� ���������� ������. ��������, ��� �� �������� ��������!" : "";  
            $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>$f)));
            $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
            $this->SmtpMail('text/html');
        }
    }

    /**
     * ����������� ��� ������� � ����������� ����������
     * 
     */
    function SbrStageCompleted($events) {
        $ev0 = $events[0];
        
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr = sbr_meta::getInstanceLocal($ev0['f_uid']);
        $stage = $sbr->initFromStage($ev0['stage_id']);
        
        if( ( $stage->sbr->scheme_type == sbr::SCHEME_PDRD || $stage->sbr->scheme_type == sbr::SCHEME_PDRD2 ) && $ev0['own_role'] == sbr::EVROLE_FRL && $stage->status == sbr_stages::STATUS_COMPLETED) {
            $sbr->getDocs();
            $r   = 'f_';
            
            $this->subject = "���������� �������� ����������� ��������� (������ �{$ev0['sbr_name']}�)";
            foreach($stage->sbr->docs as $hdoc) {
                if( $hdoc['type'] == sbr::DOCS_TYPE_ACT || $hdoc['type'] == sbr::DOCS_TYPE_FM_APPL || 
                    $hdoc['type'] == sbr::DOCS_TYPE_WM_APPL || $hdoc['type'] == sbr::DOCS_TYPE_YM_APPL ||
                    $hdoc['type'] == sbr::DOCS_TYPE_TZ_PDRD ) {
                    $head_docs[] = $hdoc;
                }
            }
            $hdoc_cnt = count($head_docs);    
            $stage_name = sbr_meta::getNameForMail($ev0);
            $msg  = "�� ������� ��������� ������ �<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$stage_name}</a>�.<br/><br/>";
            $msg .= "��� ��������� ������ ��� ���������� �������, ����������� � ���� ����������� � ��������� ������ " . ending($hdoc_cnt, '��������', '���������', '���������') . ":<br/>";

            foreach($head_docs as $hdoc) {
                $msg .= "<a href='". WDCPREFIX . "/{$hdoc['file_path']} {$hdoc['file_name']}' class='b-layout__link'> {$hdoc['name']}</a>, " . ConvertBtoMB($hdoc['file_size']);
            }
            $msg .= "<br/><br/>";
            $msg .= "����������� ��������� ���������� � ���������� ��������� �� ����� ������� ��� ����� �� ������:<br/>";
            $msg .= "- 129223, ������, �/� 33;<br/>"; 
            $msg .= "- 190031, �����-���������, ������ ��., �.13/52, �/� 427;<br/>";
            $msg .= "- 420032, ������, �/� 624;<br/>";
            $msg .= "- 454014, ��������� - 14, �/� 2710.<br/><br/>";
            $msg .= "����������� ������� ������������ �����������-���������� � ���� ����� �� �������� - ��� \"����\".<br/><br/>"; 
            $msg .= "����� �������� ���������� ������� �� ������ ��������� ���� �������� ������ � ����� ����������� �� ������ ��������. ��� ������������ �������� � �������� ����������� ��� ���������� � <a href='https://feedback.fl.ru/'>������ ���������</a>.";

            $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
            $this->SmtpMail('text/html');
        } else if(( $stage->sbr->scheme_type == sbr::SCHEME_PDRD || $stage->sbr->scheme_type == sbr::SCHEME_PDRD2 ) && $ev0['own_role'] == sbr::EVROLE_FRL && $stage->status == sbr_stages::STATUS_ARBITRAGED) {
            $sbr->getDocs();
            $r   = 'f_';
            $this->subject = "���������� �������� ����������� ��������� (������ �{$ev0['sbr_name']}�)";
            foreach($stage->sbr->docs as $hdoc) {
                if( $hdoc['type'] == sbr::DOCS_TYPE_ACT || $hdoc['type'] == sbr::DOCS_TYPE_FM_APPL || 
                    $hdoc['type'] == sbr::DOCS_TYPE_WM_APPL || $hdoc['type'] == sbr::DOCS_TYPE_YM_APPL ||
                    $hdoc['type'] == sbr::DOCS_TYPE_TZ_PDRD ) {
                    $head_docs[] = $hdoc;
                }
            }
            $stage_name = sbr_meta::getNameForMail($ev0);
            $msg .= "����������� ��� � ���, ��� ��� ��������� ������ �������� �� ������ �<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$stage_name}</a>�  � ������������ � �������� ���������, ��� ���������� �������, ����������� � ���� ����������� � ��������� ������ ���������:<br/>";
            foreach($head_docs as $hdoc) {
                $msg .= "<a href='". WDCPREFIX . "/{$hdoc['file_path']} {$hdoc['file_name']}' class='b-layout__link'> {$hdoc['name']}</a>, " . ConvertBtoMB($hdoc['file_size']);
            }
            $msg .= "<br/><br/>";
            $msg .= "����������� ��������� ���������� � ���������� ��������� �� ����� ������� ��� ����� �� ������:<br/>";
            $msg .= "- 129223, ������, �/� 33;<br/>"; 
            $msg .= "- 190031, �����-���������, ������ ��., �.13/52, �/� 427;<br/>";
            $msg .= "- 420032, ������, �/� 624;<br/>";
            $msg .= "- 454014, ��������� - 14, �/� 2710.<br/><br/>";
            $msg .= "����������� ������� ������������ �����������-���������� � ���� ����� �� �������� - ��� \"����\".<br/><br/>";
            
            $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
            $this->SmtpMail('text/html');
        } elseif($stage->sbr->scheme_type == sbr::SCHEME_LC) {
            $stage_name = sbr_meta::getNameForMail($ev0);
            $this->subject = "���������� ������ �� ������� �{$ev0['sbr_name']}� ���������";
            $endDate = date('d.m.Y', strtotime($sbr->data['dateEndLC']));
            $msg  = "���������� ������ <a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$stage_name}</a> ���������. ��� ����, ����� �������� ������������ ������, ��� ���������� ��������� ��������� � ���� ����� ������� �� ������ ���������� ����.<br/><br/>";
            $msg .= "�������� ��������, ��� ��������� ���� ���������� �� ����, ��� ������� ���� �������� ����������� (�� {$endDate}). � ��������� ������ �������� �������� ����� ���������� ���������.";
            $r = 'f_';
            $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
            $this->SmtpMail('text/html');
        }
    }
    
    /**
     * ����������� � ��������� ���������� ����������
     * 
     * @param type $events 
     */
    function SbrDocReceived($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $r   = 'f_';
         
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr = sbr_meta::getInstanceLocal($ev0['f_uid']);
        $stage = $sbr->initFromStage($ev0['stage_id']);
        
        if($stage->sbr->scheme_type == sbr::SCHEME_PDRD || $stage->sbr->scheme_type == sbr::SCHEME_PDRD2) {
            $r == 'f_';
            
            $this->subject = "�� �������� ����������� ���� ��������� (������ �{$ev0['sbr_name']}�)";
             
            $msg  = "�� �������� ����������� ���� ���������.<br/><br/>";
            $msg .= "������ ����� ���������� ��� � ������� 1-2 ������� ����. � ������ �������� ����������� ��� ���������� � <a href='https://feedback.fl.ru/'>������ ���������</a>.";
            
            $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
            $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
            $this->SmtpMail('text/html');
        }
    }
    
    /**
     * �������� ����������� ���������� � ��� ��� ������ ����������
     * 
     * @param type $events 
     */
    function SbrOvertime($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $r   = 'f_';
        
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr = sbr_meta::getInstanceLocal($ev0['f_uid']);
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        $this->subject = "����� �� ���� ������ {$sbr_name} �������. �������� ������ ���� ������ ";// ���� ������� ��� ����� {$sbr_name} ��������. �������� ������ ���� ��л �� FL.ru.)";
        
        $fmsg  = "{$ev0[$r.'uname']}, �����, ���������� ���������� �� ���������� <a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>�����</a> ���������� ������ <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>, �������. ��� ���������� �������� ��������� ����������� ������ (���� ���������� �������� �����, ����������� �� � ������������ � ������). <br/><br/>";
        $fmsg .= "<i>�������� ��������</i>: ���� �� 2 ������� ��� �������� �� ������ ������ � �� ������ �� �����, ��� ���������� ���������� � �������� � ����  �� ������� ���  5 ������� ���� � ���� ���������� �����. � ��������� ������ ����������������� ��� ������ ������ ����� ���������� ���������, � �� �� �������� ������������ �������� ��������.";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $fmsg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
        
        $r = 'e_';
        $emsg  = "{$ev0[$r.'uname']}, �����, ���������� ���� �� ���������� <a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>�����</a> ���������� ������ <a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>, �������. ��� ���������� ������� ��������� ������, ��������������� ������������. ���� �� �� ������������� ����������� ������ � ����� �������������� ������ ������������� ���� ������������ �������, � ����� � ��� ������, ���� ������ ������������ �� ���� �������������, ���������� � ��������. <br/><br/>";
        $emsg .= "<i>�������� ��������</i>: ���� �������� ������� ��������, ��� ���������� ������ ������ � �������� � ������� 5 ������� ���� � ������� ���������� �����. �� ��������� ����� ����� ����������� ���������� � �������� ��� �� �����.";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $emsg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
    }
    
    /**
     * ����������� � ��� ��� ����� ���� ������� �� ���������� �� �� ����������
     * 
     * @param array $events
     */
    function SbrPauseReset($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr_name   = sbr_meta::getNameForMail($ev0, 'sbr');
        $stage_name = sbr_meta::getNameForMail($ev0);
        
        $this->subject = "���� ����� � ������ �{$sbr_name}� �����";
        $msg = "����� ���� ������������� �����, ����� ������������ � ����� �<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$stage_name}</a>� ������� �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>�. ����� ��������, �� ������ ���������� ������ � ������ � ������� ������.";
        $r = 'f_';
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
        
        $r = 'e_';
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
    }
    
    /**
     * ����������� � ��� ��� ����� ���� ���������
     * 
     * @param array $events
     */
    function SbrPauseOver($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr_name   = sbr_meta::getNameForMail($ev0, 'sbr');
        $stage_name = sbr_meta::getNameForMail($ev0);
        
        $this->subject = "���� ����� � ������ �{$sbr_name}� ��������";
        $msg = "����� ���� �����, ����� ������������� � ����� �<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$stage_name}</a>� ������� �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>�. ������ ���� ������������� ��������� � ������� �����, ������ ����� ��������� �� �� ������.";
        $r = 'f_';
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
        
        $r = 'e_';
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
    }
    
    /**
     * ����������� ������� ��������.
     * @param type $events 
     */
    function SbrMoneyPaidFrl($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $r   = 'f_';
        
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr = sbr_meta::getInstanceLocal($ev0['f_uid']);
        $stage = $sbr->initFromStage($ev0['stage_id']);
        $arb   = $stage->getArbitrage();
        $type_payment = exrates::getNameExrates($stage->type_payment);
        
        $this->subject = "����������� ������� �������� (������ �{$ev0['sbr_name']}�)";
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        if($stage->status == sbr_stages::STATUS_ARBITRAGED && (int) $arb['frl_percent'] != 1) {
            $msg  = "����������� ��� � ���, ��� ��������� ������� �������� � ������ �<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>� ����������� (� ������������ � �������� ���������).<br/><br/>";
        } else {
            $msg  = "����������� ��� � ���, ��� � ������ �<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>� ��� ��� ���������� ������� �������� � ����� " . sbr_meta::view_cost($stage->getPayoutSum(sbr::FRL), $stage->sbr->cost_sys) . " �� ��������� ���� ���������.<br/><br/>";
        }
        $msg .= "���������� ������� �� ��� ������ ���� ����� ������ ��������� ����� (�� ���������� ����� �� ���������� ���� � ����������� �� ������� �������).";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
        
        if($stage->sbr->scheme_type == sbr::SCHEME_LC && $stage->sbr->ps_frl == pskb::WW) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/classes/single_send.php';
            $user = new users();
            $user->GetUserByUID($stage->sbr->frl_id);
            
            $single_send = new single_send($user);
            
            if( !$single_send->is_send(single_send::NOTICE_WEBM) ) {
                $msg  = "������� � ���, ��� ���� ����� ���-�������: <a href='http://webpay.pscb.ru/login/auth' target='_blank'>���-������� �������������� �����������  ������������� ����� (����)</a> � ��� ��������� ������� ��� ���������� ������ ��������� ����� � ������������� ���������� ��������� ����� ��������. ��� ������ ����� ����������� ������ ���-������� ������������ ��� �������������� � �������� ����� ���������, � ����� ��� ������� �������� �����������.<br/><br/>";
                $msg .= "���-������� ��������� ��� ��� � ������ �������� ���� <a href='https://www.fl.ru/offer_lc.pdf' target='_blank'>������ �� ���������� �������� � ������������� ������ ��������</a>. �� ����������� ��� ������������������ � ���-��������: � ���� ������ ��� ��� �� ����� ����������� �� ������ �������� �������, � ����� �� ������ ������� �������� ������ � ������ �������������� �������� (������ �������� � ��������� �������, � �������� ������������� ������ ���-�������).<br/><br/>";
                $msg .= "� ����� ��������� ����������� �� ���-�������� ����� ������������ � <a href='https://feedback.fl.ru/topic/397421-veb-koshelek-obschaya-informatsiya/{$this->_addUrlParams('f', '?')}'>��������������� ������� ������</a>.";
                
                $this->subject = "��� ����� ���-�������";
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
                $single_send->setUpdateBit(single_send::NOTICE_WEBM);
            }
        }
    }
    
    /**
     * ����������� ������� ��������.
     * @param type $events 
     */
    function SbrMoneyPaidEmp($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $r   = 'f_';
        
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $sbr = sbr_meta::getInstanceLocal($ev0['f_uid']);
        $stage = $sbr->initFromStage($ev0['stage_id']);
        $arb   = $stage->getArbitrage();
        $type_payment = exrates::getNameExrates($stage->type_payment);
        
        $this->subject = "����������� ������� �������� (������ �{$ev0['sbr_name']}�)";
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        $msg  = "����������� ��� � ���, ��� ������� ����� � ������ �<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>� ���������� (� ������������ � �������� ���������).<br/><br/>";
        $msg .= "���������� ������� �� ��� ������ ���� ����� ������ ��������� ����� (�� ���������� ����� �� ���������� ���� � ����������� �� ������� �������).";
        
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
    }
    
    /**
     * ����������� ������ �� ���������� ��� � ��� ��� ������ ������� ��� �����.
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrFeedback($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        // ���� ��������� ������ �����������, ���� ��������� ����������� ���������� �����������
        if($ev0['abbr'] == 'FRL_FEEDBACK') {
            $this->SbrStageCompleted($events);
        }
        $this->subject = "��� �������� ����� �� ���������� ������ (������ �{$ev0['sbr_name']}�)";
        $stage_name = sbr_meta::getNameForMail($ev0);
        if($ev0['own_role'] == sbr::EVROLE_FRL && $ev0['frl_feedback_id']) {
            $r = 'e_';
            $userlink = $GLOBALS["host"]."/users/".$ev0['f_login'];
            $feedback = sbr_meta::getFeedback($ev0['frl_feedback_id']);
            $uniq_id = $feedback['id'] * 2 + 1;
            $link_feedback = "{$GLOBALS["host"]}/users/{$ev0['e_login']}/opinions/#p_{$uniq_id}";
            
            require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
            $sbr = sbr_meta::getInstanceLocal($ev0['e_uid']);
            $stage = $sbr->initFromStage($ev0['stage_id']);
            
            if($stage->status == sbr_stages::STATUS_ARBITRAGED) {
                $msg = "����������� ��� � ���, ��� ����������� <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>] ������� ��� <a href='{$link_feedback}'>�����</a> �� ������ �<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>�:<br/></br>";
            } else {    
                $msg = "����������� <a href='{$userlink}'>{$ev0['f_uname']} {$ev0['f_usurname']}</a> [<a href='{$userlink}'>{$ev0['f_login']}</a>] �������� ������ �<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>� �� ����� ������� � ������� ��� <a href='{$link_feedback}'>�����</a>:<br/></br>";
            }
        } else if($ev0['emp_feedback_id']) {
            $r = 'f_';
            $userlink = $GLOBALS["host"]."/users/".$ev0['e_login'];
            $feedback = sbr_meta::getFeedback($ev0['emp_feedback_id']);
            $uniq_id = $feedback['id'] * 2 + 1;
            $link_feedback = "{$GLOBALS["host"]}/users/{$ev0['f_login']}/opinions/#p_{$uniq_id}";
            $msg = "�������� <a href='{$userlink}'>{$ev0['e_uname']} {$ev0['e_usurname']}</a> [<a href='{$userlink}'>{$ev0['e_login']}</a>] �������� ������ �<a href='{$url}?site=Stage&id={$ev0['stage_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>� �� ����� ������� � ������� ��� <a href='{$link_feedback}'>�����</a>:<br/><br/>";
            //$msg = "�������� ��� � ���, ��� ������������ <a href=\"{$userlink}\">{$ev0['e_uname']}</a> <a href=\"{$userlink}\">{$ev0['e_usurname']}</a> [<a href=\"{$userlink}\">{$ev0['e_login']}</a>]";
        }
        $sbr_link = "������ �<a href='{$url}?site=Stage&id={$ev0['own_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$stage_name}</a>� (������ �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>{$ev0['sbr_name']}</a>�)";
        if(!$feedback) return;
        $opi_path = $GLOBALS['host'].'/users/'.$ev0[$r.'login'].'/opinions/?from=norisk';
        
        $msg .= "�{$feedback['descr']}�.";
        
        //$msg .= " �������(-a) ��� ������������ �� ������� ��� ����� � {$sbr_link}:<br/><br/>---<br/>�{$feedback['descr']}�<br/>---<br/>";
        //$msg .= "<br/>�� ������ ����������� ������������ �� ������� <a href='{$opi_path}{$this->_addUrlParams($r == 'e_' ? 'e' : 'f', '&')}'>��������</a> � ����� ��������.";
        //if($ev0['emp_feedback_id']) {
        //    $msg .= "<br/><br/>����������, ��� �� ������ ��������������� ������� �������������� - <a href='{$GLOBALS['host']}/service/{$this->_addUrlParams($r == 'e_' ? 'e' : 'f')}'>���������� ������������</a> �� ������������� �� ������� ������� ��� �����.";
        //}
        
        $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'norisk_robot')));
        $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
        $this->SmtpMail('text/html');
    }
    
    /**
     * ����������� � ���, ��� �������� ����� �������� � ���.
     * ������������ ��������� ���, ���� �� ������������� � ���� ��������� (��. ������� � ������� � ����������)
     *
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrAddDoc($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
        $sbr_link_e = " �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('e', '&')}'>{$sbr_name}</a>�";
        $sbr_link_f = " �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$sbr_name}</a>�";
        if(!($doc = sbr_meta::getDoc($ev0['new_val'], false))) return 0;
        if($doc['owner_role']!=0) return 0; // ������ ���� ����� ��������.
        $doc_link_e = " �<a href='{$url}?site=Stage&id={$ev0['stage_id']}&doc={$ev0['new_val']}{$this->_addUrlParams('e', '&')}'>{$doc['name']}</a>�";
        $doc_link_f = " �<a href='{$url}?site=Stage&id={$ev0['stage_id']}&doc={$ev0['new_val']}{$this->_addUrlParams('f', '&')}'>{$doc['name']}</a>�";
        $this->subject = "�������� ����� �������� �� ���������� ������ (������ {$ev0['sbr_name']})";
        $e = 'e_';
        $f = 'f_';
        //$msg[$e] = "�������� ������� ����������� ������ �������� �������� {$doc_link_e} � ������ {$sbr_link_e}.
        $msg[$e] = "� ������ {$sbr_link_e} �������� �������� {$doc_link_e}.
        �� ������ ������������ � ����� <a href='https://feedback.fl.ru/{$this->_addUrlParams('e', '?')}'>�������� ���������� ���������� ������</a>.";
        //$msg[$f] = "�������� ������� ����������� ������ �������� �������� {$doc_link_f} � ������ {$sbr_link_f}.
        $msg[$f] = "� ������ {$sbr_link_f} �������� �������� {$doc_link_f}.
        �� ������ ������������ � ����� <a href='https://feedback.fl.ru/{$this->_addUrlParams('f', '?')}'>�������� ���������� ���������� ������</a>.";
        $footer = 'norisk_robot';

        if($doc['type'] == sbr::DOCS_TYPE_ACT) {
            $sbr = sbr_meta::getInstanceLocal($ev0['e_uid']);
            $sbr->initFromId($ev0['sbr_id']);
            
            if($sbr->isNewVersionSbr()) {
                $this->subject = "����������� ���������� ������ {$sbr_name}";
                
                if($sbr->scheme_type == sbr::SCHEME_LC) {
                    $message  = "���������� ������ {$sbr_link_e} ��������� �� ����������� �����. � ������� ������������ � ������ � ��������� ������ �� ����� ��� �������� {$doc_link_e}.<br/><br/>";
                    $message .= "<i>�������� ��������</i>: ���������� ��������� �� ����� �� ���������. ��� ������ � ����� ��� ���������� �������� ��������������.<br/><br/>";
                    $message .= "��������� ���������� �� ������� ���������� ���������� ������ ��������� � ��������������� <a href='https://feedback.fl.ru/' target='_blank'>������� ������</a>.";
                    
                    $msg[$e]  = $message;
                    $msg[$f]  = $message;
                } elseif($sbr->scheme_type == sbr::SCHEME_PDRD2){
                    //@todo: ��� �� ���������� ����� ������ sbr::getContractNum($ev0['sbr_id'], $ev0['scheme_type'], $ev0['posted']) ������� ����� ��� ������� � �������� ���������
                    $doc_tz = " �<a href='{$url}?site=Stage&id={$ev0['stage_id']}&doc=" . ($ev0['new_val']+1 ) . "{$this->_addUrlParams('e', '&')}'>����������� ������� �� �������� {$sbr_name}</a>�";
                    $message  = "���������� ������ {$sbr_link_e} ��������� �� ����������� �����. � ����������� � ������ � ��������� ������ �� ����� ��� �������� {$doc_link_e} � {$doc_tz}.<br/><br/>";
                    $message .= "��� ���� ����� �������� ������� �� ����������� ������, ��� ���������� ����������� ������ ��������� � 2-� �����������, ��������� � ��������� �� ����� �������� FL.ru: 129223, �. ������, �/� 33, ��� �����.";
                    
                    $msg[$e]  = $message;
                    $msg[$f]  = $message;
                }
                
            } else {
                $this->subject = "��������� ��� ���������� ���������� ������ �� ������� �{$ev0['sbr_name']}�";
                $msg[$e] = "
                ���� ���������� ������ �� ������� {$sbr_link_e} ��������� �� ����������� �����. � ������ ���������� ������� ��� �������� {$doc_link_e}.
                <br/><br/>
                ��� ���� ����� ������ ���� ���������� �����������, ��� ���������� ����������� ������ �������� � 2-� �����������,
                ��������� � ��������� �� ����� �������� FL.ru: 129223, �. ������, �/� 33, ��� �����.
                <br/><br/>
                ����������, �������� �������� �� ��, ��� ������ ����� ���������� ����������� ������ ����� ��������� ���� ���������� ����������. ������� ������������ ����������� � ����� � �������.
                ";

                if(!empty($events[1])) {
                    $ev1 = $events[1];
                    $_doc = sbr_meta::getDoc($ev1['new_val'], false);
                    if($_doc['type'] == sbr::DOCS_TYPE_WM_APPL || $_doc['type'] == sbr::DOCS_TYPE_YM_APPL) {
                        $_doc_link_f = " �<a href='{$url}?site=Stage&id={$ev1['stage_id']}&doc={$ev1['new_val']}{$this->_addUrlParams('f', '&')}'>{$_doc['name']}</a>�";
                    }
                }
                if($doc_link_f && $_doc_link_f) {
                    $doc_string_f = "���� ��������� {$doc_link_f} � {$_doc_link_f}";
                    $print_info_f = "��������� � ����� ����������, ��� � � ����";
                } else {
                    $doc_string_f = "��� �������� {$doc_link_f}";
                    $print_info_f = "������ �������� � 2-� �����������";
                }

                $msg[$f] = "
                ���������� ������ �� ������� {$sbr_link_f} ��������� �� ����������� �����. � ������ ���������� ������� {$doc_string_f}.
                <br/><br/>
                ��� ���� ����� ��� ���� ����������� ���� ������, ��� ���������� ����������� {$print_info_f},
                ��������� � ��������� �� ����� �������� FL.ru: 129223, �. ������, �/� 33, ��� �����.
                <br/><br/>
                ����������, �������� �������� �� ��, ��� ������ ����� ���������� ��� ������ ����� ��������� ���� ���������� ����������. ������� ������������ ����������� � ����� � �������.
                ";
                $footer = 'norisk_robot';
            }
        }

        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_FRL) == sbr::EVROLE_FRL)
            $rs[] = $f;
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_EMP) == sbr::EVROLE_EMP)
            $rs[] = $e;
        if($rs) {
            foreach($rs as $r) {
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg[$r], array('header'=>'simple', 'footer'=>$footer)));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        }
    }

    /**
     * ����������� � ���, ��� �������� ������ �� ���.
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrDelDoc($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        if(!($doc = sbr_meta::getDoc($ev0['old_val'], false))) return 0;
        $doc_link = " �{$doc['name']}�";
        $this->subject = "������ �������� �� ���������� ������";
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_FRL) == sbr::EVROLE_FRL)
            $rs[] = 'f_';
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_EMP) == sbr::EVROLE_EMP)
            $rs[] = 'e_';
        if($rs) {
            foreach($rs as $r) {
                $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
                $sbr_link = " �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}'>{$sbr_name}</a>�";
                $msg = "������������� ������ �������� {$doc_link} �� ������ {$sbr_link}";
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'simple')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        }
    }

    /**
     * ����������� �� ��������� ������� ���������.
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrDocStatusChanged($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        if(!($doc = sbr_meta::getDoc($ev0['own_id'], false))) return 0;
        $this->subject = "��������� ������ ��������� � ���������� ������";
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_FRL) == sbr::EVROLE_FRL)
            $rs[] = 'f_';
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_EMP) == sbr::EVROLE_EMP)
            $rs[] = 'e_';
        if($rs) {
            $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
            foreach($rs as $r) {
                $sbr_link = " �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}'>{$sbr_name}</a>�";
                $doc_link = " �<a href='{$url}?site=Stage&id={$ev0['stage_id']}&doc={$ev0['own_id']}{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}'>{$doc['name']}</a>�";
                $msg = "������������� ���������� ������ ������� ������ ��������� {$doc_link} � ������ {$sbr_link}: ";
                $msg .= '<br/><br/><strong>' . sbr::$docs_ss[$ev0['old_val']][0] . ' &mdash; ' . sbr::$docs_ss[$ev0['new_val']][0] . '</strong>';
                $msg .= "<br/><br/>��������� � <a href=\"{$GLOBALS['host']}/contacts/?from=norisk{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}\">���������� ���������� ������</a>, ����� �������� �����������.";
                
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'simple')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        }
    }

    /**
     * ����������� � ��������� ������� � ���������.
     *
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrDocAccessChanged($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        if(!($doc = sbr_meta::getDoc($ev0['own_id'], false))) return 0;
        $this->subject = "���������� ��������� ��������� � ���������� ������";
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_FRL) == sbr::EVROLE_FRL)
            $rs[] = 'f_';
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_EMP) == sbr::EVROLE_EMP)
            $rs[] = 'e_';
        if($rs) {
            $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
            foreach($rs as $r) {
                $sbr_link = " �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}'>{$sbr_name}</a>�";
                $doc_link = " �<a href='{$url}?site=Stage&id={$ev0['stage_id']}&doc={$ev0['own_id']}{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}'>{$doc['name']}</a>�";
                $msg = "������������� ���������� ������ ������� ������� ������� � ��������� ��������� {$doc_link} � ������ {$sbr_link}: ";
                $msg .= '<br/><br/><strong>' . sbr::$docs_access[$ev0['old_val']][0] . ' &mdash; ' . sbr::$docs_access[$ev0['new_val']][0] . '</strong>';
                $msg .= "<br/><br/>��������� � <a href=\"{$GLOBALS['host']}/contacts/?from=norisk{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}\">���������� ���������� ������</a>, ����� �������� �����������.";
                
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'simple')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        }
    }

    /**
     * ����������� � ���, ��� ���� ��������� ������������.
     * @param array $events   ���������� �� �������� (���� ������� ����������, �� �������� ��������� ���������).
     */
    function SbrDocReload($events) {
        $ev0 = $events[0];
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        if(!($doc = sbr_meta::getDoc($ev0['own_id'], false))) return 0;
        if($doc['owner_role']!=0) return 0; // ������ ���� ����� ��������.
        $this->subject = "������������ ���� ��������� � ������� ����������� ������";
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_FRL) == sbr::EVROLE_FRL)
            $rs[] = 'f_';
        if($ev0['foronly_role']===NULL || ((int)$ev0['foronly_role'] & sbr::EVROLE_EMP) == sbr::EVROLE_EMP)
            $rs[] = 'e_';
        if($rs) {
            $sbr_name = sbr_meta::getNameForMail($ev0, 'sbr');
            foreach($rs as $r) {
                $sbr_link = " �<a href='{$url}?id={$ev0['sbr_id']}{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}'>{$sbr_name}</a>�";
                $doc_link = " �<a href='{$url}?site=Stage&id={$ev0['stage_id']}&doc={$ev0['own_id']}{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}'>{$doc['name']}</a>�";
                $msg = "������������� ���������� ������ ������������ ���� ��������� {$doc_link} � ������ {$sbr_link}.";
                $msg .= "<br/><br/>��������� � <a href=\"{$GLOBALS['host']}/contacts/?from=norisk{$this->_addUrlParams($r = 'e_' ? 'e' : 'f', '&')}\">����������</a>, ����� �������� �����������.";
                
                $this->message = $this->splitMessage($this->GetHtml($ev0[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'simple')));
                $this->recipient = $ev0[$r.'uname']." ".$ev0[$r.'usurname']." [".$ev0[$r.'login']."] <".$ev0[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        }
    }

    /**
     * ����������� � ����� ����������� � ������� � ����� ���.
     *
     * @param array $ids   �������������� ����� ���������.
     * @param resource $connect   ������� ���������� � ��.
     * @return integer ���������� ������������ �����������.
     */
    function SbrNewComment($ids, $connect = NULL) {
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/sbr.php");
        if(!($comments = sbr_meta::getComments4Sending($ids, $connect)))
            return NULL;

        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        foreach($comments as $comment) {
            $this->subject = "����� ����������� � ���������� ������ �� ������� �{$comment['sbr_name']}�";
            $rs = array();
            $msg = '';
            
            //$sbr_num = sbr::getContractNum($comment['sbr_id'], $comment['scheme_type']);
            $stage_name = sbr_meta::getNameForMail($comment);
            if($comment['is_admin']=='t') {
                $this->subject = "�������� ������� ����������� � ���������� ������ �{$comment['sbr_name']}�";
                $msg = "����������� ��� � ���, ��� � ������ �<a href='{$url}?site=Stage&id={$comment['stage_id']}' target='_blank'>{$stage_name}</a>� �������� ������� ����� <a href='{$url}?site=Stage&id={$comment['stage_id']}{$this->_addUrlParams(($r == 'e_' ? 'e' : 'f'), '&')}#c_{$comment['id']}'>�����������</a>:<br/>";
                
                $rs[] = 'f_';
                $rs[] = 'e_';
            } else if($comment['user_id'] == $comment['e_uid']) {
                $userlink = $GLOBALS["host"]."/users/".$comment['e_login'];
                $msg = "����������� ��� � ���, ��� � ������ �<a href='{$url}?site=Stage&id={$comment['stage_id']}' target='_blank'>{$stage_name}</a>� �������� <a href=\"{$userlink}\">{$comment['e_uname']} {$comment['e_usurname']}</a> [<a href=\"{$userlink}\">{$comment['e_login']}</a>] ������� ����� <a href='{$url}?site=Stage&id={$comment['stage_id']}{$this->_addUrlParams(($r == 'e_' ? 'e' : 'f'), '&')}#c_{$comment['id']}'>�����������</a>:<br/>";
                
                $rs[] = 'f_';
            } else if($comment['user_id'] == $comment['f_uid']) {
                $userlink = $GLOBALS["host"]."/users/".$comment['f_login'];
                $msg = "����������� ��� � ���, ��� � ������ �<a href='{$url}?site=Stage&id={$comment['stage_id']}' target='_blank'>{$stage_name}</a>� ����������� <a href=\"{$userlink}\">{$comment['f_uname']} {$comment['f_usurname']}</a> [<a href=\"{$userlink}\">{$comment['f_login']}</a>] ������� ����� <a href='{$url}?site=Stage&id={$comment['stage_id']}{$this->_addUrlParams(($r == 'e_' ? 'e' : 'f'), '&')}#c_{$comment['id']}'>�����������</a>:<br/>";
                
                $rs[] = 'e_';
            }
            if($rs) {
                foreach($rs as $r) {
                    /*$sbr_link = "������ �<a href='{$url}?site=Stage&id={$comment['stage_id']}{$this->_addUrlParams(($r == 'e_' ? 'e' : 'f'), '&')}'>{$comment['stage_name']}</a>� ������� �<a href='{$url}?id={$comment['sbr_id']}{$this->_addUrlParams(($r == 'e_' ? 'e' : 'f'), '&')}'>{$comment['sbr_name']}</a>�";
                    $msg .= "
                    <a href='{$url}?site=Stage&id={$comment['stage_id']}{$this->_addUrlParams(($r == 'e_' ? 'e' : 'f'), '&')}#c_{$comment['id']}'>����� �����������</a> � {$sbr_link}:
                    <br/>-----<br/>
                    �" . reformat($comment['msgtext'], 0, 0, 0, 1) . "�
                    <br/>-----<br/>
                    ";*/
                    $msg_send = $msg . "<br/>�".reformat($comment['msgtext'], 0, 0, 0, 1)."�.<br/>";
                    
                    $this->message = $this->splitMessage($this->GetHtml($comment[$r.'uname'], $msg_send, array('header'=>'simple', 'footer'=>'norisk_robot')));
                    $this->recipient = $comment[$r.'uname']." ".$comment[$r.'usurname']." [".$comment[$r.'login']."] <".$comment[$r.'email'].">";
                    $this->send('text/html');
                }
            }
        }

        return $this->sended;
    }

    /**
     * ���������� ����������� � ����� ���������� � ����� ��� ������� ��������.
	 * ��������� plproxy-mail
     * 
     * @param   array      $params    ������ �� PgQ, TO-������ �����������; FROM-����� �����������
     * @param   string     $msg       ����� ���������
     */
	function SendMasssending($params, $from, $to, $msg)
	{
	    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
	    
        $uid_from = intval($from);
        $uids_to = explode(",",preg_replace("/[\{\}]/","",$to));

        if(!$uid_from || !is_array($uids_to)) return;

		$from = new users;
		$from->GetUserByUID($uid_from);

        $this->subject = "����� ��������� �� FL.ru";
  		$msg_text = "
<a href='{$GLOBALS['host']}/users/{$from->login}'>{$from->uname} {$from->usurname}</a> [<a href='{$GLOBALS['host']}/users/{$from->login}{$this->_addUrlParams('b')}'>{$from->login}</a>]
�������(�) ��� ����� ��������� �� ����� FL.ru.<br />
<br />
---------- 
<br />
".$this->ToHtml(LenghtFormatEx(strip_tags($msg), 300))."
<br />
<br />
<br />
<a href='{$GLOBALS['host']}/contacts/?from={$from->login}{$this->_addUrlParams('b', '&')}'>{$GLOBALS['host']}/contacts/?from={$from->login}</a>
<br />
<br />
------------
";
        foreach($uids_to as $uid_to) {
    		$to = new users;
    		$to->GetUserByUID($uid_to);
		
    		if (substr($to->subscr, 0, 1) != '1' || !$to->email || $to->is_banned == '1') {
    			continue;
    		}

	    	if (!$this->Connect())
    			return "���������� ���������� � SMTP ��������";
            if ($to->email && (substr($to->subscr, 12, 1) == '1')) {
    			$this->recipient = $to->uname." ".$to->usurname." [".$to->login."] <".$to->email.">";
    			$this->message = $this->GetHtml($to->uname, $msg_text, array('header' => 'default', 'footer' => 'default'), array('login'=>$to->login));
    			$this->SmtpMail('text/html');
            }
        }

        $this->subject = "���� �������� �� FL.ru ������ ���������";
   		$this->recipient = $from->uname." ".$from->usurname." [".$from->login."] <".$from->email.">";
   		$msg_text = $this->ToHtml($msg);
   		
        $body = 
        "���� ������ �� �������� ���� ����������� � �������� ������������ ����� FL.ru. 
         ����������� ��������� ���� ������������� ����� ���������� ��������� ���������� ����������:</br>
         ---<br/>
         {$msg_text}<br/>
         ---<br/>";
        
   		$this->message = $this->GetHtml($from->uname, $body, array('header'=>'simple', 'footer' => 'simple'));
        
   		$this->SmtpMail('text/html');

	}


    /**
     * ���������� ����������� � ����� ���������� � ����� ��� �������� �������������.
	 * ��������� plproxy-mail
     *
     * @param   array      $params    ������ �� PgQ, TO-������ �����������; FROM-����� �����������
     * @param   string     $msg       ����� ���������
     */
	function SendAdminMessage($params)
	{
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/messages.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        $messObj = new messages;
        
        $message_id = $params;
		if (!($message = $messObj->GetMessage($message_id)))
			return "���� ��������� �����������.";

		$this->subject = "����� ��������� �� ������� FL.ru";

		$msg_text = reformat2($message['msg_text'], 100);
		$attaches = array();
		if ($message['attach']) {
			foreach($message['attach'] as $a) {
				$attaches[] = new CFile($a['path'].$a['fname']);
			}
			$attaches = $this->CreateAttach($attaches);
		}

		if (!$this->Connect())
			return "���������� ���������� � SMTP ��������";
			
        $from = new users;
		$from->GetUserByUID( $message['from_id'] );
		$parse  = $from->login == 'admin';
		$header = $parse ? 'none' : 'default';

		for ($i=0; $users = $messObj->GetZeroMessageUsers($message['from_id'], $message_id, 1000, $i * 1000); $i++) {
			foreach ($users as $ikey=>$user) {
			    if ( $parse ) {
			    	$msg_text = reformat2($message['msg_text'], 100);
			    	$msg_text = preg_replace( "/%USER_NAME%/", $user['uname'], $msg_text );
                    $msg_text = preg_replace( "/%USER_SURNAME%/", $user['usurname'], $msg_text );
                    $msg_text = preg_replace( "/%USER_LOGIN%/", $user['login'], $msg_text );
			    }
			    
				if (!$user['email'] || substr($user['subscr'], 7, 1) == '0') continue;
				$this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
				$this->message = $this->GetHtml( $user['uname'], $msg_text, array('header' => 'none', 'footer' => 'none') );
				$this->SmtpMail('text/html', $attaches);
			}
		}
		return '';
	}
	
	/**
	 * ����� ������� ������������
	 *
	 * @param array $events
	 */
	function newPaidAdvice($ids, $connect = NULL) {
	    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        $f_user = new users();
        $t_user = new users();
        
        $this->subject = "��� �������� �����";
        
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        
        foreach ( $ids as $id ) {
            list($user_from, $user_to) = explode("-", $id);
            $f_user->GetUserByUID($user_from);
            $t_user->GetUserByUID($user_to);
            if (!$t_user->email || substr($t_user->subscr, 14, 1) != '1') continue;
            
            $to_user = get_object_vars($t_user);
            $from_user = get_object_vars($f_user);
                
            $message  = (is_emp($from_user['role'])?"��������":"���������") . " {$from_user['uname']} {$from_user['usurname']} [{$from_user['login']}] ������� ��� �����. ";
            $message .= "�� ������ ������������ � ���, � ����� ������� ��� ���������� �� ������� ������ �� ������� �������� � ����� ��������.";
                
            $this->message   = $this->GetHtml( $to_user['uname'], $message, array('header'=>'default', 'footer'=>'default'), array('login' => $to_user['login']));
            $this->recipient = $to_user['uname'].' '.$to_user['usurname'].' ['.$to_user['login'].'] <'.$to_user['email'].'>';
            $this->SmtpMail('text/html');
        }
	}
	
	/**
	 * ��������� ������� ������� ������������
	 *
	 * @param array $events
	 */
	function changePaidAdvice($ids, $connect = NULL) {
	    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
	    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/paid_advices.php';
        $f_user = new users();
        $t_user = new users();
        
        foreach ( $ids as $id ) {
            list($user_from, $user_to, $mod_status, $id_advice, $status) = explode("-", $id);
            $f_user->GetUserByUID($user_from);
            $t_user->GetUserByUID($user_to);
            if (!$t_user->email || substr($t_user->subscr, 14, 1) != '1') continue;
            
            $to_user = get_object_vars($t_user);
            $from_user = get_object_vars($f_user);
            
            if($mod_status == paid_advices::MOD_STATUS_ACCEPTED ) {
                $this->subject = "��� ����� ������ ���������";  
                $message  = "����� �� ". (is_emp($from_user['role'])?"���������":"����������") . " {$from_user['uname']} {$from_user['usurname']} [{$from_user['login']}], ������������ ���� �� ��������, ������� �����������.";
                $message .= " ��� ���� ����� ����� �������� �� ������� �������� ������ �������� � ���� ����� ���� ������������� �����, ��� ���������� ��� <a href='{$GLOBALS['host']}/users/{$to_user['login']}/opinions/{$this->_addUrlParams('b')}#n_{$id_advice}'>��������</a>.";        
            } else if($mod_status == paid_advices::MOD_STATUS_DECLINED && $status == paid_advices::STATUS_BLOCKED) {
                $this->subject = "����� ������ �����������";
                $paid_advice = new paid_advices();
                $advice = $paid_advice->getAdviceById($id_advice);
                $message = 
                "�����, ������������ ���� �� ���������, ��� ������ �� �������: 
                <br/>-----<br/>
                ".nl2br($advice['mod_msg'])."
                <br/>-----<br/><br/>
                ���������� �� ���������!<br/><br/>
                �� ���� ����������� �������� �� ������ ���������� � ���� <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>������ ���������</a>.";
            } else if($mod_status == paid_advices::MOD_STATUS_DECLINED ) {
                $this->subject = "��� ����� �� ������ ���������";
                $message  = "����� �� ". (is_emp($from_user['role'])?"���������":"����������") . " {$from_user['uname']} {$from_user['usurname']} [{$from_user['login']}], ������������ ���� �� �������� �����������, �� �������.";
                $message .= " ��� ���������� ��������� �������, ��������� ������������ � �������� ��������� ������ ��� �������� ������. ����� ����� �� ������ ��������� ����� �� ��������� ���������.";
            }
            
            $this->message   = $this->GetHtml( $to_user['uname'], $message, array('header'=>'default', 'footer'=>'default'), array('login' => $to_user['login']));
            $this->recipient = $to_user['uname'].' '.$to_user['usurname'].' ['.$to_user['login'].'] <'.$to_user['email'].'>';
            $this->SmtpMail('text/html');
        }
	}
    
    
    /**
     * �������� ��� ���������� �����������, ����������� �� hourly.php
     * 
     * @return integer  ���������� ������������� ���������� ��������
     */
    function noActiveFreelancers() {
        $DB = new DB('master');
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php';
        
        $message = "<p>
&nbsp;&nbsp;&nbsp;&nbsp;�� ��������, ��� �� ����� �� �������� �� <a href='{$GLOBALS['host']}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancers_comeback'>FL.ru</a>.<br/> 
&nbsp;&nbsp;&nbsp;&nbsp;<br/>���� �� �� ������ ����� �������� ��� ����, ����������� ���������� <a href='{$GLOBALS['host']}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancers_comeback'>������� PRO</a>: ���� ���������� ���������� ����� ����� ���� �������������, � ��� ������ ��������� � ���� � ����� �����.
&nbsp;&nbsp;&nbsp;&nbsp;<br/>��� ��������, �� ����� ����������� ����� 40 000 �������� � �����, � ������� ��������� ������� ���������� 25000 ������. ���������, ������ �� ���� �������� ����� ��� ���������.
&nbsp;&nbsp;&nbsp;&nbsp;����������, ��� ������ ������ �� <a href='{$GLOBALS['host']}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancers_comeback'>FL.ru</a> ����� ������. �� ������ ����������� �� ���������� �������� ����������� ������������� ��� ���������� �� ���� ��������� ��������� <a href='{$GLOBALS['host']}/promo/freetray/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancers_comeback'>Free Tray</a>.<br/>
&nbsp;&nbsp;&nbsp;&nbsp;<a href='{$GLOBALS['host']}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancers_comeback'>������� �� FL.ru</a>
</p>";
        $this->subject   = "���������� ��� ����� �������� FL.ru";
        $this->recipient = '';
        $this->message   = $this->GetHtml( 
            '', 
            $message,
            array( 'header' => 'default', 'footer' => 'feedback_default' ),
            array( 'login' => '', 'utm_campaign' => 'freelancers_comeback', 'target_footer' => 1 )
        );
        $msgid = $this->send("text/html");
        if ( !$msgid ) {
            return 0;
        }
        $i = 0;
        $this->recipient = array();
        $res = $DB->query("SELECT * FROM freelancer WHERE is_active = FALSE AND is_banned = B'0'");
        while ( $user = pg_fetch_assoc($res) ) {
            if ( !$user['subscr'][7] ) {
                continue;
            }
            $this->recipient[] = array(
                'email' => "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>",
                'extra' => array(
                    'USER_NAME'    => $user['uname'],
                    'USER_SURNAME' => $user['usurname'],
                    'USER_LOGIN'   => $user['login']
                )
            );
            if ( ++$i >= 10000 ) {
                $this->bind($msgid);
                unset($this->recipients);
                $this->recipient = array();
                $i = 0;
            }
        }
        if ( $i ) {
            $this->bind($msgid);
        }
        unset($this->recipients);
        return $i;
    }
    
    
    /**
     * @todo: ��������� � hourly, ��� ������������� ��������� �����
     * 
     * �������� ��� ���������� �������������, ����������� �� hourly.php
     * 
     * @return integer  ���������� ������������� ���������� ��������
     */
    function noActiveEmployers() {
        $DB = new DB('master');
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php';
        $eHost = $GLOBALS['host'];        
        $message = "<p>�� ��������, ��� �� ����� �� �������� �� <a href=\"{$eHost}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=clients_comeback\" target=\"_blank\">FL.ru</a>. ��� ��������, ����� ������� ���� ����� 1 �������� ���������������� ������������ � ����������, ���-��������, �������������, �������������, ������������, ������������, ���������� � �������������. </p>
<p>����������, ��� �������� �� FL.ru ����� � ������. ������ � ����� ������� <a href=\"{$eHost}/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_content=manager&utm_campaign=clients_comeback\" target=\"_blank\">���������</a>, ������� ������� �� ���� ��� ����������� �� ������� ������� ��� �����������, � ������ �<a href=\"{$eHost}/promo/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_content=manager&utm_campaign=clients_comeback\" target=\"_blank\">���������� ������</a>� ��������� ������ ������������ ������ �������������� � ������������ �� ���� ������ ���������� ����� ��������.</p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"{$eHost}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=clients_comeback\" target=\"_blank\">������� �� FL.ru</a></p>";
        $this->subject   = "���������� ��� ����� �������� FL.ru";
        $this->recipient = '';
        $this->message   = $this->GetHtml( 
            $user['uname'], 
            $message,
            array( 'header' => 'default', 'footer' => 'feedback_default' ),
            array( 'login' => '', 'utm_campaign' => 'clients_comeback', 'target_footer' => 1 )
        );
        $msgid = $this->send("text/html");
        if ( !$msgid ) {
            return 0;
        }
        $i = 0;
        $this->recipient = array();
        $res = $DB->query("SELECT * FROM employer WHERE is_active = FALSE AND is_banned = B'0'");
        while ( $user = pg_fetch_assoc($res) ) {
            if ( !$user['subscr'][7] ) {
                continue;
            }
            $this->recipient[] = array(
                'email' => "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>",
                'extra' => array(
                    'USER_NAME'    => $user['uname'],
                    'USER_SURNAME' => $user['usurname'],
                    'USER_LOGIN'   => $user['login']
                )
            );
            if ( ++$i >= 10000 ) {
                $this->bind($msgid);
                unset($this->recipients);
                $this->recipient = array();
                $i = 0;
            }
        }
        if ( $i ) {
            $this->bind($msgid);
        }
        unset($this->recipients);
        return $i;
    }
    
    
    /**
     * �������� ��� ����������� � ������������� ��������. ����������� � hourly.php
     * 
     * @return integer  ���������� ������������� ���������� ��������
     */
    function withoutProfileFrelancers() {
        $DB = new DB('master');
        $this->recipient = '';
        $this->subject   = "��� �������� ������ ������� �� FL.ru";//"����������� �� FL.ru: �� ������� ����������!";
        $message = "<p>
&nbsp;&nbsp;&nbsp;&nbsp;�� ��������, ��� � ��� �� �������� ������ ����������. �� ����������, 95% ������������� �������� �������� �� ����������� � ��������� ��������������, ����������� ��������� � ������ ������. ����� �� �� �������������� ������ ���������� � ���� ��� � �����������, �� �������������� ������� ����� �����, ��������� �� ����� � ������ ���� ������� ��� ��������������� � � ����� ������ ������� ������������ �� ��������������.<br/><br/>            
&nbsp;&nbsp;&nbsp;&nbsp;�� ����������� ��� ��������� ��������� ��������� ����������� ���� �����. ��� ������� ��� ������� ����� ���������� � �������� ������.<br/><br/>
&nbsp;&nbsp;&nbsp;&nbsp;������ ���������� �� ���������� ��������� ��������� <a href='https://feedback.fl.ru/'>�����</a>. �� ������ ������������ � ��� � ����� �����<br/><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<a href='{$GLOBALS['host']}'>������� �� FL.ru</a>";
        $this->message = $this->GetHtml( 
            '', 
            $message,
            array( 'header' => 'default', 'footer' => 'default' ),
            array( 'login' => '', 'utm_campaign' => 'freelancers_profile', 'target_footer' => 1 )
        );
        $msgid = $this->send("text/html");
        if ( !$msgid ) {
            return 0;
        }
        $i = 0;
        $this->recipient = array();
        $res = $DB->query("
            SELECT 
                uid, login, uname, usurname, email, subscr 
            FROM 
                freelancer u
            LEFT JOIN 
                portfolio p ON u.uid = p.user_id
            WHERE
                p.id IS NULL
                AND is_active = TRUE
                AND is_banned = B'0'
        ");
        while ( $user = pg_fetch_assoc($res) ) {
            if ( !$user['subscr'][7] ) {
                continue;
            }
            $this->recipient[] = array(
                'email' => "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>",
                'extra' => array(
                    'USER_NAME'    => $user['uname'],
                    'USER_SURNAME' => $user['usurname'],
                    'USER_LOGIN'   => $user['login']
                )
            );
            if ( ++$i >= 20000 ) {
                $this->bind($msgid);
                $this->recipient = array();
                $i = 0;
            }
        }
        if ( $i ) {
            $this->bind($msgid);
        }
        $this->recipient = array();
        return $i;
    }
       
    
    /**
     * �������� ��� ������������� � ������������� ��������. ����������� � hourly.php
     * 
     * @return integer  ���������� ������������� ���������� ��������
     */
    function withoutProfileEmployers() {
        $DB = new DB('master');
        $this->recipient = '';
        $this->subject   = "����������� �� FL.ru: ����������, ��������� ���� �������";
        $message = "<p>
&nbsp;&nbsp;&nbsp;&nbsp;�� ��������, ��� � ��� �� ��������� �������� �������. ������ ������ �� ������������� ������� ������������ �������� �������� ������������� � ���������������� ����������. �� ����������� ��� �������� ������ ���������� � ����. ��� ������� ��� ������� ����� ������� ����������� �� ���� �������.<br />
&nbsp;&nbsp;&nbsp;&nbsp;������ ���������� �� ���������� ������� ��������� <a href='https://feedback.fl.ru/'>�����</a>. �� ������ ������������ � ��� � ����� �����.<br />
&nbsp;&nbsp;&nbsp;&nbsp;<a href='{$GLOBALS['host']}'>����� ���� ������ ��� �� FL.ru</a>!
</p>";
        $this->message = $this->GetHtml( 
            '', 
            $message,
            array( 'header' => 'default', 'footer' => 'default' ),
            array( 'login' => $user['login'], 'utm_campaign' => 'clients_profile', 'target_footer' => 1 )
        );
        $msgid = $this->send("text/html");
        if ( !$msgid ) {
            return 0;
        }
        $i = 0;
        $this->recipient = array();
        $res = $DB->query("
            SELECT 
                uid, login, uname, usurname, email, subscr 
            FROM 
                employer u
            WHERE 
                is_active = TRUE
                AND is_banned = B'0'
                AND (birthday IS NULL OR birthday = '1910-01-01')
                AND (country IS NULL OR country = 0)
                AND (site IS NULL OR site = '')
                AND (icq IS NULL OR icq = '')
                AND (jabber IS NULL OR jabber = '')
                AND (phone IS NULL OR phone = '')
                AND (ljuser IS NULL OR ljuser = '')
                AND (skype IS NULL OR skype = '')
                AND (second_email IS NULL OR second_email = '')
                AND (resume IS NULL OR resume = '')
                AND (compname IS NULL OR compname = '')
                AND (logo IS NULL OR logo = '')
                AND (company IS NULL OR company = '')
        ");
        while ( $user = pg_fetch_assoc($res) ) {
            if ( !$user['subscr'][7] ) {
                continue;
            }
            $this->recipient[] = array(
                'email' => "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>",
                'extra' => array(
                    'USER_NAME'    => $user['uname'],
                    'USER_SURNAME' => $user['usurname'],
                    'USER_LOGIN'   => $user['login']
                )
            );
            if ( ++$i >= 20000 ) {
                $this->bind($msgid);
                $this->recipient = array();
                $i = 0;
            }
        }
        if ( $i ) {
            $this->bind($msgid);
        }
        $this->recipient = array();
        return $i;
    }
    
    
    /**
     * �������� �������������, ������� ������������������ ����� 30 ���� �����
     * (���������� �� nsync)
     * 
     * @param  integer $msgid       id ������� ���������
     * @param  integer $spamid      id �������� ��� NULL, ���� �������� ����� �������
     * @param  array   $recipients  ������ � uid ������������� ��� ��������
     * @return integer              id ��������� ��� 0 � ������ ������
     */
    public function empRegLess30($msgid, $spamid, $recipients ) {
       $subject = '��� ������ ����� ����������� �� FL.ru';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }

 
    /**
     * �������� �����������, ������� ������������������ �� ����� ����� 30 ���� ����� � �� ������ ������� ���
     * (���������� �� nsync)
     * 
     * @param  integer $msgid       id ������� ���������
     * @param  integer $spamid      id �������� ��� NULL, ���� �������� ����� �������
     * @param  array   $recipients  ������ � uid ������������� ��� ��������
     * @return integer              id ��������� ��� 0 � ������ ������
     */
    public function frlNotBuyPro($msgid, $spamid, $recipients) {
       $subject = '���� ������������ �� FL.ru!';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
    

    /**
     * �������� �����������, ������� ������ �������� ��� � �� ������ ������� ��� � ������� ������
     * (���������� �� nsync)
     * 
     * @param  integer $msgid       id ������� ���������
     * @param  integer $spamid      id �������� ��� NULL, ���� �������� ����� �������
     * @param  array   $recipients  ������ � uid ������������� ��� ��������
     * @return integer              id ��������� ��� 0 � ������ ������
     */
    public function frlBuyTestPro($msgid, $spamid, $recipients) {
       $subject = '������������� ������ �� FL.ru!';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
    
    
    /**
     * �������� �����������, ������� ������ �������� ��� � ����� ���� ������ ������� ������ �������
     * (���������� �� nsync)
     * 
     * @param  integer $msgid       id ������� ���������
     * @param  integer $spamid      id �������� ��� NULL, ���� �������� ����� �������
     * @param  array   $recipients  ������ � uid ������������� ��� ��������
     * @return integer              id ��������� ��� 0 � ������ ������
     */
    public function frlBuyProOnce($msgid, $spamid, $recipients) {
       $subject = '������������� ������ �� FL.ru!';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
    
    
    /**
     * �������� �����������, � ������� ����� 2 ������ ������������� ��� �� 6 ��� 12 �������.
     * (���������� �� nsync)
     * 
     * @param  integer $msgid       id ������� ���������
     * @param  integer $spamid      id �������� ��� NULL, ���� �������� ����� �������
     * @param  array   $recipients  ������ � uid ������������� ��� ��������
     * @return integer              id ��������� ��� 0 � ������ ������
     */
    public function frlEndingPro($msgid, $spamid, $recipients) {
       $subject = 'FL.ru: ��������� ��� � ��������� PRO';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
    
    
    /**
     * �������� ������������� �������������� ������� ������ ��� ������� � ������� 30 ����
     * (���������� �� nsync)
     * 
     * @param  integer $msgid       id ������� ���������
     * @param  integer $spamid      id �������� ��� NULL, ���� �������� ����� �������
     * @param  array   $recipients  ������ � uid ������������� ��� ��������
     * @return integer              id ��������� ��� 0 � ������ ������
     */
    public function empPubPrj30Days($msgid, $spamid, $recipients) {
       $subject = '��� ����� ����������� ����������� �� FL.ru';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
 
    
    /**
     * �������� ������������� �������� �������� � ������� 30 ����
     * (���������� �� nsync)
     * 
     * @param  integer $msgid       id ������� ���������
     * @param  integer $spamid      id �������� ��� NULL, ���� �������� ����� �������
     * @param  array   $recipients  ������ � uid ������������� ��� ��������
     * @return integer              id ��������� ��� 0 � ������ ������
     */
    public function empBuyMass30Days($msgid, $spamid, $recipients) {
       $subject = '��� ����� ����������� ����������� �� FL.ru';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
    
    
    /**
     * �������� ������������� �������� �� 30 ����, �� �� ������������� ��������.
     * (���������� �� nsync)
     * 
     * @param  integer $msgid       id ������� ���������
     * @param  integer $spamid      id �������� ��� NULL, ���� �������� ����� �������
     * @param  array   $recipients  ������ � uid ������������� ��� ��������
     * @return integer              id ��������� ��� 0 � ������ ������
     */
    public function empNotPubPrj($msgid, $spamid, $recipients) {
       $subject = '���������� ������� � ������� ������ ����� �����������';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
    
    
    /**
     * �������� �������������  � ������� �� ����� ���� 35+ �������� FM.
     * 
     * @param  integer $msgid       id ������� ���������
     * @param  integer $spamid      id �������� ��� NULL, ���� �������� ����� �������
     * @param  array   $recipients  ������ � uid ������������� ��� ��������
     * @return integer              id ��������� ��� 0 � ������ ������
     */
    public function empBonusFm($msgid, $spamid, $recipients) {
        $subject = '������ �� FL.ru';
        return $this->_nsyncMasssend($msgid, $spamid, $recipients, $subject);
    }
    
    /**
     * ����������� �� �������� ����� ��� ����������� � ������
     * 
     * @param mixed $mId ID ����� / ������ ID ������
     */
    function blogDeleteNotification( $mId = 0 ) {
        $sId    = !is_array($mId) ? array($mId) : $mId;
        $sQuery = 'SELECT u.uname, u.usurname, u.login, b.title, b.post_time 
            FROM blogs_msgs b
            INNER JOIN blogs_themes t ON t.thread_id = b.thread_id
            INNER JOIN users u ON u.uid = b.fromuser_id
            WHERE b.thread_id IN (?l) AND b.reply_to IS NULL';
        
        $aBlogs = $GLOBALS['DB']->rows( $sQuery, $sId );
        if ( $aBlogs ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
            
            foreach ( $aBlogs as $blog ) {
                $message = '������������, '. $blog['uname'] .' '. $blog['usurname'] .'

��������, �� ���������� ����� ��������� ���� ������� ��� ���� � ����������'. ( trim($blog["title"]) ? ' &laquo;' . ($blog["title"]) . '&raquo;' : '' ) . ' �� ' . date( 'd.m.Y', strtotimeEx($blog['post_time']) ) .'

������ ��� ������ ���� ������������ ��� ���������� � ��������� ������� �����. 

��� ��������� ���� ���������� ������������� � �� ������� ������. 

�������� �� ���������, ������� FL.ru
';
                
                messages::Add( users::GetUid($err, 'admin'), $blog['login'], $message, '', 1 );
            }
        }
    }
    
    /**
     * ����������� �� �������� ����������� � ��������
     * 
     * @param mixed $mId ID ����������� / ������ ID �����������
     */
    function contestOfferDeleteNotification( $mId ) {
        $sId    = !is_array($mId) ? array($mId) : $mId;
        $sQuery = 'SELECT po.id, po.project_id, f.uid, f.login, f.uname, f.usurname, p.name 
            FROM projects_offers po 
            INNER JOIN projects p ON p.id = po.project_id 
            INNER JOIN freelancer f ON f.uid = po.user_id 
            WHERE po.id IN (?l)';
        
        $aOffers = $GLOBALS['DB']->rows( $sQuery, $sId );
        
        if ( $aOffers ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
            
            foreach ( $aOffers as $aOne ) {
                
                $aOne['name'] = htmlspecialchars($aOne['name'], ENT_QUOTES, 'CP1251', false);
                
                $sMessage = '������������, '. $aOne['uname'] .' '. $aOne['usurname'] .'

��������, �� ��-�� ��������� ������ ���������� ����� ��������� ���� ������� ���� ������ � �������� &laquo;'. $aOne['name'] .'&raquo;
'. $GLOBALS['host'] . getFriendlyURL('project', $aOne['project_id']) .'?offer='. $aOne['id'] .'#offer-'. $aOne['id'] .'

������ ��� ������ ���� ������������ ��� ���������� ����� � ��������� ������� �����. 

��� ��������� ���� ���������� ������������� � �� ������� ������. 

�������� �� ���������, ������� FL.ru
';
                
                messages::Add( users::GetUid($err, 'admin'), $aOne['login'], $sMessage, '', 1 );
            }
        }
    }
    
    /**
     * ����������� �� �������� ����������� � ������ � ��������
     * 
     * @param mixed $mId ID ����������� / ������ ID ������������
     */
    function contestMessageDeleteNotification( $mId ) {
        $sId    = !is_array($mId) ? array($mId) : $mId;
        $sQuery = 'SELECT o.id, o.project_id, u.uid, u.login, u.uname, u.usurname, p.name 
            FROM projects_contest_msgs m 
            INNER JOIN projects_contest_offers o ON o.id = m.offer_id 
            INNER JOIN projects p ON p.id = o.project_id 
            INNER JOIN users u ON u.uid = m.user_id 
            WHERE m.id IN (?l)';
        
        $aMessages = $GLOBALS['DB']->rows( $sQuery, $sId );
        
        if ( $aMessages ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
            
            foreach ( $aMessages as $aOne ) {
                
                $aOne['name'] = htmlspecialchars($aOne['name'], ENT_QUOTES, 'CP1251', false);
                
                $sMessage = '������������, '. $aOne['uname'] .' '. $aOne['usurname'] .'

��������, �� ��-�� ��������� ������ ���������� ����� ��������� ���� ������� ��� ����������� � ������ � �������� &laquo;'. $aOne['name'] .'&raquo;
'. $GLOBALS['host'] . getFriendlyURL('project', $aOne['project_id']) .'?offer='. $aOne['id'] .'#offer-'. $aOne['id'] .'

������ ��� ������ ���� ������������ ��� ���������� ������������ � ��������� ������� �����. 

��� ��������� ���� ���������� ������������� � �� ������� ������. 

�������� �� ���������, ������� FL.ru
';
                
                messages::Add( users::GetUid($err, 'admin'), $aOne['login'], $sMessage, '', 1 );
            }
        }
    }
    
    /**
     * ����������� �� �������� ����������� �����������
     * 
     * @param mixed $mId ID ����������� ���������� / ������ ID ����������� �����������
     */
    function freelancerOfferBlockedNotification( $mId ) {
        $sId    = !is_array($mId) ? array($mId) : $mId;
        $sQuery = 'SELECT o.title, o.post_date, o.reason, f.uid, f.login, f.uname, f.usurname
            FROM freelance_offers o 
            INNER JOIN freelancer f ON f.uid = o.user_id 
            WHERE o.id IN (?l)';
        
        $aOffers = $GLOBALS['DB']->rows( $sQuery, $sId );
        
        if ( $aOffers ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
            
            foreach ( $aOffers as $aOne ) {
                $sMessage = '������������, '. $aOne['uname'] .' '. $aOne['usurname'] .'

��������, �� ��-�� ��������� ������ ���������� ����� ��������� ���� ������������� ���� ������ &laquo;'. $aOne['title'] .'&raquo;  �� '. date('d.m.Y', strtotimeEx($aOne['post_date'])) .' � ������� &laquo;������&raquo;

������� ����������: '. $aOne['reason'] .'

������ ��� ������ ���� ������������ ��� ���������� ����� � ��������� ������� �����. 

��� ��������� ���� ���������� ������������� � �� ������� ������. 

�������� �� ���������, ������� FL.ru
';
                
                messages::Add( users::GetUid($err, 'admin'), $aOne['login'], $sMessage, '', 1 );
            }
        }
    }
        
    /**
	 * �������� ����������� ������������ � ��� ������������ ������� ������ �� ����� �������������
	 *
	 * @param array $ids ����� ��� array('1-2') ��� 1 - �� �������, 2 - ��� ������
	 */
	function ProjectComplainsSend($ids, $connect = NULL) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        
        if(!is_array($ids)) {
            $ids = array($ids);
        }
        
        foreach ( $ids as $id ) {
            list($project_id, $type) = explode("-", $id);
            $complains[$project_id][] = $type;
        }
        
        // ���������
        foreach($complains as $project_id => $types) {
            $project = new projects();
            $prj     = $project->GetPrj(0, $project_id, 1);
            $emp     = new users();
            $emp->GetUserByUID($prj['user_id']);
            if (!$emp->email || substr($emp->subscr, 4, 1) != '1' || $emp->is_banned == '1') continue;
            
            $prj['name'] = htmlspecialchars($prj['name'], ENT_QUOTES, 'CP1251', false);
            
            $text_type = "";
            foreach($types as $type) {
                switch($type) {
                    case '6':
                        $this->subject = "�������������� ���� ������ �� FL.ru";
                        
                        $message  = "����������, �������� ������/���������, � ������� ����������� ��� ������ �<a href='{$GLOBALS['host']}" . getFriendlyURL("project", $project_id) . $this->_addUrlParams('e')."'>{$prj['name']}</a>�. �� ���������� �������������, ������ �������� �������: ������� �� ������������� ������������� �����������, ������� �� �������.<br/><br/>"; 
                        $message .= "<a href='{$GLOBALS['host']}/public/?step=1&public={$project_id}" . $this->_addUrlParams('e') . "'>������� � �������������� �������</a><br/><br/>";
                        $message .= "����� ����� ����������� ����������� ����, ���� ��� ������ ����������� ���������. �� ������ ������������ � ����������� �� <a href='http://feedback.fl.ru/" . $this->_addUrlParams('e') ."'>��������������</a> �������� � ����� ���������� ���������.";
                        break;
                    case '7':
                        $this->subject = "������� �������������� ���������� �� ������ ������� �� FL.ru";
                        
                        $message  = "�� ���������� �������������, �� ������� ������������ ���������� ��� ���������� ������� �<a href='{$GLOBALS['host']}" . getFriendlyURL("project", $project_id) . $this->_addUrlParams('e')."'>{$prj['name']}</a>�. ��������, ��� ����� ������� ��������� ���� ������, ��������� ����������� �������, ������� ����� ���������� ������.<br/><br/>";
                        $message .= "<a href='{$GLOBALS['host']}/public/?step=1&public={$project_id}" . $this->_addUrlParams('e') . "'>������� � �������������� �������</a><br/><br/>";
                        $message .= "�� ������ ������������ � ����������� �� <a href='http://feedback.fl.ru/" . $this->_addUrlParams('e') ."'>��������������</a> �������� � ����� ���������� ���������. ";
                        break;
                    case '8':
                        $this->subject = "������� ������ ������ ������� �� FL.ru";
                        
                        $message  = "�� ���������� �������������, �� �� ������� ������ �������� ����������� � ����� ������� �<a href='{$GLOBALS['host']}" . getFriendlyURL("project", $project_id) . $this->_addUrlParams('e')."'>{$prj['name']}</a>�.<br/><br/>";
                        $message .= "��� ���� ����� ���������� ����� ������� ����������� ������ ������/������ � ������� ������� � ������ ������ �� ���������� �������, �� ���������� ����� ������. ����������, ��������� ���� ������� � ����� �������������� �������.<br/><br/>";
                        $message .= "<a href='{$GLOBALS['host']}/public/?step=1&public={$project_id}" . $this->_addUrlParams('e') . "'>������� � �������������� �������</a><br/><br/>";
                        $message .= "�� ������ ������������ � ����������� �� <a href='http://feedback.fl.ru/" . $this->_addUrlParams('e') ."'>��������������</a> �������� � ����� ���������� ���������. ";
                        break;
                    default:
                        continue;
                        break;
                }
                
                $this->message   = $this->GetHtml( $emp->uname, $message, array('header'=>'default', 'footer'=>'feedback_default'), array('login' => $emp->login));
                $this->recipient = $emp->uname.' '.$emp->usurname.' ['.$emp->login.'] <'.$emp->email.'>';
                $this->send('text/html');
                //$this->SmtpMail('text/html');
                projects::updateComplainCounters(array('is_send' => true), $project_id, "AND is_send = false AND type = {$type}");
            }
        }
    }
    
    /**
     * �������� ����������� � ���� ������������ � ����������
     * @see trigger "aIU commune_members/mail"
     * 
     * @param array $ids     ������ ���������������
     * @param type $connect
     */
    public function CommuneMemberBan($ids, $connect = NULL) {
        if(!is_array($ids)) return;
        
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/commune.php';
        
        foreach($ids as $id) {
            list($user_id, $commune_id) = explode("-", $id);
            $user = new users();
            $user->GetUserByUID($user_id);
            if(!$user->email || substr($user->subscr, 5, 1) != '1' || $user->is_banned == '1') {
                continue;
            }
            $comm_link = $GLOBALS['host'].'/commune/?id='.$commune_id;
            
            $comm = commune::getCommuneInfoForFriendlyURL($commune_id);
            $this->subject  = "��� ������������� � ���������� ";
            $body = $this->subject . ' �<a href="'.$comm_link.$this->_addUrlParams('b', '&').'">'.$this->ToHtml($comm['name'], 1).'</a>�. ';
            $this->subject .= "�{$comm['name']}�";
            $body .= "� ���������, ������ �� �� ������ ��������� ����� ���� � ��������� ����������� � ����������.";
            
            $this->recipient = $user->uname.' '.$user->usurname.' ['.$user->login.'] <'.$user->email.'>';
            $this->message = $this->GetHtml($user->uname, $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$user->login));
            $this->send('text/html');
        }
        
        return $this->sended;
    }

    /**
     * �������� ����������� � ��� ��� �������� ���������� � ������������� ���� ��� ����� �������������
     * ��������� ������ ��� ���������� ������������� ������ � ���������� ������� ������
     *
     * @param $uids         ������ �� �������������
     * @param null $connect
     * @return int
     */
    public function activateWallet($uids, $connect = NULL) {
        if(!is_array($uids)) return;

        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/billing.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wallet/wallet.php';

        foreach($uids as $uid) {
            if((int)$uid <= 0) continue; // ���� ��
            $bill   = new billing((int)$uid);
            if( substr($bill->user['subscr'],15,1) !='1' ) continue;
            $autopay = billing::getAllAutoPayed($uid);
            if(empty($autopay)) continue; // ������������� �� ��������

            $wallet     = walletTypes::initWalletByType($uid);
            if(!walletTypes::checkWallet($wallet)) continue;  // ����� ������ ��� �� ������������
            $walletName = str_replace("%WALLET%", $wallet->getWalletBySecure(), walletTypes::getNameWallet($wallet->data['type'], 2));

            $message  = "�� ���������� {$walletName} � �������� �������� ������ ��� ������������� ��������� �����:<br/><br/>";
            foreach($autopay as $payed) {
                $message .= "-&nbsp;{$payed['name']} ({$payed['cost']} ���.)<br/>";
            }
            $message .= "<br/>";
            $message .= "���������� � �������� ������ � ������������� �����, � ����� ������ �� ��� ������������ ������� �� ������ ����� � ����� <a href='http://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>���������� ���������</a>.";

            $this->subject   = "FL.ru: ����������� ������ ������� ������";
            $this->recipient = "{$bill->user['uname']} {$bill->user['usurname']} [{$bill->user['login']}] <{$bill->user['email']}>";
            $this->message   = $this->GetHtml($bill->user['uname'], $message, array('header' => 'default', 'footer' => 'default'), array('login'=>$bill->user['login']));

            $this->send('text/html');
        }

        return $this->sended;
    }
}