<?php

class LoginController extends CController 
{
    /**
     * ������������� �����������
     */
    public function init() 
    {
        parent::init();
        
        $uid = get_uid(false);
        
        if ($uid) {
            //���� ��� ����������� �� �� �������
            $this->redirect('/');
        }
        
        $this->layout = '//layouts/content';
    }


    /**
     * ��������� ������� �� ������-���� ������
     * 
     * @param string $action
     * @return bool
     */
    /*
    public function beforeAction($action) 
    {
    }
    */
    

    public function actionIndex()
    {
        require_once(__DIR__ . '/../models/LoginForm.php');
        
        $form = new LoginForm();
        
        if (isset($_POST) && sizeof($_POST) > 0 && 
            $form->isValid($_POST)) {
            
            $this->redirect($form->getRedirect());
        }
        
        $this->render('index', array(
            'form' => $form->render()
        )); 
    }
}