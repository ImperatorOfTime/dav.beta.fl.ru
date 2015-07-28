<?php
$noJSLogin = isJSPromlebBrowser();

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/bar_notify.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Helpers/PopupAfterPageLoaded.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Helpers/SubBarNotificationHelper.php");

if ($_SESSION['uid']) {
    
    checkProLast();
    
    // ���������� ����� ������ ���������
    $iMsgsCount = messages::GetNewMsgCount( get_uid(false), $err );
    $_SESSION['newmsgs'] = intval( $iMsgsCount );

    
    //��� ���� �� ������������ �����
    /*
    // ���������� ����� ��������� � ���
    $iMsgsCount = sbr_meta::GetNewMsgCount( get_uid(false) );
    $_SESSION['sbr_newmsgs'] = intval( $iMsgsCount );

    // ���������� ����������
    $iDraftsCount = intval($_SESSION['drafts_count']);

    $barNotify = new bar_notify($_SESSION['uid']);

    // �������� ������������� �����������
    $barNotifies = $barNotify->getNotifies();

    // bill
    $oldAccountToolTip = $accountToolTip; // ��� ����� ���� ������ � �������
    $accountToolTip = null;
    if ($barNotifies['bill']) {
        $accountToolTip = $barNotifies['bill']['message'];
        $accountBtnActive = (bool)$barNotifies['bill']['message'];

    }
    if (!$accountToolTip && $oldAccountToolTip) {
        $accountToolTip = $oldAccountToolTip;
        $accountBtnActive = true;
    } elseif (!$accountToolTip && (int)$_SESSION['bn_sum']) {
        $accountToolTip = "� ��� �� ����� " . number_format(round(zin($_SESSION['bn_sum']),2), 2, ",", " ") . " �������� ���.";
    }
    */
    
    
    
    //@todo ������ ������� ������� �� ��
    require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
    $_SESSION['tu_orders'] = (TServiceOrderModel::model()->isExist($_SESSION['uid']) > 0);
    

    $role = $_SESSION['role'];
    if (is_emp($role))
        include($_SERVER['DOCUMENT_ROOT'] . "/templates/personal_emp.php");
    else
        include($_SERVER['DOCUMENT_ROOT'] . "/templates/personal_frl.php");

    //include_once($_SERVER['DOCUMENT_ROOT'].'/user/safety_phone.php');
}
else 
{

$projects_active = $grey_main && $kind != 8 && $kind != 2 && $kind != 4;
$konkurs_active = $grey_main && $kind == 2;
$vacancy_active = $grey_main && $kind == 4;
$grey_catalog = isset($grey_catalog) && (@$grey_catalog == 1);


?>
<div class="b-bar b-bar_fixed">                                    
<div class="l-outer">
    
    <header class="l-header">
        <div class="l-header-inside">
        
            <section class="l-header-section l-header-first-section">
        
                <span class="b-logo">
                    <a href="/" class="b-logo-link" title="�� �������">FL.ru</a>
                </span>
        
                <ul class="b-primary-menu">
                    <li class="b-primary-menu-clause b-primary-menu-tasks-clause<?php if ($projects_active) {?> b-primary-menu-current-clause<?php }?>">
                        <a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'projects'}" href="/projects/" class="b-primary-menu-clause-link" title="������ �������� ��� �����������">������</a>
                    </li>
                    <li class="b-primary-menu-clause b-primary-menu-employees-clause<?php if ($grey_catalog) {?> b-primary-menu-current-clause<?php }?>">
                        <a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'freelancers'}" href="/freelancers/" class="b-primary-menu-clause-link" title="������� �����������">����������</a>
                    </li>
                    <li class="b-primary-menu-clause b-primary-menu-services-clause <?= isCurrentPage('tu','b-primary-menu-current-clause','') ?>">
                        <a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'tu'}" href="/tu/" class="b-primary-menu-clause-link" title="������� ����� �����������">������</a>
                    </li>
                    <li class="b-primary-menu-clause b-primary-menu-competitions-clause<?php if ($konkurs_active) {?> b-primary-menu-current-clause<?php }?>">
                        <a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'konkurs'}" href="/konkurs/" class="b-primary-menu-clause-link" title="������ ��������� ��� �����������">��������</a>
                    </li>
                    <li class="b-primary-menu-clause b-primary-menu-vacancy-clause<?php if ($vacancy_active) {?> b-primary-menu-current-clause<?php }?>">
                        <a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'vacancy'}" href="/projects/?kind=4" class="b-primary-menu-clause-link" title="������ �������� ��� �����������">��������</a>
                    </li>
                    <li class="b-primary-menu-clause b-primary-menu-search-clause <?= isCurrentPage('search','b-primary-menu-current-clause','') ?>">
                        <a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'search'}" href="/search/" class="b-primary-menu-clause-link" title="����� �� �����">�����</a>
                    </li>
                    <li class="b-primary-menu-clause b-primary-menu-additional-clause">
                        <div class="b-dropdown b-primary-menu-dropdown" data-dropdown="true" data-dropdown-descriptor="primary-menu">
                            <a href="#" class="b-dropdown-opener" data-dropdown-opener="true" title="���������">���</a>
                            <div class="b-dropdown-concealment g-hidden" data-dropdown-concealment="true">
                                <ul class="b-dropdown-concealment-options">
                                    <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-tasks-clause<?php if ($projects_active) {?> b-dropdown-concealment-options-current-clause<?php }?>">
                                        <a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'projects'}" href="/projects/" class="b-dropdown-concealment-options-clause-link" title="������ �������� ��� �����������">������</a>
                                    </li>
                                    <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-employees-clause<?php if ($grey_catalog) {?> b-dropdown-concealment-options-current-clause<?php }?>">
                                        <a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'freelancers'}" href="/freelancers/" class="b-dropdown-concealment-options-clause-link" title="������� �����������">����������</a>
                                    </li>
                                    <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-services-clause <?= isCurrentPage('tu','b-dropdown-concealment-options-current-clause','') ?>">
                                        <a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'tu'}" href="/tu/" class="b-dropdown-concealment-options-clause-link" title="������� ����� �����������">������</a>
                                    </li>
                                    <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-competitions-clause<?php if ($konkurs_active) {?> b-dropdown-concealment-options-current-clause<?php }?>">
                                        <a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'konkurs'}" href="/konkurs/" class="b-dropdown-concealment-options-clause-link" title="������ ��������� ��� �����������">��������</a>
                                    </li>
                                     <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-vacancy-clause<?php if ($vacancy_active) {?> b-dropdown-concealment-options-current-clause<?php }?>">
                                         <a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'vacancy'}" href="/projects/?kind=4" class="b-dropdown-concealment-options-clause-link" title="������ �������� ��� �����������">��������</a>
                                     </li>
                                    <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-search-clause">
                                        <a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'search'}" href="/search/" class="b-dropdown-concealment-options-clause-link" title="����� �� �����">�����</a>
                                    </li>
                                    <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-communities-clause <?= isCurrentPage('commune','b-dropdown-concealment-options-current-clause','') ?>">
                                        <a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'commune'}" href="/commune/" class="b-dropdown-concealment-options-clause-link" title="������ ���������">����������</a>
                                    </li>
                                    <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-reclam-clause">
                                         <a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'promo_adv'}" href="/promo/adv/" class="b-dropdown-concealment-options-clause-link" title="������� �� �����">������� �� �����</a>
                                    </li>
                                    <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-faq-clause">
                                        <noindex><a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'help'}" onmouseover="UE.Popin.preload();" onclick="UE.Popin.show(); return false;" rel="nofollow" target="_blank"  href="https://feedback.fl.ru/" class="b-dropdown-concealment-options-clause-link" title="������">������</a></noindex>
                                    </li>
                                    <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-faq-clause">
                                        <noindex><a data-ga-event="{ec: 'user', ea: 'main_menu_clicked',el: 'promo_mbm'}" rel="nofollow" target="_blank" href="/promo/mbm/" class="b-dropdown-concealment-options-clause-link" title="����� ������ ������">����� ������ ������</a></noindex>
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
                <?php if (!isset($registration_page)): ?>
                <div class="b-dropdown b-identification-dropdown b-dropdown__login" data-dropdown="true" data-dropdown-descriptor="identification">
                    <a href="/registration/" class="b-dropdown-opener" data-dropdown-opener="true" title="���� ��� �����������">���� ��� �����������</a>
                    <div class="b-dropdown-concealment" data-dropdown-concealment="true">
                        
                         <form id="lfrm" class="b-form b-authorization-form g-cleared" method="post" action="/">
                            <section class="b-form-section b-form-social-section b-form-section_center b-form-section_maxwidth_820  b-layout_hide">
                                <a href="/auth/?param=vkontakte" class="b-auth_btn b-auth_medium b-auth_btn_vk"></a>
                                <a href="/auth/?param=facebook" class="b-auth_btn b-auth_medium b-auth_btn_facebook"></a>
                                <a href="/auth/?param=odnoklassniki" class="b-auth_btn b-auth_medium b-auth_btn_odnoklassniki"></a>
                                <div class="b-layout b-layout__txt b-layout__txt_padtop_10 b-auth_delimitter b-auth_delimitter_white">
                                    <span>���</span>
                                </div>
                            </section>

                            <input type="hidden" name="action" value="login" />
                            <input type="hidden" name="autologin" value="1" />

                            <section class="b-form-section b-form-login-section b-form-section_noicon">
                                <div class="b-text-field">
                                    <input type="text" name="login" placeholder="�����" class="b-text-field-entity" >
                                </div>
                            </section>
                    
                            <section class="b-form-section b-form-password-section b-form-section_noicon">
                                <div class="b-text-field">
                                    <input type="password" name="passwd" placeholder="������" class="b-text-field-entity" >
                                </div>
                            </section>
                    
                            <section class="b-form-section b-form-send-section">
                                <input type="submit" value="�����" class="b-medium-button b-green-medium-button">
                            </section>

                            <section class="b-form-section b-form-social-section b-form-section_minwidth_821">
                                <span class="b-form-text">���</span>
                                <a href="/auth/?param=vkontakte" class="b-auth_btn b-auth_mini b-auth_btn_vk"></a>
                                <a href="/auth/?param=facebook" class="b-auth_btn b-auth_mini b-auth_btn_facebook"></a>
                                <a href="/auth/?param=odnoklassniki" class="b-auth_btn b-auth_mini b-auth_btn_odnoklassniki"></a>
                            </section>
                    
                            <section class="b-form-section b-form-registration-section">
                                <a href="/registration/" class="b-form-registration-link" title="�����������">�����������</a>
                            </section>
                        </form>
                        
                    </div>
                </div>
                <?php endif; ?>
            </section>
    
        </div>
    </header>
</div>
</div>
<?}//else?>
<?=PopupAfterPageLoaded::getInstance()->render()?>