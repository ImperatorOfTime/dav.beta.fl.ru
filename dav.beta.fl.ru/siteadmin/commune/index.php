<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
session_start();
get_uid();
  
if(!(hasPermissions('adm') && hasPermissions('communes'))) {
  header ("Location: /404.php");
  exit;
}
  
$error ='';
$action    = __paramInit('string', 'action', 'action');
if ($action && $_POST["u_token_key"] != $_SESSION["rand"]) {
    header ("Location: /404.php");
    exit;
}
switch($action)
{
  case 'Insert' :
    
    $alert=NULL;
    $name    = __paramInit('string', NULL, 'name','');
    $descr   = __paramInit('string', NULL, 'descr','');

    if(!trim($name))
      $alert['name'] = "������ ��� ��������.";
    
    if(!$alert) {
      if(!commune::AddGroup($name, $descr))
        $error = "������. �� ������� �������� ������.";
      else {
        header ("Location: /siteadmin/commune/?result=success");
        exit;
      }
    }

    break;


  case 'Update' :
    
    $alert=NULL;
    $id      = __paramInit('array', NULL, 'id');
    $name    = __paramInit('array', NULL, 'name');
    $descr   = __paramInit('array', NULL, 'descr');

    $cnt = count($id);
    for($i=0; $i<$cnt; $i++) {
      $n = change_q_new($name[$i], TRUE);
      if(!trim($n))
        $alert['name[]'][intval($id[$i])] = "������ ��� ��������.";
      else {
        $d = change_q_new($descr[$i], TRUE);
        if(!commune::UpdateGroup(intval($id[$i]), $n, $d, $i+1))
          $error .= (!$error ? '' : '<br/>')."������. �� ������� �������� ������ '{$n}'.";
      }
    }

    if(!$error && !$alert) {
      header ("Location: /siteadmin/commune/?result=success");
      exit;
    }

    break;


  case 'Delete' :
    
    $alert=NULL;
    $id = __paramInit('int', 'id', NULL);
    if(!commune::DeleteGroup($id))
      $error = "������. ���������� ������� ������. �������� �������� ������ (���� ������ ��� �������� ����������).";
    else {
      header ("Location: /siteadmin/commune/?result=success");
      exit;
    }

    break;
}

$content = "../content.php";

$inner_page = "inner_index.php";
$css_file = array('moderation.css','nav.css' );
$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath."template.php");

?>
