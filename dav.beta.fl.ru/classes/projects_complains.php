<?
/**
 * ����� ��� ������ � �������� �� �������
 */
class projects_complains
{
    
    /**
     * ���������� ������ � ������ �����
     * @param boolean $moder - true/false - ��� ������� ��� ��� �������������
     * @param boolean $useCache ������������ ���
     */
    public static function getTypes ($moder = null, $useCache = true) {
        global $DB;
        $sql = '
            SELECT id, name, pos,
            (CASE WHEN moder THEN 1 ELSE 0 END) as moder, name,
            (CASE WHEN textarea THEN 1 ELSE 0 END) as textarea,
            (CASE WHEN required THEN 1 ELSE 0 END) as required,
            notkind
            FROM projects_complains_types
            WHERE deleted IS NOT TRUE';
        if ($moder === false) {
            $sql .= ' AND moder = false';
        } elseif ($moder === true) {
            $sql .= ' AND moder = true';
        }
        $sql .= ' ORDER BY pos ASC';
        if ($useCache) {
            $rows = $DB->cache(600)->rows($sql);
        } else {
            $rows = $DB->rows($sql);
        }
        return $rows;
    }
    
    /**
     * ��������� ������ ����� ����� �����
     * @global type $DB
     * @param array $add ����� ������
     * @param array $edit ������������� ������
     * @param array $delete ��������� ������
     * @param boolean $moder ������ ��� ���������� (true) ��� ��� ������������ (false)
     */
    public static function updateTypes ($add, $edit, $delete, $moder) {
        global $DB;
        $sql = '';
        
        $sqlAdd = 'INSERT INTO projects_complains_types (moder, name, textarea, required, pos) VALUES (?b, ?, ?b, ?b, ?i);';
        foreach($add as $addType) {
            $sql .= $DB->parse($sqlAdd, $moder, $addType['name'], $addType['textarea'], $addType['required'], $addType['pos']);
        }
        
        $sqlEdit = 'UPDATE projects_complains_types SET name = ?, textarea = ?b, required = ?b, pos = ?i WHERE id = ?i;';
        foreach($edit as $editType) {
            $sql .= $DB->parse($sqlEdit, $editType['name'], $editType['textarea'], $editType['required'], $editType['pos'], $editType['id']);
        }
        
        $sqlDelete = 'UPDATE projects_complains_types SET deleted = true WHERE id = ?i;';
        foreach($delete as $deleteType) {
            $sql .= $DB->parse($sqlDelete, $deleteType['id']);
        }
        
        $DB->query($sql);
    }
    
    
    /**
     * ���������� �������� ���� ��������� �� ID
     * @param  int $complainTypeID ID ���� ���������
     * @param  bool $deleted �������� ��� ������ ��� ���������, ���� �� ��������� (������ ������������ � ����� (���� ��� ����� ������))
     * @return string
     */
    function GetComplainType($complainTypeID, $deleted = false) {
        if (!$complainTypeID) {
            return false;
        }
        
        global $DB;
        $row = $DB->row('SELECT name, deleted FROM projects_complains_types WHERE id = ?i', $complainTypeID);
        $name = $row['name'];
        if ($row['deleted'] === 't' && $deleted) {
            $name .= ' (���� ��� ����� ������)';
        }
        return $name;
    }
    
    /**
     * ���������� �������������� ���� ��������� ���������� �� ID
     * @param  int $complainTypeID ID ���� ���������
     * @return boolean
     */
    function isComplainTypeModer($complainTypeID) {
        if (!$complainTypeID) {
            return false;
        }
        global $DB;
        $moder = $DB->cache(1800)->val('SELECT moder FROM projects_complains_types WHERE id = ?i', $complainTypeID);
        return $moder == 't';
    }
    
    /**
     * ���������� ���������� �� �������
     * @param  string $by    - ��� ����������� ����������
     * @param  array $bounds - ������ ������ ��� ���������� ���������� � ������ ����������� �� ������� ������� (����� $by == 'cost')
     * @return array         - ���������, ���������� ������ (������) ��� $by == 'from', ������� ��� / �� ���, 
     *                         ��������� ���c�� ����� ��� $by == 'category', ��� 10 ���������
     *                         � ������ ����������� �� ������� ������� - ��������� (���� ��������� � ��������� �������)
     */
    public static function GetComplainsStats($by = 'from', $bounds = array()) {
        global $DB;
        switch ($by) {
            case 'from': {
                // ����� ���������� � �������� "�� ��� / �� ���"
                $sql = 'SELECT SUM(CAST(u.is_pro AS INT)) AS pro, SUM(1-CAST(u.is_pro AS INT)) AS nopro
                    FROM projects_complains_counter c
                    INNER JOIN projects p ON p.id  = c.project_id
                    INNER JOIN users u    ON u.uid = p.user_id';
                $complains = $DB->row($sql);
                $complains['sum'] = array_sum($complains);
                break;
            }
            case 'category': {
                // �� ����������
                $sql = 'SELECT
                    g.id AS cat_id,
                    g.name,
                    COUNT(c.id) AS cnt 
                FROM projects_complains_counter c
                INNER JOIN projects        p ON p.id         = c.project_id
                INNER JOIN project_to_spec s ON s.project_id = c.project_id
                INNER JOIN prof_group      g ON g.id         = s.category_id
                GROUP BY cat_id
                ORDER BY cnt DESC
                LIMIT 10';
                $complains = $DB->rows($sql);
                break;
            }
            case 'cost': {
                if(!$bounds) return false;
                sort($bounds);
                // ������� �� �������
                // �������������� ������ ����������
                $bcnt = count($bounds);
                $diaps = array();
                for ($i=0; $i<=$bcnt; $i++){
                    if (isset($bounds[($i-1)])) $diaps[$i]['start']   = $bounds[($i-1)];
                    if (isset($bounds[$i]))     $diaps[$i]['end']     = $bounds[$i];
                }
                // .. ����� � sql - ����
                $sql = '';
                $complains_pcost = array();
                for ($i=0; $i<=$bcnt; $i++){
                    if (!isset($diaps[$i]['start'])) {
                        $diaps[$i]['html'] = '&lt; '.$diaps[$i]['end'];
                        $sql .= 'WHEN p.cost < '.$diaps[$i]['end'].  ' THEN '.$i."\n";
                    } elseif (!isset($diaps[$i]['end'])) {
                        $diaps[$i]['html'] = '&gt; '.$diaps[$i]['start'];
                        $sql .= 'WHEN p.cost > '.$diaps[$i]['start'].' THEN '.$i."\n";
                    } else {
                        $diaps[$i]['html'] = $diaps[$i]['start'].' &mdash; '.$diaps[$i]['end'];
                        $sql .= 'WHEN p.cost BETWEEN '.$diaps[$i]['start'].' AND '.$diaps[$i]['end'].' THEN '.$i."\n";
                    }
                    // ������ � ������ ������ �����������������
                    $complains_pcost[$i] = 0;
                }

                $sql = 'SELECT 
                    CASE 
                        WHEN p.cost = 0 THEN -1
                        '.$sql.'
                    END AS diap,
                    COUNT(c.id) AS cnt 
                FROM projects_complains_counter c
                INNER JOIN projects p ON p.id  = c.project_id
                INNER JOIN users u    ON u.uid = p.user_id
                GROUP BY diap';
                $result = $DB->rows($sql);

                foreach ($result as $val) {
                    $complains_pcost[($val['diap'] == -1 ? 'd' : $val['diap'])] = $val['cnt'];
                }
                $complains = array('diaps' => $diaps, 'result' => $complains_pcost);
                break;
            }
            default:
                $complains = false;
                break;
        }
        return $complains;
    }
    
}

?>
