<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/memBuff.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr_meta.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr_notification.php';

/**
 * ����� ��� ������ � ������� ������. ��� ������ ����������� ������� ��������� ��������������� �������������.
 * ������ �� ������ �������� ������� �� ����, ��� ���������������� ���������� $this->sbr.
 * @see sbr_meta::getInstance();
 */
class sbr_stages extends sbr_meta {

    const NAME_LENGTH = 100; // ������������ ����� �������� �����.
    const DESCR_LENGTH = 40000; // ������������ ����� �� �����.
    const MAX_COST_RUR = 3000000; // ������������ �������� ������� � ������.
    const MIN_COST_RUR = 300; // ����������� �������� ������� � ������.
    const MIN_COST_RUR_PDRD = 1000; // ����������� �������� ��� �������
    const MAX_MSG_FILES = 10; // ����������� ���-�� ������ � �������� � �������.
    const MAX_WORK_TIME = 365; // ������������ ����� �����.
    const MAX_MSG_EDIT_TIME = 600; // �����, ��������� ����� �������� �����������, ����� �������� ��������� ��������������.
    const MAX_ARBITRAGE_DAYS = 10; // ������������ ����� ������������ ��������� (� ����)

    // ������� ������.
    const STATUS_NEW         = 0; // �� �����.
    const STATUS_PROCESS     = 1; // � ����������.
    const STATUS_FROZEN      = 2; // ���������.
    const STATUS_INARBITRAGE = 3; // � ���������.
    const STATUS_COMPLETED   = 4; // ��������.
    const STATUS_ARBITRAGED  = 7; // ������ ����������.
    const STATUS_�LOSED      = 8; // ������ ��-�� ��������� (������ ��� �����������)

    const ATTACH_SOURCE_PRJ = 1; // ��� �������, ������ �� �������� �������.
    const ATTACH_SOURCE_OLD = 2; // ��� �������, ������ �� ���������� ������ �����.

    const ARB_FILE_MAX_SIZE  = 2097152; // ����. ������ ����� ��� �������� � ��������.

    /**
     * ��������� �������� ������.
     * @var array
     */
    static public $ss_classes = array (
        self::STATUS_NEW => array('grey', '��&nbsp;�����', ''),
        self::STATUS_PROCESS => array('yl', '�&nbsp;����������', 'ex-ylw'),
        self::STATUS_FROZEN => array('red', '���������', 'ex-red'),
        self::STATUS_INARBITRAGE => array('red', '�&nbsp;���������', 'ex-red'),
        self::STATUS_ARBITRAGED => array('red', '������', 'ex-red'),
        self::STATUS_COMPLETED => array('grn', '���������', 'ex-green')
    );
    
    /**
     * ��������� �������� ������ (����� ���).
     * 
     * @var array
     */
    static public $nss_classes = array (
        self::STATUS_NEW         => array('b-icon_sbr_stime', '��&nbsp;�����', ''),
        self::STATUS_PROCESS     => array('b-icon_sbr_bplay', '�&nbsp;������', 'ex-ylw'),
        self::STATUS_FROZEN      => array('b-icon_sbr_spause', '��&nbsp;�����', 'ex-red'),
        self::STATUS_INARBITRAGE => array('b-icon_sbr_avesy', '�&nbsp;���������', 'ex-red'),
        self::STATUS_ARBITRAGED  => array('b-icon_sbr_avesy', '��������&nbsp;����������', 'ex-red'),
        self::STATUS_COMPLETED   => array('b-icon_sbr_gok', '��������', 'ex-green'),
        self::STATUS_�LOSED      => array('b-icon_sbr_rdel', '������', '')
    );

    // ��� ������� � ��� � ����� ������������:
    static $arb_reasons = array (
      '6.1.2.' => '�������� �. 6.1.2. �������� �� ��������� ������ � $arb_report_num �� $arb_report_date � ���������� ������������ �������� �. 9 �������� ������� ������� � ���, ��� ������ ��������� ������������ ���������� ������� � � ����, � ����� � ����',
      '6.1.3.' => '�������� �. 6.1.3. �������� �������� � ����������� ��������� ���������� �� ������������� ������ ��������� ������ �����������, � ����� � ����',
      '7.1.1.' => '�������� �. 7.1.1 �������� �� ��������� �� ��������� � ��������������� ��������� ���� �������� �� ������������� ������ ��������� ������ ����������� � ����� � ����',
      '7.1.2.' => '�������� �. 7.1.2. �������� �� ��������� ������ � $arb_report_num �� $arb_report_date � ���������� ������������ �������� �. 9 �������� ������� ������� � ���, ��� ������ ��������� ������������ ������������ ������� �/��� �� � ����, � ����� � ����',
      '7.1.3.' => '�������� �. 7.1.3. �������� �������� � ����������� ��������� ���������� �� ������������� �������� ��������� ������ ���������, � ����� � ����',
      '7.1.4.' => '�������� �. 7.1.4. �������� ���������� ����������� ��� �������� ����������, � ����� � ����',
      '7.1.5.' => '�������� �. 7.1.5. �������� ������������ �� ���� ��������� ������� �. 6.3.2 � �. 6.3.3 �������� � ��������������� ��������� ����, � ����� � ����',
    );
    
    static $arb_new_reasons = array(
        '�. 8.3.'   => '�������� �. 8.3. ������� ��������� ��������� � ����������� ������', // ������ ����� ��������� ��������� - ��������
        '�. 8.5.'   => '�������� �. 8.5. �������� �� �������� ������', // ������ ����� ��������� ��������� - �����������
        '�. 9.1.2.' => 'C������� �. 9.1.2. ����������� ����������'
    );

    // ������������� ��������� ��� ������� � ����� ���������:
    static $arb_inits = array (
        '1) �������� ��� ����������� ���������� � �������� ��-�� �����������' => '������ 9.1 ��������: �������� � ����������� � ������ ������������� ����� ���� ����������� ������, � � �������, ��������������� � ��������, ������� ���������� � �������� ��� ����� ���������� ��������� ������������ ������� ������������ ����������� � ���������� ��������� ������ ���������� � ������������ �������.',
        '2) ������������ ��������� � �������� �� ������������� �������� ������ 8.2.' => '������ 9.1 ��������: �������� � ����������� � ������ ������������� ����� ���� ����������� ������, � � �������, ��������������� � ��������, ������� ���������� � �������� ��� ����� ���������� ��������� ������������ ������� ������������ ����������� � ���������� ��������� ������ ���������� � ������������ �������.
�
������ 8.2 ��������: �� �������, ��� � ������� 2 (����) ������� ���� � ������� �������� ���������� ������ �������� ��������� ����������� ��������� ������ � �������� ����������� � �������� ��� ������ ����������� ������� ����� � ���������� ���������� ������ ������������ � ����� ���������� ����� ������ ������������ (����� ������ �� ���������� ��������� ����� � ���� ������� �������� ����������), ��� ���������� � �������� � �������, ��������������� �. 9 ��������.',
        '3) ����������� ��������� � �������� �� ������������� �������� ������ 8.3.' => '������ 9.1 ��������: �������� � ����������� � ������ ������������� ����� ���� ����������� ������, � � �������, ��������������� � ��������, ������� ���������� � �������� ��� ����� ���������� ��������� ������������ ������� ������������ ����������� � ���������� ��������� ������ ���������� � ������������ �������.
� 
������ 8.3 ��������: � ������ ������������ ���������� �. 8.2 �������� [�� �������, ��� � ������� 2 (����) ������� ���� � ������� �������� ���������� ������ �������� ��������� ����������� ��������� ������ � �������� ����������� � �������� ��� ������ ����������� ������� ����� � ���������� ���������� ������ ������������ � ����� ���������� ����� ������ ������������ (����� ������ �� ���������� ��������� ����� � ���� ������� �������� ����������), ��� ���������� � �������� � �������, ��������������� �. 9 ��������] ����������� ��������� ��������������� ���������� � �������� � �������, ��������������� �. 9 ��������.',
    );
    
    static $arb_new_inits = array(
        '1) ����������� ��������� � ��������'   => '����������� ��������� � ��������',
        '2) �������� ��������� � ��������'      => '�������� ��������� � ��������',
        '3) ��� ���� ���������� � ��������'   => '��� ���� ���������� � ��������'
    );
    
    static $arb_new_results = array(
        '1) ���������� ������ 100% ������� ���������' => '���������� ������ 100% ������� ���������',
        '2) ���������� ������ 100% ������� �����������' => '���������� ������ 100% ������� �����������',
        '3) ���������� ������ n% ������� ��������� � n% ������� �����������' => '���������� ������ e% ������� ��������� � f% ������� �����������',
        '4) ���������� ������ ����������� ����������' => '���������� ������ ����������� ����������',
        '5) ������� ��������� 100% ������� ���������, �.�. ������ �� ���������' => '������� ��������� 100% ������� ���������, �.�. ������ �� ���������',
        '6) ������� ��������� 100% ������� ���������, �.�. ������ ��������� �� ���������, �� � ���� ' => '������� ��������� 100% ������� ���������, �.�. ������ ��������� �� ���������, �� � ���� ',
        '7) ������� ��������� 100% ������� �����������' => '������� ��������� 100% ������� �����������',
        '8) ������� ��������� n% ������� ��������� � n% ������� �����������' => '������� ��������� e% ������� ��������� � f% ������� �����������'
    );

    // ��������� ��������� ��� ������� � ����� ���������:
    static $arb_results = array (
        '1) �������� ���������� �� ������������� ������ ��������� ������ ����������� 100%' => '�� ��������� �. 9.4. �������� �������� ����������� ��� ��������� �� ����, ����� ��������� � ���������� � ������������, ��������, ����� ����������� ������������ ������ ������� ����� ����, � ����������� �� ���� ���������� ���� �� ������� ����������� �������������� �����. � ���������� �������� � ������������  �������� ���������� �� ������������� ������ ��������� ������ �����������.',
        '2) �������� ���������� �� ������������� �������� ��������� ������ ������������ 100%' => '�� ��������� �. 9.4. �������� �������� ����������� ��� ��������� �� ����, ����� ��������� � ���������� � ������������, ��������, ����� ����������� ������������ ������ ������� ����� ����, � ����������� �� ���� ���������� ���� �� ������� ����������� �������������� �����. � ���������� �������� � ������������ �������� ���������� �� ������������� �������� ��������� ������ ���������.',
        '3) �������� ���������� �� ������������� ���������� ��������/��������� ������' => '�� ��������� �. 9.4. �������� �������� ����������� ��� ��������� �� ����, ����� ��������� � ���������� � ������������, ��������, ����� ����������� ������������ ������ ������� ����� ����, � ����������� �� ���� ���������� ���� �� ������� ����������� �������������� �����. � ���������� �������� � ������������ �������� ���������� �� ������������� �������� ����� ��������� ������ ��������� � ������ ����� ��������� ������ �����������.',
        '4) �������� � ����������� ������ ����������� ����������, ������� ������������' => '�� ��������� �. 9.4. �������� �������� ����������� ��� ��������� �� ����, ����� ��������� � ���������� � ������������, ��������, ����� ����������� ������������ ������ ������� ����� ����, � ����������� �� ���� ���������� ���� �� ������� ����������� �������������� �����. � ���������� �������� � ������������ ������ ����������� ����������.',
        '5) ������������ �� ���� ��������� ������, ������� ������������' => '� ������������ � �. 9.5. �������� ������� ����������� ������ ������������ ����������� ������ ���������� � ������������ �������. ������� ����� ������, �������� ����������� �� � �������� �������� ��� ��������, � ������������� � �������� ����������� �������, ������� �� ��������� �������� ������������ ����������� ����� ������, �������������� ����������� ����������� ���������� � �����������.
������ �� ������� ����������, ��������, ������������ �������, ��������� ��������� �� ���������� ��������� ����� ����� ���������� � ������������ � ����� � �������, � ����� � ������ ����������������� ������ ����������� �� ������ ��������������� �� ����� �� ������� ���������� �� ������ �������� ����������� �� ����� (� ���������, ��� ������ ������� �������� ����� �������������, �� ����� ������� ���������� ������ �������� ��� ������������ ��� ���������� ���������� � ���������� ��������) ��������� ���� ����������� ���������:
������������ �� ���� ��������� ������.',
        '6) ������������ ���� ��������� ������ ������������ �������, �������  ������������' => '� ������������ � �. 9.5. �������� ������� ����������� ������ ������������ ����������� ������ ���������� � ������������ �������. ������� ����� ������, �������� ����������� �� � �������� �������� ��� ��������, � ������������� � �������� ����������� �������, ������� �� ��������� �������� ������������ ����������� ����� ������, �������������� ����������� ����������� ���������� � �����������.
������ �� ������� ����������, ��������, ������������ �������, ��������� ��������� �� ���������� ��������� ����� ����� ���������� � ������������ � ����� � �������, � ����� � ������ ����������������� ������ ����������� �� ������ ��������������� �� ����� �� ������� ���������� �� ������ �������� ����������� �� ����� (� ���������, ��� ������ ������� �������� ����� �������������, �� ����� ������� ���������� ������ �������� ��� ������������ ��� ���������� ���������� � ���������� ��������) ��������� ���� ����������� ���������:
������������ ���� ��������� ������ ������������ �������, � ������ __________________________________.
������ ��������� ������������ ������� �/��� �� � ����, ��������������� ����������� � ����������� �������� (�� ��������� �. 9.8.2 ��������).',
        '7) ������������ ���� ��������� ������ ���������� �������, 100% �����������' => '� ������������ � �. 9.5. �������� ������� ����������� ������ ������������ ����������� ������ ���������� � ������������ �������. ������� ����� ������, �������� ����������� �� � �������� �������� ��� ��������, � ������������� � �������� ����������� �������, ������� �� ��������� �������� ������������ ����������� ����� ������, �������������� ����������� ����������� ���������� � �����������.
������ �� ������� ����������, ��������, ������������ �������, ��������� ��������� �� ���������� ��������� ����� ����� ���������� � ������������ � ����� � �������, � ����� � ������ ����������������� ������ ����������� �� ������ ��������������� �� ����� �� ������� ���������� �� ������ �������� ����������� �� ����� (� ���������, ��� ������ ������� �������� ����� �������������, �� ����� ������� ���������� ������ �������� ��� ������������ ��� ���������� ���������� � ���������� ��������) ��������� ���� ����������� ���������:
������������ ���� ��������� ������ ���������� �������, � ������ ������ ���� ��������� � ���� � � ������������ � ��������� ������, ������������� ���������� � ������������ � ����������� �������.
� ���������� ������������ ��������� � �� ��������� �. 9.8 �������� �������� ������� ������������� �������:
������ ��������� ���������� ������� � � ���� � ������������ � ����������� � ����������� �������� (�� ��������� �. 9.8.1 ��������).',
    );


    /**
     * ������ ������ �����.
     * @var sbr
     */
    public $sbr;

    /**
     * ������ �� ����� (���� sbr_stages + ������ � ��.)
     * @var array
     */
    public $data = array();

    /**
     * ������ ��� �������� ������ ������ �����.
     * @var array
     */
    public $v_data = array();

    /**
     * ������ ����������� ������ �� ������.
     * @var array
     */
    public $uploaded_files;

    /**
     * ������ ������ ��� �������� ��� ������.
     * @var array
     */
    public $error = array();


    /**
     * ������ ��� �������� ������ �� ��������/�������������� ��������� � �������.
     * @var array
     */
    public $post_msg;

    /**
     * ������ � ����������� � �������� �� �����, ��������������� ��. ����� (���������� ��� ������������).
     * @var array
     */
    public $payouts;

    /**
     * ������ � ����������� �� ���������. ���� ===false, �� �� ��� ���������������.
     * @var array
     */
    public $arbitrage = false;

    /**
     * ������ ��� �������� ������ ������ ����� �������.
     * @var array
     */
    public $feedback;

    /**
     * ������ ��� �������� ����� ���������������� ��������.
     * @var array
     */
    public $request;




    /**
     * �����������.
     *
     * @param sbr $sbr   ������ ������ �����.
     * @param array $data   ������ �����.
     */
    function __construct($sbr, $data = NULL) {
        $this->sbr = $sbr;
        $this->data = $data;
    }

    /**
     * ��������� ������ $this->data �� ������ ����������������� �������.
     *
     * @param array $request   $_POST|$_GET
     * @return boolean   ��� �� ������ ������� ���� (������ ���� ��������� ������������ ����).
     */
    function initFromRequest($request) {
        $data_exists = false;
        foreach($request as $field=>&$value) {
            $err = NULL;
            if(is_scalar($value))
                $value = stripslashes($value);
            if(!$data_exists) { 
                if(in_array($field, array('name', 'descr', 'cost', 'work_time', 'add_work_time', 'project_attach'))) {
                    $data_exists = $value && (!is_string($value) || trim($value));
                }
            }
            switch($field) {
                    
                case 'name' :
                    if(!$this->sbr->isDraft() && is_empty_html($value)) {
                        $err = '����������, ��������� ��� ����';
                    }
                    $value = substr(trim($value), 0, self::NAME_LENGTH);
                    break;

                case 'descr' :
                    if(!$this->sbr->isDraft() && is_empty_html($value)) {
                        $err = '����������, ��������� ��� ����';
                    }
                    //$value = htmlspecialchars($value);
                    $value = substr($value, 0, self::DESCR_LENGTH);
                    break;

                case 'category' :
                case 'sub_category' :
                    $value = intvalPgSql($value);
                    break;

                case 'cost' :
                    if(is_empty_html($value)) {
                        if(!$this->sbr->isDraft())
                            $err = '������� �����';
                    }
                    else {
                        $cost = floatval(preg_replace('/\s+/', '', $value));
                        $cost_rur = $cost * $this->sbr->cost2rur();
                        if($cost_rur > self::MAX_COST_RUR)
                            $err = '������� ������� �����';
                        else if(($cost_rur < (self::MIN_COST_RUR - $this->sbr->isDraft()) && $this->data['cost'] != $cost) || $cost == 0)
                            $err = '����������� ������ &mdash; ' . self::MIN_COST_RUR . ' ���.';
                        else {
                            $this->sbr->getFrlReqvs();
                            if($this->sbr->frl_reqvs['rez_type']==sbr::RT_UABYKZ) {
                                if($cost_rur > $this->sbr->maxNorezCost())
                                    $err = '��������� ������������ ����� ����� &mdash; ' . sbr::MAX_COST_USD . ' USD (��� ' . sbr_meta::view_cost($this->sbr->maxNorezCost(), exrates::BANK) .')';
                            }
                        }
                        $value = $cost;
                    }
                    break;

                case 'work_time' :
                    if(!$this->sbr->isDraft() && is_empty_html($value)) {
                        $err = '����������, ��������� ��� ���� (����� �� 1 �� '.self::MAX_WORK_TIME.')';
                    } else {
                        $val = intvalPgSql($value);
                        if($val < (1-$this->sbr->isDraft()))
                            $err = '�������� ����';
                        else if($val > self::MAX_WORK_TIME)
                            $err = '����� �� ����� ���� ������ '.self::MAX_WORK_TIME;
                        if(!$this->sbr->isDraft())
                            $value = $val;
                    }
                    $request['work_days'] = $value;
                    $request['int_work_time'] = intval($value);
                    break;

                case 'work_time_add' :
                case 'add_work_time' :
                    $value = intvalPgSql($value);
                    break;

                case 'add_wt_switch' :
                    $value = $value == '-' ? '-' : '+';
                    break;

                case 'project_attach' :
                    if(is_array($value) && $this->sbr->project) {
                        if($this->sbr->project['attach']) {
                            $this->data['attach'] = array_intersect_key($this->sbr->project['attach'], $value);
                        }
                    }

                    break;

                case 'del_attach' :
                    break;

                case 'id' :
                    $value = intvalPgSql($value);
                    break;

                case 'version' :
                    $value = (int)$value > 32767 ? 32767 : (int)$value;
                    break;
            }
            if($err)
                $this->error[$field] = $err;
            $this->data[$field] = $value;
        }


        return $data_exists;
    }


    /**
     * �������� ������ ���������� ��������� �����, ����� ����, ��� ����������� ���������.
     *
     * @return boolean   �������?
     */
    function resendChanges() {
        $sql = "UPDATE sbr_stages SET frl_version = version WHERE id = {$this->id} AND version < frl_version";
        if($res = $this->_eventQuery($sql, false)) 
            $res = $this->sbr->resendChanges();
        if($res)
            return $this->_commitXact();
        $this->_abortXact();
        return false;
    }

    /**
     * �������� ������������ �� ��������� ����� ��������� � �����, ����� ����, ��� ����������� �� ��� ���������.
     *
     * @return boolean   �������?
     */
    function cancelChanges() {
        $sql = "UPDATE sbr_stages SET version = frl_version WHERE id = {$this->id} AND version < frl_version";
        if($res = $this->_eventQuery($sql, false)) 
            $res = $this->sbr->cancelChanges();
        if($res) {
            $event_upd = array('sbr_stages.FRL_ROLLBACK');
            sbr_notification::setNotificationCompleted(array('sbr_stages.FRL_ROLLBACK', 'sbr.FRL_ROLLBACK', 'sbr_stages.REFUSE'), $this->data['sbr_id'], $this->id);
            return $this->_commitXact();
        }
        $this->_abortXact();
        return false;
    }

    /**
     * ����������� ������������ �� ���������.
     *
     * @param integer $version   ������ ����� �� ������ ������ (��, ������� ������ ����� �����������).
     * @param string $reason   ������� ������.
     * @return boolean   �������? ���� �������� ����� ������ ����� ���������, �� false.
     */
    function refuseChanges($version, $reason = '', $sbr_version = NULL) {
        $sql = "UPDATE sbr_stages SET version = frl_version, frl_refuse_reason = '{$reason}' WHERE id = {$this->id} AND version = {$version}";
        if($res = $this->_eventQuery($sql, false)) {
            if($sbr_version) 
                $res = $this->sbr->refuseChanges($sbr_version);
        }
        if($res)
            return $this->_commitXact();
        $this->_abortXact();
        return false;
    }


    /**
     * ����������� ����������� � �����������.
     *
     * @param integer $version   ������ ����� �� ������ ������ (��, ������� ������ ����� �����������).
     * @return boolean   �������?
     */
    function agreeChanges($version, $sbr_version = NULL) {
        $this->v_data = $this->getVersion($this->frl_version, $this->data);
        
        $sql = "UPDATE sbr_stages SET frl_version = {$version} WHERE id = {$this->id} AND sbr_id = {$this->sbr->id} AND frl_version <> {$version}";
        if($res = $this->_eventQuery($sql, false)) {
            if($sbr_version) 
                $res = $this->sbr->agreeChanges($sbr_version);
        }
        if($res) {
            if($this->sbr->scheme_type == sbr::SCHEME_LC) {
                $pskb = new pskb($this->sbr);
                $lc   = $pskb->getLC();

                $v_day = intval($this->v_data['work_days']);
                $day = intval($this->data['work_days']);

                $add_day = $day - $v_day;
                if($add_day > 0) {
                    $pskb->prolongLC($lc['lc_id'], $add_day);
                }
            }
            return  $this->_commitXact();
        }
        $this->_abortXact();
        return false;
    }


    /**
     * ����� ������� ������ �������� ������ (�������� � ���������� $this->data � ������� �� ��������� ������).
     *
     * @param integer $version   ������ �����.
     * @param array $old_data    ������ ������� ������ ($this->data).
     * @return array   
     */
    function getVersion($version, &$old_data) {
        $sql = "
          SELECT se.*, ssv.ev_code, ssv.ev_name, ssv.rel, ssv.col, ssv.old_val, ssv.new_val
            FROM sbr_events se
          INNER JOIN
            vw_sbr_stages_versions ssv
              ON ssv.event_id = se.id
           WHERE se.sbr_id = ?i
             AND se.own_id = ?i
             AND se.version > ?i
           ORDER BY se.version DESC, se.xact_id DESC
        ";

        $sql = $this->db()->parse($sql, $this->sbr->id, $this->id, $version);   
             
        if($old_data['attach']) {
            foreach($old_data['attach'] as $aid=>$v)
               $old_data['attach_diff'][$aid] = 1;
        }

        $vdata = $old_data;

        // !!! � ��� ���� �������� ������ ��������?! ����� ����� ���-������ ���������, ��� �������� ��������� ���.
        if($res = pg_query(self::connect(), $sql)) {
            while($row = pg_fetch_assoc($res)) {
                if($row['rel'] == 'sbr_stages_attach') {
                    if($row['col']=='id') {
                        if($row['old_val']) {
                            if(!($a = $vdata['attach'][$row['old_val']]))
                                $a = current($this->getAttach($row['old_val'], true));
                            $a['source_type'] = sbr_stages::ATTACH_SOURCE_OLD;
                            $a['is_deleted'] = 'f';
                            $vdata['attach'][$row['old_val']] = $a;
                            $vdata['attach_diff'][$row['old_val']] = 1;
                        }
                        else {
                            $a = $vdata['attach'][$row['new_val']];
                            $a['source_type'] = sbr_stages::ATTACH_SOURCE_OLD;
                            $a['is_deleted'] = 't';
                            $vdata['attach'][$row['new_val']] = $a;
                            unset($vdata['attach_diff'][$row['new_val']]);
                        }
                    }
                }
                if($row['rel'] == 'sbr_stages') {
                    $ov = $row['old_val'];
                    if($row['col'] == 'work_time') {
                        $ov = (int)$ov;
                        $vdata['work_days'] = $ov;
                        $vdata['work_rem'] = $vdata['work_days'];
                        if($vdata['start_time']) {
                            $stime = strtotime($vdata['start_time']);
                            $vdata['dead_time'] = date('Y-m-d H:i:s', $stime + $ov*3600*24);
                            $vdata['work_rem'] = (strtotime(date('Y-m-d', $stime)) + $ov*3600*24 - strtotime(date('Y-m-d')))/3600/24;
                        }
                    }
                    if($row['col'] == 'start_time') {
                        if($vdata['work_time']) {
                            if($ov == null) $ov = $row['new_val'];
                            $vt = (int)$vdata['work_time'];
                            $stime = strtotime($ov);
                            $vdata['dead_time'] = $ov ? date('Y-m-d H:i:s', strtotime($ov) + $vt*3600*24) : NULL;
                            $vdata['work_rem'] = (strtotime(date('Y-m-d', $stime)) + $vt*3600*24 - strtotime(date('Y-m-d')))/3600/24;
                        }
                    }
                    $vdata[$row['col']] = $ov;
                }
            }
        }

        return $vdata;
    }


    /**
     * �������� ������ ����� (���������, � ���������� � �.�.).
     *
     * @param integer $status   ��� ������� (��. ���������).
     * @param integer $day      ���� �����, ���������� ������ ��� ������� sbr_stages::STATUS_FROZEN � ������������ ������ � ���� ��������
     * @return boolean   �������?
     */
    function changeStatus($status, $day = 0) {
        $status = (int)$status;
        if(!sbr_stages::$ss_classes[$status])
            return false;
        if($this->status == self::STATUS_COMPLETED
           || $this->status == self::STATUS_INARBITRAGE
           || $this->status == self::STATUS_ARBITRAGED
           || $status == self::STATUS_COMPLETED && ($this->frl_version != $this->version || $this->sbr->frl_version != $this->sbr->version)
           )
        {
            // ��� ������ �������� ������ ������ ������. ������ �� ������.
            return true;
        }
        
        // ��������� ������ ��������� ������ �� ������������ ���������� �������
        if($status == sbr_stages::STATUS_FROZEN) { 
            $sql = "SELECT sbr_trigger_fvrs_gt_vrs('sbr_stages', {$this->data['id']}); UPDATE sbr_stages SET status = {$status}, start_pause = NOW(), days_pause = {$day} WHERE id = {$this->data['id']}";
        } else {
            $sql = "SELECT sbr_trigger_fvrs_gt_vrs('sbr_stages', {$this->data['id']}); UPDATE sbr_stages SET status = {$status} WHERE id = {$this->data['id']}";
        }

        return $this->_eventQuery($sql);
    }


    /**
     * ������������ ������, ������� ����� �������������� � ������� ����������/�������������� �����.
     *
     * @return array   ������������ ����� $this->data.
     */
    function _preSql() {
        $data = $this->data;
        array_walk($data, array($this, '_preSqlCallback'));
        $data['category'] = $data['category'] ? $data['category'] : 'NULL';
        $data['sub_category'] = $data['sub_category'] ? $data['sub_category'] : 'NULL';
        $data['work_time'] = (int)$data['work_time'];
        $data['int_work_time'] = (int)$data['work_time'];
        $data['cost'] = (float)$data['cost'];
        $data['status'] = (int)$data['status'];

        return $data;
    }

    /**
     * @see sbr_stages::_preSql() 
     */
    function _preSqlCallback(&$value, $field) {
        if(is_string($value)) {
            $value = pg_escape_string(change_q_x($value, $field!='descr', false, 'b|br|i|p|cut|s|h[1-6]'));
        }
    }


    /**
     * ������� ����� ����. ���������� ������� -- ��� ������ ������ ���� ���������� �������.
     *
     * @return boolean   �������?
     */
    public function create() {
        if(!self::$XACT_ID) return false;
        if($this->data['attach']) {
            foreach($this->data['attach'] as $a) {
                if($a['source_type']==self::ATTACH_SOURCE_PRJ) {
                    $file = new CFile($a['file_id']);

                    $file->table = 'file_sbr';
                    if($file->_remoteCopy($this->sbr->getUploadDir().$file->name))
                        $this->uploaded_files[] = $file;
                }
            }
        }
        
        $sql_data = $this->_preSql();
        $sql = "
          INSERT INTO sbr_stages(sbr_id, name, descr, category, sub_category, status, cost, work_time, num)
          VALUES({$this->sbr->data['id']}, '{$sql_data['name']}', '{$sql_data['descr']}', {$sql_data['category']}, {$sql_data['sub_category']}, 0, {$sql_data['cost']}, '{$sql_data['work_time']} days'::interval, {$sql_data['num']})
          RETURNING id;
        ";
        if(!($res = pg_query(self::connect(false), $sql))) {
            return false;
        }
        $sql_attach = '';
        $sql_data['id'] = pg_fetch_result($res,0,0);
        if($this->uploaded_files) {
            foreach($this->uploaded_files as $file) {
                if(!$file->id) continue;
                $orig_filename = $file->orig_name;
                if(strlen($orig_filename)>254) {
                    $orig_file_info = pathinfo($orig_filename);
                    $orig_filename = substr($orig_file_info['filename'], 0, 254-($orig_file_info['extension'] ? (strlen($orig_file_info['extension'])+1) : 0)).($orig_file_info['extension'] ? ".{$orig_file_info['extension']}" : "");
                }
                $sql_attach .= "INSERT INTO sbr_stages_attach(stage_id, file_id, orig_name) VALUES({$sql_data['id']}, {$file->id}, '{$orig_filename}');";
            }
        }
        if($sql_attach && !pg_query(self::connect(false), $sql_attach)) {
            return false;
        }

        return true;
    }


    /**
     * ����� ���
     * ������� ����� ����. ���������� ������� -- ��� ������ ������ ���� ���������� �������.
     *
     * @return boolean   �������?
     */
    public function _new_create() {
        if(!self::$XACT_ID) return false;
        if($this->data['attach']) {
            foreach($this->data['attach'] as $a) {
                if($a['source_type']==self::ATTACH_SOURCE_PRJ) {
                    $file = new CFile($a['file_id']);

                    $file->table = 'file_sbr';
                    if($file->_remoteCopy($this->sbr->getUploadDir().$file->name))
                        $this->uploaded_files[] = $file;
                }
            }
        }

        $sql_data = $this->_preSql();
        $sql = "
          INSERT INTO sbr_stages(sbr_id, name, descr, category, sub_category, status, cost, work_time, int_work_time, num)
          VALUES({$this->sbr->data['id']}, '{$sql_data['name']}', '{$sql_data['descr']}', {$sql_data['category']}, {$sql_data['sub_category']}, 0, {$sql_data['cost']}, '{$sql_data['work_time']} days'::interval, '{$sql_data['int_work_time']}', {$sql_data['num']})
          RETURNING id;
        ";
        if(!($res = pg_query(self::connect(false), $sql))) {
            return false;
        }
        $sql_attach = '';
        $sql_data['id'] = pg_fetch_result($res,0,0);
        $this->id = $sql_data['id'];
        if($this->uploaded_files) {
            foreach($this->uploaded_files as $file) {
                if(!$file->id) continue;
                $orig_filename = $file->original_name;
                if(strlen($orig_filename)>254) {
                    $orig_file_info = pathinfo($orig_filename);
                    $orig_filename = substr($orig_file_info['filename'], 0, 254-($orig_file_info['extension'] ? (strlen($orig_file_info['extension'])+1) : 0)).($orig_file_info['extension'] ? ".{$orig_file_info['extension']}" : "");
                }
                $sql_attach .= "INSERT INTO sbr_stages_attach(stage_id, file_id, orig_name) VALUES({$sql_data['id']}, {$file->id}, '{$orig_filename}');";
            }
        }
        if($sql_attach && !pg_query(self::connect(false), $sql_attach)) {
            return false;
        }
        // ��������� �������� ���������� ���
        $memBuff = new memBuff;
        $memBuff->delete(self::$memBuff_prefix . '0' . $this->uid);
        $memBuff->delete(self::$memBuff_prefix . '1' . $this->uid);
        return true;
    }

    /**
     * ����������� ����. ���������� ������� -- ��� ������ ������ ���� ���������� �������.
     *
     * @return boolean   �������?
     */
    public function edit() {
        if($this->status == self::STATUS_COMPLETED || $this->status == self::STATUS_INARBITRAGE || $this->status == self::STATUS_ARBITRAGED)
            return false;

        if(!self::$XACT_ID) return false;

        $sql_data = $this->_preSql();
        $work_time = $sql_data['add_wt_switch'] ? $sql_data['work_time'] + (int)($sql_data['add_wt_switch'].'1')*(int)$sql_data['add_work_time'] : $sql_data['work_time'];
        
        $sql = "
          UPDATE sbr_stages
             SET name = '{$sql_data['name']}',
                 descr = '{$sql_data['descr']}',
                 category = {$sql_data['category']},
                 sub_category = {$sql_data['sub_category']},
                 cost = {$sql_data['cost']},
                 status = {$sql_data['status']},
                 work_time = '{$work_time} days'::interval,
                 int_work_time = '{$work_time}',
                 num = {$sql_data['num']}
           WHERE id = {$this->id}
             AND sbr_id = {$this->sbr->id}
        ";

        if(!($res = pg_query(self::connect(false), $sql)) || !pg_affected_rows($res))
            return false;
            
        $sql_attach = '';
        if($this->uploaded_files) {
            foreach($this->uploaded_files as $file) {
                if(!$file->id) continue;
                $orig_filename = $file->orig_name ? $file->orig_name : $file->original_name;
                if(strlen($orig_filename)>254) {
                    $orig_file_info = pathinfo($orig_filename);
                    $orig_filename = substr($orig_file_info['filename'], 0, 254-($orig_file_info['extension'] ? (strlen($orig_file_info['extension'])+1) : 0)).($orig_file_info['extension'] ? ".{$orig_file_info['extension']}" : "");
                }
                $sql_attach .= "INSERT INTO sbr_stages_attach(stage_id, file_id, orig_name) VALUES({$sql_data['id']}, {$file->id}, '{$orig_filename}');";
            }
        }
        
        if($sql_data['del_attach']) {
            foreach($sql_data['del_attach'] as $id=>$is_deleted) {
                if($is_deleted=='f')
                    $sql_attach .= "UPDATE sbr_stages_attach SET is_deleted = '{$is_deleted}' WHERE id = {$id};";
                else $del_attach[] = (int)$id;
            }
        }
        
        if($sql_attach && !pg_query(self::connect(false), $sql_attach))
            return false;

        if($del_attach && !$this->delAttach(implode(',',$del_attach)))
            return false;
        
        if ($sql_data['_new_del_attach'] && !$this->_new_delAttach($sql_data['_new_del_attach'])) {
            return false;
        }

        ////////////////////////
        pg_query(self::connect(false), "SELECT sbr_trigger_fvrs_gt_vrs('sbr_stages', {$this->id})");
        ////////////////////////

        return true;
    }


    /**
     * ������� ����. ���� �� ��������, �� ������ �������� ��� ���������. ������ ������ ���� ������� ������� ���������� �������.
     *
     * @param boolean $full   ������� �������, ������ � ��������.
     * @param boolean $force   ���� false, �� �������� ��������������, � ������, ���� ��������� ���������� ����, � ������� ��� �� ���������� ���������. ����� ��������� ��� ��������.
     * @return boolean   �������?
     */
    function delete($full = false, $force = false) {
        if(!$this->id) return false;
        if(!self::$XACT_ID) return false;
//        if($this->sbr->stages_cnt == 1) return false;
        if($full) {
            $this->delAttach(NULL, true);
            $sql = "DELETE FROM sbr_stages WHERE id = {$this->id}";
        }
        else {
            if(!$force && $this->version > $this->frl_version) {
                $this->error['version'] = '������ ������� ������ � �����������, �� �������������� ������������.'; // !!! � �������������� ���������� � �������, ���� �������� �� ������ ��������� �� $force,
                return false;
            }
            $sql = "
              SELECT sbr_trigger_fvrs_gt_vrs('sbr_stages', {$this->id}); 
              UPDATE sbr_stages
                 SET is_deleted = true
               WHERE id = {$this->id}
            ";
        }

        return !!pg_query(self::connect(),$sql);
    }

    /**
     * ���������� �������� �������� ����� �� ���������� ���������������.
     *
     * @param integer|string $attach_id   ���� ��� ��������� ��., ����������� ��������. ���� NULL, �� ������� ���.
     * @param boolean $get_deleted   ����� ���������?
     * @return array   ������ �������� CFile, ��������������� ��. �������.
     */
    function getAttach($attach_id = NULL, $get_deleted = false) {
        $ret = NULL;
        $where = "sa.stage_id = {$this->data['id']}" . ($attach_id == NULL ? '' : " AND sa.id IN ({$attach_id})");
        $where_deleted = $get_deleted!==NULL ? " AND sa.is_deleted = '".(int)$get_deleted."'" : '';
        $sql = "
          SELECT sa.*, f.fname as name, f.path, f.size, f.modified as sign_time
            FROM sbr_stages_attach sa
          INNER JOIN
            file_sbr f
              ON f.id = sa.file_id
           WHERE {$where} {$where_deleted}
           ORDER BY sa.stage_id
        ";
        if(($res = pg_query(DBConnect(),$sql)) && pg_num_rows($res)) {
            while($row = pg_fetch_assoc($res)) {
                $row['ftype'] = CFile::getext($row['name']);
                $ret[$row['id']] = $row;
            }
        }
        return $ret;
    }
    
    /**
     * ���������� ��� �������� �������� ����� �� ���������� ���������������.
     *
     * @param integer|string $attach_id   ���� ��� ��������� ��., ����������� ��������. ���� NULL, �� ������� ���.
     * @param boolean $get_deleted   ����� ���������?
     * @return array   ������ �������� CFile, ��������������� ��. �������.
     */
    function _new_getAttach($attach_id = NULL, $get_deleted = false) {
        $ret = NULL;
        $where = "sa.stage_id = {$this->data['id']}" . ($attach_id == NULL ? '' : " AND sa.id IN ({$attach_id})");
        $where_deleted = $get_deleted!==NULL ? " AND sa.is_deleted = '".(int)$get_deleted."'" : '';
        $sql = "
          SELECT sa.stage_id, sa.file_id as id, f.original_name as name, f.fname as file_name, f.path as file_path, f.size as file_size, f.modified as sign_time
          FROM sbr_stages_attach sa
          INNER JOIN file_sbr f ON f.id = sa.file_id
          WHERE {$where} {$where_deleted}
          
          UNION 

          SELECT sm.stage_id, sma.file_id as id, f.original_name as name, f.fname as file_name, f.path as file_path, f.size as file_size, f.modified as sign_time
          FROM sbr_stages_msgs sm
          INNER JOIN sbr_stages_msgs_attach sma ON sma.msg_id = sm.id
          INNER JOIN file_sbr f ON f.id = sma.file_id
          WHERE sm.stage_id = {$this->data['id']}
          
          ORDER BY sign_time ASC
        ";
          
        if(($res = pg_query(DBConnect(),$sql)) && pg_num_rows($res)) {
            while($row = pg_fetch_assoc($res)) {
                $row['ftype'] = CFile::getext($row['name']);
                $ret[$row['id']] = $row;
            }
        }
        return $ret;
    }
    
    function getAllFiles($sort = true) {
        $this->sbr->getDocs(null, ( $this->sbr->isAdmin() ? null : false ), false, $this->id, ( $this->sbr->isAdmin() ? true : false) );
        $attach = $this->_new_getAttach();
        if(is_array($this->sbr->docs) && is_array($attach)) {
            $result = array_merge($attach, $this->sbr->docs);
            foreach($result as $k=>$val) {
                $files[$val['id']] = $val;
            }
        } elseif(is_array($this->sbr->docs)) {
            $files = $this->sbr->docs;
        } else {
            $files = $attach;
        }
        
        $this->sbr->all_docs = $files;
        if($sort) {
            $this->sbr->sortFiles();
        }
    }

    /**
     * ������� �������� �������� ����� �� ���������� ���������������.
     * ���� ���� ������ ������� ��������� (��� � ��������), �� ������ ������������� ���� ��������.
     *
     * @param integer|string $attach_id   ���� ��� ��������� ��., ����������� ��������. ���� NULL, �� ������� ���.
     * @param boolean $force   ������� ��� �������� ��� ������ ��������?
     * @return boolean   �������?
     */
    function delAttach($attach_id, $force = false) {
        if(!($aa = $this->getAttach($attach_id, NULL)))
            return false;

        $server_del_arr = $aa; // �����, ������� ������������� ����� ������� � �������.
        if(!$force) {
            $server_del_arr = NULL;
            foreach($aa as $a) {
                $sql = "SELECT sbr_trigger_fvrs_gt_vrs('sbr_stages', {$a['stage_id']}); DELETE FROM sbr_stages_attach WHERE id = {$a['id']}"; // ������ ���, ������� �������������� �������.
                if($res = pg_query(self::connect(false), $sql)) {
                    if(pg_affected_rows($res))  // ���� �����, �� ������� ��������� is_deleted, �.�. ������� ��������� ������.
                        $server_del_arr[] = $a;
                }
                else {
                    return false;
                }
            }
        }

        if($server_del_arr) {
            $cfile = new CFile();
            foreach($server_del_arr as $d) {
                $cfile->Delete(0, $d['path'], $d['name']);
            }
        }

        return true;
    }
    
    /**
     * ������� �������� �������� ����� �� ���������� ���������������.
     * ���� ���� ������ ������� ��������� (��� � ��������), �� ������ ������������� ���� ��������.
     *
     * @param array $attached ������ � ������� � ������, ���������� �� attachedfiles::getAttach()
     * @param boolean $force   ������� ��� �������� ��� ������ ��������?
     * @return boolean   �������?
     */
    function _new_delAttach($attached, $force = false) {
        global $DB;
        
        if(!$force) {
            // ����� �� ����� � �������� �������� ����, ������ �������� ����� ���������� ��� 1 �����.
            $sql = "SELECT stage_id FROM sbr_stages_attach WHERE file_id = ?";
            $stage_id = (int) $DB->val($sql, $attached[0]['id']);
            foreach($attached as $file) {
                $sql = "SELECT sbr_trigger_fvrs_gt_vrs('sbr_stages', {$stage_id}); DELETE FROM sbr_stages_attach WHERE file_id = {$file['id']} AND stage_id = {$stage_id}";
                if($res = pg_query(self::connect(false), $sql)) {
                    if(pg_affected_rows($res))  // ���� �����, �� ������� ��������� is_deleted, �.�. ������� ��������� ������.
                        $server_del_arr[] = $file;
                }
            }
        }

        if($server_del_arr) {
            $cfile = new CFile();
            foreach($server_del_arr as $d) {
                $cfile->Delete(0, $d['path'], $d['name']);
            }
        }

        return true;
    }


    /**
     * ��������� ������ $this->post_msg ������� �� ����������������� �������.
     * @see sbr_stages::addMsg()
     * @see sbr_stages::editMsg()
     * 
     * @param array $request   $_GET | $_POST
     * @param array $files   $_FILES
     */
    private function _msgInitFromRequest($request, $files) {
        $files = $files['attach'];
        foreach($request as $field=>$value) {
            if($field=='id') continue;
            if(is_scalar($value))
                $value = stripslashes($value);
            switch($field) {
                case 'msgtext' :
                    if(!trim($value)) {
                        $this->error['msgs'][$field] = '��������� �� ������ ���� ������';
                    }
                    break;
                case 'msg_id' :
                    $value = intvalPgSql($value);
                    $this->post_msg['id'] = $value;
                    if($value && !$this->sbr->isAdmin()) {
                        $msg = $this->getMsgs($this->post_msg['id'], false);
                        if(!$msg) {
                            $this->error['msgs']['msgtext'] = '������������ ��������.';
                        } else if(!$this->checkMsgEditTime($msg['post_date'])) {
                            $this->error['msgs']['msgtext'] = '�������������� ����������� ����������: ����� ���������� ���� � ������� ���������� &mdash; '
                                                            . (int)(self::MAX_MSG_EDIT_TIME / 60) . ' ���.';
                        }
                    }
                    break;
                case 'parent_id' :
                    $value = intvalPgSql($value);
                    break;
                case 'delattach' :
                    $value = intarrPgSql($value);
                    break;
                case 'yt_link' :
                    if(trim($value)) {
                        if(!($val = video_validate($value)))
                            $this->error['msgs'][$field] = '�������� ������';
                        else
                            $value = $val;
                    }
                    break;
                case 'date_to_answer_eng_format' :
                    $value = __paramValue('string', $value);
                    break;
                default :
                    break;

            }
            $this->post_msg[$field] = $value;
        }

        $this->post_msg['stage_id'] = $this->data['id'];

        $fcnt = sbr::MAX_FILES;
        if(!$this->error && $files) {
            $this->sbr->getUploadDir(); // !!! ���� ����� �����������, �� ����� � ����� ������ �������� ���������.
            foreach($files['name'] as $idx=>$aname) {
                foreach($files as $prop=>$a)
                    $att[$idx][$prop] = $a[$idx];
                if(--$fcnt < 0) break;
                $file = new CFile($att[$idx]);
                if($err = $this->sbr->uploadFile($file, sbr::MAX_FILE_SIZE)) {
                    if($err == -1) continue;
                    else {
                        $this->error['msgs']['attach'] = $err;
                        break;
                    }
                }
                $this->uploaded_files[] = $file;
            }
        }

        if($this->uploaded_files)
            unset($this->error['msgs']['msgtext']);
    }
    
    /**
     * ��������� ������ $this->post_msg ������� �� ����������������� �������.
     * @see sbr_stages::addMsg()
     * @see sbr_stages::editMsg()
     * 
     * @param array $request   $_GET | $_POST
     * @param array $files   $_FILES
     */
    private function _new_msgInitFromRequest($request, $files) {
        foreach($request as $field=>$value) {
            if($field=='id') continue;
            if(is_scalar($value))
                $value = stripslashes($value);
            switch($field) {
                case 'msgtext' :
                    if(!trim($value)) {
                        $this->error['msgs'][$field] = '��������� �� ������ ���� ������';
                    }
                    break;
                case 'msg_id' :
                    $this->post_msg['id'] = $value;
                    break;
                case 'parent_id' :
                    $value = intvalPgSql($value);
                    break;
                case 'delattach' :
                    $value = intarrPgSql($value);
                    break;
                case 'yt_link' :
                    if(trim($value)) {
                        if(!($val = video_validate($value)))
                            $this->error['msgs'][$field] = '�������� ������';
                        else
                            $value = $val;
                    }
                    break;
                default :
                    break;

            }
            $this->post_msg[$field] = $value;
        }

        $this->post_msg['stage_id'] = $this->data['id'];
        if($files) {
            foreach($files as $i => $attach) {
                $file = new CFile($attach['id']);
                $file->table = 'file_sbr';
                if($file->_remoteCopy($this->sbr->getUploadDir().$file->name)) {
                    $this->uploaded_files[] = $file;
                }
            }
        }

        if($this->uploaded_files) {
            unset($this->error['msgs']['msgtext']);
            if(count($this->error['msgs']) == 0) {
                unset($this->error['msgs']);
            }
        }
    }

    /**
     * ������������ ������, ������� ����� �������������� � ������� ����������/�������������� �����������.
     *
     * @return array   ������������ ����� $this->post_msg.
     */
    function _preMsgSql() {
        $msg = $this->post_msg;
        $msg['id'] = intvalPgSql($msg['id']);
        $msg['msgtext'] = pg_escape_string(change_q_x($msg['msgtext'], false, false, 'b|br|i|p|s|ul|li|h[1-6]')); // !!! ������� ��� ���� �����.
        $msg['yt_link'] = pg_escape_string(change_q_x($msg['yt_link'], true, false));
        $msg['parent_id'] = (int)$msg['parent_id'] ? (int)$msg['parent_id'] : 'NULL';
        return $msg;
    }
    
    /**
     * ������������ ������, ������� ����� �������������� � ������� ����������/�������������� �����������.
     * 
     * @return array 
     */
    function _new_preMsgSql() {
        $msg = $this->post_msg;
        $msg['id'] = intvalPgSql($msg['id']);
        $msg['msgtext'] = pg_escape_string(__paramValue('ckeditor', $msg['msgtext']));//pg_escape_string(change_q_x($msg['msgtext'], false, false, 'b|br|i|p|s|ul|li|h[1-6]')); // !!! ������� ��� ���� �����.
        $msg['yt_link'] = pg_escape_string(change_q_x($msg['yt_link'], true, false));
        $msg['parent_id'] = (int)$msg['parent_id'] ? (int)$msg['parent_id'] : 'NULL';
        return $msg;
    }

    /**
     * ��������� ����� ��������� (�����������) � ����� ������ �� ������ ����������������� �������.
     *
     * @param array $request   $_GET | $_POST
     * @param array $files   $_FILES
     * @return boolean   �������?
     */
    function addMsg($request, $files) {
        $this->_msgInitFromRequest($request, $files); // !!! ������� ����� ������� � ����������� �������� ��� ������������ ������
        if($this->error)
            return false;

        $sql_data = $this->_preMsgSql();
        $is_admin = $this->sbr->isAdmin() ? 't' : 'f';

        if(!$this->_openXact(true))
            return false;

        $sql = "
            INSERT INTO sbr_stages_msgs (stage_id, user_id, parent_id, msgtext, yt_link, is_admin)
            VALUES ({$this->data['id']}, {$this->sbr->uid}, {$sql_data['parent_id']}, '{$sql_data['msgtext']}', '{$sql_data['yt_link']}', '{$is_admin}')
            RETURNING id;
        ";
        if(!($res = pg_query(self::connect(false), $sql))) {
            $this->_abortXact();
            return false;
        }

        $this->post_msg['id'] = pg_fetch_result($res,0,0);
        $sql_attach = '';
        if($this->uploaded_files) {
            foreach($this->uploaded_files as $file) {
                if(!$file->id) continue;
                $sql_attach .= "INSERT INTO sbr_stages_msgs_attach(msg_id, file_id, orig_name) VALUES({$this->post_msg['id']}, {$file->id}, '{$file->orig_name}');";
            }
        }

        if(!$this->setMsgsRead($this->data['read_msgs_count'] + 1) || $sql_attach && !pg_query(self::connect(false), $sql_attach)) {
            $this->_abortXact();
            unset($this->post_msg['id']);
            return false;
        }
        
        if ($is_admin === 't') {
            $dateToAnswer = $this->post_msg['date_to_answer_eng_format'];
            if ($dateToAnswer) {
                $this->setArbitrageDateToAnswer($dateToAnswer);
            }
        }
        
        $oMemBuff = new memBuff();
        if($this->sbr->uid != $this->sbr->frl_id)
            $oMemBuff->delete( 'sbrMsgsCnt'.$sbr->frl_id );
        if($this->sbr->uid != $this->sbr->emp_id)
            $oMemBuff->delete( 'sbrMsgsCnt'.$sbr->emp_id );
        
        $this->_commitXact();

        $msg_id = $this->post_msg['id'];
        unset($this->post_msg);
        return $msg_id;
    }
    
    /**
     * ��������� ����� ��������� (�����������) � ����� ������ �� ������ ����������������� �������.
     * @todo ������� ��� ������ ���. ���������� �� �� ��������� � xact_id ��� ����������� ����� �� ����� �������
     * 
     * @param array $request   $_GET | $_POST
     * @param array $files   $_FILES
     * @return boolean   �������?
     */
    function _new_addMsg($request, $files) {
        $this->_new_msgInitFromRequest($request, $files); // !!! ������� ����� ������� � ����������� �������� ��� ������������ ������
        if($this->error)
            return false;

        $sql_data = $this->_new_preMsgSql();
        $is_admin = $this->sbr->isAdmin() ? 't' : 'f';
        $xact_id  = $this->_openXact(true);
        if(!$xact_id)
            return false;

        $sql = "
            INSERT INTO sbr_stages_msgs (stage_id, user_id, parent_id, msgtext, yt_link, is_admin)
            VALUES ({$this->data['id']}, {$this->sbr->uid}, {$sql_data['parent_id']}, '{$sql_data['msgtext']}', '{$sql_data['yt_link']}', '{$is_admin}')
            RETURNING id;
        ";
        if(!($res = pg_query(self::connect(false), $sql))) {
            $this->_abortXact();
            return false;
        }

        $this->post_msg['id'] = pg_fetch_result($res,0,0);
        $sql_attach = '';
        if($this->uploaded_files) {
            foreach($this->uploaded_files as $file) {
                if(!$file->id) continue;
                $originalName = $file->shortenName($file->original_name, 128);
                $sql_attach .= "INSERT INTO sbr_stages_msgs_attach(msg_id, file_id, orig_name) VALUES({$this->post_msg['id']}, {$file->id}, '{$originalName}');";
            }
        }

        if(!$this->setMsgsRead($this->data['read_msgs_count'] + 1) || $sql_attach && !pg_query(self::connect(false), $sql_attach)) {
            $this->_abortXact();
            unset($this->post_msg['id']);
            return false;
        }
        
        $oMemBuff = new memBuff();
        if($this->sbr->uid != $this->sbr->frl_id)
            $oMemBuff->delete( 'sbrMsgsCnt'.$sbr->frl_id );
        if($this->sbr->uid != $this->sbr->emp_id)
            $oMemBuff->delete( 'sbrMsgsCnt'.$sbr->emp_id );
        
        $this->_commitXact();

        $msg_id = $this->post_msg['id'];
        unset($this->post_msg);
        return $xact_id;
    }


    /**
     * ����������� ��������� (�����������) � ����� ������ �� ������ ����������������� �������.
     *
     * @param array $request   $_GET | $_POST
     * @param array $files   $_FILES
     * @return boolean   �������?
     */
    function editMsg($request, $files) {
        $this->_msgInitFromRequest($request, $files);
        if($this->error)
            return false;

        if(!$this->_openXact())
            return false;

        if(!$this->sbr->isAdmin())
            $where_user = " AND user_id = {$this->sbr->uid}";

        $this->post_msg['id'] = $this->post_msg['msg_id'];
        $sql_data = $this->_preMsgSql();
        $sql = "
            UPDATE sbr_stages_msgs 
               SET msgtext = '{$sql_data['msgtext']}',
                   yt_link = '{$sql_data['yt_link']}',
                   modified = now(),
                   moduser_id = {$this->sbr->uid}
             WHERE id = {$sql_data['id']}
                   {$where_user}
        ";

        if(!($res = pg_query(self::connect(false), $sql)) || !pg_affected_rows($res)) {
            $this->_abortXact();
            return false;
        }

        $sql_attach = '';
        if($this->uploaded_files) {
            foreach($this->uploaded_files as $file) {
                if(!$file->id) continue;
                $sql_attach .= "INSERT INTO sbr_stages_msgs_attach(msg_id, file_id, orig_name) VALUES({$this->post_msg['id']}, {$file->id}, '{$file->orig_name}');";
            }
        }

        if($sql_attach && !pg_query(self::connect(false), $sql_attach)) {
            $this->_abortXact();
            return false;
        }

        if($sql_data['delattach']) {
            $this->delMsgAttach($sql_data['id'], implode(',', $sql_data['delattach']));
        }
        
        if ($this->sbr->isAdmin()) {
            $dateToAnswer = $this->post_msg['date_to_answer_eng_format'];
            if ($dateToAnswer) {
                $this->setArbitrageDateToAnswer($dateToAnswer);
            }
        }

        $this->_commitXact();

        $msg_id = $this->post_msg['id'];
        unset($this->post_msg);
        return $msg_id;
    }

    /**
     * ���������� ���� ��� ��������� (���) ������������ � ������� �����.
     *
     * @param integer|string $msg_id   ���� ��� ��������� ��. ���������, ����������� ��������. ���� NULL, �� ����� ���������� ��� ��������� � ���� ������.
     * @param boolean $get_attach   �������� �������� � ������?
     * @return array   ������ ���������. NULL -- ������.
     */
    function getMsgs($msg_id = NULL, $get_attach = true) {
        $sended = $this->sbr->sended ? $this->sbr->sended : $this->sbr->posted; // ���� ��� ���� �� ���� ����� �� ����
        // ����� ����� ���������, ������� � ���� �������� �� �������� �����������, ����� �� ������ ��������� �� ������ ������������.
        $where = "sm.post_date > '{$sended}' AND sm.stage_id = {$this->id}" . ($msg_id == NULL ? '' : " AND sm.id IN ({$msg_id})");
        $sql = "
          SELECT sm.*, u.login, u.uname, u.usurname, u.photo, u.role, u.is_pro, u.is_pro_test, u.is_team, ssa.date_to_answer
            FROM sbr_stages_msgs sm
          INNER JOIN
            users u
              ON u.uid = sm.user_id
          LEFT JOIN
            sbr_stages_arbitrage ssa
              ON ssa.stage_id = sm.stage_id
           WHERE {$where}
           ORDER BY sm.parent_id, sm.post_date
        ";
        if(!($res = pg_query(self::connect(false), $sql)) || !pg_num_rows($res))
            return NULL;

        $msgs_id = array();
        while($row = pg_fetch_assoc($res)) {
            $msgs_id[] = $row['id'];
            $msgs[$row['id']] = $row;
        }

        if($get_attach) {
            $msgs_id = implode(',', $msgs_id);
            if($atts = $this->getMsgAttach($msgs_id)) {
                foreach($atts as $id=>$att)
                    $msgs[$att['msg_id']]['attach'][$id] = $att;
            }
        }
        if(!$msg_id)
            return array2tree($msgs, 'id', 'parent_id', true);
        return current($msgs);
    }
    
    /**
     * ���������� ID ������� �������������� ���������.
     */
    function getFirstUnreadMsgId() {
        global $DB;
        $sQuery = "SELECT id FROM sbr_stages_msgs WHERE stage_id = {$this->id} 
                AND user_id <> {$this->sbr->session_uid} 
                AND post_date > (CASE WHEN '{$this->last_msgs_view}' = '' THEN 'epoch' ELSE '{$this->last_msgs_view}' END)::timestamp without time zone 
            ORDER BY parent_id, post_date LIMIT 1";
        
        return $DB->val( $sQuery );
    }

    /**
     * �������� �������� ��������� ���������.
     *
     * @param integer|string $msg_id   ���� ��� ��������� ��. ���������, ����������� ��������.
     * @param integer|string $attach_id   ���� ��� ��������� ��. �������, ����������� ��������. NULL -- ����� ��� �������� �� ������ ���������.
     * @return array   ������ ��������, ��������������� ��. NULL -- ������.
     */
    function getMsgAttach($msg_id, $attach_id = NULL) {
        $atts = NULL;
        $where = "ma.msg_id IN ({$msg_id})" . ($attach_id == NULL ? '' : " AND ma.id IN ({$attach_id})");
        $sql = "
            SELECT ma.*, f.fname as name, f.path, f.size, f.virus
              FROM sbr_stages_msgs_attach ma
            INNER JOIN
              file_sbr f
                ON f.id = ma.file_id
             WHERE {$where}
             ORDER BY ma.msg_id, ma.id
        ";
        if(!($res = pg_query(self::connect(false), $sql)))
            return NULL;
        while($row = pg_fetch_assoc($res))
            $atts[$row['id']] = $row;
        return $atts;
    }

    /**
     * ������� �������� ��������� ���������.
     *
     * @param integer|string $msg_id   ���� ��� ��������� ��. ���������, ����������� ��������.
     * @param integer|string $attach_id   ���� ��� ��������� ��. �������, ����������� ��������. NULL -- ������� ��� �������� �� ������ ���������.
     * @return boolean   �������?
     */
    function delMsgAttach($msg_id, $attach_id) {
        if($aa = $this->getMsgAttach($msg_id, $attach_id)) {
            $cfile = new CFile();
            foreach($aa as $a)
                $cfile->Delete(0, $a['path'], $a['name']);
            return true;
        }
        return false;
    }

    /**
     * ������� ��������� �������� �����. �������� ��� ���������.
     *
     * @param integer $msg_id   ��. ���������.
     * @return array   ������ �� ���������� ��������� {@link sbr_stages::getMsgs()}.
     */
    function delMsg($msg_id) {
        $msg_id = intvalPgSql($msg_id);
        $sql = "
            UPDATE sbr_stages_msgs 
               SET deleted = now(),
                   deluser_id = {$this->sbr->uid}
             WHERE id = {$msg_id}
               AND stage_id = {$this->data['id']}
        ";
        if(!($res = pg_query(self::connect(false), $sql)))
            return NULL;
        return $this->getMsgs($msg_id);
    }

    /**
     * �������� ��� (�� ���������) ��������� � ������� ����� ��� �����������.
     *
     * @param integer $read_cnt   ���-�� ����������� ���������. �� ��������� -- ���.
     * @return boolean   �������?
     */
    function setMsgsRead($read_cnt = NULL) {
        if($read_cnt === NULL)
            $read_cnt = $this->data['msgs_cnt'];
        if($this->data['read_msgs_count'] == $read_cnt)
            return true;
        $sql = "
           UPDATE sbr_stages_users
              SET last_msgs_view = now(),
                  read_msgs_count = ?i
            WHERE stage_id = ?i
              AND user_id = ?i
        ";
        
        $sql = $this->db()->parse($sql, $read_cnt, $this->id, $this->sbr->session_uid);      
              
        return !!pg_query(self::connect(false), $sql);
    }


    /**
     * �������, ����� �� ������������� ������ ���� �����������.
     * @return bool
     */
    function checkMsgEditTime($msg_post_date) {
        return ( time() - strtotime($msg_post_date) <= self::MAX_MSG_EDIT_TIME );
    }


    /**
     * ���������� ����� �������� ����� "��������� �� ���� ����� �� �������".
     * 
     * @param  bool $val ����� �������� �����
     * @return bool true - �����, false - ������
     */
    function setNotNP($val = true) {
        if($this->sbr->isEmp()) return true;
        $val = $val ? 'true' : 'false';
        $sql = "UPDATE sbr_stages_users SET act_notnp = ?b WHERE stage_id = ?i AND user_id = ?i";
        $sql = $this->db()->parse($sql, $val, $this->id, $this->sbr->uid);
        return !!pg_query(self::connect(false), $sql);
    }


    /**
     * ������������ ������ �� ���������� ����� ����� �� ���������� ������.
     * ���������� ������� �� ����������� ������� (������� � ��. �������) + ������ ����� ������� � ���������� � � ��������� (���� ��������)
     *
     * @param array $request   ������ � ����������� �� ������� � ����� ������ �������.
     * @param boolean $ym_on   ��������� �� � �������� ������ ������� ������.������. ������ ������ ������� ��� ������������, ���� ������ � ���.
     * @return boolean   �������?
     */
    function complete($request) {
        if($this->_openXact(TRUE)) {

            $ok = true;
            
            if(isset($request['notnp'])) {
                $ok = $this->setNotNP(!!$request['notnp']);
            }
            
            if($ok && isset($request['credit_sys'])) {
                $credit_sys = intvalPgSql($request['credit_sys']);
                $this->request['credit_sys'] = $credit_sys;
                $ok = $this->setPayoutSys($credit_sys);
                $this->sysed = $ok;
            }
            
            if($ok) {
                if($request['feedback'] && !($this->sbr->isFrl() && $this->arbitrage['id'] > 0 && $this->arbitrage['frl_percent'] == 0) && !($this->sbr->isEmp() && $this->arbitrage['frl_percent'] == 1)) {
                    $ok = $this->feedback($request['feedback'], $request['sbr_feedback']);
                    $this->fbked = $ok;
                } else if ($request['sbr_feedback'] != '') { // ������ �� ������������ ����
                    $ok = $this->sbr->feedback($request['sbr_feedback']);
                } 
                
                if ( $ok && ( ($this->sbr->isFrl() && $this->arbitrage['id'] > 0 && $this->arbitrage['frl_percent'] == 0) || ($this->sbr->isEmp() && $this->arbitrage['frl_percent'] == 1) ) ) {
                    $this->fbked = $ok;
                    
                    if($this->sbr->isFrl()) {
                        $this->updateCompleteStage(true, 'frl');
                    } elseif($this->sbr->isEmp()) {
                        $this->updateCompleteStage(true, 'emp');
                    }
                }
            }
            
            

            $docs = array();
            if($request['credit_sys'] == exrates::WMR && sbr_meta::checkWMDoc($this->sbr->user_reqvs)) {
                $ok = false; 
                $this->error['credit_sys']['act'] = '���������� ��������� ���� "���������� ������" � ����� "����������� ��������"';
            }
            $doc_err = false;
            
            // �� ������� ���������� ��� �� ������ ������.
            if($this->sbr->scheme_type == sbr::SCHEME_LC) {
                $docs = $this->generateNewPackageDocs($ok, $doc_err);
            } else {
                $docs = $this->generatePackageDocs($ok, $doc_err);
            }

            if ($doc_err) {
                if($this->sysed) {
                    $this->error['credit_sys']['act'] = current($doc_err);
                } else {
                    $this->error['feedback']['descr'] = current($doc_err);
                }
                $ok = false;
            }
            else if (count($docs)) {
                foreach($docs as $doc) {
                    $ok = $this->sbr->addDocR($doc);
                    if(!$ok) break;
                }
            }
            
            if($ok) {
                return $this->_commitXact();
            }
            $this->_abortXact();
            if($this->sysed)
                $this->payouts = NULL;
        }
        return false;
    }


    /**
     * ����������
     * 
     * ������������ ������ �� ���������� ����� ����� �� ���������� ������.
     * ���������� ������� �� ����������� ������� (������� � ��. �������) + ������ ����� ������� � ���������� � � ��������� (���� ��������)
     *
     * @param array $request   ������ � ����������� �� ������� � ����� ������ �������.
     * @param boolean $ym_on   ��������� �� � �������� ������ ������� ������.������. ������ ������ ������� ��� ������������, ���� ������ � ���.
     * @return boolean   �������?
     */
    function completeAgnt($request) {
        if($this->_openXact(TRUE)) {
            $ok = true;
            
            if(isset($request['notnp'])) {
                $ok = $this->setNotNP(!!$request['notnp']);
            }

            if(!$this->sbr->isFrl()) {
                if($request['sbr_sms_code']!=$_SESSION['close_sbr_smscode']) {
                    $this->error['feedback']['sms'] = 1;
                    $ok = false;
                }
            }
            
            if($ok) {
                if($this->isAccessOldFeedback() && $request['feedback'] && !($this->arbitrage['id'] > 0 && ($this->arbitrage['result_id'] == 1) ) && !($this->sbr->isFrl() && $this->arbitrage['id'] > 0 && ($this->arbitrage['result_id'] == 5 || $this->arbitrage['result_id'] == 6)) && !($this->sbr->isEmp() && $this->arbitrage['id'] > 0 && $this->arbitrage['result_id'] == 7) ) {
                    $ok = $this->feedback($request['feedback'], $request['sbr_feedback']);
                    $this->fbked = $ok;
                } else if ($request['sbr_feedback']) { // ������ �� ������������ ����
                    $ok = $this->sbr->feedback($request['sbr_feedback']);
                } 

                if ( $ok && ( ($this->arbitrage['id'] > 0 && ($this->arbitrage['result_id'] == 1) ) || ($this->sbr->isFrl() && $this->arbitrage['id'] > 0 && ($this->arbitrage['result_id'] == 5 || $this->arbitrage['result_id'] == 6)) || ($this->sbr->isEmp() && $this->arbitrage['id'] > 0 && $this->arbitrage['result_id'] == 7) ) ) {
                    $this->fbked = $ok;
                    
                    if($this->sbr->isFrl()) {
                        $this->updateCompleteStage(true, 'frl');
                    } elseif($this->sbr->isEmp()) {
                        $this->updateCompleteStage(true, 'emp');
                    }
                }
            }
            
            if ($this->sbr->isFrl()) {
            
                $pskb = new pskb($this->sbr);
                $lc = $pskb->getLC();
                $request['credit_sys'] = pskb::$exrates_map[$this->sbr->isEmp() ? $lc['ps_emp'] : $lc['ps_frl']];

                $emp_percent = 0;
                $frl_percent = 1;

                if($this->arbitrage === false) {
                    $this->getArbitrage(false, false);
                }
                if($this->arbitrage && $this->arbitrage['resolved']) {
                    $emp_percent = abs(sbr::EMP - $this->arbitrage['frl_percent']);
                    $frl_percent = abs(sbr::FRL - $this->arbitrage['frl_percent']);
                }

                $sumCust = round($this->data['cost'] * $emp_percent, 2);
                $sumPerf = round($this->data['cost'] * $frl_percent, 2);
            
                if($ok && $sumPerf > 0) {
                    $credit_sys = intvalPgSql(pskb::$exrates_map[$lc['ps_frl']]);
                    $this->request['credit_sys'] = $credit_sys;
                    $ok = $this->setPayoutSys($credit_sys, true);
                    $this->sysed = $ok;
                }
                
                
                $docs = array();
                if($ok) {
                    $docs = $this->generateNewPackageDocs($ok, $doc_err);
                    $ok   = !empty($docs); // �� �� ���� ���������� �� ���������������
                }

                if ($doc_err) {
                    if($this->sysed) {
                        $this->error['credit_sys']['act'] = current($doc_err);
                    } else {
                        $this->error['feedback']['descr'] = current($doc_err);
                    }
                    $ok = false;
                } else if ( !empty($docs) ) {
                    foreach($docs as $doc) {
                        $ok = $this->sbr->addDocR($doc);
                        if(!$ok) break;
                    }
                }
                
                if ($ok) {
                    if( ($sumCust + $sumPerf)  != $this->data['cost'] && $sumCust > 0) { // �� �������� ���� ����������, ������ ���� ������� �� ��������
                        $sumCust -= 0.01; // ������������ ������� ������. 
                    }
                    $resp = $pskb->payoutOpen($this, $sumCust, $sumPerf);
                    $ok = $resp && $resp->state != pskb::STATE_ERR;
                }
            }
            
            if($ok)
                return $this->_commitXact();
            $this->_abortXact();
            if($this->sysed)
                $this->payouts = NULL;
        }
        return false;
    }
    
    /**
     * ���������� ������ ����� ���������� ��� ����� ������ (���������� ������, �� ������������ �� ������ ���������� @see self::generatePackageDocs())
     * 
     * @param boolean $ok          �������� ������ �� ��������� ���������� ���� true - �� � ������� ��� ��
     * @param array   $doc_err     ���� ������������ ������ ��� ��������� ����������
     */
    public function generateNewPackageDocs($ok, &$doc_err) {
        $is_emp_arb = $this->status == sbr_stages::STATUS_ARBITRAGED && $this->arbitrage['resolved'] && floatval($this->arbitrage['frl_percent']) == 0;
        
        $reason     = 1;
        
        // ����� ����� ���������� ������ �� ��������� �����
        if ($ok && !$is_emp_arb && ($this->sysed || $this->fbked) && $this->sbr->scheme_type == sbr::SCHEME_LC) {
            if($this->status != sbr_stages::STATUS_ARBITRAGED) {
                if ($doc_file = $this->generateCompletedAct($doc_err, $doc_num)) { // ��������� ��� ����������� �� ��������� �����.
                    $docs[] = array(
                        'stage_id'      => $this->id, 
                        'file_id'       => $doc_file->id, 
                        'status'        => sbr::DOCS_STATUS_PUBL, 
                        'access_role'   => sbr::DOCS_ACCESS_ALL,
                        'owner_role'    => 0, 
                        'type'          => sbr::DOCS_TYPE_ACT
                    );
                }
            } elseif($this->status == sbr_stages::STATUS_ARBITRAGED) {
                $reason = html_entity_decode($this->arbitrage['reason'], ENT_QUOTES, 'cp1251');
                $result = html_entity_decode($this->arbitrage['result'], ENT_QUOTES, 'cp1251');
                if(strpos($reason, 'C������� �. 9.1.2. ����������� ����������') ===  false && strpos($result, '���������� ������ ����������� ����������') ===  false) {
                    
                    if ($doc_file = $this->generateArbReportFrl($doc_err, $doc_num)) { // ����� ��������� �� ��������� ����� ��� �����������.
                        $docs[] = array(
                            'stage_id'      => $this->id, 
                            'file_id'       => $doc_file->id,
                            'num'           => $doc_num, 
                            'status'        => sbr::DOCS_STATUS_PUBL,
                            'access_role'   => sbr::DOCS_ACCESS_ALL, 
                            'owner_role'    => 0, 
                            'type'          => sbr::DOCS_TYPE_ARB_REP
                        );
                    }
                }
            }
            
        } elseif ($ok && ($this->sysed || $this->fbked) && $is_emp_arb && $this->sbr->scheme_type == sbr::SCHEME_LC) {
            if ($doc_file = $this->generateArbReportEmp($doc_err, $arb_num)) { // ����� ��������� �� ��������� ����� ��� ������������.
                $eper = (1-$this->arbitrage['frl_percent'])*100;


                $docs[] = array(
                    'stage_id'      => $this->id, 
                    'file_id'       => $doc_file->id, 
                    'num'           => $arb_num, 
                    'status'        => sbr::DOCS_STATUS_PUBL,
                    'access_role'   => sbr::DOCS_ACCESS_ALL, 
                    'owner_role'    => 0, 
                    'type'          => sbr::DOCS_TYPE_ARB_REP);
            }
        }
        
        if ($doc_err) return false;
        
        return $docs;
    }
    
    /**
     * ���������� ����� ���������� ��� ������ ������ + ������ �� �������
     * 
     * @param boolean $ok          �������� ������ �� ��������� ���������� ���� true - �� � ������� ��� ��
     * @param array   $doc_err     ���� ������������ ������ ��� ��������� ����������
     * 
     * @return boolean|array ���������� ������ ���������� ��� false - ���� ��������� ������
     */
    public function generatePackageDocs($ok, &$doc_err) {
        $is_emp_arb = $this->status == sbr_stages::STATUS_ARBITRAGED && $this->arbitrage['resolved'] && floatval($this->arbitrage['frl_percent']) == 0;
        $this->sbr->getUserReqvHistoryData($this->id, 'frl');
        if ($ok && $this->sbr->isFrl() && ($this->sysed || $this->fbked)) { // ������ ����� ��������� ���������� _���������_ �����, ���� �������� ������ �������.
            if ($this->request['credit_sys'] == exrates::FM && $this->sysed) { // ��������� ������� � ������� � FM
                if ($doc_file = $this->generateFrlAppl($doc_err)) {
                    $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_PUBL, 'access_role' => sbr::DOCS_ACCESS_FRL,
                        'owner_role' => 0, 'type' => sbr::DOCS_TYPE_FM_APPL);
                }
            }
            
            if (!$doc_err) {
                if ($this->sbr->scheme_type == sbr::SCHEME_AGNT) {
                    if (!$doc_err) {
                        if ($doc_file = $this->generateFrlAct($doc_err, $doc_num)) { // ��������� ��� ����������� �� ��������� �����.
                            $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_PUBL, 'access_role' => sbr::DOCS_ACCESS_FRL,
                                'owner_role' => 0, 'type' => sbr::DOCS_TYPE_ACT);
                        }
                    }
                    if ($this->request['credit_sys'] == exrates::WMR && $this->sysed && $this->sbr->frl_reqvs['rez_type'] == sbr::RT_UABYKZ && $this->sbr->frl_reqvs['form_type'] == sbr::FT_PHYS) {
                        if ($doc_file = $this->generateFrlWMAppl($doc_err, $doc_num)) {
                            $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_PUBL, 'access_role' => sbr::DOCS_ACCESS_FRL,
                                'owner_role' => 0, 'type' => sbr::DOCS_TYPE_WM_APPL);
                        }
                    }
                    if ($this->request['credit_sys'] == exrates::YM && $this->sysed && $this->sbr->frl_reqvs['rez_type'] == sbr::RT_UABYKZ && $this->sbr->frl_reqvs['form_type'] == sbr::FT_PHYS) {
                        if ($doc_file = $this->generateFrlYMAppl($doc_err, $doc_num)) {
                            $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_PUBL, 'access_role' => sbr::DOCS_ACCESS_FRL,
                                'owner_role' => 0, 'type' => sbr::DOCS_TYPE_YM_APPL);
                        }
                    }
                    if ($this->status == sbr_stages::STATUS_ARBITRAGED) {
                        if (!$doc_err) {
                            $a_role = sbr::DOCS_ACCESS_FRL;
                            if ($this->arbitrage['resolved'] && floatval($this->arbitrage['frl_percent']) > 0) {
                                $a_role = sbr::DOCS_ACCESS_ALL;
                            }
                            if ($doc_file = $this->generateArbReport($doc_err, $doc_num)) { // ����� ��������� �� ��������� ����� ��� �����������.
                                $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'num' => $doc_num, 'status' => sbr::DOCS_STATUS_PUBL,
                                    'access_role' => $a_role, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_ARB_REP);
                            }
                        }
                    }
                } else if ($this->sbr->scheme_type == sbr::SCHEME_PDRD || $this->sbr->scheme_type == sbr::SCHEME_PDRD2) { // ��������� ��� ����������� �� �������� �������.
                    if ($doc_file = $this->generateFrlActPdrd($doc_err)) {
                        $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_PUBL, 'access_role' => sbr::DOCS_ACCESS_FRL,
                            'owner_role' => 0, 'type' => sbr::DOCS_TYPE_ACT);
                    }
                    // ���� ��� ������������� ��� ������
                    if ($doc_file) {
                        if ($doc_file = $this->generateTzPdrd($doc_err)) {
                            $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_PUBL, 'access_role' => sbr::DOCS_ACCESS_FRL,
                                'owner_role' => 0, 'type' => sbr::DOCS_TYPE_TZ_PDRD);
                        }
                    }

                    if ($this->request['credit_sys'] == exrates::WMR && $this->sysed && $this->sbr->frl_reqvs['rez_type'] == sbr::RT_UABYKZ && $this->sbr->frl_reqvs['form_type'] == sbr::FT_PHYS) {
                        if ($doc_file = $this->generateFrlWMAppl($doc_err)) {
                            $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_PUBL, 'access_role' => sbr::DOCS_ACCESS_FRL,
                                'owner_role' => 0, 'type' => sbr::DOCS_TYPE_WM_APPL);
                        }
                    }
                    if ($this->request['credit_sys'] == exrates::YM && $this->sysed && $this->sbr->frl_reqvs['rez_type'] == sbr::RT_UABYKZ && $this->sbr->frl_reqvs['form_type'] == sbr::FT_PHYS) {
                        if ($doc_file = $this->generateFrlYMAppl($doc_err)) {
                            $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_PUBL, 'access_role' => sbr::DOCS_ACCESS_FRL,
                                'owner_role' => 0, 'type' => sbr::DOCS_TYPE_YM_APPL);
                        }
                    }
                    if ($this->status == sbr_stages::STATUS_ARBITRAGED) {
                        if (!$doc_err) {
                            if ($doc_file = $this->generateArbReportPdrdFrl($doc_err)) { // ����� ��������� �� �������� ������� ��� ����������.
                                $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_PUBL,
                                    'access_role' => sbr::DOCS_ACCESS_FRL, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_ARB_REP);
                            }
                        }
                    }
                }
            }
        } elseif ($ok && $this->sbr->isEmp() && ($this->sysed || $this->fbked) && $is_emp_arb) {
            if ($this->sbr->scheme_type == sbr::SCHEME_AGNT) {
                if ($doc_file = $this->generateArbReport($doc_err, $arb_num)) { // ����� ��������� �� ��������� ����� ��� ������������.
                    $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'num' => $arb_num, 'status' => sbr::DOCS_STATUS_PUBL,
                        'access_role' => sbr::DOCS_ACCESS_EMP, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_ARB_REP);
                    $this->tmp_doc_arb = array('num' => $arb_num, 'publ_time' => date('c'));

                    if ($doc_file = $this->generateEmpAgentReport($doc_err, $rep_num)) { // ����� ������.
                        $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'num' => $rep_num, 'status' => sbr::DOCS_STATUS_PUBL,
                            'access_role' => sbr::DOCS_ACCESS_EMP, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_AGENT_REP);
                        if ($doc_file = $this->generateEmpAct($doc_err, $act_num, $rep_num)) { // ��� ������������ �� ��������� ����� (�� �� ����� �� ���������, ���� ��� ��������).
                            $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'num' => $act_num, 'status' => sbr::DOCS_STATUS_PUBL,
                                'access_role' => sbr::DOCS_ACCESS_EMP, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_ACT);
                        }
                    }
                }
            } else if ($this->sbr->scheme_type == sbr::SCHEME_PDRD || $this->sbr->scheme_type == sbr::SCHEME_PDRD2) {
                if ($doc_file = $this->generateArbReportPdrdEmp($doc_err)) { // ����� ��������� �� �������� ������� ��� ������������.
                    $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_PUBL,
                        'access_role' => sbr::DOCS_ACCESS_EMP, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_ARB_REP);
                }
            }
        }
        
        if ($doc_err) return false;
        
        return $docs;
    }
    
    /**
     * ����� ���������� �� ������
     *
     * @param integer $feedback_id   ��. ������
     * @return array
     */
    function getFeedback($feedback_id) {
        return parent::getFeedback($feedback_id);
    }

    /**
     * ����� ����� �� ������ ��������� � �������. ��� ������������� � ���� �� ���������� ����� ����� �������.
     *
     * @param array $request   ������ �� ������ ���������.
     * @param array $sbr_request   ������ �� ������ ������� ���.
     * @return array   ����� (����������� ����� ����) ��� false, ���� ������.
     */
    function feedback($request, $sbr_request = NULL) {
        if($request['id'] && $request['id'] != $this->data[$this->sbr->upfx.'feedback_id']) return false;
        $inxact = !!sbr_meta::$XACT_ID;
        if($inxact || $this->_openXact(TRUE)) {
            if($_POST['sbr_sms_code']!=$_SESSION['close_sbr_smscode']) {
                $this->error['feedback']['sms'] = 1;
            } else {
                $ok = true;
                if($sbr_request)
                    $ok = $this->sbr->feedback($sbr_request);
                if(!$ok) {
                    $this->error['sbr_feedback'] = true;
                }
                if(($feedback = parent::addFeedback($request, $this->feedback, $err)) && $ok) {
                    
                   $sql = "UPDATE sbr_stages SET {$this->sbr->upfx}feedback_id = ?i WHERE id = ?i";
                   $sql = $this->db()->parse($sql, $feedback['id'], $this->id);
                   
                   if(pg_query(self::connect(false), $sql)) {
                       if(!$inxact) $this->_commitXact();
                       return $feedback;
                   }
                }
                $this->error['feedback'] = $err;
            }
            $this->_abortXact();
        }
        return false;
    }

    /**
     * ������������ ����� �������.
     *
     * @param boolean $role   ������� ��� ������������? NULL -- ���������� ������������� (������, ���� ����� �� ����������� ����� ��� ���� ������ �� �������� ��������� �����).
     * @param integer $outsys   � ����� ������ ���������� �����. NULL -- � ������ �������.
     * @param float $arb_percent   ������� �� ��������� ��� ������� �����. ���� NULL, �� ������� ���������� ���������.  
     * @param integer $payout_sys   � ����� ������ ���������� ������� (�� ������ � $outsys). �� ����� ������� ��������� ��������. ���� NULL, �� ������� ���������� ���������.  
     * @param boolean $notnp   true, ���� �������, ��� ���� ������ ������� � ����������� (����� ����� �� ������� ���������). ���� NULL, �� ������� ���������� ���������.  
     * @return float   �����.
     */
    function getPayoutSum($role = NULL, $outsys = NULL, $arb_percent = NULL, $payout_sys = NULL, $notnp = NULL) {
        $role = (int)($role === NULL ? ($this->sbr->isEmp() ? sbr::EMP : sbr::FRL) : $role);
        $cost_coeff = $this->sbr->getCostSysCoeff($outsys);
        
        if($arb_percent === NULL) {
            if($this->arbitrage === false)
                $this->getArbitrage(false, false);
            if($this->arbitrage && $this->arbitrage['resolved'])
                $arb_percent = abs($role - $this->arbitrage['frl_percent']);
        }
        if($arb_percent !== NULL) {
            $fSum = $arb_percent * $this->cost;
            $eSum = (1-$arb_percent) * $this->cost;
            $sumPayout = $fSum; 
            
            if( ($fSum + $eSum) != $this->cost && $role == sbr::EMP && $sumPayout > 0) {
                $sumPayout -= 0.01; // �������� � ������������ ����� �� �������� ����� ��-�� �������
            }
        } else {
            $sumPayout = $this->cost;
        }
        $sum = $cost_coeff * (
                 $sumPayout + 
                 (2*$role-1) * $this->calcAllTax($role, array('P'=>$payout_sys, 'A'=>$arb_percent, 'nNP'=>$notnp), exrates::BANK) // ��� ������ ��������� � ������, ������������ � $outsys ��� �������� �����.
               );
        return $sum;
    }

    /**
     * ���������� ����� ����� �������
     * 
     * @param  int $role 0 - ���������, 1 - ������������
     * @param  array $dvals ���������
     * @param  integer $outsys � ����� ������ ���������� �����. NULL -- � ������ �������.
     * @return float
     */
    function calcAllTax($role = NULL, $dvals = array(), $outsys = NULL) {
        $total = 0;
        $role = (int)($role === NULL ? ($this->sbr->isEmp() ? sbr::EMP : sbr::FRL) : $role);
        if(!$this->sbr->scheme)
            $this->sbr->getScheme();
        if($this->sbr->scheme['taxes'][$role]) { // � ������ ������ � ���������� ����� �� ���� ������� �������� � �������.
            foreach($this->sbr->scheme['taxes'][$role] as $tax) {
                $total += ($x=$this->calcTax($tax, $dvals, $outsys));
                //echo $x.'===';
            }
        }
        return $total;
    }
    
    /**

     * ��������� �����
     * 
     * @param  array $tax ������ �� ������
     * @param  array $dvals ���������
     * @param  integer $outsys � ����� ������ ���������� �����. NULL -- � ������ �������.
     * @return float
     */
    function calcTax($tax, $dvals = array(), $outsys = NULL) {
        if(!is_numeric($tax['tax_id'])) return 0;
        
        $role = $tax['role'];
        $depends = $tax['depends'];
        $cost_coeff = $this->sbr->getCostSysCoeff($outsys);

        foreach(sbr_meta::$_taxDepends as $chr=>$dt) {
            $dv = NULL;
            if(isset($dvals[$chr]) || stripos($depends, "#{$chr}")===false) {
                if($dvals[$chr]==='NULL')
                    $dvals[$chr] = NULL;
                continue;
            }
            
            switch($chr) {
            
                case 'Ff' : 
                    if($this->sbr->scheme_type != sbr::SCHEME_LC) {
                        $r = $this->sbr->getUserReqvHistory($this->id, $this->sbr->frl_id);
                        $reqv = $r['b'];
                    } else {
                        $reqv = $this->sbr->getFrlReqvs();
                    }
                    $dv = $reqv['form_type'];
                    break;
                    
                case 'Re' :
                    if($this->sbr->scheme_type != sbr::SCHEME_LC) {
                        $r = $this->sbr->getUserReqvHistory($this->id, $this->sbr->emp_id);
                        $reqv = $r['b'];
                    } else {
                        $reqv = $this->sbr->getEmpReqvs();
                    }
                    $dv = $reqv['rez_type'];
                    break;
                    
                case 'Rf' :
                    if($this->sbr->scheme_type != sbr::SCHEME_LC) {
                        $r = $this->sbr->getUserReqvHistory($this->id, $this->sbr->frl_id);
                        $reqv = $r['b'];
                    } else {
                        $reqv = $this->sbr->getFrlReqvs();
                    }
                    $dv = $reqv['rez_type'];
                    break;
                    
                case 'P' : 
                    if($this->status == sbr_stages::STATUS_COMPLETED || $this->status == sbr_stages::STATUS_ARBITRAGED) {
                        $user_id = ($role==sbr::EMP ? $this->sbr->emp_id : $this->sbr->frl_id);
                        if(!$user_id) $user_id = $this->uid;
                        if(!$this->payouts[$user_id])
                            $this->getPayouts($user_id);
                        $dv = $this->payouts[$user_id]['credit_sys'];
                    }
                    if($dv == null) { // ������ ��� �� ���� � �������!
                        $dv = $this->type_payment;
                    }
                    if($this->sbr->scheme_type == sbr::SCHEME_LC) {
                        $role = ($role==sbr::EMP ? "emp" : "frl");
                        $dv   = pskb::$exrates_map[$this->data['ps_'.$role]];
                    }
                    break;
                    
                case 'A' : 
                    if($this->status == sbr_stages::STATUS_ARBITRAGED) {
                        if($this->arbitrage === false)
                            $this->getArbitrage(false, false);
                        $dv = abs($role - $this->arbitrage['frl_percent']);
                    }
                    break;
                    
                case 'nNP' : 
                    if($this->status == sbr_stages::STATUS_COMPLETED || $this->status == sbr_stages::STATUS_ARBITRAGED) {
                        if($act = $this->getActSums($this->sbr->frl_id))
                            $dv = ($act['act_notnp']=='t');
                    }
                    break;
            }
            
            $dvals[$chr] = $dv;
        }

        return sbr_meta::calcAnyTax($tax['tax_id'], $tax['scheme_id'], $this->cost * $cost_coeff, $dvals);
    }
    
    /**
     * ���������� ������ � ������� �� �������
     * 
     * @param  int $role 0 - ���������, 1 - ������������
     * @param  array $dvals ���������
     * @param  integer $outsys � ����� ������ ���������� �����. NULL -- � ������ �������.
     * @return float
     */
    function getTaxInfo($role = NULL, $dvals = array(), $outsys = NULL) {
        $total = 0;
        $role = (int)($role === NULL ? ($this->sbr->isEmp() ? sbr::EMP : sbr::FRL) : $role);
        $taxes = array();
        $cost = $total_sum = $this->cost;
        $cost_sys = $this->sbr->cost_sys;
        
        $a = 1;
        if($this->status == sbr_stages::STATUS_ARBITRAGED) {
            if($this->arbitrage === false)
                $this->getArbitrage(false, false);
            $a = abs((int)$this->sbr->isEmp() - $this->arbitrage['frl_percent']);
        }
        $cost = $total_sum = round($a*$cost, 2);
        
        if(!$this->sbr->scheme)
            $this->sbr->getScheme();
        if($this->sbr->scheme['taxes'][$role]) { // � ������ ������ � ���������� ����� �� ���� ������� �������� � �������.
            foreach($this->sbr->scheme['taxes'][$role] as $tax) {
                $tax_total = $this->calcTax($tax, $dvals, $outsys);
                if (!floatval($tax_total)) continue;
                
                $total_sum -= round($tax_total, 2);
                
                $tmp['name'] = $tax['name'];
                $tmp['percent'] = $tax['percent'] * 100;
                $tmp['tax_cost'] = $tax_total;
                
                $taxes[] = $tmp;
            }
        }
        
        ob_start();

        include($_SERVER['DOCUMENT_ROOT'].'/norisk2/tpl.stage-taxes-info.php');
        return ob_get_clean();
    }
    
    /**
     * ���������� ������ � ������� �� �������
     * 
     * @param  int $role 0 - ���������, 1 - ������������
     * @param  array $dvals ���������
     * @param  integer $outsys � ����� ������ ���������� �����. NULL -- � ������ �������.
     * @return float
     */
    function _new_getTaxInfo($role = NULL, $dvals = array(), $outsys = NULL, $tax_hide = true) {
        $total = 0;
        $role = (int)($role === NULL ? ($this->sbr->isEmp() ? sbr::EMP : sbr::FRL) : $role);
        $taxes = array();
        $cost = $total_sum = $this->cost;
        $cost_sys = $this->sbr->cost_sys;
        $type_payment = $this->type_payment;
        
        $a = 1;
        if($this->status == sbr_stages::STATUS_ARBITRAGED) {
            if($this->arbitrage === false)
                $this->getArbitrage(false, false);
            $a = abs((int)$this->sbr->isEmp() - $this->arbitrage['frl_percent']);
        }
        $cost = $total_sum = round($a*$cost, 2);
        $total_sum_fm = round($total_sum / 30, 2);
        
        if(!$this->sbr->scheme)
            $this->sbr->getScheme();
        if($this->sbr->scheme['taxes'][$role]) { // � ������ ������ � ���������� ����� �� ���� ������� �������� � �������.
            foreach($this->sbr->scheme['taxes'][$role] as $tax) {
                $tax_total = abs($this->calcTax($tax, $dvals, $outsys));
                if (!floatval($tax_total)) continue;
                
                $total_sum -= round($tax_total, 2);
                $total_sum_fm -= round($tax_total / 30, 2);
                $tmp = $tax;
                $tmp['name'] = $tax['name'];
                $tmp['percent'] = $tax['percent'] * 100;
                $tmp['tax_cost'] = $tax_total;
                
                $taxes[] = $tmp;
            }
        }
        $RT = $this->sbr->getRatingSum($cost, $this->sbr->isEmp() ? sbr_meta::EMP_PERCENT_TAX : sbr_meta::FRL_PERCENT_TAX);
        
        $this->total_rating_stage = $RT;
        $this->total_sum_stage = $total_sum;
        $this->total_sum_stagefm = $total_sum_fm;
        ob_start();

        include($_SERVER['DOCUMENT_ROOT'].'/sbr/tpl.stage-taxes-info.php');
        return ob_get_clean();
    }
   
    /**
     * ������� ��� ������ ���� ����� ������ ��� ������
     * 
     * @param type $dvals
     * @param type $ps
     * @return type
     */
    function viewTaxesInfoMaster($dvals, $ps) {
        $role = (int)($this->sbr->isEmp() ? sbr::EMP : sbr::FRL);
        $taxes = array();
        $total_sum = $this->cost;
        
        if(!$this->sbr->scheme)
            $this->sbr->getScheme();
        
        if($this->sbr->scheme['taxes'][$role]) {
            foreach($this->sbr->scheme['taxes'][$role] as $tax) {
                $tax_total = abs($this->calcTax($tax, $dvals, exrates::BANK));
                if (!floatval($tax_total)) continue;
                
                $total_sum -= round($tax_total, 2);
                $tmp = $tax;
                $tmp['name'] = $tax['name'];
                $tmp['percent'] = $tax['percent'] * 100;
                $tmp['tax_cost'] = $tax_total;
                
                $taxes[] = $tmp;
            }
        }
        $RT = $this->sbr->getRatingSum($this->cost, $this->sbr->isEmp() ? sbr_meta::EMP_PERCENT_TAX : sbr_meta::FRL_PERCENT_TAX);
        
        $this->total_rating_stage = $RT;
        $this->total_sum_stage = $total_sum;
        ob_start();
        
        include($_SERVER['DOCUMENT_ROOT'].'/sbr/tpl.stage-taxes.php');
        return ob_get_clean();
    }
    
    /**
     * ���������� ������ �� ��������� ����� � ������� �����.
     * 
     * @param integer $user_id   ��. �����
     * @return array
     */
    function getSU($user_id) {
        $sql = "SELECT *, act_lcomm + act_lintr as act_sum FROM sbr_stages_users WHERE user_id = ?i AND stage_id = ?i";
        $sql = $this->db()->parse($sql, $user_id, $this->id);
        if($res = pg_query(self::connect(false), $sql))
            return pg_fetch_assoc($res);
        return NULL;
    }

    /**
     * ���������� ������ �� ���� ����� � ������ ����� (��������, ����, ������� �� �����).
     * 
     * @param integer $user_id   ��. �����
     * @param integer $force_new   ����� �� ���� �� ��������?
     * @return array
     */
    function getActSums($user_id) {
        return $this->getSU($user_id);
    }

    /**
     * ��������� ����� �� ������������ ������ ������ ��� �������.
     *
     * @param  int $ex_code ��� ������
     * @return int 1 - �����, 0 - ���
     */
    function checkPayoutSys($ex_code) {
        if($ex_code == exrates::WMZ) return 0; // ������
        if($ex_code != $this->sbr->cost_sys && $this->sbr->isEmp()) return 0; // ������������ ����� ������ � ������ �������.
        $this->sbr->getUserReqvs();
        if(($ex_code == exrates::YM || $ex_code == exrates::WMR) && $this->sbr->user_reqvs['form_type']==sbr::FT_JURI) return 0; // ���������-���� �� ����� � ��/WMR.
        if(($ex_code != exrates::BANK && $ex_code != exrates::YM && $ex_code != exrates::WMR ) && $this->sbr->user_reqvs['rez_type']==sbr::RT_UABYKZ) return 0; // ������������ ������ �� ������� + (WM + YD) -> #0017234
        return 1;
    }
    
    /**
     * ��������� ������� �� ��� ��������� ����������� ��� ������� � ������ ������.
     * � ����� �� ������������ ������ ������ ��� �������.
     * 
     * @param  int $ex_code ��� ������
     * @return bool true - ������� � �����, ����� false
     */
    function checkPayoutReqvs($ex_code) {
        if(!$this->checkPayoutSys($ex_code))
            return false;
        switch($ex_code) {
            case exrates::FM   : return true; break;
            case exrates::WMR  : 
                $bool = (bool)$this->sbr->user_reqvs[sbr::FT_PHYS]['el_wmr'] && 
                        (bool)$this->sbr->user_reqvs[sbr::FT_PHYS]['el_doc_series'] &&
                        (bool)$this->sbr->user_reqvs[sbr::FT_PHYS]['el_doc_number'] &&
                        (bool)$this->sbr->user_reqvs[sbr::FT_PHYS]['el_doc_from'];
                return $bool; 
                break;
            case exrates::YM   : 
                $bool = (bool)$this->sbr->user_reqvs[sbr::FT_PHYS]['el_yd'];
                return $bool; 
                break;
            case exrates::BANK :

                sbr_meta::getReqvFields();
                if(!($ft = $this->sbr->user_reqvs['form_type']))
                    $ft = sbr::FT_PHYS;
                $rt = $this->sbr->user_reqvs['rez_type'];
                $rq = $this->sbr->user_reqvs[$this->sbr->user_reqvs['form_type']];
                foreach(sbr_meta::$reqv_fields[$ft] as $key=>$field) {
                    if( $field['grp'] != 'BANK' || !$field['rez_required'] ) continue;
                    if( ($field['rez_required'] & $rt) && !$rq[$key] ) {
                        return false;
                    }
                }
                break;
            default :
                return false;
        }
        return true;
    }


    /**
     * �������������/�������� ������ ������� �� ����������� ����.
     * ��������� ��� ������/��������, ���� � ������ ��������� -- ��� ����������� ������������.
     *
     * @param integer $credit_sys   ��� ������ (��. ����� exrates).
     * @param boolean $any_sys   ��������� ����� ������ ��� ������� (��� �������).
     * @return boolean    �������?
     */
    function setPayoutSys($credit_sys, $any_sys = false, $role = null) {
        setlocale(LC_ALL, 'en_US.UTF-8');
        $credit_sys = intvalPgSql($credit_sys);
        
        if (!$any_sys && !$this->checkPayoutSys($credit_sys)) return false;

        if(($cost = $this->getPayoutSum($role, $credit_sys, NULL, $credit_sys)) <= 0)
            return false;

        $is_arbitrage = 'false';
        if($this->arbitrage['id']) {
            if(isset($this->arbitrage['resolved']) && !$this->arbitrage['resolved']) {
                return false;
            }
            $is_arbitrage = 'true';
        }

        $user_id = $this->sbr->uid;
        if ($role) {
            $user_id = $role == sbr::FRL ? $this->sbr->data['frl_id'] : $this->sbr->data['emp_id'];
        }
        
        $credit_sum = round($cost, 2);
        $sql = "
          UPDATE sbr_stages_payouts
             SET credit_sys = {$credit_sys}, credit_sum = {$credit_sum}, is_arbitrage = {$is_arbitrage}
           WHERE stage_id = {$this->id}
             AND user_id = {$user_id}
             AND completed IS NULL
        ";
        if($res = pg_query(self::connect(false), $sql)) {
            if(!pg_affected_rows($res)) {
                $state = $this->sbr->data['scheme_type'] == sbr::SCHEME_LC ? NULL : '' ;
                $sql = "INSERT INTO sbr_stages_payouts(stage_id, user_id, credit_sys, credit_sum, is_arbitrage, state) VALUES ({$this->id}, {$user_id}, {$credit_sys}, {$credit_sum}, {$is_arbitrage}, '{$state}');";
                $res = pg_query(self::connect(false), $sql);
            }
        }
        return !!$res;
    }

    /**
     * ����� ���������� �� ������� (���������� ��� �����������) ������������ � ������� �����.
     *
     * @param integer $user_id   ��. ������������.
     * @return array   ������ ������.
     */
    function getPayouts($user_id = NULL) {
        $where = "stage_id = {$this->id}" . ($user_id ? $this->db()->sql(" AND user_id = ?i", $user_id) : '');
        $sql = "SELECT * FROM sbr_stages_payouts WHERE {$where}";
        if($res = pg_query(self::connect(), $sql)) {
            while($row = pg_fetch_assoc($res))
                $this->payouts[$row['user_id']] = $row;
        }
        return $this->payouts;
    }

    /**
     * ��������� ������ � ��������.
     *
     * @param string $descr   ������� ���������.
     * @param array $files   ������ ������ ($_FILES).
     * @return boolean   �������?
     */
    function arbitrage($descr, $files) {
        // �������� �� ������ �� ���������� ����� � ��������
        $this->getArbitrage();
        if($this->arbitrage['id'] != null) {
            $this->error['arbitrage']['descr'] = '������ ��� ��������� � ���������';
            return false;
        }
        if(!($descr = pg_escape_string(change_q_x($descr, true, false)))) {
            $this->error['arbitrage']['descr'] = '���� �� ������ ���� ������';
            return false;
        }
        $fcnt = sbr::MAX_FILES;
        if($files) {
            foreach($files as $file) {
                $cfile = new CFile($file['id']);
                $cfile->table = 'file_sbr';
                $cfile->_remoteCopy($this->sbr->getUploadDir().$cfile->name);
                $this->uploaded_files[] = $cfile;
            } 
            /*$this->sbr->getUploadDir(); // !!! ���� ����� �����������, �� ����� � ����� ������ �������� ���������.
            foreach($files['name'] as $idx=>$aname) {
                foreach($files as $prop=>$a)
                    $att[$idx][$prop] = $a[$idx];
                if(--$fcnt < 0) break;
                $file = new CFile($att[$idx]);
                // �������� �����
                if($file->size > self::ARB_FILE_MAX_SIZE) {
                    $this->error['arbitrage']['err_attach'] = "������������ ����� ������: ".ConvertBtoMB(self::ARB_FILE_MAX_SIZE);
                    return false;
                }
                if( in_array($file->getext(), $GLOBALS['disallowed_array'])) {
                    $this->error['arbitrage']['err_attach'] = "������������ ������ �����";
                    return false;
                }
                if($err = $this->sbr->uploadFile($file, self::ARB_FILE_MAX_SIZE)) {
                    if($err == -1) continue;
                    else {
                        $this->error['arbitrage']['err_attach'] = $err;
                        break;
                    }
                }
                $this->uploaded_files[] = $file;
            }*/
        }
        if($this->error) return false;

        if(!$this->_openXact(TRUE))
            return false;

        $sql = "INSERT INTO sbr_stages_arbitrage (stage_id, user_id, descr) VALUES ({$this->id}, {$this->sbr->uid}, '{$descr}') RETURNING id";
        if(!($res = pg_query(self::connect(false), $sql)) || !pg_num_rows($res)) {
            $this->_abortXact();
            return false;
        }
        $id = pg_fetch_result($res,0,0);
        $sql_attach = '';
        if($this->uploaded_files) {
            foreach($this->uploaded_files as $file) {
                if(!$file->id) continue;
                $file->orig_name = pg_escape_string($file->original_name);
                $file->orig_name = $file->shortenName($file->orig_name, 128);
                if (!$file->orig_name) {
                    continue;
                }
                $sql_attach .= "INSERT INTO sbr_stages_arbitrage_attach(arbitrage_id, file_id, orig_name) VALUES({$id}, {$file->id}, '{$file->orig_name}');";
            }
        }
        if($sql_attach && !pg_query(self::connect(false), $sql_attach)) {
            $this->_abortXact();
            return false;
        }

        $this->_commitXact();
        return true;
    }

    /**
     * ����� ���������� �� ���������, ��������� $this->arbitrage.
     *
     * @param boolean $get_user   ����� �� ��������� ���������� �� ������������, ������������� � ��������.
     * @param boolean $get_attach   ������ ������������� � ��������� �����?
     * @return array   ������ �� ���������.
     */
    function getArbitrage($get_user = false, $get_attach = true) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/LocalDateTime.php';
        if($get_user) {
            $join_u = 'INNER JOIN users u ON u.uid = sa.user_id';
            $cols_u = ', u.login, u.uname, u.usurname, u.photo, u.role, u.is_pro, u.is_team, u.is_pro_test';
        }
        $sql = "
          SELECT sa.* {$cols_u}
            FROM sbr_stages_arbitrage sa
          {$join_u}
           WHERE sa.stage_id = {$this->id}
        ";
        if($res = pg_query(self::connect(), $sql)) {
            $this->arbitrage = pg_fetch_assoc($res);
            if($this->arbitrage && $get_attach) {
                $sql = "
                  SELECT saa.*, f.fname as name, f.path, f.size
                    FROM sbr_stages_arbitrage_attach saa
                  INNER JOIN
                    file_sbr f
                      ON f.id = saa.file_id
                  WHERE saa.arbitrage_id = {$this->arbitrage['id']}
                ";
                if($res = pg_query(self::connect(), $sql)) {
                    while($row = pg_fetch_assoc($res))
                        $this->arbitrage['attach'][$row['id']] = $row;
                }
            }
            // ������� ����� ���������� ������� ���������� �������
            if($this->sbr->isFrl() && $this->arbitrage['frl_percent'] == 0 && $this->data['frl_completed'] == 't') {
                $this->data['frl_feedback_id'] = true;
            }
            if($this->sbr->isEmp() && $this->arbitrage['frl_percent'] == 1 && $this->data['emp_completed'] == 't') {
                $this->data['emp_feedback_id'] = true;
            }
        }
        if(!$this->arbitrage) {
            $this->arbitrage = NULL;
        }  else {
            $ldt = new LocalDateTime(date('d.m.Y H:i:s', strtotime($this->arbitrage['requested'])));
            $ldt->getWorkForDay(self::MAX_ARBITRAGE_DAYS);
            $overtime_arbitrage = $ldt->getTimestamp();
            $this->arbitrage['overtime_arbitrage'] = $overtime_arbitrage;
        }
        return $this->arbitrage;
    }
    
    function getStrOvertimeArbitrage() {
        if(!$this->arbitrage)
            return false;       
        return date('d', $this->arbitrage['overtime_arbitrage']) . ' ' . monthtostr(date('n', $this->arbitrage['overtime_arbitrage']), true) . ' ' . date('Y', $this->arbitrage['overtime_arbitrage']);
    }

    /**
     * ����� ������� �� ���������.
     *
     * @param array $request   ��������� ������� (�����, ������� ����������)
     * @return boolean   �������?
     */
    function arbResolve($request) {
        $must_filled = array('descr_arb', 'reason', 'init', 'result');
        foreach($must_filled as $f) {
            $this->request['pp_'.$f] = $request['pp_'.$f];
            $this->request[$f] = stripslashes($request[$f]);
            switch($f) {
                case 'reason':
                case 'result':
                    if($this->sbr->isNewVersionSbr()) { // � ����� ������ ����� ���� �����
                        break;
                    }
                default:
                    if (!trim($this->request[$f]))
                        $this->error['arbitrage'][$f] = '����� ���-������ ��������';
                    break;
                    
            }
        }
        $this->request['by_consent'] = $request['by_consent'];
        $this->request['frl_percent'] = $request['frl_percent'];

        if ((int) $this->request['frl_percent'] <= 0 && (int) $request['emp_percent'] <= 0)
            $this->error['arbitrage']['frl_percent'] = '����� ����-������ ���-������ ������';
        if ($this->error)
            return false;

        $frl_percent = $this->request['frl_percent'] / 100;
        $descr_arb = pg_escape_string(change_q_x($this->request['descr_arb'], true, false));
        $reason = pg_escape_string(change_q_x($this->request['reason'], true, false));
        $init = pg_escape_string(change_q_x($this->request['init'], true, false));
        $result = pg_escape_string(change_q_x($this->request['result'], true, false));
        $by_consent = $this->request['by_consent'] ? 'true' : 'false';
        $result_id = is_numeric(substr($request['pp_result'], 0, 1)) ? (int) substr($request['pp_result'], 0, 1) : 0;

        $sql = "
              UPDATE sbr_stages_arbitrage
                 SET resolved = now(),
                     frl_percent = {$frl_percent},
                     descr_arb = '{$descr_arb}',
                     reason = '{$reason}',
                     init = '{$init}',
                     result = '{$result}',
                     result_id = {$result_id},
                     by_consent = {$by_consent}
               WHERE stage_id = {$this->id}
            ";

        return $this->_eventQuery($sql);
    }


    /**
     * �������� ���������(!) � ��������. � ������� ��� ��������� ��������� ����� ��������.
     * @return boolean   �������?
     */
    function arbCancel() {
       $sql = "DELETE FROM sbr_stages_arbitrage WHERE stage_id = {$this->id}";
       return $this->_eventQuery($sql);
    }


    /**
     * ���� �� ������ ������ ������� ���������.
     * @return string
     */
    function view_arb_descr_full() {
        ob_start();
    ?>
        <p><strong>��������� ��������� <?=100*(1-$this->arbitrage['frl_percent'])?>% (<?=sbr_meta::view_cost($this->getPayoutSum(sbr::EMP), $this->sbr->cost_sys)?>)</strong><br />
        <strong>����������� ��������� <?=100*$this->arbitrage['frl_percent']?>% (<?=sbr_meta::view_cost($this->getPayoutSum(sbr::FRL), $this->sbr->cost_sys)?>)</strong></p>

        <p><?=reformat($this->arbitrage['descr_arb'], 40, 0, 0, 1)?></p>
        <p>����������� �������� Free-lance.ru<br /><?=date('d.m.Y H:i', strtotime($this->arbitrage['resolved']))?></p>
    <?
        return ob_get_clean();
    }

    /**
     * ���� �� ������ ������ ������� ��������� (����������� ����).
     *
     * @param array $arb   ������ ������ �� ���������.
     * @return string
     */
    function arb_descr() {
        $stage = $this;
        if($stage->arbitrage === false)
            $stage->getArbitrage(TRUE);
        if(!$stage->arbitrage) return NULL;
        ob_start();
        if(!$stage->arbitrage['resolved']) {
            include($_SERVER['DOCUMENT_ROOT'].'/norisk2/tpl.currents-arb_reason.php');
        }
        else {
            $show_pay_info = true;
            $emp = new employer();
            $frl = new freelancer();
            $emp->GetUserByUID($this->sbr->emp_id);
            $frl->GetUserByUID($this->sbr->frl_id);
            if( ($this->sbr->isEmp() && $stage->arbitrage['frl_percent'] >= 1 || $this->sbr->isFrl() && $stage->arbitrage['frl_percent'] <= 0) ||
                $this->getPayouts($this->sbr->uid) )
            {
                $show_pay_info = false;
            }
            include($_SERVER['DOCUMENT_ROOT'].'/norisk2/tpl.currents-arb_solution.php');
        }
        return ob_get_clean();
    }


    /**
     * ������. �������� ����� ������������.
     *
     * @param array $msg   ������������ ���� � ��������� 'children' -- ������ �������� �����.
     * @param boolean $need_box   ����� �� ������������ � <ul>
     */
    function msg_nodes($msg, $need_box = true) {
        if(!$msg['children']) return;
        if($need_box)                     echo '<ul class="cl-ul">';
        foreach($msg['children'] as $msg) echo $this->msg_node($msg);
        if($need_box)                     echo '</ul>';
    }
  
    /**
     * ������. �������� ���� �����������.
     * 
     * @param array $msg   ���������� �� �����������.
     * @return string
     */
    function msg_node($msg) {
        global $session;
        static $pos = 0;
        static $prev_post_time = 0;
        $post_time = strtotime($msg['post_date']);
        $li_in_cls = ($pos && !$msg['level'] ? ' cl-li-first' : '').($post_time > strtotime($this->last_msgs_view) ? ' cl-li-new' : '').($msg['is_admin']=='t' ? ' nr-ua' : '');
        $is_edit = $this->post_msg['id'] == $msg['id'];
        if($this->post_msg && $is_edit || !$this->post_msg['id'] && $this->post_msg['parent_id'] == $msg['id']) { 
            if($is_edit) {
                // ����� ������ ����� ��� �������������� (���� ���� ������ �����). ��������� post_msg ������������ ������� �� ��������.
                foreach($msg as $f=>$v) {
                    if(!isset($this->post_msg[$f]))
                        $this->post_msg[$f] = $v;
                }
            }
            $edit_form = $this->msg_form($this->post_msg, $this->error['msgs'], true);
        }

        if(!$msg['level'] && $this->sbr->docs) {
            while($doc = $this->sbr->docs[0]) {
                $doc_time = strtotime($doc['publ_time']);
                if( !($doc_time < $post_time && $doc_time > $prev_post_time) ) break;
                array_shift($this->sbr->docs);
                echo $this->sbr->doc_node($doc);
            }
            $prev_post_time = $post_time;
        }
    ?>
        <li class="cl-li<?=(!$msg['level'] ? ' first' : '')?>">
            <div class="cl-li-in<?=$li_in_cls?>" id="c_<?=$msg['id']?>"><?=$this->msg_node_content($msg)?></div>
            <div id="msg_form_box<?=$msg['id']?>"><?=$edit_form?></div>
            <? if($msg['level'] < 13) { ?>
                <?=$this->msg_nodes($msg)?>
            <? } ?>
        </li>
        <? if($msg['level'] >= 13) { ?>
            <?=$this->msg_nodes($msg, false)?>
        <? } ?>
    <?
        ++$pos;
    }

    /**
     * ������. �������� ���������� �����������.
     * @see sbr_stages::msg_node()
     * 
     * @param array $msg   ���������� �� �����������.
     * @return string
     */
    function msg_node_content($msg) {
        global $session;
        if($msg['moduser_id']) {
            $mod_a = $msg['moduser_id'] != $msg['user_id'] ? 'a' : 'u';
            $mod_alt = ($mod_a=='a' ? '��������������� ��������������: ' : '������� ���������: ') . date('d.m.Y | H:i', strtotime($msg['modified']));
        }
        $stage = $this;
        ob_start();
        include($_SERVER['DOCUMENT_ROOT'].'/norisk2/tpl.stage-msg_node_content.php');
        return ob_get_clean();
    }

    /**
     * ������. �������� ����� ����������/�������������� �����������/���������.
     *
     * @param array $msg   ���������� �� �����������.
     * @param array $error   ���������� �� ������� (��� ���������� �������� �����).
     * @param boolean $static   true, ���� ����� �� ������������ (�������� �� �������� ����� ����� ��������).
     * @return string
     */
    function msg_form($msg, $error = NULL, $static = false) {
        $site = 'Stage';
        $action = $msg['id'] ? 'msg-edit' : 'msg-add';
        $is_main = ($msg['id']==='0');
        $is_edit = !!$msg['id'];
        $is_new = !($is_main || $is_edit);
        $form_key = $is_main ? 0 : ($msg['id'] ? $msg['id'] : $msg['parent_id']);
        ob_start();
        include($_SERVER['DOCUMENT_ROOT'].'/norisk2/tpl.stage-msg_form.php');
        return ob_get_clean();
    }

    /**
     * ��������� ���� ��� ���� �������/����������� ����� �� � �����.
     * @return string
     */
    function getCCTZKey() {
       return 'SBR_CCTZ'.$this->id;
    }

    /**
     * �������, ����� �� ���������� ���� �� � ����� ��� ������.
     * @return boolean
     */
    function isTzOpened() {
       return ( !isset($_COOKIE[$this->getCCTZKey()]) || $_COOKIE[$this->getCCTZKey()]==1 );
    }
    
     /**
     * ���������� ����� �� ��������� � PDF ����� �������� �������.
     * 
     * @param array $error   ������ ������ � ��������.
     * @param array $rep_num
     * @param string $type ��� ��������� (��������� sbr::FRL|������������ sbr::EMP)
     * @return CFile   ����������� ��������.
     */
    function generateArbReportPdrdFrl(&$error) {
        $pr = array();
        $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/arb_emp.xml';
        $pdf = null;
        
        if(!$this->getArbitrage(false, false))
            return NULL;

        if(!$this->arbitrage['resolved'] || $this->arbitrage['frl_percent'] <= 0 || $this->arbitrage['frl_percent'] >= 1)
            return NULL;

        if(!($act = $this->getActSums($this->sbr->frl_id)))
            return false;

        $error = NULL;
        $cnum = $this->sbr->getContractNum();
        $ssnum = $this->getOuterNum4Docs();
        
        //$this->sbr->getFrlReqvs();
        $this->sbr->getUserReqvHistoryData($this->id, 'frl');
        // !!! 
        if(!$this->sbr->checkUserReqvs($this->sbr->frl_reqvs)) {
            $error['frl'] = '��� ������������ ������ �� ��������� �����������<br/> ���������� ��������� ��������� �� �������� "�������"';
        }
        
        $ffio = sbr_meta::getFioFromReqvs($this->sbr->frl_reqvs);
        if(!$ffio) {
            $error['frl'] = '��� ������������ ������ �� ��������� ����������� ���������� ��������� ��� �� �������� <a href="/users/'.$this->sbr->frl_login.'/setup/finance/" class="blue" target="_blank">�������</a>';
            return false;
        }

        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/num_to_word.php';
        $pr['$act_num'] = $cnum;
        $pr['$ssnum'] = $ssnum;
        $pr['$job_name'] = $this->name;
        $pr['$from_act_year'] = date('d.m.Y', strtotime($this->closed_time));   
        $frl_sum = $this->getPayoutSum(sbr::FRL, exrates::BANK) + $act['act_lndfl'] + $act['act_lnds'];
        if($this->sbr->frl_reqvs['form_type']==sbr::FT_JURI) {
            $frl_nds_s = ', � ��� ����� ��� 18% � '.num2strEx(18*$frl_sum/118);
        }
        $side_b = sbr_meta::getReqvsStr($this->sbr->frl_reqvs, $pr['$bossname']);
        $pr['$fio'] = $ffio;
        $pr['$frl_sum'] = num2strEx($frl_sum).$frl_nds_s;
        $pr['$side_b'] = $side_b;
        $pr['$adr_act'] = "129223, ������, �/� 33";
        if($this->sbr->frl_reqvs['rez_type'] == sbr::RT_RU) {
            $pr['$adr_act'] .= ";\r\n"; 
            $pr['$adr_act'] .= "190031, �����-���������, ������ ��., �.13 / 52, �/� 427;\r\n";
            $pr['$adr_act'] .= "420032, ������, �/� 624;\r\n";
            $pr['$adr_act'] .= "454014, ���������-14, �/� 2710.\r\n";
        } else {
            $pr['$adr_act'] .= ".\r\n"; 
        }
            
        $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/arb_frl.xml';
        $pdf = sbr::xml2pdf($template,$pr);

        if(!($file = $this->sbr->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ������ �� ���������";

        return $file;
    }

     /**
     * ���������� ����� �� ��������� � PDF ����� �������� �������.
     * 
     * @param array $error   ������ ������ � ��������.
     * @param array $rep_num
     * @param string $type ��� ��������� (��������� sbr::FRL|������������ sbr::EMP)
     * @return CFile   ����������� ��������.
     */
    function generateArbReportPdrdEmp(&$error) {
        $pr = array();
        $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/arb_emp.xml';
        $pdf = null;
        
        if(!$this->getArbitrage(false, false))
            return NULL;

        if(!$this->arbitrage['resolved'] || $this->arbitrage['frl_percent'] >= 1)
            return NULL;
        
        $error = NULL;
        $cnum = $this->sbr->getContractNum();
        $ssnum = $this->getOuterNum4Docs();
        
        //$this->sbr->getEmpReqvs();
        $this->sbr->getUserReqvHistoryData($this->id, 'emp');
        $this->sbr->setCheckEmpReqvs($this->id);

        // !!!
        if(!$this->sbr->checkUserReqvs($this->sbr->emp_reqvs)) {
            $error['emp'] = '��� ������������ ������ �� ��������� ���������<br/> ���������� ��������� ��������� �� �������� "�������"';
        }
        
        $efio = sbr_meta::getFioFromReqvs($this->sbr->emp_reqvs);
        if(!$efio) {
            //$efio = '������������ � ���������� ����';
            $error['emp'] = '��� ������������ ������ �� ��������� ��������� ���������� ��������� ��� �� �������� <a href="/users/'.$this->sbr->emp_login.'/setup/finance/" class="blue" target="_blank">�������</a>';
            return false;
        }
        if(!($frl_act = $this->getActSums($this->sbr->frl_id)))
            return false;
        if(!($emp_act = $this->getActSums($this->sbr->emp_id)))
            return false;

        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/num_to_word.php';
        $pr['$act_num'] = $cnum;
        $pr['$ssnum'] = $ssnum;
        $pr['$job_name'] = $this->name;
        $pr['$from_act_year'] = date('d.m.Y', strtotime($this->closed_time));   
        if($this->sbr->emp_reqvs['form_type'] == sbr::FT_PHYS) {
            $side_b = sbr_meta::getReqvsStr($this->sbr->emp_reqvs, $pr['$bossname'])
                    . "��������� ��������:\r\n\r\n"
                    . sbr_meta::getPayoutReqvsStr($this->sbr->emp_reqvs, $this->sbr->cost_sys);
        } else {
            $side_b = sbr_meta::getReqvsStr($this->sbr->emp_reqvs, $pr['$bossname']);
        }

        $frl_sum = $this->getPayoutSum(sbr::FRL, exrates::BANK) + $frl_act['act_lndfl'] + $frl_act['act_lnds'] + $frl_act['act_sum'] + $emp_act['act_sum'];
        $frl_nds_s = ', � ��� ����� ��� 18% � '.num2strEx(18*$frl_sum/118);
        $emp_sum = $this->getPayoutSum(sbr::EMP, exrates::BANK);
        $emp_nds_s = ', � ��� ����� ��� 18% � ������� '.num2strEx(18*$emp_sum/118);

        $rq = $this->sbr->emp_reqvs[$this->sbr->emp_reqvs['form_type']];
        $pr['$frl_sum'] = num2strEx($frl_sum).$frl_nds_s;
        $pr['$emp_sum'] = num2strEx($emp_sum).$emp_nds_s;
        $pr['$fio'] = $efio;//$rq['bossname'];
        $pr['$organization'] = $rq['full_name'];
        $pr['$side_b'] = $side_b;
        $pr['$adr_act'] = "129223, ������, �/� 33";
        if($this->sbr->emp_reqvs['rez_type'] == sbr::RT_RU) {
            $pr['$adr_act'] .= ";\r\n"; 
            $pr['$adr_act'] .= "190031, �����-���������, ������ ��., �.13 / 52, �/� 427;\r\n";
            $pr['$adr_act'] .= "420032, ������, �/� 624;\r\n";
            $pr['$adr_act'] .= "454014, ���������-14, �/� 2710.\r\n";
        } else {
            $pr['$adr_act'] .= ".\r\n"; 
        }    
        $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/arb_emp.xml';
        $pdf = sbr::xml2pdf($template,$pr);

        if(!($file = $this->sbr->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ������ �� ���������";

        return $file;
    }

    /**
     * ���������� ����� �� ��������� � PDF ����� �������� ������� (��� ����������).
     * 
     * @param array $error   ������ ������ � ��������.
     * @return CFile   ����������� ��������.
     */
    function generateArbReport(&$error, &$rep_num) {
        require_once(dirname(__FILE__).'/fpdf/fpdf.php');
        define('FPDF_FONTPATH',(dirname(__FILE__).'/fpdf/font/'));

        if(!$this->getArbitrage(false, false))
            return NULL;

        if(!($act = $this->getActSums($this->sbr->frl_id)))
            return false;

        $error = NULL;
        $cnum = $this->sbr->getContractNum();
        $ssnum = $this->getOuterNum4Docs();
        $this->sbr->getFrlReqvs(true);
        $this->sbr->getEmpReqvs(true);
        $this->sbr->setCheckEmpReqvs($this->id);
        
        $eper = (1-$this->arbitrage['frl_percent'])*100;
        $fper = 100-$eper;
        
        // !!! 
        if(!$this->sbr->checkUserReqvs($this->sbr->frl_reqvs) && $fper) {
            $error['frl'] = '��� ������������ ������ �� ��������� �����������<br/> ���������� ��������� ��������� �� �������� "�������"';
        }
        
        if(!$this->sbr->checkUserReqvs($this->sbr->emp_reqvs)) {
            $error['emp'] = '��� ������������ ������ �� ��������� ���������<br/> ���������� ��������� ��������� �� �������� "�������"';
        }
        
        $efio = sbr_meta::getFioFromReqvs($this->sbr->emp_reqvs);
        $ffio = sbr_meta::getFioFromReqvs($this->sbr->frl_reqvs);
        
        if(!$efio) {
            //$efio = '������������ � ���������� ����';
            $error['emp'] = '��� ������������ ������ �� ��������� ��������� ���������� ��������� ��� �� �������� <a href="/users/'.$this->sbr->emp_login.'/setup/finance/" class="blue" target="_blank">�������</a>';
        }
        if(!$ffio && $fper) {
            $error['frl'] = '��� ������������ ������ �� ��������� ����������� ���������� ��������� ��� �� �������� <a href="/users/'.$this->sbr->frl_login.'/setup/finance/" class="blue" target="_blank">�������</a>';
        }
        if($error)
            return false;
        
        if (!$ffio && !$fper) {
            $ffio = '�����������';
        }

        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/num_to_word.php';
        if($eper && ($esum = $this->getPayoutSum(sbr::EMP))) {
            $esum = round($esum, 2);
            $esums = num2strEx($esum);
            if($this->sbr->emp_reqvs['form_type']==sbr::FT_JURI) {
                $KK = 'K15';
            } else {
                $KK = 'K42';
            }
            $csys = $GLOBALS['EXRATE_CODES'][$this->sbr->cost_sys][4];
            $esums = ", � ������, {$esums}, ������ ������ {$csys}";
            $esums_not_reqv = $esums;
            $esums .= "\r\n��������� ������������:\r\n"
                   .  sbr_meta::getPayoutReqvsStr($this->sbr->emp_reqvs, $this->sbr->cost_sys);
        }
        if($fper && ($fsum = $this->getPayoutSum(sbr::FRL, exrates::BANK) + $act['act_lndfl'] + $act['act_lnds'] + $act['act_lcomm'])) {
            $fsum = round($fsum, 2);
            $fsums = num2strEx($fsum);
            $KK = 'K36-K33';
            if($this->sbr->frl_reqvs['form_type']==sbr::FT_JURI) {
                $KK = 'K14-K10';
                $fnds = num2strEx(18*$fsum/118);
                $fndss = ", � ��� ����� ��� 18% � {$fnds}";
            }
            
            $fsums = ", � ������, {$fsums}{$fndss}";
            if($payout = $this->getPayouts($this->sbr->frl_id)) {
                $payout = $payout[$this->sbr->frl_id];
                $fcsys  = $GLOBALS['EXRATE_CODES'][$payout['credit_sys']][4];
                $payout_method = sbr_meta::getPayoutMethodStr($this->sbr->frl_reqvs, $payout['credit_sys'], '');
            }
        }
        
        $ess = "{$efio}, {$eper}% �� ����� �� �������� � {$cnum}{$ssnum}{$esums}";
        //if ($fper) {
        $fss = "��������� ����� �����������: {$ffio}, {$fper}% �� ����� �� �������� � {$cnum}{$ssnum}{$fsums}.";
        //}

        if ($this->sbr->emp_reqvs['form_type']==sbr::FT_JURI && ($this->sbr->cost_sys == exrates::WMR || $this->sbr->cost_sys == exrates::YM)) {
            $efio = "{$efio} � ���������� ����";
        }

        $rep_num = $this->sbr->regArbReportNum();
        $init = html_entity_decode($this->arbitrage['init'], ENT_QUOTES, 'cp1251');
        $result = html_entity_decode($this->arbitrage['result'], ENT_QUOTES, 'cp1251');
        
        // #0018645 -- otchet_ob_arbitrage_bez_ispravlenii_12072012.doc 
        if(strpos($result, '�������� � ������������ ������ ����������� ����������.') !==  false) {
            $rast = true;
        }
        if(strpos($result, '�� ������������� ������ ��������� ������ �����������') !== false) {
            $frl_full_pay = ($fper == 100);
        }
        
        if(strpos($result, '�� ������������� �������� ��������� ������ ���������') !== false) {
            $emp_full_pay = ($eper == 100);
        }
        
        if ($payout_method) {
            $payout_method = "���������: " . $payout_method;
        }
        
        if($fper != 100) {
            $payout_method = "";
        }

        $cefio = $efio;
        $cffio = $ffio;
        $efio = '�'.$efio.'�';
        $ffio = '�'.$ffio.'�';
        
        $template = $_SERVER['DOCUMENT_ROOT'] . '/norisk2/xml/arb_report.xml';
        $pdf = sbr::xml2pdf($template, array(
        '$cnum' => $cnum,
        '$ssnum' => $ssnum,
        '$efio' => $efio,
        '$ffio' => $ffio,
        '$cefio' => $cefio,
        '$cffio' => $cffio,
        '$ifio' => $this->arbitrage['user_id'] == $this->sbr->frl_id ? $ffio : $efio,
        '$rep_num' => $rep_num,
        '$rep_from' => date('d.m.Y'),
        '$esums' => $esums,
        '$esums_not_reqv' => $esums_not_reqv,
        '$fsums' => $fsums,
        '$ess' => $ess,
        '$fss' => $fss,
        '$payout_method' => $payout_method,
        '$init' => $init,
        '$fcsys' => $fcsys, 
        '$result' => $result,
        '$fper' => $fper,
        '$fper100' => $frl_full_pay,
        '$eper100' => !$rast ? $emp_full_pay : false,
        '$rastorg' => $rast,
        '$sbr_begin' => date('d.m.Y',strtotime($this->getFirstTime())),
        '$sbr_end' => date('d.m.Y',strtotime($this->closed_time)),
        '$stage_name' => html_entity_decode($this->name, ENT_QUOTES, 'cp1251'),
        '$stage_work_days' => $this->work_days . ' '. ending($this->work_days, '����', '���', '����'),
        '$stage_cost' => num2strEx($this->cost),
        ));
        
        if(!($file = $this->sbr->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ������ �� ���������";

        return $file;
    }
    
    /**
     * ���������� ��������� �� �������� ����������� (������ ��������� - �����)
     * 
     * @param string $error
     * @param type $lc
     * @return type 
     */
    public function generateStatement(&$error, $lc = false) {
        require_once (dirname(__FILE__).'/num_to_word.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/LocalDateTime.php");
        $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/doc_statement.xml';
        
        $sbr_num = $this->sbr->getContractNum(null, null);
        
        $this->sbr->getFrlReqvs();
        $this->sbr->getEmpReqvs();
        $this->sbr->setCheckEmpReqvs($this->id);
        
        if(!$lc) {
            $pskb = new pskb($this->sbr);
            $lc = $pskb->getLC();
        }
        $days_acredit = round(( strtotime($lc['dateExecLC']) - time() ) / (3600*24) );
        $days_acredit = $days_acredit . " " . ending($days_acredit, '����', '���', '����');
        if($lc['ps_frl'] == onlinedengi::BANK_YL) {
            $frl_reqv_payment  = $lc['accPerf']."\r\n";
            $frl_reqv_payment .= "���: {$lc['psPerf']}";
        } else {
            $frl_reqv_payment = $lc['accPerf'];
        }
        
        if($lc['ps_emp'] == onlinedengi::BANK_YL) {
            $emp_reqv_payment  = $lc['accCust']."\r\n";
            $emp_reqv_payment .= "���: {$lc['psCust']}";
        } else {
            $emp_reqv_payment = $lc['accCust'];
        }
        
        if($lc['ps_frl'] == onlinedengi::WMR || $lc['ps_frl'] == onlinedengi::YD ) {
            $tax_percent_vaan = '5.4%'; //@todo ���� ��� �� ��� ����� ����� �� �����
            $tax_percent_bank = '0.6%';
        } elseif($lc['ps_frl'] != pskb::WW) {
            $tax_percent_vaan = '2.5%';
            $tax_percent_bank = '0.5%';
        } else {
            $tax_percent_bank = '0%';
        }
        $tax_percent_bank = sbr_meta::getTaxPercent(sbr::FRL, pskb::$exrates_map[$lc['ps_frl']], '�������� �����', $this->sbr->scheme_type) . "%";
        
        $dvals = array('P' => pskb::$exrates_map[$lc['ps_emp']]);
        $tax_total = 0;
        $tax_bank  = 0;
        $total_cost = $this->sbr->cost;
        foreach($this->sbr->scheme['taxes'][sbr::EMP] as $tax) {
            if($tax['tax_code'] != 'TAX_FL') {
                $tax_bank += sbr_meta::calcAnyTax($tax['tax_id'], $tax['scheme_id'], $total_cost, $dvals);//$stage->calcTax($tax, $dvals, $outsys);
                continue;
            }
            $tax_total += sbr_meta::calcAnyTax($tax['tax_id'], $tax['scheme_id'], $total_cost, $dvals);//$stage->calcTax($tax, $dvals, $outsys);
            if (!floatval($tax_total)) continue;
        }
        $sum_reserved = $this->sbr->getReserveSum(true, pskb::$exrates_map[$lc['ps_emp']]);
        
        $sum_vaan     = num2strL(round($tax_total,2));
        $sum_vaan_nds = num2strEx(round($tax_total/118*18, 2));
        
        $replace = array(
            '$sbr_num'      => $sbr_num,
            '$sbr_date'     => date("d.m.Y", strtotime($this->sbr->sended)),
            '$efio'         => $lc['nameCust'],
            '$ffio'         => $lc['namePerf'],
            '$num_acredit'  => $lc['lc_id'],
            '$date_acredit' => date('d.m.Y'),
            '$sum'          => num2strL($this->sbr->cost),
            '$sum_str'      => num2str($this->sbr->cost, true),
            '$emp_payment_method_name' => pskb::$psys[pskb::USER_EMP][$lc['ps_emp']],
            '$emp_reqv_payment' => $emp_reqv_payment,
            '$frl_payment_method_name' => pskb::$psys[pskb::USER_FRL][$lc['ps_frl']],
            '$frl_reqv_payment' => $frl_reqv_payment,
            '$frl_phone'        => $lc['numPerf'],
            '$days_acredit'     => $days_acredit,
            '$tax_percent_vaan' => $tax_percent_vaan,
            '$tax_percent_bank' => $tax_percent_bank,
            '$sum_vaan'         => $sum_vaan,
            '$sum_vaan_nds'     => $sum_vaan_nds,
            '$sum_reserved'     => num2strL($sum_reserved),
            '$sum_reserved_str' => num2str($sum_reserved, true),
            '$tax_bank'         => num2strL($tax_bank),
            '$sum_d'            => num2strD($this->sbr->cost),
            '$tax_bank_d'       => num2strD($tax_bank),
            '$sum_vaan_d'       => num2strD(round($tax_total,2)),
            '$sum_vaan_nds_d'   => num2strD(round($tax_total/118*18, 2)),
        );
        
        $pdf = sbr::xml2pdf($template,$replace);
        
        if(!($file = $this->sbr->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ��������� �� �������� �����������";
        return $file;
    }
    
    /**
     * ���������� ����� ��������� ����
     * - ����� �������� ������� ������� � 100%  ������� �����������
     * - ����� ������� �������� � ���������� � 100% ������� �����������
     * 
     * @param type $error   ������
     * @param type $rep_num
     * @return null|boolean 
     */
    public function generateArbReportEmp(&$error, &$rep_num) {
        require_once (dirname(__FILE__).'/num_to_word.php');
        
        if(!$this->getArbitrage(false, false))
            return NULL;

        if(!($act = $this->getActSums($this->sbr->frl_id)))
            return false;
        
        $eper = (1-$this->arbitrage['frl_percent'])*100;
        $fper = 100-$eper;
        
        $result = html_entity_decode($this->arbitrage['result'], ENT_QUOTES, 'cp1251');
        $init   = html_entity_decode($this->arbitrage['init'], ENT_QUOTES, 'cp1251');
        $reason  = html_entity_decode($this->arbitrage['reason'], ENT_QUOTES, 'cp1251');
        
        $is_frl_arb  = ( strpos($reason, '�������� �. 8.5.') !== false );
        $is_emp_arb  = ( strpos($reason, '�������� �. 8.3.') !== false );
        $is_vaan_arb = ( strpos($init, '��� ���� ���������� � ��������') !== false );
        
        if(strpos($init, '����������� ��������� � ��������') !== false ) {
            $user_arb = '�����������';
        } elseif(strpos($init, '�������� ��������� � ��������') !== false ) {
            $user_arb = '��������';
        } elseif($is_vaan_arb) {
            $user_arb = '��� "����"';
        }
        
        if($eper == 100) {
            $reason = html_entity_decode($this->arbitrage['reason'], ENT_QUOTES, 'cp1251');
            $result = html_entity_decode($this->arbitrage['result'], ENT_QUOTES, 'cp1251');
            if(strpos($reason, 'C������� �. 9.1.2. ����������� ����������') !== false || 
               strpos($result, '���������� ������ ����������� ����������') !==  false) {
                $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/arb_act_rastorg.xml';
            } else {
                if(strpos($result, '���������� ������ 100% ������� ���������') !==  false) {
                    $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/arb_emp_soglashenie.xml';
                } elseif(strpos($result, '������� ��������� 100% ������� ���������, �.�. ������ �� ���������') !==  false ||
                        strpos($result, '������� ��������� 100% ������� ���������, �.�. ������ ��������� �� ���������, �� � ����') !==  false) {
                    $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/arb_emp_reshenie.xml';

                    if(strpos($result, '������� ��������� 100% ������� ���������, �.�. ������ �� ���������') !==  false) {
                        $result_arb = '������������ �� ���� ��������� ������.';
                    } else {
                        $result_arb = "������������ ���� ��������� ������ ������������ �������, � ������: \r\n" . $result;
                    }
                } elseif(strpos($result, '������� ���������') !==  false) { // �� ���� �� ����� ���� �� ��� ������� 100% ��������� ����� �������� ������ 5,6
                    $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/arb_act_reshenie.xml';
                } else { 
                    return null;
                }
            }
        } else if($eper >= 0) {
            if(strpos($result, '���������� ������') !==  false) {
                $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/arb_act_soglashenie.xml';
            } elseif(strpos($result, '������� ���������') !==  false) {
                $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/arb_act_reshenie.xml';
            } else {
                return null;
            }
        } else {
            return null;
        }
        
        $error = NULL;
        $sbr_num = $this->sbr->getContractStageNum( null, null, $this->num + 1 );
        
        $this->sbr->getFrlReqvs();
        $this->sbr->getEmpReqvs();
        $this->sbr->setCheckEmpReqvs($this->id);
        
        $rep_num = $this->sbr->regArbReportNum();
        
        
        $efio = sbr_meta::getFioFromReqvs($this->sbr->emp_reqvs);
        $ffio = sbr_meta::getFioFromReqvs($this->sbr->frl_reqvs);
        
        if(!$efio)
            $error['emp'] = '��� ������������ ���� �� �������� ������ ���������<br/> ���������� ��������� ��������� �� �������� "�������"';
        if($error)
            return false;
        
        $dvals = array('P' => pskb::$exrates_map[$this->sbr->data['ps_frl']]);
        foreach($this->sbr->scheme['taxes'][sbr::FRL] as $tax) {
            $tax_frl += sbr_meta::calcAnyTax($tax['tax_id'], $tax['scheme_id'], $this->cost, $dvals);
        }
        
        $sum_emp     = $this->cost * ($eper / 100 );
        $sum_frl     = $this->getPayoutSum(sbr::FRL);
        $work_cost   = $this->cost - $tax_frl;
        $work_time   = intval($this->int_work_time) . ending(intval($this->int_work_time), ' ����', ' ���', ' ����');
        $work_type   = count($this->sbr->stages) == 1 ? '��������� ������' : '������������� ��������� ������';
        
        $replace = array(
            '$sbr_num'      => $sbr_num,
            '$date_sbr'     => date("d.m.Y", strtotime($this->getFirstTime())),
            '$efio'         => $efio,
            '$ffio'         => $ffio,
            '$sum_frl'      => num2strEx($sum_frl, '������ ���������� ���������'),
            '$sum_emp'      => num2strEx($sum_emp, '������ ���������� ���������'),
            '$tz_descr'     => $this->descr,
            '$work_time'    => $work_time,
            '$work_type'    => $work_type,
            '$work_cost'    => num2strEx($work_cost, '������ ���������� ���������'),
            '$is_arb_emp'   => $is_emp_arb,
            '$is_arb_frl'   => $is_frl_arb,
            '$is_arb_vaan'  => $is_vaan_arb,
            '$is_not_arb_vaan' => !$is_vaan_arb,
            '$user_arb'     => $user_arb,
            '$result_arb'   => $result_arb
        );
        
        $pdf = sbr::xml2pdf($template,$replace);
        
        if(!($file = $this->sbr->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ���� �� �������� ������";
        return $file;
    }
    
    /**
     * ���������� ����� ��������� ����
     * - ����� �������� ������� ������� � 100%  ������� �����������
     * - ����� ������� �������� � ���������� � 100% ������� �����������
     * 
     * @param type $error   ������
     * @param type $rep_num
     * @return null|boolean 
     */
    public function generateArbReportFrl(&$error, &$rep_num) {
        require_once (dirname(__FILE__).'/num_to_word.php');
        
        if(!$this->getArbitrage(false, false))
            return NULL;

        if(!($act = $this->getActSums($this->sbr->frl_id)))
            return false;
        
        $eper = (1-$this->arbitrage['frl_percent'])*100;
        $fper = 100-$eper;
        
        $result = html_entity_decode($this->arbitrage['result'], ENT_QUOTES, 'cp1251');
        $init   = html_entity_decode($this->arbitrage['init'], ENT_QUOTES, 'cp1251');
        $reason  = html_entity_decode($this->arbitrage['reason'], ENT_QUOTES, 'cp1251');
        
        $is_frl_arb  = ( strpos($reason, '�������� �. 8.5.') !== false );
        $is_emp_arb  = ( strpos($reason, '�������� �. 8.3.') !== false );
        $is_vaan_arb = ( strpos($init, '��� ���� ���������� � ��������') !== false );
        
        if(strpos($init, '����������� ��������� � ��������') !== false ) {
            $user_arb = '�����������';
        } elseif(strpos($init, '�������� ��������� � ��������') !== false ) {
            $user_arb = '��������';
        } elseif($is_vaan_arb) {
            $user_arb = '��� "����"';
        }
        
        if($fper == 100) {
            if(strpos($result, '���������� ������ 100% ������� �����������') !==  false || 
               strpos($result, '���������� ������ 100% ������� �����������') !==  false ) {
                $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/arb_frl_soglashenie.xml';
            } elseif(strpos($result, '������� ��������� 100% ������� �����������') !==  false) {
                $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/arb_frl_reshenie.xml';
            } elseif(strpos($result, '������� ���������') !==  false) { // �� ���� �� ����� ���� �� ��� ������� 100% ����������� ����� �������� ����� 7
                $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/arb_act_reshenie.xml';
            } else {
                return null;
            }
        } else if($fper >= 0) {
            if(strpos($result, '���������� ������') !==  false) {
                $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/arb_act_soglashenie.xml';
            } elseif(strpos($result, '������� ���������') !==  false) {
                $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/arb_act_reshenie.xml';
            } else {
                return null;
            }
        } else {
            return null;
        }
        
        $error = NULL;
        $sbr_num = $this->sbr->getContractStageNum( null, null, $this->num + 1 );
        
        $this->sbr->getFrlReqvs();
        $this->sbr->getEmpReqvs();
        $this->sbr->setCheckEmpReqvs($this->id);
        
        $rep_num = $this->sbr->regArbReportNum();
        
        
        
        $efio = sbr_meta::getFioFromReqvs($this->sbr->emp_reqvs);
        $ffio = sbr_meta::getFioFromReqvs($this->sbr->frl_reqvs);
        
        if(!$efio)
            $error['emp'] = '��� ������������ ���� �� �������� ������ ���������<br/> ���������� ��������� ��������� �� �������� "�������"';
        if($error)
            return false;
        
        $dvals = array('P' => pskb::$exrates_map[$this->sbr->data['ps_frl']]);
        foreach($this->sbr->scheme['taxes'][sbr::FRL] as $tax) {
            $tax_frl += sbr_meta::calcAnyTax($tax['tax_id'], $tax['scheme_id'], $this->cost, $dvals);
        }
        
        $sum_emp     = $this->getPayoutSum(sbr::EMP);
        $sum_frl     = $this->getPayoutSum(sbr::FRL);
        $work_cost   = ( $this->cost - $tax_frl );
        $work_time   = intval($this->int_work_time) . ending(intval($this->int_work_time), ' ����', ' ���', ' ����');
        $work_type   = count($this->sbr->stages) == 1 ? '��������� ������' : '������������� ��������� ������';
        
        $replace = array(
            '$sbr_num'      => $sbr_num,
            '$date_sbr'     => date("d.m.Y", strtotime($this->getFirstTime())),
            '$efio'         => $efio,
            '$ffio'         => $ffio,
            '$sum_frl'      => num2strEx($sum_frl, '������ ���������� ���������'),
            '$sum_emp'      => num2strEx($sum_emp, '������ ���������� ���������'),
            '$tz_descr'     => $this->descr,
            '$work_time'    => $work_time,
            '$work_type'    => $work_type,
            '$work_cost'    => num2strEx($work_cost, '������ ���������� ���������'),
            '$is_arb_emp'   => $is_emp_arb,
            '$is_arb_frl'   => $is_frl_arb,
            '$is_arb_vaan'  => $is_vaan_arb,
            '$is_not_arb_vaan' => !$is_vaan_arb,
            '$user_arb'     => $user_arb
        );
        
        $pdf = sbr::xml2pdf($template,$replace);
        
        if(!($file = $this->sbr->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ���� �� �������� ������";
        return $file;
    }
    
    /**
     * ���� ������ ������ ������� ���������� ��� �� ������ �����
     * ��� � ��� ���� ����������� �� ���� ������� ������ ���� � ������ 
     */
    public function generateCompletedAct(&$error) {
        require_once (dirname(__FILE__).'/num_to_word.php');
        
        $error = NULL;
        if(!($payout = $this->getPayouts($this->sbr->frl_id)))
            return false;
        if(!($act = $this->getActSums($this->sbr->frl_id)))
            return false;
        $payout = $payout[$this->sbr->frl_id];
        if(!$payout)
            return false;
        
        if(count($this->sbr->stages) == 1) {
            $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/act_completed_sbr.xml';
        } else {
            $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/act_completed_stage.xml';
        }
        
        $pskb = new pskb($this->sbr);
        $lc = $pskb->getLC();
        
        $sbr_num = $this->sbr->getContractStageNum( null, null, $this->num + 1 );
        $this->sbr->getFrlReqvs();
        $this->sbr->getEmpReqvs();
        $this->sbr->setCheckEmpReqvs($this->id);
        
        $efio = $lc['nameCust'];
        $ffio = $lc['namePerf'];

        if(!$efio)
            $error['emp'] = '��� ������������ ���� �� �������� ������ ���������<br/> ���������� ��������� ��������� �� �������� "�������"';
        if($error)
            return false;
        
        if(pskb::$form_map[$lc['tagCust']] == sbr::FT_PHYS) {
            $this->sbr->emp_reqvs[sbr::FT_JURI]['full_name'] = $efio;
            $this->sbr->emp_reqvs[sbr::FT_PHYS]['fio'] = $efio;
            $side_b_emp = sbr_meta::getReqvsStr($this->sbr->emp_reqvs, $bossname_emp);
        } else {
            $side_b_emp  = $this->sbr->getUserReqvAgnt($lc, 'emp');
        }
        if(pskb::$form_map[$lc['tagPerf']] == sbr::FT_PHYS) {
            $this->sbr->frl_reqvs[sbr::FT_JURI]['full_name'] = $ffio;
            $this->sbr->frl_reqvs[sbr::FT_PHYS]['fio'] = $ffio;
            $side_b_frl = sbr_meta::getReqvsStr($this->sbr->frl_reqvs, $bossname_frl);
        } else {
            $side_b_frl  = $this->sbr->getUserReqvAgnt($lc, 'frl');
        }
        
        //foreach($this->sbr->stages as $stage) {
        foreach($this->sbr->scheme['taxes'][sbr::FRL] as $tax) {
            if($tax['tax_code'] == 'TAX_FL') {
                $tax_total += $this->calcTax($tax, $dvals, $outsys);
            }
        }
        //}
        
        $sum_frl_tax = $tax_total; // �������� ������������� � ����������
        $sum_frl     = $this->getPayoutSum(sbr::FRL);
        
        $replace = array(
            '$sbr_num'      => $sbr_num,
            '$date_act'     => $this->redate_act ? $this->redate_act : date("d.m.Y"),
            '$date_sbr'     => date("d.m.Y", strtotime($this->getFirstTime())),
            '$efio'         => $efio,
            '$ffio'         => $ffio,
            '$side_b_frl'   => $side_b_frl,
            '$side_b_emp'   => $side_b_emp,
            '$bossname_frl' => $bossname_frl,
            '$bossname_emp' => $bossname_emp,
            '$sum_frl'      => num2strEx($sum_frl, '������ ���������� ���������'),
            '$sum_frl_tax'  => num2strEx($sum_frl_tax, '������ ���������� ���������'),
        );
        
        $pdf = sbr::xml2pdf($template,$replace);

        if(!($file = $this->sbr->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ���� �� �������� ������";
        return $file;
        
    }
    
     /**
     * ���������� ��� ����������� �� �������� ������ � PDF ����� ������ ������ �������.
     * 
     * @param array $error   ������ ������ � ��������.
     * @param array $act_num   ���������� ����� ���� (������ sbr_docs.id).
     * @return CFile   ����������� ��������.
     */
    function generateFrlAct(&$error, &$act_num) {
        require_once(dirname(__FILE__).'/fpdf/fpdf.php');
        define('FPDF_FONTPATH',(dirname(__FILE__).'/fpdf/font/'));
        require_once (dirname(__FILE__).'/num_to_word.php');
        
        $error = NULL;
        if(!($payout = $this->getPayouts($this->sbr->frl_id)))
            return false;
        if(!($act = $this->getActSums($this->sbr->frl_id)))
            return false;
        $payout = $payout[$this->sbr->frl_id];
        if(!$payout)
            return false;
        $cnum = $this->sbr->getContractNum();
        $this->sbr->getFrlReqvs(true);
        $this->sbr->getEmpReqvs(true);
        $this->sbr->setCheckEmpReqvs($this->id);
        if(!$this->sbr->checkUserReqvs($this->sbr->frl_reqvs)) {
            if($this->login != $this->sbr->emp_login) {
                $flogin = $this->sbr->frl_login ? $this->sbr->frl_login : $this->sbr->login;
                $error['frl'] = '��� ������������ ���� �� �������� ������ �����������<br/> ���������� ��������� ��������� �� �������� <a href="/users/'.$flogin.'/setup/finance/" class="blue" target="_blank">�������</a>';
            }
            else
                $error['frl'] = '��� ������������ ���� �� �������� ������ �����������<br/> ���������� ��������� ��������� �� �������� "�������"';
        }
        $efio = sbr_meta::getFioFromReqvs($this->sbr->emp_reqvs);
        $ffio = sbr_meta::getFioFromReqvs($this->sbr->frl_reqvs);

        if ($this->sbr->emp_reqvs['form_type']==sbr::FT_JURI && ($this->sbr->cost_sys == exrates::WMR || $this->sbr->cost_sys == exrates::YM)) {
            $efio = "�{$efio} � ���������� ����";
        }
        
        if(!$efio)
            $error['emp'] = '��� ������������ ���� �� �������� ������ ���������<br/> ���������� ��������� ��������� �� �������� "�������"';
        if($error)
            return false;

        $act_num = $this->sbr->regActNum(); // � 07.2012 �� ������������, �� ����� �������� �� ������.

        $act_ssum = sbr_meta::view_cost($act['act_sum'], NULL, false, ',', ' ');
        $act_snds = sbr_meta::view_cost(18*$act['act_sum']/118, NULL, false, ',', ' ');
        $act_np = sbr_meta::view_cost($act['act_lnp'], NULL, true, ',', ' ');

        $payout_method = sbr_meta::getPayoutMethodStr($this->sbr->frl_reqvs, $payout['credit_sys'], '������ ������: ');

        $body_nopretense_text = "��� ����� � {$ffio} ���� ������������� �� �������� {$cnum} ���������, ���������� � ���� ��������� ���� � ����� �� �����.";

        $rq = $this->sbr->frl_reqvs[$this->sbr->frl_reqvs['form_type']];
        if($this->sbr->frl_reqvs['form_type'] == sbr::FT_PHYS) {
            $side_b = sbr_meta::getReqvsStr($this->sbr->frl_reqvs, $bossname);
            if($payout['credit_sys'] && $payout['credit_sys'] != exrates::FM) {
                $side_b .= "\r\n��������� ��������:\r\n" . sbr_meta::getPayoutReqvsStr($this->sbr->frl_reqvs, $payout['credit_sys']);
            }
        }
        else {
            $side_b = sbr_meta::getReqvsStr($this->sbr->frl_reqvs, $bossname);
        }

        $ntax_s = '';
        $frl_nds = '';
        if($this->sbr->frl_reqvs['form_type']==sbr::FT_PHYS) {
            if($act['act_lndfl']!=0) {
                $ndfl_tax_id = ($this->sbr->frl_reqvs['rez_type']==sbr::RT_RU ? $this->sbr->getTaxByCode('TAX_NDFL') : $this->sbr->getTaxByCode('TAX_NDFL_NR'));
                $ntax_per = (int)($this->sbr->scheme['taxes'][sbr::FRL][$ndfl_tax_id]['percent'] * 100);
                $ntax_s = "������� ���� (� ������������ ��������� ������� � ������ ��) � {$ntax_per} ��������� � �����������: ".num2strEx($act['act_lndfl']).";";
            }
        }
                                                
        $frl_sum = $this->getPayoutSum(sbr::FRL, exrates::BANK);// - $act['act_lintr'];
        $ag_sum = floatval($frl_sum) + floatval($act['act_lndfl']) + floatval($act['act_lnds']);

        if($this->arbitrage === false)
            $this->getArbitrage(false, false);
        if($this->arbitrage['resolved']) {
            $arb_per_s = ' (' . ($this->arbitrage['frl_percent'] * 100) . '%)';
        }

        if($this->sbr->frl_reqvs['rez_type']!=sbr::RT_RU){
            if($this->sbr->frl_reqvs['form_type']==sbr::FT_JURI){
                $nds_text =  floatval($act['act_lnds']) ? '�������� ��� (� ������������ ��������� ������� � ������ ��) � 18 ��������� � �����������: '.num2strEx(floatval($act['act_lnds'])).'.' : '';
            }
        }else{
            $x_nds = $ag_sum/118*18;
            if($this->sbr->frl_reqvs['form_type']==sbr::FT_JURI){
                $frl_nds = ', � ��� ����� ��� � 18%: '.num2strEx(18*$frl_sum/118);
            }
        }
        $act_ssum_nds = ', � ��� ����� ��� 18 % � '.num2strEx($act['act_sum'] * 18  / 118);
        
        $pr['$adr_act'] = "129223, ������, �/� 33";
        if($this->sbr->frl_reqvs['rez_type'] == sbr::RT_RU) {
            $pr['$adr_act'] .= ";\r\n"; 
            $pr['$adr_act'] .= "190031, �����-���������, ������ ��., �.13 / 52, �/� 427;\r\n";
            $pr['$adr_act'] .= "420032, ������, �/� 624;\r\n";
            $pr['$adr_act'] .= "454014, ���������-14, �/� 2710.\r\n";
        } else {
            $pr['$adr_act'] .= ".\r\n"; 
        }
        
        $repl = array(
            '$yd_wm' => '',
            '$act_num' => $act_num,
            '$cnum' => $cnum,
            '$ssnum' => $this->getOuterNum4Docs(),
            '$employer_title' => $efio,
            '$freelancer_title' => $ffio,
            '$payout_name' => $payout_method,
            '$act_sum' => num2strEx($frl_sum + $act['act_lndfl'] + $act['act_lnds'] + $act['act_sum']),
            '$nds_text' => $nds_text,
            '$act_ssum' => num2strEx($act['act_sum']),
            '$act_ssum_nds' => $act_ssum_nds,
            '$act_snds' => num2strEx(18*($act['act_sum'])/118),
            '$ndfl_text' => $ntax_s,
            '$frl_sum' => num2strEx($frl_sum),
            '$frl_nds' => $frl_nds,
            '$sbr_begin' => date('d.m.Y',strtotime($this->getFirstTime())),
            '$sbr_end' => date('d.m.Y',strtotime($this->closed_time)),
//            '$now_date' => date('d.m.Y', strtotime($this->closed_time)),
            '$now_date' => date('d.m.Y'),
            '$side_b' => $side_b,
            '$bossname' => $bossname,
            '$add2_text' => $add2_text,
            '$adr_act' => $pr['$adr_act'],
            '$emp_init_arb' => ($this->arbitrage['user_id'] == $this->sbr->emp_id),
            '$arbitrage' => (!!$this->arbitrage),
        );

        $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/act_frl.xml';
        $pdf = sbr::xml2pdf($template,$repl);

        if(!($file = $this->sbr->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ���� �� �������� ������";
        return $file;
    }
    
    /**
     * ���������� ��� ������������ �� �������� ������ � PDF ����� ������ ������ �������.
     * 
     * @param array $error   ������ ������ � ��������.
     * @param integer $rep_num   ���������� ����� ������ ������.
     * @return CFile   ����������� ��������.
     */
    function generateEmpAgentReport(&$error, &$rep_num) {
        $rep_num = -1;
        return $this->generateEmpAct($error, $act_num, $rep_num);
    }
    
    /**
     * ���������� ��� ������������ �� �������� ������ ��� ����� ������ � PDF ����� ������ ������ �������.
     * 
     * @param array $error   ������ ������ � ��������.
     * @param integer $act_num   ���������� ����� ���� @deprecated.
     * @param integer $agent_rep_num   ���������� ����� ������ ������. ���� ������ -1, �� ����� ����������� ����� ������ � � ��� ���������� ��������� �����.
     * @return CFile   ����������� ��������.
     */
    function generateEmpAct(&$error, &$act_num, &$agent_rep_num) {
        require_once(dirname(__FILE__).'/fpdf/fpdf.php');
        define('FPDF_FONTPATH',(dirname(__FILE__).'/fpdf/font/'));
        require_once (dirname(__FILE__).'/num_to_word.php');

        $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/act_emp.xml';

        if($agent_rep_num === -1) {
            $agent_rep_num = $this->sbr->regAgentRepNum();
            $template = $_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/agent_rep_emp.xml';
        }
        if(!$agent_rep_num) {
            return false;
        }
        
        $error = NULL;
        if(!($act_frl = $this->getActSums($this->sbr->frl_id)))
            return false;
        if(!($act_emp = $this->getActSums($this->sbr->emp_id)))
            return false;
            
        $cnum = $this->sbr->getContractNum();
        $this->sbr->getFrlReqvs(true);
        $this->sbr->getEmpReqvs(true);
        $this->sbr->setCheckEmpReqvs($this->id);
        if(!$this->sbr->checkUserReqvs($this->sbr->emp_reqvs)) {
            if($this->login != $this->sbr->frl_login) {
                $flogin = $this->sbr->emp_login ? $this->sbr->emp_login : $this->sbr->login;
                $error['frl'] = '��� ������������ ���� �� �������� ������ ���������<br/> ���������� ��������� ��������� �� �������� <a href="/users/'.$flogin.'/setup/finance/" class="blue" target="_blank">�������</a>';
            }
            else
                $error['frl'] = '��� ������������ ���� �� �������� ������ ���������<br/> ���������� ��������� ��������� �� �������� "�������"';
        }
        $efio = sbr_meta::getFioFromReqvs($this->sbr->emp_reqvs);
        $ffio = sbr_meta::getFioFromReqvs($this->sbr->frl_reqvs);
        if ($ffio) {
            $ffio = "�{$ffio}�"; // ��� -- #0018645 (Akt_rabotodatel_12072012.docx)
        } else {
            $ffio = '�����������';
        }
        // ��� �������, �� ����� ���� ��� ������ ������. ���� �� ��� ����, �� ������������ �� �� � ���, �� � ����� ����� ���� ��������� "���������� ����".
        if ($this->sbr->emp_reqvs['form_type']==sbr::FT_JURI && ($this->sbr->cost_sys == exrates::WMR || $this->sbr->cost_sys == exrates::YM)) {
            $efio = "�{$efio} � ���������� ����";
        } else {
            $efio = "�{$efio}�"; // ��� ��� �������� ����������� � ������� -- #0018645 (Akt_rabotodatel_12072012.docx)
        }
        
        if(!$efio)
            $error['emp'] = '��� ������������ ���� �� �������� ������ ���������<br/> ���������� ��������� ��������� �� �������� "�������"';
        if($error)
            return false;

        $act_num = $this->sbr->regActNum(); // � 07.2012 �� ������������, �� ����� �������� �� ������.

        $act_ssum = $act_frl['act_sum'];
        $act_snds = 18*$act_frl['act_sum']/118;

        $frl_sum = $this->getPayoutSum(sbr::FRL, exrates::BANK) + $act_frl['act_lndfl'] + $act_frl['act_lnds'] + $act_frl['act_sum'];

        $rq = $this->sbr->emp_reqvs[$this->sbr->emp_reqvs['form_type']];
        $side_b = sbr_meta::getReqvsStr($this->sbr->emp_reqvs, $bossname);
        
        if((int)$frl_sum == 0 && $this->sbr->emp_reqvs['form_type']==sbr::FT_PHYS) {
            $side_b .= "��������� ��������:\r\n"
                    . sbr_meta::getPayoutReqvsStr($this->sbr->emp_reqvs, $this->sbr->cost_sys);
        }
        
        $ntax_s = '';
        $frl_nds = '';
        
        if($this->sbr->frl_reqvs['form_type']==sbr::FT_PHYS) {
            if($act_frl['act_lndfl']!=0) {
                $ndfl_tax_id = ($this->sbr->frl_reqvs['rez_type']==sbr::RT_RU ? $this->sbr->getTaxByCode('TAX_NDFL') : $this->sbr->getTaxByCode('TAX_NDFL_NR') );
                $ntax_per = (int)($this->sbr->scheme['taxes'][sbr::FRL][$ndfl_tax_id]['percent'] * 100);
                $ndfl_text = "���������� {$ntax_per}% ���� � ".num2strEx($frl_sum)." � ������� ".num2strEx($act_frl['act_lndfl']) . ", \r\n";
                $act_ndfl_note = " �� ������� {$ntax_per}% ���� � ��������� ����� ��� �����, �� ���� " . num2strEx($frl_sum - $act_frl['act_lndfl'] - $act_ssum) . ';';
            } else {
                $act_ndfl_note = " �� ������� ��������� ����� ��� �����, �� ���� " . num2strEx($frl_sum - $act_ssum) . ';';
            }
        } else {
            $act_nds_note = " � ��� ����� ��� 18% ";
            $act_ndfl_note = " �� ������� ��������� ����� ��� �����, �� ���� ".num2strEx($act_ssum).", � ��� ����� ��� 18%;";
        }
        
        $vaan_text = "- �������� ��������� ����� ��� ����� � ������������ � �. 10.3 �������� � ".num2strEx($act_ssum).", � ��� ����� ��� 18% � ".num2strEx($act_snds);
        
        if($this->sbr->frl_reqvs['rez_type']!=sbr::RT_RU) {
            if($this->sbr->frl_reqvs['form_type']==sbr::FT_JURI){
                $add2_text =  floatval($act_frl['act_lnds']) ? '������� ��� (� ������������ ��������� ������� � ������ ��) � 18 ��������� � �����������: '.num2strEx(floatval($act_frl['act_lnds'])).'.' : '';
            }
        } else {
            if($this->sbr->frl_reqvs['form_type']==sbr::FT_JURI){
                $frl_nds = ', � ��� ����� ��� � 18%: '.num2strEx(18*$frl_sum/118);
            }
        }
        

        if($this->arbitrage === false)
            $this->getArbitrage(false, false);

        if(!$this->arbitrage) {
            $reason = "�������� �. 6.1.1. �������� �������� ������� ������ � ���������� ���������� ������ ������������, � ����� � ����";
        } else {
            $arb_report_num = $this->tmp_doc_arb['num'];
            $arb_report_date = date('d.m.Y', strtotime($this->tmp_doc_arb['publ_time']));
            $reason = str_replace(array('$arb_report_num', '$arb_report_date'), array($arb_report_num, $arb_report_date), $this->arbitrage['reason']);
            $emp_sum = $this->getPayoutSum(sbr::EMP, exrates::BANK);
            $reason = html_entity_decode($reason, ENT_QUOTES, 'cp1251');
            if(!isset($this->tmp_doc_arb)) {
                $this->tmp_doc_arb = $this->sbr->getLastPublishedDocByType(sbr::DOCS_TYPE_ARB_REP, $this->id);
            }
        }

        if(strpos($reason, '6.1.3') !== false) {
            $reason_add = '�������� �. 7.1.3. �������� �������� � ����������� ��������� ���������� �� ������������� �������� ��������� ������ ���������, � ����� � ����';
        }

        $agent_nds_reward = $this->sbr->getTotalTax($this->sbr->scheme['taxes'][sbr::EMP][sbr::TAX_EMP_COM], 18/118, array('A'=>'NULL'));
        
        $reserved_sum = $this->sbr->getReserveSum(true);
        $pr['$adr_act'] = "129223, ������, �/� 33";
        if($this->sbr->emp_reqvs['rez_type'] == sbr::RT_RU) {
            $pr['$adr_act'] .= ";\r\n"; 
            $pr['$adr_act'] .= "190031, �����-���������, ������ ��., �.13 / 52, �/� 427;\r\n";
            $pr['$adr_act'] .= "420032, ������, �/� 624;\r\n";
            $pr['$adr_act'] .= "454014, ���������-14, �/� 2710.\r\n";
        } else {
            $pr['$adr_act'] .= ".\r\n"; 
        }
        if($this->sbr->stages_cnt > 1) {
            $sum_stage = num2strEx($this->sbr->stages[$this->num]->data['cost']);
            $stage_num_str  = '�� ' . $this->getNumStage4Docs() . ' �����';
            $stage_add_info = " (�������� " . numStringName($this->sbr->stages_cnt) . " ������) �� ��� {$sum_stage} {$stage_num_str}";
        } else {
            $stage_add_info = "";
        }
        
        $repl = array(
            '$yd_wm' => '',
            '$vaan_text' => $vaan_text,
            '$act_num' => $act_num,
            '$cnum' => $cnum,
            '$ssnum' => $this->getOuterNum4Docs(),
            '$employer_title' => $efio,
            '$freelancer_title' => $ffio,
            '$act_sum' => num2strEx($this->cost),
            '$act_nds' => num2strEx($this->cost * 18 / 118),
            '$act_nds_note' => $act_nds_note,
            '$emp_sum' => num2strEx($emp_sum),
            '$emp_sum_ex' => !!$emp_sum,
            '$emp_reward' => num2strEx($act_emp['act_sum']),
            '$emp_nds_reward' => num2strEx(18*$act_emp['act_sum']/118),
            '$frl_reward' => num2strEx($act_ssum),
            '$frl_nds_reward' => num2strEx($act_snds),
            '$ndfl_text' => $ndfl_text,
            '$act_ndfl_note' => $act_ndfl_note,
            '$add2_text' => $add2_text,
            '$frl_sum' => num2strEx($frl_sum),
            '$frl_nds' => $frl_nds,
            '$frl_sum_ex' => !!$frl_sum,
            '$sbr_begin' => date('d.m.Y',strtotime($this->getFirstTime())),
            '$sbr_end' => date('d.m.Y',strtotime($this->closed_time)),
            '$now_date' => date('d.m.Y'),
            '$side_b' => $side_b,
            '$bossname' => $bossname,
            '$reserved_sum' => num2strEx($reserved_sum),
            '$reserved_nds_sum' => num2strEx(18*$reserved_sum/118),
            '$agent_nds_reward' => num2strEx($agent_nds_reward),
            '$arb_report_num' => $this->tmp_doc_arb['num'],
            '$arb_report_date' => date('d.m.Y', strtotime($this->tmp_doc_arb['publ_time'])),
            '$adr_act' => $pr['$adr_act'],
            '$reason' => $reason,
            '$reason_add' => $reason_add,
            '$stage_num_str'  => $stage_num_str,
            '$stages_additional_information' => $stage_add_info,
            '$agent_rep_num' => $agent_rep_num,
            '$is_emp100' => ($frl_sum == 0)
        );
        $pdf = sbr::xml2pdf($template,$repl);

        if(!($file = $this->sbr->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ���� �� �������� ������";
        return $file;
    }
    
    /**
     * ���������� ���������� � ���� ����������� ����� @see self::generateFrlActPdrd();
     * 
     * @param array $error   ������ ������ � ��������.
     * @return CFile   ����������� ��������.
     */
    function generateTzPdrd(& $error, $init_date = false) {
        require_once (dirname(__FILE__).'/fpdf/fpdf.php');
        define ('FPDF_FONTPATH',(dirname(__FILE__).'/fpdf/font/'));
        require_once (dirname(__FILE__).'/num_to_word.php');
        $error = NULL;
        
        $cnum = $this->sbr->getContractNum();
        //$this->sbr->getFrlReqvs();
        $this->sbr->getUserReqvHistoryData($this->id, 'frl', true);
        if(!$this->sbr->checkUserReqvs($this->sbr->frl_reqvs)) {
            $error['frl'] = '��� ������������ ���� �� �������� ������ �����������<br/> ���������� ��������� ��������� �� �������� "�������"';
        }
        
        $stage_num = $cnum . "-" . ( $this->num + 1 ) ;
        $ffio = sbr_meta::getFioFromReqvs($this->sbr->frl_reqvs);
        $bossname = '';
        $is_juri   = $this->sbr->frl_reqvs['form_type'] == sbr::FT_JURI;
        if($is_juri) {
            $reqvs_str = sbr_meta::getReqvsStr($this->sbr->frl_reqvs, $bossname);
        }
        $adr_act = "129223, ������, �/� 33";
        if($this->sbr->frl_reqvs['rez_type'] == sbr::RT_RU) {
            $adr_act .= ";\r\n"; 
            $adr_act .= "190031, �����-���������, ������ ��., �.13 / 52, �/� 427;\r\n";
            $adr_act .= "420032, ������, �/� 624;\r\n";
            $adr_act .= "454014, ���������-14, �/� 2710.\r\n";
        } else {
            $adr_act .= ".\r\n"; 
        }
        
        $replace = array(
            '$sbr_num'      => $cnum,
            '$stage_num'    => $stage_num,
            '$date_act'     => $init_date ? $init_date : date('d.m.Y �.'),
            '$name_sbr'     => "�{$this->sbr->name}�",
            '$name_stage'   => $this->name,
            '$tz_descr'     => $this->descr,
            '$adr_act'      => $adr_act,
            '$fio'          => $ffio,
            '$bossname'     => $bossname,
            '$is_juri'      => $is_juri
        );
            
        $pdf = sbr::xml2pdf($_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/WorkTz.xml',$replace);
        if(!($file = $this->sbr->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ���� �� �������� ������";
        return $file;
    }
    
   /**
     * ���������� ��� �����������-���. ���� (������) � PDF ����� ������ ������ �������.
     * 
     * @param array $error   ������ ������ � ��������.
     * @return CFile   ����������� ��������.
     */
    function generateFrlActPdrd(&$error, $init_date = false) {
    
        require_once (dirname(__FILE__).'/fpdf/fpdf.php');
        define ('FPDF_FONTPATH',(dirname(__FILE__).'/fpdf/font/'));
        require_once (dirname(__FILE__).'/num_to_word.php');
        $error = NULL;

        if(!($payout = $this->getPayouts($this->sbr->frl_id)))
            return false;
        if(!($act = $this->getActSums($this->sbr->frl_id)))
            return false;
        $payout = $payout[$this->sbr->frl_id];
        if(!$payout)
            return false;

        $cnum = $this->sbr->getContractNum();
        //$this->sbr->getFrlReqvs();
        $this->sbr->getUserReqvHistoryData($this->id, 'frl', true); // ����� ������� ������
        if(!$this->sbr->checkUserReqvs($this->sbr->frl_reqvs)) {
            $error['frl'] = '��� ������������ ���� �� �������� ������ �����������<br/> ���������� ��������� ��������� �� �������� "�������"';
        }
    
        $acc = new account();
        $acc->GetInfo($this->sbr->frl_id);
        $ffio = sbr_meta::getFioFromReqvs($this->sbr->frl_reqvs);
        $rq = $this->sbr->frl_reqvs[$this->sbr->frl_reqvs['form_type']];
    
        $act_ssum = num2strEx($act['act_sum']);
        $act_ndfl = num2strEx($act['act_lndfl']);
        $act_np = num2strEx($act['act_lnp']);
        $frl_sum = $this->getPayoutSum(sbr::FRL, exrates::BANK);
        $payout_sum = num2strEx($frl_sum);
        $payout_nds_sum = num2strEx(18*$frl_sum/118);
        $from_date_act = date('�d� '.$GLOBALS['MONTHA'][date('n',strtotime($this->getFirstTime()))].' Y �.', strtotime($this->getFirstTime()));
        $to_date_act = $init_date ? date('�d� '.$GLOBALS['MONTHA'][date('n', strtotime($init_date))].' Y �.', strtotime($init_date)) : date('�d� '.$GLOBALS['MONTHA'][date('n')].' Y �.'); // ������� �� ������ #0017135
        
        if($this->sbr->frl_reqvs['form_type'] == sbr::FT_PHYS) {
            if($this->sbr->frl_reqvs['rez_type']==sbr::RT_RU) {
                $pssinn = ", ��������� ������������� ������������� ����������� ����������� � {$rq['pss']}";
                if($rq['inn']) {
                    $pssinn .= ", ��� {$rq['inn']}";
                }
            }
            $rq['birthday'] = date('d.m.Y', strtotime($rq['birthday']));
            $rq['idcard_from'] = date('d.m.Y', strtotime($rq['idcard_from']));
            $frl_details = "{$ffio}, {$rq['birthday']} ���� ��������, ������������������ �� ������: {$rq['address_reg']}"
                         . ", {$rq['idcard_name']}: � {$rq['idcard']}, ����� {$rq['idcard_from']} {$rq['idcard_by']}{$pssinn}";
            if($act['act_lndfl']!=0) {
                $ndfl_tax_id = ($this->sbr->frl_reqvs['rez_type']==sbr::RT_RU ? $this->sbr->getTaxByCode('TAX_NDFL') : $this->sbr->getTaxByCode('TAX_NDFL_NR') );
                $ntax_per = (int)($this->sbr->scheme['taxes'][sbr::FRL][$ndfl_tax_id]['percent'] * 100);
                $ntax_s = ", ���������� ���� {$ntax_per}% � {$act_ndfl}";
                $reward2 = " � ������������ {$payout_sum}{$ntax_s}.";
            }
        } else {
            $frl_details = $ffio;
            $nds_s = ', � ��� ����� ��� 18%';
            if($this->sbr->frl_reqvs['rez_type']==sbr::RT_RU) {
                $ntax_s = ", � ��� ����� ��� 18% � {$payout_nds_sum}";
                $reward2 = " � ������������ {$payout_sum}{$ntax_s}.";
            } else if($act['act_lnds'] != 0) {
                $act_lnds = num2strEx($act['act_lnds']);
                $reward2 = "\r\n�  ������������ ��������� ������� � ������ �� � ��� 18 ��������� � {$act_lnds}.\r\n\r\n� ������������ ���������� {$payout_sum}{$ntax_s}.";
            }
        }

        if($payout['credit_sys'] != exrates::FM) {
            $reqvs_str = "\r\n".sbr_meta::getPayoutReqvsStr($this->sbr->frl_reqvs, $payout['credit_sys']);
            $payout_method = ', ������ ������ � '.$GLOBALS['EXRATE_CODES'][$payout['credit_sys']][4];
        } else {
            $reqvs_str = ' �';
        }
        
        $reward_sum = num2strEx($frl_sum + $act['act_lndfl'] + $act['act_lnds']);
        $reward = "{$from_date_act} �� {$to_date_act} ���������� ����� {$reward_sum}{$nds_s}{$payout_method}.{$reward2}";

        $side_b = sbr_meta::getReqvsStr($this->sbr->frl_reqvs, $bossname);
        $pr['$adr_act'] = "129223, ������, �/� 33";
        if($this->sbr->frl_reqvs['rez_type'] == sbr::RT_RU) {
            $pr['$adr_act'] .= ";\r\n"; 
            $pr['$adr_act'] .= "190031, �����-���������, ������ ��., �.13 / 52, �/� 427;\r\n";
            $pr['$adr_act'] .= "420032, ������, �/� 624;\r\n";
            $pr['$adr_act'] .= "454014, ���������-14, �/� 2710.\r\n";
        } else {
            $pr['$adr_act'] .= ".\r\n"; 
        }
        $replace = array(
        '$sbr_num' => $this->sbr->getContractNum(), 
        '$ssnum' => $this->getOuterNum4Docs(),
        '$from_date_act' => $from_date_act,
        '$to_date_act' => $to_date_act,
        '$maker_info' => $frl_details,
        '$from_date_make' => $from_date_act,
        '$job_name' => "�{$this->name}�",
        '$reward_text' => $reward,
        '$bank_details' => $reqvs_str,
        '$make_date' => $init_date ? $init_date : date('d.m.Y'),
        '$side_b' => $side_b,
        '$bossname' => $bossname,
        '$adr_act' => $pr['$adr_act']
        );

        $pdf = sbr::xml2pdf($_SERVER['DOCUMENT_ROOT'].'/norisk2/xml/WorkAct.xml',$replace);
        if(!($file = $this->sbr->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ���� �� �������� ������";
        return $file;
    }
    
    /**
     * ��������� ����� �����
     *
     * @param  int $sbr_id ID ������
     * @param  int $num ����� �����
     * @return string
     */
    function getOuterNum($sbr_id = NULL, $num = NULL) {
        if(!$sbr_id) $sbr_id = $this->sbr->id;
        if($num===NULL) $num = $this->num;
        return $sbr_id.'-'.($num+1);
    }
    
    /**
     * ��������� ����� ����� ��� ���������� ���� �������.
     * 
     * @return string
     */
    function getOuterNum4Docs() {
        if($this->sbr->stages_cnt > 1)
            return ' (' . $this->getNumStage4Docs() . ' ����)';
        return NULL;
    }
    
    function getNumStage4Docs() {
        return ($this->num + 1);
    }
    
    /**
     * ���������� ����� ������ �����
     * 
     * @return string
     */
    function getFirstTime() {
        return $this->first_time ? $this->first_time : $this->sbr->reserved_time;
    }

    /**
     * ���������� ��������� � ������� � FM
     *
     * @param array $error   ������ ������ � ��������.
     * @return CFile   ����������� ��������.
     */
    function generateFrlAppl(&$error) {
        require_once (dirname(__FILE__).'/fpdf/fpdf.php');
        define ('FPDF_FONTPATH',(dirname(__FILE__).'/fpdf/font/'));
        require_once (dirname(__FILE__).'/num_to_word.php');

        $error = NULL;

        $rq = $this->sbr->getFrlReqvs(true);
        
        if(!$this->sbr->checkUserReqvs($this->sbr->frl_reqvs)) {
            $error['frl'] = '��� ������������ ��������� � ������� � FM<br/> ���������� ��������� ��������� �� �������� "�������"';
        }

        $params = array();
        $params['$date_statement'] = date("d {$GLOBALS['MONTHA'][date('n')]} Y");
        $params['$fio'] = $this->getFioFromReqvs($rq);
        $rq = $rq[1];
        foreach ($rq as $k => $v) {
            $params["$".$k] = $v;
        }
        
        $payout_sum = $this->getPayoutSum(sbr::FRL, exrates::BANK);
        $reserved_tm = strtotime($this->sbr->reserved_time);
        $params['$sbr_summ'] = sbr_meta::view_cost($payout_sum, NULL, false, ',', ' ');
        $params['$sbr_summ_str'] = num2strEx($payout_sum);
        $params['$sbr_num'] = $this->sbr->getContractNum();
        $params['$ssnum'] = $this->getOuterNum4Docs();
        $params['$sbr_date'] = date('d '.$GLOBALS['MONTHA'][date('n',$reserved_tm)].' Y �.', $reserved_tm);
        $account = new account();
        $account->GetInfo($this->sbr->uid);
        $params['$fmid'] = $account->id;

        $params['$nds_rate'] = '18%';
        $params['$nds_sum'] = sbr_meta::view_cost(18*$payout_sum/118, NULL, false, ',', ' ');
        $params['$nds_str'] = num2strEx(18*$payout_sum/118);

        $tpl_name = 'fm_appl_fiz.xml';
        
        if($this->sbr->frl_reqvs['form_type'] == sbr::FT_JURI) {
            $tpl_name = 'fm_appl_jur.xml';

            $rq = $this->sbr->frl_reqvs[sbr::FT_JURI];
            $params['$org_name'] = $rq['full_name'];
            $params['$reqvs'] = sbr_meta::getReqvsStr($this->sbr->frl_reqvs, $params['$bossname']);
        } else {
            $params['$idcard_name'] = $rq['idcard_name'];
            $params['$idcard'] = $rq['idcard'];
            $params['$idcard_by'] = $rq['idcard_from'].' '.$rq['idcard_by'];
        }

        $pdf = sbr::xml2pdf($_SERVER['DOCUMENT_ROOT']."/norisk2/xml/{$tpl_name}", $params);

        if(!($file = $this->sbr->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ��������� � ������� � FM";
        return $file;
    }

    /**
     * ��������� ������� ����� �� ������� ����� ���.
     * ������� -- ��� �������, ��� ������ ������� ���������.
     * ��, ���� ������������� "����� ��" -- ������.������ ������� ������ �����.
     * ���� ������� � FM, �� ��� ����� ������������ ����� �� ����.
     *
     * @param integer $user_id   ��. �����, �������� ��������������� �������.
     * @param integer $op_code   ��� ��������, ������ sbr::OP_CREDIT
     * @return boolean   �������?
     */
    function payout($user_id) {
        if(!$this->sbr->isAdmin()) return;
        setlocale(LC_ALL, 'en_US.UTF-8');
        if( !($this->getPayouts($user_id)) ) return false;
        if( $this->payouts[$user_id]['completed'] ) return false;
        $account = new account();
        $account->GetInfo($user_id);
        $credit_sys = $this->payouts[$user_id]['credit_sys'];
        $reqvs = sbr_meta::getUserReqvs($user_id);
        switch($credit_sys) {
            case exrates::BANK : if(!$reqvs[$reqvs['form_type']]['bank_rs']) return !($this->error = '���� �� ������'); $descr = "������ �� ����: {$reqvs[$reqvs['form_type']]['bank_rs']}"; break;
            case exrates::YM   : if(!$reqvs[sbr::FT_PHYS]['el_yd'])  return !($this->error = '������� �� ������'); $descr = "������.������ �� �������: {$reqvs[sbr::FT_PHYS]['el_yd']}"; break;
            case exrates::WMR  : if(!$reqvs[sbr::FT_PHYS]['el_wmr']) return !($this->error = '������� �� ������'); $descr = "WMR �� �������: {$reqvs[sbr::FT_PHYS]['el_wmr']}"; break;
            case exrates::FM   : $descr = "FM �� ����: {$account->id}"; break;
            default :
               return !($this->error = '�������� ������');
        }
        $this->sbr->getScheme();
        $percent = 1;
        if($this->payouts[$user_id]['is_arbitrage']=='t') {
            $this->getArbitrage();
            $percent = abs((int)($user_id==$this->sbr->emp_id) - $this->arbitrage['frl_percent']);
        }

        $iex = $credit_sys . $this->sbr->cost_sys;
        if(!$this->exrates)  $this->sbr->getExrates();
        $credit_sum = $this->payouts[$user_id]['credit_sum'];

        $debit_sum = round($credit_sum * $this->sbr->exrates[$iex], 2);
        $d_descr = '�������� '.($percent==1 ? '' : (100*$percent).'% (��������) ') . "������� ����������� ������ (���� #{$this->id})";
        
        $sbr = sbr_meta::getInstanceLocal($user_id);
        $sbr->initFromId($this->data['sbr_id'], false, false);
        $contract_num = $sbr->getContractNum();
        $comments = sbr_meta::view_cost($credit_sum, $credit_sys) . ', ' . $contract_num;
        $d_comments = sbr_meta::view_cost($debit_sum, $this->sbr->cost_sys) . ', ' . $contract_num;
        
        if( ($debit_id  = $account->CommitReserved($this->sbr->emp_id, $this->sbr->reserved_id, $d_descr, sbr::OP_DEBIT, $debit_sum, $d_comments)) &&
            ($credit_id = $account->TransferReserved($user_id, $credit_sum, $credit_sys-1, $descr, $errors, sbr::OP_CREDIT, $comments))       )
        {
            $sql = "
              UPDATE sbr_stages_payouts
                 SET debit_id = {$debit_id},
                     credit_id = {$credit_id},
                     completed = now()
               WHERE stage_id = {$this->id}
                 AND user_id = {$user_id}

                 AND completed IS NULL
              RETURNING *
            ";
            if($res = pg_query(self::connect(), $sql)) {
                $docs = array();
                $is_emp_arb = $this->status == sbr_stages::STATUS_ARBITRAGED  
                                && $this->arbitrage['resolved'] && floatval($this->arbitrage['frl_percent']) == 0;
                
                if ( ($this->sbr->scheme_type == sbr::SCHEME_AGNT || $this->sbr->scheme_type == sbr::SCHEME_LC ) && !$is_emp_arb) {
                    if($doc_file = $this->generateEmpAgentReport($doc_err, $rep_num)) { // ����� ������.
                        $docs[] = array ('stage_id' => $this->id, 'file_id' => $doc_file->id, 'num' => $rep_num, 'status' => sbr::DOCS_STATUS_PUBL,
                        'access_role' => sbr::DOCS_ACCESS_EMP, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_AGENT_REP );
                        if ($doc_file = $this->generateEmpAct($doc_err, $doc_num, $rep_num)) { // ��� ������������ �� ��������� ����� (�� �� ����� �� ���������, ���� ��� ��������).
                            $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'num' => $doc_num, 'status' => sbr::DOCS_STATUS_PUBL,
                                'access_role' => sbr::DOCS_ACCESS_EMP, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_ACT);
                        }
                    }
                } elseif ( ($this->sbr->scheme_type == sbr::SCHEME_PDRD || $this->sbr->scheme_type == sbr::SCHEME_PDRD2 ) && !$is_emp_arb) {
                    if($doc_file = $this->generateArbReportPdrdEmp($doc_err)) { // ����� ��������� �� �������� ������� ��� ������������.
                        $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_PUBL,
                                      'access_role' => sbr::DOCS_ACCESS_EMP, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_ARB_REP);
                    }
                }
                
                if (count($docs)) {
                    foreach($docs as $doc) {
                        $ok = $this->sbr->addDocR($doc);
                    }
                }
                
                $this->payouts[$user_id] = pg_fetch_assoc($res);
                return true;
            }
        }
        if ($debit_id && !$credit_id){
            $account->Del($user_id, $debit_id);
        }
        return false;
    }

    /**
     * ��������� ������� ����� �� ������� ����� ���.
     * ������� -- ��� �������, ��� ������ ������� ���������.
     *
     * @param integer $user_id   ��. �����, �������� ��������������� �������.
     * @return boolean   �������?
     */
    function payoutAgnt($user_id, pskb $pskb, pskb_lc $pskb_lc) {
        setlocale(LC_ALL, 'en_US.UTF-8');
        if( !($this->getPayouts($user_id)) ) return false;
        if( $this->payouts[$user_id]['completed'] ) return false;
        $account = new account();
        $account->GetInfo($user_id);
        $credit_sys = $this->payouts[$user_id]['credit_sys'];
        
        $lc = $pskb->getLC(true);
        $acc = $user_id == $this->sbr->emp_id ? $lc['accCust'] : $lc['accPerf'];
        
        switch($credit_sys) {
            case exrates::BANK : $descr = "������ �� ����: {$acc}"; break;
            case exrates::YM   : $descr = "������.������ �� �������: {$acc}"; break;
            case exrates::WMR  : $descr = "WMR �� �������: {$acc}"; break;
            case exrates::WEBM : $descr = "����� �� ���-�������"; break;
            case exrates::CARD : $descr = "������ �� ���� ����������� �����"; break;
            default :
               return !($this->error = '�������� ������');
        }
        $this->sbr->getScheme();
        $percent = 1;
        if($this->payouts[$user_id]['is_arbitrage']=='t') {
            $this->getArbitrage();
            $percent = abs((int)($user_id==$this->sbr->emp_id) - $this->arbitrage['frl_percent']);
        }

        $iex = $credit_sys . $this->sbr->cost_sys;
        if(!$this->exrates)  $this->sbr->getExrates();
        $credit_sum = $this->payouts[$user_id]['credit_sum'];

        $debit_sum = round($credit_sum * $this->sbr->exrates[$iex], 2);
        $d_descr = '�������� '.($percent==1 ? '' : (100*$percent).'% (��������) ') . "������� ����������� ������ (���� #{$this->id})";

        $sbr = sbr_meta::getInstanceLocal($user_id);
        $sbr->initFromId($this->data['sbr_id'], false, false);
        $comments = sbr_meta::view_cost($credit_sum, $credit_sys) . ', ' . $sbr->getContractNum();
        $d_comments = sbr_meta::view_cost($debit_sum, $this->sbr->cost_sys) . ', ' . $sbr->getContractNum();
        if( ($debit_id  = $account->CommitReserved($sbr->emp_id, $this->sbr->reserved_id, $d_descr, sbr::OP_DEBIT, $debit_sum, $d_comments)) &&
            ($credit_id = $account->TransferReserved($user_id, $credit_sum, $credit_sys-1, $descr, $errors, sbr::OP_CREDIT, $comments))       )
        {
            $sql = "
              UPDATE sbr_stages_payouts
                 SET debit_id = {$debit_id},
                     credit_id = {$credit_id},
                     completed = now(),
                     state = '{$pskb_lc->state}',
                     \"stateReason\" = '{$pskb_lc->stateReason}',
                     bank_completed = '{$pskb_lc->date}'
               WHERE stage_id = {$this->id}
                 AND user_id = {$user_id}

                 AND completed IS NULL
              RETURNING *
            ";
            if($res = pg_query(self::connect(), $sql)) {
                $this->payoutUpdateState($pskb_lc);
                $docs = array();
                $is_emp_arb = $this->status == sbr_stages::STATUS_ARBITRAGED  
                                && $this->arbitrage['resolved'] && floatval($this->arbitrage['frl_percent']) == 0;
                
                if ($this->sbr->scheme_type == sbr::SCHEME_LC && !$is_emp_arb) {
                    if($doc_file = $this->generateEmpAgentReport($doc_err, $rep_num)) { // ����� ������.
                        $docs[] = array ('stage_id' => $this->id, 'file_id' => $doc_file->id, 'num' => $rep_num, 'status' => sbr::DOCS_STATUS_PUBL,
                        'access_role' => sbr::DOCS_ACCESS_EMP, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_AGENT_REP );
                        if ($doc_file = $this->generateEmpAct($doc_err, $doc_num, $rep_num)) { // ��� ������������ �� ��������� ����� (�� �� ����� �� ���������, ���� ��� ��������).
                            $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'num' => $doc_num, 'status' => sbr::DOCS_STATUS_PUBL,
                                'access_role' => sbr::DOCS_ACCESS_EMP, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_ACT);
                        }
                    }
                } elseif ( ($this->sbr->scheme_type == sbr::SCHEME_PDRD || $this->sbr->scheme_type == sbr::SCHEME_PDRD2) && !$is_emp_arb) {
                    if($doc_file = $this->generateArbReportPdrdEmp($doc_err)) { // ����� ��������� �� �������� ������� ��� ������������.
                        $docs[] = array('stage_id' => $this->id, 'file_id' => $doc_file->id, 'status' => sbr::DOCS_STATUS_PUBL,
                                      'access_role' => sbr::DOCS_ACCESS_EMP, 'owner_role' => 0, 'type' => sbr::DOCS_TYPE_ARB_REP);
                    }
                }
                
                if (count($docs)) {
                    foreach($docs as $doc) {
                        $ok = $this->sbr->addDocR($doc);
                    }
                }
                
                $this->payouts[$user_id] = pg_fetch_assoc($res);
                return true;
            }
        }
        if ($debit_id && !$credit_id){
            $account->Del($user_id, $debit_id);
        }
        return false;
    }
    
    function payoutUpdateState(pskb_lc $pskb_lc) {
        $apply_state = array(pskb::STATE_TRANS, pskb::STATE_PASSED, pskb::PAYOUT_END, pskb::PAYOUT_ERR);
        if(!in_array($pskb_lc->state, $apply_state)) return false;
        $sql = "
              UPDATE sbr_stages_payouts
                 SET state = '{$pskb_lc->state}',
                     \"stateReason\" = '{$pskb_lc->stateReason}'
                     " . ( $pskb_lc->date ? ", bank_completed = '{$pskb_lc->date}' " :  " " ) . "
                     " . ( $pskb_lc->state == pskb::STATE_TRANS ? ", executed = NOW() " :  " " ) . "
              WHERE stage_id = {$this->id}
              " . ( !$pskb_lc->date ? " AND state <> '{$pskb_lc->state}' " : "" );
        return pg_query(self::connect(), $sql);
    }

    /**
     * ������ ����������� ����� �������. ���� ������� � FM, �� ������ ��������� �� �����.
     *
     * @param integer $user_id   ��. �����, �������� ��������������� �������.
     * @return boolean   �������?
     */
    function unpayout($user_id) {
        if(!$this->sbr->isAdmin()) return;
        if( !($this->getPayouts($user_id)) ) return false;
        $payout = $this->payouts[$user_id];
        if(!$payout['credit_id'] || !$payout['debit_id']) return false;
        $account = new account();
        if( !($err = $account->Del($user_id, $payout['credit_id'])) &&
            !($err = $account->Del($this->sbr->emp_id, $payout['debit_id']))      )
        {
            $sql = "
              UPDATE sbr_stages_payouts
                 SET completed = NULL
               WHERE stage_id = ?i
                 AND user_id = ?i
            ";
                 
            $sql = $this->db()->parse($sql, $this->id, $user_id);     
            return !!pg_query(self::connect(), $sql);
        }
        return false;
    }

    /**
     * ������� ��
     *
     * @param  int $user_id UID ������������
     * @param boolean $confirmed  �������, ��� ������ ������� �������� (����� ������-�� ��������������, �������������� �������������).
     * @return object yd_payments
     */
    function ydPayout($user_id, $confirmed = false) {
        if(!$this->sbr->isAdmin()) return;
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/yd_payments.php';
        if(!$this->getPayouts($user_id))
            return NULL;
        $payout = $this->payouts[$user_id];
        if($payout['completed']) // !!! ���� ��� ������� �� �����, �� �� ������ ����������� ��. ����� ������� ��������.
            return NULL;
        $yd = new yd_payments($payout['id'], yd_payments::SRC_SBR);
        if(!is_release()) {
            $yd->DEBUG = array(
              'address'=>$GLOBALS['host'].'/norisk2/admin/yd-server-test.php'
            );
            if(defined('BASIC_AUTH')) {
                $yd->DEBUG['headers'] = 'Authorization: Basic '.base64_encode(BASIC_AUTH)."\r\n";
            }
        }
        $reqvs = sbr_meta::getUserReqvs($user_id);
        $dstacnt_nr = $reqvs[sbr::FT_PHYS]['el_yd'];
        $yd->pay($payout['credit_sum'], $dstacnt_nr, '������� '.$this->sbr->getContractNum());
        if($yd->pmt['out_amt'] && $yd->pmt['in_amt'] <= $yd->pmt['out_amt']) {
            if(!$this->payout($user_id) && !$this->payouts[$user_id]['completed'])
                $yd->error('������ �������� �������� �� ����� ��'.($this->error ? ': '.$this->error : '').'<br/>�������� ����� ��������� ��������, ����� �� ������ "���������" � ������� ������.');
        }
        $pmt = $yd->pmt;
        $pmt['dstacnt_nr'] = $yd->tr['dstacnt_nr'];
        $pmt['performed_dt'] = $yd->tr['performed_dt'];
        $pmt['errors'] = $yd->errors;
        $pmt['amt_sys'] = exrates::YM;
        return $pmt;
    }
    
    /**
     * ������� WMR
     *
     * @param int $user_id UID ������������
     * @param boolean $confirmed  �������, ��� ������ ������� �������� (����� ������-�� ��������������, �������������� �������������).
     * @return object wm_payments
     */
    function wmPayout($user_id, $confirmed = false) {
        if(!$this->sbr->isAdmin()) return;
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wm_payments.php';
        if(!$this->getPayouts($user_id))
            return NULL;
        $payout = $this->payouts[$user_id];
        if($payout['completed']) // !!! ���� ��� ������� �� �����, �� �� ������ ����������� ��. ����� ������� ��������.
            return NULL;
        $wm = new wm_payments($payout['id'], wm_payments::SRC_SBR);
        if(!is_release()) {
            $wm->DEBUG = array(
              'address'=>$GLOBALS['host'].'/norisk2/admin/wm-server-test.php'
            );
            if(defined('BASIC_AUTH')) {
                $wm->DEBUG['headers'] = 'Authorization: Basic '.base64_encode(BASIC_AUTH)."\r\n";
            }
        }
        $reqvs = sbr_meta::getUserReqvs($user_id);
        $rq = $reqvs[sbr::FT_PHYS];
        // $passport = sbr_meta::parse_idcard($rq['idcard'], $reqvs['rez_type'], $rq['country']); 
        
        $wm->ignoreLimit = $confirmed;
        $wm->pay($rq['fio'], $rq['el_doc_series'], $rq['el_doc_number'], $rq['el_doc_from'], $rq['el_wmr'], $payout['credit_sum'],
                 NULL, NULL, $rq['phone']);
        if($wm->pmt['out_amt'] && $wm->pmt['in_amt'] <= $wm->pmt['out_amt']) {
            if(!$this->payout($user_id) && !$this->payouts[$user_id]['completed'])
                $wm->error('������ �������� �������� �� ����� ��'.($this->error ? ': '.$this->error : '').'<br/>�������� ����� ��������� ��������, ����� �� ������ "���������" � ������� ������.');
        }
        $pmt = $wm->pmt;
        $pmt['dstacnt_nr'] = $wm->tr['purse'];
        $pmt['performed_dt'] = $wm->tr['dateupd'];
        $pmt['errors'] = $wm->errors;
        $pmt['amt_sys'] = exrates::WMR;
        $pmt['confirmed'] = $wm->reqConfirm;
        return $pmt;
    }
    
    /**
     * ���������� ���������� � ������� ��
     * 
     * @param  int $user_id UID ������������
     * @return object yd_payments
     */
    function getYdPaymentInfo($user_id) {
        if(!$this->sbr->isAdmin()) return;
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/yd_payments.php';
        if(!$this->getPayouts($user_id))
            return NULL;
        $payout = $this->payouts[$user_id];
        $yd = new yd_payments($payout['id'], yd_payments::SRC_SBR);
        if(!is_release()) {
            $yd->DEBUG = array(
              'address'=>$GLOBALS['host'].'/norisk2/admin/yd-server-test.php'
            );
            if(defined('BASIC_AUTH')) {
                $yd->DEBUG['headers'] = 'Authorization: Basic '.base64_encode(BASIC_AUTH)."\r\n";
            }
        }
        $pmt = $yd->getPayment();
        if($pmt) {
            $tr = $yd->getTr($pmt['ltr_id']);
            $pmt['dstacnt_nr'] = $tr['dstacnt_nr'];
            $pmt['performed_dt'] = $tr['performed_dt'];
        } else {
            $reqvs = sbr_meta::getUserReqvs($user_id);
            $pmt = array('src_id'=>$payout['id'], 'src_type'=>yd_payments::SRC_SBR, 'in_amt'=>$payout['credit_sum'],
                         'dstacnt_nr'=>$reqvs[sbr::FT_PHYS]['el_yd']);
        }
        $pmt['balance'] = $yd->balance();
        $pmt['errors'] = $yd->errors;
        $pmt['amt_sys'] = exrates::YM;
        return $pmt;
    }
    
    
    /**
     * ���������� ���������� � ������� ��
     * 
     * @param  int $user_id UID ������������
     * @return object yd_payments
     */
    function getWmPaymentInfo($user_id) {
        if(!$this->sbr->isAdmin()) return;
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wm_payments.php';
        if(!$this->getPayouts($user_id))
            return NULL;
        $payout = $this->payouts[$user_id];
        $wm = new wm_payments($payout['id'], wm_payments::SRC_SBR);
        if(!is_release()) {
            $wm->DEBUG = array(
              'address'=>$GLOBALS['host'].'/norisk2/admin/wm-server-test.php'
            );
            if(defined('BASIC_AUTH')) {
                $wm->DEBUG['headers'] = 'Authorization: Basic '.base64_encode(BASIC_AUTH)."\r\n";
            }
        }
        $pmt = $wm->getPayment();
        $reqvs = sbr_meta::getUserReqvs($user_id);
        if($pmt) {
            $tr = $wm->getTr($pmt['ltr_id']);
            $pmt['dstacnt_nr'] = $tr ? $tr['purse'] : $reqvs[sbr::FT_PHYS]['el_wmr'];
            $pmt['performed_dt'] = $tr['dateupd'];
        } else {
            $pmt = array('src_id'=>$payout['id'], 'src_type'=>wm_payments::SRC_SBR, 'in_amt'=>$payout['credit_sum'],
                         'dstacnt_nr'=>$reqvs[sbr::FT_PHYS]['el_wmr']);
        }
        // $pmt['balance'] = $wm->balance();
        $pmt['errors'] = $wm->errors;
        $pmt['amt_sys'] = exrates::WMR;
        return $pmt;
    }

    /**
     * ���������� HTML ���� � ����������� � ������� ��|WMR
     *
     * @param  object $yd yd_payments
     * @param  int $user_id UID ������������
     * @return string
     */
    function view_payout_popup($pmt, $user_id) {
        if(!$this->sbr->isAdmin()) return;
        $sbr = $this->sbr;
        $stage = $this;
        $upfx = $user_id == $sbr->frl_id ? 'frl_' : 'emp_';
        $pmt['out_per'] = round(100 * ($pmt['out_amt'] / $pmt['in_amt']));

        ob_start();
        include($_SERVER['DOCUMENT_ROOT'].'/norisk2/admin/tpl.payout_box.php');
        return ob_get_clean();
    }
    
    
    /**
     * ������������� ����� ����� ������� WMR ��� ������� �����.
     *
     * @param  int $user_id UID ������������
     * @param  int $limit    �����.
     * @return boolean 
     */
    function saveWmPaymentLimit($user_id, $limit) {
        if(!$this->sbr->isAdmin()) return;
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wm_payments.php';
        if(!$this->getPayouts($user_id))
            return NULL;
        $payout = $this->payouts[$user_id];
        $wm = new wm_payments($payout['id'], wm_payments::SRC_SBR);
        return $wm->setLimit($limit);
    }
    
    
    /**
     * ��������� ��������� � ������� ����� ������� WebMoney Transfer
     * 
     * @param array $error   ������ ������ � ��������.
     * @param type $act_num  � ���� �����-������� �����/����� 
     * @return CFile   ����������� ��������.
     */
    public function generateFrlWMAppl(&$error, $act_num = false) {
        require_once (dirname(__FILE__).'/fpdf/fpdf.php');
        define ('FPDF_FONTPATH',(dirname(__FILE__).'/fpdf/font/'));
        require_once (dirname(__FILE__).'/num_to_word.php');
        
        $error = NULL;

        $rq = $this->sbr->getFrlReqvs(true);
        
        if(!$this->sbr->checkUserReqvs($this->sbr->frl_reqvs)) {
            $error['frl'] = '��� ������������ ��������� � ������� ����� ������� "WebMoney Transfer"<br/> ���������� ��������� ��������� �� �������� "�������"';
        }
        if($rq[1]['el_wmr'] == '') {
            $error['frl'] = '��� ������������ ��������� � ������� ����� ������� "������.������"<br/> ���������� ��������� ����� �������� WMR';
        }
        
        $params = array();
        $params['$date_statement'] = $this->redate_act ? date("d {$GLOBALS['MONTHA'][date('n')]} Y", strtotime($this->redate_act)) : date("d {$GLOBALS['MONTHA'][date('n')]} Y");
        $params['$fio'] = $this->getFioFromReqvs($rq);
        
        $rq = $rq[1];
        foreach ($rq as $k => $v) {
            $params["$".$k] = $v;
        }
        
        if($this->sbr->scheme_type == sbr::SCHEME_AGNT || $this->sbr->scheme_type == sbr::SCHEME_LC) {
            $params['$doc_offer'] = '�.6.2. ������ �� ���������� �������� �� �������������';
        } else if($this->sbr->scheme_type == sbr::SCHEME_PDRD || $this->sbr->scheme_type == sbr::SCHEME_PDRD2) {
            $params['$doc_offer'] = '�.9.2. ������ �� ���������� ���������� � ���������� ������ �/��� �������� ������ � ��������������';
        }
        
        $sbr_num = $this->sbr->getContractNum();
        $ssnum   = $this->getOuterNum4Docs();
        
        $reserved_tm = strtotime($this->sbr->reserved_time);
        $payout_sum = $this->getPayoutSum(sbr::FRL, exrates::BANK);
        $params['$sbr_summ'] = sbr_meta::view_cost($payout_sum, NULL, false, ',', ' ');
        $params['$sbr_summ_str'] = num2strEx($payout_sum);
        $params['$act_doc'] = "";
        if($act_num) {
            $params['$act_doc'] = "(��� �����-������� �����/����� � {$act_num} �� ".$params['$date_statement'].")";
        } else {
            $params['$act_doc'] = "(��� �����-������� �����/����� �� " . $params['$date_statement'] . " �� �������� ������� � {$sbr_num}{$ssnum} �� " . date('d '.$GLOBALS['MONTHA'][date('n',$reserved_tm)].' Y �.', $reserved_tm) . ")";
        }
        $params['$sbr_id']      = $this->sbr->id;
        $params['$sbr_num']     = $sbr_num;
        $params['$sbr_date']    = date('d '.$GLOBALS['MONTHA'][date('n',$reserved_tm)].' Y �.', $reserved_tm);
        $params['$ssnum']       = $ssnum;
        $params['$idcard_by']   = $rq['idcard_from'].' '.$rq['idcard_by'];
        
        $tpl_name = 'wm_appl_fiz.xml';
        
        $pdf = sbr::xml2pdf($_SERVER['DOCUMENT_ROOT']."/norisk2/xml/{$tpl_name}", $params);

        if(!($file = $this->sbr->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ��������� � ������� ����� ������� \"WebMoney Transfer\"";
        return $file;
    }
    
    /**
     * ��������� ��������� � ������� ����� ������� ������.������
     * 
     * @param array $error   ������ ������ � ��������.
     * @param type $act_num  � ���� �����-������� �����/����� 
     * @return CFile   ����������� ��������.
     */
    public function generateFrlYMAppl(&$error, $act_num = false) {
        require_once (dirname(__FILE__).'/fpdf/fpdf.php');
        define ('FPDF_FONTPATH',(dirname(__FILE__).'/fpdf/font/'));
        require_once (dirname(__FILE__).'/num_to_word.php');
        
        $error = NULL;

        $rq = $this->sbr->getFrlReqvs(true);
        
        if(!$this->sbr->checkUserReqvs($this->sbr->frl_reqvs)) {
            $error['frl'] = '��� ������������ ��������� � ������� ����� ������� "������.������"<br/> ���������� ��������� ��������� �� �������� "�������"';
        }
        if($rq[1]['el_yd'] == '') {
            $error['frl'] = '��� ������������ ��������� � ������� ����� ������� "������.������"<br/> ���������� ��������� ��������� ����� �������� ������.������';
        }
        
        $params = array();
        $params['$date_statement'] =  $this->redate_act ? date("d {$GLOBALS['MONTHA'][date('n')]} Y", strtotime($this->redate_act)) : date("d {$GLOBALS['MONTHA'][date('n')]} Y");
        $params['$fio'] = $this->getFioFromReqvs($rq);
        
        $rq = $rq[1];
        foreach ($rq as $k => $v) {
            $params["$".$k] = $v;
        }
        
        if($this->sbr->scheme_type == sbr::SCHEME_AGNT || $this->sbr->scheme_type == sbr::SCHEME_LC) {
            $params['$doc_offer'] = '�.6.2. ������ �� ���������� �������� �� �������������';
        } else if($this->sbr->scheme_type == sbr::SCHEME_PDRD || $this->sbr->scheme_type == sbr::SCHEME_PDRD2) {
            $params['$doc_offer'] = '�.9.2. ������ �� ���������� ���������� � ���������� ������ �/��� �������� ������ � ��������������';
        }
        
        $sbr_num = $this->sbr->getContractNum();
        $ssnum   = $this->getOuterNum4Docs();
        
        $reserved_tm = strtotime($this->sbr->reserved_time);
        $payout_sum = $this->getPayoutSum(sbr::FRL, exrates::BANK);
        $params['$sbr_summ'] = sbr_meta::view_cost($payout_sum, NULL, false, ',', ' ');
        $params['$sbr_summ_str'] = num2strEx($payout_sum);
        $params['$act_doc'] = "";
        if($act_num) {
            $params['$act_doc'] = "(��� �����-������� �����/����� � {$act_num} �� ".date("d {$GLOBALS['MONTHA'][date('n')]} Y").")";
        } else {
            $params['$act_doc'] = "(��� �����-������� �����/����� �� " . date("d {$GLOBALS['MONTHA'][date('n')]} Y") . " �� �������� ������� � {$sbr_num}{$ssnum} �� " . date('d '.$GLOBALS['MONTHA'][date('n',$reserved_tm)].' Y �.', $reserved_tm) . ")";
        }
        $params['$sbr_id']      = $this->sbr->id;
        $params['$sbr_num']     = $sbr_num;
        $params['$sbr_date']    = date('d '.$GLOBALS['MONTHA'][date('n',$reserved_tm)].' Y �.', $reserved_tm);
        $params['$ssnum']       = $ssnum;
        $params['$idcard_by']   = $rq['idcard_from'].' '.$rq['idcard_by'];
        
        $tpl_name = 'yd_appl_fiz.xml';
        
        $pdf = sbr::xml2pdf($_SERVER['DOCUMENT_ROOT']."/norisk2/xml/{$tpl_name}", $params);

        if(!($file = $this->sbr->_saveDocFile($pdf->Output(NULL, 'S'))))
            $error['fatal'] = "������ ��� ������������ ��������� � ������� ����� ������� \"������.������\"";
        return $file;
    }
    
    // ���������� �� ������������
    public function notificationAcredit() {
        // ��� ������������� �����
        if($this->data['status'] != sbr_stages::STATUS_�LOSED) {
            switch($this->data['lc_state']) {
                // ���� �������� ���������� 
                case 'passed':
                    if($this->getPayoutSum(sbr::FRL) > 0) {
                        $note = array(
                            'ntype'   => 'pskb.PASSED',
                            'xact_id' => null,
                            'evnt'    => null
                        );
                    } else {
                        $note = array(
                            'ntype'   => 'pskb.PASSED_EMP',
                            'xact_id' => null,
                            'evnt'    => null
                        );
                    }
                    break;
                default:
                    $note = false;
                    break;
            }
            if($note) return $note;
        }
        if(!$this->sbr->isEmp())  return false;
        if($this->data['status'] != sbr_stages::STATUS_�LOSED) return false;
        switch($this->sbr->data['state']) {
            // ���� �������� ���������� 
            case 'form':
                $note = array(
                    'ntype'   => 'pskb.FORM',
                    'xact_id' => null,
                    'evnt'    => null
                );
                break;
            // �������� ����������� �����, ����������������� ��� ������.
            case 'new':
                $note = array(
                    'ntype'   => 'pskb.NEW',
                    'xact_id' => null,
                    'evnt'    => null
                );
                break;
            //������ �� ��������� � ����. ������ �� ������.
            case 'exp':
                $note = array(
                    'ntype'   => 'pskb.EXP',
                    'xact_id' => null,
                    'evnt'    => null
                );
            // ����������� �� ����� ���������. ������ ������������ ���������. 
            case 'expExec':
                $note = array(
                    'ntype'   => 'pskb.EXP_EXEC',
                    'xact_id' => null,
                    'evnt'    => null
                );
                break;  
            //���� ����������� �����. ������ ������������ ���������. 
            case 'expEnd':
                $note = array(
                    'ntype'   => 'pskb.EXP_END',
                    'xact_id' => null,
                    'evnt'    => null
                );
            case 'passed':
                if($this->getPayoutSum(sbr::FRL) > 0) {
                    $note = array(
                        'ntype'   => 'pskb.PASSED',
                        'xact_id' => null,
                        'evnt'    => null
                    );
                } else {
                    $note = array(
                        'ntype'   => 'pskb.PASSED_EMP',
                        'xact_id' => null,
                        'evnt'    => null
                    );
                }
            default:
                return false;
                break;
        }
        
        
        return $note;
    }
    
    /**
     * �������������� ��������� ���������� �� �����
     */
    public function initNotification() {
        $note = $this->notificationAcredit();
        if($note !== false) {
            $this->notification = $note;
            return;
        } 
        
        $this->notification = sbr_notification::getNotification($this->data['sbr_id'], $this->data['id']);
    }
    
    /**
     * ���������� �������� ����������
     * 
     * @return string
     */
    public function getNotificationName() {
        if(!$this->notification) {
            $this->initNotification();
        }
        $type = $this->sbr->isEmp() ? 0 : ( $this->sbr->isFrl() ? 1 : 2 );
        return  sbr_notification::getNotificationName($this->notification['ntype'], $type, $this);
    }
    
    /**
     * �������� ������� (���� ���� ���������� ���������� ���������� ����� ������ �����)
     * 
     * @param integer $status  ������ ���
     * @return string 
     */
    public function getStatusName($status = false, $notification = true) {
        if($status === false) $status = $this->data['status'];
        
        if($this->notification && $notification && !($this->sbr->status != sbr::STATUS_CANCELED || $this->sbr->status != sbr::STATUS_REFUSED || $this->sbr->status != sbr::STATUS_CLOSED) ) {
            $notify =  ( $this->sbr->isEmp()? sbr_notification::getNotificationName($this->notification['ntype'], 0, $this) :  sbr_notification::getNotificationName($this->notification['ntype'], 1, $this) );
        }
        
        if($notify) {
            return $notify;
        } elseif($this->sbr->status == sbr::STATUS_CANCELED || $this->sbr->status == sbr::STATUS_REFUSED) {
            return "���� �������";
        } else {
            return "���� " . sbr_stages::$nss_classes[$status][1];
        }
    }
    
    /**
     * ����� ������ �������
     * 
     * @param integer $status
     * @return string
     */
    public function getStatusIco($status = false, $notification = true) {
        if($status === false) $status = $this->data['status'];
        if($this->notification && $notification && ($this->sbr->status != sbr::STATUS_CANCELED || $this->sbr->status != sbr::STATUS_REFUSED) ) {
            $ico = ( $this->sbr->isEmp() ? sbr_notification::$ico_emp[$status][$this->notification['ntype']][0] : sbr_notification::$ico_frl[$status][$this->notification['ntype']][0]);
        }
        
        if($ico) {
            return $ico;
        }
        
        if($this->sbr->status == sbr::STATUS_CANCELED || $this->sbr->status == sbr::STATUS_REFUSED) {
            return "b-icon_sbr_rdel";
        }
        
        return sbr_stages::$nss_classes[$status][0];
    }
    
    /**
     * ����� ����� �������� �������
     * 
     * @param integer $status
     * @return string
     */
    public function getStatusColor($status = false, $notification = true) {
        if($status === false) $status = $this->data['status'];
        if($this->notification && $notification) {
            return ( $this->sbr->isEmp() ? sbr_notification::$ico_emp[$status][$this->notification['ntype']][1] : sbr_notification::$ico_frl[$status][$this->notification['ntype']][1]);
        }
        return sbr_stages::$nss_classes[$status][2];
    }
    
    /**
     * ���������� �� �� �����
     * 
     * @return boolean   true - ����������, false - �� ���������� 
     */
    public function isNewVersionTz() {
        $descr_diff  = ($this->data['descr'] != $this->v_data['descr']);
        $attach_diff = ($this->data['attach_diff'] != $this->v_data['attach_diff']);
        if(is_array($this->v_data['attach_diff']) && is_array($this->data['attach_diff'])) {
            $array_diff = (array_diff_assoc($this->v_data['attach_diff'], $this->data['attach_diff']) || array_diff_assoc($this->data['attach_diff'], $this->v_data['attach_diff']));
        } else {
            $array_diff = true;
        }
        
        return ($descr_diff || $attach_diff || $this->data['attach_diff'] && $this->v_data['attach_diff'] && $array_diff);
    }
    
    /**
     * ���������� �� ����� ������ �� �����
     * 
     * @return boolean   true - ����������, false - �� ����������
     */
    public function isNewVersionCost() {
        return ($this->cost != $this->v_data['cost'] || $this->sbr->cost_sys != $this->sbr->v_data['cost_sys'] || $this->sbr->scheme_type != $this->sbr->v_data['scheme_type']);
    }
    
    /**
     * ��������� ���������� �� ����� �����
     * 
     * @return boolean   true - ����������, false - �� ���������� 
     */
    public function isNewVersionWorkTime() {
        return ($this->v_data['work_time'] != $this->data['work_time'] || $this->v_data['dead_time'] != $this->data['dead_time']); 
    }
    
    /**
     * ��������� ��������� �� ������ ����� ��� ���
     * 
     * @return boolean   true - ���������, false - �� ��������� 
     */
    public function isNewVersionStatus() {
        return ($this->data['status'] != $this->v_data['status']);
        
    }

    public function isAccessComplete() {
        return ($this->sbr->isEmp() && $_GET['event'] == 'complete' && in_array($this->data['status'], array(sbr_stages::STATUS_FROZEN, sbr_stages::STATUS_PROCESS)) );
    }

    public function isAccessOldFeedback() {
        if($this->data['closed_time'] == null) return true;
        return !$this->sbr->isEmp() || strtotime("+10 days", strtotime($this->data['closed_time'])) >= time();
    }
    
    /**
     * ������� ����� ���� �� ������� � �������
     * 
     * @return array        0 - ������ ���� ��, 1 - ����� ���� ��
     */
    public function dateVersionTz() {
        $ev_code = current(sbr_notification::getEventCode(array('sbr_stages' => array('TZ_MODIFIED'))));
        $times = sbr_notification::getNotificationsForStage($this->data['sbr_id'], $this->data['id'], $ev_code['id']);
        
        // ����� ��������� ��� ���� (0 - ���� �� ������, 1 - ���� �� �� �����)
        if(count($times) >= 2) {
            $result = array(strtotime($times[1]['xtime']), strtotime($times[0]['xtime']));
        } else if(count($times) == 1) { // ���� 1 ��������� �� ������ ���� �� - ��� ���� �������� �����, ����� ���� - ��� ���� ������ �������
            $result = array(strtotime($this->data['created']), strtotime($times[0]['xtime']));
        } else {
            $result = array(strtotime($this->data['created']), strtotime($this->data['created'])); // ���� �������� �����
        }
        
        $this->data['date_version_tz'] = $result;
        return $result;
    }
    
    /**
     * ���������� ����� �������� ������� ���������� �� ���� (� ���� ������� string ���� '5 days');
     * @param string $work_time   @see ������� sbr_stages.work_time
     * @return integer 
     */
    public function getStageWorkTime($work_time = false) {
        if(!$work_time) $work_time = $stage->data['work_time'];
        
        $work_time = intval($work_time);
        return $work_time < 0 ? 0 : $work_time;
    }
    
    /**
     * ���������� ��� ������� �� ����� ������ ��� �����
     * 
     * @global object $DB ����������� � ��
     * @return array
     */
    public function getHistoryStage($order = 'DESC') {
        global $DB;
        
        $sql = "SELECT 
                    se.*, sx.xtime, sec.own_rel || '.' || sec.abbr as abbr, sec.own_role, NULL::text as msg,
                    st.rel, st.col, sv.old_val, sv.new_val, sv.note, sv.src_id, she.descr as history_descr, 
                    se.estatus, se.fstatus
                FROM sbr_events se
                LEFT JOIN sbr_history_events she ON she.event_id = se.id
                INNER JOIN sbr_xacts sx ON sx.id = se.xact_id 
                INNER JOIN sbr_ev_codes sec ON sec.id = se.ev_code
                LEFT JOIN sbr_versions sv ON sv.event_id = se.id
                LEFT JOIN sbr_types st ON st.id = sv.src_type_id
                WHERE sbr_id = ?i AND own_id IN (?i, ?i)

                UNION
                
                SELECT 
                    se.*, sx.xtime, sec.own_rel || '.' || sec.abbr as abbr, sec.own_role, ssm.msgtext as msg,
                    st.rel, st.col, sv.old_val, sv.new_val, sv.note, ssma.file_id as src_id, she.descr as history_descr,
                    se.estatus, se.fstatus
                FROM sbr_events se
                LEFT JOIN sbr_history_events she ON she.event_id = se.id
                INNER JOIN sbr_xacts sx ON sx.id = se.xact_id 
                INNER JOIN sbr_ev_codes sec ON sec.id = se.ev_code
                INNER JOIN sbr_stages_msgs ssm ON ssm.id = se.own_id
                LEFT JOIN sbr_stages_msgs_attach ssma ON ssma.msg_id = ssm.id
                LEFT JOIN sbr_versions sv ON sv.event_id = se.id
                LEFT JOIN sbr_types st ON st.id = sv.src_type_id
                WHERE sbr_id = ?i AND ssm.stage_id = ?i    

                ORDER BY id ASC";
        
        $result = $DB->rows($sql, $this->sbr->id, $this->sbr->id, $this->id, $this->sbr->id, $this->id);
        $only_frl = array('sbr_stages.REFUSE', 'sbr.AGREE', 'sbr_stages.AGREE', 'sbr.REFUSE', 'sbr_stages.FRL_ARB', 'sbr.DELADD_SS_AGREE', 'sbr.DELADD_SS_REFUSE', 'sbr_stages.FRL_FEEDBACK', 'sbr_stages.STARTED_WORK', 'sbr_stages.COMMENT');
        
        foreach($result as $key=>$value) {
            if(in_array($value['abbr'], $only_frl) && $xact == $value['xact_id']) {
                array_unshift($history[$value['xact_id']], $value);
            } else {
                if($abbr == $value['abbr'] && $xact == $value['xact_id']) {
                    $group_history[$value['xact_id']][$value['abbr']][] = $value;
                } else {
                    $history[$value['xact_id']][$value['abbr']] = $value;
                }
            }
                
            $xact     = $value['xact_id'];
            $own_role = $value['own_role'];
            $abbr     = $value['abbr'];
        }
        $this->group_history = $group_history;
        // ���� ����������� � �������, �� ��������� ����������� ������� ������� ��������� ���
        if($order == 'DESC') $history = array_reverse($history, true); 
        $this->history       = $history;
        
        return $this->history;
    }
    
    /**
     * ������������ ��� � �������
     */
    function agreeStage($stage_id) {
        global $DB;
        
        $update = array('frl_agree' => true);
        return $DB->update('sbr_stages', $update, " id = ?i", $stage_id);
    }
    
    /**
     * ���� ���������� true, �� ��� ��������� ��� ������� ������ � ����� ��� ������ ������
     * 
     * @return type 
     */
    function isFixedState() {
        return ($this->data['status'] == self::STATUS_COMPLETED || $this->data['status'] == self::STATUS_ARBITRAGED || $this->data['status'] == self::STATUS_INARBITRAGE);
    }
    
    public function refund($event = 'sbr_stages.MONEY_PAID', $role = 1) {
        if(!$XACT_ID = $this->_openXact(true))
            return false;
        $version = $this->sbr->isEmp() ? $this->data['version'] : $this->data['frl_version'];
        $result = sbr_notification::sbr_add_event($XACT_ID, $this->data['sbr_id'], $this->data['id'], $event, $version, null, $role);
        
        if(!$result) {
            $this->_abortXact();
            return false;
        }

        $this->_commitXact();
        
        return true;
    }
    
    public function isTransferMoneyCompleted() {
        return (( $this->notification['ntype'] == 'sbr_stages.EMP_PAID' && $this->sbr->isEmp() ) || 
                ( $this->notification['ntype'] == 'sbr_stages.FRL_PAID' && $this->sbr->isFrl() ) || 
                $this->notification['ntype'] == 'sbr_stages.DOC_RECEIVED' ||
                ( ($this->notification['ntype'] == 'pskb.PASSED' || $this->notification['ntype'] == 'pskb.PASSED_EMP' ) && $this->sbr->isEmp() && $this->status == self::STATUS_ARBITRAGED && ($this->arbitrage['id'] > 0 && (int)$this->arbitrage['frl_percent'] != 1) ) // ����� ������� ������ ��� ������������
                );
    }
    
    /**
     * ���� �������� ��� ��� (�� �������� �������)
     * @return type 
     */
    public function isStageCompleted() {
        return ($this->status == sbr_stages::STATUS_COMPLETED || $this->status == sbr_stages::STATUS_ARBITRAGED);
    }
    
    /*
     * ������ ����� � ������� ���� � ����� ����� ����� �������� ����� ����� ��� ����.
     */
    public function isMoreActionInHeader() {
        return (( !($this->sbr->isEmp() && $this->sbr->data['emp_feedback_id'] > 0) && $this->status == sbr_stages::STATUS_ARBITRAGED && !$this->data[$this->sbr->upfx . 'feedback_id']) ||
                ( $this->notification['ntype'] == 'sbr_stages.EMP_PAID' && $this->sbr->isEmp() ) || 
                ( $this->notification['ntype'] == 'sbr_stages.FRL_PAID' && $this->sbr->isFrl() ) ||
                $this->notification['ntype'] == 'sbr_stages.DOC_RECEIVED' || 
               ($this->notification['ntype'] == 'sbr_stages.FRL_FEEDBACK' && $this->sbr->isFrl() && $this->head_docs) ||
                ( $this->data['lc_state'] == pskb::STATE_TRANS && $this->sbr->isFrl() ) ||
                ( $this->data['lc_state'] == pskb::STATE_PASSED && $this->sbr->isFrl() ) ||
                ( $this->data['lc_state'] == pskb::STATE_PASSED && $this->sbr->isEmp() && $this->status == self::STATUS_ARBITRAGED && ($this->arbitrage['id'] > 0 && (int)$this->arbitrage['frl_percent'] != 1) ));
    }
    
    public function stageWorkTimeLeft($days = false, $dates = array(), $tpl = '%s') {
        if(!$dates) {
            $dates[0] = $this->first_time ? strtotime($this->first_time) : time();
            $dates[1] = time();
        }
        if(!$dates[0]) {
            $dates[0] = $this->first_time ? strtotime($this->first_time) : time();
        }
        if(!$dates[1]) {
            $dates[1] = time();
        }
        if(!$days) $days = (int) $this->int_work_time;
        
        $is_overtime = ( ( $dates[0] + $days*3600*24 ) - $dates[1]); // ������ ���������
        $work_rem = (strtotime(date('Y-m-d', $dates[0])) + $days*3600*24 - strtotime(date('Y-m-d', $dates[1])))/3600/24;

        if($is_overtime < 0) {
            $ago = ago_pub($dates[0] + $days * 3600 * 24, ( $work_rem <= 1 ? 'ynjGi' : 'ynjG' ), $dates[1]);
            if($ago == '') $ago = '1 ������';
            $title = '������ ��������� �� ' . $tpl;
        } else {
            $ago = ago_pub($dates[0] + $days * 3600 * 24, ( $work_rem <= 1 ? 'ynjGx' : 'ynjG' ), $dates[1]);
            if($ago == '' || $ago == '1 ������') $ago = '1 ������';
            $title = ending(intval($ago), $ago == '1 ������' ? '��������' : '�������', '��������', '��������') . ' ' . $tpl;
        }
        
        return sprintf($title, $ago);
    }
    
    public static function getArbInit($stage) {
        if($stage->sbr->scheme_type == sbr::SCHEME_LC) { // ������ �������� ����� ��� � ���
            return self::$arb_new_inits;
        } else {
            return self::$arb_inits;
        }
    }
    
    public static function getArbReasons($stage) {
        if($stage->sbr->scheme_type == sbr::SCHEME_LC) { // ������ �������� ����� ��� � ���
            return self::$arb_new_reasons;
        } else {
            return self::$arb_reasons;
        }
    }
    
    public static function getArbResults($stage) {
        if($stage->sbr->scheme_type == sbr::SCHEME_LC) { // ������ �������� ����� ��� � ���
            return self::$arb_new_results;
        } else {
            return self::$arb_results;
        }
    }
    
    /**
     * ������� ������� �����
     * 
     * @global object $DB
     * @param integer $code        ��� �������
     * @param boolean $last_event  ���� true - ������� ������ ��������� �������, ���� false - ��� ������� ������� ����
     * @return type
     */
    public function removeEvent($code = false, $last_event = false) {
        global $DB;
        if(!$code) false;
        if($last_event) {
            $sql = "DELETE FROM sbr_events WHERE id = (SELECT id FROM sbr_events WHERE sbr_id = ?i AND own_id = ?i AND ev_code = ? ORDER BY id DESC LIMIT 1)";
        } else {
            $sql = "DELETE FROM sbr_events WHERE sbr_id = ?i AND own_id = ?i AND ev_code = ?";
        }
        return $DB->query($sql, $this->sbr->id, $this->id, $code);
    }
    
    /**
     * ��������� c������ ������� ������ ��������� �����
     * 
     * @global object $DB
     * @param type $type
     * @param type $role
     */
    public function updateCompleteStage($type = true, $role = 'emp') {
        global $DB;
        
        $field = $role == 'frl' ? 'frl_completed' : 'emp_completed';
        
        $update = array(
            $field => $type
        );
        
        $DB->update('sbr_stages', $update, 'id = ?', $this->id);
    }
    
    /**
     * ���������� ������� ��������� (��������� ������� is_deleted). ��� �������� ��� ���� �� ���������
     * 
     * @param array|integer $ids   ���� ��� ��������� ��. ����������.
     * @return boolean   �������?
     */
    public function changeStatusDoc($id, $status = true) {
        $status = $status ? 'true' : 'false';
        $sql = "UPDATE sbr_docs SET is_deleted = {$status} WHERE id = {$id}";
        return $this->_eventQuery($sql);
    }
    
    /**
     * �������� ���������������� ������ ��� ��������� (��������� �������)
     * 
     * @global object $DB
     * @param type $id  �� ���������
     * @return type��
     */
    public function deleteSecondDoc($id) {
        global $DB;
        if( !hasPermissions('sbr') ) { 
            return false;
        }
        return $DB->query("DELETE FROM sbr_docs WHERE id = ?", $id);
    }
    
    /**
     * ������� ���������� �������� ����������
     *  
     * @global object $DB
     * @param integer $first_doc       �� ��������� ���������, ���������������� �����������
     * @param integer $second_doc      �� ������ ���������������� ���������
     * @param smallint $type            ��� ���������
     * @return boolean
     */
    public function addDiffDocs($first_doc, $second_doc, $type) {
        global $DB;
        $sql = "INSERT INTO sbr_docs_diff (first_doc_id, second_doc_id, type, stage_id) VALUES (?, ?, ?, ?)";
        return $DB->query($sql, $first_doc, $second_doc, $type, $this->id);
    }
    
    /**
     * �������� ������ ���������������� ���������, ������� ������� � ������ ���������
     * 
     * @param array  $doc     ������ ������� ���������
     * @param string $action  �������� � ����������
     * @return boolean|integer
     */
    public function recreateDoc($doc, $action) {
        
        $doc_num = null;
        
        if($doc['id'] != $doc['first_doc_id'] && $doc['id'] != $doc['second_doc_id']) {
            $this->redate_act = date('d.m.Y', strtotime($doc['publ_time']));
        } else {
            $doc_date = $this->sbr->getDoc($doc['first_doc_id'], true, true);
            $this->redate_act = date('d.m.Y', strtotime($doc_date['publ_time']));
        }
        
        if($action == 'create') {
            if($doc['id'] != $doc['first_doc_id'] && $doc['id'] != $doc['second_doc_id']) {
                $delete = $this->changeStatusDoc($doc['id']);
                if($delete) {
                    $this->removeEvent(30, true); // ������� ������� �������� ����� ��� �� �����
                }
            }

            if($doc['id'] == $doc['second_doc_id']) {
                $sdel = $this->deleteSecondDoc($doc['id']);
                if($sdel) {
                    $this->removeEvent(29, true); // ������� ������� ���������� �����
                }
                $doc['id'] = $doc['first_doc_id'];
            }
        } else if($action == 'remove') {
            $this->removeEvent(29, true); // ������� ������� ���������� ����� ������
            $this->changeStatusDoc($doc['id'], false);
            $this->removeEvent(29, true); // ������� ������� ���������� ����� �����, �� ���� ��� ������������ ������������ � ��� ��������
            $this->deleteSecondDoc($doc['second_doc_id']);
            return true;
        }
        
        switch($doc['type']) {
            case sbr::DOCS_TYPE_ACT:
                if($this->sbr->scheme_type == sbr::SCHEME_PDRD2) {
                    $doc_file = $this->generateFrlActPdrd($doc_err, $this->redate_act);
                } elseif($this->sbr->scheme_type == sbr::SCHEME_LC) {
                    $doc_file = $this->generateCompletedAct($doc_err); // ��������� ��� ����������� �� ��������� �����.
                }
                break;
            case sbr::DOCS_TYPE_TZ_PDRD:
                $doc_file = $this->generateTzPdrd($doc_err, $this->redate_act);
                break;
            case sbr::DOCS_TYPE_WM_APPL:
                $doc_file = $this->generateFrlWMAppl($doc_err);
                break;
            case sbr::DOCS_TYPE_YM_APPL:
                $doc_file = $this->generateFrlYMAppl($doc_err);
                break;
            case sbr::DOCS_TYPE_ARB_REP:
                
                if($this->sbr->scheme_type == sbr::SCHEME_LC) {
                
                    $is_emp_arb = $this->status == sbr_stages::STATUS_ARBITRAGED && $this->arbitrage['resolved'] && floatval($this->arbitrage['frl_percent']) == 0;
                    $result = html_entity_decode($this->arbitrage['result'], ENT_QUOTES, 'cp1251');

                    if(!$is_emp_arb && (strpos($reason, 'C������� �. 9.1.2. ����������� ����������') ===  false && strpos($result, '���������� ������ ����������� ����������') ===  false)  ) {
                        $doc_file = $this->generateArbReportFrl($doc_err, $doc_num);
                    } else {
                        $doc_file = $this->generateArbReportEmp($doc_err, $doc_num);
                    }
                } elseif($this->sbr->scheme_type == sbr::SCHEME_PDRD2) {
                    if($doc['access_role'] == sbr::DOCS_ACCESS_EMP) {
                        $doc_file = $this->generateArbReportPdrdEmp($doc_err);
                    } elseif($doc['access_role'] == sbr::DOCS_ACCESS_FRL) {
                        $doc_file = $this->generateArbReportPdrdFrl($doc_err);
                    }
                }
                
                break;
        }
        
        if($doc_file) {
            $_doc = array(
                'stage_id'      => $this->id, 
                'file_id'       => $doc_file->id, 
                'num'           => $doc_num, 
                'status'        => $doc['status'], 
                'access_role'   => $doc['access_role'],
                'owner_role'    => 0, 
                'type'          => $doc['type']
            );

            $gen = $this->sbr->addDocR($_doc);
            if($gen) {
                $this->addDiffDocs($doc['id'], $gen, $doc['type']);
            }

            return $gen;
        }
    }
    
    static function isModifiedWorkTime($history) {
        foreach($history as $xact => $events) {
            if(in_array('sbr_stages.WORKTIME_MODIFIED', array_keys($events))) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * ������������� ���� �� ������� ���� ������ �� ������������ � ���������
     * @global object $DB
     * @param type $dateToAnswer
     * @return type
     */
    public function setArbitrageDateToAnswer($dateToAnswer) {
        global $DB;
        if (!$this->arbitrage) {
            $this->getArbitrage();
        }
        if (!$this->arbitrage || !$dateToAnswer) {
            return;
        }
        // ���� �� ����� ������� ����� �������� ������ ������ 300 ����, �� �� ������ ������ ���������
        if (!preg_match('~\d\d\d\d-\d\d-\d\d~', $dateToAnswer)) {
            return;
        } else {
            $dateToAnswer .= ' 23:59:59';
        }
        
        $sql = "UPDATE sbr_stages_arbitrage SET date_to_answer = ? WHERE id = ?i";
        $DB->query($sql, $dateToAnswer, $this->arbitrage['id']);
    }
    
    /**
     * ������������� ������� ��� ���������
     * @global type $DB
     * @param integer $arbitrageID ID ���������
     * @param integer $arbitrID ID �������
     */
    public static function setArbitr ($arbitrageID, $arbitrID) {
        global $DB;
        // ���������, ���� �� ����� ��������
        if ($DB->val("SELECT id FROM sbr_stages_arbitrage WHERE id = ?i", $arbitrageID)) {
            $sql = "UPDATE sbr_stages_arbitrage SET arbitr_id = ?i WHERE id = ?i";
            $DB->query($sql, $arbitrID, $arbitrageID);
        }
    }
    
    /**
     * ���������� ������ ���� �������� �� ������� sbr_stages_arbitrs
     */
    public function getArbitrs () {
        global $DB;
        $sql = "SELECT ssa.id, ssa.name FROM sbr_stages_arbitrs ssa";
        $rows = $DB->rows($sql);
        return $rows;
    }
    
    /**
     * ��������� ��� �� �������� �������� �� ���������� ������
     * ������ �����: ���� � ������ ���������� ��������� ����� "���������� ������"
     */
    public function isByConsent () {
        if (!$this->arbitrage) {
            $this->getArbitrage();
        }
        if (!$this->arbitrage) {
            return null;
        }
        $isByConsent = strpos($this->arbitrage['result'], '���������� ������') !== false;
        return $isByConsent;
    }
    
    /**
     * ��������� ��� �� �������� �������� �� ������� ���������
     * ������ �����: ���� � ������ ���������� ��������� ����� "������� ���������"
     */
    public function isByAward () {
        if (!$this->arbitrage) {
            $this->getArbitrage();
        }
        if (!$this->arbitrage) {
            return null;
        }
        $isByAward = strpos($this->arbitrage['result'], '������� ���������') !== false;
        return $isByAward;
    }
}

?>
