<?
/**
 * ���������� ���� � ��������� ��������� ������ � ��
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

/**
 * ����� �������������� ��������� � ������� ���������
 *
 */
class lm {

    /**
     * ���������� ���������� � ������� ��������� ����������
     *
     * @param   integer $id ������������� ��������� � ��
     * @return  array       ���������� � ��������� ����������
     */
    function GetDocumentsInfo($id) {
        global $DB; 
        $sql = "SELECT * FROM lm_docs WHERE id=?i";
        return $DB->row($sql, $id);
    }

    /**
    * ��������� ���������� � ������� ��������� ���������� ����������
    *
    * @param    integer $id     ID ��������� � ��
    * @param    boolean $status ������ ����������(true - ��������, false - ���)
    * @param    string  $date   ���� ������� ����������
    */
    function UpdateDocumentsSend($id, $status, $date) {
        global $DB;
        $sql = "UPDATE lm_docs SET docsend = ?, docsend_time = ? WHERE id = ?i";
        $DB->query($sql, $status, ($status?$date:NULL), $id);
    }

    /**
    * ��������� ���������� � ������� ��������� ����������� ����������
    *
    * @param    integer $id     ID ��������� � ��
    * @param    boolean $status ������ ����������(true - ���������, false - ���)
    * @param    string  $date   ���� ��������� ����������
    */
    function UpdateDocumentsBack($id, $status, $date) {
        global $DB;
        $sql = "UPDATE lm_docs SET docback = ?, docback_time = ? WHERE id = ?i";
        $DB->query($sql, $status, ($status?$date:NULL), $id);
    }

    /**
    * ��������� ������ � ���� ���������� ��
    *
    * @param    integer $user_id    ID ������������
    * @param    integer $op_id      ID ��������
    * @return   integer             ID ���������� ������
    */
    function AddDocumentRecord($user_id, $op_id) {
        global $DB;
        $sql = "INSERT INTO lm_docs(opid, user_id, docsend_time, docsend, docback_time, docback) VALUES(?i, ?i, NULL, false, NULL, false) RETURNING id";
        $id = $DB->val($sql, $op_id, $user_id);
        return $id;
    }

    /**
    * ���������� ������ ���� � �����-�������
    *
    * @param    integer $id         ������������� ��������� � ��
    * @param    string  $file_sf    ��� ����� �����-�������
    * @param    string  $file_act   ��� ����� ����
    */
    function UpdateFiles($id, $file_sf, $file_act) {
        global $DB;
        $sql = "UPDATE lm_docs SET file_sf=?, file_act=? WHERE id=?i";
        $DB->query($sql, $file_sf, $file_act, $id);
    }

	/**
	 * ���������� ��� ��������� � �� �� ������ ������
	 *
	 * @param string $fdate			    � ������ �����
	 * @param string $tdate			    �� ����� �����
	 * @param string $search            ��������� �����
	 * @param array  $sort              ��� ���������� [login=> DESC ...]
     * @param string $date_search_type  �� ����� ����� ����, 1-����, 0-��� (X1X2, X1 - �� ���� ��������, X2 - �� ���� �������� ����������)
	 * @return array				    ���������� �� ����������
	 */
    function GetLMRequests($fdate, $tdate, $search=NULL, $sort = NULL, $date_search_type='11') {
        global $DB;
        if($sort) {
            $sort_fld = array_keys($sort);
            $sort_fld = $sort_fld[0];
            $dir = $sort[$sort_fld];
            switch($sort_fld) {
                case 'login':
                    $orderby = "lower(u.login) {$dir}, ao.id";
                    break;
                case 'status':
                    $orderby = "COALESCE(ao.op_date, lmd.docsend_time, lmd.docback_time, 'epoch') {$dir}, ao.id";
                    break;
                case 'date':
                    $orderby = "ao.id {$dir}";
                    break;
                default:
                    $orderby = "ao.id";
                    break;
            }
        }

        $where = '';
        if(substr($date_search_type,0,1)) {
            // ���� ��������
            $where .= " (ao.op_date >= '$fdate' AND ao.op_date-'1 day'::interval < '$tdate') OR ";
        }
        if(substr($date_search_type,1,1)) {
            // ���� �������� ����������
            $where .= " (lmd.docsend_time >= '$fdate' AND lmd.docsend_time-'1 day'::interval < '$tdate') OR ";
        }
        if($where)
            $where = '('.preg_replace("/OR $/","",$where) . ')';

        if($search) {
            $where .= $where ? ' AND' : 'WHERE';
            $where .= " (u.uname ilike '%{$search}%'
                         OR u.usurname ilike '%{$search}%' 
                         OR u.login ilike '%{$search}%')";
        }

        $sql = "SELECT lmd.*,
                       u.*,
                       ao.op_date,
                       ao.id as ao_id,
                       (-1*ao.ammount) as ao_ammount
                FROM users as u
                LEFT JOIN 
                    account a 
                    ON a.uid=u.uid 
                LEFT JOIN 
                    account_operations ao 
                    ON ao.billing_id=a.id 
                LEFT JOIN 
                    op_codes oc 
                    ON oc.id = ao.op_code
                LEFT JOIN
                    lm_docs lmd
                    ON lmd.opid = ao.id
                WHERE 
                    ao.op_code = 82 AND 
                    {$where}
                ORDER BY {$orderby}";

		return $DB->rows($sql);
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

}
?>
