<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/userecho.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");


/* 
 * ��������� ������ �� UserEcho
 */

$userEcho = new UserEcho();
$result = $userEcho->newTopicComplain('�� �����', '�� �����');

echo "<p>��������� <strong>newTopic</strong>:</p>";
echo '<pre>';
print_r($result);
echo '</pre>';