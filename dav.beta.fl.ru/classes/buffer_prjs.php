<?
/**
 * ���� ��� ����������� ������ �� �������� �������� (/projects)
 * 
 */

/**
 * ���������� ���� ��� ������ � ������� �������
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
// �������������� ����� 
$memBuff = new memBuff();
$memBuff->flushGroup("prjsFPPages".$kind); // ���������� � �����

?>