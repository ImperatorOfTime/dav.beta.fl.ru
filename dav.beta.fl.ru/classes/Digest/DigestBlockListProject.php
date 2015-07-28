<?php

require_once 'DigestBlockList.php';

/**
 * ����� ��� ������ � ������ �������
 */
class DigestBlockListProject extends DigestBlockList {
    
    /**
     * ��� �������
     */
    const PROJECT_KIND = 1;
    
    /**
     * �������� �� ������ �������������� ������
     * 
     * @var boolean 
     */
    const AUTO_COMPLETE = true;
    
    /**
     * ����� ��������� � �������� ������
     * 
     * @var string 
     */
    const MASK_LINK = '~projects\/(\d+)\/~mix';
    
    /**
     * @see parent::$title
     * @var string 
     */
    public $title = '<a class="b-layout__link" href="/projects/?kind=1" target="_blank">��� %s ��������</a> � �������� ������� �������� �� ������';
    
    /**
     * @see parent::$hint
     * @var string 
     */
    public $hint  = '��������: http://www.free-lance.ru/projects/5/example.html';
    
    /**
     * @see parent::$title_field
     * @var string 
     */
    public $title_field = '������ �� �������:';
    
    /**
     * ����������� ������
     * 
     * @param integer $size
     * @param mixed   $link
     */
    public function __construct($size = null, $link = null) {
        parent::__construct($size, $link);
        $this->setTitle($this->title, $this->getListSize());
    }
    
    /**
     * @see parent::initHtmlData
     */
    public function initHtmlData() {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php';
        
        $pid = $this->parseLinks();
        
        if($pid) {
            $pid = array_map('intval', $pid);
            $stop_words = new stop_words();
            $projects   = new_projects::getProjectsById($pid);
            
            foreach($projects as $i=>$project) {
                $sTitle = ( $project['moderator_status'] === '0' && $project['kind'] != 4 && $project['is_pro'] != 't' ? $stop_words->replace($project['name']) : $project['name'] );
                $projects[$i]['sTitle'] =  reformat2($sTitle, 30, 0, 1);
                $projects[$i]['friendly_url'] = $this->getLinkById($project['id']);
                $projects[$i]['str_cost'] = $project['cost'] ? CurToChar($project['cost'], $project['currency']) . getPricebyProject($project['priceby']) : "�� ��������������";
            }
            
            $this->html_data = $projects;
        }
    }
    
    /**
     * ������� �������������� ����� �����
     * 
     * @return boolean
     */
    public function setFieldAutoComplete() {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php';
        $projects = new_projects::getTopProjectBudget( constant(get_class($this) . '::PROJECT_KIND'), $this->getListSize() );
        
        if($projects) {
            foreach($projects as $project) {
                $link[] = $GLOBALS['host'] . getFriendlyURL('project', $project['id']);
            }

            $this->initBlock($link);
            return true;
        }
        return false;
    }
}