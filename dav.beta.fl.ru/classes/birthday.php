<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/settings.php");

/**
 * ����� ��� ������ � ������������� ��� �������� ��������
 *
 */
class birthday
{
    
    /**
     * ������� ���������� ��� ��������
     *
     * @var array
     */
    private $periods = array(2009=>array('2009-04-20','2009-05-13'));

    /**
     * ���� ������ �������� �����������
     *
     * @var date
     */
    public $regFromTm;

    /**
     * ���� �������� �����������
     *
     * @var date
     */
    public $regToTm;

    /**
     * ���, � ������� ��������
     *
     * @var integer
     */
    public $year;

    /**
     * ���� ��������
     *
     * @var integer
     */
    public $isClosed;



    /**
     * ����������� ������
     *
     * @param integer $year              ���, ��� �������� �������������� ������
     *
     * @return void
     */    
    function __construct($year)
    {
        $this->regFromTm = strtotime($this->periods[$year][0]);
        $this->regToTm   = strtotime($this->periods[$year][1]) + 24*3600;
        $this->year = $year;
        $status = settings::GetVariable('birthday'.$this->year, 'status');
        $this->isClosed = (!$status && (time() < $this->regFromTm || time() > $this->regToTm) || $status=='close');
    }



    /**
     * ������������� ��� ��������� ������ ��� ��������
     *
     * @param integer $status            ������
     *
     * @return integer                   1 � ������ ������, 0 � ������ ������
     */    
    function setStatus($status)
    {
        if(pg_affected_rows(settings::SetVariable('birthday'.$this->year, 'status', $status)))
            return 1;
        $sql = "INSERT INTO settings (id, module, variable, value) SELECT COALESCE(MAX(id),0)+1, 'birthday{$this->year}', 'status', '{$status}' FROM settings";
        if(pg_query(DBConnect(),$sql))
            return 1;
        return 0;
    }



    /**
     * �������� ������ ������������, ������������������� �� ���������
     *
     * @param integer $user_id           id ������������
     *
     * @return mixed                     ������ ������������ ��� NULL � ������ �������� ��� ���� ������������ �� ��������������� �� ���������
     */    
    function getUser($user_id)
    {
        if($user_id) {
            if(($res = pg_query(DBConnect(), "SELECT * FROM birthday WHERE uid = {$user_id} AND year = {$this->year}")) && pg_num_rows($res))
                return pg_fetch_assoc($res);
        }
        return NULL;
    }



    /**
     * ��������� ������ ������������ � ������, ����������� ������� � ������������ ��� ��������
     *
     * @param integer $user_id           id ������������
     * @param array $user                ������ � ������� ������������
     * @param boolean $edit              ���������� (false) ��� ���������� ������������ (true)
     *
     * @return integer                   1 � ������ ������, 0 � ������ ������
     */    
    function add($user_id, $user, $edit = false)
    {
        if(!$edit)
            $sql = "INSERT INTO birthday (uid, uname, usurname, utype, year) VALUES ({$user_id}, '{$user['uname']}', '{$user['usurname']}', {$user['utype']}, {$this->year})";
        else
            $sql = "UPDATE birthday SET (uname, usurname, utype) = ('{$user['uname']}', '{$user['usurname']}', {$user['utype']}) WHERE uid = {$user_id} AND year = {$this->year}";
        if(pg_query(DBConnect(),$sql))
            return 1;
        return 0;
    }



    /**
     * �������� ���������� ������������������ ������������� �� ���������
     *
     * @return integer                   ���������� �������������
     */    
    function getRegCount()
    {
        if(($res = pg_query(DBConnect(), "SELECT COUNT(uid) FROM birthday WHERE year = {$this->year}")) && pg_num_rows($res))
            return pg_fetch_result($res,0,0);
        return 0;
    }



    /**
     * �������� ������ ������������������ ������������� �� ���������
     *
     * @param mixed $accepted            ��� ������������ (NULL), ������������� �����������, ���������� (true), �� ������������� �����������, ���������� (false)
     * @param string $order_by           ���� � ������ ���������� (ORDER BY SQL)
     *
     * @return mixed                     ������ � ������� ������������� ��� 0 � ������ ��������
     */    
    function getAll($accepted = NULL, $order_by="")
    {
        $addit = ($order_by)?" ORDER BY ".$order_by.", id":" ORDER BY id";
        $sql = 
        "SELECT b.*, u.login, u.uname, u.usurname, u.email
           FROM birthday b
         INNER JOIN
           users u
             ON u.uid = b.uid
          WHERE b.year = {$this->year}
            ".($accepted===NULL ? '' : 'AND b.is_accepted = '.($accepted ? 'true' : 'false')).$addit;
        if(($res = pg_query(DBConnect(), $sql)) && pg_num_rows($res))
            return pg_fetch_all($res);
        return 0;
    }



    /**
     * ������������ ��� �������� ������� ������������ �� ������������� ��� ��������
     *
     * @param integer $id                id ������������
     *
     * @return integer                   1 � ������ ������, 0 � ������ ������
     */    
    function accept($id)
    {
        $sql = "UPDATE birthday SET is_accepted = NOT(is_accepted) WHERE id = {$id}";
        if(pg_query(DBConnect(), $sql))
            return 1;
        return 0;
    }



    /**
     * ������� ������������ �� ������
     *
     * @param integer $id                id ������������
     *
     * @return integer                   1 � ������ ������, 0 � ������ ������
     */    
    function del($id)
    {
        $sql = "DELETE FROM birthday WHERE id = {$id}";
        if(pg_query(DBConnect(), $sql))
            return 1;
        return 0;
    }
}
    
?>