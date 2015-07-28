<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��� ������ � �������� ���������� � �������
 *
 */
class stats
{
    // � ����� ���� ������� ����������� �������� � ��������. ������������ � self::getRegStats()
    const NEW_PHONE_REG_MOMENT = '2013-08-14';
    
    /**
     *  ������� ��������� ���������� ����������� ��� �������
     *
     */
    function writeGeneralStat() {
        global $DB;
        $date  = date( "Y-m-d", time()-24*60*60 );
        $sdate = $date . " 00:00:01";
        $edate = $date . " 23:59:59";

        $stat = array( 'date' => $date );

        // ���-�� ������������������ �������������
        $sql = "SELECT COUNT(*) as u_reg FROM users WHERE reg_date = ?";
        $stat['u_reg'] = $DB->val( $sql, $date );

        // ���-�� ������������� � PRO
        $sql = "SELECT COUNT(*) as u_pro FROM users WHERE is_pro='t'";
        $stat['u_pro'] = $DB->val( $sql );

        // ���-�� �����������
        $sql = "SELECT COUNT(*) as frl FROM freelancer";
        $stat['frl'] = $DB->val( $sql );

        // ���-�� �������������
        $sql = "SELECT COUNT(*) as emp FROM employer";
        $stat['emp'] = $DB->val( $sql );

        // ���-�� ����� �����������
        $sql = "SELECT COUNT(*) as l_frl FROM freelancer WHERE last_time::date+'30day'::interval> ?";
        $stat['l_frl'] = $DB->val( $sql, $sdate );

        // ���-�� ����� �������������
        $sql = "SELECT COUNT(*) as l_emp FROM employer WHERE last_time::date+'30day'::interval> ?";
        $stat['l_emp'] = $DB->val( $sql, $sdate );

        // ���-�� ������������ �����������
        $sql = "SELECT COUNT(users_ban.id) as b_frl FROM users_ban INNER JOIN users ON users_ban.uid=users.uid 
                WHERE (users.role&'100000')='000000' AND date(\"from\")=date(NOW()-'1 day'::interval)";
        $stat['b_frl'] = $DB->val( $sql );

        // ���-�� ������������ �������������
        $sql = "SELECT COUNT(users_ban.id) as b_emp FROM users_ban INNER JOIN users ON users_ban.uid=users.uid 
                WHERE (users.role&'100000')='100000' AND date(\"from\")=date(NOW()-'1 day'::interval)";
        $stat['b_emp'] = $DB->val( $sql );

        // ����� ����������
        $DB->insert( 'stat_data', $stat );
        
        $stat = array( 'date' => $date );
        $sql  = "SELECT DISTINCT p.id, p.name as profname, p.n_order as n_order FROM professions p 
                 WHERE p.id <> 0 AND p.prof_group <> 0 ORDER BY  p.n_order";
        $qprof = $DB->rows( $sql );
        
        if ( $qprof && is_array($qprof) && count($qprof) ) {
        	foreach ( $qprof as $prof ) {
                $stat['spec'] = $prof['id'];
                
                // ����� ��������
                $sql = "SELECT COUNT(*) as main FROM freelancer WHERE spec = ?;";
                $stat['main'] = $DB->val( $sql, $prof['id'] );
    
                // ����� ��������������
                $sql = "SELECT COUNT(*) as add FROM spec_add_choise WHERE prof_id = ?;";
                $stat['add'] = $DB->val( $sql, $prof['id'] );
    
                // ����� ��������   
                $sql = "SELECT COUNT(*) as l_main FROM freelancer WHERE spec = ? AND last_time::date+'30day'::interval> ?;";
                $stat['l_main'] = $DB->val( $sql, $prof['id'], $sdate );
    
                // ����� ��������������
                $sql = "SELECT COUNT(spec.user_id) as l_add FROM spec_add_choise as spec, freelancer as f 
                        WHERE prof_id = ? AND spec.user_id=f.uid AND f.last_time::date+'30day'::interval> ?";
                $stat['l_add'] = $DB->val( $sql, $prof['id'], $sdate );
    
                // ����� ����������
                $DB->insert( 'stat_data_by_spec', $stat );
            }
        }
        
        $only_active = FALSE;
        $sql = 'SELECT g.name, g.id FROM prof_group g'.($only_active ? ' WHERE g.id > 0 AND EXISTS (SELECT 1 FROM professions p WHERE p.prof_group = g.id AND p.id > 0)' : '').' ORDER BY g.n_order';
        $qprof = $DB->rows( $sql );
        
        if ( $qprof && is_array($qprof) && count($qprof) ) {
            foreach ( $qprof as $prof ) {
                if ( $prof['id'] > 0 ) {
                    $stat = array();
                    
                    // �������
                    $sql = "SELECT COUNT(*) as prj FROM projects WHERE category=".$prof['id']." 
                            AND post_date>'".$sdate.".000000' AND post_date<'".$edate.".000000'";
                    $stat['prj'] = $DB->val( $sql );
    
                    // ������ �� �������
                    $sql = "SELECT COUNT(o.id) as prj_offers FROM projects_offers as o, projects as p 
                            WHERE p.category=".$prof['id']." AND p.id=o.project_id AND o.post_date>'".$sdate.".000000' 
                            AND o.post_date<'".$edate.".000000';";
                    $stat['prj_offers'] = $DB->val( $sql );
    
                    //����� ����������
                    $sql = "SELECT * FROM stat_data_by_spec WHERE date = ? AND spec = ?";
                    $s   = $DB->query( $sql, $date, $prof['id'] );
                    
                    if(pg_num_rows($s)>0) {
                        $DB->update( 'stat_data_by_spec', $stat, "date = ? AND spec = ?", $date, $prof['id'] );
                    } else {
                        $stat['date'] = $date;
                        $stat['spec'] = $prof['id'];
                        
                        $DB->insert( 'stat_data_by_spec', $stat );
                    }
                }
            }
        }
    }
    
    /**
     *  �������� ���������� �� ������������ � ��������� � ��������
     *  @param string $fromDate - � ����� ...
     *  @param string $toDate - �� ����� ����
     *  @return array int $result['reg']['all'] - ����� ����������� �� ������
     *                    $result['reg']['all_time'] - ����� ����������� �� �� ����� == ���������� �������������
     *                    $result['reg']['frl'] - ������������������ ����������� �� ������
     *                    $result['reg']['emp'] - ... �������������
     *                    *** ������ - �������� � ��������� ***
     *                    $result['phone']['new'] - ����� �������� ( == ����� ���������, ������� � NEW_PHONE_REG_MOMENT)
     *                    $result['phone']['old'] - ������ ���������, ����������� � �������� �� ������ (�����. �� NEW_PHONE_REG_MOMENT)
     *                    $result['phone']['all'] - ����� �������� �� ������
     *                    $result['phone']['all_time'] - ����� �������� �� �� �����.
     */    
    public static function getRegStats($fromDate, $toDate){
        global $DB;
        if (!isset($fromDate)) $fromDate = date('Y-m-d');
        if (!isset($toDate)) $toDate = $fromDate;
        
        $res = array(); // ������������� ������
        $result = array();
        
        // ����������� �� ������
        $sql = "SELECT 
            sum((role & B'100000' = B'100000')::integer) AS emp,
            sum((role & B'100000' = B'000000')::integer) AS frl,
            sum((reg_date >= ?)::integer)                AS phone_new
            FROM users
            WHERE reg_date >= ? AND reg_date - '1 day'::interval < ?";
        $res['period'] = $DB->row($sql, self::NEW_PHONE_REG_MOMENT, $fromDate, $toDate);
        
        // ���������, ����� �����, �������������� ������
        foreach($res['period'] as $key => $val) $val || $res['period'][$key] = 0;
        
        $result['reg'] = $res['period'];
        
        // ����� �� ������
        $result['reg']['all'] = $res['period']['frl'] + $res['period']['emp'];
        
        // ����������� �� �� �����
        $sql = "SELECT COUNT(uid) as all_time FROM users";
        $res['all'] = $DB->cache(600)->row($sql, self::NEW_PHONE_REG_MOMENT);
        
        $result['reg']['all_time'] = $res['all']['all_time'];
        
        // ����� - �������� ���������. ����� ��������
        $result['phone']['new'] = $res['period']['phone_new'];
        
        // ������ ��������
        $sql = "SELECT 
                count(*) AS all_time,
                sum((
                    activate_phone_time::date >= ? 
                    AND activate_phone_time::date - '1 day'::interval < ?
                )::integer) AS period
            FROM sbr_reqv 
            WHERE is_activate_mob = true";
        $res['from_sbr'] = $DB->row($sql, $fromDate, $toDate);
        $result['phone']['old'] = $res['from_sbr']['period'] ? $res['from_sbr']['period'] : 0;
        
        // ��� �������� �� ������                               
        $result['phone']['all'] = $result['phone']['new'] + $result['phone']['old'];
        
        // �� �� �����
        $result['phone']['all_time'] = $res['from_sbr']['all_time'];
        
        return $result;
    }
}
