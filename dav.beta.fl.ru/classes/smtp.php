<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
setlocale(LC_TIME, "ru_RU");
/**
 * SMTP Mail API. ����� ��� �������� ��������� SMTP �������.
 * �������� ��� ������� smail � pmail.
 *
 */
class SMTP {

    /**
     * �������� ����� �������.
     *
     * @var string
     */
	public $from = 'FL.ru <no_reply@free-lance.ru>';

    /**
     * ���������� ������, �������� ���� <fedya@mail.ru>.
     *
     * @var string
     */
	public $recipient = '';

    /**
     * ���� ������.
     *
     * @var string
     */
	public $subject = '';

    /**
     * ����� ������.
     *
     * @var string
     */
	public $message = '';
	
    /**
     * ��� ��������� ����� �������� � �������� ��� �������� �������.
	 * ����������� ������ ��������� ��������� ��� ���� ������.
     *
     * @var string
     */
	public $log = '';

    /**
     * ���������� ������� ������������ ���������.
     *
     * @var integer
     */
	public $sended = 0;

    /**
     * ���� SMTP �������.
     *
     * @var string
     */
	protected $server = 'localhost';

    /**
     * ���� SMTP �������.
     *
     * @var string
     */
	protected $port = 25;

    /**
     * ��������� ������������ �����.
	 * ���� ����������� ��������, ���������� ��� ��������� self::charset_convert().
     *
     * @var string
     */
	protected $charset = 'windows-1251';
	
    /**
     * ������ � �������������� �������
	 * @see self::AttachFile()
     *
     * @var array
     */
	public $attaches = array();

    /**
     * ���������� ������������� ������
     *
     * @var integer
     */
	private $attaches_nums = 0;

    /**
     * ����� � SMTP �������
	 * ���� ��� ���� ����������� ��������.
     *
	 * @var resource
     */
	protected static $socket = NULL;

    /**
     * ���������� ��������� �������� ������.
     *
	 * @var integer
     */
	protected static $objects = 0;
	
    /**
     * ��� ��������� ����� �������� � �������� ��� ���� �������� ����� ������.
	 * ����������� ������ ��������� ��������� ��� ���� ������.
     *
     * @var string
     */
	public static $flog = '';

    public function splitMessage($content) {
        $len  = strlen($content);
        $prev = '';
        $message = '';
        for ( $i = 0, $j = 0; $i < $len; $i++, $j++ ) {
            if ( ($j > 80) && ($content{$i} == ' ') ) {
                $message .= "\r\n";
                $j = 0;
            } else if ( $content{$i} == "\n" ) {
                if ( $prev != "\r" ) {
                    $message .= "\r\n";
                } else {
                    $message .= "\n";
                }
                $j = 0;
            } else {
                $message .= $content{$i};
            }
            $prev = $content{$i};
        }
        
        return $message;
    }
    
    /**
     * ��������� ������ ���������
     */
    public function prepareMessage() {
        $this->message = preg_replace("~(http|https):/\{(.*?)\}?/([^<|^\s]*)~mix", '<a href="$1://$3" target="_blank">$2</a>', $this->message);
    }
    
    /**
     * �������� email ���������
     * 
     * @param  string $content_type  mime type ���������
     * @param  array  $files         ������ � id ������������� ������ (file_template)
     * @return integer               id ������ (0 - ������)
     */
    public function send($content_type='text/plain', $files = array()) {
        $DB   = new DB('spam');
        if($this->prepare) $this->prepareMessage();
        $message = $this->splitMessage($this->message);
        if ( is_array($this->recipient) || empty($this->recipient) ) {
            $spamid = $DB->val(
                "SELECT mail.send(?, ?, ?, ?, ?, ?a)", 
                $this->from, 
                NULL, 
                $content_type, 
                $this->subject, 
                $message,
                $files
            );
        } else {
            $spamid = $DB->val(
                "SELECT mail.send(?, ?, ?, ?, ?, ?a)", 
                $this->from, 
                $this->recipient, 
                $content_type, 
                $this->subject, 
                $message,
                $files
            );
        }
        if ( $spamid && is_array($this->recipient) ) {
            $this->bind($spamid);
        }
        return $spamid;
    }
    
    
    /**
     * �������� ��������� � ��������
     * 
     * @param  integer  $spamid  id ������ ��� ������� ��������� ���������
     * @param  boolean  $unset_recipient  ����� �� ���������� ������ $this->recipient ����� �������������?
     * @return integer           id ������ (0 - ������) 
     */
    public function bind($spamid, $unset_recipient = false) {
        $DB = new DB('spam');
        $i  = 0;
        if ( is_array($this->recipient) ) {
            $recipients = array();
            foreach ( $this->recipient as $j=>$r ) {
                if ( is_array($r) && !empty($r['email']) ) {
                    $extra = array();
                    if ( $r['extra'] ) {
                        foreach ( $r['extra'] as $k => $v ) {
                            $extra[] = $k . '=' . str_replace('&', '&&', $v);
                        }
                    }
                    $recipients[] = array($r['email'], implode('&', $extra));
                } else {
                    $recipients[] = $r;
                }
                $i++;
                if ( $i % 5000 == 0 ) {
                    $DB->query("SELECT mail.bind(?, ?a)", $spamid, $recipients);
                    unset($recipients);
                    $recipients = array();
                    $i = 0;
                }
                if($unset_recipient) {
                    unset($this->recipient[$j]);
                }
            }
            if ( $i ) {
                $DB->query("SELECT mail.bind(?, ?a)", $spamid, $recipients);
                unset($recipients);
            }
        } else if ( is_string($this->recipient) && $this->recipient != '' ) {
            $spamid = $DB->query("SELECT mail.bind(?, ?a)", $spamid, array($this->recipient));
            if($unset_recipient) {
                unset($this->recipient);
            }
        }
        return $spamid;
    }
    
    
    /**
	 * ���������� ���������.
	 * ��� ������ ������ ��������� ����� � SMTP ������� (��. self::Connect()) � ��������� ��� � �����������.
	 * 
	 * @param   string   mime ��� ���������
	 * @return  boolean  TRUE ���� ��������� ����������; FALSE ���� �� ������� ��������� ��� ���������� ����������
	 */
	public function SmtpMail($content_type='text/plain', &$files = array()) {
		// ���� ����������� ��������� SERVER == beta ��� IS_LOCAL, �� ����� ����� ����������� ������ ��������� �� $TESTERS_MAIL
		if ((defined('SERVER') && SERVER != 'release') || (defined('IS_LOCAL') && IS_LOCAL === TRUE)) {
			if (preg_match("/<([^>]+)>$/", $this->recipient, $o)) {
				$test = $o[1];
			} else {
				$test = $this->recipient;
			}
			if (!is_array($GLOBALS['TESTERS_MAIL']) || !in_array($test, $GLOBALS['TESTERS_MAIL'])) {
				$this->sended++;
				return TRUE;
			}
		}
		
		$from      = $this->encode_email($this->from);
		$recipient = $this->encode_email($this->recipient);
		$subject   = $this->encode(htmlspecialchars_decode($this->subject, ENT_QUOTES));
		$mail_from = $from;
		$rcpt_to   = $recipient;
		if($brk = strpos($mail_from, '<')) $mail_from = substr($mail_from, $brk);
		if($brk = strpos($rcpt_to, '<'))   $rcpt_to = substr($rcpt_to, $brk);
    $message   = str_replace(array("\\'", '\\"', "\\\\"), array("'", '"', "\\"), $this->message);
    $message   = preg_replace("'[\r\n]+\.[ \r\n]+'", ".\r\n", $message);
		if (!self::$socket && !$this->Connect()) return FALSE;

		if ($this->cmd("MAIL FROM: $mail_from") != 250
			|| $this->cmd("RCPT TO: $rcpt_to") != 250
			|| $this->cmd("DATA") != 354
		) {
			$this->cmd("RSET");
			return FALSE;
		}

		// ������������ � �������� ���� ������
		$body = "Mime-Version: 1.0\r\n";
		if (!empty($files)) {
			$boundary = md5(uniqid(time()));
			$body .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";
			$body .= "To: {$recipient}\r\n";
			$body .= "From: {$from}\r\n";
			$body .= "Subject: {$subject}\r\n";
			$body .= "--{$boundary}\r\n";
		}
		$body .= "Content-Type: {$content_type}; charset={$this->charset}\r\n";
		$body .= "Content-Transfer-Encoding: 8bit\r\n";
		if (empty($files)) {
			$body .= "To: {$recipient}\r\n";
			$body .= "From: {$from}\r\n";
			$body .= "Subject: {$subject}\r\n";
		}
		$body .= "\r\n";
		$body .= "{$message}\r\n\r\n";
		fwrite(self::$socket, $body);
		// �����
		if (!empty($files)) {
			for ($i=0; $i<count($files); $i++) {
				if (!empty($files[$i])) {
					fputs(self::$socket, "--{$boundary}\r\n".$files[$i]);
				}
			}
		}

		if ($this->cmd(".") != 250) {
			$this->cmd("RSET");
			return FALSE;
		}

		$this->sended++;
		return TRUE;
    }

	
    /**
	 * ������������� ���������� � SMTP ��������, ���� ��� ��� �� ���� �����������.
	 * ����� ���������� � self::Send() ������� �������� ��� ���� ������ �� ���������. ��� ����� ���� 
	 * ������ �������� ����� ��������� ����� ���������, ����� ������� �������� ��� ���������� ������������
	 *
	 * @return   boolean   TRUE ���� ���������� �����������; FALSE ���� ���������� ���������� �� �������
	 */
	public function Connect() {
		if (self::$socket) return TRUE;
		if (!(self::$socket = fsockopen($this->server, $this->port, $errno, $errstr, 5))) {
			return FALSE;
		}
		if ($this->cmd(NULL) != 220 || $this->cmd("HELO {$this->server}") != 250) {
			fclose(self::$socket);
			self::$socket = NULL;
			return FALSE;
		}
		return TRUE;
	}
	

    /**
	 * ��������� ���������� � SMTP ��������.
	 * ����� ���������� � �����������, ��� ����������� ���������� ���������� ������� ������,
	 * ������� �������� ��� ���� ������ �� ���������.
	 * ���� ��� ������� �� ����, ��� ����� ��������� ��������� ������ � �����-���� ������ � ����������
	 * ��� ����� �������� ������, �� ���������� ��������� ������ (���������������).
	 *
	 * @return   boolean   TRUE ���� ���������� �������; FALSE ���� ������� �� �������
	 */
    public function Close(){
		if (self::$socket) {
			$this->cmd("QUIT");
			fclose(self::$socket);
			self::$socket = NULL;
		}
                //������ ����
                $this->log = self::$flog = '';
		return TRUE;
	}

	
    /**
	 * ���������, ������� �� � ������ ������ ���������� � SMTP ��������.
	 *
	 * @return   boolean   TRUE ���� ���������� �������; FALSE ���� ���
	 */
	public function �onnected() {
		return is_resource(self::$socket);
	}

	
    /**
	 * �����������. ���������� ������� ��������� �������� ������.
	 *
	 */
	public function __construct() {
		++self::$objects;
	}

	
    /**
	 * ����������. ��������� ���������� � SMTP ��������, ���� ������ ��� �������� ������
	 *
	 */
	public function __destruct() {
		if (--self::$objects <= 0) {
			$this->Close();
			// ���
			//file_put_contents('/tmp/smtp.log', self::$flog);
		}
	}
	
	
    /**
     * ������������ ����(�) ��� ��������� �� �����
	 * ������ ������������ ����� ���� ����� ����� ����� ������������ � �������� ��������� ��� self::SmtpMail
     * 
     * @param   mixed   $files    ���� � ���� ������� ������ CFile ��� ������ ����� ��������
     * @return  array             ������������ �����
     */
	public function CreateAttach($files) {
		if (!is_array($files)) $files = array($files);
		$i = 0;
		$res = array();
		foreach ($files as $file) {
			if (($file instanceof CFile) && ($file->name != '') && ($fcnt = @file_get_contents(WDCPREFIX_LOCAL."/{$file->path}{$file->name}"))) {
				$original_name = $this->encode( htmlspecialchars_decode($file->original_name, ENT_QUOTES) );
				$res[$i]  = "Content-Type: ".$file->getContentType()."; name=\"{$original_name}\"\r\n";
				$res[$i] .= "Content-Transfer-Encoding: base64\r\n";
				$res[$i] .= "Content-Disposition: attachment; filename=\"{$original_name}\"\r\n\r\n";
				$res[$i] .= chunk_split(base64_encode($fcnt))."\r\n";
				$i++;
			}
		}
		return $res;
	}
	
    /**
     * ������������ ��������� ����(�) ��� ��������� �� �����
	 * ������ ������������ ����� ���� ����� ����� ����� ������������ � �������� ��������� ��� self::SmtpMail
     * 
     * @param   mixed   $files    ���� �� ����� ��� ������ �����
     * @return  array             ������������ �����
     */
	public function CreateLocalAttach($files) {
		if (!is_array($files)) $files = array($files);
		$i = 0;
		$res = array();
		foreach ($files as $file) {
			if (($file != '') && ($fcnt = @file_get_contents($file))) {
				$filename = basename($file);
                $out = exec("file -i '{$file}'");
				$contentType = preg_replace('/^[^:]+:\s*([^\s;]+).*$/', '$1', $out);
				$res[$i]  = "Content-Type: ".$contentType."; name=\"{$filename}\"\r\n";
				$res[$i] .= "Content-Transfer-Encoding: base64\r\n";
				$res[$i] .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n\r\n";
				$res[$i] .= chunk_split(base64_encode($fcnt))."\r\n";
				$i++;
			}
		}
		return $res;
	}
	

    /**
     * ������������ plain text � HTML
     * 
     * @param   string   $text    �������� �����
     * @param   boolean  $body    ���� 1, �� ������ �������������� �� �����
     * @return  string            html-���������.
     */	
	public function ToHtml($text, $nolink=0) {
		$text = str_replace(array("<", ">", "'", "\"", ' - ', ' -- ', "\n"), array("&lt;", "&gt;", "&#039;", "&quot;", ' &#150; ', ' &mdash; ', ' <br/>'), $text);
        $text = preg_replace('~(https?:/){[^}]+}/~', '$1/', $text); // ������ ������� �����������.
		if (!$nolink) {
			$text = preg_replace_callback("/((https?\:\/\/|www\.)[-a-zA-Z�-��-߸�0-9\.]+\.[a-zA-Z�-��-߸�]{2,30}(?:\/[^\s]+)?)/", array($this, 'ToHtml_callback'), $text);
			$text = preg_replace("/([-_a-zA-Z0-9\.]+?\@[-a-zA-Z�-��-߸�0-9\.]+\.[a-zA-Z�-��-߸�]{2,30})/", "<a href='mailto:\$1'>\$1</a>", $text);
		}
		$text = textWrap($text, 76);
		return $text;
	}
	
	/**
	 * ��������������� callback ������� @see smtp::ToHtml
	 *
	 * @param  array $m
	 * @return string
	 */
	private function ToHtml_callback($m) {
		if ($m[2] == 'http://' || $m[2] == 'https://') {
			$link = $m[1];
		} else {
			$link = 'http://'.$m[1]; 
		}
		return "<a href='$link'>$link</a>";
	}

    /**
     * ��������� ���� ��������� � ������� HTML, � ����� ��������� �� FL.ru.
     * 
     * @param   string  $uname    ��� ������������-���������� ���������.
     * @param   string  $body     �������� ����� ���������.
     * @param   array   $format   ����� ��������� � ������������ ��� ������, ��������, ������ ���, ����� �� ���� �����������.
     * @return  string           html-���������.
     */
    public function GetHtml($uname, $body, $format=array('header'=>'default', 'footer'=>'default'), $params = null) {
        if (!empty($format) && !is_array($format)) $format = array('header'=>$format, 'footer'=>$format);
        $body = preg_replace('~(https?:/){[^}]+}/~', '$1/', $body); // ������ ������� �����������.
        $html_header = '';
        $html_footer = '';
        if($format['footer'] == 'frl_subscr_projects' || $format['footer'] == 'frl_simple_projects') {
            $format['footer'] = str_replace("frl_", "", $format['footer']);
            $role = '����������';
        } else {
            $role = '������������';
        }
        
        if($format['footer'] == 'sub_emp_projects') {
            $subscr = true;
            $format['footer'] = str_replace("sub_", "", $format['footer']);
        } else {
            $subscr = false;
        }
        
        if ($format['header']=='simple_with_add') {
            
            $html_header = '
                <div style="font-size:10px; color:#7e7e7e;">
                    ����� �� ���������� �� ������ ������ �� ������� <a style="font-size:10px; color:#006ed6" target="_blank" href="'.$GLOBALS['host'].'">FL.ru</a>, �������� ��� ����� <a style="font-size:10px; color:#006ed6" target="_blank" href="mailto:no_reply@free-lance.ru">no_reply@free-lance.ru</a> � ���� �������� �����. 
                    <a style="font-size:10px; color:#006ed6" target="_blank" href="https://feedback.fl.ru/topic/532678-instruktsiya-po-dobavleniyu-email-adresa-flru-v-spisok-kontaktov/">����������</a>
                </div>
                <br/>
                <br/>
            ';
            
            $html_header .= "������������".($uname ? ", {$uname}." : "!");
            
        } elseif ($format['header']=='default' || $format['header']=='simple' || $format['header']=='info') {
            $html_header .= "������������".($uname ? ", {$uname}." : "!");
        
        } elseif ($format['header']=='noname') {
            $html_header .= "������������!";
        } elseif ($format['header']=='default_new') {
            $html_header .= "������������, %USER_NAME%!";
        } elseif ($format['header'] == 'subscribe') {
            $html_header .= "������������".($uname ? ", {$uname}." : "!")." ";
            $html_header .= "� ".($params['type']==1 ? '�����' : '������ '.($params['topic_title'] ? ' &laquo;'.$params['topic_title'].'&raquo;' : '').' ����������').($params['title'] ? ' &laquo;'.$params['title'].'&raquo;' : '').", "; 
            $html_header .= "�� �����".($params['type']==1 ? '��' : '��')." �� ���������, �������� ����� �����������.";
        } elseif ($format['header'] == 'subscribe_edit_comment') {
            $html_header .= "������������".($uname ? ", {$uname}." : "!")." <br/><br/>";
            $html_header .= "� ".($params['type']==1 ? '�����' : '����������').($params['title'] ? ' &laquo;'.$params['title'].'&raquo;' : '').", "; 
            $html_header .= "�� �����".($params['type']==1 ? '��' : '��')." �� ���������, �������������� �����������.";
        } elseif ($format['header'] == 'subscribe_edit_post') {
            $html_header .= "������������".($uname ? ", {$uname}." : "!")." <br/><br/>";
            $html_header .= ($params['type']==1 ? '�  �����' : '����� '.($params['topic_name'] ? ' &laquo;'.$params['topic_name'].'&raquo;' : '').' ����������').($params['title'] ? ' &laquo;'.$params['title'].'&raquo;' : '');
            $html_header .= ($params["to_subscriber"] ? ", �� ������� �� ���������," : "")." ��������������.";
        }  elseif ($format['header'] == 'subscribe_delete_comment') {
            $html_header .= "������������".($uname ? ", {$uname}." : "!")." <br/><br/>";
            $html_header .= "� ".($params['type']==1 ? '�����' : '����������').($params['title'] ? ' &laquo;'.$params['title'].'&raquo;' : '').", "; 
            $html_header .= "�� �����".($params['type']==1 ? '��' : '��')." �� ���������, ������ �����������.";
        } elseif ($format['header'] == 'subscribe_delete_post') {
            $html_header .= "������������".($uname ? ", {$uname}." : "!")." <br/><br/>";
            $html_header .= ($params['type']==1 ? '����' : ($params['is_comment']? '����������� � ������  ': '����� ').($params['topic_name'] ? ' &laquo;'.$params['topic_name'].'&raquo;' : '').' ���������� ').($params['title'] ? ' &laquo;'.$params['title'].'&raquo;' : '').", "; 
            if ( !$params['to_topicstarter'] ) {
                $html_header .= "�� ������� �� ���������, ������.";
            } else {
                $html_header .= "������ " . ($params["is_author"] ? "������� ����" : "����������� ����������") .".";
            }
        }

        if(!empty($params['login'])) {
            $lnk_setup_mail = "<a href='{$GLOBALS['host']}/unsubscribe?ukey=".users::GetUnsubscribeKey($params['login'])."'>�� ���� ��������</a>";
        } else {
            if ( empty($params['target_footer']) ) {
                $lnk_setup_mail = "�� �������� &laquo;�����������/��������&raquo;";
            } else {
                $lnk_setup_mail = "<a href='{$GLOBALS['host']}/unsubscribe?ukey=%UNSUBSCRIBE_KEY%'>�� ���� ��������</a>";
            }
        }
        
        if ( !empty($params['utm_campaign']) ) {
            $lnk_team = "<a href='{$GLOBALS['host']}/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign={$params['utm_campaign']}{$this->_addUrlParams('b', '&')}'>FL.ru</a>";
        } 
        else {
            $lnk_team = "<a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";
        }

        if ($format['footer']=='default') {
            //$html_footer .= "�� ������ ��������� ����������� {$lnk_setup_mail}.";
            //$html_footer .= "<br><br>";
            //$html_footer .= "������� FL.ru ���������� ��� �� ������� � ����� ������ �������.";
            $html_footer .= "<br><br>";
            $html_footer .= "�������� ������,";
            $html_footer .= "<br>";
            $html_footer .= "������� {$lnk_team}";
        } else if ($format['footer']=='feedback_default') {
            $html_footer .= "�� ���� ����������� �������� �� ������ ���������� � ���� <a href='https://feedback.fl.ru/' taraget='_blank'>������ ���������</a>. ";
            //$html_footer .= "<br/>�� ������ ��������� ����������� {$lnk_setup_mail}.";
            //$html_footer .= "<br><br>";
            //$html_footer .= "������� FL.ru ���������� ��� �� ������� � ����� ������ �������.";
            $html_footer .= "<br><br>";
            $html_footer .= "�������� ������,";
            $html_footer .= "<br>";
            $html_footer .= "������� {$lnk_team}";
        } else if ($format['footer'] == 'simple') {
            $html_footer .= "�������� ������,";
            $html_footer .= "<br>";
            $html_footer .= "������� <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";
        } else if ($format['footer'] == 'info_robot') {
            $html_footer .= "������ ������ ���������� �������� ������� ������� FL.ru � �� ������� ������.";
            $html_footer .= "<br>";
            $html_footer .= "�� ���� ����������� �������� �� ������ ���������� � ���� <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>������ ���������</a>.";
            $html_footer .= "<br><br>";
            $html_footer .= "�������� ������,";
            $html_footer .= "<br>";
            $html_footer .= "������� <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";
        } else if ($format['footer'] == 'norisk_robot') {
            $html_footer .= "������ ������ ���������� �������� ������� FL.ru � �� ������� ������.";
            $html_footer .= "<br>";
            $html_footer .= "�� ���� ����������� �������� �� ������ ���������� � ���� <a href='https://feedback.fl.ru/{$this->_addUrlParams('b', '?')}'>������ ���������</a>.";
            $html_footer .= "<br><br>";
            $html_footer .= "�������� ������,";
            $html_footer .= "<br>";
            $html_footer .= "������� <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";
        } else if ($format['footer'] == 'info') {
            $html_footer .= "������� FL.ru";
            $html_footer .= "<br>";
            $html_footer .= "<a href='mailto:info@fl.ru'>info@fl.ru</a>";
            $html_footer .= "<br>";
            $html_footer .= "<a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>{$GLOBALS['host']}</a>";
        } else if ($format['footer'] == 'subscribe') {
            $html_footer .= "����� ��������� �������� �����������, ������� � ".($params['type']==1 ? '����' : '����������')." � ������� ������ \"����������\".";
            //$html_footer .= "<br><br>";
            //$html_footer .= "������� FL.ru ���������� ��� �� ������� � ����� ������ �������.";
            $html_footer .= "<br><br>";
            $html_footer .= "�������� ������,";
            $html_footer .= "<br>";
            $html_footer .= "������� <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";
        } else if($format['footer'] == 'simple_norisk') {
            $html_footer .= "���� � ��� ���� �����-�� ������� �� ����������� ������, ����������� � ������ ��������� <a href='mailto:norisk@fl.ru'>norisk@fl.ru</a>";
            $html_footer .= "<br><br>";
            $html_footer .= "������� FL.ru ���������� ��� �� ������� � ����� ������ �������.";
            $html_footer .= "<br><br>";
            $html_footer .= "�������� ������,";
            $html_footer .= "<br>";
            $html_footer .= "������� <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";     
        } else if($format['footer'] == 'simple_projects') {
            $html_footer .= "� ������� �� ������� ������ ������ - <strong>������ ������ �����������.</strong><br>";
            $html_footer .= "� ���� ������ �� ������ ��� �����, ��������� � ��������������� �/��� �������������� ����������� ������. � �� ������ ����������� ���������� � ��������.";
            $html_footer .= "<br><br>";
            $html_footer .= "���������� ��� <strong><a href='{$GLOBALS['host']}/public/?step=1&public={$params['project']['id']}&choose_bs=1'>�������� ������ ������ �� ���������� ������</a>.</strong><br>";
            $html_footer .= "<a href='{$GLOBALS['host']}/promo/bezopasnaya-sdelka/'>�����-�������� ���������� ������</a>.";
                    
            $html_footer .= "<br><br>";
            $html_footer .= "�������� ������,";
            $html_footer .= "<br>";
            $html_footer .= "������� <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>"; 
        } else if($format['footer'] == 'subscr_projects') {
            $html_footer .= "����� �������������� �����, ����������� � ���� �������������� � {$role}, ����������� ��������������� �<a href='{$GLOBALS['host']}/promo/sbr/{$this->_addUrlParams('b')}'>���������� �������</a>�.";
            $html_footer .= "<br><br>";
            //$html_footer .= "�� ������ ��������� ����������� {$lnk_setup_mail}.";
            //$html_footer .= "<br><br>";
            $html_footer .= "�������� ������,";
            $html_footer .= "<br>";
            $html_footer .= "������� <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>"; 
        } else if($format['footer'] == 'emp_projects') {
            if($subscr) {
                //$html_footer .= "<br><br>";
                //$html_footer .= "�� ������ ��������� ����������� {$lnk_setup_mail}.";
            }
            $html_footer .= "<br><br>";
            $html_footer .= "�������� ������,";
            $html_footer .= "<br>";
            $html_footer .= "������� <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";
        } else if($format['footer'] == 'simple_adv') {
            $html_footer .= "��������� ��������� PRO� - ��� �������� �������� � ���������������� ��������� �����. 
                            ����� ���������, ��� �� ������� ���������� ����������������� �������� ���������� �� 2 �������������� �������, 
                            � ������� ������ ������� ��� PRO ���������� ����� 10 300 ������. �������� �������� ��������� PRO�, 
                            � ����� ������������ � ������� ������������� ����������� ������ �������� �� FL.ru ����� <a href='{$GLOBALS['host']}/payed/{$this->_addUrlParams('b')}'>�����</a>.";
            $html_footer .= "<br/><br/>";
            $html_footer .= "�������� ������,";
            $html_footer .= "<br/><br/>";
            $html_footer .= "������� <a href='{$GLOBALS['host']}/{$this->_addUrlParams('b')}'>FL.ru</a>";
        }
        ob_start(null, 0, true);
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" /> 
</head>
<style type="text/css">
a { color: #003399; text-decoration:underline; font-size:10pt; font-family:Tahoma; }
body { margin:10px; background:#ffffff; color:#000000; font-size:10pt; font-family:Tahoma; }
td { font-size:10pt; font-family:Tahoma; }
</style>
<body>
<table cellspacing="0" border="0" cellpadding="0" width="100%">
<tbody>
<tr>
<td>
<?=($html_header? "{$html_header}<br><br>": "")?>
<?=$body?>
<br><br><?=$html_footer?>
</td>
</tr>
</tbody>
</table>
</body>
</html>
<?
    $ret = ob_get_clean();
        return $ret;
    }
	
	
	/**
	 * �������� ������� SMTP �������
	 *
	 * @param   string   �������
	 * @return  integer  ��� ������ SMTP �������
	 */
	protected function cmd($comm) {
		if ($comm) {
			fwrite(self::$socket, "{$comm}\r\n", strlen($comm)+2);
			$this->log .= "{$comm}\r\n";
			self::$flog .= "{$comm}\r\n";
		}
		$line = '';
		$out  = '';
		$c = 0;
		while ((strpos($out, "\r\n") === FALSE || substr($line, 3, 1) !== ' ') && $c < 100) {
			$line = fgets(self::$socket, 1024);
			$out .= $line;
			$c++;
		}
		$this->log .= $out;
		self::$flog .= $out;
		if (preg_match("/^([0-9]{1,3}) (.+)$/", $out, $o)) {
			return (int) $o[1];
		}
		return 0;
	}

	
    /**
     * �������� ����� � BASE64 ��� ����������� ����������� ����� �������� ������� �� �������������� ���������
     * 
     * @param  string  $text  �����, ������� ���������� ������������
     * @return string         �������������� �����
     */
    private function encode($text) {
        if ($text && $this->charset) {
            // define start delimimter, end delimiter and spacer
            $end = "?=";
            $start = "=?" . $this->charset . "?B?";
            $spacer = $end . " " . $start;
            // determine length of encoded text within chunks
            // and ensure length is even
            // ����� ����� ��������� ����� > 128, ���� ����� �������� � ��������� ������ � ��������� �������.
            $length = 128 - strlen($start) - strlen($end);
            $length = $length - ($length % 4);
            // encode the string and split it into chunks
            // with spacers after each chunk
            $text = base64_encode($text);
            $text = chunk_split($text, $length, $spacer);
            // remove trailing spacer and
            // add start and end delimiters
            $spacer = preg_quote($spacer);
            $text = preg_replace("/" . $spacer . "$/", "", $text);
            $text = $start . $text . $end;
        }
        return $text;
    }

	

    /**
     * �������������� ������ � ������������ ���������, �������� ����� ��� ���������� � ����������� � ������������.
     * @see smail::encode()
     * 
     * @param   string   $in_str   ��� ���������� ������ � �����, �������� "���� <fedya@mail.ru>".
     * @param   string   $charset  ��������� (�� ��, ��� ������������ � �������� ���������).
     * @return  string             ������� ������ ��� ������������� � ���������.
     */
    private function encode_email($text) {
        $subj = preg_match_all("'([^<]*)<([^>]*)>[/s,]*'", $text, $matches);
        $out  = array();
        foreach ($matches[1] as $ikey => $sting)
            $out[] = $this->encode(trim($sting))." <".$matches[2][$ikey].">";
        if (count($out)) {
            $out_str = implode(", ", $out);
        }
        else {
            $out_str = $text;
        }
        unset($out);
        return $out_str;
    }
	
	/**
     * ������ ��������� ��� ������
     * @param mixed $role 'f'(���������) ��� 'e'(������������) ��� 'b'(���)
     * @param string $firstChar ������ ������ � ����������, ? ��� &
     * @return type string
     */
    protected function _addUrlParams($role, $firstChar = '?') {
        if ($role == 'f') { 
            $params = 'utm_source=newsletter4&utm_medium=uvedomlenie&utm_campaign=free-lancer'; // ��������� ��� ����������
        } elseif ($role == 'e') {
            $params = 'utm_source=newsletter4&utm_medium=uvedomlenie&utm_campaign=rabotodatel'; // ��� ������������
        } elseif ($role == 'b') {
            $params = 'utm_source=newsletter4&utm_medium=uvedomlenie&utm_campaign=polzovatel'; // ��� �����
        } else return '';
        return $firstChar.$params;
    }
    
    
    /**
     * UTM-����� ��� ���������
     * 
     * @param type $utm_medium
     * @param type $utm_content
     * @param type $utm_campaign
     * @param type $firstChar
     * @return type
     */
    public static function _addUtmUrlParams($utm_medium, $utm_content, $utm_campaign, $firstChar = '?'){
        return "{$firstChar}utm_source=newsletter4&utm_medium={$utm_medium}&utm_content={$utm_content}&utm_campaign={$utm_campaign}";
    }

}

?>
