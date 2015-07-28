<?php

class tservices_const
{

    const CURRENCY_RUS = 0;

    static protected $_currency = array (
        self::CURRENCY_RUS => '�.',
    );


    //--------------------------------------------------------------------------
    
    
    const LABEL_TITLE           = 0;
    const LABEL_EXTRA           = 1;
    const LABEL_DISTANCE        = 2;
    const LABEL_DISTANCE_FIELD  = 3;
    const LABEL_PERSONAL_FIELD  = 4;
    const LABEL_REQUIREMENT     = 5;
    const LABEL_EXPRESS         = 6;
    const LABEL_DESCRIPTION     = 7;
    const LABEL_TAGS            = 8;
    const LABEL_CATEGORY        = 9;
    const LABEL_DAYS            = 10;
    const LABEL_UPLOADER        = 11;
    const LABEL_UPLOAD_AREA     = 12;
    const LABEL_VIDEO           = 13;

    static protected $_label = array(
        self::LABEL_TITLE           => '�������� � ��������� ������',
        self::LABEL_EXTRA           => '����������� ������, ��������� ������������� ������',
        self::LABEL_DISTANCE        => '������ ���������� ������',
        self::LABEL_DISTANCE_FIELD  => '��������',
        self::LABEL_PERSONAL_FIELD  => '�������� ������ �������',
        self::LABEL_REQUIREMENT     => '����������� �� ��������� ����������',
        self::LABEL_EXPRESS         => '���� ��������� ������ �� ��������������',
        self::LABEL_DESCRIPTION     => '��������� ��������',
        self::LABEL_TAGS            => '�������� �����',
        self::LABEL_CATEGORY        => '���������',
        self::LABEL_DAYS            => '���� ���������� ������',
        self::LABEL_UPLOADER        => '����',
        self::LABEL_UPLOAD_AREA     => '���������� ���� ����',
        self::LABEL_VIDEO           => '�������� ������ �� �����'
    );
    
    
    
    
    //--------------------------------------------------------------------------
    
    
    
    const PLACEHOLDER_REQUIREMENT = 0;
    const PLACEHOLDER_DESCRIPTION = 1;
    
    static protected $_placeholder = array(
        self::PLACEHOLDER_REQUIREMENT => '������� �� �������, ��� ������ ������������ �������� ��� ������ ������',
        self::PLACEHOLDER_DESCRIPTION => '�������� ������� ���������, ������� ������� ��������',
    );

    
    
    //--------------------------------------------------------------------------
    
    
    
    const HINT_TITLE    = 0;
    const HINT_TAGS     = 1;
    const HINT_VIDEO    = 2;
    const HINT_IMG      = 3;
    
    static protected $_hint = array(
        self::HINT_TITLE    => '��������: ������ ������� �� 2 000 �.',
        self::HINT_TAGS     => '����� ������� �� 10 ���� ����� �������',
        self::HINT_VIDEO    => '������ �� ����� � YouTube, RuTube ��� Vimeo',
        self::HINT_IMG      => '����������� ���������� 600x600 �������� � ������� jpg, jpeg, png.'
    );
    
    
    //--------------------------------------------------------------------------
    
    
    const MISC_FORM_TITLE_EDIT  = 0;
    const MISC_FORM_TITLE_NEW   = 1;
    const MISC_FORM_SUBTITLE    = 2;
    const MISC_TU_NONE          = 3;
    
    static protected $_misc = array(
        self::MISC_FORM_TITLE_EDIT   => '�������������� ������� ������',
        self::MISC_FORM_TITLE_NEW    => '�������� ������� ������ �� ���� �����',
        self::MISC_FORM_SUBTITLE     => '������� ������ � ������������� ����� �����, ������� �� ������ ��������� �� ������������� ����',
        self::MISC_TU_NONE           => '������� ����� �� �������.'
    );

    
    
    //--------------------------------------------------------------------------
    
    
    const SEO_CARD_TITLE    = 0;
    const SEO_PROF_TITLE    = 1;
    const SEO_NEW_TITLE     = 2;
    const SEO_EDIT_TITLE    = 3;
    const SEO_PROF_SHARE    = 4;
    
    static protected $_seo = array(
        self::SEO_CARD_TITLE   => '%s � ������� ������ �� FL.ru',
        self::SEO_PROF_TITLE   => '������� ������ �� FL.ru',
        self::SEO_NEW_TITLE    => '���������� ������� ������ �� FL.ru',
        self::SEO_EDIT_TITLE   => '�������������� ������� ������ �� FL.ru',
        self::SEO_PROF_SHARE   => '������� ������ � %s �� FL.ru'
    );


    
    //--------------------------------------------------------------------------

    
    
    const TIP_DAYS      = 0;
    const TIP_DISTANCE  = 1;
    const TIP_EXTRA     = 2;
    const TIP_EXPRESS   = 3;
    const TIP_EMP_ONLY  = 4;
    
    static protected $_tip = array(
        self::TIP_DAYS      => '������������ ����, � ������� �� ����� ���������',
        self::TIP_DISTANCE  => '������ �� �� ����������� � ���������� �����, ��� ������ ���������� ������ ��������?',
        self::TIP_EXTRA     => '���������� ���������� ������ � �������� ������� �������������� ����� �� ��������� ������ � �����',
        self::TIP_EXPRESS   => '������� ����� ������� �� ������� ���������� ���� ������ (�� ������ � �������������� ������)',
        self::TIP_EMP_ONLY  => '����� ������ �������� ������ �� �������� ������������'
    );


    //--------------------------------------------------------------------------
    
    
    const BUTTON_EDIT                   = 0;
    const BUTTON_SAVE                   = 1;
    const BUTTON_ADD                    = 2;
    const BUTTON_STOP_PUBLISH           = 3;
    const BUTTON_SAVE_WITHOUT_PUBLISH   = 4;
    const BUTTON_DEL                    = 5;

    static protected $_button = array(
        self::BUTTON_SAVE                   => '���������',
        self::BUTTON_ADD                    => '������������',
        self::BUTTON_EDIT                   => '�������������',
        self::BUTTON_STOP_PUBLISH           => '����� � ����������',
        self::BUTTON_SAVE_WITHOUT_PUBLISH   => '��������� ��� ����������',
        self::BUTTON_DEL                    => '������� ������'
    );



    //--------------------------------------------------------------------------
    
    const MSG_SHOW                  = 0;
    const MSG_HIDE                  = 1;
    const MSG_DELETED               = 2;
    const MSG_NEW_SAVED             = 3;
    const MSG_NEW_SAVED_PUBLISH     = 4;
    const MSG_UPDATE                = 5;
    const MSG_UPDATE_PUBLISH        = 6;
    
    static protected $_msg = array(
        self::MSG_SHOW              => '������� ������ &laquo;%s&raquo; ������������.',
        self::MSG_HIDE              => '������� ������ &laquo;%s&raquo; ����� � ����������.',
        self::MSG_DELETED           => '������� ������ &laquo;%s&raquo; �������.',
        self::MSG_NEW_SAVED         => '������� ������ &laquo;%s&raquo; ��������� ��� ����������.',
        self::MSG_NEW_SAVED_PUBLISH => '������� ������ &laquo;%s&raquo; ��������� � ������������.',
        self::MSG_UPDATE            => '������� ������ &laquo;%s&raquo; ��������� ��� ����������.',
        self::MSG_UPDATE_PUBLISH    => '������� ������ &laquo;%s&raquo; ��������� � ������������.'
    );
    
    //--------------------------------------------------------------------------
    
    static function enum($name, $const)
    {
       if(!isset(self::${'_' . $name})) return FALSE; 
       $a = self::${'_' . $name};
       return @$a[constant('self::'.strtoupper($name . '_' . $const))];
    }
    
}