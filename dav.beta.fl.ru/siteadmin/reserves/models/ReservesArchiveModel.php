<?php

require_once(ABS_PATH . '/classes/yii/CModel.php');
require_once(ABS_PATH . '/classes/tservices/tservices_helper.php');
require_once('ReservesArchiveItemIterator.php');

/**
 * ����� ����������� �� ��
 */
class ReservesArchiveModel extends CModel
{
    static public $_TABLE_FILE                  = 'file_reserves_doc_archive';
    static public $_TABLE_ARCHIVE               = 'reserves_doc_archive';
    static public $_TABLE_RESERVE_TO_LETTER     = 'reserves_to_letters';


    /**
     * ��������� ���������� WebDav ��������� ����� ����������� ����� ��� ������� 
     * � ����� ��� �� ����������� � ��������� �������� �������
     */
    const DAV_TMP_PATH = '/temp/';
    
    /**
     * ��������� ���������� ��� ������ ������
     */
    const TMP_PATH = '/tmp/reserves_archive/';
    
    /**
     * ���������� ������ �� WebDav ����������
     */
    const DAV_PATH = '/private/reserves_archive/';

    
    
    /**
     * ������ ������: ������ �� �������� ������;
     */
    const STATUS_NEW = 0;
    
    /**
     * ������ ������: �������� ������ � ��������;
     */
    const STATUS_INPROGRESS = 1;
    
    /**
     * ������ ������: ����� ������� ������;
     */
    const STATUS_SUCCESS = 2;
    
    /**
     * ������ ������: ������ ��� �������� ������;
     */
    const STATUS_ERROR = -1;
    
    
    /**
     * ������� �������� ������
     */
    const TRY_COUNT = 2;
   
    
    /**
     * ���������� ����� ���������
     * ��� �������� ������ ����� ���� �����
     * 
     */
    private $doc_type_cnt = array(
        20 => 2
    );
    
    
    /**
     * ���� ���������� ������ � ������
     * 
     * @var type 
     */
    private $doc_req = array(
        5, 20, 30, 70, 80
    );


    /**
     * ��������� ������ ������������ � /siteadmin/ltters/
     */
    const LETTER_GROUP_TXT  = "����� �� %s";
    /**
     * ����������� � ������ �� ������� �� ����� 
     * ��� ��������� �������� ��������� �� ������ ��
     */
    const LETTER_COMMENT    = "������� ����� ���������� \n %s"; 
    


    /**
     * �������� ������ �� �������� ������
     * 
     * @param type $data
     * @return type
     */
    public function addArchiveRequest($data)
    {
        return $this->db()->insert(self::$_TABLE_ARCHIVE, array(
            'uid' => $_SESSION['uid'],
            'status' => self::STATUS_NEW,
            'type' => 0, //���� � ��� ���� ��� ������
            'original_name' => $data['date_range'],
            'fields' => serialize($data['bs_ids'])
        ));
    }
    
    
    
    /**
     * ����� ���������� �������
     * 
     * @return type
     */
    public function getCount()
    {
        return $this->db()->val("
            SELECT COUNT(*) 
            FROM " . self::$_TABLE_ARCHIVE);
    }

    
    
    /**
     * ������ �������
     * 
     * @return \ReservesArchiveItemIterator
     */
    public function getList()
    {
        $sql = "
            SELECT 
                rda.*,
                frda.path || frda.fname AS filename
            FROM " . self::$_TABLE_ARCHIVE . " AS rda
            LEFT JOIN " . self::$_TABLE_FILE . " AS frda ON frda.id = rda.file_id
            LEFT JOIN users AS u ON u.uid = rda.uid
            ORDER BY rda.id DESC
        ";
        
        $sql = $this->_limit($sql);
        $data = $this->db()->rows($sql);
        return new ReservesArchiveItemIterator($data);
    }
    
    
    
    /**
     * ���������� ������ ������
     * 
     * @param type $id
     * @param type $data
     * @return type
     */
    public function updateArchive($id, $data)
    {
        return $this->db()->update(
                self::$_TABLE_ARCHIVE, 
                $data, 
                'id = ?i', $id);        
    }

    
    /**
     * �������� ������ ���������� 
     * �� ��������� ������
     * 
     * ������������� ��������� 
     * � ����� � ���������� 1-2 ������
     * 
     * @return boolean
     * @throws Exception
     */
    public function generateArchive()
    {
        //�������� ��������� ������ �� �������� ������
        $last = $this->db()->row("
            SELECT 
                rda.*,
                u.email
            FROM " . self::$_TABLE_ARCHIVE . " AS rda
            LEFT JOIN users AS u ON u.uid = rda.uid
            WHERE 
                rda.status IN(0,-1) 
                AND rda.try_count < ?i
            ORDER BY rda.id DESC, rda.status DESC
            LIMIT 1
        ", self::TRY_COUNT);
        
        if (!$last) {
            return false;
        }
            
        $archObj = new ReservesArchiveItemModel($last);
        //������ ������ � ������
        $this->updateArchive($archObj->id, array('status' => self::STATUS_INPROGRESS));
        
        try {
            
            //�������� ����� ����������
            require_once(ABS_PATH . '/classes/reserves/ReservesTServiceOrderModel.php');
            $bs_ids = $archObj->getFields();
            $files = CFile::selectFilesBySrc(
                    ReservesTServiceOrderModel::$_TABLE_RESERVES_FILES, 
                    $bs_ids, 
                    NULL, 
                    $this->db()->parse('doc_type IN(?l)', $this->doc_req));
            
            if (!$files) {
                throw new Exception('��� ������ ����������');
            }
                    
            $_archive_dir = uniqid();
            $_archive_path = self::TMP_PATH . $_archive_dir . DIRECTORY_SEPARATOR;
            $_dav_temp_path = self::DAV_TMP_PATH . $_archive_dir . DIRECTORY_SEPARATOR;
            $filelist = array();
            $cfile = new CFile();
            
            if (!$cfile->MakeDir(trim($_dav_temp_path,'/'))) {
                throw new Exception('�� ������� ������� ��������� ���������� � ���������');
            }
            
            //��������� ������ ������ ��� ������
            foreach ($files as $file) {
                
                $cfile->name = $file['fname'];
                $cfile->path = $file['path'];
                
                $info = new SplFileInfo($file['fname']);
                $_ext = $info->getExtension();                
                $name = $info->getBasename(".{$_ext}");
                
                //������� ��������� ���������� ��� ������ �� ��������� �������� �������
                $_local_tmp_path = "{$_archive_path}{$file['src_id']}/";
                if (!file_exists($_local_tmp_path)) {
                    if (!mkdir($_local_tmp_path, 0777, true)) {
                        throw new Exception("��� ���� �� ��������: {$_local_tmp_path}");
                    }
                }                 
                
                //������� ����� ��������� ������� � ����������� �� ��� ����
                $doc_cnt = isset($this->doc_type_cnt[$file['doc_type']]) && 
                           ($this->doc_type_cnt[$file['doc_type']] > 1)?
                           $this->doc_type_cnt[$file['doc_type']]:1;

                $_prefix = sprintf('%07d-', $file['src_id']);
                
                while ($doc_cnt > 0) {
                
                    $_suffix = ($doc_cnt>1)?"-{$doc_cnt}":'';
                    
                    $_dav_tmp_filename = "{$_dav_temp_path}{$name}{$_suffix}.{$_ext}";
                    if (!$cfile->copyFileTo($_dav_tmp_filename)) {
                        throw new Exception("�� ������� �����������: {$_dav_tmp_filename}");
                    }

                    $_tmp_name = empty($file['original_name'])?$name:$file['original_name'];

                    $_local_tmp_filename = "{$_local_tmp_path}{$_prefix}{$_tmp_name}{$_suffix}.{$_ext}";
                    //��������� ���� ������ ��� Windows
                    $_local_tmp_filename = iconv('WINDOWS-1251', 'CP866', $_local_tmp_filename);
                    //$_local_tmp_filename = iconv('WINDOWS-1251', 'UTF-8', $_local_tmp_filename);
                    
                    $filelist[$_dav_tmp_filename] = $_local_tmp_filename;
                    
                    //���������� ������ ��������� �� ������� �� ��������� �����
                    if (!$cfile->copyToLocalPathFromDav($_dav_tmp_filename, $_local_tmp_filename)) {
                        throw new Exception("�� ������� ����������� ���� �� ������� {$_dav_tmp_filename} 
                                             � ��������� �������� ������� {$_local_tmp_filename}");
                    }
                    
                    $doc_cnt--;
                }
            }
            
            
            if (empty($filelist)) {
                throw new Exception("�� ������� ������������ ������ ����������");
            }
            
            
            /*
             * @todo: ���� ���������� CFile::copyToLocalPathFromDav
             *             
            //���������� ������ ��������� �� ������� �� ��������� �����
            $cfile = new CFile();
            $res = $cfile->copyFilesToLocalPath($filelist);

            if (!$res) {
                throw new Exception("�� ������� ����������� ����� �� �������");
            }
            */
            

            //������� ����� ����������
            $_zip_filename = self::TMP_PATH . "{$_archive_dir}.zip";
            $zip = new ZipArchive();
            if ($zip->open($_zip_filename, ZipArchive::CREATE)) {
                foreach ($filelist as $filename) {
                    $localname = basename($filename);
                    $zip->addFile($filename, $localname);
                }
                $zip->close();
            }

            if (!file_exists($_zip_filename)) {
                throw new Exception("�� ������� ������� �����.");
            }

            //��������� ����� ����������
            $cfile = new CFile(array(
                'tmp_name' => $_zip_filename,
                'size' => filesize($_zip_filename),
                'name' => basename($_zip_filename)
            ), self::$_TABLE_FILE);

            $cfile->server_root = true;
            $cfile->original_name = $archObj->getName();
            $cfile->src_id = $archObj->id;
            $cfile->max_size = 104857600; //100Mb
            $cfile->MoveUploadedFile(self::DAV_PATH);
            
            if (!$cfile->id) {
                $_error = (is_array($cfile->error))?implode(', ', $cfile->error):$cfile->error;
                throw new Exception("�� ������� ��������� ����� � ���������: {$_error}");
            }
            

            $this->updateArchive($archObj->id, array(
                'file_id' => $cfile->id,
                //'techmessage' => null,
                'try_count' => $archObj->try_count + 1,
                'status' => self::STATUS_SUCCESS));
            
            //@todo: send mail here!

            //������� 
            $cfile->deleteFromTempDir($_dav_temp_path);
            delete_files($_archive_path, true, 1);
            unlink($_zip_filename);

            
            //------------------------------------------------------------------

            $this->addArchiveToLetters($bs_ids, $cfile);
            
        } catch (Exception $e) {
            $this->updateArchive($archObj->id, array(
                'status' => self::STATUS_ERROR,
                'try_count' => $archObj->try_count + 1,
                'techmessage' => $e->getMessage()
            ));
            
            return false;
        }
        
        return true;
    }
    
    /**
     * ������� ������ � ������� /siteadmin/letters/ ��� �� ������
     * 
     * @param type $ids
     * @param CFile $cfile
     */
    public function addArchiveToLetters($ids, CFile $cfile)
    {
        require_once(ABS_PATH . '/classes/reserves/ReservesTServiceOrderModel.php');
        require_once(ABS_PATH . '/classes/letters.php');
        //require_once(ABS_PATH . '/classes/country.php');
        require_once(ABS_PATH . '/classes/city.php');
        
        $users_reqv = ReservesTServiceOrderModel::model()->getReservesBankReqvByIds($ids);
        
        if (!$users_reqv) {
            return false;
        }
        
        $letters = new letters();
        $letter_ids_exist = $this->getLetterIds($ids);
        $letter_ids_new = array();
        //$countryObject = new country();
        $cityObject = new city();
        
        foreach ($users_reqv as $user_reqv) {

            $uid = $user_reqv['uid'];
            $src_id = $user_reqv['src_id'];
            $address = $user_reqv['address'];
            $country_id = null;
            $city_id = null;
            
            if ($res = parseAddress($address)) {
                $address = $res['address'];
                $country_id = $res['country_id'];
                $city_id = $res['city_id'];
            }
            
            if (!$city_id) {
                $city_name = trim(str_replace('�.', '', $user_reqv['city']));
                $city_data = $cityObject->getByName($city_name);
                if ($city_data) {
                    $country_id = $city_data['country_id'];
                    $city_id = $city_data['id'];
                }
            }
            
            if (!$city_id) {
                $city_id = $user_reqv['city_id'];
            }
            
            if (!$country_id) {
                $country_id = $user_reqv['country_id'];
            }
            

            $name = htmlspecialchars_decode($user_reqv['name'], ENT_QUOTES);

            $letter_company_id = $letters->findCompanyId(array(
                'fio' => $user_reqv['fio'],
                'name' => $name,
                'address' => $address,
                'index' => $user_reqv['index']
            ));

            if (!$letter_company_id) {
                $letter_company_id = $letters->addCompany(array(
                    'frm_company_name' => $name,
                    'country_columns' => array($country_id, $city_id),
                    'frm_company_index' => $user_reqv['index'],
                    'frm_company_address' => $address,
                    'frm_company_fio' => $user_reqv['fio'],
                    'frm_company_type' => sbr_meta::$types_short[$user_reqv['type']]
                ));
            }


            $frm = array(
                'letters_doc_frm_title' => sprintf(ReservesTServiceOrderModel::NUM_FORMAT, $user_reqv['src_id']),
                'letters_doc_frm_user_1_db_id' => 4,//��� ����
                'letters_doc_frm_user_2_db_id' => $letter_company_id,
                'letters_doc_frm_delivery_db_id' => 1,//������� ������
                'letters_doc_frm_user2_status_data' => 11,//������ ������
                'letters_doc_frm_user_1_section' => true,
                'letters_doc_frm_user_2_section' => true,
                'letters_doc_frm_group' => sprintf(self::LETTER_GROUP_TXT, $cfile->getOriginalName()),
                'letters_doc_frm_comment' => sprintf(self::LETTER_COMMENT, $cfile->getUrl())
            );

            if (isset($letter_ids_exist[$src_id])) {
                $letters->updateDocument($letter_ids_exist[$src_id], $frm);
            } else {
                $letter_ids_new[] = array(
                    'letter_id' => $letters->addDocument($frm),
                    'order_id' => $src_id
                );
            }
        }
        
        if (!empty($letter_ids_new)) {
            $this->addLetterIds($letter_ids_new);
        }
        
        
        return true;
    }
    
    
    /**
     * �������� ������ ID ����� ��� 
     * ����������� � ��������� ������� ��
     * 
     * @param type $bs_ids
     * @return type
     */
    public function getLetterIds($bs_ids)
    {
         $list = $this->db()->rows("
                    SELECT * FROM " . self::$_TABLE_RESERVE_TO_LETTER . " 
                    WHERE order_id IN (?l)", $bs_ids);
         
         $result = array();
         
         if ($list) {
             foreach ($list as $item) {
                 $result[$item['order_id']] = $item['letter_id'];
             }
         }
         
         return $result;
    }
    
    /**
     * �������� �������� ������ �� � ���������� ������
     * 
     * @param type $data
     * @return type
     */
    public function addLetterIds($data)
    {
        return $this->db()->insert(self::$_TABLE_RESERVE_TO_LETTER, $data);
    }
    
}