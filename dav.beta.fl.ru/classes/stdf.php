<?
/**
 * ���� �������� ��� �������� ������� �������, ������� ����� ���� ������� � ������ �� ������.
 */


    /**
     * ������� ����������� � �� Postgres
     * @deprecated
     *
     * @param boolean $read_only	�������� �� ���������� ����������� ������ ��� ������ @deprecated
     * @return resource				����������
     */
    function DBConnect($read_only = false, $force_new = false) {
        static $connection;
	    if($force_new || !$connection || !is_resource($connection)) {
            $db_conf = $GLOBALS['pg_db'];
            $connection =
    		    pg_connect("host=".$db_conf['master']['host']." port=".$db_conf['master']['port']." dbname=".$db_conf['master']['name']
    		               ." user=".$db_conf['master']['user']." password=".$db_conf['master']['pwd'], PGSQL_CONNECT_FORCE_NEW) or die ("could not connect to base");
        	pg_query($connection,"SET client_encoding='WIN1251';SET statement_timeout TO '90s';");
        }
        return $connection;
    }
    
	/**
     * ������� ����������� � �� Postgres ��� ��������
     *
     * @param boolean $read_only	�������� �� ���������� ����������� ������ ��� ������
     * @return resource				����������
     */
    function DBBannerConnect(){
        $db_conf = $GLOBALS['pg_db'];
    	if ($db_conf['banner']['host']){
            $connection =
        		pg_connect("host=".$db_conf['banner']['host']." port=".$db_conf['banner']['port']." dbname=".$db_conf['banner']['name']." user=".$db_conf['banner']['user']." password=".$db_conf['banner']['pwd']);
        	pg_query($connection,"SET client_encoding='WIN1251';SET statement_timeout TO '90s';");
        }
    	else {
            $connection =
        		pg_connect("host=".$db_conf['master']['host']." port=".$db_conf['master']['port']." dbname=".$db_conf['master']['name']." user=".$db_conf['master']['user']." password=".$db_conf['master']['pwd']) or die ("could not connect to base");
        	pg_query($connection,"SET client_encoding='WIN1251';SET statement_timeout TO '90s';");
        }
        return $connection;
    }
    
    /**
     * ������� ����������� � �� MySql
     *
     * @deprecated 
     * 
     * @return resourse				����������
     */
    function DBMyConnect(){
        $link = mysql_connect($GLOBALS['dbmyhost'], $GLOBALS['dbmyuser'], $GLOBALS['dbmypwd']) or $error="Could not connect to database<br />";
       // print_r($GLOBALS);
        if (!mysql_select_db($GLOBALS['dbmyname'],$link)) return false;
        return $link;
    }
    
    /**
     * ������������ ������ � Postgres
     * @deprecated
     *
     * @param string $sql			SQL-������
     * @param boolean $read_only	�������� �� ������ �������� ������ ��� ������ ������
     * @return resource				��������� ���������� �������
     */
    function pg_query_Ex($sql, $read_only = false){
        global $DB;
        return $DB->query($sql);
    }

    /**
    * �������� ����� ������, ���� ������ ������ ��� $truncation ���� � ������
    * ���� ����������� �� �������� �� ���������� �����, �� ����� �� ��������� �����������.
    *
    * @param string $string ������� ������
    * @param int $truncation �-�� ���� ����� �������� ���������� �������� ������
    * @return string
    */
    function smart_trim($string, $truncation) {
        $matches = preg_split("/\s+/", $string);
        $count = count($matches);

        if($count > $truncation) {
            //Grab the last word; we need to determine if
            //it is the end of the sentence or not
            $last_word = strip_tags($matches[$truncation-1]);
            $lw_count = strlen($last_word);

            //The last word in our truncation has a sentence ender
            if($last_word[$lw_count-1] == "." || $last_word[$lw_count-1] == "?" || $last_word[$lw_count-1] == "!") {
                for($i=$truncation;$i<$count;$i++) {
                    unset($matches[$i]);
                }

            //The last word in our truncation doesn't have a sentence ender, find the next one
            } else {
                //Check each word following the last word until
                //we determine a sentence's ending
                for($i=($truncation);$i<$count;$i++) {
                    if($ending_found != TRUE) {
                        $len = strlen(strip_tags($matches[$i]));
                        if($matches[$i][$len-1] == "." || $matches[$i][$len-1] == "?" || $matches[$i][$len-1] == "!") {
                            //Test to see if the next word starts with a capital
                            if($matches[$i+1][0] == strtoupper($matches[$i+1][0])) {
                                $ending_found = TRUE;
                            }
                        }
                    } else {
                        unset($matches[$i]);
                    }
                }
            }

            //Check to make sure we still have a closing <p> tag at the end
            $body = implode(' ', $matches);
            if(substr($body, -4) != "</p>") {
                $body = $body."</p>";
            }

            return $body; 
        } else {
            return $string;
        }
    }

    /**
    * �������� ����� �� ������. �������� ��� � strip_tags(), ������ �������� - ���������� ������ �����, ������� ���� �������
    *
    * @param    string  $str    ������ � ������� ���� ������� ����
    * @param    mixed   $tags   ������ ����� ��� ��������, ����� ���� ����� �������� ��� �������. ���������� ��� � strip_tags()
    * @return string            ������ �� ������� ������� ����
    */
    function strip_only($str, $tags) {
        if(!is_array($tags)) {
            $tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
            if(end($tags) == '') array_pop($tags);
        }
        foreach($tags as $tag) $str = preg_replace('#</?'.$tag.'[^>]*>#is', '', $str);
        return $str;
    }

    /**
     * ��������� ����� �������� �� ������� ������� (POST, GET, ��������� ����)
     * ��� ��������� ������ ����������� ��������� ���� ��� ������ ��� $max_word_size,
     * � ��� �� ���� ��������� �����, ������� ����� ������������ � �������
     * 
     * 
     * @param string  $input         ������ ��� ���������
     * @param boolean $strip_all     �������� ��� ����������� ������� ����� htmlspecialchars()
     * @param integer $max_word_size ������������ ���������� �������� � ����� @deprecated �� ������������ � �������
     * @param boolean$strip_tags     �������� ��� ����
     * @return string ������������ ������
     */
    function change_q($input, $strip_all = false, $max_word_size = 25, $strip_tags = true){
        setlocale(LC_ALL, 'ru_RU.CP1251');
        $input = trim($input);
        if ($strip_all && $strip_tags) $input = strip_tags($input);
        $input .= " ";
        if (!$strip_all) {
            $ret = strip_tags($input,"<b><br /><i><p><ul><li><cut>");
            $tags = array("'<([\/\!]*?[^ ]*)([^<>]*?)>'si");
            $repl = array("<$1>");
            $ret = preg_replace($tags, $repl, $ret);
            $tags = array("'(<br />[\s]*){3,}'si");
            $repl = array("<br /><br />");
            $ret = preg_replace($tags, $repl, $ret);
        } else $ret = htmlspecialchars($input); //! �����!
        $pat = array("/([\w]+)(\\\\\")([\W)\.]+)/","/([\W]?)(\\\\\")([\w]+)/","/([\r\n]{6,})/","/([\r\n]{1,2})/","/([^\\\"]+)(\\\\\")$/");
        $repl = array("$1&quot;$3","$1&quot;$3","\n","\n","$1&quot;");
        $ret = preg_replace($pat,$repl,$ret);
        $ret = str_replace(array("'","\""), array("&#039;","&quot;"), $ret);
        if($strip_all && $strip_tags) // ��������� ����� �� br-��. ���� ���������� �������.
          $ret = preg_replace('/<[^>]*>/', '', $ret);
        setlocale(LC_ALL, "en_US.UTF-8");
        //if ($max_word_size) $ret = textWrap($ret, $max_word_size," ");
        return trim($ret);
    }

    /**
     * ����� ������� ��� ��������� �������� ��������� ������
     * @see change_q()
     *
     * @param string  $input       ������ ��� ���������
     * @param boolean $strip_all   �������� ��� ����������� �������
     * @param boolean $strip_tags  ������� ����
     * @return string
     */
    function change_q_new($input, $strip_all = false, $strip_tags = true){
        setlocale(LC_ALL, 'ru_RU.CP1251');
        $input = trim($input);
        if ($strip_all && $strip_tags) $input = strip_tags($input);
        $input .= " ";
        if (!$strip_all) {
            $ret = strip_tags($input,"<b><br /><i><p><ul><li><cut>");
            $tags = array("'<([\/\!]*?[^ ]*)([^<>]*?)>'si");
            $repl = array("<$1>");
            $ret = preg_replace($tags, $repl, $ret);
            $tags = array("'(<br />[\s]*){3,}'si");
            $repl = array("<br />");
            $ret = preg_replace($tags, $repl, $ret);
        } else $ret = htmlspecialchars($input); //! �����!
        $pat = array("/([\w]+)(\\\\\")([\W)\.]+)/","/([\W]?)(\\\\\")([\w]+)/","/([\r\n]{6,})/","/([\r\n]{1,2})/","/([^\\\"]+)(\\\\\")$/");
        $repl = array("$1&quot;$3","$1&quot;$3","\n","\n","$1&quot;");
        $ret = preg_replace($pat,$repl,$ret);
        $ret = str_replace(array("'","\""), array("&#039;","&quot;"), $ret);
        if($strip_all && $strip_tags) // ��������� ����� �� br-��. ���� ���������� �������.
          $ret = preg_replace('/<[^>]*>/', '', $ret);
        setlocale(LC_ALL, "en_US.UTF-8");
        return trim($ret);
    }
    
	/**
	 * ������������ � ������ ��������� � �������, ��� ���������� ������ � �� � ��������� SQL-injection
	 *
	 * @deprecated 
	 * 
	 * @param string $input ������ ��� ���������
	 * @return string
	 */
    function change_quotes($input){
        setlocale(LC_ALL, 'ru_RU.CP1251');




        $input = trim($input);
        $input .= " ";
        $pat = array("/([\w]+)(\\\\\")([\W)\.]+)/","/([\W]?)(\\\\\")([\w]+)/","/([\r\n]{6,})/","/([\r\n]{1,2})/","/([^\\\"]+)(\\\\\")$/");
        $repl = array("$1&quot;$3","$1&quot;$3","\n","\n","$1&quot;");
        $ret = preg_replace($pat,$repl,$ret);
        setlocale(LC_ALL, 'en_US.UTF-8');
        return trim($ret); //preg_replace($pat1,$repl,$ret);
    }
    
	/**
	 * ����� ����� ������� ����� �� ����� ����� ������
	 *
	 * @param string  $input     ������ ��� ���������
	 * @param integer $max_chars ������������ ���������� �������� � �����
	 * @return string
	 */
    function parse_words($input, $max_chars){
        $ret = wordwrap($input, $max_chars, " ", 1);
        return $ret;
    }

    /**
     * ��������������� callback-������� ��� {@link textWrap()}.
     * ��� ������ ������ ���������� ���������������� ���������� ����������, ������ ������� �� ������ ����������.
     * @see textWrap()
     * 
     * @staticvar integer $ns_limit  	  ������������ ����� �����.
     * @staticvar integer $left_nospaces  ������� ������������ ������������������, ������������ � ���������� ����. �� ����, ���������� ��������,
                                   		  ������� ���� ����� ������ � ������ ����.
     *
     * @param array $matches           ������� [2] -- ����� ��� �����, �������� ����� �������������� ���� ����� �
     *                                 ��������� �� ����� -- ������� [1]. 
     * @param integer $max_word_size   ������������ ����� �����. ���������� ���� ��� ��� �������������.
     * @return string   ����� ������������������ ������.
     */
    function textWrap_callback($matches, $max_word_size=NULL)
    {
        static $ns_limit = 100,    
               $left_nospaces = 0; 

        if($max_word_size) {
            // ������������� ����������.
            $ns_limit = $max_word_size;
            $left_nospaces = 0;
            return;
        }

        $tags = $matches[1];
        $txt  = $matches[2];

        if ( 
            preg_match('~(?:<(?:(?:br|li)|/?(?:p|ul))|>\s)~i', $tags) 
            || preg_match('#'.  preg_quote(CENSORED).'#', $tags) 
        ) { // ������, ����� ����� �������� �������. �� ����, �������, ��� �������� ����� �����.
            $left_nospaces = 0;
        }
    
        $cur_ns_limit = $ns_limit - $left_nospaces;     // ������������ ������ ����� ������� � �� �������.

        if(($len = strlen($txt)) <= $cur_ns_limit) {    // ������� ����� ������ �� ������� �� ������� ������������ ������� �����.
            $left_nospaces += $len;                     // ������������, ��� � $txt ��� ��������.
            if(($rSpcPos = strrpos($txt, ' '))!==FALSE)
                $left_nospaces -= ($rSpcPos + 1);       // � ���� ����, �� ������� ������� �� ������ ������� �������.
            return $tags.$txt;
        }

        if($left_nospaces) {
            $tail = preg_replace('/^[^\s]{'.$cur_ns_limit.'}(?!\s)/', '$0 ', substr($txt, 0, $cur_ns_limit + 1));
            $rSpcPos = strrpos($tail, ' ');
            $leftTail = ($rSpcPos!==FALSE) ? substr($tail, 0, $rSpcPos+1) : '';
            $rightTail = ($rSpcPos!==FALSE) ? substr($tail, $rSpcPos+1) : $tail;
            $txt = $leftTail.preg_replace('/[^\s]{'.$ns_limit.'}(?!\s|$)/', '$0 ', $rightTail.substr($txt, $cur_ns_limit + 1));
        }
        else
            $txt = preg_replace('/[^\s]{'.$ns_limit.'}(?!\s|$)/', '$0 ', $txt);

        $left_nospaces = strlen($txt) - (strrpos($txt, ' ') + 1);

        return $tags.$txt;
    }

    /**
     * ����� HTML-����� ���������.
     * ����� ������ ������� ����� ��������� � HTML-������, �� �������
     * ����� ��������� � ���� (<b r>, ��������� � href ������ � �.�.) � ��������.
     * ����� ��������� block-���� (p, br, ul � �.�.). ���� ����� ����������, �� ���������, ��� �������� ����� �����.
     * @see textWrap_callback()
     *
     * @param string  $body �����
     * @param integer $size ������ ������ 
     * @return string   ����������������� �����.
     */
    function textWrap($body, $size) {
        $body = preg_replace('/[!?()\[\]\/\\\;]{'.$size.'}/', '$0<br />', $body); // ������� �������, ����� ������� ������� (������� �� ��������).
        textWrap_callback(NULL, $size);
        $body = preg_replace_callback("/((?:<[^\s>][^>]*>\s*|".  preg_quote(CENSORED)."\s*)*)((?:[^<]*(?:<[>\s])*)*)/", 'textWrap_callback', $body);
        /// ��������� ���������. ���� ����� �������, �������� � �� �.
        /// ������� �:
        /// // $body = preg_replace('/& ?(?:#x?(?: ?\d){2,4}|[a-z](?: ?[\da-z]){1,7}) ?;/ie', "strpos('\\0', ' ') !== false ? str_replace(' ', '', '\\0').' ' : '\\0'", $body, -1, $ecnt);
        /// ������� �:
        textWrap_callback_e(NULL, $size);
        // ����� ������� ������ ������, �.�. � ��� �������� ����� ����������� ����� 555 - 6543 - 2345
		// ���� ���-���� ����� ���, �� ����� ���-������ ���������, ����� ����������� ��� �������� (JB)
		/*$body = preg_replace_callback('/(?> ?(?>& ?(?:#(?: ?x)?(?: ?\d){2,4}|[a-z](?: ?[\da-z]){1,7}) ?;)+)+/i', 'textWrap_callback_e', $body);*/
		$body = preg_replace_callback('/(?>& ?(?:#(?: ?x)?(?: ?\d){2,4}|[a-z](?: ?[\da-z]){1,7}) ?;)+/i', 'textWrap_callback_e', $body);
        /// �����.
        return $body;
    }
    
    /**
     * ����� �������� �������� ����� ������ textWrap().
     * ������� ������ ������� ������ ��������, � ����� ��������� ������� �������� ������������������ � ����� �������.
     * @see textWrap().
     *
     * @param array $m   ������� preg_replace_callback, ��. textWrap().
     * @param integer $_size   ������������ ����� �����. ���������� ���� ��� ��� �������������.
     *
     * @return string   ����������������� �����.
     */
    function textWrap_callback_e($m, $_size = NULL) {
        static $size = 100;
        if($_size) {
            $size = $_size;
            return;
        }
        $txt = $m[0];
        if(($sp = strpos($txt, ' '))===false) return $txt;
        $sl = strpos($txt, ';', $sp);
        $out = str_replace(' ', '', substr($txt, 0, $sl+1)).' '; // ��������� ������ ������ �� ��������� ������� �� ��������.
        if(($spr = strrpos($txt, ' ')) != $sp) {
            if($slr = strrpos($txt, ';', $spr-strlen($txt))) {
                $out .= str_afternstr(str_replace(' ', '', substr($txt, $sl+1, $slr-$sl)), $size)
                     .' '; // ��������� ��������� ������ �� ��������� ������� �� ��������.
                $sl = $slr;
            }
        }
        $out .= str_replace(' ', '', substr($txt, $sl+1));
        return $out;
    }
    
    /**
     * ��������� � �������� ������ ������ $rs ����� ������� $n-�� ������� $ls.
     *
     * @param string $txt   �������� ������.
     * @param integer $n   ���������� ��������, ������� ����� ���������� ����� ��� ��� �������� $rs.
     * @param string $ls   ������, ����� �������� ��������������� ����� ������.
     * @param string $rs   ������ (������), ������� ����� ��������.
     *
     * @return string   ����� ������.
     */
    function str_afternstr($txt, $n, $ls = ';', $rs = ' ') {
        $llen = strlen($ls);
        $out = '';
        $pos=$len=$i=0;
        while(($pos = strpos($txt, $ls, $pos)) !== false) {
            $pos+=$llen;
            if(++$i >= $n) {
                $out .= substr($txt, $len, $pos-$len).$rs;
                $len = $pos;
                $i = 0;
            }
        }
        if($i < $n)
            $out .= substr($txt, $len);
        return $out;
    }


    /**
     * ����� {@link reformat()}
     * @deprecated
     */
    function reformat2() { $args = func_get_args(); return call_user_func_array('reformat', $args); }

    /**
     * �������� ������� ��� �������������� ������� �� ������.
     * 
     * @see textWrap()
     * @see reformat_callback()
     *
     * @param $input           string    �������� �����.
     * @param $max_word_size   integer   ������������ ���-�� ������������ �������� � ����� (��� ����������,
     *                                   ����� ������� ��������� {@link textWrap()}).
     * @param $cut             boolean   ������������ �� ��� <cut>:
     *                                   true: ��� � ��� ��� ���������� ���������� �� '...';
     *                                   false: ������� ���� �� ��������� ������.
     * @param $nolink          boolean   ������������ ������:
     *                                   1:  ������ ����������� ������ (��� ��������� http://www..... �������� ������ �������);
     *                                   0:  �����, ������������ � ������ URI ������������ ������ <a>. ����
     *                                       ��� ���������� �����, �� ������ ������, ����� ����������� �����
     *                                       ������������� �������� ({@link reformat_callback()}).
     *                                   -1: ��� ���������� {@link globals.php HYPER_LINKS} ����� ������������
     *                                       ����. ������, ��� ������ �����-������. � ������ ������ ������ ���� ������ ������.
     * @param $space           boolean   ���� true, �� �������������� ������������������ � ��� ������� (��. ������).
     * @param $max_link_len    integer   �� ����� ������� �������� ����� ������ ������ � ��������
     *                                   ��� �� '...'. ����� �������� ������ ��� $nolink==false.
     * @return string                    ����������������� �����.
     */
function reformat($input, $max_word_size = 100, $cut = 0, $nolink = 0, $space = 0, $max_link_len = 25, $is_wysiwyg = false) {
        $out = wysiwyg_video_replace($input, $wysiwyg_videos, true);
        $out = wysiwyg_image_replace($out, $wysiwyg_images, true); //��������� � $wysiwyg_images ����������� � ������� wysiwyg_images  
        $out = wysiwyg_code_replace($out, $wysiwyg_codes);
        $out = str_replace(array(' - ', ' -- ', "\r\n", "\n"), array(' &#150; ', ' &mdash; ', ' <br />', ' <br />'), $out);
        $out = str_replace("\r", ' <br />', $out);//� ������ ������ �������� ������ ������-�� �������� ������ ���
        // ��� ��� ��������� ��� ��� � �������� ��� �� �� ����� ������ � �� �������� �� ������ #0016101
        /*list($out, $code) = str_replace_mask('#((<p\sclass="code.*?>)(.*?)(<\/p>))#mix', $out);
        $code = str_replace("\n", "<br />", $code);*/
        if(!$is_wysiwyg) $out = str_replace('&nbsp;', ' ', $out);
        if($cut) {
            $out = preg_replace('~<cut[^>]*?>.*?(?:</cut>|$)~si', '... ', $out); // ����������� ������ �� �����, ����� ������ �� ��������� (http://zzz.com...text�����cut).
            $out = preg_replace('~<\!-- -W-EDITOR-CUT- -->.*?$~si', '... ', $out);
        } else {
            $out = preg_replace('~<[/!]*cut[^>]*>~i', ' ', $out);
            $out = preg_replace('~<\!-- -W-EDITOR-CUT- -->~si', ' ', $out);
        }
        if($nolink <= 0) {
            $GLOBALS['reformat.max_link_len'] = $max_link_len;
            $GLOBALS['reformat.can_hyper'] = ($nolink == -1);
            $hre = HYPER_LINKS ? '{([^}]+)}' : '()'; // ����������� � globals.php.
            // ������� ������������ ��� ������ ����������� ������ <a>
            $out = preg_replace_callback('~<a[^>]+?href=\"(https?:/(' . $hre . ')?/|www\.)(([\da-z-_�-���-ߨ]+\.)*([a-z�-�]{2,15})(:\d+)?([/?#][^"\s<]*)*)\"[^>]*?>.*?</a>~i', 'reformat_callback', $out);
            list($out, $url) = str_replace_mask('#((<a[^>]*>)(.*?)(<\/a>))#mix', $out);
            $out = preg_replace_callback('~(https?:/(' . $hre . ')?/|www\.)(([\da-z-_�-���-ߨ]+\.)*([a-z�-�]{2,15})(:\d+)?([/?#][^"\s<]*)*)~i', 'reformat_callback', $out);
            $out = str_unreplace_mask($url, $out);
        }
        if($max_word_size)
            $out = textWrap($out, $max_word_size);
        if($space) {
            $mx=7;
            do { $out = str_replace('  ', '&nbsp; ', $out, $cnt); } while($cnt && --$mx>=0);
            if($out[0]==' ') $out = '&nbsp;'.substr($out, 1);
        }
        // #0016101
        //$out = str_unreplace_mask($code, $out);
        $out = wysiwyg_code_restore($out, $wysiwyg_codes);
        $out = wysiwyg_image_restore($out, $wysiwyg_images);//������� ����������� � ������� wysiwyg_images 
        $out = wysiwyg_video_restore($out, $wysiwyg_videos);
        return $out;
    }
        
    /**
     * �������������� ��������� ��� ������ ������ �� ��������
     * @param string $input
     */
    function reformatExtended ($input) {
        $input = preg_replace("~<(\/?(?:html|body|title|description|keywords))>~", "&lt;$1&gt;", $input);
        return $input;
    }
    
    /**
     * ������� �������� ������������ ����� ������ � �������� �� �� ���������� ��� 
     * ������� � ����������� ���������� ������� ������� ����� ����� ������� str_unreplace_mask();
     * ! ����� �� ������ �������� ����� ������ ������� str_unreplace_mask 
     * 
     * @param string $mask    ����� ������ � ������
     * @param string $out     ����� ��� ���������� ������
     * @return array[string, array] ���������� ������ � ������� ������ ������� ��� ��� ���������� ����� ������ �����, ������ ������ � ����������� ������� �� ������ 
     */
    function str_replace_mask($mask, $out) {
        if(preg_match_all($mask, $out, $match)) {
            foreach($match[1] as $k=>$val) {
                $ucode = substr(md5($val), 0, 7);
                if(isset($match[2]) && isset($match[4])) {
                    $ph = md5(time())."_code";
                    $content = change_q_x($match[3][$k], false, true, 'strike|cut|b|strong|em|br|u|i|p(|\s'.$ph.'_.*?)|ul|ol|li|s|h[1-6]{1}', false, false);
                    $ret[$ucode] = $match[2][$k] . $content . $match[4][$k];
                } else {
                    $ret[$ucode] = $val;
                }
            }
            
            $f =  create_function('$matches', 'return substr(md5($matches[1]), 0, 7);');
            $out = preg_replace_callback($mask, $f, $out);
            return array($out, $ret);
        }
        
        return array($out, array());
    }
    
    /**
     * ������� ���������� ��� �� ����� ����� ������������� ������� str_replace_mask
     * 
     * @param array $ret !���������� ������ ������ ������� ������� ������� str_replace_mask
     * @param array $out ����� � ������� ��������
     * @return string
     */
    function str_unreplace_mask($ret, $out) {
        if(count($ret) > 0) {
            return str_replace(array_keys($ret), array_values($ret), $out);
        }
        return $out;
    }
    
    /**
     * ��������������� callback-������� ��� ���������� URI ������ <a>.
     * ������� � ����� ������ "������" �������, �������� ����� � ����������� � ����� '...'.
     * ���� ��� ���������� ����� ������� ��� ���������� $_SESSION['direct_external_links'] �����������,
     * �� ������ ������, ����� � href ����������� ����� ������������� ��������.
     * ������������ ��������� �����-������.
     * @see reformat()
     *
     * @param $matches         array     ������ ����������� ��������� ������.
     * @return string                    ��� <a> � ����������� � ���� �������.
     */
    function reformat_callback($matches) {
        static $host;
        $max_link_len = $GLOBALS['reformat.max_link_len'];
        $can_hyper = $GLOBALS['reformat.can_hyper'];
        
        if($matches[1] == "https://") $http = "https://";
        else $http = "http://";
        
        if(!$host) $host = preg_quote(preg_replace('~^(http://|https://)?(www\.)?~', '', $GLOBALS['host']), '/');

        preg_match('/^(.*?)((?:&lt;|&quot;|&#039|[,.!<])*)$/i', $matches[4], $x); // ����������� ����� �������, ����� '...' �� ���� <cut> �� ������ � ������.
        $www = ($matches[1] == 'www.' ? 'www.'. $x[1] : $x[1]);
        $end = $x[2];
        $classCut = "";
        if($can_hyper)
            $hyper = $matches[3];
        // ���� ���� �������� ������ � ���� <a>, �� $text ����� �� ���
        if (preg_match('~^<a[^>]*>(.+?)</a>$~i', $matches[0], $textMatch)) {
            $text = $textMatch[1];
            if (strlen( strip_tags($text) ) >= $max_link_len) {
//                close_tags($text , 's,i,b,h1,h2,h3,h4,h5,h6,strong,strike,em', $max_link_len);
//                $text .= "...";
                $classCut = "b-post__link_width_200";
            }
        }
        elseif($hyper) {
            $text = $hyper;
        }
        else {
            $links = $GLOBALS[LINK_INSTANCE_NAME];
            if($links && $links instanceof links) {
                if(preg_match('/^(www\.)?'.$host.'/i', $www)) {
                    $link = $links->save_find($matches, $is_found, $max_link_len);
                    if($is_found)
                        return $link;
                }
            }
            $text = substr_quasi($www, 0, $max_link_len, $qlen, $tlen);
            if($qlen >= $max_link_len && strlen($www) > $tlen) {
                $text .= '...';
                $end = preg_replace('/^\.{1,3}(?!\.)/', '', $end);
            }
            $text = str_replace(array('<','>'), array('&lt;','&gt;'), $text);
        }
        
        $bNoAphp = $_SESSION['uid'] ? ($_SESSION['direct_external_links'] == 1) : ($_COOKIE['no_a_php'] == 1);
        
        if(preg_match('/^([a-z\d-]+\.)*'.$host.'/i', $www, $domain) || $GLOBALS['PDA'] || $hyper || $bNoAphp){
            $img = '';
            $link = '<noindex><a href="'.($domain[0] ? HTTP_PREFIX : $http).$www.'" rel="nofollow" title="'.$http.$www.'" class="b-post__link  '.$classCut.'" target="_blank">'.$text.'</a></noindex>'.$end;
            if($GLOBALS['PDA']) {
                if($domain[0]){// � ������ ��������� ������
                    if(strtolower($domain[1]) != 'p.'){// ����� �������� ����� pda 
                        $img = '<a href="'.$http.$www.'" target="_blank"><img src="/images/lnk.png" width="16" height="16" alt="" /></a>';
                    }
                }else{ // ����-�� �� �������
                    $img = '<a href="'.$http.$www.'" target="_blank"><img src="/images/out_lnk.png" width="16" height="16" alt="" /></a>';
                } 
            }
            return $img.$link;
        }
        
        preg_match( '@^(?:http://)?([^/\?]+)@i', $www, $matches );
        $thost = $matches[1];
        
        preg_match( '/[^.]+\.[^.]+$/', $thost, $matches );
        
        if ( $GLOBALS['disable_link_processing'] || in_array($matches[0], $GLOBALS['white_list']) ) {
            // ���� � ����� ������ - ���� ������ ������
            $sOut = '<noindex><a href="'.$http.htmlspecialchars_decode($www).'" class="b-post__link b-post__link_ellipsis '.$classCut.'" target="_blank" rel="nofollow" title="'.$http.$www.'">'.$text.'</a></noindex>'.$end;
        }
        else {
            // ����-�� �� ������� - ������������ �� a.php
            $scheme = is_https()?"https":"http";
            
            // ��������� ���, ���� �� ��������� �� ����� �����
            $hs = $scheme.'://'.preg_replace("/https?:\/\//i",'', str_replace('old.www.fl.ru', 'www.fl.ru', $GLOBALS['host']) );
            $sOut = '<noindex><a href="'.$hs.'/a.php?href='.urlencode($http.htmlspecialchars_decode($www)).'" class="b-post__link b-post__link_ellipsis '.$classCut.'" target="_blank" rel="nofollow" title="'.$http.$www.'">'.$text.'</a></noindex>'.$end;
        }
        
        return $sOut;
    }

    /*
     * ����������� ������������ ������� ��� XML � ����
     *
     * @param    string     $str    ����� ��� ��������������
     * @return   string             �������� ����� ��� XML
     */
    function xmloutofrangechars($str) {
        $str = preg_replace('/([\x00-\x08\x0B\x0C\x0E-\x1F])/', '', $str);
        return preg_replace_callback('/([^\x09\x0A\x0D\x20-\xFF])/', 'xmloutofrangechars_callback', $str);
    }

    /*
     * ��������������� ������� ��� xmloutofrangechars (callback)
     *
     * @param    string     $str    ����� ��� ��������������
     * @return   string             �������� ����� ��� XML
     */
    function xmloutofrangechars_callback($m) {
        return "&#x".bin2hex($m[1]).";";
    }


    /**
     * �������� �� ������ ���������, �������� � �������� ������ ��� ���������� �� ��������� ������� �� N ��������.
     * ������: substr_quasi('ABC&quot;D&nbsp;EF', 4, 3) ������ 'D&nbsp;E'
     * 
     * @param $str          string    �������� ������.
     * @param $offset       integer   >=0, ��������� ������� (������ ������� � ������ N).
     * @param $len          integer   false|>0, ����� ��������� (� ������ N). ���� false, �� �� $offset �� ����� �������� ������.
     * @param $quasi_len    integer   ���������� ���������� �������� � ������������ ��������� � ������ N. �����
     *                                �����������, ���� $len ���������� (false).
     * @param $exact_len    integer   ���������� �������� ����� ������������ ��������� (�� ��, ��� strlen).
     * @param $symbols_cnt  integer   N -- �� ������� �������� ������� ������.
     * @param $pattern      string    ������, ���������� �� �������� ������� �� N ��������. �� ���������, HTML-��������.
     * @return $string                ���������.
     */
    function substr_quasi($str, $offset, $len = false, &$quasi_len = 0, &$exact_len = 0, 
                          $symbols_cnt = 1, $pattern = '/&(?:[a-z]{2,6}|#(?:\d{2,4}|x[\da-f]{2,4}));/i')
    {
        $sub = '';
        if($offset > 0) {
            substr_quasi($str, 0, $offset, $u, $olen, $symbols_cnt, $pattern);
            $str = substr($str, $olen);
        }
        if($len === false) $len = strlen($str);
        if($len > 0) {
            $parts = preg_split($pattern, $str, -1, PREG_SPLIT_OFFSET_CAPTURE);
            foreach($parts as $p) {
                if($p[1]) {
                    if(($xl = $quasi_len + $symbols_cnt) > $len)
                        break;
                    $sub .= substr($str, $exact_len, $p[1]-$exact_len);
                    $quasi_len = $xl;
                    $exact_len = $p[1];
                }
                $quasi_len += ($xl = strlen($p[0]));
                $exact_len += $xl;
                if(($ex = $quasi_len-$len) >= 0) {
                    $exact_len -= $ex;
                    $quasi_len -= $ex;
                    $sub .= substr($p[0], 0, $xl-$ex);
                    break;
                }
                $sub .= $p[0];
            }
        }
        return $sub;
    }

	/**
	 * ������ ������ ���� ������ �� ������������ ����� ������� ������������ � ������ (��������� ����)
	 * ����������� ���������� �������� ������
	 *
	 * @param string $error  ������ �� ������
	 * @param array  $ex_err ���������� ������ � ������� �� ������������ ������
	 * @return string �������� ������
	 */
    function parse_db_error($error, &$ex_err=NULL){
        $ret = $error;
        if (stristr(trim($error),"users_login_key")) {
            $ex_err['login_ex'] = 1;
            $ret =  "������������ � ����� ������� ��� ����������";
        }
        if (stristr(trim($error),"mail")) {
            $ex_err['email_ex'] = 1;
            $ret = "������������ � ����� ����������� ������ ��� ����������";
        }
        if (stristr(trim($error),"date/time field value out of range:") || stristr(trim($error),"syntax for type date"))
        $ret = "date";
        if (stristr(trim($error),"numeric field overflow") || stristr(trim($error),"overflow on numeric"))
        $ret = "���� ��������� �����������";
        if (stristr(trim($error),"teams_pkey")||stristr(trim($error),"teams_user_id_key"))
        $ret = "���� ������� ��� � ���������";
        if (stristr(trim($error),"ignor_pkey"))
        $ret = "���� ������� ��� � ������";
        if (stristr(trim($error),"numeric"))
        $ret = "���� ��������� �����������";
        return ($ret);
    }
	
    /**
     * ������� ��� �������� ������������ �� ���
     *
     * @global $is_banned 	���������� � ���������� ����������� ���� �����. ���� ��� ��������� �������� -1, 
     * 						�� � ������ ������� ���� �������� ����� �������, ����� ������ ������������ �������� banned.php
     * 						������������ ������� ��� �������
     * 
     * @param integer $uid �� ������������
     */
    function is_banned($uid)
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        global $is_banned;
        $user = new users();
        if ($is_banned === -1) {
            $is_banned = ($user->GetField($uid, $ban_error, "is_banned", false)>0)?true:false;
        }
        if ($is_banned && !$GLOBALS['already_banned'])
        { 
            $GLOBALS['already_banned']=$uid;
            logout();
            include($_SERVER['DOCUMENT_ROOT'] . "/banned.php"); exit;
        }
    }


    /**
     * ���������� UID ��������������� �����. ���� ���� ������������� � � ���� �����
     * "���������", �� ���������� ���. ����� ���������� ������� URI �������� � ������,
     * ����� ����� ������������� ��������� ����� �� ��� ��������.
     * ��������! ���������� UID �����, ���� ������� ���. ���� ����������
     * 	0 - �� ������
     *  -1 - ������� �������
     *  -2 - ������� �������������
     *  ������������� �������� ���������� ������ ��� ������ � "�����������"
     *
     * @param boolean $set_uri		��������� �� ������� URI �������� � ������
     * @return integer				UID �����
     */
    function get_uid($set_uri = true){
        if(defined('NEO')) { return xFront::creaker()->triggerFunction(__FUNCTION__, func_get_args()); }
        $fid = 0;
        $fid = isset($_SESSION['uid'])?$_SESSION['uid']:'';
        $ip = getRemoteIP();
		if ($fid) {
            is_banned(intval($fid));
		} else {
            // ��������� �����, ������������ � get/post �������� ��� ������.
            if (!$_SESSION['rand']) {
                $_SESSION['rand'] = csrf_token();
            }
		}
        /*if ($fid) {
			if(!isset($_SESSION['user_ip']) || $_SESSION['user_ip']!=$ip) {
				$GLOBALS['session']->logout($_SESSION['login']);
				session_unset();
				$fid = 0;
			}
        }*/
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        if ($fid) {
			if(!isset($_SESSION['user_ip']) || $_SESSION['user_ip']!=$ip) {
                users::SaveLoginIPLog($fid,$ip);
                if(!users::CheckUserAllowIP($ip,$fid)) {
                    // IP ��������
    				$GLOBALS['session']->logout($_SESSION['login']);
    				session_unset();
    				$fid = 0;
                    header('Location: /denyip.php?login='.$_POST['login']);
                    exit;
                }
			}
        }
        if (!$fid && (int)$_COOKIE['id'] && !headers_sent()) {
            if ($_COOKIE['pwd'] && $_COOKIE['pwd'] == users::cookieHashPasswd($_COOKIE['id'], $pwd_data)) {
                $fid = login($_COOKIE['name'], $pwd_data['pwd'], 1, false);
            } else {
                uncookie(); // ��������, ������ ������� � � ���� ������ �������.
            }
            is_banned(intval($fid));
        } else
        if ($set_uri) {
            $_SESSION['ref_uri'] = urlencode(isset($_SERVER['HTTP_ORIGINAL_URI']) ? $_SERVER['HTTP_ORIGINAL_URI'] : $_SERVER['REQUEST_URI']);
        }
        return(intval($fid));
    }
	
    /**
     * ���������� ����� ������������ �� ��� UID
     *
     * @param integer $uid  	�� ������������
     * @param integer $anon 	���������� ����������� ������ ������ ������� �������, 
     * 							���� �� ������������ � ������ �� �������� � �� ������������ ����������� �������
     * 							���� 0, �� ������� �� �������, ���� 1 �� ���� �������
     * @return string 			����� ������������, ���� ��� ����������� ������������ (� ������ ������ Anonymous)
     */
    function get_login($uid, $anon = 0){
        if ($_SESSION['uid'] == $uid ) $ret = $_SESSION['login'];
        if ($anon && $_SESSION['uid'] != $uid) $ret = 'Anonymous';
        return($ret);
    }

    /**
     * ������������ �����. ���������� UID �����. ���������� ���� "����������".
     * ��������! ���������� UID �����, ���� ������� ���. ���� ����������
     * 	0 - �� ������
     *  -1 - ������� �������
     *  -2 - ������� �������������
     *  -3 - IP �� ������ � ������ IP ����������� �������������
     *  -4 - ��������� 2�� ���� �����������
     * 
     * @param string $login				����� �����
     * @param string $pwd				������
     * @param integer $autologin		���� "����������"
     * @return integer					UID �����
     */
    function login($login, $pwd, $autologin = 0, $annoy_check = true) 
    { 
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/annoy.php");
        $annoy = new annoy();        
        $user = new users();

        if($annoy_check) {

            if(intval($_SESSION['login_wait_time'])>time()) {
                $redirect_checkpass = true;
            } else {
        		// ��������� ������� ��� �������� ������
                if(isset($_SESSION['login_wait_time'])) {
                    $annoy->Clear(getRemoteIP());
                }
                $login_count = $annoy->Check(getRemoteIP());
                if ($login_count >= $GLOBALS['max_login_tries']){
                    $_SESSION['login_wait_time'] = time()+$GLOBALS['login_wait_time']*60;
                    $redirect_checkpass = true;
                }
            }
            if($redirect_checkpass==true) {
                header ("Location: /checkpass.php");
                exit;
            }
        }

        
        //�������� ����� � ��������� �������
        $_uid = $user->getUidByLoginEmailPhone($login);
        if ($_uid > 0 && !$annoy->allowRepeatPass($_uid)) {
            header ("Location: /banned.php");
            exit;              
        }        
        
        
        //@todo: ��� �������� ������� ������
        $t_filter_prj = $_SESSION['f_project_filter'];
        $t_filter_frl = $_SESSION['f_freelancers_filter'];
        $t_ref_uri2 = $_SESSION['ref_uri2'];

        $adCatalog = $_SESSION['toppayed_catalog'];
        $adMain = $_SESSION['toppayed_main'];
        $adHead = $_SESSION['toppayed_head'];
        $adText = $_SESSION['toppayed_text'];

        $masssending = $_SESSION['masssending'];
		
		$newPrjName = $_SESSION['new_project_name'];
		$newPrjCost = $_SESSION['new_project_cost'];
        
        //��������� �������� ���� 2�������� ����������� ����� �������� ������
        $_2fa_provider = isset($_SESSION['2fa_provider'])? $_SESSION['2fa_provider'] : null;
        $_2fa_redirect = isset($_SESSION['2fa_redirect'])? $_SESSION['2fa_redirect'] : null;

        //��������� ���� ������ �� ��� ��������������� ������ ��� ����� ��� ������/�����
        $_ga_stat_url_hash = isset($_SESSION['ga_stat_url_hash'])? $_SESSION['ga_stat_url_hash'] : null;
        
        //��������� ���������� ��������
        $_ref_uri = isset($_SESSION['ref_uri'])? $_SESSION['ref_uri'] : null;
        
        
        $_pda = isset($_SESSION['pda'])? $_SESSION['pda'] : null;
        
        
        $_customer_wizard = isset($_SESSION['customer_wizard'])? $_SESSION['customer_wizard'] : null;
        
        
        session_unset();

        
        if ($_customer_wizard) {
            $_SESSION['customer_wizard'] = $_customer_wizard;
        }
        
        
        if ($_pda) {
            $_SESSION['pda'] = $_pda;
        }
        
        
        if ($_ref_uri) {
            $_SESSION['ref_uri'] = $_ref_uri;
        }
        
        
        //�������������� �������� ����� ������
        if ($_ga_stat_url_hash) {
            $_SESSION['ga_stat_url_hash'] = $_ga_stat_url_hash;
        }
        
        
        //�������������� �������� 2�������� ����������� ����� ������� ������
        if ($_2fa_provider !== null) {
            $_SESSION['2fa_provider'] = $_2fa_provider;
        }
        
        if ($_2fa_redirect !== null) {
            $_SESSION['2fa_redirect'] = $_2fa_redirect;
        }        
        
        //��������� �������� 2��� ����� ����������� 
        //���� ��� ���� �� ���� ��� ��� ������ (�� ������ �� ������������) 
        //��� �� ��� ������� ��� 2����� �������� ����� ������
        $is_2fa_off = $annoy_check === false;
        
        //�����������
        $id = $user->Auth($login, $pwd, $_SESSION, $is_2fa_off);

        //������� � ��������� �� ���������� ��������� �����������
        if (!$id && $_uid > 0) {
            $annoy->wrongRepeatPass($_uid);
        }
        
        //�������� �����������
        if ($id > 0) {
                $annoy->clearRepeatPass($id);
            
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
                $pro_last = payed::ProLast($_SESSION['login']);
                
                //������� �� ������������ ��� 1 ��� � ����� ��� ��������
                if (!is_emp()) {
                    $_SESSION['is_was_pro'] = ($pro_last) ? true : payed::isWasPro($_SESSION['uid']);
                }
                
                $_SESSION['pro_last'] = $pro_last['is_freezed'] ? false : $pro_last['cnt'];
                if ($_SESSION['pro_last'] && $_SESSION['is_pro_new'] != 't') {
                    payed::checkNewPro($id);
                }
                
                if ($pro_last['freeze_to']) {
                    $_SESSION['freeze_from'] = $pro_last['freeze_from'];
                    $_SESSION['freeze_to'] = $pro_last['freeze_to'];
                    $_SESSION['is_freezed'] = $pro_last['is_freezed'];
                    $_SESSION['payed_to'] = $pro_last['cnt'];
                }
                
                if($_SESSION['anti_login']) {
                    $pro_last = payed::ProLast($_SESSION['anti_login']);
                    $_SESSION['anti_pro_last'] = $pro_last['freeze_to'] ? false : $pro_last['cnt'];
                }
                
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
                $memBuff = new memBuff();
                $memBuff->delete("msgsCnt{$id}");
                
                //��������� ������������ ����������
                if (!is_emp()) {
                	require_once (ABS_PATH."/classes/freelancer.php");
                    $specData = freelancer::getAllSpecAndGroup($id, is_pro());
                    $_SESSION['specs'] = $specData['specs']; //������ �������������
                    $_SESSION['groups'] = $specData['groups']; //������ �����
                    //@todo: ���� �� ������������
                    //$_SESSION['specs_tree'] = $specData['specs_tree']; //����� ����� ������ > �������������
                }

                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
                $iDraftsCount = drafts::getCount( $id );
                $_SESSION['drafts_count'] = $iDraftsCount;

            if ($autologin == 1) {
                setcookie('id', $id, time()+60*60*24*30, '/', $GLOBALS['domain4cookie'], COOKIE_SECURE, true);
                setcookie('name', $login, time()+60*60*24*30, '/', $GLOBALS['domain4cookie'], COOKIE_SECURE);
                setcookie('pwd', users::cookieHashPasswd($id), time()+60*60*24*30, '/', $GLOBALS['domain4cookie'], COOKIE_SECURE, true);
            }

            $_SESSION['f_project_filter'] = $t_filter_prj;
            $_SESSION['f_freelancers_filter'] = $t_filter_frl;
            $_SESSION['ref_uri2'] = $t_ref_uri2;

            $_SESSION['toppayed_catalog'] = $adCatalog;
            $_SESSION['toppayed_main'] = $adMain;
            $_SESSION['toppayed_head'] = $adHead;
            $_SESSION['toppayed_text'] = $adText;
            
            if ($masssending) {
                $_SESSION['masssending'] = $masssending;
            }
            
			$_SESSION['new_project_name'] = $newPrjName;
			$_SESSION['new_project_cost'] = $newPrjCost;

            if($t_filter_prj) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_filter.php");
                $prj_filter = new projects_filters();
                $prj_filter->SaveFromAnon();
            }
            if($t_filter_frl) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancers_filter.php");
                $frl_filter = new freelancers_filters();
                $frl_filter->SaveFromAnon();
            }

            
            //var_dump($_SESSION['customer_wizard']);
            //var_dump($_SESSION['customer_wizard_filled']);
            //exit;

            //���� �������� �������� ������������ ������ ����� ������ �� ��������� ����� �����������
            if (is_emp() && isset($_SESSION['customer_wizard']['filled'])) {
                require_once(ABS_PATH . '/guest/models/GuestActivationModel.php');
                $_SESSION['ref_uri'] = GuestActivationModel::model()->published($id, $_SESSION['email']);
            }
        }
        // ��������� �����, ������������ � get/post �������� ��� ������.
        if (!$_SESSION['rand']) {
            $_SESSION['rand'] = csrf_token();
        }
        return $id;
    }
    
    
	/**
	 * ���������� ������� ��� ������������ �� ��� ���� ��������
	 *
	 * @param integer $date ���� �������� ������������ (� ������� UNIX TIME)
	 * @return integer ���������� ���
	 */
    function ElapsedYears($date){
        $now = time();
        $m1 = date("m", $now);
        $d1 = date("d", $now);
        $m2 = date("m", $date);
        $d2 = date("d", $date);
        $ret = date("Y", $now) - date("Y", $date);
        if ($m1 < $m2 || $m1 == $m2 && $d1 < $d2) $ret -= 1;
        return $ret;
    }
    
	/**
	 * ������ ������� ������������ ������������� �� ����� � ������� ��� �����������
	 *
	 * @param integer $date ���� ����������� ������������ (� ������� UNIX TIME)
	 * @return string
	 */
    function ElapsedMnths($date){
        $now = time();
        $m1 = date("m", $now);
        $d1 = date("d", $now);
        $m2 = date("m", $date);
        $d2 = date("d", $date);
        $all = (date("Y", $now) - date("Y", $date))*12+($m1-$m2);
        if ($d1 < $d2) $all -= 1;
        $years = floor($all/12);
        $mnths = $all - $years*12;
        if ($years == 1) $ret = "1 ���";
        else if ($years > 1 && $years < 5) $ret = $years." ����";
        else if ($years >= 5) $ret = $years." ���";
        if ($mnths == 1) $ret1 = "1 �����";
        else if ($mnths > 1 && $mnths < 5) $ret1 = $mnths." ������";
        else if ($mnths >= 5) $ret1 = $mnths." �������";
        if ($ret && $ret1) $ret = $ret." � ".$ret1;
        else $ret = $ret.$ret1;
        if (!$ret) $ret = "������ ������";
        return $ret;
    }
    
	/**
	 * ����� ������������ �� �������
	 *
	 * @param boolean $save_cookie ������� ����������� ������ ��� ��� 
	 */
    function logout($save_cookie = FALSE){
        if (!$save_cookie) {
            $sql = "UPDATE users SET solt=NULL WHERE login='".$_SESSION['login']."'";
            pg_query(DBConnect(),$sql);
            if(is_emp()) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
                tmp_project::clearTmpAll($_SESSION['login']);
            }
        }
        $GLOBALS['session']->logout($_SESSION['login']);
        if ($_SESSION['uid']) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/activate_code.php");
            $user = new users();
            $active = $user->GetField($_SESSION['uid'], $err, 'active');
            $activate_code = activate_code::getActivateCodeByUid($_SESSION['uid']);
            if( $activate_code != '' && ( $active == true || $active == 't' ) ) {
                $user->active = false;
            }
            $user->last_time = 'now';
            $user->Update($_SESSION['uid'], $res);
        }
        
        
        //��������� ���� ������ �� ��� ��������������� ������ ��� ����� ��� ������/�����
        $_ga_stat_url_hash = isset($_SESSION['ga_stat_url_hash'])? $_SESSION['ga_stat_url_hash'] : null;

        session_unset();

        //�������������� �������� ����� ������
        if ($_ga_stat_url_hash) {
            $_SESSION['ga_stat_url_hash'] = $_ga_stat_url_hash;
        }
        
        
        if (!$save_cookie) {
            uncookie();
        }
    }
    
	/**
	 * �������� ���� ��� ������� ������ ������������ �����������
	 * @see logout();
	 */
    function uncookie()
    {
        if ($_COOKIE['id']) {
            unset($_COOKIE['id']);
            setcookie("id",'',time()-60,"/", $GLOBALS['domain4cookie'], COOKIE_SECURE, true);
            setcookie("pwd",'',time()-60,"/", $GLOBALS['domain4cookie'], COOKIE_SECURE, true);
            setcookie("name",'',time()-60,"/", $GLOBALS['domain4cookie'], COOKIE_SECURE);
            setcookie("sid",'',time()-60,"/", $GLOBALS['domain4cookie'], COOKIE_SECURE);
        }
    }

	/**
	 * �������� ������������� .gif, �������� ������ ������ ����.
	 * ���������� ��� ��������� � ��� �� ����������, ��� � ��������, ��� ��� �� ������, ������ � ��������� 'na_'.
     * ���� ���� � ����� ������ (� ���������) ��� ����, �� ������ �� ������.
     * ���������� � ������ ������ ��� ����������� ����, � ������ ������ (��� ��� �� ������������� ���) -- ��� ���������.
	 *
	 * @param string $dir       ���� � ��������� (�� �����, ��� '/' � ������)
	 * @param string $orig_name ��� ���� ���������.
         * @param string $alt_dir ��� �������������� ���������� ��� ������ ����� (�������� �� foto)
	 * @return string ��� ����������� ����
	 */
    function get_unanimated_gif($dir, $orig_name, $alt_dir = false) {

        if(CFile::getext($orig_name) != 'gif')
           return $orig_name;
        
        $memBuff = new memBuff();
        $res = $memBuff->get($orig_name);
        if ($res) {
            return $res;
        }

        $orig_file = $alt_dir ? trim($alt_dir,'/').'/'.$orig_name : "users/".substr($dir, 0, 2)."/".$dir."/foto/".$orig_name;

        $orig_content = @file_get_contents(WDCPREFIX_LOCAL.'/'.$orig_file);
        $unan_content = unanimate_gif($orig_content);
        if($unan_content !== false) {
            $unan_name = "na_".$orig_name;
            $unan_file = dirname($orig_file)."/".$unan_name;
            $unan = new CFile($unan_file);
            if(!$unan->id) {
                $unan = new CFile($orig_file); 
                $unan->name = $unan_name;
                $unan->size = strlen($unan_content);
                $put = $unan->putContent($unan_file, $unan_content); // ���������� ���������� ����
                if($put) {
                    $memBuff->set($orig_name, $unan_name, 3600*12);
                    return $unan_name;
                }
            } else {
                $memBuff->set($orig_name, $unan_name, 3600*12);
                return $unan_name;
            }
        }
      
        return $orig_name; // ���� �� ������ ��������� ���������� ��������
    }
    
    function unanimate_gif($orig_content) {
        if(substr($orig_content, 0, 3) == 'GIF'                               // ��� ������������� ���.
           && ($orig_blocks = preg_split('/\x21\xf9\x04/', $orig_content, 3)) // ����� ������������������� ������� ������ � ���� (��. http://www.w3.org/Graphics/GIF/spec-gif89a.txt).
           && $orig_blocks[2])                                                // ��� ������������� ���.
        {
            $unan_content = $orig_blocks[0].               // ��� ���� ����������, ������ ������ ����.
                            chr(0x21).chr(0xf9).chr(0x04). // "�����" ������� ������.
                            $orig_blocks[1].               // ������ �����. ��������� �����������.
                            ';';
            
            return $unan_content;
        }
        
        return false;
    }


  /**
   * ���������� �������� ��� ������ ������.
   *
   * @see view_preview();
   * 
   * @param string $align ������������ ����������� �������� �� �����������
   * @param string $alt ������������� ����� ��� �������� (������� � alt)
   * @return string html-��� ��������
   */
    function view_blank_preview($align = 'center', $alt = '����������� ������� �� ���������')
    {
        return "<div  style=\"text-align:$align\"><img src=\"/images/unimaged.gif\" alt=\"$alt\" width=\"200\" height=\"124\" /></div>";
    }


  /**
   * ���������� ������ ��� ������.
   * 
   * @param string $ulogin 					����� ������������
   * @param string $filename 				��� ����� ������
   * @param string $dir 					���������� � ������� �������� ������
   * @param string $align 					������������ ������ �� �����������
   * @param boolean $show_blank_preview 	������� ������ ��� ������ ������ (true - ���������� ��������, false - ���������� ������ ������)
   * @param boolean $unanimate_gif 			�������� ��, ���� ��� ������������� .gif, ������� ������ ������ ����. @deprecated � ������ ������� �������� ������ ����������
   * @param string $alt 			        ���������� �������� alt ��� ������.
   * @param int     $max_dim     ������������ �������� ������ ��� ������ �����������, 
   *                             ���� ����� ���������� ������� ��������� (��������� ��� ������������� ����� � ���������, �.�. �� �� �� �������� ��� ��������)
   * @return string html-��� ������
   */
    function view_preview($ulogin, $filename, $dir, $align = 'center', $show_blank_preview = false, $unanimate_gif = false, $alt = '', $max_dim = 0)
    {
        $l_dir = substr($ulogin, 0, 2) . "/" . $ulogin;

        if ($filename == '')
        {
            return (($show_blank_preview) ? view_blank_preview($align) : "");
        }
        else
        {
            $path = "users/$l_dir/$dir/".$filename;
            $cfile = new CFile($path);
            $width = $cfile->image_size['width'];
            $height = $cfile->image_size['height'];
            $type = $cfile->image_size['type'];
            if (!$width || !$height)
            {
                return (($show_blank_preview) ? view_blank_preview($align) : "");
            }
            
            if ($max_dim && ($width > $max_dim || $height > $max_dim)) {
                $x_ratio = $max_dim / $width;
                $y_ratio = $max_dim / $height;

                $ratio       = min($x_ratio, $y_ratio);
                if ($ratio == 0) {
                    $ratio = max($x_ratio, $y_ratio);
                }
                $use_x_ratio = ($x_ratio == $ratio);

                $width   = $use_x_ratio  ? $max_dim  : floor($width * $ratio);
                $height  = !$use_x_ratio ? $max_dim : floor($height * $ratio);
            }
            
            if ($type == 4 || $type == 13) {
                return "<div class=\"b-page__desktop b-page__ipad\"  style=\"text-align:$align\"><div id=\"viewattachswf\"></div></div>
                    <script type=\"text/javascript\">
                    	var flashvars = {};
                    	var params = {allowscriptaccess: \"never\", wmode: \"opaque\"};
                    	var attributes = {};
                    	swfobject.embedSWF(\"".WDCPREFIX."/users/$ulogin/$dir/$filename\", \"viewattachswf\", \"$width\", \"$height\", \"9.0.16\", \"/scripts/expressInstall.swf\", flashvars, params, attributes);
                    </script>";
            }
            
            if($unanimate_gif) $filename = get_unanimated_gif($ulogin, $filename, dirname($path));
            return "<span style=\"text-align:$align\"><img src=\"".WDCPREFIX."/users/$ulogin/$dir/$filename\" alt=\"$alt\" width=\"$width\" height=\"$height\" itemprop=\"contentUrl\" /></span>";
        }
    }
    
    
    /**
     * 
     * ������ view_preview(...) ��� ��������� � CFile �� ��������� � �����
     * ������������ ��� �������� ��� �������� � ��� ������������ �� ���������� �����
     * 
     * @param type $ulogin
     * @param type $filename
     * @param type $dir
     * @param type $align
     * @param type $show_blank_preview
     * @param type $unanimate_gif
     * @param type $alt
     * @param type $max_dim
     * @return type
     */
    function view_preview2($ulogin, $filename, $dir, $align = 'center', $show_blank_preview = false, $unanimate_gif = false, $alt = '', $max_dim = 0)
    {
        $l_dir = substr($ulogin, 0, 2) . "/" . $ulogin;

        if ($filename == '') {
            return (($show_blank_preview) ? view_blank_preview($align) : "");
        } else {
            $width = $height = $max_dim;
            $path = "users/$l_dir/$dir/".$filename;
            
            if (pathinfo($filename, PATHINFO_EXTENSION) == 'swf') {
                return "<div class=\"b-page__desktop b-page__ipad\"  style=\"text-align:$align\"><div id=\"viewattachswf\"></div></div>
                    <script type=\"text/javascript\">
                    	var flashvars = {};
                    	var params = {allowscriptaccess: \"never\", wmode: \"opaque\"};
                    	var attributes = {};
                    	swfobject.embedSWF(\"".WDCPREFIX."/users/$ulogin/$dir/$filename\", \"viewattachswf\", \"$width\", \"$height\", \"9.0.16\", \"/scripts/expressInstall.swf\", flashvars, params, attributes);
                    </script>";
            }
            
            if ($unanimate_gif) $filename = get_unanimated_gif($ulogin, $filename, dirname($path));
            return "<span style=\"text-align:$align\"><img class=\"b-pic b-pic_max_{$max_dim}\" src=\"".WDCPREFIX."/users/$ulogin/$dir/$filename\" alt=\"$alt\" itemprop=\"contentUrl\" /></span>";
        }
    }    
    
    
    
    
    /**
     * ���������� ������ ������(� ���� HTML-����) ����������� � ������
     *
     * @param string  $ulogin               ����� ������������
     * @param string  $filename             ��� ����
     * @param string  $dir                  ���������� � ������� ��������� ���� 
     * @param integer $file                 � �������� ���������� ������������ ������ ������ ����� 
     *                                      (0 - ���� �� ������������� ��������, 1 - ���� ������������� �������� (��� �������� � �����, 
     *                                      �������� ������� ������, ����� �� �� �������� �� ������� �������))
     * @param integer $maxh                 ����������� ���������� ������ �����
     * @param integer $maxw                 ����������� ���������� ������ �����
     * @param integer $maxpw                ����������� ���������� ������ �����
     * @param integer $show_ico             �������� ������ ����� (.txt, .doc, etc...)
     * @param integer $is_tn                �������� ��� ������ �� ���������� �����
     * @param string  $align                ������������ �����
     * @param boolean $show_blank_preview   ������� ������ ��� ������ ������ (true - ���������� ��������, false - ���������� ������ ������)
     * @param integer $show_download_ico    �������� ��� ��� ������ �� �������� ����� ������
     * @param boolean $limitSize            ���� true, �� �������� "���������" (������ ������ ������) � $maxw � $maxh ���� ��� ������ ��������� � ��� ��������
     * @param string $title            
     * @param string $wmode                ��������� �������� wmode ��� swf ������            
     * @return string HTML-���
     */
    function viewattach($ulogin, $filename, $dir, &$file, $maxh=1000, $maxw=450, $maxpw=307200, $show_ico = 0, $is_tn = 0, $align = 'center', $show_blank_preview = false, $show_download_ico = 1, $alt="", $antivirus_ico=TRUE, $limitSize=FALSE, $title = null, $wmode = 'window'){
        $l_dir = substr($ulogin, 0, 2)."/".$ulogin;

        $lfname = $filename;
        if ($is_tn) {$lfname = $fname = $filename; $filename = "sm_".$filename; }
       
        $cfile = new CFile("users/$l_dir/$dir/".$filename);

        $virusFlag = FALSE;
        if ( $cfile->virus & 1 == 1 ) {
            //$virus = '<span class="avs-err"><span>��������� �����.</span> ���� ������ � �������.</span>';
            $link  = 'href="" onclick="alert(\'��������� �����. ���� ������.\');return false;"';
            $virusFlag = TRUE;
        } else if ( $cfile->virus === 0 )  {
            //$virus = '<span class="avs-ok">��������� �����������</span>';
            $link  = "href='".WDCPREFIX."/users/{$ulogin}/{$dir}/{$lfname}'";
        } else if ( $cfile->virus === 2 )  {
            //$virus = '<span class="avs-errcheck">���������� ���������</span>';
            $link  = "href='".WDCPREFIX."/users/{$ulogin}/{$dir}/{$lfname}'";
        } else if ( $cfile->virus == 8 ) {
            //$virus = '<span class="avs-nocheck" title="����������� ����������� �����, ����������� ����� 1&nbsp;����&nbsp;2011&nbsp;����"><span>���� �� �������� �����������</span></span>'; // � �� ����� ��������
            $link  = "href='".WDCPREFIX."/users/{$ulogin}/{$dir}/{$lfname}'";
        } else {
            //$virus = '<span class="avs-nocheck" title="����������� ����������� �����, ����������� ����� 1&nbsp;����&nbsp;2011&nbsp;����"><span>���� �� �������� �����������</span></span>';
            $link  = "href='".WDCPREFIX."/users/{$ulogin}/{$dir}/{$lfname}'";
        }
        if (!$antivirus_ico) {
            $virus = '';
        }

        if (!$cfile->size)
        {
            if ($show_blank_preview) {
                return "<div  style=\"text-align:$align\"><img src=\"/images/unimaged.gif\" alt=\"����������� ������� �� ���������\" title=\"����������� ������� �� ���������\" width=\"200\" height=\"124\" /></div>";
            } else {
                return "";
            }
        }
        $file = 1;

        $ext = $cfile->getext();

        $ico = '';
        $ico = getICOFile($ext);

        if($ext == "flv" || $ext == "rar" || $ext =="ogg" || $ext == '3gp' || $ext == "wav") $name_file = $ext == '3gp' ? $ext."_32.png" : $ico."_32.png";
        else $name_file = "ico_$ico.gif";
        
        if ( !$fname ) {
            $fsize = ConvertBtoMB($cfile->size);
        }
        else {
            $cfile2 = new CFile("users/$l_dir/$dir/".$fname);
            $fsize = ConvertBtoMB($cfile2->size);
        }
        
        if ($show_ico && (in_array($ext, $GLOBALS['file_array']) || in_array($ext, $GLOBALS['video_array']) || in_array($ext, $GLOBALS['audio_array']) || $ext=="swf")) {
            return "<div style=\"text-align:$align\" class=\"filesize\">
            <a {$link} target=\"_blank\"><img src=\"/images/$name_file\" alt=\"$filename\" title=\"$filename\" /></a>
            <div>".$ext.", ".$fsize."&nbsp;&nbsp; {$virus}</div>
            </div>";
        }

        if (in_array($ext, $GLOBALS['graf_array']) && $maxh != 0 && $maxw != 0 && $maxpw != 0 && !$virusFlag)
        {
            
            if($alt === '')
               $alt = $filename;
            else
               $alt = str_replace('"', '&quot;', $alt);
            $width = $cfile->image_size['width'];
            $height = $cfile->image_size['height'];
            $type = $cfile->image_size['type'];
            if (!$width || !$height) return '';
            if (($width <= $maxw && $height <= $maxh || $maxw == -1 && $maxh == -1 || $limitSize) && $cfile->size <= $maxpw){
                $file = 0;
                if ($type == 4 || $type == 13) return "<div class=\"b-page__desktop b-page__ipad\" style=\"text-align:$align\"><div id=\"viewattachswf\"></div></div>
        <script type=\"text/javascript\">
            var flashvars = {};
            var params = {allowscriptaccess: \"never\", wmode: \"".$wmode."\"};
            var attributes = {};
            swfobject.embedSWF(\"".WDCPREFIX."/users/$ulogin/$dir/$filename\", \"viewattachswf\", \"$width\", \"$height\", \"11.7.700.224\", \"/scripts/expressInstall.swf\", flashvars, params, attributes);
        </script>".($virus ? '<div class="avs-portfolio">'.$virus.'</div>' : '');
                else {
                    if ($is_tn) {
                        return "<div  style=\"text-align:$align\"><a href=\"".WDCPREFIX."/users/$ulogin/$dir/$fname\" target=\"_blank\"><img src=\"".WDCPREFIX."/users/$ulogin/$dir/$filename\" alt=\"$alt\" title=\"" . ($title ? $title : $alt) . "\" width=\"$width\" height=\"$height\" /></a></div>";
                    }
                    else {
                        $al = '';
                        $ar = '';
                        if ( $limitSize ) {
                            if ( $maxw > 0 && $width > $maxw ) {
                                if ( $maxh <= 0 ) {
                                    $height = round($height * ($maxw / $width));
                                } else {
                                    $height = $maxh;
                                }
                                $width = $maxw;
                                $al = "<a href='".WDCPREFIX."/users/{$ulogin}/{$dir}/{$filename}' target='_blank'>";
                                $ar = "</a>";
                            }
                            if ( $maxh > 0 && $height > $maxh ) {
                                if ( $maxw <= 0 ) {
                                    $width = round($width * ($maxh / $height));
                                } else {
                                    $width = $maxw;
                                }
                                $height = $maxh;
                                $al = "<a href='".WDCPREFIX."/users/{$ulogin}/{$dir}/{$filename}' target='_blank'>";
                                $ar = "</a>";
                            }
                        }
                        return "<div  style=\"text-align:$align\">{$al}<img src=\"".WDCPREFIX."/users/$ulogin/$dir/$filename\" alt=\"$alt\" title=\"" . ($title ? $title : $alt) . "\" width=\"$width\" height=\"$height\" />{$ar}</div>";
                    }
                }
            }
        }
        return ('<br /><div class="filesize1"><div class="ico"><a '.$link.' target="_blank"><img src="/images/'.$name_file.'" alt="'.$filename.'" title="'.$filename.'" /></a></div><div class="text">'.$ext.', '.$fsize.'&nbsp;&nbsp; '. $virus .'</div></div>');
//        return ($show_download_ico? "<img src=\"/images/ico_dowload.gif\" alt=\"���������\" title=\"���������\" width=\"14\" height=\"18\" /> ": "") . "<a href=\"".WDCPREFIX."/users/$ulogin/$dir/$filename\" target=\"_blank\" class=\"blue\">���������</a> ($ext; ".$fsize.")";
    }
    
    /**
     * ���������� ������ ��� ���������� ������������ �����.
     * �� ������ ��� viewattach, ������ ��� HTML.
     * 
     * @param  string  $ulogin �����������. ����� ������������.
	 * @param  string  $filename ��� ����.
	 * @param  string  $dir ���������� � ������� ��������� ����.
     * @param  integer $maxh ����������� ���������� ������ �����.
	 * @param  integer $maxw ����������� ���������� ������ �����.
	 * @param  integer $maxpw ����������� ���������� ������ �����.
     * @param  integer $is_tn �������� ��� ������ �� ���������� �����.
     * @return array ������ ��� ���������� ������������ �����:
     *         bool 'success' true - �����, false - ������
     *         int 'file_mode' 1 - ���������� ����� ������ ��� ������������� ����, 0 - ����� � ���������
     *         string 'file_name' �������� �����
     *         string 'file_ext' ����������
     *         int 'file_size' ������������ ������
     *         string 'file_size_str' ����������������� ������
     *         string 'file_ico' true - ������ ����� � ����������� �� ����������
     *         bool 'virus_flag' true - ��������� �����
     *         string 'virus_class' ����� ��������� ������� ��� ��������� � ������� ������
     *         string 'virus_msg' ��������� � �������/���������� ������
     *         string 'link' ����� ������ �� ����
     *         'img_width' ���� file_mode = 1. ������ ������������ �����
     *         'img_height' ���� file_mode = 1. ������ ������������ �����
     *         'img_type' ���� file_mode = 1. ��� ������������ �����
     */
    function getAttachDisplayData( $ulogin, $filename, $dir, $maxh = 1000, $maxw = 450, $maxpw = 307200, $is_tn = 0 ) {
        if ( $is_tn ) {
            $fname    = $filename;
            $filename = 'sm_' . $filename;
        }
        
        if ( $ulogin ) {
           $l_dir = substr( $ulogin, 0, 2 ) . '/' . $ulogin;
           $sPath = "users/$l_dir/$dir/";
        } else {
           $sPath = rtrim($dir, "/") . '/';
        }
        
        $cfile = new CFile( $sPath . $filename );
        
        if ( !$cfile->size ) {
            $aData = array( 'success' => false );
        }
        else {
            $ext   = $cfile->getext();
            $aData = array( 
                'success'       => true,
                'file_mode'     => 1,
                'file_name'     => $filename,
                'class_ico'     => getICOFile($ext),
                'file_ext'      => $ext,
                'orig_name'     => $cfile->original_name,
                'file_size'     => $cfile->size,
                'virus_flag'    => false,
                'link'          => 'href="' . WDCPREFIX . '/' . $sPath . $cfile->name . '"',
            );
            
            if ( !$fname ) {
                $aData['file_size_str'] = ConvertBtoMB( $cfile->size );
			}
			else {               
			    $cfile2 = new CFile( $sPath . $fname );
			    $aData['file_size_str'] = ConvertBtoMB( $cfile2->size );
			}
            
            $ico = getICOFile( $ext );
            if ( $ext == "flv" || $ext == "rar" || $ext =="ogg" || $ext == '3gp' || $ext == "wav" ) {
                $aData['file_ico'] = ( $ext == '3gp' ) ? $ext."_32.png" : $ico."_32.png";
            }
            else {
                $aData['file_ico'] = "ico_$ico.gif";
            }
           /* 
            if ( $cfile->virus & 1 == 1 ) {
                $aData['virus_class'] = 'avs-err';
                $aData['virus_msg']   = '��������� �����';
                $aData['link']        = 'href="" onclick="alert(\'��������� �����. ���� ������.\');return false;"';
                $aData['virus_flag']  = true;
            } 
            elseif ( $cfile->virus === 0 )  {
                $aData['virus_class'] = 'avs-ok';
                $aData['virus_msg']   = '��������� �����������';
            }
            elseif ( $cfile->virus == 2 )  {
                $aData['virus_class'] = 'avs-errcheck';
                $aData['virus_msg']   = '���������� ���������';
            }
            elseif ( $cfile->virus == 8) {  // � �� ����� ��������
                $aData['virus_class'] = 'avs-nocheck';
                $aData['virus_msg']   = '���� �� �������� �����������';
            } 
            else {
                $aData['virus_class'] = 'avs-nocheck';
                $aData['virus_msg']   = '���� �� �������� �����������';
            }
            */
            if ( in_array($ext, $GLOBALS['graf_array']) && $maxh != 0 && $maxw != 0 && $maxpw != 0 ) {
                $width  = $cfile->image_size['width'];
                $height = $cfile->image_size['height'];
                $type   = $cfile->image_size['type'];
                
                if ( $width && $height ) {
                    if ( 
                        ($width <= $maxw && $height <= $maxh || $maxw == -1 && $maxh == -1) 
                        && $cfile->size <= $maxpw 
                    ) {
                        $aData['file_mode']  = 0;
                    }
                    
                    $aData['img_width']  = $width;
                    $aData['img_height'] = $height;
                    $aData['img_type']   = $type;
                }
            }
        }
        
        return $aData;
    }
    
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
	 * @param integer $file_num �����������. ���� ����� ���������� �����.
     * @param string  $alt                  ALT ��� ��������
	 * @return string HTML-���
	 */
    function viewattachLeft($ulogin, $filename, $dir, &$file, $maxh=1000, $maxw=450, $maxpw=307200, $show_ico = 0, $is_tn = 0, $show_download_ico = 1, $file_num = 0, $alt='', $title = false ) {
        $ext = CFile::getext($filename);
        $lfname = $filename;
        if ($is_tn && in_array($ext, $GLOBALS['graf_array'])) {$lfname = $fname = $filename; $filename = "sm_".$filename; }
        if(substr($dir, -1)=='/') $dir = substr($dir, 0, -1);
        if($ulogin) {
           $path = WDCPREFIX."/users/$ulogin/$dir/";
           $dir = 'users/'.substr($ulogin, 0, 2)."/$ulogin/$dir/";
        } else {
           $path = WDCPREFIX."/$dir/";
           $dir = "$dir/";
        }
        
        $cfile = new CFile($dir.$filename);
        if($cfile->id === null ) {
            $filename = $lfname;
            $cfile = new CFile($dir.$lfname);
        }
        $virusFlag = FALSE;
        if ( $cfile->virus & 1 == 1 ) {
            //$virus = '<span class="avs-err margleft_15"><span>��������� �����.</span> ���� ������ � �������.</span>';
            $link  = 'href="" onclick="alert(\'��������� �����. ���� ������.\');return false;"';
            $virusFlag = TRUE;
        } else if ( $cfile->virus === 0 )  {
            //$virus = '<span class="avs-ok margleft_15">��������� �����������</span>';
            $link  = "href='{$path}{$lfname}'";
        } else  if ( $cfile->virus == 2 ) {
            //$virus = '<span class="avs-errcheck margleft_15">���������� ���������</span>';
            $link  = "href='{$path}{$lfname}'";
        } else if ( $cfile->virus == 8) {
            //$virus = '<span class="avs-nocheck margleft_15" title="����������� ����������� �����, ����������� ����� 1&nbsp;����&nbsp;2011&nbsp;����"><span>���� �� �������� �����������</span></span>';  // � �� ����� ��������
            $link  = "href='{$path}{$lfname}'";
        } else {
            //$virus = '<span class="avs-nocheck margleft_15" title="����������� ����������� �����, ����������� ����� 1&nbsp;����&nbsp;2011&nbsp;����"><span>���� �� �������� �����������</span></span>';
            $link  = "href='{$path}{$lfname}'";
        }
        $file = 1;
        $ico = getICOFile($ext);
        if($ext == "flv" || $ext == "rar" || $ext =="ogg" || $ext == "3gp" || $ext == "wav") {
            $name_file = strtolower($ext) != '3gp' ?  $ico."_32.png" : "3gp_32.png";
        }
        else $name_file = "ico_$ico.gif";
        
        if ( !$fname ) {
            $fsize = ConvertBtoMB($cfile->size);
		}
		else {           
		    $cfile2 = new CFile($dir.$fname);
		    $fsize = ConvertBtoMB($cfile2->size);
		}
        
        if ($show_ico && (in_array($ext, $GLOBALS['file_array']) || in_array($ext, $GLOBALS['video_array']) || in_array($ext, $GLOBALS['audio_array']) || $ext=="swf")) {			
            return "<div class=\"filesize1\">
            <div class=\"ico\"><a {$link} target=\"_blank\"><img src=\"/images/$name_file\" alt=\"$filename\" title=\"$filename\" /></a></div>
  			<div class=\"text\">".$ext.", ".$fsize."&nbsp;&nbsp; {$virus}</div>
  			<br />
  			</div>";
        }
        
        if ($ext != 'swf' && in_array($ext, $GLOBALS['graf_array']) && $maxh != 0 && $maxpw != 0 && $maxpw != 0 && !$virusFlag) {
            $width = $cfile->image_size['width'];
            $height = $cfile->image_size['height'];
            $type = $cfile->image_size['type'];
            if (!$width || !$height) return '';
            if (($width <= $maxw && $height <= $maxh || $maxw == -1 && $maxh == -1) && $cfile->size <= $maxpw){
                $file = 0;
                if ($type == 4 || $type == 13) return "<div class=\"b-page__desktop b-page__ipad\" style=\"text-align:center\">
  					<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab#version=9,0,16,0\" width=\"$width\" height=\"$height\">
                    <param name=\"movie\" value=\"$path/$filename\">
  					<param name=\"quality\" value=\"high\">
  					<param name=\"bgcolor\" value=\"white\">
  					<param name=\"AllowScriptAccess\" value=\"never\">
                    <embed src=\"{$path}{$filename}\"  width=\"$width\" height=\"$height\" quality=\"high\" bgcolor=\"#black\" align=\"center\" type=\"application/x-shockwave-flash\" AllowScriptAccess=\"never\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\"></embed>
  					</object></div>";
                else	{
                    $alt = $alt ? $alt : $filename;
                    if ($is_tn)
                    $str = "<div  style=\"text-align:center\"><a {$link} target=\"_blank\" title=\"{$alt}\" alt=\"{$alt}\"><img src=\"{$path}{$filename}\" alt=\"$alt\" title=\"" . ($title ? $title : $alt) . "\" width=\"$width\" height=\"$height\" /></a></div>";
                    else $str = "<div  style=\"text-align:center\"><img src=\"{$path}{$filename}\" alt=\"$alt\" title=\"" . ($title ? $title : $alt) . "\" width=\"$width\" height=\"$height\" /></div>";
                    return $str;
                }
            }
            else {
                return ( $file_num ? $file_num . '.&#160;' : '' ) . '<span class="b-icon b-icon_attach_' . $ico . '"></span> '
                    .'<a class="b-layout__link" '.$link.' target="_blank">���������</a> (' . $ext . '; ' . $fsize . ')'
                    .$virus;
            }
        }
        if($fname) {
            $filename = $fname;
            $cfile = new CFile($dir.$filename);
        }
        
        //return (($show_download_ico)?"<img src=\"/images/ico_dowload.gif\" alt=\"���������\" title=\"���������\" width=\"14\" height=\"18\" />&nbsp;":"&nbsp;")."<a {$link} target=\"_blank\" class=\"blue\">���������</a> ($ext; ".ConvertBtoMB($cfile->size).") {$virus}";
        
        return  ( $file_num ? $file_num . '.&#160;' : '' ) . '<span class="b-icon b-icon_attach_' . $ico . '"></span> '
            .'<a  class="b-layout__link"' . $link . ' target="_blank">���������</a> (' . $ext . '; ' . $fsize . ')'
            .$virus;
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
    function viewattachExternal($ulogin, $filename, $dir, $url, $bigtext=0){
        $l_dir = substr($ulogin, 0, 2)."/".$ulogin;
        $cfile = new CFile("users/$l_dir/".$dir."/".$filename);
        if (!$cfile->size) return "";
        $ext = $cfile->getext($filename);

        $virusFlag = FALSE;
        if ( $cfile->virus & 1 == 1 ) {
            //$virus = '<span class="avs-err"><span>��������� �����.</span> ���� ������ � �������.</span>';
            $link  = 'href="" onclick="alert(\'��������� �����. ���� ������.\');return false;"';
            $virusFlag = TRUE;
        } else if ( $cfile->virus === 0 )  {
            //$virus = '<span class="avs-ok">��������� �����������</span>';
            $link  = "href='{$url}'";
        } else if ( $cfile->virus == 8) {
           // $virus = '<span class="avs-nocheck" title="����������� ����������� �����, ����������� ����� 1&nbsp;����&nbsp;2011&nbsp;����"><span>���� �� �������� �����������</span></span>';
            $link  = "href='{$url}'";
        } else {
            //$virus = '<span class="avs-nocheck" title="����������� ����������� �����, ����������� ����� 1&nbsp;����&nbsp;2011&nbsp;����"><span>���� �� �������� �����������</span></span>';
            $link  = "href='{$url}'";
        }
        $ico = getICOFile($ext);
        
        $fsize = ConvertBtoMB($cfile->size);
        
        if (!$bigtext) {
	        return "<div class=\"filesize1\">
  			<div class=\"ico\"><a {$link} target=\"_blank\"><img src=\"/images/ico_".$ico.".gif\" alt=\"$filename\" title=\"$filename\" /></a></div>
  			<div class=\"text\">".$ext.", ".$fsize."&nbsp;&nbsp; {$virus}</div>
  			<br />
  			</div>";
        } else {
	        return "<div  style=\"text-align:center\" class=\"filesize\">
  			<a {$link} target=\"_blank\"><img src=\"/images/ico_".$ico.".gif\" alt=\"$filename\" title=\"$filename\" /></a>
  			<div>".$ext.", ".$fsize."</div>
            &nbsp;&nbsp; {$virus}
  			</div>";
        }
    }


    /**
     * ���������� ������ ������ � �������� ��� ������������
     *
     * @param array $attaches ������ � ����������� � �����/������ (����. ��������� ������� �� ������� file)
     * @return string HTML ���
     */
    function viewattachList($attaches) {
        if(!$attaches || !count($attaches)) return '';
        
        $list = '';

        if(isset($attaches['id'])) $attaches = array($attaches);

        foreach($attaches as $attach) {
            $cfile = new CFile();
            $ext = $cfile->getext($attach['fname']);
            $ico = getICOFile($ext);
            $fsize = ConvertBtoMB($attach['size']);

            $path = WDCPREFIX."/{$attach['path']}{$attach['fname']}";

            $list .= "<li class='$ico'><a href='$path'>$ext, $fsize</a></li>\n";
        }

        return "<ul class='added-files-list c'>$list</ul>";
    }

    /**
     * ���������� ������ ������ � �������� ��� ������������ (����� ������)
     *
     * @param array $attaches ������ � ����������� � �����/������ (����. ��������� ������� �� ������� file)
     * @return string HTML ���
     */
    function viewattachListNew($attaches) {
        if(!$attaches || !count($attaches)) return '';

        $thumbs = '';
        $list = '';

        if(isset($attaches['id'])) $attaches = array($attaches);

        foreach($attaches as $attach) {
            $cfile = new CFile();
            $ext = $cfile->getext($attach['fname']);
            $ico = getICOFile($ext);
            $fsize = ConvertBtoMB($attach['size']);

            $path = WDCPREFIX."/{$attach['path']}{$attach['fname']}";

            if($attach['size'] < 300 * 1024 && in_array($ext, $GLOBALS['graf_array']) && $ext != 'swf') {
                $path_th = WDCPREFIX."/{$attach['path']}sm_{$attach['fname']}";

                $th = new CFile("{$attach['path']}sm_{$attach['fname']}");
                if(!$th->id) $path_th = $path;

                $thumbs .= "<li class='afl-img c'><a href='$path' title='$ext, $fsize'><img src='$path_th' alt='$ext, $fsize' /></a></li>\n";
            } else {
                $list .= "<li class='$ico'><a href='$path'>$ext, $fsize</a></li>\n";
            }
        }

        return "<ul class='added-files-list c'>{$thumbs}{$list}</ul>";
    }

	/**
	 * ��������� ���� �� � ������, �������, � ���� ���� �� ���������� HTML-��� 
	 * ��� ���� ����� ������� ����� �������� � html �����, ����� ���������� �������
	 *
	 * @todo � �� ������ ��� - return (trim($descr)!=''?"<div style=\"text-align:left;padding-top:8px;\">" . $descr . "</div>":"");
	 * 
	 * @param string $ulogin ����� ������������ @deprecated �� ������������ � ��������
	 * @param string $descr  ����� ��� ������
	 * @return string HTML-code
	 */
    function viewdescr($ulogin, $descr)
    {
        if (trim($descr) != '')
        {
            //		  $ret = "<div style=\"text-align:left;padding-top:8px;\"><img src=\"/images/laquo.gif\" class=\"laquo\" alt=\"\" width=\"19\" height=\"19\" border=\"0\">" . $descr . "<img src=\"/images/raquo.gif\" class=\"raquo\" alt=\"\" width=\"19\" height=\"19\" border=\"0\"></div>";
            $ret = "<div style=\"text-align:left;padding-top:8px;\">" . LenghtFormatEx($descr,300,'...', false) . "</div>";
        }
        else
        {
            $ret = '';
        }
        return $ret;
    }
    
	/**
	 * ����������� ������� ����� �� ������ � ���������
	 *
	 * @param integer $size ������ (� ������)
	 * @return string
	 */
    function ConvertBtoMB($size){
        return sizeFormat($size);//str_replace(' ', '&nbsp', sizeFormat($size));
    }
    
	/**
	 * �����(�������) � ������ ��� "http://" ��� ������ ������ ������ ���� "www.ya.ru",
	 * � ��� �� ��� ������� ������� ��������� � ������
	 *
	 * @param string $input ����� � ������
	 * @return string
	 */
    function trimhttp($input){
        $srch = array("http://", " ");
        $out = str_replace("http://","",$input);
        return $out;
    }
    /**
     * ��������� � ������ http://, �� ������ ���� �������� �� ������
     * ����� ������� ������ ���� ��� ������� ������ �� 'http://'
     */
    function addhttp ($url) {
        if (!preg_match('~^https?://~', $url) && $url !== '') {
            $url = 'http://' . $url;
        }
        if ($url === 'http://') {
            $url = '';
        }
        return $url;
    }
    
	/**
	 * ������������ ������, �������� ��� "http://" �� ������ �����, � ������� �� ������������ ������(@see urlencode())
	 * ��� ������������ ������ ������
	 *
	 * @param string $input ����� � ������
	 * @return string
	 */
    function formatLink($input){
        $srch = array("http://", " ");
        $repl = array("", "%20");
        $out = str_replace($srch, $repl,$input);
        return htmlspecialchars($out);
    }

    /**
     * �������� � ������ ��� ������ �������� �� ���� ������� � ��� �� ������������ ���������� �������� �� ���������� ��������
     *
     * @param string $input �����
     * @return string
     */
    function input_ref($input){
        $out = preg_replace('~<br ?/?>~', "\r\n", $input);
        $srch = array("&laquo;", "&raquo;");
        $out = str_replace($srch, "\"", $out);
        return $out;
    }

    
	/**
	 * ����� ������ ��������� ������ �� ������ ������ �������� � ������, 
	 * � ��� �� ������������ �������� ���� �� ����� ���������� ��������� ���������
	 * 
	 * @see input_ref()
	 * 
	 * @param string $input �����
         * @param boolean $keep_wrapping ��������� ��� ��� "������" �������� �����
	 * @return string
	 */
    function input_ref_scr($input,$keep_wrapping = false){
        $out = str_replace("\\", "\\\\", $input);
        $out = input_ref($out);
        if($keep_wrapping){
            $out = preg_replace("/([\r\n])/","\\r\\n\\\r\\\n",$out);
        }else{
            $out = preg_replace("/([\r\n]{1,2})/","\\r\\n\\\r\\\n",$out);
        }
        //$out = str_replace("\r","\\r\\n\\\r\\\n",input_ref($out)); //������ �����!
        $srch = array("&laquo;", "&raquo;", "&quot;");
        $out = str_replace($srch, "\"", $out);
        $out = str_replace("'", "\'", $out);
        $out = str_replace("&#039;", "\'", $out);
        $out = str_replace("&amp;", "&", $out);
        return $out;
    }
    
	/**
	 * ���������� ������� �������
	 *
	 * @param string $input �����
	 * @return string
	 */
    function ref_scr($input){
        $out = str_replace("\r\n","\\\r\\\n",$input);
        //$out = str_replace("\n","\\\n",$out);
        //$out = str_replace("\r","\\\r",$out);
        $out = str_replace("\"", "\\\"", $out);
        $out = str_replace("'", "&rsquo;", $out);
        return $out;
    }
    
	/**
	 * �������� ������ �� ������ � HTML �����
	 *
	 * @param string $error ��������� �� ������ 
	 * @return string HTML ���
	 */
    function view_error($error){
        if ($error)
        $error_str = "<div class=\"errorBox\"><img src=\"/images/ico_error.png\" alt=\"\" width=\"22\" height=\"18\" /> &nbsp;$error</div>";
        return $error_str;
    }
    
	/**
	 * @see view_error()
	 * @ignore
	 */
    function view_error2($error){
        if ($error)
        $error_str = "<div class=\"errorBox\" style=\"color:#FFF\"><img src=\"/images/ico_error2.gif\" alt=\"\" /> &nbsp;$error</div>";
        return $error_str;
    }
    
	/**
	 * @see view_error()
	 * @ignore
	 */
    function view_error3($error){
        if ($error)
        $error_str = "<div class=\"errorBox1\">$error</div>";
        return $error_str;
    }
    
	/**
	 * @see view_error()
	 * @ignore
	 */
    function view_error4($error){
        if ($error)
        $error_str = "<div class=\"errorBox2\">$error</div>";
        return $error_str;
    }
    
	/**
	 * �������� ���������� �� �������� ���������� ������ ���� �������� ��� ���������� ������� (POST, GET, ��� �������� �������������� ������ � �.�.)
	 *
	 * @param string $info ����� ����������
	 * @return string HTML-���
	 */
    function view_info($info){
        if ($info)
        $out = "<div class=\"b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_6db335\"><img class=\"b-layout__pic b-layout__pic_margbot_-2\" src=\"/images/ico_ok.gif\" alt=\"\" width=\"19\" height=\"18\" /> $info</div>";
        return $out;
    }
    
    function view_info2($info, $top = NULL, $left = NULL){
        if ($info) {
            $pos = ' style="';
            if($top !== NULL)  $pos .= "margin-top:{$top}px;";
            if($left !== NULL) $pos .= "margin-left:{$left}px;";
            $pos .= '"';
            return '<div class="b-fon b-fon_bg_f0ffdf b-fon_pad_10 b-fon_padleft_35 b-fon_margbot_20"' . $pos . '><span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>' . $info . '</div>';
        }
    }
    
	/**
	 * ���������� ������������ ������ �� ������������� ����
	 *
	 * @param integer $cur ��� ������
	 * @return string ������������ ������
	 */
	function GetCur($cur){
        switch ($cur) {
            case 1: $out = "EUR"; break;
            case 2: $out = "RUR"; break;
            case 3: $out = "FM"; break;
            default: $out = "USD";
        }
        return $out;
    }
	
    /**
     * ���������� ���������� � ������������ ������ � ����������������� ����
     *
     * @param integer $val  ���������� �����
     * @param integer $ind  ��� ������
     * @return string
     */
    function CurToChar($val, $ind){
    	$val = number_format($val, 2, '.', ' ');
    	if (strpos($val, ".") == 5) {$val = str_replace(" ", "", $val);}
    	$val = str_replace(".00", "", $val);
    	$val = str_replace(" ", "&nbsp;", $val);
        switch ($ind) {
            case 1: $out = $val."&nbsp;&euro;"; break;
            case 2: $out = $val."&nbsp;�."; break;
            case 3: $out = $val."&nbsp;FM"; break;
            default: $out = $val."&nbsp;$";
        }
        return $out;
    }

  /**
   * ���������� ������ � ������� � ����������� �� ���� ������
   *
   * @param float $val				������
   * @param integer $ind				��� ������
   * @param bool $colorized			���� �� ������������ (������������ ��� ������ ��������. ����� � ����� � ������������)
   * @return string
   */
    function CurToChar2($val, $ind, $colorized = false){
        if ($colorized) $val = "<span style=\"\">".(floor($val)).".".(($val*100)%100)."</span>";
        switch ($ind) {
            case 1: $out = $val." FM"; break;
            case 5: $out = $val." ��� (������)"; break;
            case 2: $out = $val." WMZ"; break;
            case 3: case 11: $out = $val." WMR"; break;
            case 4: $out = $val." ������.������"; break;
            case 6: $out = $val." ��� (��������)"; break;
            case 7: $out = $val." Assist"; break;
            case 8: $out = $val." SMS"; break;
            case 9: $out = $val." ����"; break;
        }
        return $out;
    }

    
     /**
       * ���������� ������ � ������� � ����������� �� ���� ������
       *
       * @param float $val				������
       * @param integer $ind			��� ������
       * @return string
       */
    function CurToChar3($val, $ind) {
        $num = number_format(floor($val), 0, '.', '  ');
        $val = str_replace(' ', "&nbsp;", $num);
        switch ($ind) {
            case 1: $out = $val."&nbsp;FM"; break;
            case 5: $out = $val."&nbsp;���.&nbsp;(������)"; break;
            case 2: $out = $val."&nbsp;WMZ"; break;
            case 3: $out = $val."&nbsp;WMR"; break;
            case 4: $out = $val."&nbsp;������.������"; break;
            case 6: $out = $val."&nbsp;���.&nbsp;(��������)"; break;
            case 7: $out = $val."&nbsp;Assist"; break;
            case 8: $out = $val."&nbsp;SMS"; break;
            case 9: $out = $val."&nbsp;����"; break;
        }
        return $out;    
    }
    
    /**
     * ���������� ��������������� HTML-��� ��� ������ �������� ������������
     *
     * @param string  $user  		����� ������������

     * @param string  $file  		�������� ����� ��������
     * @param integer $size  		��� �������� 1 = ������, 0 - �������
     * @param integer $animated 	1 = � ���������, 0 - ��� �������� (����� �������� � gif ������ ) @see get_unanimated_gif();
     * @return string
     */
    function view_avatar($user, $file, $size = 1, $animated=1, $cls="b-pic"){
    	if($animated == 0) {
    		if ($size && $file) $file = "sm_".$file;
    		$file = get_unanimated_gif($user, $file);
    		 
    		if ($size){
	            if ($file) return "<img src=\"".WDCPREFIX."/users/$user/foto/$file\" alt=\"$user\" width=\"50\" height=\"50\" class=\"{$cls}\" border=\"0\"/>";
	            else  return "<img src=\"".WDCPREFIX."/images/user-default-small.png\" alt=\"$user\" width=\"50\" height=\"50\" class=\"no_foto {$cls}\"  border=\"0\"/>";
	        } else {
	            if ($file) return "<img src=\"".WDCPREFIX."/users/$user/foto/$file\" alt=\"$user\" width=\"100\" height=\"100\" class=\"{$cls}\" border=\"0\"/>";
	            else  return "<img src=\"".WDCPREFIX."/images/no_foto_b.png\" alt=\"$user\" width=\"100\" height=\"100\" class=\"no_foto {$cls}\" border=\"0\"/>";
	        }
    	}
    	
        if ($size){
            if ($file) return "<img src=\"".WDCPREFIX."/users/$user/foto/sm_$file\" alt=\"$user\" width=\"50\" height=\"50\" class=\"{$cls}\" border=\"0\"/>";
            else  return "<img src=\"".WDCPREFIX."/images/user-default-small.png\" alt=\"$user\" width=\"50\" height=\"50\" class=\"no_foto {$cls}\" border=\"0\"/>";
        } else {
            if ($file) return "<img src=\"".WDCPREFIX."/users/$user/foto/$file\" alt=\"$user\" width=\"100\" height=\"100\" class=\"{$cls}\" border=\"0\"/>";
            else  return "<img src=\"".WDCPREFIX."/images/no_foto_b.png\" alt=\"$user\" width=\"100\" height=\"100\" class=\"no_foto {$cls}\" border=\"0\"/>";
        }
    }

	/**
	 * ��������� view_avatar, ������ � ALT ������� ����������� ������������� � ����� ���������� �� �����
	 * 
     * @see view_avatar();
     * @see ElapsedMnths(strtotime($user->reg_date))
     * @see professions::GetProfName($user->spec)
	 *
	 * @param string  			$user    		����� ������������ 
	 * @param string  			$file			��� ����� ��������
	 * @param integer 			$size			��� �������� (1 - ������, 0 - �������)
	 * @param integer|string 	$spec			�������������, ���� 0 - �� ���������� �� ���������
	 * @param integer|string 	$reg_date		���� �����������, ���� 0 - �� ���������� �� ��������� 
	 * @param string                $class                  ����� ��� ���� img
         * @return string HTML code
	 */
    function view_avatar_info($user, $file, $size = 1, $spec = 0, $reg_date = 0, $class = false){
        if ($spec)
        {
            $alt_info = "�������������: ".htmlspecialchars($spec, ENT_QUOTES, 'cp1251');
        }
        if ($reg_date)
        {
            if ($alt_info) $alt_info .= ", �� ����� ".ElapsedMnths(strtotime($reg_date));
            else $alt_info .= "�� ����� ".ElapsedMnths(strtotime($reg_date));
        }
        if ($class === false) {
            $class = "b-post__userpic";
        }

        if ($size){
            if ($file) return "<img src=\"".WDCPREFIX."/users/$user/foto/sm_$file\" alt=\"$alt_info\" title=\"$alt_info\" width=\"50\" height=\"50\" class=\"$class\" />";
            else  return "<img src=\"/images/user-default-small.png\" alt=\"$alt_info\" title=\"$alt_info\" width=\"50\" height=\"50\" class=\"$class\" />";
        } else {
            if ($file) return "<img src=\"".WDCPREFIX."/users/$user/foto/$file\" alt=\"$alt_info\" title=\"$alt_info\" width=\"100\" height=\"100\" />";
            else  return "<img src=\"/images/no_foto_b.png\" alt=\"$alt_info\" title=\"$alt_info\" width=\"100\" height=\"100\" />";
        }
    }
    
	/**
	 * �������� ������ �������� (HTML-���)
	 *
	 * @deprecated �� ������������ �� �����, ������ ��� ����� ������������ view_avatar � ������ size = 1
	 * 
	 * @param string $user ����� ������������
	 * @param string $file ��� ����� ������������
	 * @return string
	 */
    function view_avatar_sm($user, $file) {
        if ($file) return "<img src=\"".WDCPREFIX."/users/$user/foto/sm_$file\" width=\"25\" height=\"25\" alt=\"\" class=\"lpl-avatar\" />";
        else  return "<img src=\"/images/user-default-small.png\" width=\"25\" height=\"25\" alt=\"\" />";
    }

  /**
   * ��������� ���� �� ������� PostgreSQL � Unix Timestamp ��������!
   *
   * ��������! �� ����������� ��� �������!
   * ��� ������� � ��������� ������ ��� �������������.
   * ����������� ������� dateFormat()
   *
   * @param string $strInput		����-�����
   * @return integer				Unix Timestamp
   */
    function strtotimeEx($strInput) {
        $pos = strpos($strInput,".");
        if ($pos !== false)
        $strInput = substr_replace($strInput, "", $pos);

        $iVal = -1;
        for ($i=1900; $i<=1969; $i++) {
            # Check for this year string in date
            $strYear = (string)$i;
            if (!(strpos($strInput, $strYear)===false)) {
                $replYear = $strYear;
                $yearSkew = 1970 - $i;
                $strInput = str_replace($strYear, "1970", $strInput);
            };
        };
        if ($strInput) $iVal = strtotime($strInput); else $iVal = strtotime("this");
        if ($yearSkew > 0) {
            $numSecs = (60 * 60 * 24 * 365 * $yearSkew);
            $iVal = $iVal - $numSecs;
            $numLeapYears = 0;        # Work out number of leap years in period
            //print $replYear.$yearSkew;
            for ($j=$replYear; $j<=1970; $j++) {
                $thisYear = $j;
                $isLeapYear = false;
                # Is div by 4?
                if (($thisYear % 4) == 0) {
                    $isLeapYear = true;
                };
                # Is div by 100?
                if (($thisYear % 100) == 0) {
                    $isLeapYear = false;
                };
                # Is div by 1000?
                if (($thisYear % 1000) == 0) {
                    $isLeapYear = true;
                };
                if ($isLeapYear == true) {
                    if ($replYear == $j && date("n",$iVal) > 2) $numLeapYears = $numLeapYears-1;
                    $numLeapYears++;
                };

            };
            //print " " . $numLeapYears; exit;
            $iVal = $iVal - (60 * 60 * 24 * $numLeapYears);//+ 60 * 60 * 24 ;
        };
        return($iVal);
    };

    /**
	 * ����������� ���� �� ������ ���������� ������� � ������
	 *
	 * @param string $sFormat		������ ������ ���� (��� ��� �-��� date())
	 * @param string $sDate			����-�����
	 * @return string				���� � ������ �������
	 */
    function dateFormat($sFormat, $sDate){
        $date = new DateTime($sDate);
        return $date->format($sFormat);
        return $sDate;
    }
	
    /**
     * ��������������� ��� ������ ����� �������� � ������ ������ �������
     *
     * @deprecated 
     * 
     * @param string  $tab           �������� 
     * @param string  $cur_orderby   �������� ��������
     * @param integer $profid        �� ��������?? 
     * @return string
     */
    function view_order_by($tab, $cur_orderby, $profid){
        switch ($tab) {
            case "opinions":
                $out = ($tab == $cur_orderby)? "<img src=\"/images/arrow_gr_d.gif\" alt=\"\" width=\"9\" height=\"5\" />&nbsp;������" : "<img src=\"/images/arrow_gr_r.gif\" alt=\"\" width=\"7\" height=\"6\" />&nbsp;<a href=\".?".(($profid)?"prof=$profid&":"")."order=ops\" class=\"blue\">������</a>";
                break;
            case "recomendations":
                $out = ($tab == $cur_orderby)? "<img src=\"/images/arrow_gr_d.gif\" alt=\"\" width=\"9\" height=\"5\" />&nbsp;������������" : "<img src=\"/images/arrow_gr_r.gif\" alt=\"\" width=\"7\" height=\"6\" />&nbsp;<a href=\".?".(($profid)?"prof=$profid&":"")."order=rcm\" class=\"blue\">������������</a>";
                break;
            case "teams":
                $out = ($tab == $cur_orderby)? "<img src=\"/images/arrow_gr_d.gif\" alt=\"\" width=\"9\" height=\"5\" />&nbsp;����������&nbsp;�&nbsp;�������" : "<img src=\"/images/arrow_gr_r.gif\" alt=\"\" width=\"7\" height=\"6\" />&nbsp;<a href=\".?".(($profid)?"prof=$profid&":"")."order=tms\" class=\"blue\">����������&nbsp;�&nbsp;�������</a>";
                break;
            case "visits":
                $out = ($tab == $cur_orderby)? "<img src=\"/images/arrow_gr_d.gif\" alt=\"\" width=\"9\" height=\"5\" />&nbsp;������������" : "<img src=\"/images/arrow_gr_r.gif\" alt=\"\" width=\"7\" height=\"6\" />&nbsp;<a href=\".?".(($profid)?"prof=$profid&":"")."order=vst\" class=\"blue\">������������</a>";
                break;
            case "general":
                $out = ($tab == $cur_orderby)? "<img src=\"/images/arrow_gr_d.gif\" alt=\"\" width=\"9\" height=\"5\" />&nbsp;�����" : "<img src=\"/images/arrow_gr_r.gif\" alt=\"\" width=\"7\" height=\"6\" />&nbsp;<a href=\".?".(($profid)?"prof=$profid&":"")."\" class=\"blue\">�����</a>";
                break;
        }
        return ($out);
    }
    
	/**
	 * ������� ����� ��������� � ������
	 * 
	 * @todo ������������ ������ � smail.php -> 1215 ������ � ���, ����� ������� ������� �� ���� �������� �����-�� ������ �������� ������� ������������ �������� ��� ���
	 * 
	 * @param string  $inp_str ������
	 * @param integer $maxlen  ������������ ����������
	 * @param string  $cr     	����� ������ ��������  
	 * @return string
	 */
    function LenghtFormat($inp_str, $maxlen, $cr = "\r\n"){
        $out = preg_replace("'([^".$cr."]{".$maxlen."}) '", "\\1\n", $inp_str);
        return $out;
    }
    
	/**
	 * ��������� ������ ����� � ���� ��� ������� ������� �������� ����� � ��������� � ����� ����� $etc
	 *
	 * @param string  $string		������
	 * @param integer $length		������������ ������ ������
	 * @param string  $etc			��� ��������� � ����� ����������� �����
	 * @param boolean $break_words	������� ������� ����� ������� ��� ���(false - ������� ��������, true - ��������� ����������)
	 * @return string
	 */
    function LenghtFormatEx($string, $length, $etc = "...", $break_words = false)
    {
        if ($length == 0)
        return '';
        if (strlen($string) >= $length) {
            $lnt = $length - strlen($etc);
            if (!$break_words)
            $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $lnt));

            return substr($string, 0, $length).$etc;
        } else
        return $string;
    }
	
    /**
     * ���������� � ��������� ����� ������� ������ ������� � ���������� ������������ ����� ���� ������, �� ���� ����������
     *
     * @param string $from_date ���� ����������
     * @return string
     */
    function str_ago_pub($from_date){
        switch (date("z") - date("z",$from_date) + (date("Y") - date("Y",$from_date))*365){
            case 0 : $out = "�������"; break;
            case 1 : $out = "�����"; break;
            default : $out = ""; break;
        }
        return $out;
    }
	
    /**
     * ���������� � ��������� ����� ������� ������ ������� � ���������� ������������ ����� ���� ������, �� ���� ����������
     *
     * @param integer $from_date	���� ���������� (UNIX TIME)
     * @param string  $format		������ ����������� (ynjGi - �������� ��� ���������� ����������������������, ������ i ��� ����� ������������� x 	(���� ������ -- � �������� ������������))
     * @param integer $to_date		�� ����� ���� �������, �� ��������� ���� � ��������� ������ (UNIX TIME)
     * @return string
     */
    function ago_pub($from_date, $format = "ynjGi", $to_date = 0, $is_project=false) {
        if (!$to_date) $to_date = time();
        $date_diff = ($from_date < $to_date)?($to_date - $from_date - (3*60*60)):($from_date - $to_date - (3*60*60));
        // ���, ���, ����
        if (($val = date("j", $date_diff)-1) && strpos($format, "j") !== false && $is_project) {
            return date('d.m.Y H:i', $from_date);
        }
        
        //����
        if (($val = (int) date("y", $date_diff)-70) && strpos($format, "y") !== false ) {
            $mod1 = $val % 100;
            $mod2 = $mod1 % 10;
            if ($mod2 == 1 && ($mod1 < 10 || $mod1 > 20)) {
                $out[] = "$val ���";
            } else if (($mod1 >= 10 && $mod1 <= 20) || $mod2 > 4 || $mod2 == 0) {
                $out[] = "$val ���";
            } else {
                $out[] = "$val ����";
            }
        }
        //������
        if (($val = date("n", $date_diff)-1) && strpos($format, "n") !== false){
            $mod1 = $val % 10;
            if ($mod1 == 1 && ($val < 10 || $val > 20)) {
                $out[] = "$val �����";
            } else if (($val >= 10 && $val <= 20) || $mod1 > 4 || $mod1 == 0) {
                $out[] = "$val �������";
            } else {
                $out[] = "$val ������";
            }
        }
        //���
        if (($val = date("j", $date_diff)-1) && strpos($format, "j") !== false){
            $mod1 = $val % 10;
            if ($mod1 == 1 && ($val < 10 || $val > 20)) {
                $out[] = "$val ����";
            } else if (($val >= 10 && $val <= 20) || $mod1 > 4 || $mod1 == 0) {
                $out[] = "$val ����";
            } else {
                $out[] = "$val ���";
            }
        }
        
        //����
        if (($val = date("G", $date_diff)) && strpos($format, "G") !== false){
            $mod1 = $val % 10;
            if ($mod1 == 1 && ($val < 10 || $val > 20)) {
                $out[] = "$val ���";
            } else if (($val >= 10 && $val <= 20) || $mod1 > 4 || $mod1 == 0) {
                $out[] = "$val �����";
            } else {
                $out[] = "$val ����";
            }
        }
        //������ (���� ������)
        if (($val = (int) date("i", $date_diff)) && strpos($format, "i") !== false){
            $mod1 = $val % 10;
            if ($mod1 == 1 && ($val < 10 || $val > 20)) {
                $out[] = "$val ������";
            } else if (($val >= 10 && $val <= 20) || $mod1 > 4 || $mod1 == 0) {
                $out[] = "$val �����";
            } else {
                $out[] = "$val ������";
            }
        }
        //������ (���� ������ -- � �������� ������������)
        if (($val = date("i", $date_diff)) && strpos($format, "x") !== false){
            $mod1 = $val % 10;
            if ($mod1 == 1 && ($val < 10 || $val > 20)) {
                $out[] = (int)$val . " ������";
            } else if (($val >= 10 && $val <= 20) || $mod1 > 4 || $mod1 == 0) {
                $out[] = (int)$val . " �����";
            } else {
                $out[] = (int)$val . " ������";
            }
        }
        
        if ($out) {
            $ret = implode(" ", $out). ($is_project?" �����":"");
        } elseif ((int)date("i", $date_diff) < 1) {
            $ret = '������ ���';
        } else {
            $ret = "";
        }
        return $ret;
    }
    
	/**
	 * �������� ��� ������� ago_pub
	 * 
	 * @see ago_pub()
	 * @ignore
	 */
    function ago_pub_x($from_date, $format = "ynjGx", $to_date = 0, $is_project = false) {
        return ago_pub($from_date, $format, $to_date, $is_project);
    }
    
    /**
     * ����� ���� ��� ��������
     * 
     * @param type $from_date
     * @return type 
     */
    function ago_project_created($from_date) {
        // ����� ����� �����
        if($from_date > strtotime('-1day')) {
            return ago_pub_x($from_date, "ynjGi", 0, true);
        } else if($from_date > strtotime('-6month')) { // ������ �������� �����
            return date('j', $from_date) . ' ' . monthtostr(date('n', $from_date), true) . ", " . date('H:i', $from_date);
        } else { //����� �������� �����.
            return date('j', $from_date) . ' ' . monthtostr(date('n', $from_date), true) . " " . date('Y, H:i', $from_date);
        }
    }
    
    /**
     * ����� ��� ������� ���������
     * ������� �� ������ ago_pub
     * �� ���� ������ ����� ����, �� ��� � ���������� '����� ����'
     * @param type $from_date
     * @return type
     */
    function ago_arbitrage_answered($from_date) {
        // ����� ���� �����
        if($from_date > strtotime('-1 hours')) {
            return '����� 1 ����';
        } else {
            return ago_pub($from_date);
        }
    }
    
    /**
     * ������� ����������� ������ �� ������
     * 
     * @param type $kind    ��� �������
     * @param type $offers  ���������� �������
     * @return string 
     */
    function project_status_link($kind, $offers) {
        if($kind == 7 || $kind == 2) {
            if($offers > 0) {
                return $offers . ' ' . ending($offers, '���c����', '���������', '����������'); 
            } else {
                return '��� ����������';
            }
        } else {
            if($offers > 0) {
                return $offers . ' ' . ending($offers, '�����', '������', '�������');
            } else {
                return '��� �������';
            }
        }
    }

    /**
     * �� ����� ���� ���� �������� ��� �������
     *
     * @param integer $date ���� (UNIX TIME)
     * @param string $pre ������� � ������
     * @return string
     */
    function pro_days($date, $pre = '��'){
        //���

        /*	$mod1 = $date%10;
        if ($mod1 == 1 && ($date < 10 || $date > 20)) $out = "������� ".$date." ����";
        elseif ($mod1 < 5 && $mod1 > 0 && ($date < 10 || $date > 20)) $out = "�������� ".$date." ���";
        else $out = "�������� ".$date." ����";*/
        $dtDate = new DateTime($date);
        $out = "$pre ".date_format( $dtDate, "d.m.y" );
        return $out;
    }
    
	/**
	 * ������� ������� �������� � "0", ����� ���������� ��������
	 * 
	 * @todo 	���� ����� ������ ������� ifnull() ��� ������ 0 ����� �������� � �������� ������ ���������� @example ifnull($input, "0");
	 * 			����� ����� �������� � ������ ������ �������, ��� � �������� ��������� ���������� ifnull()
	 *
	 * @param mixed $input ����������� ��������
	 * @return mixed
	 */
    function zin($input){
        if (!$input) return "0";
        return $input;
    }

    /**
     * ����������� �������� ������ �� ����� ������
     * 
     * @param integer $nm �����
	 * @param boolean $lower true, ���� ���������� �������� � ��������� �����
     * @return string
     */
    function monthtostr($nm, $lower=FALSE){
        $out = '';
		if ($lower) {
			switch ((int)$nm){
				case 1 : $out = "������"; break;
				case 2 : $out = "�������"; break;
				case 3 : $out = "�����"; break;
				case 4 : $out = "������"; break;
				case 5 : $out = "���"; break;
				case 6 : $out = "����"; break;
				case 7 : $out = "����"; break;
				case 8 : $out = "�������"; break;
				case 9 : $out = "��������"; break;
				case 10 : $out = "�������"; break;
				case 11 : $out = "������"; break;
				case 12 : $out = "�������"; break;
			}
		} else {
			switch ((int)$nm){
				case 1 : $out = "������"; break;
				case 2 : $out = "�������"; break;
				case 3 : $out = "�����"; break;
				case 4 : $out = "������"; break;
				case 5 : $out = "���"; break;
				case 6 : $out = "����"; break;
				case 7 : $out = "����"; break;
				case 8 : $out = "�������"; break;
				case 9 : $out = "��������"; break;
				case 10 : $out = "�������"; break;
				case 11 : $out = "������"; break;
				case 12 : $out = "�������"; break;
			}
        }
        return $out;
    }
    
	/**
	 * ����� ���� �� ������� ��������
	 *
	 * @deprecated 
	 * 
	 * @param mixed $obj ������ ��������
	 */
    function debug($obj){
        if ($_SESSION['login'] == 'sawa') print_r($obj);
    }
    
	
    /**
     * �������� �� ������������ ���������������
     *
     * @param bit $role ���� ������������ � ������� (������� ���� �� users.role)
     * @return boolean 1 - �����, ����� 0
     */
    function is_admin($role = NULL){
        return substr($role===NULL ? $_SESSION['role'] : $role, 3, 1);
    }
    
    /**
     * �������� �� ������������ ��������������� ���
     *
     * @param bit $role ���� ������������ � ������� (������� ���� �� users.role)
     * @return boolean 1 - �����, ����� 0
     */
	function is_admin_sbr($role = NULL){
        return substr($role===NULL ? $_SESSION['role'] : $role, 5, 1);
    }
	
    /**
     * �������� �� ������������ ����� ���������������
     *
     * @param bit $role ���� ������������ � ������� (������� ���� �� users.role)
     * @return boolean 1 - �����, ����� 0
     */
    function is_admin_sm($role = NULL){
        return substr($role===NULL ? $_SESSION['role']: $role, 4, 1);
    }
    
	/**
     * �������� �� ������������ �����������
     *
     * @param bit $role ���� ������������ � ������� (������� ���� �� users.role)
     * @return boolean 1 - �����, ����� 0
     */
    function is_moder($role = ''){
        if ($role == '' && $_SESSION['role']) return substr($_SESSION['role'], 1, 1);
        if ($role) return substr($role, 1, 1);
        else return 0;
    }
    
	/**
     * �������� �� ����������������������
     *
     * @param bit $role ���� ������������ � ������� (������� ���� �� users.role)
     * @return boolean 1 - ��������, ����� 0
     */
    function is_redactor($role = ''){
        if ($role == '' && $_SESSION['role']) return substr($_SESSION['role'], 2, 1);
        if ($role) return substr($role, 2, 1);
        else return 0;
    }
    
	/**
	 * �������� �� ������ �� ������ ������������ "sigitov"
	 *
	 * @deprecated �� ��������....
	 * @return boolean
	 */
    function is_mamadmin(){
        //	if (substr($_SESSION['role'], 2, 1) || $_SESSION['login'] == "sigitov") return 1;
        if (is_admin() || $_SESSION['login'] == "sigitov") return 1;
        return 0;
    }
    
	/**
	 * �������� �� ������������ �������������
	 *
	 * @param bit $role ���� ������������ � ������� (������� ���� �� users.role)
     * @return boolean 1 - ������������, ����� ���������
	 */
    function is_emp($role = ''){
        if ($role == '' && $_SESSION['role']) return substr($_SESSION['role'], 0, 1);
        if ($role) return substr($role, 0, 1);
        else return 0;
    }
    
    /**
     * �������� �� ������������ ���
     *
     * @param boolean $exact ��������� ������. ���� ����� �� ������ ������������
     * @param integer $uid   ��������� �������� ��� ������������ c uid = $uid � �� ��� ��������
     * @return boolean
     */
    function is_pro($exact = false, $uid = false) {
        if ($exact){
            require_once (ABS_PATH."/classes/payed.php");
            if ($uid === false)	{ 
	            return payed::CheckPro($_SESSION['login']);
            }
            else {
            	global $DB;
				$sql = "SELECT login FROM users
		          WHERE uid=?";
				$login  = $DB->val($sql, $uid);
            	return payed::CheckPro($login);
            }
        } else {
            return $_SESSION['pro_last'];
        }  
    }

    /**
     * �������� URL �� ����������
     * 
     * @param string	url	������ URL
     * @param boolean   $protocol ��������� ������� ��������� � ������ ������
     * @return boolean
     */
    function url_validate($url, $protocol = false)
    {
        $protPattern = $protocol ? 'https?://' : '';
        if ( !preg_match ("~^".$protPattern."(?:(?:[a-z0-9_-]{1,32}(?::[a-zA-Z�-��-ߨ�0-9_-]{1,32})?@)?)?(?:(?:[a-z0-9�-�A-ߨ�_-]{1,128}\.)+([a-zA-Z�-��-�]{2,15})|(?!0)(?:(?!0[^.]|255)[0-9]{1,3}\.){3}(?!0|255)[0-9]{1,3})(?:/[a-zA-Z�-��-ߨ�0-9.,_@%&;?+=\:\(\)\~/-]*)?(?:#[^ '\"&<>]*)?$~i", $url)) {
            return 0;
        }
        else {
            return 1;
        }
    }
    
	/**
	 * ���������� �������������� � ���������� �������� ����� ��������� ��� [��� ������(2), ��� ������(3)]
	 *
	 * @deprecated 
	 * 
	 * @return array [��� ������(2), ��� ������(3)]
	 */
    function getGeo(){
        //$geo_record = geoip_record_by_name(getRemoteIP());
        $moscow = array('Moskau', 'Moskva', 'Moskovskiy', 'Moskovskaya', 'Moscow', 'Moscou');
        $spb = array('Saint Petersburg', 'Sankt Petersburg', 'Sankt-Peterburg', 'Piter', 'Leningrad', 'Leningradskaya', 'Leninskiy');
        if (in_array($geo_record['city'], $moscow)) return array('10', '100');
        if (in_array($geo_record['city'], $spb)) return array('10', '010');
        if ($geo_record['country_code'] == 'RU') return array('10', '001');
        return array('01', '001');
    }
	
    /**
     * �������� ������ ��� ��� �����������
     *
     * @param string $middle		��� ������������
     * @param string $logo_height ������ �������� (��������)
     * @param string $link		������ �� ������������	
     * @return string HTML - ���
     */
    function view_pro($middle = false, $logo_height = false, $link = true, $title = '������� �������'){
    	$sHeight = ($logo_height)?('height: '.$logo_height.'px'):'';
    	if ($link){
    		$pre = "<a class=\"b-layout__link\" href=\"/payed/\" target=\"_blank\">";
    		$post = "</a>";
    	}
        /*return $pre."<img ".($logo_height?'height=\"'.$logo_height.'px\"':'')." src=\"/images/icons/f-pro.png\" alt=\"\" class=\"pro\"" . ($middle?" style=\"vertical-align:middle;$sHeight \"":"") . " border=\"0\" />".$post;*/
        return $pre."<span alt=\"{$title}\" title=\"{$title}\" class=\"b-icon b-icon__pro b-icon__pro_f {$middle}\"></span>".$post;
    }
    
    	/**
    	 *	��������� ������� view_pro() � ������ ��������� ��������
    	 *
    	 * @see view_pro()
    	 * 
    	 * @param unknown_type $is_pro_test ��������� �� ���� ���
    	 * @param string $middle		��� ������������
     	 * @param string $logo_height ������ �������� (��������)
     	* @param string $title        ����� ������ ��� ������
    	 * @return string HTML - ���
    	 */
     function view_pro2($is_pro_test = false, $middle = false, $logo_height = false, $title = '������� �������'){
          $sHeight = ($logo_height)?('height: '.$logo_height.'px'):'';
          if ($is_pro_test)	$image = "t";
          else $image = "f";
          
          if ($title) {
              $title = " title='{$title}' ";
          }
          
          /*return "<a href=\"/payed/\" class=\"b-layout__link\" {$title}><img ".($logo_height?'height=\"'.$logo_height.'\"':'')." src=\"/images/".$image."\" alt=\"PRO\" {$title}" . ($middle?" style=\"border:0;vertical-align:middle;$sHeight \"":"style=\"border:0; \"") . "  /></a>";*/
          return "<a class=\"b-layout__link\" target=\"_blank\" href=\"/payed/\" {$title}><span alt=\"������� �������\" class=\"b-icon b-icon__pro b-icon__pro_".$image."\" {$title}></span></a>";
    }
    
    
    
    /**
     * �������� ������ PROFI a�������
     * 
     * @return string
     */
    function view_profi($class = 'b-icon_top_1')
    {
        return "<a class=\"b-layout__link\" target=\"_blank\" href=\"/profi/\"><span  data-profi-txt=\"������ ���������� ����� FL.ru. �������� �� ����� ����� 2-� ���, ������ ����������� �������� � ����� �� ����� 98% ������������� �������.\" class=\"b-icon b-icon__lprofi {$class}\"></span></a>";
    }
    
    
    
    	/**
    	 *
    	 *
    	 * @return string HTML - ���
    	 */
     function view_pro_icon($is_pro_test){
          if ($is_pro_test)	$b_icon__pro = "b-icon__pro_t";
          else $b_icon__pro = "b-icon__pro_f";
          return $b_icon__pro;
    }

    /**
     * �������� ������ ��� ������������ (���� ������)
     *

     * @todo � �� ����� �������� ��� ���������� � view_pro ��� �������� ���� ������������ ������ � ��� �������� ���?
     * 
     * @param string $middle		��� ������������
     * @param string $logo_height 	������ �������� (��������)
     * @return string HTML - ���
     */

    function view_pro_emp($middle = false, $logo_height = false){
       $sFile = ($logo_height)?('icons/e-pro-s.png'):'icons/e-pro.png';
    /*	return "<a href=\"/payed-emp/\" class=\"ac-epro\"><img ".($logo_height?'height="'.$logo_height.'"':'')." src=\"/images/$sFile\" alt=\"\"  style=\"border:0;\" width=\"26\" height=\"11\"/></a>"; */
       return "<a class=\"b-layout__link\" href=\"/payed-emp/\" target=\"_blank\"><span alt=\"������� �������\" title=\"������� �������\" class=\"b-icon b-icon__pro b-icon__pro_e {$middle}\"></span></a>"; 
    }
				
/**
     * @return string ���������� ����� ��� ������ ��� ��� null
     * 
    function get_pro_icon_class ($is_pro, $is_emp, $is_team, $is_pro_test, $is_freezed){
        if ($is_pro && !$is_team && !$is_pro_test) {
            if ($is_emp) {
                if (!$is_freezed) {
                    $b_icon__pro = 'b-icon__pro_e';
                } else {
                    $b_icon__pro = 'b-icon__pro_z';
                }
            } else {
                if (!$is_freezed) {
                    $b_icon__pro = 'b-icon__pro_f';
                } else {
                    $b_icon__pro = 'b-icon__pro_z';
                }
            }
        } else if ($is_team) {
            $b_icon__pro = 'b-icon__pro_team';
        } else if ($is_pro_test) {
            $b_icon__pro = 'b-icon__pro_t';
        }
        return $b_icon__pro;
    }
     */
					
	/**
	 * ���������� ��� ������ ������������ �� �����
	 *
	 * @return string HTML-���
	 */
    function view_vip()
    {
     //0002199 return '<span class="vip">&nbsp;<img src="/images/ico_vip.gif" border="0" alt="VIP" class="vip" /></span>';
    }
    
    function view_verify($title = '���������������� ������������', $css = 'b-icon_valign_middle') {
        return '&#160;<a class="b-layout__link" href="/promo/verification" title="' . $title . '" alt="' . $title . '" target="_blank"><span class="b-icon b-icon__ver "></span></a>';
    }
    
    function view_team_fl() {
        return '<a class="b-layout__link" href="/about/team/" target="_blank"><span alt="������� Free-lance.ru" title="������� Free-lance.ru" class="b-icon b-icon__pro b-icon__pro_team" title="������� Free-lance.ru"></span></a>';    
    }
    
    function view_sbr_shield($aClass = '', $spanClass = '') {
        return '<a class="b-layout__link ' . $aClass . '" href="/promo/bezopasnaya-sdelka/" target="_blank" title="������ ����� ���������� ������" target="_blank"><span class="b-icon b-icon__shield ' . $spanClass . '"></span></a>';
    }
    
    function view_reserve_shield() 
    {
        return '<a class="b-layout__link " href="/promo/bezopasnaya-sdelka/" target="_blank" title="������������ ������� ����� ���������� ������" target="_blank"><span class="b-icon b-icon__shield"></span></a>';
    }
    
    function view_mark_user($value, $pfx = '', $pro2 = true, $nbsp = "") {
        /*!!!is_team!!!*/
        
        if(@$value[$pfx . 'is_profi'] == 't'){
            $pro = '&#160;' . view_profi();
        } else {
            if($pro2) $is_pro = '&#160;'.view_pro2($value['is_pro_test'] == 't');
            else $is_pro = '&#160;'.view_pro();
            $pro = ($value[$pfx.'is_pro'] == 't' ? (is_emp($value[$pfx.'role'] ) ? '&#160;'.view_pro_emp() : $is_pro) : ""); 
        }

        $is_team = '&#160;'.view_team_fl();
        if (is_string($value['is_verify'])) {
            $isVerify = $value['is_verify'] === 't';
        } elseif (is_bool($value['is_verify'])) {
            $isVerify = $value['is_verify'];
        } else {
            // ��� ����� ������ ��������, ����� �� ���� �������� ������������
            // ������ ����������� ���������� �������� � ����
            $isVerify = $value[$pfx . 'login'] && is_verify($value[$pfx . 'login']);
        }
        if ($isVerify) {
            $pro     .= view_verify();
            $is_team .= view_verify();
        }
        
        
        return ($value[$pfx.'is_team']=='t'?$is_team:$pro); 
    }
    
    function view_mark_user2($value) {
        $mark = '';
        
        if(@$value['is_profi'] == 't') {
            $mark .= '&#160;'.view_profi();
        } else {
            $is_pro = '&#160;'.view_pro2($value['is_pro_test'] == 't');
            $mark .= $value['is_pro'] == 't' ? (is_emp($value['role']) ? '&#160;'.view_pro_emp() : $is_pro) : '';
        }
        
        $is_team = '&#160;'.view_team_fl();
        $mark .= $value['is_team']=='t' ? $is_team : '';
        
        if (is_string($value['is_verify'])) {
            $isVerify = $value['is_verify'] === 't';
        } elseif (is_bool($value['is_verify'])) {
            $isVerify = $value['is_verify'];
        }
        $mark .= $isVerify ? view_verify() : '';
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
        $is_sbr = sbr_meta::hasReserves((int)$value['uid']);
        if ($is_sbr) {
            $mark .= '&#160;'.view_reserve_shield();
        }
        
        return $mark;
    }
    
    /**
     * ������� ������ PRO � TEAM � ������� span (�� img)
     * @param boolean $is_pro
     * @param boolean $is_emp
     * @param boolean $is_team
     * @param boolean $nbsp ������� ������� ����� �� ������ (&nbsp;)
     * @return string html ��� �������
     */
    function view_mark_user_div($is_pro, $is_emp, $is_team, $nbsp) {
        $out = "";
        if ($is_pro && !$is_team) {
            if ($is_emp) {
                $out .=  '<span alt="������� �������" title="������� �������" class="b-icon b-icon__pro b-icon__pro_e"></span>';
            } else {
                $out .=  '<span alt="������� �������" title="������� �������" class="b-icon b-icon__pro b-icon__pro_f"></span>';
            }
        }
        if ($is_team) {
            $out .= '<span alt="������� Free-lance.ru" title="������� Free-lance.ru" class="b-icon b-icon__pro b-icon__pro_team"></span>';
        }
        return $out;
    }
	
    /**
     * ���������� ���������� ��� �����, �� ��� ������, � HTML-���
     *
     * @global $session ������ ������������
     * 
     * @param array   $user				���������� � �����	 
     * @param string  $pfx				������� �������
     * @param string  $cls				����� ������ ������ ���������� ��� HTML-����
     * @param string  $sty				�������������� �����
     * @param string  $prms				�������������� ��������� ��� ������ �� ������������
     * @param boolean $view_ol_status	���������� ������ ������������ ���� ���
     * @param boolean $is_link			��� ������������ ���������� ��� ������ ���� ���
     * @return string HTML-���
     */
    function view_user($user,$pfx='',$cls='',$sty='',$prms='',$view_ol_status=TRUE,$is_link=TRUE)
    {
      global $session;
      if(is_object($user))
        $user = get_object_vars($user);
      $is_emp=is_emp($user[$pfx.'role']);
      $login=$user[$pfx.'login'];
      $uname=$user[$pfx.'uname'];
      $usurname=$user[$pfx.'usurname'];

      if($sty)  $sty = " style='$sty'";
      if(!$cls) $cls = ($is_emp ? 'emp' : 'frl').'name11';

      if (@$user[$pfx.'is_profi'] == 't') {
        $pro = '&#160;' . view_profi();
      } else {
        $pro = ($user[$pfx.'is_pro']=='t' ? ($is_emp ? '&#160;'.view_pro_emp() : '&#160;'.view_pro2($user[$pfx.'is_pro_test']=='t')).'' : '');
      }
      
      $is_team = view_team_fl();
      if(is_verify($login)) {
          $pro     .= view_verify();
          $is_team .= view_verify();
      } 
      return (   "<span class='{$cls}'{$sty}>".
                 ($is_link ? "<a class='{$cls}'{$sty} href='/users/{$login}/{$prms}' title='{$uname} {$usurname}'>" : '').highlight2($uname, $user[$pfx.'=SEARCH=']).' '.highlight2($usurname, $user[$pfx.'=SEARCH=']).($is_link ? '</a>' : '').
                 ' ['.($is_link ? "<a class='{$cls}'{$sty} href='/users/{$login}/{$prms}' title='{$login}'>" : '').highlight2($login, $user[$pfx.'=SEARCH=']).($is_link ? '</a>' : '').']'.
                 ($user['boss_rate']==1 ? view_vip() : '').
                 "</span>"
																	.($user[$pfx.'is_team']=='t'?'&#160;'.$is_team:$pro) 
														);
    }
    /**
     * ���������� ���������� ��� �����, �� ��� ������, � HTML-���
     *
     * @global $session ������ ������������
     * 
     * @param array   $user				���������� � �����	 
     * @param string  $pfx				������� �������
     * @param string  $cls				����� ������ ������ ���������� ��� HTML-����
     * @param string  $sty				�������������� �����
     * @param string  $prms				�������������� ��������� ��� ������ �� ������������
     * @param boolean $view_ol_status	���������� ������ ������������ ���� ���
     * @param boolean $is_link			��� ������������ ���������� ��� ������ ���� ���
     * @return string HTML-���
     */
      function view_user2($user,$pfx='',$cls='',$sty='',$prms='',$view_ol_status=FALSE,$is_link=TRUE, $onclick='') {
      global $session;
      if(is_object($user))
        $user = get_object_vars($user);
      $is_emp=is_emp($user[$pfx.'role']);
      $login=$user[$pfx.'login'];
      $uname=$user[$pfx.'uname'];
      $usurname=$user[$pfx.'usurname'];
      
      $sbr = false;
      if ( $user["completed_cnt"] > 0) {
          $sbr = true;
      }

      if($sty)  $sty = " style='$sty'";
      if(!$cls) $cls = ($is_emp ? 'emp' : 'frl').'name11';

      if (@$user[$pfx.'is_profi'] == 't') {
          $pro = view_profi();
      } else {
          $pro = ($user[$pfx.'is_pro']=='t' ? ($is_emp ? view_pro_emp() : view_pro2($user[$pfx.'is_pro_test']=='t')) : '');
      }
        
      $is_team = view_team_fl();
      if(is_verify($login)) {
          $pro     .= view_verify();
          $is_team .= view_verify();
      }
      $html = ($view_ol_status ? $session->view_online_status($login) . '&nbsp;' : '').
              ($is_link ? "<a class='{$cls}'{$sty} ".($onclick ? "onClick=\"{$onclick}\"" : "")." href='/users/{$login}/{$prms}' title='{$uname} {$usurname}'>" : '').highlight2($uname, $user[$pfx.'=SEARCH=']).' '.highlight2($usurname, $user[$pfx.'=SEARCH=']).($is_link ? '' : '').
              ' ['.highlight2($login, $user[$pfx.'=SEARCH=']).']'.($is_link?"</a>":"").' '.($user[$pfx.'is_team']=='t'?$is_team." ":$pro).
              ( $sbr? ' <a class="b-layout__link" href="/promo/bezopasnaya-sdelka/" title="������������ ������� ����� ���������� ������" target="_blank"><span class="b-icon b-icon__shield b-icon_top_1"></span></a>':'').
              ($user['boss_rate']==1 ? view_vip() : '');
      
              
      return $html;
      /*return (   ($user[$pfx.'is_pro']=='t' ? ($is_emp ? view_pro_emp() : view_pro2($user[$pfx.'is_pro_test']=='t')).'&nbsp;' : '').
                 "<font class='{$cls}'{$sty}>".
                 ($view_ol_status ? $session->view_online_status($login) : '').
                 '&nbsp;'.
                 ($is_link ? "<a class='{$cls}'{$sty} href='/users/{$login}/{$prms}' title='{$uname} {$usurname}'>" : '').highlight2($uname, $user[$pfx.'=SEARCH=']).' '.highlight2($usurname, $user[$pfx.'=SEARCH=']).($is_link ? '</a>' : '').
                 ' ['.($is_link ? "<a class='{$cls}'{$sty} href='/users/{$login}/{$prms}' title='{$login}'>" : '').highlight2($login, $user[$pfx.'=SEARCH=']).($is_link ? '</a>' : '').']'.
                ($user['boss_rate']==1 ? view_vip() : '').
                 "</font>"      );  */  
    }    
    /**
     * ���������� ���������� ��� �����, �� ��� ������, � HTML-��� (����� ������)
     * 
     * @global session $session
     * @param type $user
     * @return $session 
     */
    function view_user3($user, $query_link="") {
        global $session;
        
        if (is_object($user)) {
            $user = get_object_vars($user);
        }
        
        $is_emp = is_emp($user['role']);
        $is_emp_pfx = $is_emp ? 'e' : 'f';
        $is_emp_pfx = $user['is_pro_test'] === 't' ? 'test' : $is_emp_pfx;
        
        $login = $user['login'];
        $link = '/users/' . $login;
        $login_cls = $is_emp ? '6db335' : 'fd6c30';
        $username = $user['uname'] . ' ' . $user['usurname'];

        $html = $session->view_online_status($login).'<a class="b-username__link b-username__link_color_000 b-username__link-empty" href="' . $link . $query_link . '">' . $username . '</a> 
                <span class="b-username__login-mark">
                <span class="b-username__login b-username__login_color_' . $login_cls . '">[<a class="b-username__link b-username__link_color_' . $login_cls . '" href="' . $link . $query_link . '">' . $login . '</a>]</span>';
        
        //$pro = '&#160;' . view_profi();
        
        $is_pro = $user['is_pro'] == 't' || $user['is_pro_tes'] == 't';
        $html .=  ( $is_pro || $user['is_team'] == 't' || @$user['is_profi'] == 't' ? ' ': '') .'<span class="b-username__marks">';
        
        if (@$user['is_profi'] == 't') {
            $html .= view_profi();
        } else {
            if ($is_pro && $user['is_team'] == 'f') {
                /* $is_emp_pfx = $is_emp_pfx . 'pro'; */
                $title = 'PRO';
                $html .= $is_emp ? view_pro_emp() : view_pro();
            }
        }
        
        
        if ($user['is_team'] == 't') {
            $is_emp_pfx = 'team';
            $title = '������� Free-lance.ru';
            $html .= view_team_fl(); 
            //$html .= ' <a class="b-layout__link" href="/about/team/" target="_blank"><span alt="������� �������" title="������� �������" class="b-icon b-icon__pro b-icon__pro_' . $is_emp_pfx . ' "></span></a>'.(is_verify($login)? view_verify():'');
        }
        
        $is_verify = !empty($user['is_verify']) ? ( $user['is_verify'] == 't' ) : is_verify($login);
        
        $html .= ($is_verify? view_verify():'').'</span>';
        $status = $session->getActivityByLogin($user['login']);
        $last_ref_unixtime = strtotime($status);
        if ($status && (time() - $last_ref_unixtime <= 30*60)){
            $ago = ago_pub(strtotimeEx($status));
            if (intval($ago) == 0) {
                $ago = "����� ������";
            }
            
           /* $html .= '&#160;<span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_fd6c30 b-layouyt__txt_weight_normal">�� �����</span>';*/
        } else {
           /* $html .= '&#160;<span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_808080 b-layouyt__txt_weight_normal">��� �� �����</span>';*/
        }
        $html .= '</span>';
        
        return $html;
    }
    
    /**
     * ���������� ������������
     *
     * @param array  $user  ������ ������������
     * @param string $pfx   ������� ������
     * @param string $cls   ����� ����������� ������ �� ������������ (��� ������������ ���� ��� ����������)
     * @param string $sty   �������������� ����� �����������(���� ���������)
     * @return string
     */
    function __prntUsrInfo(                              
    $user,
    $pfx='',
    $cls='',
    $sty='',
    $hyp=false)
    {
        $user = (array)$user;
      global $session;
      $is_emp=is_emp($user[$pfx.'role']);
      $login=$user[$pfx.'login'];
      $uname=$user[$pfx.'uname'];
      $usurname=$user[$pfx.'usurname'];

      if($sty)  $sty = " style='$sty'";
      else{
          if($is_emp) $sty = " style='color:green'";
      }
      if(!$cls) $cls = ($is_emp ? 'employer' : 'freelancer').'-name';

      //require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
      //return (   (payed::CheckPro($login) ? ($is_emp ? view_pro_emp() : view_pro()).'&nbsp;' : '').

      if($hyp) {
          $uname = hyphen_words($user['dsp_uname']? $user['dsp_uname']: $uname);
          $usurname = hyphen_words($user['dsp_usurname']? $user['dsp_usurname']: $usurname);
      }
      $pro = ($user[$pfx.'is_pro']=='t' ? ($is_emp ? view_pro_emp() : view_pro2($user[$pfx.'is_pro_test']=='t')).'&nbsp;' : '');
      $is_team = view_team_fl()."&nbsp;";
      
      return (   
                 "<span class='{$cls}'{$sty}>".
                 ($user['is_admin'] == 't' ? '<span class="cau-admin">Admin</span>&nbsp;' : '').
                 "<a class='{$cls}'{$sty} href='/users/{$login}' title='{$uname} {$usurname}'>".$uname." ".$usurname."\n".
                 " [".($user['dsp_login']? $user['dsp_login']: $login)."]</a>".
                 "</span>&nbsp;" 
																	.($user[$pfx.'is_team']=='t'?$is_team:$pro).$session->view_online_status($login)
																	 );
    }
    
	/**
	 * ��������� ������������� ����� � ������
	 *
	 * @param string $text	�����
	 * @param string $word	����� ��� ���������
	 * @return string ����� � ����������� �����
	 */
    function highlight($text, $word, $fs=false){
        setlocale(LC_ALL, 'ru_RU.CP1251');
        $temp_repl = array();
        if(preg_match_all("/\<a([^\>]*)\>([\w\s\W\S]+)\<\/a>/im", $text, $res)){
            for($i = 0; $i < count($res[0]); $i++){
                if(empty($res[2][$i]) || empty($word) || !strpos($res[2][$i], $word)) continue;
                $out_part = '<a'.$res[1][$i].'>'.str_ireplace($word, '<span '.($fs ? 'style="font-size:'.(int)$fs.'px"' : '').' class="highlight-search">'.$word.'</span>', $res[2][$i]).'</a>';
                $temp_repl[$i] = $out_part;
                $text = str_ireplace($res[0][$i], '#'.$i.'#', $text);
            }
        }
        $out = str_ireplace($word, '<span '.($fs ? 'style="font-size:'.(int)$fs.'px"' : '').' class="highlight-search">'.$word.'</span>', $text);
        if(count($temp_repl)){
            foreach ($temp_repl as $k=>$v){
                $out = str_ireplace('#'.$k.'#', $v, $out);
            }
        }
        setlocale(LC_ALL, 'en_US.UTF-8');
        return $out;
    }
    
	/**
	 * @see highlight();
	 * @ignore 
	 */
    function highlight2($text, $word){
        if(!$word) return $text;
        setlocale(LC_ALL, 'ru_RU.CP1251');
        $out = preg_replace ('/(('.preg_quote($word,'/').')+)/i', '<span class="marked">$1</span>', $text);
        setlocale(LC_ALL, 'en_US.UTF-8');
        return $out;
    }

    /**
     * ����� ������� ���������� ������������ �� ������ �������
     *
     * @return string IP-������
     */
    function getRemoteIP(){
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) return $_SERVER['HTTP_X_REAL_IP'];
        else return $_SERVER['REMOTE_ADDR'];
    }
	
    /**
     * ���������� �� ������� � �������
     *
     * @deprecated 
     * 
     * @return array
     */
    function arrayColumnSort()
    {
        $n = func_num_args();
        $ar = func_get_arg($n-1);
        if(!is_array($ar))
        return false;


        for($i = 0; $i < $n-1; $i++)
        $col[$i] = func_get_arg($i);

        foreach($ar as $key => $val)
        foreach($col as $kkey => $vval)
        if(is_string($vval))
        ${"subar$kkey"}[$key] = $val[$vval];

        $arv = array();
        foreach($col as $key => $val)
        $arv[] = (is_string($val) ? ${"subar$key"} : $val);
        $arv[] = $ar;

        call_user_func_array("array_multisort", $arv);
        return $ar;
    }
    
    /**
     * ���������� true ���� ������ ������� ������ �� 0 (��� '')
     *
     * @param array $arr
     * @return boolean
     */
    function isNulArray($arr){
        if (is_array($arr))
            foreach ($arr as $val){
                if (strlen($val) == 0 || $val === 0) continue;
                return false;  
            }
        else if ($arr) return false;
        return true;   
    }

    /**
     * ������ �� �������� �������, �������� �� �� ����������, ������ ��� ������ �� ������, ������ ���-�� ���������
     *
     * @todo �� ������ ������� � ������������� �� � �������?
     * 
     * @param string $text ����� ������
     * @param string $role ���������� �� ������������ � ������� (�������� ���� ����� � �������)
     * @param string $pro  ��� ���������� (�������� �������� ��� �� ���)
     * @return string
     */
    function user_in_color ($text,$role, $pro)
    {

      /*  switch ($role) {
            case "000000":
                if ($pro) return '<font color="#fd6c30">'.$text.'</font>';
                else return '<font color="#666666">'.$text.'</font>';
                break;
            case "100000":
                return '<font color="#78B42A">'.$text.'</font>';
                break;
            case "010000":
                if ($pro) return '<font color="#F7522C">'.$text.'</font>';
                else return '<font color="#666666">'.$text.'</font>';
                //		return '<font color="#65a9f5">'.$text.'</font>';
                break;
            case "000100":
                if ($pro) return '<font color="#F7522C">'.$text.'</font>';

                else return '<font color="#666666">'.$text.'</font>';
                //		return '<font color="#5287f2">'.$text.'</font>';
                break;
        //default:
        //return $text;
        //break;
        }
        */
        
        switch ($pro) {
        	case 't':
        		return $text;
        		break;
        	case 'f':
        		return ''.$text.'';
        		break;
        	default:
        		return $text;
        		break;
        }

    }

  /**
   * ���������� �������� �������� �����.
   *
   * @param integer $tab_id ��� ��������
   * @return string �������� ��������
   */
    function view_tab_name ($tab_id)
    {
        switch ($tab_id)
        {
            case "1":
                $res = '������';
                break;
            case "0":
            default:
                $res = '���������';
                break;
        }
        return $res;
    }

  /**
   * ���������� ����������������� ���������.
   *
   * @param real $cost ���������
   * @param string $add �������
   * @param string $empty_val ����� ��� ������� ��������
   * @param boolean $bold ��������� ���� ������ �������
   * @return string ����������������� ���������
   */
    function view_cost($cost, $add = '', $empty_val = '���', $bold = true)
    {
        if (!isset($cost) || is_null($cost) || ($cost == 0) || ($cost == ''))
        {
            $ret = $empty_val;
        }
        else
        {
            $ret = ($add?$add . ' ' : '') . (($bold) ? '<strong>' : '') . '$' . floatval($cost) . (($bold) ? '</strong>' : '');
        }
        return $ret;
    }

    /**
    * ���������� �������� ������ �� ������
    *
    * @param int $currency ����� ������
    * @return string �������� ������
    */
    function get_currency_name($currency)
    {
        $currencies = array(0 => '$', 1 => '&euro;', 2 => ' ���.', 3 => ' FM',);
        return isset($currencies[$currency])?$currencies[$currency]:$currencies[0];
    }

  /**
   * ���������� ����������������� ��������� c �������.
   *
   * @param real 	$cost 		���������
   * @param string 	$add 		�������
   * @param string 	$empty_val 	����� ��� ������� ��������
   * @param boolean $bold 		��������� ���� ������ �������
   * @param integer $currency 	����� ������
   * @return string ����������������� ���������
   */
    function view_cost2($cost, $add = '', $empty_val = '���', $bold = true, $currency = 0)
    {
        // �������� ������ �� ������
        $cur_name = get_currency_name($currency);

        if (!isset($cost) || is_null($cost) || ($cost == 0) || ($cost == ''))
        {
            $ret = $empty_val;
        }
        else
        {
            $ret = ($add?$add . ' ' : '') . (($bold) ? '<strong>' : '') . floatval($cost) . ' ' . $cur_name . (($bold) ? '</strong>' : '');
        }
        /*else
        {
            $ret = ($add?$add . ' ' : '') . (($bold) ? '<strong>' : '') . floatval($cost) . ' ' .$cur_name . (($bold) ? '</strong>' : '');
        }*/
        return $ret;
    }

  /**
   * ���������� ����������������� �������� ���������.
   *
   * @param real 	$cost_from 		��������� ���������
   * @param real 	$cost_to 		�������� ���������
   * @param string 	$add 			�������
   * @param string 	$empty_val 		����� ��� ������� ��������
   * @param boolean $bold 			��������� ���� ������ �������
   * @return string ����������������� ���������
   */
    function view_range_cost($cost_from, $cost_to, $add = '', $empty_val = '���', $bold = true, $currency = 0)
    {
        // �������� ������ �� ������
        $cur_name = get_currency_name($currency);
        
        if ((!isset($cost_from) || is_null($cost_from) || ($cost_from == 0) || ($cost_from == ''))
        && (!isset($cost_to) || is_null($cost_to) || ($cost_to == 0) || ($cost_to == '')))
        {
            $ret = $empty_val;
        }
        else
        {
            $txt_cost = '';
            if (!(!isset($cost_from) || is_null($cost_from) || ($cost_from == 0) || ($cost_from == '')))
            {
                $txt_cost .= '�� ' . (($bold) ? '<strong>' : '') . floatval($cost_from) . (($bold) ? '</strong>' : '');
            }
            if (!(!isset($cost_to) || is_null($cost_to) || ($cost_to == 0) || ($cost_to == '')))
            {
                $txt_cost .= ($txt_cost?' ':'') . '�� ' . (($bold) ? '<strong>' : '') . floatval($cost_to) . (($bold) ? '</strong>' : '');
            }
            $txt_cost .= ($txt_cost?' '.$cur_name:'');
            $ret = ($add?$add . ' ' : '') . $txt_cost;
        }
        return $ret;
    }

    /**
    * ���������� ������ ���� �� ���� ����������
    * �-� ������������� ��� ������������� �� ������� �������, �� ���� ��������� ���������� �� ������ �� ������,
    * �������� ����
    *
    * @param real    $cost_from      ��������� ���������
    * @param real    $cost_to        �������� ���������
    * @param integer $currency       ����� ������
    * @return string ����������������� ���������
    */
    function view_one_cost($cost_from, $cost_to, $currency = 0)
    {
        $ret = '';

        // �������� ������ �� ������
        $cur_name = get_currency_name($currency);

        $cost_from = intval($cost_from);
        $cost_to = intval($cost_to);        

        if ($cost_from || $cost_to) {
            $cost = $cost_to?$cost_to:$cost_from;

            $ret = $cost.' '.$cur_name;
        }

        return $ret;
    }

  /**
   * ���������� ����������������� �������� ��������� c �������.
   *
   * @param real 	$cost_from 		��������� ���������
   * @param real 	$cost_to 		�������� ���������
   * @param string 	$add 			�������
   * @param string 	$empty_val 		����� ��� ������� ��������
   * @param boolean $bold 			��������� ���� ������ �������
   * @param integer $currency 		����� ������
   * @return string ����������������� ���������
   */
    function view_range_cost2($cost_from, $cost_to, $add = '', $empty_val = '���', $bold = true, $currency = 0)
    {
        // �������� ������ �� ������
        $cur_name = get_currency_name($currency);

        if ((!isset($cost_from) || is_null($cost_from) || ($cost_from == 0) || ($cost_from == ''))
        && (!isset($cost_to) || is_null($cost_to) || ($cost_to == 0) || ($cost_to == '')))
        {
            $ret = $empty_val;
        }
        else
        {
            $txt_cost = '';
            //if (($currency == 1) || ($currency == 2) || ($currency == 3)) {
                if ((!(!isset($cost_from) || is_null($cost_from) || ($cost_from == 0) || ($cost_from == ''))) && (!(!isset($cost_to) || is_null($cost_to) || ($cost_to == 0) || ($cost_to == ''))) && ($cost_to == $cost_from))
                {
                    $txt_cost .= (($bold) ? '<strong>' : '') . number_format(floatval($cost_from),0,'.',' ') . (($bold) ? '</strong>' : '');
                } else {
                    if (!(!isset($cost_from) || is_null($cost_from) || ($cost_from == 0) || ($cost_from == '')))
                    {
                        $txt_cost .= '<span class="b-layout__bold">���������:</span> �� ' . (($bold) ? '<strong>' : '') . number_format(floatval($cost_from),0,'.',' ') . (($bold) ? '</strong>' : '');
                    }
                    if (!(!isset($cost_to) || is_null($cost_to) || ($cost_to == 0) || ($cost_to == '')))
                    {
                        $txt_cost .= ($txt_cost?' ':'') . '�� ' . (($bold) ? '<strong>' : '') . number_format(floatval($cost_to),0,'.',' ') . (($bold) ? '</strong>' : '');
                    }
                }
                $txt_cost .= ($txt_cost ? ' ' . $cur_name:'');
            /*} else {
                if ((!(!isset($cost_from) || is_null($cost_from) || ($cost_from == 0) || ($cost_from == ''))) && (!(!isset($cost_to) || is_null($cost_to) || ($cost_to == 0) || ($cost_to == ''))) && ($cost_to == $cost_from))
                {
                    $txt_cost .= (($bold) ? '<strong>' : '') . $cur_name . number_format(floatval($cost_from),0,'.',' ') . (($bold) ? '</strong>' : '');
                } else {
                    if (!(!isset($cost_from) || is_null($cost_from) || ($cost_from == 0) || ($cost_from == '')))
                    {
                        $txt_cost .= '�� ' . (($bold) ? '<strong>' : '') . $cur_name . number_format(floatval($cost_from),0,'.',' ') . (($bold) ? '</strong>' : '');
                    }
                    if (!(!isset($cost_to) || is_null($cost_to) || ($cost_to == 0) || ($cost_to == '')))
                    {

                        $txt_cost .= ($txt_cost?' ':'') . '�� ' . (($bold) ? '<strong>' : '') . $cur_name . number_format(floatval($cost_to),0,'.',' ') . (($bold) ? '</strong>' : '');
                    }
                }
            }*/
            $ret = ($add ? $add . ' ' : '') . $txt_cost;
        }
        return $ret;
    }

  /**
   * ���������� ���������� ��� � ������������.
   *
   * @param integer $years ���������� ���
   * @return string "���", ���� ���������� ��� = 0 ��� ���������� ��� � ����� "���", "����", "���" � ����������� �� ���������� ���.
   */
    function view_exp($years)
    {
        $years = intval($years);
        $year = $years % 10;
        if ($years == 0) $ret = "���";
        elseif (($years >= 10) && ($years <= 20)) $ret = $years . " ���";
        elseif ($year == 1) $ret = $years . " ���";
        else if ($year > 1 && $year < 5) $ret = $years . " ����";
        else if (($year == 0) || ($year >= 5)) $ret = $years . " ���";
        return $ret;
    }

  /**
   * ���������� ����������������� �����.
   *
   * @param integer $time_value �������� ������� �������
   * @param ingteger $time_type ��� ������� ������� (0 - ����, 1 - ���, 2 - ������, 3-������, 4 - �������)
   * @return string ����������������� �����
   */
    function view_time($time_value, $time_type)
    {
        if (!isset($time_value) || is_null($time_value) || ($time_value == 0) || ($time_value == '') || ($time_type < 0)  || ($time_type > 5) )
        {
            $ret = '';
        }
        else
        {
            $mod1 = $time_value % 10;
            switch ($time_type)
            {
                //����
                default:
                case 0:
                    if (($mod1 == 1) && ($time_value < 10 || $time_value > 20)) $add = "���";
                    elseif (($mod1 < 5) && ($mod1 > 1) && ($time_value < 10 || $time_value > 20)) $add = "����";
                    else $add = "�����";
                    break;
                    //���
                case 1:
                    if (($mod1 == 1) && ($time_value < 10 || $time_value > 20)) $add = "����";
                    elseif (($mod1 < 5) && ($mod1 > 1) && ($time_value < 10 || $time_value > 20)) $add = "���";
                    else $add = "����";
                    break;
                    //������
                case 2:
                    if (($mod1 == 1) && ($time_value < 10 || $time_value > 20)) $add = "�����";
                    elseif (($mod1 < 5) && ($mod1 > 1) && ($time_value < 10 || $time_value > 20)) $add = "������";
                    else $add = "�������";
                    break;
                case 3:
                    if (($mod1 == 1) && ($time_value < 10 || $time_value > 20)) $add = "������";
                    elseif (($mod1 < 5) && ($mod1 > 1) && ($time_value < 10 || $time_value > 20)) $add = "������";
                    else $add = "�����";
                    break;
                case 4:
                    if (($mod1 == 1) && ($time_value < 10 || $time_value > 20)) $add = "�������";
                    elseif (($mod1 < 5) && ($mod1 > 1) && ($time_value < 10 || $time_value > 20)) $add = "�������";
                    else $add = "������";
                    break;


            }
            $ret = $time_value . ' ' . $add;
        }
        return $ret;
    }

  /**
   * ���������� ����������������� ����� ��� ������ �������.
   *
   * @deprecated 
   * 
   * @param integer $time_value �������� ������� �������
   * @param ingteger $time_type ��� ������� ������� (0 - ����, 1 - ���, 2 - ������, 3-������, 4 - �������)
   * @return string ����������������� �����
   */
    function view_time_wo_time($time_value, $time_type)
    {

        if (($time_type < 0)  || ($time_type > 5) )
        {
            $ret = '';
        }
        else
        {
            $time_value=($time_value ? $time_value : 0);
            $mod1 = $time_value % 10;
            switch ($time_type)
            {
                //����
                default:


                case 0:
                    if (($mod1 == 1) && ($time_value < 10 || $time_value > 20)) $add = "���";
                    elseif (($mod1 < 5) && ($mod1 > 1) && ($time_value < 10 || $time_value > 20)) $add = "����";
                    else $add = "�����";
                    break;
                    //���
                case 1:
                    if (($mod1 == 1) && ($time_value < 10 || $time_value > 20)) $add = "����";
                    elseif (($mod1 < 5) && ($mod1 > 1) && ($time_value < 10 || $time_value > 20)) $add = "���";
                    else $add = "����";
                    break;
                    //������
                case 2:
                    if (($mod1 == 1) && ($time_value < 10 || $time_value > 20)) $add = "�����";
                    elseif (($mod1 < 5) && ($mod1 > 1) && ($time_value < 10 || $time_value > 20)) $add = "������";
                    else $add = "�������";
                    break;
                case 3:
                    if (($mod1 == 1) && ($time_value < 10 || $time_value > 20)) $add = "������";
                    elseif (($mod1 < 5) && ($mod1 > 1) && ($time_value < 10 || $time_value > 20)) $add = "������";
                    else $add = "�����";
                    break;
                case 4:
                    if (($mod1 == 1) && ($time_value < 10 || $time_value > 20)) $add = "�������";
                    elseif (($mod1 < 5) && ($mod1 > 1) && ($time_value < 10 || $time_value > 20)) $add = "�������";
                    else $add = "������";
                    break;


            }
            $ret = $add;
        }
        return $ret;
    }

    function get_time_type_suffix($time_type, $time, $mod)
    {
        $condition = ($mod == 1 && substr(strval($time), -2) != 11);

        switch ($time_type)
        {
            //����
            default:
            case 0:
                $add = $condition?'����':'�����';
                break;
            //���
            case 1:
                $add = $condition?'���':'����';
                break;
            //������
            case 2:
                $add = $condition?'������':'�������';
                break;
            //������
            case 3:
                $add = $condition?'�����':'�����';
                break;
        }

        return $add;
    }   

    /**
    * echo plural_form(42, array('�����', '������', '�������')); 
    **/
    function plural_form($n, $forms) {
        return $n%10==1&&$n%100!=11?$forms[0]:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$forms[1]:$forms[2]);
    }

    function get_time_type_suffix_plural($time_type, $time)
    {
        $forms = array(
            0 => array('���', '����', '�����'),
            1 => array('����', '���', '����'),
            2 => array('�����', '������', '�������'),
            3 => array('������', '������', '�����'),
        );

        $form = isset($forms[$time_type])?$forms[$time_type]:$forms[0];
        return plural_form($time, $form);
    }

    /**
    * �������� ������ ���� �� ���� ������
    * �-� ������������� ��� ������������� �� ������� �������, �� ���� ��������� ������ �� ������ �� �����,
    * �������� ����
    *
    * @param integer $time_from_value    ������ ��������� �������
    * @param integer $time_to_value      ����� ��������� ������� �������
    * @param integer $time_type          ��� ������� ������� (0 - ����, 1 - ���, 2 - ������, 3 - ������)
    * @return string ����������������� �����
    */
    function view_one_time($time_from_value, $time_to_value, $time_type)
    {
        $ret = '';

        $time_from_value = intval($time_from_value);
        $time_to_value = intval($time_to_value);

        if ($time_from_value || $time_to_value) {
            $time = $time_to_value?$time_to_value:$time_from_value;

            // ��������� ��� ����������� ������� (�����, ����, �������, �����)
            $add = get_time_type_suffix_plural($time_type, $time);
            $ret = $time.' '.$add;
        }

        return $ret;
    }

  /**
   * ���������� ����������������� �������� �������.
   *
   * @param integer $time_from_value 	������ ��������� �������
   * @param integer $time_to_value 		����� ��������� ������� �������
   * @param integer $time_type 			��� ������� ������� (0 - ����, 1 - ���, 2 - ������, 3 - ������)
   * @return string ����������������� �����
   */
    function view_range_time($time_from_value, $time_to_value, $time_type)
    {
        if (
        ((!isset($time_from_value) || is_null($time_from_value) || ($time_from_value == 0) || ($time_from_value == ''))
        && (!isset($time_to_value) || is_null($time_to_value) || ($time_to_value == 0) || ($time_to_value == '')))
        || ($time_type < 0)  || ($time_type > 3) )
        {
            $ret = '';
        }
        else
        {
            if ($time_from_value == $time_to_value) $time_from_value = 0;
            if (!$time_to_value) $mod = $time_from_value % 10;
            else $mod = $time_to_value % 10;

            // ��������� ��� ����������� ������� (�����, ����, �������, �����)
            $add = get_time_type_suffix($time_type, $time_to_value, $mod);

            $ret = '';
            if (isset($time_from_value) && !is_null($time_from_value) && ($time_from_value != 0) && ($time_from_value != ''))
            {
                $ret .= '<span class="b-layout__bold">���� ���������� �������:</span> �� ' . $time_from_value;
            }
            if (isset($time_to_value) && !is_null($time_to_value) && ($time_to_value != 0) && ($time_to_value != ''))
            {
                $ret .= (($ret == '') ? '' : ' ') . '�� ' . $time_to_value;
            }
            $ret .= ' ' . $add;
        }
        return $ret;
    }

    /**
     * ���������� �������� �������� � �������� ������������ �� ��� ����
     *
     * @param integer $num ��� ��������
     * @return string
     */
    function GetKind ($num){
        switch ($num) {
            case 0: case 1:
                return "�������";
                break;
            case 3:
                return "�� ��������";
                break;
            case 2:
			case 7:
                return "��������";
                break;
            case 4:
                return "��������";
                break;
            default:
                return "�������";
                break;
        }
    }

  /**
   * ��������� ���� �� �������� � POST. � ������, ���� �������� ����, �������� ��������� ��������.
   *
   * @param string $VarName �������� ����
   * @param mixed  $DefValue ��������� ��������
   * @return string
   */
    function InPost($VarName, $DefValue = ''){
        return ((isset($_POST[$VarName]))?(utf16parse($_POST[$VarName])):($DefValue));
    }

  /**
   * ��������� ���� �� �������� � GET. � ������, ���� �������� ����, �������� ��������� ��������.
   *
   * @param string $VarName �������� ����
   * @param mixed  $DefValue ��������� ��������
   * @return string
   */
    function InGet($VarName, $DefValue = ''){
        return ((isset($_GET[$VarName]))?(utf16parse($_GET[$VarName])):($DefValue));
    }

  /**
   * ��������� ���� �� �������� � Cookie. � ������, ���� �������� ����, �������� ��������� ��������.
   *
   * @param string $VarName �������� ����
   * @param mixed  $DefValue ��������� ��������
   * @return string
   */
    function InCookie($VarName, $DefValue = ''){
        return ((isset($_COOKIE[$VarName]))?(($_COOKIE[$VarName])):($DefValue));
    }

  /**
   * ��������� ���� �� �������� � Cache. � ������, ���� �������� ����, �������� ��������� ��������.
   *
   * @param string $VarName �������� ����
   * @param mixed  $DefValue ��������� ��������
   * @param string $CacheId �������� ������� � ����
   * @return string
   */
    function InCache($VarName, $DefValue = '', $CacheId = 'common'){
        return ((isset($_SESSION['cache'][$CacheId][$VarName]))?(($_SESSION['cache'][$CacheId][$VarName])):($DefValue));
    }
  /**
   * ��������� ���� �� �������� � GET, POST(������ � ����� �������) ������ ��������� � ����� ���������. � ������, ���� ������ �� �������, �������� ��������� ��������.
   *
   * @param string $VarName �������� ����
   * @param mixed  $DefValue ��������� ��������
   * @return string
   */
    function InGetPost($VarName, $DefValue = ''){
        return ((isset($_GET[$VarName]))?(utf16parse($_GET[$VarName])):((isset($_POST[$VarName]))?(utf16parse($_POST[$VarName])):($DefValue)));
    }

  /**
   * ��������� ���� �� �������� � POST, GET(������ � ����� �������) ������ ��������� � ����� ���������. � ������, ���� ������ �� �������, �������� ��������� ��������.
   *
   * @param string $VarName  �������� ����
   * @param mixed  $DefValue ��������� ��������
   * @return string
   */
    function InPostGet($VarName, $DefValue = ''){
        return ((isset($_POST[$VarName]))?(utf16parse($_POST[$VarName])):((isset($_GET[$VarName]))?(utf16parse($_GET[$VarName])):($DefValue)));
    }
  /**
   * ��������� ���� �� �������� � GET, POST, Cache(������ � ����� �������) ������ ��������� � ����� ���������. � ������, ���� ������ �� �������, �������� ��������� ��������.
   *
   * @param string $VarName �������� ����
   * @param mixed  $DefValue ��������� ��������
   * @param string $CacheId �������� ������� � ����
   * @return string
   */
    function InGetPostCache($VarName, $DefValue = '', $CacheId = 'common'){
        return ((isset($_GET[$VarName]))?(utf16parse($_GET[$VarName])):((isset($_POST[$VarName]))?(utf16parse($_POST[$VarName])):((isset($_SESSION['cache'][$CacheId][$VarName]))?(($_SESSION['cache'][$CacheId][$VarName])):($DefValue))));
    }
  /**
   * ��������� ���� �� �������� � POST, GET, Cache(������ � ����� �������) ������ ��������� � ����� ���������. � ������, ���� ������ �� �������, �������� ��������� ��������.
   *
   * @param string $VarName �������� ����
   * @param mixed  $DefValue ��������� ��������
   * @param string $CacheId �������� ������� � ����
   * @return string
   */
    function InPostGetCache($VarName, $DefValue = '', $CacheId = 'common'){
        return ((isset($_POST[$VarName]))?(utf16parse($_POST[$VarName])):((isset($_GET[$VarName]))?(utf16parse($_GET[$VarName])):((isset($_SESSION['cache'][$CacheId][$VarName]))?(($_SESSION['cache'][$CacheId][$VarName])):($DefValue))));
    }
  /**
   * ���������� ������� ��� ������� InGetPost.. etc
   *
   * @param mixed $t �������� ��� ���������
   * @return string
   */
    function utf16parse($t)
    {
        $t = preg_replace('/\&\#([0-9]+)\;/me', "((\\1>255)?(utf8_decode(code2utf(\\1))):('&#\\1;'))", $t);
        return $t;
    }

    /**
     * Returns the utf string corresponding to the unicode value (from php.net, courtesy - romans@void.lv)
     *
     * @param integer $num �����
     * @return string
     */
    function code2utf($num)
    {
        if ($num < 128) return chr($num);
        if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
        if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
        if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
        return '';
    }
	
	/**
	 * �������� ������� �� ������������ ����� (��������� ���� � ������)
	 *
	 * @param string $hour   � ������ ���������� ������� ����� ���� ����, ���� ���� ������ � �������(HH:mm)
	 * @param integer $minute ���� ������ ���������� = -1 ������ ������ �������� � ���������� $hour
	 * @return boolean true - ���� ����� ���������, ����� false
	 */
    function checktime($hour, $minute = -1) {
        if ($minute == -1) list($hour,$minute) = explode(":",$hour);
        if ($hour > -1 && $hour < 24 && $minute > -1 && $minute < 60) {
            return true;
        } else return false;
    }
    
	/**
	 * ������������� HTML-��� ��� ������ ������, ���� �������� ������ (������������ � �������)
	 *
	 * @param string  $link			������
	 * @param string  $title   		�������� ������
	 * @param boolean $is_href		���������� �� ������ ���������� � ������� HTML � ����� <a>
	 * @param string  $class		����� ��� ������ ������
	 * @return stirng
	 */
    function ShowLink($link, $title, $is_href = 1, $class = "blue"){
        if ($is_href) return "<a href=\"".$link."\" class=\"".$class."\">".$title."</a>";
        return $title;
    }
    
	/**
	 * ��������� ����� ������ ��� ��� �� ���� �������
	 *
	 * @param string $prj_date ���� �������
	 * @return boolean
	 */
    function is_new_prj($prj_date)
    {
      return ($prj_date < '2008-07-17');
#    	return ($prj_date < '01.01.2008');
        //return (dateFormat("U",$prj_date) < dateFormat("U", "2008-06-23"));
    }
    
	/**
	 * ������ ������� intval(), ��� ���������� ��������� � ������ ����� � PgSQL, �� � PqSQL ������� ����� ������� ������ intval() �� ������
	 *
	 * @param integer $val ������ ��� ���������
	 * @return integer
	 */
    function intvalPgSql($val){
        $ret = intval($val);
        if ($ret > 2147483647) $ret = 2147483647;
        return $ret;
    }
    
    /**
     * �������� ��� �������� ������� � ������.
     * @see intvalPgSql()
     * @param mixed $arr   �������� ������. ���� ������, �� ���������� � ������ �� ������� ������.
     * @return array
     */
    function intarrPgSql($arr) {
        $arr = (array)$arr;
        array_walk_recursive($arr, '_intarrPgSql_callback');
        return $arr;
    }
    
    /**
     * @see intarrPgSql()
     * @param mixed $v   ������ �� ������� �������.
     */
    function _intarrPgSql_callback(&$v) {
        $v = intvalPgSql($v);
    }
    
    
	/**
	 * ������ ����� �� HTM�
	 *
	 * @param string $html HTML �����
	 * @return string
	 */
    function cleanHTML($html)
    {
        $bads = '<!--[if !vml]-->|<!--[endif]-->';
        $bads = addcslashes($bads,'![]');
        return preg_replace('/('.$bads.')/i', "", $html);
    }


  /**
   * ���������� ���������� ������� ��������� ���������������� ��� �����.
   *
   * @param integer $num �����
   * @param string  $v1 ������ ������� (1)
   * @param string  $v2 ������ ������� (2-5)
   * @param string  $v3 ������ ������� (0, 5-20)
   * @return string �������, ��������������� �����.
   */
    function ending ($num, $v1, $v2, $v3)
    {
        $num = intval($num);
        /*$e   = $num % 10;

        if ((($num == 0) || (($num > 5) && ($num < 20))) || (($e == 0) || ($e > 4))) {
            $result = $v3;
        }
        elseif ($e == 1) {
            $result = $v1;
        }
        else {
            $result = $v2;
        }
        return $result;*/
        
        $val = $num % 100;
        if ($val > 10 && $val < 20) {
            return $v3;
        } else {
            $val = $num % 10;
            if ($val == 1) return $v1;
            elseif ($val > 1 && $val < 5) return $v2;
            else return $v3;
        }
    }

	/**
	 * ���������, ���� �� ����� � ����� ������ ������ ��� � �������.
	 *
	 * @param string $html	����� ��� ��������
	 * @return boolean
	 */
  function is_empty_html($html)   // 
  {
    // ����, ����� ������� ������ ����...
    return ( preg_replace('/(\s+|\xa0|<[^>]*(>|$)|&(nbsp|ensp|emsp|#8195|#8194|#32);?)/', '', $html) === '' );
  }

	/**
	 * ��� ������ change_q()
	 * 
	 * @see change_q()
	 *
	 * @param string  $input			�����
	 * @param boolean $strip_all		���� ������, �� ��� ����. ������� ������������� � ��������,
                       					����� ������� �������� ���� ����� � ����, ������� �� ������ � (b|br|i|p|ul|li|cut),
                       					���������� &lt;���&gt;, � ��� ������� � ���������� ����������� � ��������.
	 * @param boolean $strip_tags		���� ($strip_tags && $strip_all), �� ��� ���� ������ ���������, � ������� � ���������� ����������� � ��������.
                       					���� !$strip_all, �� �������� �� ����� (�� ����������� ������).
	 * @param string  $safe_tags		������ �����, ������� ����� ��������. ����� �������� ������ ���� !$strip_all.
	 * @param boolean $a_tag			������� ��� ��� ������, ������� ���� � ������
	 * @param boolean $a_tag			�������� ��� ��� ������� �� ������ (trim)
     * @param boolean $is_addslashes    ��������� ����� ���� �������� magic_quotes ��� �� ���������
     * @param int $max_len              ������������ ����� �������� ������. �������� ��������� ������ ��� close_tags()
	 * @return string
	 */
  function change_q_x(                
   $input,
   $strip_all = TRUE, 
   $strip_tags = TRUE, 
   $safe_tags='b|br|i|p|ul|li|cut|s|h[1-6]{1}', 
   $a_tag=FALSE,
   $trim=false,
   $add_slashes=true,
   $max_len = null
  )
  {
    setlocale(LC_ALL, 'ru_RU.CP1251');
    $input = str_replace(array('&#60;', '&#62;', '&#x3C;', '&#x3E;'), array('&lt;', '&gt;', '&lt;', '&gt;'), $input);
    // �������� NULL �����
    $input = preg_replace('~\\\0~', '', $input);

    if($strip_all) {
      if($strip_tags)
        $input = preg_replace('/<[^>]*(>|$)/', '', $input); // ������ ������� ���� ���.
      //$input = htmlspecialchars($input, ENT_QUOTES, 'cp1251'); // �������� ������� � ���������, ����������� �� � ��������.
      $input = str_replace(array('<', '>', '"', '\''), array('&lt;', '&gt;', '&quot;', '&#039;'), $input);
    }
    else
    {
        //close_tags($input, 's,i,b,h1,h2,h3,h4,h5,h6', $max_len);
      $safe_tags = is_null($safe_tags) ? 'b|br|i|p|ul|li|cut|s|h[1-6]{1}' : $safe_tags;
      // ���������� ������� ���������� -- ������, ������� �� ������ ����������� � �������� ������.
      $dS = '@;;,,@;;@;__-=-=@~~~~'.mt_rand(8, 10000);
      $input = str_replace(array("<br />", "<br>",), array("\n", "\n"), $input);
      
      // ��������� ������������ � ����������
      $input = preg_replace('#<p[^>]*?align=\\\"(center|left|right)\\\"#', '<p$1 ', $input);
      $safe_tags .= '|pcenter|pleft|pright';
      
      $input = preg_replace("/<($safe_tags)\s[^>]*?>/mix", "<$1>", $input); // ������ ���� ���� <strong style='awesome'> �� <>
      
      // ������� ����������� �������� �����
      $badAttrs = "onmousemove|onerror|onclick|onload|onunload|onabort|onblur|onchange|onfocus|onreset|onsubmit|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmouseup|onmouseover|onmouseout|onselect|javascript";
      $inputNew = "";
      while ($input !== $inputOld) {
        $inputOld = $input;  
        $input = preg_replace("/<(.+?)((?:$badAttrs)=[^\s>]+)([^>]*?)>/mix", "<$1$3>", $input);
      }

      $input = preg_replace('/'.$dS.'/', '', $input);      // ������� ��, ���� ���-���� ����������� (���� ����������� ���, �� ��� ��)
      $input = preg_replace('/(<|>)/', $dS.'$1', $input);  // �������� ��� '<' � '>' �� $dS ���� ������� '<' ��� '>' ��������������.
      $input = preg_replace("/$dS<(\/?($safe_tags))$dS>/i", '<$1>', $input); // ��������� ������ ���������� ����.
      $input = preg_replace('/'.$dS.'</', '&lt;', $input); // ������ �������� ���������� $dS � ������������ � ��������� �� &lt; ��� &gt;
      $input = preg_replace('/'.$dS.'>/', '&gt;', $input);
      $input = preg_replace('/(\r?\n)/', "\n", $input);
      //$input = nl2br($input);
      // �� �������� �����. $input = preg_replace('#(<br //>\s*){3,}#i', '<br /><br />', $input); // �������� ��� BR-����.
      
      /*if(strstr($safe_tags, 'img') && !preg_match('/<img.*?>/', $input)
          && !preg_match('/<p.*?>/', $input) ) */
      
      $input = preg_replace('/\"/', '&quot;', $input);               // ��� ������� ��������� � ��������.
      $input = preg_replace('/\'/', '&#039;', $input);
      
      // ��������������� ������������
      $input = str_replace(array('<pcenter', '<pleft', '<pright'), array('<p align="center"', '<p align="left"', '<p align="right"'), $input);
      
      $input = str_replace(array("<cut>", "</cut>"), array ("<!-- -W-EDITOR-CUT- -->", "<!-- -W-EDITOR-CUT-END -->"), $input);
      
      close_tags($input, 's,i,b,h1,h2,h3,h4,h5,h6', $max_len);
      
      $input = str_replace(array ("<!-- -W-EDITOR-CUT- -->", "<!-- -W-EDITOR-CUT-END -->"), array("<cut>", "</cut>"), $input);
    }

    if(!get_magic_quotes_gpc() && $add_slashes && !defined('NEO'))
      $input = addslashes( (string) $input);


    if ($trim)
	  $input = trim($input);
    
	setlocale(LC_ALL, 'en_US.UTF-8');
    //setlocale(LC_ALL, '');

    return $input;
  }

	/**
	 * �������� ��� ������� change_q_x()
	 * 
	 * @see change_q_x();
	 * @ignore
	 */
  function change_q_x_a($input, $strip_all = TRUE, $strip_tags = TRUE, $safe_tags='b|br|i|p|ul|li|cut|s|h[1-6]{1}', $max_len=null)
  {
    return change_q_x($input, $strip_all, $strip_tags, $safe_tags, TRUE, false, true, $max_len);
  }


    /**
     * ��������� ���������� ���� � ������.
     *
     * @param string $input   �����.
     * @param string|array $tags   ������ ��� ������ (����������� ��������) �����.
     * @param boolean $max_len ������������ ����� ������ ����� �������� �����
     * ���� ���� ������ ������� �� �������, �� ���������� ��� �� �����������, � ���������
     */  
    function close_tags(&$input, $tags, $max_len = null) {
        if(!is_array($tags)) $tags = explode(',', $tags);
        if ($max_len) {
            $input = substr($input, 0, $max_len);
        }
        foreach($tags as $t) {
            $ot="<{$t}>";
            $ct="</{$t}>";
            // ��������� ���� ����� �� ������ �� �����, ������ �� ��������
            $pos = 0; // ������� � ����������� ������
            $tags_opened = 0; // ������� ����� �������
            $ot_len = strlen($ot); // ���������� �������� � ����������� ����
            $ct_len = strlen($ct); // ���������� �������� � ����������� ����
            while ($sub = substr($input, $pos, 1)) {
                if ($sub !== '<') {
                    $pos++;
                    continue;
                }
                if ($ot === substr($input, $pos, $ot_len)) { // ���� ���������� ����������� ���
                    $tags_opened++;
                    $pos = $pos + $ot_len;
                    continue;
                } elseif ($ct === substr($input, $pos, $ct_len)) { // ���� ���������� ����������� ���
                    $tags_opened--;
                    if ($tags_opened < 0) { // ���� ����������� ����� ������ ��� �����������
                        $input = substr_replace($input, '', $pos, $ct_len); // ������� �������� ����������� ���
                        $tags_opened++;
                    } else {
                        $pos = $pos + $ct_len;
                    }
                    continue;
                }
                $pos++;
            }
            $input .= str_repeat($ct, $tags_opened); // ��������� � ����� ������ ����������� ����
        }
        $input = str_replace(PHP_EOL, '<br>', $input);
        $input = iconv('CP1251', 'UTF-8', $input);
        $config = array(
            'show-body-only'    => true,
            'wrap'              => 0,
            'break-before-br'   => false,
            'drop-empty-paras'  => false,
            'preserve-entities' => true,
            'bare'              => true,
        );
        //#0023246 - �������� <ul>����� ��� <li> </ul>
        $pattern = "#<ul>([^>]*)</ul>#si";
        $input = preg_replace($pattern, '$1'."\n", $input);
        $input = str_replace(' ', '@@##@@##@@', $input); // �������� ������� ����� ����������� ����� ��������� ����� tidy
        
        $Tidy = new tidy();
        $Tidy->parseString($input, $config, 'utf8');
        $Tidy->cleanRepair();
        $input = $Tidy->value;
        $input = str_replace('@@##@@##@@', ' ', $input);
        $input = iconv('UTF-8', 'CP1251', $input);
        $input = str_replace(PHP_EOL, '', $input); // ������ �������� �� Tidy
        $input = str_replace("<br>", "\r", $input);
        // ������� ���������� ���� <li> ����������� tidy
        $input = preg_replace('#' . PHP_EOL . '*<li style="list-style: none">[\s\S]*?<\/li>' . PHP_EOL . '*#', '', $input);
        // � ����� <ul> � <ol> �� ������ ���� ������ ��� ����� <li>
        $input = preg_replace('#(<(?:ul|ol)>)[^<]*?(<li>)#', '$1$2', $input);
        $input = preg_replace('#(<\/li>)[^<]*?(<li>)#', '$1$2', $input);
        $input = preg_replace('#(<\/li>)[^<]*?(<\/(?:ul|ol)>)#', '$1$2', $input);
        $input = preg_replace('#' . PHP_EOL . '\s*<\/li>#', '</li>', $input);
        // ���� ���� ����������� �� ����� ������
        /*if ($max_len) {
            $tagsPatt = implode('|', $tags);
            // ���� ��������� ������� ������
            while (($overChars = strlen($input) - $max_len) > 0) {
                // ������� ����� � ����� �� ������ �����
                $patt = '~([\s\S]*?)[^<>]{1,'.$overChars.'}((?:</?(?:'.$tagsPatt.')>)*)$~i';
                $input = preg_replace($patt, '$1$2', $input);
            }
        }*/        
    }
    
    /**
     * ���������� ��������� ������� �������
     * 
     * @param string $text
     * @return string 
     */
    function close_tags2($text) { 
        $text = str_replace("<br>", "<br/>", $text);
        $patt_open    = "%((?<!</)(?<=<)[\s]*[^/!>\s]+(?=>|[\s]+[^>]*[^/]>)(?!/>))%"; 
        $patt_close    = "%((?<=</)([^>]+)(?=>))%"; 
        if (preg_match_all($patt_open,$text,$matches)) { 
            $m_open = $matches[1]; 
            if(!empty($m_open))  { 
                preg_match_all($patt_close,$text,$matches2); 
                $m_close = $matches2[1]; 
                if (count($m_open) > count($m_close)) { 
                    $m_open = array_reverse($m_open); 
                    foreach ($m_close as $tag) $c_tags[$tag]++; 
                    foreach ($m_open as $k => $tag)    if ($c_tags[$tag]--<=0) $text.='</'.$tag.'>'; 
                } 
            } 
        } 
        return $text; 
    }
    
    /**
     * ������ HTML ��� ���� � ��� ������������ ��� <cut>, 
     * ��� ���� ����� ��� ������ ���� �������, ���� �� ������ ������� 
     * 
     */
    function clearHTMLBeforeCutTags($html) {
        $e = explode("<cut>", $html);
        if(count($e) > 0) {
            foreach($e as $k=>$v) { 
                if($k>0) {
                    $res[1] .= str_replace("<cut>", "", $e[$k]); 
                    continue;
                }
                $res[0] .= close_tags2($v); // ������� ����
            }
            $result = implode("<cut>", $res);
            return $result;
        } else {
            return $html;
        }
    }
  
  /**
   * �������� ��������, ��������� ����� �� ���� � �������� ��� �� ����� ��������������� � �����
   *
   * @param string $str ����� ��� ��������
   * @return string
   */
  function antispam($str)
  {
    setlocale(LC_ALL, 'ru_RU.CP1251');
    $str = preg_replace_callback('/&#(3[3-9]|[4-9][0-9]|1[01][0-9]|12[0-6])(;|\D)/', '_antispamEDec2Ch', $str);
    $str = preg_replace_callback('/&#x(2[1-9A-F]|[3-6][0-9A-F]|7[0-9A-E])(;|\D)/i', '_antispamEHex2Ch', $str);
    $b = '(?:[\s.=~,*_$;%\'\]\[}{)(+|\\\\\/:`-]|\xa0|<[^>]*>|&(?:nbsp|ensp|emsp|sum|minus|bull|(?:n|m)dash|oline|shy|middot|tilde|sim|sdot|#[0-9]{2,4});?){0,2}';
    // ��� ���������� ���������
    //$b = '(?:\S?|(?:[\s.=~,*_$;%\'\]\[}{)(+|\\\\\/:`-]|\xa0|<[^>]*>|&(?:nbsp|ensp|emsp|sum|minus|bull|(?:n|m)dash|oline|shy|middot|tilde|sim|sdot|#[0-9]{2,4});?){0,2})';
    $tre = array(
      't'=>'[tT��]','b'=>'[bB��]','a'=>'[aA��]','c'=>'[cC��]','e'=>'[eE��]',
      'o'=>'[oO��]','p'=>'[pP��]','h'=>'[hH�]','k'=>'[kK��]','m'=>'[mM��]', 'y'=>'[yY��]'
    );
    $spams = array('elkabux.narod.ru','bestlance','toplance','shopprojects','rulance',
                   'unconferencewordpresscom','2009.kiev.ua', 'designsocial', 'profzone',
                   'ebukva', 'talkonet', 'ellance', 'freelancedays',
                   'twago', 'bejali', 'revolance', 'freelancechat', 'singlework',
                   'onbine.tw1.ru', 'unfoship.webtm.ru', 'part-ner.net',
                   'golance', 'my-free-lance','free-lances.3dn.ru', 'f-lans.ru', 'postprofit.net', 'photo-stocks.biz');
    foreach($spams as $s) {
      $re = '/(http:\/\/)?(w\s*w\s*w\s*\.\s*)?';
      $len = strlen($s);
      for($i=0;$i<$len;$i++) {
        $ptt = $tre[$s[$i]];
        if(!$ptt)
          $ptt = $s[$i];
        $re .= $ptt.($i==$len-1?'':$b);
      }
      $re .= '(\s*\.\s*(r\s*u|c\s*o\s*m|d\s*e))?/is';
      $str = preg_replace($re,'[��� ����]',$str); // ��� �������� � ������� �����
    }
    $str = preg_replace('/����\s*-?\s*�����/i','[��� ����]',$str);
    setlocale(LC_ALL, 'en_US.UTF-8');
    return $str;
  }
  
    /**
   * callback ��� ��������� ��������� (������ �� �� �������).
   * @see antispam()
   */
  function _antispamEDec2Ch($m) {
      return chr($m[1]).($m[2]==';'?'':$m[2]);
  }
  
  /**
   * callback ��� ��������� ��������� (������ �� �� �������).
   * @see antispam()
   */
  function _antispamEHex2Ch($m) {
      return chr(hexdec('0x'.$m[1])).($m[2]==';'?'':$m[2]);
  }
  
  /**
   * ��� ��������� �������� ������ � ������� XAJAX ���������� ��������� ����� ���������� ������� ���� � ������� �� ��� GET � POST ������������� ������� ���������.
   * 
   */
  function __paramValue($type, $value, $maxlen = null, $strip_tags = false) {
      return __paramInit($type, null, null, null, $maxlen, $strip_tags, $value);
  }
	/**
	 * ���������� ������������� ���������� ���������� GET � POST ���������� �������������
	 *
	 * @param string  $type		��� ���������� ������ (int, float, string, link, html, bool, money)
	 * @param string  $get_name	�������� ������ � GET 
	 * @param ustring $post_name	�������� ������ � POST 
	 * @param mixed   $empty_val	�������� �� ���������
	 * @param integer $maxlen		������������ ������ ������
	 * @param string $strip_tags		��� $type = 'html'. ���� TRUE, �� ��� ���� � �����������, ���� � ����� ������ ��
     * @param string $value  ��� ��������� �������� ������ � ������� XAJAX ���������� ��������� ����� ����������
  *                            ����������� � ��������.
  *                            @see change_q_x()
	 * @return mixed
	 */
  function __paramInit($type='int', $get_name=NULL, $post_name=NULL, $empty_val=NULL, $maxlen=NULL, $strip_tags = FALSE, $value = FALSE) {
    //$value = FALSE;

    if($get_name && isset($_GET[$get_name])) {
      if($_GET[$get_name]!=='')
        $value = $_GET[$get_name];
    }
    
    if($value===FALSE) {
      if($post_name && isset($_POST[$post_name])) {
        if($_POST[$post_name]!=='')
          $value = $_POST[$post_name];
      }
    }

    if($value===FALSE)
      return $empty_val;

    if (intval($maxlen)) {
      $value = addslashes(substr(stripslashes($value), 0, intval($maxlen)));
    }

    switch($type) {
      case 'striptrim': return stripslashes(trim($value));  
      case 'page': $value = intvalPgSql($value); return ($value <= 0)?1:$value;
      case 'uinteger':
      case 'uint': return abs(intvalPgSql($value));
      case 'array_integer':
      case 'array_int': return array_map('intvalPgSql', $value);
      case 'integer':
      case 'int'    : return intvalPgSql($value);
      case 'String' : //$value = stripslashes($value);
      case 'string' : return change_q_x($value, TRUE);
      case 'string_no_slashes' : return stripslashes(change_q_x($value, TRUE));
      case 'link'   : return change_q_x(strip_http($value), TRUE);
      case 'html'   : return $strip_tags ? change_q_x($value, FALSE, TRUE, "", false, false)
                                         : change_q_x($value, FALSE, TRUE, null, false, false) ;
      case 'htmltext': return change_q_x($value, TRUE, FALSE) ;
      // ���������� 'htmltext', �� ��������� ���� ul, li, b, p, i
      case 'html_save_ul_li_b_p_i':
          return change_q_x($value, false, false, 'b|i|p|ul|li');
      case 'array'  : return $value;
      case 'bool'   : return !! $value;
      case 'float'  : setlocale(LC_ALL, 'en_US.UTF-8'); return floatval($value);
      case 'money'  : setlocale(LC_ALL, 'en_US.UTF-8'); return floatval(preg_replace('/\s+/','',str_replace(",", ".", $value)));
      
        case 'ckedit_nocut':
            $nocut = true;
        case 'ckeditor':
        case 'ckedit':
            //$value = stripslashes($value);
            $value = wysiwyg_video_replace($value, $wysiwyg_videos);
            if(hasPermissions('adm')) $value = wysiwyg_image_replace($value, $wysiwyg_images);
            $value = wysiwyg_code_replace($value, $wysiwyg_codes);
            $ph    = md5(time())."_code";
            
            list($value, $url) = str_replace_mask('#((<a[^>]*>)(.*?)(<\/a>))#mix', $value);
            foreach ($url as &$u) {
                $u = stripslashes($u);
                $u = cleanHref($u);
            }
            
            $value = preg_replace(array("~<cut\s*?\/>~mix", "~<\/cut>~"), array("<cut>", ""), $value); // �������� ����
            if($nocut) { // ������� ����
                $value = str_replace("<cut>", "", $value);
            }
            $value = str_replace(array("<br />", "<br/>", "<br>"), "___BR___", $value); // �������� �������� ��� ���������� ��������� ������� �� �������
            // ������������ ������� � ���� <pre>
            $value = str_replace("\n", "__N__", $value );
            $value = preg_replace_callback('~<pre>(.*?)<\/pre>~mix', 'rn2br', $value);
            $value = str_replace("__N__", "\n", $value );
            
            $value = strip_tags($value, '<a>,<strike>,<cut>,<pre>,<b>,<strong>,<em>,<u>,<i>,<p>,<ul>,<ol>,<li>,<s>,<h1>,<h2>,<h3>,<h4>,<h5>,<h6>');
            
            $value = change_q_x($value, FALSE, TRUE, 'strike|cut|pre|b|strong|em|u|i|p(\s'.$ph.'_\w*)?+|ul|ol|li|s|h[1-6]{1}', false, false);
            $value = str_replace("___BR___", "<br />", $value); // ���������� ��������
            $value = clearHTMLBeforeCutTags($value);
            $value = str_replace("<cut>", "<!-- -W-EDITOR-CUT- -->", $value);
            
            $value = hlcode($value);
            
            $tidy = new tidy();
            $value = $tidy->repairString(
                    $value, array(
                'fix-backslash' => false,
                'show-body-only' => true,
                'bare' => true,
                'clean' => false,
                'drop-empty-paras' => false,
                'preserve-entities' => true,
                'wrap' => '0'), 'raw');
            
            $value = str_unreplace_mask($url, $value);
            
            $value = wysiwygLinkEncode( $value );
            $value = wysiwygLinkDecode( $value );
            
            $value = str_replace("<!-- -W-EDITOR-CUT- -->", "<cut>", $value);
            $value = str_replace("<p></p>", "<p>&nbsp;</p>", $value);
            $value = str_replace("\n", "", $value);
            
            /*
             * \h - �������������� ���������� ������. ��� ��������� ���������� --PHP >= 5.2.4, PCRE >= 7.2 (�� ���� ������ PCRE 6.6)
             */
            //$value = preg_replace("/[\p{Zs}]/", " ", $value);
            
            $value = wysiwyg_code_restore($value, $wysiwyg_codes);
            if(hasPermissions('adm')) $value = wysiwyg_image_restore($value, $wysiwyg_images);
            $value = wysiwyg_video_restore($value, $wysiwyg_videos);
            return $value;
            break;
      
      case 'wysiwyg':
      case 'wysiwyg_tidy':
      case 'wysiwyg_message':
      	$value = wysiwyg_video_replace($value, $wysiwyg_videos);
      	$value = wysiwyg_image_replace($value, $wysiwyg_images);
      	$value = wysiwyg_code_replace($value, $wysiwyg_codes);
        $value = str_replace(array("\n", "\r"), "", $value);
        $value = preg_replace("[\r\n]", "", $value);
        $ph = md5(time())."_code";
        list($value, $url) = str_replace_mask('#((<a[^>]*>)(.*?)(<\/a>))#mix', $value);
        foreach ($url as &$u) {
            $u = cleanHref($u);
        }
        //$value = preg_replace('/<p\sclass.*?code\s(\w*?).?"/si', "<p {$ph}_$1", $value);        
        $value = change_q_x($value, FALSE, TRUE, 'a|strike|cut|b|strong|em|u|i|p(\s'.$ph.'_\w*)?+|ul|ol|li|s|h[1-6]{1}', false, false);
        $value = str_unreplace_mask($url, $value);
        $value = preg_replace("#<([^><]+?)([^a-z_\-]on\w*|xmlns)(\s*=\s*[^><]*)([><]*)#i", "<\\1\\4", $value);
        $value = preg_replace("/<br ?\/?>/si", "\n", $value);
        $value = str_replace(array("<br />", "<br />", "&nbsp;"), array("\n", "\n", " "), $value);
        $value = clearHTMLBeforeCutTags($value);
        $value = wysiwygLinkEncode( $value );
        $value = wysiwygLinkDecode( $value );
        $value = preg_replace('/\&amp;/', '&',  $value);
        //$value = preg_replace('/<p\s[a-z0-9]{32}_code_(.*?)>/', '<p class="code $1">', $value);
        $value = preg_replace("/(li|ol|ul)>[\n]+/iU", "$1>", $value);
        //$value = str_replace(array('  '), array('&nbsp;&nbsp;'), $value );
        $value = str_replace("<cut>", "<!-- -W-EDITOR-CUT- -->", $value);
        $value = str_replace("&lt;!-- -W-EDITOR-CUT- --&gt;", "<!-- -W-EDITOR-CUT- -->", $value);
//        $value = preg_replace_callback("/<([^\s>]+)[^>](.*?)*>/si",
//                create_function('$matches', 'return str_replace("&nbsp;", " ", $matches[0]);'),
//            $value);
        if ($type == 'wysiwyg_message') {
            $value = preg_replace(array("/<p>/", "/<\/p>/", "/\n+\s*$/"), array("", "\n\n", ""), $value);
            $value = str_replace("\n", "<br />", $value);
        } elseif ($type != 'wysiwyg_tidy') {
            $value = nl2br($value);
        }
        if ($type == 'wysiwyg_tidy') {
            //tidy
            $tidy = new tidy();
            $value = $tidy->repairString(
                $value, array(
                'fix-backslash' => false,
                'show-body-only' => true,
                'bare' => true,
                'drop-empty-paras' => false,
                'preserve-entities' => true,
                'wrap' => '0'), 'raw');
            $value = str_replace("<p></p>", "<p>&nbsp;</p>", $value);
            $value = str_replace("\n", "", $value);
            $value = preg_replace("/\p{Zs}/", " ", $value);
            //!tidy
        }
        $value = wysiwyg_code_restore($value, $wysiwyg_codes);
        $value = wysiwyg_image_restore($value, $wysiwyg_images);
        $value = wysiwyg_video_restore($value, $wysiwyg_videos);
        return $value;

    }

    return NULL;
  }
  
  function rn2br($text) {
      if(preg_match('~{code_.*?}~', $text[1])) {
          return  str_replace(array("__R__", "__N__"), "___BR___", $text[1]);
      }
      return "<pre>" . str_replace(array("__R__", "__N__"), "___BR___", $text[1]) ."</pre>";
  }
  
  /**
   * ��������� href � ���� A �� ����������
   * @param strint $url - ��� A ������� ���� ���������
   * ���� ������ �� ��������, �� ������������ ��� A ��� ���������, �� � ������� (<a>�����</a>)
   */
  function cleanHref ($url) {
      //$match = preg_match("~href=.?([\"\'])(.*?)\\1~", $url, $matches);
      $match = preg_match("~href=(.*?)[\s|>]~", $url, $matches);
      if (!$match) {
          return preg_replace('~<a[^>]*>([^<]*)</a>~', '$1', $url);
      }
      $hrefs = array_slice($matches, 1);
      foreach ($hrefs as $href) {
          $href = trim($href, "'");
          $href = trim($href, '"');

          // ���� ������ �� �������� �� ��������� ��� ��� ������
          if (!url_validate($href, true)) {
              return preg_replace('~<a[^>]*>([^<]*)</a>~', '$1', $url);
          } else {
              $url = preg_replace("~<a[^>]*>~", '<a href="'.$href.'">', $url);
          }
      }
      return $url;
  }
  
   /**
   * �������� ���� ����������� � ������ WYSYWIG ��������� �� ��������� ������� 
   * � ������������ ����� �������� ��� ��������� ����� width, heigth, style, src
   * � �������� �� � $images
   * @param  $text   �������� �����
   * @param  &$images - ������ � ������� ����� ������������ ������������ ���� img  
   * @param  bool $checkWysywygClass - ���� true �� �������������� ������ ����������� � class="wysiwyg_image"   
   * @return string $text
   * */
  function wysiwyg_image_replace($text, &$images, $checkWysywygClass = false) {
      $p = "#<img\s+[^>]+>#si";
      if ($checkWysywygClass) {
          $p = "#<img\s.*class=\"wysywyg_image\"\s+[^>]+>#si";
      }
      $f = preg_match_all($p, $text, $images);
      $text = preg_replace($p, "WYSIWYG_IMAGE", $text);
      if ($f) {
        $images = $images[0];
        $p = "#\s+on[\w=]*\"[^\"]*\"#si";
        foreach ($images as $k=>$img) {
            $img = preg_replace($p, "", $img);
            $images[$k] = str_replace("\\\"", "\"", $img);
            if (strpos($images[$k], 'class="wysywyg_image"') === false) {
                $images[$k] = str_replace("<img", '<img class="wysywyg_image"', $images[$k]);
            }
        }
        $p = "/on.*?=[\"|'].*?[\"|']\s/";
        foreach ($images as $k=>$img) {
            $images[$k] = preg_replace($p, "", $img);
        }
//        $p = "#\s+on[\w=]*\([^\)]*\)#si";
//        foreach ($images as $k=>$img) {
//            $images[$k] = preg_replace($p, "", $img);
//        };
        /*echo "<pre>";
        print_r($images);
        echo "</pre>";
        die(__FILE__.__LINE__); /**/
    }      
      return $text;
  }
  
  /**
   * ��������������� ���� ����������� � ������ WYSYWIG ��������� �� ������� $images  
   * @see wysiwyg_image_replace()
   * @param  $text   �����
   * @param  $images - ������ ����� img  
   * @return string $text
   * */
  function wysiwyg_image_restore($text, $images) {
      $arr = explode("WYSIWYG_IMAGE", $text);
      $result = "";
      $j = 0;
      for ($i = 0; $i < count($arr); $i++) {
        $result .= $arr[$i];
        if ($images[$j]) {
            $result .= $images[$j];
            $j++;
        }
    }
    return $result; 
  }
  
  
    /**
    * �������� ���� ����� � ������ WYSYWIG ��������� �� ��������� ������� 
    * � �������� �� � $video
    * @param  $text   �������� �����
    * @param  &$video - ������ � ������� ����� ������������ ������������ ���� �����
    * @param  $forReformat - ���� true �� ������ img ����� ����������
    * @return string $text
    * */
   function wysiwyg_video_replace($text, &$video, $forReformat = false) {
        $patt = "#<img[^>]*class=[^>]+wysiwyg_video[^>]+>#si";
        $find = preg_match_all($patt, $text, $videos);
        $text = preg_replace($patt, "WYSIWYG_VIDEO", $text);
        $video = array();
        if ($find) {
            $videos = $videos[0];
            $pattURL = "#(?:video_url=[\\\\]?\")(.*?)(?:[\\\\]?\")#si";
            foreach ($videos as $key => $vid) {
                preg_match($pattURL, $vid, $videoURLs);
                $videoURL = $videoURLs[1];
                if (!$forReformat) {
                    if (video_validate($videoURL)) {
                        $video[] = '<img class="wysiwyg_video" src="/images/video.png" video_url="' . $videoURL . '">';
                    } else {
                        $video[] = '';
                    }
                } else {
                    $video[] = show_video('wysiwyg_video' . $key, $videoURL);
                }
            }
        }      
        return $text;
    }
  
  /**
   * ��������������� ���� ����������� � ������ WYSYWIG ��������� �� ������� $images  
   * @see wysiwyg_image_replace()
   * @param  $text   �����
   * @param  $images - ������ ����� img  
   * @return string $text
   * */
    function wysiwyg_video_restore($text, $video) {
      $arr = explode("WYSIWYG_VIDEO", $text);
      $result = "";
      $j = 0;
      for ($i = 0; $i < count($arr); $i++) {
        $result .= $arr[$i];
        if ($video[$j]) {
            $result .= $video[$j];
            $j++;
        }
    }
    return $result;
  }
  
    /**
    * �������� ���� <p class="code... � ������ WYSYWIG ��������� �� ��������� ������� 
    * � �������� �� � $code
    * @param  $text   �������� �����
    * @param  &$code - ������ � ������� ����� ������������ ������������ ���� �����
    * @return string $text
    * */
    function wysiwyg_code_replace($text, &$code) {
        // ��������� ��� ������ ������ � �����
        $patt = '#<p\sclass.*?code\s(?:\w*?).?"[^>]*>.*?<\/p>#si';
        // ��������� ��� ����� � ����� � $codes
        $find = preg_match_all($patt, $text, $codes);
        // ������� �� �� ������
        $text = preg_replace($patt, "WYSIWYG_CODE", $text);
        //$patt = "#<img[^>]*class=[^>]+wysiwyg_video[^>]+>#si";
        //$find = preg_match_all($patt, $text, $videos);
        $code = array();
        if ($find) {
            $codes = $codes[0];
            $pattCode = '#^(<p\sclass.*?code\s(?:\w*?).?"[^>]*>)(.*)(<\/p>)$#si';
            foreach ($codes as $key => $cod) {
                preg_match("#class=['|\"](.*?)['|\"]#mix", stripslashes($cod), $class);
                $cod = preg_replace("#<p.*?>#mix", '<p class="' . $class[1] . '">', $cod); // ������� ��� ������ �� ����

                preg_match($pattCode, $cod, $match);
                $rows = explode('<br />', $match[2]);
                foreach($rows as $value) {
                    $value = htmlspecialchars($value);
                }
                $match[2] = implode('<br />', $rows);
                $code[] = $match[1] . $match[2] . $match[3];
            }
        }      
        return $text;
    }
  
  /**
   * ��������������� ���� ���� � ������ WYSYWIG ��������� �� ������� $code  
   * @see wysiwyg_code_replace()
   * @param  $text   �����
   * @param  $images - ������ ����� img  
   * @return string $text
   * */
  function wysiwyg_code_restore($text, $codes) {
      $arr = explode("WYSIWYG_CODE", $text);
      $result = "";
      $j = 0;
      for ($i = 0; $i < count($arr); $i++) {
        $result .= $arr[$i];
        if ($codes[$j]) {
            $result .= $codes[$j];
            $j++;
        }
    }
    return $result;
  }
  
    /**
     * �������� ������ <a> � ������ ��������� �� WYSIWYG ���������.
     * 
     * ������� ��� ��������� ������ ����� href � ������ - ������ �� ����� ������ ���� ���� ����� ���������
     * �������� ������ �� ������� ������������������, ������� �� ������ ������ ��������� ������.
     * 
     * @param  string $sIn �������� �����
     * @return string
     */
    function wysiwygLinkEncode( $sIn = '' ) {
        $sPattern = '#(?:<noindex>)?<a([^>]*)?href=(?:\\\)?"([^"]+)(?:\\\)?"([^>]*)?>(.+)</a>(?:</noindex>)?#iU';
        return preg_replace_callback( $sPattern, '_wysiwygLinkEncodeCallback', $sIn );
    }
  
    /**
     * ��������������� callback-������� ��� ������������ ������ <a> � ������ ��������� �� WYSIWYG ���������.
     * 
     * @param  array $matches ������ ����������� ������ preg_replace_callback
     * @return string ������ ��� �����������
     */
    function _wysiwygLinkEncodeCallback( $matches ) {
        $sUrl = implode("\x1D ", str_split(urlencode(rtrim($matches[2], '\\'))));
        $sTxt = implode("\x1D ", str_split($matches[4]));
        return "\x07"."\x02".$sUrl."\x03\x02".$sTxt."\x03\x07";
    }
    
    /**
     * ���������� ������ <a> � ������ ��������� �� WYSIWYG ���������.
     * 
     * �������� wysiwygLinkEncode
     * �������� ������� ������������������ �� ������, ����������� ������ ��� ���������, 
     * ����������� � noindex �, ���� �����, ������ ������� �� a.php
     * 
     * @param  string $sIn �������� �����
     * @return string
     */
    function wysiwygLinkDecode( $sIn = '' ) {
        $sPattern = '#\x07\x02([^\x03]*)\x03\x02([^\x03]+)\x03\x07#i';
        return preg_replace_callback( $sPattern, '_wysiwygLinkDecodeCallback', $sIn );
    }
    
    /**
     * ��������������� callback-������� ��� �������������� ������ <a> � ������ ��������� �� WYSIWYG ���������.
     * 
     * @param  array $matches ������ ����������� ������ preg_replace_callback
     * @return string ������ ��� �����������
     */
    function _wysiwygLinkDecodeCallback( $matches ) {
        $sUrl  = urldecode( html_entity_decode(str_replace("\x1D ", '', $matches[1])) );
        $sTxt  = str_replace("\x1D ", '', $matches[2]);
        $aUrl = parse_url($sUrl); 
        $aServerUrl = parse_url($GLOBALS['host']);
        //$max_link_len = 30;
        $sTxt = str_replace(array("http://", "https://"), "", $sTxt);
        /* ����������������, ��� ��� ��� ��������� �������� �������� ������� reformat, ������� ��������� ������� ������
        if(strlen($sTxt) > $max_link_len) {
            $txt = $sTxt;
            $sTxt = substr_quasi($sTxt, 0, $max_link_len, $qlen, $tlen);
            if($qlen >= $max_link_len && strlen($txt) > $tlen) {
                $sTxt .= '...';
            }
        }
        */
        $sOut = '<noindex><a class="b-post__link b-post__link_ellipsis b-post__link_width_200" href="'.htmlspecialchars_decode($sUrl).'" target="_blank" rel="nofollow" title="'.$sUrl.'">'.$sTxt.'</a></noindex>';
        /* ���������������� ����� � ���� ����������� ����� ������, � �� ������ �� a.php?href=...
        if ( $GLOBALS['disable_link_processing'] || in_array($aUrl['host'], $GLOBALS['white_list']) || $aUrl['host'] == $aServerUrl['host'] ) {
            // ���� � ����� ������ - ���� ������ ������
            $sOut = '<noindex><a class="blue" href="'.htmlspecialchars_decode($sUrl).'" target="_blank" rel="nofollow" title="'.$sUrl.'">'.$sTxt.'</a></noindex>';
        }
        else {
            // ����-�� �� ������� - ������������ �� a.php
            $hs = $GLOBALS['host'];
            
            if ( !preg_match("#^$hs/a\.php\?href=#", $sUrl) ) {
                $sOut = '<noindex><a class="blue" href="'.$hs.'/a.php?href='.urlencode(htmlspecialchars_decode($sUrl)).'" target="_blank" rel="nofollow" title="'.$sUrl.'">'.$sTxt.'</a></noindex>';
            }
            else {
                $sOut = '<noindex><a class="blue" href="'.htmlspecialchars_decode($sUrl).'" target="_blank" rel="nofollow" title="'.$sUrl.'">'.$sTxt.'</a></noindex>';
            }
        }
        */
        return $sOut;
    }



/**
 * �������� ����������� �� �������� � ���������� (�������� ���, �� ������������ ����)
 *
 * @example 
 * <td>
 *    <?
 *      function pageLinkOut($i, $s_prm)
 *      {
 *        global $om; return "<a href='/commune/?om={$om}&page={$i}{$s_prm}' style='color:#666'>{$i}</a>";
 *      }
 *      $pages = ceil($groupCommCnt / commune::MAX_ON_PAGE);
 *      print(__prntPageLinks($pages, $page, 'pageLinkOut', $s_prm));
 *    ?>
 *  </td>
 * 
 * 
 * @param integer $pages			����� ���������� �������.
 * @param integer $page				����� ������� �������� (1 <= $page <= $pages).
 * @param string  $output_func		callback �� ������ ������� �����������, ����� ������ ����. �������� ����� ������� ��������.
 * @param mixed   $arg				�������������� ��������, ������� ����� ������� � $output_func ��� ������ �� ������
 * @return string HTML-���
 */
  function __prntPageLinks($pages, $page, $output_func, $arg=NULL) {
    ob_start();
  ?>
    <table border="0" cellspacing="1" cellpadding="0" class="pgs">
      <tr>
        <?
          if ($pages > 1)
          {
            $maxpages = $pages;
            $i = 1;
  
            if ($pages > 32){
              $i = floor($page/10)*10 + 1;
              if ($i >= 10 && $page % 10 < 5)
                $i = $i - 5;
              $maxpages = $i + 22 - floor(log($page,10)-1)*4;
              if ($maxpages > $pages)
                $maxpages = $pages;
              if ($maxpages - $i + floor(log($page,10)-1)*4 < 22 && $maxpages - 22 > 0)
                $i = $maxpages - 24 + floor(log($page,10)-1)*3;
            }
            for ($i; $i <= $maxpages; $i++) {
              if ($i != $page) {
                ?><td><?=call_user_func($output_func, $i, $arg)?></td><?
              }
              else {
                ?><td class='box'><?=$i?></td><?
              }
            }
            if ($pages > 25 && $maxpages < $pages-1) {
              ?><td>...</td><td><?=call_user_func($output_func, $pages-1, $arg)?></td><td><?=call_user_func($output_func, $pages, $arg)?></td><?
            }
          }
        ?>
      </tr>
    </table>
  <?
    $str = ob_get_contents();
    ob_end_clean();
    $str = preg_replace("/>\s+</","><",$str);
    $str = preg_replace("/[\r\n]/"," ",$str);
    $str = preg_replace("/\s{2,}/"," ",$str);

    return $str;
  }

/**
 * ������ timestamp �� ������
 * 
 * @param string $string ����� 
 * @return string
 */
function make_timestamp($string)
{
    if(empty($string)) {
        // ����� ������� �����
        $time = time();

    } elseif (preg_match('/^\d{14}$/', $string)) {
        // mysql timestamp ������ YYYYMMDDHHMMSS?
        $time = mktime(substr($string, 8, 2),substr($string, 10, 2),substr($string, 12, 2),
                       substr($string, 4, 2),substr($string, 6, 2),substr($string, 0, 4));
        
    } elseif (is_numeric($string)) {
        // �������� ������
        $time = (int)$string;
        
    } else {
        // ������� strtotime
        $time = strtotime($string);
        if ($time == -1 || $time === false) {
            // strtotime() �� ���������, �� ����� ������� �����:
            $time = time();
        }
    }
    return $time;
}

	/**
	 * ������ ��������� win-1251 � UTF-8
	 *

	 * @param string $s ������
	 * @return string
	 */
	function win2utf($s){
	   for($i=0, $m=strlen($s); $i<$m; $i++)
	   {
	       $c=ord($s[$i]);
	       if ($c<=127) {$t.=chr($c); continue; }
	       if ($c>=192 && $c<=207)    {$t.=chr(208).chr($c-48); continue; }
	       if ($c>=208 && $c<=239) {$t.=chr(208).chr($c-48); continue; }
	       if ($c>=240 && $c<=255) {$t.=chr(209).chr($c-112); continue; }
	       if ($c==184) { $t.=chr(209).chr(209); continue; };
	   if ($c==168) { $t.=chr(208).chr(129);  continue; };
	   }
	   return $t;
	}
	/**
	 * �������� ����������� � ����� �� �����
	 *
	 * @param string $addr ������ ���������� ������������
	 * @param string $cidr ��� ������ ��� ��������
	 * @return boolean
	 */
	function matchCIDR($addr, $cidr) {
	    list($ip, $mask) = explode('/', $cidr);
	    $mask = 0xffffffff << (32 - $mask);
	    return ((ip2long($addr) & $mask) == (ip2long($ip) & $mask));
	}

	/**
	 * ����������(� ����������� �����������) ����� ���������� ����� ���� ������ (����� �������� ������, ���������� ������������, ���������� �������� � ��.)
	 *
	 * @param integer $num				����������
	 * @param string  $mode				��� ������
	 * @param integer $ending_type		���������� ��� ��������� ��������� (���, ����, ��� � ��)
	 * @return string
	 */
  function getSymbolicName($num, $mode, &$ending_type = NULL)
  {
    $s = '';
    if( ($num % 100 >= 11 && $num % 100 <= 14)
        || $num % 10 > 4
        || !($num % 10)
      )
    {
      switch($mode)
      {
        case 'year' : $s = '���'; break;
        case 'month' : $s = '�������'; break;
        case 'day' : $s = '����'; break;
        case 'second' : $s = '������'; break;
        case 'man' : $s = '��'; break; // man, ������� � ���� "�����", "��������".
        case 'human' : $s = ''; break; // human, ������� � ���� "�������".
        case 'messages' : $s = '���������'; break;
		case 'comments' : $s = '������������'; break;
		case 'offers' : $s = '�����������'; break;
        case 'hidden_offers' : $s = '������� �����������'; break;
		case 'candidates' : $s = '����������'; break;
		case 'projects' : $s = '��������'; break;
		case 'blogs' : $s = '������'; break;
        case 'votes' : $s = '�������'; break;
      }
      $ending_type = 5;
    }
    else if($num % 10 == 1) {
      switch($mode)
      {
        case 'year' : $s = '���'; break;
        case 'month' : $s = '�����'; break;
        case 'day' : $s = '����'; break;
        case 'second' : $s = '�������'; break;
        case 'human' : $s = ''; break;
        case 'messages' : $s = '���������'; break;
		case 'comments' : $s = '�����������'; break;
		case 'offers' : $s = '�����������'; break;
        case 'hidden_offers' : $s = '������� �����������'; break;
		case 'candidates' : $s = '��������'; break;
		case 'projects' : $s = '������'; break;
		case 'blogs' : $s = '����'; break;
        case 'votes' : $s = '�����'; break;
      }
      $ending_type = 1;
    }
    else {
      switch($mode)
      {
        case 'year' : $s = '����'; break;
        case 'month' : $s = '������'; break;
        case 'day' : $s = '���'; break;
        case 'second' : $s = '�������'; break;
        case 'man' : $s = '�'; break;
        case 'human' : $s = '�'; break;
        case 'messages' : $s = '���������'; break;
		case 'comments' : $s = '�����������'; break;
		case 'offers' : $s = '�����������'; break;
        case 'hidden_offers' : $s = '������� �����������'; break;
		case 'candidates' : $s = '���������'; break;
		case 'projects' : $s = '�������'; break;
		case 'blogs' : $s = '�����'; break;
        case 'votes' : $s = '������'; break;
      }
      $ending_type = 2;
    }

    return $s;
  }
  
  /**
   * �������� ������� ������ ���������
   *
   * @param string $link ������
   * @return string
   */
  function strip_http($link) {
    if (substr($link, 0, 8) == 'https://') {
      $link = substr($link, 8, strlen($link) - 8);
    }
    if (substr($link, 0, 7) == 'http://') {
      $link = substr($link, 7, strlen($link) - 7);
    }
    if (substr($link, 0, 6) == 'ftp://') {
      $link = substr($link, 6, strlen($link) - 6);
    }
/*
    if (substr($link, strlen($link) - 1, 1) == '/') {
      $link = substr($link, 0, strlen($link) - 1);
    }
*/
    return $link;
  }

  
  /**
   * ������� ���� HTML ��� ������� ����� � youtube/vimeo/rutube
   * 
   * @param   string   $id    id ��� ���� object
   * @param   string   $url   ������ � �����
   * @return  string          html
   */
   function show_video($id, $url) {
   	$url = preg_replace("#^http://http#", "http", $url);
    libxml_disable_entity_loader();
    $is_youtube_video = false;
	if (stristr($url, 'youtube.com') !== FALSE || stristr($url, 'youtu.be') !== FALSE) {
        $url  = preg_replace("/^(http:\/\/youtu\.be\/([-_A-Za-z0-9]+))/i", HTTP_PREFIX."youtube.com/v/$2", $url);
		$url  = str_replace('watch?v=', 'v/', $url);
		$url = str_replace("http://youtube.com", HTTP_PREFIX."youtube.com", $url);
		$url = str_replace("http://www.youtube.com", HTTP_PREFIX."www.youtube.com", $url);
		if (!stripos($url, 'fs=1')) $url .= '&fs=1';
		$dom = "youtube-{$id}";
		$width  = 425;
		$height = 344;
		$is_youtube_video = true;
	} else if (stristr($url, 'rutube.ru') !== FALSE) {
        $s1 = preg_match('~^(?:http|https)://rutube.ru/tracks/(\d{1,})\.html$~', $url, $m1);
        $s2 = preg_match('~^(?:http|https)://rutube.ru/video/([-_A-Za-z0-9]+)/{0,1}$~', $url, $m2);
        if($s1 || $s2) {
            if($s2) {
                $rutube_url = "https://rutube.ru/api/video/{$m2[1]}/?format=xml";
                $xml = simplexml_load_string(file_get_contents($rutube_url));
                if($xml) {
                    //$html = (string) $xml->html; 
                    //preg_match("/src=\"http:\/\/rutube.ru\/embed\/(\d{1,})\"/", $html, $html_url);
                    //$m1[1] = basename( (string) $xml->embed_url );
                    $url = (string) $xml->embed_url;
                }
            }
            
            if($s1) {
                $rutube_url = "https://rutube.ru/api/video/{$m1[1]}/?format=xml";
                $xml = simplexml_load_string(file_get_contents($rutube_url));
                if($xml) {
                    $url = (string) $xml->embed_url;
                }
            }
            
            $url = "https://rutube.ru/player.swf?hash=" . (int) basename($url) . "&referer=";
            //$rutube_url = "http://rutube.ru/cgi-bin/xmlapi.cgi?rt_mode=movie&rt_movie_id={$m1[1]}&utf=1";
            //$url = (string) $xml->embed_url;
            
            //$xml = simplexml_load_string(file_get_contents($rutube_url));
            //if($xml) {
            //    $url = (string) $xml->movie->playerLink;
            //}
        }
		$dom = "rutube-{$id}";
		$width  = 470;
		$height = 353;
                
	            $html  = "<div class=\"b-layout__txt b-layout__txt_padbot_10 b-page__ipad b-page__iphone\"><a class=\"b-layout__link\" href=\"{$url}\">{$url}</a></div>";
                $html .= "<center class=\"b-page__desktop b-page__ipad\"><object width='{$width}' height='{$height}'>";
                $html .= "<param name='movie' value='{$url}'>";
                $html .= "</param><param name='wmode' value='opaque'></param>";
                $html .= "<param name='allowfullscreen' value='true'></param>";
                $html .= "<param name='flashvars' value='referer=" . $GLOBALS['host'] . "'></param>";
                $html .= "<embed src='{$url}' flashvars='referer=" . $GLOBALS['host'] . "' type='application/x-shockwave-flash' wmode='opaque' width='{$width}' height='{$height}' allowfullscreen='true' ></embed>";
                $html .= "</object></center>";
                
                //$html  = "<div style='width: {$width}px; height: {$height}px; text-align: center; margin:0 auto;'>";
                //$html .= "<iframe width='{$width}' height='{$height}' src='{$url}' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowfullscreen scrolling='no'> </iframe>";
                //$html .= "</div>";
                return $html;
	} else if (stristr($url, 'vimeo.com') !== FALSE) {
		$url = preg_replace("/^http:\/\/vimeo.com\//", "", $url);
		$url = HTTP_PREFIX."vimeo.com/moogaloop.swf?clip_id={$url}&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1";
		$dom = "vimeo-{$id}";
		$width  = 400;
		$height = 265;
	} else {
		return '';
	}
	
	/*if(is_https() && $is_youtube_video == false) {
	    $url = preg_replace('~^https://~', 'http://', $url);
	}*/
	
	$html  = "<div class=\"b-layout__txt b-layout__txt_padbot_10 b-page__ipad b-page__iphone\"><a class=\"b-layout__link\" href=\"{$url}\">{$url}</a></div>";
	$html .= "<div class=\"b-page__desktop b-page__ipad\" style='width: {$width}px; height: {$height}px; text-align: center; margin:0 auto;'>";
	$html .= "<div id='ytplayer-{$dom}' class='ytplayer' style='{$width}px; height: 100%; background-color: #F2F2F2; font-size: 14px; padding: 7px'><p>&nbsp;</p>";
	$html .= "��� ��������� ����-������ ���������� ���������� ���� ����� � ��������� ������ JavaScript.";
	$html .= "</div></div>\n";
    $html .= "<script type='text/javascript'>\n";
    $html .= "if (typeof videoPlayers == 'undefined') videoPlayers = {};";
    $html .= "videoPlayers.item_".(md5($url))." = function(){\n";
    $html .= "var params = { allowfullscreen: 'true', allowscriptaccess: 'always', wmode: 'opaque' };\n";
    $html .= "var atts = { id: 'myytplayer_{$dom}', style: 'text-align: center;' };\n";
    $html .= "swfobject.embedSWF('{$url}', 'ytplayer-{$dom}', '{$width}', '{$height}', '8', null, null, params, atts);\n";
    $html .= "};";
    $html .= "</script>\n";
	return $html;
  }

    /**
    * ��������� ������ � Youtube/Rutube/Vimeo
    * 
    * @param   string          $url   ������, ������� ����� ���������
    * @return  string|boolean         ���� ��������� �������, ���������� ��������� ����������������� ������, ��� FALSE
    */
    function video_validate($url) {
        if (!preg_match("/^(?:http|https)?:\/\//i", $url)) $url = 'http://'.$url;
        if (preg_match("/^((?:http|https):\/\/youtu\.be\/([-_A-Za-z0-9]+))/i", $url, $o)) {
            return $o[1];
        } else if (preg_match("/^((?:http|https):\/\/(?:ru\.|www\.)?youtube\.com\/watch\?).*(v=[-_A-Za-z0-9]+)/i", $url, $o)) {
            return $o[1] . $o[2];
        } else if (preg_match("/^((?:http|https)?:\/\/(?:www\.)?rutube\.ru\/video\/?[-_A-Za-z0-9]+\/{0,1})/i", $url, $o)) {
            return preg_replace(array( "/^http:\/\/www\./", "/^https:\/\/www\./", "/^http:\/\//"), "https://", $o[1]);
        } else if (preg_match("/^((?:http|https)?:\/\/(?:www\.|video\.)?rutube\.ru\/(?:tracks\/)?[-_A-Za-z0-9]+(?:\.html)?)/i", $url, $o)) {
            return preg_replace(array( "/^http:\/\/www\./", "/^https:\/\/www\./", "/^http:\/\//"), "https://", $o[1]);
        } else if (preg_match("/^((?:http|https):\/\/(?:www\.)?vimeo\.com\/[0-9]+)/i", $url, $o)) {
            return preg_replace(array("/^http:\/\/www\./", "/^https:\/\/www\./", "/^https:\/\//"),  "http://", $o[1]);
        }
        return FALSE;
        // ������� ������ �� youtube
        // http://www.youtube.com/watch?feature=player_detailpage&v=hZI-LMHYU48 - �� ������������, ���� ������������
        // http://www.youtube.com/watch?feature=player_detailpage&v=hZI-LMHYU48#t=7s - ���� �� ������������
        // http://www.youtube.com/watch?v=hZI-LMHYU48&feature=g-logo&context=G295d7c5FOAAAAAAAAAA
        // ��� ��� ���� ����������� � ������ - http://www.youtube.com/watch?v=hZI-LMHYU48
        // http://youtu.be/hZI-LMHYU48
    }
  
   /**
    * �������� ��������� �� �������, ���� ���������� ����� ����� ����������� ����������� ����������, ����� ����������� ����������
    *
    * @todo ������������ zin(), ��������� zin() �������� �� ���.
    * 
    * @param mixed  $value 		�������� ����������
    * @param string $rstr		������ ��� ������ ���� ���������� ������
    * @return string
    */
  function ifnull($value, $rstr='&nbsp;') {
    if(!$value)
      return $rstr;
    return $value;
  }


  /**
   * ������� �������� URL �� ����������
   * ���������� true, ���� URL ������, false -- ���� ���, ���� � ����� �� �����
   *
   * @deprecated �� ��������, �� ������������, �� � �������� �� ���������� �����
   * 
   * @param string $url URL
   * @return boolean
   */
  function is_url($url)
  {
  	if ($url == "")
  	{
  		return true;
  	}
  	else
  	{
		if (!eregi("://", $url)) $url = "http://".$url;
		return ( ! preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $url)) ? false : true;
	}
  }


  /**
   * ��������� ����� e-mail �� ����������.
   *
   * @param string $email   ������, ���������� �������������� e-mail.
   * @return boolean    true, ���� ����� ������.
   */
  function is_email($email) {
      list($_localPart, $_domainName) = explode("@", $email);
      return preg_match('/^[A-Za-z0-9�-��-�\.\x21\x23\x24\x25\x26\x27\x2a\x2b\x2d\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e]{1,63}@[A-Za-z0-9�-��-�-]{1,63}(\.[A-Za-z0-9�-��-�]{1,63})*\.[A-Za-z�-�a-�]{2,15}$/', $email)
      && !strpos($email, '..') 
      && !preg_match("/(^\.|\.$)/", $_localPart);
  }

  /**
   * �������� ����� ����� � ����� ��
   * 
   * @return array ������, ��������������� ������ ����� (USD, EUR � �.�.), �������� �������� ���� �������� ������ ������.
   *               ���� ����� � ������ ������ ������� �� �������� 'Value'.
   */
  function getCBRates()
  {
    static $rates = NULL;
    if($rates) {
        return $rates;
    }
    
    $memBuff = new memBuff();
    $curr_day = strtotime(date('d.m.Y'));
    
    if($rdata = $memBuff->get('getCBRates')) {
        $rates = $rdata['rates'];
        if($rdata['day'] == $curr_day) {
            return $rates;
        }
    }
    
    $xml = @file_get_contents('http://www.cbr.ru/scripts/XML_daily.asp', false, stream_context_create(array('http'=>array('timeout' => 1))));//���� 5 ���
    
    if($xml && get_http_response_code($http_response_header) == 200) {
        $xmlDoc = new DOMDocument();
        if (@$xmlDoc->loadXML($xml)) {
            $rates = NULL;
            $xpath = new DOMXPath($xmlDoc);
            $valutes = $xpath->query('//Valute/CharCode');

            foreach($valutes as $v) {
              $name = $v->nodeValue;
              if($children = $v->parentNode->childNodes) {
                foreach($children as $ch) {
                  if($ch->nodeType==XML_ELEMENT_NODE)
                    $rates[$name][$ch->nodeName] = $ch->nodeValue;
                }
              }
            }
            
            //@todo: �������� ���� ���� �� ����� ����� ����� ����������� � ���� � ��
            $memBuff->set('getCBRates', array('day'=>$curr_day, 'rates'=>$rates), 86400);
        }
    }
    
    return $rates;
  }

    /**
     * �������� � ����� ������ ���� � ������ ������ (��������, 123.00 ����� 123)
     * 
     * @param  string $dec �����
     * @return string
     */
    function cutz($dec) {
    	return preg_replace('/\.0+$/','', $dec);
    }
    
  /**
   * ������ ��� ����������� (���� �� ����������).
   * 
   * @todo ������������ ��� ������� ����������� � ������� ���� ��� ����������� �� ���� �����
   *
   * @param integer $pages			���-�� �������
   * @param integer $page			������� ��������
   * @param string  $pre_link    	������ ��� �������� �� ���������. 'page=' ������ ���� � ����� ������, ���� ����� ������������ ����� ��������
   * @return string		    HTML ��� ��������
   */
  function get_pager($pages, $page, $pre_link)
  {
   /**
    * ������� ���������, ���������� �������� � ��������, � ��� �� �������� ������� ��������
    * 
    * @param integer $iCurrent ������� ��������
    * @param integer $iStart   ��������� ������� ��������� ������ �������
    * @param integer $iAll     �������� ������� ��������� ������ �������
    * @param string  $sHref    ������ ������� �� �� ��� ���� �������� (������ ������, ������ ����������� page)
    * @return string HTML-���
    */
  	function get_pager_navigation($iCurrent, $iStart, $iAll, $sHref) {
      $sNavigation = '';
      for ($i=$iStart; $i<=$iAll; $i++) {
        if ($i != $iCurrent)
          $sNavigation .= '<a href="'.$sHref.$i.'" >'.$i.'</a>&nbsp;';
        else
          $sNavigation .= '<span class="page"><span><span>'.$i.'</span></span></span>&nbsp;';
      }
      return $sNavigation;
    } 
    
    if ($pages > 1) {
      $sBox = '<div class="pager">';
      if ($page == $pages)
        ;//$sBox .= '<span class="page-next">���������&nbsp;&nbsp;&rarr;</span>';
      else
        $sBox .= '<input id="next_navigation_link" type="hidden" value="'.$pre_link.($page+1).'" /><span class="page-next"><a href="'.$pre_link.($page+1).'">���������</a>&nbsp;&nbsp;&rarr;</span>';
      if ($page == 1)
        ;//$sBox .= '<span class="page-back">&larr;&nbsp;&nbsp;����������</span>';
      else
        $sBox .= '<input id="pre_navigation_link" type="hidden" value="'.$pre_link.($page-1).'" /><span class="page-back">&larr;&nbsp;&nbsp;<a href="'.$pre_link.($page-1).'">����������</a></span>';
  
      if ($page <= 10) { // � ������
        $sBox .= get_pager_navigation($page, 1, ($pages>10)?($page+4):$pages, $pre_link);
        if ($pages > 15) {
          $sBox .= '...';
        }
      }
      elseif ($page >= $pages-10) { // � �����
        $sBox .= '...';
        $sBox .= get_pager_navigation($page, $page-5, $pages, $pre_link);
      }
      else {
        $sBox .= '...';
        $sBox .= get_pager_navigation($page, $page-4, $page+4, $pre_link);
        $sBox .= '...';
      }
      $sBox .= "</div>";
    }

    return $sBox;
  }

    /**
     * ������ ������� (����������� ���� �� ���������� �������)
     *
     * @param integer $pages	���-�� �������
     * @param integer $page		������� ��������
     * @param string  $sHref    ������ ��� �������� �� ���������. 'page=' ������ ���� � ����� ������, ���� ����� ������������ ����� ��������
     * @return string		    HTML ��� ��������
     */
    function get_pager2($pages,$page,$sHref) {
    	
    	/**
    	 * ������� ���������, ���������� �������� � ��������, � ��� �� �������� ������� ��������
    	 * 
    	 * @param integer $iCurrent ������� ��������
    	 * @param integer $iStart   ��������� ������� ��������� ������ �������
    	 * @param integer $iAll     �������� ������� ��������� ������ �������
    	 * @param string  $sHref    ������ ������� �� �� ��� ���� �������� (������ ������, ������ ����������� page)
    	 * @return string HTML-���
    	 */
        function buildNavigation($iCurrent, $iStart, $iAll, $sHref) {
            $sNavigation = '';
            for ($i=$iStart; $i<=$iAll; $i++) {
                if ($i != $iCurrent) {
                    $sNavigation .= "<a href=\"".$sHref.$i."\" >".$i."</a>";
                } else {
                    $sNavigation .= '<b style="margin-right: 5px">'.$i.'</b>';
                }
            }
            return $sNavigation;
        }

        $maxpages = $pages;
        $i = 1;

        if ($pages > 32){
            $i = floor($page/10)*10 + 1;
            if ($i >= 10 && $page%10 < 5) $i = $i - 5;
            $maxpages = $i + 22 - floor(log($page,10)-1)*4;
            if ($maxpages > $pages) $maxpages = $pages;
            if ($maxpages - $i + floor(log($page,10)-1)*4 < 22 && $maxpages - 22 > 0) $i = $maxpages - 24 + floor(log($page,10)-1)*3;
        }
        $sBox = '<div id="fl2_paginator"><table width="100%"><tr>';
        if ($page == 1 || $page > $pages){
            $sBox .= '<td><div id="nav_pre_not_active"><span>����������</span></div></td>';
        } else {
            $sBox .= "<input type=\"hidden\" id=\"pre_navigation_link\" value=\"".($sHref.($page-1))."\" />";
            $sBox .= "<td><div id=\"nav_pre_not_active\"><a href=\"".($sHref.($page-1))."\">����������</a></div></td>";
        }
        $sBox .= '<td  style="text-align: center; width:90%">';
    	//� ������
        if ($page <= 10) {
            $sBox .= buildNavigation($page, 1, (($page+4 > $pages)? $pages: ($page+4)), $sHref);
            if ($page + 4 < $pages) {
                $sBox .= '<span style="padding-right: 5px">...</span>';
            }
        }
        //� �����
        elseif ($page >= $pages-10) {
            $sBox .= buildNavigation($page, 1, 5, $sHref);
            $sBox .= '<span style="padding-right: 5px">...</span>';
            $sBox .= buildNavigation($page, $page-4, $pages, $sHref);
        }else {
            $sBox .= buildNavigation($page, 1, 5, $sHref);
            $sBox .= '<span style="padding-right: 5px">...</span>';
            $sBox .= buildNavigation($page, $page-4, (($page+4 > $pages)? $pages: ($page+4)), $sHref);
            $sBox .= '<span style="padding-right: 5px">...</span>';
        }
        $sBox .= '</td>';
        if ($page == $pages || $page > $pages){

            $sBox .= "<td><div id=\"nav_next_not_active\"><span>���������</span></div></td>";
        } else {
            $sBox .= "<input type=\"hidden\" id=\"next_navigation_link\" value=\"".($sHref.($page+1))."\">";
            $sBox .= "<td><div id=\"nav_next_not_active\"><a href=\"".($sHref.($page+1))."\" >���������</a></div></td>";
        }
        $sBox .= '</tr>';
        $sBox .= '</table></div>';
        return $sBox;
    }

    /**
     * ������� N-�� ��������� ���������� � ������
     *
     * @todo ������� ������� ������������ � ������� ���� ��� ����� ���� ������ �� �������.
     * 
     * @param string  $base		�������� ������
     * @param string  $str		��� ����
     * @param integer $n		����� ���������
     * @return integer			����� ������� � �������� ������ 
     */
    function strnpos($base, $str, $n) {        
        if ($n <= 0 || intval($n) != $n || substr_count($base, $str) < $n)  return FALSE;
        
        $str = strval($str);
        $len = 0;
        
        for ($i=0 ; $i<$n-1 ; ++$i)
        {
            if ( strpos($base, $str) === FALSE ) return FALSE;
            
            $len += strlen( substr($base, 0, strpos($base, $str) + strlen($str)) );
            
            $base = substr($base, strpos($base, $str) + strlen($str) );
        }
        return strpos($base, $str) + $len;
    }
    
    
    /* �������� � ������� ����� */
    /**
     * 
     * @deprecated 
  	* @return name of foto file in upload directory
  	* @desc Moves uploaded foto
  	*/
    /*function MoveUploadedFile($dir, $file, &$error, $maxpw = 1048576, $file_ext = "", $image_size = 0, $change_name = 1, $server_root = 0, $resize=0, $prop = 0, $rgb=0xFFFFFF, $quality=100, $topfill=0){ //1 Mb
        $dir = ($server_root) ? $_SERVER['DOCUMENT_ROOT'] . $dir : $_SERVER['DOCUMENT_ROOT'] . "/users/".substr($dir, 0, 2)."/".$dir."/";

        if (!file_exists($dir)) {
        	mkdir($dir, 0777,1);
        }
        if ($file['size'] > 0){
            $ext = strtolower(getext($file['name']));

            if (in_array($ext, $GLOBALS['video_array'])) {
                if ($file['size'] > $GLOBALS['maxpw_video']) {
                    $error = "C������ ������� ���������. ";
                    return "";
                }
            }
            elseif (in_array($ext, $GLOBALS['audio_array'])) {
                if ($file['size'] > $GLOBALS['maxpw_audio']) {
                    $error = "C������ ������� ���������. ";
                    return "";
                }
            }
            elseif ($file['size'] > $maxpw){
                $error = "C������ ������� ����. ";
                return "";
            }
            if (!in_array($ext, $GLOBALS['graf_array']) && !in_array($ext, $GLOBALS['file_array']) && !in_array($ext, $GLOBALS['video_array']) && !in_array($ext, $GLOBALS['audio_array']) && $ext != $file_ext){
                $error = "������������ ��� �����. ";
                return "";
            }
            if ($change_name || preg_match("/[^-a-zA-Z0-9_.]+/", $file['name'])){
                /*
                ��� ��� ����� ���� ������� ��������, ���� $dir �� ���������� ���, �� �����-�� ��������, �������� ���������� ����� � ���� ����� ���������� ��������,
                � ���� ������ ������� secure_tmpname ���������� ����, �.�. ���� � ���� ������ ��������� � ��������� ��������� ����� � ������������ �� ������� ������ �� ��, ��� �������.
                
                $tmp = secure_tmpname($dir,".".$ext);
                if (!$tmp) return false;
                $photoname = substr_replace($tmp,"",0,strlen($dir));
            } else $photoname = $file['name'];
            
            if (file_exists($dir))
            {
                if ((!@move_uploaded_file($file['tmp_name'],$dir.$photoname)))
                {
                    $error .= "���������� ��������� ����. ";
                }
                elseif (in_array($ext, $GLOBALS['video_array'])) { // �����
                    $result = recodeVideo($dir, $photoname, $error, $server_root);
                    if (!$error)
                    {
                        return $result;
                    }
                }
                elseif (in_array($ext, $GLOBALS['audio_array'])) { // ����
                    $result = recodeAudio($dir, $photoname, $error, $server_root);
                    if (!$error)
                    {
                        return $result;
                    }
                }
                elseif ($image_size) {
                    list($width, $height, $type, $attr) = getimagesize($dir.$photoname);

                    if ($resize) {


                        $src = $dir.$photoname;
                        $dest = $dir.$photoname;

                        //

                        // print $image_size['width'];

                        $size = getimagesize($src);

                        $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
                        $icfunc = "imagecreatefrom" . $format;
                        $imfunc = "image" . $format;
                        if (!function_exists($icfunc) || !function_exists($imfunc)) {
                            unlink ($src);
                            $error = "������������ ������ �����. ". $imfunc;
                            $photoname = "";
                        }
                        else {



                            $x_ratio = $image_size['width'] / $size[0];
                            $y_ratio = $image_size['height'] / $size[1];

                            $ratio       = min($x_ratio, $y_ratio);
                            if ($ratio == 0) $ratio = max($x_ratio, $y_ratio);
                            $use_x_ratio = ($x_ratio == $ratio);

                            $new_width   = $use_x_ratio  ? $image_size['width']  : floor($size[0] * $ratio);
                            $new_height  = !$use_x_ratio ? $image_size['height'] : floor($size[1] * $ratio);
                            $new_left    = $use_x_ratio  ? 0 : floor(($image_size['width'] - $new_width) / 2);
                            $new_top     = !$use_x_ratio ? 0 : floor(($image_size['height'] - $new_height) / 2);

                            $isrc = $icfunc($src);

                            if ($isrc)
                            {
                                if ($prop){
                                    if ($topfill) {
                                        $idest = imagecreatetruecolor( $image_size['width'], $image_size['height']);
                                        imagefill($idest, 0, 0, $rgb);
                                        imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
                                        $new_width, $new_height, $size[0], $size[1]);
                                    }
                                    else {
                                        $idest = imagecreatetruecolor($new_width, $new_height);
                                        imagefill($idest, 0, 0, $rgb);
                                        imagecopyresampled($idest, $isrc, 0, 0, 0, 0,
                                        $new_width, $new_height, $size[0], $size[1]);
                                    }
                                } else {
                                    $idest = imagecreatetruecolor( $image_size['width'], $image_size['height']);
                                    imagefill($idest, 0, 0, $rgb);
                                    imagecopyresampled($idest, $isrc, 0, 0, 0, 0,
                                    $image_size['width'], $image_size['height'], $size[0], $size[1]);
                                }
                                //unlink ($dir.$photoname);
                                if ($size[2] == 2) imagejpeg($idest, $dest, $quality);
                                else $imfunc($idest, $dest);
                                imagedestroy($isrc);
                                imagedestroy($idest);
                                unset($isrc);
                                unset($idest);

                            }
                            else
                            {
                                unlink ($dir.$photoname);
                                $error = "�� ���� �������� ������ �����. ";
                                $photoname = "";
                            }




                            //

                        }

                    }
                    else if ((!$image_size['less'] && ($width != $image_size['width'] || $height != $image_size['height'])) || ($image_size['less'] && ($width > $image_size['width'] || $height > $image_size['height']))) {
                        unlink ($dir.$photoname);
                        $error = "������������ ������� �����. ";
                        $photoname = "";
                    }

                }
            }
            else
            {
                $error .= "���������� ��������� ����. ";
            }
        } else $photoname = "";

        return ($photoname);
    }*/
    
    /**
     * ��������� ��� ����� �������
     *
     * @param integer $page    ������� ��������
     * @param integer $pages   ����� �������
     * @param integer $count   ���������� ������ ������ ������ �������, ������� ���������� �������� �� ������������� ���������� 
     * 							    (@example ��� �������� 3 - 1,2,3 ��� �������� 4 1,2,3,4 etc...)
     * @param string $href    ������ �� �������� ��� ������������ ���������, ������ �������� �������� � ����� ��� (%s/link_href?page=%d&param%s) 
     * 							    ��� %d ���������� �� �������� � ������� ���������� �������
     * 							    @see sprintf();
     * @param string $link_type ��������� �������� ������������ ������� �� �������. ������ href ����� ������� onclick � ��.�������
     * @return string
     */
    function new_paginator($page, $pages, $count=PAGINATOR_PAGES_COUNT, $href=false, $link_type = 'href') {
		if($pages==1) {return '';}
        
	    $html = '<div class="b-pager" >';
        
        if ( $link_type != 'href' ) {
            $link_type = 'href="#" ' . $link_type;
        }
        
        $start = (int) $page - $count;
        if($start<1) $start = 1;
        
        $end = (int) $page + $count;
        if($end>$pages) $end = $pages;
        
        $html .= '<ul class="b-pager__back-next">';
        if($page < $pages) {$html .= sprintf($href, '<li class="b-pager__next"><a class="b-pager__link" '.$link_type.'=', $page+1, ' id="PrevLink"></a></li>');} 
        if($page > 1) {$html .= sprintf($href, '<li class="b-pager__back"><a class="b-pager__link" '.$link_type.'=', $page-1 ,' id="NextLink"></a></li>');} //$page-1
        $html .= '</ul>';
        
        $html .= '<ul class="b-pager__list">';
        for ($i=$start;$i<=$end;$i++) {
            if ($i == $start && $start > 1) {  
                $html .= sprintf($href, '<li class="b-pager__item"><a class="b-pager__link" '.$link_type.'="', 1 ,'">1</a></li>');  
                if ($i==3) {
                    $html .= sprintf($href, '<li class="b-pager__item"><a class="b-pager__link" '.$link_type.'="', 2 ,'">2</a></li>'); 
                } elseif ($i!=2) {
                    $html .= '<li class="b-pager__item b-pager__item_hellip"></li>';
                }
            }
            $html .= $page == $i 
                    ? '<li class="b-pager__item b-pager__item_active">'.$i.'</li>' 
                    : sprintf($href, '<li class="b-pager__item"><a class="b-pager__link" '.$link_type.'=', $i ,'>'.$i.'</a></li>');
            
            if ($i == $end && $page < $pages-1 && $pages > $end ) { 
                if($pages-$end-1 > 1) $html .= '<li class="b-pager__item b-pager__item_hellip"></li>';
            }
        }
        $html .= '</ul>';
        
        return $html.'</div>';   
    }

    /**
     * ��������� ��� ����� ������� (����� ������)
     *
     * @param integer $page    ������� ��������
     * @param integer $pages   ����� �������
     * @param integer $count   ���������� ������ ������ ������ �������, ������� ���������� �������� �� ������������� ����������
     * 							    (@example ��� �������� 3 - 1,2,3 ��� �������� 4 1,2,3,4 etc...)

     * @param string $href    ������ �� �������� ��� ������������ ���������, ������ �������� �������� � ����� ��� (%s/link_href?page=%d&param%s)
     * 							    ��� %d ���������� �� �������� � ������� ���������� �������
     * 							    @see sprintf();
     * @param boolean $js_view ���� ������ ������ "��������� ��������", "���������� ��������" ����� JavsScript
     * @param string $page_param ��� ��������� ������� �������� �� ����� �������� � $href
     * @return string
     */
    function new_paginator2($page, $pages, $count=PAGINATOR_PAGES_COUNT, $href=false, $js_view = false, $page_param = '') {
        if($pages==1) {return '';}
        $html = '<div class="b-pager" >';
        if ($href) {
            $href = change_q_x($href, true, false);
        }
        if (is_array($count)) {
            list($scount, $ecount) = $count;
        } else {
            $scount = $ecount = $count;
        }
        if($pages > 1){
            $start = $page - $scount;
            if($start<1) $start = 1;

            $end = $page + $ecount;
            if($end>$pages) $end = $pages;
        
            
            $html .= '<ul class="b-pager__back-next">';
            if($page < $pages) {
                if($js_view) {
                    $seo_text = sprintf($href, '<li class="b-pager__next"><a href="', $page+1,'" id="PrevLink" class="b-pager__link"></a></li>');
                    $html .= seo_end($seo_text);
                } else {
                    $html .= sprintf($href, '<li class="b-pager__next"><a class="b-pager__link" href="', $page+1,'" id="PrevLink"></a></li>');
                }
            } 
            if($page > 1) {
                if($js_view) {
                    $seo_text = sprintf($href, '<li class="b-pager__back"><a  id="NextLink" class="b-pager__link" href="', $page-1,'"></a></li>');
                    $html .= seo_end($seo_text);
                } else {
                    $html .= sprintf($href, '<li class="b-pager__back"><a id="NextLink" class="b-pager__link" href="', $page-1,'" ></a></li>');
                }
            } 
            $html .= '</ul>';
            $html .= '<ul class="b-pager__list">';
            for($i=$start;$i<=$end;$i++) {
                $tempHtml = "";
                //������ �� ������ �������� �, ��������, �� ������
                if ($i == $start && $start > 1) {
                    $tempHtml .= sprintf($href, '<li class="b-pager__item"><a class="b-pager__link" href="',1,'">1</a></li>');
                    if ($i == 3) {
                        $tempHtml .= sprintf($href, '<li class="b-pager__item"><a class="b-pager__link" href="',2,'">2</a></li>');
                    } elseif ($i != 2) {
                        $tempHtml .= "<li class='b-pager__item b-pager__item_hellip'></li>";
                    }
                }
                
                $tempHtml .= ( $page == $i ? '<li class="b-pager__item b-pager__item_active">'.$i.'</li>' : sprintf($href, '<li class="b-pager__item"><a class="b-pager__link" href="',$i,'">'.$i.'</a></li>') );
                if($i == $end && $pages > $end) { 
                    $tempHtml .= '<li class="b-pager__item b-pager__item_hellip"></li>';
                }
                
                // ������� �������� � ������ �������� ��� ����� ������� url
                if ($page_param && ($i === 1 || ($i == $start && $start > 1))) {
                    $tempHtml = preg_replace("/\?".$page_param."=1$/", ".", $tempHtml);
                    $tempHtml = preg_replace("/&".$page_param."=1$/", "", $tempHtml);
                }
                $html .= $tempHtml;
            }
            $html .= '</ul>';
        }
        
        return $html.'</div>';
    }
    
    /**
     * ��� ���� ��������� ��� ����������� ������ �������
     *
     * @param integer $page		������� ��������
     * @param integer $pages	����� �������
     * @param integer $count	���������� ������ ������ ������ �������, ������� ���������� �������� �� ������������� ���������� 
     * 							(@example ��� �������� 3 - 1,2,3 ��� �������� 4 1,2,3,4 etc...)
     * @param string  $sHref	������ �� �������� ��� ������������ ���������, ������ �������� �������� � ����� ��� (%s/link_href?page=%d&param%s) 
     * 							��� %d ���������� �� �������� � ������� ���������� �������
     * 							@see sprintf();
     * @return string 	HTML-���
     */
    function paginators($page, $pages, $count=PAGINATOR_PAGES_COUNT, $sHref=false) {
        if($pages <= 1) return '';
		$sBox = '<div id="fl2_paginator">';
	    $maxpages = $pages;
	    $i = 1;
		
	    if ($pages > 32){
	    	$i = floor($page/10)*10 + 1;
	        if ($i >= 10 && $page%10 < 5) $i = $i - 5;
	        $maxpages = $i + 22 - floor(log($page,10)-1)*4;
	        if ($maxpages > $pages) $maxpages = $pages;
	        if ($maxpages - $i + floor(log($page,10)-1)*4 < 22 && $maxpages - 22 > 0) $i = $maxpages - 24 + floor(log($page,10)-1)*3;
	    }
	    $sBox .= '<table width="100%"><tr>';
	    if ($page == 1){
	    	$sBox .= '<td><div id="nav_pre_not_active"><span>����������</span></div></td>';
	    } else {
	        $sBox .= sprintf($sHref, "<input type=\"hidden\" id=\"pre_navigation_link\" value=\"", $page-1 ,"\" />");
	        $sBox .= sprintf($sHref, "<td><div id=\"nav_pre_not_active\"><a href=\"", $page-1 ,"\" style=\"color: #717171\">����������</a></div></td>");
	    }
	    $sBox .= '<td  style="text-align: center; width:90%">';
	    
		$cnt = 2;      // ����� ������� - ������ ����� ������� ��������
	    $o = $cnt*2+1; // ������� ����� ����������
	    $r = $page+$o; // ������ ������� �� ������� ��������
	    $l = $page-$cnt; // ����� ������� �� ������� ��������
	    
	    
	    if($pages > $r || $l>=$o) {
	    	$sBox .= buildPaginator($page, 1, $l>=$o?$cnt+1:$page+$cnt, $sHref);
	    	$sBox .= '<span style="padding-right: 5px">...</span>';
	    	if($r<$pages && $l>=$o) {
	    		$sBox .= buildPaginator($page, $l>=$o?$page-$cnt:$pages-$cnt, $page+$cnt, $sHref);
	    		$sBox .= '<span style="padding-right: 5px">...</span>';
	    		$sBox .= buildPaginator($page, $pages-$cnt, $pages, $sHref);
	    	} else {
	    		$sBox .= buildPaginator($page, $l>=$o?$page-$cnt:$pages-$cnt, $pages, $sHref);
	    	}
	    } else {
	    	$sBox .= buildPaginator($page, 1, $pages, $sHref);
	    }
	    
        $sBox .= '</td>';
        if ($page == $pages) {
        	$sBox .= "<td><div id=\"nav_next_not_active\"><span>���������</span></div></td>";
        } else {
        	$sBox .= sprintf($sHref, "<input type=\"hidden\" id=\"next_navigation_link\" value=\"", $page+1 ,"\">");
        	$sBox .= sprintf($sHref, "<td><div id=\"nav_next_not_active\"><a href=\"", $page+1, "\" style=\"color: #717171\">���������</a></div></td>");
        }
        
        $sBox .= '</tr>';
        $sBox .= '</table>';
	    $sBox .= '</div>';
	    
	    return $sBox;
    }
    
   /**
    * ������� ���������, ���������� �������� � ��������, � ��� �� �������� ������� ��������
    * 
    * @see paginators();
    * 
    * @param integer $iCurrent ������� ��������
    * @param integer $iStart   ��������� ������� ��������� ������ �������
    * @param integer $iAll     �������� ������� ��������� ������ �������
    * @param string  $sHref    ������ ������� �� �� ��� ���� �������� (������ ������, ������ ����������� page)
    * @return string HTML-���
    */
    function buildPaginator($iCurrent, $iStart, $iAll, $sHref) {
		$sNavigation = '';
		for ($i=$iStart; $i<=$iAll; $i++) {
			if ($i != $iCurrent) {
				$sNavigation .= sprintf($sHref, "<a href=\"", $i,"\" style='color:#717171; text-decoration:underline; margin-right: 5px;'>".$i."</a>");
			} else {
				$sNavigation .= '<b style="margin-right: 5px; font-weight:normal; padding:0 3px; color: #c6c6c6; text-decoration:none; border:1px solid #c6c6c6;">'.$i.'</b>';
			}
		}
		return $sNavigation;
	}
	
	/**
	 * ������ ������������� �������� �� �������
	 * ����� � ��� ��� ������� array_uniqie() �� ����� ������������ ��������� ������, 

	 * ��� ��� "���", "���", "���" ��� ������ �������� �������, 
	 * � ��� ���������� ������ �������� ������� ������������  (������ ������� ������������ ��� ���������� ������������� �����)
	 *
	 * @param array $arr	������ ��� ��������
	 * @return array 
	 */
	function get_array_unique($arr) {
		foreach($arr as $k=>$v)  $result[strtolower(trim($v))] = strtolower(trim($v));
		foreach($result as $val) $ret[] = $val; 
		return $ret;
	}
	
	/**
	 * ������� ������ ������ ��� ���������� �������.
	 * ������� ������� ���������� �� ����������� ���������� (������� ��������� ���� ���)
	 *
	 * @param string  $nowFrom	������� ��������� ������ (������� ������ � �������)
	 * @param string  $toFrom	������ ������� �������������� ������ �� �������� ���������
	 * @param integer $sort		��� ���������� 	
	 * @param string  $user		������������ (�����)
	 * @param string  $num		���������� �������
	 * @return string HTML-���
	 */
	function getSortOpinionLink($nowFrom, $toFrom, $sort, $user, $num) {
		switch($sort) {
			case 1: $dp = "+&nbsp;"; $class="pOpinion"; break;
			case 2: $dp = ""; $class="nOpinion"; break;

			case 3: $dp = "-&nbsp;"; $class="mOpinion"; break;
			default: $dp = ""; $class=""; break; 
		}
		
		if(intval($_GET['sort']) == $sort && $toFrom == $nowFrom) { 
			$result = "<span class='".$class."'>".$dp.$num."</span>"; 
		} else {
			$result =  "<a href='/users/$user/opinions/?from=$toFrom&sort=$sort' class='".$class."'>".$dp.$num."</a>";
		}
		
		return $result;
	}

        	/**
	 * ������� ������ ������ ��� ���������� �������.
	 * ������� ������� ���������� �� ����������� ���������� (������� ��������� ���� ���)
	 *
	 * @param string  $nowFrom	������� ��������� ������ (������� ������ � �������)
	 * @param string  $toFrom	������ ������� �������������� ������ �� �������� ���������
	 * @param integer $sort		��� ����������
	 * @param string  $user		������������ (�����)
	 * @param string  $num		���������� �������
     * @param integer $period   ������ �� ������� ���������� ������: 0 - ���, 1 - �� ���, 2 - �� ��� ����, 3 - �� �����
     * @param integer $author   ����� ������: 0 - �����, 1 - ���������, 2 - ������������
	 * @return string HTML-���
	 */
	function getSortOpinionLinkEx($nowFrom, $toFrom, $sort, $user, $num, $period=0, $author = '') {
		switch($sort) {
			case 1: $dp = "+&nbsp;"; $class="ops-plus"; break;
			case 2: $dp = ""; $class="ops-neitral"; break;
			case 3: $dp = "-&nbsp;"; $class="ops-minus"; break;
			default: $dp = ""; $class=""; break;
		}

		if(intval($_GET['sort']) == $sort && $toFrom == $nowFrom) {
			$result = "<span class='".$class."'>".$dp.$num."</span>";
		} else {
			$result =  "<a href='/users/$user/opinions/?sort=$sort&period=$period&author=$author#op_head' class='".$class."'>".$dp.$num."</a>";
		}

		return $result;
	}
	
    
    
    
    function getOpinionLinks($login, $value)
    {
       return getSortOpinionLinkEx(0, 1, 1, $login, zin($value['total_opi_plus']), null, 0) . '&nbsp;' . 
              getSortOpinionLinkEx(0, 1, 2, $login, zin($value['total_opi_null']), null, 0) . '&nbsp;' . 
              getSortOpinionLinkEx(0, 1, 3, $login, zin($value['total_opi_minus']), null, 0);
    }





	/**
	 * ���������� ���������, ������� "��������" html ��������, ���� ��� �������� � ����� �������
	 * 
	 * @param   string   $str           ������, �� ������� ����� ����� ���������
	 * @param   integer  $start         ��������� ������� ��������� � ������
	 * @param   integer  $length        ����� ���������
	 * @param   boolean  $sripslashes   ���� TRUE, � $str � ������ ���������� striplashes, ����� ����� ����� �� ����������� � �������� ��������.
	 *                                  ����� ������ ��������� � ��� ����� ��������� addslashes, ��� ������� ��� �� �����
	 * @return  string                  ���������� ���������
	 */
	function substr_entity($str, $start, $length, $stripslashes=FALSE) {
		$range  = 10;
		$result = '';
		$point  = $length - $start - 1;
		$strlen = strlen($str);
		if ($stripslashes) $str = stripslashes($str);
		for ($i=0; ($i<$range && ($point-$i)>0); $i++) {
			if ($str{$point-$i} == ';') break;
			if ($str{$point-$i} == '&') {
				for ($j=0; ($j<$range && ($point+$j)<$strlen); $j++) {
					if ($str{$point+$j} == ';') {
						$result = substr($str, $start, $length-$i-1);
						break;
					}
				}
			}
		}
		if (!$result) $result = substr($str, $start, $length);
		return $stripslashes? addslashes($result): $result;
	}

    /**
     * ����������� "������" ��������� � ������.
     * �������������� ����� ��� �������� (UTF-8) � ����������� ������ (ANSI).
     *
     * @staticvar $re_attrs_fast_safe 	� ���������� PCRE ��� PHP \s - ��� ����� ���������� ������, � ������ ����� �������� [\x09\x0a\x0c\x0d\x20\xa0] ���, �� �������, [\t\n\f\r \xa0]
     *									���� \s ������������ � ������������� /u, �� \s ���������� ��� [\x09\x0a\x0c\x0d\x20]
     *   								regular expression for tag attributes correct processes dirty and broken HTML in a singlebyte or multibyte UTF-8 charset!
     * 
     * @param   string   $s        �����
     * @param   string   $is_html  ���� TRUE, �� html ����, ����������� � �������� �� ��������������
     * @return  string
     */
    function hyphen_words($s, $is_html = false)
    {
        $s = iconv('CP1251','UTF-8',$s);
        if (! $is_html)
        {
            $m = array($s);
            $m[3] =& $m[0];
            return iconv('UTF-8','CP1251',_hyphen_words($m));
        }

        static $re_attrs_fast_safe =  '(?![a-zA-Z\d])  #statement, which follows after a tag
                                       #correct attributes
                                       (?>
                                           [^>"\']++
                                         | (?<=[\=\x20\r\n\t]|\xc2\xa0) "[^"]*+"
                                         | (?<=[\=\x20\r\n\t]|\xc2\xa0) \'[^\']*+\'
                                       )*
                                       #incorrect attributes
                                       [^>]*+';

        $regexp = '/(?> #���������� PHP, Perl, ASP ���
                        <([\?\%]) .*? \\1>  #1

                        #����� CDATA
                      | <\!\[CDATA\[ .*? \]\]>

                        #MS Word ���� ���� "<![if! vml]>...<![endif]>",
                        #�������� ���������� ���� ��� IE ���� "<!--[if lt IE 7]>...<![endif]-->"
                      | <\! (?>--)?
                            \[
                            (?> [^\]"\']+ | "[^"]*" | \'[^\']*\' )*
                            \]
                            (?>--)?

                        >

                        #�����������
                      | <\!-- .*? -->

                        #������ ���� ������ � ����������

                      | <((?i:noindex|script|style|comment|button|map|iframe|frameset|object|applet))' . $re_attrs_fast_safe . '(?<!\/)>
                          .*?
                        <\/(?i:\\2)>  #2

                        #������ � �������� ����
                      | <[\/\!]?+ [a-zA-Z][a-zA-Z\d]*+ ' . $re_attrs_fast_safe . '>

                        #html �������� (&lt; &gt; &amp;) (+ ��������� ������������ ��� ���� &amp;amp;nbsp;)
                      | &(?>
                            (?> [a-zA-Z][a-zA-Z\d]++
                              | \#(?> \d{1,4}+
                                    | x[\da-fA-F]{2,4}+
                                  )
                            );
                         )+
                    )+
                    \K

                    #�� html ���� � �� ��������
                    | [^<&]++
                   /sxSX';
        $ret = preg_replace_callback($regexp, '_hyphen_words', $s);
        $ret = preg_replace("/(\.{5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(,{5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(\({5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(\){5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(;{50})/", "$1<br />",$ret);
        $ret = preg_replace("/(\\\{50})/", "$1<br />",$ret);
        $ret = preg_replace("/(\/{50})/", "$1<br />",$ret);
        $ret = preg_replace("/(:{50})/", "$1<br />",$ret);
        $ret = preg_replace("/(!{50})/", "$1<br />",$ret);
        $ret = preg_replace("/('{5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(\+{5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(={5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(_{5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(<{5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(>{5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(#{5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(\&{5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(\*{5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(\^{5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(%{5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(\\\${5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/((&quot;){50})/", "$1<br />",$ret);
        $ret = preg_replace("/((&#039;){50})/", "$1<br />",$ret);
        $ret = preg_replace("/((&lt;){5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/((&gt;){5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(@{5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(\?{5})/", "$1\xc2\xad",$ret);
        $ret = preg_replace("/(\d{5})/", "$1\xc2\xad",$ret);
        return iconv('UTF-8','CP1251',$ret);
    }
    
	/**
	 * ����������� "������" ��������� � ������. (��������������� �������)
	 * 
	 * @see hyphen_words();
	 * 
	 * @staticvar $rules  ����������� ��� ��� ������������ �������
	 *
	 * @param array $m	�������� �� ������ ������ ������
	 * @return string
	 */
    function _hyphen_words(array &$m)
    {
        if (strlen($m[0]) < 4) return $m[0];

        static $rules = null;

        if ($rules === null)
        {
            #����� (letter)
            $l = '(?: \xd0[\x90-\xbf\x81]|\xd1[\x80-\x8f\x91]  #�-� (���)
                    | [a-zA-Z]
                  )';
        
            #����� (letter)
            $l_en = '[a-zA-Z]';
            #����� (letter)
            $l_ru = '(?: \xd0[\x90-\xbf\x81]|\xd1[\x80-\x8f\x91]  #�-� (���)
                     )';
        
            #������� (vowel)
            $v = '(?: \xd0[\xb0\xb5\xb8\xbe]|\xd1[\x83\x8b\x8d\x8e\x8f\x91]  #���������� (�������)
                    | \xd0[\x90\x95\x98\x9e\xa3\xab\xad\xae\xaf\x81]         #��������ߨ (�������)
                    | (?i:[aeiouy])
                    | \d
                    | [\.,\+=_\-"]
                  )';
        
            #��������� (consonant)
            $c = '(?: \xd0[\xb1-\xb4\xb6\xb7\xba-\xbd\xbf]|\xd1[\x80\x81\x82\x84-\x89]  #�������������������� (���������)
                    | \xd0[\x91-\x94\x96\x97\x9a-\x9d\x9f-\xa2\xa4-\xa9]                #�������������������� (���������)
                    | (?i:sh|ch|qu|[bcdfghjklmnpqrstvwxz]|\d|[\.,\+=_\-"])
                  )';
        
            #�����������
            $x = '(?:\xd0[\x99\xaa\xac\xb9]|\xd1[\x8a\x8c])';   #������ (�����������)
        
            if (0)
            {
                #�������� �.�������� � ����������� �������� � ������������
                $rules = array(
                    # $1       $2
                    "/($x)     ($l$l)/sxSX",
                    "/($v)     ($v$l)/sxSX",
                    "/($v$c)   ($c$v)/sxSX",
                    "/($c$v)   ($c$v)/sxSX",
                    "/($v$c)   ($c$c$v)/sxSX",
                    "/($v$c$c) ($c$c$v)/sxSX",
                );
        
                #improved rules by Dmitry Koteroff
                $rules = array(
                    # $1                      $2
                    "/($x)                    ($l (?>\xcc\x81)? $l)/sxSX",
                    "/($v (?>\xcc\x81)? $c$c) ($c$c$v)/sxSX",
                    "/($v (?>\xcc\x81)? $c$c) ($c$v)/sxSX",
                    "/($v (?>\xcc\x81)? $c)   ($c$c$v)/sxSX",
                    "/($c$v (?>\xcc\x81)? )   ($c$v)/sxSX",
                    "/($v (?>\xcc\x81)? $c)   ($c$v)/sxSX",
                    "/($c$v (?>\xcc\x81)? )   ($v (?>\xcc\x81)? $l)/sxSX",
                );
            }
        
            #improved rules by Dmitry Koteroff and Rinat Nasibullin
            $rules = array(
                # $1                      $2
                "/($c)                    ($c (?>\xcc\x81)? $l)/sxSX",
                "/($v)                    ($v (?>\xcc\x81)? $l)/sxSX",
                "/($x)                    ($c (?>\xcc\x81)? $l)/sxSX",
                "/($v (?>\xcc\x81)? $c$c) ($c$c$v)/sxSX",
                "/($v (?>\xcc\x81)? $c$c) ($c$v)/sxSX",
                "/($v (?>\xcc\x81)? $c)   ($c$c$v)/sxSX",
                "/($c$v (?>\xcc\x81)? )   ($c$v)/sxSX",
                "/($v (?>\xcc\x81)? $c)   ($c$v)/sxSX",
                "/($c$v (?>\xcc\x81)? )   ($v (?>\xcc\x81)? $l)/sxSX",
            );
        }
        #\xc2\xad = &shy;  U+00AD SOFT HYPHEN
        return preg_replace($rules, "$1\xc2\xad$2", $m[0]);
    }
    
    /**
     * ���������� ���������� ico ��� ������������� �����
     *
     * @param string $ext ���������� �����
     * @return string
     */
    function getICOFile($ext, $pda=false) {
        
        switch ($ext) {
            case "swf":
				$ico = 'swf';
                break;

            case "mp3":
                $ico = 'mp3';
                break;

            case "rar":
                if($pda) $ico = 'archive';
                else $ico = 'rar';
                break;

            case "doc":
            case "docx":
                $ico = 'doc';
                break;

            case "pdf":
                $ico = 'pdf';
                break;

            case "ppt":
                $ico = 'ppt';
                break;

            case "rtf":
                $ico = 'rtf';
                break;

            case "txt":
                $ico = 'txt';
                break;


            case "xls":
            case "xlsx":
                $ico = 'xls';
                break;  
                  
            case "zip":
                if($pda) $ico = 'archive';
                else $ico = 'zip';
                break;
            case "jpg":
            case "jpeg":   
                $ico = 'jpeg';
                break;
            case "png":
                $ico = 'png';
                break; 
            case "ai":
                $ico = 'ai';
                break; 
            case "bmp":
                $ico = 'bmp';
                break; 
            case "psd":
                $ico = 'psd';
                break; 
            case "gif":
                $ico = 'gif';
                break;   
            case "flv":
                $ico = 'flv';
                break;   
            case "wav":
                $ico = 'wav';
                break;
            case "ogg":
                $ico = "ogg";
                break;
            case "3gp":
                $ico = $pda ? "3gp" : "gp";
                break; 
            case "wmv":
                $ico = "wmv";
                break;
            case "tiff":
                $ico = "tiff";
                break;
            case "avi":
                $ico = "avi";
                break;
            case "mkv":
                $ico = "hdv";
                break;
            case "ihd":
                $ico = "ihd";
                break;
            case "fla":
                $ico = "fla";
                break;
            default:
                $ico = 'unknown';
                break;

        }
        
        return $ico;
    }


    /**
     * �������� ������������� ��������
     * @return boolean
     */
    function browserCompat(&$browser = null, &$version = null) {
//        var_dump($_SERVER['HTTP_USER_AGENT']);

        // Yandex Bot
        if( stristr($_SERVER['HTTP_USER_AGENT'], 'http://yandex.com/bots') ) {
            $browser = "chrome";
            $version = array(0=>'Chromium/20', 1=>20);
            return true;
        }

        // Google Bot
        if( stristr($_SERVER['HTTP_USER_AGENT'], 'Googlebot') || stristr($_SERVER['HTTP_USER_AGENT'], 'Mediapartners-Google') || stristr($_SERVER['HTTP_USER_AGENT'], 'AdsBot-Google') ) {
            $browser = "chrome";
            $version = array(0=>'Chromium/20', 1=>20);
            return true;
        }

        // Opera
        if( stristr($_SERVER['HTTP_USER_AGENT'], 'Opera/') ) {
            preg_match ("/Opera\/([0-9]{1,1})/i", $_SERVER['HTTP_USER_AGENT'], $version);
            if (preg_match ("/Version\/([0-9]{2,2})/i", $_SERVER['HTTP_USER_AGENT'], $version2)) {
                $version[1] = $version2[1];
            }
            $browser = "opera";
            
            return ($version[1] >= 10);
        }
        
        // Chrome
        if( stristr($_SERVER['HTTP_USER_AGENT'], 'Chrom') ) {
            preg_match ("/Chrom.*?([0-9]{1,2})/i", $_SERVER['HTTP_USER_AGENT'], $version);
            $browser = "chrome";
            return ($version[1] >= 3);
        }

        // Safari
        if( stristr($_SERVER['HTTP_USER_AGENT'], 'Safari/') ) {
            preg_match ("/Version\/([0-9]{1,1})/i", $_SERVER['HTTP_USER_AGENT'], $version);
            $browser = "safari";
            if (!count($version)) { // �������� ��� ����� ��� �����
                preg_match('~(?:iOs|iPhone OS) (\d)~i', $_SERVER['HTTP_USER_AGENT'], $version);
                if ( empty($version) ) {
                    preg_match('#Mozilla/5.0 \(iPad; CPU OS [0-9]_[0-9]_[0-9] like Mac OS X\) AppleWebKit/[0-9]+\.[0-9]+ \(KHTML, like Gecko\) CriOS/([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+) Mobile/[0-9\w]+ Safari/[0-9]+\.[0-9]+#', $_SERVER['HTTP_USER_AGENT'], $version);
                }
                $browser = 'safari mobile';
                return ($version[1] >= 4);
            } elseif ($version[1] < 4) {
                $s = $_SERVER['HTTP_USER_AGENT'];
                if (strpos($s, "iPhone; U; CPU iPhone OS 5_1_1 like Mac OS X;") !== FALSE) {
                    $browser = 'CriOS';
                    $version = preg_replace("#.*CriOS/([0-9]*)\..*#", '$1', $s);
                    return true;
                }
            }
            return ($version[1] >= 4);
        }

        // MSIE
        if( stristr($_SERVER['HTTP_USER_AGENT'], 'msie ') ) {
            preg_match("/msie\s([0-9]{1,2}.[0-9]{1,3})/i", $_SERVER['HTTP_USER_AGENT'], $version);
            $browser = "msie";
            return ($version[1] >= 9);
        }

        // Firefox
        if( stristr($_SERVER['HTTP_USER_AGENT'], 'Firefox/') ) {
            preg_match ("/Firefox\/([0-9]+\.?[0-9]*)\.?[0-9]*/i", $_SERVER['HTTP_USER_AGENT'], $version);
            $browser = "firefox";
            return ($version[1] >= 9);
        }

        // Mozilla/FF 3
        if( stristr($_SERVER['HTTP_USER_AGENT'], 'rv:') ) {
            preg_match ("/rv:([0-9]+\.?[0-9]*)\.?[0-9]*/i", $_SERVER['HTTP_USER_AGENT'], $version);
            $browser = "mozilla";
            return ($version[1] >= 1.9);
        }

//        // KHTML
//        if( stristr($_SERVER['HTTP_USER_AGENT'], 'KHTML') ) {
//            preg_match ("/KHTML\/([0-9]{1,1})/i", $_SERVER['HTTP_USER_AGENT'], $version);
//            return ($version[1] >= 4);
//        }

        return false;
    }
    
    /**
     * ������� ��������� ������� ������ � �����
     *
     * @param string $bStr ������� ������ 
     * @return integer
     */
    function bitStr2Int($bStr)
    {
      $len = strlen($bStr);
      for($int=0,$i=0; $i<$len; $i++)
        $int |= ($bStr[$i] == '1') * (1 << ($len - ($i + 1)));

      return $int;
    }
    

/**
 * #0006191 ������������ � �������� ��� ������������� <body> �� ���/�� ���/������������� � �.�.
 *
 * @param string $add_class   �������������� css.
 * @return string CSS �����
 */
function cssClassBody($add_class = '') {
    if(!$_SESSION['uid']) {
        $bClass = "u-guest";     
    } elseif($_SESSION['pro_last']) {
        $bClass = "u-pro"; 
    } else {
        $bClass = "u-nopro";
    }
    return ($add_class ? $add_class.' ' : '').$bClass;
}


/**
 * ���������� ��� ��� ������ ������������
 * ���������� ��� ����� ��� if (!defined('IN_STDF')), �.�. ������������ ��� �������� ����� �� ���������-���������.\
 * 
 * @param integer $kind   ��� ������
 * @return string HTML-���	
 */
function webim_button($kind = 0, $title='������ ������ ������-������������', $class='b-footer__link') {
    $url = "/webim/client.php?theme=default&amp;lang=ru";
    $params = "class=\"{$class}\" href=\"$url\" target=\"_blank\"  onclick=\"if (navigator.userAgent.toLowerCase().indexOf('opera') != -1 && window.event.preventDefault) window.event.preventDefault();this.newWindow = window.open('/webim/client.php?theme=default&amp;lang=ru&'+'opener='+encodeURIComponent(document.location.href) + '&openertitle='+encodeURIComponent(document.title) , 'webim_beta_free_lance_ru', 'toolbar=0, scrollbars=0, location=0, menubar=0, width=600, height=600, resizable=1');if (this.newWindow==null)return false;this.newWindow.focus();this.newWindow.opener=window;return false\"";
    $out = "<!-- webim button --><a {$params}>";
    switch ($kind) {
        case 1: // ������ �� �������� ������� ���������
            $out .= '<img src="/images/consult2.png" width="163" height="14" alt="������-�����������" />';
            break;
        case 2: // ������� ������
            $out .= $title;
            break;
        case 3: // ������ �� BSOD
            $out .= '<img alt="������-�����������" src="/images/consult-maintenance.png" border="0" width="99" height="14" />';
            break;
        default:
            $out .= '<img alt="������-�����������" src="/images/consult.png" border="0" width="99" height="14" />';
    }
    $out .= '</a><!-- /webim button -->';
    // ������ ���������� ������
    if ($kind == 4) {
        $out = $params;
    }
    return $out;
}

/**
 * ������������ svg � Vml ?
 * 
 * @param  string $text
 * @return string
 */
function svgToVml($text) {

    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/XSL2/xslt-php4-to-php5.php');

    $xsl = $_SERVER['DOCUMENT_ROOT'] . "/classes/XSL2/svg2vml.xsl";
    $xml_contents = $text;

    $from = "/(<meta[^>]*[^\/]?)>/i";
    $xml_contents = preg_replace($from, "$1/>", $xml_contents);
    $from = "/\/(\/>)/i";
    $xml_contents = preg_replace($from, "$1", $xml_contents);
    $xml_contents = preg_replace("/<\!DOCTYPE[^>]+\>/i", "", $xml_contents);
    $xml_contents = preg_replace("/<\?xml-stylesheet[^>]+\>/i", "", $xml_contents);

    $xh = xslt_create();
    $arguments = array('/_xml' => $xml_contents, '/_xsl' => file_get_contents($xsl));
    $result = xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);
    xslt_free($xh);

    return $result;
}

/**
 * ����������� ������ � ����������
 * 
 * ���������� $GLOBALS['host'] � HTTP_PREFIX
 * 
 * http://www.free-lance.ru/blogs/   ->   http://www.free-lance.ru/blogs/
 * www.free-lance.ru/blogs/          ->   http://www.free-lance.ru/blogs/
 * /blogs/                           ->   http://www.free-lance.ru/blogs/
 * 
 * @param  string $link
 * @return string 
 */
function getAbsUrl( $link ) {
    if ( !preg_match('#^'. preg_quote($GLOBALS['host']) .'#', $link) ) {
        $domain = preg_replace('#^'. HTTP_PREFIX .'#', '', $GLOBALS['host']);

        if ( preg_match('#^'. preg_quote($domain) .'#', $link) ) {
            $link = HTTP_PREFIX . $link;
        }
        else {
            $link = $GLOBALS['host'] . $link;
        }
    }
    
    return $link;
}

/**
 * ������� ���-������
 *
 * @param array $params ������ � �����������
 * @param boolean $reset �� ��������� FALSE - � ��������� ���������� ����������� ������� ��������� �� $_GET,
 *                       ���� TRUE - ������ $_GET �� �����������
 * @return string ���
 */
function url($get, $params = array(), $reset = false, $prefix = '') {
    $keystr = "";
    $keyarr = array();
    $tmp = $_GET;
    //���� ������ (������ ������)
    if(is_array($get)) {
        if(count($get))
            $keystr = implode(",", array_keys($get));
    } elseif(!is_array($get) && $get) {
        $keystr = $get;
    } else {
        if(count($_GET))
            $keystr = implode(",", array_keys($_GET));
    }
    if($keystr) $keyarr = explode(",", change_q_x($keystr, 1,1));
//    if($keystr) $keyarr = explode(",", $keystr);

    foreach($tmp as $k => $v) {
        if(!in_array($k, $keyarr) || is_array($v)) {
            unset($tmp[$k]);
            continue;
        }
        $tmp[$k] = change_q_x(stripslashes($v), true, 1);
    }

    if($reset) $tmp = array();

    if(count($params))
        foreach($params as $k=>$v) {
            if(!$v) {
                unset($tmp[$k]);
                continue;
            }
            $tmp[$k] = change_q_x($v, true, 1);

        }

    if(count($tmp)) {
        return $prefix . http_build_query($tmp);
    }

    return '';
}

/**
 * ����������� ���-������ (��.����� e_url_safe)
 * ������, ����� ����� ��������������� ��� ��������� URI, �������� �������� �������� ����������
 *
 * @param string  $var   �������� ���������� � URI, ������� ����� ��������, ������� ��� ��������
 * @param string  $value ��������, ������� ������ ��������� ���������� $var. ���� NULL, �� ���������� ��������� �� URI
 * @param string  $url   URI, ������� �������������. �� ��������� ������� _SERVER['REQUEST_URI']
 *                       
 * @return string        ���������� URI
 * 
 * @TODO ������ ��� ������, ���� �� ������� parse_str(), http_build_query()
 * � ���� ������� �������� �������� ���
 * 
 * parse_str($url, $get);
 * if($value === null) unset($get[$var]); else $get[$var] = $value;
 * $resutl = http_build_query($get);
 * 
 */
function e_url($var, $value=NULL, $url=NULL) {
    if ($url === NULL) {
		$url = $_SERVER['REQUEST_URI'];
	}
	$adding_new = TRUE;
	$url_stack = preg_split("/(\?|&)/", $url, -1, PREG_SPLIT_DELIM_CAPTURE);
	if (count($url_stack)>1) {
		if ($url_stack[1] == '?') {
            $result = $url_stack[0] . '?';
            $s = 2;
        } else {
            $result = "?";
            $s = 0;
        }
		for($i=$s;$i<count($url_stack);$i=$i+2) {
			$t = explode("=", $url_stack[$i]);
			if ($t[0] == $var) {
				if ($value !== NULL) {
					$result .= "$var=$value&";
				}
				$adding_new = FALSE;
			} else {
				$result .= $url_stack[$i] . "&";
			}
		}
		if ($adding_new && ($value !== NULL)) {
			$result .= "$var=$value";
		} else {
			$result = substr($result, 0, strlen($result)-1);
		}
	} else {
        $result = $url_stack[0];
        
		if ($value !== NULL) {
			$result .= "?$var=$value";
		}
	}
    
	return $result;
}


/**
 * ����������� ���-������ � ���������� �������� ����� urlencode ��� ������ �� xss � ��.���� (��.����� e_url)
 * ������, ����� ����� ��������������� ��� ��������� URI, �������� �������� �������� ����������
 *
 * @param string  $var   �������� ���������� � URI, ������� ����� ��������, ������� ��� ��������
 * @param string  $value ��������, ������� ������ ��������� ���������� $var. ���� NULL, �� ���������� ��������� �� URI
 * @param string  $url   URI, ������� �������������. �� ��������� ������� _SERVER['REQUEST_URI']
 *                       
 * @return string        ���������� URI
 */
function e_url_safe($var, $value=NULL, $url=NULL) {
    if ($url === NULL) {
		$url = $_SERVER['REQUEST_URI'];
	}
	$adding_new = TRUE;
	$url_stack = preg_split("/(\?|&)/", $url, -1, PREG_SPLIT_DELIM_CAPTURE);
	if (count($url_stack)>1) {
		if ($url_stack[1] == '?') {
            $result = $url_stack[0] . '?';
            $s = 2;
        } else {
            $result = "?";
            $s = 0;
        }
		for($i=$s;$i<count($url_stack);$i=$i+2) {
			$t = explode("=", $url_stack[$i]);
			if ($t[0] == $var) {
				if ($value !== NULL) {
					$result .= urlencode($var) . '=' . urlencode($value) . '&';
				}
				$adding_new = FALSE;
			} else {
				$result .= urlencode($t[0]) . '=' . urlencode($t[1]) . "&";
			}
		}
		if ($adding_new && ($value !== NULL)) {
			$result .= urlencode($var) . '=' . urlencode($value);
		} else {
			$result = substr($result, 0, strlen($result)-1);
		}
	} else {
        $result = $url_stack[0];
        
		if ($value !== NULL) {
			$result .= '?' . urlencode($var) . '=' . urlencode($value);
		}
	}
	return $result;
}

/**
 * ������� ���������� ����� � ������ ���������
 * ������ �� ����������
 *
 * @param $number
 * @param $variants ������, ���������� ��������� ��������, �� ������ array(0 => '����', 1 => '���', 2=> '����')
 *
 * @return string;
 */
function getTermination($number,$variants){
            $mod1 = $number % 10;
            if ($mod1 == 1 && ($number < 10 || $number > 20)) {
                return $variants[0];
            } else if (($number >= 10 && $number <= 20) || $mod1 > 4 || $mod1 == 0) {
                return $variants[2];
            } else {
                return $variants[1];
            }
}

/**
 *  ������� ������� ��� �������, ������� �� �������� ������� �� ������,
 *  � ���������� ���������� �������� ��������� �����
 */
function extractInteger($string, $default_value = 0){
    if(!$str = preg_replace("/[^0-9]+/i", '', $string)) return $default_value;
    return abs(intval($str));
}

/**
 * ��������� ����� $n � ������.
 * ����� ����������� ������ ���� 0 < $n < 1000.
 * $rod ��������� �� ��� �������� (0 - �������, 1 - �������; ��������, "�����" - 1, "������" - 0).
 * @param integer $n - ����� ��� �������������
 * @param $rod - ��� ��������
 * @return string
 */
function number2string($n,$rod){
		$a = floor($n / 100);
		$b = floor(($n - $a*100) / 10);
		$c = $n % 10;

		$s = "";
		switch($a)
		{
			case 1: $s = "���";
			break;
			case 2: $s = "������";
			break;
			case 3: $s = "������";
			break;
			case 4: $s = "���������";
			break;
			case 5: $s = "�������";
			break;
			case 6: $s = "��������";
			break;
			case 7: $s = "�������";
			break;
			case 8: $s = "���������";
			break;
			case 9: $s = "���������";
			break;
		}
		$s .= " ";
		if ($b != 1)
		{
		   switch($b)
		   {
			case 1: $s .= "������";
			break;
			case 2: $s .= "��������";
			break;
			case 3: $s .= "��������";
			break;
			case 4: $s .= "�����";
			break;
			case 5: $s .= "���������";
			break;
			case 6: $s .= "����������";
			break;
			case 7: $s .= "���������";
			break;
			case 8: $s .= "�����������";
			break;
			case 9: $s .= "���������";
			break;
		   }
		   $s .= " ";
		   switch($c)
		   {
			case 1: $s .= $rod ? "����" : "����";
			break;
			case 2: $s .= $rod ? "���" : "���";
			break;
			case 3: $s .= "���";
			break;
			case 4: $s .= "������";
			break;
			case 5: $s .= "����";
			break;
			case 6: $s .= "�����";
			break;
			case 7: $s .= "����";
			break;
			case 8: $s .= "������";
			break;
			case 9: $s .= "������";
			break;
		   }
		}
		else //...�����
		{

		   switch($c)
		   {
			case 0: $s .= "������";
			break;
			case 1: $s .= "�����������";
			break;
			case 2: $s .= "����������";
			break;
			case 3: $s .= "����������";
			break;
			case 4: $s .= "������������";
			break;
			case 5: $s .= "�����������";
			break;
			case 6: $s .= "������������";
			break;
			case 7: $s .= "�����������";
			break;
			case 8: $s .= "�������������";
			break;
			case 9: $s .= "�������������";
			break;
		   }
		}
		return $s;
	}

        

/**
 * �������������� ������ ����� � ������.
 *
 * @param array   �������� ������ �� ������� �����, ������� ����� � ������ �� ����� ������������ �����.
 * @param string $id_col   ��� ����, ��������� ����.
 * @param string $par_col   ��� ����, ��������� ������������ ����.
 * @param boolean $is_indexed   ������������ �� ������ �� ������ �����.
 * @return array   ������.
 */
function array2tree($arr, $id_col, $par_col, $is_indexed = false) {
    $thread = array();
    $ch = array();
    if(!$is_indexed) {
        // �������������� ����.
        foreach($arr as $b)
            $thread[$b[$id_col]] = $b;
    } else {
        $thread = $arr;
    }

    // ��������� �������� ���� ������������ ����� �� �����.
    foreach($thread as $i=>$b) {
        if(!$b[$par_col])
            continue;
        $bp = &$thread[$b[$par_col]];
        $thread[$i]['level'] = $bp['level']+1;
        $bp['children'][$i] = &$thread[$i];
        $ch[] = $i;
    }
  
    // ������� ��������������� ���� � �������� �������.
    while($i=array_pop($ch))
        unset($thread[$i]);
    return $thread;
}

/**
 * ��������� ������ � ����������.
 *
 * @param string $location   uri, ���� ����� ���������.
 * @param integer $mode   0:��������� ���������, 1:��������� JS.
 */
function header_location_exit($location, $mode = 0, $innerOnly = TRUE) {
    if ( $innerOnly ) {
        preg_match("/^(https?\:\/\/(?:www\.)?free\-lance\.ru)?(.*)/", $location, $out);
        $location = str_replace('//', '/', $out[2]);
    }
    $location = str_ireplace(array("\n", "\r", "%0D", "%0A"), array("", "", "", ""), $location);
    if ( $mode == 0 ) {
        header("Location: {$location}");
    } else if ( $mode == 1 ) {
        echo "<script type='text/javascript'>document.location.href='{$location}'</script>";
    }
    exit;
}

/**
 * ����������� ����� ��� ���������� ������� � �������� html-��������.
 *
 * @param string $value   �����
 * @return string   ������� �����.
 */
function html_attr($value) {
    return str_replace( array('"', '<', '>'), array('&quot;', '&lt;', '&gt;'), $value );
}
        

/**
 * ���������� ������� ������ �� maqic_qoutes
 *
 * @param  mixed   $arr  ��������� ���������� ��� ������, ������� ����� �������� �� maqic_qoutes
 * @return mixed         ������������ ������ ��� ������
 */
function strip_magic_quotes($arr) {
	foreach ($arr as $k => $v) {
		if (is_array($v)) {
			$arr[$k] = strip_magic_quotes($v);
		} else {
			$arr[$k] = stripslashes($v);
		}
	}
	return $arr;
}

/**
 * ��������������� html (��������� ���������� ����, ��������� ����������� ���� � �.�.)
 * ��� ������ tidy
 *
 * @param string $input Html ���
 * @return string       ��������������� html-���
 */
function repair_html($input) {
    $input = preg_replace("/(li|ol|ul)>[\n]+/iU", "$1>", $input);

    $tidy = new tidy();
    $input = $tidy->repairString(
        str_replace(array(' '), array('&nbsp;'), nl2br($input) ),
        array('show-body-only' => true, 'wrap' => '0'), 'raw');
    $input = str_replace("\n", "", $input);
    $input = preg_replace("/\h/", " ", $input);

    return $input;
}

    /**
     *
     * @param string $filename
     * @return string ��� css-������ ��� �������
     */
    function getIcoClassByExt($filename) {
        $ext = strtolower(end(explode(".", $filename)));
        //return getICOFile($ext);
        switch (trim($ext)) {
            case 'doc':
            case 'docx':
                return 'doc';
                break;
            case 'xlsx':
            case 'xls':
                return 'xls';
                break;
            case 'rar':
                return 'rar';
                break;
            case 'zip':
                return 'zip';
                break;
            case 'pdf':
                return 'pdf';
                break;
            case 'jpg':
            case 'jpeg':
                return 'jpg';
                break;
            default: 
                return getICOFile($ext);
                break;
        }
        return $ext;
    }

    /**
     * ����������� ������ �����
     * 
     * @param  int $size ������ �����
     * @param  bool $rank
     * @param  int $bytes_in_kb ������� ���� � ��������� (1024, 1000)
     * @return unknown
     */
    function sizeFormat($size, $rank=false, $bytes_in_kb = 1024) {
        if ($size == 0)
            return "0 MB";
        if ($rank === false) {
            $rank = 0;
            $tmp_size = $size + 1;
            while (($tmp_size /= $bytes_in_kb) > 1) {
                $rank++;
            }
        }
        $abbr = array("�", "��", "��", "��", "��");

        if ($rank > 0)
            $size = $size / pow($bytes_in_kb, $rank);

        if ($rank < 1 || $size - intval($size) < 0.01)
            $retval = sprintf("%.0f {$abbr[$rank]}", $size);
        else
            $retval = sprintf("%.2f {$abbr[$rank]}", $size);

        return $retval;
    }

    /**
     * ����� �������� �����
     */
    function PrintSiteLogo(){
        $template = '<img src="/images/logo.png" width="197" height="28" alt="��������� ������, ���-����" class="logo" />';
        //...
        return $template;
    }

    /**
     * ���������� ����� ������� ���������� ������������.
     * 
     * @param  bool $parse ���������� � true ���� ����� ������� �������� �������� � "������������" ������� "Firefox 3.6.18"
     *     ����� ������ user agent �������� ���������
     * @return string
     */
    function getBrowser($parse = false){
        if(!$parse) return $_SERVER['HTTP_USER_AGENT'];
        $out = '';
        if ( stristr($_SERVER['HTTP_USER_AGENT'], 'Firefox') ) {
            $out .= 'Firefox ';
            if(preg_match("/(firefox|version)\/([0-9]+\.[0-9]+)/i", $_SERVER['HTTP_USER_AGENT'],$res)) $out .= 'v.'.$res[2];
        }
        elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'Chrome') ) {
            $out .= 'Coogle Chrome ';
            if(preg_match("/(chrome|version)\/([0-9]+\.[0-9]+)/i", $_SERVER['HTTP_USER_AGENT'],$res)) $out .=  'v.'.$res[2];

        }
        elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'Safari') ) {
            $out .= 'Safari ';
            if(preg_match("/(safari|version)\/([0-9]+\.[0-9]+)/i", $_SERVER['HTTP_USER_AGENT'],$res)) $out .=  'v.'.$res[2];
        }
        elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'Opera') ) {
            $out .= 'Opera ';
            if(preg_match("/(opera|version)\/([0-9]+\.[0-9]+)/i", $_SERVER['HTTP_USER_AGENT'],$res)) $out .=  'v.'.$res[2];
        }
        elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0') ) {
            $out .= 'Internet Explorer 6';
        }
        elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0') ) {
            $out .= 'Internet Explorer 7';
        }
        elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0') ) {
            $out .= 'Internet Explorer 8';
        }
        elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 9.0') ) {
            $out .= 'Internet Explorer 9';
        }
        return trim(ucfirst($out));
     }

    /**
     * ���������� ����� ������������ ������� ���������� ������������.
     * 
     * @return string
     */
     function getOs(){
         $oses = array (
        'Windows 3.11' => 'Win16',
        'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
        'Windows 98' => '(Windows 98)|(Win98)',
        'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
        'Windows XP' => '(Windows NT 5.1)|(Windows XP)',
        'Windows 2003' => '(Windows NT 5.2)',
        'Windows Vista' => 'Windows NT 6.0',
        'Windows 7' => 'Windows NT 6.1',
        'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
        'Windows ME' => 'Windows ME',
        'Open BSD'=>'OpenBSD',
        'Sun OS'=>'SunOS',
        'Linux'=>'(Linux)|(X11)',
        'Macintosh'=>'(Mac_PowerPC)|(Macintosh)',
        'QNX'=>'QNX',
        'BeOS'=>'BeOS',
        'OS/2'=>'OS/2',
        'Search Bot'=>'(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp/cat)|(msnbot)|(ia_archiver)'
    );

    foreach($oses as $os=>$pattern)
    {
        if (eregi($pattern, $_SERVER['HTTP_USER_AGENT']))
            return $os;
    }
    return 'Unknown';
}

/**
 * ���������� HTML ��� ���������� �����
 *
 * @param  bool $is_pro �������� �� ������� ������������ PRO
 * @param  bool $show_facebook ���������� � true ���� ����� ���������� ������ facebook
 * @param  bool $show_banner ���������� �� ������
 * @return string
 */
function printBanner240($is_pro, $show_facebook = false, $show_banner = true)
{
    ob_start();
    include $_SERVER['DOCUMENT_ROOT'].'/templates/banners/banner240.php';
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}

/**
 * ������ ������
 *
 * @param  string $partial_template_path ���� � �������
 * @param  array $variables ������������� ������ � ����������� �������
 * @return string
 */
function partialRender($partial_template_path, $variables = null){
    if(!empty($variables) && is_array($variables)){
        extract($variables, EXTR_OVERWRITE);
    }
    ob_start();
    include($partial_template_path);
    $partial_html_result = ob_get_contents();
    ob_end_clean();
    return $partial_html_result;
}

/**
 * ��������� �������� �� ������ "������"
 * 
 * @return bool true - ������ "������", false- ���������, ����, �����
 */
function is_release() {
    return (SERVER==='release');
}

/**
 * ��������� �������� �� ������ "���������"
 * 
 * @return bool true - ������ "���������", false- ����, �����, ������
 */
function is_local() {
    return (SERVER==='LOCAL');
}


/**
 * ��������� �������� �� c����� "beta"
 * 
 * @return bool
 */
function is_beta() 
{
    return (SERVER === 'beta');
}





/**
 * ��������� �������� �����
 * 
 * @param  string $string �������� �����
 * @param  int $length ������ �����
 * @param  string $etc ��� ��������� � ����� ����������� ��������
 * @return string
 */
function CutFileName($string, $length, $etc = "..."){
    $string = trim($string);
    if ($length == 0) return $string;
    $ext = trim('.'.substr(strrchr($string,'.'),1));
    $etc .= $ext;
    $fn = substr($string, 0, strlen($string) - strlen($ext));
    $cut_length = $length - strlen(trim($etc));
    if (strlen($fn)+strlen($ext) >= $length) {
        return substr($fn, 0, $cut_length).$etc;
    } else
    return $string;
}


/**
 * ���� � �������� ���������� �����(iframe)
 * 
 * @param  int $id ID ����, ��� �� �����������
 * @param  string $title �������� ������ ��� ������ ������
 * @param  string $from ��� �� ����������� ��� ����������� �������������� ����������
 * @param  string $img ����� �������� ������� ����� �������������� ��� ������ ������, ���� ������� FL
 * @param  string $login ����� ������
 * @param  string $name ��� � ������� ������ ������
 * @param  string $gr_name ��� ������ �����
 * @return string HTML-���
 */
function SocialButtons( $id = 0, $title = '', $from = '', $img = '', $login = '', $name = '', $gr_name = '' ) {
    if ( $login ) {
        $login  = urlencode( $login );
        $login = "&login=" . $login;
    }
    if ( $name ) {
        $name  = urlencode( $name );
        $name = "&name=" . $name;
    }
    if ( $gr_name ) {
        $gr_name  = urlencode( $gr_name );
        $gr_name = "&gr_name=" . $gr_name;
    }
    $title = htmlspecialchars_decode($title, ENT_QUOTES);
    $title  = urlencode( $title );
    $img    = urlencode( $img );
    if(is_array($id)) {
        $id_href = array();
        foreach($id as $k=>$v) {
            $id_href[] = "id[]=$v";
        }
        $id_href = implode("&", $id_href);
    } else {
        $id_href = "id={$id}";
    }
    
    switch($from) {
        case "commune_topic":
            $html   = "<iframe id='social-btns-frame' src='/share.php?{$id_href}&img={$img}&title={$title}&from={$from}{$login}{$name}' scrolling='no' frameborder='0' style='border:none; overflow:hidden; height: 25px;' allowTransparency='true'></iframe>";
            break;
        default:
            $html   = "<iframe id='social-btns-frame' src='/share.php?{$id_href}&img={$img}&title={$title}&from={$from}{$login}{$name}{$gr_name}' scrolling='no' frameborder='0' style='border:none; overflow:hidden; height: 25px;width:155px;' allowTransparency='true'></iframe>";
            $html   = "<div class='yashare-block'>{$html}</div>";
            break;
    }
    
    return $html;
}

/**
 * ����� ���������� ������ #0018624
 * @param type $page    �������� ��� ���������
 * @param type $title   ���������
 * @param type $fb      ����� �� ���� FaceBook
 * @param type $vb
 * @param string $image ������ �� �����������
 * 
 * ��� $page == 'project'
 * @param string $descr ����� �������
 * @param int $id ID �������
 * 
 * @return string 
 */
function ViewSocialButtons($page = 'all', $title = '', $fb = false, $vb=false, $descr = null, $id = null, $image = null) {
    $url = $GLOBALS['host'] . $_SERVER['REQUEST_URI'];
    $autoInit = " yashare-auto-init";
    switch($page) {
        case 'project':
            $descr = str_replace( array("&#039;"), array("\\'"), $descr);
            $descr = str_replace("\r", ' ', $descr);
            $descr = str_replace(PHP_EOL, ' ', $descr);
            $full_descr = substr(strip_tags($descr), 0, 100);
            // ������ 95 �������� �������� �������
            $descr = substr(strip_tags($descr), 0, 95);
            // ������� �������� �����
            $readMore = "<a href=\"$url\">������ ������...</a>";
            // �������� + ������
            $prjDescr = addslashes($descr . " - $readMore");
            // �������� URL ��� �������. �� ������ ����� ��� "https://www.free-lance.ru/projects/1289696" - ��� 42 ������� (��� �������� �������� 98)
            $shortURL = $GLOBALS['host'] . "/projects/" . $id;
            $title = $descr;
            $autoInit = "";
        case 'all':
        case 'commune':
        case 'commune_topic':
        case 'viewproj':
        case 'project':
        case 'blog':
            $dataImage = $image ? ' data-yashareImage="' . $image . '"' : '';
            $dataLink = ' data-yashareLink="' . $GLOBALS['host'] . $_SERVER['REQUEST_URI'] . '"';
            $html = '<div class="b-free-share__icons b-free-share__icons_float_right"> 
						  <span class="b-free-share__txt b-free-share__txt_top_7">����������:</span>
						  <span id="yashare" class="b-free-share__yashare' . $autoInit . '" data-yashareL10n="ru" data-yashareType="none" data-yashareQuickServices="yaru,vkontakte,facebook,twitter,lj,gplus,blogger" data-yashareTitle="'.$title.'"' . $dataImage . $dataLink . '></span><span
						   class="b-free-share__pinterest"><a href="http://pinterest.com/pin/create/button/?url='.urlencode($url).'&description='.urlencode(iconv( 'CP1251', 'UTF-8', substr(strip_tags($descr), 0, 400))).'" class="pin-it-button" count-layout="none" target="_blank"></a></span>';
            $html .= '</div>';
			if($vb && get_uid(false) && BLOGS_CLOSED == false) {
                $url_blog = HTTP_PREFIX . $_SERVER['HTTP_HOST']. ( isset($_SERVER['HTTP_ORIGINAL_URI']) ? $_SERVER['HTTP_ORIGINAL_URI'] : $_SERVER['REQUEST_URI'] );
                $html .= '<a class="b-free-share__blog b-free-share__blog_margtop_3" target="_blank" href="'.$url_blog.'#buttom"></a>';
            }
            $html .= '<div class="b-free-share__like b-free-share__like_padleft_10">
						  <div id="fb-root"></div>
						  <div class="fb-like" data-href="'.urlencode($url).'" data-send="false" data-layout="button_count" data-width="150" data-show-faces="false"></div>';
            break;
            
        case 'small_block':
            $html = '<div class="b-free-share__icons "> 
						  <span class="b-free-share__txt">���������� � ���������� �����:</span>
						  <span class="b-free-share__yashare yashare-auto-init" data-yashareL10n="ru" data-yashareType="none" data-yashareQuickServices="yaru,vkontakte,facebook,twitter,lj,gplus,blogger" data-yashareTitle="'.$title.'"></span><span
						   class="b-free-share__pinterest"><a href="http://pinterest.com/pin/create/button/?url='.urlencode($url).'&description='.urlencode($title).'" class="pin-it-button" count-layout="none" target="_blank"></a></span>';
			//if($vb && get_uid(false)) {
            //    $url_blog = HTTP_PREFIX . $_SERVER['HTTP_HOST']. ( isset($_SERVER['HTTP_ORIGINAL_URI']) ? $_SERVER['HTTP_ORIGINAL_URI'] : $_SERVER['REQUEST_URI'] );
            //    $html .= '<a class="b-free-share__blog b-free-share__blog_margtop_3" target="_blank" href="'.$url_blog.'#buttom"></a>';
            //}
            //$html .= '<div class="b-free-share__like b-free-share__like_padleft_10">
			//			  <div id="fb-root"></div>
			//			  <div class="fb-like" data-href="'.$url.'" data-send="false" data-layout="button_count" data-width="150" data-show-faces="false"></div>
			//		  </div>';
            break;
        /**
         * @deprecated 
         */
        case 'blog':
            $html  = '<div class="b-free-share">';
            $html .= '<div class="b-free-share__body b-free-share__body_padtop_10 b-free-share__body_align_right b-free-share__body_padbot_10 b-free-share__body_padleft_19">';
            $html .= '  <span class="b-free-share__txt b-free-share__txt_top_7 b-free-share__txt_bold b-free-share__txt_float_left">���������� � ���������� �����:</span>
                        <span class="b-free-share__yashare yashare-auto-init" data-yashareL10n="ru" data-yashareType="none" data-yashareQuickServices="yaru,vkontakte,facebook,twitter,lj,gplus,blogger" data-yashareTitle="'.$title.'"></span><a
                         class="b-free-share__pinterest" href="http://pinterest.com/pin/create/button/?url='.urlencode($url).'&description='.urlencode($title).'" target="_blank"></a>
                        <div class="b-free-share__like b-free-share__like_padleft_50">
                            <div id="fb-root"></div>
                            <div class="fb-like" data-href="'.urlencode($url).'" data-send="false" data-layout="button_count" data-width="150" data-show-faces="false"></div>					
                        </div>';
            $html .= '</div>';
            break;
        /**
         * @deprecated 
         */
        case 'viewproj':
            $html  = '<div class="b-free-share ">';
			$html .= '<div class="b-free-share__body b-free-share__body_align_right b-free-share__body_padright_10">
					      <span class="b-free-share__yashare yashare-auto-init" data-yashareL10n="ru" data-yashareType="none" data-yashareQuickServices="yaru,vkontakte,facebook,twitter,lj,gplus,blogger" data-yashareTitle="'.$title.'"></span><a
                           class="b-free-share__pinterest" href="http://pinterest.com/pin/create/button/?url='.urlencode($url).'&description='.urlencode($title).'" target="_blank"></a>
                      </div>';
            break;
        /**
         * @deprecated 
         */
        case 'commune_topic':
            $html  = '<div class="b-free-share ">';
			$html .= '<div class="b-free-share__body b-free-share__body_padtop_10 b-free-share__body_padbot_10 b-free-share__body_align_right">
                          <span class="b-free-share__txt b-free-share__txt_top_7  b-free-share__txt_float_left">���������� � ���������� �����:</span>
                          <span class="b-free-share__yashare yashare-auto-init" data-yashareL10n="ru" data-yashareType="none" data-yashareQuickServices="yaru,vkontakte,facebook,twitter,lj,gplus,blogger" data-yashareTitle="'.$title.'"></span><a
                           class="b-free-share__pinterest" href="http://pinterest.com/pin/create/button/?url='.urlencode($url).'&description='.urlencode($title).'" target="_blank"></a>
                      </div>';
            break;
        /**
         * @deprecated 
         */
        case 'commune':
            $html  = '<div class="b-free-share ">';
			$html .= '<div class="b-free-share__body ">
					      <span class="b-free-share__yashare yashare-auto-init" data-yashareL10n="ru" data-yashareType="none" data-yashareQuickServices="yaru,vkontakte,facebook,twitter,lj,gplus,blogger" data-yashareTitle="'.$title.'"></span><a
                           class="b-free-share__pinterest" href="http://pinterest.com/pin/create/button/?url='.urlencode($url).'&description='.urlencode($title).'" target="_blank"></a>
                      </div>';
            break;
        
        default:
            $html  = '<div class="b-free-share">';
			$html .= '<div class="b-free-share__body b-free-share__body_padtop_10 b-free-share__body_padbot_10 b-free-share__body_padleft_19 b-free-share__body_padright_19">
						<div class="b-free-share__icons b-free-share__icons_float_right"> 
                          <span class="b-free-share__txt b-free-share__txt_top_7">���������� � ���������� �����:</span>
                          <span class="b-free-share__yashare yashare-auto-init" data-yashareL10n="ru" data-yashareType="none" data-yashareQuickServices="yaru,vkontakte,facebook,twitter,lj,gplus,blogger" data-yashareTitle="'.$title.'"></span><a
                           class="b-free-share__pinterest" href="http://pinterest.com/pin/create/button/?url='.urlencode($url).'&description='.urlencode($title).'" target="_blank"></a>
					  	</div>';
            if($fb) {
                $html .= '<div class="b-free-share__like">
                            <div id="fb-root"></div>
                            <div class="fb-like" data-href="'.urlencode($url).'" data-send="false" data-layout="button_count" data-width="150" data-show-faces="false"></div>					
                          </div>';
            }
            $html .= '</div>';
            break;
    }
    
    $html .= '<script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script>';
    //$html .= '<script type="text/javascript" src="http://assets.pinterest.com/js/pinit.js"></script>';
    
    switch ($page) {
        case 'project':
            $html .= "<script type='text/javascript'>
                var YaShareInstance = new Ya.share({
                    element: 'yashare',
                    link: '" . addslashes($url) . "',
                    /*description: '" . $prjDescr . "',*/
                    serviceSpecific: {
                        vkontakte: {
                            /*title: '". htmlspecialchars( $title, ENT_NOQUOTES, 'cp1251', false ) ."',*/
                            title: '" . $full_descr . "'
                        },
                        blogger: {
                            title: '". $descr ." ������ ������...'
                        },
                        twitter: {
                            title: '" . str_replace("/", '\/', addslashes(  htmlspecialchars_decode($descr, ENT_QUOTES)) ) . "',
                            link: '" . $shortURL . "'
                        },
                        yaru: {
                            title: '" . str_replace(array('"', "'", '<', '>'), '', htmlspecialchars_decode($full_descr, ENT_QUOTES)) . " ������ ������...'
                        },
                        lj: {
                            link: '" . $prjDescr . "'
                        },
                        blogger: {
                            description: '" . $full_descr . "'
                        }
                    },
                    elementStyle: {
                        type: 'none',
                        quickServices: ['yaru','vkontakte','facebook','twitter','lj','gplus','blogger','pinterest']
                    }
                });
            </script>";            
            break;
        
        case 'viewproj':
            
            break;
    }
    
    if($fb) {
        $html .= '<script type="text/javascript">
                        (function(d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (d.getElementById(id)) return;
                        js = d.createElement(s); js.id = id;
                        js.src = "//connect.facebook.net/ru_RU/all.js#xfbml=1";
                        fjs.parentNode.insertBefore(js, fjs);
                        }(document, \'script\', \'facebook-jssdk\'));
                </script>';
    }
    $html .= '</div>';
    
    return $html;
}

/**
 * ���� � �������� ���������� �����
 * 
 * @param  int $id ID ����, ��� �� �����������
 * @param  string $title �������� ������ ��� ������ ������, ���� document.title
 * @param  string $from ��� �� ����������� ��� ����������� �������������� ����������
 * @param  string $img ����� �������� ������� ����� �������������� ��� ������ ������, ���� ������� FL
 * @param  string $login ����� ������
 * @param  string $name ��� � ������� ������ ������
 * @param  string $gr_name ��� ������ �����
 * @return string HTML-���
 */
function SocialButtonsSrc( $id = 0, $title = '', $img = '', $from = '', $login = '', $name = '', $gr_name = '' ) {
    $titleYaru = $title;
    $title   = html_entity_decode( $title, ENT_QUOTES );
    $gr_name   = html_entity_decode( $gr_name );
    $sClass  = '';
    $nLength = 116;
    
    switch ( $from ) {
    	case 'blogs':
    	    $link          = "'".HTTP_PREFIX."www.free-lance.ru".getFriendlyURL("blog", $id)."'";
    		$sTwitterTitle = '���������� '.($title ? '�'.LenghtFormatEx( $title, $nLength - 36, '...', true ).'� ' : '').'� ������ @free_lanceru';
    		break;
        case 'commune':
            $link          = "'".HTTP_PREFIX."www.free-lance.ru/commune/?id={$id}'";
    		$sTwitterTitle = '���������� �'.LenghtFormatEx( $title, $nLength - 31, '...', true ).'� �� @free_lanceru';
    		break;
    	case 'commune_topic':
            $link          = "'".HTTP_PREFIX."www.free-lance.ru/commune/?id={$id[0]}&site=Topic&post={$id[1]}'";
    		$sTwitterTitle = '���������� '.($title ? '�'.LenghtFormatEx( $title, $nLength - 31, '...', true ).'� ' : '' ).'� ����������� @free_lanceru';
    		break;	
        case 'projects':
            $link          = "'".HTTP_PREFIX."www.free-lance.ru/projects/{$id}'";
    		$sTwitterTitle = '������ �'.LenghtFormatEx( $title, $nLength - 27, '...', true ).'� �� @free_lanceru';
    		break;
        case 'viewproj':
            $link          = "'".HTTP_PREFIX."www.free-lance.ru/users/{$login}/viewproj.php?prjid={$id}'";
    		$sTwitterTitle = '������ �'.LenghtFormatEx( $title, $nLength - 52, '...', true )."� � ��������� ���������� �� @free_lanceru. {$name} [{$login}]";
    		
    		if ( $img ) {
    		    $aData = getAttachDisplayData( $login, $img, "upload", -1, -1, 1048576, 0 );
    		    
    		    if ( $aData['success'] && !$aData['file_mode'] && !$aData['virus_flag'] && strtolower($aData['file_ext']) != 'swf' ) {
    		    	$img = WDCPREFIX."/users/$login/upload/$img";
    		    }
    		    else {
                    $img = '';
    		    }
    		}
    		break;
        case 'articles':
            $link          = "'".HTTP_PREFIX."www.free-lance.ru/articles/?id={$id}'";
    		$sTwitterTitle = '������ �'.LenghtFormatEx( $title, $nLength - 27, '...', true ).'� �� @free_lanceru';
    		$sClass        = 'articles-share';
    		break;
        case 'interview':
            $link          = "'".HTTP_PREFIX."www.free-lance.ru/interview/?id={$id}'";
    		$sTwitterTitle = '�������� �������� ����������� � ������������� �� @free_lanceru. '.LenghtFormatEx( $title, $nLength - 66, '...', true );
    		$sClass        = 'interview-share';
    		break;
    	default:
    	    $link          = 'window.parent.location.href';
    		$sTwitterTitle = LenghtFormatEx( $title, $nLength, '...', true );
    		break;
    }
    //******************************
    // �������� ������ ������� �� ��� ��������� �������
    $titleYaru = preg_replace('/\\\&quot;/', '��', $titleYaru);
    // �������� ��������� �� ��������� �������
    $titleYaru = preg_replace('/\\\&#039;/', '�', $titleYaru);
    //*******************************
    $img   = $img ? $img : HTTP_PREFIX."www.free-lance.ru/images/free-lance_logo.jpg";
    $html  = "<script type='text/javascript' src='//yandex.st/share/share.js' charset='utf-8'></script>";
    $html .= "<script type='text/javascript'>
              var YaShareInstance = new Ya.share({
			    element: 'yashare',
			    link: {$link},
			    title: '".strip_tags($title)."',
                image: '{$img}',
                serviceSpecific: {
                    vkontakte: {
                        title: '". htmlspecialchars( $title, ENT_NOQUOTES, 'cp1251', false ) ."'
                    },
                    facebook: {
                        title: '". strip_tags($title)."'
                    },
                    twitter: {
                        title: '".strip_tags($sTwitterTitle)."'
                    },
                    yaru: {
                        title: '".$titleYaru."'
                    }
                },
        		elementStyle: {
        		    type: 'none',
                    quickServices: ['yaru','vkontakte','facebook','twitter','odnoklassniki','moimir','lj','friendfeed']
        		}
        	  });
        	  </script>";
    $html .= '<div id="yashare"'. ($sClass ? ' class="'.$sClass.'"' : '') .'"></div>';
    return $html;
}

/**
 * ������ Like ��� FB
 *
 * @param   string  $where  �� ����� �������� ���������� ������ Like: blog - �����, portfolio - ������ � ���������
 * @param   string  $url    URL ��������
 * @return  string          HTML-��� ������ Like FB
 */
function SocialFBLikeButton($where, $url='') {
    if(!$url) $url = $_SERVER["REQUEST_URI"];
    switch($where) {
        case 'articles':
            return '<div class="fb-like" data-href="'.urlencode($url).'" data-send="false" data-layout="button_count" data-show-faces="false"></div>';
        case 'blog':
            $params = "href=".urlencode($url)."&amp;layout=button_count&amp;show_faces=true&amp;width=450&amp;action=like&amp;font=tahoma&amp;colorscheme=light&amp;height=21";
            $styles = "width:150px; height:21px;";
            break;
        case 'portfolio':
            $params = "href=".urlencode($url)."&amp;show_faces=false&amp;action=like&amp;send=false&amp;layout=standard&amp;width=450&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;image=".urlencode(HTTP_PREFIX."www.free-lance/images/free-lance_logo.jpg");
            $styles = "width:450px; height:42px;";
            break;
        case 'commune':
            $params = "href=".urlencode($url)."&amp;layout=button_count&amp;show_faces=true&amp;width=450&amp;action=like&amp;font=tahoma&amp;colorscheme=light&amp;height=21";
            $style  = "width:150px; height:21px;";
            break;
    }
    $html = "<iframe src='//www.facebook.com/plugins/like.php?{$params}' scrolling='no' frameborder='0' style='border:none; overflow:hidden; {$styles}' allowTransparency='true'></iframe>";
    return $html;
}

/**
 * ��������� ����� �� ������������ ����������� ����� �������
 * 
 * @todo: ������ ������ ���� ���� ���! �����? - ����������
 * 
 * @param   string      ��� ����� �������
 * @param   integer     ID ������������
 * @return  boolean     true - �����, false - ���
 */
function hasPermissions($permission, $uid=0) 
{
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/permissions.php");
    $uid = intval($uid);
    
    if (!$uid) {
        $uid = get_uid(false);
    }
    
    //���� ������� ��� ��������� �� ���� � ���� ���!
    if ($uid <= 0) {
        return false;
    }
    
    if($uid!=$_SESSION['uid']) {
        $user_permissions = permissions::getUserPermissions($uid);
    } else {
        $user_permissions = $_SESSION['permissions'];
    }
    if(!$user_permissions) $user_permissions = array();
    return (in_array($permission,$user_permissions) || in_array('all',$user_permissions));
}

/**
 * ��������� ������ �� ������������ � ������ ���� �������
 *
 * @param   string      ��� ������(administrator - ��������������, moderator - ����������)
 * @param   integer     ID ������������
 * @return  boolean     true - ������, false - �� ������
 */
function hasGroupPermissions($group, $uid=0) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/permissions.php");
    if(!$uid) $uid = get_uid(false);
    return permissions::getUserGroupPermissions($uid,$group);
}

/**
 * ������ $additional_header ��� ������ � ����������� .css � .js ������
 *
 * @param  string|array $str ���������� $additional_header
 * @param  object $stc @see static_compress
 * @return string $str ������ $additional_header
 */
function parse_additional_header($str, $stc=false, $only = null) 
{
    if(!$stc) return $str;
    
    if (is_array($str) && count($str)) {
        foreach ($str as $file) {
            $stc->Add($file);  
        }
        return;
    }
    
    $is_link   = (!$only || $only == 'css') && preg_match_all('/href\s*=\s*["\']([^"\']+\.css)/i', $str, $css);
    $is_script = (!$only || $only == 'js') && preg_match_all('/src\s*=\s*["\']([^"\']+\.js)/i', $str, $script);
    $is_php_script = (!$only || $only == 'js') && preg_match_all('/src\s*=\s*["\']([^"\']+\.php.*?)"/i', $str, $php_script);
    
    if($is_link) {
        foreach($css[1] as $css_file) 
            $stc->Add($css_file);   
    }
            
    if($is_script) {
        foreach($script[1] as $script_file) 
            $stc->Add($script_file); 
    }
    
    if($is_php_script) {
        foreach($php_script[1] as $script_file) 
            $stc->Add($script_file); 
    }
            
    // ������ ������ ��� �� ����� � ���        
    $str = preg_replace('/<link[^>]+\.css[^>]*>(?:\s*<\/link>)?/i', '', $str);
    $str = preg_replace('/<script[^>]+\.js[^>]*>(?:\s*<\/script>)?/i', '', $str);
    $str = preg_replace('/<script[^>]+\.php[^>]*>(?:\s*<\/script>)?/i', '', $str);
    
    return $str;
}

/**
 * �������� �� �������� � ������� �������� � JS (����. Opera Mini)
 *
 * @param string|boolean $user_agent ����� ������������
 * @return boolean true - ���� ��� ����� �������, ����� false
 */
function isJSPromlebBrowser($user_agent = false) {
    global $JSProblemBrowser;
    
    if(!$user_agent) $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    foreach($JSProblemBrowser as $name=>$rule) {
        if(preg_match($rule, $user_agent)) {
            return true;
        }
    }
    
    return false;
}

/**
 * ���������� �������� ������ ������� ������������
 *
 * @param unknown_type $status_type
 */
function getStatusUserCSS($status_type) {
    switch ($status_type) {
		case 1:
			$status_cls = 'b-status_busy';
			break;
		case 2:
			$status_cls = 'b-status_abs';
			break;
		case -1:
			$status_cls = 'b-status_no';
			break;
		default:
			$status_cls = 'b-status_free';
    }
    
    return $status_cls;
}

/**
 * ��� ������� � ��������
 *
 * @param string $priceby ���
 */
function getPricebyProject($priceby) {
    switch($priceby) {
        case '1':
            $priceby_str = "/���";
            break;
        case '2':
            $priceby_str = "/����";
            break;
        case '3':
            $priceby_str = "/�����";
            break;
        case '4':
            $priceby_str = "/������";
            break;
        default:
            $priceby_str = "";
            break;
    }
    
    return $priceby_str;
}

/**
 * ������� ������� �����
 *
 * @param string $string  ����� �� �������� �������
 * @return string
 */
function deleteHiddenURLFacebook($string) {
    return preg_replace("/:\/\{.*?\}/mix", ":/", $string);
}

/**
 * ������ ����� �� ���� ��������� ��� ������ ����� JS
 *
 * @param string $text    �����
 */
function clearTextForJS($text) {
    // �������� ��� �������� � ����� code ������ �� ����������
    $str = "@_".mt_rand(10000, 99999)."_@";
    $text1 = $text;
    $text2 = "";
    while ($text1 !== $text2) {
        $text2 = $text1;
        $text1 = preg_replace('/(<p class="code[^>]+>.*?)\n(.*?<\/p>)/s', "$1$str$2", $text2);
    }
    $text = $text1;
    
    $text = trim(str_replace(array("\r", "\n", '</script>'), array('', '', '<\/script>'), addexcslashes($text, '"')));
    $text = preg_replace("/\s+/", " ", $text);
    // ���������� �������� � code � ���� <br />
    $text = str_replace($str, "<br />", $text);
    
    return $text;
}

function seo_start($is_ajax = false) {
    //if(!isset($_GET['seo'])) return false;
    if(!$is_ajax) {
        ob_start();
    }
}

function seo_end($bhtml = false, $is_ajax=false) {
    //if(!isset($_GET['seo'])) return false;
    if($is_ajax) return ""; 
    if(!$bhtml) {
        $bhtml = clearTextForJS(ob_get_contents());
        ob_end_clean();
    } else {
        $bhtml = clearTextForJS($bhtml);
    }
    return "<script type=\"text/javascript\">document.write('{$bhtml}');</script>";
}

/**
 * ������ ���������� GET � POST
 * ������� ������� � ������ � �����, ������������ ����� � "������" ��������
 * 
 * @param string $text �����
 */
function clearInputText( $text ) {
    $text = trim( $text );
    
    if ( get_magic_quotes_runtime() || get_magic_quotes_gpc() ) {
        $text = stripslashes( $text );
    }
    
    $text = iconv( 'CP1251', 'UTF-8', $text );
    $text = str_replace( "\xC2\xAD", '', $text );
    $text = iconv( 'UTF-8', 'CP1251', $text );
    
    return $text;
}

/**
 * ������� ��� ������� ����� � ������� ������� ������ ���� ��� "\r\n" � ��������� ��� ��� �� 2 �������,
 * � �� ����� ��� � JS ������� 1 ������ @see http://beta.free-lance.ru/mantis/view.php?id=13496
 *
 * @param string $string    ������
 * @return integer
 */
function strlen_real($string) {
    return strlen(str_replace("\r", "", stripslashes(html_entity_decode($string, ENT_QUOTES))));
}

function translit($str) {
    $tr = array(
        "�"=>"a","�"=>"b","�"=>"v","�"=>"g",
        "�"=>"d","�"=>"e","�"=>"j","�"=>"z","�"=>"i",
        "�"=>"y","�"=>"k","�"=>"l","�"=>"m","�"=>"n",
        "�"=>"o","�"=>"p","�"=>"r","�"=>"s","�"=>"t",
        "�"=>"u","�"=>"f","�"=>"h","�"=>"ts","�"=>"ch",
        "�"=>"sh","�"=>"sch","�"=>"","�"=>"yi","�"=>"",
        "�"=>"e","�"=>"yu","�"=>"ya","�"=>"a","�"=>"b",
        "�"=>"v","�"=>"g","�"=>"d","�"=>"e","�"=>"j",
        "�"=>"z","�"=>"i","�"=>"y","�"=>"k","�"=>"l",
        "�"=>"m","�"=>"n","�"=>"o","�"=>"p","�"=>"r",
        "�"=>"s","�"=>"t","�"=>"u","�"=>"f","�"=>"h",
        "�"=>"ts","�"=>"ch","�"=>"sh","�"=>"sch","�"=>"y",
        "�"=>"yi","�"=>"","�"=>"e","�"=>"yu","�"=>"ya", 
        " "=> "-", "."=> "", "/"=> "_"
    );
    
    return preg_replace('/[^A-Za-z0-9_\-]/', '', strtr($str,$tr));
}


/**
 * �������!
 * ��������� ����� �������� ������ ����� ajax 
 * ��� ��������� ������������, �� ������ � ������� mb_convert_encoding($string, 'HTML-ENTITIES', $charset); �� �������� 
 * �� ������� ������ �������� ������ HTML-����������
 * 
 * @param string $string      ������ � ������� ���� �������� �����������, ����������
 * @param string $charset     ��������� � ������� ��������� ���� ������
 * @return string
 */
function _htmlentities($string, $charset='cp1251') {
    $tr = array(
        "�"=>"a","�"=>"b","�"=>"v","�"=>"g",
        "�"=>"d","�"=>"e","�"=>"j","�"=>"z","�"=>"i",
        "�"=>"y","�"=>"k","�"=>"l","�"=>"m","�"=>"n",
        "�"=>"o","�"=>"p","�"=>"r","�"=>"s","�"=>"t",
        "�"=>"u","�"=>"f","�"=>"h","�"=>"ts","�"=>"ch",
        "�"=>"sh","�"=>"sch","�"=>"","�"=>"yi","�"=>"",
        "�"=>"e","�"=>"yu","�"=>"ya","�"=>"a","�"=>"b",
        "�"=>"v","�"=>"g","�"=>"d","�"=>"e","�"=>"j",
        "�"=>"z","�"=>"i","�"=>"y","�"=>"k","�"=>"l",
        "�"=>"m","�"=>"n","�"=>"o","�"=>"p","�"=>"r",
        "�"=>"s","�"=>"t","�"=>"u","�"=>"f","�"=>"h",
        "�"=>"ts","�"=>"ch","�"=>"sh","�"=>"sch","�"=>"y",
        "�"=>"yi","�"=>"","�"=>"e","�"=>"yu","�"=>"ya"
    );
    
    $array_val = array_values($tr);
    $array_key = array_keys($tr);
    foreach($array_val as $k=>$val) {
        $array_val[$k] = "___{$val}_{$k}___"; // ������������ ����, ����� ������� ��� �������
    }
    
    if($charset != 'cp1251') {
        foreach($array_key as $k=>$val) {
            $array_key[$k] = iconv('cp1251', $charset, $val);
        }
    }
    
    foreach($array_key as $k=>$val) {
        $result_revert[$array_val[$k]] = $val;
        $result[$val] = $array_val[$k];
    }
    
    $string = strtr($string,$result);
    $string = mb_convert_encoding($string, 'HTML-ENTITIES', $charset);
    $string = strtr($string,$result_revert);
    
    return $string;
}

/**
 * �������� �� ���� ������� POST, GET ������ �� CSRF
 *
 */
function csrf_magic() {
    static $log;
    if(defined("NO_CSRF")) return true; // �� ���������� ���� ��������� �������� � ������� 
    if($_POST['u_token_key'] != $_SESSION['rand']) {
        if(!$log) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
            $log = new log('csrf/'.SERVER.'-%d%m%Y.log', 'a', 
                           '%d.%m.%Y %H:%M:%S - ' . getRemoteIP()
    		               . ' "' . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'] . '"'
    		               . ' "' . $_SERVER['HTTP_USER_AGENT'] . '"'
    		              );
        
        }
        $log->writeln(" post_key: {$_POST['u_token_key']}, session_key: {$_SESSION['rand']}");
        $_POST = array();
        $_REQUEST = array();
    }
}


/**
 * �������� ������ ��� ������ �� CSRF
 * ����� �������� ��� ����� ����� �����������
 * 
 * @return string  �����
 */
function csrf_token() {
    if ( !empty($_SESSION['rand']) ) {
        return $_SESSION['rand'];
    }
    if ( empty($_SESSION['uid']) ) {
        mt_srand();
        return md5(uniqid(mt_rand(), true));
    } 
    $user = new users;
    $user->GetUserByUID($_SESSION['uid']);
    if ( $user->solt ) {
        return md5(md5($user->email) . $user->solt) ;
    } else {
        mt_srand();
        return md5(uniqid(mt_rand(), true));
    }
}

/**
 * ���������� ����� � ������� ���������
 *
 */
function is_https() {
    return isset($_SERVER['HTTP_NGINX_HTTPS']);
}

/**
 * HTML ��� �� ������ "������� ������"
 *
 * @param    array    $data    ������ ��� ������: array(title, url). title - ����� ������, url - ������
 * @return   string            HTML ��� ������
 */
function getCrumbs($data, $style = "") {
    global $host;
    $html = '';
    if($data) {
        $count = count($data);
        switch ($style) {
            case "new_blogs":
                $html .= "<div class=\"b-menu b-menu_crumbs\">";
                $html .= "<ul class=\"b-menu__list\">";
                foreach($data as $item) {
                    if($item['title'] == '') {
                        $count--; 
                        continue;
                    }
                    $link = $item['url'] ? "<a class=\"b-menu__link\" href=\"{$item['url']}\">{$item['title']}</a>" : $item['title'];
                    $html .= "<li class=\"b-menu__item\">{$link}".($count==1 ? "" : "&nbsp;&#8594;&nbsp;")."</li>\n";
                    $count--;
                }
                $html .= "</ul>";
                $html .= "</div>";
                break;
            case "commune":
                $html .= "<div class=\"b-menu b-menu_crumbs\">";
                $html .= "<ul class=\"b-menu__list\">";
                foreach($data as $item) {
                    $link = $item['url'] ? "<a class=\"b-menu__link\" href=\"{$item['url']}\">{$item['title']}</a>" : $item['title'];
                    $html .= "<li class=\"b-menu__item\">{$link}".($count==1 ? "" : "&nbsp;&#8594;&nbsp;")."</li>\n";
                    $count--;
                }
                $html .= "</ul>";
                $html .= "</div>";
                break;
            case "freelancers":
                $html .= "<div class='b-menu b-menu_crumbs'  xmlns:v='http://rdf.data-vocabulary.org/#'><ul class=\"b-menu__list\">";
                foreach($data as $key=>$item) {
                    $link = $item['url'] ? "<a class=\"b-menu__link\" href=\"{$host}{$item['url']}\" rel=\"v:url\" property=\"v:title\">{$item['title']}</a>" : $item['title'];
                    $arrow = $count > 1 ? "&nbsp;&#8594;&nbsp;" : '';
                    $html .= "<li class=\"b-menu__item\"><span typeof=\"v:Breadcrumb\">{$link}" . $arrow . "</span></li>\n";
                    $count--;
                }
                $html .= "</ul></div>";
                break;
            case 'blogs':
            	$html .= "<ul class=\"b-menu__list\" xmlns:v=\"http://rdf.data-vocabulary.org/#\" style=\"padding-top:11px; padding-bottom:13px;\">";
                foreach($data as $item) {
                    $link = $item['url'] ? "<a class=\"b-menu__link b-menu__link_fontsize_22 b-menu__link_color_000\" href=\"{$item['url']}\" rel=\"v:url\" property=\"v:title\" style='display:inline'>{$item['title']}</a>" : "<h1 class=\"b-menu__title b-menu__title_fontsize_22\">".$item['title']."</h1>";
                    $html .= "<li class=\"b-menu__item b-menu__item_fontsize_22 \" typeof=\"v:Breadcrumb\">{$link}".($count==1 ? "" : "&nbsp;&#8594;&nbsp;")."</li>\n";
                    //$html .= ($count==1 ? "" : "<li>&rarr;</li>");
                    $count--;
                }
                $html .= "</ul>";                
                break;
            default:
            	$html .= "<ul class=\"b-menu__list\" xmlns:v=\"http://rdf.data-vocabulary.org/#\">";
                foreach($data as $item) {
                    $link = $item['url'] ? "<a class=\"b-menu__link b-menu__link_fontsize_22 b-menu__link_color_000\" href=\"{$item['url']}\" rel=\"v:url\" property=\"v:title\">{$item['title']}</a>" : "<h1 class=\"b-menu__title b-menu__title_fontsize_22\">".$item['title']."</h1>";
                    $html .= "<li class=\"b-menu__item b-menu__item_fontsize_22 \" typeof=\"v:Breadcrumb\">{$link}".($count==1 ? "" : "&nbsp;&#8594;&nbsp;")."</li>\n";
                    //$html .= ($count==1 ? "" : "<li>&rarr;</li>");
                    $count--;
                }
                $html .= "</ul>";                
                break;
        } 
        
    }
    return $html;
}

/**
 * ���������� ��� ����������� ������� � ������ �������� ��������.
 *
 * @param string $str   �������� ������.
 * @param string $chars   ����� ��������, ������� ����� ������������.
 * @param string $escape_char   ����� �������� ������������.
 *
 * @return string
 */
function str_escape($str, $chars, $escape_char) {
    return preg_replace('/[' . preg_quote($chars, '/') . ']/', $escape_char.'$0', $str);
}

/**
 * ���������� ��� ������
 *
 * @param    string    $type    ��� ������(project, blog � �.�.)
 * @param    integer|array   $data      ��������� ��� ������. ���� �����, �� id ������� � ��, ����� ������� ������ $data (��. ������).
 * @return   string             ��� ������
 */
function getFriendlyURL($type, $data = NULL) {
    static $url_cache = array();
    $url = '';
    if(!is_array($data)) {
        $id = intval($data);
        $data = NULL;
    } else {
        $id = intval($data['id']);
    }
    if(!$id) {
        return NULL;
    }
    if($url_cache[$type][$id]) {
        return $url_cache[$type][$id];
    }
    switch($type) {
        case 'project':
            if(!$data) {
                require_once($_SERVER['DOCUMENT_ROOT']."/classes/projects.php");
                $data = projects::getInfoForFriendlyURL($id);
            }
            if($data) {
                $name = translit(strtolower(htmlspecialchars_decode($data['name'], ENT_QUOTES)));
                $url = "/projects/{$id}/".($name ? "{$name}.html" : "");
            }
            break;
        case 'blog':
            require_once($_SERVER['DOCUMENT_ROOT']."/classes/blogs.php");
            $data = blogs::getMsgInfoForFriendlyURL($id);
            if($data) {
                $name = translit(strtolower(htmlspecialchars_decode($data['name'], ENT_QUOTES)));
                $category = translit(strtolower(htmlspecialchars_decode($data['category'], ENT_QUOTES)));
                $url = "/blogs/{$category}/{$id}/".($name ? $name.".html" : "");
            }
            break;
        case 'blog_group':
            require_once($_SERVER['DOCUMENT_ROOT']."/classes/blogs.php");
            $data = blogs::GetGroupName($id);
            if($data) {
                $category = translit(strtolower(htmlspecialchars_decode($data, ENT_QUOTES)));
                $url = "/blogs/".($category ? "{$category}/" : "");
            }
            break;
        case 'article':
            require_once($_SERVER['DOCUMENT_ROOT']."/classes/articles.php");
            $data = articles::getInfoForFriendlyURL($id);
            if($data) {
                $name = translit(strtolower(htmlspecialchars_decode($data['title'], ENT_QUOTES)));
                $url = "/articles/{$id}/".($name ? $name.".html" : ""); 
            }
            break;
        case 'interview':
            require_once($_SERVER['DOCUMENT_ROOT']."/classes/interview.php");
            $data = interview::getInfoForFriendlyURL($id);
            if($data) {
                $name = translit(strtolower(htmlspecialchars_decode($data['uname'].' '.$data['usurname'].' '.$data['login'], ENT_QUOTES)));
                $url = "/interview/{$id}/".($name ? $name.".html" : ""); 
            }
            break;
        case 'commune':
            require_once($_SERVER['DOCUMENT_ROOT']."/classes/commune.php");
            $data = commune::getMsgInfoForFriendlyURL($id);
            if($data) {
                $category = translit(strtolower(htmlspecialchars_decode($data['group_link'], ENT_QUOTES)));
                $commune = translit(strtolower(htmlspecialchars_decode($data['commune_name'], ENT_QUOTES)));
                $name = translit(strtolower(htmlspecialchars_decode($data['name'], ENT_QUOTES)));
                $commune_id = $data['commune_id'];
                $url = "/commune/{$category}/{$commune_id}/{$commune}/{$id}/".($name ? $name.".html" : "");
            }
            break;
        case 'commune_group':
            require_once($_SERVER['DOCUMENT_ROOT']."/classes/commune.php");
            $data = commune::getGroupInfoForFriendlyURL($id);
            if($data) {
                $category = translit(strtolower(htmlspecialchars_decode($data['link'], ENT_QUOTES)));
                $url = "/commune/{$category}/";
            }
            break;
        case 'commune_commune':
            require_once($_SERVER['DOCUMENT_ROOT']."/classes/commune.php");
            $data = commune::getCommuneInfoForFriendlyURL($id);
            if($data) {
                $category = translit(strtolower(htmlspecialchars_decode($data['category_link'], ENT_QUOTES)));
                $commune = translit(strtolower(htmlspecialchars_decode($data['name'], ENT_QUOTES)));
                $commune = $commune ? $commune : 'commune';
                $url = "/commune/{$category}/{$id}/{$commune}/";
            }
            break;
    }
    if($url) {
        $url_cache[$type][$id] = $url;
    }
    return $url;
}


/**
 *  ��������� �� �������� �� ��� ��� � �� ���� �� ������ ������ � ������� ��������. 
 */
function checkProLast() {
    if( $_SESSION['pro_last'] && strtotime($_SESSION['pro_last']) < time() ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
        $pro_last = payed::ProLast($_SESSION['login']);
        $_SESSION['pro_last'] = $pro_last['is_freezed'] ? false : $pro_last['cnt'];
    }
}

/**
 * ���������� HTML ��� ��� ������ ���������� ��������� �� ��������
 * 
 * @param  int $log_pp ���������� ��������� �� ��������
 * @param  string $page_seg ������� �������� ������ ���������� �� �����������
 * @return string HTML ��� ��� ������ ���������� ��������� �� ��������
 */
function printPerPageSelect( $log_pp, $page_seg = 'page' ) {
    $sHref = e_url( $page_seg, null );
    $sHref = e_url( 'log_pp', null, $sHref );
    $sHref = e_url( 'log_pp', '', $sHref );
    
    return '<div style="padding-top: 15px;">
        ���������� ��������� �� ��������:&nbsp;<select name="log_pp" id="log_pp" onChange="window.location=\'' . $sHref . '\'+this.value;">
            <option value="20" ' . ( $log_pp == 20 ? ' selected' : '' ) . '>20</option>
            <option value="50" ' . ( $log_pp == 50 ? ' selected' : '' ) . '>50</option>
            <option value="100" ' . ( $log_pp == 100 ? ' selected' : '' ) . '>100</option>
        </select>
        </div>';
}

function printMetaFBShare($share) {
    $html = "";
    if($share['title']) {
        $share['title'] = str_replace('"', "&quot;",$share['title']);
        $html .= "<meta property=\"og:title\" content=\"{$share['title']}\" />\n"; 
    }
    if($share['description']) {
        $share['description'] = str_replace('"', "&quot;",$share['title']);
        $html .= "<meta property=\"og:description\" content=\"{$share['description']}\" />\n"; 
    }
    if($share['image']) {
        $html .= "<meta property=\"og:image\" content=\"{$share['image']}\" />\n"; 
    }
    
    return $html;
}

/**
 * �� ��, ��� addslashes, �� ��������� �������, ����� �� �������� �� ������������.
 *
 * @param string $str   �������� ������
 * @param string|array $excepts   ������ ��� ������ �� ��������, ������� ���� ���������.
 * @return string
 */
function addexcslashes($str, $excepts = NULL) {
    $str = addslashes($str);
    if($excepts !== NULL) {
        if(!is_array($excepts)) {
            $excepts = str_split($excepts);
        }
        $str = str_replace(array_map('addslashes', $excepts), $excepts, $str);
    }
    return $str;
}

/**
 * ���������� http-������ ���������� ������ �� ���������� �������.
 * ��������, ����� file_get_contents('http://www.ya.ya');
 * 
 * @param array $headers   ������ ���������� � ����� $http_response_header ��� get_headers().
 * @return int
 */
function get_http_response_code($headers) {
    preg_match('~^HTTP/\d\.\d\s+(\d+)\s+~', $headers[0], $m);
    return (int)$m[1];
}

/**
 * ������� ������ � ��� ��� ������ ������ ������ ���� ���� �� ���������
 * 
 * @param string $descr ����� ������
 */
function view_hint_access_action( $descr = false , $class='') {
    //require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
    //$REG    = new registration();
    //if(!$REG->checkUserAccess(get_uid(false))) {
    //    $_SESSION['link_back'] = $_SERVER['REQUEST_URI'];
    //    if(!$descr) $descr = '����� ��������';
    //    echo "<div class='b-fon {$class}'><div class='b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb'><span class='b-fon__attent_red'></span>{$descr}, ��������� <a class='b-fon__link' href='/registration/info.php'>������ ����������</a>.</div></div><br/>";
    //}
}

/**
 *  �������� �������� �� ������� ������������
 */
function access_action_site($confirm = '') {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
    $reg    = new registration();
    $access = $reg->checkUserAccess();

    if(!$access) {
        $_SESSION['confirm_info'] = $confirm;
        header("Location: /registration/info.php");
        exit;
    }
}

/**
 * ����� file_get_contents � �������������� Authorization: Basic.
 * 
 * @param string $filename   ��� �����.
 * @param string $ba   ��������� basic authorization ("���:������")
 * @param int $flags   ��. file_get_contents()
 * @param int $offset  ��. file_get_contents()
 *
 * @return string ���������� �����.
 */
function file_get_contents_ba($filename, $ba, $flags = 0, $offset = -1) {
    if(strpos($ba, ':')) {
        $context = stream_context_create(
          array (
            'http' => array (
                'method' => 'GET',
                'header' => 'Authorization: Basic '.base64_encode($ba)."\r\n"
            )
          )
        );
    }
    
    return file_get_contents($filename, $flags, $context, $offset);
}

/**
 * ������� �� CR/LF ��� �������� ������ � HTTP header ������ �������
 *
 * @param    string    $in    ������
 * @return   string           ������ � ���������� \n, \r, %0d, %0a
 */
function clearCRLF($in) {
    return str_ireplace(array("\n", "\r", "%0D", "%0A"), array("", "", "", ""), $in);
}

function initContactData($name, $val, $own = true) {
    switch($name) {
        case 'site':
        case 'site_1':
        case 'site_2':
        case 'site_3':    
            if (!preg_match("/^[a-z]{3,5}\:\/\//", $val)) {
                $val = 'http://' . $val;
            }
            $value = $own ? reformat($val, 0, 0, 0, 0, 80) : '<noindex>' . reformat($val, 0, 0, 0, 0, 80) . '</noindex>';
            break;
        case 'second_email':
        case 'email_1':
        case 'email_2': 
        case 'email_3':
            $value = '<a href="mailto:' . $val . '" class="blue" rel="nofollow">' . $val . '</a>';
            break;
        case 'ljuser':
        case 'lj_1':
        case 'lj_2':
        case 'lj_3':
            $value = $own ? reformat('http://' . $val . '.livejournal.com', 0, 0, 0, 0, 80) : '<noindex><a href="http://' . $val . '.livejournal.com" target="_blank" class="blue" title="' . $val . '" rel="nofollow">' . $val . '.livejournal.com' . '</a></noindex>';
            break;
        default:
            $value = $val;
            break;
    }
    return $value;
}
/**
 * 
 * @param type $uid �� ������������ � �������� ������� ��������
 */
function is_view_contacts($uid) {
    $guid = get_uid(false);
    //return $guid && ( is_pro() || is_pro(true, $uid) || $uid == $guid );
	return (is_pro(true, $uid))||($guid&&is_pro())||($uid == $guid) ;
	// ���� �������� ������������ ���, ��� � ����������� � � ���, ��� ��� ��������
}

/**
 * ���� ���� ����������� ��������
 * 
 * @param type $user
 * @return type
 */
function is_contacts_not_empty($user) {
    if(is_object($user)) {
        $user = get_object_vars($user);
    }
    return ($user['site']          || $user['site_1']    || $user['site_2']    || $user['site_3'] ||
            $user['icq']           || $user['icq_1']     || $user['icq_2']     || $user['icq_3']  ||
            $user['jabber']        || $user['jabber_1']  || $user['jabber_2']  || $user['jabber_3'] ||
            $user['ljuser']        || $user['lj_1']      || $user['lj_2']      || $user['lj_3'] ||
            $user['skype']         || $user['skype_1']   || $user['skype_2']   || $user['skype_3'] ||
            $user['second_email']  || $user['email_1']   || $user['email_2']   || $user['email_3'] ||
            $user['phone']         || $user['phone_1']   || $user['phone_2']   || $user['phone_3'] ||
            $user['country'] || $user['info_for_reg']['country']
            );
}

/**
 * ���������� ��������� ��� ��� ������������� ������������� ������
 * 
 * @param  array $tmp_files ������ ������������� ������
 * @param  int $max_files ����������� ��������� ���������� ������������� ������
 * @param  int $max_file_size ������������ ������ ������� �� ������������� ������
 * @param  string $kind ��� ������ (contacts, blog � �.�)
 * @param  string $tag_id id html ���� ��� ������������� ������������� ������
 * @return string 
 */
function getAttachedFilesJs( $tmp_files = array(), $max_files = 0, $max_file_size = 0, $kind = '', $tag_id = 'adm_edit_attachedfiles' ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php' );
    
    $sAttach               = 'attachedfiles_list = new Array();';
    $attachedfiles         = new attachedfiles('', true);
    $attachedfiles_session = $attachedfiles->createSessionID();
    
    $attachedfiles->addNewSession( $attachedfiles_session );
    
    if ( $tmp_files ) {
        $attachedfiles_files = array();
        
        foreach( $tmp_files as $attachedfiles_file ) {
            if ( $kind == 'contacts' || $kind == 'project' ) {
                $sId = $kind == 'contacts' ? 'id' : 'file_id';
                $attachedfiles_files[] = $attachedfiles_file[$sId];
            }
            else {
                $attachedfiles_files[] = $attachedfiles_file;
            }
        }
        
        $attachedfiles->setFiles( $attachedfiles_files );
    }
    
    $files = $attachedfiles->getFiles();
    
    if ( $files ) {
        $n = 0;
        foreach ( $files as $attachedfiles_file ) {
            $sAttach .= "attachedfiles_list[{$n}] = new Object;\n";
            $sAttach .= "attachedfiles_list[{$n}].id = '".md5($attachedfiles_file['id'])."';\n";
            $sAttach .= "attachedfiles_list[{$n}].name = '{$attachedfiles_file['orig_name']}';\n";
            $sAttach .= "attachedfiles_list[{$n}].path = '".WDCPREFIX."/{$attachedfiles_file['path']}{$attachedfiles_file['name']}';\n";
            $sAttach .= "attachedfiles_list[{$n}].size = '".ConvertBtoMB($attachedfiles_file['size'])."';\n";
            $sAttach .= "attachedfiles_list[{$n}].type = '{$attachedfiles_file['type']}';\n";
            $n++;
        }
    }
    
    $sAttach .= "attachedFiles.init('adm_edit_attachedfiles', '$attachedfiles_session', attachedfiles_list, 
        '$max_files', '$max_file_size', '". implode(', ', $GLOBALS['disallowed_array']) ."',
        '$kind', ".get_uid(false)."
        );";
    
    return $sAttach;
}

/**
 * ��� �������� �������� ������ ����� ����������, �������� ����� ����������� �� ���������
 * ����� ����������� � �����
 * ��� ��������� ������ ����� ������ �� ���������
 */
function detectSiteVersion () {
    
    global $host;
    if(isset($_SERVER['SHELL'])) return; // ���� ������ ����������� � �������, �� ���� ������ ���������� � �����������
    $_host = str_replace(HTTP_PREFIX, '', $host);
    
    // ��� ����� ������ ����� � ������ ������� ����������� ��������� PDA_PREFIX
    if (!defined('PDA_PREFIX')) {
        define('PDA_PREFIX', 'p');
    }
    
    $_pdaHost = PDA_PREFIX . '.' . $_host;
    
    // ���� ����������� PDA ������ - �� ������ �� ���������� � �� ����������
    if ($_SERVER['HTTP_HOST'] === $_pdaHost) {
        $parsed = parse_url($_SERVER['REQUEST_URI']);
        $path = $parsed['path'];
        $fullLink = HTTP_PREFIX . $_host . $path . '?pda=0';
        // ��������� ������ �� �������� ������ �����
        $GLOBALS['fullLink'] = $fullLink;
        return;
    }
    
    // ������ �� ��������� ������ �����
    $parsed = parse_url($_SERVER['REQUEST_URI']);
    $path = $parsed['path'];
    $pdaLink = HTTP_PREFIX . $_pdaHost . (isMobileVersionExists() ? $path : '');
    $GLOBALS['pdaLink'] = $pdaLink;
    
    // ���� ������ ������ ����� �������� �������������
    if ($_GET['pda'] !== null && ($_GET['pda'] === '0' || $_GET['pda'] === '1')) {
        $_SESSION['pda'] = $_GET['pda'];
    } elseif ($_SESSION['pda'] !== null && ($_SESSION['pda'] === '0' || $_SESSION['pda'] === '1')) {
        // ��� ������ �� ������, �� ��� �������� �����
    } elseif ($_COOKIE['pda'] !== null && ($_COOKIE['pda'] === '0' || $_COOKIE['pda'] === '1')) { // ���� ������ ������ � �����
        $_SESSION['pda'] = $_COOKIE['pda'];
    } else {
        require_once(ABS_PATH . "/classes/Mobile_Detect.php");
        $mobileDetect = new Mobile_Detect();
        $_SESSION['pda'] = (string)(int)$mobileDetect->isMobile();
    }
    
    // ���������� �� ���
    if ($_COOKIE['pda'] !== $_SESSION['pda']) {
        setcookie('pda', $_SESSION['pda'], time() + 3600 * 24 * 30 * 365, '/');
    }
    
    // ���� ����� ��������� ������ - ����������
    /*if ($_SESSION['pda'] === '1' && !is_release()) { //#0024887 - ?�������� ��� ������, ���� ��������� ������ ��� ���
        header_location_exit($pdaLink, null, false);
    }*/
    
    // ����������� �� ?pda=...
    if ($_GET['pda'] !== null) {
        header_location_exit(HTTP_PREFIX . $_host . $path, null, false);
    }
}

// ���� �� ������ ������� �������� � ��������� ������
function isMobileVersionExists () {
    $validSites = array(
        'index',
        'blogs',
        'public',
        'projects',
        'registration',
        'login',
        'users',
        'contacts'
    );

    $parsed = parse_url($_SERVER['REQUEST_URI']);
    $path = $parsed['path'];
    if ($path === '/') {
        $site = 'index';
    } elseif (preg_match('~^\/(.*?)(\/|$|\?)~', $path, $match)) {
        $site = $match[1];
    }
    
    if (in_array($site, $validSites)) {
        return true;
    } else {
        return false;
    }
}

// ������ �� ��������� ������ ������� ��������
function getMobileVersionLink () {
    global $pdaLink;
    return $pdaLink;
}
function getFullVersionLink () {
    global $fullLink;
    return $fullLink;
}

/**
 * ���������� ������ �� �����
 * 
 * @param array  $array       ������
 * @param string $group_key   ��� ����� ��� ����������
 * @return array
 */
function array_group(& $array, $group_key, $subname = null) {
    if(!is_array($array)) return;
    $group = array();
    foreach($array as $key=>$value) {
        if(!is_array($value)) return;
        if(!array_key_exists($group_key, $value)) return;
        if($subname) {
            if(!array_key_exists($subname, $value)) return;
            $group[$value[$group_key]][$value[$subname]] = $array[$key];
        } else {
            $group[$value[$group_key]][] = $array[$key];
        }
    }
    $array = $group;
    return true;
}

/**
 * ��� ������ ����� ����� ������������ �� ���� ����� � ���������� ������

 * @staticvar type $curs 
 * @param type $sum
 * @return type
 */
function _bill($sum, $cur = null) {
    static $curs;
    
    if($curs == null) {
        require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/exrates.php');
        $exrates = new exrates();
        if($cur == null) {
            $cur = exrates::WMR;
        }
        $curs = $exrates->GetField(exrates::FM . $cur, $error, 'val');
    }
    
    return round( $sum * $curs, 2);
}


function hlcode($text, $revert = false) {
    if($revert) {
        $text = str_replace(array("\r", "\n"), array("__r__", "__n__"), $text);
        $text = preg_replace("/<p\s*?class=['|\"]code\s*?([\w\:]*?)['|\"]>(.*?)<\/p>/mix", "{code_$1}$2{/code_$1}", $text);
        $text = str_replace(array("__r__", "__n__"), array("\r", "\n"), $text);
    } else {
        $aCodeAllowed = array('bash', 'xml', 'html', 'cpp', 'sql', 'css', 'php', 'python', 'perl', 'ruby', 'cs', 'java:javascript');
        
        $text = preg_replace('/\{code_('. implode('|', $aCodeAllowed) .')\}/mix', '<p class="code $1">', $text);
        $text = preg_replace('/\{\/code_(?:'. implode('|', $aCodeAllowed) .')\}/mix', '</p>', $text);
    }
    return $text;
}

function html2wysiwyg($text) {
    $text = hlcode($text, true);
    /**
     * @todo ����� ������ � ������ ���� ����
     */
    $text = str_replace("</cut>", "", $text);
    $text = preg_replace("/<cut\s*?\/>/mix", "<cut>", $text);
    $text = str_replace("[cut]", "<cut>", $text);
    $text = str_replace("<cut>", "<cut></cut>", $text);
    return $text;
}

/**
 * ��������� ������������ ������������� �� �� ����� ��������, ���� �� ���������� true
 * 
 * @param boolean $exact ��������� ������. ���� ����� �� ������ ������������
 * @param integer $uid   ��������� �������� ��� ������������ c uid = $uid � �� ��� ��������
 * @return type
 */
function is_verify($login = false) {
    static $verify;
    
    if (!$login) {
        return $_SESSION['is_verify'] == 't';
    }
    
    if ( empty($verify[$login]) ) {
        require_once (ABS_PATH . "/classes/users.php");
        $user = new users();
        $user->getUser($login == false ? $_SESSION['login'] : $login);
        $verify[$login] = ( $user->is_verify == 't') ;
        return $verify[$login];
    } else {
        return $verify[$login];
    }
}

/**
 * ����� ������ ������ � �������� ���� ��������� ����������
 * 
 * @param type $email
 * @return type
 */
function email_alias($email) {
    $aliases = array(
        array('ya.ru', 'yandex.ru')
    );
    
    list($nick, $host) = explode("@", $email);
    $result = array();
    foreach($aliases  as $i=>$key) {
        if(in_array($host, $key)) {
            foreach($key as $mail) {
                $result[] = "{$nick}@$mail";
            }
            break;
        }
    }
    
    if(!$result) {
        $result[] = $email;
    }
    return $result;
}

function dimension_image($width, $height, $hdim) {
    if ($hdim && ($width > $hdim || $height > $hdim)) {
        $x_ratio = $hdim / $width;
        $y_ratio = $hdim / $height;

        $ratio = min($x_ratio, $y_ratio);
        if ($ratio == 0) {
            $ratio = max($x_ratio, $y_ratio);
        }
        $use_x_ratio = ($x_ratio == $ratio);

        $width = $use_x_ratio ? $hdim : floor($width * $ratio);
        $height = !$use_x_ratio ? $hdim : floor($height * $ratio);
    }
    
    return array($width, $height);
}

function array2params(&$item, $key) {
    $item = $key . ' = "'.$item.'"';
}

function view_image_file($filename, $login, $dir, $params = array()) {
    if($params['template'] == '') $params['template'] = '%s';
    if ($filename == '') {
        return "";
    }
    
    $l_dir = substr($login, 0, 2) . "/" . $login;
    $path = "users/$l_dir/$dir/".$filename;
    $cfile = new CFile($path);
    $width = $cfile->image_size['width'];
    $height = $cfile->image_size['height'];
    $type = $cfile->image_size['type'];
    
    if (!$width || !$height) {
        return "";
    }

    list($width, $height) = dimension_image($width, $height, $params['max_dim']);
    
    if($params['unanimate_gif']) $filename = get_unanimated_gif($login, $filename, dirname($path));
    $image_path   =  WDCPREFIX."/users/$login/$dir/$filename";
    $params['image'] = array(
        'src'    => $image_path,
        'alt'    => $params['alt'],
        'width'  => $width,
        'height' => $height,
        'id'     => $params['id'],
        'class'  => $params['class']
    );
    array_walk($params['image'], 'array2params');
    $html = '<img '.implode(" ", $params['image']).'/>';
    
    return sprintf($params['template'], $html);
}


/**
 * ��������� ������� ����� ��� action-������� �������. ��� ����� ������������ ��� ����������� �����������
 * ��������� ������ �� Standby, ������������� � �������.
 * Action-������ -- ��� ������ POST-������ (�������� �����), ��������, ��������� ����������� � ����. ��� �������� � ����� �������� � ���
 * ������������ ������ ��������� �� ������� ��. ����� ��� ������������� ������� ���������� � ������ ������������
 * � � ��������� ����������� ������� ������.
 * ������ ����� ������������ ������ � ������ � ������ DB::STBY_NOACT. ��� ����:
 * - ���� ���������� DB::STBY_NOACT, �� DB::STBY_OPTS_NOACT_LAG <= 0, �� ������������� ������ ������ �� ���������� ����� action-�������.
 *   ��� �������� �� �������� �����������, ��� �������� ���.
 * - ���� ���������� DB::STBY_NOACT � DB::STBY_OPTS_NOACT_LAG > 0, �� ������ ������������� ����� � ������� STBY_OPTS_NOACT_LAG ������ ������
 *   ����� ���������� action-�������. ����� ������� �������������� ����������� ����� ��� ����������� ��������� ������, �, ���� ����
 *   �������� �� �������� ����������� ������ ����� �����, �� ����� ������ �� �������.
 *
 * @see DB::checkStandby()
 */
function setLastUserAction() {
    if ( defined('IS_USER_ACTION')
         || isset($_POST['u_token_key'])
         || isset($_GET['u_token_key'])
         || isset($_POST['action'])
         || ( isset($_GET['action']) && stripos($_SERVER['REQUEST_URI'], '/search') !== 0 )
       )
    {
        // !!! �����
        $_SESSION['last_user_action'] = time();
        if(!defined('IS_USER_ACTION')) {
            define('IS_USER_ACTION', 1);
        }
    }
}

/**
 * ����� ������ �� ������ ���������� is_view �������� ����� ���������� ������� ������ ������ ������ ����� �� ������ ����� �������
 * 
 * @example access_view('link_name', <a>%s</a>, true)  -> <a>link_name</a> 
 *          access_view('link_name', <a>%s</a>, false) -> link_name
 * 
 * @param type $name        �������� ������� ��������� �� ���������
 * @param type $template    ������ ������� ����������� �������� � ���� � ��������� ��� is_view true
 * @param type $is_view     
 * @return type
 */
function access_view($name, $template, $is_view) {
    return $is_view ? sprintf($template, $name) : $name;
}

/**
 * ��� ������������ ������ �����
 * @param type $number
 * @param type $d
 * @return type
 */
function to_money($number, $d=0) {
    $number = round($number, $d);
    return number_format($number, $d, '.', ' ');
}

function name_page($path = null) {
    if($path == null) {
        $path = $_SERVER['REQUEST_URI'];
    }
    return strtolower( current( explode("/", trim($path, '/')) ) );
}

/**
 * 0024876
 * @desc �������� ������������ ������ ���������� ���� � ��������
* */
function validate_code_style(&$s) {
    $allow = array('bash', 'xml', 'html', 'cpp', 'sql', 'css', 'php', 'python', 'perl', 'ruby', 'cs', 'java:javascript');
    $doc = new DOMDocument();
    $doc->validateOnParse = false;
    @$doc->loadHTML($s);
    $ps = $doc->getElementsByTagName("p");
    for ($i = 0; $i < $ps->length; $i++) {
        $class =  $ps->item($i)->getAttribute("class");
        $class = trim(str_replace("code", "", $class));
        if ( !in_array($class, $allow) ) {
            $s = preg_replace("#class=\"code $class\"#si", "", $s);
        }
     }
}


/**
 * ������ JSON ����� � PHP ������
 * @deprecated   �����-�� ������� ��� xajax+json. �� ������������!
 * 
 * @param  string $sArray JSON ������
 * @return array
 */
function _jsonArray( $sArray = '' ) {
    $aArray = array();
    
    if ( is_bool($sArray) === true ) {
        $sArray = ''; 
    }
    
    if ( trim($sArray) ) {
        $sArray = stripslashes( $sArray );
        
        $sArray = iconv( 'CP1251', 'UTF-8', $sArray );
        $aArray = json_decode( $sArray, true );
        
        foreach ( $aArray as $sKey => $sVal ) {
            $aArray[$sKey] = iconv( 'UTF-8', 'CP1251', $sVal );
        }
    }
    
    return $aArray;
}


//------------------------------------------------------------------------------


/**
 * �������������� ������ ����� ������ �����
 * � ����� ���������� � ������� ����� �����
 * .?! - ��������. 
 * 
 * �� ��������� ������ ����������� �������� � �������� � ���.
 * @todo: � �������� �� ����� ������ ���� � ���� ����� �������� � ������� ������� �� ����� ������������� � ��������!
 * 
 * @param string $string
 * @return string
 */
function sentence_case($string) {
    
    $locale = setlocale(LC_ALL,NULL);
    setlocale(LC_ALL,'ru_RU.CP1251');

    $sentences = preg_split('/([.?!]+)/', $string, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
    $new_string = '';
    foreach ($sentences as $key => $sentence) {
        $new_string .= ($key & 1) == 0?
            ucfirst(strtolower(trim($sentence))) :
            $sentence.' ';
    }
    
    $new_string = trim($new_string);
    
    setlocale(LC_ALL,$locale);
    
    return $new_string;
} 



//------------------------------------------------------------------------------


/**
 * ���������� ������� ������������
 * 
 * @param type $user
 * @return type
 */
function view_user_label($user, $prefix = '')
{
    $html = '';
    
    if (is_object($user)) {
        $user = get_object_vars($user);
    }
    
    //������ �����
    if (@$user[$prefix . 'is_profi'] == 't') {
        $html .= '&nbsp;' . view_profi();
    }
    
    //@todo: ��������� ���������� �� �������������
    //�������� ������ ���.��������� ��������� ������� ������
    
    return $html;
}


//------------------------------------------------------------------------------



/**
 * ������ �������� ��� ����� � ������ �����
 * 
 * @param mixed $user
 * @return string
 */
function view_fullname($user = NULL, $hide_login = false)
{
    if(!$user)
    {
        $u = array(
            'uname'     => $_SESSION['name'],
            'usurname'  => $_SESSION['surname'],
            'login'     => $_SESSION['login']
        );
    }
    else
    {
        $u = (is_object($user)) ? array(
            'uname'     => $user->uname,
            'usurname'  => $user->usurname,
            'login'     => $user->login
        ) : $user;
    }
    
    $fullname = "{$u['uname']}";
    $fullname .= ((empty($fullname)) ? "" : " ") . "{$u['usurname']}";
    if(!$hide_login) {
        $fullname .= (empty($fullname)) ? "{$u['login']}" : " [{$u['login']}]";
    }

    return $fullname;
}


//------------------------------------------------------------------------------

/**
 * ��������� �������� ������� � �����
 * 
 * @param int $cnt
 * @return string
 */
function view_event_count_format($cnt)
{
    if($cnt <= 0) return '';
    $format = '<span class="b-user-menu-clause-quantity %s">%s</span>';
    $is_inf = ($cnt > 99);
    $cnt = $is_inf?'&infin;':$cnt;
    return sprintf($format,($is_inf)?'b-user-menu-clause-infinite-quantity':'',$cnt);
}

//------------------------------------------------------------------------------

/**
 * ��������� ��������� ����� � �����
 * 
 * @return string
 */
function view_account_format($tip = FALSE)
{
    $ac_sum = round(zin($_SESSION['ac_sum']), 2);
    $bn_sum = round(zin($_SESSION['bn_sum']), 2);
    $ac_sum_txt = number_format($ac_sum, 2, ",", " ");
    $bn_sum_txt = number_format($bn_sum, 2, ",", " ");

    $ret = ($tip)?"� ��� �� ����� {$ac_sum_txt} " . ending($ac_sum, '�����', '�����', '������'): 
                  $ac_sum_txt . ((int) $_SESSION['bn_sum'] ? ' + ' . $bn_sum_txt : '') .' ���.';
    
    $ret = str_replace(',00', '', $ret);
    return $ret;
}


//------------------------------------------------------------------------------


/**
 * �������������� ����
 * 
 * @param type $cost
 * @param type $currency
 * @param type $preffix
 * @param type $short_currency_format
 * @return type
 */
function view_cost_format($cost, $currency = true, $preffix = false, $short_currency_format = true)
{
    $is_negative = ($cost < 0);
    $cost = round(abs($cost), 2);
    $cur = '&nbsp;' . (($short_currency_format)?'�.':ending($cost, '�����', '�����', '������'));
    $cost = number_format($cost, 2, ',', ' ');

    return (($preffix || $is_negative)?(($is_negative)?'-':'+'):'') . 
           str_replace(',00', '', $cost) . 
           (($currency)?$cur:'');        
}


//------------------------------------------------------------------------------


/**
 * ��������� �������� �� ������� �������� ����� ���������
 * �� ����������� ������� ������������� �����.
 * 
 * @staticvar null $site
 * @param string | array $page
 * @param mixed $retTrue - ������� ���� ��������
 * @param mixed $retFalse - ������� ���� �� ���������
 * @param array $notParams - ��� �������� ��������� URL ���������, ���� ���� ������������� ��������� ���������
 * @return mixed
 */
function isCurrentPage($page = '/', $retTrue = TRUE, $retFalse = FALSE, $notParams = array(), $notPage = array())
{
    static $site = NULL;
    static $params = array();
    
    $page = !is_array($page)?array($page):$page;
    
    if(!$site)
    {   
        $parsed = parse_url($_SERVER['REQUEST_URI']);
        $path = $parsed['path'];
        if ($path === '/') $site = array($path);
        else $site = explode('/', preg_replace('|/*(.+?)/*$|', '\\1', $path));

        parse_str($parsed['query'],$params);
    }

    $ret = !empty($notParams) && (count(array_intersect_assoc(array_keys($params), $notParams)) == count($notParams)); 
    $ret = (count(array_intersect_assoc($site, $page)) == count($page)) && !$ret; 
    if(!empty($notPage))  $ret = (count(array_intersect_assoc($site, $notPage)) != count($notPage)) && $ret;

    return ($ret)?$retTrue:$retFalse;
}


//------------------------------------------------------------------------------


/**
 * ������� ���� �������: �� ���� "2013-09-12" ����������� � "12 �������� 2013"
 * 
 * @param type $date
 * @return type
 */
function date_text($date, $d = 'j') 
{
    $time = strtotime($date);
    return date($d,$time) . ' ' . 
           monthtostr(date('n',$time),true) . ' ' . 
           date('Y',$time);
}


//------------------------------------------------------------------------------


/**
 * ��������� ������� REQUEST_URI
 */
function ref_uri()
{
    $_SESSION['ref_uri'] = urlencode(isset($_SERVER['HTTP_ORIGINAL_URI']) ? 
            $_SERVER['HTTP_ORIGINAL_URI'] : $_SERVER['REQUEST_URI']);
}


//------------------------------------------------------------------------------


/**
 * ������� ������������ �������?
 * 
 * @param type $days
 * @return boolean
 */
function isNoob($days = 60)
{
    if(!isset($_SESSION['reg_date'])) {
        return false;
    }
        
    $ts = strtotime($_SESSION['reg_date']);
    return ($ts + $days * 86400) > time();
}


//------------------------------------------------------------------------------


/**
 * ���� ����������� ������ �������� ��� �� ����� ���� ���, 
 * ��� �� ���� �� ������� ������� ��� �� ����� � ����� (�� ��������� ��� ����) 
 * ��� ��������� ��������
 * 
 * @return boolean
 */
function isAllowTestPro()
{
    $is_noob = isNoob(60);
    return $is_noob || (
            !$is_noob && 
            !@$_SESSION['pro_last'] && 
            isset($_SESSION['is_was_pro']) && 
            $_SESSION['is_was_pro'] === false);
}



//------------------------------------------------------------------------------


/**
 * ������� ������������ PROFI?
 * 
 * @return boolean
 */
function isProfi()
{
    return isset($_SESSION['is_profi']) && $_SESSION['is_profi'] && $_SESSION['pro_last'];
}


//------------------------------------------------------------------------------


/**
 * �������� �� ������� PROFI ��� �������� ������������
 * 
 * - ���� ��������������� �� ����� ������ 2� ���, �
 * - ���� �����������, �
 * - �������� ������� ������������� ������� ����� ��� ��������� 98%, �
 * - ��������� ���������� ������� ������ 10
 */
function isAllowProfi($refresh = false)
{
    static $is_allow = null;
    
    if($is_allow !== null && !$refresh) {
        return $is_allow;
    }
    
    $uid = get_uid(false);
    
    if($uid <= 0 || is_emp()) {
        $is_allow = false;
        return false;
    }
    
    $ts = strtotime($_SESSION['reg_date']);
    $isOld = strtotime('- 2 year') > $ts;
    
    if(!$isOld || $_SESSION['is_verify'] == 'f') {
        $is_allow = false;
        return false;
    }
    
    global $DB;
    $uc = $DB->row("
        SELECT 
            paid_advices_cnt + ops_emp_plus + sbr_opi_plus + ops_frl_plus + tu_orders_plus + projects_fb_ext_plus AS cnt_plus,
            ops_emp_minus + sbr_opi_minus + ops_frl_minus + tu_orders_minus + projects_fb_ext_minus AS cnt_minus
        FROM users_counters 
        WHERE user_id = ?i 
        LIMIT 1", $uid);
    
    if(!$uc) {
        $is_allow = false;
        return false;
    }

    $cnt_plus = @$uc['cnt_plus'];
    $cnt_minus = @$uc['cnt_minus'];
    $cnt_total = $cnt_plus + $cnt_minus;
    
    if($cnt_total < 10) {
        $is_allow = false;
        return false;
    }
    
    $per_plus = ($cnt_plus > 0)? ($cnt_plus * 100 / $cnt_total) : 0;
    
    if($per_plus < 98) {
        $is_allow = false;
        return false;
    }
    
    $is_allow = true;
    return true;
}


//------------------------------------------------------------------------------


function br2br($string)
{
    return preg_replace('#<br\s*/?>\s+<br\s*/?>#', "<br/>", $string);
}


//------------------------------------------------------------------------------


function mb_unserialize($string) 
{
    $string = preg_replace('/s:(\d+):"([^"]*)";/se', "'s:'. strlen('\\2') .':\"\\2\";'", $string);
    //$string = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $string);
    return unserialize($string);
}


//------------------------------------------------------------------------------


/**
 * ������������ ������ ��� ������
 * @todo �� ������ �������� ������-��. ��. ����� encodeCharset2
 * 
 * @param type $from
 * @param type $to
 * @param type $data
 * @return type
 */
function encodeCharset($from, $to, $data)
{
    if (is_array($data)) {
        array_walk_recursive($data, function(&$item) { 
            //$item = mb_convert_encoding($item, 'UTF-8', 'CP1251'); 
            $item = iconv($from, $to, $item);//�������???
        });        
    } else {
       $data = iconv($from, $to, $data); 
    }
    
    return $data;
}

/**
 * ������������ ������ ��� ������
 * 
 * @param type $from
 * @param type $to
 * @param type $data
 * @return type
 */
function encodeCharset2($from, $to, $data)
{
    if (is_array($data)) {
        foreach ($data as $key => $item) { 
            $data[$key] = encodeCharset2($from, $to, $item);
        }        
    } else {
       $data = iconv($from, $to, $data); 
    }
    
    return $data;
}


//------------------------------------------------------------------------------


/**
 * �������� � �������� ������� ��� �������� � ��������
 * 
 * @return boolean
 */
function isLandingProject()
{
    //���� ������ ������� �� ���������� ������� 
    //�� ������� ���������� ID �������� � ��������
    if (empty($_POST)) {
        unset($_SESSION['landingProjectId']);
    }    
    
    //���������� �������� ������� ��� �������� � ��������
    $name = __paramInit('striptrim', NULL, 'project_name', NULL);

    if ($name && !empty($name) && isset($_POST['hash']) && 
        $_POST['hash'] === @$_SESSION['from_landing_hash'] && 
        rtrim($_SERVER['HTTP_REFERER'],'/') === $GLOBALS['host']) {

        unset($_SESSION['from_landing_hash']);
        $_POST = array();

        require_once(ABS_PATH . '/classes/LandingProjects.php');
        $_SESSION['landingProjectId'] = LandingProjects::model()->addLandingProject($name);
        
        return $name;
    }    
    
    return false;
}

//------------------------------------------------------------------------------

/**
 * ������� ��������� ID ������ � ���������� 
 * �������� � �������� �� ���������� �������
 * 
 * @return type
 */
function getLastLandingProjectId()
{
    $ret = false;

    if (isset($_SESSION['landingProjectId'])) {
        $ret = $_SESSION['landingProjectId'];
        unset($_SESSION['landingProjectId']);
    }    
    
    return $ret;
}


//------------------------------------------------------------------------------

/**
 * ���� �� � ������������ ������� ����� ����������
 * 
 * @param type $uid
 * @return boolean
 */
function isWasPlatipotom($bill_id = 0)
{
    static $isWasPlatipotom = null;
    
    if ($isWasPlatipotom !== null) {
        return $isWasPlatipotom;
    }   
    
    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/platipotom.php');
    $platipotomObject = new platipotom();
    $isWasPlatipotom = $platipotomObject->isWasPlatipotom($bill_id);

    return $isWasPlatipotom;
}


//------------------------------------------------------------------------------


/**
 * ��������� ��������� �� �������� ������������ ������ �����
 * 
 * @param type $post
 * @return boolean
 */
function is_crimea_people($post) 
{
    $address = isset($post['address'])?$post['address']:'';
    $idcard_by = isset($post['idcard_by'])?$post['idcard_by']:'';
    
    $crimea_city = array(
       '����',
       '������',
       '������',
       '�������',
       '����������',
       '���������',
       '�������',
       '���������',
       '��������',
       '�����',
       '���������������',
       '����',
       '�����������',
       '�����������',
       '������ ����',
       '�����',
       '�����',
       '��������',
       'ٸ�����',
       '����'     
    );
    
    $parts = explode(',', $address);
    $city = ucfirst(strtolower(trim($parts[2])));

    //��������� ���� �� � ������ ����� �����
    //� ����� ��� ����� �������
    if (in_array($city, $crimea_city) ||        
        strpos($idcard_by, '����')) {
        
        return true;
    }
    
    return false;
}


//------------------------------------------------------------------------------


function currentUserHasPermissions($permission) 
{
    $user_permissions = !empty($_SESSION['permissions'])? $_SESSION['permissions'] : array();
    return (in_array($permission,$user_permissions) || in_array('all',$user_permissions));
}


//------------------------------------------------------------------------------


/**
 * �������� ����������� ����������
 * 
 * @param type $path
 * @param type $del_dir
 * @param type $level
 * @return boolean
 */
function delete_files($path, $del_dir = FALSE, $level = 0)
{
    // Trim the trailing slash
    $path = rtrim($path, DIRECTORY_SEPARATOR);

    if ( ! $current_dir = @opendir($path)) {
        return FALSE;
    }

    while (FALSE !== ($filename = @readdir($current_dir))) {
        if ($filename !== '.' and $filename !== '..') {
            if (is_dir($path.DIRECTORY_SEPARATOR.$filename) && $filename[0] !== '.') {
                delete_files($path.DIRECTORY_SEPARATOR.$filename, $del_dir, $level + 1);
            } else {
                unlink($path.DIRECTORY_SEPARATOR.$filename);
            }
        }
    }
    @closedir($current_dir);

    if ($del_dir == TRUE AND $level > 0) {
        return @rmdir($path);
    }

    return TRUE;
}


//------------------------------------------------------------------------------




/**
 * �������� ��������� ������, ���� �� ���������� �� ������ �� ��� ��������
 * ��������: 127287, ������, �. ������, ��. 2-� ��������� � 38� ���.9
 */
function parseAddress($address)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/country.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/city.php');
    
    $index = null;
    $country_name = null;
    $country_id = null;
    $city_id = null;
    $city_name = null;
    
    $parts = explode(',', $address);
    
    if ($parts) {

        $countryObject = new country();
        $cityObject = new city();
        
        $parts = array_map('trim', $parts);
        
        //������
        $country_pos = 0;
        if (intval($parts[0]) > 0) {
            $index = intval($parts[0]);
            $country_pos = 1;
        }
        
        //������
        $city_pos = $country_pos;
        $_country_name = ucfirst($parts[$country_pos]);
        if ($country_id = $countryObject->getCountryId($_country_name)) {
            $country_name = $_country_name;
            $city_pos++;
        }
        
        //�����
        $street_pos = $city_pos;
        $_city_name = ucfirst(trim(str_replace('�.', '', $parts[$city_pos])));
        if ($city_data = $cityObject->getByName($_city_name)){
            $city_id = $city_data['id'];
            $city_name = $_city_name;
            $street_pos++;
            
            //���� ��� ������ �� ������� �� ���������� �� ������
            $country_id = (!$country_id)?$city_data['country_id']:$country_id;
        }
        
        //��������� ��������� ������
        if (!$street_pos) {
            return false;
        }

        $parts = array_slice($parts, $street_pos);
        $address = implode(', ', $parts);
        
        return array(
           'index' => $index,
           'country' => $country_name,
           'country_id' => $country_id,
           'city' => $city_name,
           'city_id' => $city_id,
           'address' => $address
       );       
    }    
    
    return false;
}


//------------------------------------------------------------------------------

/**
 * ��������� MD5 ���� ��� ���������� �� ������� ����
 * 
 * @staticvar null $_current_date
 * @param type $params
 * @param type $uid
 * @return type
 */
function paramsHash($params, $uid = null)
{
    $_current_date = date('Y-m-d');

    if (!$uid && isset($_SESSION['uid']))  {
        $uid = $_SESSION['uid'];
    }

    return md5("5L30{*R{@1RC" . $_current_date . serialize(array_values($params)) . $uid);    
}


//------------------------------------------------------------------------------


function proItemToText($pay)
{
    if (isset($pay['day']) && $pay['day'] > 0) {
        $txt_time = ending($pay['day'], '����', '���', '���');
        $title = "{$pay['day']} {$txt_time}";
    } elseif (isset($pay['week']) && $pay['week'] > 0) {
        $txt_time = ending($pay['week'], '������', '������', '������');
        $title = "{$pay['week']} {$txt_time}";
    } else {
        if ($pay['month'] == 12) {
            $txt_time = '���';
            $title = "1 {$txt_time}";
        } else {   
            $txt_time = ending($pay['month'], '�����', '������', '�������');
            $title = "{$pay['month']} {$txt_time}";
        }
    }
    
    return $title;
}


//------------------------------------------------------------------------------



function view_social_buttons($with_email = true, $attrs = array())
{
    $buttons = array(
        'vkontakte' => '���������',
        'facebook' => 'Facebook',
        'odnoklassniki' => '�������������'
    );
    
    require_once(ABS_PATH . "/templates/tpl.social_buttons.php");
}


//------------------------------------------------------------------------------










/////////////////////////// ����� ������� ���� ����� �����! ////////////////////////////

        
if (!defined('IN_STDF'))
{
    require_once (dirname(__FILE__).'/../vendor/autoload.php');
    
    libxml_disable_entity_loader();
    date_default_timezone_set('Europe/Moscow');

    define('IN_STDF', 1);
    define("START_TIME", microtime(true));
    if ( !defined('COOKIE_SECURE') ) {
        define('COOKIE_SECURE', isset($_SERVER['HTTP_NGINX_HTTPS']));
    }
    define('HTTP_PFX', 'http' . (COOKIE_SECURE ? 's' : '') . '://');
    define('NY2012TIME', (date('Ymd') >= '20111229' && date('Ymd') <= '20120109'));
    
    include("config.php");
    include("globals.php");
    
    require_once('memBuff' . (defined('USE_MEMCACHED') ? 2 : 1) . '.php'); // ����� ��������� ��������.
    include("CFile.php");
	require_once "DB.php"; 
    $DB = new DB('master');
    
    foreach((array)$GLOBALS['pg_db_standby_defaults'] as $k=>$v) {
        if(preg_match('~(?:income/|minutly|hourly)~', $_SERVER['PHP_SELF'])) {
            $v = array(DB::STBY_OPTS_ANY_MASK=>DB::STBY_CACHED);
        }
        DB::setStandby($k, $v);
    }

    $cssBrowser = $bVersion = "";
    define('BROWSER_COMPAT', browserCompat($cssBrowser, $bVersion));
    define('BROWSER_NAME', $cssBrowser);
    define('BROWSER_VERSION', intval($bVersion[1]));

    if(!defined('IS_EXTERNAL')) {

        $is_banned = -1;

        if ($_POST && sizeof($_POST) > 0 && !$allow_fp && !$xajax){
            if ($_SERVER["HTTP_REFERER"]){
                preg_match("'^https?://(www\.)?([^/]*)'", $_SERVER["HTTP_REFERER"], $mch);
                preg_match("'^(www\.)?([^/]*)'", $_SERVER["HTTP_HOST"], $mch2);
                //if ($mch[2] != $mch2[2])
                if ( !preg_match('/' . preg_quote($mch2[2]) . '$/', $mch[2]) )
                    unset($_POST);
            }
            // ����������������, �� ������� ����, ��� � ��������� ������ �������� �� ���������
            // ��������� � ������������ Referers.
            // else { unset($_POST); }
        }
        
        
        require_once(ABS_PATH . "/classes/session.php");
        require_once(ABS_PATH . "/classes/adriver.php");
        require_once(ABS_PATH . '/classes/Helpers/GaJsHelper.php');
        
        
        session_start();
        detectSiteVersion();
        $UID = get_uid(false);

        $ip = getRemoteIP();

        // Ticket #0028763
        if (is_release() && //��� �����
            $UID > 0 && //�������
            !isset($error404_page) && //��� �� 404�
            strpos($ip, '10.') !== 0 && //�� VPN
            isset($_SESSION['permissions']) && //���� �����-�� �����
            !empty($_SESSION['permissions'])) {
            
            //����� �� ��� VPN
            header ("Location: /404.php");
            exit; 
        }

        
        //���� ��� �� ����������� �� ������ �����
        $_action = __paramInit('striptrim', 'action', 'action');
        if ($_action !== 'login' && 
            !defined('IS_AUTH_SECOND') && 
            !defined('IS_OPAUTH') && 
            !defined('IS_PHP_JS')) {
            
            unset($_SESSION['2fa_provider']);
        } 

        // ���������� � Apache ��� �������������� ������������
        if (function_exists('apache_note')) {
            apache_note('custom_field', $UID);
        }

        if(isset($_GET['blogon'])) {
            $_SESSION['blog_ON'] = 1;
        }
        if(isset($_GET['blogoff'])) {
            unset($_SESSION['blog_ON']);
        }
        // ��������� ����� (������� ����� � ����������) #0023347
        // @TODO ������� ����� ��� ��� ��������� � ������
        if($_SESSION['blog_ON'] != 1) { // ��� ����� �� ����
            define("BLOGS_CLOSED", true);
        } else {
            define("BLOGS_CLOSED", false);
        }
        define("REDIRECT_BLOG_URL", "/commune/drugoe/5000/obschenie/");
        
        // @todo ������������ ������� �����, ����� ���� ������ �������
        // #0017167
        /*if(isset($_GET['template'])) {
            if($_GET['template'] == 'new') {
                setcookie('template_site', "template3.php", time()+60*60*24*30);
                $_COOKIE['template_site'] = "template3.php";
            }

            if($_GET['template'] == 'old') {
                setcookie('template_site', "template2.php", time()+60*60*24*30);
                $_COOKIE['template_site'] = "template2.php";
            }
        }*/

        setLastUserAction();
        
        if ($_POST) {
            csrf_magic();
        }
        if (!$_SESSION['rand']) {
            $_SESSION['rand'] = csrf_token();
        }
            
        if ($UID) {
             // ������ ������������ � �������� �����
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
            $REG    = new registration();
            $REG->listenerAccess($_POST);
            
            if ($UID > 0) {
                users::regVisit();
                
                $memBuff = new memBuff();
                //���������, �� ��� �� ������� ��� ������ ���������� ������ �������������
                //(��������, �������� �������� � ���������� ����� �������� ������ �� ����������)
                $ac_sum_update = $memBuff->get('ac_sum_update_'.$UID);
                if($ac_sum_update) {
                    $_SESSION['ac_sum'] = $ac_sum_update;
                    $memBuff->delete('ac_sum_update_'.$UID);
                }
                
                //���������, �� ��� �� ������� ������ ���
                $is_changed_pro = $memBuff->get('is_changed_pro_'.$UID);
                if($is_changed_pro) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
                    payed::updateUserSession();
                    $memBuff->delete('is_changed_pro_'.$UID);
                }
            }
        }
        
        // 0023233: �������� �������, ���� ����������
        require_once( ABS_PATH . '/classes/traffic_stat.php' );
        $traffic_stat = new traffic_stat();
        $traffic_stat->checkReferer();
        
        // ����� ��� ��������� UserEcho SSO-�����
        require_once(ABS_PATH . '/classes/userecho.php');
        $GLOBALS['userecho_sso'] = UserEcho::get_sso_token(USERECHO_API_KEY, USERECHO_PROJECT_KEY, array());
        
        //��������� UTM ����� GA
        require_once(ABS_PATH . '/classes/ga_stat.php');
        $gaStat = new GaStat();
        $gaStat->checkUtm();
    }

    require_once (ABS_PATH . '/siteclosed/index.php');
}
