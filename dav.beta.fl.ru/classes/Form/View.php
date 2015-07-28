<?php

//���� ��� ������������� �������
set_include_path(get_include_path()
        . PATH_SEPARATOR . ABS_PATH . '/classes/'
);

// ������� ��������� �� ������� ���������� ���� Zend_Form
$translateValidators = array(
    Zend_Validate_Alnum::NOT_ALNUM => '��������� �������� ������������. ��������� ������ ��������� ������� � �����', 
    Zend_Validate_Alnum::STRING_EMPTY => '���� �� ����� ���� ������. ��������� ���, ����������', 
    Zend_Validate_Alpha::NOT_ALPHA => '������� � ��� ���� ������ ��������� �������', 
    Zend_Validate_Alpha::STRING_EMPTY => '���� �� ����� ���� ������. ��������� ���, ����������', 
    Zend_Validate_Between::NOT_BETWEEN => '�������� ������ ���� � ��������� ����� "%min%" � "%max%"', 
    Zend_Validate_Between::NOT_BETWEEN_STRICT => '�������� �� ��������� ������ ����� "%min%" � "%max%"', 
    Zend_Validate_Ccnum::LENGTH => '�������� ������ ���� ��������� ��������� �� 13 �� 19 ���� �������', 
    Zend_Validate_Ccnum::CHECKSUM => '������� ����������� ����� ��������. �������� �������', 
    Zend_Validate_Date::INVALID => '�������� ����', 
    Zend_Validate_Date::FALSEFORMAT => '�������� �� �������� �� �������', 
    Zend_Validate_Digits::NOT_DIGITS => '�������� ������������. ������� ������ �����', 
    Zend_Validate_Digits::STRING_EMPTY => '���� �� ����� ���� ������. ��������� ���, ����������', 
    Zend_Validate_EmailAddress::INVALID => '������������ ����� ����������� �����. ������� ��� � ������� ���@�����', 
    Zend_Validate_EmailAddress::INVALID_FORMAT => "����� ����������� ����� ������ ��������� @, ����� �, �������, ��� ������� ����� �����.",
    Zend_Validate_EmailAddress::INVALID_HOSTNAME => '"%hostname%" �������� ����� ��� ������ "%value%"', 
    Zend_Validate_EmailAddress::INVALID_MX_RECORD => '����� "%hostname%" �� ����� MX-������ �� ������ "%value%"', 
    Zend_Validate_EmailAddress::DOT_ATOM => '"%localPart%" �� ������������� ������� dot-atom', 
    Zend_Validate_EmailAddress::QUOTED_STRING => '"%localPart%" �� ������������� ������� ��������� ������', 
    Zend_Validate_EmailAddress::INVALID_LOCAL_PART => '"%localPart%" �� ���������� ��� ��� ������, ������� ����� ���� ���@�����', 
    Zend_Validate_Float::NOT_FLOAT => '�������� �� �������� ������� ������', 
    Zend_Validate_GreaterThan::NOT_GREATER => '�������� �� ��������� "%min%"', 
    Zend_Validate_Hex::NOT_HEX => '�������� �������� � ���� �� ������ ����������������� �������', 
    Zend_Validate_Hostname::IP_ADDRESS_NOT_ALLOWED => '"%value%" - ��� IP-�����, �� IP-������ �� ��������� ', 
    Zend_Validate_Hostname::UNKNOWN_TLD => '"%value%" - ��� DNS ��� �����, �� ��� �� ����� ���� �� TLD-������', 
    Zend_Validate_Hostname::INVALID_DASH => '"%value%" - ��� DNS ��� �����, �� ���� "-" ��������� � ������������ �����', 
    Zend_Validate_Hostname::INVALID_HOSTNAME_SCHEMA => '"%value%" - ��� DNS ��� �����, �� ��� �� ������������� TLD ��� TLD "%tld%"', 
    Zend_Validate_Hostname::UNDECIPHERABLE_TLD => '"%value%" - ��� DNS ��� �����. �� ������ ������� TLD �����', 
    Zend_Validate_Hostname::INVALID_HOSTNAME => '"%value%" - �� ������������� ��������� ��������� ��� DNS ����� �����', 
    Zend_Validate_Hostname::INVALID_LOCAL_NAME => '"%value%" - ����� �������� ������������ ��������� ������� �������', 
    Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED => '"%value%" - ����� �������� ������� �������������, �� ��������� ������� ������ �� ���������', 
    Zend_Validate_Identical::NOT_SAME => '�������� �� ���������', 
    Zend_Validate_Identical::MISSING_TOKEN => '�� ���� ������� �������� ��� �������� �� ������������', 
    Zend_Validate_InArray::NOT_IN_ARRAY => '�������� �� ������� � ������������� ���������� ���������', 
    Zend_Validate_Int::NOT_INT => '�������� �� �������� ������������� ���������', 
    Zend_Validate_Ip::NOT_IP_ADDRESS => '�������� �� �������� ���������� IP-�������', 
    Zend_Validate_LessThan::NOT_LESS => '�������� �� ������, ��� "%max%"', 
    Zend_Validate_NotEmpty::IS_EMPTY => '�������� �������� ������, ��������� ����, ����������', 
    Zend_Validate_StringLength::TOO_SHORT => '����� ��������� ��������, ������ ��� %min% ����.', 
    Zend_Validate_StringLength::TOO_LONG => '����� ��������� �������� �� ������ ���� ������ ��� %max% ��������', 
);
$translator = new Zend_Translate('Zend_Translate_Adapter_Array', $translateValidators);
Zend_Validate_Abstract::setDefaultTranslator($translator);


class Form_View extends Zend_Form
{
    protected $viewScriptPrefixPath = 'classes/Form/Templates';
    protected $_idSuffix = null;
    protected $_idPreffix = null;


    public function __construct($options = null)
    {
        parent::__construct($options);
        $view = new Zend_View();
        $view->setScriptPath($_SERVER['DOCUMENT_ROOT']);
        $this->setView($view);
        
        //��� ���� ��������� �������
        $this->addElementPrefixPath(
                'Form_Filter',
                'Form/Filter',
                'filter');
        
        //��� ���� ��������� ����������
        $this->addElementPrefixPath(
                'Form_Validate',
                'Form/Validate',
                'validate');
        
        //�� ��������� ��� ��������� ������ �������� ID - ��� ������ �������
        $this->setDefaultIdPreffix();
    }

    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            'FormElements',
            'Form',
        ));

        $this->setSubFormDecorators(array(
            'FormElements'
        ));
    }

    public function addElement($element, $name = null, $options = null)
    {
        $names = explode('_', get_class($element));
        if ($names[0] === 'Zend' || 
            (
                isset($element->override_view_script) && 
                $element->override_view_script == true
            )) {
            
            $element_name = array_pop($names);
            $element->clearDecorators();
            $element->addDecorator('ViewScript', array('viewScript' => $this->viewScriptPrefixPath.'/'.$element_name.'.phtml'));
        }

        if ($this->getAttrib('readonly')) {
            // ������ ��� �������� ����� ������ ��� ������
            $element->setAttrib('readonly', true);
            $options['readonly'] = true;
        }

        $view = new Zend_View();
        $view->setScriptPath($_SERVER['DOCUMENT_ROOT']);
        $element->setView($view);   

        return parent::addElement($element, $name, $options);
    }

    
    
    public function addElementByName($element, $name, $options)
    {
        return parent::addElement($element, $name, $options);
    }

    



    /**
    * ���������� ��� �������� ������� � ���� ������
    *
    * @return array $values
    */
    public function getSubFormsValues()
    {
        $values = array();

        foreach ($this->getSubForms() as $form) {
            $name = $form->getName();
            $value = $form->getValues(); 
        
            $values = array_merge($value[$name], $values);    
        }
        
        return $values;
    }
    
    
    /**
     * ���������� ������� ID
     *
     * @param string $suffix
     * @return My_Form
     */
    public function setIdSuffix($suffix)
    {
        $this->_idSuffix = $suffix;
        return $this;
    }

    /**
     * ���������� ������� ID
     * 
     * @param type $preffix
     * @return \Form_View
     */
    public function setIdPreffix($preffix)
    {
        $this->_idPreffix = $preffix;
        return $this;
    }
    
    /**
     * ���������� ����������� ��������� �������� ID ��� ������ �������
     * 
     * @return object
     */
    public function setDefaultIdPreffix()
    {
        $preffix = strtolower(str_replace('Form', '', get_called_class()));
        return $this->setIdPreffix($preffix);
    }

    

    /**
     * ����� �����
     * @todo: �������������� ��� ��������� �������� � �������� ��� ID ����� �/��� ���������
     *
     * @param Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if (!is_null($this->_idSuffix) || 
            !is_null($this->_idPreffix)) {
            
            // form
            $formId = $this->getId();
            if (0 < strlen($formId) && !is_null($this->_idSuffix)) {
                $this->setAttrib('id', $formId . '-' . $this->_idSuffix);
            }

            // elements
            $elements = $this->getElements();
            foreach ($elements as $element) {
                
                $element_id = $element->getId();
                
                if ($this->_idPreffix) {
                    $element_id = $this->_idPreffix . '-' . $element_id;
                }
                
                if ($this->_idSuffix) {
                    $element_id .= '-' . $this->_idSuffix;
                }
                
                $element->setAttrib('id', $element_id);
            }
        }

        return parent::render($view);
    }
    
    /**
     * �������� ������ ��������� � ���� ������ 
     * ��� ������� � ��������� ������������
     * 
     * @param type $glue
     * @return type
     */
    public function getAllMessages($glue = '. ')
    {
        $result = null;
        $messages = $this->getMessages();
        
        if (count($messages)) {
            foreach($messages as $key => $value) {
                $result[$key] = implode($glue, $value);
            }
        }
        
        return $result;
    }
    
    
}

