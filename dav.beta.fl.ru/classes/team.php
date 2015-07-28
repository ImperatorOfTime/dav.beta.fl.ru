<?
/**
 * ���������� ���� � ��������� ���������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/*
 * ����� �������� ������� http://free-lance.ru/team/
 *
 */
class team 
{

	/**
	 * ������� ����� ������
	 *
	 * @param string $title	�������� ������
	 * @return boolean ������ true
	 */
	function CreateGroup($title,$position)
	{
        //�� ������ ������ ����� ��������� ����������� ������� ������� ���� �����
		$res = $GLOBALS['DB']->query("SELECT id, position FROM team_groups ORDER BY position, id");
		$i = 1;
		$sql = "";
		while ($ret = pg_fetch_assoc($res))
		{
			$sql .= "UPDATE team_groups SET position='$i' WHERE id='{$ret['id']}'; ";
			$i++;
		}
		$GLOBALS['DB']->query($sql);

        $i--;
        if($position>$i) {
            $position = $i+1;
        } else {
            $GLOBALS['DB']->query("UPDATE team_groups SET position=(position+1) WHERE position >= ?i", $position);
        }

		$GLOBALS['DB']->insert('team_groups', array('title' => $title, 'position' => $position));

		return TRUE;
	}

    /**
    * ������������� ������ �������� � ����������
    *
    * @param    string  $order  ������� ������ �������� � ����������
    *
    */
    function ReorderTeam($order) {
        global $DB;
        $DB->clear();
        $p = preg_split("/\|/",$order);
        $g = self::GetAllGroups();
        $sql = '';
        foreach($p as $g_num=>$p_in_c) {
            if($p_in_c!='') {
                $team_d = preg_split("/,/",$p_in_c);
                foreach($team_d as $teams) {
                    if($teams!='') {
                        $team = preg_split("/=/",$teams);
                        $DB->hold()->query("UPDATE team_people SET groupid = ?i, position = ?i WHERE id = ?", $g[$g_num]['id'], ($team[1]+1), $team[0]);
                    }
                }
            }
        }
        $DB->query();
    }

	/**
	 * ����������� ������
	 *
	 * @param integer $groupid		id ������
	 * @param string $title			�������� ������
	 * @return boolean ������ true
	 */
	function EditGroup($groupid, $title, $position)
	{
        //�� ������ ������ ����� ��������� ����������� ������� ������� ���� �����
		$res = $GLOBALS['DB']->query("SELECT id, position FROM team_groups ORDER BY position, id");
		$i = 1;
		$sql = "";
		while ($ret = pg_fetch_assoc($res))
		{
			$sql .= "UPDATE team_groups SET position='$i' WHERE id='{$ret['id']}'; ";
			$i++;
		}
		$GLOBALS['DB']->query($sql);

        $i--;
        if($position>$i) {
            $position = $i;
        }

		$g = $GLOBALS['DB']->row("SELECT * FROM team_groups WHERE id = ?", $groupid);

		$GLOBALS['DB']->query("UPDATE team_groups SET position=(position-1) WHERE id <> ?i AND position > ?i", $groupid, $g['position']);

		$GLOBALS['DB']->query("UPDATE team_groups SET title = ?, position = ?i WHERE id = ?", $title, $position, $groupid);

		$GLOBALS['DB']->query("UPDATE team_groups SET position=(position+1) WHERE id <> ?i AND position >= ?i", $groupid, $position);

		return TRUE;
	}

	/**
	 * ������� ������ � ���� �� ������
	 *
	 * @param integer $groupid		id ������
	 * @return boolean ������ true
	 */
	function DeleteGroup($groupid)
	{
		$GLOBALS['DB']->query("
			DELETE FROM team_groups WHERE id = ?;
			DELETE FROM team_people WHERE groupid = ?;
		", $groupid, $groupid);

		$res = $GLOBALS['DB']->query("SELECT id, position FROM team_groups ORDER BY position, id");

		$i = 1;
		$sql = "";
		while ($ret = pg_fetch_assoc($res))
		{
			$sql .= "UPDATE team_groups SET position='$i' WHERE id='{$ret['id']}'; ";
			$i++;
		}
		$res = $GLOBALS['DB']->query($sql);

		return TRUE;
	}

	/**
	 * ���������� ������������ ������ �� id
	 *
	 * @param integer groupid		id ������
	 * @return array
	 */
	function GetGroup($groupid)
	{
		return $GLOBALS['DB']->row("SELECT id, title FROM team_groups WHERE id = ? LIMIT 1", $groupid);
	}

	/**
	 * ���������� ������ � id � title ���� �����
	 *
	 * @return array
	 */
	function GetAllGroups()
	{
		return $GLOBALS['DB']->rows("SELECT id, title, position FROM team_groups ORDER BY position, id");
	}

	/**
	 * ���������� ������ �� ������
	 *
	 * @param string moveto		��� ����������� up/down
	 * @param integer groupid	������, ������� ������� �����������
	 * @return boolean ������ true
	 */
	function MoveGroup($moveto, $groupid)
	{
		if ($moveto == "up" || $moveto == "down")
		{
			//�� ������ ������ ����� ��������� ����������� ������� ������� ���� �����
			$res = $GLOBALS['DB']->query("SELECT id, position FROM team_groups ORDER BY position, id");

			$i = 1;
			$sql = "";

			while ($ret = pg_fetch_assoc($res))
			{
				$sql .= "UPDATE team_groups SET position='$i' WHERE id='{$ret['id']}'; ";
				$i++;
			}

			$res = $GLOBALS['DB']->query($sql);

			$ret_from = $GLOBALS['DB']->row("SELECT id, position FROM team_groups WHERE id = ? LIMIT 1", $groupid);

			$ret_to = $GLOBALS['DB']->row(
				"SELECT id, position FROM team_groups WHERE position ".(($moveto=="up")?"<":">")." ?i ORDER BY position ".(($moveto=="up")?"DESC":"ASC")." LIMIT 1",
				$ret_from['position']
			);

			if ($ret_to['id'] && $ret_from['id'])
			{
				$sql  = "UPDATE team_groups SET position='{$ret_from['position']}' WHERE id='{$ret_to['id']}'; ";
				$sql .= "UPDATE team_groups SET position='{$ret_to['position']}' WHERE id='{$ret_from['id']}'; ";
				$res = $GLOBALS['DB']->query($sql);
			}
		}
	
		return TRUE;
	}

	/**
	 * ���������� ������������ �� ������
	 *
	 * @param string moveto		��� ����������� left/right
	 * @param integer groupid	������, ������������ ������� ������� �����������
	 * @param integer userid	������������, �������� ������� �����������
	 * @return boolean ������ true
	 */
	function MoveUser($moveto, $groupid, $userid)
	{
		if ($moveto == "left" || $moveto == "right")
		{
			$res = $GLOBALS['DB']->query("SELECT id, position FROM team_people WHERE groupid = ? ORDER BY position, id", $groupid);

			$i = 1;
			$sql = "";

			while ($ret = pg_fetch_assoc($res))
			{
				$sql .= "UPDATE team_people SET position='$i' WHERE id='{$ret['id']}'; ";
				$i++;
			}

			$res = $GLOBALS['DB']->query($sql);

			$ret_from = $GLOBALS['DB']->row("SELECT id, position FROM team_people WHERE id = ? LIMIT 1", $userid);

			$ret_to = $GLOBALS['DB']->row(
				"SELECT id, position FROM team_people WHERE position ".(($moveto=="left")?"<":">")." ?i AND groupid='".$groupid."' ORDER BY position ".(($moveto=="left")?"DESC":"ASC")." LIMIT 1",
				$ret_from['position']
			);

			if ($ret_to['id'] && $ret_from['id'])
			{
				$res = $GLOBALS['DB']->query("
					UPDATE team_people SET position = ? WHERE id = ?;
					UPDATE team_people SET position = ? WHERE id = ?;
				", $ret_from['position'], $ret_to['id'], $ret_to['position'], $ret_from['id']);
			}
		}
		
		return true;
	}

	/**



	 * ������� ������ ������������ � ������������ ������
	 *

	 * @param string name			���
	 * @param string login			�����
	 * @param string occupation		���������
	 * @param string userpic		��������
	 * @param integer groupid 		id ������. � ������� ����������� ������������
     * @param integer position      ����� �� �������
     * @param string info           �������������� ����������
	 * @return ineger               ID ������������ ������������
	 */
	function AddUser($name, $login, $occupation, $userpic, $groupid, $position, $info)
	{
		//�� ������ ������ ����� ��������� ����������� ������� ������� ���� ������������� � ������
		$res = $GLOBALS['DB']->query("SELECT id, position FROM team_people WHERE groupid = ? ORDER BY position, id", $groupid);

		$i = 1;
		$sql = "";
		while ($ret = pg_fetch_assoc($res))
		{
			$sql .= "UPDATE team_people SET position='$i' WHERE id='{$ret['id']}'; ";
			$i++;
		}
		$res = $GLOBALS['DB']->query($sql);

        $i--;
        if($position>$i || $i==0) {
            $position = $i+1;
        } else {
            $GLOBALS['DB']->query("UPDATE team_people SET position=(position+1) WHERE position >= ?i", $position);
        }

		$uid = $GLOBALS['DB']->insert('team_people', array(
			'name'       => $name,
			'occupation' => $occupation,
			'login'      => $login,
			'userpic'    => $userpic,
			'groupid'    => $groupid,
			'position'   => $position,
			'info'       => $info
		), 'id');

		return $uid;
	}

	/**
	 * ����������� ������������ � ������������ ������
	 *
	 * @param string userid			id ������������
	 * @param string name			���
	 * @param string login			�����
	 * @param string occupation		���������
	 * @param string userpic		��������
	 * @param integer groupid 		id ������. � ������� ����������� ������������
     * @param integer position      ����� �� �������
     * @param string info           �������������� ����������
	 * @return ineger               ID ������������ ������������
	 */
	function EditUser($id, $name, $login, $occupation, $userpic, $groupid, $position, $info)
	{
		$u = $GLOBALS['DB']->row("SELECT * FROM team_people WHERE id = ?", $id);

        if($userpic) {
            $cfile = new CFile();
            $cfile->Delete(0,'team/'.$u['userpic']);
        }

		//�� ������ ������ ����� ��������� ����������� ������� ������� ���� ������������� � ������
		$res = $GLOBALS['DB']->query("SELECT id, position FROM team_people WHERE groupid = ? ORDER BY position, id", $groupid);

		$i = 1;
		$sql = "";
		while ($ret = pg_fetch_assoc($res))
		{
			$sql .= "UPDATE team_people SET position='$i' WHERE id='{$ret['id']}'; ";
			$i++;
		}
		$res = $GLOBALS['DB']->query($sql);
        $i--;

        if($position>$i) {
            $position = $i;
        }

		$GLOBALS['DB']->query("UPDATE team_people SET position=(position-1) WHERE id <> ?i AND position > ?i", $id, $u['position']);

        if($userpic) {
		    $GLOBALS['DB']->update('team_people', array(
				'name'       => $name,
				'occupation' => $occupation,
				'login'      => $login,
				'userpic'    => $userpic,
				'groupid'    => $groupid,
				'info'       => $info,
				'position'   => $position
			), "id = ?", $id);
        } else {
		    $GLOBALS['DB']->update('team_people', array(
				'name'       => $name,
				'occupation' => $occupation,
				'login'      => $login,
				'groupid'    => $groupid,
				'info'       => $info,
				'position'   => $position
			), "id = ?", $id);
        }

		$GLOBALS['DB']->query("UPDATE team_people SET position=(position+1) WHERE id <> ?i AND position >= ?i", $id, $position);

		return TRUE;
	}

	/**
	 * ������� ������������
	 *
	 * @param integer $userid		id ������
	 *
	 */
	function DeleteUser($userid)
	{
        $groupid = $GLOBALS['DB']->val("SELECT groupid FROM team_people WHERE id = ?", $userid);
        
		$GLOBALS['DB']->query("DELETE FROM team_people WHERE id = ?", $userid);

		//�� ������ ������ ����� ��������� ����������� ������� ������� ���� ������������� � ������
		$res = $GLOBALS['DB']->query("SELECT id, position FROM team_people WHERE groupid = ? ORDER BY position, id", $groupid);

		$i = 1;
		$sql = "";
		while ($ret = pg_fetch_assoc($res))
		{
			$sql .= "UPDATE team_people SET position='$i' WHERE id='{$ret['id']}'; ";
			$i++;
		}
        if($sql) {
			$res = $GLOBALS['DB']->query($sql);
        }

		return true;
	}

	/**

	 * ������� ���� ������������
	 *
	 * @param integer $id		id ������������
	 *
	 */
	function DeletePhoto($id)
	{
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
		$userpic = $GLOBALS['DB']->val("SELECT userpic FROM team_people WHERE id = ?", $id);
        $cfile = new CFile();
        @$cfile->Delete(0,'/team/',$userpic);
		$GLOBALS['DB']->query("UPDATE team_people SET userpic = '' WHERE id = ?", $id);
		return TRUE;
	}

	/**
	 * ���������� ���� ������������� �� ������������ ������
	 *
	 * @param integer groupid		id ������
	 * @return array
	 */
	function GetGroupUsers($groupid)
	{
		return $GLOBALS['DB']->rows("SELECT * FROM team_people WHERE groupid = ? ORDER BY position, id", $groupid);
	}

	/**
	 * ���������� ������������� ������������ �� id
	 *
	 * @param integer userid		id ������������
	 * @return array
	 */
	function GetUser($userid)
	{
		return $GLOBALS['DB']->row("SELECT * FROM team_people WHERE id = ? LIMIT 1", $userid);
	}

    /**
    * �������� ���������� ������������
    *
    * @param    object  $foto   ����������
    *
    * @return boolean           true - ���� ����������, false - ������
    */
    function UpdateFoto($foto) {
        $error = 0;
        if ($foto) {
            $foto->max_size = 100000;
            $foto->max_image_size = array('width'=>150, 'height'=>200);
            $foto->resize = 1;
            $foto->proportional = 1;
            $foto->topfill = 1;
            $foto->server_root = 1;
            $photo = $foto->MoveUploadedFile("team/");
            if($foto->StrError()) {
                $error = 1;
            }
        }

        return (array('error'=>$error,'foto'=>$photo));
    }
}

?>
