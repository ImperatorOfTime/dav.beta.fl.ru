<?php

/**
 * ����� ��� ������ � ������ ��������� �������� ��� SEO �����.
 */

class freelancer_seo {
    /**
     * ID ������� ������-��������
     *
     * @var int
     */
    private $sLinkId    = null;
    
    /**
     * ������ �������� (id �� professions)
     *
     * @var int
     */
    private $sProfId    = null;
    
    /**
     * �������� �������������
     *
     * @var string
     */
    private $sTitle     = null;
    
    /**
     * ������� ������� �����������
     *
     * @var string
     */
    private $sCondition = null;
    
    /**
     * ��� ���������� ������
     *
     * @var int
     */
    private $nSide      = null;
    
    /**
     * ����������� ������� �������
     * 
     * @var array
     */
    private $aGet       = array();
    
    /**
     * ��������� ����� SQL ������� 
     * 
     * @var array
     */
    private $aSQL       = array();
    
    /**
     * ID �������� ��� �������������� ����� ��� SQL �������
     *
     * @var string
     */
    private $sSQLProfId = 0;
    
    /**
     * ����� ����� ������� � ��������
     */
    const CATALOG_MEM_LIFE = 1800;
    
    /**
     * ������������ ��������� ����������� � ��������� �������
     */
    const RANDOM_LIMIT  = 1000;
    
    /**
     * �����������
     *
     * @param $link_id ID ������� ������-��������
     */
    function __construct( $link_id = null ) {
        $this->fseoInit( $link_id );
    }
    
    /**
     * �������������
     *
     * @param $link_id ID ������� ������-��������
     */
    function fseoInit( $link_id = null ) {
        global $DB;
        
        $this->aGet = array();
        $this->sSQLProfId = 0;
        
        $aLink = $DB->row( 'SELECT * FROM freelancer_seo WHERE id=?i', $link_id );
        
        if ( $aLink ) {
            $this->sLinkId    = $aLink['id'];
            $this->sProfId    = $aLink['prof_id'];
            $this->sTitle     = $aLink['title'];
            $this->sCondition = $aLink['condition'];
            $this->nSide      = $aLink['side'];
            
        	$aParts = explode('&', $aLink['condition']);
        	
        	foreach ( $aParts as $sOne ) {
        		list( $sKey, $sVal ) = explode( '=', $sOne );
        		
        		if ( $sKey == 'additional_prof' || $sKey == 'main_prof' ) {
        			$this->aGet[ $sKey ] = professions::GetProfessionOrigin( $sVal );
        		}
        		else {
                    $this->aGet[ $sKey ] = explode( ',', $sVal );
        		}
        	}
        	
        	if ( !empty($this->aGet['main_prof']) || !empty($this->aGet['additional_prof']) ) {
                $this->sSQLProfId = ( !empty($this->aGet['main_prof']) ) ? $this->aGet['main_prof'] : $this->aGet['additional_prof'];
            }
        }
    }
    
    /**
     * ���������� ���� ������ ��� ��������������� ������� �����������.
     * 
     * @param  int $nSide ��� ���������� ������: 0 - � ����� �������, 1 - ��� ��������
     * @param  int $sProfId �����������. ������ �������� (id �� professions). ���� �� ������� - �� $this->sProfId
     * @return array
     */
    function fseoGetLinksBlock( $nSide = 0, $sProfId = null ) {
        global $DB;
        
        $aProfId = ( $sProfId ) ? professions::GetMirroredProfs($sProfId) : array( $this->sProfId );
        $sQuery  = 'SELECT id, title FROM freelancer_seo WHERE side = ?i AND prof_id IN (?l)';
        $aReturn = ( is_array($aProfId) && count($aProfId) ) ? $DB->rows( $sQuery, $nSide, $aProfId ) : array();
        
        return $aReturn;
    }
    
    /**
     * ���������� �������� ������-�������� �����������.
     * 
     * @param  int count ���������� ����� ����������� � ������ ������� ��������.
     * @param  int size ���������� ����������� �� ������ �������� ��������.
     * @param  array works ������, ��������������� ��. �����������, ���������� ������ �� ���� ������ ����� ������� ���������� � ������ �������.
     * @param  int limit ������� ����������� �� ����� ��������.
     * @param  int offset OFFSET.
     * @param  string order ��� ����������
     * @param  int direction ������� ����������. 0 -- �� ���������, �� 0 -- �� ������������.
     * @return array
     */
    function fseoGetCatalog( &$count, &$size, &$works, $limit, $offset, $order = "general", $direction = 0 ) {
        global $DB;
        
        // ������ ������
        $this->fseoSetSelect();
        $this->fseoSetFrom();
        $this->fseoSetJoin();
        $this->fseoSetWhere();
        $this->fseoSetOrderBy( $order, $direction );
        
        // �������� ������ �����������
        $sQuery = 'SELECT ' . implode(', ', $this->aSQL['select']) 
            . ' FROM ' . $this->aSQL['from'] . ( $this->aSQL['join'] ? ' ' . implode(' ', $this->aSQL['join']) : '' ) 
            . ' WHERE ' . implode(' AND ', $this->aSQL['where']) . ' ORDER BY ' . implode(', ', $this->aSQL['order_by']) 
            . ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        
        $memBuff = new memBuff();
        $frls    = $memBuff->getSql( $error, $sQuery, self::CATALOG_MEM_LIFE );
        
        if ( $error || !$frls ) {
            return NULL;
        }
        
        // �������� ����� ���������� �����������
        // ��� �������� ���������� ����������� �� �����
        unset( $this->aSQL['join']['portf_choise'] );
        unset( $this->aSQL['join']['portf_professions'] );
        
        $sQuery = 'SELECT COUNT(s.uid) AS count FROM ' . $this->aSQL['from'] 
            . ( $this->aSQL['join'] ? ' ' . implode(' ', $this->aSQL['join']) : '' ) 
            . ' WHERE ' . implode(' AND ', $this->aSQL['where']);
        
        $aCount = $memBuff->getSql( $error, $sQuery, self::CATALOG_MEM_LIFE );
        $count  = $aCount[0]['count'];
        $size   = sizeof( $frls );
        
        // �������� ������ �����������
        foreach ( $frls as $row ) {
            $frl_ids[] = $row['uid'];
        }
        
        $sQuery = 'SELECT p.id, p.user_id, p.name, p.descr, p.pict, p.prev_pict, p.show_preview, p.norder, p.prev_type, p.is_video 
            FROM portfolio p 
            INNER JOIN portf_choise pc ON pc.user_id = p.user_id AND pc.prof_id = p.prof_id 
            '.( $this->sSQLProfId ? '' : 'INNER JOIN freelancer f ON f.uid = p.user_id' ).' 
            WHERE p.user_id IN ('.implode(', ', $frl_ids).') 
                AND p.prof_id = '.( $this->sSQLProfId ? $this->sSQLProfId : 'f.spec_orig' ).' 
                AND p.first3 = true 
            ORDER BY p.user_id, p.norder';
        
        $ret  = $memBuff->getSql( $error, $sQuery, self::CATALOG_MEM_LIFE );
        
        if ( $ret ) {
            foreach ( $ret as $row ) {
                $works[$row['user_id']][] = $row;
            }
        }
        
        return $frls;
    }
    
    /**
     * �������� SELECT ����� SQL ������� �� ������� ������� � $this->aGet
     */
    function fseoSetSelect() {
        // ���� ������� ���� ������
        $this->aSQL['select'] = array( 's.is_profi, s.is_pro AS payed, s.is_pro_test, s.uname, s.usurname, s.login, s.uid, s.photo, 
            s.spec, s.status_type, s.cost_month, s.cost_type_month, s.site, s.icq, s.phone, s.ljuser, s.country, s.city, 
            s.last_time, s.boss_rate, s.tabs, s.spec_orig, uc.ops_frl_plus AS sg, uc.ops_frl_minus AS sl, uc.ops_frl_null AS se, 
            zin(uc.ops_frl_plus) - zin(uc.ops_frl_minus) AS ssum, rating_get(s.rating, s.is_pro, s.is_verify, s.is_profi) AS rating, 
            pc.cost_from, pc.cost_type, pc.cost_1000, p.name AS profname, p.is_text' 
        );
        
        if ( $this->sSQLProfId ) {
            // ����� ���� �������� ��� �������������� ����� 
        	$this->aSQL['select'][] = '(s.spec_orig = ' . $this->sSQLProfId . ') AS its_his_main_spec, 
        	   pc.cost_hour, pc.cost_type_hour';
        }
        else {
            // ����� ��� ������ ������� ����
            $this->aSQL['select'][] = 's.cost_hour, s.cost_type_hour';
        }
    }
    
    /**
     * �������� FROM ����� SQL ������� �� ������� ������� � $this->aGet
     */
    function fseoSetFrom() {
        global $DB;
        
        $sRndSelect   = '';
        $sRndRestrict = '';
        
        // ���� ������� ������ ���� ���������, �� ����� ��������� �����
        if ( !empty($this->aGet['random']) ) {
        	$memBuff = new memBuff();
        	
        	if ( !$nRnd = $memBuff->get('fseo_' . $this->sLinkId . '_rnd') ) {
        	    $nRnd = mt_rand(1, 1000);
        	    
        		$memBuff->set( 'fseo_' . $this->sLinkId . '_rnd' , $nRnd, self::CATALOG_MEM_LIFE );
        	}
        	
        	$sRndSelect   = ", fu.uid % $nRnd AS rnd_num";
        	$sRndRestrict = 'ORDER BY rnd_num LIMIT ' . self::RANDOM_LIMIT;
        }
        
        if ( !empty($this->aGet['main_prof']) || !empty($this->aGet['additional_prof']) ) {
            // ����� ���� �������� ��� �������������� ����� 
            $aSQL = array();
            
            if ( !empty($this->aGet['main_prof']) ) {
                // ����� ���� �������� ����� 
                $aSQL['main_choise'] = 'SELECT *' . $sRndSelect . ' FROM fu WHERE spec_orig = ' . $this->sSQLProfId;
            }
            elseif ( !empty($this->aGet['additional_prof']) ) {
                // ����� ���� �������������� ����� 
                $aSQL['add_choise']  = 'SELECT fu.*' . $sRndSelect . ' FROM fu 
                    INNER JOIN spec_add_choise sp ON sp.user_id = fu.uid 
                    AND sp.prof_id = ' . $this->sSQLProfId . ' WHERE fu.is_pro = true';
                
                $aSQL['paid_choise'] = 'SELECT fu.*' . $sRndSelect . ' FROM fu 
                    INNER JOIN spec_paid_choise pc ON pc.user_id = fu.uid 
                    AND pc.prof_id  = ' . $this->sSQLProfId . ' AND pc.paid_to > NOW()';
            }
            
            $sBegin = ( $sRndRestrict ) ? '((' : '(';
            $sEnd   = ( $sRndRestrict ) ? ") $sRndRestrict ) AS s " : ' ) AS s ';
            
            $this->aSQL['from'] = $sBegin . implode( ' UNION ALL ' , $aSQL ) . $sEnd;
        }
        else {
            // ����� ��� ������ ������� ����
            $this->aSQL['from'] = ( $sRndSelect ) ? '(SELECT *' . $sRndSelect . ' FROM fu ' . $sRndRestrict . ') s' : 'fu s';
        }
    }
    
    /**
     * �������� JOIN ����� SQL ������� �� ������� ������� � $this->aGet
     */
    function fseoSetJoin() {
        if ( $this->sSQLProfId ) {
            // ����� ���� �������� ��� �������������� �����
            $this->aSQL['join']['portf_choise'] = 'LEFT JOIN portf_choise pc ON pc.prof_id = ' . $this->sSQLProfId 
                . ' AND pc.user_id = s.uid';
            
            $this->aSQL['join']['portf_professions'] = 'INNER JOIN professions p ON p.id = ' . $this->sSQLProfId;
        }
        else {
            // ����� ��� ������ ������� ����
            $this->aSQL['join']['all_portf'] = 'INNER JOIN portf_choise pc ON pc.prof_id = s.spec_orig AND pc.user_id = s.uid';
            $this->aSQL['join']['all_professions'] = 'INNER JOIN professions p ON p.id = s.spec';
        }
        
        $this->aSQL['join']['users_counters'] = 'LEFT JOIN users_counters uc ON uc.user_id = s.uid';
    }
    
    /**
     * �������� WHERE ����� SQL ������� �� ������� ������� � $this->aGet
     */
    function fseoSetWhere() {
        $this->aSQL['where'] = array( "s.is_banned = '0' AND s.cat_show = true" );
        
        // ������ � ������
        if ( $sVal = $this->aGet['country'] ) {
            $this->aSQL['where'][] = 's.country IN (' . implode(',', $sVal) . ')';
        }
            
        if ( $sVal = $this->aGet['city'] ) {
            $this->aSQL['where'][] = 's.city IN (' . implode(',', $sVal) . ')';
        }
        
        // �� ����������� ������� � �����
        if ( $sVal = $this->aGet['except_country'] ) {
            $this->aSQL['where'][] = 's.country NOT IN (' . implode(',', $sVal) . ')';
        }
            
        if ( $sVal = $this->aGet['except_city'] ) {
            $this->aSQL['where'][] = 's.city NOT IN (' . implode(',', $sVal) . ')';
        }
    }
    
    /**
     * �������� ORDER BY ����� SQL ������� �� ������� ������� � $this->aGet
     * 
     * @param  string order ��� ����������
     * @param  int direction ������� ����������: 0 - �� ���������, �� 0 - �� ������������.
     */
    function fseoSetOrderBy( $order = "general", $direction = 0 ) {
        global $project_exRates;
        
        $dir_sql = ( !$direction ? 'DESC' : 'ASC' );
        
        $this->aSQL['order_by'] = array( 's.is_pro DESC' );
        
        // �������� �����: � ���� ���� - ��� ����
        if ( $sVal = $this->aGet['word'] ) {
            $this->aSQL['order_by'][] = "(s.spec_text LIKE '%{$sVal[0]}%') DESC";
        }
        
        $ord_spec = ( $this->sSQLProfId ) ? "(s.spec_orig = '{$this->sSQLProfId}') DESC, " : '';
        
        switch($order)
        {
            case "opinions":
                $this->aSQL['order_by'][] = "ssum {$dir_sql}, rating {$dir_sql}";
                break;
            case "cost_hour":
                $this->aSQL['order_by'][] = "{$ord_spec} COALESCE(pc.cost_hour,0)=0, cost_fm {$dir_sql}, rating {$dir_sql}";
                $orderCf = 'pc.cost_hour';
                $orderCt = 'pc.cost_type_hour';
                break;
            case "cost_month":
                $this->aSQL['order_by'][] = "{$ord_spec} COALESCE(s.cost_month,0)=0, cost_fm {$dir_sql}, rating {$dir_sql}";
                $orderCf = 's.cost_month';
                $orderCt = 's.cost_type_month';
                break;
            case "cost_proj":
                $this->aSQL['order_by'][] = "{$ord_spec} COALESCE(pc.cost_from,0)=0, cost_fm {$dir_sql}, rating {$dir_sql}";
                $orderCf = 'pc.cost_from';
                $orderCt = 'pc.cost_type';
                break;
            case "cost_1000":
                $this->aSQL['order_by'][] = "{$ord_spec} COALESCE(pc.cost_1000,0)=0, cost_fm {$dir_sql}, rating {$dir_sql}";
                $orderCf = 'pc.cost_1000';
                $orderCt = 'pc.cost_type';
                break;
            case "general":
            default:
                $this->aSQL['order_by'][] = "{$ord_spec} rating {$dir_sql}";
                break;
        }

        if ( $orderCf && $orderCt ) {
            $this->aSQL['select'][] = "CASE 
                WHEN COALESCE({$orderCt},0) = 0 THEN {$orderCf} * {$project_exRates[24]}
                WHEN {$orderCt} = 1 THEN {$orderCf} * {$project_exRates[34]}
                WHEN {$orderCt} = 2 THEN {$orderCf} * {$project_exRates[44]}
                ELSE {$orderCf}
                END as cost_fm ";
        }
        
        $this->aSQL['order_by'][] = 's.uid DESC';
    }
}

?>