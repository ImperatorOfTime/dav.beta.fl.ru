<?php

require_once $_SERVER['DOCUMENT_ROOT']."/classes/stdf.php";
require_once $_SERVER['DOCUMENT_ROOT']."/classes/masssending.php";
session_start();

$masssending = new masssending;

if ( empty($_SESSION['masssending_total_filesize']) ) $_SESSION['masssending_total_filesize'] = 0;
if ( empty($_SESSION['masssending']['files']) )       $_SESSION['masssending']['files'] = array();

if ( count($_SESSION['masssending']['files']) >= masssending::MAX_FILES ) {
	$error = '������������ ���-�� ������������� ������ - ' . masssending::MAX_FILES;
}
else {
    if ( $_SESSION['masssending_total_filesize'] + $_FILES['attach']['size'] > masssending::MAX_FILE_SIZE ) {
    	$error = '������������ ����� ������������� ������ - ' . (masssending::MAX_FILE_SIZE / (1024*1024)).' M�';
    }
    else {
        if ($uid = get_uid(FALSE)) {
        	$login = get_login($uid);
        	$file = new CFile($_FILES['attach']);
            $file->table = 'file';
        	$file->max_size = masssending::MAX_FILE_SIZE;
        	$filename = $file->MoveUploadedFile("{$login}/contacts");
            $filetype = $file->getext();
        	$error = $file->error;
        } else {
        	$error = '�� �� ������������';
        }
        
        if (!$file->id && !$error) {
        	$error = '������ ��� �������� �����. ����������, ���������� ��� ���.';
        }
        
        $masssending->AddFile($file->id, session_id());
    }
}

if ($error || $error = $masssending->error) {
	echo "
		-- IBox --
		<uploaded>
			<nothing>opera</nothing>
			<status>error</status>
			<message>{$error}</message>
		</uploaded>
		-- IBox --
	";
	exit(1);
}

$_SESSION['masssending']['savetime']     = mktime();
$_SESSION['masssending_total_filesize'] += $_FILES['attach']['size'];
$_SESSION['masssending']['files'][]      = array(
    'id'          => $file->id, 
    'displayname' => stripslashes($_FILES['attach']['name']), 
    'filename'    => WDCPREFIX."/users/{$login}/contacts/{$filename}",
    'size'        => $_FILES['attach']['size'],
    'filetype'        => $filetype
);

$filename = stripslashes($_FILES['attach']['name']);
if(strlen($filename) > 45) {
    $filename = substr($filename, 0, 30) . "..." . substr($filename, strlen($filename)-10, strlen($filename));
}
    
echo "
	-- IBox --
	<uploaded>
		<nothing>opera</nothing>
		<status>success</status>
		<fileid>{$file->id}</fileid>
		<displayname>".$filename."</displayname>
		<filename>".WDCPREFIX."/users/{$login}/contacts/{$filename}</filename>
        <filetype>{$filetype}</filetype>
	</uploaded>
	-- IBox --
";




?>
