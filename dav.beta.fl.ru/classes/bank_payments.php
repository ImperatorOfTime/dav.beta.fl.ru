<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/exchrates.php");

/**
 * ����� ��� ������ � ������� ����� ����
 *
 * @todo ��� � ����������� ���(�� ����������) ��������
 */
class bank_payments
{
  /**
   * ��� ���������
   *
   */
  const BC_SB = 1;
  const BC_SB_SBR = 2;

  /**
   * �� ������
   *
   * @var integer
   */
  public $id;
  
  /**
   * ������������� ������������
   *
   * @var integer
   */
  public $user_id;
  
  /**
   * ��� �����
   *
   * @var integer
   */
  public $bank_code;
  
  /**
   * ����� ��������
   *
   * @var integer
   */
  public $bill_num;
  
  /**
   * ��� �����������
   *
   * @var string
   */
  public $fio;
  
  /**
   * ������ �����������
   *
   * @var string
   */
  public $address;
  
  /**
   * ����� ������
   *
   * @var integer
   */
  public $sum;
  
  /**
   * ����� � ��
   *
   * @var integer
   */
  public $fm_sum;
  
  /**
   * ������������� ��������
   *
   * @var integer
   */
  public $billing_id;
  
  /**
   * ��� ��������
   *
   * @var integer
   */
  public $op_code;
  
  /**
   * ����� �������
   *
   * @var string
   */
  public $invoiced_time;
  
  /**
   * ����� ����������� �� ����
   *
   * @var string
   */
  public $accepted_time;
  
  /**
   * �������� ������� ��� ���������� ����� ��� ���
   * @var boolean 
   */
  public $is_gift;
  
  public $sbr_id;

  /**
   * �������� ���� �������
   *
   * @var string
   */
  protected $pr_key = 'id';

    /**
     * ��������� ������
     * 
     * @return string ��������� �� ������
     */
	function CheckInput() {
    	$this->address = substr(change_q($this->address), 0, 128);
    	$this->fio = substr(change_q($this->fio), 0, 64);
	    $this->bank_code = $this->bank_code ? $this->bank_code : self::BC_SB;
  	  $this->sum     = (float)$this->sum;
      setlocale(LC_ALL, 'en_US.UTF-8');
  	  $this->fm_sum  = $bp->sum / EXCH_TR;
  //	  if(isset($this->id))
//      	  $this->id = (int)$this->id;
  	  if(!$this->fio) $alert['fio'] = '���� ���������� �����������.';
  	  if(!$this->address) $alert['address'] = '���� ���������� �����������.';
  	  if(!$this->sum || $this->sum < 0.01) $alert['sum'] = '���� ���������� �����������.';
  	  return $alert;
	}
  
  
 	/**
	 * ���������� ���������� � ����� �� ��� ����. ���� ������� (����� ���� ����� � �� �� �������).
	 * ���� ����� ��������� ������ ����� (������ ���������-��� ����� �������������� �������)
	 *
	 * @param integer $bank_code ��� ����� (���� �������� � ���� ������)
	 * @return array ���������� �� �����.
	 */
  function GetBank($bank_code)
  {
    $bank;
    switch($bank_code) {

      case self::BC_SB:
         $bank = array('name'=>'��������', // �� ������ ������ ������������.
                       'prefix'=>'��',     // ������������ ��� ������������ ������ �����.
                       'payment_sys'=>5);  // ��� ������� account_operations
    }

    return $bank;
  }

	/**
	 * ���������� ����� �������� (���� ������ ��� ����� ����� ��� XXX-XXX-XXXX)
	 *
	 * @param integer $bank_code  ��� �����
	 * @param integer $user_id    �� ������������
	 * @param integer $account_id �� ��������
	 * @return string
	 */
  function GenBillNum($bank_code, $user_id, $account_id=NULL)
  {
    if(!($bank = self::GetBank($bank_code)))
        return NULL;
        
    $lst = self::GetLastReqv($bank_code, $user_id, 12);
    $ord = (int)preg_replace('/^'.$bank['prefix'].'-\d+-(\d+)/','$1',$lst['bill_num']) + 1;
    if(!$account_id) {
      global $DB;
      
      $account_id = $DB->val( "SELECT id FROM account WHERE uid = ?", $user_id );
    }

    if($account_id && $ord)
      return $bank['prefix'].'-'.$account_id.'-'.$ord;

    return NULL;
  }
  
  /**
   * �������� ����� ����������� �����
   * 
   * @param  int $id ID �������� � ����������������� �������
   * @return string
   */
  public static function GetBillNum($id){
        $sql = "SELECT bill_num FROM bank_payments WHERE billing_id = ?i LIMIT 1";
        global $DB;
        return $DB->val($sql,$id);
  }

  /**
   * ����� ��������� ����������� ������ ����� ����
   *
   * @param integer $bank_code ��� �����
   * @param integer $user_id   �� �����
   * @return array
   */
  function GetLastReqv($bank_code, $user_id, $op_code = NULL)
  {
    global $DB;
    
    $where = 'WHERE user_id = ? AND bank_code = ?';
    if($op_code)
        $where .= ' AND op_code = ?';
    $sql = "SELECT * FROM bank_payments {$where} ORDER BY invoiced_time DESC LIMIT 1";
    $res = $DB->row( $sql, $user_id, $bank_code, $op_code );

    return count($res) ? $res : null;
  }


 	/**
	 * ���������� ��� �����, ���������� �� ������ ������
	 *
	 * @param string $fdate			� ������ ����� �������� �����
	 * @param string $tdate			�� ����� �����
	 * @param string $search        ��������� �����
	 * @param array  $sort          ��� ���������� [login=> DESC, fio=>ASC, ...]
	 * @return array				���� �� ������
	 */
	function GetOrders($fdate, $tdate, $search = NULL, $sort){
	  $tdate = preg_replace("#\-0+#", "-0", $tdate);
	  $fdate = preg_replace("#\-0+#", "-0", $fdate);
	  $sort_fld = array_keys($sort);
	  $sort_fld = $sort_fld[0];
 	  $dir = $sort[$sort_fld];
	  switch($sort_fld) {
	    case 'login': $orderby = "lower(u.login) {$dir}, bp.id"; break;
	    case 'fio': $orderby = "lower(bp.fio) {$dir}, bp.id"; break;
	    case 'sum': $orderby = "bp.sum {$dir}, bp.id"; break;
	    case 'status': $orderby = "COALESCE(bp.accepted_time, 'epoch') {$dir}, bp.id"; break;
 	    case 'date': $orderby = "bp.id {$dir}"; break;
 	    default: $orderby = "bp.id"; break;
	  }
	  
	    global $DB;
		$sql = 
		"SELECT bp.*, u.login, u.photo, u.uname, u.usurname, u.role
		   FROM bank_payments bp
		 INNER JOIN
		   users u
		     ON u.uid = bp.user_id
		  WHERE bp.invoiced_time >= '$fdate' AND bp.invoiced_time < '$tdate'::date + 1".
		  ($search ? " AND (bp.fio ilike '%{$search}%'
		                    OR bp.bill_num ilike '%{$search}%'
		                    OR u.login ilike '%{$search}%') "
		           : ''
		  )."
		  ORDER BY {$orderby}";
		  
        $res = $DB->rows( $sql );
        
        return count($res) ? $res : null;
	}
    
    /**
	 * ����������� ��� ���������� ������� � ���������� ��������������� ������, 
	 * ������� ������������ � ��������� ������ ��� $this->[���� �� ��] = [�������� ��]
	 *
	 * @param  integer $id ������������� ��������� ����
	 * @param  string  $addit ������� �������
	 * @param  string  $order ����������
	 * @return integer ������ ���������� 1
	 */
	function GetRow( $id = "", $addit = "", $order = "" ) {
		$current = get_class($this);
  $id = intval($id);
		if ( $id ) $addit = $this->pr_key."='$id'" . $addit;
		if ( $order ) $order = " ORDER BY ".$order;
		$out = $GLOBALS['DB']->row("SELECT * FROM $current WHERE ($addit)".$order);
		foreach ( $out as $key => $value ) {
			$this->$key = $value;
		}
		return 1;
	}
	
	/**
	 * ���������������� ����� ������ ����������� �� �������
	 * ������ ������ ��������� ���������� � ������ �� �������, ��� � ����� ������
	 *
	 * @param  array $arr ������ ����������
	 * @return integer ������ ���������� 0
	 */
	function BindRequest( $arr, $force = false ) {
		$class_vars = get_class_vars(get_class($this));
		foreach ( $class_vars as $name => $value ) {
			if ( $force || isset($arr[$name]) ) {
   				$this->$name = ($force && !isset($arr[$name])) ? '' : $arr[$name];
			}
		}
		return 0;
	}
	
	/**
	 * ������� ������ �� �������
	 *
	 * @param  integer $id �� ��������� ����
	 * @param  string  $addit ������� �������� (�� ��������� ��� ���, �� ��� �����������)
	 * @return string ��������� �� ������
	 */
	function Del( $id, $addit = "" ) {
		$current = get_class($this);
		if ( $id ) $addit = $this->pr_key."='$id'" . $addit;
		if ( $GLOBALS['DB']->query("DELETE FROM $current WHERE $addit") ) {
			return '';
		} else {
			return 'DB Error';
		}
	}
	
	/**
	 * �������� ������ �� ����������� ������.
	 * 
	 * @param string $error ���������� ��������� �� ������ ���� ����.
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
	 * @param  string $add �������������� �������
	 * @return ���������� ��������� �� ������ ���� ����
	 */
	function Update( $id = '', $add = '' ) {
	    global $DB;
	    
	    $sError = '';
	    $aData  = $this->_getDataArray();
	    
	    if ( $aData ) {
	        if ( !$DB->update(get_class($this), $aData, $this->pr_key.' = ?' . $add, $id) ) {
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
}
?>