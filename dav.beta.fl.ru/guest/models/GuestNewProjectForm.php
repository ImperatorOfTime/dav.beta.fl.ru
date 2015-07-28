<?php

require_once('GuestForm.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/ProfessionsDropdown.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/BudgetExt.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/GuestProjectUploader.php");

/**
 * Class GuestNewProjectForm
 * ����� ������ �������
 */
class GuestNewProjectForm  extends GuestForm
{
    /**
     * ������������� �����
     */
    public function init()
    {
        $this->addElement(
           new Zend_Form_Element_Text('name', array(
               'label' => '�������� �������',
               'required' => true,
               'padbot' => 30, // ������ �����
               'maxlength' => 60,
               'filters' => $this->filtersAll,
               'validators' => array(
                   array('StringLength',true,array('max' => 60,'min' => 4))
                ),
               'placeholder' => '���� �� ����� � ����� ������ ����� ���������.'
        )));        
        
        if ($this->isAdm()) {
            $this->addElement(
                new Zend_Form_Element_Text('link', array(
                    'label' => '������ �� ������',
                    'required' => true,
                    'padbot' => 30, // ������ �����
                    'filters' => $this->filters,
                    'validators' => array(
                        array('StringLength', true, array('min' => 4)),
                        array('UrlInvited', true, array('type' => GuestConst::TYPE_PROJECT))
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
              'padbot' => 30, // ������ �����
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
          new Form_Element_BudgetExt('cost', array(
              'padbot' => $this->isAdm() ? 5 : 30, // ������ �����
              'label' => '������',
              'filters' => $this->filters,
              'value' => array(
                  'priceby_db_id' => 4
              )
          ))
        );
        
        if ($this->isAdm()) {
            $this->addElement(new Zend_Form_Element_Hidden('prefer_sbr', array('value' => 1)));
        } else {
            $this->addElement(
                new Zend_Form_Element_Radio('prefer_sbr',array(
                    'padbot' => 30, // ������ �����
                    'label' => '������ ������',
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
                        0 => '�������������� ��� ������� ����� � �������� ������. �� ���� ��������������� � ������������ � ������� � ������� ������. 
                              � �������������� ����������� ��� ���������, ��������� � ��������� � ������� ���������� ������.'
                    )
                ))
            );
            
            /*
            $this->addElement(
                new Zend_Form_Element_MultiCheckbox('filter', array(
                    'padbot' => 5, // ������ �����
                    'label' => '�������� �� ������ ����� ������ ...',
                    'value' => 'pro_only',
                    'multiOptions' => array(
                        'pro_only' => '���������� � ��������� '.  view_profi() . ' ��� ' . view_pro(),
                        //'verify_only' => '���������� c ������������ ' . view_verify()
                    )
                ))
            );*/
        }
        
        $this->addElement(
          new Form_Element_Hidden('auth', array(
              'validators' => array(
                  array('Digits')
               )
        )));
        
        $this->addElement(new Zend_Form_Element_Hidden('kind', array('value' => 1)));
        
        
    }    
    
    
    
    public function getCustomErrorMessage($err)
    {
        return GuestConst::getErrorMessage($err, GuestConst::TYPE_PROJECT);
    }
    
    
    public function getCustomMessage($mes)
    {
        return GuestConst::getMessage($mes, GuestConst::TYPE_PROJECT);
    }
    
}
