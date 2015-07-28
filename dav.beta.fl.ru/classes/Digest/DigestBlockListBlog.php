<?php

require_once 'DigestBlockList.php';

/**
 * ����� ��� ������ � ������ "��������� � ������"
 */
class DigestBlockListBlog extends DigestBlockList {
    
    /**
     * @see parent::ADD_FIELD
     */
    const ADD_FIELD = true;
    
    /**
     * @see parent::MASK_LINK
     */
    const MASK_LINK = '~blogs\/\S+?\/(\d+?)\/~mix';
    
    /**
     * @see parent::$title
     * @var string 
     */
    public $title = '��������� � <a class="b-layout__link" href="/blogs/" target="_blank">������</a>';
    
    /**
     * @see parent::hint
     * @var string 
     */
    public $hint  = '��������: https://www.free-lance.ru/blogs/obschenie/268587/example.html';
    
    /**
     * @see parent::$tirle_field
     * @var string 
     */
    public $title_field = '������ �� �����:';
    
    
    /**
     * @see parent::initHtmlData
     */
    public function initHtmlData() {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/blogs.php';
        $ids = $this->parseLinks();
        if($ids) {
            $this->html_data = blogs::getBlogsByIds(array_map("intval", $ids));
        }
    }
}