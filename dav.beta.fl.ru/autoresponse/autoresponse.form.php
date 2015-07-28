<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/View.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/Budget.php");

class AutoresponseForm extends Form_View
{
    public $filters = array(
        'StringTrim',
        'StripSlashes'
    );
    
    public function init()
    {
        $this->addElement(
            new Zend_Form_Element_Textarea('descr', array(
                    'label' => '����� ������',
                    'required' => true,
                    'placeholder' => '������ ������� ���� ������ �����������, ������� ��������������, ������� � ����������� ���������� � ��������� ����� ������� ������.',
                    'padbot' => 0, // ������ �����
                    'maxlength' => 1000,
                    'filters' => $this->filters,
                    'validators' => array(
                        array(new Zend_Validate_StringLength(array('max' => 1000)), true),
                    ),
                    'suffix' => '�� ����� 1000 ��������.'
                )
            )
        );

        $this->addElement(
            new Zend_Form_Element_Checkbox('only_4_cust', array(
                    'label'      => '������ �����, ������ ��� ������� ������ ������������ (������ �������)',
                    'required' => false,
                )
            )
        );

        $this->addElement(
            new Zend_Form_Element_Text('total', array(
                    'label' => '����������<br>�����������',
                    'width' => 80,
                    'required' => false,
                    'validators' => array(
                        array(new Zend_Validate_Int(), true),
                        array(new Zend_Validate_Between(array('min' => 1, 'max' => 100000)), true),
                    )                    
                )
            )
        );

        $this->addElement(
            new Form_Element_Budget('filter_budget', array(
                    'label' => '������ ��',
                    'width' => 80,
                    'required' => false,
                )
            )
        );
    }
}
