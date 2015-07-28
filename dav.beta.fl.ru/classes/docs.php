<?

/**
 * ���������� ���� � ��������� ���������
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 *
 * ����� ��� ������ � �������� ���������� (docs)
 *
 */
class docs {

    /**
     * ����� ����������
     * 
     * @param  int ����������� ID ������� ����������
     * @return array ������ �������
     */
    public static function getDocs($section_id = false) {
        global $DB;
        
        if ( $section_id ) {
        	$data = $DB->rows( 'SELECT D.*, S.name AS section_name FROM docs D
                        INNER JOIN docs_sections S ON (D.docs_sections_id = S.id)
                        WHERE docs_sections_id = ?i ORDER BY sort', $section_id );
        }
        else {
            $data = $DB->rows( 'SELECT D.*, S.name AS section_name  FROM docs D
                        INNER JOIN docs_sections S ON (D.docs_sections_id = S.id)
                        ORDER BY S.sort, sort' );
        }
        
        if ($data)
            foreach ($data as &$item) {
                $item['attach'] = docs_files::getDocsFiles($item['id']);
            }
        return $data;
    }

    /**
     * ����� ��������� $limit ����������
     * 
     * @param  int $limit ���������� ����������
     * @return array ������ �������
     */
    public static function getLast($limit = 10) {
        global $DB;
        
        return $DB->rows( 'SELECT D.*, S.name AS section_name  FROM docs D
                        INNER JOIN docs_sections S ON (D.docs_sections_id = S.id)
                        ORDER BY date_create DESC LIMIT ?i', $limit );
    }

    /**
     * ����� ������������ ��������
     *
     * @param integer $id �� ���������
     * @return array ������ �������
     */
    public static function getDoc($id) {
        global $DB;
        $data = $DB->row( 'SELECT * FROM docs WHERE id = ?i', $id );
        $data['attach'] = docs_files::getDocsFiles($id);
        return $data;
    }

    /**
     * �������� �������� � ��
     *
     * @param string  $name    ������
     * @param string  $desc    ��������
     * @param string  $section_id      ID ������
     * @return mixed  false - ������, id-������ � ������ ������
     */
    public static function Add($name, $desc, $section_id) {
        if(!trim($name)) return false;
        global $DB;
        $max = $DB->val( 'SELECT MAX(sort) as _max FROM docs WHERE docs_sections_id= ?i', $section_id );
        $iOrder = ($max) ? ($max + 1) : 1;
        $sql = "INSERT INTO docs (\"name\", \"desc\", docs_sections_id, sort) VALUES (?, ?, ?i, ?i);
                SELECT MAX(id) FROM docs AS last_insert_id;";

        $tmp = $DB->val( $sql, trim($name), $desc, $section_id, $iOrder );
        return $DB->error ? false : $tmp;
    }

    /**
     * �������� ��������
     *
     * @param string  $docs_id    ID ���������
     * @param string  $name      ��������
     * @param boolean $desc     ��������
     * @param integer $section_id �� ������ ���������
     * @return string ��������� �� ������
     */
    public static function Update($docs_id, $name, $desc, $section_id) {
        if(!trim($name)) return false;
        global $DB;
        $sql = "UPDATE docs SET \"name\"=?, \"desc\"=?, docs_sections_id=?i, date_update = NOW()
                WHERE id = ?i" ;
        
        $DB->query( $sql, trim($name), $desc, $section_id, $docs_id );
        
        return $DB->error;
    }

    /**
     * ������� Doc
     *
     * @param mixed $docs_id �� ������� ��� ������ � ���� id|id2|id3...
     * @return string ��������� �� ������
     */
    public static function Delete($id) {
        if (is_numeric($id)) {
            $files = docs_files::getDocsFiles($id);
            $file = new CFile();
            foreach ($files as $key => $value) {
                $file->Delete($value['file_id']);
            }
            
            global $DB;
            $DB->query( "DELETE FROM docs WHERE id = ?i", $id );
            
            return $DB->error;
        } else {
            foreach (explode('|', $id) as $idx) {
                if (!(int) $idx)
                    continue;
                self::Delete((int) $idx);
            }
            return false;
        }
    }

    /**
     * ��������� �������� � ������ ������
     * 
     * @param mixed $docs_id �� ������� ��� ������ � ���� id|id2|id3...
     * @param integer $section ID ������
     * @return string ��������� �� ������
     */
    public static function Move($docs_id, $section) {
        global $DB;
        
        if (is_numeric($id)) {
            $DB->update( 'docs', array('docs_sections_id' => $section), 'id = ?i', $docs_id );
            return $DB->error;
        } else {
            $in = array();
            foreach (explode('|', $docs_id) as $idx) {
                if (!(int) $idx)
                    continue;
                $in[] = $idx;
            }
            if (count($in)) {
                $DB->update( 'docs', array('docs_sections_id' => $section), 'id IN (?l)', $in );
                return $DB->error;
            } else {
                return false;
            }
        }
    }

    /**
     * ���������� ��������� �����
     *
     * @param string $s      ��������� �����
     * @return string        ��������������� ��������� �����
     */
    public static function filterQuery($s) {
        $s = strip_tags($s);
        $s = str_replace('&nbsp;', ' ', $s);
        $s = html_entity_decode($s, ENT_QUOTES, 'cp1251');
        $s = self::filterStopWords($s);
        $s = preg_replace('/\s+/', ' ', $s);
        $s = trim($s);
        return $s;
    }

    /**
     * �������� �� ��������� ����� ������, ��������� � �.�.
     *
     * @param string $s      ��������� �����
     * @return string        ��������� ����� ��� ���������, ������ � �.�.
     */
    public static function filterStopWords($s) {
        $stopWords = array(
            '�',
            '���', '�����', '��', '���', '����', '����', '����', '����',
            '�', '���', '���', '����', '��', '���', '���', '�����', '����', '��',
            '���',
            '��', '����', '���', '��',
            '���', '��', '����', '����', '���',
            '��',
            '��', '�����',
            '�', '��', '���', '��', '��',
            '�', '���', '��', '�����', '���',
            '��', '����',
            '���', '�����', '��',
            '��', '����', '���', '��', '����', '���', '���', '��', '���', '��', '��',
            '�', '��', '������', '��', '���', '���', '���', '��', '�����',
            '��', '���', '���',
            '�', '��',
            '���', '�����', '�����', '���', '��', '���', '��', '����', '����', '���', '������', '���', '��',
            '�', '���',
            '����',
            '����', '���', '���', '���', '�����', '���', '���',
            '���', '���', '���',
            '�', ','
        );
        foreach ($stopWords as $w) {
            $s = preg_replace('/(?<!\pL)' . $w . '(?!\pL)/', '', $s);
        }
        $s = preg_replace("/\. /", " ", $s);
        $s = preg_replace("/\(/", "", $s);
        $s = preg_replace("/\)/", "", $s);
        $s = preg_replace("/�/", "", $s);
        $s = preg_replace("/�/", "", $s);
        $s = preg_replace("/\"/", "", $s);
        return $s;
    }

    /**
     * �������������� ������ ������ ��� ������ c ���������� ����
     *
     * @param    string  $s          ����� ������
     * @param    boolean $is_short   true - �� �������� �����, false - �������� ������ ����� ����� � �������������� ������
     * @param    string  $query      �����, ������� ���������� ����������
     * @param    boolean is_highlight    ����� �� ������������ �����
     * @return   string              ����� � ���������� ����
     */
    public static function cut($s, $is_short = false, $query = null, $is_highlight=true) {
        setlocale(LC_ALL, 'ru_RU.CP1251');
        $ret = '';
        $rw = "A-Za-z0-9�-��-�_.;&@";
        if (empty($s)) {
            return $ret;
        }
        $query = self::filterQuery($query);
        $qParams = preg_split('/[\s,:!?)(_]/u', $query);
        $temp = array();
        foreach ($qParams as $q) {
            if (!empty($q)) {
                $temp[] = $q;
            }
        }
        $qParams = $temp;
        unset($temp);

        if ($is_short) {
            foreach ($qParams as $q) {
                $pos = stripos($s, $q);
                if (!$pos)
                    return '';
                if ($pos > 200) {
                    $lpos = @strpos($s, ' ', $pos - 100);
                    $rpos = @stripos($s, ' ', $pos + 100);
                    if (empty($lpos))
                        $lpos = 0;
                    if (empty($rpos))
                        $rpos = strlen($s);
                    $ret = substr($s, $lpos, ($rpos - $lpos));
                    if ($lpos != 0)
                        $ret = '... ' . $ret;
                    if ($rpos != strlen($s))
                        $ret = $ret . ' ...';
                } else {
                    if (strlen($s) > 200) {
                        $ret = substr($s, 0, strpos($s, ' ', 200) - 1);
                        $ret .= ' ...';
                    } else {
                        $ret = $s;
                    }
                }
            }
        } else {
            foreach ($qParams as $q) {
                $ret = $s;
            }
        }

        if ($is_highlight) {
            $ret = preg_replace('/(?<!\pL)([' . $rw . '-]*' . preg_quote($q, '/') . '[' . $rw . '-]*)(?!\pL)/i', '=====s=====\\1=====e=====', $ret);
            $ret = preg_replace("/=====s=====/", '<strong class="help-colored">', $ret);
            $ret = preg_replace("/=====e=====/", '</strong>', $ret);
        }
        return $ret;
    }

    /**
     * ���������� ��������� ��������������� ���������� �������.
     * 
     * @param string $query - ������ ��� ������
     * @return mixed ��������� ������
     */
    public static function Search($query) {
        $text = self::filterQuery($query);
        $s_texts = explode(" ", $text);
        foreach ($s_texts as $s_word) {
            $s_word = trim(pg_escape_string(DBConnect(), $s_word));
            // � ��������� ���� ��� ��� �����
            $sql_1 .= "LOWER(h.\"name\") LIKE LOWER('%$s_word%') AND ";
            // � ��������� ���� ���� �� ���� �� ����.
            $sql_3 .= "LOWER(h.\"name\") LIKE LOWER('%$s_word%') OR LOWER(h.\"desc\") LIKE LOWER('%$s_word%') OR ";
        }
        $sql_1 = preg_replace("/AND $/", "", $sql_1);
        $sql_3 = preg_replace("/OR $/", "", $sql_3);
        // � ������ ����� ���������� ��������� (������� � ������� ���� ���������)
        $sql_2 = "LOWER(h.desc) LIKE LOWER('%" . pg_escape_string(DBConnect(), $text) . "%') ";
        $sql = "SELECT h.*
                 FROM docs AS h
                 WHERE ($sql_1)
                UNION ALL
                SELECT h.*
                 FROM docs AS h
                 WHERE ($sql_2)
                UNION ALL
                SELECT h.*
                 FROM docs AS h
                 WHERE ($sql_3)";
        
        global $DB;
        $results = $DB->rows( $sql );
        
        if ( count($results) ) {
            $idx = array();
            while (list($key, $result) = each($results)) {
                if (in_array($result['id'], $idx)) {
                    unset($results[$key]);
                    continue;
                } else {
                    array_push($idx, $result['id']);
                }
                $text = strip_tags(strtr($result['desc'],array('&nbsp;' => ' ',
                    '&laquo;' => '"',
                    '&raquo;' => '"',
                    '&quot;' => '"',)));
                reset($s_texts);
                $n = 0;
                foreach ($s_texts as $word) {
                    $mode = ($n == 0) ? true : false;
                    $r_text = self::cut($text, $mode, $word, false);
                    if (!$r_text) {
                        $n = 0;
                    } else {
                        $text = $r_text;
                        $n = 1;
                    }
                }
                reset($s_texts);
                foreach ($s_texts as $word) {
                    $text = self::cut($text, false, $word, true);
                }
                $results[$key]['desc'] = $text;
            }
            return $results;
        } else {
            return false;
        }
    }

}