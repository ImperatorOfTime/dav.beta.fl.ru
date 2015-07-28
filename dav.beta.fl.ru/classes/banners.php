<?
/**
 * ���������� ���� � ��������� ���������
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * ����� ��������� ��������
 *
 */
class banners {

    const ITEMS_ON_PAGE = 20;
    
    const MEM_SERVER_KEY = 'memcachedBannersServers';
    const MEM_GROUP_KEY = 'banners.getCachedBanners.group_key';
    const MEM_LIFE      = 90; // ����� �� ����� ��-�� �������� ������� �� ����/�������.

	protected $DB = NULL;

    /**
     * ����������� ������
     */
	function __construct() {
		$this->DB = new DB('banner');
	}

	/**
	 * �������� ������ �������/��������
	 * 
	 * @param string $name	�������� ��������
	 * @param string $adr	�����
	 * @param string $phone	�������
	 * @param string $cont	���������� ���
	 * @param string $email	E-mail �������
	 * @param string $notes	������� � �������
	 * @return string	�� ������� ���� C�������� �� ������
	 */
	function AddCompany($name, $adr, $phone, $cont, $email, $notes){
		$res = $this->DB->insert('ban_company', array(
			'name'  => $name,
			'adr'   => $adr,
			'phone' => $phone,
			'cont'  => $cont,
			'email' => $email,
			'notes' => $notes
		), 'id');
		return $res;
	}
	
	/**
	 * ���������� ������ ���� ��������
	 *
	 * @param string $error	���������� ��������� �� ������
	 * @return array ������ �������� [[�������������, ��������],...]
	 */
	function GetCompanies(&$error){
		$rows = $this->DB->rows("SELECT id, name FROM ban_company ORDER BY name");
		$error = $this->DB->error;
		return $rows;
	}
	
	/**
	 * �������� ���������� �� ������������� ��������
	 *
	 * @param integer $id	 �� ��������
	 * @param string  $error ���������� ��������� �� ������, ���� ��� ����
	 * @return array ���������� � ��������	[�������������, ��������, �����, �������, �������, �-����, �������]
	 */
	function GetCompany($id, &$error){
		$row = $this->DB->row("SELECT * FROM ban_company WHERE id = ?", $id);
		$error = $this->DB->error;
		return $row;
	}
	
	/**
	 * ������������� ���������� � ��������	
	 *  * 
	 * @param integer $id 	 �� ��������
	 * @param string  $name	 �������� ��������
	 * @param string  $adr	 �����
	 * @param string  $phone �������
	 * @param string  $cont	 ���������� ���
	 * @param string  $email E-mail �������
	 * @param string  $notes �������
	 * @return string $error ��������� �� ������
	 */
	function EditCompany($id, $name, $adr, $phone, $cont, $email, $notes){
		$this->DB->update('ban_company', array(
			'name'  => $name,
			'adr'   => $adr,
			'phone' => $phone,
			'cont'  => $cont,
			'email' => $email,
			'notes' => $notes
		), "id = ?", $id);
		return $this->DB->error;
	}
	
	/**
	 * �������� ����� ������
	 * 
	 * @param string  $name				�������� �������
	 * @param string  $link				URL c����� �������
	 * @param integer $company_id		�� ��������
	 * @param string  $filename			��� ����� �������
	 * @param string  $from_date		C ������ ��� ����������
	 * @param string  $to_date			�� ����� ���� ����������
	 * @param integer $banner_type		��� ������� (1 - ������� (600�90(60)), 2 - ������� (240�400), 3 - ����� (183� ), 4 - ������� (300�90(60)))
	 * @param bit(3)  $country			������ (100 - ������, 010 - �������, 001- ������)
	 * @param bit(4)  $city				����� (1000 - ���, 0100 - �����, 0010 - ����, 0001 - ������)
	 * @param bit(2)  $pro				���������� ��� ��� (10 - ������ ���, 01 - ������ �� ���)
	 * @param bit(3)  $role				���������� ������ ��� ���������� ����� (100 - ���������, 010 - ������������, 001 - ������)
	 * @param string  $error			���������� ��������� �� ������
	 * @param integer $show_cntd        ���������� �������		
	 * @param string  $pixel       		���. HTML(���� ��� �������� � �.�.)
	 * @param string  $code        		��� ������� (���� ������ �������� �� ��������� �����)
	 * @param boolean $for_money   		��� ������ (false - ���������, true - �� ������)
	 * @param integer $rf				RF (������ ���� ��� 0 - ������������)
	 * @param string  $stat_fname       ��� ������������ ����� ������
	 * @param integer $perday			���������� ������� � ����
	 * @param boolean $static			����������� ����� ��� ���(true - ��, false - ���)
	 * @param boolean $uniq_cnt			������� ������ (��� �������� � RF ��������� ������)
	 * @param integer $uid				�� ������������
	 * @param integer $specs			������ �� ��������������
	 * @param array $specs_array		������ �������������
     * @param integer $sex              ��������� �� ����
	 * @return integer	$l_id �� ���������� �������
	 */
	function NewBanner(
		$name, $link, $company_id, $filename, $from_date, $to_date, $banner_type, $citys,  $pro,
		$role, &$error, $show_cntd, $pixel, $code, $for_money='f', $rf = 0, $stat_fname = '', $perday = 0,
		$static = 0, $uniq_cnt = 0, $uid = '', $specs, $specs_array, $sex, $from_time, $to_time, $amount = 0,
		$age_from = NULL, $age_to = NULL
	){
		$l_id = $this->DB->insert('ban_banners', array(
			'name'       => $name,
			'link'       => $link,
			'from_date'  => $from_date,
			'to_date'    => $to_date,
			'filename'   => $filename,
			'company_id' => $company_id,
			'type'       => $banner_type,
			'pro'        => $pro,
			'role'       => $role,
			'show_cntd'  => (($show_cntd == '')? NULL: $show_cntd),
			'pixel'      => $pixel,
			'code'       => $code,
			'for_money'  => $for_money,
			'rf'         => $rf,
			'stat_fname' => $stat_fname,
			'per_day'    => (($perday == '')? NULL: $perday),
			'static'     => $static,
			'uniq_cnt'   => $uniq_cnt,
			'uid'        => (($uid == '')? NULL: $uid),
			'show_cnta'  => (($show_cntd == '')? NULL: $show_cntd),
			'spec'       => $specs,
            'from_time'  => $from_time,
            'to_time'    => $to_time,
            'amount'     => $amount,
            'age_from'   => $age_from,
            'age_to'     => $age_to
		), 'id');
		$error = $this->DB->error;
		if ($l_id){
			$this->DB->insert('ban_stats1', array('banner_id' => $l_id));
			if (count($specs_array)) $this->UpdateSpecs($l_id, $specs_array);
			if ($citys) {
				foreach ($citys as $city){
					$this->addCity($l_id, $city);
				}
			} else {
				$this->addCity($l_id, '0:0');
			}
		}
		return $l_id;
	}
	
    /**
     * ������� ������ � �������
     * 
     * @param int $l_id �� ������
     * @param string $inp ����� � ������� "ID������:ID������"
     */
	function addCity($l_id, $inp){
		list($country, $city) = explode(":", $inp);
		if (!$country) $country = '0';
		if (!$city) $city = '0';
		$this->DB->insert('ban_bantocity', array(
			'ban_id'  => $l_id,
			'city_id' => $city,
			'country' => $country
		));
	}
	
	/**
	 * �������� ��� ������ �� ������
	 *
	 * @param int $id �� ������
	 */
	function dropCitys($id){
		$this->DB->query("DELETE FROM ban_bantocity WHERE ban_id = ?", $id);
	}
	
	/**
	 * �������� ������ �������������
	 * 
	 * @param int $banner_id  �� ������
	 * @param array $specs_array ������ �������������
	 */
	function UpdateSpecs($banner_id, $specs_array){
		$this->DB->query("DELETE FROM ban_specs WHERE banner_id = ?", $banner_id);
		if (is_array($specs_array) && sizeof($specs_array) > 0){
			$cur_specs = $this->GetSpecs($banner_id);
			if (sizeof($cur_specs) > 0 && sizeof($specs_array) > 0){
				$new = array_diff($specs_array, $cur_specs);
				$obs = array_diff($cur_specs, $specs_array);
				if (sizeof($obs) > 0){
					$sql = "DELETE FROM ban_specs WHERE banner_id = '$banner_id' AND spec_id IN (".implode(",",$obs).");";
				} else {
					$sql = "";
				}
				$specs_array = $new;
			}
			if ($specs_array) {
		    	foreach ($specs_array as $spec){
		    		$sqla[] = "('$banner_id', $spec)";
		    	}
		    	$sql .= "INSERT INTO ban_specs (banner_id, spec_id) VALUES ".implode(",",$sqla);
			}
			$this->DB->query($sql);
		}
	}
	
	/**
	 * �������� ������ �������������
	 * 
	 * @param  int $banner_id  �� ������
	 * @return array
	 */
	function GetSpecs($banner_id){
		$out = array();
		if (!$banner_id) {
			return $out;
		}
		$specs_arr = $this->DB->rows("SELECT spec_id FROM ban_specs WHERE banner_id = ?", $banner_id);
		if ($specs_arr) {
			foreach ($specs_arr as $item){
				$out[] = $item['spec_id'];
			}
		} 
		return $out;
	}
	
	/**
	 * �������� ���������� �� �������
	 *
	 * @param integer $id				������������� �������
	 * @param string  $name				�������� �������
	 * @param string  $link				URL c����� �������
	 * @param integer $company_id		�� ��������
	 * @param string  $filename			��� ����� �������
	 * @param string  $from_date		C ������ ��� ����������
	 * @param string  $to_date			�� ����� ���� ����������
	 * @param integer $banner_type		��� ������� (1 - ������� (600�90(60)), 2 - ������� (240�400), 3 - ����� (183� ), 4 - ������� (300�90(60)))
	 * @param bit(3)  $country			������ (100 - ������, 010 - �������, 001- ������)
	 * @param bit(4)  $city				����� (1000 - ���, 0100 - �����, 0010 - ����, 0001 - ������)
	 * @param bit(2)  $pro				���������� ��� ��� (10 - ������ ���, 01 - ������ �� ���)
	 * @param bit(3)  $role				���������� ������ ��� ���������� ����� (100 - ���������, 010 - ������������, 001 - ������)
	 * @param string  $error			���������� ��������� �� ������
	 * @param integer $show_cntd        ���������� �������		
	 * @param string  $pixel       		���. HTML(���� ��� �������� � �.�.)
	 * @param string  $code        		��� ������� (���� ������ �������� �� ��������� �����)
	 * @param boolean $for_money   		��� ������ (false - ���������, true - �� ������)
	 * @param integer $rf				RF (������ ���� ��� 0 - ������������)
	 * @param string  $stat_fname       ��� ������������ ����� ������
	 * @param integer $perday			���������� ������� � ����
	 * @param boolean $static			����������� ����� ��� ���(true - ��, false - ���)
	 * @param boolean $uniq_cnt			������� ������ (��� �������� � RF ��������� ������)
	 * @param integer $uid				�� ������������
     * @param integer $sex              ��������� �� ����
	 * @return string $error		 	C�������� �� ������
	 */
	function EditBanner(
			$id, $name, $link, $company_id, $filename, $from_date, $to_date, $banner_type, $citys,
			$pro, $role, &$error, $show_cntd, $pixel, $code, $for_money='f', $rf = 0, $stat_fname = '',
			$perday = 0, $static = 0, $uniq_cnt = 0, $uid = '', $specs, $specs_array, $sex, $from_time, $to_time, $amount,
			$age_from = NULL, $age_to = NULL
		) {
		$banner = $this->DB->row("SELECT * FROM ban_banners WHERE id = ?", $id);
        if ($filename || $filename === NULL) {
			$f_name = $banner['filename'];
			if ($f_name) {
				$cfile = new CFile("banners/" . $f_name);
				$cfile->Delete("banners/" . $f_name);
			}
            $filename = $filename === NULL ? '' : $filename;
			$new_file = ", filename='$filename'";
		}
		if ($stat_fname || $stat_fname === NULL) {
			$f_name = $banner['filename'];
			if ($f_name) {
				$cfile = new CFile("banners/" . $f_name);
				$cfile->Delete("banners/" . $f_name);
			}
            $stat_fname = $stat_fname === NULL ? '' : $stat_fname;
			$new_st_file = ", stat_fname='$stat_fname'";
		}

		// ��������� �������, ���� �� ����������� ������ ����� (����� ������� ��� RF ��� uniq_cnt).
		// � �� �� ����� ������� rotatebanerstats_(), ����� ���������� ����������, �������� �� ��, ��� rf = 0.
		
        $noclear = (!$rf && $banner['rf']) || (!$uniq_cnt && ($banner['uniq_cnt'] == 't'));
        $show_cntd_r = (int) $banner['show_cntd'];
        $show_cnta_r = (int) $banner['show_cnta'];
		$cnta_q = "CASE WHEN $show_cnta_r <> $show_cntd THEN $show_cntd ELSE show_cnta END";
		$cntd_q = "CASE WHEN $show_cnta_r <> $show_cntd THEN $show_cntd_r - ($show_cnta_r - $show_cntd) ELSE show_cntd END";
		$show_cntd_q = "show_cntd = " . (($show_cntd == '') ? "null" : $cntd_q) . ", show_cnta = " . (($show_cntd == '') ? "null" : $cnta_q) . ", ";
		$this->DB->query("
			UPDATE
				ban_banners
			SET
				name = ?, type = ?, link = ?, from_date = ?, to_date = ?, pixel = ?, code = ?, company_id = ?,
				{$show_cntd_q}
				pro = ?, role = ? {$new_file} {$new_st_file}, for_money = ?, rf = ?, per_day = ?, static = ?, uniq_cnt = ?,
				uid = ?, spec = ?, noclear = ?b, sex = ?i, from_time = ?, to_time = ?, amount = ?, age_from = ?, age_to = ? 
			WHERE
				id = ?
		",
			$name, $banner_type, $link, $from_date, $to_date, $pixel, $code, $company_id, $pro, $role, $for_money, $rf,
			(($perday == '')? NULL: $perday), $static, $uniq_cnt, (($uid == '')? NULL: $uid), $specs, $noclear, $sex, $from_time, $to_time, $amount, $age_from, $age_to, 
			$id
		);
		$error = $this->DB->error;
		error_log("update banners: " . $sql . " | " . $_SESSION['login']);
		if (!$error) {
			if (!count($specs_array))
				$specs_array = array();
			$this->UpdateSpecs($id, $specs_array);
			$error = $this->DB->error;
			error_log("update banners: " . $sql . " | " . $_SESSION['login']);
			if (!$error) {
				$this->dropCitys($id);
				$citys = array_unique($citys);
				if ($citys)
					foreach ($citys as $city) {
						$this->addCity($id, $city);
					} else {
					$this->addCity($id, '0:0');
				}
			}
		}
		return $error;
	}
	
	/**
	 * �������� ������ �������, ��� ������������ ������
	 *
	 * @param integer $id	 ������������� �������
	 * @param array   $pages [['id', 'type']]
	 * @return string $error ��������� �� ������
	 */
	function EditLocation($banner_id, $pages) {
		$sql = "DELETE FROM ban_views WHERE banner_id='" . intval($banner_id) . "';
			PREPARE insertplan (int, int, int) AS
		    INSERT INTO ban_views (banner_id, page_id, page_type_id) VALUES ($1, $2, $3);";
		if ($pages)
			foreach ($pages as $page)
				if (isset($page['id'])) {
					$sql .= "EXECUTE insertplan ('" . intval($banner_id) . "', '" . intval($page['id']) . "', '" . intval($page['type']) . "');";
				}
		$sql .= "DEALLOCATE insertplan";
		$this->DB->query($sql);
		return $this->DB->error;
	}
  	
  	/**
	 * �������� ��� �������, ������� ������������ �� ���������� ������
	 *
	 * @param string $ord			�� ������ ������� �����������
	 * @param string $from_date		� ������ ��� ����������
	 * @param string $to_date		�� ����� ���� ����������
	 * @param string $error			���������� ��������� �� ������
	 * @return array				[[�������� ��������, ������������� ��������, ������������� �������, �������� �������, ����� ������� �������, ����� ������ �� �������]]
	 */
	function GetBanners($ord, $from_date, $to_date, &$error, $only_active = FALSE, $filters = array(), $offset = 0) {
        if ($from_date && $to_date) {
            $where = "WHERE (c_date <= '" . pg_escape_string($to_date) . "' AND c_date >= '" . pg_escape_string($from_date) . "')";
        }
        if ($only_active) {
            $where .= ( $where ? 'AND' : 'WHERE') . " ban_banners.to_date >= now()-'1day'::interval";
        }

        switch ($ord) {
            case 'status':
                $ord = 'is_active';
                break;
            case 'id':
                $ord = 'ban_banners.id';
                break;
            case 'client':
                $ord = 'company';
                break;
            case 'company':
                $ord = 'banner';
                break;
            case 'shows':
                $ord = 'views';
                break;
            case 'clicks':
                $ord = 'clicks';
                break;
            case 'ctr':
                $ord = 'ctr';
                break;
            case 'act':
                $ord = 'ban_banners.from_date';
                break;
            case 'deact':
                $ord = 'ban_banners.to_date';
                break;
//      case 'range':
//          $ord = '';
//          break;
            default:
                $ord = 'ban_banners.id DESC';
        }

        if (count($filters)) {
            $tmp_q = array();
            if ($filters['client']) {
                $tmp_q[] = "ban_company.id = {$filters['client']}";
            }
            if ($filters['type']) {
                $tmp_q[] = "ban_banners.type = {$filters['type']}";
            }
            if ($filters['from_date']) {
                $filters['from_date'] = date('Y-m-d', strtotime($filters['from_date']));
                if ($filters['to_date']) {
                    $filters['to_date'] = date('Y-m-d', strtotime($filters['to_date']));
                    $tmp_q1[] = "(ban_banners.from_date >= '{$filters['from_date']}' AND ban_banners.from_date <= '{$filters['to_date']}')";
                } else {
                    $tmp_q1[] = "ban_banners.from_date >= '{$filters['from_date']}'";
                }
            }
            if ($filters['to_date']) {
                $filters['to_date'] = date('Y-m-d', strtotime($filters['to_date']));
                if ($filters['from_date']) {
                    $filters['from_date'] = date('Y-m-d', strtotime($filters['from_date']));
                    $tmp_q1[] = "(ban_banners.to_date >= '{$filters['from_date']}' AND ban_banners.to_date <= '{$filters['to_date']}')";
                } else {
                    $tmp_q1[] = "ban_banners.to_date <= '{$filters['to_date']}'";
                }
            }
            if ($filters['from_date'] && $filters['to_date']) {
                $tmp_q1[] = "(ban_banners.from_date <= '{$filters['from_date']}' AND ban_banners.to_date >= '{$filters['to_date']}')";
            }
            if ($tmp_q1) {
                $tmp_q[] = "(" . implode(" OR ", $tmp_q1) . ")";
            }

            if ($filters['status']) {
                switch ($filters['status']) {
                    case 1:
                        $tmp_q[] = "ban_banners.from_date <= NOW() AND ban_banners.to_date >= NOW()::date";
                        break;
                    case 2:
                        $tmp_q[] = "ban_banners.to_date < NOW()";
                        break;
                    case 3:
                        $tmp_q[] = "ban_banners.from_date > NOW()";
                        break;
                }
            }

            if (count($tmp_q)) {
                $tmp_q = implode(' AND ', $tmp_q);
                $where .= ( $where ? 'AND' : 'WHERE') . " $tmp_q";
            }
        }

        $sql = "SELECT ban_company.name as company, ban_company.id as cid, ban_banners.id as ban_id,
                ban_banners.name as banner, ban_banners.from_date, ban_banners.to_date, t2.views, static,
                t2.clicks, rf, type, per_day, show_cnta, city, country, uniq_allviews,
                  CASE WHEN ban_banners.from_date <= NOW() AND ban_banners.to_date >= NOW() THEN 0 ELSE 1 END as is_active,
                  CASE WHEN t2.clicks != 0 AND t2.views != 0 THEN floor(t2.clicks/t2.views*10000)/100 ELSE 0 END as ctr
                  FROM ban_banners
               INNER JOIN ban_company ON ban_company.id = ban_banners.company_id
               LEFT JOIN (SELECT SUM(views) as views, SUM(clicks) as clicks, banner_id FROM(
                        SELECT SUM(views) as views, SUM(clicks) as clicks, banner_id FROM ban_stats1 GROUP BY banner_id
                        UNION ALL SELECT COUNT(*), NULL, banner_id FROM ban_stats2 GROUP BY banner_id) as t
                        GROUP BY t.banner_id) as t2
               ON t2.banner_id = ban_banners.id $where	ORDER by $ord LIMIT " . self::ITEMS_ON_PAGE . " OFFSET " . $offset;

        $res = $this->DB->query($sql);
        $error = $this->DB->error;
        return pg_fetch_all($res);
    }
	
	/**
	 * �������� ��� ���� � �������
	 *
	 * @param integer $id			������������� �������
	 * @param integer $user_id		ID ������������
	 * @param string  $error		���������� ��������� �� ������
	 * @return array ���������� � ������ [�������� ��������, ������������� ��������, ������, �������, ���������� ���, 
	 * 									  e-mail, �������, �������� �������, � ������ ����� ������������, �� ����� ����� ������������,
	 * 									  ��� ����� ������, ������� ������ ��� ���, ����������, ������, RF]
	 */
	function GetBanner($id, $user_id, &$error) {
		$id = intval($id);
		$user_id = intval($user_id);
   		$sql = "SELECT ban_company.name as company, ban_company.id as cid, ban_company.adr,
   			 ban_company.phone, ban_company.cont, ban_company.email, ban_company.notes,
   			 ban_banners.name, ban_banners.from_date, ban_banners.to_date, ban_banners.filename, uniq_cnt,
   			 views + zin(views2) as views, clicks, rf, type, per_day, show_cnta, city, country, uniq_allviews,
   			 ban_banners.id FROM ban_banners INNER JOIN ban_company ON ban_company.id=ban_banners.company_id
   			 LEFT JOIN (SELECT SUM(views) as views, SUM(clicks) as clicks, banner_id FROM ban_stats1
   			     WHERE banner_id='$id' GROUP BY banner_id ) as t ON t.banner_id = ban_banners.id
   			 LEFT JOIN (SELECT COUNT(*) as views2, banner_id FROM ban_stats2 WHERE banner_id='$id' GROUP BY banner_id) as t2
   			 ON t2.banner_id = ban_banners.id WHERE ban_banners.id = {$id}
        ";
        
		if ($user_id != -1) {
		    $sql .= " AND uid = {$user_id}";
		}
		
		$row = $this->DB->row($sql);
		$error = $this->DB->error;
		return $row;
	}
	
	/**
	 * ���������� ������ ����� ������� �� �����, ��� �������� ������
	 *
	 * @param integer $id		������������� �������
	 * @param string  $error	���������� ��������� �� ������
	 * @return array			[������������� ��������]
	 */
	function GetPages($id, &$error){
		$all = $this->DB->rows("SELECT page_id, page_type_id FROM ban_views WHERE banner_id = ?", $id);
		$error = $this->DB->error;
		$sel = array();
		if (!$error) {
			if ($all)
				foreach($all as $row){
					$sel[] = $row['page_type_id']."|".$row['page_id'];
				}
		}
		return $sel;
	}
	
	/**
	 * ���������� ���������� � ������� ������ ��������
	 *
	 * @param integer $cid			������������� ��������
	 * @param integer $user_id		ID ������������
	 * @param string $error			���������� ��������� �� ������
	 * @return array ���������� � ������� [�������� ��������, ������������� ��������, ������, �������, ���������� ���, 
	 * 									  e-mail, �������, �������� �������, � ������ ����� ������������, �� ����� ����� ������������,
	 * 									  ��� ����� ������, ������� ������ ��� ���, ����������, ������, RF]
	 */
	function GetBannersByClient($cid, $user_id, &$error){
		$cid = intval($cid);
		$user_id = intval($user_id);
  		$sql = "SELECT ban_company.name as company, ban_banners.id as id, ban_company.adr,
  			 ban_company.phone, ban_company.cont, ban_company.email, ban_company.notes,
  			 ban_banners.name, ban_banners.from_date, ban_banners.to_date, ban_banners.filename,
  			 views + views2 as views, clicks FROM ban_banners INNER JOIN ban_company ON ban_company.id=ban_banners.company_id
  			 LEFT JOIN (SELECT SUM(views) as views, SUM(clicks) as clicks, banner_id FROM ban_stats1
  			     GROUP BY banner_id) as t ON t.banner_id = ban_banners.id
  			 LEFT JOIN (SELECT COUNT(*) as views2, banner_id FROM ban_stats2 GROUP BY banner_id) as t2
  			 ON t2.banner_id = ban_banners.id
        ";
		
        if ($cid == -1) {
			$sql .= "WHERE uid = {$user_id}";
        } else {
			$sql .= "WHERE ban_banners.company_id = {$cid}" . ($user_id == -1 ? '' : " AND uid = {$user_id}");
		}
		
		$rows = $this->DB->rows($sql);
		$error = $this->DB->error;
		return $rows;
	}
	
	/**
	 * ���������� ��� ���������� �� ������������ �������
	 *
	 * @param integer $id			������������� �������
	 * @param string $error			���������� ��������� �� ������
	 * @return array ���������� � ������� (ban_banners.*)
	 */
	function GetBannerSettings($id, &$error){
		$ret = $this->DB->query(
				"SELECT ban_banners.*, COALESCE(ban_city.id,0) as cid, ban_city.cname, ban_bantocity.country FROM ban_banners
				LEFT JOIN ban_bantocity ON ban_banners.id = ban_bantocity.ban_id 
				LEFT JOIN ban_city ON ban_city.id = ban_bantocity.city_id  WHERE ban_banners.id = ?", $id);
		$error = $this->DB->error;
		return pg_fetch_all($ret);
	}
	
	/**
	 * ���������� ������ � ����������� � �������, ������� ���� ���������� �� ������ ��������
	 *
	 * @param string $page				������������� �������� (�������� 0|4, ��� 0 - ���, 4 - �� ��������)
	 * @param integer $banner_type		��� ������� (1 - ������� (600�90(60)), 2 - ������� (240�400), 3 - ����� (183� ), 4 - ������� (300�90(60)))
	 * @return array					[������������� �������, ��� �����, ������ �������, ������ �������, ��� ������� (���� ��� ��������)]
	 */
	function ViewBanner($page, $banner_type) {
        if (defined('DISABLE_BANNERS') && DISABLE_BANNERS) {
            return false;
        }
        $uuid = $this->GetUUID();
        if (!$uuid || !in_array($banner_type, array(2, 5))) { // ��. ������� � ������� ban_stats2.
            return false;
        }
        
        $cached_banners = $this->getCachedBanners($banner_type, $page);
        $row = $this->DB->row(
           'SELECT id, filename, pixel, code, stat_fname FROM viewbanner(?a, ?i, ?, ?, ?f)',
           $cached_banners, $banner_type, $uuid, htmlspecialchars($_SERVER["HTTP_REFERER"], ENT_QUOTES),
           round((float)$_SESSION['ac_sum'], 2)
        );
        
		if ($row['filename']) {
		    $cfile = new CFile("banners/".$row['filename']);
		    $out['width'] = $cfile->image_size['width'];
			$out['height'] = $cfile->image_size['height'];
			$out['type'] = $cfile->image_size['type'];
			$out['id'] = $row['id'];
			$out['filename'] = $row['filename'];
			$out['pixel'] = str_replace("[random]",mt_rand(0,99999),$row['pixel']);
			$out['stat_fname'] = $row['stat_fname'];
		} elseif ($row['code']) {
			$out['code'] = str_replace("[random]",mt_rand(0,99999),$row['code']);
		}
		
		return $out;
	}
	
	/**
	 * ���������� ��� ����� �� ������, ����������� ������� ������ � ���������� ������ � ������� �� ������� ������
	 *
	 * @param integer $banner_id	������������� �������
	 * @return string $out			������ �� ������� ������ ��� ����� �������
	 */
	function ClickBanner($banner_id){
		$out = $this->DB->val("SELECT * FROM clickbanner(?)", $banner_id);
		return $out? $out: array();
	}
	
	/**
	 * ������ ���������� ������������� ����� ��� �������� (RF) � ���������� ��� � ���� �� ���.
	 *
	 */
	function SetCookies(){
	    if (!self::GetUUID()) {
	        $trand = mt_rand(1, 30);
	        $key = md5(uniqid('', true)).'.'.base64_encode(getRemoteIP().'t'.$trand);
            setcookie('XUUID', $key, time()+3600*24*(180+$trand), '/', $GLOBALS['domain4cookie']);
        }
	}
	
	/**
	 * ���������� ���������� ������������� ����� ��� �������� (RF)
	 *
	 * @return string
	 */
	function GetUUID(){
	    return $_COOKIE['XUUID'];
	}
	
	/**
	 * ���������� ���������� �� ban_stats2 � ban_stats1 � ������ �������.
	 * @param integer $banner_type   ��� ������� (2:240�400, 5:�������� ������).
	 */
	function GC($banner_type){
	    $this->DB->query('SELECT rotatebanerstats(?i)', $banner_type);
	}

    /**
     * ������ ���������� �� ������� ������� ���������� ������
     *
     * @param integer $bid		������������� �������
     * @return array			������ �� ����������� ���� array(ucnt, vdate, views) - ���-�� ����. ������� �� ����,
     * 								���-�� �������, ���� ������ + array('all' => ucnt) - ����� ���-�� �������.
     */
    function GetBannerUNStat($bid, $filters = null, $order = null, $offset = null) {
        $ord = '';
        $where_date1 = '';
        $where_date2 = '';
        if ($filters['from_date']) {
            $fdate = date('Y-m-d', strtotime($filters['from_date']));
            $where_date1 .= " AND sdate >= '{$fdate}'";
            $where_date2 .= " AND vdate >= '{$fdate}'";
        }
        if ($filters['to_date']) {
            $tdate = date('Y-m-d', strtotime($filters['to_date']));
            $where_date1 .= " AND sdate <= '{$tdate}'";
            $where_date2 .= " AND vdate < '{$tdate}'::date + 1";
        }
        
        switch ($order) {
          case 'ucnt':
              $ord = 'ucnt';
              break;
          case 'shows':
              $ord = 'views';
              break;
          case 'clicks':
              $ord = 'clicks';
              break;
          case 'ctr':
              $ord = 'ctr';
              break;
          default:
              $ord = 'sdate DESC';
              break;
        }
        
        if($ord) {
            $ord = "ORDER BY {$ord}";
        }


        if ($offset) {
            $offset = $this->DB->parse('LIMIT ?i OFFSET ?i', self::ITEMS_ON_PAGE, $offset);
        } else {
            $offset = '';
        }
        
        $psql = "
          SELECT banner_id, sdate, SUM(views) as views, SUM(uniq_views) as ucnt, SUM(clicks) as clicks
            FROM (
              SELECT banner_id, sdate, views, uniq_views, clicks FROM ban_stats1 WHERE banner_id = ?i {$where_date1}
              UNION ALL
              SELECT banner_id, vdate, SUM(views), COUNT(1), 0
                FROM (
                  SELECT banner_id, vdate::date as vdate, uuid, COUNT(1) as views
                    FROM ban_stats2
                   WHERE banner_id = ?i {$where_date2}
                   GROUP BY banner_id, vdate::date, uuid
                ) as x
               GROUP BY banner_id, vdate
            ) as bs
           GROUP BY banner_id, sdate
        ";
        
        $out = $this->DB->rows(
          "SELECT *, CASE WHEN views > 0 THEN (clicks/views*10000)/100 ELSE 0.0 END as ctr
             FROM ({$psql}) as zz
            {$ord} {$offset}",
          $bid, $bid
        );
        
        $out['all'] = $this->DB->row(
          "SELECT COUNT(*), SUM(ucnt) as c_ucnt, SUM(views) as c_views, SUM(clicks) as c_clicks FROM ({$psql}) as zz",
          $bid, $bid
        );
        
        return $out;
    }
	
	/**
	 * ��������� ������ ������������� ����� � �������
	 *
	 * @param integer $uid    �� ������������
	 * @param integer $ban_id �� �������
	 * @return boolean true - ���� ���� ������, false - ���� ��� �������
	 */
	function checkAccess($uid, $ban_id){
		if ($uid){
        	$out = $this->DB->val("SELECT uid FROM ban_banners where id = ?", $ban_id);
			return ($out == $uid);
		}
		return false;
	}
	
	/**
	 * ��������� ����� � �������� ������ ���������
	 * 
	 * @param  float $number
	 * @return string
	 */
	function numformat($number){
		return number_format($number, 0, ',' ,' ');
	}
	
	/**
	 * ���������� ������� ��� ���������� ���������� �������
	 * 
	 * @param  string $from � ������ ����� ����������
	 * @param  string $to �� ������ ����� ����������
	 * @param  bool $static ��������� ����� ��� ���
	 * @return array
	 */
	function GetBannersByDate($from, $to, $static = false){
		return $this->DB->rows("
			SELECT
				ban_banners.id, ban_company.name as cname, ban_banners.name, from_date, to_date, rf, show_cnta,	show_cntd
			FROM
				ban_banners
			INNER JOIN
				ban_company ON ban_company.id = company_id
			WHERE
				from_date <= ? AND to_date >= ?" . (($static)? " AND static = true": " AND static <> true"),
			$to, $from
		);
	}
	
	/**
	 * ���������� ������ �����.
	 * 
	 * @return array
	 */
	function GetCountrys(){
	    return array( array('country'=>'RU','country_name'=>'������'), array('country'=>'UA','country_name'=>'�������'));
	}
	
	/**
	 * ���������� ������ �������.
	 *
	 * @param  string $country ��� ������
	 * @return array
	 */
	function GetCitys($country){
		return $this->DB->rows("SELECT id, cname FROM ban_city WHERE country = ? AND cname IS NOT NULL ORDER BY cname", $country);
	}


    /**
     * �������� ���������� ��������
     * 
     * @param  mixed $ord �� ������������
     * @param  string $from �� ������������ � ������ ����� ����������
	 * @param  string $to �� ������������ �� ������ ����� ����������
     * @param  string $error ��������� �� ������
     * @param  boll �� ������������  $only_active �������� �� ����
     * @param  array $filters �������������� �������
     * @return array
     */
    function GetBannersCount($ord, $from_date, $to_date, &$error, $only_active = FALSE, $filters = array()) {
//        if ($from_date && $to_date)
//            $where = "WHERE (c_date <= '" . pg_escape_string($to_date) . "' AND c_date >= '" . pg_escape_string($from_date) . "')";
//        if ($only_active) {
//            $where .= ( $where ? 'AND' : 'WHERE') . " ban_banners.to_date >= now()-'1day'::interval";
//        }

        if (count($filters)) {
            $tmp_q = array();
            if ($filters['client']) {
                $tmp_q[] = "ban_company.id = {$filters['client']}";
            }
            if ($filters['type']) {
                $tmp_q[] = "ban_banners.id = {$filters['type']}";
            }
            if ($filters['from_date']) {
                $filters['from_date'] = date('Y-m-d', strtotime($filters['from_date']));
                if ($filters['to_date']) {
                    $filters['to_date'] = date('Y-m-d', strtotime($filters['to_date']));
                    $tmp_q1[] = "(ban_banners.from_date >= '{$filters['from_date']}' AND ban_banners.from_date <= '{$filters['to_date']}')";
                } else {
                    $tmp_q1[] = "ban_banners.from_date >= '{$filters['from_date']}'";
                }
            }
            if ($filters['to_date']) {
                $filters['to_date'] = date('Y-m-d', strtotime($filters['to_date']));
                if ($filters['from_date']) {
                    $filters['from_date'] = date('Y-m-d', strtotime($filters['from_date']));
                    $tmp_q1[] = "(ban_banners.to_date >= '{$filters['from_date']}' AND ban_banners.to_date <= '{$filters['to_date']}')";
                } else {
                    $tmp_q1[] = "ban_banners.to_date <= '{$filters['to_date']}'";
                }
            }
            if ($filters['from_date'] && $filters['to_date']) {
                $tmp_q1[] = "(ban_banners.from_date <= '{$filters['from_date']}' AND ban_banners.to_date >= '{$filters['to_date']}')";
            }
            if ($tmp_q1) {
                $tmp_q[] = "(" . implode(" OR ", $tmp_q1) . ")";
            }


            if ($filters['status']) {
                switch ($filters['status']) {
                    case 1:
                        $tmp_q[] = "ban_banners.from_date <= NOW() AND ban_banners.to_date >= NOW()::date";
                        break;
                    case 2:
                        $tmp_q[] = "ban_banners.to_date < NOW()";
                        break;
                    case 3:
                        $tmp_q[] = "ban_banners.from_date > NOW()";
                        break;
                }
            }

            if (count($tmp_q)) {
                $tmp_q = implode(' AND ', $tmp_q);
                $where .= ( $where ? 'AND' : 'WHERE') . " $tmp_q";
            }
        }

        $res = $this->DB->query("SELECT COUNT(*)  FROM ban_banners
            INNER JOIN ban_company ON ban_company.id = ban_banners.company_id
            LEFT JOIN (SELECT SUM(views) as views, SUM(clicks) as clicks, banner_id FROM(
                SELECT SUM(views) as views, SUM(clicks) as clicks, banner_id FROM ban_stats1 GROUP BY banner_id
                UNION ALL SELECT COUNT(*), NULL, banner_id FROM ban_stats2 GROUP BY banner_id) as t
                GROUP BY t.banner_id) as t2
            ON t2.banner_id = ban_banners.id $where");
        $error = $this->DB->error;
        return pg_fetch_row($res);
    }
    

    /**
     * ��������� � ������ ������� ip ����� � ������-����� ��� ��������,
     * ���� ip ���������, ������ ����� ����� �����������.
     */
    function CheckSessionCountryCity() {
        $DB = new DB('banner');
        
        @session_start();
        

        $ip = getRemoteIP();
        
        if (!isset($_SESSION['last_ip']) || (isset($_SESSION['last_ip']) && $_SESSION['last_ip'] != $ip )
            || (!isset($_SESSION['banners_city']) && !isset($_SESSION['banners_country'])) ) {
            $_SESSION['last_ip'] = $ip;
            
            $sql = "SELECT city_id, country
                FROM ban_netwoks
                INNER JOIN ban_city ON ban_city.id = ban_netwoks.city_id
                WHERE ? BETWEEN ip_from AND ip_to
                ORDER BY ip_to LIMIT 1";
                
            $res = $DB->row($sql, $ip);
            
            $_SESSION['banners_city'] = intval($res['city_id']);
            $_SESSION['banners_country'] = $res['country'];
        }
    }
    
    /**
     * �������� �������������� ��������, ������� ����� ���� ���������� ������������,
     * �� ��������� ������ ��� ������
     * @see banners::CheckSessionCountryCity()
     * 
	 * @param integer $banner_type   ��� ������� (5:������� (��������); 2:������� (240�400))
	 * @param string $page           ������������� �������� (�������� 0|4, ��� 0 - ���, 4 - �� ��������)
     * @return array   ������ ���������������.
     */
    function getCachedBanners($banner_type, $page) {
        $GLOBALS[memBuff::SERVERS_VARKEY] = banners::MEM_SERVER_KEY;
        $city = intval($_SESSION['banners_city']);
        $country = $_SESSION['banners_country'];
        $specs = $_SESSION['specs'] ? $_SESSION['specs'] : NULL;
        list($page_type, $page_id) = preg_split("/[|]/", $page);
        $sex = ($_SESSION['sex'] == 't' ? 1 : ($_SESSION['sex'] == 'f' ? 2 : -1));
        $age = ($_SESSION['uid'] ? ($_SESSION['age']>0 ? $_SESSION['age'] : 0) : 0);
        $pro  = '00';
        $role = '001';
        if ($_SESSION['uid']) {
            $pro  = $_SESSION['pro_last'] > 0 ? '10' : '01';
            $role = is_emp() ? '010' : '100';
        }
        
        return $this->DB->cache(banners::MEM_LIFE, banners::MEM_GROUP_KEY)->col(
                  'SELECT getbanners4cache(?i, ?i, ?i, ?, ?, ?i, ?, ?i, ?a, ?i)',
                  $banner_type, $page_id, $page_type, $role, $pro, $sex, $country, $city, $specs, $age 
               );
    }
    
    public function validTimer($time, &$ret=false) {
        $e = explode(":", $time);
        if($e[0] >= 0 && $e[0] < 24) {
            $hour = (int)$e[0];
        } else {
            return false;
        }
        
        if($e[1] >= 0 && $e[1] < 60) {
            $min = $e[1];
        } else {
            return false;
        }
        $ret = (int) ($hour.$min);   
        return date('H:i:00', mktime($hour, (int)$min, 0, 0, 0, 0));
    }

}
?>
