<?php


/**
 * ����� ������ ��� ���� ���������
 * @todo: �������� �� ������ ���� ���� ������ ��� ��������� �������������
 */
class StatisticConfig
{
    protected $type;
    
    /**
     * ����������� ������������� ��� �������� � ������ ��������
     * ������� ����� ����� ������������ � �������� ����.
     * 
     * @param string $type
     */
    public function __construct($type) 
    {
        $this->type = strtolower($type);
    }

    
    /**
     * ���������� ������� ��������� �� ��������� ��� �������� ��������
     * 
     * @return array
     */
    public function options()
    {
        $ua_list = array(
            'release' => 'UA-163162-4', //release
            'beta' => 'UA-49313708-3', //beta
            'alpha' => 'UA-49313708-2', //alpha
            'local' => 'UA-59845348-1' //local - @todo: ����� ������������ ����
        );
        
        $srv = defined('SERVER')?strtolower(SERVER):'local';
        $ua = (isset($ua_list[$srv]))?$ua_list[$srv]:'';
        
        $default_options = array(
            
            'ga' => array(
                'v' => 1,
                'tid' => $ua,
                'cid' => md5($ua)
            )
            
            
            
        );
        
        return $default_options[$this->type];
    }
    
    
    /**
     * ���������� ��������� ��������� �� ����� ��� �������� ��������
     * 
     * @param string $key
     * @return string
     */
    public function text($key)
    {
        $default_text = array(
            
            'ga' => array(
                'newsletter_new_projects_freelancer' => '���������� �������� �������� �� �����������',
                'newsletter_new_projects_employer' => '���������� �������� �������� �� �������������',
                'sended' => '���������� %s',
                'open' => '������� �������� �� %s',//'�������. ��������: %s',
                'year' => '%d ���',
                'total' => '�����',
                'new' => '����� ������ �����', //� ������� ������
                'payed_ykassa' => '%s,ykassa'                
            )
            
        );
        
        if(!isset($default_text[$this->type][$key])) return FALSE;
        
        return $this->conv($default_text[$this->type][$key]);
    }

    
    /**
     * ��������� ���������
     * 
     * @param string $str
     * @return string
     */
    protected function conv($str)
    {
        return iconv('cp1251', 'utf-8', $str);
    }

}