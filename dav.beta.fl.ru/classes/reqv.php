<?
/**
 * ����� ��� ������ � ������� �� ������������ ������� (����� ����)
 *
 */
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/country.php' );

class reqv {
	
	/**
	 * �� ������
	 *
	 * @var integer
	 */
	var $id;
	
	/**
	 * �� ������������
	 *
	 * @var integer
	 */
	var $user_id;
	
	/**
	 * �������� �����������
	 *
	 * @var string
	 */
  	var $org_name;
  	
  	/**
  	 * ���������� �������
  	 *
  	 * @var string
  	 */
  	var $phone;
  	
  	/**
  	 * ����
  	 *
  	 * @var string
  	 */
  	var $fax;
  	
  	/**
  	 * ����� ������
  	 *
  	 * @var string
  	 */
  	var $email;
  	
  	/**
  	 * ������. ID �� country
  	 *
  	 * @var string
  	 */
  	var $country_id;
  	
  	/**
  	 * �����. ID �� city
  	 *
  	 * @var string
  	 */
  	var $city_id;
  	
  	/**
  	 * ������. �������
  	 *
  	 * @var string
  	 */
  	var $country;
  	
  	/**
  	 * �����. �������
  	 *
  	 * @var string
  	 */
  	var $city;
  	
  	/**
  	 * �������� ������
  	 *
  	 * @var string
  	 */
  	var $index;
  	
  	/**
  	 * ������
  	 *
  	 * @var string
  	 */
  	var $address;
  	
  	/**
  	 * ������ ��������
  	 *
  	 * @var string
  	 */
  	var $address_grz;
  	
  	/**
  	 * ���
  	 *
  	 * @var integer
  	 */
  	var $inn;
  	
  	/**
  	 * ���
  	 *
  	 * @var integer
  	 */
 	var $kpp;
 	
  	/**
  	 * ����
  	 *
  	 * @var integer
  	 */
 	var $okpo;
 	
 	/**
 	 * ������ ��� �����������
 	 *
 	 * @var string
 	 */
  	var $full_name;
  	
  	/**
  	 * ������� ��� ��������
  	 *
  	 * @var string
  	 */
  	var $fio;
  	
  	/**
  	 * ����������� ������
  	 *
  	 * @var string
  	 */
  	var $address_jry;
  	
  	/**
  	 * �������� �����
  	 *
  	 * @var string
  	 */
  	var $bank_name;
  	
  	/**
  	 * ����� �����
  	 *
  	 * @var string
  	 */
  	var $bank_city;
  	
  	/**
  	 * ��������� ����
  	 *
  	 * @var string
  	 */
	var $acc;
	
  	/**
  	 * ��������� ����

  	 *
  	 * @var string
  	 */
	var $bank_rs;

	/**
	 * ����������������� ����
	 *
	 * @var string
	 */
  	var $bank_ks;
  	
	/**
	 * ���
	 *
	 * @var string
	 */
  	var $bank_bik;
  	
	/**
	 * ��. ������ ��� ����� (�����).
	 *
	 * @var integer
	 */
  	var $sbr_id;
  	
  	/**
  	 * �������� ���������� ����� �������
  	 *
  	 * @var string
  	 */
	var $pr_key = 'id';
        
        /**
         * �������� ������� ��� ���������� ����� ��� ���
         * @var boolean 
         */
        public $is_gift;
	
	/**
	 * ��������� ��������� ������ ���������� �� � ����� � �������� �� � ���������� �����
	 *
	 * @return string ��������� �� ������
	 */
	function CheckInput($sbr = false) {
		$this->org_name    = $this->org_name ? change_q(substr($this->org_name, 0, 128)) : '';
		$this->phone       = $this->phone ? substr(change_q($this->phone), 0, 24) : '';
		$this->fax         = $this->fax? substr(change_q($this->fax), 0, 24) : '';
		$this->email       = $this->email ? substr(change_q($this->email), 0, 64) : '';
		$this->country     = $this->country ? substr(change_q($this->country), 0, 64) : '';
		$this->country_id  = intval($this->country_id);
		$this->city        = $this->city ? substr(change_q($this->city), 0, 64) : '';
		$this->city_id     = intval($this->city_id);
		$this->index       = $this->index ? substr(change_q($this->index), 0, 7) : '';
		$this->address     = $this->address ? substr(change_q($this->address), 0, 128) : '';
		$this->address_grz = $this->address_grz ? substr(change_q($this->address_grz), 0, 128) : '';
		$this->inn         = $this->inn ? substr(change_q($this->inn), 0, 32) : '';
		$this->kpp         = $this->kpp ? substr(change_q($this->kpp), 0, 32) : '';
		$this->okpo        = $this->okpo ? substr(change_q($this->okpo), 0, 10) : '';
		$this->full_name   = $this->full_name ? change_q(substr($this->full_name, 0, 128)) : '';
		$this->fio         = $this->fio ? substr(change_q($this->fio), 0, 64) : '';
		$this->address_jry = $this->address_jry ? substr(change_q($this->address_jry), 0, 128) : '';
		$this->bank_name   = $this->bank_name ? substr(change_q($this->bank_name), 0, 64) : '';
		$this->bank_city   = $this->bank_city ? substr(change_q($this->bank_city), 0, 32) : '';
		$this->bank_ks     = $this->bank_ks ? substr(change_q($this->bank_ks), 0, 64) : '';
		$this->bank_rs     = $this->bank_rs ? substr(change_q($this->bank_rs), 0, 64) : '';
		$country_id        = country::getCountryId( $this->country );
		$aRequired         = array("org_name","phone","email","country_id","city_id","index","address","inn","full_name","address_jry");
		
		// ������� �� ������������ ���� � ����������� �� ��������
		if ( $this->country_id != 1 ) {
			unset( $aRequired[7] ); // inn
		}
		
		if ( $sbr ) {
		    unset( $aRequired[0] ); // org_name
		}
		
		$error = $this->check_required( $aRequired );
		
		if ( isset($error['country']) ) $error['country'] = '����������, �������� ������';
		if ( isset($error['city']) )    $error['city']    = '����������, �������� �����';
		
		if (!is_email($this->email)) { $error['email'] = "���� ��������� �����������";}
		if ($this->kpp && !preg_match( "/^\d{9}$/", $this->kpp )) { $error['kpp'] = "���� ��������� �����������";}
		if ( $country_id == 1 ) {
            if (!preg_match("/^\d{10,12}$/", $this->inn) || strlen($this->inn)==11) { $error['inn'] = "���� ��������� �����������";}
		}
		if (!preg_match( "/^([0-9]+)$/", $this->index )) { $error['index'] = "���� ��������� �����������";}
		if($this->okpo && !preg_match('/^(?:\d{8}|\d{10})$/', $this->okpo)) { $error['okpo'] = "���� ��������� �����������";}
		if(!preg_match("/^[A-Za-z\d\-\(\)+\s]+$/", $this->phone)) { $error['phone'] = "���� ��������� �����������"; }
		return $error;
	}
	
	/**
	 * ����� ������ �� �� ������������
	 *
	 * @param integer $uid �� ������������
	 * @return arrya|integer ������ �� �������, ���� 0
	 */
	function GetByUid($uid){
	    $current = get_class($this);
		$sql = "SELECT * FROM $current WHERE user_id = ? ORDER BY {$this->pr_key}";
	    
		if ( $ret = $GLOBALS['DB']->rows($sql, $uid) ) {
			if ( sizeof($ret) > 0 ) {
				return $ret;
			}
		}
		return 0;
	}
	
	/**
	 * �������� ������ �� ����������� ������.
	 * 
	 * @param  string $error ���������� ��������� �� ������ ���� ����.
	 * @param  bool $bReturnId ���������� �� ������������� ������.
	 * @return integer -1 - ���� �������� ������, 0 - ���� ����� �������� $return_id = 0 � ��� ������ �������, �� ��������� ������
	 */
	function Add( &$sError, $bReturnId = false ) {
	    global $DB;
	    
		$aData = $this->_getDataArray();
        
		if ( $aData ) {
		    $sReturn = ( $bReturnId ) ? $this->pr_key : '';
		    $mRes    = $DB->insert(get_class($this), $aData, $sReturn );
		    
            if ( $DB->error ) {
                $sError = $DB->error;
                return -1;
            }
            elseif ( $bReturnId ) {
                return $mRes;
            }
            else {
                return 0;
            }
		}
		
		return -1;
	}
	
	/**
	 * �������� ������ �� ����������� ������.
	 *
	 * @param  integer $id ������������� ��������� ����
	 * @return ���������� ��������� �� ������ ���� ����.
	 */
	function Update( $id = '' ) {
	    global $DB;
	    
	    $sError = '';
	    $aData  = $this->_getDataArray();
	    
	    if ( $aData ) {
	        if ( !$DB->update(get_class($this), $aData, $this->pr_key.' = ?', $id) ) {
	            $sError = $DB->error;
	        }
	    }
	    
	    return $sError;
	}
	
	/**
	 * ��������������� �������. �������� ���������� ������ � ������ ��� Add � Update.
	 * 
	 * @return array
	 */
	function _getDataArray() {
	    $aData = array();
	    $vars  = get_class_vars( get_class($this) );
	    
	    foreach ( $vars as $name => $value) {
	        if ( isset($this->$name) && $name != "pr_key" ) {
	            if ( strtolower($this->$name) == 'null' ) {
	                $sVal = null;
	            }
	            elseif ( strtolower($this->$name) == 'false' ) {
	                $sVal = false;
	            }
	            elseif ( strtolower($this->$name) == 'true' ) {
	                $sVal = true;
	            }
	            else {
	                $sVal = $this->$name;
	            }
                $aData[$name] = $sVal;
	        }
        }
        
        return $aData;
	}
	
	/**
	 * ������� ������ �� �������
	 *
	 * @param integer $id �� ��������� ����
	 * @param string  $addit ������� �������� (�� ��������� ��� ���, �� ��� �����������)
	 * @return string ��������� �� ������
	 */
	function Del($id, $addit = ""){
		$current = get_class($this);
		if ($id) $addit = $this->pr_key."='$id'" . $addit;
		if ($GLOBALS['DB']->query("DELETE FROM $current WHERE $addit")) {
			return '';
		} else {
			return 'DB Error';
		}
	}
	
	/**
	 * ���������������� ����� ������ ����������� �� �������
	 * ������ ������ ��������� ���������� � ������ �� �������, ��� � ����� ������
	 *
	 * @param  array $arr ������ ����������
	 * @return integer ������ ���������� 0
	 */
	function BindRequest($arr, $force = false){
        // �� ����� ID ������ ������� �� country_db_id, � ���� �������� � country_id (���� ����� � � �������)
        if ($arr['country_db_id'] && $arr['country_id'] === null) {
            $arr['country_id'] = $arr['country_db_id'];
        }
        if ($arr['city_db_id'] && $arr['city_id'] === null) {
            $arr['city_id'] = $arr['city_db_id'];
        }
		$class_vars = get_class_vars(get_class($this));
		foreach ($class_vars as $name => $value) {
			if ($force || isset($arr[$name])){
   				$this->$name = ($force && !isset($arr[$name])) ? '' : $arr[$name];
			}
		}
		return 0;
	}
	
	/**
	 * ����������� ��� ���������� ������� � ���������� ��������������� ������, 
	 * ������� ������������ � ��������� ������ ��� $this->[���� �� ��] = [�������� ��]
	 *
	 * @param integer $id    ������������� ��������� ����
	 * @param string  $addit ������� �������
	 * @param string  $order ����������
	 * @return integer ������ ���������� 1
	 */
	function GetRow($id = "", $addit = "", $order = ""){
		$current = get_class($this);
  $id = intval($id);
		if ($id) $addit = $this->pr_key."='$id'" . $addit;
		if ($order) $order = " ORDER BY ".$order;
		$out = $GLOBALS['DB']->row("SELECT * FROM $current WHERE ($addit)".$order);
		foreach ($out as $key => $value){
			$this->$key = $value;
		}
		return 1;
	}
	
	/**
	 * ����� ������ ������������� ���� �� �����
	 *
	 * @param integer $uid       �� ����
	 * @param string  $error     ���������� ��������� �� ������
	 * @param string  $fieldname ���� �������
	 * @return string ������ ����
	 */
	function GetField($uid, &$error, $fieldname){
		$current = get_class($this);
		return $GLOBALS['DB']->val("SELECT {$fieldname} FROM {$current} WHERE {$this->pr_key} = ?", $uid);
	}
	
	/**
	 * �������� ������������������� �� ����������� ����
	 * 
	 * @param array $reqvs ������ � ������� ����������� �����
	 * @return array ��������� �� �������
	 */
	function check_required($reqvs){
		foreach ( $reqvs as $varname ) {
			if ( !isset($this->$varname) || !$this->$varname ) {
				$error[$varname] = "���� ��������� �����������";
			}
		}
		return $error;
	}
}
?>
