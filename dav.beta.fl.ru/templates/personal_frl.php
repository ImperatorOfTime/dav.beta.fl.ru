<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_dialogue.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/notifications.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');

$user_link = "/users/{$_SESSION['login']}";

$anti_login = ($_SESSION['anti_uid']) ? $_SESSION['anti_login'] : '�����';

$projects_active = $grey_main && $kind != 8 && $kind != 2 && $kind != 4;
$konkurs_active = $grey_main && $kind == 2;
$vacancy_active = $grey_main && $kind == 4;
$grey_catalog = isset($grey_catalog) && (@$grey_catalog == 1);


//����������� ��
$tip_msg = notifications::getMessTip();

//����������� � ������ ��� ��������� ������ ���� "������� � ������"
/*
if ($_SESSION['po_count'] || @$_SESSION['tu_orders']):
    $tip_group = notifications::getFrlGroupTip();
endif;
*/


//����������� � ������ ��� ��������
if ($_SESSION['po_count']):
    $tip_prj = notifications::getProjectsTipFrl();
endif;


//����������� � ������ ��� ������� ��
if (@$_SESSION['tu_orders']):
    $tip_tu = notifications::getTServicesOrdersTip();
endif;


//����������� �� ��
$tip_sbr = notifications::getAllSbrTip();
$link_sbr = '/' . sbr::NEW_TEMPLATE_SBR . '/';

$tserviceOrderModel = TServiceOrderModel::model();
$isNewTserviceOrders = $tserviceOrderModel->checkNewOrders(get_uid(false));
if ($isNewTserviceOrders) {
    $_SESSION['has_new_tservices_orders'] = 1;
}


$account_sum_is_plus = $_SESSION['ac_sum'] >= 0;
$account_sum_format = view_account_format();        
        
?>
<script type="text/javascript">
var notification_delay = '<?=NOTIFICATION_DELAY?>';
var prj_check_delay = '<?=PRJ_CHECK_DELAY?>';
</script>

<div class="b-bar b-bar_fixed">                                    
    <div class="l-outer">

        <header class="l-header">
            <div class="l-header-inside">

                <section class="l-header-section l-header-first-section">

                    <span class="b-logo">
                        <a href="/" class="b-logo-link" title="�� �������">FL.ru</a>
                    </span>

                    <ul class="b-primary-menu">
                        <li class="b-primary-menu-clause b-primary-menu-tasks-clause<?php if ($projects_active) { ?> b-primary-menu-current-clause<?php } ?>">
                            <a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'projects'}" href="/projects/" class="b-primary-menu-clause-link" title="������ �������� ��� �����������">������</a>
                        </li>
                        <li class="b-primary-menu-clause b-primary-menu-employees-clause<?php if ($grey_catalog) { ?> b-primary-menu-current-clause<?php } ?>">
                            <a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'freelancers'}" href="/freelancers/" class="b-primary-menu-clause-link" title="������� �����������">����������</a>
                        </li>
                        <li class="b-primary-menu-clause b-primary-menu-services-clause <?= isCurrentPage('tu','b-primary-menu-current-clause','',null,array(1 => 'order')) ?>">
                            <a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'tu'}" href="/tu/" class="b-primary-menu-clause-link" title="������� ����� �����������">������</a>
                        </li>
                        <li class="b-primary-menu-clause b-primary-menu-competitions-clause<?php if ($konkurs_active) { ?> b-primary-menu-current-clause<?php } ?>">
                            <a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'konkurs'}" href="/konkurs/" class="b-primary-menu-clause-link" title="������ ��������� ��� �����������">��������</a>
                        </li>
                        <li class="b-primary-menu-clause b-primary-menu-vacancy-clause<?php if ($vacancy_active) {?> b-primary-menu-current-clause<?php }?>">
                           <a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'vacancy'}" href="/projects/?kind=4" class="b-primary-menu-clause-link" title="������ �������� ��� �����������">��������</a>
                        </li>
                        <li class="b-primary-menu-clause b-primary-menu-search-clause <?= isCurrentPage('search','b-primary-menu-current-clause','') ?>">
                            <a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'search'}" href="/search/" class="b-primary-menu-clause-link" title="����� �� �����">�����</a>
                        </li>
                        <li class="b-primary-menu-clause b-primary-menu-additional-clause">
                            <div class="b-dropdown b-primary-menu-dropdown" data-dropdown="true" data-dropdown-descriptor="primary-menu">
                                <a href="#" class="b-dropdown-opener" data-dropdown-opener="true" title="���������">���</a>
                                <div class="b-dropdown-concealment g-hidden" data-dropdown-concealment="true">
                                    <ul class="b-dropdown-concealment-options">
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-tasks-clause<?php if ($projects_active) { ?> b-dropdown-concealment-options-current-clause<?php } ?>">
                                            <a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'projects'}" href="/projects/" class="b-dropdown-concealment-options-clause-link" title="������ �������� ��� �����������">������</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-employees-clause<?php if ($grey_catalog) { ?> b-dropdown-concealment-options-current-clause<?php } ?>">
                                            <a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'freelancers'}" href="/freelancers/" class="b-dropdown-concealment-options-clause-link" title="������� �����������">����������</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-services-clause <?= isCurrentPage('tu','b-dropdown-concealment-options-current-clause','',null,array(1 => 'order')) ?>">
                                            <a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'tu'}" href="/tu/" class="b-dropdown-concealment-options-clause-link" title="������� ����� �����������">������</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-competitions-clause<?php if ($konkurs_active) { ?> b-dropdown-concealment-options-current-clause<?php } ?>">
                                            <a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'konkurs'}" href="/konkurs/" class="b-dropdown-concealment-options-clause-link" title="������ ��������� ��� �����������">��������</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-vacancy-clause<?php if ($vacancy_active) {?> b-dropdown-concealment-options-current-clause<?php }?>">
                                            <a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'vacancy'}" href="/projects/?kind=4" class="b-dropdown-concealment-options-clause-link" title="������ �������� ��� �����������">��������</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-search-clause">
                                            <a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'search'}" href="/search/" class="b-dropdown-concealment-options-clause-link" title="����� �� �����">�����</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-communities-clause <?= isCurrentPage('commune','b-dropdown-concealment-options-current-clause','') ?>">
                                            <a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'commune'}" href="/commune/" class="b-dropdown-concealment-options-clause-link" title="������ ���������">����������</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-reclam-clause">
                                            <a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'promo_adv'}" href="/promo/adv/" class="b-dropdown-concealment-options-clause-link" title="������� �� �����">������� �� �����</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-faq-clause">
                                            <noindex><a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'help'}" onmouseover="UE.Popin.preload();" onclick="UE.Popin.show(); return false;" rel="nofollow" target="_blank" href="https://feedback.fl.ru/" class="b-dropdown-concealment-options-clause-link" title="������">������</a></noindex>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-faq-clause">
                                           <noindex><a data-ga-event="{ec: 'freelancer', ea: 'main_menu_clicked',el: 'promo_mbm'}" rel="nofollow" target="_blank" href="/promo/mbm/" class="b-dropdown-concealment-options-clause-link" title="����� ������ ������">����� ������ ������</a></noindex>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <ul class="b-primary-menu">
                        <li class="b-primary-menu-clause b-primary-menu-faq-clause">
                            <noindex><a rel="nofollow" target="_blank" href="https://feedback.fl.ru/" class="b-primary-menu-clause-link" title="������">������</a></noindex>
                        </li>
                    </ul>
                    
                    
                    <?php  
                        //����� ����� ��������
                        require_once("personal_au_form.php"); 
                    ?>
                    
                    <ul class="b-user-menu b-user-employee-menu" data-antiuser="false">
                        <li class="b-user-menu-clause b-user-menu-messages-clause <?= isCurrentPage('contacts','b-user-menu-current-clause','') ?>">
                            <a href="/contacts/" class="b-user-menu-clause-link" title="<?= $tip_msg['tip'] ?>">
                                ���������
                                <?= view_event_count_format($tip_msg['count']) ?>
                            </a>
                        </li>
                        <?php if (isset($tip_prj)): ?>
                        <li class="b-user-menu-clause b-user-menu-tasks-clause <?= isCurrentPage('proj','b-user-menu-current-clause','') ?>">
                            <a href="<?= $tip_prj['link'] ?>" class="b-user-menu-clause-link" title="<?= $tip_prj['tip'] ?>">
                                �������
                                <?= view_event_count_format($tip_prj['count']) ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(isset($tip_tu)): ?>
                        <li class="b-user-menu-clause b-user-menu-orders-clause <?= isCurrentPage('tu-orders','b-user-menu-current-clause','') ?> <?= isCurrentPage(array('tu','order'),'b-user-menu-current-clause','') ?>">
                            <a href="<?= $tip_tu['link'] ?>" class="b-user-menu-clause-link" title="<?= $tip_tu['tip'] ?>">
                                ������
                                <?= view_event_count_format($tip_tu['count']) ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php /*
                        <li class="b-user-menu-clause b-user-menu-contracts-clause <?= isCurrentPage(sbr::NEW_TEMPLATE_SBR,'b-user-menu-current-clause','') ?>">
                            <a href="<?= $link_sbr ?>" class="b-user-menu-clause-link" title="<?= $tip_sbr['tip'] ?>">
                                ������
                                <?= view_event_count_format($tip_sbr['count']) ?>
                            </a>
                        </li>
                        <li class="b-user-menu-clause b-user-menu-digest-clause <?= isCurrentPage('lenta','b-user-menu-current-clause','') ?>">
                            <a href="/lenta/" class="b-user-menu-clause-link" title="����� ����� ����� � ���������">�����</a>
                        </li>
                        */ ?>
                        <?
                        
                        //��� �� ��� �� ���������?
                        
                        $freeze_info = '';
                        if ($_SESSION['freeze_from']) {
                            if ($_SESSION['is_freezed']) {
                                $freeze_info = "����� ��������� ��������� PRO ����� ����������� �� " . date('d.m.Y', strtotime($_SESSION['payed_to']));
                            } else {
                                $freeze_info = "� ������ ��������� � ";
                                $freeze_info .= date('d.m.Y', strtotime($_SESSION['freeze_from'])) . " �� " . date('d.m.Y', strtotime($_SESSION['freeze_to']));
                            }
                        }
                        ?>
                        <?php if (isProfi()) { ?>
                            <li class="b-user-menu-clause b-user-menu-profi-clause">
                                <a href="/profi/" class="b-user-menu-clause-link" title="������� <?= pro_days($_SESSION['pro_last']) ?>">PROFI</a>
                            </li>
                        <?php } elseif ($_SESSION['pro_last']) { ?>
                            <li class="b-user-menu-clause b-user-menu-pro-clause">
                                <a href="/payed/" class="b-user-menu-clause-link" title="������� <?= pro_days($_SESSION['pro_last']) ?>">PRO</a>
                            </li>
                        <?php } elseif ($_SESSION['is_freezed']) { ?>
                            <li class="b-user-menu-clause b-user-menu-pro-clause b-user-menu-frozen-pro-clause">
                                <a href="/payed/" class="b-user-menu-clause-link" title="<?= pro_days($_SESSION['freeze_to'], '��') ?>">PRO</a>
                            </li>
                        <?php } else { ?>
                            <li class="b-user-menu-clause b-user-menu-pro-clause b-user-menu-completed-pro-clause">
                                <a href="/payed/" class="b-user-menu-clause-link" title="������ ������� ���">PRO</a>
                            </li>
                        <?php } ?>

                        <li class="b-user-menu-clause b-user-menu-additional-clause">
                            <div class="b-dropdown b-user-menu-dropdown" data-dropdown="true" data-dropdown-descriptor="user-menu">
                                <a href="<?= $user_link ?>" class="b-dropdown-opener" data-dropdown-opener="true" title="<?= view_fullname() ?>">
                                    <span class="b-dropdown-opener-picture">
                                        <?= view_avatar($_SESSION['login'], $_SESSION['photo'], 0, 1, 'b-dropdown-opener-picture-entity') ?>
                                    </span>
                                </a>
                                <div class="b-dropdown-concealment g-hidden" data-dropdown-concealment="true">
                                    <ul class="b-dropdown-concealment-options">
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-profile-clause">
                                            <a title="�������" class="b-dropdown-concealment-options-clause-link" href="<?= $user_link?>">�������</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-messages-clause">
                                            <a href="/contacts/" class="b-dropdown-concealment-options-clause-link" title="<?= $tip_msg['tip'] ?>">���������</a>
                                        </li>
                                        <?
                                        if (isset($tip_prj)):
                                        ?>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-tasks-clause">
                                            <a href="<?= $tip_prj['link'] ?>" class="b-dropdown-concealment-options-clause-link" title="<?= $tip_prj['tip'] ?>">
                                                �������
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <?php if(isset($tip_tu)): ?>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-orders-clause">
                                            <a href="<?= $tip_tu['link'] ?>" class="b-dropdown-concealment-options-clause-link" title="<?= $tip_tu['tip'] ?>">
                                                ������
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-contracts-clause">
                                            <a href="<?= $link_sbr ?>" class="b-dropdown-concealment-options-clause-link" title="<?= $tip_sbr['tip'] ?>">������</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-digest-clause">
                                            <a href="/lenta/" class="b-dropdown-concealment-options-clause-link" title="����� ����� ����� � ���������">�����</a>
                                        </li>
                                        <? if ($_SESSION['pro_last']) { ?>
                                            <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-pro-clause">
                                                <a href="/payed/" class="b-dropdown-concealment-options-clause-link" title="������� <?= pro_days($_SESSION['pro_last']) ?>">PRO-�������</a>
                                            </li>
                                        <? } elseif ($_SESSION['is_freezed']) { ?>
                                            <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-pro-clause">
                                                <a href="/payed/" class="b-dropdown-concealment-options-clause-link" title="<?= pro_days($_SESSION['freeze_to'], '��') ?>">PRO-�������</a>
                                            </li>
                                        <? } else { ?>
                                            <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-pro-clause">
                                                <a href="/payed/" class="b-dropdown-concealment-options-clause-link" title="������ ������� ���">PRO-�������</a>
                                            </li>
                                        <? } ?>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-statistics-clause">
                                            <a href="/promotion/" class="b-dropdown-concealment-options-clause-link" title="���������� ��������">����������</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-wallet-clause">
                                            <a href="/bill/history/?period=3" class="b-dropdown-concealment-options-clause-link" title="<?= $account_sum_format ?>">
                                                ����
                                                <span class="b-txt b-txt_pa b-txt_right_20 b-txt_lh_40 b-txt_fs_14 b-txt_ff_hn b-txt_color_<?php if($account_sum_is_plus): ?>fd6c30<?php else: ?>c10600<?php endif; ?>">
                                                    <?= $account_sum_format ?>
                                                </span>
                                            </a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-settings-clause">
                                            <a href="<?= $user_link ?>/setup/" class="b-dropdown-concealment-options-clause-link" title="��������� ��������">���������</a>
                                        </li>
                                        <?php if (hasPermissions('adm')) { ?>
                                            <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-switch-clause">
                                                <a class="b-dropdown-concealment-options-clause-link" href="/siteadmin/">�������</a>
                                            </li>
                                        <?php }//if?>
                                        <?php if (hasPermissions('sbr') || hasPermissions('sbr_finance')) { ?>
                                            <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-switch-clause">
                                                <a class="b-dropdown-concealment-options-clause-link" href="/norisk2/?site=admin">������� ��</a>
                                            </li>
                                            <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-switch-clause">
                                                <a class="b-dropdown-concealment-options-clause-link" href="/siteadmin/reserves/">������� ������� �� ��</a>
                                            </li>                                             
                                        <?php }//if?>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-switch-clause">
                                            <a data-toggle-action="antiuser" href="javascript:void(0);" class="b-dropdown-concealment-options-clause-link" title="������� � ������� ������������">����� �������������</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-exit-clause">
                                            <a href="javascript:void(0);" class="b-dropdown-concealment-options-clause-link" title="����� �� �������� ����������" onclick="Bar_Ext.logout();">�����</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>

                </section>
            </div>
        </header>

    </div>
</div>
<?php if ($isNewTserviceOrders): ?>
<div class="b-page__desktop">
	<div class="l-outer w-outer">
		<header class="l-header">
			<div class="l-header-inside">
				<section class="l-header-section l-header-second-section">
					<div class="b-general-notification">
						� ��� ���� ���������������� ������ 
						<a class="b-general-notification-link b-general-notification-employee-link" href="/tu-orders/?s=new">���������� ������</a> 
					</div>
				</section> 
			</div>
		</header>
	</div>
</div>
<?php elseif ($mes = SubBarNotificationHelper::getInstance()->showMessage()): ?>
    <?=$mes?>
<?php else: ?>
    <?=isset($user_phone_block)?$user_phone_block:''?>
<?php endif; ?>