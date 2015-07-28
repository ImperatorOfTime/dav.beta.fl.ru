<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/smtp.php';
/**
 * ������ ������ ������ ��� �������� ���������
 * ��������!!! ����-������!
 * 
 */
class SMTP2 extends SMTP {
    
    /**
     * �������� ����� �������.
     *
     * @var string
     */
	public $sender = 'Free-lance.ru <no_reply@free-lance.ru>';
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
     * ��������� ������������ �����.
	 * ���� ����������� ��������, �� �� ���� ����� � ���� :)
     *
     * @var string
     */
	protected $charset = 'windows-1251';
    /**
     * MIME ��� ������. �� ������ ������ �������������� text/plain � text/html.
     * ��� ������������� ����� ���������� �� multipart/mixed ��� multipart/related
     * 
     * @var string
     */
    public $contentType = 'text/html';
    /**
     * ���� SMTP �������.
     *
     * @var string
     */
	protected $_server = 'localhost';
    /**
     * ���� SMTP �������.
     *
     * @var string
     */
	protected $_port = 25;
    /**
     * ������ � ������� � ������������� ������
     * @see self::attach();
     * 
     * @var array
     */
    protected $_attaches = array();
    /**
     * ����� � SMTP �������
	 * ���� ��� ���� ����������� ��������.
     *
	 * @var resource
     */
	//private static $socket = NULL;
    /**
     * ���������� ��������� �������� ������.
     *
	 * @var integer
     */
	//private static $objects = 0;
    /**
     * ��� ��������� ����� �������� � �������� ��� �������� �������.
	 * ����������� ������ ��������� ��������� ��� ���� ������.
     *
     * @var string
     */
	public $log = '';
   /**
     * ��� ��������� ����� �������� � �������� ��� ���� �������� ����� ������.
	 * ����������� ������ ��������� ��������� ��� ���� ������.
     *
     * @var string
     */
	public static $flog = '';
    
    
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
		if ( --self::$objects <= 0 ) {
			$this->_close();
			// ���
			//file_put_contents('/tmp/smtp.log', $this->log);
            //echo "<pre>" . $this->log . "</pre>";
		}
	}
    

    /**
     * �������� ������ ������ ��������. �������� �������� ��� self::_send() � ��������� �������
     * ������ ����������, ����� ������� � ������� ����� ������ ������ ������ �������� ��������.
     * 
     * @return boolean  ����� �������
     */
    public function send() {
        if ( preg_match('/^.+\@.+$/', $this->sender) && preg_match('/^.+\@.+$/', $this->recipient) ) {
            return $this->_send();
        }
        return false;
    }

    
    /**
     * �������������� ������� � �������� ��������. �������� �������� ��� self::_send(). ����� ���
     * ���������� ������� ������ ����������� ������ ����������, �.�. self::$recipient ������������.
     * 
     * @return boolean  ����� �������
     */
    public function masssend() {
        if ( preg_match('/^.+\@.+$/', $this->sender) ) {
            $this->recipient = '';
            return $this->_send();
        }
        return false;
    }
    
    
    /**
     * �������� ����������� ��� �������� ��������
     * 
     * @param  integer $spamid  id ������ ��� ������� ��������� ���������
     * @return integer          id ������ (0 - ������) 
     */
    public function bind($spamid) {
        $db = new DB('spam');
        $i  = 0;
        if ( is_array($this->recipient) ) {
            $recipients = array();
            foreach ( $this->recipient as $r ) {
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
                    $db->query("SELECT mail.bind(?, ?a)", $spamid, $recipients);
                    unset($recipients);
                    $recipients = array();
                    $i = 0;
                }
            }
            if ( $i ) {
                $db->query("SELECT mail.bind(?, ?a)", $spamid, $recipients);
                unset($recipients);
            }
        } else if ( is_string($this->recipient) && $this->recipient != '' ) {
            $spamid = $db->query("SELECT mail.bind(?, ?a)", $spamid, array($this->recipient));
        }
        return $spamid;
    }

    
    /**
	 * ���������, ������� �� � ������ ������ ���������� � SMTP ��������.
	 *
	 * @return   boolean   TRUE ���� ���������� �������; FALSE ���� ���
	 */
	public function connected() {
		return is_resource(self::$socket);
	}
    
    
    /**
     * ������� ���������� ��� ��� �����������, ������� ������ ������������ ������ HTML (inline attached).
     * ������������� ������� ������ ��� ��������� ����������� ����� �� �����������, �����
     * ������������ ���� �����.
     * 
     * @param  string $prefix  ������� ��� ����������� �����, �� �������
     * @return string          ���������� ���
     */
    public function cid($prefix='') {
        $host   = preg_replace("/^https?\:\/\//", '', $GLOBALS['host']);
        $uniqid = uniqid(($prefix != '')? $prefix . '.': '', true);
        return $uniqid . '@' . $host;
    }

    
    /**
     * ��������� ����� � ������
     * 
     * @param mixed $file  ���� � ���� ���������� ����� ��� ������� ������ CFile
     * @param type  $cid   cid �����, ������� ����� �������������� ��� ���������� ������ �� ����.
     *                     ���� cid �� ������, �� ���� �������������� ��� ������� ������������� ����.
     */
    public function attach($file, $cid='') {
        $this->_attaches[] = array(
            'file' => $file,
            'cid'  => $cid
        );
    }

    
    /**
     * ������� ��� ������ self::_open()
	 * ������������� ���������� � SMTP ��������, ���� ��� ��� �� ���� �����������.
	 * ����� ���������� � self::_send() ������� �������� ��� ���� ������ �� ���������. ��� ����� ���� 
	 * ������ �������� ����� ��������� ����� ���������, ����� ������� �������� ��� ���������� ������������.
     *
	 * @return  boolean   TRUE ���� ���������� �����������; FALSE ���� ���������� ���������� �� �������
     */
    public function connect() {
        return $this->_open();
    }

    
    /**
     * ���������� ��������� �� smtp ������. �������� ��� �������, ��� �������������, �� �����.
     * ������� ����� ������ ���������� ��������� spam.php. ��� �������� ����� ��. @see self::send(),
     * @see self::masssend(), @see self::bind
     * 
     * @param  string $sender     �����������
     * @param  string $recipient  ����������
     * @param  string $body       �������������� � �������� ���������, �� ����� �����������
     * @return boolean            �����
     */
    public function mail($sender, $recipient, $body) {
		if ( preg_match('/\<(.+@.+)\>$/', $sender, $o) ) {
            $sender = $o[1];
        } else {
            $sender = $sender;
        }
		if ( preg_match('/\<(.+@.+)\>$/', $recipient, $o) ) {
            $recipient = $o[1];
        } else {
            $recipient = $recipient;
        }
		// ���� ����������� ��������� SERVER == beta ��� IS_LOCAL, �� ����� ����� ����������� ������ ��������� �� $TESTERS_MAIL
		if  ( (defined('SERVER') && SERVER != 'release') || (defined('IS_LOCAL') && IS_LOCAL === true) ) {
			if ( !is_array($GLOBALS['TESTERS_MAIL']) || !in_array($recipient, $GLOBALS['TESTERS_MAIL']) ) {
				$this->sended++;
				return true;
			}
		}
        // ����������� � ��������
		if ( !self::$socket && !$this->_open() ) {
            return false;
        }
        //
		if ( $this->_cmd("MAIL FROM: {$sender}") != 250
			|| $this->_cmd("RCPT TO: {$recipient}") != 250
			|| $this->_cmd("DATA") != 354
		) {
			$this->_cmd("RSET");
			return false;
		}
        //
        fwrite(self::$socket, $body);
        // 
		if ($this->_cmd(".") != 250) {
			$this->_cmd("RSET");
			return false;
		}
		$this->sended++;
		return true;
    }
    
    
    /**
	 * ������������� ���������� � SMTP ��������, ���� ��� ��� �� ���� �����������.
     * @see self::connect()
	 *
	 * @return   boolean   TRUE ���� ���������� �����������; FALSE ���� ���������� ���������� �� �������
	 */
	protected function _open() {
		if ( self::$socket ) {
            return true;
        }
		if ( !(self::$socket = fsockopen($this->_server, $this->_port, $errno, $errstr, 60)) ) {
			return false;
		}
		if ( $this->_cmd(null) != 220 || $this->_cmd("HELO {$this->_server}") != 250 ) {
			fclose(self::$socket);
			self::$socket = null;
			return false;
		}
		return true;
	}
	

    /**
	 * ��������� ���������� � SMTP ��������.
	 * ����� ���������� � �����������, ��� ����������� ���������� ���������� ������� ������,
	 * ������� �������� ��� ���� �� ���������.
	 * ���� ��� ������� �� ����, ��� ����� ��������� ��������� ������ � �����-���� ������ � ����������
	 * ��� ����� �������� ������, �� ���������� ��������� ������ (���������������).
	 *
	 * @return   boolean   TRUE ���� ���������� �������; FALSE ���� ������� �� �������
	 */
    public function _close(){
		if ( self::$socket ) {
			$this->_cmd("QUIT");
			fclose(self::$socket);
			self::$socket = null;
		}
		return true;
	}

    
    // ��������
    public function close() {
        return $this->_close();
    }
    
	
	/**
	 * �������� ������� SMTP �������
	 *
	 * @param   string   �������
	 * @return  integer  ��� ������ SMTP �������
	 */
	protected function _cmd($comm) {
		if ( $comm ) {
			fwrite(self::$socket, "{$comm}\r\n", strlen($comm)+2);
			$this->log  .= "{$comm}\r\n";
			self::$flog .= "{$comm}\r\n";
		}
		$line = '';
		$out  = '';
		$c = 0;
		while ( (strpos($out, "\r\n") === FALSE || substr($line, 3, 1) !== ' ') && $c < 100 ) {
			$line = fgets(self::$socket, 1024);
			$out .= $line;
			$c++;
		}
		$this->log  .= $out;
		self::$flog .= $out;
		if (preg_match("/^([0-9]{1,3}) (.+)$/", $out, $o)) {
			return (int) $o[1];
		}
		return 0;
	}
    
    
    /**
     * ��������� �������������� ���� ��������� � ������ ��� � ������� �� ��������
     * ��. ����� @see self::send(), self::masssend(0
     * 
     * @return boolean  �����
     */
    protected function _send() {
		// ������ � �����������, ������������ � �����
		$sender    = $this->_encodeEmail($this->sender);
        $recipient = ($this->recipient == '')? '%%%recipient%%%': $this->_encodeEmail($this->recipient);
		$subject   = $this->_encode(htmlspecialchars_decode($this->subject, ENT_QUOTES));
        // ��������� ������ ������
        $message = $this->message;
        $message = str_replace(array("\\'", '\\"', "\\\\"), array("'", '"', "\\"), $message);
        $message = preg_replace("'[\r\n]+\.[ \r\n]+'", ".\r\n", $message);
        // ��������� ��������� �� ������ �������� � 80 ��������
        // ����� �������� ����� ��������� ������� ����� ������ ����
        $len  = strlen($message);
        $prev = '';
        $res  = '';
        for ( $i=0, $j=0; $i<$len; $i++, $j++ ) {
            if ( ($j > 80) && ($message{$i} == ' ') ) {
                $res .= "\r\n";
                $j = 0;
            } else if ( $message{$i} == "\n" ) {
                if ( $prev != "\r" ) {
                    $res .= "\r\n";
                } else {
                    $res .= "\n";
                }
                $j = 0;
            } else {
                $res .= $message{$i};
            }
            $prev = $message{$i};
        }
        $message = $res;
        // ���������� ���� �� ��������� ����������� � ������������� �����
        $mixed   = false;
        $related = false;
        for ( $i=0; $i<count($this->_attaches); $i++ ) {
            if ( $this->_attaches[$i]['cid'] == '' || $this->contentType == 'text/plain' ) {
                $mixed = true;
            } else {
                $related = true;
            }
        }
        // ���������� ������� ��� ��������
        $baseContentType = $this->contentType;
        if ( $related ) {
            switch ( $this->contentType ) {
                // ���� ���� ��������� ����������� � text/plain -> ���������� ��� ��������
                case 'text/plain': {
                    $baseContentType = 'multipart/mixed';
                    break;
                }
                // ��� html ������� ���������� related (�������) ������
                case 'text/html': {
                    $baseContentType = 'multipart/related';
                    break;
                }
                default: {
                    $baseContentType = $this->contentType;
                    break;
                }
            }
        }
        if ( $mixed ) {
            $baseContentType = 'multipart/mixed';
        }
        // ��������� ��� ������� ������
        if ( preg_match('/^multipart\//', $baseContentType) ) {
            $multipart = true;
        } else {
            $multipart = false;
        }
        $alternate = false;
        // ������ ���� ���������
        $body  = "MIME-Version: 1.0\r\n";
        $body .= "To: {$recipient}\r\n";
        $body .= "From: {$sender}\r\n";
        $body .= "Subject: {$subject}\r\n";
// ����������� ��� �����, � ���������� ������������� ���� �� ������ ��������. ���� ����������� �� postfix ��������� ����� ����� �������� � ������ ������ �����������
//        $body .= "Date: " . date("r") . "\r\n";
        if ( $multipart ) {
            $boundaries = array();
            $boundary = '------------' . md5(uniqid(time()));
            array_push($boundaries, $boundary);
            $body .= "Content-Type: {$baseContentType};\n boundary=\"{$boundary}\"\r\n\r\n";
            $body .= "This is a multi-part message in MIME format.\r\n";
            if ( $related && $mixed ) {
                $body .= "--{$boundary}\r\n";
                $boundary = '------------' . md5(uniqid(time()));
                array_push($boundaries, $boundary);
                $body .= "Content-Type: multipart/related;\n boundary=\"{$boundary}\"\r\n\r\n";
                $boundary = array_pop($boundaries);
            }
            if ( $this->contentType == 'text/plain' || $alternate ) {
                $body .= "--{$boundary}\r\n";
                $body .= "Content-Type: text/plain; charset={$this->charset}; format=flowed\r\n";
                $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                $body .= $message;
                $body .= "\r\n\r\n";
            }
            if ( $this->contentType == 'text/html' || $alternate ) {
                $body .= "--{$boundary}\r\n";
                $body .= "Content-Type: text/html; charset={$this->charset}\r\n";
                $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                $body .= $message;
                $body .= "\r\n\r\n";
            }
            if ( $related ) {
                foreach ( $this->_attaches as $attach ) {
                    if ( $attach['cid'] == '' ) {
                        continue;
                    }
                    $file = &$attach['file'];
                    $cid  = $attach['cid'];
                    if ( $file instanceof CFile ) {
                        if ( !empty($file->size) ) {
                            $fc = @file_get_contents(WDCPREFIX_LOCAL."/{$file->path}{$file->name}");
                            if ( !empty($fc) ) {
                                $type = $file->getContentType();
                                $name = $this->_encode(htmlspecialchars_decode($file->original_name, ENT_QUOTES));
                            }
                        }
                    } else if ( is_string($file) && $file != '' ) {
                        $fc = @file_get_contents($file);
                        if ( !empty($fc) ) {
                            $name = basename($file);
                            $out  = exec("file -i '{$file}'");
                            $type = preg_replace('/^[^:]+:\s*([^\s;]+).*$/', '$1', $out);
                        }
                    }
                    if ( !empty($fc) ) {
                        $body .= "--{$boundary}\r\n";
                        $body .= "Content-Type: {$type};\n name=\"{$name}\"\r\n";
                        $body .= "Content-Transfer-Encoding: base64\r\n";
                        $body .= "Content-ID: <{$cid}>\r\n";
                        $body .= "Content-Disposition: inline;\n filename=\"{$name}\"\r\n\r\n";
                        $body .= chunk_split(base64_encode($fc));
                    }
                }
                if ( $mixed ) {
                    $body .= "--{$boundary}--\r\n\r\n";
                    $boundary = array_pop($boundaries);
                }
            }
            if ( $mixed ) {
                foreach ( $this->_attaches as $attach ) {
                    if ( $attach['cid'] != '' && $this->contentType != 'text/plain' ) {
                        continue;
                    }
                    $file = &$attach['file'];
                    if ( $file instanceof CFile ) {
                        if ( !empty($file->size) ) {
                            $fc = @file_get_contents(WDCPREFIX_LOCAL."/{$file->path}{$file->name}");
                            if ( !empty($fc) ) {
                                $type = $file->getContentType();
                                $name = $this->_encode(htmlspecialchars_decode($file->original_name, ENT_QUOTES));
                            }
                        }
                    } else if ( is_string($file) && $file != '' ) {
                        $fc = @file_get_contents($file);
                        if ( !empty($fc) ) {
                            $name = basename($file);
                            $out  = exec("file -i '{$file}'");
                            $type = preg_replace('/^[^:]+:\s*([^\s;]+).*$/', '$1', $out);
                        }
                    }
                    if ( !empty($fc) ) {
                        $body .= "--{$boundary}\r\n";
                        $body .= "Content-Type: {$type};\n name=\"{$name}\"\r\n";
                        $body .= "Content-Transfer-Encoding: base64\r\n";
                        $body .= "Content-Disposition: attachment;\n filename=\"{$name}\"\r\n\r\n";
                        $body .= chunk_split(base64_encode($fc));
                    }
                }
            }
            $body .= "--{$boundary}--\r\n";
        } else {
            if ( $this->contentType == 'text/html' ) {
                $body .= "Content-Type: text/html; charset={$this->charset}\r\n";
            } else {
                $body .= "Content-Type: text/plain; charset={$this->charset}; format=flowed\r\n";
            }
            $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $body .= $message;
            $body .= "\r\n\r\n";
        }
        // ���������� ��������� � �������
        $db = new DB('spam');
        if ( $this->recipient == '' ) {
            $spamid = $db->val(
                "SELECT mail.send(?, ?, ?, ?, ?, ?a)",
                $this->sender,
                NULL,
                'SMTP2',
                $this->subject,
                $body,
                array()
            );
        } else {
            $spamid = $db->val(
                "SELECT mail.send(?, ?, ?, ?, ?, ?a)",
                $this->sender,
                $this->recipient,
                'SMTP2',
                $this->subject,
                $body,
                array()
            );
        }
        unset($body);
        return $spamid;
    }
    
    
    /**
     * �������� ����� � BASE64 ��� ����������� ����������� ����� �������� ������� �� �������������� ���������
     * 
     * @param  string  $text  �����, ������� ���������� ������������
     * @return string         �������������� �����
     */
    protected function _encode($text) {
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
	
    // ��������!!!
    public function encodeEmail($text) {
        return $this->_encodeEmail($text);
    }

    /**
     * �������������� ������ � ������������ ���������, �������� ����� ��� ���������� � ����������� � ������������.
     * @see smail::encode()
     * 
     * @param   string   $in_str   ��� ���������� ������ � �����, �������� "���� <fedya@mail.ru>".
     * @param   string   $charset  ��������� (�� ��, ��� ������������ � �������� ���������).
     * @return  string             ������� ������ ��� ������������� � ���������.
     */
    protected function _encodeEmail($text) {
        $subj = preg_match_all("'([^<]*)<([^>]*)>[/s,]*'", $text, $matches);
        $out  = array();
        foreach ($matches[1] as $ikey => $sting)
            $out[] = $this->_encode(trim($sting))." <".$matches[2][$ikey].">";
        if (count($out)) {
            $out_str = implode(", ", $out);
        }
        else {
            $out_str = $text;
        }
        unset($out);
        return $out_str;
    }
    
    
}

