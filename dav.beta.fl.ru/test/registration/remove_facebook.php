<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php' );

$login = __paramInit('string', 'login');

if ($login) {
    global $DB;
    $uid = $DB->val('SELECT uid FROM users WHERE login = ?', $login);
    
    if ($uid) {
        $DB->query('DELETE FROM users_social WHERE user_id = ?i', $uid);
        echo '<p>�������� �������.</p>';
    }
}


echo '<p>���������� ������������ �� �������� ���������� ����</p>';
echo '<p><strong>��������,</strong> /test/registration/remove_facebook?login=username</p>';