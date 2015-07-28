<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/View.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/Spinner.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");

class CaruselForm extends Form_View
{
    public $filters = array(
        'StringTrim',
        'StripSlashes',
        //'StripTags',
        //'Htmlspecialchars',
        'Carusel'
    );
    
    //���� � ������� ���������
    protected $viewScriptPrefixPath = 'classes/Form/Templates/Horizontal';
    //���� ������� ����
    protected $viewScriptFormPrefixPath = 'templates/quick_payment/forms';
    
    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            //'PrepareElements',
            array('ViewScript', array('viewScript' => 
                $this->viewScriptFormPrefixPath . 
                '/carusel_form.phtml'))
        ));
    }    
    
    public function init()
    {
        $this->addElement(
           new Zend_Form_Element_Text('title', array(
               'placeholder' => '���������',
               'required' => true,
               //'padbot' => 20, // ������ �����
               'maxlength' => pay_place::MAX_HEADER_SIZE,
               'filters' => $this->filters,
               'validators' => array(
                   array('StringLength',true,array('max' => pay_place::MAX_HEADER_SIZE,'min' => 4))
                )
        )));       
        
        $this->addElement(
          new Zend_Form_Element_Textarea('description', array(
              'placeholder' => '����� ����������',
              'required' => true,
              //'padbot' => 20, // ������ �����
              'filters' => $this->filters,
              'validators' => array(
                  array('StringLength', true, array('max' => pay_place::MAX_TEXT_SIZE, 'min' => 4))
               )
        )));        
        
        $this->addElement(
          new Zend_Form_Element_Spinner('num', array(
              'required' => true,
              'width' => 80,
              'value' => 1,
              'max' => 99,
              'min' => 1,
              'suffix' => array('����������','����������','����������')
          ))
        );
        
        $this->addElement(
          new Zend_Form_Element_Spinner('hours', array(
              'required' => true,
              'width' => 80,
              'value' => 1,
              'max' => 99,
              'min' => 1,
              'suffix' => array('���','����','�����')
          ))
        );        
    }
    

}