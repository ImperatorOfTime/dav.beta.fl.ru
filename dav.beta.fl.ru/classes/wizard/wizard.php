<?php 

/**
 * ����� ��� ������ � ���������, ������
 * 
 * �������� ����� ��������� ������� 
 * 
 * wizard           - ������� ��������, ��� �� ������� = self::$_id
 * wizard_step      - ������� ����� (�� �������� �� �������, ������� ������� ����� ��������� ���������� ����, � ������ ����������)
 * wizard_to_step   - ������� ���������� ������� � �����, � ������������ ������������ �������� �� �� ����������� � ���������� �������
 * wizard_action    - ������� �������� ������������ � �������� � ������. ���������� ������� - ������� ������������ ���.
 *  
 * ������ �� ������������ �������� ����� ���� (������� ���, ���������� �� ������������ ������������ �������) - 
 * ��� ������� ������� ����� ������������� ���� ���� � ������� ����� "{name_cookie}{self::$_id}"
 * @todo ���������� ������������� �������� ����� ���� ���������, ����� ����� ����� ��������� ������������� ������ � ������
 * 
 * ����������� ������� �������� ������������ ����� ��� ��������� �����, �� ��������� ��� ����� step_wizard, �� ����� �������������� ���� �����
 * ������� ������ ����������� ������� � ������ ������ step_wizard
 * 
 * @example
 * 
 * $wizard = new wizard(1, new MY_step_wizard()); -- ����� ����� � ������������� 
 * 
 */
class wizard 
{
    /**
     * ������������ ������ �������� �����
     *
     */
	const MAX_FILE_SIZE     = 5242880;
    
    /**
     * ������������ ���������� ������
     *
     */
    const MAX_FILE_COUNT    = 10;
    
    /**
     * ����� ��� ������ 
     */
    const FILE_DIR = "wizard/";
    
    /**
     * ������������ ����� ����� ������ �� ������� �������������
     */
    const LIFE_TIME_ACTION = '1 month';
    
    /**
     * ���������� �� �������
     * 
     * @var integer 
     */
    protected $_id = 0;
    
    /**
     * ����������� � ��
     * 
     * @var object
     */
    public $_db;
    
    /**
     * �������� ��� ������������ ��������
     * 
     * @var array
     */
    protected $_cookie_names = array(
        "uid"           => "W_UID",
        "step"          => "W_STEP",
        "role"          => "W_ROLE",
        "categories"    => "your_categories",
        "subcategories" => "your_subcategories",
        "visit"         => "visited_wizard"
    );
    
    /**
     * �� ������������ �������
     * 
     * @var string 
     */
    protected $_uid = "";
    
    /**
     * ������������� �������� ����
     * 
     * @var integer 
     */
    protected $_step = 0;
    
    /**
     * �������� ��������� �� ������� ����������� � ����
     * 
     * @var integer 
     */
    public $_action = 0;
    
    /**
     * ��������� ��� � ������� � ������� ��� ���
     * 
     * @var boolean 
     */
    protected $_complete_step = false;
    
    /**
     * ���������� �� ����� �������
     * 
     * @var array 
     */
    public $steps = array();
    
    /**
     * ����������� ������
     * 
     * @global object $DB
     * @param integer $id       �� �������
     * @param object $obj_step  ����� ��������� ����� ��� ��������� @see step_wizard;    
     */
    public function __construct($id = false, $obj_step = false) {
        global $DB;
        $this->_db = $DB;
        
        $this->init($id, $obj_step);
    }
    
    /**
     * ������������� ������ ��� ������ � ��������
     * 
     * @param type $id                  �� �������
     * @param step_wizard $obj_step     ����� ��������� ����� ��� ��������� @see step_wizard;    
     * @return boolean false ���� ID �� �����
     */
    public function init($id = false, $obj_step = false) {
        if(!$id) return false;
        
        if($id) {
            $this->_id = $id;
            $this->setInitWizard();
        }
        
        if($obj_step instanceof step_wizard) {
            $this->obj_step = $obj_step;
        } else {
            $this->obj_step = new step_wizard();
        }
        
        $this->_cookie_names['uid']  = "W_UID{$this->_id}";
        $this->_cookie_names['step'] = "W_STEP{$this->_id}";
        
        $this->setInitUser();
        $this->setInitSteps();
    }
    
    /**
     * ���������� ��� ���� �� ��� ����� @see self::_cookie_names
     * 
     * @param string $key ���� ����
     * @return boolean  ���� ����� ���� �� ���������� ���������� false
     */
    public function getCookieName($key) {
        if(isset($this->_cookie_names[$key])) {
            return $this->_cookie_names[$key];
        }
        return false;
    }
    
    public function getWizardUID() {
        return $this->_uid;
    }
    
    /**
     * �������� ������� � �������
     * 
     * @return boolean 
     */
    public function isAccess() {
        if(empty($this->data)) {
            $this->setInitWizard();
        }
        
        switch($this->access_type) {
            // ���� �������������
            case 0:
                return true;
                break;
            // ������ �� ������������������ ������������� + ������������� ������� ������������������ ����� ������
            case 1:
                return (!get_uid(false) || $this->checkUserIDReg());
                break;
            // ������ ������������������ �������������
            case 2:
                $reg = $this->checkUserIDReg();
                return $reg;
                break;
            default:
                return false;
                break;
        }
    }
    
    /**
     * ������������� ������ ������� 
     */
    public function setInitWizard() {
        $sql = "SELECT * FROM wizard WHERE id = ?i";
        $this->data = $this->_db->row($sql, $this->_id);
    }
    
    /**
     * ������������� ������ ������������ ������������ ������� 
     * 
     * @todo ������������� ������������� ������� ��� ������������������ �������������
     */
    public function setInitUser() {
        if (!isset($_COOKIE[$this->_cookie_names['uid']])) {
            $this->_uid = $this->_generateWizardUserID();
            setcookie($this->_cookie_names['uid'], $this->_uid, $this->_lifeTimeCookie(), '/', $GLOBALS['domain4cookie']);
            setcookie($this->_cookie_names['visit'], 1, $this->_lifeTimeCookie(), '/', $GLOBALS['domain4cookie']);
            $_COOKIE[$this->_cookie_names['uid']] = $this->_uid;
        } else {
            $this->_uid = __paramValue('string', $_COOKIE[$this->_cookie_names['uid']]);
        }
        $_SESSION['WUID'] = $this->_uid;
    }
    
    /**
     * ��������� ����������� �� ������������ ������������ �������
     * @return type 
     */
    protected function _generateWizardUserID() {
        return substr(md5(microtime() + $_SERVER['HTTP_USER_AGENT'] + getRemoteIP()), 0, 10);
    }
    /**
     * ����� ����� �����
     * 
     * @return timestamp
     */
    protected function _lifeTimeCookie() {
        return time() + 3600 * 24 * 180;
    }
    
    /**
     * ��������� ������ ������������������� ������������ � ��������
     * 
     * @return type 
     */
    public function checkUserIDReg() {
        $sql = "SELECT 1 FROM wizard_action WHERE reg_uid = ? AND wiz_uid = ?";
        return $this->_db->val($sql, $this->getUserIDReg(), step_wizard::getWizardUserID());
    }
    
    /**
     * ���������� �� ������������������� ������������
     * @return type 
     */
    public function getUserIDReg() {
        return $_SESSION['uid'] ? $_SESSION['uid'] : $_SESSION['RUID'];
    }
    
    /**
     * ������������� ����� ������� 
     */
    public function setInitSteps() {
        $sql = "SELECT ws.*, wts.id as id_wiz_to_spec, wts.wizard_id, wts.step_id, wts.pos, wts.type_step, wa.status, wts.depend_pos, wa.reg_uid, wa.id as action_id 
                FROM wizard_to_step wts 
                INNER JOIN wizard_step ws ON ws.id = wts.step_id 
                LEFT JOIN wizard_action wa ON wa.id_wizard_to_step = wts.id AND wiz_uid = ?
                WHERE wts.wizard_id = ?i
                ORDER BY pos ASC"; 
        
        $steps = $this->_db->rows($sql, $this->_uid, $this->_id);
        if($steps) {
            foreach($steps  as $k=>$step) {
                if($step['reg_uid']) {
                    $this->reg_uid = $step['reg_uid'];
                    $_SESSION['RUID'] = $step['reg_uid'];
                }
                $wstep = $this->obj_step->initInstance($step['id_wiz_to_spec']);//new step_wizard($step['id_wiz_to_spec']);
                $wstep->setContent($step);
                $wstep->parent = $this;
                $this->steps[$step['pos']] = $wstep;
            }
        }
        $this->setLastStep();
    }
    
    /**
     * ����� �� �������� ������������ �� ��������� ���� � �������
     * 
     * @param integer $id �� ����
     * @return integer 
     */
    public function getAction($id = false) {
        if(!$id) {
            $id = $this->steps[$this->_step]->id_wiz_to_spec;
        }
        $sql = "SELECT id FROM wizard_action WHERE id_wizard_to_step = ?i AND wiz_uid = ?";
        $res =  $this->_db->val($sql, $id, $this->_uid);
        $this->_action = $res;
        return $res;
    }
    
    /**
     * �������� ���������� ��������
     * 
     * @param int $pos ������� ������� �� ��������� � ����� 
     * @return boolean 
     */
    public function checkAction($pos) {
        if(!$this->_action) {
            $res = $this->getAction();
        } else {
            $res = $this->_action;
        }
        return (!$res  && $pos != $this->_step && $this->isCompliteStep());
    }
    
    /**
     * ���������� �������� ������������ �� ���� (�������� �������� ���������� ���)
     * 
     * @param object  $step      ��� ������������ @see step_wizard();
     * @param integer $status    ������
     * @return integer �� ���������� ��������
     */
    public function saveActionWizard($step, $status = 1) {
        $data = array(
            "id_wizard_to_step" => $step->id_wiz_to_spec,
            "wiz_uid"           => $this->_uid,
            "reg_uid"           => $this->getUserIDReg(),
            "status"            => $status
        );
        
        return $this->_db->insert("wizard_action", $data, "id");
    }
    
    /**
     * ������� �� ��������� ��� �������
     * 
     * @param integer $pos     ������� ���� �������
     */
    public function setNextStep($pos) {
        if($this->isStep($pos)) {
            // ���� ��� �������� �� ��������� ��� �������� ������ ���������� �������� ����, ����� ���������� � ��
            $this->saveCheckStep($pos);
            setcookie($this->_cookie_names['step'], $pos, $this->_lifeTimeCookie(), '/', $GLOBALS['domain4cookie']);
            $_COOKIE[$this->_cookie_names['step']] = $pos;
            $this->steps[$this->_step]->clearSessionStep();
            $this->setLastStep();
        }
    }
    
    /**
     * �������� ������������ ����� � ������ ���������� ��� �����������
     * 
     * @param integer $pos ��� �������
     */
    public function saveCheckStep($pos) {
        if($this->checkAction($pos)) {
            $this->_action = $this->saveActionWizard($this->steps[$this->_step]);
            $this->steps[$this->_step]->setContent();
        }
    }
    
    /**
     * �������� ���� �� ����������� ��� �������� �� ����
     * 
     * @param integer $pos     ������� ���� �������
     * @return boolean 
     */
    public function isStep($pos) {
        return ((isset($this->steps[$pos]) && !$this->steps[$pos]->isDisable()) || $this->isCompliteStep()) ;
    }
    
    /**
     *  ������ ������� �������� ��� 
     */
    public function setLastStep() {
        if (!isset($_COOKIE[$this->_cookie_names['step']])) {
            $this->_step = current(array_keys($this->steps));
            setcookie($this->_cookie_names['step'], $this->_step, $this->_lifeTimeCookie(), '/', $GLOBALS['domain4cookie']);
        } else {
            $this->_step = __paramValue('int', $_COOKIE[$this->_cookie_names['step']]);
        }
    }
    
    /**
     * ���������� ������� �������� ���
     * 
     * @return object @see new step_wizard(); 
     */
    public function getLastStep() {
        return $this->steps[$this->_step];
    }
    
    /**
     * ���������� ������� ������� ���� �������
     * 
     * @return integer
     */
    public function getPosition() {
        return $this->_step;
    }
    
    /**
     * ������ ��������� ���� ����������|������ �� ������ �������, ��� ��������
     * 
     * @param boolean $action 
     */
    public function setCompliteStep($action = false) {
        $this->_complete_step = $action;
    }
    
    /**
     * �������� �� ����������|������ �� ������ ������� ����
     * 
     * @return boolean
     */
    public function isCompliteStep() {
        return $this->_complete_step;
    }
    
    /**
     * ����� ������� � ���������� ������� 
     * 
     * @param string $name    ��� ����������
     * @return mixed ������ ���������� 
     */
    public function __get($name) {
        if(!is_array($this->data)) return null;
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            return null;
            //trigger_error("Variable '{$name}',  not found", E_USER_NOTICE);  
        }
    }
    
    /**
     * ������� ���������� ������ , ����������� ����� ������� ������ ������� ����� ��� �����
     */
    public function cleaningOldData() {
        $sql = "SELECT DISTINCT wiz_uid FROM wizard_action WHERE date_complite <= NOW() - '".self::LIFE_TIME_ACTION."'";
        $uid = $this->_db->col($sql);
        $sql = "DELETE FROM wizard_action WHERE wiz_uid IN (?l);";
        return $this->_db->query($sql, $uid);
    }
    
    /**
     * ������ ��� ���� �� ������� 
     */
    public function clearCookies() {
        foreach ($this->_cookie_names as $cookie) {
            unset($_COOKIE[$cookie]);
            setcookie($cookie, null, time(), '/', $GLOBALS['domain4cookie']);
        }
    }
    
    /**
     * ������ ��� ���� �� ��������
     */
    public function clearCookiesById($id) {
        foreach ($this->_cookie_names as $cookie) {
            unset($_COOKIE[$cookie . $id]);
            setcookie($cookie . $id, null, time(), '/', $GLOBALS['domain4cookie']);
        }
    }
    
    /**
     * ��� ������ ������ ����
     * @return type 
     */
    public function clearActions($uid = false) {
        if(!$uid) $uid = $this->_uid;
        $sql = "DELETE FROM wizard_action WHERE wiz_uid = ?";
        return $this->_db->query($sql, $uid);
    }
    
    /**
     * ��� ������ ������ ������ 
     */
    public function clearSession() {
        unset($_SESSION['WUID'], $_SESSION['RUID'], $_SESSION['view_wizard_project']);
    }
    
    /**
     * ����� �� ������� 
     * 
     * @param boolean redirect �������� ��������������� �� ������� ��� ���
     */
    public function exitWizard($redirect = true) {
        $this->clearCookies();
        $this->clearActions();
        $this->clearSession();
        if($redirect) header("Location: /"); // ������� �� �������
    }
    
    /**
     * ������ ����������� ������ � �� 
     * 
     * @global object $DB     ����������� � ��
     * @param integer $attach_id   �� �����
     * @param integer $id          �� ��������
     */
    public function insertAttachedFile($attach_id, $id, $type = 1) {
        $update = array('src_id' => (int) $id, 'type' => (int) $type);
        $this->_db->update("file_wizard", $update, "fname = ?", $attach_id); 
    }
    
    /**
     * ����������/�������� ������ 
     * 
     * @param array   $files   ������ ������
     * @param integer $id      �� ��������
     */
    public function addAttachedFiles($files, $id, $type = 1) {
        if($files) {
            foreach($files as $file) {
                switch($file['status']) {
                    case 4:
                        // ������� ����
                        $this->delAttach($file['id']);   
                        break;
                    case 1:
                        // ��������� ����
                        $cFile = new CFile($file['id']);
                        $cFile->table = 'file_wizard';
                        $ext = $cFile->getext();
                        $tmp_name = $cFile->secure_tmpname(self::FILE_DIR, '.'.$ext);
                        $tmp_name = substr_replace($tmp_name,"",0,strlen(self::FILE_DIR));
                        $cFile->_remoteCopy(self::FILE_DIR.$tmp_name, true);
                        $this->insertAttachedFile($cFile->name, $id, $type);
                        break;
                }
            }
        }
    }
    
    /**
     * ������� ���� �� ID
     */
    public function delAttach ($file_id) {
        $cFile = new CFile($file_id);
        $cFile->Delete($file_id);
    }
    
    /**
     * ������ ������� ������������������� ������������ � ��� �� ������� 
     * 
     * @param integer $uid �� ������������������� ������������
     */
    public function bindUserIDReg($ruid, $wuid = false) {
        if(!$wuid) $wuid = $this->_uid;
        $update = array("reg_uid" => $ruid);
        return $this->_db->update("wizard_action", $update, "wiz_uid = ?", $wuid);
    }
    
    /**
     * ����� �������������� ������ �� �������
     * 
     * @param string $wiz_uid  �� ������� ������������
     * @return type 
     */
    public function getFieldsUser($wiz_uid = false) {
        if(!$wiz_uid) $wiz_uid = $this->_uid;
        
        $sql = "SELECT * FROM wizard_fields WHERE wiz_uid = ?";
        $rows = $this->_db->rows($sql, $wiz_uid);
        
        if($rows) {
            foreach($rows as $key=>$value) {
                $result[$value['field_name']] = $value['field_value'];
            }

            return $result;
        }
        
        return false;
    }
    
    /**
     * ���������� �������������� ������ �� �������
     * 
     * @param array $data
     * @return type 
     */
    public function saveFieldsInfo($data) {
        if(!$data) return false;
        $this->clearFieldsInfo(array_keys($data));
        
        $sql = "INSERT INTO wizard_fields (field_name, field_value, field_type, wiz_uid) VALUES ";
        foreach($data as $key=>$value) {
            $insert[] = " ('{$key}', '{$value}', '".gettype($value)."', '".step_wizard::getWizardUserID()."') ";
        }
        
        $sql .= implode(", ", $insert);
        
        return $this->_db->query($sql);
    }
    
    /**
     * ������� �������������� ������ �� �������
     * 
     * @param array  $fields      ���� ��� ��������
     * @param string $wiz_uid     �� ������������ �������
     * @return type 
     */
    function clearFieldsInfo($fields, $wiz_uid = false) {
        if(!$wiz_uid) $wiz_uid = step_wizard::getWizardUserID();
        $sql    = "DELETE FROM wizard_fields WHERE wiz_uid = ? AND field_name IN (?l)";
        return $this->_db->query($sql, $wiz_uid, $fields);
    }
    
    function isUserWizard($uid, $step, $wizard) {
        global $DB;
        $sql = "SELECT wa.wiz_uid, wa.id 
                FROM wizard_action wa
                INNER JOIN wizard_to_step wts ON wts.pos = ?i AND wizard_id = ?i
                WHERE wa.reg_uid = ?i AND wa.id_wizard_to_step = wts.id";
        return $DB->row($sql, $step, $wizard, $uid);
    }
}

?>