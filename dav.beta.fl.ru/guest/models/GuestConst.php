<?php

/**
 * Class GuestConst
 * ��������� ������
 */
class GuestConst 
{

    const TYPE_PERSONAL_ORDER   = 10;
    const TYPE_PROJECT          = 20;
    const TYPE_VACANCY          = 25;

    const EMAIL_ERR  = 0x0001;


    protected static $_error_messages = array(
        self::EMAIL_ERR => array(
            self::TYPE_PERSONAL_ORDER => '
                �� ���� e-mail ��������������� ��� ������� ����������. <br/>
                ����� ����� ������������� � ���������� �����, ����������, ������� ������ e-mail �����.',

            self::TYPE_PROJECT => '
                �� ���� e-mail ��������������� ��� ������� ����������. <br/>
                ����� ����� ������������� � ������������ ������, ����������, ������� ������ e-mail �����.'
        )
    ); 



    const MSG_AL        = 0x1001;
    const MSG_AL_EXIST  = 0x1002;
    const MSG_SUBMIT    = 0x1003;
    const URI_CANCEL    = 0x1004;
    const FORM_ID      = 0x1005;

    const VACANCY_ACTION_NOPRO = '��������';
    const VACANCY_ACTION_PRO = '����������� � ��������'; 

    const VACANCY_EMAIL_BUSY = '
        �� ���� e-mail ��������������� ��� ������� ����������. 
        ����� ����� ������������� � ���������� ��������, ����������, ������� ������ e-mail �����.
        ';

    public static $_unsubscribe_ok_message = array(
        'title' => '��������� �����������',
        'message' => '
            �� ������� ���������� �� ����������� � ���������/�������� � ������ �� ������ �������� �� �� ����� %s'
        );


    protected static $_messages = array(

        self::FORM_ID => array(
            self::TYPE_PERSONAL_ORDER => 'new-personal-order',
            self::TYPE_PROJECT => 'new-project',
            self::TYPE_VACANCY => 'new-vacancy'
        ), 

        //��������� ����� ���������� � ������ ��� ����� ������
        self::MSG_AL => array(
            self::TYPE_PERSONAL_ORDER => array(
                   'title' => '����� ��������� �����������',
                   'message' => '
                        �� ��������� e-mail ����� ���������� ������ �� �������, 
                        �� ������� �� ������� ������������� ����������� ����������� 
                        � ������� � ������������ ���� �����.'
               ),
            self::TYPE_PROJECT => array(
                   'title' => '������ ������',
                   'message' => '
                        �� ��������� e-mail ����� ���������� ������ �� �������, 
                        �� ������� �� ������� ������������� ����������� ����������� 
                        � ������� � ��������� ������.'
               ),
            self::TYPE_VACANCY => array(
                   'title' => '�������� ���������',
                   'message' => '
                        �� ��������� e-mail ����� ���������� ������ �� �������, 
                        �� ������� �� ������� ������������� ����������� ����������� 
                        � �������� ���������� ����� ��������.'
               )
        ),

        //��������� ����� ���������� � ������ ��� ������������ ������
        self::MSG_AL_EXIST => array(
            self::TYPE_PERSONAL_ORDER => array(
                   'title' => '����� ��������� �����������',
                   'message' => '
                        �� ��������� e-mail ����� ���������� ������ �� �������, 
                        �� ������� �� ������� ������� � ������������ ���� �����.'
               ),
            self::TYPE_PROJECT => array(
                   'title' => '������ ������',
                   'message' => '
                        �� ��������� e-mail ����� ���������� ������ �� �������, 
                        �� ������� �� ������� ������������� ����������� ����������  
                        � ������� � ��������� ������.'
               ),
            self::TYPE_VACANCY => array(
                   'title' => '�������� ���������',
                   'message' => '
                        �� ��������� e-mail ����� ���������� ������ �� �������, 
                        �� ������� �� ������� %s ���������� ����� ��������.'
               )
        ),


        //����� ��� ������� ���������� �/��� �����������
        self::MSG_SUBMIT => array(
            self::TYPE_PERSONAL_ORDER => '������������������ � ���������� �����',
            self::TYPE_PROJECT => '������������������ � ������������ ������',
            self::TYPE_VACANCY => '������������������ � ���������� �������� �� %d ���'
        ),

        //������ ����� � �������
        self::URI_CANCEL => array(
            self::TYPE_PERSONAL_ORDER => '/registration/?user_action=add_order',
            self::TYPE_PROJECT => '/registration/?user_action=add_project',
            self::TYPE_VACANCY => '/registration/?user_action=add_vacancy'
        )
    );


    public static function getErrorMessage($err, $type)
    {
        return isset(self::$_error_messages[$err][$type])?
            self::$_error_messages[$err][$type]:false;
    }


    public static function getMessage($mes, $type)
    {
        return isset(self::$_messages[$mes][$type])?
            self::$_messages[$mes][$type]:false; 
    }


}