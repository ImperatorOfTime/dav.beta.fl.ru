<?php
/**
 * ������������� ����������������� ��������. ������. ����������.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

$aStreams = $user_content->getContentsForUser();