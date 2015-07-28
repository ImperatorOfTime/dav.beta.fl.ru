<?
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php';

/**
 * ����� ��� ������ � ��������� ���������� � ������ ����������
 */
class spam {

    const MASSSEND_BIND_QUEUE_SIZE = 5000;
    
    /**
     * ������ ������ user � ������������ ���������� ������ ��������
     * 
     * @var users
     */
    protected $_sender;
    /**
     * ������ ������ DB � ������������ � �� master
     * 
     * @var DB
     */
    protected $_dbMaster;
    /**
     * ������ ������ DB � ������������ � �� plproxy
     * 
     * @var DB
     */
    protected $_dbProxy;
    
    
    /**
     * ����������� ������
     * 
     * @param  string  $sender  ����� ������ ��������
     */
    public function __construct($sender='admin') {
        $this->_sender = new users;
        $this->_sender->GetUser($sender);
        $this->_dbMaster = new DB('master');
        $this->_dbProxy  = new DB('plproxy');
    }

    
    /**
     * ������ �������� �� sql �������.
     * � SELECT ����� sql ������� ����������� ������ ���� ���� uid
     * 
     * @param  string   $sql         SQL ������ ������������ ������ �����������
     * @param  string   $message     ���������
     * @param  string   $mailFunc    ������� �������� ��������� �� ����� (�� ������ pmail)
     * @param  integer  $recOnStep   ���������� ������������� ���������� � ���� �������
     * @return integer               id ��������� �� ������� messages
     */
    protected function _masssendSql($sql, $message, $mailFunc, $recOnStep = self::MASSSEND_BIND_QUEUE_SIZE) {
        $msgid = $this->_dbProxy->val("SELECT masssend(?, ?, ?a, ?)", $this->_sender->uid, $message, array(), $mailFunc);
        $res   = $this->_dbMaster->query($sql);
        $i = 0;
        $uids = array();
        while ( $row = pg_fetch_assoc($res) ) {
            $uids[] = $row['uid'];
            if ( ++$i % $recOnStep == 0 ) {
                $this->_dbProxy->query("SELECT masssend_bind(?, ?, ?a)", $msgid, $this->_sender->uid, $uids);
                $uids = array();
                $i = 0;
            }
        }
        if ( $i ) {
            $this->_dbProxy->query("SELECT masssend_bind(?, ?, ?a)", $msgid, $this->_sender->uid, $uids);
            $uids = array();
        }
        $this->_dbProxy->query("SELECT masssend_commit(?, ?)", $msgid, $this->_sender->uid);
        return $msgid; 
    }

    
    /**
     * ��������� ������ (�������� ������ � �������� ��������, ������� ����� ������������
     * ������ � ������ ����������)
     * 
     * @param  string  $href   ������
     * @param  string  $title  ����� ������
     * @return string          ������ ��� ������� � �����
     */
    protected function _link($href = '', $text = '') {
        $h = preg_replace("/https?\:\/\//", "", $GLOBALS['host']);
        return 'http:/{'.$text.'}/'.$h.$href;
    }
    
    
    /**
     * ������ ��� ��������� (������� 1)
     * 
     * @param  string  $text   ����� ���������
     * @return string          ��������� ������������ �� �������
     */
    protected function _template1($text) {
        return "
������������!
        
{$text}

�� ���� ����������� �������� �� ������ ���������� � ���� " . $this->_link('https://feedback.fl.ru/', '������ ���������') . ".
�� ������ ��������� ����������� ��". $this->_link('/users/%USER_LOGIN%/setup/mailer/', '�������� ������������/��������') . " ������ ��������.

�������� ������,
������� " . $this->_link('', 'Free-lance.ru') . "
        ";
    }
    
    
    /**
     * @todo: ��������� � hourly, ��� ������������� ��������� �����
     * 
     * �������� PRO �������������, ������� ������������������ ����� 30 ���� �����
     * 
     * @return integer  id ��������� ��� 0, ���� ������
     */
    public function proEmpRegLess30() {
        $message = $this->_template1(
"�� ����� ���� ��� �� Free-lance.ru!

�� ����� ����� �������� ����� 1 ���. ������������.  � �� �����, ������� ������� ����� ��������, ������������ ��������� � ������� �� ����� ����� �� �����������, ����� ������� ���������� ����������� �� ������ ���������� ����������. ������ ������� �� ���������� ��� ���� ������. ���� �� ������ ���������� ���� �����, �������������� ������� ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_registration_30", "&laquo;������ �����������&raquo;").".

���� ����������������� ��������� �� ������� ��������� ������ ��� ��� ����� ������ ������������. ��� ����� ���� ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_registration_30", "��������� ������")." � ��� ��������� �� ������� �� ��� � ���������� �����."
        );
        //PRO
        $sql = $this->_dbMaster->parse("
            SELECT 
                uid 
            FROM 
                employer u
            LEFT JOIN
                orders o ON o.from_id = u.uid
            WHERE
                is_banned = '0' AND substr(u.subscr::text,8,1) = '1' AND uid <> 103
                AND reg_date >= NOW()::date - interval '1 month'
            AND o.payed=true AND o.from_date<=now() AND o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval >= now() AND o.active='true'
            AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)   
        ", $this->_sender->uid);
        $res1 = $this->_masssendSql($sql, $message, "empRegLess30");
        return $res1;
    }

    
    /**
     * @todo: ��������� � hourly, ��� ������������� ��������� �����
     * 
     * �������� �������������, ������� ������������������ ����� 30 ���� �����
     * 
     * @return integer  id ��������� ��� 0, ���� ������
     */
    public function noProEmpRegLess30() {
        //NOT PRO
        $message = "�� ����� ���� ��� �� Free-lance.ru!

�� ����� ����� �������� ����� 1 ���. ������������.  � �� �����, ������� ������� ����� ��������, ������������ ��������� � ������� �� ����� ��������� �����������, ����� ������� ���������� ����������� �� ������ ���������� ����������. ������ ������� �� ���������� ��� ���� ������. ���� �� ������ ���������� ���� �����, �������������� ������� ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_registration_30", "&laquo;������ �����������&raquo;").". 

���� ����������������� ��������� �� ������� ��������� ������ ��� ��� ����� ������ ������������. ��� ����� ���� ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_registration_30", "��������� ������")." � ��� ��������� �� ������� �� ��� � ���������� �����.

���� �� �� ������ ����� ����������� ��� ���������� ����� �������� ��������������, ����������� ��� ���������� ����������. �������� ��������: ������ �������� ���� ����������� ����� ������ ��������� ".$this->_link("/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_registration_30", "�������� PRO").". ������ ����������� ������ ������ �������� (e-mail, ICQ, Skype) �������������, �� ������ ������������ �������� �� ��� ������� ������ �����, ������� ������� �������������� ���������� � ���� � �������� ���������� � ������ ���� �������� �������������.";
        $sql = $this->_dbMaster->parse("
            SELECT 
                uid 
            FROM 
                employer u
            LEFT JOIN
                orders o ON o.from_id = u.uid
            WHERE
                is_banned = '0' AND substr(u.subscr::text,8,1) = '1' AND uid <> ?i
                AND reg_date >= NOW()::date - interval '1 month'
            AND                 
                (o.payed IS NULL                
                 OR o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval <= now()
                 OR o.active='false'
                 OR (NOW() <= freeze_from_time::date AND NOW() > freeze_to_time)
                )   
        ", $this->_sender->uid);
        $res1 = $this->_masssendSql($sql, $message, "empRegLess30");
        return $res1;
    }
    
    /**
     * �������� �����������, ������� ������������������ �� ����� ����� 30 ���� ����� � �� ������ ������� ���
     * 
     * @return integer  id ��������� ��� 0, ���� ������
     */
    public function frlNotBuyPro() {
        $message = $this->_template1(
'�� ����� ���� ��� �� Free-lance.ru!

����� ����� �������� ���������� ������, ����� ���������� ����� ����������� � ����� �������� ��� �������������. ��� ����� ���������� ��� ' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_registration_30', '�������������� ������� PRO') . '.

�� ���������� Free-lance.ru, �� ������� ��������� PRO-�������� ���������� �� 2 ������� �� �������������. � ������� ������� �� ������ &laquo;������ ��� PRO&raquo; ���������� 25&nbsp;000 ������.
��� ������, ���, ' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_registration_30', '����� PRO') . ', �� ������ ������������ ������.

� ��� � ������ ������������:<ul><li>� PRO �������������� ���������� ������� �� �������, � �� ����� ��� � ������������� � ��������� ��������� ������� ������ 3 ���������� ������ � �����;</li><li>������� �� ������� �� PRO ������������� ���� �������� ��������� �����������, ��� ���������� �������� �������������.</li><li>���������� � PRO ������������� ���� ������ ������������� � �������� �����������.</li></ul>�������������� �����: ������������ � ����� �������� ����� ������ ����������� � PRO � �������� ��������������� ������������. ������ �������� ������ ������ ��� ���� &ndash; ��� ����� ���� ������� �� ������������� ����������� ����� �������� ������ ����������������� ��������.�

' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_registration_30', '������� ������ � ��PRO�����!')
        );
        $sql = "
            SELECT 
                u.uid 
            FROM 
                freelancer u
            LEFT JOIN 
                orders o ON o.from_id = u.uid AND o.ordered = '1' AND o.payed = 't' AND 
                    o.tarif IN ( 15, 16, 28, 35, 42, 47, 48, 49, 50, 51, 52, 76 ) 
            WHERE 
                o.id is NULL AND u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1'
                    AND u.reg_date >= NOW()::date - interval '1 month' AND u.reg_date <= NOW()::date
        ";
        return $this->_masssendSql($sql, $message, __FUNCTION__);
    }
    
    
    /**
     * �������� �����������, ������� ������ �������� ��� � �� ������ ������� ��� � ������� ������
     * 
     * @return integer  id ��������� ��� 0, ���� ������
     */
    public function frlBuyTestPro() {
        $message = $this->_template1(
'�� �������� ��������, ��� �� ����������� �������� PRO. �����, ������ ����� ' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_PRO', '���������� �����������') . '<nobr>?</nobr>

��������� ���������� � ������ PRO:<ol><li>�� ������� ��������� PRO-�������� ���������� �� 2 ������� �� �������������. � ������� ������� �� ������ &laquo;������ ��� PRO&raquo; ���������� 25�000 ������. 
��� ������, ���, ����� PRO, �� ������ ������������ ������.</li><li>�������������� ���������� ������� �� ������� (���������� � ��������� ��������� ����� ��������� �������� ����� �� 3 ������� � �����) � ����������� ���������� ������������ ���� ������ ������.</li><li>���������� � ���� PRO &ndash; ����, ��� ��������� ������������ &ndash; ��� ������� �� ������ ������ � � �������� �����������.</li><li>����� ����� ���� ������������� (� ������� �� ����� � ��� ����������� ���������� ��������).</li></ol>�������������� �����: ������������ � ����� �������� ����� ������ ����������� � PRO � �������� ��������������� ������������. ������ �������� ������ ������ ��� ���� &ndash; ��� ����� ���� ������� �� ������������� ����������� ����� �������� ������ ����������������� ��������.�

������ ����� �������� ��� ���� ������������� � ���������� ������� PRO ����� '. $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_PRO', '�����' ) .'.'
        );
        $sql = "
            SELECT 
                u.uid 
            FROM 
                freelancer u
            INNER JOIN 
                orders ot ON ot.from_id = u.uid AND ot.ordered = '1' AND ot.payed = 't' AND ot.tarif = 47 
            LEFT JOIN 
                orders op ON op.from_id = u.uid AND op.ordered = '1' AND op.payed = 't' 
                    AND op.tarif IN ( 15, 16, 28, 35, 42, 48, 49, 50, 51, 52, 76 ) 
            WHERE 
                op.id is NULL AND u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1'
                    AND ot.from_date + ot.to_date + COALESCE(ot.freeze_to, '0')::interval >= NOW() - interval '1 month' 
                    AND ot.from_date + ot.to_date + COALESCE(ot.freeze_to, '0')::interval < NOW()
        ";
        return $this->_masssendSql($sql, $message, __FUNCTION__);
    }
    

    /**
     * �������� �����������, ������� ������ �������� ��� � ����� ���� ������ ������� ������ �������
     * 
     * @return integer  id ��������� ��� 0, ���� ������
     */
    public function frlBuyProOnce() {
        $message = $this->_template1(
	'�� �������� ��������, ��� ����� ������� ' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_repeat_PRO', '�������� PRO') . ' �� �� �������� ���� ��� ��������. 

������ � ���, �� ���������� �� ������� ��������� PRO-�������� ���������� �� 2 ������� �� �������������, � ������� ������ ������� � �������� &laquo;������ ��� PRO&raquo; ���������� ����� 25�000  ������. 
� ��� ������, ��� PRO ����� ���� �������.

' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_repeat_PRO', '������� PRO') . ' ���� �������������� ����������� ����� ����������:<ul><li>�������� ������ � ����� ���������: ���� ���������� ���������� ����� ������ ��� ������������ � ����� �� ��� ������ ������ ��������� � ���� � ������ �������������;</li><li>�������������� ���������� ������� �� ������� (���������� � ��������� ��������� ����� ��������� �������� ����� �� 3 ������� � �����) � ����������� �������� ��������� ���� ������ ������, ��������� �� � ������ ����������� � �������;</li><li>���������� � ���� PRO � ���� ����������� � ��������� ��������� � ��� ������� �� ������ ������ � � �������� �����������;</li><li>���� ������������� (� ������� �� ����� � ��� ����������� ���������� ��������).</li></ul>�������������� �����: ������������ ����� ����������� � PRO � �������� ��������������� ������������ � ����� ��������. ������ �������� ������ ������ ��� ���� � ��� ����� ���� ������� �� ������������� ����������� ����� �������� ����������������� ��������.

������ ����� �������� ��� ���� ������������� � ���������� ������� PRO ' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_repeat_PRO', '����� �����')
        );
        $sql = "
            SELECT 
                u.uid 
            FROM 
                freelancer u
            INNER JOIN 
                orders ot ON ot.from_id = u.uid AND ot.ordered = '1' AND ot.payed = 't' AND ot.tarif = 47 
            INNER JOIN (
                SELECT 
                    from_id, COUNT(tarif) AS pro_cnt, MAX(from_date + to_date + COALESCE(freeze_to, '0')::interval) AS max_date 
                FROM 
                    orders 
                WHERE 
                    ordered = '1' AND payed = 't' 
                        AND tarif IN ( 15, 16, 28, 35, 42, 48, 49, 50, 51, 52, 76 ) 
                GROUP BY from_id 
            ) AS op ON op.from_id = u.uid 
            WHERE 
                u.is_banned = '0' AND op.pro_cnt = 1
                    AND substr(u.subscr::text,8,1) = '1'
                    AND op.max_date >= NOW() - interval '1 month' AND op.max_date < NOW()
        ";
        return $this->_masssendSql($sql, $message, __FUNCTION__);
    }
    
    
    /**
     * �������� �����������, � ������� ����� 2 ������ ������������� ��� �� 6 ��� 12 �������.
     * 
     * @return integer  id ��������� ��� 0, ���� ������
     */
    public function frlEndingPro() {
        $message = $this->_template1(
'����� ���������, ��� ����� 2 ������ ������������� �������� ������ ' . $this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_ending_PRO', '�������� PRO') .'.

������ � ���������� ����� �������� ����������������� �������� �������� ����� ������� � ��������� ���� ���: <ul>
<li>����������� �������� ��������� � ����������: ���� �������� �� ����� ����� �������������</li>
<li>�������������� ���������� ������� �� �������</li>
<li>����������� �������� �� ������� � �������� &laquo;������ ��� PRO&raquo;</li>
<li>���������� � ���� PRO � �������� ����������� � � ������ �������������� �� �������������� �������</li>
<li>������� �������, ������� �������������� �� ������ �������</li>
<li>���� � ��������� ����������� ��������� � ������, ����������� ��������������� ������������ ���� ������ ������ ��� ������ �� �������������� ������</li>
<li>�������������� �������������</li>
<li>����������� ��������� ���� ����������, ���������� ������� �� ����� � ������ ������.</li>
</ul>��� ���� ����� � ������ ������������ �������������� ����������������� ��������, �� ������ '.$this->_link('/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=freelancer_ending_PRO', '�������� ���� �������� �������� PRO').'.'
        );
        //�������� ���, � ���� PRO ������������� ����� ��� ������
        $sql = "
            SELECT
                    uid
                FROM
                    orders o
                LEFT JOIN 
                    freelancer u ON o.from_id = u.uid 
                WHERE 
                    u.is_banned = '0' 
                    AND u.is_pro='true' AND u.is_pro_auto_prolong = 'f' 
                        AND substr(u.subscr::text,8,1) = '1'
                        AND o.payed='true' AND o.active='true'
                        AND o.tarif IN (42, 48, 49, 50, 51, 52, 66, 67, 68, 76 )
                        AND o.from_date + o.to_date + COALESCE(o.freeze_to, '0')::interval > (NOW()+'2 weeks')
                        AND o.from_date + o.to_date + COALESCE(o.freeze_to, '0')::interval < (NOW()+'2 weeks 1 day')
                GROUP BY 
                    uid 
        ";
        return $this->_masssendSql($sql, $message, __FUNCTION__);
    }
    
    
    /**
     * @todo: ��������� � hourly, ��� ������������� ��������� �����
     * 
     * �������� PRO ������������� �������������� ������� ������ ��� ������� � ������� 30 ����
     * 
     * @return integer  id ��������� ��� 0, ���� ������
     */
    public function empProPubPrj30Days() {
        $message = $this->_template1(
        "�� �������� �������� �� ��, ��� �� ������� ������������ ������. ���� �� ��� ������� ������� � ������ ������ � ���������� �����������, �������������� �������� ".$this->_link("/promo/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=project_30", "&laquo;���������� ������&raquo;").", ������������� ������������ � ���������� ������ �� ������� �����������.

���� �� ����� �� �������������� �� ������ �� �������, ����������� ��� ��������������� ".$this->_link("/masssending/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=project_30", "��������� �� �������� �����������").". �������� ��������� ����� ���������� ���� ������������� ������������ � ���������� � ��� ��������.

����� ����, �� ������ ���������� � ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=project_30", "����� ����������").", ������� ������� �� ���� ��� ����������� �� ������� ��������� �����������."
);
        $sql = "
            SELECT 
                DISTINCT(u.uid)
            FROM 
                employer u 
            INNER JOIN 
                projects p ON p.user_id = u.uid
            LEFT JOIN
                orders o ON o.from_id = u.uid 
            WHERE 
                p.billing_id IS NOT NULL AND u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1'
                    AND p.create_date >= NOW() - interval '1 month' AND p.create_date < NOW()
            AND o.payed=true AND o.from_date<=now() AND o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval >= now() AND o.active='true'
            AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)
        ";
        $res1 = $this->_masssendSql($sql, $message, "empPubPrj30Days");
        return $res1; 
    }
    
    
    /**
     * @todo: ��������� � hourly, ��� ������������� ��������� �����
     * 
     * �������� �� PRO ������������� �������������� ������� ������ ��� ������� � ������� 30 ����
     * 
     * @return integer  id ��������� ��� 0, ���� ������
     */
    public function empNoProPubPrj30Days() {        
        $message = $this->_template1(
        "�� �������� �������� �� ��, ��� �� ������� ������������ ������. ���� �� ��� ������� ������� � ������ ������ � ���������� �����������, �������������� �������� ".$this->_link("/promo/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=project_30", "����������� ������").", ������������� ������������ � ���������� ������ �� ������� �����������.
���� �� ����� �� �������������� �� ������ �� �������, ����������� ��� ��������������� ��������� �� �������� �����������. �������� ��������� ����� ���������� ���� ������������� ������������ � ���������� � ��� ��������. ����� ��������� � ������ ��� �������������, ����������� ��� ���������� ����������. �������� ��������: ������ �������� ���� ����������� ����� ������ ��������� ".$this->_link("/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=project_30", "�������� PRO").".

�����  �� ������ ���������� � ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=project_30", "����� ����������").", ������� ������� �� ���� ��� ����������� �� ������� ��������� �����������."
);
        $sql = "
            SELECT 
                DISTINCT(u.uid)
            FROM 
                employer u 
            INNER JOIN 
                projects p ON p.user_id = u.uid
            LEFT JOIN
                orders o ON o.from_id = u.uid 
            WHERE 
                p.billing_id IS NOT NULL AND u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1'
                    AND p.create_date >= NOW() - interval '1 month' AND p.create_date < NOW()
            AND                 
                (o.payed IS NULL                
                 OR o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval <= now()
                 OR o.active='false'
                 OR (NOW() <= freeze_from_time::date AND NOW() > freeze_to_time)
                )
        ";
        $res2 = $this->_masssendSql($sql, $message, "empPubPrj30Days");
        return $res2; 
    }
    
    /**
     * �������� PRO  ������������� �������� �������� � ������� 30 ����
     * 
     * @return integer  id ��������� ��� 0, ���� ������
     */
    public function empProBuyMass30Days() {
        $message = $this->_template1(
"�� �������� �������� �� ��, ��� �� ������������ ������� ".$this->_link("/masssending/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rassilka_30", "&laquo;�������� �� �������� �����������&raquo;").". ���� �� ��� �� ����� ������������, �� ����������� ��� ".$this->_link("/public/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rassilka_30", "������������ ������ ��� �������")." �� Free-lance.ru. 

���� �� ��� ������� ������� � ������ ������ � ���������� �����������, ���������� ���, ��� �� Free-lance.ru ���� ������, �������������� ������ ������������ � ���������� ��������������, � ".$this->_link("/promo/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rassilka_30", "&laquo;���������� ������&raquo;")."."
        );
        //PRO
        $sql = "
            SELECT 
                DISTINCT(u.uid)
            FROM 
                employer u
            INNER JOIN 
                mass_sending p ON p.user_id = u.uid 
            LEFT JOIN
                orders o ON o.from_id = u.uid
            WHERE 
                u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1'
                    AND p.posted_time >= NOW() - interval '1 month' AND p.posted_time < NOW()
            AND o.payed=true AND o.from_date<=now() AND o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval >= now() AND o.active='true'
            AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)
        ";
        $res1 = $this->_masssendSql($sql, $message, "empBuyMass30Days");       
        return $res1;
    }
    
/**
     * �������� �� PRO ������������� �������� �������� � ������� 30 ����
     * 
     * @return integer  id ��������� ��� 0, ���� ������
     */
    public function empNoProBuyMass30Days() {
        $message = $this->_template1(
"�� �������� �������� �� ��, ��� �� ������������ ������� ".$this->_link("/masssending/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rassilka_30", "&laquo;�������� �� �������� �����������&raquo;").". ���� �� ��� �� ����� ������������, �� ����������� ��� ".$this->_link("/public/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rassilka_30", "������������ ������ ��� �������")." �� Free-lance.ru. 

���� �� ��� ������� ������� � ������ ������ � ���������� �����������, ���������� ���, ��� �� Free-lance.ru ���� ������, �������������� ������ ������������ � ���������� ��������������, � ".$this->_link("/promo/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rassilka_30", "&laquo;���������� ������&raquo;").".

����� ��������� � ������������ ��������, ����������� ��� ���������� ����������. �������� ��������: ������ �������� ���� ����������� ����� ������ ��������� ".$this->_link("/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=rassilka_30", "�������� PRO").". ����� ���������������� ������� ���� � ������ ������������ ����� �����������: ������ �� ��� ������� ������, ���������� � ������ ���� ��������, ���������� ��������� ������ ������� � ����� ����� � ������ ������.
"
        );
        //no PRO
        $sql = "
            SELECT 
                DISTINCT(u.uid)
            FROM 
                employer u
            INNER JOIN 
                mass_sending p ON p.user_id = u.uid 
            LEFT JOIN
                orders o ON o.from_id = u.uid
            WHERE 
                u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1'
                    AND p.posted_time >= NOW() - interval '1 month' AND p.posted_time < NOW()
            AND                 
                (o.payed IS NULL
                 OR o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval <= now()
                 OR o.active='false'
                 OR (NOW() <= freeze_from_time::date AND NOW() > freeze_to_time)
                )
        ";
        $res2 = $this->_masssendSql($sql, $message, "empBuyMass30Days");
        return $res2;
    }
    
    
    /**
     * @todo: ��������� � hourly, ��� ������������� ��������� �����
     * 
     * �������� ������������� �������� �� 30 ����, �� �� ������������� ��������.
     * 
     * @return integer  id ��������� ��� 0, ���� ������
     */
    public function empNoProNotPubPrj() {
        //������  �� ��� �������������
        $sql = "SELECT 
                DISTINCT(u.uid)
            FROM 
                employer u
            LEFT JOIN 
                projects p ON p.user_id = u.uid
            LEFT JOIN
                orders o ON o.from_id = u.uid
            WHERE 
                p.id is NULL AND u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1' AND u.is_active = true
                AND                 
                (o.payed IS NULL                
                 OR o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval <= now()
                 OR o.active='false'
                 OR (NOW() <= freeze_from_time::date AND NOW() > freeze_to_time)
                )
            ";
        $messageNoPro = $this->_template1(
"�� ���������������� �� Free-lance.ru, ������ ��� �� ���������� �� ������ �������. � ���������, ������ ���������� �� �������� ������������� ��� �������������� ��������. ����� ��������� ������� � ������������, ��� ����� ������ ".$this->_link("/public/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_active_30", "����������� ����������� ������� ��� ��������").".
���� �� ����� ����������� � �������� ����������� � ������ ��������� � ��� ��������, ����������� ��� ���������� ����������. �������� ��������: ������ �������� ���� ����������� ����� ������ ��������� ".$this->_link("/payed/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_active_30", "�������� PRO").". 
�� ����������� ������ ��������� ".$this->_link("/promo/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_active_30", "&laquo;���������� ������&raquo;")."  � ��� �� ������� ������������ ����� ����������� � ������ ������� � ���, ��� ��� ����� ����� �������� ����� � ���� � � ������������ � ����������� ��������. ��� �������������� ����� ����������� ������ ������� ����������� ������������� ������ ����� ����, ��� �� ������� ��������� ������.
���� � ��� ��� ������� �� ����� ���������� ��� ���������� ����� �������, �� ������ ��������� ������������ ��� ������ ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_active_30", "����� ����������").".
"        );
        $res2 = $this->_masssendSql($sql, $messageNoPro, "empNotPubPrj");
        return $res2;
    }


    /**
     * @todo: ��������� � hourly, ��� ������������� ��������� �����
     * 
     * �������� PRO������������� �������� �� 30 ����, �� �� ������������� ��������.
     * 
     * @return integer  id ��������� ��� 0, ���� ������
     */
    public function empProNotPubPrj() {
        $messagePro = $this->_template1("�� ���������������� �� Free-lance.ru, ������ ��� �� ���������� �� ������ �������. � ���������, ������ ���������� �� �������� ������������� ��� �������������� ��������. ����� ��������� ������� � ������������, ��� ����� ������ ".$this->_link("/public/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_active_30", "����������� ����������� ������� ��� ��������").".
�� ����������� ������ ��������� ".$this->_link("/promo/" . sbr::NEW_TEMPLATE_SBR . "/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_active_30", "&laquo;���������� ������&raquo;")."  � ��� �� ������� ������������ ����� ����������� � ������ ������� � ���, ��� ��� ����� ����� �������� ����� � ���� � � ������������ � ����������� ��������. ��� �������������� ����� ����������� ������ ������� ����������� ������������� ������ ����� ����, ��� �� ������� ��������� ������.
���� � ��� ��� ������� �� ����� ���������� ��� ���������� ����� �������, �� ������ ��������� ������������ ��� ������ ".$this->_link("/manager/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=client_active_30", "����� ����������").".");
        //������ ��� �����������
        $sql = "
            SELECT 
                u.uid 
            FROM 
                employer u
            LEFT JOIN 
                projects p ON p.user_id = u.uid
            LEFT JOIN
                orders o ON o.from_id = u.uid
            WHERE 
                p.id is NULL AND u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1' AND u.is_active = true
                AND o.payed=true AND o.from_date<=now() AND o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval >= now() AND o.active='true'
            AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)
        ";
        $res1 = $this->_masssendSql($sql, $messagePro, "empNotPubPrj");
        return $res1;
    }

    /**
     * �������� �������������  � ������� �� ����� ���� 35+ �������� FM.
     * 
     * @return integer  id ��������� ��� 0, ���� ������
     */
    public function empBonusFm() {
        $message = $this->_template1(
'�� ������ ������ �� ����� �������� ����� Free-lance.ru ��������� ����� � 1050 ���. �� ������ ��������� �������� ����� �� ������ ����� ����� ���:<ul>
<li>�������� ��������������� �������� ������ �����,</li>
<li>���������� �������� �������,</li>
<li>�������� ������� ������ ����� ��������,</li></ul>
� ����� �� ��� ������ ������� �������� �����.

����� �� ������ �������� �������������� ���������� ' . $this->_link('/help/?q=936&utm_source=newsletter4&utm_medium=rassilka&utm_campaign=bonus_35', '�������� ������') . ', �������� � ������ �� Free-lance.ru. ���������� ���������� � ����������� ������ ����� ����� �� �������� ������ ' . $this->_link('/bill/?utm_source=newsletter4&utm_medium=rassilka&utm_campaign=bonus_35', '������� �����') . '.'
        );
        $sql = "
            SELECT 
                u.uid 
            FROM 
                employer u 
            INNER JOIN 
                account a ON a.uid = u.uid 
            WHERE 
                u.is_banned = '0' AND substr(u.subscr::text,8,1) = '1' AND a.bonus_sum >= 35
        ";
        return $this->_masssendSql($sql, $message, __FUNCTION__);
    }
    
    
}

