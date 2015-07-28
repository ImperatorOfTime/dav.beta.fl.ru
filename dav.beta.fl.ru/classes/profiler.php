<?php
/**
 * ��������� ��, ������ ������� ���������� ��������
 * 
 * @see        search
 * @category   Free-lance.ru
 * @package    System
 */
class profiler
{
    /**
     * ��������� �����.
     *
     * @var array
     */
    private $_start = array();

    /**
     * �������� �����.
     *
     * @var array 
     */
    private $_end = array();

    /**
     * ��������� ����� ������ ��� �������� � ��������� ������.
     *
     * @param string $key ����������� ���� ��� ��������
     */
    function start($key) {
        //��������� ������� ����� 
        $mtime = microtime(); 
        //��������� ������� � ������������ 
        $mtime = explode(" ",$mtime); 
        //���������� ���� ����� �� ������ � ����������� 
        $mtime = $mtime[1] + $mtime[0]; 
        //���������� ��������� ����� � ���������� � ��������� ������.
        $this->_start[$key] = $mtime;
    }

    /**
     * ��������� ����� ��������� ��� �������� � ��������� ������.
     *
     * @param string $key ����������� ���� ��� ��������
     */
    function stop($key) {
        //��������� ������� ����� 
        $mtime = microtime(); 
        //��������� ������� � ������������ 
        $mtime = explode(" ",$mtime); 
        //���������� ���� ����� �� ������ � ����������� 
        $mtime = $mtime[1] + $mtime[0]; 
        //���������� ����� ��������� � ���������� � ��������� ������.
        $this->_end[$key] = $mtime;
    }

    /**
     * ������� ����� ������ � ��������� ��� �������� � ��������� ������.
     * ���� ���� �� ������ - ������� ��� ������.
     *
     * @param string $key ����������� ���� ��� ��������
     */
    function clear($key = null) {
        if ($key === null) {
            $this->_start = array();
            $this->_end   = array();
        } else {
            if (is_array($this->_start) && (array_key_exists($key, $this->_start))) {
                unset($this->_start[$key]);
            }
            if (is_array($this->_end) && (array_key_exists($key, $this->_end))) {
                unset($this->_end[$key]);
            }
        }
    }

    /**
     * ���������� ����� ���������� �������� � ��������� ������.
     * ���� ���� �� ������ - ���������� ����� ���������� ���� ��������.
     *
     * @param string $key ����������� ���� ��� ��������
     * @return float ����� ��������� ��� false, ���� �������� �� ������� ��� �������� ���.
     */
    function get($key = null) {
        $result = false;
        if ($key === null) {
            if (is_array($this->_start) && (array_key_exists($key, $this->_start)) && is_array($this->_end) && (array_key_exists($key, $this->_end))) {
                $result = $this->_end[$key] - $this->_start[$key];
            }
        } else {
            if (is_array($this->_start)) {
                $result = 0;
                foreach ($this->_start as $key => $value) {
                    if (is_array($this->_end) && (array_key_exists($key, $this->_end))) {
                        $result += $this->_end[$key] - $this->_start[$key];
                    }
                }
            }
        }
        return $result;
    }
}