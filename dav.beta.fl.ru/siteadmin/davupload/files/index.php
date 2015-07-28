<? 
if (!defined('IS_SITE_ADMIN')) {
    header ("Location: /404.php"); 
    exit;
}
/**
* @desc ������������� ������������ ��� ����� ($_POST['filename']) � ���������� ��� � ����������� ������������ �����
* @param CFile $cfile
* @return string 
*/
function dav_file_upload_createDestName($cfile) {
    $uploadFileName = $cfile->name;
    $ext = preg_replace("#.*(\.[0-9a-zA-Z]*)$#", "$1", $uploadFileName);
    //ext = .*
    $filename = __paramInit("string", null, "filename");
    $pattern = '#\\'.$ext.'#';
    $filename = preg_replace($pattern, '', $filename);
    if ( strlen($filename) == 0 ) {
        $filename = preg_replace($pattern, '', $uploadFileName);
    }
    $filename = translit($filename) . $ext;
    return $filename;
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/dav_file_upload.php");
switch ($action) {
    case 'upload': {
    /*
1 Search file by phisic name
  if FOUND
      getFidByPhisicName
      if ( FID ) rename
  save
  log     
* */
        $path = __paramInit("string", null, "path");
        $path = trim($path, '/');
        $info = '';
        $old_link = '';
        $rename_name = '';
        $name = '';
        $link = '';
        $error_folder = '';
        $cf = new CFile($_FILES['document'], dav_file_upload::FILE_TABLE);
        if ( $cf->CheckPath($path) ) {
            $destname = dav_file_upload_createDestName($cf);
            //check existing file
            $existingFile = new CFile("$path/$destname");
            if ($existingFile->id > 0) {
                $ext = $existingFile->getext($existingFile->name);
                $tmp = $existingFile->secure_tmpname($path . '/', '.' . $ext );
                $rename_name = substr_replace($tmp,"",0,strlen($path) + 1);
                $s = preg_replace("#\.".$ext."$#", "", $destname);
                $length = strlen( $s . '_' .  $rename_name );
                if ( $length > 64 && strlen($rename_name) < 64 ) {
                    $s = substr($s, 0, 63 - strlen($rename_name) );
                    $rename_name = $s . "_" .  $rename_name;
                }
                $existingFile->Rename("{$path}/{$rename_name}");
            	$info = '���� ��� �������';
            	$old_link = WDCPREFIX . '/' . $path . '/' . $rename_name;
            }
	        $cf->server_root = 1;
	        $cf->max_size = dav_file_upload::MAX_FILE_SIZE;
            $cf->MoveUploadedFile($path . '/', true, $destname);
            $err = is_string( $cf->error[0] ) ? $cf->error : $cf->error[0];
            if ($err == '') {
              $link = WDCPREFIX . '/' . $cf->path . $cf->name;
              $name = WDCPREFIX . '/' . $cf->path . $cf->name;
              $info = '���� ��� ��������';
              //��������� ������ � ������� replace_file_log
              dav_file_upload::addRecord($cf->id, $cf->name, $rename_name);
            }
        } else {
            $error_folder = '��� ������ ��������';
        }
        include dirname(__FILE__)."/uploadform.php";
        return;
    }
}
if ($view == 'form') {
    include dirname(__FILE__)."/uploadform.php";
} else {
   $css_file    = array( 'moderation.css', 'nav.css' );
	include $rpath.'template.php';
}
