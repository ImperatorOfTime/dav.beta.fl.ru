<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/View.php");

class BillInvoiceForm extends Form_View
{
    //���� � ������� ���������
    //protected $viewScriptPrefixPath = 'classes/Form/Templates/Horizontal';
    //���� ������� ����
    protected $viewScriptFormPrefixPath = 'templates/quick_payment/forms';
    
    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            //'PrepareElements',
            array('ViewScript', array('viewScript' => 
                $this->viewScriptFormPrefixPath . 
                '/billinvoice_form.phtml'))
        ));
    }    
    
    public function init()
    {
        $minPrice = 300;
        if(isset($_SESSION['ac_sum']) && $_SESSION['ac_sum'] < 0) {
            $minPrice = abs($_SESSION['ac_sum']);
        }
        
        $this->addElement(
           new Zend_Form_Element_Text('sum', array(
               'label_width' => 160,
               'label' => '����� ����������',
               'unit' => '���.',
               'width' => 80,
               'maxlength' => 7,
               'required' => true,
               //'padbot' => 20, // ������ �����
               'filters' => array('StripTags'),
               'validators' => array(
                   array('Digits', true),
                   array('Between', true, array('max' => 999999,'min' => $minPrice))
                )
        )));
    }

}