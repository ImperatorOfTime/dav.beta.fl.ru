<?php 

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_answers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");


/*
 * ����� ��� ����������� �������� �� �������
 *
 */

class HTMLProjects{

    /**
    * id ������������
    *
    * @var integer
    */
	private $uid;

    /**
    * ����� ������� ������������
    *
    * @var numeric
    */
	private $user_role;

    /**
    * ���� PRO
    *
    * @var boolean
    */
	private $pro_last;
	
	/**
	 * �������� �� PRO
	 * 
	 * @var boolean
	 */
    private $is_pro; 
	   
    /**
    * ���� ������ ��� ����������
    *
    * @var boolean
    */
	private $edit_mode;

    /**
    * ��� ���������� ������������ -- ��� ���������� 1 ��� �� ��� ���������� 0
    *
    * @var integer
    */
	private $show_data;

    /**
    * ���-�� �������� �� ��������
    *
    * @var integer
    */
	private $num_prjs;

    /**
    * ������ �������� (��������� ������ projects::GetProjects())
    *
    * @var array
    */
	private $projects;

    /**
    * ������ ���������� � �������
    *
    * @var array
    */
	private $project;
	
    /**
    * ID ��������(��� �������)
    *
    * @var integer
    */
	private $kind;

    /**
    * ����� ��������
    *
    * @var integer
    */
	private $page;

    /**
    * ���� ������� ��������.
	* TRUE - ��������� �������������, FALSE - �� ������������.
    *
    * @var integer
    */
	private $filter;
    
    public $template = '/projects/tpl.lenta.php';

    
    public $hide_paginator = false;
    public $hide_rss = false;






    /**
	 * ������� ��������� ����� ��������
	 *
	 * @param integer $num_prjs	�������� �� ��������
	 * @param array   $projects	������ �������� (��������� ������ projects::GetProjects())
	 * @param integer $kind		��������
	 * @param integer $page		����� ��������
	 * @param inetger $is_ajax    ���� ������� ������� ����� ajax @see JS seo_print();
	 * @return HTML
	 *
	 */

	function ShowProjects($num_prjs, $projects, $kind, $page, $filter, $is_ajax) {
        $this->projects = $projects;
        $this->num_prjs = $num_prjs;
        $this->kind = $this_kind = $kind;
        $this->page = $this_page = ($page == "") ? 1 : $page;
        $this->pages = ceil($this->num_prjs / new_projects::PAGE_SIZE);
        $this->filter = $this_filter = (int) ($filter && $filter['active'] == 't');

        $this->uid = $this_uid = get_uid(false);
        $this->pro_last = $this_pro_last = $_SESSION['pro_last'];
        $this->is_pro = $this_is_pro = payed::CheckPro($_SESSION['login']);
        $this->edit_mode = $this_edit_mode = hasPermissions('projects');

        if ($this->uid) {
            $this->user_role = $this_user_role = $_SESSION['role'];
        }

        $outHTML = "";

        $outHTML .= $this->ShowHeader();

        $list = array();

        if ($projects) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
            $stop_words = new stop_words( hasPermissions("projects") );
            
            foreach ($projects as $i => $project) {
                $this->project = $project;
                $list[$i] = $project;

                if ($this->edit_mode || $this->project['kind'] == 2 || $this->project['user_id'] == $this->uid || $this->project['offer_id'] || $this->pro_last) {
                    $this->show_data = 1;
                    $list[$i]['show_data'] = 1;
                } else {
                    $this->show_data = 0;
                    $list[$i]['show_data'] = 0;
                }
                
                $descr = $list[$i]['descr'];
                $descr = $list[$i]['moderator_status'] === '0' && $list[$i]['kind'] != 4 && $list[$i]['is_pro'] != 't' ? $stop_words->replace($descr) : $descr;
                $descr = preg_replace("/^ /","\x07",$descr);
                $descr = preg_replace("/(\n) /","$1\x07",$descr);
                $descr = LenghtFormatEx($descr, 180);
                $descr = htmlspecialchars($descr, ENT_QUOTES, 'CP1251', false);
                $descr = reformat($descr, 50, 1, 0, 1);
                $descr = preg_replace("/\x07/","&nbsp;",$descr);

                $list[$i]['name'] = htmlspecialchars($list[$i]['name'], ENT_QUOTES, 'CP1251', false);
                $list[$i]['descr'] = $descr;
                $list[$i]['t_is_payed'] = ($this->project['payed'] && $this->project['kind'] != 2 && $this->project['kind'] != 7 && $this->project['kind'] != 4);
                $list[$i]['t_is_contest'] = ($this->project['kind'] == 2 || $this->project['kind'] == 7);
                $list[$i]['t_pro_only'] = ($this->project['pro_only'] == "t" );
                $list[$i]['t_verify_only'] = ($this->project['verify_only'] == "t" );
                $list[$i]['t_prefer_sbr'] = ($this->project['prefer_sbr'] == "t" );
                $list[$i]['priceby'] = $this->project['priceby'];
                $list[$i]['t_is_adm'] = hasPermissions('projects');
                $list[$i]['t_is_ontop'] = (strtotime($this->project['top_to']) >= time());
                $list[$i]['unread'] = ((int) $this->project['unread_p_msgs'] + (int) $this->project['unread_c_msgs'] + (int) $this->project['unread_c_prjs']);
                $list[$i]['t_is_proonly'] = ($this->project['pro_only'] == 't' && !$_SESSION['pro_last'] && !$this->edit_mode && ($this->uid != $this->project['user_id']));
                $list[$i]['friendly_url'] = getFriendlyURL('project', array('id'=>$this->project['id'], 'name'=>$this->project['name']));

                $attaches = projects::GetAllAttach($this->project['id']);
                $attaches = !$attaches ? array() : $attaches;

                foreach ($attaches as $k => $a) {
                    $a['virus'] = is_null($a['virus']) ? $a['virus'] : bindec($a['virus']);
                    $attaches[$k] = $a;
                }
                
                $list[$i]['attaches'] = $this->project['attaches'] = $attaches;
                
                $list[$i]['view_cnt'] = projects::getProjectViews($this->project['id']);

            }
        } elseif ($page == 1) {
            $outHTML .= "<div class=\"project-preview\">������ �� �������</div>";
        }
        
        
        $kind = $this->kind;
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php';
        
        switch($kind){
            case 0: case 1: $rss_link = "projects.xml"; break;
            case 2: $rss_link = "competition.xml"; break;
            case 4: $rss_link = "office.xml"; break;
            case 6: $rss_link = "pro.xml"; break;
            case 5: $rss_link = "all.xml"; break;
            default: $rss_link = "all.xml"; break;
        }
        
        $isPrjOpened = isset($_COOKIE['isPrjOpened']) ? $_COOKIE['isPrjOpened'] : true;

        ob_start();
        include (ABS_PATH . $this->template);
        $out = ob_get_clean();
        
        if ( $page <= $this->pages ) {
        	$out .= '<!--data_found-->';
        }

        return $out;
    }
    

	/*
	 * ������� ��������� HTML ���� ����������� JS
	 *
	 * @return HTML
	 *
	 */
	private function ShowHeader()
	{
		return '
            <a name="viewfirst"></a><div id="publicfirst"></div>
        ';
	}

	/*
	 * ������� ��������� ������������� ������(��������)
	 *
	 * @return HTML     HTML-��� ��������
	 */
	private function ShowPages()
	{
		// ��������
		$pages = ceil($this->num_prjs / new_projects::PAGE_SIZE);
		if ($pages > 1){
		    $sBox = "
		    <div class=\"pager\">";
		    if ($this->page == $pages){
				;//$sBox .= "<span class=\"page-next\">���������&nbsp;&nbsp;&rarr;</span>";
			}else {
				$sBox .= "
				<input id=\"next_navigation_link\" type=\"hidden\" value=\"?kind=".$this->kind."&amp;page=".($this->page+1)."\" /><span class=\"page-next\"><a href=\"?kind=".$this->kind."&amp;page=".($this->page+1)."\">���������</a>&nbsp;&nbsp;&rarr;</span>";
			}
			if ($this->page == 1){
				;//$sBox .= "<span class=\"page-back\">&larr;&nbsp;&nbsp;����������</span>";
			}else {
				$sBox .= "
				<input id=\"pre_navigation_link\" type=\"hidden\" value=\"?kind=".$this->kind."&amp;page=".($this->page-1)."\" /><span class=\"page-back\">&larr;&nbsp;&nbsp;<a href=\"?kind=".$this->kind."&amp;page=".($this->page-1)."\">����������</a></span>";
			}
	
			//� ������
			if ($this->page <= 10) {
				$sBox .= $this->BuildNavigation($this->page, 1, ($pages>10)?($this->page+4):$pages, "?kind=".$this->kind."&amp;page=");
				if ($pages > 15) {
					$sBox .= '...';
				}
			}
			//� �����
			elseif ($this->page >= $pages-10) {
				$sBox .= '...';
				$sBox .= $this->BuildNavigation($this->page, $this->page-5, $pages, "?kind=".$this->kind."&amp;page=");
			}else {
				$sBox .= '...';
				$sBox .= $this->BuildNavigation($this->page, $this->page-4, $this->page+4, "?kind=".$this->kind."&amp;page=");
				$sBox .= '...';
			}
            $sBox .= "</div>";
		} // �������� �����������

		return $sBox;
	}

	/**
	 * ������� ��������� ������ �� RSS
	 *
	 * @return HTML     HTML-��� � �������� �� RSS
	 */
	private function ShowRSSLink() {
		$sBox = "<br/><div class=\"rss\">";
		switch ($this->kind) {
			case 0: case 1: $sBox .= "<a href=\"javascript:void(0)\" onClick=\"showRSS(); return false;\" class=\"ico_rss\"><img src=\"/images/ico_rss.gif\" alt=\"RSS\" /></a> "; break; //<a href=\"/rss/projects.xml\">�������</a>";
			case 2: $sBox .= "<a href=\"javascript:void(0)\" onClick=\"showRSS(); return false;\" class=\"ico_rss\"><img src=\"/images/ico_rss.gif\" alt=\"RSS\" /></a> "; break; //<a href=\"/rss/competition.xml\">��������</a>";
			case 4: $sBox .= "<a href=\"javascript:void(0)\" onClick=\"showRSS(); return false;\" class=\"ico_rss\"><img src=\"/images/ico_rss.gif\" alt=\"RSS\" /></a> "; break; //<a href=\"/rss/office.xml\">� ����</a>"; break;
			case 6: $sBox .= "<a href=\"javascript:void(0)\" onClick=\"showRSS(); return false;\" class=\"ico_rss\"><img src=\"/images/ico_rss.gif\" alt=\"RSS\" /></a> "; break; //<a href=\"/rss/pro.xml\">������� ��� PRO</a>"; break;
			case 5: $sBox .= "<a href=\"javascript:void(0)\" onClick=\"showRSS(); return false;\" class=\"ico_rss\"><img src=\"/images/ico_rss.gif\" alt=\"RSS\" /></a>"; break;
		}
		
		$sBox .= $this->ShowRSSPopup($this->kind);
        $sBox .= "</div>";
        return $sBox;
	}

	/*
	 * ��������������� ������� ��������� ��������/�� �������� ������� �� �������� ��� ��������
	 *
	 * @return HTML     HTML-��� ������ �� ��������
	 */
	private function BuildNavigation($iCurrent, $iStart, $iAll, $sHref)
	{
		$sNavigation = '';
		for ($i=$iStart; $i<=$iAll; $i++) {
			if ($i != $iCurrent) {
				$sNavigation .= "<a href=\"".$sHref.$i."\" >".$i."</a>&nbsp;";
			}else {
				$sNavigation .= '<span class="page"><span><span>'.$i.'</span></span></span>&nbsp;';
			}
		}
		return $sNavigation;
	}


	/*
	 * ������� ��������� ������ �� �������� ������� � ����������� �� ���� �������(������ ��� PRO/��� ����)
	 *
	 * @return HTML     HTML-��� � ������� /proonly.php, ���� ������ ������ ��� PRO, ����� ������ ��� ��������� �������
	 */
	function GetProjectLink()
	{
		if (is_new_prj($this->project['post_date'])) {
        	    $link = ($this->project['pro_only'] == 't' && !$this->is_pro && !$this->edit_mode && ($this->uid != $this->project['user_id']))?"/proonly.php":"/blogs/view.php?tr=".$this->project['thread_id'];
	        } else {
        	    $link = ($this->project['pro_only'] == 't' && !$this->is_pro && !$this->edit_mode && ($this->uid != $this->project['user_id']))?"/proonly.php":getFriendlyURL("project", $this->project['id']);
	        }

        	return $link;
    }

	/**
	 * ������� ������������� ��� ��������������� ��������
	 *
	 * @param string  $reason	      ������� ����������
	 * @param date    $date	          ���� ���������
	 * @param string  $moder_login    ����� ���������� (�������� ������, ���� ���������� �� �����)
	 * @param string  $moder_name     uname � usurname ���������� (�������� ������, ���� ���������� �� �����)
	 *
	 * @return HTML
	 */
    function BlockedProject($reason, $date, $moder_login='', $moder_name='')
    {
        $reason = reformat($reason, 24, 0, 0, 1, 24);
        
        $html = "
            <div class='br-moderation-options'>
                <a href='http://feedback.fl.ru/' class='lnk-feedback' style='color: #fff;'>������ ���������</a>
                <div class='br-mo-status'><strong>������ ������������.</strong> �������: $reason</div>";
        if ($moder_login) {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php';
            $users = new users();
            $admUid = $users->GetUid($error, $moder_login);
            $link = "/siteadmin/admin_log/?cmd=filter&to_d=" . dateFormat('d', $date) . "&to_m=" . dateFormat('m', $date) . "&to_y=" . dateFormat('Y', $date) . "&adm=" . $admUid . "&act=9";
            $html .=   '<div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_float_right">
                            <img class="b-layout__pic b-layout__pic_valign_middle" src="/images/comm.gif" alt="" width="15" height="14"> 
                            <a class="b-layout__link b-layout__link_fontsize_11" href="' . $link . '">����������� �� �������</a>
                        </div>';
        }
        $html .= "<p class='br-mo-info'>".
                ($moder_login? "������������: <a href='/users/$moder_login' style='color: #FF6B3D'>$moder_name [$moder_login]</a><br />": '').
                "���� ����������: ".dateFormat('d.m.Y H:i', $date)."</p>
            </div>
        ";
        
        return $html;
    }

    /**
     * ���������� ����� ��� ������ ������� � RSS
     *
     */
    function ShowRSSPopup($kind) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php';
        
        switch($kind){
            case 0: case 1: $rss_link = "projects.xml"; break;
            case 2: $rss_link = "competition.xml"; break;
            case 4: $rss_link = "office.xml"; break;
            case 6: $rss_link = "pro.xml"; break;
            case 5: $rss_link = "all.xml"; break;
            default: $rss_link = "all.xml"; break;
        }
        
        $categories = professions::GetAllGroupsLite();
//        $subcat = professions::GetAllProfessions();
//        var_dump(professions::GetAllProfessions());
        $script = "
            <script type='text/javascript'>
            var sub = new Array();
        ";
        $professions = professions::GetAllProfessions();
        array_group($professions, 'groupid');
        $professions[0] = array();
        foreach ($categories as $cat) {
            $out_s = array();
        
            $script .= "
                sub[{$cat['id']}] = new Array(
            ";
            if(is_array($professions[$cat['id']])) {
                foreach ($professions[$cat['id']] as $subcat) {
                    $out_s[] = " new Array({$subcat['id']}, '".clearTextForJS($subcat['profname'])."') ";
                }
                $script .= implode(', ', $out_s);
            }
            $script .= "
                );
            ";
        }
        $script .= "
            function applySubcat(cat){
                if(typeof sub[cat] != 'undefined')
                for(var i = 0; i < sub[cat].length; i++){
                    var option = document.createElement('option');
                    option.value = sub[cat][i][0];
                    option.innerHTML = sub[cat][i][1];
                    document.getElementById('rss_sub').appendChild(option);
                }
            }

            function getRssUri(){
                var cat = document.getElementById('rss_cat').value;
                var sub = document.getElementById('rss_sub').value;
                var xml_path = '/rss/{$rss_link}';
                if(sub){
                    return xml_path+'?subcategory='+sub+(cat ? '&category='+cat : '');
                }else if(cat){
                    return xml_path+'?category='+cat;
                }else{
                    return xml_path;
                }
            }

            function gotoRSS(){
                document.location.href = getRssUri();
            }

            function clearSelect(sid)
            {

            var oListbox = document.getElementById(sid);
            for (var i=oListbox.options.length-1; i >= 0; i--)
            {
                oListbox.remove(i);
            }

            }

            function showRSS(){
                clearSelect('rss_sub');
                // var newoption = new Option('���� ������', '');
                var newoption = document.createElement('option');
                newoption.value = '';
                newoption.innerHTML = '���� ������';
                document.getElementById('rss_sub').appendChild(newoption);
                document.getElementById('rss_cat').value = '';
                document.getElementById('rsso').style.display='block';
            }


            function FilterSubCategoryRSS(category)
            {
                var objSel = $('rss_sub');
                objSel.options.length = 0;
                objSel.disabled = 'disabled';
                objSel.options[objSel.options.length] = new Option('���� ������', 0);
                if(category == 0) {
                    objSel.set('disabled', true);
                } else {
                    objSel.set('disabled', false);
                }
                //  var ft = true;
                applySubcat(category);
                //  for (i in filter_specs[category]) {
                //  if (filter_specs[category][i][0]) {
                //  objSel.options[objSel.options.length] = new Option(filter_specs[category][i][1], filter_specs[category][i][0], ft, ft);
                //  ft = false;
                //  }
                //  }
                objSel.value = 0;
            }
            </script>
        ";
        // $subcategories = professions::Get
        $select = "<select style=\"width:340px\"  onchange=\"FilterSubCategoryRSS(this.value);\" name=\"rss_cat\" id=\"rss_cat\">><option value=\"\">��� �������</option>";
        foreach($categories as $cat) {
            if(!$cat['id']) continue;
            $select .= "<option value=\"{$cat['id']}\">{$cat['name']}</option>";    
        }
        $select .= "</select>";
        
        
        return "
        {$script}
        <div style=\"display: none;\" class=\"overlay ov-out\" id=\"rsso\">
            <b class=\"c1\"></b>
            <b class=\"c2\"></b>
            <b class=\"ov-t\"></b>
            <div class=\"ov-r\">
               <div class=\"ov-l\">
                       <div class=\"ov-in\" style=\"height:110px\">
                            <label for=\"rss\">������� �������:</label>&nbsp;&nbsp;<br/>{$select}<br/>
                            <label for=\"rss_sub\">������� ����������:</label>&nbsp;&nbsp;<br/><select style=\"width:340px\" name=\"rss_sub\" id=\"rss_sub\">
                            <option value=\"\">���� ������</option>
                            </select>
                            <div class=\"ov-btns\">
                                <input value=\"�����������\" class=\"i-btn i-bold\" type=\"button\" onClick=\"gotoRSS(); document.getElementById('rsso').style.display='none'; return false;\">
                                <input value=\"��������\" class=\"i-btn\" onclick=\"$(this).getParent('div.overlay').setStyle('display', 'none'); return false;\" type=\"button\">
                           </div>
                       </div>
                    </div>
                 </div>
              <b class=\"ov-b\"></b>
              <b class=\"c3\"></b>
               <b class=\"c4\"></b>
           </div>";
        
    }
}

?>
