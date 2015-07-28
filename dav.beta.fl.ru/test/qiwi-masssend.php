<?php

require_once '../classes/stdf.php';
//require_once '../classes/messages.php';
require_once '../classes/memBuff.php';

$master  = new DB('master');
$plproxy = new DB('plproxy');
//$messages = new messages;

$text = "��������� ������������,
�����������!

�� ��������� �������� �� ��� ������� ���������� ����-������ � QIWI-�������� � ��������� 100 ������ �������� � ������� �� �������� QIWI.

������� Free-lance.ru ���������� ��� �� ������� � ����� ������ �������. 

�������� ������, 
��� Free-lance.ru";

$users = $master->col("
SELECT
	users.uid, users.login, users.uname, users.usurname
FROM (
	SELECT
		a.user_id, SUM(value)
	FROM
		surveys_users_answers a
	INNER JOIN
		surveys_questions_options o ON a.answer_id = o.id
	GROUP BY
		a.user_id
	HAVING
		SUM(value) = 7
) p
INNER JOIN
	surveys_users u ON u.id = p.user_id AND u.date_end IS NOT NULL
INNER JOIN
	users ON users.uid = u.uid
");


$plproxy->query("SELECT messages_masssend(103, ?a, ?, '{}')", $users, $text);

$memBuff = new memBuff();
$memBuff->flushGroup("msgsCnt");