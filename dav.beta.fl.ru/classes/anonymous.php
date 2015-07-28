<?
/**
 * ���������� ���� � ��������� ���������
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ������ � ���������� ������� [���������]
 *
 */
class anonymous
{
	/**
	 * ������������� �������
	 *
	 * @var integer
	 */
	public $id;
	/**
	 * icq
	 *
	 * @var char
	 */
	public $icq;
	/**
	 * e-mail
	 *
	 * @var char
	 */
	public $mail;
	/**
	 * �������
	 *
	 * @var char
	 */
	public $phone;
	/**
	 * ip ������� �������
	 *
	 * @var char
	 */
	public $ip;
  	/**
  	 * ����� ������ ������
  	 *
  	 * @var datetime
  	 */
  	public $createtime;
	/**
	 * ���������� �� ������� �������
	 *
	 * @var bool
	 */
	public $visible;
	
	/**
	 * ������� �������
	 *
	 * @param char $error
	 * @return integer		������������� �������
	 */
	function Create( &$error ) {
	    global $DB;
		$id = 0;
		if(!$error){
		    $data = array( 'icq' => $this->icq, 'mail' => $this->mail, 'phone' => $this->phone, 'ip' => getRemoteIP() );
		    $DB->insert( 'anonymous', $data );
			$id    = $DB->val( "SELECT currval('anonymous_id_seq');" );
			$error = $DB->error;
		}
		return ($id);
	}
	
	/**
	 * �������� �������� "���������" ������� �� ���������������
	 *
	 * @param integer $aid		������������� �������
	 * @return char				��������� �� ������
	 */
	function ChVisible( $aid ) {
	    global $DB;
		$sql = "UPDATE anonymous SET visible = NOT visible::bool WHERE id = ?";
		$DB->squery( $sql, $aid );
		$error = pg_errormessage();
		return ($error);
	}
	
	/**
	 * �������� ���� �� �������
	 *
	 * @param integer $aid		������������� �������
	 * @return char				��������� �� ������
	 */
	function Update( $aid ) {
	    global $DB;
	    $data = array( 'icq' => $this->icq, 'mail' => $this->mail, 'phone' => $this->phone );
	    $DB->update( 'anonymous', $data, 'id = ?', $aid );
		return $DB->error;
	}
}
?>
