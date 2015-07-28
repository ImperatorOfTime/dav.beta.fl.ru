<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/op_codes.php');

/**
 * ����� ��������� ������� ���� �� ������� ��������, � ����� ������� ���� � ��������.
 *
 */
class firstpage
{

	/**
     * 
	 * �������� ��������� ��������:
	 * - id ������, ��� id �� users_first_page, �.�. ������ ����� ���������� ������ �� ������� �������� ����� ��� ��� ���������
	 * - id ����� ����������, � ���� ��� ����������� ��� id ��������� ��� �������� ����������. �� ���� id �� ������� professions
	 * - id ��������, ��� id �� upf_descriptions
     * 
     * @todo: � ������ ����� �������� id ����� ����������?
	 */
	
    /**
     * ������ ����������
     */
    const OP_CODE_MAIN                  = 10;
    const OP_CODE_MAIN_DISCOUNT         = 167;
    
    const OP_CODE_CATEGORY              = 19;
    const OP_CODE_CATEGORY_DISCOUNT     = 168;
    
    const OP_CODE_SUBCATEGORY           = 20;
    const OP_CODE_SUBCATEGORY_DISCOUNT  = 169;
    
    
    /**
     * ������ ��������
     */
    const OP_CODE_MAIN_UP              = 145;
    const OP_CODE_MAIN_UP_DISCOUNT     = 170;
    
    const OP_CODE_CATEGORY_UP          = 154;
    const OP_CODE_CATEGORY_UP_DISCOUNT = 171;
    
    const OP_CODE_SUBCATEGORY_UP            = 146;
    const OP_CODE_SUBCATEGORY_UP_DISCOUNT   = 172;
    
    
    const OP_CODE_BUFFER = 147;
    
    
    /**
     * ������� �������� ��� �������������
     * 
     * @var type 
     */
    static protected $_bills = array();


    
    /**
     * �������� ������ �������� ��� ���������� ������������
     * 
     * @param type $uid
     * @return type
     */
    static protected function getBilling($uid)
    {
        if (!isset(self::$_bills[$uid])) {
            self::$_bills[$uid] = new billing($uid);
        }
        
        return self::$_bills[$uid];
    }



    /**
     * �������� ��������� �������� ����������
     * ���� ������� ID ������������ �� ������� �������� ������
     * 
     * @param type $prof_group_id
     * @param type $prof_id
     * @param type $uid
     * @return type
     */
    static function getPlacementUpPrice($prof_group_id, $prof_id, $uid = 0)
    {
        $opCode = $prof_group_id ? 
                self::OP_CODE_CATEGORY_UP : 
                ($prof_id > 0 ? self::OP_CODE_SUBCATEGORY_UP : self::OP_CODE_MAIN_UP);
        
        //������� �������� ������ ��� ����������� ������������
        if($uid > 0) {
            $bill = self::getBilling($uid);
            $opCode = $bill->getDiscountOpCode($opCode);
        }

        $data = op_codes::getDataByOpCode($opCode);
        return @$data['sum'];
    }

    
    
    /**
     * �������� ��������� ����������
     * ���� ������� ID ������������ �� ������� �������� ������
     * 
     * @param type $prof_group_id
     * @param type $prof_id
     * @param type $uid
     * @return type
     */
    static function getPlacementPrice($prof_group_id, $prof_id, $uid = 0)
    {
        $opCode = $prof_group_id ? 
                self::OP_CODE_CATEGORY : 
                ($prof_id==-1 ? self::OP_CODE_MAIN : self::OP_CODE_SUBCATEGORY);
        
        //������� �������� ������ ��� ����������� ������������
        if($uid > 0) {
            $bill = self::getBilling($uid);
            $opCode = $bill->getDiscountOpCode($opCode);
        }

        $data = op_codes::getDataByOpCode($opCode);
        return @$data['sum'];
    }










    /**
	 * ��������� �������� � ������ �� ����������. ������ �������.
     * @see firstpage::UpdateFullDescription
     * 
	 * @param   string   $text       ��������
	 * @param   integer  $ordid      id ������
	 * @param   integer  $user_id    uid ������������
	 * @param   boolean  $admin      ��������� ������ �����?
	 * @return  string               ��������� �� ������ ��� ������ ������, ���� ��� ���������
	 */
	function adddescr($text, $ordid, $user_id, $admin = 0){
        global $DB;
		$sql = "SELECT user_id, from_date FROM users_first_page WHERE id=?i";
        $q = $DB->row($sql, $ordid);
		$id = $q['user_id'];
        $date = $q['from_date'];
		
		if (!$admin && $id != $user_id) return 0;
		else $sql = "UPDATE users_first_page SET descr = ? WHERE user_id = ?i AND from_date >= ?";
		$DB->query($sql, $text, $id, $date);
		$ret = $DB->error;
		$memBuff = new memBuff();
		$ret = $memBuff->flushGroup("firstpg");
		return $ret;
	}
	
	
	/**
	 * ���������� ������� �������� ������. ������ �������.
     * @see firstpage::GetFullDescription
	 *
	 * @param   integer  $ordid   id ������
	 * @return  array             ������ ������ �� ������� users_first_page
	 */
	function GetDescr($ordid){
        global $DB;
		$sql = "SELECT descr FROM users_first_page WHERE id = ?i";
		return $DB->row($sql, $ordid);
	}


  /**
   * �������������� ���������� ���������� �������� ����������
   * @param   integer   $ufp_description_id    id ��������
   * @return                                   ���������� ����������
   */
  function IncWatchCount($ufp_description_id)
  {
    global $DB;
    $sql = "UPDATE ufp_description SET watch_count = watch_count + 1 WHERE id = ?i RETURNING watch_count";
    return $DB->val($sql, $ufp_description_id);
  }

  
  /**
   * ������������� ���������� ��������� �� ������� ������������ � �������� ����������
   * @param   integer   $user_id   uid ������������
   * @param   integer   $prof_id   id ����� ����������
   * @return  boolean              ����� ��������
   */
  function IncJump2UserCount($user_id, $prof_id)
  {
    global $DB;
    $sql = "UPDATE ufp_description d
               SET jumptouser_count = d.jumptouser_count + 1
              FROM users_first_page ufp
             WHERE ufp.user_id = ?i
               AND ufp.profession = ?i
               AND ufp.payed = true
               AND ufp.from_date <= now() AND ufp.from_date + ufp.to_date >= now()
               AND d.id = ufp.ufp_description_id
             RETURNING ufp.id";

    $ufp_id = $DB->val($sql, $user_id, $prof_id);
    if($ufp_id) {
      $mc = new memBuff();
      $mckey = "firstpage.mg_rightContent.{$ufp_id}.";
      $mc->delete($mckey.'1'); // ������� �� ���� ������ ��������� ������� ������, �.�. ��� ����������� ����������.
      return 1;
    }

    return NULL;
  }


  /**
   * ������������� ���������� ��������� �� ������ � ��������� ������������ � �������� ����������
   * @param   integer   $portf_id  id ���������
   * @param   integer   $user_id   uid ������������
   * @param   integer   $prof_id   id ���������� � �������� (�� ������� professions)
   * @return  boolean              ����� ��������
   */
  function IncJump2WorkCount($portf_id, $user_id, $prof_id)
  {
    global $DB;
    $sql = "UPDATE ufp_portfolio p
               SET jumptowork_count = jumptowork_count + 1
              FROM users_first_page ufp
             WHERE ufp.user_id = ?i
               AND ufp.profession = ?i
               AND ufp.payed = true
               AND ufp.from_date <= now() AND ufp.from_date + ufp.to_date >= now()
               AND p.portfolio_id = ?i
               AND p.ufp_description_id = ufp.ufp_description_id
            RETURNING ufp.id";
    $ufp_id = $DB->val($sql, $user_id, $prof_id, $portf_id);
    if($ufp_id) {
      $mc = new memBuff();
      $mckey = "firstpage.mg_rightContent.{$ufp_id}.";
      $mc->delete($mckey.'1'); // ������� �� ���� ������ ��������� ������� ������, �.�. ��� ����������� ����������.
      return 1;
    }

    return NULL;
  }


  /**
   * ��������� ��� ����������� ������ ���������� ������������
   * @param   integer    $ufp_id       id ����� ����������
   * @param   string     $title        ���������
   * @param   string     $descr        ������� ��������
   * @param   string     $full_descr   �������������� �������� (���������)
   * @param   array      $sels         ������ � �������. � ������ ���� �������� ������ �����, �� ������� �������� �� ���������.
   *                                   � ������ ���������� �������� � ������� ������ ����������� �����.
   * @return  boolean                  ����� ��������
   */
  function UpdateFullDescription($ufp_id, $title, $descr, $full_descr, $sels)
  {
    global $DB;
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
    $sql = "SELECT ufp_description_id, user_id FROM users_first_page WHERE id = ?i";

    if(!($res = $DB->rows($sql, $ufp_id)))
      return 0;

    if(!$sels)
      $sels=array();

    $user_id = $res[0]['user_id'];
    $users = new users();
    $user = $users->GetName($user_id, $e);
    $login = $user['login'];
    $trans = 'COMMIT';
    $sModer = '';

    if(!$title) $title = null;
    if(!$descr) $descr = null;
    if(!$full_descr) $full_descr = null;
    
    if ( $user_id == $_SESSION['uid'] && !hasPermissions('users') 
        && ( $title || $descr || $full_descr || $sels ) 
    ) {
        // �����, �� �����, �� ��� - ��������� �� �������������
        $sModer = ' , moderator_status = 0 ';
    }

    if($d_id=$res[0]['ufp_description_id'])
      $sql =
      "UPDATE ufp_description
          SET title = ? ,
              descr = ? ,
              full_descr = ?
              $sModer
        WHERE id = ?i";
      $sql2 = "DELETE FROM ufp_portfolio WHERE ufp_description_id = ?i RETURNING portfolio_id, jumptowork_count, pict";
      if ( $d_id && $DB->query($sql, $title, $descr, $full_descr, $d_id) ) {
        $res = $DB->rows($sql2, $d_id);
        if(!$sels)
          $sels=array();
        $DB->start();
        
        // !!! �������� ������ � ������� ���� �������.
        
//        if() {
          $dels = array();
          $olds = array();
          if ( $res ) {
            foreach($res as $k=>$v) {
                if($v['portfolio_id'])
                  $olds[$v['portfolio_id']] = $v;
                else
                  $dels[$v['pict']] = $v['pict'];
            }
          }

          $i=0;

          $sql = "INSERT INTO ufp_portfolio (portfolio_id, pict, ufp_description_id, n_order, jumptowork_count) VALUES ";
          foreach($sels as $key) { // !!! is_numeric ���� �������� �� ���������� �������� ������ �����.
            $sql .= ($i++?',':'').
                    "(".(is_numeric($key) ? "{$key}, NULL" : "NULL, '{$key}'" ).
                    ", {$d_id}, {$i}, ".
                    (is_numeric($key) && $olds[$key] ? (int)$olds[$key]['jumptowork_count'] : 0).")";
            unset($dels[$key]);
          }

          if($i && !$DB->squery($sql))
            $trans = "ROLLBACK";
          if($trans != "ROLLBACK") {
            $dir = "users/".substr($login,0,2)."/".$login."/upload/";
            $cfile = new CFile(); 
            foreach($dels as $f) {
              if ($f){ 
                  $cfile->Delete(0, $dir, $f);
                  $cfile->Delete(0, $dir, "sm_".$f);
              }
            }
          }
          
            if($trans=="ROLLBACK") {
                $DB->rollback();
            } else {
                $DB->commit();
            }



      
      
    } else { 
      
      $sql = "INSERT INTO ufp_description (title, descr, full_descr) VALUES(?, ?, ?) RETURNING (id)";
      $d_id = $DB->val($sql, $title, $descr, $full_descr);
      if($d_id) {
        $sql = 
        "UPDATE users_first_page ufp
            SET ufp_description_id = ?i
            FROM users_first_page ux
          WHERE ufp.from_date >= ux.from_date
            AND ufp.user_id = ux.user_id
            AND ufp.profession = ux.profession
            AND ux.id = ?i";

        if ( $DB->query($sql, $d_id, $ufp_id) ) {
            if ( $sels ) {
                $i=0;

                $sql = "INSERT INTO ufp_portfolio (portfolio_id, pict, ufp_description_id, n_order) VALUES ";
                foreach($sels as $key) // !!! is_numeric ���� �������� �� ���������� �������� ������ �����.
                    $sql .= ($i++?',':'').
                    "(".(is_numeric($key) ? "{$key}, NULL" : "NULL, '{$key}'" ).
                    ", {$d_id}, {$i})";
                
                if($i)
                    $DB->squery($sql);
            }
        }
        else {
            $trans = "ROLLBACK";
        }
      }
      else{
          $trans = "ROLLBACK";
      }
    }
    
    if ( $d_id && $trans != "ROLLBACK" && (!hasPermissions('users') || !$res[0]['ufp_description_id']) 
        && ( $title || $descr || $full_descr || $sels )
    ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );

        $stop_words    = new stop_words( true );
        $nStopWordsCnt = $stop_words->calculate( $title, $descr, $full_descr );
        
        $DB->insert( 'moderation', array('rec_id' => $ufp_id, 'rec_type' => user_content::MODER_FIRST_PAGE, 'stop_words_cnt' => $nStopWordsCnt) );
    }
    elseif ( $d_id && $trans != "ROLLBACK" && (!hasPermissions('users') || !$res[0]['ufp_description_id']) 
        && !$title && !$descr && !$full_descr && !$sels  
    ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        
        $aId = $DB->col( 'SELECT id FROM users_first_page WHERE ufp_description_id = ?i', $d_id );
        $DB->query( 'DELETE FROM moderation WHERE rec_id IN (?l) AND rec_type = ?i', $aId, user_content::MODER_FIRST_PAGE );
    }
    
    $memBuff = new memBuff();
    $memBuff->flushGroup("firstpg");
    $_SESSION['clr_ufp'][$ufp_id] = true;

    return 1;
  }

  
  /**
   * ���������� ������ ������ � ������
   * @param   integer   $upf_id    id ������
   * @result  array                ������ � ������� ������ �� users_first_page + ufp_descriptions.
   *                               � �������� portfolio �������� ������ ������ �� ufp_portfolio
   */
  function GetFullDescription($ufp_id)
  {
    global $DB;
    $sql =
    "SELECT ufp.id as ufp_id, d.first_post as ufp_first_post, ufp.user_id as user_id,
            d.id, d.title, ufp.profession, 
            COALESCE(NULLIF(d.descr,''), ufp.descr) as descr,
            d.full_descr, d.jumptouser_count, d.watch_count 
       FROM users_first_page ufp
     LEFT JOIN
       ufp_description d
         ON d.id = ufp.ufp_description_id
     WHERE ufp.id = ?i";

    $d = $DB->row($sql, $ufp_id);
    if($d) {

      if($d['id']) {
        $sql =
        "SELECT up.jumptowork_count,
                up.n_order as upf_n_order,
                up.ufp_description_id,
                COALESCE(up.portfolio_id::varchar(16), up.pict) as key, 
                p.id,
                COALESCE(p.pict, up.pict) as pict,
                COALESCE(p.prev_pict, 'sm_' || up.pict) as prev_pict,
                COALESCE(p.name, '') as name,
                prev_type
          FROM ufp_portfolio up
         LEFT JOIN
           portfolio p
             ON p.id = up.portfolio_id
          WHERE up.ufp_description_id = ?i
          ORDER BY up.n_order";

        $ret = $DB->rows($sql, $d['id']);
        if($ret) $d['portfolio'] = $ret;
      }

      return $d;
    }

    return NULL;
  }


  /**
   * ���������� id �������� �� id ������
   * @param   integer   $ufp_id   id ������
   * @return  integer             id ��������
   */
  function GetFullDescrID($ufp_id)
  {
    global $DB;
    $sql = "SELECT ufp_description_id FROM users_first_page WHERE id = ?";

    return $DB->val($sql, $ufp_id);
  }

	
	/**
	 * ������ �������������, ����������� ������ � ������� ������, ��������������� � ������� �� ������ �� �������
	 * @param   interger   $page    id ����� ����������
	 * @return  array               ������ � ������� � �������������
	 */
	function GetAll($page = -1, $profs = array(), $is_group = false){
        $prof_case = 'ufp.profession = ' . intval($page);
        if ($page == -1 && is_array($profs) && count($profs) > 0) {
            $prof_ids = array();
            foreach ($profs as $prof) {
                $prof_ids[] = $prof['id'];
            }
            $prof_string = implode(',', $prof_ids);
            $prof_case = 'ufp.profession IN (' . $prof_string . ')';
        } elseif ($is_group) {
            $prof_case .= ' AND ufp.tarif = 19';            
        }
        
        $sql_base = " SELECT u.login, u.uname, u.usurname, u.photo, u.role, COALESCE(NULLIF(ufpd.descr,''), ufp.descr) as descr, ufpd.title,
           ufp.user_id, ufp.id, u.is_profi, u.is_pro as payed, u.is_team, u.is_pro_test as payed_test, ufp.psum, ufp.profession, u.boss_rate,
            ( COALESCE(ufpd.full_descr,'')<>''
             OR (ufpd.id IS NOT NULL
                 AND EXISTS(SELECT 1 FROM ufp_portfolio WHERE ufp_description_id = ufpd.id)) ) as has_full_descr
      FROM users_first_page ufp
    INNER JOIN
      users u
        ON u.uid = ufp.user_id
    LEFT JOIN 
      ufp_description ufpd
        ON ufpd.id = ufp.ufp_description_id
     WHERE ufp.payed = true
       AND ufp.from_date <= now() AND ufp.from_date + ufp.to_date >= now()
       AND $prof_case
       AND u.is_banned='0'";
		$sql = '(' . $sql_base . " AND ufp.skip_psum = TRUE ORDER BY ufp.first_post DESC)";
        $sql .= ' UNION ALL ('. $sql_base. " AND ufp.skip_psum IS NULL ORDER BY ufp.psum DESC, ufp.first_post DESC)";

        $memBuff = new memBuff();
		$ret = $memBuff->getSql($error, $sql, 600, false, "firstpg");
		return $ret;
	}

	
	/**
	 * ������ UID �������������, ����������� ������ � ������� ������, ��������������� � ������� �� ������ �� �������
	 * @param   interger   $prof_id   id ����� ����������
	 * @return  array                 ������ �� ������� uid �������������
	 */
    function GetAllUids(
     $prof_id = -1)
    {
      global $DB;
      $sql = 
      "SELECT user_id
         FROM users_first_page as ufp
       INNER JOIN
         freelancer f
           ON f.uid = ufp.user_id
          AND f.is_banned='0'
        WHERE ufp.payed = true
          AND ufp.from_date <= now() AND ufp.from_date + ufp.to_date >= now()
          AND ufp.profession = ?i
        ORDER BY ufp.psum DESC, ufp.first_post";

      $ret = $DB->rows($sql, $prof_id);
      if(!$ret) $ret = NULL;
      return $ret;
    }

	
	/**
	 * ���������� �������������, ����������� ������ � ������� ������
	 * @param   interger   $prof_id   id ����� ����������
	 * @return  integer               ������ �� ������� uid �������������
	 */
    function GetCount(
     $prof_id = -1)
    {
      global $DB;
      $sql = 
      "SELECT COUNT(ufp.user_id)
         FROM users_first_page as ufp
       INNER JOIN
         freelancer f
           ON f.uid = ufp.user_id
          AND f.is_banned='0'
        WHERE ufp.payed = true
          AND ufp.from_date <= now() AND ufp.from_date + ufp.to_date >= now()
          AND ufp.profession = ?i";

      $ret = $DB->val($sql, $prof_id);
      if(!$ret) $ret = NULL;
      return $ret;
    }


    /**
	 * ������� ������� ���� ����� � ���������� ������� (��������).
	 * @param   integer   $user_id   uid ������������
	 * @param   mixed     $profs     ������� ������, ������ (����������� ��������) ��� ���� ������������� ����� ����������.
	 *                               ���� ��� ����, �� ������ ������� �� ���� ��������, ��������� ������.
	 * @return  array                ������, ���� �������� - id ����� ����������, �������� - �������
	 */
	function GetPositions(
     $user_id,
     $profs = -1)
    {
      global $DB;
      $pcond='';

      if(is_array($profs))
        $profs = implode(",", $profs);

      if($profs)
        $pcond = "AND ufp.profession IN ({$profs})";

      $sql = "
        SELECT ufp.profession as prof_id,
               COUNT(uft.user_id) + 1 as pos
        
          FROM users_first_page as ufp

        LEFT JOIN
          users_first_page as uft
            ON uft.payed = true
           AND uft.from_date <= now()
           AND uft.from_date + uft.to_date >= now()
           AND uft.profession = ufp.profession
           AND uft.user_id <> ufp.user_id
           AND (uft.psum > ufp.psum
                OR (uft.psum = ufp.psum AND uft.first_post < ufp.first_post))

         WHERE ufp.payed = true
           AND ufp.from_date <= now()
           AND ufp.from_date + ufp.to_date >= now()
           AND ufp.user_id = ?i
           {$pcond}

         GROUP BY ufp.profession
      ";

      $ret = NULL;

        $res = $DB->rows($sql, $user_id);
        if($res) {
            foreach($res as $k=>$v) {
                $ret[$v['prof_id']] = $v['pos'];
            }
        }

      return $ret;
    }


    /**
	 * ���������� �������� ���� � ������� ������
	 * @param    integer  $user_id   uid ������������
	 * @return   array               ������ � ����������� �������� � ���������� ������
	 */
	function getBufferSumInfo($user_id)
    {
        global $DB;
        $sql = 
        "SELECT CASE ufc.profession WHEN 0 THEN '����� �������' WHEN -1 THEN '������� ��������' ELSE p.name END as prof_name,
                CASE WHEN ufc.from_date + ufc.to_date >= now() THEN ufc.first_post ELSE NULL END     as current_start,
                CASE WHEN ufc.from_date + ufc.to_date >= now() THEN ufc.psum ELSE NULL END     as current_buffer,
                CASE WHEN ufc.from_date + ufc.to_date < now()  THEN ufc.from_date + ufc.to_date ELSE ufx.from_date + ufx.to_date END as prev_end,
                CASE WHEN ufc.from_date + ufc.to_date < now()  THEN ufc.psum ELSE ufx.psum END as prev_buffer
           FROM 
           (
             SELECT MAX(id) as id, profession
               FROM users_first_page
              WHERE user_id = ?i
                AND profession IS NOT NULL
              GROUP BY profession
           ) as lst
         INNER JOIN
           users_first_page ufc
             ON ufc.id = lst.id
         LEFT JOIN
           users_first_page ufx
             ON ufx.id = (SELECT id
                            FROM users_first_page
                           WHERE user_id = ufc.user_id
                             AND profession = ufc.profession
                             AND from_date + to_date < ufc.first_post
                           ORDER BY from_date + to_date DESC LIMIT 1)
         LEFT JOIN
           professions p
             ON p.id = ufc.profession
          ORDER BY ufc.profession";
        $ret = $DB->rows($sql, $user_id);
        if(!$ret) $ret = NULL;
        return $ret;
    }


    /**
	 * ���������� ��� �������. ���������� ���� ������������� � ������� �����������,
	 * � ���������� � ������������ �� ������� ����� ���� � �������� �����. �� �������,
	 * �� ���� ��������� ����.
	 * @return   array   ����������
	 */
	function GetAll4Stat()
    {
      global $DB;
      $sql = "
      SELECT ufp.id,
             u.login, u.uname, u.usurname, u.photo, u.is_pro,
             CASE WHEN ufp.profession=0 THEN '����� �������' ELSE p.name END as prof_name,
             s.by_today,
             s.by_7d
        FROM users_first_page ufp
      LEFT JOIN
        ufp_description ufpd
          ON ufpd.id = ufp.ufp_description_id
      LEFT JOIN
        (
          SELECT ufp_description_id,
                 SUM((day = now()::date)::int * jumptouser_count) as by_today,
                 SUM(jumptouser_count) as by_7d
            FROM ufp_stat
           WHERE day > now()::date - 7
           GROUP BY ufp_description_id
        ) as s
          ON s.ufp_description_id = ufpd.id
      LEFT JOIN
        users u
          ON u.uid = ufp.user_id
      LEFT JOIN
        professions p
          ON p.id = ufp.profession
       WHERE ufp.payed = true
         AND ufp.from_date <= now()
         AND ufp.from_date + ufp.to_date >= now()
       ORDER BY ufp.profession, ufp.psum DESC, ufp.first_post";

      $ret = $DB->rows($sql);
      if(!$ret) $ret = NULL;
      return $ret;
    }

	
    /**
	 * ��������� id ��������.
	 * ������ ���� � ������� ����������, ����, ���� �� �������, � ����������, ����������� � ��� �� ������� ���� �������������.
	 * ������������ ��� ����, ����� ������������ ����������, ����������� ������ � ���������� ����������, ����� �� ��������
	 * ��� �������� ����� ����������.
	 * @param    integer   $id   id ����� ����������
	 * @param    integer $moderator_status �����������. ������ ������������� ����������: 
     *           0 - ��������� � �������� (����� ���� �����������������), UID ���������� - �����������, NULL - ��� ������� �� ����������� (��� ������)
     *           ����� ����� ������ � ��� ������, ���� ���������� �������
	 * @return   integer         ������ �� id � ������� ufp_descriptions ��� NULL, ���� ����� �� ����������.
	 */
	function GetLastUfpID( $id, &$moderator_status = null )
    {
      global $DB;
      if(!$id)
        return NULL;

      $sql = 
      "SELECT COALESCE(ufp.ufp_description_id, ufx.ufp_description_id)
         FROM users_first_page ufp
       LEFT JOIN
         users_first_page ufx
           ON ufx.user_id = ufp.user_id
          AND ufx.profession = ufp.profession
          AND ufx.ufp_description_id IS NOT NULL
          AND ufx.from_date = ( SELECT from_date
                                  FROM users_first_page
                                 WHERE user_id = ufp.user_id
                                   AND profession = ufp.profession
                                   AND ufp_description_id IS NOT NULL
                                   AND from_date < ufp.from_date
                                 ORDER BY from_date DESC
                                 LIMIT 1   )
        WHERE ufp.id = ?i
      ";
      
      $description_id = $DB->val($sql, $id);
      
      if ( $description_id ) {
          $moderator_status = $DB->val( 'SELECT moderator_status FROM ufp_description WHERE id = ?i', $description_id );
      }

      return $description_id;
    }

    
	/**
	 * ���������� ������ � ����� ���������� � ������� ������. ��� ������ �������� ������� - ����� ����������
	 * -2  -  ���������� �����������
	 * -1  -  ������� ��������
	 *  0  -  ��� ���������
	 * @return   array   ������ �������� ������� - id ����� ����������, �������� �������� - ���� ����������.
	 */
	function GetPrice()
    {
      global $DB;
      $sql = "SELECT CASE id WHEN 10 THEN '-1' WHEN 19 THEN '0' WHEN 33 THEN '-2' ELSE 'n' END as where, sum
                FROM op_codes
               WHERE id IN (10,19,20,33)";
      $ret = NULL;
        $res = $DB->rows($sql);
        if($res) {
            foreach($res as $k=>$v) {
                $ret[$v['where']]=round($v['sum'],2);
            }
        }
      return $ret;
    }

	
	/**
	 * ��������� ������������ � ������� ������ ��������, �� ������� ��������, � ������ "��� ����������" �/��� �� ������������ ����������� �����������. 
	 * ���� ������������ ��� ���������� � ������ �����, �� ��� ������ �����������������.
	 * ���� ���������� ��� ���������, �� ����������� ����� ����������, ���� ������ �������� ����� ���������� ��������, �.�. ���������� ������������.
	 * @param   integer  $user              uid ������������
	 * @param   integer  $transaction_id    id ���������� � ��������
	 * @param   array    $pages             ������, ������� ��������� �������� ��������� ����� ����������, � �������� ��������� - ���������� ������.
	 * @param   integer                     id ������ ������
	 */
	function SetOrdered($user, $transaction_id, $pages, $tarif = null, $promo = 0, &$error)
    {
        if (is_array($tarif)) {
            $buy_tarif = current($tarif);
            $tarif = key($tarif);
        } else {
            $buy_tarif = $tarif;
        }
        
        global $DB;
        if ($pages) {
            foreach ($pages as $prof => $interv) {
                if ($tarif == null) {
                    if ($prof === 0)
                        $tarif = 19;
                    else if ($prof === -1)
                        $tarif = 10;
                    else if ($prof === -2)
                        $tarif = 33;
                    else
                        $tarif = 20;
                }
                if ($tarif == 20) {
                    $this->addProfToUserIfNeed($user, $prof);
                }
                
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
                $account = new account();

                $sql = "SELECT from_date+to_date FROM users_first_page 
                WHERE user_id = ?i AND from_date+to_date >= now() AND payed=true AND ordered = true AND profession=?i AND tarif = ?i
                ORDER BY from_date+to_date DESC LIMIT 1;";
                $last_date = $DB->val($sql, $user, $prof, $tarif);

                $prof_name = '����� �������';
                if ($tarif == 19) { //������ ��������
                    $profession = professions::getProfGroupTitle($prof);
                    $prof_name = '������� ' . $profession;
                } elseif ($prof > 0) {
                    $profession = professions::getProfTitle($prof);
                    $prof_name = '������� ' . $profession['name'];
                }
                $descr = ($last_date ? '��������� ����������' : '����������') . ' � ' . $prof_name . ' �������� ����������� � �����';


                $date = new DateTime($last_date);
                $date->modify('+' . ($interv * 7) . ' day');
                $comment = '�� ' . $date->format('d.m.Y H:i');

                $error = $account->Buy($bill_id, $transaction_id, $buy_tarif, $user, $descr, $comment, $interv, 0, $promo);
                if ($error !== 0)
                    return 0;
                $sql = "INSERT INTO users_first_page (user_id, to_date, tarif, ordered, payed, billing_id, profession, skip_psum) 
                VALUES (?, ?, ?, 'true', 'true', ?, ?, 'true')
                RETURNING id";

                $id = $DB->val($sql, $user, $interv . ' weeks', $tarif, $bill_id, $prof);

                $sql = "UPDATE users_first_page SET skip_psum = TRUE, first_post = NOW() WHERE id IN (SELECT id FROM users_first_page 
                    WHERE user_id = ? AND profession=? )";
                $DB->query($sql, $user, $prof);

                self::bindDescription($id);
            }
        }
        //if ($bill_id) $account->commit_transaction($transaction_id, $user, $bill_id);
        $memBuff = new memBuff();
        $ret = $memBuff->flushGroup("firstpg");

        return $id;
    }

    /**
     * ���� � ������������ ��� ��������� �������������, �� �������� ��
     * ��������������� � �������� ��������
     * 
     * @param type $user_id �� �����������
     * @param type $prof_id �� �������������
     */
    private function addProfToUserIfNeed($user_id, $prof_id) 
    {
        $add_key = self::needAddProf($user_id, $prof_id);
        if ($add_key == 1) { //��������� ���.�������������
            professions::UpdateProfsAddSpec($user_id, 0, $prof_id, 0);
        } elseif ($add_key == 2) { //������������� �������� �������������
            
            $frl = new freelancer;
            $frl->spec = $prof_id;
            $frl->spec_orig = $prof_id;
            
            professions::setLastModifiedSpec($user_id, $prof_id);
            $frl->Update($user_id, $res);
        }
    }
    
    /**
     * ����������, ����� �� ��������� ������������� � �������
     * @param type $uid �� ������������
     * @param type $prof_id �� �������������
     * @return int 0 ���� �� �����, 1 ���� ���. �������������, 2 ���� �������� ����-�
     */
    public static function needAddProf($uid, $prof_id) {
        $user_profs = professions::GetProfessionsByUser($uid, true, true);
        $selected_profs_count = count(professions::GetProfessionsByUser($uid, false));
        $has_free_spec_slot = $selected_profs_count < (1 + (is_pro(true, $uid) ? PROF_SPEC_ADD : 0));
        
        if (!in_array($prof_id, $user_profs) && $has_free_spec_slot) {
            $user = new freelancer();
            $user->GetUserByUID($uid);
            return $user->spec == 0 ? 2 : 1;
        }
        return 0;
    }
    
    /**
     * ��������� ���������� � �������� �����
     * 
     * @param  int $nId ID �������� ����� �� users_first_page
     * @param  string $sError �����������. ���������� ��������� �� ������.
     *         ���� ������� ������� false, �� ������ ��� - ������ ������ ��� ����������.
     * @return bool true - �����, false - ������
     */
    function bindDescription( $nId = 0, &$sError = '' ) {
        $bRet = false;
        
        if ( $nId ) {
            $nModeratorStatus = null;
            $nDescriptionId   = self::GetLastUfpID( $nId, $nModeratorStatus );

            if ( $nDescriptionId ) {
                $GLOBALS['DB']->query( 
                    'UPDATE users_first_page SET ufp_description_id = ?i WHERE id = ?i', $nDescriptionId, $nId 
                );
                
                $bRet   = true;
                $sError = $GLOBALS['DB']->error;
                
                if ( empty($sError) && !$nModeratorStatus ) {
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                    
                    $GLOBALS['DB']->query( 
                        'DELETE FROM moderation WHERE rec_type = ?i AND rec_id IN (SELECT id FROM users_first_page WHERE ufp_description_id = ?i)', 
                        user_content::MODER_FIRST_PAGE, $nDescriptionId 
                    );
                    
                    $aDescr = firstpage::GetFullDescription( $nId );

                    if ( !empty($aDescr['title']) || !empty($aDescr['descr']) || !empty($aDescr['full_descr']) || !empty($aDescr['portfolio']) ) {
                        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
                        
                        $stop_words    = new stop_words( true );
                        $nStopWordsCnt = $stop_words->calculate( $aDescr['title'], $aDescr['descr'], $aDescr['full_descr'] );

                        $GLOBALS['DB']->insert( 'moderation', array('rec_id' => $nId, 'rec_type' => user_content::MODER_FIRST_PAGE, 'stop_words_cnt' => $nStopWordsCnt) );
                    }
                }
            }
        }
        
        return $bRet;
    }
	
  /**
   * ���������� �� ������� �������� � �������� �������
   * @param    integer   $bill_id          ���������� id �������
   * @param    integer   $gift_id          ���������� id �������
   * @param    integer   $gid              uid ����������
   * @param    integer   $fid              uid ��������
   * @param    integer   $transaction_id   id ���������� � ��������
   * @param    string    $inv              ���� �������� ����������
   * @param    integer   $tarif            id ������
   * @param    string    $comments         ����������� ��������
   * @param    integer   $ammount          ��������� �����! ������ ���� ������ �������
   * @return   integer                     id ������ ������
   */
  function Gift(&$bill_id, &$gift_id, $gid, $fid, $transaction_id, $inv, $tarif, $comments, $ammount=1){
        global $DB; 
		$tarif = 17;
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
		$account = new account();
    $error = $account->Gift($bill_id, $gift_id, $transaction_id, $tarif, $fid, $gid, "������ �������� � �������", $comments, $ammount);
		if ($error!==0) return 0;
    $sql = "INSERT INTO users_first_page (user_id, to_date, tarif, ordered, payed, billing_id, profession) VALUES (?, ?, ?, 'true', 'true', ?, -1)
            RETURNING id";

		$res = $DB->row($sql, $gid, $inv, $tarif, $bill_id);
        
        $id = $res['id'];
        
        self::bindDescription( $id );
        
    	$memBuff = new memBuff();
		$ret = $memBuff->flushGroup("firstpg");
		return $id;
	}

  
  /**
   * ���������� �� ������� �������� � �������� ������� ��� ���������� ����� (������ �������� ��� WebMoney)
   * @param    integer   $bill_id          ���������� id �������
   * @param    integer   $gift_id          ���������� id �������
   * @param    integer   $gid              uid ����������
   * @param    integer   $fid              uid ��������
   * @param    integer   $transaction_id   id ���������� � ��������
   * @param    string    $inv              ���� �������� ����������
   * @param    integer   $tarif            id ������ �� ������� op_code
   * @param    string    $comments         ����������� ��������
   * @return   integer                     id ������ ������
   */	
	function GiftOrdered(&$bill_id, &$gift_id, $gid, $fid, $transaction_id, $inv, $tarif, $descr, $comments){
        global $DB; 
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
		$account = new account();
		$error = $account->Gift($bill_id, $gift_id, $transaction_id, $tarif, $fid, $gid, $descr, $comments);
		if ($error!==0) return 0;
    	$sql = "INSERT INTO users_first_page (user_id, to_date, tarif, ordered, payed, billing_id, profession) VALUES (?, ?, ?, 'true', 'true', ?, -1)
            RETURNING id";
		
		$id = $DB->val($sql, $gid, $inv, $tarif, $bill_id);
        
        self::bindDescription( $id );
        
    	$memBuff = new memBuff();
		$ret = $memBuff->flushGroup("firstpg");
		return $id;
	}

          /**
   * ���������� �� ������� �������� �������� � �������� �������
   * @param    integer   $bill_id          ���������� id �������
   * @param    integer   $gift_id          ���������� id �������
   * @param    integer   $gid              uid ����������
   * @param    integer   $fid              uid ��������
   * @param    integer   $transaction_id   id ���������� � ��������
   * @param    integer   $prof             id ��������, ��� 0 ���� �� ���� ��������
   * @param    string    $ammount          ���� ���������� � �������
   * @param    integer   $tarif            id ������ �� ������� op_code
   * @param    string    $comments         ����������� ��������
   * @return   integer                     id ������ ������
   */
	function GiftOrderedCat(&$bill_id, &$gift_id, $gid, $fid, $transaction_id, $prof, $ammount, $tarif, $comments) {
        if (!$ammount) return 0;
        
        global $DB;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
        $inv = $ammount * 7;
        $inv = $inv . ' days';

        $account = new account();
        $error = $account->Gift($bill_id, $gift_id, $transaction_id, $tarif, $fid, $gid, "���������� �� �������� �������� � �������", $comments, $ammount);
        if ($error !== 0) return 0;
        $sql = "INSERT INTO users_first_page (user_id, to_date, tarif, ordered, payed, billing_id, profession) VALUES (?, ?, ?, 'true', 'true', ?, ?)
            RETURNING id";

        $res = $DB->row($sql, $gid, $inv, $tarif, $bill_id, $prof);
        $id = $res['id'];
        
        self::bindDescription( $id );
        
        $memBuff = new memBuff();
        $ret = $memBuff->flushGroup("firstpg");
        return $id;
    }

	/**
	 * ���������� ������ � ������� �������������� � ������� ����������, ���� � ���������� ������
	 * @param   integer  $page   ����� ��������. � ������ �� 100 �������.
	 * @param   integer  $id     ���� ������ id, �� ������������ ������ � ���������� ������. ���� 0, �� ������ �������
	 * @return  array            ������ � ������� �������
	 */
	function GetOrders($page = 1, $id = 0){
        global $DB; 
		if (!$id) {
			$sql = "SELECT r.id, login, uname, usurname, ammount, from_date, to_date+from_date as to_date, sum, op_name, tarif FROM 
		 		(SELECT * FROM users_first_page ORDER BY id DESC LIMIT 100 OFFSET ?i ) as r
				LEFT JOIN op_codes ON op_codes.id = r.tarif
				LEFT JOIN billing ON r.billing_id=billing.id
				LEFT JOIN users ON users.uid = r.user_id WHERE payed = 'false' AND ordered = true ORDER BY id";
            $ret = $DB->rows($sql, (100*($page-1)));
		} else {
			$sql = "SELECT r.id, login, uname, usurname, ammount, from_date, to_date+from_date as to_date, sum, op_name, tarif FROM 
		 		(SELECT * FROM users_first_page WHERE id = ?i ) as r
				LEFT JOIN op_codes ON op_codes.id = r.tarif
				LEFT JOIN billing ON r.billing_id=billing.id
				LEFT JOIN users ON users.uid = r.user_id";
            $ret = $DB->rows($sql, $id);
        }
		return $ret;
	}

	
	/**
	 * ������ ����� ����������� ����������
	 * �� ������������ � ����� � ��������� ���� �������� �������
     * @deprecated
	 *
	 * @param  integer  $id        id ����������
	 * @param  ammount  $ammount   ��������� �����! ������ ���� �������
	 * @param  string   $date      ���� ������ �������� ���������� (� ������� psql)
	 * @param  string   $to_date   ���� ��������� �������� ���������� (� ������� psql)
	 * @param  integer  $type      id ����������
	 * @return                     ������ ������ ��� ��������� �� ������
	 */
	function SetPayed($id, $ammount, $date, $to_date, $type){
        global $DB; 
		$sql = "UPDATE users_first_page SET tarif=?, payed=true, from_date=?, to_date=(timestamp ? -timestamp ? ) WHERE id=?i ; SELECT user_id, tarif FROM users_first_page WHERE id=?i ;";
		
        $q = $DB->row($sql,$type, $date, $to_date, $date, $id, $id);
		$uid = $q['user_id'];
        $tarif = $q['tarif'];
		$sql = "SELECT billing_id FROM users_first_page WHERE id=?i ;";

		$billing_id = $DB->val($sql, $id);
		if ($billing_id){
			$sql = "DELETE FROM billing WHERE id= ?i";
			$DB->query($sql, $billing_id);
		}
		$sql = "INSERT INTO billing (uid, ammount, op_code) VALUES (?,?,?) RETURNING id";
		$billing_id = $DB->val($sql, $uid, $ammount, $tarif);
		$sql = "UPDATE users_first_page SET billing_id=? WHERE id = ?";
        $DB->query($sql, $billing_id, $id);
		$memBuff = new memBuff();
		$ret = $memBuff->flushGroup("firstpg");
		return $error;
	}

	
	/**
	 * �������� ������ �� ����������
	 * @param   integer   $id   id ������
	 */
	function DeleteOrder($id){
        global $DB; 
		$sql = 'DELETE FROM users_first_page WHERE id=?i';
        
		$DB->val($sql, $id);
        
        if ( !$DB->error ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            $DB->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i', $id, user_content::MODER_FIRST_PAGE );
        }
        
		$memBuff = new memBuff();
		$ret = $memBuff->flushGroup("firstpg");
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
		$sql = "SELECT uname, usurname, login, ammount, op_code, acc, bill
                    FROM (SELECT ammount,op_code, 
                    CASE WHEN ammount < 0 THEN to_uid ELSE from_uid END as acc,
                    billing_to_id as bill
				FROM account_operations, present 
				WHERE account_operations.id=?i AND (billing_to_id = account_operations.id AND to_uid = ?i OR
				billing_from_id = account_operations.id AND from_uid = ?i)) as a LEFT JOIN users ON a.acc = uid";

                $row = $DB->row($sql, $bill_id, $uid, $uid);
//		list($uname, $usurname, $login, $ammount, $op_code) = $row; //��� ����-�� �� ��������
                $uname = $row['uname'];
                $usurname = $row['usurname'];
                $login = $row['login'];
                $ammount = $row['ammount'];

                $op_code = $row['op_code'];
                $acc = $row['acc'];
                $bill = $row['bill'];
                
                $direction = (int)$ammount == 0 ? '��' : '���';
		if (($op_code == 17 || $op_code == 18) && $ammount < 0) {
			$out = "������ �������� $direction <a href=\"/users/".$login."\" class=\"blue\">".$uname." ".$usurname." [".$login."]</a>";
		} elseif($op_code == 84 || $op_code == 85){ // ���������� � �������� � �������
                    $out = "���������� � �������� $direction <a href=\"/users/".$login."\" class=\"blue\">".$uname." ".$usurname." [".$login."]</a>";
                    $sql = "
      SELECT from_date, (from_date+to_date) as to_date, profession FROM users_first_page
			WHERE billing_id=?i AND user_id=?i";

			$row = $ammount < 0 ? $DB->row($sql, $bill, $acc) : $DB->row($sql, $bill_id, $uid);
			if ($row) {
                            include_once($_SERVER['DOCUMENT_ROOT'].'/classes/professions.php');
                            $prof = professions::GetProfNameWP($row['profession']);
 				$out .= " c ".date("d.m.Y | H:i",strtotimeEx($row['from_date']))." �� ".date("d.m.Y | H:i",strtotimeEx($row['to_date']));
                                $out .= $op_code == 85 ? ' � ������� &laquo;'.$prof.'&raquo;' : '';
                        }
                }else {
			$sql = "
      SELECT from_date, (from_date+to_date) as to_date, professions.id, name FROM users_first_page LEFT JOIN professions ON profession=professions.id
			WHERE billing_id=?i AND user_id=?i";

			$row = $DB->row($sql,$bill_id, $uid);
			if ($row) {
				$out = "� ".date("d.m.Y | H:i",strtotimeEx($row['from_date']))." �� ".date("d.m.Y | H:i",strtotimeEx($row['to_date']));
				if ($row['id'] > 0) $out .= " � ������� &laquo;".$row['name']."&raquo;";
			}
		}
		return $out;
	}
	
	
	/**
	 * �������� ������ �� id � account_operations
	 * @see account::DelByOpid()
	 *
	 * @param  integer   $uid    uid ������������
	 * @param  integer   $opid   id �������� � ��������
	 */
	function DelByOpid($uid, $opid){
        global $DB; 
		$sql = "DELETE FROM users_first_page WHERE billing_id=?i AND user_id=?i RETURNING id";
        
		$id = $DB->val($sql, $opid, $uid);
        
        if ( $id ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            $DB->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i', $id, user_content::MODER_FIRST_PAGE );
        }
        
		$memBuff = new memBuff();
		$ret = $memBuff->flushGroup("firstpg");
		return 0;
	}
	
	/**
	 * ������ ������� ������������
	 * @param  integer   $uid   uid ������������
	 * @return array            ������ � ������� ������. ������ - ����� ����������.
	 */
	function GetCurOrdersByUid($uid){
        global $DB; 
		$sql = "SELECT name, profession,  from_date, to_date,  first_post, psum
		 FROM 
		(SELECT MAX(from_date) as from_date, MAX(first_post) as first_post, MAX(from_date+to_date) as to_date, MAX(psum) as psum, profession FROM users_first_page 
		WHERE user_id = ?i AND from_date+to_date >= now() AND payed=true
		AND ordered = true GROUP BY profession) as fpu
		LEFT JOIN professions ON fpu.profession=professions.id ORDER BY profession";
		$ret = $DB->rows($sql, $uid);
		if ($ret)
		foreach($ret as $ikey=>$row){
			$out[$row['profession']] = $row;
		}
		return $out;
	}
	
	/**
	 * C����� �������� �������
	 * @param    integer   $page_id    id ����� ����������
	 * @return   array                 ������ � ������� �������. ������ - uid ������������
	 */
	function GetOrdersByPage($page_id, $uid = null){
        global $DB; 
		$sql = "
		  SELECT f.login, f.uname, f.usurname, f.photo, f.uid, f.is_pro as payed, f.is_pro_test as payed_test,
		         ufp.psum, EXTRACT(EPOCH FROM ufp.first_post) as first_post
		    FROM users_first_page ufp
		  INNER JOIN
		    freelancer f
		      ON f.uid = ufp.user_id
             AND f.is_banned='0'
		   WHERE ufp.profession = ?
             " . ($uid !== null ? "AND ufp.user_id = ?" : "" ) . "
		     AND ufp.payed = true
             AND ufp.from_date<=now() AND ufp.from_date + ufp.to_date >= now()
           ORDER BY ufp.psum DESC, first_post
        ";
		$ret = $DB->rows($sql, $page_id, $uid);
		if ($ret)
		foreach($ret as $ikey=>$row){
			$out[$row['uid']] = $row;
		}
		return $out;
	}
	
	/**
	 * ��������� ������� ������������
	 * @param   integer   $user              uid ������������
	 * @param   integer   $transaction_id    id ����������
	 * @param   array     $bids              ������, � ������� ������ ��������� - id ����� ����������, �������� - ����� ��� �������� �������
	 * @return  boolean                      ����� ��������
	 */
	function BidPlaces($user, $transaction_id, $bids){
        global $DB; 
		if ($bids)	foreach($bids as $prof => $sum){
			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
			$account = new account();
			$error = $account->Buy($bill_id, $transaction_id, 21, $user, "", "", $sum, 0);
			if ($error!==0) return 0;
			$sql = "UPDATE users_first_page SET psum=psum+?i WHERE id IN (SELECT id FROM users_first_page 
				WHERE user_id = ?i AND from_date+to_date >= now() AND payed=true
				AND ordered = true AND profession=? )";
			$res = $DB->query($sql, $sun, $user, $prof);
			$id = 1;
		}
		if ($bill_id) $account->commit_transaction($transaction_id, $user, $bill_id);
		$memBuff = new memBuff();
		$ret = $memBuff->flushGroup("firstpg");
		return $id;
	}

	
	/**
	 * ���������� �� �������� �� ��������� ����� � �������� �������������
	 * @return   array   ������� u - ������ �� �������� �������������, p - ������ �� ��������.
	 *                   ������ �������� ������ � ����������:count - ����������, phrase - ����������� ������� � ������ �����
	 */
	function ShowStats()
	{
        require_once (ABS_PATH . '/classes/project_exrates.php');
        
		$sql = "SELECT count(uid) as cnt FROM users WHERE active = true";
		$memBuff = new memBuff();
	  	$tmp = $memBuff->getSql($error, $sql, 600);
		$users = $tmp[0]['cnt'];

		$sql = "SELECT count(id) as cnt FROM projects WHERE post_date >= '".(date("Y-m-d", time() - (3600*24*31)))."'";
		$tmp = $memBuff->getSql($error, $sql, 600);
		$projects = $tmp[0]['cnt'];
		$projects = $tmp[0]['cnt'];
        
        $project_exRates = project_exrates::GetAll();
        $costProjectWithoutCost = 21000;
        $sql = "SELECT 
                    count(t.id) as cnt, SUM(t.cost_rub) as sum 
                    FROM (SELECT 
                            CASE WHEN currency = 0 THEN ( CASE WHEN cost = 0 THEN {$costProjectWithoutCost} ELSE cost * {$project_exRates[24]} END )
                            WHEN currency  = 1 THEN ( CASE WHEN cost=0 THEN {$costProjectWithoutCost} ELSE cost * {$project_exRates[34]} END )
                            WHEN currency = 3 THEN ( CASE WHEN cost=0 THEN {$costProjectWithoutCost} ELSE cost * {$project_exRates[14]} END )
                            ELSE ( CASE WHEN cost=0 THEN {$costProjectWithoutCost} ELSE cost END ) END as cost_rub, id
                          FROM projects WHERE post_date >= NOW() - interval '1 month'
                    ) as t";
        $tmp = $memBuff->getSql($error, $sql, 600);                   
        if($tmp[0]['cnt'] > 0) $projects_budget = round($tmp[0]['sum'] / $tmp[0]['cnt'], 0);
        else $projects_budget = 0;
 
        $users_str = ending($users, '������������', '������������', '�������������');
        $projects_str = ending($projects, '������', '�������', '��������');

		if ($projects >= 10000) $projects = number_format($projects, 0, '', ' ');
		if ($users >= 10000) $users = number_format($users, 0, '', ' ');
		if ($projects_budget >= 10000) $projects_budget = number_format($projects_budget, 0, '', ' ');
		
        //$str = "<span>".$projects."</span> ".$projects_str." � �����, <span>".$users."</span> ".$users_str."";

        return array('u'=>array('count'=>$users, 'phrase'=>$users_str),
                     'p'=>array('count'=>$projects, 'phrase'=>$projects_str.' � �����'),
                     's'=>array('count'=>$projects_budget, 'phrase' => '������� ������ ��������'));
	}
	

	/**
	 * ���������� �������� �������
	 * @param    integer   $user             uid ������������
	 * @param    integer   $transaction_id   id ���������� � ��������
	 * @param    integer   $page             id ����� ����������
	 * @param    string    $time             ���� �������� ����������
	 * @param    integer   $tarif            id ������ ���������� �������� �������
	 * @param    string    $comments         ����������� ��������
	 * @return   integer   $id               id ������������ ���������� ��� 0, � ������ ������
	 */
	function AdminAddFP($user, $transaction_id, $page, $time, $tarif = 64, $comments="������� ����������. ���������� �������� �������"){
        global $DB; 
    	require_once(ABS_PATH . "/classes/account.php");
    	$account = new account();
    	$error = $account->Buy($bill_id, $transaction_id, $tarif, $user, $comments, $comments);
    	if ($error!==0) return 0;
        $sql = "INSERT INTO users_first_page (user_id, to_date, tarif, ordered, payed, billing_id, profession) VALUES (?, ?, ?, 'true', 'true', ?, ?)
            RETURNING id";
    	
    	$id = $DB->val($sql, $user,$time,$tarif, $bill_id, $page);
        
        self::bindDescription( $id );
        
        $memBuff = new memBuff();
		$ret = $memBuff->flushGroup("firstpg");
		return $id;
	}
	
	/**
	 * ���������� � ������� ��������� ��������
	 * 
	 * @param array $data - ���������� �� ��������
	 * @return array ����������
	 */
	function getSuccessInfo($data) {
        global $DB; 
	    if(in_array($data['op_code'], array(17,84,85))) {
    		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/present.php");
    		$present = new present();
    		return $present->getSuccessInfo($data);
	    }
	    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
	    $uid  = get_uid(false);
	    $sql  = "SELECT o.*, u.login FROM users u, users_first_page o WHERE u.uid = ?i AND o.user_id = u.uid AND o.billing_id = ?i";
	    $asc  = $DB->row($sql, $uid, $data['id']); 
	    $profs[]   = $asc['profession'];
	    $prof_info = professions::GetProfessions($profs);
	    $poss      = firstpage::GetPositions($uid, $profs);
	    $is_up     = ($poss[$asc['profession']]>1);
	    $login_pp = "{$asc['login']}_pp";
	    $linktopage = "/freelancers/#$login_pp";	    
	    if ($prof_info[$profs[0]]["id"] == -1) {
	    	$linktopage = "/#$login_pp";
	    } elseif ($prof_info[$profs[0]]["id"]) {
	    	$linktopage = "/freelancers/{$prof_info[$profs[0]]["link"]}/#$login_pp";
	    }
	    $name = "������� ����� � �������� (<a class=\"b-layout__link b-layout__link_bold\" href=\"{$linktopage}\">{$prof_info[$asc['profession']]['name']}</a> � {$poss[$asc['profession']]}-� �����".($is_up?" <a class=\"b-layout__link b-layout__link_bold\" href=\"/firstpage/position.php?cur_prof={$asc['profession']}\">��������� ����</a>?)":")");
	    $data['ammount'] = abs($data['ammount']);
	    $suc = array("date"  => $data['op_date'],
	                 "name"  => $name,
	                 "descr" => "",
	                 "sum"   => "{$data['ammount']} ���."); 
	    return $suc;                  
	}
	
	
    /**
     * DEPRECATED
     * ������������� ������� ����
     *
     * @return 
     */
    function toAutoPayed() {
        global $DB; 
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
        $smail = new smail();
    
        $sql = "SELECT ap.*, a.sum, p.name as prof_name, u.uname, u.login, u.email, u.usurname, u.subscr
            FROM users_first_page  fp
            INNER JOIN users_first_page_autopay ap ON ap.user_id = fp.user_id AND ap.profession = fp.profession
            INNER JOIN professions p ON p.id = ap.profession
            INNER JOIN (
                SELECT user_id, profession, MAX(from_date + to_date) as maxdate FROM users_first_page
                WHERE payed = TRUE AND ordered = true
                GROUP BY user_id, profession
            ) mx ON mx.user_id = fp.user_id AND mx.profession = ap.profession
            INNER JOIN users u ON u.uid = fp.user_id AND u.is_banned = '0'
            INNER JOIN account a ON a.uid = fp.user_id
            WHERE fp.payed = TRUE AND fp.ordered = true
            AND mx.maxdate <= (fp.from_date+fp.to_date)  
                AND ((fp.from_date+fp.to_date) >= NOW() AND (fp.from_date+fp.to_date) < NOW() + interval '2 hours')
        ";
	    
        $ret = $DB->rows($sql);
        if(!$ret) return false;

        $price = self::getPrice();
        $result = array();
        foreach($ret as $k=>$user) {
            if ( $val['sum_cost'] > $val['sum'] ) {
                continue;
            }
            $cost = $price[$user['profession']]?$price[$user['profession']]:$price['n'];
            $p[$user['user_id']]['prof'][] = array('id' => $user['profession'], "cost"=>$cost, 'name' => $user['prof_name']);
            $p[$user['user_id']]['sum_cost'] += $cost; 
            $p[$user['user_id']]['sum']  = $user['sum'];
            $p[$user['user_id']]['user'] = $user;
        }

        foreach ( $p as $uid=>$val ) {
            if($val['sum_cost'] <= $val['sum']) {
                $result[$uid] = $val;
            }
        }
        
        foreach ( $result as $uid=>$val ){
            foreach ( $val['prof'] as $prof ) {
                $prof_id = $prof['id'];
                if ( intval($prof_id) || intval($prof_id) === 0 ) {
                    $fp_request[$uid][intval($prof_id)] = 1;
                }
            }
        }
        
        if($fp_request) {
            $account = new account();
            foreach($fp_request as $user_id=>$profs) {
                $tr_id    = $account->start_transaction($user_id, $tr_id);
                $order_id = self::SetOrdered($user_id, $tr_id, $profs);
            }
        }
		
        if($order_id) {
           // $smail->SuccessAutopayed($result);
           // messages::SuccessAutopayed($result);
        }
	   
    }
	
	/**
	 * ����� ������������� ������������
	 *
	 * @param integer $user_id    �� ������������
	 * @return array
	 */
    function getAutoPayed($user_id) {
        global $DB; 
        $sql = "SELECT * FROM users_first_page_autopay WHERE user_id = ?i";
        $ret = $DB->rows($sql, $user_id);
        if(!$ret) return null;
        foreach($ret as $val) $result[$val['profession']] = 1;
        
        return $result;
    }
    /**
     * �������� �������������
     *
     * @param integer $user_id     �� ������������
     * @param integer $profession  �� ������� ��� �������� ��������
     * @return string ��������� �� ������
     */
    function setAutoPayed($user_id, $profession) {
        global $DB; 
        if(!$user_id) return false;
        $sql = "INSERT INTO users_first_page_autopay (user_id, profession) VALUES(?i, ?i)";
        $DB->query($sql, $user_id, $profession);
        
        return $DB->error;
    }
    
    /**
     * ��������� ������������� ������������
     *
     * @param integer $user_id     �� ������������
     * @param integer $profession  �� ������� ��� �������� ���������
     * @return string ��������� �� ������
     */
    function delAutoPayed($user_id, $profession) {
        global $DB; 
        if(!$user_id) return false;
        $sql = "DELETE FROM users_first_page_autopay WHERE user_id = ?i AND profession = ?i";
        
        $DB->query($sql, $user_id, $profession);
        return $DB->error;  
    }
    
    /**
     * ���������� ������������� ������� ���������� ������� ����������� �� ����� � ��������� ����� 1 ���.
     *
     * @return array
     */
    function getReminderAutoPayed($days = 1, $test = false) {
        global $DB;

        $where = "";
        $inner_where = "";
        if($test) {
            $where = $DB->parse("WHERE u.login = ? AND ufl.to_date > now()", $test);
        } else {
            $inner_where = "AND  ufl.to_date >= now()::date + ?i AND  ufl.to_date < now()::date + ?i";
        }

        $sql = "SELECT ufa.*, a.sum, a.id as acc_id, p.name as prof_name, u.uname, u.login, substr(u.subscr::text,16,1) = '1' as bill_subscribe,
                u.email, u.usurname, u.subscr, u.is_banned
                FROM users_first_page_autopay ufa
              INNER JOIN
              (
                SELECT user_id, profession, MAX(from_date + to_date) as to_date
                  FROM users_first_page
                 WHERE payed = true
                   AND ordered = true
                 GROUP BY user_id, profession
              ) as ufl
                ON ufl.user_id = ufa.user_id
               AND ufl.profession = ufa.profession
               {$inner_where}
               INNER JOIN account a ON a.uid = ufa.user_id
               INNER JOIN professions p ON p.id = ufa.profession
               INNER JOIN users u ON u.uid = ufa.user_id
               {$where}
               ";
        
        $ret = $DB->rows($sql, $days, ($days+1));
        
        if(!$ret) return false;
        
        $price = self::getPrice();
        foreach($ret as $k=>$user) {
            $cost = $price[$user['profession']]?$price[$user['profession']]:$price['n'];
            
            $p[$user['user_id']]['prof'][] = array('id' => $user['profession'], "cost"=>$cost, 'name' => $user['prof_name']);
            $p[$user['user_id']]['sum_cost'] += $cost; 
            $p[$user['user_id']]['sum']  = $user['sum'];
            $p[$user['user_id']]['user'] = $user;
        }
        $result = $p;
        
//        foreach($p as $uid=>$val) {
//            if($val['sum_cost'] <= $val['sum']) $result[$uid] = $val;
//        }
        
        if(isset($result)) return $result; 
    }

    /**
     * ���� ����������� � ������� ������������� ���� ���������� ����������
     */
    public static function autoPayedReminder ($days = 1, $interval = 'days', $test = false) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/bar_notify.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        global $DB;

        $sql = "
                SELECT ufa.*, a.sum, a.id as acc_id, p.name as prof_name, u.uname, u.login, u.email, u.usurname, u.subscr, u.is_banned, substr(u.subscr::text,16,1) = '1' as bill_subscribe, ufl.to_date
                FROM users_first_page_autopay ufa
                INNER JOIN (
                    SELECT user_id, profession, MAX(from_date + to_date) as to_date
                    FROM users_first_page
                    WHERE payed = true
                        AND ordered = true
                    GROUP BY user_id, profession
                ) as ufl
                ON ufl.user_id = ufa.user_id
                AND ufl.profession = ufa.profession ";

        if (!$test && $interval == 'days') {
            $sql .= "
                AND  ufl.to_date >= now()::date + interval '$days days' AND  ufl.to_date < now()::date + interval '" . ($days + 1) . " days'";
        } elseif(!$test && $interval == 'hour') {
            $sql .= "
                AND  ufl.to_date >= now() + interval '$days hour' AND  ufl.to_date < now() + interval '" . ($days + 1) . " hour'";
        } else {
            $uid  = $DB->val("SELECT uid FROM users WHERE login = ?", $test);
            $sql .= $DB->parse(" AND  ufl.to_date >= now()::date AND ufl.user_id = ?", $uid);
        }
        $sql .= "
                INNER JOIN account a ON a.uid = ufa.user_id
                INNER JOIN professions p ON p.id = ufa.profession
                INNER JOIN users u ON u.uid = ufa.user_id AND u.is_banned = B'0'";

        $res = $DB->rows($sql);
        if (!$res) {
            return false;
        }

        $price = self::getPrice();
        $users = array();
        foreach($res as $user) {
            $cost = $price[$user['profession']] ? $price[$user['profession']] : $price['n'];

            $users[$user['user_id']]['profs'][] = array(
                'id'        => $user['profession'],
                'cost'      => $cost,
                'name'      => $user['prof_name'],
                'to_date'   => $user['to_date']);
            $users[$user['user_id']]['userData'] = $user;
            $users[$user['user_id']]['user'] = $user;
            $users[$user['user_id']]['sum_cost'] += $cost;
        }
        if (!$users) {
            return;
        }

        // ���� �������� �� 1 �� 2 ���� �� ���������, �� ��������� ������ �������
        if ($days == 1) {
            $mail = new smail();

            foreach($users as $uid => $user) {
                $bill = new billing($uid);
                $bill->cancelAllNewAndReserved();
                $barNotify = new bar_notify($uid);

                // ��������� ���� ��� ������ ���������
                foreach($user['profs'] as $userProf) {
                    $tarif = self::getBillingTarif($userProf['id']);
                    $options = array(
                        'prof_id' => $userProf['id'],
                        'week'    => 1, // �������� �� 1 ������
                    );

                    $bill->setOptions($options);
                    $create = $bill->create($tarif, true, false);
                    if (!$create) {
                        continue;
                    }

                    $queue[] = $create;
                }

                $billing = new billing($uid);
                if(!empty($queue)) {
                    //������������ ������ ������������� ��� ������
                    $billing->preparePayments($user['sum_cost'], false, $queue);
                    $complete = billing::autoPayed($billing, $user['sum_cost']);
                    $user['prof'] = $user['profs'];

                    if($complete) {
                        $barNotify->addNotify('bill', '', '������ ������� ��������.');
                    } else if($interval == 'days') { // ������ �������
                        $barNotify->addNotify('bill', '', '������ ��������, ������ �� ��������.');
                    } else { // ������ ������� �� �������
                        $barNotify->addNotify('bill', '', '������ ��������, ������������� ���������.');
                    }
                }
            }
        }

    }

    /**
     * ���������� ����� ��� ������� billing::setOptions
     */
    public static function getBillingTarif ($prof_id) {
        if ($prof_id == 0) {
            $tarif = 19;
        } else if ($prof_id == -1) {
            $tarif = 10;
        } else if ($prof_id == -2) {
            $tarif = 33;
        } else {
            $tarif = 20;
        }
        return $tarif;
    }
    
    /**
     * ���������� ��������, �� ������� ������������ ����� ���������� � ��� ���������� �� ������� ���� �������������
     * @param $uid - ������������� ������������
     * */
    public static function GetUserPlaces($uid) {
        $uid = (int)$uid;
        $cmd = "SELECT DISTINCT professions.id AS id, professions.name AS name, ap.user_id as auto
                        FROM users_first_page AS ufp
                        LEFT JOIN professions ON professions.id = ufp.profession
                        LEFT JOIN users_first_page_autopay as ap ON ap.user_id = ufp.user_id AND ap.profession = ufp.profession
                        WHERE ufp.user_id = ?i AND (from_date + to_date) > NOW()  ORDER BY id, name";
        global $DB;
        $rows = $DB->rows($cmd, $uid);
        return $rows;
    }
    
    /**
     * ���������� ���������� (ufp_description) ��� ������������� 
     * 
     * @return array
     */
    function getModeration() {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        
        $sQuery = 'SELECT f.id AS fpage_id, d.*, COALESCE(NULLIF(d.descr,\'\'), f.descr) as descr, '. user_content::MODER_FIRST_PAGE .' AS content_id, f.profession, 
                u.login, u.uname, u.is_pro, u.is_pro_test, u.is_team, u.usurname, u.is_chuck, u.warn, u.is_banned, u.ban_where 
            FROM moderation b 
            INNER JOIN users_first_page f ON f.id = b.rec_id 
            INNER JOIN ufp_description d ON f.ufp_description_id = d.id 
            INNER JOIN freelancer u ON u.uid = f.user_id 
            WHERE b.rec_type = '. user_content::MODER_FIRST_PAGE .' 
            ORDER BY b.stop_words_cnt DESC, b.rec_id ASC ';
        
        $aReturn = $GLOBALS['DB']->rows( $sQuery );
        
        if ( $aReturn ) {
            $ids = array();
            $tmp = array();
            
            for ( $i = 0; $i < count($aReturn); $i++ ) {
                $ids[]    = ($id = $aReturn[$i]['id']);
                $tmp[$id] = &$aReturn[$i];
            }
            
            $aAttach = $GLOBALS['DB']->rows( 'SELECT ufp_description_id, 
                    up.portfolio_id, 
                    COALESCE(p.prev_pict, up.pict) as pict 
                FROM ufp_portfolio up 
                LEFT JOIN portfolio p ON p.id = up.portfolio_id 
                WHERE up.ufp_description_id IN (?l)
                ORDER BY up.n_order', 
                $ids 
            );
            
            if ( $aAttach ) {
                foreach ( $aAttach as $row ) {
                    $tmp[$row['ufp_description_id']]['attach'][] = $row;
                }
            }
        }
        
        return $aReturn;
    }
    
    /**
     * ���������� ���������� ���������� (ufp_description) ��� ������������� 
     * 
     * @return int
     */
    function getModerationCount() {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        
        $sQuery  = 'SELECT COUNT(rec_id) AS cnt FROM moderation WHERE rec_type = '. user_content::MODER_FIRST_PAGE;
        
        return intval( $GLOBALS['DB']->val($sQuery) );
    }
    
    /**
     * ���������� ���������� ���� �� ����������� ������� ����
     * ufp_description � paid_places
     * 
     * @return int
     */
    function getPaidPlacesModerCounter() {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        
        $sQuery = 'SELECT COUNT(rec_id) AS cnt FROM moderation WHERE rec_type IN ('. user_content::MODER_FIRST_PAGE .', '. user_content::MODER_CAROUSEL .')';
        
        return intval( $GLOBALS['DB']->val($sQuery) );
    }
    
    /**
     * �������� ���������� ����������� �����������
     * 
     * @param  int|array $fpage_id id ����� ���������� users_first_page
     * @param  int|array $descr_id id ���������� ufp_description
     * @param  int $moder_id uid ����������
     * @return bool true - �����, false - ������
     */
    function setModeration( $fpage_id = 0, $descr_id = 0, $moder_id = 0 ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        
        $bRet     = false;
        $fpage_id = is_array($fpage_id) ? $fpage_id : array($fpage_id);
        $descr_id = is_array($descr_id) ? $descr_id : array($descr_id);
        
        if ( $fpage_id && $descr_id && $moder_id ) {
            $GLOBALS['DB']->query( 'UPDATE ufp_description SET moderator_status = ?i WHERE id IN (?l)', $moder_id, $descr_id );

            if ( !$GLOBALS['DB']->error ) {
                $GLOBALS['DB']->query('DELETE FROM moderation WHERE rec_type = ?i AND rec_id IN (?l)', user_content::MODER_FIRST_PAGE, $fpage_id );
                $bRet = true;
            }
        }
        
        return $bRet;
    }
    
    /**
     * ��������� ��������� �� ���������� �� �������������
     * 
     * @param  int $fpage_id id ����� ���������� users_first_page
     * @return bool true - ���������, false - ���
     */
    function checkModeration( $fpage_id = 0) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        
        $sId = $GLOBALS['DB']->val('SELECT rec_id FROM moderation WHERE rec_type = ?i AND rec_id = ?i', user_content::MODER_FIRST_PAGE, $fpage_id );
        
        return !empty( $sId );
    }
    
    public static function getPayedList() {
        $payed = array(
            array(
                'week'   => 1,
                'cost'   => array(4500, 750, 300),
                'opcode' => array(10,19,20)
            ),
            array(
                'week'   => 2,
                'cost'   => array(9000, 1500, 600),
                'opcode' => array(10,19,20)
            ),
            array(
                'week'   => 4,
                'cost'   => array(18000, 3000, 1200),
                'opcode' => array(10,19,20)
            ),
            array(
                'week'   => 12,
                'cost'   => array(54000, 9000, 3600),
                'opcode' => array(10,19,20)
            )
        );
        
        return $payed;
    }

    public static function getNameFirstpageLocation($prof_id, $prof_name = '') {
        switch($prof_id) {
            case -1:
                $name = "������� ���������� �� �������";
                break;
            case 0:
                $name = "������� ���������� � �������� �����������";
                break;
            default:
                $name = "������� ���������� � ������� {$prof_name}";
                break;
        }
        return $name;
    }
    
    /**
     * ���������� ������ � ����������� � ���������� ����������
     * @param type $uid
     * @param type $prof_id
     */
    public static function getPlaceInfo($uid, $prof_id, $is_group = false) {
        $group_case = $is_group ? 'AND tarif = 19' : '';
        $cmd = "SELECT * FROM users_first_page WHERE user_id = ?i AND profession = ?i {$group_case} ORDER BY id DESC";
        global $DB;
        $row = $DB->row($cmd, $uid, $prof_id);
        return $row;
    }
    
    /**
     * ���������� ��������� �������� �� ������ �����
     * @param type $prof_id
     * @return int
     */
    public static function getTopPrice($prof_id = -1) {
        return $prof_id == 0 ? 400 : ($prof_id == -1 ? 1000 : 200);
    }
    
    /**
     * ��������� ���� ����������, �������� �� ������ �����
     * @param type $uid
     * @param type $prof_id
     * @param type $from_buffer
     */
    public static function moveTop($uid, $prof_id, $is_group, $transaction_id, $from_buffer = false, $psum = 0, $op_code, $promo = 0)
    {
        $success = false;
        
        $prof_name = self::getOperationDescr($is_group?0:$prof_id, $is_group?$prof_id:0);

        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
        $account = new account();
        $ok = $account->Buy($id, $transaction_id, $op_code, $uid, 
            "����������� ������ ���������� � {$prof_name} �������� ����������� � �����",
            ($from_buffer ? '������ �� ������' : ''),
            1, 1, $promo
        );
            
        if ($ok === 0) {
            global $DB;
            $data = array(
                'first_post' => 'NOW()',
                'skip_psum' => true,
                'sent_up' => false
            );
            if ($from_buffer) $data['psum'] = $psum;
            $tarif_case = $is_group ? 'AND tarif = 19' : '';
            $success = $DB->update('users_first_page', $data, "user_id = ?i AND profession = ?i {$tarif_case}", $uid, $prof_id);
            
        }
        
        $memBuff = new memBuff();
        $memBuff->flushGroup("firstpg");
        
        unset($_SESSION['payed_frl']);
        
        return $success;
    }
    
    /**
     * ���������� ������ � ��������� �������� ��� ������� �����
     * @param type $prof_id
     */
    public static function getOperationDescr($prof_id, $prof_group_id)
    {
        $prof_name = '';
        if ($prof_id == -1) {
            $prof_name = '����� �������';
        } elseif($prof_id == 0) {
            $group_title = professions::GetProfGroupTitle($prof_group_id);
            $prof_name = '������� ' . $group_title;
        } else {
            $group_id = professions::GetGroupIdByProf($prof_id);
            $group_title = professions::GetProfGroupTitle($group_id);
            $prof_title .= professions::GetProfName($prof_id);
            $prof_name = '������� ' . $group_title . ' &mdash; ' . $prof_title;
        }
        return $prof_name;
    }
    
    public static function getUserDateStop($user_id, $prof, $prof_group = 0) {
        global $DB;
        $prof_use = $prof_group ?: $prof;
        $tarif_case = $prof_group ? 'AND tarif = 19' : '';
        $sql = 'SELECT from_date + to_date FROM users_first_page WHERE user_id = ?i AND profession = ?i '.$tarif_case.' ORDER BY from_date DESC LIMIT 1';
        $date_stop = $DB->val($sql, $user_id, $prof_use);
        return $date_stop;
    }
    
    /**
     * �������� �������������, � ������� � ������� ����� �������� ���� ����������
     * @global type $DB
     * @param type $param
     * @return boolean
     */
    public static function getExpiring()
    {
        global $DB;

         $sql = "SELECT ufl.profession as prof_id, ufl.tarif, (ufl.tarif != 19) as is_spec, 
            u.uid, u.login, u.email, u.uname, u.usurname, u.subscr, ufl.to_date as to_date
            FROM (
                SELECT user_id, profession, tarif, MAX(from_date + to_date) as to_date
                FROM users_first_page
                WHERE payed = true AND ordered = true  AND (sent_prolong IS NULL OR sent_prolong = FALSE)
                GROUP BY user_id, profession, tarif
            ) as ufl
            INNER JOIN users u ON u.uid = ufl.user_id
            WHERE ufl.to_date >= now() AND ufl.to_date + '-1 day' <= now() 
                AND u.is_banned = '0' AND substr(u.subscr::text,16,1) = '1'
        ";
        $ret = $DB->rows($sql);

        return $ret;
        
    }
    
    /**
     * ���������� ������, ������� ���������� �� 4 ����� � ����
     * @global type $DB
     * @return type
     */
    public static function getDowned()
    {
        global $DB;
        
        $sql = "SELECT ufl.profession as prof_id, ufl.tarif, (ufl.tarif != 19) as is_spec,
            u.uid, u.login, u.email, u.uname, u.usurname, u.subscr, ufl.from_date
            FROM (
                SELECT user_id, profession, tarif, MAX(first_post) as first_post, MAX(from_date) as from_date
                FROM users_first_page
                WHERE payed = true AND ordered = true AND tarif IN (10, 19, 20)
                AND (sent_up IS NULL OR sent_up = FALSE) AND from_date+to_date > now()
                GROUP BY user_id, profession, tarif
            ) as ufl
            INNER JOIN users u ON u.uid = ufl.user_id
            WHERE u.is_banned = '0' AND substr(u.subscr::text,16,1) = '1'
                AND (SELECT COUNT(DISTINCT(ufp2.user_id)) FROM users_first_page as ufp2 
                    WHERE ufl.user_id != ufp2.user_id AND ufl.first_post < ufp2.first_post 
                    AND ufl.profession = ufp2.profession
                ) >= 3
                ORDER BY ufl.first_post desc;";
        $ret = $DB->rows($sql);
        return $ret;
    }
    
    
    /**
     * �������� ���� ����� �������� ���������������� ����������� (� ��������� ��� ��������)
     * @global type $DB
     * @param string $type ��� ����������� prolong|up
     * @param type $uid �� �����
     * @param type $profession �� ���������
     * @param type $tarif ����� (��� ����������� ����������� �������)
     * @return boolean true ���� �������
     */
    public static function markSent($type, $uid, $profession, $tarif)
    {
        if (!in_array($type, array('prolong', 'up'))) {
            return false;
        }
        
        global $DB;
        
        return $DB->update(
            'users_first_page', 
            array('sent_'.$type => true), 
            'user_id = ?i AND profession = ?i AND tarif = ?i', 
            $uid, $profession, $tarif
        );        
    }
}




/**
 * ����� ��������� � �������� �������� �������� ����������
 * @param   array     $fd     ������ � ������� ����������
 * @param   integer   $mod    $mod & 1, ���� ������������� ����� ����������. $mod & 2, ���� ��� �����, 0 - ������� ����.
 * @return  string            HTML
 */
function __fpPrntTitleDescr($fd, $mod) // ����� ��������� � �������� �������� �������� ����������.
{
  global $session;
  ob_start();
?>
  <div style="padding:3px 0 2px 0">
    <? /* ?><a href="javascript:void(0)" style="color:#666666"><? */ ?>
      <span style="font-weight:bold"><?=reformat2($fd['title'], 16, 0, 1)?></span><br/>
      <?=reformat2($fd['descr'], 18, 0, 1)?>
    <? /* ?></a><? */ ?>
  </div>
  <? if($fd['has_full_descr']=='t') { ?>
    <div style="border-bottom:1px dotted #666666;padding-top:10px;width:75px"><a href="javascript:;" onclick="mg_onClick(<?=$fd['ufp_id']?>, <?=$mod?>)" style="color:#666666;text-decoration:none">���������&nbsp;-&nbsp;&gt;</a></div>
  <? } ?>
<?
  $str = ob_get_contents();
  ob_end_clean();
  return $str;
}


/**
 * ����� ������� �������� ����������
 * @param    array            ������ � ������� ����������
 * @return   string           HTML
 */
function __fpPrntPosition($value)   // ����� ������� �������� ����������.
{
  global $session;
  $ufp_mod = ( (int)($_SESSION['uid'] == $value['user_id']) )  |  ( 2 * hasPermissions('users') );
 
  if (strlen($value['uname']) > 10) {
	$uname = hyphen_words($value['uname']);
	$t = explode("�", $uname);
	for ($i=0; $i<count($t); $i++) {
		if (strlen($t[$i]) > 10) {
			$uname = LenghtFormatEx($value['uname'], 10);
			break;
		}
	}
  } else {
	$uname = $value['uname'];
  }

  if (strlen($value['usurname']) > 10) {
	$usurname = hyphen_words($value['usurname']);
	$t = explode("�", $usurname);
	for ($i=0; $i<count($t); $i++) {
		if (strlen($t[$i]) > 10) {
			$usurname = LenghtFormatEx($value['usurname'], 10);
			break;
		}
	}
  } else {
	$usurname = $value['usurname'];
  }

  ob_start();
?>
  <div id="mgCapsule<?=$value['id']?>">
    <div id="mgContent<?=$value['id']?>">
      <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:10px">
        <col style="width:10px" />
        <col />
        <tr valign="top">
          <td>
            <a href="/users/<?=$value['login']?>/?f=<?=stat_collector::REFID_PAIDSEATINGS?>&stamp=<?=$_SESSION['stamp']?>" title="<?=$value['uname']?> <?=$value['usurname']?>"
              <? /* if ($ufp_mod | (!$_SESSION['uid'] || is_emp()))  { ?> onmouseover="mg_onOver(<?=$value['id']?>, <?=$ufp_mod?>);" onmouseout="mg_onLeave()"<? } */ ?> class="img50x50">
              <?=view_avatar($value['login'], $value['photo'], 1, 0)?>
            </a>
          </td>
          <td style="padding-left:10px">
            <?
              if ($value['payed'] == 't')
                print(view_pro2(($value['payed_test'] == 't')?true:false).'&nbsp;');
              print($session->view_online_status($value['login']));
            ?>
            <a <? /* if ($ufp_mod | (!$_SESSION['uid'] || is_emp()))  { ?> onmouseover="mg_onOver(<?=$value['id']?>, <?=$ufp_mod?>);" onmouseout="mg_onLeave()"<? } */ ?>
               class="freelancer-name" href="/users/<?=$value['login']?>/?f=<?=stat_collector::REFID_PAIDSEATINGS?>&stamp=<?=$_SESSION['stamp']?>" title="<?=$value['uname']?> <?=$value['usurname']?>">
              <?=$uname." ".$usurname." [".$value['login']."]"?></a><?=($value['boss_rate']==1 ? view_vip() : '')?>
            <? 
              if ($ufp_mod) {
                ?><div id="fptext<?=$value['id']?>" style="overflow:hidden;"><?
              }

              $fd = $value;
              $fd['ufp_id']=$value['id'];
              print(__fpPrntTitleDescr($fd, $ufp_mod));

              if ($ufp_mod) {
            ?>
              </div>
              <a style="display:block; padding-top:10px; width:120px;" id="chtextB<?=$value['id']?>" href="javascript:void(0);" onclick="mg_onChangeClick(<?=$value['id']?>, <?=$ufp_mod?>);" class="blue">�������� ����������</a>
            <? 
              } 
              if ($ufp_mod & 1) {
                ?><a style="display:block; width:117px;" href="/firstpage/position.php" class="blue">�������� ���������</a><?
              }
            ?>
          </td>
        </tr>
      </table>
    </div>
  </div>
<?
  $str = ob_get_contents();
  ob_end_clean();
  return $str;
}
?>
