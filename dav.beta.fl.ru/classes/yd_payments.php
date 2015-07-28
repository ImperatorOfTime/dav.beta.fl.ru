<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/account.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/log.php');

class yd_payments {
    
    const VERSION = '2.02';
    const AGENT_ID = 159202;
    const AGENT_NAME = '��� "����"';
    const CONTRACT_NUM = '�.11111.03';
    
    // ��� ��������� �������
    const SRC_SBR = 1; // ������ ��� �����
    
    // ���� ��������
    const ACT_PAY       = 1;    // ����������
    const ACT_STATUS    = 1001; // ������ � ��������� ����������
    const ACT_CHECKPAY  = 1002; // �������� ����������� ����������
    const ACT_BALANCE   = 1003; // ������ ������� ������
    
    // ���������� ���������� ��������
    const RES_OK      = 0;  // �����. ��������� ���������. �������� ��������� �������
    const RES_WAIT    = 1;  // � ���������. ������ � �������� ���������
    const RES_FAIL    = 3;  // ����������. ��������� ���������
    const RES_UNKNOWN = 30; // ����������� �������� �� ������� �������
    
    // ��� ������
    const CUR_CD  = 643; // �����

    const REGISTRY_NOENC_SFX = '_vaan.txt';
    const REGISTRY_PATH   = '/var/tmp/';
    const REGISTRY_DIR    = 'yd_payments';
    const REGISTRY_FROM   = 'payments@free-lance.ru';
    const REGISTRY_YDTO   = 'onlinegate@yamoney.ru';
    
    const PGP_SIGN    = 'gpg --clearsign --always-trust --batch --no-secmem-warning --homedir=/var/www/.gnupg';
    const PGP_ENCRIPT = 'gpg -r ACE74CE2 -a --always-trust --sign --pgp6 --homedir=/var/www/.gnupg --encrypt';
    const PGP_CHECK   = 'gpg --verify --batch --no-secmem-warning --homedir=/var/www/.gnupg';
    
    const BALANCE_MEM_KEY = 'yd_payments.balance()';
    
    private $_address = "https://calypso.yamoney.ru/onlinegates/vaan.aspx";

    private $_src = array();
    private $_pmt = array();
    private $_tr = array();
    private $_ptry = 0;
    private $_pdata;
    private $_answer;
    private $_maxAmt = 15000;

    private $_performedAmt      = 0;
    private $_performedAmtFixed = 0;
    
    private $_isPmtLocked = false;
    
    private $_log;


    // ��� ������� ����.
    public $pmt;
    public $tr;

    public $DEBUG;

    static $REGISTRY_VAANTO = array('ey@free-lance.ru', 'kotova@free-lance.ru', 'abbram@mail.ru', 'payments@free-lance.ru');
    
    /**
     * ���� ��������
     * 
     * @var array
     */
    static $act_nm = array (
        self::ACT_PAY       => '����������',
        self::ACT_STATUS    => '������ � ��������� ����������',
        self::ACT_CHECKPAY  => '�������� ����������� ����������',
        self::ACT_BALANCE   => '������ ������� ������'
    );

    /**
     * ������, ������������ ��� ��������� ��������.
     * 
     * @var array
     */
    static $yd_errs = array (
        14 => '������� ������ ������ (CUR_CD).',
        16 => '������� ����� ���� ���������� ������� (DSTACNT_NR). ������ � ����������� ����� ��� ������� ������ �����',
        17 => '������� ������ ����� (TR_AMT).',
        18 => '������� ����� ����� ����������  (TR_NR).',
        20 => '������� ����� ��� �������� (ACT_CD).',
        22 => '����������� ����������� ��������� (����� SIGN).',
        24 => '������ �� ������������� �����.',
        25 => '������� ������� ����� ���������.',
        26 => '�������� � ����� ������� ���������� (TR_NR), �� ������� ����������� ��� �����������.',
        50 => '����������� ������� (SIGN).',
        51 => '������� �� ������������ (������ ������� �� ��������� � ������� �������).',
        53 => '������ �������� ����������� ������� PGP-������.',
        55 => '����� ���� �������� ���������� PGP-����� �� ������.',
        56 => '�������� ������ �������(SIGN). ������� �� ���������� ��� PGP-sign.',
        40 => '���� ������.',
        41 => '���� � ������� ������������. ���������� �� ���� ���������.',
        42 => '����� � ����� ������� �� ����������.',
        43 => '��������� ����������� �� ������������� ����������� �����.',
        44 => '��������� ����������� �� ������������ ����� ���������� �� ������ �������. ��������� ����������� ��������� ����� � ��������� ������.',
        45 => '������������ ������� ��� ���������� ��������. ���������� ����������� �������� ������� �� ��������� ���� �� ������.������.',
        30 => '����������� ���� �� ������� ��. ��������� ��������� ������� ����������.',
        19 => '� ������� � ��������� ���������� ����� ����� ���������� (TR_NR), ������� �� �������������.'
    );
    
    /**
     * ����������� ������
     *
     * @param int $src_id ID ��������� �������
     * @param int $src_type ��� ��������� �������
     */
    function __construct($src_id = NULL, $src_type = NULL) {
        $this->DB = new DB('master');
        $this->setSrc($src_id, $src_type);
        $this->_log = new log('yd_payments/%d%m%Y.log');
    }
    
    /**
     * ������������� �������� ������
     * 
     * @param int $src_id ID ��������� �������
     * @param int $src_type ��� ��������� �������
     */
    function setSrc($src_id, $src_type) {
        $this->_src = array('id'=>(int)$src_id, 'type'=>(int)$src_type);
    }
    
    /**
     * ���������� ����������������� �����
     * 
     * @param  float $amt �����
     * @return string ����� � 2 ������� ����� �����
     */
    private function _amtFmt($amt) {
        return number_format($amt, 2, '.', '');
    }
    
    /**
     * ��������� ����� ������� ��
     * 
     * @return string ����������������� �������� ����� �������, ��� bool false - ������
     */
    private function _initAmt() {
        $tr_amt = $this->_pdata['tr_amt'];
        if(!((float)$tr_amt)) 
            return $this->error('�������� �����');
        return ($this->_pdata['tr_amt'] = $this->_amtFmt($tr_amt));
    }
    
    /**
     * ��������� ����� ����� (��������) ��
     *
     * @return string ����� �������� ��, ��� bool false - ������
     */
    private function _initAcntNr() {
        $dstacnt_nr = trim($this->_pdata['dstacnt_nr']);
        if(!account::isValidYd($dstacnt_nr))
            return $this->error('�������� ����� ��������');
        return ($this->_pdata['dstacnt_nr'] = $dstacnt_nr);
    }
    
    /**
     * ��������� ��������� ��� ���������� ������� ��
     *
     * @return string ��������� ��� ���������� �������, ��� bool false - ������
     */
    private function _initCont() {
        $cont = trim($this->_pdata['cont']);
        if(!$cont)
            return $this->error('����� ��������� ����������');
        if(strlen($cont > 128))
            return $this->error('������� ������� ����� ���������');
        return ($this->_pdata['cont'] = $cont);
    }
    
    /**
     * ������� ����� ������ ������� ��
     * 
     * @return array ������ ������� (��. ������� yd_payments), ��� bool false - ������
     */
    private function _createPayment() {
        if(!$this->_src['id'] || !$this->_src['type'])
            return false;
        if(!$this->_initAmt())
            return false;
        $this->_pmt = $this->DB->row('INSERT INTO yd_payments (src_type, src_id, in_amt, is_locked) VALUES (?i, ?i, ?f, true) RETURNING *', $this->_src['type'], $this->_src['id'], $this->_pdata['tr_amt']);
        $this->_isPmtLocked = ($this->_pmt['is_locked'] == 't');
        return $this->_pmt;
    }
    
    /**
     * ��������� ������ ������� ��
     * 
     * @param  bool $lock �������� ����� ����������
     * @return array ������ ������� (��. ������� yd_payments), ��� bool false - ������
     */
    private function _lockPayment($lock = true) {
        if(!$this->_src['id'] || !$this->_src['type'])
            return false;
        $lw = $lock ? '�������������' : '��������������';
        $pmt = $this->DB->row('UPDATE yd_payments SET is_locked = ?b WHERE src_type = ?i AND src_id = ?i RETURNING *', $lock, $this->_src['type'], $this->_src['id']);
        if(!$pmt)
            return $this->error("�� ������� {$lw} ������ #{$pmt['id']}! ���������� � ���. �����.");
        $this->_isPmtLocked = $lock;
        return ($this->_pmt = $pmt);
    }
    
    /**
     * ���������� ������ ������� �� �� ��������� �������
     * @see yd_payments::setSrc
     * 
     * @param  bool $lock ����� �� ��������� ����������� ������ � ���������
     * @return array ������ ������� (��. ������� yd_payments), ��� NULL ���� ������ �� �������
     */
    function getPayment($lock = false) {
        if(!$this->_src['id'] || !$this->_src['type'])
            return NULL;
        $pmt = $this->DB->row('SELECT * FROM yd_payments WHERE src_type = ?i AND src_id = ?i'.($lock ? ' FOR UPDATE' : ''), $this->_src['type'], $this->_src['id']);
        $this->_isPmtLocked = ( $lock && $this->DB->error || $pmt['is_locked'] == 't' );
        return $pmt;
    }
    
    /**
     * ��������� ������������� �� ������
     *
     * @return bool
     */
    function isPmtLocked() {
        return $this->_isPmtLocked;
    }
    
    /**
     * �������������� ��������
     * 
     * @return array ������ ������� ��
     */
    private function _initPayment() {
        if($this->_pmt)
            return $this->_pmt;

        if($this->DB->start()) {
            $this->_pmt = $this->getPayment(TRUE);
            if($this->isPmtLocked()) {
                $this->DB->rollback();
                return $this->error('�������� �� ������ ������� ��� �����������...');
            }

            if(!$this->_pmt) {
                $this->_createPayment();
            } else {
                $this->_lockPayment(TRUE);
            }
            
            if($ok = ($this->_pmt && !$this->errors))
                $ok = $this->DB->commit();
            if(!$ok) {
                $this->DB->rollback();
                return false;
            }
        }

        return $this->_pmt;
    }
    
    /**
     * ��������� ��������
     */
    private function _commitPayment() {
        $this->_lockPayment(FALSE);
    }
    
    /**
     * ������������ �������� ���������� ��
     *
     * @param  float $tr_amt ����� �������
     * @param  string $dstacnt_nr ����� ����� (��������) ��
     * @param  string $cont ��������� ��� ���������� �������
     * @return float ����������� �����
     */
    function pay($tr_amt = NULL, $dstacnt_nr = NULL, $cont = NULL) {
        $this->_pdata = array('tr_amt' => $tr_amt, 'dstacnt_nr' => $dstacnt_nr, 'cont' => $cont);
        if($this->_initPayment()) {
            if($this->_initTr())
                $this->_analyzeTr();
            $this->_commitPayment();
        }
        $this->log("���������:\t{$this->_performedAmt}");
        $this->pmt = $this->_pmt;
        $this->tr = $this->_tr ? $this->_tr : $this->_pdata;
        $memBuff = new memBuff();
        $memBuff->delete(self::BALANCE_MEM_KEY);
        return $this->_performedAmt;
    }
    
    /**
     * ������������ �������� ������ � ��������� ����������
     * 
     * @return string ����� �������
     */
    function status() {
        if($this->_initPayment()) {
            if($this->_pmt['ltr_id'] && $this->_initTr())
                $this->_send( array('TR_NR'=>$this->_tr['id'], 'ACT_CD'=>self::ACT_STATUS) );
            $this->_commitPayment();
        }
        return $this->_answer;
    }
    
    /**
     * ������������ �������� ������ ������� ������
     *
     * @param  boolean $nocache   ��������� ����� ������ �� ����.
     * @return string ����� �������
     */
    function balance($nocache = false) {
        $memBuff = new memBuff();
        $balance = $nocache ? false : $memBuff->get(self::BALANCE_MEM_KEY);
        if($balance === false) {
            if($answer = $this->_send(array('ACT_CD'=>self::ACT_BALANCE))) {
                $balance = $answer['balance'];
                $memBuff->set(self::BALANCE_MEM_KEY, $balance, 180);
            }
        }
        return $balance;
    }
    
    /**
     * ���������� ��������� ��������������� ������
     * 
     * @return float
     */
    function _getLstBalance() {
        if($this->_tr && $this->_tr['balance'])
            return $this->_tr['balance'];
       return $this->DB->val('SELECT balance FROM yd_trs ORDER BY id DESC WHERE balance IS NOT NULL LIMIT 1');
    }
    
    /**
     * ��������� �������� �� ������� ���������� �����������
     * 
     * @return bool
     */
    private function _isTrPerformed() {
        return ($this->_tr['performed_dt'] && $this->_tr['act_cd'] == self::ACT_PAY);
    }
    
    /**
     * ���������� ������� ����� ������ �������� � ������������ �������.
     * 
     * @return float
     */
    private function _remAmt() {
        return $this->_amtFmt($this->_pmt['in_amt'] - ($this->_pmt['out_amt'] + $this->_performedAmt));
    }
    
    /**
     * �������� ������������ ����� ����������.
     * 
     * @param  float $amt ����� ����������
     * @return bool true - ���������, false - �� ���������
     */
    private function _safeAmt(&$amt) {
        if( $this->_performedAmtFixed != $this->_performedAmt )
            return $this->error('��������� �������� ����� (����� ���������� ���������� != ����� ��������������� ����������).');
        if( ($amt = $this->_amtFmt(min($amt, $this->_maxAmt, $this->_remAmt()))) <= 0 )
            return $this->error('������� ������������ � ���������� ������� �����.');
        return true;
    }

    /**
     * ���������� ���������� ��
     * 
     * @param  int $tr_id ID ����������
     * @return array
     */
    function getTr($tr_id) {
        if($tr_id)
            return $this->DB->row('SELECT * FROM yd_trs WHERE id = ?i', $tr_id);
        return NULL;
    }
    
    /**
     * �������������� ���������� ��
     * 
     * @return array
     */
    private function _initTr() {
        if($this->_pmt['ltr_id'])
            return ($this->_tr = $this->getTr($this->_pmt['ltr_id']));
        if( ! ($this->_initAmt() && $this->_initAcntNr() && $this->_initCont()) )
            return false;
        if( ! $this->_safeAmt($this->_pdata['tr_amt']) )
            return false;
        $this->_tr = $this->_pdata;
        return $this->_createTr();
    }
    
    /**
     * ������� ���������� ��
     *
     * @return array ���������� �� ��� false � ������ ������
     */
    private function _createTr() {
        if( !$this->_tr )
            return false;
        $this->_tr = $this->DB->row('INSERT INTO yd_trs (payment_id, dstacnt_nr, tr_amt, cont) VALUES (?i, ?, ?f, ?) RETURNING *',
                             $this->_pmt['id'], $this->_tr['dstacnt_nr'], $this->_tr['tr_amt'], $this->_tr['cont']);
        if($this->_tr) {
            $this->_pmt['ltr_id'] = $this->_tr['id'];
            return $this->_tr;
        }
        return $this->error('������ ��� �������� ����������.');
    }
    
    /**
     * �������� ����������� ����������
     * 
     * @param  array $answer ���������� ����� �������
     * @return bool true - �����, false - ������
     */
    private function _checkPayTr(&$answer) {
        $this->_tr['act_cd'] = self::ACT_CHECKPAY;
        $answer = $this->_send( array('TR_NR'=>$this->_tr['id'], 'ACT_CD'=>$this->_tr['act_cd'], 'DSTACNT_NR'=>$this->_tr['dstacnt_nr'], 'TR_AMT'=>$this->_tr['tr_amt'], 'CUR_CD'=>self::CUR_CD) );
        return ! ($this->errors || $answer['err_cd'] || $answer['res_cd']);
    }
    
    /**
     * ����������
     * 
     * @param  bool $new_tr ����� �� ��������� ����� ����������
     * @return bool false
     */
    private function _payTr($new_tr = false) {
        if( $new_tr && !$this->_createTr() )
            return false;
        if( !$this->_tr )
            return false;
        if( $this->_isTrPerformed() )
            return $this->error("������� ��������� ������������ ���������� {$this->_tr['id']}. ���������� � ���. �����.");
        if( ! $this->_safeAmt($this->_tr['tr_amt']) )
            return false;

        if($this->_ptry > 10 && $this->DEBUG) {
            return $this->error('������. ��������� ������.');
        }

        $this->_ptry++;
        if($this->_checkPayTr($answer)) {
            $this->_tr['act_cd'] = self::ACT_PAY;
            $answer = $this->_send( array('TR_NR'=>$this->_tr['id'], 'ACT_CD'=>$this->_tr['act_cd'], 'DSTACNT_NR'=>$this->_tr['dstacnt_nr'],
                                   'TR_AMT'=>$this->_tr['tr_amt'], 'CUR_CD'=>self::CUR_CD, 'CONT'=>$this->_tr['cont']) );
        }
        if($answer) {
            foreach($answer as $f=>$v)
                $this->_tr[$f] = $v;
            if($this->_commitTr()) {
                $this->_analyzeTr();
            }
        }

        return false;
    }
    
    /**
     * ���������� ��
     * 
     * @return bool true - �����, false - ������
     */
    private function _analyzeTr()
    {
        if(!$this->_tr)
            return false;
        
        // ���� ��� ���������, �� �������.
        if($this->_remAmt() <= 0)
            return true;

        // ���� ��� ���-�� ��������� �� ������� ��������, �� �� ����� ����� �������, �� �������.
        // !!! ���� ������ ��� ��������, �� ������� ����� ���� �� ������� ������� �� ��� ���, ���� �� ������� ������ 16, 40-42. � �����
        //     ������ ����� ������� ������ ������ �� ����� �������.
        //     ���� ����� ������ ��������, �� ����� � ������������ ���� ��������������� �������������� �������� � ������ ���� ���������, �� ������� ��� �������.
        //     �� ��� �� ���� ��������� ���� ����������, ������� ������ ���������� ������, �������.
        if($this->_remAmt() < $this->_pmt['in_amt'] && $this->_pdata['dstacnt_nr'] != $this->_tr['dstacnt_nr'])
            return $this->error("������� ����� �������� {$this->_pdata['dstacnt_nr']} ������������ �� ��������� � ���, �� ������� ��� ���� ����������� �� ������ �������: {$this->_tr['dstacnt_nr']}.<br/>���������� ��������� �������� � ���������� � ���. �����.");

        if($this->_tr['res_cd'] == self::RES_OK) {
            // ������ ���� �� �������� ��� �����. ������ ���� ���������:
            // �) ���� ��� ����� ������ ������ �� �������;
            // �) ��������� ���������� �� ���� ������������� (performed_dt is null), �������� �� ���������;
            // �) �������������, �� ��� �� ��� ���������. ����� ������� ����� � �����������.
            return $this->_payTr( $this->_isTrPerformed() );
        }

        if($this->_tr['res_cd'] == self::RES_WAIT) {
            // ��� ���� �������� ��������� ��������� � �������.
            if($this->_ptry < 3) {
                sleep(1);
                return $this->_payTr();
            }
            return $this->error('������ � ���������. ��������� ������.');
        }

        if($this->_tr['res_cd'] == self::RES_UNKNOWN || $this->_tr['err_cd'] == self::RES_UNKNOWN) {
            $ii = floor((time() - strtotime($this->_tr['req_date'])) / 60);
            $mi = $this->_tr['req_cnt'] <= 4 ? 5 : 30;
            if($ii >= $mi) // ��������� ������������� ��������, ��������� ������.
                return $this->_payTr();
            return $this->error(self::$yd_errs[self::RES_UNKNOWN] . ' ��������� ������� ����� '.($mi-$ii).' ���.');
        }

        switch( $err_cd = $this->_tr['err_cd'] ) {
            
            ///////// ������, ������� �� ����������� � ��. ����� ��������� � ��� �� �����������.
            
            case 16 :
                if($this->_initAcntNr() && !$this->_ptry) { // ������ ���� �������, �� ������ ���� �������� �������.
                    $this->_tr['dstacnt_nr'] = $this->_pdata['dstacnt_nr'];
                    return $this->_payTr();
                }
                break;

            case 25 :
                if($this->_initCont() && !$this->_ptry) { // ���� �������.
                    $this->_tr['cont'] = $this->_pdata['cont'];
                    return $this->_payTr();
                }
                break;

            case 26 : // ������ ������ ������. ����� � ���� �����������.
                break;

            case 14 : case 17 : case 18 : case 20 : case 22 :
            case 24 : case 50 : case 51 : case 53 : case 55 :
            case 56 :
                if(!$this->_ptry) // ���� ��� ������� � �������.
                    return $this->_payTr();
                break;


            ///////// ��� ���� ����� ����� ����� ���������� �������.

            case 40 :
            case 41 :
            case 42 :
                if(!$this->_ptry || $this->_tr['dstacnt_nr'] != $this->_pdata['dstacnt_nr']) { // ������� � ����� ���������.
                    $this->_tr['dstacnt_nr'] = $this->_pdata['dstacnt_nr'];
                    return $this->_payTr(true);
                }
                break;

            case 43 :
                $this->_maxAmt = $this->_tr['tr_amt'] * 0.618;  // ����� ������� ����, ������������ �������� �� ���������.
                return $this->_payTr(true);

            case 44 :
                if(date('Ymd') != date('Ymd', strtotime($this->_tr['req_date']))) { // ������ ���� �������� ����. ����.
                    return $this->_payTr(true);
                } 
                break;

            case 45 :
                if( !$this->_ptry && ($cbal = (float)$this->balance(true)) > (float)$this->_getLstBalance() ) { // ���������, ���� ������ ���������, �� �������� ��������� (�������, ��� ���� ���������).
                    $this->_tr['balance'] = $cbal;
                    return $this->_payTr(true);
                } 
                break;

            default :
                self::$yd_errs[$err_cd] = "����������� ������ (���: {$err_cd})";
                break;
                
        }

        $this->error( self::$yd_errs[$err_cd] );
    }
    
    /**
     * ���������� ������ �� ������
     * 
     * @param  array $sign_fields ���� ��� ������������ �������
     * @return array ����� �������
     */
    private function _send($sign_fields) {
        if(!($sign = $this->_sign($sign_fields)))
            return false;
        $query            = $sign_fields;
        $query['SIGN']    = $sign;
        $query['VERSION'] = self::VERSION;

        $context = array (
            'http' => array (
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($query, '', '&')
        ) );

        foreach($sign_fields as $f=>$v) $prms .= ($i++?', ':'')."{$f}={$v}";
        $this->log("��������:\t" . self::$act_nm[$query['ACT_CD']] . " [{$prms}]", "\r\n\r\n");
        $this->log("�������:\t{$sign}");

        if($this->DEBUG) {
            $context['http']['header'] .= $this->DEBUG['headers'];
            $this->_answer = @file_get_contents($this->DEBUG['address'], false, stream_context_create($context));
        } else {
            $this->_answer = @file_get_contents($this->_address, false, stream_context_create($context));
        }

        $this->log("�����:\t{$this->_answer}");

        return $this->_parseAnswer();
    }
        
    /**
     * ��������� ������� ��� �������
     *
     * @param  array $fields ���� ��� ������������ �������
     * @return string
     */
    private function _sign($fields) {
        if($fields) {
            $prms = implode('&', $fields);
            if($this->DEBUG)
                return $prms;
            @exec("echo '{$prms}' | ".self::PGP_SIGN, $encrypted, $errorcode);
            if($errorcode == 0)
                return implode(PHP_EOL, $encrypted);
        }
        return $this->error('������ ������� �������.');
    }
    
    /**
     * ����������� ����� ������ ������� � ������������� ������
     * 
     * @return array
     */
    private function _parseAnswer() {
        $answer = NULL;
        if($this->_checkAnswerSign()) {
            preg_match('/^RES_CD=([^\r\n]*)/m', $this->_answer, $m);
            $answer['res_cd'] = $m[1];
            preg_match('/^ERR_CD=([^\r\n]*)/m', $this->_answer, $m);
            $answer['err_cd'] = $m[1];
            preg_match('/^PERFORMED_DT=([^\r\n]*)/m', $this->_answer, $m);
            $answer['performed_dt'] = $m[1] ? $m[1] : NULL;
            preg_match('/^BALANCE=([^\r\n]*)/m', $this->_answer, $m);
            $answer['balance'] = $m[1] ? $m[1] : NULL;
        }
        return $answer;
    }
    
    /**
     * ��������� ������� ������ �������
     * 
     * @return bool true - ������� �����, false - ������
     */
    private function _checkAnswerSign() {
        if(!$this->_answer)
            return $this->error('������������ ����� �� �������.');
        if($this->DEBUG) {
            if($this->DEBUG['wrong_sign'])
                $errorcode = 1;
        } else {
            @exec("echo '{$this->_answer}' | ".self::PGP_CHECK, $message, $errorcode);
        }
        if ($errorcode > 0)
            return $this->error('PGP-������� �� �����!');
        return true;
    }
    
    /**
     * ��������� ����������
     * 
     * @return bool true - �����, false - ������
     */
    private function _commitTr() {
        
        if($this->_isTrPerformed()) {
            $this->_performedAmt += $this->_tr['tr_amt'];
        }
        $this->_tr = $this->DB->row('
           UPDATE yd_trs
              SET res_cd = ?i, err_cd = ?i, performed_dt = ?::timestamp without time zone, balance = ?f, act_cd = ?i, tr_amt = ?f, dstacnt_nr = ?,
                  req_cnt = req_cnt + 1, req_date = now()
            WHERE id = ?i
              AND performed_dt IS NULL
           RETURNING *
          ',
          $this->_tr['res_cd'], $this->_tr['err_cd'], $this->_tr['performed_dt'], $this->_tr['balance'], $this->_tr['act_cd'],
          $this->_tr['tr_amt'], $this->_tr['dstacnt_nr'], $this->_tr['id']
        );

        if($this->_tr) {
            if($this->_isTrPerformed()) {
                $this->_performedAmtFixed += $this->_tr['tr_amt'];
            }
            return true;
        }

        return $this->error('�� ������� ������������� ����������! ���������� � ���. �����.');
    }

    /**
     * ��������� ������ �������� ��������
     *
     * @param  string $from_dt ��������� ���� 
     * @param  string $to_dt �������� ����
     * @return string ���� � ���������������� �����, ��� bool false - ������
     */
    function createRegistry($from_dt = NULL, $to_dt = NULL) {
        $from_dt = date('Y-m-d', strtotime($from_dt === NULL ? '-1 day' : $from_dt));
        $to_dt   = date('Y-m-d', strtotime($to_dt === NULL   ? $from_dt.' +1 day' : $to_dt));

        $content = 'Agent_ID:'.self::AGENT_ID."\r\n"
                 . 'Agent_name:'.self::AGENT_NAME."\r\n"
                 . 'Contract_number:'.self::CONTRACT_NUM."\r\n";
        $tcnt = 0;
        $tsum = 0;
        $table = '';
        $trs = $this->DB->rows('SELECT * FROM yd_trs WHERE performed_dt >= ?::date AND performed_dt < ?::date AND act_cd = ?i ORDER BY performed_dt', $from_dt, $to_dt, self::ACT_PAY);
        if($trs) {
            foreach($trs as $tr) {
                $dtt = strtotime($tr['performed_dt']);
                $sum = $this->_amtFmt($tr['tr_amt']);
                $tcnt++;
                $tsum += $sum;
                $table .= date('d.m.Y', $dtt)."\t"
                       . date('H:i:s', $dtt)."\t"
                       . $tr['id']."\t"
                       . $tr['dstacnt_nr']."\t"
                       . $sum."\t"
                       . "\r\n";
            }
        }
        $content .= 'Total:'.$tcnt."\t".$this->_amtFmt($tsum)."\r\n";
        if($table) {
            $content .= "Table:Date\tTime\tTransaction\tAccount\tAmount\r\n"
                     .  $table;
        }
        $fname = self::AGENT_ID.'_'.date('Ymd', strtotime($from_dt));
        $fpath = self::REGISTRY_PATH.self::REGISTRY_DIR;
        if(!file_exists($fpath))
            mkdir($fpath);
        $rname = $fpath.'/'.$fname;
        $ne_name = $rname.self::REGISTRY_NOENC_SFX;

        if($fp = fopen($ne_name, 'w')) {
            fwrite($fp, $content);
            fclose($fp);
            @unlink($rname);
            $errorcode = 1;
            if($this->DEBUG) {
                if($ft = fopen($rname, 'w')) {
                    fwrite($ft, $content);
                    fclose($ft);
                    $errorcode = 0;
                }
            } else {
                @exec(self::PGP_ENCRIPT." -o {$rname} {$ne_name}", $encrypted, $errorcode);
            }
            if($errorcode == 0)
                return $rname;
            $this->error('������ ���������� �������.');
        }

        $this->error('�� ������� ������������ ������.');
        return false;
    }

    /**
     * ��������� ��������� � ���
     *
     * @param string $str ���������
     * @param string $pfx �������
     */
    function log($str, $pfx = '') {
        $this->_log->writeln($pfx . date('c') . "\t{$str}");
    }
    
    /**
     * ��������� ������.
     * ��������� ��������� �� ������ � ������ $this->errors � � ���
     * 
     * @param  string $err ��������� �� ������
     * @return bool false
     */
    function error($err) {
        $this->errors[] = $err;
        $this->log("������:\t{$err}");
        return FALSE;
    }
}

