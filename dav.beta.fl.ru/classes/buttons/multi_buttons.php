<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/buttons/buttons.php");

/**
 * ����� ��� ������ � ������� ������
 */
class multi_buttons extends buttons 
{
     /**
     * ������ ������ ������
     * 
     * @var string 
     */
    public $TEMPLATE = 'tpl.button-multi.php';
    
    /**
     * ����� ������
     * @var array 
     */
    public $buttons  = array();
    
    /**
     * ��������� ������, ���� ������ 1 �� ������� ��� ����, 
     * ����� ���������� ������� ������ (������ � ������), 
     * � ������� ��� ������ ������
     * 
     * @return string HTML-��� 
     */
    public function draw() {
        if(count($this->buttons) == 0) return;
        if(count($this->buttons) == 1) {
            reset($this->buttons);
            $button = current($this->buttons);
            if(!is_object($button)) return false;
            return $button->draw();
        }
        $this->setMainButton();
        return parent::draw();
    }
    
    /**
     * ���������� ������� ������ 
     */
    public function setMainButton() {
        $this->main = array_shift($this->buttons);
    }
    
    /**
     * ���������� ������ � �����
     * 
     * @param buttons $button ������
     */
    public function addButton(buttons $button) {
        array_push($this->buttons, $button);
    }
    
    /**
     * ���������� ����� ��� ������ �� �������� ����� ������
     * 
     * @param string $color   ���� ������
     * @return string 
     */
    public function getColorMain($color = null) {
        if(!$color) $color = $this->main->getColor();
        switch($color) {
            case 'red':
                return 'b-button-multi__item_red';
                break;
            case 'green':
            default:    
                return 'b-button-multi__item_green';
                break;
        }
    }
    
    /**
     * ���������� ����� ��� ��� ������ �� �������� ����� ������ (������ � ���������� ����)
     * 
     * @param string $color   ���� ������
     * @return string 
     */
    public function getColorLink($color = null) {
        switch($color) {
            case 'red':
                return 'b-layout__link_dot_c7271e';
                break;
            case 'green':
            default:    
                return 'b-layout__link_bordbot_dot_0f71c8';
                break;
        }
    }
    
    /**
     * ���������� ���� �� � ������ ������ � ������������
     * 
     * @param string $abbr 
     */
    public function isButton($abbr) {
        if($this->main) {
            if($this->main->getAbbr() == $abbr) return true;
        }
        foreach($this->buttons as $button) {
            if($button->getAbbr() == $abbr) return true;
        }
        
        return false;
    }
    
    public function removeButton(buttons $button) {
        foreach($this->buttons as $k=>$btn) {
            if($button == $btn) {
                unset($this->buttons[$k]); 
                break;
            }
        }
    }
}

?>