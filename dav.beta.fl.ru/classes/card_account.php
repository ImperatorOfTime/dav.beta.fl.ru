<?
/**
 * ����� ��� ������ � ������������ ������� �������������.
 *
 */
class card_account
{
	/**
	 * �� ��������
	 *
	 * @var integer
	 */
	var $id;
	/**
	 * �� �������� ������������ (accounts.id)
	 *
	 * @var integer
	 */
	var $account_id;
	/**
	 * ���� ��������
	 *
	 * @var data
	 */
	var $post_date;
	
	/**
	 * �������� ������ ������������ ����� (��������� �������� ��, ���� �� �� �������)
	 *
	 * @param integer $id �� �����
	 * @return integer ���� �������� ���������� �� ��������, 0 - ���� ��� 
	 */
	function checkPayment($id) {
	    global $DB;
	    $this->account_id = NULL;
	    if ($row = $this->getPayments($id)) {
    	    foreach ( $row as $key => $val ) {
    			$this->$key = $val;
    		}
    		if ($this->id)
    		    $DB->query('DELETE FROM card_account WHERE id = ?', $this->id);
	    }
		
		return (int)$this->account_id;
	}
	
	function getPayments($id = NULL) {
	    global $DB;
	    $m = 'rows';
	    if($id) {
    	    $where = 'WHERE id = ?';
    	    $m = 'row';
	    }
	    return $DB->$m("SELECT * FROM card_account {$where} ORDER BY id", $id);
	}
	
	/**
	 * �������� ������
	 * 
	 * @return ������������� ����������� ������
	 */
	function Add() {
	    global $DB;
	    
		$aData = array('account_id' => $this->account_id);
	    $mRes  = $DB->insert('card_account', $aData, 'id');
	    
        if ( $DB->error ) {
            $sError = $DB->error;
            return -1;
        }
        else {
            return $mRes;
        }
	}
}
?>