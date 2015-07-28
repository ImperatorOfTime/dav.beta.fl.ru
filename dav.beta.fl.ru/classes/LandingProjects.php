<?php

/**
 * ��������� ������� ��������� ��� �������� � ��������
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yii/CModel.php');

class LandingProjects extends CModel
{
    protected $TABLE = 'landing_projects';
    
    /**
     * �������� �������� ������� ��� ���������� � ��������
     * 
     * @param type $name
     * @return type
     */
    public function addLandingProject($name)
    {
        $name = __paramValue('html', $name, 60, true);
        return $this->db()->insert($this->TABLE, array('name' => $name), 'id');
    }
    
    
    /**
     * ����������� � ��������������� �������
     * 
     * @param type $id
     * @param type $project_id
     * @return type
     */
    public function linkWithProject($id, $project_id, $is_noob = true)
    {
        return $this->db()->update($this->TABLE, array(
            'project_id' => $project_id,
            'is_noob' => $is_noob
        ), 'id = ?i', $id);
    }
    
}