<?php

/**
 * �-3 (��� ������������� ����������� ������ ������������ � ���������� ��������)
 */

/**
 * ���� ������
 */
$smail->subject = "������������� ���������� �������� �� ����� FL.ru";

$activate_url = sprintf("%s/guest/activate/%s/", $GLOBALS['host'], $code);
$pro_url = $GLOBALS['host'] . '/payed-emp/';

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
$vacancy_price = new_projects::getProjectInOfficePrice(false);
$vacancy_price_pro = new_projects::getProjectInOfficePrice(true);
?>
�� �������� ��� ������, �.�. ��� e-mail ����� ��� ������ �� ����� FL.ru ��� ����������� ������� � ���������� ����� ��������.

����� ��������� ����������� � ������������ �������� �� <?=$vacancy_price?> ������, ����������, ��������� �� ������ <a href="<?=$activate_url?>"><?=$activate_url?></a> ��� ���������� �� � �������� ������ ��������.

���� �� ���������� ���������� ������ ����� ��������, ����������� ����������, <a href="<?=$pro_url?>">����� ������� PRO</a> � � ��� �� ������ ��������� �������� �� <?=$vacancy_price_pro?> ������.

���� �� �� ����������� �������� �� ����� FL.ru � �� ��������� ���� e-mail � ������ �������������� ������. ��������, ���� �� ����� ������������� ������ �������.