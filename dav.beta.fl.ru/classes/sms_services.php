<?
/**
 * ���������� ���� � ��������� ���������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ������ � ������� ����� ���
 *
 */
class sms_services
{

    const DUP_OP_FULL     = 1; // ������ ����� (������ ��� ������)
    const DUP_OP_NOTSAVED = 2; // ��������� (�������� �� ������� ������, �� �� ���� ����������� � sms_operations).
    
    
    /**
     * ������� ������� (������ -- ����� ��������). ����������� ��������� SMS � USD.
     *  .descr -- ��������� � ������.
     * @var array
     */
    public static $tariffs =
      array ( '4161' => array('country'=>array('RU'=>4.75, 'UA'=>5, 'BY'=>5), 'descr'=>'����� 4161, ��������� SMS-��������� ���������� �������������� 160 ������ ��� ���. ��� ��������� ���� ������������ GSM ���������� �������, ����� ������ �������, ��������� SMS - 30 UAH. ����� � ������� � ������ ���. ������������� ������������ ���� � ���������� ���� � ������� 7,5% �� ��������� ������ ��� ����� ���. <br/>��� ��������� ��� �������� ��������� SMS � 25000 BYR.'),
              '4446' => array('country'=>array('RU'=>1,    'UA'=>1, 'BY'=>1), 'descr'=>'��������� SMS-��������� ��� �� ���������� �������������� 35 ������ � ���. ��������� ��� ������� ������� &mdash; 8 ������ � ������ ��� (������������� ������������ ���� � ���������� ���� � ������� 7,5%).'),
              '4449' => array('country'=>array('RU'=>3,    'UA'=>3, 'BY'=>3), 'descr'=>'����� 4449, ��������� SMS-��������� ���������� �������������� 90 ������ ��� ���. ��� ������� 16 UAH � ������ ���. ������������� ������������ ���� � ���������� ���� � ������� 7,5%. <br />��� ��������� ��� �������� ��������� SMS � 19900 BYR.')
            );

    /**
     * ������� ����������� �������� (������ -- ��� �������, ����������
     * � SMS-���������: 1=���������� �����), ������������ �� ������� (������� ���������).
     * � ��������� ����� FM, ���������� ��� ���������� ����� (��� 1) ��� 0 -- ������ ������ �����.
     * @var array
     */
    public static $services =array( 
        '1' => array( 
            '4449'=>array( 'fm_sum'=>'1.3', 'rur_sum'=>'90', 'byr_sum'=>'19&nbsp;900', 'uah_sum'=>'16' ),
            '4161'=>array( 'fm_sum'=>'2.5', 'rur_sum'=>'160', 'byr_sum'=>'24&nbsp;900', 'uah_sum'=>'30' )
        ), 
        '2' => array('4446'=>0) 
    );

    /**
     * ��������� � ���������� ����� �� ���� �������, ������ SMS � ������ ��������.
     *
     * @param string  $serviceCode   ��� ������ (1 -- ���������� �����; 2 -- ������� �����; 3 -- ������� ����� �� ������ � �.�.).
     * @param string  $phone         �����, �� ������� ������������ SMS ��� ������ ������ ������.
     * @param string  $countryCode   ������ �������� (�������������� ��� �� ISO 3166-1).
     * @param integer $error         ���������� ��� ������ (1 -- �������� ������; 2 -- �������� �����; 3 -- ������ �� ����������).
     * @return array   .usd_sum -- ��������� SMS (������� �������� � ��� ��������) � USD,
     *                 .fm_sum  -- ���������� ��������� � FM.
     */
    function checkTariff($serviceCode, $phone, $countryCode = 'RU', &$error = NULL)
    {
        $countryCode = strtoupper($countryCode);

        if(!($service = self::$services[$serviceCode]))
            $error = 1;
        else if(!self::$tariffs[$phone] || !isset($service[$phone]))
            $error = 2;
        else if(!($tariff = self::$tariffs[$phone]['country'][$countryCode]))
            $error = 3;

        if($error)
            return NULL;

        return array('usd_sum'=>$tariff, 'fm_sum'=>$service[$phone]['fm_sum']);
    }


    /**
     * ��������� ��� ������ �� ��� ����������� ������ (evtId).
     *
     * @global DB $DB
     * @param integer $evtId ID �������
     * @param integer $op_id   ���������� ��. account_operations.
     * @return integer   ��� �����, ��� 0, ���� �� �����.
     */
    function checkEvtId($evtId, &$op_id) {
        global $DB;
        $r = self::DUP_OP_FULL;
        $op_id = $DB->val('SELECT operation_id FROM sms_operations WHERE evt_id = ?', $evtId);
        if(!$op_id) {
            $op_id = $DB->val('SELECT id FROM account_operations WHERE payment_sys = 7 AND descr LIKE ?', "SMS #{$evtId} %");
            $r = self::DUP_OP_NOTSAVED;
        }
        return $op_id ? $r : 0;
    }
    
    
	/**
	 * ���������� ������ �� ��� � ��������� ������� ��� ������� ���������� �������
	 *
	 * @param integer $operation   �� �������� (account_operations)
	 * @param float $profit   ������� �� �������� � �������� ������
	 * @param string $currency_str   ��� ������ �������
	 * @param integer $evtId   ���������� ����� SMS �� I-Free
	 */
	function saveEvtId($operation, $profit = 0, $currency_str = 'RUB', $evtId = NULL) {
		global $DB;
        if($currency_str != 'RUB') {
            $now = time();
            $dtime = strtotime('-1 month', $now);
            while (date('m', $dtime) == date('m', $now)) {
                // �����: strtotime("-1 month", strtotime("2011-12-31")) -- ������ 2011-12-01
                $dtime = strtotime('-1 day', $dtime);
            }
            $day = date('t', $dtime);
            $currency = self::getCurrencyForDate(date($day.'/m/Y', $dtime));
            if(isset($currency[$currency_str])){
                $amount = $currency[$currency_str]['units'];
                $kurs = $currency[$currency_str]['kurs'];
                $single_kurs = $kurs / $amount;
                if ($currency_str == 'BYR') {
                    $single_kurs = round($single_kurs, 4);
                }
                $profit = $profit * $single_kurs;
            } else {
                $profit = NULL;
            }
        }
		$DB->query('INSERT INTO sms_operations (operation_id, profit, evt_id) VALUES(?, ?f, ?i)', $operation, $profit, $evtId);
	}
	
	/**
	 * ����� ����� ����� �� ������������ ����
	 *
	 * @param string $date ���� (� ������� 01/01/2009), �� ��������� ������� ����
	 * @return array|boolean ����� ����� ��� ���� ��� �������� ������ � �������� ��� ������ [units=>������, kurs=> ���� �� ��������� � �����], ���� false ���� �� ������� ����� ����� �����
	 */
	function getCurrencyForDate($date = false) {
		if(!$date) $date = date("d/m/Y");
        $mb = new memBuff();
        if($tmp = $mb->get('currency_for_date')){
            if($tmp['date'] == $date && $tmp['data']) {
                return $tmp['data'];
            }
        }
        libxml_disable_entity_loader();
		$file  = file_get_contents("http://www.cbr.ru/scripts/XML_daily.asp?date_req=$date&d=1"); // ������ ������� �������� ��� � �����
		$file2 = file_get_contents("http://www.cbr.ru/scripts/XML_daily.asp?date_req=$date&d=0"); // ������ ������� �������� ��� � ����
		
		$p  = simplexml_load_string($file);
		$p2 = simplexml_load_string($file2);
		
		$v = "Valute";
		
		foreach($p->$v as $key=>$value) {
			$cur[(string)($value->CharCode)] = array("units"=> intval($value->Nominal), "kurs"=> round(str_replace(",", ".", $value->Value), 4));
		}
		foreach($p2->$v as $key=>$value) {
			$cur[(string)($value->CharCode)] = array("units"=> intval($value->Nominal), "kurs"=> round(str_replace(",", ".", $value->Value), 4));
		}
		
		if(!isset($cur)) return false;
		
		$cur['RUB'] = array("units" => 1, "kurs"=>1); // �������� ��� �����
		$mb->set('currency_for_date', array('date' => $date, 'data' => $cur));
		return $cur;	
	}
	
	/**
	 * ������� ������������� ���������� ������� � ���
	 * @deprecated
	 *
	 * @param string $data  ���� ��������� � ������� MM/YYYY (11/2009)
	 * @return string|boolean ��������� �� ������, ���� false ���� �� ������� ����� ����� �����
	 */
	function recalc_sms_operation() {
		global $DB;
		$date_sql = "SELECT date_operation FROM sms_operations WHERE profit IS NULL /*AND date_operation < date_trunc('month', now())*/ ORDER BY date_operation ASC LIMIT 1";
        $date_operations = $DB->row($date_sql);
        if(!$date_operations) return false;
        $date_operation = array_shift($date_operations);
        
        $dtime = strtotime("-1 month", strtotime($date_operation));
        $day = date('t', $dtime);
	    
	    $currency = self::getCurrencyForDate(date($day.'/m/Y', $dtime));
        
	    if(!$currency) return false;
	    
        /* ������� ��������� ������� ��� �������� ������ �� ������� ������ (������� ����)*/
        $insert = "CREATE TEMP TABLE temp_currency (name varchar(3), kurs numeric(8,4)) ON COMMIT PRESERVE ROWS; ";
        foreach($currency as $code=>$val) $insert .= "INSERT INTO temp_currency VALUES('{$code}', '".($val['kurs']/$val['units'])."');";
        
        /* ��������� ������ */
        $insert .= "UPDATE sms_operations so SET profit = (t.profit*tc.kurs) 
                   FROM sms_tarif t INNER JOIN temp_currency tc ON (tc.name ILIKE t.currency) 
                   WHERE so.smstarif_id = t.id AND so.profit IS NULL
                   AND so.date_operation >= '$date_operation'
                   AND so.date_operation < date_trunc('month', timestamp '".$date_operation."' + interval '1 month')";
        
        $res = $DB->query($insert);
        
        return $DB->error;
	}
    
}
