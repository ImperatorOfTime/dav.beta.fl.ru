<?php

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

/**
 * �������� ��� �� 1 ���� � ���� ��� $lost_date ����� 
 */

exit;//�������� �� ���������� ����������

require_once '../../classes/stdf.php';
require_once '../../classes/account.php';
require_once '../../classes/session.php';
require_once '../../classes/firstpage.php';
require_once '../../classes/payed.php';


$lost_date = '2014-08-29';


$users = $DB->rows("
    SELECT
        DISTINCT o.from_id, u.login
    FROM orders AS o
    INNER JOIN users AS u ON o.from_id = u.uid
    WHERE 
        (o.from_date <= '{$lost_date} 23:59:59') AND 
        (o.from_date + o.to_date >= '{$lost_date} 00:00:00')
");

echo "Execute recovery PRO accounts for " . count($users) . " users.\n";

$payed = new payed;
$sess  = new session;//����� ���???

if (count($users)) {
    foreach ($users as $user) {
        $transaction_id = account::start_transaction($user['from_id']);
        $payed->AdminAddPRO($user['from_id'], $transaction_id, '1 days');
        $session->UpdateProEndingDate($user['login']);
    }
}