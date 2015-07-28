<?
if (!defined("IN_STDF")){
    header("HTTP/1.1 403 Forbidden");
    header("location: /403.html");
	die();
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");

/** ������ �������� �������
 * ���� file - �����������
 * �������������: exclude - ������ � ����� ����������� ���������
 */ 
$teasersEmp = array (
    array('file' => 'tpl.teaser-masssending.php',   'exclude' => array('no-public', 'masssending')), // �������� ��������
    array('file' => 'tpl.teaser-pro-emp.php',       'exclude' => array('no-public', 'pro')), // ��� �������
    array('file' => 'tpl.teaser-public.php',        'exclude' => array('project')), // ���������� ��������
    array('file' => 'tpl.teaser-sbr-emp.php',       'exclude' => array('no-public')), // ���
    array('file' => 'tpl.teaser-top.php',           'exclude' => array('public', 'top')), // ��������� �� �����
    //array('file' => 'tpl.teaser-up.php',            'exclude' => array('public', 'project')), // ������� ������
    //array('file' => 'tpl.teaser-up-conk.php',       'exclude' => array('public', 'contest')), // ������� �������
    //array('file' => 'tpl.teaser-up-top.php',        'exclude' => array('public', 'project')), // ������� ������������ ������
);
$teasersFrl = array (
    array('file' => 'tpl.teaser-sbr-frl.php',       'exclude' => array()), // ���
    //array('file' => 'tpl.teaser-main-carusel.php',  'exclude' => array('car-main')), // ����� � �������� �� �������
    //array('file' =>'tpl.teaser-catalog-carusel.php','exclude' => array('car-cat')), // ����� � �������� ��������
    array('file' => 'tpl.teaser-pro-frl.php',       'exclude' => array('pro')), // ��� �������
    array('file' => 'tpl.teaser-test-pro-frl.php',  'exclude' => array('pro', 'test-pro')), // �������� ���
    //array('file' => 'tpl.teaser-offers.php',        'exclude' => array('offers')), // ������� ������
);
$filteredTeasers = array();


if (!$teasersExclude) {
    $teasersExclude = array();
}
// ������ ������ ��� ����������� � �������������
$uid = get_uid(0);
if ($uid) {
    if (is_emp()) {
        $teasers = $teasersEmp;
    } else {
        $teasers = $teasersFrl;
    }
} else {
    $teasers = $teasersEmp;
}
// ��� ��� ������������� �� ���������� ������� ��� ��������
if (is_pro()) {
    if ($_SESSION['pro_test'] === 'f') { // ����� ��� ���������� ���� ������ �������� ���
        $teasersExclude[] = 'pro';
    }
    $teasersExclude[] = 'test-pro';
    $teasersExclude[] = 'offers';
} elseif (!payed::IsUserWasPro($uid)) {
    $teasersExclude[] = 'test-pro';
}



// ��������� ������ �� ��������� ���������� �������
foreach ($teasers as $key => $teaser) {
    $ok = true;
    
    foreach ($teaser['exclude'] as $filter) {
        if (isset($teasersExclude) && in_array($filter, $teasersExclude)) {
            $ok = false;
            break;
        }
    }
    
    if ($ok) {
        $filteredTeasers[] = $teaser;
    }
}

// ���������� �������
$teasersCount = count($filteredTeasers);
if (!$teasersCount) {
    return;
}

// ��������� �����
$teaserKey = mt_rand(0, $teasersCount - 1);
$teaser = $filteredTeasers[$teaserKey]['file'];

// ��� "������� ������"
if ($teaser === 'tpl.teaser-up.php' || $teaser === 'tpl.teaser-up-conk.php' || $teaser === 'tpl.teaser-up-top.php') {
    $account = new account();
    $account->GetInfo(get_uid());
    $transaction_id = $account -> start_transaction(get_uid());
    ?>
    <form action="/users/<?= $_SESSION['login'] ?>/setup/" id="upprj" name="frm" method="POST">
        <input type="hidden" name="action" value="prj_up">
        <input type="hidden" name="transaction_id" value="<?=$transaction_id?>" />
        <input type="hidden" name="prjid" value="<?= $prj_id ?>">
        <input type="hidden" value="<?= $_SESSION['rand'] ?>" name="r">
        <input type="hidden" value="<?= $_SESSION['rand'] ?>" name="u_token_key">
    </form>
    <?
}

?>
<div class="b-dot b-dot_margtop_100">
    <div class="b-dot__bot">
        <div class="b-dot__top">
        <div class="b-dot__left">
            <div class="b-dot__right">
            <div class="b-dot__body b-dot__body_padtb_10">
                <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
                    <tr class="b-layout__tr">
                        <? include($abs_path . "/teasers/$teaser"); ?>
                    </tr>
                </table>
            </div>
            </div>
        </div>
        </div>
    </div>
    <span class="b-dot__tl"></span> <span class="b-dot__tr"></span> <span class="b-dot__bl"></span> <span class="b-dot__br"></span> 
</div>