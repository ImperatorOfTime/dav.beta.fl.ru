<?php

require '../classes/stdf.php';

$login = $_GET['login'];
$row = $DB->row("SELECT * FROM users WHERE login = ?", $login);
if ( empty($row) ) {
    echo "������������ {$login} �� ����������";
} else {
    $DB->query("UPDATE users SET is_verify = NOT is_verify WHERE login = ?", $login);
    echo "������������ {$login} ������ " . (($row['is_verify'] == 't')? '�� ': '') . "�������������";
}