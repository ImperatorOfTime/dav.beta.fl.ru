<?php

require_once("stdf.php");

/**
 * ����� ��� ������ �� ��������� /catalog/, /catalog/admin/
 * ��� �������� ������ �������� �������� � ������� ����� ���������.
 * ������ � ����� ������ ����� ������� ������ �� ������������ ����������, ����� ����� ���������� ��� ������
 * 
 * @example 
 * <?php
 * // ������ ������� �� ��������� "spb" (���� ��� �� ����������, �� ���������)
 * $seo = new seo("spb");
 * // ������� ������ ���� ���������� ������������ � �������
 * $seo_full = new seo();
 * ?>
 * 
 */
class seo
{
    /**
     * �������� - �� ��������� ��� ���������
     *
     * @var array
     */
    public $subdomain = array("id"             => 0,
                              "subdomain"      => "",
                              "name_subdomain" => "��� �������");
    const COUNT_DINAMIC_ARTICLES = 6;
    
    /**
     * ����������� ������
     *
     * @param string|boolean $subdomain   ���������� �� ����� �������� ��������, �� ��������� false
     */
    public function __construct($subdomain = false){
        if($subdomain) {
            $this->subdomain = $this->getSubdomainByName($subdomain);
        } 
    }

    /**
     * ��������� ��� ������
     *
     * @param    string    $subdomain   �����
     * @param    string    $direction   �����������
     * @param    string    $dir         ������
     * @param    string    $folder      ������
     * @return   string                 ��� ������
     */
    function getFriendlyURL($subdomain, $direction, $dir, $folder='') {
        global $host;
        if ( $subdomain == '' && preg_match("/http:\/\/www\./", $host) ) {
            $subdomain = 'www';
        }
        $url = (($subdomain && $subdomain!='all') ? HTTP_PREFIX.$subdomain.'.'.(preg_replace('~^'.HTTP_PREFIX.'(www\.)?~', '', $host)).'/catalog/' : $host.'/catalog/');
        $url .= ($direction ? $direction.'/' : '');
        $url .= ($folder ? $folder.'/' : '');
        $url .= ($dir ? $dir.'.html' : '');
        return $url;
    }
    /**
     * ��������� ����������� ��� ������ ������ � �����������/�������
     *
     * @param    string    $type         ��� ������: direct - �����������, section - ������
     * @param    string    $link         ����������� ������
     * @param    integer   $direct_id    ID �����������
     * @param    integer   $parent_id    ID �������
     * @param    integer   $section_id   ID ��������
     * @param    interer   $subdomain_id ID ���������
     * @return   boolean                 true - ������ ����, false - ������ ���
     */
    public function checkLink($type, $link, $direct_id=0, $parent_id=0, $section_id=0, $subdomain_id=0) {
        global $DB;

        $ret = true;
        $link = strtolower($link);
        switch($type) {
            case 'direct':
                $sql = "SELECT 1 FROM seo_direct WHERE lower(name_section_link)=?u AND id<>?i";
                $ret = (bool) $DB->val($sql, $link, $direct_id);
                break;
            case 'section':
                $sql = "SELECT 1 
                        FROM seo_sections AS s
                        ".($parent_id<>0 ? "LEFT JOIN seo_bind AS b ON b.section_id = s.id" : "")."
                        WHERE s.direct_id=?i 
                          AND s.parent=?i 
                          AND lower(s.name_section_link)=?u 
                          AND s.id<>?i
                          ".($parent_id<>0 ? $DB->parse("AND b.subdomain_id=?i", $subdomain_id) : "")."
                        ";
                $ret = (bool) $DB->val($sql, $direct_id, $parent_id, $link, $section_id);
                break;
        }
        return $ret;
    }

    /**
     * �������� ID ����� ��� ������� ������� ����
     *
     * @return array    ������ � ID ����� � �� ��������
     */
    function getCountries() {
        global $DB;
        $sql = "SELECT * FROM country WHERE id IN (SELECT DISTINCT(country_id) FROM seo_subdomain) ORDER BY pos";
        return $DB->rows($sql);
    }
    
    /**
     * ����� ���  ������� � ������������, ��������� �� ����� ������� @see self::parseResultSections($result)
     *
     * @return array $section ������ �������� � ����������� ����������� (array[0]['subsections']) 
     */
    public function getSections($full = true, $direct_id = null) {
        global $DB;
        
        $inner_subdomain = $this->getSubdomainInnerSQL();
        if($full) {
            $full_sql = "UNION ALL 
                        SELECT ss.*, sb.is_draft, sb.subdomain_id FROM seo_sections ss
                        {$inner_subdomain}
                        WHERE ss.parent <> 0 ORDER BY parent, pos_num";    
        } else {
            $full_sql = "ORDER BY parent, pos_num";
        }
        
        $where_add = "";
        if ($direct_id) {
            $direct_id = $direct_id == -1 ? ' IS NULL' : ' = ' . (int) $direct_id;
            $where_add = " AND ss.direct_id " . $direct_id;
        }
        
        $sql = "SELECT ss.*, sb.is_draft, sb.subdomain_id FROM seo_sections ss 
                LEFT JOIN seo_bind sb ON sb.id = ss.bind WHERE ss.parent = 0 {$where_add} {$full_sql}";
                
        $section = $this->parseResultSections($DB->rows($sql));
        
        return $section;
    }

    /**
     * ����� ���  ������� � ������������ ��� ������� ��������, ��������� �� ����� ������� @see self::parseResultSections($result)
     *
     * @return array $section ������ �������� � ����������� ����������� (array[0]['subsections']) 
     */
    public function getSectionsForMain($limit=5) {
        global $DB;
        
        $inner_subdomain = $this->getSubdomainInnerSQL();
        
        $sql = "SELECT ss.id, ss.bind, ss.parent, ss.pos_num, ss.date_create, ss.date_modified, ss.name_section, ss.name_section_link, ss.direct_id, sb.is_draft, sb.subdomain_id FROM seo_sections ss 
                LEFT JOIN seo_bind sb ON sb.id = ss.bind WHERE ss.parent = 0
                UNION ALL 
                SELECT ss.id, ss.bind, ss.parent, ss.pos_num, ss.date_create, ss.date_modified, ss.name_section, ss.name_section_link, ss.direct_id, sb.is_draft, sb.subdomain_id FROM seo_sections ss
                {$inner_subdomain}
                WHERE ss.parent <> 0 ORDER BY parent, date_create desc 
                ";
                
        $section = $this->parseResultSections($DB->rows($sql), $limit);
        
        return $section;
    }
    
    /**
     * ����� ������ (� ��� ����������) �� ��� �� 
     *
     * @param  inetger $section_id �� �������
     * @return array
     */
    public function getFullSectionById($section_id) {
        global $DB;
        if(!$section_id) return false;
        
        $inner_subdomain = $this->getSubdomainInnerSQL();
           
        $sql = "SELECT * FROM seo_sections WHERE id = ?i
                 UNION ALL
                SELECT ss.* FROM seo_sections ss 
                {$inner_subdomain}
                WHERE ss.parent = ?i ORDER BY parent, pos_num";
        $result = $DB->rows($sql, $section_id, $section_id);
        if($result) {
            return current($this->parseResultSections($result));
        }
        return false;
    }
    
    /**
     * ����� ������ ������ ������������� ������� (��� �����������) �� ��� ��
     *
     * @param integer $section_id  �� ������� (����������)
     * @return array ������ �������
     */
    public function getSectionById($section_id) {
        global $DB;
        if(!$section_id) return false;
        
        $sql = "SELECT ss.*, sb.is_draft, sb.subdomain_id, sb.id as bind FROM seo_sections ss LEFT JOIN seo_bind sb ON sb.section_id = ss.id WHERE ss.id = ?i";   
        
        return $DB->row($sql, $section_id); 
    }
    
    /**
     * ����� ������ ������ ������������� ������� (��� �����������) �� ��� ��
     *
     * @param integer $section_id  �������� ������� (����������)
     * @return array ������ �������
     */
    public function getSectionByName($section_name, $with_subdomain=true, $direct_id=NULL, $cat=NULL) {
        global $DB;
        if(!$section_name) return false;

        if($with_subdomain) {
            $sql = "SELECT ss.*, sb.is_draft, sb.subdomain_id, sb.id as bind FROM seo_sections ss JOIN seo_bind sb ON sb.section_id = ss.id AND sb.subdomain_id = ?i WHERE ss.name_section_link = ? ".($direct_id ? "AND ss.direct_id = ?i" : "").($cat ? $DB->parse("AND ss.parent = (SELECT id FROM seo_sections WHERE parent=0 AND name_section_link=? AND direct_id=?i)", $cat, $direct_id) : "");   
            $ret = $DB->row($sql, $this->subdomain['id'], $section_name, $direct_id); 
        } else {
            $sql = "SELECT ss.* FROM seo_sections ss WHERE ss.name_section_link = ? ".($direct_id ? "AND ss.direct_id = ?i" : "");               
            $ret = $DB->row($sql, $section_name, $direct_id); 
        }

        return $ret;
    }
    
    /**
     * ��������� ��������� ������ ��������
     *
     * @param array $result ��������� ������ �������� @see self::getSections();
     * @return array $section ������ �������� � ����������� ����������� (array[0]['subsections']) 
     */
    public function parseResultSections($result, $limit=NULL) {
        if(!$result) return false;
        foreach($result as $key=>$value) {
            if($value['parent'] == 0) {
                $section[$value['id']] = $value;
            } else {
                if (!isset($section[$value['parent']])) continue;
                if ($limit!=NULL) {
                    if (count($section[$value['parent']]['subsection'])>=$limit) continue;
                }
                $section[$value['parent']]['subsection'][] = $value;
            }
        }
        
        return $section;
    }
    
    /**
     * ����� ������ ��������� �� ��� �����
     *
     * @param string $name ��� ��������� (spb, msk)
     * @return array ������ ���������
     */
    public function getSubdomainByName($name) {
        global $DB;
        
        if(is_integer($name)) {
            $sql = "SELECT * FROM seo_subdomain WHERE id = ?i";
        } else {
            $sql = "SELECT * FROM seo_subdomain WHERE subdomain = ?";
        }
        $result = $DB->row($sql, $name);
        if(!$result) return $this->subdomain;
        return $result;
    }

    /**
     * �������� ������ ���������� � ������� ���� ������ � ������ �����������
     *
     * @param    $integer    $direct_id     ID �����������
     * @return   array                      ������ �������
     */
    function getSubdomainsByDirectID($direct_id) {
        global $DB;
        $sql = "SELECT DISTINCT ON (ss.num) ss.* 
                FROM seo_subdomain ss 
                INNER JOIN seo_bind sb ON ss.id = sb.subdomain_id AND sb.is_draft = 'f' 
                INNER JOIN seo_sections ssec ON ssec.bind = sb.id 
                ORDER BY ss.num ASC";
        $ret = $DB->rows($sql);
        foreach($ret as $k=>$val) $result[$val['id']] = $val;
        return $result;
    }
    
    /**
     * ����� ��� ��������� ������� ���� � ����
     *
     * @param boolean $active ���� true, �� ����� ������ �� � ������� ����������� �����-���� ������  
     * @return array ������ ����������
     */
    public function getSubdomains($active = true) {
        global $DB;
        
        if($active) {
            $sql = "SELECT DISTINCT ON (ss.num) ss.* FROM seo_subdomain ss
                    INNER JOIN seo_bind sb ON ss.id = sb.subdomain_id AND sb.is_draft = 'f' ORDER BY ss.num ASC";
        } else {
            $sql = "SELECT * FROM seo_subdomain ORDER BY num ASC";
        }
        $ret = $DB->rows($sql);
        foreach($ret as $k=>$val) $result[$val['id']] = $val;
        return $result;
    }
    
    /**
     * ���������� �������/����������
     *
     * @example $section = array(
     *          "bind"             => 0, 
     *          "parent"           => 0, 
     *          "name_section"     => "����������������",  
     *          "meta_description" => "����", 
     *          "meta_keywords"    => "�����", 
     *          "content_before"   => "��",
     *          "content_after"    => "�����",
     *          "date_create"      => "NOW();"
     *    );
     * 
     * pos_num ����������� ����� ������� @see bI seo_section/pos_num()
     * 
     * @param array   $info         @see example
     * @param integer $subdomain    �� ��������� (�������)
     * @param integer $parent       �� �������
     */
    public function createSection($info, $subdomain = 0, $is_draft = 0) {
        global $DB;
        
        $fields = implode(",", array_keys($info));
        $sql = "INSERT INTO seo_sections ({$fields}) VALUES (?l) RETURNING id, pos_num";
        $section = $DB->row($sql, array_values($info));
        $id_section = $section['id'];
        $pos_section = $section['pos_num'];
        
        if($subdomain && $info['parent'] != 0) {
            $bind = $this->setBind($id_section, $subdomain, $is_draft?true:false);
        }
        
        return array('id'=>$id_section, 'pos'=>$pos_section);
    }
    
    /**
     * ��������� ������ ���������
     *
     * @param unknown_type $section_id
     * @param unknown_type $info
     * @param unknown_type $subdomain
     * @param unknown_type $is_draft
     */
    public function updateSection($section_id, $info, $bind=0, $subdomain = 0, $is_draft = 0) {
        global $DB;
        
        foreach($info as $field=>$value) {
            $set[] = "{$field} = '$value'";
        }
        $set_sql = implode(",", $set);
        
        $sql = "UPDATE seo_sections SET {$set_sql} WHERE id = {$section_id}";
        $DB->query($sql);
        
        if($subdomain && $info['parent'] != 0 && $bind) {
            $this->updateBind($bind, $section_id, $subdomain, $is_draft?true:false); 
        }
    }
    
    /**
     * ���������� ������� � �������� (����������) @see ������� � ���� aI seo_bind/section()
     *
     * @param integer $section     �� �������
     * @param integer $subdomain   �� ����������
     * @return inetger �� �����
     */
    public function setBind($section, $subdomain, $is_draft=false) {
        global $DB;
        $is_draft = $is_draft?"'t'":"'f'";
        $sql = "INSERT INTO seo_bind (section_id, subdomain_id, is_draft) VALUES(?, ?, $is_draft) RETURNING id";
        return $DB->val($sql, $section, $subdomain);   
    }
    
    /**
     * ���������� ����� ���������� � ����������
     *
     * @param integer $bind        �� �����
     * @param integer $section     �� ����������
     * @param integer $subdomain   �� ���������
     * @param integer $is_draft    ���� ��������������� ��������� ��� � �������� @todo �������� ����� ������ ���� � ������� seo_sections
     * @return boolean
     */
    public function updateBind($bind, $section, $subdomain, $is_draft=false) {
        global $DB;
        $is_draft = $is_draft?"'t'":"'f'";
        $sql = "UPDATE seo_bind SET section_id = ?i, subdomain_id = ?i, is_draft = {$is_draft} WHERE id = ?i";
        return $DB->query($sql, $section, $subdomain, $bind);  
    }
    
    /**
     * �������� ����� �� �� ���������� 
     * @todo ���� ����� �� ������������, �� ���� ����� ������ �������, ���� ������� ����� �� ��������� ������ ���������� ��������, ��. ��������� ��� ����� �� ����������
     *
     * @param integer $section  �� ����������
     * @return boolean
     */
    public function deleteBind($section) {
        global $DB;
        $sql = "DELETE FROM seo_bind WHERE section_id = ?i";
        return $DB->query($sql, $section);       
    }
    
    /**
     * ��������� ��������� �� �� � ����� ���� ��������� � ���� �� ��  ������ SQL ��� ������ ������ ������ ����� ���������
     *
     * @return string
     */
    public function getSubdomainInnerSQL() {
        global $DB;
        if($this->subdomain['id'] > 0 || $this->subdomain['id']==-1) {
            $draft_sql =  hasPermissions('seo')?"":"AND is_draft = 'f'";
            
            return $DB->parse("INNER JOIN seo_bind sb ON sb.section_id = ss.id AND sb.subdomain_id = ?i {$draft_sql}", $this->subdomain['id']);
        } else {
            $draft_sql =  hasPermissions('seo')?"":"AND is_draft = 'f'";
            return "INNER JOIN seo_bind sb ON sb.section_id = ss.id {$draft_sql}";
        }
        return "";
    }
    
    /**
     * �������� ������� (����������) �� ��� �� (���� ������� ������, ������������� ������� ��� ���������� � ���)
     *
     * @param integer $section_id  �� ������� (����������)
     * @return boolean
     */
    public function deleteSection($section_id) {
        global $DB;
        
        $sql = "DELETE FROM seo_sections WHERE id = ?i OR parent = ?i";
        return $DB->query($sql, $section_id, $section_id);
    }
    
    /**
     * �������������� ���������� ���������
     *
     * @param array   $data        ������ �������������� ��������� (�������)
     * @param integer $subdomain   �� ��������� (�������) ������� �����������
     * @return boolean
     */
    public function updateContentSubdomain($data, $subdomain) {
        global $DB;
        
        $sql = "UPDATE seo_subdomain SET meta_description = ?, meta_keywords = ?, content = ? WHERE id = ?i";
        return $DB->query($sql, $data['meta_description'], $data['meta_keywords'], $data['content'], $subdomain);
    }
    
    /**
     * ��������� ������� ����� ������������� ���������� ��������� (�������)
     *
     * @param object $objResponse @see class xajaxResponse
     */
    public function getLoadMainFormTemplate(& $objResponse, $is_save =false, $msgtext='') {
        $seo = $this;
        $subdomains = $seo->getSubdomains(false);
        ob_start();
        include($_SERVER['DOCUMENT_ROOT'] . '/catalog/admin/tpl.form-main.php');
        $html = ob_get_clean();
        
        $objResponse->assign("form_content", "innerHTML", $html);
        $objResponse->script("window.addEvent('domready', function() { var KeyWord = __key(1); KeyWord.bind(document.getElementById('kword_se'), kword, {bodybox:'body_1', maxlen:120}); initWysiwyg();});");
    }
    
    /**
     * ����� ������������� ��� ������������� ��������
     *
     * @param string $keys    ����� ������ ("���, ���, ���" - ����� ������ ���� meta_keywords �� ������� seo_sections, ���� ����� �������)
     * @param string|integer $city    ����� ������ (����� ���� ��� �� ������, ��� � �������� ���)
     * @return array|boolean ��������� �������, false ���� ������ �� �������
     */
    public function getDinamicContent($keys, $city) {
        global $DB;
        
        $exp = explode(",", $keys);
        foreach($exp as $k=>$v) $exp[$k] = "'".trim($v)."'";

        if($city!=-1 && $city!='���') {
            if(!is_numeric($city)) {
                require_once($_SERVER['DOCUMENT_ROOT']."/classes/city.php");
                $city = city::getCityId($city);
            }
        } else {
            $city = '';
        }
        
        $sql = "SELECT u.uid, u.photo, u.role, u.login, u.uname, u.usurname, u.is_pro, u.is_team, u.reg_date, u.info_for_reg, u.country, u.city, u.birthday, sm.completed_cnt as completed_sbr_cnt
                FROM words w 
                JOIN portf_word pw ON pw.wid = w.id 
                JOIN freelancer u ON u.uid = pw.uid ".($city ? "AND city = ?i" : "")." AND u.is_pro = true AND u.is_banned='0' 
                LEFT JOIN sbr_meta sm ON sm.user_id = u.uid
                WHERE w.name IN (".implode(", ", $exp).") 
                GROUP BY u.uid, u.photo, u.role, u.login, u.uname, u.usurname, u.is_pro, u.is_team, u.reg_date, u.info_for_reg, u.country, u.city, u.birthday, sm.completed_cnt
                ORDER BY RANDOM() LIMIT 9";

        $result = $DB->rows($sql, $city);

        if($result) return $result;
        return false;
    }
    
    public function getDinamicContentArticles($keys) {
        global $DB;
        
        $exp = explode(",", $keys);
        foreach($exp as $k=>$v) $exp[$k] = "'".trim($v)."'";
        
        $sql = "SELECT ar.* FROM (
                    SELECT DISTINCT ON(aw.article_id) article_id, a.title
                    FROM words w 
                    JOIN articles_word aw ON aw.word_id = w.id 
                    JOIN articles_new a ON a.id = aw.article_id
                    WHERE approved = true AND w.name IN (".implode(", ", $exp).") 
                ) ar ORDER BY RANDOM() LIMIT ".self::COUNT_DINAMIC_ARTICLES;
        
        $result = $DB->rows($sql, $city);
        if($result) return $result;
        return false;    
    }
    
    public function updatePosition($old, $new, $parent, $direct_id) {
        global $DB;
        
        if($old != $new) {
            if (!$new) {
                $sql = "UPDATE seo_sections SET pos_num = pos_num-1 WHERE parent = ?i AND pos_num > ?i AND direct_id = ?";
                $DB->query($sql, $parent, $old, $direct_id);
                return;
            } else if($new > $old) {
                $sql = "UPDATE seo_sections SET pos_num = pos_num-1 WHERE parent = ?i AND pos_num > ?i AND pos_num <= ?i AND direct_id = ?";
                $DB->query($sql, $parent, $old, $new, $direct_id);
            } else if($new < $old) {
                $sql = "UPDATE seo_sections SET pos_num = pos_num+1 WHERE parent = ?i AND pos_num >= ?i AND pos_num < ?i AND direct_id = ?";
                $DB->query($sql, $parent, $new, $old, $direct_id);
            } 
        }
    }
    
    public function updatePositionsByParent($parent, $pos, $direct_id) {
        global $DB;
        
        $sql = "UPDATE seo_sections SET pos_num = pos_num-1 WHERE parent = ?i AND pos_num > ?i AND direct_id = ?;";
        
        return $DB->query($sql, $parent, $pos, $direct_id);
    }
    
    public function getPositions($parent = 0, $direct_id) {
        global $DB;
        
        $sql = "SELECT COUNT(*) FROM seo_sections ss WHERE ss.parent = ?i AND ss.direct_id = ?";   
        
        return $DB->val($sql, (int) $parent, $direct_id); 
    }

    /**
     * ���������� �� ����������� �� ��� ������
     * 
     * @global DB $DB
     * @return type 
     */
    public function getDirectionByLink($str) {
        global $DB;
        
        $sql = "SELECT * FROM seo_direct WHERE name_section_link = ?";
        $res = $DB->row($sql, $str);
        
        return $res; 
    }

    /**
     * ������� ��� �����������
     * 
     * @global DB $DB
     * @return type 
     */
    public function getDirections($direct_id = null, $without_empty=false) {
        global $DB;

        if($without_empty) {
            $sql = "SELECT * FROM seo_direct WHERE id IN (SELECT DISTINCT(direct_id) FROM seo_sections WHERE parent<>0) ORDER BY id";
        } else {     
            $where = "";
            if (intval($direct_id)) {
                $where = " WHERE id = {$direct_id} ";
            }
            $sql = "SELECT * FROM seo_direct {$where} ORDER BY id";
        }
        return ($without_empty ? $DB->cache(300)->rows($sql) : $DB->rows($sql)); 
    }
    
    /**
     * ���������/������� ����� �����������
     * 
     * @global DB $DB
     * @param type $update
     * @param type $id
     * @return type 
     */
    public function saveDirection($update, $id = null) {
        global $DB;
        $ret = null;
        
        if (!$id) {
            $ret = $DB->insert('seo_direct', $update, 'id');
        } else {
            $ret = $DB->update('seo_direct', $update, 'id = ?', $id);
        }
        
        return $ret;
    }
    
    /**
     *
     * @global DB $DB
     * @param type $id
     * @return null 
     */
    public function deleteDirection($id) {
        global $DB;
        $ret = null;
        
        if (!$id) {
            return null;
        }
        
        $ret = $DB->query('DELETE FROM seo_direct WHERE id = ?', $id);
        
        return $ret;
    }

    public function getDirectionById($id) {
        global $DB;
        
        $sql = "SELECT * FROM seo_direct WHERE id = ?";
        $res = $DB->row($sql, $id);
        
        return $res; 
    }

    public function getDirectionIdFirst() {
        global $DB;
        
        $sql = "SELECT * FROM seo_direct ORDER BY id LIMIT 1";
        $res = $DB->row($sql);
        
        if (!$res) return null;
        
        return $res['id']; 
    }
}
?>
