<?php
// �������� ������ ����� ����� Qiwi
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
if(is_release()) exit;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");


$account = new account;

if(isset($_POST['cancel'])) {
    $back_url = $_SESSION['referer'];
    unset($_SESSION['referer']);
    header("Location: {$back_url}");
    exit;
} elseif(isset($_POST['success']) ) {
    $sum = $_SESSION['post_payment']['sum'];
    $account = new account();
    $account->GetInfo( $_SESSION['post_payment']['ok_f_uid'] );

    //$descr = "OKPAY #".$_SESSION['post_payment']['ok_txn_id']." �� ������� ".$_SESSION['post_payment']['ok_receiver_wallet']." OKPAYID: ".$_SESSION['post_payment']['ok_payer_id']." ����� - ".$_SESSION['post_payment']['ok_item_1_price'].",";
    //$descr .= " ��������� ".$_SESSION['post_payment']['ok_txn_datetime'].", ���� - ".$_SESSION['post_payment']['ok_f_bill_id'];

    $descr = "OKPAY #11 �� ������� OK460571733 OKPAYID: 1111 ����� - ".$_SESSION['post_payment']['ok_item_1_price'].",";
    $descr .= " ��������� ".date("Y-m-d H:i:s").", ���� - ".$_SESSION['post_payment']['ok_f_bill_id'];

    $account->deposit($op_id, $_SESSION['post_payment']['ok_f_bill_id'], $_SESSION['post_payment']['ok_item_1_price'], $descr, 14, $_SESSION['post_payment']['ok_item_1_price'], 12);

    header("Location: /bill/");
    exit;
}

$_SESSION['post_payment'] = $_POST;
$_SESSION['referer']      = $_SERVER['HTTP_REFERER'];

?>

<h2>�������� ������ OKPAY</h2>
<p>
������ ����� ������� #<?= get_uid(false);?>, ����� ������ <?= to_money($_POST['ok_item_1_price'],2)?> ������
</p>

<? if ($created) { ?>
������:
<pre>
<?var_dump($created);?>
</pre>
<? } else { //if?>
<form method="POST" />


    <input type="submit" name="success" value="��������" />
    <input type="submit" name="cancel" value="������" />
    <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>"/>
</form>
<? }//if?>