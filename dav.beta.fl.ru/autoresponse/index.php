<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/autoresponse.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupAutoresponse.php');

//require_once(dirname(__FILE__).'/autoresponse.form.php');
        
session_start();

// ������ �������� ������ ��� �������������� �����������
if (is_emp() || !get_uid()) {
    header("Location: /frl_only.php\r\n");
    exit();
}

$stretch_page = true;
$showMainDiv  = true;

$stop_words = new stop_words();

// �� � �������
autoresponse::$db = $GLOBALS['DB'];

/*
$form = new AutoresponseForm();

// �������� ������ ����������
if (isset($_POST) && sizeof($_POST) > 0) {

    if ($form->isValid($_POST)) {
        $data = $form->getValues();
        $data['user_id'] = get_uid();
        $data['is_pro'] = is_pro(); // ���� �� � ������������ ��� ������� �� ������ ������� ����������
        $data['filter_category_id'] = $_POST['filter_category_columns'][0];
        $data['filter_subcategory_id'] = $_POST['filter_category_columns'][1];

        $data['filter_budget'] = $form->getElement('filter_budget')->getValue('budget');
        $data['filter_budget_currency'] = $form->getElement('filter_budget')->getValue('currency_db_id');
        $data['filter_budget_priceby'] = $form->getElement('filter_budget')->getValue('priceby_db_id');

        if ($ar = autoresponse::create($data)) {
            // ��������� ������ � �������� JavaScript ����� ��� ������
            echo "<script>";
            echo "window.parent.autoresponseShowPayModal({$ar->data['id']});";
            echo "</script>";
            exit();
        }
    }
    exit();
}
else {
    $form->setDefaults(array('total' => autoresponse::$config['default_quantity']));
}
*/

// �������� ������ ����������� ������������
$autoresponse_list = autoresponse::findForUser(get_uid());

// ������������� ������ ������
//quickPaymentPopupAutoresponse::getInstance()->init();

// ��������� ������ ��� ������ � ������������ 
//$GLOBALS['js_file']['autoresponse'] = 'autoresponse.js';

$content = "content.php";
$header = "../header.php";
$footer = "../footer.html";

include ("../template3.php");