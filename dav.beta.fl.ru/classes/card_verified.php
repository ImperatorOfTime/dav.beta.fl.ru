<?
/**
 * ����� ��� ������ � ������������ ������� �������������.
 *
 */
class card_verified
{
	/**
	 * ��
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
	var $v_date;
	/**
	 * ����� �����
	 *
	 * @var string
	 */
	var $card_num;
	/**
	 * ������ �����
	 *
	 * @var string
	 */
	var $verified;
	/**
	 * ��������� ���� � �������
	 *
	 * @var string
	 */
	var $pr_key="id";
	
	/**
	 * �������� ����� (���� �� ������ ��������)
	 *
	 * @param integer $id �� �����
	 * @param integer $account_id ����� ����� �����
	 * @return boolean true ���� �������� �������, 0 - ���� ��� 
	 */
	function checkCard( $id, $account_id ) {
	    global $DB;
        
		$aRow = $DB->row( 'SELECT * FROM card_verified WHERE card_num = ?', $id );
		
		if ( $aRow ) {
			foreach ( $aRow as $key => $val ) {
				$this->$key = $val;
			}
		}
		
		if ($this->verified == 't') return true;
		elseif (!$this->id) {
			$this->account_id = $account_id;
			$this->card_num = $id;
			
			$DB->insert( 'card_verified', array('account_id' => $account_id, 'card_num' => $id) );
		}
		return false;
	}
	
	/**
	 * ��������� ������� ����� � "��������"
	 *
	 */
	function verifyCards() {
	    global $DB;
		$sql = "UPDATE card_verified SET verified = true WHERE verified = 'f' AND v_date + '1 day'::interval < now()";
		
		$DB->squery( $sql );
	}
}
?>