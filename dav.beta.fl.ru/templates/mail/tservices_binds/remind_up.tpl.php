<?php
/**
 * �-2 � ����������, ����� ����, ��� ����������� ���������� ����
 */

/**
 * ���� ������
 */
$smail->subject = "��������� ���� ������ ����� �� ������ �����";

$link_prolong = $GLOBALS['host'] . $link_prolong;
$link_up = $GLOBALS['host'] . $link_up;

?>
����������, ��� ����� �� ��������� �� ������ ����� ������ &laquo;<?=$title?>&raquo; <?=$kind?>. ������ ������ ���������� ������ �� ���� ���� ��������� ���� ������, ������� ���� ����.

����� ����� ��������� ������ �� ������ �����, ����������� ������� �� ����� (��� ����� ������������) ��� �������� ���� �����������.

<a href="<?=$link_prolong?>">�������� ���� �����������</a> / <a href="<?=$link_up?>">������� ����� �� ������ �����</a>