<?php

/**
 * Class Form_Element
 * 
 * ������� ����� �������������� �������� �����
 */

abstract class Form_Element extends Zend_Form_Element
{
    //��������� ������ Form_View �������������� 
    //���� � ������ ��� ��������� ���������
    public $override_view_script = true;

}