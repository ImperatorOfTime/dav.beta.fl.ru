<?
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/exrates.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/intrates.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/project_exrates.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/CFile.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/account.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/reqv_ordered.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/bank_payments.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/memBuff.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/professions.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ydpay.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/pskb.php';

$GLOBALS['MONTHS'] = array(1=>'������', '�������', '����', '������', '���', '����', '����', '������', '��������', '�������', '������', '�������');
$GLOBALS['MONTHA'] = array(1=>'������', '�������', '�����', '������', '���', '����', '����', '�������', '��������', '�������', '������', '�������');
$GLOBALS['EXRATE_CODES']  = array(
    exrates::BANK=>array('�����','���.','�/�','���������� �������','����������� ������ (����)'), 
    exrates::YM=>array('������.������','���.','��','��','������.������'), 
    exrates::WMR=>array('WMR','���.','WMR','WMR','WebMoney'), 
    exrates::WMZ=>array('WMZ','WMZ','WMZ','WMZ','WebMoney'), 
    exrates::FM=>array('���.','���.','���.','���.','���.'),
    exrates::WEBM => array('���-�������', '���.','WEBM','WEBM','���-�������'),
    exrates::CARD => array('���������� ��������', '���.', 'CARD','CARD', '���������� ��������')
);

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr_meta.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr_stages.php';

/**
 * ������������ ����� sbr_emp|sbr_frl|sbr_adm ��� ������ � ���. �������� ����� �������, ��� ������� ��������� ���� ����� �������������.
 * ����������� ������� ������� ��������������� �������������.
 */    
class sbr extends sbr_meta
{
    const NAME_LENGTH = 100; // ������������ ����� �������� ���.
    const SBR_REASONS_LENGTH = 1000; // ������������ ����� ������� ������ �� ���.
    const MAX_FILES = 10; // ������������ ���-�� ������ �� ���������.
    const MAX_FILE_SIZE = 52428800; // ����������� ���������� ������ ������ �� ���������.
    const MAX_COST_USD = 50000; // ������������ �������� ������� � USD (��� ������������: � ���������� ����������� �� ����, � ������������ �� ��� ������).
    const MAX_COST_USD_STR = "50000"; // ������������ �������� ������� � USD (��� ������������: � ���������� ����������� �� ����, � ������������ �� ��� ������).
    const MAX_COST_USD_FIZ = 5000; // ������������ ������ ����� ������� ����������� � ���������� ����

    
    const NEW_TEMPLATE_SBR   = 'bezopasnaya-sdelka';
    const MAX_DATE_LIMIT = '2 month'; // ������������ ���� �������� ����������� ��� ��� ������ � ������ ���
    // ������� ���.
    const STATUS_NEW       = 0;   // ����� ������.
    const STATUS_CHANGED   = 100; // ���������� ������.
    const STATUS_PROCESS   = 400; // � ����������.
    const STATUS_REFUSED   = 500; // ����������� (�����������).
    const STATUS_CANCELED  = 600; // ���������� (����������).
    const STATUS_COMPLETED = 700; // �����������.
    const STATUS_CLOSED    = 800; // ������ ������� (������ ��� �����������)

    // ���� ��������.
    const OP_RESERVE = 77; // ��� �������� ��������������.
    const OP_DEBIT   = 78; // ��� �������� ��������.
    const OP_CREDIT  = 79; // ��� �������� �������� ����� (�������).

    // ���� ����. (3 -- ������������ ��� �������� ����)
    const SCHEME_AGNT = 1;  // ��� ��������� �����.
    const SCHEME_PDRD = 2;  // ��� ����� �������.
    const SCHEME_OLD  = 10; // ��� ������ ����� (10%).
    const SCHEME_LC   = 4;  // ��� �����������
    const SCHEME_PDRD2 = 5; // ��� �������
    const SCHEME_DEFAULT = self::SCHEME_LC; // ��� ����� �� ���������.

    // ���� ������ � ���
    const FRL = 0; // ���������.
    const EMP = 1; // ������������.

    // ���� �������.
    const TAX_OLD_COM = 1;  // ��� �������� �� ������ �����.
    const TAX_EMP_COM = 2;  // ��� �������� � ������������.
    const TAX_FRL_COM = 3;  // ��� �������� � ����������.
    const TAX_NDS     = 6;  // ��� ���.
    const TAX_NDFL    = 7;  // ��� ���� ��� ���������� ��.
    const TAX_NDFL_NR = 12; // ��� ���� ��� ������������ ��.
    const TAX_NP      = 11; // ��� ������ �� ������� (��� ������������ ��).
    const TAX_FRL_NDS = 14; // ��� ���, ����������� � �����������.
    const TAX_EMP_COM_NEW = 30; // ��� ������� � ������������ ��� ����� ������
    
    // ���� �������� ����������.
    const DOCS_STATUS_SENT = 1; // ���������.
    const DOCS_STATUS_RECV = 2; // �������.
    const DOCS_STATUS_SIGN = 3; // ��������.
    const DOCS_STATUS_PUBL = 4; // �����������.

    // ���� ������� � ����������.
    const DOCS_ACCESS_FRL = 1; // �������� �����������.
    const DOCS_ACCESS_EMP = 2; // ���������.
    const DOCS_ACCESS_ALL = 3; // ����.

    // ���� ����������.
    const DOCS_TYPE_ACT            = 0x0001; // ��� (�� ������)
    const DOCS_TYPE_FACTURA        = 0x0002; // ����-������� (�� ������).
    const DOCS_TYPE_REP            = 0x0004; // �����.
    const DOCS_TYPE_ARB_REP        = 0x0008; // ����� �� ���������.
    const DOCS_TYPE_OFFER          = 0x0010; // ������.
    const DOCS_TYPE_COPY_AGREEMENT = 0x0020; // ����������� ����� ���������� (�� ����������).
    const DOCS_TYPE_COPY_CONTRACT  = 0x0040; // ����������� ����� �������� (�� ����������)
    const DOCS_TYPE_COPY_ACT       = 0x0080; // ����������� ����� ���� (�� ����������)
    const DOCS_TYPE_COPY_FACTURA   = 0x0100; // ����������� ����� ����-������� (�� ����������)
    const DOCS_TYPE_FM_APPL        = 0x0200; // ��������� � ������� � FM
    const DOCS_TYPE_WM_APPL        = 0x0400; // ��������� � ������� � WM
    const DOCS_TYPE_YM_APPL        = 0x0800; // ��������� � ������� � Yandex.Money
    const DOCS_TYPE_AGENT_REP      = 0x1000; // ����� ������.
    const DOCS_TYPE_TZ_PDRD        = 0x2000; // �� � �������
    const DOCS_TYPE_STATEMENT      = 0x4000; // ��������� �� ����������
    const DOCS_TYPE_PSKB_ACT       = 0x5000; // ��� �� ����� ����� ��������

    const DOCS_REQUIRED            = 0x0180; // ������������ ��������� ��� ������������� �������.

    const DOCS_FILE_MAX_SIZE = 2097152; // ����������� ������ ����� ���������.

    // ���� ���.
    const FT_PHYS = 1; // ���. ����.
    const FT_JURI = 2; // ��. ����.

    // ���� �����������.
    const RT_RU     = 1;    // ������
    const RT_UABYKZ = 2;    // �������, �������� ��� ���������
    const RT_REFUGEE = 3;   // �������
    const RT_RESIDENCE = 4; //��� �� ����������
    
    
    static public $rez_list = array(
        self::RT_RU         => '�������� ��',
        self::RT_UABYKZ     => '���������� ��',
        self::RT_REFUGEE    => '�������',
        self::RT_RESIDENCE  => '��� �� ���������� � ��'
    );



    // ������ ������� � �����������.
    const RS_WAITING = 1; // ���������.
    const RS_ACCEPTED = 2; // �������� � ��������.
    const RS_DENIED = 3; // ������������.

    // ���� �������������� �������.
    const EVROLE_FRL = 1; // ��� ����������.
    const EVROLE_EMP = 2; // ��� ������������.
    const EVROLE_ADM = 3; // ��� ������.

    // ��� �������.
    const XTYPE_EVNT = 1; // ������� �������.
    const XTYPE_RLBK = 2; // ������� ������.
    
    /**
     * ��������� �������� ����������.
     * @var array
     */
    static public $docs_ss = array (
        self::DOCS_STATUS_SENT => array('���������', 'sent_time', '���������'),
        self::DOCS_STATUS_RECV => array('�������', 'recv_time', '�������'),
        self::DOCS_STATUS_SIGN => array('��������', 'sign_time', '��������'),
        self::DOCS_STATUS_PUBL => array('������������', 'publ_time', '�����������')
    );


    /**
     * ��������� �������� ����������.
     * @var array
     */
    static public $docs_access = array (
        0 => array('�������', '�������'),
        self::DOCS_ACCESS_FRL => array('�����������', '�����������'),
        self::DOCS_ACCESS_EMP => array('������������', '������������'),
        self::DOCS_ACCESS_ALL => array('��� ��������� �������', '���')
    );


    /**
     * ��������� ����� ����������.
     * @var array
     */
    static public $docs_types = array (
        self::DOCS_TYPE_ACT => array('���', 0),
        self::DOCS_TYPE_FACTURA => array('����-�������', 0, '��/�'),
        self::DOCS_TYPE_REP => array('�����', 0),
        self::DOCS_TYPE_ARB_REP => array('����� �� ���������', 0),
        self::DOCS_TYPE_OFFER => array('������', 0),
        self::DOCS_TYPE_COPY_AGREEMENT => array('���������� (� ��������)', self::DOCS_ACCESS_ALL),
        self::DOCS_TYPE_COPY_CONTRACT => array('������� (� ��������)', self::DOCS_ACCESS_ALL),
        self::DOCS_TYPE_COPY_ACT => array('��� (� ��������)', self::DOCS_ACCESS_ALL),
        self::DOCS_TYPE_COPY_FACTURA => array('����-������� (� ��������)', self::DOCS_ACCESS_ALL, '��/� (� ��������)'),
        self::DOCS_TYPE_FM_APPL => array('��������� � ������� �� ������ ����', 0),
        self::DOCS_TYPE_WM_APPL => array('��������� � ������� ����� ������� WebMoney Transfer', 0, '��������� � ������� � WMR'),
        self::DOCS_TYPE_YM_APPL => array('��������� � ������� ����� ������� ������.������'),
        self::DOCS_TYPE_AGENT_REP => array('����� ������'),
        self::DOCS_TYPE_TZ_PDRD => array('����������� �������', 0),
        self::DOCS_TYPE_STATEMENT => array('��������� �� �������� �����������', self::DOCS_ACCESS_EMP),
        self::DOCS_TYPE_PSKB_ACT => array('��� (�� ����� �������������� ��������)', 0),
        0 => array('�� ���������', self::DOCS_ACCESS_ALL)
    );


    /**
     * ��������� �������� ������.
     * @var array
     */
    static public $ss_classes = array(
        sbr::STATUS_NEW => array('nr-list-new', '����� ����������� ������ ��� �����������', '���. ������� �� ����������'),
        sbr::STATUS_CHANGED => array('nr-list-changed', '���������� ����������� ������ ��� �����������', '������ � ������'),
        sbr::STATUS_PROCESS => array('nr-list-progress', '� ����������', '������ � ������'),
        sbr::STATUS_CANCELED => array('nr-list-canceled', '���������� �������', '������ ��������'),
        sbr::STATUS_REFUSED => array('nr-list-canceled', '����������� �������', '������ ���������'),
        sbr::STATUS_COMPLETED => array('nr-list-completed', '�����������', '������ ��������'),
        sbr::STATUS_CLOSED   => array('nr-list-canceled', '�����������', '������ ������')
    );


    /**
     * ��������� ���� ���.
     * @var array
     */
    static public $scheme_types = array (
        self::SCHEME_AGNT  => array('��������� �������', 'http://www.free-lance.ru/offer_lc.pdf', '�'),
        self::SCHEME_PDRD  => array('������� �������', 'http://www.free-lance.ru/offer_work_employer.pdf', '�'),
        self::SCHEME_LC    => array('����������', 'http://www.free-lance.ru/offer_lc.pdf', '�'),
        self::SCHEME_PDRD2 => array('������� �������', 'http://www.free-lance.ru/offer_work_employer.pdf', '�'),
        self::SCHEME_OLD   => array('�������� �����', '', '�')
    );
    
    static public $name_filter = array(
        ''         => '� ��� ���� ��� ������, ��������� � ����� ���������� (����� 02.10.2012).',
        'disable'  => '� ��� ��� ������ �� ������������.',
        'disable_emp' => '��� �������������� ����� ��� ����� <a href="?site=new" class="b-layout__link">������ ����� ������</a>.',
        'enable'   => '� ��� ���� ��� ������ �� �������� "� ������".',
        'cancel'   => '� ��� ��� ���������� ������.',
        'complete' => '� ��� ���� ��� ����������� ������.'
    );

    /**
     * ��. ������������, �� �������� ���������������� ������.
     * @var array
     */

    public $uid;

    /**
     * ����� ������������, �� �������� ���������������� ������.
     * @var array
     */
    public $login;

    /**
     * ��. ����������� (���������) ������������, ������������������� �����.
     * @var array
     */
    public $session_uid;

    /**
     * ���������� ��� �������� ������.
     * @var array
     */
    protected $_uploadDir;

    /**
     * ������ �� ������ (���� ������� sbr � �.�.)
     * @var array
     */
    public $data = array();

    /**
     * ������, ������ �� ������ ������ ������.
     * @var array
     */
    public $v_data = array();

    /**
     * ��� ���� � ������� sbr �����. ���� �������� ������������ (frl_id|emp_id).
     * @var string
     */
    public $uid_col;

    /**
     * ������� ����� ������� ���������������� ����� ("frl_":���� ������� ���� ��������, "emp_":���� ������� ���� �����������)


     * @var string
     */
    public $apfx;
    
    /**
     * ������ ������ ��� ��������� �������� ��� �������.
     * @var array
     */
    public $error = array();

    /**
     * ����� ������. ������ �������� sbr_stages.
     * @var array
     */
    public $stages;

    /**
     * ������� ����� ������ (����������� ����� �������������� ��� ��������� �� ������� � ������������ � ����� ��������� �����).
     * @var array
     */
    public $scheme;

    /**
     * ������ �������� �� ����� �����.
     * @var array
     */
    public $intrates;

    /**
     * ������ ������� ����� �����.
     * @var array
     */
    public $exrates;

    /**
     * ������ ���������� �� ������ ������� �� ���������� ���.
     * @var array
     */
    public $feedback;

    /**
     * ��������� �������� ������������ (�� �������� "�������").
     * @var array
     */
    public $user_reqvs = false;

    public $frl_reqvs = false;
    public $emp_reqvs = false;

    /**
     * ������ ������ � ����� ��� ������������� ���������.
     * @var array
     */
    public $post_doc;

    /**
     * ������ ��������� ������.
     * @var array
     */
    public $docs;

    /**
     * ������������ ����� ������� (��� ����������� -- �����, ��� ������������� -- ���� ������) ��� ������������ �� � ������.
     * @var int
     */
    public $max_norez_cost = NULL;

    /**
     * ���� �� ���� ������ ������, ��� ������ ��������� ����������� ���������� ��� ����������-����������� ��.
     * @var boolean
     */
    public $has_norez_overcost = false; 
    
    public $getter_schemes = 1; // �� ��������� �����
    
    /**
     * ����������� ������� sbr*
     *
     * @param integer $uid  ��. ������������, �� �������� ������� ��� (����� ����� �������� �� ����������� � ���������)
     * @param string $login ������ ������������ � uid=$uid.
     * @param integer $session_uid  ��. ����������� ������������. � ������� ������ $uid ������ ����� $session_uid.
     */
    function __construct($uid, $login = NULL, $session_uid = NULL) {
        $this->uid = $uid;
        $this->login = $login;
        $this->session_uid = $session_uid ? $session_uid : $this->uid;
    }

    /**
     * �� ���� ������ ����������, ��� ��������������� �����.
     * @return boolean   ����� ���?
     */
    function isAdminFinance() { return ( get_class($this)=='sbr_adm_finance' ); }
    /**
     * �� ���� ������ ����������, ��� ��������������� �����.
     * @return boolean   �����?
     */
    function isAdmin() { return ( get_class($this)=='sbr_adm' ); }
    /**
     * �� ���� ������ ����������, ��� ��������������� �����.
     * @return boolean   ������������?
     */
    function isEmp()   { return ( get_class($this)=='sbr_emp' ); }
    /**
     * �� ���� ������ ����������, ��� ��������������� �����.
     * @return boolean   ���������?
     */
    function isFrl()   { return ( get_class($this)=='sbr_frl' ); }

    /**
     * ����� ��� ������� ����� ���. ���� ������� ������ ��� ������� � ������������ ������, �� ������ ����������� $this->scheme.
     *
     * @param boolean $get_taxes   �������� ���������� �� ������� ��� ������ �����?
     * @return array   ������ ����.
     */
    function getSchemes($get_taxes = true) {
        $sql = "
          SELECT *
            FROM (
              SELECT type, MAX(date) as date
                FROM sbr_schemes
               WHERE date <= now()
               GROUP BY type
            ) as scx
          INNER JOIN
            sbr_schemes sch
              ON sch.type = scx.type
             AND sch.date = scx.date
        ";
        if($res = pg_query(DBConnect(), $sql)) {
            while($row = pg_fetch_assoc($res)) {
                if($get_taxes) {
                    $row['taxes'] = $this->getTaxes($row['id']);
                }
                $schemes[$row['id']] = $row;
            }
        }
        if($this->scheme_id && isset($schemes[$this->scheme_id])) {
            $this->scheme = $schemes[$this->scheme_id];
        }
        return $schemes;
    }

    /**
     * �������������� ����� ������� ���. ���� ������ ������ ��������� � �����, �� �������� �� ��������������, ����� �������� ������� �����, �������������� ���� ����� ������ ���.
     *
     * @param boolean $get_taxes   �������� ���������� �� ������� ��� �����?
     * @return boolean
     */
    function getScheme($get_taxes = true) 
    {
        $this->getUserReqvs();
        
        $sql = null;
        
        if ($this->scheme_id) {
            $sql = "SELECT * FROM sbr_schemes WHERE id = ?i";
            $sql = $this->db()->parse($sql, $this->scheme_id);
        } else if($this->scheme_type) {
            $sql = "SELECT * FROM sbr_schemes WHERE type = ?i AND date <= now() ORDER BY date DESC LIMIT 1";
            $sql = $this->db()->parse($sql, $this->scheme_type);
        }
        
        if (!$sql) { 
            return false;
        }
        
        if ($res = pg_query(DBConnect(), $sql)) {
            $this->scheme = pg_fetch_assoc($res);
            if($get_taxes)
                $this->scheme['taxes'] = $this->getTaxes($this->scheme['id']);
        }
        
        return !!$res;
    }

    /**
     * ���������� ���������� �� ������� � ��������� ��� ���������� �����.
     * ������ 0 -- ������ ����������, 1 -- ������������. ����� ������� -- ��. �������.
     *
     * @param integer $scheme_id  ��. �����.
     * @return array   ������ ������.
     */
    function getTaxes($scheme_id) {
        $sql = "
          SELECT *
            FROM sbr_taxes_schemes stch
          INNER JOIN sbr_taxes st ON st.id = stch.tax_id
           WHERE stch.scheme_id = ?i
           ORDER BY pos
        ";
           
        $sql = $this->db()->parse($sql, $scheme_id);   
           
        if($res = pg_query(DBConnect(), $sql)) {
            while($row = pg_fetch_assoc($res)) {
                $taxes[$row['role']][$row['tax_id']] = $row;
            }
        }
        return $taxes;
    }

    /**
     * �������� �������������� ������ ������ not_used.
     * ����� ����� �������������� � �����, �� � ����������� �� ������ ���������� �������������� ��� �� �������������� (��. ���� sbr_taxes.formula).
     * 
     * @param int $role   ��� ������ �������� (sbr::EMP | sbr::FRL).
     */
    function markNotUsedTaxes($role = sbr::EMP) {
        if(!$this->stages || !($fstage = $this->stages[0]))
            return;

        if(!($taxes = &$this->scheme['taxes'][$role]))
            return;
        foreach($taxes as $tax_id=>&$tax) { 
            // �������� �������������� ������, ����� �� 1-�� ����������� �����...
            $tax['not_used'] = ($fstage->calcTax($tax) == 0); 
        }
    }

    /**
     * ��������� �� ����������� �� 2� ������ �������� ����� ����� ����� ����������� ������ (��� ����� �������) ���� ������.
     * ����� ������ ���������, ����� �������� ������������, ��� � ������, ���� �� ������ ����� ����� ��� ������������� ��������.
     * 
     * @return float   �������� �����.
     */
    function getTotalCost($look_arbitrage = true) {
        $total = 0;
        $this->setStagesEx();
        foreach($this->stages as $s) {
            $a = 1;
            if($s->status == sbr_stages::STATUS_ARBITRAGED && $look_arbitrage) {
                if($s->arbitrage === false)
                    $s->getArbitrage(false, false);
                $a = abs((int)$this->isEmp() - $s->arbitrage['frl_percent']);
            }
            $total += round($a*$s->cost, 2);
        }
        return $total;
    }

    /**
     * ���������� ����� ����� �������
     * 
     * @param  array $tax ������ �� ������
     * @param  float $coeff ����������� �� ������� ���������� ������ �����
     * @param  array $dvals ���������
     * @return float
     */
    function getTotalTax($tax, $coeff = 1, $dvals = NULL, $round = true) {
        $total = 0;
        $this->setStagesEx();
        foreach($this->stages as $s) 
            $total += $round ? round($s->calcTax($tax, $dvals) * $coeff, 2) : ( $s->calcTax($tax, $dvals) * $coeff ) ;
        return $total;
    }


    /**
     * ���������� ����������� ������ ������ �����.
     *
     * @param  int $newsys ��� ������ 
     * @return float
     */
    function getCostSysCoeff($newsys) {
        $cost_coeff = 1;
        $newsys = $newsys===NULL ? $this->cost_sys : $newsys;
        if($newsys != $this->cost_sys) {
            if(!$this->exrates)
                $this->getExrates();
            $cost_coeff = $this->exrates[$this->cost_sys . $newsys];
        }
        return $cost_coeff;
    }


    /**
     * ������ ����� ��������� ������� ������.
     *
     * @param integer $id   ��. ������ (�� ��������� -- �������)
     * @param integer $scheme_type   ��� ����� ������
     * @return string
     */
    function getContractNum($id = NULL, $scheme_type = NULL, $posted = null) {
        $prefix = $this->getSbrPrefix($id, $posted);
        
        if(!$id) $id = $this->id;
        if(!$scheme_type) $scheme_type = $this->scheme_type;
        
        return "{$prefix}-{$id}-".self::$scheme_types[$scheme_type][2].'/�';
    }
    
    function getContractStageNum($id = NULL, $scheme_type = NULL, $stage = 1, $posted = null) {
        $prefix = $this->getSbrPrefix($id, $posted);
        
        if(!$id) $id = $this->id;
        if(!$scheme_type) $scheme_type = $this->scheme_type;
        return "{$prefix}-{$id}-".self::$scheme_types[$scheme_type][2].'/�-'.$stage;
    }
    
    function getSbrPrefix ($id = null, $posted = null) {
        if ($posted) {
            $postedTime = strtotime($posted);
        } elseif (!$id && $this->data['posted']) {
            $postedTime = strtotime($this->data['posted']);
        } elseif (!$posted && $id && $this->id && $id == $this->id && $this->data['posted']) { // 
            $postedTime = strtotime($this->data['posted']);
        } elseif ($this->id && !$this->data['posted']) { // ��� �� ������������
            $postedTime = time();
        }
        
        // ������� � ������� ���� - ����� ������� - ��
        $prefix = ($postedTime > mktime(0, 0, 0, 7, 1, 2013)) ? '��' : '���';
        
        return $prefix;
    }

    /**
     * ������ ����� ���. � ������ � �������� �������, � ������� -- � �������...
     *
     * @param float $comm   ������ ����� ��������, ���� ����� ���������.
     * @return float
     */
    function getCommNds(&$comm) {

        $comm = 0;
        if(!$this->scheme)
            $this->getScheme();
        if($this->scheme_type == self::SCHEME_AGNT || $this->scheme_type == self::SCHEME_LC) {
            $comm = $this->getTotalTax($this->scheme['taxes'][sbr::EMP][self::TAX_EMP_COM]);
            return 18*$comm/118;
        }

        else if($this->scheme_type == self::SCHEME_PDRD || $this->scheme_type == self::SCHEME_PDRD2) {
            return $this->getTotalTax($this->scheme['taxes'][sbr::EMP][self::TAX_NDS]);
        }
    }
    
    /**
     * ���������� ����������� ���������� ������ � ������ ��� ����������� ��.
     *
     * @return float
     */
    function maxNoRezCost() {
        $default = sbr::MAX_COST_USD * 28;
        if(!$this->max_norez_cost) {
            $this->max_norez_cost = (int)$this->usd2rur(sbr::MAX_COST_USD);
        }
        return ( $this->max_norez_cost ? $this->max_norez_cost : $default );
    }


    /**
     * ����� ���������� �� ���-����������.
     * @see sbr_meta::getUserReqvs()
     *
     * @return array
     */
    function getUserReqvs() {
        if($this->user_reqvs === false)
            $this->user_reqvs = parent::getUserReqvs($this->uid);
        return $this->user_reqvs;
    }

    /**
     * ����� ���-��������� ���������� (�� �������� ����������, ������� "�������")
     * 
     * @return array ������ � �����������, ���������������: [1] -- ��������� ���. ����, [2] -- ��������� ��. ����, [any] -- ��. ����, �����.
     */
    function getFrlReqvs($force = false) {
        if( ($this->frl_reqvs === false && $this->frl_id) || $force === true)
            $this->frl_reqvs = parent::getUserReqvs($this->frl_id);
        return $this->frl_reqvs;
    }
    
    /**
     * ����� ���-��������� ������������ (�� �������� ����������, ������� "�������")
     * 
     * @return array ������ � �����������, ���������������: [1] -- ��������� ���. ����, [2] -- ��������� ��. ����, [any] -- ��. ����, �����.
     */
    function getEmpReqvs($force = false) {
        if( ($this->emp_reqvs === false && $this->emp_id) || $force === true)
            $this->emp_reqvs = parent::getUserReqvs($this->emp_id);
        return $this->emp_reqvs;
    }
    /**
     * ���������, ��������� �� ����������� ��������� �� �������� "�������" ������������.
     * ��������� ��� ���� (��. ��� ���.).
     *
     * @return boolean   ��/���.
     */
    function checkUserReqvs($reqvs = NULL, $form_type = NULL) {
        if(!$reqvs)
            $reqvs = $this->getUserReqvs();
        if($form_type === null) $form_type = $reqvs['form_type'];
        $is_filled = explode(',',preg_replace('/[}{]/', '', $reqvs['is_filled']));
        return $is_filled[$form_type - 1] == 't';
    }
    
    /**
     * ��� ���������� ���������� �������� ����� ���� �� ����������� ��-�� �������� ���������� �� ������ �������������
     *
     */
    function setCheckEmpReqvs($stage_id) {
        if(!$this->checkUserReqvs($this->emp_reqvs)) {
            $hreqvs = $this->getUserReqvHistory($stage_id, $this->emp_id); 
            $fre = '/^_(\d)_(.*)$/';
            if($hreqvs['b']) {
                foreach($hreqvs['b'] as $n=>$v) {
                    if(preg_match($fre, $n, $m)) {
                        $ret[$m[1]][$m[2]] = $v;
                    } else {
                        $ret[$n] = $v;
                    }
                }
                $this->emp_reqvs = $ret;
            }   
        }
        
        if($this->emp_reqvs['form_type']==sbr::FT_PHYS && $this->emp_reqvs[sbr::FT_PHYS]['fio'] == '') {
            $this->emp_reqvs[sbr::FT_PHYS]['fio'] = "���������� ����";    
        }
        
        if ($this->emp_reqvs['form_type']==sbr::FT_PHYS && ($this->cost_sys == exrates::WMR || $this->cost_sys == exrates::YM)) {
            if(trim($this->emp_reqvs[$this->emp_reqvs['form_type']]['address']) == '') {
                $this->emp_reqvs[$this->emp_reqvs['form_type']]['address'] = $this->emp_reqvs[$this->emp_reqvs['form_type']]['address_reg']; 
            }
            
            if(trim($this->emp_reqvs[$this->emp_reqvs['form_type']]['inn']) == '') {
                $this->emp_reqvs[$this->emp_reqvs['form_type']]['inn'] = '0000000000';    
            }
        } else if($this->emp_reqvs['form_type']==sbr::FT_PHYS) {
            if(trim($this->emp_reqvs[$this->emp_reqvs['form_type']]['inn']) == '') {
                $this->emp_reqvs[$this->emp_reqvs['form_type']]['inn'] = '0000000000';    
            }    
        }        
    }


    /**
     * ���������, ����� �� ����� �������� �� �����.
     * @return boolean
     */
    function isOffIntrates($stage = NULL) {
        return ( ($this->isEmp() && $stage && $stage->arbitrage) || strtotime($this->sended) < strtotime('25.11.2010') );
    }


    /**
     * �������������� ������ �������� �� ����� �����.
     * @see intrates
     */
    function getIntrates($stage = NULL) {
        $this->intrates = intrates::GetAll();
        if($this->isOffIntrates($stage))
            array_walk($this->intrates, create_function('&$v', '$v=0;'));
    }

    /**
     * �������������� ������ ������� ������ ������ �����.
     * @see exrates
     */
    function getExrates() {
        $this->exrates = exrates::GetAll();
    }

    /**
     * �������������� $this->login �� ���������� $this->uid.
     * @return string   ����� �������� ������������.
     */
    function getLogin() {
        if(!$this->uid) return NULL;
        if(!$this->login) {
            $uo = new $this->uclass();
            if($u = $uo->GetName($this->uid, $err));
                $this->login = $u['login'];
        }
        return $this->login;
    }

    /**
     * ���������� ���� � �����, � ������� ����� ��������� ����� ��������� ������������.
     * @return string
     */
    function getUploadDir() {
        if(!$this->getLogin())
            return false;
        return ( $this->_uploadDir = 'users/'.substr($this->login, 0, 2)."/{$this->login}/upload/" );
    }

    /**
     * ��������� ���� �� ������.
     * 
     * @param CFile $file   ������, ����������������� �� $_FILES.
     * @param integer $max_size   ����������� ���������� ������ �����.
     * @return string   ������ ��� 0 -- ��� ��.
     */
    function uploadFile($file, $max_size) {
        if(!$file->size && strlen($file->tmp_name) == 0) return -1;
        if ( $file->size == 0 ) {
            return '���� ' . htmlspecialchars($file->name, ENT_QUOTES, 'cp1251') . ' ����� ������ 0 ����';
        }
        $file->server_root = 1;
        $file->table = 'file_sbr';
        $file->max_size = $max_size;
        $file->orig_name = change_q_x($file->name);
        if(!$file->MoveUploadedFile($this->_uploadDir) || !isNulArray($file->error)) {
            return '������ ��� �������� ����� ' . htmlspecialchars($file->name, ENT_QUOTES, 'cp1251') . ': '. $file->error;
        }
        return 0;
    }

    /**
     * ����� ���� ������ ������ ���� ���� � �������� ��.
     * 
     * @param integer $id   ��. �����.
     * @return sbr_stages   ����.
     */
    function getStageById($id) {
        foreach($this->stages as $stage) {
            if($stage->id==$id)
                return $stage;
        }
        return NULL;
    }
    
    public function getWhereNewSbr($tbl_alias = 's') {
        if($this->getter_schemes == 1) {
            $where = " AND ( {$tbl_alias}.scheme_type = " . self::SCHEME_LC . " OR {$tbl_alias}.scheme_type = " . self::SCHEME_PDRD2 . " OR {$tbl_alias}.scheme_id IS NULL)";
        } else {
            $where = " AND ( {$tbl_alias}.scheme_type = " . self::SCHEME_AGNT . " OR {$tbl_alias}.scheme_type = " . self::SCHEME_PDRD . " OR {$tbl_alias}.scheme_type = " . self::SCHEME_OLD . ")";
        }
        return $where;
    }

    /**
     * ����� ������� ��� ������� ������.
     * 
     * @param integer $sbr_id   ��. ����������� ������ (���� �����, �� ���)
     * @param boolean $get_stages   ����� �� ����� ������?
     * @param boolean $get_attach   ����� �� �������� � ������?
     * @param boolean $is_draft     true:����� ���������, false:����� �� ���������, NULL:�� �����
     * @param boolean $get_scheme   ����� ���������� �� ����� ������?
     * @param boolean $only_active  ������ �������� ������ (�� �����������, �� �����������)?
     * @param boolean $only_reserved  ������ ����������������� ������?
     * @return array   ������ �������� sbr, ��������������� ��. ������.
     */
    protected function _getAllCommon($sbr_id = NULL, $get_stages = true, $get_attach = false, $is_draft = false, $get_scheme = false, $only_active = false, $only_reserved = false) {
        $class = get_class($this);
        $ret = NULL;
        $order_by = 's.status, s.posted DESC';
        $where  = $is_draft!==NULL ? " AND s.is_draft = '".(int)$is_draft."'" : '';
        $where .= $sbr_id!==NULL ? ' AND s.id = '.intvalPgSql($sbr_id) : '';
        if($only_active) {
            $where .= ' AND s.status NOT IN (' . self::STATUS_CANCELED.','.self::STATUS_REFUSED.','.self::STATUS_COMPLETED.','.self::STATUS_CLOSED . ')';
        }
        if($only_reserved) {
            $where .= ' AND s.reserved_id IS NOT NULL';
        }
        // � ������ ������� ������ �������
        if(!$this->isAdmin() && $sbr_id == null) { // ��� ������� ������� �� �����
            $where .= $this->getWhereNewSbr();
        }
        $left_field = ', pl.id as pskb_pl_id, pl.ps_emp, pl.ps_frl, pl.lc_id, pl.state, pl.sum as pl_sum, pl."sumOpen", pl."stateReason", pl."dateExecLC", pl."dateEndLC", 
                                pl."dateCoverLC", pl."tagCust", pl."nameCust", pl."numCust", pl."psCust", pl."accCust", pl."innCust", pl."kppCust", pl."tagPerf",
                                pl."namePerf", pl."numPerf", pl."psPerf", pl."accPerf", pl."innPerf", pl."kppPerf", pl.created as pskb_created, pl.covered, pl.executed,
                                pl.ended, pl.dol_paymentid, pl.dol_payment_time, pl.dol_lastcheck, pl.dol_completed, pl.dol_raw_resp, pl.dol_is_failed,
                                pl."alienCust", pl."alienPerf"';
        $join_pskb  = "LEFT JOIN pskb_lc pl ON pl.sbr_id = s.id";
        
        if(!$this->isAdmin() && !$this->isAdminFinance()) {
            $sql = "
              SELECT s.*, date_part('day', work_time) as work_days,
                     a.login as {$this->apfx}login, a.uname as {$this->apfx}uname, a.usurname as {$this->apfx}usurname, a.is_pro as {$this->apfx}is_pro, a.is_verify as {$this->apfx}is_verify, a.is_team as {$this->apfx}is_team, a.is_pro_test as {$this->apfx}is_pro_test, a.photo as {$this->apfx}photo, a.role as {$this->apfx}role, a.email as {$this->apfx}email,
                     u.login as {$this->upfx}login, u.uname as {$this->upfx}uname, u.usurname as {$this->upfx}usurname, u.is_pro as {$this->upfx}is_pro, u.is_verify as {$this->upfx}is_verify, u.is_team as {$this->upfx}is_team, u.is_pro_test as {$this->upfx}is_pro_test, u.photo as {$this->upfx}photo, u.role as {$this->upfx}role, u.email as {$this->upfx}email
                     {$left_field}
                FROM sbr s
              LEFT JOIN
                {$this->anti_tbl} a
                  ON a.uid = {$this->anti_uid_col}
              LEFT JOIN 
                 {$this->uclass} u
                  ON u.uid = {$this->uid_col}
               {$join_pskb}
               WHERE s.{$this->uid_col} = {$this->uid}
               {$where}
               ORDER BY s.posted DESC
            ";
        } else {
            $sql = "

              SELECT s.*, date_part('day', work_time) as work_days,
                     e.login as emp_login, e.uname as emp_uname, e.usurname as emp_usurname, e.is_pro as emp_is_pro, e.is_verify as emp_is_verify, e.is_team as emp_is_team, e.is_pro_test as emp_is_pro_test, e.photo as emp_photo, e.role as emp_role, e.email as emp_email,
                     f.login as frl_login, f.uname as frl_uname, f.usurname as frl_usurname, f.is_pro as frl_is_pro, f.is_verify as frl_is_verify, f.is_team as frl_is_team, f.is_pro_test as frl_is_pro_test, f.photo as frl_photo, f.role as frl_role, f.email as frl_email
                     {$left_field}
                FROM sbr s
              INNER JOIN
                employer e
                  ON e.uid = s.emp_id
              LEFT JOIN
                freelancer f

                  ON f.uid = s.frl_id
              {$join_pskb}
               WHERE true -- !!!
               {$where}
               ORDER BY s.posted DESC
            ";
        }
        $sbr_id = array();
        $sbrs = NULL;
        if(($res = pg_query(DBConnect(), $sql)) && pg_num_rows($res)) {
            while($row = pg_fetch_assoc($res)) {
                $sbrs[$row['id']] = new $class($this->uid, $this->login, $this->session_uid);
                $sbrs[$row['id']]->data = $row;
                if($get_stages)
                    $sbrs[$row['id']]->setStages(NULL, $get_attach, true);
                if($get_scheme)
                    $sbrs[$row['id']]->getScheme();
            }
        }

        return $sbrs;
    }
    
    /**
     * ����� ������� ��� ������� ������.
     * 
     * @todo ��������� _new_ ����� ��������� ����������� ������� ������� ���������� ����� ������ ��� ������
     * 
     * @param integer $sbr_id   ��. ����������� ������ (���� �����, �� ���)
     * @param boolean $get_stages   ����� �� ����� ������?
     * @param boolean $get_attach   ����� �� �������� � ������?
     * @param boolean $is_draft     true:����� ���������, false:����� �� ���������, NULL:�� �����
     * @param boolean $get_scheme   ����� ���������� �� ����� ������?
     * @param boolean $only_active  ������ �������� ������ (�� �����������, �� �����������)?
     * @param boolean $only_reserved  ������ ����������������� ������?
     * @param boolean $filter
     * @param boolean $limit
     * @param boolean $offset
     * @return array   ������ �������� sbr, ��������������� ��. ������.
     */
    protected function _new_getAllCommon($sbr_id = NULL, $get_stages = true, $get_attach = false, $is_draft = false, $get_scheme = false, $only_active = false, $only_reserved = false, $filter = false, $limit = false, $offset = false) {
        
        if ($limit) {
            $limit = intval($limit);
        }
        
        if ($offset) {
            $offset = intval($offset);
        }
        
        $max_date_limit = self::MAX_DATE_LIMIT;
        $class = get_class($this);
        $ret = NULL;
        $order_by = 's.status, s.posted DESC';
        $where  = $is_draft!==NULL ? " AND s.is_draft = '".(int)$is_draft."'" : '';
        if($sbr_id!==NULL) {
            $only_sbr = true;
            $where .= $sbr_id!==NULL ? ' AND s.id = '.intvalPgSql($sbr_id) : '';
        }
        if($only_active) {
            $where .= ' AND s.status NOT IN (' . self::STATUS_CANCELED.','.self::STATUS_REFUSED.','.self::STATUS_COMPLETED . ')';
        }
        if($only_reserved) {
            $where .= ' AND s.reserved_id IS NOT NULL';
        }
        
        if(!$this->isAdmin() && $sbr_id == null) { // ��� ������� ������� �� �����
            $where .= $this->getWhereNewSbr();
        }
        $limit_sql = trim( ( $limit ? "LIMIT $limit" : '') . ' ' . ($offset ? "OFFSET $offset" : '') ) ;
        switch($filter) {
            case 'disable_emp':
            case 'disable':
                $where .= ' AND ( s.status IN (' . sbr::STATUS_NEW . ', '.sbr::STATUS_CHANGED . ', ' . sbr::STATUS_PROCESS . ') AND s.reserved_id IS NULL)';
                break;
            case 'enable':
                $where .= ' AND ( s.status IN ('.sbr::STATUS_PROCESS . ', '.sbr::STATUS_CHANGED . ') AND s.reserved_id IS NOT NULL)';
                break;
            case 'cancel':
                $where .= ' AND (s.status = '.sbr::STATUS_CANCELED . ' OR s.status = ' . sbr::STATUS_REFUSED . ')';
                break;
            case 'complete':
                $where .= ' AND s.status = '.sbr::STATUS_COMPLETED;
                break;
        }
        
        if(!$this->isAdmin() && !$this->isAdminFinance()) {
            if($this->scheme_type == self::SCHEME_LC) {
                $left_field = ', pl.id as pskb_pl_id, pl.ps_emp, pl.ps_frl, pl.lc_id, pl.state, pl.sum as pl_sum, pl."sumOpen", pl."stateReason", pl."dateExecLC", pl."dateEndLC", 
                                 pl."dateCoverLC", pl."tagCust", pl."nameCust", pl."numCust", pl."psCust", pl."accCust", pl."innCust", pl."kppCust", pl."tagPerf",
                                 pl."namePerf", pl."numPerf", pl."psPerf", pl."accPerf", pl."innPerf", pl."kppPerf", pl.created as pskb_created, pl.covered, pl.executed,
                                 pl.ended, pl.dol_paymentid, pl.dol_payment_time, pl.dol_lastcheck, pl.dol_completed, pl.dol_raw_resp, pl.dol_is_failed,
                                 pl."alienCust", pl."alienPerf"';
                                  
                $join_pskb  = "LEFT JOIN pskb_lc pl ON pl.sbr_id = s.id";
            }
            
            // @todo ���������������� ������� -- ���������� � ���� ���� �����
            // ������� ���������� ����� ������ ��� ������
            if($limit_sql == '') {
                $sql_new = "SELECT s.*, date_part('day', work_time) as work_days,
                        a.login as {$this->apfx}login, a.uname as {$this->apfx}uname, a.usurname as {$this->apfx}usurname, a.is_pro as {$this->apfx}is_pro, a.is_verify as {$this->apfx}is_verify, a.is_team as {$this->apfx}is_team, a.is_pro_test as {$this->apfx}is_pro_test, a.photo as {$this->apfx}photo, a.role as {$this->apfx}role, a.email as {$this->apfx}email
                        {$left_field}
                    FROM sbr s
                LEFT JOIN
                    {$this->anti_tbl} a
                    ON a.uid = {$this->anti_uid_col}
                {$join_pskb}    
                WHERE s.{$this->uid_col} = {$this->uid} AND COALESCE(s.completed, CURRENT_TIMESTAMP) > CURRENT_TIMESTAMP - interval '{$max_date_limit}'
                {$where} AND s.status = " . self::STATUS_NEW . "
                ORDER BY s.posted DESC";
            }
            $status = ($this->isEmp() ? "" : " AND s.status <> " . self::STATUS_NEW );
            $sql = "
              SELECT s.*, date_part('day', work_time) as work_days,
                     a.login as {$this->apfx}login, a.uname as {$this->apfx}uname, a.usurname as {$this->apfx}usurname, a.is_pro as {$this->apfx}is_pro, a.is_verify as {$this->apfx}is_verify, a.is_team as {$this->apfx}is_team, a.is_pro_test as {$this->apfx}is_pro_test, a.photo as {$this->apfx}photo, a.role as {$this->apfx}role, a.email as {$this->apfx}email
                     {$left_field}
                FROM sbr s
              LEFT JOIN
                {$this->anti_tbl} a
                  ON a.uid = {$this->anti_uid_col}
               {$join_pskb}   
               WHERE s.{$this->uid_col} = {$this->uid} ". ( $only_sbr ? "" : "AND COALESCE(s.completed, CURRENT_TIMESTAMP) > CURRENT_TIMESTAMP - interval '{$max_date_limit}'" ) . " 
               {$where} {$status}
               ORDER BY s.last_event_id DESC
               {$limit_sql}
            ";
        } else {
            if($limit_sql == '') {
                $sql_new = "

                SELECT s.*, date_part('day', work_time) as work_days,
                        e.login as emp_login, e.uname as emp_uname, e.usurname as emp_usurname, e.is_pro as emp_is_pro, e.is_verify as emp_is_verify, e.is_team as emp_is_team, e.is_pro_test as emp_is_pro_test, e.photo as emp_photo, e.role as emp_role, e.email as emp_email,
                        f.login as frl_login, f.uname as frl_uname, f.usurname as frl_usurname, f.is_pro as frl_is_pro, f.is_verify as frl_is_verify, f.is_team as frl_is_team, f.is_pro_test as frl_is_pro_test, f.photo as frl_photo, f.role as frl_role, f.email as frl_email
                    FROM sbr s
                INNER JOIN
                    employer e
                    ON e.uid = s.emp_id
                LEFT JOIN
                    freelancer f
                    ON f.uid = s.frl_id
                WHERE COALESCE(s.completed, CURRENT_TIMESTAMP) > CURRENT_TIMESTAMP - interval '{$max_date_limit}'
                {$where} AND s.status = " . self::STATUS_NEW . "
                ORDER BY s.posted DESC
                ";
            }
            $status = ($this->isEmp() ? "" : " AND s.status <> " . self::STATUS_NEW );
            $sql = "

              SELECT s.*, date_part('day', work_time) as work_days,
                     e.login as emp_login, e.uname as emp_uname, e.usurname as emp_usurname, e.is_pro as emp_is_pro, e.is_verify as emp_is_verify, e.is_team as emp_is_team, e.is_pro_test as emp_is_pro_test, e.photo as emp_photo, e.role as emp_role, e.email as emp_email,
                     f.login as frl_login, f.uname as frl_uname, f.usurname as frl_usurname, f.is_pro as frl_is_pro, f.is_verify as frl_is_verify, f.is_team as frl_is_team, f.is_pro_test as frl_is_pro_test, f.photo as frl_photo, f.role as frl_role, f.email as frl_email
                FROM sbr s
              INNER JOIN
                employer e
                  ON e.uid = s.emp_id
              LEFT JOIN
                freelancer f
                  ON f.uid = s.frl_id
               WHERE ". ( $only_sbr ? "" : "COALESCE(s.completed, CURRENT_TIMESTAMP) > CURRENT_TIMESTAMP - interval '{$max_date_limit}'" ) . " 
               {$where} {$status}
               ORDER BY s.last_event_id DESC
               {$limit_sql}
            ";
        }
        
        
        $sbr_id = array();
        $sbrs = NULL;
        
        if($sql_new && $this->isFrl()) {
            if(($res = pg_query(DBConnect(), $sql_new)) && pg_num_rows($res)) {
                $this->new_count = 0;
                while($row = pg_fetch_assoc($res)) {
                    $this->new_count++;
                    $sbrs[$row['id']] = new $class($this->uid, $this->login, $this->session_uid);
                    $sbrs[$row['id']]->data = $row;
                    if($get_stages)
                        $sbrs[$row['id']]->setStages(NULL, $get_attach, true);
                    if($get_scheme)
                        $sbrs[$row['id']]->getScheme();
                }
            }
        }
        
        if(($res = pg_query(DBConnect(), $sql)) && pg_num_rows($res)) {
            while($row = pg_fetch_assoc($res)) {
                $sbrs[$row['id']] = new $class($this->uid, $this->login, $this->session_uid);
                $sbrs[$row['id']]->data = $row;
                if($get_stages)
                    $sbrs[$row['id']]->setStages(NULL, $get_attach, true);
                if($get_scheme)
                    $sbrs[$row['id']]->getScheme();
            }
        }

        return $sbrs;
    }


    /**
     * ����� ��� ������� ������ (������� �����) ������������. ������������ �� �������.
     * @return array   ������ �������� sbr, ��������������� ��. ������.
     */
    function getCurrents() {
        $sbrs = $this->_getAllCommon(NULL, true, false, false, $this->isFrl(), false, false);
        if($sbrs) {
            foreach($sbrs as $id=>$s) {
                $ret[(int)$s->status][$id] = $s;
            }
            ksort($ret);
        }
        return $ret;
    }
    
    /**
     * ����� ������� ��� ������� ������� ������ ��� 
     * 
     * @todo ��������� _new_ ����� ��������� ����������� ������� ������� ���������� ����� ������ ��� ������
     */
    function _new_getCurrents($filter = false, $limit = false) {
        return $this->_new_getAllCommon(NULL, true, false, false, $this->isFrl(), false, false, $filter, $limit);
    }
    
    /**
     * ���������� ������ ���� ��������� �� �������� �������
     * @param integer $uid uid ������������, ��� �������� ���� ��������� �� ���
     * 
     * @return array (uid => login) ���������
     */
    function _new_getOpenSbrPartners () {
        $sbrs = $this->_new_getAllCommon(null, false, false, false, false, true);
        $roleStr = $this->isEmp() ? 'frl' : 'emp';
        $sbrPartnersUid = array();
        foreach ($sbrs as $sbr) {
            $sbrPartnersUid[$sbr->data[$roleStr . '_id']] = $sbr->data[$roleStr . '_login'];
        }
        return $sbrPartnersUid;
    }
    
    /**
     * ���� �� ���� ���� ������
     */
    function _new_isAnySbrExists () {
        return (bool)count($this->_new_getAllCommon(null, false, false, null, false, false, false, false, 1));
    }

    /**
     * ����� ��������� ������
     * 
     * @param boolean $get_stages   ����� �� ����� ������?
     * @param boolean $get_attach   ����� �� �������� � ������?
     * @param boolean $get_scheme   ����� ���������� �� ����� ������?
     * 
     * @return this
     */
    function getOldSbrCompleted($get_stages = true, $get_attach = false, $get_scheme = false) {
        $where = $this->getWhereNewSbr();
        
        $sql = "SELECT s.*, date_part('day', work_time) as work_days,
                     a.login as emp_login, a.uname as emp_uname, a.usurname as emp_usurname, a.is_pro as emp_is_pro, a.is_verify as emp_is_verify, a.is_team as emp_is_team, a.is_pro_test as emp_is_pro_test, a.photo as emp_photo, a.role as emp_role, a.email as emp_email
                FROM sbr s
              LEFT JOIN
                employer a
                  ON a.uid = emp_id
               WHERE s.{$this->uid_col} = {$this->uid} AND COALESCE(s.completed, s.completed, CURRENT_TIMESTAMP) < CURRENT_TIMESTAMP - interval '" . sbr::MAX_DATE_LIMIT . "'
                AND s.is_draft = '0' AND s.status IN (" . sbr::STATUS_COMPLETED . ", " . sbr::STATUS_CANCELED . ", " . sbr::STATUS_REFUSED . ", " . sbr::STATUS_CLOSED . ") {$where}
               ORDER BY s.posted DESC";
               
        $class = get_class($this);
        $ret = NULL;
        
        if(($res = pg_query(DBConnect(), $sql)) && pg_num_rows($res)) {
            while($row = pg_fetch_assoc($res)) {
                $sbrs[$row['id']] = new $class($this->uid, $this->login, $this->session_uid);
                $sbrs[$row['id']]->data = $row;
                if($get_stages)
                    $sbrs[$row['id']]->setStages(NULL, $get_attach, true);
                if($get_scheme)
                    $sbrs[$row['id']]->getScheme();
            }
        }

        return $sbrs;
    }
    
    function getCountCompleteSbr() {
        global $DB;
        
        $memBuff = new memBuff;
        $key = self::$memBuff_prefix_compl . $this->getter_schemes . $this->uid;
        if ( ($cnt = $memBuff->get($key)) !== false ) {
            return $cnt;
        }
        
        $where = $this->getWhereNewSbr();
        
        $sql = "SELECT COUNT(*) 
                FROM sbr s 
                WHERE s.{$this->uid_col} = {$this->uid} AND COALESCE(s.completed, s.completed, CURRENT_TIMESTAMP) < CURRENT_TIMESTAMP - interval '" . sbr::MAX_DATE_LIMIT . "'
                AND s.is_draft = '0' AND s.status IN (" . sbr::STATUS_COMPLETED . ", " . sbr::STATUS_CANCELED . ", " . sbr::STATUS_REFUSED . ", " . sbr::STATUS_CLOSED . ") {$where}";
        
        $cnt = $DB->val($sql);
        $memBuff->set($key, $cnt, 180);
        return $cnt;
    }
    
    /**
     * ����� ��� �� ��� �� 
     *   
     */
    function getSbrForId($sbr_id = false) {
        if(!$sbr_id) return false;
        return $this->_new_getAllCommon($sbr_id, true, false, false, $this->isFrl(), false, false, $filter, $limit);
    }
    
    /**
     * ����� ���������� ��� � ����������� �� �������
     * 
     * @global type $DB
     * @param string $filter  �������
     * @return integer
     */
    function getCountCurrentsSbr($filter = false) {
        global $DB;
        if(!$this->uid_col) return false;
        $memBuff = new memBuff;
        $key = self::$memBuff_prefix . $this->getter_schemes . $this->uid . $filter;
        if ( ($cnt = $memBuff->get($key)) !== false ) {
            return $cnt;
        }
        $ret = NULL;
        $order_by = 's.status, s.posted DESC';
        $where    = '';
        switch($filter) {
            case 'disable':
                $where .= ' AND (s.status = ' . sbr::STATUS_NEW . ' OR s.status = ' . sbr::STATUS_CHANGED . ')';
                break;
            case 'enable':
                $where .= ' AND s.status = '.sbr::STATUS_PROCESS;
                break;
            case 'cancel':
                $where .= ' AND (s.status = '.sbr::STATUS_CANCELED . ' OR s.status = ' . sbr::STATUS_REFUSED . ')';
                break;
            case 'complete':
                $where .= ' AND s.status = '.sbr::STATUS_COMPLETED;
                break;
        }
        
        $where .= $this->getWhereNewSbr();
        
        if(!$this->isAdmin() && !$this->isAdminFinance()) {
            $sql = "SELECT COUNT(s.*) FROM sbr s WHERE s.{$this->uid_col} = {$this->uid} AND s.is_draft = '0' {$where}";
        } else {
            $sql = "SELECT COUNT(s.*) FROM sbr s WHERE s.{$this->uid_col} = {$this->uid} AND s.is_draft = '0' {$where}";
        }
        $cnt = $DB->val($sql);
        $memBuff->set($key, $cnt, 180);
        return $cnt;
    }

    /**
     * ����� ������ ��� ������ �� �������� ��������.
     *
     * @param limit $����������� ����������.
     * @return array   ������ �������� sbr, ��������������� ��. ������.
     */
    function getRatings($limit = 0) {
        $sbrs = $this->_getAllCommon(NULL, false);
        foreach($sbrs as $id=>$s) {
            if($limit && $i>=$limit || !$s->setStages(NULL, false, false, true)) {
                unset($sbrs[$id]);
                continue;
            }
            $i++;
        }
        return $sbrs;
    }

    /**
     * �������� ���� � �������������� ��������� sbr �� ��. ������ ����� ������.
     * ��� ���� �� ����� ��� ��������� �����.
     * 
     * @param integer $stage_id   ��. �����.
     * @param boolean $get_attach   ����� �� �������� ��� ��������� ������ � �����.
     * @return sbr_stages   ���� ������.
     */
    function initFromStage($stage_id, $get_attach = true) {
        if(!$stage_id) return NULL;
        if($stage = $this->setStage($stage_id, $get_attach, true)) {
            $this->initFromId($stage->sbr_id, true, false, false);
            $stage->data['cost_sys'] = $this->cost_sys;
            if($this->error || !$this->id) {
                unset($stage);
                return NULL;
            }
        }
        return $stage;
    }


    /**
     * �������������� ���������� ���� � ������ ������.
     * @see sbr::getStage()
     *
     * @param integer $stage_id   ��. �����
     * @return object
     */
    function setStage($stage_id) {
        $args = func_get_args();
        $stage = call_user_func_array(array($this, 'getStage'), $args);
        $this->stages[$stage->num] = $stage;
        return $stage;
    }

    /**
     * ��������� ���������� ���� ������.
     * @see sbr::getStages()
     *
     * @param integer $stage_id   ��. �����
     * @return sbr_stages
     */
    function getStage($stage_id) {
        if(!$stage_id) return NULL;
        $args = func_get_args();

        $stages = call_user_func_array(array($this, 'getStages'), $args);
        return current($stages);
    }

    /**
     * ������������� ������ ������ � ������ ������.
     * @see sbr::getStages()
     * @return boolean
     */
    function setStages() {
        $args = func_get_args();
        $this->stages = call_user_func_array(array($this, 'getStages'), $args);
        return !!$this->stages;
    }

    /**
     * ������������� ������ ������ � ������ ������. ���������, ���� ��� ��� ����� ����������������.
     * @see sbr::getStages()
     * @return boolean
     */
    function setStagesEx() {
        if(!$this->stages || $this->stages_cnt != count($this->stages)) {
            $args = func_get_args();
            $this->stages = call_user_func_array(array($this, 'getStages'), $args);
        }
        return !!$this->stages;
    }

    /**
     * ���������� ����� ������� ������
     * 
     * @param integer $stage_id   ��. �����, ���� ������, �� ����� ������ ������������ ����, ����� ��� ������� ������.
     * @param boolean $get_attach   ����� �� �������� ������?
     * @param boolean $get_su   ����� �� ������ �� ��������� ����/���� (��������� ���������, ���-�� ���������� ���������)?
     * @param boolean $get_feedbacks   ����� ������ �� ����� �� ���������������� �����.
     * @return array
     */
    function getStages($stage_id = NULL, $get_attach = false, $get_su = false, $get_feedbacks = false, $get_info_pskb = true) 
    {
        $stages = NULL;
        
        $where = ($stage_id == NULL)? $this->db()->parse("ss.sbr_id = ?i", $this->id) : 
                                      $this->db()->parse("ss.id = ?i", $stage_id);
        
        $order_by = 'ss.num';
        if($get_su && $this->uid) {
            $join_su = "LEFT JOIN sbr_stages_users su ON su.stage_id = ss.id AND su.user_id = {$this->session_uid}";
            $cols_su = ', ss.msgs_cnt - su.read_msgs_count AS unread_msgs_count, su.read_msgs_count, su.last_msgs_view';
        }
        if($get_feedbacks) {
            $join_sf = "INNER JOIN sbr_feedbacks sf ON sf.id = ss.{$this->apfx}feedback_id";
            $cols_sf = ", sf.p_rate, sf.a_rate, sf.n_rate";
            $order_by = 'ss.closed_time DESC';
        }
        if($get_info_pskb) {
            $join_sbr = "LEFT JOIN pskb_lc p ON p.sbr_id = ss.sbr_id";
            $cols_sbr = ", p.ps_emp, p.ps_frl, p.\"tagCust\", p.\"tagPerf\"";
        }
        $join_pskb = " LEFT JOIN sbr_stages_payouts ps ON ps.stage_id = ss.id";
        $cols_pskb = ', ps.state as lc_state, ps."stateReason" as lc_state_reason, COALESCE(ps.bank_completed, ps.completed) as lc_date ';
        $sql = "
          SELECT ss.*, date_part('day', work_time) as work_days, (start_time - worked_time) as start_time_without_pause, EXTRACT(EPOCH FROM worked_time) as worked_time_sec,
            (COALESCE(start_time, now()) + work_time)::date - now()::date as work_rem, start_time + work_time as dead_time
                 {$cols_sbr}
                 {$cols_su}
                 {$cols_sf}
                 {$cols_pskb}
            FROM sbr_stages ss
          {$join_sbr}  
          {$join_su}
          {$join_sf}
          {$join_pskb}
          WHERE {$where}
          ORDER BY {$order_by}
        ";
        if($res = pg_query(DBConnect(), $sql)) {
            while($row = pg_fetch_assoc($res)) {
                $stage = new sbr_stages($this);
                $stage->data = $row;
                $stage->data['cost_sys'] = $this->cost_sys;
                if($stage->status == sbr_stages::STATUS_ARBITRAGED || $stage->status == sbr_stages::STATUS_INARBITRAGE) {
                    $stage->getArbitrage();
                    if($stage->arbitrage['frl_percent'] !== NULL) {
                        $cost_frl = $stage->data['cost'] * $stage->arbitrage['frl_percent'];
                    } else {
                        $cost_frl = $stage->data['cost']; 
                    }
                } else {
                    $cost_frl = $stage->data['cost'];
                }
                if($row['ps_frl'] != null && $cost_frl <= pskb::WW_ONLY_SUM && $row['tagPerf'] == pskb::PHYS) { // ������ ��� �������
                    $stage->data['ps_frl'] = pskb::WW;
                }
                if($stage->cost * $this->cost2rur() > $this->maxNorezCost())
                    $this->has_norez_overcost = true;
                if($get_attach)
                    $stage->data['attach'] = $stage->getAttach();
                    
                if ( $get_su && $this->uid && $stage->data['unread_msgs_count'] ) {
                    $stage->data['unread_first_id'] = $stage->getFirstUnreadMsgId();
                }
                
                $stages[$row['num']] = $stage;
            }
        }
        return $stages;
    }

    /**
     * ���������� id ������ �� id �����
     * 
     * @param  integer $stage_id  id �����
     * @return integer            id ������
     */
    function getSbrIdFromStage($stage_id) {
        global $DB;
        if($res = $DB->query("SELECT sbr_id FROM sbr_stages WHERE id = ?i", $stage_id)) {
            if($row = pg_fetch_row($res)) {
                return $row[0];
            }
        }
        return NULL;
    }

    /**
     * �������������� ������ �� ��.
     * 
     * @param integer $sbr_id   ��. ����������� ������ (���� �����, �� ���)
     * @param boolean $get_stages   ����� �� ����� ������?
     * @param boolean $get_attach   ����� �� �������� � ������?
     * @param boolean $is_draft     true:����� ���������, false:����� �� ���������, NULL:�� �����
     * @param boolean $get_scheme   ����� ���������� �� ����� ������?
     * @return boolean   ��� ��?
     */
    function initFromId($sbr_id, $get_stages = true, $get_attach = true, $is_draft = NULL, $get_scheme = true) {
        $sbrs = $this->_getAllCommon($sbr_id, $get_stages, $get_attach, $is_draft);
        if(!$this->error && $sbrs[$sbr_id]) {
            $this->data = $sbrs[$sbr_id]->data;
            if($get_stages) {
                $this->stages = $sbrs[$sbr_id]->stages;
                foreach($this->stages as $stage)
                    $stage->sbr = $this;
            }
            if($get_scheme)
                $this->getScheme(); 
            return true;
        }

        return false;
    }

    /**
     * ����� ������ �������� ������.
     * @see sbr::_getAllCommon();
     * @return array   ������ �������� sbr, ��������������� ��. ������.
     */
    function getActives($get_stages = false, $get_attach = false, $get_scheme = false) {
        return $this->_getAllCommon(NULL, $get_stages, $get_attach, false, $get_scheme, true);
    }

    /**
     * ����� ���������� �������� ������.
     * @see sbr::getActives();


     * @return array   ������ �������� sbr, ��������������� ��. ������.
     */
    function getActivesCount() {
        $sbrs = $this->getActives();
        return $sbrs ? count($sbrs) : 0;
    }

    /**
     * ����� ������ ����������������� ������.
     * @see sbr::_getAllCommon();
     * @return array   ������ �������� sbr, ��������������� ��. ������.
     */
    function getReserved($get_stages = false, $get_attach = false, $get_scheme = true, $only_active = true) {
        return $this->_getAllCommon(NULL, $get_stages, $get_attach, false, $get_scheme, $only_active, true);
    }

    /**
     * ����� ������, ����������� � �������� (�����������������, �� ��� �� �����������).
     * @see sbr::_getAllCommon();

     * @return array   ������ �������� sbr, ��������������� ��. ������.
     */
    function getProcessings($get_stages = false, $get_attach = false, $get_scheme = false) {
        return $this->_getAllCommon(NULL, $get_stages, $get_attach, false, $get_scheme, true, true);
    }

    /**
     * ���������� ������� �������.
     * @see sbr_meta::parseEvents()
     * 

     * @params array $filter   ������ �� ���� �������, ���� ��� �����, � ������� ��������� �������.
     * @param integer $dir_col   ����, �� �������� ����������� ������� (0: �� ���� �������).
     * @param string $dir   ��� ���������� ASC|DESC
     * @return array
     */
    function getHistory(&$filter = NULL, $dir_col = 0, $dir = 'ASC') {
        $dirs = array(
           0 => array('ASC'=>'se.xact_id, ec.level', 'DESC'=>'se.xact_id DESC, ec.level DESC')
        );
        if(!$dirs[$dir_col]) $dir_col = 0;
        if($dir!='DESC') $dir = 'ASC';

        $sql = "
            SELECT se.id, ec.level, se.sbr_id, se.version, se.ev_code, se.xact_id, sx.xtime as ev_time, ec.name as ev_name,
                   s.name as sbr_name, ss.name as stage_name, se.own_id, ec.own_rel, st.rel, st.col,
                   sv.old_val, sv.new_val, sv.note, sv.src_id
              FROM sbr s
            INNER JOIN
              sbr_events se
                ON se.sbr_id = s.id
            INNER JOIN
              sbr_xacts sx
                ON sx.id = se.xact_id
            INNER JOIN
              sbr_ev_codes ec
                ON ec.id = se.ev_code
            LEFT JOIN
              sbr_stages ss
                ON ss.id = se.own_id
               AND ec.own_rel = 'sbr_stages'
            LEFT JOIN
              sbr_versions sv
            INNER JOIN
              sbr_types st
                ON st.id = sv.src_type_id
                ON sv.event_id = se.id
             WHERE s.id = ?i
               -- AND se.ev_code < 500 -- !!! �������� ����� �������.
             ORDER BY {$dirs[$dir_col][$dir]}
        ";

        $sql = $this->db()->parse($sql, $this->data['id']);
             
        if(($res = pg_query(self::connect(),$sql)) && pg_num_rows($res))
            return self::parseEvents(pg_fetch_all($res), $filter);
        return array('events' => array(), 'options' => array(), 'filter' => $filter);
    }


    /**
     * �������� ������ ���������� ��������� ������, ����� ����, ��� ����������� ���������.
     * @return boolean   �������?
     */
    function resendChanges() {
        if(self::$XACT_ID) {
            $sql = "UPDATE sbr SET frl_version = version WHERE id = ?i AND version < frl_version";
            $sql = $this->db()->parse($sql, $this->id);
            return !!pg_query(self::connect(false), $sql);
        }
        return false;
    }


    /**
     * �������� ������������ �� ��������� ����� ��������� � ������, ����� ����, ��� ����������� �� ��� ���������.
     * @return boolean   �������?
     */
    function cancelChanges() {
        if(self::$XACT_ID) {
            $sql = "UPDATE sbr SET version = frl_version WHERE id = ?i AND version < frl_version";
            $sql = $this->db()->parse($sql, $this->id);
            return !!pg_query(self::connect(false), $sql);
        }
        return false;
    }


    /**
     * ����������� ������������ �� ���������.
     *
     * @param integer $version   ������ ������ �� ������ ������ (��, ������� ������ ����� �����������).
     * @return boolean   �������? ���� �������� ����� ������ ����� ���������, �� false.
     */
    function refuseChanges($version) {
        if(self::$XACT_ID) {
            $sql = "UPDATE sbr SET version = frl_version WHERE id = ?i AND version = ?i";
            $sql = $this->db()->parse($sql, $this->id, $version);
            return !!pg_query(self::connect(false), $sql);
        }
        return false;
    }

    /**
     * ����������� ����������� � �����������.
     *
     * @param integer $version   ������ ������ �� ������ ������ (��, ������� ������ ����� �����������).
     * @return boolean   �������?
     */
    function agreeChanges($version) {
        if(self::$XACT_ID) {
            $sql = "UPDATE sbr SET frl_version = ?i WHERE id = ?i AND frl_version <> ?i";
            $sql = $this->db()->parse($sql, $version, $this->id, $version);
            return !!pg_query(self::connect(false), $sql);
        }
        return false;
    }


    /**
     * ����� ������� ������ �������� ������ (�������� � ���������� $this->data � ������� �� ��������� ������).
     *
     * @param integer $version   ������ ������.
     * @param array $old_data    ������ ������� ������ ($this->data).
     * @return array   
     */
    function getVersion($version, &$old_data) {
        $sql = "
          SELECT se.*, sx.xtime as ev_time, ssv.ev_code, ssv.ev_name, ssv.rel, ssv.col, ssv.old_val, ssv.new_val
            FROM sbr_events se
          INNER JOIN
            sbr_xacts sx
              ON sx.id = se.xact_id
          INNER JOIN
            vw_sbr_versions ssv
              ON ssv.event_id = se.id
           WHERE se.sbr_id = ?i
             AND se.own_id = ?i
             AND se.version > ?i
           ORDER BY se.version DESC, se.xact_id DESC
        ";

        $sql = $this->db()->parse($sql, $this->id, $this->id, $version);
             
        $vdata = $old_data;
        if($res = pg_query(DBConnect(), $sql)) {
            while($row = pg_fetch_assoc($res)) {
                if($row['rel'] == 'sbr_stages') {
                }
                if($row['rel'] == 'sbr') {
                    $ov = $row['old_val'];
                    $vdata[$row['col']] = $ov;
                }
            }
        }
        return $vdata;
    }


    /**
     * �������� ���������� �� ����� ������� ������.
     * @param float $curr_cost   ��������� $this->cost (������) ���� ���������.
     */
    function view_scheme_info($curr_cost = NULL) {

        if(!$curr_cost) $curr_cost = $this->cost;
        $curr_sbr = $this;
        $sh_info = array();
        $tmp_ts = $total_sum = $curr_sbr->getTotalCost();
        if($curr_sbr->scheme['taxes'][sbr::FRL]) foreach($curr_sbr->scheme['taxes'][sbr::FRL] as $tid=>$tax) {
            $tax_sum = $curr_sbr->getTotalTax($tax);
            if(!$tax_sum) continue;
            $total_sum -= $tax_sum;
            $tmp = array();
            $tmp['name'] = $tax['name'];
            $tmp['percent'] = ($tax['percent']*100).'%';
            $tmp['cost'] = sbr_meta::view_cost($tax_sum, $curr_sbr->cost_sys, false);
            if(in_array($tax['id'],$curr_sbr->getTaxByCode(array('TAX_NDFL', 'TAX_NDFL_NR', 'TAX_FRL_NDS')))){
                $tmp2 = array();
                $tmp2['name'] = '�������������� ����������';
                $tmp2['percent'] = '�';
                $tmp2['cost'] = sbr_meta::view_cost($tax_sum + $total_sum, $curr_sbr->cost_sys, false);
                $hndl = &$tmp2;
                $sh_info[] = $tmp2;
            }
            $sh_info[] = $tmp;
        }
        ob_start();

        include($_SERVER['DOCUMENT_ROOT'].'/norisk2/tpl.scheme_info.php');
        $this->view_sign_alert();
        return ob_get_clean();
    }

    /**
     * �������� �������������� � ������������� ��������� ��������� � ��������� ���������.
     */
    function view_sign_alert() {
        $curr_sbr = $this;
        if($this->isFrl() && !$this->isOffIntrates())
            include($_SERVER['DOCUMENT_ROOT'].'/norisk2/tpl.wmyd_alert.php');
        if($this->isEmp()) echo '<br/>';
        include($_SERVER['DOCUMENT_ROOT'].'/norisk2/tpl.sign_alert.php');
    }

    /**
     * ���������� ����� ���������� ��������� �������� ���-�������� �������������.
     * @see sbr_meta::setLastView()
     * @param integer $user_id   ��. ������������.
     * @param string $interface new - ����� ��������� old - ������ ���������
     * @return boolean   �������?
     */
    function setLastView($interface = 'new') {
        return parent::setLastView($this->session_uid, $interface);
    }



    /**
     * ����� ����� ������� ��� �� ������������.
     * @see sbr_meta::addFeedback()
     * 
     * @param array $request   ������ ����������������� ������� � ������������ ������.
     * @return boolean   �������?
     */
    function feedback($request) {
        if($request['descr'] == '') $request['descr'] = '&nbsp;'; //return true; // ����� ������� ������ �� ����������
        if($request['id'] && $request['id'] != $this->data[$this->upfx.'feedback_id']) return false;
        $inxact = !!sbr_meta::$XACT_ID;
        if($inxact || $this->_openXact(TRUE)) {
            if ($feedback = parent::addFeedback($request, $this->feedback, $err)) {
                
               $sql = "UPDATE sbr SET {$this->upfx}feedback_id = ?i WHERE id = ?i";
               $sql = $this->db()->parse($sql, $feedback['id'], $this->id);
               
               if(pg_query(self::connect(false), $sql)) {
                   if(!$inxact) $this->_commitXact();
                   return $feedback;
               }
            }
            $this->error['feedback'] = $err;
            $this->_abortXact();
        }
        return false;
    }


    /**
     * �������� ������� ������ � ������� ������ ������ � ����������� � ����������� ����������.
     * @param array $doc   ���������� �� ��������� (���� ������� sbr_docs).
     */
    function doc_node($doc) {
    ?>
        <li class="cl-li nr-sys-i-li">
            <div class="nr-sys-i">
                <b class="b1"></b>
                <b class="b2"></b>
                <div class="nr-sys-i-in c">
                    <a href="?site=history&id=<?=$doc['sbr_id']?>" class="lnk-white">������� �������</a>
                    <a href="?site=docs&id=<?=$doc['sbr_id']?>" class="lnk-white">��������� �������</a>
                    <span class="nr-sys-date">
                        <?=date('d.m.Y | H:i', strtotime($doc['publ_time']))?>
                    </span>
                    <strong class="nr-sys-file">
                        <a href="<?=WDCPREFIX.'/'.$doc['file_path'].$doc['file_name']?>" class="lnk-white" style="float:none;margin:0"><?=$doc['name']?></a> 
                        <?= $doc['type'] == self::DOCS_TYPE_WM_APPL || $doc['type'] == self::DOCS_TYPE_YM_APPL ? '���������' : ($doc['status'] == self::DOCS_STATUS_PUBL ? '��������' : '�������')?>
                    </strong>
                </div>
                <b class="b2"></b>
                <b class="b1"></b>
            </div>
        </li>
    <?
    }

    /**
     * ����� ��������� ������� ������ � ������ $this->docs.
     * 
     * @param integer $doc_id   ��. ���������, ���� ����� ����� ������������.
     * @param boolean $is_deleted   true:����� ���������, false:����� �� ���������, NULL:�� �����.
     * @param boolean $only_publ   ����� ������ �������������� (��������� � ������� � ������).
     * @return array   
     */
    function getDocs($doc_id = NULL, $is_deleted = false, $only_publ = false, $stage_id = NULL, $diff = false) 
    {
        $where = $this->db()->parse("WHERE sd.sbr_id = ?i", $this->id) . ($doc_id ? $this->db()->parse(' AND sd.id = ?i', $doc_id) : '');
        $where .= $is_deleted === false ? ' AND sd.is_deleted = false' : ($is_deleted === true ? ' AND sd.is_deleted = true' : '');
        $order = 'sd.id';
        if($only_publ) {
            $where .= ' AND sd.status = ' . self::DOCS_STATUS_PUBL;
            $order = 'sd.publ_time';
        }
        if($stage_id) {
            $where .= $this->db()->parse(" AND (sd.stage_id IS NULL OR sd.stage_id = ?i)", $stage_id); // !!! �� ������������� �������� �������.
        }
        if(!$this->isAdmin() && !$this->isAdminFinance()) {
            if($this->isEmp())
                $where .= ' AND sd.access_role & ' . self::DOCS_ACCESS_EMP . ' = ' . self::DOCS_ACCESS_EMP;
            else
                $where .= ' AND sd.access_role & ' . self::DOCS_ACCESS_FRL . ' = ' . self::DOCS_ACCESS_FRL;
        }
        return ($this->docs = parent::getDocs($where, $order, true, $diff));
    }

    /**
     * ���������� ��������� �������������� �������� ��������� ����.
     * 
     * @param integer $type   ��� ���������.
     * @param integer $stage_id   ��. ����� ������.
     * @return array   
     */
    function getLastPublishedDocByType($type, $stage_id = NULL) {
        $where = "WHERE sd.sbr_id = {$this->id} AND sd.is_deleted = false AND sd.status = " . self::DOCS_STATUS_PUBL;
        if($stage_id) {
            $where .= " AND (sd.stage_id IS NULL OR sd.stage_id = {$stage_id})";
        }
        $order = 'sd.publ_time DESC';
        $docs = parent::getDocs($where, $order, false);
        return $docs[0];
    }

    /**
     * �������� ����������������� ������� ����� ��������� ��� ��������������� ���������.
     * ���������� ���������� ������ � $this->post_doc (��� �� ������, ��� ������ ������), � ����� ��������� ���� ��������� �� ������.
     * 
     * @param array $request   ������ ������� (���, ����).
     * @param array $files   ������ $_FILES
     */
    function _docInitFromRequest($request, $files) {
        $this->post_doc = array();
        foreach($request as $field=>$value) {
            if(is_scalar($value))
                $value = stripslashes($value);
            switch($field) {
                case 'status' :
                case 'access_role' :
                case 'owner_role' :
                case 'id' :
                case 'type' :
                    $value = intvalPgSql($value);
                    break;
            }
            $this->post_doc[$field] = $value;
        }
        if($files != null) $this->post_doc['file_id'] = null;
        if(!$this->isAdmin() && !$this->isAdminFinance()) {
            if(!isset($this->post_doc['status']))
                $this->post_doc['status'] = self::DOCS_STATUS_SENT;
            if(!isset($this->post_doc['access_role']))
                $this->post_doc['access_role'] = $this->isEmp() ? self::DOCS_ACCESS_EMP : self::DOCS_ACCESS_FRL;
        }

        if(!$this->post_doc['name']) {
            $cnum = $this->getContractNum();
            $dn = sbr::$docs_types[$this->post_doc['type']][0];
            switch($this->post_doc['type']) {
                case sbr::DOCS_TYPE_STATEMENT      :
                    $this->post_doc['name']  = "{$dn} � {$cnum}";
                    break;
                case sbr::DOCS_TYPE_TZ_PDRD        :
                    $this->post_doc['name']  = "{$dn} � {$cnum}";
                    break;
                case sbr::DOCS_TYPE_ACT            :
                case sbr::DOCS_TYPE_FACTURA        :
                case sbr::DOCS_TYPE_REP            :
                case sbr::DOCS_TYPE_COPY_ACT       :
                case sbr::DOCS_TYPE_COPY_FACTURA   :
                case sbr::DOCS_TYPE_AGENT_REP      :
                case sbr::DOCS_TYPE_PSKB_ACT      :
                    $this->post_doc['name']  = "{$dn} �� �������� � {$cnum}";
                    break;
                case sbr::DOCS_TYPE_COPY_CONTRACT  :
                case sbr::DOCS_TYPE_OFFER          :
                    if($this->post_doc['subtype'] == 1) {
                        // ��������!!! � �������� ��������� ����������� ������ �������������� ����� � ������ "�������" (�������, ��������, �������� ...)
                        $this->post_doc['name']  = "{$dn} � {$cnum} �� ���������� ��������";
                    } else if($this->post_doc['subtype'] == 2) {
                        $this->post_doc['name']  = "{$dn} � {$cnum} �� ���������� ����������";
                    } else {
                        $this->post_doc['name']  = "{$dn} � {$cnum}";
                    }
                    break;
                case sbr::DOCS_TYPE_ARB_REP        :
                case sbr::DOCS_TYPE_COPY_AGREEMENT :
                case sbr::DOCS_TYPE_FM_APPL :
                case sbr::DOCS_TYPE_WM_APPL :
                case sbr::DOCS_TYPE_YM_APPL :
                    $this->post_doc['name']  = "{$dn}";
                    break;
                default:
                    $this->error['docs']['name'] = '����������, ��������� ��� ����';
                break;
            }
        }
        
        if(!$this->error && $files['attach']['size']) {
            $this->getUploadDir();
            $file = new CFile($files['attach']);
            if($err = $this->uploadFile($file, self::DOCS_FILE_MAX_SIZE))
                $this->error['docs']['attach'] = $err;
            else
                $this->post_doc['file_id'] = $file->id;
        }
    }

    /**
     * �������� �������� �� ������ ����������������� �������.
     * 
     * @param array $request   ������ ������� (���, ����).
     * @param array $files   ������ $_FILES
     * @return boolean   �������?
     */
    function addDocR($request, $files = NULL) {
        $this->_docInitFromRequest($request, $files);
        if(!$this->error) {
            return $this->addDoc($this->post_doc, $files === null?true:false);
        }
        return false;
    }

    /**
     * �������� ��������
     * 
     * @param array   $doc      ������ ������ � ������������ ������.
     * @param boolean $system   ��������� �������� ������ ������������� ��� ��� (true-�������������, false - ���)
     * @return boolean   �������?
     */
    function addDoc($doc, $system) {
        $doc['file_id'] = intval($doc['file_id']);
        if(!$doc['file_id']) {
            $this->error['docs']['attach'] = '���������� ��������� ����';
            return false;
        }
        $inxact = !!sbr_meta::$XACT_ID;
        if($inxact || $this->_openXact(TRUE)) {
            $sql_data = $doc;
            $sql_data['name'] = pg_escape_string(change_q_x($sql_data['name']));
            $sql_data['stage_id'] = $sql_data['stage_id'] ? (int)$sql_data['stage_id'] : 'NULL';
            if(!isset($sql_data['owner_role']))
                $sql_data['owner_role'] = (sbr::DOCS_ACCESS_EMP*$this->isEmp() | sbr::DOCS_ACCESS_FRL*$this->isFrl());
            $sql_data['num'] = $sql_data['num'] ? (int)$sql_data['num'] : 'NULL';
            $sql = "
              INSERT INTO sbr_docs (sbr_id, stage_id, file_id, name, status, access_role, " . self::$docs_ss[$sql_data['status']][1] . ", type, owner_role, num)
              VALUES ({$this->id}, {$sql_data['stage_id']}, {$sql_data['file_id']}, '{$sql_data['name']}', {$sql_data['status']}, {$sql_data['access_role']}, now(),
                      {$sql_data['type']}, {$sql_data['owner_role']}, {$sql_data['num']})
              RETURNING id
            ";
            if($res = pg_query(self::connect(false), $sql)) {
                if(!$inxact) $this->_commitXact();
                if($sql_data['type'] == 1 || $sql_data['type'] == 8) { // ����, ������ �� ���������
                    switch($sql_data['access_role']) {
                        case 1: // ����������
                            if(!sbr_meta::$save_reqv_frl) {
                                $this->setUserReqvHistory($this->data['frl_id'], $sql_data['stage_id'], 1);
                                sbr_meta::$save_reqv_frl = true;
                            }
                            break; 
                        case 2: // ������������
                            if(!sbr_meta::$save_reqv_emp) {
                                $this->setUserReqvHistory($this->data['emp_id'], $sql_data['stage_id'], 1);
                                sbr_meta::$save_reqv_emp = true;
                            }
                            break;
                        case 3: // ����
                            if(!sbr_meta::$save_reqv_frl) {
                                $this->setUserReqvHistory($this->data['frl_id'], $sql_data['stage_id'], 1);
                                sbr_meta::$save_reqv_frl = true;
                            }
                            if(!sbr_meta::$save_reqv_emp) {
                                $this->setUserReqvHistory($this->data['emp_id'], $sql_data['stage_id'], 1);
                                sbr_meta::$save_reqv_emp = true;
                            }
                            break;
                    }
                }
                return pg_fetch_result($res,0,0);
            }
            $this->_abortXact();
        }

        return false;
    }
    
    /**
     * ���������� ��������� ����� ����� �� �������� �����
     *
     * @return int
     */
    function regActNum() {
        if($res = pg_query(self::connect(false), "SELECT nextval('sbr_docs_num/acts')"))
            return pg_fetch_result($res,0,0);
        return NULL;
    }

    /**
     * ���������� ��������� ����� ������� �� ���������
     *
     * @return int
     */
    function regArbReportNum() {
        if($res = pg_query(self::connect(false), "SELECT nextval('sbr_docs_num/arb_reports')"))
            return pg_fetch_result($res,0,0);
        return NULL;
    }

    /**
     * ������ ���������� ����� ��� ������ ������ ������.
     * @return int
     */
    function regAgentRepNum() {
        if($res = pg_query(self::connect(false), "SELECT nextval('sbr_docs_num/agent_reports')"))
            return pg_fetch_result($res,0,0);
        return NULL;
    }


    /**
     * �������� ����� ��� ����������/�������������� ���������.
     * 
     * @param array $doc   ������ �� ���������.
     * @param boolean $is_edit   �������� �������������?
     * @return string   html-����� � ������.
     */
    function doc_form($doc, $stage_id = NULL, $is_edit=false) {
        $sbr = $this;
        ob_start();
        include($_SERVER['DOCUMENT_ROOT'].'/norisk2/tpl.docs-form.php');
        return ob_get_clean();
    }

    /**
     * ��������� ������� ��������� � ����.
     * 
     * @param string $content   ����� �����.
     * @param string $ext   ���������� �����.
     * @return CFile   ����������� ����.
     */
    function _saveDocFile($content, $ext = '.pdf') {
        if(trim($content) == "") return NULL;
        $file = new CFile();
        $file->table = 'file_sbr';
        $file->path = $this->getUploadDir();
        $file->name = basename($file->secure_tmpname($file->path,'.pdf'));
        $file->size = strlen($content);
        if($file->putContent($file->path.$file->name, $content))
            return $file;
        return NULL;
    }

    /**
     * ���������� PDF-�������� �� ��������� XML-����� offer2.xml
     *
     * @param  string $error ���������� ��������� �� ������
     * @return CFile   ����������� ����.
     */
    public function generateAgreement(&$error){
        $error = NULL;
        
        if($this->scheme_type == sbr::SCHEME_LC) {
            $template = "agreement_lc.xml";
        } else {
            $template = "offer2.xml";
        }
        $pdf = self::xml2pdf($_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/'.$template, array('$num' => $this->getContractNum()));
        
        if(!($file = $this->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ������ �� ���������� ����������";
        return $file;
    }
    
    /**
     * ���������� PDF-�������� �� ��������� XML-����� offer1.xml
     *
     * @param  string $error ���������� ��������� �� ������
     * @return CFile   ����������� ����.
     */
    public function generateContract(&$error){
        $error = NULL;
        
        if($this->scheme_type == sbr::SCHEME_LC) {
            $template = "offer_lc.xml";
        } else {
            $template = "offer1.xml";
        }
        $pdf = self::xml2pdf($_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/'.$template, array('$num' => $this->getContractNum()));
        if(!($file = $this->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ������ �� ���������� ��������";
        return $file;
    }
    
    /**
     * ���������� PDF-�������� �� ��������� XML-����� ow_emp.xml
     *
     * @param  string $error ���������� ��������� �� ������
     * @return CFile   ����������� ����.
     */
    public function genereteBailmentEmp(&$error){
        $error = NULL;
        $pdf = self::xml2pdf($_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/ow_emp.xml',array('$num' => $this->getContractNum()));
        if(!($file = $this->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ������ �� ���������� ����������";
        return $file;
    }

    /**
     * ���������� PDF-�������� �� ��������� XML-����� ow_frl.xml
     *
     * @param  string $error ���������� ��������� �� ������
     * @return CFile   ����������� ����.
     */
    public function genereteBailmentFrl(&$error){
        $error = NULL;
        $pdf = self::xml2pdf($_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/ow_frl.xml',array('$num' => $this->getContractNum()));
        if(!($file = $this->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ������ �� ���������� ����������";
        return $file;
    }

    /**
     * �������������� ������ XML ����
     * 
     * @param  string $text ������ XML ����
     * @param  bool $keep_text_wrap ���������� � true ���� ����� ��������� �������� �����
     * @return string
     */
    public static function prepareNodeText($node, $replacements = array(), $keep_text_wrap = false) {
        if($out = (string)$node->nodeValue) {
            $out = iconv('UTF-8', 'WINDOWS-1251', $out);
            if(!$keep_text_wrap) {
                $out = preg_replace('/[\r\n]+/', ' ', $out);
            }
            $out = preg_replace('/ {2,}/', ' ', $out);
            if ((int)$node->getAttribute('parse') && $replacements) {
                do {
                    $bef = $out;
                    $out = strtr($out, $replacements);
                } while($bef != $out);
            }

            $out = trim(htmlspecialchars_decode($out, ENT_QUOTES));
        }

        return $out;
    }

    /**
     * ���������� PDF-�������� �� ��������� XML-�����.
     *
     * @param string $file          ���� ��� ���������
     * @param mixed $replacements   ������ ��� ����������� ��������
     * @return FPDF �������������� �������� PDF ��� FALSE � ������ �������
     */
    public static function xml2pdf($file,$replacements=false){
        // ����� ��������� PDF
        require_once ($_SERVER['DOCUMENT_ROOT'].'/classes/odt2pdf.php');
        $tpl = basename($file, ".xml") .".odt";
        $t = new odt2pdf($tpl);
        $t->convert($replacements);
        return $t;
        
        /**
         * @deprecated
         */
        if (!file_exists($file))
            return false;
        require_once(dirname(__FILE__) . '/fpdf/fpdf.php');
        define('FPDF_FONTPATH', (dirname(__FILE__) . '/fpdf/font/'));
        if (is_array($replacements)) {
            foreach ($replacements as &$val) {
                $val = htmlspecialchars_decode($val, ENT_QUOTES);
            }
        }
        $replacements['$tab'] = '    ';
        $xml = new DOMDocument('1.0', 'windows-1251');
        $xml->load($file);
        $pdf=new FPDF();

		// ��������� ������
        $pdf->AddFont('ArialMT','','c9bb7ceca00657d007d343f4e34b71a8_arial.php');
        $pdf->AddFont('Arial-BoldMT','','9cb9fc616ba50d7ecc7b816984f2ffda_arialbd.php');
        $pdf->AddFont('TimesNewRomanPSMT','','5f37f1915715e014ee2254b95c0b6cab_times.php');
        $pdf->AddFont('TimesNewRomanPS-BoldMT', '', 'e07f6c05a47ebec50a80f29789c7f1f6_timesbd.php');

        /*
        ��������� XML-�������� � ������ �� ���� �������� ��������� ��� ��������� PDF-���������
        
        */
        $root = $xml->documentElement;
        $title = $root->getAttribute('title') ? iconv('windows-1251', 'utf-8', $root->getAttribute('title')) : '';// ��������� ���������
        $author = $root->getAttribute('author');// �����
        $margin_left = $root->getAttribute('margin-left') ? $root->getAttribute('margin-left') : 20;// ������ �����
        $margin_right = $root->getAttribute('margin-right') ? $root->getAttribute('margin-right') : 20;// ������ ������
        $margin_top = $root->getAttribute('margin-top') ? $root->getAttribute('margin-top') : 20;// ������ ������
        $font_name = $root->getAttribute ( 'font-name' ) ? $root->getAttribute ( 'font-name' ) : 'ArialMT';// ��������� ����� (���)
        $font_size = (int)$root->getAttribute ( 'font-size' ) ? (int)$root->getAttribute ( 'font-size' ) : 10;// ��������� ����� (������)
        $text_width = (int)$root->getAttribute ( 'width' ) ? (int)$root->getAttribute ( 'width' ) : 170;// ������ �������� ������� ���������
        $paragraph_indent = (int)$root->getAttribute ( 'paragraph-indent' ) ? (int)$root->getAttribute ( 'paragraph-indent' ) : 0;// ������ ����� �����������
        $printable = $pdf->h-$margin_top-20;

        $pdf->SetTitle($title, true);
        $pdf->SetAuthor($author);
        $pdf->SetLeftMargin($margin_left);
        $pdf->SetRightMargin($margin_right);
        $pdf->SetTopMargin($margin_top);
        $pdf->AddPage();
        $pdf->SetFont($font_name,'',$font_size);
        $pdf->SetX($margin_left);
		
        $locates = array();
        // ������ XML-���������
        $xpath = new DOMXPath ( $xml );
        $scale = $xpath->query('/document/page/*');
        if ($scale->length) {
            $footer = $xpath->query('//footer');
            $footer = $footer->length ? $footer->item(0) : NULL;
            
            $no_brake = $xpath->query('//nobreak');
            $no_brake = $no_brake->length ? TRUE : FALSE;
            
            // ���� ���� ���� <nobreak>, �� ����������� ������� ������� ������
            if ($no_brake) $pdf->SetAutoPageBreak(false);
            
            $last_y = 0;
            $pages = array();
            foreach ($scale as $node) {
                $last_y = intval($pdf->y);
                if ($node->tagName == 'nobreak' && $node->getAttribute('start')) {
                    $max_h = $last_y;
                    $loc_offset = 0;
                    foreach ($xpath->query('//cell|locate[(following::nobreak)]') as $i => $nd) {
                        if ($nd->tagName == 'nobreak' && $node->getAttribute('end')) {
                            break;
                        }
                        $_h = $nd->getAttribute('height');
                        if ($i >0 && !$loc_offset) {
                            $_h = 0;
                        }
                        $max_h += intval($_h);
                        $loc_offset = $nd->getAttribute('x_offset');
                    }
                    $max_h += $last_y;
                        
                    if ($max_h > $printable) {
                        
                        if ($footer) {
                            $pdf->SetY(-20);
                            $pdf->SetFont($font_name, '', 9);
                            $pdf->Cell(0, 10, self::prepareNodeText($footer), 0, 0, 'C');
                            $pages[] = $pdf->PageNo();
                        }
                        
                        $pdf->AddPage();
                    }
                }
                
                
                if ($no_brake && $pdf->y > $printable) {
                    
                    if ($footer && !in_array($pdf->PageNo(), $pages)) {
                        $pdf->SetY(-20);
                        $pdf->SetFont($font_name, '', 9);
                        $pdf->Cell(0, 10, self::prepareNodeText($footer), 0, 0, 'C');
                        $pages[] = $pdf->PageNo();
                    }
                    
                    $pdf->AddPage();
                }
                
                if (!(int) $node->getAttribute('keep-pos'))
                    $pdf->SetX($margin_left); // ����� ������� �� X-��� ���� <node keep-pos="0" ��� �� �����
                if ((int) $node->getAttribute('offset-left'))
                    $pdf->SetX((int) $node->getAttribute('offset-left') + $margin_left);
                if ($node->tagName == 'text') { // ����� ������
                    if ($node->getAttribute('font-name'))
                        $font_name = $node->getAttribute('font-name');
                    if ((int) $node->getAttribute('font-size'))
                        $font_size = (int) $node->getAttribute('font-size');
                    $align = $node->getAttribute('align') ? strtoupper($node->getAttribute('align')) : 'C';
                    $width = (int) $node->getAttribute('width') ? (int) $node->getAttribute('width') : $text_width;
                    $height = (int) $node->getAttribute('height') ? (int) $node->getAttribute('height') : 5;
                    $border = $node->getAttribute('border') ? strtoupper($node->getAttribute('border')) : 0;
                    $text = self::prepareNodeText($node, $replacements);
                    if (!($color = $node->getAttribute('color')))
                        $color = '000000';
                    $pdf->SetTextColor(hexdec(substr($color, 0, 2)), hexdec(substr($color, 2, 2)), hexdec(substr($color, 4, 2)));
                    $pdf->SetFont($font_name, '', $font_size);
                    $skip_empty = (int) $node->getAttribute('skip-empty') ? (int) $node->getAttribute('skip-empty') : 0;
                    if ((int) $skip_empty) {
                        if (!trim($text))
                            continue;
                    }
                    $pdf->Cell($width, $height, trim($text), $border, 1, $align);
                }elseif ($node->tagName == 'paragraph') { // ������� ������������� ��������� ����, ����� ��������� ��� ������������ ������ (L, J, R, C)
                    if ( ($show_if = $node->getAttribute('show-if')) && !$replacements[$show_if]) {
                        continue;
                    }
                    if ($node->getAttribute('font-name'))
                        $font_name = $node->getAttribute('font-name');

                    if ((int) $node->getAttribute('font-size'))
                        $font_size = (int) $node->getAttribute('font-size');
                    $align = $node->getAttribute('align') ? strtoupper($node->getAttribute('align')) : 'J';
                    $width = (int) $node->getAttribute('width') ? (int) $node->getAttribute('width') : $text_width;
                    $height = (int) $node->getAttribute('height') ? (int) $node->getAttribute('height') : 5;
                    $border = $node->getAttribute('border') ? strtoupper($node->getAttribute('border')) : 0;
                    $keep_text_wrap = (int) $node->getAttribute('keep-text-wrap') ? (int) $node->getAttribute('keep-text-wrap') : 0;
                    $text = self::prepareNodeText($node, $replacements, $keep_text_wrap);
                    if (!($color = $node->getAttribute('color')))
                        $color = '000000';
                    $pdf->SetTextColor(hexdec(substr($color, 0, 2)), hexdec(substr($color, 2, 2)), hexdec(substr($color, 4, 2)));
                    $pdf->SetFont($font_name, '', $font_size);
                    $skip_empty = (int) $node->getAttribute('skip-empty') ? (int) $node->getAttribute('skip-empty') : 0;
                    if ((int) $skip_empty) {
                        if (!trim($text))
                            continue;
                    }
                    $pdf->MultiCell($width, $height, $text, $border, $align);
                    if ($paragraph_indent)
                        $pdf->Ln($paragraph_indent);
                }elseif ($node->tagName == 'ln') { // ������� ������
                    $height = (int) $node->getAttribute('height') ? (int) $node->getAttribute('height') : 5;
                    $pdf->Ln($height);
                } elseif ($node->tagName == 'cell') { // ������ ������
                    if ($node->getAttribute('font-name'))
                        $font_name = $node->getAttribute('font-name');
                    if ((int) $node->getAttribute('font-size'))
                        $font_size = (int) $node->getAttribute('font-size');
                    $align = $node->getAttribute('align') ? strtoupper($node->getAttribute('align')) : 'J';
                    $width = (int) $node->getAttribute('width') ? (int) $node->getAttribute('width') : $text_width;
                    $height = (int) $node->getAttribute('height') ? (int) $node->getAttribute('height') : 5;
                    $border = $node->getAttribute('border') != '' ? (int) ($node->getAttribute('border')) : 1;
                    $keep_text_wrap = (int) $node->getAttribute('keep-text-wrap') ? (int) $node->getAttribute('keep-text-wrap') : 0;
                    $text = self::prepareNodeText($node, $replacements, $keep_text_wrap);
                    if (!($color = $node->getAttribute('color')))
                        $color = '000000';
                    $pdf->SetTextColor(hexdec(substr($color, 0, 2)), hexdec(substr($color, 2, 2)), hexdec(substr($color, 4, 2)));
                    $pdf->SetFont($font_name, '', $font_size);
                    $pdf->Cell($width, $height, $text, $border, 0, $align);
                }elseif ($node->tagName == 'locate') { // ���������� ��������� � ������������ ������� � ���������
                    $x = $node->getAttribute('x') ? $node->getAttribute('x') : 0;
                    $y = $node->getAttribute('y') ? $node->getAttribute('y') : 0;
                    $x_offset = (int) $node->getAttribute('x_offset') ? (int) $node->getAttribute('x_offset') : 0;
                    $y_offset = (int) $node->getAttribute('y_offset') ? (int) $node->getAttribute('y_offset') : 0;
                    if (strpos($x, '@') !== false)
                        $x = $locates['x'][$x] + $x_offset;
                    if (strpos($y, '@') !== false)
                        $y = $locates['y'][$y] + $y_offset;
                    if (!$x) {
                        $x = ($pdf->GetX() + $x_offset);
                    }
                    if (!$y) {
                        $y = ($pdf->GetY() + $y_offset);
                    }
                    $pdf->SetXY($x, $y);
                } elseif ($node->tagName == 'fix-locate') { // ���������� ��������� � ������������ ������� � ���������
                    if ($x = $node->getAttribute('x'))
                        $locates['x'][$x] = $pdf->GetX();
                    if ($y = $node->getAttribute('y'))
                        $locates['y'][$y] = $pdf->GetY();
                }elseif ($node->tagName == 'line') {// ������ ������� �������������� �����
                    $x = (int) $node->getAttribute('x') ? (int) $node->getAttribute('x') : $margin_left;
                    $y = (int) $node->getAttribute('y') ? (int) $node->getAttribute('y') : $margin_top;
                    $len = (int) $node->getAttribute('len') ? (int) $node->getAttribute('len') : $text_width;
                    if ($x)
                        $pdf->setX($x);
                    if ($y)
                        $pdf->setY($y);
                    $pdf->Cell($len, 0, '', 1);
                }elseif ($node->tagName == 'newpage') {//����� �������� � ������� ��������� �� ���
                    $pdf->AddPage();
                }
                
            }
        }
        return $pdf;
    }

    /**
     * ���������, ����� �� � ������ ������ ���� ������� ��� �����������.
     *
     * @return integer   0: �����,
     *                   1: �� �����, ������ ��� ���� �������� ������.
     */
    function checkChangeRT() {
        if(($sbr_info = sbr_meta::getUserInfo($this->uid)) && $sbr_info['all_cnt']) {
            // !!! ���������.
            if($this->isFrl()) {
                $arb_join = "
                  LEFT JOIN
                    sbr_stages_arbitrage sa
                      ON sa.stage_id = ss.id
                     AND sa.resolved IS NOT NULL
                     AND sa.frl_percent = 0
                ";
                $arb_cond = "AND sa.id IS NULL";
            }
            
            $admin_cond = "";
            if (hasPermissions('users')) {
                $admin_cond = " AND ?i IN (s.frl_id, s.emp_id) ";
                $admin_cond = $this->db()->parse($admin_cond, $this->uid);
            }
            
            $sql = "
              SELECT 1
                FROM sbr_stages_users su
              INNER JOIN
                sbr_stages ss
                  ON ss.id = su.stage_id
              INNER JOIN
                sbr s
                  ON s.id = ss.sbr_id
                 AND s.is_draft = false
                 AND s.status NOT IN (" . sbr::STATUS_CANCELED.','.sbr::STATUS_REFUSED.','.sbr::STATUS_NEW . ")
                 AND s.norisk_id IS NULL
                 {$admin_cond}
              {$arb_join}
               WHERE su.user_id = ?i
                 AND su.docs_received = false
                 AND su.is_removed = false
                 {$arb_cond}
               LIMIT 1
            ";
            
            $sql = $this->db()->parse($sql, $this->uid);     
                 
            if($res = pg_query(self::connect(false), $sql))
                return pg_num_rows($res);
        }
        return 0;
    }

    /**
     * ���������� ����������� �������� ������ ������� � �����.
     * @see exrates
     * @return float
     */
    function cost2rur() {
        if(!$this->exrates)
            $this->getExrates();
        return $this->exrates[$this->cost_sys . exrates::BANK];
    }

    /**
     * ��������� ������� � ����� ����� ����� ��
     *
     * @param  float $usd_sum ����� � ��������
     * @return float ����� � ������
     */
    function usd2rur($usd_sum) {
        $rates = getCBRates();
        return $usd_sum * str_replace(',','.',$rates['USD']['Value']);
    }
    
    
    /**
     * ���������� ����� �������������� � ����������� � ������������ �������� ������ � ������� ������ �������.
     * 
     * @param type $force   �����������
     * @param type $ps      �� ��������� ������� (�� exrates)
     * @return float        ����� ��������������.
     */
    function getReserveSum($force = false, $ps = null, $round = true) {
        if(!$this->reserve_sum || $force) {
            if($this->cost_sys) {
                // @todo #0020738 ������ �������� �� ��� ��� ��� ���������� �� 2 ����� �� ��������� ������� ����� �� �� �������� �����
                // ������� ���� ������ ��� �����������, ���������� ������������ ��� ���� ������.
                if($this->scheme_type == sbr::SCHEME_LC) {
                    $round = false;
                }
                $csum = $this->getTotalCost(false);
                $tsum = 0;
                foreach($this->scheme['taxes'][sbr::EMP] as $tax) {
                    $dvals = array('A' => NULL);
                    if ($ps) {
                        $dvals['P'] = $ps;
                        $tsum += sbr_meta::calcAnyTax($tax['tax_id'], $tax['scheme_id'], $csum, $dvals);
                    } else {
                        $tsum += $this->getTotalTax($tax, 1, $dvals, $round);
                    }
                }
                $this->reserve_sum = $round ? ( $csum + $tsum ) : round( $csum + $tsum , 2);
            }
        }
        return $this->reserve_sum;
    }
    
    /**
     * ���������� id ������� �� �� ����� (�� sbr_taxes.tax_code)
     * ���� scheme_id �����������, �� ������� ��������� �����, 
     * ���� ����������� ������ scheme_type - ������ ������ �� ���������� ����� ������� ����.
     * 
     * @param string|array  $codes       ������ ��� ������ � ����� ������ (�� sbr_taxes.tax_code)
     * @param integer       $role        
     * @return integer|array             ������ ��� �����, � ����������� �� �������� ������
     */
    function getTaxByCode ($codes = '', $role = null) {
        if (!$this->scheme) {
            $this->getScheme();
        }
        
        $res = array();
        if (is_array($codes)) {
            foreach ($codes as $code) {
                $res[$code] = $this->getTaxByCode($code, $role);
            }
            return $res;
        }
        
        if (!is_array($codes)) {
            $scheme = $this->scheme;
            
            if ($role) {
                $taxes = $scheme['taxes'][$role];
            } else {
                $taxes = array_merge($scheme['taxes'][sbr::EMP], $scheme['taxes'][sbr::FRL]);
            }
            foreach ($taxes as $tax) {
                if ($tax['tax_code'] == $codes) {
                    return $tax['id'];
                }
            }
        }
        
        return false;
    }
    
    /**
     * !!! ����� ������� ���� � ��.
     * ��������� � ������������
     *
     * @param integer $tax_id  �� �������� ������ @see table sbr_taxes
     * @return string
     */
    function getDescrTaxes($tax_id, $percent = "13", $is_emp = null) {
        if($is_emp === null) $is_emp = is_emp();
        
        switch($tax_id) {
            //�������� Free-lance.ru
            case 1:
            case 2:
            case 3:
                return "�����, ������� ���� Free-lance.ru ������� � ������������ �� ������������� ������� ����������� ������";
                break;
            // ���
            case 5:
                return "����� � ���� ����������� ����������� ";
                break;
            // ����    
            case 7:
            case 12:
            case 16:
            case 17:
                $descr = $is_emp ? "���� ���������� %s �� ����� ������ (�� ������� �������� Free-lance.ru)" : "���� ���������� %s �� ����� ������ (�� ������� �������� Free-lance.ru)";
                return sprintf($descr, $percent . "%");
                break;
            // ���    
            case 6:
            case 14:
            case 15:
                return $is_emp ? "��� ��������� � �������� ����� �������, ������� ��������� ������ � �������� Free-lance.ru" : "��� ��������� � �������� ����� ������� (�� ������� �������� Free-lance.ru)";
                break; 
            // ������� �� ����� (WMR)
            case 13:
                return "�������� �� ����� � ������.������ ��� Webmoney (�����)";
                break;    
            // ���
            case 8:
                return "������������ ������������� ��������� ������ � ���������� ���� ��";
                break;
            // �����
            case 9:
                return "����� � ����������� ���� ������������� ������������ ����������� �� ������������� ������";
                break; 
            // �����    
            case 10:
                return "����� � ��������������� ���� ������������� ������������ ����������� �� ������������� ������";
                break; 
        }
        
        return false;
    }
    
    /**
     * �������� ���� ��� ������ ������������ ���
     *
     * @param string $calc  ������ � serialize 
     * @return string
     */
    function getSbrCalcHash($calc) {
        return substr(md5($calc), 0, 9);
    }
    
    /**
     * ������ ������ ������� ��� ������������
     *
     * @param array  $calc    ������
     * @param string $hash    ������������ ���
     * @return array ���������� ������
     */
    
    function setSbrCalc($calc, &$hash) {
        global $DB;
        $calc = serialize($calc);
        $hash = sbr::getSbrCalcHash($calc);
        if(($info = sbr::getSbrCalc($hash)) == false) {
            $sql = "INSERT INTO sbr_calc (calc, hash) VALUES(?, ?)";
            $info = unserialize($calc);
            $DB->query($sql, $calc, $hash);
            return $info;
        }
        
        return $info;
    }
    
    /**
     * ����� ������ ������� ������������ ���
     *
     * @param string $hash    
     * @return string ������ � serialize
     */
    function getSbrCalc($hash) {
        global $DB;
        $sql = "SELECT calc FROM sbr_calc WHERE hash = ?";
        $res = $DB->val($sql, $hash);
            
        if($res) return unserialize($res);
        return false;
    }
    
    /**
     * �������� ���������������� ����� � ���������
     * 
     * @return boolean
     */
    function isEmpReservedMoney() {
        return ($this->status == sbr::STATUS_PROCESS && $this->stages_version == $this->frl_stages_version && $this->version == $this->frl_version && !$this->data['reserved_id'] && $this->isEmp());
    }
    
    /**
     * ����� ������� ������� ������� ��������� ��� ���������� � ������� �� ������ ��� �����
     * 
     * @param float $total_sum   ����� �������� ����� (� ���)
     * @return float
     */
    public function getRatingSum($total_sum, $percent='0.05') {
        return sbr_meta::getSBRRating($total_sum);//, $percent);
    }
    
    /**
     * ��������� ��������� ��� ������� ��� ���� ������� ��� ������ �� �� �������� ������� �������� ������
     * 
     * @global object $DB ����������� � ��
     * 
     * @param integer $payment ��� ������� @see class exrates()
     * @return boolean 
     */
    public function setTypePayment($payment) {
        global $DB;
        if($payment == exrates::WMR || $payment == exrates::YM || $payment == exrates::BANK || $payment == exrates::FM) {
            $u = $DB->update('sbr_stages', array('type_payment' => $payment), 'sbr_id = ?i', $this->id);
            return $u;
        }
        return false;
    }
    
    /**
     * ���������� ��� ���� ����� ���������� ����� ������ �� ������ � ������ 
     */
    public function sbrCanceledSaveEvent() {
        $XACT_ID = $this->_openXact(true);
        sbr_notification::sbr_add_event($XACT_ID, $this->id, $this->id, 'sbr.CANCEL', $this->version, null, 3);
        $this->_commitXact();
    }
    
    /**
     * ������������� ����� ������� (��� ������ ������ � ����� ���)
     * 
     * @param type $scheme 
     */
    public function setGetterSchemes($scheme = 1) {
        $this->getter_schemes = $scheme;
    }
    
    public function removeEvent($code = false) {
        global $DB;
        if(!$code) false;
        
        $sql = "DELETE FROM sbr_events WHERE sbr_id = ?i AND own_id = ?i AND ev_code = ?";
        $DB->query($sql, $this->id, $this->id, $code);
    }
    
    /**
     * ���� ������
     * @param array $filter ����� ���������� ������
     *      $filter['uid'] => ID ������������, ��� �������� ���� ������
     */
    public function searchSBR ($filter) {
        global $DB;
        if (!$filter || !is_array($filter)) {
            return array();
        }
        $sql = "SELECT s.id, s.scheme_type, s.posted, s.status, ss.name 
                    FROM sbr s
                    INNER JOIN sbr_stages ss
                        ON s.id = ss.sbr_id";
        
        if (isset($filter['uid'])) {
            $sql .= " WHERE";
            $sql .= $DB->parse(' (emp_id = ?i OR frl_id = ?i)', $filter['uid'], $filter['uid']);
        } else {
            return array();
        }
        
        $sql .= " ORDER BY s.posted DESC, ss.id ASC";
        
        $res = $DB->rows($sql);
        return $res;
    }
    
    public function checkEnableMethodPayments() {
        if($this->scheme_type != sbr::SCHEME_LC) return true;
        
        $this->stage_payout_ww = array();
        $this->stage_payout_other = array();
        
        foreach($this->stages as $key => $stage) {
            if($stage->cost <= pskb::WW_ONLY_SUM && $stage->tagPerf == pskb::PHYS) {
                $this->stage_payout_ww[$key] = sprintf('<span class="b-fon__txt b-fon__txt_bold">&laquo;%s&raquo;</span>', $stage->data['name']);
                $_method_ww = true;
            } else {
                $this->stage_payout_other[$key] = sprintf('<span class="b-fon__txt b-fon__txt_bold">&laquo;%s&raquo;</span>', $stage->data['name']);
                $_method_any = true;
            }
        } 
        
        $this->is_diff_method = ($_method_ww && $_method_any);
        $this->is_only_ww     = ($_method_ww && !$_method_any);
    }
    
    
    /**
     * ��������� �������� ���� �����������
     * 
     * @param type $rez_type
     * @return type
     */
    public static function getRezTypeText($rez_type)
    {
        return isset(self::$rez_list[$rez_type])? self::$rez_list[$rez_type] : '';
    }
    
    
    
    public static function isAllowDownloadFile($file_id, $uid)
    {
        global $DB;
        
        return $DB->val("SELECT 1 FROM sbr_docs AS sd 
                         INNER JOIN sbr AS s ON s.id = sd.sbr_id
                         WHERE sd.file_id = ?i AND (s.emp_id = ?i OR s.frl_id = ?i)", 
                $file_id, $uid, $uid);
    }
    
    
}

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr_frl.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr_emp.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr_adm.php';