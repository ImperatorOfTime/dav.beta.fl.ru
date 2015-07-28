<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");

/**
 * Class TServiceFreelancersCategories
 *
 * ������ - ������ ��������� ������������� �����������
 */
class TServiceFreelancersCategories extends CWidget {

    public function run() 
    {
        //�� ������ ���� ��� ���� ���������� 
        //���������� � ������� �������
        global $profs;

        if(!isset($profs))
        {
            $prfs = new professions();
            $profs = $prfs->GetAllProfessions("", 0, 1);
            //@todo: ���������� ������������ ������� ������ �� 60 ��� � ������ ����
        }
        
        $this->render('t-service-freelancers-categories', array(
            'profs' => $profs,
        ));
    }
}