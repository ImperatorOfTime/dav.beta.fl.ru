<?
/**
 * ����� ��� �������� � ��������� ��������� ������ ������������
 */
class login_change {
	/**
	 * id ���������
	 *
	 * @var integer
	 */
	public $id;
	/**
	 * UID
	 *
	 * @var integer
	 */
	public $user_id;
	/**
	 * ����� �����
	 *
	 * @var char
	 */
	public $new_login;
	/**
	 * ������ �����
	 *
	 * @var char
	 */
	public $old_login;
	
	/**
	 * ������� �� ������ �����
	 *
	 * @var boolean
	 */
	public $save_old;
	
	/**
	 * ID �������� � ������� account_operations
	 *
	 * @var boolean
	 */
	public $operation_id;
	
	/**
	 * ���� ��������
	 *
	 * @var string
	 */
	public $cdate;
	
	public $pr_key = "id";
	
	const OP_CODE = 70;
	
	/**
	 * ��������� ������ �����. ����� ������� ���������� ������������������� ����� ������
	 * old_login, new_login, save_old
	 * 
	 * @param string $error	���������� ��������� �� ������	
	 * @return 0
	 * @see classes/db_access#Add($error, $return_id)
	 */
	function Add(&$error){
	    global $DB;
	    
		require_once ABS_PATH.'/classes/users.php';
		$user = new users();
		$this->user_id = $user->GetUid($error, $this->old_login);
		if (!$this->user_id) {$error = "������������ �� ������!"; return 0;}
		$new_user = $user->GetUid($error, $this->new_login);
		if ($new_user) {$error = "����� �����!"; return 0;}
		if ($this->save_old){
			require_once ABS_PATH.'/classes/users_old.php';
			require_once ABS_PATH.'/classes/account.php';
			$account = new account();
			$tr_id = $account->start_transaction($this->user_id);
			$id = 0;
			$error = $account->Buy($id, $tr_id, login_change::OP_CODE, $this->user_id, "�������� ������", "��������� ������");
			if ($error) return 0;
			$this->operation_id = $id;
			$users_old = new users_old();
			$users_old->Add($this->old_login);
		}
		
        if (!$error) {
            $aData = array(
                'user_id'      => '',
                'old_login'    => '',
                'new_login'    => '',
                'save_old'     => '',
                'operation_id' => ''
            );
            
            foreach ( $aData as $key => $val ) {
            	$aData[$key] = $this->$key;
            }
            
    		$CFile = new CFile();
    		if (!$CFile->MoveDir($this->new_login, $this->old_login)) {
    			$error = "���������� �� �������! $this->new_login, $this->old_login";
    			if ($this->operation_id){
    				$account->Del($this->user_id,$this->operation_id);
    			}
    		} else {
                $DB->insert('login_change', $aData);
    			$user->login = $this->new_login;
    			$user->Update($this->user_id, $res);
    		}
        }
        return 0;
	}
	
	/**
	 * �������� ������ �� login_change �� ���� old_login � ������������� ���������� ������.
	 * 
	 * @param  string $sOldLogin old_login
	 * @return bool true - �����, false - ������
	 */
	function GetRowByOldLogin( $sOldLogin = '' ) {
	    global $DB;
	    
	    $bRet = true;
	    $aRow = $DB->row('SELECT * FROM login_change WHERE lower(old_login)=lower(?) ORDER BY id DESC', $sOldLogin);
	    
	    if ( is_array($aRow) && count($aRow) ) {
            foreach ( $aRow as $key => $val ) {
                $this->$key = $val;
            }
	    }
	    else {
	        $bRet = false;
	    }
	    
	    return $bRet;
	}
	
	/**
	 * ����� �� ����� ������:
	 * 1. �� ����������� ������.
	 * 2. �� ����������� ������ � ���������� ����.
	 * 3. ��� ����� ������ �� ��������� ������.
	 * 
	 * @param  string $login �����������. ������ ����� ��� ������ �� ������.
	 * @param  string $date �����������. ���� ����� ������ �� ������.
	 * @param  string $ds �����������. ��������� ���� ��� ������ �� ������.
	 * @param  string $de �����������. �������� ���� ��� ������ �� ������.
	 * @return array ������ �������.
	 */
	function getAllForAdmin( $login = '', $date = '', $ds = '', $de = '' ) {
	    global $DB;
	    
	    if ( $login ) {
        	$sWhere  = "old_login = '$login'";
        	$sWhere .= ( $date ) ? " AND cdate > '$date'" : '';
        } else {
            $ds  = ( $ds ) ? $ds : date('Y-m-d');
            $de  = ( $de ) ? $de : date('Y-m-d');
        	$sWhere = "cdate >= '$ds 00:00:01' AND cdate < '$de 23:59:59'";
        }
        
        return $DB->rows( "SELECT * FROM login_change WHERE $sWhere ORDER BY id" );
	}
	
	/**
	 * �������� ��� ������ � account::Del();
	 * 
	 * @param integer $uid	UID	
	 * @param integer $opid ������������� ��������
	 * @return 0
	 */
	function DelByOpid($uid, $opid){
		return 0;
	}
	
	/**
	 * �������� ��� ������ � account::GetHistoryInfo();
	 * 
	 * @param  integer $bill_id ������������� ��������
	 * @param  integer $uid ID ������������
	 * @param  integer $mode 1:������� �����; 2:������� ����� ��� ������; 3:�������
	 * @return string ��������� �������� ��������
	 */
	function GetOrderInfo( $bill_id, $uid, $mode ) {
	    return '';
	}
	
	/**
	 * �������� ��� ������ � account::Blocked();
	 * 
	 * @param  integer $uid ID ������������ ������� ���������� ����������
	 * @param  integer $opid ������������� ���� ��������
	 * @return string ��������� �� ������
	 */
	function BlockedByOpid( $uid, $opid ) {
	    return '';
	}
	
    /**
     * �������� ��� ������ � account::unBlocked();
     * 
     * @param integer $uid �� ��������������� ������ 
	 * @param integer $opid �� ��������������� ��������
     */
    function unBlockedByOpid( $uid, $opid ) {
        return true;
    }
}
?>