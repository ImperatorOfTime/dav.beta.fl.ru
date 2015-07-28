<?
/**
 * ���������� ���� � ��������� ���������
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ������ � ������� ������� �������������
 *
 */
class permissions {

    /**
     * �������� ���������� � ������ � �� ������ �������
     *
     * @param   integer $id     ������������� ������
     * @param   string  $name   �������� ������
     * @param   array   $rights ����� ������� ������
     */
    function updateGroup($id, $name, $rights) {
        global $DB;

        $sql = "UPDATE permissions_groups SET name=? WHERE id=?i AND id<>0";
        $DB->query($sql, $name, $id);

        $sql = "SELECT * FROM permissions_groups_rights WHERE group_id=?i";
        $drights = $DB->rows($sql, $id);
        if($drights) {
            foreach($drights as $right) {
                $sql = "DELETE FROM permissions_rights_users WHERE right_id = ?i AND is_allow = 'f';";
                $DB->hold()->query($sql, $right['right_id']);
            }
            $DB->query();
        }

        $sql = "DELETE FROM permissions_groups_rights WHERE group_id=?i";
        $DB->query($sql, $id);

        if($rights && $id) {
            $sql = "";
            foreach($rights as $right) {
                $sql .= "INSERT INTO permissions_groups_rights(group_id, right_id) VALUES({$id}, {$right}); ";
            }
            $DB->query($sql);
        }
    }

    /**
     * �������� ������ ���������� � ������ � �� ������
     *
     * @param   integer $id ������������� ������
     * @return  array       ���������� � ������ � ������ �������
     */
    function getGroupInfo($id) {
        global $DB;

        $sql = "SELECT * FROM permissions_groups WHERE id=?i";
        $group = $DB->row($sql, $id);
        $group['rights'] = array();

        $sql = "SELECT right_id FROM permissions_groups_rights WHERE group_id=?i";
        $rights = $DB->rows($sql, $id);
        if($rights) {
            foreach($rights as $right) {
                array_push($group['rights'], $right['right_id']);
            }
        }

        return $group;
    }

    /**
     * �������� ����� ������
     *
     * @param   string  $name   �������� ������
     * @param   array   $rights ����������� ����� ��� ������
     * @return  integer         ID ��������� ������
    */
    function addGroup($name, $rights) {
        global $DB;

        $sql = "INSERT INTO permissions_groups(name) VALUES(?) RETURNING id";
        $id = $DB->val($sql, $name);

        if($rights && $id) {
            $sql = "";
            foreach($rights as $right) {
                $sql .= "INSERT INTO permissions_groups_rights(group_id, right_id) VALUES({$id}, {$right}); ";
            }
            $DB->query($sql);
        }

        return $id;
    }

    /**
     * �������� ������
     *
     * @param   integer $id ������������� ������
     */
    function deleteGroup($id) {
        global $DB;

        $sql = "SELECT * FROM permissions_groups_rights WHERE group_id=?i AND group_id<>0 AND group_id<>1";
        $rights = $DB->rows($sql, $id);
        if($rights) {
            foreach($right as $right) {
                $sql = "DELETE FROM permissions_rights_users WHERE right_id = ?i AND is_allow = 'f';";
                $DB->hold()->query($sql, $right['right_id']);
            }
            $DB->query();
        }

        $sql = "DELETE FROM permissions_groups WHERE id=?i AND id<>0 AND id<>1";
        $DB->query($sql, $id);
    }

    /**
     * �������� ������ ���� ��������� ����
     *
     * @return  array   ������ ���� ��������� ����
     */
    function getAllRights() {
        global $DB;

        $sql = "SELECT * FROM permissions_rights ORDER BY code";
        $rights = $DB->rows($sql);

        return $rights;
    }

    /**
     * �������� ������ ���� �����
     *
     * @return  array   ���������� � �������
     */
    function getAllGroups() {
        global $DB;

        $sql = "SELECT * FROM permissions_groups ORDER BY name";
        $groups = $DB->rows($sql);

        return $groups;
    }

    /**
     * ��������� ������ �� ������������ � ������
     *
     * @param   integer $uid    ID ������������
     * @param   string  $group  ������
     * @return  boolean         ������ ��� ���
     */
    function getUserGroupPermissions($uid, $group) {
        global $DB;

        switch($group) {
            case 'administrator':
                $group_id = 0;
                break;
            case 'moderator':
                $group_id = 1;
                break;
            default:
                $group_id = -1;
                break;
        }

        $sql = "SELECT 1 FROM permissions_groups_users WHERE user_id={$uid} AND group_id={$group_id}";
        return ($DB->val($sql)==1?true:false);
    }

    /**
     * �������� ����� ������� ������������
     *
     * @param   integer $uid    ID ������������
     * @return  array           ����������� ����� ��� ������������
     */
    function getUserPermissions($uid) {
        global $DB;

        $permissions = array();
        $notAllowPermissions = array();

        $sql = "SELECT p_r.code as code, p_r_u.is_allow as is_allow
                FROM permissions_rights_users as p_r_u
                INNER JOIN permissions_rights as p_r ON p_r.id = p_r_u.right_id
                WHERE p_r_u.user_id = ?i

                UNION ALL

                SELECT p_r.code as code, NULL as is_allow
                FROM (SELECT group_id FROM permissions_groups_users WHERE user_id = ?i) as p_g
                INNER JOIN permissions_groups_rights as p_g_r ON p_g_r.group_id = p_g.group_id
                INNER JOIN permissions_rights as p_r ON p_g_r.right_id = p_r.id

                UNION ALL

                SELECT 'all' as code, NULL as is_allow
                FROM permissions_groups_users
                WHERE user_id = ?i AND group_id = 0
                ";
        $permissions_data = $DB->rows($sql, $uid, $uid, $uid);

        if($permissions_data) {
            foreach($permissions_data as $v) {
                if(!in_array($v['code'],$permissions)) {
                    array_push($permissions, $v['code']);
                }
            }
            foreach($permissions_data as $v) {
                if($v['is_allow']=='f' && !in_array($v['code'],$notAllowPermissions)) {
                    array_push($notAllowPermissions, $v['code']);
                }
            }
        }
  
        $permissions = array_diff($permissions,$notAllowPermissions);

        return $permissions;
    }

    /**
     * ����� ������������� �� �������� ���������
     *
     * @param   integer $group_id   ������������� ������
     * @param   string  $login      ����� ������������
     * @return  array               ����� ��������� �������������
     */
    function getUsers($search_group, $search_login) {
        global $DB;

        if(trim($search_login)) {
            $sqlSearchLogin = " AND lower(u.login) LIKE lower(?u)";
        }

        switch($search_group) {
            case '-4':
                $sql = "SELECT u.* 
                        FROM users as u
                        WHERE ".($search_login?"lower(u.login) LIKE lower(?u) ":"1=2")."
                        ORDER BY u.login
                        ";
                break;
            case '-3':
                $sql = "SELECT DISTINCT(a_u.*) 
                        FROM (
                                SELECT DISTINCT(u.*) 
                                FROM permissions_groups_users as p_g_u
                                LEFT JOIN users as u ON u.uid = p_g_u.user_id 
                                WHERE 1=1 {$sqlSearchLogin}

                                UNION ALL

                                SELECT DISTINCT(u.*) 
                                FROM permissions_rights_users as p_r_u
                                LEFT JOIN users as u ON u.uid = p_r_u.user_id
                                LEFT JOIN permissions_groups_users as p_g_u ON p_g_u.user_id = p_r_u.user_id
                                WHERE p_g_u.group_id IS NULL {$sqlSearchLogin}
                             ) as a_u
                        ORDER BY a_u.login
                        ";
                break;
            case '-2':
                $sql = "SELECT DISTINCT(u.*) 
                        FROM permissions_rights_users as p_r_u
                        LEFT JOIN users as u ON u.uid = p_r_u.user_id
                        LEFT JOIN permissions_groups_users as p_g_u ON p_g_u.user_id = p_r_u.user_id
                        WHERE p_g_u.group_id IS NULL {$sqlSearchLogin}
                        ORDER BY u.login
                        ";
                break;
            case '-1':
                $sql = "SELECT DISTINCT(u.*) 
                        FROM permissions_groups_users as p_g_u
                        LEFT JOIN users as u ON u.uid = p_g_u.user_id 
                        WHERE 1=1 {$sqlSearchLogin}
                        ORDER BY u.login
                        ";
                break;
            default:
                $sql = "SELECT DISTINCT(u.*) 
                        FROM permissions_groups_users as p_g_u
                        LEFT JOIN users as u ON u.uid = p_g_u.user_id
                        WHERE p_g_u.group_id = {$search_group} {$sqlSearchLogin}
                        ORDER BY u.login
                        ";
                break;
        }

        $users = $DB->rows($sql, "%{$search_login}%", "%{$search_login}%");
        return $users;
    }

    /**
     * �������� ������ ����� � ������� ��������� ������������
     *
     * @param   integer $uid    ID ������������
     * @return  array           ������ � ������� ��������� ������������
     */
    function getUserGroups($uid) {
        global $DB;

        $sql = "SELECT p_g.* 
                FROM permissions_groups_users as p_g_u 
                LEFT JOIN permissions_groups as p_g ON p_g.id = p_g_u.group_id
                WHERE p_g_u.user_id=?i";
        return $DB->rows($sql, $uid);
    }

    /**
     * �������� ������ �������������� ���� ������������
     *
     * @param   integer $uid    ID ������������
     * @return  array           �������������� �����, ������� ����� ������������
     */
    function getUserExtraRights($uid) {
        global $DB;

        $sql = "SELECT p_r.*, p_r_u.is_allow
                FROM permissions_rights_users as p_r_u 
                LEFT JOIN permissions_rights as p_r ON p_r.id = p_r_u.right_id
                WHERE p_r_u.user_id=?i";
        return $DB->rows($sql, $uid);
    }

    /**
     * �������� ������������ �� ���� ���� � �������� ���� ��� ����
     *
     * @param   integer $uid    ID ������������
     */
    function deleteUser($uid) {
        global $DB;

        $sql = "DELETE FROM permissions_groups_users WHERE user_id=?i";
        $DB->query($sql, $uid);

        $sql = "DELETE FROM permissions_rights_users WHERE user_id=?i";
        $DB->query($sql, $uid);
    }

    /**
     * �������� ���������� � ������� � ������ ������������
     *
     * @param   integer     $uid                ID ������������
     * @param   array       $groups             ���������� � �������
     * @param   array       $rights_allow       ���������� � ����������� ������
     */
    function updateUser($uid, $groups, $rights_allow) {
        global $DB;

        $user_groups_rights = array();
        if(!is_array($rights_allow)) $rights_allow = array();
        if(!is_array($rights_disallow)) $rights_disallow = array();

        $sql = "DELETE FROM permissions_groups_users WHERE user_id=?i";
        $DB->query($sql, $uid);
        $sql = "DELETE FROM permissions_rights_users WHERE user_id=?i";
        $DB->query($sql, $uid);

        if(is_array($groups)) {
            $sql = "";
            if($groups) {
                foreach($groups as $group) {
                    $g_rights = permissions::getGroupInfo($group);
                    if($g_rights['rights']) {
                        foreach($g_rights['rights'] as $g_right) {
                            if(!in_array($g_right, $user_groups_rights)) {
                                array_push($user_groups_rights, $g_right);
                            }
                        }
                    }
                    $sql .= "INSERT INTO permissions_groups_users(group_id,user_id) VALUES({$group},{$uid});\n ";
                }
                $DB->query($sql);
                $DB->query("UPDATE users SET is_chuck = true WHERE uid = {$uid}");
            }
        }

        $tr_allow = array_diff($rights_allow, $user_groups_rights);
        $tr_disallow = array_diff($user_groups_rights, $rights_allow);
        $rights_allow = $tr_allow;
        $rights_disallow = $tr_disallow;

        if(is_array($rights_allow)) {
            $sql = "";
            if($rights_allow) {
                foreach($rights_allow as $right) {
                    $sql .= "INSERT INTO permissions_rights_users(right_id,user_id,is_allow) VALUES({$right},{$uid},'t');\n ";
                }
                $DB->query($sql);
            }
        }

        if(is_array($rights_disallow)) {
            $sql = "";
            if($rights_disallow) {
                foreach($rights_disallow as $right) {
                    $sql .= "INSERT INTO permissions_rights_users(right_id,user_id,is_allow) VALUES({$right},{$uid},'f');\n ";
                }
                $DB->query($sql);
            }
        }
    }

}
?>
