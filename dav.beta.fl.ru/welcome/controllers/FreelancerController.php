<?php

class FreelancerController extends CController 
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
        $this->redirect('/welcome/freelancer/1/');
    }
    
    
    public function action1()
    {
        $this->render('step1'); 
    }
    
    
    public function action2()
    {
        $_SESSION['from_welcome_wizard'] = true;
        $this->render('step2');
    }
    
    
    /*
    protected function _check_wizard($current_step)
    {
    }
     */
}