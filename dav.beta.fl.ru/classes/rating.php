<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
/**
 * ����� ��� ������ � ����������
 *
 */
class rating
{
  
  /**
   * ���� ���������� �� ��, ��� ��� ������� ���������� �������
   *
   */
  const A_KIS_S_BONUS = '+1';
  /**
   * ���� ���������� �� ��, ��� ��� �������� �� �����-�� ��������.
   *
   */
  const A_KIS_R_BONUS = -1;
  /**
   *
   * ���� ���������� �� ��, ��� ��� ������� ������������ �������.
   */
  const A_KIS_E_BONUS = '+1';
  /**
   * ������������ ����, ������� ����� ����
   *
   */
  const MAX_RANK = 3;
  /**
   * �����������, ���������� �������� �������� ��� ���
   */
  const PRO_FACTOR = 1.2;
  /**
   * �����������, ���������� �������� �������� ��� ����������������� ������������
   */
  const VERIFY_FACTOR = 1.2;
  
  /**
   * �����������, ���������� �������� �������� ��� PROFI
   */
  const PROFI_FACTOR = 1.4;
  
  
  /**
   * ������ � ���������� �������� ������������. ����������� � $this->init()
   *
   * @var array
   */
  public $data = NULL;

  /**
   * ��� ��������
   *
   * @var string
   */
  private $log_file = null;
  
  public $bit_factor = array(0 => "���������������. ����� �� ����������� ������",
                             1 => "���������������. ����� �� ������ �� ������ �������������",
                             2 => "���������������. ������ �������",
                             3 => "������������. ����� �� ����������� ������",
                             4 => "������������. ����� �� ��������-����������� � ��������",
                             5 => "������������. ����� �� ������ �� ������ �������������",
                             6 => "������������. ������ �������",
                             7 => "����������. ����� �� ����������� ������",
                             8 => "����������. ����� �� ������ �� ������ �������������",
                             9 => "����������. ������ �������",
                             10 => "������ �������. ����� �� ������ ����������� �� �������",
                             11 => "������ �������. ����� �� ������ �� ����",
                             12 => "������ �������. ����� �� ������������� ��������",
                             13 => "������ �������. ����� �� ���������.",
                             14 => "������ �������. �������� ���-�� ����� �� ���������",
                             15 => "������ �������. ������ �������: ������ � �.�.",
                             16 => "������ �������. ����� �� �������������� ������ � ������",
                             17 => "������ �������. ����� �� �������������� �������",
                             18 => "������ �������. ����� �� ����������� ������ (������������)",
                             19 => "������ �������. ����� �� �������������� ������� ������� (������������)",
                             20 => "������ �������. ����� �� �������������� �������� (������������)",
                             21 => "������ �������. ����� �� ��������� � ������� ��������� (������������)",
                             22 => "������ �������. ����� �� ������� �������� ������� (������������)",
                             23 => "������ ������������� + ����� �� ������ ������� *_opi_factor",
                             24 => "������������ �������������/����������� + ����� �� ������ ����� *_sbr_factor",
                             25 => "������ �������. ����� �� ���������� ������",
                             26 => "������ �������. ����� �� ������ ����� � ��������",
                             27 => "������ �������. ����� �� ������ ����� � ��������",
                             28 => "������ �������. ����� �� ������ ����� � ��������",
                             29 => "������ �������. ��� �� �������� �� ������������ (�� ������ � ����� �� ����������)",
                             30 => "������������",
                             31 => "������ �������. ����� ��������� �� 500 ���������� � ����������.",
                             32 => "����� �� ����������� �����������" 
                       ); 
    
  
  /**
   * �����������, �������� $this->init()
   * @see self::init()
   *
   * @param   integer   $user_id     uid ������������
   * @param   string    $is_pro      't' - ���� ������������ pro, 'f' - ���� ���
   * @param   boolean   $is_verify   't' - ���� ������������ ����������������, 'f' - ���� ���
   * @param   boolean   $is_profi    't' - ���� ������������ PROFI, 'f' - ���� ���
   * @param   boolean   $fill_pos    ��������� ������� ������������ ������������ ������ �����������?
   * @param   boolean   $fill_max    ��������� ������������ ������� ����� ���� �����������?
   * @param   integer   $is_emp ������������(1) ��� ���������(0)
   */
  function __construct($user_id=NULL, $is_pro='f', $is_verify='f', $is_profi='f', $fill_pos=TRUE, $fill_max=TRUE, $is_emp = 0)
  {
    $this->init($user_id, $is_pro, $is_emp, $is_verify, $is_profi);
  }
    
  
  /**
   * ��������� $this->data ���������� ������������, ��������� ������������ ������ ����������� � ������������� ����������
   *
   * @param   integer   $user_id     uid ������������
   * @param   string    $is_pro      't' - ���� ������������ pro, 'f' - ���� ���
   * @param   integer   $is_emp      ������������(1) ��� ���������(0)
   * @param   string    $is_verify   't' - ���� ������������ �������������, 'f' - ���� ���
   * @param   string    $is_profi    - PROFI 't' �� ��� 'f' ��� 
   * @return  boolean                ����� ��������
   */
  function init($user_id, $is_pro='f', $is_emp = 0, $is_verify='f', $is_profi='f')
  {
    if(!$user_id)
      return FALSE;
      
      global $DB;
      $this->data = $DB->row( 'SELECT *, rating_get(total, ?, ?, ?) as total, total as f_total FROM rating WHERE user_id = ?', $is_pro, $is_verify, $is_profi, $user_id );

      if( !count($this->data) )
        return FALSE;
	return TRUE;
  }

  
  /**
   * ������� ������� ����� ������� rating_get � ���������
   * @param   float    $rating     �������
   * @param   string   $is_pro     't' - ���� ������������ pro, 'f' - ���� ���
   * @return  float                ����������� �������
   */
  function GetByFormula($rating, $is_pro, $is_verify, $is_profi = 'f')
  {
    global $DB;
    if ( !($ret = $DB->val("SELECT rating_get(?f, ?, ?, ?)", $rating, $is_pro, $is_verify, $is_profi)) )
      return 0;

    return $ret;
  }

  /**
   * �������������� ������� ���� ���������� ������ ��� ��� ����������������
   *
   * @param    integer    $uid          ID ������������
   * @param    boolean    $is_pro       ��� ��� ���
   * @param    boolean    $is_verify    ������������� ��� ���
   * @return   integer                  �������
   */
  function GetPredictionPRO($uid, $is_pro='f', $is_verify='f') {
    global $DB;
    if ( !($ret = $DB->val("SELECT rating_prediction_pro(?i, ?, ?)", $uid, $is_pro, $is_verify)) )
      return 0;

    return $ret;
  }

  /**
   * �� ������� ���������� ������� �� ���������, ���� ������������ ����� ������� PRO.
   * ������������ � ������������� �� �������� ���������������.
   * @param   integer   $user_id   uid ������������
   * @return  integer              ������� ��������� PRO � �� PRO
   */
  function GetWorkFactorPlusIfPro($user_id)
  {
    global $DB;
    $sql = 
    "SELECT CASE WHEN o_wrk_factor_a > rating_const('o_wrk_pro_max')::smallint
                 THEN rating_const('o_wrk_pro_max')::smallint
                 ELSE o_wrk_factor_a
             END - o_wrk_factor
       FROM rating
      WHERE user_id = ?";

    return $DB->val( $sql, $user_id );
  }

  
  /**
   * ������� ���������� ������������ ������ ������ �� ��� ��������
   * @param    string    $prm    ������� � ������� rating �� �������� ������� ����� ������������� ������� ����������
   * @return   integer           �������
   */
  function get_pos_by($prm = 'total')
  {
    if($prm=='total')
      $sql = "SELECT COUNT(*) as cnt FROM freelancer WHERE rating_get(rating, is_pro, is_verify, is_profi) > {$this->data[$prm]}";
    else
      $sql = "SELECT COUNT(*) as cnt FROM rating WHERE {$prm} > {$this->data[$prm]}";

    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
    $memBuff = new memBuff();
    $pos = $memBuff->getSql($error, $sql, 1800);
    if(!$pos)
      return NULL;
    return $pos[0]['cnt'] + 1;
  }

  
  /**
   * ������� ���������� ������������ ������, ������ �� ��� ��������, � ���������� ��������������
   * 
   * /user/header.php ������ 45 �������� �� professions::GetCatalogPosition
   * 
   * @param    mixed   $specs    id ������������� ���� ������ � id �������������
   * @return   array             ������ ������� - id �������������; 
   *                             ��������� ������� - ������ � ������� ������� � ������������� � �� ���������
   */
  function get_pos_by_specs( $specs )
  {
      if ( !$specs ) return NULL;
      
    if ( !is_array($specs) )
        $aSpecs = array( $specs );

    global $DB;
    $sql = 
    "SELECT 
            p.id as prof_id,
            p.name as prof_name,
            COUNT(DISTINCT s.uid) + 1 as pos
       
       FROM professions p

     INNER JOIN
       professions px
         ON px.id = COALESCE((SELECT main_prof FROM mirrored_professions WHERE mirror_prof = p.id), p.id)
     LEFT JOIN
       (
         SELECT uid, spec_orig as spec
           FROM fu
          WHERE fu.is_banned = '0'
            AND rating_get(rating, is_pro, is_verify, is_profi) > ?f 
         UNION ALL
         SELECT fu.uid, sa.prof_id
           FROM fu
         INNER JOIN
           spec_add_choise sa
             ON sa.user_id = fu.uid
          WHERE fu.is_pro = true
            AND fu.is_banned = '0'
            AND rating_get(rating, is_pro, is_verify, is_profi) > ?f 
         UNION ALL
         SELECT fu.uid, sp.prof_id
           FROM fu
         INNER JOIN
           spec_paid_choise sp
             ON sp.user_id = fu.uid AND sp.prof_id IS NOT NULL AND sp.paid_to > NOW()
          WHERE fu.is_banned = '0'
            AND rating_get(rating, is_pro, is_verify, is_profi) > ?f 
       ) AS s
     INNER JOIN
       portf_choise pc
         ON pc.user_id = s.uid
        AND pc.prof_id = s.spec

         ON s.spec = px.id

      WHERE p.id IN (?l)
      GROUP BY p.id, p.name";

    $aRes = $DB->rows( $sql, $this->data['total'], $this->data['total'], $this->data['total'], $aSpecs );
    
    if( !count($aRes) )
      return NULL;

    $pos = NULL;
    
    foreach ( $aRes as $row )
      $pos[$row['prof_id']] = $row;

    return $pos;
  }

  
  /**
   * ����� ������� ������� ����� ���� �����������
   * @param    string    $prm    ������� � ������� rating �� ������� ����� ������������� ������������ �������
   * @param    integer   $is_emp ������������(1) ��� ���������(0)
   * @return   float             ������������ �������
   */
  function get_max_of($prm = 'total', $is_emp = 0)
  {
    global $DB;
    
    $from = 'freelancer';
    if ($is_emp) {
        $from = 'employer';
    }
    
    if($prm=='total')
        return $DB->val("SELECT rating_get(rating, is_pro, is_verify, is_profi) as rating FROM {$from} ORDER BY rating DESC LIMIT 1");
    else
        return $DB->val("SELECT {$prm} FROM rating ORDER BY {$prm} DESC LIMIT 1");
  }


  /**
   * ���������� ������������� ��������������� �������
   * @param   integer  $rank   ������
   * @return  integer          ���������� �������������
   */
  function CountByRank($rank)
  {
      global $DB;
      $sql =   "SELECT COUNT(*)
                FROM rating r
                INNER JOIN freelancer f
                    ON f.uid = r.user_id
                    AND f.ignore_in_stats = false 
                WHERE r.rank = ?i
                    AND f.is_banned = '0'";
      return $DB->cache(600)->val($sql , $rank);
  }


  /**
   * ������ ������������� ������������� �������, ��������������� �� ��������
   * @param    integer   $rank    ������
   * @param    string    $limit   ������� ������������� ������� (LIMIT $limit OFFSET $offset)
   * @param    integer   $offset  � ������ ������������ ������ ����� (LIMIT $limit OFFSET $offset)
   * @return   array              ������ �������������
   */
  function GetByRank($rank, $limit=0, $offset=0)
  {
    global $DB;
    $sql = 
    "SELECT f.uid, f.login, f.uname, f.usurname, f.is_profi, f.is_pro, f.photo, f.role, COALESCE(p.name, '') as prof_name
       FROM rating r
     INNER JOIN
       freelancer f
         ON f.uid = r.user_id AND f.ignore_in_stats = false AND f.is_banned = '0'
     LEFT JOIN
       professions p
         ON p.id = f.spec_orig
      WHERE r.rank = ?i 
      ORDER BY rating_get(f.rating, f.is_pro, f.is_verify, f.is_profi) DESC
      LIMIT ?i OFFSET ?i ";

    return $DB->cache(600)->rows( $sql, $rank, $limit, $offset );
  }


  /**
   * �������� ������ � ������� rating.
   * @param    integer   $user_id    uid ������������
   * @param    array     $fv         ������ � �������: ������ - ������� � ������� rating; �������� - ����� �������
   * @param    integer               ����� ��������
   */
  function Update($user_id, $fv)
  {
    // � ���, ����� ������� ����� ��������, ��. �������� � ������� rating
    global $DB;
    $DB->update( 'rating', $fv, 'user_id = ?', $user_id );
	
    return (empty($DB->error)) ? 1 : 0;
  }


  /**
   * ���������� �� ������ ����� ����� �������
   * @param   float   $val   ����� ��� ����������
   * @return  float          ���������
   */
  function round($val)
  {
    return round($val,2);
  }


  /**
   * �������� ������� rating_daily_calc(),
   * ������� ������� ������� �������� �������� ��� � �����.
   *
   * @return integer ����� ��������
   */
  function calcDaily() {
      global $DB;
      $sql  = 'SELECT * FROM rating_daily_calc() AS (ins int, del int)';
      $data = $DB->mrow( $sql );

      if ( $DB->error || !count($data) ) {
         $this->writeLog('!!������!!', $sql, $DB->error);
         return NULL;
      }

      $this->writeLog('������� �� �����', $sql, "���������� �����: {$data['ins']}. ������� �����: {$data['del']}");

      return true;
  }

  /**
   * �������� ������� rating_monthly_calc(),
   * ������� ������� ������� �������� �������� ��� � �����.
   * ������� ����������� �������� �������� �� 4-� ��������
   *
   * @return integer ����� ��������
   */
  function calcMonthly() {
      global $DB;
      $sql  = 'SELECT rating_monthly_calc_new() as ins';
      $data = $DB->mrow( $sql );

      if ( $DB->error || !count($data) ) {
         $this->writeLog('!!������!!', $sql, $DB->error);
         return NULL;
      }
      
      $this->writeLog('������� �� �����', $sql, "���������� �����: {$data['ins']}");

      return true;
  }

  /**
   * ����� ��� � ����
   *
   * @param string $name
   * @param string $sql
   * @param string $message
   */
  function writeLog($name, $sql, $message) {
      $log_file = $this->log_file;
      if(!$log_file) $log_file = $_SERVER['DOCUMENT_ROOT'] . '/stat_collector/logs/rating.log';

      if($f = fopen($log_file, 'a')) {
          $str = date('Y-m-d H:i:s') . ' ' . $name . ' : ';
          $str .= "������ ($sql) : ";
          $str .= $message . "\n";

          fwrite($f, $str);
          fclose($f);
      }
  }

  /**
   * ������ ��� ���-�����
   *
   * @param string $file
   */
  function setLogFile($file) {
      $this->log_file = $file;
  }

    /**
     * �������� ������� ������������ �� ����� �� ��������� ��������� ����.
     *
     * @param integer $user �� ������������
     * @param string $date ����
     */
    function getRatingByMonth($user_id, $date) {
        global $DB;

        $tm = strtotime($date); 

        $date_start = date('Y-m-01', $tm);
        $date_end = date('Y-m-t', $tm);

        $memBuffKey = 'rating_Month' . $user_id . '_' . $date;
        $memBuff = new memBuff();
        $res = $memBuff->get($memBuffKey);

        if ($res === false) {
            $sql = "
                SELECT 
                    r.user_id, 
                    r._date, 
                    COALESCE(r.rating, u.rating) as rating, 
                    u.is_pro,
                    u.is_verify
                FROM
                (
                    SELECT user_id, _date, rating FROM rating_daily WHERE user_id = ?i AND _date >= ? and _date <= ?
                    UNION ALL
                    ( SELECT user_id, _date, rating FROM rating_daily WHERE  user_id = ?i AND _date < ?  ORDER BY _date DESC LIMIT 1 )
                    UNION ALL
                    SELECT ?i, now()::date, NULL WHERE date_trunc('month', now()) = ?
                ) as r
                INNER JOIN users u ON u.uid = r.user_id
                ORDER BY r._date
            ";
            $res = $DB->rows($sql, $user_id, $date_start, $date_end, $user_id, $date_start, $user_id, $date_start);
            $memBuff->set($memBuffKey, $res, 3600 * 24);
        }

        return $res;
    }
  
    /**
     * �������� ������� ������� ������������.
     *
     * @param integer $user �� ������������
     * @param integer $year ���
     */
    function getRatingByYear($user_id, $year) {
        global $DB;

        $memBuffKey = 'rating_Year' . $user_id . '_' . $year;
        $memBuff = new memBuff();
        $res = $memBuff->get($memBuffKey);

        if ($res === false) {
            $sql = "SELECT 
                        s.*, 
                        false AS is_pro,
                        false AS is_verify
                    FROM (
                        SELECT * FROM rating_monthly_new 
                        WHERE user_id = ? AND date_part('year', _date) = ?
                        ORDER BY _date DESC LIMIT 1
                    ) s
                    UNION 
                    SELECT 
                        r.*,
                        users.is_pro AS is_pro,
                        users.is_verify AS is_verify
                    FROM rating_monthly_new r
                    INNER JOIN users ON users.uid = r.user_id
                    WHERE user_id = ? AND date_part('year', _date) = ? 
                          AND r._date < date_trunc('day', NOW())::date
                    ORDER BY _date";

            //$parts = $this->getMonthParts(date('Y-m-d')); ???

            $data = $DB->rows( $sql, $user_id, intval($year-1), $user_id, $year );

            $res = ( count($data) ) ? $data : null;
            $memBuff->set($memBuffKey, ($res === null ? false : $res), 3600 * 24);
        }
        
        return $res;
    }

  /**
   * ������� ������� (��� �������� �������) �� ��������� ����
   *
   * @param <type> $date
   * @return <type>
   */
  function getMonthParts($date) {
        $ts = strtotime($date);

        $months = array();
        for ($i = 0; $i < 12; $i++) {
            $ts = mktime(0, 0, 0, ($i + 1), 1, date('Y', $ts));
            $maxdays = date('t', $ts);

            $parts = array();
            $parts[] = mktime(0, 0, 0, date('m', $ts), 1+floor($maxdays / 4), date('Y', $ts));
            $parts[] = mktime(0, 0, 0, date('m', $parts[0]), (date('d', $parts[0]) + ceil($maxdays / 4)), date('Y', $parts[0]));
            $parts[] = mktime(0, 0, 0, date('m', $parts[1]), (date('d', $parts[1]) + floor($maxdays / 4)), date('Y', $parts[1]));
            $parts[] = mktime(0, 0, 0, date('m', $ts), $maxdays, date('Y', $ts));

            $months[$i] = $parts;
        }

        return $months;
    }
    
    /**
     * �������� ������� ������������ ��������� ������ $this->data @see self::init()
     * 
     * �������� ���� o_oth_factor ����� �������������� ��� ��������� �������
     * 
     * @return bool true - �����, false - ������
     */
    function nullRating() {
        return $this->Update( $this->data['user_id'], array('o_oth_factor' => ($this->data['o_oth_factor'] - $this->data['total'])) );
    }
    
    /**
     * ����� ���� �������� �� ������ ������������
     *
     * @param string $user
     */
    function getRatingLog($user=false, $filter = false, $limit=40, $page = 1, &$count=0) {
        global $DB;
        
        if($page > 0) {
            $page -= 1;
            $offset = $page*$limit;
        } else {
            $offset = 0;
        }
        
        $query = array();
        
        if($user !== false) {
            $query[] = " u.login = '$user' ";
        }
        
        if($filter !== false) {
            $bit = "000000000000000000000000000000000";
            $bit = substr_replace($bit, "1", $filter, 1);
            $query[] = " (rl.factor::bit(33) & B'{$bit}') != B'000000000000000000000000000000000' ";
        }
        
        $sql_query = implode(" AND ", $query);
        if($sql_query != "") $sql_query = "WHERE ".$sql_query;
        
        $sql = "SELECT 
                    rl.*, o.id as u_is_pro, u.is_verify, u.uname, u.usurname, u.login, u.role, o.from_date, (o.from_date + o.to_date::interval) as to_date, vff.req_time as ver_data_ff, vwm.req_time as ver_data_wm
                FROM rating_2month_log rl 
                JOIN users u ON 
                    rl.user_id = u.uid 
                LEFT JOIN orders o ON 
                    from_id = u.uid AND o.from_date <= rl._date AND o.from_date + o.to_date::interval >= rl._date
                LEFT JOIN verify_ff vff
                    ON vff.user_id = u.uid
                LEFT JOIN verify_webmoney vwm
                    ON vwm.user_id = u.uid
                {$sql_query}
                ORDER BY rl._date DESC
                LIMIT {$limit} OFFSET {$offset}
                ";
        $sql_count = "SELECT COUNT(*) as cnt FROM rating_2month_log rl JOIN users u ON rl.user_id = u.uid {$sql_query}";
        $count = $DB->val($sql_count);
        
        return $DB->rows($sql);
    }
    
    /**
     * �������� ����� ������� ������ �� �������
     *
     * @param string $str ��� �������
     * @return array ������� ����
     */
    function getBitFactors($str) {
        $position = array();
        while(($pos = strpos($str, '1')) !== false) {
            $i++;
            $position[] = $pos;
            $str = substr_replace($str, '0', $pos, 1);
            if($i > 100) break; // �� ������ ������
        } 
        
        return $position;
    }
    
    /**
     * �������� ���� ����������� ������������ �� ������ rating_log
     * ���� ����� ���� ��� ������ ������������ �� �������, ������ false 
     * @param int $user_id ����� ������������
     * @return mixed string|false
     */
    function GetVerifyDate($user_id) {
        global $DB;
        $uer_id = (int)$user_id;
        $query = "SELECT _date FROM  rating_2month_log WHERE substring( factor, 33, 1 )  = B'1' AND user_id = {$user_id} LIMIT 1"; // !! ������ �� ���????
        $date = $DB->val($query);
        if ( !$date ) {
            return false;
        }
        return $date;
    }
}
?>