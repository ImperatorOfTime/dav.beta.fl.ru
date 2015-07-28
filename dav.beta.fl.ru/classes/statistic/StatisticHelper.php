<?php

/**
 * ����������
 * ����� ����������� ������� ����������� ������
 */
class StatisticHelper 
{

    /**
     * ��������� ������ ��� �������� 
     * ����� �������� ������ � ���������
     * 
     * @param int $type ��� 0/1 ���������/������������
     * @param string $label ����� ��� ���������� - ��� ����������� �������� �����
     * @param string $uid ���������� ID ����� - ������������� login + uid
     * @return string
     */
    public static function track_url($type, $label, $timestamp, $uid)
    {
        $params = array(
            't' => (string)$type,
            'y' => (string)$label,
            's' => (string)$timestamp,
            'l' => md5($uid)
        );
        
        $params['h'] = md5(STAT_URL_PREFIX . serialize($params));
        return '/s.gif?' . http_build_query($params);
    }
    
    
    
}

