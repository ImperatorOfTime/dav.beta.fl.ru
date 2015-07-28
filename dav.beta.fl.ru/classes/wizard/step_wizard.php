<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/wizard.php';

/**
 * ����� ��� ������ � ������������ ������ �������
 *  
 */
class step_wizard 
{
    /**
     * ������ ���������� ����� ������� 
     */
    const STATUS_COMPLITED = 1;
    
    /**
     * ������ ������������� ����� 
     * ���� ����� ���� ������ �� ��� �������� �� ��������� ���� ���������� ����������� ����������� ����
     * �������� ��� ����������� �������������� �������� ��������� ��������
     */
    const STATUS_CONFIRM   = 2;
    
    /**
     * ����������� ������
     * 
     * @global object $DB ����������� � ��
     * @param integer $id  �� ����
     */
    public function __construct($id = false) {
        global $DB;
        
        $this->_db = $DB;
        $this->_id = $id;
    }
    
    /**
     * ������������� ������
     * 
     * @param integer $id �� ����
     * @return object 
     */
    public function initInstance($id = false) {
        $this->_id = $id;
        return clone $this;
    }
    
    /**
     * ������������� ������ ����
     * 
     * @param mixed $content     ������ ����, ���� false, ������� ����� �� �������
     */
    public function setContent($content = false) {
        if($content) {
            $this->data = $content;
        } else {
            $sql = "
                SELECT ws.*, wts.id as id_wiz_to_spec, wts.wizard_id, wts.step_id, wts.pos, wts.type_step, wa.status, wts.depend_pos, wa.reg_uid 
                FROM wizard_to_step wts 
                INNER JOIN wizard_step ws ON ws.id = wts.step_id 
                LEFT JOIN wizard_action wa ON wa.id_wizard_to_step = wts.id
                WHERE wts.id = ?i";
            $this->data = $this->_db->row($sql, $this->_id);
        }
    }
    
    /**
     * ����� ������ �� ����������� ����
     * 
     * @return string  
     */
    public function render() {
        return $this->name;
    }
    
    /**
     * ����� ������� � ���������� ����
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
        }
    }
    
    /**
     * �������� ����, ������� �� � ������ ������ ��� ���
     * 
     * @param integer $pos ������� ������� ����
     * @return boolean 
     */
    public function isActive($pos) {
        return ($pos == $this->parent->getPosition() );
    }
    
    /**
     * �������� ����, ��� �� �� �������� ��� ���
     * 
     * @return boolean 
     */
    public function isCompleted() {
        return ($this->status == self::STATUS_COMPLITED);
    }
    
    /**
     * �������� ����, ������� ��� ��� �������� ��� ���
     * 
     * @return boolean
     */
    public function isDisable() {
        // ���� ����� ����������� ����, � ���� ������ ������ �����������
        if($this->isCompleted()) {
            $type_step = ($this->type_step == 'f');
        } else {
            $type_step = false;
        }
        // ���� ��� ������� �� ������-���� ���� �� ����, �� ��������� ��� �������.
        if($this->depend_pos) {
            $depend_step = ($this->parent->steps[$this->depend_pos]->status != self::STATUS_COMPLITED);
        } else {
            $depend_step = false;
        }
        
        return ($type_step || $depend_step);
    }
    
    /**
     * ���������� ������������ ������ ���� �������
     * 
     * @param integer $status  ������ @see self::STATUS_*
     * @return boolean 
     */
    public function setStatusStep($status) {
        if(!$this->action_id) return false;
        return $this->_db->update("wizard_action", array("status" => $status, "reg_uid" => wizard::getUserIDReg()), "id = ?", $this->action_id);
    }
    
    public function setStatusStepAdmin($status, $uid, $action_id) {
        global $DB;
        return $DB->update("wizard_action", array("status" => $status), "reg_uid = ? AND id = ?", $uid, $action_id);
    }
    
    /**
     * ���������� �� ������������ ����, ���������� ��� ���� ����� ��� ����� ��� ����� ������� �� ������ �������
     * 
     * @return string
     */
    public function getWizardUserID() {
        if($this->parent instanceof wizard) {
            return $this->parent->getWizardUID();
        }
        
        return $_SESSION['WUID'];
    }
    
    /**
     * ������ ������ ������������ �� ����� 
     */
    public function clearSessionStep() {}  
}

?>