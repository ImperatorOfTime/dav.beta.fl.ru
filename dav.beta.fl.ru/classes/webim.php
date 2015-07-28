<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
/**
 * ����� ��� ������ � �������� �������������
 *
 *
 */
class webim {

	/**
	 * ���������� ������� �� �������� � �������
	 *
	 */
	const REC_ON_PAGE = 20;

	/**
	 * id �������� �������
	 * 
	 * @var integer
	 */
	public $thread = 0;

	/**
	 * ��� ������� �������� �������
	 *
	 * @var string
	 */
	public $client = '';

	/**
	 * uid ������������ �������� �������
	 *
	 * @var integer
	 */
	public $clientid = 0;

	/**
	 * ��� ��������� �������� �������
	 *
	 * @var string
	 */
	public $operator = '';

	/**
	 * id ��������� �������� �������
	 *
	 * @var integer
	 */
	public $operatorid = 0;

	/**
	 * ������� ������
	 *
	 * @var array
	 */
	public $dialog = array();
    
    /**
     * ������ ������� ����������� � ���� webim (mysql)
     * 
     * @var resource
     */
    protected $_webimConn = NULL;

	/**
	 * �������� ������ ����� ���������� � ������������� � ���� webim
	 *
	 * @param  integer  $uid  uid ������������
	 * @param  integer  $thread  id �������
	 *
	 * @return array    ������ �������: client - ��� �������, operator - ��� ���������, operatorid - id ���������, dialog - ������
	 */
	public function GetChat($uid, $thread) {
        $this->_webimConnect();
		$res = mysql_query("
            SELECT m.*, o.fullname 
            FROM chatmessage m 
            LEFT JOIN chatoperator o ON m.operatorid = o.operatorid 
            WHERE m.threadid = '{$thread}' ORDER BY m.created
        ", $this->_webimConn);
		$this->thread = $thread;
		$this->clientid = $uid;
		$this->client = '';
		$this->operatorid = 0;
		$this->operator = '';
		$this->dialog = array();
		while ($row = mysql_fetch_assoc($res)) {
			if (!$this->client && $row['sendername']) {
				$this->client = $row['sendername'];
			}
			if (!$this->operatorid && $row['operatorid']) {
				$this->operatorid = $row['operatorid'];
				$this->operator   = $row['fullname'];
			}
			$this->dialog[] = array(
				'client' => ($row['sendername']? $row['sendername']: ''),
				'operator' => ($row['fullname']? $row['fullname']: ''),
				'time' => $row['created'],
				'message' => $row['message']
			);
		}
		return empty($this->dialog)? FALSE: TRUE;
	}

	/**
	 * �������� ����� � ������� � �������������
	 *
	 * @param integer  $e1 ����� (�� 1 �� 5)�� "�������� ������"
	 * @param integer  $e2 ����� (�� 1 �� 5)�� "��������� ����������"
	 * @param integer  $e3 ����� (�� 1 �� 5)�� "����� �����������"
	 * @param string   $wish ���������
	 *
	 * @return string  ��������� ������
	 */
	public function Evaluate($e1, $e2, $e3, $wish) {
		if (empty($this->dialog)) {
			return '���������� ��������� �� ���������� ��� �� ��� �������� �����.';
		}
		
		global $DB;
		
		$count = $DB->val("SELECT COUNT(*) FROM webim WHERE thread = ?", $this->thread);
		
		if ($count) {
			return '���������� ��������� �� ���������� ��� �� ��� �������� �����.';
		}
		
		$e1 = intval($e1);
		$e2 = intval($e2);
		$e3 = intval($e3);
		$wish = trim($wish);
		$dialog = '';
		foreach ($this->dialog as $row) {
			$name = $row['client']? ($row['client'].': '): ($row['operator']? ($row['operator'].': '):  '');
			$dialog .= "[".$row['time']."] " . $name . $row['message'] . "\n\n";
		}
		
		$aData = array(
            'thread'        => $this->thread, 
            'user_id'       => !empty($this->clientid) ? $this->clientid : NULL, 
            'user_name'     => $this->client, 
            'operator_id'   => !empty($this->operatorid) ? $this->operatorid: NULL, 
            'operator_name' => $this->operator, 
            'dialog'        => $dialog, 
            'evaluation1'   => $e1, 
            'evaluation2'   => $e2, 
            'evaluation3'   => $e3, 
            'wish'          => !empty($wish) ? $wish : NULL
		);
		
		$DB->insert( 'webim', $aData );
		
		// ���������� ������������ � ����� ������ � ��� �������
		if ( !$DB->error ) {
		    $this->_webimConnect();
		    
		    $mRes = mysql_query( "INSERT INTO chatmessage (threadid, kind, message, created) 
                VALUES ('{$this->thread}', '3', '������������ �������� ��� ������', '".date('Y-m-d H:i:s')."')", 
                $this->_webimConn 
            );
            
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/webim/classes/config.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/webim/classes/functions.php' );
            
            $filename = $this->thread . HAS_MESSAGES_OPERATOR_FILE_POSTFIX;
            $filename = ONLINE_FILES_DIR . DIRECTORY_SEPARATOR . substr(md5($filename), 0, 1) . DIRECTORY_SEPARATOR . $filename;
            
            set_has_threads( $filename );
		}
		
		return $DB->error;
	}

	/**
	 * ��������, ��������� �� ��� ������ � ���������� �� �� ������
	 * 
	 * @param  integer  $thread   id ������� � webim
     * @param  string   $visitor  id �������� � webim
	 * @return boolean  TRUE - ������ ���������� � ������ ��� �� �������, FALSE - � ��������� ������
	 */
	public function Check($thread, $visitor) {
		if ( $GLOBALS['DB']->val("SELECT COUNT(*) FROM webim WHERE thread = ?", $thread) ) {
            return FALSE;
        }
        $this->_webimConnect();
        $sql = "
            SELECT COUNT(*)
            FROM chatthread t 
            INNER JOIN chatvisitsession s ON t.visitsessionid = s.visitsessionid 
            WHERE t.threadid = '{$thread}' AND s.visitorid = '{$visitor}'
        ";
        $res = mysql_query($sql, $this->_webimConn);
        $row = mysql_fetch_row($res);
        return (bool) $row[0];
	}

	/**
	 * ���������� �� ������� �����
	 *
	 * @return array  count - ������� � ���� ������, pcount - ������� � ������� ������, div - ������ ������� ����� ��������, average - ������� ����� �� �����
	 */
	public function MonthlyStat() {
	    global $DB;
		$cur = date('Y-m-01 00:00:00');
		$sql = "SELECT COUNT(*) AS count, SUM(evaluation1 + evaluation2 + evaluation3) AS average FROM webim WHERE post_time >= ?";
		$row = $DB->row( $sql, $cur );
		
		$sql = "SELECT COUNT(*) AS count FROM webim WHERE post_time < ? AND post_time >= (date ? - interval '1 month')";
		$row['pcount'] = $DB->val($sql, $cur, $cur );
		
		$row['div'] = $row['count'] - $row['pcount'];
		$row['average'] = $row['count']? ($row['average'] / ($row['count'] * 3)): 0;
		return $row;
	}

	/**
	 * �������� ������ ������� �������� ����� � ��������
	 *
	 * @param integer  $nums ���������� ���������� ��������� �������
	 * @param array    $filter �������� ������ ������� (��� WHERE)
	 * @param string   $sort �������� ����������
	 * @param integer  $pagenum ����� ������������ ��������
	 *
	 * @return array   ������ � �������
	 */
	public function ShowAll(&$nums, array $filter, $sort, $pagenum) {
		$where = "";
		if (!empty($filter['sdate'])) $where .= " AND f.post_time >= '{$filter['sdate']} 00:00:00' ";
		if (!empty($filter['edate'])) $where .= " AND f.post_time <= '{$filter['edate']} 23:59:59' ";
		if (!empty($filter['kind'])) $where .= " AND f.operator_id = '{$filter['kind']}' ";
		if (!empty($sort)) {
			if ($sort == 'date') {
				$sort = " ORDER BY f.post_time DESC ";
			} else if ($sort == 'average') {
				$sort = " ORDER BY score DESC ";
			}
		}
		
		global $DB;
		$nums = $DB->val( "SELECT COUNT(*) FROM webim f WHERE 1 = 1 $where" );
		
		if ( $nums > 0 ) {
			$sql = "SELECT
					f.*, ((f.evaluation1 + evaluation2 + evaluation3) / 3) AS average, (f.evaluation1 + evaluation2 + evaluation3) AS score, 
					u.login, u.uname, u.usurname, u.email
				FROM 
					webim f 
				LEFT JOIN 
					users u ON f.user_id = u.uid 
				WHERE 
					1 = 1
				$where
				$sort";
			
			return $DB->rows( $sql.' LIMIT '.self::REC_ON_PAGE.' OFFSET '.(($pagenum - 1) * self::REC_ON_PAGE) );
		} else {
			return array();
		}
	}

	/**
	 * ���������� ������ �������������
	 *
	 * @return array   ������ � �������
	 */
	public function Consultants() {
        $this->_webimConnect();
		$res = mysql_query("SELECT * FROM chatoperator", $this->_webimConn);
		$rows = array();
		while ($row = mysql_fetch_assoc($res)) {
			$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * �������� ������ �� ������
	 *
	 * @param  integer  $thread  id �������
	 *
	 * @return array    ������ � �������
	 */
	public function Get($thread) {
	    global $DB;
		return $DB->row( "SELECT * FROM webim WHERE thread = ?i", intval($thread) );
	}
    
    /**
     * ����������� � mysql ���� webim
     * 
     */
    protected function _webimConnect() {
        if ( empty($this->_webimConn) ) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/webim/classes/config.php';
            $this->_webimConn = mysql_connect(EXTERNAL_DB_HOST, EXTERNAL_DB_USER, EXTERNAL_DB_PASSWORD);
            mysql_select_db(EXTERNAL_DB_NAME, $this->_webimConn);
            mysql_set_charset('CP1251', $this->_webimConn);
        }
    }
    
    
  }