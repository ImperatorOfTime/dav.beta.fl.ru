<?php
/**
 * ����� ��������� ������.
 * ���������� ������ �������� � WebDav �� nginx, � �� ������������ ������ PUT, DELETE, MKCOL, COPY � MOVE
 */
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/webdav_proxy.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/CFileCache.php');

// !!! ����� ����� � �������� ���� '.jpeg': if($exp != 'jpg') $this->name = preg_replace('/\.[^.]+$/', '.jpg', $this->name);

class CFile
{
    /**
     * id �����
     *
     * @var integer
     */
    public $id;
    
    /**
     * ������� ������� ������ (file, file_projects, file_blogs � �.�.)
     * ���� ������������ ������� �� ��������� (�.�. ������������ ������ -- file_template), �� ��� ������� ������ � ������� file (��. �������), �.�.
     * ������ _������_ ���� ������ ������. ��� ��������� �������� ����� ����� ����.
     * ���������� ������ ��������� ������ �������.
     * (note: �� ��������� file_template, � �� file, �.�., ��������, ��� �������� �� ������ �������� �� ����� ������� �������.)
     *
     * @var string
     */
    public $table = 'file_template';

    
    /**
     * ��� ����� �� ��������� ����������
     *
     * @var string
     */
    public $tmp_name;
    
    /**
     * ��� �����
     *
     * @var string
     */
    public $name;
    
    /**
     * ���� ���������� ��������� �����
     *
     * @var string
     */
    public $modified;
    
	/**
     * ������ ����� � ������
     *
     * @var integer
     */
    public $size = 0;
    
    /**
     * ��������� ������������ �����
     *
     * @var array
     */
    public $image_size = array('width'=>0,'height'=>0, 'type' => 0);
    
    /**
     * ���� �� �����
     *
     * @var string
     */
    public $path = '';
    
    /**
     * ������ ������ ��� ������ � ������
     *
     * @var array
     */
    public $error = array();
    
    /**
     * ������������ ������ ����� ��� �������
     *
     * @var integer
     */
    public $max_size = 1048576;
    
    /**
     * �������������� ��������� ���������� ��� ����� 
     *
     * @var unknown_type
     */
    public $file_ext = "";

    /**
     * ������ � ����������� ������������
     *
     * @var array
     */
    public $allowed_ext = array();
    
    /**
     * ������������ ������� �������� ��� �������. ���� resize = 0, 'less' = 0 � ������� ��������
     * ������ ���������, �� ������ ������.
     * prevent_less = 1 - ��������� �������� ��������, ���� �� ������ ������ ���������
     *
     * @var array
     */
    public $max_image_size = array('width'=>0,'height'=>0, 'less' => 0);

    /**
     * ���� ����� ������ ������������ ����� ���-������� (���� 0, �� ������������ /upload/users)
     *
     * @var integer
     */
    public $server_root = 0;
    
    /**
     * ���� �� ��������� ��������(0-���,1-��)
     *
     * @var integer
     */
    public $resize=0;
    
    /**
     * ���� ���� ���������, �� ���� �� ��������� ��������������� ��� ������ ������� �������� $max_image_size (0-���, 1-��)
     *
     * @var integer
     */
    public $proportional = 0;
    
    /**
     * ���� ��������� ���������������, �� ������ ���� ����������
     *
     * @var integer:16
     */
    public $background = 0xFFFFFF;
    
    /**
     * �������� �������� ��� ������� (%)
     * Note: 100 �� 90 ������ ����� �� ����������, �� ��� ���� ����� ����������� �������� ����� ������� ������ ���������,
     *       ������� 90 �� ���������.
     *
     * @var integer
     */
    public $quality = 90; 

	/**
     * ���� �������� ����������� (4 ����)
     * 1 - ���� �������
     * 2 - ���������� ��������� (����.������������� �����)
     * 3 - ������ ��� �������� (��� ������� � ����� � �.�.)
     * 4 - ���� ��������� �� ����� (��. self::$antivirusSkip � self::MoveUploadedFile)
     * NULL - ���� �� ����������
     * 0000 - ���� �� �������
     *
     * var integer
     */
    public $virus = NULL;

    /**
     * ��� ������, ���� ���� �������
     *
     * var string
     */
    public $virusName = '';

    /**
     * ������������ ��� �����
     *
     * var string
     */
    public $original_name = '';

    /**
     * ������������� ���������, ��������, � ������ -- ��. ���������, ����������� ������ ����.
     *
     * @var string
     */
    public $src_id;

    /**
     * ������� ����, ��� ���� ����� ����������� ����� (������������ �� �� ���� ��������).
     *
     * @var mixed
     */
    public $small = 0;

    /**
     * ���-�� ������ �� ����. ���� ��� �������� 0, �� ���� ��������� ���������, ����� ����������� �� 1 ��� ���� � ���� �� ���������
     *
     * @var integer
     */
    public $count_links = 0;
    
    /**
     * ������� ����, ��� ���� (gif) �� ������ ����� ��������, �� ��������� �������� �����������
     *
     * @var mixed
     */
    public $disable_animate = false;

    /**
     * ����������� ������, ������� �� ����� ��������� �����������
     *
     * @var array
     */
    public $antivirusSkip = array();

    /**
     * @var boolean $exclude_reserved_wdc   ��������� �� ��������� ������� (��� �����-�� ��������� ������, ���� �� ��������� ������ ���).
     */
    public $exclude_reserved_wdc = false;
    
    /**
     * WebDAV-������
     * @var webdav_proxy
     */
    private $_wdp;
    
    /**
     * ���� true �� ������� ��������� ���� ��� ������ �����������
     * @var unlinkOff
     */
    public $unlinkOff;
    
    /**
     * �����������. �������������� ���������� ������ �� ������� $_FILES, ���� �� ����� ��� id �����.
     * ���� �� ����� ������������ �����, ��� ������� �����. ��������: users/te/temp/upload/new.jpg
     *
     * @param mixed $file_arr	- ������� ������� $_FILES, ���� �� ����� ��� id ����� �� ������� file
     */
    function __construct($file_arr = 0, $table = NULL) {
        if ($table)
            $this->table = $table;
        $this->_wdp = webdav_proxy::getInst($GLOBALS['WDCS']);
        if (is_array($file_arr)) { 
            $this->tmp_name = $file_arr['tmp_name'];
            $this->size = $file_arr['size'];
            $this->name = change_q_x($file_arr['name'], true);
            $this->original_name = change_q_x($file_arr['name'], true);
            if($file_arr['error'] != UPLOAD_ERR_OK) 
            {
              switch($file_arr['error']) {
                case UPLOAD_ERR_FORM_SIZE:
                case UPLOAD_ERR_INI_SIZE:
                  //$this->error[] = "������� ������� ���� ({$file_arr['error']})";
                  $this->error[] = "������� ������� ����. ";
                  break;
                case UPLOAD_ERR_NO_FILE:
                  $this->error[] = "�������� ���� ��� ��������";
                  break;
                default:
                  //$this->error[] = "���������� ��������� ���� ({$file_arr['error']})";
                  $this->error[] = "���������� ��������� ����";
              }
            }
        } elseif ($file_arr) {
            if (strcmp($file_arr,intval($file_arr)) == 0){
                $this->GetInfoById($file_arr);
            }
            else 
                $this->GetInfo($file_arr);
        }
        $this->unlinkOff = false;
    }
    
    /**
     * ����������. ���������� ��������� ���� ($this->tmp_name), ���� ���� �������� �����.
     *
     */
    function __destruct() {
        if ($this->tmp_name && !$this->unlinkOff) @unlink($this->tmp_name);
    }
    
    /**
     * �������������� ���������� ������ ������� �� ���� �� ����� �����
     *
     * @param string $file - ���� �� ����� (������������ ���������� upload)
     */
    function GetInfo($file) {
        if ( !($row = $GLOBALS['CFileCache']->get($file)) ) {
            $rows = CFile::selectFilesByFullName($this->table, $file);
            $row = $rows[0];
        }
        $this->initByRow($row);
    }
    
    /**
     * �������������� ���������� ������ ������� �� ���� �� id ����� � ������� file
     *
     * @param integer $id - id ����� � ������� file
     */
    function GetInfoById($id) {
        if ($id = (int)$id) {
            if ( !($row = $GLOBALS['CFileCache']->get($id)) ) {
                $rows = CFile::selectFilesById($this->table, $id);
                $row = $rows[0];
            }
        }
        $this->initByRow($row);
    }
    
    static function selectFilesBySrc($t_name, $values, $order_by = NULL, $add_where = NULL, $limit = NULL) {
        if(is_array($values)) {
            foreach($values as $k=>$v) { $values[$k] = intval($v); }
        } else {
            $values = intval($values);
        }
        $rows = DB::londiste('INKEYS')->select($t_name, 'src_id', $values, $order_by, $add_where, $limit);
        $GLOBALS['CFileCache']->put($rows);
        return $rows;
    }

    static function selectFilesById($t_name, $values, $order_by = NULL, $add_where = NULL, $limit = NULL) {
        if(is_array($values)) {
            foreach($values as $k=>$v) { $values[$k] = intval($v); }
        } else {
            $values = intval($values);
        }
        $rows = DB::londiste('INKEYS')->select($t_name, 'id', $values, $order_by, $add_where, $limit);
        $GLOBALS['CFileCache']->put($rows);
        return $rows;
    }
    
    static function selectFilesByFullName($t_name, $values, $order_by = NULL, $add_where = NULL, $limit = NULL) {
        $rows = DB::londiste('INKEYS')->select($t_name, '(path||fname)', $values, $order_by, $add_where, $limit);
        $GLOBALS['CFileCache']->put($rows);
        return $rows;
    }
    
    /**
     * �������������� ���������� ������ ������� ������ ���������� �� ����
     *
     * @param array $row   ������ �� ������� ������.
     */
    function initByRow($row) {
        if (!$row) return;
        $this->size = $row['size'];
        $this->image_size = array('width'=>$row['width'],'height'=>$row['height'], 'type' => $row['ftype']);
        $this->name = $row['fname'];
        $this->original_name = $row['original_name'] ? $row['original_name'] : $row['fname'];
        $this->path = $row['path'];
        $this->id = $row['id'];
        $this->virus = is_null($row['virus']) ? $row['virus'] : bindec($row['virus']);
        $this->virusName = $row['virus_name'];
        $this->modified = $row['modified'];
        $this->count_links = $row['count_links'];
        $this->src_id = @$row['src_id'];
    }
    
    /**
     * ���������� ���������� �����
     *
     * @param string $fname		��� �����. ���� �� ������, ������������ $this->name
     * @return string			���������� �����. �������� jpg 
     */
    function getext($fname = ''){
        if ($fname == '' && $this) $fname = $this->name;
        $filename = preg_split("/[.]+/",$fname);
        if(count($filename)==1) {
            $ext = 'dat';
        } else {
            $ext = strtolower(array_pop($filename));
        }
        return $ext;
    }
    /**
     * ���������, �� �������� �� ���������� ����� ������������� �������.
     * ���� �������� - �� ���������� ���������� �� .dat
     */
    function cyrillicExtension ($ext) {
        return preg_match('/[�-��-�]/', $ext) ? "dat" : $ext;
    }
    
    
    /**
     * �������� �������� ����� (����� �������� ����� �� �������)
     *
     * @param string $dir      ���������� ��������
     * @param string $postfix  ������� (����������� � ����� �������� �����)
     * @param string $prefix   ������� (����������� � ������ �������� �����)
     * @return string ����� ��� �����
     */
    function secure_tmpname($dir = null, $postfix = '.temp', $prefix = 'f_') {
        // validate arguments
        if (! (isset($postfix) && is_string($postfix))) {
            return false;
        }
        if (! (isset($prefix) && is_string($prefix))) {
            return false;
        }
        if (! isset($dir)) {
            return false;
        }
        
        $new_name = $dir . uniqid($prefix . str_pad(mt_rand(0,999), 3, '0', STR_PAD_LEFT)) . $postfix;
        return $new_name;
    }
    /**
     * ���������� ����������� ���� � ����� �����
     *
     * @param string  $dir ����� �������
     * @param boolean $virusScan ���� TRUE, �� ��������� �� ������
     * @param string  $destFileName ���� �� �����, �� ��������� ���� � ���� ������
     * @return string �������� ����������� �����
     */
    function MoveUploadedFile($dir, $virusScan=TRUE, $destFileName = ''){
        if (@$this->error[0]) return NULL;
        $this->path = ($this->server_root) ? $dir : "users/".substr($dir, 0, 2)."/".$dir."/";
        $dir = $this->path;

        if ( !$virusScan ) {
            $this->virus = 16;
        }

        /*if (!file_exists($dir)) {
        	mkdir($dir, 0777,1);
        }*/
        
        if ($this->size > 0){
            $ext = strtolower($this->getext($this->name));
            if ( strlen($destFileName) > 0) {
                $ext = strtolower($this->getext($destFileName));
            }
            if ($this->size > $this->max_size){
                $this->error = "C������ ������� ����. ";
                return NULL;
            }
            
            if ( in_array($ext, $GLOBALS['disallowed_array']) && $ext != $this->file_ext ) {
                $this->error = "������������ ��� �����. ";
                return NULL;
            }

            if(count($this->allowed_ext) && !in_array($ext, $this->allowed_ext)) {
                $this->error = "������������ ��� �����. ";
                return NULL;
            }

            if (!$this->CheckPath($this->path, true)) { // ��������� ����������, ���� ���� - �������.
            	$this->MakeDir($this->path);
            }

            if ($this->CheckPath($dir, true))
            {
                $tmp = $this->secure_tmpname($dir,".".$ext);
                if (!$tmp) {
                    $this->error = "���������� ������ �������. ";
                    return false;
                }
                if ( strlen($destFileName) == 0) {
                    $this->name = substr_replace($tmp,"",0,strlen($dir));
                } else {
                    $this->name = $destFileName;
                }
                if(in_array($ext, $GLOBALS['graf_array']) && $this->disable_animate) {
                    $this->getDisabledAnimateGIF();
                }
                if (!isNulArray($this->max_image_size) && in_array($ext, $GLOBALS['graf_array'])) {
    
                    $this->_getImageSize($this->tmp_name);
                    $this->validExtensionFile($this->image_size['type']);
                    $ext = strtolower($this->getext($this->name));
                    if ( in_array($ext, $GLOBALS['disallowed_array']) && $ext != $this->file_ext ) {
                        $this->error = "������������ ��� �����. ";
                        return NULL;
                    }
                    if(count($this->allowed_ext) && !in_array($ext, $this->allowed_ext)) {
                        $this->error = "������������ ��� �����. ";
                        return NULL;
                    }
                    $prevent_less = ($this->max_image_size['prevent_less'] &&
                                ($this->image_size['width'] < $this->max_image_size['width']
                                    || $this->image_size['height'] < $this->max_image_size['height']));
                    
                    if ($this->resize && ($this->image_size['width'] > $this->max_image_size['width']
                            || $this->image_size['height'] > $this->max_image_size['height']) && !$prevent_less) {

                        $src = $this->tmp_name;
                        $dest = $this->tmp_name;
                        
                        $format = strtolower(substr($this->image_size['mime'], strpos($this->image_size['mime'], '/')+1));
                        $icfunc = "imagecreatefrom" . $format;
                        $imfunc = "image" . $format;
                        if (!function_exists($icfunc) || !function_exists($imfunc)) {
                            $this->error = "������������ ������ �����. ". $imfunc;
                            $this->name = "";
                        } else {

                            $x_ratio = $this->max_image_size['width'] / $this->image_size['width'];
                            $y_ratio = $this->max_image_size['height'] / $this->image_size['height'];
    
                            $ratio       = min($x_ratio, $y_ratio);
                            if ($ratio == 0) $ratio = max($x_ratio, $y_ratio);
                            $use_x_ratio = ($x_ratio == $ratio);
    
                            $new_width   = $use_x_ratio  ? $this->max_image_size['width']  : floor($this->image_size['width'] * $ratio);
                            $new_height  = !$use_x_ratio ? $this->max_image_size['height'] : floor($this->image_size['height'] * $ratio);
                            $new_left    = $use_x_ratio  ? 0 : floor(($this->max_image_size['width'] - $new_width) / 2);
                            $new_top     = !$use_x_ratio ? 0 : floor(($this->max_image_size['height'] - $new_height) / 2);
    
                            $isrc = $icfunc($src);
    						
                            if ($isrc) {
                            	
                                if ($this->proportional){
                                    if ($this->topfill) {
                                    	if($this->image_size['type']==3) {
                                            $idest = $this->imageResizeAlpha($isrc, $new_width, $new_height);
                                        } else {
	                                        $idest = imagecreatetruecolor($this->max_image_size['width'], $this->max_image_size['height']);
	                                        imagefill($idest, 0, 0, $this->background);
	                                        imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
                                        				   $new_width, $new_height, $this->image_size['width'], $this->image_size['height']);
                                        }
                                    } elseif($this->crop) {
                                        if($this->image_size['type']==3) {
                                            $idest = $this->imageResizeAlpha($isrc, $new_width, $new_height);
                                        } else {
                                            $newWidth      = $this->max_image_size['width'];
                                            $newHeight     = $this->max_image_size['height'];
                                            $optionArray   = $this->_getImageOptimalCrop($newWidth, $newHeight);
                                            $optimalWidth  = $optionArray['optimalWidth'];
                                            $optimalHeight = $optionArray['optimalHeight'];
                                            $imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
                                            imagecopyresampled($imageResized, $isrc, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->image_size['width'], $this->image_size['height']);
                                            $isrc = $imageResized;
                                            $cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
                                            $cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );
                                            $idest = imagecreatetruecolor($newWidth , $newHeight);
                                            imagecopyresampled($idest, $isrc, 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
                                        }
                                    } else {
                                    	if($this->image_size['type']==3) {
                                            $idest = $this->imageResizeAlpha($isrc, $new_width, $new_height);
                                    	} else { 
                                    		$idest = imagecreatetruecolor($new_width, $new_height);
	                                        imagefill($idest, 0, 0, $this->background);
	                                        imagecopyresampled($idest, $isrc, 0, 0, 0, 0,
	                                        $new_width, $new_height, $this->image_size['width'], $this->image_size['height']);
                                    	}
                                    }
                                } else {
                                    $idest = imagecreatetruecolor($this->max_image_size['width'], $this->max_image_size['height']);
                                    imagefill($idest, 0, 0, $this->background);
                                    imagecopyresampled($idest, $isrc, 0, 0, 0, 0,
                                    $this->max_image_size['width'], $this->max_image_size['height'],
                                    $this->image_size['width'], $this->image_size['height']);
                                }
                                if ($this->image_size['type'] == 2) imagejpeg($idest, $dest, $this->quality);
                                else {
                                    if($this->image_size['type']!=1) {
                                        $imfunc($idest, $dest);
                                    }
                                }
                                $this->_getImageSize($dest);
                                $this->size = filesize($dest);
                                imagedestroy($isrc);
                                imagedestroy($idest);
                                unset($isrc);
                                unset($idest);
                            } else {
                                $this->error[] = "�� ���� �������� ������ �����. ";
                                $this->name = "";
                            }
     
                        }
    
                    }
                    elseif ((!$this->resize && ((!$this->max_image_size['less'] && 
                            ($this->image_size['width'] != $this->max_image_size['width'] 
                            || $this->image_size['height'] != $this->max_image_size['height']))
                            || ($this->max_image_size['less'] && ($this->image_size['width'] > $this->max_image_size['width']
                            || $this->image_size['height'] > $this->max_image_size['height']))))
                                ||
                            $prevent_less
                                ) {
                        $this->error[] = "������������ ������� �����. ";
                        $this->name = "";
                    }
                }
                if (isNulArray($this->error)){
                    $this->_upload($this->tmp_name);
                }
            }
            else
            {
                $this->error[] = "���������� ��������� ����. ";
            }
        } else $this->name = "";
    
        return ($this->name);
    }
    
    /**
     * �������� ������ � ����
     *
     * @param string $path     ���� � �����
     * @param string $content  ������
     * @return boolean true - ���� ������ ������ ������, false - ������
     */
    public function putContent($path, $content) {
        if(!$this->CheckPath(dirname($path), true)) {
            $this->MakeDir(dirname($path));
        }
        if($this->_wdp->put('/'.$path, $content, $this->exclude_reserved_wdc)) {
            if($this->name == '') {
                $this->_autoFileParams($path, strlen($content));
            }
            $this->_addFileParams();
            return true;
        }
        return false;
    }
    /**
     * �������� ������ ��� ������ � ������� �� ������ (���� � �����, ������ �����)
     *
     * @param string  $path    ���� � �����
     * @param integer $size    ������ �����
     */
    public function _autoFileParams($path, $size) {
        $this->name = $this->original_name = basename($path);
        $this->size = $size;
        $this->path = dirname($path)."/";
        $this->virus = null;
        $this->image_size = array('type'=>0, 'width'=>0, 'height'=>0);     
    }
    
    /**
     * �������� ����� ����� WebDav. ��� ����� � ���� ���������� �������� � $this->name, $this->path.
     *
     * @param string $from ��� ���������
     * @return boolean true - ��� ������ ������, false - �� ���������� ���������
     */
    private function _upload($from) {
        $ext = $this->getext();
        if (in_array($ext, $GLOBALS['graf_array']))
            $this->_getImageSize($from);
        $this->validExtensionFile($this->image_size['type']);
        if($this->prefix_file_name != "") 
            $this->name = $this->prefix_file_name . $this->name;
            
        if(!$this->CheckPath($this->path, true)) {
            $this->MakeDir($this->path);
        }
        
        if ($this->_wdp->put_file('/'.$this->path.$this->name, $from, $this->exclude_reserved_wdc)) {
            $this->_addFileParams();
            $this->fireEvent('create');
            return true;
        }
        return false;
    }
    
    /**
     * �������� �� ���������� ���� ����� � ���������
     *
     * @param integer $type ��� ����� (1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF(orden de bytes intel), 8 = TIFF(orden de bytes motorola), 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF, 15 = WBMP, 16 = XBM)
     */
    public function validExtensionFile($type) {
        $exp = $this->getext();
        switch($type) {
            case 1:
                if($exp != 'gif') $this->name = preg_replace('/\.[^.]+$/', '.gif', $this->name);
                break;
            case 2:
                if($exp != 'jpg') $this->name = preg_replace('/\.[^.]+$/', '.jpg', $this->name);
                break;
            case 3:
                if($exp != 'png') $this->name = preg_replace('/\.[^.]+$/', '.png', $this->name);
                break;
            case 4:
            case 13:
                if($exp != 'swf') $this->name = preg_replace('/\.[^.]+$/', '.swf', $this->name);
                break;
            case 6:
                if($exp != 'bmp') $this->name = preg_replace('/\.[^.]+$/', '.bmp', $this->name);
                break;   
                
        }    
    }
    
    /**
     * ���������� ���������� � ������������ �����
     *
     */
    protected function _addFileParams(){
        if (!$this->modified) {
			$this->modified = date("Y-m-d H:i:s");
		}
        // �� file_template ��� ����� �������, ������� ���������� � file, �� �� �� ������ id.
        $table = ($this->table == 'file_template' ? 'file' : $this->table);
        $this->id = $GLOBALS['DB']->insert($table, array(
			'fname'      => $this->name,
			'original_name'      => $this->original_name,
			'modified'   => $this->modified,
			'size'       => $this->size,
			'path'       => $this->path,
			'ftype'      => $this->image_size['type'],
			'width'      => $this->image_size['width'],
			'height'     => $this->image_size['height'],
            'virus'      => is_null($this->virus) ? $this->virus : sprintf("%04b", $this->virus),
            'virus_name' => $this->virusName,
            'src_id' => $this->src_id
		), 'id');
    }
    
    /**
     * ���������� ���������� ������������ �����
     * ���� �� ��������, �� ����� ������� ���� ��������� � � ����� ������� ����� ��� ����������,
     * �� �������� ������ ����� ������� $file->table = 'file_template'.
     *
     * @param array $params ��������� ���������� [size=>1, path=>,...] (��. ������� file)
     */
    function updateFileParams($params, $nomod = false) {
        global $DB;
        if (!$this->id || !$params || !is_array($params)) {
			return;
		}
        if (!$this->modified) {
			$this->modified = date("Y-m-d H:i:s");
		}
        $params['modified'] = $this->modified;
        $DB->update($this->table, $params, 'id = ?', $this->id);
		return $DB->error;
    }

    /**
     * ������ ����� ������ � ��. ������������ ������ ��� ��������������� ������� ������ ������ � ������� file
     *
     * @param string $from	��� ����� � ����� ������������ users/ (�������� te/temp/upload/test.jpg)
     * @return integer|boolean		���������� id ����� � ������ ������ ��� false, ���� ����� �� ����������.
     */
    function DBImport($from){
        if (!file_exists(ABS_PATH.'upload/'.$from)) return false;
        $this->size = filesize(ABS_PATH.'upload/'.$from);
        if ($this->size){
            $this->_getImageSize(ABS_PATH.'upload/'.$from);
            $this->path = dirname($from)."/";
            $this->name = basename($from);
            $this->_addFileParams();
            return $this->id;
        }
        return false;
    }
    
    /**
     * ��������� ���� SWF ����������� 7z, ����� �������� ��� ������
     * 
     * @param string $file
     * @return boolean
     * @todo ��� �������, ��� ������ � PHP ������� ��������� ����� getimagesize -- ����� ������ ������� ������
     */
    private function _lzmaSWF($file) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/LZMA_SWF.php';
        $lzma = new LZMA_SWF($file);
        if( $lzma->isLZMACompress() ) {
            $lzma->getInformationSWF();
            return $lzma->info;
        }
        return false;
    }
    
    /**
     * ����� ������� ������������ �����������
     *
     * @param resource $file  ����������� �����������
     */
    public function _getImageSize($file){
        $img = getimagesize($file);
        if(!$img) { // ���� �� ���� ���������� ������ �������� �� SWF �� ��� ������������ 7z
            $img = $this->_lzmaSWF($file);
        }
        $ret['width'] = ($img[0])?$img[0]:0;
        $ret['height'] = ($img[1])?$img[1]:0;
        $ret['type'] = ($img[2])?$img[2]:0;
        $ret['mime'] = ($img['mime'])?$img['mime']:0;
        $this->image_size = $ret;
    }
    
    /**
     * �������� �����
     * ����� ������� ����� ���������, ��� $file->table ������� ������.
     *
     * @param string $dest      ��� �����
     * @param boolean $new_name ������ ����� ��� ����� ��� ���(true - ��, false - ���)
     * @return boolean true - ���� �������� ������ �������, false - �� �������
     */
    public function _remoteCopy($dest, $new_name = true){
        if(!$this->CheckPath(dirname($dest), false)) {
            $this->MakeDir(dirname($dest));
        }
        if ($this->_wdp->copy_file('/'.$this->path.$this->name, '/'.$dest, true)) {
            $this->fireEvent('copy', $dest);
            $tmp_name = $this->name;
            $tmp_path = $this->path; 
            $this->path = dirname($dest)."/";
            $this->name = basename($dest);
            $this->_addFileParams();
            if (!$new_name){
                $this->name = $tmp_name;
                $this->path = $tmp_path;
            }
            return true;
        }
        return false;
    }
    
    
    
    
    
    /**
     * �������� ����� ����� ��� �������� � ��
     */
    public function copyFileTo($dest, $create_dir = false)
    {
        if ($create_dir) {
            if(!$this->CheckPath(dirname($dest), false)) {
                $this->MakeDir(dirname($dest));
            }
        }
        
        if ($this->_wdp->copy_file(
                "/{$this->path}{$this->name}", 
                "{$dest}", true)) {
                    
            return true; 
        }
        
        return false;
    }






    /**
     * ������� ���� �� ��� id ��� ���� �� �����
     *
     * @param string $id		id ����� � ������� file
     * @param string $dir		���������� ����� (������������ �����, ��� ������� �����). (�����������, ���� id = 0) ��������: users/te/temp/
     * @param string $fname		��� �����. (�����������, ���� id = 0)
     */
    function Delete($id, $dir = "", $fname = ""){
        if ($id = intval($id)) {
            $where = 'id = ' . $id;
            if( !($rows = $GLOBALS['CFileCache']->get($id)) )
                $rows = self::selectFilesById($this->table, $id);
            if(isset($rows[0])) {
                $dir = $rows[0]['path'];
                $fname = $rows[0]['fname'];
                $count_links = intval($rows[0]['count_links']);
            } else {
                $dir = $rows['path'];
                $fname = $rows['fname'];    
                $count_links = intval($rows['count_links']);
            }
        } else if ($dir && $fname) {
            if ( !($rows = $GLOBALS['CFileCache']->get($dir.$fname)) ) {
                $rows = CFile::selectFilesByFullName($this->table, $dir.$fname);
            }
            if(isset($rows[0])) {
                $count_links = intval($rows[0]['count_links']);
            } else {
                $count_links = intval($rows['count_links']);
            }
            $where = "fname = '".pg_escape_string($fname)."' AND path = '".pg_escape_string($dir)."'";
		}

        if ($count_links) {
            $GLOBALS['DB']->query("UPDATE {$this->table} SET count_links = count_links-1 WHERE {$where}");
            if ($id) { 
                $GLOBALS['CFileCache']->del($id); 
            } else {
                $GLOBALS['CFileCache']->del($dir.$fname);
            }
        } else {
            if ($fname && $this->_wdp->delete('/'.$dir.$fname, $this->exclude_reserved_wdc)){
                $GLOBALS['DB']->query("DELETE FROM {$this->table} WHERE {$where}");
                
                if ($id) { 
                    $GLOBALS['CFileCache']->del($id); 
                } else {
                    $GLOBALS['CFileCache']->del($dir.$fname);
                }
                
                $this->fireEvent('delete', $dir . $fname);
            }
        }
    }
    
    /**
     * ��������������� ���� �� ������� �������
     * ���� �� ��������, �� ����� ������� ���� ��������� � � ����� ������� ����� ��� ����������,
     * �� �������� ������ ����� ������� $file->table = 'file_template'.
     *
     * @param string $to	��� ����� ������ � ����� ����������� (������������ �����, ��� ������� �����) ��������: users/te/temp/upload/new.jpg
     * @return boolean		���������� true, ���� �������������� ������ �������
     */
    function Rename($to) {
        if ($to && $this->name) {
            if(!$this->CheckPath(dirname($to), true)) {
                $this->MakeDir(dirname($to));
            }
            if ($this->_wdp->move('/'.$this->path.$this->name, '/'.$to, true)) {
                $this->name = basename($to);
                $this->path = dirname($to).'/';
                $this->updateFileParams(array( 'path'=>$this->path, 'fname'=>$this->name ));
                $GLOBALS['CFileCache']->del($this->id);
                return true;
            }
        }
        return false;
    }
    
    /**
     * ������� ���������� �� �������
     *
     * @param string $path		���� � ���������� (�������� users/te/temp).
     * @return boolean ���������� true, ���� �������� ������ �������
     */
    function MakeDir($path) {
        $parent_dir = dirname($path);
        if (!($ok = $this->CheckPath($parent_dir, false))) {
            $ok = $this->MakeDir($parent_dir);
        }
        if($ok) {
            $ok = $this->_wdp->mkcol('/'.$path.'/');
        }
        return $ok;
    }
    
    /**
     * ��������� ���������� �� ������������� 
     *
     * @param string $path	           ���� �� ���������� (������������ �����, ��� ������� �����) ��������: users/te/temp/
     * @param boolean $dont_check_put    true: �� ���������, ���� webdav ����� �� nginx � ������� create_full_put_path, ������ ������� true.
     * @return boolean			���������� true, ���� ���������� ����������
     */
    function CheckPath($path, $dont_check_put = false) {
        if (!$path) $path = $this->path;
        return $this->_wdp->check_file($path, $dont_check_put);
    }
    
    /**
     * ������� ���������� � ��������������� � ������� � ���
     *
     * @param string $path		���� �� ���������� (������������ �����, ��� ������� �����) ��������: users/te/temp/
     * @param boolean $check	������������ �� ��������, ����� �������� �� �������� ������ ��� (��������� ������� ������ � �������� ���������� users/??/)
     * @return boolean			true, ���� �������. false - ���� �� �������.
     */
    function DeleteDir($path = '', $check = true){
        if (!$path) $path = $this->path;
        if ($check){
            $path_arr = explode("/",$path);
            if ($path_arr[0] != 'users') return false;
            if ($path_arr[1] == '.' || $path_arr[1] == '..') return false;
            if (!$path_arr[2]) return false;
        }
        if ($path && $this->_wdp->delete('/'.$path)) {
            $ppath = str_escape($path, '%_', '!');
            $GLOBALS['DB']->query("DELETE FROM {$this->table} WHERE path LIKE '{$ppath}%' ESCAPE '!'");
        }
        return true;
    }
    
    
    
    /**
     * ������� ���������� �� ��������� ���������� ���������
     * � �� ����� �� �����������
     * 
     * @param type $path
     * @return boolean
     */
    public function deleteFromTempDir($path)
    {
        $path = str_replace('.', '', $path);
        $path = str_replace('//', '', $path);
        $path = trim($path, '/');
        $path_arr = explode('/', $path);
        
        if (empty($path_arr) || 
            (count($path_arr) == 1 && 
             $path_arr[0] == 'temp')) {
            
            return false;
        }
        
        if ($path_arr[0] != 'temp') {
            $path = "temp/{$path}";
        }
        
        return $this->_wdp->delete("/{$path}/");
    }




    /**
     * ��������������� ���������� � ��������������� � ������� � ���. ������ ��� ����� ������!!!
     *
     * @param string $new_login	����� �����
     * @param string $old_login	������ �����
     * @return boolean			true, ���� �������. false - ���� �� �������.
     */
    function MoveDir($new_login, $old_login){
        $udir = 'users/';
        $old_path  = $udir.substr($old_login, 0, 2).'/'.$old_login.'/';
        $new_ppath = $udir.substr($new_login, 0, 2).'/';
        $new_path  = $new_ppath.$new_login.'/';
        if(!$this->CheckPath($old_path, false)) {
            return true;
        }
        if (!$this->CheckPath($new_ppath, false)) {
        	$this->MakeDir($new_ppath);
        }
        if ($this->_wdp->move('/'.$old_path, '/'.$new_path, 0)) {
            $pold_path = str_escape($old_path, '%_', '!');
            $GLOBALS['DB']->query(
              "UPDATE {$this->table}
                  SET path = ?::text||substring (path, '/([^/]*)/$')||'/'
                WHERE path LIKE '{$pold_path}%' ESCAPE '!'",
              $new_path
            );
            return true;
        }
        return false;
    }
    
    /**
     * ���������� ������, ���������� ��� ������ ��������� ����� ($this)
     *
     * @param string $glue		����������� ������ � ������
     * @return string			������ ������, ����������� $glue
     */
    function StrError($glue = " "){
        if(is_array($this->error))
            return implode($glue,$this->error);
        return $this->error;
    }
   
   /**
    * ���������� ����� �� ������ ��������
    * NB! ��� ������������ ������ �� �������� 
    *
    * @param string $destanation   ��� ������������� �����
    * @param array  $tn_image_size ������ �����
    * @param boolean  $allow_less ��������� ��� ��� ������ �������� ������ ����������
    * @return boolean false, ���� �� ���������
    */
    function img_to_small($destanation, $tn_image_size, $allow_less = false){
             
        $src = $this->tmp_name;
        $dest = $this->tmp_name."_sm";

        if (!file_exists($src)) {
            $this->error[] = "����������� ���� �� ������!";
            return false;
        }
       
        /*if (file_exists($dest)) {  ��� �������� ����� ��������
            $size_sm = getimagesize($dest);
            if ((!$width || $size_sm[0] <= $width) && (!$height || $size_sm[1] <= $height)){
                return true;
            }

        }*/

        if ($this->image_size === false) return false;
        $width = $tn_image_size['width'];
        $height = $tn_image_size['height'];
        // �� ���������, ���� ����������� ������ ������ ��������, ������ ������� �����

        if($allow_less && ($this->image_size['width'] < $width || $this->image_size['height'] < $height)) {
            $this->error[] = "���� �� ������������� �������� ��������!";
            return false;
        }
        
        if ((!$width || $this->image_size['width'] <= $width) && (!$height || $this->image_size['height'] <= $height)){
            $tmp_name = $this->name;
            $ret = $this->_remoteCopy($this->path.$destanation, false);
            $this->name = $tmp_name;
            return $ret;
        }

        // ���������� �������� ������ �� MIME-����������, ���������������
        // �������� getimagesize, � �������� ��������������� �������
        // imagecreatefrom-�������.
        $format = strtolower(substr($this->image_size['mime'], strpos($this->image_size['mime'], '/')+1));
        $icfunc = "imagecreatefrom" . $format;
        $imfunc = "image" . $format;
        if (!function_exists($icfunc) || !function_exists($imfunc)) return false;

        $x_ratio = $width / $this->image_size['width'];
        $y_ratio = $height / $this->image_size['height'];

        $ratio       = min($x_ratio, $y_ratio);
        if ($ratio == 0) $ratio = max($x_ratio, $y_ratio);
        $use_x_ratio = ($x_ratio == $ratio);

        $new_width   = $use_x_ratio  ? $width  : floor($this->image_size['width'] * $ratio);
        $new_height  = !$use_x_ratio ? $height : floor($this->image_size['height'] * $ratio);

        $isrc = $icfunc($src);
		
        if ($isrc)
        {
			
        	
        	if ($this->proportional){
            	if($this->image_size['type']==3) {
                    $idest = $this->imageResizeAlpha($isrc, $new_width, $new_height);
            	} else {                            
	                $idest = imagecreatetruecolor($new_width, $new_height);
	                imagefill($idest, 0, 0, $this->background);
	                imagecopyresampled($idest, $isrc, 0, 0, 0, 0,
	                $new_width, $new_height, $this->image_size['width'], $this->image_size['height']);
            	}
            } else {
            	if($this->image_size['type']==3) {
                    $idest = $this->imageResizeAlpha($isrc, $new_width, $new_height);
            	} else {   
	                $idest = imagecreatetruecolor($width, $height);
	                imagefill($idest, 0, 0, $this->background);
	                imagecopyresampled($idest, $isrc, 0, 0, 0, 0,
	                $width, $height, $this->image_size['width'], $this->image_size['height']);
            	}    
            }

            if ($this->image_size['type'] == 2) imagejpeg($idest, $dest, $this->quality);
            else $imfunc($idest, $dest);
            imagedestroy($isrc);
            imagedestroy($idest);
            unset($isrc);
            unset($idest);
            $tmp_name = $this->name;
            $tmp_size = $this->size;
            $this->name = $destanation;
            $this->size = filesize($dest);
            $ret = $this->_upload($dest);
            $this->name = $tmp_name;
            $this->size = $tmp_size;
            unlink($dest);
            return $ret;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * ������� ��� ������ � ������������� PNG, ��� ���������� Alpha - ������ ��� ���������� ��������
     *
     * @param resource $src   ������ ��������
     * @param resource $ovr   ���������� ������ ������� imageResizeAlpha()
     * @param integer  $ovr_x ������ ���������� �����
     * @param integer  $ovr_y ������ ���������� �����
     * @param integer  $ovr_w ������ ��������� ����� (���� false �� �����������)
     * @param integer  $ovr_h ������ ��������� ����� (���� fakse �� �����������)
     */
    function imageComposeAlpha(&$src, &$ovr, $ovr_x, $ovr_y, $ovr_w = false, $ovr_h = false)
	{
		if( $ovr_w && $ovr_h )
		$ovr = imageResizeAlpha( $ovr, $ovr_w, $ovr_h );
		
		/* Noew compose the 2 images */
		imagecopy($src, $ovr, $ovr_x, $ovr_y, 0, 0, imagesx($ovr), imagesy($ovr) );
	}
    /**
	* Resize a PNG file with transparency to given dimensions
	* and still retain the alpha channel information
	*/
	function imageResizeAlpha(&$src, $w, $h)
	{
		/* create a new image with the new width and height */
		$temp = imagecreatetruecolor($w, $h);
		
		/* making the new image transparent */
		//$background = imagecolorallocate($temp, 0, 0, 0);
		//ImageColorTransparent($temp, $background); // make the new temp image all transparent
		//ImageSaveAlpha($temp, false);
		
		imagealphablending($temp, true);
		imagesavealpha($temp,true);
		$transparent = imagecolorallocatealpha($temp, 255, 255, 255, 127);
		
		imagefilledrectangle($temp, 0, 0, $w, $h, $transparent);
        imagefill($temp, 0, 0, $transparent); 
		//imagealphablending($temp, false); // turn off the alpha blending to keep the alpha channel
		
		/* Resize the PNG file */
		/* use imagecopyresized to gain some performance but loose some quality */
		//imagecopyresized($temp, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));
		/* use imagecopyresampled if you concern more about the quality */
		imagecopyresampled($temp, $src, 0, 0, 0, 0, $w, $h, $this->image_size['width'], $this->image_size['height']);
		return $temp;
	}
    
    /**
     * ����������� � ������� mime-��� �����.
     * 
     * @param string $fname   ������ ��� ����� (������� ����, ������������ �����, ��� ������� �����) ��������: users/te/temp/xoxo.zip
     * @return boolean|string false - ���� �� ������� �������� mime-���, ��� ����������� mime-��� �����
     */
    public function getContentType($fname = '') {
        if(!$fname) $fname = $this->path.$this->name;
        if(!$fname) return false;
        return $this->_wdp->get_content_type($fname);
    }
    
    /**
     * ������� ����� � ������, ���������� � ���� ��� ���������
     * 
     */
    public function removeDeleted(){
        $ret = $GLOBALS['DB']->rows("SELECT id FROM {$this->table} WHERE deleted = true");
        if ($ret) {
			foreach ($ret as $row) {
				$this->Delete($row['id']);
			}
        }
    }

	/**
     * ��������� ���� ����������� (drweb)
     * 
     * @param  boolean  $delete  ���� TRUE, �� ������ ���������� ����
     * @return integer           ��� �������� (��. self::$virus) ��� FALSE � ������ ������
     */
    public function antivirus($delete = TRUE) {
        global $DB;
        if ( !defined('DRWEB_DEAMON') && !defined('DRWEB_DUMMY') ) {
            return FALSE;
        }
        $path = pathinfo($this->name);
        if ( in_array(strtolower($path['extension']), $this->antivirusSkip) ) {
            $DB->update('file', array( 'virus' => '1000' ), 'id = ?', $this->id);
            return 8;
        }
        $name = '';
        if ( defined('DRWEB_DEAMON') ) {
            $file = DRWEB_STORE . '/' . $this->path . $this->name;
            exec(DRWEB_DEAMON . ' -n' . DRWEB_HOST . ' -p' . DRWEB_PORT . ' -rv -f"'.$file.'"', $shellText, $res);
            if ( $res > 0 ) {
                $code  = 0;
                $code += ( $res & 1 );
                $code += ( $res & 6 ) ? 2 : 0;
                $code += ( $res > 7 ) ? 4 : 0;
                if ( $code == 1 ) {
                    if (preg_match('/Known virus/', $shellText[1]))	{
                        $name = trim($shellText[2]);
                    }
                    if ( $delete ) {
                        $r = $DB->row("SELECT fname, path FROM {$this->table} WHERE id = ?", $this->id);
                        if ($r['fname']) {
                            $this->_wdp->delete('/'.$r['path'].$r['fname']);
                        }
                    }
                }
            }
        }
        $this->virus = $code;
        $this->virusName = $name;
        $DB->update($this->table, array ( 'virus' => sprintf("%04b", $code), 'virus_name' => $name ), 'id = ?', $this->id);
        return $code;
    }

    /**
     * ������ �������� ��� ����������� � webdav
     *
     * @param    string     $to               ���� ��������� � � ����� ������
     * @param    integer    $newWidth         ������ � ��������
     * @param    integer    $newHeight        ������ � ��������
     * @param    string     $option           ��� �������: portrait - ��������� ������
     *                                                     landscape - ��������� ������
     *                                                     crop - ��������� �� ������ ��������
     *                                                     auto - �������������� �����
     *                                                     cropthumbnail - ��������� � �������� �� ������
     * @param    boolean    $savePngAlpha     ��������� ����� ����� ��� PNG ������
     * @return    boolean                     true - ������� ������, false - �� ������
     */
    public function resizeImage($to, $newWidth, $newHeight, $option="auto", $savePngAlpha = false, $table = null) {
        if(!in_array($this->image_size['type'], array(1,2,3))) {
            // ������������ ������ �����
            return false;
        }

        $tmp_name = uniqid();
        $file = "/tmp/{$tmp_name}";
        file_put_contents($file, file_get_contents(WDCPREFIX_LOCAL.'/'.$this->path.$this->name));
        
        /*switch($this->image_size['type']) {
            case 1:
                $img = imagecreatefromgif($file);
                break;
            case 2:
                $img = imagecreatefromjpeg($file);
                break;
            case 3:
                $img = imagecreatefrompng($file);
                break;
            default:
                $img = false;
                break;
        }

        if(!$img) {
            // ������ �������� �����
            return false;
        }

        // ����������� ����� �������� ������ � ������ ��������
        switch ($option) {
            case 'portrait':
                $optimalWidth = $this->_getImageSizeByFixedHeight($newHeight);
                $optimalHeight= $newHeight;
                break;
            case 'landscape':
                $optimalWidth = $newWidth;
                $optimalHeight= $this->_getImageSizeByFixedWidth($newWidth);
                break;
            case 'auto':
                $optionArray = $this->_getImageSizeByAuto($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
            case 'crop':
                $optionArray = $this->_getImageOptimalCrop($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
        }

        if ($this->image_size['type'] && $savePngAlpha) {
            $imageResized = $this->imageResizeAlpha($img, $optimalWidth, $optimalHeight);
        } else {
            $imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
            imagecopyresampled($imageResized, $img, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->image_size['width'], $this->image_size['height']);
        }

        if ($option == 'crop') {
            $cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
            $cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );
            $crop = $imageResized;
            $imageResized = imagecreatetruecolor($newWidth , $newHeight);
            imagecopyresampled($imageResized, $crop , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
        }

        // ��������� ���������� ��������
        $file = $file.'_resized';
        switch($this->image_size['type']) {
            case 1:
                $ext = 'gif';
                imagegif($imageResized, $file);
                break;
            case 2:
                $ext = 'jpg';
                imagejpeg($imageResized, $file, $this->quality);
                break;
            case 3:
                $ext = 'png';
                $scaleQuality = round(($this->quality/100) * 9);
                $invertScaleQuality = 9 - $scaleQuality;
                imagepng($imageResized, $file, $invertScaleQuality);
                break;
        }*/
        
        switch ($option) {
            case 'portrait':
                $optimalWidth = $this->_getImageSizeByFixedHeight($newHeight);
                $optimalHeight= $newHeight;
                break;
            case 'landscape':
                $optimalWidth = $newWidth;
                $optimalHeight= $this->_getImageSizeByFixedWidth($newWidth);
                break;
            case 'auto':
                $optionArray = $this->_getImageSizeByAuto($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
            case 'crop':
                $optionArray = $this->_getImageOptimalCrop($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
        }
        
        $imagick = new Imagick($file);
        
        if ($option == 'cropthumbnail'){
            
            $imagick->cropThumbnailImage($newWidth, $newHeight);
            
        } else if ($option == 'crop') {
            $cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
            $cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );
            $imagick->cropImage($newWidth, $newHeight, $cropStartX, $cropStartY);
        } else {
            if($this->image_size['type'] == 1) { // GIF �������� ��������� �������� ��� ���������� �����������
                $imagick = $imagick->coalesceImages();
                do {
                    $imagick->scaleImage($optimalWidth, $optimalHeight, true);
                } while ($imagick->nextImage());

                //$imagick->optimizeImageLayers();
            } else {
                $imagick->scaleImage($optimalWidth, $optimalHeight, true);
            }
        }
        //����������� ������ � ���������
        $imagick = $imagick->deconstructImages();
        $imagick->writeImages($file, true);
        
        $tFile = new CFile();
        if($table) $tFile->table = $table;
        else $tFile->table = $this->table;
        $tFile->tmp_name = $file;
        $tFile->original_name = $this->original_name;
        $tFile->size = filesize($file);
        $tFile->path = dirname($to) . '/';
        $tFile->name = basename($to);
        $tFile->_upload($file);
        return $tFile;
    }

    /**
     * ������ ����������� ������ ��� ������������� ������
     *
     * @param    integer    $newHeight    ������ � ��������
     * @return   integer                  ����������� ������
     */
    private function _getImageSizeByFixedHeight($newHeight) {
        $ratio = $this->image_size['width'] / $this->image_size['height'];
        $newWidth = $newHeight * $ratio;
        return $newWidth;
    }

    /**
     * ������ ����������� ������ ��� ������������� ������
     *
     * @param    integer    $newWidth     ������ � ��������
     * @return   integer                  ����������� ������
     */
    private function _getImageSizeByFixedWidth($newWidth) {
        $ratio = $this->image_size['height'] / $this->image_size['width'];
        $newHeight = $newWidth * $ratio;
        return $newHeight;
    }

    /**
     * ������ ����������� ������ � ������
     *
     * @param    integer    $newWidth     ������ � ��������
     * @param    integer    $newHeight    ������ � ��������
     * @return   array                    ����������� ������ � ������
     */
    public function _getImageSizeByAuto($newWidth, $newHeight) {
        if ($this->image_size['height'] < $this->image_size['width']) {
            $optimalWidth = $newWidth;
            $optimalHeight= $this->_getImageSizeByFixedWidth($newWidth);
        } elseif ($this->image_size['height'] > $this->image_size['width']) {
            $optimalWidth = $this->_getImageSizeByFixedHeight($newHeight);
            $optimalHeight= $newHeight;
        } else {
            if ($newHeight < $newWidth) {
                $optimalWidth = $newWidth;
                $optimalHeight= $this->_getImageSizeByFixedWidth($newWidth);
            } else if ($newHeight > $newWidth) {
                $optimalWidth = $this->_getImageSizeByFixedHeight($newHeight);
                $optimalHeight= $newHeight;
            } else {
                $optimalWidth = $newWidth;
                $optimalHeight= $newHeight;
            }
        }
        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }
    
    public function getImageSizeByAuto ($newWidth, $newHeight) {
        return $this->_getImageSizeByAuto($newWidth, $newHeight);
    }

    /**
     * ������ ����������� ������ � ������ ��� �����
     *
     * @param    integer    $newWidth     ������ � ��������
     * @param    integer    $newHeight    ������ � ��������
     * @return   array                    ����������� ������ � ������
     */
    private function _getImageOptimalCrop($newWidth, $newHeight) {
        $heightRatio = $this->image_size['height'] / $newHeight;
        $widthRatio  = $this->image_size['width'] /  $newWidth;
        if ($heightRatio < $widthRatio) {
            $optimalRatio = $heightRatio;
        } else {
            $optimalRatio = $widthRatio;
        }
        $optimalHeight = $this->image_size['height'] / $optimalRatio;
        $optimalWidth  = $this->image_size['width']  / $optimalRatio;
        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }
    
    /**
     * ������� ����� ������������ gif ����� �������� 1 ����
     * 
     * @param string $file  ���� �� �����
     * @return boolean 
     */
    public function getDisabledAnimateGIF($file = false) {
        if(!$file && $this->tmp_name) {
            $file = $this->tmp_name;
        } else {
            return false;
        }
        $orig_content = @file_get_contents($file);
        if(($unan_content = unanimate_gif($orig_content)) !== false) {
            file_put_contents($file, $unan_content);
        }
    }

    /**
     * ������� ������ �� ����
     *
     * @return boolean  treu - ������, false - �� ������
     */
    public function makeLink() {
        if($this->id) {
            $this->table = 'file_template';
            $this->count_links++;
            $this->updateFileParams(array( 'count_links'=>$this->count_links ));
            $GLOBALS['CFileCache']->del($this->id);
        } else {
            return false;
        }
    }
    
    /**
     * ����������� ��� ����� �� ��������� ���������� ��������, �������� ����������
     * @param string $name ��� ����� ������� ���� ���������
     * @param integer $maxLength ������������ ����� ����� ����� � ����������� � ������
     * 
     * @return ���������� ����� ����������� ��� �����
     */
    public function shortenName ($name, $maxLength) {
        $nameLength = strlen($name);
        if ($nameLength <= $maxLength) {
            return $name;
        }
        $ext = $this->getext($name);
        $maxLength_ = $maxLength - strlen($ext) - 1; // ������������ ����� ��� ���������� � �����
        $name_ = substr($name, 0, $maxLength_);
        $newName = $name_ . '.' . $ext;
        return $newName;
    }
 
    
    
    /**
     * @deprecated ���� �� ������������
     * @todo: �� ��� �������� - �� ��������� ������������ ���� �������
     * 
     * �������� ���� �� WebDav 
     * � ��������� �������� �������
     * 
     * @param type $localpath
     */
    /*
    public function copyToLocalPath($localpath) 
    {
        return $this->_wdp->get_file("/{$this->path}{$this->name}", $localpath);
    }
    */

    /**
     * @deprecated ���� �� ������������
     * @todo: �� ��� �������� - �� ��������� ������������ ���� �������
     * 
     * �������� ������ ������ �� WebDav � ��������� �������� �������
     * ������ ������� ������ array("remotepath" => "localpath")
     * 
     */
    /*
    public function copyFilesToLocalPath($filelist) 
    {
        return $this->_wdp->get_files($filelist);
    }
    */

    
    public function copyToLocalPathFromDav($remotepath, $localpath) 
    {
        $data = file_get_contents(WDCPREFIX_LOCAL . $remotepath);
        
        if ($data) {
            return (bool)file_put_contents($localpath, $data);
        }
        
        return false;
    }   

    


    /**
     * ���� ������� �������� � ������
     * ���� ������������ ������ ��� �����
     * 
     * @global type $DB
     * @param type $event
     * @param type $params
     * @return boolean
     */
    public function fireEvent($event, $params = null)
    {
        global $DB, $BACKUP_SERVICE;

        //���� ����� ��������
        if(!isset($BACKUP_SERVICE['active']) || 
           $BACKUP_SERVICE['active'] === false) {
            return false;
        }
        
        switch ($event) {
            
            case 'delete':   
                $filepath = $params;
                
            case 'create': 
                if(!isset($filepath)){
                    $filepath = $this->path . $this->name;
                }
                
                $DB->query("SELECT pgq.insert_event('backup', '{$event}', 'file={$filepath}');");
                
                break;
                
            case 'copy':
                $from_filepath = $this->path . $this->name;
                $to_filepath = $params;
                
                $DB->query("SELECT pgq.insert_event('backup', '{$event}', 'file={$from_filepath}&to={$to_filepath}');");
                
                break;
                
        }
        
        return true;
    }
    
    
    
    
    public function getTableName($id = null)
    {
        global $DB;
        
        if (!$id)  {
            $id = $this->id;
        }
        
        return $DB->val("
            SELECT p.relname
            FROM file_template AS f, pg_class AS p
            WHERE f.id = ?i AND f.tableoid = p.oid            
        ", $id);
    }
    
    
    
    /**
     * ������ �� ����
     * 
     * @return type
     */
    public function getUrl()
    {
        return WDCPREFIX . $this->path . $this->name;
    }
    
    /**
     * ���� ��������/����������� �����
     * 
     * @param type $format
     * @return type
     */
    public function getModified($format = null)
    {
        return ($format)?date($format, strtotime($this->modified)):$this->modified;
    }
    
    /**
     * �������� ����� ��� UI
     * 
     * @return type
     */
    public function getOriginalName()
    {
        return $this->original_name;
    }
    
}
