<?php

/**
 * ���������� ���� � ��������� ���������
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ���� �� ��������
 */
if(!defined('SBR_FOLDER_DOCS')) {
    define("SBR_FOLDER_DOCS", $_SERVER['DOCUMENT_ROOT'] . "/norisk2/docs/");
}

if(!defined('SBR_FOLDER_TMP')) {
    define("SBR_FOLDER_TMP", '/var/tmp/sbr_docs/');
}

if(!defined('ODT2PDF_OPTIONS_EXEC')) {
    define("ODT2PDF_OPTIONS_EXEC", '--format=pdf --timeout=30 --stdout');
}

if(!defined('ODT2PDF_UNOCONV_EXEC')) {
    define("ODT2PDF_UNOCONV_EXEC", 'unoconv -v ');
}
/**
 * ����� ��� ��������� �� ������� ODT � PDF � ���������� �������������� ���������� 
 */
class odt2pdf
{
    /**
     * ������ ����� ODT
     * 
     * @var string 
     */
    protected $_content = "";
    
    /**
     * ���� � �������� ODT
     * 
     * @var string 
     */
    protected $_folder = SBR_FOLDER_DOCS;
    
    
    protected $_tmp = SBR_FOLDER_TMP;
    
    /**
     * ������ ��������� ������� ���������� ������������
     * 
     * @var string 
     */
    public $doc    = "test.odt";
    
    /**
     * ������ ������� unoconv 
     * 
     * @var string
     */
    public $programm_exec = ODT2PDF_UNOCONV_EXEC;
    
    /**
     * ����� ������� ���������� @see unoconv --help
     * 
     * @var string
     */
    public $option_exec = ODT2PDF_OPTIONS_EXEC;
    
    /**
     * �������� ����� � ������� ������� �� ������������ � ����� �� ������� ODT
     * 
     * @var string 
     */
    public $content_file = "content.xml";
    
    /**
     * �������� ������������� ������������������ �����
     * 
     * @var string 
     */
    public $convert_file = "";
    
    /**
     * ���� �� ����� � ���������������� PDF
     * 
     * @var string 
     */
    public $outputpath   = SBR_FOLDER_TMP;
    
    /**
     * ����� ���������� � �������
     * 
     * @var string 
     */
    public $mask_vars   = "{%s}";
    
    /**
     * ������� ������ � ������� ODT 
     */
    const LINE_BREAK     = "<text:line-break/>";
    
    /**
     * @deprecated
     * ����� ������� � ������� ODT
     */
    const MASK_CONDITION = "IFSHOW";
    
    /**
     * ����� ������ ��� ���
     * 
     * @var boolean
     */
    public $opened = false;
    
    /**
     * ������ � ZIP ��������
     * 
     * @var object ZipArchive
     */
    public $zip;
    
    public $log_unoconv = '/var/tmp/unoconv.log';
    
    /**
     * ����������� ������
     * 
     * @param string $doc ������ ��������� 
     */
    public function __construct($doc = false) {
        if($doc) $this->doc = $doc;
        $this->log = new log('odt2pdf/odt2pdf-'.SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
    }
    
    /**
     * ���������� � ��������� ����� �������
     * ������� ����� ���� ����������� ����������� �������
     * 
     * @return boolean 
     */
    public function prepareFile() {
        $fname = $this->getFolder() . $this->doc;
        if(!file_exists($fname)) return false;
        $this->convert_file = $this->generateNameFile();
        $this->file_path = $this->getTmpFolder() . $this->convert_file . ".odt";
        
        return copy($fname, $this->file_path);
    }
    
    /**
     * ���������� �������� ������ �����
     * 
     * @return string 
     */
    public function generateNameFile() {
        return substr(md5(microtime()), 0, 6);
    }
    
    /**
     * ������������� �������� ������
     * 
     * @return boolean 
     */
    public function initZipOpenFile() {
        $this->zip = new ZipArchive;
        if ($this->zip->open($this->file_path)) {
            $this->opened = true;
            return true;
        }
        return false;
    }
    
    /**
     * ������ ��������� ������ � ������
     * 
     * @param string $content     ���������� ������
     * @return boolean 
     */
    public function setContentFile($content) {
        if($this->opened) {
            return $this->zip->addFromString($this->content_file, $content);
        }
        return false;
    }
    
    /**
     * ����� ������ �� ������� 
     * 
     * ������ ��������� � self::$content_file ����� 
     */
    public function getContentFile() {
        if ($this->opened) {
            if (($index = $this->zip->locateName($this->content_file, ZIPARCHIVE::FL_NOCASE)) !== false) {
                $this->setContent($this->zip->getFromIndex($index));
            }
        }
    }
    
    /**
     * ������������ �������������� ���������� � �������
     * 
     * @param array $variables   ���������� ������� array('$name' => '��������')
     * @return array(key, $var)  ���������� ������ � ������������� ������� � ���������� � ��� 
     */
    public function prepareVariables($variables) {
        $keys = array_keys($variables);
        $vals = array_values($variables);
        
        foreach($vals as $k=>$val) {
            // ������ � ���������� ����������� �� ��� �������� (�������� -- &)
            $val = iconv("windows-1251", "utf-8", $val);
            $val = html_entity_decode($val, ENT_QUOTES, 'UTF-8');
            $val = htmlspecialchars($val, ENT_QUOTES, 'UTF-8', false); // ������ ��� �� �������� ��������� � ��������
            $val = str_replace("\r", "", $val);
            $val = str_replace("\n", self::LINE_BREAK, $val);
            $val = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $val);
            if($val == '') continue; // ����� ������ �������� clearEmptyVariables();
            $vals[$k] = $val;
        }
        
        foreach($keys as $i=>$key) {
            $keys[$i] = sprintf($this->mask_vars, $key);
        }
        
        return array($keys, $vals);
    }
    
    /**
     * ������� ������������ ���������� ��� ���������� � �������� ����� ���� ��� �� ���� ���������� 
     * 
     * � OpenOffice ����� �������� � ������������ � ���������� �������� $emp_init_arb - � ������ ������ ������ �� ����
     * ����� ������ ������������ ����� ������ � ��������� - ���� ���������� $variables['$emp_init_arb'] = false -- �������� �� ���������
     * 
     * @param type $variables
     * @return type 
     */
    public function prepareConditionsVar($variables) {
        $keys = array_keys($variables);
        foreach($keys as $i=>$key) {
            $keys[$i] = str_replace(array('_', '$'), array('_5f_', '_24_'), $key);
        }
        
        return $keys;
    }
    
    /**
     * ������ ������ � �������
     * ������ ��� ��� ����������� ���� {$name} -- � ������� ���� �� � ���� ������ � ����� ��� <text:p><text:p>{</text:p>$name<text:p>}</text:p></text:p>
     * ����� ��������� ����� ����������� ������ ������
     * 
     * @param string $content ������ �� �������
     * @return type 
     */
    public function clearDataVar($content) {
        $content = preg_replace("/<text([^>])+>\}<\/text([^>])+>/mix", "}", $content);
        $content = preg_replace("/<text([^>])+>\{<\/text([^>])+>/mix", "{", $content);
        
        return $content;
    }
     
    /**
     * ������ ��������� ������ �������, ������������ �������, �������� ��� ���������� �� ��������
     * 
     * @param string $content     ������ �� ������� (���� self::$content_file)
     * @param array  $variables   ���������� ������� array('$name' => '��������')
     * @return string
     */
    public function parseStructure($content, $variables) {
        list($keys, $vals) = $this->prepareVariables($variables);
        $content = $this->clearDataVar($content);
        $condition_keys = $this->prepareConditionsVar($variables);
        
        $dom = new DOMDocument('1.0');
        $dom->loadXML($content);
        $xpath = new DOMXPath($dom);
        
        foreach($condition_keys as $key=>$condition) {
            if(!is_bool($vals[$key])) continue;
            
            if($vals[$key] == false) {
                $find = '//text:p[@text:style-name= "'.$condition.'"]';
                
                $element = $xpath->query($find, $dom->documentElement);
                if($element->length > 0) {
                    for($i=0;$i<$element->length;$i++) {
                        $remove_element[] = $element->item($i);
                    }
                }
            }
        }
        if($remove_element) {
            foreach($remove_element as $element) {
                $parent = $element->parentNode;
                $parent->removeChild($element);
            }
            $content = $dom->saveXML();
        }
        
        $content = str_replace($keys, $vals, $content);
        return $this->clearEmptyVariables($content);
    }
    
    /**
     * ������ �� ������������ ����������
     * 
     * @param string $content ������ �� ������� (���� self::$content_file)
     * @return string 
     */
    public function clearEmptyVariables($content) {
        if(preg_match_all('/({\$.*?})/mix', $content, $matches)) {
            $vars = array_map('trim', $matches[1]);
            foreach($vars as $var) {
                $var = str_replace('$', '\$', $var);
                $content = preg_replace('/<text([^>])+>'. $var . '.*?<\/text([^>])+>/mix', "", $content);
            }
            return str_replace($vars, '', $content);
        }
        return $content;
    }
    
    /**
     * ����� ������� � �������
     * 
     * @deprecated
     * @param string $content ������ �� �������
     * @return array 
     */
    public function conditions($content) {
        if(preg_match_all("#{".self::MASK_CONDITION."=(.*?)}#", $content, $matches)) {
            return array_map('trim', $matches[1]);
        }
        return array();
    }
    
    /**
     * ����������� �� ODT � PDF
     * 
     * @param array  $replace     ���������� ��� ������ ������� array('$name' => '��������'). ���� false, �� ������� ������� �� ����� �����������
     * @param string $filename    �������� �����, ���� ���, �� ���� pdf �� ����� ��������
     */
    public function convert($replace = false, $filename = "") {
        if($this->prepareFile()) {
            
            if($this->initZipOpenFile()) {

                $this->getContentFile();
                
                if(is_array($replace)) {
                    $toContent = $this->parseStructure($this->getContent(), $replace);
                    $this->setContentFile($toContent);
                }
                
                $this->zip->close();
                $this->execConvert();
//                if($exec != '') {
//                    $this->log->writeln("unoconv �������� ������ -- {$exec}");
//                }
//                if(!file_exists($this->outputpath . $this->convert_file . ".pdf")) {
//                    $this->log->writeln("Template: {$this->doc}");
//                    $this->log->writeln("������ ����������� unoconv (�������� ����������������� unoconv ��� soffice) (file not exists -- {$this->outputpath}{$this->convert_file}.pdf)");
//                    unlink($this->file_path);
//                    return false;
//                }
//                $this->output = $this->getOutput();
                if($this->output == '') {
                    $this->log->writeln("������ ����������� ������� (�������� ������ �� ������������ ������� (�� �������� xml ������ �������) -- {$this->doc})");
                }
                $this->remove();
                
                if($filename != "" && $this->output != "") {
                    file_put_contents($this->getTmpFolder() . $filename, $this->output);
                }
            } else {
                $this->log->writeln("Template: {$this->doc}");
                $this->log->writeln("������ �������� ������ -- {$this->file_path}");
            }
        } else {
            $fname = $this->getFolder() . $this->doc;
            $this->log->writeln("������ ������������ ����� ������� -- {$fname}");
        }
    }
    
    /**
     * ������ �� ������ ����� �����������
     * 
     * @return string
     */
    public function getOutput() {
        $content = file_get_contents($this->outputpath . $this->convert_file . ".pdf");
        return $content;
    }
    
    /**
     * ������� �������� ��� ���������� ���������� ������� ������ FPDF::Output($name='', $dest='')
     * ������ ���������� ������ ����������� �� ���� �������� ��� ����� FPDF::Output(NULL, 'S')
     * 
     * @param type $a
     * @param type $b
     * @return type 
     */
    public function output($a = null, $b = 'S') {
        return $this->output;
    }
    
    /**
     * �������� ��������� ������ ����������� ��� ����������� 
     */
    public function remove() {
        if(file_exists($this->outputpath . $this->convert_file . ".pdf")) unlink($this->outputpath . $this->convert_file . ".pdf");
        if(file_exists($this->file_path)) unlink($this->file_path);
    }
    
    /**
     * ������ �������� �����������
     * 
     * @return string
     */
    public function execConvert() {
        $this->output = shell_exec("{$this->programm_exec} {$this->option_exec} -o {$this->outputpath} {$this->file_path} ");
//        $this->log->writeln($out);
        return $this->output;
    }
    
    /**
     * ������ ������ �� �������
     * 
     * @param string $content ������
     */
    public function setContent($content) {
        $this->_content = $content;
    }
    
    /**
     * ����� ������ �� �������
     * 
     * @return string
     */
    public function getContent() {
        return $this->_content;
    }
    
    /**
     * ������ ����� � ���������
     * 
     * @param string $folder ���� �� ����� � ���������
     */
    public function setFolder($folder) {
        $this->_folder = $folder;
    }
    
    /**
     * ���������� ���� �� ����� � ���������
     * 
     * @return string
     */
    public function getFolder() {
        return $this->_folder;
    }
    
    public function getTmpFolder() {
        if(!file_exists($this->_tmp)) {
            mkdir($this->_tmp, 0777);
        }
        return $this->_tmp;
    }
}


?>
