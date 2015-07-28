<?php

require_once('GuestForm.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/MultiDropdown.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/BudgetExt.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/GuestProjectUploader.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/Hidden.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Validate/CostOrAgreementRequired.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Validate/UrlInvited.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");

/**
 * Class GuestNewVacancyForm
 * ����� ����� ��������
 */
class GuestNewVacancyForm  extends GuestForm
{
    /**
     * ������������� �����
     */
    public function init()
    {
        $this->addElement(
           new Zend_Form_Element_Text('name', array(
               'label' => '�������� ��������',
               'required' => true,
               'placeholder' => '���� �� ����� � ����� ������ ����� ���������.',
               'padbot' => 30, // ������ �����
               'maxlength' => 60,
               'filters' => $this->filtersAll,
               'validators' => array(
                   array('StringLength',true,array('max' => 60,'min' => 4))
                )
        )));  
        

        if ($this->isAdm()) {
            $this->addElement(
                new Zend_Form_Element_Text('link', array(
                    'label' => '������ �� ��������',
                    'required' => true,
                    'padbot' => 30, // ������ �����
                    'filters' => $this->filters,
                    'validators' => array(
                        array('StringLength',true,array('min' => 4)),
                        array(new Form_Validate_UrlInvited(array('type' => GuestConst::TYPE_VACANCY)), true)
                     )
                ))
            );
        }
        
        $this->addElement(
          new Zend_Form_Element_Textarea('descr', array(
              'label' => '�������� ������� �������',
              'required' => true,
              'placeholder' => '������� ���������� � ����������� � ����������, ����� ���������� � ������ ������� ������.',
              'padbot' => 5, // ������ �����
              'filters' => $this->filtersAll,
              'validators' => array(
                  array('StringLength', true, array('max' => 5000, 'min' => 4))
               )
        )));        
        

        //@todo: ������� ������� ����������
        $this->addElement(
          new Form_Element_GuestProjectUploader('IDResource' , array(
              'hide_label' => true,
              'label' => '�����',
              'padbot' => 30 // ������ �����
          ))
        ); 
        
        
        $this->addElement(
          new Form_Element_ProfessionsDropdown('profession', array(
              'padbot' => 30, // ������ �����
              'label' => '������������� �������',
              'required' => true,
              'class'       => 'b-combo__input_width_320',
              'spec_class'  => 'b-combo__input_width_300',
              'sort_type'   => 'sort_cnt',
              //���� ����� �� ���������
              /*
              'value' => array(
                  'group_db_id' => 3,
                  'group' => '������',
                  'spec_db_id' => 46,
                  'spec' => '��������'),
               */
              'placeholder' => '�������� ������',
              'spec_placeholder' => '�������� ������������� (�� �����������)'
          ))
        );
        
        
        $this->addElement(
          new Form_Element_MultiDropdown('location', array(
              'padbot' => 30, // ������ �����
              'label' => '����� ����������� ��',
              'class' => 'b-combo__input_width_250 
                          b-combo__input_visible_height_200 
                          b-combo__input_arrow_yes 
                          b-combo__input_init_citiesList
                          b-combo__input_on_click_request_id_getcities',
              'suffix' => '���� �������������� ������ � ����� - �������, � ����� ������ �� ���������.',
              'value' => '��� ������',
              'validators' => array(
                  array('Digits', true)
              )
          ))
        );
        
        $this->addElement(
            new Form_Element_BudgetExt('cost', array(
                'padbot' => 30, // ������ �����
                'label' => '������',
                'required' => true,
                'filters' => $this->filters,
                'validators' => array(
                    array(new Form_Validate_CostOrAgreementRequired(), true)
                ),
                'value' => array(
                    'priceby_db_id' => 3
                )
            ))
        );

        if (!$this->isAdm()) {
            $this->addElement(
              new Zend_Form_Element_MultiCheckbox('filter', array(
                  'padbot' => 5, // ������ �����
                  'label' => '�������� �� �������� ����� ������ ...',
                  'value' => 'pro_only',
                  'multiOptions' => array(
                      'pro_only' => '���������� � ��������� '.  view_profi() . ' ��� ' . view_pro(),
                      //'verify_only' => '���������� c ������������ ' . view_verify()
                  )
              ))
            );
        }
        
        $this->addElement(
          new Form_Element_Hidden('auth', array(
              'validators' => array(
                  array('Digits')
               )
        ))); 
        
        $this->addElement(new Zend_Form_Element_Hidden('kind', array('value' => 4)));
        
    }    
    
    
    
    public function getCustomErrorMessage($err)
    {
        return GuestConst::getErrorMessage($err, GuestConst::TYPE_VACANCY);
    }
    
    
    public function getCustomMessage($mes)
    {
        $message = GuestConst::getMessage($mes, GuestConst::TYPE_VACANCY);
        
        if ($mes == GuestConst::MSG_SUBMIT) {
            $vacancyPrice = new_projects::getProjectInOfficePrice();
            $message = sprintf($message, $vacancyPrice);
        }
        
        return $message;
    }
    
}