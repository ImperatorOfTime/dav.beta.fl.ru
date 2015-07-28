<?php

/**
 * ����� ��� ��������� ���������� ���������� ��� � ������� XLSX
 */
class XLSXDocument_ITO extends odt2pdf {
    
    /**
     * �������� ����� � ���������� �������
     * 
     * @var string
     */
    private $_sharedStrings = 'xl/sharedStrings.xml';
    
    /**
     * �������� ����� � ������� �������� �����
     * 
     * @var string
     */
    private $_workSheet     = 'xl/worksheets/sheet1.xml';
    
    /**
     * �������� ����� � ������� � �������� ������������ � ���������
     * 
     * @var string
     */
    private $_calcChain     = 'xl/calcChain.xml';
    
    /**
     * ����� �����������
     * 
     * @var string
     */
    private $_styles        = 'xl/styles.xml';
    
    /**
     * ������ ���������
     * 
     * @var string
     */
    private $_template;
    
    /**
     * ���� � �������� ODT
     * 
     * @var string 
     */
    protected $_folder = SBR_FOLDER_DOCS;
    
    /**
     * ���� � ��������� �����
     * 
     * @var string
     */
    protected $_tmp    = SBR_FOLDER_TMP;
    
    /**
     * ������ ����� ������� � ������
     * 
     * @var integer
     */
    protected $_sharedIndex;
    
    /**
     * ������ ����� ������� � ������
     * 
     * @var integer
     */
    protected $_sheetIndex;
    
    /**
     * ������ ����� ������ � ������
     * 
     * @var integer
     */
    protected $_calcIndex;
    
    /**
     * ������ ����� ������ � ������
     * 
     * @var integer
     */
    protected $_styleIndex;
    
    /**
     * ����� ������ � ������� �������� ��������� ������
     * 
     * @var integer
     */
    protected $_startPosition = 25; // ������ � ������� �������� ��������� ������
    
    /**
     * �������� ������������������ DOMDocument
     * 
     * @var array
     */
    public $dom = array();
    
    /**
     * �������� ������������������ DOMXPath
     * 
     * @var array
     */
    public $xpath = array();
     
    public $debug = false;
    
    /**
     * ����������� ������
     * 
     * @param string $template      �������� ������� �� ������ ����� ��������  
     */
    public function __construct($template = 'tpl_ito.xlsx') {
        $this->setTemplate($template);
    }
    
    /**
     * ������ ������ ���������
     * 
     * @param string $template  �������� ������� �� ������ ����� ��������  
     * @throws Exception
     */
    public function setTemplate($template) {
        try {
            if( !file_exists($this->_folder . DIRECTORY_SEPARATOR . $template) ) {
                throw new Exception('Template file does not exists.');
            }
            $this->_template = $template;
        } catch(Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    
    /**
     * ���������� �������� �������
     * 
     * @return string
     */
    public function getTemplate() {
        return $this->_template;
    }
    
    /**
     * �������� ����� � UTF8
     * 
     * @param string $val   ����� ��� �����������
     * @return type
     */
    private function _enc($val) {
        return iconv('cp1251', 'utf8', $val);
    }
    
    /**
     * ���������� � ��������� ����� �������
     * ������� ����� ���� ����������� ����������� �������
     * 
     * @return boolean 
     */
    public function prepareFile() {
        $fname = $this->getFolder() . DIRECTORY_SEPARATOR . $this->getTemplate();
        $this->convert_file = $this->generateNameFile();
        $this->file_path = $this->getTmpFolder() . $this->convert_file . ".xlsx";
        
        return copy($fname, $this->file_path);
    }
    
    /**
     * ������ ��������� ������ � ������
     * 
     * @param string $file_path   ���� ����� � ������ ������� ������
     * @param string $content     ���������� ������
     * @return boolean 
     */
    public function setContentFile($file_path, $content) {
        if($file_path == null) return false;
        if($this->opened) {
            return $this->zip->addFromString($file_path, $content);
        }
        return false;
    }
    
    /**
     * ������ ������ ������ ������ ��� ��������� ���������
     * 
     * @param array $period (01-01-2012,30-01-2012)
     */
    public function setPeriod($period) {
        $this->period = $period;
    }
    
    /**
     * ���������� ����� ��������
     * 
     * @param boolean $save    ��������� �������� �� ����� ��� ���
     * @return boolean
     */
    public function generateDocument($save = false) {
        set_time_limit(0);
        if($this->prepareFile()) {
            
            if($this->initZipOpenFile()) {
                $this->parseContent();
                
                if($save) {
                    return $this->saveFile();
                }
            }
        }
    }
    
    /**
     * ���������� ������ �������������� �����
     * 
     * @return string
     */
    public function getOutput() {
        $content = file_get_contents($this->file_path);
        return $content;
    }
    
    /**
     * ��������� ������������ ���� � �������
     * 
     * @return \CFile
     */
    public function saveFile() {
        $login = 'admin';
        $content = $this->getOutput();

        $file = new CFile();
        $file->path = "users/" . substr($login, 0, 2) . "/{$login}/upload/";
        $file->name = basename($file->secure_tmpname($file->path, '.xlsx'));
        $file->size = strlen($content);
        if ($file->putContent($file->path . $file->name, $content)) {
            return $file;
        }
    }
    
    /**
     * ������������ ������ ��������� � �������� �� ���� �����
     * 
     */
    public function parseContent() {
        if ($this->opened) {
            $this->_sharedIndex = $this->zip->locateName($this->_sharedStrings, ZIPARCHIVE::FL_NOCASE);
            $this->_sheetIndex  = $this->zip->locateName($this->_workSheet, ZIPARCHIVE::FL_NOCASE);
            $this->_calcIndex   = $this->zip->locateName($this->_calcChain, ZIPARCHIVE::FL_NOCASE);
//            $this->_styleIndex  = $this->zip->locateName($this->_styles, ZIPARCHIVE::FL_NOCASE);
            
            $this->initDOMDocument('shared', $this->_sharedIndex);
            $this->initDOMDocument('sheet',  $this->_sheetIndex);
//            $this->initDOMDocument('style',  $this->_styleIndex);
            $this->initDOMDocument('calc',  null); // �� �������� �������� ����� ��������
           
            $pskb = sbr_meta::getReservedSbr($this->period);
            $count_rows = count($pskb);
            
            $from_date = date('d.m.Y', strtotime($this->period[0]));
            $to_date   = date('d.m.Y', strtotime($this->period[1]));
            
            $period = "�� ������ � {$from_date} �� {$to_date}";
            $this->replaceSharedString(4, $period);
            $this->moveFooter($count_rows);
            foreach($pskb as $i=>$data) {
                $this->setOneRowTable($i, $data);
            }
            $this->generateFormulaData();
            
            $this->setContentFile($this->_sharedStrings, $this->dom['shared']->saveXML());
            $this->setContentFile($this->_workSheet, $this->dom['sheet']->saveXML());
            $this->setContentFile($this->_calcChain, $this->dom['calc']->saveXML());
            
            // ��� ��� �����������
            $this->zip->close();
        }
    }
    
    /**
     * �������� ����� �� ������������ ���������� �����
     * 
     * @param integer $rows ���������� �����
     */
    public function moveFooter($rows = 1) {
        // ������� �� ������� ������ ������ (������ �����)
        $position = $this->_startPosition;
        $row = $this->xpath['sheet']->query('//p:row[@r= "' . $position . '"]', $this->dom['sheet']->documentElement)->item(0);
        
        do {
            $now_position = (int) $row->getAttribute('r');
            $replace[$now_position] = ($now_position + $rows); // �������� �������

            $row->setAttribute('r', $replace[$now_position]);
            for ($i = 0; $i < $row->childNodes->length; $i++) {
                $c  = $row->childNodes->item($i);
                $rc = $c->getAttribute('r');
                
                if ($rc == "H{$this->_startPosition}") {
                    $fv = ($position - 1) + $rows;
                    $f = $this->dom['sheet']->createElement('f', $this->_enc("SUM(H{$this->_startPosition}:I{$fv})"));
                    $c->appendChild($f);
                }
                $rc = str_replace($now_position, $replace[$now_position], $rc);
                $c->setAttribute('r', $rc);
            }
        } while ( ($row = $row->nextSibling) );
        
        // ������� ������� � ������������
        $mergeCells   = $this->dom['sheet']->getElementsByTagName('mergeCells')->item(0);
        $find_replace = array_keys($replace);

        for($i=0; $i < $mergeCells->childNodes->length; $i++) {
            $node = $mergeCells->childNodes->item($i);
            $ref  = $node->getAttribute('ref');
            list($from, $to) = explode(":", $ref);
            $from = preg_replace("/\D+/", "", $from);
            if( in_array($from, $find_replace) ) {
                $ref = str_replace($from, $replace[$from], $ref);
                $node->setAttribute('ref', $ref);
            }
        }
    }
    
    /**
     * ��������� ������ � �������
     * 
     * @param integer $n       ����� ������
     * @param array   $data    ������ ����������
     */
    public function setOneRowTable($n, $data) {
        $pos = $this->_startPosition + $n;
        // ���� ���������� �������
        if(!$this->prevRow) {
            $this->prevRow   = $this->xpath['sheet']->query('//p:row[@r= "' . ($pos - 1) . '"]', $this->dom['sheet']->documentElement)->item(0);
            // ����� �������
            for($i = 0;$i<$this->prevRow->childNodes->length;$i++) {
                $snode = $this->prevRow->childNodes->item($i);
                $this->style_table[str_replace($pos - 1, '', $snode->getAttribute('r'))] = $snode->getAttribute('s');
            }
        }
        $row  = $this->dom['sheet']->createElement('row');
        $row->setAttribute('r', $pos);
        $row->setAttribute('spans', "1:9");
        $row->setAttribute('customHeight', "1");
        $row->setAttribute('x14ac:dyDescent', "0.2");
        
        $c = $this->dom['sheet']->createElement('c');
        $v = $this->dom['sheet']->createElement('v');
        $f = $this->dom['sheet']->createElement('f'); // �������
        
        $name_emp = $this->_enc($data['nameCust']);
        $sbr_id   = $this->_enc("� {$data['sbr_id']}, ".date('d.m.Y H:i', strtotime($data['covered'])));
        $lc_id    = $this->_enc("� {$data['lc_id']}");
        $cost     = $this->_enc($data['cost']);
        
        $len_name = strlen($data['nameCust']);
        $height   = ceil($len_name / 33) * 18;
        $row->setAttribute('ht', $height);
        
        // ������� "�/�"
        if($pos == $this->_startPosition) {
            $cell['A'] = $this->createOneCell($c, $v, array('r' => "A{$pos}", 's' => $this->style_table['A']), "1");
        } else {
            $R = $pos-1;
            $cell['A'] = $this->createOneCell($c, $f, array('r' => "A{$pos}", 's' => $this->style_table['A']), "A{$R}+1");
        }
        
        // ������� "������������ ���������"
        $cell['B'] = $this->createOneCell($c, $v, array('r' => "B{$pos}", 's' => $this->style_table['B'], 't' => 's'), $this->createSharedTextItem($name_emp));
        $cell['C'] = $this->createOneCell($c, $v, array('r' => "C{$pos}", 's' => $this->style_table['C']));
        
        // ������� "���������� � ����"
        $cell['D'] = $this->createOneCell($c, $v, array('r' => "D{$pos}", 's' => $this->style_table['D'], 't' => 's'), $this->createSharedTextItem($sbr_id));
        $cell['E'] = $this->createOneCell($c, $v, array('r' => "E{$pos}", 's' => $this->style_table['E']));
        
        // ������� "������������� �����������"
        $cell['F'] = $this->createOneCell($c, $v, array('r' => "F{$pos}", 's' => $this->style_table['F'], 't' => 's'), $this->createSharedTextItem($lc_id));
        $cell['G'] = $this->createOneCell($c, $v, array('r' => "G{$pos}", 's' => $this->style_table['G']));
        
        // ������� "����� �������� �������� �������"
        $cell['H'] = $this->createOneCell($c, $v, array('r' => "H{$pos}", 's' => $this->style_table['H']), $cost);
        $cell['I'] = $this->createOneCell($c, $v, array('r' => "I{$pos}", 's' => $this->style_table['I']));
        
        foreach($cell as $node) {
            $row->appendChild($node);
        }
        
        $this->prevRow = $this->dom['sheet']->getElementsByTagName('sheetData')->item(0)->insertBefore($row, $this->prevRow->nextSibling);
        $this->generateMergeForNewRow($pos);
    }
    
    /**
     * ���������� ������, ��������� ������
     * 
     * @param DOMElement $c     ������ �������
     * @param DOMElement $v     ������ ������ (����� ���� ��� ������ ������ ��� � �������)
     * @param array $attributes ��������� ������
     * @param string $value     �������� ������    
     * @return DOMNode
     */
    public function createOneCell($c, $v, $attributes, $value = null) {
        if($value != null) {
            $cell_value = $v->cloneNode(true);
            $cell_value->nodeValue = $value;
        }
        $cell       = $c->cloneNode(true);
        foreach($attributes as $name=>$attr) {
            $cell->setAttribute($name, $attr);
        }
        if($value != null) {
            $cell->appendChild($cell_value);
        }
        return $cell;
    }
    
    /**
     * ������� ���������� ������ � �������, ���� ����� � ������� ���������� �������� ������� ������ ���������
     * 
     * @param string $text    �����
     * @return integer  ����� ������� ���������������� ��������
     */
    public function createSharedTextItem($text) {
        $si = $this->dom['shared']->createElement('si');
        $t  = $this->dom['shared']->createElement('t', $text);
        $t->setAttribute('xml:space', 'preserve');
        $si->appendChild($t);
        
        $this->dom['shared']->documentElement->appendChild($si);
        $sst = $this->dom['shared']->getElementsByTagName('sst')->item(0);
        $position   = $sst->childNodes->length;
        
        $sst->setAttribute('count', $position);
        $sst->setAttribute('uniqueCount', $position-2);
        return ($position - 1); // ���������� � ����
    }
    
    /**
     * ���������� ������ ��� ������ (������ � �������� ��������� � ��������� �����)
     */
    public function generateFormulaData() {
        $formula = $this->xpath['sheet']->query('//p:f', $this->dom['sheet']->documentElement);
        
        $calcChain = $this->dom['calc']->createElement('calcChain');
        
        for($i=0; $i < $formula->length; $i++) {
            $node = $formula->item($i);
            $r    = $node->parentNode->getAttribute('r');
            $c    = $this->dom['calc']->createElement('c');
            $c->setAttribute('r', $r);
            $c->setAttribute('i', 1);
            if($r{0} == 'H') {
                $c->setAttribute('l', 1);
            }
            $calcChain->appendChild($c);
        }

        $this->dom['calc']->appendChild($calcChain);
    }
    
    /**
     * �������� ����� � ������������ ����������
     * 
     * @param integer $index   ������ ������ (������� ��������)
     * @param string  $text    ���������� �����    
     */
    public function replaceSharedString($index, $text) {
        $period = $this->xpath['shared']->query('//p:si[' . $index . ']/p:t', $this->dom['shared']->documentElement)->item(0);
        $period->nodeValue = $this->_enc($text);
    }
    
    /**
     * ���������� ������ ����������� ����� (��� �������)
     * 
     * @param integer $new_position    ������� ������ ������� ��������
     */
    public function generateMergeForNewRow($new_position) {
        $m0 = $this->dom['sheet']->createElement('mergeCell');
        $m0->setAttribute('ref', "B{$new_position}:C{$new_position}");
        $m1 = $m0->cloneNode();
        $m1->setAttribute('ref', "D{$new_position}:E{$new_position}");
        $m2 = $m0->cloneNode();
        $m2->setAttribute('ref', "F{$new_position}:G{$new_position}");
        $m3 = $m0->cloneNode();
        $m3->setAttribute('ref', "H{$new_position}:I{$new_position}");

        $mergeCells = $this->dom['sheet']->getElementsByTagName('mergeCells')->item(0);
        $mergeCells->appendChild($m0);
        $mergeCells->appendChild($m1);
        $mergeCells->appendChild($m2);
        $mergeCells->appendChild($m3);
        $mergeCells->setAttribute('count', $mergeCells->childNodes->length);
    }
    
    /**
     * ������������� DOMDocument ��� ������ � �������
     * 
     * @param string  $name     �������� �����
     * @param integer $index    ������ ��������� � ������ 
     * @return type
     */
    public function initDOMDocument($name, $index = null) {
        $this->dom[$name] = new DOMDocument('1.0', 'UTF-8');
        $this->dom[$name]->standalone = true;
        if($index !== null) {
            $content = $this->zip->getFromIndex($index);
            $this->dom[$name]->loadXML($content);
        }
        
        $this->xpath[$name] = new DOMXPath($this->dom[$name]);
        $this->xpath[$name]->registerNamespace("p", "http://schemas.openxmlformats.org/spreadsheetml/2006/main");
        
        return $this->dom[$name];
    }
}
?>