<?

/**
 * ���������� ���� � ��������� ���������
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 *
 * ����� ��� ������ � �������
 *
 */
class docs_files {

    /**
     * ����� ����� ��� ������������� ���������
     * $param integer $docs_id - �� ���������
     * @return array ������ �������
     */
    public static function getDocsFiles($docs_id) {
        global $DB;
        $sql = 'SELECT D.*, F.id AS cfile_id, F.size, F.path, F.modified, F.fname
        FROM docs_files D
        INNER JOIN file F ON (D.file_id = F.id)
        WHERE D.docs_id = ?i
        ORDER BY D.sort';
        
        $data = $DB->rows( $sql, (int)$docs_id );
        
        if ($data){
            foreach ($data as &$file) {
                $file['ico_class'] = getIcoClassByExt($file['fname']);
                $file['file_size'] = sizeFormat($file['size']);
            }
            self::SelectMinMax($data);
        }
        return $data;
    }

    /**
     * ������������� ����� �� ������������� ������ ������, � ����� ��������� �� ������� ����������
     * 
     * @param array $data ����� �������������� � ���������.
     */
    public static function SelectMinMax(&$data){
        $min = $data[0]['sort'];
        $min_id = 0;
        $max = 0;
        $max_id = 0;
        foreach ($data as $key => $item){
            $item['is_first'] = false;
            $item['is_last'] = false;
            if($min > $item['sort']){
                $min = $item['sort'];
                $min_id = $key;
            }
            if($max < $item['sort']){
                $max = $item['sort'];
                $max_id = $key;
            }
        }
        $data[$min_id]['is_first'] = true;
        $data[$max_id]['is_last'] = true;
    }

    /**
     * ����� ������������ ����
     *
     * @param integer $id �� �����
     * @return array ������ �������
     */
    public static function getFile($id) {
        global $DB;
        $sql = 'SELECT D.*, F.id AS cfile_id, F.size, F.path, F.modified, F.fname
        FROM docs_files D
        INNER JOIN file F ON (D.file_id = F.id)
        WHERE D.id = ?i';
        
        return $DB->row( $sql, $id );
    }

    /**
     * �������� ����� ���� � ��
     *
     * @param integer $docs_id      �� ���������
     * @param string  $file_id      �� ����� �� ������� file
     * @param string  $file_name    ��� ����� ��� ������
     * @return string ��������� �� ������
     */
    public static function Add($docs_id, $file_id, $file_name) {
        global $DB;
        $max  = $DB->val( 'SELECT MAX(sort) as _max FROM docs_files WHERE docs_id = ?i', $docs_id );
        $sort = ($max) ? ($max + 1) : 1;
        $data = compact( 'docs_id', 'file_id', 'file_name', 'sort' );
        
        $DB->insert( 'docs_files', $data );
        
        return $DB->error;
    }

    /**
     * ������� ����
     *
     * @param integer $id �� �����
     * @return string ��������� �� ������
     */
    public static function Delete($id) {
        global $DB;
        $fid = $DB->val( "DELETE FROM docs_files WHERE id = ?i RETURNING file_id", $id );
        
        if ( $fid ) {
            $file = new CFile();
            $file->Delete( $fid );
        }
        
        return $DB->error;
    }

    /**
     * ������� ������ ������� ����� � ���������� �� -1
     *
     * @param integer $id �� �����
     * @return integer �� ��������� (����� ��� xajax)
     */
    public static function MoveDown($id) {
        global $DB;
        $curr  = self::getFile($id);
        $sql   = "SELECT id, sort FROM docs_files WHERE docs_id = ?i AND sort = (SELECT MIN(sort) FROM docs_files WHERE docs_id = ?i AND sort > ?i);";
        $donor = $DB->row( $sql, $curr['docs_id'], $curr['docs_id'], $curr['sort'] );
        if ( $donor ) {
            $DB->update( 'docs_files', array('sort' => $curr['sort']), 'id = ?i', $donor['id'] );
            $DB->update( 'docs_files', array('sort' => $donor['sort']), 'id = ?i', $curr['id'] );
            
            return $curr['docs_id'];
        }
        return $curr['docs_id'];
    }

        /**
     * ������� ������ ������� ����� � ���������� �� +1
     *
     * @param integer $id �� �����
     * @return integer �� ��������� (����� ��� xajax)
     */
    public static function MoveUp($id) {
        global $DB;
        $curr  = self::getFile($id);
        $sql   = "SELECT id, sort FROM docs_files WHERE docs_id = ?i AND sort = (SELECT MAX(sort) FROM docs_files WHERE docs_id = ?i AND sort < ?i);";
        $donor = $DB->row( $sql, $curr['docs_id'], $curr['docs_id'], $curr['sort'] );
        if ( $donor ) {
            $DB->update( 'docs_files', array('sort' => $curr['sort']), 'id = ?i', $donor['id'] );
            $DB->update( 'docs_files', array('sort' => $donor['sort']), 'id = ?i', $curr['id'] );
            
            return $curr['docs_id'];
        }
        return $curr['docs_id'];
    }
}