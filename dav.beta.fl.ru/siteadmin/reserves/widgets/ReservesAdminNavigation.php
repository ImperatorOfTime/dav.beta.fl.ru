<?php

/**
 * Class ReservesAdminNavigation
 *
 */
class ReservesAdminNavigation extends CWidget 
{
    public $menu_items = array();
    public $current_action;


    public function init() 
    {
        $default_menu_items = array(
            'index' => array(
                'title' => '��� ������',
                'url' => '?action=index'
            ),
            'frod' => array(
                'title' => '�������������� ������',
                'url' => '?action=frod'
            ),
            'reestr' => array(
                'title' => '�������',
                'url' => '?action=reestr'
            ),
            'factura' => array(
                'title' => '������ ����-������',
                'url' => '?action=factura'
            ),
            'archive' => array(
                'title' => '����� ����������',
                'url' => '?action=archive'
            )
        );
        
        $this->menu_items = array_merge(
                $default_menu_items, 
                $this->menu_items);
    }
    

    public function run() 
    {
        //�������� ������
        $this->render('reserves-admin-navigation', array(
            'menu_items' => $this->menu_items,
            'current_action' => $this->current_action
        ));
    }
}