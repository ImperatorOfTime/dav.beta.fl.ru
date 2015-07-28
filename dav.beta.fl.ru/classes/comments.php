<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/blogs.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/articles.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/articles_comments.php';
/**
 * ����� �������� � ������� ������������ �� ������ ��������.
 * �������, ������, ������.
 *
 * 
 */
class comments {

	/**
	 * ������������� ������������ � �������
	 *
	 */
	const T_ARTICLES = 2;

	/**
	 * ���������� ������ ������������
	 *
	 * @param  integer  $count   ���������� ����� ���������� ������������
	 * @param  array    $filter  ��������� �������: category - ���������� ���������� ���������, sdate - � ��������� ����, edate - �� ���������� ����
	 * @param  integer  $limit   ���������� ���������, ������� ����� ����������
	 * @param  integer  $offset  � ������ ��������� ����������
	 * @return array             ������ � �������
	 */
	public function GetItems(&$count, $filter, $limit, $offset) {
        global $DB;

		// ��������� ��� ������
		if (empty($filter['category']) || $filter['category'] == self::T_ARTICLES) {
			$subquery = $articles = "
				SELECT
					c.id, c.article_id AS thread, articles.title AS name, ''::text AS title, c.from_id AS user_id, c.created_time AS post_time, c.msgtext AS msg,
					c.deleted_id AS deleted, 2 AS comment_type, c.youtube_link AS yt_link
				FROM
					articles_comments c
				INNER JOIN
					articles_new AS articles ON c.article_id = articles.id
			";
		}

		// �������������� ������� ��� �������
		$where = '';
		if (!empty($filter['sdate'])) {
			$where = " post_time >= '{$filter['sdate']} 00:00:00' ";
		}
		if (!empty($filter['edate'])) {
			$where .= (empty($where)? "": " AND ") . " post_time <= '{$filter['edate']} 23:59:59' ";
		}
		if ($where) {
			$where = "WHERE {$where}";
		}

		// ����� ���������� ������������
		$count = $DB->val("SELECT COUNT(*) FROM ({$subquery}) comments {$where}");

		$sql = "
			SELECT
				c.*, users.login, users.uname, users.usurname, users.is_banned, users.ban_where, COUNT(warns.id) AS warns
			FROM (
				{$subquery}
			) c
			INNER JOIN
				users ON users.uid = c.user_id
			LEFT JOIN
				users_warns warns ON users.uid = warns.uid
			{$where}
			GROUP BY
				 c.id, c.thread, c.name, c.title, c.user_id, c.post_time, c.msg, c.deleted, c.comment_type, c.yt_link, users.login, users.uname, users.usurname, users.is_banned, users.ban_where
			ORDER BY
				c.post_time DESC
			LIMIT
				{$limit}
			OFFSET
				{$offset}
		";
		$res = $DB->rows($sql);

		$rows = array();
		$ids = array();
        if($res) {
    		foreach ($res as $row) {
    			$rows[] = $row;
    			$ids[$row['comment_type']][] = $row['id'];
    		}
        }

		// ������
		$files = array();

		if (!empty($ids[1])) {
			$sql = "SELECT src_id AS comment_id, f.* FROM file_blogs f WHERE f.src_id IN (".implode(',', $ids[1]).")";
			$res = $DB->rows($sql);
            if($res) {
    			foreach ($res as $row) {
    				$files[1][$row['comment_id']][] = $row;
    			}
            }
		}

		if (!empty($ids[2])) {
			$sql = "
				SELECT
					attach.comment_id, attach.small, file.*
				FROM
					articles_comments_files attach
				INNER JOIN
					file ON attach.file_id = file.id
				WHERE
					attach.comment_id IN (".implode(',', $ids[2]).")
			";
			$res = $DB->rows($sql);
            if($res) {
    			foreach ($res  as $row) {
    				$files[2][$row['comment_id']][] = $row;
    			}
            }
		}

		for ($i=0; $i<count($rows); $i++) {
			if (empty($files[ $rows[$i]['comment_type'] ][ $rows[$i]['id'] ])) {
				$rows[$i]['attach'] = array();
			} else {
				$rows[$i]['attach'] = $files[ $rows[$i]['comment_type'] ][ $rows[$i]['id'] ];
			}
		}

		return $rows;

	}


	/**
	 * ���������� ������ �����������
	 * 
	 * @param  integer  $type  ��� ������ ������������
	 * @param  integer  $id    id �����������
	 * @return array           ������ � �������
	 */
	public function GetItem($type, $id) {
        global $DB;
		$item = array();
		switch ($type) {
			case self::T_ARTICLES: {
				$sql = "
					SELECT
						c.id, c.article_id AS thread, articles.title AS name, NULL AS title, c.from_id AS user_id, c.created_time AS post_time, c.msgtext AS msg,
						c.deleted_id AS deleted, 2 AS comment_type, c.youtube_link AS yt_link, users.login, users.uname, users.usurname
					FROM
						articles_comments c
    				INNER JOIN
    					articles_new AS articles ON c.article_id = articles.id
					INNER JOIN
						users ON c.from_id = users.uid
					WHERE
						c.id = ?i
				";
				$item = $DB->row($sql, $id);
				$sql = "
					SELECT
						attach.comment_id, attach.id AS attach_id, attach.small, file.*
					FROM
						articles_comments_files attach
					INNER JOIN
						file ON attach.file_id = file.id
					WHERE
						attach.comment_id = ?i
				";
				$res = $DB->rows($sql, $id);
				if (count($res)) {
					$item['attach'] = $res;
				} else {
					$item['attach'] = array();
				}
				break;
			}
		}
		return $item;
	}


	/**
	 * �������������� �����������
	 *
	 * @param  integer  $type        ��� ������ ������������
	 * @param  integer  $id          id �����������
	 * @param  integer  $user_id     uid ������������, ����������� �������� �����������
	 * @param  integer  $moder_uid   uid ������������, ������� �����������
	 * @param  string   $title       ��������� ����������� (���� ���� � ������)
	 * @param  string   $msg         ���������
	 * @param  array    $attaches    ������ � ������ ������� (������� CFile)
	 * @param  array    $rmattaches  ������ � ������� ��� �������� ������. �������� ����:
	 *                               array(attach_id: id ����� �� ���������� ������� �������; file_id: id ����� � ������� file; name: ��� �����
	 * @param  string   $yt_link     ������ �� Youtube/Rutube/etc...
	 * @return string                ��������� ������
	 */
	public function Edit($type, $id, $user_id, $moder_uid, $title, $msg, $attaches, $rmattaches, $yt_link) {
        global $DB;
		$error = '';
		switch ($type) {
			case self::T_ARTICLES: {
				$comments = new articles_comments;
				$rmfiles = FALSE;
				if (!empty($rmattaches)) {
					$rmfiles = array();
					for ($i=0; $i<count($rmattaches); $i++) {
						$rmfiles[] = $rmattaches[$i]['file_id'];
					}
					$comments->removeAttaches($id, $rmfiles);
				}
				if ($attaches) {
					$user = new users;
					$user->GetUserByUID($user_id);
					list($att, $error, $error_flag) = $comments->UploadFiles($attaches, array('width' => 390, 'height' => 1000, 'less' => 0), $user->login);
				}
				if (empty($error)) {
					$comments->Update($id, $moder_uid, $msg, $yt_link, $att, 0, $error, array());
				}
				break;
			}
		}
		return $error;
	}


	/**
	 * ������� ����������� (�������� ���������)
	 * 
	 * @param  integer  $type        ��� ������ ������������
	 * @param  integer  $id          id �����������
	 * @param  integer  $user_id     uid ������������, ����������� �������� �����������
	 * @param  integer  $moder_uid   uid ������������, ������� �����������
	 * @return string                ��������� ������
	 */
	public function Del($type, $id, $user_id, $moder_id) {
		$error = '';
		switch ($type) {
			case self::T_ARTICLES: {
				$comments = new articles_comments;
				$comments->DeleteComment($id, $moder_id);
				break;
			}
		}
		return $error;
	}


	/**
	 * ��������������� �����������
	 *
	 * @param  integer  $type        ��� ������ ������������
	 * @param  integer  $id          id �����������
	 * @param  integer  $user_id     uid ������������, ����������� �������� �����������
	 * @param  integer  $moder_uid   uid ������������, ������� �����������
	 * @return string                ��������� ������
	 */
	public function Restore($type, $id, $user_id, $moder_id) {
		$error = '';
		switch ($type) {
			case self::T_ARTICLES: {
				$comments = new articles_comments;
				$comments->RestoreComment($id, $user_id);
				break;
			}
		}
		return $error;
	}


}
?>
