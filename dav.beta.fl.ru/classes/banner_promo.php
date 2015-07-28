<?
/**
 * ���������� ���� � ��������� ���������
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff2.php");

/**
 * ����� ������ � PROMO ���������
 * 
 * � ���� �� ������� ������ (ID|NAME)
 * 
 * 1 - EF
 * 2 - QIWI
 * 3 - Smiar
 * 4 - WebEffector
 */
class banner_promo
{
    /**
     * �������� ������� ���������� �������
     *
     * @var string
     */
    public $tbl_name= "ban_promo"; 
    
    /**
     * ��� ������� (ef, smiar, qiwi, etc...)
     *
     * @var string
     */
    public $type_banner; 
    
    /**
     * ����� ������ ����� ����
     *
     * @var string
     */
    public $dateError;
        
    /**
     *  ���� ������ ������
     *
     * @var string
     */
    public $from;
    /**
     *  ���� ��������� ������
     *
     * @var string
     */
    public $to;
    /**
     * ����� ������ ��� ���� ������� � ������
     *
     * @var string
     */
    public $files;
    /**
     * ������
     *
     * @var string
     */
    public $link;
    
    public static $type_ban = array(
        'image' => '�����������',
        'code' => '���'
    );
    
    public static $target_page = array(
        '0|0'  => '��� ��������',
        '0|1' => '������ �������',
        '0|103' => '������ ������ ��������',
        '0|21' => '������ �������� �������'
    );
    
    function __construct($type = false, $tbl_name = false) {
        $this->is_pro   =  is_pro() ? '10' : '01';
        $this->is_role  = get_uid(false) ? ( is_emp() ? '01': '10' ) : '00';
        if($tbl_name) {
            $this->tbl_name = $tbl_name;
        }
        if($type) {
            $this->setType($type, 1);
        }
    }
    /**
     * ������� ������ �� ������� ban_promo �� �������������� ��� �� ��������� ������� ���� � �������� ������
     * @param int $type    - ������������� ������� �� ������� ban_promo
     * @param int $nocache - ������������ �� ����������� ��� ������� �� ��
     * @return ������������� ������
     **/
    function setType($type = NULL, $nocache = false) {
        global $DB;        
        if(!$type) {
            $sql = "
              SELECT * FROM ban_promo_types
              WHERE now()::date BETWEEN from_date AND to_date AND deleted = 'f'
              AND (is_pro & B'{$this->is_pro}' = '{$this->is_pro}') AND (is_role & B'{$this->is_role}' = '{$this->is_role}')
              ORDER BY  is_activity DESC, advertising DESC, from_date ASC 
              LIMIT 1
            ";
        } else {
            $sql = "SELECT * FROM {$this->tbl_name}_types WHERE id = ?i";
        }
        if($nocache) { 
            $this->info = $DB->row($sql, $type);  
        } else {
            $this->info = $DB->cache(300)->row($sql, $type); 
        }
        if (strpos($this->info["name_img"], "/users") === 0) {
            $this->info["name_img"] = WDCPREFIX.$this->info["name_img"];
        } else {
            $this->info["name_img"] = $this->info["name_img"];
        }
        $this->type_banner = $this->info['id'];               
        return $this->type_banner;
    }
    
    /**
     * ������� ������ �� ������� ban_promo_types �� ���� ��������
     * setTypeByPage
     * @param type $target
     */
    public function setTypeByPage($target = '0|0')
    {
        global $DB;
        
        $sql = "SELECT * FROM ban_promo_types
            WHERE now()::date BETWEEN from_date AND to_date AND deleted = 'f'
            AND (is_pro & B'{$this->is_pro}' = '{$this->is_pro}') AND (is_role & B'{$this->is_role}' = '{$this->is_role}')
            ORDER BY (page_target = ?) DESC, is_activity DESC, advertising DESC, from_date ASC 
            LIMIT 1
        ";
        $query = $DB->parse($sql, $target);

        $memBuff = new memBuff();
        $result = $memBuff->getSql($error, $query, 600, true, 'banner_promo');
        
        if ($result) {
            $this->info = $result[0];
        
            if (strpos($this->info["name_img"], "/users") === 0) {
                $this->info["name_img"] = WDCPREFIX.$this->info["name_img"];
            } else {
                $this->info["name_img"] = $this->info["name_img"];
            }
            $this->type_banner = $this->info['id'];               
        }
        
        return $this->type_banner;
        
    }
    
    public function clearCache()
    {
        $memBuff = new memBuff();
        $memBuff->flushGroup('banner_promo');
    }
    
    /**
     * �������� � ���������� ����� �������
     *
     */
    function writeViewStat() {
        global $DB;
        $date = date("Y-m-d");
        $sql = "UPDATE {$this->tbl_name} SET views = views+1 WHERE c_date = DATE ? AND type_id = ?i"; // !!! ����� ���� ����
        $res = $DB->query($sql, $date, $this->type_banner);
        if($res && !pg_affected_rows($res)) {
            $sql = "INSERT INTO {$this->tbl_name}(views, clicks, c_date, type_id) VALUES(1, 0, ?, ?i)";
            $DB->query($sql, $date, $this->type_banner);
        }
    }

    /**
     * �������� � ���������� ���� �� �������
     *
     */
    function writeClickStat() {
        global $DB;
        $date = date("Y-m-d");
        $sql = "UPDATE {$this->tbl_name} SET clicks = clicks+1 WHERE c_date = DATE ? AND type_id = ?i";
        $DB->query($sql, $date, $this->type_banner);
    }

    /**
     * �������� ����� ���-�� ���������� � ������ �� ������
     *
     * @return  array   ���-�� ������ � ���������� �������
     */
    function getCountStat() {
        global $DB;
        $sql = "SELECT SUM(views) as views, SUM(clicks) as clicks FROM {$this->tbl_name} WHERE type_id = {$this->type_banner}";
        return $DB->row($sql);
    }

    /**
     * �������� ���������� ������� �� ����
     *
     * @return  array   ���-�� ������ � ���������� ������� �� ����
     */
    function getStat() {
        global $DB;
        $sql = "SELECT * FROM {$this->tbl_name} WHERE type_id = {$this->type_banner} ORDER BY c_date DESC";
        return $DB->rows($sql);
    }
    
    /**
     * ����� ���������� �� ���� �������� ������� ���� � �������
     *
     */
    function getInfoBanners() {
        global $DB;
        $sql = "SELECT * FROM {$this->tbl_name}_types WHERE deleted != 't' ORDER BY id DESC";
        return $DB->rows($sql);    
    }
    
    /**
     * ��������� ����������
     * 
     * @param inetger $id               �� �������
     * @param string  $name             �������� �������
     * @param string  $from_date        ���� ������ ����������
     * @param string  $to_date          ���� ����� ����������
     * @param string  $location         �����������������
     * @param integer $is_activity      ������� ��� ���
     * @param string  $name_img         �������� �������� �������
     * @param string  $img_style        ����� ��������
     * @param string  $img_title        ��������� ��������
     * @param string  $link_style       ����� ������
     * @param string  $advertising      ��������� �� ������
     * @param string  $text             ����� ������
     * @return boolean
     */
    function saveInfoBanner($id, $name, $from_date, $to_date, $location, $is_activity, $name_img, $img_style, $img_title, $banner_link, $link_style, $advertising, $text, $type_ban = 'image', $code_txt = '', $login_access = '', $is_pro = '11', $is_role = '11', $page_target = '0|0') {
        global $DB;
        $sql = "UPDATE {$this->tbl_name}_types 
                SET name=?, from_date = DATE ?, to_date = DATE ?, 
                    location = ?, is_activity = ?, name_img = ?,  
                    img_style = ?, img_title = ?, banner_link = ?, link_style = ?, advertising = ?, linktext = ?, 
                    type_ban = ?, code_text = ?, login_access = ?, is_pro = ?, is_role = ?, page_target = ?
                WHERE id = ?i";
        if($is_activity==1) {
            $DB->query("UPDATE {$this->tbl_name}_types SET is_activity = false WHERE is_activity = true");    
        }
        $this->info["linktext"]        = $text;
        $this->info["advertising"]     = ($advertising?'t':'');
        $this->info["is_activity"]     = ($is_activity?'t':'');   
        $this->info["is_pro"]          = $is_pro;
        $this->info["is_role"]         = $is_role;
        
        $ok = $DB->query($sql, $name, $from_date, $to_date, $location, $is_activity==1?'t':'f', $name_img, $img_style, $img_title, $banner_link, $link_style, $advertising==1?'t':'f', $text, $type_ban, $code_txt, $login_access, $is_pro, $is_role, $page_target, $id);
        
        if ($ok) {
            $this->clearCache();
        }
        
        return $ok;
    }
    
    /**
     * �������� �������
     *
     * @param integer $id  �� �������
     * @return boolean
     */
    function deleteBanner($id) {
        global $DB;
        $sql = "UPDATE {$this->tbl_name}_types SET deleted = 't' WHERE id = ?i";
        
        $ok = $DB->query($sql, $id);
        
        if ($ok) {
            $this->clearCache();
        }
        
        return $ok;
    }
        
    /**
     * �������� ����� �������
     *
     * @param string  $name             �������� �������
     * @param string  $from_date        ���� ������ ����������
     * @param string  $to_date          ���� ����� ����������
     * @param string  $location         �����������������
     * @param integer $is_activity      ������� ��� ���
     * @param string  $name_img         �������� ��� �����
     * @param string  $img_style        ����� ��������
     * @param string  $img_title        ��������� ��������
     * @param string  $banner_link      ������
     * @param string  $link_style       ����� ������
     * @param string  $advertising      ��������� �� ������
     * @param string  $text             ����� ������
     * @return boolean
     */
    function createBanner($name, $from_date, $to_date, $location, $is_activity, $name_img, $img_style, $img_title, $banner_link, $link_style, $advertising, $text, $type_ban = 'image', $code_txt = '', $login_access = '', $is_pro = '11', $is_role = '11', $page_target='0|0') {
        global $DB;
        
        $sql = "INSERT INTO {$this->tbl_name}_types (name, from_date, to_date, location, is_activity, name_img, img_style, img_title, banner_link, link_style, advertising, linktext, type_ban, code_text, login_access, is_pro, is_role, page_target) 
                VALUES(?, DATE ?, DATE ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if($is_activity==1) {
            $DB->query("UPDATE {$this->tbl_name}_types SET is_activity = false WHERE is_activity = true");    
        }
        $ok = $DB->query($sql, $name, $from_date, $to_date, $location, $is_activity==1?'t':'f', $name_img, $img_style, $img_title, $banner_link, $link_style, $advertising==1?'t':'f', $text, $type_ban, $code_txt,
                $login_access, $is_pro, $is_role, $page_target);
        
        if ($ok) {
            $this->clearCache();
        }
        
        return $ok;
    }
    /**
     * ����� �� ��������� �������
     *
     * @return integer
     */
    function getActiveBanner() {
        global $DB;
        $sql = "SELECT id FROM {$this->tbl_name}_types WHERE is_activity = true AND (is_pro & B'{$this->is_pro}' = '{$this->is_pro}') AND (is_role & B'{$this->is_role}' = '{$this->is_role}')";
        return $DB->val($sql);
    }    
    /**
     * ���� $_FILES �� ����, ��������� ����������� �� DAV ������� � ���������� ���� � �����.
     * ����� ���������, ���������� �� name_img � ����� images ���� ��, �� ���������� � ����� ������� ���� � ���
     * @param &$err    - ����� ������
     * @return stdObject->name (���� � �����  ��� ��������� �������) ��� false
     * */
    public function saveImg(&$err) {
        $obj = new StdClass();
        $obj->name = '';
        $cfile = new CFile(0, "file");
        $name = str_replace(WDCPREFIX, "", $_POST["name_img"]);
        $name = preg_replace("#^/#", '', $name);
        $cfile->GetInfo($name);        
        if (file_exists($_SERVER["DOCUMENT_ROOT"].$_POST["name_img"])||($cfile->id > 0)) {
            $this->info["name_img"] = $_POST["name_img"];
            $obj->name = str_replace(WDCPREFIX, "", $_POST["name_img"]);        
        }else {
            $err = "����������� �� �������";
        }
        if ($_FILES["file_main"]["tmp_name"] !== '') {        
            $name   = $this->_moveUploadedFile("file_main", $err);
            if ($name) {
                $_POST["name_img"] = $this->info["name_img"] = WDCPREFIX.$name;
                $obj->name = $name;
            }
        }	    
        return $obj;
    }
    /**
     * ���������� ����������� ���� ����������� �� dav ������ ���� ������ ����� ����� 30 px
     * @param $id           - ���� ������� ���������� � ����� � $_FILES
     * @param &$err         - ��������� ��������� �� ������    
     * @return string       - ���� � ����� �� ����� dav ������� ��� WDCSERVER ��� ������ ������
     * */
    private function  _moveUploadedFile($id, &$err) {
        if (count($_FILES[$id])) {
            $data = $_FILES[$id];
            $name = strtolower($data["name"]);
            $mime = $data["type"];
            if (strpos($mime, "png")||strpos($mime, "jpg")||strpos($mime, "gif")||strpos($mime, "jpeg")) {
                $sz = getimagesize($data["tmp_name"]);
                if ($sz[1] > 30) {
                    $err = "������ ����� ��������� 30 ��������";
                    return '';
                }   	    	    
                $cfile = new CFile($data);
                $name = $cfile->MoveUploadedFile("images");
                return ("/".$cfile->path.$name); 
            }else {
                $err = "������������ ��� �����";
                return '';
            }
        }
    }
    
    public function isAccess($type_id) {
        global $DB;
        $sql = "SELECT login_access FROM {$this->tbl_name}_types WHERE id = ?i";
        $row = $DB->val($sql, $type_id);
        
        $access = explode(",", $row);
        $access = array_map("trim", $access);
        $access = array_map("mb_strtolower", $access);
        
        $login  = mb_strtolower($_SESSION['login']);
        
        return in_array($login, $access);
    }
}
?>
