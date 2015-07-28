<?php
/**
 * ����� �������� ������
 * ���� - ��� ������,
 * �������� - ��������� ����� + (�������������) �����
 * 
 * /test/ => $map = array("test" => array("class"=>"test"));
 * /test/action/ => $map = array("test" => array("class"=>"test")); -> ������ � ������ test ����� �����  actionAction
 * /test/action2/ => $map = array("test" => array("class"=>"test", "action2"=>array("class"=>"action2"))); -> ������ � ������ action2 ����� �����  indexAction
 * ����� ����������� ������ � ��������
 * @var
 */
$map = array(
    "press" => array("class"=>"press"),
    "about" => array("class"=>"about"),
    "myblog" => array("class"=>"mycorp"),
    "test" => array("class"=>"test"),
    "bill" => array("class"=>"bill"),
    
    
    //В движке жестко проверяются вызовы action через http, выозвращается 404, если не модератор или админ!
    "adminback" => array("class"=>"admin",
        "news"=>array("class"=>"admin_news"),
        "static_pages"=>array("class"=>"admin_static_pages"),
        "tests"=>array("class"=>"admin_tests"),
        "cblog"=>array("class"=>"admin_cblog"),
        "faq"=>array("class"=>"admin_faq"),
        "team"=>array("class"=>"admin_team"),
        "smi"=>array("class"=>"admin_smi"),
        "opinions"=>array("class"=>"admin_opinions"),
        "partners"=>array("class"=>"admin_partners"),
        "flashUpload"=>array("class"=>"admin_flash_upload2"),
    ),
    "flash"=>array("class"=>"admin_flash_upload2"),
    
	"pda2" => array("class"=>"pda_index",
		"login"=>array("class"=>"pda_index", "method" => "login"),
		"logout"=>array("class"=>"pda_index", "method" => "logout"),
		"blogs"=>array("class"=>"pda_blogs"),
		"contacts"=>array("class"=>"pda_contacts"),
		),
);

?>
