<?
/**
 * ���������� ���� � ��������� ���������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reqv.php");

/**
 * �����, �������������� ��������� ������ �� ������������ �������, �� ������� ��� ����������� ������ (����. reqv_ordered)
 *
 */
class reqv_ordered extends reqv
{
	
	/**
	 * ������ �� ��������
	 *
	 * @var boolean
	 */
	public $pcheck;
	
	/**
	 * ������ �� ������ � ������
	 *
	 * @var boolean
	 */
	public $payed;
	
	/**
	 * ����� �� ����������
	 *
	 * @var float
	 */
	public $ammount;
	
	/**
	 * ���� ������� �����
	 *
	 * @var string (Postgres timestamp)
	 */
	public $op_date;
	
	/**
	 * ���� ��������������
	 *
	 * @var string (Postgres timestamp)
	 */
	public $edited;
	/**
	 * ���� ������� ��������
	 *
	 * @var string (Postgres timestamp)
	 */
	public $pcheck_time;
	
	/**
	 * ���� ������� ����� �� ����
	 *
	 * @var string (Postgres timestamp)
	 */
	public $payed_time;
	
	/**
	 * ������� �� ���������
	 *
	 * @var boolean
	 */
	public $docsend;
	
	/**
	 * ���� �������� ����������
	 *
	 * @var string (Postgres timestamp)
	 */
	public $docsend_time;
	
	/**
	 * id ���������� ������ �� ���� ����� (����. account_operations)
	 *
	 * @var integer
	 */
	public $billing_id;
	
	/**
	 * ����� ����� �� ������� ��� ������� �����
	 *
	 * @var integer
	 */
	public $bill_no;
	
	/**
	 * ��� ��������, ��� ������� ������������� ��� ������ (op_codes)
	 *
	 * @var integer
	 */
	public $op_code;
	
	/**
	 * ��������� �� ���������?
	 *
	 * @var boolean
	 */
	public $docback;
	
	/**
	 * ���� �������� ����������
	 *
	 * @var string (Postgres timestamp)
	 */
	public $docback_time;
	
	/**
	 * id ������ ��� ����� (�� ������ ������������� ��� ���)
	 *
	 * @var integer
	 */
	public $norisk_id;


 public $file_sf;
 public $file_act;
	
	/**
	 * ����������� ������
	 *
	 * @param object $reqv ����� reqv 
	 */
	function reqv_ordered($reqv = 0){
		if ($reqv){
			$class_vars = get_class_vars(get_class($reqv));
			foreach ($class_vars as $name => $value) {
    			if (isset($reqv->$name)){
    				$this->$name = $reqv->$name;
    			}
			}
		}
	}
	
	/**
	 * ������ ������ � ������� � ������������ ����� �� ������� ����������
	 *
	 * @return integer
	 */
	function SetOrdered(){
		unset($this->id);
		$error = '';
		$ret = $this->Add($error, 1);
		return $ret;
	}
	
	/**
	 * ���������� ��� �����, ���������� �� ������ ������
	 *
	 * @todo �������� ��� ������� � ������������� ���
	 * 
	 * @param string $fdate			    � ������ ����� �������� �����
	 * @param string $tdate			    �� ����� �����
	 * @param string $search            ��������� �����
	 * @param array  $sort              ��� ���������� [login=> DESC, fio=>ASC, ...]
     * @param string $date_search_type  �� ����� ����� ����, 1-����, 0-��� (X1X2X3, X1 - �� ���� ��������, X2 - �� ���� ������, X3 - �� ���� �������� ����������)
	 * @return array				    ���� �� ������
	 */
	function GetOrders($fdate, $tdate, $search=NULL, $sort = NULL, $date_search_type='111'){
	  if($sort) {
  	  $sort_fld = array_keys($sort);
  	  $sort_fld = $sort_fld[0];
  	  $dir = $sort[$sort_fld];
  	  switch($sort_fld) {
  	    case 'login': $orderby = "lower(u.login) {$dir}, ro.id"; break;
  	    case 'fio': $orderby = "COALESCE(NULLIF(lower(ro.full_name),''), lower(ro.org_name), '') {$dir}, ro.id"; break;
  	    case 'sum': $orderby = "ro.ammount {$dir}, ro.id"; break;
  	    case 'status': $orderby = "COALESCE(ro.payed_time, ro.pcheck_time, ro.docsend_time, ro.docback_time, 'epoch') {$dir}, ro.id"; break;
  	    case 'date': $orderby = "ro.id {$dir}"; break;
  	    default: $orderby = "ro.id"; break;
  	  }
  	}
    $where = '';
    if(substr($date_search_type,0,1)) {
        // ���� ��������
        $where .= " (op_date >= '$fdate' AND op_date-'1 day'::interval < '$tdate') OR ";
    }
    if(substr($date_search_type,1,1)) {
        // ���� ������
        $where .= " (payed_time >= '$fdate' AND payed_time-'1 day'::interval < '$tdate') OR ";
    }
    if(substr($date_search_type,2,1)) {
        // ���� �������� ����������
        $where .= " (docsend_time >= '$fdate' AND docsend_time-'1 day'::interval < '$tdate') OR ";
    }

    if($where)
        $where = '('.preg_replace("/OR $/","",$where) . ')';

    if($search) {
        $where .= $where ? ' AND' : 'WHERE';
        $where .= " (ro.fio ilike '%{$search}%'
                     OR ro.org_name ilike '%{$search}%'
                     OR ro.full_name ilike '%{$search}%'
                     OR '�-'||a.id||'-'||(COALESCE(ro.bill_no,0)+1) ilike '%{$search}%'
                     OR '�-���-'||s.id||'-'||CASE s.scheme_type WHEN 1 THEN '�' WHEN 2 THEN '�' ELSE '�' END||'/�' ilike '%{$search}%'
                     OR u.login ilike '%{$search}%')";
    }

    if($where) $where = ' WHERE '.$where;
    
        global $DB;
		$sql = 
		"SELECT ro.*, u.*, s.scheme_type, a.id as billing_id, ro.city as city_name, scheme_type
       FROM reqv_ordered ro
     INNER JOIN
       users u
         ON u.uid = ro.user_id
     LEFT JOIN
       sbr s
         ON s.id = ro.sbr_id
     INNER JOIN
       account a
         ON a.uid = u.uid
  		{$where}
			ORDER BY {$orderby}";


		$ret   = $DB->rows( $sql );
		$error = $DB->error;
		
		if ( $error ) {
			$ret = null;
		}
		return $ret;
	}
}
?>
