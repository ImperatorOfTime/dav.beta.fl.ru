<?
/**
 * ���������� ���� � ��������� ���������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ������ � �������� "��������"
 *
 */
class interview 
{
	/**
	 * �������� ����� ��������
	 *
	 * @param integer  $uid       �� ������������
	 * @param array    $questions �������
	 * @return integer  ID ���� ��� ��, 0 - ���� �� ����������.
	 */
	function Add($uid, $questions){
	    $data = array();
		foreach($questions as $ikey=>$value){
			if ($value != '') {
				$data["q".($ikey+1)] = change_q_x($value, false, false);
			}
		}
		if($uid && $data){
		    $data['from_id'] = $uid;
		    
		    global $DB;
			return $DB->insert( 'interview', $data, 'id' );
		}
		return 0;
	}
	
	/**
	 * �������� ��������
	 *
	 * @param integer  $uid       �� ��������
	 * @param array    $questions �������
	 * @return integer ������ ���������� 0
	 */
	function Update($id, $questions)
	{
	    $data = array();
		foreach($questions as $ikey=>$value) {
		  if($value != '') {
				$data["q".($ikey+1)] = change_q_x($value, false, false);
			}
		}

		if($id && $qstn){
			global $DB;
			$DB->update( 'interview', $data, 'id=?', $id );
		}

		return 0;
	}
	
	/**
	 * �������������� ����������
	 *
	 * @param integer $id     ��
	 * @param object  $attach ���������� ����� (��. ����� CFile)
	 * @param array   $photo  ����� (��. ����� CFile)
	 * @return integer
	 */
	function EditPhotos($id, $attach, $photo){
	    if (!$id) return 0;
	    // ��������� ��������� �� ����� � ������������ �� ���� ��� ����
		if ($attach->size > 0){
		    $fn = 0;
		    $attach->proportional = 1;
			$f_name = $attach->MoveUploadedFile($_SESSION['login']."/upload");
			if (!isNulArray($attach->error)) {$error_flag = 1; $alert[2] = "���� �� ������������� �������� ��������";}
			else {
				$ext = $attach->getext();
				if (in_array($ext, $GLOBALS['graf_array']) && $ext != "swf") {
					if (!$attach->image_size['width'] || !$attach->image_size['height']) {$error_flag = 1; $alert[2] = "���������� ��������� ��������.";}
					if ($attach->image_size['width'] > 200 || $attach->image_size['height'] > 1000)
						if (!$attach->img_to_small("sm_".$f_name, array('width'=>200,'height'=>1000, 'less' => 0)))
						{$error_flag = 1; $alert[2] = "���������� ��������� ��������.";} else $fn = 2;
					else $fn = 1;
				}
			}
		}
		// ���� ���� �����
	    if ($photo) {
	    	// ��������� �����
	    	foreach($photo as $ikey => $wrk) {
			    $tn = 0;
				$w_name = $wrk->MoveUploadedFile($_SESSION['login']."/upload");
			    if (!isNulArray($wrk->error)) {$error_flag = 1; $alert[2] = "���� �� ������������� �������� ��������";}
				else {
				    $ext = $wrk->getext();
				    $wrk->proportional = 1;
					if (in_array($ext, $GLOBALS['graf_array']) && $ext != "swf") {
						if (!$wrk->image_size['width'] || !$wrk->image_size['height']) {$error_flag = 1; $alert[2] = "���������� ��������� ��������.";}
						if ($wrk->image_size['width'] > 200 || $wrk->image_size['height'] > 1000)
							if (!$wrk->img_to_small("sm_".$w_name, array('width'=>200,'height'=>1000, 'less' => 0)))
							{$error_flag = 1; $alert[2] = "���������� ��������� ��������.";} else $tn = 2;
						else $tn = 1;
					}
				}
				if(!$error_flag) {
					$qstn[] = "file".($ikey+1)."='$w_name'";
					$qstv[$ikey] = "file".($ikey+1);
					$qsts[$ikey] = "pt".($ikey+1);
					$qstp[] = "pt".($ikey+1)."='".$tn."'";
				}
				$w_name="";
			}
	    }
	    
	    // ���������� ����� ������������� �����
		if($id && ($qstn || $f_name)) {
		    global $DB;
			$sql = "SELECT ft, foto".(isset($qstv) ? ",".implode(", ", $qstv).", ".implode(", ", $qsts) : "")." FROM interview WHERE id=?";
			$ret = $DB->row( $sql, $id );
			if ($ret['foto'] && $f_name) {
			    $file = new CFile();
			    $file->Delete(0,"users/".substr($_SESSION['login'],0,2)."/".$_SESSION['login']."/upload/",$ret['foto']);
			    if ($ret['ft'] == 2) $file->Delete(0,"users/".substr($_SESSION['login'],0,2)."/".$_SESSION['login']."/upload/","sm_".$ret['foto']);
			}
			if ($f_name) $af = "foto = '$f_name', ft = '$fn'";
			if ($qstn)	{
				foreach($qstv as $ikey => $value){
					if ($ret[$value] && $qstn[$ikey]) { 
					    $file = new CFile();
					    $file->Delete(0,"users/".substr($_SESSION['login'],0,2)."/".$_SESSION['login']."/upload/",$ret[$value]);
					    if ($ret[$qsts[$ikey]] == 2) $file->Delete(0,"users/".substr($_SESSION['login'],0,2)."/".$_SESSION['login']."/upload/","sm_".$ret[$value]);
					}
				}
				$af .= (isset($af)?",":"").implode(", ", $qstn).", ".implode(", ", $qstp);
			}
			$sql = "UPDATE interview SET ".$af." WHERE id=?";
			$res = $DB->query( $sql, $id );
		}
		return 0;
	}
	
	/**
	 * ����� ���������� �� ��������
	 *
	 * @param integer $uid �� ������������
	 * @return array
	 */
	function GetInfo($uid){
	    global $DB;
		$sql = "SELECT * FROM interview WHERE from_id=?";
		$ret = $DB->row( $sql, $uid );
		return $ret;
	}
	
	/**
	 * ����� �� ���� ��� ������� ��������
	 *
	 * @param integer $id �� ��������
	 * @return integer
	 */
	function GetUID($id){
	    global $DB;
		$sql = "SELECT from_id FROM interview WHERE id=?";
		return intval( $DB->val($sql, $id) );
	}
	
	/**
	 * ����� ������ ���� �� �������� (��� ����� ������ ������������)
	 *
	 * @param integer $id �� ��������
	 * @return array ������ �������
	 */
	function GetInfo2($id){
	    global $DB;
		$sql = "SELECT interview.*, login, usurname, uname, role, uid FROM interview LEFT JOIN users ON uid=from_id WHERE id=?";
		return $DB->row( $sql, $id );
	}

    /**
     * ���������� ������ ����������� ��� ���������� ��� ������
     *
     * @param     integer    $id    ID ��������
     * @return    array             ������ ��� ���
     */
    public function getInfoForFriendlyURL($id) {
        global $DB;
        $sql = "SELECT login, usurname, uname FROM interview_new LEFT JOIN users ON uid=user_id WHERE id=?";
        return $DB->cache(1800)->row($sql, $id);
    }
	
	/**
	 * �������� ����������.
	 *
	 * @param integer $id �� ��������
	 * @return array $ret ������ ��������� �����
	 */
	function Deletefoto($id){
	    global $DB;
		$sql = "SELECT foto AS file, ft AS small FROM interview WHERE id=?";
		
		extract( $DB->row($sql, $id) );
		
	    if ($file) {
		    $cfile = new CFile();
		    $cfile->Delete(0,"users/".substr($_SESSION['login'],0,2)."/".$_SESSION['login']."/upload/",$file);
		    if ($small == 2) $cfile->Delete(0,"users/".substr($_SESSION['login'],0,2)."/".$_SESSION['login']."/upload/","sm_".$file);
		}
		$sql = "UPDATE interview SET foto='', ft=0 WHERE id=?";
		$ret = $DB->row( $sql, $id );
		return $ret;
	}
	
	/**
	 * �������� ������
	 *
	 * @param integer $id  �� ��������
	 * @param integer $fid �� �����
	 * @return array $ret ������ ��������� ������
	 */
	function Del($id, $fid){
	    global $DB;
		$sql = "SELECT file$fid AS file, pt$fid AS small FROM interview WHERE id=?";
		
		extract( $DB->row($sql, $id) );
		
	    if ($file) {
		    $cfile = new CFile();
		    $cfile->Delete(0,"users/".substr($_SESSION['login'],0,2)."/".$_SESSION['login']."/upload/",$file);
		    if ($small == 2) $cfile->Delete(0,"users/".substr($_SESSION['login'],0,2)."/".$_SESSION['login']."/upload/","sm_".$file);
		}
		$sql = "UPDATE interview SET file$fid='', pt$fid=0 WHERE id=?";
		$ret = $DB->row( $sql, $id );
		return $ret;
	}
	
	/**
	 * ���������� ��� �������� ����������� � �� � ��������������� �� ��
	 *
	 * @return array
	 */
	function GetAll(){
	    global $DB;
		$sql = "SELECT interview.*, login, usurname, uname, role, uid, email FROM interview LEFT JOIN users ON uid=from_id ORDER BY id";
		$ret = $DB->rows( $sql );
		return $ret;
	}
	
	/**
	 * �������� ��������
	 *
	 * @param integer $id �� ��������
	 * @return integer
	 */
	function DelInterv($id){
		for ($i = 1; $i < 8; $i++ ){
			$fls .= ", file".$i.", pt".$i;
		}
		global $DB;
		$sql = "SELECT foto as file0, ft as pt0 ".$fls." FROM interview WHERE id=?";
		$row = $DB->row( $sql, $id );
	    for ($i = 0; $i < 8; $i++ ){
	        if ($row['file'.$i]){
	            $cfile = new CFile();
		        $cfile->Delete(0,"users/".substr($_SESSION['login'],0,2)."/".$_SESSION['login']."/upload/",$row['file'.$i]);
		        if ($row['pt'.$i] == 2) $cfile->Delete(0,"users/".substr($_SESSION['login'],0,2)."/".$_SESSION['login']."/upload/","sm_".$row['file'.$i]);
	        }
		}
		$sql = "DELETE FROM interview WHERE id=?";
		$res = $DB->query( $sql, $id );
		return 0;
	}

    /**
     * �������� ���� �������� �� ID, ���� ������ ���� $id == null
     *
     * @param integer $uid �� ������������
     * @param integer $id
     * @param string $order ��� ���������� date|views
     * @return array
     */
    function getInterview($uid, $id = null, $order = 'date', $year = null, $filter=0) {
    	global $DB;
        $users_inner_sql =  "";
        switch($filter) {
            case 1:
                $users_inner_sql = "INNER JOIN freelancer as u ON u.uid = i.user_id";  
                $where = "is_jury = 'f'";  
                break;
            case 2:
                $users_inner_sql = "INNER JOIN employer as u ON u.uid = i.user_id";  
                $where = "is_jury = 'f'";
                break;
            case 3:
                $users_inner_sql = "INNER JOIN users as u ON u.uid = i.user_id";
                $where = "is_jury = 't'";
                break;
            default:
                $users_inner_sql = "INNER JOIN users as u ON u.uid = i.user_id";
                $where = "";
                break;
        }
        
        $sql = $DB->parse("SELECT i.*,
                    f.path, f.fname, f.width, f.height,
                    u.uname, u.usurname, u.login,
                    iu.lastviewtime
                FROM interview_new i
                LEFT JOIN interview_users iu ON iu.user_id = ?i AND iu.interview_id = i.id
                {$users_inner_sql} 
                LEFT JOIN file as f ON f.id = i.main_foto", $uid);
        if($id) {
            $sql .= $DB->parse(" WHERE i.id = ?i" . ($where!=""?" AND ".$where:""), $id);
        } else {
            if($year) {
                $sql .= $DB->parse(" WHERE date_part('year', i.post_time) = ?" . ($where!=""?" AND ".$where:""), $year );
            } elseif($where != "") {
                $sql .= " WHERE {$where}";
            }

            switch($order) {
                case 'views':
                    $sql .= " ORDER by i.view_cnt DESC, i.post_time DESC";
                    break;
                default:
                    $sql .= " ORDER by i.post_time DESC";
            }
        }
        
        $res = $DB->query( $sql );
        $ret = $id ? pg_fetch_assoc($res) : pg_fetch_all($res);

        return $ret;
    }


    /**
     * ������������� ����� ���������� ��������� �������� �������������
     *
     * @param integer $user_id �� ������������
     * @param array $interview ��������� interview::getInterview()
     * @return boolean
     */
    function setInterviewLVT($user_id, $interview) {
        if ( !$user_id ) return false;
        
        global $DB;
        $interview_id = $interview['id'];

        if ( $interview['lastviewtime'] === NULL ) {
            $sql = "INSERT INTO interview_users (user_id, interview_id, lastviewtime)
                        VALUES (?i, ?i, NOW())";
            if ( !$DB->query($sql, $user_id, $interview_id) ) {
                return false;
            }
            $sql = "UPDATE interview_new SET view_cnt = view_cnt+1
                        WHERE id = ?i";
            if ( !$DB->query($sql, $interview_id) ) {
                return false;
            }
        } 
        else {
            $sql = "UPDATE interview_users SET lastviewtime = NOW()
                        WHERE interview_id = ?i AND user_id = ?i";
            if ( !$DB->query($sql, $interview_id, $user_id) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * �������� ��������
     * 
     * @param int $uid ID ������������, � �������� ����� ��������
     * @param string $text ����� ��������
     * @param array $files ����� ������������� � ��������
     */
    function addInterview($uid, $text, $files = array(), $is_jury = 'f') {
        $mainfile = isset($files['main']) ? intval($files['main']) : NULL;

        global $DB;
        $sql = "INSERT INTO interview_new (user_id, txt, post_time, main_foto, is_jury)
                VALUES (?i, ?, NOW(), ?, ?)
                RETURNING id";
        
        $id = $DB->val( $sql, $uid, $text, $mainfile, $is_jury );

        if( isset($files['ex']) ) {
            foreach ($files['ex'] as $file ) {
                if(!intval($file)) continue;
                
                $DB->insert( 'interview_files', array('interview_id'=>$id, 'file_id'=>$file) );
            }
        }

        return $id;
    }

    /**
     * ���������� ��������
     * 
     * @param int $id ID ��������
     * @param int $uid ID ������������, � �������� ����� ��������
     * @param string $text ����� ��������
     * @param array $files ����� ������������� � ��������
     */
    function updateInterview($id, $uid, $text, $files = array(), $is_jury) {
        $mainfile = isset($files['main']) ? ", main_foto = '{$files['main']}'" : '';
        
        global $DB;
        $sql = "UPDATE interview_new SET user_id = ?i,
                    txt = ?,
                    is_jury = ?,
                    mod_time = NOW() $mainfile
                WHERE id = ?i";
        
        $res = $DB->query( $sql, $uid, $text, $is_jury, $id );

        if( isset($files['ex']) ) {
            foreach ($files['ex'] as $file ) {
                if(!intval($file)) continue;
                
                $DB->insert( 'interview_files', array('interview_id'=>$id, 'file_id'=>$file) );
            }
        }

        return $res;
    }

    /**
     * �������� ��������
     *
     * @param  integer $id �� ��������
     * @return resource ��������� �������
     */
    function delInterview($id) {
        global $DB;
        $res = $DB->query( 'DELETE FROM interview_new WHERE id = ?', $id );
        return $res;
    }

    /**
     * ��������� ������ �����
     *
     * @param  integer $interview_id
     * @return array
     */
    function getInterviewFiles($interview_id) {
        global $DB;
        $sql = "SELECT f.id, f.path, f.fname FROM interview_files as i 
                INNER JOIN file as f ON f.id = i.file_id
                WHERE i.interview_id = ?";

        $res = $DB->query( $sql, $interview_id );
        $ret = pg_fetch_all($res);

        return $ret;
    }
    
    /**
     * ���������� ������ ��� ��������� ����������-���������
     *
     * @param  array $interview ������� ��������
     * @param  string $order �����������. ���� ��� ����������. �� ������������.
     * @return array
     */
    function getNavigation($interview, $order = 'date') {
        switch($order) {
//            case 'views':
//                $field = "view_cnt";
//                break;
            default:
                $field = "post_time";
        }
        
        $sql = "SELECT NULL as id, NULL as pos, NULL as uname, NULL as usurname, NULL as login, NULL as $field
                UNION
                (SELECT id, 1 as pos, uname, usurname, login, $field FROM interview_new i 
                LEFT JOIN users as u ON u.uid = i.user_id 
                WHERE $field > '{$interview[$field]}' ORDER by $field ASC, id ASC LIMIT 1)
                UNION
                (SELECT id, 2 as pos, uname, usurname, login, $field FROM interview_new i 
                LEFT JOIN users as u ON u.uid = i.user_id 
                WHERE $field < '{$interview[$field]}' ORDER by $field DESC, id DESC LIMIT 1)
                ORDER by $field DESC";
        
        global $DB;
        $res = $DB->squery( $sql );
        $ret = pg_fetch_all( $res );

        return $ret;
    }
    
    /**
     * ���������� ������ �����, � ������� ������� ��������
     * 
     * @return array
     */
    function getYears() {
        global $DB;
        $ret = $DB->rows( "SELECT date_part('year', post_time) as yr FROM interview_new GROUP BY yr ORDER BY yr DESC" );

        return $ret;
    }
    
    /**
     * ����� �������� �� �� ��
     * 
     * @global type $DB
     * @param array $ids
     * @return boolean
     */ 
    public static function getInterviewById($ids) {
        global $DB;
        
        if(!is_array($ids)) return false;
        
        $sql = "SELECT 
                    i.*, u.login, u.usurname, u.uname, u.role, u.uid, u.email
                FROM interview_new i
                LEFT JOIN users u ON u.uid = i.user_id 
                WHERE i.id IN(?l)";
        
        $res = $DB->rows($sql, $ids);
        return $res;
    }
}
?>