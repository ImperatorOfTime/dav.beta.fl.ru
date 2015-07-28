<?php

define('IS_PHP_JS', true);

/**
 * ������� JS-������ � �������� �������� ���� (������ kwords), ��� ��������� ����� � ���������������� (����������
 * ������-���������� � ��������� �������, ��������, ���: /freelancers/filter.php).
 * ������ ���������� � ������� � �� ������� �������.
 * ������������ ����� 
 * <script type="text/javascript" src="/kword_js.php"></script>
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
$kwords = new kwords();
$mem = new memBuff();

if(!($kdata = $mem->get(kwords::MEM_KEY_NAME))) {
    $js    = $kwords->getJSValue($kwords->load());
    $etag  = md5($js);
    $kdata = array('js'=>$js, 'etag'=>$etag);
    $mem->set(kwords::MEM_KEY_NAME, $kdata, kwords::MEM_TIME);
}

//header('Content-Type: text/javascript; charset=windows-1251');
//header("Cache-Control: public, must-revalidate, max-age=0");
//header("Etag: {$kdata['etag']}");
//if($_SERVER['HTTP_IF_NONE_MATCH']==$kdata['etag']) {
//    header("HTTP/1.1 304 Not Modified");
//	exit;
//}
print($kdata['js']);