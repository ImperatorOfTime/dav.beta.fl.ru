<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/wizard.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/step_employer.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/step_freelancer.php';

/**
 * ����� ��� ������ � �������� �����������
 *  
 */
class wizard_registration extends wizard
{
    const REG_FRL_ID = 2;
    const REG_EMP_ID = 1;
    
    /**
     * ��������� ���� ��� ����������� ������� 
     * @param type $role    1 - ������������, 2 - ���������
     * @return type 
     */
    public function setRole($role) {
        return setcookie($this->_cookie_names['role'], $role, $this->_lifeTimeCookie(), '/', $GLOBALS['domain4cookie']);
    }
    
    /**
     * ���������� �������� ���� �� ����
     * @return type 
     */
    public function getRole() {
        return $_COOKIE[$this->_cookie_names['role']];
    }
}


?>