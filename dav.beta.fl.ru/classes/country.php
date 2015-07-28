<?
/**
 * ����� ��� ������ �� ��������
 *
 */
class country
{
    
    const ISO_RUSSIA = 643;
    
    //�������� ������ � �� ISO ���� 
    //��� �������� �������
    protected $iso_country_list = array(
        '������' => self::ISO_RUSSIA,
        '�������' => 804,
        '�����������' => 31,
        '���������' => 398,
        '����������' => 417,
        '������' => 428,
        '�����' => 440,
        '�������' => 498,
        '��������' => 496,
        '�����������' => 762,
        '������������' => 795,
        '����������' => 860,
        '�������' => 233,
        '�������' => 895,
        '����� ������' => 896,
        '������' => 268,
        '���������' => 32,
        '�������' => 51,
        '��������' => 112,
        '�������' => 376
    );
    
    
    static protected $_dataId_cache = array();




    /**
     * ����� ID ������ �� ����� � ��������
     *
     * @param    string    $translit    �������� ������ � ��������
     * @return   array                  ID ������
     */
    function getCountryIDByTranslit( $translit ) {
        global $DB;
        $id = $DB->val( 'SELECT id FROM country WHERE translit_country_name = ?', strtolower($translit) );

        return $id;
    }

    /**
     * ����� �������� ������ �� ��
     * 
     * @param  integer $id �� ������
     * @return string �������� ������
     */
    function GetCountryName( $id ) {
        global $DB;
        return $DB->cache(300)->val('SELECT country_name FROM country WHERE id = ?i', $id);
    }
    
    /**
     * ����� �� ������ �� ��������.
     * 
     * @param  string $name �������� ������
     * @return int �� ������
     */
    function getCountryId($name) 
    {
        global $DB;
        
        if (isset(self::$_dataId_cache[$name])) {
            return self::$_dataId_cache[$name];
        }        
        
        $ret = $DB->val('SELECT id FROM country WHERE country_name = ?', $name);
        
        if ($ret) {
            self::$_dataId_cache[$name] = $ret;
        }
        
        return $ret;
    }
    
    /**
     * ������ �� �����
     * 
     * @param type $name
     * @return type
     */
    function getCountryByName($name)
    {
        return $GLOBALS['DB']->cache(300)->row('SELECT * FROM country WHERE country_name = ?', $name);
    }

    
    /**
     * �� ����� ������ ������� �� ISO ���
     * 
     * @param type $name
     * @return type
     */
    function getCountryISO($name)
    {
        if (isset($this->iso_country_list[$name])) {
            return $this->iso_country_list[$name];
        }
        
        return $GLOBALS['DB']->cache(300)->val('SELECT iso FROM country WHERE country_name = ?', $name);
    }


    
    /**
     * ����� �� ������ �� �� ������
     * 
     * @param integer $id  �� ������
     * @return integer 
     */
    public function getCountryByCityId($id) {
        return $GLOBALS['DB']->val( 'SELECT country_id FROM city WHERE id = ?i', $id );
    }
    
    /**
     * ����� ��� ������ �� �������
     * 
     * @param  boolean $full ����� �� ������� ������ �������� ������ ��� ��� ���� (false - ������ ��������, true - ��� ����)
     * @return array ���������� �������
     */
    function GetCountries( $full = false ) {
        global $DB;
        $ret = $DB->rows( "SELECT * FROM country WHERE id <> '0' ORDER BY pos" );
        $out = array();
        
        if( !$full ) {
            foreach ( $ret as $value ) {
                $out[$value['id']] = $value['country_name'];
            }
        }
        else { 
            foreach ( $ret as $value ) {
                $out[$value['id']] = $value;
            }
        }
        
        return ($out);
    }
    
    /**
     * ���������� ������ �� �������
     * 
     * @param  string $limit ����� ������
     * @return array ������ �������
     */
    function CountAll( $limit = '' ) {
        $sql_limit = ( $limit ) ? ' LIMIT ' . (int)$limit : '';
        
        global $DB;
        $sql = 'SELECT country_name, COUNT(*) as cnt, country as country_id 
                FROM users LEFT JOIN country ON users.country = country.id GROUP BY country_name, country 
                ORDER BY cnt DESC' . $sql_limit;
        $ret = $DB->cache(1200)->rows( $sql );
        
        return ($ret);
    }
    
    /**
     * ���������� ������ ������������� �� ���������� ������������������ ������������� �� ���� ����� 
     * */
    function GetCountriesByCountUser() {
    	$cmd = "SELECT c.id AS id , count(uid) as nn, c.country_name AS name
							FROM country AS c
							LEFT JOIN users as u
							 ON c.id = u.country 							
							GROUP BY c.id, c.country_name  
							ORDER BY nn desc";
        $DB = new DB('master');
        $rows = $DB->cache(1200)->rows($cmd);
        return $rows;
    }
    
    function GetCountryIsoCode($country_id = 0) {
        return $GLOBALS['DB']->val( 'SELECT iso_code3 FROM country WHERE id = ?', $country_id);
    }
    
    
    /**
     * �������� �������� ������ � ������
     * 
     * @global type $DB
     * @param type $country_id
     * @param type $city_id
     * @return type
     */
    public function getCountryAndCityNames($country_id, $city_id = null)
    {
        global $DB;
        return $DB->row("
            SELECT
                co.country_name,
                ci.city_name,
                co.country_name || (CASE WHEN ci.city_name IS NOT NULL THEN ': ' || ci.city_name ELSE ': ��� ������' END) AS name
            FROM country AS co
            LEFT JOIN city AS ci ON ci.country_id = co.id ".(($city_id > 0)?"AND ci.id = {$city_id}":"AND ci.id IS NULL")."
            WHERE co.id > 0 AND co.id = ?i 
        ", $country_id);
    }
    
    
}