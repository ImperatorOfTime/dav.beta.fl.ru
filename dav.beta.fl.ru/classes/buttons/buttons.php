<?php

/**
 * ����� ��� ������ � ��������
 *  
 */
class buttons
{
    /**
     * ������ ����� ������
     * 
     * @var string 
     */
    public $TEMPLATE       = 'tpl.button.php';
    
    /**
     * �������� ������ �� ���������
     * 
     * @var string 
     */
    public $name           = 'Button';
    
    /**
     * ������������ ������
     * 
     * @var string 
     */
    public $abbr           = 'button';
    
    /**
     * ��������� ������ �� ���������
     * 
     * @var array 
     */
    public $options = array('link'  => 'javascript:void(0)',     
                            'color' => '',
                            'css'   => '', 
                            'event' => array());
    
    /**
     * ����������� ������
     * 
     * @param string $name �������� ������ 
     */
    public function __construct($name = null, $color = null, $abbr = null) {
        $this->setColor($color);
        if($name) $this->setName($name);
        if($abbr) $this->setAbbr($abbr);
    }
    
    /**
     * ��������� ������
     * 
     * @return string HTML-��� ������ 
     */
    public function draw() {
        ob_start();
        include $this->TEMPLATE;
        $result = ob_get_clean();
        return $result;
    }
    
    /**
     * ���������� ������ 
     */
    public function view() {
        print $this->draw();
    }
    
    /**
     * ������ CSS ��� ������
     * 
     * @param string  $css        �������� ������/�������
     * @param boolean $rewrite    ���������� ����� ��� ���
     */
    public function setCss($css, $rewrite = false) {
        if($rewrite) {
            $this->options['css'] = $css;
        } else {
            $this->options['css'] .= ' '.$css;
        }
    }
    
    /**
     * ���������� CSS ������
     * 
     * @return string
     */
    public function getCss() {
        return $this->options['css'];
    }
    
    /**
     * ������ ���� ������
     * 
     * @param string $color ���� ������ @see self::getColorMain(); 
     */
    public function setColor($color = '') {
        $this->options['color'] = $color;
    }
    
    /**
     * ���������� ���� ������
     * 
     * @return string 
     */
    public function getColor() {
        return $this->options['color'];
    }
    
    /**
     * ���������� ����� ��� ������ �� �������� ����� ������
     * 
     * @param string $color   ���� ������
     * @return string 
     */
    public function getColorMain($color = null) {
        if(!$color) $color = $this->getColor();
        switch($color) {
            case 'red':
                return 'b-button_flat_red';
                break;
            case 'green':
            default:    
                return 'b-button_flat_green';
                break;
        }
    }
    
    /**
     * ��������� ������� ������� ������
     * 
     * @param string $event ������� ������� ������ (js - onclick)
     */
    public function addEvent($name, $event) {
        $this->options['event'][$name] = $event;
    }
    
    /**
     * ���������� ������� ������� ������
     * 
     * @return string
     */
    public function getEvent($name) {
        return $this->options['event'][$name];
    }
    
    /**
     * ��������� ������ ������ 
     * 
     * @param string $link    ������ ������
     */
    public function setLink($link) {
        $this->options['link'] = $link;
    }
    
    /**
     * ���������� ������ ������
     * 
     * @return string 
     */
    public function getLink() {
        return $this->options['link'];
    }
    
    /**
     * ������ �������� �����
     * 
     * @param string $name �������� ������ 
     */
    public function setName($name) {
        $this->name = $name;
    }
    
    /**
     * ���������� �������� ������
     * 
     * @return string 
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * ���������� ��� ������� �� ������
     * 
     * @return string 
     */
    public function getEvents() {
        $string = '';
        foreach($this->options['event'] as $event_name => $event) {
            $string .= $event_name."=\"{$event}\" ";
        }
        return $string;
    }
    
    /**
     * ������ ����������� ������ (������������� ������)
     * 
     * @param string $abbr 
     */
    public function setAbbr($abbr) {
        $this->abbr = $abbr;
    }
    
    /**
     * ���������� ����������� ������
     * 
     * @return string 
     */
    public function getAbbr() {
        return $this->abbr;
    }
    
    /**
     * ���������� �������� ������
     * 
     * @return string
     */
    public function __toString() {
        return $this->name;
    }  
}


?>