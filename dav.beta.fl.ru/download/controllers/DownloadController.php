<?php

class DownloadController extends CController 
{
    const USER_BASE_PATH = '^users(\/[-a-zA-Z0-9_]{2})?\/([a-zA-Z0-9]+[-a-zA-Z0-9_]{2,})';
    
    protected $uid = 0;
    protected $login;
    protected $permission = 'adm';
    protected $filename;


    /**
     * ������ � ������
     * 
     * @todo: ����� �� ���������� ��� ��� ���� �������� ����
     * 
     * @param type $params
     * @return boolean
     */
    /*
    protected function _resume($params)
    {
        return true;
    }
    */


   /**
    * ����������� �������� ���� ��������������
    * 
    * - ������ �� �������
    * 
    * @param type $params
    * @return boolean
    */
   protected function _upload($params, CFile $file)
   {
       
       $tableName = $file->getTableName();
       $allow_download = ($this->uid > 0);
       
       
       
       switch ($tableName) {
           
           case 'file_sbr':
               if ($this->uid > 0) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php');
                    $allow_download = (bool)sbr::isAllowDownloadFile($file->id, $this->uid);
               } else {
                   $allow_download = false;
               }
               break;
           
           default:
               
               //��������� ����� ���������
               $allow_download = true;
               
               break;
       }
       
        
       
       return $allow_download;
   }

   
   /**
    * @todo: ���� ��� ����� ���� �� ��������� ������� � NGINX!
    * 
    * ����� ������� �������� ���� ��������������
    *
    * @param type $params
    * @return boolean
    */
   protected function _projects($params)
   {
       return true;
   }

   
   /**
    * ����� ��������� � �� �������� ���������� � ������
    * 
    * @param type $params
    * @param CFile $file
    * @return type
    */
   protected function _contacts($params, CFile $file)
   {
        //��� ������������� ��� ��� � ����� �������
        //$tableName = $file->getTableName();

        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/users.php');
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php';
        
        $allow_download = false;
        $users = new users();
        $from_uid = $users->GetUid($error, $params['login']);
        
        if ($from_uid > 0) {
            $msgObj = new messages;
            $allow_download = $msgObj->isFileExist($from_uid, $this->uid, $file->id);
        }
        
        return $allow_download;
    }

    

    /**
     * ��������� ������� �� ��������� �� � ������
     * 
     * @param type $params
     * @return type
     */
    protected function _reserves($params)
    {
        return $this->_orders($params);
    }

    /**
     * ������ � ������ ��������� � ������ (�������� �� ��)
     * 
     * @param type $params
     * @return type
     */
    protected function _orders($params)
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');

        $orderModel = TServiceOrderModel::model();
        return $orderModel->isOrderMember($params['order_id'], $this->uid);        
    }

    

    /**
     * ��������� ������� �� ��������� ����� � ����� �����
     * 
     * @param type $params
     * @param CFile $file
     * @return type
     */
    protected function _attach($params, CFile $file)
    {
        $tableName = $file->getTableName();
        $allow_download = false;
        
        //��� ��� � ����� ����� ����� ������ ������ �� ��� ����������� ����� �����
        //��������� ��� �� ���� � ��������� ������ � ����
        switch ($tableName) {
            
            //����� �� � ���������� 
            case 'file_tservice_msg':

                require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceMsgModel.php');
                $this->permission = 'tservices';
                $msg_id = intval($file->src_id);
                $msgModel = TServiceMsgModel::model();
                $allow_download = $msgModel->isMsgMember($msg_id, $this->uid);
                
            break;
        }
        
        return $allow_download;
    }

    
    /**
     * ����������� ��������� ������ �� ����
     * ���� ��� �� ������������ ���������� ��� ����� 
     * 
     * @param type $params
     * @return boolean
     */
    protected function _default($params)
    {
        return false;
    }

    
    /**
     * ������� �������� �� ����������
     * 
     * @return boolean
     */
    protected function routeMaps()
    {
        $routes = array(

            //��������� ��
            'reserves' => array(
                'regex' => '/' . self::USER_BASE_PATH . '\/reserves\/(\d+)\//',
                'params' => array('login' => 2, 'order_id' => 3),
                'permission' => 'tservices',
                
                //�� ��������� � �� ���������� ������ ���� CFile
                //��� ��� ���� ������ ��� �������� ���������� �� URI
                //��� ������� �������� �� ����
                'is_file' => false 
             ),

            //��������� � ������ (����� ����������)
            'orders' => array(
                'regex' => '/' . self::USER_BASE_PATH . '\/private\/orders\/(\d+)\//',
                'params' => array('login' => 2, 'order_id' => 3),
                'permission' => 'tservices',
                
                //�� ��������� � �� ���������� ������ ���� CFile
                //��� ��� ���� ������ ��� �������� ���������� �� URI
                //��� ������� �������� �� ����
                'is_file' => false 
             ),
            
            //��������� � ��
            'contacts' => array(
                'regex' => '/' . self::USER_BASE_PATH . '\/contacts\//',
                'params' => array('login' => 2)
            ),

            //����� ������� ���������� (���������)
            'upload' => array(
                'regex' => '/' . self::USER_BASE_PATH . '\/upload\//',
                'params' => array('login' => 2),
                'is_check_auth_in_method' => true
            ),

            //������ ������������ (���������)
            'resume' => array(
                'regex' => '/' . self::USER_BASE_PATH . '\/resume\//',
                'params' => array('login' => 2),
                
                //�� ��������� ����������� ���� ������ ����
                'is_auth' => false
            ),

            //��������� ����� (���� ������ ��������� � ������) 
            //� ����� ��������� (��������� � ������) - ���������� ������� � ��������� ���������� (�� account)
            //� ����� ��������� � �� (�������� ���� ������� ���������� � ������) - ���������� ������� � ��������� ���������� (�� orders)
            'attach' => array(
                'regex' => '/' . self::USER_BASE_PATH . '\/attach\//',
                'params' => array('login' => 2)
            ),

            //����� ������ (������ ��������� � ������)
            'account' => array(
                'regex' => '/' . self::USER_BASE_PATH . '\/private\/account\//',
                'params' => array('login' => 2),
                
                'is_file' => false
            ),
            
            //������ ������
            'letters' => array(
                'regex' => '/^letters\//',
                'is_file' => false
            ),
            
            /*
            //����� �������� (���������) - �� ������������ �� ��������
            'projects' => array(
                'regex' => '/projects\/upload\/(\d+)\//',
                'params' => array('project_id' => 1)
            ),*/
        );       

        
        foreach ($routes as $action => $route) {
            
            $match = array();
            
            if (preg_match($route['regex'], $this->filename, $match)) {
                
                $params = array();
                if (isset($route['params'])) {
                    foreach ($route['params'] as $pname => $pidx) {
                        $params[$pname] = $match[$pidx];
                    }
                }

                if (isset($route['permission'])) {
                    $this->permission = $route['permission'];
                }

                $is_file = isset($route['is_file']) && $route['is_file'] === false? false:true;
                $is_auth = isset($route['is_auth']) && $route['is_auth'] === false? false:true;
                $is_check_auth_in_method = isset($route['is_check_auth_in_method']) && $route['is_check_auth_in_method'] === true? true:false;
                
                return array($action, $params, $is_file, $is_auth, $is_check_auth_in_method);
            }
        }

        return false;
    }

    
    

    /**
     * ������������� �����������
     */
    public function init($path) 
    {
        parent::init();

        $this->filename = $path? ltrim(parse_url($path, PHP_URL_PATH) ,'/') : null;
        
        if ($this->filename) {
            $this->filename = $this->fixFilename($this->filename);
        }
        
        if (!$this->filename) {
            $this->send404();
        }
        

        $this->uid = isset($_SESSION['uid'])? $_SESSION['uid'] : 0;
        $this->login = isset($_SESSION['login'])? $_SESSION['login'] : '';
    }


    /**
     * ��������� ������� �� ������-���� ������
     * 
     * @param string $action
     * @return bool
     */
    /*
    public function beforeAction($action) 
    {
    }
    */
    
    
    /**
     * ��������� ���������� ������� �� URI ��� ���������� 
     * ��� ������ ������ � � ������� ������ ����������� 
     * �� ����������� ��������� ��������� ����.
     * �� ����������� �������� ������ �����.
     */
    public function actionIndex()
    {
        $_data = $this->routeMaps();

        if (!$_data) {
            $this->send404();
        }
        
        $_method = "_{$_data[0]}";
        $_params = $_data[1];
        $_is_file = $_data[2];
        $_is_auth = $_data[3];
        $_is_check_auth_in_method = $_data[4];
        
        //����������� �� ����� ����� ���� ����
        if ($_is_auth === false) {
            $this->sendFile();
        //���� ��������� �����������    
        } elseif (!$this->isCurrentUserAuth() && 
                  !$_is_check_auth_in_method) {
            $this->send404();
        }

        //���� ��� ���������� �������� ����� �� ����� ���� �������
        if (isset($_params['login'])) {
            $allow_download = $this->isCurrentUserLogin($_params['login']);
        } 
        
        //����� ��� ����� ����� � ���� ���� ����� �������
        if (!$allow_download) {
            $allow_download = currentUserHasPermissions($this->permission);
        }
        
        //���� ��� �� ��� ��� ����� ��������� ��������
        if (!$allow_download) {
            if (method_exists($this, $_method)) {
                
                $GLOBALS['DB'] = new DB('master');
                
                if ($_is_file) {
                    $file = new CFile($this->filename);
                    $allow_download = ($file->id > 0)? $this->{$_method}($_params, $file) : false;
                } else {
                    $allow_download = $this->{$_method}($_params);
                }

            } else {
                $allow_download = $this->_default($_params);
            }
        }

        if ($allow_download) {
            $this->sendFile();
        }
            
        $this->send404();
    }
    
    
    /**
     * ���������� �� ���������� ���� � ����� �����
     * 
     * @return type
     */
    protected function fixFilename($filename)
    {
        $components = explode('/', $filename);
        $components_cnt = count($components);
        
        //������ ���� ��� ���������� �����������
        if ($components[0] === 'users' && $components_cnt > 2) {
            if (strlen($components[1]) > 2) {
                array_splice($components, 1, 0, array(substr($components[1], 0, 2)));
                $filename = implode('/',  $components);
            }
        }
        
        return $filename;
    }
    

    protected function isCurrentUserAuth()
    {
        return $this->uid > 0;
    }
    

    protected function isCurrentUserLogin($login)
    {
        return $login === $this->login;
    }
    

    protected function sendFile()
    {
        header('X-Accel-Redirect: /bzqvzvyw/' . $this->filename);
        header("Content-Type:");

        if (is_local()) {
            //header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($this->filename));
        }

        exit;         
    }
    

    protected function send404()
    {
        global $host;
        $this->redirect("{$host}/404.php");
    }
}