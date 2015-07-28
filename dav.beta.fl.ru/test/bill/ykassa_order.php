<?php
// �������� ������ ����� ����� ������.�����

define('NO_CSRF', 1);

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/yandex_kassa.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");


if(is_release()) exit;


$host = $GLOBALS['host'];
$_SERVER['HTTP_X_REAL_IP'] = '77.75.157.166';

$payment = $_GET;

//��� ��� ������� ������
$post = array(
    'requestDatetime' => date('c'),
    'action' =>	'checkOrder',
    'shopId' => $payment['ShopID'],//yandex_kassa::SHOPID_DEPOSIT, //����� ��������, ��� ������ ����� �� - � ����� ������� SHOPID_DEPOSIT
    'invoiceId' => $payment['invoiceId'],
    'customerNumber' => $payment['customerNumber'],
    'orderCreatedDatetime' => date('c'),
    'orderSumAmount' => floatval($payment['Sum']),
    'orderSumCurrencyPaycash' => 643,
    'orderSumBankPaycash' => 1001,
    'shopSumAmount' => $payment['Sum'],
    'shopSumCurrencyPaycash' => 643,
    'shopSumBankPaycash' => 1001,
    'paymentPayerCode' => 42007148320,
    'paymentType' => $payment['paymentType']
);

if(isset($payment['orderId'])) {
    $post['orderId'] = $payment['orderId'];
}

$post['md5'] = strtoupper(md5(implode(';', array(
    $post['action'],
    $post['orderSumAmount'],
    $post['orderSumCurrencyPaycash'],
    $post['orderSumBankPaycash'],
    $post['shopId'],
    $post['invoiceId'],
    $post['customerNumber'],
    YK_KEY
))));

if (!empty($payment)) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host . "/income/ykassa.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    ob_start();
    $res = curl_exec($ch);
    $complete = ob_get_clean();
}

$list = $DB->rows("
    SELECT 
        br.id,
        br.ammount,
        a.id AS cn
    FROM bill_reserve AS br
    INNER JOIN account AS a ON a.uid = br.uid
    ORDER BY id 
    DESC LIMIT 10
");

if ($list) {
    foreach ($list as $el) {
        $invoceId = rand(1, 50000);
        $ammount = (int)$el['ammount'];
        $url = "{$host}/test/bill/ykassa_order.php?scid=52128&ShopID=17004&Sum={$ammount}&customerNumber={$el['cn']}&paymentType=PC&orderId={$el['id']}&invoiceId={$invoceId}";
        echo "<p><a href='{$url}'>{$url}</a></p>";
    }
}

/*
$q = (!empty($payment))?http_build_query($payment):null;
$host .= "/test/bill/ykassa_order.php";
$host .= ($q)?"?{$q}":"?scid=52128&ShopID=17004&Sum=399&customerNumber=179&paymentType=PC&orderId=610&invoiceId=7777";
*/
//echo "<p><a href='{$host}'>{$host}</a></p>";

echo "<p>��������� <strong>checkOrder</strong>:</p>";
echo '<pre>';
print_r(htmlspecialchars($complete));
echo '</pre>';


exit;

















$uid = get_uid(false);
$key = 'post_payment_' . $uid;
$memBuff = new memBuff();


if(isset($_GET['cancel'])) { //����� �� �������
    header("Location: /bill/fail");
    exit;
} elseif(isset($_GET['success'])) {
    
    
    $host    = $GLOBALS['host'];
    $payment = $memBuff->get($key);
    
    $_SERVER['HTTP_X_REAL_IP'] = '77.75.157.166';
    
    
    $invoceId    = (isset($payment['invoiceId']))?$payment['invoiceId']:rand(1, 50000);
    
    //��� ��� ������� ������
    $post = array(
        'requestDatetime' => date('c'),
        'action' =>	'checkOrder',
        'shopId' => $payment['ShopID'],//yandex_kassa::SHOPID_DEPOSIT, //����� ��������, ��� ������ ����� �� - � ����� ������� SHOPID_DEPOSIT
        'invoiceId' => $invoceId,
        'customerNumber' => $payment['customerNumber'],
        'orderCreatedDatetime' => date('c'),
        'orderSumAmount' => floatval($payment['Sum']),
        'orderSumCurrencyPaycash' => 643,
        'orderSumBankPaycash' => 1001,
        'shopSumAmount' => $payment['Sum'],
        'shopSumCurrencyPaycash' => 643,
        'shopSumBankPaycash' => 1001,
        'paymentPayerCode' => 42007148320,
        'paymentType' => $payment['paymentType']
    );
    
    if(isset($payment['orderId'])) {
        $post['orderId'] = $payment['orderId'];
    }
    
    $post['md5'] = strtoupper(md5(implode(';', array(
        $post['action'],
        $post['orderSumAmount'],
        $post['orderSumCurrencyPaycash'],
        $post['orderSumBankPaycash'],
        $post['shopId'],
        $post['invoiceId'],
        $post['customerNumber'],
        YK_KEY
    ))));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host . "/income/ykassa.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    ob_start();
    $res = curl_exec($ch);
    $complete = ob_get_clean();
    
    echo "<p>��������� <strong>checkOrder</strong>:</p>";
    echo '<pre>';
    print_r(htmlspecialchars($complete));
    echo '</pre>';
    
    
    
    
    print_r(http_build_query($payment));
    exit;
    
    
    
    
    
    $xmlObj = @simplexml_load_string($complete);
    
    //������ ����������� ������������� 
    //���� ������ ����� ��������� ������
    if($xmlObj && (int)$xmlObj->attributes()->code === 0)
    {
        sleep(2);

        $post['action'] = 'paymentAviso';
        $post['md5'] = strtoupper(md5(implode(';', array(
            $post['action'],
            $post['orderSumAmount'],
            $post['orderSumCurrencyPaycash'],
            $post['orderSumBankPaycash'],
            $post['shopId'],
            $post['invoiceId'],
            $post['customerNumber'],
            YK_KEY
        ))));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host . "/income/ykassa.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        ob_start();
        $res = curl_exec($ch);
        $complete = ob_get_clean();
        
        echo "<p>��������� <strong>paymentAviso</strong>:</p>";
        echo '<pre>';
        print_r(htmlspecialchars($complete));
        echo '</pre>';
    }
    
    echo '<p><a href="/bill/success/">��������� � �������</a></p>';
    exit;
    
    
} else {
    //������ ��������� �����
    $paypost = $_POST;
    //��������� � ������, �.�. ������ 
    //��� ������ ��� ���� ����������� ��������
    $memBuff->set($key, $paypost);
}

$payway = array(
    yandex_kassa::PAYMENT_YD => "������.������",
    yandex_kassa::PAYMENT_AC => "�����",
    yandex_kassa::PAYMENT_WM => "Webmoney",
    yandex_kassa::PAYMENT_AB => "�����-����",
    yandex_kassa::PAYMENT_SB => "�������� ������"
);
?>

<h2>�������� ������ ������.�����</h2>
<p>
    ������ ����� ������� <strong>#<?= intval($paypost['customerNumber'])?></strong><br />
    C���� ������ <strong><?= to_money($paypost['Sum'], 2)?> ������</strong><br />
    C����� ������ <strong><?= $payway[$paypost['paymentType']]?></strong><br />
    IP c������ ������� <strong><?=getRemoteIp()?></strong><br />
    ShopID <strong><?=$paypost['ShopID']?></strong><br />
    Bill reserve ID: <strong><?=@$paypost['orderId']?></strong>
</p>

<form method="GET" action="ykassa.php" >
    <input type="submit" name="success" value="������� ��������" />
    <input type="submit" name="cancel" value="��������� � �������" />
    <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>"/>
</form>