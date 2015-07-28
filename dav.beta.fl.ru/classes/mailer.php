<?php 

require_once $_SERVER['DOCUMENT_ROOT']."/classes/log.php";
require_once $_SERVER['DOCUMENT_ROOT']."/classes/smtp.php";

/**
 * ����� ��� ������ � ����� �������� ��������� 
 * 
 */
class mailer
{
    static $LINKS_HINT = array(
        '%USER_NAME%'     => '��� ������������',
        '%USER_SURNAME%'  => '������� ������������',
        '%USER_LOGIN%'    => '����� ������������',
        '%URL_PORTFOLIO%' => '������ �� ���������',
        '%URL_LK%'        => '������ �� ������ ������� ������������',
        '%URL_BILL%'      => '������ �� ������ ���� ������������'
    );
    
    static $TYPE_REGULAR = array( 
        1 => '��� ����������',
        2 => '�����������',
        3 => '����������',
        4 => '��������'
    );
    
    static $SUB_TYPE_REGULAR = array(
        2 => array(1 => '������ �����������', 
                   2 => '������ �������', 
                   3 => '������ �����', 
                   4 => '������ �������', 
                   5 => '������ �������', 
                   6 => '������ �������', 
                   7 => '������ �����������')
    );
    
    
    const LIMIT_MAILER_LIST = 20;
    
    /**
     * ������������ ���������� ������
     *
     */
    const MAX_FILE_COUNT    = 10;
    
    /**
     * ������������ ������ �������� �����
     *
     */
	const MAX_FILE_SIZE     = 5242880;
    
    /**
     * ����� ��� ������ �������� 
     */
    const FILE_DIR = "mailer/";
    
    /**
     * ������������ ���������� �������� ������ �� ���� ��� 
     */
    const MAX_SEND_USERS = 25000;
    
    /**
     * ���������� ��������� � �������� ���������� � ������������
     * 
     * @var array
     */
    protected $subfilter = array( 0 => 'efinance', 
                                  1 => 'ebuying', 
                                  2 => 'eproject',
                                  3 => 'emassend',
                                  4 => 'ffinance', 
                                  5 => 'fbuying', 
                                  6 => 'fproject',
                                  7 => 'fspec',
                                  8 => 'fblog',
                                  9 => 'flocation' );
    
    /**
     * @todo ����� ���� ����� �� �������� �������, ��� ������� �������� ������� �� ���� ����� �������� �������� ����� array_keys();
     * 
     * @var array
     */
    public static $buying_employer = array(7  => "������� ������, 300 ���. - ������ �������",
                                    8  => "������� ������",
                                    9  => "�������",
                                    12 => "���������� �����",
                                    13 => "������ ��������",
                                    15 => "������� PRO, ��� �������",
                                    16 => "������� ��� � �������",
                                    17 => "������ �������� � �������, 1 ������",
                                    23 => "�������",
                                    37 => "�������� ����� �� ����� ����������� ������",
                                    40 => "���������� ����� �� �������� ���� �� ������� ������ � �������",
                                    43 => "������� ����� �� ��������� ����������� ������",
                                    45 => "������� �������� �� ��������",
                                    46 => "������� ����� �� ��������",
                                    48 => "������� PRO �� �����",
                                    52 => "������� ��� � �������",
                                    53 => "������� ������",
                                    54 => "�������� ����� � ��������� ����� �� ������� ������ � �������",
                                    63 => "������� �������� ������� - ������� PRO",
                                    69 => "����� ������� ������� �������� � �������",
                                    70 => "��������� ������",
                                    71 => "�������������� ������ � ������� SMS",
                                    72 => "�������, 1500 ���. - ������ ��������",
                                    73 => "������� ����� ������� ��������",
                                    74 => "������� �������������",
                                    76 => "������� PRO �� ������",
                                    77 => "�������������� �����",
                                    78 => "�������� ����� �� ����� ����������� ������",
                                    79 => "������� ����� �� ����������� ������",
                                    82 => "������ ������� ���������",
                                    83 => "����� ������� �������� � �������",
                                    84 => "���������� �� �������� �������� � �������",
                                    85 => "���������� �� �������� ��������, ���������� ��������, � �������",
                                    86 => "������� �������",
                                    87 => "������� ������ ������ - 300 ���.",
                                    88 => "�������, 750 ���. - ������ ��������",
                                    90 => "������� PRO �� ����� � ������� (���������� ����� �����-���� �� ����� �� 1000 ������)",
                                    91 => "������� ��� ���������� ����� �� 2000 WMR",
                                    92 => "������� ��� ���������� ����� �� 1000 WMR",
                                    93 => "������� ��� ���������� ����� �� 5000 WMR",
                                    94 => "������ ����������� ����������",
                                    95 => "������� ��� ���������� ����� ����� ��������� ��������� �� 2000 ������",
                                    96 => "������� ��� ���������� ����� ����� ��������� ��������� �� 1000 ������",
                                    97 => "������� ��� ���������� ����� ����� ��������� ��������� �� 5000 ������",
                                    99 => "������� ��� ���������� ����� ����� ����������� ������ �� 2000 ������",
                                    100 => "������� ��� ���������� ����� ����� ����������� ������ �� 1000 ������",
                                    101 => "������� ��� ���������� ����� ����� ����������� ������ �� 5000 ������",
                                    103 => "������� ������, 600 ���. - ������ ������� (�� ���)",
                                    104 => "�������, 1050 ���. - ������ �������� (�� ���)",
                                    105 => "������� ������ ������ - 600 ���. (�� ���)",
                                    106 => "������� (�� ���)",
                                    107 => "������ ������������");
    
    /**
     * @todo ����� ���� ����� �� �������� �������, ��� ������� �������� ������� �� ���� ����� �������� �������� ����� array_keys();
     * 
     * @var array
     */
    public static $buying_freelance = array( 10 => "������ ��������, 1 ������",
                                      12 => "���������� �����",
                                      13 => "������ ��������",
                                      15 => "������� PRO, ��� �������",
                                      16 => "������� ��� � �������",
                                      17 => "������ �������� � �������, 1 ������",
                                      19 => "������� ����������, ����� �������",
                                      20 => "������� ����������, ���������� ��������",
                                      21 => "��������� ������� �������� ����������",
                                      23 => "�������",
                                      36 => "�������������� �����",
                                      38 => "������� ����� �� ����������� ������",
                                      43 => "������� ����� �� ��������� ����������� ������",
                                      45 => "������� �������� �� ��������",
                                      46 => "������� ����� �� ��������",
                                      47 => "�������� ������� PRO",
                                      48 => "������� PRO �� �����",
                                      49 => "������� PRO �� 3 ������",
                                      50 => "������� PRO �� 6 �������",
                                      51 => "������� PRO �� 12 �������",
                                      52 => "������� ��� � �������",
                                      53 => "������� ������",
                                      55 => "����� ������� �����",
                                      61 => "������� ����� �� ������",
                                      62 => "������� ����� �� ������ ����� SMS",
                                      63 => "������� �������� ������� - ������� PRO",
                                      64 => "������� �������� ������� - ������� ����������",
                                      65 => "������� ����� � ��������� �����",
                                      66 => "������� ��� � ������� �� 3 ������",
                                      69 => "����� ������� ������� �������� � �������",
                                      70 => "��������� ������",
                                      71 => "�������������� ������ � ������� SMS",
                                      73 => "������� ����� ������� ��������",
                                      74 => "������� �������������",
                                      75 => "�������� ��������",
                                      76 => "������� PRO �� ������",
                                      77 => "�������������� �����",
                                      79 => "������� ����� �� ����������� ������",
                                      80 => "������� �������������",
                                      81 => "����� �� ������� ������.������",
                                      82 => "������ ������� ���������",
                                      83 => "����� ������� �������� � �������",
                                      84 => "���������� �� �������� �������� � �������",
                                      85 => "���������� �� �������� ��������, ���������� ��������, � �������",
                                      90 => "������� PRO �� ����� � ������� (���������� ����� �����-���� �� ����� �� 1000 ������)",
                                      91 => "������� ��� ���������� ����� �� 2000 WMR",
                                      93 => "������� ��� ���������� ����� �� 5000 WMR",
                                      94 => "������ ����������� ����������",
                                      95 => "������� ��� ���������� ����� ����� ��������� ��������� �� 2000 ������",
                                      96 => "������� ��� ���������� ����� ����� ��������� ��������� �� 1000 ������",
                                      97 => "������� ��� ���������� ����� ����� ��������� ��������� �� 5000 ������",
                                      98 => "��������� ����������� ������",
                                      99 => "������� ��� ���������� ����� ����� ����������� ������ �� 2000 ������",
                                      102 => "������� ������ �� ������ � ����� � ����������� �������",
                                      106 => "������� (�� ���)",
                                      107 => "������ ������������",
                                      108 => "����������� �����",
                                      109 => "������������ �����");
    
    /**
     * ����������� ������, ��������� ��� ��������� ��� ��������.
     */
    public function __construct() {
    }
    
    /**
     * ������� �������� ��������
     * 
     * @global type $DB
     * @param type $insert 
     */
    public function create($insert) {
        global $DB;
        
        return $DB->insert("mailer_messages", $insert, "id");
    }
    
    /**
     * ���������� ��������
     * 
     * @global object $DB     ����������� � ��
     * @param array   $insert    ������ ��� ���������� @see self::initPost();
     * @param integer $id        �� �������� 
     */
    public function update($insert, $id) {
        global $DB;
        if(empty($id)) return false;
        if(is_array($id)) {
            $id = array_map("intval", $id);
            $where = "id IN (".implode(", ", $id).")";
        } else {
            $id = intval($id);
            $where = "id = {$id}";
        }
        return $DB->update("mailer_messages", $insert, $where);
    }
    
    /**
     * �������� �������� 
     * 
     * @global type $DB ����������� � ��
     * @param type $id  �� �������� 
     * @return boolean
     */
    public function delete($id) {
        global $DB;
        
        $sql = "DELETE FROM mailer_messages WHERE id = ?i";
        
        return $DB->query($sql, $id);
    }
    
    /**
     * ������� ���������� ��������
     * 
     * @global object $DB     ����������� � ��
     * @param string  $name      �������� ������� ������� @see self::checkDBTableFilter();  
     * @param array   $insert    ������ ��� ���������� @see self::initPostEmpFilter(), self::initPostFrlFilter();
     * @param integer $id        �� �������
     * @return null 
     */
    public function updateFilter($name, $insert, $id) {
        global $DB;
        if(!$this->checkDBTableFilter($name)) return null;
        if(($f = $this->execFilter($name, $id)) == 1) {
            $DB->update($name, $insert, "id = ?i", $id);
            return $id;
        } else {
            return $this->createFilter($name, $insert);
        }
    }
    
    /**
     * ������� �������� �������, ���������� ID ���������� ������� 
     * 
     * @global object $DB     ����������� � ��
     * @param string $name    �������� ������� (������� � ��) @see self::checkDBTableFilter()
     * @param array $insert   ������ ��� ������ @see DB::insert();
     * @return integer ID ���������� ������� 
     */
    public function createFilter($name, $insert) {
        global $DB;
        if(!$this->checkDBTableFilter($name)) return null;
        
        return $DB->insert($name, $insert, "id");
    }
    
    /**
     * �������� �������
     * @global type $DB
     * @param type $name
     * @param type $id
     * @return null 
     */
    public function deleteFilter($name, $id) {
        global $DB;
        if($id === 0) return null;
        if(!$this->checkDBTableFilter($name)) return null;
        
        $sql = "DELETE FROM {$name} WHERE id = ?";
        
        return $DB->query($sql, $id);
    }
    
    /**
     * �������� �� ������������� �������
     * 
     * @global object $DB     ����������� � ��
     * @param string $name    �������� ������� (������� � ��) @see self::checkDBTableFilter()
     * @param type $id        �� �������
     * @return null 
     */
    public function execFilter($name, $id) {
        global $DB;
        if(!$this->checkDBTableFilter($name)) return null;
        $sql = "SELECT 1 FROM {$name} WHERE id = ?i";
        return $DB->val($sql, $id);
    }
    
    /**
     * �������� �������� ������� ������� 
     * 
     * @param string $name    �������� ������� �������
     * @return boolean      true - �������� ���������, false - �����������
     */
    public function checkDBTableFilter($name) {
        if($name != "mailer_filter_employer" && $name != "mailer_filter_freelancer") return false;
        return true;
    }
	
	/**
	 * ��������� ���� ������ � ���������� ��� ���
	 * 
	 * @return type
	 */
	public function uploadExtra($old_file = '') {
		$cf = new CFile($_FILES['extra_recievers_file'], 'file');
		if ($cf) {
			$cf->server_root = true;
			$cf->max_size = 104857600; //100Mb
			if($filename = $cf->MoveUploadedFile("/mailer/")) {
				return $filename;
			}
		}
        
		return $old_file;
	}
	
	/**
	 * �� ����� �� ������� ������� �������� ������� ������ � ��������������� � ���� �������
	 * 
	 * @param type $filename
	 */
	function parseExtraList($filename, $offset = 0) {
		$list = array();
		$handle = fopen(WDCPREFIX_LOCAL . '/mailer/' . $filename, 'r');
		
		$row = 0;
		while ($row < $offset + self::MAX_SEND_USERS && ($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			if ($data[1] == 'uname' && $data[2] == 'usurname') {
				continue;
			}
			
			$row++;
			
			if ($row <= $offset) {
				continue;
			}
						
			$list[] = array(
				'uid' => (int)$data[0],
				'uname' =>  iconv("UTF-8", "CP1251", $data[1]),
				'usurname' => iconv("UTF-8", "CP1251", $data[2]),
				'login' => $data[3],
				'email' => $data[4]
			);
		}
		
		fclose($handle);
		return $list;
	}

	/**
     * ����� �����  ����������� � ��������
     * 
     * @global object $DB     ����������� � ��
     * @param integer $id     �� ��������
     * @return array ������ ������ ����������� � ��������
     */
    public function getAttach($id) {
        global $DB;
        // INNER JOIN file_mailer fm ON fm.id = ma.fid 
        $sql = "SELECT * FROM mailer_attach ma WHERE ma.sid = ?i ORDER by ma.sort";
        $rows = $DB->rows($sql, $id);
        
        return $rows;
    }
    
    /**
     *  ����������/�������� ������ � ��������
     * @param array   $files   ������ ������
     * @param integer $id      �� ��������
     */
    public function addAttachedFiles($files, $id) {
        if($files) {
            foreach($files as $file) {
                switch($file['status']) {
                    case 4:
                        // ������� ����
                        $this->delAttach($file['id']);   
                        break;
                    case 1:
                        // ��������� ����
                        $cFile = new CFile($file['id']);
                        $cFile->table = 'file_mailer';
                        $ext = $cFile->getext();
                        $tmp_name = $cFile->secure_tmpname(self::FILE_DIR, '.'.$ext);
                        $tmp_name = substr_replace($tmp_name,"",0,strlen(self::FILE_DIR));
                        $cFile->_remoteCopy(self::FILE_DIR.$tmp_name, true);
                        $this->insertAttachedFile($cFile->id, $id);
                        break;
                }
            }
        }
    }
    
    /**
     * �������� ���������� �����
     * @param integer $attach_id   �� �����
     */
    public function delAttach($attach_id) {
        $cfile = new CFile();
        $cfile->Delete($attach_id, self::FILE_DIR);
    }
    
    /**
     * ������ ����������� ������ � �� � ������� ������� 
     * @global object $DB     ����������� � ��
     * @param integer $attach_id   �� �����
     * @param integer $id          �� ��������
     */
    public function insertAttachedFile($attach_id, $id) {
        global $DB;
        $insert = array('sid' => $id, 'fid' => $attach_id);
        
        $DB->insert("mailer_attach", $insert); 
    }
    
    
    public function getDigestById($id) {
        global $DB;
        
        $sql = "SELECT mm.*, md.blocks FROM mailer_messages mm
                INNER JOIN mailer_digest md ON md.id_mailer = mm.id
                WHERE mm.id = ?";
        
        $res = $DB->row($sql, $id);
        $res['blocks'] = base64_decode($res['blocks']);
        return $res;
    }
    
    /**
     * ����� ��������� �������� �� ��� ��
     * 
     * @param integer $id �� ��������
     */
    public function getMailerById($id) {
        global $DB;
        
        $sql = "SELECT 
                mm.*, md.id as is_digest,
                
                mfe.type_account as etype_account, mfe.type_profile as etype_profile, mfe.from_regdate as efrom_regdate, mfe.to_regdate as eto_regdate, 
                mfe.from_lastvisit as efrom_lastvisit, mfe.to_lastvisit as eto_lastvisit, mfe.type_sex as etype_sex, 
                mfe.finance as {$this->subfilter[0]}, mfe.buying as {$this->subfilter[1]}, mfe.project as {$this->subfilter[2]}, mfe.massend as {$this->subfilter[3]},
                
                mff.type_account as ftype_account, mff.type_profile as ftype_profile, mff.type_portfolio as ftype_portfolio, mff.from_regdate as ffrom_regdate, 
                mff.to_regdate as fto_regdate, mff.from_lastvisit as ffrom_lastvisit, mff.to_lastvisit as fto_lastvisit, mff.type_sex as ftype_sex, 
                mff.finance as {$this->subfilter[4]}, mff.buying as {$this->subfilter[5]}, mff.project as {$this->subfilter[6]}, 
                mff.specs as {$this->subfilter[7]}, mff.blogs as {$this->subfilter[8]}, mff.geo as {$this->subfilter[9]},
                mff.regdate_interval as fregdate_interval

                FROM  mailer_messages mm
                LEFT JOIN mailer_filter_employer as mfe ON mfe.id = mm.filter_emp
                LEFT JOIN mailer_filter_freelancer as mff ON mff.id = mm.filter_frl
                LEFT JOIN mailer_digest md ON md.id_mailer = mm.id
                WHERE mm.id = ?i";
        
        $row = $DB->row($sql, $id);
        
        foreach($this->subfilter as $val) {
            if($row[$val]) $row[$val] = unserialize($row[$val]);
        }
        
        return $row;
    }
    
    /**
     * �������� �������� 
     * 
     * @global type $DB
     * @param integer $page    �������� �������
     * @param array   $filter  ������ �������
     * @param integer $count   ���������� ���������� �������� � ������ �������
     * @return array ������ �� ��������� 
     */
    public function getMailer($page = 1, $filter=false, & $count = 0) {
        global $DB;
        
        if($filter) {
            $filter_sql = $this->getFilterSQL($filter);
        }
        
        $order_by = $this->getOrderSQL($filter['sort']);
       
        
        if($page<=0) $page = 1;
        $limit = self::LIMIT_MAILER_LIST;
        $offset = self::LIMIT_MAILER_LIST * ( $page -1 );
        
        $sql_count = "SELECT COUNT(*) FROM mailer_messages m
                     LEFT JOIN mailer_digest md ON md.id_mailer = m.id
                     " . ($filter_sql ? "WHERE ".$filter_sql : "");
        $count = $DB->val($sql_count);
        
        $sql = "SELECT m.*, u.login, u.uname, u.usurname, u.is_pro, u.photo, md.id as is_digest FROM 
                mailer_messages m
                INNER JOIN users u ON u.uid = m.user_id 
                LEFT JOIN mailer_digest md ON md.id_mailer = m.id
                " . ($filter_sql ? "WHERE ".$filter_sql : "") . "
                {$order_by}
                LIMIT {$limit} OFFSET {$offset}";
                
        return $DB->rows($sql, $id);
    }
    
    public function getOrderSQL($sort) {
        switch($sort) {
            case 1:
                $order = "ORDER BY (m.count_rec_frl + m.count_rec_emp) DESC";
                break;
            case 2:
                $order = "ORDER BY (m.count_rec_frl + m.count_rec_emp) ASC";
                break;
            case 3:
                $order = "ORDER BY m.subject DESC";
                break;
            case 4:
                $order = "ORDER BY m.subject ASC";
                break;
            case 5:
                $order = "ORDER BY m.user_id DESC";
                break;
            case 6:
                $order = "ORDER BY m.user_id ASC";
                break;
            case 7:
                $order = "ORDER BY m.date_sending DESC, m.date_created DESC, m.real_date_sending DESC";
                break;
            case 8:
                $order = "ORDER BY m.date_sending ASC, m.date_created ASC, m.real_date_sending ASC";
                break;
            default:
                $order = "ORDER BY m.date_sending DESC, m.date_created DESC, m.real_date_sending DESC";
                break;
                
        }
        
        return $order;
    }
    
    /**
     * ��������� ������� ������� ��������
     * 
     * @param array $filter  ������
     * @return string|boolean 
     */
    public function getFilterSQL($filter) {
        // ���������� ������������
        if($filter['emp']) {
            $OR_sql[] = " filter_emp > 0 ";
        }
        // ���������� ����������
        if($filter['frl']) {
            $OR_sql[] = " filter_frl > 0 ";
        }
        // �����������
        if($filter['users']) {
            $sql[] = " user_id = {$filter['users']} ";
        }
        // ���� �������� �������� ��, ��
        if($filter['from'] && $filter['to']) {
            $filter['from'] = date('Y-m-d', strtotime($filter['from']));
            $filter['to'] = date('Y-m-d', strtotime($filter['to']));
            $sql[] = " ( date_created >= TIMESTAMP '{$filter['from']} 00:00:00' AND date_created <= TIMESTAMP '{$filter['to']} 23:59:59' ) ";
        } else if($filter['from']) {
            $filter['from'] = date('Y-m-d', strtotime($filter['from']));
            $sql[] = " ( date_created >= TIMESTAMP '{$filter['from']} 00:00:00' AND date_created <= NOW() ) ";
        } else if($filter['to']) {
            $filter['to'] = date('Y-m-d', strtotime($filter['to']));
            $sql[] = " ( date_created <= TIMESTAMP '{$filter['to']} 23:59:59' ) ";
        }
        
        // @todo 
        if($filter['keyword']) {
            $sql[] = " lower(subject) LIKE lower('%{$filter['keyword']}%') ";
        }
        // �����������
        if($filter['sending']) {
            $OR_sql[] = " status_message = 1 ";
        }
        // � ����������
        if($filter['draft']) {
            $OR_sql[] = " in_draft = true ";
        }
        // ���������� �����������
        if($filter['regular']) {
            $OR_sql[] = " (type_regular > 1 AND status_sending <> 3) ";
        }
        // ���������� �� �����
        if($filter['pause']) {
            $OR_sql[] = " status_sending = 3 ";
        }
        
        if($filter['digest']) {
            $OR_sql[] = " md.id IS NOT NULL";
        }
        
        if($filter['mailer']) {
            $OR_sql[] = " md.id IS NULL";
        }
        
        if($OR_sql) {
            $rsql[] = "( " . implode(" OR ", $OR_sql) . ")";
        }
        if($sql) {
            $rsql[] = "( " . implode(" AND ", $sql) . ")";
        }
        
        if($rsql) {
            $result = implode(" AND ", $rsql);
            return $result;
        }
        
        return false;
    }
    
    /**
     * ��������� �������� ������ �� ������ ��������
     * 
     * @param array $post    �������� ������ $_POST
     * @return array ������������� ������ ��� ������ array(name=>value) ��� name - ��� ���� � ������� value - �������� ����
     */
    public function initPost($post) {
        $insert = array();
        
        if($post['attachedfiles_session']) {
            $attachedfiles = new attachedfiles($post['attachedfiles_session']);
            $attachedfiles_files = $attachedfiles->getFiles(array(1,3,4));
            if(count($attachedfiles_files) > 0) {
                $insert['is_attached'] = true;
                $_POST['attachedfiles_files'] = $attachedfiles_files;
            } else {
                $insert['is_attached'] = false;
            }
        }
        
        $insert['in_draft'] = (int) $post['in_draft'] == 0 ? 'false' : 'true';
        
        if(isset($post['status_sending'])) {
            $insert['status_sending'] = (int) $post['status_sending'];
        }
        
        if(isset($post['status_message'])) {
            $insert['status_message'] = (int) $post['status_message'];
        }
        
        if(!is_empty_html($post['subject'])) {
            $insert['subject'] = __paramValue('string', addslashes($post['subject']));
        } else {
            $insert['subject'] = '�������� Free-lance.ru';
            //$this->error['subject'] = '������� ��������� ������';
        }
        
        if(!is_empty_html($post['message'])) {
            //$insert['message'] = addslashes(__paramValue('ckedit', stripslashes($post['message'])  ));
            $insert['message'] = $post['message'];
        } else {
            $this->error['message'] = '������� ����� ������';
        }
        
        if($post['type_sending']) {
            $post['type_sending'] = array_map('intval', $post['type_sending']);
            $insert['type_sending'] = (string) implode("", array((int) $post['type_sending'][0], (int) $post['type_sending'][1]));
        }
        
        if($post['type_regular']) {
            $insert['type_regular'] =  __paramValue('int', $post['type_regular']);
        }
        
        if($post['type_send_regular']) {
            $insert['type_send_regular'] =  __paramValue('int', $post['type_send_regular']);
        }
        
        if(!is_empty_html($post['time_sending'])) {
            $time = __paramValue('string', $post['time_sending']).":00";
            
            if(is_empty_html($post['date_sending'])) {
                $insert['date_sending'] = date('Y-m-d '.$time);
            }
        } elseif($insert['type_regular'] == 2) {
           $this->error['time_sending'] = '������� ����� �������� ��������'; 
        }
        
        if(!is_empty_html($post['date_sending'])) {
            $time = "00:00";
            if(!is_empty_html($post['time_sending'])) {
                $time = __paramValue('string', $post['time_sending']).":00";
            }
            
            $insert['date_sending'] = date('Y-m-d '.$time, strtotime($post['date_sending']));
        } elseif($insert['type_regular'] != 2) {
            $this->error['date_sending'] = '������� ���� �������� ��������';
        }
        
        // ��������� ������� ������������
        if($post['filter_emp']) {
            // ���������� ������
            $insert['filter_emp'] = $this->initPostEmpFilter($post);
        }
        
        if($post['filter_frl']) {
            // ���������� ������
            $insert['filter_frl'] = $this->initPostFrlFilter($post);
        }
		
        if($post['filter_file']) {
            $insert['filter_file'] = true;
        } else {
            $insert['filter_file'] = '';
        }
         
        $insert['user_id'] = get_uid();
        //$insert['count_recipients'] = 0;
        
        return $insert;
    }
    
    
    function checkRangeDate($from, $to, $error_name) {
        if(strtotime($from) > strtotime($to)) {
            $this->error[$error_name] = true;
        }
    }
    
    /**
     * ��������� �������� ������ �� ������ � ������ �������������
     * 
     * @param array $post    �������� ������ $_POST
     * @return integer ID ���������� ������� 
     */
    function initPostEmpFilter($post) {
        $filter_emp['type_account'] = subfilter::setType("bit", array((int)$post['etype_account'][0], (int)$post['etype_account'][1])); 
        $filter_emp['type_profile'] = subfilter::setType("bit", array((int)$post['etype_profile'][0], (int)$post['etype_profile'][1]));
        $filter_emp['type_sex']     = subfilter::setType("bit", array((int)$post['etype_sex'][0], (int)$post['etype_sex'][1]));

        if($post['efrom_regdate']) {
            $filter_emp['from_regdate'] = date('Y-m-d', strtotime($post['efrom_regdate']));
        }

        if($post['eto_regdate']) {
            $filter_emp['to_regdate'] = date('Y-m-d', strtotime($post['eto_regdate']));
        }
        
        $this->checkRangeDate($post['efrom_regdate'], $post['eto_regdate'], 'eregdate');

        if($post['efrom_lastvisit']) {
            $filter_emp['from_lastvisit'] = date('Y-m-d', strtotime($post['efrom_lastvisit']));
        }

        if($post['eto_lastvisit']) {
            $filter_emp['to_lastvisit'] = date('Y-m-d', strtotime($post['eto_lastvisit']));
        }
        
        $this->checkRangeDate($post['efrom_lastvisit'], $post['eto_lastvisit'], 'elastvisit');
        
        // �������
        if($post['efinance'] == 1) {
            $efinance_spend   = array_filter($post['efinance_spend']);
            $efinance_deposit = array_filter($post['efinance_deposit']);

            $EFinance = new UFinance();
            if($post['efinance_money']) $EFinance->setFilter("integer", "money", $post['efinance_money']/30);
            if(!empty($efinance_spend)) {
                $EFinance->setFilter("range_date", "spend", $post['efinance_spend']);
                if($EFinance->error['range_date']) {
                    $this->error['efinance_spend'] = true; 
                }
            }
            if(!empty($efinance_deposit)) {
                $EFinance->setFilter("range_date", "deposit", $post['efinance_deposit']);
                if($EFinance->error['range_date']) {
                    $this->error['efinance_deposit'] = true; 
                }
            }
            if($post['efinance_method_deposit']) $EFinance->setFilter("bit", "method_deposit", array((int)$post['efinance_method_deposit'][0], (int)$post['efinance_method_deposit'][1], (int)$post['efinance_method_deposit'][2], (int)$post['efinance_method_deposit'][3]));

            if($EFinance->is_update) $filter_emp['finance'] = (string) $EFinance; 
        }

        // �������
        if($post['ebuying'] == 1) {
            $ebuying_period = array_filter($post['ebuying_period']);

            $EBuying = new UBuying();
            if($post['ebuying_buying']) $EBuying->setFilter("bit", "buying", array((int)$post['ebuying_buying'][0], (int)$post['ebuying_buying'][1]));
            if(!empty($ebuying_period)) {
                $EBuying->setFilter("range_date", "period", $post['ebuying_period']);
                if($EBuying->error['range_date']) {
                    $this->error['ebuying_period'] = true; 
                }
            }

            foreach($post['ebuying_type_buy'] as $key=>$val) {
                $EBuying->startMultiFilter($key, "buy");
                if(is_array($post['ebuying_sum'][$key])) {
                    $post['ebuying_sum'][$key] = array_map(create_function('$a', 'return ($a/30);'), $post['ebuying_sum'][$key]);
                }
                $EBuying->setMultiValue('integer', 'type_buy', $val);
                $EBuying->setMultiValue('range_integer', 'count_buy', $post['ebuying_count_buy'][$key]);
                $EBuying->setMultiValue('range_integer', 'sum', $post['ebuying_sum'][$key]);

                $EBuying->endMultiFilter();
            }

            $filter_emp['buying'] = (string) $EBuying;
        }

        // �������
        if($post['eproject'] == 1) {
            $eproject_period = array_filter($post['eproject_period']);

            $EProject = new EProjects();
            if(!empty($eproject_period)) {
                $EProject->setFilter('range_date', "period", $post['eproject_period']);
                if($EProject->error['range_date']) {
                    $this->error['eproject_period'] = true; 
                }
            }
            $EProject->setFilter('range_integer', "created", $post['eproject_created']);
            $EProject->setFilter('range_integer', "freelance", $post['eproject_freelance']);
            $EProject->setFilter('range_integer', "only_pro", $post['eproject_only_pro']);
            $EProject->setFilter('range_integer', "in_office", $post['eproject_in_office']);
            $EProject->setFilter('range_integer', "konkurs", $post['eproject_konkurs']);
            $EProject->setFilter('range_integer', "budget", $post['eproject_budget']);
            $EProject->setFilter('range_integer', "sum_budget", $post['eproject_sum_budget']);
            $EProject->setFilter('range_integer', "avg_answer", $post['eproject_avg_answer']);
            if($post['eproject_executor']) $EProject->setFilter('bit', "executor", array((int)$post['eproject_executor'][0], (int)$post['eproject_executor'][1], (int)$post['eproject_executor'][2]));
            $EProject->setFilter('integer', "spec", $post['eproject_spec']);
            $filter_emp['project'] = (string) $EProject;
        } 

        // ��������
        if($post['emassend'] == 1) { 
            $EMassend = new EMasssend();
            $EMassend->setFilter("integer", "spec", $post['massend_spec']);
            $EMassend->setFilter("range_integer", "recipient", $post['massend_recipient']);
            $filter_emp['massend'] = (string) $EMassend;
        }
        
        return $filter_emp;
    }
    
    /**
     * ��������� �������� ������ �� ������ � ������ �����������
     * 
     * @param array $post    �������� ������ $_POST
     * @return integer ID ���������� ������� 
     */
    function initPostFrlFilter($post) {
        $filter_frl['type_account']   = subfilter::setType("bit", array((int)$post['ftype_account'][0], (int)$post['ftype_account'][1])); //(string) implode("", );
        $filter_frl['type_profile']   = subfilter::setType("bit", array((int)$post['ftype_profile'][0], (int)$post['ftype_profile'][1]));
        $filter_frl['type_portfolio'] = subfilter::setType("bit", array((int)$post['ftype_portfolio'][0], (int)$post['ftype_portfolio'][1]));
        $filter_frl['type_sex']       = subfilter::setType("bit", array((int)$post['ftype_sex'][0], (int)$post['ftype_sex'][1]));

        //�� ����� ������ ���� �������
        if($post['fregdate_interval']) {
            $filter_frl['regdate_interval'] = '2 mons';
        } else {
            $filter_frl['regdate_interval'] = null;
        }
        
        if($post['ffrom_regdate']) {
            $filter_frl['from_regdate'] = date('Y-m-d', strtotime($post['ffrom_regdate']));
        }

        if($post['fto_regdate']) {
            $filter_frl['to_regdate'] = date('Y-m-d', strtotime($post['fto_regdate']));
        }
        
        $this->checkRangeDate($post['ffrom_regdate'], $post['fto_regdate'], 'fregdate');

        if($post['ffrom_lastvisit']) {
            $filter_frl['from_lastvisit'] = date('Y-m-d', strtotime($post['ffrom_lastvisit']));
        }

        if($post['fto_lastvisit']) {
            $filter_frl['to_lastvisit'] = date('Y-m-d', strtotime($post['fto_lastvisit']));
        }
        
        $this->checkRangeDate($post['ffrom_lastvisit'], $post['fto_lastvisit'], 'flastvisit');

        // �������
        if($post['ffinance'] == 1) {
            $ffinance_spend   = array_filter($post['ffinance_spend']);
            $ffinance_deposit = array_filter($post['ffinance_deposit']);

            $ffinance_method = array((int)$post['ffinance_method_deposit'][0], 
                                        (int)$post['ffinance_method_deposit'][1], 
                                        (int)$post['ffinance_method_deposit'][2], 
                                        (int)$post['ffinance_method_deposit'][3]);

            $FFinance = new UFinance();
            if($post['ffinance_money']) $FFinance->setFilter("integer", "money", $post['ffinance_money']/30);
            if(!empty($ffinance_spend)) {
                $FFinance->setFilter("range_date", "spend", $post['ffinance_spend']);
                if($FFinance->error['range_date']) {
                    $this->error['ffinance_spend'] = true; 
                }
            }
            if(!empty($ffinance_deposit)) {
                $FFinance->setFilter("range_date", "deposit", $post['ffinance_deposit']);
                if($FFinance->error['range_date']) {
                    $this->error['ffinance_deposit'] = true; 
                }
            }
            if($post['ffinance_method_deposit']) $FFinance->setFilter("bit", "method_deposit", $ffinance_method);

            if($FFinance->is_update) $filter_frl['finance'] = (string) $FFinance; 
        }

        // �������
        if($post['fbuying'] == 1) {
            $ebuying_period = array_filter($post['fbuying_period']);

            $FBuying = new UBuying();
            if($post['fbuying_buying']) $FBuying->setFilter("bit", "buying", array((int)$post['fbuying_buying'][0], (int)$post['fbuying_buying'][1]));
            if(!empty($ebuying_period)) {
                $FBuying->setFilter("range_date", "period", $post['fbuying_period']);
                if($FBuying->error['range_date']) {
                    $this->error['fbuying_period'] = true; 
                }
            }

            foreach($post['fbuying_type_buy'] as $key=>$val) {
                $FBuying->startMultiFilter($key, "buy");
                
                if(is_array($post['fbuying_sum'][$key])) {
                    $post['fbuying_sum'][$key] = array_map(create_function('$a', 'return ($a/30);'), $post['fbuying_sum'][$key]);
                }
                $FBuying->setMultiValue('integer', 'type_buy', $val);
                $FBuying->setMultiValue('range_integer', 'count_buy', $post['fbuying_count_buy'][$key]);
                $FBuying->setMultiValue('range_integer', 'sum', $post['fbuying_sum'][$key]);

                $FBuying->endMultiFilter();
            }

            $filter_frl['buying'] = (string) $FBuying;
        }

        // �������
        if($post['fproject'] == 1) {
            $fproject_period = array_filter($post['fproject_period']);
            $fproject_count  = array_filter($post['fproject_count']);
            $FProject = new FProjects();

            if(!empty($fproject_period)) {
                $FProject->setFilter('range_date', "period", $post['fproject_period']);
                if($FProject->error['range_date']) {
                    $this->error['fproject_period'] = true; 
                }
            }
            if(!empty($fproject_count)) $FProject->setFilter('range_integer', "count", $post['fproject_count']);
            if($post['fproject_type']) $FProject->setFilter ('bit', 'type_project', array((int)$post['fproject_type'][0], (int)$post['fproject_type'][1], (int)$post['fproject_type'][2]));

            if($FProject->is_update) $filter_frl['project'] = (string) $FProject;
        }

        //�������������
        if($post['fspec'] == 1) {
            $FSpec = new FSpecs();

            $FSpec->setFilter('integer', 'spec_orig', $post['fspec_orig']);

            foreach($post['fspec_dspec'] as $key=>$val) {
                $FSpec->startMultiFilter($key, "specs");
                $FSpec->setMultiValue('integer', 'spec', $val);
                $FSpec->endMultiFilter();
            }

            $filter_frl['specs'] = (string) $FSpec;
        }

        if($post['fblog'] == 1) {
            $fblog_period = array_filter($post['fblog_period']);
            $fblog_post   = array_filter($post['fblog_post']);

            $FBlog = new FBlogs();

            if(!empty($fblog_period)) {
                $FBlog->setFilter('range_date', "period", $post['fblog_period']);
                if($FBlog->error['range_date']) {
                    $this->error['fblog_period'] = true; 
                }
            }
            if(!empty($fblog_post)) $FBlog->setFilter('range_integer', "post", $post['fblog_post']);

            if($FBlog->is_update) $filter_frl['blogs'] = (string) $FBlog;
        }

        if($post['flocation'] == 1) {
            $FLocation = new ULocation();

            $FLocation->setFilter('integer', 'country', $post['country']);
            $FLocation->setFilter('integer', 'city', $post['city']);

            $filter_frl['geo'] = (string) $FLocation;
        }
        return $filter_frl;
    }
    
    /**
     * ������� ������ ������������� ����������� ��������
     * 
     * @global type $DB
     * @return array ������ �������������
     */
    public function getUsersSender() {
        global $DB;
        $sql = "SELECT DISTINCT ON (uid) login, uid, uname, usurname FROM mailer_messages mm INNER JOIN users u ON u.uid = mm.user_id GROUP BY uid";
        $result = $DB->rows($sql);
        return $result;
    }
    
    /**
     * �������� ���� ��������� ��������
     * @deprecated 
     */
    public function getNextDateSending() {
        $nextDay = 8 - date('N');
        $time    = strtotime("+ {$nextDay}days");
        
        return $time;
    }
    
    /**
     * �������� ������ � ���������� 
     * 
     * @param type $post    $_POST
     * @return type 
     */
    public function loadPOST($post) {
        $row = $this->initPost($post);
        $row['subject'] = stripslashes($row['subject']);
        $row['message'] = stripslashes($row['message']);
        $emp = array('type_account' => 'etype_account', 'type_profile' => 'etype_profile', 'from_regdate' => 'efrom_regdate', 
            'to_regdate' => 'eto_regdate', 'from_lastvisit' => 'efrom_lastvisit', 'to_lastvisit' => 'eto_lastvisit', 'type_sex' => 'etype_sex',
            'finance' => $this->subfilter[0], 'buying' => $this->subfilter[1], 'project' => $this->subfilter[2], 'massend' => $this->subfilter[3]
        );  
        $frl = array('type_account' => 'ftype_account', 'type_profile' => 'ftype_profile',  'type_portfolio' => 'ftype_portfolio', 'from_regdate' => 'ffrom_regdate', 
            'to_regdate' => 'fto_regdate', 'from_lastvisit' => 'ffrom_lastvisit', 'to_lastvisit' => 'fto_lastvisit', 'type_sex' => 'ftype_sex',
            'finance' => $this->subfilter[4], 'buying' => $this->subfilter[5], 'project' => $this->subfilter[6], 
            'specs' => $this->subfilter[7], 'blogs' => $this->subfilter[8], 'geo' => $this->subfilter[9],
            'regdate_interval' => 'fregdate_interval'
        );
        
        if(isset($row['filter_emp'])) {
            foreach($row['filter_emp'] as $key=>$val) {
                $row[$emp[$key]] = $val;
            }
            $row['filter_emp']= (int)$post['id_filter_emp'] ? (int) $post['id_filter_emp'] : (int)$post['filter_emp'];
        } else {
            $row['filter_emp'] = (int)$post['filter_emp'];
        }
        
        if(isset($row['filter_frl'])) {
            foreach($row['filter_frl'] as $key=>$val) {
                $row[$frl[$key]] = $val;
            }
            $row['filter_frl']= (int)$post['id_filter_frl'] ? (int) $post['id_filter_frl'] : (int)$post['filter_frl'];
        } else {
            $row['filter_frl'] = (int)$post['filter_frl'];
        }
        
        
        
        foreach($this->subfilter as $val) {
            if($row[$val]) $row[$val] = unserialize($row[$val]);
        }
        
        return $row;
    }
    public function setMainWhereSQL(&$sql, $bit="OR") {
        if($sql == null) return "";
        if(sizeof($sql) == 1) {
            $result = current($sql) . "\r\n";
        } else {
            $result = " ( ".implode(" {$bit} ", $sql)." )\r\n";
        }
        $sql = array();
        return $result;
    }
    
    public function getSQLFilterFreelancer($filter, $fields = "uid") {
        global $DB;

        if(isset($filter['filter_frl'])) {
            $main_sql[] = "f.active = true AND substring(f.subscr from 8 for 1)::integer = 1 AND f.is_banned = B'0'";
            // �������
            if($filter['ftype_account'][0] == 1) {
                $sql[] = "f.is_pro = true";
            }
            if($filter['ftype_account'][1] == 1) {
                $sql[] = "f.is_pro = false";
            }
            if(sizeof($sql) == 1) $main_sql[] = self::setMainWhereSQL($sql);
            $sql = array();
            // �������
            if($filter['ftype_profile'][0] == 1) {
                $sql[] = "r.o_inf_factor > 0";
            }
            if($filter['ftype_profile'][1] == 1) {
                $sql[] = "r.o_inf_factor = 0";
            }
            if(sizeof($sql) == 1) {
                $inner_sql['rating'] = "LEFT JOIN rating r ON r.user_id = f.uid";
                $main_sql[] = self::setMainWhereSQL($sql);
            }
            $sql = array();

            // ���������
            if($filter['ftype_portfolio'][0] == 1) {
                $sql[] = "r.o_wrk_factor_a > 0";
            }
            if($filter['ftype_portfolio'][1] == 1) {
                $sql[] = "r.o_wrk_factor_a = 0";
            }
            if(sizeof($sql) == 1) {
                $inner_sql['rating'] = "LEFT JOIN rating r ON r.user_id = f.uid";
                $main_sql[] = self::setMainWhereSQL($sql);
            }
            $sql = array();

            // ���������������
            if($filter['ffrom_regdate']) {
                $date = date('Y-m-d', strtotime($filter['ffrom_regdate']));
                $sql[] = $DB->parse("f.reg_date >= DATE ?", $date);
            }
            if($filter['fto_regdate']) {
                $date = date('Y-m-d', strtotime($filter['fto_regdate']));
                $sql[] = $DB->parse("f.reg_date <= DATE ?", $date);
            }
            
            //�� ����� ��������������� ��������� �����
            if($filter['fregdate_interval']) {
                $sql[] = $DB->parse("f.reg_date > (NOW() - ?::interval)", $filter['fregdate_interval']);
            }
            
            if($sql) $main_sql[] = self::setMainWhereSQL($sql, "AND"); //" ( ".implode(" AND ", $freg)." )\r\n";
            
            // ��������� �����
            if($filter['ffrom_lastvisit']) {
                $date = date('Y-m-d', strtotime($filter['ffrom_lastvisit']));
                $sql[] = $DB->parse("f.last_time >= DATE ?", $date);
            }
            if($filter['fto_lastvisit']) {
                $date = date('Y-m-d', strtotime($filter['fto_lastvisit']));
                $sql[] = $DB->parse("f.last_time <= DATE ?", $date);
            }
            if($sql) $main_sql[] = self::setMainWhereSQL($sql, "AND");

            //���
            if($filter['ftype_sex'][0] == 1) {
                $sql[] = "f.sex = true";
            }
            if($filter['ftype_sex'][1] == 1) {
                $sql[] = "f.sex = false";
            }
            if(sizeof($sql) == 1) $main_sql[] = self::setMainWhereSQL($sql);
            $sql = array();

            // ���������
            if($filter['flocation']) {
                $flocation = $filter['flocation'];

                if($flocation['country'] > 0) {
                    $main_sql[] = $DB->parse(" f.country = ?", $flocation['country']);
                } 
                if($flocation['city'] > 0) {
                    $main_sql[] = $DB->parse(" f.city = ?", $flocation['city']);
                }
            }

            // �������������
            if($filter['fspec']) {
                $fspec = $filter['fspec'];
                $spec_sql = '';
                if(intval($fspec['spec_orig']) > 0) {
                    $spec_sql = "(f.spec_orig = ".intval($fspec['spec_orig']) . 
                                   (intval($fspec['specs'][0]['spec']) > 0 ? " OR s.prof_id = ".intval($fspec['specs'][0]['spec']). " OR sp.prof_id = ".intval($fspec['specs'][0]['spec']) : "") . 
                                  ")";
                    if ( intval($fspec['specs'][0]['spec']) == 0 ) {
                        $spec_sql = "(f.spec_orig IN( SELECT id  FROM professions WHERE prof_group = ".intval($fspec['spec_orig'])." ))";
                    }
                    $main_sql[] = $spec_sql;
                } 
            }

            if($main_sql) $main_where = self::setMainWhereSQL($main_sql, "AND \r\n");

            //�������
            if($filter['ffinance']) {
                $finance = $filter['ffinance'];
                // ����� �� �����
                if($finance['money']) {
                    $inner_sql['account'] = "INNER JOIN account a ON a.uid = f.uid";
                    $union_sql[] = $DB->parse("a.sum <= ?", $finance['money']);
                }

                // ��������� ��������
                if($finance['spend'][0]) {
                    $date = date('Y-m-d', strtotime($finance['spend'][0]));
                    $sql[] = $DB->parse("ao.op_date >= DATE ?", $date);
                }
                if($finance['spend'][1]) {
                    $date = date('Y-m-d', strtotime($finance['spend'][1]));
                    $sql[] = $DB->parse("ao.op_date <= DATE ?", $date);
                }
                if($sql) {
                    $sql[] = "ao.ammount <= 0";
                    $union_sql[] = self::setMainWhereSQL($sql, "AND");
                }

                // ��������� ����������
                if($finance['deposit'][0]) {
                    $date = date('Y-m-d', strtotime($finance['deposit'][0]));
                    $sql[] = "ao.op_date >= DATE '{$date}'";
                }
                if($finance['deposit'][1]) {
                    $date = date('Y-m-d', strtotime($finance['deposit'][1]));
                    $sql[] = "ao.op_date <= DATE '{$date}'";
                }
                if($sql) {
                    $sql[] = "ao.ammount > 0";
                    $union_sql[] = self::setMainWhereSQL($sql, "AND");
                }

                if($union_sql) $union_sql[] = self::setMainWhereSQL($union_sql); //"( " . implode(" OR ", $ffsql). " )";

                //������ ���������� �����
                if($finance['method_deposit'][0] == 1) {
                    $sql[] = "ao.payment_sys = 3";
                }
                if($finance['method_deposit'][1] == 1) {
                    $sql[] = "ao.payment_sys IN (1,2,10)";
                }
                if($finance['method_deposit'][2] == 1) {
                    $sql[] = "ao.payment_sys = 7";
                }
                if($finance['method_deposit'][3] == 1) {
                    $sql[] = "ao.payment_sys IN (4,5,6,11)";
                }
                if(sizeof($sql) != 4 && $sql) {
                    $union_sql[] = self::setMainWhereSQL($sql);
                }
                $sql = array();
                if($union_sql) $where = self::setMainWhereSQL($union_sql, "AND");
                if($where && $main_where) $where .= " AND ( $main_where )";
                else if($main_where) $where = " ( {$main_where} )";
                $union[] = "SELECT DISTINCT a.uid FROM 
                                account a
                            INNER JOIN freelancer f ON f.uid = a.uid 
                            LEFT JOIN rating r ON r.user_id = f.uid
                            LEFT JOIN spec_add_choise s on s.user_id = f.uid
                            LEFT JOIN spec_paid_choise sp on sp.user_id = f.uid AND sp.paid_to <= NOW()
                            INNER JOIN account_operations ao ON ao.billing_id = a.id
                            " . ( $where ? " WHERE {$where}" : '') . "
                            GROUP BY a.uid";
                unset($where);
            }

            // �������
            if($filter['fbuying']) {
                $buying = $filter['fbuying'];
                // ���������� �������
                if($buying['buying'][0] == 1) {
                    $sql[] = "ao.id IS NULL";
                }
                if($buying['buying'][1] == 1) {
                    $sql[] = "ao.id IS NOT NULL";
                }
                if(sizeof($sql) == 1) {
                    $inner_sql['account'] = "INNER JOIN account a ON a.uid = f.uid";
                    $inner_sql['acc_operations'] = "LEFT JOIN account_operations ao ON ao.billing_id = a .id";
                    $sql[] = self::setMainWhereSQL($sql);
                } else {
                    $sql = array();
                }
                // ��������� ����������
                if($buying['period'][0]) {
                    $date = date('Y-m-d', strtotime($buying['period'][0]));
                    $sql[] = "ao.op_date >= DATE '{$date}'";
                }
                if($buying['period'][1]) {
                    $date = date('Y-m-d', strtotime($buying['period'][1]));
                    $sql[] = "ao.op_date <= DATE '{$date}'";
                }
                if($sql) {
                    $sub_where = self::setMainWhereSQL($sql, "AND");
                }

                //������� 
                if($buying['buy']) {
                    foreach($buying['buy'] as $key=>$val) {
                        if($val['type_buy'] > 0) {
                            $sql[] = $DB->parse("op_code = ?", $val['type_buy']);
                        }
                        if($val['count_buy'][0] > 0) {
                            $sql[] = $DB->parse("cnt >= ?", $val['count_buy'][0]);
                        }
                        if($val['count_buy'][1] > 0) {
                            $sql[] = $DB->parse("cnt <= ?", $val['count_buy'][1]);
                        }
                        if($val['sum'][0] > 0) {
                            $sql[] = $DB->parse("ammount >= ?", $val['sum'][0]);
                        }
                        if($val['sum'][1] > 0) {
                            $sql[] = $DB->parse("ammount <= ?", $val['sum'][1]);
                        }
                        if($sql) $union_sql[] = self::setMainWhereSQL($sql, "AND");
                    }

                    if($union_sql) $where = self::setMainWhereSQL($union_sql);
                }
                if($sub_where && $main_where) $sub_where .= " AND ( $main_where )";
                else if($main_where) $sub_where = " ( {$main_where} )";
                
                $union[] = "SELECT DISTINCT uid FROM (
                                SELECT 
                                    COUNT(*) as cnt, 
                                    ABS(SUM(ammount)) as ammount, a.uid, op_code 
                                FROM 
                                    account a
                                INNER JOIN freelancer f ON f.uid = a.uid
                                LEFT JOIN rating r ON r.user_id = f.uid
                                LEFT JOIN spec_add_choise s on s.user_id = f.uid
                                LEFT JOIN spec_paid_choise sp on sp.user_id = f.uid AND sp.paid_to <= NOW()
                                INNER JOIN account_operations ao ON ao.billing_id = a.id
                                ". ($sub_where ? "WHERE ".$sub_where : "" )." 
                                GROUP BY a.uid, op_code) as tbl
                            ". ( $where ? " WHERE {$where}" : '');
                unset($where, $sub_where);
            }

            // �������
            if($filter['fproject']) {
                $fproject = $filter['fproject'];
                //�� ������
                if($fproject['period'][0]) {
                    $date = date('Y-m-d', strtotime($fproject['period'][0]));
                    $sql[] = "po.post_date >= DATE '{$date}'";
                }
                if($fproject['period'][1]) {
                    $date = date('Y-m-d', strtotime($fproject['period'][1]));
                    $sql[] = "po.post_date <= DATE '{$date}'";
                }
                if($sql) $union_sql[] = self::setMainWhereSQL($sql, "AND");//implode(" AND ", $fpperiod);

                //������������ �������
                if($fproject['type_project'][0] == 1) {
                    $sql[] = "p.budget_type = 3";
                }
                if($fproject['type_project'][1] == 1) {
                    $sql[] = "p.budget_type = 2";
                } 
                if($fproject['type_project'][2] == 1) {
                    $sql[] = "p.budget_type = 1";
                } 
                if(sizeof($sql) != 3 && $sql) $union_sql[] = self::setMainWhereSQL($sql);//implode(" OR ", $fptype);
                $sql = array();
                // ������� ������
                if($fproject['count'][0] > 0) {
                    $sql[] = $DB->parse(" cnt >= ?", $fproject['count'][0]);
                }
                if($fproject['count'][1] > 0) {
                    $sql[] = $DB->parse(" cnt <= ?", $fproject['count'][1]);
                }
                if($sql) $where = self::setMainWhereSQL($sql, "AND");//implode(" AND ", $s);
                if($union_sql) $sub_where =  self::setMainWhereSQL($union_sql, "AND");  
                if($sub_where && $main_where) $sub_where .= " AND ( $main_where )";
                else if($main_where) $sub_where = " ( {$main_where} )";
                $union[] = "SELECT DISTINCT user_id as uid FROM (
                                SELECT 
                                    COUNT(*) cnt, po.user_id 
                                FROM 
                                    projects_offers po
                                INNER JOIN freelancer f ON f.uid = po.user_id    
                                LEFT JOIN rating r ON r.user_id = f.uid
                                LEFT JOIN spec_add_choise s on s.user_id = f.uid
                                LEFT JOIN spec_paid_choise sp on sp.user_id = f.uid AND sp.paid_to <= NOW()
                                INNER JOIN projects p ON p.id = po.project_id
                                ". ($sub_where ? "WHERE ".$sub_where : "" )." 
                                GROUP BY po.user_id ) tbl 
                            ". ( $where ? " WHERE {$where}" : '');
                unset($where, $sub_where);
            }

            // ���������� � ������
            if($filter['fblog']) {
                $fblog = $filter['fblog'];

                if($fblog['period'][0]) {
                    $date = date('Y-m-d', strtotime($fblog['period'][0]));
                    $sql[] = " post_time >= DATE '{$date}'";
                }
                if($fblog['period'][1]) {
                    $date = date('Y-m-d', strtotime($fblog['period'][1]));
                    $sql[] = " post_time <= DATE '{$date}'";
                }
                if($sql) {
                    $sub_where = self::setMainWhereSQL($sql, "AND");//implode(" AND ", $fblog_period);
                }

                if(intval($fblog['post'][0]) >= 0) {
                    $sql[] = " cnt >= ".intval($fblog['post'][0]);
                }
                if(intval($fblog['post'][1]) >= 0) {
                    $sql[] = " cnt <= ".intval($fblog['post'][1]);
                }
                if($sql) $where = self::setMainWhereSQL($sql, "AND");//implode(" AND ", $fblog_count);
                if($sub_where && $main_where) $sub_where .= " AND ( $main_where )";
                else if($main_where) $sub_where = " ( {$main_where} )";
                $union[] = "SELECT DISTINCT uid FROM (
                                SELECT 
                                    COUNT(*) cnt, 
                                    fromuser_id as uid
                                FROM 
                                    blogs_msgs
                                INNER JOIN freelancer f ON f.uid = blogs_msgs.fromuser_id
                                LEFT JOIN rating r ON r.user_id = f.uid
                                LEFT JOIN spec_add_choise s on s.user_id = f.uid
                                LEFT JOIN spec_paid_choise sp on sp.user_id = f.uid AND sp.paid_to <= NOW()
                                ". ($sub_where ? "WHERE ".$sub_where : "" )."
                                GROUP BY fromuser_id) as t
                            " . ( $where ? " WHERE {$where}" : '');
                unset($where, $sub_where);
            }
            if($main_where) {
                $union[]= "SELECT 
                            DISTINCT ON (f.uid) f.uid 
                        FROM  freelancer f
                        LEFT JOIN rating r ON r.user_id = f.uid 
                        LEFT JOIN spec_add_choise s on s.user_id = f.uid 
                        LEFT JOIN spec_paid_choise sp on sp.user_id = f.uid AND sp.paid_to <= NOW() 
                        " . ($main_where ? "WHERE {$main_where}" : "" );
            }
            if($union) {
                return implode( "\r\nINTERSECT\r\n", $union);
            } else {
                $sql = "SELECT uid FROM freelancer f WHERE f.active = true AND substring(f.subscr from 8 for 1)::integer = 1 AND f.is_banned = B'0'";
                return $sql;
            }
        } else {
            $sql = "SELECT uid FROM freelancer f WHERE f.active = true AND substring(f.subscr from 8 for 1)::integer = 1 AND f.is_banned = B'0'";
            return $sql;
        }
    }
    
    public function getSQLFilterEmployer($filter) {
        global $DB;
        // �������
        if(isset($filter['filter_emp'])) {
            $main_sql[] = "e.active = true AND substring(e.subscr from 8 for 1)::integer = 1 AND e.is_banned = B'0'";
            if($filter['etype_account'][0] == 1) {
                $sql[] = "e.is_pro = true";
            }
            if($filter['etype_account'][1] == 1) {
                $sql[] = "e.is_pro = false";
            }
            if(sizeof($sql) == 1) $main_sql[] = self::setMainWhereSQL($sql);
            $sql = array();
            // �������
            if($filter['etype_profile'][0] == 1) {
                $sql[] = "r.o_inf_factor > 0";
            }
            if($filter['etype_profile'][1] == 1) {
                $sql[] = "r.o_inf_factor = 0";
            }
            if(sizeof($sql) == 1) {
                $inner_sql['rating'] = "LEFT JOIN rating r ON r.user_id = f.uid";
                $main_sql[] = self::setMainWhereSQL($sql);
            }
            $sql = array();

            // ���������������
            if($filter['efrom_regdate']) {
                $date = date('Y-m-d', strtotime($filter['efrom_regdate']));
                $sql[] = "e.reg_date >= DATE '{$date}'";
            }
            if($filter['eto_regdate']) {
                $date = date('Y-m-d', strtotime($filter['eto_regdate']));
                $sql[] = "e.reg_date <= DATE '{$date}'";
            }
            if($sql) $main_sql[] = self::setMainWhereSQL($sql, "AND"); //" ( ".implode(" AND ", $freg)." )\r\n";

            // ��������� �����
            if($filter['efrom_lastvisit']) {
                $date = date('Y-m-d', strtotime($filter['efrom_lastvisit']));
                $sql[] = "e.last_time >= DATE '{$date}'";
            }
            if($filter['eto_lastvisit']) {
                $date = date('Y-m-d', strtotime($filter['eto_lastvisit']));
                $sql[] = "e.last_time <= DATE '{$date}'";
            }
            if($sql) $main_sql[] = self::setMainWhereSQL($sql, "AND");

            //���
            if($filter['etype_sex'][0] == 1) {
                $sql[] = "e.sex = true";
            }
            if($filter['etype_sex'][1] == 1) {
                $sql[] = "e.sex = false";
            }
            if(sizeof($sql) == 1) $main_sql[] = self::setMainWhereSQL($sql);
            $sql = array();
            if($main_sql) $main_where = self::setMainWhereSQL($main_sql, "AND \r\n");

            //�������
            if($filter['efinance']) {
                $finance = $filter['efinance'];
                // ����� �� �����
                if($finance['money']) {
                    $inner_sql['account'] = "INNER JOIN account a ON a.uid = f.uid";
                    $union_sql[] = $DB->parse("a.sum <= ?", $finance['money']);
                }

                // ��������� ��������
                if($finance['spend'][0]) {
                    $date = date('Y-m-d', strtotime($finance['spend'][0]));
                    $sql[] = "ao.op_date >= DATE '{$date}'";
                }
                if($finance['spend'][1]) {
                    $date = date('Y-m-d', strtotime($finance['spend'][1]));
                    $sql[] = "ao.op_date <= DATE '{$date}'";
                }
                if($sql) {
                    $sql[] = "ao.ammount <= 0";
                    $union_sql[] = self::setMainWhereSQL($sql, "AND");
                }

                // ��������� ����������
                if($finance['deposit'][0]) {
                    $date = date('Y-m-d', strtotime($finance['deposit'][0]));
                    $sql[] = "ao.op_date >= DATE '{$date}'";
                }
                if($finance['deposit'][1]) {
                    $date = date('Y-m-d', strtotime($finance['deposit'][1]));
                    $sql[] = "ao.op_date <= DATE '{$date}'";
                }
                if($sql) {
                    $sql[] = "ao.ammount > 0";
                    $union_sql[] = self::setMainWhereSQL($sql, "AND");
                }

                if($union_sql) $union_sql[] = self::setMainWhereSQL($union_sql); //"( " . implode(" OR ", $ffsql). " )";

                //������ ���������� �����
                if($finance['method_deposit'][0] == 1) {
                    $sql[] = "ao.payment_sys = 3";
                }
                if($finance['method_deposit'][1] == 1) {
                    $sql[] = "ao.payment_sys IN (1,2,10)";
                }
                if($finance['method_deposit'][2] == 1) {
                    $sql[] = "ao.payment_sys = 7";
                }
                if($finance['method_deposit'][3] == 1) {
                    $sql[] = "ao.payment_sys IN (4,5,6,11)";
                }
                if(sizeof($sql) != 4 && $sql) {
                    $union_sql[] = self::setMainWhereSQL($sql);
                }
                $sql = array();
                if($union_sql) $where = self::setMainWhereSQL($union_sql, "AND");
                if($where && $main_where) $where .= " AND ( $main_where )";
                else if($main_where) $where = " ( {$main_where} )";
                $union[] = "SELECT DISTINCT a.uid FROM 
                                account a
                            INNER JOIN employer e ON e.uid = a.uid 
                            LEFT JOIN rating r ON r.user_id = e.uid
                            INNER JOIN account_operations ao ON ao.billing_id = a.id
                            " . ( $where ? " WHERE {$where}" : '') . "
                            GROUP BY a.uid";
                unset($where);
            }

            // �������
            if($filter['ebuying']) {
                $buying = $filter['ebuying'];
                // ���������� �������
                if($buying['buying'][0] == 1) {
                    $sql[] = "ao.id IS NULL";
                }
                if($buying['buying'][1] == 1) {
                    $sql[] = "ao.id IS NOT NULL";
                }
                if(sizeof($sql) == 1) {
                    $inner_sql['account'] = "INNER JOIN account a ON a.uid = f.uid";
                    $inner_sql['acc_operations'] = "LEFT JOIN account_operations ao ON ao.billing_id = a .id";
                    $sql[] = self::setMainWhereSQL($sql);
                } else {
                    $sql = array();
                }

                // ��������� ����������
                if($buying['period'][0]) {
                    $date = date('Y-m-d', strtotime($buying['period'][0]));
                    $sql[] = "ao.op_date >= DATE '{$date}'";
                }
                if($buying['period'][1]) {
                    $date = date('Y-m-d', strtotime($buying['period'][1]));
                    $sql[] = "ao.op_date <= DATE '{$date}'";
                }
                if($sql) {
                    $sub_where = self::setMainWhereSQL($sql, "AND");
                }

                //������� 
                if($buying['buy']) {
                    foreach($buying['buy'] as $key=>$val) {
                        if($val['type_buy'] > 0) {
                            $sql[] = $DB->parse("op_code = ?", $val['type_buy']);
                        }
                        if($val['count_buy'][0] > 0) {
                            $sql[] = $DB->parse("cnt >= ?", $val['count_buy'][0]);
                        }
                        if($val['count_buy'][1] > 0) {
                            $sql[] = $DB->parse("cnt <= ?", $val['count_buy'][1]);
                        }
                        if($val['sum'][0] > 0) {
                            $sql[] = $DB->parse("ammount >= ?", $val['sum'][0]);
                        }
                        if($val['sum'][1] > 0) {
                            $sql[] = $DB->parse("ammount <= ?", $val['sum'][1]);
                        }
                        if($sql) $union_sql[] = self::setMainWhereSQL($sql, "AND");
                    }

                    if($union_sql) $where = self::setMainWhereSQL($union_sql);
                }
                if($sub_where && $main_where) $sub_where .= " AND ( $main_where )";
                else if($main_where) $sub_where = " ( {$main_where} )";
                $union[] = "SELECT DISTINCT uid FROM (
                                SELECT 
                                    COUNT(*) as cnt, 
                                    ABS(SUM(ammount)) as ammount, a.uid, op_code 
                                FROM 
                                    account a
                                INNER JOIN employer e ON e.uid = a.uid
                                LEFT JOIN rating r ON r.user_id = e.uid
                                INNER JOIN account_operations ao ON ao.billing_id = a.id
                                ". ($sub_where ? "WHERE ".$sub_where : "" )." 
                                GROUP BY a.uid, op_code) as tbl
                            ".( $where ? " WHERE {$where}" : '');
                unset($where, $sub_where);
            }

            // �������
            if($filter['eproject']) {
                $eproject = $filter['eproject'];

                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
                $project_exRates = project_exrates::GetAll();

                // ��������� ����������
                if($eproject['period'][0]) {
                    $date = date('Y-m-d', strtotime($eproject['period'][0]));
                    $sql[] = "p.post_date >= DATE '{$date}'";
                }
                if($eproject['period'][1]) {
                    $date = date('Y-m-d', strtotime($eproject['period'][1]));
                    $sql[] = "p.post_date <= DATE '{$date}'";
                }
                if($sql) $subunion_sql[] = self::setMainWhereSQL($sql, "AND");

                // ������ ��������
                if($eproject['created'][0]) {
                    $sql[] = $DB->parse("all_cnt >= ?", $eproject['created'][0]);
                }
                if($eproject['created'][1]) {
                    $sql[] = $DB->parse("all_cnt <= ?", $eproject['created'][1]);
                }
                if($sql) $union_sql[] = self::setMainWhereSQL($sql, "AND");

                // ���-����
                if($eproject['freelance'][0]) {
                    $sql[] = $DB->parse("frl_cnt >= ?", $eproject['freelance'][0]);
                }
                if($eproject['freelance'][1]) {
                    $sql[] = $DB->parse("frl_cnt <= ?", $eproject['freelance'][1]);
                }
                if($sql) $union_sql[] = self::setMainWhereSQL($sql, "AND");

                // ������ ��� ���
                if($eproject['only_pro'][0]) {
                    $sql[] = $DB->parse("pro_cnt >= ?", $eproject['only_pro'][0]);
                }
                if($eproject['only_pro'][1]) {
                    $sql[] = $DB->parse("pro_cnt <= ?", $eproject['only_pro'][1]);
                }

                // � �����
                if($eproject['in_office'][0]) {
                    $sql[] = $DB->parse("office_cnt >= ?", $eproject['in_office'][0]);
                }
                if($eproject['in_office'][1]) {
                    $sql[] = $DB->parse("office_cnt <= ?", $eproject['in_office'][1]);
                }
                if($sql) $union_sql[] = self::setMainWhereSQL($sql, "AND");

                // ��������
                if($eproject['konkurs'][0]) {
                    $sql[] = $DB->parse("konk_cnt >= ?", $eproject['konkurs'][0]);
                }
                if($eproject['konkurs'][1]) {
                    $sql[] = $DB->parse("konk_cnt <= ?", $eproject['konkurs'][1]);
                }
                if($sql) $union_sql[] = self::setMainWhereSQL($sql, "AND");

                // ������ ������� �������
                $cr  = 2;
                $cex = array(2,3,4,1); 
                if(($cost_from = (float)$eproject['budget'][0]) < 0) $cost_from = 0;
                if(($cost_to = (float)$eproject['budget'][1]) < 0)     $cost_to = 0;
                if($cost_to < $cost_from && $cost_to != 0)       $cost_to = $cost_from;
                if($cost_to || $cost_from) {

                    for($i=0;$i<4;$i++) {
                        $exfr  = round($cost_from * $project_exRates[$cex[$cr].$cex[$i]],4);
                        $exto  = round($cost_to * $project_exRates[$cex[$cr].$cex[$i]],4);
                        $sql[] = $DB->parse("(currency = ? AND cost >= ?", $i, $exfr).($cost_to ? $DB->parse(" AND cost <= ?", $exto) : '').')';
                    }
                }
                if($sql) $subunion_sql[] = self::setMainWhereSQL($sql, "OR");

                // ���. ������� ���� ��������
                if($eproject['sum_budget'][0]) {
                    $sql[] = $DB->parse("sum_budget >= ?", $eproject['sum_budget'][0]);
                }
                if($eproject['sum_budget'][1]) {
                    $sql[] = $DB->parse("sum_budget <= ?", $eproject['sum_budget'][1]);
                }
                if($sql) $union_sql[] = self::setMainWhereSQL($sql, "AND");

                // ������ �� �������
                if($eproject['avg_answer'][0]) {
                    $sql[] = $DB->parse("answers >= ?", $eproject['avg_answer'][0]);
                }
                if($eproject['avg_answer'][1]) {
                    $sql[] = $DB->parse("answers <= ?", $eproject['avg_answer'][1]);
                }
                if($sql) $union_sql[] = self::setMainWhereSQL($sql, "AND");

                // ������ �� �������
                if($eproject['executor'][0] == 1) {
                    $sql[] = "budget_type = 3";
                }
                if($eproject['executor'][1] == 1) {
                    $sql[] = "budget_type = 2";
                }
                if($eproject['executor'][2] == 1) {
                    $sql[] = "budget_type = 1";
                }
                if($sql) $subunion_sql[] = self::setMainWhereSQL($sql);

                $sub_where = self::setMainWhereSQL($subunion_sql, "AND");
                
                if($sub_where && $main_where) $sub_where .= " AND ( $main_where )";
                else if($main_where) $sub_where = " ( {$main_where} )";
                $where = self::setMainWhereSQL($union_sql, "AND");
                $union[] = "SELECT user_id as uid FROM (
                                SELECT 
                                    SUM(1) as all_cnt,
                                    SUM(CASE WHEN kind = 7 OR kind = 2 THEN 1 ELSE 0 END) as konk_cnt,
                                    SUM(CASE WHEN kind = 1 THEN 1 ELSE 0 END) as frl_cnt,
                                    SUM(CASE WHEN pro_only = true THEN 1 ELSE 0 END) as pro_cnt,
                                    SUM(CASE WHEN kind = 4 THEN 1 ELSE 0 END) as office_cnt,
                                    SUM(CASE WHEN po.id IS NOT NULL THEN 1 ELSE 0 END) as answers,
                                    SUM(
                                        CASE WHEN currency = 0 THEN cost * {$project_exRates[24]}
                                        WHEN currency  = 1 THEN cost * {$project_exRates[34]}
                                        WHEN currency = 3 THEN cost * {$project_exRates[14]}
                                        ELSE cost END
                                    ) as sum_budget,
                                    p.user_id
                                FROM projects p
                                INNER JOIN employer e ON e.uid = p.user_id
                                LEFT JOIN rating r ON r.user_id = e.uid
                                LEFT JOIN projects_offers po ON po.project_id = p.id
                                ". ($sub_where ? "WHERE ".$sub_where : "" )."   
                                GROUP BY p.user_id
                            ) t ". ($where ? "WHERE {$where}" : "" );
                unset($where, $sub_where);
            }

            if($filter['emassend']) {
                $emassend = $filter['emassend'];

                if($emassend['spec'] > 0) {
                    $union_inner = "LEFT JOIN mass_sending_profs msp ON msp.mass_sending_id = ms.id";
                    $union_sql[] = $DB->parse("msp.prof_id = ?", $emassend['spec']);
                }

                if($emassend['recipients'][0] > 0) {
                    $sql[] = $DB->parse("ms.all_count >= ?", $emassend['recipient'][0]);
                }
                if($emassend['recipients'][1] > 0) {
                    $sql[] = $DB->parse("ms.all_count <= ?", $emassend['recipient'][1]);
                }
                if($sql) $union_sql[] = self::setMainWhereSQL($sql, "AND");

                $where = self::setMainWhereSQL($union_sql, "AND");
                if($where && $main_where) $where .= " AND ( $main_where )";
                else if($main_where) $where = " ( {$main_where} )";
                if($where) {
                    $union[] = "SELECT 
                                    ms.user_id as uid
                                FROM 
                                mass_sending ms
                                INNER JOIN employer e ON e.uid = ms.user_id
                                LEFT JOIN rating r ON r.user_id = e.uid
                                " . ($union_inner?$union_inner:"") . " 
                                " . ($where ? "WHERE {$where}" : "" );
                    unset($where, $union_inner);
                }
            }
            
            if($main_where) {
                $union[]= "SELECT 
                            DISTINCT ON (e.uid) e.uid 
                        FROM  employer e
                        LEFT JOIN rating r ON r.user_id = e.uid
                        ". ($main_where ? "WHERE {$main_where}" : "" );
            }
            
            if($union) {
                return implode( "\r\nINTERSECT\r\n", $union);
            } else {
                return "SELECT uid FROM employer e WHERE e.active = true AND substring(e.subscr from 8 for 1)::integer = 1 AND e.is_banned = B'0'";
            }
        } else {
            $sql = "SELECT uid FROM employer e WHERE e.active = true AND substring(e.subscr from 8 for 1)::integer = 1 AND e.is_banned = B'0'";
            return $sql;
        }
    }
    
    public function getCountRecipients($type, $filter) {
        global $DB;
        if(!is_array($type)) return 0;
        $cnt = array();
        if(in_array("emp", $type)) {
            $sql  = "SELECT COUNT(*) cnt FROM (";
            $sql .= self::getSQLFilterEmployer($filter);
            $sql .= ") tbl";
            $cnt[] = $DB->val($sql);
        } 
        
        if(in_array("frl", $type)) {
            $sql  = "SELECT COUNT(*) cnt FROM (";
            $sql .= self::getSQLFilterFreelancer($filter);
            $sql .= ") tbl";
            $cnt[] = $DB->val($sql);
        }
        
        return $cnt;
    }
    
    /**
     * ������� ��������� ��������
     * 
     * @global type $DB 
     */
    public function digestSend($id = null) {
        global $DB;
        
        $this->log = new log('massend/digest-'.SERVER.'-%d.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        
        if($id !== null) {
            $result = $DB->rows("SELECT * FROM mailer_messages WHERE id = ?i AND status_sending NOT IN(2,3) AND status_message = 0 AND in_draft = false", $id);
        } else {
            $sql = "SELECT mm.* FROM mailer_messages mm
                    INNER JOIN mailer_digest md ON md.id_mailer = mm.id 
                    WHERE 
                    in_draft = false 
                    AND status_sending NOT IN(2,3) 
                    AND status_message = 0 
                    AND date_sending <= NOW()
                    ORDER BY (current_date + date_trunc('minute', date_sending)::time) ASC";
            
            $result = $DB->rows($sql);
        }
        
        if(!$result) {
            $this->log->writeln("Digest not found, stop sending [{$id}]");
            return false;
        }
        
        $ids    = array_map(create_function('$a', 'return $a["id"];'), $result);
        $this->log->writeln("Sending digest - (".count($ids).")");
        $this->update(array("status_sending" => 2), $ids); 
        
        foreach($result as $k => $message) {
            $this->log->writeln("------------------------------------------------");
            $this->log->writeln("Initialization sending to email {$message['id']}");
            
            // ������������ �������, ������� ������ ��� �������
            if($message['filter_frl'] !== null && $message['filter_emp'] !== null) {
                $sql = "SELECT uid FROM users WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0'";
            } elseif($message['filter_frl'] !== null) {
                $sql = "SELECT uid FROM freelancer WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0'";
            } elseif($message['filter_emp'] !== null) {
                $sql = "SELECT uid FROM employer WHERE substring(subscr from 8 for 1)::integer = 1 AND is_banned = B'0'";
            } else {
                $this->log->writeln("Recipients not selected");
                continue;
            }
            
            $mail = new smtp();
            $mail->subject = $message['subject'];
            $mail->message = $message['message'];
            $mail->recipient = '';
            $spamid = $mail->send('text/html');
            if(!$spamid) {
                $this->log->writeln("Error create spamid");
                continue;
            }
            $mail->recipient = array();
            
            $cnt_emp = 0;
            $cnt_frl = 0;
            $i       = 0;
            while ( $users = $DB->col($sql . " LIMIT ".self::MAX_SEND_USERS." OFFSET ?", $i) ) {
                $user  = $DB->rows("SELECT uid, login, uname, usurname, role, email FROM users WHERE uid IN (?l)", $users);
                $emp   = array_filter($user, create_function('$a', 'return (substr($a["role"], 0, 1) == 1);'));
                $frl   = array_filter($user, create_function('$a', 'return !(substr($a["role"], 0, 1) == 1);'));
                $cnt_emp += count($emp);
                $cnt_frl += count($frl);
                
                $mail->recipient = array_map(array("mailer", "array2send"), $user);
                $mail->bind($spamid);
                $mail->recipient = array();
                
                $i = $i + self::MAX_SEND_USERS;
            }
            
            $this->log->writeln("Complete binding users (employer = {$cnt_emp}, freelancer = {$cnt_frl})");
            
            $this->update(array("status_sending" => 1, 
                                "status_message" => 1, 
                                "real_date_sending" => "NOW()",
                                "spamid"            => (int) $spamid,
                                "count_rec_frl"     => (int) $cnt_frl,
                                "count_rec_emp"     => (int) $cnt_emp), $message['id']);
            
            unset($mail, $spamid);
        }
        $this->log->writeln("Complete sending digest");
    }
    
    /**
     * ������� ��������� ���������
     * 
     * @global type $DB 
     */
    public function getMailerSend() {
        global $DB;
        $PLDB = new DB('plproxy');
        // @see type_send_regular == self::$TYPE_REGULAR;
        $sql = "SELECT 
                    mm.*, 
                
                    mfe.type_account as etype_account, mfe.type_profile as etype_profile, mfe.from_regdate as efrom_regdate, mfe.to_regdate as eto_regdate, 
                    mfe.from_lastvisit as efrom_lastvisit, mfe.to_lastvisit as eto_lastvisit, mfe.type_sex as etype_sex, 
                    mfe.finance as {$this->subfilter[0]}, mfe.buying as {$this->subfilter[1]}, mfe.project as {$this->subfilter[2]}, mfe.massend as {$this->subfilter[3]},

                    mff.type_account as ftype_account, mff.type_profile as ftype_profile, mff.type_portfolio as ftype_portfolio, mff.from_regdate as ffrom_regdate, 
                    mff.to_regdate as fto_regdate, mff.from_lastvisit as ffrom_lastvisit, mff.to_lastvisit as fto_lastvisit, mff.type_sex as ftype_sex, 
                    mff.finance as {$this->subfilter[4]}, mff.buying as {$this->subfilter[5]}, mff.project as {$this->subfilter[6]}, 
                    mff.specs as {$this->subfilter[7]}, mff.blogs as {$this->subfilter[8]}, mff.geo as {$this->subfilter[9]},
                    mff.regdate_interval as fregdate_interval    

                FROM mailer_messages mm
                LEFT JOIN mailer_filter_employer as mfe ON mfe.id = mm.filter_emp
                LEFT JOIN mailer_filter_freelancer as mff ON mff.id = mm.filter_frl    
                WHERE 
                in_draft = false AND status_sending NOT IN(2,3) AND status_message = 0 AND
                ( 
                    ( type_send_regular = 1 AND date_sending <= NOW() ) 
                        OR
                    ( type_send_regular = 2 AND 
                      current_date + date_trunc('minute', date_sending)::time  <= date_trunc('minute', NOW()) AND 
                      type_send_regular = extract(ISODOW from NOW()) )
                        OR
                    ( type_send_regular = 3 AND 
                      extract('day' from date_sending) = extract('day' from NOW()) AND 
                      current_date + date_trunc('minute', date_sending)::time  <= date_trunc('minute', NOW()) )
                        OR
                    ( type_send_regular = 4 AND 
                      extract('month' from date_sending) = extract('month' from NOW()) AND 
                      extract('day' from date_sending) = extract('day' from NOW()) AND 
                      current_date + date_trunc('minute', date_sending)::time  <= date_trunc('minute', NOW()) )
                )
                ORDER BY (current_date + date_trunc('minute', date_sending)::time) ASC";
        
        $result = $DB->rows($sql);
        if(!$result) return false;
        $ids    = array_map(create_function('$a', 'return $a["id"];'), $result);
        $this->log = new log('massend/massend-'.SERVER.'-%d.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        
        // ��������� ��� �������� � ������ "���� ��������"
        $this->log->writeln("Sending messages - (".count($ids).")");
        $this->update(array("status_sending" => 2), $ids);
        foreach($result as $k => $message) {
            $this->log->writeln("Start sending message ID = {$message['id']}");
            foreach($this->subfilter as $val) {
                if($message[$val]) $message[$val] = unserialize($message[$val]);
            }
            
            // ������������ ��������
            $attached = array();
            if($message['is_attached']) {
                $attached = $this->getAttach($message['id']);
                $attached = array_map(create_function('$a', 'return $a["fid"];'), $attached);
            }
            
            if (!$message['filter_file']) { //��� �����: ������������ �� ��������
				$sql_emp_recipient = $this->getSQLFilterEmployer($message);
				$sql_frl_recipient = $this->getSQLFilterFreelancer($message);

				// ������������ �������, ������� ������ ��� �������
				if($message['filter_frl'] == 0 && $message['filter_emp'] == 0) {
					$sql = $sql_emp_recipient ." UNION ".$sql_frl_recipient;
				} else if($message['filter_frl'] > 0 && $message['filter_emp'] > 0) {
					$sql = $sql_emp_recipient ." UNION ".$sql_frl_recipient;
				} else if($message['filter_frl'] > 0) {
					$sql = $sql_frl_recipient;
				} else if($message['filter_emp'] > 0) {
					$sql = $sql_emp_recipient;
				}
			}
            
            // �������� ������ ����������
            if($message['type_sending'][0] == 1) {
                $this->log->write("Initialization sending to personal messages\n");
                $msg_attached = "'{}'";
                if(count($attached) > 0) {
                    $msg_attached = "'{".implode(", ", $attached)."}'";
                }
                $msgid = $PLDB->val("SELECT masssend({$message['user_id']}, '{$message['message']}', $msg_attached, '')");
            } 
            // �������� �� �����
            if($message['type_sending'][1] == 1) {
                $this->log->write("Initialization sending to email\n");
                $mail = new smtp();
                $mail->prepare = true;
                $mail->subject = $message['subject'];
                $mail->message = $this->getMailContent($message['message']);
                $mail->recipient = '';
                $spamid = $mail->send('text/html', $attached);
                $mail->recipient = array();
            }
            // ���� ������ �� ������� ���������� ������ ���������
            if($message['type_sending'][0] == 0 && $message['type_sending'][1] == 0) {
                $this->log->write("Initialization sending to personal messages\n");
                $msg_attached = "'{}'";
                if(count($attached) > 0) {
                    $msg_attached = "'{".implode(", ", $attached)."}'";
                }
                $msgid = $PLDB->val("SELECT masssend({$message['user_id']}, '{$message['message']}', $msg_attached, '')");
                // ------------------------------ //
                $this->log->write("Initialization sending to email\n");
                $mail = new smtp();
                $mail->prepare = true;
                $mail->subject = $message['subject'];
                $mail->message = $this->getMailContent($message['message']);
                $mail->recipient = '';
                $spamid = $mail->send('text/html', $attached);
                $mail->recipient = array();
            }
            
            $i = 0;
            $cnt_emp = 0;
			$cnt_frl = 0;
			
			if ($message['filter_file']) {
				while ( $user = $this->parseExtraList($message['filter_file'], $i) ) {
					$cnt_frl += count($user);
					$users = array();
                    foreach($user as $u) {
                        $users[] = (int)$u['uid'];
                    }

                    if($msgid) {
						$PLDB->query("SELECT masssend_bind(?, ?, ?a)", $msgid, $message['user_id'], $users);
					}

					if($spamid) {
						$mail->recipient = array_map(array("mailer", "array2send"), $user);
						$mail->bind($spamid);
						$mail->recipient = array();
					}
                    
					$i = $i + self::MAX_SEND_USERS;
				}
				$this->log->write("Complite binding users (parsed users = {$cnt_frl})\n");
			} else {
				
				while ( $users = $DB->col($sql . " LIMIT ".self::MAX_SEND_USERS." OFFSET ?", $i) ) {
					$user  = $DB->rows("SELECT uid, login, uname, usurname, role, email FROM users WHERE uid IN (?l)", $users);
					$emp   = array_filter($user, create_function('$a', 'return (substr($a["role"], 0, 1) == 1);'));
					$frl   = array_filter($user, create_function('$a', 'return !(substr($a["role"], 0, 1) == 1);'));
					$cnt_emp += count($emp);
					$cnt_frl += count($frl);

					if($msgid) {
						$PLDB->query("SELECT masssend_bind(?, ?, ?a)", $msgid, $message['user_id'], $users);
					}

					if($spamid) {
						$mail->recipient = array_map(array("mailer", "array2send"), $user);
						$mail->bind($spamid);
						$mail->recipient = array();
					}

					$i = $i + self::MAX_SEND_USERS;
				}
				$this->log->write("Complite binding users (employer = {$cnt_emp}, freelancer = {$cnt_frl})\n");
			}
			
            $PLDB->query("SELECT masssend_commit(?, ?)", $msgid, $message['user_id']); 
            
            // ��������� ������ ��������
            $this->update(array("status_sending" => 1, 
                                "status_message" => 1, 
                                "real_date_sending" => "NOW()",
                                "msgid"             => (int) $msgid,
                                "spamid"            => (int) $spamid,
                                "count_rec_frl"     => (int) $cnt_frl,
                                "count_rec_emp"     => (int) $cnt_emp), $message['id']);
            if($message['type_regular'] > 1) {
                $this->updateRegularDate($message['type_regular'], $message['id']);
            }
            unset($mail, $msgid, $spamid);
            $this->log->write("\n-----------------------------------------------------------------------\n");
        }
        $this->log->write("Complite sending messages\n");
    }
    
    /**
     * �������� ������������� �� ������, �������� ������ � ���������� ���, ��������������� �������
     * 
     * @param array $a   ���������� �������
     * @return type 
     */
    public function array2send($a) {
        $ulink = "{$GLOBALS['host']}/users/{$a['login']}";
        return array(
            'email' => $a['email'], 
            'extra' => array(
                'USER_NAME'      => $a['uname'], 
                'USER_SURNAME'   => $a['usurname'],
                'USER_LOGIN'     => $a['login'],
                'URL_PORTFOLIO'  => '<a href="' . "{$ulink}/portfolio/" . '" target="_blank">' . "{$ulink}/portfolio/" . '</a>' ,
                'URL_LK'         => '<a href="' . "{$ulink}/" . '" target="_blank">' . "{$ulink}/" . '</a>',
                'URL_BILL'       => '<a href="' . "{$ulink}/bill/" . '" target="_blank">' . "{$ulink}/bill/" . '</a>', 
                'UNSUBSCRIBE_KEY'       => users::GetUnsubscribeKey($a['login']) 
            )
        );
    }
    
    /**
     * ������� ������� ��������� ��������
     * 
     * @global type $DB
     * @param integer $mailer_id   �� ��������
     */
    public function sendForMe($mailer_id) {
        $message = $this->getMailerById($mailer_id);
        
        // ������������ ��������
        $attached = array();
        if($message['is_attached']) {
            $attached = $this->getAttach($message['id']);
            $attached = array_map(create_function('$a', 'return $a["fid"];'), $attached);
        }
        
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/smtp.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php';
        
        $user = new users();
        $user->GetUserByUID($message['user_id']);
        $user = get_object_vars($user);
        
        if($message['type_sending'][0] == 1) {
            $PLDB = new DB('plproxy');
            $adm  = new users();
            $adm_id = $adm->GetUid($e, "admin");
            $PLDB->val("SELECT messages_add(?i, ?i, ?, ?b, ?a, ?b)", $adm_id, $user['uid'], $message['message'], true, $attached, true);
        }
        
        if($message['type_sending'][1] == 1) {
            $mail = new smtp();
            $mail->prepare     = true;
            $mail->subject     = $message['subject']; 
            $mail->message     = $this->getMailContent($message['message']); 
            $mail->recipient[] = $this->array2send($user);
            $mail->send('text/html', $attached); 
        }
        
        if($message['type_sending'][0] == 0 && $message['type_sending'][1] == 0) {
            $PLDB = new DB('plproxy');
            $adm  = new users();
            $adm_id = $adm->GetUid($e, "admin");
            $PLDB->val("SELECT messages_add(?i, ?i, ?, ?b, ?a, ?b)", $adm_id, $user['uid'], $message['message'], true, $attached, true);
            // ------------------------ //
            $mail = new smtp();
            $mail->prepare     = true;
            $mail->subject     = $message['subject']; 
            $mail->message     = $this->getMailContent($message['message']);
            $mail->recipient[] = $this->array2send($user);
            $mail->send('text/html', $attached); 
        }
    }
    
    public function getMailContent($message)
    {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/template.php";
        $path = $_SERVER['DOCUMENT_ROOT'].'/templates/mail/mailer.tpl.php';

        $utm = smtp::_addUtmUrlParams('email', '', 'unsubscribe_news', '&');
        
        return Template::render($path, array(
            'message' => $message,
            'utm' => $utm
        ));
    }
    
    /**
     * ���������� ������� ��������
     * 
     * @global type $DB 
     * @param booleab $force - ����� �������� �������
     */
    public function updateStatusSending($force = false) {
        global $DB;
        $PLDB = new DB('plproxy');
        
        $sql = "SELECT * FROM mailer_messages WHERE status_sending = 2;";
        $mailer = $DB->rows($sql);
        
        foreach($mailer as $message) {
            if($message['spamid'] > 0) {
                $sql = "SELECT varvalue FROM mail.vars WHERE varname = 'mailid:2'";
                $varvalue = $PLDB->val($sql); //
                $sql = "SELECT COUNT(*) FROM mail.recipients_{$message['spamid']} WHERE mailid > {$varvalue}";
                $spam_send = ($PLDB->val($sql) == 0 ? 1 : 0);
            }
            if($message['msgid'] > 0) {
                $sql = "SELECT 1 FROM messages_zeros_userdata({$message['user_id']}, {$message['msgid']}) LIMIT 1;";
                $msg_send = $PLDB->val($sql);
            }
            
            if($message['spamid'] > 0 && $message['msgid'] > 0 && $spam_send == 1 && $msg_send == 1) {
                $this->update(array("status_sending" => ( $message['type_regular'] <= 1 ? 1 : 0 ), 
                                    "status_message" => 1), $message['id']);
            } else if($message['spamid'] > 0 && $spam_send == 1) {
                $this->update(array("status_sending" => ( $message['type_regular'] <= 1 ? 1 : 0 ), 
                                    "status_message" => 1), $message['id']);
            } else if($message['msgid'] > 0 && $msg_send == 1) {
                $this->update(array("status_sending" => ( $message['type_regular'] <= 1 ? 1 : 0 ), 
                                    "status_message" => 1), $message['id']);
            } elseif($force) {
                $this->update(array("status_sending" => ( $message['type_regular'] <= 1 ? 1 : 0 ), 
                                    "status_message" => 1), $message['id']);
            }
        }
    }
    
    public function updateRegularDate($regular, $id) {
        global $DB;
        
        switch($regular) {
            // �����������
            case 2:
                $interval = "date_sending + interval '7 day'";
                break;
            // ����������
            case 3:
                $interval = "date_sending + interval '1 month'";
                break;
            // ��������
            case 4:
                $interval = "date_sending + interval '1 year'";
                break;
            default:
                $interval = false;
        }
        if($interval) {
            $sql = "UPDATE mailer_messages SET date_sending = {$interval} WHERE id = ?i";
            return $DB->query($sql, $id);
        }
        return false;
    }
    
    /**
     * ������� ����� ������������ � ����������� �� ��������� ��������
     * 
     * @param array $filter ������ �������
     * @param array $cnt    ������ ����������
     * @return integer 
     */
    public function calcSumRecipientsCount($filter, $cnt) {
        if($filter['filter_emp'] > 0 && $filter['filter_frl'] > 0) {
            $sum = array_sum($cnt);
        } elseif($filter['filter_emp'] > 0) {
            $sum = $cnt[0];
        } elseif($filter['filter_frl'] > 0) {
            $sum = $cnt[1];
        } else {
            $sum = array_sum($cnt);
        }
        
        return $sum;
    }
    
    /**
     * ������� ��� ������ ���
     * 
     * @param array $mailer  ������ ��������
     * @return type     
     */
    public function getDateSubscr($mailer) {
        // � ���������� - ���� ��������
        if($mailer['in_draft'] == 't' ) {
            return strtotime($mailer['date_created']);
        } else if($mailer['type_send_regular'] > 1) { // ��������� ����������� - ��������� ���� ��������
            return strtotime($mailer['date_sending']);
        } else if($mailer['status_message'] == 1) { // ����������� - ��������� ���� ��������
            return strtotime($mailer['real_date_sending']);
        } else { // � ���� ��������� - ���� ��������
            return strtotime($mailer['date_sending']);
        }
    }
    
    /**
     * �������� ���� ������ ��������
     * 
     * @param type $mailer 
     */
    public function getColorMailer($mailer) {
        // �������� � ���������� � �� ����� �����
        if($mailer['in_draft'] == 't' || $mailer['status_sending'] == 3) {
            return 'b-layout__link_color_a7a7a6';
        } else if($mailer['status_message'] == 1) { // ��� ����������� �����
            return '';
        } else if(strtotime($mailer['date_sending']) <= strtotime("+1week") || $mailer['status_message'] != 1) { // ��� ������������� �������
            return 'b-layout__link_color_c10600';
        } else { // �� ��������� �����
            return '';
        }
    }
    
    public static function checkEmptyRange($value, $name, $subname) {
        return ($value[$name][$subname][0] || $value[$name][$subname][1]);
    }
}

class subfilter {
    
    /** 
     * ������ ���� ������������ � ��
     * 
     */
    const DATA_FORMAT = "Y-m-d H:i";
    
    /**
     *  ��������� ���������� ����� ���������� �������
     *  false = ����� ��������� ����� �������
     *  true = ������ ������������� �������� @see self::$filter;
     */
    public $block_added = false;
    
    /**
     * ���� �������������� ������ ���� �� 1 ���� ������
     * @var boolean
     */
    public $is_update     = false; 
    
    
    function __construct() {
        if(!$this->block_added) {
            $this->filter = array();
        }
    }
    
    function checkRangeDate($from, $to, $error_name) {
        if(strtotime($from) > strtotime($to)) {
            $this->error[$error_name] = true;
        }
    }
    
    /**
     * ��������� ���������� ������ � ���������
     * 
     * @param type $type    ��� ����������� ������
     * @param type $value   ������
     */
    public function setType($type, $value) {
        unset($this->error);
        switch($type) {
            case 'integer':
                $value = intval($value);
                break;
            case 'bit':
                if(is_array($value)) {
                    $value = (string) implode("", $value);
                } else {
                    $value = (string) $value;
                }
                break;
            case 'range_date':
                $date = create_function('$a', 'if($a != "") return date("'.self::DATA_FORMAT.'", strtotime($a));');
                $value = array_map($date, $value);
                $this->checkRangeDate($value[0], $value[1], 'range_date');
                break;
            case 'range_integer':
                $int  = create_function('$a', 'if($a == "") return $a; return intval($a);');
                $value = array_map($int, $value);
                break;
            case 'date':
                $value = date(self::DATA_FORMAT, strtotime($value));
                break;
            case 'string':
            default: 
                $value = (string) $value;
                break; 
        }
        
        return $value;
    }
    
    /**
     * ���������� ������ �������� � ���������
     * 
     * @param string $type    ��� ���������� ���������� @see self::setType();
     * @param string $name    �������� ����
     * @param mixed  $value   �������� ����
     * @return boolean 
     */
    public function setFilter($type, $name, $value) {
        if(!array_key_exists($name, $this->filter) && $this->block_added == true) return false;
        if($this->is_update === false) $this->is_update = true;
        $this->filter[$name] = $this->setType($type, $value);
    }
    
    /**
     * ���� ������ ����������� �������������� ������� 
     * 
     * @example
     * self::startMultiFilter(0, 'a');
     * self::setMultiValue('integer', 'ammount', 100);
     * self::setMultiValue('string', 'keyword', 'key, test');
     * self::endMultiFilter();
     * 
     * @param type $key     �����
     * @param type $name    �������� �������
     */
    public function startMultiFilter($key, $name) {
        $this->multi_name = $name;
        $this->multi_key  = $key;
    }
    
    /**
     * ���� ��������� ���������� �������������� � ������ 
     */
    public function endMultiFilter() {
        $this->multi_name = null;
        $this->multi_key  = null;
    }
    
    /**
     * ���������� ���������� � ������, ������� ����� ���� ��������� @see self::startMultiFilter() self::endMultiFilter
     * 
     * @param type $type
     * @param type $name
     * @param type $value
     * @return boolean 
     */
    public function setMultiValue($type, $name, $value) {
        if(!array_key_exists($name, $this->filter[$this->multi_name][0]) && $this->block_added == true) return false;  
        if($this->is_update === false) $this->is_update = true;
        $this->filter[$this->multi_name][$this->multi_key][$name] = $this->setType($type, $value); 
    }
    
    /**
     * ����� ��������������� ������ �� ���������� ��� ������ � ��
     * @return string 
     */
    public function __toString() {
        if($this->is_update) {
            return serialize($this->filter);
        } 
        
        return "";
    }
    
    /**
     * �������� ������� ������� �� ��
     * 
     * @param string $filter serialize string
     * 
     */
    public function loadFilter($filter) {
        $this->filter = unserialize($filter);
    }
    
    
}

/**
 * ��������� ����������� 
 * ������ ������ �������� ���������� �������, ��������� �������� �������
 * 
 * U - �������� ��� ��� ������������� ���� ����� �������������
 * F - ��� �������������� � �����������
 * E - ��� ������������� � ������������� 
 */

/**
 * ������ ���������� "�������" 
 */
class UFinance extends subfilter 
{
    public $block_added = true; // @see subfilter::$block_added
    
    public $filter = array('money'          => null,              // (inetger) ����� �� �����
                           'spend'          => array(null, null), // (range_date) ��������� �������� 
                           'deposit'        => array(null, null), // (range_date) ��������� ����������
                           'method_deposit' => null               // (bit) ������ ���������� ����� : [1111] - [������.������, Webmoney, ���, ���������� �������]
                    );
}

/**
 * ������ ���������� "�������"  
 */
class UBuying extends subfilter 
{
    public $block_added = true; // @see subfilter::$block_added
    
    public $filter = array('buying'         => null,                // (bit) ������� : [11] - [�� �������� �� ����� �������, �������� ���� �� ���� �������]      
                           'period'         => array(null, null),   // (range_date) �� ������
                           'buy'            => array( 0 => array(   // (multy) ������� (���������� - �������� "��� �������") 
                                                            'type_buy'       => null,               // (integer) ��� ������� (�������� "��� �������", "����� �� ������� ��������", � �.�.)
                                                            'count_buy'      => array(null, null),  // (range_integer) ���������� ����������    
                                                            'sum'            => array(null, null)   // (range_integer) �� �����
                                                            )
                                                )
                    );
}

/**
 * ������ ���������� "�������" (���)  
 */
class FProjects extends subfilter
{
    public $block_added = true; // @see subfilter::$block_added
    
    public $filter = array('period'        => array(null, null),    // (range_date) �� ������ 
                           'count'         => array(null, null),    // (range_integer) ���������� �������
                           'type_project'  => null                  // (bit) ������������ ������� : [111] - [������� ������, ��������, �������]
                    );
}

/**
 * ������ ���������� "�������������"
 */
class FSpecs extends subfilter
{
    public $block_added = true; // @see subfilter::$block_added
    
    public $filter = array('spec_orig'     => null,                             // (integer) �������� ������������
                           'specs'         => array(0 => array('spec' => null)) // (multy) (integer) �������������� ������������� (����� ���� ���������)
                        );
}

/**
 * ������ ���������� "���������� � ������" 
 */
class FBlogs extends subfilter
{
    public $block_added = true; // @see subfilter::$block_added
    
    public $filter = array('period'     => array(null, null),   // (range_date) �� ������
                           'post'       => array(null, null)    // (range_integer) ������� ������
                        );
}

/**
 * ������ ���������� "���������" 
 */
class ULocation extends subfilter
{
    public $block_added = true; // @see subfilter::$block_added
    
    public $filter = array('country'     => null,    // (integer) ������
                           'city'       => null     // (integer) �����
                        );
}

/**
 * ������ ���������� "�������" 
 */
class EProjects extends subfilter
{
    public $block_added = true; // @see subfilter::$block_added
    
    public $filter = array('period'     => array(null, null), // (range_date) �� ������ : array('21.02.2012 12:34', '24.02.2012 13:45');
                           'created'    => array(null, null), // (range_integer) ������ �������� ������ ���� : array(1, 5), ���� null �������
                           'freelance'  => array(null, null), // (range_integer) ������� - ���-���� : array(1, 5), ���� null �������
                           'only_pro'   => array(null, null), // (range_integer) ������� - ������ ��� ��� : array(1, 5), ���� null �������
                           'in_office'  => array(null, null), // (range_integer) ������� - � ���� : array(1, 5), ���� null �������
                           'konkurs'    => array(null, null), // (range_integer) �������� : array(1, 5), ���� null �������
                           'budget'     => array(null, null), // (range_integer) ������ ������� ������� : array(1, 5), ���� null �������
                           'sum_budget' => array(null, null), // (range_integer) ����� �������� ���� �������� : array(1, 5), ���� null �������
                           'avg_answer' => array(null, null), // (range_integer) ������� ���������� ������� �� ������� : array(1, 5), ���� null �������
                           'executor'   => null,              // (bit) ���������� �� ������������ : [111] = [������� ������, ��������, �������]
                           'spec'       => null               // (integer) ������������� : 14
                        );
}

/**
 * ������ ���������� "��������" 
 */
class EMasssend extends subfilter
{
    public $block_added = true; // @see subfilter::$block_added
    
    public $filter = array('spec'       => null,             // (integer) ������������� : 14
                           'recipient'  => array(null, null) // (range_integer) �����������   : array(3,7)  
                        );
}
     

?>