<?php

/**
 * ����� ��� ������ � ����������� ODT
 */
class ODTDocument extends odt2pdf 
{
    /**
     * ������� ��� ������ � ������� �������
     * 
     * @var type 
     */
    protected $_adapter;
    
    /**
     * ������������� �������
     * 
     * @param ODTDocumentAdapter $adapter
     */
    public function setAdapter(ODTDocumentAdapter $adapter) {
        $this->_adapter = $adapter;
    }
    
    /**
     * ���������� �������
     * 
     * @return ODTDocumentAdapter
     */
    public function getAdapter() {
        return $this->_adapter;
    }
    
    /**
     * ���������� ������ ���������
     * 
     * @return type
     */
    public function getOutput() {
        $content = file_get_contents($this->file_path);
        return $content;
    }
    
    /**
     * ���������� ����� ��������
     * 
     * @param boolean $save    ��������� �������� �� ����� ��� ���
     * @return boolean
     */
    public function generateDocument($save = false) {
        if($this->_adapter == null) return false;
        
        if($this->prepareFile()) {
            
            if($this->initZipOpenFile()) {
                $this->getContentFile();
                $this->setContentFile($this->getAdapter()->parseContent($this->getContent()));
                $this->zip->close();
                
                if($save) {
                    return $this->saveFile();
                }
            }
        }
    }
    
    /**
     * ��������� �������� �� ����� 
     * 
     * @return \CFile
     */
    public function saveFile() {
        $login = 'admin';
        $content = $this->getOutput();

        $file = new CFile();
        $file->path = "users/" . substr($login, 0, 2) . "/{$login}/upload/";
        $file->name = basename($file->secure_tmpname($file->path, '.odt'));
        $file->size = strlen($content);
        if ($file->putContent($file->path . $file->name, $content)) {
            return $file;
        }
    }
}

/**
 * ���������� �������� ����������
 */
interface ODTDocumentAdapter 
{
    function parseContent($content);
}

/**
 * ����� ��� ��������� ������ ���
 */
class ODTDocument_ITO implements ODTDocumentAdapter
{
    public $dom;
    public $xpath;
    
    /**
     * ������ ������ ������ ������ ��� ��������� ���������
     * 
     * @param array $period (01-01-2012,30-01-2012)
     */
    function setPeriod($period) {
        $this->period = $period;
    }
    
    /**
     * �������� ����� � UTF8
     * 
     * @param string $val
     * @return type
     */
    private function _enc($val) {
        return iconv('cp1251', 'utf8', $val);
    }
    
    /**
     * ������ ������ ��������� � �������� �� ���� �����
     * 
     * @param string $content   ������ ��������� (������ ��� content.xml �� ������� ��������� ODT)
     * 
     * @return type
     */
    function parseContent($content) {
        $this->dom = new DOMDocument('1.0');
        $this->dom->loadXML($content);
        $this->xpath = new DOMXPath($this->dom);
        
        $this->setStyleTable();
        
        $period = $this->xpath->query('//text:p[@text:style-name= "period"]', $this->dom->documentElement)->item(0);
        
        if($period) {
            $from_date = date('d.m.Y', strtotime($this->period[0]));
            $to_date   = date('d.m.Y', strtotime($this->period[1]));
            
            $new_period = $this->dom->createElement('text:p', iconv("windows-1251", "utf-8", "�� ������ � {$from_date} �� {$to_date}") );
            $new_period->setAttribute('text:style-name', 'period');
            $period->parentNode->replaceChild($new_period, $period);
        }
        
        $table  = $this->xpath->query('//table:table[@table:name= "table_test"]', $this->dom->documentElement)->item(0);
        
        $pskb = sbr_meta::getReservedSbr($this->period);
        $i    = 1;
        $sum  = 0;
        
        if($pskb) {
            foreach($pskb as $data) {
                $table_row = $this->dom->createElement('table:table-row');

                $name_emp = $this->_enc($data['nameCust']);
                $sbr_id   = $this->_enc("� {$data['sbr_id']}, ".date('d.m.Y H:i', strtotime($data['covered'])));
                $lc_id    = $this->_enc("� {$data['lc_id']}");
                $cost     = $this->_enc(number_format($data['cost'], 2, ',', ' '));

                $table_row->appendChild($this->createTableCell($i)); // �/�
                $table_row->appendChild($this->createTableCell($name_emp)); // ������������ ���������
                $table_row->appendChild($this->createTableCell($sbr_id)); // ���������� (�, ����)
                $table_row->appendChild($this->createTableCell($lc_id)); // ������������� �����������
                $table_row->appendChild($this->createTableCell($cost)); // ����� ��������������

                $i++;

                $sum += $data['cost'];

                $table->appendChild($table_row);
            }
        }
        
        // ��������� �����
        $table_row = $this->dom->createElement('table:table-row');
        $table_row->appendChild($this->createTableCell($this->_enc('����� �� �������� ������:'), 4, 'p_1'));
        $table_row->appendChild($this->dom->createElement('table:covered-table-cell')); // ������������ ���������
        $table_row->appendChild($this->dom->createElement('table:covered-table-cell')); // ���������� (�, ����)
        $table_row->appendChild($this->dom->createElement('table:covered-table-cell')); // ������������� �����������
        $table_row->appendChild($this->createTableCell(number_format($sum, 2, ',', ' '))); // �����
        $table->appendChild($table_row);

        return $this->dom->saveXML();
    }
    
    /**
     * ������� ������ �������
     * 
     * @param string  $content     ������ ������
     * @param integer $col         ����������� ������� (1 -- ��� �����������)      
     * @param string  $text_style  �������� ����� ������
     * @return DOMNode
     */
    public function createTableCell($content, $col=1, $text_style = 'P1') {
        $table_cell = $this->dom->createElement('table:table-cell');

        $table_cell->setAttribute('table:style-name', 'table_5f_test.A2');
        $table_cell->setAttribute('office:value-type', 'string');
        if($col>0) {
            $table_cell->setAttribute('table:number-columns-spanned', $col);
        }
        $text = $this->dom->createElement('text:p', $content);
        $text->setAttribute('text:style-name', $text_style);

        $table_cell->appendChild($text);
        
        return $table_cell;
    }
    
    /**
     * ������ ����� ��� ����� �������
     */
    public function setStyleTable() {
        $styles = $this->xpath->query('//office:automatic-styles', $this->dom->documentElement)->item(0);

        $new_style = $this->dom->createElement('style:style');
        $new_style->setAttribute('style:name', 'table_5f_test.A2');
        $new_style->setAttribute('style:family', 'table-cell');

        $style_cell = $this->dom->createElement('style:table-cell-properties');
        $style_cell->setAttribute('fo:padding', '0.097cm');
        $style_cell->setAttribute('fo:border-left', '0.002cm solid #000000');
        $style_cell->setAttribute('fo:border-right', '0.002cm solid #000000');
        $style_cell->setAttribute('fo:border-top', '0.002cm solid #000000');
        $style_cell->setAttribute('fo:border-bottom', '0.002cm solid #000000');

        $new_style->appendChild($style_cell);
        $styles->appendChild($new_style);
    }
}

?>