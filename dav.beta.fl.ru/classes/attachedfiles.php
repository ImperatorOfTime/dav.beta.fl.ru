<?php
/**
 * ����� ��� ������ � ������� � ������ ��������, �����, ������, ���������.
 *
 */
class attachedfiles {

    /**
     * ������� ��� ����� �������� ���������� ��� ������ ������
     */
    const SESSION_PARAMS_KEY = 'attachedfiles_params_key';
    
    /**
     * ID ������
     *
     * @var string
     */
    public $session = array();

    /**
     * ����������� ������
     *
     * @param    string    $session    ID ������
     * @return   object
     */
    function __construct($session='', $clear = false) {
        if(!$clear) {
            if(is_array($session)) {
                foreach($session as $sess) {
                    $this->addNewSession($sess);
                }
            } else {
                $this->addNewSession($session);
            }
        }
    }
    
    /**
     * ��������� ������
     * 
     * @param  $session 
     */
    public function addNewSession($session = false) {
        if(!$session) {
            $session = $this->createSessionID();
        }
        array_push($this->session, __paramValue('string', $session)); 
    }
    
    /**
     * ������� ������ 
     */
    public function clearSession() {
        $this->session = array();
    }
    
    public function getSession($key = 0) {
        return $this->session[$key];
    }
    
    /**
     * �������� ����������� �� ��� ������
     * 
     * @return string
     */
    public function createSessionID() {
        return  md5(time().'-'.get_uid(false).'-'.rand(1,9999999));
    }
    
    /**
     * �������� ����� �� ������
     *
     * @param    string    $file_id    ID ���������� �����
     */
    public function delete($file_id) {
        global $DB;
        $sql = "SELECT * FROM attachedfiles WHERE session IN (?l) AND MD5(file_id::text)=?u";
        $file = $DB->row($sql, $this->session, $file_id);
        switch($file['status']) {
            case 1:
                $status = 2;
                break;
            case 3:
                $status = 4;
                break;
            default:
                $status = 0;
                break;
        }
        if($status) {
            $sql = "UPDATE attachedfiles SET status=?i WHERE id=?i";
            $DB->query($sql, $status, $file['id']);
        }
    }

    /**
     * ���������� �����
     *
     * @param   object    $file    ����������� ����
     * @return  array              ���������� � ����������� �����
     */
    public function add($cFile, $skey = 0) {
        global $DB;
        $sql = "INSERT INTO attachedfiles(file_id, status, session, size) VALUES(?i, 1, ?u, ?i)";
        $DB->query($sql, $cFile->id, $this->session[$skey], $cFile->size);
        $file = array();
        $file['id'] = $cFile->id;
        $file['name'] = $cFile->name;
        $file['path'] = $cFile->path;
        $file['size'] = $cFile->size;
        $file['orig_name'] = $cFile->original_name;
        $file['type'] = $cFile->getext();
        return $file;
    }

    /**
     * ������� ������ ������
     *
     */
    public function clear($skey = false) {
        global $DB;
        $sql = "SELECT file_id FROM attachedfiles WHERE session " . ( $skey ? " = ?u" : "IN (?l)" ) . "  AND status IN (1,2)";
        $files = $DB->rows($sql, ( $skey ? $this->session[$skey] : $this->session ));
        if($files) {
            foreach($files as $file) {
                $cFile = new CFile($file['file_id']);
                if($cFile->id) { $cFile->delete($file['file_id']); }
            }
        }
        $sql = "DELETE FROM attachedfiles WHERE session " . ( $skey ? " = ?u" : "IN (?l)" ) . "";
        $DB->query($sql, ( $skey ? $this->session[$skey] : $this->session ));
    }
    
    /**
     * ������ ������ ������ � 1 �� 3 � ������� ������
     * �� ���� ������ ������ � ������ �� ������
     * @param int $fileID �������������� ��������, ���� ����� �� ��������� ������ ������ ������ �����, � �������� ID
     */
    public function setStatusTo3 ($fileID = null) {
        global $DB;
        $whereFileID = $fileID ? $DB->parse('AND file_id = ?i', $fileID) : '';
        $sql = "
            UPDATE attachedfiles
            SET status = 3
            WHERE session IN (?l)
            $whereFileID
            AND status = 1";
        $DB->query($sql, $this->session);
    }
    

    /**
     * ������� ���-�� � ������ ������ � ������ ������
     *
     * @return    array    ���-�� � ������ ������
     */
    public function calcFiles($skey = 0) {
        global $DB;
        $sql = "SELECT count(id) as count, sum(size) as size FROM attachedfiles WHERE status IN (1,3) AND session=?u";
        $info = $DB->row($sql, $this->session[$skey]);
        return array('count'=>$info['count'], 'size'=>$info['size']);
    }

    /**
     * ��������� ����� � ������
     *
     * @param    array    $files    ������ ID ������
     * @param    integer  $status   ������ �����
     * @param    integer  $skey     ���� ������ � ����� ��������� ����, �� ��������� � �������
     */
    public function setFiles($files, $status=3, $skey = 0) {
        global $DB;
        
        if($files) {
            foreach($files as $file) {
                $cFile = new CFile($file);
                if($status==1) {
                    $cFile->table = 'file';
                    $cFile->makeLink();
                }
                $sql = "INSERT INTO attachedfiles(file_id, status, date, session, size) VALUES(?i, ?i, NOW(), ?u, ?i)";
                $DB->hold()->query($sql, $cFile->id, $status, $this->session[$skey], $cFile->size);
            }
            $DB->query();
        }
    }

    

    /**
     * �������� ������� ������ ������
     *
     * @param    array    $status      ������ ������ ������� ���� ��������
     * @return   array                 ������� ������ ������
     */
    public function getFiles($status=array(1,3), $md5key = false, $md5id = false, $skey=false) {
        global $DB;
        $files = array();
        $sql = "SELECT * FROM attachedfiles WHERE session " . ( $skey ? " = ?u" : "IN (?l)" ) . " AND status IN (?l) ORDER BY DATE, file_id;";
        $qFiles = $DB->rows($sql, ( $skey ? $this->session[$skey] : $this->session ), $status);
        if($qFiles) {
            foreach($qFiles as $file) {
                $cFile = new CFile($file['file_id']);
                if($cFile->id) {
                    $arr = array(
                        'id'     => ( $md5id ? md5($cFile->id) : $cFile->id ), 
                        'name'   => $cFile->name, 
                        'path'   => $cFile->path, 
                        'size'   => $cFile->size, 
                        'tsize'  => ConvertBtoMB($cFile->size),
                        'orig_name' => $cFile->original_name,
                        'type'   => $cFile->getext(), 
                        'status' => $file['status']
                    );
                    
                    if (!$md5key) {
                        array_push($files, $arr);
                    } else {
                        $files[md5($cFile->id)] = $arr;
                    }
                }
            }
        }
        return $files;
    }
    
    /**
     * �������� ������� ������ ������ ��� ������� � ������� �����������
     *
     * @param    integer    $src_id    id �������
     * @return   array                 ������� ������ ������ ��� false
     */
    public function getFilesForWizard($src_id) {
        global $DB;
        $files = array();
        $sql = "SELECT wf.id as id
                    FROM file_wizard wf 
                    WHERE src_id = ?i 
                    ORDER BY id;";
        $files = $DB->rows($sql, $src_id);
        if ($DB->error) {
            return false;
        }
        
        $Files = array();
        $setFiles = array();
        
        // ���������� ����� � ������
        foreach($files as $file) {
            array_push($setFiles, $file['id']);
        }        
        $this->setFiles($setFiles);
        
        $Files = $this->getFiles();
        
        return $Files;
    }
    
    
    
    /**
     * �������� ���� ������� � ����� ������� ������
     * ������ ����������� ����� �������� �������� ������
     * 
     * @global type $DB
     * @return boolean
     */
    function clearBySession() 
    {
        global $DB;
        
        if(empty($this->session)) {
            return false;
        }
        
        $sql = "DELETE FROM attachedfiles WHERE session IN (?l)";
        if ($DB->query($sql, $this->session)) {
            $this->clearSession();
            return true;
        }

        return false;
    }


    /**
     * ������� ������ ������ � �������� �����������, �� �� ������������� ������
     *
     */
    function clearOldSessions() {
        global $DB;

        $file_ids = array();
        $date = date("Y-m-d H:i:s", time()-5*60*60);
        
        $cfile = new CFile();
        $cFile->table = 'file';

        $sql = "SELECT * FROM attachedfiles WHERE date < ?";
        $res = $DB->query($sql, $date);
        while($session = pg_fetch_assoc($res)) {
            if($session['status']==1 || $session['status']==2) {
                $cfile->Delete($session['file_id']);
            }
        }

        $sql = "DELETE FROM attachedfiles WHERE date < ?";
        $DB->query($sql, $date);
    }
    
    public static function getFormTemplate($cssName, $attached_type, $attached_params = array()) {
        //$attachedfiles_session = $attached_params['session'] ? $attached_params['session'] : $this->session;
        //$attachedfiles_files = $this->getFiles();
        $params = $attached_params;
        ob_start();
        include $_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles/tpl.form.php";
        return ob_get_clean();
    }
    
    /**
     * �������������� ������ ��� JSON (�� ������������ ������� ����� ������ � UTF-8)
     * 
     * @param array $files   ������ ������
     * @return type 
     */
    public static function getInitJSONContentSBRFiles($files) {
        foreach($files as $st => $sfile) {
            foreach($sfile as $nm=>$val) {
                $files[$st][$nm]['tsize'] = iconv('WINDOWS-1251', 'UTF-8', $val['tsize']);
                $files[$st][$nm]['orig_name'] = iconv('WINDOWS-1251', 'UTF-8', $val['orig_name']);
            }
        } 
        
        return $files;
    }
    
    public static function getInitJSON($files) {
         foreach($files as $nm=>$val) {
            $files[$nm]['tsize'] = iconv('WINDOWS-1251', 'UTF-8', $val['tsize']);
            $files[$nm]['orig_name'] = iconv('WINDOWS-1251', 'UTF-8', $val['orig_name']);
        }
        
        return $files;
    }
    
}