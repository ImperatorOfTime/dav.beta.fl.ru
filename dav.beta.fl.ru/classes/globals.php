<?php
/**
 * ���� ���������� ��������� ������� ������������ � �������
 */
	
    /**
     * @global integer ������ �������� ���� ��� "����" �����
     */
    $rolesize = 6;
    /**
     * @global ������ �������� ���� ��� ��������� �����
     * @todo: ����� ������� ��� ������ ����������?
     */
    $tabsize = 8;
    /**
     * @global integer ������ �������� ���� ��� "��������" ����� �� ��������
     */
    $subscrsize = 16;
    /**
     * @global integer ������ �������� ���� blocks � ������� freelancer, ���������� ����� ����� ���� ����������, � ����� ���.
     */
    $blockssize = 8;
    /**
     * @global integer
     * @todo   �� ����� ��� ������������ � �������
     */
    $lgflagssize = 2;
    /**
     * @global integer ���-�� ��������� � ������� � "���������"
     */
    $msgspp = 41;
    /**
     * @global integer ���-�� �������� �� �������� (�� �������)
     */
    $prjspp = 30;
    /**
     * @global integer ���-�� ��� � ������ �� ��������
     */
    $blogspp = 20;	
    /**
     * @global integer ���-�� ����������� �� �������� � ��������
     *
     */
    define("FRL_PP", 40);
    /**
     * @global integer ���-�� ������������� �� �������� � ��������
     *
     */
    define("EMP_PP", 30);
    /**
     * @global integer ���-�� ����� �� �������� � ��������
     *
     */
    define("PRF_PP", 30);
    /**
     * @global integer �������� ����� � ��������� ��� ����� ���������
     */
    $prjs_pu = 1000;
    /**
     * @global string ������� ����� ��� �������� ����������
     */
    $frlmask = '000000';
    /**
     * @global string ������� ����� ��� �������� ������������
     */
    $empmask = '100000';	
    /**
     * @global string ������� ����� ��� ������
     */
    $adminmask = '000100';	
    /**
     * @global string ������� ����� ��� ����������
     */
    $modermask = '010000';	
    /**
     * @global string ������� ����� ��� ���������
     */
    $redactormask = '001000';
    /**
     * @global string ������ ������
     * @todo ��� ������������ �� �����
     */
    $textlength = '500';	
    /**
     * @global integer ����������� �� ������ ����� ��� ������ - ��� �����
     */
    $upload_dir_size = 104857600;
    if (!isset($rpath)) $rpath = "../";
    /**
     * @global integer  ��������� � ���������� "�������� ������"
     */
    $wrong_pass = "wrongpass.php";
    /**
     * @global resource ������� � ��
     */
    $connection = false;

    // ����� ����� �����������/��������� ���������� ���� ��� ��������������
    // � js-������� checkext() (������ ������ � /warning.js).
    /**
     * @global array ������ �������� ������ "��������"
     */
    $graf_array = array("gif", "jpg", "jpeg", "png", "swf");
    /**
     * @global array ������ �������� ������� ������
     */
    $file_array = array("zip", "rar", "mp3", "doc", "docx", "psd", "pdf", "xls", "xlsx", "rtf", "txt", "bmp");	
    /**
     * @global array ������ �������� ������ "�����"
     */
    $video_array = array("avi","flv","mp4","3gp","wmv","mpeg","mpg");
    /**
     * @global array  ������ �������� ������ "�����"
     */
    $audio_array = array("wma","ogg","wav");
    
    /**
     * @global array ������ �� ������� ����������� ����� ������.
     * 
     * ����� ����� �����������/��������� ���������� ���� ��� ��������������
     * � js-������� allowedExt (������ ������ � /warning.js).
     */    
    $disallowed_array = array( "ade", "adp", "bat", "chm", "cmd", "com", "cpl", "exe",
        "hta", "ins", "isp", "jse", "lib", "mde", "msc", "msp",
        "mst", "pif", "scr", "sct", "shb", "sys", "vb", "vbe",
        "vbs", "vxd", "wsc", "wsf", "wsh" );
    
    /**
     * @global integer ������������ ���-�� ������� �������������
     */
    $max_login_tries = 15;	
    /**
     * @global integer ����� ����
     */
    $login_wait_time = 5;	

    
    /**
     * @global integer ������������ ������ �����
     */
    $maxpw_audio = 1048576 * 2;	
    /**
     * @global integer ������������ ������ �����
     */
    $maxpw_video = 1048576 * 2; 
    
    /**
     * @global integer ������� �� ���
     */
    $norisk_service_prc = 10; 

    /**
     * @global array ������ ����� ������������� �� ������, ������� �� ��������
     */
    $ourUserLogins = array('vvvv', 'test-freelance', 'CheGevara2', 'kim-test-2', 'comedie1', 'comedie3', 'comedie5', 'testuser', 'vagavr');

    /**
     * @global array ������ ������������� ����������� ��� �����������
     */
    $disallowUserLogins = array(
                                'ms',
                                'saint-petersburg',
                                'kdar',
                                'ekburg',
                                'nsibirsk',
                                'nizhnov',
                                'sm',
                                'rost-don',
                                'vlstok',
                                'khrsk',
                                'chbinsk',
                                'kryarsk',
                                'kazn',
                                'irktsk',
                                'uf',
                                'srtov',
                                'ulyansk',
                                'pm',
                                'kemvo',
                                'vorzh',
                                'stapol',
                                'om',
                                'tmsk',
                                'rzan',
                                'vlgrad',
                                'brnl',
                                'h-m',
                                'tm',
                                'orburg',
                                'izhvsk',
                                'yavl',
                                'kgrad',
                                'tla',
                                'vdmir',
                                'kv',
                                'odssa',
                                'khkov',
                                'dtsk',
                                'd-p',
                                'lv',
                                'pt',
                                'lg',
                                'chrk',
                                'vinnca',
                                'zprozhie',
                                'nklv',
                                'sm-ua',
                                'khmeln',
                                'i-f',
                                'kirovgrad',
                                'rovno',
                                'hrson',
                                'zhtomir',
                                'ternopol',
                                'chernovcy',
                                'chrnigov',
                                'lck',
                                'mnsk',
                                'gmel',
                                'grdno',
                                'brst',
                                'almaaty',
                                'shimkent',
                                'trz',
                                'astna'
                               );
    
    /**
     * @global array ������ ������������� ������� ������ ������������ � ����������
     * 
     */
    $usersNotBeIgnored = array('administrator', 'moderation', 'moderator', 'norisk', 'admin');
    
    /**
     * @global array ��. �������������, ������� �������� ������ �����������
     */
    $aPmUserUids = array(519633, 419427, 543545);

    /**
     * @global array ������ ����� ������������� �� ������, ��������� � ��������
     */
    $ourUserLoginsInCatalog = array('clients', 'fmanager'); // ��� ��������� ��������������� ������ "ix employer/catalog"
    
    /**
     * @global array ������ UID �������������, ��������� �� ������� � ����� �� ��������� � �������������
     */
    if ( (defined('SERVER') && SERVER != 'release') || (defined('IS_LOCAL') && IS_LOCAL === TRUE) ) { // ��������
        if ( SERVER == 'local' || SERVER == 'beta' ) {
            $aContactsNoMod = array( 237871, 142409, 53791, 103 );
        }
        else {
            $aContactsNoMod = array( 235515, 226893, 53791, 142409, 419427, 103 );
        }
    }
    else { // ������
        $aContactsNoMod = array( 142409, 419427, 543545, 103, 235515, 226893, 53791 );
    }
    
    /**
     * @global array ������ �������, ������ �� ������� �� �������������� �� �������� a.php, � ���� ��������
     * @see reformat_callback($matches)
     * @see _wysiwygLinkDecodeCallback($matches)
     */
    $white_list = array( 'hh.ru', 'fl.ru', 'free-lance.ru', 'dizkon.ru' );

    /**
     * @global boolean ��������� ��������� ������������� ������ ����� �������� a.php
     * @see reformat_callback($matches)
     * @see _wysiwygLinkDecodeCallback($matches)
     */
    $disable_link_processing = false;
    
    /**
     * @global �������� ����� ���������� � JS
     */
    $JSProblemBrowser = array('Opera Mini'=>'/Opera\sMini\//');
    
    /**
     * @global ������� ������������� @see /search/
     */
    $status_users = array(
	  "<div class='b-page__desktop b-page__ipad'>��������</div><div class='b-page__iphone'>�<br>�<br>�<br>�<br>�<br>�<br>�<br>�</div>",
	  "<div class='b-page__desktop b-page__ipad'>�����</div><div class='b-page__iphone'>�<br>�<br>�<br>�<br>�</div>",
	  "<div class='b-page__desktop b-page__ipad'>����������</div><div class='b-page__iphone'>�<br>�<br>�<br>�<br>�<br>�<br>�<br>�<br>�<br>�</div>",
	  -1=>"<div class='b-page__desktop b-page__ipad'>��� �������</div><div class='b-page__iphone'>�<br>�<br>�<br> �<br>�<br>�<br>�<br>�<br>�<br>�</div>"
	  );
    
    /**
     * @global boolean ����� �� ������������ ����. �������������� ��� ������ �����-������ (��. stdf.php, reformat())
     *
     */
    define('HYPER_LINKS', TRUE);

    /**
     * @global integer ����� ����� ������������ ������� ����������� (������ ���������, ���)
     */
    define('NOTIFICATION_DELAY', 300000);
    
    /**
     * @global integer ����� ����� ������������ ������� � �������� ����� ��������� � �������������. (10 ���)
     */
    define('PRJ_CHECK_DELAY', 600000);
    
    /**
     * @global integer ��. �����, ��� �������� ��������� �������� �����, �������.
     */
    define('SPEC_USER', 12245);

    /**
     * @global string   ��� ���������� ������ links. ������ �������������� ���� ������ ������������ ����. ��������� ������.
     * @see links
     */
    define('LINK_INSTANCE_NAME', '___iLinks___');

    define('VALENTIN_DATE_BEGIN', 'Feb 14, 2012 00:00:00');
    define('VALENTIN_DATE_END', 'Feb 15, 2012 00:00:00');
    
    if ( !defined('COOKIE_SECURE') ) {
        define('COOKIE_SECURE', isset($_SERVER['HTTP_NGINX_HTTPS']));
    }
    
    $allow_love = (strtotime(VALENTIN_DATE_BEGIN) < time() && strtotime(VALENTIN_DATE_END) > time());
    
    /**
     * @global array email ������ ������� �������� ����� ���� ����� �������� ��� �������� � ������� ��������
     * @see smail::FeedbackPost
     */
    if ( (defined('SERVER') && SERVER != 'release') || (defined('IS_LOCAL') && IS_LOCAL === TRUE) ) { // ��������
        $aFeedbackPost = array(
            1 => array( 'email' => 'helpdesk_beta_1@free-lance.ru', 'subj' => '������ �� �������� �����, �������� �����' ),
            2 => array( 'email' => 'helpdesk_beta_3@free-lance.ru', 'subj' => '������ �� �����, �������� �����' ),
            3 => array( 'email' => 'helpdesk_beta_2@free-lance.ru', 'subj' => '���������� ������, �������� �����' ),
            4 => null, // ������ ������ ����������
            5 => array( 'email' => 'helpdesk_beta_5@free-lance.ru', 'subj' => '����������� ������' ),
            6 => array( 'email' => 'helpdesk_beta_1@free-lance.ru', 'subj' => '��������� ������ ����������� �� ������ ����������� � �������� �����, �������� �����' ),
            7 => array( 'email' => 'helpdesk_beta_1@free-lance.ru', 'subj' => '���� ����������� �� ��������� ������ �����, �������� �����' ),
            8 => array( 'email' => 'helpdesk_beta_1@free-lance.ru', 'subj' => '������ �� ����� �� ������� ������������' ),
            9 => array( 'email' => 'helpdesk_beta_1@free-lance.ru', 'subj' => '������ �� ���� ������� �����������' ),
            10 => array( 'email' => 'helpdesk_beta_1@free-lance.ru', 'subj' => '������ �� ���� ��������' ),
            11 => array( 'email' => 'helpdesk_beta_1@free-lance.ru', 'subj' => '�������� � ������������ ��� ������������ �� �����' )
        );
    }
    else { // ������
        $aFeedbackPost = array(
            1 => array( 'email' => 'info@free-lance.ru',    'subj' => '������ �� �������� �����, �������� �����' ),
            2 => array( 'email' => 'tester@free-lance.ru',  'subj' => '������ �� �����, �������� �����' ),
            3 => array( 'email' => 'finance@free-lance.ru', 'subj' => '���������� ������, �������� �����' ),
            4 => null, // ������ ������ ����������
            5 => array( 'email' => 'norisk@free-lance.ru',  'subj' => '����������� ������' ),
            6 => array( 'email' => 'info@free-lance.ru',    'subj' => '��������� ������ ����������� �� ������ ����������� � �������� �����, �������� �����' ),
            7 => array( 'email' => 'info@free-lance.ru',    'subj' => '���� ����������� �� ��������� ������ �����, �������� �����' ),
            8 => array( 'email' => 'help@free-lance.ru',    'subj' => '������ �� ����� �� ������� ������������' ),
            9 => array( 'email' => 'manager@free-lance.ru', 'subj' => '������ �� ���� ������� �����������' ),
            10 => array( 'email' => 'adv@free-lance.ru', 'subj' => '������ �� ���� ��������' ),
            11 => array( 'email' => 'info@free-lance.ru',    'subj' => '�������� � ������������ ��� ������������ �� �����' )
        );
    }
    
    /**
     * @global email ����� ������� ��������� (������ �����������)
     */
    $sManagerEmail = ( (defined('SERVER') && SERVER != 'release') || (defined('IS_LOCAL') && IS_LOCAL === TRUE) ) ? 'helpdesk_beta_4@free-lance.ru' : 'manager@free-lance.ru';
    
    /**
     * @global string ���������� � ����� ��������
     */
    $logoAddition = (date('dm') === "0104" || date('dmH') === "300317") ? "1april" : '';
    
    
    /**
     * @global integer ����������� ������ ����� ������������� ��������� ����� ������ ��� GET-��������.
     */
    define('VISIT_GET_UPDATE_PERIOD', 300);
    
    /**
     * @global integer ����������� ������ ����� ������������� ��������� ����� ������ ��� POST-��������.
     */
    define('VISIT_POST_UPDATE_PERIOD', 60);
    
    /**
     * @global string ������, ������� �� ������� �� ����� ������� �� ���������.
     */
    $GLOBALS['VISIT_IGNORED_URI'] = '~(?:notification\.php|iframe_[^.]+\.php|xajax/blocks\.server\.php|kword_js\.php)~';
    
    /**
     * @global array ������ ������� ����� ������ ���� ������������
     */
    $GLOBALS['balanceCanChangeAdmins'] = array('administrator', 'pppiu');
    
    /**
     * @global string ���� ������ ��� ������ �����-�������� ����������� 
     */
    define(CROSSDOMAINAUTH_KEY_NAME, 'CROSSDOMAINAUTH_');

    
    /**
     * ������ �� ������� ���� ���������� ����������� ������� � ���������� �������������
     */
    define( 'CENSORED', '[����������� ����������]' );
    

    define('HOME', realpath(__DIR__.'/../'));
    
