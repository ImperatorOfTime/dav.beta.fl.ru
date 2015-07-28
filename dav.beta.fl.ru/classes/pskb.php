<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/onlinedengi.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/multi_log.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/LocalDateTime.php");

/**
 * ���������� � ������
 * 
 * ���-������� ������������� Free Lance(����� FL) API ��� ������ � �������������(����� LC). API
 * ������������ ����� ����� �������, � ������� FL ����� ��������� ������� � �������� ������.
 * �������������� ���������� �� ��������� HTTP.
 * 
 * ��� ������ API ��� POST ������� � URL https://werbpay.pscb.ru/apiLCPlace/methodName, 
 * ��� methodName ��� ��� API - �������. ��������� ���������� ����������� ��� POST - �������
 * �������. � ������, ���� �������� �� �������� ������� �����, �� ���������� �� �������� JSON. ���
 * �������� ��������� �������� ���������� ������ �������������� ��������� UTF-8. ���� ������
 * ������������ � ������� dd.mm.yyyy.
 * 
 * ����� ������������ �������� � ������� JSON.
 * 
 * ��� ������� ���������� ��������� LC � ��������� LC (��. ����� pskb_lc):
 * 
 */
class pskb {
    
    
    const USER_FRL = 0;
    const USER_EMP = 1;

    const CARD = 63;
    const WW = 199;
    const WMR = 2;      //�� ����.������� WMR ��� �������� � ���� (pskb_lc.ps[Cust|Perf])
    
    const WW_ONLY_SUM = 15000;
    const MAX_YD_SUM  = 15000;
    
    const DATE_COVER_DELAY =    5;
    const DATE_DOCS_DELAY =     2;
    const DATE_END_DELAY =      0;
    
    /**
     * �� ������� ���� �� ��������� �����������, ����� ������ ������ � ��������
     */
    const ARBITRAGE_PERIOD_DAYS = 2;
    
    /**
     * ����� ���������� ������� ����� ( 5 ���� �� �������� ���� + 2 ��� �� �������� ���� ) 
     * ������ ���� ��� + 10 ���� �� �������� - �������� ��� #0023494
     */
    const TEMP_STAGE_DELAY = 7;
    
    const REQV_ALL = 0;
    const REQV_FIZ = 1;
    const REQV_YUR = 2;
    
    
    const STATE_FORM =      'form';
    const STATE_NEW =       'new';
    const STATE_COVER =     'cover';
    const STATE_EXP_COVER = 'expCover';
    const STATE_EXP_EXEC =  'expExec';
    const STATE_EXP_END =   'expEnd';
    const STATE_END =       'end';
    const STATE_PASSED =    'passed';
    const STATE_TRANS =     'trans';
    const STATE_RET =       'ret';
    const STATE_ERR =       'err';
    
    const PAYOUT_END = 'END';
    const PAYOUT_ERR = 'ERR';
    
    const KEY_CHECK_AUTH   = ':FASLh*19rebn,cHag83esb';
    
    const URL_REJOIN = '/income/sbr_check.php';
    const URL_REJOIN_DEBUG = '/income/sbr_check.php';
    const URL_CARD_FRAME = 'https://webpay.pscb.ru/apiCard/list?lc=';
    const URL_CARD_FRAME_TEST = '/sbr/pskb_cards_test.php?lc=';
    /**
     * ���� �� �������������� ������� (����)
     */
    const PERIOD_RESERVED = 5;
    /**
     * ���� ������� ������ �� ��������� ����� ���������� ����� ���������� ������ (����) 
     */
    const PERIOD_FRL_EXEC = 2;
    /**
     * ���� ��� �������� ������� �� ������ ���� ����� ���������� �� ������ ��������� (����)
     */
    const PERIOD_EXP = 5;
    
    const PHYS = 0;
    const JURI = 1;
    
    const PERIOD_CREDIT = 7;
    
    const SMS_RESEND_KEY = '_pskb_resend_id_';
    
    const ADM_PAGE_SIZE = 30;
    
    /**
     * ��������� ������� �������� ����� supercheck
     */
    const PSKB_SUPERCHECK = true;
    const SUPERCHECK_LIMIT_ID_REQUEST = 300; // 300 �� � ����� ������� (� �� ������� ������ POST ������� 4000)
    
//    const SECRET_CARDS = '0v4EB9tbk%y904JG*8dbV2Srrpb3w9V@';
    const SECRET_CARDS = 'H4fInVOoaVuuGLp0SPxhrbot4agtkgz6';
    
    public static $state_messages = array(
        self::STATE_PASSED =>   '��������� ���������� ����',
        self::STATE_TRANS =>    '���� ������� �������.',
        self::STATE_END =>      '������� ���� �����������',
        self::STATE_COVER =>    '������� ���� �����������',
        self::PAYOUT_END =>     '������� ���� �����������',
        self::STATE_ERR =>      '������ �������',
    );
    
    public static $state_adm_messages = array(
        self::STATE_NEW => '�����',
        self::STATE_FORM => '�������� ����������',
        self::STATE_PASSED =>   '��������� ���������� ����',
        self::STATE_TRANS =>    '���� ������� �������.',
        self::STATE_END =>      '��������',
        self::STATE_COVER =>    '������',
        self::STATE_ERR =>      '������',
        self::STATE_EXP_COVER =>'������ ���� ��������',
        self::STATE_EXP_END =>  '������ ���� �����������',
        self::STATE_EXP_EXEC => '������ ���� ����������',
    );
    
    public static $state_po_messages = array(
        self::STATE_COVER =>    '��������� ���������� ����',
        self::STATE_PASSED =>   '��������� ���������� ����',
        self::STATE_TRANS =>    '���� ������� �������.',
        self::PAYOUT_END =>     '������� �����������',
        self::PAYOUT_ERR =>     '��������� � ���-�������',
    );
    
    public static $ps_str = array(
        onlinedengi::BANK_YL =>   '��',
        onlinedengi::WMR =>   'WMR',
        onlinedengi::YD =>   '��',
        onlinedengi::CARD =>   '��',
        self::WW => '��',
        self::WMR =>   'WMR',
        self::CARD =>   '��',
    );

        /**
     * ���� ��������� ��������� �������
     * 
     * @var array 
     */
    public static $psys = array(
        self::USER_EMP => array(
            onlinedengi::BANK_YL => '���������� �������',
            onlinedengi::WMR =>     'Webmoney, �����',
            onlinedengi::YD =>      '������.������',
            onlinedengi::CARD =>    '���������� �����',
        ),
        self::USER_FRL => array(
            onlinedengi::BANK_YL => '���������� �������',
            onlinedengi::WMR =>     'Webmoney, �����',
            onlinedengi::YD =>      '������.������',
            self::WW =>             '���-�������',
        )
    );
    
    public static $form_map = array(
        self::PHYS => sbr::FT_PHYS,
        self::JURI => sbr::FT_JURI
    );
    
    public static $exrates_map = array(
        onlinedengi::BANK_YL => exrates::BANK,
        onlinedengi::WMR => exrates::WMR,
        2 => exrates::WMR,   // ������ ��
        onlinedengi::YD => exrates::YM,
        onlinedengi::CARD => exrates::CARD,
        self::WW => exrates::WEBM,
    );

    public static $psys_dest = array(
        onlinedengi::BANK_YL => '�� ���������� ����',
        onlinedengi::WMR => '�� ���� � ������� Webmoney',
        onlinedengi::YD => '�� ���� � ������� ������.������',
        self::WW => '�� ���� � ������� <a class="b-layout__link" href="https://webpay.pscb.ru/login/auth" target="_blank">���-�������</a> <a href="https://feedback.fl.ru/topic/397421-veb-koshelek-obschaya-informatsiya/" target="_blank" class="b-shadow__icon b-shadow__icon_top_-1 b-shadow__icon_valign_middle b-shadow__icon_quest b-shadow__icon_quest_no_event"></a>',
    );

    /**
     * ���� ������ ������������ ������, � ��������������� �� ��������
     */
    public static $psys_error_codes = array(
        '0' => '������',
        '100' => '���������� ���� ��������',
        '101' => '���������� ���� �����',
        '102' => '����� ���� �������� �����������',

        '200' => '������������ ��� ����� ����������',

        '300' => '������ �������� ����������',
        '301' => '������������ ��� ������������',
        '302' => '������: ���������� ������� �������',
        '303' => '����������, ���� ����������� ������ ������������ �����',
        '304' => '���� ��������',
        '305' => '���� ��� ������ ������������',

        '500' => '�� ��������� ID',
        '501' => '�� ���������� ������� ������ ��� ������ �������',
        '502' => '�������� ID',
        '503' => '�������� ������',
        '504' => '������������� ����� �����������',
        '505' => '�������� ��� �������������',
        '506' => '��� �� ������� ��� �������',
        '507' => '�� ������� ��� ��������',
        '599' => '������ �������� �� ��������',
        '600' => '������ ����������, ��������� �� ���� ���-��������',
    );    
    
    /**
     * 
     * @var string 
     */
    protected $_request_url = 'http://192.168.88.13/apiLCPlace/';
    
    /**
     *
     * @var pskb_user 
     */
    private $_frl;
    
    /**
     *
     * @var pskb_user 
     */
    private $_emp;
    
    /**
     *
     * @var sbr 
     */
    private $_sbr;
    
    /**
     *
     * @var array 
     */
    public $_lc;


    /**
     * 
     * @var DB 
     */
    private $_db;
    
    
    private $_messages;
    
    
    private $_log;
    
    /**
     * ������ ���� � ������� _pskb_log ?
     * 
     * @var type 
     */
    private $_useAlternativeLog = true;
    
    public $not_different_finance = true;
    
//    private static $_instance;
    
    /**
     * ������ �������� ������� � ����������� �������� 
     */
    const PERIOD_BANK_CHECK = '7 days';
    /**
     * ������ ����������, ������� ����� ������������� ��� ������ � ������.
     * 
     * @var type 
     */
    public static $reqvs_fields = array(
        'mob_phone' =>  array('��������� �������', array( 'all' ), array('all')),
        'full_name' =>  array('������ �������� �����������', array( onlinedengi::BANK_YL), array(sbr::FT_JURI) ) ,
        'address_jry'=> array('����������� �����', array('all'), array(sbr::FT_JURI)),
        'fio' =>        array('�������, ���, ��������', array( 'all' ), array(sbr::FT_PHYS)),
        'inn' =>        array('���', array( onlinedengi::BANK_YL ), array(sbr::FT_JURI)),
        'bank_bik' =>   array('��� �����', array( onlinedengi::BANK_YL ), array('all')),
        'bank_rs' =>    array('��������� ����', array( onlinedengi::BANK_YL), array('all')),
        'el_yd' =>      array('������.������', array( onlinedengi::YD ), array(sbr::FT_PHYS)),
        'el_wmr' =>     array('WMR', array( onlinedengi::WMR ), array(sbr::FT_PHYS)),
    );
    
    /**
     * ������ ����������, ������� ����� ������������� ��� ������ � ������ ��� �� ����������
     * 
     * @var type 
     */
    public static $reqvs_fields_rez = array(
        'mob_phone' =>  array('��������� �������', array( 'all' ), array('all')),
        'full_name' =>  array('������ �������� �����������', array( onlinedengi::BANK_YL), array(sbr::FT_JURI) ) ,
        'address_fct'=> array('����� ������������ ����������', array(onlinedengi::BANK_YL), array(sbr::FT_JURI)),
        //'address_jry'=> array('����������� �����', array('all'), array(sbr::FT_JURI)),
        'fio' =>        array('�������, ���, ��������', array( 'all' ), array(sbr::FT_PHYS)),
        'rnn' =>        array('��������������� ����� � ��������� ������', array( onlinedengi::BANK_YL ), array('all')),
        'bank_rf_bik'=> array('��� ��������������� ����� � ��', array( onlinedengi::BANK_YL ), array('all')),
        'bank_rs' =>    array('��������� ����', array( onlinedengi::BANK_YL), array('all')),
        'bank_ks' =>    array('����������������� ����', array( onlinedengi::BANK_YL), array('all')),
        'bank_name'=>   array('�������� ����� �� ������� ������', array( onlinedengi::BANK_YL ) , array('all')  ),
        //'bank_rf_ks'=>  array('����������������� ���� ��������������� ����� � ��', array( onlinedengi::BANK_YL ), array('all') ),
        'bank_city'    => array('����� �����', array( onlinedengi::BANK_YL ) , array('all')  ),
        'bank_country' => array('������ �����', array( onlinedengi::BANK_YL ) , array('all')  ),
        'bank_swift'=>  array('S.W.I.F.T.', array( onlinedengi::BANK_YL ), array('all') ),
        'el_yd' =>      array('������.������', array( onlinedengi::YD ), array(sbr::FT_PHYS)),
        'el_wmr' =>     array('WMR', array( onlinedengi::WMR ), array(sbr::FT_PHYS)),
    );
    
    /**
     * ������ ����������, ������� ����� ������������� ��� ������ � ������
     * ��� ����� (� ���������� � ������������)
     * 
     * @var type 
     */
    public static $reqvs_fields_both = array(
        'mob_phone' =>  array('��������� �������', array( 'all' ), array('all')),
        'full_name' =>  array('������ �������� �����������', array( onlinedengi::BANK_YL), array(sbr::FT_JURI) ) ,
        'address_jry'=> array('����������� �����', array('rezident'), array(sbr::FT_JURI)),
        'address_fct'=> array('����� ������������ ����������', array('not_rezident'), array(sbr::FT_JURI)),
        'fio' =>        array('�������, ���, ��������', array( 'all' ), array(sbr::FT_PHYS)),
        'inn' =>        array('���', array( onlinedengi::BANK_YL, 'rezident' ), array(sbr::FT_JURI)),
        'bank_bik' =>   array('��� �����', array( onlinedengi::BANK_YL, 'rezident' ), array('all')),
        'rnn' =>        array('��������������� ����� � ��������� ������', array( onlinedengi::BANK_YL, 'not_rezident' ), array('all')),
        'bank_rf_bik'=> array('��� ��������������� ����� � ��', array( onlinedengi::BANK_YL, 'not_rezident' ), array('all')),
        'bank_rs' =>    array('��������� ����', array( onlinedengi::BANK_YL), array('all')),
        'bank_name'=>   array('�������� ����� �� ������� ������', array( onlinedengi::BANK_YL, 'not_rezident' ) , array('all')),
        'bank_ks'=>     array('����������������� ����', array( onlinedengi::BANK_YL, 'not_rezident' ), array('all')),
        'bank_swift'=>  array('S.W.I.F.T.', array( onlinedengi::BANK_YL, 'not_rezident' ), array('all')),
        'el_yd' =>      array('������.������', array( onlinedengi::YD ), array(sbr::FT_PHYS)),
        'el_wmr' =>     array('WMR', array( onlinedengi::WMR ), array(sbr::FT_PHYS)),
    );
    
    public static $card_messages = array(
        0 =>        '������ ��������� �������',
        1 =>        '������ ��������� � ���������, ����������� �������',
        2 =>        '������ ������� ������������� ����������� �������',
        -1 =>       '���������� ���������� ��',
        -2 =>       '���������� ���������� ����',
        -3 =>       '�������� ��������� �������, ������ �� ������ �������� � ���������� ������',
        -4 =>       '����� �� ���������: ���������, ���� �����, � ������� �������� ������� ������, �� ��������� � ���-�������� ��� ������, � ��� ���������, �������� ��������� ������',
        -5 =>       '����������� ������, ���������� ����������',
        -17 =>      '������� �� �����',
        -18 =>      '��������� ������� ����',
        -19 =>      '������� �����',
        -999 =>     '������ �� �������� ���� �� ������ ����������',
    );
    
    
    /**
     * 
     * @param pskb_user $emp    ��������� ��������� � ��������� ������� pskb_user
     * @param pskb_user $frl    ��������� ����������� � ��������� ������� pskb_user
     */
    public function __construct (sbr $sbr = null) {        
        if (defined('PSKB_TEST_MODE')) {
            $_host = !defined('IS_LOCAL') ? str_replace('http://', 'https://', $GLOBALS['host']) : $GLOBALS['host'];
            $this->_request_url = $_host . '/sbr/pskb_server.php?method=';
        }
        $this->_db = new DB('master');
        $this->_sbr = $sbr;
        $this->_lc = $this->getLC();
        
    }
    
    public function setSbr (sbr $sbr) {
        $this->_sbr = $sbr;
    }
    
    /**
     * ��� ������ �� �������� ���
     * 
     * @param array  $lc      ������ � �������
     * @param string $pfx     ������� ('', 'Cust', 'Perf')    
     * @return string
     */
    public function getMd5Reqvs($lc, $pfx = '') {
        $check_params = array('tag', 'alien', 'name', 'num', 'ps', 'acc', 'inn', 'nameBank', 'swift', 'cityBank', 'cntrBank', 'corAccbank');
        $string_param = '';
        foreach($check_params as $check) {
            if($check == 'num') $lc[$check.$pfx] = str_replace('+', '', $lc[$check.$pfx]); // � ������� pskb_lc ���� ��� ����� � � pskb_users � ������
            $string_param .= (string) $lc[$check.$pfx];
        }
        return md5($string_param);
    }
    
    public function diffUserReqvs($reqv1, $reqv2) {
        $this->not_different_finance = ($reqv1 == $reqv2);
        return $this->not_different_finance;
    }
    
    /**
     * ���������� ����������� ������ ������������
     * 
     * @param integer $ps   ��� ������
     * @return boolean|\pskb_user
     */
    public function initPskbUser($ps) {
        if (!$ps) {
            return false;
        }
        $reqv_fn  = $this->_sbr->isEmp() ? 'getEmpReqvs' : 'getFrlReqvs';
        $user     = new pskb_user($this->_sbr->$reqv_fn(), intval($this->_sbr->isEmp()));
        $user->checkPsys($ps, null, true);
        
        return $user;
    }
    /**
     * �������������� ������ LC.
     * ��������� ����������� ��������� ������� � ������������ � ���������� �����������.
     * ���� ���������� ����������, ������������ �� � ������� pskb_lc
     * 
     * @param type $ps          ��������� ��������� �������
     * @return type
     */
    public function prepareLC ($ps) {
        if (!$ps) {
            $this->_setError('������ �������.');
            return false;
        }
        
        $reqv_fn = $this->_sbr->isEmp() ? 'getEmpReqvs' : 'getFrlReqvs';
        $user_pfx = 'Perf';
        
        if ($this->_sbr->isEmp()) {
            $user_pfx = 'Cust';
            $data['ps_emp'] = $ps;
            $data['sum'] = $this->_sbr->getReserveSum(true, pskb::$exrates_map[$ps]);
        } else {
            $data['ps_frl'] = $ps;
            $data['sum'] = $this->_sbr->getReserveSum();
        }
        
        $data['sbr_id'] = $this->_sbr->data['id'];
        
        $user = new pskb_user($this->_sbr->$reqv_fn(), intval($this->_sbr->isEmp()));
        if ($user->checkPsys($ps, null, true)) {
            if ($this->_sbr->isEmp()) {
                $err_txt = '��������';
            } else {
                $err_txt = '���������';
            }
            $this->_setError("��������� ������ {$err_txt} ����� �� ��������. �� ������� ������ �� �������� �������.");
            return false;
        }
        
        $data['tag' . $user_pfx] = $user->tag;
        $data['alien' . $user_pfx] = $user->alien;
        $data['name' . $user_pfx] = $user->name;
        $data['num' . $user_pfx] = $user->num;
        $data['ps' . $user_pfx] = $user->ps;
        $data['acc' . $user_pfx] = $user->acc;
        $data['inn' . $user_pfx] = $user->inn;
//        $data['kpp' . $user_pfx] = $user->kpp;
        $data['nameBank' . $user_pfx] = $user->nameBank;
        $data['cityBank' . $user_pfx] = $user->cityBank;
        $data['cntrBank' . $user_pfx] = $user->cntrBank;
        $data['swift' . $user_pfx] = $user->swift;
        $data['corAccbank' . $user_pfx] = $user->corAccbank;
        $data['email' . $user_pfx] = $user->email;
          
        $lc = $this->getLC();
        if ($lc['lc_id'] && $lc['state'] == self::STATE_NEW) {
            $this->_setError('���������� ��� ������. ��������� ������.');
            return false;
        }
        if (!$this->_sbr->isEmp() && !$lc['id']) {
            $res = $this->_db->insert('pskb_lc', $data, 'id');
        } else {
            $res = $this->upLC($data, $this->_sbr->data['id'], 'sbr_id');
        }
        
        return $res;
    }
    
    /**
     * ������� ����������. ������������, ���� ����������, �������� ��� �������������.
     * � ������ �������� ��������� ���� ���������� ������������� ��� � ��������� �������.
     * 
     * @param type $sum         ����� �������
     * 
     * @return pskb_lc
     */
    public function reserve () {
        $lc = $this->getLC();
        $numDog = $this->_sbr->data['id'];
        
        if($lc['lc_id'] > 0 && $lc['state'] == self::STATE_ERR) {
            $resp  = $this->_checks(json_encode(array('id' => array($lc['lc_id']))));
            $lc_ch = $resp[$lc['lc_id']];
            
            if($lc_ch->state == self::STATE_NEW) {
                if($this->not_different_finance) {
                    return 'no_different';
                } else {
                    $this->upLC(array('lc_id' => null), $lc['lc_id']); // ����� ����� ����������, ���������� ������ �������
                    $lc['lc_id'] = null;
                }
            } elseif($lc_ch->state == self::STATE_COVER) {
                $this->upLC(array('state' => 'new'), $lc['lc_id']);
                pskb::checkStatus(array($lc['lc_id']), $in, $out);
                return true;
            }
        }
        
        if (!$lc) {
            $this->_setError('������ �������.');
            return false;
        }
        
        if ($lc['lc_id'] && $lc['state'] == self::STATE_NEW) {
            $this->_setError('���������� ��� ������. ��������� ������.');
            return false;
        }
        $cdate = new LocalDateTime();
        $cdate->getExcDaysInit(false, true);
        
        $stages = $this->_sbr->getStages();
        $work_time = 0;
        foreach ($stages as $stage) {
            $work_time += $stage->data['work_time'];
            $cdate->start_time = "now + {$work_time} day";
            $cdate->setTimestamp(strtotime("now + {$work_time} day"));
            $cdate->getWorkForDay(self::TEMP_STAGE_DELAY);
            $work_time += $cdate->getCountDays();
        }
        
        // ������� 5 ������� ����
        $cdate->getWorkForDay(self::DATE_COVER_DELAY, true);
        $cover_time = $cdate->getTimestamp();
        $exec_time = $cover_time + 3600*24*$work_time;
        $end_time = $exec_time + 3600*24*self::DATE_END_DELAY;
        
        $dateCoverLC = date('d.m.Y', $cover_time);
        $dateExecLC = date('d.m.Y', $exec_time);
        $dateEndLC = date('d.m.Y', $end_time);
        
        $resp = $this->_addLC(
            $lc['sum'], 
            $this->_sbr->data['id'], 
            $dateExecLC, 
            $dateEndLC, 
            $dateCoverLC, 
            $lc['tagCust'], 
            $lc['alienCust'], 
            $lc['nameCust'],
            $lc['numCust'], 
            $lc['psCust'], 
            $lc['accCust'], 
            $lc['innCust'], 
//            $lc['kppCust'], 
            $lc['nameBankCust'], 
            $lc['swiftCust'], 
            $lc['corAccbankCust'], 
            $lc['emailCust'],
            $lc['cityBankCust'],
            $lc['cntrBankCust'],
                
            $lc['tagPerf'],
            $lc['alienPerf'], 
            $lc['namePerf'], 
            $lc['numPerf'], 
            $lc['psPerf'], 
            $lc['accPerf'], 
            $lc['innPerf'], 
//            $lc['kppPerf'],
            $lc['nameBankPerf'], 
            $lc['swiftPerf'], 
            $lc['corAccbankPerf'],
            $lc['emailPerf'],
            $lc['cityBankPerf'],
            $lc['cntrBankPerf'],
            $this->_sbr->data['cost']
        );
        
        $dateCoverLC = date('Y-m-d', $cover_time);
        $dateExecLC = date('Y-m-d', $exec_time);
        $dateEndLC = date('Y-m-d', $end_time);
        
        if ($resp->id) {
            $data = array(
                'lc_id' => $resp->id,
                'dateCoverLC' => $dateCoverLC,
                'dateExecLC' => $dateExecLC,
                'dateEndLC' => $dateEndLC,
                'state' => $resp->state,
                'stateReason' => $resp->stateReason,
            );
            
            $res = $this->upLC($data, $this->_sbr->data['id'], 'sbr_id');
        } else {
            $this->_setError('������ �������� �����������.');
            $resp = false;
        }
        
        return $resp;
    }
    
    /**
     * ������ �� ��������� �����������.
     * ���� ���������� ������������ ��� � �����.
     * 
     * @param sbr_stages $stage 
     * @param type $sumCust     ����� � ��������� ���������
     * @param type $sumPerf     ����� � ��������� �����������. �����!! ����� ��������� ��������� ��� ����� ����� ��������!!!
     * @return pskb_lc          �������� ����������� � ��������� pskb_lc ��� false, ���� ������
     */
    public function payoutOpen (sbr_stages $stage, $sumCust, $sumPerf) {
//        $stage = $this->_sbr->getStageById($stage_id);
        if (!$stage) {
            return false;
        }
        if (!$this->_sbr) $this->_sbr = $stage->sbr;
        
        $lc = $this->getLC();
        if (!$lc) {
            return false;
        }
        
        $dateAct = date('d.m.Y');
        $idAct = $stage->getOuterNum();
        
        $resp = $this->_openLC($lc['lc_id'], $sumCust, $sumPerf, $dateAct, $idAct);
        if($resp->state != 'passed') $resp->state = 'err';
        if (!$resp->id || $resp->state == 'err') {
            $this->_setError($resp->stateReason);
            return false;
        }
        
        $data['state'] = $resp->state;
        $data['stateReason'] = $resp->stateReason;
        $data['stage_id'] = $stage->data['id'];
        
        $this->upLC($data, $lc['lc_id']);
        $stage->payoutUpdateState($resp);
        return $resp;
    }
    
    /**
     * ������������� ������� �� ������� �������
     * 
     * @param type $code
     * @return pskb_lc      �������� ����������� � ��������� pskb_lc ��� false, ���� ������
     */
    public function payoutConfirm ($code, $stage_id) {
        $lc = $this->getLC();
        
        if (!$lc) {
            $this->_setError('���������� �� ������.');
            return false;
        }
        
        if ($lc['state'] != 'passed') {
            $this->_setError('������������ ������ �����������.');
            return false;
        }
        
        $resp = $this->_subOpenLC($lc['lc_id'], $code);
        if($resp->state != 'trans') $resp->state = 'err'; // ��� ������� �� state ���� trans ���� err, ��� ��������� ������� err
        if (!$resp->id || ($resp->state == 'err' && $resp->stateReason) ) {
            $this->_setError($resp->stateReason, true);
            return false;
        }
        
        $data['state'] = $resp->state;
        $data['stateReason'] = $resp->stateReason;
        
        $res = $this->upLC($data, $this->_sbr->data['id'], 'sbr_id');
        $sbr = new sbr(get_uid());
        $stage = $sbr->getStage($stage_id);
        $stage->payoutUpdateState($resp);
        
        return $resp;
    }
    
    public function prolongLC ($id, $days) {        
        if ($days <= 0) {
            $this->_setError('���� ����������� ����� �������� ������ � ������� ����������.');
            return false;
        }
        
        $lc = $this->_lc;
        if (!$lc || ($lc && $lc['lc_id'] != $id)) {
            $lc = $this->getLCbyId(intval($id));
        }
        
        if (!$lc) {
            $this->_setError('���������� �� ������.');
            return false;
        }
        
        if (preg_match('/^exp/', $lc['state']) || in_array($lc['state'], array(pskb::STATE_END, pskb::STATE_ERR))) {
            $this->_setError('������������ ������ �����������.');
            return false;
        }
        
        $dateCoverLCtime = strtotime($lc['dateCoverLC']);
        $dateExecLCtime = strtotime($lc['dateExecLC']);
        $dateEndLCtime = strtotime($lc['dateEndLC']);
        
        $dateExecLCtime = mktime(0, 0, 0, date('m', $dateExecLCtime), date('d', $dateExecLCtime)+$days, date('Y', $dateExecLCtime) );
        $dateEndLCtime = mktime(0, 0, 0, date('m', $dateEndLCtime), date('d', $dateEndLCtime)+$days, date('Y', $dateEndLCtime) );
        
        $dateCoverLC = date('d.m.Y', $dateCoverLCtime); // ���� �������� �� ������
        $dateExecLC = date('d.m.Y', $dateExecLCtime);
        $dateEndLC = date('d.m.Y', $dateEndLCtime);
        
        $resp = $this->_changeDateLC($id, $dateExecLC, $dateEndLC, $dateCoverLC);
        if (!$resp->id) {
            $this->_setError('������������ ����� �������');
            return false;
        }
        if ($resp->state == 'err') {
            $this->_setError($resp->stateReason);
            return false;
        }
        
        $data['dateExecLC'] = date('Y-m-d', $dateExecLCtime);
        $data['dateEndLC'] = date('Y-m-d', $dateEndLCtime);
        
        $res = $this->upLC($data, $lc['lc_id'], 'lc_id');
        
        return $resp;
    }
    
    /**
     * 
     * @return boolean
     */
    public function resendCode (sbr_stages $stage) {
        $lc = $this->getLC();
        
        if (!$lc) {
            $this->_setError('���������� �� ������.');
            return false;
        }
        
        $pskb_lc = $this->_checkLC($lc['lc_id']);
        
        if($pskb_lc->state == pskb::STATE_COVER && strstr($pskb_lc->stateReason, 'nosignPerf'))  {
            $emp_percent = 0;
            $frl_percent = 1;
            
            $stage->getPayouts(get_uid());
            
            if($stage->arbitrage === false) {
                $stage->getArbitrage(false, false);
            }
            
            if($stage->arbitrage && $stage->arbitrage['resolved']) {
                $emp_percent = abs(sbr::EMP - $stage->arbitrage['frl_percent']);
                $frl_percent = abs(sbr::FRL - $stage->arbitrage['frl_percent']);
            }

            $sumCust = round($stage->data['cost'] * $emp_percent, 2);
            $sumPerf = round($stage->data['cost'] * $frl_percent, 2);
            
            if( ($sumCust + $sumPerf)  != $this->data['cost'] && $sumCust > 0) { // �� �������� ���� ����������, ������ ���� ������� �� ��������
                $sumCust -= 0.01; // ������������ ������� ������. 
            }
            
            $idAct   = $stage->getOuterNum();
            $dateAct = date('d.m.Y', strtotime($stage->payouts[$stage->sbr->data['frl_id']]['requested']));
            $resp    = $this->_openLC($lc['lc_id'], $sumCust, $sumPerf, $dateAct, $idAct);
            if($resp->state != 'passed') $resp->state = 'err';
            if (!$resp->id) {
                $this->_setError($resp->stateReason);
                return false;
            }
        
            if ($resp->state == pskb::STATE_PASSED) {
                $data['state'] = $resp->state;
                $data['stateReason'] = $resp->stateReason;
                $this->upLC($data, $lc['lc_id']);
            }
            
            return $resp;
        }
        
        if ($lc['state'] != 'passed') {
            $this->_setError('������������ ������ �����������.');
            return false;
        }
        
        $resp = $this->_reqCode($lc['lc_id']);
        
        if (!$resp->id || $resp->state == 'err') {
            $this->_setError($resp->stateReason);
            return false;
        }
        
        return $resp;
    }
    
    /**
     * 
     * @return boolean
     */
    public function checkNew () {
        $lc = $this->getLC();
        if (!$lc) {
            $this->_setError('���������� �� ������.');
            return 'err';
        }
        
        if ($lc['state'] != 'form') {
            $this->_setError('��������� ������ �� ��������������.');
            return 'err';
        }
        
        if ($lc['state'] == self::STATE_NEW) {
            return self::STATE_NEW;
        }
        
        $lc = $this->_checkLC($lc['lc_id']);
        
        switch ($lc->state) {
            case 'form': return 'form';
                break;
            case self::STATE_NEW:
                require_once $_SERVER['DOCUMENT_ROOT'].'/classes/smail.php';
                $smail = new smail();
                $smail->SbrReservedMoney($this->_sbr->data['id'], $this->_sbr->data['emp_id']);
                $data['created'] = 'NOW()';
            case self::STATE_COVER:
            case self::STATE_ERR:
                $data['state'] = $lc->state;
                $data['stateReason'] = $lc->stateReason;

                $this->upLC($data, $lc->id);
                
                return $lc->state;
                break;
            default:
                $this->_setError('��������� ������ �� ��������������.');
                return 'err';
        }
        
        return $lc['state'];
    }
    
    public function getLCInfo() {
        $lc = $this->getLC();
        
        if (!$lc) {
            $this->_setError('���������� �� ������.');
            return false;
        }
        
        $pskb_lc = $this->_checkLC($lc['lc_id']);
        
        if (!$pskb_lc->id) {
            $this->_setError('���������� �� ������, ���� �������� � ������� �����.');
            return false;
        }
        
//        $sql = "SELECT  st.num, st.cost,
//                        po.*,
//                        f.login flr_login, 
//                        e.login emp_login
//                FROM sbr_stages st
//                INNER JOIN pskb_lc lc ON lc.sbr_id = st.sbr_id
//                INNER JOIN sbr s ON s.id = lc.sbr_id
//                INNER JOIN freelancer f ON f.uid = s.frl_id
//                INNER JOIN employer e ON e.uid = s.emp_id
//                LEFT JOIN sbr_stages_payouts po ON po.stage_id = st.id
//                WHERE st.sbr_id = ?
//                ORDER BY st.num";
        
        $sql = "SELECT po.* FROM sbr_stages_payouts po
                INNER JOIN sbr_stages st ON st.id = po.stage_id
                INNER JOIN pskb_lc lc ON lc.sbr_id = st.sbr_id
                WHERE st.sbr_id = ?";
        
        $po = $this->_db->rows($sql, $lc['sbr_id']);
        
        $payouts = array();
        foreach ($po as $row) {
            $payouts[$row['stage_id']][] = $row;
        }
        
        return array(
            'lc' => $lc,
            'pskb_lc' => $pskb_lc,
            'payouts' => $payouts,
        );
    }
    
    /**
     * ��������� ���������� ������������ � �������� form|new.
     * ���� ������ ��������� �� cover, �������� ������ � ��������������� ������
     * 
     * @param type $cntIn       
     * @param type $cntOut
     * @return boolean
     */
    public static function checkStatus ($ids = array(), &$cntIn = 0, &$cntOut = 0) {
        $db = new DB('master');
        $sql = "SELECT lc.*, s.emp_id, s.frl_id FROM pskb_lc lc
                INNER JOIN sbr s ON s.id = lc.sbr_id AND s.is_draft = false
                WHERE lc_id IS NOT NULL AND state IN ('form', 'new')";
        if ($ids) {
            $sql .= " AND lc_id IN (?l) ";
            $data = $db->rows($sql, $ids);
        } else {
            $data = $db->rows($sql);
        }
        
        if (!$data) {
            return false;
        }
        
        $cntIn = count($data);
        
        $list = array();
        foreach ($data as $row) {
            $list[] = intval($row['lc_id']);
        }
        
        $pskb = new pskb();
        $resp = $pskb->_checks(json_encode(array('id' => $list)));
        
        foreach ($data as $row) {
            if (!$row['lc_id'] || !isset($resp[$row['lc_id']])) continue;
            $lc = $resp[$row['lc_id']];
            
            if ($lc->state == $row['state']) {
                continue;
            }
                
            $up_data = array(
                'state' => $lc->state,
                'stateReason' => $lc->stateReason
            );
            
            switch ($lc->state) {
                case self::STATE_NEW:
                    // ����������� ������ ��� ������� form
                    if ($row['state'] != pskb::STATE_FORM) {
                        continue;
                    }
                    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/smail.php';
                    $smail = new smail();
                    $smail->SbrReservedMoney($row['sbr_id'], $row['emp_id']);
                    $up_data['created'] = 'NOW()';
                    break;
                case self::STATE_COVER:
                    // ������� ����� ������ �� ������� new
                    if ($row['state'] != pskb::STATE_NEW) {
                        continue;
                    }
                    
                    $time = time();
                    $date = date('c', $time);

                    $descr = "���� ���������� #{$row['lc_id']};"
                           . " ��������� c������ ������������ #{$row['ps_emp']}: ����� ������ {$row['sum']} ���.;"
                           . " ��������� {$date}";

                    $account = new account();
                    $account->GetInfo($row['emp_id'], true);

                    $ammount = $row['sum'];
                    $op_code = sbr::OP_RESERVE;
                    $amm = 0;
                    $descr .= ' ��� #' . $row['sbr_id'];
                    $error = $account->deposit($op_id, $account->id, $amm, $descr, onlinedengi::PAYMENT_SYS, $ammount, $op_code, $row['sbr_id']);
                    
                    $up_data['covered'] = 'NOW()';
                    break;
                case self::STATE_EXP_COVER:
                case self::STATE_EXP_EXEC:
                case self::STATE_EXP_END:
                    break;
                case self::STATE_END:
                    break;
                case self::STATE_ERR:
                    $up_data['dol_is_failed'] = null;
                    break;
                default:
                    continue;
            }

            if (!$error) {
                $db->update('pskb_lc', $up_data, 'lc_id = ?', $row['lc_id']);
            }
            $cntOut++;
        }
        
        // �������� ������
        
        return true;
    }

    /**
     * ������ ���� ������ ������
     */
    public static function fixStagePayoutsCompleted() {
        $db = new DB('master');

        $sql = "SELECT sp.*, ss.sbr_id, ss.num, pl.lc_id, u.role, s.emp_id, s.frl_id, pl.state as lc_state 
                FROM sbr_stages_payouts sp 
                INNER JOIN sbr_stages ss ON ss.id = sp.stage_id
                INNER JOIN sbr s ON s.id = ss.sbr_id
                INNER JOIN pskb_lc pl ON pl.sbr_id = s.id 
                INNER JOIN users u ON u.uid = sp.user_id
                WHERE s.scheme_type = ?i AND sp.state IS NOT NULL AND sp.bank_completed IS NOT NULL AND sp.bank_completed::date >= '2012-10-01'::date AND sp.bank_completed::date <= '2012-10-03'::date;";

        $data = $db->rows($sql, sbr::SCHEME_LC);

        foreach($data as $row) {
            $pskb_lc = $pskb->_historyLC($row['lc_id'], sbr_stages::getOuterNum($row['sbr_id'], $row['num']), ( is_emp($row['role']) ? false : true ) );

            $sbr = new sbr($row['emp_id']);
            $stage = $sbr->getStage($row['stage_id']);
            $stage->payoutUpdateState($pskb_lc);
        }
     }

     
    public static function checkStagePayoputForSuperCheck($ids = array(), &$cntIn = 0, &$cntOut = 0) {
         $db = new DB('master');

        $sql = "SELECT sp.*, ss.sbr_id, ss.num, pl.lc_id, u.role, s.emp_id, s.frl_id, pl.state as lc_state 
                FROM sbr_stages_payouts sp 
                INNER JOIN sbr_stages ss ON ss.id = sp.stage_id
                INNER JOIN sbr s ON s.id = ss.sbr_id
                INNER JOIN pskb_lc pl ON pl.sbr_id = s.id
                INNER JOIN users u ON u.uid = sp.user_id";

        if ($ids) {
            $sql .= " WHERE sp.stage_id IN (?l)";
            $data = $db->rows($sql, $ids);
        } else {
            $sql .= " WHERE s.scheme_type = ?i 
                        AND sp.state = 'trans' 
                        AND ( sp.completed IS NULL OR (requested > NOW() - '" . self::PERIOD_BANK_CHECK . "'::interval AND credit_sys = ?i) )";
            $data = $db->rows($sql, sbr::SCHEME_LC, exrates::BANK);
        }

        if (!$data) {
            return false;
        }
        $cntIn = count($data);

        $list = array();
        foreach ($data as $lsrow) {
            $list[] = intval($lsrow['lc_id']);
        }

        $pskb = new pskb();
        $pskb->_SuperCheck($list); // ����� ����� ���
        return false;
    }
     
    /**
     * ����� �� ���� ��, �� ��� ��� ��� �������� � ���
     * 
     * @param type $lc_id       �� �����������
     * @param type $uid         �� ������ (9000-1) ��� 9000 - �� ��� 1 - ����� �����
     * @param type $target      true - ����������� false - ��������
     * @return boolean|\pskb_lc
     */
    public function getSuperCheckLocal($lc_id, $uid, $target) {
        if(!$lc_id) return new pskb_lc();
        if(!$uid) return new pskb_lc();
        
        $db  = new DB('master');
        if($target === null) {
            $sql = "SELECT * FROM pskb_lc_supercheck WHERE lc_id = ? AND uid = ? ORDER BY date_create DESC";
            $history = $db->rows($sql, $lc_id, $uid, $target);
            if(!$history) return new pskb_lc();
            foreach($history as $hist) {
                $hist['target'] = $hist['target'] == 't' ? true : false;
                $ret[] = new pskb_lc(json_encode($hist));
            }
        } else {
            $sql = "SELECT * FROM pskb_lc_supercheck WHERE lc_id = ? AND uid = ? AND target = ? ORDER BY date_create DESC LIMIT 1";
            $history = $db->row($sql, $lc_id, $uid, $target);
            if(!$history) return new pskb_lc();
            $history['target'] = $history['target'] == 't' ? true : false;
            $ret = new pskb_lc(json_encode($history));
        }
        
        return $ret;
    }
    
    /**
     * ������� �������� ������� ������������ ����� ������
     * 
     * @param array     $ids     �� ������
     * @param integer   $cntIn   ���������� �������������� ��������
     * @param integer   $cntOut  ���������� ������������ ��������
     * @return boolean 
     */
    public static function checkStagePayouts( $ids = array(), &$cntIn = 0, &$cntOut = 0) {
        $db  = new DB('master');
        
        $sql = "SELECT sp.*, ss.sbr_id, ss.num, pl.lc_id, u.role, s.emp_id, s.frl_id, pl.state as lc_state 
                FROM sbr_stages_payouts sp 
                INNER JOIN sbr_stages ss ON ss.id = sp.stage_id
                INNER JOIN sbr s ON s.id = ss.sbr_id
                INNER JOIN pskb_lc pl ON pl.sbr_id = s.id
                INNER JOIN users u ON u.uid = sp.user_id";
        
        if ($ids) {
            $sql .= " WHERE sp.stage_id IN (?l)";
            $data = $db->rows($sql, $ids);
        } else {
            $sql .= " WHERE s.scheme_type = ?i 
                        AND sp.state = 'trans' 
                        AND ( sp.completed IS NULL OR (requested > NOW() - '" . self::PERIOD_BANK_CHECK . "'::interval AND credit_sys = ?i) )";
            $data = $db->rows($sql, sbr::SCHEME_LC, exrates::BANK);
        }
        
        if (!$data) {
            return false;
        }
        $cntIn = count($data);
        
        $list = array();
        foreach ($data as $lsrow) {
            $list[] = intval($lsrow['lc_id']);
        }
        
        $pskb = new pskb();
        
        $resp = $pskb->_checks(json_encode(array('id' => $list)));
        foreach ($data as $row) {
            if(self::PSKB_SUPERCHECK) {
                $pskb_lc = $pskb->getSuperCheckLocal($row['lc_id'], sbr_stages::getOuterNum($row['sbr_id'], $row['num']), ( is_emp($row['role']) ? false : true ));
            } else {
                $pskb_lc = $pskb->_historyLC($row['lc_id'], sbr_stages::getOuterNum($row['sbr_id'], $row['num']), ( is_emp($row['role']) ? false : true ) );
            }
            if (!$pskb_lc->id) {
                continue;
            }
            
            if ($pskb_lc->state == $row['state']) {
                continue;
            }
            
            // ���� ������� ��� ������ �� ��� �� �������� ������ ������ � checks -- ����� ���������� ��� ��� ����� � ����
            if (!isset($resp[$row['lc_id']])) {
                continue;
            }
            
            $lc = $resp[$row['lc_id']];
            
            if (!$lc->id || $lc->state == $row['lc_state']) {
                continue;
            }
            
            switch ($pskb_lc->state) {
                case self::PAYOUT_END: // ���������� �� ����������
                case self::PAYOUT_ERR: // ��������� � ��
                    // ���������
                    $sbr = new sbr($row['emp_id']);
                    $stage = $sbr->getStage($row['stage_id']);
                    $stage->getArbitrage(false, false);
                    if($stage->arbitrage && $stage->arbitrage['resolved']) {
                        $emp_percent = abs(sbr::EMP - $stage->arbitrage['frl_percent']);
                        $frl_percent = abs(sbr::FRL - $stage->arbitrage['frl_percent']);
                    } else {
                        $frl_percent = 1;
                    }
                    
                    $frl_payout = true;
                    $emp_payout = true;
                    if($frl_percent > 0) {
                        $frl_payout = !$stage->payoutAgnt($row['frl_id'], new pskb($stage->sbr->data['id']), $pskb_lc);
                    }
                    if($emp_percent > 0) {
                        $emp_payout = !$stage->payoutAgnt($row['emp_id'], new pskb($stage->sbr->data['id']), $pskb_lc);
                    }
                    if (!$frl_payout && !$emp_payout) {
                        continue;
                    }
                    break;
            }
            
            switch ($lc->state) {
                case self::STATE_COVER:
                case self::STATE_END:
                    if ($lc->state == self::STATE_END) {
                        $up_data['ended'] = $pskb_lc->date;
                    }
                    break;
                case self::STATE_ERR:
                    break;
                default:
                    continue;
            }
            $up_data['state'] = $lc->state;
            $up_data['stateReason'] = $lc->stateReason;
            
            $pskb->upLC($up_data, $row['lc_id']);
            
            $cntOut++;
        }
    }
    
    /**
     * ��������� ������ ������ 
     */
    public static function checkStagePayoutsCompleted() {
        $db = new DB('master');
        
        $sql = "SELECT sp.*, ss.sbr_id, ss.num, pl.lc_id, u.role, s.emp_id, s.frl_id, pl.state as lc_state 
                FROM sbr_stages_payouts sp 
                INNER JOIN sbr_stages ss ON ss.id = sp.stage_id
                INNER JOIN sbr s ON s.id = ss.sbr_id
                INNER JOIN pskb_lc pl ON pl.sbr_id = s.id
                INNER JOIN users u ON u.uid = sp.user_id
                WHERE s.scheme_type = ?i AND sp.bank_completed IS NULL AND sp.completed IS NOT NULL";
        
        $data = $db->rows($sql, sbr::SCHEME_LC);
        
        $pskb = new pskb();
        
        foreach ($data as $row) {
            $pskb_lc = $pskb->_historyLC($row['lc_id'], sbr_stages::getOuterNum($row['sbr_id'], $row['num']), ( is_emp($row['role']) ? false : true ) );
            
            $sbr = new sbr($row['emp_id']);
            $stage = $sbr->getStage($row['stage_id']);
            $stage->payoutUpdateState($pskb_lc);
        }
    }
    
    /**
     * ��������� ���������� ����� ��������  
     */
    public static function checkBankCovered() {
        $db = new DB('master');
        $sql = "SELECT lc.*, s.emp_id, s.frl_id FROM pskb_lc lc 
                INNER JOIN sbr s ON s.id = lc.sbr_id 
                WHERE lc_id IS NOT NULL AND bank_covered IS NULL";
        $data = $db->rows($sql);
        
        $list = array();
        foreach ($data as $row) {
            $list[] = intval($row['lc_id']);
        }
        $pskb = new pskb();
        $resp = $pskb->_checks(json_encode(array('id' => $list)));
        
        foreach ($data as $row) {
            if (!$row['lc_id'] || !isset($resp[$row['lc_id']])) continue;
            $lc = $resp[$row['lc_id']];
            
            if($lc->cover) {
                $pskb->upLC(array('bank_covered' => $lc->cover), $row['lc_id']);
            }
        }
    }
    
    /**
     * ��������� ���������� ������������ � �������� trans.
     * ���� ������ ��������� �� cover ��� end, �������� ������� � ��������������� ������
     * 
     * @param type $cntIn       
     * @param type $cntOut
     * @return boolean
     */
    public static function checkPayouts ($ids = array(), &$cntIn = 0, &$cntOut = 0) {
        $db = new DB('master');
        $sql = "SELECT lc.*, s.emp_id, s.frl_id FROM pskb_lc lc
                INNER JOIN sbr s ON s.id = lc.sbr_id
                WHERE lc_id IS NOT NULL AND state IN ('trans') AND stage_id IS NOT NULL";
        if ($ids) {
            $sql .= " AND lc_id IN (?l) ";
            $data = $db->rows($sql, $ids);
        } else {
            $data = $db->rows($sql);
        }
        
        if (!$data) {
            return false;
        }
        
        $cntIn = count($data);
        
        $list = array();
        foreach ($data as $row) {
            $list[] = intval($row['lc_id']);
        }
        
        $pskb = new pskb();
        $resp = $pskb->_checks(json_encode(array('id' => $list)));
        
        foreach ($data as $row) {
            if (!$row['lc_id'] || !isset($resp[$row['lc_id']])) continue;
            $lc = $resp[$row['lc_id']];
            if ($lc->state == $row['state']) {
                continue;
            }
            
            if (!in_array($lc->state, array(self::STATE_COVER, self::STATE_END, self::STATE_ERR))) {
                continue;
            }
            
            switch ($lc->state) {
                case self::STATE_COVER:
                case self::STATE_END:
                    // ���������
                    $sbr = new sbr($row['emp_id']);
                    $stage = $sbr->getStage($row['stage_id']);
                    $stage->getArbitrage(false, false);
                    if($stage->arbitrage && $stage->arbitrage['resolved']) {
                        $emp_percent = abs(sbr::EMP - $stage->arbitrage['frl_percent']);
                        $frl_percent = abs(sbr::FRL - $stage->arbitrage['frl_percent']);
                    } else {
                        $frl_percent = 1;
                    }
                    
                    $frl_payout = true;
                    $emp_payout = true;
                    if($frl_percent > 0) {
                        $frl_payout = !$stage->payoutAgnt($row['frl_id'], new pskb($stage->sbr->data['id']));
                    }
                    if($emp_percent > 0) {
                        $emp_payout = !$stage->payoutAgnt($row['emp_id'], new pskb($stage->sbr->data['id']));
                    }
                    if (!$frl_payout && !$emp_payout) {
                        continue;
                    }
                    $up_data['executed'] = 'NOW()';
                    if ($lc->state == self::STATE_END) {
                        $up_data['ended'] = 'NOW()';
                    }
                    break;
                case self::STATE_ERR:
                    break;
            }
            $up_data['state'] = $lc->state;
            $up_data['stateReason'] = $lc->stateReason;
            $pskb->upLC($up_data, $row['lc_id']);
        }
    }
    
    /**
     * ��������� �����������, ���� ������� �������.
     * 
     * @param type $cntIn       
     * @param type $cntOut
     * @return boolean
     */
    public static function checkExpired ($ids = array(), &$cntIn = 0, &$cntOut = 0) {
        $db = new DB('master');
        $sql = 'SELECT lc.*, s.emp_id, s.frl_id FROM pskb_lc lc
                INNER JOIN sbr s ON s.id = lc.sbr_id
                WHERE lc_id IS NOT NULL 
                AND (
                        "dateCoverLC"::date <= COALESCE(covered::date, NOW()::date) OR 
                        "dateExecLC"::date <= NOW()::date OR 
                        "dateEndLC"::date <= NOW()::date
                )
                AND lc.state not in (\'end\', \'expEnd\', \'expCover\', \'expExec\')';
        
        if ($ids) {
            $sql .= " AND lc_id IN (?l) ";
            $data = $db->rows($sql, $ids);
        } else {
            $data = $db->rows($sql);
        }
        
        if (!$data) {
            return false;
        }
        
        $cntIn = count($data);
        
        $list = array();
        foreach ($data as $row) {
            $list[] = intval($row['lc_id']);
        }
        
        $pskb = new pskb();
        $resp = $pskb->_checks(json_encode(array('id' => $list)));
        
        foreach ($data as $row) {
            if (!$row['lc_id'] || !isset($resp[$row['lc_id']])) continue;
            $lc = $resp[$row['lc_id']];
            if (!in_array($lc->state, array(self::STATE_EXP_COVER, self::STATE_EXP_EXEC, self::STATE_EXP_END))) {
                continue;
            }
            $up_data['state'] = $lc->state;
            if (strlen($lc->stateReason)) {
                $up_data['stateReason'] = $lc->stateReason;
            }
            $pskb->upLC($up_data, $row['lc_id']);
        }
    }
    
    /**
     * ��������� ������ �� ����� ��� ���������������� ������ (�����/��������)
     */
    public function prolongPaused(&$cntIn = 0, &$cntOut = 0) {
        $db = new DB('master');
        $sql = "SELECT lc.* FROM sbr_stages st 
                INNER JOIN sbr s ON s.id = st.sbr_id
                INNER JOIN pskb_lc lc ON lc.sbr_id = s.id AND lc.lc_id IS NOT NULL
                LEFT JOIN sbr_events se ON se.sbr_id = s.id AND se.own_id = st.id AND ev_code = 14 AND se.version = st.version AND se.version <= st.frl_version
                LEFT JOIN sbr_versions sve ON sve.event_id = se.id AND sve.src_type_id = 6 AND sve.new_val = '2'
                WHERE st.status = 3 OR ( st.status = 2 AND sve.event_id IS NOT NULL )";
        
        $data = $db->rows($sql);
        
        if (!$data) {
            return false;
        }
        $cntIn = count($data);
        
        $list = array();
        foreach ($data as $row) {
            $list[] = intval($row['lc_id']);
        }
        
        $pskb = new pskb();
        $resp = $pskb->_checks(json_encode(array('id' => $list)));
        
        foreach ($data as $row) {
            if (!$row['lc_id'] || !isset($resp[$row['lc_id']])) continue;
            $lc = $resp[$row['lc_id']];
            if (preg_match('/^exp/', $lc->state) || in_array($lc->state, array(pskb::STATE_END, pskb::STATE_ERR))) {
                continue;
            }
            $pskb = new pskb();
            $pskb->_lc = $row;
            $res = $pskb->prolongLC($lc->id, 1);
        }
    }


    public function getLC ($force = false) {
        $sbr_id = $this->_sbr->data['id'];
        if (!$sbr_id) {
            return false;
        }
        
        if ($this->_lc && !$force) {
            return $this->_lc;
        }
        
        if (!$this->_lc || $force) {
            $sql = "SELECT * FROM pskb_lc WHERE sbr_id = ? LIMIT 1";
            $this->_lc = $this->_db->row($sql, $sbr_id);
        }
        
        return $this->_lc;
    }


    public function getLCbyId ($id) {
        $sql = "SELECT * FROM pskb_lc WHERE id = ? LIMIT 1";
        $this->_lc = $this->_db->row($sql, $id);
        
        return $this->_lc;
    }
    
     public function getLCbyLCId ($id) {
        $sql = "SELECT * FROM pskb_lc WHERE lc_id = ? LIMIT 1";
        $this->_lc = $this->_db->row($sql, $id);
        
        return $this->_lc;
    }
    
    public function getLcList($page, &$page_count) {
        $limit = self::ADM_PAGE_SIZE;
        $offset = ($page-1)*$limit;
        
        $sql = "SELECT lc.*, st.id as stage_id, st.num stage_num FROM sbr_stages st
                INNER JOIN pskb_lc lc ON lc.sbr_id = st.sbr_id
                INNER JOIN sbr s ON s.id = st.sbr_id
                ORDER BY lc.id DESC
                LIMIT ? OFFSET ?";
        
        $sql_cnt = "SELECT COUNT(*) cnt FROM sbr_stages st
                INNER JOIN pskb_lc lc ON lc.sbr_id = st.sbr_id";
        
        $page_count = $this->_db->cache(300)->col($sql_cnt);
        $page_count = $page_count[0];
        
        return $this->_db->rows($sql, $limit, $offset);
    }
    
    public function searchLC ($page, $request, &$page_count) {
        $limit = self::ADM_PAGE_SIZE;
        $offset = ($page-1)*$limit;
        $where = array();
        $orWhere = array();
        $params = array();
        
        $sql[] = 'SELECT lc.* FROM pskb_lc lc';
        $sql_cnt[] = 'SELECT COUNT(*) cnt FROM pskb_lc lc';
        $sql[] = $sql_cnt[] = 'INNER JOIN sbr s ON s.id = lc.sbr_id AND s.is_draft = FALSE';
        
        foreach ($request as $k => $v) {
            if ($v == 'null' || !$v) {
                continue;
            } 
            switch ($k) {
                case 'search':
                    $orWhere[] = 'lc.lc_id = ?';
                    $orWhereParams[] = intval($v);
                    
                    $orWhere[] = 'lc.sbr_id = ?';
                    $orWhereParams[] = intval($v);
                    break;
                case 'state':
                    $where[] = 'lc.state = ?';
                    $whereParams[] = $v;
                    break;
                case 'ps_emp':
                    $where[] = 'lc.ps_emp = ?';
                    $whereParams[] = $v;
                    break;
                case 'ps_frl':
                    $where[] = 'lc.ps_frl = ?';
                    $whereParams[] = $v;
                    break;
                case 'date_cover':
                    if($v['from']['year'] > 0) {
                        $date_cover_from = mktime(0,0,0, $v['from']['month'] ? (int) $v['from']['month'] : (int) $v['from']['month'] + 1, $v['from']['day'] ? (int) $v['from']['day'] : (int) $v['from']['day'] + 1, (int) $v['from']['year']);
                        $where[] = "to_char(lc.covered, 'YYYY-MM-DD') >= ?";
                        $whereParams[] = date('Y-m-d', $date_cover_from);
                    }
                    if($v['to']['year'] > 0) {
                        $date_cover_to = mktime(23,59,59, $v['to']['month'] ?  (int) $v['to']['month'] : 12, $v['to']['day'] ? (int) $v['to']['day'] : 30, (int) $v['to']['year']);
                        $where[] = "to_char(lc.covered, 'YYYY-MM-DD') <= ?";
                        $whereParams[] = date('Y-m-d', $date_cover_to);
                    }
                    break;
                case 'date_end':
                    if($v['from']['year'] > 0) {
                        $date_end_from = mktime(0,0,0, $v['from']['month'] ? (int) $v['from']['month'] : (int) $v['from']['month'] + 1, $v['from']['day'] ? (int) $v['from']['day'] : (int) $v['from']['day'] + 1, (int) $v['from']['year']);
                        $where[] =  "to_char(lc.ended, 'YYYY-MM-DD') >= ?";
                        $whereParams[] = date('Y-m-d', $date_end_from);
                    }
                    if($v['to']['year'] > 0) {
                        $date_end_to = mktime(23,59,59, $v['to']['month'] ? (int) $v['to']['month'] : 12, $v['to']['day'] ? (int) $v['to']['day'] : 30, (int) $v['to']['year']);
                        $where[] = "to_char(lc.ended, 'YYYY-MM-DD') <= ?";
                        $whereParams[] = date('Y-m-d', $date_end_to);
                    }
                    break;
            }
        }
        
        if (count($orWhere) || count($where)) {
            $sql_where[] = 'WHERE';
            if (count($where)) {
                $sql_where[] = implode(' AND ', $where);
                $params = array_merge($params, $whereParams);
            }
            
            if (count($orWhere)) {
                if (count($where)) {
                    $sql_where[] = 'AND';
                }
                $sql_where[] = '(' . implode(' OR ', $orWhere) . ')';
                $params = array_merge($params, $orWhereParams);
            }
            
            $sql[] = implode(' ', $sql_where);
            $sql_cnt[] = implode(' ', $sql_where);
        }
        
        $sql[] = 'ORDER BY lc.id DESC';
        
        $sql[] = 'LIMIT ? OFFSET ?';
        $params[] = $limit; $params[] = $offset;
        
        $sql_str = implode(' ', $sql);
        $sql_cnt_str = implode(' ', $sql_cnt);
        
        $sql_p = $params;
        array_unshift($sql_p, $sql_cnt_str);
        $page_count = call_user_method_array('row', $this->_db->cache(300), $sql_p);
        $page_count = $page_count['cnt'];
        
        array_unshift($params, $sql_str);
        $res = call_user_method_array('rows', $this->_db, $params);

        return $res;
    }
    
    
    /**
     * ������� ������ � ������� (������ ������� ���� ������ ���� � ���������)
     * 
     * @return boolean 
     */
    public function removeLC() {
        $sbr_id = $this->_sbr->data['id'];
        if (!$sbr_id) {
            return false;
        }
        $sql = "DELETE FROM pskb_lc WHERE sbr_id = ?";
        return $this->_db->query($sql, $sbr_id);
    }
    
    public function upLC ($up_data, $id, $id_fld = 'lc_id') {
        if ($this->_lc) {
            foreach ($up_data as $k => $v) {
                $this->_lc[$k] = $v;
            }
        }
        return $this->_db->update('pskb_lc', $up_data, $id_fld . ' = ?', $id);
    }
    
    
    protected function _getLCsByStatus ($status, $where = '') {
        $sql = "SELECT * FROM pskb_lc WHERE state = ? " . $where;
        return $this->_db->rows($sql, $status);
    }
    
    protected function _setLCCovered (pskb_lc $lc) {
        $data = array(
            'state' => $lc->state,
            'stateReason' => $lc->stateReason,
            'covered' => 'NOW()',
        );
        
        $res = $this->_db->update('pskb_lc', $data, 'lc_id = ?', $lc->id);
        
        return $res;
    }

    /**
     * ���������� ������ �����������
     * 
     * @param type $sum             �����
     * @param type $numDog          ����� ������ (����)
     * @param type $dateExecLC      ���� ������ ����������
     * @param type $dateEndLC       ���� ��������� LC
     * @param type $dateCoverLC     ���� �������� LC
     * @param type $tagCust         ������� ��������� ��. ����(�������� 1)
     * @param type $nameCust        ������������ ���������(��� ��� ������������ ��.����)
     * @param type $numCust         ����� �������� ���������
     * @param type $psCust          ������������ ��(��������� �������) ��������� (��� � ������ ������������ ������������)
     * @param type $accCust         �������(���������� ����) ��������� � ��
     * @param type $innCust         ��� ���������
     * @param type $kppCust         ��� ���������
     * @param type $tagPerf         ������� ����������� ��. ����(�������� 1)
     * @param type $namePerf        ������������ �����������(��� ��� ������������ ��.����)
     * @param type $numPerf         ����� �������� �����������
     * @param type $psPerf          ������������ ��(��������� ������� �����������)
     * @param type $accPerf         ������� ����������� � ��
     * @param type $innPerf         ��� �����������
     * @param type $kppPerf         ��� �����������
     * @param type $sumPlanned      ����� ��� ���������
     * @return pskb_lc              ��� �������� ���������� - ������� ���������� �������� LC � ��������� LC �� �������� new
     */
    protected function _addLC ($sum, $numDog, $dateExecLC, $dateEndLC, $dateCoverLC, 
        $tagCust, $alienCust, $nameCust, $numCust, $psCust, $accCust, $innCust, $nameBankCust, $swiftCust, $corAccbankCust, $emailCust, $cityBankCust, $cntrBankCust,  
        $tagPerf, $alienPerf, $namePerf, $numPerf, $psPerf, $accPerf, $innPerf, $nameBankPerf, $swiftPerf, $corAccbankPerf, $emailPerf, $cityBankPerf, $cntrBankPerf,
        $sumPlanned) {
        
        $numCust = pskb::phone($numCust);
        $numPerf = pskb::phone($numPerf);
        
        return new pskb_lc ($this->_request('addLC', array(
            'sum' => $sum,
            'sumPlanned' => $sumPlanned,
            'numDog' => $numDog,
            'dateExecLC' => $dateExecLC,
            'dateEndLC' => $dateEndLC,
            'dateCoverLC' => $dateCoverLC,
            
            'tagCust' => $tagCust,
            'alienCust' => $alienCust,
            'nameCust' => $nameCust,
            'numCust' => $numCust,
            'psCust' => $psCust,
            'accCust' => $accCust,
            'innCust' => $innCust,
            'nameBankCust' => $nameBankCust,
            'swiftCust' => $swiftCust,
            'corAccbankCust' => $corAccbankCust,
            'emailCust' => $emailCust,
            'cityBankCust' => $cityBankCust,
            'cntrBankCust' => $cntrBankCust,
            
            'tagPerf' => $tagPerf,
            'alienPerf' => $alienPerf,
            'namePerf' => $namePerf,
            'numPerf' => $numPerf,
            'psPerf' => $psPerf,
            'accPerf' => $accPerf,
            'innPerf' => $innPerf,
            'nameBankPerf' => $nameBankPerf,
            'swiftPerf' => $swiftPerf,
            'corAccbankPerf' => $corAccbankPerf,
            'emailPerf' => $emailPerf,
            'cityBankPerf' => $cityBankPerf,
            'cntrBankPerf' => $cntrBankPerf,
        )));
    }
    
    public function checkLC ($id) {
        return $this->_checkLC($id);
    }
    /**
     * �������� �����������
     * 
     * @param type $id
     * @return pskb_lc
     */
    protected function _checkLC ($id) {
        return new pskb_lc ($this->_request('checkLC', array(
            'id' => $id
        )));
    }
    
    /**
     * �������� ������� �������� �� ������ ��������.
     * ���� �������� ��� - �� ����� ������, � �� ������� ������ ������� ��� ��� ���������
     * 
     * @param  string    $num   ����� �������� � ������� +79213247716
     * @return pskb_lc
     */
    protected function _checkOrCreateWallet ($num) {
        $this->_useAlternativeLog = false;
        return $this->_request('checkOrCreateWallet', array(
            'num' => $num
        ));
        $this->_useAlternativeLog = true;
    }
    
    /**
     * ��������� ����� ��� self::_checkOrCreateWallet()
     * 
     * @param  string    $num   ����� �������� � ������� +79213247716
     * @return pskb_lc
     */
    public function checkOrCreateWallet ($num) {
        return $this->_checkOrCreateWallet($num);
    }
    
    /**
     * ���������� ����������� �� id
     * 
     * @param string $ids     json ������ � ������� {"id": [1,2,...n]}    
     * @return array
     */
    public function _checks ($ids) {
        $res = $this->_request('checks', $ids, true);
//        var_dump($res);
        $res = json_decode($res, 1);
        $out = array();
        if(!$res) return array();
        foreach ($res as $row) {
            $accr = new pskb_lc($row);
            $out[$accr->id] = $accr;
        }
        return $out;
    }

    /**
     * ��������� ����� LC
     * 
     * @param type $id          �� �����������
     * @param type $dateExecLC      ���� ������ ����������
     * @param type $dateEndLC       ���� ��������� LC
     * @param type $dateCoverLC     ���� �������� LC
     * @return pskb_lc 
     */
    protected function _changeDateLC ($id, $dateExecLC, $dateEndLC, $dateCoverLC) {
        return new pskb_lc ($this->_request('changeDateLC', array(
            'id' => $id,
            'dateExecLC' => $dateExecLC,
            'dateEndLC' => $dateEndLC,
            'dateCoverLC' => $dateCoverLC,
        )));
    }

    /**
     * ��������� LC
     * 
     * @param type $ID          �� �����������
     * @param type $sum         ����� LC �� ��������� (������������� ���������)
     * @param type $sumOpen     ����� LC ���������(������������� �����������)
     * @param type $numCust     ����� �������� �����������
     * @param type $dateAct     ���� ���������� ���� � ����������� ������
     * @param type $idAct       ���������� ������������� ���� � ����������� ������
     * @return pskb_lc 
     */
    protected function _openLC ($ID, $sumCust, $sumPerf, $dateAct, $idAct) {
        return new pskb_lc ($this->_request('openLC', array(
            'id' => $ID,
            'sumCust' => $sumCust,
            'sumPerf' => $sumPerf,
            'dateAct' => $dateAct,
            'idAct' => $idAct
        )));
    }

    /**
     * ���������� ���� ������������ � ������ � ��������� � ������ � �������� LC
     * 
     * @param type $ID          �� �����������
     * @param type $asp         ��� ��������� ������������� � ������ �������� FL
     * @return pskb_lc 
     */
    protected function _subOpenLC ($ID, $asp) {
        return new pskb_lc ($this->_request('subOpenLC', array(
            'id' => $ID,
            'asp' => $asp,
        )));
    }

    /**
     * ��������� ������������ ������� LC
     * ��������� ������ ��� LC � ������� err.
     * 
     * @param type $id          �� �����������
     * @return pskb_lc 
     */
    protected function _transLC ($id) {
        return new pskb_lc ($this->_request('transLC', array(
            'id' => $id
        )));
    }
    
    /**
     * ������ SMS ���� ��� ���� �� LC
     * 
     * @param type $id          �� �����������
     * @param type $num         ����� ��� ���
     * @return pskb_lc
     */
    protected function _reqCode ($id) {
        return new pskb_lc ($this->_request('reqCode', array(
            'id' => $id,
        )));
    }
    
    /**
     * �������� LC
     * 
     * @param type $id          �� �����������
     * @param type $num         ����� ��� ���
     * @return pskb_lc
     */
    protected function _closeLC ($id) {
        return new pskb_lc ($this->_request('closeLC', array(
            'id' => $id,
        )));
    }
    
    public function closeLC($id) {
        return $this->_closeLC($id);
    }
    
    public function getHistoryLC($id, $uid = null, $target = null) {
        if(self::PSKB_SUPERCHECK) {
            return $this->getSuperCheckLocal($id, $uid, $target);
        } else {
            return $this->_historyLC($id, $uid, $target);
        }
    }
    
    /**
     * ������� ��� ���������� ���������
     * 
     * @global type $DB
     * @return type
     */
    public static function fillingSuperCheck() {
       global $DB;
       if(!is_release()) return true;
       
       $sql = "SELECT lc_id, ( ss.sbr_id || '-' || (ss.num + 1) )::text as uid,  (CASE WHEN s.emp_id = ssp.user_id THEN false ELSE true END ) as target
               FROM sbr_stages_payouts ssp
               INNER JOIN sbr_stages ss ON ss.id  = ssp.stage_id
               INNER JOIN sbr s ON s.id = ss.sbr_id
               INNER JOIN pskb_lc p ON p.sbr_id = ss.sbr_id
               WHERE requested::date >= '2012-11-01' AND requested::date <= '2012-12-19'
                EXCEPT
               SELECT lc_id, uid, target FROM pskb_lc_supercheck
               ORDER BY uid
               LIMIT 200;";
       
       $lc_ids = $DB->col($sql);
       $pskb = new pskb();
       return $pskb->getSuperCheck($lc_ids);
    }
    
    public function getSuperCheck($ids, $url_rejoin = false) {
        return $this->_SuperCheck($ids, $url_rejoin);
    }
     
    protected function _SuperCheck($ids, $url_rejoin = false) {
        if(!$url_rejoin) {
            $_host = !defined('IS_LOCAL') ? str_replace('http://', 'https://', $GLOBALS['host']) : $GLOBALS['host'];
            $url_rejoin = $_host . ( defined('PSKB_TEST_MODE') ? pskb::URL_REJOIN_DEBUG : pskb::URL_REJOIN);
        } else {
            $_host = !defined('IS_LOCAL') ? str_replace('http://', 'https://', $GLOBALS['host']) : $GLOBALS['host'];
            $url_rejoin = $_host . $url_rejoin;
        }
        
        $url_rejoin .= "?key=".pskb::KEY_CHECK_AUTH; // ����������� ��  ����� �������
        
        // ��� ��� � ��������� ����� �� �������� ������ � 4000 �������� ����� �������� �� �����
        $parts_ids = array_chunk($ids, pskb::SUPERCHECK_LIMIT_ID_REQUEST);
        foreach($parts_ids as $lc_ids) {
            $req = array(
                'id'        => implode(',', $lc_ids),
                'urlRejoin' => $url_rejoin
            );
            $send = $this->_request('superCheck', $req);
        }
        
        return $send;
    }
    
    /**
     * ���������� ������� � ��������� ������ �� LC, ���� �� ����� �������
     * 
     * @param type $id          �� �����������
     * @param type $uid         ����� ��������, ���������� � openLC
     * @param type $target      ����������� ������� (false - ���������, true - �����������)
     */
    protected function _historyLC($id, $uid = null, $target = null) {
        $req['id'] = $id;
        if($uid !== null) $req['uid'] = $uid;
        if($target !== null) $req['target'] = $target ? 'true' : 'false';
        $res = $this->_request('historyLC', $req, false, array('method' => 'GET'));
        $out = json_decode($res);
        if(is_array($out)) {
            $out = json_decode($res, 1);
            foreach($out as $k=>$json) {
                $out[$k] = new pskb_lc ($json);
            }
            return $out;
        } else {
            return new pskb_lc ($res);
        }
    }

    protected function _request ($method, $params, $content_plain = false, $query_params = array('method' => 'POST')) {        
        $ch = curl_init();
        $str_method = strtolower($method);
        $cp1251_params = $params;
        if (is_array($params)) {
            foreach ($params as $k => $v) {
                $params[$k] = $this->_enc($v);
            }
        } else {
            $params = $this->_enc($params);
        }
        
        if($query_params['method'] == 'GET') {
            $method .=  ( defined('PSKB_TEST_MODE') ? "&" : "?" ) . http_build_query($params);
        }
        
        if(defined('PSKB_BETA_MODE')) {
            curl_setopt($ch, CURLOPT_PORT, 8085);
            curl_setopt($ch, CURLOPT_URL, "http://localhost/apiLCPlace/" . $method);
        } else if (!defined('PSKB_TEST_MODE')) {
            curl_setopt($ch, CURLOPT_PORT, 8085);
            curl_setopt($ch, CURLOPT_URL, 'http://192.168.88.13/apiLCPlace/' . $method);
//            curl_setopt($ch, CURLOPT_URL, "http://localhost/apiLCPlace/" . $method);
        } else {
            if(defined('BASIC_AUTH')) {
                curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
            }
            curl_setopt($ch, CURLOPT_URL, $this->_request_url . $method);
        }
        
        if($query_params['method'] == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if ($content_plain) {
            curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain')); 
        }
        $res = curl_exec($ch);
        
        $log = new log("pskb/{$str_method}-".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        if($this->_useAlternativeLog) {
            $log->addAlternativeMethodSave(new log_pskb(), true);
            $alt_out = array(
                'request_url' => ( $this->_request_url . $method ),
                'param'       => $cp1251_params,
                'response'    => iconv('utf8', 'cp1251', $res)
            );
            $log->setAlternativeWrite(serialize($alt_out), 'log_pskb');
        }
        ob_start();
        var_dump($this->_request_url . $method);
        var_dump($params);
        var_dump($res);
        $out = ob_get_clean();
        $log->writeln(iconv('utf8', 'cp1251', $out));
        
        return $res;
    }
    
    private function _enc($str) {
        return iconv('cp1251', 'utf8', $str);
    }
    
    private function _prepareError($msg) {
        $code_err = intval(trim(preg_replace("/\W+/mix", "", $msg)));
        
        if ($code_err != 600) {
            $msg = self::$psys_error_codes[$code_err];
        } else {
            $msg = preg_replace('~^[\d -]*~', '', trim($msg));
        }
        return $msg;
    }
    /**
     * ���������� ������� ������
     * @param string  $msg              ����� ������
     * @param boolean $prepareError     ��������� ������ ����� �������
     */
    private function _setError( $msg , $prepareError = false) {
        if (!trim($msg)) {
            $msg = '������ �������� �� ��������.';
        }
        if($prepareError) {
            $msg = $this->_prepareError($msg);
        }
        
        $this->_messages = $msg;
    }
    
    public function getError() {
        if (!$this->_messages) {
            return false;
        }
        return $this->_messages;
    }
    
    /**
     * 
     * @param type $force
     * @return \pskb_user
     */
    private function _getEmp($force = false) {
        if ($this->_emp && !$force) {
            return $this->_emp;
        }
        
        return new pskb_user($this->_sbr->getEmpReqvs(), 1);
    }
    
    /**
     * 
     * @param type $force
     * @return \pskb_user
     */
    private function _getFrl($force = false) {
        if ($this->_frl && !$force) {
            return $this->_frl;
        }
        
        return new pskb_user($this->_sbr->getFrlReqvs(), 0);
    }
    
    public static function phone ($str) {
        $str = trim($str);
        if (!$str) {
            return $str;
        }
        $str = preg_replace('/\D+/s', '', $str);
        $str = str_replace('+', '', $str);
        $str = '+' . $str;
        
        return $str;
    }
    
    public function getSmsCode($id) {
        $sql = "SELECT sms FROM pskb_lc_test WHERE id = ?";
        return $this->_db->col($sql, $id);
    }
    
    /**
     * ����� ������ ������������ �����, ��� ���������� � ���������� �� ����������� ������������
     * 
     * @param integer $rez_type    ��� ������������ ������������ @see sbr::RT_*
     * @return array
     */
    public static function getReqvsFields($rez_type = null) {
        if(!$rez_type) $rez_type = sbr::RT_RU;
        if($rez_type == sbr::RT_UABYKZ) {
            return self::$reqvs_fields_rez;
        } else {
            return self::$reqvs_fields;
        }
    }
    
    public static function getRequiredFieldsForType($reqvs_fields, $type, $form_type) {
        foreach($reqvs_fields as $name=>$data) {
            if( (in_array('all', $data[1]) || in_array($type, $data[1])) &&
                (in_array('all', $data[2]) || in_array($form_type, $data[2])) ) {
                $_req[$name] = true;
            }
        }
        
        if($_req) return $_req;
        
        return null;
    }
    
    /**
     * ��������� ��������� ��� ��� ���������� ������ � ����� ������
     * 
     * @param string $reqv �������� (���������� ����, ����� ��������)
     * @return string
     */
    public static function preparePayedReqvs($reqv) {
        if($reqv == '') return '';
        return substr($reqv, 0, 4) . "..." . substr($reqv, -4);
    }
    
    /**
     * ���������� ������ ��� ������ ����������
     * 
     * @param array  $lc    ������ �� �����������
     * @param string $type  ��� ������������ �� �������� ���������� ���������
     * @return type 
     */
    public static function getPayedReqvs($lc, $type = 'emp') {
        if(!$lc) return array();
        $lc_type = $type == 'emp' ? 'Cust' : 'Perf';
        
        switch($lc['ps_'.$type]) {
            case onlinedengi::BANK_FL:
            case onlinedengi::BANK_YL:
                if($lc['nameBank'.$lc_type]) {
                    $reqv['����:'] = $lc['nameBank'.$lc_type];
                }
                $reqv['����:'] = self::preparePayedReqvs($lc['acc'.$lc_type]);
                break;
            case 2: // ������ �� ��� 
            case onlinedengi::WMR:
            case onlinedengi::YD:
                $reqv['�������:'] = self::preparePayedReqvs($lc['acc'.$lc_type]);
                break;
            case onlinedengi::CARD:
                $reqv['�����:'] = self::preparePayedReqvs($lc['acc'.$lc_type]);
                break;
            case self::WW:
                $reqv['�������:'] = self::preparePayedReqvs($lc['num'.$lc_type]);
                break;
            default:
                $reqv['����:'] = self::preparePayedReqvs($lc['acc'.$lc_type]);
        }
        
        return $reqv;
    }
    
    public static function listenRequest($src) {
        global $DB;
        
        $log = new log("pskb_listen/{$src}-".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        ob_start();
        var_dump(file_get_contents('php://input'));
        $out = ob_get_clean();
        $log->writeln(iconv('utf8', 'cp1251', $out));
                
        switch($src) {
            case 'superCheck':
                $request = json_decode(file_get_contents('php://input'));
                if($request) {
                    $pskb = new pskb();
                    foreach($request as $k=>$pskb_req) {
                        $pskb_req = new pskb_lc(json_encode($pskb_req));
                        
                        $row = $pskb->getLCbyLCId($pskb_req->id);
                        if($pskb_req->history) {
                            foreach($pskb_req->history as $pskb_lc) {
                                
                                if (!$pskb_lc->id) {
                                    continue;
                                }
                                
                                // �������� ���������� uid ���� �� ������, �������� ������ ��� ����������� ������
                                // @todo � ������������� ���� ���-�� ��������� ����
                                if($pskb_lc->uid == '' || $pskb_lc->uid == null) {
                                    $sql = "SELECT s.id FROM pskb_lc pl
                                            INNER JOIN sbr s ON s.id = pl.sbr_id
                                            WHERE pl.lc_id = ? AND stages_cnt = 1"; // ������ ������ ���-�� ����� ��������� ��� ���� � ������ ���� ������������
                                    $sbr_id = $DB->val($sql, $pskb_req->id);
                                    if($sbr_id > 0) {
                                        $pskb_lc->uid = "{$sbr_id}-1";
                                    }
                                }
                                
                                if($DB->val("SELECT id FROM pskb_lc_supercheck WHERE lc_id = ? AND uid = ? AND state = ? AND date = ? AND target = ?b", 
                                            $pskb_req->id, "{$pskb_lc->uid}", $pskb_lc->state, $pskb_lc->date, $pskb_lc->target ? true : false) > 0) {
                                    continue; // � ���� ��� ���� ���������� ������
                                }
                                
                                $insert = array(
                                    'lc_id'   => $pskb_req->id,
                                    'state'   => $pskb_lc->state,
                                    'date'    => date('c', strtotime($pskb_lc->date)),
                                    'uid'     => "{$pskb_lc->uid}",
                                    'target'  => $pskb_lc->target ? true : false,
                                    'sum'     => $pskb_lc->sum,
                                    'account' => $pskb_lc->account,
                                    'ps'      => $pskb_lc->ps 
                                );
                                
                                $DB->insert('pskb_lc_supercheck', $insert);
                                continue;
                                /**
                                 * @deprecated ���� ��� ��� ���� �������� �� ������ ������ ����� � ���� ������� ������� ����������
                                 */
                                $stg = sbr_meta::getStatePayout(explode('-', $pskb_lc->uid));
                                if ($pskb_lc->state == $stg['state']) {
                                    continue;
                                }
                                
                                switch ($pskb_lc->state) {
                                    case self::PAYOUT_END: // ���������� �� ����������
                                    case self::PAYOUT_ERR: // ��������� � ��
                                        // ���������
                                        $sbr = new sbr($stg['emp_id']);
                                        $stage = $sbr->getStage($stg['stage_id']);
                                        $stage->getArbitrage(false, false);
                                        if($stage->arbitrage && $stage->arbitrage['resolved']) {
                                            $emp_percent = abs(sbr::EMP - $stage->arbitrage['frl_percent']);
                                            $frl_percent = abs(sbr::FRL - $stage->arbitrage['frl_percent']);
                                        } else {
                                            $frl_percent = 1;
                                        }

                                        $frl_payout = true;
                                        $emp_payout = true;
                                        if($frl_percent > 0) {
                                            $frl_payout = !$stage->payoutAgnt($stg['frl_id'], new pskb($stage->sbr->data['id']), $pskb_lc);
                                        }
                                        if($emp_percent > 0) {
                                            $emp_payout = !$stage->payoutAgnt($stg['emp_id'], new pskb($stage->sbr->data['id']), $pskb_lc);
                                        }
                                        if (!$frl_payout && !$emp_payout) {
                                            continue;
                                        }
                                        break;
                                }

                                $lc = $pskb_req;

                                if (!$lc->id || $lc->state == $row['state']) {
                                    continue;
                                }

                                switch ($lc->state) {
                                    case self::STATE_COVER:
                                    case self::STATE_END:
                                        if ($lc->state == self::STATE_END) {
                                            $up_data['ended'] = $pskb_lc->date;
                                        }
                                        break;
                                    case self::STATE_ERR:
                                        break;
                                    default:
                                        continue;
                                }
                                $up_data['state'] = $lc->state;
                                $up_data['stateReason'] = $lc->stateReason;

                                $pskb->upLC($up_data, $row['lc_id']);
                            }
                        }
                    }
                }
                break;
            default:
                break;
        }
        return true;
    }
    
    public static function getCardsFrameUrl() {
        return !defined('PSKB_TEST_MODE') ? self::URL_CARD_FRAME : self::URL_CARD_FRAME_TEST;
    }
    
    public static function signCardRequest($params) {
        return md5(
            (isset($params['service']) ? $params['service'] : '') .
            (isset($params['account']) ? intval($params['account']) : '') .
            (isset($params['amount']) ? $params['amount'] : '') .
            (isset($params['state']) ? intval($params['state']) : '') .
            self::SECRET_CARDS
        );
    }
    
    public static function validateCardRequest($params) {
        if (!isset($params['account'])) {
            return false;
        }
        return $params['sign'] == self::signCardRequest($params);
    }
    
    public static function getNonceSign($lc_id) {
        $nonce = md5(time() . mt_rand(1000, 999999));
        $nonce = substr($nonce, mt_rand(1, 10), mt_rand(8, 15));
        
        return $nonce . md5($lc_id . self::SECRET_CARDS . $nonce);
    }
}


/**
 * �������� ��������� ������� �����������, ������������ ��� ����
 * 
 * id, stateReason � ��������� � ������� ���-�������� ����(����� WW).
 * sum ������������� ����� �������� ������������ �����������(�������� �������� ���������)
 * sumOpen ������������� ����� �������� ������� ������������� �����������.
 */
class pskb_lc {

    /**
     * ���������� ������������� LC � ��������
     * 
     * @var int 
     */
    public $id;
    
    /**
     * ����� ������� ���������� �� FL
     * 
     * @var int 
     */
    public $numDog;
    
    /**
     * ������ LC
     * 
     * LC ����� ��������� � ����� �� ��������� ���������:
     * new -    �����
     * cover -  ������
     * exp -    ���������(� ��������� ������� expCover, expExec, expEnd)
     * end -    ��������
     * passed - �������
     * ret -    �����
     * err -    ������
     * 
     * @var string 
     */
    public $state;
    
    /**
     * ����� LC �� ���������
     * 
     * @var type 
     */
    public $sum;
    
    /**
     * ����� LC ���������
     * 
     * @var float 
     */
    public $sumOpen;
    
    /**
     * ����������� �������
     * 
     * @var float 
     */
    public $stateReason;
    
    /**
     * ���������� ���� ��������
     * 
     * @var string 
     */
    public $cover;
    
    /**
     * ���������� ���� ������� �����������
     * 
     * @var string
     */
    public $date;
    
    /**
     * ��� ��������� ����������
     */
    public $sms;
    public $dateExecLC;
    public $dateEndLC;
    public $dateCoverLC;
    public $account;
    public $uid;
    public $target;
    public $history;
    public $ps;

    public function __construct($json = null) {
        if ($json) {
            if (!is_array($json)) {
                $json = json_decode($json, true);
            }
            if (!$json) {
                $json = array();
            }
            foreach ($json as $k => $v) {
                if($v == null) continue;
                if($k == 'history') {
                    foreach($v  as $hist) {
                        $this->history[] =  new pskb_lc(json_encode($hist));
                    }
                } else {
                    $this->$k = iconv('utf8', 'cp1251', $v);
                }
            }
        }
    }
    
    public function __set($name, $value) {}
    public function __get($name) {}
}


class pskb_user {
    
    const PSYS_ERR_DISABLED = 1;
    const PSYS_ERR_HIDDEN = 2;
    const PSYS_ERR_BOTH = 3;
    
    private $_params = array(
        'tag' =>    null,
        'alien' =>  null,
        'name' =>   null,
        'num' =>    null,
        'ps' =>     null,
        'acc' =>    null,
        'inn' =>    null,
        'nameBank' =>    null,
        'swift' =>    null,
        'cityBank'  => null,
        'cntrBank'  => null,
        'corAccbank' =>    null,
        'email' => null
    );
    
    private $_is_emp;
    
    public $__reqvs;
    
    public $_reqvs;
    
    /**
     * ��� ������������ ������������ @see sbr::RT_*
     * 
     * @var integer 
     */
    private $_rez_type;

    private $_sum;
    
    private $_only_ww;
    
    private $_user;
    
    public function __construct ($reqvs, $is_emp, $sum = null) {
        $user = new users();
        $user->GetUserByUID((int)$reqvs['user_id']);
        $this->_user = $user;
        $this->_is_emp = intval($is_emp);
        $this->__reqvs = $reqvs;
        $this->_rez_type = $reqvs['rez_type'];
        $reqvs = $reqvs[intval($reqvs['form_type'] ? $reqvs['form_type'] : sbr::FT_PHYS)];
        $this->_reqvs = $reqvs;
        $this->_sum = $sum;
    }
    
    public function setOnlyWW($status) {
        $this->_only_ww = $status;
    }

    public function setPs ($ps) {
        $check = $this->checkPsys($ps, ($this->__reqvs['form_type'] == 2 ? sbr::FT_JURI : sbr::FT_PHYS ), true, $this->_sum);
        
        if ($check) {
            return false;
        }
    }


    /**
     * ��������� ������ ��������� ������, ��������� ������������
     * 
     * @param int $tag      ������� ���/������
     */
    public function getPsystems () {
        $pslist = pskb::$psys[$this->_is_emp];
        $out['list'] = $pslist;
        $out['disabled'] = array();
        $out['err'] = array();
        
        foreach (array(sbr::FT_PHYS, sbr::FT_JURI) as $form_type) {
            $_disabled = array();
            $_hidden = array();
            
            foreach ($pslist as $k => $v) {
                $check = $this->checkPsys($k, $form_type);
                if ($check === self::PSYS_ERR_DISABLED) {
                    $_disabled[] = $k;
                }
                if ($check === self::PSYS_ERR_HIDDEN) {
                    $_hidden[] = $k;
                }
                if ($check === self::PSYS_ERR_BOTH) {
                    $_disabled[] = $k;
                    $_hidden[] = $k;
                }
            }
            
            $out['disabled'][$form_type] = array_values(array_diff($_disabled, $_hidden));
            $out['hidden'][$form_type] = $_hidden;
        }
        
        return $out;
    }
    
    public function checkPsys ($psys, $form_type = null, $set_vars = false) {
        $_disabled = $_hidden = null;
        $form_type = $form_type ? $form_type : $this->__reqvs['form_type'];
        
        if (!$form_type) {
            return self::PSYS_ERR_BOTH;
        }
        
        $reqvs = $this->__reqvs[$form_type];
        
        if (!$reqvs['mob_phone']) {
            $_disabled = $psys;
        }
        
        if ($form_type == sbr::FT_PHYS && !$reqvs['fio']) {
            $_disabled = $psys;
        }
        
        switch ($psys) {
            case onlinedengi::BANK_YL:
                if ($form_type == sbr::FT_JURI) {
                    if ( (!$reqvs['full_name'] || !$reqvs['bank_rs'] || !$reqvs['bank_bik'] || !$reqvs['inn'] || !$reqvs['address_jry']) && $this->_rez_type == sbr::RT_RU ) {
                        $_disabled = $psys;
                    }
                    // ��� ������������ ������ ���� ������ �������� ����������
                    if($this->_rez_type == sbr::RT_UABYKZ) {
                        $_req = pskb::getRequiredFieldsForType(pskb::$reqvs_fields_rez, onlinedengi::BANK_YL, sbr::FT_JURI);
                        if($_req) {
                            foreach($reqvs as $name_field=>$value) {
                                if($_req[$name_field] && $value == '') {
                                    $_disabled = $psys;
                                    break; // OR
                                }
                            }
                        }
                    }
                } else {
                    if ($this->_is_emp) $_hidden = $psys;
                    if ( (!$reqvs['fio'] || !$reqvs['bank_rs'] || !$reqvs['bank_bik']) && $this->_rez_type == sbr::RT_RU ) {
                        $_disabled = $psys;
                    }
                    // ��� ������������ ������ ���� ������ �������� ����������
                    if($this->_rez_type == sbr::RT_UABYKZ) {
                        $_req = pskb::getRequiredFieldsForType(pskb::$reqvs_fields_rez, onlinedengi::BANK_YL, sbr::FT_PHYS);
                        if($_req) {
                            foreach($reqvs as $name_field=>$value) {
                                if($_req[$name_field] && $value == '') {
                                    $_disabled = $psys;
                                    break; // OR
                                }
                            }
                        }
                    }
                }
                break;
            case onlinedengi::CARD:
                if ($form_type == sbr::FT_JURI) {
                    $_hidden = $psys;
                }
                break;
            case onlinedengi::WMR:
                if (!$reqvs['el_wmr']) {
                    $_disabled = $psys;
                }
                if ($form_type == sbr::FT_JURI) {
                    $_hidden = $psys;
                }
                break;
            case onlinedengi::YD:
                if (!$reqvs['el_yd']) {
                    $_disabled = $psys;
                }
                if ($form_type == sbr::FT_JURI || ($this->_sum > pskb::MAX_YD_SUM && !$this->_is_emp)) {
                    $_hidden = $psys;
                }
                break;
            case pskb::WW:
                if ($form_type == sbr::FT_JURI) {
                    $_hidden = $psys;
                }
                break;
            default: 
                return false;
        }
        
        if ($this->_sum && $form_type == sbr::FT_PHYS && $this->_sum <= pskb::WW_ONLY_SUM && $psys != pskb::WW && !$this->_is_emp) {
            $_disabled = $psys;
            $_hidden = $psys;
        }
        // ��� �������� (������ ������������: ����� ���� 3 ����� �� 10� �� ���� ����� ������� �� ����� ���� �� ���-�������)
        // ���� �������� _only_ww - �������������� ������� ������ ����� $sbr->stages
        if($this->_only_ww && $psys != pskb::WW && !$this->_is_emp && $form_type == sbr::FT_PHYS) {
            $_disabled = $psys;
            $_hidden = $psys;
        }
        
        $_disabled = $_disabled ? self::PSYS_ERR_DISABLED : 0;
        $_hidden = $_hidden ? self::PSYS_ERR_HIDDEN : 0;
        $res = (intval($_disabled) + intval($_hidden));
        $rez_type = intval($this->__reqvs['rez_type']) == sbr::RT_RU || intval($this->__reqvs['rez_type']) == 0 ? 0 : 1;

        if ($set_vars && !$res) {
            $this->tag = 0;
            $this->alien = $rez_type;
            $this->num = $reqvs['mob_phone'];
            $this->email = $this->_user->email; // E-mail � �������
            $this->name = $reqvs['fio'];
            $this->ps = $psys;
            
            if ($psys == onlinedengi::BANK_YL) {
                $this->ps = $reqvs['bank_bik'];
                $this->acc = $reqvs['bank_rs'];
                $this->inn = $reqvs['inn'];
                
                if ($rez_type == 1) {
                    $this->inn = $reqvs['rnn'];
                    $this->ps = $reqvs['bank_rf_bik'];
                    $this->nameBank = $reqvs['bank_name'];
                    $this->cityBank  = $reqvs['bank_city'];
                    $this->cntrBank  = $reqvs['bank_country'];
                    $this->swift = $reqvs['bank_swift'];
                    $this->corAccbank = $reqvs['bank_ks'];
                }
            }
            
            if ($form_type == sbr::FT_JURI) {
                $this->tag = 1;
                $this->name = $reqvs['full_name'];
            }
            
            if ($psys == onlinedengi::WMR) {
                $this->acc = $reqvs['el_wmr'];
            }
            
            if ($psys == onlinedengi::YD) {
                $this->acc = $reqvs['el_yd'];
            }
            
            if ($psys == pskb::WW) {
                $this->acc = $reqvs['mob_phone'];
            }
            
            if ($psys == onlinedengi::CARD) {
                $this->ps = pskb::CARD;
            }
            
            if ($psys == onlinedengi::WMR) {
                $this->ps = pskb::WMR;
            }
        }
        
        return $res;
    }
    
    public function getPsystemsForm ($sbr, $isReqvsFilled) {
        ob_start();
        $paysystems = $this->getPsystems();
        
        include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.pskb-psys.php");
        $out = ob_get_clean();
        
        return $out;
    }

    public function getParams() {
        return $this->_params;
    }
    
    public function __set($name, $value) {
        if (!array_key_exists($name, $this->_params)) {
            return;
        }
        
        $this->_params[$name] = $value;
    }
    
    public function __get($name) {
        if (!array_key_exists($name, $this->_params)) {
            return null;
        }
        
        return $this->_params[$name];
    }
}


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");

/**
 * ������ ���������� ������� ������� ���� =)
 */
class pskb_server {
    
    const MEMB_PFX = '_pskb_lc_store_';
    
    /**
     *
     * @var DB 
     */
    private $_db;
    
    public function __construct() {
        $this->_db = new DB('master');
    }
    
    public function serve($method, $params = array()) {
        
        switch ($method) {
            
            case 'checkOrCreateWallet':
                
                $num = $params['num'];
                
                //��������
                $test['+71234567890'] = array(
                    "state" => "EXIST",
                    "message" => "��� ������ +71234567890",
                    "fio" => "������ ����� ������������",
                    "verified" => TRUE,
                    "identified" => false
                );
                
                $test['+79272540217'] = array(
                    "state" => "EXIST",
                    "message" => "C����� +79272540217",
                    "fio" => "������ ����� ������������",
                    "verified" => TRUE,
                    "identified" => TRUE
                );
                
                $test['+380664848120'] = array(
                    "state" => "EXIST",
                    "message" => "��� ������ +380664848120",
                    "fio" => "������ ����� ������������",
                    "verified" => FALSE,
                    "identified" => TRUE
                );
                
                $test['+79034731235'] = array(
                    "state" => "EXIST",
                    "message" => "��� ������ +79034731235",
                    "fio" => "������ ����� ������������",
                    "verified" => TRUE,
                    "identified" => TRUE
                );                
                
                /*
                $test['+380664848120'] = array(
                    "state" => "EXIST",
                    "message" => "��� ������ +380664848120",
                    "fio" => "������ ����� ������������",
                    "verified" => TRUE,
                    "identified" => false
                );                 
                */
                
                if(isset($test[$num]))
                {
                    $lc = $test[$num];
                }
                else
                {
                    $lc = array(
                        //����������� �������� �������� ���-��������
                        /*
                        "state" => "EXIST",
                        "message" => "��� ������ +71234567890", 
                        "fio" => "������ ����� ������������", 
                        "verified" => false,
                        "identified" => false
                         */
                    );
                }
                
                break;
            
            case 'superCheck':
                //$params = json_decode(file_get_contents('php://input'), 1);
                
                $ids = explode(",", $params['id']);
                /*if(is_array($params['id'])) {
                    $ids = array_map('intval', $params['id']);
                } else {
                    $ids = array(intval($params['id']));
                }*/
                
                $ids = array_map('intval', $ids);
                $lcs = $this->getRows($ids);
                
                if (!$lc) {
                    $lc = $this->_err('������. ���� �� ������.');
                }
                
                $pskb = new pskb();
                
                foreach($lcs as $k=>$lc) {
                    if ($lc->state == 'trans') {
                        $lc->state = pskb::PAYOUT_END;
                        $this->set($lc);
                    }

                    $pskb_lc = $pskb->getLCbyLCId($lc->id);
                    
                    $lc->sum     = $pskb_lc['sum'];
                    $lc->sumOpen = $pskb_lc['sumOpen'];
                    $lc->numDog  = $pskb_lc['sbr_id'];
                    $lc->cover   = date('d.m.Y H:i', strtotime($pskb_lc['dateCoverLC']));
                    
                    $payouts = sbr_meta::getStatePayout(array($pskb_lc['sbr_id'], 0), true);
                    if($payouts) {
                        foreach($payouts as $payout) {
                            if($lc->state != pskb::PAYOUT_END) continue;
                            $history = array(
                                'id'     => $lc->id,
                                'state'  => $lc->state,
                                'date'   => date('d.m.Y H:i'),
                                'uid'    => $pskb_lc['sbr_id'].'-'. ( $payout['num'] + 1 ),
                                'target' => ($payout['user_id'] == $payout['emp_id'] ? false : true),
                                'sum'    => $payout['credit_sum'],
                                'account'=> ($payout['user_id'] == $payout['emp_id'] ? $pskb_lc['accCust'] : $pskb_lc['accPerf']),
                                'ps'     => ($payout['user_id'] == $payout['emp_id'] ? $pskb_lc['ps_emp'] : $pskb_lc['ps_frl'])
                            );

                            $lc->history[] = new pskb_lc(json_encode($history));
                        }
                    }
                    
                    $lcs[$k] = $lc;
                }
                
                $post = json_encode($lcs);
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $params['urlRejoin']);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                if(defined('BASIC_AUTH')) {
                    curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
                }
                $res = curl_exec($ch);
                return;
                break;
            case 'historyLC':
                $id = intval($params['id']);
                $lc = $this->get($id);
                
                if (!$lc) {
                    $lc = $this->_err('������. ���� �� ������.');
                }
                
                if ($lc->state == 'trans') {
                    $lc->state = pskb::PAYOUT_END;
                    $this->set($lc);
                }
                $lc->sum   = 1000;
                $lc->account = '79210000000';
                $lc->uid     = $params['uid'];
                $lc->target  = $params['target'] ? $params['target'] : true;
                $lc->date  = date('d.m.Y H:i');
                break;
            case 'addLC':
                
                $lc = new pskb_lc();
                $lc->state = 'form';
                $lc->stateReason = 'test';
                
                foreach ($params as $k => $v) {
                    $lc->$k = $v;
                }
                
                $lc = $this->set($lc);
                
                break;
                
            case 'checkLC':
                $id = intval($params['id']);
                
                $lc = $this->get($id);
                
                if (!$lc) {
                    $lc = $this->_err('������. ���� �� ������.');
                }
                
//                $m = new memBuff();
//                $cntr = intval($m->get('___lc_cntr__' . $lc->id));
//                
//                if ($lc->state == 'form' && $cntr == 3) {
//                    $lc->state = 'err';
//                    $lc->stateReason = '500';
//                    $this->set($lc);
//                }
//                
//                if ($lc->state == 'form' && $cntr > 5) {
//                    $lc->state = 'new';
//                    $this->set($lc);
//                }
//                
//                $m->set('___lc_cntr__' . $lc->id, ($cntr+1));
//                
                if ($lc->state == 'form') {
                    $lc->state = 'new';
                    $this->set($lc);
                }
                
                break;
                
            case 'checks':
                $this->expStatusUpdate();
                $id = json_decode(file_get_contents('php://input'), 1);
                $ids = $id['id'];
                $lc = $this->getRows($ids);
                
                if (!$lc) {
                    $lc[] = $this->_err('������. ���� �� ������.');
                }
                
                break;
            
            case 'changeDateLC':
                $id = intval($params['id']);
                
                $lc = $this->get($id);
                
                if (!$lc) {
                    $lc = $this->_err('������. ���� �� ������.');
                    break;
                }
                
                if ($lc->status == 'end') {
                    $lc = $this->_err('������. ���� �� ������.');
                    break;
                }
                
                foreach ($params as $k => $v) {
                    $lc->$k = $v;
                }
                
                $this->set($lc);
                
                break;
            
            case 'openLC':
                $id = intval($params['id']);
                
                $lc = $this->get($id);
                
                if (!$lc) {
                    $lc = $this->_err('������. ���� �� ������.');
                    break;
                }
                
                if ($lc->status == 'end') {
                    $lc = $this->_err('������. ���� �� ������.');
                    break;
                }
                
                $lc->state = 'passed';    
                $lc->sms = strtoupper(substr(md5(time()), 3, 4));
                
                $this->set($lc);
                break;
            
            case 'subOpenLC':
                $id = intval($params['id']);
                
                $lc = $this->get($id);
                $code = $params['asp'];
                
                if (!$lc) {
                    $lc = $this->_err('������. ���� �� ������.');
                    break;
                }
                
                if ($lc->status == 'end') {
                    $lc = $this->_err('������. ���� �� ������.');
                    break;
                }
                
                if ($code != $lc->sms) {
                    $lc = $this->_err('�������� ��� �������������.');
                    break;
                }
                
                foreach ($params as $k => $v) {
                    $lc->$k = $v;
                }
                
                $lc->state = 'trans';
                
                $this->set($lc);
                
                break;
            
            case 'transLC':
                $id = intval($params['id']);
                
                $lc = $this->get($id);
                
                if (!$lc) {
                    $lc = $this->_err('������. ���� �� ������.');
                    break;
                }
                
                if ($lc->state != 'err') {
                    $lc = $this->_err('������. ��������� ������ ��� LC � ������� err.');
                    break;
                }
                
                $lc->state = 'end';
                $this->set($lc);
                
                break;
                
            case 'reqCode':
                $id = intval($params['id']);
                
                $lc = $this->get($id);
                
                if (!$lc) {
                    $lc = $this->_err('������. ���� �� ������.');
                    break;
                }
                
                if ($lc->state != 'passed') {
                    $lc = $this->_err('������. ��������� ������ ��� LC � ������� passed.');
                    break;
                }
                
                $lc->sms = strtoupper(substr(md5(time()), 3, 4));
                
                $this->set($lc);
                
                break;
            
            default:
                $lc = $this->_err('������ �������.');
        }
        
        echo json_encode($lc);
        exit();
    }
    
    public function set($lc) {
        if ($lc->dateExecLC) {
            $lc->dateExecLC = date('Y-m-d', strtotime($lc->dateExecLC));
        }
        if ($lc->dateEndLC) {
            $lc->dateEndLC = date('Y-m-d', strtotime($lc->dateEndLC));
        }
        if ($lc->dateCoverLC) {
            $lc->dateCoverLC = date('Y-m-d', strtotime($lc->dateCoverLC));
        }
        
        $data = array(
            'state' => $lc->state,
            'stateReason' => $lc->stateReason,
            'dateExecLC' => $lc->dateExecLC,
            'dateEndLC' => $lc->dateEndLC,
            'dateCoverLC' => $lc->dateCoverLC,
            'sms' => $lc->sms,
        );
        $resp = $this->_db->update('pskb_lc_test', $data, 'id = ?', $lc->id);
        
        if ($resp && !pg_affected_rows($resp)) {
            $lc->id = $this->_db->insert('pskb_lc_test', $data, 'id');
        }
        
        return $lc;
    }
    
    /**
     * 
     * @param type $id
     * @return \pskb_lc
     */
    public function get($id) {
        $sql = "SELECT * FROM pskb_lc_test WHERE id = ?";
        $res = $this->_db->row($sql, $id);
        
        if ($res) {
            foreach ($res as $k => $v) {
                $res[$k] = $this->_enc($v);
            }
        }
        
        $lc = new pskb_lc($res);
        return $lc;
    }
    
    public function getRows($ids = array()) {
        $sql = "SELECT * FROM pskb_lc_test WHERE id IN (?l)";
        $res = $this->_db->rows($sql, $ids);
        $lcs = array();
        
        if ($res) {
            foreach ($res as $cols) {
                foreach ($cols as $k => $v) {
                    $cols[$k] = $this->_enc($v);
                }
                $lcs[] = new pskb_lc($cols);
            }
        }
        return $lcs;
    }


    /**
     * 
     * @param type $msg
     * @return \pskb_lc
     */
    private function _err($msg) {
        $lc = new pskb_lc();
        $lc->state = 'err';
        $lc->stateReason = $this->_enc($msg);
        return $lc;
    }
    
    private function _enc($str) {
        return iconv('cp1251', 'utf8', $str);
    }
    
   public function expStatusUpdate() {
        global $DB;
        $sql = 'UPDATE pskb_lc_test SET state = 
                        (CASE WHEN "dateExecLC"::date  <= NOW()::date THEN \'expExec\'
                              WHEN "dateEndLC"::date   <= NOW()::date  THEN \'expEnd\'
                              ELSE state END)
                WHERE ("dateExecLC"::date  <= NOW()::date OR 
                      "dateEndLC"::date   <= NOW()::date) 
                      AND state NOT IN (\'END\', \'end\', \'expEnd\', \'expCover\', \'expExec\')';
        
        return $DB->query($sql);
    }
    
}