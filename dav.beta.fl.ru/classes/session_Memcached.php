<?
/**
 * ��������� � �������� ������ (memcached)
 * �� ��������� FREETRAY
 *
 */
class session extends Memcached
{

	/** 
	 * �������� ������ � ��������� ����������� (��. view_online_status())
	 * @var string
	 */
    public $ago;
	
	/** 
	 * ���� ��������� ���������� (��. view_online_status())
	 * @var string
	 */
    public $last_ref;
    
	/** 
	 * ������� �� ��� ������������ (��. view_online_status())
	 * @var boolean
	 */
    public $is_active;
    
	/** 
	 * ���� �� ���������� � �������� ���-����
	 *
	 * @var boolean
	 */
	private $bIsConnected = false;
    
    /**
     * ����� ��� ������ ����
     * 
     * @var object 
     */
    private $_log = NULL;
    
    /**
     * ���� �� ������ ��� ������ � memcache
     * 
     * @var type 
     */
    public $err = FALSE;
	
	/**
	 * �����������. ������������ � ������� �������
	 * 
	 */
	function __construct() {
		parent::__construct();
        if ( !($server = $GLOBALS['memcachedSessionServer']) ) {
            // � /classes/config.php ���������:
            // $memcachedSessionServer = 'localhost';
            if ( !($server = $GLOBALS['memcachedServers'][0]) )
                die('�� ������� ������� Memcached');
        }
        $this->bIsConnected = $this->addServer($server, 11211);
        
        $this->setOption (self::OPT_PREFIX_KEY , SERVER);
        
        $this->_log = new log('sessions/error-%d%m%Y.log', 'a');
	}
	
	/**
	 * ������� ����������
	 *
	 * @return boolean
	 */
   function open() {
       return $this->bIsConnected;
   }
   
   /**
    * ������� ����������
    *
    * @return boolean
    */
   function close() {
       return true;//$this->close();
   }
   
   /**
    * ������ ������ �� ����
    *
    * @param string $sessID		������������� ������
    * @return array				������ � ������� �� ������ ��� ������ ������ (���� ������ �� �������)
    */
   function read($sessID) {
       // fetch session-data
	   $results = "";
       $res = $this->get($sessID);
       // return data or an empty string at failure
       $this->_error('get', $sessID);
       if ( $res ) {
           return $res;
       } else  {
           $this->_error('get', $sessID);
       }
       return settype($results, 'string');
   }
   
   /**
    * ��������� ������ ������ � ���
    *
    * @param string $sessID		������������� ������
    * @param array $sessData	������ ������
    * @return boolean			true � ������ ������� ������
    */
   function write($sessID, $sessData) {
       $ret = $this->set($sessID, $sessData, 7200);
       if ( $ret === FALSE ) {
           $this->_error('set', $sessID);
       }
       if ( !empty($_SESSION['login']) ) {
           $last_ref['date'] = $_SESSION['last_refresh']; // ��. users::regVisit()
           $last_ref['sid'] = $sessID;
           $ret = $this->set($_SESSION['login'], $last_ref, 7200);
            if ( $ret === FALSE ) {
                $this->_error('set', $sessID);
            }
       }
       return $ret;
   }
   
   /**
    * ������� ������ �� �� ID
    *
    * @param string $sessID		������������� ������
    * @return boolean			true � ������ �����
    */
   function destroy($sessID) {
       // delete session-data
       $ret = $this->delete($sessID);
       return $ret;
   }
   
   /**
    * ������� ������ - ������������ ���������� ��� memcache ��. write()
    *
    * @return boolean	������ ���������� true	
    */
   function gc() {

       return true;
   }
   
   /**
    * ���� �� ���� �� �����
    *
    * @param string $login		����� �����
    * @return string			���� ��������� ���������� ����� � ������� ISO 8601 ��� 0 ���� ������ �� �������
    */
   function getActivityByLogin($login){
   		if (!$login) return 0;
   		$last_ref = $this->get($login);

        if (!isset($last_ref['sid']) && isset($last_ref['sess_id'])) {
            $last_ref['sid'] = $last_ref['sess_id'];
            
            if (isset($last_ref['data']['date'])) {
                $last_ref['date'] = $last_ref['data']['date'];
            }
        }

   		if ($last_ref)
   			$sessData = $this->get($last_ref['sid']);
		if($sessData)
           return $last_ref['date'];
        else $this->destroy($login);
		return 0;
   }
   
    /**
     * ���������� ���������� ����� �� �����
     * 
     * @param  string $login ����� �����
     * @return bool true - �����, false - ������
     */
    function nullActivityByLogin( $login ) {
        $bRet = false;
        
        if ( $login ) {
            $last_ref = $this->get( $login );
            
            if ( $last_ref) {
                $last_ref['date'] = null;
                $ret = $this->set( $login, $last_ref, 7200);
                $bRet = true;
            }
        }
        
        return $bRet;
    }

   /**
   * ��������� ���� ��������� PRO � ������ ������������
   * 
   * @param string $login   ����� ������������
   */
   function UpdateProEndingDate($login) {
        if(!$login) return;
        $s = $this->get($login);
        if($s) {
            require_once($_SERVER['DOCUMENT_ROOT']."/classes/payed.php");
            $pro_last = payed::ProLast($login);
            $pro_last = $pro_last['freeze_to'] ? false : $pro_last['cnt'];
            $session_data = $this->read($s['sid']);
            
            $session_data = preg_replace(
                    "/;pro_last\|(?:s:0:\"\"|s:[0-9]{2}:\".*\"|b\:0|N)/U",
                    ";pro_last|s:".strlen($pro_last).":\"$pro_last\"",
                    $session_data);
            
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/account.php";
    		$user = new users();
            $user->GetUser($login);
            $account = new account();
            $account->GetInfo($user->uid);
            $session_data = preg_replace("/ac_sum\|s:\d{1,}:\".*\"/U","ac_sum|s:".strlen($account->sum).":\"$account->sum\"",$session_data);
            $session_data = preg_replace("/is_profi\|b:[0-1]/U","is_profi|b:".(($user->isProfi())?'1':'0'), $session_data);
            
            $this->set($s['sid'],$session_data,7200);
        }
   }
   
   /**
   * ��������� ������ ����������� � ������ ������������
   * 
   * @param string $login   ����� ������������
   */
   function UpdateVerification($login) {
        if(!$login) return;
        $s = $this->get($login);
        if($s) {
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
            $user = new users();
            $user->GetUser($login);
            
            $session_data = $this->read($s['sid']);
            
            $session_data = preg_replace("/is_verify\|s:1:\"[ft]\"/U", "is_verify|s:1:\"".$user->is_verify."\"", $session_data);

            $this->set($s['sid'],$session_data,7200);
        }
   }
   
   /**
    * ��������� ������ � ������������ �� ��� ������
    * 
    * @param string $login    ����� ������������
    * @return type 
    */
   function UpdateAccountSum($login) {
        if(!$login) return;
        $s = $this->get($login);
        if($s) {
            $session_data = $this->read($s['sid']);
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/account.php";
    		$user = new users();
            $user->GetUser($login);
            $account = new account();
            $account->GetInfo($user->uid);
            $session_data = preg_replace("/ac_sum\|s:\d{1,}:\".*\"/U","ac_sum|s:".strlen($account->sum).":\"$account->sum\"",$session_data);
            $session_data = preg_replace("/ac_sum\|d:\d+?;/U", "ac_sum|s:".strlen($account->sum).":\"$account->sum\";", $session_data);
            $session_data = preg_replace("/bn_sum\|s:\d{1,}:\".*\"/U","bn_sum|s:".strlen($account->bonus_sum).":\"$account->bonus_sum\"",$session_data);
            $this->set($s['sid'],$session_data,7200);
        }
   }
   
    /**
    * ��������� ������ �� ���������
    * 
    * $login - � ���� ��������� ������
    * $antiUser - ������ ������ users � ������� �������
    */
    /*public function UpdateAntiuser ($login, $antiUser) {
        if (!$login) return;
        $s = $this->get($login);
        if (!$s) return;
        $s['anti_uid'] = $antiUser->uid;
        $s['anti_login'] = $antiUser->login;
        $s['anti_surname'] = $antiUser->surname;
        $s['anti_name'] = $antiUser->name;
        $set = $this->set($login, $s, 7200);
    }*/

   /**
    * ���������� ������ ���������� ����� �� �����
    *
    * @param string $login		����� �����
    * @param boolean $full		���������� �� ��������� ���������� ("��� �� �����")
    * @return string			HTML-��� ������ ����������
    */
   function view_online_status($login, $full=false, $nbsp='&nbsp;', &$activity = NULL){
        $this->is_active = false;
        $this->ago = 0;
        $this->last_ref = NULL;
		if ($login) {
			$this->last_ref = $this->getActivityByLogin($login);
			$activity = $this->last_ref;
			$last_ref_unixtime = strtotime($this->last_ref);
		}
		if ($this->last_ref && (time() - $last_ref_unixtime <= 30*60)) {	      
            $this->ago = ago_pub($last_ref_unixtime);
			$this->ago = ago_pub(strtotime($this->last_ref));
            $this->is_active = true;
			if (intval($this->ago) == 0) $this->ago = '����� ������';
		/*	return  ($full ? "<span class='u-act' title=\"��������� ���������� ���� ".$this->ago." �����\">�� �����</span>" : "{$nbsp}<img src=\"/images/dot_active.png\" class=\"u-inact\" alt=\"��������� ���������� ���� ".$this->ago." �����\" title=\"��������� ���������� ���� ".$this->ago." �����\" />$nbsp");*/
		/*	return  "<span class='b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_6db335 b-layouyt__txt_weight_normal'>�� �����.</span>"; */
        return  ($full ? "<span class='b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_6db335 b-layouyt__txt_weight_normal'>�� �����.</span>" : "<span class=\"b-icon b-icon__lamp\" title='�� �����'></span>$nbsp");
		}
	/*	return ($full ? "<span class='u-inact'>��� �� �����</span>" : "{$nbsp}<img src=\"/images/dot_inactive.png\" class=\"u-inact\" alt=\"��� �� �����\" title=\"��� �� �����\" />$nbsp");*/
	/*	return "<span class='b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_808080 b-layouyt__txt_weight_normal'>��� �� �����.</span>"; */
      return ($full ? "<span class='b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_808080 b-layouyt__txt_weight_normal'>��� �� �����.</span>" : '');
	}

   /**
    * ���������� ������ ���������� ����� �� ����� (����� ������ view_online_status)
    *
    * @param string $login		����� �����
    * @param boolean $full		���������� �� ��������� ���������� ("��� �� �����")
    * @return string			HTML-��� ������ ����������
    */
    function view_online_status_new($login, $full=false, $nbsp='&nbsp;', &$activity = NULL){
        if ($login)
            $last_ref = $this -> getActivityByLogin($login);
        $activity = $last_ref;
        $last_ref_unixtime = strtotime($last_ref);
        if ($last_ref && (time() - $last_ref_unixtime <= 30*60)){
            $ago = ago_pub(strtotimeEx($last_ref));
            if (intval($ago) == 0) $ago = "����� ������";
          /*  return  ($full ? "<span class='u-act' title=\"��������� ���������� ���� ".$ago." �����\">�� �����</span>" : "{$nbsp}<img src=\"/images/dot_active.png\" class=\"u-act\" alt=\"��������� ���������� ���� ".$ago." �����\" title=\"��������� ���������� ���� ".$ago." �����\" />$nbsp");*/
            return  "<span class='b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_6db335 b-layouyt__txt_weight_normal'>�� �����.</span>";
        }
        /*return  ($full ? "<span class='u-inact'>��� �� �����</span>" : "{$nbsp}<img src=\"/images/dot_inactive.png\" width=\"8\" height=\"9\" alt=\"��� �����\" class=\"u-inact\" title=\"��� �� �����\" />$nbsp");*/
        return  "<span class='b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_808080 b-layouyt__txt_weight_normal'>��� �� �����.</span>";
    }
	
	/**
	 * ���������� ������ ������������ �� ��� ������
	 *
	 * @param string $login		����� �����
	 * @return boolean			true ���� ������ ���� ����������, false - ���� �� �������
	 */
	function logout($login){
		if (!$login) return 0;
   		$last_ref = $this->get($login);
   		if ($last_ref['sid'])
   		    $this->destroy($last_ref['sid']);
   		$ret = $this->destroy($login);
       return $ret;
	}

    /**
     * ������ � ��� ��� ������������� ������
     * 
     * @param  string  $optype  ��� �������� (get, set, add)
     * @param  string  $key     ���� � memcache
     * @return void
     */
    private function _error($optype = NULL, $key = NULL) {
	    if(!$this->_log->linePrefix) {
    		$this->_log->linePrefix = '%d.%m.%Y %H:%M:%S - ' . getRemoteIP()
    		                        . ' - "'
    		                        . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI']
    		                        . ($_SERVER['REQUEST_METHOD']=='POST' && $_POST ? '?'.http_build_query($_POST) : '')
    		                        . '" : ';
		}
	    $rcode = $this->getResultCode();
	    $rmsg = $this->getResultMessage();
	    $ttime = $this->_log->getTotalTime('%H:%M:%S', 3);
	    if($rcode == Memcached::RES_NOTFOUND
	       || $rcode == Memcached::RES_SUCCESS
	       || ($optype == 'add' && $rcode == Memcached::RES_NOTSTORED)
	      )
	    {
	        return;
	    }
        $this->err = TRUE;
	    $this->_log->writeln("[error: {$rcode}, method: {$optype}, key: {$key}, time: {$ttime}] {$rmsg}");
	}
    
    
    
    /**
     * ��������� ��������� �� ���������� ��������� � ��������
     * 
     * @param type $value - ���� ���������
     * @param string $key - ���� ��� �������������� ���������
     * @param type $type - ��� (���� �� ������������)
     * @return boolean
     */
    public static function setFlashMessage($value, $key = 'default', $type = 'success')
    {
        if (empty($value))  {
            return false;
        }
        
        $_SESSION['flash_message'][$key] = array(
            'type' => $type, 
            'value' => $value);
        return true;
    }
    
    
    /**
     * �������� ������� ���������
     * 
     * @return string
     */
    public static function getFlashMessages($key = 'default')
    {   
        if (!isset($_SESSION['flash_message'][$key])) {
            return '';
        }
        
        $message = $_SESSION['flash_message'][$key]['value'];
        unset($_SESSION['flash_message'][$key]);
        return $message;
    }  
    
}