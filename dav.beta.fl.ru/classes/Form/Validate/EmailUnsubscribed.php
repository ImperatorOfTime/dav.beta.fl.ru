<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/guest/models/GuestInviteUnsubscribeModel.php");

/**
 * Class Form_Validate_NoUserExists
 * 
 * ��������� ��������� ������� ���������� ���� ������������ 
 * �� ������ �� ������� ����������. 
 */
class Form_Validate_EmailUnsubscribed extends Zend_Validate_Abstract 
{
    const ERROR_USER_UNSUBSCRIBED  = 'unsubscribed';
    
    
    protected $_messageTemplates = array(
        self::ERROR_USER_UNSUBSCRIBED => '������������ � ���� e-mail ������� �������� �������� ��� �����������'
    );
    
    public function isValid($value) 
    {
        $isValid = true;
        
        $this->_setValue($value);
        
        $guestInviteUnsubscribeModel = new GuestInviteUnsubscribeModel();
       
        if ($guestInviteUnsubscribeModel->isUnsubscribed($value)) {
            
            $this->_error(self::ERROR_USER_UNSUBSCRIBED);
            $isValid = false;
        }
        
        return $isValid;
    }
}