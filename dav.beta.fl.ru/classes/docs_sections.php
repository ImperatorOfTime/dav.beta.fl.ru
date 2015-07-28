<?

/**
 * ���������� ���� � ��������� ���������
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

class docs_sections {

    /**
     * ����� ��������� ����������
     *
     * @return array ������ �������
     */
    public static function getSections() {
        global $DB;
        $sql  = 'SELECT DISTINCT A.id, A."name", A.date_create, A.date_update, A.sort, COUNT(B.id) 
                FROM docs_sections A 
                LEFT JOIN docs B ON (A.id = B.docs_sections_id) 
                GROUP BY A.id, A."name", A.date_create, A.date_update, A.sort 
                ORDER BY A.sort';
        $data = $DB->rows( $sql );
        if ($data)
            docs_files::SelectMinMax($data);
        return $data;
    }

    /**
     * ����� ������������ ��������� ����������
     *
     * @param integer $id �� ���������
     * @return array ������ �������
     */
    public static function getSection($id) {
        global $DB;
        $sql = 'SELECT DISTINCT A.id, A."name", A.date_create, A.date_update, A.sort, COUNT(B.id) 
                FROM docs_sections A 
                LEFT JOIN docs B ON (A.id = B.docs_sections_id) 
                WHERE A.id = ?i 
                GROUP BY A.id, A."name", A.date_create, A.date_update, A.sort';
        
        return $DB->row( $sql, $id );
    }

    /**
     * �������� ����� ������
     *
     * @param string  $name    Name
     * @return string ��������� �� ������
     */
    public static function Add($name) {
        global $DB;
        $max    = $DB->val( 'SELECT MAX(sort) as _max FROM docs_sections' );
        $iOrder = ($max) ? ($max + 1) : 1;
        $DB->insert( 'docs_sections', array('name' => $name, 'sort' => $iOrder) );
        return $DB->error;
    }

    /**
     * �������� ��� ������
     *
     * @param integer  $id    ID
     * @param string  $name      ���
     * @return string ��������� �� ������
     */
    public static function Update($id, $name) {
        global $DB;
        $name = trim($name);
        $id   = (int)$id;
        $sql  = "UPDATE docs_sections SET name=?, date_update = NOW() WHERE id = ?i";
        
        $DB->query( $sql, $name, $id );
        
        return $DB->error;
    }

    /**
     * ������� ������
     *
     * @param integer $id �� ������
     * @return string ��������� �� ������
     */
    public static function Delete($id) {
        global $DB;
        $sql = "DELETE FROM docs_sections WHERE id = ?i AND (SELECT COUNT(id) FROM docs WHERE docs_sections_id = ?i) = 0;";
        $DB->query( $sql, $id, $id );
        return $DB->error;
    }
    
    /**
     * ������� ������
     *
     * @param integer $id �� ������
     * @return string ��������� �� ������
     */
    public static function DeleteList($ids) {
        global $DB;
        if ( !is_array($ids) ) return false;
        $sql = "DELETE FROM docs_sections WHERE id IN (?l) AND (SELECT COUNT(id) FROM docs WHERE docs_sections_id IN (?l)) = 0;";
        $DB->query( $sql, $ids, $ids );
        return $DB->error;
    }

        /**
     * ������� ������ ������� ������� � ���������� �� -1
     *
     * @param integer $id �� �������
     * @return string ��������� �� ������
     */
    public static function MoveDown($id) {
        global $DB;
        $curr  = self::getSection($id);
        $sql   = "SELECT id, sort FROM docs_sections WHERE sort = (SELECT MIN(sort) FROM docs_sections WHERE sort > ?i);";
        $donor = $DB->row( $sql, $curr['sort'] );
        if ( $donor ) {
            $sql = "UPDATE docs_sections SET sort = ?i WHERE id = ?i;
                    UPDATE docs_sections SET sort = ?i WHERE id = ?i;";
            $DB->query( $sql, $curr['sort'], $donor['id'], $donor['sort'], $curr['id'] );
            return $DB->error;
        }
        return false;
    }

            /**
     * ������� ������ ������� ������� � ���������� �� +1
     *
     * @param integer $id �� �������
     * @return string ��������� �� ������
     */
    public static function MoveUp($id) {
        global $DB;
        $curr  = self::getSection($id);
        $sql   = "SELECT id, sort FROM docs_sections WHERE sort = (SELECT MAX(sort) FROM docs_sections WHERE sort < ?i);";
        $donor = $DB->row( $sql, $curr['sort'] );
        if ( $donor ) {
            $sql = "UPDATE docs_sections SET sort = ?i WHERE id = ?i;
                    UPDATE docs_sections SET sort = ?i WHERE id = ?i;";
            $DB->query( $sql, $curr['sort'], $donor['id'], $donor['sort'], $curr['id'] );
            return $DB->error;
        }
        return false;
    }

}