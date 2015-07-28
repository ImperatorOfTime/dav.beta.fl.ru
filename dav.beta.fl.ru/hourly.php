<?php
ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

require_once("classes/config.php");
require_once("classes/log.php");
require_once("classes/multi_log.php");
$log = new log('hourly/'.SERVER.'-%d%m%Y[%H].log', 'w');

$log->writeln('------------ BEGIN hourly (start time: ' . date('d.m.Y H:i:s') . ') -----');

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/contacts.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sitemap.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stats.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/hh.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/maintenance.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search_parser.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/spam.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/articles.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users_suspicious_contacts.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/FreelancerCatalog.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/annoy.php");


$mail = new smail();
$mail2 = new smail2();
$spam = new spam();
$H = (int)date('H');


/**
 * ������ ������� ������������� 
 * ����� ������ ��� ������������
 */
annoy::clearRepeatPassByCnt();

//$cfile = new CFile();
//$log->TRACE( $cfile->removeDeleted() );

if ( $H == 0 ) {
    $log->TRACE( $traffic_stat->calculateStatsIp() );
}

// ��������� ����� �����
// try {
//     $log->TRACE( sitemap::update('blogs') );
// } catch(Exception $e) {
// 	$log->TRACE($e->getMessage());
// }

try {
    $log->TRACE( sitemap::update('projects'));
} catch(Exception $e) {
	$log->TRACE($e->getMessage());
}

try {
    $log->TRACE( sitemap::update('commune') );
} catch (Exception $e) {
    $log->TRACE($e->getMessage());
}

// try {
//     $log->TRACE( sitemap::update('articles'));
// } catch (Exception $e) {
//     $log->TRACE($e->getMessage());
// }

try {
    $log->TRACE( sitemap::update('portfolio') );
} catch (Exception $e) {
    $log->TRACE($e->getMessage());
}

try {
    $log->TRACE( sitemap::update('users') );
} catch (Exception $e) {
    $log->TRACE($e->getMessage());
}

try {
    $log->TRACE( sitemap::update('catalog') );
} catch (Exception $e) {
    $log->TRACE($e->getMessage());
}

try {
    $log->TRACE( sitemap::update('userpages') );
} catch (Exception $e) {
    $log->TRACE($e->getMessage());
}

try {
    $log->TRACE( sitemap::update('tservices') );
} catch (Exception $e) {
    $log->TRACE($e->getMessage());
}

try {
    $log->TRACE( sitemap::generateMainSitemap() );
    $log->TRACE( sitemap::send() );
} catch (Exception $e) {
    $log->TRACE($e->getMessage());
}

// ������ ������ ����������, �� �� �������������� ������
$log->TRACE( attachedfiles::clearOldSessions() );

//------------------------------------------------------------------------------

// �������� ��� � ��� ��� ��� ���������� ����� ����
// �������� ��� ��� � ���� �������� ����� � ��� ��� � ���� ���
// �� ��������� � ����� ���� ���� ���������� �� getPROEnding
// ��� ��� ���� ������������� ��������������
$log->TRACE( payed::getPROEnding(true, 3));// �� 3 ��� ��� ��� � ���� �������� �������������
$log->TRACE( payed::getPROEnding(true, 1));// �� 1 ���� ��� ��� � ���� �������� �������������
$log->TRACE( payed::getPROEnding(false, 3)); // �� 3 ��� ��� ��� � ���� �� �������� �������������
$log->TRACE( payed::getPROEnding(false, 1)); // �� 1 ���� ��� ��� � ���� �� �������� �������������


//@todo: ���� ��������� ����������� �� ������������� ��� ��� ��� ��������� �������������
//@todo: ��� ��� � �������� ��������
//$log->TRACE( payed::checkAutoPRO());

// ��������� email ��� ��� � ���� �������� ������������� PRO � �� ���������� ����� 1 ����
// @todo: ���� ������������� �� ������������
// @todo: ������ ��������� ����� ��������� ����� ���� getPROEnding ���������� � ���� �������?
//$log->TRACE( payed::AlertPROEnding() );

//------------------------------------------------------------------------------


// ������� ����������� �������� ���� � ��� � ������� 2� ���� �� ���� �� ������ ������
$log->TRACE( projects::autoSetTopProject());

// ��� � ��� ������������� �������� ��������� (������������� ������ ���������� "�������")
$log->TRACE( commune::recalcThemesCountCommunes(commune::COMMUNE_BLOGS_ID) );

if(date("H") == 1) {
    $log->TRACE( $mail->SendWarnings() ); // ���������� �������������� ����� � ���, ��� ������� ��� �������� � ��������� ���.
    $temp = new users;
    $rpath = "";
    $log->TRACE( $temp->DropInactive() );
    // ����� ���������� ��� ������� #0003426
    $log->TRACE( stats::writeGeneralStat() );
    // �������� ��� ����� ����������� � ���������
    $log->TRACE( professions::calcAvgPrices() );
}

if (date("H") == 2) {

	$log->TRACE( $mail->ContestReminder() );
	$log->TRACE( $mail->ContestEndReminder() );

    // ������ �� ���������� �������
	$log->TRACE(billing::checkOldReserve());
}


//------------------------------------------------------------------------------

/**
 * ����������� ����������� ��
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_smail.php');
$tservices_smail = new tservices_smail();
$log->TRACE($tservices_smail->remind24hEndBinds());//�� �����
$log->TRACE($tservices_smail->remindBindsUp());//���� ��������� ���� 4 ������� ������������

//------------------------------------------------------------------------------

//�� ����� �� ���������� ����� �������� �����������
$mail->remindFreelancerbindsProlong();

//����� ����, ��� ����������� ���������� ���� �������� ������ ����������� (� � ������ ������ ������ �����������)
$mail->remindFreelancerbindsUp();


//------------------------------------------------------------------------------

/**
 * ���������� ���������� ������������� � �������� �������� �����������
 */
$catalog = new FreelancerCatalog();
$log->TRACE($catalog->recalcCounters());

//------------------------------------------------------------------------------

/**
 * �������� ���������� ������������� �� � ������ ���������
 */
if (date("H") == 2) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_categories.php");
	tservices_categories::ReCalcCategoriesCount();
}

//------------------------------------------------------------------------------

if (date("H") == 6) {
	$log->TRACE( professions::ReCalcProfessionsCount() );
    $hh = new hh();
    $log->TRACE( $hh->delOldFilters() );
    $log->TRACE( $mail->employerHelpInfo() );
}
$log->TRACE( professions::PaidSpecsEndingReminder() );

// ban
$usr=new users();
$log->TRACE( $usr->GetBanTimeout() );

//���������� ���������� ������
$log->TRACE( $usr->UpdateInactive() );

if (date("H") == 0 || date("H") == 6 || date("H") == 12 || date("H") == 18) {
    // ��������� xml ��� webprof
    $log->TRACE( freelancer::webprofGenerateRss('upload/webprof.xml') );
}

// ��������� xml ��� ������.������
$log->TRACE( new_projects::yandexGenerateRss('upload/yandex-office.xml', array(4)) );
$log->TRACE( new_projects::yandexGenerateRss('upload/yandex-project.xml', array(1, 2, 7)) );

// ��������� xml ��� Jooble.ru, indeed � trovit
$projects_for_xml  = new_projects::getProjectsForXml('1 month');
$log->TRACE( new_projects::joobleGenerateRss('upload/jooble.xml', $projects_for_xml) );
$log->TRACE( new_projects::indeedGenerateRss('upload/indeed.xml', $projects_for_xml) );
$log->TRACE( new_projects::trovitGenerateRss('upload/trovit.xml', $projects_for_xml) );

// ��������� xml ��� joobradio
if(date("H")==4) {
    $log->TRACE( new_projects::jobradioGenerateRss('upload/jobradio.xml') );
    
    if ( users_suspicious_contacts::getResetContacts() ) {
        users_suspicious_contacts::resetContacts();
        users_suspicious_contacts::setResetContacts();
    }
}
// ��������� xml ��� careerjet
if(date("H")==23) {
    $log->TRACE( new_projects::careerjetGenerateRss('upload/careerjet.xml') );
}
// ��������� xml ��� adWords
if(date("H")==3) {
    $log->TRACE( new_projects::adWords('upload/adwords.csv') );
}

// ���� ����������.
$scl = new stat_collector();
$log->TRACE( $scl->Run() );
$log->TRACE( $scl->wordsStatRun() );
if(date("H") == 1) {
    // ���������� stat_monthly
    $log->TRACE( $scl->stat_monthly_split() );
}


// �������� email ��� � ���� ������������� ����������� ������� ������� ������� ��������
$log->TRACE( $mail->EndTopDaysPrjSendAlerts() );

if (date("H") == 7){
    $log->TRACE( $mail->sendYdDayRegistry() );
    //$log->TRACE( $mail->SbrReqvAlerts() );
    $log->TRACE( $mail->SbrDeadlineAlert() );
}


// ������� ����� �� �����
$pp = new pay_place();
$log->TRACE( $pp->clearOldData() );
$pp = new pay_place(1);
$log->TRACE( $pp->clearOldData() );


if ( date('H') == 6 ) {
    $stc = new static_compress();
    $log->TRACE( $stc->cleaner() );
}

$rating = new rating();
if(date('H') == 1) {
    //$rating = new rating();
    //$log->TRACE( $rating->calcDaily() );
    $log->TRACE( $rating->calcMonthly() );
}
$log->TRACE( $rating->calcDaily() );

// ���������� � /minutly.php
/*if(date('H') >= 0 && date('H') <= 5) { 
    // ���������� ���
    $log->TRACE( payed::freezeUpdateProUsers() );
}*/

if(date('H') == 0) {
    
    //�������� ������ �� ��������� ������ ����� ��
    $log->TRACE( project_exrates::updateCBRates() );
    
    // ����������� � ������� �� �����
    $log->TRACE( $mail->sendReminderUsersUnBan(1) ); // �� 1 ���� ��
    
    // ����������� � ������������� ����������� ������� ����� ��� ��� ����� �����������
    $log->TRACE( $mail->activateAccountNotice() ); //
}

// ���������� ����������� � ����� ������� � �����������.
$log->TRACE( $mail->CommuneNewTopic() );

// �������� �� ���� ��������� � /siteadmin/contacts
$log->TRACE( $mail->SendMailToContacts() );


/*
 * �������� ����� �������� �����������
 * @depricated: ���������� � ��������� ���� /hourly_newsletter_frl.php
 *
if((int)date('H') == 1) {
    $log->TRACE( $mail->NewProj2($users) );
}
*/



//------------------------------------------------------------------------------



/*
// �������� -------------------------------------
if ( date('d-H') == '26-02' ) {
    // �������� PRO �������������, ������� ������������������ ����� 30 ���� �����
    $log->TRACE( $spam->proEmpRegLess30() );
}

if ( date('d-H') == '26-03' ) {
    // �������� �� PRO �������������, ������� ������������������ ����� 30 ���� �����
    $log->TRACE( $spam->noProEmpRegLess30() );
}

if ( date('d-H') == '27-03' ) {
    // �������� �����������, ������� ������������������ �� ����� ����� 30 ���� ����� � �� ������ ������� ���
    $log->TRACE( $spam->frlNotBuyPro() );
}
    
if ( date('d-H') == '27-04' ) {
    // �������� �����������, ������� ������ �������� ��� � �� ������ ������� ��� � ������� ������
    $log->TRACE( $spam->frlBuyTestPro() );
}
    
if ( date('d-H') == '27-05' ) {
    // �������� �����������, ������� ������ �������� ��� � ����� ���� ������ ������� ������ �������
    $log->TRACE( $spam->frlBuyProOnce() );
}

if ( date('d-H') == '26-04' ) {
    // �������� �������������  � ������� �� ����� ���� 35+ �������� FM.
    $log->TRACE( $spam->empBonusFm() );
}

if ( $H == 5 ) {
    // �������� �����������, � ������� ����� 2 ������ ������������� ��� �� 6 ��� 12 �������
    $log->TRACE( $spam->frlEndingPro() );
}
    
if ( $H == 6 ) {
    // #0015818: �������� ������������� �� ��������� ��� �������
    $log->TRACE( $mail->sendEmpContestWithoutBudget() );
}

if ( date('d-H') == '15-02' ) {
    // �������� ������������� �������� �� 30 ����, �� �� ������������� ��������
    $log->TRACE( $spam->empProNotPubPrj() );
}

if ( date('d-H') == '15-03' ) {
    // �������� ������������� �������� �� 30 ����, �� �� ������������� ��������
    $log->TRACE( $spam->empNoProNotPubPrj() );
}
    
if ( date('d-H') == '15-04' ) {
    // #0015221: �������� PRO ������������� �������������� ������� ������ ��� ������� � ������� 30 ����
    $log->TRACE( $spam->empProPubPrj30Days() );
}

if ( date('d-H') == '15-05' ) {
    // #0015221: �������� �� PRO ������������� �������������� ������� ������ ��� ������� � ������� 30 ����
    $log->TRACE( $spam->empNoProPubPrj30Days() );
}

if ( date('d-H') == '07-04' ) {
    // �������� ������������� �������� �������� � ������� 30 ����
    $log->TRACE( $spam->empProBuyMass30Days() );
}

if ( date('d-H') == '07-05' ) {
    // �������� ������������� �������� �������� � ������� 30 ����
    $log->TRACE( $spam->empNoProBuyMass30Days() );
}

if (date('d-H') == '28-03') {
    // ���� ������������� � ������������ ��������
    $pmail = new pmail;
    $log->TRACE( $pmail->withoutProfileFrelancers() );
    $log->TRACE( $pmail->withoutProfileEmployers() );
}

if (date('d-H') == '28-04') {
    // ���� ���������� �������������
    $pmail = new pmail;
    $log->TRACE( $pmail->noActiveFreelancers() );
    $log->TRACE( $pmail->noActiveEmployers() );
}

//-----------------------------------------------
*/
//���� ��������� �������� - ������ ����
if (date('H') == 6) {
    $parser = search_parser::factory(1);
    $log->TRACE( $parser->cleanByLimit() );
    $log->TRACE( $parser->parseRaw() );
}

//���� ��������� �������� - ��������� ���� (��� ������� ����� ������������ � ��������)
if (date('H') == 7) {
    $parser = search_parser::factory(1);
    $log->TRACE( $parser->filterRaw() );
}

//���� ��������� �������� - ��������� ���� (������� �� ������)
if (date('H') == 8) {
    $parser = search_parser::factory(1);
    $log->TRACE( $parser->filterRaw('users') );
}

//���� ��������� �������� - ��������� ���� (������� �� ��������)
if (date('H') == 9) {
    $parser = search_parser::factory(1);
    $log->TRACE( $parser->filterRaw('projects') );
    $log->TRACE( $parser->cleanup() );
}

//������� "������" ������������ ��� ������� � ������� ����������� � �� ���������� ����������� (������� commune_attach, file_commune � articles_comments_files, file
if (date('H') == 23) {
    //$log->TRACE( commune::removeWysiwygTrash());
    $log->TRACE( articles::removeWysiwygTrash());
}

// ������ ���� ������� ����� ��������� �������� ITO �� ������� �����
/*
if(date('j') == 1 && date('H') == 1) {
    $prevMonth = time() - 3600 * 24 * 2; // �������� ��� ��� �� ������ ������
    $log->TRACE( sbr_meta::generateDocITO(array(0 => date('Y-m-01', $prevMonth), 1 => date('Y-m-t', $prevMonth)), false, 'xlsx'));
}
*/

//������� ����� ���� �� ����
/*
if(date('H') == 5) {
    // $log_pskb = new log_pskb();
    // $log->TRACE( $log_pskb->clearCloneData() );
    // $log->TRACE( $log_pskb->packOldData(true) );
}
*/


//////////////////// !!! ��������� ��� ���� ������� !!! ///////////////////////

$mt = new Maintenance();
if ( in_array($H, array(2, 9, 21)) ) {
    $log->TRACE( $mt->analyze('master', Maintenance::MODE_VACUUM) );
} else if ( in_array($H, array(3, 6, 10, 13, 16, 19, 22)) ) {
    $log->TRACE( $mt->analyze('master', Maintenance::MODE_ANALYZE) );
}


$log->writeln('------------ END hourly    (total time: ' . $log->getTotalTime() . ') ---------------');