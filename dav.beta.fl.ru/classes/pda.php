<?

/**
 * ����� ��� ������ ������� � ������ ���
 *
 */
class pda
{
    /**
	 * ���������� ������ �������������� ������ � HTML-���, ������� ����� ������������ �� ������ ���� (�����, ����� ����� ������)
	 *
	 * @param string  $ulogin   			����� ������������
	 * @param string  $filename 			��� ����
	 * @param string  $dir					���������� � ������� ��������� ����	
	 * @param integer $file					� �������� ���������� ������������ ������ ������ ����� 
	 * 										(0 - ���� �� ������������� ��������, 1 - ���� ������������� �������� (��� �������� � �����, 
	 * 										�������� ������� ������, ����� �� �� �������� �� ������� �������))
	 * @param integer $maxh					����������� ���������� ������ �����
	 * @param integer $maxw					����������� ���������� ������ �����
	 * @param integer $maxpw       			����������� ���������� ������ �����
	 * @param integer $show_ico				�������� ������ ����� (.txt, .doc, etc...)
	 * @param integer $is_tn				�������� ��� ������ �� ���������� �����
	 * @param integer $show_download_ico   	�������� ��� ��� ������ �� �������� ����� ������
	 * @return string HTML-���
	 */
    function viewattachLeft($ulogin, $filename, $dir, &$file, $maxh=1000, $maxw=450, $maxpw=307200, $show_ico = 0, $is_tn = 0, $show_download_ico = 0) {
        if ($is_tn && in_array($ext, $GLOBALS['graf_array'])) {$fname = $filename; $filename = "sm_".$filename; }
        if($ulogin) {
           $cfile = new CFile('users/'.substr($ulogin, 0, 2)."/$ulogin/$dir/$filename");
           $path = WDCPREFIX."/users/$ulogin/$dir/";
        }
        else {
           $cfile = new CFile($dir.$filename);
           $path = WDCPREFIX.'/'.$dir;
        }
        $file = 1;
        $ext  = $cfile->getext();
        $ico  = getICOFile($ext, true);
        
        $width = $cfile->image_size['width'];
        $height = $cfile->image_size['height'];
        $type = $cfile->image_size['type'];
        
        
        if ($show_download_ico && in_array($ext, $GLOBALS['graf_array']) && ($width <= $maxw && $height <= $maxh || $maxw == -1 && $maxh == -1) && $cfile->size <= $maxpw && $ico != "swf"){
            $pda_content = '<p class="bg-c"><a href="'.$path.$filename.'"><img src="'.$path."/".$filename.'" width="'.$width.'" height="'.$height.'" /></a></p>';    
        } else {
            $pda_content = '<p><img src="/pda/images/mime/'.$ico.'.png" alt="'.$ext.'">&nbsp;<a href="'.$path.$filename.'" target="_blank">���������</a> | '.$ext.', '.ConvertBtoMB($cfile->size).' </p>';
        }
        
        return $pda_content;
    }
    
    /**
     * ������� ����� � ��������� ������� URL ��� ������������� �����
     *
     * @param string  $ulogin   	����� ������������
     * @param string  $filename 	��� ����� 
     * @param string  $dir      	����� �����
     * @param string  $url		 	������ �� ����
     * @param integer $bigtext  	������ ������� ����� ������� ����� ���������� �����(TXT, PDF, etc...) ���� ���������� ��������� �������� 1
     * @return string HTML-code
     */
    function viewattachExternal($ulogin, $filename, $dir, $url, $bigtext=0) {
        $l_dir = substr($ulogin, 0, 2)."/".$ulogin;
        $cfile = new CFile("users/$l_dir/".$dir."/".$filename);
        if (!$cfile->size) return "";
        $ext   = $cfile->getext($filename);
        $ico   = getICOFile($ext, true);
        echo $ico;
        $fsize = ConvertBtoMB($cfile->size);
        return '<p><img src="/pda/images/mime/'.$ico.'.png" alt="'.$ext.'" width="18" height="16"> <a href="'.$url.'" target="_blank">�������</a> | '.ucfirst($ext).', '.$fsize.' </p>';
    }
    
    /**
     * ��������� ��� PDA
     *
     * @param integer $page    ������� ��������
     * @param integer $pages   ����� �������
     * @param integer $count   ���������� ������ ������ ������ �������, ������� ���������� �������� �� ������������� ���������� 
     * 						   (@example ��� �������� 3 - 1,2,3 ��� �������� 4 1,2,3,4 etc...)
     * @param string $href     ������ �� �������� ��� ������������ ���������, ������ �������� �������� � ����� ��� (%s/link_href?page=%d&param%s) 
     * 							    ��� %d ���������� �� �������� � ������� ���������� �������
     * 							    @see sprintf();
     * @return string
     */
    function pda_pager($page, $pages, $count=PAGINATOR_PAGES_COUNT, $href=false) {
		if($pages==1) {return '';}
	    $html = '<div class="pg">';
        
        $start = $page - $count;
        if($start<1) $start = 1;
        
        $end = $page + $count;
        if($end>$pages) $end = $pages;
        
        if($page > 1) {$html .= sprintf($href, '<a href=', $page-1 ,'>����������</a>&nbsp;'); if(!($page < $pages)) $html .= "<br />";}
        if($page < $pages) {$html .= sprintf($href, '&nbsp;<a href=', $page+1, '>���������</a><br />');} 
         
        for($i=$start;$i<=$end;$i++) {
            if($i == $start && $start > 1) {  $html .= sprintf($href, '<a href="', 1 ,'">1</a>&nbsp;');  if($i==3) $html .= sprintf($href, '<a href="', 2 ,'">2</a>&nbsp;'); elseif($i!=2) $html .= "&nbsp;..&nbsp;&nbsp;";}
            $html .= ($page == $i? '<span>'.$i.'</span>&nbsp;' : sprintf($href, '<a href=', $i ,'>'.$i.'</a>&nbsp;'));
            if($i == $end && $page < $pages-1 && $pages > $end && $pages <= 50) { if($pages-$end-1 == 1) $html .= sprintf($href, '<a href="', $pages-1 ,'">'.($pages-1).'</a>&nbsp;'); elseif($pages-$end-1 > 1) $html .= "..&nbsp;"; $html .= sprintf($href, '<a href="', $pages ,'">'.$pages.'</a>&nbsp;');}
            elseif($i == $end && $page < $pages-1 && $pages > $end && $pages > 50) { $html .= "&nbsp;...";}
        } 
        
        return $html.'</div>';   
    }
    
    /**
     * ���������� ������������ ��� ���
     *
     * @param array   $user      ������ ������������
     * @param boolean $is_pro    ��� ���� ��� ��� 
     * @param boolean $is_emp    ������������, ���� ��� �� ���������
     * @param boolean $is_online ������ ������������ ��� ���
     * @param string  $addopt    ��������������� ����������� 
     * @return string HTML-��� ��� ������  
     */
    function pda_info_user($user, $is_pro, $is_emp, $is_online, $addopt=false, $with_link = true, $is_view_online = true) {
        if($user['photo'] == '') {
            $photo = "/images/no_foto_25.gif";
        } else {
            $photo = WDCPREFIX.'/users/'.$user['login'].'/foto/'.$user['photo'];
        }
        
        $pda_html = '<div class="av"><img src="'.$photo.'" alt="" width="20" height="20"></div>';
        if($with_link) $pda_html = '<a href="/users/'.$user['login'].'/"> '.$pda_html.'</a>';
        $pda_html .= '<div class="u">';
        
        if($is_emp) $cls = 'class="emp"';
        else $cls = 'class="frl"';
        
        if($user['is_team'] == 't') $pda_html .= '<img class="m-team" src="/pda/images/team.gif" alt="team" /> ';
        
        if($user['is_team']!='t' && $is_pro && $is_emp) $pda_html .= '<img class="m-pro" src="/pda/images/e_pro.png" alt="pro" width="21" height="9" /> ';           
        elseif($user['is_team']!='t' && $is_pro) $pda_html .= '<img class="m-pro" src="/pda/images/f_pro.png" alt="pro" width="21" height="9" /> '; 
        if($is_view_online) {
            if($is_online) $pda_html .= '<img class="m-dot" src="/pda/images/dot_a.png" alt="�� �����" width="9" height="9" /> ';
            else  $pda_html .= '<img class="m-dot" src="/pda/images/dot_ia.png" alt="��� �� �����" width="9" height="9" /> ';
        }
        
        $pda_html .= ($with_link ? "<a {$cls} href=\"/users/{$user['login']}/\">" : '')."<strong {$cls}>".$user['uname'].' '.$user['usurname'] .' ['.$user['login'].']</strong>'.($with_link ? "</a>" : '');
        
        if($addopt) $pda_html .= $addopt;
        
        $pda_html .= '</div>';
        
        return $pda_html;
    }
    /**
     * ��� ������ ������� �� �������� ����
     *
     * @param string $href        ������ 
     * @param string $href_str    �������� ������
     * @param string $size        ������ ������
     * @param string $type        ��� �������� ��������
     * @return string
     */
    function pda_external_link($href, $href_str='', $size=40, $type=1, $title='') {
        $is_ext = $_COOKIE['pda_ext_link'] == 1?false:true;
        if($href_str=='') $href_str = $href;
        if(strlen($href_str) > $size) {
            $href_str = str_replace("http://", "", substr($href_str, 0, $size)."...");
        } else {
            $href_str = str_replace("http://", "", $href_str);
        }
        $title = urlencode($title);
        if($is_ext) {
            $href = urlencode($href);
            $href = "/a.php?a={$href}&type={$type}&title={$title}";
        }
        return "<a href='{$href}' target='_blank'>{$href_str}</a>&nbsp;<a href='{$href}' target='_blank'><img src='/pda/images/f.png' alt='������� �� �������� ����'></a>";
    }
}
?>