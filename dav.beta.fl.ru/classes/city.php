<?
/**
 * ���������� ���� � ��������� ���������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ������ � �������� � ��
 */
class city 
{
    static protected $_data_cache = array();

    /**
     * ����� ID ������ �� ����� � ��������
     *
     * @param    string    $translit    �������� ������ � ��������
     * @param    integer   $country_id  ID ������
     * @return   array                  ���������� � ������
     */
    function getCityIDByTranslit( $translit, & $country_id=0 ) {
        global $DB;
        if($country_id == 0) {
            $country_id = $DB->val( 'SELECT country_id FROM city WHERE translit_city_name = ?', strtolower($translit));
            $id = $DB->val( 'SELECT * FROM city WHERE translit_city_name = ? AND country_id = ?i', strtolower($translit), $country_id);
        } else {
            $id = $DB->val( 'SELECT * FROM city WHERE translit_city_name = ? AND country_id = ?i', strtolower($translit), $country_id );
        }
        return $id;
    }

    /**
     * �������� ��� ���������� � ������ �� ID
     *
     * @param    integer    $id    ID ������
     * @return   array             ���������� � ������
     */
    function getCity($id) {
        global $DB;
        $sql = "SELECT * FROM city WHERE id = ?i";
        $city = $DB->row($sql, intval($id));
        return $city;
    }

    /**
     * ����� �������� ������ �� ��� ��
     * 
     * @param  integer $id �� ������
     * @return string �������� ������
     */
    function GetCityName( $id ) {
        global $DB;
        return $DB->cache(300)->val('SELECT city_name FROM city WHERE id = ?i', $id);
    }
    
    /**
     * ����� �������� ������ �� �������������� ������ 
     * @param  integer $id �� ������
     * @return string �������� ������
     */
    function GetCountryName($cityId) {
        global $DB;
        $name = $DB->val( 'SELECT country_name FROM city LEFT JOIN country ON country.id = city.country_id WHERE city.id = ?i', $cityId );
        return ($name);
    }
    
    /**
     * ����� �� ������ �� ��������.
     * 
     * @param  string $name �������� ������
     * @return int �� ������
     */
    function getCityId( $name ) {
        return $GLOBALS['DB']->val( 'SELECT id FROM city WHERE city_name = ?', $name );
    }
    
    /**
     * ����� �� ������ �� �������� � ������.
     * 
     * @param  string $name �������� ������
     * @param  int $country_id �� ������
     * @return int �� ������
     */
    function getCityIdByCountry($name, $country_id) {
        return $GLOBALS['DB']->val( 'SELECT id FROM city WHERE city_name = ? AND country_id = ?i', $name, $country_id);
    }
    
    /**
     * ����� ��� ������ �� ������������ ������
     * 
     * @param  integer $country �� ������
     * @return array ������ �������
     */
    function GetCities( $country ) {
        if (!$country) return 0;
        
        global $DB;
        $sql = 'SELECT id, city_name FROM city WHERE country_id = ?i ORDER BY id IN(1,2) DESC, TRIM(city_name)';
        $ret = $DB->rows( $sql, $country );
        $out = array();
        
        if ( $ret ) {
            foreach ( $ret as $value ) {
                $out[$value['id']] = $value['city_name'];
            }
        }
        
        return ($out);
    }
    
    /**
     * ���������� ���� ������� � ��
     * 
     * @param  string $limit ����� ������ - '10 OFFSET 0'
     * @return array
     */
    function CountAll( $limit = '' ) {
        $sql_limit = ( $limit ) ? ' LIMIT ' . (int)$limit : '';
        
        global $DB;
        $sql = 'SELECT city_name, COUNT(*) as cnt FROM users 
                LEFT JOIN city ON users.city = city.id 
                GROUP BY city_name ORDER BY cnt DESC' . $sql_limit;
        $ret = $DB->cache(1200)->rows( $sql );
        
        return ($ret);
    }
    /**
     * ������� ���� ��������� � ����� � ������� ��� ���������.
     * @return array $rows 
     * */
    function GetCountriesAndCities(){
        $DB = new DB('master');
        $cmd = "SELECT country.id AS country_id, country.country_name, city.id, city.city_name AS name
                FROM country 
                LEFT JOIN city
                 ON city.country_id = country.id					
				ORDER BY country.pos, city.id
	            ";
        $rows = $DB->cache(1200)->rows($cmd);
        return $rows;
     }
     

     /**
      * ��� ������ �� ����� ������
      * 
      * @global type $DB
      * @param type $name
      * @return type
      */
     public function getByName($name)
     {
        global $DB;
         
        if (isset(self::$_data_cache[$name])) {
            return self::$_data_cache[$name];
        }
         
        $ret = $DB->cache(1200)->row('SELECT * FROM city WHERE city_name = ?', $name);
        
        if ($ret) {
            self::$_data_cache[$name] = $ret;
        }
        
        return $ret; 
     }
}