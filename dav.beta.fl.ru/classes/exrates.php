<?
/**
 * ����� ��� ������ � ������� ������ ����� ���������� ���������
 * 
 */
class exrates {
	
	/**
	 * ID ������.
	 *
	 * @var integer
	 */
	public $id;
	/**
	 * ���� ������.
	 *
	 * @var float
	 */
	public $val;
	/**
	 * Primary key ������� exrates.
	 *
	 * @var string
	 */
	public $pr_key = "id";	


	/**
	 * ID ��� FM
	 */
	const FM   = 1; 
	/**
	 * ID ��� WMZ
	 */
	const WMZ  = 2; 
	/**
	 * ID ��� WMR
	 */
	const WMR  = 3;
	/**
	 * ID ��� YandexMoney
	 */
	const YM   = 4;
	/**
	 * ID ��� ���������� �������� (�����)
	 */
	const BANK = 5;
    /**
     * ���-�������
     */
    const WEBM = 6;
    /**
     * ���������� �����
     */
    const CARD = 7;
    
    /**
     * ����
     */
    const QIWIPURSE = 8;
    
     /**
     * ���������
     */
    const OSMP = 9;

     /**
     * OKPay
     */
    const OKPAY = 14;

     /**
     * QiwiMobile
     */
    const MOBILE = 15;
	
	
	/**
	 * �������� ��� ����� ������
	 * @param   array   $arr   ������ � ������ �������, � �������:
	 *                         ������� - id ������, �������� - ���� ������
	 * @return  integer        1 - � ������ ������, ����� - 0
	 */
	function BatchUpdate( $arr ) {
		foreach ($arr as $ikey => $val){
			$vals[] = "INSERT INTO exrates (id, val) VALUES ('".$ikey."','".$val."')";
		}
		
		global $DB;
		$sql = "DELETE FROM exrates; ".implode('; ', $vals).";";
		
		if( $DB->squery($sql) ) return 1;
        
		return 0;
	}
	
	/**
	 * ���������� ������� �������� �����
	 * @return  array    ������ � �������� �������, � �������:
	 *                   ������� - id ������, �������� - ���� ������
	 */
	function GetAll() {
	    global $DB;
		$res = $DB->squery( "SELECT * FROM exrates" );
		$ret = pg_fetch_all($res);
		if ($ret)
			foreach($ret as $val){
				$out[$val['id']] = $val['val'];
			}
		return $out;
	}
	
	/**
	 * ����� ������ ������������� ���� �� �����
	 * 
	 * @param  integer $uid �� ����
	 * @param  string $error ���������� ��������� �� ������
	 * @param  string $fieldname ���� �������
	 * @return string ������ ����
	 */
	function GetField( $uid, &$error, $fieldname ) {
		$current = get_class($this);
		return $GLOBALS['DB']->val("SELECT {$fieldname} FROM {$current} WHERE {$this->pr_key} = ?", $uid);
	}
    
    static function getNameExratesForHistory($exr) {
        switch($exr) {
            case self::WMR:
                return 'WebMoney';
            case self::YM:
                return '������.��������';
            case self::BANK:
                return '����� ���������� ����';  
            case self::CARD:
                return '����������� ������';
            case self::WEBM:
                return '���-���������';
            case self::QIWIPURSE:
                return "QIWI-���������";
            case self::OSMP:
                return '����� ��������';
        }
    }
    
    function getNameExrates($exr) {
        switch($exr) {
            case self::FM:
                return '���� �� �����';
            case self::WMR:
                return '������� WMR';
            case self::YM:
                return '������� ������.������';
            case self::BANK:
                return '���������� ����';   
        }
    }
}

?>