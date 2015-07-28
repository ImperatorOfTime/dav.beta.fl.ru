<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs_proto.php");

/**
 * ����� ��� ������ � ������������� � �������
 *
 */
class articles_comments extends blogs_proto {

    const MAX_FILE_SIZE = 2097152;
    
    const MAX_FILE_COUNT = 10;
    /**
     * �������� ���������(�����������)
     *
     * @param integer $fid    UID
     * @param integer $reply  ������������� ��������� ������� �� ������� �������� ������ ���������
     * @param integer $thread ����
     * @param string  $msg    ���������
     * @param string  $yt_link  ������ �� ����
     * @param mixed   $files  �������� ������
     * @param char    $ip     �� �����������
     * @param mixed   $error  ���������� ��������� �� ������
     * @param mixed   $small  ��� ����
     * @return integer  ID ������ ���������
     */
    function Add($fid, $reply, $thread, $msg, $yt_link, $files, $ip, &$error, $small) {
        global $DB;

        if (!$error_flag) {
            $curname = get_class($this);

            $sql = "INSERT INTO articles_comments (from_id, parent_id, from_ip, created_time, article_id, msgtext, youtube_link)
                    VALUES (?i, ?, ?, NOW(), ?, ?, ?) RETURNING id";

            $l_id = $DB->val($sql, $fid, $reply, $ip, $thread, $msg, $yt_link);
            $error = $DB->error;

            if(!$error && !isNulArray($files['f_name'])) {
                $sql = '';
                if (is_array($files)) {
                    $data = array();
                    for($i = 0; $i < count($files['f_name']); $i ++) {
                        if ($files['f_name'][$i]) {
                            $data[] = "('$l_id', '{$files['f_id'][$i]}', '{$files['tn'][$i]}')";
                        }
                    }
                    if (count($data))
                        $sql = implode(', ', $data);
                } else {
                    $sql = "('$l_id', '$files', '$small')";
                }
                $DB->squery("INSERT INTO articles_comments_files (comment_id, file_id, small) VALUES $sql");
            }
        }

        return $l_id;
    }

    /**
     * �������� �����������
     *
     * @param integer $id    id ��������
     * @param integer $fid   UID
     * @param string  $msg   ���������
     * @param string  $yt_link  ������ �� ����
     * @param mixed   $files  �������� ������
     * @param mixed   $error  ���������� ��������� �� ������
     * @param mixed   $small  ��� ����
     * @return integer  ID ������ ���������
     */
    function Update($id, $fid, $msg, $yt_link, $files, $files_cnt = 0, &$error, $small) {
        global $DB;

        $curname = get_class($this);

        $sql = "UPDATE articles_comments SET msgtext = ?,
                    modified_id = ?i,
                    modified_time = NOW(),
                    youtube_link = ?
                WHERE id = ?i";

        $DB->query($sql, $msg, $fid, $yt_link, $id);
        $error = $DB->error;
        $l_id = $id;

        if(!$error && !isNulArray($files['f_name'])) {
            $sql = '';
            if (is_array($files)) {
                $data = array();
                for($i = 0; $i < count($files['f_name']); $i ++) {
                    if ($files['f_name'][$i]) {
                        $data[] = "('$l_id', '{$files['f_id'][$i]}', '{$files['tn'][$i]}')";
                    }
                }
                if (count($data))
                    $sql = implode(', ', $data);
            } else {
                $sql = "('$l_id', '$files', '$small')";
            }
            $DB->squery("INSERT INTO articles_comments_files (comment_id, file_id, small) VALUES $sql");
        }

        return $l_id;
    }

    /**
     * ������� �����������
     * 
     * @param  int $id ID �����������
     * @param  int $uid UID ���� ��� �������
     * @return bool true - �����, false - ������
     */
    function DeleteComment($id, $uid) {
        global $DB;
        $sql = "UPDATE articles_comments SET deleted_id = ?i
                WHERE id = ?i AND deleted_id IS NULL";

        $DB->query($sql, $uid, $id);
        if($DB->error) return false;

        return true;
    }
    
    /**
     * ������������ �����������
     * 
     * @param  int $id ID �����������
     * @param  int $uid UID ���� ��� ���������������
     * @return bool true - �����, false - ������
     */
    function RestoreComment($id, $uid) {
        global $DB;
        $sql = "UPDATE articles_comments SET deleted_id = NULL
                WHERE id = ?i AND deleted_id IS NOT NULL";

        $DB->query($sql, $id);
        if($DB->error) return false;

        return true;
    }

    /**
     * ������� ��� ���������
     *
     * @param integer $item_id  �� �����
     * @param string  $error    ���������� ��������� �� ������
     */
    function GetThreads($item_id, &$error) {
        global $DB;
        $curname = get_class($this);
        $sql = "SELECT id, blg.from_id, parent_id, created_time, msgtext,
                    modified_id, deleted_id,
                    modified_time,
                    u.uname, u.usurname, u.is_banned, u.login, u.photo, u.is_pro, u.is_pro_test, u.role,
                    mod.uname as mod_name, mod.usurname as mod_usurname, mod.login as mod_login, mod.role as mod_role,
                    youtube_link
                FROM articles_comments as blg
                INNER JOIN users as u ON u.uid=blg.from_id
                LEFT JOIN users as mod ON mod.uid=blg.modified_id
                WHERE blg.article_id=?i
                ORDER BY created_time";

        $this->thread = $DB->rows($sql, $item_id);        

        $error .= $DB->error;
        if ($error) $error = parse_db_error($error);
        else {
            $this->msg_num = count($this->thread);
            if ($this->msg_num > 0) $this->SetVars(0);
        }
        //return array($name, $id_gr, 99);
    }

    /**
     * ��������� ������ � �������� � ��������� ������������
     *
     * @param array|int $ids ������ ��������������� ������������ ��� �� ������ �����������
     */
    function getAttaches($ids, $autoindex = false) {
        global $DB;
        if(!count($ids)) return false;

        $comments = is_array($ids) ? implode(',', $ids) : $ids;
        $sql = "SELECT f.*, af.comment_id FROM articles_comments_files as af
                    INNER JOIN file as f ON f.id = af.file_id
                 WHERE af.comment_id IN ($comments) ORDER BY af.id";

        $rows = $DB->rows($sql);

        if($DB->error) return false;

        $attaches = array();

        if(!$rows) return $attaches;

        if(is_array($ids)) {
            foreach($rows as $attach) {
                if(!$autoindex) {
                    $attaches[$attach['comment_id']][$attach['id']] = $attach;
                } else {
                    $attaches[$attach['comment_id']][] = $attach;
                }
            }
        } else {
            foreach($rows as $attach) {
                if(!$autoindex) {
                    $attaches[$attach['id']] = $attach;
                } else {
                    $attaches[] = $attach;
                }
            }
        }

        return $attaches;
    }

    /**
     * ������� �������� �� �� id
     *
     * @param integer $comment_id ID �����������, �� �������� ��������� ��������
     * @param array $attaches ������ � ���������������� ������, ������� ����� �������
     */
    function removeAttaches($comment_id, $attaches) {
        $comment_attaches = $this->getAttaches($comment_id);

        $file = new CFile();
        foreach($attaches as $attach) {
            if(!isset($comment_attaches[$attach])) continue;
            $file->Delete($attach);
        }
    }


    /**
     * ��������� �������
     *
     * @param array $attach			������ � ���������� ���� CFile
     * @param array $max_image_size	������ � ������������� ��������� �������� (��. CFile). ���� ��� ���� ��������� attach
     * @param string $login			����� �����, �������� ��������� ��������. �� ��������� - ���� �� $_SESSION['login']
     * @return array				������ ($files, $alert, $error_flag)
     */
    function UploadFiles($attach, $max_image_size, $login = '') {
        $alert = null;
        if ($login == '')
            $login = $_SESSION['login'];
        if ($login == '')
            $login = 'Anonymous';
        if ($attach)
            foreach ($attach as $file) {
                $file->max_size = self::MAX_FILE_SIZE;
                $file->proportional = 1;
                $f_name = $file->MoveUploadedFile($login . "/upload");
                $f_id = $file->id;
                $ext = $file->getext();
                if (in_array($ext, $GLOBALS['graf_array']))
                    $is_image = TRUE;
                else
                    $is_image = FALSE;
                $p_name = '';
                $p_id = '';
                if (! isNulArray($file->error)) {
                    $error_flag = 1;
                    $alert = "���� ��� ��������� ������ �� ������������� �������� ��������.";
                    break;
                } else {
                    if ($is_image && $ext != 'swf' && $ext != 'flv') {
                        if (! $file->image_size['width'] || ! $file->image_size['height']) {
                            $error_flag = 1;
                            $alert = '���������� ��������� ��������';
                            break;
                        }
                        if (! $error_flag && ($file->image_size['width'] > $max_image_size['width'] || $file->image_size['height'] > $max_image_size['height'])) {
                            if (! $file->img_to_small("sm_" . $f_name, $max_image_size)) {
                                $error_flag = 1;
                                $alert = '���������� ��������� ��������.';
                                break;
                            } else {
                                $tn = 2;
                                $p_name = "sm_$f_name";
                                $p_id = $file->id;
                            }
                        } else {
                            $tn = 1;
                        }
                    } else
                    if ($ext == 'flv') {
                        $tn = 2;
                    } else {
                        $tn = 0;
                    }
                }
                $files['f_id'][] = $f_id;
                $files['f_name'][] = $f_name;
                $files['p_name'][] = $p_name;
                $files['p_id'][] = $p_id;
                $files['tn'][] = $tn;
            }
        return array($files, $alert, $error_flag);
    }


    /**
     * �������� ����������� �� ID
     *
     * @param integer $id �� �����������
     */
    function getComment($id) {
        global $DB;
//        $sql = "SELECT * FROM articles_comments WHERE id = $id";

        $sql = "SELECT id, blg.from_id, parent_id, created_time, msgtext,
                    modified_id, deleted_id,
                    modified_time,
                    u.uname, u.usurname, u.is_banned, u.login, u.photo, u.is_pro, u.is_pro_test, u.role,
                    mod.uname as mod_name, mod.usurname as mod_usurname, mod.login as mod_login, mod.role as mod_role,
                    youtube_link
                FROM articles_comments as blg
                LEFT JOIN users as u ON u.uid=blg.from_id
                LEFT JOIN users as mod ON mod.uid=blg.modified_id
                WHERE blg.id=?i";

        $comment = $DB->row($sql, $id);
        if($DB->error) return false;

        return $comment;
    }


    /**
     * �������� ����������� �� ID, ��� ��������
     *
     * @param integer $id �� �����������
     */
    function getComments4Sending($message_ids, $connect = NULL) {
        global $DB;
        if(!$message_ids) return NULL;
        if(is_array($message_ids))
          $message_ids = implode(',', array_unique($message_ids));

        $sql = "SELECT c.id, c.from_id, c.parent_id, c.created_time, c.msgtext, c.article_id,
                    u.uid, u.uname, u.usurname, u.is_banned, u.login, u.role,
                    s.uid as s_uid, s.uname as s_name, s.usurname as s_usurname, s.login as s_login, s.role as s_srole, s.subscr as s_subscr, s.email as s_email, s.is_banned as s_banned,
                    art.user_id as a_uid,
                    aa.uname as a_uname,
                    aa.usurname as a_usurname,
                    aa.login as a_login,
                    aa.email as a_email,
                    aa.subscr as a_subscr,
                    aa.is_banned as a_banned
                FROM articles_comments as c
                LEFT JOIN users as u ON u.uid=c.from_id
                LEFT JOIN articles_comments as par ON par.id = c.parent_id
                LEFT JOIN users as s ON s.uid=par.from_id
                LEFT JOIN articles_new as art ON art.id=c.article_id
                LEFT JOIN users as aa ON aa.uid=art.user_id
                WHERE c.id IN ($message_ids) AND 
                    ((c.parent_id IS NOT NULL AND par.from_id != c.from_id) OR c.parent_id IS NULL)";

        return $DB->rows($sql);
    }

    /**
     * �������� �������
     *
     * @deprecated
     * @param <type> $id
     * @param <type> $files
     *
     */
    function reorderFiles($id, $files) {
        global $DB;
        foreach($files as $k => $file) {
            $k++;
            $sql = "UPDATE articles_comments_files SET file_order =?
                    WHERE comment_id = ?i AND file_id = ?";

            $DB->query($sql, $k, $id, $file);
            if($DB->error) return false;
        }

    }

}

