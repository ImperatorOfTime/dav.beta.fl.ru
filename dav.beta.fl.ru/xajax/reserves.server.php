<?php

//$rpath = "../";

require_once($_SERVER['DOCUMENT_ROOT'] . '/xajax/reserves.common.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesPayoutPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderFeedbackModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderStatus.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesPayout.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_helper.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DocGen/DocGenReserves.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reserves/ReservesArbitrage.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reserves/ReservesArbitragePopup.php");//��� ���� ���?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reserves/ReservesSmail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_order_history.php');


session_start();


//------------------------------------------------------------------------------

/**
 * ������� �� �������
 * 
 * @todo: ���� ������ ������������� ������ ��� ������
 * �� �������� ��������� ��� ������ ������� � ����� ������ ��������
 * 
 * @param type $type - ��� ������� �������
 * @param type $params - ������
 * @return \xajaxResponse
 */
function reservesPayoutProcess($type, $params)
{
    $objResponse = &new xajaxResponse();
    
    $orderModel = TServiceOrderModel::model();
    
    $uid = get_uid(false);
    $order_id = intval(@$params['oid']);
    $hash = @$params['hash'];
    $test_hash = md5(TServiceOrderModel::SOLT . $order_id);
    $error = false;
    
    try
    {
        if ($uid <= 0 || 
            $hash !== $test_hash || 
            !in_array($type, ReservesPayoutPopup::$payment_list))  {
            
                throw new Exception();
        }
            
        $orderData = $orderModel->getCard($order_id, $uid);
    
        if (!$orderData || 
            !$orderModel->isStatusEmpClose() || 
            !$orderModel->isReserve())  {
            
                throw new Exception();
        }
           
        $reserveInstance = $orderModel->getReserve();
        if (!$reserveInstance->isAllowPayout($uid) || 
            !$reserveInstance->isFrlAllowFinance()) { 
            
                throw new Exception();   
        }
            
        $history = new tservices_order_history($order_id);
        $reservesPayout = new ReservesPayout();

        $status = $reservesPayout->requestPayout($reserveInstance, $type);
        
        $is_done = $reserveInstance->changePayStatus($status);
        
        if ($is_done) {
            //@todo: �������� ������ ���������� ������ �� ��������� ��� ��������� ����
            //��������� �� ������� �� �������
            $orderData['reserve_data'] = $reserveInstance->getReserveData();
            //@todo: ���������� ������ - ����� ����������� ���������
            $orderData['reserve'] = $reserveInstance;

            try {
                $doc = new DocGenReserves($orderData);
                $doc->generateInformLetterFRL();
            } catch (Exception $e) {
                require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/log.php');
                $log = new log('reserves_docs/' . SERVER . '-%d%m%Y.log', 'a', "%d.%m.%Y %H:%M:%S: ");
                $log->writeln(sprintf("Order Id = %s: %s", $orderData['id'], iconv('CP1251','UTF-8',$e->getMessage())));

                //$sHtml = tservices_helper::getMessage($e->getMessage(),'error');
                //$objResponse->call('TServices_Order.showBeforeStatus', $order_id, $sHtml);
            }
        }
    
        $feedback = @$params['feedback'];
        $is_feedback = !empty($feedback);
        $fbtype = @$params['fbtype'];
    
    
        //��������� ����� ���� �� ����
        if($is_feedback && !$orderModel->isFrlFeedback())
        {
            $is_emp = false;

            $orderFeedbackModel = new TServiceOrderFeedbackModel();
            $is_valid = $orderFeedbackModel->attributes(array(
                'feedback' => $feedback,
                'rating' => $fbtype,
                'is_emp' => $is_emp,
                'user_id' => $uid
            ));

            //��� ������������ ������ ��� ���������
            if(!$is_valid || !$orderFeedbackModel->addFeedback($order_id)) return $objResponse;

            $attributes = $orderFeedbackModel->attributes();   
            $orderData['frl_feedback'] = $attributes['feedback'];
            $orderData['frl_rating'] = $attributes['rating'];

            //��������� �������� � �������
            $history->saveFeedback($is_emp, $fbtype);

            //������ ��� ���-�� ����� ��������� ����� ����� ��������� ����������� 
            $orderModel->clearCountEvent($orderData["emp_id"]);
        }  
    
        
        $tserviceOrderStatusWidget = new TServiceOrderStatus();
        $tserviceOrderStatusWidget->setIsEmp(false); 
        $tserviceOrderStatusWidget->setOrder($orderData);
        $tserviceOrderStatusWidget->init();

        ob_start();
        $tserviceOrderStatusWidget->run();
        $sHtml = ob_get_contents();
        ob_end_clean();
        
        $objResponse->assign('tservices_order_status_'.$order_id,'innerHTML',$sHtml);
        $objResponse->script('window.order_feedback_factory = new OrderFeedbackFactory();');
    }
    catch(Exception $e)
    {
        $error = $e->getMessage();
    }
        
    if($error !== false)
    {
        //���� ���� ������ �� ������ �� ����������� � 
        //� ������ ������ ����� �� �������� ��� ������ ������� ���
        $idx = ReservesPayoutPopup::getPopupId($order_id);
        $objResponse->script("
            var rp = window.reserves_payout_factory.getReservesPayout('{$idx}');
            if(rp) ".((!empty($error))?"rp.show_error('{$error}');":"rp.close_popup();
        "));
    }
    else
    {
        //����� ������ ��������� � ����� �������� JS �������
        $objResponse->script("
            Bar_Ext.popuper();
            window.reserves_payout_factory = new ReservesPayoutFactory();
        ");
    }
     
    return $objResponse;
}


//------------------------------------------------------------------------------


/**
 * �������� ���������
 * 
 * @todo: ����� ����� ��������� ���������� ������
 * �� �� ������ �������� ������ � �������
 * 
 * @param array $form
 * @return \xajaxResponse
 */
function reservesArbitrageNew($form) 
{
    $objResponse = new xajaxResponse();
    
    $uid = get_uid(false);
    $order_id = intval($form['oid']);
    
    // ���������� ������ ��� ����� �� ������
    if ($uid <= 0 || !$order_id) { 
        return $objResponse; 
    }
    
    $is_emp = is_emp();
    
    $orderModel = TServiceOrderModel::model();
    $order = $orderModel->getCard($order_id, $uid);
    
    //����� �� ������
    if (!$order) {
        return $objResponse;
    }
    
    //��������� �������� �� ��������
    if ($orderModel->isAllowArbitrageNew()) {
        
        $data = array(
            'reserve_id' => $order['reserve_data']['id'],
            'frl_id' => $order['frl_id'],
            'emp_id' => $order['emp_id'],
            'is_emp' => $is_emp,
            'message' => $form['message']
        );

        $reservesArbitrage = new ReservesArbitrage();
        $arbitrage = $reservesArbitrage->createArbitrage($data);
        if ($arbitrage) {
            //��������� ������� ���������
            $orderModel->getReserve()->setReserveDataByKey('arbitrage_id', $arbitrage['id']);
            $orderModel->getReserve()->setReserveDataByKey('arbitrage_is_emp', $arbitrage['is_emp']);
            $orderModel->getReserve()->setReserveDataByKey('arbitrage_message', $arbitrage['message']);
            $order['reserve_data'] = $orderModel->getReserve()->getReserveData();

            //�����������
            $reservesSmail = new ReservesSmail();
            $reservesSmail->onNewArbitrage($order, $is_emp);

            //��������� ��� �������
            $tservicesOrderHistory = new tservices_order_history($order_id);
            $tservicesOrderHistory->reserveArbitrageNew($is_emp);
        }
    }
    
    
    //����� ������ ���������� ��� ������������
    //@todo: ����� ������� � ������������� ������ ������� ��� ������
    $tserviceOrderStatusWidget = new TServiceOrderStatus();
    $tserviceOrderStatusWidget->setIsEmp($is_emp); 
    $tserviceOrderStatusWidget->setOrder($order);
    $tserviceOrderStatusWidget->init();  
    
    ob_start();
    $tserviceOrderStatusWidget->run();
    $sHtml = ob_get_contents();
    ob_end_clean();

    $objResponse->assign('tservices_order_status_'.$order_id,'innerHTML',$sHtml);
    $objResponse->script('window.order_arbitrage['.$order_id.'].close_popup();');

    return $objResponse;
}


//------------------------------------------------------------------------------


/**
 * ��������� ������� ��������
 * @param array $form
 * @return \xajaxResponse
 */
function reservesArbitrageApply($form) 
{
    $objResponse = new xajaxResponse();
    
    $order_id = @$form['order_id'];
    $price_pay = (int)@$form['price']; //����� ��� ������� �����������
    $allow_fb_frl = (bool)@$form['allow_fb_frl'];
    $allow_fb_emp = (bool)@$form['allow_fb_emp'];
    
    $orderModel = TServiceOrderModel::model();
    $orderModel->attributes(array('is_adm' => hasPermissions('tservices')));
    $order = $orderModel->getCard((int)$order_id, get_uid(false));

    if(!$order) return $objResponse;
    
    $reservesArbitrage = new ReservesArbitrage();
    $reservesArbitrage->db()->start();
    
    try
    {
        if ($price_pay > $order['reserve_data']['price']) {
            $price_pay = $order['reserve_data']['price'];
        }
        $price_back = $order['reserve_data']['price'] - $price_pay;

        //���������� �����, ������� ���� ��������� ��������, ��������� �������� � �����
        $ok = $reservesArbitrage->closeArbitrage($order['reserve_data'], array(
            'price_pay' => $price_pay, 
            'price_back' => $price_back,
            'allow_fb_frl' => $allow_fb_frl,
            'allow_fb_emp' => $allow_fb_emp
        ));

        if ($ok) {
            $is_emp = true; //��������� ����� �� ���� ���������
            $orderModel->changeStatus($order_id, 'close', $is_emp);

            //���������� �����������
            $reservesSmail = new ReservesSmail();
            $reservesSmail->onApplyArbitrage($order, $price_pay);

            $order = $orderModel->getOrderData();
            //����� ������ ���������� ��� ������������
            $order['reserve_data']['arbitrage_price'] = $price_pay;
            $order['reserve_data']['arbitrage_date_close'] = date('Y-m-d H:i:s');
            //��� ��� �� � �������� ���������� ������ �� ��������� ��� ������
            $order['reserve']->setReserveData($order['reserve_data']);
            //$order['status'] = TServiceOrderModel::STATUS_EMPCLOSE;
            
            //���������� ���������
            try {
                
                $doc = new DocGenReserves($order);

                if ($price_pay > 0) {
                    $doc->generateActCompletedFrl();
                } 
                
                $doc->generateArbitrageReport();
            
            } catch(Exception $e) {
                require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/log.php');
                $log = new log('reserves_docs/' . SERVER . '-%d%m%Y.log', 'a', "%d.%m.%Y %H:%M:%S: ");
                $log->writeln(sprintf("Order Id = %s: %s", $order['id'], iconv('CP1251','UTF-8',$e->getMessage())));
            }
            
            
            $tservicesOrderHistory = new tservices_order_history($order_id);
            $tservicesOrderHistory->reserveArbitrageDecide($price_pay, $price_back);
            
            $tserviceOrderStatusWidget = new TServiceOrderStatus();
            $tserviceOrderStatusWidget->setIsOwner(false); 
            $tserviceOrderStatusWidget->setOrder($order);
            $tserviceOrderStatusWidget->init();

            ob_start();
            $tserviceOrderStatusWidget->run();
            $sHtml = ob_get_contents();
            ob_end_clean();

            $objResponse->assign('tservices_order_status_'.$order_id,'innerHTML',$sHtml);
        }
    }
    catch(Exception $e)
    {
        $reservesArbitrage->db()->rollback();
        $sHtml = tservices_helper::getMessage($e->getMessage(),'error');
        $objResponse->call('TServices_Order.showBeforeStatus', $order_id, $sHtml);
        return $objResponse;
    }
    
    $reservesArbitrage->db()->commit();
    
    $objResponse->call('TServices_Order.hideBeforeStatus', $order_id);
    return $objResponse;
}


//------------------------------------------------------------------------------


/**
 * ���������� ���������
 * @param int $order_id
 * @return \xajaxResponse
 */
function reservesArbitrageCancel($order_id) {
    
    $objResponse = new xajaxResponse();

    //�������� ����� � ��������� ���
    if (!$order_id) {
        return $objResponse;
    }
        
    $orderModel = TServiceOrderModel::model();
    $orderModel->attributes(array('is_adm' => hasPermissions('tservices')));
    $order = $orderModel->getCard((int)$order_id, get_uid(false));
    if (!$order || !isset($order['reserve_data']['arbitrage_id'])) {
        return $objResponse;
    }
    
    //������� ��������
    $reservesArbitrage = new ReservesArbitrage();
    $ok = $reservesArbitrage->removeArbitrage($order['reserve_data']['id']);
    if ($ok) {
        //���������� �����������
        $reservesSmail = new ReservesSmail();
        $reservesSmail->onRemoveArbitrage($order);
        
        $history = new tservices_order_history($order_id);
        $history->reserveArbitrageCancel();
        
        //����� ������ ���������� ��� ������������
        unset($order['reserve_data']['arbitrage_id']);
        $tserviceOrderStatusWidget = new TServiceOrderStatus();
        $tserviceOrderStatusWidget->setIsOwner(false); 
        $tserviceOrderStatusWidget->setOrder($order);
        $tserviceOrderStatusWidget->init();

        ob_start();
        $tserviceOrderStatusWidget->run();
        $sHtml = ob_get_contents();
        ob_end_clean();

        $objResponse->assign('tservices_order_status_'.$order_id,'innerHTML',$sHtml);
    }
    
    return $objResponse;
    
}

$xajax->processRequest();
