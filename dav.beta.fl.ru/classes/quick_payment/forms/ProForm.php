<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/View.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");

class ProForm extends Form_View
{
    public $filters = array(
        'StringTrim',
        'StripSlashes'
    );

    //���� � ������� ���������
    protected $viewScriptPrefixPath = 'classes/Form/Templates/Horizontal';
    
    
    /**
     * ����� ����� ��������
     */
    const TXT_ITEM      = "����� � ������ %s ���.";
    const TXT_ITEM_TEST = "����� � ������ <strike>&nbsp;%s&nbsp;</strike> <span class=\"g-color_f1645b\">%s ���.</span>";

    
    /**
     * ��� ������������
     * 
     * @var type 
     */
    protected $is_emp = false;


    /**
     * ������ ������� �������� ������
     * 
     * @var type 
     */
    protected $list = array();




    public function __construct($is_emp = false, $options = null) 
    {
        $this->is_emp = $is_emp;
        $this->list = payed::getPayedPROList( ($this->is_emp ? 'emp' : 'frl') );
        
        parent::__construct($options);
    }
    
    
    public function getPayedList()
    {
        return $this->list;
    }
    

    public function init()
    {
        $multiOptions = array();
        $attrOptions = array();
        if ($this->list) {
            foreach ($this->list as $item) {
                
                $cost = view_cost_format($item['cost'], false);
                if (isset($item['old_cost'])) {
                    $old_cost = view_cost_format($item['old_cost'], false);
                    $label = sprintf(self::TXT_ITEM_TEST, $old_cost, $cost);                    
                } else {
                    $label = sprintf(self::TXT_ITEM, $cost);
                }
                
                $multiOptions[$item['opcode']] = $label;
                $attrOptions[$item['opcode']] = "data-quick-payment-price=\"{$item['cost']}\"";
            }
        }
        
        
        $this->addElement(
            new Zend_Form_Element_Radio('type', array(
                'value' => key($multiOptions),
                'class' => 'b-radio_pro',
                'hide_label' => true,
                'required' => true,
                'multiOptions' => $multiOptions,
                'attr' => $attrOptions
            ))
        );
    }
    

}