<?php

/**
 * Class quickPaymentPopupFactory
 * ������� ��� �������� �������� "�������" ������
 */
class quickPaymentPopupFactory 
{
    const QPP_PROCESS_SESSION = 'quickPaymentPopupProcess';
    
    /**
     * �������� ������������ 
     * ������� ������� ������
     * 
     * @var type 
     */
    protected static $models = array(
        'reserve'           => 'reserve',
        'autoresponse'      => 'autoresponse',
        'frlbind'           => 'frlbind',
        'frlbindup'         => 'frlbindup',
        'carusel'           => 'carusel',
        'tservicebind'      => 'tservicebind',
        'tservicebindup'    => 'tservicebindup',
        'billinvoice'       => 'billInvoice',
        'account'           => 'account',
        'masssending'       => 'masssending',
        'pro'               => 'pro'
    );

    
    /**
     * �������� ������ ������������ ����� ������� ������
     * ��� �������� ����� �������
     * 
     * @return array
     */
    public static function getModelsList()
    {
        return array_keys(static::$models);
    }

    
    /**
     * ���� �� ���� � ������
     * 
     * @return type
     */
    public static function isExistProcess()
    {
        return isset($_SESSION[self::QPP_PROCESS_SESSION]);
    }

    

    /**
     * ������� ���������� � �������������� ������ ������ ��� �������� �� �� ����
     * 
     * @param type $type - ��� ������
     * @return object
     * @throws Exception
     */
    public static function getInstance($type = null) 
    {
        $type = (!$type)?@$_SESSION[self::QPP_PROCESS_SESSION]:$type;
        if (!$type || !in_array($type, array_keys(self::$models))) {
            throw new Exception("The type not found.");
        }
        
        $class = 'quickPaymentPopup' . ucfirst(self::$models[$type]);
        
        if (!class_exists($class, false)) {
            $filename = sprintf('%s/%s.php', __DIR__, $class);
            
            if (!file_exists($filename)) {
                throw new Exception("The class name $class could not be instantiated.");
            }
            
            require_once $filename;
        }
        
        return $class::getInstance();
    }  
    
}
