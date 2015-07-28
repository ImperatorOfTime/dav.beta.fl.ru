<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/pmail.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/account.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/QChat.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/template.php';

class messages {

    protected $DB;
    
    /**
     * uid ������������ ����������� � ������� ����������� (���������� � ������������)
     * ���� ������������ � �������� Masssend*, ����� ����� �������������� �����
     * 
     * @var integer
     */
    public $uid = 0;

    /**
     * ������������ ������ ����� � ����msgsCnt��
     *
     * @var integer
     */
    const MAX_FILE_SIZE = 5242880;

    /**
     * ������������ ���������� ������������� ������
     *
     * @var integer
     */
    const MAX_FILES = 10;

    /**
     * ������������ ����� ������� ���������
     *
     * @var integer
     */
    const MAX_MSG_LENGTH = 20000;
    
    /**
     * ���������� �������������� ����� ������ ���������� �� ��������.
     */
    const PM_AUTOFOLDERS_PP = 10;

    /**
     * ���-�� ��������� ����������� ��� �������� ������ ��� ����� � ������ ������� SPAM_CAPTCHA_TIME_WITHOUT
     *
     * @var integer
     */
    const SPAM_CAPTCHA_MSG_COUNT = 10;

    /**
     * ���-�� ��������� ����������� ��� �������� ������ ��� ����� � ������ ������� SPAM_CAPTCHA_TIME_WITHOUT ��� �������������, ������� ���-�� �������� �� �����
     *
     * @var integer
     */
    const SPAM_CAPTCHA_MSG_COUNT_PAY = 30;

    /**
     * ������ �������(� ��������) � ������� ����� ���������� ������ SPAM_CAPTCHA_MSG_COUNT ��� �����
     *
     * @var integer
     */
    const SPAM_CAPTCHA_TIME_WITHOUT = 60;

    /**
     * ������ �������(� ��������) � ������� �������� ����� ������������ �����
     *
     * @var integer
     */
    const SPAM_CAPTCHA_TIME_SHOW = 900;

    /**
     * ������� ��� ������� �������� ����� ��������� � ����
     * 
     */
    const MEMBUFF_CHAT_PREFIX = 'QChatMsgsCnt';
    
    /**
     * ���� � ��������
     */
    const TPL_PATH = "/templates/messages/";
    
    

    
    /**
     * ��� ������� ��� ������� ���������� ��������� � �������������
     * 
     * @var type 
     */
    const TABLE_ALLOWED = 'messages_allowed';

    
    const CACHE_TAG_IS_ALLOWED = 'Messages_isAllowed_ForUserId%s';
    const KEY_CHECK_IS_ALLOWED = 'Messages_isAllowed__WasCheckForUserId%sTo%s';
    const KEY_CHECK_TAG_IS_ALLOWED = 'Messages_isAllowed__WasCheck';
    
    
    const MESSAGES_NOT_ALLOWED = '
        ����������� �������� ������ ��������� ������������� ������� ��-�� ����� �� ����, 
        ����� �������, ����� �� ��������� ������������ � ������� ������� ������������, 
        ������������ � ��� �� ������ �� ����� ��� ������ ��� ������������ ������� �����������.';




    /**
	 * ����������� ������
	 */
	public function __construct( $uid = 0 ) {
		$this->uid = $uid;
        $this->DB = new DB('plproxy');
	}


    /**
     * �������� �������� ��������� �������������
     *
     * @param  string   $message      ����� ���������
     * @param  boolean  $recipients   ������ � �������� ������������
     * @param  boolean  $mailFunc     ��� ������ ������ pmail ��� �������� email
     * @param  array    $attachments  ������������� ����� (������ �������� ������ CFile)
     *
     * @return integer                0 � ������ ������, id ���������� ��������� � ������ ������
     */
    public function masssendTo($message, $recipients, $mailFunc = '', $attachments = array()) {
        $DB = new DB('plproxy');
        $files = array();
		foreach ( $attachments as $file ) {
			$files[] = $file->id;
		}
        $msgid = $DB->val("SELECT masssend(?, ?, ?a, ?)", $this->uid, $message, $files, $mailFunc);
        if ( $msgid && !empty($recipients) ) {
            $where = $DB->parse("login IN (?l) AND is_banned = B'0' AND substr(subscr::text,8,1) = '1'", $recipients);
            $sql = "SELECT uid FROM users WHERE {$where}";
            $DB->query("SELECT masssend_sql(?, ?, ?)", $msgid, $this->uid, $sql);
        }
        return $msgid;
    }
    
    
    /**
     * �������� �������� ��� ���� �������������
     *
     * @param  string   $message      ����� ���������
     * @param  boolean  $pro          TRUE - ������ ��� PRO, FALSE - ������ ��� �� PRO, NULL - ��� ����
     * @param  boolean  $mailFunc     ��� ������ ������ pmail ��� �������� email
     * @param  array    $attachments  ������������� ����� (������ �������� ������ CFile)
     *
     * @return integer                0 � ������ ������, id ���������� ��������� � ������ ������
     */
    public function masssendToAll($message, $pro, $mailFunc = '', $attachments = array()) {
        $DB = new DB('plproxy');
        $files = array();
		foreach ( $attachments as $file ) {
			$files[] = $file->id;
		}
        $msgid = $DB->val("SELECT masssend(?, ?, ?a, ?)", $this->uid, $message, $files, $mailFunc);
        if ( $msgid ) {
            $where = $DB->parse("is_banned = B'0' AND substr(subscr::text,8,1) = '1' AND uid <> ?i", $this->uid);
            if ( !is_null($pro) ) {
                $where = $pro? ' AND is_pro = TRUE ': ' AND is_pro = FALSE ';
            }
            $sql = "SELECT uid FROM users WHERE {$where}";
            $DB->query("SELECT masssend_sql(?, ?, ?)", $msgid, $this->uid, $sql);
        }
        return $msgid;
    }
	
	
    /**
     * �������� �������� ��� ���� �������������
     *
     * @param  string   $message      ����� ���������
     * @param  boolean  $pro          TRUE - ������ ��� PRO, FALSE - ������ ��� �� PRO, NULL - ��� ����
     * @param  boolean  $mailFunc     ��� ������ ������ pmail ��� �������� email
     * @param  array    $attachments  ������������� ����� (������ �������� ������ CFile)
     *
     * @return integer                0 � ������ ������, id ���������� ��������� � ������ ������
     */
    public function masssendToEmployers($message, $pro, $mailFunc = '', $attachments = array()) {
        $DB = new DB('plproxy');
        $files = array();
		foreach ( $attachments as $file ) {
			$files[] = $file->id;
		}
        $msgid = $DB->val("SELECT masssend(?, ?, ?a, ?)", $this->uid, $message, $files, $mailFunc);
        if ( $msgid ) {
            $where = $DB->parse("is_banned = B'0' AND substr(subscr::text,8,1) = '1' AND uid <> ?i", $this->uid);
            if ( !is_null($pro) ) {
                $where = $pro? ' AND is_pro = TRUE ': ' AND is_pro = FALSE ';
            }
            $sql = "SELECT uid FROM employer WHERE {$where}";
            $DB->query("SELECT masssend_sql(?, ?, ?)", $msgid, $this->uid, $sql);
        }
        return $msgid;
    }


    /**
     * �������� �������� ��� ���� �����������
     *
     * @param  string   $message      ����� ���������
     * @param  boolean  $pro          TRUE - ������ ��� PRO, FALSE - ������ ��� �� PRO, NULL - ��� ����
	 * @param  array    $profs        NULL - ���� �����������. ��� ������ � ���������������� ��������� ��� ��������
	 *                                ������ ����� �����:
	 *                                id - id. ��������� ��� �������
	 *                                is_group - ������ (true) / ��������� (false)
     * @param  boolean  $mailFunc     ��� ������ ������ pmail ��� �������� email
     * @param  array    $attachments  ������������� ����� (������ �������� ������ CFile)
     *
     * @return integer                0 � ������ ������, id ���������� ��������� � ������ ������
     */
    public function masssendToFreelancers($message, $pro, $profs, $mailFunc = '', $attachments = array()) {
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        $dbMaster = new DB('master');
        $dbProxy  = new DB('plproxy');

        $files = array();
        foreach ($attachments as $file) {
            $files[] = $file->id;
        }
        
        $where = $dbMaster->parse("u.is_banned = B'0' AND substr(subscr::text,8,1) = '1' AND uid<>?i", $this->uid);
        
        if ( empty($profs) ) {
			
            if ( !is_null($pro) ) {
                $where .= $dbMaster->parse( $pro? ' AND u.is_pro = TRUE ': ' AND u.is_pro <> TRUE ' );
            }
            
        } else {
            
            $items  = array();
            $groups = array();
            
            foreach ( $profs as $prof ) {
                if ( $prof['is_group'] ) {
                    $groups[] = $prof['id'];
                } else {
                    $items[]  = $prof['id'];
                }
            }
            
            if ( $groups ) {
                $rows = $dbMaster->col("SELECT id FROM professions WHERE prof_group IN (?l)", $groups);
                $items = array_merge($items, $rows);
            }

            if ( empty($items) ) {
                return 0;
            }
            
            $unions = array_unique($items);
            foreach ( $unions as $prof ) {
                $mirrors = professions::GetMirroredProfs($prof);
                $items = array_merge($items, $mirrors);
            }
            $items = array_unique($items);

            $inner = $dbMaster->parse(' LEFT JOIN spec_paid_choise sp ON sp.user_id = u.uid ', $items);
            $wprof = $dbMaster->parse('u.spec IN (?l) OR (sp.prof_id IN (?l) AND sp.paid_to >= NOW())', $items, $items);
            if ( $pro === FALSE ) {
                $where .= $dbMaster->parse(' AND u.is_pro <> TRUE ');
            } else {
                $inner .= $dbMaster->parse(' LEFT JOIN spec_add_choise sa ON sa.user_id = u.uid ', $items);
                $wprof .= $dbMaster->parse(' OR (sa.prof_id IN (?l) AND u.is_pro = TRUE) ', $items);
                $where .= $pro? $dbMaster->parse(' AND u.is_pro = TRUE '): '';
            }
            $where = "{$where} AND ({$wprof})";
            
        }
        
        $sql = $dbMaster->parse("SELECT DISTINCT uid FROM freelancer u {$inner} WHERE {$where}");
            
        $msgid = $dbProxy->val("SELECT masssend(?, ?, ?a, ?)", $this->uid, $message, $files, $mailFunc);
        if ( $msgid ) {
            $dbProxy->query("SELECT masssend_sql(?, ?, ?)", $msgid, $this->uid, $sql);
        }

		return $msgid;
	}
    

	/**
	 * ��������� ��������� ����������� ���������� � �������� �������� (������� mass_sending_users)
	 *
	 * @param   integer   $user_id         uid ������������ ����������� ��������
	 * @param   integer   $masssending_id  id ��������
	 * @param   string    $text            ����� ��������
	 * @param   string    $posted_time     ���� �������� ��������
         * @param   bool $skip_mail           ���� TRUE - �� ���������� ����������� � ����� ��������� �� �����.
         * @return  bool true - �����, false - ������
	 */
	function Masssending($user_id, $masssending_id, $text, $posted_time, $skip_mail=false) {
		$master  = new DB('master');
		$plproxy = new DB('plproxy');
		$error = '';
		
		$files = $master->col("SELECT file.id FROM mass_sending_files m INNER JOIN file ON m.fid = file.id WHERE mass_sending_id = ? ORDER BY m.pos", $masssending_id);

        $ignors = $plproxy->col("SELECT user_id FROM ignor_me(?)", $user_id);
        array_push($ignors, $user_id);
        
        $sql = $master->parse("
            SELECT 
                m.uid 
            FROM 
                mass_sending_users m 
            INNER JOIN 
                users u ON m.uid = u.uid AND u.is_banned = B'0' 
            WHERE 
                mid = ?i AND m.uid NOT IN (?l)
        ", $masssending_id, $ignors);
        /*$msgid = $plproxy->val("SELECT masssend(?, ?, ?a, ?)", $user_id, $text, $files, ($skip_mail? '': 'SpamFromMasssending'));
        if ( $msgid ) {
            $plproxy->query("SELECT masssend_sql(?, ?, ?)", $msgid, $user_id, $sql);
        }*/
        
        $msgid = $plproxy->val( 'SELECT masssend(?, ?, ?a, ?, ?, ?, ?)', $user_id, $text, $files, $masssending_id, $posted_time, ($skip_mail? '': 'SpamFromMasssending'), $sql );
		
        // TODO: ��������� �������
		//$master->query("DELETE FROM mass_sending_users WHERE mid = ?", $masssending_id);
            return empty( $plproxy->error );
	}
	
	/**
	 * ���������� ������ � ��������� �������� ��������
	 * 
	 * @param  int $message_id ID ���������
	 * @return array
	 */
	function GetMessage($message_id) {
		$DBProxy  = new DB('plproxy');
        $DBMaster = new DB('master');
        $message = $DBProxy->row("SELECT * FROM messages_mass_userdata(?i)", $message_id);
		if ( !empty($message) ) {
			// !!! OLD !!!
			if ($message['files'] && $message['files']!='{}') {
				$res = $DBMaster->query("SELECT * FROM file WHERE id IN (".substr($message['files'], 1, strlen($message['files'])-2).")");
				while ($row = pg_fetch_assoc($res)) {
					$message['attach'][] = $row;
				}
			}
			// !!! OLD !!!
		} else {
			$message = NULL;
		}
		return $message;
	}
	
	/**
	 * ���������� ����������� �������� ��������
	 * 
	 * @param  int $user_id  UID ����������. �� ������������
	 * @param  int $message_id ID ���������
	 * @param  int $limit ���������� �����������
	 * @param  int $offset � ������ ��������
	 * @param  bool $only_subscr �����������. �� ��������� TRUE - ���������� ������ �������������.
	 * @return array
	 */
	function GetZeroMessageUsers($user_id, $message_id, $limit='ALL', $offset=0, $only_subscr=TRUE) {
		$DB = new DB;
		if ($users = $DB->rows("SELECT * FROM messages_zeros_userdata(?i, ?i)".($only_subscr ? " WHERE substr(subscr::text,8,1) = '1'" : '')." LIMIT $limit OFFSET $offset", $user_id, $message_id)) {
			return $users;
		} else {
			return NULL;
		}
	}
	
    /**
     * ������������ ����� ������ ���������
     *
     * @param integer $user_id              id ������������-����������� 
     * @param string $target_login          ����� ������������-����������
     * @param string $text                  ����� ���������
     * @param array $files                  ������������� �����
     * @param integer $force                ����������/����� ������ �� ������ (1/0)
     * @param bool $skip_mail               ���� TRUE - �� ���������� ����������� � ����� ��������� �� �����.
	 * @param string $attachedfiles_session   ID ������ ����������� ������
     *
     * @return mixed                    ��������� �� ������ � ���� ������ � ������ �� ��������������
     */
    function Add($user_id, $target_login, $text, $files, $force=0, $skip_mail=false, $attachedfiles_session=null, &$message_id=0){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/ignor.php";
        
		$users = new users;
        $login  = $users->GetName($user_id, $err);
        $tar_id = $users->GetUid($err,$target_login);
        if ((ignor::CheckIgnored($tar_id, $user_id) || in_array($target_login, array('admin', 'Anonymous'))) && !$force) {
			$error = "������������ �������� ���������� ��� ���������";
        } else {
            if ($files) {
                if (count($files) > messages::MAX_FILES) {
                    $alert[1] = "�� �� ������ ���������� ������ " . messages::MAX_FILES . " ������ � ���������.";
                } else {
                    $max_file_size = messages::MAX_FILE_SIZE;
                    foreach ($files as $file) {
                        $ext = $file->getext();
                        $file->max_size = $max_file_size;
                        $max_file_size -= $file->size;
                        if ( !in_array($ext, $GLOBALS['disallowed_array']) ) {
                            $f_name = $file->MoveUploadedFile($login['login'] . "/contacts");
                            if (!isNulArray($file->error)) {
                                if ($max_file_size < 0) {
                                    $alert[1] = "�� ��������� ����������� ���������� ������ ������";
                                } else {
                                    $alert[1] = $GLOBALS['PDA'] ? '���� �� ������������� �������� ��������'
                                                        : "���� ��� ��������� ������ �� ������������� �������� ��������.";
                                }
                                break;
                            }
                        } else {
                            $alert[1] = $GLOBALS['PDA'] ? '���� �� ������������� �������� ��������'
                                                        : '���� ��� ��������� ������ ����� ������������ ������.';
                        }

                    }
                }
            }
            
			if (empty($alert) && empty($error)) {
			    $memBuff = new memBuff();
			    
			    // �������������� ����� ��� �������� �������� ������ ����������
                global $aPmUserUids;
                
                if ( 
                    in_array($tar_id, $aPmUserUids) // ����� ������� ��������� 
                    || SERVER === 'local' || SERVER === 'beta' || SERVER === 'alpha' // ��� �����������
                ) { 
                    $DBproxy = new DB;
                    
                    $nRecId = $DBproxy->val( 'SELECT mess_pm_ustf_add(?i, ?i)', $tar_id, $user_id );
    			    
    			    if ( $nRecId ) {
    			    	$memBuff->delete( 'pmAutoFolder'. $tar_id .'_'. $nRecId );
    			    }
                }
                //---------------------------------------------
			    
				$DB = new DB;
				$f = array();
				if ($files) {
					foreach ($files as $file) {
						$f[] = $file->id;
					}
				}

				require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
				$attachedfiles = new attachedfiles($attachedfiles_session);
				$attachedfiles_files = $attachedfiles->getFiles();
				if($attachedfiles_files) {
					foreach($attachedfiles_files as $attachedfiles_file) {
						$cFile = new CFile($attachedfiles_file['id']);
						$cFile->table = 'file';
                        $ext = $cFile->getext();

                        $tmp_dir = "users/".substr($login['login'], 0, 2)."/".$login['login']."/contacts/";
                        $tmp_name = $cFile->secure_tmpname($tmp_dir, '.'.$ext);
                        $tmp_name = substr_replace($tmp_name,"",0,strlen($tmp_dir));

						$cFile->_remoteCopy($tmp_dir.$tmp_name, true);
						$f[] = $cFile->id;
					}
				}
				$attachedfiles->clear();
                
                $aNoMod = array_merge( $GLOBALS['aContactsNoMod'], $GLOBALS['aPmUserUids'] );
                //$bNoMod = hasPermissions('streamnomod', $user_id) || hasPermissions('streamnomod', $tar_id) || is_pro(true, $user_id) || is_pro(true, $tar_id) || in_array($user_id, $aNoMod);
                $bNoMod = true; // #0022344: ������ �� ������� �����
				$message_id = $DB->val("SELECT messages_add(?i, ?i, ?, ?b, ?a, ?b)", $user_id, $tar_id, $text, $skip_mail, $f, $bNoMod);
                
                if ( $user_id % 2 == $tar_id % 2 ) {
                    $memBuff->delete(self::MEMBUFF_CHAT_PREFIX . $tar_id);
                }
                
                if ( $message_id /*&& $bNoMod*/ && !$skip_mail && !QChat::active($tar_id) ) {
                    $mail = new pmail;
                    $mail->NewMessage($user_id, $tar_id, stripslashes($text));
                }

                if ($message_id) {
                    require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/base.php");
                    require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/api.php");
                    require_once($_SERVER['DOCUMENT_ROOT']."/classes/external/api/mobile.php");
                    externalApi_Mobile::addPushMsg($tar_id, 'message', array('from_user_id'=>get_uid(false), 'text'=>stripslashes($text)));
                }
			}
			
        }
        
		return array($alert,$error);
    }
    
    /**
     * �������������� ������� ���������
     * 
     * @param  int $from_id UID ������������-����������� 
     * @param  int $modified_id UID ������������ ����������� ���������
     * @param  int $id ID ���������
     * @param  string $msg_text ����� ���������
     * @param  array $attachedfiles_file ������������� �����
     * @param  string $modified_reason ������� ��������������
     * @return bool true - �����, false - ������
     */
    function Update( $from_id = 0, $modified_id = 0, $id = 0, $msg_text = '', $attachedfiles_file = array(), $modified_reason = '' ) {
        $bRet = false;
        
        if ( $from_id && $id && $msg_text ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
            
            $users = new users;
            $login = $users->GetName( $from_id, $err );
            $files = array();
            
            if ( $login ) {
                if ( $attachedfiles_file ) {
                    foreach( $attachedfiles_file as $file ) {
                        switch ( $file['status'] ) {
                            case 1:
                                // ��������� ����
                                $cFile = new CFile($file['id']);
                                $cFile->table = 'file';
                                $ext = $cFile->getext();

                                $tmp_dir = "users/".substr($login['login'], 0, 2)."/".$login['login']."/contacts/";
                                $tmp_name = $cFile->secure_tmpname($tmp_dir, '.'.$ext);
                                $tmp_name = substr_replace($tmp_name,"",0,strlen($tmp_dir));

                                $cFile->_remoteCopy($tmp_dir.$tmp_name, true);
                                $files[] = $cFile->id;
                                break;
                            case 3:
                                // ����� �����������
                                $files[]  = $file['id'];
                            break;
                            case 4:
                                // ������� ����
                                $cFile    = new CFile();
                                $cFile->Delete( $file['id'] );
                                break;
                        }
                    }
                }
                
                $DB = new DB;
                $DB->val( "SELECT message_update(?i, ?i, ?, ?a, ?)", $id, $modified_id, $msg_text, $files, $modified_reason );
                
                $bRet = empty( $DB->error );
            }
        }
        
        return $bRet;
    }
    
    /**
     * �������� ���������� � ���������� ���������
     * 
     * @param int $user_id UID ������������-����������� 
     * @param int $message_id ID ���������
     */
    function Get( $user_id, $message_id ) {
        $DB     = new DB;
        $sQuery = 'SELECT * FROM message_get(?i, ?i);';
        $aRows  = $DB->rows( $sQuery, $user_id, $message_id );
        
        self::getMessagesAttaches( $aRows );
        
        return $aRows[0];
    }

    /**
     * �������� ������ ������������ ������ � ���������
     *
     * @return  array               ���������� � ������
     *
     */
    function getAttachedFiles() {
        global $DB;

        $fList = array();
        if($_SESSION['attachedfiles_contacts']['added']) {
            $login = $_SESSION['login'];
            $files = ($_SESSION['attachedfiles_contacts']['added'] ? preg_split("/ /", trim($_SESSION['attachedfiles_contacts']['added'])) : array());
            $dfiles = ($_SESSION['attachedfiles_contacts']['deleted'] ? preg_split("/ /", trim($_SESSION['attachedfiles_contacts']['deleted'])) : array());
            if(count($files)) {
                $sql = "SELECT * FROM file WHERE MD5(id::text || fname) IN (?l);";
                $aFiles = $DB->rows($sql, $files);
                foreach($aFiles as $f) {
                    $cFile = new CFile("users/".substr($login, 0, 2)."/".$login."/contacts/".$f['fname']);
                    if($cFile->id) {
                    	if(in_array(md5($cFile->id.$cFile->name), $dfiles)) {
                    		$is_deleted = 't';
                    	} else {
                    		$is_deleted = 'f';
                    	}
                        array_push($fList, array('file_id'=>$cFile->id, 'name'=>$cFile->name, 'path'=>$cFile->path, 'size'=>$cFile->size, 'ftype'=>$cFile->getext(), 'is_del'=>$is_deleted));
                    }
                }
            }
        }
        return $fList;
    }

	
    /**
     * �������� ������ � ������ ���������� ����� ����� ��������������
     *
     * @param integer $to_id          id ������������-����������
     * @param string $from_login      ����� ������������-�����������
     * @param integer $num_msgs_from  �����, ������� � ������-�� ���������
     * @param integer $msg_offset     ���������� ���������� ��������� (($msg_offset-1) * $GLOBALS['msgspp'])
     *
     * @return mixed                  ������ ������� � ������ ������ ��� ����� ������
     */
    function GetMessages($to_id, $from_login, &$num_msgs_from, $offset=1, $limit=NULL){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";

		$limit = $limit? $limit: $GLOBALS['msgspp'];
        $offset = ((($offset < 1)? 1: $offset) - 1) * $limit;
        $user = new users();
        $user->login = $from_login;
        $from_id = $user->GetUid($error);
        $num_msgs_from = 0;

        if ($from_id) {

			$DB = new DB;
            $offset = intvalPgSql( (string) $offset );
			$rows = $DB->rows("SELECT m.*, array_length(m.files, 1) AS fcount FROM messages_dialog(?i, ?i) m LIMIT ?i OFFSET ?i", 
				$to_id, $from_id, $limit, $offset);
            
			self::getMessagesAttaches( $rows );	
            self::readDialog( $to_id, $from_id );
			$num_msgs_from = $this->num_msgs_from;
			return $rows;
			
		}
    }
    
    /**
     * �������� ��������� �����������
     * 
     * @param  int $to_id UID ������������-����������
     * @param  int $from_id UID ������������-�����������
     * @return bool true - �����, false - ������
     */
    function readDialog( $to_id = 0, $from_id = 0 ) {
        $DB = new DB;
        
        if ( $this->num_msgs_from = $DB->val("SELECT messages_dialog_count(?i, ?i)", $to_id, $from_id) ) {
            $DB->query( 'SELECT messages_dialog_read(?i, ?i)', $to_id, $from_id );

            if ( empty($DB->error) ) {
                $mem = new memBuff();
                $mem->delete("msgsNewSender{$to_id}");
            }
        }
        
        return empty( $DB->error );
    }
    
    /**
     * ���������� ������� ������� ����� ����� ��������������. � ������� �� ������ ������������� ���������
     * � ��������� �������� ��������. ����� ��������� ������� ����� � ����������� ��������������
     * 
     * @param  string  $uid1           uid ��� ���� ����������� �������
     * @param  integer $uid2           uid (��� ������ � uid'���) � ��� ����� �������
     * @param  integer $limit          ���������� ���������, ������� ����� �������� (� �����)
     *                                 ������ ����� �����. 'ALL' ������������ ������
     * @param  integer $maxid          �������� ������ ��������� � id < $maxid (0 = ���)
     *                                 (��������� ������ ���� $uid2 ������)
     * @return array                   ������ � �����������
     */
    function GetHistory($uid1, $uid2, $limit=4, $maxid=0) {
        if ( empty($uid1) || empty($uid2) ) {
            return;
        }
        $where = '';
        $DB    = new DB;
        if ( !is_array($uid2) ) {
            $where = ($maxid > 0)? $DB->parse("WHERE id < ?", $maxid): '';
            $uid2  = array($uid2);
        }
        $sql = "SELECT m.*, array_length(m.files, 1) AS fcount FROM messages_history(?i, ?a, ?i) m {$where}";
        $rows  = $DB->rows($sql, $uid1, $uid2, $limit);
        self::getMessagesAttaches($rows);
        return $rows;
    }
    
    
    /**
     * �������� ������ � ������ ���������� ����� ����� �������������� ��� �����������
     * ���� �� ������ ��������� ������� �� ����� �� ����� ���������� ������������
     *
     * @param integer $to_id          id ������������-����������
     * @param string $from_login      ����� ������������-�����������
     * @param integer $num_msgs_from  �����, ������� � ������-�� ���������
     * @param integer $msg_offset     ���������� ���������� ��������� (($msg_offset-1) * $GLOBALS['msgspp'])
     *
     * @return mixed                  ������ ������� � ������ ������ ��� ����� ������
     */
    function GetMessagesForModers($to_id, $from_login, $offset=1, $limit=NULL){
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        $limit         = $limit? $limit: $GLOBALS['msgspp'];
        $offset        = ((($offset < 1)? 1: $offset) - 1) * $limit;
        $user          = new users();
        $user->login   = $from_login;
        $from_id       = $user->GetUid($error);
        $num_msgs_from = 0;
        if ( $from_id ) {
            $DB     = new DB;
            $offset = intvalPgSql( (string) $offset );
            $rows   = $DB->rows("SELECT m.*, array_length(m.files, 1) AS fcount FROM messages_dialog(?i, ?i) m WHERE moderator_status IS NOT NULL LIMIT ?i OFFSET ?i", 
				$to_id, $from_id, $limit, $offset);
            self::getMessagesAttaches( $rows );
            return $rows;
        }
    }
    
    
    /**
     * ����������� ��������� ����� � ������� ���������
     * 
     * @param  array $rows ������ ���������
     * @param  string $pk ������ � �������, ���������� ID ���������
     * @return array
     */
    function getMessagesAttaches( &$rows, $pk = 'id' ) {
        if ( is_array($rows) && count($rows) ) {
            $DB = new DB;
            
            $files = '';
			$fids  = array();
			for ($i=0; $i<count($rows); $i++) {
				$f = $DB->array_to_php($rows[$i]['files']);
				for ($j=0; $j<count($f); $j++) {
				    if($f[$j]) {
					    $fids[$f[$j]] = $rows[$i][$pk];
    					$files .= $f[$j].',';
					}
				}
				$rows[$i]['files'] = array();
			}
			
			// !!! OLD !!!
			if ($files) {
				$res = pg_query(DBConnect(), "SELECT * FROM file LEFT JOIN mass_sending_files AS msf ON msf.fid = file.id WHERE id IN (".substr($files, 0, strlen($files)-1).") ORDER BY msf.pos, id");
				while ($row = pg_fetch_assoc($res)) {
					for ($i=0; $i<count($rows); $i++) {
						if ($rows[$i][$pk] == $fids[$row['id']]) {
							if (!is_array($rows[$i]['files'])) $rows[$i]['files'] = array();
							$rows[$i]['files'][] = $row;
							break;
						}
					}
				}
			}
			// !!! OLD !!!
        }
    }


    /**
     * �������� ��������� ���������
     * 
     * @param  int $msg_id 
     * @param  int $del_uid 
     * @return bool true - �����, false - ������
     */
    function deleteMessage( $msg_id = 0, $del_uid = 0 ) {
        $DB = new DB;
        
        $DB->query( 'SELECT message_delete(?i, ?i)', $msg_id, $del_uid );
        
        return empty( $DB->error );
    }
    
    /**
     * �������� �������� ������������� ��� ������� ���� �������
     *
     * @param integer  $uid     id ������������
     * @param string   $search  ��������� ��� ������ ������������
     *
     * @return mixed            ������ ���������
     */
    function GetContactsWithNote($uid, $search=''){
        $DB = new DB;
		return $DB->rows("SELECT * FROM notes_users_search(?i, ?)", $uid, $search);
    }
	
    
	/**
     * �������� �������� �������������
     *
     * @param  integer  $uid     id ������������
     * @param  string   $folder  �����, ��� ������� �������� ��������
     * @param  string   $search  ��������� ��� ������ � ������ ������������
     *
     * @return array             ������ ���������
     */
    public function GetContacts($uid, $folder=0, $search=NULL, $limit = 'ALL', $offset = 0, &$count = -1) {
		$rows = array();
		if ($search) {
			if ($folder > 0) {
                $func = $this->DB->parse("messages_search_folder(?i, ?i, ?)", $uid, $folder, $search);
			} else if ($folder == 0) {
                $func = $this->DB->parse("messages_search(?i, ?)", $uid, $search);
			} else if ($folder == -1) {
                $func = $this->DB->parse("messages_search_team(?i, ?)", $uid, $search);
			} else if ($folder == -2) {
                $func = $this->DB->parse("messages_search_ignor(?i, ?)", $uid, $search);
			} else if ($folder == -3) {
                $func = $this->DB->parse("messages_search_del(?i, ?)", $uid, $search);
			} else if ($folder == -4) {
                $func = $this->DB->parse("messages_search_notes(?i, ?)", $uid, $search);
			} else if ($folder == -6) {
                $func = $this->DB->parse("messages_search_mass(?i, ?)", $uid, $search);
			} else if ($folder == -7) {
                $func = $this->DB->parse("messages_search_unread(?i, ?)", $uid, $search);
			}
		} else {
			if ($folder > 0) {
                $func = $this->DB->parse("messages_contacts_folder(?i, ?i)", $uid, $folder);
			} else if ($folder == 0) {
                $func = $this->DB->parse("messages_contacts(?i)", $uid);
			} else if ($folder == -1) {
                $func = $this->DB->parse("messages_contacts_team(?i)", $uid);
			} else if ($folder == -2) {
                $func = $this->DB->parse("messages_contacts_ignor(?i)", $uid);
			} else if ($folder == -3) {
                $func = $this->DB->parse("messages_contacts_del(?i)", $uid);
			} else if ($folder == -4) {
                $func = $this->DB->parse("messages_contacts_notes(?i)", $uid);
			} else if ($folder == -6) {
                $func = $this->DB->parse("messages_contacts_mass(?i)", $uid);
			} else if ($folder == -7) {
                $func = $this->DB->parse("messages_contacts_unread(?i)", $uid);
			}
		}

        // ����� ������, �� �������� ��������� -- ������ ���� ������ �� �������. � ������, ����� ����� ���������� � �������,
        // ���-�� ��������� ������� ��������.
        $sql = "
          WITH w_contacts as (SELECT * FROM {$func})
          SELECT *, (SELECT COUNT(1) as count FROM w_contacts) as __count FROM w_contacts LIMIT {$limit} OFFSET {$offset}
        ";
        $rows = $this->DB->rows($sql);
        if($count !== -1) {
            $count = $rows[0]['__count'];
        }
         
		return $rows;
	}
    
    /**
     * ���������� ������ � ������� �� ����������� ���������� ���������
     * 
     * @param int $uid - id ������������ (���������� ���������)
     */
    public function GetLastMessageContact ($uid) {
        $user = $this->DB->row("SELECT * FROM messages_one_new(?i)", $uid);
        return $user;
    }
    
	/**
     * ������ ����� ������������
     *
     * @param  integer  $uid     id ������������
     *
     * @return array             ������ ��������� (������: uid �����������)
     */	
	function GetUsersInFolders($uid) {
		$res = array();
		$rows = $this->DB->rows("SELECT * FROM messages_folders_users(?i)", $uid);
		foreach ($rows as $row) {
			$res[$row['to_id']][] = $row['folder'];
		}
		return $res;
	}
	

    /**
     * ���������� �����, � ������� ���������� ������ �������
     *
     * @param integer $to_id          id ������������-��������� �������� (�� ������� �������� ������������� �������)
     * @param integer $from_id        id ������������-��������
     *
     * @return array                  ������ �����
     */
    function GetContactFolders($uid1, $uid2, &$error) {
        $DB = new DB;
		$out = array();
		$res = $DB->query("SELECT * FROM messages_folders_user(?i, ?i)", $uid1, $uid2);
		while ($row = pg_fetch_assoc($res)) {
			$out[$row['to_id']][] = $row['folder'];
		}
		return $out;

    }
	
    
	/**
     * ������� ��������� �������� (������ �� ���������� ��� �����-���������)
     *
     * @param integer $user_id        id ������������-��������� ��������
     * @param array $selected         id �������������-���������, ���������� ��������
     *
     * @return string                 ����� ������, � ������ ��������
     */
    function DeleteFromUsers($from_id, $to_id){
		$DB = new DB;
		$DB->query("SELECT messages_dialog_delete(?i, ?i)", $from_id, $to_id);
		return '';
    }
	
	
    /**
     * ��������������� ��������� �������
     *
     * @param integer $user_id        id ������������-�����������
     * @param array $selected         id �������������, ������� ��������� ������������
     *
     * @return string                 ����� ������, � ������ ��������
     */
    function RestoreFromUsers($from_id, $to_id){
        $DB = new DB;
		$DB->query("SELECT messages_dialog_restore(?i, ?i)", $from_id, $to_id);
		return '';
    }
	
	
    /**
     * �������� ���������� ����� ������ ���������
     *
     * @param integer $user_id        id ������������-����������
     * @param boolean $nocache        �� ������ �� ����, ������ ������ ������ � ��
     *
     * @return integer                  ���������� ����� ������ ��������� ��� NULL � ������ ������
     */
	public function GetNewMsgCount($uid, $nocache=FALSE) {
		$DB = new DB;
return (int)$DB->val("SELECT messages_newmsg_count(?)", $uid); // ��� ������������ �� ��������. ���� �� ��������.

		$mem = new memBuff();
        if ( $nocache ) {
            $count = FALSE;
        } else {
            $count = $mem->get("msgsCnt{$uid}");
        }
        if ($count === FALSE) {
			$DB = new DB;
			$count = (int) $DB->val("SELECT messages_newmsg_count(?)", $uid);
			$mem->set("msgsCnt{$uid}", $count, 1800, 'msgsCnt');
		}
		return $count;
	}
	

    /**
     * �������� ���������� ����� ������ ��������� ��� ����������
     *
     * @param integer $user_id        id ������������-����������
     * @param boolean $nocache        �� ������ �� ����, ������ ������ ������ � ��
     *
     * @return integer                  ���������� ����� ������ ��������� ��� NULL � ������ ������
     */
	public function ChatNewMsgCount($uid, $nocache=FALSE) {
		$DB = new DB;
		$mem = new memBuff();
        if ( $nocache ) {
            $count = FALSE;
        } else {
            $count = $mem->get(self::MEMBUFF_CHAT_PREFIX . $uid);
        }
        if ($count === FALSE) {
			$DB = new DB;
			$count = (int) $DB->val("SELECT chat_newmsgs_count(?)", $uid);
			$mem->set(self::MEMBUFF_CHAT_PREFIX . $uid, $count, 1800, self::MEMBUFF_CHAT_PREFIX . $uid);
		}
		return $count;
	}
    
    
    /**
     * �������� ��� ������������� ���������.
     * @see externalApi_Freetray
     *
     * @param integer $uid   ��. ������������.
     * @return array    ������ ���������.
     */
    function getNewMessages($uid) {
        $DB = new DB;
        $rows = $DB->rows("SELECT * FROM messages_moder_newmsg(?i)", $uid);

        return count($rows) ? $rows : null;
    }
    
    /**
     * ���������� ��� ��������� ������������ (�������� � ���������), ������� ���� ���������, 
     * �������� ��� �������� ���������� ����� ������������ ����.
     * (!) �������� �������� ������ ��������.
     * 
     * @param  int $uid UID ������������
     * @param  string $time �����, ����� �������� ����� �������� ���������
     * @return array ������ ���������
     */
    function getMessagesAllSinceDate( $uid = 0, $time = '' ) {
        $DB = new DB;
        return $DB->rows("SELECT * FROM messages_get_all_since_date(?i, ?)", $uid, $time);
    }
    
    /**
     * �������� ��� ������������� ��������� ��� ����������
     *
     * @param integer $uid   ��. ������������.
     * @param boolean $read  ����� �� ������� ������������ ��� ���������
     * @return array    ������ ���������.
     */
    function ChatNewMessages($uid, $read=false) {
        $DB = new DB;
        $rows = $DB->rows("SELECT * FROM chat_newmsgs(?) ORDER BY post_time", $uid);
        if ( $read ) {
            $ids = array();
            foreach ( $rows as $row ) {
                if ( !in_array($row['uid'], $ids) ) {
                    $ids[] = $row['uid'];
                    $DB->query("SELECT chat_dialog_read(?i, ?i)", $uid, $row['uid']);
                }
            }
            $memBuff = new memBuff();
            $memBuff->delete(self::MEMBUFF_CHAT_PREFIX . $uid);
        }
        self::getMessagesAttaches( $rows );
        return $rows;
    }
    
    /**
     * �������� ������ �� �����������
     */
    function getMessageAuthorByUid($uid) {
        global $DB;
        $sql = "SELECT us.login, us.uname, us.usurname
                    FROM users us
                    WHERE us.uid = (?i)";
        $row = $DB->row($sql, $uid);
        return count($row) ? $row : null;
    }


    /**
     * ������������ ���������-�������������� � ������������ ��������� � ������
     *
     * @param integer $login          ����� ������������-����������
     * @param integer $msgid          id ���������
     * @param integer $thid           id ����� � ������
     *
     * @return                        @see messages::Add()

     */
    function SendWarn ($login,$msgid=0,$thid=0) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
        $f_user = new users();
        $f_user->GetUser($login);
        $msg=new blogs();
        if ($thid) {
            $w_msg=$msg->GetThreadMsgInfo($thid,$error,$perm);
            //print_r($w_msg);
           // exit;
        }
        else {
            $w_msg=$msg->GetMsgInfo($msgid,$error,$perm);
        }
        $message = "
$f_user->uname $f_user->usurname!

���������� ������ ������� ����� ��� ����������� ������������:

\"$f_user->uname $f_user->usurname. [$login] ".date("[d.m.Y | H:i]",strtotimeEx($w_msg["post_time"]))."
".reformat($w_msg["title"])."
".reformat($w_msg["msgtext"])."
\"

�� ��������� ��� ������ �� ������ �������� ������������, ����� ���������� ����� ��� ������� ������� � �����.

��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������.

�������� �� ���������, ������� Free-lance.ru.";

        messages::Add(users::GetUid($err,"admin"),$login,$message,'',1);
    }



    /**
     * ������������ ���������-�������������� �� �������� �������� � ������
     *
     * @param integer $msgid          id ���������
     * @param array $w_msg            ���������
     *
     * @return                        @see messages::Add()
     */
    function SendMsgDelWarn ($msgid, $w_msg) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");

        $f_user = new users();
        $f_user->GetUserByUID($w_msg['fromuser_id']);

        $message = "
������������, $f_user->uname $f_user->usurname

���������� ������ ������� ������� ��� �����������:

{$GLOBALS['host']}/blogs/view.php?tr={$w_msg['thread_id']}&openlevel={$msgid}#o{$msgid}
\"
$f_user->uname $f_user->usurname. [$f_user->login] ".date("[d.m.Y | H:i]",strtotimeEx($w_msg["post_time"]))."
".reformat($w_msg["title"])."
".reformat($w_msg["msgtext"])."
\"
�� ��������� ��� ������ �� �������� ������� �������, ����� ���������� ����� ��� ������� ������� � �����.

��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������.

�������� �� ���������, ������� Free-lance.ru.";
        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * ������������ ���������-�������������� �� �������� ������������ �����������.
     * @see TComments::deleteComment() (classes/comments/Comments.php)
     *
     * @param array $w_msg ���������
     *
     * @return @see messages::Add()
     */
    function sendCommentDeleteWarn( $w_msg ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

        $f_user = new users();
        $f_user->GetUserByUID($w_msg['user_id']);

        $message = "
������������, $f_user->uname $f_user->usurname

���������� ������ ������� ������� ��� �����������:

{$w_msg['link']}

$f_user->uname $f_user->usurname. [$f_user->login] ".date("[d.m.Y | H:i]",strtotimeEx($w_msg["post_time"]))."
".reformat($w_msg["msg"])."


�� ��������� ��� ������ �� �������� ������� �������, ����� ���������� ����� ��� ������� ������� � �����.

��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������.

�������� �� ���������, ������� Free-lance.ru.";
        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * ��������� ������ ����������� � ���������� �� �������� �����������
     * @param type $w_msg
     */
    function sendCommuneCommentDeleteWarn($w_msg) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");

        $user_data = commune::GetUserCommuneRel($w_msg['commune_id'], get_uid(0));
        
        $f_user = new users();
        $f_user->GetUserByUID($w_msg['user_id']);
        if ($user_data['is_author']) {
            $deleter = '���������� ����������';
        } elseif ($user_data['is_moderator']) {
            $deleter = '����������� ����������';
        } else {
            $deleter = '����������� �����';
        }
        if (!$user_data['is_author'] && !$user_data['is_moderator']) {
            $attention = "�� ��������� ��� ������ �� �������� ������� �������, ����� ���������� ����� ��� ������� ������� � �����.
    
";
        }
        
        $message = "
������������, $f_user->uname $f_user->usurname

��� ����������� {$w_msg['link']} �� " . date("d.m.Y", strtotime($w_msg["post_time"])) . " ��� ������ $deleter.
    
" . $attention .
"��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������.

������� Free-lance.ru.";
        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * ��������� ������ ����������� � ���������� �� ��������� �����������
     * @param type $w_msg
     */
    function sendCommuneCommentEditedWarn($comment) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");

        $user_data = commune::GetUserCommuneRel($comment['resource_id'], get_uid(0));
        
        $f_user = new users();
        $f_user->GetUserByUID($comment['author']);
        if ($user_data['is_author']) {
            $deleter = '���������� ����������';
        } elseif ($user_data['is_moderator']) {
            $deleter = '����������� ����������';
        } else {
            $deleter = '����������� �����';
        }
        if (!$user_data['is_author'] && !$user_data['is_moderator']) {
            $attention = "�� ��������� ��� ������ �� �������� ������� �������, ����� ���������� ����� ��� ������� ������� � �����.
    
";
        }
        
        $message = "
������������, $f_user->uname $f_user->usurname

��� ����������� {$comment['link']} �� " . date("d.m.Y", strtotime($comment["created_time"])) . " ��� �������������� $deleter.
    
" . $attention .
"��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������.

������� Free-lance.ru.";
        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }


    /**
     * ������������ ���������-�������������� � ������������ �������
     *
     * @param integer $login          ����� ������������-����������
     * @param integer $prjid          id �������
     *
     * @return                        @see messages::Add()
     */
    function SendProjectWarn ($login, $prjid=0) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
        $f_user = new users();
        $f_user->GetUser($login);
        $obj_project = new projects();
        $project = $obj_project->GetPrjCust($prjid);
        $message = "
$f_user->uname $f_user->usurname!

���������� ������ ������� ����� ��� ������ ������������:

\"$f_user->uname $f_user->usurname. [$login] ".date("[d.m.Y | H:i]",strtotimeEx($project["post_date"]))."
".reformat($project["name"])."
".reformat($project["descr"])."
\"
�� ��������� ��� ������ �� ����������� �������� ��������, ����� ���������� ����� ��� ������� ������� � �����.

��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������.

�������� �� ���������, ������� Free-lance.ru.";

        messages::Add(users::GetUid($err,"admin"),$login,$message,'',1);
    }

    
    /**
     * ��������� � ���������� ����� � ������
     *
     * @param integer  $thread_id   id �����
     * @param string   $reason      �������
     *
     * @return                      @see messages::Add()
     */    
    function SendBlockedThread ($thread_id=0, $reason) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $msg=new blogs();
        $w_msg=$msg->GetThreadMsgInfo($thread_id,$error,$perm);
        $f_user = new users();
        $f_user->GetUserByUID($w_msg['fromuser_id']);
        $message = "
$f_user->uname $f_user->usurname!

���������� ������ ������� ����� ��� ����".((trim($w_msg["title"])!="")?" &laquo;".($w_msg["title"])."&raquo;":"")." �� ".date("d.m.Y",strtotimeEx($w_msg["post_time"]))." ������������:

�������: ".($reason)."

�� ��������� ��� ������ �� ��������� �������� ������, ����� ���������� ����� ��� ������� ������� � �����.

��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������.

�������� �� ���������, ������� Free-lance.ru.";

        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }

    /**
     * ��������� � ���������� ������ � �����������
     * 
     * @param array $topic ������ ���������� � ������ @see commune::GetTopMessageByAnyOther
     * @param string $reason �����������. ������� ����������
     */
    function SendBlockedCommuneTheme( $topic = array(), $reason = '' ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
        
        //$topic = commune::GetTopMessageByAnyOther( $msg_id, null, commune::MOD_ADMIN );
        
        if ( $topic ) {
            $message = "
{$topic['user_uname']} {$topic['user_usurname']}!";
            
            if ( $topic['is_blocked_c'] == 't' ) {
            	// ����������� ������ ����������
                $message .= '
                
���������� ���������� &laquo;'.$topic['commune_name'].'&raquo; ����� ���� ���������'.((trim($topic["title"])!="")?" &laquo;".($topic["title"])."&raquo;":"")." �� ".date("d.m.Y",strtotimeEx($topic["created_time"])).' ������������.

�� ��������� ��� ������ �� ��������� �������� ���������, ����� ���������� ����� ��� ������� ������� � ����������.';
            }
            else {
                // ����������� ������ ����� Free-lance.ru
            	$message .= '

���������� ������ ������� ����� ���� ���������'.((trim($topic["title"])!="")?" &laquo;".($topic["title"])." &raquo;":"")." �� ".date("d.m.Y",strtotimeEx($topic["created_time"])).' � ���������� &laquo;'.$topic['commune_name'].'&raquo; ������������:

�������: '. $reason.'

�� ��������� ��� ������ �� ��������� �������� ���������, ����� ���������� ����� ��� ������� ������� � �����.';
            }
            
            $message .= '

��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������.

������� Free-lance.ru.';
            
        	messages::Add( users::GetUid($err, 'admin'), $topic['user_login'], $message, '', 1 );
        }
        
    }
    
    /**
     * ��������� �� �������� ������ � �����������
     * 
     * @param array $topic ������ ���������� � ������ @see commune::GetTopMessageByAnyOther
     * @param string $deleter ��� ������� 'admin' - ����� ����������, 'moder' - ���������
     */
    function SendDeletedCommuneTheme( $topic = array(), $deleter = 'admin' ) {
        if ( $topic ) {
            $whoDelete = $deleter === 'admin' ? '���������� ����������' : ($deleter === 'moder' ? '����������� ����������' : '�����������');
            
            $message = '������������, '. $topic['user_uname'] . ' ' . $topic['user_usurname'] . '
            
���� ��������� '. ( trim($topic["title"]) ? '&laquo;' . ($topic["title"]) . '&raquo;' : '' ) . ' �� ' . date( 'd.m.Y', strtotimeEx($topic['created_time']) ) .' � ���������� &laquo;'.$topic['commune_name'].'&raquo; ���� ������� ' . $whoDelete . '.
' . ($deleter === 'site-moder' ? '����������� �� �������� <a href="https://feedback.fl.ru/article/details/id/161">������� �����</a>, � ��������� ������ �� ����� ��������� ������������� ��� �������.
    ' : '') . '

��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������. 

������� Free-lance.ru
';
                
                messages::Add( users::GetUid($err, 'admin'), $topic['user_login'], $message, '', 1 );
        }
    }
    
    
    
    
    
    
    /**
     * ��������� � ���������� ������� ������
     * 
     * @param array $service
     * @param string $reason
     * @return mix
     */
    function SendBlockedTServices ($service = array(), $reason)
    {
        if(!count($service)) return false;
        
        $sName   = $service["title"] ? ' &laquo;'. $service["title"] .'&raquo;' : '';
        $sUser   = $service["uname"] . ' ' . $service["usurname"];

        $message = "
$sUser!

��������, �� ���������� ����� �������� ������ ���� ������� ������$sName

������������: ".($reason)."


����������, �������� ���������� ������ � ������������ � �������������. ����� ����� �� ������ ����� ������������ ������ � ������ �� �������.

��� ��������� ���� ������� ������������� � �� ������� ������.

������ �������� �������! ������� Free-lance.ru.";   
                            
        return messages::Add(users::GetUid($err,"admin"), $service['login'], $message,'',1);
    }






    /**
     * ��������� � ���������� �������
     *
     * @param integer  $project_id   id �������
     * @param string   $reason       �������
     *
     * @return                       @see messages::Add()
     */    
    function SendBlockedProject ($project_id=0, $reason) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $pr=new projects();
        $prj = $pr->GetPrjCust($project_id);
        $prj_url = getFriendlyURL('project', $project_id);
        $f_user = new users();
        $f_user->GetUserByUID($prj['user_id']);
        $name = $f_user->uname || $f_user->usurname ? trim($f_user->uname . ' ' . $f_user->usurname) : $f_user->login;
        $message = "
������������, $name!

���������� ��� �� ��, ��� ��������������� ������ FL.ru ��� ������ �����������. 
� ���������, ��� ������ �<a href='$prj_url'>{$prj["name"]}</a>� ��� ������������. 
$reason

<a href='http://feedback.fl.ru/'>���������� � ������ ���������</a> (���� ������ ������������ ��������)
---
� ���������, ������� FL.ru";

        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * ��������� � ������������� �������
     *
     * @param integer  $project_id   id �������
     * @return                       @see messages::Add()
     */    
    function SendUnBlockedProject ($project_id=0) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $pr=new projects();
        $prj = $pr->GetPrjCust($project_id);
        $f_user = new users();
        $f_user->GetUserByUID($prj['user_id']);
        
        $message = Template::render(
            $_SERVER['DOCUMENT_ROOT'] . self::TPL_PATH . 'send_unblocked_project.tpl.php', 
            array(
                'name'  => $f_user->uname,
                'surname' => $f_user->usurname,
                'project_name' => $prj["name"]
            )
        );

        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * ��������� � ���������� ����������� �� �������
     *
     * @param  integer $offer_id ID �����������
     * @param  integer $user_id UID ������������
     * @param  integer $project_id id �������
     * @param  string $reason �������
     * @return @see messages::Add()
     */    
    function SendBlockedProjectOffer( $offer_id = 0, $user_id = 0, $project_id = 0, $reason = '' ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $pr     = new projects();
        $prj    = $pr->GetPrjCust( $project_id );
        $f_user = new users();
        $f_user->GetUserByUID( $user_id );
        
        $message = "
$f_user->uname $f_user->usurname!

���������� ������ ������� ����� ���� ���������� �� ������� &laquo;".($prj["name"])."&raquo; ������������:

�������: ".($reason)."

�� ��������� ��� ������ �� ����������� �������� �����������, ����� ���������� ����� ��� ������� ������� � �����.

��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������.

�������� �� ���������, ������� Free-lance.ru.";

        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * ��������� � ���������� �������
     * 
     * @param  integer $portfolio_id ID ������ � ���������
     * @param  string $reason �������
     * @return @see messages::Add()
     */    
    function SendBlockedPortfolio( $portfolio_id = 0, $reason = '' ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/portfolio.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $portfolio = portfolio::GetPrj( $portfolio_id );
        $f_user    = new users();
        $f_user->GetUserByUID( $portfolio['user_id'] );
        
        $sName   = $portfolio["name"] ? ' &laquo;'. $portfolio["name"] .'&raquo;' : '';
        $link    = getAbsUrl( '/users/'. $f_user->login .'/viewproj.php?prjid='. $portfolio['id'] );
        $message = "
$f_user->uname $f_user->usurname!

���������� ������ ������� ����� ���� ������$sName � ��������� ������������:

�������: ".($reason)."

$link

�� ��������� ��� ������ �� ����������� �������� ��������, ����� ���������� ����� ��� ������� ������� � �����.

��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������.

�������� �� ���������, ������� Free-lance.ru.";

        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * ��������� � ���������� ����������� � ����������� �� �������
     * 
     * @param  integer $dialogue_id ID ����������
     * @param  string $reason �������
     * @return @see messages::Add()
     */    
    function SendBlockedDialogue( $dialogue_id = 0, $reason = '' ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/projects_offers_dialogue.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $dialogue    = projects_offers_dialogue::getDialogueMessageById( $dialogue_id );
        $f_user    = new users();
        $f_user->GetUserByUID( $dialogue['user_id'] );
        
        $sName   = $dialogue["project_name"] ? ' &laquo;'. $dialogue["project_name"] .'&raquo;' : '';
        $link    = getAbsUrl( getFriendlyURL('project', $dialogue['project_id']) );
        $message = "
$f_user->uname $f_user->usurname!

���������� ������ ������� ����� ��� ����������� � ���������� �� �������$sName ������������:

�������: ".($reason)."

$link

�� ��������� ��� ������ �� ����������� �������� ������������, ����� ���������� ����� ��� ������� ������� � �����.

��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������.

�������� �� ���������, ������� Free-lance.ru.";

        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * ��������� � ������ ��������������
     *
     * @param integer  $uid          ����
     * @param string   $reason       �������
     * @param string   $link         ���
     *
     * @return                       @see messages::Add()
     */    
    function SendUserWarn ($uid=0, $reason, $link) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $f_user = new users();
        $f_user->GetUserByUID($uid);
        
        $rules = WDCPREFIX."/about/documents/appendix_2_regulations.pdf";
        
        $message = "
������������, $f_user->uname $f_user->usurname!

".strip_tags($reason, "<BR>")."

����������, ������ ���������� <a href='".$rules."'>������� �����</a>.

---
� ���������, ������� FL.ru";
        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
	
    /**
     * ��������� � ��������� ������
     *
     * @param integer  $uid          ����
     * @param integer  $project_url  �� �������
     * @param integer  $project_name �������� �������
     * @param string   $text         ����� ������
     * @param string   $link         ������ �� ���� � userEcho
     *
     * @return                       @see messages::Add()
     */    
    public static function sendProjectComplain($uid=0, $project_url, $project_name, $text, $link)
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $f_user = new users();
        $f_user->GetUserByUID($uid);
        
        $message = "
������������, $f_user->uname $f_user->usurname!
�� �������� �� ��� ������ �� ������ <a href='".$project_url."'>{$project_name}</a> � ������������: 
".strip_tags($text)."

�� ������ ������������ ������ {$link}
����������, ������ ���������� ������ �� ������ � �������������� ������. 

---
� ���������, ������� FL.ru";
        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * ���������� ����������� �� �������� ��������� � ����� ("��� ��������").
     * 
     * @param  int $from_uid UID �����������
     * @param  int $to_uid UID ����������
     * @param  string $msg ����� ���������
     * @return bool true - �����, false - ������
     */
    function messageDeletedNotification( $from_uid = 0, $to_uid = 0, $msg = '' ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $to = new users;
		$to->GetUserByUID( $to_uid );
        
        $from = new users;
		$from->GetUserByUID( $from_uid );
        
        $message = '������������, '. $from->uname .' '. $from->usurname .'

���������� ������ ������� ������� ���� ��������� � ������� &laquo;�������� ������&raquo;

'. $to->uname .' '. $to->usurname .' ['. $to->login .']
'. $msg .'

�� ��������� ��� ������ �� �������� ������� �������, ����� ���������� ����� ��� ������� ������� � �����. 

��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������. 

�������� �� ���������, ������� Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $from->login, $message, '', 1 );
    }
    
    /**
     * ���������� ����������� �� ��������� ������� ��������� � ����� ("��� ��������").
     * 
     * @param  int $from_uid UID �����������
     * @param  int $to_uid UID ����������
     * @param  string $msg ����� ���������
     * @param  string $reason ������� ��������������
     * @return bool true - �����, false - ������
     */
    function messageModifiedNotification( $from_uid = 0, $to_uid = 0, $msg = '', $reason = '' ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $to = new users;
		$to->GetUserByUID( $to_uid );
        
        $from = new users;
		$from->GetUserByUID( $from_uid );
        
        $sRason    = $reason ? "\n\n�������: ". $reason : '';
        $sFeedback = "<a href=\"{$GLOBALS['host']}/about/feedback/\" target=\"_blank\">������ ���������</a>";        
        $message   = '������������, '. $from->uname .' '. $from->usurname .'

���������� ������ ������� ��������������� ���� ��������� � ������� &laquo;�������� ������&raquo;

'. $to->uname .' '. $to->usurname .' ['. $to->login .']
'. $msg . $sRason . '

�� ������ ���������� � '. $sFeedback .'.

�������� �� ���������, ������� Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $from->login, $message, '', 1 );
    }
    
    /**
     * ���������� ����������� �� ��������� ������� ��������� � ����� ("��� ��������").
     * 
     * @param  int $from_uid UID �����������
     * @param  string $ucolumn �������� ����
     * @param  string $utable �������� ������������� �������
     * @param  string $reason ������� ��������������
     * @return bool true - �����, false - ������
     */
    function profileModifiedNotification( $from_uid = 0, $ucolumn = '', $utable = '', $reason = '' ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        switch ( $ucolumn ) {
            case 'uname': $sColumn = '���'; break;
            case 'usurname': $sColumn = '�������'; break;
            case 'pname': $sColumn = '��������� ��������'; break;
            case 'spec_text': $sColumn = '��������� � ������� � ���������'; break;
            case 'resume_file': $sColumn = '���� ������'; break;
            case 'resume':
                if ( $utable == 'freelancer' ) {
                    $sColumn = '����� ������';
                }
                else {
                    $sColumn = '�������������� ����������';
                }
                break;
            case 'konk': $sColumn = '������� � ��������� � �������'; break;
            case 'company': $sColumn = '� ��������'; break;
            case 'status_text': $sColumn = '������'; break;
            case 'compname': $sColumn = '��������'; break;
            default: $sColumn = ''; break;
        }
        
        $to = new users;
		$to->GetUserByUID( $to_uid );
        
        $from = new users;
		$from->GetUserByUID( $from_uid );
        
        $sRason    = $reason ? "\n\n�������: ". $reason : '';
        $sFeedback = str_replace( '//', '/{������ ���������}/', $GLOBALS['host'] . '/about/feedback/' );
        $message   = '������������, '. $from->uname .' '. $from->usurname .'

���������� ������ ������� ��������������� ����'. ($sColumn ? ' &laquo;'.$sColumn.'&raquo;' : '') .' � ����� �������.'. $sRason . '

�� ������ ���������� � '. $sFeedback .'.

�������� �� ���������, ������� Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $from->login, $message, '', 1 );
    }
    
    /**
     * ��������� �� ��������� ������ � ���������
     * 
     * @param  array $portfolio ������ � ���������
     * @param  object $f_user ������ � ���������
     * @param  string $reason �������
     * @return @see messages::Add()
     */    
    function portfolioModifiedNotification( $portfolio = 0, $f_user = null, $reason = '' ) {
        $sFeedback = str_replace( '//', '/{������ ���������}/', $GLOBALS['host'] . '/about/feedback/' );
        $sName   = $portfolio["name"] ? ' &laquo;'. $portfolio["name"] .'&raquo;' : '';
        $link    = getAbsUrl( '/users/'. $f_user->login .'/viewproj.php?prjid='. $portfolio['id'] );
        $sRason  = $reason ? "\n\n�������: ". $reason : '';
        $message = "
$f_user->uname $f_user->usurname!

���������� ������ ������� ��������������� ���� ������$sName � ���������:$sRason

$link

�� ������ ���������� � $sFeedback.

�������� �� ���������, ������� Free-lance.ru";

        messages::Add(users::GetUid($err,"admin"),$f_user->login,$message,'',1);
    }
    
    /**
     * ��������� �� ��������� ����� (����/�����������)
     * 
     * @param  int $rec_type 1- ����, 2 - �����������
     * @param  string $title ��������� �����
     * @param  string $post_time ���� �������� �����
     * @param  string $uname ��� ������ ����� 
     * @param  string $usurname ������� ������ ����� 
     * @param  string $login ����� ������ ����� 
     * @param  string $reason ������� ���������
     * @return @see messages::Add()
     */
    function blogModifiedNotification( $rec_type = 0, $title = '', $post_time = '', $uname = '', $usurname = '', $login = '', $reason = '' ) {
        $sFeedback = str_replace( '//', '/{������ ���������}/', $GLOBALS['host'] . '/about/feedback/' );
        $sKind   = $rec_type == 1 ? '���� ��������� � �����' : '��� ����������� � �����';
        $sRason  = $reason ? "\n\n�������: ". $reason : '';
        $message = "
$uname $usurname!

���������� ������ ������� ��������������� $sKind".((trim($title)!="")?" &laquo;".($title)."&raquo;":"")." �� ".date("d.m.Y",strtotimeEx($post_time)).":" . $sRason . "

�� ������ ���������� � $sFeedback.

�������� �� ���������, ������� Free-lance.ru.";

        messages::Add(users::GetUid($err,"admin"),$login,$message,'',1);
    }
    
    /**
     * ��������� �� ��������� �����/����������� � �����������
     * 
     * @param  int $rec_id id ������
     * @param  int $rec_type 1- ����, 2 - �����������
     * @param  string $login ����� ������ ����� 
     * @param  string $uname ��� ������ ����� 
     * @param  string $usurname ������� ������ ����� 
     * @param  string $reason ������� ���������
     * @param  int $post_id id ����� ��� �����������
     * @return @see messages::Add()
     */
    function communityModifiedNotification( $rec_id = 0, $rec_type = 0, $login = '', $uname = '', $usurname = '', $reason = '', $post_id = 0 ) {
        if ( $rec_type == 1 ) {
            $sLink = getAbsUrl( getFriendlyURL('commune', $rec_id) );
        }
        else {
            $sLink  = getAbsUrl( getFriendlyURL( 'commune', $post_id) ) . '#c_' . $rec_id;
        }
        
        $sFeedback = str_replace( '//', '/{������ ���������}/', $GLOBALS['host'] . '/about/feedback/' );
        $sKind     = $rec_type == 1 ? '��� ���� � �����������' : '��� ����������� � �����������';
        $sRason    = $reason ? "\n\n�������: ". $reason : '';
        $message   = "
$uname $usurname!

���������� ������ ������� ��������������� $sKind:
$sLink $sRason

�� ������ ���������� � $sFeedback.

������� Free-lance.ru.";
        
        messages::Add( users::GetUid($err, 'admin'), $login, $message, '', 1 );
    }
    
    /**
     * ���������� ������ ��������� � ��� ��� ����� � ���������� ��������������
     * @param array $comm ������ ���������� �� commune::GetCommune
     * @param array $post ������ ���������� �� commune::GetMessage
     * @param string $editor ��� �������������� ����� ('comm-author', 'comm-moder', 'site-moder')
     */
    function communityPostModifiedNotification($comm, $post, $editor = 'site-moder') {
        $createDate = date('d.m.Y', strtotime($post['created_time']));
        switch ($editor) {
            case 'comm-author':
                $editorText = '���������� ����������';
                break;
            case 'comm-moder':
                $editorText = '����������� ����������';
                break;
            case 'site-moder':
                $editorText = '����������� �����';
                break;
        }        
        $sFeedback = str_replace( '//', '/{������ ���������}/', $GLOBALS['host'] . '/about/feedback/' );
        
        $message   = "
������������, {$post['user_uname']} {$post['user_usurname']} [{$post['user_login']}].

���� ���������" . ($post['title'] ? " �" . $post['title'] . "�" : '') . " �� {$createDate} � ���������� �{$comm['name']}� ���� ��������������� $editorText.

��� ��������� ���� ������� �������������, �������� �� ���� �� �����.

�� ������ ���������� � $sFeedback.

������� Free-lance.ru.";
        
        messages::Add( users::GetUid($err, 'admin'), $post['user_login'], $message, '', 1 );
    }
    
    /**
     * ��������� �� ��������� ����������� � �������
     * 
     * @param  int $rec_id id ������
     * @param  string $login ����� ������ ����� 
     * @param  string $uname ��� ������ ����� 
     * @param  string $usurname ������� ������ ����� 
     * @param  string $reason ������� ���������
     * @param  int $art_id id ������
     * @return @see messages::Add()
     */
    function artComModifiedNotification( $rec_id = 0, $login = '', $uname = '', $usurname = '', $reason = '', $art_id = 0 ) {
        $sLink     = getAbsUrl( getFriendlyURL( 'article', $art_id) ) . '#c_' . $rec_id;
        $sFeedback = str_replace( '//', '/{������ ���������}/', $GLOBALS['host'] . '/about/feedback/' );
        $sRason    = $reason ? "\n\n�������: ". $reason : '';
        $message   = "
$uname $usurname!

���������� ������ ������� ��������������� ��� ����������� � �������:
$sLink $sRason

�� ������ ���������� � $sFeedback.

�������� �� ���������, ������� Free-lance.ru.";
        
        messages::Add( users::GetUid($err, 'admin'), $login, $message, '', 1 );
    }
    
    /**
     * ��������� �� ��������� �������/��������
     * 
     * @param  int $rec_id id ������
     * @param  int $rec_type 7 - �������, �� 7 - ������
     * @param  string $login ����� ������ ����� 
     * @param  string $uname ��� ������ ����� 
     * @param  string $usurname ������� ������ ����� 
     * @param  string $reason ������� ���������
     * @return @see messages::Add()
     */
    function projectsModifiedNotification( $rec_id = 0, $rec_type = 0, $login = '', $uname = '', $usurname = '', $reason = '' ) {
        $sFeedback = str_replace( '//', '/{������ ���������}/', $GLOBALS['host'] . '/about/feedback/' );
        $sKind     = $rec_type == 7 ? '�������' : '������';
        $sLink     = getAbsUrl( getFriendlyURL('project', $rec_id) );
        $sRason    = $reason ? "\n\n�������: ". $reason : '';
        $message   = "
$uname $usurname!

���������� ������ ������� ��������������� ��� $sKind:
$sLink $sRason

�� ������ ���������� � $sFeedback.

�������� �� ���������, ������� Free-lance.ru.";
        
        messages::Add( users::GetUid($err, 'admin'), $login, $message, '', 1 );
    }
    
    /**
     * ���������� ����������� �� ��������� ������� ����������� �� �������
     * 
     * @param  int $from_uid UID �����������
     * @param  int $project_id ID �������
     * @param  string $reason ������� ��������������
     * @return bool true - �����, false - ������
     */
    function prjOfferModifiedNotification( $from_uid = 0, $project_id = 0, $reason = '' ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
        
        $from = new users;
		$from->GetUserByUID( $from_uid );
        
        $pr        = new projects();
        $prj       = $pr->GetPrjCust( $project_id );
        $sName     = $prj['name'] ? ' &laquo;'. $prj['name'] .'&raquo;' : '';
        $sLink     = getAbsUrl( getFriendlyURL('project', $project_id) );
        $sRason    = $reason ? "\n\n�������: ". $reason : '';
        $sFeedback = str_replace( '//', '/{������ ���������}/', $GLOBALS['host'] . '/about/feedback/' );
        $message   = '������������, '. $from->uname .' '. $from->usurname .'

���������� ������ ������� ��������������� ���� ����������� � �������'. $sName .'

'. $sLink . $sRason . '

�� ������ ���������� � '. $sFeedback .'.

�������� �� ���������, ������� Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $from->login, $message, '', 1 );
    }
    
    /**
     * ���������� ����������� �� ��������� ������� ����������� �� ��������
     * 
     * @param  int $rec_id id �����������
     * @param  int $prj_id id ��������
     * @param  string $login ����� ������ ����� 
     * @param  string $uname ��� ������ ����� 
     * @param  string $usurname ������� ������ ����� 
     * @param  string $reason ������� ���������
     * @return @see messages::Add()
     */
    function contestOfferModifiedNotification( $rec_id = 0, $prj_id = 0, $login = '', $uname = '', $usurname = '', $reason = '' ) {
        $sFeedback = str_replace( '//', '/{������ ���������}/', $GLOBALS['host'] . '/about/feedback/' );
        $sLink     = getAbsUrl( getFriendlyURL('project', $prj_id) ) . '#c-offer-' . $rec_id;
        $sRason    = $reason ? "\n\n�������: ". $reason : '';
        $message   = "
$uname $usurname!

���������� ������ ������� ��������������� ���� ������ � ��������:
$sLink $sRason

�� ������ ���������� � $sFeedback.

�������� �� ���������, ������� Free-lance.ru.";
        
        messages::Add( users::GetUid($err, 'admin'), $login, $message, '', 1 );
    }
    
    /**
     * ���������� ����������� �� ��������� ������� ������� � �������
     * 
     * @param  int $from_uid UID �����������
     * @param  int $project_id ID �������
     * @param  string $msg ����� ���������
     * @param  string $reason ������� ��������������
     * @return bool true - �����, false - ������
     */
    function prjDialogModifiedNotification( $from_uid = 0, $project_id = 0, $msg = '', $reason = '' ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
        
        $from = new users;
		$from->GetUserByUID( $from_uid );
        
        $pr        = new projects();
        $prj       = $pr->GetPrjCust( $project_id );
        $sName     = $prj['name'] ? ' &laquo;'. $prj['name'] .'&raquo;' : '';
        $sLink     = getAbsUrl( getFriendlyURL('project', $project_id) );
        $sRason    = $reason ? "\n\n�������: ". $reason : '';
        $sFeedback = str_replace( '//', '/{������ ���������}/', $GLOBALS['host'] . '/about/feedback/' );
        $message   = '������������, '. $from->uname .' '. $from->usurname .'

���������� ������ ������� ��������������� ��� ����������� � ���������� �� �������'. $sName .'

'. $sLink . '
'. $msg . $sRason . '

�� ������ ���������� � '. $sFeedback .'.

�������� �� ���������, ������� Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $from->login, $message, '', 1 );
    }
        
    /**
     * ���������� ����������� �� ��������� ������� ����������� � ������ � ��������
     * 
     * @param  int $pid ID ��������
     * @param  int $oid ID ������ � ��������
     * @param  int $cid ID �����������
     * @param  string $login ����� ������ ����������� 
     * @param  string $uname ��� ������ ����������� 
     * @param  string $usurname ������� ������ ����������� 
     * @param  string $reason ������� ��������������
     * @return bool true - �����, false - ������
     */
    function contestComModifiedNotification( $pid = 0, $oid = 0, $cid = 0, $login = '', $uname = '', $usurname = '', $reason = '' ) {
        $sLink     = getAbsUrl( $GLOBALS['host'] . getFriendlyURL( 'project', $pid ) . '?comm='. $cid .'#comment-'. $cid );
        $sRason    = $reason ? "\n\n�������: ". $reason : '';
        $sFeedback = str_replace( '//', '/{������ ���������}/', $GLOBALS['host'] . '/about/feedback/' );
        $message   = '������������, '. $uname .' '. $usurname .'

���������� ������ ������� ��������������� ��� ����������� � ������ � ��������:

'. $sLink . $sRason . '

�� ������ ���������� � '. $sFeedback .'.

�������� �� ���������, ������� Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $login, $message, '', 1 );
    }
    
    /**
     * ���������� ����������� �� ��������� ������� ����������� ����������� ������
     * 
     * @param  string $msg ����� �����������
     * @param  string $login ����� ������ ����������� 
     * @param  string $uname ��� ������ ����������� 
     * @param  string $usurname ������� ������ ����������� 
     * @param  string $reason ������� ��������������
     * @return bool true - �����, false - ������
     */
    function sdelauModifiedNotification( $msg = '', $login = '', $uname = '', $usurname = '', $reason = '' ) {
        $sRason    = $reason ? "\n\n�������: ". $reason : '';
        $sFeedback = str_replace( '//', '/{������ ���������}/', $GLOBALS['host'] . '/about/feedback/' );
        $message   = '������������, '. $uname .' '. $usurname .'

���������� ������ ������� ��������������� ���� ���������� � ������������ �����������:

'. $msg . $sRason . '

�� ������ ���������� � '. $sFeedback .'.

�������� �� ���������, ������� Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $login, $message, '', 1 );
    }
    
   
    /**
     * ���������� ����������� �� ��������� ������� ������� ���� �� ������� ��� � ��������
     * 
     * @param  string $login ����� ������ ����������� 
     * @param  string $uname ��� ������ ����������� 
     * @param  string $usurname ������� ������ ����������� 
     * @param  string $reason ������� ��������������
     * @return bool true - �����, false - ������
     */
    function carouselModifiedNotification( $login = '', $uname = '', $usurname = '', $reason = '' ) {       
        $sRason    = $reason ? "\n\n�������: ". $reason : '';
        $sFeedback = str_replace( '//', '/{������ ���������}/', $GLOBALS['host'] . '/about/feedback/' );
        $message   = '������������, '. $uname .' '. $usurname .'

���������� ������ ������� ��������������� ���� ������� ���������� �� &laquo;��������&raquo;:'. $sRason . '

�� ������ ���������� � '. $sFeedback .'.

�������� �� ���������, ������� Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $login, $message, '', 1 );
    }
    
    /**
     * ���������� ����������� �� ��������� ������� ��������� � ����� ("��� ��������").
     * 
     * @param  int $from_uid UID �����������
     * @param  int $prof_id ID ���������
     * @param  string $reason ������� ��������������
     * @return bool true - �����, false - ������
     */
    function portfChoiceModifiedNotification( $from_uid = 0, $prof_id = 0, $reason = '' ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php' );
        
        $from = new users;
		$from->GetUserByUID( $from_uid );
        
        $prj       = professions::GetProfDesc( $from_uid, $prof_id );
        $sLink     = getAbsUrl( '/users/'. $from->login .'/setup/#prof'. $prof_id );
        $sRason    = $reason ? "\n\n�������: ". $reason : '';
        $sFeedback = str_replace( '//', '/{������ ���������}/', $GLOBALS['host'] . '/about/feedback/' );
        $message   = '������������, '. $from->uname .' '. $from->usurname .'

���������� ������ ������� ��������������� ���� ��������� � ������� &laquo;'. $prj['profname'] .'&raquo;

'. $sLink . $sRason . '

�� ������ ���������� � '. $sFeedback .'.

�������� �� ���������, ������� Free-lance.ru
';
        
        messages::Add( users::GetUid($err,"admin"), $from->login, $message, '', 1 );
    }
    
    /**
     * ���������� ����������� �� �������� ����������� � �������
     * 
     * @param array $offer ���������� � �����������
     */
    function offerDeletedNotification( $offer = array() ) {
        if ( $offer ) {
            $message = '������������, '. $offer['uname'] .' '. $offer['usurname'] .'

���������� ������ ������� ������� ���� ����������� � ������� &laquo;'. $offer['name'] .'&raquo;
'. $GLOBALS['host'] . getFriendlyURL('project', $offer['project_id']) .'

�� ��������� ��� ������ �� �������� ������� �������, ����� ���������� ����� ��� ������� ������� � �����. 

��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������. 

�������� �� ���������, ������� Free-lance.ru
';
            
            messages::Add( users::GetUid($err,"admin"), $offer['login'], $message, '', 1 );
        }
    }
    
    /**
     * ���������� ����������� �� �������� ����������� � ����������� � �������
     * 
     * @param array $dialogue ���������� � �����������
     */
    function dialogueMessageDeletedNotification( $dialogue = array() ) {
        if ( $dialogue ) {
            $message = '������������, '. $dialogue['uname'] .' '. $dialogue['usurname'] .'

���������� ������ ������� ������� ���� ��������� � ����������� �� ������� &laquo;'. $dialogue['name'] .'&raquo;
'. $GLOBALS['host'] . getFriendlyURL('project', $dialogue['project_id']) .'

�� ��������� ��� ������ �� �������� ������� �������, ����� ���������� ����� ��� ������� ������� � �����. 

��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������. 

�������� �� ���������, ������� Free-lance.ru
';
            
            messages::Add( users::GetUid($err,"admin"), $dialogue['login'], $message, '', 1 );
        }
    }
    
    function portfolioDeletedNotification( $name = '', $surname = '', $login = '' ) {
        if ( $login ) {
            $message = '������������, '. $name .' '. $surname .'

���������� ������ ������� ������� ���� ������ � ���������.

�� ��������� ��� ������ �� �������� ������� �������, ����� ���������� ����� ��� ������� ������� � �����. 

��� ��������� ���� ������� �������������, � ����� �� ���� �� ����� ���������������. 

�������� �� ���������, ������� Free-lance.ru
';
            
            messages::Add( users::GetUid($err,"admin"), $login, $message, '', 1 );
        }
    }
    
    /**
     * ���������� ��������� ��� ������� �������� �� ��� ������������
     * @param array $partnersLogins ������ � �������� ����������� �����������
     * @param string $login ����� ���������������� ������������
     */
    function yourSbrPartnerIsBanned(array $partnersLogins, $login) {
        if (!is_array($partnersLogins)) {
            return;
        }
        $message = 
'Free-lance.ru: ������������ ������������, � ������� �� ��������� ����������� ������

������������!

�������� ���, ��� ������������ [' . $login . '], � ������� �� ��������� ����� ������ ����������� ������, ��� ������������ �������������� �����. ��� ���������� ������� ������ � ������ ������������� ���������� � ��������.

��������� ���������� �� ���������� �������������� ����� ����������� �������� ��������� � ��������������� ������ ������� ��������.

�������� ������!
������� Free-lance.ru';

        foreach ($partnersLogins as $targetLogin) {
            messages::Add(users::GetUid($err, "admin"), $targetLogin, $message, '', 1);
        }
        
    }


    /**
     * ���������� ��������� �� ������������� �� �������, ��� �����.
     *
     * @param string $message ����� ���������
     */
    function AdminMsgFromTempl($message) {
        $tpl = "
������������.

������� \"Free-lance.ru\" ���������� ��� �� ���� ������� ����������� � ����� ������ �������.


{$message}

--
������� \"Free-lance.ru\"
info@free-lance.ru
www.free-lance.ru
        ";
        return $tpl;
    }
	
    /**
     * ���������� ������ �����, � ������� ���� �������� �������� � ������ ����������.
     * 
     * @param  string $sUid UID ������������
     * @return array
     */
	function pmAutoFoldersGetYears( $sUid = '' ) {
	    $DBproxy = new DB;
	    return $DBproxy->col( 'SELECT * FROM mess_pm_folder_years(?i)', $sUid );
	}
	
	/**
	 * ���������� �������������� ����� ��� �������� �������� ������ ����������.
	 * 
	 * @param  string $sUid UID ������������
	 * @param  string $sYear �� ����� ���
	 * @param  int $nLimit
	 * @param  int $nOffset
	 * @return array
	 */
	function pmAutoFolders( $sUid = '', $sYear = '', $nLimit = 0, $nOffset = 0 ) {
	    $DBproxy = new DB;
	    return $DBproxy->rows( 'SELECT * FROM mess_pm_folders_get(?i, ?i, ?i, ?i)', $sUid, $sYear, $nLimit, $nOffset );
	}
	
	/**
	 * ���������� ���������� �������������� ����� ��� �������� �������� ������ ����������.
	 *
	 * @param  string $sUid UID ������������
	 * @param  string $sYear �� ����� ���
	 * @return int
	 */
	function pmAutoFoldersCount( $sUid = '', $sYear = '' ) {
	    $DBproxy = new DB;
	    return $DBproxy->val( 'SELECT mess_pm_folders_count(?i, ?i)', $sUid, $sYear );
	}
	
	/**
	 * ���������� �������������� ����� ������������ �� ID
	 * 
	 * @param  string $sUid UID ������������
	 * @param  string $sFolderId ID �����
	 * @return array
	 */
	function pmAutoFolderGetById( $sUid = '', $sFolderId = '' ) {
	    $DBproxy = new DB;
	    return $DBproxy->row( 'SELECT * FROM mess_pm_folder_get(?i, ?i)', $sUid, $sFolderId );
	}
	
	/**
	 * ��������������� �������������� ����� ������������
	 * 
	 * @param  string $sUid UID ������������
	 * @param  string $sFolderId ID �����
	 * @param  string $sName ����� �������� �����
	 * @return string ������ ������ - �����, ��������� �� ������ - ������.
	 */
    function pmAutoFolderRename( $sUid, $sFolderId, $sName = '' ) {
        $sError = '';
        
        if ( $sName ) {
            $DBproxy = new DB;
            $DBproxy->val('SELECT mess_pm_folder_rename(?i, ?i, ?)', $sUid, $sFolderId, $sName );
            $sError = $DBproxy->error;
        }
        
        return $sError;
    }
    
    /**
     * ������� �������������� ����� ������������
     * 
     * @param  string $sUid UID ������������
     * @param  string $sFolderId ID �����
     * @return string ������ ������ - �����, ��������� �� ������ sql - ������
     */
    function pmAutoFolderDelete( $sUid, $sFolderId ) {
        $DBproxy = new DB;
        $DBproxy->query( 'SELECT mess_pm_folder_delete(?i, ?i)', $sUid, $sFolderId );
        return $DBproxy->error;
    }
    
    /**
	 * �������� �������� �� �������������� ����� ������������
	 *
	 * @param  string $sUid UID ������������.
     * @param  string $sFolderId �����, ��� ������� �������� ��������
     * @param  string $sSearch ��������� ��� ������ � ������ ������������
	 * @return array
	 */
	function pmAutoFolderGetContacts( $sUid = '', $sFolderId = '', $sSearch = '' ) {
	    $DBproxy = new DB;
	    
	    if ( $sSearch ) {
            $sQuery = $DBproxy->parse( 'SELECT * FROM messages_search_pm_folder(?, ?, ?)', $sUid, $sFolderId, $sSearch );
	    }
	    else {
            $sQuery = $DBproxy->parse( 'SELECT * FROM messages_contacts_pm_folder(?, ?)', $sUid, $sFolderId );
	    }
        
	    return $DBproxy->rows( $sQuery );
	}

    /**
     * ����� �� ������������ ����� ��� ������ �� �������� �����
     *
     * @param   integer $uid    ID ������������ 
     * @return  boolean         true - ��, false - ���
     */
    function isNeedUseCaptcha($uid) {
        global $DB, $ourUserLogins;
        $ret = NULL;
        $user = new users();
        $login = $user->GetField($uid,$ee,'login');
        foreach($ourUserLogins as $ourUserLogin) {
            if(strtolower($login)==strtolower($ourUserLogin)) {
                $ret = false;
            }
        }
        if(hasGroupPermissions('administrator') || hasGroupPermissions('moderator')) { $ret = false; }
        if($ret===NULL) {
            $sql = "SELECT EXTRACT(EPOCH FROM date) as date, count FROM messages_sendlog WHERE uid=?i";
            $log = $DB->row($sql, $uid);
            if($log) {
                $spam_msg_count = (account::checkPayOperation($uid) ? self::SPAM_CAPTCHA_MSG_COUNT_PAY : self::SPAM_CAPTCHA_MSG_COUNT);
                if($log['count']>=$spam_msg_count && ($log['date']+self::SPAM_CAPTCHA_TIME_SHOW)>time()) {
                    $ret = true;
                } else {
                    $ret = false;
                }
            } else {
                $ret = false;
            }
        }
        return $ret;
    }

    function updateSendLog($uid) {
        global $DB;
        $sql = "SELECT EXTRACT(EPOCH FROM date) as date, count FROM messages_sendlog WHERE uid=?i";
        $log = $DB->row($sql, $uid);
        if($log) {
            $spam_msg_count = (account::checkPayOperation($uid) ? self::SPAM_CAPTCHA_MSG_COUNT_PAY : self::SPAM_CAPTCHA_MSG_COUNT);
            if(($log['count']<=$spam_msg_count && ($log['date']+self::SPAM_CAPTCHA_TIME_WITHOUT)>time()) || $log['count']>=$spam_msg_count) {
                if($log['count']>$spam_msg_count && ($log['date']+self::SPAM_CAPTCHA_TIME_SHOW)>time()) {
                    $sql = "UPDATE messages_sendlog SET count=count+1 WHERE uid=?i";
                } else {
                    if($log['count']<=$spam_msg_count) {
                        $sql = "UPDATE messages_sendlog SET count=count+1 WHERE uid=?i";
                    } else {
                        $sql = "UPDATE messages_sendlog SET count=1, date=NOW() WHERE uid=?i";
                    }
                }
            } else {
                $sql = "UPDATE messages_sendlog SET count=1, date=NOW() WHERE uid=?i";
            }
        } else {
            $sql = "INSERT INTO messages_sendlog(uid, date, count) VALUES(?i, NOW(), 1)";
        }
        $DB->query($sql, $uid);
    }
    
    /**
     * ������� ��� �����������, ���������� � ������, � ���� �����������
     * @param type $sender_uid uid �����������
     */
    public function clearMessageSender ($sender_uid) {
        $mem = new memBuff();
        $mem->touchTag("msgsNewSenderID{$sender_uid}");
    }
    
    
    /**
     * �������� ���� �� ���� � ��������� �������������
     * 
     * @param type $from_id
     * @param type $to_id
     * @param type $file_id
     * @return type
     */
    public function isFileExist($from_id, $to_id, $file_id)
    {
        $res = $this->DB->val("SELECT messages_file_exist(?i,?i,?i);", $from_id, $to_id, $file_id);
        return $res == 't';
    }

    
   
   /**
    * ��������� �������� ���������
    * 
    * @global type $DB
    * @param type $to_id
    * @param type $from_id
    * @return boolean
    */ 
   public static function setIsAllowed($to_id, $from_id, $stop_check = false)
   {
       global $DB;
       
       if (!$stop_check && 
            self::_isAllowed($to_id, $from_id)) {
           
           return true;
       }
       
       $DB->val("
           INSERT INTO " . self::TABLE_ALLOWED . " (to_id, from_id) 
           SELECT ?i, ?i WHERE NOT EXISTS(SELECT 1 FROM " . self::TABLE_ALLOWED . "
           WHERE to_id = ?i AND from_id = ?i LIMIT 1);
        ", 
           $to_id, $from_id,
           $to_id, $from_id   
       );
       
       $mem = new memBuff();
       $cache_tag_key = sprintf(self::CACHE_TAG_IS_ALLOWED, $from_id);
       $mem->delete($cache_tag_key);
       
       
       if (is_beta()) {                
            require_once(ABS_PATH . "/classes/log.php");                                                                                                                                                                                                                                  
            $log = new log('debug/0029319-%d%m%Y.log'); 
            $log->writeln('----- ' . date('d.m.Y H:i:s'));
            $log->writeln("to_id = {$to_id}, from_id = {$from_id}");
       }
   }

   
   
   /**
    * �������� � �� ������������ �������� ���������
    * 
    * @global type $DB
    * @param type $to_id
    * @param type $from_id
    * @return type
    */
   public static function _isAllowed($to_id, $from_id)
   {
       global $DB;
       static $exists_allowed = null;
       
       $cache_tag_key = sprintf(self::CACHE_TAG_IS_ALLOWED, $from_id);
       
       if (!$exists_allowed) {
           
           $mem = new memBuff(); 
           
           if (!$exists_allowed = $mem->get($cache_tag_key)) {
           
                $_exists_allowed = $DB->col('SELECT to_id FROM ' . self::TABLE_ALLOWED . ' 
                                             WHERE from_id = ?i', $from_id);
                
                if ($_exists_allowed) {
                    $exists_allowed = array_flip($_exists_allowed);
                    $mem->set($cache_tag_key, $exists_allowed, 604800);
                }
           }
           
       }
            
       return isset($exists_allowed[$to_id]);
   }

   


   /**
     * �������� ����������� ��������� ��������� �����������
     * 
     * @global type $DB
     * @staticvar null $exists_allowed
     * @param type $to_id
     * @param type $from_id
     * @return boolean
     */
    public static function isAllowed($to_id, $from_id = null) 
    {
        $is_auth = isset($_SESSION['uid']) && $_SESSION['uid'] > 0;

        if (!$from_id && !$is_auth) {
            return false;
        } 
        
        if (!$from_id) {
            $from_id = $_SESSION['uid'];
        }
               
        if ($is_auth && (currentUserHasPermissions('users') || is_emp())) {
            return true;
        }
        

        $is_allowed = self::_isAllowed($to_id, $from_id);
        
        
        if(!$is_allowed) {
            
            //���� �� ��� �������� �����������
            //����� ������������ ��������� ������
            $key_check_is_allowed = sprintf(self::KEY_CHECK_IS_ALLOWED, $from_id, $to_id);
            $mem = new memBuff();            

            if ($mem->get($key_check_is_allowed)) {
                return false;
            }

            
            //����� ������ ��������
            
            //������� ��� ���� �� ��� �������� � ���������� ����� �����, 
            //�������� ���� �������� ����������� ������� ��� ��� ����� ��������
            $proxy_db = new DB();
            $is_allowed = $proxy_db->val("SELECT messages_dialog_count(?i, ?i)", $to_id, $from_id) > 0;
            
            //������� �������� ������ ������������ � ����� ����� �������
            if (!$is_allowed) {
                require_once(ABS_PATH . "/classes/projects.php");
                $is_allowed = (bool)projects::isExec($from_id, $to_id);
            }
            

            //� ������ ������� � �� ��� ������� ���������� ������ �� ����� - ���� ���� ����������� ����� 
            //(� �������� ��� ���, �� ��, ������� ��� ������) � ������ ����������, �� ���� ��������� 
            //����������� ������ ��� � �����, ��� ��� � ������ �������� ����� ��������� � ��� ��� ������������.
            if (!$is_allowed) {
                require_once(ABS_PATH . "/tu/models/TServiceOrderModel.php");
                $is_allowed = (bool)TServiceOrderModel::hasSuccessfulOrder($from_id, $to_id);
            }

            
            //���� ��������� ��� ������ �� ����� �������� ����� � �������� �� 
            //�� ����� ������ ��������� ���������.
            if (!$is_allowed) {
                require_once(ABS_PATH . "/classes/contest.php");
                $is_allowed = (bool)contest::isPrizePlace($from_id, $to_id);
            }
            
            
            if ($is_allowed) {
                self::setIsAllowed($to_id, $from_id, true);
            }   
            
            
            $mem->set($key_check_is_allowed, 1, 0, self::KEY_CHECK_TAG_IS_ALLOWED);
        }
        
        return $is_allowed;
    }
    
    
    
    
}
