<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/atservices_model.php');

/**
 * Class UserModel
 * ������ ������������ - ����������
 */
class FreelancerModel extends atservices_model {

	private $TABLE_USERS = 'users';

	/**
	 * ��� ������ ������ ������� $rows ��������� �������� � ������������, ID �������� ������ � $id_attr
	 * ���� $extend_attr ������, �� �������� ����������� � ������ rows ��������� ������
	 * ����� ����� ����� ����������� ������������ ����������, ��� ������������� �� ������������ �������� $extend_prefix
	 *
	 * @param $rows
	 * @param $id_attr
	 * @param $extend_attr
	 * @param $extend_prefix
	 * @return $this
	 */
	public function extend(&$rows, $id_attr, $extend_attr = null, $extend_prefix = '')
	{
		$ids = array();
		foreach($rows as $row) // ������� ID
		{
			if (!empty($row[$id_attr]))
			{
				$ids[$row[$id_attr]] = false;
			}
		}
		if (empty($ids))
		{
			return $this;
		}

		$sql = <<<SQL
SELECT
	u.uid as {$extend_prefix}uid,
	u.uname as {$extend_prefix}uname, -- ���
	u.usurname as {$extend_prefix}usurname, -- �������
	u.login as {$extend_prefix}login, -- ����� ������������
	u.photo as {$extend_prefix}photo, -- ������� max 100x100
	u.photosm as {$extend_prefix}photosm, -- ������ ������� max 50x50
	u.role as {$extend_prefix}role, -- ���������/������������ ...
    u.is_profi as {$extend_prefix}is_profi,
	u.is_pro as {$extend_prefix}is_pro, -- ������������ ���
	u.is_verify as {$extend_prefix}is_verify, -- ������������ �������������
	u.country as {$extend_prefix}country, -- c�����
	u.city as {$extend_prefix}city -- �����
FROM {$this->TABLE_USERS} u
WHERE u.uid in (?lu)
SQL;
		$extends = $this->db()->cache(300)->rows($sql, array_keys($ids));
		foreach($extends as $extend) // ��������� ������ �� ID
		{
			$ids[$extend['uid']] = $extend;
		}

		foreach($rows as &$row) // ���������� �������������� �������� � �������� ������ �����
		{
			if (empty($ids[$row[$id_attr]]))
			{
				continue;
			}
			$extend = $ids[$row[$id_attr]];
			if (false === $extend)
			{
				continue;
			}

			if ($extend_attr)
			{
				$row[$extend_attr] = $extend; // ��������� ����
			} else
			{
				$row = array_merge($row, $extend); // ���������� �������
			}
		}
		return $this;
	}

	/**
	 * @return FreelancerModel
	 */
	public static function model()
	{
		$class = get_called_class();
		return new $class;
	}
}