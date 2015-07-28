<?php
// �������� ������ ����� ����� ����� �����

define('NO_CSRF', 1);

//������ ��������� �����
$paypost = $_POST;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT']."/classes/platipotom.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");


if(is_release()) exit;


if(isset($_GET['cancel'])) { //����� �� �������
    header("Location: /bill/fail");
    exit;
} elseif($_GET['success']) {
    $host    = $GLOBALS['host'];
    $platipotom = new platipotom();
    $payment = $_SESSION['post_payment'];
    
    $request = array(
        'orderid' => $payment['orderid'], //���������� ������������� ������ � ���� ��������.
        'subid' => $payment['subid'], //���������� ������������� ������������
        'sig' => $platipotom->getSig($payment['price'], $payment['orderid'], $payment['subid']) //������� �������.
    );
    $get = '?';
    foreach ($request as $param => $value) {
        if ($get !== '?') $get .= '&';
        $get .= $param .'='. $value;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host . "/income/platipotom.php".$get);
    curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
    ob_start();
    $res = curl_exec($ch);
    $complete = ob_get_clean();
    
    echo "<p>��������� <strong>�����������</strong>:</p>";
    echo '<pre>';
    print_r(htmlspecialchars($complete));
    echo '</pre>';
    echo '<p><a href="/bill/success/">��������� � �������</a></p>';
    exit;
} else {
    //��������� � ������, �.�. ������ ��� ������ ��� ���� ����������� ��������
    $_SESSION['post_payment'] = $paypost;
}
?>

<h2>�������� ������ ����� �����</h2>
<p>
    ������ ����� ������� <strong>#<?= intval($paypost['subid'])?></strong><br />
    C���� ������ <strong><?= to_money($paypost['price'], 2)?> ������</strong><br />
</p>

<form method="GET" >
    <input type="submit" name="success" value="������� ��������" />
    <input type="submit" name="cancel" value="��������� � �������" />
    <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>"/>
</form>