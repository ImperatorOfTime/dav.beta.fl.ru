<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/blogs.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/smail.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
/**
 * ����� ��� ������ � �������� ������
 *
 */
class feedback {

	/**
	 * ������������ ������ ����� ��� ��������
	 */
	const MAX_FILE_SIZE = 5242880; //5Mb
	
	/**
	 * ������������ ����� ��������� ������
	 *
	 */
	const MAX_FILES = 10;
	
	/**
	 * ���������� ������� �� �������� (� �������)
	 */
	const REC_ON_PAGE = 20;

	/**
	 * ������������ ���������� �������� � ���������
	 */
	const MAX_WISH_CHARS = 5000;
	
    /**
     * ���� ������� � helpdesk
     *
     * @var array
     */
	public $departaments = array(
		'er' => '������ �� �����',
		'fi' => '������� � �������',
		'io' => '����� �������',
		'MN' => '������ ��������',
		'NR' => '����������� ������'
	);
	
	/**
	 * ��������� ��������� � �������� ����� � �������� ������ � ����������� �����
	 * 
	 * @param integer $uid    uid ������������, ���� �� �����������
	 * @param string  $login  ��� ������������, ���� �� �� �����������
	 * @param string  $email  email ������������, ���� �� �� �����������
	 * @param integer $kind   id ������ (1-����� �������, 2-������ �� �����, 3-���������� ������, 4-���.��������, 5-���)
	 * @param string  $msg    ���������
	 * @param CFile   $files   ������������� ����
     *
     * @return string         ��������� ������
	 */
	public function Add($uid, $login, $email, $kind, $msg, $files,$additional=false) {
	    global $DB;
        mt_srand();
		$uc = md5(microtime(1).mt_rand());
		$uc = substr($uc, 0, 6).substr($uc, 12, 6);
        $login = substr($login, 0, 64);
		$uid = intval($uid);
		$kind = intval($kind);
		if (intval($uid)) {
			$user = new users;
			$user->GetUserByUID($uid);
			$login = $user->login;
			$email = $user->email;
		}
		$sql = 'INSERT INTO feedback 
				( uc, dept_id, user_id, user_login, email, question, request_time ) 
			VALUES
				( ?, ?, ?, ?, ?, ?, NOW() ) RETURNING id';
        if ( strtolower( mb_detect_encoding($login, array("utf-8")) ) == "utf-8" ) {
            $login = iconv("UTF-8", "WINDOWS-1251//IGNORE", $login);
        }
		$sId = $DB->val( $sql, $uc, $kind, $uid, $login, $email, $msg );
		
		if ( $DB->error ) {
			return '������ ��� �������� ��������� (db)';
		}
		
		$mail = new smail;
		if (count($files)){
                    foreach ($files as $attach) {
                        $msg .= "\n\n=============================================\n";
			$msg .= "� ����� ������ ���������� ���� ".WDCPREFIX."/upload/about/feedback/{$attach->name}";
			$msg .= "\n=============================================\n";
                    }
		}
                if($kind == 2){
                        $msg .= "\n\n=============================================\n";
			$msg .= "�������������� ����������: �������: ". (!empty($additional['browser']) ? $additional['browser'] : 'N/A').' ��: '.(!empty($additional['os']) ? $additional['os'] : 'N/A');
			$msg .= "\n=============================================\n";
                }
		$mail->FeedbackPost( $login, $email, $kind, $msg, $uc, $sId );
		
		// ����� ���������� ��������� � feedback
		$date  = date('Y-m-d H:01:00');
		$sql   = 'SELECT date FROM stat_feedback WHERE date=? AND type=?';
		$exist = $DB->val( $sql, $date, $kind );
		
		if ( $exist ) {
			$sql = "UPDATE stat_feedback SET count=count+1 WHERE date = ? AND type = ?";
		} else {
			$sql = "INSERT INTO stat_feedback(date,type,count) VALUES( ?, ?, 1 )";
		}
		
		$DB->query( $sql, $date, $kind );
		
		return '';
	}
	
	/**
	 * ���������, ��������� �� ����� � ������ � ������� �����
	 * 
	 * @param  string  $uc ���������� ����� ������ �������� �����
	 * @param  integer $deskid ����� ������ � helpdesk
	 * @return boolean TRUE ���� ����� �� ���������, FALSE ���� ��������� ��� ������ �� ����������
	 */
	public function Check( $uc, $deskid ) {
	    global $DB;
	    
	    $bRet = false;
	    // ������� �������: ��������� ����� ������� ����������� ��� ���� uc � desk_id
		$aRow = $DB->row( 'SELECT id, evaluation_time FROM feedback WHERE uc = ? AND desk_id = ?', $uc, $deskid );
		
		if ( $aRow ) {
			$bRet = empty( $aRow['evaluation_time'] );
		}
		else {
		    // ��� ������������� �� ������ ���������: ���� ���� uc, �� ��� desk_id - ������ ��� �� ���������
		    $bRet = (bool) $DB->val( 'SELECT COUNT(*) FROM feedback WHERE uc = ? AND desk_id IS NULL', $uc );
		}
		
		return $bRet;
	}
	

	/**
	 * �������� ����� � ������ �������� �����
	 *
	 * @param string   $uc ���������� ����� ������ �������� �����
	 * @param integer  $deskid ����� ������ � helpdesk
	 * @param integer  $e1 ����� (�� 1 �� 5)�� "�������� ������"
	 * @param integer  $e2 ����� (�� 1 �� 5)�� "��������� ����������"
	 * @param integer  $e3 ����� (�� 1 �� 5)�� "����� �����������"
	 * @param string   $wish ���������
	 *
	 * @return string  ��������� ������
	 */
	public function Evaluate($uc, $deskid, $e1, $e2, $e3, $wish) {
		$e1 = intval($e1);
		$e2 = intval($e2);
		$e3 = intval($e3);
		global $DB;
		// ������� �������: ��������� ����� ������� ����������� ��� ���� uc � desk_id
		$row = $DB->row( 'SELECT id, evaluation_time FROM feedback WHERE uc = ? AND desk_id = ?', $uc, $deskid );
		
		if ( !$row ) {
		    // ��� ������������� �� ������ ���������: ���� ���� uc, �� ��� desk_id - ������ ��� �� ���������
		    $row = $DB->row( 'SELECT id FROM feedback WHERE uc = ? AND desk_id IS NULL', $uc );
		    
		    if ( !$row ) {
    			return '���������� ��������� �� ���������� ��� �� ��� �������� �����.';
    		}
		}
		elseif ( !empty($row['evaluation_time']) ) {
		    return '���������� ��������� �� ���������� ��� �� ��� �������� �����.';
		}
		
		$sql = "
			UPDATE
				feedback 
			SET
				desk_id = ?, evaluation1 = ?, evaluation2 = ?, evaluation3 = ?, wish = ?, evaluation_time = NOW() 
			WHERE
				id = ?";
		
		$DB->query( $sql, $deskid, $e1, $e2, $e3, $wish, $row['id'] );
		
		return '';
	}

	/**
	 * ����������� ������ ������� � helpdesk
	 *
	 * @param  string  $code ���������� ��� ������ �������� ���� + ������������� ����� �� helpdesk
	 * 
	 * @return array   id-����� �� helpdesk, uc-���������� ��� ������ �������� �����
	 */
	public function DecodeUCode($code) {
		// ������������ ��� ���������� ������ ������ �� helpdesk, ��� ����, ����� �� ����� � ���� helpdesk'�
		// �� ������, ���� ���� ��� ����������, �� ���-����� �� ������ ������������� �� ����� �����, �.�. ���
		// ��� ����������� ������������ ���������� ����� ������ �������� ����� ($uc)
		$c = array(
			'e'=>'0', 'i'=>'0', 'b'=>'0', 'z'=>'0', '2'=>'0', '9'=>'0',
			'j'=>'1', 'm'=>'1', 'c'=>'1', 'p'=>'2', '7'=>'2', 'v'=>'2',
			'f'=>'3', '8'=>'3', 's'=>'3', 'u'=>'4', 'r'=>'4', 'd'=>'4',
			'y'=>'5', '0'=>'5', 'h'=>'5', 'w'=>'6', 'k'=>'6', 'l'=>'6',
			'n'=>'7', 'g'=>'7', 'q'=>'7', '3'=>'8', '1'=>'8', 't'=>'8',
			'x'=>'9', 'a'=>'9', 'o'=>'9'
		);
		$n = array('fi'=>'fi', 'io'=>'io', 'er'=>'er', 'mn'=>'MN', 'nr'=>'NR');
		$strlen = strlen($code);
		if ($strlen < 20 || $strlen > 30) {
			return FALSE;
		}
		$id = substr($code, 0, 6) . substr($code, 18);
		$uc = substr($code, 6, 12);
		$lenid = strlen($id);
		if (!isset($n[strtolower(substr($id, 0, 2))])) {
			return FALSE;
		}
		for ($i=2; $i<$lenid; $i++) {
			if (!isset($c[$id{$i}])) {
				return FALSE;
			}
			$id{$i} = $c[$id{$i}];
		}
		return array('id'=>$id, 'uc'=>$uc);
	}
	
	/**
	 * ���������� �� ������� �����
	 * 
	 * @param  array  $filter �������� ������ ������� (��� WHERE)
	 * @return array  count - ������� � ���� ������, pcount - ������� � ������� ������, div - ������ ������� ����� ��������, average - ������� ����� �� �����
	 */
	public function MonthlyStat( $filter = array() ) {
	    $where = "";
	    
	    if ( !empty($filter['kind']) ) $where .= " AND dept_id = '{$filter['kind']}' ";
	    
	    global $DB;
		$cur = date('Y-m-01 00:00:00');
		$sql = "SELECT COUNT(*) AS count, SUM(evaluation1 + evaluation2 + evaluation3) AS average FROM feedback WHERE evaluation_time IS NOT NULL AND request_time >= ? $where";
		$row = $DB->row( $sql, $cur );
		$sql = "SELECT COUNT(*) AS count FROM feedback WHERE evaluation_time IS NOT NULL AND request_time < ? AND request_time >= (date ? - interval '1 month') $where";
		
		$row['pcount']  = $DB->val($sql, $cur, $cur );
		$row['div']     = $row['count'] - $row['pcount'];
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
		if (!empty($filter['sdate'])) $where .= " AND f.request_time >= '{$filter['sdate']} 00:00:00' ";
		if (!empty($filter['edate'])) $where .= " AND f.request_time <= '{$filter['edate']} 23:59:59' ";
		if (!empty($filter['kind'])) $where .= " AND f.dept_id = '{$filter['kind']}' ";
		if (!empty($sort)) {
			if ($sort == 'date') {
				$sort = " ORDER BY f.request_time DESC ";
			} else if ($sort == 'average') {
				$sort = " ORDER BY score DESC ";
			}
		}
		
		global $DB;
		$nums = $DB->val( "SELECT COUNT(*) FROM feedback f WHERE evaluation_time IS NOT NULL $where" );
		
		if ( $nums ) {
			$sql = "
				SELECT
					f.*, ((f.evaluation1 + evaluation2 + evaluation3) / 3) AS average, (f.evaluation1 + evaluation2 + evaluation3) AS score, 
					u.login, u.uname, u.usurname, u.email
				FROM 
					feedback f 
				LEFT JOIN 
					users u ON f.user_id = u.uid 
				WHERE 
					evaluation_time IS NOT NULL
				$where
				$sort
			";
			
			return $DB->rows( $sql.' LIMIT '.self::REC_ON_PAGE.' OFFSET '.(($pagenum - 1) * self::REC_ON_PAGE) );
		} else {
			return array();
		}
	}

}