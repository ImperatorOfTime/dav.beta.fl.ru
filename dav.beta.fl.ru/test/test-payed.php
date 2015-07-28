<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/firstpage.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/bar_notify.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pmail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
/*
?>
<p>
������������� ������ ��������� ��� ����� 3 ��� beta.free-lance.ru/test/test-payed.php?update_pro=login&day=3 <br/>
<pre>
update_pro   - ����� ������������ �������� ���������� �������� ���
    day      - 3 ��� 1
</pre>
</p>
<hr>
<?
if($_GET['update_pro']) {
    $uid  = $DB->val("SELECT uid FROM users WHERE login = ?", $_GET['update_pro']);

    if(!isset($_GET['day'])) $_GET['day'] = 3;
    if($_GET['day'] != 1 && $_GET['day'] != 3) $_GET['day'] = 3;
    $day = intval($_GET['day']);

    $sql = "WITH m AS
    (select ( MIN(from_date + to_date) - now()) + SUM(to_date) -  '1 mon' as ival from orders where from_id = ?i AND from_date + to_date > now())
    update orders set from_date = from_date - m.ival + '{$day} days 2 hour' FROM m
    where from_id = ?i AND from_date + to_date > now()";

    $DB->query($sql, $uid, $uid);
} */
?>

<p>
����� ����������� ��������� ��� ����������� ����� ������ beta.free-lance.ru/test/test-payed.php?login=login&attempt=1<br/>
<pre>
login   - ����� ������������ �������� ���������� �������������
attempt - ������� ������������� (1 ��� 2)
</pre>

</p><?
if ($_GET['login']) {
    payed::checkAutoProTest($_GET['login'], intval($_GET['attempt']));
}
?>
<hr>
<p>
����� �������� ����������� �� <b>days</b> ���� �� ��������� ������ beta.free-lance.ru/test/test-payed.php?user=login&service=pro&auto=1<br/>
<pre>
user    - ����� ������������ �������� ���������� �������������
service - �� ������ ������� ����������� (pro, firstpage) (���� �� ������ �� ��������� pro)
auto    - ���� ����������� � 1 ������ ������������� ��������, ����� 0 - ���������  (���� �� ������ �� ��������� 0)
days    - �������������� ������� ����, ����� ��������� �������� 1 ��� 3 (���� �� ������, �� ��������� 3)
P.S: �� ����� ���� ������������ days = 1 � auto = 1
</pre>

<form method="GET">
    <select name="service">
        <option value="pro" <?= $_GET['service'] == 'pro' ? 'selected' : ''?>>������� ���</option>
        <option value="firstpage" <?= $_GET['service'] == 'firstpage' ? 'selected' : ''?>>������� ���������</option>
    </select><br/>
    ������������ <input type="text" value="<?= htmlspecialchars($_GET['user'])?>" name="user"/><br/>
    �� <input type="text" value="<?= isset($_GET['days']) ? htmlspecialchars($_GET['days']) : 3?>" name="days"/> ���(����)<br/>

    �������� ������������� <input type="checkbox" name="auto" value="1" <?= isset($_GET['auto']) ? "checked" : ""?>/> <br/>
    <input type="submit" value="������">
</form>
</p>
<hr>
<?
if($_GET['user']) {
    if(!isset($_GET['service'])) $_GET['service'] = 'pro';
    if(!isset($_GET['days'])) $_GET['days'] = 3;
    if(!isset($_GET['auto'])) $_GET['auto'] = 0;

    if($_GET['days'] != 1 && $_GET['days'] != 3) {
        echo "<strong style='color:red'>���� ����� ��������� �������� 1 ��� 3</strong>";
        return;
    }
    if($_GET['days'] == 1 && $_GET['auto'] == 1) {
        echo "<strong style='color:red'>�� ����� ���� ������������ days = 1 � auto = 1</strong>";
        return;
    }

    $mail = new smail();
    if($_GET['service'] == 'pro') {
        $sql  = "SELECT u.*, a.id as acc_id FROM users u INNER JOIN account a ON a.uid = u.uid WHERE u.login = ?";
        $user = $DB->row($sql, $_GET['user']);

        if($_GET['auto'] == 1) {
            $role = is_emp($user['role']) ? "employer" : "freelancer";
            $mail->remindAutoprolongPRO(array($user), $role, $_GET['days']);
        } else {
            $mail->remindTimeleftPRO(array($user), $_GET['days']);
        }
    } else {
        if($_GET['auto'] == 1) {
            $mail->remindAutoprolongFirstpage($_GET['days'], $_GET['user']);
        } else {
            $mail->reminderFPNotAutopayed($_GET['days'], $_GET['user']);
        }
    }

    echo "<strong style='color:green'>����������� �������</strong>";
}

?>
<p>
����� ����������� ��������� �������� ���������� ����������� ����� ������ beta.free-lance.ru/test/test-payed.php?fflogin=login&attempt=1<br/>
<pre>
fflogin - ����� ������������ �������� ���������� �������������
attempt - ������� ������������� (1 ��� 2)
</pre>
</p>
<hr>
<?
if ($_GET['fflogin']) {
    if(intval($_GET['attempt']) == 1) {
        firstpage::autoPayedReminder(1, 'days', $_GET['fflogin']);
    } else {
        firstpage::autoPayedReminder(1, 'hour', $_GET['fflogin']);
    }

    echo "<strong style='color:green'>�������� ��������� �������� ����������</strong>";
}

//if ($_GET['firstpage'] == 1) {
//    firstpage::autoPayedReminder($_GET['firstpage'], 'days', true);
//}
//
//if ($_GET['firstpage'] == 1 && isset($_GET['hour'])) {
//    firstpage::autoPayedReminder($_GET['firstpage'], 'hour', true);
//}

//?><!--<p>����� ��������� ������� ������� ����������� ������ beta.free-lance.ru/test/test-payed.php?check_reserve=1</p>--><?//
//if ($_GET['check_reserve']) {
//    billing::checkOldReserve();
//    echo '<p><strong>�������� �������</strong> - ��</p>';
//}

//$pmail = new pmail;
//$pmail->DepositMail(329);

//firstpage::autoPayedReminder(3, true);

$profs = new professions();
$allProfs = $profs->GetAllProfessions();
$allProfsID = array();
foreach($allProfs as $prof) {
    $allProfsID[$prof['id']] = $prof['profname'];
}
?><p>��������� ��������� [� ���������� �������]</p>
<pre><?= var_dump ($allProfsID); ?></pre>


