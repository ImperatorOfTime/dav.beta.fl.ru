<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceMsgModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_helper.php');

/**
 * Class TServiceOrderMessagesForm
 *
 * ������ - ����� ��������� � ��������� � ������
 */
class TServiceOrderMessagesForm extends CWidget 
{
    private $is_scroll;

    protected $uid; 
    protected $order_id;

    public function init() 
    {
        parent::init();

        global $js_file;
        $js_file['tservices_order_messages'] = 'tservices/tservices_order_messages.js';
        $js_file['tservices_order_messages_attached'] = 'attachedfiles.js';

        $this->is_scroll = __paramInit('bool', null, 'form-block', false);
    }

    public function run() 
    {
        $attachedfiles = new attachedfiles();
        $attachedfiles_session = $attachedfiles->getSession();
        //��� ������������ ���������� ���������� �����
        //������ ������������ ��� ���������� ������
        $param_hash = tservices_helper::getOrderUrlHash(
                array((int)$this->order_id, $attachedfiles_session),
                $this->uid);
        
        //�������� ������
        $this->render('t-service-order-messages-form', array(
            'order_id' => $this->order_id,
            'param_hash' => $param_hash,
            'is_scroll' => $this->is_scroll,
            'attachedfiles_session' => $attachedfiles_session
        ));
	}
}