<?php
/** 
 * ���������� ����� � ��������� ��������� ������� 
 */ 
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php"); 

class verify
{
    
    const VERIFY_DATE_START = 'April 10, 2013 00:00:00';
    
    /**
     * ��������� ������������ �� ��������
     */
    public function addSubscribeUser($uid = null) {
        global $DB;
        if(!$uid) $uid = $_SESSION['uid'];
        $memBuff = new memBuff();
        $memBuff->delete("verify_count"); // ������� ���
        return $DB->insert("verify", array("uid" => $uid));
    }
    
    /**
     * ���������� ������������� �� ��������
     * @global type $DB
     * @return type
     */
    public function getCountSubscribe() {
        global $DB;
        $memBuff = new memBuff();
        $count   = $memBuff->get('verify_count');
        if( !$count ) {
            $count = $DB->val("SELECT COUNT(*) as cnt FROM verify");
            $memBuff->add('verify_count', $count, 600);
        }
        return $count;
    }
    
    /**
     * �������� ������������ ��� ���
     * 
     * @global type $DB
     * @param type $uid �� ������������
     * @return type
     */
    public function isSubscribeUser($uid = null) {
        global $DB;
        if(!$uid) $uid = $_SESSION['uid'];
        
        return $DB->val("SELECT id FROM verify WHERE uid = ?", $_SESSION['uid']);
    }
    
    public static function converNumbersTemplate($num) {
        return preg_replace("/(\d{1})/", '<span class="b-promo__digital b-promo__digital_margright_3">$1</span>', $num);
    } 
}
