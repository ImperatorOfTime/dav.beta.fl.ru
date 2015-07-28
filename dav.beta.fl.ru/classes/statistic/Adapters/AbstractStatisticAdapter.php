<?php

/**
 * ���������� ����� ��������
 */
abstract class AbstractStatisticAdapter 
{
    protected $service;
    protected $config;
    protected $options;

    protected $lastRequest;
    
    
    /**
     * ����������� ������������� �������
     * 
     * @param array $options
     * @param object $config
     */
    public function __construct($options = array(), $config = NULL) 
    {
        $default_options = array();
        
        if($config){
            $this->setConfig($config);
            $default_options = $this->config->options();
        }

        $options = (count($options))? $options + $default_options : $default_options;
        
        if(count($options)){
            $this->setOptions($options);
        }
        
        //����� ������ ��� ������������� ������� ����������
        $this->initService();
    }
    
    
    /**
     * ������� ������ ������������
     * 
     * @param object $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }
    
    
    /**
     * ������� ���������
     * 
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    
    /**
     * ������� ���������
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
    
    
    /**
     * ������������� ������� ����������
     * ����� ������ ���� ������ � ���������� ������
     */
    protected abstract function initService();
    
    
    
    /**
     * ���������� ������� � �������
     */
    public function queue($type, Array $data)
    {
        return $this->db()->query("SELECT pgq.insert_event('statistic', ?, ?)", 
                $type, 
                http_build_query($data));
    }
    
    
    /**
     * ����� ������ �������
     */
    public function call($type, Array $data)
    {
        if (method_exists($this, $type)) {
            return call_user_func_array(array($this, $type), $data);
        }
        
        return false;
    }

    
    
    public function getLastRequest()
    {
        return $this->lastRequest;
    }
    

    
    /**
     * @return DB
     */
    public function db()
    {
        return $GLOBALS['DB'];
    }
    
}
