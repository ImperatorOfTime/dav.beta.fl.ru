<?php


class Form_Filter_Carusel implements Zend_Filter_Interface
{

    public function filter($value)
    {
        $value = change_q_x($value, TRUE, FALSE);
        $value = strtolower( strtr ( $value, '���������������������������������', '���������������������������������' ) );
        $value = preg_replace('/(^|[.!?]\s+)([a-z�-�])/ie',"'$1'.strtoupper(strtr ( '$2', '���������������������������������', '���������������������������������' ))", $value);
        $value = str_replace("\r\n", "\n", $value);
        return $value;
    }
}
