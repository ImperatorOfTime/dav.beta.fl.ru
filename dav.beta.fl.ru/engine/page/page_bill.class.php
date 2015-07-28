<?php

//error_reporting(E_ALL);

/**
 * ����� ��� ��������� ������� ������ ����� � ���������� ����� (/bill/) 
 *
 */
class page_bill extends page_base {
	/**
	 * �������� �������� � URL
	 *
	 * @var string
	 */
	public $name_page = "bill";
	
	/**
	 * ����������� ������, �������������� ������� ������������ � ��� �� ������ ����������� ��� ������ ��������
	 *
	 */
	function __construct() {
		session_start();
		$uid = get_uid();
		
		// ��������� ������ �� �������� ��� �����, ������ ������ �������
		if(!hasPermissions('users')) {
		    //header("Location: /bill/"); // ���� ������������ �� �����
		    //exit;
		}
		
		if(!$uid && $_GET['pg'] != '/bill/alphabank/') {
			header("Location: /fbd.php"); // ���� ������������ �� �����������, �� �������� �� ������ ��������
			exit;
		}
		
		require_once($_SERVER['DOCUMENT_ROOT']."/classes/account.php");
		
		if (is_emp()) {
    		require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php');
    		$sbr = sbr_meta::getInstance();
    		front::og("tpl")->sbr_reserved  = $sbr->getReserved();
    	}
		
		$account = new account();
		$account->GetInfo(get_uid());
		$this->account = $account;
	    $_SESSION['ac_sum'] = $account->sum;
	    $_SESSION['bn_sum'] = $account->bonus_sum;
		$this->uid = $uid;
		front::og("tpl")->footer_bill = true;
		front::og("tpl")->main_css  = "/css/bill.css";
		front::og("tpl")->uid       = $uid; // �� ������������
		front::og("tpl")->account   = $account;   // ������� ��� ��������
		front::og("tpl")->month_name   = array(1=>"������", 2=>"�������", 3=>"����", 4=>"������", 5=>"���", 6=>"����", 7=>"����", 8=>"������", 9=>"��������", 10=>"�������", 11=>"������", 12=>"�������");
		front::og("tpl")->name_page = $this->name_page;	
		front::og("tpl")->no_banner = !!is_pro();
		front::og("tpl")->g_page_id = "0|27";
	}
	
	
	/**
	 * ����� ������� �������� 
	 * ������� ������� ��������. �� ��������� ��������� ����
	 */
	function indexAction() {
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/wizard/wizard_billing.php");
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/wizard/wizard.php");
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/wizard/step_freelancer.php");
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/wizard/step_employer.php");
	    unset($_SESSION['sum']);
		front::og("tpl")->page = "index";
		if($this->uri[0]=='success.php') {
		    header('Location: /bill/success/');
		    exit;
		}
        front::og("tpl")->no_banner = is_pro()?true:false;
        $master = wizard_billing::getDraftAccountOperations($_SESSION['uid']);
        if(!is_emp()) {
            $pro_op_codes = step_freelancer::getOperationCodePRO();
            foreach($master as $pay) {
                if(in_array($pay['op_code'], $pro_op_codes)) {
                    $is_pro = true;
                    $op_id  = $pay['id'];
                } elseif($pay['op_code'] == step_freelancer::OFFERS_OP_CODE) {
                    $disabled[$pay['id']] = $pay['id'];
                }
            }
            if(!$is_pro) unset($disabled);
            if($disabled) {
                $str_disabled = implode(",", $disabled);
                $dis[$op_id] = $str_disabled;
            }
            front::og("tpl")->pro_op_codes = $pro_op_codes;
            front::og("tpl")->disabled     = $disabled;
            front::og("tpl")->dis          = $dis;
            front::og("tpl")->is_pay_pro   = $is_pro;
        } else {
            foreach($master as $pay) {
                if($pay['op_code'] == step_employer::OP_CODE_PRO) {
                    $is_pro = true;
                    $op_id  = $pay['id'];
                } elseif($pay['op_code'] == 53 && $pay['option'] == 'color') {
                    $disabled[$pay['id']] = $pay['id'];
                }
            }
            if(!$is_pro) unset($disabled);
            if($disabled) {
                $str_disabled = implode(",", $disabled);
                $dis[$op_id] = $str_disabled;
            }
            front::og("tpl")->pro_op_codes = step_employer::OP_CODE_PRO;
            front::og("tpl")->disabled     = $disabled;
            front::og("tpl")->dis          = $dis;
            front::og("tpl")->is_pay_pro   = $is_pro;
        }
        front::og("tpl")->master =  $master;
    	
    	front::og("tpl")->text = static_pages::get("bill_index"); 
    	front::og("tpl")->display("bill/bill_index.tpl");
    }
    
    /**
     * ����� �������� /bill/alphabank/
     */
    function alphabankAction() {
        front::og("tpl")->display("bill/bill_alphabank.tpl");
    }
    
    /**
     * ����� �������� /buy/ "�������� ������"
     * 
     */
    function buyAction() {
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/payed.php");
        
    	front::og("tpl")->page = "buy";
    	front::og("tpl")->text = static_pages::get("bill_buy"); 
    	front::og("tpl")->display("bill/bill_buy.tpl");
    }
    /**
     * ����� �������� /gift/ "�������"
     *
     */
    function giftAction() {
        include $_SERVER['DOCUMENT_ROOT']."/404.php";
        exit;
        /**
         * @deprecated ��� ��� ���� ���� �� ������������
         */
    	front::og("tpl")->page = "gift";
    	
    	self::isBlockMoney();
    	
    	
    	/**
    	 * ���������� AJAX ��� ��������
    	 */
    	require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/bill.common.php");
		front::og("tpl")->script    = "/scripts/bill2.js"; 
    	front::og("tpl")->xajax     = $xajax; 
		front::og("tpl")->mnth      = 1;  // ������� ������ �� ���������
    	$val = $this->uri[0];
    	switch($val) {
    		case "pro": // ����� ����������� "������� �PRO�"
    			/**
    			 * ��������� ������� "��������"
    			 */
    			if($_POST['act']) {
    				$mnth = intval(trim($_POST['mnth'])); // �������
					$login = trim(strip_tags($_POST['login'])); // ����� ������������
					$msg =  change_q_x(__paramInit('string', NULL, 'msg', NULL, 300));
					$usertype = trim(strip_tags($_POST['usertype']));
					
					/**
					 * ���������� ����� ��� ������ �������
					 */
					require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
					$prof = new payed();
					$tr_id = $_REQUEST['transaction_id'];
					if (!$tr_id) {
						$this->account->view_error("���������� ��������� ����������. ���������� ��������� �������� � ������ ������.");
					}
					
					front::og("tpl")->tr_id = $tr_id;
					front::og("tpl")->login = $login;
					front::og("tpl")->msg   = $msg;
					front::og("tpl")->mnth  = $mnth;
					
					/**
					 * ���� ��� ������ �������
					 */
					if ($mnth > 0 && $login) {
						// ����� ��� ������ � �������������
						require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
						$user = new users();
						$gid = $user->GetUid($error, $login);
						$user->GetUser($login);
						if (!$gid ) $alert['login'] = "��� ������ ������������";
						if ($gid == get_uid()) $alert['login'] = "�� �� ������ ������� ������� ������ ����";
						if(defined('SPEC_USER') && get_uid()==SPEC_USER) {$alert['login'] = "������������� ���� ������ ��������� ��� ������ ��������";}

						if (!$alert) {
							$role = $user->GetRole($login, $error);
							/**
							 * ����������� ����� ��� ���������. ����� ������� �� ���������� ������� (��������� ����������� ������)
							 */
							if (substr($role, 0, 1)  != '0') {$tarif = 16;}
							else {
							  $tarif = 52;
								if($mnth==3) $tarif = 66;
								if($mnth==6) $tarif = 67;
								if($mnth==12) $tarif = 68;
							}
							
								$ok = $prof->GiftOrderedTarif($bill_id, $gift_id, $gid, get_uid(), $tr_id, $mnth, $msg, $tarif); // ������ �������
								
								if ($ok) {
									// �������� ����������� � ������� � ��������� �� �������� �������� ������
									$sm = new smail();
									$sm->NewGift($_SESSION['login'], $login, $msg, $gift_id);
									$_SESSION['success_aid'] = $bill_id;
									header("Location: /{$this->name_page}/success/"); 
									exit;
								}
								unset($msg);
						} 
						
					} else {
						$alert['login'] = "������ ���� �������� ������������";	
					}
					
					front::og("tpl")->error = $alert;
    			}
    			
    			front::og("tpl")->display("bill/bill_gift_pro.tpl");
    			break;
    		case "main": // ����� ����������� "������� ���������� � �������"
    			front::og("tpl")->type  = 1; // ���� ���������� = ������
    			/**
    			 * ��������� ������� "��������" - ��� ������� ������ ��������
    			 */
    			if($_POST['act']) {
    				$type  = intval(trim($_POST['type'])); // ������� ������
					$login = trim(strip_tags($_POST['login']));
					$msg =  change_q_x(__paramInit('string', NULL, 'msg', NULL, 300));
					
					/**
					 * ����� ��� ��������� ������ � �������������� ��������� ������
					 */
					require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/firstpage.php");
					$prof = new firstpage();
					$tr_id = $_REQUEST['transaction_id'];
					if (!$tr_id) {
						$this->account->view_error("���������� ��������� ����������. ���������� ��������� �������� � ������ ������.");
					}
					
					front::og("tpl")->tr_id = $tr_id;
					front::og("tpl")->login = $login;
					front::og("tpl")->msg   = $msg;
					front::og("tpl")->type  = $type;
					
					if(!$type) {
					  $alert['type'] = "�� ������� ���������� ������.";
					} else {
			   			$d_time = "{$type} ������";
			   			$intv   = "{$type} weeks";
			  			
			  			if ($login) {
			  				require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
			  				$user = new users();
			  				$gid = $user->GetUid($error, $login);
			  				$user->GetUser($login);
			  				if (!$gid) $alert['login'] = "��� ������ ������������";
			  				if (is_emp($user->role)) $alert['login'] = "������������ �� �������� �����������";
			  				if ($gid == get_uid()) $alert['login'] = "�� �� ������ ������� ������� ������ ����";
    						if(defined('SPEC_USER') && get_uid()==SPEC_USER) {$alert['login'] = "������������� ���� ������ ��������� ��� ������ ��������";}
			  				
			  				if (!$alert) {
			  					$order_id = $prof->Gift($bill_id, $gift_id, $gid, get_uid(), $tr_id, $intv, 17, $msg, $type);
			  					if (!$order_id) {
			  					    header("Location: /bill/fail/"); // ���� ������ �� ������
			  					    exit;
			  					}
			  					else { // ����������� �� �����
			  						$sm = new smail();
			  						$sm->NewGift($_SESSION['login'], $login, $msg, $gift_id);
			  						$_SESSION['success_aid'] = $bill_id;
			  						header("Location: /{$this->name_page}/success/"); // ���� ��� ������ ������� ��������� �� �������� - �������
			  						exit;
			  					}
			  				}
			  			} else {
			  				$alert['login'] = "������ ���� �������� ������������";	
			  			}
					}
					
					front::og("tpl")->error = $alert;
    			}
    			front::og("tpl")->display("bill/bill_gift_main.tpl");
    			break;
                    case "fronttop": // ��������� ����������� "������� ����� ������� ������� ��������"
    			// ������� ������� ������ "��������"
    			if($_POST['act']) {
    				$login = trim(strip_tags($_POST['login'])); // ����� 
					$msg =  change_q_x(__paramInit('string', NULL, 'msg', NULL, 300));
					
					$tr_id = $_REQUEST['transaction_id'];
					if (!$tr_id) {
						$this->account->view_error("���������� ��������� ����������. ���������� ��������� �������� � ������ ������.");
					}
					
					front::og("tpl")->tr_id = $tr_id;
					front::og("tpl")->login = $login;
					front::og("tpl")->msg   = $msg;
					
					if($login) {
						require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
			  			$user = new users();
			  			$gid = $user->GetUid($error, $login);
			  			$user->GetUser($login);

                        if (is_emp($user->role)) $alert['login'] = "������������ �� �������� �����������";
			  			if ($gid == get_uid()) $alert['login'] = "�� �� ������ ������� ������� ������ ����";	
   						if(defined('SPEC_USER') && get_uid()==SPEC_USER) {$alert['login'] = "������������� ���� ������ ��������� ��� ������ ��������";}
   						if (!$gid) $alert['login'] = "��� ������ ������������";
			  			
			  			if (!$alert) {
			  				/**
			  				 * ����� ��� ������ � ������� �������.
			  				 */
			  				require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");
			  				$place = new pay_place();
			  				$tarif = 69; 
			  				$profs = $place->gift($bill_id, $gift_id, $tr_id, $gid, get_uid(), $msg, $tarif); // ���������� �������
			  				
			  				if($profs) {
			  				    $_SESSION['success_aid'] = $bill_id;
			  					header("Location: /{$this->name_page}/success/"); // ��� ������ �������
			  					exit;
			  				}
			  			}
					} else {
						$alert['login'] = "������ ���� �������� ������������";	
					}
					
					front::og("tpl")->error = @$alert; // ���������� ������
    			}
    			front::og("tpl")->display("bill/bill_gift_fronttop.tpl");
    			break;


                        case "cattop": // ��������� ����������� "������� ����� ������� ��������"
    			// ������� ������� ������ "��������"
    			if($_POST['act']) {
    				$login = trim(strip_tags($_POST['login'])); // �����
					$msg =  change_q_x(__paramInit('string', NULL, 'msg', NULL, 300));

					$tr_id = $_REQUEST['transaction_id'];
					if (!$tr_id) {
						$this->account->view_error("���������� ��������� ����������. ���������� ��������� �������� � ������ ������.");
					}

					front::og("tpl")->tr_id = $tr_id;
					front::og("tpl")->login = $login;
					front::og("tpl")->msg   = $msg;

					if($login) {
						require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
			  			$user = new users();
			  			$gid = $user->GetUid($error, $login);
			  			$user->GetUser($login);
                        if (!$gid)   $alert['login'] = "��� ������ ������������";
			  			if (is_emp($user->role)) $alert['login'] = "������������ �� �������� �����������";
			  			if ($gid == get_uid()) $alert['login'] = "�� �� ������ ������� ������� ������ ����";
   						if(defined('SPEC_USER') && get_uid()==SPEC_USER) {$alert['login'] = "������������� ���� ������ ��������� ��� ������ ��������";}

			  			if (!$alert) {
			  				/**
			  				 * ����� ��� ������ � ������� �������.
			  				 */
			  				require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");
			  				$place = new pay_place(1);
			  				$tarif = 83;
			  				$profs = $place->gift($bill_id, $gift_id, $tr_id, $gid, get_uid(), $msg, $tarif); // ���������� �������

			  				if($profs) {
			  				    $_SESSION['success_aid'] = $bill_id;
			  					header("Location: /{$this->name_page}/success/"); // ��� ������ �������
			  					exit;
			  				}

                                                }
					} else {
						$alert['login'] = "������ ���� �������� ������������";
					}

					front::og("tpl")->error = @$alert; // ���������� ������
    			}
    			front::og("tpl")->display("bill/bill_gift_cattop.tpl");
    			break;


                        case "catalog": // ��������� ����������� "������� ����� � �������� � �������"
    			// ������� ������� ������ "��������"
                        front::og("tpl")->filter_categories = professions::GetAllGroupsLite(TRUE);
                        front::og("tpl")->filter_subcategories = professions::GetAllProfessions(1);
                        $x = new op_codes();
                        $x->GetRow(84);
                        $top_p = $x->sum;
                        $x->GetRow(85);
                        $inside_p = $x->sum;
                        front::og("tpl")->price_top = $top_p;
                        front::og("tpl")->price_inside = $inside_p;
    			if($_POST['act']) {
    				$login = trim(strip_tags($_POST['login'])); // �����
					$msg =  change_q_x(__paramInit('string', NULL, 'msg', NULL, 300));
                                        $pf_category=  __paramInit('int', NULL, 'pf_category', 0);
                                        $pf_subcategory=  __paramInit('int', NULL, 'pf_subcategory', 0);
//echo '<pre>'; print_r($_POST); exit('</pre>');
					$tr_id = $_REQUEST['transaction_id'];
					if (!$tr_id) {
						$this->account->view_error("���������� ��������� ����������. ���������� ��������� �������� � ������ ������.");
					}

					front::og("tpl")->tr_id = $tr_id;
					front::og("tpl")->login = $login;
					front::og("tpl")->msg   = $msg;
                                        front::og("tpl")->pf_category   = $pf_category;
                                        front::og("tpl")->pf_subcategory   = $pf_subcategory;

					if($login) {
						require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
			  			$user = new users();
			  			$gid = $user->GetUid($error, $login);
			  			$user->GetUser($login);
                        if (!$gid)   $alert['login'] = "��� ������ ������������";
			  			if (is_emp($user->role)) $alert['login'] = "������������ �� �������� �����������";
			  			if ($gid == get_uid()) $alert['login'] = "�� �� ������ ������� ������� ������ ����";
   						if(defined('SPEC_USER') && get_uid()==SPEC_USER) {$alert['login'] = "������������� ���� ������ ��������� ��� ������ ��������";}

        $weeks = (int)$_POST['weeks'];
        if(!$weeks) $alert['week'] = '������ ���� �������� ������������';

			  			if (!$alert) {
                                                    
			  						require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/firstpage.php");
                                                                        $prof = new firstpage();
                                                                        $pf_category = (int)$_POST['pf_category'];
                                                                        $pf_subcategory = (int)$_POST['pf_subcategory'];
                                                                        $page = !$pf_subcategory ? 0 : $pf_subcategory;
                                                                        $place_info = array($page => $weeks);
                                                                        $op_code = $page ? 85 : 84;
                                                                        
//                                                                               require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
//                                                                                $account = new account();
//                                                                                $account ->/ view_error("���������� ��������� ����������. ���������� ��������� �������� � ������ ������.");
                                                                        $account = new account();
                                                                        
                                                                        if($prof->GiftOrderedCat($bill_id, $gift_id, $gid, get_uid(), $tr_id, $page, (int)$weeks, $op_code, $msg)) {
                                                                            $_SESSION['success_aid'] = $bill_id;
                                                                                header("Location: /{$this->name_page}/success/"); // ��� ������ �������
                                                                                exit;
                                                                        }

                                                }
					} else {
						$alert['login'] = "������ ���� �������� ������������";
					}

					front::og("tpl")->error = @$alert; // ���������� ������
    			}
    			front::og("tpl")->display("bill/bill_gift_catalog.tpl");
    			break;
    		default: 
    			/**
    			 * �� ��������� ������� ������� �������� ������� "�������"
    			 */
    			front::og("tpl")->display("bill/bill_gift.tpl");
    			break;	
    	}
    }
    /**
     * ���������� ������ �������� /send/ - "�������� ������"
     *
     */
    function sendAction() {
        if(!hasPermissions('payments')) {
            include $_SERVER['DOCUMENT_ROOT']."/404.php";
            exit;
        }
    	front::og("tpl")->page = "send";
    	front::og("tpl")->script = "/scripts/bill2.js";
    	
    	self::isBlockMoney();
    	
    	/**
    	 * ���������� AJAX ��� ��������
    	 */
    	require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/bill.common.php");
		front::og("tpl")->xajax     = $xajax; 

		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
		$canTransfer = account::checkDepositByNotCard(get_uid(false));
		front::og("tpl")->canTransfer = $canTransfer;

		// ��������� ������� ������������� ��������
    	if($_POST['action'] == 'sendm' && !$_POST['last_action'] && $canTransfer) {
                $trs_sum = round(floatval($_POST['sum']), 2);
    		$sum = round(floatval(str_replace(",",".",trim($_POST['sum'])))); // ����� ��������
			$login = trim(strip_tags($_POST['login']));
			$msg =  __paramInit('string', NULL, 'msg', NULL, 300);
			$tr_id = $_REQUEST['transaction_id'];
			
			front::og("tpl")->tr_id = $tr_id;
			front::og("tpl")->login = $login;
			front::og("tpl")->msg   = $msg;
			front::og("tpl")->sum   = $sum;
			
			$user = new users();
			$gid = $user->GetUid($error, $login);
			if (!$gid) {$error = 1; $alert['login'] = "������������ �� ������";}
			if ($sum > 0 && $this->account->sum < $sum) {$error = 1; $alert['sum'] = "������������ �������";}
			if ($sum <= 0) {$error = 1; $alert['sum'] = "�������� ������ ���� ������ ����";}
			if (!$error) {
				// ������ ��� ��� �����
				$order_id = $this->account->transfer(get_uid(), $gid, $sum, $tr_id, $msg, true, $trs_sum);
				if (!$order_id) {
				    header("Location: /bill/fail/");
				    die();
				} else {
					header("Location: /{$this->name_page}/success/");
					die();
			     }
			} else {
				// ���� ���� ������ ������� �� �� �������� �� � ������ ������
				$inner = "send2.php";
				$user->GetUser($login);
				$transaction_id = $tr_id;
				
				front::og("tpl")->user = $user;
				
				front::og("tpl")->alert = $alert;
				front::og("tpl")->display("bill/bill_send2.tpl");
				return true;
			}
			
			front::og("tpl")->alert = $alert;
    	}
    	
    	
    	if($_POST['last_action']) {
    	    $sum = floatval(trim($_POST['sum']));
			$login = trim($_POST['login']);
			$msg =  trim($_POST['msg']);
			$tr_id = $_REQUEST['transaction_id'];
			
			front::og("tpl")->tr_id = $tr_id;
			front::og("tpl")->login = $login;
			front::og("tpl")->msg   = $msg;
			front::og("tpl")->sum   = $sum; 
    	}
    	
    	front::og("tpl")->display("bill/bill_send.tpl");	
    }
    
    /**
     * ����� �������� �� ���������� /bill/qiwi/
     *
     */
    function qiwiAction() {
    	front::og("tpl")->page = "index";
    	front::og("tpl")->uri = $this->uri;
    	front::og("tpl")->display("bill/bill_qiwi.tpl");
    }

    
    /**
     * ����� �������� �� ���������� /bill/svyasnoy/
     *
     */
    function svyasnoyAction() {
    	front::og("tpl")->page = "index";
    	front::og("tpl")->uri = $this->uri;
    	front::og("tpl")->display("bill/bill_svyasnoy.tpl");
    }


    /**
     * ����� �������� �� ���������� /bill/euroset/
     *
     */
    function eurosetAction() {
    	front::og("tpl")->page = "index";
    	front::og("tpl")->uri = $this->uri;
    	front::og("tpl")->display("bill/bill_euroset.tpl");
    }

    
    /**
     * ����� �������� �� ���������� /bill/elecsnet/
     *
     */
    function elecsnetAction() {
        //front::og("tpl")->page = "index";
    	//front::og("tpl")->uri = $this->uri;
    	//front::og("tpl")->display();
    	include $_SERVER['DOCUMENT_ROOT']."/404.php";
    }
    
    
    function qiwipurseAction() {
    	front::og("tpl")->page = "index";
		front::og("tpl")->script    = "/scripts/bill2.js"; 
		
    	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/qiwipay.php");
  	    $qiwipay = new qiwipay($this->uid);

	    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/captcha.php");
	    $captcha = new captcha();
            
            
    	if($_POST['action']=='create') {
             $error = $qiwipay->createBill($_POST);
             if(!$error) {
    	        $_SESSION['bill.GET']['success'] = $qiwipay->form['sum'];
    	        header("Location: /{$this->name_page}/qiwipurse/");
    	        exit;
    	    }
            $captcha->setNumber();
            front::og("tpl")->alert = $error;
            front::og("tpl")->comment = $qiwipay->form['comment'];
        }else{
            $captcha->setNumber();
        }
        
        front::og("tpl")->sum   = $_POST['sum']; // $qiwipay->form['sum'];
        front::og("tpl")->phone = $qiwipay->form['phone'];
        
        if(isset($_SESSION['bill.GET']['success'])) {
            front::og("tpl")->success = $_SESSION['bill.GET']['success'];
            unset($_SESSION['bill.GET']['success']);
        }
        
    	front::og("tpl")->display("bill/bill_qiwipurse.tpl");
    }
    
    /**
     * ����� �������� �� ��������� �������� WebMoney /bill/webmoney/
     *
     */
    function webmoneyAction() {
    	front::og("tpl")->page = "index";
    	front::og("tpl")->type = 'webmoney';
    	front::og("tpl")->script = array( "/scripts/bill2.js", 'md5.js' );
		require_once($_SERVER['DOCUMENT_ROOT']."/classes/pmpay.php");
    	$wmpay = new wmpay();
    	$pmpay = new pmpay();
        mt_srand();
     
    	$user = new users();
        $user->GetUserByUID(get_uid(0));

        if (date('Ymd') >= 20130301) { // #0022399
            $wm_paymaster = 2;
        } else {
            $wm_paymaster = $user->wm_paymaster;
            $is_weekend = in_array(date('w'), array(0,6));
            if ($wm_paymaster === NULL || $wm_paymaster == 2 && $is_weekend) {
                // 1:wmr, 2:paymaster
                $wm_paymaster = 1;
                if(!$is_weekend) {
                    $wm_paymaster += (mt_rand(1,100) > 50);
                }
                $user->setWmPaymaster($user->uid, $wm_paymaster);
            }
        }

        front::og("tpl")->_user = $user;
    	front::og("tpl")->payment_number = mt_rand(1, 500000);
        front::og("tpl")->wmr_purse = $wmpay->wmzr[1];
        
        if ($is_paymaster = ($wm_paymaster == 2)) {
            front::og("tpl")->payment_number = $pmpay->genPaymentNo();
            front::og("tpl")->wmr_purse = $pmpay->merchants[pmpay::MERCHANT_BILL];
        }
     
        front::og("tpl")->is_paymaster = $is_paymaster;
        front::og("tpl")->display("bill/bill_paysys.tpl");	
    }
    
   	/**
     * ����� �������� �� ��������� �������� Yandex Money
     *
     */
    function yandexAction() {
    	front::og("tpl")->page = "index";
    	front::og("tpl")->script = "/scripts/bill2.js";
    	front::og("tpl")->display("bill/bill_yd.tpl");	
    }

   	/**
     * ����� �������� ���-�������� ����
     *
     */
    function webpayAction() {
    	front::og("tpl")->page = "index";
    	front::og("tpl")->script = "/scripts/bill2.js";
    	front::og("tpl")->display("bill/bill_webpay.tpl");	
    }
    
    /**
     * ����� �������� ������ ��������� �� ������ ����� ��������
     *
     */
    function printAction() {
    	front::og("tpl")->page = "index";
    	
    	front::og("tpl")->pid  = intval($this->uri[0]);
    	front::og("tpl")->print_mode  = intval($this->uri[1]);
    	front::og("tpl")->display("bill/bill_bank_print.tpl");		
    }
    
    function transferAction() {
    	front::og("tpl")->page = "index";
    	
    	if(intval($this->uri[1]) == 1) {
    	    front::og("tpl")->tid  = intval($this->uri[0]);
            front::og("tpl")->show_ex_code = isset($this->uri[2]) && $this->uri[2] == 'show_ex_code';
            front::og("tpl")->display("bill/bill_transfer_print.tpl");
    	} else if (intval($this->uri[1]) == 2) {
            front::og("tpl")->tid  = intval($this->uri[0]);
            front::og("tpl")->display("bill/bill_transfer_print2.tpl");
        } else {
    	    front::og("tpl")->display("bill/bill_transfer.tpl");
    	}
    }
    
    /**
     * ����� �������� �� ��������� - "��������� ���������"
     *
     */
    function sberAction() {
    	front::og("tpl")->page = "index";
    	front::og("tpl")->script = "/scripts/bill2.js";
    	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/bank_payments.php");
    	$fm_val = '';
        
    	/**
    	 * ����� ������ �� ����������� ��������, ���� �� ����
    	 */
	    $bp = new bank_payments();
	  	if($id = __paramInit('int',NULL,'id')) {   // �������������.
	  		front::og("tpl")->edit = 1;
	  		$bp->GetRow($id, " AND user_id = {$this->uid}");
                        $fm_val = $bp->sum / EXCH_TR;
	    	if(!$bp->id) exit;
	  	} else { // ���� ���, ����� ����.
	  	  	$bp->bank_code = __paramInit('int',NULL,'bc', bank_payments::BC_SB);
	  	  	$bp->sum = __paramInit('float',NULL,'Sum');
	  	  	$bp_reqv = bank_payments::GetLastReqv($bp->bank_code, $this->uid);
	  	  	$bp->fio = $bp_reqv['fio'];
	  	  	$bp->address = $bp_reqv['address'];
	  	}
	
	  	if(!$bp->bill_num) $bp->bill_num  = bank_payments::GenBillNum($bp->bank_code, $this->uid, $this->account->id);
	
		$bank = bank_payments::GetBank($bp->bank_code);
	
	 	if($bp->accepted_time) exit; // ������������� ����� (��� �������� ������) ������ ��������.
    	
    	if($_POST['act']) {
    		$bp = new bank_payments();
	  	  	$bp->fio     = substr(__paramInit('string',NULL,'fio'),0,128);
            $bp->is_gift = false;
	  	  	$bp->address = substr(__paramInit('string',NULL,'address'),0,255);
		    $bp->bank_code = __paramInit('int',NULL,'bc');
	  	  	$bp->sum     = __paramInit('float',NULL,'sum');
	      	setlocale(LC_ALL, 'en_US.UTF-8'); // ��������� ����! (��� �� ���)
	  	  	$bp->fm_sum  = $bp->sum / EXCH_TR;
	  	  	$id          = __paramInit('int',NULL,'id');
			/**
			 * �������� ������
			 */
	  	  	if(!$bp->fio) $alert['fio'] = '���� ��������� �����������.';
	  	  	if(!$bp->address) $alert['address'] = '���� ��������� �����������.';
	  	  	if(!$bp->sum || $bp->sum < 0.01) $alert['sum'] = '���� ��������� �����������.';
	
	  	  	//if($alert) break;
			
	  	  	if(!$alert) {	  	  	
		    	if($id) {
		    		$bp->bank_code = NULL;
		    	  	$bp->Update($id, " AND user_id = {$this->uid} AND accepted_time IS NULL");
		    	} else {
		  	    	$bp->bill_num = bank_payments::GenBillNum($bp->bank_code, $this->uid, $this->account->id);
		  	    	$bp->user_id = $this->uid;
		  	    	$bp->op_code = 12;
		    	  	$id = $bp->Add($error, TRUE);
		  	  	}
		
		   	 	if(!$error) {
		   	    	header("Location: /".$this->name_page."/print/{$id}/");
		   	   		exit;
		   	  	}
	  	  	}
	  	  	
	  	  	front::og("tpl")->alert = $alert;
    	}

    	front::og("tpl")->bp = $bp;
    	front::og('tpl')->fm_val = $fm_val;
    	front::og("tpl")->display("bill/bill_sber.tpl");
    }
    
    /**
     * ������� ��� �� ��� - "���������� ������� ��� ����������� ��� (�����)"
     *
     */
    function bankAction() {
    	front::og("tpl")->page = "index";	
    	
    	$act = $this->uri[0];
    	
    	if($act == 'delete') {
    	    unset($_SESSION['sum']);
    	    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reqv.php");
			$did = intval($this->uri[1]);
			if ($did){
				require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reqv.php");
				$reqv = new reqv();
				$reqv->Del($did, " AND user_id='".get_uid()."'");
				
				header("Location: /{$this->name_page}/bank/");
				exit;
			}
			unset($reqv);
			
    	}
    	
    	if($_POST['sum']>0) {
    	    $_SESSION['sum'] = floatval($_POST['sum']);
    	} else {
    	    front::og("tpl")->sum  = floatval($_SESSION['sum']);
    	}
    	
    	
    	
    	// ��������� ����������� �������������� ������ ��� ��������
    	if($act == 'edit') {
    		/**
    		 * ����������� ���� ����������� ������� ��� ������ ������ ��������
    		 */
    		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reqv.php");
    		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reqv_ordered.php");
			
    		if($_POST['update']) {
				$reqv = new reqv();
				$reqv->BindRequest($_POST);
				$error = $reqv->CheckInput();
				if (!$error){
					$reqv->user_id = get_uid();
					$reqv->Update($reqv->id, " AND user_id='".get_uid()."'");
					header("Location: /{$this->name_page}/bank/#reqv".$reqv->id);
					exit;
				} else {
					$action = "edit";
					$edit_mode = 1;
					$eid = intval($reqv->id);
				}
				
				front::og("tpl")->error  = $error;
    		}
    		
			front::og("tpl")->countries = country::GetCountries();
			$reqvs = new reqv();
			$reqvByUid = $reqvs->GetByUid(get_uid());
			$reqvs_ord = new reqv_ordered();
			$billNum = sizeof($reqvs_ord->GetByUid(get_uid()));
			$sum = trim($this->uri[2]);
			$norisk_id= intval(trim($_REQUEST['noriskId']));
			
			front::og("tpl")->reqvs = $reqvs;
			front::og("tpl")->sum = $sum;
			front::og("tpl")->reqvByUid  = $reqvByUid;
			front::og("tpl")->reqvs_ord  = $reqvs_ord;
			front::og("tpl")->billNum    = $billNum;
			front::og("tpl")->norisk_id  = $norisk_id;
			front::og("tpl")->edit_mode  = $edit_mode = 1;
			front::og("tpl")->eid        = $eid = intval($this->uri[1]);
			
			// ������� ������� ������ - ��������
			if(!$_POST['update']) {
				foreach ($reqvByUid as $ikey => $value) {
					$reqvs->BindRequest($value);
					if ($edit_mode && $reqvs->id == $eid) $reqvkey = $ikey;
				}
				
				$reqv = new reqv();
				if ($act == "edit" && !$error) {
					$reqv->BindRequest($reqvByUid[$reqvkey]);
				} elseif ($error) {
					$reqv->BindRequest($_POST);
				}
				
				
			}
			front::og("tpl")->reqv  = $reqv;
				
	    	front::og("tpl")->display("bill/bill_bank_step2.tpl");
	    	exit;	
    	} else { // ����� ������� ������� ��������, ��� ���������� ��������� ����
	    	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reqv.php");
			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reqv_ordered.php");
			
			front::og("tpl")->countries = country::GetCountries();
			$reqvs = new reqv();
			$reqvByUid = $reqvs->GetByUid(get_uid());
			$reqvs_ord = new reqv_ordered();
			$billNum = sizeof($reqvs_ord->GetByUid(get_uid()));
			$sum = trim(!$_POST['sum']?$_SESSION['sum']:floatval($_POST['sum']));
			$norisk_id= intval(trim($_REQUEST['noriskId']));
		
			front::og("tpl")->reqvs = $reqvs;
			front::og("tpl")->sum = $sum;
			front::og("tpl")->reqvByUid  = $reqvByUid;
			front::og("tpl")->reqvs_ord  = $reqvs_ord;
			front::og("tpl")->billNum    = $billNum;
			front::og("tpl")->norisk_id  = $norisk_id;
				
			$reqv = new reqv();
			if ($action == "edit" && !$error) $reqv->BindRequest($reqvByUid[$reqvkey]); elseif ($error) $reqv->BindRequest($_POST);
			front::og("tpl")->reqv  = $reqv;
				
			/**
	    	 * ������� �� ��������� �������� ��� ���� ����� ����������� ��������� ������
	    	 */
	    	if($_POST['send']) {
				$reqv = new reqv();
				$reqv->BindRequest($_POST);
				$error = $reqv->CheckInput();
				//var_dump($error);
				if (!$error && !$_POST['editing']){
					//$reqv->user_id = get_uid();
					//$reqv->Add($err);
					front::og("tpl")->sum = !$_POST['sum']?$_SESSION['sum']:floatval($_POST['sum']);
					//front::og("tpl")->sum = $_POST['sum'];
					front::og("tpl")->reqv  = $reqv;
				
					front::og("tpl")->display("bill/bill_bank_step3.tpl");
					exit;
				} 
				
				front::og("tpl")->sum = !$_POST['sum']?$_SESSION['sum']:floatval($_POST['sum']);
				//front::og("tpl")->sum = $_POST['sum'];
				front::og("tpl")->reqv  = $reqv;
				front::og("tpl")->error = $error;//array("firm"=>"������� ��������");
				//front::og("tpl")->display("bill/bill_bank_step2.tpl");
	    		//exit;
				
	    	}
	    	
	    	/**
	    	 * �������������� ������ � ������� �����.
	    	 */
	    	if($_POST['next']) {
	    		$reqv = new reqv();
				$reqv->BindRequest($_POST);
				$error = $reqv->CheckInput();
				if (!$error){
		    		$reqv->user_id = get_uid();
					$reqv->Add($err);
					header("Location: /{$this->name_page}/bank/");
					exit;
				}
				
				front::og("tpl")->sum = !$_POST['sum']?$_SESSION['sum']:floatval($_POST['sum']);
				//front::og("tpl")->sum = $_POST['sum'];
				front::og("tpl")->reqv  = $reqv;
				front::og("tpl")->error = $error;
	    	}
	    	
	    	front::og("tpl")->display("bill/bill_bank_step2.tpl");
    	}
    	
    	//front::og("tpl")->display("bill/bill_bank_step1.tpl");
    }
    
    /**
     * ����� �������� ��� ������ ������������ �������
     *
     */
    function cardAction() {
    	front::og("tpl")->page = "index";	
    	
    	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/card_account.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr_meta.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/settings.php");

        $user = new users();
        $user->GetUser($_SESSION['login']);
        $city = $user->city ? city::GetCityName($user->city) : '';
        $reqv = sbr_meta::getUserReqvs($user->uid);
        $card_account = new card_account();
        $card_account->account_id = $this->account->id;
        $order_id = $card_account -> Add();
        $sum = round(trim($_REQUEST['sum']), 4);
        $merchant = settings::GetVariable('billing', 'card_merchant');
    		
    	front::og("tpl")->sum = $sum;
    	front::og("tpl")->card_account = $card_account;
    	front::og("tpl")->order_id     = $order_id;
    	front::og("tpl")->city         = $city;
    	front::og("tpl")->user         = $user;
        front::og("tpl")->reqv         = $reqv[sbr::FT_PHYS];
    	front::og("tpl")->script       = "/scripts/bill2.js";
     

    	if ($order_id > 0) {
         $tpl_file = "";
         if ($merchant) {
             $tpl_file = "_dol";
         }
         front::og("tpl")->display("bill/bill_card_step2{$tpl_file}.tpl");
     }
    }
    
    /**
     * ����� ��������  /sms/ - "������ �� ���"
     *
     */
    // �������� ��������� #0019358
    /*function smsAction() {
    	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sms_services.php");
    	
    	front::og("tpl")->page = "index";
    	front::og("tpl")->display("bill/bill_sms.tpl");		
    }*/
    
    /**
     * ����� �������� /history/ - "������� �����"
     *
     */
    function historyAction() {
    	front::og("tpl")->page = "history";
    	front::og("tpl")->my_uid  = get_uid();
    	front::og("tpl")->caltype = 2; // ���� ������� ������ ��������� (����=1, �������� ���=2) 
    	
    	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
    	
    	/**
    	 * ���������� ����� ��� ������ � AJAX
    	 */
    	require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/bill.common.php");
		front::og("tpl")->xajax     = $xajax; 
		
		
		// ���������� ��� ��������
		$v0 = intval($this->uri[0]); // ��������
    	$v1 = intval($this->uri[1]); // ���������� ��� ���������� �� ��������
    	$v2 = (string)$this->uri[2]; // ���������� �� ����
    	$v3 = (string)$this->uri[3]; // ������ ����������
        // ����� �������� ��� ����� ����������
         if(!isset($_SESSION['v3_sorting_key']) || $_SESSION['v3_sorting_key'] != $v3){
                $_SESSION['v3_sorting_key'] = $v3;
                $v0 = 1;
        }

    	front::og("tpl")->v1 = $v1;
    	front::og("tpl")->v2 = htmlspecialchars($v2);
    	front::og("tpl")->v3 = htmlspecialchars($v3);
    	
    	$month = date('m'); // ������� �����
    	
    	$sday = mktime(0,0,0,date('m'), 1, date('Y'));
    	$eday = mktime(23,59,59,date('m'), date('t'), date('Y'));
    	front::og("tpl")->date_input = "01.".date('m.y')." - ".date('t').".".date('m.y');
    	
    	// ��������� ��������
    	if($v2 != '') {
    		if(strlen($v2)==8) {
    			front::og("tpl")->is_calendar = true;
	    		$day   = intval(substr($v2, 0, 2));
	    		$month = intval(substr($v2, 2, 2));
	    		$year  = intval(substr($v2, 4, 6));
	    			
	    		$day = $day<10?"0".$day:$day;
	    		$month = $month<10?"0".$month:$month;
	    		
	    		$sday = mktime(0,0,0, $month, $day, $year);
	    		$eday = mktime(23,59,59,$month, $day, $year);
	    			
	    		front::og("tpl")->month = $month;
    			front::og("tpl")->monthDay = date('t', $sday);
	    		front::og("tpl")->year = $year;
	    		
	    		front::og("tpl")->date_input = $day.".".$month.".".$year;
	    		front::og("tpl")->caltype = 1;
    		} elseif(strlen($v2)==6) {
    			front::og("tpl")->is_calendar = true;
    			$month = intval(substr($v2, 0, 2));
    			$year  = intval(substr($v2, 2, 4));
    			
    			$month = $month<10?"0".$month:$month;
    			
    			$sday = mktime(0,0,0, $month, 1, $year);
	    		$eday = mktime(23,59,59, $month, date('t', $sday), $year);
	    		
	    		front::og("tpl")->month = $month;
    			front::og("tpl")->monthDay = date('t', $sday);
	    		front::og("tpl")->year = $year;
	    		
	    		front::og("tpl")->date_input = "01.".$month.".".substr($year, 2, 2)." - ".date('t', $sday).".".$month.".".substr($year, 2, 2);
	    		front::og("tpl")->caltype = 1;
    		} elseif(strpos($v2, "-") == 6) {
    			front::og("tpl")->is_calendar = true;
    			$e = explode("-", $v2);
    			
    			$fday   = intval(substr($e[0], 0, 2));
    			$fmonth = intval(substr($e[0], 2, 2));
    			$fyear  = intval(substr($e[0], 4, 2));
    			
    			$tday   = intval(substr($e[1], 0, 2));
    			$tmonth = intval(substr($e[1], 2, 2));
    			$tyear  = intval(substr($e[1], 4, 2));
    			
    			$fday = $fday<10?"0".$fday:$fday;
    			$tday = $tday<10?"0".$tday:$tday;
    			$fmonth = $fmonth<10?"0".$fmonth:$fmonth;
    			$tmonth = $tmonth<10?"0".$tmonth:$tmonth;
    			
    			front::og("tpl")->from_day   = $fday;
    			front::og("tpl")->from_month = $fmonth;
    			front::og("tpl")->from_year  = $fyear;
    			
    			front::og("tpl")->to_day   = $tday;
    			front::og("tpl")->to_month = $tmonth;
    			front::og("tpl")->to_year  = $tyear;
    			
    			$sday = mktime(0,0,0, $fmonth, $fday, $fyear);
	    		$eday = mktime(23,59,59, $tmonth, $tday, $tyear);
    			
	    		front::og("tpl")->month = date('m');
    			front::og("tpl")->monthDay = date('t');
	    		front::og("tpl")->year = date('Y');
	    		
	    		if(date('Y', $eday) == date('Y')) {
	    		    front::og("tpl")->date_input = date('d.m', $sday).date('.y', $sday)." - ".date('d.m', $eday).date('.y', $eday);
	    		} else {
	    		    front::og("tpl")->date_input = date('d.m', $sday).date('.y', $sday)." - ".date('d.m', $eday).date('.y', $eday);
	    		    //$fday.".".$fmonth.($fyear!=date('Y')?".".$fyear:"")." - ".$tday.".".$tmonth.($tyear!=date('Y')?".".$tyear:"");
	    		}
	    		
	    		front::og("tpl")->caltype = 2;
    		}
    	} else {
    		front::og("tpl")->v2 = date('mY');
    	}
    	
    	
    	
    	$type = false; // �������������� ���������� ��� ���������� �����������
    	if($v3 != '') {
    		$case = substr($v3, 0, 1);
    		switch($case) {
    			case "p": // ���� �������
    				$type = ' AND ammount > 0';
    				break;
    			case "m": // ����� �������
    				$type = ' AND ammount < 0';
    				break;
    			case "f": // ������������ �������� ����� ������ �������
    				$ammount = round(substr($v3, 1, strlen($v3)-1), 2);
    				$type    = ' AND ammount = '.($ammount);
    				front::og("tpl")->f = $ammount;
    				break;
    			case "e": // ������ �� ��������
    				$op_code = intval(substr($v3, 1, strlen($v3)-1));
        $opcode = $op_code;
    				if($op_code == 0) break;
        if($op_code == 77) $op_code = '36,77';
        if($op_code == 78) $op_code = '37,78';
        if($op_code == 79) $op_code = '38,79';
    				$type = ' AND op_code IN ('.$op_code.')';
    				front::og("tpl")->opselect = $opcode;
    				break;	
    			/*case "i":
    				$page = intval(substr($v3, 1, strlen($v3)-1));
    				break;	*/
    			case "a": // �� ���������
    			default: 
    				$type = false; 
    				break;
    		}
    	}
    	
    	$page = $v0;
    	if($page<=0) $page = 1;
    	if(strlen($page) > 6) { header("Location: /404.php"); exit; }

    	front::og("tpl")->sort = $v1==0?$v1+1:$v1; // ���������� �� ��������
        if(!isset($_COOKIE['bill_history_pp'])){
            setcookie ('bill_history_pp', 20, time() + 24*60*60*1000,'/');
            $perpage = 20;
        }else{
            $perpage = (int)$_COOKIE['bill_history_pp'];
        }
    	$history = $this->account->searchBillHistory($sday, $eday, $v1, $type, $page, $pages, $total, $perpage); // ����� ������� �� ����������� ���������� �������
            //�������������� ��� ��� ��������� �������� ����� ��� ������
        $sbrIds = array(); $nSbr = 0;
        foreach ($history as $key=>$val) {
            if(in_array($val['op_code'], array(sbr::OP_RESERVE, sbr::OP_DEBIT, sbr::OP_CREDIT))) {
                if (preg_match('~���-(\d+)-[����]/�~', $val['comments'], $m)) {
                    if ((int)$m[1]) {
                        $sbrIds[] = (int)$m[1];
                        $history[$key]['sbrId'] = (int)$m[1]; 
                        $nSbr++;
                    }
                } 
                //$comments = sbr_meta::parseOpComment($comments);
            }
        }
        if ($nSbr) {
            $sbrSchemes = sbr_meta::getShemesSbr($sbrIds);
            if ($sbrSchemes) {
                foreach ($history as $key=>$val) {
                    if((int)$val["sbrId"]) {
                        $val['comments'] = sbr_meta::parseOpComment($val['comments'], null, null, $sbrSchemes[$val["sbrId"]]);
                        $history[$key] = $val;
                    }
                }
            }
        }
        front::og("tpl")->per_page  = $perpage;
    	front::og("tpl")->page_h  = $page;
    	front::og("tpl")->pages_h = $pages;
    	front::og("tpl")->total_h = $total;
//    	echo $pages." - ".$total;
    	/**
    	 * �������� ����������.
    	 */
    	if($history) {
            $acc = null;
	    	foreach($history as $k=>&$v) {
	    		if($v['op_code'] == 23) $exec[$v['id']] = $v['id'];
            if($v['op_code'] == 12 && !trim($v['comments'])) {
                $v['comments'] = '���������� ����� ����� '.account::GetPSName($v['payment_sys']);
            }
	    	}
    	}
        front::og("tpl")->total_fm  = $this->account->getSumAmmountSpentService(get_uid(false));//$o_mny;
    	front::og("tpl")->history  = $history;
    	front::og("tpl")->exec     = @$exec;
    	front::og("tpl")->event    = $this->account->searchBillEvent($sday, $eday); // ����� ������� ������������ � ������ ���������� �������
    	front::og("tpl")->calendar = $this->account->getDateBillOperation($month, false, false, $year);
    	front::og("tpl")->display("bill/bill_history.tpl");	
    }
    
    /**
     * ����� �������� /success/ - "������� �����"
     *
     */
    function successAction() {
        $uid = get_uid();
        if(!$uid) return false;
        require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
   	    if(is_emp()) {
       	   $rand = array(2,4,5,6,8);    
   	    } else {
   	       $rand = array(1,3,5,7);      
   	    }
   	    
   	    $success_type = $this->uri[0];
        front::og("tpl")->success_type = $success_type;
   	    
        $is_pending = 0;
   	    switch($success_type) {
   	    
   	        case 'card' :
   	            break;
   	            
   	        default :
   	        
                $gift =  array(39, 40, 54, 18, 24, 25, 34, 16, 35); // ���� ����� ����� ������������ ���� �������, ��������� �� ������� �� ����� �������, � ��������� ������������ ����� ������ �������
                if($rows = $this->account->getLastOperations($uid)) {
             	    foreach($rows as $ret) {
                      if (isset($ret['is_pending'])) {
                          $is_pending = $ret['is_pending'];
                      }
                 	    $class_name = $this->account->GetOperationClassName($ret['id'], $ret['op_code']);
                 	    if($class_name) {
                 	        require_once $_SERVER['DOCUMENT_ROOT']."/classes/".$class_name.".php"; // account ��� ��������� ������ ������� require_once
                 	    }
                 	    if(method_exists($class_name, "getSuccessInfo") && array_search($ret['op_code'], $gift) === false) {
                 	        $cls = new $class_name();
                 	        $success[] = $cls->getSuccessInfo($ret);    
                 	    } else {
                 	        $success[] = $this->account->getSuccessInfo($ret);
                 	    }
                 	    $notRand = self::getOperation2Promo($ret['op_code']);
                 	    if(($nrkey = array_search($notRand, $rand)) !== false) {
                 	        unset($rand[$nrkey]);
                 	    }
                 	}
                }
   	            break;
        }
        if ($is_pending) {
            front::og("tpl")->is_pending = $is_pending;
        }
        
  	    shuffle($rand);
  	    
  	    if ( $_SESSION['bill.GET']['back'] ) {
  	    	front::og("tpl")->back = $_SESSION['bill.GET']['back'];
  	    }
  	    else {
  	    	front::og("tpl")->back = '';
  	    }
        
        front::og("tpl")->addinfo = $_SESSION['bill.GET']['addinfo'];
	    front::og("tpl")->rand  = $rand[0];
	    
        if($key = $_SESSION[tmp_project::SESS_LAST_KEY]) {
            front::og("tpl")->tmpkey = $key;
            front::og("tpl")->tmpPrj = new tmp_project($key);
        }
            
        front::og("tpl")->success = $success;
        if(isset($_SESSION['bill.GET']))
          	unset($_SESSION['bill.GET']);

        front::og("tpl")->display("bill/bill_success.tpl");	    
    }
    
    /**
     * ����� �������� /cardsuccess/
     *
     */
    function cardsuccessAction() {
        $_SESSION['bill.GET']['addinfo'] = '<a href="/bill/" class="blue">���������</a> �� &laquo;���������� �����&raquo;';
        header('Location: /bill/success/card/');
        exit;
    }
    
    
	function failAction() {
        front::og("tpl")->error = $_SESSION['bill.GET']['error'];
        if(isset($_SESSION['bill.GET']))
          	unset($_SESSION['bill.GET']);
    	front::og("tpl")->display("bill/bill_fail.tpl");
	}
	
    /**
     * ������� ����������� ���� ��������
     *
     * @deprecated �� ������������
     * @param integer $type ��� ��������
     * @return array|boolean ����, ���� ��� �� ������ false
     */
    function getTypeBillOperation($type) {
    	switch($type) {
    		case 1: # ����������
    			$t = array(12);
    			break;	
    		case 2: # �������� "��"
    			$t = array(23);
    			break;
    		case 3: # �������
    			$t = array(16, 17, 18, 24, 25, 26, 27, 34, 35, 39, 42, 52, 66, 67, 68);	
    			break;
    		case 4: # ������
    			$t = array(); // ��������� ���������
    			break;
    		case 5: # ��������
    			$t = array(37, 41,54);
    			break;
    		case 6: # �������� "���"
    			$t = array(23); // ��������� ���������
    			break;
    		case 7: # ����� �������  
    			$t = array(13); //  ��������� ���������
    			break;
    		default: 
    			$t = false; 
    			break;
    	}
    	
    	return $t;
    }
    
    function getOperation2Promo($op_code) {
        switch($op_code) {
            case 15: 
            case 16:
            case 28: 
            case 35:
            case 42:
            case 48:
            case 49:
            case 50:
            case 51:
            case 52:
            case 66:
            case 67:
            case 68: 
            case 76: 
                $r = 7;
                break;
            case 7:
            case 8;
            case 53:
                $r = 8;
                break; 
            case 36:
                $r = 6;
                break;
            case 45:
                $r = 5;
                break;
            case 10:
            case 11:
            case 17:
            case 18:
                $r = 3;
                break;
            case 9:
            case 72:
                $r = 2;
                break; 
            case 19:
            case 25:
            case 29:
            case 73:
                $r = 1;
                break;  
            default: $r = 0; break;                       
        }
        
        return $r;
    }
    
    function isBlockMoney() {
        if($this->account->is_block == 't') {
            front::og("tpl")->display("bill/bill_block.tpl");
            die();
        }
    }
}    
?>
