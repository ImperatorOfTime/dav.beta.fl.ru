<?php


require_once( dirname(__FILE__) . "/../../config/pgq_php_config.php");
require_once( ABS_PATH."/classes/pgq/api/PGQConsumer.php" );
$Config["LOGLEVEL"] = NOTICE;
$Config["LOGFILE"] = ABS_PATH."/classes/pgq/logs/mail_log.pgq";


$Config["DELAY"] = 5;
/**
 * �����, � ��������, ����� ��������� ���������� ������-������ ������� � pgq � ��� ��������
 * ����� ������� ��� ������� ����� ������� ������.
 *
 * @var integer
 */
$Config["RESTART_EVENTS_INTERVAL"] = 1800;
/**
 * ���������� ������� ��� ���������� �������� ��� ������� pgq
 * ��� ������� ����� ������� ������
 *
 * @var integer
 */
$Config["RESTART_EVENTS_COUNT"] = 50;

?>