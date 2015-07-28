<?php
/*
 * ������, ����������� �������� "��������" ����� ������ ��� ������������� ������������*/
$session_fail = 0;
if (count($_POST) > 0) $session_fail = 1;
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/stdf.php";
if (!((count($_POST) == 0)&&($session_fail))) $session_fail = 0;
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/memBuff2.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/search/sphinxapi.php";
require_once($_SERVER['DOCUMENT_ROOT'] ."/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] ."/classes/payed.php");
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects_offers_answers.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/firstpage.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/firstpagepos.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer_offers.php");
$purchaseController = new CPurchaseServiceController($session_fail);
class CPurchaseServiceController {
    private $session_fail = 0;
    public function __construct($session_fail){
        $this->session_fail = $session_fail;
        $this->processRequest();
    }
    
    public function processRequest() {        
        $action = __paramInit("string", "", "action");        
        switch ($action) {
            case "setpro":
                $this->setProMonth();
                break;
            case "setproWeek":
                $this->setProWeek();
                break;
            case "setAnswers":
                $this->setAnswers();
                break;
            case "setfp":
                $this->setCatalogOrFpPlace();
                break;
            case "setrb":
                $this->setCatalogOrMainPageRoundabout();
                break;
            case "set_upfp":
                $this->setUpActionInCatalogOrFpPlace();
                break;
            case "setim":
                $this->setIMakeValues();
                break;               
            default:
                if ($this->session_fail) $this->jsonError("��������, �� �������������� �� ����� � ������ ������� ����� ��������.\n�������� ��������");    
        }
    }
    
    private function setIMakeValues() {        
        global $DB;
        $uid     = (int)$_POST["uid"];
        $rawIds  = $_POST["ids"];        
        $role  = $DB->val("SELECT role FROM users WHERE uid = $uid");
        $role = $role[0];
        if ($role === '0') {
            $aIds = explode(",", $rawIds);
            foreach ($aIds as $i) {
                $pair  = explode(":", $i);
                $group = $pair[0];
                $spec  = $pair[1];
                if (($spec === '0')||(intval($spec) != 0)){
                	if (($group === '0')||(intval($group) != 0)) {
                		//----------------------------------------
                	    $frl_offers = new freelancer_offers();
                        $create = array("user_id" => $uid,
                            "title"          => iconv("UTF-8", "WINDOWS-1251//IGNORE", $_POST['title']),
                            "descr"          => iconv("UTF-8", "WINDOWS-1251//IGNORE", $_POST['text']),
                            "category_id"    => intval($group),
                            "subcategory_id" => intval($spec)
                            );            
                        $account = new account;
                        $transaction_id = $account->start_transaction($uid, $tr_id);
                        $error = $account->Buy($billing_id, $transaction_id, freelancer_offers::FM_OP_CODE, $uid, "������� ���������� ����������� ����������", "������� ���������� �����������", 1, 0);
                        if ($error) $this->jsonError($error);
                        $account->commit_transaction($transaction_id, $uid, $billing_id);
                        $create['bill_id'] = $billing_id;
                        $id_offer = $DB->insert('freelance_offers', $create, 'id');            
                        if($id_offer > 0) {
                           $this->jsonOk();
                        }
                    }
                }
            }
        }
        else {
            $this->jsonError("������������ �� ������ ��� �������� �������������");
        }
    }
    
    private function setAnswers() {
        global $DB;
        $uid     = (int)$_POST["uid"];
        $amount  = (int)$_POST["amount"];
        if (($amount != 1)&&($amount != 5)&&($amount != 10)) {
            $this->jsonError("������������ �������� ���������� FM");
        }
        $role  = $DB->val("SELECT role FROM users WHERE uid = $uid");
        $role = $role[0];
        if ($role === '0'){
            $answers = new projects_offers_answers;
            $error = $answers->BuyByFM($uid, $amount);
            if ($error === 0) {
                $this->jsonOk();          
            }else {
                $this->jsonError($error?$error:"��������� ����������� ������");
            }
        }
        else {
            $this->jsonError("������������ �� ������ ��� �������� �������������");
        }
    }
    /**
     * ��������� ��� ��� ������������� ������������ �� ��������� ������     
     * */
    private function setProWeek() {
        global $DB;
        $week = (int)$_POST["weeks"];
        if (($week > 0)&&($week < 11)) {
            $this->setProMonth($week);            
        }else{
            $this->jsonError("������������ ���������� ������");
        }
    }
    /**
     * ��������� ��� ��� ������������� ������������
     * @param $count - ���� ����� 1, ������ �������� ��� �� ��������� ������� � ����������� �� �������� $_REQUEST['type']
     *                 ���� ������ 1, ������ ������� ���������� �� ��������� ($count) ������     
     * */
    private function setProMonth($count = 1) {
        global $DB;
        $uid  = (int)$_POST["uid"];
        $uid  = $DB->val("SELECT uid FROM users WHERE uid = $uid");
        $date = $_POST["date"];        
        $f = preg_match("#[0-9]{4}\-[0-9]{2}\-[0-9]{2}#", $date, $m);
        if (!$f) $date = false;
        if ($uid) {
           $account = new account();
           $transaction_id = $account -> start_transaction($uid, $tr_id);
             $oppro = intval(trim($_POST['type']));
             $prof = new payed();
           if($oppro <= 0)           $oppro = is_emp()?15:48;
           if ($oppro == 47) { //��������� ������������ ������ �������� ���
               $sql = "DELETE FROM orders WHERE id IN (SELECT id FROM orders WHERE from_id = '$uid' AND ordered = '1' AND payed = 't' AND tarif IN (1,2,3,4,5,6,15,16,28,35,42,47,48,49,50,51,52,76))";
               $DB->query($sql);
           }
           $rewriteFromDate = false; //������������ �� ���� ������ �������� PRO � ����������� ������
           if (($date !== false)&&($date != date("Y-m-d") ) ) { //���� �������� ���� ������� PRO ����� ��� ������� ���� ������� ������� ������� PRO ������� ���� ������� ����� ��������� ����
               $sql = "DELETE FROM orders WHERE id 
                       IN (SELECT id FROM orders 
                           WHERE from_id = '$uid' AND ordered = '1' AND payed = 't' 
                           AND tarif IN (1,2,3,4,5,6,15,16,28,35,42,47,48,49,50,51,52,76)
                            AND from_date > '$date'
                           )";               
               $res = $DB->query($sql);
               $num = pg_affected_rows($res);
               if ($num) $rewriteFromDate = true;
           }
           //���������� ����� ��������� � ������� ��� ��� ������� ��� 
           $ok = $prof->SetOrderedTarif($uid, $transaction_id, $count, "������� PRO", $oppro, $error);
           if ($ok) {
               if (!$rewriteFromDate) {
                   require_once($_SERVER['DOCUMENT_ROOT'] ."/classes/session_Memcached.php");
                   $session = new session();
                   $login = $DB->val("SELECT login FROM users WHERE uid = $uid");
                   $session->UpdateProEndingDate($login);
               }       
           }else {
                   $this->json("status", "error", "msg", ($error?$error:"����������� ������"));
           }
           if ($rewriteFromDate) { //���� �������� ���� ������� PRO ����� ��� ������� ���� ������������� ���� ��������� ������� � ���������� ����
                  $sql = "SELECT id FROM orders WHERE posted = (SELECT MAX(posted) FROM orders WHERE from_id = $uid)";
                  $id = $DB->val($sql);
                  if ($id) {
                         $date .= " ".date("H:i:s").".".date("u");
                         $sql = "UPDATE orders SET from_date = '$date' WHERE id = $id";                         
                         $DB->query($sql);                         
                         require_once($_SERVER['DOCUMENT_ROOT'] ."/classes/session_Memcached.php");
                   $session = new session();
                   $login = $DB->val("SELECT login FROM users WHERE uid = $uid");
                   $session->UpdateProEndingDate($login);
                  }else $this->json("status", "error", "msg", "������ ��� ��������� ���� ������� ��� '$date'");                  
           }/**/ 
           $this->json("status", "ok");
        }else {            
            $this->json("status", "error", "msg", "������������ �� ������");    
        }
    }
    
    /**
     * ������� ����� � �������� �� ������� � �������� ��������
     * */
    private function setCatalogOrMainPageRoundabout() {        
        global $DB;
        $uid  = (int)$_POST["uid"];
        $row  = $DB->row("SELECT role, uname, usurname, login, sum  FROM users LEFT JOIN account ON account.uid = users.uid 
                          WHERE users.uid = $uid");
        $role = $row["role"][0];
        $user = $row['uname']." ".$row['usurname']." [".$row['login']."]";
        $sum  = $row['sum'];
        if ($role !== '0') {
            $this->jsonError("������������ �� ������ ��� �������� �������������");
        }
        $date = $_POST["date"];        
        $f = preg_match("#[0-9]{4}\-[0-9]{2}\-[0-9]{2}#", $date, $m);
        if (!$f) $date = false;
        if ($uid) {
            $catalog = ($_POST['type'] == "catalog");
            if($_POST['type'] == "catalog") {
                $tarif = 73;
            } else if ($_POST['type'] == "main") {
                $tarif = 65;
            }
            $payPlace = new pay_place($catalog?1:0);
            $account = new account();
            $transaction_id = $account->start_transaction($uid, $tr_id);
    
            if(($buy = $account->Buy($id, $transaction_id, $tarif, $uid, '������ ����� ������� ���� �� FM', '��������', 1)) === 0) {
                $payPlace->addUser($uid);
                $t = intval($payPlace->getTimeShow());
                $msg = 
                  "$user ����� �������� ".($catalog ? '� <a href="/freelancers/">��������</a> ' : '�� <a href="/">������� ��������</a> ')
                  . ($t==0 ? '������' : "����� {$t} �����".($t==1 ? '�' : ($t>1&&$t<5 ? '�' : ''))).'.';
                $this->jsonOk("msg", $msg);
            } else {                
                $msg = "� ������ ������ �� ����� � $user $sum FM. <a href=\"/bill\" target=\"_blank\">��������� ����</a><br/><br/>";
                $this->jsonError($msg);
           }
        }         
    }
    /**
     * ������� ����� �� ������� ��� � �������� ��� ��� ������������� ������������
     * */
    private function setCatalogOrFpPlace() {
        global $DB;
        $uid  = (int)$_POST["uid"];
        $role  = $DB->val("SELECT role FROM users WHERE uid = $uid");
        $role = $role[0];
        if ($role !== '0'){
            $this->jsonError("������������ �� ������ ��� �������� �������������");
        }
        $date = $_POST["date"];        
        $f = preg_match("#[0-9]{4}\-[0-9]{2}\-[0-9]{2}#", $date, $m);
        if (!$f) $date = false;
        $count = (int)$_POST["weeks"];
        if (!$count) $this->jsonError("������������ �������� ������");
        if ($uid) {
            $sIds = $_POST['ids'];
            $aIds = explode(",", $sIds);
            $fp_request = array();
            $valid = false;
            for ($i = 0; $i < count($aIds); $i++) {
                $id = str_replace("-", "", $aIds[$i]);
                if (!preg_match("#[\D]#", $id, $m)&&(!( ($aIds[$i][0] == '0')&&(strlen($aIds[$i]) > 1) ) ) ) {
                    $valid = true;
                }
                $fp_request[$aIds[$i]] = $count; 
            }
            if (!$valid) $this->jsonError("������������ �������� ������������� � ������");
            $sProlong = $_POST['a'];
            $aProlong = explode(",", $sProlong);
            $valid = true;
            if (strlen($sProlong) > 0) {
                for ($i = 0; $i < count($aProlong); $i++) {
                        $id = str_replace("-", "", $aProlong[$i]);
                        if (preg_match("#[\D]#", $id, $m)||( ($aProlong[$i][0] == '0')&&(strlen($aProlong[$i]) > 1) ) )  {
                            $valid = false;
                        }
                }
                if (!$valid) $this->jsonError("������������ �������� ������������� � ������ ����������������");
            }else $valid = false;
            
            $account = new account();
            $transaction_id = $account -> start_transaction($uid, $tr_id);
            $prof = new firstpage();
            foreach($aIds as $prof_id) {
                $prof->delAutoPayed($uid, $prof_id);
            }
            $rewriteFromDate = false;
            if ( $date&&($date != date('Y-m-d')) ) {
                $date .= " ".date('H:i:s');//.".".date("u");
                $query = "DELETE FROM users_first_page 
                          WHERE user_id = $uid 
                          AND from_date > '$date'
                          AND profession IN ($sIds)
                          ";
               $res = $DB->query($query);
               $num = pg_affected_rows($res);               
               if ($num) $rewriteFromDate = true;                
            }
            $st = $prof->SetOrdered($uid, $transaction_id, $fp_request, $null, $err);
            if ($st == 0) {
            	$this->jsonError($err);
            }
            if ($rewriteFromDate) {
                $recs = array();
                foreach ($aIds as $id) {
                    $query = "SELECT id FROM users_first_page WHERE user_id = $uid AND first_post = 
                               (SELECT max(first_post) FROM users_first_page WHERE user_id = $uid AND profession = $id)";                    
                    $recId = $DB->val($query);
                    if ($recId) {
                        $recs[] = $recId;
                    }
                }
                if (count($recs)) {
                    $r = join(",", $recs);
                    $query = "UPDATE users_first_page SET from_date = '$date' WHERE id IN ($r)";                    
                    $DB->query($query);
                }                                 
            }
            if ($valid) foreach ($aProlong as $prof_id) {
                $prof->setAutoPayed($uid, $prof_id);
            }
            $this->jsonOk();
        }
    }
    
    /**
     * ��������� ����� � �������� ��� �� �������
     * */
    private function setUpActionInCatalogOrFpPlace() {
    	global $DB;
        $uid  = (int)$_POST["uid"];
        $role  = $DB->val("SELECT role FROM users WHERE uid = $uid");
        $role = $role[0];
        if ($role !== '0'){
            $this->jsonError("������������ �� ������ ��� �������� �������������");
        }
        if ($uid) {
  	        $prof = new firstpagepos();
			$ids = $_POST['ids'];
			$aIds = array();
			$sIds = explode(",", $ids);
			$sum = 5;//!!
			foreach ($sIds as $id) {
			    if ($id === '0') {
			        $aIds[$id] = $sum;
			    }
			    elseif (intval($id) != 0) {
			        $aIds[intval($id)] = $sum;
			    }
			}
			$account = new account();
			$transaction_id = $account->start_transaction($uid, $tr_id);
			$orderId = $prof->BidPlaces($uid, $transaction_id, $aIds, $error);
			if ($orderId == 0) $this->jsonError($error);
			$this->jsonOk();
	   }
    }
    
    private function json() {
        $sz = func_num_args();        
        if (($sz == 0) || ($sz % 2 != 0)){
            echo json_encode(array("status"=>"error", "msg"=>"Invalid number arguments in CPurchaseServiceController::json"));
            exit;
        }
        $data = array();
        for ($i = 0;  $i < $sz; $i += 2){
            $v = func_get_arg($i + 1);
            $k = func_get_arg($i);
            $data[$k] =  iconv("WINDOWS-1251", "UTF-8//IGNORE", $v);
        }
        echo json_encode($data);
        exit;
    }
    
    private function jsonOk() {
        $sz = func_num_args();        
        if (($sz % 2 != 0)){
            echo json_encode(array("status"=>"error", "msg"=>"Invalid number arguments in CPurchaseServiceController::jsonOk"));
            exit;
        }
        $data = array("status"=>"ok");        
        for ($i = 0;  $i < $sz; $i += 2){
            $v = func_get_arg($i + 1);
            $k = func_get_arg($i);
            $data[$k] =  iconv("WINDOWS-1251", "UTF-8//IGNORE", $v);
        }
        echo json_encode($data);
        exit;
    }
    
    private function jsonError($msg) {
        $sz = func_num_args();        
        if (($sz == 0) || ($sz % 2 == 0)){
            echo json_encode(array("status"=>"error", "msg"=>"Invalid number arguments in CPurchaseServiceController::jsonError"));
            exit;
        }
        $data = array("status"=>"error");
        $data["msg"] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $msg);
        for ($i = 1;  $i < $sz; $i += 2){
            $v = func_get_arg($i + 1);
            $k = func_get_arg($i);
            $data[$k] =  iconv("WINDOWS-1251", "UTF-8//IGNORE", $v);
        }
        echo json_encode($data);
        exit;
    }
    
    private function log($s) {
        $h = fopen("/home/andrey/flclog/log.txt", "a");
        fwrite($h, "============\r\n$s\r\n====================\r\n");
        fclose($h);
    }
    
}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<link href="/css/block/b-combo/b-combo.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/scripts/mootools-new.js"></script>
<script type="text/javascript" src="/scripts/b-combo/b-combo-dynamic-input.js"></script>
<script type="text/javascript" src="/scripts/b-combo/b-combo-multidropdown.js"></script>
<script type="text/javascript" src="/scripts/b-combo/b-combo-autocomplete.js"></script>
<script type="text/javascript" src="/scripts/b-combo/b-combo-calendar.js"></script>
<script type="text/javascript" src="/scripts/b-combo/b-combo-manager.js"></script>
<script type="text/javascript" >
/* ��������!  ����������� ���� ���������� ��� ������������� ������� �� ����� �� �����, ��� ���
 * ��� ��� ����������  
 */ 
 var _TOKEN_KEY = '<?=$_SESSION['rand']?>'; 
</script>
<script type="text/javascript">
/*��������� ����� ������������*/ 
    function onSelectUser(){
        var i = $('user_db_id');
        if (i) {
            var id = Number(i.value);
            if (id) {
                upFirstPageAndCatalog_onChangeUser(id);                
            }
        }
    }
//process PRO purchase
    function processSelectedValue() {
        if ($('pro_type_db_id').value == 47) {
            $('pro_block_message').set('html', '<div style="color:#ff0000; font-weight:bold">\
    ��������! ������� ��������� �������� ��� �������������, ��� �������� ����� ���������� ��� ��� ��������� ��� ���������� ������ � �������� PRO \
    ���� �������������.</div>');
        }else $('pro_block_message').set('html','');
        var v = 'none';
        if ($('pro_type_db_id').value == 76) {
            v = 'block';
        }
        $('pro_week_purchase').setProperty("style", "display:" + v);
    }
    
    function setPro(){
        var uid     = parseInt($('user_db_id').value);
        var proType = parseInt($('pro_type_db_id').value);        
        if (uid && proType) {
            var req = new Request.JSON(
            {
                url: window.location.href,
                onSuccess: onProSuccess,
                onFailure: onProFail
            }
        );
        var action = "setpro";
        if ($('pro_type_db_id').value == 76) {
            action = 'setproWeek';
        }
        var data = "u_token_key=" + _TOKEN_KEY + "&action=" + action + "&uid=" + uid + "&type=" + proType + "&date=" + $('date_from_pro_eng_format').value + "&weeks=" + $('countWeek').value;
        $('setProButton').disabled = true;
        ComboboxManager.getInput("pro_type").setDisabled(1);
        ComboboxManager.getInput("user").setDisabled(1);    
        req.post(data);
        }else {
            if (!uid) {
                alert("���������� ������� ������������ �� ������");
                return;
            }
            if (!proType) {
                alert("���������� ������� ������� ������� �������� PRO �� ������");
                return;
            }
        }
    }

    function onProSuccess(data){
        $('setProButton').disabled = false;
        ComboboxManager.getInput("pro_type").setDisabled(0);
        ComboboxManager.getInput("user").setDisabled(0);
        if (data.status != "ok") alert(data.msg);
         else alert("�������");
    }
    
    function onProFail(){
    }

    function incProWeek() {
        var v = Number($('countWeek').value);        
        $('countWeek').value =  ((v + 1) < 10)?(v + 1):10;
    }
    
    function decProWeek() {
        var v = Number($('countWeek').value);
        $('countWeek').value =  ((v - 1) > 1)?(v - 1):1;
    }
    
    //process answers purchase
    function setAnswers() {
        var uid     = parseInt($('user_db_id').value);
        var amount = parseInt($('amountAnswers_db_id').value);        
        if (uid && amount) {
            var req = new Request.JSON(
            {
                url: window.location.href,
                onSuccess: onAnswersSuccess,
                onFailure: onAnswersFail
            }
        );
        var action = "setAnswers";
        var data = "u_token_key=" + _TOKEN_KEY + "&action=" + action + "&uid=" + uid + "&amount=" + amount;
        $('setAnswersButton').disabled = true;
        ComboboxManager.getInput("amountAnswers").setDisabled(1);
        ComboboxManager.getInput("user").setDisabled(1);    
        req.post(data);
        }else {
            if (!uid) {
                alert("���������� ������� ������������ �� ������");
                return;
            }
            if (!amount) {
                alert("���������� ������� ���������� FM ��� ������� �� ������");
                return;
            }
        }
    }
    function onAnswersSuccess(data){
        $('setAnswersButton').disabled = false;
        ComboboxManager.getInput("amountAnswers").setDisabled(0);
        ComboboxManager.getInput("user").setDisabled(0);
        if (data.status != "ok") alert(data.msg);
         else alert("�������");
    }
    
    function onAnswersFail(){    }

    //menu
    function showBlock(s){
        var ls = $$(".fieldGroup");        
        for (var i = 0; i < ls.length; i++){
            var v = 'none';
            if (ls[i].id == s) v = 'block';
               ls[i].style.display = v; 
        }
    }
    
    //first page and catalog
    /*
     ������� ��������� ��������� ������ �������� � "�����"
     "�����" - ��� ������ ��������������� �������� �������� � �������, � �����  ������ �������������    
    */
    function addProfession() {
        var cId    = $('profs_column_id').value;
        var profId = $('profs_db_id').value;
        if ((cId != 0)||($('fpmainpage').checked)||($('fpmainpage').checked)) {
            if (!$('fph' + profId)) {
                var div = $('selProfIds');
                var h = new Element("input", {'type':'hidden', 'value':profId, 'id':'fph' + profId});
                h.inject(div, 'bottom');
                var div = $('selProfView');
                var t = new Element("div", {'html':$('profs').value + ' <a id="plink' + profId + '" href="javascript:prolong(' + profId + ')" style="color:#980000">������������� ����.</a><input type="button" value="[x]" onclick="delProfItem(' + profId + ')" />', 'style':'float:left; padding-right:15px;font-size:11px', 'id':'fpt' + profId});
                t.inject(div, 'bottom');
            }
        }else alert('���������� ������� ��������� ��� ������� �������� ��� ��� ������� ��������');
    }
    /*
     ������� ������� DOM-�������   
    */
    function rem(id){
        var e = $(id);
        if (e){
            var p = e.parentNode;
            if (p){
                p.removeChild(e);
            }
        }
    }
    /*
     ������� �������� ��� ��������� ������������� ��� ���������� ����� ���������� 
    */
    function prolong(id) {
        var rawId = id;
        var link = $('plink' + id);
        id = 'fphprolong' + id;
        if (!$(id)) {
            var div = $('selProfProlong');
            var h = new Element("input", {'type':'hidden', 'value':rawId, 'id':id});
            h.inject(div, 'bottom');
            link.setProperty("style", "color:#009800");
            link.setProperty("text", "������������� ���.");
        }else {
            rem(id);
            link.setProperty("style", "color:#980000");
            link.setProperty("text", "������������� ����.");
        }
    }
    /*
     ������� ������� ������ � ���������� ������ �� ������� � � ��������
    */
    function delProfItem(id) {        
        rem('fph' + id);
        rem('fpt' + id);
        rem('fphprolong' + id);
    }
    /*
     ������� ����������� ���������� ���������� ������ ���������� �� ������� ��� � ��������
    */
    function incFpWeek() {
        var v = Number($('countFpWeek').value);        
        $('countFpWeek').value =  ((v + 1) < 1000)?(v + 1):1000;
    }
    /*
     ������� ��������� ���������� ���������� ������ ���������� �� ������� ��� � ��������
    */
    function decFpWeek() {
        var v = Number($('countFpWeek').value);
        $('countFpWeek').value =  ((v - 1) > 1)?(v - 1):1;
    }
    /*
    ������� �������� �������������� �������� � ������������� � ���� ������
    (������������ � setFp) 
    */
    function getFpAndCatalogIds() {
        var pages = new Array();
        var ls = $("selProfIds").getElements("input");
        for (var i = 0; i < ls.length; i++) {
            pages.push(ls[i].value);  
        }
        if ($('fpmainpage').checked == true) pages.push(-1);
        if ($('fpcatalog').checked == true) pages.push(0);
        if (pages.length) {
            var s = "&ids=" + pages.join(",");
            var prolong = new Array();
            var ls = $("selProfProlong").getElements("input");
            for (var i = 0; i < ls.length; i++) {
                prolong.push(ls[i].value);  
            }
            if (prolong.length) {
                var p = prolong.join(",");
                s += "&a=" + p;
            }
            return s;
        }
        return ''; 
    }
    /*
     ������� ���������� ������ ������� ���������� ���� �� �������  � � �������� �������� �� ������
    */
    function setFp(){
        var uid     = parseInt($('user_db_id').value);
        var ids = getFpAndCatalogIds();        
        if (uid && ids) {
            var req = new Request.JSON(
                {
                    url: window.location.href,
                    onSuccess: onFpSuccess,
                    onFailure: onFpFail
                }
            );
        var action = "setfp";        
        var data = "u_token_key=" + _TOKEN_KEY + "&action=" + action + "&uid=" + uid + ids + "&date=" + $('date_from_fpc_eng_format').value + "&weeks=" + $('countFpWeek').value;
        $('setFpButton').disabled = true;
        ComboboxManager.getInput("profs").setDisabled(1);
        ComboboxManager.getInput("user").setDisabled(1);    
        req.post(data);
        }else {
            if (!uid) {
                alert("���������� ������� ������������ �� ������");
                return;
            }
            if (!ids) {
                alert("���������� ������� ������� �������� ��� �������� ����� �� �������");
                return;
            }
        }
    }
    /*
     ��������� ���������� ������  
    */
    function onFpSuccess(data){
        $('setFpButton').disabled = false;
        ComboboxManager.getInput("profs").setDisabled(0);
        ComboboxManager.getInput("user").setDisabled(0);
        if (data.status != "ok") alert(data.msg);
         else alert("�������");
    }
    /*
    ��������� ������ ��������� ������
    */
    function onFpFail(){}
    
    //roundabout
    /**
    *�������� ������ ��� g������ ����� � ��������
    */
    function setRb() {
        if (($('rbcatalog').checked)||($('rbmain').checked)) {
            var type = $('rbmain').checked ? 'main': 'catalog';
            var uid     = parseInt($('user_db_id').value);
            if (uid) {
                var req = new Request.JSON(
                    {
                        url: window.location.href,
                        onSuccess: onRbSuccess,
                        onFailure: onRbFail
                    }
                );
                var action = "setrb";        
                var data = "u_token_key=" + _TOKEN_KEY + "&action=" + action + "&uid=" + uid + "&type=" + type;
                $('setRbButton').disabled = true;                
                ComboboxManager.getInput("user").setDisabled(1);    
                req.post(data);
            }else alert("���������� ������� ������������ � ������ ����");
        }else alert("���������� ������� ������� �������� ��� �������");
    }
    /*
    ��������� ���������� ������  
   */
    function onRbSuccess(data) {
        $('setRbButton').disabled = false;        
        ComboboxManager.getInput("user").setDisabled(0);
        var s = "<div>" + data.msg + "</div>";
        if (data.status != "ok") {
            var s = "<div style='color:#ff0000; font-weight:bold;'>" + data.msg + "</div>";
        }        
        $('rb_block_message').set("html", s);
    }
    /*
    ��������� ������ ��������� ������
    */
    function onRbFail(){}

    //up action first page and catalog
    /*
     ������� ��������� ��������� ������ �������� � "�����"
     "�����" - ��� ������ ��������������� �������� �������� � �������    
    */
    function addUpProfession() {        
        var profId = $('profs2_db_id').value;
        if ((profId)||($('upfpmainpage').checked)||($('upfpmainpage').checked)) {
            if (!$('upfph' + profId)) {
                var div = $('selUpProfIds');
                var h = new Element("input", {'type':'hidden', 'value':profId, 'id':'upfph' + profId});
                h.inject(div, 'bottom');
                var div = $('selUpProfView');
                var t = new Element("div", {'html':$('profs2').value + ' <input type="button" value="[x]" onclick="delUpProfItem(' + profId + ')" />', 'style':'float:left; padding-right:15px;font-size:11px', 'id':'upfpt' + profId});
                t.inject(div, 'bottom');
            }
        }else alert('���������� ������� ��������� ��� ������� �������� ��� ��� ������� ��������');
    }    
    
    /*
     ������� ������� ������ � ���������� ������ �� ������� � � ��������
    */
    function delUpProfItem(id) {        
        rem('upfph' + id);
        rem('upfpt' + id);        
    }    
    /*
    ������� �������� �������������� �������� � ������������� � ���� ������
    (������������ � setUpFp) 
    */
    function getUpFpAndCatalogIds() {
        var pages = new Array();
        var ls = $("selUpProfIds").getElements("input");
        for (var i = 0; i < ls.length; i++) {
            pages.push(ls[i].value);  
        }
        if ($('upfpmainpage').checked == true) pages.push(-1);
        if ($('upfpcatalog').checked == true) pages.push(0);
        if (pages.length) {
            var s = "&ids=" + pages.join(",");            
            return s;
        }
        return ''; 
    }
    /*
     ������� ���������� ������ ������� ���������� ���� �� �������  � � �������� �������� �� ������
    */
    function setUpFp(){
        var uid     = parseInt($('user_db_id').value);
        var ids = getUpFpAndCatalogIds();        
        if (uid && ids) {
            var req = new Request.JSON(
                {
                    url: window.location.href,
                    onSuccess: onUpFpSuccess,
                    onFailure: onUpFpFail
                }
            );
        var action = "set_upfp";        
        var data = "u_token_key=" + _TOKEN_KEY + "&action=" + action + "&uid=" + uid + ids;
        /*$('setUpFpButton').disabled = true;
        ComboboxManager.getInput("profs2").setDisabled(1);
        ComboboxManager.getInput("user").setDisabled(1);/**/    
        req.post(data);
        }else {
            if (!uid) {
                alert("���������� ������� ������������ �� ������");
                return;
            }
            if (!ids) {
                alert("���������� ������� ������� �������� ��� ����� �� �������");
                return;
            }
        }
    }
    /*
     ��������� ���������� ������  
    */
    function onUpFpSuccess(data){
        $('setUpFpButton').disabled = false;
        ComboboxManager.getInput("profs2").setDisabled(0);
        ComboboxManager.getInput("user").setDisabled(0);
        if (data.status != "ok") alert(data.msg);
         else alert("�������");
    }
    /*
     ��������� ������ ��������� ������
    */
    function onUpFpFail(){}
    /*
     ��������� ����� ������������
    */
    function upFirstPageAndCatalog_onChangeUser(uid) {
        /*�������� �������� ��������, � ������� �������� ������������*/
        var p = ComboboxManager.getInput("profs2");
        p.loadData("getUserPlacesWithoutMainAndAllSection", uid);
        /*������� ����� �� ��������� ���������� �� ����������� ������������*/
        function clearDiv(id) {
        	var div = $(id);
            div.set("html", "");
        }
        clearDiv("selUpProfIds");
        clearDiv("selUpProfView");
    }

    //������ ������ !!!!
    /*
     ������� ��������� ��������� ������ �������� � "�����"
     "�����" - ��� ������ ��������������� �������� �������� � �������, � �����  ������ �������������    
    */
    function addIMakeProfession() {
        var cId    = parseInt($('profs3_column_id').value);
        var profId = parseInt($('profs3_db_id').value);
        if ((String(cId) == "NaN")|| (String(profId) == "NaN")) {
        	alert('���������� ������� ������');
        	return;
        }        
        var groupId = profId;
        var allSpec = "��� �������������";
        var v = $('profs3').value;
    	if (cId == 1) {
            var cb  = ComboboxManager.getInput("profs3");
            groupId = cb.breadCrumbs[0];
        }else {
            profId = 0;
            if (v.indexOf(allSpec) == -1) v += ":" + allSpec;
        }
        if (!document.IMakeString) {
            document.IMakeString = new String("");
            document.IMakeArray  = new Array();
        }
        if (document.IMakeString.indexOf(groupId + ":" + profId) == -1) {
        	document.IMakeArray.push(groupId + ":" + profId);
        	document.IMakeString = document.IMakeArray.join(",");
        	div = $("selIMakeProfView");
        	var t = new Element("div", {'html':v + ' <input type="button" value="[x]" onclick="delProfItemIMake(\'' + groupId + "_" + profId + '\')" />', 'style':'float:left; padding-right:15px;font-size:11px', 'id':'imv' + groupId + "_" + profId});
            t.inject(div, 'bottom');
        }
    } 
    /*
     ������� ������� ������ � ������������ �������������� � ������� ������
    */
    function delProfItemIMake(id) {
        id = id.replace("_", ":");
    	console.log(document.IMakeArray);
        var arr = new Array(); 
        for (var i = 0; i < document.IMakeArray.length; i++) {
            if (document.IMakeArray[i] != id) {
                arr.push(document.IMakeArray[i]);
            }
        }
        document.IMakeArray = arr;
        document.IMakeString = document.IMakeArray.join(",");
        id = id.replace(":", "_");
        rem('imv' + id);
    }   
    /*
    ������� �������� �������������� �������� � ���� ������
    (������������ � setIMake() )
    */
    function getIMakeIds() {
        if (document.IMakeArray.length > 0) {
            var s = "ids=" + document.IMakeString;
            return s; 
        }
        return ''; 
    }
    /*
     ������� ���������� ������ ������� ���������� ���� �� �������  � � �������� �������� �� ������
    */
    function setIMake() {
        var uid = parseInt($('user_db_id').value);
        var ids = getIMakeIds();        
        var title = $('imtitle').value;
        var text  = $('imtext').value;
        if (uid && ids && title && text) {
            var req = new Request.JSON(
                {
                    url: window.location.href,
                    onSuccess: onIMakeSuccess,
                    onFailure: onIMakeFail
                }
            );
        var action = "setim";        
        var data = "u_token_key=" + _TOKEN_KEY + "&action=" + action + "&uid=" + uid +  "&" + ids + "&title=" + title + "&text=" + text;
        /*$('setIMakeButton').disabled = true;
        ComboboxManager.getInput("profs3").setDisabled(1);
        ComboboxManager.getInput("user").setDisabled(1);/**/
        req.post(data);
        }else {
            if (!uid) {
                alert("���������� ������� ������������ �� ������");
                return;
            }
            if (!ids) {
                alert("���������� ������� ������� ��������");
                return;
            }
            if (!title) {
                alert("���������� ��������� ���������");
                return;
            }
            if (!text) {
                alert("���������� ��������� ��������");
                return;
            }
        }
    }
    /*
     ��������� ���������� ������  
    */
    function onIMakeSuccess(data) {
        $('setFpButton').disabled = false;
        /*ComboboxManager.getInput("profs3").setDisabled(0);
        ComboboxManager.getInput("user").setDisabled(0);/**/
        if (data.status != "ok") alert(data.msg);
         else alert("�������");
    }
    /*
    ��������� ������ ��������� ������
    */
    function onIMakeFail(){}
</script>
<style type="text/css">
 .input_group {padding-top:25px;padding-bottom:25px;}
</style>
</head>
<body>
<h3>����� �������������</h3>
<div>������� ������� ��� ������������ ��� ������ �������������</div>
<div class="b-combo">
    <div class="b-combo__input  b-combo__input_resize b-combo__input_dropdown b-combo__input_width_140 b-combo__input_max-width_250  b-combo__input_arrow-user_yes b_combo__input_request_id_getuserlistold">
        <input id="user" class="b-combo__input-text"  type="text" size="80" value="" onchange="onSelectUser()"/>
        <label class="b-combo__label" for="user"></label>
        <span class="b-combo__arrow-user"></span>
    </div>
</div>

<div class="input_group">
<hr/>
    <input type="button" value="������� ���" onclick="showBlock('proBlock')" style="float:left;margin:5px 10px" />
    <input type="button" value="������� ������� �� �������" onclick="showBlock('answersBlock')" style="float:left;margin:5px 10px" />
    <input type="button" value="������� ���� � ��������" onclick="showBlock('fpBlock')" style="float:left;margin:5px 10px" />
    <input type="button" value='������� ����� �� "��������"' onclick="showBlock('rbBlock')" style="float:left;margin:5px 10px" />
    <input type="button" value='�������� � ��������' onclick="showBlock('upfpBlock')" style="float:left;margin:5px 10px" />
    <!-- input type="button" value='���������� � ������� "������"' onclick="showBlock('')" style="float:left;margin:5px 10px" /-->
    <!-- input type="button" value="" onclick="showBlock('')" style="float:left;margin:5px 10px" /-->
</div>
<hr/>
<!-- // -->
<div style="padding-top:25px;display:none" id="proBlock" class="fieldGroup">
<fieldset>
<legend>������� ���</legend>

<h3>�������� ���� ������ �������� ���</h3>
<div class="b-combo">
    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_140 b-combo__input_max-width_140 use_past_date date_format_use_text no_set_date_on_load">
        <input id="date_from_pro" class="b-combo__input-text" type="text" size="80" />
        <label class="b-combo__label" for="date_from_pro"></label>
        <span class="b-combo__arrow-date"></span>
    </div>
</div>

<h4>������ �������� �������� PRO </h4>
<div class="b-combo">
    <div class="b-combo__input b-combo__input_multi_dropdown show_all_records b-combo__input_width_150 b-combo__input_resize  b-combo__input_max-width_450 b-combo__input_arrow_yes  b-combo__input_on_load_request_id_get_pro_types">
            <input id="pro_type" class="b-combo__input-text" type="text" size="80" readonly="readonly" onchange="processSelectedValue()"/>        
            <span class="b-combo__arrow"></span>
    </div>
</div>
<div id="pro_week_purchase" class="input_group" style="display:none">
    <div style="float:left; padding-right:10px">������� ���������� ������ </div>
   <input type="button" id="pro_week_dec" name="pro_week_dec" value="-" style="float:left; margin-right:10px" onclick="decProWeek()"/>
   <input id="countWeek"  type="text" size="80" readonly="readonly" value="1" style="float:left; width:20px !important; margin-right:10px"/>
    <input type="button" id="pro_week_inc" name="pro_week_inc" value="+" onclick="incProWeek()"/>
</div>
<div id="pro_block_message" style="padding-top:25px"></div>
<div style="float:left; padding-left:500px; padding-top:25px">
    <input type="button" onclick="setPro()" id="setProButton" value="�������� � ���������� ������������"/>
</div>
</fieldset>
</div>
<!-- // -->
<script type="text/javaScript">
var amountAnswers = {
        1:"���� ����� �� 1 FM",
        5:"���� ������� �� 4 FM",
        10:"������ ������� �� 7 FM"
}
</script>
<div style="padding-top:25px;display:none" id="answersBlock" class="fieldGroup">
<fieldset>
<legend>������� ������� �� ������� ��� �����������</legend>
<h4>�������� ���������� �������</h4>
<div class="b-combo">
    <div class="b-combo__input b-combo__input_multi_dropdown show_all_records b-combo__input_width_150 b-combo__input_resize  b-combo__input_max-width_450 b-combo__input_arrow_yes  b-combo__input_init_amountAnswers">
            <input id="amountAnswers" class="b-combo__input-text" type="text" size="80" readonly="readonly" />
            <span class="b-combo__arrow"></span>
    </div>
</div>
<div id="answer_block_message" style="padding-top:25px"></div>
<div style="float:left; padding-left:500px; padding-top:25px">
    <input type="button" onclick="setAnswers()" id="setAnswersButton" value="�������� ������ ��� ���������� ������������"/>
</div>
</fieldset>
</div>
<!-- // -->
<div style="padding-top:25px;display:none" id="fpBlock" class="fieldGroup">
<fieldset>
<legend>���������� �� ������� � � ��������</legend>

<h3>�������� ���� ������ ���������� �� ������� ��� � ��������</h3>
<div class="b-combo">
    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_140 b-combo__input_max-width_140 use_past_date date_format_use_text">
        <input id="date_from_fpc" class="b-combo__input-text" type="text" size="80" />
        <label class="b-combo__label" for="date_from_pro"></label>
        <span class="b-combo__arrow-date"></span>
    </div>
</div>

<h3>������� �������� </h3>
<div class="b-combo">
    <div class="b-combo__input b-combo__input_multi_dropdown show_all_records b-combo__input_width_150 b-combo__input_resize  b-combo__input_max-width_450 b-combo__input_arrow_yes  b-combo__input_on_click_request_id_getprofessions b-combo__input_on_load_request_id_getprofgroups">
        <input id="profs" class="b-combo__input-text"  type="text" size="80" />
        <span class="b-combo__arrow"></span>
    </div>
</div><input type="checkbox" id="fpmainpage"/><label for="fpmainpage">���������� �� �������</label> <a id="plink-1" href="javascript:prolong(-1)" style="color:#980000">������������� ����.</a><br>
<input type="checkbox" id="fpcatalog"/><label for="fpcatalog">���������� � ������� �������� "��� ����������"</label> <a id="plink0" href="javascript:prolong(0)" style="color:#980000">������������� ����.</a><br>
<input type="button" value="�������� �������������" onclick="addProfession()"/>
<div class="input_group" id="selectedProfessions">
<div id="selProfIds"></div>
<div id="selProfView"></div>
<div id="selProfProlong"></div>
</div>
<div class="input_group" >
    <div style="float:left; padding-right:10px">������� ���������� ������ </div>
   <input type="button" id="fp_week_dec" name="fp_week_dec" value="-" style="float:left; margin-right:10px" onclick="decFpWeek()"/>
   <input id="countFpWeek"  type="text" size="80" readonly="readonly" value="1" style="float:left; width:20px !important; margin-right:10px"/>
   <input type="button" id="fp_week_inc" name="pro_week_inc" value="+" onclick="incFpWeek()"/>
</div>
<div style="float:left; padding-left:500px; padding-top:25px">
    <input type="button" onclick="setFp()" id="setFpButton" value="�������� � ���������� ������������"/>
</div>
</fieldset>
</div>

<!-- // -->
<div style="padding-top:25px;display:none" id="rbBlock" class="fieldGroup">
<fieldset>
<legend>���������� ������ ("� ��������"?) �� ������� � � ��������</legend>
<h3>�������� ��������</h3>
<input type="radio" name="rbcatalog" id="rbmain" checked="checked" onclick="selectRoundaboutPage('main')"/><label for="rbmain">���������� ������ �� ������� ��������</label>
<input type="radio" name="rbcatalog" id="rbcatalog" onclick="selectRoundaboutPage('catalog')"/><label for="rbcatalog">���������� ������ �� �������� �������� </label>
<div id="rb_block_message" style="padding-top:25px"></div>
<div style="float:left; padding-left:500px; padding-top:25px">
    <input type="button" onclick="setRb()" id="setRbButton" value="�������� � ���������� ������������"/>
</div>
</fieldset>
</div>

<!-- // -->
<div style="padding-top:25px;display:none" id="upfpBlock" class="fieldGroup">
<fieldset>
<legend>������� ������� �� ������� � � ��������</legend>

<h3>������� �������� � ������� �������� ������������</h3>
<div class="b-combo">
    <div class="b-combo__input b-combo__input_multi_dropdown show_all_records b-combo__input_width_150 b-combo__input_resize  b-combo__input_max-width_450 b-combo__input_arrow_yes override_value_id_0_0_���+�������">
        <input id="profs2" class="b-combo__input-text"  type="text" size="80" />
        <span class="b-combo__arrow"></span>
    </div>
</div>
<div id="upfpmainpagegroup" style="display:none"><input type="checkbox" id="upfpmainpage"/><label for="upfpmainpage">���������� �� �������</label></div><br>
<div id="upfpcataloggroup" style="display:none"><input type="checkbox" id="upfpcatalog"/><label for="upfpcatalog">���������� � ������� �������� "��� ����������"</label></div><br>
<input type="button" value="�������� �������������" onclick="addUpProfession()"/>
<div class="input_group" id="selectedUpProfessions">
<div id="selUpProfIds"></div>
<div id="selUpProfView"></div>
<div id="selUpProfProlong"></div>
</div>
<div style="float:left; padding-left:500px; padding-top:25px">
    <input type="button" onclick="setUpFp()" id="setUpFpButton" value="������� � ���������� ������������"/>
</div>
</fieldset>
</div>

<!-- // -->
<div style="padding-top:25px;" id="imakeBlock" class="fieldGroup">
<fieldset>
<legend>���������� ����������� � ������� "������"</legend>

<h3>�������� �������������</h3>
<div class="b-combo">
    <div class="b-combo__input b-combo__input_multi_dropdown show_all_records b-combo__input_width_150 b-combo__input_resize  b-combo__input_max-width_450 b-combo__input_arrow_yes override_value_id_0_0_���+������� b-combo__input_on_click_request_id_getprofessions b-combo__input_on_load_request_id_getprofgroups">
        <input id="profs3" class="b-combo__input-text"  type="text" size="80" />
        <span class="b-combo__arrow"></span>
    </div>
</div>
<input type="button" value="�������� �������������" onclick="addIMakeProfession()"/>
<div class="input_group" id="selectedIMakeProfessions">
<div id="selIMakeGroupIds"></div>
<div id="selIMakeProfIds"></div>
<div id="selIMakeProfView"></div>
</div>
<div class="input_group">
<label for="imtitle">���������</label> &nbsp;<input type="text" id="imtitle"  style="width:72%"/><br/>
<label for="imtext">��������</label><br/><textarea id="imtext" rows="15" style="width:80%"></textarea>
</div>
<div style="float:left; padding-left:500px; padding-top:25px">
    <input type="button" onclick="setIMake()" id="setIMakeButton" value="�������� � ���������� ������������"/>
</div>

</body>
</html>