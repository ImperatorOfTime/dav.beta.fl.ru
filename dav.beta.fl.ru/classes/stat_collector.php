<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");


/**
 * ����� ��� �������� � ��������� ��������� ������� �������������.
 */
class stat_collector
{
  /**
   * ������������ ���������� ����, ��������� ���������� � ������� ���������� ���������.
   * ������������ ��� ������� stat_daily
   */
  const MAX_CYCLICS_OFFSET = 30;

    
  /**
   * ��� ��������� � ����. ������.
   */
  const LT_ERROR     = 1;

  /**
   * ��� ��������� � ����. ��������������.
   */
  const LT_WARNING   = 2;

  /**
   * ��� ��������� � ����. ���������.
   */
  const LT_NOTICE    = 3;

  /**
   * ��� ��������� � ����. �������� ���.
   */
  const LT_CODE      = 4;

  /**
   * ��� ��������� � ����. ������ ������ ��������.
   */
  const LT_HEADER    = 5;
  /**
   * ��� ��������� � ����. ������ �����-���� ��������.
   */
  const LT_SUBHEADER = 6;

  /**
   * ��� ��������� � ����. ��������� ��������.
   */
  const LT_RESULT    = 7;

  /**
   * ��� ��������� � ����. ��������� �������.
   */
  const LT_SERVER_MESSAGE = 8;

  
  /**
   * ������ �����. ����������� ������.
   */
  const REFID_UNKNOWN      = 0;

  /**
   * ������ �����. �����.
   */
  const REFID_BLOGS        = 1;

  /**
   * ������ �����. ��������.
   */
  const REFID_CATALOG      = 2;

  /**
   * ������ �����. ����������.
   */
  const REFID_COMMUNE      = 3;

  /**
   * ������ �����. ������� �����.
   */
  const REFID_PAIDSEATINGS = 4;

  /**
   * ������ �����. �������� �������������.
   */
  const REFID_USERS        = 5;
  /**
   * ������ �����. ������� ����� ������� ��������.
   */
  const REFID_PAYPLACE     = 6;
  /**
   * ����� ����������� �����������
   *
   */
  const REFID_FRL_OFFERS = 7; 
  
  /**
   * ����� 
   *
   */
  const REFID_SEARCH = 8;
    
  const LOGTBL_MEM_KEY     = 'stat_collector.LOGTBL_MEM_KEY';
  
  
  /**
   * �������� ������� ��� ������ ����������� ������� ������
   * @var  string
   */
  private $root    = '';

  /**
   * ������� ��� ���������� ����� � ������. 
   * @var  string
   */
  private $log_dir = '';

  /**
   * ������� ��� �������� ��������� ������. 
   * @var  string
   */
  private $tmp_dir = '';

  /**
   * ������� ��� ������ �� ������� stat_log
   * @var  string
   */
  private $arc_dir = '';

  /**
   * ��� ����
   * @var  string
   */
  private $run_log = '';

  /**
   * ���� TRUE, �� ������� ��� �� �����, ������ $this->run_log
   * @var  boolean
   */
  private $output  = FALSE;

  /**
   * ������ ���������� � ��
   * @var  resource
   */
  private $connect = NULL;

  /**
   * ������ ��� �������� ����
   * @var  string
   */
  private $log_str    = '';

  /**
   * ��������� � �� ����������.
   * @var object
   */
  private $_sDB;

  /**
   * �����������. ���������� ���� ��� ���� ����������� ���������.
   */
  function __construct($output = FALSE)
  {
    $this->_sDB = new DB('stat');
    $this->output  = $output;
    $this->root    = preg_replace('/\/$/','',$_SERVER['DOCUMENT_ROOT']).'/stat_collector';
    $this->log_dir = $this->root.'/logs';
    $this->tmp_dir = $this->log_dir.'/tmp';
    $this->arc_dir = $this->log_dir.'/arc';
    $this->run_log = $this->log_dir.'/run.log';
  }

  /**
   * ���������� ������� ����� ���� ������ � unixtime
   * @return   float   ������� ����� � unixtime
   */
  private function get_time()
  {
    if ( $res = $this->_sDB->val('SELECT EXTRACT(EPOCH FROM now())') )
        return $res;
    
    return time();
  }

  /**
   * ��������� ������ � ������� � ���
   * @param   string    $msg        �������� �������
   * @param   integer   $log_type   ��� �������
   * @return  string                ���������� ���������� ������� ��������� ���� ��������� LT_ERROR ��� LT_SERVER_MESSAGE, ����� NULL
   */
  private function log($msg, $log_type = self::LT_NOTICE)
  {
    
    $e = NULL;
    $m = date('Y-m-d H:i:s').' ';
    switch($log_type)
    {
      case self::LT_ERROR :
        $m .= '������! '.$msg."\n";
        $e = $msg;
        break;
      case self::LT_WARNING :
        $m .= '��������! '.$msg."\n";
        break;
      case self::LT_NOTICE :
        $m .= $msg."\n";
        break;
      case self::LT_CODE :
        $m .= $msg."\n";
        break;
      case self::LT_HEADER :
        $m = "\n".$m.strtoupper($msg)."\n\n";
        break;
      case self::LT_SUBHEADER :
        $m .= $msg."\n";
        break;
      case self::LT_RESULT :
        $m .= $msg."\n";
        break;
      case self::LT_SERVER_MESSAGE :
        if($msg) $m .= '�������� ��������� �� �������: '.$msg."\n";
        else
          return $e;
        break;
      default :
        $m .= $msg."\n";
        break;
    }

    $this->log_str .= $m;
    return $e;
  }


    /**
     * ������������� ������� ��� ������ ���������.
     * @see stat_collector::Step1()
     *
     * @param string $table       ��� ������� (stat_log|stat_log_t)
     * @param boolean $only_mem   true: ��������� ������ � ������, ����� ��� � � ��.
     * @return boolean   �������?
     */
     private function _setLogTable($table, $only_mem = false) {
        $MEM = new memBuff();
        if ( $only_mem || $this->_sDB->update('stat_variables', array('value'=>$table), 'name = ?', 'log_table') ) {
            return $MEM->set(stat_collector::LOGTBL_MEM_KEY, $table, 3000);
        }
        return false;
    }

    /**
     * �������� ������� ������� ��� ������ ���������.
     * @see stat_collector::LogStat()
     * @return string   stat_log|stat_log_t
     */
    function getLogTable() {
        $MEM = new memBuff();
        if ( !($table = $MEM->get(stat_collector::LOGTBL_MEM_KEY)) ) {
            $table = $this->_sDB->val('SELECT value FROM stat_variables WHERE name = ?', 'log_table');
            $this->_setLogTable($table, true);
        }
        if ( !$table ) {
            $table = 'stat_log';
        }
        return $table;
    }

    /**
     * ��������� ��������� �� ���������� � �������.
     * ONLYLOG ��������������� ������ � ['pg_db']['stat_tmp'] � ������ ������ � �.�., �����
     * ��������� ������ ���� � ��������, �� ���������� �� ������� �����.
     * 
     * @return integer   0:�� ���������, 1:���������� ������, 2:�������� ������ ��� �����.
     */ 
    function isDisabled() {
        if(defined('STAT_DISABLED') && STAT_DISABLED) {
            if(STAT_DISABLED == 'ONLYLOG' && isset($GLOBALS['pg_db']['stat_tmp'])) {
                $this->_sDB = new DB('stat_tmp');
                return 2;
            }
            return 1;
        }
        return 0;
    }

    /**
     * ��������� ������ � ��������� ���������������� ��������.
     * �������������, ������������ ����� ���-�� ��������� � ������� ���� �� �������, ���� ������� ��� ������ - 
     * � ������� ����� � ������� "��� ����������", � �������, ������� � ���������� ����� �� ������� ��������.
     * @param   integer   $user_id    uid ������������, �������� �������� �������������
     * @param   integer   $guest_id   uid ������������, ���������� ��������
     * @param   string    $guest_ip   IP ����������
     * @param   integer   $referer_id �����, � �������� ������� ��������� (������ �������� REFID_*)
     * @param   integer   $by_e       1 - ���� ������������� ������������, 0 - ���� ���-�� ������
     * @return  string                ��������� �� ������ ��� 0, ���� ��� ������ �������
     */
    function LogStat($user_id, $guest_id, $guest_ip, $referer_id, $by_e, $stamp = false) {
        if($this->isDisabled() == 1) {
            return false;
        }
        $DB = $this->_sDB;
        
        if(self::checkStamp($stamp, true)) $referer_id = 0; //return false; // ��������� ������������ �� ���������� �� ����� �� ������� � ����������
        if((int)$guest_id < 0)
            $guest_id = 0; // ������ ���� �����...
        $aSQLdata = compact( 'user_id', 'guest_id', 'guest_ip', 'referer_id', 'by_e' );
        $log_table = $this->getLogTable();
                
        if( !$DB->insert($log_table, $aSQLdata) )
            return $this->log("stat_collector::LogStat(). ������ ������ � {$log_table}. " . $DB->error, self::LT_ERROR);

        return 0;
    }

    /**
     * ��������� ������ ��� ���������� ��������� �������� ������������ �� �������� ������.
     *
     * @param int $user_id UID ������� ��������
     * @param int $guest_id UID ����� ��� ����, ���� ����� �������������
     * @param string $guest_ip IP ����� �����
     * @param bool $is_emp ��� �� ����� �������������
     * @param string $words ������ �������� ����, ����������� �������� (���� ���������)
     */
    function wordsStatLog( $user_id, $guest_id, $guest_ip, $is_emp, $words ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/kwords.php' );
        
        if ( $keywords = kwords::getKeys(stripslashes(urldecode($words))) ) {
            foreach ( $keywords as $k => $v ) {
                $aWords[] = $v['id']; 
            }
        }
        else {
            return false;
        }
        
        global $DB;
        $sQuery = "SELECT wid FROM portf_word WHERE uid=? AND wid IN (?l)";
        $aRows  = $DB->rows( $sQuery, $user_id, $aWords );
        
        if ( $DB->error ) {
        	return false;
        }
        
        if ( $aRows ) {
        	$aData = array();
        	
        	foreach ($aRows as $aOne) {
        		$aData[] = array(
                    'user_id'  => $user_id,
                    'word_id'  => $aOne['wid'],
                    'guest_id' => $guest_id,
                    'guest_ip' => $guest_ip,
                    'is_emp'   => $is_emp
        		);
        	}
        	
        	if( !$DB->insert('stat_word_log', $aData) ) {
        	    return false;
        	}
        }
        
        return true;
    }

  /**
   * ����������� ������ ���� ����� ������� ��� �������� ���������� (������: Step1() - Step6())
   */
  function Run()
  {
    if($this->isDisabled() == 1) {
        $this->log('������ �������� ��������.', self::LT_HEADER);
        return;
    }
    
    $this->log('������ stat_collector::Run()', self::LT_HEADER);

    ob_start();

    $this->Step1();
    $this->log(ob_get_contents(), self::LT_SERVER_MESSAGE);
    ob_clean();

    $this->Step2();
    $this->log(ob_get_contents(), self::LT_SERVER_MESSAGE);
    ob_clean();

    $this->Step3();
    $this->log(ob_get_contents(), self::LT_SERVER_MESSAGE);
    ob_clean();

    $this->Step4();
    $this->log(ob_get_contents(), self::LT_SERVER_MESSAGE);
    ob_clean();

    $this->Step5();
    $this->log(ob_get_contents(), self::LT_SERVER_MESSAGE);
    ob_clean();

    $this->Step6();
    $this->log(ob_get_contents(), self::LT_SERVER_MESSAGE);

    ob_end_clean();

    $this->log('���������� stat_collector::Run()', self::LT_HEADER);
  }


  /**
  * ������� ��������� ����������.
  * ����� ������ �� stat_log �� ���������� ���� � ���������� �� � ����������� �� ������ ����, uid ���������� � uid ���� ��������.
  * �.�. ����� ���-�� ���������� ���-���� (guest_id) � ������� ���� ����-���� (user_id) ��������� ����� ����������.
  * ��������������� ������ ����������� � stat_hourly, � ������ ���� ���������� �������� � ���� � ���������� $this->arc
  * @return   string   ��������� �� ������ ��� 0, ���� ��� ������ �������
  */
  function Step1()
  {
    // ���������� ������ �� stat_log � stat_hourly. ��������.
    // �������� ��� ($arc_name) �� �������. ����� ����� �����, �� �������. ����� ��� �������������, ����� ����� �� �������� � ���.
    // ��� ������� ��� ������.
    
    $DB = $this->_sDB;

    $this->log('stat_collector::Step1(). ������� ������ �� stat_log � stat_hourly.', self::LT_SUBHEADER);
    
    $time  = $this->get_time();
    $curH  = date('Y-m-d H', $time).':00:00';
    $curTH = strtotime($curH);

    // 1. ������� ������ "ix stat_log_t/_time" ��� ���������� ����������� ���������� ������ � stat_log_t.
    // 2. �������������� ������ �� stat_log_t.
    // 3. ��������� ��� ������ � stat_log_t.
    // 4. TRUNCATE stat_log.
    // 5. ���������� ������� �� stat_log.
    // 6. ������� ������ "ix stat_log_t/_time" �����, ����� ������������ ��� � ��������.
    // 7. ������������ �� ������ ���� stat_log_t.
    // 8. � ����� ������ VACUUM FULL stat_log_t.
    // ����� ������� ��� ��������� ����� �������������� �� ������� stat_log_t. � ������ ������� ������ ������ �������,
    // stat_log_t ����� ��������� ������ �� �������� ���������� ��� (_time >= $curH � ���������� ������), ������� ����� �����������
    // ������ �������, ������������� � stat_log .

    $this->log('������� ������ "ix stat_log_t/_time".', self::LT_NOTICE);
    $sql = 'DROP INDEX IF EXISTS "ix stat_log_t/_time"';
    if ( !$this->_sDB->squery($sql) )
      $this->log('������. '.$this->_sDB->error, self::LT_WARNING);

    $this->log('�������������� ������� �� stat_log_t.', self::LT_NOTICE);
    if( !$this->_setLogTable('stat_log_t') )
      return $this->log('������. ', self::LT_ERROR);

    $this->log('��.', self::LT_NOTICE);
    $this->log('���� 3 ������� ��� ���������� ������ ����������...', self::LT_NOTICE);
    sleep(3);

    $truncateErr = NULL;
    $this->log('��������� ��� ������ �� stat_log � stat_log_t, TRUNCATE ONLY stat_log.', self::LT_NOTICE);
    $sql = 'INSERT INTO stat_log_t SELECT * FROM ONLY stat_log';
    if ( !$DB->squery($sql) )
      $truncateErr = $this->log('������. '.$DB->error, self::LT_ERROR);
    else {
      $sql = 'TRUNCATE ONLY stat_log';
      if ( !$DB->squery($sql) )
        $truncateErr = $this->log('������. '.$DB->error, self::LT_ERROR);
    }

    $this->log('���������� ������� �� stat_log.', self::LT_NOTICE);
    if( !$this->_setLogTable('stat_log') )
      return $this->log('������. ', self::LT_ERROR);

    if($truncateErr)
      return $truncateErr;

    $this->log('��������������� ������ "ix stat_log_t/_time".', self::LT_NOTICE);
    $sql = 'CREATE INDEX CONCURRENTLY "ix stat_log_t/_time" ON stat_log_t USING btree (_time)';
    if ( !$DB->squery($sql) )
      $this->log('������. '.$DB->error, self::LT_WARNING);


    $lT = $DB->val( "SELECT _time FROM stat_log_t ORDER BY _time LIMIT 1" );
    
    if ( $DB->error )
      return $this->log('������ ������ stat_log_t. '.$DB->error, self::LT_ERROR);

    if( !$lT )
      return $this->log('������ ���.', self::LT_NOTICE);

    $tH = strtotime(date('Y-m-d H', strtotime($lT)).':00:00');
    if($tH < $curTH)
      $tH += 3600;


    // ������������ �� ������ ����.

    for($tH; $tH <= $curTH; $tH += 3600)
    {
      $H = date('Y-m-d H', $tH).':00:00';
      // � ����� $arc_name �������� ������� H, �.�. � ������ ����� ��-�� ������ ����� ����� �������������� ���� � ��� �� ���,
      // ��� ��������� � ��������� ����������� ������.
      $arc_name = $this->arc_dir.'/'.date('YmdH', $tH).'-'.date('H').'.log';

      $this->log("��������� ������: FROM stat_log_t WHERE _time < '{$H}'.", self::LT_NOTICE);

      // (�) ���������, ���� �� ������ � stat_log_t, ������� ����� ��������������.

      $sql = "SELECT 1 FROM stat_log_t WHERE _time < ? LIMIT 1";
      if ( !($res = $DB->query($sql, $H)) )
        return $this->log('������ ������ stat_log_t. '.$DB->error, self::LT_ERROR);

      if(!pg_num_rows($res)) {
        $this->log("������ ���.", self::LT_NOTICE);
        continue;
      }
    

      // (�) ����� ������ �� stat_log_t �� ��� "������ ����" (���, ����� �������� ����) � ����������� �� �� ��������� ������� � � ��������� �����.
      //     ������ ������������� � ������ ����, ��� �������������� � ������������� (���� �� �������������).
      //     � ������ ������ ���������� ��������.

      if ( !$DB->start() )
        return $this->log('�� ������� ������� ����������. '.$DB->error, self::LT_ERROR);

      $sql = "SELECT * INTO TEMPORARY TABLE ___tmp_arc FROM stat_log_t WHERE _time < ?";
      if ( !($res = $DB->query($sql, $H)) ) {
        $e = $DB->error;
        $DB->rollback();
        return $this->log('������ ������� � ___tmp_arc. '.$e, self::LT_ERROR);
      }

      $all_data = pg_copy_to($DB->connect(), '___tmp_arc');
      if(!file_put_contents($arc_name, $all_data))
        $this->log("��� {$arc_name} �� ���������.", self::LT_WARNING);

      unset($all_data);
      

      // ���������� ��� ������ � ������ ��������� ������.
      if ( !$DB->query('SAVEPOINT arc_created') ) {
        $e = $DB->error;
        $DB->rollback();
        return $this->log('�� ������� ������� SAVEPOINT. '.$e, self::LT_ERROR);
      }

      // (�) ����� ������ �� stat_log_t �� ��� �� ������, ��� � � (�), �� ���������� �� ����������� �������, ���, ����� �� ���� ������ "�������".
      // (�) ���� ������ �������� � ����� � ���������� ������ (��� ���������� ����� ��������,
      //     �� ����, ������ �� �� ������� �������� ��������� ������, � ����� ��������� ������� ������� ��������� ������
      //     "�� ���������"), �� ������� ����. ����� ������� ���� � ���������� ��������.
      //     ������ ��� ���������� ������ ����������� �������, ������ ����� .tmp ������. ���� �� �� �������� �
      //     stat_hourly �� ������� ���� ������-������, �� ���������� ��������. ��� ����� �������� ������������� ������,
      //     ������� ���������� ��� ����������� ��������� (��������) � stat_summary.

      $grp_data_sql = 
      "(
         SELECT user_id,
                guest_id,
                CASE WHEN guest_id = 0 THEN guest_ip ELSE '' END as guest_ip,
                by_e,
                MAX(_time) as _time,
                MAX((referer_id=".self::REFID_BLOGS.")::int)::bool as from_b,
                MAX((referer_id=".self::REFID_CATALOG.")::int)::bool as from_c,
                MAX((referer_id=".self::REFID_PAIDSEATINGS.")::int)::bool as from_p,
				MAX((referer_id=".self::REFID_PAYPLACE.")::int)::bool as from_t,
				MAX((referer_id=".self::REFID_FRL_OFFERS.")::int)::bool as from_o,
				MAX((referer_id=".self::REFID_SEARCH.")::int)::bool as from_s
           FROM ___tmp_arc
          GROUP BY 
                user_id,
                guest_id,
                CASE WHEN guest_id = 0 THEN guest_ip ELSE '' END,
                by_e,
                DATE_TRUNC('hour', _time)
       )";

      $sql = "INSERT INTO stat_hourly (user_id, guest_id, guest_ip, by_e, _time, from_b, from_c, from_p, from_t, from_o, from_s) SELECT * FROM {$grp_data_sql} t";

      if( !($res = $DB->squery($sql)) )
      {
        $e = $DB->error;
        $DB->squery( "ROLLBACK TO SAVEPOINT arc_created" ); // ������������ � "����� �������� ��. �������"
        $this->log('������ ��� ������� � stat_hourly. �������� �������� ������, ������������, �������� ������. '.$e, self::LT_WARNING);
        $sql = 
        "INSERT INTO stat_hourly (user_id, guest_id, guest_ip, by_e, _time, from_b, from_c, from_p, from_t, from_o, from_s)
         SELECT t.*
           FROM {$grp_data_sql} t
         LEFT JOIN
           stat_hourly h
             ON h.user_id  = t.user_id
            AND h.guest_id = t.guest_id
            AND h.guest_ip = t.guest_ip
            AND h._time    = t._time
            
-- !!! ����� AND DATE_TRUNC('hour', h._time) = DATE_TRUNC('hour', t._time)
-- !!! � ������ ���������� (user_id, guest_id, guest_ip, DATE_TRUNC('hour', _time))
            
          WHERE h.user_id IS NULL";

        if ( !($res = $DB->squery($sql)) ) {
          $e = $DB->error;
          $DB->rollback(); // ������������ �� ������.
          return $this->log('������ �� � ��������� ������. '.$e, self::LT_ERROR);
        }
      }

      $this->log('�������� '.pg_affected_rows($res).' �����.', self::LT_NOTICE);
      pg_free_result($res);

      // ��������� ��� ��� ����.
      if ( !$DB->commit() ) {
        $e = $DB->error;
        $DB->rollback();
        return $this->log('�� ������� ������������� ����������. '.$e, self::LT_ERROR);
      }

      $DB->squery( 'DROP TABLE IF EXISTS ___tmp_arc' );

      // (�) ������� ��������������� ������ �� stat_log_t. ��� ��� �� �����, �.�. ��� ��� ���� � stat_hourly.
      //     ���� ������ �� ���������, �� � ��������� ��� ��������� �������� �� ��������� � ������ (�), ��� ��� ��� ������
      //     ���� ����.

      $DB->start();
      
      $sql = "DELETE FROM stat_log_t WHERE _time < ?";
      if( !($res = $DB->query($sql, $H)) || !$DB->commit() )
      {
        $e = $DB->error;
        $DB->rollback();
        $this->log('������ ��� �������� �� stat_log_t. '.$e, self::LT_WARNING);
      }
      else
        pg_free_result($res);


      $this->log('��.', self::LT_NOTICE);
    }

    $this->log('��������� stat_log_t.', self::LT_NOTICE);

    if ( !$DB->squery('VACUUM FULL stat_log_t') )
      $this->log('VACUUM FULL stat_log_t �� ��������. '.$DB->error, self::LT_WARNING);

    return 0;
  }


  /**
   * ���������� �������� ���������� ���������� ��������� ���������������� �������, 
   * � ����� ���������� ��������� �� ��� � ���������, ������ � ������� ����.
   * ������� ���� � ������� ����������� �������, ��������� ����������� � stat_summary.
   * @return   string   ��������� �� ������ ��� 0, ���� ��� ����������� ������
   */
  function Step2()
  {
    // ���������� �������� �������� � stat_summary.
    // ��� ������ � ����� ����������.
    // ������� �����, �� ������� ����� ����� ������ �� stat_hourly, ����� ��������� ��������.
    // ����������� ���� ss_totals_last �� ������� stat_variables (lTC). ��� ����� ���� NULL, �� ���� ��� �������� �� ����������.
    // ��������� ��� ������ �� ������� stat_hourly, ��� _time > lTC, �� ����, ������ ��, ������� �� ��� �� ����� � ������,
    // ���������� � ����������� �� �������������.
    // ���������� �����, ����������� ������������ � ����������, ������ �� ������� stat_hourly � stat_variables.ss_totals_last.
    // ���� ��������� ������, �� ������������.
    
    $this->log('stat_collector::Step2(). ���������� �������� �������� � stat_summary.', self::LT_SUBHEADER);
    
    $DB = $this->_sDB;
    
    $DB->start();
    
    $sql = 
    "INSERT INTO stat_summary (user_id)
     SELECT h.user_id
       FROM (SELECT DISTINCT user_id
               FROM stat_hourly
              WHERE _time > COALESCE((SELECT value::timestamp without time zone FROM stat_variables WHERE name = 'ss_totals_last'), '1970-01-01')) as h
     LEFT JOIN
       stat_summary s
         ON s.user_id = h.user_id
      WHERE s.user_id IS NULL";

    if ( !$DB->squery($sql) || !$DB->commit() )
    {
      $e = $DB->error;
      $DB->rollback();
      return $this->log('���������� �������� ���������. ������ �� ��������������� �����. '.$e, self::LT_ERROR);
    }

    $DB->start();
    
    $sql = 
    "UPDATE stat_summary as s
        SET
            from_b = s.from_b + h.from_b,
            from_c = s.from_c + h.from_c,
            from_p = s.from_p + h.from_p,
			from_t = s.from_t + h.from_t,
            from_a = s.from_a + h.from_a,
            from_o = s.from_o + h.from_o,
            from_s = s.from_s + h.from_s
       FROM
       (
        SELECT user_id,
               SUM(from_b::int) as from_b,
               SUM(from_c::int) as from_c,
               SUM(from_p::int) as from_p,
			   SUM(from_t::int) as from_t,
			   SUM(from_o::int) as from_o,
			   SUM(from_s::int) as from_s,
               COUNT(*) as from_a
          FROM stat_hourly
         WHERE _time > COALESCE((SELECT value::timestamp without time zone FROM stat_variables WHERE name = 'ss_totals_last'), '1970-01-01')
         GROUP BY user_id
       ) as h
      WHERE s.user_id = h.user_id;

     UPDATE stat_variables SET value = (SELECT _time FROM stat_hourly ORDER BY _time DESC LIMIT 1) WHERE name = 'ss_totals_last'";

    if ( !$DB->squery($sql) || !$DB->commit() )
    {
      $e = $DB->error;
      $DB->rollback();
      return $this->log('������ ��� ���������� �������� ��������� � stat_summary. '.$e, self::LT_ERROR);
    }
    
    return 0;
  }


  /**
   * ������������ ����� ���������� ��������� �� ���������� ���� (��� ��������� ����, ���� ���������� �� ���� ���� �� ������).
   * ������ ������������ �� ���� ������ ������ (�����, ��������, ������� �����) � ��� ������� (���������, ������������ ��� ������).
   * ������������� ������� ��������� ������������ (����������� ���������� ��������� �� ��������� �� ���� ������), � ����� ��������� Step3a().
   * @return    string    ��������� �� ������ ��� 0, ���� ��� ������ �������
   */
  function Step3()
  {
    // ������������ ������ �� stat_hourly � stat_daily.
    // ������� ��������� ����, ����� ��� ���������� � stat_daily (mD). ������ -- �������.
    // ����� ������ �� stat_hourly, ��� _time < D (��� ������ ���) � _time >= mD + 1 (mD + 1 = nD: ���� ���� � stat_daily ��� ���).
    // ���������� � ���������� ������ � � stat_daily. ������ -- �������.
    
    $this->log('stat_collector::Step3(). ������������ ������ �� stat_hourly � stat_daily.', self::LT_SUBHEADER);
    
    $DB = $this->_sDB;
    
    $time = $this->get_time();
    $D = date('Y-m-d', $time);
    
    $sql = "SELECT _date + 1 FROM stat_daily ORDER BY _date DESC LIMIT 1";
    $nD  = $DB->val( $sql );
    
    if ( $DB->error )
      return $this->log('������ ��� ������ ������ �� stat_daily. '.$DB->error, self::LT_ERROR);

    if ( !$nD )
      $nD = '1970-01-01';
    
    $DB->start();
    
    $sql = 
    "INSERT INTO stat_daily
     (
       user_id,
       _date,
       by_f_from_b,
       by_e_from_b,
       by_u_from_b,
       by_f_from_c,
       by_e_from_c,
       by_u_from_c,
       by_f_from_p,
       by_e_from_p,
       by_u_from_p,
       by_f_from_t,
       by_e_from_t,
       by_u_from_t,
       by_f_from_o,
       by_e_from_o,
       by_u_from_o,
       by_f_from_s,
       by_e_from_s,
       by_u_from_s,
       by_f,
       by_e,
       by_u
     )
     SELECT 
            user_id,
            _time::date,
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
      WHERE _time >= ? AND _time < ? 
      GROUP BY user_id, _time::date";

    if ( !($res = $DB->query($sql, $nD, $D)) || !$DB->commit() )
    {
      $e = $DB->error;
      $DB->rollback();
      return $this->log('������ ��� ������� ������ � ������� stat_daily. '.$e, self::LT_ERROR);
    }

    $daily_affected = pg_affected_rows($res);
    $this->log("�������� {$daily_affected} �����.", self::LT_NOTICE);

    // ������� ������ �� stat_hourly ��� _time < D - 1 (��������� ������ ������� � �����). ������, �� � ���� � ���, �����
    // ��������.

    $sql = "DELETE FROM stat_hourly WHERE _time < ?::date - 1";
    if ( !$DB->query($sql, $D) )
      $this->log('������ ��� �������� ������ �� ������� stat_hourly. '.$DB->error, self::LT_WARNING);

    // ��� � ���� ��������� ��������� ������������ (���� ���� ������ � ��������� ������������).
    // ����, ��������, ������ �� ������� ����� ����, �� ��������� � ���� ���� �� ���� (����� ���� �.�. ����).
    // ��������� ������� ����... ���������. ��� �������� � �������� �����-���� ����������� ����� ������ ��������.
    // ������, ���� �� ��� ������ �� �������� �������...
    // �� ����� ������ ������ ���������, ����� ������� ������� �� ������ � ������� ��������.
    if($daily_affected) // ����� ���� ���������� ���-���� �������. ��������, ��� ��������� � 3 ���� ���� ���������.
    {
        $DB->start();
        
      $sql =
      "CREATE TEMPORARY TABLE ___tmp_dp
       (
         week_day smallint,
         by_e_from_c decimal,
         by_f_from_c decimal,
         by_u_from_c decimal
       );
       INSERT INTO ___tmp_dp
       SELECT EXTRACT(DOW FROM _date),
              SUM(by_e_from_c),
              SUM(by_f_from_c),
              SUM(by_u_from_c)
         FROM stat_daily
        WHERE _date < CURRENT_DATE AND _date >= CURRENT_DATE - 7
        GROUP BY EXTRACT(DOW FROM _date);

       UPDATE stat_week_coeffs wc
          SET by_e_from_c = (wc.by_e_from_c + t.by_e_from_c) / 2, -- ����������, �� ��������...
              by_f_from_c = (wc.by_f_from_c + t.by_f_from_c) / 2,
              by_u_from_c = (wc.by_u_from_c + t.by_u_from_c) / 2
         FROM 
         (
           SELECT s.week_day,
                  CASE WHEN a.by_e_from_c = 0 THEN 0 ELSE s.by_e_from_c / a.by_e_from_c END as by_e_from_c,
                  CASE WHEN a.by_f_from_c = 0 THEN 0 ELSE s.by_f_from_c / a.by_f_from_c END as by_f_from_c,
                  CASE WHEN a.by_u_from_c = 0 THEN 0 ELSE s.by_u_from_c / a.by_u_from_c END as by_u_from_c
             FROM ___tmp_dp s
           CROSS JOIN
             (
               SELECT COUNT(*) as count,
                      SUM(by_e_from_c) as by_e_from_c,
                      SUM(by_f_from_c) as by_f_from_c,
                      SUM(by_u_from_c) as by_u_from_c
                 FROM ___tmp_dp
             ) as a
            WHERE a.count = 7
              AND a.by_e_from_c + a.by_f_from_c + a.by_u_from_c > 0
         ) as t

        WHERE wc.week_day = t.week_day";

      if ( !($res = $DB->squery($sql)) || !$DB->commit() ) {
        $e = $DB->error;
        $DB->rollback();
        return $this->log('������ ��� ���������� ��������� �������������. '.$e, self::LT_WARNING);
      }
      
      pg_free_result($res);
    }

    return $this->Step3a();
  }


  /**
   * ������������� �������� ��������� �� ��������� ���� � �������� ����.
   * @return    string    ��������� �� ������ ��� 0, ���� ��� ������ �������
   */
  private function Step3a()
  {
    // ��������� �������� 7d, 30d. ������ ����� �������� ��� 7d.
    // ��� �����: 1) ��������� �������� �� ���������� ����������� � stat_daily; 2) ��������� �������� ��
    // ����������, ������������ �� �������� ��� �� ����� ������������� 7-�������� �������.

    // (������) �� ��������� ������� ��������� ��� 2008-06-23. ��������� ������ �� 25 � 27-�� ����� (26-�� ��� ������).
    //          �� ��� ��� ��� �����������. �� ����� ���� ������������ ��� 2008-06-28, �������� �� ��� 2008-06-23 � �������� ������� nCnt. ����� �������
    //          ���� ��������� ���� - 7 � ������� nCnt ���� �� ���� ���� �� ���������.
    //          2008-06-23 ��� ��������, �� ��������� ��� �� ������� ������ �� 2008-06-16 �� 2008-06-23 (23 �� ����������). ������ 23-�
    //          ����� ��������, ��� ������ ��������� ����� ��� 23-�� �����. � ����� �� ��������� � 28-�� �����, �� ������ ��������� ��������
    //          �� ����������� ���, �� ����: 23, 24, 25, 26, 27. � ��������� ��, ����� ���: 16, 17, 18, 19, 20. ��� � ��������� ����� ����������
    //          ������ � 21 �� 28 (��� 28 �� ����������).

    // (�) ������ ����, �� ������� ��������� ��� ���� ���������� �������� (ldD). ������ -- ���� � 6 (�����).
    // (�) ������� ������� nCnt = D - lD, ��� �������� ���������� ����� ����, ����� �� ���������� � ��������.
    //     ���� nCnt >= 7, �� �������� �� ����� ���������, ����� �� �������� � 0 � ���� � (�), ����� (�). ������ -- ������� �� ����.
    // (�, �, �) ���� � ����� ����������.
    // (�) ��������� ��������. ����� ������, ��� _date >= lD-7 (��� ������ ���� ����� ����������� ����������) �
    //     _date < D-7 (������ ���� ����� ����������) � ��������� ��������, �� ����������, ������ �� ����� �������. �� ����,
    //     ��� "������" ���, ������� � ������� ���������� �� ������. ������ -- �����.
    // (�) ���������� ��������. ����� ����� ��������� �� ���������� ������������ �������, ����� �� ���������� � ��������.
    //     ������ ����� _date >= lD (������ �� ��� ������������� ����) � _date >= D-7 (������ ���� ����� ����������, �������� �����
    //     �� ������ ���� nCnt > 7. ��, �� �������� � ���� ������ �������� � 0, �� ������ ��������� ������ ������� --
    //     ��������, nCnt = 20 -- ���� ����� ������ ��������� ������). ������ -- �����.
    // (�) ������� � �� ����������� ���� (������ lD = D). ������ -- �����.
    //     ���������.
    
    $this->log('stat_collector::Step3a(). ��������� �������� 7d, 30d ������� stat_summary.', self::LT_SUBHEADER);
    
    $DB = $this->_sDB;

    $time = $this->get_time();
    $D = date('Y-m-d', $time);
    $limit = 10000; // �������� ������� �� ���� ������.
    $affected_total = 0;
    $offset = (int)$DB->val("SELECT value::int FROM stat_variables WHERE name='ss_cyclics_offset'");
    
    // (�), (�)
    $sql = "SELECT value::date AS ld, ?::date - value::date AS cnt FROM stat_variables WHERE name='ss_cyclics_last'";
    if ( !($res = $DB->query($sql, $D)) )
      return $this->log('������ ��� ��������� ss_cyclics_last. '.$DB->error, self::LT_ERROR);

    $nCnt = 100000;
    if(!pg_num_rows($res) || !($lD = pg_fetch_result($res,0,0)))
      $lD   = '1970-01-01';
    else
      $nCnt = (int)pg_fetch_result($res,0,1);

    if($nCnt <= 0)
      return $this->log('����� ������ ��� ���������� ����������� ��������� ���.', self::LT_NOTICE);

    $do_7decrease  = ($nCnt < 7);
    $do_30decrease = ($nCnt < 30);


    $this->log('���������� stat_summary ������ ��������������, ���� ������ ����.', self::LT_NOTICE);

    $sql = 
    "INSERT INTO stat_summary (user_id)
     SELECT d.user_id
       FROM (SELECT DISTINCT user_id
               FROM stat_daily
              WHERE _date >= ?) as d
     LEFT JOIN
       stat_summary s
         ON s.user_id = d.user_id
      WHERE s.user_id IS NULL
    ";


    if (!$DB->query($sql, $lD)) {
        return $this->log('������. '.$DB->error, self::LT_ERROR);
    }
    
    $this->log('��������� '.pg_affected_rows($res).' �����.', self::LT_NOTICE);
    
    
    do {
        
        if (!$DB->start()) {
            return $this->log("���������� ����������� ���������, �����: {$offset}. ������ -- ���������� ������� ����������. {$DB->error}", self::LT_ERROR);
        }
        
        if($affected_total) {
            $offset += $limit;
        }
        
        $affected_total = 0;
        
        
        if($do_30decrease) {
            
            // (�)
            if($do_7decrease) {
            
                $this->log("��������� �������� 7d �� ���-�� ��������� �� ������ [lD-7...D-7], �����: {$offset}.", self::LT_NOTICE);
                
                $sql =
                "UPDATE stat_summary as s
                    SET 
                        by_f_from_b_7d = by_f_from_b_7d - n.by_f_from_b::smallint,
                        by_e_from_b_7d = by_e_from_b_7d - n.by_e_from_b::smallint,
                        by_u_from_b_7d = by_u_from_b_7d - n.by_u_from_b::smallint,
                        by_f_from_p_7d = by_f_from_p_7d - n.by_f_from_p::smallint,
                        by_e_from_p_7d = by_e_from_p_7d - n.by_e_from_p::smallint,
                        by_u_from_p_7d = by_u_from_p_7d - n.by_u_from_p::smallint,
                        by_f_from_o_7d = by_f_from_o_7d - n.by_f_from_o::smallint,
                        by_e_from_o_7d = by_e_from_o_7d - n.by_e_from_o::smallint,
                        by_u_from_o_7d = by_u_from_o_7d - n.by_u_from_o::smallint,
                        by_f_from_s_7d = by_f_from_s_7d - n.by_f_from_s::smallint,
                        by_e_from_s_7d = by_e_from_s_7d - n.by_e_from_s::smallint,
                        by_u_from_s_7d = by_u_from_s_7d - n.by_u_from_s::smallint
                   FROM
                   (
                     SELECT
                            user_id,
                            SUM(by_f_from_b) as by_f_from_b,
                            SUM(by_e_from_b) as by_e_from_b,
                            SUM(by_u_from_b) as by_u_from_b,
                            SUM(by_f_from_c) as by_f_from_c,
                            SUM(by_e_from_c) as by_e_from_c,
                            SUM(by_u_from_c) as by_u_from_c,
                            SUM(by_f_from_p) as by_f_from_p,
                            SUM(by_e_from_p) as by_e_from_p,
                            SUM(by_u_from_p) as by_u_from_p,
                            SUM(by_f_from_t) as by_f_from_t,
                            SUM(by_e_from_t) as by_e_from_t,
                            SUM(by_u_from_t) as by_u_from_t,
                            SUM(by_f_from_o) as by_f_from_o,
                            SUM(by_e_from_o) as by_e_from_o,
                            SUM(by_u_from_o) as by_u_from_o,
                            SUM(by_f_from_s) as by_f_from_s,
                            SUM(by_e_from_s) as by_e_from_s,
                            SUM(by_u_from_s) as by_u_from_s


                       FROM stat_daily

                      WHERE _date <  '{$D}'::date - 7
                        AND _date >= '{$lD}'::date - 7

                      GROUP BY user_id
                      ORDER BY user_id
                      LIMIT {$limit} OFFSET {$offset}

                   ) as n

                  WHERE s.user_id = n.user_id
                ";
                
                if( !($res = $DB->query($sql)) ) {
                    $e = $DB->error;
                    $DB->rollback();
                    return $this->log('������ ��� ���������� ����������� ���������. '.$e, self::LT_ERROR);
                }
                
                $affected = (int)pg_affected_rows($res);
                $affected_total += $affected;
                $this->log("��������� {$affected} �����.", self::LT_NOTICE);
            } // if($do_7decrease)


            $this->log("��������� �������� 30d �� ���-�� ��������� �� ������ [lD-30...D-30], �����: {$offset}.", self::LT_NOTICE);
            
            $sql =
            "UPDATE stat_summary as s
                SET 
                    by_f_from_b_30d = by_f_from_b_30d - n.by_f_from_b,
                    by_e_from_b_30d = by_e_from_b_30d - n.by_e_from_b,
                    by_u_from_b_30d = by_u_from_b_30d - n.by_u_from_b,
                    by_f_from_c_30d = by_f_from_c_30d - n.by_f_from_c,
                    by_e_from_c_30d = by_e_from_c_30d - n.by_e_from_c,
                    by_u_from_c_30d = by_u_from_c_30d - n.by_u_from_c,
                    by_f_from_p_30d = by_f_from_p_30d - n.by_f_from_p,
                    by_e_from_p_30d = by_e_from_p_30d - n.by_e_from_p,
                    by_u_from_p_30d = by_u_from_p_30d - n.by_u_from_p,
                    by_f_from_t_30d = by_f_from_t_30d - n.by_f_from_t,
                    by_e_from_t_30d = by_e_from_t_30d - n.by_e_from_t,
                    by_u_from_t_30d = by_u_from_t_30d - n.by_u_from_t,
                    by_f_from_o_30d = by_f_from_o_30d - n.by_f_from_o,
                    by_e_from_o_30d = by_e_from_o_30d - n.by_e_from_o,
                    by_u_from_o_30d = by_u_from_o_30d - n.by_u_from_o,
                    by_f_from_s_30d = by_f_from_s_30d - n.by_f_from_s,
                    by_e_from_s_30d = by_e_from_s_30d - n.by_e_from_s,
                    by_u_from_s_30d = by_u_from_s_30d - n.by_u_from_s,
                    by_f_30d = by_f_30d - n.by_f,
                    by_e_30d = by_e_30d - n.by_e,
                    by_u_30d = by_u_30d - n.by_u
               FROM
               (
                 SELECT
                        user_id,
                        SUM(by_f_from_b) as by_f_from_b,
                        SUM(by_e_from_b) as by_e_from_b,
                        SUM(by_u_from_b) as by_u_from_b,
                        SUM(by_f_from_c) as by_f_from_c,
                        SUM(by_e_from_c) as by_e_from_c,
                        SUM(by_u_from_c) as by_u_from_c,
                        SUM(by_f_from_p) as by_f_from_p,
                        SUM(by_e_from_p) as by_e_from_p,
                        SUM(by_u_from_p) as by_u_from_p,
                        SUM(by_f_from_t) as by_f_from_t,
                        SUM(by_e_from_t) as by_e_from_t,
                        SUM(by_u_from_t) as by_u_from_t,
                        SUM(by_f_from_o) as by_f_from_o,
                        SUM(by_e_from_o) as by_e_from_o,
                        SUM(by_u_from_o) as by_u_from_o,
                        SUM(by_f_from_s) as by_f_from_s,
                        SUM(by_e_from_s) as by_e_from_s,
                        SUM(by_u_from_s) as by_u_from_s,
                        SUM(by_f) as by_f,
                        SUM(by_e) as by_e,
                        SUM(by_u) as by_u

                   FROM stat_daily

                   WHERE _date <  '{$D}'::date - 30
                     AND _date >= '{$lD}'::date - 30

                  GROUP BY user_id
                  ORDER BY user_id
                  LIMIT {$limit} OFFSET {$offset}

               ) as n

             WHERE s.user_id = n.user_id
            ";
             
            if( !($res = $DB->query($sql)) ) {
                $e = $DB->error;
                $DB->rollback();
                return $this->log('������ ��� ���������� ����������� ���������. '.$e, self::LT_ERROR);
            }
            
            $affected = (int)pg_affected_rows($res);
            $affected_total += $affected;
            $this->log("��������� {$affected} �����.", self::LT_NOTICE);
        } // if($do_30decrease)



        $dd7  = $do_7decrease;  // ��� ���������.
        $dd30 = $do_30decrease;


        // (�), (�)
        $this->log("����������� �������� 7d � 30d �� ���-�� ��������� �� ������ [lD...D], �����: {$offset}.", self::LT_NOTICE);

        $sql = 
        "UPDATE stat_summary as s
            SET 
                by_f_from_b_7d  = ".($dd7 ? 's.by_f_from_b_7d +' : '')." p.by_f_from_b_7d::smallint,
                by_e_from_b_7d  = ".($dd7 ? 's.by_e_from_b_7d +' : '')." p.by_e_from_b_7d::smallint,
                by_u_from_b_7d  = ".($dd7 ? 's.by_u_from_b_7d +' : '')." p.by_u_from_b_7d::smallint,
                by_f_from_p_7d  = ".($dd7 ? 's.by_f_from_p_7d +' : '')." p.by_f_from_p_7d::smallint,
                by_e_from_p_7d  = ".($dd7 ? 's.by_e_from_p_7d +' : '')." p.by_e_from_p_7d::smallint,
                by_u_from_p_7d  = ".($dd7 ? 's.by_u_from_p_7d +' : '')." p.by_u_from_p_7d::smallint,
                by_f_from_o_7d  = ".($dd7 ? 's.by_f_from_o_7d +' : '')." p.by_f_from_o_7d::smallint,
                by_e_from_o_7d  = ".($dd7 ? 's.by_e_from_o_7d +' : '')." p.by_e_from_o_7d::smallint,
                by_u_from_o_7d  = ".($dd7 ? 's.by_u_from_o_7d +' : '')." p.by_u_from_o_7d::smallint,
                by_f_from_s_7d  = ".($dd7 ? 's.by_f_from_s_7d +' : '')." p.by_f_from_s_7d::smallint,
                by_e_from_s_7d  = ".($dd7 ? 's.by_e_from_s_7d +' : '')." p.by_e_from_s_7d::smallint,
                by_u_from_s_7d  = ".($dd7 ? 's.by_u_from_s_7d +' : '')." p.by_u_from_s_7d::smallint,
                by_f_from_b_30d = ".($dd30 ? 's.by_f_from_b_30d +' : '')." p.by_f_from_b_30d,
                by_e_from_b_30d = ".($dd30 ? 's.by_e_from_b_30d +' : '')." p.by_e_from_b_30d,
                by_u_from_b_30d = ".($dd30 ? 's.by_u_from_b_30d +' : '')." p.by_u_from_b_30d,
                by_f_from_c_30d = ".($dd30 ? 's.by_f_from_c_30d +' : '')." p.by_f_from_c_30d,
                by_e_from_c_30d = ".($dd30 ? 's.by_e_from_c_30d +' : '')." p.by_e_from_c_30d,
                by_u_from_c_30d = ".($dd30 ? 's.by_u_from_c_30d +' : '')." p.by_u_from_c_30d,
                by_f_from_p_30d = ".($dd30 ? 's.by_f_from_p_30d +' : '')." p.by_f_from_p_30d,
                by_e_from_p_30d = ".($dd30 ? 's.by_e_from_p_30d +' : '')." p.by_e_from_p_30d,
                by_u_from_p_30d = ".($dd30 ? 's.by_u_from_p_30d +' : '')." p.by_u_from_p_30d,
                by_f_from_t_30d = ".($dd30 ? 's.by_f_from_t_30d +' : '')." p.by_f_from_t_30d,
                by_e_from_t_30d = ".($dd30 ? 's.by_e_from_t_30d +' : '')." p.by_e_from_t_30d,
                by_u_from_t_30d = ".($dd30 ? 's.by_u_from_t_30d +' : '')." p.by_u_from_t_30d,
                by_f_from_o_30d = ".($dd30 ? 's.by_f_from_o_30d +' : '')." p.by_f_from_o_30d,
                by_e_from_o_30d = ".($dd30 ? 's.by_e_from_o_30d +' : '')." p.by_e_from_o_30d,
                by_u_from_o_30d = ".($dd30 ? 's.by_u_from_o_30d +' : '')." p.by_u_from_o_30d,
                by_f_from_s_30d = ".($dd30 ? 's.by_f_from_s_30d +' : '')." p.by_f_from_s_30d,
                by_e_from_s_30d = ".($dd30 ? 's.by_e_from_s_30d +' : '')." p.by_e_from_s_30d,
                by_u_from_s_30d = ".($dd30 ? 's.by_u_from_s_30d +' : '')." p.by_u_from_s_30d,
                by_f_30d = ".($dd30 ? 's.by_f_30d +' : '')." p.by_f_30d,
                by_e_30d = ".($dd30 ? 's.by_e_30d +' : '')." p.by_e_30d,
                by_u_30d = ".($dd30 ? 's.by_u_30d +' : '')." p.by_u_30d
           FROM
           (
             SELECT
                    user_id,
                    SUM((_date >= '{$D}'::date - 7)::int * by_f_from_b) as by_f_from_b_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_e_from_b) as by_e_from_b_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_u_from_b) as by_u_from_b_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_f_from_p) as by_f_from_p_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_e_from_p) as by_e_from_p_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_u_from_p) as by_u_from_p_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_f_from_o) as by_f_from_o_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_e_from_o) as by_e_from_o_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_u_from_o) as by_u_from_o_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_f_from_s) as by_f_from_s_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_e_from_s) as by_e_from_s_7d,
                    SUM((_date >= '{$D}'::date - 7)::int * by_u_from_s) as by_u_from_s_7d,
                    SUM(by_f_from_b) as by_f_from_b_30d,
                    SUM(by_e_from_b) as by_e_from_b_30d,
                    SUM(by_u_from_b) as by_u_from_b_30d,
                    SUM(by_f_from_c) as by_f_from_c_30d,
                    SUM(by_e_from_c) as by_e_from_c_30d,
                    SUM(by_u_from_c) as by_u_from_c_30d,
                    SUM(by_f_from_p) as by_f_from_p_30d,
                    SUM(by_e_from_p) as by_e_from_p_30d,
                    SUM(by_u_from_p) as by_u_from_p_30d,
                    SUM(by_f_from_t) as by_f_from_t_30d,
                    SUM(by_e_from_t) as by_e_from_t_30d,
                    SUM(by_u_from_t) as by_u_from_t_30d,
                    SUM(by_f_from_o) as by_f_from_o_30d,
                    SUM(by_e_from_o) as by_e_from_o_30d,
                    SUM(by_u_from_o) as by_u_from_o_30d,
                    SUM(by_f_from_s) as by_f_from_s_30d,
                    SUM(by_e_from_s) as by_e_from_s_30d,
                    SUM(by_u_from_s) as by_u_from_s_30d,
                    SUM(by_f) as by_f_30d,
                    SUM(by_e) as by_e_30d,
                    SUM(by_u) as by_u_30d

               FROM stat_daily

              WHERE _date >= '{$lD}'
                AND _date >= '{$D}'::date - 30

              GROUP BY user_id
              ORDER BY user_id
              LIMIT {$limit} OFFSET {$offset}

           ) as p

          WHERE s.user_id = p.user_id
        ";
        
        if( !($res = $DB->query($sql))
            || !$DB->query('UPDATE stat_variables SET value = ? WHERE name = ?', $offset, 'ss_cyclics_offset')
            || !$DB->commit() )
        {
            $e = $DB->error;
            $DB->rollback();
            return $this->log('������ ��� ���������� ����������� ���������. '.$e, self::LT_ERROR);
        }
        
        $affected = (int)pg_affected_rows($res);
        $affected_total += $affected;
        $this->log("��������� {$affected} �����.", self::LT_NOTICE);
        sleep(1);
    
    } while($affected_total);
    
   

    // !!! ���� ���� ������ � ���������� stat_daily, �� ����� ���� ������...
    // _date >= '{$lD}' -- �� ��������� ����� ����������� ������ � stat_daily, ������� ������ ������ �� ������.
    // ����� ���� ��� ��� ��� ����� ����� _date >= ��������� ����� ����������� � stat_summary, � �� �����, �� ������� ��� ��������� ��� �������.
    // �� ��������� ������� � � �����������... � �����, ������ ��� ������� ������� �� Step3()...

    if( !$DB->query(
        'UPDATE stat_variables SET value = ? WHERE name = ?; UPDATE stat_variables SET value = ? WHERE name = ?',
        $D, 'ss_cyclics_last', '0', 'ss_cyclics_offset') )
    {
        return $this->log("������ ��� ��������� stat_variables.ss_cyclics_*! {$DB->error}", self::LT_ERROR);
    }


    return 0;
  }


  /**
   * ������������ ���������� ��������� �������������� ������� �� ���������� ����� (��� ������, ���� ����� ��� ��� ������ �� ����������).
   * ��������� ����������� � stat_monthly.
   * @return    string   ��������� �� ������ ��� 0, ���� ��� ������ �������
   */
  function Step4()
  {
    // ������� ������ �� stat_daily � stat_monthly.
    // (�) ������� ��������� ���������� ����� � stat_monthly (mM) (��� ���� ������� ��� ������-�� ������). ������ -- ������� �� ����.
    // (�) ����� �� stat_daily ������, ��� mM != 0 � _date >= mM + '1 month' (������ ���� ������, ��������������� ��� � stat_monthly)
    //     � _date < M (������� ����� �� ������ ���� � stat_monthly). ����������� ������ �� �����/����.
    //     ������ -- �������.
    
    $this->log('stat_collector::Step4(). ������� ������ �� stat_daily � stat_monthly.', self::LT_SUBHEADER);
    
    $DB = $this->_sDB;

    $time = $this->get_time();
    $M = date('Y-m', $time).'-01';


    $sql = "SELECT _date + interval '1 month'  FROM stat_monthly ORDER BY _date DESC LIMIT 1";
    $nM  = $DB->val( $sql );
    
    if ( $DB->error )
      return $this->log('������ ��� ������ stat_monthly. '.$DB->error, self::LT_ERROR);

    if ( !$nM )
      $nM   = '1970-01-01';

    $sql = 
    "INSERT INTO stat_monthly (user_id, _date, by_f, by_e, by_u)
     SELECT user_id,
            date_trunc('month', _date),
            stat_dayarray(date_part('day', _date)::smallint, by_f),
            stat_dayarray(date_part('day', _date)::smallint, by_e),
            stat_dayarray(date_part('day', _date)::smallint, by_u)
       FROM stat_daily
      WHERE _date >= ? 
        AND _date < ? 
      GROUP BY user_id, date_trunc('month', _date)";
    
    $DB->start();
    
    if ( !$DB->query($sql, $nM, $M) || !$DB->commit() )
    {
      $e = $DB->error;
      $DB->rollback();
      return $this->log('������ ��� ���������� stat_monthly. '.$e, self::LT_ERROR);
    }


    return 0;
  }


  /**
   * ������� ��� ������������ � �������� ������ �� stat_daily.
   * @return   string   ��������� �� ������ ��� 0, ���� ��� ������ �������
   */
  function Step5()
  {
    // ������� ������ ������ �� stat_daily.
    // (�) ������� ��������� ���������� ����� � stat_monthly (mM). ���� ������ ��� mM = 0 (� stat_monthly ��� ������ �� ���������) -- �������.
    // (�) ����� ������ _date < ldD - 30 (_date >= ldD - 30 -- ���������� ��������� ��� ��������� 30d). ldD �� ���� ������ ���� ����� D.
    //     � _date < mM + '1 month' (����� ������, ������ ���� ��� ��� ���� ���������� � stat_monthly).

    $this->log('stat_collector::Step5(). ������� ������ ������ �� stat_daily.', self::LT_SUBHEADER);
    
    $DB = $this->_sDB;

    $time = $this->get_time();
    $M = date('Y-m', $time).'-01';
    
    $sql = "SELECT _date + interval '1 month'  FROM stat_monthly ORDER BY _date DESC LIMIT 1";
    $nM  = $DB->val( $sql );
    
    if ( $DB->error )
      return $this->log('������ ��� ������ stat_monthly. '.$DB->error, self::LT_ERROR);

    if ( !$nM )
      return $this->log('��� ������ ������ ��� �������� �� stat_daily.', self::LT_NOTICE);

    $sql = "SELECT value FROM stat_variables WHERE name = 'ss_cyclics_last'";
    $lD  = $DB->val( $sql );
    
    if ( $DB->error )
      return $this->log('������ ��� ��������� ss_cyclics_last. '.$DB->error, self::LT_ERROR);

    if ( !$lD )
      return $this->log('��� ������ ������ ��� �������� �� stat_daily.', self::LT_NOTICE);
    
    $DB->start();
    
    $sql = "DELETE FROM stat_daily WHERE _date < ?::date - ".self::MAX_CYCLICS_OFFSET." AND _date < ?";

    if ( !$DB->query($sql, $lD, $nM) || !$DB->commit() )
    {
      $e = $DB->error;
      $DB->rollback();
      return $this->log('������ ��� �������� stat_daily. '.$e, self::LT_ERROR);
    }
    
    
    return 0;
  }


  /**
   * ������ VACUUM ��� stat_log
   * @return   string    ��������� �� ������ ��� 0, ���� ��� ������ �������
   */
  function Step6()
  {
    $this->log('stat_collector::Step6(). �������.', self::LT_SUBHEADER);
    
    if(date('G')==8 && date('w')==0) {
        $DB = $this->_sDB;
        
        if ( !$DB->squery('VACUUM FULL stat_log') )
            $this->log('������� stat_log �� ��������. '.$DB->error, self::LT_WARNING);
    }
    
    return 0;
  }

    /**
     * ������� ���������� ��������� �������� ������������� �� �������� ������.
     */
    function wordsStatRun() {
        $this->wordsStatStep1();
        $this->wordsStatStep2();
        $this->wordsStatStep3();
    }
  
    /**
     * �������� ��������� ���������� �� �������� ������.
     * ������������ ����� ������ � ��������� � ������� ���������� �� �����.
     */
    function wordsStatStep1() {
        global $DB;
        
        if ( !$DB->start() ) {
        	return false;
        }
        
        // 1.1 ���������� ����� ������ ���������� ��������� �������� ������������� �� ������� ��������� ����� 
        // �� stat_word_log (������� ��������� ��������� ������ � ���� �� ����� � ������� ���� �� ����) � ������ �� ��������� �������.
        // 
        // ���� ���� �� �����-�� �������� ���������� �� ����������� ���������� ����� - �� �������� ����� ��������� 
        // � ������ ��� ������� ��� ��������� ����������� �� date_trunc('hour', stat_word_log.visited)
        $sQuery = "SELECT i.user_id, i.word_id, 
                sum( (i.emp_cnt<>0)::int ) AS emp_cnt, 
                sum( (i.frl_cnt<>0)::int ) AS frl_cnt, 
                sum( (i.user_cnt<>0)::int ) AS user_cnt, 
                i.visited_hour AS stat_date 
            INTO TEMPORARY TABLE ___tmp_word_stat 
            FROM ( 
                SELECT 
                    user_id, word_id, 
                    MAX( (guest_id<>0 AND is_emp='t')::int ) AS emp_cnt, 
                    MAX( (guest_id<>0 AND is_emp='f')::int ) AS frl_cnt, 
                    MAX( (guest_id=0)::int ) AS user_cnt,
                    date_trunc('hour', visited) AS visited_hour
                FROM stat_word_log 
                GROUP BY visited_hour, user_id, word_id, guest_id, 
                    CASE WHEN guest_id = 0 THEN guest_ip ELSE '' END, is_emp 
            ) i 
            GROUP BY i.user_id, i.word_id, i.visited_hour";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        
        // 1.2 ������ � ��������� ���������� ��������� �������� ������������ �� �������� ������ � stat_word_hourly.
        // ��� ���������� ������������ ����� � ������� "�������" � ������ ��� �������� ������� ���������� (���3)
        $sQuery = "INSERT INTO stat_word_hourly ( user_id, word_id, emp_cnt, frl_cnt, user_cnt, stat_date ) 
            SELECT user_id, word_id, emp_cnt, frl_cnt, user_cnt, stat_date 
            FROM ___tmp_word_stat";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        // 1.3 ������ � ��������� ���������� ��������� �������� ������������ �� �������� ������ � stat_word_tmp.
        // ��� ���������� ������ ��� ��������� ���������� �� 30 ���� � ����� ���������� (���2)
        $sQuery = "INSERT INTO stat_word_tmp ( user_id, word_id, emp_cnt, frl_cnt, user_cnt, stat_date ) 
            SELECT user_id, word_id, emp_cnt, frl_cnt, user_cnt, stat_date 
            FROM ___tmp_word_stat";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        // 1.4 ������� ��������� �������.
        if ( !$DB->squery('DROP TABLE IF EXISTS ___tmp_word_stat') ) {
            $DB->rollback();
        	return false;
        }
        
        // 1.5 ������� stat_word_log.
        if ( !$DB->squery('TRUNCATE stat_word_log') ) {
            $DB->rollback();
        	return false;
        }
        
        if ( !$DB->commit() ) {
            $DB->rollback();
        	return false;
        }
        
        return true;
    }
    
    /**
     * ��������� �������� ����� ���������� �� �������� ������.
     * � ����� ���������� � ���������� �� ��������� 30 ���� ��������� ������, ������� ��������� �� ������� ���������� ���������.
     * 
     * ���������� ����� � ��������� ���������� �� ������ �������� �� ���� ���. ����� �� �� ��� �������� ������� ��� ��� ����� 
     * ����� ������ � ����� ����������. ���� ���������� �� ����������� ����� 30 ����, �� � ���������� "�� 30 ����" ������� ������ 
     * ��������, �� ��� ��� �� �������� � wordsStatStep3. 
     */
    function wordsStatStep2() {
        global $DB;
        
        // 2.1 ��������� � ����� ���������� �������� ����� ��� ������� ��� ��� ������� - � ����� ������ ����������.
        $sQuery = "INSERT INTO stat_word_summary ( user_id, word_id ) 
            SELECT h.user_id, h.word_id 
            FROM ( SELECT DISTINCT user_id, word_id FROM stat_word_tmp ) as h 
            LEFT JOIN stat_word_summary s ON s.user_id = h.user_id AND s.word_id = h.word_id 
            WHERE s.user_id IS NULL AND s.word_id IS NULL";
        
        if ( !$DB->squery($sQuery) ) {
        	return false;
        }
        
        if ( !$DB->start() ) {
        	return false;
        }
        
        // 2.2 ��������� � ����� ���������� � � ���������� �� ��������� 30 ���� ���������� ��������� ������������ 
        // � ������� ���������� ���������� ����� ����������
        $sQuery = "UPDATE stat_word_summary AS s SET
            emp_cnt     = s.emp_cnt     + h.emp_cnt, 
            frl_cnt     = s.frl_cnt     + h.frl_cnt, 
            user_cnt    = s.user_cnt    + h.user_cnt, 
            emp_30_cnt  = s.emp_30_cnt  + h.emp_cnt, 
            frl_30_cnt  = s.frl_30_cnt  + h.frl_cnt, 
            user_30_cnt = s.user_30_cnt + h.user_cnt 
        FROM
        (
            SELECT user_id, word_id, 
                SUM(emp_cnt) AS emp_cnt, 
                SUM(frl_cnt) AS frl_cnt, 
                SUM(user_cnt) AS user_cnt
            FROM stat_word_tmp 
            GROUP BY user_id, word_id 
        ) AS h
        WHERE s.user_id = h.user_id AND s.word_id = h.word_id";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        // 2.3 ������� �������� ������
        if ( !$DB->squery('TRUNCATE stat_word_tmp') ) {
            $DB->rollback();
        	return false;
        }
        
        if ( !$DB->commit() ) {
            $DB->rollback();
        	return false;
        }
    }
    
    /**
     * ������������ ���������� �� ����. ��������� ���������� �� ��������� 30 ����.
     */
    function wordsStatStep3() {
        global $DB;
        
        if ( !$DB->start() ) {
        	return false;
        }
        
        // 3.1 �������� ���������� �� �������� ������ �� ����
        // ���� ���� �� �����-�� �������� ���������� �� ����������� ���������� ����� - �� �������� ����� ��������� 
        // � ������ ���� ��������� ����������� �� date_trunc('hour', stat_word_hourly.stat_date)
        $sQuery = "INSERT INTO stat_word_daily ( user_id, word_id, stat_date, emp_cnt, frl_cnt, user_cnt ) 
        SELECT user_id, word_id, date_trunc('day', stat_date)::date AS visited_day, 
            SUM(emp_cnt) AS emp_cnt, 
            SUM(frl_cnt) AS frl_cnt, 
            SUM(user_cnt) AS user_cnt 
        FROM stat_word_hourly 
        WHERE stat_date < date_trunc('day', NOW())::date 
        GROUP BY visited_day, user_id, word_id";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        // 3.2 ������� ��� �������� ��������� ���������� - ��� ������ �� ����������
        $sQuery = "DELETE FROM stat_word_hourly WHERE stat_date < date_trunc('day', NOW())::date";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        if ( !$DB->commit() ) {
            $DB->rollback();
        	return false;
        }
        
        // ��������� ���������� �� ��������� 30 ����.
        if ( !$DB->start() ) {
        	return false;
        }
        
        // 3.3 ��������� ���������� �� ��������� 30 ���� �� �������� ������.
        $sQuery = "UPDATE stat_word_summary AS s SET 
            emp_30_cnt  = (CASE WHEN s.emp_30_cnt >= h.emp_cnt THEN s.emp_30_cnt - h.emp_cnt ELSE 0 END), 
            frl_30_cnt  = (CASE WHEN s.frl_30_cnt >= h.frl_cnt THEN s.frl_30_cnt - h.frl_cnt ELSE 0 END),
            user_30_cnt = (CASE WHEN s.user_30_cnt >= h.user_cnt THEN s.user_30_cnt - h.user_cnt ELSE 0 END)
        FROM
        (
            SELECT user_id, word_id, 
                SUM(emp_cnt)  AS emp_cnt, 
                SUM(frl_cnt)  AS frl_cnt, 
                SUM(user_cnt) AS user_cnt
            FROM stat_word_daily 
            WHERE stat_date <= date_trunc('day', NOW())::date - 30 
            GROUP BY user_id, word_id 
        ) AS h
        WHERE s.user_id = h.user_id AND s.word_id = h.word_id";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        // 3.4 ������� �������� ������� ���������� �������� ������ - ��� ������ �� ����������
        $sQuery = "DELETE FROM stat_word_daily WHERE stat_date <= date_trunc('day', NOW())::date - 30";
        
        if ( !$DB->squery($sQuery) ) {
            $DB->rollback();
        	return false;
        }
        
        if ( !$DB->commit() ) {
            $DB->rollback();
        	return false;
        }
        
        return true;
    }
    
  
  /**
   * ������������� ����������� ��������
   *
   */
  function setStamp() {
      if(!isset($_SESSION['stamp'])) $_SESSION['stamp'] = mt_rand(10000, 99999); 
  }
  
  /**
   * ��������� ��������
   *
   * @param integer|boolean $stamp �������� ��������
   * @param boolean $del   ������� �������� ��� ���
   * @return boolean
   */
  function checkStamp($stamp = false, $del=true) {
      $s_stamp = $_SESSION['stamp'];
      if($del) {
          unset($_SESSION['stamp']);
      }
      if($stamp !== false && $stamp !== $s_stamp) return true;
      
      return false;
  }


  /**
   * ����������. ��������� ��� ����������� ������ � $this->run_log ��� �������
   * �� �� �����, ���� $this->output == true.
   */
  function __destruct()
  {
    // ���������� ��� � ���.
    if($this->log_str) {
      if($this->output)
        print($this->log_str);
      if($f=fopen($this->run_log,"a")) {
        fwrite($f, $this->log_str);
        fclose($f);
      }
    }
  }

    /**
     * �������� �������, ������� �������� ������ �� ������� ����
     * �� stat_monthly � �������� ������� stat_monthly_YYYY
     *
     * @return <type>
     */
    function stat_monthly_split() {
        if($this->isDisabled() == 1) {
            $this->log('������ �������� ��������.', self::LT_HEADER);
            return false;
        }
        if (!$this->_sDB->squery("SELECT stat_monthly_split()")) {
            return false;
        }
    }
  
}
?>
