
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
    <head>
        <meta name="description" lang="ru" content="��������� ������ (���-����) �� Free-lance.ru" />
        <meta name="keywords" lang="ru" content="��������� ������ (���-����) �� Free-lance.ru" />
                <meta content="text/html; charset=windows-1251" http-equiv="Content-Type" />
        <title>��������� ������ (���-����) �� Free-lance.ru</title>
        <link rel="shortcut icon" href="/favicon.ico" />
               <link type="text/css" href="http://betadav.free-lance.ru/wdstatic/2bade4cfc0b1b892ab5fbd574989ca0b_1335164244.css" rel="stylesheet" />                                                  

<script type="text/javascript" src="/scripts/mootools-new.js"></script>
<script type="text/javascript" src="/scripts/b-combo/b-combo-dynamic-input.js?rand=<?=rand(1000,9999)?>"></script>
<script type="text/javascript" src="/scripts/b-combo/b-combo-multidropdown.js?rand=<?=rand(1000,9999)?>"></script>
<script type="text/javascript" src="/scripts/b-combo/b-combo-autocomplete.js"></script>
<script type="text/javascript" src="/scripts/b-combo/b-combo-calendar.js?rand=<?=rand(1000,9999)?>"></script>
<script type="text/javascript" src="/scripts/b-combo/b-combo-manager.js"></script>

        
        <!--[if lte IE 8]>
        <link href="/css/ie8.css" rel="stylesheet" type="text/css" />
        <![endif]-->
        <!--[if lte IE 7]>
        <link href="/css/ie7.css?v" rel="stylesheet" type="text/css" />
        <![endif]-->
        <script type="text/javascript">var ___isIE5_5 = 1;</script>
        <!--[if lt IE 5.5]><script type="text/javascript">var ___isIE5_5 = 0;</script><![endif]-->
        <!--[if lte IE 6]>
        <link href="/css/ie6.css?v" rel="stylesheet" type="text/css" />
         <style type="text/css">
        img { behavior: url(/scripts/iepngfix.htc) }
        </style>
        <![endif]-->
        
        <script type="text/javascript">
           var ___WDCPREFIX = 'http://betadav.free-lance.ru';
 <?
 require_once $_SERVER["DOCUMENT_ROOT"]."/classes/stdf.php";
 require_once $_SERVER["DOCUMENT_ROOT"]."/classes/memBuff2.php";
 require_once $_SERVER["DOCUMENT_ROOT"]."/classes/search/sphinxapi.php";
  ?> 
 var _TOKEN_KEY = '<?=$_SESSION['rand']?>';           
           var _UID = 237151;
                      window.addEvent('domready', function() {               
           });
           
   

           
                   </script>
        
            </head>
    <body class="u-nopro firefox ">
        <div class="container">
            <script type="text/javascript">
document.write('<div class="b-banner  b-banner_layout_horiz"><iframe src="/iframe_100pct.php?p=" scrolling="no" frameborder="0" width="100%" height="90"></iframe></div>');
</script>
            
<script type="text/javascript">
    window.addEvent('domready', function() {
        $$('.b-consultant__toggler').addEvent('click', function(){
            $(this).getParent('.b-consultant').toggleClass('b-consultant_hidden');
            var index = Number($(this).getParent('.b-consultant').className.indexOf('hidden'));
            var status = index == -1 ? '0' : '1';
            setConsultantStatusCookie(status);
            return false;
        });
    });
    
    function setConsultantStatusCookie(val){
        var exdate=new Date();
        exdate.setDate(exdate.getDate()+365);
        document.cookie="consultant_state="+val + ";expires="+exdate.toGMTString()+";path=/";
    }
</script>
<div class="b-consultant " >
    <span class="b-consultant__toggler"></span>
    <span class="b-consultant__body">
    	<a class="b-consultant__link" target="_blank" href="/help/?all">� ��� ���� ������?</a>
    </span>
</div>


            <div class="header">
            <div class="b-header">
		<div class="b-header__tel">
			<div class="b-header__tel-ic b-header__tel-ic_green"></div>
			<big class="b-header__tel-number">8-800-555-33-14</big>
			<div class="b-header__txt">������ ���������</div> 
		</div>
		<!--
		<div class="b-header__tel">
			<div class="b-header__tel-ic b-header__tel-ic_green"></div>
			<big class="b-header__tel-number">8-800-555-33-14</big>
			<div class="b-header__txt">��������� ��� ������</div> 
		</div>
		-->
    		
		<a class="b-header__link b-header__link_logo" href="/"><img class="b-header__logo" src="/images/logo.png" alt="Free-lance.ru" /></a>
		
		
    
</div>


            
<script type="text/javascript">
var notification_delay = '300000';
var prj_check_delay = '600000';
</script>






<div class="b-userbar">
    <div class="b-userbar__top b-userbar__top_free">
        <b class="b-userbar__b1"></b>
        <b class="b-userbar__b2"></b> 
        <ul class="b-userbar__toplist">
            <li class="b-userbar__login">
                <a class="b-userbar__toplink" href="/users/jb_admin">jb_admin</a>
                                    [<a class="b-userbar__toplink" href="/siteadmin/" title="�������������">�</a>]
                            </li>
			<li class="b-userbar__account">
                            <a class="b-userbar__toplink" href="/bill/"><i class="b-userbar__icacc"></i><b class="b-userbar__bold">��� ����:</b> 0  FM</a>
            </li>
            
                        <li id="new_offers_messages" class="b-userbar__projects "><a class="b-userbar__toplink" href="/proj/?p=list"><img class="b-userbar__prjic b-userbar__prjic_hide" src="/css/block/b-userbar/b-userbar__prjfree.gif" alt="" width="16" height="16" /><i class="b-userbar__icprj"></i>�������</a></li>
                        
                        <li class="b-userbar__sbr">
				<a class="b-userbar__toplink" href="/norisk2/">
				<img class="b-userbar__sbric b-userbar__sbric_hide" src="/css/block/b-userbar/b-userbar__sbrfree.gif" alt="" width="24" height="24" /><i class="b-userbar__icsbr"></i>���</a>
            </li>
            			
            <li class="b-userbar__exit">
                <form id="___logout_frm___" method="post" action="/">
                	<div>
                    	<input type="hidden" value="logout" name="action" />
                    	<input class="b-userbar__exitbtn" type="submit" value="�����" title="�����" alt="�����" />
                    </div>
                </form>
            </li>
			
            			
			
			
			
        </ul>
    </div>
    <div class="b-userbar__bot">
        <ul class="b-userbar__botlist">
            <li class="b-userbar__settings"> <a class="b-userbar__link" href="/users/jb_admin/setup/"><i class="b-userbar__icset"></i>���������</a> </li>
                                    <li class="b-userbar__pro"><a class="b-userbar__link" href="/payed/">������ <img class="b-userbar__icpro" src="/images/icons/f-pro.png" alt="pro" /></a></li>
            			
                        <li class="b-userbar__message">
                <a id="userbar_link_msgs" class="b-userbar__link b-userbar__link_green" href="/contacts/"> 
                    <img class="b-userbar__mess " src="/css/block/b-userbar/b-userbar__mess.gif" alt="" width="15" height="15" /><i class="b-userbar__icmess b-userbar__icmess_hide" ></i>
                    <span id="userbar_message">3 ����� ���������</span>
                </a>
            </li>
            
            <li class="b-userbar__services"><a class="b-userbar__link" href="/bill/buy/">������� ������</a></li>
            <li class="b-userbar__stat"><a class="b-userbar__link" href="/promotion/"><i class="b-userbar__icstat"></i>����������</a></li>
            <li class="b-userbar__lenta"><a class="b-userbar__link" href="/lenta/"><i class="b-userbar__iclenta"></i>�����</a></li>
            <li class="b-userbar__drafts"><a class="b-userbar__link" href="/drafts/"><i class="b-userbar__icdrafts"></i>���������  (12)</a></li>
        </ul>
        <b class="b-userbar__b3"></b>
        <b class="b-userbar__b4"></b>
    </div>
</div>











            </div>
            <div class="b-menu b-menu_main">

	
    	
    <ul class="b-menu__list b-menu__list_right" ><li class="b-menu__item b-menu__item_first ">
							<a class="b-menu__link" href="/blogs/">�����</a>
					</li><li class="b-menu__item ">
							<a class="b-menu__link" href="/commune/">����������</a>
					</li>

                <li class="b-menu__item ">
							<a class="b-menu__link" href="/articles/">������ � ��������</a>
					</li>
        
        <li class="b-menu__item b-menu__item_last ">
							<a class="b-menu__link" href="/help/?all">������</a>
					</li>

        
    </ul>
    <ul class="b-menu__list"><li class="b-menu__item b-menu__item_first ">
							<a class="b-menu__link" href="/">�������</a>
					</li><script type="text/javascript"></script><li class="b-menu__item b-menu__item_last ">
							<a class="b-menu__link" href="/service/">������</a>
					</li>
    </ul>
</div>




                                    <div class="body c">
                <div class="main c">
                    <a name="top"></a>
                    <div class="admin">
    <h2>�����������������</h2>
    <div class="lm-col">
        <div class="admin-menu">

    <h3>�������������</h3>
        
                - ��������<br/>
                -- <a class="blue" href="/siteadmin/admin_log/?site=log">����� ��������</a><br/>
                        -- <a class="blue" href="/siteadmin/admin_log/?site=user">����������</a><br/>
                        -- <a class="blue" href="/siteadmin/admin_log/?site=proj">������� � ��������</a><br/>
        -- <a class="blue" href="/siteadmin/admin_log/?site=offer">�����������</a><br/>
                
                <br/>- <a class="blue" href="/siteadmin/user_search/">������������</a><br/>
        -- <a class="blue" href="/siteadmin/gray_ip">����� ������ IP</a><br/>
        -- <a href="/siteadmin/ban-razban/?mode=users" class="blue">������������</a><br/>
        -- <a href="/siteadmin/suspicious-users/" class="blue">�������������� ������������ (19952)</a><br/>
        -- <a href="/siteadmin/suspicious-ip/" class="blue">�������������� IP</a><br/>
        -- <a href="/siteadmin/users/" class="blue">������������ (��� ����)</a><br/>
        -- <a href="/siteadmin/unreads/" class="blue">������������� ���������</a><br/>
                
                <br/>- ������<br/>
                -- <a class="blue" href="/siteadmin/ban-razban/?mode=complain">������ �� �������</a><br/>
        -- <a class="blue" href="/siteadmin/ban-razban/?mode=offers">������ �� �����������</a><br/>
                        -- <a class="blue" href="/siteadmin/messages_spam">������ �� ����</a><br/>
                        
        <br/>
        
    	- <a href="/siteadmin/ban-razban/?mode=blogs" class="blue">�����</a><br/>	- <a href="/siteadmin/ban-razban/?mode=projects" class="blue">�������</a><br/>    - <a href="/siteadmin/ban-razban/?mode=commune" class="blue">����������</a><br/>	- <a href="/siteadmin/shopworks/" class="blue">������ � ��������</a><br/>        --- <a href="/siteadmin/shop/" class="blue">������� � ��������</a><br/>        --- <a href="/siteadmin/shopworks/trash/" class="blue">�������</a><br/>	- <a href="/siteadmin/comments/" class="blue">�����������</a><br/>		- <a href="/siteadmin/protest/" class="blue">PRO-����</a><br/>        <br/>
    
    <br/>
        
    
    <h3>�����������������</h3>
    
        - ������ �������<br/>
    --- <a href="/siteadmin/feedback/" class="blue">OC</a><br/>
    --- <a href="/siteadmin/consultants/" class="blue">������������</a><br/><br/>
        
    - <a href="http://stat.free-lance.ru/helpdesk/admin/" class="blue">�������� �����</a><br/>    - <a href="/siteadmin/login-unlock/" class="blue">������� �������������</a><br/>    - <a href="/siteadmin/login/" class="blue">��������� ������</a><br/>    - <a href="/siteadmin/proj_reasons/" class="blue">������� �������� ���.</a><br/>    
        <br/>- ���������<br/>
    -- <a class="blue" href="/siteadmin/admin_log/?site=notice">�����������</a><br/>
    
        <br/>- <a href="/siteadmin/ban-razban/?mode=moders" class="blue">����������</a><br/>
    --- <a class="blue" href="/siteadmin/admin_log/?site=stat">��� ����������</a><br/>
        
        <br/>- <a href="/siteadmin/changepwdlog/" class="blue">��� ����� �������</a><br/>
    - <a href="/siteadmin/rating/" class="blue">�������</a><br/>
    --- <a href="/siteadmin/rating_log/" class="blue">������� ����</a><br/>
        
        <br/>- ����� �������<br/>
    --- <a href="/siteadmin/permissions/?action=group_list" class="blue">������</a><br/>
    --- <a href="/siteadmin/permissions/?action=user_list" class="blue">������������</a><br/><br/>
        
    	  <br/>- ��������������� (���)<br/>
	  --- <a href="/siteadmin/norisk2/?site=docsflow&scheme=1" class="blue">�����</a><br/>
	  --- <a href="/siteadmin/norisk2/?site=docsflow&scheme=2" class="blue">������</a><br/>
	  --- <a href="/siteadmin/norisk2/?site=docsflow&scheme=0" class="blue">���</a><br/>
	  --- <a href="/siteadmin/norisk2/?site=stat" class="blue">����������</a><br/>
	  --- <a href="/siteadmin/norisk2/?site=arbitrage" class="blue">��������</a><br/>
	  --- <a href="/siteadmin/norisk2/?site=docsflow&scheme=-1" class="blue">�����</a><br/>
   	  <br/>
		
		- <a style="color: #666;" href="/siteadmin/paid_advice/">������������</a> (20)<br/>
	     
		<br/>
	  - <a href="/siteadmin/team/" class="blue">������� Free-lance.ru</a><br/><br/>
	  		- <a href="/siteadmin/manager/" class="blue">������ ��������</a><br/><br/>
				- <a href="/siteadmin/profsavgcost/" class="blue">��������� (������� ����)</a><br/><br/>
	
		- <a href="/siteadmin/blogs/" class="blue">�����</a><br/><br/>
	
	  - <a href="/siteadmin/commune/" class="blue">����������</a><br/><br/>
  
  - <a href="/siteadmin/catalog/" class="blue">�������</a><br/><br/>
	
		- <a href="/siteadmin/vipusers/" class="blue">VIP-������������</a><br/><br/>
			- <a href="/siteadmin/banners/" class="blue">�������</a><br/>
	--- <a href="/siteadmin/banners/" class="blue">����� ��������</a><br/>
	--- <a href="/siteadmin/banners/clients.php" class="blue">������� � ��������</a><br/>
	--- <a href="/siteadmin/banners/clients.php?status=1" class="blue">��������</a><br/>
	--- <a href="/siteadmin/banners/calendar.php" class="blue">���������</a><br/><br/>
        
    
		- <a href="/siteadmin/admin/" class="blue">������������� (����)</a><br/>
    - <a href="/siteadmin/mailer/" class="blue">����� ��������</a><br/><br/>
        	- <a href="/siteadmin/stats/" class="blue">����������</a><br/><br/>
		- <a href="/siteadmin/fpstat/" class="blue">���������� ������� ����</a><br/><br/>
	
			
	- <a href="/siteadmin/masssending/" class="blue">������ �� �������� �� �������� (80)</a><br/><br/>
	
  	 - <a href="/siteadmin/survey/" class="blue">�����</a><br/><br/>
  		- <a href="/siteadmin/topmoney/" class="blue">���-100 �� �������</a><br/><br/>
			- <a href="/siteadmin/search_kwords/" class="blue">����� �� �����</a><br/><br/>
		
		- <a href="/siteadmin/projects/?page=exrates" class="blue">����� �����</a><br/>
	- <a href="/siteadmin/account/" class="blue">���������� (�����)</a><br/>
	- <a href="/siteadmin/staff/" class="blue">���� ��������</a><br/><br/>
		
		- <a href="/siteadmin/bank/" class="blue">������</a><br/>
	--- <a href="/siteadmin/bank_payments/" class="blue">���������� ����</a><br/>
		
	--- <a href="/siteadmin/banklm/" class="blue">������ ��������</a><br/>
	--- <a href="/siteadmin/alpha/" class="blue">�����-����</a><br/>
    --- <a href="/siteadmin/bankpf/" class="blue">������ �����������</a><br/><br/>

	- <a href="/siteadmin/transfers/" class="blue">�������� � �������</a><br/><br/>

        
- <a href="/siteadmin/income/" class="blue">���������� �����</a><br/><br/>- <a href="/siteadmin/income2/" class="blue">������� ������</a><br/><br/>- <a href="/siteadmin/askmanager/" class="blue">������ ��������</a><br/><br/>
- <a href="/siteadmin/contacts" class="blue">��������</a><br/><br/>
- HeadHunter<br/>
--- <a href="/siteadmin/hh/?site=catalog" class="blue">�������</a><br/>
--- <a href="/siteadmin/hh/?site=currency" class="blue">������</a><br/>
--- <a href="/siteadmin/hh/?site=regions" class="blue">�������</a><br/><br/>

- ��������� ��-��<br/>
--- <a href="/siteadmin/ban_promo/" class="blue">����� �������</a><br/>
--- <a href="/siteadmin/quiz/" class="blue">QUIZ MS</a><br/>
</div>    </div>
    <div class="r-col">
        <div class="ban-razban">
            
<script type="text/javascript" charset="UTF-8">
/* <![CDATA[ */
try { if (undefined == xajax.config) xajax.config = {}; } catch (e) { xajax = {}; xajax.config = {}; };
xajax.config.requestURI = "/xajax/mailer.server.php";
xajax.config.statusMessages = false;
xajax.config.waitCursor = true;
xajax.config.version = "xajax 0.5 rc1";
xajax.config.legacy = false;
xajax.config.defaultMode = "asynchronous";
xajax.config.defaultMethod = "POST";
/* ]]> */
</script>
<script type="text/javascript" charset="UTF-8">
var U_TOKEN_KEY = "1e7d9663b79a0f4afb0a5ca89980a52d";
</script>
<script type="text/javascript" src="/xajax/xajax_js/xajax_core.js?v=" charset="UTF-8"></script>

<script type='text/javascript' charset='UTF-8'>
/* <![CDATA[ */
xajax_setStatusSending = function() { return xajax.request( { xjxfun: 'setStatusSending' }, { parameters: arguments } ); };
xajax_recalcRecipients = function() { return xajax.request( { xjxfun: 'recalcRecipients' }, { parameters: arguments } ); };
xajax_GetCitysByCid = function() { return xajax.request( { xjxfun: 'GetCitysByCid' }, { parameters: arguments } ); };
/* ]]> */
</script>
<script type="text/javascript" >
var sregtype = new Array();
   
    sregtype[2] = ['������ �����������', '������ �������', '������ �����', '������ �������', '������ �������', '������ �������', '������ �����������'];
</script>

<div class="b-layout">	
    <h2 class="b-layout__title b-layout__title_padbot_30">����� ��������&#160;&#160;&#160;<a class="b-layout__link b-layout__link_fontsize_13" href="/siteadmin/mailer/">��� ��������</a></h2>
             <form method="post" enctype="multipart/form-data" id="create_form" name="create_mailer_form">
        <input type="hidden" name="action" id="action" value="edit">
        <input type="hidden" name="in_draft" id="draft" value="0">
                    <input type="hidden" name="id" value="8">
            <input type="hidden" name="id_filter_frl" value="3">
            <input type="hidden" name="id_filter_emp" value="3">
                <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt">���� ������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo">
                        <div class="b-combo__input">
                            <input id="c1" class="b-combo__input-text" name="subject" type="text" size="80" value="hyjthdg" />
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_40" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt">����� ������</div>
                </td>
                <td class="b-layout__right">
                    <textarea class="wysiwyg" name="message" cols="80" rows="5"></textarea>

                                                            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11"><a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bold b-layout__link_bordbot_dot_000" href="#">%USER_NAME%</a> � ��� ������������</div>    
                                        <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11"><a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bold b-layout__link_bordbot_dot_000" href="#">%USER_SURNAME%</a> � ������� ������������</div>    
                                        <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11"><a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bold b-layout__link_bordbot_dot_000" href="#">%USER_LOGIN%</a> � ����� ������������</div>    
                                        <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11"><a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bold b-layout__link_bordbot_dot_000" href="#">%URL_PORTFOLIO%</a> � ������ �� ���������</div>    
                                                            <div id="attachedfiles" class="b-fon b-fon_width_full"></div>
                  
                    
                    										
                </td>
            </tr>
        </table>

        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_5" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">&#160;</td>
                <td class="b-layout__right">
                    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15">���������� &mdash; <span id="all_recipients_count">109049</span> &#160;&#160;<a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="calcRecpient();">�����������</a></div>
                </td>
            </tr>
        </table>

        <div class="b-layout__txt b-layout__txt_margleft_130 b-layout__txt_padbot_5 b-username b-check">
            <input id="emp_check1" class="b-check__input" name="filter_emp" type="checkbox" value="1" checked/>
            <label class="b-check__label b-check__label_fontsize_13" for="emp_check1">
                <span class="b-username__role b-username__role_emp"></span>
                <span class="b-username__txt b-username__txt_color_6db335">������������</span> &mdash; <span id="emp_recipients_count">30284</span>
            </label>
            <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padleft_5 b-layout__txt_top_-1">
                <span class="b-layout__ygol  b-layout__ygol_hide"></span>
                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8 show-filter" href="#">�������� �������</a>
            </span>
        </div>
        <div id="filter_employer" class="b-layout__inner b-layout__inner_bordtop_c6 b-layout__inner_bordbot_c6 b-layout__inner_margbot_30 b-layout__inner_padtb_20 ">
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">�������</div>
            </td>
            <td class="b-layout__right">
                <div class="b-check b-check_padbot_10">
                    <input id="check5" class="b-check__input" name="etype_account[0]" type="checkbox" value="1" />
                    <label class="b-check__label b-check__label_fontsize_13" for="check5">���������������� <img src="/images/icons/e-pro.png" alt=""  /></label>
                </div>
                <div class="b-check b-check_padbot_20">
                    <input id="check6" class="b-check__input" name="etype_account[1]" type="checkbox" value="1" />
                    <label class="b-check__label b-check__label_fontsize_13" for="check6">���������</label>
                </div>
            </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">�������</div>
            </td>
            <td class="b-layout__right">
                <div class="b-check b-check_padbot_10">
                    <input id="check7" class="b-check__input" name="etype_profile[0]" type="checkbox" value="1" />
                    <label class="b-check__label b-check__label_fontsize_13" for="check7">��������</label>
                </div>
                <div class="b-check b-check_padbot_20">
                    <input id="check8" class="b-check__input" name="etype_profile[1]" type="checkbox" value="1" />
                    <label class="b-check__label b-check__label_fontsize_13" for="check8">������</label>
                </div>
            </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130 b-layout__left_valign_middle">
                <div class="b-layout__txt">���������������</div>
            </td>
            <td class="b-layout__right">
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                        <input id="c3" class="b-combo__input-text" name="efrom_regdate" type="text" size="80"  readonly="readonly" value=""/>
                        <span class="b-combo__arrow-date"></span>
                    </div>
                </div>
                <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                        <input id="c3" class="b-combo__input-text" name="eto_regdate" type="text" size="80"  readonly="readonly" value=""/>
                        <span class="b-combo__arrow-date"></span>
                    </div>
                </div>
                <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;�� �����</span>
            </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130 b-layout__left_valign_middle">
                <div class="b-layout__txt">��������� �����</div>
            </td>
            <td class="b-layout__right">
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                        <input id="c3" readonly="readonly" class="b-combo__input-text" name="efrom_lastvisit" type="text" size="80"  value=""/>
                        <span class="b-combo__arrow-date"></span>
                    </div>
                </div>
                <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                        <input id="c3" readonly="readonly" class="b-combo__input-text" name="eto_lastvisit" type="text" size="80"  value=""/>
                        <span class="b-combo__arrow-date"></span>
                    </div>
                </div>
                <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;�� �����</span>
            </td>
        </tr>
    </table>
        <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings ">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('efinance').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('efinance').set('value', 1);">�������</a>
    </div>
        <input type="hidden" id="efinance" name="efinance" value="0">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 b-fon_hide">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('efinance').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">�������</div>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">����� �� �����</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135 ">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="efinance_money" type="text" size="80"  value="" />
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;FM</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">���������<br />��������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="efinance_spend[0]" type="text" size="80"  
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="efinance_spend[1]" type="text" size="80"  
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;�� �����</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">���������<br />����������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="efinance_deposit[0]" type="text" size="80"  
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="efinance_deposit[1]" type="text" size="80"  
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;�� �����</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">������<br />���������� �����</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_5">
                        <input id="check13" class="b-check__input" name="efinance_method_deposit[0]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check13">������.������</label>
                    </div>
                    <div class="b-check b-check_padbot_5">
                        <input id="check14" class="b-check__input" name="efinance_method_deposit[1]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check14">Webmoney</label>
                    </div>
                    <div class="b-check b-check_padbot_5">
                        <input id="check15" class="b-check__input" name="efinance_method_deposit[2]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check15">���</label>
                    </div>
                    <div class="b-check">
                        <input id="check16" class="b-check__input" name="efinance_method_deposit[3]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check16">���������� �������</label>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>        
        <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings b-layout__txt_hide">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('ebuying').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('ebuying').set('value', 1);">�������</a>
    </div>
        <input type="hidden" id="ebuying" name="ebuying" value="1">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 ">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('ebuying').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">�������</div>
        <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    &#160;
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_5">
                        <input id="check17" class="b-check__input" name="ebuying_buying[0]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check17">�� �������� �� ����� �������</label>
                    </div>
                    <div class="b-check b-check_padbot_20">
                        <input id="check18" class="b-check__input" name="ebuying_buying[1]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check18">�������� ���� �� ���� �������</label>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">�� ������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="ebuying_period[0]" type="text" size="80"  
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="ebuying_period[1]" type="text" size="80"  
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;�� �� �����</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    &#160;
                </td>
                <td class="b-layout__right i-button" id="buying_types">
                                                                                <span id="buying_type1" class="buying_type">
                        <a class="b-button b-button_margtop_7 b-button_admin_del b-button_float_right " href="javascript:void(0)" onclick="removeBuyingType(this)"></a>
                        <div class="b-select b-select_inline-block">
                            <select class="b-select__select b-select__select_width_160" onchange="addBuyingType(this)" name="ebuying_type_buy[0]">
                                <option value="0">����� �������</option>
                                                                                                <option value="7" >������� ������, 10 FM - ������ �������</option>
                                                                <option value="8" >������� ������</option>
                                                                <option value="9" >�������</option>
                                                                <option value="12" >���������� �����</option>
                                                                <option value="13" >������ ��������</option>
                                                                <option value="15" >������� PRO, ��� �������</option>
                                                                <option value="16" >������� ��� � �������</option>
                                                                <option value="17" >������ �������� � �������, 1 ������</option>
                                                                <option value="23" >�������</option>
                                                                <option value="37" >�������� ����� �� ����� '������ ��� �����'</option>
                                                                <option value="40" >���������� ����� �� �������� ���� �� ������� ������ � �������</option>
                                                                <option value="43" >������� ����� �� ��������� '������ ��� �����'</option>
                                                                <option value="45" >������� �������� �� ��������</option>
                                                                <option value="46" >������� ����� �� ��������</option>
                                                                <option value="48" >������� PRO �� �����</option>
                                                                <option value="52" >������� ��� � �������</option>
                                                                <option value="53" >������� ������</option>
                                                                <option value="54" >�������� ����� � ��������� ����� �� ������� ������ � �������</option>
                                                                <option value="63" >������� �������� ������� - ������� PRO</option>
                                                                <option value="69" >����� ������� ������� �������� � �������</option>
                                                                <option value="70" >��������� ������</option>
                                                                <option value="71" >�������������� ������ � ������� SMS</option>
                                                                <option value="72" >�������, 50 FM - ������ ��������</option>
                                                                <option value="73" >������� ����� ������� ��������</option>
                                                                <option value="74" >������� �������������</option>
                                                                <option value="76" >������� PRO �� ������</option>
                                                                <option value="77" >�������������� �����</option>
                                                                <option value="78" >�������� ����� �� ����� '������ ��� �����'</option>
                                                                <option value="79" >������� ����� �� '������ ��� �����'</option>
                                                                <option value="82" >������ ������� ���������</option>
                                                                <option value="83" >����� ������� �������� � �������</option>
                                                                <option value="84" >���������� �� �������� �������� � �������</option>
                                                                <option value="85" >���������� �� �������� ��������, ���������� ��������, � �������</option>
                                                                <option value="86" >������� �������</option>
                                                                <option value="87" >������� ������ ������ - 10 FM</option>
                                                                <option value="88" >�������, 25 FM - ������ ��������</option>
                                                                <option value="90" >������� PRO �� ����� � ������� (���������� ����� �����-���� �� ����� �� 1000 ������)</option>
                                                                <option value="91" >������� ��� ���������� ����� �� 2000 WMR</option>
                                                                <option value="92" >������� ��� ���������� ����� �� 1000 WMR</option>
                                                                <option value="93" >������� ��� ���������� ����� �� 5000 WMR</option>
                                                                <option value="94" >������ ����������� ���-�������</option>
                                                                <option value="95" >������� ��� ���������� ����� ����� ��������� ��������� �� 2000 ������</option>
                                                                <option value="96" >������� ��� ���������� ����� ����� ��������� ��������� �� 1000 ������</option>
                                                                <option value="97" >������� ��� ���������� ����� ����� ��������� ��������� �� 5000 ������</option>
                                                                <option value="99" >������� ��� ���������� ����� ����� ����������� ������ �� 2000 ������</option>
                                                                <option value="100" >������� ��� ���������� ����� ����� ����������� ������ �� 1000 ������</option>
                                                                <option value="101" >������� ��� ���������� ����� ����� ����������� ������ �� 5000 ������</option>
                                                                <option value="103" >������� ������, 20 FM - ������ ������� (�� ���)</option>
                                                                <option value="104" >�������, 35 FM - ������ �������� (�� ���)</option>
                                                                <option value="105" >������� ������ ������ - 20 FM (�� ���)</option>
                                                                <option value="106" >������� (�� ���)</option>
                                                                <option value="107" >������ ������������</option>
                                                                                            </select>
                        </div>
                        <span class="b-layout__txt">&#160;&#215;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_25">
                                <input id="c3" readonly="readonly" class="b-combo__input-text" name="ebuying_count_buy[0][0]" type="text" size="80" value="0"/>
                            </div>
                        </div>
                        <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_25">
                                <input id="c3" readonly="readonly" class="b-combo__input-text" name="ebuying_count_buy[0][1]" type="text" size="80" value="0"/>
                            </div>
                        </div>
                        <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;�� �����&#160;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_50">
                                <input id="c3" readonly="readonly" class="b-combo__input-text" name="ebuying_sum[0][0]" type="text" size="80" value="0"/>
                            </div>
                        </div>
                        <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_50">
                                <input id="c3" readonly="readonly" class="b-combo__input-text" name="ebuying_sum[0][1]" type="text" size="80" value="0"/>
                            </div>
                        </div>
                        <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;FM</span>
                        <br/><br/>
                    </span>
                                                        </td>
            </tr>
        </table>
    </div>
</div>        
    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings b-layout__txt_hide">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('eproject').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('eproject').set('value', 1);">�������</a>
    </div>
    <input type="hidden" id="eproject" name="eproject" value="1">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 ">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('eproject').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">�������</div>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">�� ������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_period[0]" type="text" size="80"  
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_period[1]" type="text" size="80"  
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;�� �� �����</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">������ ��������<br />������ ����</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_created[0]" type="text" size="80" value="0"/>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_created[1]" type="text" size="80" value="0"/>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;����� ����������</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">���-����</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_freelance[0]" type="text" size="80" value="0"/>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_freelance[1]" type="text" size="80" value=""/>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;����� ����������</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">������ ��� <img src="/images/icons/f-pro.png" alt="" /></div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_only_pro[0]" type="text" size="80" value="0"/>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3"  readonly="readonly" class="b-combo__input-text" name="eproject_only_pro[1]" type="text" size="80" value="0"/>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;����� ����������</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">� ����</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_in_office[0]" type="text" size="80" value="0"/>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_in_office[1]" type="text" size="80" value="0"/>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;����� ����������</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">��������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_konkurs[0]" type="text" size="80" value="0"/>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_konkurs[1]" type="text" size="80" value="0"/>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;����� ����������</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">������ �������<br />�������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_budget[0]" type="text" size="80" value="0" />
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_budget[1]" type="text" size="80" value="0"/>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;�����</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">���. ������<br />���� ��������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_sum_budget[0]" type="text" size="80" value="0" />
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_sum_budget[1]" type="text" size="80" value="0" />
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;�����</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">������� ���-��<br />���. �� �������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_avg_answer[0]" type="text" size="80" value="0" />
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="eproject_avg_answer[1]" type="text" size="80" value="0" />
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;����� ����������</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">��������� ��<br />������������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_5">
                        <input id="check19" class="b-check__input" name="eproject_executor[0]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check19">�������� ������</label>
                    </div>
                    <div class="b-check b-check_padbot_5">
                        <input id="check20" class="b-check__input" name="eproject_executor[1]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check20">��������</label>
                    </div>
                    <div class="b-check b-check_padbot_20">
                        <input id="check21" class="b-check__input" name="eproject_executor[2]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check21">�������</label>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_10" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt">�������������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select">
                        <select name="eproject_spec" class="b-select__select b-select__select_width_300">
                            <option value="0">�����</option>
                                                                                    <option value="1" >����������</option>
                                                        <option value="2" >���������� ������</option>
                                                        <option value="3" >������</option>
                                                        <option value="18" >���</option>
                                                        <option value="5" >����������������</option>
                                                        <option value="6" >����������� (SEO)</option>
                                                        <option value="17" >����������</option>
                                                        <option value="4" >����</option>
                                                        <option value="8" >������</option>
                                                        <option value="7" >��������</option>
                                                        <option value="9" >3D �������</option>
                                                        <option value="19" >��������/��������������</option>
                                                        <option value="10" >����������</option>
                                                        <option value="11" >�����/�����</option>
                                                        <option value="12" >�������/���������</option>
                                                        <option value="16" >���������� ���</option>
                                                        <option value="14" >�����������/��������</option>
                                                        <option value="20" >����������</option>
                                                        <option value="13" >����������</option>
                                                        <option value="22" >��������</option>
                                                        <option value="0" selected>������</option>
                                                                                </select>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>    
    <div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_margleft_130 i-button show-settings b-layout__txt_hide">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('emassend').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('emassend').set('value', 1);">��������</a>
    </div>
	<input type="hidden" id="emassend" name="emassend" value="1">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 ">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('emassend').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">��������</div>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt">�������������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select">
                        <select name="massend_spec" class="b-select__select b-select__select_width_300">
                            <option value="0">�����</option>
                                                                                    <option value="1" >����������</option>
                                                        <option value="2" >���������� ������</option>
                                                        <option value="3" >������</option>
                                                        <option value="18" >���</option>
                                                        <option value="5" >����������������</option>
                                                        <option value="6" >����������� (SEO)</option>
                                                        <option value="17" >����������</option>
                                                        <option value="4" >����</option>
                                                        <option value="8" >������</option>
                                                        <option value="7" >��������</option>
                                                        <option value="9" >3D �������</option>
                                                        <option value="19" >��������/��������������</option>
                                                        <option value="10" >����������</option>
                                                        <option value="11" >�����/�����</option>
                                                        <option value="12" >�������/���������</option>
                                                        <option value="16" >���������� ���</option>
                                                        <option value="14" >�����������/��������</option>
                                                        <option value="20" >����������</option>
                                                        <option value="13" >����������</option>
                                                        <option value="22" >��������</option>
                                                        <option value="0" selected>������</option>
                                                                                </select>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full  b-layout__table_margbot_10" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">�����������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="massend_recipient[0]" type="text" size="80"  value="0"/>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="massend_recipient[1]" type="text" size="80"  value="0"/>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;����� ����������</span>
                </td>
            </tr>
        </table>
    </div>
</div>			
				
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">���</div>
            </td>
            <td class="b-layout__right">
                <div class="b-check b-check_padbot_10 b-check_padtop_4">
                    <input id="check9" class="b-check__input" name="etype_sex[0]" type="checkbox" value="1" />
                    <label class="b-check__label b-check__label_fontsize_13" for="check9">�������</label>
                </div>
                <div class="b-check">
                    <input id="check10" class="b-check__input" name="etype_sex[1]" type="checkbox" value="1" />
                    <label class="b-check__label b-check__label_fontsize_13" for="check10">�������</label>
                </div>
            </td>
        </tr>
    </table>
</div><!-- b-layout__inner -->	

        <div class="b-layout__txt b-layout__txt_margleft_130 b-layout__txt_padbot_5 b-username b-check">
            <input id="frl_check2" class="b-check__input" name="filter_frl" type="checkbox" value="1" checked/>
            <label class="b-check__label b-check__label_fontsize_13" for="frl_check2">
                <span class="b-username__role b-username__role_frl"></span>
                <span class="b-username__txt b-username__txt_color_fd6c30">���-�������</span> &mdash; <span id="frl_recipients_count">78765</span>
            </label> 
            <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padleft_5 b-layout__txt_top_-1">
                <span class="b-layout__ygol  b-layout__ygol_hide"></span>
                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8 show-filter" href="#">�������� �������</a>
            </span>
        </div>
        <div id="filter_freelancer" class="b-layout__inner b-layout__inner_bordtop_c6 b-layout__inner_bordbot_c6 b-layout__inner_margbot_30 b-layout__inner_padtb_20 ">
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt">�������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_10">
                        <input id="check5" class="b-check__input" name="ftype_account[0]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check5">���������������� <img src="/images/icons/f-pro.png" alt=""  /></label>
                    </div>
                    <div class="b-check b-check_padbot_20">
                        <input id="check6" class="b-check__input" name="ftype_account[1]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check6">���������</label>
                    </div>
                </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt">�������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_10">
                        <input id="check7" class="b-check__input" name="ftype_profile[0]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check7">��������</label>
                    </div>
                    <div class="b-check b-check_padbot_20">
                        <input id="check8" class="b-check__input" name="ftype_profile[1]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check8">������</label>
                    </div>
                </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt">���������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_10">
                        <input id="check11" class="b-check__input" name="ftype_portfolio[0]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check11">���� ���� �� ���� ������</label>
                    </div>
                    <div class="b-check b-check_padbot_20">
                        <input id="check12" class="b-check__input" name="ftype_portfolio[1]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check12">��� �� ����� ������</label>
                    </div>
                </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130 b-layout__left_valign_middle">
                <div class="b-layout__txt">���������������</div>
            </td>
            <td class="b-layout__right">
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                        <input id="c3"  class="b-combo__input-text" name="ffrom_regdate" type="text" size="80"  value="03.05.2002" />
                        <span class="b-combo__arrow-date"></span>
                    </div>
                </div>
                <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                        <input id="c3" readonly="readonly" class="b-combo__input-text" name="fto_regdate" type="text" size="80"  value=""/>
                        <span class="b-combo__arrow-date"></span>
                    </div>
                </div>
                <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5 b-layout__txt_fontsize_11">&#160;&#160;�� �����</span>
            </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130 b-layout__left_valign_middle">
                <div class="b-layout__txt">��������� �����</div>
            </td>
            <td class="b-layout__right">
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                        <input id="c3" readonly="readonly" class="b-combo__input-text" name="ffrom_lastvisit" type="text" size="80"  value=""/>
                        <span class="b-combo__arrow-date"></span>
                    </div>
                </div>
                <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                        <input id="c3" readonly="readonly" class="b-combo__input-text" name="fto_lastvisit" type="text" size="80"  value=""/>
                        <span class="b-combo__arrow-date"></span>
                    </div>
                </div>
                <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5 b-layout__txt_fontsize_11">&#160;&#160;�� �����</span>
            </td>
        </tr>
    </table>
    
        <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings ">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('ffinance').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('ffinance').set('value', 1);">�������</a>
    </div>
    	<input type="hidden" id="ffinance" name="ffinance" value="0">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 b-fon_hide">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('ffinance').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">�������</div>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">����� �� �����</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135 ">
                            <input id="c3" readonly="readonly" class="b-combo__input-text" name="ffinance_money" type="text" size="80"  value="" />
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;FM</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">���������<br />��������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" class="b-combo__input-text" name="ffinance_spend[0]" type="text" size="80"  value="" readonly />
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" class="b-combo__input-text" name="ffinance_spend[1]" type="text" size="80"  
                                   value="" readonly />
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;�� �����</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">���������<br />����������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" class="b-combo__input-text" name="ffinance_deposit[0]" type="text" size="80"  readonly
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" class="b-combo__input-text" name="ffinance_deposit[1]" type="text" size="80"  readonly
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;�� �����</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">������<br />���������� �����</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_5">
                        <input id="check13" class="b-check__input" name="ffinance_method_deposit[0]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check13">������.������</label>
                    </div>
                    <div class="b-check b-check_padbot_5">
                        <input id="check14" class="b-check__input" name="ffinance_method_deposit[1]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check14">Webmoney</label>
                    </div>
                    <div class="b-check b-check_padbot_5">
                        <input id="check15" class="b-check__input" name="ffinance_method_deposit[2]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check15">���</label>
                    </div>
                    <div class="b-check">
                        <input id="check16" class="b-check__input" name="ffinance_method_deposit[3]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check16">���������� �������</label>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>			
				
		
        <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings b-layout__txt_hide">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('fbuying').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('fbuying').set('value', 1);">�������</a>
    </div>
	    <input type="hidden" id="fbuying" name="fbuying" value="1">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 ">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('fbuying').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">�������</div>
        <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    &#160;
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_5">
                        <input id="check17" class="b-check__input" name="fbuying_buying[0]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check17">�� �������� �� ����� �������</label>
                    </div>
                    <div class="b-check b-check_padbot_20">
                        <input id="check18" class="b-check__input" name="fbuying_buying[1]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check18">�������� ���� �� ���� �������</label>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">�� ������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" class="b-combo__input-text" name="fbuying_period[0]" type="text" size="80"  readonly
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" class="b-combo__input-text" name="fbuying_period[1]" type="text" size="80"  readonly
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;�� �� �����</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    &#160;
                </td>
                <td class="b-layout__right i-button" id="buying_types">
                                                                                <span id="buying_type1" class="buying_type">
                        <a class="b-button b-button_margtop_7 b-button_admin_del b-button_float_right " href="javascript:void(0)" onclick="removeBuyingType(this)"></a>
                        <div class="b-select b-select_inline-block">
                            <select class="b-select__select b-select__select_width_160" onchange="addBuyingType(this)" name="fbuying_type_buy[0]">
                                <option value="0">����� �������</option>
                                                                                                <option value="10" >������ ��������, 1 ������</option>
                                                                <option value="12" >���������� �����</option>
                                                                <option value="13" >������ ��������</option>
                                                                <option value="15" >������� PRO, ��� �������</option>
                                                                <option value="16" >������� ��� � �������</option>
                                                                <option value="17" >������ �������� � �������, 1 ������</option>
                                                                <option value="19" >������� ����������, ����� �������</option>
                                                                <option value="20" >������� ����������, ���������� ��������</option>
                                                                <option value="21" >��������� ������� �������� ����������</option>
                                                                <option value="23" >�������</option>
                                                                <option value="36" >�������������� �����</option>
                                                                <option value="38" >������� ����� �� '������ ��� �����'</option>
                                                                <option value="43" >������� ����� �� ��������� '������ ��� �����'</option>
                                                                <option value="45" >������� �������� �� ��������</option>
                                                                <option value="46" >������� ����� �� ��������</option>
                                                                <option value="47" >�������� ������� PRO</option>
                                                                <option value="48" >������� PRO �� �����</option>
                                                                <option value="49" >������� PRO �� 3 ������</option>
                                                                <option value="50" >������� PRO �� 6 �������</option>
                                                                <option value="51" >������� PRO �� 12 �������</option>
                                                                <option value="52" >������� ��� � �������</option>
                                                                <option value="53" >������� ������</option>
                                                                <option value="55" >����� ������� �����</option>
                                                                <option value="61" >������� ����� �� ������</option>
                                                                <option value="62" >������� ����� �� ������ ����� SMS</option>
                                                                <option value="63" >������� �������� ������� - ������� PRO</option>
                                                                <option value="64" >������� �������� ������� - ������� ����������</option>
                                                                <option value="65" >������� ����� ������� ������� ��������</option>
                                                                <option value="66" >������� ��� � ������� �� 3 ������</option>
                                                                <option value="69" >����� ������� ������� �������� � �������</option>
                                                                <option value="70" >��������� ������</option>
                                                                <option value="71" >�������������� ������ � ������� SMS</option>
                                                                <option value="73" >������� ����� ������� ��������</option>
                                                                <option value="74" >������� �������������</option>
                                                                <option value="75" >�������� ��������</option>
                                                                <option value="76" >������� PRO �� ������</option>
                                                                <option value="77" >�������������� �����</option>
                                                                <option value="79" >������� ����� �� '������ ��� �����'</option>
                                                                <option value="80" >������� �������������</option>
                                                                <option value="81" >����� �� ������� ������.������</option>
                                                                <option value="82" >������ ������� ���������</option>
                                                                <option value="83" >����� ������� �������� � �������</option>
                                                                <option value="84" >���������� �� �������� �������� � �������</option>
                                                                <option value="85" >���������� �� �������� ��������, ���������� ��������, � �������</option>
                                                                <option value="90" >������� PRO �� ����� � ������� (���������� ����� �����-���� �� ����� �� 1000 ������)</option>
                                                                <option value="91" >������� ��� ���������� ����� �� 2000 WMR</option>
                                                                <option value="93" >������� ��� ���������� ����� �� 5000 WMR</option>
                                                                <option value="94" >������ ����������� ���-�������</option>
                                                                <option value="95" >������� ��� ���������� ����� ����� ��������� ��������� �� 2000 ������</option>
                                                                <option value="96" >������� ��� ���������� ����� ����� ��������� ��������� �� 1000 ������</option>
                                                                <option value="97" >������� ��� ���������� ����� ����� ��������� ��������� �� 5000 ������</option>
                                                                <option value="98" >��������� ����������� ������</option>
                                                                <option value="99" >������� ��� ���������� ����� ����� ����������� ������ �� 2000 ������</option>
                                                                <option value="102" >������� ������ �� ������ � ����� � ����������� �������</option>
                                                                <option value="106" >������� (�� ���)</option>
                                                                <option value="107" >������ ������������</option>
                                                                <option value="108" >����������� �����</option>
                                                                                            </select>
                        </div>
                        <span class="b-layout__txt">&#160;&#215;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_25">
                                <input id="c3" class="b-combo__input-text" name="fbuying_count_buy[0][0]" type="text" size="80" value="0"/>
                            </div>
                        </div>
                        <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_25">
                                <input id="c3" class="b-combo__input-text" name="fbuying_count_buy[0][1]" type="text" size="80" value="0"/>
                            </div>
                        </div>
                        <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;�� �����&#160;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_50">
                                <input id="c3" class="b-combo__input-text" name="fbuying_sum[0][0]" type="text" size="80" value="0"/>
                            </div>
                        </div>
                        <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_50">
                                <input id="c3" class="b-combo__input-text" name="fbuying_sum[0][1]" type="text" size="80" value="0"/>
                            </div>
                        </div>
                        <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;FM</span>
                        <br/><br/>
                    </span>
                                                        </td>
            </tr>
        </table>
    </div>
</div>    			
			
    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings ">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('fproject').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('fproject').set('value', 1);">�������</a>
    </div>
	<input type="hidden" id="fproject" name="fproject" value="0">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 b-fon_hide">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('fproject').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">������ �� �������</div>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">�� ������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" class="b-combo__input-text" name="fproject_period[0]" type="text" size="80"  readonly
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" class="b-combo__input-text" name="fproject_period[1]" type="text" size="80"  readonly
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;�� �� �����</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">���-�� �������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text" name="fproject_count[0]" type="text" size="80" value=""/>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text" name="fproject_count[1]" type="text" size="80" value="" />
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;����� ����������</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">������������<br />�������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_5">
                        <input id="check19" class="b-check__input" name="fproject_type[0]" type="checkbox" value="1" />
                        <label class="b-check__label b-check__label_fontsize_13" for="check19">�������� ������</label>
                    </div>
                    <div class="b-check b-check_padbot_5">
                        <input id="check20" class="b-check__input" name="fproject_type[1]" type="checkbox" value="1"  />
                        <label class="b-check__label b-check__label_fontsize_13" for="check20">��������</label>
                    </div>
                    <div class="b-check b-check_padbot_5">
                        <input id="check21" class="b-check__input" name="fproject_type[2]" type="checkbox" value="1"  />
                        <label class="b-check__label b-check__label_fontsize_13" for="check21">�������</label>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>			
    
    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings b-layout__txt_hide">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('fspec').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('fspec').set('value', 1);">�������������</a>
    </div>
	<input type="hidden" id="fspec" name="fspec" value="1">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 ">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('fspec').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">�������������</div>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_10" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt">��������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select">
                        <select name="fspec_orig" class="b-select__select b-select__select_width_300">
                            <option value="0">�����</option>
                                                                                    <option value="1" >����������</option>
                                                        <option value="2" >���������� ������</option>
                                                        <option value="3" >������</option>
                                                        <option value="18" >���</option>
                                                        <option value="5" >����������������</option>
                                                        <option value="6" >����������� (SEO)</option>
                                                        <option value="17" >����������</option>
                                                        <option value="4" >����</option>
                                                        <option value="8" >������</option>
                                                        <option value="7" >��������</option>
                                                        <option value="9" >3D �������</option>
                                                        <option value="19" >��������/��������������</option>
                                                        <option value="10" >����������</option>
                                                        <option value="11" >�����/�����</option>
                                                        <option value="12" >�������/���������</option>
                                                        <option value="16" >���������� ���</option>
                                                        <option value="14" >�����������/��������</option>
                                                        <option value="20" >����������</option>
                                                        <option value="13" >����������</option>
                                                        <option value="22" >��������</option>
                                                        <option value="0" selected>������</option>
                                                                                </select>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_10" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt">��������������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select">
                        <select name="fspec_dspec[0]" class="b-select__select b-select__select_width_300">
                            <option value="0">�����</option>
                                                                                    <option value="1" >����������</option>
                                                        <option value="2" >���������� ������</option>
                                                        <option value="3" >������</option>
                                                        <option value="18" >���</option>
                                                        <option value="5" >����������������</option>
                                                        <option value="6" >����������� (SEO)</option>
                                                        <option value="17" >����������</option>
                                                        <option value="4" >����</option>
                                                        <option value="8" >������</option>
                                                        <option value="7" >��������</option>
                                                        <option value="9" >3D �������</option>
                                                        <option value="19" >��������/��������������</option>
                                                        <option value="10" >����������</option>
                                                        <option value="11" >�����/�����</option>
                                                        <option value="12" >�������/���������</option>
                                                        <option value="16" >���������� ���</option>
                                                        <option value="14" >�����������/��������</option>
                                                        <option value="20" >����������</option>
                                                        <option value="13" >����������</option>
                                                        <option value="22" >��������</option>
                                                        <option value="0" selected>������</option>
                                                                                </select>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>			
				
    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130 i-button show-settings ">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('fblog').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('fblog').set('value', 1);">���������� � ������</a>
    </div>
	<input type="hidden" id="fblog" name="fblog" value="0">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 b-fon_hide">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('fblog').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">���������� � ������</div>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">�� ������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" class="b-combo__input-text" name="fblog_period[0]" type="text" size="80"  readonly
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" class="b-combo__input-text" name="fblog_period[1]" type="text" size="80"  readonly 
                                   value=""/>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;�� �� �����</span>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_10" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120 b-layout__left_valign_middle">
                    <div class="b-layout__txt">������� ������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text" name="fblog_post[0]" type="text" size="80" value="" />
                        </div>
                    </div>
                    <span class="b-layout__txt">&#160;&mdash;&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_135">
                            <input id="c3" class="b-combo__input-text" name="fblog_post[1]" type="text" size="80" value="" />
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;&#160;����� ����������</span>
                </td>
            </tr>
        </table>
    </div>
</div>			
				
    <div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_margleft_130 i-button show-settings b-layout__txt_hide">
        <a class="b-button b-button_poll_plus" href="#" onclick="$('flocation').set('value', 1);"></a>&#160;&#160;
        <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="#" onclick="$('flocation').set('value', 1);">���������</a>
    </div>
	<input type="hidden" id="flocation" name="flocation" value="1">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 ">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('flocation').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">���������</div>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_10" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt">������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select">
                        <select class="b-select__select b-select__select_width_300" id="pf_country" name="country" onChange="updateCitys(this.value)">
                            <option value="">�����</option>
                                                                                    <option value="1" >������</option>
                                                        <option value="2" >�������</option>
                                                        <option value="110" >�������</option>
                                                        <option value="3" >���������</option>
                                                        <option value="4" >�������</option>
                                                        <option value="5" >�����������</option>
                                                        <option value="111" >�������</option>
                                                        <option value="112" >�����</option>
                                                        <option value="113" >�������</option>
                                                        <option value="114" >������</option>
                                                        <option value="6" >��������</option>
                                                        <option value="115" >�������</option>
                                                        <option value="116" >������� � �������</option>
                                                        <option value="7" >���������</option>
                                                        <option value="8" >�������</option>
                                                        <option value="9" >�������</option>
                                                        <option value="117" >����������</option>
                                                        <option value="118" >������</option>
                                                        <option value="119" >���������</option>
                                                        <option value="120" >��������</option>
                                                        <option value="121" >�������</option>
                                                        <option value="10" >��������</option>
                                                        <option value="11" >�����</option>
                                                        <option value="12" >�������</option>
                                                        <option value="122" >�����</option>
                                                        <option value="13" >�������</option>
                                                        <option value="14" >��������</option>
                                                        <option value="123" >�������</option>
                                                        <option value="124" >������ � �����������</option>
                                                        <option value="125" >��������</option>
                                                        <option value="15" >��������</option>
                                                        <option value="126" >������</option>
                                                        <option value="127" >������� ����</option>
                                                        <option value="128" >�������</option>
                                                        <option value="129" >�����</option>
                                                        <option value="130" >�������</option>
                                                        <option value="16" >��������������</option>
                                                        <option value="17" >�������</option>
                                                        <option value="131" >���������</option>
                                                        <option value="132" >��������� �����</option>
                                                        <option value="18" >�������</option>
                                                        <option value="133" >�����</option>
                                                        <option value="134" >������</option>
                                                        <option value="19" >�����</option>
                                                        <option value="135" >������</option>
                                                        <option value="136" >������</option>
                                                        <option value="137" >����</option>
                                                        <option value="20" >���������</option>
                                                        <option value="138" >���������</option>
                                                        <option value="139" >������</option>
                                                        <option value="140" >������-�����</option>
                                                        <option value="21" >��������</option>
                                                        <option value="141" >���������</option>
                                                        <option value="22" >��������</option>
                                                        <option value="23" >�������</option>
                                                        <option value="142" >�������</option>
                                                        <option value="24" >������</option>
                                                        <option value="25" >������</option>
                                                        <option value="26" >�����</option>
                                                        <option value="143" >�������</option>
                                                        <option value="144" >��������</option>
                                                        <option value="145" >������������� ����������</option>
                                                        <option value="28" >������</option>
                                                        <option value="146" >������</option>
                                                        <option value="147" >��������</option>
                                                        <option value="29" >�������</option>
                                                        <option value="30" >�����</option>
                                                        <option value="31" >���������</option>
                                                        <option value="32" >��������</option>
                                                        <option value="33" >����</option>
                                                        <option value="34" >����</option>
                                                        <option value="35" >��������</option>
                                                        <option value="148" >��������</option>
                                                        <option value="36" >�������</option>
                                                        <option value="37" >������</option>
                                                        <option value="149" >�����</option>
                                                        <option value="160" >����</option>
                                                        <option value="150" >����-�����</option>
                                                        <option value="38" >���������</option>
                                                        <option value="151" >��������</option>
                                                        <option value="39" >�������</option>
                                                        <option value="40" >������</option>
                                                        <option value="152" >��������� �������</option>
                                                        <option value="41" >������</option>
                                                        <option value="153" >�����</option>
                                                        <option value="154" >�����</option>
                                                        <option value="42" >����</option>
                                                        <option value="155" >��������</option>
                                                        <option value="156" >��������</option>
                                                        <option value="44" >�����</option>
                                                        <option value="157" >��������</option>
                                                        <option value="158" >������</option>
                                                        <option value="159" >�����</option>
                                                        <option value="161" >�����</option>
                                                        <option value="162" >������</option>
                                                        <option value="46" >�����-����</option>
                                                        <option value="163" >���-�'�����</option>
                                                        <option value="47" >����</option>
                                                        <option value="48" >������</option>
                                                        <option value="49" >����������</option>
                                                        <option value="164" >����</option>
                                                        <option value="50" >������</option>
                                                        <option value="165" >������</option>
                                                        <option value="166" >�������</option>
                                                        <option value="51" >�����</option>
                                                        <option value="53" >�����</option>
                                                        <option value="54" >�����</option>
                                                        <option value="167" >�����������</option>
                                                        <option value="55" >����������</option>
                                                        <option value="168" >��������</option>
                                                        <option value="169" >����������</option>
                                                        <option value="170" >����������</option>
                                                        <option value="171" >�����</option>
                                                        <option value="56" >���������</option>
                                                        <option value="172" >������</option>
                                                        <option value="57" >��������</option>
                                                        <option value="173" >����</option>
                                                        <option value="174" >��������</option>
                                                        <option value="58" >������</option>
                                                        <option value="64" >�������</option>
                                                        <option value="175" >���������� �������</option>
                                                        <option value="59" >�������</option>
                                                        <option value="176" >����������</option>
                                                        <option value="60" >��������</option>
                                                        <option value="61" >�������</option>
                                                        <option value="62" >������</option>
                                                        <option value="63" >��������</option>
                                                        <option value="177" >������</option>
                                                        <option value="178" >�������</option>
                                                        <option value="179" >�����</option>
                                                        <option value="180" >�����</option>
                                                        <option value="181" >�����</option>
                                                        <option value="182" >�������</option>
                                                        <option value="65" >���������� (���������)</option>
                                                        <option value="183" >���������</option>
                                                        <option value="66" >����� ��������</option>
                                                        <option value="67" >��������</option>
                                                        <option value="68" >�.�.�.</option>
                                                        <option value="184" >����</option>
                                                        <option value="69" >������ ���</option>
                                                        <option value="70" >��������</option>
                                                        <option value="185" >�����</option>
                                                        <option value="186" >������</option>
                                                        <option value="187" >����� - ����� ������</option>
                                                        <option value="188" >��������</option>
                                                        <option value="71" >����</option>
                                                        <option value="72" >������</option>
                                                        <option value="73" >����������</option>
                                                        <option value="189" >������-����</option>
                                                        <option value="74" >�������</option>
                                                        <option value="190" >������</option>
                                                        <option value="75" >�������</option>
                                                        <option value="76" >���</option>
                                                        <option value="77" >���������</option>
                                                        <option value="191" >�����</option>
                                                        <option value="192" >���-������</option>
                                                        <option value="193" >���-���� � ��������</option>
                                                        <option value="194" >���������� ������</option>
                                                        <option value="195" >���������</option>
                                                        <option value="196" >����������� �������</option>
                                                        <option value="197" >�������</option>
                                                        <option value="198" >����-������� � ���������</option>
                                                        <option value="109" >������</option>
                                                        <option value="78" >��������</option>
                                                        <option value="79" >�����</option>
                                                        <option value="80" >��������</option>
                                                        <option value="81" >��������</option>
                                                        <option value="199" >���������� �������</option>
                                                        <option value="200" >������</option>
                                                        <option value="201" >�����</option>
                                                        <option value="82" >�������</option>
                                                        <option value="202" >������-�����</option>
                                                        <option value="83" >�����������</option>
                                                        <option value="85" >�������</option>
                                                        <option value="84" >�������</option>
                                                        <option value="203" >��������</option>
                                                        <option value="204" >����</option>
                                                        <option value="205" >�����</option>
                                                        <option value="206" >�������� � ������</option>
                                                        <option value="207" >������</option>
                                                        <option value="86" >�����</option>
                                                        <option value="87" >������������</option>
                                                        <option value="89" >����� � ������</option>
                                                        <option value="90" >������</option>
                                                        <option value="91" >������</option>
                                                        <option value="92" >����������</option>
                                                        <option value="208" >�������</option>
                                                        <option value="209" >�����</option>
                                                        <option value="210" >���������</option>
                                                        <option value="93" >���������</option>
                                                        <option value="94" >�������</option>
                                                        <option value="95" >��������</option>
                                                        <option value="211" >���</option>
                                                        <option value="212" >���</option>
                                                        <option value="108" >����������</option>
                                                        <option value="96" >�����</option>
                                                        <option value="97" >����</option>
                                                        <option value="98" >���������</option>
                                                        <option value="99" >������</option>
                                                        <option value="213" >���-�����</option>
                                                        <option value="100" >�������</option>
                                                        <option value="214" >�������������� ������</option>
                                                        <option value="215" >�������</option>
                                                        <option value="101" >�������</option>
                                                        <option value="216" >�������</option>
                                                        <option value="102" >���</option>
                                                        <option value="217" >����� ������</option>
                                                        <option value="218" >����� �����</option>
                                                        <option value="105" >������</option>
                                                        <option value="106" >������</option>
                                                                                </select>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_10" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt">�����</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select" id="frm_city">
                        <select class="b-select__select b-select__select_width_300" id="pf_city" name="city"> 
                            <option value="">��� ������</option>
                                                    </select>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>    
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">���</div>
            </td>
            <td class="b-layout__right">
                <div class="b-check b-check_padbot_10 b-check_padtop_4">
                    <input id="check9" class="b-check__input" name="ftype_sex[0]" type="checkbox" value="1" />
                    <label class="b-check__label b-check__label_fontsize_13" for="check9">�������</label>
                </div>
                <div class="b-check">
                    <input id="check10" class="b-check__input" name="ftype_sex[1]" type="checkbox" value="1" />
                    <label class="b-check__label b-check__label_fontsize_13" for="check10">�������</label>
                </div>
            </td>
        </tr>
    </table>
</div><!-- b-layout__inner -->
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_30" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt">���������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_10 b-check_padtop_3">
                        <input id="check3" class="b-check__input" name="type_sending[0]" type="checkbox" value="1" checked/>
                        <label class="b-check__label b-check__label_fontsize_13" for="check3">������ ����������</label>
                    </div>
                    <div class="b-check">
                        <input id="check4" class="b-check__input" name="type_sending[1]" type="checkbox" value="1" checked/>
                        <label class="b-check__label b-check__label_fontsize_13" for="check4">������� �� �����</label>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_30" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt">������������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select">
                        <select name="type_regular" class="b-select__select b-select__select_width_220" onchange="selectRegularType(this.value, sregtype);">
                                                        <option value="1" >��� ����������</option>
                                                        <option value="2" selected>�����������</option>
                                                        <option value="3" >����������</option>
                                                        <option value="4" >��������</option>
                                                    </select>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_30 " id="repeat_type" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt">���������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select">
                        <select id="type_send_regular" name="type_send_regular" class="b-select__select b-select__select_width_220">
                                                                                    <option value="1" selected>������ �����������</option>
                                                        <option value="2" >������ �������</option>
                                                        <option value="3" >������ �����</option>
                                                        <option value="4" >������ �������</option>
                                                        <option value="5" >������ �������</option>
                                                        <option value="6" >������ �������</option>
                                                        <option value="7" >������ �����������</option>
                                                                                </select>
                    </div>
                </td>
            </tr>
        </table>

        <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_30" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">���� � �����<br />�����������</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo__hide" id="date_sending">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes no_set_date_on_load use_past_date">
                            <input id="c3" class="b-combo__input-text" name="date_sending" type="text" size="80"  value="" />
                            <label class="b-combo__label" for="c3"></label>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_padtop_3 b-layout__hide" id="str_date_sending">&#160;�&#160;</span>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_width_45">
                            <input id="c1" class="b-combo__input-text" name="time_sending" type="text" size="80" value="12:20"  maxlength="5"/>
                            <label class="b-combo__label" for="c1"></label>
                        </div>
                    </div>
                                                        </td>
            </tr>
        </table>

        <div class="b-buttons b-buttons_padtop_40 b-buttons_padleft_132">
            <a class="b-button b-button_rectangle_color_green"  href="javascript:void(0)" onClick="$('draft').set('value', '0'); $('create_form').submit();">
                <span class="b-button__b1">
                    <span class="b-button__b2">
                        <span class="b-button__txt">��������� � �������</span>
                    </span>
                </span>
            </a>
            &#160;&#160;<a class="b-buttons__link" href="javascript:void(0)" onclick="$('draft').set('value', '1'); $('create_form').submit();">��������� ��� ��������</a>
            <span class="b-buttons__txt">,</span>	
            <a class="b-buttons__link" href="javascript:void(0)" onclick="$('draft').set('value', '1'); $('action').set('value', 'create_and_sendme'); $('create_form').submit();">������� ������� ���</a>	
                        <span class="b-buttons__txt">���</span>	
            <a class="b-buttons__link b-buttons__link_color_c10601" href="javascript:void(0)" onclick="$('action').set('value', 'delete'); $('create_form').submit();">�������</a>
                    </div>
	</form>
</div>	        </div>
    </div>
</div>                </div>
            </div>
                        <div class="i-footer">
	<div class="b-footer">
		<div class="b-footer__top">
						<div class="b-footer__col b-footer__col_user">
			    			    <h4 class="b-footer__h4"><a class="b-footer__link" href="/users/jb_admin"><!--�������� ������� [-->jb_admin<!--]--></a></h4>
			    				<ul class="b-footer__list">
				    					<li class="b-footer__item"><a class="b-footer__link" href="/contacts/">��� ��������</a></li>
															<li class="b-footer__item"><a class="b-footer__link" href="/bill/">��� ����</a></li>
															<li class="b-footer__item"><a class="b-footer__link" href="/proj/?p=list">�������</a></li>
																				<li class="b-footer__item"><a class="b-footer__link" href="/users/jb_admin/setup/">���������</a></li>
										<li class="b-footer__item"><a class="b-footer__link logoutBtn" href="javascript:logout()">�����</a></li>
				</ul>
			</div>
					
		



			<div class="b-footer__col b-footer__col_services">
				<script type="text/javascript">
								document.write('<h4 class="b-footer__h4"><a class="b-footer__link" href="/service/">������</a></h4>');
								</script>
				<ul class="b-footer__list">
														<li class="b-footer__item"><a class="b-footer__link" href="/start/">��������� ��������</a></li>
															<li class="b-footer__item"><a class="b-footer__link" href="/payed/">������� PRO</a></li>
															<li class="b-footer__item"><a class="b-footer__link" href="/firstpage/">������� ����� �� ������� ��������</a></li>
															<li class="b-footer__item"><a class="b-footer__link" href="/firstpage/?catalog">������� ����� � �������� ���-��������</a></li>
															<li class="b-footer__item"><a class="b-footer__link" href="/service/shop/">�������</a></li>
															<li class="b-footer__item"><a class="b-footer__link" href="/norisk2/">������ ��� �����</a></li>
													</ul>
			</div>




			<div class="b-footer__col b-footer__col_project">
								<h4 class="b-footer__h4"><a class="b-footer__link" href="/about/">� �������</a></h4>
								<ul class="b-footer__list">
										<li class="b-footer__item"><a class="b-footer__link" href="/press/adv/">�������</a></li>
															<li class="b-footer__item"><a class="b-footer__link" href="/press/contacts/">��������</a></li>
														</ul>
				<ul class="b-footer__list">
										<li class="b-footer__item"><a class="b-footer__link" href="<?=WDCPREFIX?>/about/documents/appendix_2_regulations.pdf" target="_blank">������� �����</a></li>
															<li class="b-footer__item"><a class="b-footer__link" href="<?=WDCPREFIX?>/about/documents/agreement_site.pdf" target="_blank">���������������� ����������</a></li>
														</ul>
				
				<ul class="b-footer__list">
				    <li class="b-footer__item"><a class="b-footer__link" href="<?=WDCPREFIX?>/about/documents/politika_po_obrabotke_pdn.pdf" target="_blank">�������� �� ��������� ������������ ������</a></li>
				    <li class="b-footer__item"><a class="b-footer__link" href="<?=WDCPREFIX?>/about/documents/polozhenie_po_obespecheniu_bezopasnosti_pdn.pdf" target="_blank">��������� �� ����������� ������������ ������������ ������</a></li>
				</ul>
			</div>

			<div class="b-footer__col b-footer__col_help">
				<script type="text/javascript">
								document.write('<h4 class="b-footer__h4"><a class="b-footer__link" href="/help/">������</a></h4>');
								</script>
				<ul class="b-footer__list">
										<li class="b-footer__item"><a class="b-footer__link" href="/about/feedback/">������ ���������</a></li>
										<li class="b-footer__item"><!-- webim button --><a class="b-footer__link" href="/webim/client.php?theme=default&amp;lang=ru&chooseoperator=N" target="_blank"  onclick="if (navigator.userAgent.toLowerCase().indexOf('opera') != -1 && window.event.preventDefault) window.event.preventDefault();this.newWindow = window.open('/webim/client.php?theme=default&amp;lang=ru&chooseoperator=N&'+'opener='+encodeURIComponent(document.location.href) + '&openertitle='+encodeURIComponent(document.title) , 'webim_beta_free_lance_ru', 'toolbar=0, scrollbars=0, location=0, menubar=0, width=600, height=600, resizable=1');if (this.newWindow==null)return false;this.newWindow.focus();this.newWindow.opener=window;return false">�����������</a><!-- /webim button --></li>
				</ul>
			</div>

		
		
		
		
		</div>
		<div class="b-footer__bot">
			<div class="b-footer__rightcol"> <script type="text/javascript">document.write('<a class="b-footer__link" href="/press/adv/">�������</a>');</script>
				<div class="b-footer__mailru"> 
					<!--Rating@Mail.ru counter--> 
					<script language="javascript" type="text/javascript"><!--
d=document;var a='';a+=';r='+escape(d.referrer);js=10;//--></script> 
					<script language="javascript1.1" type="text/javascript"><!--
a+=';j='+navigator.javaEnabled();js=11;//--></script> 
					<script language="javascript1.2" type="text/javascript"><!--
s=screen;a+=';s='+s.width+'*'+s.height;
a+=';d='+(s.colorDepth?s.colorDepth:s.pixelDepth);js=12;//--></script> 
					<script language="javascript1.3" type="text/javascript"><!--
js=13;//--></script><script type="text/javascript"><!--
d.write('<a class="b-footer__link" href="http://top.mail.ru/jump?from=2071473" target="_blank">'+
'<img class="b-footer__counter" src="http://db.c9.bf.a1.top.mail.ru/counter?id=2071473;t=99;js='+js+
a+';rand='+Math.random()+'" alt="�������@Mail.ru"  '+
'height="18" width="88" \/><\/a>');if(11<js)d.write('<'+'!-- ');//--></script>
					<noscript>
					<a class="b-footer__link" target="_blank" href="http://top.mail.ru/jump?from=2071473"> <img class="b-footer__counter" src="http://db.c9.bf.a1.top.mail.ru/counter?js=na;id=2071473;t=99" height="18" width="88"  alt="�������@Mail.ru" /></a>
					</noscript>
					<script type="text/javascript"><!--
if(11<js)d.write('--'+'>');//--></script> 
					<!--// Rating@Mail.ru counter--> 
				</div>
			</div>
			<div class="b-footer__leftcol">
				<div class="b-footer__copyright">Copyright 2005&ndash;2012 Free-lance.ru</div>
				<div class="b-footer__rambler"> 
					<!-- begin of Top100 logo --> 
					<script type="text/javascript">document.write('<a class="b-footer__link" href="http://top100.rambler.ru/home?id=1367737" target="_blank">�������� Rambler\'s Top100</a>');</script> 
					<!-- end of Top100 logo --> 
				</div>
				<div class="b-footer__hh"> ������ ������ �������� HeadHunter&nbsp; <img class="b-footer__hhlogo" src="/images/hh.png" alt="HeadHunter" /> </div>
			</div>
			<div class="b-footer__midcol"> 
				<!-- tns-counter.ru --> 
				<script type="text/javascript">
				   var img = new Image();
				   img.src = 'http://www.tns-counter.ru/V13a***R>' + document.referrer.replace(/\*/g,'%2a') + '*hh_ru/ru/CP1251/tmsec=hh_free-lance/';
				</script>
				<noscript>
				<div><img class="b-footer__counter" src="http://www.tns-counter.ru/V13a****hh_ru/ru/CP1251/tmsec=hh_free-lance/"  alt="" /></div>
				</noscript>
				<!--/ tns-counter.ru --> 
				<!--LiveInternet counter--><script type="text/javascript"><!--
						  document.write("<a class='b-footer__link' href='http://www.liveinternet.ru/click;HeadHunter' "+
						  "target=_blank><img class='b-footer__counter' src='http://counter.yadro.ru/hit;HeadHunter?t44.6;r"+
						  escape(document.referrer)+((typeof(screen)=="undefined")?"":
						  ";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?
						  screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+
						  ";"+Math.random()+
						  "' alt='' title='LiveInternet' "+
						  " width='0' height='0' \/><\/a>")
						  //--></script><!--/LiveInternet--> 
				
				<!-- begin of Top100 code --> 
				<a class="b-footer__link" href="http://top100.rambler.ru/top100/"><img class="b-footer__counter" src="http://counter.rambler.ru/top100.cnt?1367737" alt="Rambler's Top100"   /></a> 
				<!-- end of Top100 code --> 
				
				<!-- bigmir)net TOP 100 --> 
				<a class="b-footer__link" href="http://www.bigmir.net/" onclick="img = new Image();img.src='http://www.bigmir.net/?cl=119761';" > 
				<script type="text/javascript"><!--
							bmQ='<img class="b-footer__counter" src=http://c.bigmir.net/?s119761&t8';
							bmD=document;
							bmD.cookie="b=b";
							if(bmD.cookie)bmQ+='&c1';
						   //--></script> 
				<script type="text/javascript"><!--
							bmS=screen;bmQ+='&d'+(bmS.colorDepth?bmS.colorDepth:bmS.pixelDepth)+"&r"+bmS.width;
						   //--></script> 
				<script type="text/javascript"><!--
							bmF = bmD.referrer.slice(7);
							((bmI=bmF.indexOf('/'))!=-1)?(bmF=bmF.substring(0,bmI)):(bmI=bmF.length);
							if(bmF!=window.location.href.substring(7,7+bmI))bmQ+='&f'+escape(bmD.referrer);
							bmD.write(bmQ+" alt=\"bigmir TOP100\">");
						   //--></script> 
				</a> 
				<!-- end of bigmir)net TOP 100 --> 
				
				<script type="text/javascript">

                            var _gaq = _gaq || [];
                            _gaq.push(['_setAccount', 'UA-163162-1']);
                            _gaq.push(['_addOrganic', 'images.yandex.ru', 'q', true]);
                            _gaq.push(['_addOrganic', 'blogsearch.google.ru', 'q', true]);
                            _gaq.push(['_addOrganic', 'blogs.yandex.ru', 'text', true]);
                            _gaq.push(['_addOrganic', 'go.mail.ru',  'q']);
                            _gaq.push(['_addOrganic', 'nova.rambler.ru', 'query']);
                            _gaq.push(['_addOrganic', 'nigma.ru', 's']);
                            _gaq.push(['_addOrganic', 'webalta.ru', 'q']);
                            _gaq.push(['_addOrganic', 'aport.ru', 'r']);
                            _gaq.push(['_addOrganic', 'poisk.ru', 'text']);
                            _gaq.push(['_addOrganic', 'km.ru', 'sq']);
                            _gaq.push(['_addOrganic', 'liveinternet.ru', 'ask']);
                            _gaq.push(['_addOrganic', 'quintura.ru', 'request']);
                            _gaq.push(['_addOrganic', 'search.qip.ru', 'query']);
                            _gaq.push(['_addOrganic', 'gde.ru', 'keywords']);
                            _gaq.push(['_addOrganic', 'gogo.ru', 'q']);
                            _gaq.push(['_addOrganic', 'ru.yahoo.com', 'p']);
                            _gaq.push(['_addOrganic', 'akavita.by', 'z']);
                            _gaq.push(['_addOrganic', 'tut.by', 'query']);
                            _gaq.push(['_addOrganic', 'all.by', 'query']);
                            _gaq.push(['_addOrganic', 'meta.ua', 'q']);
                            _gaq.push(['_addOrganic', 'bigmir.net', 'q']);
                            _gaq.push(['_addOrganic', 'i.ua', 'q']);
                            _gaq.push(['_addOrganic', 'online.ua', 'q']);
                            _gaq.push(['_addOrganic', 'a.ua', 's']);
                            _gaq.push(['_addOrganic', 'ukr.net', 'search_query']);
                            _gaq.push(['_addOrganic', 'search.com.ua', 'q']);
                            _gaq.push(['_addOrganic', 'search.ua', 'query']);
                            _gaq.push(['_addOrganic', 'search.ukr.net', 'search_query']);
                            _gaq.push(['_trackPageview']);

                            (function() {
                            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
                            })();

                            </script> 
				
				<!-- Yandex.Metrika counter -->
				<div style="display:none;"> 
					<script type="text/javascript">
                            (function(w, c) {
                                (w[c] = w[c] || []).push(function() {
                                    try {
                                        w.yaCounter6051055 = new Ya.Metrika(6051055);
                                         yaCounter6051055.clickmap(true);
                                         yaCounter6051055.trackLinks(true);

                                    } catch(e) { }
                                });
                            })(window, 'yandex_metrika_callbacks');
                    </script> 
				</div>
				<script src="//mc.yandex.ru/metrika/watch.js" type="text/javascript" defer="defer"></script>
				<noscript>
				<div><img class="b-footer__counter" src="//mc.yandex.ru/watch/6051055" style="position:absolute; left:-9999px;" alt="" /></div>
				</noscript>
				<!-- /Yandex.Metrika counter --> 
				
								
			</div>
		</div>
	</div>
</div>






















<!-- 2  -->
<!--  -->
<!--  0.88176  -->
        </div>
    
    </body>
    <!-- dev.free-lance.ru -->
</html>
