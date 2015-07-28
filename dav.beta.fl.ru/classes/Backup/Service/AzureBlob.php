<?php

require_once('Abstract.php');

use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Blob\Models\CreateContainerOptions;
use WindowsAzure\Blob\Models\CreateBlobOptions;
use WindowsAzure\Blob\Models\PublicAccessType;
use WindowsAzure\Common\ServiceException;

class Backup_Service_AzureBlob extends Backup_Service_Abstract
{
    const DELETED_CONTAINER = 'deleted';
    
    //��������� ����������
    protected $connectionString;
    
    //������� ���������
    protected $containerName;
    //������� ��� ������
    protected $blobName;
    
    //��������� � �������� ���������
    protected $createContainerName = true;

    //������ ����������� ������� ��� ���� ������� � ����������
    protected $existContainers = array();

    //������� ���� ��� url � �����
    protected $filePrefix;


    //������ �������������� � ��������
    protected $blobRestProxy;
    
    /**
     * ����������� �������
     * 
     * @param type $options
     * @throws Exception
     */
    public function __construct($options) 
    {
        $connectionOption = array();
        
        if(isset($options['�reateContainerName'])) {
            $this->createContainerName = $options['�reateContainerName'] === true;
        }
        
        if(!isset($options['FilePrefix'])){
            throw new Exception('Required option "FilePrefix" is missing.');
        }

        $this->filePrefix = rtrim($options['FilePrefix'],'/');        
        
        
        if(!isset($options['UseDevelopmentStorage'])){
        
            $connectionOption['DefaultEndpointsProtocol'] = 
                    isset($options['DefaultEndpointsProtocol'])?
                        $options['DefaultEndpointsProtocol']:
                        'https';


            if(!isset($options['AccountName'])){
                throw new Exception('Required option "AccountName" is missing.');
            }

            $connectionOption['AccountName'] = $options['AccountName'];


            if(!isset($options['AccountKey'])){
                throw new Exception('Required option "AccountKey" is missing.');
            }

            $connectionOption['AccountKey'] = $options['AccountKey'];   
        
        } else {
            $connectionOption['UseDevelopmentStorage'] = 'true';
        }
        
        $this->connectionString = urldecode(http_build_query($connectionOption,'',';'));
        $this->blobRestProxy = ServicesBuilder::getInstance()->createBlobService($this->connectionString);
    }
    

    /**
     * ���������� ������������� ���� � �����
     * ������ ���������� ���� �������� ����������� Blob ���������
     * 
     * @param type $filepath
     */
    public function setFilePath($filepath) 
    {
        $this->filepath = ltrim($filepath, '/');
        if(!$this->filepath) {
            throw new Exception("Filepath is empty.");
        }        
        
        $this->containerName = current(explode('/', $this->filepath));
        if(!$this->containerName) {
            throw new Exception("Not found container name: {$this->filepath}");
        }
        
        $this->blobName  = ltrim($this->filepath, "{$this->containerName}/");
        if(!$this->blobName){
            throw new Exception("Not found blob name: {$this->filepath}");
        }
    }

    
    /**
     * ������� ������������ ���������
     * 
     * @param type $name
     * @throws \WindowsAzure\Common\ServiceException
     */
    public function createContainer($name)
    {
        $createContainerOptions = new CreateContainerOptions(); 
        $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);
        
        try
        {
            // �������� ����������.
            $this->blobRestProxy->createContainer($name, $createContainerOptions);
        }
        catch(ServiceException $e)
        {
            //@todo: ������ �� ������ ���� ����� ��������� ��� ������
            //http://msdn.microsoft.com/ru-ru/library/windowsazure/dd179439.aspx
            if($e->getCode() != 409) {
                //������ ������ ������ � ������ ������� ��������
                throw $e;
            }
        }        
    }
    
   
    /**
     * ������� ��������� ���� ���� �����������
     * 
     * @return boolean
     * @throws \WindowsAzure\Common\ServiceException
     */
    public function createContainerIfPossible()
    {
        //���� ��������� ��� ���������� 
        //��� ����������� ����� �� ��������� ������� �� �������
        if(in_array($this->containerName, $this->existContainers) || 
           !$this->createContainerName) {
            return false;
        }

        $this->createContainer($this->containerName);
        
        //��������������� ��������� ����������
        $this->existContainers[] = $this->containerName;
        return true;
    }



    /**
     * �������� Blob � ���������
     * 
     * @param type $filepath
     * @return boolean
     * @throws Exception
     */
    public function create($filepath) 
    {
        $this->setFilePath($filepath);
        $this->createContainerIfPossible();

        $fullpath = "{$this->filePrefix}/{$this->filepath}";
        
        // ���������� mime-���
        $finfo = finfo_open(FILEINFO_MIME_TYPE); 
        $mime = finfo_file($finfo, $fullpath);
        finfo_close($finfo);

        if(!$mime) {
            throw new Exception("Not found mime-type: {$fullpath}");
        }
        
        $content = fopen($fullpath, "r");

        if(!$content) {
            throw new Exception("Cant open file: {$fullpath}");
        }
        
        $options = new CreateBlobOptions();
        $options->setBlobContentType($mime);
        
        //�������� blob-�������
        $this->blobRestProxy->createBlockBlob(
               $this->containerName, 
               $this->blobName, 
               $content, 
               $options);
       
        return true;
    }
    
    
    /**
     * �������� Blob �� ����������
     * 
     * @param type $filepath
     * @return boolean
     */
    public function delete($filepath) 
    {
        $this->setFilePath($filepath);
        
        //�������� � ���������
        $this->blobRestProxy->copyBlob(
                self::DELETED_CONTAINER, 
                $this->filepath, 
                $this->containerName, 
                $this->blobName);
        
        //�������� Blob
        $this->blobRestProxy->deleteBlob(
                $this->containerName, 
                $this->blobName);
        
        return true;
    }
    
    
    /**
     * ����������� Blob
     * 
     * @param type $from
     * @param type $to
     */
    public function copy($from, $to)
    {
        //�������� ��������
        $this->setFilePath($from);
        $fromContainerName = $this->containerName;
        $fromBlobName = $this->blobName;
        
        //�������� ����������
        $this->setFilePath($to);
        $toContainerName = $this->containerName;
        $toBlobName = $this->blobName; 
        //������� ������� ��������� ����������
        $this->createContainerIfPossible();
        
        //��������
        $this->blobRestProxy->copyBlob(
                $toContainerName, 
                $toBlobName, 
                $fromContainerName, 
                $fromBlobName);        
        
        return true;
    }
    
}