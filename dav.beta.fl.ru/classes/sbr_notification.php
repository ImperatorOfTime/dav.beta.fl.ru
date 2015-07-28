<?php 

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr_stages.php';
/**
 * ����� ��� ������ � ������������ 
 */
class sbr_notification
{
    public $name;
    
    /**
     * ������ ��������� ���������� � ����������� �� ������� ����� � ����������
     * 
     * @var array array('������������� ����������' => array('����� ������', '����� ������ ��� ����������'));
     */
    static public $ico_frl = array (
        sbr_stages::STATUS_NEW => array(
            'sbr.AGREE'                    => array('b-icon_sbr_srur', 'b-layout__txt_color_c10600'),
            'sbr.DELADD_SS_AGREE'          => array('b-icon_sbr_srur', 'b-layout__txt_color_c10600'),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_sattent', ''),
            'sbr_stages.TZ_MODIFIED'       => array('b-icon_sbr_sattent', ''),
            'sbr_stages.COST_MODIFIED'     => array('b-icon_sbr_sattent', ''),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_sattent', ''),
            'sbr_stages.EMP_ROLLBACK'      => array('b-icon_sbr_sattent', ''),
            'sbr_stages.PAUSE_RESET'       => array('b-icon_sbr_sattent', ''),
            'sbr_stages.PAUSE_OVER'        => array('b-icon_sbr_sattent', ''),
            'sbr.EMP_ROLLBACK'             => array('b-icon_sbr_sattent', ''),
            'sbr.SCHEME_MODIFIED'          => array('b-icon_sbr_sattent', ''),
            'sbr.COST_SYS_MODIFIED'        => array('b-icon_sbr_sattent', ''),
            'sbr_stages.COMMENT'           => array('b-icon_sbr_scom', ''),
            'sbr.REFUSE'                   => array('b-icon_sbr_rdel', ''),
            'sbr_stages.REFUSE'            => array('b-icon_sbr_rdel', ''),
            'sbr.DELADD_SS_REFUSE'         => array('b-icon_sbr_rdel', ''),
            'sbr.CANCEL'                   => array('b-icon_sbr_rdel', ''),
            'sbr.RESERVE'                  => array('b-icon_sbr_srur', 'b-layout__txt_color_a0763b'),
        ),
        sbr_stages::STATUS_PROCESS     =>  array(
            'sbr_stages.COMMENT'           => array('b-icon_sbr_bcom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.TZ_MODIFIED'       => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.COST_MODIFIED'     => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.PAUSE_RESET'       => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.PAUSE_OVER'        => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.AGREE'             => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr.RESERVE'                  => array('b-icon_sbr_brur', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.OVERTIME'          => array('b-icon_sbr_bplay', 'b-layout__txt_color_a0763b')
        ),
        sbr_stages::STATUS_FROZEN      => array(
            'sbr_stages.COMMENT'           => array('b-icon_sbr_scom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.TZ_MODIFIED'       => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.COST_MODIFIED'     => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.AGREE'             => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b')
        ),
        sbr_stages::STATUS_INARBITRAGE  => array(
            'sbr_stages.EMP_ARB'           => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.FRL_ARB'           => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.COMMENT'           => array('b-icon_sbr_acom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_aattent', ''),
            'sbr_stages.ARB_COMMENT'       => array('b-icon_sbr_acom', 'b-layout__txt_color_a0763b')
        ),
        sbr_stages::STATUS_ARBITRAGED  => array(
            'sbr_stages.ARB_RESOLVED'      => array('b-icon_sbr_aok', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_sattent', ''),
            'sbr_stages.EMP_FEEDBACK'      => array('b-icon_sbr_acom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.FRL_FEEDBACK'      => array('b-icon_sbr_acom', 'b-layout__txt_color_a0763b')
        ),
        sbr_stages::STATUS_COMPLETED   => array(
            'sbr_stages.COMMENT'           => array('b-icon_sbr_gcom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.COMPLETED'         => array('b-icon_sbr_gattent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.EMP_FEEDBACK'      => array('b-icon_sbr_gcom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.FRL_FEEDBACK'      => array('b-icon_sbr_gattent', ''),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.DOC_RECEIVED'      => array('b-icon_sbr_gattent', ''),
          
        ),
    );
    
    /**
     * ������ ��������� ������������ � ����������� �� ������� ����� � ����������
     * 
     * @var array array('������������� ����������' => array('����� ������', '����� ������ ��� ����������'));
     */
    static public $ico_emp = array(
        sbr_stages::STATUS_NEW => array(
            'sbr.OPEN'                     => array('b-icon_sbr_stime', ''),
            'sbr.AGREE'                    => array('b-icon_sbr_srur', ''),
            'sbr_stages.AGREE'             => array('b-icon_sbr_sattent', ''),
            'sbr_stages.REFUSE'            => array('b-icon_sbr_rdel', ''),
            'sbr.DELADD_SS_AGREE'          => array('b-icon_sbr_sattent', ''),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_stime', ''),
            'sbr_stages.TZ_MODIFIED'       => array('b-icon_sbr_stime', ''),
            'sbr_stages.COST_MODIFIED'     => array('b-icon_sbr_stime', ''),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_sattent', ''),
            'sbr_stages.PAUSE_RESET'       => array('b-icon_sbr_sattent', ''),
            'sbr_stages.PAUSE_OVER'        => array('b-icon_sbr_sattent', ''),
            'sbr.SCHEME_MODIFIED'          => array('b-icon_sbr_sattent', ''),
            'sbr.COST_SYS_MODIFIED'        => array('b-icon_sbr_sattent', ''),
            'sbr_stages.COMMENT'           => array('b-icon_sbr_scom', ''),
            'sbr.REFUSE'                   => array('b-icon_sbr_rdel', ''),
            'sbr.DELADD_SS_REFUSE'         => array('b-icon_sbr_sattent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.REFUSE'            => array('b-icon_sbr_sattent', 'b-layout__txt_color_a0763b'),
            'sbr.CANCEL'                   => array('b-icon_sbr_rdel', ''),
            'sbr.RESERVE'                  => array('b-icon_sbr_sattent', ''),
            'pskb.FORM'                    => array('b-icon_sbr_sattent', ''),
            'pskb.NEW'                     => array('b-icon_sbr_sattent', ''),
            'pskb.EXP'                     => array('b-icon_sbr_sattent', ''),
            'pskb.EXP_EXEC'                => array('b-icon_sbr_sattent', ''),
            'pskb.EXP_END'                 => array('b-icon_sbr_sattent', ''),
        ),
        sbr_stages::STATUS_PROCESS     =>  array(
            'sbr_stages.COMMENT'           => array('b-icon_sbr_bcom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.STARTED_WORK'      => array('b-icon_sbr_bplay', 'b-layout__txt_color_a0763b'),
            'sbr_stages.REFUSE'            => array('b-icon_sbr_battent', ''),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_battent', ''),
            'sbr_stages.TZ_MODIFIED'       => array('b-icon_sbr_battent', ''),
            'sbr_stages.COST_MODIFIED'     => array('b-icon_sbr_battent', ''),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_battent', ''),
            'sbr_stages.PAUSE_RESET'       => array('b-icon_sbr_battent', ''),
            'sbr_stages.PAUSE_OVER'        => array('b-icon_sbr_battent', ''),
            'sbr.AGREE'                    => array('b-icon_sbr_battent', ''),
            'sbr.RESERVE'                  => array('b-icon_sbr_battent', ''),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.OVERTIME'          => array('b-icon_sbr_bplay', 'b-layout__txt_color_a0763b'),
            'pskb.EXP'                     => array('b-icon_sbr_sattent', ''),
            'pskb.EXP_EXEC'                => array('b-icon_sbr_sattent', ''),
            'pskb.EXP_END'                 => array('b-icon_sbr_sattent', ''),
        ),
        sbr_stages::STATUS_FROZEN      => array(
            'sbr_stages.COMMENT'           => array('b-icon_sbr_scom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.TZ_MODIFIED'       => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.COST_MODIFIED'     => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.AGREE'             => array('b-icon_sbr_spause', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b')
        ),
        sbr_stages::STATUS_INARBITRAGE  => array(
            'sbr_stages.FRL_ARB'           => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.EMP_ARB'           => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_COMMENT'       => array('b-icon_sbr_acom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.WORKTIME_MODIFIED' => array('b-icon_sbr_aattent', ''),
            'sbr_stages.COMMENT'           => array('b-icon_sbr_acom', 'b-layout__txt_color_a0763b')
        ),
        sbr_stages::STATUS_ARBITRAGED  => array(
            'sbr_stages.ARB_RESOLVED'      => array('b-icon_sbr_aok', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_sattent', ''),
            'sbr_stages.FRL_FEEDBACK'      => array('b-icon_sbr_acom', 'b-layout__txt_color_a0763b')
        ),
        sbr_stages::STATUS_COMPLETED   => array(
            'sbr_stages.COMMENT'           => array('b-icon_sbr_gcom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.COMPLETED'         => array('b-icon_sbr_gattent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.FRL_FEEDBACK'      => array('b-icon_sbr_gcom', 'b-layout__txt_color_a0763b'),
            'sbr_stages.STATUS_MODIFIED'   => array('b-icon_sbr_battent', 'b-layout__txt_color_a0763b'),
            'sbr_stages.ARB_CANCELED'      => array('b-icon_sbr_avesy', 'b-layout__txt_color_a0763b'),
            'pskb.EXP'                     => array('b-icon_sbr_sattent', ''),
            'pskb.EXP_EXEC'                => array('b-icon_sbr_sattent', ''),
            'pskb.EXP_END'                 => array('b-icon_sbr_sattent', ''),
            'pskb.PASSED'                  => array('b-icon_sbr_gattent', ''),
            'pskb.PASSED_EMP'              => array('b-icon_sbr_gattent', '')
            
        ),
    );
    
    /**
     * �������� ����������
     * @var array (0 - ���������� ��� ������������, 1 - ���������� ��� �����������, 2 - ��� ����� �����)  
     */
    static public $notification = array(
        // ������ �����������
        'sbr_stages.EMP_FEEDBACK'       => array(false, '�������� ������� ��� �����', '�������� ������� �����'),
        'sbr_stages.EMP_ARB'            => array(false, '�������� ��������� � ��������', '�������� ��������� � ��������'),
        'sbr_stages.DOC_RECEIVED'       => array(false, '���� �������'),
        'sbr_stages.FRL_PAID'           => array(false, '���� �������'),
        'sbr_stages.MONEY_PAID'         => array(false, '������� ��������'),
        // ������ ���������
        'sbr.DELADD_SS_REFUSE'          => array('����������� ��������� �� ���������', false, '����������� ��������� �� ���������'),
        'sbr.OPEN'                      => array('�������� �������� �����������', false, '�������� �������� �����������'),
        'sbr_stages.STARTED_WORK'       => array('����������� ��������� � ������', '�� ���������� � ������', '����������� ��������� � ������'),
        'sbr_stages.REFUSE'             => array('����������� ��������� �� ���������', false, '����������� ��������� �� ���������'),
        'sbr_stages.FRL_ARB'            => array('����������� ��������� � ��������', false, '����������� ��������� � ��������'),
        'sbr_stages.EMP_PAID'           => array('�������� ���������� ������ ������������', false, '���� ������� ���������'),
        'sbr_stages.EMP_MONEY_REFUNDED' => array('������ ����������, �������� �����������', false, '������ ����������, �������� ������� �����������'),
        'pskb.FORM'                     => array('���� �������� ���������� ', false, '���� �������� ���������� ���������'),
        'pskb.NEW'                      => array('�������� ����������� �����', false, '�������� ������� ����������� �����'),
        'pskb.EXP'                      => array('������ �� ��������� � ����. ������ �� ������', false, '������ �� ��������� � ����. ������ �� ������'),
        'pskb.EXP_EXEC'                 => array('����������� �� ����� ���������. ������ ������������ ���������', false, '����������� �� ����� ���������. ������ ������������ ���������'),
        'pskb.EXP_END'                  => array('���� ����������� �����. ������ ������������ ���������', false, '���� ����������� �����. ������ ������������ ���������'),
        'pskb.PASSED'                   => array('�������� ������������� ������������ ��������� �����', '�������� ���� �������������', '�������� ������������� ������������ ��������� �����'),
        'pskb.PASSED_EMP'               => array('�������� ������������� ������������ �������� �����', '�������� ���� �������������', '�������� ������������� ������������ �������� �����'),
        // �����
        'sbr_stages.OVERTIME'           => array('����� �� ���� �������', '����� �� ���� �������', '����� �� ���� �������'),
        'sbr.AGREE'                     => array('����������� ���������� �� ������', '�������� ��� �� �������������� ������!', '�������� ��� �� �������������� ������!'),
        'sbr.DELADD_SS_AGREE'           => array('����������� ���������� � �����������', '�������� ��� �� �������������� ������!', '�������� ��� �� �������������� ������!'),
        'sbr_stages.AGREE'              => array('����������� ���������� � �����������', '�� ����������� � �����������', '����������� ���������� � �����������'),
        'sbr_stages.WORKTIME_MODIFIED'  => array('�������� �������� �����������', '�������� ����� �������� ������� �����', '�������� ����� �������� ������� �����'),
        'sbr_stages.TZ_MODIFIED'        => array('�������� �������� �����������', '�������� ����� �������� ������� �����', '�������� ����� �������� ������� �����'),
        'sbr_stages.COST_MODIFIED'      => array('�������� �������� �����������', '�������� ����� �������� ������� �����', '�������� ����� �������� ������� �����'),
        'sbr_stages.STATUS_MODIFIED'    => array('�������� �������� �����������', '�������� ����� �������� ������ �����', '�������� ����� �������� ������ �����'),
        'sbr_stages.PAUSE_RESET'        => array('����� �������� (�� ������������ � ����)', '����� �������� (�� ������������ � ����)', '����� �������� (�� ������������ � ����)'),
        'sbr_stages.PAUSE_OVER'         => array('���� ����� ��������', '���� ����� ��������', '���� ����� ��������'),
        'sbr.SCHEME_MODIFIED'           => array('�������� �������� �����������', '�������� ����� �������� ������� ������', '�������� ����� �������� ������� ������'),
        'sbr.COST_SYS_MODIFIED'         => array('�������� �������� �����������', '�������� ����� �������� ������� ������', '�������� ����� �������� ������� ������'),
        'sbr.EMP_ROLLBACK'              => array('�������� �������� �����������', '�������� ����� �������� ������� ������', '�������� ����� �������� ������� ������'),
        'sbr_stages.EMP_ROLLBACK'       => array('�������� �������� �����������', '�������� ����� �������� ������� �����', '�������� ����� �������� ������� �����'),
        'sbr_stages.COMMENT'            => array('����������� ������� �����������', '�������� ������� �����������'),
        'sbr.REFUSE'                    => array('����������� ��������� �� ������', '�� ���������� �� ������', '����������� ��������� �� ������'),
        'sbr.CANCEL'                    => array('�� �������� ������', '������ �������� ����������', '������ �������� ����������'),
        'sbr.RESERVE'                   => array('���� ��������� � �������', '�������� �������������� ������', '�������� �������������� ������'),
        'sbr_stages.COMPLETED'          => array('', '�������� �������� ����', '�������� �������� ����'),
        'sbr_stages.FRL_FEEDBACK'       => array('����������� ������� ��� �����', '�������� ������������ ����', '����������� ������� �����'),
        'sbr_stages.ARB_COMMENT'        => array('�������� ������� �����������', '�������� ������� �����������', '�������� ������� �����������'),
        'sbr_stages.ARB_RESOLVED'       => array('�������� ����� ������������� �������', '�������� ����� ������������� �������', '�������� ����� ������������� �������'),
        'sbr_stages.ARB_CANCELED'       => array('������������� ������� ��������', '������������� ������� ��������', '������������� ������� ��������')
    );
    
    /**
     * �� ����� �������� ������ ���� ������� �� ������������
     * 
     * @var array (0 - ������� ������ ���� �� ������������, 1 - ������� ������ ���� �� ����������) 
     */
    static public $reaction = array(
        // �����
        'sbr_stages.COMMENT'            => array(true, true),
        'sbr_stages.COMPLETED'          => array(true, true),
        'sbr_stages.ARB_COMMENT'        => array(true, true),
        'sbr_stages.ARB_RESOLVED'       => array(true, true),
        'sbr_stages.ARB_CANCELED'       => array(true, true),
        'sbr_stages.OVERTIME'           => array(true, true),
        'sbr_stages.PAUSE_RESET'        => array(true, true),
        'sbr_stages.PAUSE_OVER'         => array(true, true),
        // ������������
        'sbr.AGREE'                     => array(true, false),
        'sbr_stages.AGREE'              => array(true, false),
        'sbr.DELADD_SS_AGREE'           => array(true, false),
        'sbr.REFUSE'                    => array(true, false),
        'sbr_stages.STARTED_WORK'       => array(true, false),
        'sbr.REFUSE'                    => array(true, false),
        'sbr_stages.FRL_FEEDBACK'       => array(true, false),
        'sbr.DELADD_SS_REFUSE'          => array(true, false),
        'sbr_stages.REFUSE'             => array(true, false),
        'sbr_stages.FRL_ARB'            => array(true, false),
        // ����������
        'sbr_stages.WORKTIME_MODIFIED'  => array(false,true),
        'sbr_stages.TZ_MODIFIED'        => array(false,true),
        'sbr_stages.COST_MODIFIED'      => array(false,true),
        'sbr.SCHEME_MODIFIED'           => array(false,true),
        'sbr.COST_SYS_MODIFIED'         => array(false,true),
        'sbr.CANCEL'                    => array(false,true),
        'sbr.RESERVE'                   => array(false,true),
        'sbr_stages.EMP_FEEDBACK'       => array(false,true),
        'sbr.EMP_ROLLBACK'              => array(false,true),
        'sbr_stages.EMP_ROLLBACK'       => array(false,true),
        'sbr_stages.EMP_ARB'            => array(false,true)
    );
    
    /**
     * �������� � �������
     * @var array (0 - ������� ��� ������������, 1 - ������� ��� �����������)  
     */
    static public $history = array (
        'sbr.SCHEME_MODIFIED'           => array('�� �������� ��� ��������.', '�������� ������� ��� ��������.', '�������� ������� ��� ��������.'),
        'sbr_stages.ADD_DOC'            => array('�������� ��������.', '�������� ��������.', '�������� ��������.'),
        'sbr.ADD_DOC'                   => array('�������� ��������.', '�������� ��������.', '�������� ��������.'),
        'sbr.OPEN'                      => array('�� �������� ������ �� ������.', '�������� ������� ��� ������ �� ������.', '�������� ������� ������ �� ������.'),
        'sbr.REOPEN'                    => array('�� ����� �������� ������ �� ������.', '�������� ����� ������� ��� ������ �� ������.', '�������� ����� ������� ������ �� ������.'), // ������ ������������
        'sbr.COST_SYS_MODIFIED'         => array('�� ������ �������� ������� �����.', '�������� ����� �������� ������� ����� (� ������������ � �������� 5 <a class="b-layout__link" href="link_offer_lc">��������</a>).', '�������� ����� �������� ������� �����.'),
        'sbr.DELADD_SS_REFUSE'          => array('����������� ��������� �� ����� �������.', '�� ���������� �� ����� �������.', '����������� ��������� �� ����� �������.'),
        'sbr.DELADD_SS_AGREE'           => array('����������� ���������� � ������ ���������.', '�� ����������� � ������ ���������.', '����������� ���������� � ������ ���������.'),
        'sbr_stages.COST_MODIFIED'      => array('�� ������ �������� ������� �����.', '�������� ����� �������� ������� ����� (� ������������ � �������� 5 <a class="b-layout__link" href="link_offer_lc">��������</a>).', '�������� ����� �������� ������� �����.'),
        'sbr_stages.TZ_MODIFIED'        => array('�� ������ �������� ������� �����.', '�������� ����� �������� ������� ����� (� ������������ � �������� 5 <a class="b-layout__link" href="link_offer_lc">��������</a>).', '�������� ����� �������� ������� �����.'),
        'sbr_stages.STATUS_MODIFIED'    => array('�� ������ �������� ������ �����.', '�������� ����� �������� ������ ����� (� ������������ � �������� 5 <a class="b-layout__link" href="link_offer_lc">��������</a>).', '�������� ����� �������� ������ �����.'),
        'sbr_stages.PAUSE_RESET'        => array('����� �������� (�� ������������ � ����)', '����� �������� (�� ������������ � ����)', '����� �������� (�� ������������ � ����)'),
        'sbr_stages.PAUSE_OVER'         => array('���� ����� ��������', '���� ����� ��������', '���� ����� ��������'),
        'sbr_stages.STATUS_MODIFIED_OK' => array('�� �������� ������ �����.', '�������� ������� ������ ����� (� ������������ � �������� 8.2, 8.3 <a class="b-layout__link" href="/offer_lc.pdf">��������</a>).', '�������� ������� ������ �����.'),
        'sbr_stages.STATUS_MODIFIED_OK_NEW_CONTRACT' => array('�� �������� ������ �����.', '�������� ������� ������ ����� (� ������������ � �������� 8.2 - 8.4 <a class="b-layout__link" href="link_offer_lc">��������</a>).', '�������� ������� ������ �����.'),
        'sbr_stages.WORKTIME_MODIFIED'  => array('�� ������ �������� ������� �����.', '�������� ����� �������� ������� ����� (� ������������ � �������� 5 <a class="b-layout__link" href="link_offer_lc">��������</a>).', '�������� ����� �������� ������� �����.'),
        'sbr.AGREE'                     => array('����������� ���������� �� ������.', '�� ����������� �� ������%s.', '����������� ���������� �� ������.'),
        'sbr.REFUSE'                    => array('����������� ��������� �� ������.', '�� ���������� �� ������.', '����������� ��������� �� ������.'),
        'sbr_stages.AGREE'              => array('����������� ���������� � �����������.', '�� ����������� � �����������.', '����������� ���������� � �����������.'),
        'sbr_stages.REFUSE'             => array('����������� ��������� �� ���������%s.', '�� ���������� �� ���������%s.', '����������� ��������� �� ���������%s.'),
        'sbr_stages.EMP_ROLLBACK'       => array('�� �������� ��������� ��������� �����.', '�������� �������� �������� ������� �����.', '�������� �������� �������� ������� �����.'),
        'sbr.RESERVE'                   => array('�� ��������������� ������%s.', '�������� �������������� ������.', '�������� �������������� ������.'),
        'sbr_stages.EMP_ARB'            => array('�� ���������� � ��������.', '�������� ��������� � �������� (� ������������ � ������� 9.1 <a class="b-layout__link" href="link_offer_lc">��������</a>).', '�������� ��������� � ��������.'),
        'sbr_stages.FRL_ARB'            => array('����������� ��������� � �������� (� ������������ � ������� 9.1 <a class="b-layout__link" href="link_offer_lc">��������</a>).', '�� ���������� � ��������.', '����������� ��������� � ��������.'),
        'sbr_stages.ARB_RESOLVED'       => array('�������� ����� ������������� ������� (� ������������ � ������� %s <a class="b-layout__link" href="link_offer_lc">��������</a>).', '�������� ����� ������������� ������� (� ������������ � ������� %s <a class="b-layout__link" href="link_offer_lc">��������</a>).', '�������� ����� ������������� �������.'),
        'sbr.CANCEL'                    => array('�� �������� ������.', '�������� ������� ������.', '�������� ������� ������.'),
        'sbr_stages.FRL_FEEDBACK'               => array('����������� ������� ��� %s (� ������������ � �������� 4 <a class="b-layout__link" href="/about/appendix_2_regulations.pdf">����������������� ����������</a>).', '�� �������� ��������� %s.', '����������� ������� %s.'),
        'sbr_stages.FRL_FEEDBACK_NEW_CONTRACT'  => array('����������� ������� ��� %s (� ������������ � ����������� �2 � <a class="b-layout__link" href="/about/appendix_2_regulations.pdf">����������������� ����������</a>).', '�� �������� ��������� %s.', '����������� ������� %s.'),
        'sbr_stages.EMP_FEEDBACK'               => array('�� �������� ����������� %s.', '�������� ������� ��� %s (� ������������ � �������� 4 <a class="b-layout__link" href="/about/appendix_2_regulations.pdf">����������������� ����������</a>).', '�������� ������� %s.'),
        'sbr_stages.EMP_FEEDBACK_NEW_CONTRACT'  => array('�� �������� ����������� %s.', '�������� ������� ��� %s (� ������������ � ����������� �2 � <a class="b-layout__link" href="/about/appendix_2_regulations.pdf">����������������� ����������</a>).', '�������� ������� %s.'),
        'sbr_stages.STARTED_WORK'       => array('����������� ��������� � ������.', '�� ���������� � ������.', '����������� ��������� � ������.'),
        'sbr_stages.ARB_CANCELED'       => array('�������� �������.', '�������� �������.', '�������� �������.'),
        'sbr_stages.EMP_MONEY_REFUNDED' => array('%s', '', '%s'),
        'sbr_stages.MONEY_PAID'         => array('', '%s', '%s'),
        'sbr_stages.COMMENT'            => array('', ''),
        'sbr_stages.ARB_COMMENT'        => array('', ''),
        'sbr.COMPLETED'                 => array('������ ���������', '������ ���������', '������ ���������'),
        'sbr_stages.OVERTIME'           => array('�����, ���������� ���� �� ���������� ����� ����� ������, �������. �� %s,  �� ������ ������� ������� � ���, ��� ������ � ������������ ������ �� ����� �����:', '�����, ���������� ���������� �� ���������� ����� ����� ������, �������.', '�����, ���������� ���������� �� ���������� ����� ����� ������, �������.'),
        'sbr_stages.DOCS_NOTE'          => array('', '', ''),
    );
    
    /**
     * ���������� �������� � ������� � ���������� �� ���� ������� � ����
     * 
     * @param string  $arb             ��� �������
     * @param boolean $role            ���� (true - ��������, false - �����������) @see sbr::isEmp();
     * @param string  $str_additional  �������������� ������ @see self::$history['sbr_stages.FRL_FEEDBACK']
     * @return string �������� ������� 
     */
    public function getHistoryName($arb, $role, $str_additional = "") {
        return sprintf(self::$history[$arb][$role], $str_additional);
    }
    
    /**
     * ���������� ��������� ���������� �� ����� (��� ���)
     * 
     * @global object $DB ����������� � ��
     * 
     * @param integer $sbr_id  �� ���
     * @param integer $own_id  �� ������ @see ������� sbr_events
     * @param string  $level   ���������� ��� ���� ���������� (sbr_stages - ���������� �� ����, sbr - ���������� �� ��� ������)  
     * @param inetegr $uid     �� ������������
     * @return array
     */
    public function getNotification($sbr_id, $own_id, $level = 'sbr_stages', $uid = false) {
        global $DB;
        
        if(is_emp()) {
            $where = " AND se.estatus IS NULL";
        } else {
            $where = " AND se.fstatus IS NULL";
        }
        
        $sql = "(SELECT sec.*, (sec.own_rel || '.' || sec.abbr) as ntype, se.id as evnt, se.xact_id FROM sbr_events se
                INNER JOIN sbr_ev_codes sec ON sec.id = se.ev_code
                WHERE se.sbr_id = ?i AND se.own_id = ? AND sec.own_rel = ? {$where} ORDER BY se.id DESC LIMIT 1)
                
                UNION 

                (SELECT sec.*, (sec.own_rel || '.' || sec.abbr) as ntype, se.id as evnt, se.xact_id FROM sbr_events se
                INNER JOIN sbr_ev_codes sec ON sec.id = se.ev_code
                INNER JOIN sbr_stages_msgs ssm ON ssm.id = se.own_id
                WHERE se.sbr_id = ?i  AND ssm.stage_id = ? AND sec.own_rel = 'sbr_stages' AND sec.abbr IN ('COMMENT', 'ARB_COMMENT') {$where} ORDER BY se.id DESC LIMIT 1)
                
                ORDER BY evnt DESC 
                LIMIT 1";
        
        $result = $DB->row($sql, $sbr_id, $level == 'sbr_stages' ? $own_id : $sbr_id, $level, $sbr_id, $own_id);
        if($result['abbr'] == 'ADD_DOC' || $result['abbr'] == 'DEL_DOC') return false;
        if(!$result && $level == 'sbr_stages') {
            return self::getNotification($sbr_id, $own_id, 'sbr', $uid);
        } else if(!$result && $level == 'sbr_stages') {
            return false;
        }
        return $result;
    }
    
    /**
     * ����� ���������� ��������� �� ����� � ��� �� �������
     * 
     * @global object $DB ����������� � ��
     *   
     * @param integer $sbr_id  �� ���
     * @param integer $own_id  �� ������ @see ������� sbr_events
     * @param integer $ev_code �� �������  
     * @return string 
     */
    public function getNotificationsForStage($sbr_id, $own_id, $ev_code) {
        global $DB;
        $sql = "SELECT se.*, sx.xtime
                FROM sbr_events se 
                INNER JOIN sbr_xacts sx ON sx.id = se.xact_id
                WHERE se.sbr_id = ?i AND se.own_id = ?i AND se.ev_code = ?i
                ORDER BY se.id DESC";
        
        return $DB->rows($sql, $sbr_id, $own_id, $ev_code);
        
    }
    
    /**
     * ���� �� ����������� ������������ �� ����������
     * @param array $notification    ���������� @see self::getNotification();
     * @return boolean
     */
    public static function isReaction($notification) {
        return (is_emp() ? self::$reaction[$notification['ntype']][0] : self::$reaction[$notification['ntype']][1]);
    }
    
    /**
     * ����� �� ����� �������
     * 
     * @global object $DB ����������� � ��
     * 
     * @param array $list  ���������� ���� ������� @see self::parseEventName();
     * @return array 
     */
    public function getEventCode($list) {
        global $DB;
        if(!$list) return array();
        
        foreach($list as $own=>$abbr) {
            $str_abbr  = implode("', '", $abbr);
            $where[] = " ( sec.own_rel = '$own' AND sec.abbr IN ('{$str_abbr}') )";
        }
        
        $where = implode(" OR ", $where);
        
        $sql = "SELECT * FROM sbr_ev_codes sec WHERE {$where}";
        return $DB->cache(1800)->rows($sql, $own, $abbr);
    }
    
    /**
     * ������ ��������� ���� ������� 
     *  
     * @param string $name    ������� @example "sbr.AGREE" (��� sbr - ��� �������, AGREE - ���������� ��� �������)
     * @return array    array['��� �������'] = array('���������� ���', '���������� ���', ...);
     */
    public function parseEventName($name) {
        if(is_array($name)) {
            foreach($name as $k=>$val) {
                list($own, $abbr) = explode(".", $val);
                $result[$own][] = $abbr;
            }
        } else {
            list($own, $abbr) = explode(".", $name);
            $result[$own][] = $abbr;
        }
        
        return $result;
    }
    
    /**
     * ����� ��������� �������� ����������
     * 
     * @global object $DB ����������� � ��
     * 
     * @param inetger $sbr_id      �� ���
     * @param integer $stage_id    �� ����� ���
     * @return array - �� ���������� ����������
     */
    public function getNotificationActive($sbr_id, $stage_id) {
        global $DB;
        if(is_emp()) {
            $where = " AND se.estatus IS NULL";
        } else {
            $where = " AND se.fstatus IS NULL";
        }
        
        if(hasPermissions('sbr')  && $_SESSION['access']=='A') {
            $where = " AND (se.estatus IS NULL OR se.fstatus IS NULL)";
        }
        
        $sql = "(SELECT se.xact_id FROM sbr_events se
                INNER JOIN sbr_ev_codes sec ON sec.id = se.ev_code
                WHERE se.sbr_id = ?i {$where} ORDER BY se.id DESC)
                
                UNION 

                (SELECT se.xact_id FROM sbr_events se
                INNER JOIN sbr_ev_codes sec ON sec.id = se.ev_code
                INNER JOIN sbr_stages_msgs ssm ON ssm.id = se.own_id
                WHERE se.sbr_id = ? AND ssm.stage_id = ? AND sec.own_rel = 'sbr_stages' AND sec.abbr IN ('COMMENT', 'ARB_COMMENT') {$where} ORDER BY se.id DESC)
                
                ORDER BY xact_id DESC";
                
        return $DB->col($sql, $sbr_id, $sbr_id, $stage_id);
    }
    
    /**
     * ��������� ���������� � ������ ����������� � ������������� ��������� (��� ������ ��� ��� �� ����� ������� ����� ������� self::getNotification())
     * 
     * @global object $DB ����������� � ��
     * 
     * @param inetger $sbr_id      �� ���
     * @param integer $stage_id    �� ����� ���
     * @return boolean 
     */
    public function setNotificationCommentViewCompleted($sbr_id, $stage_id) {
        global $DB;
        
        $where = is_emp() ? " AND se.estatus IS NULL" : " AND se.fstatus IS NULL";
        $name_fld = (is_emp() ? 'estatus': 'fstatus');
        
        $sql = "UPDATE sbr_events SET {$name_fld} = true WHERE xact_id IN 
                (SELECT se.xact_id FROM sbr_events se
                INNER JOIN sbr_ev_codes sec ON sec.id = se.ev_code
                INNER JOIN sbr_stages_msgs ssm ON ssm.id = se.own_id
                WHERE se.sbr_id = ?i AND ssm.stage_id = ?i AND sec.own_rel = 'sbr_stages' AND sec.abbr IN ('COMMENT', 'ARB_COMMENT') {$where})";
        
        return $DB->query($sql, $sbr_id, $stage_id);
    }
    
    /**
     * ��������� ���������� ���������� � ������������ ��������� (��� ������ ��� ��� �� ����� ������� ����� ������� self::getNotification())
     * 
     * @global object $DB ����������� � ��
     * 
     * @param array|string $name    �������� ���������� @example sbr.AGREE ��� array('sbr.AGREE', 'sbr.OPEN');
     * @param integer $sbr_id       �� ������
     * @param inetger $own_id       �������������� �� (����� ���� ����� �� ������ ��� �� �����) @see ������� sbr_events
     */
    public function setNotificationCompleted($name, $sbr_id, $own_id) {
        global $DB;
        
        $event_name = self::parseEventName($name);
        $ev_codes   = array_map(create_function('$res', 'return $res["id"];'), self::getEventCode($event_name));
        
        $status = (is_emp() ? 'estatus': 'fstatus');
        $update[$status] = true;
        
        if(count($ev_codes) > 0) {
            $DB->update("sbr_events", $update, "own_id = ? AND sbr_id =? AND ev_code IN (?l) AND {$status} IS NOT true", $own_id, $sbr_id, $ev_codes);
        }
    }
    
    public function setNotificationCompletedAdmin($xact_id) {
        global $DB;
        $update = array('estatus' => true, 'fstatus' => true);
        return $DB->update("sbr_events", $update, "xact_id = ? AND (estatus IS NOT true OR fstatus IS NOT true) ", $xact_id);
    }
    
    public function sbr_add_event($XACT_ID, $sbr_id, $own_id, $abbr, $version, $foronly = null, $role = null) {
        global $DB;
        $sql = "SELECT sbr_add_event({$XACT_ID}, {$sbr_id}, {$own_id}, sbr_evc('{$abbr}'), {$version}, ?, ?i); ";
        return $DB->mquery($sql, $foronly, $role);
    }
    
    static public function getNotificationName($abbr, $type, $stages) {
        if($abbr == 'sbr_stages.FRL_FEEDBACK' && $type == 1 && !$stages->head_docs) {
            return '';
        }
        return self::$notification[$abbr][$type];
    }
}
?>