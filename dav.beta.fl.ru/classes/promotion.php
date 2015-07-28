<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * �������� ���������������
 *
 */
class promotion
{

  /**
   * ���������� ������� �� �������� ���������������
   */
  const BM_COUNT     = 2;
  /**
   * ID ��� ������� "��������" (�������� bm � GET �������)
   */
  const BM_PROGNOSES = 0;
  /**
   * ID ��� ������� "����������" (�������� bm � GET �������)
   */
  const BM_GUESTS    = 1;
  /**
   * ID ��� ���������� "������� PRO" � ��������� (�������� tool � GET �������)
   */
  const TOOL_PRO_ID = 0;
  /**
   * ID ��� ���������� "������� �����" � ��������� (�������� tool � GET �������)
   */
  const TOOL_FP_ID  = 1;
  /**
   * ID ��� ���������� "�� �������" � ������� ������ (�������� mode � GET �������)
   */
  const MODE_FP_MAIN_ID = 0;
  /**
   * ID ��� ���������� "� ��������" � ������� ������ (�������� mode � GET �������)
   */
  const MODE_FP_CTLG_ID = 1;

  
  /**
   * ���������� ���������� ��������� �� ���� �� ������ ������� stat_hourly
   * @param   integer   $user_id   uid ������������
   * @param   integer   $day       ���� � ������� postgresql
   * @return  array                ������ � ������� ���������, ������� ������ ��������� � ���������, ������ � ������� ����,
   *                               �������� �� �������������, ����������� � ��������. NULL - ���� ��������� ������ ��� ������ ���.
   */
  function GetDayFromHourly($user_id, $day)          // 
  {
    $DB = new DB('stat');
    $sql = 
    "SELECT '{$day}'::date                                    as _date,
            SUM((guest_id<>0 AND NOT(by_e) AND from_b)::int)  as by_f_from_b,
            SUM((guest_id<>0 AND by_e AND from_b)::int)       as by_e_from_b,
            SUM((guest_id=0  AND from_b)::int)                as by_u_from_b,
            SUM((guest_id<>0 AND NOT(by_e) AND from_c)::int)  as by_f_from_c,
            SUM((guest_id<>0 AND by_e AND from_c)::int)       as by_e_from_c,
            SUM((guest_id=0  AND from_c)::int)                as by_u_from_c,
            SUM((guest_id<>0 AND NOT(by_e) AND from_p)::int)  as by_f_from_p,
            SUM((guest_id<>0 AND by_e AND from_p)::int)       as by_e_from_p,
            SUM((guest_id=0  AND from_p)::int)                as by_u_from_p,
            SUM((guest_id<>0 AND NOT(by_e) AND from_t)::int)  as by_f_from_t,
            SUM((guest_id<>0 AND by_e AND from_t)::int)       as by_e_from_t,
            SUM((guest_id=0  AND from_t)::int)                as by_u_from_t,
            SUM((guest_id<>0 AND NOT(by_e) AND from_o)::int)  as by_f_from_o,
            SUM((guest_id<>0 AND by_e AND from_o)::int)       as by_e_from_o,
            SUM((guest_id=0  AND from_o)::int)                as by_u_from_o,
            SUM((guest_id<>0 AND NOT(by_e) AND from_s)::int)  as by_f_from_s,
            SUM((guest_id<>0 AND by_e AND from_s)::int)       as by_e_from_s,
            SUM((guest_id=0  AND from_s)::int)                as by_u_from_s,
            SUM((guest_id<>0 AND NOT(by_e))::int)             as by_f,
            SUM((guest_id<>0 AND by_e)::int)                  as by_e,
            SUM((guest_id=0)::int)                            as by_u
       FROM stat_hourly
      WHERE _time >= ?::date AND _time < ?::date + 1
        AND user_id = ?
      GROUP BY user_id";

    $res = $DB->row( $sql, $day, $day, $user_id );
    
    return count($res) ? $res : null;
  }

  /**
   * ���������� ���������� ��������� �� ������� �� ������ ������� stat_hourly.
   * @param   integer   $user_id   uid ������������
   * @return  array                ������ � ������� ���������, ������� ������ ��������� � ���������, ������ � ������� ����,
   *                               �������� �� �������������, ����������� � ��������. NULL - ���� ��������� ������ ��� ������ ���.
   */
  function GetToday($user_id)          // 
  {
    return self::GetDayFromHourly($user_id, 'now()');
  }


  /**
   * ���������� ���������� ��������� �� ��������� ������ + ����� ���� � ���������� ��������� �������� - ��������������, 
   * ������������ � ��������� �� ��������� ������.
   * @param   integer   $user_id     uid ������������
   * @param   string    $from_date   ���� ������ ������� � ������� postgresql
   * @param   string    $to_date     ���� ����� ������� � ������� postgresql
   * @param   string    $from_type   ������ ��� �������� ������ ��������� ����������: 
   *                                 all - ���, cats - �� ��������, blogs - �� ������, places - �� ������� ����, others - �� ������ ����
   * @return  array                  �������� ������� �� 0 �� 23, ������� ������� �� ��������: � ��������� emploers - ������ ������ � ������������� �
   *                                 freelancers - ������ ������ � �����������, �� ������ ���. ����� ���� �������� �������: by_e, by_f, by_u - � �����
   *                                 ����������� ��������� � by_e_uniq, by_f_uniq, by_u_uniq - � ���������� ����������� ��������� �� ��������� ������.
   */
  function GetGuests($user_id, $from_date, $to_date, $from_type='all')
  {
    $guests = NULL;

    global $DB;
    $sDB = new DB(defined('PROMOTION_GUESTS_DB_ALIAS') ? PROMOTION_GUESTS_DB_ALIAS : 'stat');

    $fromTypeSQL = '';
    switch($from_type) {
        case 'cats':
            $fromTypeSQL = "AND from_c = 't'";
            break;
        case 'blogs':
            $fromTypeSQL = "AND from_b = 't'";
            break;
        case 'places':
            $fromTypeSQL = "AND (from_p = 't' OR from_t = 't')";
            break;
        case 'offers':
            $fromTypeSQL = "AND from_o = 't'";
            break;    
        case 'search':
            $fromTypeSQL = "AND from_s = 't'";    
            break;
        case 'others':
            $fromTypeSQL = "AND (from_c = 'f' AND from_b = 'f' AND from_p = 'f' AND from_t = 'f' AND from_o = 'f')";
            break;
    }

    $sql = 
    "SELECT 
            EXTRACT(hour from _time) as hour,
            _time,
            guest_id,
            by_e  as guest_is_emp,
            from_b,
            from_c,
            from_p,
            from_t,
            from_o,
            from_s
       FROM stat_hourly
      WHERE _time >= ? AND _time < ? 
        AND user_id = ? 
        AND guest_id<>0
        {$fromTypeSQL}
      ORDER BY EXTRACT(hour from _time), by_e DESC, CASE WHEN by_e THEN now() - _time ELSE _time - CURRENT_DATE END";
    
    $res_arr = $sDB->rows( $sql, $from_date, $to_date, $user_id );
    
    if ( !$sDB->error ) {
      if ( count($res_arr) ) {
        foreach ( $res_arr as $row ) {
            $tgids[] = $row['guest_id'];
        }
        $sql = 'SELECT uid, login as guest_login, photo as guest_photo, usurname as guest_usurname, uname as guest_uname FROM users WHERE uid IN (?l)';
        $grows = $DB->rows( $sql, array_unique($tgids) );
        foreach($grows as $g) {
            $gids[$g['uid']] = $g;
        }
        foreach ( $res_arr as $row ) {
            if( !($g = $gids[$row['guest_id']]) ) continue;
            $guests[$row['hour']][($row['guest_is_emp']=='t' ? 'employers' : 'freelancers')][] = ($row + $g);
        }
      }
      
      $sql = 
      "SELECT
              SUM( (guest_id<>0 AND by_e)::int ) as by_e,
              SUM( (guest_id<>0 AND NOT(by_e))::int ) as by_f,
              SUM( (guest_id = 0)::int ) as by_u,
              COUNT( DISTINCT (guest_id || ',' || guest_ip) || NULLIF((guest_id<>0 AND by_e), false) ) as by_e_uniq,
              COUNT( DISTINCT (guest_id || ',' || guest_ip) || NULLIF((guest_id<>0 AND NOT(by_e)), false) ) as by_f_uniq,
              COUNT( DISTINCT (guest_id || ',' || guest_ip) || NULLIF((guest_id = 0), false) ) as by_u_uniq
         FROM stat_hourly h
        WHERE _time >= ? AND _time < ?
          AND user_id = ?
          {$fromTypeSQL}";
      
      $res_arr = $sDB->row( $sql, $from_date, $to_date, $user_id );
      
      if ( !$sDB->error && count($res_arr) ) {
        if ( $guests )
          $guests += $res_arr;
        else
          $guests = $res_arr;
      }
    }

    return $guests;
  }


  /**
   * ���������� ����� ���������� �� ������������: ����� ���-�� ����������, �� 7 � 30 ���� (������� stat_summary).
   * @param   integer   $user_id   uid ������������
   * @return  array                ������ � �������
   */
  function GetSummary($user_id)        // 
  {
    $sDB = new DB('stat');
    $res = $sDB->row( "SELECT * FROM stat_summary WHERE user_id = ?", $user_id );
    
    return ( !$sDB->error && count($res) ) ? $res : null;
  }


  /**
   * ���������� ���������� ���������� �������� ������������ �� ���� (������� stat_daily) �� ��������� ������.
   * @param   integer   $user_id     uid ������������
   * @param   string    $from_date   ���� ������ ������� � ������� postgresql
   * @param   string    $to_date     ���� ��������� ������� � ������� postgresql ��� NULL, ���� ����� ������ �� ������������ ���
   * @return  array                  ������ � �������
   */
  function GetFromDaily($user_id, $from_date, $to_date = NULL) // 
  {
    $sDB = new DB('stat');
    $sql = "SELECT * FROM stat_daily WHERE user_id = {$user_id} AND _date >= '{$from_date}'".($to_date ? " AND _date < '{$to_date}'" : '')." ORDER BY _date";
    $res = $sDB->rows( $sql );
    
    return ( !$sDB->error && count($res) ) ? $res : null;
  }

  
  /**
   * ���������� ���������� ���������� �������� ������������ �� ������� (������� stat_monthly) �� ��������� ������.
   * @param   integer   $user_id     uid ������������
   * @param   string    $from_date   ���� ������ ������� � ������� postgresql (������� ����������� ������ ��� � �����)
   * @param   string    $to_date     ���� ��������� ������� � ������� postgresql ��� NULL, ���� ����� ������ �� ������������ ��� (������� ����������� ������ ��� � �����)
   * @return  array                  ������ � �������
   */
  function GetFromMonthly($user_id, $from_date, $to_date = NULL) // 
  {
    $sDB = new DB('stat');
    $sql = "SELECT * FROM ONLY stat_monthly WHERE user_id = {$user_id} AND _date >= '{$from_date}'".($to_date ? " AND _date < '{$to_date}'" : '')." ORDER BY _date";
    $res = $sDB->rows( $sql );
    
    return ( !$sDB->error && count($res) ) ? $res : null;
  }

  
  /**
   * ���������� ���������� ���������� �������� ������������ �� ���� ���� (������� stat_daily).
   * @param   integer   $user_id     uid ������������
   * @param   string    $date        ����
   * @return  array                  ������ � �������
   */
  function GetDayFromDaily($user_id, $day)                     // 
  {
    $from_date = $day;
    $to_date = date('Y-m-d', strtotime($day) + 3600 * 24);
    if(!$res_arr = self::GetFromDaily($user_id, $from_date, $to_date))
      return NULL;

    return $res_arr[0];
  }

  
  /**
   * ���������� ���������� ���������� �������� ������������ �� ��������� ����.
   * @param   integer   $user_id     uid ������������
   * @param   string    $date        ���� NULL, �� ���������� ������������ �� ��������� ����, ����� �� ���������
   * @return  array                  ������ � �������
   */
  function GetYesterday($user_id, $day = NULL)                     // 
  {
    $res = NULL;
    if(!$day)
        $day = date('Y-m-d', time() - 3600 * 24);
    if(!($res = self::GetDayFromDaily($user_id, $day)))
        $res = self::GetDayFromHourly($user_id, $day);
    return $res;
  }

  
  /**
   * ������� �� 5 "�������" ������ ������ �������.
   * ������ �� ������ ���� ��������� � ������� ������ � ������ ����� ����� ���� ��� � ������.
   * @param   array    $pro_group      ������ � ������� ������������� ������� ��� � ���� ���� � ��� �� ��������������. ��. - self::GetProCatalogByDay()
   * @param   integer  $base_rating    ������� ������������ ��� �������� ����� ����� "�������"
   * @return  array                    ������ � ��������� A,B,C,D,E ����������� �� ����� �������� �� $pro_group � ������� "������"
   */
  function GetNeighbours($pro_group, $base_rating)             // 
  {
    $uppers = array();
    $lowers = array();
    $tmp = &$uppers;

    foreach($pro_group as $idx=>$n) {
      if($n['rating'] < $base_rating) {
        $tmp = &$lowers;
        //if($n['user_id']==$uid) continue;
      }
      $n['index'] = $idx;
      $tmp[] = $n;
    }

    $uppers = array_reverse($uppers);

    $nn = array('A'=>NULL, 'B'=>NULL, 'C'=>NULL, 'D'=>NULL, 'E'=>NULL);

    // ������� �������� ������-�����, ������-�����.

    $cond = array(true, true, true, true);
    $ulen = count($uppers);
    $llen = count($lowers);
    $j=0;

    while(!$nn['C'] && $j<=4) {
      for($u=0,$l=0; $u<$ulen || $l<$llen; $u++,$l++) {
        $up = $uppers[$u];
        $lo = $lowers[$l];

        if( !$up
            || ($cond[0] && $up['due_to_add_spec'])
            || ($cond[1] && $up['has_otherpage_ps'])
            || ($cond[2] && $up['blog_count'] > 3)
            || ($cond[3] && $up['has_firstpage_ps'])  )
        {
          if( !$lo
              || ($cond[0] && $lo['due_to_add_spec'])
              || ($cond[1] && $lo['has_otherpage_ps'])
              || ($cond[2] && $lo['blog_count'] > 3)
              || ($cond[3] && $lo['has_firstpage_ps'])  )
          {
            continue;
          }

          $nn['C'] = $lo;
          break;
        }

        $nn['C'] = $up;
        break;
      }

      $cond[$j++] = false;
    }

    if($nn['C']) {
      if($nn['B'] = self::_getABDENeighbours($pro_group, $nn['C']['index']-1, $u, -1))
        if($nn['D'] = self::_getABDENeighbours($pro_group, $nn['C']['index']+1, $l, 1))
          if($nn['A'] = self::_getABDENeighbours($pro_group, $u-1, $u, -1))
            $nn['E'] = self::_getABDENeighbours($pro_group, $l+1, $l, 1);
    }

    return $nn;
  }

  /**
   * ��������������� ������� 
   * @see self::GetNeighbours
   * 
   * @param  array $pro_group
   * @param  int $start
   * @param  int $i
   * @param  int $dir
   * @return mixed
   */
  function _getABDENeighbours($pro_group, $start, &$i, $dir)             // 
  {
    $cond = array(true, true, true, true);
    $j=0;
    while($j<=4)
    {
      for($i=$start; $dir>0&&$i<count($pro_group)||$dir<0&&$i>=0; $i+=$dir) {
        $cd = $pro_group[$i];
        if( ($cond[0] && $cd['due_to_add_spec'])
            || ($cond[1] && $cd['has_otherpage_ps'])
            || ($cond[2] && $cd['blog_count'] > 3)
            || ($cond[3] && $cd['has_firstpage_ps'])  )
        {
          continue;
        }

        return $cd;
      }

      $cond[$j++] = false;
    }

    return NULL;
  }

  /**
   * ���������� ������� � ������� ������������ ��� PRO
   * @param   integer  $user_id     uid ������������
   * @param   string   $from_date   ������ ������� � ������� postgresql
   * @param   boolean  $check_freeze   ��������� ������� ���������
   * @return  array                 ������ � ������� ��� NULL ���� ������ ��� ��� ��������� ������
   */
  function GetUserProPeriods( $user_id, $from_date, $check_freeze = false )
  {
    global $DB;
    $sql = 
    "SELECT DISTINCT
            CASE WHEN from_date < ? THEN ? ELSE from_date END  as from_time,
            from_date + to_date + COALESCE(freeze_to, '0')::interval as to_time,
            CASE WHEN tarif IN(164) THEN 1 ELSE 0 END as is_profi
       FROM orders
      WHERE from_id = ?i 
        -- AND payed = true -- deprecated #0021704
        AND from_date + to_date + COALESCE(freeze_to, '0')::interval >= ? 
      ORDER BY from_time, to_time DESC";
    
    $res = $DB->rows( $sql, $from_date, $from_date, $user_id, $from_date );

    if ($DB->error || !count($res)) {
        return null;
    }
    
    if ($check_freeze) {
        $freeze = promotion::getFreezePeriods($user_id);
        if (!$freeze) $freeze = array();
        
        $f = array();
        $pro = array();
        
        foreach($freeze as $i => $p) {
            $from = strtotime(date('Ymd', strtotime($p['from_time'])));
            $to = strtotime(date('Ymd', strtotime($p['to_time'])));
            
            $d = $from;
            while ($d < $to) {
                $f[] = $d;
                $d = mktime(0, 0, 0, date('m',$d), date('d',$d)+1, date('Y',$d));
            }
        }
        
        $st = 1000;
        foreach ($res as $i => $p) {
            $from = strtotime(date('Ymd', strtotime($p['from_time'])));
            $to = strtotime(date('Ymd', strtotime($p['to_time'])));
            
            $d = $from;
            $freezed = false;
            $pro[$st]['is_profi'] = $p['is_profi'];
            
            while ($d <= $to) {
                if (!in_array($d, $f)) {
                    $pro[$st][] = $d;
                    $freezed = false;
                } else {
                    if (!$freezed) $st++;
                    $freezed = true;
                }
                $d = mktime(0, 0, 0, date('m',$d), date('d',$d)+1, date('Y',$d));
            }
            $st++;
        }
        
        $new = array();
        foreach ($pro as $k => $v) {
            $new[$k]['from_time'] = date('Y-m-d', $v[0]);
            $new[$k]['to_time'] = date('Y-m-d', end($v));
            $new[$k]['is_profi'] = $v['is_profi'];
        }
        
//        echo '<pre>'; var_dump($new); die();
        
        if (count($new)) {
            $res = $new;
        }
        
    }
    
    return count($res) ? $res : null;
  }


  /**
   * ���������� ������� � ������� ������������ ��������� � ������� ������
   * @param    integer   $user_id     uid ������������
   * @param    string    $from_date   ������ ������� � ������� postgresql
   * @param    integer   $prof_id     ����� ������������ � ������� ������ (id �� professions)
   * @param    string    $prof_op     SQL �������� ��� $prof_id � ����� WHERE �������
   * @return   array                  ������ � ������� ��� NULL ���� ������ ��� ��� ��������� ������
   */
  function GetUserPsPeriods(              // 
   $user_id,
   $from_date,
   $prof_id=-1,
   $prof_op='=')
  {
    global $DB;
    $sql = 
    "SELECT DISTINCT
            CASE WHEN from_date < ? THEN ? ELSE from_date END  as from_time,
            from_date + to_date                                as to_time
       FROM users_first_page
      WHERE payed = true
        AND from_date + to_date >= ? 
        AND profession {$prof_op} ? 
        AND user_id = ? 
      ORDER BY from_time, to_time DESC";
    
    $res = $DB->rows( $sql, $from_date, $from_date, $from_date, $prof_id, $user_id );
    
    return ( !$DB->error && count($res) ) ? $res : null;
  }


  /**
   * ���������� ���������� ���� ����������� ������������� � ������� ������ �� ��������� ������.
   * @param   integer    $user_id    uid ������������
   * @param   string     $from_date  ���� ������ ������� � ������� postgresql
   * @param   string     $to_date    ���� ��������� ������� � ������� postgresql
   * @return  array                  ���������� ������ �� ���� ���������: fp_days - ���������� ���� �� ������ ��������, 
   *                                 ctg_days - ���������� ���� � ��������� ������ ��� NULL ���� ������ ��� ��� ��������� ������
   */
  function GetUserPsDayCount(              // 
   $user_id,
   $from_date,
   $to_date)
  {
    global $DB;
    $sql =                                                                                                                                       
    "SELECT 
            SUM( (profession = -1)::int * 
                 ((from_date + to_date)::date - 
                  (CASE WHEN from_date < ? THEN ?::date ELSE from_date::date END) -
                  (CASE WHEN from_date + to_date <= ? THEN 0 ELSE (from_date + to_date)::date - ?::date END)) ) as fp_days,
            SUM( (profession <> -1)::int * 
                 ((from_date + to_date)::date - 
                  (CASE WHEN from_date < ? THEN ?::date ELSE from_date::date END) -
                  (CASE WHEN from_date + to_date <= ? THEN 0 ELSE (from_date + to_date)::date - ?::date END)) ) as ctg_days
       FROM users_first_page
      WHERE payed = true
        AND from_date <= ? AND from_date + to_date >= ? 
        AND user_id = ?";

    $res = $DB->row( $sql, $from_date, $from_date, $to_date, $to_date, $from_date, $from_date, $to_date, $to_date, $to_date, $from_date, $user_id );
    
    return ( !$DB->error && count($res) ) ? $res : null;
  }

  /**
   * ���������� ����������, � ����������, ���������� � ��������� PRO ��������� ���� $base_rating � ������������� ���������� $spec
   * @param   integer   $spec          id �������������
   * @param   integer   $base_rating   �������� �������
   * @return  integer                  uid ���������� ������������ ��� NULL, ���� ������ ������������ ��� ��� ��������� ������
   */
  function GetProFromLikeSpec(                // 
   $spec,
   $base_rating)
  {
    $R = 'rating_get(fu.rating, true, fu.is_verify, fu.is_profi)';
    $r = floatval($base_rating);
   
    global $DB; 
    $sql = 
    "SELECT fu.uid
       FROM fu
     INNER JOIN
       orders o
         ON o.from_id = fu.uid
        AND o.payed = true
        AND ( o.from_date <= now()::date - 27 AND o.from_date + o.to_date >= now() -- �����, ���� �������� ����� ��� ���.
              OR o.from_date > now()::date - 27 AND o.from_date<>o.posted ) -- ������, ��� �������.
     INNER JOIN
       professions p
         ON p.id = fu.spec_orig
     CROSS JOIN
       ( SELECT pcount FROM professions WHERE id = ? ) px
      WHERE fu.is_banned = '0'
      ORDER BY (p.pcount >= px.pcount) DESC,  -- � ���� ������ � ������� ������������.
               ABS(p.pcount - px.pcount),     -- �� ��������� �� ������������ � ���������.
               ({$R} >= ?f) DESC,           -- � ���� ������� ������ ���������.
               ABS({$R} - ?f)               -- �� ��������� � ���������.
      LIMIT 1";

    return $DB->val( $sql, $spec, $r, $r );
  }


  /**
   * ������� ������������� �� �������������� $spec, ����������� ������ � ����� �������� � � ������� � ������� ��������� ������� ��� PRO.
   * ���������� ������� � ������� ��� ������������ ����� PRO �������.
   * ����� ������ � GetProCatalogByDay() � ����� ��� ����, ����� ��������� ����� �� ����� ��� �������� �� ���������� ����.
   * @param   array    $specs       ������ id �������������
   * @param   string   $from_date   ������ ������� � ������� postgresql (������������ ������ ���� PRO ���� ���� ����)
   * @param   string   $to_date     ����� ������� � ������� postgresql (������������ ������ ���� PRO ���� ���� ����)
   * @return  array                 ������ � ������� ��� NULL ���� ������ ��� ��� ��������� ������
   */
  function GetProPeriods(
   $specs,
   $from_date,
   $to_date)
  {
    global $DB;
    $sql = 
    "SELECT DISTINCT
            CASE WHEN o.from_date < ? THEN ? ELSE o.from_date::date END                        as from,
            CASE WHEN o.from_date + o.to_date > ? THEN ? ELSE (o.from_date + o.to_date)::date END  as to
        FROM 
        (
          SELECT uid FROM fu WHERE fu.is_banned = '0' AND fu.spec_orig IN ({$specs})
          UNION ALL
          SELECT fu.uid
            FROM fu
          INNER JOIN
            spec_add_choise s
              ON s.user_id = fu.uid
             AND s.prof_id IN ({$specs})
           WHERE fu.is_banned = '0'
          UNION ALL
          SELECT fu.uid
            FROM fu
          INNER JOIN
            spec_paid_choise sp
              ON sp.user_id = fu.uid
             AND sp.prof_id IN ({$specs}) AND sp.paid_to > NOW()
           WHERE fu.is_banned = '0'
        ) as fu
      INNER JOIN
        orders o
          ON o.from_id = fu.uid
         AND o.payed = true
         AND o.from_date <= ? AND o.from_date + o.to_date >= ?::date + 1";
        
    $res = $DB->rows( $sql, $from_date, $from_date, $to_date, $to_date, $to_date, $from_date );
    
    return ( !$DB->error && count($res) ) ? $res : null;
  }


  /**
   * ���������� ������ ������������� � ������� � ��������� ���� (�� ��������� �������) ��� ������� PRO (���� ����).
   * @param   array    $specs    ������ id �������������
   * @param   string   $day      ���� � ������� postgresql
   * @return  array              ������ � ������� ������������� ������� �������, ������������ �� ������ ��������, ���-�� ��������� � ������
   */
  function GetProCatalogByDay(         // 
   $specs,
   $day = 'CURRENT_DATE') // ����� ���, � ���� ����� ���� ���� ��� ���.
  {
    global $DB;
    $sql = 
    "SELECT 
            fu.uid as user_id,
            rating_get(fu.rating, true, fu.is_verify, fu.is_profi) as rating,
            o.from_date + o.to_date as pro_to,
            MAX((ufp.profession = -1)::int) as has_firstpage_ps,
            MAX((ufp.profession > -1)::int) as has_otherpage_ps,
            due_to_add_spec,
            COUNT(b.id) as blog_count

        FROM
        (
          SELECT 
                uid, 
                rating, 
                1 - MAX(is_main_spec) as due_to_add_spec, 
                is_verify, 
                is_profi
            FROM
            (
              SELECT uid, rating, 1 as is_main_spec, is_verify, is_profi 
              FROM fu 
              WHERE fu.is_banned = '0' AND fu.spec_orig IN ({$specs})
              
              UNION ALL

              SELECT fu.uid, fu.rating, 0, is_verify, is_profi
                FROM fu
              INNER JOIN
                spec_add_choise s
                  ON s.user_id = fu.uid
                 AND s.prof_id IN ({$specs})
               WHERE fu.is_banned = '0'
               
              UNION ALL
              
              SELECT fu.uid, fu.rating, 0, is_verify, is_profi
                FROM fu
              INNER JOIN
                spec_paid_choise sp
                  ON sp.user_id = fu.uid
                 AND sp.prof_id IN ({$specs}) AND sp.paid_to > NOW()
               WHERE fu.is_banned = '0'
            ) as x
           GROUP BY uid, rating, is_verify, is_profi
        ) as fu
      INNER JOIN
        orders o
          ON o.from_id = fu.uid
         AND o.payed = true
         AND (o.from_date <= ?::date AND o.from_date + o.to_date >= ?::date + 1
              OR o.from_date::date = ?::date AND o.posted <> o.from_date)  -- ���� �������.
      LEFT JOIN
        users_first_page ufp
          ON ufp.payed = true
         AND ufp.from_date <= CURRENT_DATE
         AND ufp.from_date + ufp.to_date >= CURRENT_DATE
         AND ufp.user_id = fu.uid
      LEFT JOIN
        blogs_msgs_".date('Y')." b
          ON b.fromuser_id = fu.uid
         AND b.reply_to IS NULL
         AND b.post_time >= CURRENT_DATE - 7 AND b.post_time < CURRENT_DATE

       GROUP BY fu.uid, rating, o.from_date + o.to_date, due_to_add_spec, fu.is_verify, fu.is_profi
       ORDER BY rating DESC, fu.uid DESC";
    
    $res = $DB->rows( $sql, $day, $day, $day );

    return ( !$DB->error && count($res) ) ? $res : null;
  }


  /**
   * ���������� ���������� ��������� �� ������������� �� ������� ��� �������� �� �������
   * @param   integer   $user_id   uid ������������
   * @return  array                ������, ������� �������� ������ �������, � �������� ���������� ���������
   */
  function GetCountMsgsByMonths($user_id)
  {
    $ret = NULL;
    $DB  = new DB;
    $sql = "SELECT * FROM messages_emp_months_count(?i)";

    $res = $DB->rows( $sql, $user_id );
    
    if ( !$DB->error ) {
        foreach ( $res as $row )
            $ret[$row['out_month']] = $row['out_count'];
    }
    
    return $ret;
  }


  /**
   * ���������� ��������� ������������ ��������� �� �������� �������������, � ��������.
   * (������� ��������� �� ���� ������, ������� stat_week_coeffs)
   * @return   array   ������ � ������� ��� NULL, ���� ������ ��� ��� ��������� ������
   */
  function GetWeekCoeffs()   // 
  {
    $sDB = new DB('stat');
    $res = $sDB->rows( "SELECT * FROM stat_week_coeffs ORDER BY week_day" );
    
    return ( !$sDB->error && count($res) ) ? $res : null;
  } 


  /**
   * ���������� ����� ���������� ���������, �� �������� �������������, � ������� ���� �� ������� (������, ������ � ��������� �����)
   * � � ������� ���� � ����� �������� (������ �����) �� ��������� ������.
   * @param   string   $from_date   ������ ������� � ������� postgresql (������������ ������ ���� PRO ���� ���� ����)
   * @param   string   $to_date     ����� ������� � ������� postgresql (������������ ������ ���� PRO ���� ���� ����)
   * @return  array                 ������ � ������� ��� NULL, ���� ������ ��� ��� ��������� ������
   */
  function GetFromPSummary(                // 
   $from_date,
   $to_date)
  {
    $ret = NULL;
    $sDB = new DB('stat');
    $sql = "SELECT pos_id, -- 1, 2, -1: �� ������� (������, ������ � ��������� ����� ����.). 0 -- ������ ����� � ����� ��������.
                   SUM(by_e) as by_e,
                   SUM(by_f) as by_f,
                   SUM(by_u) as by_u,
                   SUM(by_e+by_f+by_u) as by_a
              FROM stat_from_p
             WHERE _date >= ? AND _date < ?
             GROUP BY pos_id";
    
    $res = $sDB->rows( $sql, $from_date, $to_date );

    if ( !$sDB->error ) {
      foreach ( $res as $row )
        $ret[$row['pos_id']] = $row;
    }

    return $ret;
  }
  
    /**
     * ���������� ���������� � ��������� �������� ����� �� �������� ������.
     *
     * @param  int $user_id UID ������������.
     * @param  string $sort_order ������ ����������
     * @return array ������ � �������
     */
    function getWordsSummary( $user_id = 0, $sort_order = 'total' ) {
        global $DB;
        
        $sOrder = '';
        
        switch ( $sort_order ) {
            case 'today':
                $sOrder .= 'total_today_cnt DESC';
                break;
            case 'yesterday':
                $sOrder .= 'total_yesterday_cnt DESC';
                break;
            case '30days':
                $sOrder .= 'total_30_cnt DESC';
                break;
        }
        
        $sOrder .= (($sOrder) ? ', ' : '') . 'total_cnt DESC, word_name';
        $aReturn = array();
        $sQuery  = "SELECT s.user_id, s.word_id, w.name AS word_name, 
                s.emp_cnt + s.frl_cnt + s.user_cnt AS total_cnt, 
                s.emp_30_cnt + s.frl_30_cnt + s.user_30_cnt AS total_30_cnt, 
                SUM(d.emp_cnt) + SUM(d.frl_cnt) + SUM(d.user_cnt) AS total_yesterday_cnt, 
                SUM(h.emp_cnt) + SUM(h.frl_cnt) + SUM(h.user_cnt) AS total_today_cnt, pw.uid as is_active  
            FROM stat_word_summary s 
            INNER JOIN words w ON w.id = s.word_id 
            LEFT JOIN stat_word_daily d ON 
                d.user_id = s.user_id AND d.word_id = s.word_id 
                AND d.stat_date >= date_trunc('day', NOW())::date - 1 AND d.stat_date < date_trunc('day', NOW())::date 
            LEFT JOIN stat_word_hourly h ON 
                h.user_id = s.user_id AND h.word_id = s.word_id 
                AND h.stat_date >= date_trunc('day', NOW())::date
            LEFT JOIN portf_word pw ON pw.wid = w.id AND pw.uid = s.user_id     
            WHERE s.user_id = ? AND (s.emp_cnt + s.frl_cnt + s.user_cnt) > 0 
            GROUP BY s.user_id, w.name, s.word_id, s.emp_cnt, s.frl_cnt, s.user_cnt, 
                s.emp_30_cnt, s.frl_30_cnt, s.user_30_cnt, pw.uid  
            ORDER BY $sOrder";
        $aReturn = $DB->rows( $sQuery, $user_id );
        
        return $aReturn;
    }



    /**
     * ���������� ������� ��������� ���
     *
     * @param  int $user �� ������������
     * @return array
     */
    function getFreezePeriods($user) {
        global $DB;
        $sql = "SELECT *, from_time::date AS from_time_date,
                       to_time::date AS to_time_date
                FROM orders_freezing_pro WHERE user_id = ?";

        $data = $DB->rows($sql, $user);
        return ( count($data) ) ? $data : null;
    }

}



/**
 * ����� ��� ����������� ��������� HTML ������ �� �������� ���������������
 *
 */
class t_promotion
{
  
  /**
   * ��������� �� ������ ����� ���������� round � php, �� ��� �����������, ��� ������ ����� ����������� � ������� �������
   * ���� ������� ����� ����� ������ 0.5 (�� �� �����). ����� ��� ����� ���������� ������������� ��������.
   * @param    float     $num   ����������� �����
   * @return   integer          ���������
   */
  function bround($num)
  {
    //return round($num);
    $floor=floor($num);
    if($num-0.5==$floor && !($floor%2)) return $floor;
    return round($num);
  }
  
  
  /**
   * ������ ����������� ������� � ������� ��������� (� ����������� �� �����������, ������������� � ��������)
   * @param   float    $h           ���������� ������ ������ ������������ ������� � ��������
   * @param   float    $K_h         ���������� �������� � ������� ���������� ��� ������ ������������
   *                                (���������� �� - ������������ ������ ������� / ������������ ���-�� ������������� �� ������)
   * @param   float    $by_e        ���������� ������������� (����� ���� float, �������� ��� �������� ��������)
   * @param   float    $by_f        ���������� ����������� (����� ���� float, �������� ��� �������� ��������)
   * @param   float    $by_u        ���������� �������� (����� ���� float, �������� ��� �������� ��������)
   * @param   integer  $colspan     html �������� colspan ��� td (���� 0, �� �� ������������)
   * @param   boolean  $with_nums   �������� ��, ����� �������, ��� � �������� ��������?
   * @param   string   $cls         �������� ��� ����� css ������ � td
   */
  function proCol(&$h, $K_h, $by_e, $by_f, $by_u, $colspan=0, $with_nums=false, $cls=NULL)
  {
    $eh = ceil($by_e * $K_h);
    $fh = ceil($by_f * $K_h);
    $uh = ceil($by_u * $K_h);
    $h = $eh+$fh+$uh;
    ob_start();
  ?>
    <td<?=($colspan&&$colspan>1 ? ' colspan="'.$colspan.'"' : '')?> style="height:140px; width:2px;" class="lorange-bg z-s<?=($cls?' '.$cls:'')?>" width="2"><? 
      if($with_nums) {
        if($by_e) { ?><div class="green-c sml-s"><?=$by_e?></div><? }
        if($by_f) { ?><div class="orange-c sml-s"><?=$by_f?></div><? }
        if($by_u) { ?><div class="sml-s" style="padding-bottom:3px"><?=$by_u?></div><? }
      }
      if($h) { 
        if($eh) { ?><div class="green-col" style="height:<?=$eh?>px">&nbsp;</div><? }
        if($fh) { ?><div class="orange-col" style="height:<?=$fh?>px">&nbsp;</div><? }
        if($uh) { ?><div class="gray-col" style="height:<?=$uh?>px">&nbsp;</div><? }
      } else print('<div></div>');
    ?></td>
  <?
    return ob_get_clean();
  }

  
  /**
   * ������ ����������� (�����) ������� � ������� ���������
   * @param   float    $h           ���������� ������ ������ ������������ ������� � ��������
   * @param   float    $K_h         ���������� �������� � ������� ���������� ��� ������ ������������
   *                                (���������� �� - ������������ ������ ������� / ������������ ���-�� ������������� �� ������)
   * @param   float    $by_a        ���������� ������������� (����� ���� float, �������� ��� �������� ��������)
   * @param   integer  $colspan     html �������� colspan ��� td (���� 0, �� �� ������������)
   * @param   boolean  $with_nums   �������� ��, ����� �������, ��� � �������� ��������?
   * @param   string   $cls         �������� ��� ����� css ������ � td
   */
  function noProCol(&$h, $K_h, $by_a, $colspan=NULL, $with_nums=false, $cls=NULL)
  {
    $h = ceil($by_a * $K_h);
    ob_start();
  ?>
    <td<?=($colspan&&$colspan>1 ? ' colspan="'.$colspan.'"' : '')?> class="lgray-bg z-s<?=($cls?' '.$cls:'')?>" style="width:2px;" width="2"><?
      if($with_nums && $by_a) { ?><div style="padding-bottom:3px;" class="sml-s"><?=$by_a?></div><? } 
      if($h) { ?><div class="lgray-col" style="height:<?=$h?>px">&nbsp;</div><? } else print('<div></div>');
    ?></td>
  <?
    return ob_get_clean();
  }


  /**
   * ������� html ���� � ����������� ���������� ����������� �� ��������� ���� + ���������� �� ������� ���� �� ���� ����
   * @param   array    $guests   ������ � ���������� �� ����. ��������� ��. promotion::GetGuests()
   * @param   integer  $last_h   �� ������ ���� �������� ��������� ����������
   * @param   string   $title    ���������, ����������� �� ����� ���� �������� ����������
   * @return  string             HTML
   */
  function guests($guests,$last_h,$title)
  {
    ob_start();

  ?>
    <table border="0" cellspacing="1" cellpadding="0" style="table-layout:fixed">
      <col style="width:70px"/>
      <col style="width:100px"/>
      <col style="width:70px"/>
      <? for($i=0;$i<24;$i++) { ?><col style="width:25px"/><? } ?>
      <tr style="vertical-align:bottom">
        <td>&nbsp;</td>
        <td style="vertical-align:bottom;">
          <div class="green-c big-by_e" title="���������� �� ����">
            <?=(int)$guests['by_e_uniq']?>
          </div>
        </td>
        <td class="green-c" style="padding-bottom:7px; vertical-align:bottom;">���������</td>
        <? for($i=0;$i<=$last_h;$i++) { ?>
          <td style="text-align:center; vertical-align:bottom;">
            <? 
              if($gg=$guests[$i]['employers']) {
                foreach($gg as $g)
                  print(self::avatar($g));
              }
              else
                print('&nbsp;');
            ?>
          </td>
        <? } ?>
      </tr>
      <tr>
        <td><div class="hbig-s"><?=$title?></div>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <? for($i=0;$i<=$last_h;$i++) { ?><td class="sml-s lgray-c" style="padding:5px 0 5px 0; text-align:center"><?=$i?>:00<img src="/images/1.gif" width="25" height="1" style="display:block; height:1px; width:25px;"></td><? } ?>
      </tr>
      <tr style="vertical-align:top">
        <td>&nbsp;</td>
        <td>
          <div class="orange-c big-by_f" title="���������� �� ����">
            <?=(int)$guests['by_f_uniq']?>
          </div>
          <? if($guests['by_u_uniq']) { ?>
            <div  style="position:absolute;width:150px;padding:5px 0 0 15px; text-align:left" class="lgray-c">
              � ��� <span title="���������� �� ����"><?=$guests['by_u_uniq']?></span> ������������������
            </div>
          <? } ?>
        </td>
        <td class="orange-c" style="padding-top:3px">����������</td>
        <? for($i=0;$i<=$last_h;$i++) { ?>
          <td style="text-align:center">
            <? 
              if($gg=$guests[$i]['freelancers']) {
                foreach($gg as $g)
                  print(self::avatar($g));
              }
              else
                print('&nbsp;');
            ?>
          </td>
        <? } ?>
      </tr>
    </table>
  <?
    return ob_get_clean();
  }

  /**
   * ���������� ������ ������������
   * @param    array   $user   ������ � ������� � ������������
   * @return                   HTML
   */
  function avatar($user)
  {
    ob_start();
    $fromStr = '';
    if($user['from_b']=='t') $fromStr = '�� ������ - ';
    if($user['from_c']=='t') $fromStr = '�� �������� - ';
    if($user['from_p']=='t' || $user['from_t']=='t') $fromStr = '� ������� ���� - ';
    if($user['from_o']=='t') $fromStr = '�� ����� ����������� - ';
    if($user['from_s']=='t') $fromStr = '�� ������ - ';
    if($fromStr=='') $fromStr = '������ - ';
  ?>
    <div style="height:26px;width:25px">
      <a href="/users/<?=$user['guest_login']?>" title="<?=$fromStr.$user['guest_uname']?> <?=$user['guest_usurname']?> <?= '['.$user['guest_login'].']';?>">
        <? $pht = $user['guest_photo'] ? WDCPREFIX."/users/{$user['guest_login']}/foto/{$user['guest_photo']}" : "/images/no_foto_25.png"; ?>
        <img src="<?=$pht?>" alt="<?=($user['guest_uname'].' '.$user['guest_usurname'].' ['.$user['guest_login'].']')?>" width="25" height="25" style="border:0" />
      </a>
    </div>
  <?
    return ob_get_clean();
  }

  /**
   * ������� html ���� ��� ������� �������� ����� � ��������
   * @param    integer   $id     id ������� ��������
   * @param    string    $name   �������� �������
   * @param    float     $price  ���� ���������� � �������
   * @return   string            HTML
   */
  function selBox($id=NULL,$name=NULL,$price=NULL)
  {
    $js = ($id===NULL);
    $id   = !$js ? $id : "'+p.id+'";
    $name = !$js ? $name : "'+p.n+'";
    $price = !$js ? $price : "'+p.prc+'";
    ob_start();
  ?>
    <div class="sel-box" id="sb<?=$id?>">
      <div style="text-align:right;margin-bottom:3px;font-size:0px">
        <a href="javascript:ctgdp(<?=$id?>)">
          &nbsp;<img style="position:absolute;margin-left:-11px;" width="11" height="11" src="/images/ico_close_fp.gif" />
        </a>
      </div>
      <div class="black-c big-s">
        <?=$name?>
      </div>
      <div class="mid-s black-c" style="padding:12px 0 10px 0">
        <b>���������� ������:&nbsp;
        <input type="text" onmousewheel="cibywheel(this,1,99999);ctgcp(this,<?=$id?>)" name="weeks[<?=$id?>]" style="width:30px;text-align:right" value="1" onblur="ctgcp(this,<?=$id?>)" onkeyup="ctgcp(this,<?=$id?>)" maxlength="5"/>
        &nbsp;&ndash; <span id="ps<?=$id?>"><?=$price?></span> ���.</b>
      </div>
    </div>
  <?
    $str = ob_get_clean();
    if($js)
      $str = "'".preg_replace('/\s+/', ' ', $str)."'";
    return $str;
  }


  /**
   * ������� ������ ������
   * @param   float    $acc_sum    FM, ������� ���� � ������������
   * @param   float    $need_sum   FM, ����������� ��� �������
   * @param   string   $id         id ��� input � �������
   * @param   string   $src        �������� ��� ������
   * @return  string               HTML
   */
  function buyButton($acc_sum, $need_sum, $id, $src='/images/btn_buy2.jpg')
  {
    ob_start();
  ?>    
    <div style="padding-top:20px; width:200px">
      <button id="<?=$id?>" class="b-button b-button_flat b-button_flat_green" type="submit">������ ������</button>
    </div>
  <?
    return ob_get_clean();
  }

  /**
   * ������������ ������ � �������� postgresql � ������ php
   * @param   string   $pg_arr   ������ � �������� postgresql
   * @return  array              ���������������� ������ php
   */
  function pg2php_arr($pg_arr) { return explode(',',preg_replace('/[}{]/', '', $pg_arr)); }
}
?>
