<?
/**
 * ���������� ���� � ��������� ���������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ������ � ������� �����
 *
 */
class payed
{
        static public $date_action_test_pro = array(20130210, 20130221);
        
        const PRICE_EMP_PRO = 899; // � ������
        const PRICE_FRL_PRO = 899;
    
	/**
	 * ������� ��������� ��� 
	 *
	 * @param boolean $get_all ����� ��� ��� �� ���
	 * @return integer ���������
	 */
	 function GetProPrice($get_all=FALSE, $op_code = 48) {
	 	$base = 10;
			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/op_codes.php");
			$op_codes = new op_codes();
			if($get_all) {
			  $prices = NULL;
	      if($rows = $op_codes->getCodes('132,131,47,48,49,50,51,76,114')) {
	        foreach($rows as $r)
	          $prices[$r['id']] = $r['sum'] * $base;
	      }
	      return $prices;
	    }
	    return $op_codes->GetField($op_code, $error, 'sum') * $base;
	}
        
        static function get_opcode_action_test_pro() {
            return ( (int) date('Ymd') >= self::$date_action_test_pro[0] && (int) date('Ymd') <= self::$date_action_test_pro[1] ) ? 114 : 47;
        }
	
	/**
	 * ������ ������������ ������ (������� ���)
	 *
	 * @param integer $fid              �� ������������
	 * @param integer $transaction_id   �� ����������
	 * @param string  $time             ����� 
	 * @param string  $comments         ������ ������
	 * @param integer $tarif            ����� ������
	 * @return integer|array 0 - ���� ������ �� �����, ����� ������ �� ������
	 */
	function SetOrderedTarif($fid, $transaction_id, $time, $comments="��� �������", $tarif = 48, $promo_code = 0, &$error = null){
        global $DB;
	    //�� �������� ������ ������� PRO �������������, ������� ��� �����-���� �� ������������
	    if ($this->IsUserWasPro($fid) && ($tarif == 47 || $tarif == 114) )
	    	return 0;
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
		$account = new account();
		$cost = $time * 10;
        if($tarif == 15) {
            $cost = $time * ( payed::PRICE_EMP_PRO );
        }
		$is_pro_test = 'false';
                if($tarif == 114 && self::get_opcode_action_test_pro() != 114) // ��������� �� ����� �� ����� �����
                    return 0;
		if ($tarif == 47 || $tarif == 114) {
			$time = "7 days";
			$is_pro_test = 'true';
		}
		elseif ($tarif == 131)	{$time = "1 week";}
		elseif ($tarif == 132)	{$time = "1 day";}
		elseif (in_array($tarif, array(48, 163, 164))) {$time = "1 month";}
		elseif ($tarif == 49)	{$time = "3 month";}
		elseif ($tarif == 50)	{$time = "6 month";}
		elseif ($tarif == 51)	{$time = "12 month";}
	    elseif ($tarif == 15)	{$time = $time." month";}
        elseif ($tarif == 118)	{$time = "3 month";}
		elseif ($tarif == 119)	{$time = "6 month";}
		elseif ($tarif == 120)	{$time = "12 month";}
	    elseif ($tarif == 76)   {$time = $time." week";}
        
        
        //�������� ��������� � ������������� ���
        if ($tarif == 164) {
            $data = $this->ProLastById($fid, array($tarif));
            if ($data) {
                //���� ���� ��� ����� ��������� �� ��������
                if (!empty($data['freeze_from'])) {
                    $this->freezeProDeactivate($fid);
                }

                //������������� ���������� PRO � PROFI
                $_interval = $this->getProfiDaysFromPro($fid);

                //��������� ����������� �������� �������������� �����
                if ($_interval) {
                    $diff = abs(strtotime('now') - strtotime("+ {$_interval}"));
                    $days = floor($diff / (60*60*24));

                    if ($days > 0) {
                        $comments .= sprintf(" + %s %s ����������� �� �������� ���", $days, ending($days, '����', '���', '����'));                       
                    }
                }
            }
        }
        
        $DB->start();
		$error = $account->Buy($bill_id, $transaction_id, $tarif, $fid, "��� �������", $comments, $cost, 0, $promo_code);
        
        if (!$error) {
            
            $this->account_operation_id = $bill_id;
            
            //���� ���� ��� �� ������������ � PROFI � ���������
            if ($tarif == 164) {
                if ($data) {
                    if ($_interval) {
                        $this->disableActivePro($fid, array($tarif));
                        $time = sprintf("%s + %s", $time, $_interval);
                    }
                }
            }

            $sql = "INSERT INTO orders (from_id, to_date, tarif, ordered, billing_id, payed) VALUES (?, ?, ?, true, ?, true);";
            if ($DB->query($sql, $fid, $time, $tarif, $bill_id)) {
                
                //@todo: ��� �� ����� �������� ��� ������� �������� - � ������ ��� ���������!
                if ($fid == $_SESSION['uid'] && !is_pro())
                    $_SESSION['is_pro_new'] = 't';
                
                $DB->commit();
                return true;
            }else {
                $DB->rollback();
            }
        } else {
            $DB->rollback();
        }
    return false;
	}
	
	/**
	 * ����� ������ �� ������������� ������
	 *
	 * @param integer $bill_id  	   �� ������
	 * @param integer $gift_id  	   �� �������
	 * @param integer $gid      	   �� �������
	 * @param integer $fid      	   �� ������������
	 * @param integer $transaction_id  �� ����������
	 * @param integer $time            �����
	 * @param integer $comments        ����������� ������
	 * @param integer $tarif           �� ������ (��. ������� op_codes)
	 * @return array ������ �������
	 */
	function GiftOrderedTarif(&$bill_id, &$gift_id, $gid, $fid, $transaction_id, $time, $comments="������� PRO  � �������", $tarif = 52){
        global $DB;
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
		$account = new account();
		$error = $account->Gift($bill_id, $gift_id, $transaction_id, $tarif, $fid, $gid, "��� �������", $comments, 10*($tarif==52||$tarif==16 ? $time : 1));
		if(!$error) {
            $sql = "INSERT INTO orders (from_id, to_date, tarif, ordered, billing_id, payed) VALUES (?, ?, ?, true, ?, true)";
            if($DB->query($sql, $gid, (is_numeric($time)? "{$time} month": $time) , $tarif, $bill_id)) {
                $login = get_login($gid);
                
                if($gid==$_SESSION['uid'] && !is_pro()) 
                    $_SESSION['is_pro_new'] = 't';
                
                if ($gid == $_SESSION['uid']) {
                    $pro_last = payed::ProLast($login);
                    $_SESSION['pro_last'] = $pro_last['freeze_to'] ? false : $pro_last['cnt'];
                } else {
                    $session = new session();
                    $session->UpdateProEndingDate($login);
                }
                
                return true;
            }
        }
        return false;
	}
	
	
    /**
     * ���������� ������ �� ������������� ������� ���������� �������� ��� ��������
     *
     * @return array ������ �������
     */
    function GetProTestUsers() {
        global $DB;
        $sql = "
            SELECT users.*, orders.from_date, from_date+to_date+COALESCE(freeze_to, '0')::interval as to_date
            FROM orders 
            INNER JOIN users ON users.uid = orders.from_id
            WHERE ( -- payed=true AND orders.active=true AND -- deprecated #0021704
            ordered=true AND from_date < now() AND from_date+to_date+COALESCE(freeze_to, '0')::interval > now() AND is_pro_test = 't')
        ";
		return $DB->rows($sql);
    }
    
	/**
	 * ����� ������ � ������������� ������ (�� �������)
	 *
	 * @deprecated 
	 * 
	 * @param integere $id      �� ������
	 * @param integer  $ammount ����� ������
	 * @param string   $date    ���� ������
	 * @param string   $to_date ���� (�� ����� ����� ��������� ������)
	 * @return string ��������� �� ������
	 */
	function SetPayed($id, $ammount, $date, $to_date){
        global $DB;
		$sql = "UPDATE orders SET payed=true, from_date= ? , to_date=(timestamp ? -timestamp ? ) WHERE id=?i; SELECT from_id, tarif FROM orders WHERE id=?i;";
		$res = $DB->query($sql, $date, $to_date, $date, $id, $id);
		list($uid, $tarif) = @pg_fetch_row($res);
		$sql = "SELECT billing_id FROM orders WHERE id=?i;";
		$billing_id = $DB->val($sql, $id);
		if ($billing_id){
			$sql = "DELETE FROM billing WHERE id=?i";
			$DB->query($sql, $billing_id);
		}
		$sql = "INSERT INTO billing (uid, ammount, op_code) VALUES (?i,?,?);
		SELECT id FROM billing ORDER BY id DESC LIMIT 1";
		$billing_id = $DB->val($sql, $uid, $ammount, $tarif);
        $error = $DB->error;
		$sql = "UPDATE orders SET billing_id=?i WHERE id = ?i";
		$DB->query($sql, $billing_id, $id);
		return $error;
	}
	
	/**
	 * ������� ������ �� �� ��.
	 *
	 * @param integer $id �� ������
	 * @return string ��������� �� ������
	 */
	function DeleteOrder($id){
        global $DB;
		$sql = "DELETE FROM orders WHERE id=?i";
		$DB->query($sql, $id);
		$error = $DB->error;
		return $error;
	}
	
	/**
     * ��������, �������� �� ������������ ��� �� ������
     *
     * @param string  $login    ����� ������������
     * @return integer �� ������������ ���� �� ���������� � �������, ����� 0
     */
	function CheckPro($login){
        global $DB;
		$sql = "SELECT from_id FROM orders
          LEFT JOIN users ON from_id=uid
          WHERE login=? -- AND payed=true AND orders.active='true' -- depracated #0021704
          AND from_date<=now() AND from_date+to_date+COALESCE(freeze_to, '0')::interval >= now() 
            AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)";
		$id = $DB->val($sql,$login);
        $error = $DB->error;
        return ($id?$id:0);
    }

    /**
     * ��������, �������� �� ������������ ��� �� ��
     *
     * @param integer $uid �� ������������
     * @return boolean true - ���� ��������, ����� false
     */
    function checkProByUid($uid)
    {
        global $DB;
        $sql = "SELECT 1 FROM orders 
                WHERE from_id = ?i -- AND payed=true AND active='true' -- deprecated #0021704
                AND from_date<=now() AND from_date+to_date+COALESCE(freeze_to, '0')::interval >= now() 
                AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)";
        return (bool) $DB->val($sql, $uid);
    }

    /**
     * ������������ ��� � ������� ����������� ���� ���, �������� �� ������
     *
     * @param string $login  �����
     * @return integer
     */
    function ProLast($login) {
        global $DB;
        $sql = "SELECT from_date+to_date+COALESCE(freeze_to, '0')::interval as cnt,
                    fr.from_time as freeze_from,
                    fr.to_time-'1 day'::interval as freeze_to,
                    CASE WHEN NOW() >= fr.from_time AND NOW() < fr.to_time THEN 1 ELSE 0 END as is_freezed
                FROM orders
                LEFT JOIN users ON from_id=uid
                LEFT JOIN (
                    SELECT from_time, to_time, user_id, (to_time-from_time)::interval-'1 day'::interval _freeze_to
                    FROM orders_freezing_pro
                    WHERE to_time > NOW() 
                ) fr ON fr.user_id = uid
                WHERE login=? -- AND payed=true AND orders.active='true' -- deprecated #0021704
                    AND from_date+to_date+COALESCE(COALESCE(_freeze_to, freeze_to), '0')::interval > now()
                ORDER BY id DESC LIMIT 1";
        $res = $DB->query($sql, $login);
        $error = $DB->error;
        $result = null;
        if (!$error && pg_numrows($res)) {
            $result = pg_fetch_row($res, null, PGSQL_ASSOC);
        }
        return $result;
    }
	
	
    /**
     * ���������� � ������� ��� � ��� ��������� �� ID ������������
     * ������ ProLast
     * 
     * @global DB $DB
     * @param type $uid - ID ������������
     * @param type $_notIn - ID op_codes ������� ����� ��������� �� ������������
     * @return type
     */
    function ProLastById($uid, $_notIn = array()) 
    {
        global $DB;
        $sql = "SELECT 
                    from_date + to_date + COALESCE(freeze_to, '0')::interval as cnt,
                    fr.from_time as freeze_from,
                    fr.to_time-'1 day'::interval as freeze_to,
                    CASE WHEN NOW() >= fr.from_time AND NOW() < fr.to_time THEN 1 ELSE 0 END as is_freezed
                FROM orders
                LEFT JOIN (
                    SELECT 
                        from_time, 
                        to_time, 
                        user_id, 
                        (to_time-from_time)::interval-'1 day'::interval _freeze_to
                    FROM orders_freezing_pro
                    WHERE to_time > NOW() 
                ) fr ON fr.user_id = from_id
                WHERE 
                    from_id=?i 
                    ".(!empty($_notIn)?" AND tarif NOT IN(?l)":"")."
                    AND from_date + to_date + COALESCE(COALESCE(_freeze_to, freeze_to), '0')::interval > now()
                ORDER BY id DESC LIMIT 1";
        
        $res = $DB->query($sql, $uid, $_notIn);
        $error = $DB->error;
        $result = null;

        if (!$error && pg_numrows($res)) {
            $result = pg_fetch_row($res, null, PGSQL_ASSOC);
        }
        
        return $result;
    }    
    
    
    
    
    
    
    
    
    /**
     * ����������� ������������� ����� ��� � PROFI
     * 
     * @global DB $DB
     * @param type $uid
     */
    function getProfiDaysFromPro($uid)
    {
        global $DB;
        
        return $DB->val("
        SELECT 
            ((from_date + to_date - COALESCE(COALESCE(_freeze_to, freeze_to), '0')::interval) - NOW()) / 13
        FROM orders
        LEFT JOIN (
            SELECT 
                user_id, 
                (to_time - from_time)::interval _freeze_to
            FROM orders_freezing_pro
            WHERE to_time > NOW() 
        ) fr ON fr.user_id = from_id
        WHERE 
            from_id = ?i 
            AND tarif <> 164
            AND from_date + to_date + COALESCE(COALESCE(_freeze_to, freeze_to), '0')::interval > NOW()
        ORDER BY id DESC LIMIT 1    
        ", $uid);
    }








    /**
	 * ������� ������������� ������� ���������� ������������ �� ��������� ��� ������
	 *
	 * @see smail::SendWarnings();
	 * 
	 * @return array
	 */
	function GetWarnings(){
        global $DB;
        $sql = "
          SELECT u.uname, u.usurname, u.login, u.email, u.role, a.from_date+a.to_date+COALESCE(a.freeze_to, '0')::interval as to_date 
            FROM (
              SELECT *
                FROM orders
               WHERE (from_date, from_id) IN (SELECT MAX(from_date), from_id FROM orders GROUP BY from_id)
                 AND from_date+to_date+COALESCE(freeze_to, '0')::interval < now()+INTERVAL '3 DAY' 
                 AND from_date+to_date+COALESCE(freeze_to, '0')::interval > now()+INTERVAL '2 DAY' 
                 -- AND payed = true AND active = true -- deprecated
            ) a 
          INNER JOIN users u
              ON u.uid = a.from_id 
           WHERE u.is_banned = '0'
             AND u.is_pro = true
             AND u.is_pro_auto_prolong IS NOT TRUE
        ";
        $ret = $DB->rows($sql);
        return $ret;
	}
	
	/**
	 * ���������� ��� ���������
	 *
	 * @return array [����������]
	 */
	function CountPro(){
        global $DB;
		$sql = "SELECT COUNT(*) FROM orders WHERE (payed=true AND orders.active=true AND ordered=true AND from_date < now() AND from_date+to_date+COALESCE(freeze_to, '0')::interval > now())";
		$ret['cur'] = $DB->val($sql);
		$sql = "SELECT COUNT(*) FROM (SELECT DISTINCT from_id FROM orders WHERE (payed=true AND ordered=true)) as t";
		$ret['all'] = $DB->val($sql);
		return $ret;
	}
	
	/**
	 * ���������� � ������ �� ������ ������ � ��. ������������
	 *
	 * @param integer $bill_id �� ������
	 * @param integer $uid     �� ������������
	 * @return string
	 */
	function GetOrderInfo($bill_id, $uid){
        global $DB;
        $out = '';
        $sql = "
          SELECT u.uname, u.usurname, u.login, ao.ammount, ao.op_code
            FROM account_operations ao
          LEFT JOIN
            present p
          INNER JOIN
            users u
              ON u.uid = CASE WHEN p.to_uid = ?i THEN p.from_uid ELSE p.to_uid END
              ON (p.billing_to_id = ao.id AND p.to_uid = ?i OR p.billing_from_id = ao.id AND p.from_uid = ?i)
           WHERE ao.id = ?i
        ";
		$res = $DB->row($sql, $uid, $uid, $uid, $bill_id);
        $uname = $res['uname'];
        $usurname = $res['usurname'];
        $login = $res['login'];
        $ammount = $res['ammount'];
        $op_code = $res['op_code'];
		if (in_array($op_code,array(16,52,66,67,68)) && $ammount < 0) {
			$out = "������� PRO ��� <a href=\"/users/".$login."\" class=\"blue\">".$uname." ".$usurname." [".$login."]</a>";
		} else {
			
			if (in_array($op_code,array(16,52,66,67,68))) 
				$out = "������� PRO �� <a href=\"/users/".$login."\" class=\"blue\">".$uname." ".$usurname." [".$login."]</a><br>";
			$sql = "
                SELECT 
                    from_date, 
                    (from_date+to_date+COALESCE(freeze_to, '0')::interval) as to_date 
                FROM orders 
                WHERE 
                    billing_id = ?i 
                    AND from_id = ?i 
                    AND to_date > '0'::interval
                ";
            $row = $DB->row($sql, $bill_id, $uid);
			if ($row) {
    		    $out .= "� ".date("d.m.Y | H:i", strtotime($row['from_date']))." �� ".date("d.m.Y | H:i", strtotime($row['to_date']));
    		} else {
                $out .= '�������� ����� �������� �������� PRO/PROFI';
            }
		}
		return $out;
	}
	
	/**
	 * ������� ������ �� ��. ������������ � ��. �������� 
	 *
	 * @param integer $uid �� ������������
	 * @param integer $opid �� ��������
	 * @return integer
	 */
	function DelByOpid($uid, $opid){
        global $DB;
        $sql = "DELETE FROM orders WHERE billing_id=?i AND from_id=?i";
        $DB->query($sql, $opid, $uid);

        self::UpdateProUsers();

        return 0;
	}
	
    
    /**
     * �������� ������� ��� ��������� ���
     * 
     * @global DB $DB
     * @param type $uid
     * @param type $_notIn
     * @return type
     */
    function disableActivePro($uid, $_tarifNotIn = array()) 
    {
        global $DB;
        
        $DB->start();
        
        //�������� ������� ���
        $ok1 = $DB->query("
            UPDATE orders SET
                from_date = NOW(),
                to_date = '0'::interval
            WHERE
                from_id = ?i 
                ".(!empty($_tarifNotIn)?" AND tarif NOT IN(?l) ":"")." 
                AND from_date > NOW()                
        ", $uid, $_tarifNotIn);
        
        //��������� ������� ���
        $ok2 = $DB->query("
            UPDATE orders SET
                to_date = (NOW() - from_date)::interval
            WHERE 
                from_id = ?i 
                ".(!empty($_tarifNotIn)?" AND tarif NOT IN(?l) ":"")." 
                AND NOW() BETWEEN from_date AND from_date + to_date
        ", $uid, $_tarifNotIn);
        
        if(!$ok1 || !$ok2) {
            $DB->rollback();
        }
        
        $DB->commit();
        
        return true;
    }


    
    /**
	 * �������� ��� ������������� � ������� ����� ����� ����������� �������
	 *
	 * @return integer
	 */
	function UpdateProUsers() 
    {
        global $DB;

		$sql = "UPDATE users SET 
                    is_pro = false, 
                    is_pro_test = false, 
                    is_profi = false
		        WHERE 
                   is_pro = true 
                   AND (
                     uid NOT IN (SELECT from_id FROM pro_orders_id_uid)
                     OR uid IN (SELECT user_id FROM orders_freezing_pro WHERE now() >= from_time AND now() < to_time)
                   );";
        
		$DB->squery($sql);

		return 0;
	}
	
	
	/**
	 * ���������, ����� �� ���������� ���� is_new_pro, ����������� � 25.11.2010:
	 * 1. ������� ������� � ���, ������������ � ������ ������.
	 * 2. �������, ���� ���� ������� ����� ��� ����� 25-��, �� ������������� ����.
	 *
	 * @param integer $uid   ��. �����.
	 * @return boolean
	 */
	function checkNewPro($uid) {
        global $DB;
	    $sql = "
	      UPDATE freelancer f
	         SET is_pro_new = true
	        FROM orders o
	       WHERE f.uid = o.from_id
	         AND f.is_pro_new = false
	         AND o.from_date <= now()
	         AND o.from_date + o.to_date + COALESCE(freeze_to, '0')::interval >= now()
	         AND o.posted >= '2010-11-25'::date
	         AND o.from_id = {$uid}
	    ";
	    return !!$DB->query($sql, $uid);
	}
	
	
	/**
	 * �������� ����������� � ��� ��� ����� ��� ����������
	 *
	 * @return integer
	 */
    function AlertPROEnding() {
        global $DB;
    	/**
    	 * ���� ��� ������ � ������ � ���������
    	 */
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        $mail = new smail();
        $sql = "SELECT pro_users.uid, pro_users.date_end FROM (
                    SELECT uid, MAX(from_date+to_date+COALESCE(freeze_to, '0')::interval) AS date_end
                        FROM orders 
                        LEFT JOIN users ON from_id=uid 
                        WHERE users.is_banned = '0' AND users.is_pro='true' AND users.is_pro_auto_prolong='t' -- AND orders.payed='true' AND orders.active='true' -- deprecated #0021704
                        AND from_date+to_date+COALESCE(freeze_to, '0')::interval > NOW() GROUP BY uid
                    ) pro_users
                WHERE pro_users.date_end>(NOW()+'1 day') AND pro_users.date_end<=(NOW()+'1 day 1 hour');
                ";
        $qusers = $DB->rows($sql);
        if($qusers) {
            foreach($qusers as $user) {
                $mail->PROEnding( $user['uid'], $user['date_end'] );
            }
        }
        return 0;
    }

    /**
     * ����������� �� 3 ��� �� �������� ��� � ��� � ���� �� �������� �������������
     *
     * @return bool
     */
    public function getPROEnding($auto = true, $days = 3) {
        global $DB;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");

        foreach( array('freelancer', 'employer') as $tbl ) {
            $sql = "
                SELECT
                  pro_users.uid, pro_users.date_end, u.email, u.login, a.id as acc_id, substr(u.subscr::text,16,1) = '1' as bill_subscribe,
                  u.uname, u.usurname, u.subscr, date_part('days', date_end - NOW() ) as days_left
                FROM (
                    SELECT uid, MAX(from_date+to_date+COALESCE(freeze_to, '0')::interval) AS date_end
                        FROM orders
                        INNER JOIN {$tbl} u ON from_id=uid
                    WHERE
                        u.is_banned = '0'
                        AND u.is_pro='true'
                        AND u.is_pro_auto_prolong=?
                        -- AND orders.payed='true' AND orders.active='true' -- deprecated #0021704
                        AND from_date+to_date+COALESCE(freeze_to, '0')::interval > NOW()
                    GROUP BY uid
                ) as pro_users
                INNER JOIN {$tbl} u ON u.uid = pro_users.uid
                INNER JOIN account a ON a.uid = u.uid
                WHERE (pro_users.date_end>(NOW()+'{$days} day') AND pro_users.date_end<=(NOW()+'{$days} day 1 hour'));";
            $result[$tbl] = $DB->rows($sql, $auto);
            if(!$result[$tbl]) unset($result[$tbl]); // ���� ������ ���������
        }
        
        if($result) {
            foreach($result as $role => $users) {
                $mail = new smail();
                $mail->remindTimeleftPRO($users, $days);
                
                /*
                 @todo: ������������� ���� ���, ��� ���������� ������
                if(!$auto) {
                    $mail->remindTimeleftPRO($users, $days);
                } else {
                    $mail->remindAutoprolongPRO($users, $role, $days);
                }
                */
                
//                $mail = new smail2();
//                $mail->sendPROEnding(( $role == 'freelancer' ? 'FRL' : 'EMP' ), $users);
            }
            return true;
        }
        return false;
    }

    /**
     * ���� ������������� � ������� �� ��������� PRO �������� ���� ����� � �������� �������������
     * ��������� ���������� ��������������� ������
     */
    function checkAutoPro() {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/bar_notify.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        global $DB;
        foreach( array('freelancer', 'employer') as $tbl ) {
            // ���� � ������ ��� ������ �� ���������� ������, �������� ������ ��� ��� �� ��� �� ���������� ������
            $sql = "
                SELECT pro_users.uid, pro_users.date_end, a.id as acc_id, u.email, u.login, u.uname, u.usurname, u.subscr, substr(u.subscr::text,16,1) = '1' as bill_subscribe,
                (CASE WHEN (pro_users.date_end > (NOW() + '1 hour') AND pro_users.date_end <= (NOW() + '2 hour')) = true
                    THEN 2 -- ������ ������� ���������
                    ELSE 1 -- ������ ������� ���������
                END) as attempt
                FROM (
                    SELECT uid, MAX(from_date+to_date+COALESCE(freeze_to, '0')::interval) AS date_end
                    FROM orders
                    INNER JOIN {$tbl} u ON from_id=uid
                    WHERE u.is_banned = '0'
                        AND u.is_pro = TRUE
                        AND u.is_pro_auto_prolong = TRUE
                        AND from_date + to_date + COALESCE(freeze_to, '0')::interval > NOW()
                    GROUP BY uid
                ) as pro_users
                INNER JOIN {$tbl} u
                    ON u.uid = pro_users.uid AND u.is_banned = B'0'
                INNER JOIN account a ON a.uid = u.uid
                WHERE
                  ( pro_users.date_end > (NOW() + '1 day') AND pro_users.date_end <= (NOW() + '1 day 1 hour') )
                    OR
                  ( pro_users.date_end > (NOW() + '1 hour') AND pro_users.date_end <= (NOW() + '2 hour') )
                ;";

            $result[$tbl] = $DB->rows($sql);
            if(!$result[$tbl]) unset($result[$tbl]); // ���� ������ ���������
        }

        if($result) {
            foreach($result as $role => $users) {
                $op_code = $role === 'freelancer' ? 48 : 15;
                $price   = $role === 'freelancer' ? self::PRICE_FRL_PRO : self::PRICE_EMP_PRO;
                $mail    = new smail();
                foreach($users as $user) {
                    $billing = new billing($user['uid']);
                    $queueID = $billing->create($op_code, 1);
                    if ($queueID) {
                        //������������ ������ ������������� ��� ������
                        $billing->preparePayments($price, false, array($queueID));
                        $complete = billing::autoPayed($billing, $price);

                        $barNotify = new bar_notify($user['uid']);
                        if($complete) {
                            $barNotify->addNotify('bill', '', '������ ������� ��������.');
                            $mail->successAutoprolong(array('user' => $user, 'sum_cost' => $price), 'pro');
                        } else if($user['attempt'] == 1) { // ������ �������
                            $barNotify->addNotify('bill', '', '������ ��������, ������ �� ��������.');
                            $mail->attemptAutoprolong(array('user' => $user, 'sum_cost' => $price), 'pro');
                        } else { // ������ ������� �� �������
                            $barNotify->addNotify('bill', '', '������ ��������, ������������� ���������.');
                            $mail->failAutoprolong(array('user' => $user, 'sum_cost' => $price), 'pro');
                        }
                    };
                }

//                $mail = new smail();
//                $mail->sendAutoPROEnding(( $role == 'freelancer' ? 'FRL' : 'EMP' ), $users);
            }
            return true;
        }
    }

    /**
     * ��� �������� ��������� PRO � ������������
     *
     * @param $attempt ������� ������� ���������
     */
    function checkAutoProTest($login, $attempt = 1) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/bar_notify.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/wallet.php");
        global $DB;

        if($attempt <= 0) {
            $attempt = 1;
        }

        $sql = "
            SELECT users.uid, a.id as acc_id, email, login, uname, usurname, subscr, role, substr(subscr::text,16,1) = '1' as bill_subscribe
            FROM users
            INNER JOIN account a ON a.uid = users.uid
            WHERE users.login = ?";
        $user = $DB->row($sql, $login);
        if (!$user) {
            return;
        }
        $user['date_end'] = date('Y-m-d H:i:s', time());
        $op_code = !is_emp($user['role']) ? 48 : 15;
        $price   = !is_emp($user['role']) ? self::PRICE_FRL_PRO : self::PRICE_EMP_PRO;

        $billing = new billing($user['uid']);
        $queueID = $billing->create($op_code, 1);
        if (!$queueID) {
            return;
        }
        //������������ ������ ������������� ��� ������
        $billing->preparePayments($price, false, array($queueID));
        $complete = billing::autoPayed($billing, $price);

        // @todo ��������� �� ������������� ��� ��� ��� ������ ������� ( �� ���� �� ������� ����� ������ �� ����� )
        // ������������� �� ����� ������� ���������� �� ���� ������������
        $barNotify = new bar_notify($user['uid']);
        $mail = new smail();
        if($complete) {
            $barNotify->addNotify('bill', '', '������ ������� ��������.');
            $mail->successAutoprolong(array('user' => $user, 'sum_cost' => $price), 'pro');
            //$mail->sendAutoPROEnding(( $user['role'] == 'freelancer' ? 'FRL' : 'EMP' ), array($user));
        } else if($attempt == 1) {
            $barNotify->addNotify('bill', '', '������ ��������, ������ �� ��������.');
            $mail->attemptAutoprolong(array('user' => $user, 'sum_cost' => $price), 'pro');
        } else {
            $barNotify->addNotify('bill', '', '������ ��������, ������������� ���������.');
            $mail->failAutoprolong(array('user' => $user, 'sum_cost' => $price), 'pro');
        }
    }


	
	/**
	* ��������� ����������� �� ������������ �������� �������� (���, ������ ��������) (��. ������� op_codes) � ���� ����������� �� ���������� �� ������
	* 
	* @param integer $uid id �����
	* @return integer �� ������, ����� 0
	*/
	function IsUserWasPro($uid)
	{
        global $DB;
		$uid = intval($uid);

		$sql = "SELECT id FROM orders WHERE from_id = ?i AND ordered = '1' -- AND payed = 't' -- deprecated #0021704
                    AND tarif IN (1,2,3,4,5,6,15,16,28,35,42,47,114,48,49,50,51,52,76) LIMIT 1";
        $id = $DB->val($sql, $uid);
        return ($id?$id:0);
	}
	
    
    /**
     * ������� �� ������������ ��� �� ��������� 90 ����
     * 
     * @global DB $DB
     * @param type $uid
     * @return type
     */
    static public function isWasPro($uid)
    {
        global $DB;
        
        $op_codes = array();//array(163, 48, 49, 50, 51);
        
        $sql = "
            SELECT 1 
            FROM orders 
            WHERE 
                from_id = ?i 
                AND ordered = true 
                AND from_date > NOW() - '90 days'::interval 
                " . (!empty($op_codes)?" AND tarif IN(?l)":"") . "
            LIMIT 1
        ";
        
        return (bool)$DB->val($sql, $uid, $op_codes);
    }


    /**
	 * ��������� ������� ��� ������� ��� ��������
	 *
	 * @param integer $fid            ���� (�� ������������)
	 * @param integer $transaction_id �� ����������
	 * @param string  $time           �����
	 * @param string  $comments       ������
	 * @param integer $tarif          ����� (op_codes)
	 * @return integer 0 - ���� �� ����������, 1 - ���� ��� ������ �������
	 */
	function AdminAddPRO($fid, $transaction_id, $time, $comments="������� PRO. ���������� �������� �������", $tarif = 63){
        global $DB;
		require_once(ABS_PATH . "/classes/account.php");
		$account = new account();
		$error = $account->Buy($bill_id, $transaction_id, $tarif, $fid, $comments, $comments);
		if(!$error) {
            $sql = "INSERT INTO orders (from_id, to_date, tarif, ordered, billing_id, payed) VALUES (?, ?, ?, true, ?, true);
                    UPDATE users SET is_pro = true, is_pro_test = false WHERE uid=?;";
            $res = $DB->query($sql, $fid, $time, $tarif, $bill_id, $fid);
        }
        return !!$res;
	}
	
	/**
	 * ���������� � ������� ��������� ��������
	 * 
	 * @param array $data - ���������� �� ��������
	 * @return array ����������
	 */
	function getSuccessInfo($data) {
        global $DB;
	    if(in_array($data['op_code'], array(52,66,67,68))) {
    		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/present.php");
    		$present = new present();
    		return $present->getSuccessInfo($data);
	    }
	    $uid = get_uid(false);
        $sql = "SELECT (o.from_date + o.to_date)::date as to_date FROM users u, orders o WHERE u.uid = ?i AND o.from_id = u.uid AND o.billing_id = ?i";
        $pro = $DB->row($sql, $uid, $data['id']); 
	    $date = date('d.m.Y', strtotime($pro['to_date']));
	           
	    $data['ammount'] = abs($data['ammount']);
	    $suc = array("date"  => $data['op_date'],
	                 "name"  => "������� \"���\" (���� �������� � {$date})",
	                 "descr" => "",
	                 "sum"   => "{$data['ammount']} ���."); 
	    return $suc;                        
	}
	
	/**
	 * ��������� ��������
	 *
	 * @param integer $fid                �� ���� ���������
	 * @param integer $transaction_id     �� ����������     
	 * @param integer $time               �����
	 * @param string  $comments           �������������� �����������
	 * @param integer $tarif              �����
	 * @param integer $ammount            K��������� ������
	 * @param integer $commit             ��������� �� ���������� ����� ���� ��������.
	 * @return unknown
	 */
	function getUpRating($fid, $sum, $transaction_id, $time, $comments="�������� �������� �� FM", $tarif = 75, $ammount=1, $commit = 1) {
	    require_once(ABS_PATH . "/classes/account.php");
		$account = new account();
		$error = $account->Buy($bill_id, $transaction_id, $tarif, $fid, $comments, $comments, $ammount, $commit);
		if ($error!==0) return 1;
		return 0;
	}

    /**
     * ���������� ��������� ������ ��������� ���
     *
     * @param integer $uid �� ������������
     */
    function getLastFreeze($uid) {
        global $DB;
        $uid = intval($uid);

        $sql = "SELECT *, from_time::date AS from_time_date, 
                       (to_time - '1 day'::interval)::date AS to_time_date
                FROM orders_freezing_pro WHERE user_id = ?i
                ORDER BY id DESC LIMIT 1";
        
        $res = $DB->row($sql, $uid);
        $error = $DB->error;

        if (!$error && $res) {
            $fz = self::getFreezedDaysCnt($uid);
            $res['freezed_days'] = 0;
            $res['freezed_cnt'] = 0;
            if ($fz) {
                $res['freezed_days'] = $fz['days'];
                $res['freezed_cnt'] = $fz['cnt'];
            }
            return $res;
        } else {
            return FALSE;
        }
    }

    /**
     * ��������� ����� ������ � ������� �� ���������
     *
     * @param integer $uid �� ������������
     * @param string $from_date ���� ������ ���������
     * @param string $to_date ���� ��������� ���������
     * @return boolean
     */
    function freezePro($uid, $from_date, $to_date) {
        global $DB;
        $uid = intval($uid);
        
        $sql = "INSERT INTO orders_freezing_pro (user_id, from_time, to_time, order_id)
                VALUES (?, ? ::timestamp, (? ::timestamp), 1)";

        $res = $DB->query($sql, $uid, $from_date, $to_date);

        if(!$res) return false;

        return true;
    }

    /**
     * ������ ���������
     *
     * @param integer $uid �� ������������
     * @param integer $freeze_id �� ���������
     * @return boolean
     */
    function freezeProCancel($uid, $freeze_id) {
        global $DB;
        $uid = intval($uid);
        $freeze_id = intval($freeze_id);

        $sql = "DELETE FROM orders_freezing_pro
                WHERE id = $freeze_id AND user_id = $uid";

        $res = $DB->query($sql, $freeze_id, $uid);

        if(!$res) return false;

        return true;
    }

    /**
     * ��������� ����������
     *
     * @param integer $uid �� ������������
     * @param integer $freeze_id �� ���������
     * @return boolean
     */
    function freezeProStop($uid, $freeze_id) {
        global $DB;
        $uid = intval($uid);
        $freeze_id = intval($freeze_id);

        $sql = "UPDATE orders_freezing_pro SET to_time = NOW(), stop_time = NOW()
                WHERE id = $freeze_id AND user_id = $uid";

        $res = $DB->query($sql, $freeze_id, $uid);

        if(!$res) return false;

        return true;
    }

    
    /**
     * ����������� ������� ��� ������� ��������� ���
     * 
     * @global DB $DB
     * @param type $uid
     * @return boolean
     */
    function freezeProDeactivate($uid)
    {
        global $DB;
        $uid = intval($uid);
        
        $data = $DB->row("
            SELECT 
                id,
                (from_time <= NOW() AND to_time > NOW()) AS is_now
            FROM orders_freezing_pro 
            WHERE user_id = ?i
            ORDER BY id DESC LIMIT 1            
        ", $uid);
        
        if($data) {
            return $data['is_now'] == 't'?
                   $this->freezeProStop($uid, $data['id']):
                   $this->freezeProCancel($uid, $data['id']);
        } 
        
        return false;
    }




    /**
     * ��������� ��� �������������, � ������� ���������� ������ ���������.
     * @global DB $DB
     * @return boolean
     */
    function freezeUpdateProUsers() {
        global $DB;
        $sql = "SELECT id FROM settings WHERE module = 'unfreeze' AND variable = 'lastdate' AND value::date = NOW()::date";

        $res = $DB->row($sql);
        if($res) return false;

        $sql = "UPDATE orders_freezing_pro SET stop_time = to_time
                              WHERE to_time <= NOW()::date AND stop_time IS NULL;";
        $sql .= "UPDATE settings SET value = NOW() WHERE module = 'unfreeze' AND variable = 'lastdate';";
        
        $res = $DB->squery($sql);
        if(!$res) return false;

        return true;
    }


    /**
     * ��������� ������ ��������� ���.
     *
     * @return boolean
     */
    function isProFreezed($uid) {
        global $DB;
        $uid = intval($uid);

        $sql = "SELECT *, from_time::date AS from_time_date,
                       (to_time - '1 day'::interval)::date AS to_time_date
                FROM orders_freezing_pro WHERE user_id = ?i
                AND from_time <= NOW() AND to_time > NOW()
                ORDER BY id DESC LIMIT 1";
        $res = $DB->query($sql, $uid);
        $error = $DB->error;

        if (!$error && pg_numrows($res)) {
            return pg_fetch_row($res, null, PGSQL_ASSOC);
        } else {
            return FALSE;
        }
    }

    /**
     * ���������� ���-�� ����, 
     * � ������� ������� ��� ��������� � ������� ����
     *
     * @param integer $uid �� ������������
     */
    function getFreezedDaysCnt($uid) {
        global $DB;
        $uid = intval($uid);
        
        $sql = "SELECT COUNT(*) cnt, CASE WHEN extract('days' from SUM((to_time-'1 sec'::interval) - from_time)) = 0 
            AND SUM(to_time - from_time) != '0'::interval 
            THEN 1 ELSE extract('days' from SUM((to_time-'1 sec'::interval) - from_time)) END days
        FROM orders_freezing_pro WHERE user_id = ?i
        AND date_part('year', from_time) = ?i";
        
        $res = $DB->row($sql, $uid, date('Y'));
        $error = $DB->error;

        if ($res) {
            return $res;
        } else {
            return FALSE;
        }
    }
    
    
    
    /**
     * ������� ������ ��������� ����� �� ��� �/��� �����
     * 
     * @param type $is_emp
     * @return type
     */
    static function getAvailablePayedList($is_emp = false)
    {
        $payed = self::getPayedPROList($is_emp?'emp':'frl');
        
        if(!$is_emp && isAllowProfi()) {
            $payed = isProfi()?self::getPayedPROFIList():array_merge($payed, self::getPayedPROFIList());
        }
        
        return $payed;
    }


    /**
     * ������ ����� �� �����
     * 
     * @return int
     */
    static function getPayedPROFIList()
    {
        $payed = array();
        
        $payed[] = array(
            'week'  => 0,
            'month'  => 1,
            'cost'   => 5990,
            'opcode' => 164            
        );
        
        return $payed;
    }



    /**
     * ���������� �� ���� ��� ����� ������ ( �� 1,3,6,12 �������) 
     * 
     * @return array
     */
    static function getPayedPROList($role = 'frl') {
        
        if($role == 'frl') {
            $payed = array(
                /*
                 * @todo: �������� �� https://beta.free-lance.ru/mantis/view.php?id=28753
                0 => array(
                    'day'  => 1,
                    'month'  => 1,
                    'cost'   => 99,
                    'opcode' => 132
                ),
                1 => array(
                    'week'  => 1,
                    'month'  => 1,
                    'cost'   => 299,
                    'opcode' => 131
                ),*/
                2 => array(
                    'month'  => 1,
                    'cost'   => 899,
                    'opcode' => 48,
                    'sale_txt' => '������ �����'
                ),
                3 => array(
                    'month'  => 3,
                    'cost'   => 2499,
                    'opcode' => 49,
                    'sale' => '7%', //�������� � %
                ),
                /*
                4 => array(
                    'month'  => 6,
                    'cost'   => 4599,
                    'opcode' => 50
                ),*/
                5 => array(
                    'month'  => 12,
                    'cost'   => 8199,
                    'opcode' => 51,
                    'sale' => '24%' //�������� � %
                )
            );            
            
            //n����� �������� ��� �� �����    
            if (isAllowTestPro()) {
                $payed[2] = array(
                        'week'  => 0,
                        'month'  => 1,
                        'cost'   => self::getPriceByOpCode(163),
                        'opcode' => 163,
                        'badge' => '�����',
                        'old_cost' => self::getPriceByOpCode(48),
                        'sale_txt' => '������ �����',
                        'class' => 'b-promo__buy_test_pro'
                    );
            }
            
        } else {
            $payed = array(
                array(
                    'month'  => 1,
                    'cost'   => 899,
                    'opcode' => 15,
                    'sale_txt' => '������ �����',
                    //'class' => 'b-promo__buy_min-width_250'
                ),
                array(
                    'month'  => 3,
                    'cost'   => 2499,
                    'opcode' => 118,
                    'sale' => '7%', //�������� � %
                    //'class' => 'b-promo__buy_min-width_250'
                ),/*
                array(
                    'month'  => 6,
                    'cost'   => 4599,
                    'opcode' => 119,
                    'sale' => '15%', //�������� � %
                    //'class' => 'b-promo__buy_min-width_250'
                ),*/
                array(
                    'month'  => 12,
                    'cost'   => 8199,
                    'opcode' => 120,
                    'sale' => '24%', //�������� � %
                    //'class' => 'b-promo__buy_min-width_250'
                )
            );
        }
        
        return $payed;
    }
    
    static function getPriceByOpCode($opCode)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/op_codes.php';
        $price = op_codes::getPriceByOpCode($opCode);
        return ceil($price * 10);
    }
    
    
    /**
     * ��������� ������ � ��� � ������ ������������
     * @return boolean
     */
    public static function updateUserSession()
    {
        if (!$_SESSION['login']) {
            return false;
        }
        
        $pro_last = payed::ProLast($_SESSION['login']);
        
        $_SESSION['pro_last'] = $pro_last['is_freezed'] ? false : $pro_last['cnt'];
        
        if ($pro_last['freeze_to']) {
            $_SESSION['freeze_from'] = $pro_last['freeze_from'];
            $_SESSION['freeze_to'] = $pro_last['freeze_to'];
            $_SESSION['is_freezed'] = $pro_last['is_freezed'];
            $_SESSION['payed_to'] = $pro_last['cnt'];
        }
        
        return true;
    }
}