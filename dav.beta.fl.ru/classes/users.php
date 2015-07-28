<?

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");

/**
 * ����� ������ � ��������������
 *
 * ������������ ������� users, freelancer, employer
 * 
 */
class users{

    //������ ������������� 2��� ����� �����������
    const AUTH_STATUS_2FA = -4;
    
    
    const TXT_AUTH_2FA_SOC_FAIL = '
        � ������ �������� �������� ������ ������� �������.<br/>
        ���������� ����� �� ���� � �������������� ��� ��� ��� <a href="/registration/">������� ��� ������ ���������</a>.';
    
    const TXT_AUTH_2FA_LOG_FAIL = '
        � ������ �������� �������� ������ ������� �������.<br/>
        ���������� �������������� ��� ��� ��� <a href="/registration/">������� ��� ������ ���������</a>.';
    
    
    /**
     * id �����������
     *
     * @var integer
     */
    public $uid;
    
    /**
     * ������� ������������
     *
     * @var integer
     */
    public $rating;

    /**
     * ����� �������
     *
     * @var numeric
     */
    public $role;

    /**
     * ����� �� �����
     *
     * @var string
     */
    public $login;

    /**
     * ���
     *
     * @var string
     */
    public $uname;

    /**
     * �������
     *
     * @var string
     */
    public $usurname;

    /**
     * E-mail
     *
     * @var string
     */
    public $email;
    
    /**
     * Email - ���� ���������� ���������
     *
     * @var integer
     */
    public $email_edit_date;
    
    /**
     * �������������� Email - ���� ���������� ���������
     *
     * @var integer
     */
    public $second_email_edit_date;

    /**
     * �������������� e-mail 1
     *
     * @var string
     */
    public $email_1;
    
    /**
     * ���������� �������������� e-mail 1 ��� ������
     *
     * @var string
     */
   // public $email_1_as_link;
    
    /**
     * Email 1 - ���� ���������� ���������
     *
     * @var integer
     */
    public $email_1_edit_date;

    /**
     * �������������� e-mail 2
     *
     * @var string
     */
    public $email_2;
    
    /**
     * ���������� �������������� e-mail 2 ��� ������
     *
     * @var string
     */
   // public $email_2_as_link;
    
    /**
     * Email 2 - ���� ���������� ���������
     *
     * @var integer
     */
    public $email_2_edit_date;

    /**
     * �������������� e-mail 3
     *
     * @var string
     */
    public $email_3;
    
    /**
     * ���������� �������������� e-mail 3 ��� ������
     *
     * @var string
     */
   // public $email_3_as_link;
    
    /**
     * Email 3 - ���� ���������� ���������
     *
     * @var integer
     */
    public $email_3_edit_date;

    /**
     * �������������� e-mail
     *
     * @var string
     */
    public $second_email;
    
    /**
     * ���������� �������������� e-mail ��� ������
     *
     * @var boolean
     */
    //public $email_as_link;

    /**
     * ������
     *
     * @var string
     */
    public $passwd;

    /**
     * ���� �����������
     *
     * @var string
     */
    public $reg_date;

    /**
     * ���� ��������� ���������� �� �����
     *
     * @var string
     */
    public $last_time;

    /**
     * ����������
     *
     * @var object
     */
    public $photo;

    /**
     * ����� �������� �� ��������
     *
     * @var numeric
     */
    public $subscr;
    
    /**
     * ����� �������� ��������/���������
     *
     * @var numeric
     */
    public $settings;

    /**
     * ������
     *
     * @var integer
     */
    public $country;

    /**
     * �����
     *
     * @var integer
     */
    public $city;

    /**
     * ����� ���� ������������
     *
     * @var numeric
     */
    public $is_banned;

    /**
     * ICQ
     *
     * @var integer
     */
    public $icq;
    
    /**
     * ICQ - ���� ���������� ���������
     *
     * @var integer
     */
    public $icq_edit_date;

    /**
     * �������������� ICQ 1
     *
     * @var integer
     */
    public $icq_1;
    
    /**
     * ICQ 1 - ���� ���������� ���������
     *
     * @var integer
     */
    public $icq_1_edit_date;

    /**
     * �������������� ICQ 2
     *
     * @var integer
     */
    public $icq_2;
    
    /**
     * ICQ 2 - ���� ���������� ���������
     *
     * @var integer
     */
    public $icq_2_edit_date;

    /**
     * �������������� ICQ 3
     *
     * @var integer
     */
    public $icq_3;
    
    /**
     * ICQ 3 - ���� ���������� ���������
     *
     * @var integer
     */
    public $icq_3_edit_date;

    /**
     * Jabber
     *
     * @var string
     */
    public $jabber;
    
    /**
     * Jabber - ���� ���������� ���������
     *
     * @var integer
     */
    public $jabber_edit_date;

    /**
     * Skype
     *
     * @var string
     */
    public $skype;
    
    /**
     * ���������� Skype ��� ������
     *
     * @var string
     */
   // public $skype_as_link;
    
    /**
     * Skype - ���� ���������� ���������
     *
     * @var integer
     */
    public $skype_edit_date;

    /**
     * ���� ���������
     *
     * @var boolean
     */
    public $active;

    /**
     * ���������� ��������� �������� �� ����
     *
     * @var integer
     */
    public $hitstoday;

    /**
     * ������� ����
     *
     * @var string
     */
    public $ban_reason;

    /**
     * ���������� ��������������
     *
     * @var integer
     */
    public $warn;

    /**
     * ��� ������� ������������ (�����, ����)
     * 0 - ����� (���� is_banned 1) ��� ����� 1 - �����
     *
     * @var integer
     */
    public $ban_where;

    /**
     * ��������� �� ������������ �� ����� �� ��������� �����
     *
     * @var boolean
     */
    public $is_active;

    /**
     * ���� PRO
     *
     * @var boolean
     */
    public $is_pro;

    /**
     * ���� PRO Test
     *
     * @var boolean
     */
    public $is_pro_test;

    /**
     * ����� ������� ������ ���������� ��� �������������������� ������������� (serialize)
     *
     * @var string
     */
    public $info_for_reg;

    /**
     * �������
     *
     * @var object
     */
    public $logo;

    /**
     * �������������� id ������������ ��� ���������� ��� ������������
     *
     * @var integer
     */
    public $anti_uid;

    /**
     * ���������� ������������� �������
     *
     * @var integer
     */
    public $ops_plus;

    /**
     * ���������� ����������� �������
     *
     * @var integer
     */
    public $ops_null;

    /**
     * ���������� ������������� �������
     *
     * @var integer
     */
    public $ops_minus;

    /**
     * ������� �������������
     *
     * @var string
     */
    public $boss_note;

    /**
     * ������ �������������. ���� 1 ��� 0 (1 -- ������������ ����� ������ ������������, 0 -- ���), �� � ������� ����� ��������c�
     *
     * @var integer
     */
    public $boss_rate;

    /**
     * ������� ������������
     *
     * @var integer
     */
    public $pop;

    /**
     * �������� �������� ������������ (����������� � �������� ����)
     *
     * @var string
     */
    public $pname;
    
    /**
     * Site - ���� ���������� ���������
     *
     * @var integer
     */
    public $site_edit_date;

    /**
     * ���� ������������ 1
     *
     * @var string
     */
    public $site_1;
    
    /**
     * Site 1 - ���� ���������� ���������
     *
     * @var integer
     */
    public $site_1_edit_date;

    /**
     * ���� ������������ 2
     *
     * @var string
     */
    public $site_2;
    
    /**
     * Site 2 - ���� ���������� ���������
     *
     * @var integer
     */
	public $site_2_edit_date;
    /**
     * ���� ������������ 3
     *
     * @var string
     */
    public $site_3;
    
    /**
     * Site 3 - ���� ���������� ���������
     *
     * @var integer
     */
    public $site_3_edit_date;

    /**
     * �������������� Jabber 1
     *
     * @var string
     */
    public $jabber_1;
    
    /**
     * Jabber 1 - ���� ���������� ���������
     *
     * @var integer
     */
    public $jabber_1_edit_date;

    /**
     * �������������� Jabber 2
     *
     * @var string
     */
    public $jabber_2;
    
    /**
     * Jabber 2 - ���� ���������� ���������
     *
     * @var integer
     */
    public $jabber_2_edit_date;

    /**
     * �������������� Jabber 3
     *
     * @var string
     */
    public $jabber_3;
    
    /**
     * Jabber 3 - ���� ���������� ���������
     *
     * @var integer
     */
    public $jabber_3_edit_date;

    /**
     * ������� - ���� ���������� ���������
     *
     * @var integer
     */
    public $phone_edit_date;
    /**
     * ������� 1
     *
     * @var string
     */
    public $phone_1;
    
    /**
     * ������� 1 - ���� ���������� ���������
     *
     * @var integer
     */
    public $phone_1_edit_date;

    /**
     * ������� 2
     *
     * @var string
     */
    public $phone_2;
    
    /**
     * ������� 2 - ���� ���������� ���������
     *
     * @var integer
     */
    public $phone_2_edit_date;

    /**
     * ������� 3
     *
     * @var string
     */
    public $phone_3;
    
    /**
     * ������� 3 - ���� ���������� ���������
     *
     * @var integer
     */
    public $phone_3_edit_date;
    
    /**
     * Livejournal - ���� ���������� ���������
     *
     * @var integer
     */
    public $ljuser_edit_date;
    /**
     * Livejournal 1
     *
     * @var string
     */
    public $lj_1;
    
    /**
     * Livejournal 1 - ���� ���������� ���������
     *
     * @var integer
     */
    public $lj_1_edit_date;

    /**
     * Livejournal 2
     *
     * @var string
     */
    public $lj_2;
    
    /**
     * Livejournal 2 - ���� ���������� ���������
     *
     * @var integer
     */
    public $lj_2_edit_date;

    /**
     * Livejournal 3
     *
     * @var string
     */
    public $lj_3;
    
    /**
     * Livejournal 3 - ���� ���������� ���������
     *
     * @var integer
     */
    public $lj_3_edit_date;

    /**
     * �������������� Skype 1
     *
     * @var string
     */
    public $skype_1;
    
    /**
     * ���������� Skype 1 ��� ������
     *
     * @var string
     */
    //public $skype_1_as_link;
    
    /**
     * Skype 1 - ���� ���������� ���������
     *
     * @var integer
     */
    public $skype_1_edit_date;

    /**
     * �������������� Skype 2
     *
     * @var string
     */
    public $skype_2;
    
    /**
     * ���������� Skype 2 ��� ������
     *
     * @var string
     */
    //public $skype_2_as_link;
    
    /**
     * Skype 2 - ���� ���������� ���������
     *
     * @var integer
     */
    public $skype_2_edit_date;

    /**
     * �������������� Skype 3
     *
     * @var string
     */
    public $skype_3;
    
    /**
     * ���������� Skype 3 ��� ������
     *
     * @var string
     */
    //public $skype_3_as_link;
    
    /**
     * Skype 3 - ���� ���������� ���������
     *
     * @var integer
     */
    public $skype_3_edit_date;

    /**
     * ���������� ���� (�����, ��������, �� �������, ��� �����-�� ��� ��)
     *
     * @var string  t|f
     */
    public $is_chuck;

    /**
     * ���� �� ������� Free-lance.ru
     *
     * @var string  t|f
     */
    public $is_team;
    
    /**
     * Sex
     */
     public $sex;

    /**
     * ��������� �� ������� ����� ������
     */
     public $is_visited;

    /**
     * ������������ ������������ � ��� ���� � ���������� (/siteadmin/account/)
     *
     * @var string  t|f
     */
    public $ignore_in_stats;
    
    /**
     * ��� ����� ������ ����� WM: NULL - �� ����������, 1 - webmoney, 2 - paymaster
     * 
     * @var integer 
     */
    public $wm_paymaster;
    
    /**
     * ������ �� ������������ ��������������
     * 
     * @var bool 
     */
    public $self_deleted;
    
    /**
     * ���� ��� ������������ � ������ � token
     * 
     * @var string
     */
    public $solt;
    
    /**
     * �������� ���������� � ������������� ������� (bit)  @see splash_screen::SPLASH_*
     * 
     * @var integer (bit)
     */
    public $splash_show;
    
    /**
     * ���� ���������� ������ ������ SPLASH_FREELANCER ��� SPLASH_EMPLOYER
     * 
     * @var integer (integer)
     */
    public $splash_last_date;
    
    /**
     * �������� ���������� �� ������������ ������� ������������ (bit)  @see single_send::NOTICE_*
     * 
     * @var integer (bit)
     */
    public $single_send;
    
    /**
     * ID ������������, ���������������� �������
     * 
     * @var integer 
     */
    public $moduser_id;
    
    /**
     * ������������� ������������ ��� ���
     * 
     * @var type 
     */
    public $is_verify;
    
    /**
     * ����� ���������� �������������� ������
     */
    public $modified_time;
    
    /**
     * ����� ���������� �������������� ������
     */
    public $photo_modified_time;
    
    
    /**
     * ������� PROFI 
     */
    public $is_profi;






    /**
     * ���-�� IP ������� ����� �������� � ���� ������� �������������
     *
     */
    const MAX_LOGIN_IP_LOG = 10;

    /**
     * ���-�� Email ������� ����� �������� � ���� ����� email �������������

     *
     */
    const MAX_CHANGE_EMAIL_LOG = 10;
    
    /**
     * ����������� �� ����������� � ������ IP � �����.
     */
    const MAX_REG_IP = 20;

    
    /**
     * ���������� �������� ��� ��������� ������ ������ 
     */
    const MAX_NEW_PASSWORD_LENGTH = 8;

    
    
    /**
     * ������� ��� ����� ������������ (����)
     */
    const NOOB_TIME_DAYS = 60;
    
    
    
    
    /**
    * ���������� ��� ���� ������������
    *
    * @return string $rolename ��� ���� (freelancer ��� employer)
    */
    public function getRoleName()
    {
        $rolename = '';

        if (isset($this->role)) {
            if ((binary) $this->role === (binary) '000000') {
                $rolename = 'freelancer';
            }

            if ((binary) $this->role === (binary) '100000') {
                $rolename = 'employer';
            }
        }

        return $rolename;
    }   

    /**
     * �������� ������ ������������
     *
     * @param integer $rerror               ������ � ������ ������������
     * @param array $error                  ������ ���� ������
     *
     * @return mixed                        id ������������ ��� ������, � ������ ��������
     */
    function Create(&$rerror, &$error){
        global $DB;
        $id = 0;
        $ip = getRemoteIP();
        
        // ���������� ����������� � ������ IP
        $sDate  = date('Y-m-d');
        $sQuery = "SELECT COUNT(uid) FROM users WHERE reg_ip=? AND reg_date=?";
        $nCount = $DB->val($sQuery, $ip, $sDate );
        
        if ( $nCount >= users::MAX_REG_IP ) {
        	$error['exceed_max_reg_ip'] = 1;
        	
        	// ����� ���
        	$sQuery = "SELECT COUNT(reg_ip) FROM users_regip_log WHERE reg_ip=? AND reg_date=?";
        	
        	if ( !$DB->val($sQuery, $ip, $sDate ) ) {
        		$DB->insert( 'users_regip_log', array('reg_ip' => $ip, 'reg_date' => $sDate) );
        	}
        	
        	return 0;
        }
        
        if (!preg_match("/^[a-zA-Z0-9]+[-a-zA-Z0-9_]{2,}$/", $this->login)) $rerror += 1;
        if($this->checked_name) {
            if (!preg_match("/^[-a-zA-Z�-��-߸�]+$/", $this->uname)) $rerror += 2;
            if (!preg_match("/^[-a-zA-Z�-��-߸�]+$/", $this->usurname)) $rerror += 4;
        }
        if (!is_email($this->email)) $rerror += 8;

        if(!($rerror & 1)) {
            $sql = "SELECT uid FROM users WHERE lower(login) = ?";
            if ($DB->val($sql, strtolower($this->login))) $error['login_ex'] = 1;
        }

        if(!($rerror & 8)) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/temp_email.php");
            
            if ( temp_email::isTempEmail($this->email) ) {
            	$error['email_ex'] = 2;
            }
            else {
                $sql = "SELECT uid FROM users WHERE lower(email) = ?";
                if ($DB->val($sql, strtolower($this->email))) $error['email_ex'] = 1;
            }
        }

        if(!$error && !$rerror){
            if(!preg_match("/^[0-1]{16}/", $this->subscr)) {
                $this->subscr = '1111111111111111';
            }
            $rolesize = $GLOBALS['rolesize'];
            $sex_str = 'NULL';//$this->sex ? 'true' : 'false';
            $active = true;
            $sql = "
            INSERT INTO users
                (login, uname, usurname, passwd, email, role, reg_date, reg_ip, last_time, last_ip, sex, active, subscr) 
            VALUES 
                (?, ?, ?, ?, lower(?), B'$this->role'::bit($rolesize),current_date, ?, now(), ?, $sex_str, ?, ?); 
            SELECT currval('users_uid_seq');";
            $id = $DB->val($sql, $this->login, $this->uname, $this->usurname, users::hashPasswd($this->passwd), $this->email, $ip, $ip, $active, $this->subscr);
            $error = $DB->error;
            if (!$error && !$rerror && $id){
                $sd = substr($this->login, 0, 2)."/";
                $cfile = new CFile();
                $cfile->DeleteDir('users/'.$sd.$this->login.'/');
                self::SaveChangeEmailLog($id, $this->email);
            } else {
              
              // $error = parse_db_error($error, $ex_err);
              $error = parse_db_error($error, $ex_err);
              if($ex_err)
                $error=$ex_err;
            }
        }
        return ($id);
    }
    
    /**
     * ���������� ��� ������
     * 
     * @param  string $passwd ������
     * @param  integer $version  0:�������� ������, 1:��� �������������� �� ������ md5(pass+pass).
     * @return string
     */
    function hashPasswd($passwd, $version = 0) {
        if($version == 0) {
            $passwd = md5($passwd.$passwd);
        }
        return md5(PRIVATE_PWD_SALT . md5($passwd . trim(chunk_split(base64_encode(PRIVATE_PWD_SALT), 76, "\n")))); // ��� �������������� encode()
    }
    
    /**
     * ���������� ��� ��� ����������� ����� ����.
     * 
     * @param  integer $uid   ��. �����
     * @param  array $pwd_data   ������ � ���� users::GetUserSoltCookie($uid)
     * @return string
     */
    function cookieHashPasswd($uid, &$pwd_data = NULL) {
        $pwd_data = users::GetUserSoltCookie($uid);
        return md5(COOKIE_PWD_SALT . $pwd_data['solt'] . sha1($pwd_data['pwd']) . ($pwd_data['safety_bind_ip']=='t' ? getRemoteIP() : ''));
    }
    
    /**
     * �������� ������������
     *
     * @param integer $id               id ������������
     * @param string $passwd            ������ ������������
     * @param string $error             ������������ ������ ������
     * @param string $login             ����� ������������
     * @param integer $force            (1 - ������������ ������ ������������ ��� ��������, 0 - ������������ ���� id-passwd)
     *
     * @return mixed                    ����� ������������ ��� ������, � ������ ��������
     */
    function DeleteUser($id, $passwd, $error, $login = "", $force = 0){
        global $DB;
        if ($force && $login) $sql = "DELETE FROM users_counters WHERE user_id = '$id';DELETE FROM users WHERE (uid=$id AND login='$login') RETURNING login";
        else $sql = "DELETE FROM users WHERE (uid='$id' AND passwd='$passwd') RETURNING login";
        $dlogin = $DB->val($sql);
        $error .= $DB->error;
        if ($error) die("������ ����������: ".$error);
        if ($login == "")
            $login = $dlogin;
        $cfile= new CFile();
        $cfile->DeleteDir("users/".substr($login, 0, 2)."/".$login."/");

        return ($dlogin?1:0);
    }



    /**
    * ��������� ������ ������������
    * ������ ���� ���������� ������ �� ����������, ������� ���� ��������
    * ����� ������� ��������� ���������� ������!
    *
    * @param integer $fid               id ������������
    * @param array $res                 ������ ���� ������
    * @param string $eddition           �������������� ������� SQL
    *
    * @return string (error)
    */
    function Update($fid, &$res, $eddition = ""){
        global $DB;
    	if (!$fid) return("������������ �� ���������!");
        //�������
        $parnames = array(
            'skype_as_link', 'skype_1_as_link', 'skype_2_as_link', 'skype_3_as_link', 
            'email_as_link', 'email_1_as_link', 'email_2_as_link', 'email_3_as_link'
        );
        $current = get_class($this);
        $class_vars = get_class_vars(get_class($this));
        $fields = array();
        $pwd_changed = false;
               
        if ( !isset($this->moduser_id) ) {
            $this->moduser_id = $fid;
        }
        
        $this->modified_time = 'now';
        
        if ( isset($this->photo) ) {
            $this->photo_modified_time = 'now';
        }
        
        // �������� �� ������������� -----------
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
        
        $stop_words   = new stop_words();
        $aModerFields = array( 'uname', 'usurname', 'pname', 'spec_text', 'resume_file', 'resume', 'konk', 'company', 'status_text', 'photo', 'logo', 'compname' );
        $aModerUpdate = array();
        $aOldValues   = array();
        //--------------------------------------
        
        foreach ($class_vars as $name => $value) {
            if (!in_array($name,$parnames) && isset($this->$name)){
                if ($name == "passwd") {
                    $fields[] = $name."= '".users::hashPasswd($this->$name)."'";
                    $pwd_changed = true;
                } else if ($name == "rating") {
                    // ������� �������� � users ������ ������
                    continue;
                } else {
                    //$fields[] = $name."= '".$this->$name."'";
                    $fields[] = $DB->parse("$name = ?", $this->$name);
                    
                    // �������� �� ������������� -----------
                    if ( $this->moduser_id == $fid && in_array($name, $aModerFields) ) {
                        $aModerUpdate[]    = $name;
                        $aOldValues[$name] = $this->GetField( $fid, $sGetFieldError, $name );
                    }
                    //--------------------------------------
                }
            }
        }
        $fld = implode(", ",$fields);      
        if ($fld){
            $fid = intval($fid);
            $sql .= $DB->parse("UPDATE $current SET $fld WHERE (uid = ?i ".$eddition.")", $fid);
            if($res = $DB->squery($sql)) {
                // �������� �� ������������� -----------
                if ( $aModerUpdate ) {
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );

                    $aModerNoWord = array( 'resume_file', 'photo', 'logo' );
                    $nOrderId     = $DB->val("SELECT from_id FROM orders WHERE from_id= ?i 
                        AND from_date <= now() AND from_date + to_date + COALESCE(freeze_to, '0')::interval >= now() 
                        AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)", $fid 
                    );

                    foreach ( $aModerUpdate as $name) {
                        $sChangeId = $DB->val( 'SELECT id FROM users_change WHERE user_id = ?i AND ucolumn = ?', $fid, $name );

                        if ( !$sChangeId && ($stop_words->calculate($this->$name) || in_array($name, $aModerNoWord)) ) {
                            if ( !empty($this->$name) ) { 
                                $aData = array(
                                    'user_id'          => $fid, 
                                    'utable'           => $current, 
                                    'ucolumn'          => $name, 
                                    'old_val'          => $aOldValues[$name], 
                                    'new_val'          => $this->$name, 
                                    'moderator_status' => ( $nOrderId ? -2 : 0 )
                                );

                                $sChangeId = $DB->insert( 'users_change', $aData, 'id' );

                                if ( !$nOrderId ) {
                                    $aData = array(
                                        'rec_id'   => $sChangeId, 
                                        'rec_type' => user_content::MODER_PROFILE, 
                                        'status'   => 0

                                    );

                                    if ( !in_array($name, $aModerNoWord) ) {
                                        $stop_words->calculateRegexNoWords(); // ������������� ���� �����
                                        $aData['stop_words_cnt'] = $stop_words->calculate( $this->$name );
                                    }

                                    $DB->insert( 'moderation', $aData );
                                }
                            }
                        }
                        else {
                            if ( !empty($this->$name) && ($stop_words->calculate($this->$name) || in_array($name, $aModerNoWord)) ) { 
                                $stop_words->calculateRegexNoWords(); // ������������� ���� �����
                                $DB->update( 'users_change', array('new_val' => $this->$name), 'id = ?i', $sChangeId );
                                $DB->update( 
                                    'moderation', 
                                    array('stream_id' => null, 'stop_words_cnt' => $stop_words->calculate($this->$name)), 
                                    'rec_id = ?i AND rec_type = ?i', 
                                    $sChangeId, user_content::MODER_PROFILE 
                                );
                            }
                            else {
                                $DB->query( 'DELETE FROM users_change WHERE id = ?i', $sChangeId );
                                $DB->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i', $sChangeId, user_content::MODER_PROFILE );
                            }
                        }
                    }
                }
                //--------------------------------------
                
                if ($pwd_changed) {
                    // ������� ������ �������� �������� api.
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/external/session.php");
                    $sess = new externalSession();
                    $sess->destroy($fid);
                    
                    // ����� �������� ��������� ���������� � ������������ -----
                    if ( $aAnti = $DB->col( 'SELECT "login" FROM users WHERE anti_uid = ?i', $fid ) ) {
                        foreach ( $aAnti as $sLogin ) {
                        	$GLOBALS['session']->logout( $sLogin );
                        }
                        
                        if ( $_SESSION['uid'] == $fid ) {
                            $_SESSION['anti_uid'] = $_SESSION['anti_login'] = $_SESSION['anti_surname'] = $_SESSION['anti_name'] = '';
                        }
                        
                        $DB->query( 'UPDATE users SET anti_uid = NULL WHERE uid = ?i OR anti_uid = ?i', $fid, $fid );
                    }
                    //---------------------------------------------------------
                }//���� ������� ��������� �� ���� ��������, ������� ������ �� users_subscribe_keys
                if ( ( strpos($this->subscr, '1') === false ) && ( strlen($this->subscr) > 0 ) ) {
                    $DB->query( 'DELETE FROM users_subscribe_keys WHERE uid = ?i', $fid );
                }
            }
            else if($error = $DB->error)
                $error = parse_db_error($error);
        }
        return ($error);
    }

    /**
     * ��������� ����������� �� �������
     * @param type $uid
     * @param type $reg_date
     */
    public function checkRegDate($uid, $reg_date) {
        $time = strtotime('+3 day', strtotime($reg_date));
        if($time < strtotime(date("d-m-Y"))) { // ������������ ��� � �� ���������� �������, ������� ���������
            $this->active = false;
            $this->Update($uid, $res);
            logout();
            header("Location: /inactive.php");
            exit;
        }
    }

    /**
     * �������������� ������������ � ���������� ��� ������ ������������ �������
     *
     * @param string $login             ����� ������������
     * @param string $pwd               ������ ������������
     * @param array &params             ������ ������������
     * @param boolean $is_2fa_off       �������������� ����������� 2� ������� ��������
     *
     * @return integer                  id ������
     * @global DB $DB
     */
    function Auth($login, $pwd, &$params, $is_2fa_off = false) {
        //////////////////////////////////////////////////////////
        // ������! ��������� ������ ����� �������� ����� � ����� ������.
        // ��������, ��� ���������� ����� ����� � ������, �������� �� � Web_Front::login()
        //////////////////////////////////////////////////////////

        global $DB;

        $plogin = preg_replace("/[+ ()-]/", "", $login);
        $phoneType = preg_replace("/\D/", "", $plogin);
        if(($phoneType == $plogin)) {
            $plogin = "+".$plogin;
            $sql = "SELECT user_id FROM sbr_reqv WHERE (_1_mob_phone = ? OR _2_mob_phone = ?) AND is_activate_mob = 't'";
            $uids = $DB->rows($sql, $plogin, $plogin);
            if($uids) {
                foreach($uids as $u) {
                    $sql_uids .= $u['user_id'].',';
                }
                $sql_uids = preg_replace("/,$/", "", $sql_uids);
            }
        }

        $sql = "
          SELECT 
            u.email, u.role, u.uname, u.usurname, u.uid, u.is_banned, u.ban_where, u.active, 
            a.sum, a.bonus_sum,
            u.login, u.anti_uid, u.is_pro_test, u.is_pro_new, u.is_chuck, 
            u.sex, u.settings, u.splash_show, u.is_verify,
            u.reg_date, ac.code, u.photo, u.is_profi,
            u.birthday
          FROM users AS u
          LEFT JOIN activate_code ac ON ac.user_id = u.uid  
          LEFT JOIN account AS a ON a.uid = u.uid
          WHERE ((lower(u.login) = ? OR lower(u.email) = ?) AND u.passwd = ?) ".($sql_uids ? "OR ( u.uid IN ({$sql_uids}) AND u.passwd = ?)" : "");
          
        $res = $DB->rows($sql, strtolower($login), mb_strtolower($login), $pwd, $pwd);
        
        if($res) {
            $qres = $res;
            $uvisits = array();
            $n = 0;
            foreach($qres as $k=>$v) {
                $uvisits[$this->getLastVisit($v['uid']).'-'.$n] = $k;
                $n++;
            }
            asort($uvisits);
            $res = $qres[array_pop($uvisits)];
        }
        $error .= $DB->error;
        $first_login = $this->getLastVisit($res['uid']);
        $ip = getRemoteIP();

        /**
         * �������������� �������� ������. 
         * ����� ��� ����������� �������, ���������� 
         * ���� (��� ������� �� ���� ������������������)
         * 
         * !!������ ����� ��������� ���������� ����� �������.
         */
        if(!$res) {
            // ��������� ������ (0018079)
            //$res = $this->FixPassword($sql, $login);
        }

        
        
        
        /**
         * ���������� ����� �� 2�������� �����������
         */        
        if (!$is_2fa_off && //2� ������ �������� �� ���������
             count($res) && //�������� �����������
             $first_login) { //�� ������ ����
            

            //���� �� 2�� ����� ����� ������ ������� �� ��������� 
            //������� �� 2�� ���� � �������� ������
            if ( isset($params['2fa_provider']['uid']) && 
                 $params['2fa_provider']['uid'] != $res['uid'] ) {
                
                $is_login = $params['2fa_provider']['type'] == 0;
                
                session::setFlashMessage(
                        $is_login? self::TXT_AUTH_2FA_LOG_FAIL : 
                                   self::TXT_AUTH_2FA_SOC_FAIL,
                        '/auth/second/');
                
                return self::AUTH_STATUS_2FA;
            }
            
            
            $is_opauth = defined('IS_OPAUTH');
            
            if (!isset($params['2fa_provider']) || //������ ��� ����������� 
                ($params['2fa_provider']['type'] > 0) != $is_opauth) { //����������� ���� ����������� �� 2�� �����
                
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opauth/OpauthModel.php");
                $opauthModel = new OpauthModel();
                $is_2fa = $opauthModel->getMultilevel($res['uid']);
                if (isset($is_2fa['type'])) {
                    //$is_2fa - ����������� ����� ��������� �������
                    //0 - ����� ������� ����������� ��� ��� ���� ��� �������� ����� �������
                    $params['2fa_provider'] = array(
                        'type' => (!$is_opauth)?$is_2fa['type']:0,
                        'uid' => $res['uid'],
                        'login' => $res['login'] 
                    );

                    //���������� �����������
                    $res = array();

                    //��������� �� 2�� ������
                    return self::AUTH_STATUS_2FA;
                }
            }
            
        }
        
        //����� ��� �������� ������ ����������� �� �����
        unset($params['2fa_provider']);

        
        
        /**
         * �������� �����������
         */
        if (count($res)){            
            list($email, $trole, $tname, $tsurname, $tid, $is_banned, $ban_where, $active, 
                 $sum, $bonus_sum, $log, $anti_uid, $is_pro_test, $is_pro_new, 
                 $is_chuck, $sex, $settings, $splash_show, $is_verify, $reg_date, 
                 $activate_code, $photo, $is_profi, $birthday) = array_values($res);
            
            if($activate_code != '' && $active == 't') {
                $this->checkRegDate($tid, $reg_date);
            }
            
            if ($is_banned ) return -1;
            //if ($active=='f') return -2; //##0027983
            if(!$this->CheckUserAllowIP($ip,$tid)) return -3;

            $params['birthday'] = ($birthday)?strtotime($birthday):null;
            $params['age'] = ($params['birthday'])? intval(ElapsedYears($params['birthday'])):null;

            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/permissions.php");
            $params['permissions'] = permissions::getUserPermissions($tid);
            $params['email'] = $email;
            $params['role'] = $trole;
            $params['name'] = $tname;
            $params['surname'] = $tsurname;
            $params['uid'] = $tid;
            $params['user_ip'] = $ip;
            $params['ac_sum'] = zin($sum);
            $params['bn_sum'] = zin($bonus_sum);
            $params['login'] = $log;
            $params['is_pro_new'] = $is_pro_new;
            $params['pro_test'] = $is_pro_test;
            $params['is_chuck'] = $is_chuck;
            $params['is_verify'] = $is_verify;
            $params['sex'] = $sex;
            $params['reg_date'] = $reg_date;
            $params['photo'] = $photo;
            
            if (!is_emp($trole)) {
                $params['is_profi'] = ($is_profi === 't');
            }
            
            if($anti_uid) {
              $anti_class = is_emp($trole) ? 'freelancer' : 'employer';
              require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/{$anti_class}.php");
              $anti = new $anti_class();
              $anti->GetUserByUID($anti_uid);
              $params['anti_uid'] = $anti->uid;
              $params['anti_login'] = $anti->login;
              $params['anti_surname'] = $anti->usurname;
              $params['anti_name'] = $anti->uname;
            }
            if(!is_emp($params['role'])) {
              require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
              if($po_summary = projects_offers::GetFrlOffersSummary($params['uid']))
                $params['po_count'] = $po_summary['total'];
            }
            $sql = "UPDATE users SET last_time = now(), last_ip = ?, is_active = true WHERE uid = ?i";
            $res = $DB->query($sql, $ip, $tid);
            $this->SaveLoginIPLog($tid,$ip);

            $this->increaseLoginsCnt($tid);
            
            // ���������� ��������
            $sQuery = 'SELECT COUNT(ao.id) FROM account_operations ao 
                INNER JOIN account a ON a.id = ao.billing_id WHERE a.uid = ?i AND (ao.ammount <> 0 OR ao.trs_sum <> 0)';
            
            $params['account_operations'] = $DB->val($sQuery, $tid);
            
            $params['question_button_hide']  = $settings[1]; // ����������/�������� ������ "� ��� ���� ������?"
            $params['promo_block_hide']      = $settings[2]; // ���������� ���� "������� ������ � �������� �������� �����"
            $params['direct_external_links'] = $settings[3]; // �� ���������� �������� "������� �� ������� ������" a.php
            $params['sbr_slash_show']        = $settings[4] && $first_login < strtotime('2012-08-08'); // ����������/������ ��� �����-����
            $params['splash_show']           = $splash_show;
            $params['chat']                  = $settings[5];
            $params['chat_sound']            = $settings[6];
            // #0017182 > ������ ����� �� �� �������� ��� ��������� �� ��� ������������� � ��������� �� � ����?
            if ( empty($settings[3]) && $_COOKIE['direct_external_links'] == 1 ) {
            	$this->setDirectExternalLinks( $tid, 1 );
            	
            	if ( $anti_uid ) {
            	    $this->setDirectExternalLinks( $anti_uid, 1 );
            	}
            	
            	setcookie( 'direct_external_links', '', time()-60*60*24*365, '/' );
            	setcookie( 'no_a_php', '1', time()+60*60*24*365*2, '/' );
            }
            //��������� ���� ��� userecho
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/userecho.php");
            setcookie('ue_sso_token', UserEcho::get_sso_token(USERECHO_API_KEY, USERECHO_PROJECT_KEY, array()), 0, '/', preg_replace('/^https?\:\/\/(?:www\.)?/', '.', 'fl.ru'));

            // ������ �����, ����������� ����� ������, ������ �� ��������
            if($first_login == 0) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/wizard.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/wizard_registration.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_employer.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_freelancer.php");
                
                if(is_emp($params['role'])) {
                    $wiz_user = wizard::isUserWizard($tid, step_employer::STEP_REGISTRATION_CONFIRM, wizard_registration::REG_EMP_ID);
                } else {
                    $wiz_user = wizard::isUserWizard($tid, step_freelancer::STEP_REGISTRATION_CONFIRM, wizard_registration::REG_FRL_ID);
                }
                if($wiz_user['id'] > 0) {
                    $role = is_emp($params['role']) ? wizard_registration::REG_EMP_ID : wizard_registration::REG_FRL_ID ;
                    header("Location: /registration/activated.php?role=".$role);
                    //header("Location: /wizard/registration/?role={$role}");
                    exit;
                } elseif(!is_emp($params['role'])) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
                    $pro_last = payed::ProLast($_SESSION['login']);
                    $_SESSION['pro_last'] = $pro_last['is_freezed'] ? false : $pro_last['cnt'];
                    if($_SESSION['pro_last'] && $_SESSION['is_pro_new'] != 't')
                        payed::checkNewPro($id);
                    if($pro_last['freeze_to']) {
                        $_SESSION['freeze_from'] = $pro_last['freeze_from'];
                        $_SESSION['freeze_to'] = $pro_last['freeze_to'];
                        $_SESSION['is_freezed'] = $pro_last['is_freezed'];
                        $_SESSION['payed_to'] = $pro_last['cnt'];
                    }
                    if($_SESSION['anti_login']) {
                        $pro_last = payed::ProLast($_SESSION['anti_login']);
                        $_SESSION['anti_pro_last'] = $pro_last['freeze_to'] ? false : $pro_last['cnt'];
                    }
                    
                    //���������� ������ � �����, ��� �������� �� �����
                    /*
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
                    $mail = new smail();
                    
                    if (is_emp()) {                
                        $mail->employerQuickStartGuide(get_uid(false));
                    } else {
                       $mail->freelancerQuickStartGuide(get_uid(false));
                    }
                    */
                    
                    return $tid;
                    
                    if ( !defined('IN_API') ) { // ��� API ���������� ���������� �� �����
                        header("Location: /users/{$login}/");
                        exit;
                    }
                }
            }
            //-----------------------------------
        } else {
            $tid = 0;
            require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/annoy.php");
            $annoy = new annoy();
            $annoy->Add($ip);
        }
        
        return $tid;
    }

    /**
     * ���� ���-�� ����������� �� �����
     *
     * @param    integer    $uid    ID ������������
     */
    function increaseLoginsCnt($uid) {
        global $DB;
        $sql = "SELECT user_id FROM users_login_cnt WHERE user_id = ?i";
        $res = $DB->val($sql, $uid);
        if (!$res) {
            $sql = "INSERT INTO users_login_cnt(user_id, cnt) VALUES (?i, 1);";
            $DB->query($sql, $uid);
        } else {
            $sql = "UPDATE users_login_cnt SET cnt = cnt+1 WHERE user_id = ?i";
            $DB->query($sql, $uid);
        }
    }

    /**
     * ��������� ��-�� ����������� ������������
     *
     * @param    integer    $uid     ID ������������
     * @return   integer             ���-�� �����������
     */
    function getLoginsCnt($uid) {
        global $DB;
        $sql = "SELECT cnt FROM users_login_cnt WHERE user_id = ?i";
        $n = intval($DB->val($sql, $uid));
        if ($n < 11) {
            $STAT = new DB("stat");
            $m = intval($STAT->val("SELECT COUNT(user_id) FROM users_visits_daily WHERE user_id = ?i", $uid));
        }
        if ($m > $n) {
            $n = $m;
        }
        return $n;
    }

    /**
    * ����� ��� IP ������������� ��� ������ �� �����
    *
    * @param    integer $uid    ������������� ������������
    * @param    string  $ip     IP �����
    */
    function SaveLoginIPLog($uid,$ip) {
        global $DB;
        $int_ip = ip2long($ip);
        $sql = "SELECT id FROM users_loginip_log WHERE ip=?i AND uid=?i";
        $res = $DB->val($sql, $int_ip, $uid);
        if ( !$res ) {
            // � ����� IP ��� �� ������� �������
            $sql = "DELETE FROM users_loginip_log WHERE id IN (SELECT id FROM users_loginip_log WHERE uid=?i ORDER BY date DESC offset ?i)";
            $DB->query($sql, $uid, (users::MAX_LOGIN_IP_LOG-1));
            $sql = "INSERT INTO users_loginip_log(uid,ip,date) VALUES (?i,?i,NOW())";
            $DB->query($sql, $uid, $int_ip);
        }
        else {
            $sql = 'UPDATE users_loginip_log SET "date"=NOW() WHERE id = ?i';
            $DB->query($sql, $res);
        }
    }

    /**

    * ����� ��� ����� email �������������
    *
    * @param    integer $uid    ������������� ������������
    * @param    string  $email  E-mail
    */
    function SaveChangeEmailLog($uid,$email) {
        global $DB;
        $sql = "SELECT id FROM users_change_emails_log WHERE email=? AND uid=?";
        $log = $DB->row($sql, $email, $uid);
        if(!$log) {
            // ������ email ��� �� ����
            $sql = "DELETE FROM users_change_emails_log WHERE uid=?i AND id NOT IN (SELECT id FROM users_change_emails_log WHERE uid=?i ORDER BY date DESC LIMIT ?i)";
            $DB->query($sql, $uid, $uid, (users::MAX_CHANGE_EMAIL_LOG-1));
            $sql = "INSERT INTO users_change_emails_log(uid,email,date) VALUES (?i,?,NOW())";
            $DB->query($sql, $uid, $email);
        } else {
            $sql = "UPDATE users_change_emails_log SET email=?, date=NOW() WHERE id=?i";
            $DB->query($sql, $email, $log['id']);
        }
    }
    
    /**
     * ���� ��� ����� ����� ����, ������� ������ ������
     * @global DB $DB
     * @param type $uid
     */
    function initChangeEmailLog($uid) {
        global $DB;
        $sql = "SELECT id FROM users_change_emails_log WHERE uid=?";
        $log = $DB->row($sql, $uid);
        if(!$log) {
            // ����� email ��� �� ����
            $sql = "INSERT INTO users_change_emails_log(uid,email,date) VALUES (?i, (SELECT email FROM users WHERE uid = ?i),NOW())";
            $DB->query($sql, $uid, $uid);
        }
    }
    
    
    /**
     * ���������� ������ ��������� IP � ������� ������� ������������
     * 
     * @param  int $sUid UID ������������
     * @param  int $nCount �����������. ����������, 0 - �� ����������
     * @return array
     */
    function getLastIps( $sUid = '', $nCount = 10 ) {
        $sQuery = 'SELECT * FROM users_loginip_log WHERE uid=?i ORDER BY date DESC' 
            . ( $nCount ? '' : ' LIMIT ' . intval($nCount) );
        
        return $GLOBALS['DB']->rows( $sQuery, $sUid );
    }
    
    /**
     * ���������� ������ ��������� email ������� ������������ ������������
     * 
     * @param  int $sUid UID ������������
     * @param  int $nCount �����������. ����������, 0 - �� ����������
     * @return array
     */
    function getLastEmails( $sUid = '', $nCount = 10 ) {
        $sQuery = 'SELECT * FROM users_change_emails_log WHERE uid=?i ORDER BY date DESC' 
            . ( $nCount ? '' : ' LIMIT ' . intval($nCount) );
        
        return $GLOBALS['DB']->rows( $sQuery, $sUid);
    }

    /**
    * ���������� ������ ��� ������������ � ���� ��� �������� ���� � IP
    *
    * @param integer    $uid                ID ������������
    * @return array                         'pwd' - ������� ������, 'solt' - ��������������� ������
    */
    function GetUserSoltCookie($uid) {
        global $DB;
		$sql = "SELECT passwd as pwd, solt, safety_bind_ip FROM users WHERE uid=?i";
        $ret = $DB->row($sql, $uid);
        if(!$ret['solt']) {
			mt_srand();
            $solt = md5(uniqid(mt_rand(), true));
            $sql = "UPDATE users SET solt=? WHERE uid=?i";
            $DB->query($sql, $solt, $uid);
            $ret['solt'] = $solt;
        }
		return $ret;
    }

    /**
    * ��������� IP �� �������������� � ����������� IP, ������� ������������ ������� ��� IP � ������� �������� �����
    *
    * @param string     $ip     IP ����� ����������
    * @param integer    $uid    ID ������������
    *
    * @return boolean       TRUE - ������ ��������, FALSE - ������ ��������
    */
    function CheckUserAllowIP($ip,$uid) {
        global $DB;
        $ret = TRUE;
        $longip = ip2long($ip);
        $sql = "SELECT b_ip,e_ip FROM users_safety WHERE uid=?i";
        $ips = $DB->rows($sql, $uid);
        if($ips) {
            $ret = FALSE;
            while(list($k,$dip)=each($ips)) {
                if($longip>=$dip['b_ip'] && $longip<=$dip['e_ip']) {
                    $ret = TRUE;
                    return $ret;
                }
            }
        }
        return $ret;
    }


    /**
     * ���������������� ���������� ������ ������� �� �������, ���������������� ������������ � ������ �������
     *
     * @param string $login              ����� ������������
     *
     * @return string                    ��������� �� ������
     */
    function GetUser($login, $anybase = true, $email = false){
        if($email) {
            $q = array('lower(login) = ? OR lower(email) = ?', strtolower($login), mb_strtolower($login));
        } else {
            $q = array('lower(login) = ?', strtolower($login));
        }
        return $this->InitFromSQL($q, $anybase);
    }
    

    
    /**
     * �������� UID ������������ �� ������ ��� ���� ��� ��������
     * 
     * @global DB $DB
     * @param type $login
     * @return type
     */
    public function getUidByLoginEmailPhone($login)
    {
        global $DB;
        
        if (empty($login)) {
            return 0;
        }
        
        $_login = strtolower($login);
        $_email = mb_strtolower($login);
        
        $plogin = preg_replace("/[+ ()-]/", "", $login);
        $phoneType = preg_replace("/\D/", "", $plogin);
        
        $_phone_sql = '';
        
        if ($phoneType == $plogin && !empty($plogin)) {
            $plogin = "+" . $plogin;
            
            $_phone_sql = $DB->parse("OR ((sr._1_mob_phone = ? OR sr._2_mob_phone = ?) AND sr.is_activate_mob = 't')", 
                $plogin, $plogin);
        }

        $uid = $DB->cache(300)->val("
            SELECT u.uid FROM users AS u 
            ".(($_phone_sql)?"LEFT JOIN sbr_reqv AS sr ON sr.user_id = u.uid":"")."
            WHERE lower(u.login) = ? OR lower(u.email) = ? {$_phone_sql}
        ", $_login, $_email);
            
        return $uid;
    }







    /**
     * ���������������� ���������� ������ ������� �� �������, ���������������� ������������ � ������ UID
     *
     * @param integer $uid               id ������������
     *
     * @return string                    ��������� �� ������
     */
    function GetUserByUID($uid){
        return $this->InitFromSQL(array('uid = ?i', $uid));
    }
    

    /**
     * ���������������� ���������� ������ ������� �� �������, ���������������� ������������ � ������ ������� � �������
     *
     * @param string $login              ����� ������������
     * @param string $passwd             ������ ������������ � md5
     * @return string                    ��������� �� ������
     */
    function GetUserByLoginPasswd($login, $passwd) {
        return $this->InitFromSQL( array('lower(login) = ? AND passwd = ?', strtolower($login), $passwd) );
    }


    /**
     * ���������� �������� �������� ������������
     *
     * @param integer $fid               id ������������
     * @param string $name               ��� ������������
     * @param string $surname            ������� ������������
     * @param string $email              e-mail ������������
     * @param string $oldpwd             ������ ������
     * @param string $pname              �������� �������� ������������
     * @param string $try_pwd            ���� 1, �� ������ ��������� ������ � ������� 1 � ������ ������, 0 - � ������ ������
     *
     * @return string                    ��������� �� ������
     */
    function UpdateMain($fid, $name, $surname, $email, $oldpwd, $pname, $try_pwd = 0){
        global $DB;
        $this->uname = $name;
        $this->usurname = $surname;
        $this->email = $email;
        $this->pname = $pname;
        $current = get_class($this);
        if ($try_pwd){
            $sql = "SELECT uid FROM users WHERE uid = ?i AND passwd = ?";
            $res = $DB->query($sql, $fid, users::hashPasswd(stripslashes($oldpwd)));
        } else $error = $this->Update($fid, $res, "AND passwd = '".users::hashPasswd(stripslashes($oldpwd))."'");
        $error = parse_db_error($error);
        if ($error) return ($error);
        if ($try_pwd){ if (pg_num_rows($res) == 0) $error = 1;
        } else {
            if (pg_affected_rows($res) == 0) $error = 1;
            else {
                $_SESSION['name'] = $name;
                $_SESSION['surname'] = $surname;
            }
        }
        return ($error);
    }
    


    /**
     * ���������� ������ ������������
     *
     * @param integer $fid               id ������������
     * @param string $oldpwd             ������ ������
     * @param string $pwd                ����� ������
     * @param string $try_pwd            ���� ��������� ������ ��� �����
     *
     * @return string                    ��������� �� ������
     */
    function UpdatePwd($fid, $oldpwd, $pwd, $try_pwd = 0){
        global $DB;
        $this->passwd = $pwd;
        if ($try_pwd){
            $sql = "SELECT uid FROM users WHERE uid = ?i AND passwd = ?";
            $res = $DB->query($sql, $fid, users::hashPasswd(stripslashes($oldpwd)));
        } else $error = $this->Update($fid, $res, "AND passwd = '".users::hashPasswd(stripslashes($oldpwd))."'");
        $error = parse_db_error($error);
        if ($error) return ($error);
        if ($try_pwd){ if (pg_num_rows($res) == 0) $error = "���� ��������� �����������";
        } else {
            if (pg_affected_rows($res) == 0) $error = "���� ��������� �����������";
        }
        return ($error);
    }
    


    /**
     * �������� ������������ ���� ������������ �� ������� �������������
     *
     * @param integer $uid               id ������������
     * @param string $error              ������ ���� ������
     * @param string $fieldname          ����
     *
     * @return string                    �������� ����
     * 
     * @deprecated 
     */
    /*function GetField($uid, &$error, $fieldname) {
        $join = '';
        $current = get_class($this);
        $uid     = intval($uid);
        $sql = "SELECT $fieldname FROM $current $join WHERE (uid='$uid')";
        //$current = get_class($this);
        //$sql = "SELECT $fieldname FROM $current WHERE ($idname='$uid')";
        
        $res = pg_query_Ex($sql, true);
        $error .= pg_errormessage();
        if ($error) $error = parse_db_error($error);
        else{
            list($ret) = pg_fetch_row($res);
        }
        return ($ret);
    }*/
    
    /**
     * �������� ������������ ���� ������������ �� ������� �������������
     *
     * @global DB $DB
     * @staticvar type $user
     * @param integer $uid               id ������������
     * @param string $error              ������ ���� ������ @deprecated 
     * @param string $fieldname          ����
     * @param type $nocache
     * @return mixed
     * 
     * @todo ������ ���������� $error �� ���� �����
     */
    function GetField($uid, & $error, $fieldname, $nocache = true) {
        global $DB;
        static $user;
        if(!$uid) return;
        if(!$fieldname) return;
        
        if($_SESSION['uid'] == $uid && isset($_SESSION[$fieldname]) && $nocache) {
            return $_SESSION[$fieldname];
        } if(isset($user[$uid][$fieldname]) && $nocache) {
            return $user[$uid][$fieldname];
        } else {
            $current = get_class($this);
            $sql     = "SELECT {$fieldname} FROM {$current} WHERE uid=?i";
            $user[$uid][$fieldname] = $DB->val($sql, $uid);
            $error .= $DB->error;
            if ($error) $error = parse_db_error($error);
            return ($user[$uid][$fieldname]);
        }
     }
    
    /**
     * �������� ������������ �� PRO
     *
     * @param integer $uid               id ������������
     * @param string $error              ������ ���� ������
     *
     * @return mixed                     1 - PRO, 0 - �� PRO, NULL - ������
     */
    function IsPro($uid, &$error) {
        global $DB;
        $sql = "SELECT uid FROM users WHERE uid=?i AND is_pro=true";
        $is_pro = $DB->val($sql, $uid);
        $error = $DB->error;
        if($error) {
            $error = parse_db_error($error);
            $ret = NULL;
        } else {
            $ret = ($is_pro?1:0);
        }
        return $ret;
    }
    
    public function IsVerified()
    {
        return $this->is_verify == 't';
    }

    
    
    /**
     * ��� ������ �������� �����?
     * 
     * @return type
     */
    public function isCurrent()
    {
        return ($this->uid > 0) && ($this->uid == @$_SESSION['uid']);
    }



    /**
     * �������� ������ ������������ (���, �������, e-mail, ����)
     *
     * @param integer $uid               id ������������
     * @param string $error              ������ ���� ������
     *
     * @return mixed                     ������ ������������ ��� ������ � ������ ��������
     */
    function GetName($uid, &$error){
        global $DB;
        $current = get_class($this);
        $sql = "SELECT uname, usurname, login, photo FROM $current WHERE uid=?i";
        $ret = $DB->row($sql, $uid);
        $error = $DB->error;
        if ($error) $error = parse_db_error($error);
        return ($ret);
    }



    /**
     * ���������� id ������������ �� ��� ������
     *
     * @param string $error             ���������� ��������� �� ������
     * @param string $login             ����� ������������
     *
     * @return integer                  id ������������ ��� 0 � ������ ��������
     */
    function GetUid($error, $login=''){
        global $DB;
        if (!$login && $this)
            $login = $this->login;
        if (!$login) return 0;
        $sql = "SELECT uid FROM users WHERE (lower(login)=?)";
        $res = $DB->query($sql, strtolower($login));
        $error = $DB->error;
        if ($error) $error = parse_db_error($error);
        else{
            list($result) = pg_fetch_row($res);
        }
        return ($result);
    }
    


    /**
     * �������� ����� ������� ������������
     *
     * @param integer $login             ����� ������������
     * @param string $error              ������ ���� ������
     *
     * @return mixed                     ������ ������������ ��� ������ � ������ ��������
     */
    function GetRole($login, &$error){
        global $DB;
    	$login = strtolower($login);
        $sql = "SELECT role FROM users WHERE (lower(login)=?)";
        $res = $DB->query($sql, $login);
        $error = $DB->error;
        if ($error) $error = parse_db_error($error);
        else{
            list($ret) = pg_fetch_row($res);
        }
        return ($ret);
    }
    


    /**
     * ��������� ���� ������������
     *
     * @param integer $fid               id ������������
     * @param object $foto               ���� ����������
     * @param integer $del               ���� �������� ���� (1 - �������, 0 - �����������)
     * @return string                    ����� ������ � ������ ��������
     */
    function UpdateFoto( $fid, $foto, $del ){
        // ���� ���� ������ ������ ���� ���������� ��� �� ������������� - ����� ������� ���������
        $aChange  = $GLOBALS['DB']->row( "SELECT id, old_val, new_val FROM users_change WHERE user_id = ?i AND ucolumn = 'photo'", $fid );
        $aDelFile = array(); // ����� ������� ����� ����� ������� �����
        
        $dir = get_login($fid);
        $err = "";
        if (!$dir) $error = "��� ������������ �� ����������";
        $this->photo = $this->GetField($fid, $err, "photo");
        $old_foto = $this->photo;
        $error .= $err;
        if ($del == 1){
            $this->photo = "";
        } else {
            if ($foto && !$error) {
                $foto->max_size = 102400000;
                $foto->max_image_size = array('width'=>100, 'height'=>100, 'less' => 0);
                $foto->resize = 1;
                $foto->proportional = 1;
                $foto->topfill = 1;
                $cFile->crop = 1;
                $foto->allowed_ext = array_diff( $GLOBALS['graf_array'], array('swf', 'gif') );
                $this->photo = $foto->MoveUploadedFile($dir."/foto");
                $error .= $foto->StrError('<br />');
                if (!$error && !$foto->img_to_small("sm_".$this->photo,array('width'=>50, 'height'=>50)))
                    $error .= "���������� ��������� ��������.";
            }
        }
        
        if ( !$error ) {
            $error .= $this->Update($fid, $res);
            
            // ������������ ����� ����� ����� �������
            if ( $del == 1 ) { // ������� ������
                if ( $aChange ) { // ���� ������� ������ ������� �� ������ �������� - �� ������� ���
                    if ( $aChange['old_val'] ) {
                        $aDelFile[] = $aChange['old_val'];
                    }
                    
                    $aDelFile[] = $aChange['new_val'];
                }
                else { // ����� ������ ������� ������
                    $aDelFile[] = $old_foto;
                }
            }
            elseif ( $foto ) { // ������ ������
                if ( $aChange && $aChange['new_val'] ) { // ������� ������ ������������� ������, ���� ����
                    $aDelFile[] = $aChange['new_val'];
                }
            }
        }
        
        // �������� �� ������ ������ (���� ��� ������ ��� ���������� ����������)
        if ( $aDelFile && !$error ) {
            foreach ( $aDelFile as $file ) {
                $foto->Delete(0, "users/".substr($dir, 0, 2)."/".$dir."/foto/", $file);
                $foto->Delete(0, "users/".substr($dir, 0, 2)."/".$dir."/foto/", "sm_".$file);
            }
        }
        
        return ($error);
    }
    


    /**
     * �������� ������ ������������� � ��������
     *
     * @param string $where              ������� WHERE
     * @param string $order_by           ������� ORDER
     *
     * @return string                    ����� ������ � ������ ��������
     */
    function GetUsers($where=NULL, $order_by=NULL)
    {
      global $DB;
      $current = get_class($this);
      $where = $where===NULL ? '' : "WHERE {$where}";
      $order_by = $order_by===NULL ? '' : "ORDER BY {$order_by}";
      $sql = "SELECT * FROM {$current} {$where} {$order_by}";
      $ret = $DB->rows($sql);
      return ($ret?$ret:NULL);
    }
    


    /**
     * �������� ������ ������������� � ��������
     *
     * @param integer $size              ���������� ������������ �������
     * @param string $addit              ������� WHERE
     * @param string $order              ������� ORDER
     * @param integer $frl_pp            ���������� �� �������� (LIMIT)
     * @param integer $offset            OFFSET
     * @param integer $orderby           ����� ����������
     *                                   - opinions - �� �������
     *                                   - teams - �� ����������� � �������
     *                                   - recomendations - �� �������������
     *                                   - visits - �� ������������
     *                                   - default - �� ��������
     *
     * @return mixed                     ������ ������������� ��� ����� ������ � ������ ��������
     */
    function GetAll(&$size, $addit = "", $order = "uid", $frl_pp = FRL_PP, $offset = 0, $orderby = "general"){
        global $DB;
        $size = 0;
        $current = get_class($this);
        if ($addit) 
            $addit = " WHERE freelancer.is_active = true AND ".$addit;
        else 
            $addit = " WHERE freelancer.is_active = true ";
        $ssum = "";
        if ($current=="users") $sql = "SELECT COUNT(*) FROM users $addit";
        else $sql = "SELECT COUNT(*) FROM freelancer $addit";
        if ($current == "freelancer") {
            $ssum = ", ssum, professions.name as profname, rating_get(rating, is_pro, is_verify, is_profi) as rating";
            switch ($orderby){
                case "opinions":
                    $ssum = ", professions.name as profname, (zin(sg)-zin(sl)) as ssum,  rating_get(rating, is_pro, is_verify, is_profi) as rating, sg, se, sl";
                    $order = "ssum DESC, rating DESC, freelancer.".$order;
                    $frladd = "LEFT JOIN professions ON professions.id=freelancer.spec LEFT JOIN (SELECT fid, COUNT(*) as sg FROM freelancer LEFT JOIN opinions ON freelancer.uid=opinions.touser_id WHERE opinions.rating > 0 GROUP BY fid) as tst ON tst.uid=freelancer.uid
                LEFT JOIN (SELECT fid, COUNT(*) as se FROM freelancer LEFT JOIN opinions ON freelancer.uid=opinions.touser_id WHERE opinions.rating = 0 GROUP BY fid) as tst1 ON tst1.fid=freelancer.uid
                LEFT JOIN (SELECT fid, COUNT(*) as sl FROM freelancer LEFT JOIN opinions ON freelancer.uid=opinions.touser_id WHERE opinions.rating < 0 GROUP BY fid) as tst2 ON tst2.fid=freelancer.uid";
                    break;
                case "teams":
                    $order = "ssum IS NULL, ssum DESC, rating DESC, freelancer.".$order;
                    $frladd = " LEFT JOIN professions ON professions.id=spec LEFT JOIN (SELECT target_id, COUNT(target_id) as ssum FROM teams INNER JOIN employer on fid=user_id GROUP BY target_id) as tst ON tst.target_id=freelancer.uid";
                    break;
                case "recomendations":
                    $order = "ssum IS NULL, ssum DESC, rating DESC, freelancer.".$order;
                    $frladd = "LEFT JOIN professions ON professions.id=spec LEFT JOIN (SELECT target_id, COUNT(target_id) as ssum FROM teams INNER JOIN freelancer on fid=user_id GROUP BY target_id) as tst ON tst.target_id=freelancer.uid";
                    break;
                case "visits":
                    $order = "ssum DESC, rating DESC, freelancer.".$order;
                    $ssum = ", hits as ssum, professions.name as profname";
                    $frladd = "LEFT JOIN professions ON professions.id=spec";
                    break;
                default:
                    /*$frladd = "LEFT JOIN (SELECT fid, ssum(ops, tfs, tes) as ssum FROM (
                    SELECT fid, sum(opinions.rating) as ops, tfs, tes FROM freelancer LEFT JOIN opinions ON freelancer.fid=opinions.touser_id
                    LEFT JOIN (SELECT target_id, COUNT(target_id) as tfs FROM teams INNER JOIN freelancer on fid=user_id GROUP BY target_id) as recom ON freelancer.fid=recom.target_id
                    LEFT JOIN (SELECT target_id, COUNT(target_id) as tes FROM teams INNER JOIN employer on fid=user_id GROUP BY target_id) as tms ON freelancer.fid=tms.target_id
                    GROUP BY fid, tfs, tes) as tst1) as tst ON tst.fid=uid";*/
                    $ssum = ", rating_get(rating, is_pro, is_verify, is_profi) as rating as ssum, professions.name as profname";
                    $order = "ssum DESC, freelancer.".$order;
                    $frladd = "LEFT JOIN professions ON professions.id=spec";
            }
        }
        $size = $DB->val($sql);
        $error = $DB->error;
        if (!$error && $size >= $offset) {
            if ($current=="users") $sql = "SELECT uname, usurname, login, photo, is_banned, email, last_ip, email, reg_ip, role FROM users $addit ORDER BY $order LIMIT $frl_pp OFFSET $offset";
            else {
                $sql = "SELECT uname, usurname, login, photo, is_banned, email, is_pro as payed, role $ssum FROM freelancer
                 $frladd $addit ORDER BY payed, $order LIMIT $frl_pp OFFSET $offset";
            }
            $ret = $DB->rows($sql);
            $error = $DB->error;
            if ($error) $error = parse_db_error($error);
        }
        return $ret;
    }
    


    /**
     * ��������� ������ �������� ������������
     *
     * @param integer $fid                       id ������������
     * @param integer $newmsgs                   ���� �������� �� ����� ���������
     * @param integer $vacan                     ���� �������� �� ��������
     * @param integer $comments                  ���� �������� �� �����������
     * @param integer $opin                      ���� �������� �� ����� ������
     * @param integer $prj_comments              ���� �������� �� ����������� � ��������
     * @param integer $commune_subscr            ���� �������� �� ��������� ����������
     * @param integer $commune_top_subscr        ���� �������� �� ���� ����������
     * @param integer $adm_subscr                ���� �������� �� ��������� �� �������������
     * @param integer $contest_subscr            ���� �������� �� ����������� � ���������
     * @param integer $defilecomments            ���� �������� �� ����������� � ������
     * @param integer $articlescomments          ���� �������� �� ����������� � �������
     * @param integer $shop                      ����������� � ������ � ��������
     * @param integer $paid_advice               ����������� � ������� �������������
     * @param integer $payment                   ����������� � ��������
     *
     * @return string                            ����� ������ � ������ ��������
     */
    function UpdateSubscr( $fid, $newmsgs, $vacan, $comments, $opin, $prj_comments, $commune_subscr, $commune_top_subscr, $adm_subscr, $contest_subscr, $team, $defilecomments, $articlescomments, $shop, $paid_advice, $payment) {
      
        $this->subscr = (int)$newmsgs.(int)$vacan.(int)$comments.(int)$opin.(int)$prj_comments.(int)$commune_subscr.(int)$commune_top_subscr.(int)$adm_subscr.(int)$contest_subscr.(int)$team.(int)$defilecomments.(int)$articlescomments.(int)'1'.(int)$shop.(int)$paid_advice.(int)$payment;
        while (strlen($this->subscr) < $GLOBALS['subscrsize'])
        $this->subscr .= '0';
        $error = $this->Update($fid, $res);
        return ($error);
    }
    
    
    
    /**
     * ���������� ������ UpdateSubscr
     * �� ������������ ��� �����������, ���������� ������ ������ (�������� ��������� ��������)
     *      * 
     * @param int $uid
     * @param array/string $subscr
     * @return array
     */
    function UpdateSubscr2( $uid, $subscr){
        
        $func_map = function($value){return (intval($value) > 0)?1:0;};
        $this->subscr = (is_array($subscr))?implode('', array_map($func_map,$subscr)):$subscr;
        
        while (strlen($this->subscr) < $GLOBALS['subscrsize'])
            $this->subscr .= '0';

       $error = $this->Update($uid, $res);
       return ($error);       
    }




    /**
     * �����������/���������� �� �������� � ����� ����� � �����������
     * @param integer $user_id
     * @param boolean $communeSubscr
     */
    function UpdateCommuneSubscr ($user_id, $communeSubscr) {
        $subscr = $this->GetField($user_id, $error, 'subscr');
        if ($subscr{6} == $communeSubscr) {
            return;
        }
        $subscr{6} = (int)(bool)$communeSubscr;
        $this->subscr = $subscr;
        $this->Update($user_id, $res);
    }
    
    /**
     * ��������� ��������� �������/��������
     * 
     * @param  int $sUid uid ������������
     * @param  array $aSettings ������ �����, ������� ����� �������� (��������� => ��������)
     *         �������� array( 'question_button' => 1, 'safety_phone' => 0 ); 
     *         ����� ����������� �� ���.
     * @return bool true � ������ ������, false � ������ �������
     */
    function updateSettings( $sUid = 0, $aSettings = array() ) {
        // ��������� �����
        $aBits   = array(
            'safety_phone',   // ���������� �������� � ��������
            'question_button', // ���������� ������ "� ��� ���� ������?"
            'promo_block', // ���������� ���� "������� ������ � �������� �������� �����"
            'direct_external_links', // �� ���������� �������� "������� �� ������� ������" a.php
            'sbr_slash_show', // ���������� ��� �����-����
            'chat', // �������� ���������
            'chat_sound' // �������� ���� � ����������
        );
        
        $bReturn = true;
        $sError  = '';
        $sField  = $this->GetField( $sUid, $sError, 'settings' );
        
        if ( !$sError && $sField ) {
        	if ( is_array($aSettings) && count($aSettings) ) {
        	    foreach ( $aSettings as $key => $val ) {
            		if ( in_array($key, $aBits) ) {
            			$sField[ array_search($key, $aBits) ] = $val;
            		}
            	}
            	
            	$user = new users();
            	$user->settings = $sField;
            	$sError = $user->Update( $sUid, $res );
            	
            	if ( $sError ) {
            		$bReturn = false;
            	}
        	}
        }
        else {
            $bReturn = false;
        }
        
        return $bReturn;
    }


    /**
     * ��������� ���� ��� ����������� ������
     *
     * @param integer $mail              e-mail ������������
     * @param string $error              ������
     *
     * @return array                     ������ ������������
     */
    function Remind($mail, &$error){
        global $DB;
        $ret = 0;
        $sql = "SELECT login, uid FROM users WHERE lower(email)=?";
        $res = $DB->query($sql, strtolower($mail));
        $error = $DB->error;
        if (!$error) {
            $size = pg_num_rows($res);
            if (!$size) $error = "��������� ����������� ����� � ���� �� ������";
            else {
                require_once(ABS_PATH . "/classes/codes.php");
                $code = new codes();
                list($login, $uid) = pg_fetch_row($res);
                $code->user_id = $uid;
                $code->type = 1;
                $ret = $code->Add($error);
                $this->GetUser($login);
            }
        }
        return ($ret);
    }
    
    /**
     * ������������ ������ � �������� �������� ������ ����� �������?
     * 
     * @param  string $sParam ���� �����, ���� email
     * @param  bool $bIsEmail ����������� ���������� true ���� ������ $sParam �������� email
     * @return bool true - ������ ����� �������, false - ��� ������
     */
    function isRemindByPhoneOnly( $sParam = '', $bIsEmail = '' ) {
        $sWhere = $bIsEmail ? ' email = ? ' : ' lower(login) = ? ';
        $sParam = $bIsEmail ? $sParam       : strtolower( $sParam );
        $sOnly  = $GLOBALS['DB']->val( 'SELECT safety_only_phone FROM users WHERE ' . $sWhere, $sParam );
        
        return ( $sOnly == 't' );
    }

    /**
     * �������� ������ ������������� � ��������
     *
     * @param integer $size              ���������� ������������ �������
     * @param unknown $info              �� ������������
     * @param unknown $fpinfo            �� ������������
     * @param integer &$size             ���������� ������������ �������
     * @param string $addit              ������� WHERE
     * @param string $order              ������� ORDER
     * @param integer $frl_pp            ���������� �� �������� (LIMIT)
     * @param integer $offset            OFFSET
     *
     *
     * @return mixed                     ������ ������������� ��� ����� ������ � ������ ��������
     */
    function GetAllEx(&$size, &$info, &$fpinfo, $addit = "", $order = "uid", $frl_pp = FRL_PP, $offset = 0){
        global $DB;
        $size = 0;
        if (get_class($this) != "users") return 0;
        if ($addit) $addit = " AND ".$addit;
        $addit = "WHERE email IS NOT NULL". $addit;
        $sql = "SELECT COUNT(*) FROM users LEFT JOIN login_change ON user_id=users.uid  $addit";
        $size = $DB->val($sql);
        $error = $DB->error;
        if (!$error && $size >= $offset) {
            $sql = "SELECT users.uid, uname, usurname, login, photo, is_banned, ban_where, email, last_ip, email, reg_ip, s.sum, icq, hits, ban_reason, warn, role, old_login, safety_phone, safety_only_phone, users.active, account.is_block as is_block_money FROM users
            LEFT JOIN account ON account.uid = users.uid
            LEFT JOIN (SELECT uid, sum(ammount) FROM billing GROUP BY uid) as s ON s.uid = users.uid
            LEFT JOIN login_change ON user_id=users.uid 
             $addit ORDER BY $order LIMIT $frl_pp OFFSET $offset";
          
            $ret = $DB->rows($sql);
            $error = $DB->error;
            if ($error) $error = parse_db_error($error);
        }
        return $ret;
    }



    /**
     * @deprecated ��� ������� �� ������������ - ������ ��� �������� ����� � �������� ���� ��������� ��������
     * @see users::setUserBan()
     * 
     * ����� ������������
     *
     * @param string  $login             login ������������, �������� ����� ��������
     * @param boolen  $force             ���� ����������
     * @param string  $reason            �������
     * @param string  $comment           ����������� ����������
     * @param integer $time              ���-�� ���� ���� ��� 0, ���� �����������
     * @param integer $uids              uid ������������, �������� ����� ��������, ���� �������, �� $login �� ������������
     * @param boolean $razban            ���������� � 1, ���� ����� ��������� ������������
     * @param int $no_send �����������. ���������� � 1 ���� �� ����� ��������� ����� � ��� ��� �� �������.
     * @param integer $where             ��� �������� ������������ (0 - �����, 1 - � ������)
     *
     * @return string                    ����� ������
     */
    /*
    function Ban($login, $force, $reason=0, $comment='',$time=0, $uids=0, $razban=0, $where=0, $no_send = 0){
        global $session;
        global $DB;
        if ($force && $login){
            $uid = ($uids ? $uids : $this->GetUid($error, $login));
            $this->GetUser($login);
            
            if ($this->ban_where && !$this->is_banned && !$where) {
                $user = new users();
                $user->ban_where = 0;
                $error .= $user->Update($uid, $res);
                $this->ban_where = 0;
            }
            
            if (!$this->is_banned && !$this->ban_where && $uid && !$razban) {

                if ($where) {
                    if ($time) { $to="now() + '".$time." day'::interval"; } else { $to="NULL"; }
                    $sql =  "INSERT INTO users_ban ( uid, \"from\", \"to\", reason, comment, admin, \"where\") VALUES (".$uid.", now(), ".$to.", '".$reason."', '".$comment."', '".$_SESSION["uid"]."', '".$where."');
                             UPDATE users SET ban_where=".$where." WHERE uid=?i;";
                    $res = $DB->query($sql, $uid);
                    //require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
                    //$mail=new smail();
                    //$mail->SendBanBlog($login);
                }
                else {
                    if ($time) { $to="now() + '".$time." day'::interval"; } else { $to="NULL"; }
                    $add = "UPDATE freelancer SET mailer=0 WHERE uid='".$uid."';";
                    $sql = "INSERT INTO users_ban ( uid, \"from\", \"to\", reason, comment, admin) VALUES (".$uid.", now(), ".$to.", '".$reason."', '".$comment."', '".$_SESSION["uid"]."');
                            UPDATE users SET is_banned=B'1', subscr=B'".str_repeat('0',$GLOBALS['subscrsize'])."' WHERE uid=?i;$add";

                    $res = $DB->query($sql, $uid);
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
                    projects::CloseAllUserPrj($uid);
                    
                    users::banSuspiciousUser( $uid );
                    
                    if ( !$no_send ) {
                        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
                        $mail=new smail();
                        $mail->SendBan($login);
                    }
                }
                $session->logout($login);
            }
            else {
                if ($where) {
                    $sql .= "UPDATE users_ban SET \"to\"=NULL WHERE uid=?i AND \"where\"='".$where."' ;";
                    $res = $DB->query($sql, $uid);
                    $user = new users();
                    $user->ban_where = 0;
                    $error .= $user->Update($uid, $res);
                }
                else {
                    $user = new users();
                    $user->is_banned = 0;
                    $error .= $user->Update($uid, $res);
                    
                    users::approveSuspiciousUser( $uid );
                }
            }
            $memBuff = new memBuff();
            $memBuff->touchTag("msgsCnt");

            //$this->is_banned = ($this->GetField($uid, $error, "is_banned")+1)%2;

        }
        return ($error);
    }
    */
    
    /**
     * ������������� ��� ������������.
     * 
     * @param  int $uid UID ������������.
     * @param  int $ban_where ����� ��� ������: 0 - �� ���� �����, 1 - � ������.
     * @param  string $reason �������
     * @param  int $reason_id ID �������, ���� ��� ������� �� ������ (������� admin_reasons)
     * @param  string $date_to ���� ��������� �������� ����, ��� ������ ������ ���� ��������
     * @param  int $no_send �����������. ���������� � 1 ���� �� ����� ��������� ����� � ��� ��� �� �������.
     * @param  bool $self_deleted ���������� � true ���� ������� ��������� ������������� ��������������
     * @return int ID ����� ������ � ���� ������������ ��� NULL � ������ ������.
     */
    function setUserBan( $uid, $ban_where, $reason, $reason_id, $date_to = '', $no_send = 0, $self_deleted = false ) {
        if ( !$no_send ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php' );
            $mail = new smail();
        }
        
        $date_to = $date_to ? $date_to : NULL;
        
        $GLOBALS['DB']->start();
        
        if ( $ban_where ) {
            // ��� � ������
            $sId = $GLOBALS['DB']->val( 'INSERT INTO users_ban ( uid, "from", "to", reason, comment, admin, "where") 
                VALUES (?i, now(), ?, ?i, ?, ?i, ?i) RETURNING id', 
                $uid, $date_to, $reason_id, $reason, $_SESSION["uid"], $ban_where
            );
            
            if ( $sId ) {
                $GLOBALS['DB']->query( 'UPDATE users SET ban_where = ?i WHERE uid=?i', $ban_where, $uid );
                
                if ( !$GLOBALS['DB']->error && !$no_send ) {
                    $mail->SendBlogsBan2( $uid, $reason );
                }
            }
        }
        else {
            // ��� �� ���� �����
            $sId = $GLOBALS['DB']->val( 'INSERT INTO users_ban ( uid, "from", "to", reason, comment, admin) 
                VALUES (?i, now(), ?, ?i, ?, ?i) RETURNING id', 
                $uid, $date_to, $reason_id, $reason, $_SESSION["uid"]
            );
            
            if ( $sId ) {
                $sSubscr = str_repeat( '0', $GLOBALS['subscrsize'] );
                
                if ($self_deleted) { // ���� ������� ��������� �������������
                    $GLOBALS['DB']->query( 'UPDATE users SET is_banned=B\'1\', subscr=B\'' . $sSubscr . '\', self_deleted = TRUE WHERE uid=?i;
                        UPDATE freelancer SET mailer=0 WHERE uid=?i;', 
                        $uid, $uid
                    );
                } else { // ���� ������ �������
                    $GLOBALS['DB']->query( 'UPDATE users SET is_banned=B\'1\', subscr=B\'' . $sSubscr . '\' WHERE uid=?i;
                        UPDATE freelancer SET mailer=0 WHERE uid=?i;', 
                        $uid, $uid
                    );
                }
                
                if ( !$GLOBALS['DB']->error ) {
                    $this->_afterUserBan( $uid );
                    
                    if ( !$no_send ) {
                        $mail->SendBan2( $uid, $reason );
                    }
                }
            }
        }
        
        if ( $sId && !$GLOBALS['DB']->error ) {
            $GLOBALS['DB']->commit();
            $memBuff = new memBuff();
            $memBuff->touchTag("msgsCnt");
        }
        else {
            $GLOBALS['DB']->rollback();
        }
        
        return $sId;
    }
    
    /**
     * �������������� �������� ��� ���� ������������ �� ���� �����.
     * 
     * @param int $uid UID ������������.
     */
    function _afterUserBan( $uid ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/opinions.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        
        $opinions = new opinions();
        $opinions->HideOpin( $uid );
        projects::CloseAllUserPrj($uid);
        users::banSuspiciousUser( $uid );
        $mess = new messages();
        $mess->clearMessageSender($uid);
    }
    
    /**
     * �������� ������� ��� ������������.
     * 
     * @param  int $uid UID ������������.
     * @param  int $ban_where_old ����� ��� ���: 0 - �� ���� �����, 1 - � ������.
     * @param  int $ban_where_new ����� ��� �����: 0 - �� ���� �����, 1 - � ������.
     * @param  string $reason �������
     * @param  int $reason_id ID �������, ���� ��� ������� �� ������ (������� admin_reasons)
     * @param  string $date_to ���� ��������� �������� ����, ��� ������ ������ ���� ��������
     * @return bool true - �����, false - ������
     */
    function updateUserBan( $uid, $ban_where_old, $ban_where_new, $reason, $reason_id, $date_to = '' ) {
        $GLOBALS['DB']->start();
        
        $date_to = $date_to ? $date_to : NULL;
        $sWhere  = $ban_where_old ? 'b.where = 1 AND u.ban_where = 1' : '(b.where = 0 OR b.where IS NULL) AND u.is_banned = B\'1\'';
        $sQuery  = 'UPDATE users_ban SET 
            "to" = ?, reason = ?i, comment = ?, admin = ?i, "where" = ?i 
            WHERE id = (
                SELECT MAX(b.id) AS id FROM users_ban b 
                INNER JOIN users u ON b.uid = u.uid 
                WHERE b.uid = ?i AND '. $sWhere .' 
                GROUP BY b.uid
            )  RETURNING id';
        
        $sId = $GLOBALS['DB']->val( $sQuery, $date_to, $reason_id, $reason, $_SESSION["uid"], $ban_where_new, $uid );
        
        if ( $sId && !$GLOBALS['DB']->error && $ban_where_old != $ban_where_new ) {
            // ���� ��� ���� ���������
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php' );
            $mail = new smail();
            
            if ( $ban_where_new ) {
                // ������ �� ��� � ������
                $GLOBALS['DB']->query( 'UPDATE users SET is_banned=B\'0\', ban_where = ?i WHERE uid=?i', $ban_where_new, $uid );
                
                if ( !$GLOBALS['DB']->error ) {
                    $mail->SendBlogsBan2( $uid, $reason );
                }
            }
            else {
                // ������ �� ��� �� ���� �����
                $sSubscr = str_repeat( '0', $GLOBALS['subscrsize'] );
                
                $GLOBALS['DB']->query( 
                    'UPDATE users SET is_banned=B\'1\', ban_where = 0, subscr=B\'' . $sSubscr . '\' WHERE uid=?i;
                    UPDATE freelancer SET mailer=0 WHERE uid=?i;', 
                    $uid, $uid
                );
                
                if ( !$GLOBALS['DB']->error ) {
                    $mail->SendBan2( $uid, $reason );
                    $this->_afterUserBan( $uid );
                }
            }
        }
        
        if ( $sId && !$GLOBALS['DB']->error ) {
            $GLOBALS['DB']->commit();
            return true;
        }
        else {
            $GLOBALS['DB']->rollback();
            return false;
        }
    }
    
    /**
     * ������� ��� � ������������.
     * 
     * @param  int $uid UID ������������.
     * @param  int $ban_where ����� ��� �������: 0 - �� ���� �����, 1 - � ������.
     * @return string ��������� �� ������ ��� ������ ������.
     */
    function unsetUserBan( $uid, $ban_where ) {
        $user = new users();
        
        if ( $ban_where ) {
            // � ������
            $GLOBALS['DB']->query( 'UPDATE users_ban SET "to" = NULL WHERE uid = ?i AND "where" = ?i', 
                $uid, $ban_where
            );
            
            $sError = $GLOBALS['DB']->error;
            
            if ( !$sError ) {
                $user->ban_where = 0;
                $sError = $user->Update( $uid, $res );
            }
        }
        else {
            // �� ���� �����
            $user->is_banned = 0;
            $user->self_deleted = "FALSE"; // ���� ������� ������ ��������������
            $sError = $user->Update( $uid, $res );
            
            if ( !$sError ) {
                users::approveSuspiciousUser( $uid );
            }
        }
        
        $memBuff = new memBuff();
        $memBuff->touchTag("msgsCnt");
        
        // ��������������� ������ � ���������������� ������������ � ������ ��� ���������
        /*require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/session_Memcached.php");
        $user->GetUserByUID($uid);
        $antiUid = $user->anti_uid;
        $antiUser = new users;
        $antiUser->GetUserByUID($antiUid);        
        $session = new session;
        $session->UpdateAntiuser($antiUser->login, $user);*/
        
        return $sError;
    }

    /**
     * �������� ���������� �������������
     *
     * @param integer $uid               id ������������
     * @param integer $where             ������� ��� ������� ������������ (0 - �����, 1 - � ������)
     *
     * @return array                     ������ �������������
     */
    function GetBan ($uid,$where=0) {
        $where = ($where ? $where : '0 OR "where" IS NULL');
        $sql =  "SELECT * FROM users_ban WHERE uid='".$uid."' AND (\"where\"= {$where}) ORDER BY \"from\" DESC LIMIT 1; ";
        $res = pg_query_Ex($sql, true);
        return pg_fetch_assoc($res);
    }
    


    /**
     * �������� ��� �������������, ��������� �������
     *
     * @return boolean                     true � ������ ������
     */
    function GetBanTimeout () {
        global $DB;
        $sql =  "SELECT * FROM users_ban WHERE \"to\">'epoch' AND \"to\"<now()";
        $res = $DB->rows($sql);
        $sql='';
        if($res) {
            require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php');
            $objUser = new users();
            
            foreach ($res as $banned){
                $sql .= "UPDATE users_ban SET \"to\"=NULL WHERE uid='".$banned["uid"]."';
                UPDATE users SET is_banned=B'0', ban_where=0 WHERE uid='".$banned["uid"]."';
                ";
                
                // ����� ��� ��������� ��������
                $nLogActId = $banned['where'] ? 6 : 4; // (0 - �����, 1 - �����)
                $sWhere    = $banned['where'] ? '� ������' : '�� �����';
                $objUser->GetUserByUID( $banned["uid"] );
                $sObjName  = $objUser->uname. ' ' . $objUser->usurname . '[' . $objUser->login . ']';
                $sObjLink  = '/users/' . $objUser->login;
                $sReason   = '�������������� ������������� ' . $sWhere . ' �� ��������� ����� ����������';
                admin_log::addLog( 
                    admin_log::OBJ_CODE_USER, $nLogActId, $banned['uid'], $banned['uid'], 
                    $sObjName, $sObjLink, 0, '', 0, $sReason, null, '', $banned['admin'] 
                );
            }
        }
        if ($sql) $res = $DB->query($sql);
        return true;
    }
    


    /**
     * �������� ������� ����
     *
     * @param string $login              login ������������
     * @param string $reason             ������� ����
     *
     * @return string                    ����� ������
     */
    function Banreason($login, $reason){
        if ($reason && $login ) {
            $uid = $this->GetUid($error, $login);
            if ($this->GetField($uid, $error, "is_banned", false)>0){
                $this->ban_reason = substr($reason,0,100);
                $error .= $this->Update($uid, $res);
            }
        }
        return ($error);
    }

    
    
    /**
     * ���������� ������� ����
     *
     * @return string  ������� ����
     */
    function GetBanComment() {
        global $DB;
        return $DB->val("SELECT comment FROM users_ban WHERE uid = ?", $this->uid);
    }

    

    /**
     * �������������� ������������
     *
     * @param  string $login      login ������������
     * @param  string $reason     ������� ����
     * @param  int    $reasonId   ID �������, ���� ��� ������� �� ������ (������� admin_reasons, ��� act_id = 1)
     * @param  string $link       ������ ��� ���� ��������������
     * @return int                ID ��������������
     */
    function Warn( $login, $reason, $reasonId = null, $link, $userContent ) {
        global $DB;
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/messages.php';
        $uid = $this->GetUid($error, $login);
        
        $sId = $DB->val( 
            "INSERT INTO users_warns (uid, \"admin\", reason, warn_time, reason_id, users_content) VALUES(?i, ?i, ?, NOW(), ?, ?) RETURNING id", 
            $uid, $_SESSION['uid'], $reason, $reasonId, $userContent 
        );
        
        $this->warn = $this->GetField($uid, $error, "warn");
        messages::SendUserWarn($uid, $reason, $link);
        
        return $sId;
    }



    /**
     * ����� ��������������
     *
     * @param integer $warn_id           id ��������������
     *
     * @return string                    ����� ������
     */
    function UnWarn($warn_id) {
        global $DB;
        $sql = 'SELECT * FROM users_warns WHERE id = ?i';
        $row = $DB->row($sql, intval($warn_id));
        
        if ( $row && !$this->GetUserByUID($row['uid']) ) {
            $DB->query('DELETE FROM users_warns WHERE id = ?i', intval($warn_id));
            
            $this->warn = $this->GetField($row['uid'], $error, "warn");
            return ($error);
        }
        return 'Unknow warn_id';
    }



    /**
     * �������� ���������� � ��������������
     *
     * @param integer $warn_id           id ��������������
     *
     * @return array                     ����������
     */
    function GetWarn($warn_id) {
        global $DB;
        $sql = 'SELECT users_warns.*, users.login, admins.login AS admin_login, admins.uname AS admin_name, admins.usurname AS admin_uname
            FROM users_warns
            JOIN users ON users_warns.uid = users.uid
            LEFT JOIN users AS admins ON users_warns.admin = admins.uid
            WHERE users_warns.id = ?i';
        return $DB->row($sql, intval($warn_id));
    }
    


    /**
     * �������� ���������� � ��������������� ������������
     *
     * @param integer $uid               id ������������
     *
     * @return array                     ����������
     */
    function GetWarns($uid) {
        global $DB;
        $sql = 'SELECT users_warns.*, users.login, admins.login AS admin_login, admins.uname AS admin_name, admins.usurname AS admin_uname
            FROM users_warns
            JOIN users ON users_warns.uid = users.uid
            LEFT JOIN users AS admins ON users_warns.admin = admins.uid
            WHERE users_warns.uid = ?i 
            ORDER BY (users_warns.warn_time IS NULL), users_warns.warn_time DESC';
        return $DB->rows($sql, intval($uid));
    }
    


    /**
     * ������ ��������� �������������
     *
     * @param integer $nums              ���������� ���-�� �������
     * @param boolen  $error             ���������� ������, ���� ������� �������
     * @param integer $page              ����� ��������, ������� ����� ����������
     * @param string  $string            ����������
     * @param integer $show_type         ��� ���������� (0 - ���� �����������, 1 - ������ �� �����, 2 - ������ � ������, 3 - ������ � ���������������� (��� �����), 4 - � ���������������� � ������)
     * @param integer $admin             uid ������, ������������� �������� ����� ���������� ��� 0, ���� ��� ����
     *
     * @return array                     ������������
     */
    function GetBannedUsers(&$nums, &$error, $page, $sort, $show_type=0, $search='', $admin=0) {
        global $DB;
        $limit = USERS_ON_PAGE;
        $offset = $limit*($page-1);
        // ����������
        if ($search) {
            switch ($sort) {
                case 'btime':
                    $order = "ORDER BY (sh.from IS NULL), sh.from DESC";
                break;
                case 'utime':
                    $order = "ORDER BY (sh.to IS NULL), sh.to DESC";
                break;
                case 'login':
                    $order = "ORDER BY LOWER(sh.login)";
                break;
                default:
                    $order = "ORDER BY relevant DESC";
            }
        } else {
            switch ($sort) {
                case 'btime':
                    $order = "ORDER BY (".($show_type? "": "is_banned = B'0' OR ")."s.from IS NULL), s.from DESC";
                break;
                case 'utime':
                    $order = "ORDER BY (".($show_type? "": "is_banned = B'0' OR ")."s.to IS NULL), s.to DESC";
                break;
                case 'login':
                    $order = "ORDER BY LOWER(users.login)";
                break;
                default:
                    $order = "ORDER BY users.uid";
            }
        }
        // ��� ���������
        switch ($show_type) {
            case 1:  $w = "(users.is_banned = B'1' AND users.ban_where = 0)"; $iw = "(users_ban.where = 0 OR users_ban.where IS NULL)"; break;
            case 2:  $w = "(users.ban_where = 1)"; $iw = "(users_ban.where = 1)"; break;
            case 3:  $w = "(users.warn > 0 AND users.is_banned = B'0' AND users.ban_where = 0)"; break;
            case 4:  $w = "(users.warn > 0)"; break;
            default: $w = "(users.is_banned = B'1' OR users.ban_where <> 0 OR users.warn > 0)";
        }
        if ($show_type <> 3 && $show_type <> 4) {
            $se = ", s.reason AS ban_reason, s.comment AS admin_comment, s.admin AS admin, s.from AS from, s.to AS to, s.admin_login AS admin_login";
            $ja = "
                SELECT users_ban.*, admins.login AS admin_login
                FROM users_ban
                JOIN (
                    SELECT uid, MAX(id) AS id
                    FROM users_ban
                    " . ($iw? "WHERE $iw": "") . "
                    GROUP BY uid
                ) AS b ON users_ban.id = b.id
                LEFT JOIN users AS admins ON admins.uid = users_ban.admin
                " . ($admin? "WHERE users_ban.admin = '$admin'": "") . "
            ";
            $jn = "LEFT JOIN ($ja) AS s ON s.uid = users.uid";
        } else {
            if ($sort == 'utime' || $sort == 'btime') {
                $se = ", s.from";
                $ja = "SELECT uid, MAX(warn_time) AS from FROM users_warns".($admin? " WHERE admin = '$admin'": '')." GROUP BY uid";
                $jn = "LEFT JOIN (SELECT uid, MAX(warn_time) AS from FROM users_warns GROUP BY uid) AS s ON users.uid = s.uid";
            } else {
                $ja = "SELECT DISTINCT uid FROM users_warns".($admin? " WHERE admin = '$admin'": '');
            }
        }
        // �������� ������ + ������ �� ��������� ���-�� �������
        if ($admin) {
            $sql  = "SELECT * FROM ($ja) AS s JOIN users ON s.uid = users.uid AND $w";
            $csql = "SELECT COUNT(*) FROM ($ja) AS s JOIN users ON s.uid = users.uid AND $w";
        } else {
            $sql  = "SELECT users.* $se FROM users $jn WHERE $w";
            $csql = "SELECT COUNT(*) FROM users WHERE $w";
        }
        // ������� � ������� ��� ������
        if ($search) {
            $w = preg_split("/\\s/", $search);
            for ($i=0; $i<count($w); $i++) {
                $s .= "(
                    CASE
                    WHEN
                        (LOWER(login) = LOWER('{$w[$i]}') OR LOWER(uname) = LOWER('{$w[$i]}') OR LOWER(usurname) = LOWER('{$w[$i]}')) THEN 2
                    WHEN
                        (LOWER(login) LIKE LOWER('%{$w[$i]}%') OR LOWER(uname) LIKE LOWER('%{$w[$i]}%') OR LOWER(usurname) LIKE LOWER('%{$w[$i]}%')) THEN 1
                    ELSE 0
                    END
                ) + ";
                $t .= "(LOWER(login) LIKE LOWER('%{$w[$i]}%') OR LOWER(uname) LIKE LOWER('%{$w[$i]}%') OR LOWER(usurname) LIKE LOWER('%{$w[$i]}%')) OR ";
            }
            $s = substr($s, 0, strlen($s) - 3);
            $t = substr($t, 0, strlen($t) - 4);
            $sql  = "SELECT sh.*, ($s) AS relevant FROM ($sql) AS sh WHERE $t";
            $csql = "SELECT COUNT(*) FROM ($sql) AS sh WHERE $t";
        }
        $nums = $DB->val($csql);
        //echo "<pre>$sql $order LIMIT $limit OFFSET $offset</pre>";
        return $DB->rows("$sql $order LIMIT $limit OFFSET $offset");
    }
    


    /**
     * ���������� �� ��������� �������������
     *
     * @return array                     ����������
     */
    function GetBannedStat() {
        $sql = "SELECT COUNT(*) AS cnt FROM users WHERE is_banned = B'1' AND ban_where = 0";
        $memBuff = new memBuff();
        $row = $memBuff->getSql($error, $sql, 180);
        $site = $row[0]['cnt'];
        $sql = "SELECT COUNT(*) AS cnt FROM users WHERE ban_where = 1";
        $memBuff = new memBuff();
        $row = $memBuff->getSql($error, $sql, 180);
        $blogs = $row[0]['cnt'];
        $sql = "SELECT COUNT(*) AS cnt FROM users WHERE warn > 0 AND is_banned = B'0' AND ban_where = 0";
        $memBuff = new memBuff();
        $row = $memBuff->getSql($error, $sql, 180);
        $warns = $row[0]['cnt'];
        return array('all'=>$site + $blogs + $warns, 'site'=>$site, 'blogs'=>$blogs, 'warns'=>$warns);
    }



    /**
     * ���������� �� �����������
     *
     * @return array                     ����������
     */
    function GetModersStat() {
        global $DB;
        $sql = "
            SELECT
                moders.uid, moders.login, moders.uname, moders.usurname,
                users_ban.ban_count, blogs_ban.blogs_ban_count, users_warns.warns_count, blogs_blocked.blogs_blocked_count, projects_blocked.projects_blocked_count
            FROM
                (SELECT * FROM users WHERE (role::bit(5) & B'01011')::integer > 0) AS moders
            LEFT JOIN (
                SELECT users_ban.admin, COUNT(users_ban.id) AS ban_count
                FROM (
                    SELECT users_ban.* 
                    FROM users_ban
                    JOIN (
                        SELECT uid, MAX(id) AS id
                        FROM users_ban
                        WHERE users_ban.where = 0 OR users_ban.where IS NULL
                        GROUP BY uid
                    ) AS b ON users_ban.id = b.id
                ) AS users_ban
                JOIN users ON users.uid = users_ban.uid AND users.is_banned = B'1' AND users.ban_where = 0
                GROUP BY users_ban.admin
            ) AS users_ban ON moders.uid = users_ban.admin
            LEFT JOIN (
                SELECT users_ban.admin, COUNT(id) AS blogs_ban_count
                FROM (
                    SELECT users_ban.* 
                    FROM users_ban
                    JOIN (
                        SELECT uid, MAX(id) AS id
                        FROM users_ban
                        GROUP BY uid
                    ) AS b ON users_ban.id = b.id
                ) AS users_ban
                JOIN users ON users.uid = users_ban.uid AND users.ban_where = 1
                GROUP BY users_ban.admin
            ) AS blogs_ban ON moders.uid = blogs_ban.admin
            LEFT JOIN
                (SELECT users_warns.admin, COUNT(uid) AS warns_count FROM users_warns GROUP BY users_warns.admin) AS users_warns ON moders.uid = users_warns.admin
            LEFT JOIN
                (SELECT blogs_blocked.admin, COUNT(blogs_blocked.thread_id) AS blogs_blocked_count FROM blogs_blocked GROUP BY blogs_blocked.admin) AS blogs_blocked ON moders.uid = blogs_blocked.admin
            LEFT JOIN
                (SELECT projects_blocked.admin, COUNT(project_id) AS projects_blocked_count FROM projects_blocked GROUP BY projects_blocked.admin) AS projects_blocked ON moders.uid = projects_blocked.admin
            ORDER BY
                LOWER(moders.uname)
        ";
        return $DB->rows($sql);
    }

    

    /**
     * �������� ���� ��������� ��������
     *
     * @param string $login              login ������������
     *
     * @return string                    ����� ������
     */
    function IncHits($login){
        global $DB;
        $sql = "UPDATE users SET hits=hits+1, hitstoday=hitstoday+1 WHERE login=?";
        $DB->query($sql, $login);
        $error = $DB->error;
        return $error;
    }

    


    /**
     * �������� ���������� ��������� �� ���� ���� �������������
     *
     * @return string                    ����� ������
     */
    function ResetTodayHits(){
        global $DB;
        $sql = "UPDATE users SET hitstoday=0";
        $DB->query($sql);
        $error = $DB->error;
        return $error;
    }

    

    /**
     * ���������� ���������� �� ������������� (��� �������)
     *
     * @return array                     ����������
     */
    function CountAll(){
        global $DB;
        $DBProxy = new DB('plproxy');
  
        $sql = 'SELECT * FROM get_users_stat();';
        $ret = $DBProxy->row($sql);
        
        $sQuery = 'SELECT get_messages_stat as mess FROM get_messages_stat()';
        $ret1 = $DBProxy->row( $sQuery );
        $ret = array_merge($ret, $ret1);
        
        $sQuery = 'SELECT SUM(cnt) as notes FROM get_notes_stat()';
        $ret1 = $DBProxy->row( $sQuery );
        $ret = array_merge($ret, $ret1);
        
        $sQuery = 'SELECT get_teams_stat as teams FROM get_teams_stat()';
        $ret1 = $DBProxy->row( $sQuery );
        $ret = array_merge($ret, $ret1);

        $sql = "SELECT 
                    (SELECT COUNT(uid) FROM freelancer WHERE ((tabs&'10000000')='10000000')) as tportf, 
                    (SELECT COUNT(uid) FROM freelancer WHERE ((tabs&'01000000')='01000000')) as tserv, 
                    (SELECT COUNT(uid) FROM freelancer WHERE ((tabs&'00100000')='00100000')) as tinfo, 
                    (SELECT COUNT(uid) FROM freelancer WHERE ((tabs&'00010000')='00010000')) as tjour";
        $ret1 = $DB->row($sql);
        $ret = array_merge($ret, $ret1);

        $sql = "SELECT 
                    (SELECT COUNT(*) FROM freelancer WHERE is_active=true) as live_frl_today,
                    (SELECT COUNT(*) FROM employer WHERE is_active=true) as live_emp_today";
        $ret1 = $DB->row($sql);
        $ret = array_merge($ret, $ret1);

        $sql ="SELECT 
                    (SELECT COUNT(*) FROM projects WHERE post_date > CURRENT_DATE) as prjt,
                    (SELECT COUNT(*) FROM projects WHERE post_date < CURRENT_DATE AND post_date > CURRENT_DATE - interval '1 day') as prjy;";
        $ret1 = $DB->row($sql);
        $ret = array_merge($ret, $ret1);

        $sql = "SELECT COUNT(*) as active FROM freelancer WHERE freelancer.is_active = true";
        $ret1 = $DB->row($sql);
        $ret = array_merge($ret, $ret1);

        $sql = "SELECT COUNT(*) as autopro_fl FROM freelancer WHERE is_pro_auto_prolong='t'";
        $ret1 = $DB->row($sql);
        $ret = array_merge($ret, $ret1);

        $sql = "SELECT COUNT(*) as autopro_emp FROM employer WHERE is_pro_auto_prolong='t'";
        $ret1 = $DB->row($sql);
        $ret = array_merge($ret, $ret1);

        return ($ret);
    }

    function getCountUsersAll() {
        global $DB;
        
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM freelancer WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0') as live_frl_today,
                    (SELECT COUNT(*) FROM employer WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0') as live_emp_today";
        
        return $DB->row($sql);
    }

    /**
     * ������� �� ���� �������������, ������� �� ������������ ���� ������� �������� �� 3 ���� (4 ��� - ��������)
     * (����������� �� ������ � ��� ����)
     *
     * @return string                    ��������� �� ������
     */
    function DropInactive() {
        // ��������� ��� �-�, ����� �������� ���� ��� ��� ��� �� ������ �-��
        return '';
    }



    /**
     * �������� �� ���� �������������, ������� �� ������������ ���� ������� �������� �� 3 ���� (4 ��� - ��������)
     *
     * @return array                    ������ �������������
     */
    function GetUnactive(){
        $sql = "SELECT uname, usurname, login, email, last_time + '3 mon 1 week'::interval as to_date FROM users LEFT JOIN orders ON uid=orders.from_id WHERE (last_time + '3 mon'::interval < now() AND last_time + '3 mon'::interval > now()-'1 day'::interval AND (payed<>true OR orders.active<>true OR payed IS NULL))";
        $ret = $DB->rows($sql);
        return $ret;
    }



    /**
     * �������� ������ ������������� �� ip
     *
     * @param string ip                 ip-�����
     *
     * @return array                    ������ �������������
     */
    function FindByIp($ip, &$count, $limit = 30, $offset = 0){
        global $DB;
        if(!ip2long($ip)) return;
        $longIp = ip2long($ip);
        $sql = "
            SELECT
                users.uid, uname, role, usurname, login, photo, is_banned, email, 
                last_ip, email, reg_ip, icq, hits, ban_where, safety_phone, 
                safety_only_phone, SUM(ammount) as sum, users.active, account.is_block as is_block_money,
                lc.old_login, ban_reason, warn
            FROM
                users
            LEFT JOIN billing s ON s.uid = users.uid
            LEFT JOIN account ON account.uid = users.uid
            LEFT JOIN login_change lc ON user_id=users.uid 
            WHERE
                reg_ip = ? OR last_ip = ?i
            GROUP BY
                users.uid, uname, role, usurname, login, photo, is_banned, email, 
                last_ip, email, reg_ip, icq, hits, ban_where, safety_phone, 
                safety_only_phone, active, is_block_money, old_login, ban_reason, warn

            UNION 
            SELECT 
                users.uid, uname, role, usurname, login, photo, is_banned, email, 
                last_ip, email, reg_ip, icq, hits, ban_where, safety_phone, 
                safety_only_phone, SUM(ammount) as sum, users.active, account.is_block as is_block_money,
                lc.old_login, users.ban_reason, users.warn
            FROM 
                users_loginip_log l 
            INNER JOIN users ON users.uid = l.uid
            LEFT JOIN billing s ON s.uid = users.uid
            LEFT JOIN account ON account.uid = l.uid
            LEFT JOIN login_change lc ON user_id=l.uid 
            WHERE 
                l.ip = ?i
            GROUP BY
                users.uid, uname, role, usurname, login, photo, is_banned, email, 
                last_ip, email, reg_ip, icq, hits, ban_where, safety_phone, 
                safety_only_phone, active, is_block_money, old_login, ban_reason, warn
        ";
                
        $sql1 = "SELECT * FROM ({$sql}) q ORDER BY uid LIMIT ?i OFFSET ?i";
        $ret = $DB->rows($sql1, $ip, $ip, $longIp, $limit, $offset);

        $error = $DB->error;
        if ($error) $error = parse_db_error($error);
                
        $sql2 = "SELECT COUNT(*) as c FROM ({$sql}) q";
        $count = $DB->col($sql2);
        if ($count) {
            $count = $count[0];
        }
//        else {
//            if ($ret) {
//                foreach ($ret as $ikey => $row) $uids[] = $row['uid'];
//            }
//        }
        return $ret;
    }

    /**
     * �������� ������ ������������� �� ������ ��������� �����
     *
     * @param string card               ����� ����� ��� ������
     *
     * @return array                    ������ �������������
     */
    function FindByCard($card){
        global $DB;
        $sql = "SELECT DISTINCT users.uid, uname, role, usurname, login, photo, is_banned, email, last_ip, email, reg_ip, sum, icq, hits, ban_where, safety_phone, safety_only_phone FROM users 
            LEFT JOIN (SELECT uid, sum(ammount) FROM billing GROUP BY uid) as s ON s.uid = users.uid  
            WHERE users.uid IN (
                SELECT DISTINCT account.uid FROM account_operations AS ao 
                LEFT JOIN account ON account.id=ao.billing_id
                WHERE ao.descr ILIKE ? OR ao.descr ILIKE ? OR ao.descr ILIKE ?
            )
            ORDER BY uid";
        $ret = $DB->rows($sql, "%� ������� {$card} %", "%� ����� %{$card}% %", "%����� ������� - {$card}");
        $error = $DB->error;
        if ($error) $error = parse_db_error($error);
        else {
            if ($ret) {
                foreach ($ret as $ikey => $row) $uids[] = $row['uid'];
            }
        }
        return $ret;
    }


    /**
     * �������� �������� e-mail ������������
     *
     * @param integer $uid               id ������������
     * @param string $email             ����� email ������������
     *
     * @return mixed                     0 � ������ ������, ����� ������ � ������ ��������
     */
    function ChangeMail($uid,$email){
        global $DB;
        if($email = change_q(substr(trim($email), 0, 96), true))
            if (!preg_match( '/^[A-z0-9_\\.-]+[@][A-z0-9_-]+([.][A-z0-9_-]+)*[.][A-z]{2,4}$/', $email )) {
                return "���� Email ��������� �����������"; 
            }
        if(empty($email)) {
            return "���� Email �� ����� ���� ������"; 
        }
        $sql = "SELECT uid,login from users WHERE email=?";
        $res = $DB->row($sql, $email);
        if (!$res) {
            self::initChangeEmailLog($uid);
            $res = $DB->query(" UPDATE users SET email=? WHERE uid=?i", $email, $uid);
            self::SaveChangeEmailLog($uid,$email);
            return 0;
        }
        else {
            return "����� �-���� ��� ���� � ����� ".$res["login"]."  [id: ".$res["uid"]." ]";
        }
    }

    /**
     * ������������� ��� ������������
     * 
     * @param  int $uid UID ������������
     * @param  bool $sex ��� ������������ true - �������, false - �������
     * @return resource ��������� �������
     */
    public function SetSex($uid,$sex){
        $sex_str = $sex ? 'true' : 'false';
        global $DB;
        if(get_uid(false)) $_SESSION['sex'] = $sex;
        return $DB->query(" UPDATE users SET sex=? WHERE uid=?i", $sex_str, $uid);
    }




    /**
     * �������� ������� ������������
     * 
     * @param  int $uid UID ������������
     * @param  bool $can_change ���� ���� ������������
     * @return bool true - �����, false - ������
     */
    function NullRating( $uid, $can_change = false ) {
        $bRet = false;
        
        if ( $can_change ) {
        	require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/rating.php' );
        	$user = new users();
        	$user->GetUserByUID( $uid );
            
            $rating = new rating($uid, $user->is_pro, $user->is_verify, $user->is_profi);
            $bRet   = $rating->nullRating();
        }
        
        return $bRet;
    }



    /**
     * ������ ����� ������������ �� �������������
     *
     * @param integer $uid               id ������������
     * @param string $can_change         ����� �� �������
     *
     * @return integer                   0 � ����� ������
     */
    function ChModer ($uid, $can_change = 0){
        global $DB;
        if ($can_change = 0) return 0;
        $sql = "UPDATE users SET role= (role # '".$GLOBALS['modermask']."')&'".$GLOBALS['modermask']."'|(role & ~ B'".$GLOBALS['modermask']."') WHERE uid=?i";
        $DB->query($sql, $uid);
        return 0;
    }



    /**
     * ������ ����� ������������ �� ������������
     *
     * @param integer $uid               id ������������
     * @param string $can_change         ����� �� �������
     *
     * @return integer                   0 � ����� ������
     */
    function ChRedact ($uid, $can_change = 0){
        global $DB;
        if ($can_change = 0) return 0;
        $sql = "UPDATE users SET role= (role # '".$GLOBALS['redactormask']."')&'".$GLOBALS['redactormask']."'|(role & ~ B'".$GLOBALS['redactormask']."') WHERE uid=?i";
        $DB->query($sql, $uid);
        return 0;
    }



    /**
     * ���������������� ���������� ������ ������� �� ������� � ������� ������������� �������
     *
     * @param string $where            ������ ����� WHERE
     *
     * @return string                  ��������� �� ������
     */
    function InitFromSQL($where_prms, $any_base = true){
        global $DB;
        $fields = array();
        $current = get_class($this);
        $class_vars = get_class_vars(get_class($this));
        $inner_counters = "LEFT JOIN users_counters uc ON uc.user_id = uid";
        foreach ($class_vars as $name => $value) {
            if($name == "rating") {
                $fields[] = "rating_get($current.rating, $current.is_pro, $current.is_verify, $current.is_profi) as rating";
                continue;
            }
            
            if($name != "passwd"){
                $fields[] = $current.".".$name;
            }
        }
        $fld = implode(", ", $fields);
        
        $where = array_shift($where_prms);
        $sql = "SELECT $fld, uc.ops_frl_null as ops_null, uc.ops_frl_plus as ops_plus, uc.ops_frl_minus as ops_minus
                FROM $current {$inner_counters} WHERE {$where}";
        array_unshift($where_prms, $sql);
        $result = call_user_func_array(array($DB, 'row'), $where_prms);
        if ($DB->error) {
            $error = parse_db_error($DB->error);
        } else if ($result) {
            foreach ($result as $name => $value) {
                $this->$name = $value;
            }
        }
        
        return $error;
    }



    /**
     * ������ ���� "������������ �� ��� �� ����� ������ ������"
     *
     * @return string                    ��������� �� ������
     */
    function UpdateInactive(){
        global $DB;
        $sql = "UPDATE users SET is_active=false WHERE is_active=true AND (last_time + '6 mons'::interval) < now()";
        $DB->query($sql);
        return ($DB->error);
    }



    /**
     * ������ ���� ��������� ������������ (������ ������������ ������������������)
     *
     * @return integer                   1 � ����� ������
     */
    function SetActive ($login){
        global $DB;
        $sql = "UPDATE users SET active='true' WHERE login=?; DELETE FROM activate_code WHERE user_id=(SELECT uid FROM users WHERE login=?)";
        $DB->query($sql, $login, $login);
        return 1;
    }

    
    /**
     * ������ ���� ��������� ������������ (������ ������������ ������������������)
     * 
     * @param  int $uid UID ������������
     * @return bool true - �����, false - ������
     */
    function SetActiveByUid( $uid ) {
        global $DB;
        $sql = "UPDATE users SET active='true' WHERE uid=?i; DELETE FROM activate_code WHERE user_id=?i";
        $DB->query( $sql, $uid, $uid );
        return ( !$DB->error );
    }


    /**
     * �������� ����� ������������ �� id �������������
     *
     * @param integer $voter_id          id ������������� ������������
     *
     * @return mixed                     ������ ������ ��� NULL � ������ ��������
     */
    function GetPopByVoter($voter_id)
    {
      global $DB;
      $sql = "SELECT vote FROM users_pop WHERE user_id = ?i AND voter_id = ?i";
      return $DB->val($sql, $this->uid, $voter_id);
    }


    /**
     * ���������� ����� �����������
     *
     * @param integer $voter_id          id ����������� ������������
     * @param string $voter_login        login ����������� ������������
     *
     * @return string                    HTML
     */
    function PrintPopBtn($voter_id,$voter_login)
    {
      $p_href = '';
      $m_href = '';
      $p_click = '';
      $m_click = '';
      if($voter_id && $this->uid!=$voter_id) {
        $vote = $this->GetPopByVoter($voter_id);
        if($vote!=1) {
          $p_href = ' href="javascript:;"';
          $p_click = " onclick=\"try { if(!lockPop) xajax_PopVote('{$_SESSION['rand']}', '{$this->login}',1, document.getElementById('idPVote').innerHTML); lockPop=1; } catch(e) { }\"";
        }
        if($vote!=-1) {
          $m_href = ' href="javascript:;"';
          $m_click = " onclick=\"try { if(!lockPop) xajax_PopVote('{$_SESSION['rand']}', '{$this->login}',-1, document.getElementById('idPVote').innerHTML); lockPop=1; } catch(e) { }\"";
        }
      }

      ob_start();
    ?>
      <script type="text/javascript">var lockPop=0;</script>
      <a<?=$p_href.$p_click?>><img src="/images/gray_plus_btn.gif" style="vertical-align:middle" alt=""  /></a>
      &nbsp;<b id="idPVote"><?=$this->pop?></b>&nbsp;
      <a<?=$m_href.$m_click?>><img src="/images/gray_minus_btn.gif" style="vertical-align:middle" alt=""  /></a>
    <?
      return ob_get_clean();
    }

    /**
     * ���������� ����� �����������
     *
     * @param integer $voter_id          id ����������� ������������
     * @param string $voter_login        login ����������� ������������
     *
     * @return string                    HTML
     */
    function PrintPopBtnNew($voter_id,$voter_login)
    {
      $p_href = '';
      $m_href = '';
      $p_click = '';
      $m_click = '';
      if($voter_id && $this->uid!=$voter_id) {
        $vote = $this->GetPopByVoter($voter_id);
        if($vote!=1) {
          $p_href = ' href="javascript:;"';
          $p_click = " onclick=\"try { if(!lockPop) xajax_PopVote('{$_SESSION['rand']}', '{$this->login}',1, document.getElementById('idPVote').innerHTML); lockPop=1; } catch(e) { }\"";
        }
        if($vote!=-1) {
          $m_href = ' href="javascript:;"';
          $m_click = " onclick=\"try { if(!lockPop) xajax_PopVote('{$_SESSION['rand']}', '{$this->login}',-1, document.getElementById('idPVote').innerHTML); lockPop=1; } catch(e) { }\"";
        }
      }

      ob_start();
    ?>
<script type="text/javascript">var lockPop=0;</script>
    <a class="b-button b-button_vote_down <?= !$m_click ? 'b-button_disabled' : '';?>" <?=$m_href.$m_click?>></a>
    <span class="b-layout__txt b-layout__txt_lineheight_1 b-layout__txt_valign_middle b-layout__txt_bold b-layout__txt_color_<?=$this->pop >= 0 ? '6db335' : 'c10600'?>">&#160; <span id="idPVote"><?=$this->pop?></span> &#160;</span>
    <a class="b-button b-button_vote_up <?= !$p_click ? 'b-button_disabled' : '';?>" <?=$p_href.$p_click?>></a>
    <?
      return ob_get_clean();
    }



    /**
     * �������� �����
     *
     * @param integer $voter_id          id ����������� ������������
     * @param integer $voter             1 - ��, -1 - ������
     *
     * @return integer                   1 � ������ ������, 0 � ������ ��������
     */
    function PopVote($voter_id, $vote) 
    {
      global $DB;
      if($vote!=1 && $vote!=-1)
        return 0;

      $sql = "INSERT INTO users_pop (user_id, voter_id, vote, vote_time, voter_ip) VALUES (?i, ?i, ?i, ?, ?)";

      if( $DB->query($sql, $this->uid, $voter_id, $vote, date('Y-m-d H:i:s'), getRemoteIP()) )
        return 1;

      return 0;
    }



    /**
     * �������� id ������������ �� ����������� ����
     * @see ��������� ����������� � /rss/commune.php
     *
     */
    function GetUidByFUID($fuid)
    {
      global $DB;
      $sql = "SELECT user_id FROM users_fuids WHERE fuid=?";
      return $DB->val($sql, $fuid);
    }



    /**
     * �������� ���������� ��� �� id ������������
     * @see ��������� ����������� � /rss/commune.php
     *
     */
    function GetFUIDByUid($uid)
    {
      global $DB;
      $sql = "SELECT fuid FROM users_fuids WHERE user_id=?i";
      return $DB->val($sql, $uid);
    }



    /**
     * ���������� ���������� ���, ���� ������������ ��� ��� �� �����, ��������� � ������� � ���������� ���
     * @see ��������� ����������� � /rss/commune.php
     *
     */
    function SetUserFUID($uid)
    {
      global $DB;
      if($fuid=self::GetFUIDByUid($uid)) return $fuid;

      // ������������ �������������. ���� �����������, ������� � �������.
      $uni_num_key = ($uid-98723)*316; // ����� �� ������ �����!
      list($msec, $sec) = explode(' ',microtime());
      mt_srand();
      $fuid  = dechex(mt_rand(9021,982357902987));
      $fuid .= '-'.dechex($sec);
      $fuid .= '-'.implode('',array_reverse(str_split(dechex($uni_num_key))));
      $fuid .= '-'.dechex(substr($msec,2,strlen($msec-2)));
      $fuid  = strtolower($fuid);
      ///////////////////////////////

      $sql = 
      "INSERT INTO users_fuids (user_id, fuid)
       SELECT ?i, ?
        WHERE NOT EXISTS (SELECT 1 FROM users_fuids WHERE user_id=?i OR fuid=?)
       RETURNING fuid";

      return $DB->val($sql, $uid, $fuid, $uid, $fuid);
    }



    /**
     * ��������� e-mail ������������ �� ���������� ��� � ���� (������������ ��� ����� email � ���������� � ��� ����������� �������������)
     *
     * @param string $email              e-mail �����
     *
     * @return integer                   1 - ����, 0 - ���
     */
    function CheckEmail($email)
    {
        global $DB;
        $email = strtolower($email);
        if ($email == strtolower($this->email)) {return 0;}
        else
        {
            $email = email_alias($email);
            $sql = "SELECT COUNT(uid) FROM users WHERE lower(email) IN (?l)";
            if ($DB->val($sql, $email)) return 1;
            else return 0;
            
        }
    }
    
    /**
     * ��������� ������� email-�� � ����
     * @param $emails ������ ������� ������� ���� ������ � ����
     * !!!! ��� ������ ������ ���� ���������� � ������ �������
     * @return ������ ��������� ������� �������
     */
    function CheckEmailArray($emails) {
        global $DB;
        
        if (!is_array($emails) || empty($emails)) {
            return array();
        }
        
        $sql = "
            SELECT u.email, u.login, u.uname, u.usurname, u.photo
            FROM users u
            WHERE lower(email) IN (?l)";
        return $DB->rows($sql, $emails);
    }

    /**
     * ��������� ������� ��������������� ��������� PRO
     *
     * @param   string   $pro_auto_prolong    ����� ������ ��������������� ��������� PRO (on/off)
     * @param   integer  $uid                 uid ����������
     */
    function setPROAutoProlong($pro_auto_prolong, $uid) {
        global $DB;
        if($pro_auto_prolong=='on') {
            $sql_prolong = "t";
        }
        if($pro_auto_prolong=='off') {
            $sql_prolong = "f";
        }
        $sql = "UPDATE users SET is_pro_auto_prolong=? WHERE uid=?i";

        $DB->query($sql, $sql_prolong, $uid);
    }

    /**
    * ���������� ���������� �� ������� ������������ � ���������� �������� ������������
    *
    * @param    integer     $uid            uid ������������
    * @param    array       $ip_addresses   ������ � IP ���������
    * @param    char        $bind_ip        ������� ����������� ����������� �� ����� � IP-������, 't'-��,'f'-���
    *
    * @return   integer                     1 � ������ ������, 0 � ������ ��������
    */
    function UpdateSafetyInformation($uid,$ip_addresses,$bind_ip) {
        global $DB;
        $ret = 1;
        $DB->query("UPDATE users SET safety_bind_ip=? WHERE uid=?i", $bind_ip, $uid);
        if($ret) {
            $sql = "DELETE FROM users_safety WHERE uid=?i";
            $DB->query($sql, $uid);
            if(!empty($ip_addresses)) {
                while(list($k,$v)=each($ip_addresses)) {
                    if(strstr($v,'-')) {
                        list($b_ip,$e_ip) = preg_split("/\-/",$v);
                        $b_ip = ip2long($b_ip);
                        $e_ip = ip2long($e_ip);
                    } else {
                        if(strstr($v,'/')) {
                            list($b_ip,$e_ip) = preg_split("/\//",$v);
                            $b_ip = ip2long($b_ip);
                            $e_ip = $b_ip + pow(2, (32-$e_ip)) - 1;
                        } else {
                            $b_ip = ip2long($v);
                            $e_ip = ip2long($v);
                        }
                    }
                    $sql = "INSERT INTO users_safety(uid,b_ip,e_ip) VALUES(?i,?i,?i);";
                    if(!$DB->query($sql, $uid, $b_ip, $e_ip)) $ret = 0;
                }
            }
        }
        return $ret;
    }

    /**
    * �������� IP ������� ������� ��������� � �������� ������������
    *
    * @param    integer $uid    id ������������
    *
    * @return array             ������ � IP ��������
    */
    function GetSafetyIP($uid) {
        global $DB;
        $ip = array();
        $sql = "SELECT b_ip,e_ip FROM users_safety WHERE uid=?i";
        $qip = $DB->rows($sql, $uid);
        if($qip) {
            foreach($qip as $dip) {
                if($dip['e_ip']!=$dip['b_ip']) {
                    array_push($ip,long2ip($dip['b_ip']).'-'.long2ip($dip['e_ip']));
                } else {
                    array_push($ip,long2ip($dip['b_ip']));
                }
            }
        }
        return $ip;
    }

    /**
    * �������� �� ���������� ��������� ������������� IP ������� � ��������� ��������, ������ ������������
    *
    * @param    string  $ip_addresses
    *
    * @return   array   'ip_addresses' - ������������������ ���������� ���� ������ IP, 'alert' - ����� ��������� �� ������, ���� ��� ����, 'error_flag' - 1-IP ������� �� ���������, 0 - ���������
    */
    function CheckSafetyIP($ip_addresses) {
        $flag_error_ip = 0;
        $t_ip_addresses = preg_split("/\r\n|\n/",$ip_addresses);
        if(!empty($ip_addresses)) {
            $ip_addresses = array();
            while(list($key,$ip)=each($t_ip_addresses)) {
                $ip = trim($ip);
                $ip = preg_replace("/ {0,}, {0,}/",",",$ip);
                $ip = preg_replace("/ {0,}- {0,}/","-",$ip);
                if(strstr($ip,',')) {
                    $t_ips = preg_split("/,/",$ip);
                    while(list($k,$v)=each($t_ips)) { array_push($ip_addresses,$v); }
                    continue;
                }
                array_push($ip_addresses,$ip);
            }
            reset($ip_addresses);
            while(list($key,$ip)=each($ip_addresses)) {
                $test_ips = array();
                if(strstr($ip,'-')) {
                    $t_ips = preg_split("/-/",$ip);
                    while(list($k,$v)=each($t_ips)) { array_push($test_ips,$v); }
                } else {
                    if(strstr($ip,'/')) {
                        $cidr = preg_split("/\//",$ip);
                        array_push($test_ips,$cidr[0]);
                        array_push($test_ips,long2ip(ip2long($cidr[0]) + pow(2, (32-$cidr[1])) - 1));
                    } else {
                        array_push($test_ips,$ip);
                    }
                }
                while(list($k,$v)=each($test_ips)) {
                    $ip_parts = explode('.',$v);
                    if(count($ip_parts)==4 && isset($ip_parts[0]) && isset($ip_parts[1]) && isset($ip_parts[2]) && isset($ip_parts[3]) && !array_search('',$ip_parts)) {
                        $ip_parts[0] = (int)$ip_parts[0];
                        $ip_parts[1] = (int)$ip_parts[1];
                        $ip_parts[2] = (int)$ip_parts[2];
                        $ip_parts[3] = (int)$ip_parts[3];
                        if(!is_numeric(str_replace('.','',$v)) || $ip_parts[0]>255 || $ip_parts[0]<0 || $ip_parts[1]>255 || $ip_parts[1]<0 || $ip_parts[2]>255 || $ip_parts[2]<0 || $ip_parts[3]>255 || $ip_parts[3]<0 || ip2long($v)==-1 || ip2long($v)==FALSE) {
                            $flag_error_ip = 1;
                        }

                    } else {
                        $flag_error_ip = 1;
                    }
                }
            }
        }
        if($flag_error_ip) {
            $error_flag = 1;
            $alert[1] = "�� ����� IP � ������������ �������";
            $ip_addresses = change_q(trim(stripslashes($_POST['ip_addresses'])),true);
        }
        return array('ip_addresses'=>$ip_addresses,'alert'=>$alert,'error_flag'=>$error_flag);
    }

    /**
    * �������� ������������ ���������� �������� � ��������� ������� ������������, ������ ������������
    *
    * @param    string  $phone
    *
    * @return   array   'phone' - ����������������� � ���������� ���� �������, 'alert' - ����� ��������� �� ������, ���� ��� ����, 'error_flag' - 1-������� ������ �� ���������, 0 - ���������
    */
    function CheckSafetyPhone($phone) {
        $t_phone = $phone;
        if(!preg_match("/^\+\d{7,}$/",$t_phone) && $t_phone!='') {
            $error_flag = 1;
            $alert[2] = "�� ����� ������� � ������������ �������";
        }
        if(strlen($t_phone)>30) {
            $error_flag = 1;
            $alert[2] = "����� �������� ������ ���� ������ 30 ����";
        }
        return array('phone'=>$phone,'alert'=>$alert,'error_flag'=>$error_flag);
    }

    /**
    * ������� ������������� �� ������ �������� ������������� ��� ������������� ������
    *
    * @param    string  $phone
    *
    * @return   array   ������ �������������
    */
    function FindUsersBySafetyPhone($phone) {
        global $DB;
        $sql = "SELECT login, uname, usurname, role FROM users WHERE safety_phone=?";
        $ret = $DB->rows($sql, $phone);
        return $ret;
    }

    /**
    * ����� ������ ������������ � ��������� ������
    *
    * @param    integer $uid    id ������������
    * @param    string  $phone  ������� ��������� � ������� ������������
    *
    * @return   string          ����� ������, ����� ���� ������� �� ���������
    */
    function ResetPasswordSMS($uid, $phone) {
        global $DB;
        $new_password = '';
        $phone = str_replace("+", "", $phone);
        $phone = '+'.$phone;
        
        $sql = "SELECT u.uid FROM users u
                INNER JOIN sbr_reqv s ON s.user_id = u.uid
                WHERE u.uid=?i AND (s._1_mob_phone = ? OR s._2_mob_phone = ?)";
        if(pg_num_rows($DB->query($sql, $uid, $phone, $phone))==1) {
            mt_srand();
            $new_password = substr(md5(uniqid(mt_rand())), 0, self::MAX_NEW_PASSWORD_LENGTH);
            $sql = "UPDATE users SET passwd=? WHERE uid=?i";
            $DB->query($sql, users::hashPasswd($new_password), $uid);

            // ����� � ��� ����� �������
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/restorepass_log.php");
            restorepass_log::SaveToLog($uid, getRemoteIP(), 2);
        }
        return $new_password;
    }

    /**
    * ��������� ������ �������� ������������� ������� ����� SMS
    *
    * @param    integer $uid    ID ������������
    * @param    string  $phone  ����� ��������
    *
    * @return   string  ��������� �� ������
    */
    function ChangeSafetyPhone($uid,$phone,$only_phone) {
        global $DB;
        $res = '';
        $t_phone = change_q(stripslashes($phone),true);
        $c_phone = users::CheckSafetyPhone($t_phone);
        $phone = $c_phone['phone'];
        $res = $c_phone['alert'][2];
        if(!$res) {
            $only_phone = ($only_phone=='t')?'t':'f';
            $sql = "UPDATE users SET safety_phone=?, safety_only_phone=? WHERE uid=?i";
            $DB->query($sql, $phone, $only_phone, $uid);
        }
        return $res;
    }

    /**
    * ��������� IP �������� ����������� ��� ������ ������������
    *
    * @param    integer $uid    ID ������������
    * @param    string  $ip     IP �������
    *
    * @return   string  ��������� �� ������
    */
    function ChangeSafetyIP($uid,$ip) {
        global $DB;
        $res = '';
        $ip_addresses = preg_replace("/,[ ]{0,}/","\n",$ip);
        $c_ip = users::CheckSafetyIP($ip_addresses);
        $ip_addresses = $c_ip['ip_addresses'];
        $res = $c_ip['alert'][1];
        if(!$res) {
            $sql = "DELETE FROM users_safety WHERE uid=?i";
            $DB->query($sql, $uid);
            if(!empty($ip_addresses)) {
                while(list($k,$v)=each($ip_addresses)) {
                    if(strstr($v,'-')) {
                        list($b_ip,$e_ip) = preg_split("/\-/",$v);
                        $b_ip = ip2long($b_ip);
                        $e_ip = ip2long($e_ip);
                    } else {
                        if(strstr($v,'/')) {
                            list($b_ip,$e_ip) = preg_split("/\//",$v);
                            $b_ip = ip2long($b_ip);
                            $e_ip = $b_ip + pow(2, (32-$e_ip)) - 1;
                        } else {
                            $b_ip = ip2long($v);
                            $e_ip = ip2long($v);
                        }
                    }
                    $sql = "INSERT INTO users_safety(uid,b_ip,e_ip) VALUES(?i,?i,?i);";
                    if(!$DB->query($sql, $uid, $b_ip, $e_ip)) $ret = 0;
                }
            }
        }
        return $res;
    }

    /**
     * ��������� ������ �������������� �������������
     * 
     * @return resource ��������� �������.
     */
    function GetSuspiciousUsers($offset = 0, $limit = 'ALL') {
        return $GLOBALS['DB']->query( 'SELECT 
                u.uid, u.login, u.uname, u.usurname, u.is_banned, u.ban_where, u.photo, (a.code IS NOT NULL) AS activate 
            FROM users u 
            INNER JOIN users_suspicious s ON s.user_id = u.uid 
            LEFT JOIN activate_code a ON a.user_id = u.uid 
            WHERE is_verified = false
            ORDER BY uname, usurname, login OFFSET '.$offset.' LIMIT '.$limit
        );
    }
    
    /**
     * ������ ������������� ���� �������������� ������������� �� ������������ � �� �������������.
     * 
     * �������� ��� ���������� ����� �������������� ����, ��� ��� �� �� ������ ����� ������ 
     * �������������� ����� ������������ ���������� � ������, ����� � �������.
     * 
     * @return bool true - �����, false - ������.
     */
    function resetAllSuspiciousUsers() {
        global $DB;
        set_time_limit(0);
        
        $words_login = users::GetSuspiciousWordsLogin();
        $words_name  = users::GetSuspiciousWordsName();
        $sql_login   = '';
        $sql_name    = '';
        
        setlocale(LC_ALL, 'ru_RU.CP1251');

        if ( $words_login ) {
            foreach($words_login as $word) {
                $sql_login .= "lower(login) LIKE '%".strtolower($word['word'])."%' OR ";
            }
            
            $sql_login = preg_replace("/OR $/","",$sql_login);
        }
        
        if ( $words_name ) {
            foreach($words_name as $word) {
                $sql_name .= "lower(uname) LIKE '%".strtolower($word['word'])."%' OR lower(usurname) LIKE '%".strtolower($word['word'])."%' OR ";
            }
            
            $sql_name = preg_replace("/OR $/","",$sql_name);
        }
        
        setlocale(LC_ALL, "en_US.UTF-8");
        
        if ( $sql_login || $sql_name ) {
            $mRid = $DB->query( 'SELECT uid FROM users 
                WHERE ' . ($sql_login ? $sql_login : '') . ($sql_name ? ($sql_login ? ' OR ' : '').$sql_name : '')
            );
            
            if ( $mRid ) {
                if ( !$DB->start() ) {
                	return false;
                }
                
                while ( $aRow = pg_fetch_assoc($mRid) ) {
                    if ( !$DB->insert('users_suspicious', array('user_id' => $aRow['uid'])) ) {
                        $DB->rollback();
                    	return false;
                    }
                }
                
                if ( !$DB->commit() ) {
                    $DB->rollback();
                	return false;
                }
            }
            else {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * �������� ��������������� ������������ ��� ������������.
     * 
     * @param  int $sUid UID ������������
     * @return bool true - �����, false - ������.
     */
    function approveSuspiciousUser( $sUid = 0 ) {
        global $DB;
        
        $aData = array(
            'is_verified' => true,
            'is_approved' => true
        );
        
        $DB->update( 'users_suspicious', $aData, 'user_id = ?', $sUid );
    }
    
    /**
     * �������� ��������������� ������������ ��� �����������.
     * 
     * @param  int $sUid UID ������������
     * @return bool true - �����, false - ������.
     */
    function banSuspiciousUser( $sUid = 0 ) {
        global $DB;
        
        $aData = array(
            'is_verified' => true,
            'is_approved' => false
        );
        
        $DB->update( 'users_suspicious', $aData, 'user_id = ?', $sUid );
    }
    
    /**
     * ��������� �� �������� �� ������������ ��������������.
     * 
     * 1. ���� ����� �����, ��� � ������� �� �������� �������������� ���� - ���� ��������� �� ������� ��������������, 
     *    ����� �� ����� ���� �������� � ��� ���������� �����.
     * 2. ���� ����� �����, ��� � ������� ��������� �� ������� - ���� �������������� �� ��������� � ����� ������, 
     *    ����� �� ���������� ����� ������ ������ ��� ��� ���� �������� ����� ��������.
     * 3. ����� ���� ��������� �������������� - ��������� ��� � ������� ��������������.
     * 
     * @param  int $sUid UID ������������
     * @param  string $sLogin ����� �����
     * @param  string $sOldLogin ������ �����
     * @param  string $sName ����� ���
     * @param  string $sOldName ������ ���
     * @param  string $sSurname ����� �������
     * @param  string $sOldSurname ������ �������
     * @return bool true - ��������������, false - ����������.
     */
    function isSuspiciousUser( $sUid = 0, $sLogin= '', $sOldLogin= '', $sName = '', $sOldName = '', $sSurname = '', $sOldSurname = '' ) {
        $bSuspicious = $bLoginSusp = $bNameSusp = $bSurnameSusp = false;
        
        if ( $sLogin ) {
        	$aWordsLogin = users::GetSuspiciousWordsLogin();
        	$bLoginSusp = users::_isWordSuspicious( $sLogin, $aWordsLogin );
        }
        
        if ( $sName || $sSurname ) {
        	$aWordsName = users::GetSuspiciousWordsName();
        	
        	if ( $sName ) {
                $bNameSusp = users::_isWordSuspicious( $sName, $aWordsName );
            }
            
            if ( $sSurname ) {
                $bSurnameSusp = users::_isWordSuspicious( $sSurname, $aWordsName );
            }
        }
        
        if ( $sLogin && !$bLoginSusp && $sName && !$bNameSusp && $sSurname && !$bSurnameSusp ) {
        	$GLOBALS['DB']->query( 'DELETE FROM users_suspicious WHERE user_id = ?', $sUid );
        }
        elseif ( 
            ($sLogin != $sOldLogin && $bLoginSusp) 
            || ($sName != $sOldName && $bNameSusp) 
            || ($sSurname != $sOldSurname && $bSurnameSusp) 
        ) {
            $GLOBALS['DB']->insert( 'users_suspicious', array('user_id' => $sUid) );
            $bSuspicious  = true;
        }
        
        return $bSuspicious;
    }
    
    /**
     * ��������� ������ �� ������ ����� � ������ ��������������.
     * 
     * @param  string $sWord ����� ������� ����� ���������
     * @param  array $aWords ������ �������������� ����
     * @return bool true - ����� ��������������, false - ����� ����������.
     */
    function _isWordSuspicious( $sWord = '', $aWords = array() ) {
        $bSuspicious = false;
        
        if ( $sWord ) {
        	if ( $aWords ) {
        	    setlocale(LC_ALL, 'ru_RU.CP1251');
        	    $sLow = strtolower( $sWord );
                
        		foreach ( $aWords as $aWord ) {
        		    if ( strpos($sLow, strtolower($aWord['word'])) !== false ) {
        		        $bSuspicious = true;
        		        break;
        		    }
        		}
        		
        		setlocale(LC_ALL, "en_US.UTF-8");
        	}
        }
        
        return $bSuspicious;
    }
    
    /**
	 * �������� ����� ��� ������ �� ��������� ��� ��������������� �����.
	 *
	 * @param  string $sUid ������������� �����.
	 * @return array ������ � ������� login, passwd, code ��� ������ ������ ���� ������ �� �������.
	 */
	function getSuspectActivationData( $sUid = '' ) {
	    global $DB;
	    
	    $sQuery = 'SELECT u.login, a.suspect_plain_pwd AS passwd, a.code FROM activate_code a 
	       INNER JOIN users u ON a.user_id = u.uid 
	       WHERE u.uid = ? AND a.suspect_plain_pwd IS NOT NULL';
	    
	    return $DB->row( $sQuery, $sUid );
	}
	

    /**
     * ���������� ����������� �������������� �������������
     * 
     * @return int
     */
    function GetCountSuspiciousUsers() {
        global $DB;
        
        $nCount = $DB->val('SELECT COUNT(*) FROM users_suspicious WHERE is_verified = false');
        
        return $nCount;
    }
    
    /**
     * ���������� IP � ������� ������������� ������ ������������� ���������� ����������� � �����
     * � ��� �� ������������� ������� ���� ���������������� � ���� IP
     *
     * @param  integer $nCount ���������� ����� ���������� �������.
     * @param  integer $nLimit ����������� ���������� ���������, ������� ����� ����������
	 * @param  integer $nOffset ����������� � ������ ��������� ����������
	 * @return array
     */
    function GetSuspiciousIPs( &$nCount, $nLimit = 0, $nOffset = 0 ) {
        $nCount  = 0;
        $aReturn = array();
        global $DB;
        $sLimit  = ( $nLimit ) ? ( $nOffset ? " LIMIT $nLimit OFFSET $nOffset" : " LIMIT $nLimit" ) : '';
        $sQuery  = 'SELECT u.uid, u.login, u.uname, u.usurname, u.reg_ip, u.reg_date, u.is_banned FROM users_regip_log l 
            INNER JOIN users u ON u.reg_ip = l.reg_ip AND u.reg_date = l.reg_date 
            ORDER BY u.reg_date DESC, u.reg_ip DESC ' . $sLimit;
        
        $aRows = $DB->rows( $sQuery );
        
        if ( !$DB->error && $aRows ) {
        	$sQuery  = 'SELECT COUNT(u.uid) FROM users_regip_log l 
                INNER JOIN users u ON u.reg_ip = l.reg_ip AND u.reg_date = l.reg_date';
        	
        	$nCount    = $DB->val( $sQuery );
        	$sCurrIp   = $aRows[0]['reg_ip'];
        	$sCurrDate = $aRows[0]['reg_date'];
        	$aUsers    = array();
        	
        	foreach ( $aRows as $aOne ) {
        		if ( $aOne['reg_ip'] == $sCurrIp && $aOne['reg_date'] == $sCurrDate ) {
        			$aUsers[] = $aOne;
        		}
        		else {
        		    $aReturn[] = array( 'reg_ip' => $sCurrIp, 'reg_date' => $sCurrDate, 'users' => $aUsers );
        		    $sCurrIp   = $aOne['reg_ip'];
        		    $sCurrDate = $aOne['reg_date'];
        		    $aUsers    = array( $aOne );
        		}
        	}
        	
        	$aReturn[] = array( 'reg_ip' => $sCurrIp, 'reg_date' => $sCurrDate, 'users' => $aUsers );
        }
        
        return $aReturn;
    }

    /**
     * ��������� ������ �������������� ���� ��� ������
     *
     * @return  array                   ������ �������������� ����
     */
    function GetSuspiciousWordsLogin() {
        $sql = "SELECT * FROM users_suspicious_words WHERE type=1";
        return pg_fetch_all(pg_query(DBConnect(), $sql));
    }

    /**
     * ��������� ������ �������������� ���� ��� �����
     *
     * @return  array                   ������ �������������� ����
     */
    function GetSuspiciousWordsName() {
        $sql = "SELECT * FROM users_suspicious_words WHERE type=2";
        return pg_fetch_all(pg_query(DBConnect(), $sql));
    }
    
    /**
     * �������� ���� ������������� ��� �� ��������������
     */
    function approveAllSuspiciousUsers() {
        set_time_limit(0);
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php' );
        $smail = new smail();
        $mRid  = self::GetSuspiciousUsers();
        
        if ( $mRid ) {
            while ( $user = pg_fetch_assoc($mRid) ) {
                users::approveSuspiciousUser( $user['uid'] );
                
                if ( $user['activate'] == 't' ) {
                    $aData = users::getSuspectActivationData( $user['uid'] );
                    $smail->NewUser( $aData['login'], false, $aData['code'] );
                }
            }
        }
    }
    
    /**
     * ���������� ����� �������������� ���� ��� �������� ������
     *
     * @param string  $words ����� ����� �������
     * @param integer $type  ��� ����� ��� �������� (1 - ��� ������ � ������, 2 - ��� ������ � ����� � ������� )
     * @return string ��������� �� ������
     */
    function setSuspeciousWordsName($words = '', $type= 1) {
        global $DB;
        $type = intval($type);
        // ������ ����� �����������
        $error = $DB->query("DELETE FROM users_suspicious_words WHERE type = {$type}");
        
        if($words != '' && $error !== false) {
            $exp_words  = explode(",", $words);
            $sql_insert = "INSERT INTO users_suspicious_words (word, type) VALUES ";
            
            foreach($exp_words as $key=>$word) {
                $word = trim($word);
                if($word == "") continue;
                $word = __paramValue('string', $word);
                $sql_insert_array[] = "('{$word}', {$type})";
            }
            $sql_insert .= implode(", ", $sql_insert_array).";";
            
            $error = $DB->query($sql_insert);
            return $error;
        }
        
        return '';
    }


    /**
     * ������ ��� ������, � ������� �� ������ ������������ ������ ���� ���� ����,
     * �� ��� ���� �������� �������� strip_tags ��� �����������.
     * http://beta.free-lance.ru/mantis/view.php?id=9807
     *
     * ��������� ����.
     * ����� ����������� ) ����� ������� ���� ����� �������� ������������.
     * 
     * @global DB $DB
     * @param <type> $login
     * @return <type>
     */
    function FixPassword($sql, $login) {
        global $DB;
        $pwd = trim($_POST['passwd']);
        $pwd2 = strip_tags($pwd);
        if($_POST['action'] != 'login' || !$pwd || $pwd == $pwd2) {
            return NULL;
        }
        if($res = $DB->row($sql, strtolower($login), users::hashPasswd(stripslashes($pwd2)))) {
            $DB->update('users', array('passwd' => users::hashPasswd(stripslashes($pwd))),
                        'uid = ?i', $res['uid']);
        }

        return  $res;
    }
    
    /**
     * ��������� ���������� �� ���� �������� �������� � ��������.
     * 
     * @param  int $uid UID ������������.
     * @return bool true - ����������, false - �������
     */
    function isSafetyPhoneShow( $uid = 0 ) {
        global $DB;
        
        $bReturn = false;
        $sTable  = get_class($this);
        $sQuery  = "SELECT settings FROM users WHERE uid = ?i AND safety_phone IS NULL";
        $sField  = $DB->val( $sQuery, $uid );
        
        if ( $sField ) {
        	$bReturn = ( $sField[0] == '1' ) ? true : false;
        }
        
        return $bReturn;
    }
    
    /**
     * ������ ��������� ��� ����� ���� settings � �������� ���������� ������������ setup/main/
     * 
     * @param  int $uid UID ������������
     * @param  int $q_button ���������� ������ "� ��� ���� ������?" 1 ��� 0
     * @param  int $p_block ���������� ���� "������� ������ � �������� �������� �����" 1 ��� 0
     * @param  int $e_links �� ���������� �������� "������� �� ������� ������" a.php 1 ��� 0
     * @return bool true - �����, false - ������
     */
    function updateMainSettings( $uid = 0, $q_button = 0, $p_block = 0, $e_links = 0 ) {
        $aBits = array(
            'question_button'       => $q_button,
            'promo_block'           => $p_block,
            'direct_external_links' => $e_links
        );
        
        $_SESSION['question_button_hide']  = $q_button;
        $_SESSION['promo_block_hide']      = $p_block;
        $_SESSION['direct_external_links'] = $e_links;
        
        return $this->updateSettings( $uid, $aBits );
    }
    
    /**
     * �� ���������� �������� "������� �� ������� ������"
     *
     * @param  int $uid UID ������������.
     * @param  string $new ����� ��������
     * @return bool true - �����, false - ������
     */
    function setDirectExternalLinks( $uid = 0, $new = 0 ) {
        $_SESSION['direct_external_links'] = $new;
        return $this->updateSettings( $uid, array('direct_external_links' => $new) );
    }

    /**
     * �� ���������� �������� ��� �����-����
     *
     * @param  int $uid UID ������������.
     * @param  string $new ����� ��������
     * @return bool true - �����, false - ������
     */
    function setSbrSlash( $uid = 0, $new = 0 ) {
        $_SESSION['sbr_slash_show'] = $new;
        return $this->updateSettings( $uid, array('sbr_slash_show' => $new) );
    }
    
    /**
     * ������������� � ���� ��������� ���� "������� ������ � �������� �������� �����"
     *
     * @param  int $uid UID ������������.
     * @param  string $new ����� ��������
     * @return bool true - �����, false - ������
     */
    function setPromoBlockShow( $uid, $new ) {
        return $this->updateSettings( $uid, array('promo_block' => $new) );
    }
    
    /**
     * ������ �� ���������� ��� ��������� �������� �������� � ��������.
     *
     * @param  int $uid UID ������������.
     * @return bool true - �����, false - ������
     */
    function setSafetyPhoneHide( $uid = 0 ) {
        return $this->updateSettings( $uid, array('safety_phone' => 0) );
    }
    
    /**
     * ������������� ��������� ���������� �� ��������� ������ "� ��� ���� ������?"
     *
     * @param  int $uid UID ������������.
     * @param  string $new ����� ��������
     * @return bool true - �����, false - ������
     */
    function setQuestionButtonShow( $uid = 0, $new ) {
        $_SESSION['question_button_hide'] = $new;
        return $this->updateSettings( $uid, array('question_button' => $new) );
    }
    
    /**
     * �������� �������� � ��������.
     *
     * @param  int $uid UID ������������.
     * @param  string $phone ������� ��� ������������� ������
     * @param  char $only_phone ������� �������������� ������ ������ ����� �������: 't' - ��, 'f' - ���
     * @return bool true - �����, false - ������
     */
    function updateSafetyPhone( $uid = 0, $only_phone = '' ) {
        global $DB;
        $bRet = true;
        $sql = "UPDATE users SET safety_only_phone=? WHERE uid=?i";
        if(!$DB->query($sql, $only_phone, $uid)) $bRet = false;
        return $bRet;
    }
    
    /**
     * ������������� � ���� ��������� ���� "������� ������ � �������� �������� �����"
     *
     * @param  int $uid UID ������������.
     * @param  string $new ����� ��������
     * @return bool true - �����, false - ������
     */
    function setPromoBlockShowCookie( $uid, $new ) {
        $obj = null;
        
        if (isset($_COOKIE['nfastpromo_x'])) {
            $obj = json_decode(stripslashes($_COOKIE['nfastpromo_x']), 1);
        }
        
        $obj = !$obj ? array() : $obj;
        
        if (!$new) {
            $obj['close'] = 1;
        } else {
            if (key_exists('close', $obj)) {
                unset($obj['close']);
            }
            $obj['state'] = 1;
        }
        
        setcookie('nfastpromo_x', json_encode($obj), time()+60*60*24*365, '/');
        setcookie('nfastpromo_open', $new, time()+60*60*24*365, '/');
        $_SESSION['promo_block_hide'] = $new;
    }
    
    
    /**
     * ������������� ��� ����� ������ ����� WM
     * 
     * @global DB $DB
     * @param type $uid     Id ������������
     * @param type $value   ��� �����: 1 - webmoney, 2 - paymaser
     * @return type         
     */
    function setWmPaymaster ( $uid, $value ) {
        global $DB;
        
        $res = $DB->update('users', array(
            'wm_paymaster' => $value
        ), 'uid = ?i', $uid);
        
        return $res;
    }
    
    /**
     * ����� ������������� � ������� �� ������� �������� ������� �� ���� 
     * @global DB $DB
     * 
     * @param int $days ������� ���� �� ������� ��������
     * @return array 
     */
    public function getReminderUsersUnBan($days) {
        global $DB;
        if(intval($days) <= 0) $days = 1;
        
        $days_from = $days. " day";
        $days_to   = ( $days - 1 ). " day";
        $sql = "SELECT DISTINCT ON (uid) ub.*, u.login, u.uname, u.usurname, u.email, u.subscr  
                FROM users_ban ub 
                INNER JOIN users u ON u.uid = ub.uid AND u.is_banned = B'1' 
                WHERE \"to\" <= NOW() + '{$days_from}' AND \"to\" > NOW() + '{$days_to}';";
        $result = $DB->rows($sql);
        
        return $result;
    }

    /**
     * ������������ ��������� ������������.
     */
    function regVisit() {
        if (!$_SESSION['uid']) return;
        if (defined('LAST_REFRESH_DISABLE')) {
            return;
        }
        
        $_SESSION['last_refresh'] = date('c');
        $visit_updated_period = $_POST ? VISIT_POST_UPDATE_PERIOD : VISIT_GET_UPDATE_PERIOD;
        
        if ( (int)$_SESSION['last_visit'] < time() - $visit_updated_period
             && !preg_match($GLOBALS['VISIT_IGNORED_URI'], $_SERVER['REQUEST_URI']) )
        {
            $DB = new DB('stat');
            $_SESSION['last_visit'] = time();
            $DB->query('INSERT INTO users_visits (user_id, is_emp) VALUES (?i, ?b)', $_SESSION['uid'], ($_SESSION['role'][0] == 1 ? TRUE : FALSE) );
        }
    }
    
    /**
     * ������ ����� ���������� ��������� ����� �������������.
     *
     * @param integer $uid   ��. �����.
     * @return integer   �����.
     */
    function getLastVisit($uid) {
        if($uid == $_SESSION['uid'] && $_SESSION['last_visit']) {
            return $_SESSION['last_visit'];
        }
        $DB = new DB('stat');
        return (int)strtotime($DB->cache(VISIT_GET_UPDATE_PERIOD)->val('SELECT last_visit FROM users_visits WHERE user_id = ?i', $uid));
    }
    
    /**
     * ������ ����-���� ��� ���������� ����������� � ������� ������������ (����� ������������ � �������� (��� ������ �������)).
     * � ������� �� session::view_online_status(), ������ ����� ���������� ��������� ����� ���� ��� ���������� �� �����.
     *
     * @return string
     */
    function getOnlineStatus4Profile(&$message=null) {
        global $session;
        if (!$this->uid) return;
        $online_status = $session->view_online_status($this->login, false);
        $ago = $session->ago;
        if (!$session->is_active) {
            $lt = max((int)strtotime($this->last_time), users::getLastVisit($this->uid));
            $fmt = 'ynjGi';
            if (time() - $lt > 24 * 3600) {
                $fmt = 'ynjG';
                if (time() - $lt > 30 * 24 * 3600)
                    $fmt = 'ynj';
            }
            $ago = ago_pub($lt, $fmt);
        }
        if(!$ago) $ago = "����� ������";
        if($ago == '������ ���') $ago = '������ ���';
        $message = ($session->is_active ? ' ' : '�������'.($this->sex == 'f' ? '� ' : ' ').$ago . ( $ago != '������ ���' ? ' �����' : '' ) );
        $online_status .= $message;
        return $online_status;
    }
    
    /**
     * �������� ������ � ������������� �� �� ID
     * @param  string $id ������ ���� "n0, n1, n2, ... nN"
     * @return array
     * */
    static public function GetUsersInfoByIds ($ids, $table='users') {
        global $DB;
        if ( is_array($ids) ) {
            $ids = implode(',', $ids);
        }
        $cmd = "
            SELECT 
                u.uid, u.uname, u.usurname, u.login, u.photo, u.role
            FROM 
                {$table} AS u
            WHERE 
                uid IN ($ids);
        ";
        $rows = $DB->cache(600)->rows($cmd);
        return $rows;
    }
    
    /**
     * ���������� �������������, � ��� ��� ��� �������� �����������(�� ������ ���������� ����������) ������ ���������
     * @param string $substring   ���������
     * @param int    $limit  - ������� �������� �������
     * @param int    &$count - ������� ����� ����� �������, ��� ����� $limit
     * @return array
     * */
    static public function GetUsersBySubstringInFinInfo($substring, $limit, &$count) {
        $substring = __paramValue('string', $substring);
        $limit     = __paramValue('string', $limit);
        if($limit != 'ALL') $limit = intval($limit);
        $s = trim($substring);
        $DB = new DB('master');        
        $t = translit($s); 
        $filter = '';
        $filter2 = '';
        $a = preg_split("#\s+#", $s);
        if (count($a) > 1) {
            foreach($a as $v) {
                $v = trim($v);
                if($v) {
                    $filter .= " OR (r._1_fio ILIKE '%{$v}%' AND r.form_type=1) OR (r._2_full_name ILIKE '%{$v}%' AND r.form_type=2) ";
                    $filter2 .= " OR org_name ILIKE '%{$v}%' OR full_name ILIKE '%{$v}%' ";
                }
            }
        }
        
        $cmd = "SELECT u.uid, u.login, u.photo, u.role, CASE WHEN r.form_type=1 THEN r._1_fio ELSE r._2_full_name END AS name,
                    CASE WHEN r.form_type=1 THEN r._1_country ELSE r._2_country END AS country,
                    CASE WHEN r.form_type=1 THEN r._1_city ELSE r._2_city END AS city,
                    CASE WHEN r.form_type=1 THEN r._1_index ELSE r._2_index END AS index,
                    CASE WHEN r.form_type=1 THEN r._1_address ELSE r._2_address END AS address  

                    FROM sbr_reqv r 
                    LEFT JOIN users AS u ON u.uid = r.user_id 
                    
                    WHERE (
                            ( (r._1_fio ILIKE '%{$s}%' AND r.form_type=1) OR (r._2_full_name ILIKE '%{$s}%' AND r.form_type=2) {$filter} OR r.user_id IN (SELECT user_id FROM reqv_ordered WHERE org_name ILIKE '%{$s}%' OR full_name ILIKE '%{$s}%' {$filter2}) ) 
                            AND ( (r.form_type=1 AND r._1_country IS NOT NULL) OR (r.form_type=2 AND r._2_country IS NOT NULL) ) 
                            AND ( (r.form_type=1 AND r._1_city IS NOT NULL) OR (r.form_type=2 AND r._2_city IS NOT NULL) ) 
                            AND ( (r.form_type=1 AND r._1_index IS NOT NULL) OR (r.form_type=2 AND r._2_index IS NOT NULL) ) 
                            AND ( (r.form_type=1 AND r._1_address IS NOT NULL) OR (r.form_type=2 AND r._2_address IS NOT NULL) ) 
                          ) 
                    
                    ORDER BY name DESC
                     LIMIT {$limit}
                    ";        
        $countCmd = "SELECT count(r.user_id) 
                     FROM sbr_reqv AS r 
                     LEFT JOIN users AS u ON u.uid = r.user_id 
                   
                     WHERE (
                            ( (r._1_fio ILIKE '%{$s}%' AND r.form_type=1) OR (r._2_full_name ILIKE '%{$s}%' AND r.form_type=2) {$filter} OR r.user_id IN (SELECT user_id FROM reqv_ordered WHERE org_name ILIKE '%{$s}%' OR full_name ILIKE '%{$s}%' {$filter2}) ) 
                            AND ( (r.form_type=1 AND r._1_country IS NOT NULL) OR (r.form_type=2 AND r._2_country IS NOT NULL) ) 
                            AND ( (r.form_type=1 AND r._1_city IS NOT NULL) OR (r.form_type=2 AND r._2_city IS NOT NULL) ) 
                            AND ( (r.form_type=1 AND r._1_index IS NOT NULL) OR (r.form_type=2 AND r._2_index IS NOT NULL) ) 
                            AND ( (r.form_type=1 AND r._1_address IS NOT NULL) OR (r.form_type=2 AND r._2_address IS NOT NULL) ) 
                          ) 
                    ";
        global $DB;

        $rows = $DB->cache(600)->rows($cmd);
        $count = $DB->cache(600)->val($countCmd);

        return $rows;
    }

    /**
     * �������� ���������� � ������������ ��� ������ � ���������� ������
     * @param $uid ������������� ������������
     * @return array  
     * */
    static public function getUserShortInfoFinInfo($uid) {
        global $DB;
        $query = "SELECT u.uid, u.login, u.photo, u.role, CASE WHEN r.form_type=1 THEN r._1_fio ELSE r._2_full_name END AS name,
                    CASE WHEN r.form_type=1 THEN r._1_country ELSE r._2_country END AS country,
                    CASE WHEN r.form_type=1 THEN r._1_city ELSE r._2_city END AS city,
                    CASE WHEN r.form_type=1 THEN r._1_index ELSE r._2_index END AS index,
                    CASE WHEN r.form_type=1 THEN r._1_address ELSE r._2_address END AS address  

                    FROM sbr_reqv r 
                    LEFT JOIN users AS u ON u.uid = r.user_id 
                    
                    WHERE r.user_id = {$uid}
                 ";
        $data = $DB->row($query);
        $data["uname"]      = iconv("WINDOWS-1251", "UTF-8//IGNORE", $data["name"]);
        $data["usurname"]   = '';
        $data["address"]    = iconv("WINDOWS-1251", "UTF-8//IGNORE", "{$data['country']}, {$data['city']}, {$data['index']}, {$data['address']}");
        $data['path']       = "users/".substr($data['login'], 0, 2)."/".$data['login']."/foto/";
        $data['photo']      = get_unanimated_gif($data['login'], $data['photo']);
        $no_foto = false;
        if (($data["photo"] === null)||($data["path"] === null)) {
            $data["photo"] = "/images/temp/small-pic.gif";
            $no_foto = true;
        }
        $data["isContacts"] = 0;
        $found = 0;
        if (count($data)) $found = 1;
        $data = Array(
                          "record" => $data,
                          "found" => $found,                
                          "dav" => WDCPREFIX,
                          "no_foto" => $no_foto
                     );
        return $data;
    }

    
    /**
     * ���������� �������������, � ��� ���, ������� ��� ����� ������ ��������� � ��� �������������� ������ � $in
     * @param string $substring   ���������
     * @param int    $userType    ������ ����������� ������  
     *                            0: ������ � ����������� � �������������,   
     *                            1: ������ ������ �����������,
     *                            2: ������ ������ �������������
     * @param int    $limit  - ������� �������� �������
     * @param int    &$count - ������� ����� ����� �������, ��� ����� $limit 
     * @param string $in     - �������������� ������������� (n, n0, n1, ... nN) 
     * @param boolean $is_delete - ������ ���������(���������) ������������� ��� ���
     * @return array 
     * */
     static public function GetContactsBySubstring($substring, $userType, $limit, &$count, $in, $is_delete = false) {
         $s = $substring;
         $userTypeFilter = self::getUserTypeFilter($userType);
         $DB = new DB('master');        
         $t = translit($s); 
         $filter = '';
         $a = preg_split("#\s+#", $s);
         if (count($a) > 1) $filter = " OR (u.uname LIKE('%{$a[0]}%') AND u.usurname LIKE('%{$a[1]}%')) 
                                        OR (u.uname LIKE('%{$a[1]}%') AND u.usurname LIKE('%{$a[0]}%'))";
         
         $deleted = !$is_delete ? "  AND u.is_banned = B'0' AND u.self_deleted = false " : " ";                                 
         $cmd = "SELECT u.uid, u.uname, u.usurname, u.login, u.photo, file.path, u.role,
                         
                     (   (u.uname LIKE('%{$a[0]}%') AND u.usurname LIKE('%{$a[1]}%')) 
                     OR (u.uname LIKE('%{$a[1]}%') AND u.usurname LIKE('%{$a[0]}%'))
                     ) AS rank
                         
                     FROM users AS u
                     LEFT JOIN file 
                         ON file.fname = u.photo 
                     WHERE (login LIKE('%$t%') OR uname LIKE('%$s%') OR usurname LIKE('%$s%')
                     $filter) $userTypeFilter  AND u.uid IN ($in) {$deleted}
                     ORDER BY rank DESC
                      LIMIT {$limit}
                     ";    
        $countCmd = "SELECT count(uid) FROM users AS u WHERE (login LIKE('%$t%') OR uname LIKE('%$s%') OR usurname LIKE('%$s%')
	                        $filter ) $userTypeFilter  AND u.uid IN ($in) {$deleted}
	                    ";
	    global $DB;
	    $rows = $DB->cache(600)->rows($cmd);
	    $count = $DB->cache(600)->val($countCmd);
	    return $rows; 
     }

     /**
      * �������� ���������� ������������� �� �� ����, ��� �������������� ������ � $in
      * ���������� $limit ������������� ������������ ���� ��� ��� ����� ����
      * @param int    $userType    ������ ����������� ������  
      *                            0: ������ � ����������� � �������������,   
      *                            1: ������ ������ �����������,
      *                            2: ������ ������ �������������
      * @param int    $limit  - ������� �������� �������
      * @param int    &$count - ������� ����� ����� �������, ��� ����� $limit \
      * @param boolean $is_delete - ������ ���������(���������) ������������� ��� ���
      * @return array
      * */ 
     static public function GetMoreContactsByRole($userType, $limit, &$count, $in, $is_delete = false) {
        $deleted = !$is_delete ? " AND u.is_banned = B'0' AND u.self_deleted = false " : " ";  
    	$userTypeFilter = self::getUserTypeFilter($userType);
        $cmd = "SELECT u.uid, u.uname, u.usurname, u.login, u.photo, file.path, u.role
                FROM users AS u
                LEFT JOIN file 
                  ON file.fname = u.photo 
                WHERE 
                  u.uid IN ($in) $userTypeFilter {$deleted}
                LIMIT {$limit}
	                    ";
	            $countCmd = "SELECT count(uid) FROM users AS u WHERE
		                         u.uid IN ($in) $userTypeFilter {$deleted}
		                    ";
	   
	    global $DB;
	    $rows = $DB->cache(600)->rows($cmd);
	    $count = $DB->cache(600)->val($countCmd);
	    return $rows;
     }
    /**
      * ��������� ��������� ������� � �� ��� ������� ������������� �� �� ����      
      * @param int    $userType    ������ ����������� ������  
      *                            0: ������ � ����������� � �������������,   
      *                            1: ������ ������ �����������,
      *                            2: ������ ������ �������������      
      * @return string 
      * */ 
    static private function getUserTypeFilter($userType) {
        $userTypeFilter = '';
            switch ($userType) {
    	        case 1:
                    $userTypeFilter = " AND (substr( CAST(u.role AS varchar), 1, 1) = '0') ";
                    break;
    		    case 2:
                    $userTypeFilter = " AND (substr( CAST(u.role AS varchar), 1, 1) = '1') ";
                   break;    		    				
    	}
    	return $userTypeFilter;
    }
    /**
     * �������� ���������� � ������������ ��� ������ � ���������� ������
     * @param $uid ������������� ������������
     * @return array  
     * */
    static public function getUserShortInfo($uid) {
        global $DB;
        $query = "SELECT u.uid, u.uname, u.usurname, u.login, u.photo, file.path, u.role
                    FROM users AS u
                    LEFT JOIN file 
                        ON file.fname = u.photo 
                    WHERE u.uid = $uid                                                            
                 ";
        $data = $DB->row($query);
        $data["uname"]      = iconv("WINDOWS-1251", "UTF-8//IGNORE", $data["uname"]);
	    $data["usurname"]   = iconv("WINDOWS-1251", "UTF-8//IGNORE", $data["usurname"]);
	    $data['photo']      = get_unanimated_gif($data['login'], $data['photo']);
        if (($data["photo"] === null)||($data["path"] === null)) {
		    $data["photo"] = "/images/temp/small-pic.gif";
		}
		$contacts = explode(',', self::getMsgContact($_SESSION["uid"]));
		$data["isContacts"] = 0;
		foreach ($contacts as $c) {
		    if ($uid == $c) {
		        $data["isContacts"] = 1;
		        break;
		    }
		}
		$found = 0;
		if (count($data)) $found = 1;
		$data = Array(
		                  "record"=>$data,
                          "found"=>$found,                
                          "dav"=>WDCPREFIX
                     );
        return $data;
    }

    /**
     * �������� �������������� ������������� � ������� $uid �������� ���
     * @param int $uid  ������������� ������������
     * @param string $userRole  ����  ������������
     * @param int $limit  ������� ����������� �������
     * @param int $size   ���������� �������
     * @return string ���� (n, n0, n1 ... nN)  
     * */
    static public function GetSbrPartners($uid, $userRole, $limit, &$size) {
        //�������� ���� ������������
        session_start();
        $role = $userRole[0];             
        if ($role !== null)  {//���� ���� ������������ ��������            
	        //�������� ��������� �� ��� ������������            
	        $partner = 'emp_id';
	        $entity  = 'frl_id';
	        if ($role == 1) {
			    $partner = 'frl_id';
	            $entity  = 'emp_id';
	        }
	        $cmd = "SELECT $partner FROM sbr WHERE $entity = ".$_SESSION["uid"]." AND $partner IS NOT NULL ORDER BY reserved_time DESC";
	        $DB = new DB("master");
	        $rawsbr = $DB->cache(600)->rows($cmd);	        	        
	        $data = array();
	        $j = 0; 
	        foreach ($rawsbr as $i) {
	        	$data[$i[$partner]] = $i[$partner]; // ������������ � ������ ������ ���� ���������
	        	$j++;
	        }
	        $size = $j;
	        return (join(",", $data));              
        }       
    }
    
   /**
     * �������� �������������� ��������� ������������     
     * @param $uid   - ������������� ��������� ������������
     * */
    static public function GetMsgContact($uid) {  	
    	if (!$uid) return false;
    	$cmd = "SELECT messages_contacts($uid)";
		$DB = new DB("plproxy");
		$rawdata = $DB->cache(600)->rows($cmd);
		$data    = array();
		foreach ($rawdata as $i) {
			$f = preg_match("#^\((\d+),#", $i["messages_contacts"], $matches);
			if ($matches[1] !== null) $data[] = $matches[1];
		}
		if (count($data)) return (join(",", $data)); 
		return false;					 
    }/**/
    
    /**
     * ��������� ��������� �� ��������� � ������� �� ���������
     * 
     * @param  int $sUid UID ������������
     * @param  string $sField �������� ����
     * @return bool true - �� ���������, false - ��� ������ ���������
     */
    function isChangeOnModeration( $sUid = 0, $sField = '' ) {
        $nVal = $GLOBALS['DB']->val( 'SELECT id FROM users_change WHERE user_id = ?i AND ucolumn = ? AND (moderator_status = 0 OR moderator_status = -1)', $sUid, $sField );
        return !empty($nVal);
    }
    
    /**
     * ���������� email  � uid ������������ �� ��� ����� ��� ������� �� ��������
     * @return array true - �� ���������, false - ��� ������ ���������
     */
    function GetUserInfoByUnsubscribeKey($ukey) {
        global $DB;
        
        $ukey = substr( $ukey, 0, 32 );
        $row  = $DB->row( 'SELECT users.uid, users.email, users.role, users.subscr FROM users_subscribe_keys
            LEFT JOIN users ON users.uid =  users_subscribe_keys.uid
            WHERE users_subscribe_keys.key = ?', 
            $ukey 
        );
        
        return $row;
    }
    
    /**
     * ���������� email  � uid ������������ �� ��� ����� ��� ������� �� ��������
     * @return string ���� ��� ������� �� ��������
     */
    function GetUnsubscribeKey($login) {
        global $DB;
        $row = $DB->row("
        SELECT usk.key, u.uid FROM users u 
          LEFT JOIN users_subscribe_keys AS usk ON u.uid =  usk.uid
        WHERE u.login = '{$login}'
        ");
        $val = $row["key"];
        if (!$val) {
            return users::writeUnsubscribeKey($row["uid"]);
        }
        return $val;
    }
    
    /**
     * ������ ����� ��� ������� �� �������� 
     * @param $uid    - uid ������������
     * @param $force  - ������������ ���� ���, ��� ������� �� ���� ��������
     */
    function writeUnsubscribeKey($uid, $force = false) {
    	$hash = md5(uniqid("usk_", true).$uid.date("Y-m-d H:i:s"));
        global $DB;
        $condition = "AND position('1' IN CAST(users.subscr AS text) ) != 0";
        if ($force) {
            $condition = "";
        }
        $res = $DB->query("UPDATE users_subscribe_keys SET key = ? FROM users WHERE users.uid = ?i {$condition} AND users_subscribe_keys.uid = users.uid", $hash, $uid);
        if (pg_affected_rows($res) == 0) {
            $res = $DB->query("INSERT INTO users_subscribe_keys (uid, key) SELECT uid, ? FROM users WHERE uid = ? {$condition} ", $hash, $uid);
            if (pg_affected_rows($res) == 0) {
                return null;
            } else {
                return $hash;
            }
        }
        return $hash;
    }

    /**
     * ���������� �������� ��� ������ ��������� ������� ������������ (������������ ����� �������)
     *
     * @param object $u    - ���������� � ������������
     */
    public function execOnFirstVisit($u) {
        global $DB;
        if($u->is_visited=='f') {
            if(is_emp($u->role)) {
                $sql = "UPDATE employer SET tabs = tabs & b'11101' WHERE tabs & b'00010' = b'00010' AND uid = ?i;
                        UPDATE employer SET tabs = tabs & b'11110' WHERE tabs & b'00001' = b'00001' AND uid = ?i AND (SELECT count(id) FROM shop WHERE from_user = ?i AND is_deleted='f' AND is_accept='t')=0;
                        ";
                $DB->query($sql, $u->uid, $u->uid, $u->uid);
            } else {
                $sql = "UPDATE freelancer SET tabs = tabs & b'11111011' WHERE tabs & b'00000100' = b'00000100' AND uid = ?i;
                        UPDATE freelancer SET tabs = tabs & b'11111101' WHERE tabs & b'00000010' = b'00000010' AND uid = ?i AND (SELECT count(id) FROM shop WHERE from_user = ?i AND is_deleted='f' AND is_accept='t')=0;
                        ";
                $DB->query($sql, $u->uid, $u->uid, $u->uid);
            }
            $sql = "UPDATE users SET is_visited = 't' WHERE uid = ?i";
            $DB->query($sql,$u->uid);
        }
    }
    
    public function getUserPROPromo($spec = false, $uid) {
        global $DB;
        $spec_sql = "";
        if($spec) {
            $spec     = intval($spec);
            $spec_sql = " AND u.spec_orig = {$spec}";
        }
        $sql = "SELECT 
                  u.login, u.uname, u.usurname, u.is_pro, u.photo, 
                  u.icq, u.skype, u.second_email, u.jabber, u.phone, u.ljuser, u.site,
                  a.id AS p_id, a.link AS lnk, a.name AS p_name, b.id AS b_id, b.name AS g_name
                FROM 
                  freelancer u
                  LEFT JOIN professions a ON a.id = u.spec
		  LEFT JOIN prof_group b ON a.prof_group = b.id
                WHERE u.is_pro = true AND u.uid <> ? {$spec_sql}
                ORDER BY RANDOM() LIMIT 30";
        
        $result = $DB->cache(300)->rows($sql, $uid);
        
        if($result) {
            if(count($result) > 4) {
                //�������� ����������� ������ 4 ������������
                $rnd = array_rand($result, 4);
                $result = array($result[$rnd[0]], $result[$rnd[1]], $result[$rnd[2]], $result[$rnd[3]]);
            }
            
            // �������� ������� ������������, ����
            foreach($result as $num=>$user) {
                if($user['skype'] != '') {
                    $user['name_contact'] = 'skype';
                    $user['ico_contact']  = 'sky';
                    $result[$num] = $user;
                    continue;
                }
                if($user['icq'] != '') {
                    $user['name_contact'] = 'icq';
                    $user['ico_contact']  = 'icq';
                    $result[$num] = $user;
                    continue;
                }
                if($user['second_email'] != '') {
                    $user['name_contact'] = 'second_email';
                    $user['ico_contact']  = 'mail';
                    $result[$num] = $user;
                    continue;
                }
                if($user['phone'] != '') {
                    $user['name_contact'] = 'phone';
                    $user['ico_contact']  = 'tel';
                    $result[$num] = $user;
                    continue;
                }
                if($user['site'] != '') {
                    $user['name_contact'] = 'site';
                    $user['ico_contact']  = 'www';
                    $result[$num] = $user;
                    continue;
                }
                if($user['jabber'] != '') {
                    $user['name_contact'] = 'jabber';
                    $user['ico_contact']  = 'jb';
                    $result[$num] = $user;
                    continue;
                }
                if($user['ljuser'] != '') {
                    $user['name_contact'] = 'ljuser';
                    $user['ico_contact']  = 'lj';
                    $result[$num] = $user;
                    continue;
                }
            }
            
            return $result;
            
        } else {
            return false;
        }
    }
    
    /**
     * �������� ���������� � ������������ �� ID ���. �����
     *
     * @param    string    $type    ���. ����
     * @param    string    $id      ID ������������ � ���. ����
     * @return   array              ���������� � ������������
     */
    public function getUserBySocialID($type, $id) {
        global $DB;
        $sql = "SELECT uid, login, passwd FROM users WHERE snet_{$type}=?u";
        $user = $DB->row($sql, $id);
        return $user;
    }

    /**
     * �������� ���������� � ������������ �� email �� ���. �����
     *
     * @param    string    $email   email ������������ � ���. ����
     * @return   array              ���������� � ������������
     */
    public function getUserBySocialEmail($email) {
        global $DB;
        $sql = "SELECT uid, login, passwd FROM users WHERE email=?u";
        $user = $DB->row($sql, $email);
        return $user;
    }

    /**
     * �������� ���������� � ���. ���� � ������������
     *
     * @param    string    $type    ���. ����
     * @param    string    $id      ID ������������ � ���. ����
     * @param    string    $uid     ID ������������
     */
    public function updateUserSocialID($type, $id, $uid) {
        global $DB;
        $sql = "UPDATE users SET snet_{$type}=?u WHERE uid=?i";
        $user = $DB->query($sql, $id, $uid);
        return $user;
    }
    
    /**
     * ���������, ������������ �� ������������ �� ����� ��� � �����
     * @param    string    $uid     ID ������������
     */
    static public function userWasInOldYear($uid) {
        global $DB;
        $uid = (int)$uid;
        $row = $DB->row("SELECT week_pro_action.id, role FROM week_pro_action LEFT JOIN users ON users.uid =week_pro_action.uid WHERE ts IS NULL AND week_pro_action.uid = {$uid}");
        if ($row['id'] > 0) {
            return $row;
        }
        return false;
    }

    /**
     * �������� ����� ������������
     * @param    string    $uid     ID ������������
     */
    static public function GetUserLangs($uid) {
        global $DB;
        $uid = (int)$uid;
        $rows = $DB->rows("SELECT user_langs.id, lang_id, name, quality FROM user_langs  LEFT JOIN languages ON user_langs.lang_id = languages.id WHERE uid  = {$uid} ORDER BY languages.weight, user_langs.id");
        if ($rows[0]['lang_id'] > 0) {
            return $rows;
        }
        return false;
    }
    
    /**
     * ��������� �������� ��������� �������������
     * 
     * @param type $post_contacts
     * @param type $contacts
     * @return string
     */
    static public function validateContacts($post_contacts, &$contacts) {
        $error = array();
        
        foreach ($post_contacts as $name => $value) {
            if (!isset($contacts[$name]))
                continue;
            switch ($name) {
                case 'phone':
//                    if (!preg_match('/^[+]*?[0-9\\s]{9,17}$/', $value) && trim($value) != '') {
//                        $error["contact_{$name}"] = '���� ��������� �����������';
//                    }
                    break;
                case 'site':
                    if (!url_validate(ltrim(ltrim($value, 'http://'), 'https://')) && trim($value) != '') {
                        $error["contact_{$name}"] = '���� ��������� �����������';
                    }
                    if (strpos($value, 'htt') === false && trim($value) != '') {
                        $value = 'http://' . $value;
                    }
                    break;
                case 'email':
                    if (!is_email($value) && trim($value) != '') {
                        $error["contact_{$name}"] = '���� ��������� �����������';
                    }
                    break;
            }
            $contacts[$name]['value'] = __paramValue('htmltext', stripslashes($value));
        }
        return $error;
    }
    
    /**
     * ��������� ��������������� �� ������������ ����� ������.������
     * 
     * @param  int uid ������������
     * @return bool true - ���������������, false - ���
     */
    public function isYdVerified( $uid = 0 ) {
        $nId = $GLOBALS['DB']->val( 'SELECT id FROM verify_yd WHERE user_id = ?i', $uid );
        return !empty($nId);
    }
    
        
    
    /**
     * ������������ ��� �������?
     * 
     * @return boolean
     */
    public function isNoob()
    {
        if(!isset($this->reg_date)) {
            return false;
        }
        
        $ts = strtotime($this->reg_date);
        return ($ts + self::NOOB_TIME_DAYS * 86400) > time();
    }
    
  
    
    /**
     * ������� ������ ������������ PROFI?
     * 
     * @return type
     */
    public function isProfi()
    {
        return $this->is_profi == 't';
    }
    
    
    
    /**
     * �������� ������ ����� is_profi ��� ���� ID
     * 
     * @global DB $DB
     * @param type $ids
     * @return type
     */
    public function getUsersProfi($ids = array())
    {
        global $DB;
        
        $result = array();
        
        $data = $DB->cache(1800)->rows("
            SELECT uid, is_profi 
            FROM users 
            WHERE uid IN(?l)", $ids);
        
        if($data) {
            foreach($data as $el){
                $result[$el['uid']] = $el['is_profi'];
            }
        }
        
        return $result;
    }        
    
    public function getAnchor($key, $num, $maxlength)
    {
        $anchor = '';
        $field = $key . ($num ? '_'.$num : '');
        $realField = $key == 'email' && $num == 0 ? 'second_email' : $field;
        if (isset($this->{$realField})) {
            if ($this->{$field . '_as_link'} == 't') {
                switch ($key) {
                    case 'skype':
                        $anchor = "��������� �� skype";
                        break;
                    case 'email':
                        $anchor = "�������� ������";
                        break;
                }
            } else {
                $anchor = LenghtFormatEx($this->{$realField}, $maxlength);
            }
        }
        return $anchor;
    }
    
    
    /**
     * ���������� ������������� ������������ ��� ����������
     */
    public static function getCid()
    {
        return isset($_COOKIE['_ga_cid']) ? $_COOKIE['_ga_cid'] : '';
    }
}

