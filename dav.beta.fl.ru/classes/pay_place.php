<?php
/**
 * ���������� ���� � ��������� ���������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");


/**
 * ����� ��� ������ � �������� �������
 *
 */
class pay_place
{
	/**
	 * ����� ����� ������� ������ ������������ ������������ (� ��������)
	 *
	 * @var integer
	 */
	var $time_show = 10; 
	
	/**
	 * ����� ����� ������� ������ ��������� ���� �� ������� ����� ������ (� ��������)
	 *
	 * @var integer
	 */
	var $time_delete = 10; 
	
	/**
	 * ������� ������� ������� � �������
	 *
	 * @var integer
	 */
	var $history_count = 16;
	
	/**
	 * ��� ����� (��. ����. paid_places)
	 * 
	 * @var integer
	 */
	var $type_place = 0;
    
    /**
     * ����������� ���������� ���������� �������� � ��������� ����������
     */
    const MAX_HEADER_SIZE = 64;
    
    /**
     * ����������� ���������� ���������� �������� � ������ ����������
     */
    const MAX_TEXT_SIZE = 500;
    
    /**
     * ���� ���������� �� �������
     */
    const PRICE_FRONT = 299;
    
    const PRICE_FRONT_DISCOUNT = 239;
    
    /**
     * ���� ���������� � �������� �����������
     */
    const PRICE_USERS = 299;

    
    const INTERVAL_FORMAT = 'hours';//'minutes'; //hours
    

    public static $_TABLE           = 'paid_places';
    public static $_TABLE_REQUEST   = 'paid_places_request';


    /**
	 * ����������� ������
	 *
	 * @param bool $type ��� ����� (��. ����. paid_places)
	 */
	function __construct($type=false) {
	    if($type) $this->type_place = $type;
	}
	
    
    
    public static function getPrice()
    {
        return isProfi()?self::PRICE_FRONT_DISCOUNT:self::PRICE_FRONT;
    }


    public function setTypePlace($type)
    {
        $this->type_place = $type;
    }
    

    /**
     * �������� (��� ��������) ������ �� ���������� � ��������
     * 
     * @global type $DB
     * @param type $options
     * @return boolean
     */
    public function addUserRequest($uid, $options)
    {
        global $DB;
        
        if (!$uid) {
            return false;
        }
        
        $insert = array(
            'uid' => $uid,
            'type_place' => $this->type_place,
            'ad_header' => (isset($options['ad_header']))?substr($options['ad_header'], 0, self::MAX_HEADER_SIZE):NULL,
            'ad_text' => (isset($options['ad_text']))?substr($options['ad_text'], 0, self::MAX_TEXT_SIZE):NULL,
            'ad_img_file_name' => @$options['ad_img_file_name'],
            'num' => isset($options['num'])?$options['num']:1
        );
        
        if (isset($options['hours'])) {
            $insert['hours'] = $options['hours'];
        }
        
        $is_done = !!$DB->insert(static::$_TABLE_REQUEST, $insert);
        
        //����� ��������� ���������� ����� ������� ����������
        //���� �������� �� ������ �� ����������
        if ($is_done) {
            $this->cron($uid);
        }
        
        return $is_done; 
    }


    /**
     * �������� �������� ���������� �� ������� �������� �� ����������
     * 
     * @global type $DB
     * @return type
     */
    public static function getTypePlacesInRequest()
    {
        global $DB;
        
        return $DB->col("
            SELECT type_place
            FROM " . static::$_TABLE_REQUEST . "
            GROUP BY type_place
        "); 
    }

    
    public function getUserRequest($uid)
    {
        global $DB;
        
        return $DB->row("
            SELECT *, (date_published + (interval '1 " . self::INTERVAL_FORMAT . "' * hours)) AS next_date_published 
            FROM " . static::$_TABLE_REQUEST . " 
            WHERE uid = ?i AND type_place = ?i
        ", $uid, $this->type_place);         
    }

    

    /**
     * ���� ��������� ���� �������� �� ����������
     * ������������� �������� � ���� ��� 1-2 �����
     * 
     * @return array
     */
    public static function cronRequest()
    {
        $type_places = static::getTypePlacesInRequest();
        
        if(!$type_places) {
            return $type_places;
        }
        
        $res = array();
        $payPlace = new self;
        
        foreach ($type_places as $type_place) {
            $payPlace->setTypePlace($type_place);
            $res[$type_place] = $payPlace->cron();
        }

        return $res;
    }

    
    /**
     * ���� ��������� �������� �������� ����������
     * 
     * @param int $uid - ���� ������ ��� ����������� ������������
     * @global type $DB
     * @return boolean|int
     */
    public function cron($uid = 0)
    {
        global $DB;
        
        //���� ������������ ����������� �� ��� 
        //�� ���������� �� ������ ����� ������ �������
        if(!$this->isDone()) {
            return false;
        }
        
        $uids = $DB->col("
            SELECT uid
            FROM " . static::$_TABLE . " 
            WHERE type_place = ?i 
            ORDER BY date_create DESC LIMIT 5 OFFSET 0
        ", $this->type_place);

        $where = (count($uids))?$DB->parse(" AND ppr.uid NOT IN(?l)", $uids):'';
        if ($uid > 0) {
            $where .= $DB->parse(" AND ppr.uid = ?i", $uid);
        }
        
        $list = $DB->rows("
            SELECT
                (
                    SELECT id 
                    FROM paid_places AS pp 
                    WHERE pp.uid = ppr.uid AND pp.type_place = ?i
                    ORDER BY pp.date_create DESC 
                    LIMIT 1
                ) AS pp_id,
                ppr.num,
                ppr.uid,
                ppr.ad_header,
                ppr.ad_text,
                ppr.ad_img_file_name,
                ppr.type_place
            FROM " . static::$_TABLE_REQUEST . " AS ppr
            WHERE 
                ppr.num > 0 AND ppr.type_place = ?i 
                AND (ppr.date_published IS NULL OR ppr.date_published <= NOW() - interval '1 ".self::INTERVAL_FORMAT."' * ppr.hours) 
                {$where}
            ORDER BY ppr.date_update ASC, ppr.id ASC
        ", $this->type_place, $this->type_place);
        
        $cnt = 0;        
                
        if ($list) {
            foreach ($list as $req){
                $is_done = false;
                $pp_id = $req['pp_id'];
                $num = $req['num'];
                unset($req['pp_id'], $req['num']);
                $req['date_create'] = 'NOW()';
                $req['is_done'] = 0;
                
                if ($pp_id > 0) {
                    if (empty($req['ad_img_file_name'])) {
                        unset($req['ad_img_file_name']);
                    }
                    
                    $is_done = $this->updatePaidPlaceWithOutAuth($pp_id, $req);
                } else {
                    $is_done = $this->addUserTopNew($req);
                }
                
                if ($is_done) {
                    $DB->query("
                        UPDATE " . static::$_TABLE_REQUEST . " 
                        SET 
                            num = num - 1,
                            " . (($num-1 == 0)?"hours = 0,":"") . "
                            date_published = ?
                        WHERE uid = ?i    
                    ", $req['date_create'], $req['uid']);
                    
                    $cnt++;
                }
            }
            
        }
        
        return $cnt;
    }




    /**
	 * �������� ������������ � ������� (������� ������������ ��� ������� ������������)
	 *
	 * @param integer $uid Id ������������
     * @param string $adHead ��������� ���������� ��������� � ����������
     * @param string $adText ����� ���������� ���������� ������ ������������
     * @param string $adImg �������� ���������� ������� � ����������
	 * @return boolean false - ���� ������, true - ���� ������������ ��� ��������
	 */
	function addUser($uid, $adHead = null, $adText = null, $adImg = null) {
	    global $DB;
        $insert = array(
            'uid' => $uid,
            'type_place' => $this->type_place
        );
        if ($adHead) {
            $insert['ad_header'] = substr( $adHead, 0, self::MAX_HEADER_SIZE );
        }
        if ($adText) {
            $insert['ad_text'] = substr( $adText, 0, self::MAX_TEXT_SIZE );
        }
        if ($adImg) {
            $insert['ad_img_file_name'] = $adImg;
        }
        return !!$DB->insert('paid_places_queue', $insert);
	}
    
    /**
     * �������� ���������� � paid_places
     * ��� ������������� ���������� ���������� �� �������������. ��� ������������� ������� ����� ��������.
     * 
     * @param  int $nId ������������� ����������
     * @param  array $aParams ������ � �������, ���������������� �����, ������� ����� ��������
     * @param  bool $bDelImg �����������. ���������� � true, ���� ����� ������� ��������. 
     *         (!) ���� true - ������������ �������� � $aParams �� null
     * @return bool true - �����, false - ������
     */
    function updatePaidPlace( $nId = 0, $aParams = array(), $bDelImg = false ) {
        // ���� ������� paid_places ������� ����� �������� ��� ������ ���� �������
        $aFld  = array( 'ad_header', 'ad_text', 'ad_img_file_name' );
        $aData = array(); // ����������� ������ ��� ����������
        $bRet  = false;
        $sFile = ''; // ��� ����� ��� ��������
        
        foreach ( $aFld as $sFld) {
            if ( array_key_exists($sFld, $aParams) ) {
                $aData[$sFld] = $aParams[$sFld];
            }
        }
        
        if ( $nId && ($aData || $bDelImg) ) {
            $aPlace = $GLOBALS['DB']->row( 'SELECT p.uid, p.ad_img_file_name, u.login FROM paid_places p 
                INNER JOIN freelancer u ON u.uid = p.uid 
                WHERE p.id = ?i', $nId );
            
            if ( $aPlace['uid'] == $_SESSION['uid'] || hasPermissions('users') ) {
                // ����� ���� �����
                if ( $aPlace['uid'] == $_SESSION['uid'] && !hasPermissions('users') 
                    /*&& (!empty($aData['ad_header']) || !empty($aData['ad_text']) || !empty($aData['ad_img_file_name']))*/ 
                ) {
                    // �����, �� ����� - �������� �� ����������� ������������ (�� ����� �������� ����� "�������� ��������")
                    $aData['moderator_status'] = 0;
                }
                
                if ( $bDelImg ) {
                    $sFile = !empty($aData['ad_img_file_name']) ? $aData['ad_img_file_name'] : '';
                    $aData['ad_img_file_name'] = null;
                }

                $GLOBALS['DB']->update( 'paid_places', $aData, 'id = ?i', $nId );

                $bRet = empty( $GLOBALS['DB']->error );
                
                if ( $bRet ) {
                    // �������� ������ ���� �����
                    if ( 
                        ($aPlace['ad_img_file_name'] || $sFile) && $bDelImg
                        || $aPlace['ad_img_file_name'] && array_key_exists('ad_img_file_name', $aData) && empty($aData['ad_img_file_name'])
                    ) {
                        // �������� ����� ������� ��������. ��� ���� ���� ������ ���� ����� ��������
                        // ��� �������� ������ ��� ����� ��������. ��� ���� ���� ������ ��������
                        $dir  = $aPlace['login'];
                        $file = new CFile();
                        
                        if ( $aPlace['ad_img_file_name'] ) {
                            // ���� ��� ������� �������� ����� ���� ����� ������� "�� ������� ��������" � "� �������� ���-��������", 
                            // �� ����������� �������� ����� ���� �� �����. ������� ����� ��������� - ��������
                            if ( !$GLOBALS['DB']->val('SELECT id FROM paid_places WHERE ad_img_file_name = ?', $aPlace['ad_img_file_name']) ) {
                                $file->Delete( 0, 'users/' . substr($dir, 0, 2) . '/' . $dir . '/foto/', $aPlace['ad_img_file_name'] );
                            }
                        }
                        
                        if ( $sFile ) {
                            $file->Delete( 0, 'users/' . substr($dir, 0, 2) . '/' . $dir . '/foto/', $sFile );
                        }
                    }
                }

                if ( $bRet && $aPlace['uid'] == $_SESSION['uid'] && !hasPermissions('users') 
                    /*&& (!empty($aData['ad_header']) || !empty($aData['ad_text']) || !empty($aData['ad_img_file_name']))*/ 
                ) {
                    // �����, �� ����� - ��������� � ������� �� �������������
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
                    
                    // ������� ���������� ���������� �� �������� ��� �������� � �������
                    $stop_words    = new stop_words( true );
                    $nStopWordsCnt = $stop_words->calculate( 
                        !empty($aData['ad_header']) ? $aData['ad_header'] : '', 
                        !empty($aData['ad_text']) ? $aData['ad_text'] : '' 
                    );

                    $DB->insert( 'moderation', array('rec_id' => $nId, 'rec_type' => user_content::MODER_CAROUSEL, 'stop_words_cnt' => $nStopWordsCnt) );
                }
            }
        }
        
        return $bRet;
    }
    
    
    /**
     * ������ updatePaidPlace ������ ��� �������� ���� �������
     * 
     * @param type $nId
     * @param type $aParams
     * @param type $bDelImg
     * @return type
     */
    function updatePaidPlaceWithOutAuth( $nId = 0, $aParams = array(), $bDelImg = false ) {
        global $DB;
        
        // ���� ������� paid_places ������� ����� �������� ��� ������ ���� �������
        $aFld  = array( 'ad_header', 'ad_text', 'ad_img_file_name', 'date_create', 'is_done');
        $aData = array(); // ����������� ������ ��� ����������
        $bRet  = false;
        $sFile = ''; // ��� ����� ��� ��������
        
        foreach ( $aFld as $sFld) {
            if ( array_key_exists($sFld, $aParams) ) {
                $aData[$sFld] = $aParams[$sFld];
            }
        }
        
        if ( $nId && ($aData || $bDelImg) ) {
            
            // �������� �� ����������� ������������ (�� ����� �������� ����� "�������� ��������")
            $aData['moderator_status'] = 0;
    
            if ( $bDelImg ) {
                $sFile = !empty($aData['ad_img_file_name']) ? $aData['ad_img_file_name'] : '';
                $aData['ad_img_file_name'] = null;
            }

            $DB->update( 'paid_places', $aData, 'id = ?i', $nId );

            $bRet = empty( $DB->error );

            if ( $bRet ) {
                
                $aPlace = $DB->row( 'SELECT p.uid, p.ad_img_file_name, u.login FROM paid_places p 
                INNER JOIN freelancer u ON u.uid = p.uid 
                WHERE p.id = ?i', $nId );
                    
                // �������� ������ ���� �����
                if ( 
                    ($aPlace['ad_img_file_name'] || $sFile) && $bDelImg
                    || $aPlace['ad_img_file_name'] && array_key_exists('ad_img_file_name', $aData) && empty($aData['ad_img_file_name'])
                ) {
                    // �������� ����� ������� ��������. ��� ���� ���� ������ ���� ����� ��������
                    // ��� �������� ������ ��� ����� ��������. ��� ���� ���� ������ ��������
                    $dir  = $aPlace['login'];
                    $file = new CFile();

                    if ( $aPlace['ad_img_file_name'] ) {
                        // ���� ��� ������� �������� ����� ���� ����� ������� "�� ������� ��������" � "� �������� ���-��������", 
                        // �� ����������� �������� ����� ���� �� �����. ������� ����� ��������� - ��������
                        if ( !$DB->val('SELECT id FROM paid_places WHERE ad_img_file_name = ?', $aPlace['ad_img_file_name']) ) {
                            $file->Delete( 0, 'users/' . substr($dir, 0, 2) . '/' . $dir . '/foto/', $aPlace['ad_img_file_name'] );
                        }
                    }

                    if ( $sFile ) {
                        $file->Delete( 0, 'users/' . substr($dir, 0, 2) . '/' . $dir . '/foto/', $sFile );
                    }
                }
            }

            //��������� ���� �� ��������� �������� �� �������������
            if ( $bRet ) {
                // �����, �� ����� - ��������� � ������� �� �������������
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );

                // ������� ���������� ���������� �� �������� ��� �������� � �������
                $stop_words    = new stop_words( true );
                $nStopWordsCnt = $stop_words->calculate( 
                    !empty($aData['ad_header']) ? $aData['ad_header'] : '', 
                    !empty($aData['ad_text']) ? $aData['ad_text'] : '' 
                );

                $DB->insert( 'moderation', array('rec_id' => $nId, 'rec_type' => user_content::MODER_CAROUSEL, 'stop_words_cnt' => $nStopWordsCnt) );
            }
         
        }
        
        return $bRet;
    }
    
    
    /**
     * ���������� ���������� (paid_places) 
     * 
     * @param  int $nId ������������� ����������
     * @return array
     */
    function getPaidPlace( $nId = 0 ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        
        $aReturn = array();
        $sQuery  = 'SELECT p.id, p.ad_header, p.ad_text, p.ad_img_file_name, p.type_place, pr.name as title, 
                u.uid, u.login, u.uname, u.is_pro, u.is_pro_test, u.is_team, u.usurname, u.is_chuck, u.warn, u.is_banned, 
                u.ban_where, u.status_text as descr, u.photo  
            FROM paid_places p 
            INNER JOIN freelancer u ON u.uid = p.uid 
            LEFT JOIN professions pr ON pr.id = u.spec_orig 
            WHERE p.id = ?i';
        
        return $GLOBALS['DB']->row( $sQuery, $nId );
    }
	
	/**
	 * �������� ������������ � ������� (������� ������������ ��������������� ��� ������� �������)
	 *
	 * @param integer $uid Id ������������
	 * @param integer $type ��� ����� (��. ����. paid_places)
	 * @return boolean true - ���� ��� ������ ������, ����� false
	 */
	function addUserTop($uid) {
	    global $DB;
        return !!$DB->insert( 'paid_places', array('uid' => $uid, 'type_place' => $this->type_place) );
	}
    function addUserTopNew($ad) {
	    global $DB;
        $insert = array();
        $insert['uid'] = $ad['uid'];
        $insert['type_place'] = $ad['type_place'];
        if ($ad['ad_header']) {
            $insert['ad_header'] = $ad['ad_header'];
        }
        if ($ad['ad_text']) {
            $insert['ad_text'] = $ad['ad_text'];
        }
        if ($ad['ad_img_file_name']) {
            $insert['ad_img_file_name'] = $ad['ad_img_file_name'];
        }

        $DB->setCheckAutoSlashes(false);
        
        $nId = $DB->insert( 'paid_places', $insert, 'id' );
        
        if ( $nId /*&& (!empty($ad['ad_header']) || !empty($ad['ad_text']) || !empty($ad['ad_img_file_name']))*/ ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );

            $stop_words    = new stop_words( true );
            $nStopWordsCnt = $stop_words->calculate( $ad['ad_header'], $ad['ad_text'] );

            $DB->insert( 'moderation', array('rec_id' => $nId, 'rec_type' => user_content::MODER_CAROUSEL, 'stop_words_cnt' => $nStopWordsCnt) );
        }

        $DB->setCheckAutoSlashes(true);
        
        return !empty( $nId );
	}
	
	/**
	 * ����� ����� ����� ����� ������� ������� ������������ ������ ���
	 * ���������� �������� � �������
	 * @return integer
	 */
	function getTimeShow() {
        $count = 1;
        global $DB;
		$sql = "SELECT COUNT(*) FROM paid_places_queue WHERE type_place = ?i";
		$res = $DB->val( $sql, $this->type_place );
        if( !$DB->error )
            $count = $res;
        return ceil(($this->time_show / 60) * ($count - 1));
	}
	
	/**
	 * ������� ������ ��� ������
	 * 
	 * @return array ������ �������
	 */
	function getUserPlace() {
        self::getDoneShow();
        if(self::isDone()) {
            if($uid = self::getLastQueueUser())
                self::addUserTop($uid);
		}
		
		global $DB;
        $sql = "SELECT paid_places.uid FROM paid_places 
            INNER JOIN freelancer ON (freelancer.uid = paid_places.uid AND freelancer.is_banned::int = 0)
            WHERE paid_places.type_place = ?i ORDER BY paid_places.date_create DESC LIMIT 12 OFFSET 0";
        $res = $DB->rows( $sql, $this->type_place );
        
        if ( !$DB->error && count($res) ) {
            foreach ( $res as $row )
                $result[] = $row['uid'];
        }
        return $result;
	}    
    /**
     * ���� ��� � getUserPlace()
     * �� ���������� �� ������ ID ������������� �� � ��������� ���������� (���������, �����, �����������)
     * @global type $DB
     * @return null
     */
    function getUserPlaceNew() {
        self::getDoneShow();
        if(self::isDone()) {
            $ad = self::getLastQueueUserNew();
            if ($ad && is_array($ad)) {
                self::addUserTopNew($ad);
            }
		}
		
		global $DB;
        $sql = "SELECT paid_places.uid, paid_places.ad_header, paid_places.ad_text, paid_places.ad_img_file_name, freelancer.photo FROM paid_places 
            INNER JOIN freelancer ON (freelancer.uid = paid_places.uid AND freelancer.is_banned::int = 0)
            WHERE paid_places.type_place = ?i ORDER BY paid_places.date_create DESC LIMIT 12 OFFSET 0";
        $res = $DB->rows( $sql, $this->type_place );
        
        if ( !$DB->error && count($res) ) {
            return $res;
        }
        return null;
	}
	
	/**
	 * ����� ���������� ����� �� ������� ��������� ��� � ���� ��� �������, � ������� ��� �� �������
	 * 
	 * @return boolean true - ���� ��� ������ ������, ����� false
	 */
	function getLastQueueUser() {
	    global $DB;
        $sql = "DELETE FROM paid_places_queue WHERE type_place = ?i AND id = (SELECT id FROM paid_places_queue ORDER by date_create ASC LIMIT 1) RETURNING uid";
        if( $res = $DB->val( $sql, $this->type_place ) )
             return $res;
        return 0;
	}
    function getLastQueueUserNew() {
	    global $DB;
        $sql = "DELETE FROM paid_places_queue WHERE type_place = ?i AND id = (SELECT id FROM paid_places_queue WHERE type_place = ?i ORDER by date_create ASC LIMIT 1) RETURNING *";
        if ($res = $DB->row($sql, $this->type_place, $this->type_place)) {
            return $res;
        }
        return 0;
	}
    
	/**
	 * ����� ������ ��� ������� (�������� "��� ����� ���")
	 *
	 * @param integer $count ���������� �������
	 * @return array ������ �������������
	 */
	function getUserHistory($count = false) {
		if(!$count) $count = $this->history_count;
		
		global $DB;
        $sql = "SELECT uid FROM paid_places WHERE type_place = ?i ORDER BY date_create DESC LIMIT ?i OFFSET 0";
        $res = $DB->rows( $sql, $this->type_place, $count );
        
        if ( !$DB->error && count($res) ) {
            foreach ( $res as $row )
                $result[] = $row['uid'];
        }
        return $result;
	}
    
    /**
     * ���������� ������ � ��������� ���������� ����������
     * ����� ���� � � ������� � � ��� ������������ �����������
     * @param integer $uid ID ����������
     * @return array ������ � ������� �� ���������� ��� null ���� ���������� �� ���� ��� ��������� ������
     */
    function getLastUserAdvert ($uid) {
        global $DB;
        if (!$uid) {
            return null;
        }
        $sql = "
            SELECT ad_header, ad_text, ad_img_file_name, date_create
            FROM paid_places
            WHERE uid = ?i
            
            UNION
            SELECT ad_header, ad_text, ad_img_file_name, date_create
            FROM paid_places_queue
            WHERE uid = ?i

            ORDER BY date_create DESC
            LIMIT 1";
        $res = $DB->row($sql, $uid, $uid);
        if (!is_array($res) || $DB->error) {
            return null;
        }
        return $res;
    }
	
	/**
	 * �������� ������ ������� ��� ��������
	 * 
	 * @return boolean
	 */
    function deleteUserPlace() {
        global $DB;
        $sql   = "DELETE FROM paid_places WHERE type_place = ?i AND is_show = 1 AND date_show <= now() - interval ?";
        return !!$DB->query( $sql, $this->type_place, $this->time_delete );
    }
	
	/**
	 * ���������� ����� ������
	 *
	 * @param mixed $uids ������ � �� ������������� ������� ����������
	 * @return boolean
	 */
	function updateShow($tShow) {
	    global $DB;
        $sql   = "UPDATE paid_places SET is_show = 1, date_show = now() WHERE type_place = ?i AND date_create <= now() AND is_show = 0";
        return !!$DB->query( $sql, $this->type_place );
	}
	
	/**
	 * ���������� ������, ������� ��� ���� ������ ����� ��� ���
	 *
	 * @return boolean
	 */
	function getDoneShow() {
	    global $DB;
        $sql   = "UPDATE paid_places SET is_done = 1 WHERE type_place = ?i AND date_create <= now() - interval '{$this->time_delete} seconds' AND is_done <> 1";
        return !!$DB->query( $sql, $this->type_place );
	}
	
	/**
	 * ���������� ���������� ������������� 
	 *
	 * @return integer ���������� �������������
	 */
	function getCountUsers() {
	    global $DB;
		$sql = "SELECT COUNT(*) FROM paid_places WHERE type_place = ?i";
		
        if ( $res = $DB->val($sql, $this->type_place) )
            return $res;
        return 0;
	}
	
	/**
	 * ����� ������ ���������� ��� ������
	 * 
	 * @param array $uids �� ������������� 	(������ �������� [3,4,5,...])
	 * @return array ���������� ��� ������
	 */
	function getAllInfo($uids = false) {
        if(!$uids) return false;
        global $DB;
        $sql = "SELECT professions.name as title, freelancer.status_text as descr, freelancer.uid, users_change.id AS on_moder, us.is_pro as is_pro 
            FROM freelancer 
            LEFT JOIN professions ON professions.id = freelancer.spec_orig 
            LEFT JOIN users_change ON users_change.user_id = freelancer.uid AND users_change.ucolumn = 'status_text'
            
            LEFT JOIN users us
                ON freelancer.uid = us.uid
            
            WHERE freelancer.is_banned::int <> 1 AND freelancer.uid IN(".implode(", ", array_values($uids)).")";
        $res = $DB->rows( $sql );
        
        if ( !$DB->error && count($res) ) {
            foreach ( $res as $row )
                $result[$row['uid']] = $row;
        }
        return $result;
	}
	
	/**
	 * �������� ���� �� ����� � �� ������ ������� �������
	 *
	 * @return boolean true - ���� ����, ����� false
	 */
    function isDone() {
        global $DB;
        $sql = "SELECT 1 FROM paid_places WHERE type_place = ?i AND is_done = 0 LIMIT 1";
        if( $res = $DB->query($sql, $this->type_place) )
            return !pg_num_rows($res);
        return false;
	}
	
	/**
	 * ������� ������ ������
	 * 
	 * @return boolean
	 */
	function clearOldData() {
		$notDel = self::getUserHistory($this->history_count*2);
        
		global $DB;
		$sql = 
		"DELETE FROM paid_places WHERE type_place = {$this->type_place} AND uid NOT IN(" . implode(',', $notDel) . ");
		 DELETE FROM paid_places p USING freelancer f WHERE p.type_place = {$this->type_place} AND p.uid = f.uid AND f.is_banned = '1'";
        
		return !!$DB->squery( $sql );
	}
	
	/**
	 * ������ ������� "����� ������� ������� ��������"
	 *
	 * @param integer $bill_id �� ��������
	 * @param integer $gift_id ���������� �� �������
	 * @param integer $tr_id   �� ����������
	 * @param integer $gid     �� ����������
	 * @param integer $fid     �� ��������
	 * @param string  $comments �����������
	 * @param integer $tarif   �� ������ (op_codes)
	 * @return boolean
	 */
	function gift(&$bill_id, &$gift_id, $tr_id, $gid, $fid, $comments, $tarif = 69) {
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
		$account = new account();
                $cm = (int)$tarif == 83 ? "����� ������� �������� � �������" : "����� ������� ������� �������� � �������";
		$error = $account->Gift($bill_id, $gift_id, $tr_id, $tarif, $fid, $gid, $cm, $comments);
		
		if($error!==0) return 0;
			
		return $this->addUser($gid);
	}
	
	/**
	 * ���������� � ������� ��������� ��������
	 * 
	 * @param array $data - ���������� �� ��������
	 * @return array ����������
	 */
	function getSuccessInfo($data) {
	    $obj = NULL;
	    if($data['op_code'] == 69 || $data['op_code'] == 83) {
    		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/present.php");
    		$obj = new present();
	    } else {
     		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
     		$obj = new account;
        }
  		return $obj->getSuccessInfo($data);
	}
	
	/**
     * �������� ������ �� id � account_operations
     * @see account::DelByOpid()
     *
     * @param  intr $uid uid ������������
     * @param  int $opid id �������� � ��������
     * @return int 0
     */
 	function DelByOpid($uid, $opid) {
   		return 0;
 	}

    /**
	 * ���������� � ������ � HTML �� id � account_operations: �����, ��� ������������, ��� ���������, ����� ��������.
	 * @param   integer   $bill_id   id �������� � account_operations
	 * @param   integer   $uid       uid ������������
	 * @return  string               ������ � ������ � ���� HTML
	 */
	function GetOrderInfo($bill_id, $uid){
	    global $DB;
		$sql = "SELECT uname, usurname, login, ammount, op_code FROM (SELECT ammount,op_code, CASE WHEN ammount < 0 THEN to_uid ELSE from_uid END as acc
				FROM account_operations, present
				WHERE account_operations.id=? AND (billing_to_id = account_operations.id AND to_uid = ? OR
				billing_from_id = account_operations.id AND from_uid = ?)) as a LEFT JOIN users ON a.acc = uid";
		
		extract( $DB->row($sql, $bill_id, $uid, $uid) );
		
                $direction = (int)$ammount == 0 ? '��' : '���';
                $place = (int)$op_code == 83 ? '��������' : '������� ��������';
                $out = "����� ������� $place $direction <a href=\"/users/".$login."\" class=\"blue\">".$uname." ".$usurname." [".$login."]</a>";
		return $out;
        }
        
    /**
     * ���������� ���������� (paid_places) ��� ������������� 
     * 
     * @return array
     */
    function getModeration() {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        
        $aReturn = array();
        $sQuery  = 'SELECT p.id, p.ad_header, p.ad_text, p.ad_img_file_name, p.type_place, '. user_content::MODER_CAROUSEL .' AS content_id, 
                u.uid, u.login, u.uname, u.is_pro, u.is_pro_test, u.is_team, u.usurname, u.is_chuck, u.warn, u.is_banned, u.ban_where, 
                pr.name as title, u.status_text as descr, u.photo  
            FROM moderation b 
            INNER JOIN paid_places p ON p.id = b.rec_id 
            INNER JOIN freelancer u ON u.uid = p.uid 
            LEFT JOIN professions pr ON pr.id = u.spec_orig 
            WHERE b.rec_type = '. user_content::MODER_CAROUSEL .' 
            ORDER BY b.stop_words_cnt DESC, b.rec_id ASC ';
        
        return $GLOBALS['DB']->rows( $sQuery );
    }
    
    /**
     * ���������� ���������� ���������� (paid_places) ��� ������������� 
     * 
     * @return int
     */
    function getModerationCount() {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        
        $sQuery  = 'SELECT COUNT(rec_id) AS cnt FROM moderation WHERE rec_type = '. user_content::MODER_CAROUSEL;
        
        return intval( $GLOBALS['DB']->val($sQuery) );
    }
    
    /**
     * �������� ���������� ����������� �����������
     * 
     * @param  int|array $fpage_id id ����� ���������� paid_places
     * @param  int|array $fake ��� ������������� � users_first_page
     * @param  int $moder_id uid ����������
     * @return bool true - �����, false - ������
     */
    function setModeration( $id = 0, $fake = 0, $moder_id = 0 ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        
        $bRet = false;
        $id   = is_array($id) ? $id : array($id);
         
        if ( $id && $moder_id ) {
            $GLOBALS['DB']->query( 'UPDATE paid_places SET moderator_status = ?i WHERE id IN (?l)', $moder_id, $id );

            if ( !$GLOBALS['DB']->error ) {
                $GLOBALS['DB']->query('DELETE FROM moderation WHERE rec_type = ?i AND rec_id IN (?l)', user_content::MODER_CAROUSEL, $id );
                $bRet = true;
            }
        }
        
        return $bRet;
    }
    
    /**
     * ��������� ��������� �� ���������� �� �������������
     * 
     * @param  int $fpage_id id ����� ���������� paid_places
     * @return bool true - ���������, false - ���
     */
    function checkModeration( $id = 0) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        
        $sId = $GLOBALS['DB']->val('SELECT rec_id FROM moderation WHERE rec_type = ?i AND rec_id = ?i', user_content::MODER_CAROUSEL, $id );
        
        return !empty( $sId );
    }
}
?>
