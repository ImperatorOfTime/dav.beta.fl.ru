<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/View.php");

class NewOrderForm extends Form_View
{
    public $filters = array(
        'StringTrim',
        'StripSlashes'
    );
    
    public $filtersAll = array(
        'StripTags',
        'StringTrim',
        'StripSlashes'        
    );    
    
    public function init()
    {
        
        $this->addElement(
           new Zend_Form_Element_Text('title', array(
               'label' => '���������',
               'required' => true,
               'padbot' => 20, // ������ �����
               'maxlength' => 60,
               'filters' => $this->filtersAll,
               'validators' => array(
                   array('StringLength',true,array('max' => 60,'min' => 4))
                ),
               'suffix' => '��� ��������� �������. ��������: ������ ��� ��������-�������� ������� ������'
        )));

        $this->addElement(
          new Zend_Form_Element_Textarea('description', array(
              'label' => '�������',
              'required' => true,
              'placeholder' => '',
              'padbot' => 20, // ������ �����
              'filters' => $this->filtersAll,
              'validators' => array(
                  array('StringLength', true, array('max' => 5000, 'min' => 4))
               ),
              'suffix' => '�������� ������� ������, ������ ������� ������.'
        )));
        
        $this->addElement(
           new Zend_Form_Element_Text('order_days', array(
               'label' => '����',
               'unit' => '����',
               'width' => 80,
               'maxlength' => 3,
               'required' => true,
               'padbot' => 20, // ������ �����
               'validators' => array(
                   array('Digits', true),
                   array('Between', true, array('max' => 365,'min' => 1))
                )
        )));
        
        $this->addElement(
           new Zend_Form_Element_Text('order_price', array(
               'label' => '������',
               'unit' => '���.',
               'width' => 80,
               'maxlength' => 7,
               'required' => true,
               'padbot' => 20, // ������ �����
               'validators' => array(
                   array('Digits', true),
                   array('Between', true, array('max' => 9999999,'min' => 300))
                )
        )));
        

        $this->addElement(
            new Zend_Form_Element_Radio('pay_type',array(
                'label' => '',
                'value' => 1,
                'required' => true,
                'attr' => array(
                    1 => 'data-show-class="#order_status_indicator_1" data-hide-class="#order_status_indicator_0"',
                    0 => 'data-show-class="#order_status_indicator_0" data-hide-class="#order_status_indicator_1"'
                ),
                'multiOptions' => array(
                    1 => '���������� ������ (� ��������������� �������) &#160;<a class="b-layout__link" href="/promo/bezopasnaya-sdelka/" target="_blank"><span class="b-shadow__icon b-shadow__icon_quest2 b-icon_top_2"></span></a>',
                    0 => '������ ������ ����������� �� ��� �������/����'
                ),
                'subTitles' => array(
                    1 => '���������� �������������� � ��������� �������� �������. �� ������������ ������ ������ �� ����� FL.ru - � �� ����������� ��� ������� �����, ���� ������ ����� ��������� ������������ ������������� ��� �� � ����.',
                    0 => '�������������� ��� ������� ����� � �������� ������. �� ���� ��������������� � ������������ � ������� � ������� ������. � �������������� ����������� ��� ���������, ��������� � ��������� � ������� ���������� ������.'
                )
            ))
        );

        
        
    }
    

}