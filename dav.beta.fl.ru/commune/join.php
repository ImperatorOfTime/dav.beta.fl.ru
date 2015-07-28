<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/commune.common.php");
$xajax->printJavascript('/xajax/');
global $id, $comm, $user_mod, $uid, $result;

  $fromPage = __paramInit('string', 'fp', 'fp');

  $name = $comm['name'];

  //  if ( $restrict_type & commune::RESTRICT_READ_MASK )
  //  {

  if($user_mod & (commune::MOD_COMM_ACCEPTED | commune::MOD_COMM_ASKED))
    $header = '�� ����� �� ����������';
  else
    $header = '���������� � ����������';

  $comm_link = "<b class=\"vv\">&laquo;<a class=\"frlname11\" href=\"/commune/?id={$id}\">{$name}</a>&raquo;</b>";
?>
<h1 class="b-page__title">�� �� ��������� ������ ����������</h1>
<?php if($user_mod & commune::MOD_COMM_ASKED) { ?>

		<div class="b-layout__txt b-layout__txt_padbot_10">�� �� ��������� ������ ���������� <strong class="b-layout__bold">�<?=$comm['name']?>�</strong>. �������� ����� ���������� ��� ����������.</div>
		<div class="b-layout__txt b-layout__txt_padbot_10">���������� � ���������� �������� ������ ����� ��������� ���������������. ���� ������ ���������� �������������� ����������. <a class="b-layout__link b-layout__link_dot_c10600" href="javascript:void(0)" onclick="xajax_OutCommune(<?=$comm["id"]?>, true); return false;">��������</a></div>

		<div class="b-layout__txt"><a class="b-layout__link" href="/commune/">��������� ����������</a></div>
		

<?php } else { ?>
		<a class="b-button b-button_flat b-button_flat_green b-button_float_right b-button_margtop_-10" onclick="return false" href="#"><span onclick="xajax_JoinCommune(<?=$comm["id"]?>, true);">�������� � ����������</span></a>
		<div class="b-layout__txt b-layout__txt_padbot_10">�� �� ��������� ������ ���������� <strong class="b-layout__bold">�<?=$comm['name']?>�</strong>. �������� ����� ���������� ��� ����������.</div>
		
<?php }?>
