<?php
require_once $_SERVER["DOCUMENT_ROOT"].'/classes/stdf.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/classes/account.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/classes/payed.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/classes/users.php';?>
<div style="padding-left:25px; padding-bottom:100px">
 ����� ������������� ����� ����� � ������� bolvan1.<br/><br/>
 ��������<br/><br/>
 <a href="/test/hand-test-gift-sber-webmoney.php?oc=95&uid=237169"> ���������� ����� ��� bolvan1 �� 2000</a><br/> 
 <a href="/test/hand-test-gift-sber-webmoney.php?oc=96&uid=237169"> ���������� ����� ��� bolvan1 �� 1000</a><br/> 
 <a href="/test/hand-test-gift-sber-webmoney.php?oc=97&uid=237169"> ���������� ����� ��� bolvan1 �� 5000</a><br/><br/> 
 <a href="/test/hand-test-gift-sber-webmoney.php?oc=97&uid=237962"> ���������� ����� ��� givejob1 �� 5000</a><br/><br/><br/>
 
 Webmoney<br/><br/>
 <a href="/test/hand-test-gift-sber-webmoney.php?oc=91&uid=237169"> ���������� ����� ��� bolvan1 �� 2000</a><br/>
 <a href="/test/hand-test-gift-sber-webmoney.php?oc=93&uid=237169"> ���������� ����� ��� bolvan1 �� 5000</a><br/><br/>
 <a href="/test/hand-test-gift-sber-webmoney.php?oc=93&uid=237962"> ���������� ����� ��� givejob1 �� 5000</a><br/>
 </div><div style="text-align:center; width:100%">
<?php

$uid = __paramInit("int", "uid", null, 0);
$uid = intval($uid);
$opcode = intval(__paramInit("int", "oc", null, 0));
if ( $opcode != 95 && $opcode != 96 && $opcode != 97 && $opcode != 91 && $opcode != 93   ) {
 $opcode = 0;
}
$admin = 103;
if ( $uid > 0 ) {
    global $DB;
    //��������� ���������
    if ( $uid && $opcode ) {
        //�����
        $account = new account();
        $payed = new payed();
        $op_code = $opcode;
        $tr_id = $account->start_transaction($admin);
        $error = $account->Gift($id, $gid, $tr_id, $op_code, $admin, $uid, "������������!!!", "", 1);//$payed->GiftOrderedTarif($bill_id, $gift_id, $uid, $admin, $tr_id, $interval, "������������ ���������� ����� ����� �������� ��� �������", $op_code);
        if( $error ) {
            echo "��������� �����-�� ������, ��� ������ ".$error;
        } else {
            echo "<a href='/login.php' target='_blank'>�������� �� ����, ����� �������� ���������</a>";
        }
    }
} 
?></div>