<?php

require_once 'DigestBlockList.php';

/**
 * ����� ��� ������ � ������ "��������"
 */
class DigestBlockListInterview extends DigestBlockList {
    
    /**
     * ����������� ��������� �������������� ����
     * 
     * @var boolean 
     */
    const ADD_FIELD = true;
    
    /**
     * ����� ��������� � �������� ������
     * 
     * @var string 
     */
    const MASK_LINK = '~interview\/(\d+)\/~mix';
    
    /**
     * @see parent::$title
     * @var string 
     */
    public $title = '<a class="b-layout__link" href="/interview/" target="_blank">��������</a>';
    
    /**
     * @see parent::$hint
     * @var string 
     */
    public $hint  = '��������: https://www.free-lance.ru/interview/100/example.html';
    
    /**
     * @see parent::$title_field
     * @var string 
     */
    public $title_field = '������ �� ��������:';
    
    
    /**
     * @see parent::initHtmlData
     */
    public function initHtmlData() {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/interview.php';
        
        $ids = $this->parseLinks();
        if($ids) {
            $this->html_data = interview::getInterviewById(array_map("intval", $ids));
        }
    }
}