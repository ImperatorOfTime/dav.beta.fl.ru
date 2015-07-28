<?php
/**
* ����� ��� ������ � ��������� ��� ������ � php ���������
*/

class Template
{
    /**
    * ��������� ������� �� ������� ������� � ��������� ���
    *
    * @param string $path ���� � �������
    * @param array $vars ���������� ��� �������� � ������
    * @return string ������� ��������������� �� ������ �������
    */
    public static function render($path, $vars = array())
    {
        extract($vars);

        ob_start();
        include($path);        
        return ob_get_clean();
    }
    
}
