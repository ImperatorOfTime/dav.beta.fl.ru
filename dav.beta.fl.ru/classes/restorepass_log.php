<?php
/**
 * ���������� ����� � ��������� ��������� �������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ����������� ����� ������ ��������������
 *
 */
class restorepass_log {

    /**
	 * ������ ���������� � ����� ������
	 *
	 * @param integer   $uid        - ID ������������ ��� �������� �������� ������
	 * @param string    $ip         - IP � �������� �������� ������
	 * @param integer   $service    - ����� ��� �������� ������: 1 - Email, 2 - SMS, 3 - � ������ �������� ������������; 4 - �������
	 */
	function SaveToLog($uid, $ip, $service, $modified_uid = null) 
    {
		$ip = ip2long($ip);
        $modified_uid = (!$modified_uid)?$uid:$modified_uid;
		$GLOBALS['DB']->query("INSERT INTO users_change_pwd_log (uid, ip, date, service, modified_uid) VALUES (?i, ?, NOW(), ?i, ?i)", $uid, $ip, $service, $modified_uid);
	}

    /**
	 * ��������� ���������� � ������ �������
	 *
	 * @param string    $login      - ����� ������������
	 * @param string    $ip         - IP � �������� ������� ������
	 * @param string    $ds         - ���� ������ �������
	 * @param string    $de         - ���� ����� �������
	 * @param integer   $is_sms     - �������� �� ���� �������������� SMS ��� ����� ������, 1 - ��
	 * @param integer   $is_email   - �������� �� ���� �������������� E-mail ��� ����� ������, 1 - ��
	 * @return array                - ���������� � ������ ������� � ����� ������ �������
	 */
	function GetLogData($login, $ip, $ds, $de, $is_sms, $is_email) {
		if ($login)
			$add_sql_login = " AND lower(users.login) = lower('$login')";
		if ($ip)
			$add_sql_ip = " AND log.ip = " . ip2long($ip);
		if ($is_sms == 1 || $is_email == 1) {
			$ids = ($is_sms ? ',2' : '') . ($is_email ? ',1' : '');
			$ids = preg_replace("/^,/", "", $ids);
			$add_sql_service = " AND service IN($ids)";
		}

		$sql = "SELECT login, uname, usurname, reg_ip, log.date as date, log.ip as ip, log.service as service FROM users
                INNER JOIN users_change_pwd_log as log ON users.uid=log.uid WHERE log.date >= '$ds' AND log.date < '$de'::date+'1 day'::interval" . $add_sql_login . $add_sql_ip . $add_sql_service;
		return $GLOBALS['DB']->rows($sql);
	}
}