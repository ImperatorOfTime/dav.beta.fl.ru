<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

if (!defined('PUBLIC_INDEX')) {
    header_location_exit('/404.php');
}

$hide_banner_top = true;
$g_page_id = "0|991";
$public_project_page = 1;
$stretch_page = true;
$showMainDiv  = true;
$no_banner = 1;
$enter=true;
$header = "../header.php";
$footer = "../footer.html";
$page_title = "���������� ������� - �������, ��������� ������ �� FL.ru";
$page_keyw = "���������, ������������, ��������� ������, ����� ������, ����������� ������, ��������� �����������, ���������� ������, ����������������, ��������, ������, ������, ���, �������, ���������, ������, fl.ru";
$page_descr = "���������. ������������.��������� ������. ����� ������. ����������� ������. ��������� �����������. ���������� ������, ����������������, ��������, ������, ������, ���, �������, ���������, ������. FL.ru";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/smtp.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/guest/models/GuestMemoryModel.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/PromoCodes.php");

session_start();
$uid = get_uid(false);

$drafts = new drafts;

$step    = __paramInit('int', 'step', 'step', -1);
$proj_id = __paramInit('int', 'public', 'public', 0);
$kind    = __paramInit('int', 'kind', 'kind');
$back    = __paramInit('string', 'red', 'red', '');
$key     = __paramInit('string', 'pk', 'pk');
$exec    = __paramInit('string', 'exec', 'exec'); //����� ���������� ����������� � ������������ �������
$hash = __paramInit('string', 'hash');
$promo = __paramInit('string', 'promo', 'promo');

$scrollToPay = __paramInit('bool', 'pay_services', 'pay_services', false);

$draft_id   = intval(__paramInit('int', 'draft_id', 'draft_id'));
$auto_draft = intval(__paramInit('int', 'auto_draft', 'auto_draft'));

if(!$key) {
  $key = md5(uniqid($uid)); // ����-������������� ������������/�������������� �������, ��� �������� � ����.
  if($step==1 && !$proj_id) { // ���� � �������� ���� ������. �� �������� ���� ������ �����, �.�. ���� ����� ���������
                              // ��������� �������� ����� �������, � ���� ������ ���� ������ ���� ����������.
      header("Location: /public/?step=1&kind={$kind}".($exec?"&exec={$exec}":'')."&pk={$key}&".($auto_draft?'auto_draft=1&':'').($draft_id?'draft_id='.$draft_id.'&':'').($hash?'hash='.$hash.'&':'')."red=".urlencode($back));
      exit;
  }
}
$pprm = '&pk='.$key;


$tmpPrj = new tmp_project($key);
if(!($project = $tmpPrj->init($step, $proj_id))) {
    
    $user_action = ($exec)?'/registration/?user_action=add_project_to_' . $exec:
                           '/guest/new/project/';
    
    if(($kind != 1 && $kind != 4) || $exec) {
        ref_uri();//��������� ref_uri ��������
    }
    
    if ($kind == 4) { 
        $user_action = '/guest/new/vacancy/'; 
    } elseif ($kind == 7) {
        $user_action = '/registration/?user_action=add_contest'; 
    }
    
	// ���������� �������� �������� ������ ��� ������������
    header('Location: ' . $user_action);
    exit;
}

//�������� �������� � �������� ���������� �������
if (($name = isLandingProject())) {
    $tmpPrj->setProjectField('name', $name);
}


ref_uri();//��������� ref_uri ��������


$is_personal = false;
if ($exec || $project['exec_id']) {
	$freelancer = new freelancer();
	if ($exec) { //���������� �������
		$freelancer->GetUser($exec);
	} else {
		$freelancer->GetUserByUID($project['exec_id']);
	}	
    
	if ($freelancer->uid && 
        ($project['kind'] == projects::KIND_PERSONAL || 
         $kind == projects::KIND_PERSONAL)) {
        
        $is_personal = true;
    }
}

if (!$kind) {
    $kind = $project['kind'];
}


//#0026326 
//����� �� ���-�� ���������� �������� ��� ��PRO �� 24 ���� 
if( $kind == 1 && //��� ������
    !$proj_id && //��� ����� ������, �� ��������������
    //������ ����������� � isProjectsLimit, ���� ��������� �����  ����������� PRO � $tmpPrj->init()
    //�� ��� �� ������� ������� ����� orders? ������ �� ������������ is_pro � employer?
    //$project['is_pro'] == 'f' && //���� �����
    ($last_prj_date = $tmpPrj->isProjectsLimit($uid))){ //� ���� �����

    $last_prj_date = ago_pub($last_prj_date,'G:i');
    $content = "new/tpl.limit.php";
    include("../template2.php");
    exit;    
}


if ($kind == 7) {
    $page_title = "���������� �������� - �������, ��������� ������ �� FL.ru";
} elseif ($kind == 4) {
    $page_title = "����� �������� - �������, ��������� ������ �� FL.ru";
}

if($proj_id && projects::isProjectOfficePostedAfterNewSBR($project) && !hasPermissions('projects')) {
    header("Location: /404.php"); 
    exit;
}


if ($proj_id && $tmpPrj->isKonkurs() && !$tmpPrj->isActiveKonkurs() && !hasPermissions('projects')) {
    $error = 'contest_closed';
    $content = "error.php";
    include("../template2.php");
    exit;
}
    
$backLink = $back ? $back : $tmpPrj->pop('backLink');
$tmpPrj->push('backLink', $backLink);
$error    = $tmpPrj->pop('error');

$action  = __paramInit('string', 'action', 'action');

if(!$proj_id) { $draft_prj_id  = __paramInit('int', 'draft_prj_id', 'draft_prj_id'); } else { $draft_prj_id = $proj_id; }

if($PDA) {
    if($_POST['action_prev'] != "") $action = "prev";
    if($_POST['action_next'] != "") $action = "next";
    if($_POST['action_save'] != "") $action = "save";
    if($_POST['action_reload'] != "") $action = "reload";
    if($_POST['action_change'] != "") $action = "change";
    if($_POST['action_change2'] != "") $action = "change_country";
    if($_POST['action_change3'] != "") $action = "change_country2";
}

$employer = new employer();
$employer->GetUserByUID(get_uid(false));

$contacts = array(
    'phone' => array(
        'name'  => '�������',
        'value' => $employer->phone
    ),
    'site' => array(
        'name'  => '����',
        'value' => $employer->site
    ),
    'icq' => array(
        'name'  => 'ICQ',
        'value' => $employer->icq
    ),
    'skype' => array(
        'name'  => 'Skype',
        'value' => $employer->skype
    ),
    'email' => array(
        'name'  => 'E-mail',
        'value' => $employer->second_email
    )
);

//hash. ���� ������������ ������ ������ �� �����������, �� ����������� ������ � ������ #_POST
//@todo ���������� ��� � �������.
if ($hash) {
    $guestMemoryModel = new GuestMemoryModel();
    $savedData = $guestMemoryModel->getData($hash);
    
    $tmpPrj->setProjectField('name', substr(__paramValue('html', antispam(addslashes($savedData['name'])), 60, true), 0, 512));
    $tmpPrj->setProjectField('descr', __paramValue('html', antispam(addslashes($savedData['descr'])), null, true));
    
    if (isset($savedData['IDResource'])) {
        $uploader = new uploader($savedData['IDResource']);
    }
    
    $agreement = $savedData['agreement'];
    $tmpPrj->setProjectField('agreement', $agreement);
    if ($agreement) {
        $tmpPrj->setProjectField('cost', 0);
        $tmpPrj->setProjectField('currency', 0);
        $tmpPrj->setProjectField('priceby', 1);
    } else {
        $tmpPrj->setProjectField('cost', $savedData['cost']);
        $tmpPrj->setProjectField('currency', $savedData['currency']);
        $tmpPrj->setProjectField('priceby', $savedData['priceby']);
    }

    $categories = current($savedData['categories']);
    if ($categories && $categories['category_id']) {
        $cats[] = array(
            'category_id'   => $categories['category_id'],
            'subcategory_id' => $categories['subcategory_id']
        );
        $tmpPrj->setCategories($cats);
    }

    $tmpPrj->setProjectField('country', $savedData['country']);
    $tmpPrj->setProjectField('city', $savedData['city']);

    $tmpPrj->setProjectField('pro_only', $savedData['pro_only'] ? 't' : 'f');
    $tmpPrj->setProjectField('verify_only', $savedData['verify_only'] ? 't' : 'f');
    
    if (isset($savedData['prefer_sbr'])) {
        $tmpPrj->setProjectField('prefer_sbr', $savedData['prefer_sbr'] ? 't' : 'f');
    }
}

// ���������. ���� ������������ ����� ���������, �� ����������� ������ � ������ #_POST
if ( $draft_id && $auto_draft ) {
    
    $draft = $drafts->getDraft($draft_id, $uid, 1);

    $uploader = new uploader(uploader::createResource('project'));
    $attachedfiles_tmpdraft_files = drafts::getAttachedFiles($draft_id, 4);
    if($attachedfiles_tmpdraft_files) {
        $attachedfiles_draft_files = array();
        foreach($attachedfiles_tmpdraft_files as $attachedfiles_draft_file) {
            $attachedfiles_draft_files[] = $attachedfiles_draft_file;
        }
        $uploader->setFiles($attachedfiles_draft_files, uploader::STATUS_ADDED);
    }

    $_POST = array(
        'kind' => $draft['kind'],
        'descr' => addslashes($draft['descr']),
        'name' => addslashes($draft['name']),
        'cost' => $draft['cost'],
        'currency' => $draft['currency'],
        'priceby' => $draft['priceby'],
        'pro_only' => ($draft['pro_only'] == 't'? 1: 0),
        'verify_only' => ($draft['verify_only'] == 't'? 1: 0),
        'videolnk' => addslashes($draft['videolnk']),
        'strong_top' => $draft['strong_top'],
        'prefer_sbr' => ($draft['prefer_sbr'] == 't'? 1: 0),
        'urgent' => ($draft['urgent'] == 't'? 1: 0),
        'hide' => ($draft['hide'] == 't'? 1: 0),
        'draft_id' => $draft_id,
        'auto_draft' => $auto_draft,
        'budget_type' => $draft['budget_type'],
        'IDResource' => array($uploader->resource),
        //'attachedfiles_session' => $attachedfiles->getSession(),
        'attachedfiles_deleteold' => '1',
        'contacts' => unserialize($draft['contacts'])
    );

//echo '<pre>'; var_dump($_POST); echo '</pre>';    exit;
    if ( $draft['kind'] == 4 ) {
        $_POST['country'] = $draft['country'];
        $_POST['city'] = $draft['city'];
    }
    
    if ( $draft['kind'] == 7 ) {
        $_POST['end_date'] = $draft['p_end_date'];
        $_POST['win_date'] = $draft['p_win_date'];
    }
    
    $_POST['categories'] = array();
    $_POST['subcategories'] = array();
    
    if ( !empty($draft['categories']) ) {
        $c = explode(',', $draft['categories']);
        $cats = array();
        foreach ( $c as $v ) {
            $p = explode('|', $v);
            $_POST['categories'][] = $p[0];
            $_POST['subcategories'][] = $p[1];
        }
    }
    
    $action = 'next';

} elseif ($draft_id && !$drafts->getDraft($draft_id, $uid, 1)) { // ���� ��� id ���������, �� ����� �������� �� ����������
    header("Location: /403.php"); exit;
}

$account = new account();
$account->GetInfo($uid);
$_SESSION['ac_sum'] = $account->sum;
$_SESSION['ac_sum_rub'] = $account->sum_rub;
$_SESSION['bn_sum'] = $account->bonus_sum;

$konk_price = new_projects::getPriceByCode(( is_pro() ? new_projects::OPCODE_KON : new_projects::OPCODE_KON_NOPRO ));

//���������� ��������� ���� ��� �������
$promoCodes = new PromoCodes();
$allowedPromoCodes = array(
    PromoCodes::SERVICE_PROJECT
);
if ($kind == projects::KIND_VACANCY) {
    $allowedPromoCodes[] = PromoCodes::SERVICE_VACANCY;
} elseif ($kind == 7) {
    $allowedPromoCodes[] = PromoCodes::SERVICE_CONTEST;
}
$promoCodesForm = $promoCodes->render($allowedPromoCodes);

switch($step)
{
    case 2:
        unset($_SESSION['isExistProjects']);
        
    case 1:
        if (!$PDA) {
            $js_file = array();
            $js_file[] = 'attachedfiles2.js';
            $js_file[] = 'public_step_1.js';
            
            $attachLogo = new attachedfiles();
        }

        // ��� PDA ������
        if($action == 'prev' && $PDA) {
            $content = "content0.php";
            break;
        }
        
        if($PDA && (!$project['subcategory'] || $action == "change")) {
            $cat = __paramInit('int', 'category', null, 0);
            $subcat = __paramInit('int', 'subcategory', null, 0);
            $tmpPrj->setProjectField('category', $cat);
            $tmpPrj->setProjectField('subcategory', $subcat);
        }
        
        if($PDA && (!$project['city'] || $action == "change_country" || $action == "change_country2")) {
            $tmpPrj->setProjectField('country', __paramInit('int', 'country', 0));
            $tmpPrj->setProjectField('city', __paramInit('int', 'city', 0));
        }
        
        if($action=="change_country2" && $PDA) {
            header("Location: /public/?step={$step}&kind={$project['kind']}&category={$project['category']}&subcategory={$project['subcategory']}{$pprm}");
            exit;
        }
        
        if($_POST['link']=='����� ����� �� �������') {
            $_POST['link'] = '';
        } else {
            $_POST['link'] = addhttp($_POST['link']);
        }

        $tmpPrj->setProjectField('kind', $kind ? $kind : $project['kind']);
        // ����� ���� ������� � �������� ����.
        $project = $tmpPrj->getProject();
        
        if($project['kind'] && !in_array($project['kind'], array(0,1,2,4,7,9))) {
            $error['kind'] = '�������� �� �������';
        }
        
        if($_POST['logo_del']=="1") {
            $_POST['logo_ok'] = 0;
            $_POST['logo_file_id'] = "";
        }

        if($action=='save' || $action=="change" || $action=="change_country") {
            
            $use_draft = ($project['user_id']==get_uid(false));
            if(isset($_POST['contacts'])) {
                $error = users::validateContacts($_POST['contacts'], $contacts);
                $project['contacts'] = serialize($contacts);
                $tmpPrj->setProjectField('contacts', $project['contacts']);
            }
            //$tmpPrj->setProjectField('descr', antispam(__paramInit('html', NULL, 'descr', NULL, NULL, TRUE)));
            $tmpPrj->setProjectField('descr', __paramValue('html', antispam($_POST['descr']), null, true));
            //$tmpPrj->setProjectField('name', substr(antispam(__paramInit('html', NULL, 'name', NULL, 60, true)),0,512));
            $tmpPrj->setProjectField('name', substr(__paramValue('html', antispam($_POST['name']), 60, true), 0, 512));
            $tmpPrj->setProjectField('budget_type', __paramInit('int', NULL, 'budget_type',0));
            $agreement = __paramInit('int', NULL, 'agreement',0);
            $tmpPrj->setProjectField('agreement', $agreement);
            if ($agreement) {
                $tmpPrj->setProjectField('cost', 0);
                $tmpPrj->setProjectField('currency', 0);
                $tmpPrj->setProjectField('priceby', 1);
            } else {
                // ���� ������������� ������� � ����� �������� ������� ��������� ����������, �� ������ ������ ������
                if ( !($project['kind'] == 7 && $tmpPrj->isEdit() && new_projects::isNewContestBudget($project['post_date'])) ) {
                    $tmpPrj->setProjectField('cost', __paramInit('float', NULL, 'cost',0));
                    $tmpPrj->setProjectField('currency', __paramInit('int', NULL, 'currency_db_id',0));
                    $tmpPrj->setProjectField('priceby', __paramInit('int', NULL, 'priceby_db_id',0));
                }
            }
            
            if(!$PDA) {
                
                if (!$is_personal) {
					// ��������� �������������
					$check = array();
					for ($i = 0; $i <= 2; $i++) {
						$catID = __paramValue('int', $_POST['project_profession' . $i . '_columns'][0]);
						$subcatID = __paramValue('int', $_POST['project_profession' . $i . '_spec_columns'][0]);
						if ($catID || $subcatID) {
							$check[] = $catID . '_' . $subcatID;
						}
					}

					if (count($check)) {
						$uniq = array_unique($check);
						foreach($uniq as $val) {
							list($cat, $subcat) = explode("_", $val);
							$cats[] = array(
								'category_id'   => $cat,
								'subcategory_id'=> $subcat
							);
						}
						$tmpPrj->setCategories($cats);
					} else {
						$error['category'] = '�� ������ ������';   
					}
				} else {
					$subcat = $freelancer->spec;
					$cat = professions::GetGroupIdByProf($subcat);
					$cats[] = array(
						'category_id'   => $cat,
						'subcategory_id'=> $subcat
					);
					$tmpPrj->setCategories($cats);
				}

                $tmpPrj->setProjectField('country', __paramValue('int', $_POST['project_location_columns'][0]));
                $tmpPrj->setProjectField('city', __paramValue('int', $_POST['project_location_columns'][1]));
            }
            
            
            if ($project['kind'] == projects::KIND_PROJECT) {
                $tmpPrj->setProjectField('pro_only','t');
            } else {
                $tmpPrj->setProjectField('pro_only', __paramInit('bool', NULL, 'pro_only') ? 't' : 'f');
            }
            
            $tmpPrj->setProjectField('verify_only', __paramInit('bool', NULL, 'verify_only') ? 't' : 'f');
            $tmpPrj->setProjectField('videolnk',  __paramValue('html', antispam($_POST['videolnk']), 60, true) );
            $tmpPrj->setProjectField('strong_top', __paramInit('int', NULL, 'strong_top'));
            $tmpPrj->setProjectField('prefer_sbr', __paramInit('bool', NULL, 'prefer_sbr') ? 't' : 'f');

            $tmpPrj->setAddedTopDays(__paramInit('bool', NULL, 'top_ok') ? __paramInit('int', NULL, 'top_days',0) : 0);
            $tmpPrj->setProjectField('link', substr(__paramInit('string', NULL, 'link'),0,100));

            $oproject = $project;
            $project = $tmpPrj->getProject();
            
            //������ � ����� ��������� ������� "�������" � "�������"
            //���� ��������������, �� ����� ������ ��������. ����� false
            if($tmpPrj->isEdit()) {
                $tmpPrj->setProjectField('o_hide', $oproject['hide']);
                $tmpPrj->setProjectField('o_urgent', $oproject['urgent']);
            } else {
                $tmpPrj->setProjectField('o_hide', 'f');
                $tmpPrj->setProjectField('o_urgent', 'f');
            }
            //������������� ����� ����� ��� ���� �����
            $tmpPrj->setProjectField('urgent', __paramInit('bool', NULL, 'urgent') ? 't' : 'f');
            $tmpPrj->setProjectField('hide', __paramInit('bool', NULL, 'hide') ? 't' : 'f');
            
            if ($project['cost'] < 0) {
                $error['cost'] = '������� ������������� �����';
            }

            if ($project['cost'] > 999999) {
                $error['cost'] = '������� ������� �����';
            }

            if ($project['cost']>0 && ($project['currency'] < 0 || $project['currency'] > 3)) {
                $error['currency'] = '������ �� ����������';
            }

            if (is_empty_html($project['descr'])) {
                $error['descr'] = '���� �� ���������';
            }

            if (is_empty_html($project['name'])) {
                $error['name'] = '���� �� ���������';
            }
            
            $descr_limit = !$PDA ? 5000 : 2500;
            if (strlen_real($project['descr']) > $descr_limit) {
                $error['descr'] = "�������� ����� �������� ($descr_limit)";
            }
				
			if ($project['kind'] == 7) {
				$tmpPrj->setProjectField('end_date', str_replace('.', '-', __paramInit('string', NULL, 'end_date')),0,64);
				$tmpPrj->setProjectField('win_date', str_replace('.', '-', __paramInit('string', NULL, 'win_date')),0,64);
				$project = $tmpPrj->getProject();

                $pExrates = project_exrates::getAll();
                if ($project['currency'] == 0) { // USD
                    $costRub = $project['cost'] * $pExrates['24']; // ������ � ������
                } elseif ($project['currency'] == 1) { // EURO
                    $costRub = $project['cost'] * $pExrates['34'];
                } else { // �����
                    $costRub = $project['cost'];
                }
                $tmpPrj->setCostRub($costRub);

                // ����������� ������ �������� ������� �� ���� ������� �� ����� ������� �������� ��������� ����������
                $contestMinBudget = new_projects::isNewContestBudget($project['post_date']) ? new_projects::NEW_CONTEST_MIN_BUDGET : new_projects::CONTEST_MIN_BUDGET;
                if (!$project['cost'] || $costRub < $contestMinBudget) {
                    $error['cost'] = true;
                }
                
				if (!preg_match("/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/", $project['end_date'], $o1) || !checkdate($o1[2], $o1[1], $o1[3])) {
					$error['end_date'] = '������������ ����';
                }

				if (!preg_match("/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/", $project['win_date'], $o2) || !checkdate($o2[2], $o2[1], $o2[3])) {
					$error['win_date'] = '������������ ����';
                }
				
				// ����������� ����������	
			    if(!hasPermissions('projects')) {
    				if (!$error['end_date'] && mktime(0, 0, 0, $o1[2], $o1[1], $o1[3]) <= mktime(0, 0, 0)) {
    					$error['end_date'] = '���� ��������� �������� �� ����� ����������  � �������';
                    }
    			
    				if (!$error['win_date'] && mktime(0, 0, 0, $o2[2], $o2[1], $o2[3]) <= mktime(0, 0, 0, $o1[2], $o1[1], $o1[3])) {
                        $error['win_date'] = '���� ����������� ���������� ������ ���� ������ ���� ��������� ��������';
                    }
				}				
			}
            
            $logoOK = __paramInit('bool', NULL, 'logo_ok');
            if ($logoOK) {
                $logoAttach = new attachedfiles($_POST['logo_attachedfiles_session']);
                $logoFiles = $logoAttach->getFiles(array(1,3));
                if (count($logoFiles)) {
                    $logoFile = array_pop($logoFiles); // ��������� ����� ���� ��������� ������, ����� ���������
                    $logoCFile = new CFile($logoFile['id']);
                    $tmpPrj->setLogoNew($logoCFile);
                    $logoAttach->setStatusTo3($logoFile['id']);
                } elseif ($_POST['logo_file_id']) {
                    //$logoCFile = new CFile(__paramInit('int', null, 'logo_file_id'));
                    //$tmpPrj->setLogoNew($logoCFile);
                } else {
                    if($_POST['is_exec_quickprj']!=1) {
                    $error['logo'] = '���������� ������� ����';
                    }
                }
            } else {
                $tmpPrj->clearLogo();
            }

            if(!$error) {
                if(!$PDA) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
                    reset($_POST['IDResource']);
                    $uploader = new uploader(current($_POST['IDResource']));
                    
                    $attachedfiles_files = $uploader->getFiles();
                    $tmpPrj->clearAttaches();
                    $tmpPrj->addAttachedFiles($attachedfiles_files, ($draft_id && !$is_tmp_draft ? true : false));
                    $uploader->clear();
                }

                if($PDA) {
                    // �������� ����� ��� ��� ������
                    $idDel = __paramInit('int', NULL, 'atch');
                    if($idDel <= 0 && $_FILES['attach']['error'][0] == 4) {
                        $tmpPrj->delAttach(0);
                    }
                    // ���������� �����
                    if (is_array($_FILES['attachedfiles_file']) && !$_FILES['attachedfiles_file']['error']) {
                        $_POST['attachedfiles_action'] = 'add';
                        $_POST['attachedfiles_type'] = 'project';
                        ob_start();
                        include($_SERVER['DOCUMENT_ROOT'] . "/attachedfiles.php");
                        ob_clean();
                        $attachedfiles_files = $attachedfiles->getFiles(array(1,3,4));
                        $tmpPrj->addAttachedFiles($attachedfiles_files);
                        $attachedfiles->clear();
                    }
                }
            }
            
            $tmpPrj->fix();
            // ����� ��� ���
            if($action=="change" && $PDA) {
                header("Location: /public/?step={$step}{$pprm}&kind=".$project['kind']);
                exit;
            }
            if($action=="change_country" && $PDA) {
                header("Location: /public/?step={$step}{$pprm}&kind={$project['kind']}&category={$project['category']}&subcategory={$project['subcategory']}");
                exit;
            }

            $error_type = '';
            if(!$error_type && $error['attach']) {
                $error_type = 'attach';
            }
            if(!$error_type && ($error['win_date'] || $error['end_date'])) {
                $error_type = 'date';
            }

            if (!$error) {
            	//��� ������������
                $account_sum = $account->sum;
                $account_bonus_sum = $account->bonus_sum;
                //��� ����������
                $view_user_login = '';
                if (hasPermissions("projects")) {
                	$uid      = $tmpPrj->getAuthorId();
                	$view_account = new account();
                	$view_account->GetInfo($uid);
                	$account_sum = $view_account->sum; 
                	$account_bonus_sum    = $view_account->bonus_sum;
                	$view_user_login =   $tmpPrj->getAuthorLogin();
                }
                if ($action=='save') {
                    $_POST['logo_id'] = $_POST['logo_file_id'];
                    if ($PDA && !$tmpPrj->getCategories()) {
                        $cats = array();
                        $cats[] = array('category_id' => $project['category'], 'subcategory_id' => $project['subcategory']);
                        $tmpPrj->setCategories($cats);
                    }
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
                    
                    $bill = new billing($uid);
                    $bill->clearOrders();
                    
                    $tmpProject = $tmpPrj->getProject();
                    if ($tmpPrj->isVacancy()) {
                        $initedState = $tmpProject['state'];
                        $tmpPrj->setProjectField('old_state', $initedState);
                        $tmpPrj->setProjectField('state', projects::STATE_PUBLIC);
                    }
                        
                    if ($tmpPrj->getAmmount() && $account_bonus_sum < $tmpPrj->getAmmount()) {

                        if(! $tmpPrj->isEdit()) {
                            $tmpProject['uid'] = $uid;
                            $tmpProject['draft_id'] = $draft_id;
                            $tmpProject['project_profession0_columns'] = $_POST['project_profession0_columns'];
                            $tmpProject['project_profession1_columns'] = $_POST['project_profession1_columns'];
                            $tmpProject['project_profession2_columns'] = $_POST['project_profession2_columns'];
                            $tmpProject['currency_db_id'] = $tmpProject['currency'];
                            $tmpProject['priceby_db_id']  = $tmpProject['priceby'];
                            $tmpProject['top_ok']         = $_POST['top_ok'];
                            $tmpProject['top_days']       = intval($_POST['top_days']);
                            $tmpProject['logo_link']      = $tmpProject['link'];
                            $tmpProject['logo_ok']        = $_POST['logo_ok'];
                            $tmpProject['logo_id']        = $_POST['logo_id'];
                            $tmpProject['logo_file_id']   = $_POST['logo_id'];
                            $tmpProject['logo_attachedfiles_session'] = $_POST['logo_attachedfiles_session'];
                            $tmpProject['pro_only']       = $tmpProject['pro_only'] == 't' ? 1 : 0;
                            $tmpProject['prefer_sbr']     = $tmpProject['prefer_sbr'] == 't' ? 1 : 0;
                            $tmpProject['urgent']     = $tmpProject['urgent'] == 't' ? 1 : 0;
                            $tmpProject['hide']     = $tmpProject['hide'] == 't' ? 1 : 0;
                            $tmpProject['project_location_columns'] = $_POST['project_location_columns'];
                            $tmpProject['verify_only'] = $tmpProject['verify_only'] == 't' ? true : false;
                            $tmpProject['videolnk']        = $_POST['videolnk'];
                            $tmpProject['name']        = $_POST['name']; // ��� ��� �������������� ���
                            $tmpProject['descr']       = $_POST['descr']; // ��� ��� �������������� ���
                            $tmpProject['IDResource']  = $_POST['IDResource'];
                            $attached_files = $tmpPrj->getNewAttach();
                            if($attached_files) {
                                foreach($attached_files as $k=>$val) {
                                    $attached_files[$k]['id'] = $val['file_id'];
                                }
                            }
                            $project = drafts::SaveProjectNew($tmpProject, $attached_files);
                        } 
                        
                        
                        $logo    = $tmpPrj->getLogo();
                        if($tmpProject['logo_id'] > 0 && !$logo['id'] && $_POST['logo_ok']) {
                            $logoCFile = new CFile($tmpProject['logo_id']);
                            $tmpPrj->initLogo($logoCFile);
                            $logo    = $tmpPrj->getLogo();
                        }
                        $price = $tmpPrj->getPrice($items, $__temp, true);
                        
                        $option = array(
                            'is_edit'      => $tmpPrj->isEdit(),
                            'items'        => $items,
                            'prj_id'       => $project['id'],
                            'logo_id'      => $logo['id'], 
                            'logo_link'    => $tmpProject['link']
                        );
                        if($items['top']) {
                            $option['addTop'] = $tmpPrj->getAddedTopDays();
                        }
                        
                        if($tmpPrj->isKonkurs()) {
                            if (new_projects::isNewContestBudget()) {
                                $cost      = $tmpPrj->getCostRub();
                                $op_code   = new_projects::getContestTaxOpCode($tmpPrj->getCostRub(), is_pro());
                                $items['contest']["no_pro"] = $tmpPrj->isEdit() ? 0 : new_projects::getContestTax($cost, is_pro());
                                $items['contest']["pro"] = $tmpPrj->isEdit() ? 0 : new_projects::getContestTax($cost, true);
                            } else {
                                if (!$tmpPrj->isEdit())
                                {
                                    $items['contest']["no_pro"] = 3300;
                                    $items['contest']["pro"] = 3000;
                                }           
                                $op_code    = is_pro() ? new_projects::OPCODE_KON : new_projects::OPCODE_KON_NOPRO;
                            }
                            $op_code_pay = new_projects::OPCODE_PAYED_KON;
                        } else {
                            $op_code     = new_projects::OPCODE_PAYED;
                            $op_code_pay = new_projects::OPCODE_PAYED;
                        }
                        
                        if($items) {
                            $bill->start();
                            
                            // ���� ������ ����� �������
                            if($items['office'] > 0) {
                                $bill->setPromoCodes('SERVICE_VACANCY', $promo); 
                                $option['items'] = array('office' => $items['office']);
                                $bill->setOptions($option);
                                $op_code_vacancy = $tmpPrj->getVacancyOpCode();
                                $success = $bill->addServiceToCart($op_code_vacancy);
                                $items['office'] = 0;
                                $bill->unsetPromoCodes();
                            }

                            // �������
                            if($items['contest'] > 0) {
                                $bill->setPromoCodes('SERVICE_CONTEST', $promo); 
                                $option['items'] = array('contest' => $items['contest']);
                                $bill->setOptions($option);
                                $success = $bill->addServiceToCart($op_code);
                                $items['contest'] = 0;
                                $bill->unsetPromoCodes();
                            }
                            
                            $bill->setPromoCodes('SERVICE_PROJECT', $promo); 

                            // ������� ������ �� �����������
                            foreach($items as $opt=>$value) {
                                if ( is_array($value) && $value["no_pro"] <= 0) {
                                    continue;
                                }
                                if($value <= 0) {
                                    continue;
                                }

                                $option['items'] = array($opt => $value);
                                $bill->setOptions($option);
                                
                                $ownOpCode = new_projects::getOpCodeByService($opt);
                                $use_op_code = $ownOpCode ? $ownOpCode : $op_code_pay;
                                
                                $success = $bill->addServiceToCart($use_op_code);
                                if(!$success) break;
                            }

                            if(!$success) {
                                $bill->rollback();
                            } else {
                                $bill->commit();
                                
                                if (!$tmpPrj->isEdit()) {
                                    $_SESSION['new_public'] = 1;
                                }
                                
                                // �������� ������ ������� ��� ���� ������� ������� ������
                                if($tmpPrj->isEdit()) {
                                    if ($tmpPrj->isVacancy()) {
                                        $tmpPrj->setProjectField('state', $initedState);
                                    }
                                    
                                    if((is_array($items['logo']) && $items['logo']['no_pro'] > 0) 
                                        || !is_array($items['logo']) && $items['logo'] > 0) {
                                        $tmpPrj->clearLogo();
                                    }
                                    
                                    if($items['top'] > 0) {
                                        $tmpPrj->setAddedTopDays(0);
                                    }

                                    $error = $tmpPrj->saveProject(hasPermissions('projects') ? $uid : NULL, $proj);
                                }
                                
                                if($_POST['is_exec_quickprj']!=1) {
                                    header('Location: /bill/orders/');
                                    exit;
                                } else {
                                    echo "<script>";
                                    echo "window.parent.quickPRJ_process_continue();";
                                    echo "</script>";
                                    exit;
                                }
                            }
                        }
                    }
                    $t_cats = $tmpPrj->getCategories();
                    $is_edit = $tmpPrj->isEdit();
                    if (!($error['buy'] = $tmpPrj->saveProject(hasPermissions('projects') ? $uid : NULL, $proj))) {

                        //���� ������ ��� ������ ��� �������� � �������� 
                        //�� ����������� ��� ��� ����������
                        if (!$is_edit) {
                            if (($landingProjectId = getLastLandingProjectId())) {
                                require_once(ABS_PATH . '/classes/LandingProjects.php');
                                LandingProjects::model()->linkWithProject(
                                    $landingProjectId, 
                                    $proj['id'],
                                    false);
                            }
                        }
                        
                        
                        if (!$is_edit) {
                            # ���� ���� ������ "������" ����� ���������
                            $finded = 0;
                            foreach ($t_cats as $cat) {
                                if (in_array($cat['category_id'], array(3,10,11,18))) {
                                    $finded = $cat['category_id'];
                                    break;
                                }
                            }
                            
                            if ($finded > 0) {
                                
                                $category_names = array(
                                    3 => '������',
                                    10 => '����������',
                                    11 => '�����/�����',
                                    18 => '���'
                                );
                                
                                $mail = new smtp();
                                $mail->subject = '����������� ���� ������ �� Fotogazon.ru';
                                $mail->message = "������������!
<br /><br />
��������� ��� �� ���������� ������� � ��������� {$category_names[$finded]} �� ����� FL.ru. ����� �������� ��������� ����� ������������� ���� ������� ����� ���������������� ����������, ������ �� ������� � �������� ��������� �� ���������� ����� ������.
<br /><br />
<a href='http://fotogazon.ru/'>FotoGazon.ru</a> - ��� ����� ������ �� ������ ����������. ������� ������� ��������� �� <a href='http://fotogazon.ru/'>FotoGazon.ru</a>                                    
<br /><br />
---<br />
� ���������,<br /> 
������� <a href='http://fotogazon.ru/'>FotoGazon.ru</a><br />
";

/*
                                $mail->subject   = '����������� ���� ������ �� DizKon.ru';  // ��������� ������
                                $mail->message = "������������!
<br /><br />
��������� ��� �� ���������� ������� � ��������� ������ �� ����� <a href='https://fl.ru'>FL.ru</a>. ����� �������� ��������� ����� ������������� ���� ������� ����� ���������������� ����������, ������ �� ������� � �������� ��������� �� ���������� ����� ������.
<br /><br />
������, �������� ����� �������, �������� �������� ������ ��� ������ �������� ���������� �������� �� ����� <a href='http://www.dizkon.ru/?utm_source=flnwsldiz&utm_medium=email&utm_campaign=dizkonvsjob' >DizKon.ru</a>.
<br /><br />
<a href='http://www.dizkon.ru/?utm_source=flnwsldiz&utm_medium=email&utm_campaign=dizkonvsjob'>DizKon.ru</a> � ��� ������ �������� ��� ���������� ������-��������� � ������, �� ������� ��� ������� ����� ������ ����������� ����������. ��� ���������� �� ������������ ����������� ������ �������� �� ����� ������ � ��������� ����������� ������������ ���������� �� ��� ����������, ������� ���������, �������������� ����� �� ������������ ������������� ��������� ����������� ������.
<br /><br />
<a href='http://www.dizkon.ru/?utm_source=flnwsldiz&utm_medium=email&utm_campaign=dizkonvsjob'>DizKon.ru</a> � ��� ������ ���������, �� ����������� ����� ������ ��������, �������, � ������ � �����, ���������� ���������, ����������� ������� ��������, ���� ������ �� �����������, � ����� 10 ���. ���������������� ������������ � ����� �������.
<br /><br />
������� �� �������������� ��������������!
<br /><br />
---<br />
� ���������,<br />
������ ������������<br />
������������ ������� <a href='http://www.dizkon.ru/?utm_source=flnwsldiz&utm_medium=email&utm_campaign=dizkonvsjob' >DizKon.ru</a><br />
<a href='mailto:maxim@dizkon.ru'>maxim@dizkon.ru</a><br />
                                "; // ����� ������
                                 */
                                
                                $mail->recipient = "{$employer->login} <".$employer->email.">"; // ����������
                                $mail->send('text/html'); // ���������� ������ ��� plain/text
                            }
                        }
				
                        
                        if (!$is_edit && $is_personal) 
                        {
                            //��������� ����� ����������
                            //$message = '� �������' . ($sex == 'f' ? '�' : '') . ' ���� ����������� � �������, � ������ ������� ������ �� ����.';
                            $message = '';
                            $obj_offer = new projects_offers();
                            $obj_offer->AddOffer($freelancer->uid, $proj['id'], '', '', 2, '', '', 0, $message, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', false, 0, 1);
                            $tmpPrj->SetExecutor($proj['id'], $freelancer->uid, $employer->uid);
                            
                            //���������� ��� ����������
                            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_sms.php");
                            ProjectsSms::model($freelancer->uid)->sendStatus(0, $proj['id'], 9);
                        }

                        
                        if (hasPermissions("projects")) {
                    		$inspect_user_session = new session();
	                		$inspect_user_session->UpdateProEndingDate($view_user_login);
                    	}
                		
                        if (!$is_edit) {
                            $_SESSION['new_public'] = 1;
                        }                    

                        $drafts->DeleteDraft($draft_id, $uid, 1);
                        
                        
                        
                        if ($is_edit) {
                            $back = $backLink ? $backLink : '/projects/'.$proj['id'];
                        } else {
                            $back = "/public/?step=2&public={$proj['id']}";
                        }

                        header("Location: {$back}");
                        exit;
                        
                        
                        //@todo: ���� ������ ���������� ���� ���� �� ����
                        /*
                        if($_POST['is_exec_quickprj']==1) {
                            $friendly_url = getFriendlyURL('project', $proj['id']);
                            $_SESSION['quickprj_ok'] = 1;
                            echo "<script>";
                            echo "window.parent.quickPRJ_process_done('".$friendly_url."?quickprj_ok=1"."');";
                            echo "</script>";
                            exit;
                        }
                        */
                        
                        /*
                        if($price) {
                            // � PDA ��� �������-���������
                            if ($PDA) {
                                if($back == '/') $back = false;
                                $_SESSION['bill.GET']['back'] = $back;
                                header("Location: /bill/success/");
                                exit;
                            }
                            // ������� �����
                            $payedParams = "";
                            foreach ($payedItems as $name=>$sum) {
                                if ($sum > 0) {
                                    $payedParams .= "&" . $name . "=" . $sum;
                                }
                            }
                            $payedParams .= "&top_days=" . __paramInit('int', NULL, 'top_days',0);
                            $params = "?prj_id=" . $proj['id'] . $payedParams;
                            // ���� �������
                            if ($proj['kind'] == 7) {
                                if (new_projects::isNewContestBudget($proj['post_date'])) {
                                    $contestPrice = new_projects::getContestTax($tmpPrj->getCostRub(), $project['is_pro'] === 't');
                                } else {
                                    $contestPrice = new_projects::getKonkursPrice(is_pro());
                                }
                                $contest = "&contest=" . $contestPrice;
                                header("Location: /public/contest_published.php/" . $params . $contest . $editPrj);
                            } else {
                                if($proj['kind'] == 4 && !$tmpPrj->isEdit() && $proj['is_pro'] !== 't') {
                                    $inoffice = "&inoffice=".new_projects::getProjectInOfficePrice();
                                }
                                header("Location: /public/payed_project_published.php/" . $params . $inoffice . $editPrj);
                                //header("Location: /bill/success/");
                            }
                        } else {
                            if ($draft_prj_id) {
                                header("Location: {$back}");
                            } else {
                                // � PDA ��� ������� ���������
                                if ($PDA) {
                                    header("Location: {$back}");
                                } else {
                                    header("Location: /public/project_published.php?prj_id=" . $proj['id'] . $editPrj);
                                }
                            }
                        }
                        exit;
                        */
                    }
                } else {
                    if(!$PDA) {
                        $tmpPrj->fix();
                        if($_POST['is_exec_quickprj']==1) {
                            echo "<script>";
                            echo "window.parent.quickPRJ_process_continue();";
                            echo "</script>";
                            exit;
                        } else {
                            header("Location: /bill/?paysum=".ceil($price - $account->sum));
                            exit;
                        }
                    } else {
                        $error['buy'] = '�� ����� ����� �� ������� '. round($price - $account->sum, 2). ' ' . ending(round($price - $account->sum), '�����', '�����', '������');
                    }
                }
            }
            
        } else if ( $draft_id ) {
    
            // �������� � ���������
            
            $draft = $drafts->getDraft($draft_id, $uid, 1);
            
            $tmpPrj->setProjectField('kind',       $draft['kind']);
            $tmpPrj->setProjectField('descr',      addslashes($draft['descr']));
            $tmpPrj->setProjectField('name',       addslashes($draft['name']));
            $tmpPrj->setProjectField('videolnk',       addslashes($draft['videolnk']));
            $tmpPrj->setProjectField('cost',       $draft['cost']);
            $tmpPrj->setProjectField('currency',   $draft['currency']);
            $tmpPrj->setProjectField('priceby',    $draft['priceby']);
            $tmpPrj->setProjectField('pro_only',   $draft['pro_only']);
            $tmpPrj->setProjectField('verify_only',$draft['verify_only']);
            $tmpPrj->setProjectField('strong_top', $draft['strong_top']);
            $tmpPrj->setProjectField('prefer_sbr', $draft['prefer_sbr']);
            $tmpPrj->setProjectField('urgent', $draft['urgent']);
            $tmpPrj->setProjectField('hide', $draft['hide']);
            $tmpPrj->setProjectField('budget_type', $draft['budget_type']);
            
            $tmpPrj->setProjectField('top_days',    $draft['top_days']);
            $tmpPrj->setProjectField('logo_id',     $draft['logo_id']);
            $tmpPrj->setProjectField('link',        $draft['logo_link']);
            $tmpPrj->setProjectField('contacts', $draft['contacts']);
            
            if ( $draft['kind'] == 4 ) {
                $tmpPrj->setProjectField('country', $draft['country']);
                $tmpPrj->setProjectField('city',    $draft['city']);
            }

            if ( $draft['kind'] == 7 ) {
                $tmpPrj->setProjectField('win_date', $draft['p_win_date']);
                $tmpPrj->setProjectField('end_date', $draft['p_end_date']);
            }

            if ( !empty($draft['categories']) ) {
                $c = explode(',', $draft['categories']);
                $cats = array();
                foreach ( $c as $v ) {
                    $p = explode('|', $v);
                    $cats[] = array('category_id' => $p[0], 'subcategory_id' => $p[1]);
                }
                if ( $cats ) {
                    $tmpPrj->setCategories($cats);
                }
            }
    
        }
        if(!$uploader) {
            $uploader = new uploader(uploader::createResource('project'));
        }
        
        if(!$_POST['IDResource'] && $draft_id) {
            $attachedfiles_tmpdraft_files = drafts::getAttachedFiles($draft_id, 4);
            if($attachedfiles_tmpdraft_files) {
                $attachedfiles_draft_files = array();
                foreach($attachedfiles_tmpdraft_files as $attachedfiles_draft_file) {
                    $attachedfiles_draft_files[] = $attachedfiles_draft_file;
                }
                $uploader->setFiles($attachedfiles_draft_files, uploader::STATUS_ADDED);
            }
        } else {
            $attachedfiles_tmpprj_files = $tmpPrj->getAttach();
            if($attachedfiles_tmpprj_files) {
                $attachedfiles_prj_files = array();
                foreach($attachedfiles_tmpprj_files as $attachedfiles_prj_file) {
                    $attachedfiles_prj_files[] = $attachedfiles_prj_file['file_id'];
                }
                $set = $uploader->setFiles($attachedfiles_prj_files, $draft_id ? uploader::STATUS_ADDED : uploader::STATUS_CREATE);
            }
        }
        $attachedfiles_files = $uploader->getFiles();

        $content = "new/tpl.step_1.php";
        
        
        break;
        
//------------------------------------------------------------------------------
    
    case 0:
    default:
        header_location_exit('/public/?step=1&kind=1');
        break;
}


// ��� ��������� $tmpPrj->_project ��������� � ����������.
$project = $tmpPrj->getProject();

if(trim($project['contacts']) != '') {
    $contacts = unserialize($project['contacts']) ? unserialize($project['contacts']) : $contacts;
}
if ($project['country']) {
    $location = country::GetCountryName($project['country']);
    if ($project['city']) {
        $location .= ': ' . city::GetCityName($project['city']);
    }
    $project['location'] = $location;
}

$prj_categories = $tmpPrj->getCategories();
if ($prj_categories) {
    foreach ($prj_categories as $ind => $category) {
        $prj_categories[$ind]['prof_name'] = $category['subcategory_id'] ? professions::GetProfNameWP($category['subcategory_id'], ': ', '', false) : professions::GetGroupName($category['category_id']);
    }
}

if($step > 0) {
    $tmpPrj->fix();
}

if(!$additional_header) $additional_header = '';

//$additional_header .= '<script type="text/javascript" src="/scripts/tawl_bem.js"></script>';
$js_file[] = 'tawl_bem.js';

    
if($content == 'content2.php') {
    //$additional_header .= '<script type="text/javascript" src="/css/block/b-shadow/b-shadow.js"></script>';
    $js_file[] = '/css/block/b-shadow/b-shadow.js';
    
} else if($content == 'new/tpl.step_1.php') {
    
    $choose_bs = __paramInit('bool', 'choose_bs');
    $use_draft = ($project['user_id'] == get_uid(false));
    
    if($use_draft) {
        //$additional_header .= '<script type="text/javascript" src="/scripts/drafts.js"></script>';
        $js_file[] = 'drafts.js';
    }
    
    //$additional_header .= '<script type="text/javascript" src="/scripts/projects.js"></script>';
    $js_file[] = 'projects.js';
    
    if ($project['kind'] == 2 || $project['kind'] == 7) {
        //$additional_header .= '<script type="text/javascript" src="/scripts/calendar.js"></script>';
        $js_file[] = 'calendar.js';
    }
    
    //$additional_header .= '<script type="text/javascript" src="/scripts/attachedfiles.js"></script>';
    //$additional_header .= '<script type="text/javascript" src="/scripts/uploader.js"></script>';
    
    $js_file[] = 'attachedfiles.js';
    $js_file[] = 'uploader.js';
}
    
    
include("../template3.php");