<?php
require_once("classes/config.php");
require_once("classes/payed.php");
require_once("classes/pay_place.php");
require_once("classes/commune.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/wallet/walletAlpha.php');
require_once("classes/log.php");


//#0027582 ������ ������ ������������ ������� �� ���������� � ��������
pay_place::cronRequest();


// ������ ��� ���� ��������� ������ ��������
if(date('i') == 30) {
    require_once("classes/mailer.php");
    $mailer = new mailer();
    $mailer->updateStatusSending();
}

// ������ ���������� �� ������� ��� �������� � ��������� ���� #0021788
if(!in_array((int) date('Hi'), array(2358, 2359))) { 
    payed::UpdateProUsers();
}

//@todo: ��������� ��� ����? 
//���� ���� �������� 10 ��� � ������� ���������� 
//�� �������� ��� ��� ������������� ���� ��� ��� ����� � ���������!
$pp = new pay_place();
$pp->getDoneShow(0);

$user_content = new user_content();
$user_content->releaseDelayedStreams();
$user_content->getQueueCounts();
$user_content->getStreamsQueueCounts();

if (date('i') % 5 == 0) {
    walletAlpha::checkProgressOrders();
}

// ������ 20 ����� ������������� �������� ��������� ���������
if (date('i') % 20 == 0) {
    commune::recalcThemesCountCommunes(null, commune::COMMUNE_BLOGS_ID);
}

if (date('i') % 15 == 0) {
    // �������� �������� �������� paymaster ��� ��������
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pmpay.php");
    $pm = new pmpay();
    if(DEBUG) {
        $pm->setDebugUrl($GLOBALS['host'].'/norisk2/admin/pm-server-test.php');
    } 
    $pm->checkRefund();
}
    
if(SERVER === 'release') {
    
    /*
     * @todo: https://beta.free-lance.ru/mantis/view.php?id=29134#c87337
     * 
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/qiwipay.php");
    $qiwipay = new qiwipay();
    $qiwipay->checkBillsStatus($error);
    */
    
    if (date('i') % 10 == 0) {
        // �������� �������� �������� paymaster
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pmpay.php");
        $pm = new pmpay();
        $pm->checkInvoiced();
    }
}

// ����������� � 0 � 1 ������ ������� ���� � ������ ����� �� 5 ����
if(date('i') == 0 && date('H') >= 0 && date('H') <= 5) { 
    $log = new log('minutly/'.SERVER.'-%d%m%Y[%H].log', 'w');
    // ���������� ���
    $log->TRACE( payed::freezeUpdateProUsers() );
}

professions::autoProlongSpecs();


