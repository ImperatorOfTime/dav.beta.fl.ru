<?php

require_once 'DigestBlockListProject.php';

class DigestBlockListContest extends DigestBlockListProject {
    
    /**
     * ��� �������
     */
    const PROJECT_KIND = 7;
    
    /**
     * @see parent::$title
     * @var string 
     */
    public $title = '<a class="b-layout__link" href="/konkurs/" target="_blank">��� %s ���������</a> � �������� ������� �������� �� ������';
    
    /**
     * @see parent::$hint
     * @var string 
     */
    public $hint  = '��������: http://www.free-lance.ru/projects/5/example.html';
    
    /**
     * @see parent::$title_field
     * @var string 
     */
    public $title_field = '������ �� ��������:';
}
