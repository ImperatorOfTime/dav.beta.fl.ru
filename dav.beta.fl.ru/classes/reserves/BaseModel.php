<?php

//@todo: ���� ��������� ����������� ����� ����� �� �� 
//����� ��� ����� �������� � ����� ����������
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/atservices_model.php');


/**
 *  ������� ����� ������
 */
abstract class BaseModel extends atservices_model
{
    
    /**
     * ������� ���� ����
     * @return ReservesModel
     */
    public static function model(array $options = array()) 
    {
        $class = get_called_class();
        return new $class($options);
    }
    
}
