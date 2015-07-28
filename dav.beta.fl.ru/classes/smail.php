<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/smtp.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/template.php';
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/settings.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/projects_offers.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/statistic/StatisticFactory.php');



/**
 * ����� ��� �������� �����
 *
 */
class smail extends SMTP {
    
    /**
    * ���������� email � ������� �� �������� ������ ����� ����������
    *
    * @param     string    $name    ��� � �������
    * @param     string    $msg     ����� �����������
    * @return    string             ��������� ������
    */
    function NewPromoCommune($name, $msg) {
        if (!$this->Connect())
            return "���������� ���������� � SMTP ��������";

        $this->subject = "����������� ������ ����� ����������";
        $this->recipient = "adv@FL.ru";
        $msg_text = "��� � �������:<br>".htmlspecialchars(stripslashes($name), ENT_COMPAT | ENT_HTML401, 'cp1251')."<br><br>�����������:<br>".htmlspecialchars(stripslashes($msg), ENT_COMPAT | ENT_HTML401, 'cp1251');
        $this->message = $this->GetHtml('', $msg_text, array('header' => 'none', 'footer' => 'none'));
        $this->SmtpMail('text/html');
    }
    /**
     * ���������� ��������� �� ������������� ������ ������, ������������ � ������ /siteadmin/admin/. ��������� �� hourly.php.
     *
     * ����� ��������� ���� ���������� ����� ��� ������� � ������� messages � ����� to_id ������ 0 �,
     * �� �������������, ���������� ������ ���� ������������� ����� ��������� ���������.
     * ����� ����, ����� ���������� ���������������� ������ ��������� � ������� ���������� variables, ����������
     * � ������ 'admin_message_id' �� ��������� ��������������� ������������� ���������.
     * ���������� ����������� � ����� ��������� � ����� ("��� ��������").
	 *
	 * @return   string   ��������� ������
     */
	function SendAdminMessage()
	{
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/spam.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
		
		if (!($message_id = spam::GetAdminMessageID()))
			return "�� ���������������� �� ������ ��������� �� ������������� (������� 'variables', ��� ���������� 'admin_message_id').";

		if (!($message = messages::GetMessage($message_id)))
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

		for ($i=0; $users = messages::GetZeroMessageUsers($message['from_id'], $message_id, 1000, $i * 1000); $i++) {
			foreach ($users as $ikey=>$user) {
				if (!$user['email'] || substr($user['subscr'], 7, 1) == '0') continue;
				$this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
				$this->message = $this->GetHtml($user['uname'], $msg_text, array('header' => 'none', 'footer' => 'none'));
				$this->SmtpMail('text/html', $attaches);
			}
		}
		return '';
	}
	
    
    /**
     * ���������� ��������� �� ������������� ������ ������, ������������ � ������ /siteadmin/admin/. ��������� �� hourly.php.
     *
     * ����� ��������� ���� ���������� ����� ��� ������� � ������� messages � ����� to_id ������ 0 �,
     * �� �������������, ���������� ������ ���� ������������� ����� ��������� ���������.
     * ����� ����, ����� ���������� ���������������� ������ ��������� � ������� ���������� variables, ����������
     * � ������ 'admin_message_id' �� ��������� ��������������� ������������� ���������.
     * ���������� ����������� � ����� ��������� � ����� ("��� ��������").
	 *
	 * @return   string   ��������� ������
     */
	function SendMasssending()
	{
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/spam.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
		
		if (!($message_id = spam::GetMasssendingMessageID()))
			return "�� ���������������� �� ������ ��������� �� ������������� (������� 'variables', ��� ���������� 'admin_message_id').";

		if (!($message = messages::GetMessage($message_id)))
			return "���� ��������� �����������.";

		$this->subject = "����� ��������� �� FL.ru";
		$msg_text = "
<a href='{$GLOBALS['host']}/users/{$message['from_login']}{$this->_addUrlParams('b')}'>{$message['from_uname']} {$message['from_usurname']}</a> [<a href='{$GLOBALS['host']}/users/{$message['from_login']}{$this->_addUrlParams('b')}'>{$message['from_login']}</a>]
��������(�) ��� ����� ��������� �� ����� FL.ru.<br />
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
		// ����� �� ���� �� ����� � ������ � �������� ���������, ����� ������ �� ����� :) �� ���� �����... �� �������� ���
		$attaches = array();
		/*if ($message['attach']) {
			foreach($message['attach'] as $a) {
				$attaches[] = new CFile($a['path'].$a['fname']);
			}
			$attaches = $this->CreateAttach($attaches);
		}*/

		if (!$this->Connect())
			return "���������� ���������� � SMTP ��������";

		for ($i=0; $users = messages::GetZeroMessageUsers($message['from_id'], $message_id, 1000, $i * 1000, FALSE); $i++) {
			foreach ($users as $ikey=>$user) {
       
				if ($user['email'] && (substr($user['subscr'], 12, 1) == '1')) {
					$this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
					$this->message = $this->GetHtml($user['uname'], $msg_text, array('header' => 'default', 'footer' => 'default'), array('login'=>$user['login']));
					$this->SmtpMail('text/html', $attaches);
				}
			}
		}
		
		// ���������� ��������� ������ ��������
		$this->subject = "���� ������ �� �������� ������ ���������";
		$this->recipient = $message['from_uname']." ".$message['from_usurname']." [".$message['from_login']."] <".$message['from_email'].">";
		$attaches = '';
		if ($message['attach']) {
			foreach ($message['attach'] as $a) {
				$attaches .= ", <a href='".WDCPREFIX."/{$a['path']}{$a['fname']}{$this->_addUrlParams('b')}'>{$a['fname']}</a>";
			}
		}
		$msg_text = $this->ToHtml($message['msg_text']);
        $body = 
        "���� ������ �� �������� ���� ����������� � �������� ������������ ����� FL.ru (3). 
         ����������� ��������� ���� ������������� ����� ���������� ��������� ���������� ����������:</br>
         ---<br/>
         {$msg_text}<br/>
         ---<br/>";
		$this->message = $this->GetHtml($message['from_uname'], $body, array('header'=>'default', 'footer'=>'simple'));
		$this->SmtpMail('text/html');
		
		return '';
	}
	

    /**
     * ���������� ������������� FL.ru ��������� �� ����� �� �������� �����.
     *
     * @param  string   $login  ��� ������������
     * @param  string   $email  e-mail ������������
     * @param  int      $kind   ��� �������
     * @param  string   $msg    ����� ���������.
     * @param  int      $fid    id �� ������� feedback
     * @return integer          ��������� ������������ �����.
     */
	function FeedbackPost( $login, $email, $kind, $msg, $ucode = '', $fid = 0 ) {
	    $nRet = 0;
	    
	    if ( !empty($GLOBALS['aFeedbackPost'][$kind]) ) {
	        $login = stripslashes(htmlspecialchars_decode($login, ENT_QUOTES));
            $msg   = stripslashes($msg);
            
    	    $this->recipient = $GLOBALS['aFeedbackPost'][$kind]['email'];
            $this->subject   = $GLOBALS['aFeedbackPost'][$kind]['subj'];
            $this->message   = $msg . ( ($ucode && $fid) ? "\n".'[[UCODE::{'.$ucode.'},FID::{'.$fid.'}]]' : "" );
    		$this->from      = "$login <$email>";
    		
            $this->SmtpMail( 'text/plain' );
            
            $nRet = $this->sended;
	    }
        
        return $nRet;
	}
	

    /**
     * ���������� ����������� ������ ���������� � ����� ������ �� ����������.
     *
     * @param  int    $user_id   users.id ��������� ������ �����.
     * @param  array  $comm      ������ � ����������� � ����������, � ������� ����� ��������.
     */
    function CommuneJoinAction($user_id, $comm)
    {
        if(!$comm['author_email'] || $comm['author_subscr'][5] != '1')
            return NULL;
        
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        $user = new users();
        $user->GetUserByUID($user_id);
        $this->recipient = $comm['author_uname'].' '.$comm['author_usurname'].' ['.$comm['author_login'].'] <'.$comm['author_email'].'>';
        $this->subject .= '����� ������ �� ���������� � ���������� �'.$comm['name'].'�';
        $body = 
"������������ <a href=\"{$GLOBALS['host']}/users/{$user->login}{$this->_addUrlParams('b')}\">{$user->uname} {$user->usurname}</a> [<a href=\"{$GLOBALS['host']}/users/{$user->login}{$this->_addUrlParams('b')}\">{$user->login}</a>] 
����� �������� � ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$comm['id']}{$this->_addUrlParams('b', '&')}\">".$this->ToHtml($comm['name'], 1)."</a>�. 
�� ������ <a href=\"{$GLOBALS['host']}/commune/?id={$comm['id']}&site=Admin.members&mode=Asked{$this->_addUrlParams('b', '&')}\">��������� ��� �������</a> ��� ������.";
        $this->message = $this->GetHtml($comm['author_uname'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$comm['author_login']));
        $this->SmtpMail('text/html');
    }
	

    /**
     * ���������� ����������� ����� ����������, ��� ��� ��������, �������, ������� ������ � �.�.
     *
     * @param int $user_id   users.id ��������� ������ �����.
     * @param string $action   ��� �����������.
     * @param array $comm   ������ � ����������� � ����������, � ������� ����.
     */
    function CommuneMemberAction($user_id, $action, $comm)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$user = new users();
        $user->GetUserByUID($user_id);
        $comm_link = $GLOBALS['host'].'/commune/?id='.$comm['id'];
        

        if(!$user->email || substr($user->subscr, 5, 1) != '1' || $user->is_banned == '1')
            return NULL;
        
        switch($action)
        {
            case 'do.Kill.member'     : $this->subject = '��� ������� �� ���������� '; break;
            case 'do.Accept.member'   : $this->subject = '��� ������� � ���������� '; break;
            case 'do.Unaccept.member' : 
                $body = "���� ������ �� ���������� � ���������� ";
                $this->subject = '������ �� ���������� � ���������� '; 
                break;
            case 'do.Add.admin'       : $this->subject = '��� �������� � �������������� ���������� '; break;
            case 'do.Remove.admin'    : $this->subject = '��� ������� �� ������������� ���������� '; break;
            case 'UnBanMember'        : $this->subject = '��� �������������� � ���������� '; break;
            case 'BanMember'          : $this->subject = '��� ������������� � ���������� '; break;
            case 'WarnMember'         : $this->subject = '��� ������� �������������� � ���������� '; break;
        }
        
        $body = ( $body ? $body : $this->subject ).' �<a href="'.$comm_link.$this->_addUrlParams('b', '&').'">'.$this->ToHtml($comm['name'], 1).'</a>�';
        $comm['name'] = $comm['name'];
        $this->subject .= '�'.$comm['name'].'�';
        if($action=='do.Unaccept.member') {
            $this->subject .= ' ���������';
            $body .= ' ���������';
        }
        $body .= '. ';
        
        switch($action) {
            case 'BanMember':   $body .= "� ���������, ������ �� �� ������ ��������� ����� ���� � ��������� ����������� � ����������."; break;
            case 'UnBanMember': $body .= "������ �� ����� ������ ��������� ����� ���� � ��������� ����������� � ����������. "; break;
        }

        $this->recipient = $user->uname.' '.$user->usurname.' ['.$user->login.'] <'.$user->email.'>';
        $this->message = $this->GetHtml($user->uname, $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$user->login));
        $this->SmtpMail('text/html');
    }

    /**
     * ���������� ����������� � ����� ����� � ����������. ���������� �� hourly.php ��� � ���.
     */
    function CommuneNewTopic()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/commune.php';
		
		if(!($topics = commune::GetTopic4Sending('ALL', true)))
            return NULL;

        foreach($topics as $top)
        {
            if(!($recs = commune::GetTopicSubscribers($top['commune_id'])))
                continue;

            $this->subject = '����� ���� � ���������� �'.$top['commune_name'].'�';
            $domain = $GLOBALS['host'];
            $body = 
"<a href=\"$domain/users/{$top['user_login']}\">{$top['user_uname']} {$top['user_usurname']}</a> [<a href=\"$domain/users/{$top['user_login']}\">{$top['user_login']}</a>] ������(-�) <a href=\"{$GLOBALS['host']}/commune/?id={$top['commune_id']}&site=Topic&post={$top['id']}{$this->_addUrlParams('b', '&')}\">����� ����</a> � ���������� �<a href=\"{$GLOBALS['host']}/commune/?id={$top['commune_id']}{$this->_addUrlParams('b', '&')}\">".$this->ToHtml($top['commune_name'], 1)."</a>�.
<br/><br/>
--------
<br/>".$top['title']."
<br/>".reformat(LenghtFormatEx(strip_tags($top['msgtext'], "<br><p>"), 300))."
<br/>
<br/>
--------";

            if(commune::SetTopicIsSent($top['theme_id'])) {
				if (!$this->Connect()) {
					return "���������� ���������� � SMTP ��������";
				}
                foreach($recs as $r) {
                    if($top['user_login']!=$r['login']) {
                        $this->recipient = $r['uname']." ".$r['usurname']." [".$r['login']."] <".$r['email'].">";
                        if (!$r['unsubscribe_key']) {
                            $r['unsubscribe_key'] = users::GetUnsubscribeKey($r['login']);
                        }
                        $this->message = $this->GetHtml($r['uname'], $body, array('header' => 'default', 'footer' => 'default'), array('login'=>$r['login'], 'UNSUBSCRIBE_KEY'=>$r['unsubscribe_key']));
                        $this->SmtpMail('text/html');
                    }
                }
            }
        }
    }

	
    /**
     * ���������� ������������ 2009 �� �������.
     * @deprecated
     */
    function NY2009()
    {
        if($GLOBALS['host']!='http://www.FL.ru')
            return;

        return;

        $t_user = new users();
        $this->subject = '����������� ������ ����!';
        $attach = self::CreateAttach(array(0=>array('path'=>$_SERVER['DOCUMENT_ROOT'].'/images/', 'name'=>'otkrytka.jpg', 'content_type'=>'image/jpeg')));
        $i=0;
        do
        {
            $users = $t_user->GetAll($size, "is_banned = '0'", "uid", 1000,($i*1000));
            if ($users) {
                foreach ($users as $ikey=>$user){
                    if (!$user['email']) continue;
                    $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
                    if(!is_emp($user['role'])) {
                        $body = 
'<p>
������� ���� ����� ������ ������������ �� ���������!
</p>
<p>
    �� ����� ������� ��� ������� �������.
</p>
<p>
    ������� �� ��, ��� �� ����.<br/>
    �� ���� ����� ������� � �������� ������ �����. �� ������ ��� �����������, �� ������ ����.
    �� ��, ��� ��, �� ����� ������ ������� � ���, ���������� ������ � ���� ������ ������.<br/>
    �� ��� ���� �������� �������, ������� ��� ����� ����� � �������� ������ ���� ��� ������� � ����������.<br/>
    �� ��� ������� � ���� � �����, ������� �� ������ � ����� �������.<br/>
    �� ��������, � ������� �� �������� ����� ���������� ������ � ����������� � ������.<br/>
    �� ��� ��������������� � ���������������. ��������������� � ���������������, ��������� ������� �� ����� ����� � ���������� �������� � ���, ��� ���� ���������� � ������������ ������.<br/>
    �� ���� ������ � �������������, � ��-�� ����� &mdash; ��� �� ��� �������.<br/>
    �� ���� �������� � ���, �����������, ���������� � ��������.
</p>
<p>
    �� ��, ��� ��������� ��� ���� ���-����.�� (��, ���� ��� �������� � ������� ���� � ������������, ��, ������ ���� :)
</p>
<p>
    ��������� ������� ��� ����������� ������� � �������� ������, ������� � �������� � ����������� ����. ����� ��� � ������.
</p>
<p>
    �� ��������� � ������ ������� � �������, ������ ����� ������ ������, ���� ����� ���������� �������, �� ��� ���� ���������� ���� &mdash; �� ������� ���� ����� � �� ������ ������� ���� ������� � �������������. �� ����������.
</p>
<p>
    ����� ������ �������� ��� ����, � �������� �������� ����, ��������� ��������� ������ � ���������� ��, ����������� ���� ���������� ������� � ������� �� ������ ����� �������. ��� ���� ��� ����� �������, �� �� ������ ������ ����������.
</p>
<p>
    ������ ��� ��������� �����. �� ���� � ������� � ��������.
</p>
<p>
    ����������� ������ ����!
</p>
<p>
    ���� ���-����.��: <a href="http://www.FL.ru/newyear2009/">http://www.FL.ru/newyear2009/</a>
</p>';
                    }
                    else    {
                        $body = 
'<p>
    ������� ���� ����� ������ ������������ �� ���������!
</p>
<p>
    �� ����� ������� ��� ������� �������.
</p>
<p>
    �� ���� ����� ������� � �������� ������ �����. �� ������ ��� �����������, �� ������ ����.<br/>
    �� ��� ���� ������� � ��������.<br/>
    �� ��� ���� �������� �������, ������� ��� ����� ����� � �������� ������ ���� ��� ������� � ����������.<br/>
    �� ��������, � ������� �� �������� ����� ���������� ������ � ����������� � ������.<br/>
    �� ��� ��������������� � ���������������. ��������������� � ���������������, ��������� ������� �� ����� ����� � ���������� �������� � ���, ��� ���� ���������� � ������������ ������.<br/>
    �� ���� �������� � ���, �����������, ���������� � ��������.<br/>
    �� ��, ��� ��������� ��� ���� ���-����.�� (��, ���� ��� �������� � ������� ���� � ������������, ��, ������ ���� :)<br/>
</p>
<p>
    ��������� ������� ��� ����������� ������� � �������� ������, ������� � �������� � ����������� ����. ����� ��� � ������.
</p>
<p>
    ����� ������ ����� ������ ����������, � ���������� ��� ������� ����������� �������, � ����������� ������� ���������, �� ����� ����������� ��������� �������. ������ � ������� ������� �� ��� ����������. �������������� ����� �����, ��� ����� ������ �������. � ��������� ��� ������� ������. �������������� ��� �����������. ����� � �����������.
</p>
<p>
    ������ ��� ��������� �����. �� ���� � ������� � ��������.
</p>
<p>
    ����������� ������ ����!
</p>';
                    }
                    $this->message = self::GetHtml($user->uname, $body, array('header'=>'no', 'footer'=>'no'));
                    $error = $this->SmtpMail(true,0,'text/html',$attach);
                }
            }
            $i++;
        } while (sizeof($users) == 1000);
    }


    /**
     * ����������� �� ������ 2009 �� �������.
     * @deprecated
     */
    function BD2009()
    {
        return;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/birthday.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");

        $bd = new birthday(2009);
        $users = $bd->getAll(false);

        $this->subject = '';
        $smtp = 0;
        $admin_id = users::GetUid($error, 'admin');
        foreach ($users as $ikey=>$user) {
            if (!$user['email']) continue;
            $email_msg = // ��������� ��� �-���� ��������.
'';
            $lichka_msg = // ��������� ��� �������� �� ������.
'';
            $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
            $this->message = self::GetHtml($user['uname'], $email_msg); // ���� ����������� �� ����, �� �������� 3-� ��������: array('header'=>'no', 'footer'=>'no'));
            $smtp = $this->SmtpMail(false, $smtp, 'text/html');
            $error = messages::Add($admin_id, $user['login'], $lichka_msg, '');
        }
        self::SmtpClose($smtp);
    }
	

//------------------------------------------------------------------------------
    
    
    
    /**
     * �������� ���������� � ����� ��������
     * 
     * @param int  ������� �������� ��������
     * @param int  ������� �������� ������ �� ������
     * @return int ����� ���������� ����� ���������
     */
    public function EmpNewProj($show_limit = 10, $min_users = 200)
    {
        //$show_limit = 10;//�������� ��������
        //$min_users = 200;//�������� ������ �� ������
        
        $projects = projects::GetNewProjectsPreviousDay($error, false, $show_limit, true);
        
        $projects_count = count($projects);
        if(!$projects_count) return FALSE;
        
        $page  = 0;
        $count = 0;         
        $message = '';
        
        $current_date = time();
        $current_date_sufix = '_' . date('dmy',$current_date); //format:_270314
        
        foreach ($projects as $prj) {
            
            $message .= Template::render(
                    $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/emp_new_projects/project.tpl.php', 
                    array(
                            'url'   => $GLOBALS['host'] . getFriendlyURL('project', array('id' => $prj['id'], 'name' => $prj['name'])),
                            'name'  => ($prj['name'] ? reformat($prj['name'], 50, 0, 1) : ''),
                            'descr' => $prj['descr'],
                            'host' => $GLOBALS['host'],
                            'project_kind' => $prj['kind'],
                            'project_pro_only' => ($prj['pro_only'] == 't'),
                            'project_verify_only' => ($prj['verify_only'] == 't'),
                            'project_urgent' => ($prj['urgent'] == 't'),
                            'price' => ($prj['cost'])? CurToChar($prj['cost'], $prj['currency']) . getPricebyProject($prj['priceby']) : NULL,
                            'end_date' => $prj['end_date'],
                            'create_date' => $prj['create_date'],
                            'utm_param' => $this->_addUtmUrlParams('email', 'emp%UTM_CONTENT%', 'day_projects' . $current_date_sufix)
                    )
           );
        }
        
        
        //����������� ������ ��� �������������           
        $settings = new settings();
        $banner_file = $settings->GetVariable('newsletter', 'emp_banner_file');
        $banner_link = $settings->GetVariable('newsletter', 'emp_banner_link');            

        
        $this->subject = '����� ������� �� FL.ru';
        $this->message = Template::render(
                $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/emp_new_projects/project_layout.tpl.php', 
                array(
                    'projects' => $message,
                    'host' => $GLOBALS['host'],
                    'projects_cnt' => $projects_count,
                    'date' => strtotime('- 1 day'),
                    'join_url' => $GLOBALS['host'] . '/public/?step=1&kind=1',
                    'unsubscribe_url' => '%UNSUBSCRIBE_URL%',
                    'track_url' => '%TRACK_URL%',
                    
                    'banner_file' => $banner_file,
                    'banner_link' => $banner_link                    
                )
        );
        $this->recipient = '';
        $massId = $this->send('text/html');
        
        $statistics = array();
        
        while ( $users = employer::GetPrjRecps($error, ++$page, $min_users) ) {
            
            $this->recipient = array();
            
            foreach ( $users as $user ) {
                
                if (!$user['unsubscribe_key']) {
                    $user['unsubscribe_key'] = users::GetUnsubscribeKey($user['login']);
                }
                
                
                if($user['last_years_ago'] > 0){
                   $utm_content = ($user['last_years_ago'] > 3)?'_3y':'_1-3y';
                }else{
                   $utm_content = ($user['reg_days_ago'] > 7)?'_1y':'_new';
                }
                $utm = $this->_addUtmUrlParams('email', 'emp' . $utm_content, 'unsubscr_day_projects' . $current_date_sufix);
                
                
                //����������� ����������
                $stat_idx = (($user['reg_days_ago'] > 7)?$user['reg_date_year']:'new');
                $statistics[$stat_idx]++; 
                
                
                $this->recipient[] = array(
                    'email' => sprintf('%s %s [%s] <%s>', $user['uname'], $user['usurname'], $user['login'], $user['email']),
                    'extra' => array(
                        'USER_NAME'         => $user['uname'],
                        'USER_SURNAME'      => $user['usurname'],
                        'USER_LOGIN'        => $user['login'],
                        'UTM_CONTENT'       => ($user['reg_days_ago'] > 7)?$user['reg_date_year']:'_new',
                        'UNSUBSCRIBE_URL'   => "/unsubscribe/?type=new_projects&ukey={$user['unsubscribe_key']}" . $utm,
                                
                        'TRACK_URL'         => $GLOBALS['host'] . StatisticHelper::track_url(1, $stat_idx, $current_date, $user['login'] . $user['uid'])
                    )
                );
                
                        
       
                        
                $count++;
            }
            
            
            $this->bind($massId, true);  

        }
        
        
        //��������� ���������� ���������� � GA
        $statistics['total'] = $count;
        $ga = StatisticFactory::getInstance('GA');
        $ga->newsletterNewProjectsEmp($statistics, $current_date);
        
        return $count;
    }

    


//------------------------------------------------------------------------------




    /**
     * @todo: ������ NewProj
     * 
     * �������� � ����� �������� �� ���������� ����. ���������� ��� � ���� �� hourly.php
     * 
     * @param array $uids - ������ ��������������� �������������, ������� ����� ���������
     * @return integer   ���������� ���������� ��������
     */
    public function NewProj2($uids = array())
    {
        $show_pro_limit = 25;
        $show_limit = 25;
        
        $projects = projects::GetNewProjectsPreviousDay($error, true);
        $groups   = professions::GetAllGroupsLite(true);
        
        $page  = 0;
        $count = 0;        
        
        $projects_count = count($projects);
        if(!$projects_count) return FALSE;
        
        //�������� �������
        $settings = new settings();
        $banner_file = $settings->GetVariable('newsletter', 'banner_file');
        $banner_link = $settings->GetVariable('newsletter', 'banner_link');
        
        
        $this->subject = '����� ������� �� FL.ru';

        $this->message = Template::render(
                $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/new_projects/project_layout.tpl.php', 
                array(
                    'projects' => '%MESSAGE%',
                    'host' => $GLOBALS['host'],
                    'title' => '%TITLE%',
                    'unsubscribe_url' => '%UNSUBSCRIBE_URL%',
                    'date' => strtotime('- 1 day'),
                    'track_url' => '%TRACK_URL%'
                )
        );
        $this->recipient = '';
        $massId = $this->send('text/html');
        
        $project_ids = array();
        
        foreach ($projects as $i => $prj) {
            
            $descr = $prj['descr'];
            $descr = htmlspecialchars($descr, ENT_QUOTES, 'CP1251', false);
            $descr = reformat(LenghtFormatEx($descr,180), 50, 0, 1);

            $price = ($prj['cost'])? CurToChar($prj['cost'], $prj['currency']) . getPricebyProject($prj['priceby']) : NULL;
            

            $projects[$i]['html'] = Template::render(
                    $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/new_projects/project.tpl.php', 
                    array(
                            'url'   => $GLOBALS['host'] . getFriendlyURL('project', array('id' => $prj['id'], 'name' => $prj['name'])),
                            'name'  => ($prj['name'] ? reformat(htmlspecialchars($prj['name'], ENT_QUOTES, 'CP1251', false), 50, 0, 1) : ''),
                            'descr' => $descr,//LenghtFormatEx(reformat($prj['descr'], 100, 0, 1),250),
                            'host' => $GLOBALS['host'],
                            'project_kind' => $prj['kind'],
                            'project_pro_only' => ($prj['pro_only'] == 't'),
                            'project_verify_only' => ($prj['verify_only'] == 't'),
                            'project_urgent' => ($prj['urgent'] == 't'),
                            'price' => $price,
                            'end_date' => $prj['end_date'],
                            'create_date' => $prj['create_date'],
                            
                            'utm_param' => '%UTM_PARAM%'
                    )
           );
            
            
           $project_ids[] =  $prj['id'];
        }
 
        //�������� ������ � ������� ���� ������ �� ����� �������
        $offers_exist = array();
        $offers = projects_offers::AllFrlOffersByProjectIDs($project_ids);

        if(count($offers)){
            foreach($offers as $offer)
            {
                if(!isset($offers_exist[$offer['project_id']])) $offers_exist[$offer['project_id']] = array();
                $offers_exist[$offer['project_id']][$offer['user_id']] = TRUE;
            }
        }
        
        
        $strtotime_3y_ago = strtotime('- 3 year');
        $strtotime_1y_ago = strtotime('- 1 year');
        $strtotime_1w_ago = strtotime('- 1 week');
        $current_date = time();
        $current_date_sufix = '_' . date('dmy',$current_date); //format:_270314
        
        $statistics = array();
        
        while ( $users = freelancer::GetPrjRecps($error, ++$page, 200, $uids) ) {

            $this->recipient = array();
            
            foreach ( $users as $user ) {
                
                //���� �� � ���������� ��������� �� ����������
                $is_mailer_str = (strlen($user['mailer_str']) > 0);
                
                $subj = array();
                if($is_mailer_str){
                    foreach ( $groups as $group ) {
                        if( freelancer::isSubmited($user['mailer_str'], array( array('category_id' => $group['id'])) ) ) {
                            $subj[$group['id']] = $group['name'];
                        }
                    }
                }
                
                $message_pro  = '';
                $cnt_pro = 0;
                $message = '';
                $cnt = 0;
                $cnt_submited = 0;
                $cnt_user_submited = 0;
                
                
                foreach ( $projects as $prj ) {
                    
                    //�������� �� ��������� �� ������������� � ������� ��������� ������
                    if ($is_mailer_str && !freelancer::isSubmited($user['mailer_str'], $prj['specs']) ) {
                        continue;
                    }
                    
                    //������� ��� ������� �� ��������� ��������������
                    $cnt_submited++;
                    
                    //������� �� ��������� � ������
                    if(($prj['is_blocked'] == 't') || 
                       ($prj['closed'] == 't') || 
                       ($prj['state'] == projects::STATE_MOVED_TO_VACANCY) || 
                       ($prj['kind'] == projects::KIND_PERSONAL)) {
                        continue;
                    }
                    
                    
                    //���� � ���������� ����� �� ������ �� �� ��������� ��� � ��������
                    if(isset($offers_exist[$prj['id']][$user['uid']])){
                        continue;
                    }
                    
 
                    if($prj['pro_only'] == 't'){
                        if($cnt_pro < $show_pro_limit) {
                            $message_pro .= $prj['html'];
                            $cnt_pro ++;
                        }
                    }else{
                        if($cnt < $show_limit) {
                            $message .= $prj['html'];
                            $cnt ++;
                        }
                    }
                    
                    $cnt_user_submited++;
                }
                
                $message = $message_pro . $message;
                
                if ( empty($message) ) {
                    continue;
                }
                
                if ($cnt_user_submited <= ($show_pro_limit + $show_limit)) {
                    $cnt_submited = $cnt_user_submited;
                }
                
                //��������� UTM ����� ���������
                $reg_date = strtotime($user['reg_date']);
                $reg_year = date('Y',$reg_date);
                $utm_content = ($reg_date >= $strtotime_1w_ago)?'_new':$reg_year;
                //$utm_content = ($user['reg_days_ago'] > 7)?$user['reg_date_year']:'_new';
                $utm_param = $this->_addUtmUrlParams('email', 'free' . $utm_content, 'day_projects' . $current_date_sufix);
                $message = str_replace('%UTM_PARAM%', $utm_param, $message);

                
                //�������� ������
                $message = Template::render(
                    $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/new_projects/project_list.tpl.php', 
                    array(
                        'projects' => $message,
                        'spec_list' => implode(" / ", $subj),
                        'setup_url' => $GLOBALS['host'] . "/users/{$user['login']}/setup/mailer/",
                        'other_count' => $cnt_submited - $cnt_pro - $cnt,
                        'more_url' => $GLOBALS['host'] . $utm_param,
                        'banner_file' => $banner_file,
                        'banner_link' => $banner_link        
                    )
                );
                
                           
                if (!$user['unsubscribe_key']) {
                    $user['unsubscribe_key'] = users::GetUnsubscribeKey($user['login']);
                }
                
                
                /*
                $date = strtotime($projects[0]['post_date']);
                $date = date( 'j', $date ) . ' ' . monthtostr(date('n', $date),true);
                */
                
                $projects_count_txt = $cnt_submited . ' ' . plural_form($cnt_submited, array('�����', '�����', '�����')) . ' ' . 
                                                            plural_form($cnt_submited, array('������', '�������', '��������'));

                //$title = "{$projects_count_txt} �� {$date}";
                

                $last_time = strtotime($user['last_time']);
                if($last_time < $strtotime_3y_ago){
                    $utm_content = '_3y';
                }elseif(($last_time >= $strtotime_3y_ago) && ($last_time <= $strtotime_1y_ago)){
                    $utm_content = '_1-3y';
                }elseif($reg_date < $strtotime_1w_ago){
                    $utm_content = '_1y';
                }

                /*
                 * @todo: EXTRACT ���������
                 
                if($user['last_years_ago'] > 0){
                   $utm_content = ($user['last_years_ago'] > 3)?'_3y':'_1-3y';
                }else{
                   $utm_content = ($user['reg_days_ago'] > 7)?'_1y':'_new';
                }
                */
                
                
                //����������� ����������
                $stat_idx = (($reg_date >= $strtotime_1w_ago)?'new':$reg_year);
                $statistics[$stat_idx]++;
                
                
                $this->recipient[] = array(
                    'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">",
                    'extra' => array(
                        'USER_NAME'         => $user['uname'],
                        'USER_SURNAME'      => $user['usurname'],
                        'USER_LOGIN'        => $user['login'],
                        'MESSAGE'           => $message,
                        'UNSUBSCRIBE_URL'   => "/unsubscribe/?type=new_projects&ukey={$user['unsubscribe_key']}" . $this->_addUtmUrlParams('email', 'free' . $utm_content, 'unsubscr_day_projects' . $current_date_sufix),
                        'TITLE'             => $projects_count_txt,//$title
                        
                        'TRACK_URL'         => $GLOBALS['host'] . StatisticHelper::track_url(0, $stat_idx, $current_date, $user['login'] . $user['uid'])
                    )
                );
                   
                $count++;
            }
            
            $this->bind($massId, true);            
        }
        
        
        //��������� ���������� ���������� � GA
        $statistics['total'] = $count;
        $ga = StatisticFactory::getInstance('GA');
        $ga->newsletterNewProjectsFrl($statistics, $current_date);

        
        return $count;
    }

    



    /**
     * @todo �� ������������
     * @deprecated since 0026073
     * 
     * �������� � ����� ��������. ���������� ��� � ���� �� hourly.php
     * @param array $uids - ������ ��������������� �������������, ������� ��� ���������� ����� �������� (��. NewProjForMissingMoreThan24h)
     * @return integer   ���������� ���������� ��������
     */
    public function NewProj($uids) {
        $projects = projects::GetNewProjects($error, true, 600, 50);
        $groups   = professions::GetAllGroupsLite(true);
        $page  = 0;
        $count = 0;

        if ( empty($projects) ) {
            return 0;
        }

        $this->subject = '����� ������� �� FL.ru';
        $message = 
'<p>
������ ������ ���������� �������� ������� ������� FL.ru � �� ������� ������.
</p>
<p>
�� ����� <a href="' . $GLOBALS['host'] . $this->_addUrlParams('f') . '">' . $GLOBALS['host'] . '</a> ������������ ����� �������
</p>
%MESSAGE%
<p>
���� �� ������ ���������� �������� ���������� �� �������������� �� FL.ru ��������, 
�������� � ���������� ���������� ����������-�������� <a href="'. $GLOBALS['host'] . '/promo/freetray/' . $this->_addUrlParams('f') . '">Free-tray</a>. 
</p>';
        $this->message = $this->GetHtml(
            '%USER_NAME%', 
            $message, 
            array('header' => 'default', 'footer' => 'default'), 
            array('target_footer' => true)
        );
        $this->recipient = '';
        $massId = $this->send('text/html');
        
        foreach ( $projects as $i=>$prj ) {
            $url = $GLOBALS['host'] . getFriendlyURL("project", $projects[$i]['id']);
            $projects[$i]['html'] = array(
                'post_date' => date("d.m.y", strtotimeEx($prj['post_date'])),
                'name'      => ($prj['name']? reformat($prj['name'], 100, 0, 1): ''),
                'descr'     => reformat($prj['descr'], 100, 0, 1),
                'url'       => "<a href='{$url}{$this->_addUrlParams('f')}'>{$url}</a>",
            );
        }
        
        while ( $users = freelancer::GetPrjRecps($error, ++$page, 50, $uids) ) {
            $this->recipient = array();
            foreach ( $users as $user ) {
                if ( empty($user['mailer']) ) {
                    continue;
                }
                $subj = array();
                foreach ( $groups as $group ) {
                    if( freelancer::isSubmited($user['mailer_str'], array( array('category_id' => $group['id'])) ) ) {
                        $subj[$group['id']] = $group['name'];
                    }
                }
                $lastKind = 0;
                $message  = '';
                foreach ( $projects as $prj ) {
                    if ( !freelancer::isSubmited($user['mailer_str'], $prj['specs']) ) {
                        continue;
                    }
                    if ( $lastKind != $prj['kind'] ) {
                        $kindName = '';
                        switch ( $prj['kind'] ) {
                            case 1: {
                                $kindName = '����������';
                                break;
                            }
                            case 2: {
                                $kindName = '��������';
                                break;
                            }
                            case 3: {
                                $kindName = '�� ��������';
                                break;
                            }
                            case 4: {
                                $kindName = '� ����';
                                break;
                            }
                            case 7: {
                                $kindName = '��������';
                                break;
                            }
                        }
                        $message .= "\n";
                        $message .= "<div>-----------------------------------------------------------------------------------</div>\n";
                        $message .= "<div>{$kindName}</div>\n";
                        $message .= "<div>-----------------------------------------------------------------------------------</div>\n";
                        $lastKind = $prj['kind'];
                    }
                    $message .= "\n<div>&nbsp;</div><div>-----</div>\n";
                    $message .= "<div>{$prj['html']['post_date']}</div>\n";
                    $message .= "<div>{$prj['html']['name']}</div>\n";
                    $message .= "<div>-----</div>\n";
                    $message .= "<div>{$prj['html']['descr']}</div>\n";
                    $message .= "<div>{$prj['html']['url']}</div>\n";
                    $message .= "<div>-----------------------------------</div>\n";
                }
                
                if ( empty($message) ) {
                    continue;
                }
                
                $message = '<div>(' . implode("/", $subj) . ')</div><div>&nbsp;</div>' . $message;
                
                if (!$user['unsubscribe_key']) {
                    $user['unsubscribe_key'] = users::GetUnsubscribeKey($user['login']);
                }
                $this->recipient[] = array(
                    'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">",
                    'extra' => array(
                        'USER_NAME'    => $user['uname'],
                        'USER_SURNAME' => $user['usurname'],
                        'USER_LOGIN'   => $user['login'],
                        'MESSAGE'      => $message,
                        'UNSUBSCRIBE_KEY' => $user['unsubscribe_key']
                    )
                );
                
                $count++;
            }
            
            $this->bind($massId, true);
            
        }
        
        return $count;
        
    }


    /**
     * ������ �������������� ������. ���������� ���������� �� ��������� e-mail.
     * @param  string  $mail  e-mail.
     *
     * @return string         ��������� ������.
     */
    function Remind($mail) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $uuid = $t_user->Remind($mail, $error);
        if ($uuid && !$error) {
            $msg = 
			
			"�� �������� ��� ������, �.�. ��� e-mail ����� ��� ������ �� ����� FL.ru ��� ������� ������������ ������ � �������� {$t_user->login}. ��� �������������� �������, ����������, ��������� �� ������ <a href='{$GLOBALS['host']}/changepwd.php?c={$uuid}{$this->_addUrlParams('b', '&')}'>{$GLOBALS['host']}/changepwd.php?c={$uuid}</a> ��� ���������� �� � �������� ������ ��������.<br/><br/>���� �� �� ���������� ������ �� ����� FL.ru ��� ��������� ���� e-mail � ������ �������������� ������. ��������, ���� �� ����� ������������� ������ �������.";
            $this->message = $this->GetHtml($t_user->uname, $msg, array('header'=>'simple', 'footer'=>'simple'));
            $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
            $this->subject = "��������� ������� ������ �� FL.ru";
            $this->SmtpMail('text/html');
        }
        return $error;
    }
	
	
    /**
     * ���������� ��������� ��������������������� ����� � ����������� �� ��������� ��������.
     * @param string  $login    ����� �����.
     * @param string  $passwd   ������ �����.
     * @param string  $code     ��� ���������.
     * @param string  $masterId id ������������ � ������� �����������
     * @param string  $uType    ��� ������������ (frl, emp) ��� ������� �����������
     *
     * @return string   ��������� ������.
     */
    function NewUser($login, $passwd = false, $code = false, $masterId = false, $uType = false){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUser($login, false);
        
        $subject = "�� ������� ������������������ �� FL.ru";
        
        $search_factor = is_emp($t_user->role) ? "������������" : "� ���������� ������";

        $message .= "����������� ��� � �������� ������������ �� ����� <a href='https://fl.ru/' target='_blank'>FL.ru</a>.";
        if($code) $message .= "<br/>�������� ���� ������������ ������� �� ������ <a href='".$GLOBALS['host']."/registration/activate.php?code=$code".($masterId? "&m={$masterId}": "").($uType? "&u={$uType}": "")."{$this->_addUrlParams('b', '&')}' target='_blank'>".$GLOBALS['host']."/registration/activate.php?code=$code".($masterId? "&m={$masterId}": "").($uType? "&u={$uType}": "")."</a>";
        $message .= "<br/><br/><strong>���� ������� ������:</strong><br/><br/>�����: {$t_user->login}<br/>������: {$passwd}<br/>����������, ��������� �� � �� ����������� ������� �����.<br><br>";
        $message .= "��� ��������� ������ ".$search_factor.":<br/><br/>";

        if(is_emp($t_user->role)) {
            $message .= "1. ����������� <a href='".$GLOBALS['host']."/public/?step=1' target='_blank'>������</a> ��� <a href='".$GLOBALS['host']."/public/?step=1&kind=7' target='_blank'>��������</a><br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;������� ������, ������� �� ������ � ����� ���������� � <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;��� ��������� ������ ������� ����������� � ������ ��������������.<br/><br/>";
            $message .= "2. ��������� <a href='".$GLOBALS['host']."/masssending/' target='_blank'>��������</a> �� �����������<br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;��������� ������� ������ �����������, �������� ����� �����������<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;� ��������� ������� �� ����� ��������������.<br><br>";
            $message .= "3. ����������� <a href='".$GLOBALS['host']."/freelancers/' target='_blank'>������� �����������</a><br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;�������������� ����� ����������� � ��������, ������ ��� ���������,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;������ � ��������� � ������� ������� ��������������.<br><br>";
            $message .= "4. ������� ������� �� ����� <a href='http://dizkon.ru' target='_blank'>Dizkon.ru</a><br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;���� ��� ����� ������� ��� ������ ����������� �������,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;�������������� ����� �������� - DizKon.ru.<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;�������� ������� � ������ ��� ����������� � ���������� ��������������.<br><br>";
        } else {
            require_once $_SERVER['DOCUMENT_ROOT'].'/classes/payed.php';
            
            $message .= "1. ������ ������� <a href='".$GLOBALS['host']."/payed/' target='_blank'>���</a> �� <strike style='color:#d7d7d7'>".payed::getPriceByOpCode(48)."</strike> ".payed::getPriceByOpCode(163)." ������<br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;��������� �� �������, �������� � �������� ��� �����������.<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;������ <a href='".$GLOBALS['host']."/payed/' target='_blank'>���</a> � ��������� �������� ������.<br><br>";
            
            $message .= "2. ��������� <a href='".$GLOBALS['host']."/users/{$login}/setup/main/' target='_blank'>�������</a> � <a href='".$GLOBALS['host']."/users/{$login}/setup/portfolio/' target='_blank'>���������</a><br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;������� � ������� ���� ���������� ������ � �������� ���,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;��������� ������� ������ � ������� ������� ��������������.<br><br>";
            
            $message .= "3. �������� �� ������������� <a href='".$GLOBALS['host']."/projects/' target='_blank'>������� �� �������</a><br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;����������� ������ �������� �� ������� �, �������� ������ �� <a href='".$GLOBALS['host']."/users/{$login}/setup/specsetup/' target='_blank'>�����</a><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='".$GLOBALS['host']."/users/{$login}/setup/specsetup/' target='_blank'>��������������</a>, �������� � �������� �� ���������� ��� �������.<br><br>";
            
            $message .= "4. ����������������� ����� �� ����� <a href='http://dizkon.ru' target='_blank'>Dizkon.ru</a><br><br>";
            $message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;���� �� ������ ����������� � ��������� � ������������ ���������<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;�������������� ����� �������� - DizKon.ru.<br><br>";
        }
        
        $message .= "���� � ��� �������� �������, ���������� � <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}' target='_blank'>������ ��������� FL.ru.</a><br/><br/>";
        
        $this->message = $this->GetHtml('', $message, array('header'=>'simple_with_add', 'footer'=>'simple'));
        //$this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->recipient = "{$t_user->login} <".$t_user->email.">";
        $this->subject = $subject;
        if (!$this->send('text/html')) return '��������� ��������� ���������';
        //if (!$this->SmtpMail('text/html')) return '��������� ��������� ���������';
        return '';
    }
    
    /**
     * ���������� ������ � ���, ��� �������� ����� �������������� ����.
     *
     * @param string $sLogin ����� ��������������� �����.
     * @param string $sName ��� ��������������� �����.
     * @param string $sSurname ������� ��������������� �����.
     */
    function adminNewSuspectUser( $sLogin = '', $sName = '', $sSurname = '' ) {
        $this->message = $this->GetHtml( '', 
'� ������ <a href="' . $_SERVER["HTTP_HOST"] . '/siteadmin/suspicious-users/' . $this->_addUrlParams('b') . '">' . $_SERVER["HTTP_HOST"] . '/siteadmin/suspicious-users/</a> �������� ����� �������:<br />
----------------------------<br />
�����: <a href="' . $_SERVER["HTTP_HOST"] . '/users/' . $sLogin . $this->_addUrlParams('b') .'">' . $sLogin . '</a><br />
���: ' . $sName . '<br />
�������: ' . $sSurname . '<br />
----------------------------<br />' );
        
        $this->recipient = 'info@FL.ru';
        $this->subject   = '�������������� ������������ �� ����� '.$_SERVER["HTTP_HOST"];
        $this->SmtpMail( 'text/html' );
    }
	

    /**
     * ���������� ������������ ����� � ����� ������.
     * @param int $uid   users.uid �����, ���������� ������.
     * @param string $passwd   ����� ������.
     *
     * @return string   ��������� ������.
     */
    function ChangePwd($uid, $passwd){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUserByUID($uid);
        
        $msg = 
        "�� ����� FL.ru ���� ����������� ��������� ���������� ��������, � ���������� ������ �������� 
        � �������� ������ ����������� ����� ��� ������ ����� ������ ��������� �����. 
        ���� �������� ������ ����� ���������� ��������� ���, ����������, ���������� � ������ ��������� 
        FL.ru �� ������ <a href='http://feedback.fl.ru/' target='blank'>http://feedback.fl.ru/</a>";
        
		$this->message = $this->GetHtml($t_user->uname, $msg, array('header'=>'simple', 'footer' => 'simple'));
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "��������� ���������� ������� ������ �� ����� FL.ru";
        if (!$this->SmtpMail('text/html')) return '��������� ��������� ���������';
        return '';
    }

	
    /**
     * ���������� �������������� ����� � ���, ��� ������� ��� �������� � ��������� ���. ���������� �� hourly.php.
     *
     * @return string   ��������� ������.
     */
    function SendWarnings(){
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
        $t_user = new payed();
        $all = $t_user->GetWarnings();
        if ($all) foreach ($all as $ikey=>$recp){
            $page = ( substr($recp['role'], 0, 1) != 1 ) ? 'payed' : 'payed-emp';
            $body = 
            "����������, ��� ".date('d '.monthtostr(date('m', strtotime($recp['to_date']))).' Y � H:i ', strtotime($recp['to_date'])).
            "������������� ����� �������� �������������� ���� �������� PRO �� ����� FL.ru. 
             �� ������ <a href='{$GLOBALS['host']}/$page/{$this->_addUrlParams('b')}'>��������</a> ���� �������� ����������������� ��������.";
            
            $this->message = $this->GetHtml($recp['uname'], $body, array('header' =>'simple', 'footer'=>'simple_adv'));
            //$this->recipient = "\"".$recp['uname']." ".$recp['usurname']." [".$recp['login']."]\"<".$recp['email'].">";
            $this->recipient = $recp['uname']." ".$recp['usurname']." [".$recp['login']."] <".$recp['email'].">";
            $this->subject = "������������� ���� �������� ������ �������� PRO �� FL.ru";
            $this->SmtpMail('text/html');
        }
        return '';
    }


    /**
     * ���������� �������������� ����� � ���, ��� ����� ���������� ���� �� ������, � �� ������. ��������.
     * @deprecated
     *
     * @return string   ��������� ������.
     */
    function SendUnactive(){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer.php';
		$t_user = new freelancer();
        $all = $t_user->GetUnactive();
        if ($all) foreach ($all as $ikey=>$recp){
            $this->message = "

������������.

�� �� ���������� �� ����� � ������� ���� �������, ��� ������� ����� ������ ".date('d '.monthtostr(date('m', strtotime($recp['to_date']))).' Y � H:i ', strtotime($recp['to_date'])).".
���� �� ������ �������� �������, ���������� ������������ �� ����� ".$GLOBALS['host']."

-- 
������� \"FL.ru\"
info@FL.ru
".$GLOBALS['host'];
            //$this->recipient = "\"".$recp['uname']." ".$recp['usurname']." [".$recp['login']."]\"<".$recp['email'].">";
            $this->recipient = $recp['uname']." ".$recp['usurname']." [".$recp['login']."] <".$recp['email'].">";
            $this->subject = "��������! �������� �������� - FL.ru";
            $this->SmtpMail();
        }
        return '';
    }


    /**
     * @deprecated
     * ������� ��� ������. ��� �� ������������ �������� �����������.
     *
     * @return string   ��������� ������.
     */
    function OrderFP($login, $order_id, $d_time, $sum){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUser($login);
        $this->message = "������������, ".$t_user->uname.".

������� \"FL.ru\" ���������� ��� �� ���� ������� ����������� � ����� ������ �������.


������ FL.ru ���� ������� ���� ������ �� ������� ���������� �� ������� ��������.
��������� ������:

���� �������� - $d_time

�����: $".$sum."

��� ������ � ���������� ���������� �������:

$t_user->uname $t_user->usurname [$t_user->login] (fp $order_id) 


��������� ������ ����� ������������� � ������� ���������� ������� �� ���� FL.ru
����� ���������� ���� �������� � ������� �������� ��� ������ ����� �������� �� ���� FL.ru





������� ������:

1. WebMoney 
��� ������������� � ������� WebMoney 200477354071.
��� ������ � �������������� WebMoney Transfer

R199396491834 - ������� ��� ������� � ������
Z801604194058 - ������� ��� ������� � �������� 
������� ���������� ����������� ��� ��������� ������.

� ���������� �������:
$t_user->uname $t_user->usurname [$t_user->login] (fp $order_id) 


2. ������.������ 
��� ����� ����� � ������� ������.������ 4100126337426.

� ���������� �������:
$t_user->uname $t_user->usurname [$t_user->login] (fp $order_id) 


3. Western Union 

����������� ������� 50 ��������.
��� ���������� �������� � ������� Western Union ���������� ������� ������ � ���������� �� info@FL.ru.


4. ����������� ������

����������� ������� 100 ��������.
��� ���������� ������ �� ������������ �������� ���������� ������� ������ � ���������� �� info@FL.ru.


-- 
������� \"FL.ru\"
info@FL.ru
".$GLOBALS['host'];
        $this->message = input_ref($this->message);
        $this->recipient = "\"$t_user->uname $t_user->usurname [$t_user->login]\"<".$t_user->email.">";
        $this->subject = "������� ����� �� ������� �������� - FL.ru";
        if (!$this->SmtpMail()) $error = '���������� ��������� ���������';
        return $error;
    }

	
    /**
     * ���������� ����������� ����� � ���, ��� ������ ���� ��� ������ ������� (��������, ���).
     *
     * @param  string  $from_login  users.login -- �� ���� �������.
     * @param  string  $to_login    users.login -- ���� �������.
     * @param  string  $msg         �� ������������.
     * @param  int     $idg         present.id -- ��. �������.
     *
     * @return string               ��������� ������.
     */
    function NewGift($from_login, $to_login, $msg, $idg){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUser($to_login);
        $f_user = new users();
        $f_user->GetUser($from_login);
        
        $body = "�� �������� <a href=\"".$GLOBALS['host']."/present/?id=$idg{$this->_addUrlParams('b')}\">�������</a> �� ������������ <a href=\"".$GLOBALS['host']."/users/{$f_user->login}{$this->_addUrlParams('b')}\">{$f_user->uname} {$f_user->usurname}</a> [<a href=\"".$GLOBALS['host']."/users/{$f_user->login}{$this->_addUrlParams('b')}\">{$f_user->login}</a>]";
        
        $this->message = $this->GetHtml($t_user->uname, $body, array('header'=>'simple', 'footer'=>'simple'));
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "������� �� ����� FL.ru";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }
    
    /**
     * �����-����: ����������� � ���, ��� �������� ����������� �������
     * 
     * @param int $to_id uid ����� ���� ���� �����������.
     */
    function alphaBankMistakeSorry( $to_id, $op_date ) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUserByUID( $to_id );
        
        $this->message = $this->GetHtml( $t_user->uname, '
����������� ��� � ���, ��� '.date('d.m.Y', strtotime($op_date)).' � '.date('H:i', strtotime($op_date)).' ��� ��� �������� �������� ������� ������� �� ������ ���� �� ����� FL.ru. ������ ������������ ���������� ���� ��������, ����������� �� ������ �������� �������� ������� � ������ �����.
<br />
�������� ���� ��������� �� ����������!
<br />
<br />
', 'simple' );
        
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject   = '�������� ��������� �� ��������� ����������';
        if ( !$this->SmtpMail('text/html') ) $error = '���������� ��������� ���������';
        return $error;
    }
	

    /**
     * ���������� ���������� (��� �������������) �����, ������� �������� ����� ������ e-mail.
     *
     * @param   string  $login    users.login -- ����� �����.
     * @param   string  $newmail  ����� ���� �����.
     * @param   string  $code     ��� �������������.
     *
     * @return  string            ��������� ������.
     */
    function ConfirmNewEmail($login, $newmail, $code){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUser($login);
        $this->message = $this->GetHtml($t_user->uname, "
������ ������ ���������� �������� ������� ������� FL.ru � �� ������� ������.
<br />
����������, ���� � ��� ���� �������, ��������� � ���� �� ������: <a href='http://feedback.fl.ru/' target='_blank'>http://feedback.fl.ru/</a>
<br />
<br />
����� �������� ��� ������ e-mail �� $newmail ��������� �� ���� ������
<a href='".HTTP_PREFIX.$_SERVER["HTTP_HOST"]."/activatemail.php?code=$code{$this->_addUrlParams('b', '&')}'>".HTTP_PREFIX.$_SERVER["HTTP_HOST"]."/activatemail.php?code=$code</a>
", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "��������� ����������� ����� �� ����� FL.ru";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }

	
    /**
     * ���������� ����������� ����� � ���, ��� ��� ��������.
     * �������������� ������
     *
     * @param   string   $login   users.login -- ����� �����.
     * @return  string            ��������� ������.
     */
    function SendBan($login) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$f_user = new users();
        $f_user->GetUser($login);
        $uid = $f_user->GetUid($error, $login);
        $ban = $f_user->GetBan($uid);

        $this->message = "���������� ������ ��������: $f_user->uname $f_user->usurname! <br />
<br />
�� ������ ������� � ������ �������� �� ����� FL.ru �� ������� ������������� ���������.<br />
<br />
";

        switch ($ban["reason"]) {
            case 1:
                $this->message .= "�������: ������ ������������ ��������� �� ����� <br /><br />";
                break;
            case 2:
                  $this->message .=   "�������: ���� � ������ <br /><br />";
                break;
            case 3:
                  $this->message .=   "�������: ���� � �������� <br /><br />";
                break;
        }
        $this->message .= "

".($ban["comment"] ? "����������� ��������������: ".$this->ToHtml($ban["comment"])."<br /><br />" : "")."

��� ���� ��������� ��������� � ����������. <br />
����� ������������ ������ � ��������, ��� ���������� ��������� � �������� FL.ru �� ������ <a href='http://feedback.fl.ru/' target='_blank'>http://feedback.fl.ru/</a>. <br />
<br />
����� �� ��� ��������� �� ����� ��������������� <br />
������� FL.ru";
        // print $this->message; exit;
        $this->message = $this->GetHtml($f_user->uname, $this->message, array());
		$this->recipient = "{$f_user->uname} {$f_user->usurname} [{$f_user->login}] <".$f_user->email.">";
        $this->subject = "��� �� FL.ru";
        $this->from = "FL.ru <administration@FL.ru>";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
	}
	
	/**
	 * ���������� ����������� � ���, ��� ������������ ��������.
	 * ����� ������ � ������� �������
	 * 
	 * @param  int $uid UID ������������.
	 * @param  string $reason ����� ������� ����������
	 * @return bool true - �����, false - ������
	 */
	function SendBan2( $uid, $reason ) {
	    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
	    $f_user = new users();
        $f_user->GetUserByUID( $uid );
        
        $this->message = "���������� ������ ��������: $f_user->uname $f_user->usurname! <br />
<br />
�� ������ ������� � ������ �������� �� ����� FL.ru �� ������� ������������� ���������.<br />
<br />
�������: " . reformat( $reason, 24, 0, 0, 1, 24 ) . "<br />
<br />
��� ���� ��������� ��������� � ����������. <br />
����� ������������ ������ � ��������, ��� ���������� ��������� � �������� FL.ru �� ������ <a href='http://feedback.fl.ru/' target='_blank'>http://feedback.fl.ru/</a>. <br />
<br />
����� �� ��� ��������� �� ����� ��������������� <br />
������� FL.ru
";
        
        $this->message   = $this->GetHtml( $f_user->uname, $this->message, array() );
		$this->recipient = "{$f_user->uname} {$f_user->usurname} [{$f_user->login}] <{$f_user->email}>";
        $this->subject   = '��� �� FL.ru';
        $this->from      = 'FL.ru <administration@FL.ru>';
        
        return $this->SmtpMail( 'text/html' );
	}

    /**
     * �������� ������ � �������� ���������� (����������� ������)
     *
     * @param integer $uid      UID �����
     * @return string           ������ �� ������
     */
    function DocsBack($uid){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$f_user = new users();
        $f_user->GetUserByUID($uid);
        $this->message = $this->GetHtml($f_user->uname,
"������������, $f_user->uname $f_user->usurname!
<br />
<br />
����������� ��������� �� ������ ������������ ������� ���������.�������� �� ������� ������������
�������� ����� � ���������� ��� ������ �� �������� ���� ������ �� �����.
����������, ��������� � ���������� �� ������ <a href='mailto:finance@FL.ru'>finance@FL.ru</a>

� ���������, ������� FL.ru", array());
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "��������� �� ������������ ������� �� FL.ru";
        $this->from = "FL.ru <finance@FL.ru>";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }
	

    /**
     * �������� ������ �� ������ ������ ����������� �� ���
     *
     * @param integer $frl_id       UID ����������
     * @param integer $prj_id       id �������
     * @return string
     */
    function NoRiskCancelFrl($frl_id, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        $t_user->GetUserByUID($frl_id);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "
		
�������� ���, ��� ����� ������� <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('f')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a>
�� ����������� ������ ������ ������� � ����� �����������. 

", 'simple');

        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "����� ����������� � ����������� ������";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }
	
	
    /**
     * �������� ������ � ���, ��� �������� �������� �3 �� ���
     *
     * @param string $login     ����� ����������
     * @param integer $prj_id       id �������
     * @return string
     */
    function NoRiskT3Send($login, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        $t_user->GetUser($login);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "
		
�������� ������ ��� ����������� �������, ������ � ����� �� ������� <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('f')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a>
<br />
����������, ������������ � ��������� � ����������� ���� ��������.

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "����������� �������, ������������ ������� � ������ �� ����������� ������";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }

	
    /**
     * �������� ������ � ���, ��� �������� �������� ����� �3 �� ���
     *
     * @param string $login         ����� ����������
     * @param integer $prj_id       id �������
     * @return string
     */
    function NoRiskNewT3Send($login, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        $t_user->GetUser($login);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "
		
�������� ���, ��� � ����������� �������, ������ � ����� �� ������� <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('f')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a> ������� ��������� (����������).
<br />
����������, ������������ � ����� ������� � ����������� ���� ��������. 

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "����������� �������, ������ � ����� �� ����������� ������ ���� �������� ";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }

    /**
     * �������� ������ �� ��������� � �������� �� ��� ����������� (������ ���������)
     *
     * @param integer $uid          UID ���������
     * @param integer $prj_id       id �������
     * @return string
    */
    function NoRiskArbitrageEmp($uid, $prj_id){

        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';
        $t_user = new employer();
        $t_user->GetUserByUID($uid);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
		$this->message = $this->GetHtml($t_user->uname, "
		
�������� ���, ��� ����������� ������� �������� ��� ������� ������� ��������,
��������� � �������� ���������� ���� ������� <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('e')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a>

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "����������� ������� �������� �� ����������� ������";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }

	
    /**
     * �������� ������ �� ��������� � �������� �� ��� ���������� (������ ����������)
     *
     * @param integer $uid          UID ����������
     * @param integer $prj_id       id �������
     * @return string
     */
    function NoRiskArbitrageFrl($uid, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer.php';
        $t_user = new freelancer();
        $t_user->GetUserByUID($uid);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "
		
�������� ���, ��� �������� ������� �������� ��� ������� ������� ��������,
��������� � �������� ������ ��� �������� <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('f')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a> �� ����������� ������.

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "�������� ������� �������� �� ����������� ������";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }


    /**
     * �������� ������ � ���������� ��� (�������� ����� �� ������)
     *
     * @param integer $frl_id       UID ����������
     * @param integer $prj_id       id �������
     * @return string
     */
    function NoRiskClosed($frl_id, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUserByUID($frl_id);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "
		
�������� ���, ��� ����� ������� <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('f')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a> �� ����������� ������ ������� ������ �����������.
<br />
�� ������ �������� ������ ������� ��� ��������.

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "�������� ������ ������ �� ����������� ������";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }
  
    /**
     * �������� ������ � ������� ��������� �� ���
     *
     * @param integer $uid          UID �����
     * @param integer $prj_id       id �������
     * @return string
     */
    function NoRiskArbiterClosed($uid, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUserByUID($uid);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "

�������� ���, ��� �������� ����� ������� �� ������� ��������, ��������� � �������� ������ ��� �������� 
<a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('b')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a> �� ����������� ������.

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "�������� ����� ������� �� ����������� ������";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }

    /**
     * �������� ������ � ������� ����� �� ���
     *
     * @param integer $uid          UID ����������
     * @param integer $prj_id       id �������
     * @return string
     */
    function NoRiskMoneyReserved($uid, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer.php';
		$t_user = new freelancer();
        $t_user->GetUserByUID($uid);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "

�������� ���, ��� �������� �������������� ������ �� ������ ����� ��� ����������� ������ �� ������� <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('f')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a>.
<br />
������ �� ������ ���������� � ������.

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "�������� �������������� ������ ��� ����������� ������";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }
  
    /**
     * �������� ������ � �������� ����� �� ���� ����� �� ���
     *
     * @param integer $uid          UID �����
     * @param integer $prj_id       id �������
     * @return string
     */
    function NoRiskPaymentCommited($uid, $prj_id){
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
		$t_user = new users();
        $t_user->GetUserByUID($uid);
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        $prj = projects::GetProject($prj_id);
        $this->message = $this->GetHtml($t_user->uname, "

����������� ���! ����������� ������ �� ������� <a href='{$GLOBALS['host']}".getFriendlyURL("project", $prj['id']).$this->_addUrlParams('b')."'>{$GLOBALS['host']}".getFriendlyURL("project", $prj['id'])."</a> ���������.

", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "����������� ������ ���������";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }

    /**
     * �������� ������ � ���������������� �� ��������� ����������� ������� �� ����� ������� ��������
     *
     * @return string
     */
    function EndTopDaysPrjSendAlerts(){
        require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $projects = new projects();
        $alerts = $projects->GetAlertsPrjTopDays();
        if ($alerts) {
            foreach ($alerts as $alert) {
		        $prj_user = new users();
                $prj_user->GetUserByUID($alert['user_id']);
                if($prj_user->is_banned == '1') continue;
                if($alert['kind']==2 || $alert['kind']==7) {
                    // �������
                    $this->subject = "����������� �������� � ����� �������� FL.ru";
                    $this->message = $this->GetHtml($prj_user->uname, 
"{$alert['date_d']} ".monthtostr($alert['date_m'], true)." {$alert['date_y']} � {$alert['date_t']} ������������� ����� ����������� ������ �������� \"<a href='{$GLOBALS['host']}".getFriendlyURL("project", $alert['id']).$this->_addUrlParams('e')."'>".$alert['name']."</a>\" ������� ����� �������� �� ����� FL.ru.
<br><br>
��� ���� ����� �������� ���� ����������� ������ ��������, ���������� �������� ��������� ������� � ������ ��� �������������� �� ������ <a href=".$GLOBALS['host']. "/public/?step=2&public=".$alert['id']."&red=/users/".$prj_user->login."/setup/projects/".$this->_addUrlParams('e', '&').">".$GLOBALS['host']. "/public/?step=2&public=".$alert['id']."&red=/users/".$prj_user->login."/setup/projects/</a>.
", array('header' => 'simple', 'footer' => 'emp_projects'));
                } else {
                    // ������
                    $this->subject = "������ ������������� ���� ����������� ������ ������� �� ������� �������� FL.ru";
                    $this->message = $this->GetHtml($prj_user->uname, 
"������, {$alert['date_d']} ".monthtostr($alert['date_m'], true)." {$alert['date_y']} � {$alert['date_t']} ������������� ���� �������� ������ ������������ ������� �� ������� �������� ����� FL.ru�. 
�� ������ <a href=".$GLOBALS['host']. "/public/?step=1&public=".$alert['id']."&red=/users/".$prj_user->login."/setup/projects/".$this->_addUrlParams('e', '&').">�������� ����</a> ����������� ������ �������.
���������� ���, ��� ������������ � <a href='".$GLOBALS['host']. "/payed/".$this->_addUrlParams('e')."'>��������� PRO</a> �������� �� ������� ������� �����.", array('header' => 'simple', 'footer' => 'emp_projects'));
                }
                $this->recipient = $prj_user->uname." ".$prj_user->usurname." [".$prj_user->login."] <".$prj_user->email.">";
				$this->SmtpMail('text/html');
            }
        }
    }
	
	
    /**
     * �������� ������ � ���������� � ���, ��� ������������ ������� � ������
     * �������������� ������ � ����� �������
     * 
     * @param  string $login login ������������, �������� ����� ��������
     * @param  int $reason ��� �������
     * @return string
     */
    function SendBlogsBan( $login, $reason = 1 ) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        $t_user->GetUser($login);
        
        $sReason  = ( $reason == 2 ) ? '���� � ������' : (( $reason == 3 ) ? '���� � ��������' : '������ ������������ ��������� �� �����' );
        $sMessage = "������� FL.ru ������������� ��� ������ � ������ \"�����\" �� �������: $sReason.<br/><br/>
        ����������� ������ ��� ������������ � ��������� ����� FL.ru <a href='".WDCPREFIX."/about/documents/appendix_2_regulations.pdf'>".WDCPREFIX."/about/documents/appendix_2_regulations.pdf</a> � ����������������� ��� � ������ � ������� �� �����.";
        
        $this->message = $this->GetHtml($t_user->uname, $sMessage, 'info');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "������ � \"�����\" �� FL.ru ������������";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }

    /**
	 * ���������� ����������� � ���, ��� ������������ �������� � ������
	 * ����� ������ � ������� �������
	 * 
	 * @param  int $uid UID ������������.
	 * @param  string $reason ����� ������� ����������
	 * @return bool true - �����, false - ������
	 */
    function SendBlogsBan2( $uid, $reason ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
	    $t_user = new users();
        $t_user->GetUserByUID( $uid );
        
        $sMessage = "������� FL.ru ������������� ��� ������ � ������ \"�����\" �� �������: " . reformat( $reason, 24, 0, 0, 1, 24 ) . "<br/><br/>
        ����������� ������ ��� ������������ � ��������� ����� FL.ru <a href='".WDCPREFIX."/about/documents/appendix_2_regulations.pdf'>".WDCPREFIX."/about/documents/appendix_2_regulations.pdf</a> � ����������������� ��� � ������ � ������� �� �����.";
        
        $this->message   = $this->GetHtml( $t_user->uname, $sMessage, 'info' );
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject   = '������ � "�����" �� FL.ru ������������';
        $this->from      = 'FL.ru <administration@FL.ru>';
        
        return $this->SmtpMail( 'text/html' );
    }
	
    /**
     * �������� ����� ����� �� ��������� (manager.php)
     *
     * @param integer $uid   UID �����
     * @param integer $msg   ����� ������.
     *
     * @return string ��������� ������.
     */
    function SendManagerAnswer($uid, $msg) {

        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        $t_user->GetUserByUID($uid);
      
        $this->message = $this->GetHtml($t_user->uname, $this->ToHtml($msg."
      <br />
	  <br />
      -----------
	  <br />
      �������� ������� \"FL.ru\"
      "), array('header'=>'default', 'footer'=>''));
      
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "����� ��������� �� ������";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }
	
	
    /**
     * �������� ����� ����������� � ����������� �� ��� ������.
     *
     * @param integer $uid   users.uid �����, ����������� �����.
     * @param integer $uid2  users.uid �����, ����������� ����������� �� ���� �����.
     *
     * @return string ��������� ������.
     */
    function SendCommentOpinions($uid, $uid2) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        $t_user->GetUserByUID($uid);
        if($t_user->is_banned == '1') return null;
      
        $t_user2 = new users();
        $t_user2->GetUserByUID($uid2);
      
        $this->message = $this->GetHtml($t_user->uname, 
"������������ <a href='{$GLOBALS['host']}/users/{$t_user2->login}{$this->_addUrlParams('b')}'>{$t_user2->uname} {$t_user2->usurname}</a> [<a href='{$GLOBALS['host']}/users/{$t_user2->login}{$this->_addUrlParams('b')}'>{$t_user2->login}</a>]
������� ����������� �� ���� ������ �� �������� ������ ��������.
<br />
�� ������ ��������� ��� �� �������� �������� ������������ - <a href='{$GLOBALS['host']}/users/$t_user2->login/opinions/{$this->_addUrlParams('b')}'>{$GLOBALS['host']}/users/$t_user2->login/opinions/</a>
", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "����� ����������� �� ���� ������ �� FL.ru";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }
    
    /**
     * �������� ����� ����������� � ����������� �� ��� �����.
     *
     * @param integer $uid   users.uid �����, ����������� �����.
     * @param integer $uid2  users.uid �����, ����������� ����������� �� ���� �����.
     *
     * @return string ��������� ������.
     */
    function SendCommentFeedback($uid, $uid2, $isEdit = false) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        $t_user->GetUserByUID($uid);
        if($t_user->is_banned == '1') return null;
      
        $t_user2 = new users();
        $t_user2->GetUserByUID($uid2);
        
        $isEditText = $isEdit ? '�������' : '�������';
      
        $this->message = $this->GetHtml($t_user->uname, 
"������������ <a href='{$GLOBALS['host']}/users/{$t_user2->login}{$this->_addUrlParams('b')}'>{$t_user2->uname} {$t_user2->usurname}</a> [<a href='{$GLOBALS['host']}/users/{$t_user2->login}{$this->_addUrlParams('b')}'>{$t_user2->login}</a>]
$isEditText ����������� �� ��� ����� �� �������� ������ ��������.
<br />
�� ������ ��������� ��� �� �������� �������� ������������ - <a href='{$GLOBALS['host']}/users/$t_user2->login/opinions/{$this->_addUrlParams('b')}'>{$GLOBALS['host']}/users/$t_user2->login/opinions/</a>
", 'simple');
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->subject = "����� ����������� �� ��� ����� �� FL.ru";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }
	
	
	

    /**
     * �������� ��������� ������� ��������� �� �������� �����
     *
     * @param integer $uid id ������������
     * @param string $msg ���������
     * @param string $email e-mail �����
     * @return string ��������� ������
     */
    function SendManagerWork( $uid, $msg, $phone, $umail="", $fio="", $files=false, $email='' ){
		require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        if ($uid) {
            $t_user->GetUserByUID($uid);
        } else {
        	$t_user->uname = "������";
        	$t_user->usurname =  "������";
        	$t_user->login = "������";
        	$t_user->email = "no_reply@free-lance.ru";
        }
        
        if($umail == "") {
            $mail = $t_user->email;
        } else {
            $mail = $umail;    
        }
        
        if($fio == "") {
            $name = $t_user->uname." ".$t_user->usurname." [".$t_user->login."]";    
        } else {
            $name = html_entity_decode($fio, ENT_QUOTES);
        }
        
        $msg = "������������!\n\n".$msg;
        
        if($phone != "") {
            $msg .= "\n\n�������: $phone";
        }
        
        if($fio) {
            $msg .= "\r\n���: $fio";
        }
        
        $this->message = $this->GetHtml('', $this->ToHtml($msg), array());
        
        $this->recipient = ($email) ? $email : "�������� <{$GLOBALS['sManagerEmail']}>";
        $this->subject = "������ �����������, �������� �����";
        
		$this->from = "$name <$mail>";
        
		$attaches = $this->CreateAttach($files);
		if (!$this->SmtpMail('text/html', $attaches)) $error = '���������� ��������� ���������';
        return $error;
    }
    
    /**
     * �������� ��������� ������� ��������� �� ����� ������
     *
     * @param integer $uid id ������������
     * @param string $fio ������� ��� ���������
     * @param string $phone ������� ��� �����
     * @param string $time_to_call ������� ����� ������
     * @return string ��������� ������
     */
    function SendManagerOrderCall( $uid, $fio, $phone, $time_to_call, $client_email='', $email='' ){
		require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$t_user = new users();
        if ($uid) {
            $t_user->GetUserByUID($uid);
        } else {
        	$t_user->uname = "������";
        	$t_user->usurname =  "������";
        	$t_user->login = "������";
        	$t_user->email = "no_reply@free-lance.ru";
        }
        
        $mail = $t_user->email;
        
        if($fio == "") {
            $name = $t_user->uname." ".$t_user->usurname." [".$t_user->login."]";    
        } else {
            $name = html_entity_decode($fio, ENT_QUOTES);
        }
        
        $msg = "������������!\n\n";
        $msg .= "\n\n���: {$name}";
        $msg .= "\n\n�������: $phone";
        $msg .= "\n\n������� ����� ������: $time_to_call";
        if($client_email != '') $msg .= "\n\nE-mail: $client_email";
        
        $this->message = $this->GetHtml('', $this->ToHtml($msg), array());
        
        $this->recipient = ($email) ? $email : "�������� <{$GLOBALS['sManagerEmail']}>";
        $this->subject = "����� ������ ���������";
        
		$this->from = "$name <$mail>";
        
		if (!$this->SmtpMail('text/html', $attaches)) $error = '���������� ��������� ���������';
        return $error;
    }

	
    /**
     * �������� ��������� ������������� � ���, ��� ������� PRO ����� ������������� �������
     *
     * @param integer $user_id id ������������
     * @param  string $to_date ����/����� ��������� PRO
     * @return string ��������� ������
     */
    function PROEnding( $user_id, $to_date ) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/payed.php";
		$user = new users();
        $user->GetUser($user->GetField($user_id,$ee,'login'));
        if($user->is_banned == '1') return null;

        $cost = ( substr($user->role, 0, 1) != 1 ) ? payed::PRICE_FRL_PRO : payed::PRICE_EMP_PRO;
        $page = ( substr($user->role, 0, 1) != 1 ) ? 'payed' : 'payed-emp';
        $date = date('d '.monthtostr(date('m', time()+86400)).' Y ����', time()+86400);
        $time = date('H:i ', strtotime($to_date));
        $body = 
        "������, {$date}, � {$time} ������������� ���� �������� ������ �������� PRO. 
         ����������, ��� �� ���������� ������� ������������� ����������������� ��������. 
         ��� ��������, ��� ������, {$date}, ������� ������������� ������� �������� �������� PRO �� �����, 
         ��� ���� � ������ ����� ����� ������� " . $cost . " ���. (��������� �������� PRO �� �����). 
         ������ ������������� ����� �������� ����������������� �������� �������� ����������.";
        
         $this->message = $this->GetHtml($user->uname, $body, array('header' => 'simple', 'footer' => 'simple_adv'));
        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <".$user->email.">";
        $this->subject = "���� �������� ������ �������� PRO �������� ������";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }
    
    /**
     * �������� ��������� ������������ � ������������� ��������������� ��������� �������� PRO ��-�� �������� ������� �� �����
     *
     * @param integer $user_id id ������������
     * @return string ��������� ������
     */
    function PROAutoProlongError($user_id){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/payed.php";
		$user = new users();
        $user->GetUser($user->GetField($user_id,$ee,'login'));
        $cost = ( substr($user->role, 0, 1) != 1 ) ? payed::PRICE_FRL_PRO : payed::PRICE_EMP_PRO;
        $page = ( substr($user->role, 0, 1) != 1 ) ? 'payed' : 'payed-emp';
        $this->message = $this->GetHtml($user->uname, 
"����� �� ����� ����� ������������ ��� ������������� PRO. ��� ���� ����� ������� ������ �������� ��� PRO �� �����, ��� ���������� ��������� ��� ���� �� FL.ru �� $cost ���. (��������� �������� pro � �����).
<br />
���� ������ ������������� �������� ����������.
<br />
<br />
�� ������ ���������� �� ������ ������� �� �������� <a href='{$GLOBALS['host']}/$page/{$this->_addUrlParams('b', '&')}#pro_autoprolong'>{$GLOBALS['host']}/$page/#pro_autoprolong</a>", 'simple');
        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <".$user->email.">";
        $this->subject = "����� �� ����� ����� ������������ ��� ������������� PRO";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }
	
	
    /**
     * �������� ��������� ������������� � �� �������� �������������� ��������� �������� PRO
     *
     * @param integer $user_id id ������������
     * @return string ��������� ������
     */
    function PROAutoProlongOk($user_id){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/payed.php";
		$user = new users();
        $user->GetUser($user->GetField($user_id,$ee,'login'));
        $cost = ( substr($user->role, 0, 1) != 1 ) ? payed::PRICE_FRL_PRO : payed::PRICE_EMP_PRO;
        $page = ( substr($user->role, 0, 1) != 1 ) ? 'payed' : 'payed-emp';
        
        $body = 
        "���� �������� ������ �������� PRO ��� ������������� ������� �� �����, ��� ��� � ��� ���������� ������� ������������� PRO. 
         � ������ ����� ���� ������� ". $cost . " ���. (��������� �������� PRO �� �����). 
         �������� ��������: ���� ������ ������������� �������� ����������.  
         �� ������ <a href='{$GLOBALS['host']}/$page/{$this->_addUrlParams('b', '&')}#pro_autoprolong'>����������</a> �� ������������� �������� PRO.";
        
        $this->message = $this->GetHtml($user->uname, $body, array('header' => 'simple', 'footer' =>'simple_adv'));
        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <".$user->email.">";
        $this->subject = "��� ������� PRO ������� �� �����. " . $cost ." ���. ���� ������� � ������ �����";
        if (!$this->SmtpMail('text/html')) $error = '���������� ��������� ���������';
        return $error;
    }
	
	
    /**
     * ���������� ����������� � ����� ������������ � ���� �����.
     * 
     * @param string $message  �����������
     * @param resource $user   ������ �����
     */
    function CorporativeBlogNewComment($comment, $user, $p_user, $link=null)
    {  
		if(substr($user->subscr, 2, 1) == '1'){ } 
            
        $this->subject = "����� �� ����������� � ������������� ����� �� ����� FL.ru";

        
        $this->message = $this->GetHtml($p_user->uname, 

"������� FL.ru ���������� ��� �� ���� ������� ����������� � ����� ������ �������. 
<br /><br />
<a href='{$GLOBALS['host']}/users/{$user->login}'>{$user->uname} {$user->usurname}</a> [<a href='{$GLOBALS['host']}/users/{$user->login}{$this->_addUrlParams('b', '&')}'>{$user->login}</a>]
�������(�) ��� ����������� � ����������/������������ � ������������� ����� �� ����� FL.ru.

<br />--------
<br />".strip_tags(input_ref(LenghtFormatEx($comment['title'], 300), 1))."
<br />---
<br />".strip_tags(input_ref(LenghtFormatEx($comment['msgtext'], 300), 1))."
<br />--------
<br />
$link

");
        $this->recipient = $p_user->uname." ".$p_user->usurname." [".$p_user->login."] <".$p_user->email.">";
        $this->SmtpMail('text/html');
        
        return $this->sended;
    }
	
	
	/**
     * ����������� � ���������� �����������
     *
     * @return   string    ��������� ������
     */    
	function ContestReminder() {
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';
		$users = contest::WInterval('1 day');
		
		if (!$this->Connect())
			return "���������� ���������� � SMTP ��������";
		
		foreach ($users as $prj_id=>$u) {
            if ( intval($u['is_blocked']) > 0 ) {
                continue;
            }
            
            $project_name = htmlspecialchars($u['project_name'], ENT_QUOTES, 'CP1251', false);
            
			// ��������
			$user = new employer();
			$user->GetUserByUID($u['employer']);
			if ($user->email && substr($user->subscr, 8, 1) == '1') {
                
                $u['project_name'] = htmlspecialchars($u['project_name'], ENT_QUOTES, 'CP1251', false);
                
				$this->message = $this->GetHtml($user->uname, "
�������� ���, ��� �� ���������� ����������� � �������� �<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj_id).$this->_addUrlParams('e')."\">".$project_name."</a>� ������� ���� ����.
", array('header' => 'default', 'footer'=>'sub_emp_projects'), array('login'=>$user->login));
				$this->recipient = "$user->uname $user->usurname [$user->login] <".$user->email.">";
				$this->subject = '������� 1 ���� �� ���������� ����������� �������� �'.htmlspecialchars_decode($u['project_name'], ENT_QUOTES).'�';
				$this->SmtpMail('text/html');
			}

			// ����������
			foreach ($u['freelancer'] as $user) {
				if (!$user['email'] || substr($user['subscr'], 8, 1) != '1' || $user['is_banned'] == '1') continue;
				$this->message = $this->GetHtml($user['uname'], "
�������� ���, ��� ������� ���� ���� �� ���������� ����������� � �������� �<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj_id).$this->_addUrlParams('f')."\">".$project_name."</a>�.
�� ������ ������� � ����� <a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj_id)."?offer={$user['offer_id']}{$this->_addUrlParams('f', '&')}#offer-{$user['offer_id']}\">������</a>.
<br />
", array('header'=>'simple', 'footer'=>'default'), array('login' => $user['login']));
				$this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
				$this->subject = '������� 1 ���� �� ���������� ����������� �������� �'.htmlspecialchars_decode($u['project_name'], ENT_QUOTES).'�';
				$this->SmtpMail('text/html');
			}
		
		}
		return 0;
	}

	
	/**
     * ����������� �� ��������� ��������
     *
     * @return   string    ��������� ������
     */    
	function ContestEndReminder() {
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';
		$users = contest::WInterval('1 day', 'end_date');
		
		if (!$this->Connect()) return "���������� ���������� � SMTP ��������";
		
		foreach ($users as $prj_id=>$u) {
            
            $project_name = htmlspecialchars($u['project_name'], ENT_QUOTES, 'CP1251', false);
            
			// ��������
			$user = new employer();
			$user->GetUserByUID($u['employer']);
			if ($user->email && substr($user->subscr, 8, 1) == '1') {
				$this->message = $this->GetHtml($user->uname, "
�������� ���, ��� �������� ���� ���� �� ��������� �������� �<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj_id).$this->_addUrlParams('e')."\">".$project_name."</a>�.
", array('header' => 'simple', 'footer' => 'sub_emp_projects'), array('login'=> $user->login));
				$this->recipient = "$user->uname $user->usurname [$user->login] <".$user->email.">";
				$this->subject = '������� ���� �� ��������� �������� �'.htmlspecialchars_decode($u['project_name'], ENT_QUOTES).'�';
				$this->SmtpMail('text/html');
			}

			// ����������
			foreach ($u['freelancer'] as $user) {
				if (!$user['email'] || substr($user['subscr'], 8, 1) != '1' || $user['is_banned'] == '1') continue;
				
				$this->message = $this->GetHtml($user['uname'], "
�������� ���, ��� �������� ���� ���� �� ��������� �������� �<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj_id).$this->_addUrlParams('f')."\">".$project_name."</a>�.
<br />
�� ������ ������� � ����� <a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $prj_id)."?offer={$user['offer_id']}{$this->_addUrlParams('f', '&')}#offer-{$user['offer_id']}\">������</a>.
<br />
", array('header'=>'simple', 'footer'=>'default'), array('login' => $user['login']));
				$this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
				$this->subject = '������� ���� �� ��������� �������� �'.htmlspecialchars_decode($u['project_name'], ENT_QUOTES).'�';
				$this->SmtpMail('text/html');
			}
		
		}
		
		return 0;
	}
	
    /**
     * ������� �����-������� ���� ���� 
     * 
     * @param users $user 
     * @param CFile $file 
     * @param string $type ��� ��������� (sf - ����-�������, act - ���)
     * @return string
     */
    function DocSend(users $user, CFile $file, $type = 'sf') {
        if(!$user->uid) return false;

        $this->message = $this->GetHtml($user->uname,
        "������������, $user->uname $user->usurname!
        <br />
        <br />
        ��� ��������� ������� �������������. ����������, �� ��������� �� ����!
        <br />
        <br />
        � ���������� " . ($type == 'sf' ? '����' : '���') . " �� ������, ��������� �� ����� FL.ru (��� \"����\") <br />
        ��������� �����  ���������� ��� � ��������� �����.
        <br />
        <br />
        �� ���� �������� �� ������ ���������� �� ������ - <a href='mailto:finance@FL.ru'>finance@FL.ru</a> <br />
        ���������� �� ��������������!", array());

        $att = $this->CreateAttach($file);

        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <" . $user->email . ">";
        if ($type == 'sf') {
            $this->subject = "����-�������, ���������� �������� ����� �� ����� FL.ru (��� \"����\")";
        } else {
            $this->subject = "���, ���������� �������� ����� �� ����� FL.ru (��� \"����\")";
        }
        $this->from = "FL.ru <finance@FL.ru>";
        if (!$this->SmtpMail('text/html', $att))
            $error = '���������� ��������� ���������';

        return $error;
    }

    /**
     * ������� �����-������� ���� ���� ��� ������� ���������
     * 
     * @param users $user 
     * @param CFile $file 
     * @param string $type ��� ��������� (sf - ����-�������, act - ���)
     * @return string
     */
    function LMDocSend(users $user, CFile $file, $type = 'sf') {
        if(!$user->uid) return false;

        $this->message = $this->GetHtml($user->uname,
        "������������, $user->uname $user->usurname!
        <br />
        <br />
        ��� ��������� ������� �������������. ����������, �� ��������� �� ����!
        <br />
        <br />
        � ���������� " . ($type == 'sf' ? '����' : '���') . " �� ������, ��������� �� ����� FL.ru (��� \"����\") <br />
        ��������� �����  ���������� ��� � ��������� �����.
        <br />
        <br />
        �� ���� �������� �� ������ ���������� �� ������ - <a href='mailto:finance@FL.ru'>finance@FL.ru</a> <br />
        ���������� �� ��������������!", array());

        $att = $this->CreateAttach($file);

        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <" . $user->email . ">";
        if ($type == 'sf') {
            $this->subject = "����-�������, ������ ������� ��������� �� ����� FL.ru (��� \"����\")";
        } else {
            $this->subject = "���, ������ ������� ��������� �� ����� FL.ru (��� \"����\")";
        }
        $this->from = "FL.ru <finance@FL.ru>";
        if (!$this->SmtpMail('text/html', $att))
            $error = '���������� ��������� ���������';

        return $error;
    }
    
    /**
     * �������� ��������� ���� ������� �� ����� ����: 
     * 
     * @example file.txt
     * 'email1@email.ru'
     * 'email2@email.ru'
     * ...
     * 'email1299@email.ru'
     *
     * @param string $file_name   ������ ���� �� ����� ���� ���� �� � ����� �� �������� ��� ��������� �������
     * @param string $subject     ���� ���������
     * @param string $message     ���������
     * @param string $from        �� ���� ������
     */
    function massSendingForFile($file_name, $subject, $message, $from = 'FL.ru <no_reply@free-lance.ru>') {
        $file = file($file_name); // ������� ����
        if(count($file) < 1) return false;
        
        $this->subject = $subject;
        $this->message = $this->GetHtml(NULL, $message, NULL); 
        $this->from    = $from;
         
        foreach($file as $mail) {
            if(trim($mail) == "") continue;
            $mail = str_replace("'", "", $mail);
            $this->recipient = "<" . trim($mail) . ">";
            $this->SmtpMail('text/html');
        }
    }

    /**
     * ����������� ������ � ���, ��� ������������ ���� ��������� �� ������� "�������".
     *
     * @param string $login   ����� ������������.
     */
    function FinanceChanged($login) {
        $user = new users();
        $user->GetUser($login);
        if(!$user->uid) return;
        $sbr = sbr_meta::getInstance(sbr_meta::ADMIN_ACCESS, $user, is_emp($user->role));
        if(!$sbr->getReserved()) return;

        $this->subject = "������������ {$user->login} �������� ��������� �� �������� �������";
        $this->message = $this->GetHtml(NULL, 
          "������������ {$user->uname} {$user->usurname} [{$user->login}] �������� ��������� �� �������� �������:<br/><br/>
           <a href='{$GLOBALS['host']}/users/{$user->login}/setup/finance/{$this->_addUrlParams('b')}'>{$GLOBALS['host']}/users/{$user->login}/setup/finance/</a>
          ",
          'info'
        );
        $this->recipient = '<donpaul@FL.ru>';
        $this->SmtpMail('text/html');
    }

    /**
     * �����������, ��� ����� ��������� �������� "�������", ���� "������ ������", ���� � ����� ���� �������� ���.
     * @see sbr_meta::getReqvAlerts()
     * @deprecated ����� ������������ ������ �������� �����������
     */
    function SbrReqvAlerts() {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
        if($users = sbr_meta::getReqvAlerts()) {
            $this->subject = "���������� ������� ���������";
            foreach($users as $u) {
                $msg =  "
                  ����������, ������� ��� ����������� ������ �� ������� �<a href='{$GLOBALS['host']}/users/{$u['login']}/setup/finance/{$this->_addUrlParams('b')}'>�������</a>�. ��������� �� ������� ��������� ��������� ��� ����������� ��������
                  �� �������� ����� � ��������� ���������� ����������� ������.
                ";
                $this->message = $this->GetHtml($u['uname'], $msg, array('header'=>'simple', 'footer'=>'simple'));
                $this->recipient = $u['uname']." ".$u['usurname']." [".$u['login']."] <".$u['email'].">";
                $this->SmtpMail('text/html');
            }
        }
    }
    
    /**
     * ����������� � ������ ��������� ������ ���������� ����� ��� ��� � ���, ��� ����� ��� �������.
     * @see sbr_meta::getDeadlines()
     */
    function SbrDeadlineAlert() {
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/sbr.php");
        $url = $GLOBALS['host'].'/' . sbr::NEW_TEMPLATE_SBR . '/';
        if(!($deadlines = sbr_meta::getDeadlines())) return 0;
        foreach($deadlines as $stage) {
            $sbr_link_e = "������ �<a href='{$url}?site=Stage&id={$stage['id']}'>{$stage['name']}</a>� � ������� ����������� ������ �<a href='{$url}?id={$stage['sbr_id']}{$this->_addUrlParams('e', '&')}'>{$stage['sbr_name']}</a>�";
            $sbr_link_f = "������ �<a href='{$url}?site=Stage&id={$stage['id']}'>{$stage['name']}</a>� � ������� ����������� ������ �<a href='{$url}?id={$stage['sbr_id']}{$this->_addUrlParams('f', '&')}'>{$stage['sbr_name']}</a>�";
            for($e=0;$e<2;$e++) {
                $r = $e ? 'e_' : 'f_';
                if($stage[$r.'banned'] == '1') continue;
                if($stage['is_dead'] == 't') {
                    $this->subject = '����� ���������� ������� �� ����������� ������ �������';
                    $msg = "�������� ��� � ���, ��� ���������� ���� ���������� ".($e ? $sbr_link_e : $sbr_link_f)."<br/><br/>";
                    $msg .= $e ? "�� ��������� ������ ������ �������� ������������, � �� ������ ���������� � �������� ��� ��������� ���������� ������ ������.<br/><br/>
                                  ����������, ��������� � ����������� ��� ��������� ��������� ����� ���������� ��������."
                               : "�� ��������� ������ ������ �������� ������������, � �������� ������ ���������� � �������� ��� ��������� ���������� ������ ������.<br/><br/>
                                  ����������, ��������� � ���������� ��� ��������� ��������� ����� ���������� ��������.";
                }
                else {
                    $this->subject = '�� ��������� ����������� ������ �������� 1 ����';
                    $msg = $e ? "���������� ��� � ���, ��� �� ��������� ���������� {$sbr_link_e} ������� ���� ����.<br/><br/>
                                 �� ��� ������������ �� ���� ����������� �������, ���������� {$stage['f_uname']} {$stage['f_usurname']} [{$stage['f_login']}], � ��������, ��� �� ������ � ����."
                              : "�������� ��� � ���, ��� �� ��������� {$sbr_link_f} �������� 1 ����.";
                }
                $this->message = $this->GetHtml($stage[$r.'uname'], $msg, array('header'=>'simple', 'footer'=>'simple'));
                $this->recipient = $stage[$r.'uname']." ".$stage[$r.'usurname']." [".$stage[$r.'login']."] <".$stage[$r.'email'].">";
                $this->SmtpMail('text/html');
            }
        }
    }

    
    /**
     * ���������� ������ �������� � �� (���)
     * @see yd_payments
     * @see sbr_stages::ydPayout()
     *
     * @param string from_dt   ����, �� ������� ����� ������������ ������.
     */
    function sendYdDayRegistry($from_dt = NULL, $debug = false) {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yd_payments.php');
        $yd = new yd_payments();
        $yd->DEBUG = $debug;
        
        $from_dt = date('Y-m-d', strtotime($from_dt ? $from_dt : '-1 day'));
        if( !($enc_file = $yd->createRegistry($from_dt)) )
            return implode(' | ', $yd->errors);

        $default_from = $this->from;
        $this->from = yd_payments::REGISTRY_FROM;
        $this->subject = "������ �������� ��������, {$from_dt}, ".yd_payments::AGENT_NAME;
        $this->message = '';

        if(!$yd->DEBUG) {
            // � ��.
            $this->recipient = yd_payments::REGISTRY_YDTO;
            $this->SmtpMail('text/plain', $this->CreateLocalAttach($enc_file));
        }
        
        // � ����
        $noenc_att = $this->CreateLocalAttach($enc_file.yd_payments::REGISTRY_NOENC_SFX);
        foreach(yd_payments::$REGISTRY_VAANTO as $email) {
            $this->recipient = $email;
            $this->SmtpMail('text/plain', $noenc_att);
        }
        $this->from = $default_from;
    }
    
    /**
     * ����������� �� 1 ���� �� ��������� ����� ������� �������������, ���� ��������� �������������
     * 
     * @param  string $user_id UID ������������
     * @param  string $sum ����� � ������
     * @return string ��������� �� ������
     */
    function PaidSpecsEnding($user_id, $sum) {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/users.php";
        $user = new users();
        $user->GetUser($user->GetField($user_id, $ee, 'login'));
        if($user->is_banned == '1') return null;
        $date = date('d '.monthtostr(date('m', time()+86400)).' Y ����', time()+86400);
        $body = 
        "������, {$date}, ������������� ���� �������� ���������� ���� �������������� �������������.
        � ��� �������� ������� �������������. ��� ��������, ��� ������, {$date}, ������� ������������� 
        ������� �������� ��������� ���� �������������� �������������, ��� ���� � ������ ����� ����� ������� 90 ���. 
        �������� ��������: ���� ������ ������������� �������� ����������.
        <br/><br/>
        ���������� ���, ��� ���������� ��������� PRO� ����� ��������� ������� 5 �������������� �������������. 
        ��������� � ���� ������������ ����������������� �������� ����� ������ <a href='{$GLOBALS['host']}/payed/{$this->_addUrlParams('f')}'>�����</a>.";
        
        $this->message = $this->GetHtml($user->uname, $body, array('footer' => 'simple', 'header'=>'simple'));

        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <" . $user->email . ">";
        $this->subject = "������ �������� ���� �������� �������������� ������������� � ��������� �� FL.ru ";
        if (!$this->SmtpMail('text/html'))
            $error = '���������� ��������� ���������';
        return $error;
    }

    /**
     * ����������� �� �������������� ��������� ������� �������������
     *
     * @param  string $user_id UID ������������
     * @param  string $sum ����� � ������
     * @return string ��������� �� ������
     */
    function PaidSpecsAutopayed($user_id, $sum) {
        return; // #0022795
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/users.php";
        $user = new users();
        $user->GetUser($user->GetField($user_id, $ee, 'login'));
        if($user->is_banned == '1') return null;
        $body = 
        "���� �������� ��������� ���� �������������� ������������� ��� ������������� ������� �� �����, 
         ��� ��� � ��� �������� ������� ������������� ������ ������. � ������ ����� ���� ������� " . round($sum, 2) . " ���. 
         �������� ��������: ���� ������ ������������� �������� ����������. �� ������ <a href='{$GLOBALS['host']}/payed/{$this->_addUrlParams('f')}'>����������</a> �� ������� �������������.";
        $this->message = $this->GetHtml($user->uname, $body, array('footer' => 'simple', 'header'=>'simple'));

        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <" . $user->email . ">";
        $this->subject = "�������� ����� �������������� ������������� �������� �� �����";// {$sum} FM ���� ������� � ������ �����.";
        if (!$this->SmtpMail('text/html'))
            $error = '���������� ��������� ���������';
        return $error;
    }

    /**
     * ���������� email �������� �� /siteadmin/contacts/. ��������� �� hourly.php.
	 *
	 * @return   string   ��������� ������
     */
	function SendMailToContacts()
	{

		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/contacts.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $mails = contacts::GetMails();

        if($mails) {
            $fromSave = $this->from;
            foreach($mails as $mail) {
                $user = new users();
                $user->GetUser($user->GetField($mail['user_id'], $ee, 'login'));
                $this->subject = $mail['subject'];
                $attaches = array();
                if($mail['attaches']) {
                    $files = preg_split("/,/",$mail['attaches']);
        			foreach($files as $a) {
        				$attaches[] = new CFile('users/'.substr($user->login, 0 ,2).'/'.$user->login.'/upload/'.$a);
		        	}
		            $attaches = $this->CreateAttach($attaches);
                }
                $contact_ids = preg_split("/,/",$mail['contact_ids']);
                
                foreach ( $contact_ids as $contact_id ) {
                    $contact = contacts::getContactInfo( $contact_id );
                    
                    if ( $contact['emails'] ) {
                        $msg_text = $mail['message'];
                        $msg_text = preg_replace( "/%CONTACT_NAME%/", $contact['name'], $msg_text );
                        $msg_text = preg_replace( "/%CONTACT_SURNAME%/", $contact['surname'], $msg_text );
                        $msg_text = preg_replace( "/%CONTACT_COMPANY%/", $contact['company'], $msg_text );
                        
                        foreach ( $contact['emails'] as $email ) {
                            $this->from      = 'ekaterina@FL.ru';
        					$this->recipient = $contact['name']." <".$email.">";
        					$this->message   = $msg_text;
        					
        					$this->SmtpMail( 'text/html', $attaches );
                        }
                    }
                }
            contacts::DeleteMail($mail['id']);
            }
            $this->from = $fromSave;
        }
		return '';
	}

	/**
	 * ����������� �� ������ � ���������� ������
	 * 
	 * @param string $user_id UID ������������
	 * @param string $title ��������� ������
	 * @param string $msg ������� ������
	 */
    function delArticleSendReason($user_id, $title, $msg) {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/users.php";
        $user = new users();
        $user->GetUserByUID($user_id);

        if(!$user->uid) return;

        $this->subject = "���� ������ �� ����� ������������ �� FL.ru";
        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <" . $user->email . ">";
        
        
        //$body  = "������� �� ������� � ������� ������� � ���������";
        $reason = ".";
        if ($msg) {
            $reason = " �� �������:<br/><br/>
         ----<br/>
         {$msg}<br/>
         ----";
        }
        $body  = 
        "� ���������, ���� ������ �� ���� ������� � ���������� ����������� FL.ru. 
         � ���������� ������ �{$title}� ���� ��������$reason
         <br/><br/>
         ����������� ���������� ���� ����� ������ � ��������, ��� ����� ������������. 
         ���������� �� ����������� ������� � ������� <a href='{$GLOBALS['host']}/articles/{$this->_addUrlParams('b')}'>������� � ���������</a> ����� FL.ru.";

        $this->message = $this->GetHtml($user->uname, $body, array('header'=>'default', 'footer'=>'simple'));
        $this->SmtpMail('text/html');
    }

        /**
         * @deprecated @see pmail::DepositMail #0016262 
         * ����������� �� ����������� ���������.
         * ���������� ������� ����� � �������������� ����� ����� ���� �� ��� #0010465
         *
         * @param string $user_id UID ������������
         * @param string $billCode ����� �����
         * @param float $sum �����
         */
        public function depositNotify($uid, $billCode, $sum) {
//            echo $uid.' '.$bill_no.' '.$sum.' '.$op_id.' '.$payment_sys;
//            require_once dirname(__FILE__).'/users.php';
//            require_once dirname(__FILE__).'/reqv_ordered.php';
//            $reserved = account::getOperationInfo($op_id);
//            if($payment_sys == 4){// ������
//                $reqv_ordered = new reqv_ordered();
//                $billCode = '�-'.$reserved['billing_id'].'-'.sizeof($reqv_ordered->GetByUid($uid));
//            }elseif($payment_sys == 5){// �������
//                $DB = new DB('master');
//                if($code = $DB->val("SELECT bill_num FROM bank_payments WHERE billing_id=?i", $op_id)) {
//                    $billCode = $code;
//                }
//                
//            }else{
//                return false;
//            }
            $num_str = '��  ����� � '. $billCode;

            $t_user = new users();
            $t_user->GetUserByUID($uid);
            $this->message = $this->GetHtml($t_user->uname, $this->ToHtml(
"������� FL.ru ����������� ��� � ���, ��� �������� �������� {$num_str}
�� ����������� ������� � ����� {$sum} ".  ending($sum, '�����', '�����', '������')." ���� ��������� �� ��� ������ ���� �� �����.
"), array('header'=>'simple', 'footer'=>'simple'));
            $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
            $this->subject = "���������� ������� ����� �� FL.ru";
            $this->SmtpMail('text/html');
    }

    
    /**
     * �������� ����������� ���������� � ��� ��� ��������� �� ��� ��������
     * @param string $suids �������� ���� 27_11 ��� 27 - �� ����� ���, 11 - �� ������������   
     * @return bolean 
     */
    public function docsReceivedSBR($suids) {
        session_start();
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        list($stage_id, $user_id) = explode("_", $suids);
        $sbr = new sbr($user_id);
        $stage = current($sbr->getStages($stage_id));
        $t_user = new users();
        $t_user->GetUserByUID($user_id);
        
        $this->subject = "�������� ��������� �� ����������� ������ �{$stage->data['sbr_id']}/{$stage->data['id']}";
        $message = "������������ ���� ��������� �� ����������� ������ �{$stage->data['sbr_id']}/{$stage->data['id']} �������� ��������� FL.ru. 
                    �� ��������� ����� <a href='https://feedback.fl.ru/{$this->_addUrlParams('f', '?')}'>���������� ������� ����������� ������</a> ��� ����� ����������� �������� �������� � ���� ������ �� ����������� ������. 
                    �� ������ �������� �������������� ���������� ������������ ������������ �� ����������� ������� � ��������������� <a href='https://feedback.fl.ru/{$this->_addUrlParams('f', '?')}'>������� �������</a>.";
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->message = $this->GetHtml($t_user->uname, $message, array('header'=>'default', 'footer'=>'simple_norisk'));

        return $this->SmtpMail('text/html');
    }
    
    /**
     * �������� ������������� ����������� ���� �� �� ������� �� ����� �������� ��������� ���� -- �� ��������� 1 ����.
     * 
     * @param integer $days   �� ������� ���� ��������.
     */
    public function sendReminderUsersUnBan($days = 1) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        $users = users::getReminderUsersUnBan($days);
        
        foreach($users as $user) {
            if (!$user['email']) continue;
            $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
            $day_str = $days . " " . ending($days, "����", "���", "����");
            $date_str = date('d.m.Y �.', strtotime("+ {$days} day"));
            
            switch($user['where']) {
                // � ������
                case 1:
                    $this->subject = "��� ����� ������ ������ � ������ �� FL.ru";
                    $message = 
                    "����� {$day_str}, {$date_str}, ������ � ������ �� FL.ru ����� ��� ��� ������. 
                    �� ��������� ���������� ������� � ������ ������ ����� � ������� ����������� ��� ������������ � <a href='https://feedback.fl.ru/article/details/id/168{$this->_addUrlParams('b', '?')}' target='_blank'>��������� ���������</a> � ��������.
                    <br/><br/>
                    �� ���� ����������� �������� �� ������ ���������� � ���� <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}' target='_blank'>������ ���������</a>.";
                    $this->message = $this->GetHtml($user['uname'], $message, array('header'=>'default', 'footer'=>'simple'));
                    $this->SmtpMail('text/html');
                    break;
                // �� ���� �����
                case 0:
                    $this->subject = "����� ��� ������� ����� ������������� �� FL.ru";
                    
                    $message = 
                    "����� {$day_str}, {$date_str}, ��� ������� �� FL.ru ����� �������������. 
                    �� ��������� ���������� � ������� ����������� ��� ������������ � <a href='https://feedback.fl.ru/knowledgebase?category=38{$this->_addUrlParams('b', '?')}' target='_blank'>��������� �������.</a><br/><br/>
                    �� ���� ����������� �������� �� ������ ���������� � ���� <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}' target='_blank'>������ ���������</a>.";
                    $this->message = $this->GetHtml($user['uname'], $message, array('header'=>'default', 'footer'=>'simple'));
                    $this->SmtpMail('text/html' );
                    break;
            }
        }
        
        return '';
    }
    
    /**
     * ����������� �� ����� ��� #0015818: �������� ���������� �� ��������� ��� ������� 
     * 
     */
    function sendEmpContestWithoutBudget() {
        global $DB;
        $eHost = $GLOBALS['host'];
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/messages.php';
        $msg = new messages();
        $result = employer::GetPROEmployersCreatedProjectsWithoutPrice();
        $users = new users();
        $adminId = $users->GetUid($err, 'admin');
        $pHttp = str_replace("://", "", HTTP_PREFIX);
		$pHost = str_replace(HTTP_PREFIX, "", $eHost);        
        $this->subject = "�� ������� ������� �� ����������� � �������� �� FL.ru";
        if(count($result) > 0) {
            foreach($result as $user) {
                if (!$user['email'] || substr($user['subscr'], 7, 1) == '0') continue;
                $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
                $message  = "<p>�� ������������ <a href=\"{$eHost}/projects/{$user['prj_id']}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget\" target=\"_blank\">�������</a> �� ����� FL.ru, �� �� ������� ������ ��� ������ ��������. �� �������� ��������  �������� ����� ����������� �� �����������. �����  �������� ������ ��������, � � ���� � ������ ����, ������� � ���������, ����������� ��� ������ ��������� ���� ������� ��� ���������� ��������. ��������� ���������� ��������� � <a href=\"https://feedback.fl.ru/article/details/id/144?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget\" target=\"_blank\">��������������� �������</a> ������.</p>
<p>���� �� ��� ������� ������� � ������ ������ � ���������� �����������, ����������� ��������� �<a href=\"{$eHost}/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget\" target=\"_blank\">����������� ������</a>�, ����� ���� �� 100% ���������� � �������� ��������������.</p>
<p>�� ���� ����������� �������� �� ������ ���������� � ���� <a href=\"https://feedback.fl.ru/\" target=\"_blank\">������ ���������</a>.</p>";
        
        $contacts_message  = "������������!
�� ������������ {$pHttp}:/{�������}/{$pHost}/projects/{$user['prj_id']}?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget �� ����� FL.ru, �� �� ������� ������ ��� ������ ��������. �� �������� ��������  �������� ����� ����������� �� �����������. �����  �������� ������ ��������, � � ���� � ������ ����, ������� � ���������, ����������� ��� ������ ��������� ���� ������� ��� ���������� ��������. ��������� ���������� ��������� � {$pHttp}:/{��������������� �������}/feedback.FL.ru/article/details/id/144?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget ������.

���� �� ��� ������� ������� � ������ ������ � ���������� �����������, ����������� ��������� {$pHttp}:/{&laquo;���������� ������&raquo;}/{$pHost}/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget, ����� ���� �� 100% ���������� � �������� ��������������.

�� ���� ����������� �������� �� ������ ���������� � ���� {$pHttp}:/{������ ���������}/feedback.FL.ru/.";
                
                $this->message = $this->GetHtml($user['uname'], $message, array('header'=>'default', 'footer'=>'default'), array('login' => $user['login']));
                $msg->Add($adminId, $user['login'], $contacts_message, false, 0, true);    
                $this->SmtpMail('text/html' );
            }
        }
        
        $result = employer::GetNoPROEmployersCreatedProjectsWithoutPrice();
        if(count($result) > 0) {
            foreach($result as $user) {
                if (!$user['email'] || substr($user['subscr'], 7, 1) == '0') continue;
                $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
                $message  = "<p>�� ������������ <a href=\"{$eHost}/projects/{$user['prj_id']}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget\" target=\"_blank\">�������</a> �� ����� FL.ru, �� �� ������� ������ ��� ������ ��������. �� �������� ��������  �������� ����� ����������� �� �����������. �����  �������� ������ ��������, � � ���� � ������ ����, ������� � ���������, ����������� ��� ������ ��������� ���� ������� ��� ���������� ��������. ��������� ���������� ��������� � <a href=\"https://feedback.fl.ru/article/details/id/144?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget\" target=\"_blank\">��������������� �������</a> ������.</p>
<p>����������, ��� ��� �������� PRO �� �� ������ ������������� �������� ���� �����������. ��� ���� ����� ����������� �������� � ������������� ������������, </p>
<p>����������� ���������� <a href=\"{$eHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget\" target=\"_blank\">������� PRO</a>, ������� ��������� ������ ���������� ���������� ���� �������������, ������������ �������� �� ��� ������� ������ ����� � ���� ������ �������� ������. </p>
<p>���� �� ��� ������� ������� � ������ ������ � ���������� �����������, ����������� ��������� �<a href=\"{$eHost}/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget\" target=\"_blank\">���������� ������</a>�, ����� ���� �� 100% ���������� � �������� ��������������.</p>

<p>�� ���� ����������� �������� �� ������ ���������� � ���� <a href=\"https://feedback.fl.ru/\" target=\"_blank\">������ ���������</a>.</p>";
                $contacts_message  = "������������!
�� ������������ {$pHttp}:/{�������}/{$pHost}/projects/{$user['prj_id']}?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget �� ����� FL.ru, �� �� ������� ������ ��� ������ ��������. �� �������� ��������  �������� ����� ����������� �� �����������. �����  �������� ������ ��������, � � ���� � ������ ����, ������� � ���������, ����������� ��� ������ ��������� ���� ������� ��� ���������� ��������. ��������� ���������� ��������� � {$pHttp}:/{��������������� �������}/feedback.FL.ru/article/details/id/144?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget ������.

����������, ��� ��� �������� PRO �� �� ������ ������������� �������� ���� �����������. ��� ���� ����� ����������� �������� � ������������� ������������,����������� ���������� {$pHttp}:/{������� PRO}/{$pHost}/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget, ������� ��������� ������ ���������� ���������� ���� �������������, ������������ �������� �� ��� ������� ������ ����� � ���� ������ �������� ������.

���� �� ��� ������� ������� � ������ ������ � ���������� �����������, ����������� ��������� {$pHttp}:/{&laquo;���������� ������&raquo;}/{$pHost}/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=konkurs_budget, ����� ���� �� 100% ���������� � �������� ��������������.

�� ���� ����������� �������� �� ������ ���������� � ���� {$pHttp}:/{������ ���������}/feedback.FL.ru/.";
                
                $this->message = $this->GetHtml($user['uname'], $message, array('header'=>'default', 'footer'=>'default'), array('login' => $user['login']));
                $msg->Add($adminId, $user['login'], $contacts_message, false, 0, true);    
                $this->SmtpMail('text/html' );
            }
        }
        
        return false;
    }

    
    /**
     * ����������� � �������������� ������ �����������
     * 
     * @return integer  id ������ (0 - �� ����������)
     */
    function sbrFeedbackEdit($fbId, $userId, $moderId, $sbr) {
        $moder = new users;
        $user  = new users;
        $moder->GetUserByUID($moderId);
        $user->GetUserByUID($userId);
        if ( substr($user->subscr, 14, 1) == '0' ) {
            return 0;
        }
        $uniqId = $fbId * 2 + 1;
        if($sbr->frl_id == $moderId) {
            $role_name = "�����������";
            $role_opinion = "��� ";
        } elseif($sbr->emp_id == $moderId) {
            $role_name = "��������";
            $role_opinion = "��� ";
        } else {
            $role_name = "���������";
            $role_opinion = "";
        }
        
        $this->subject   = "{$role_name} �������������� {$role_opinion}������������ �� FL.ru";
        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <{$user->email}>";
        $message = 
"{$role_name} �������������� ������������ �� ����������� ������.<br />
<br />
�� ������ ��������� �� �� <a href='{$GLOBALS['host']}/users/{$user->login}/opinions/#p_{$uniqId}'>�������� ������ ��������</a>.<br />
";
        $this->message = $this->GetHtml($user->uname, $message, array('header'=>'default', 'footer'=>'default'), array('login' => $user->login));
        return $this->send('text/html');
    }

    /**
     * ����������� � ����� ����������� � ���� ����������
     * @param $themeName         - ������������ ���� � ����������
     * @param $communeName       - ������������ ����������
     * @param $userLink          - ������ �� ������� ������������ ����������� �����������
     * @param $authorName        - ��� ������ �����������
     * @param $authorLogin       - ����� ������ �����������
     * @param $authorSurname     - ������� ������ �����������
     * @param $msgtext           - ����� ���������,
     * @param $domain            - �����
     * @param $url               - ������ �� �����������,
     * @param $recipientName     - ��� ����������,
     * @param $recipientSurname  - ������� ����������,
     * @param $recipientLogin    - ����� ����������,
     * @param $email             - ����� ����������  
     * @param $topicUrl          - ������ �� ���� ����������   
     * @param $communeUrl        - ������ �� ����������
     * */
    public function commentInThemeOfCommune($themeName, $communeName, $userLink, $authorName, $authorLogin, $authorSurname, $msgtext, $domain, $url, $recipientName, $recipientSurname, $recipientLogin, $email, $topicUrl, $communeUrl) {
        $body = " 
  	    � ��������� \"<a href=\"$topicUrl\">{$themeName}</a>\" ���������� \"<a href=\"$communeUrl\">{$communeName}</a>\", �� ������� �� ����������� <a href=\"$userLink\">{$authorName}</a> <a href=\"$userLink\">{$authorSurname}</a> [<a href=\"$userLink\">{$authorLogin}</a>] �������(�) <a href=\"$url\">�����������</a> � ���������/�����������.
  	                     <br/>
  	                     --------<br/>".reformat(LenghtFormatEx(strip_tags($msgtext, "<br><p>"), 300))."
  	                     --------
  	                     ";
  	    $mail = new smtp;
        $mail->subject   = '����� ����������� � ��������� �'.$themeName.'� ���������� �'.$communeName.'�';
        $mail->message   = $mail->GetHtml($recipientName, $body, array('header' => 'subscribe', 'footer' => 'subscribe'), array('login'=>$recipientLogin));
        $mail->recipient = $recipientName." ".$recipientSurname." [".$recipientLogin."] <".$email.">";
        $mail->send('text/html');
    }
    
    /**
     * ��������� � ������� (������ ��������� � ������ ����� �������� �������� �� ��������������� ������) -- ������ ��� �����������
     * 
     * @param type $sbr_id      �� ������
     * @param type $user_id     �� ������������
     * @return type 
     */
    public function SbrReservedMoney($sbr_id, $user_id) {
        session_start();
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        
        $sbr = sbr_meta::getInstanceLocal($user_id);
        $sbr->initFromId($sbr_id, false);
        $sbr_num = $sbr->getContractNum();
        $t_user = new users();
        $t_user->GetUserByUID($user_id);
        
        $url_sbr = "{$GLOBALS['host']}/" . sbr::NEW_TEMPLATE_SBR . "/";
        
        $this->subject = "�������������� �������� ������� �� ����������� ������ � {$sbr_num}";
        $message = "��� ���������� ����������� �������� �������� �� ����������� ������ <a href='{$url_sbr}?id={$sbr->id}'>� {$sbr_num}</a> � ������� " . ( pskb::PERIOD_RESERVED ) ." ������� ���� � ������� ������� �� ������ ���������������� ��������. � ��������� ������ ������ ����� ��������.";
        
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->message = $this->GetHtml($t_user->uname, $message, array('header'=>'default', 'footer'=>'norisk_robot'));

        return $this->send('text/html');
    }
    
    /**
     * ��� ������� �����������: ������������ ������, ��� ��, ��� � ����, ��� ��������.
     * ��� �������� ��� ����� �������������. ������������ ����� �����������.     
     * @param type $user_id     �� ������������
     * @return type 
     */
    public function employerQuickStartGuide($user_id) {
        session_start();
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $host = $GLOBALS['host'];
        $t_user = new users();
        $t_user->GetUserByUID($user_id);
        $login = $t_user->login;
        $this->subject = "FL.ru: ��� ������ ������ �� �����?";
        $message =
"<p>�� ���� �������������� ��� �� ���������� ����� ��������� ������ FL.ru. ������������ � ���� ����� � ��������� &ndash; �� �������� ����� ������������� ��� ������ ������� &laquo;<a href='{$host}/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_1'>���������� ������</a>&raquo;: ��� �� ���� ������������ � ���, ��� ��������� �� �������� ��� ����� ��� �������� � �����������. ������ �������: �� �������� ������ � ���� � �� ��.</p>

<p>������ �����, ��� ����� ����� ����������� � ���������� � ��� ������ �������. �� ������ �������� � ������������ � ������� ��������� �� ����� ��� ��������� ��������. ���� � ��� ����� ��������� �������, �� ������� ������ ���������� ���������� (�������, e-mail, Skype � �.�.) ������ ��� �����������, ������� ������� ��. �� ����������� ��� ���������� <a href='{$host}/payed/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_1'>������� PRO</a>, ������� ��������� ������ ������ �������� ���� ������������� � ���� ��������� ������ �����������.</p>

<p>����� ������������ �� ����� ����� ����� ������� ���������:</p>

<ul>
<li><a href='https://feedback.fl.ru/article/details/id/121?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_1'>���������� �������</a>: �� �������� ������ � ��������� ������� ������ � ��������� ������� �� �����������, ������� ������ �� ������� �� ���������� ������ ������.<br />&nbsp;<br /></li>
<li>����� ����������� <a href='{$host}/freelancers/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_1'>� ��������</a>: �������� ����������� ���������, ������������ ��������� �����������.<br />&nbsp;<br /></li>
<li>������� &laquo;<a href='{$host}/projects/?kind=4&utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_1'>��������</a>&raquo;: ������� ������� ������ ������� �� �������� � �� ������ �������� ������� �� �����������, ������� �������������� � ������������ ��������������.<br />&nbsp;<br /></li>
<li><a href='https://feedback.fl.ru/article/details/id/139?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_1'>�������� �� �������� �����������</a>: �� ������ ���������� ����� � �������� ���������� ������������� ������������ ����� ��������.</li>
</ul>

<br />&nbsp;<br />

<p>
<a href='{$host}/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_1'>������� �� ���� � ����� �����������</a>
</p>
";
        
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->message = $this->GetHtml($t_user->uname, $message, array('header'=>'default', 'footer'=>'feedback_default'), array("target_footer"=>true, "login"=>$login));

        return $this->send('text/html');
    }
    
    /**
     * ��� �������� ������: ����������� �� ������� ��� ��� ������ �������� ����� �������. ��������� ����� ����, � �� �������, ��������� ���-�� ���-��.
     * ��� �������� ��� ����� �����������. ������ ������ ����� �����������.     
     * @param type $user_id     �� ������������
     * @return type 
     */
    public function freelancerQuickStartGuide($user_id) {
        session_start();
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $host = $GLOBALS['host'];
        $t_user = new users();
        $t_user->GetUserByUID($user_id);
        $login = $t_user->login;
        $this->subject = "FL.ru: ��� ����� ������ �� �����?";
        $message = 
"<p>�� ���� �������������� ��� �� ���������� ����� ��������� ������ FL.ru. �������� � ���� ����� � ��������� &ndash; �� �������� ����� ������������� ��� ������ ������� &laquo;<a href='{$host}/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_freelancer'>���������� ������</a>&raquo;. ��� �� ���� ������������ � ���, �������� �� ��������, �� ������� �� ������� ������� � ��������� ������ ��� � ���-�� ������. �� ��������� ������������ ���� ������ � ����������.</p>
    
<p>���� ��������� �������� ������ ������ �� ����� �����, � ������� �� ������ �� ��� ����������.</p>

<ul>
<li>��������� � <a href='https://feedback.fl.ru/article/details/id/204?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_freelancer'>���������</a> ������� ����� ������ ����� � ������� ���� ������������� &ndash; ��� ���������� ����� ����� ����� ��� ����� ������ �����������.</li>
<li>�������� ���� �������� (�������, e-mail, Skype � �.�.): �����  ��������� ����� �������� ����������� � ����, ����������� ���������� <a href='{$host}/payed/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_freelancer'>������� PRO</a>.</li>
<li><a href='https://feedback.fl.ru/article/details/id/149?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_freelancer'>��������� �� �������</a>: ��������� �������� ���������� ��� ��� �����������, ������������ ����� �������� �� ������� �������� �����, &ndash; � ��� ���� 3 ���������� ������ � �����. � ��������� PRO ����� �������� �� �������������� ���������� ��������.</li>
<li>���������� � <a href='{$host}/konkurs/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_freelancer'>���������</a>: ���� �������� ����������� � ���, ��� �������� ��������� �������, � ���������� ��������� ���. ���������� ��������� �������� ��������������.</li>
</ul>

<p>����������� �������� ����� ������ &laquo;<a href='/". sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_freelancer'>���������� ������</a>&raquo; &ndash; ��� ������ �������� ����, ��� �� ������ �������� ������������ ������: ����� ������� �������������� �������� ����������� ��� ������� �� ����������� �����, � �������� �� ����� �������.</p>

<p>����� ���� ��� ������ ����� ���������, � �������� ������ ��, �� ������ ������� ������ ���������� ���������: �� ���������� ����� ����� �������� � ����������� ��������� �������� ���-�������, ������.������, WebMoney, � ����� �� ��������� ���� � �����.</p>

<p><a href='{$host}/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_freelancer'>������� �� ���� � ������ ��������</a>!</p>";
        
        $this->recipient = "{$t_user->uname} {$t_user->usurname} [{$t_user->login}] <".$t_user->email.">";
        $this->message = $this->GetHtml($t_user->uname, $message, array('header'=>'default', 'footer'=>'feedback_default'), array("target_footer"=>true, "login"=>$login));

        return $this->send('text/html');
    }
    
    /**
     * ��� �������� � ��������� ������������: �� ������ �������� ������ � ���������� � ������������� � ����-��, ������ ���.
     * ��� �������� ��� ����� �������������. ������������ � ��� �� ���� ����� ��������� �����.
     * ���������� �� hourly.php ��� � �����  
     */
    public function employerHelpInfo() {
        session_start();
        global $DB;
        $rows = employer::GetNewEmployer();
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $host = $GLOBALS['host'];
        $this->subject = "FL.ru: ��� �������� � ��������� ������������?";
        $message =
"<p>�� ���� �������������� ��� �� ���������� ����� ��������� ������ FL.ru. ������ ������ �� ����� &ndash; ��� ������ ������� ��� �����������. ����� ����������� ����� ����������� ���������:</p>
    
<ul>
<li>���������� ������� ��� ��������;</li>
<li>����� � �������� �����������;</li>
<li>����� ������� ����������� � ����� ����������.</li>
</ul>

<p>����� ���� ��� �� ������������ � �����������, ������� ����� ��������� ��� �����, �������� � ��� ������ ��������������.</p>

<p>�� ����������� ������ ��������� &laquo;<a href='{$host}/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_2'>���������� ������</a>&raquo; &ndash; ��� �� ������� ������������ ����� ����������� � ������ ������� � ���, ��� ��� ����� ����� �������� ����� � ���� � � ������������ � ����������� ��������.</p>

<p>��� �������������� ����� &laquo;���������� ������&raquo; ������� ����������� ������������� ������ ����� ����, ��� �� ������� ��������� ������. <a href='https://feedback.fl.ru/topic/397436-chto-takoe-bezopasnaya-sdelka/?utm_source=newsletter4&utm_medium=rassylka&utm_campaign=wellcome_employer_2'>������</a> ������������ ����� ������� ��� ��� ��������: ���������� ������, � ������� ����������� ��������� ������ ��� �� �� ������������ �������.</p>

<p>������� �� ���� � ���������� � ������ �����������!</p>";
        
        $this->message = $this->GetHtml(
            false, 
            $message, 
            array('header' => 'default', 'footer' => 'feedback_default'), 
            //array('login' => '%USER_LOGIN%')
            array('target_footer' => true)
        );
        if ( count($rows) < 20) {
            foreach ($rows as $user) {
                $this->message = $this->GetHtml(
                    false, 
                    $message, 
                    array('header' => 'default', 'footer' => 'feedback_default'), 
                    array('login' => $user['login'])
                );
                $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
                $this->send("text/html");
            }   
            return;
        }
        $this->recipient = '';
        $massId = $this->send('text/html');
        foreach ($rows as $user) {
            if (!$user['unsubscribe_key']) {
                $user['unsubscribe_key'] = users::GetUnsubscribeKey($user['login']);
            }
	        $this->recipient[] = array(
	                    'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">",
	                    'extra' => array(
	                        'USER_NAME'    => $user['uname'],
	                        'USER_SURNAME' => $user['usurname'],
	                        'USER_LOGIN'   => $user['login'],
	                        'MESSAGE'      => $message,
	                        'UNSUBSCRIBE_KEY' => $user['unsubscribe_key']
	                    )
	                );
        }
        $this->bind($massId);        
    }
    
    /**
     * ����������� ���������� �� �������������� �������� ������� ���� � ������� 2-� ���� �� ���� �� ������ ������ �� ������ ������(�)
     * 
     * @param array $projects    ������ ��������
     */
    public function sendAutoSetTopProject($projects) {
        session_start();
        
        $host = $GLOBALS['host'];
        $this->subject = "FL.ru: �� ������� ��� ������ � ����� ����� �������������� ��������";
        $is_binding = ( count($projects) > 20 );
        
        $pHost = str_replace("http://", "", $GLOBALS['host']);
        if ( defined('HTTP_PREFIX') ) {
            $pHttp = str_replace("://", "", HTTP_PREFIX); // ������� � ������ ���� ����������� ��������� HTTPS �� �������� (��� ����� � ��)
        } else {
            $pHttp = 'http';
        }
        $PLDB = new DB('plproxy');
        $adm  = new users();
        $adm_id = $adm->GetUid($e, "admin");
        
        if($is_binding) {
            $this->recipient = '';
            $massId = $this->send('text/html');
        }
        // ���������� �� �������������
        foreach($projects as $prj) {
            $users_sended[$prj['user_id']][] = $prj;
        }
        
        foreach($users_sended as $uid => $project) {
            $user = current($users_sended[$uid]);
            if(substr($user['subscr'], 8, 1) != '1') continue;
//            $uname    = ( $user['uname'] != '' && $user['usurname'] != '' ) ? "{$user['uname']} {$user['usurname']}" : $user['login'] ;
//            $_message = "������������, {$uname}!<br/><br/>";
            foreach($project as $value) {
                $value['name'] = htmlspecialchars($value['name'], ENT_QUOTES, 'CP1251', false);
                $message  = "<p>�� ��� ������ �<a href=\"{$GLOBALS['host']}".getFriendlyURL("project", $value['id']).$this->_addUrlParams('e')."\" target=\"_blank\">".$value['name']."</a>� �� ��������� �������� �� �����������, ������� �� ������� ��� � ����� ������ � ������ �� ���������� ������� ����� �������� ��� ����� ��������������. ��������, ��� ������� ��� ����� ���������� ������������.</p><br/><br/>";
                $message .= "<p>��� ����������� �������� � ������� �������������� ��������������� ������� � <a href=\"http://feedback.fl.ru/topic/397530-zakreplenie-proekta-naverhu-lentyi-proektov-opisanie-stoimost-instruktsiya/\" target=\"_blank\">����������� ������� ������� �����</a> � �������� �������� � ������ �� ��� ���� (��������� ���������� � � ������� ������ <a href=\"http://feedback.fl.ru/topic/397524-platnyie-proektyi-opisanie/\" target=\"_blank\">������������ ������� � ���������� �������� � ������ �� ���� ��������</a>).</p>";
                // � ������ ���������
//                $_message .= "�� ��� ������ �{$pHttp}:/{{$value['name']}}/{$pHost}" . getFriendlyURL("project", $value['id'])."� �� ��������� �������� �� �����������, ������� �� ������� ��� � ����� ������ � ������ �� ���������� ������� ����� �������� ��� ����� ��������������. ��������, ��� ������� ��� ����� ���������� ������������.<br/><br/>";
//                $_message .= "��� ����������� �������� � ������� �������������� ��������������� ������� � http:/{����������� ������� ������� �����}/feedback.FL.ru/article/details/id/157 � �������� �������� � ������ �� ��� ���� (��������� ���������� � � ������� ������ http:/{������������ ������� � ���������� �������� � ������ �� ���� ��������}/feedback.FL.ru/article/details/id/127<span>)</span>.<br/><br/>";
            
                $this->message = $this->GetHtml(
                    false, 
                    $message, 
                    array('header' => 'default', 'footer' => 'feedback_default'), 
                    $is_binding ? array('target_footer' => true) : array('login' => $user['login'])
                );


                if(!$is_binding) {
                    $this->recipient = $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">";
                    $this->send("text/html");
                } else {
                    if (!$user['unsubscribe_key']) {
                        $user['unsubscribe_key'] = users::GetUnsubscribeKey($user['login']);
                    }
                    $this->recipient[] = array(
                        'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">",
                        'extra' => array(
                            'USER_NAME'    => $user['uname'],
                            'USER_SURNAME' => $user['usurname'],
                            'USER_LOGIN'   => $user['login'],
                            'MESSAGE'      => $this->message,
                            'UNSUBSCRIBE_KEY' => $user['unsubscribe_key']
                        )
                    );
                }
            }
            // � ����� �� ��������
            //$_message .= "�������� ������ � FL.ru!";
            //$PLDB->val("SELECT messages_add(?i, ?i, ?, ?b, ?a, ?b)", $adm_id, $user['uid'], $_message, true, array(), true);
        }
        
        if($is_binding) {
            $this->bind($massId); 
        }
    }
    
    /**
    * ����������� �� �������� ����������� ��� ����� � ������
    * @param int $moderator_uid - ������������� ������ �����
    * @param array $userSubscribe - ������ ��������������� ��������� ���������� ������������
    * */
    public function sendBlogPostDeleted($moderator_uid, $userSubscribe) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
     // �������� ������������� �� ����  
        if($userSubscribe) {
            $moderator = new users();
            $moderator->GetUserByUID($moderator_uid);
            $a_login = $moderator->login;
            $a_uname = $moderator->uname;
            $a_usurname = $moderator->usurname;
        	foreach($userSubscribe as $comment) {
	            if( substr($comment['s_subscr'], 2, 1) == '1' 
	                && !$notSend[$comment['s_uid']]
	                && $comment['s_email'])
	            {
	                $this->subject = "� ����� �� ����� FL.ru ������ �����������";
	                $post_type = "����������� � <a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'>� �����</a>, �� ������� �� ���������";
	                if ( $comment['s_uid'] == $comment['uid']  ) {
                        $this->subject = "��� ����������� � ����� �� ����� FL.ru ������";
                        $post_type = "��� ����������� � <a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}&openlevel={$comment['id']}{$this->_addUrlParams('b', '&')}#o{$comment['id']}'> �����</a>";
                    }
	                $message_template = "subscribe_delete_comment";
	                if ( $comment['reply_to'] == '' ) {
	                    $this->subject = "�� ����� FL.ru ������ ����";
	                    $post_type = "����, �� ������� �� ���������";
	                    if ( $comment['s_uid'] == $comment['uid']  ) {
                            $this->subject = "��� ���� � ������ �� ����� FL.ru ������";
                            $post_type = "��� ���� � ������";
                        }
	                    $message_template = "subscribe_delete_post";
	                }
	                $link_title = "<a href='{$GLOBALS['host']}/blogs/view.php?tr={$comment['thread_id']}{$this->_addUrlParams('b', '&')}' target='_blank'>" . ( $comment['blog_title'] == ''? '��� ��������' : $comment['blog_title'] )  ."</a>";  
	                $this->message = $this->GetHtml($comment['s_uname'], "
	������������ <a href='{$GLOBALS['host']}/users/{$a_login}/{$this->_addUrlParams('b')}'>{$a_uname} {$a_usurname}</a> [<a href='{$GLOBALS['host']}/users/{$a_login}{$this->_addUrlParams('b')}'>{$a_login}</a>]
	������(-�) {$post_type} �� ����� FL.ru.
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
	������������ <a href='{$GLOBALS['host']}/users/{$a_login}/{$this->_addUrlParams('b')}'>{$a_uname} {$a_usurname}</a> [<a href='{$GLOBALS['host']}/users/{$a_login}{$this->_addUrlParams('b')}'>{$a_login}</a>]
    ������(-�) {$post_type} �� ����� FL.ru.
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
    }
    }

    /**
     * ��� ��� � ���� �������� ������������� ��� ��������
     * �������� ��������� ������������� � ���, ��� ������� PRO ���������� ����� ����
     *
     * @param integer $user_id �� ������������
     * @return null
     */
    public function sendAutoPROEnding($role = 'FRL', $users) {
        global $host;
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";

        $UTM = "utm_source=newsletter4&utm_medium=email&utm_campaign=expiring_PRO";
        $this->subject   = "FL.ru: ������������� �������� ������ �������� PRO";

        $message =
            '<p>������������, %USERNAME%!</p>
            <p>������ ������������� ���� �������� ���������� ���� ������ �PRO �������.<br/>
            ��� ��������� ����� �������� ������ ���������, ����������, �� <a href="' . $host . '/bill/">���� ������</a>, ����� ���������� � �������� ������� ����� ������� ��� ��������.</p>
            <p>� ��������� ����������� �� ���������� �������� � ������ ������ �� FL.ru �� ������ ������������ � ����� <a href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=9239https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=9239">���������� ���������</a>.</p>
            <p>�� ���� ����������� �������� ����������� � ���� <a href="https://feedback.fl.ru/">������ ���������</a>.<br/>
            <p>�������� ������ � FL.ru!</p>';

        foreach($users as $user) {
            if ($user['bill_subscribe'] === 'f') {
                continue;
            }
            $this->recipient = (string)"{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
            $this->message   = str_replace(array('%USERNAME%', '%LOGIN%'), array(($user['uname'] ? $user['uname'] : $user['login']), $user['login']) , $message);
            $this->send('text/html');
        }
    }


    /**
     * ������ �����
     * 
     * @global type $host
     * @global type $DB
     * @param type $reserves
     * @param type $is_reserved
     */
    public function sendReservedOrders($reserves, $is_reserved) {
        global $host, $DB;
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/billing.php";
        
        $reserved_ids  = array_keys($reserves);
        $not_reserved  = array_diff($reserved_ids, $is_reserved);
        
        if(!empty($is_reserved)) {
            $bill = new billing($reserves[$is_reserved[0]]['uid']);
            $this->recipient = (string)"{$bill->user['uname']} {$bill->user['usurname']} [{$bill->user['login']}] <{$bill->user['email']}>";
            $more  = (count($is_reserved) > 1);
            
            $this->subject   = "FL.ru: ���������� " . ( $more ? "�������" : "������" ) . " �������";
            
            $payed_sum = 0;
            foreach($is_reserved as $id) {
                $payed_sum += $reserves[$id]['ammount'];
            }
            
            $where = $DB->parse(" AND status = ? AND reserve_id IN (?l)", billing::STATUS_COMPLETE, $is_reserved);
            $orders = $bill->findOrders($where);
            
            $message = "�� ����� " . ($more ? "�������������� ������� �������" : "��������������� ������ ������")  . " �" . implode(", �", $is_reserved).  "  ������� ����� ".to_money($payed_sum, 2)." ���. � ������������ ��������� ������:<br/><br/>";
            $message .= "---<br/>";
            
            foreach($orders as $order) {
                $message .= $order['comment']."<br>";
            }
            
            $message .= "---<br/><br/>";
            //$message .= "��������� ����� ����� ������ ����� � " . $bill->acc['sum'] . " ���. ";
            
            if(!empty($not_reserved)) {
                $notpayed_sum = 0;
                foreach($not_reserved as $id) {
                    $notpayed_sum += $reserves[$id]['ammount'];
                }

                if(count($not_reserved) >= 1) {
                    $message .= ( count($not_reserved) > 1 ? "������ ������� �" : " ������ ������� �" ). implode(", �", $not_reserved) . " �� ����� " . to_money($notpayed_sum, 2) . " ���. ��-�������� ������� ������.<br/><br/>";
                }
            }
            
            $message .= "� ��������� ����������� �� ���������� �������� � ������ ������ �� FL.ru �� ������ ������������ � ����� <a href='https://feedback.fl.ru/' target='_blank'>���������� ���������</a>.<br/>";
            
            $this->message = $this->getHtml($bill->user['login'], $message, array('header'=>'default', 'footer'=>'feedback_default'), array('login' => $bill->user['login']));
            
        } else {
            $bill = new billing($reserves[$not_reserved[0]]['uid']);
            $this->recipient = (string)"{$bill->user['uname']} {$bill->user['usurname']} [{$bill->user['login']}] <{$bill->user['email']}>";
            $more = (count($not_reserved) > 1);
            
            $this->subject   = "FL.ru: ������������ ������� ��� ���������� " . ( $more ? "�������" : "������" ) . " �������";
            
            $message  = "����� ���� " . ($more ? "���� ������������ ������" : "��� ����������� ������") . " ������� �".implode(", �", $not_reserved).", ������ ����� ������ � " . ($more ? "���" : "���")  . " ������ ���, ������� �������� �� ����� ������ �����.<br/><br/>";
            
            $message .= "�������� " . ($more ? "������" : "������"). " �������, �������� �� ��� ��������� ������ �� ������ �� ���� <a href='{$host}/bill/history/'>������</a>.";
            $this->message = $this->getHtml($bill->user['login'], $message, array('header'=>'default', 'footer'=>'feedback_default'), array('login' => $bill->user['login']));
        }
        
        $this->send('text/html');
    }
    
    /**
     *
     *
     */
    public function sendCancelReserve ($reserves, $resDays) {
        global $host;
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";

        $this->subject   = "FL.ru: ������� ������ �������";

        $message =
            '<p>����� ���� ��� ����������� ���� �� ������� ����� �%RESERVE_ID%. ��� ��� � ������� ' . $resDays . ending($resDays, ' ���', '����', ' ����') . ' �� �� ��� �������, ���� ������������� �������.<br/>
            �� ������ �������� ������ � �������� ������ �� ���� <a href="' . $host . '/bill/">������</a>.<br/>
            � ��������� ����������� �� ���������� �������� � ������ ������ �� FL.ru �� ������ ������������ � ����� <a href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=9239">���������� ���������</a>.</p>';

        foreach($reserves as $reserve) {
            $this->recipient = (string)"{$reserve['uname']} {$reserve['usurname']} [{$reserve['login']}] <{$reserve['email']}>";
            $message_ = $this->getHtml('', $message, array('header'=>'default_new', 'footer'=>'feedback_default'), array('login' => $reserve['login']));
            $this->message = str_replace(array('%USER_NAME%', '%RESERVE_ID%'), array(($reserve['uname'] ? $reserve['uname'] : $reserve['login']), $reserve['reserve_id']) , $message_);
            //print ($this->message); exit;
            $this->send('text/html');
        }
    }

    /**
     * ����������� ������ ��� � �������� ����������� � ��� ��� ��� ����� ����������
     */
    public function remindTimeleftPRO($users, $days=3) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";

        if($days == 1) {
            $this->subject = "FL.ru: ������ �������� ���� �������� �������� PRO";
        } else {
            $this->subject = "FL.ru: �������� ���� �������� �������� PRO";
        }

        $time     = strtotime("+{$days} days");
        $date     = date('j', $time) . ' ' . monthtostr(date('n', $time), true) . ' ' . date('Y ����', $time);

        if($days > 1) {
            $message  = "����������, ��� ����� {$days} " . ending($days, "����", "���", "����") . ", {$date}, ������������� ���� �������� ������ �������� PRO.<br/>";
        } else {
            $message  = "����������, ��� ������, {$date},  ������������� ���� �������� ������ �������� PRO.<br/>";
        }
        $message .= "����������� ��� �������� ���������� ������.<br/><br/>";
        $message .= "���������� � �������� ������, � ����� ������ �� ��� ������������ ������� �� ������ ����� � ����� <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>���������� ���������</a>.<br/><br/>";

        foreach($users as $user) {
            if($user['bill_subscribe'] == 'f') continue;

            $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
            $this->message   = str_replace("%UNSUBSCRIBE_KEY%", users::GetUnsubscribeKey($user['login']), $message);
            $this->message   = $this->getHtml($user['login'], $this->message, array('header'=>'default', 'footer'=>'simple'), array('login' => $user['login']));
            $this->send('text/html');
        }
    }

    /**
     * ������������� �� 3 ��� �� ��������� ������
     *
     * @param $users            ������ �������������
     * @param string $role
     */
    public function remindAutoprolongPRO($users, $role = 'freelancer', $days=3) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/payed.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/wallet.php";

        $this->subject = "FL.ru: �������� ���� �������� �������� PRO";
        $cost     = $role == 'freelancer' ? payed::PRICE_FRL_PRO : payed::PRICE_EMP_PRO;
        $time     = strtotime("+{$days} days");
        $date     = date('j', $time) . ' ' . monthtostr(date('n', $time), true) . ' ' . date('Y ����', $time);

        foreach($users as $user) {
            if($user['bill_subscribe'] == 'f') continue;

            $time        = strtotime("+". ($days-1). " days");
            $next        = date('j', $time) . ' ' . monthtostr(date('n', $time), true) . ' ' . date('Y ����', $time);
            $wallet      = WalletTypes::initWalletByType($user['uid']);
            $type        = WalletTypes::checkWallet($wallet) ? $wallet->data['type'] : -1;
            $walletName  = WalletTypes::getNameWallet($type, 3, $user['acc_id']);
            $unsunscribe = users::GetUnsubscribeKey($user['login']);

            $message   = "����������, ��� ����� {$days} " . ending($days, "����", "���", "����") . ", {$date}, ������������� ���� �������� ������ �������� PRO.<br/><br/>";

            if($type == -1) {
                $time     = strtotime("+". ($days-1). " days");
                $date     = date('j', $time) . ' ' . monthtostr(date('n', $time), true) . ' ' . date('Y ����', $time);
                $message .= "��� ��� ����� �� �������� ������������� ������ (��� �������� ������� ������), �� �� ����� �� ��������� ����� �� ��������, {$date}, � {$walletName} ����� ������� ��������������� ����� ({$val['sum_cost']} " . ending($val['sum_cost'], '�����', '�����', '������') . ").<br/><br/>";
                $message .= "�������� ��������, ��� ��� �������������� �������� ������� � ��������� ����� � ���������� ���������� ������� � ������������ ���� �� ��������� ��� �������� ������.<br/><br/>";
            } else {
                $message .= "��� ��� ����� �� �������� ������������� ������, �� �� ����� �� ��������� ����� �� ��������, {$next}, � {$walletName} ����� ������� ��������������� ����� ({$cost} " . ending($cost, '�����', '�����', '������') . "). ����� �������� ������� �������� �������� PRO ����� ��������.<br/><br/>";
                $message .= "����������� ��� ��������� ������� ����������� �����, ������� ����� ������� � {$walletName}.<br/><br/>";
            }

            $message .= "���������� �� �������������, � ����� ������ �� ��� ������������ ������� �� ������ ����� � ����� <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>���������� ���������</a>.<br/><br/>";


            $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
            $this->message   = $this->getHtml($user['login'], $message, array('header'=>'default', 'footer'=>'simple'), array('login' => $user['login']));
            $this->send('text/html');
        }
    }

    /**
     * ������ �������� �������
     *
     * @param string $service
     * @param $cost
     * @param $user
     */
    public function successAutoprolong($info, $service = 'pro') {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/wallet.php";

        $user   = $info['user'];
        if($user['bill_subscribe'] == 'f') return;
        $date   = date('j') . ' ' . monthtostr(date('n'), true) . ' ' . date('Y ����');
        $cost   = $info['sum_cost'];

        $wallet = WalletTypes::initWalletByType($user['uid']);
        $type   = WalletTypes::checkWallet($wallet) ? $wallet->data['type'] : -1;
        $walletName  = WalletTypes::getNameWallet($type, 3, $user['acc_id']);

        if($service == 'pro') {
            $time     = strtotime("+1 month");
            $next     = date('j', $time) . ' ' . monthtostr(date('n', $time), true) . ' ' . date('Y ����', $time);

            $link     = is_emp($user['role']) ? "{$GLOBALS['host']}/payed-emp/" : "{$GLOBALS['host']}/payed/";
            $message  = "�������, {$date}, ��� ������������� ������� ��� ������� PRO. � {$walletName} ���� ������� {$cost} " . ending($cost, '�����.', '�����.', '������.')."<br/><br/>";
            $message .= "��������� ������������� �������� PRO ��������� ����� �����, {$next}. �� ������ ��������� ��� ��������� <a href='{$link}{$this->_addUrlParams('b', '?')}'>������� �������������</a> �������� PRO �� FL.ru.<br/><br/>";
        }
        $message .= "���������� �� �������������, � ����� ������ �� ��� ������������ ������� �� ������ ����� � ����� <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>���������� ���������</a>.";

        $this->subject   = "FL.ru: ���� �������� " . ( $service == 'pro' || sizeof($info['prof'])==1 ? '������' : '�����' ) . " �������";
        $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
        $this->message   = $this->getHtml($user['login'], $message, array('header'=>'default', 'footer'=>'default'), array('login' => $user['login']));
        $this->send('text/html');
    }

    // �� ������� �������� �� ����
    public function attemptAutoprolong($info, $service = 'pro') {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/wallet.php";

        $user   = $info['user'];
        if($user['bill_subscribe'] == 'f') return;
        $date   = date('j') . ' ' . monthtostr(date('n'), true) . ' ' . date('Y ����');
        $cost   = $info['sum_cost'];
        $wallet = WalletTypes::initWalletByType($user['uid']);
        $type   = WalletTypes::checkWallet($wallet) ? $wallet->data['type'] : -1;
        $walletName  = WalletTypes::getNameWallet($type, 3, $user['acc_id']);

        if($service == 'pro') {
            $this->subject = "FL.ru: ������ ��� ������������� �������� PRO";

            $message  = "�������, {$date}, ������ ���� ���������� �������������� ��������� ����� �������� ������ �������� PRO.<br/><br/>";
        }
        $message .= "����� � {$walletName} ������ ���� ������� {$cost} " . ending($cost, '�����', '�����', '������') . ", ������ � �������� �������� ��������� ������.<br/><br/>";
        $message .= "��������� �������� ����� ������������ ������ � ��� �� �����. ����������� ��� ��������� ������� ����������� �����, ������� ����� ������� � {$walletName}.<br/><br/>";
        $message .= "���������� �� �������������, � ����� ������ �� ��� ������������ ������� �� ������ ����� � ����� <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>���������� ���������</a>.";

        $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
        $this->message   = $this->getHtml($user['login'], $message, array('header'=>'default', 'footer'=>'default'), array('login' => $user['login']));
        $this->send('text/html');
    }

    // �� ������� �������� �� ��� �� ��������� (������ ����)
    public function failAutoprolong($info, $service = 'pro') {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/wallet.php";

        $user   = $info['user'];
        if($user['bill_subscribe'] == 'f') return;
        $date   = date('j') . ' ' . monthtostr(date('n'), true) . ' ' . date('Y ����');
        $cost   = $info['sum_cost'];
        $wallet = WalletTypes::initWalletByType($user['uid']);
        $type   = WalletTypes::checkWallet($wallet) ? $wallet->data['type'] : -1;
        $walletName  = WalletTypes::getNameWallet($type, 3, $user['acc_id']);

        if($service == 'pro') {
            $this->subject = "FL.ru: ������������� �������� PRO ���������";

            $message  = "�������, {$date}, ������ ���� ���������� ��������� �������� ������� ��� ��������������� ��������� ����� �������� ������ �������� PRO.<br/>";
            $message .= "����� � {$walletName} ������ ���� ������� {$cost} ".ending($cost, '�����', '�����', '������').", ������ � �������� �������� ����� ��������� ������.<br/><br/>";
            $message .= "���� �������� �������� PRO ��������, � ��� ������������� �������� ��������� � ������������ ��� ��������� ������������ ������.<br/><br/>";
            $message .= "���������� � ��������� ������������ ����� � �������������, � ����� ������ �� ��� ������������ ������� �� ������ ����� � ����� <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>���������� ���������</a>.";
        }

        $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
        $this->message   = $this->getHtml($user['login'], $message, array('header'=>'default', 'footer'=>'default'), array('login' => $user['login']));
        $this->send('text/html');
    }
    /**
    * @desc ��������� ������������ � ���, ��� �� ��� email ���� ��������� ������� ��������� ����������� (#0024792) 
    * @param string $email
    **/
    public function reRegisterToYourMail($email) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        $user = new users();
        $user->GetUser($email, true, $email);
        if ($user->login) {
	        $this->subject = "����������� ����� ������� ������ �� FL.ru";
	        $message = "�� ����� FL.ru ������������ ������ ������������ ����� ������� ������ � ��� ������ ��� ����� ����������� �����.<br>";
	        $message .= "<p>���� ��� ���� �� �� � �������� ������ ����� ���������� ��������� ���, ����������, ���������� � ������ ��������� FL.ru �� ������ http://feedback.fl.ru/<br>";
	        $message .= "<p> ���� �� ������ ������������ ������ ��, �� �������� ��������, ��� �� ��������� ����� ���� ��� ��������������� ������� � �����������:";
	        $message .= "<p> ����� {$user->login}";
	        $message .= "<p>  ������ ******";
	        $message .= "<p> ����� �� ������ �������������� � �������� � ���������� ������ �� �����.<br>";
	        $this->message   = $this->getHtml($user->uname, $message, array('header'=>'default', 'footer'=>'default'), array('login' => $user->login));
	        $this->recipient = "{$user->uname} {$user->usurname} [{$user->login}] <{$user->email}>";
	        $this->send('text/html');
        }
    }

    /**
     * ����������� ���������� � ���, ��� ���� ��������������� ������ ����� �����   � ���� ����� ����� �������� ������
     * @param int $hours - ���������� �����, �� ������� ������� ����� ������ 24 ��� 72 
     */
    public function sendSbrReserveNotice($hours = 24) {
        session_start();        
        $host = $GLOBALS['host'];
        $this->subject = "�� �� ��������������� ������ ������";
        $message = "<p>���������� ���, ��� ���������� ��������������� ������ �� ���������� ������ ��-%SBR_ID%-�/�, ���� �� ���������� �������������� � ���. ����� ������ �� ��������� �������������, �������� ������ ���� ��������� � ������� 5 ������� ����.<p>
        <p>���������� � �������������� � ���������� ������, � ����� ������ �� ��� ������������ ������� �� ������ ����� � ����� <a href='https://feedback.fl.ru/' target='_blank'>���������� ���������</a>.</p>
        ";
        if ($hours == 72) {
        	$this->subject = "�������� 2 ������� ��� �� ��������������� ���������� ������";
        	$message = "<p>����������, ��� �� ��� �� ��������������� ������ ���������� ������ ��-%SBR_ID%-�/�. ���� ����� �� ���������� � ������� 2 ������� ����, ������ ����� ������������� ��������.</p>
            <p>����������, �������������� ������, ���� ��� ����� ��������� ������, � �� ���������� �������� � ��������� ������������. <p>
            <p>���������� � �������������� � ���������� ������, � ����� ������ �� ��� ������������ ������� �� ������ ����� � ����� <a href='https://feedback.fl.ru/' target='_blank'>���������� ���������</a>.</p>
            ";
        }
        $time_limit = $hours + 24;
        $query = "SELECT emp_id, e.email, e.login, e.uname, e.usurname, usk.key AS ukey, e.uid, sbr.id AS sbr_id
                  FROM sbr
                  LEFT JOIN employer AS e ON e.uid = sbr.emp_id
                  LEFT JOIN users_subscribe_keys AS usk ON usk.uid = e.uid
                  WHERE reserved_time IS NULL 
                      AND NOW() - posted > '{$hours} hours'::interval
                      AND NOW() - posted < '{$time_limit} hours'::interval;";
        if  ($_GET["debug"] == 1 && $_GET["bs"] == 1) {
            $query = "SELECT emp_id, e.email, e.login, e.uname, e.usurname, usk.key AS ukey, e.uid, sbr.id AS sbr_id
                  FROM sbr
                  LEFT JOIN employer AS e ON e.uid = sbr.emp_id
                  LEFT JOIN users_subscribe_keys AS usk ON usk.uid = e.uid
                  WHERE e.email = 'lamzin.a.n@rambler.ru' LIMIT 1";
        }
        $DB = new DB("master");
        $users = $DB->rows($query);
        $this->message = $this->GetHtml(
                false, 
                $message, 
                array('header' => 'default_new', 'footer' => 'feedback_default'), 
                array('target_footer' => true)
         );
        $this->recipient = '';
        $massId = $this->send('text/html');
        require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/users.php";
        $i = 0;
        $cnt = 0;
        $this->recipient= array();
        foreach($users as $row) {
            if($row['email'] == '') continue;
		    if ( strlen($row['ukey']) == 0 ) {
		        $row['ukey'] = users::writeUnsubscribeKey($row["uid"], true);
		    }
		    $this->recipient[] = array(
		        'email' => $row['email'],
		        'extra' => array( 
		                         'USER_LOGIN' => $row['login'],
		                         'UNSUBSCRIBE_KEY' => $row['ukey'],
		                         'USER_NAME' => $row['uname'],
		                         'SBR_ID' => $row["sbr_id"])
		    );
		    if (++$i >= 30000) {
		        $this->bind($massId);
		        $this->recipient = array();
		        $i = 0;
		    }
		    $cnt++;
        }
        $this->bind($massId);
    }
    /**
     * ����������� ���������� � ���, ��� ���� ��������������� ������ ����� �����   � ���� ����� ����� �������� ������
     */
    public function activateAccountNotice() {
        $DB = new DB("master");
        $host = $GLOBALS['host'];
        $this->subject = "��������� ���� ��� ��������� ��������";
        $message = "<p>����������, ����������� ��� ������� %NAME_LOGIN% � ������� �����.<p>
        <p>��� ��������� ���������� ������� �� ��������� ������ ��� ����������� �� � �������� ������ ��������:</p>
        <p><a href='%LINK%' target='_blank'>%LINK%</a></p>
        <p>��� ������������� ������� �  ���������� �������� ����������� ��� <a href='https://feedback.fl.ru/' target='_blank'>������������ � �����������</a> ��� <a href='https://feedback.fl.ru/' target='_blank'>�������� ���</a>. �� ����������� ��� �������.</p>
        <p>���������� � �������������� � ���������� ������, � ����� ������ �� ��� ������������ ������� �� ������ ����� � ����� <a href='https://feedback.fl.ru/' target='_blank'>���������� ���������</a>.</p>
        ";
        $hours = 48;
        $time_limit = $hours + 24;
        $query = "SELECT u.email, u.login, u.uname, u.usurname, usk.key AS ukey, u.uid, ac.code
                  FROM users AS u
                  LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
                  LEFT JOIN activate_code AS ac ON ac.user_id = u.uid
                  WHERE active = false  
                      AND NOW() - last_time > '{$hours} hours'::interval
                      AND NOW() - last_time < '{$time_limit} hours'::interval;";
        if  ($_GET["debug"] == 1 && $_GET["activate"] == 1) {
            $query = $DB->parse("SELECT u.email, u.login, u.uname, u.usurname, usk.key AS ukey, u.uid, ac.code
                  FROM users AS u
                  LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
                  LEFT JOIN activate_code AS ac ON ac.user_id = u.uid
                  WHERE u.login = ? LIMIT 1", $_GET['login']);
        }
        
        $users = $DB->rows($query);
        $this->message = $this->GetHtml(
                false, 
                $message, 
                array('header' => 'noname', 'footer' => 'feedback_default'), 
                array('target_footer' => true)
         );
        $this->recipient = '';
        $massId = $this->send('text/html');
        require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/users.php";
        $i = 0;
        $cnt = 0;
        $this->recipient= array();
        foreach($users as $row) {
            if($row['email'] == '') continue;
            if ( strlen($row['ukey']) == 0 ) {
                $row['ukey'] = users::writeUnsubscribeKey($row["uid"], true);
            }
            $link = $host . "/registration/activate.php?code={$row['code']}";
            $name = trim($row["uname"] . " " . $row["usurname"]);
            $name_login = ($name ? $name . ", " : '') . $row["login"]; 
            $this->recipient[] = array(
                'email' => $row['email'],
                'extra' => array( 
                    'USER_LOGIN' => $row['login'],
                    'UNSUBSCRIBE_KEY' => $row['ukey'],
                    'NAME_LOGIN' => $name_login,
                    'LINK' => $link
                )
            );
            if (++$i >= 30000) {
                $this->bind($massId);
                $this->recipient = array();
                $i = 0;
            }
            $cnt++;
        }
        $this->bind($massId);
    }
    
    
    
    
    
//------------------------------------------------------------------------------
    
    
    /**
     * ##0026613
     * �������� �� ���������� � ������������ � ����������� �������� �����
     * �� �������� 2014 ����
     * https://beta.free-lance.ru/mantis/view.php?id=26613
     */
    public function sendEmpPrjFeedback()
    {
        $type = 2;
        $date_interval = $this->__get_next_spam_date($type);
        if(!$date_interval) return '���������� ����������� ���� ��������';
        
        $datefrom = $date_interval['from_date']; 
        $dateto = $date_interval['to_date'];
        $host = $GLOBALS['host'];
        
        $this->subject = "�������� ������ �� �������� {$date_interval['year']} ����!";
        
        $this->message = Template::render(
                $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/spam/emp_projects_feedback.tpl.php', 
                array(
                    'host' => $host,
                    'login' => '%USER_LOGIN%'
                )
        );        
        $this->recipient = '';
        $massId = $this->send('text/html');        
        
        $page  = 0;
        $count = 0;
        
        while ( $users = projects::getEmpPrjFeedback($datefrom, $dateto, ++$page, 200) ) 
        {
            $ids = array();
            foreach ( $users as $user ) 
            {

               $this->recipient[] = array(
                    'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">",
                    'extra' => array(
                        'USER_NAME'         => $user['uname'],
                        'USER_SURNAME'      => $user['usurname'],
                        'USER_LOGIN'        => $user['login']
                    )
                );

               $ids[] = array('user_id' => $user['uid'], 'type' => $type);
               $count++;
            }
            
            $this->__save_sended_ids($ids);
            $page = 0;
        
            $this->bind($massId, true);
        }
        
        $this->__save_spam_date(array(
            'from_date' => $date_interval['from_date'],
            'to_date' => $date_interval['to_date'],
            'type' => $type,
            'sended' => $count
        ));  
        
        return $count;        
    }



//------------------------------------------------------------------------------
    
    
    /**
     * ##0026617
     * �������� �� ������������ ��������� � �������� 2014 ����
     * https://beta.free-lance.ru/mantis/view.php?id=26617
     * 
     */
    public function sendFrlProjectsExec()
    {
        $type = 1;
        $date_interval = $this->__get_next_spam_date($type);
        if(!$date_interval) return '���������� ����������� ���� ��������';
        
        
        $datefrom = $date_interval['from_date']; 
        $dateto = $date_interval['to_date'];
        $host = $GLOBALS['host'];
        
        $this->subject = "�������� ������ �� �������� {$date_interval['year']} ����!";

        $this->message = Template::render(
                $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/spam/frl_projects_exec.tpl.php', 
                array(
                    'project_links' => '%PROJECT_LINKS%',
                    'host' => $host
                )
        );        
        $this->recipient = '';
        $massId = $this->send('text/html');        
        
        $page  = 0;
        $count = 0;
        
        while ( $users = projects::getFrlExec($datefrom, $dateto, ++$page, 200) ) 
        {
            $ids = array();
            foreach ( $users as $user ) 
            {
               //����� � ����� ������� ��� ���� ����� �� ������� � �������� �� ��������� ����
               $ids[] = array('user_id' => $user['uid'], 'type' => $type);
               
               $projects_list = DB::array_to_php($user['projects_list']); 
               if(empty($projects_list)) continue;
               //�������������� ��� 10� ��������
               $projects_list = array_slice($projects_list, 0, 10);
               
               $links = '';
               foreach($projects_list as $el)
               {
                   $parts = explode('||', $el);
                   if(!isset($parts[0],$parts[1]) || intval($parts[0]) <= 0) continue;
                   $links .= '<a href="'. $host . getFriendlyURL("project", array('id' => intval($parts[0]),'name' => $parts[1])) . '">'.$parts[1].'</a><br/>';
               }
               
               if(empty($links)) continue; 
              
               $this->recipient[] = array(
                    'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">",
                    'extra' => array(
                        'USER_NAME'         => $user['uname'],
                        'USER_SURNAME'      => $user['usurname'],
                        'USER_LOGIN'        => $user['login'],
                        'PROJECT_LINKS'     => $links,
                    )
                );

                $count++;
            }
            
            $this->__save_sended_ids($ids);
            $page = 0;
            
            $this->bind($massId, true);
        }
        
        $this->__save_spam_date(array(
            'from_date' => $date_interval['from_date'],
            'to_date' => $date_interval['to_date'],
            'type' => $type,
            'sended' => $count
        ));        
        
        return $count;
    }
    
    
    
    
//------------------------------------------------------------------------------
    
    
    /**
     * ##0026615
     * �������� �� ����������� ���������� ���� ���� ��� � �������� �� 2014 ��� 
     * ��� � �� ���� �������������.
     * 
     * https://beta.free-lance.ru/mantis/view.php?id=26615
     * 
     */
    public function sendFrlOffer()
    {
        $type = 0;
        $date_interval = $this->__get_next_spam_date($type);
        if(!$date_interval) return '���������� ����������� ���� ��������';
        
        
        $datefrom = $date_interval['from_date']; 
        $dateto = $date_interval['to_date'];
        $host = $GLOBALS['host'];
        
        $this->subject = "�������� ������ �� �������� {$date_interval['year']} ����!";
        
        $this->message = Template::render(
                $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/spam/frl_project_offer.tpl.php', 
                array(
                    //'project_links' => '%PROJECT_LINKS%',
                    'host' => $host
                )
        );        
        $this->recipient = '';
        $massId = $this->send('text/html');        
        
        $page  = 0;
        $count = 0;
        
        while ( $users = projects::getFrlOffer($datefrom, $dateto, ++$page, 200) ) 
        {
            $ids = array();
            foreach ( $users as $user ) 
            {
               /* 
               $projects_list = DB::array_to_php($user['projects_list']); 
               if(empty($projects_list)) continue;
               $links = '';
               foreach($projects_list as $el)
               {
                   $parts = explode('||', $el);
                   if(!isset($parts[0],$parts[1]) || intval($parts[0]) <= 0) continue;
                   $links .= '<a href="'. $host . getFriendlyURL("project", array('id' => intval($parts[0]),'name' => $parts[1])) . '">'.$parts[1].'</a><br/>';
               }
               
               if(empty($links)) continue; 
              */
                
               $this->recipient[] = array(
                    'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] <".$user['email'].">",
                    'extra' => array(
                        'USER_NAME'         => $user['uname'],
                        'USER_SURNAME'      => $user['usurname'],
                        'USER_LOGIN'        => $user['login']//,
                        //'PROJECT_LINKS'     => $links,
                    )
                );
                
                $ids[] = array('user_id' => $user['uid'], 'type' => $type);
                $count++;
            }
            
            $this->__save_sended_ids($ids);
            //���������� �������� ��� ��� ������ ����� ��������� 
            //�� ��� ��� ���� ��� id ������ �� ������� � ������ 
            //����������� � �������������� ����� ������ ������.
            $page = 0;
            
            $this->bind($massId, true);
        }
        
        $this->__save_spam_date(array(
            'from_date' => $date_interval['from_date'],
            'to_date' => $date_interval['to_date'],
            'type' => $type,
            'sended' => $count
        ));
        
        return $count;
    }
    
    
    
    /**
     * ��������� ����� ������ ��� ���� ���������
     * 
     * @global type $DB
     * @param type $ids
     * @return type
     */
    private function __save_sended_ids($ids)
    {
        global $DB;
        return $DB->insert('projects_spam_is_send',$ids);
    }

    /**
     * ��������� ��������� �������� ��������
     * 
     * @global type $DB
     * @param type $data
     * @return type
     */
    private function __save_spam_date($data)
    {
        global $DB;
        return $DB->insert('projects_spam_interval',$data,'id');
    }

    
    /**
     * �������� ��������� �������� ������� ��� �������� ���������� ����
     * 
     * @global type $DB
     * @param int $type
     * @return boolean | array
     */
    private function __get_next_spam_date($type = 0)
    {
        global $DB;
        
        //�������� ��������
        $spam_interval = 3;//�� ������
        //����������� ���� ��������
        $min_date = strtotime('2009-01-01');

        $last = $DB->val("
            SELECT
                from_date
            FROM projects_spam_interval
            WHERE type = ?i
            ORDER BY from_date, id DESC
            LIMIT 1
        ",$type);
        
        if(!$last) return FALSE;

        $from = strtotime("- {$spam_interval} month", strtotime($last));
        
        if($from < $min_date) return FALSE;
        
        return array(
            'year' => date('Y',$from),
            'from_date' => date('Y-m-d H:i:s',$from),
            'to_date' => $last
        );
    }
    
    
   /**
    * ����������� ��������� ����� �������� ������ 
    * ������������ �� �������� ��������
    * 
    * @param type $project
    * @return type
    */
   public function sendMovedToVacancySuccessPayed($project) 
   {
        $this->subject = "���� �������� ������� ��������";
        $this->recipient = "{$project['email']} <{$project['email']}>";
        $this->message = Template::render(ABS_PATH . '/templates/mail/projects/makevacancy_payed.tpl.php',array(
            'title' => $project['name']
        ));
        return $this->send('text/html');
    }
    
    
    /**
     * ����������� ���������� �� 1 ���� �� ��������� 
     * ���������� � ����������� � ��������
     */
    public function remindFreelancerbindsProlong() {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer_binds.php";

        $binds = freelancer_binds::getExpiring();
        if(!$binds) return false;
        
        foreach($binds as $val) {
            if($val['bill_subscribe'] == 'f') continue;
            
            $catalog_name = '';
            $catalog_url = "{$GLOBALS['host']}/freelancers/";
            if ($val['prof_id'] == 0) {
                $catalog_name = '����� �������';
            } elseif ($val['is_spec'] == 'f') {
                $group = professions::GetGroup($val['prof_id'], $error);
                $catalog_url .= $group['link'];
                $catalog_name = "������� <a href='{$catalog_url}'>{$group['name']}</a>";
            } else {
                $prof_name = professions::GetProfName($val['prof_id']);
                $catalog_url .= professions::GetProfLink($val['prof_id']);
                $catalog_name = "���������� <a href='{$catalog_url}'>{$prof_name}</a>";
            }

            $this->recipient = "{$val['uname']} {$val['usurname']} [{$val['login']}] <{$val['email']}>";
            $this->message = Template::render(
                    $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/freelancer_binds/remind_prolong.tpl.php', 
                    array(
                        'smail' => &$this,
                        'time' => dateFormat('H:i', $val['to_date']),
                        'catalog_url' => $catalog_url,
                        'catalog_name' => $catalog_name
                    )
            );
            $ok = $this->send('text/html');
            if ($ok) {
                freelancer_binds::markSent('prolong', $val['uid'], $val['prof_id'], $val['is_spec']);
            }
        }
        return 0;
    }
    
    /**
     * ����������� ���������� �� 1 ���� �� ��������� 
     * ���������� � freelancer_binds
     */
    public function remindFreelancerbindsUp() {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer_binds.php";

        $binds = freelancer_binds::getDowned();
        if(!$binds) return false;
        
        foreach($binds as $val) {
            if($val['bill_subscribe'] == 'f') continue;
            
            $catalog_name = '';
            $catalog_url = "{$GLOBALS['host']}/freelancers/";
            if ($val['prof_id'] == 0) {
                $catalog_name = '����� �������';
            } elseif ($val['is_spec'] == 'f') {
                $group = professions::GetGroup($val['prof_id'], $error);
                $catalog_url .= $group['link'];
                $catalog_name = "������� <a href='{$catalog_url}'>{$group['name']}</a>";
            } else {
                $prof_name = professions::GetProfName($val['prof_id']);
                $catalog_url .= professions::GetProfLink($val['prof_id']);
                $catalog_name = "���������� <a href='{$catalog_url}'>{$prof_name}</a>";
            }

            $this->recipient = "{$val['uname']} {$val['usurname']} [{$val['login']}] <{$val['email']}>";
            $this->message = Template::render(
                    $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/freelancer_binds/remind_up.tpl.php', 
                    array(
                        'smail' => &$this,
                        'catalog_url' => $catalog_url,
                        'catalog_name' => $catalog_name
                    )
            );
            $ok = $this->send('text/html');
            if ($ok) {
                freelancer_binds::markSent('up', $val['uid'], $val['prof_id'], $val['is_spec']);
            }
        }
        return 0;
    }
}

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/smtp2.php';

class smail2 extends SMTP2 
{
    /**
     * �������� ��������� ������������� � ���, ��� ������� PRO ���������� ����� ����
     * 
     * @param integer $user_id �� ������������
     * @return null
     *
     * @deprecated #0024638
     */
    public function sendPROEnding($role = 'FRL', $users) {
        return;
        global $host;
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        
        $UTM = "utm_source=newsletter4&utm_medium=email&utm_campaign=expiring_PRO";
        $this->subject   = "FL.ru: ������������� �������� ������ �������� PRO";
        
        if($role == 'EMP') {
            $cid1  = $this->cid();
            $cid2  = $this->cid();
            $cid3  = $this->cid();

            $this->attach(ABS_PATH . '/images/letter/19.png', $cid1);
            $this->attach(ABS_PATH . '/images/letter/14.png', $cid2);
            $this->attach(ABS_PATH . '/images/letter/18.png', $cid3);

            ob_start();
            include ($_SERVER['DOCUMENT_ROOT']."/classes/letters_html/tpl.outpro-emp.php");
            $body_html = ob_get_clean();
        } else {
            $cid1  = $this->cid();
            $cid2  = $this->cid();
            $cid3  = $this->cid();

            $this->attach(ABS_PATH . '/images/letter/19.png', $cid1);
            $this->attach(ABS_PATH . '/images/letter/13.png', $cid2);
            $this->attach(ABS_PATH . '/images/letter/18.png', $cid3);

            ob_start();
            include ($_SERVER['DOCUMENT_ROOT']."/classes/letters_html/tpl.outpro-frl.php");
            $body_html = ob_get_clean();
        }
        foreach($users as $user) {
            $this->message   = str_replace("%UNSUBSCRIBE_KEY%", users::GetUnsubscribeKey($user['login']) , $body_html);
            $this->recipient = "{$user['uname']} {$user['usurname']} [{$user['login']}] <{$user['email']}>";
            $this->send('plain/text');
        }
    }


    /**
     * ������ � ��� ��� �������� �������
     * @param $user ����� ��������
     */
    public function masssendingAccepted ($params) {
        global $host;
        $UTM = '';//"?utm_source=newsletter4&utm_medium=email&utm_campaign=expiring_PRO";
        $this->subject   = "FL.ru: ������ �� �������� ��������";

        $cid = $this->cid();
        $this->attach(ABS_PATH . '/images/letter/pay.png', $cid);

        ob_start();
        include($_SERVER['DOCUMENT_ROOT'] . "/masssending/tpl.accept_letter.php");
        $body_html = ob_get_clean();

        $this->message   = str_replace("%UNSUBSCRIBE_KEY%", users::GetUnsubscribeKey($params['login']) , $body_html);
        $this->recipient = "{$params['uname']} {$params['usurname']} [{$params['login']}] <{$params['email']}>";
        $this->send('plain/text');
    }
    /**
     * �������� � ����� �������� �����, ��������������� �� ����� ����� ����� � ����� ����. ���������� ��� � ���� �� hourly.php
     * @param array &$uids - ������ ��������������� �������������, ����������� �� ����� ������� �� ������� ���������� ��� �����������
     *                        ���������� �������� � smail::NewProj
     * @return integer   ���������� ���������� ��������
     */
    public function NewProjForMissingMoreThan24h(&$uids) {
        $projects = projects::GetNewProjectsWithBudjet($error);
        //���������� �� ���������
        foreach ($projects as $key=>$prj) {
            $prj["sort_cost"] = $prj["cost"];
            if ($prj["currency"] == 0) {
                $prj["sort_cost"] *= 30; //� ������ ������ ������ ���� �� �����, ����� �������������
            }
            if ($prj["currency"] == 1) {
                $prj["sort_cost"] *= 40; //� ������ ������ ������ ���� �� �����, ����� �������������
            }
            $projects[$key] = $prj;
        }
        
        $all_mirrored_specs = professions::GetAllMirroredProfsId();
        $professions = professions::GetProfessionsAndGroup();
        $professionsTree = array();
        foreach ($professions as $k=>$i) {                         
            if ($professionsTree[$i["gid"]] === null) {
                $professionsTree[$i["gid"]] = array( "gid" => $i["gname"]);
                if ($i["id"] !== null) $professionsTree[$i["gid"]] [$i["id"]] = $i["name"];
                    else $professionsTree[$i["gid"]] = $i["gname"];
            }else if ( is_array($professionsTree[$i["gid"]]) ) {
                $professionsTree[$i["gid"]] [$i["id"]] = $i["name"];
            }
        }
        $page  = 0;
        $count = 0; // total
        $countBs     = 0; // ��
        $countCar    = 0; // ��������
        $countPro    = 0; // ���
        $countPayed  = 0; // ������� �����
        $countVerify = 0; // �����������
        $this->subject = "����� ������� � �������� �� FL.ru";
        $pHost = $GLOBALS['host'];
        
        ob_start();
        include($_SERVER['DOCUMENT_ROOT'] . "/masssending/tpl.missing_more_than_24h.php");
        $this->message = ob_get_clean();
        $this->recipient = '';
        $massId = $this->masssend();
        $dbStat = new DB("master");
        while ( $users = freelancer::GetMissingMoreThan24h($error, ++$page, 100) ) {
            $this->recipient = array();
            foreach ( $users as $user ) {
                if (!$user['unsubscribe_key']) {
                    $user['unsubscribe_key'] = users::GetUnsubscribeKey($user['login']);
                }
                $unsubscribe_link = "{$pHost}/unsubscribe?ukey=" . $user['unsubscribe_key'];
                $advert_template = $this->getAdvertTemplate($user, $n);
                //����� ���������� ����� 0 - ��, 1 - ��������, 2 - ���, 3 - ������� �����, 4 - �����������
                switch ($n) {
                    case 0:
                        $countBs++;
                        break;
                    case 1:
                        $countCar++;
                        break;
                    case 2:
                        $countPro++;
                        break;
                    case 3:
                        $countPayed++;
                        break;
                    case 4:
                        $countVerify++;
                        break;
                }
                $pList = $this->getProjectsForUser($projects, $user, $all_mirrored_specs, $professionsTree);
                $length = count( $pList );
                if ( $length == 0 ) {
                    continue;
                }
	            for ($i = 0; $i < count($pList); $i++) {
		            for ($j = $i; $j < count($pList); $j++) {
		                $a = $pList[$i];
		                $b = $pList[$j];
		                if ( $b["sort_cost"] > $a["sort_cost"]) {
		                    $buf = $pList[$i];
		                    $pList[$i] = $pList[$j];
		                    $pList[$j] = $buf;
		                }
		            }
		        }
		        $pListHtml = "";
		        foreach ($pList as $p) {
		            ob_start();
		            include($_SERVER['DOCUMENT_ROOT'] . "/masssending/tpl.missing_more_than_24h_list_item.php");
		            $pListHtml .= ob_get_clean();
		        }
                $str = "���������� ���� ����� ������ &mdash; ��� ����� ��� �����������.";
                switch ($length) {
                	case 1:
                        $str = "���������� ����� ������ &mdash; �� ����� ��� �����������.";
                        break;
                    case 2:
                        $str = "���������� ��� ����� ������ &mdash; ��� ����� ��� �����������.";
                        break;
                    case 3:
                        $str = "���������� ��� ����� ������ &mdash; ��� ����� ��� �����������.";
                        break;
                    case 4:
                        $str = "���������� ������ ����� ������ &mdash; ��� ����� ��� �����������.";
                        break;
                }
                ob_start();
                include $_SERVER['DOCUMENT_ROOT'] . "/masssending/$advert_template";
                $advHtml = ob_get_clean();
                if ($user["subscr_new_prj"] == 't') {
                    $uids[] = $user["uid"];
                }
                
                $recipient[] = array (
	                'email' => $user['uname']." ".$user['usurname']." [".$user['login']."] " . " <" . $user['email'] . ">",
	                'extra' => array (
	                    'NAME'  => (string) $user['uname'],
	                    'EMAIL' => (string) $user['email'],
	                    'LIST'  => (string) $pListHtml,
	                    'ADV'   => (string) $advHtml,
                        'STR'   => (string) $str,
                        'UNSUBSCRIBE_LINK'   => (string) $unsubscribe_link
	                )
	            );
                $count++;
            }
            
            $this->recipient = $recipient;
            $this->bind($massId);
            $recipient = array();
        }
        $query = "INSERT INTO subscribe_missing_24h_stat (date_subscribe, bs, carusel, pro, payed_places, verify) VALUES (?, ?i, ?i, ?i, ?i, ?i)";
        $dbStat->query($query, date("Y-m-d"), $countBs, $countCar, $countPro, $countPayed, $countVerify);
        return $count;
    }
    /**
     * @see    NewProjForMissingMoreThan24h
     * @desc   ���������� ������ �������, ������� ���������� ������������
     * @param  array $user - ������������� ������ � ������� � ������������
     * @param  int   $n    - ���� ������������ ����� ���������� ����� 0 - ��, 1 - ��������, 2 - ���, 3 - ������� �����, 4 - �����������
     * @return string ��� ������� � ����� $_SERVER['DOCUMENT_ROOT'] . "/masssending/ 
    **/
    private function getAdvertTemplate($user, &$n) {
        $tplList = array(
            0 => "tpl.missing_more_than_24h_sbr_advert.php", //�� 
            1 => "tpl.missing_more_than_24h_carusel_advert.php", //�������� 
            2 => "tpl.missing_more_than_24h_pro_advert.php", //PRO 
            3 => "tpl.missing_more_than_24h_adv_places_advert.php", //������� ����� 
            4 => "tpl.missing_more_than_24h_verify_advert.php" //����������� 
        );
        $n = intval(date('z')) % 5;
        if ( $_GET["debug"] == 1  ) {
            $n = intval($_GET["type"]) % 5;
        } else {
            if ( $n == 2 && $user["is_pro"] == 't') {
                $n = ($n + 2) % 5;
            }
            if ( $n == 4 && $user["is_pro"] == 't') {
                $n = ($n + 2) % 5;
            }
        }
        return $tplList[$n];
    }
    /**
     * @see    NewProjForMissingMoreThan24h
     * @desc   �������� �� projects ��� ������� � ��� ��������, ��������������� ������������� ������������
     * @param  array $projects - ������ � ������� � ��������, ������������� �� ��������� �����
     * @param  array $user     - ������������� ������ � ������� � ������������
     * @param  array $all_mirrored_specs - ������ � ������� �� ���������� ��������������
     * @param  array $professionsTree - ������ ����� ��������������
     * @return string ��� ������� � ����� $_SERVER['DOCUMENT_ROOT'] . "/masssending/ 
    **/
    private function getProjectsForUser($projects, $user, $all_mirrored_specs, $professionsTree) {
        $userProjects = array();
        $userTenders  = array();
        $foundProjectsSpecIds = array(); //������ �� ������������� ������������, �� ������� ������� �������
        $foundTenderSpecIds = array();   ////������ �� ������������� ������������, �� ������� ������� ��������
        foreach ($projects as $p) {
            $projectForSpec = null;
            if ( count($p["specs"]) ) {
            	$projectForSpec = '';
            	foreach ( $p["specs"] as $k => $i ) {
            		$projectForSpec[] = $i["subcategory_id"];
            	}
            }
            if ($projectForSpec !== null ) {
	            //base
	            if ( in_array($user['spec'], $projectForSpec) ) {
	            	if ($p["kind"] != 2 && $p["kind"] != 7) {
	            		$userProjects[ $p["id"] ] = $this->prepareProjectDataForSubscribe($p);
	            		$foundProjectsSpecIds[] = $user['spec'];
	            	} else {
	            		$userTenders[ $p["id"] ] = $this->prepareProjectDataForSubscribe($p);
	            		$foundTendersSpecIds[] = $user['spec'];
	            	}
	                continue;
	            }
	            //additional
	            $add_specs = $user["additional_specs"];
	            $add_specs = $this->getMirroredSpecs(explode(',', $add_specs . ',' . $user['spec']), $all_mirrored_specs);
	            $continue = false;
	            foreach ($add_specs as $spec) {
	                $spec = intval($spec);
		            if ( in_array($spec, $projectForSpec) ) {
		                if ($p["kind"] != 2 && $p["kind"] != 7) {
		                    $userProjects[ $p["id"] ] = $this->prepareProjectDataForSubscribe($p);
		                    $foundProjectsSpecIds[] = $spec;
		                } else {
		                    $userTenders[ $p["id"] ] = $this->prepareProjectDataForSubscribe($p);
		                    $foundTendersSpecIds[] = $spec;
		                }
		                $continue = true;
	                 }
	            }
	            if ($continue) {
	                continue;
	            }
            }
        }
        //���� ����� �������
        if ( count($userProjects) + count($userTenders) < 5) {
            $rel_specs = $this->getRelatedSpecs( $user["additional_specs"] . ',' . $user["spec"], $professionsTree );
            //������� ��� ��, �� ������� ��� �������
            $sz = count($foundProjectsSpecIds);
            $sz2 = count($foundTendersSpecIds);
            if ($sz2 > $sz) {
                $sz = $sz2;
            }
            for ($i = 0; $i < $sz; $i++ ) {
            	if ( $i < count($foundProjectsSpecIds) ) {
	            	$n = $foundProjectsSpecIds[$i];
	            	$rel_specs = str_replace("$n,", "", $rel_specs);
            	}
                if ( $i < count($foundTendersSpecIds) ) {
                    $n = $foundTendersSpecIds[$i];
                    $rel_specs = str_replace("$n,", "", $rel_specs);
                }
            }
            $rel_specs = explode(",", $rel_specs);
            foreach ($projects as $p) {
	            if ( count($p["specs"]) ) {
	                $projectForSpec = array();
	                foreach ( $p["specs"] as $k => $i ) {
	                    $projectForSpec[] = $i["subcategory_id"];
	                }
	            }
                foreach ($rel_specs as $spec) {
                    $spec = intval($spec);
                    if ( in_array($spec, $projectForSpec) ) {
                        if ($p["kind"] != 2 && $p["kind"] != 7) {
                            $userProjects[ $p["id"] ] = $this->prepareProjectDataForSubscribe($p);
                        } else {
                            $userTenders[ $p["id"] ] = $this->prepareProjectDataForSubscribe($p);
                        }
                        continue;
                    }
                }
            }
        }
        $result = array();
        $i = 0;
        $limit = 5 - count($userTenders);
        if ($limit < 3) {
            $limit = 3;
        }
        foreach ($userProjects as $project) {
            if ($i >= $limit) {
                break;
            }
            $result[] = $project;
            $i++;
        }
        foreach ($userTenders as $project) {
            if (count($result) > 4) {
                break;
            }
            $result[] = $project;
        }
        return $result;
    }

    /**
     * @see getProjectsForUser
     * @desc �������� ��� ���������� ������������� �������������� $specs  
     * @param array $specs - �������������, ������� ������� ���� �����
     * @param array $all_mirrored_specs - ������ ��������� ��������������
     * @return array ��������� �������������� ������������
    **/
    private function getMirroredSpecs($specs, $all_mirrored_specs) {
    	$mspecs = array();
        foreach ($specs as $spec) {
            $spec = (int)$spec;
            if ($spec) {
                foreach ($all_mirrored_specs as $ms) {
                    if ( $ms["main_prof"] == $spec ) {
                        $mspecs[] = $ms["mirror_prof"];
                    }
                    if ( $ms["mirror_prof"] == $spec ) {
                        $mspecs[] = $ms["main_prof"];
                    }
                }
                $mspecs[] = $spec;
            }
        }
        return $mspecs;
    }
    /**
    * @see 
    * @desc �������� ������������� ������� $specs 
    * @param string $specs ������ ��������������� �������������� ����������� �������
    * @param array $professionsTree ������ �������������� � �� ����� 
    * @return string ������ ��������������� ������� �������������� ����������� �������
    **/
    private function getRelatedSpecs( $specs, $professionsTree ) {
    	$dbg = false;
    	$relatedSpecs = "";
        $specs = explode(",", $specs);
        foreach ($specs as $spec) {
            $spec = (int)$spec;
            if ($spec) { echo "";
            	if ($spec == 1) {
            		$dbg = true;
            	}
	            foreach ($professionsTree as $group => $list) {
	            	$buffer = "";
	            	$k = 0;
	            	$found = 0;
	                foreach ($list as $id => $name) {
	                    if ($k == 0) {
	                        $k++;
	                        continue;
	                    }
	                    if ($id == $spec) {
	                        $found = 1;
	                    } else {
	                        $buffer .= $id . ",";
	                    }
	                }
	                if ($found) {
	                    $relatedSpecs .= $buffer;
	                }
	            }
            }
        }
        return $relatedSpecs;
    }

    /**
    * @see  getProjectsForUser
    * @desc   �������������� ������ ������� (��������� ���� �������, ������� ��������� � �������� �������� tpl.missing_more_than_24h)
    * @param  array $project ������������� ������ � ������� � �������
    * @return array ������������� ������ � ������� � �������
    **/
    private function prepareProjectDataForSubscribe($project) {
    	$p = $project;
    	if ($p["kind"] == 2 || $p["kind"] == 7) {
    	    $p["str_kind"] = "�������";
    	} else {
    	    $p["str_kind"] = "������";
    	}
        //������ ������� currency (0 - USD, 1 - EUR, 2 - RUR, 3 - FM)
        // ��� ��������� �������: 1 - �� ���, 2 - �� ����, 3 - �� �����, 4 - �� ������
        $currency = array(0 => '$', 1 => '&euro;', 2 => '�.', 3 => 'FM');
        $priceby = array(1 => '���', 2 => '����', 3 => '�����', 4 => '������');
        $p["measure"] = "{$currency[ $p['currency'] ]}/{$priceby[ $p['priceby'] ]}";
        if ($p["kind"] == 7 || $p["kind"] == 2) {
            $p["measure"] = "{$currency[ $p['currency'] ]}";
        }
        if ($p["cost"] == 0) {
            $p["measure"] = "�� ��������������";
            $p["cost"] = '';
        }
        $p["link"] = $GLOBALS["host"] . getFriendlyURL("project" , array( "id" => $p["id"], "name" => $p["name"] ));
        $p["link"] .= "?utm_source=newsletter4&utm_medium=email&utm_campaign=notif_ed_all";
        if ( strlen($p["descr"]) > 200 ) {
            $s = $p["descr"];
            $j = 200;
            for ($i = 199; $i > -1; $i--) {
                if ($s[$i] == ' ') {
                    $j = $i;
                    break;
                }
            }
            $p["descr"] = substr($s, 0, $j) . "&hellip;";
        }
        if ( strlen($p["name"]) > 50 ) {
            $s = $p["name"];
            $j = 50;
            for ($i = 49; $i > -1; $i--) {
                if ($s[$i] == ' ') {
                    $j = $i;
                    break;
                }
            }
            $p["name"] = substr($s, 0, $j) . "&hellip;";
        }
        return $p;
    }
}