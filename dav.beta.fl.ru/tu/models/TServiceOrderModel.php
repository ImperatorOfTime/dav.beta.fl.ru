<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/atservices_model.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_helper.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_smail.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_order_history.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/account.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");

/**
 * Class TServiceOrderModel
 * ������ ������ ������� ������tservices
 */
class TServiceOrderModel extends atservices_model {
        
        const SOLT = "2AxIA0A2symadzu";
        
        //���� � ����� �������� ���-�� ����� ������� �����
        const MEMCACHE_EVENT_CNT_KEY_PREFIX = "tserviceOrderEventsCnt";
        const MEMCACHE_LAST_EVENT_ORDER_ID_KEY_PREFIX = "tserviceOrderEventLastId";
        const MEMCACHE_EVENT_CNT_TTL = 1800;
        const MEMCACHE_EVENT_CNT_TAG_KEY = "tserviceOrderEventsCnt";
        
        
        //������� �� ����� � ����� ��������
        const TAX = 0.1;//10%
        const TAX_DEBT_PERIOD = 3;//+3 ��� � ����� ���������� ������ ��� ��������� �������������
        const TAX_MSG = "�������� �� ����� ������� ������ #%d �%s�";
        
        
        /**
         * �� ����� ����� ������ ����� ������������ �����
         */
        //�� ������ ��
        const TYPE_TSERVICE     = 0;
        //�� ������ �������
        const TYPE_PROJECT      = 1;
        //������������ �����
        const TYPE_PERSONAL     = 2;
        
        
        
        /**
         * ��� ��������� ������� ������
         */
        //����� ����� �������� ������������� �����������
        const STATUS_NEW        = 0;
        //��������� ���������
        const STATUS_DECLINE    = -1;
        //������������ �������
        const STATUS_CANCEL     = -2;
        //��������� ���������� � ����� ������
        const STATUS_ACCEPT     = 1;
        //��������� �������� � ���������� ������
        const STATUS_FRLCLOSE   = 2;
        //������������ ��������� ��������������
        const STATUS_EMPCLOSE   = 3;
        //������������ �������� �� ���������
        const STATUS_FIX        = 4;
        
        
       /**
        * ��������� �������� ������������ 
        * � �� ����������
        *  
        * @var array
        */
       protected $STATUS_LIST = array(
           'cancel'    => self::STATUS_CANCEL,
           'decline'   => self::STATUS_DECLINE,
           'accept'    => self::STATUS_ACCEPT,
           'fix'       => self::STATUS_FIX,
           'done'      => self::STATUS_FRLCLOSE,
           'close'     => self::STATUS_EMPCLOSE
       );


       /**
         * � ����� ������ ����� 
         * ���������� ����� ��������
         * 
         * @var array
         */
        protected $STATUS_EMP_NEXT = array(
            self::STATUS_NEW => array(
                self::STATUS_CANCEL     //����� -> �������
            ),
            
            self::STATUS_ACCEPT => array(
                self::STATUS_EMPCLOSE   //� ������ -> ������
            ),
            
            self::STATUS_FRLCLOSE => array(
                self::STATUS_FIX,       //������ -> �� ���������
                self::STATUS_EMPCLOSE   //������ -> ������
            ),
            
            self::STATUS_FIX => array(
                self::STATUS_EMPCLOSE   //�� ��������� -> ������
            )
        );

        
        /**
         * � ����� ������ ����� 
         * ���������� ����� �����������
         * 
         * @var array
         */
        protected $STATUS_FRL_NEXT = array(
            self::STATUS_NEW => array(
                self::STATUS_ACCEPT,    //����� -> � ������
                self::STATUS_DECLINE    //����� -> ���������
            ),
            
            self::STATUS_ACCEPT => array(
                self::STATUS_FRLCLOSE   //� ������ -> ������
            ),
            
            self::STATUS_FIX => array(
                self::STATUS_FRLCLOSE   //�� ��������� -> ������
            )
        );



        //��������� ���������� �������� ��� �������
        protected $STATUS_PARAMS = array(
            'new' => array(self::STATUS_NEW),
            'accept' => array(
                self::STATUS_ACCEPT,
                self::STATUS_FIX
            ),
            'close' => array(
                self::STATUS_FRLCLOSE, 
                self::STATUS_EMPCLOSE,
                
                self::STATUS_CANCEL,
                self::STATUS_DECLINE
            )
            
            /*
            'accept' => array(self::STATUS_ACCEPT),
            'decline' => array(self::STATUS_DECLINE),
            'cancel' => array(self::STATUS_CANCEL),
            'open' => array(self::STATUS_NEW, self::STATUS_ACCEPT),
            'close' => array(self::STATUS_FRLCLOSE, self::STATUS_EMPCLOSE),
            'all' => array()*/
        );
        
        

        private $TABLE                  = 'tservices_orders';
        static public $_TABLE           = 'tservices_orders';
        private $TABLE_FREELANCER       = 'freelancer';
        private $TABLE_EMPLOYER         = 'employer';
        private $TABLE_USERS            = 'users';
        private $TABLE_DEBT             = 'tservices_orders_debt';
        private $TABLE_FEEDBACK         = 'tservices_orders_feedbacks';
        private $TABLE_ACTIVATE         = 'tservices_order_activate';
        private $TABLE_ORDER_FILES      = 'file_tservices_order';
        private $TABLE_ORDER_MSG_FILES  = 'file_tservice_msg';
        private $TABLE_ORDER_MSG        = 'tservices_msg';



        //���.����� ������
        protected $order_extra = array();
        //������� �� �����
        protected $order_is_express = FALSE;
        
        protected $emp_id;
        protected $status = NULL;
        protected $is_adm = FALSE;

        protected $order = array();
        protected $_mapper = array();
        
        
        
        //�������� ������ ������
        const PAYTYPE_DEFAULT = 0;
        const PAYTYPE_RESERVE = 1;
        //��� ������ ������
        protected $order_paytype = self::PAYTYPE_DEFAULT;
        
        
        
        /**
         * ������ �������
         * (���� � ������ ��������� ��� ������������ ������ ���������� � �����)
         * @var object
         */
        protected $_membuff = NULL;


        
        /**
         * ������� ��������� ���������� 
         * ������� �� ��� ����
         * 
         * @param type $status
         * @return type
         */
        public function getStatusTag($status)
        {
            $flip = array_flip($this->STATUS_LIST);
            return @$flip[$status];
        }



        /**
         * ������������� ��� ��������� ���������� ������
         * 
         * @param array $attributes
         * @return type
         */
        public function attributes($attributes = null) 
        {
            if (is_null($attributes)) 
            {
                return get_object_vars($this);
            }

            foreach ($attributes as $key => $value) 
            {
                if (property_exists($this, $key)) 
                {
                    $this->{$key} = $value;
                }
            }
            
            
            //������ ��� ��������� ���������
            if($this->status)
            {
                if(!isset($this->STATUS_PARAMS[$this->status])) 
                {   
                    $this->status = NULL;
                    return FALSE;
                }
                $this->status = $this->STATUS_PARAMS[$this->status];
            }
            
            //��������� ��� ������ ������
            $this->order_paytype = in_array(intval($this->order_paytype),array(self::PAYTYPE_DEFAULT,self::PAYTYPE_RESERVE))?intval($this->order_paytype):self::PAYTYPE_DEFAULT;
            
            return TRUE;
        }
        
        
        /**
         * ������������ ������ �� ������ �� � ������� ����������
         * 
         * @param int $id - ID ��
         * @return array
         */
        protected function _prepare($id)
        {
            $tu = new tservices();
            $order = $tu->getCardForOrder($id);
            if(!$order) return FALSE;

            //@todo: � �� �������� ��� ��������������� �����������
            //������� ��������� �������
            $order['title'] = htmlspecialchars_decode($order['title'], ENT_QUOTES);
            $order['description'] = htmlspecialchars_decode($order['description'], ENT_QUOTES);
            $order['requirement'] = htmlspecialchars_decode($order['requirement'], ENT_QUOTES);
            
            $order['emp_id'] = $this->emp_id;
            $order['order_price'] = intval($order['price']);
            $order['order_days'] = intval($order['days']);
            $order['order_is_express'] = $this->order_is_express;
            
            
            //���� ������� ��������� ����������� ����� �� ����� �� �� ���������� ������ ������
            if($this->order_paytype > self::PAYTYPE_DEFAULT)
            {
                $is_allow_reserve = tservices_helper::isAllowOrderReserve($order['category_id']);
                $order['pay_type'] = ($is_allow_reserve)?$this->order_paytype:self::PAYTYPE_DEFAULT;
            }
            else 
            {
                $order['pay_type'] = $this->order_paytype;
            }
            
            //���� ���� ���.����� �� ��������� ��� � ������
            if(!empty($this->order_extra))
            {
                foreach($this->order_extra as $_value)
                {
                    $_value = intval($_value);
                    if(!isset($order['extra'][$_value])) continue;
                    $_extra = $order['extra'][$_value];
                    $order['extra'][$_value]['title'] = htmlspecialchars_decode(
                            $order['extra'][$_value]['title'] , ENT_QUOTES);
                    
                    $order['order_price'] += intval($_extra['price']);
                    $order['order_days'] += intval($_extra['days']);
                    $order['order_extra'][] = $_value;
                }
            }            
            
            //���� ���� ����� ����� ��������� �� ��������� ��� � ������
            if($this->order_is_express == TRUE && $order['is_express'] == 't')
            {
                $order['order_days'] = intval($order['express_days']);
                $order['order_price'] += intval($order['express_price']);
            }
            
            //���� ���� �������� ������� ��
            //� ��������� �� �������� ������
            if(self::TAX > 0) 
            {
                $order['tax'] = self::TAX;
                $order['tax_price'] = floor($order['order_price'] * self::TAX);
            }
            
            $this->order = $order;

            foreach(array('order_extra','extra') AS $key)
            {
               if(is_array($order[$key]) && count($order[$key])) 
               {
                   $order[$key] = serialize($order[$key]);
               }
            }
            

            return $order;
        }

        
        /**
         * ������ ������� ��� ���������
         * 
         * @param int $uid
         * @return array
         */
        public function getListForEmp($uid)
        {
            return $this->getList($uid);
        }

        
        
        /**
         * ������ ������� ��� ����������
         * 
         * @param int $uid
         * @return array
         */
        public function getListForFrl($uid)
        {
            return $this->getList($uid, FALSE);
        }

        
        
        /**
         * ������ ������� �����
         * 
         * @param int $uid - ID �����
         * @param bool $is_emp - ��� ���� ��������� ��� �������� 
         * @return array - ������ �������, ����� ���������� ���������������
         */
        public function getList($uid, $is_emp = TRUE)
        {
           $in_status = ''; 
           if(!empty($this->status))
           {
               $in_status = $this->db()->parse("AND o.status IN (?l)",$this->status);
           }
           
           $user_table = ($is_emp)?$this->TABLE_FREELANCER:$this->TABLE_EMPLOYER; 
           $user_field = ($is_emp)?'frl_id':'emp_id';
           $where_field = ($is_emp)?'emp_id':'frl_id';
           
           $prefix = ($is_emp)?'emp':'frl';
           $anti_prefix = ($is_emp)?'frl':'emp';
           
           $sql = $this->db()->parse("
                SELECT 
                    o.id,
                    o.title,
                    o.order_price,
                    o.order_days,
                    o.status,
                    o.tax,
                    o.tax_price,
                    o.close_date,
                    o.frl_read,
                    o.emp_read,
                    o.pay_type,
                    o.emp_feedback_id,
                    o.frl_feedback_id,    
                    
                    u.uid,
                    u.login,
                    u.uname,
                    u.usurname,
                    u.role,
                    u.photo,
                    u.is_pro,
                    u.is_profi,
                    u.is_pro_test,
                    u.is_team,
                    u.is_verify,                    
                    u.is_banned,
                    u.self_deleted,
                    
                    fb.feedback AS frl_feedback,
                    fb.rating AS frl_rating,
                    eb.feedback AS emp_feedback,
                    eb.rating AS emp_rating                    

                FROM {$this->TABLE} AS o 
                LEFT JOIN {$user_table} AS u ON u.uid = o.{$user_field}
                LEFT JOIN {$this->TABLE_FEEDBACK} AS fb ON (fb.id = o.frl_feedback_id AND fb.deleted = FALSE)
                LEFT JOIN {$this->TABLE_FEEDBACK} AS eb ON (eb.id = o.emp_feedback_id AND eb.deleted = FALSE)
                WHERE
                    o.{$where_field} = ?i 
                    {$in_status}
                ORDER BY
                    o.{$prefix}_read, 
                    o.date_{$anti_prefix}_last DESC, 
                    o.id DESC
            ",$uid);
           
            $sql = $this->_limit($sql);
            $rows = $this->db()->rows($sql);
            
            //���� ������ �������� �������� ������
            //�� �������� ��� ����������� ������
            if(!$this->is_adm && count($rows))
            {
                $ids = array();
                foreach($rows as &$row) 
                {
                    if($row["{$prefix}_read"] == 'f') $ids[] = $row['id'];
                    
                    if($row['pay_type'] == self::PAYTYPE_RESERVE)
                    {
                        $reserveInstance = ReservesModelFactory::getInstance(ReservesModelFactory::TYPE_TSERVICE_ORDER);
                        if($reserveInstance)
                        {
                            $reserve_data = $reserveInstance->getReserve($row['id']);
                            if(!empty($reserve_data)) $row['reserve_data'] = $reserve_data;
                            $row['reserve'] = $reserveInstance;
                        }
                    }
                }
                
                if(!empty($ids)) $this->markAsReadOrderEvents($uid, $ids, $is_emp);   
            }
            
            return $rows;
        }

        

        /**
         * ���������� ������� �����
         * 
         * @param int $uid - ID �����
         * @param bool $is_emp - ��� �����
         * @return int - ���-�� ������� �����
         */
        public function getCount($uid, $is_emp = TRUE)
        {
            $where_field = ($is_emp)?'emp_id':'frl_id';
            
            $sql = $this->db()->parse("
                SELECT COUNT(*) 
                FROM {$this->TABLE} AS o 
                WHERE 
                    o.{$where_field} = ?i
                ",$uid);
            
            return (int)$this->db()->val($sql);
        }


        
        /**
         * ���-�� ������� �����
         * ������������� �� ��������
         * @todo �� ���� ������ �������� � ������� ����� ��������� ������� � ��� ������ �����
         * @todo ����� �������� �����������!
         * 
         * @param int $uid
         * @param bool $is_emp
         * @return array
         */
        public function getCounts($uid, $is_emp = TRUE)
        {
            $where_field = ($is_emp)?'emp_id':'frl_id';            
            $select = array('COUNT(*) as total');
            
            if(!empty($this->STATUS_PARAMS))
                foreach($this->STATUS_PARAMS as $key => $value)
                {
                    if(empty($value))continue;
                    $select[] = $this->db()->parse("SUM( CASE WHEN A.status IN(?l) THEN 1 ELSE 0 END ) AS {$key}", $value);
                }
            
            $select = implode(',' . PHP_EOL, $select);
            
            $sql = <<<SQL
                SELECT
                    {$select}
                FROM
                (
                    SELECT
                        o.status AS status
                    FROM {$this->TABLE} AS o
                    WHERE
                        o.{$where_field} = ?i
                ) A
SQL;
                             
             return $this->db()->row($sql, $uid);
        }

        

        protected function addMapper($key, $values, $prefix = '')
        {
            $_select_sql = array();
            if(!count($values)) return '';
            if(!isset($this->_mapper[$key])) 
                $this->_mapper[$key] = array();
            $this->_mapper[$key] += $values;
            
            foreach($values as $idx => $value)
            {
                $_select_sql[] = (is_string($idx)?
                        "{$prefix}{$idx} AS {$value}":
                        "{$prefix}{$value}");
            }
            
            return implode(', ', $_select_sql);
        }

        protected function mapper(&$row, $key, $remove = FALSE)
        {
            if(!isset($this->_mapper[$key])) return FALSE;
            
            foreach($this->_mapper[$key] as $idx => $value)
            {
                $_idx = (is_string($idx))?$idx:$value;
                $row[$key][$_idx] = @$row[$value];
                if($remove) unset($row[$value]);
            }
            
            return TRUE;
        }

        
        
        /**
         * �������� �������� ������
         * @todo ������ ���������� ���� ���� �������, ���������� ��������� �� ����
         * 
         * @param int $order_id - ID ������
         * @param int $uid - ID ���������/���������� ������
         * @return array
         */
        public function getCard($order_id, $uid, $fb_deleted = FALSE)
        {
            $_select_freelancer_sql = 
            $this->addMapper('freelancer', 
                    array(
                        'uid' => 'freelancer_id',
                        'login',
                        'uname',
                        'usurname',
                        'photo',
                        'is_pro',
                        'is_profi',
                        'is_pro_test',
                        'is_team',
                        'is_verify',
                        'role',
                        'skype',
                        'second_email',
                        'email',
                        'country',
                        'city' => 'user_city',
                        'is_banned',
                        'self_deleted'
                        ),'u.');

             $_select_employer_sql = 
             $this->addMapper('employer', array(
                    'uid' => 'emp_uid',
                    'login' => 'emp_login',
                    'uname' => 'emp_uname',
                    'usurname' => 'emp_usurname',
                    'photo' => 'emp_photo',
                    'email' => 'emp_email',
                    'photo' => 'emp_photo',
                    'is_pro' => 'emp_is_pro',
                    'is_pro_test' => 'emp_is_pro_test',
                    'is_team' => 'emp_is_team',
                    'is_verify' => 'emp_is_verify',
                    'role' => 'emp_role',
                    'skype' => 'emp_skype',
                    'second_email' => 'emp_second_email',
                    'city' => 'emp_city',
                    'is_banned' => 'emp_is_banned',
                    'self_deleted' => 'emp_self_deleted'
                 ),'e.');

            $sql = "
                SELECT 
                    fb.feedback AS frl_feedback,
                    fb.rating AS frl_rating,
                    fb.posted_time AS frl_fb_posted_time,
                    fb.deleted AS frl_fb_deleted,
                    eb.feedback AS emp_feedback,
                    eb.rating AS emp_rating,
                    eb.posted_time AS emp_fb_posted_time,
                    eb.deleted AS emp_fb_deleted,
                    o.*,
                    {$_select_freelancer_sql},
                    {$_select_employer_sql}
                FROM {$this->TABLE} AS o
                INNER JOIN {$this->TABLE_FREELANCER} AS u ON u.uid = o.frl_id 
                INNER JOIN {$this->TABLE_EMPLOYER} AS e ON e.uid = o.emp_id 
                LEFT JOIN {$this->TABLE_FEEDBACK} AS fb 
                ON (fb.id = o.frl_feedback_id".(!$fb_deleted?" AND fb.deleted = FALSE)":")")." 
                LEFT JOIN {$this->TABLE_FEEDBACK} AS eb 
                ON (eb.id = o.emp_feedback_id".(!$fb_deleted?" AND eb.deleted = FALSE)":")")."
                WHERE
                    o.id = ?i 
                    ".(($this->is_adm)?"":"AND (o.frl_id = ?i OR o.emp_id = ?i)")."
                LIMIT 1
            ";
            
            $row = $this->db()->row($sql, $order_id, $uid, $uid);
            
            if($row)
            {
                $row['order_extra'] = ($row['order_extra'])?mb_unserialize($row['order_extra']):array();
                $row['extra'] = ($row['extra'])?mb_unserialize($row['extra']):array();
                $row['files'] = $this->getFiles($row['id']);
                
                $this->mapper(&$row, 'freelancer', TRUE);
                $this->mapper(&$row, 'employer', TRUE);
                
                if($row['pay_type'] == self::PAYTYPE_RESERVE)
                {
                    $reserveInstance = ReservesModelFactory::getInstance(ReservesModelFactory::TYPE_TSERVICE_ORDER);
                    if($reserveInstance)
                    {
                        $reserveInstance->setSrcObject($this);
                        $reserve_data = $reserveInstance->getReserve($row['id']);
                        if(!empty($reserve_data)) $row['reserve_data'] = $reserve_data;
                        $row['reserve'] = $reserveInstance;
                    }
                }
                
                $this->order = $row;
                
            }
            
            return $row;
        }

        
        
        /**
         * �������� �������� ������ 
         * � ������� �����������
         * 
         * @param type $order_id
         */
        public function getShortCard($order_id)
        {
            $_select_freelancer_sql = 
            $this->addMapper('freelancer', 
                    array(
                        'uid' => 'freelancer_id',
                        'login',
                        'uname',
                        'usurname',
                        'photo',
                        'is_pro',
                        'is_pro_test',
                        'is_team',
                        'is_verify',
                        'role',
                        'skype',
                        'second_email',
                        'email',
                        'country',
                        'city' => 'user_city',
                        'is_banned',
                        'self_deleted'
                        ),'u.');

             $_select_employer_sql = 
             $this->addMapper('employer', array(
                    'uid' => 'emp_uid',
                    'login' => 'emp_login',
                    'uname' => 'emp_uname',
                    'usurname' => 'emp_usurname',
                    'photo' => 'emp_photo',
                    'email' => 'emp_email',
                    'photo' => 'emp_photo',
                    'is_pro' => 'emp_is_pro',
                    'is_pro_test' => 'emp_is_pro_test',
                    'is_team' => 'emp_is_team',
                    'is_verify' => 'emp_is_verify',
                    'role' => 'emp_role',
                    'skype' => 'emp_skype',
                    'second_email' => 'emp_second_email',
                    'city' => 'emp_city',
                    'is_banned' => 'emp_is_banned',
                    'self_deleted' => 'emp_self_deleted'
                 ),'e.');
             
            $sql = "
                SELECT 
                    o.*,
                    {$_select_freelancer_sql},
                    {$_select_employer_sql}
                FROM {$this->TABLE} AS o
                INNER JOIN {$this->TABLE_FREELANCER} AS u ON u.uid = o.frl_id 
                INNER JOIN {$this->TABLE_EMPLOYER} AS e ON e.uid = o.emp_id 
                WHERE
                    o.id = ?i 
                LIMIT 1
            ";
            
            $row = $this->db()->row($sql, $order_id);             
            
            if($row)
            {
                $row['order_extra'] = ($row['order_extra'])?mb_unserialize($row['order_extra']):array();
                $row['extra'] = ($row['extra'])?mb_unserialize($row['extra']):array();
                
                $this->mapper(&$row, 'freelancer', TRUE);
                $this->mapper(&$row, 'employer', TRUE);
            }
            
            return $row;
        }




        /**
         * �������� ������ ����������� ������
         * 
         * @todo: ���������� �� ��������� ��������������� - �� ����� ��������� ������� ��� ������ ��������� ������ 
         * a ����� ���� ������� ������ ������ � ���.������ ������������ �������� ��� � ��������� ��� � ������.
         * ����� ������� �� �������� join � union ����!
         * 
         * @param type $order_id
         * @return array ������������� ������
         */
        public function getFiles($order_id) {
            $sql = "
                SELECT 
                    f.*,
                    0 AS doc_type
                FROM {$this->TABLE_ORDER_MSG_FILES} AS f 
                INNER JOIN {$this->TABLE_ORDER_MSG} AS tm ON f.src_id = tm.id 
                WHERE tm.order_id = ?i 
                
                /*
                
                UNION ALL
                
                SELECT 
                    f.*,
                    0 AS doc_type
                FROM {$this->TABLE_ORDER_FILES} AS f
                WHERE f.src_id = ?i
                
                */
                
                UNION ALL
                
                SELECT 
                    f.*
                FROM file_reserves_order AS f
                WHERE f.src_id = ?i

                ORDER BY modified DESC
            ";
            
            $files = $this->db()->rows($sql, $order_id, $order_id, $order_id);
            return $files;
        }
        
        
        
        /**
         * �������� �� ���� ��������� ������
         * 
         * @param type $order_id
         * @param type $uid
         * @return type
         */
        public function isOrderMember($order_id, $uid)
        {
            return (bool)$this->db()->val("
                SELECT o.id
                FROM {$this->TABLE} AS o
                WHERE 
                    o.id = ?i AND 
                    (o.frl_id = ?i OR o.emp_id = ?i)
                LIMIT 1
            ", $order_id, $uid, $uid);       
        }



        /**
         * ���� ���� ���� �����?
         * 
         * @global type $DB
         * @param int $uid
         * @return int
         */
        public function isExist($uid)
        {
            return $this->db()->val("
                SELECT o.id
                FROM {$this->TABLE} AS o
                WHERE (o.frl_id = ?i OR o.emp_id = ?i)
                LIMIT 1
            ",$uid,$uid);
        }
        
        

        
        
        
        public function isExistByType(
                $src_id, 
                $frl_id,
                $type = self::TYPE_TSERVICE, 
                $status = self::STATUS_NEW)
        {
            return $this->db()->val("
                SELECT id
                FROM {$this->TABLE}
                WHERE 
                    tu_id = ?i AND 
                    frl_id = ?i AND 
                    type = ?i AND 
                    status >= ?i
            ",
                $src_id,
                $frl_id,
                $type,
                $status
            );
        }

        





        /**
         * �������� ������ ������
         * 
         * @param type $order_id
         * @param type $uid
         * @return type
         */
        public function getStatus($order_id, $uid)
        {
            return $this->db()->row("
                SELECT 
                    o.status,
                    o.close_date
                FROM {$this->TABLE} AS o
                WHERE 
                    (o.frl_id = ?i OR o.emp_id = ?i)
                    AND o.id = ?i
                LIMIT 1
            ", $uid, $uid, $order_id);
        }

        



        /**
         * �������� ������ �� ������ ��
         * 
         * @param type $service_id - ID ��
         * @return boolean
         */
        public function create($service_id)
        {
            if(!$data = $this->_prepare($service_id)) return FALSE;
            if(!$id = $this->db()->insert($this->TABLE, $data, 'id')) return FALSE;
            $this->order['id'] = $id;
            
            //@todo: � ���������� �� ���� �������� ������ paytype ���������� �����
            // ������� ������ � ������� ����� � ����� ��.
            
            //��������� �������� � �������
            $history = new tservices_order_history($id);
            $history->save($this->order);
            
            //�������� ��� �������� ����� ������� � ����������
            $this->clearCountEvent($this->order['frl_id']);
            return $this->order;
        }

        
        
        /**
         * �������� ����� � ���������� ������� �������
         * 
         * @param array $data
         * @return boolean
         */
        public function add($data)
        {
            if(!$id = $this->db()->insert($this->TABLE, $data, 'id')) return FALSE;
            $data['id'] = $id;
            $this->order = array_merge($this->order,$data);
            
            //��������� �������� � �������
            $history = new tservices_order_history($id);
            $history->save($this->order);
            
            //�������� ��� �������� ����� ������� � ����������
            $this->clearCountEvent($this->order['frl_id']);
            
            return $this->order;
        }

        
        /**
         * �������� ������������ �����
         * 
         * @param type $data
         * @return type
         */
        public function createPersonal($data)
        {
            $data['type'] = self::TYPE_PERSONAL;
            $data['tu_id'] = 0;
            $data['category_id'] = 0;
            return $this->add($data);
        }

        
        /**
         * ������� ����� �� ���� �������
         * 
         * @param type $data
         * @return type
         */
        public function createFromProject($data)
        {
            $data['type'] = self::TYPE_PROJECT;
            $data['category_id'] = 0;
            return $this->add($data);
        }

        






        /**
         * �������������� ���� � ����� ������
         * 
         * @param type $uid
         * @return type
         */
        public function edit($orderid, $data = array(), $tax = 0) 
        {
            if(empty($data)) return false;
            //���� ���� �������� ������� ��
            //� ��������� �� �������� ������
            if(isset($data['order_price'])) 
            {
                $data['tax'] = $tax;
                $data['tax_price'] = floor($data['order_price'] * $tax);
            }

            $data['date_emp_last'] = 'NOW()';
            
            return $this->db()->update($this->TABLE, $data, 'id = ?i', $orderid);
        }
        
        
        
        
        
        /**
         * �������� ����� � ����� ����������� �� �������� �� ������
         * 
         * @param type $uid
         */
        public function clearDebt($uid)
        {
            
            $list = $this->db()->row('SELECT * FROM tservices_orders_debt WHERE user_id = ?i', $uid); 
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");                                                                                                                                                                                                                                  
            $log = new log('debug/0029177-%d%m%Y.log'); 
            $log->writeln('clearDebt----------- ' . date('d.m.Y H:i:s'));
            $log->writedump($uid);
            $log->writedump($list);
            
            //���� ���� ������ �� �� ��� �������� �������� ������ ������, ��
            //o�������� ����� �������� �������� ���������� �������� (��� �� �����/����) ��� ������ ��    
            $res = $this->db()->query("
                UPDATE ".tservices::$_TABLE." AS s SET
                    payed_tax = tservices_catalog_payed_tax(id, user_id),
                    tax_payed_last = NOW()
                FROM (
                    SELECT 
                        DISTINCT o.tu_id
                    FROM {$this->TABLE} AS o
                    INNER JOIN {$this->TABLE_DEBT} AS d ON d.order_id = o.id OR o.id = ANY(d.order_ids)
                    WHERE d.user_id = ?i AND o.frl_id = ?i AND o.type = ?i                    
                ) AS sq
                WHERE s.id = sq.tu_id AND s.user_id = ?i 
                
                RETURNING tax_payed_last
            ", $uid, $uid, self::TYPE_TSERVICE, $uid);
            
                    
            if ($res) {
                $row = pg_fetch_row($res);
                $log->writedump($row);
            } else {
                $log->writedump($res);
            }
                    
            
            //������ ����� � �����        
            return $this->db()->query('DELETE FROM tservices_orders_debt WHERE user_id = ?', $uid);        
        }



        
        /**
         * �������� �������� �� ������������ 
         * ��������� �� �������� ������� ��
         * 
         * @param type $uid
         * @return type
         */
        public function isDebt($uid)
        {
            return $this->db()->row("
                SELECT 
                    date,
                    (date::DATE < NOW()::DATE) AS is_blocked
                FROM {$this->TABLE_DEBT}
                WHERE 
                    user_id = ?i
                LIMIT 1
            ",$uid);
        }

        

        /**
         * ���������� ���� ���������� ������
         * 
         * @global type $DB
         * @param int $order_id - ID ������
         * @param bool $is_emp - ��� �����
         * @param bool $touch_read - �������� ��� �� �����������
         * @return type
         */
        public static function touchOrder($order_id, $is_emp = true, $touch_read = false)
        {
            global $DB;
            
            $prefix = ($is_emp)?'emp':'frl';
            $anti_prefix = ($is_emp)?'frl':'emp';
            
            return $DB->query("
                UPDATE ".self::$_TABLE." SET 
                    date_{$prefix}_last = NOW()
                    ".(($touch_read)?",{$anti_prefix}_read = FALSE":"")."    
                WHERE id = ?i", 
            $order_id);
        }





        /**
         * ������� ������ � ������ ��������� �������
         * 
         * @param int $order_id - ID ������
         * @param string $new_status - ��������� ��������� ������ �������
         * @param boolean $is_emp - ���� �����
         * @param boolean $fb_rating - ������ ������ (���� ����)
         * @return boolean | int - ����� ������
         */
        public function changeStatus($order_id, $new_status, $is_emp, $fb_rating = 0)
        {
            if (empty($this->order)) {
                return FALSE;
            }
            
            $next = ($is_emp)?$this->STATUS_EMP_NEXT:$this->STATUS_FRL_NEXT;
            $current_status = $this->order['status'];
            $list = $this->STATUS_LIST;

            if(!isset($list[$new_status]) || 
               !isset($next[$current_status]) || 
               !in_array($list[$new_status], $next[$current_status])) return FALSE;
            
            $tservices_smail = new tservices_smail();
            
            $status = $list[$new_status];
            $time = time();
            $data = array(
                'status' => $status,
                'date_'.($is_emp?'emp':'frl').'_last' => date('Y-m-d H:i:s', $time)
            );
            
            //������� ������ �� ������ ����� ������������������ � ��� ������������
            //��� ����������� ��������� ����� �������
            $history = new tservices_order_history($order_id);
            
            switch($status)
            {   
                //�������� �������� ����������
                case self::STATUS_CANCEL:
                    
                    //���� ���� ������ �� ������� �������� � ���
                    if ($this->isReserve()) {
                        $done = $this->getReserve()->changeStatus(ReservesModel::STATUS_CANCEL);
                        //������!
                        if(!$done) {
                            return false;
                        }
                    }
                    
                break;
                
                //����������� �������� ����������
                case self::STATUS_DECLINE:
                    
                    //������ ���������� ���� ��� ��� 
                    //��������� ������ ������� ���������
                    if ($this->isReserve()) {
                        return false;
                    }
                    
                break;
                
                
                //�������� ������ �� ��������� (��������� ��������������)
                case self::STATUS_FIX:
                    //@todo: �� ������� ���� ��� ����
                    //$data['done_date'] = NULL;
                break;
                
                //����������� ���������� � ������
                case self::STATUS_ACCEPT:
                    
                    //���� ������������� ��� ������ � ��������
                    //�� ������ �� ������ � ���������� ����������� 
                    //��� ������� ����� ���������
                    if ($this->order['pay_type'] == self::PAYTYPE_RESERVE) {
                        
                        $reserve_data = $this->getReserve()->newReserve();
                        
                        if (empty($reserve_data)) {
                            return FALSE;
                        }
                        
                        //������ �� �������� �� STATUS_ACCEPT �������� STATUS_NEW
                        //�� ��������� ������ �� ������ �����
                        //� ��� ������ �� ��������� � ����������
                        
                        //@todo: �� reserve_data ����� ������� � ������ ������� reserve
                        $this->order['reserve_data'] = $reserve_data;
                        
                        $data['status'] = $current_status;
                        
                    } else {
                        //����� ������������� � ������ ������
                        $data['accept_date'] = date('Y-m-d H:i:s', $time);
                    }

                break;
                
                
                //����������� �������� � ����������
                case self::STATUS_FRLCLOSE:
                    //@todo: �� ������� ���� ��� ����
                    //$data['done_date'] = date('Y-m-d H:i:s', $time);
                break;
                
            
                //�������� ��������� ������ � ��������� �����
                case self::STATUS_EMPCLOSE:
                
                    $data['close_date'] = date('Y-m-d H:i:s', $time);
                    $this->order['close_date'] = $data['close_date'];
                    
                    if ($this->isNeedTax($order_id, $status, $fb_rating)) {

                        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");                                                                                                                                                                                                                                  
                        $log = new log('debug/0029177-%d%m%Y.log'); 
                        $log->writeln('isNeedTax----------- ' . date('d.m.Y H:i:s'));
                        $log->writedump($order_id);
                        
                        
                        $desc = sprintf(self::TAX_MSG, $this->order['id'], reformat(htmlspecialchars($this->order['title']), 30, 0, 1));
                        $acc_info = $this->chargeTax(
                                $this->order['frl_id'], 
                                $this->order['tax_price'], 
                                $desc
                        );

                        if (!$acc_info) {
                            return FALSE;
                        }
                        
                        
                        $log->writedump($acc_info);
                        
                        
                        $data['acc_op_id'] = $acc_info['op_id'];
                        $is_debt = ($acc_info['sum'] < 0);
                        
                        if ($is_debt) {
                            $debt_info = $this->isDebt($this->order['frl_id']);

                            if (!$debt_info) {
                                //������� ����� � ������������ ��
                                
                                $debt_timestamp = strtotime('+ '.(self::TAX_DEBT_PERIOD).' days', $time);
                                $ret = $this->db()->insert($this->TABLE_DEBT,array(
                                    'user_id' => $this->order['frl_id'],
                                    'order_id' => $this->order['id'],
                                    'date' => date('Y-m-d H:i:s', $debt_timestamp),
                                    'order_ids' => "{{$this->order['id']}}"
                                ));

                            } else {
                                //��������� ID ������ �� �������� ��� �� ���� �������������
                                
                                $ret = $this->db()->query("
                                    UPDATE {$this->TABLE_DEBT} SET
                                        order_ids = order_ids || ?i
                                    WHERE user_id = ?i
                                ", $this->order['id'], $this->order['frl_id']);

                                $debt_timestamp = strtotime($debt_info['date']);
                            }
                            
                            if (!$ret) {
                                return FALSE;
                            }  
                        
                            
                            //��������
                            //$_debt_info = $this->isDebt($this->order['frl_id']);
                            $_dump = $this->db()->row('SELECT * FROM tservices_orders_debt WHERE user_id = ?i', $this->order['frl_id']); 
                            $log->writedump($_dump);
                            
                            
                            $tservices_smail->attributes(array('debt_timestamp' => $debt_timestamp));
                            
                        } elseif ($this->order['type'] == self::TYPE_TSERVICE) {
                            //���� ������������� �� ��������� � ����� �� ��, �� ��������� ���� ���������� �������� ��������
                            $this->db()->query("
                                UPDATE tservices SET
                                    tax_payed_last = NOW()
                                WHERE id = ?i AND user_id = ?i
                            ", 
                            $this->order['tu_id'], 
                            $this->order['frl_id']);
                        }
  
                    }   

                break;
            }

            $ret = $this->db()->update($this->TABLE, $data, 'id = ?i', $order_id);
            
            if ($ret) {
                $this->order = array_merge($this->order,$data);
                $this->afterChangeStatus($status);
                
                //��������� �������� � �������
                $history->saveStatus($status);
            
                //����������� �� ������� �� ����, ���������� � �������
                $tservices_smail->attributes(array('order' => $this->order, 'is_emp' => $is_emp));
                $tservices_smail->changeOrderStatus($status);
                
                //������ ��� ���-�� ����� ������� � ����� ��� ��������� �������
                $prefix = ($is_emp)?'frl':'emp';
                $this->clearCountEvent($this->order["{$prefix}_id"]);
            }
            
            return $status;
        }

        
        /**
         * ��������� ������� ����� �������� ����� �������
         * 
         * @param type $new_status
         */
        protected function afterChangeStatus($new_status)
        {
            switch($new_status)
            {
                case self::STATUS_EMPCLOSE:
                    
                    if($this->order['pay_type'] == self::PAYTYPE_RESERVE)
                    {
                        //���� ����� ����������� �� �� ������� ���������
                        
                        if(!$this->order['reserve']->isArbitrage())
                        {
                            //�� �������� ������ ������� ��� �����������
                            $this->order['reserve']->changePayStatus(ReservesModel::SUBSTATUS_NEW);

                            //���������� ���������
                            require_once(ABS_PATH . '/classes/DocGen/DocGenReserves.php');
                            try {
                                $doc = new DocGenReserves($this->order);
                                $doc->generateActCompletedFrl();
                            } catch(Exception $e) {
                                require_once(ABS_PATH . '/classes/log.php');
                                $log = new log('reserves_docs/' . SERVER . '-%d%m%Y.log', 'a', "%d.%m.%Y %H:%M:%S: ");
                                $log->writeln(sprintf("Order Id = %s: %s", $this->order['id'], iconv('CP1251','UTF-8',$e->getMessage())));
                            }
                        }
                    }
                    
                    
                    //���� ����� �� �� �� ������������� ���� �������� �� ��������
                    if ($this->order['acc_op_id'] > 0 && 
                        $this->order['type'] == self::TYPE_TSERVICE) {
                        $this->db()->query("
                            UPDATE tservices SET
                                payed_tax = tservices_catalog_payed_tax(id, user_id)
                            WHERE id = ?i AND user_id = ?i
                        ", 
                        $this->order['tu_id'], 
                        $this->order['frl_id']);
                    }
                    
                    
                    //�������� ��� ������ �������� �������� ���������
                    require_once(ABS_PATH . '/classes/messages.php');
                    messages::setIsAllowed($this->order['emp_id'], $this->order['frl_id']);
                    
                    
                    break;
                
                
                
                case self::STATUS_CANCEL:
                case self::STATUS_DECLINE:
                    
                    //���� ����� � ����� �� ���� ������� �� ��������� � ���������
                    //����� ���������� ����������� � �������
                    if($this->order['type'] == self::TYPE_PROJECT)
                    {
                        $project_id = $this->order['tu_id'];
                        $exce_id = $this->order['frl_id'];
                        $emp_id = $this->order['emp_id'];

                        $projectOffers = new projects_offers();
                        $offer_id = $projectOffers->getOfferIDByProjectID($project_id, $exce_id);
                        
                        if($offer_id > 0)
                        {
                            $projects = new projects();
                            $projects->ClearExecutor($project_id, $emp_id);
                            $projectOffers->SetSelected($offer_id, $project_id, $exce_id, true);
                        }
                    }
                    
                break;
            }
        }








        /**
         * ����� �� ��������� �������� �� �����
         * 
         * @param type $param
         * @return boolean - ���������� true, ���� �����, ��� false, ���� �� �����
         */
        function isNeedTax($order_id, $status, $feedback_rating) {
            
            $angry_emp = $status == self::STATUS_EMPCLOSE && $feedback_rating < 0;
            
            $already_paid = isset($this->order['acc_op_id']) && $this->order['acc_op_id'] > 0;
            
            $is_tax = isset($this->order['tax_price']) && $this->order['tax_price'] > 0;
            
            if (!$order_id || $angry_emp || $already_paid || !$is_tax) {
                return false;                
            }
            
            return true;
        }
        
        
        /**
         * �������� �������� ������ ��� �����
         * 
         * @param int $uid - ID ����� (�� ������ �������� �� ����������)
         * @param int $tax_price - ����� ��������
         * @param string $desc - �������� ��������
         * @return int - ���������� account_operations ID ��������
         */
        public function chargeTax($uid, $tax_price, $desc)
        {
            $account = new account();
            if(!$account->GetInfo($uid, true) || !$tax_price) return FALSE;
            
            $tax_price = -abs($tax_price);
            $op_id = NULL;

            $account->depositEx2(
                    &$op_id, 
                    $account->id, 
                    $tax_price, 
                    $desc, 
                    $desc, 
                    134, //�������� ����� �� - ������ �� ���� op_codes
                    $tax_price, 
                    NULL, 
                    date('c')
               );
            
            if ($uid == get_uid(false)){
                $_SESSION['ac_sum'] = $account->sum;
            } else {
                $membuff = new memBuff();
                $expires = 30 * 24 * 60 * 60; //30 ����
                $membuff->set('ac_sum_update_'.$uid, $account->sum, $expires);
            }
            
            return ($op_id)?array(
                'op_id' => $op_id,
                'sum' => $account->sum
                ):FALSE;
        }

        /**
         * ������� ������������ ��������
         * 
         * @param int $order_id ID ������
         */
        /*
        function cancelTax($order_id) {
            //TODO ����������� �����
        }
        */
        
        
        
        /**
         * ��������� ������ ��� ����������� ���������
         * � ��������� ������ ������ � ����� �� �������������.
         * ���������� 32 ���������� ��� ��� ������ ����:
         * /tu/new-order/5fb9d075b6979c3ee66a887043ff3fae/
         * 
         * @param array $data
         * @return boolean | md5 hash 
         */
        public function newOrderActivation($data = array())
        {
            if(!isset($data['email'])) return FALSE;
            $data['code'] = md5( self::SOLT . serialize($data) . uniqid(mt_rand(), TRUE) );
            if(!isset($data['options']) || empty($data['options'])) unset($data['options']);
            else {
                $options = $data['options'];
                //��������� �������
                foreach($options as $key => $value){$options['order_' . $key] = $value;unset($options[$key]);}
                $data['options'] = serialize($options);
            }
            return $this->db()->insert($this->TABLE_ACTIVATE,$data,'code');
        }

        
        /**
         * �������� ���������� ��������� ���������
         * 
         * @param md5 string $code
         * @return array
         */
        public function getOrderActivation($code)
        {
            if(empty($code)) return FALSE;
            
            $row = $this->db()->row("
                SELECT *
                FROM {$this->TABLE_ACTIVATE} 
                WHERE code = ? 
                LIMIT 1
            ",$code);
            
            if($row)
            {
                $row['options'] = ($row['options'])?mb_unserialize($row['options']):array();
            }
                
            return $row;   
        }

        
        /**
         * ������� ��������� ���������
         * 
         * @param md5 string $code
         * @return boolean
         */
        public function deleteOrderActivation($code)
        {
            return $this->db()->val("
                DELETE FROM {$this->TABLE_ACTIVATE}
                WHERE code = ?
            ",$code);
        }

        
        
        
        
        
        /**
         * �������, �� ���������, �������� ������ � ������� ���-���� �� ��������� �� ������� �����
         * �� 24 � 72 ���� ��������������.
         * 
         * @param int $page
         * @param int $offset
         * @return array
         */
        public function getNoneFeedbackOrders($hours = 24, $page = 1, $offset = 200)
        {
            $from = $offset;
            $to = ($page-1)*$offset;            
            
            $where_sql ="
                WHERE 
                    o.status = " . self::STATUS_EMPCLOSE . " AND 
                    (ra.id IS NULL OR ra.allow_fb_frl = TRUE OR ra.allow_fb_emp = TRUE) AND 
                    (o.frl_feedback_id IS NULL OR o.emp_feedback_id IS NULL) AND 
                    (o.close_date BETWEEN to_char(now() - '{$hours} hours'::interval,'YYYY-MM-DD HH24:00:00')::timestamp AND
                                    to_char(now() - '{$hours} hours'::interval,'YYYY-MM-DD HH24:59:59.999999')::timestamp)
                ORDER BY o.id            
                LIMIT {$from} OFFSET {$to}";
                
            $select_sql = "
            	COALESCE(ra.allow_fb_emp,true) AS allow_fb_emp,
                COALESCE(ra.allow_fb_frl,true) AS allow_fb_frl,
            ";
            
            $join = "
                LEFT JOIN " . ReservesArbitrage::$_TABLE .  " AS ra ON ra.reserve_id = r.id 
            ";    
                
            $sql = $this->getBaseSqlWithReserve($where_sql, $select_sql, $join);
            $res = $this->db()->query($sql); 
            $ret = pg_fetch_all($res); 
        
            return $ret;            
        }


        /**
         * �������, �� ���������, �� ��������* ������ �� 24 � 72 �����.
         * * �� �������� - ��� � ������� �� ��������� ����� ������ �������� � �������� ���������.
         * 
         * @param int $page
         * @param int $offset
         * @return array
         */
        public function getInactiveOrders($hours = 24, $page = 1, $offset = 200)
        {
            $from = $offset;
            $to = ($page-1)*$offset;

            $where_sql ="
                WHERE 
                r.id IS NULL AND 
                o.status = ".self::STATUS_NEW." AND
                (o.date BETWEEN to_char(now() - '{$hours} hours'::interval,'YYYY-MM-DD HH24:00:00')::timestamp AND 
                                to_char(now() - '{$hours} hours'::interval,'YYYY-MM-DD HH24:59:59.999999')::timestamp)
                ORDER BY o.id            
                LIMIT {$from} OFFSET {$to}";
            
            $sql = $this->getBaseSqlWithReserve($where_sql);
            $res = $this->db()->query($sql);          
            $ret = pg_fetch_all($res); 
        
            return $ret;   
        }


        
        /**
         * ������� ������ ��� ����������� � �������� �� � ������������ � �� (�������� �������)
         * 
         * @param type $where_sql
         * @param type $select_sql
         */
        protected function getBaseSqlWithReserve($where_sql, $select_sql = '', $join = '')
        {
            $_select_freelancer_sql = 
            $this->addMapper('freelancer', array(
                    'uid' => 'frl_uid',
                    'login' => 'frl_login',
                    'uname' => 'frl_uname',
                    'usurname' => 'frl_usurname',
                    'email' => 'frl_email',
                    'is_banned' => 'frl_is_banned',
                    'self_deleted' => 'frl_self_deleted'
                 ),'u.');

             $_select_employer_sql = 
             $this->addMapper('employer', array(
                    'uid' => 'emp_uid',
                    'login' => 'emp_login',
                    'uname' => 'emp_uname',
                    'usurname' => 'emp_usurname',
                    'email' => 'emp_email',
                    'is_banned' => 'emp_is_banned',
                    'self_deleted' => 'emp_self_deleted'
                 ),'e.');
             
            $sql = "
                SELECT 
                    {$select_sql}
                    o.*,
                    {$_select_freelancer_sql},
                    {$_select_employer_sql}
                FROM {$this->TABLE} AS o
                INNER JOIN {$this->TABLE_FREELANCER} AS u ON u.uid = o.frl_id 
                INNER JOIN {$this->TABLE_EMPLOYER} AS e ON e.uid = o.emp_id 
                LEFT JOIN " . ReservesModel::$_TABLE . " AS r 
                    ON r.src_id = o.id 
                       AND r.type = "  . ReservesModelFactory::TYPE_TSERVICE_ORDER . " 
                       AND o.pay_type = " . self::PAYTYPE_RESERVE . " 
                {$join}
                {$where_sql}
            ";            
            
            return $sql;
        }

        


        /**
         * ������� ����������� ������ ������� ������� ���� � �������
         * 
         * @param type $where_sql
         * @param type $select_sql
         * @return string
         */
        protected function getBaseSql($where_sql, $select_sql = '')
        {
            $fb_deleted = FALSE;
            
            $_select_freelancer_sql = 
            $this->addMapper('freelancer', array(
                    'uid' => 'frl_uid',
                    'login' => 'frl_login',
                    'uname' => 'frl_uname',
                    'usurname' => 'frl_usurname',
                    'email' => 'frl_email',
                    'is_banned' => 'frl_is_banned',
                    'self_deleted' => 'frl_self_deleted'
                 ),'u.');

             $_select_employer_sql = 
             $this->addMapper('employer', array(
                    'uid' => 'emp_uid',
                    'login' => 'emp_login',
                    'uname' => 'emp_uname',
                    'usurname' => 'emp_usurname',
                    'email' => 'emp_email',
                    'is_banned' => 'emp_is_banned',
                    'self_deleted' => 'emp_self_deleted'
                 ),'e.');

            $sql = "
                SELECT 
                    {$select_sql}
                    fb.feedback AS frl_feedback,
                    fb.rating AS frl_rating,
                    fb.posted_time AS frl_fb_posted_time,
                    fb.deleted AS frl_fb_deleted,
                    eb.feedback AS emp_feedback,
                    eb.rating AS emp_rating,
                    eb.posted_time AS emp_fb_posted_time,
                    eb.deleted AS emp_fb_deleted,
                    o.*,
                    {$_select_freelancer_sql},
                    {$_select_employer_sql}
                FROM {$this->TABLE} AS o
                INNER JOIN {$this->TABLE_FREELANCER} AS u ON u.uid = o.frl_id 
                INNER JOIN {$this->TABLE_EMPLOYER} AS e ON e.uid = o.emp_id 
                LEFT JOIN {$this->TABLE_FEEDBACK} AS fb 
                ON (fb.id = o.frl_feedback_id".(!$fb_deleted?" AND fb.deleted = FALSE)":")")." 
                LEFT JOIN {$this->TABLE_FEEDBACK} AS eb 
                ON (eb.id = o.emp_feedback_id".(!$fb_deleted?" AND eb.deleted = FALSE)":")")."
                {$where_sql}
            ";            
            
            return $sql;
        }

        
        /**
         * �������� ��� ���-�� ����� ������� ������
         * 
         * @param int $user_id
         * @return boolean
         */
        public function clearCountEvent($user_id)
        {
            if(!$this->_membuff) $this->_membuff = new memBuff();
            
            $re = $this->_membuff->delete(self::MEMCACHE_EVENT_CNT_KEY_PREFIX . $user_id);
            $rl = $this->_membuff->delete(self::MEMCACHE_LAST_EVENT_ORDER_ID_KEY_PREFIX . $user_id);
            
            return $re && $rl;
        }

        
        /**
         * �������� ���-�� ����� ������� � ������� ��
         * 
         * @param int $user_id
         * @param boolean $is_emp
         * @return int
         */
        public function getCountEvents($user_id, $is_emp = TRUE)
        {
            if(!$this->_membuff) $this->_membuff = new memBuff();
            $count = $this->_membuff->get(self::MEMCACHE_EVENT_CNT_KEY_PREFIX . $user_id);
            if (is_array($count)) $count = FALSE;
            
            if ($count === FALSE) 
            {
                $prefix = ($is_emp)?'emp':'frl';
                $anti_prefix = ($is_emp)?'frl':'emp';
                
                $sql = "
                    SELECT SUM(CASE WHEN o.{$prefix}_read = false THEN 1 ELSE 0 END) AS cnt
                    FROM {$this->TABLE} AS o
                    INNER JOIN {$this->TABLE_USERS} AS u ON u.uid = o.{$anti_prefix}_id
                    WHERE 
                        u.is_banned = B'0' AND 
                        u.self_deleted = FALSE AND
                        o.{$prefix}_id = ?
                ";
                
                $count = $this->db()->val($sql, $user_id);
                $this->_membuff->set(
                        self::MEMCACHE_EVENT_CNT_KEY_PREFIX . $user_id, 
                        $count, 
                        self::MEMCACHE_EVENT_CNT_TTL, 
                        self::MEMCACHE_EVENT_CNT_TAG_KEY);
            }
            
            return $count;
        }

        
        /**
         * �������� URL ���������� ������ � ������� ���� 
         * �� ���������� ������� ��������� ������� ��� �����
         * 
         * @param int $user_id
         * @param boolean $is_emp
         * @return string
         */
        public function getLastEventOrderURL($user_id, $is_emp = TRUE)
        {
            if(!$this->_membuff) $this->_membuff = new memBuff();
            $order_id = $this->_membuff->get(self::MEMCACHE_LAST_EVENT_ORDER_ID_KEY_PREFIX . $user_id);
            if (is_array($order_id)) $order_id = FALSE;
            
            if ($order_id === FALSE) 
            {
                $prefix = ($is_emp)?'emp':'frl';
            
                $sql = "
                    SELECT o.id
                    FROM {$this->TABLE} AS o
                    WHERE 
                        o.{$prefix}_read = FALSE AND
                        o.{$prefix}_id = ?
                    ORDER BY o.date DESC
                    LIMIT 1        
                ";
                        
                 $order_id = $this->db()->val($sql, $user_id);
                 $this->_membuff->set(
                        self::MEMCACHE_LAST_EVENT_ORDER_ID_KEY_PREFIX . $user_id, 
                        $order_id, 
                        self::MEMCACHE_EVENT_CNT_TTL, 
                        self::MEMCACHE_EVENT_CNT_TAG_KEY);
            }
            
            return ($order_id > 0)?tservices_helper::getOrderCardUrl($order_id):'';
        }

        
        
        /**
         * �������� ������ ��� �����������
         * 
         * @param int $user_id
         * @param array | int $order_ids
         * @param boolean $is_emp
         * @return boolean
         */
        public function markAsReadOrderEvents($user_id, $order_ids = array(), $is_emp = TRUE)
        {
            if(empty($order_ids)) return FALSE;
            $order_ids = (!is_array($order_ids))?array($order_ids):$order_ids;
            
            $prefix = ($is_emp)?'emp':'frl';
            $ret = $this->db()->update($this->TABLE,array(
                "{$prefix}_read" => TRUE
            ),'id IN(?l)', $order_ids);

            if($ret) $this->clearCountEvent($user_id);   
        }
        
        
        /**
         * ��������� ������� ���������������� �������
         * 
         * @param int $uid �� ����������
         * 
         * @return bool
         */
        public function checkNewOrders($uid) 
        {
           $isExist = $this->db()->val("
               SELECT 1 
               FROM {$this->TABLE} AS o
               LEFT JOIN " . ReservesModel::$_TABLE . " AS r 
                   ON r.src_id = o.id 
                      AND r.type = ?i 
                      AND o.pay_type = ?i 
               WHERE r.id IS NULL AND o.frl_id = ?i AND o.status = ?i 
           ", 
           ReservesModelFactory::TYPE_TSERVICE_ORDER, 
           self::PAYTYPE_RESERVE,
           $uid, 
           self::STATUS_NEW);

           return (bool)$isExist;
        }

        

        public function getOrderData()
        {
            return $this->order;
        }

        

        public function isFrlFeedback()
        {
            return !empty($this->order['frl_feedback']);
        }

        
        public function isStatusEmpClose()
        {
            return @$this->order['status'] == self::STATUS_EMPCLOSE;
        }


        public function isStatusWork()
        {
            return in_array(@$this->order['status'], array(
                self::STATUS_ACCEPT,
                self::STATUS_FRLCLOSE,
                self::STATUS_FIX
            ));
        }

        
        public function isReserve()
        {
            return @$this->order['pay_type'] == self::PAYTYPE_RESERVE && 
                   isset($this->order['reserve'], 
                         $this->order['reserve_data']);
        }

        
        public function isPayTypeReserve()
        {
            return @$this->order['pay_type'] == self::PAYTYPE_RESERVE;
        }
        
        public function isPayTypeDefault()
        {
            return @$this->order['pay_type'] == self::PAYTYPE_DEFAULT;
        }

        public function getReserve()
        {
            return $this->order['reserve'];
        }


        public function isOwner($uid)
        {
            return !empty($this->order) && in_array($uid, array(
                @$this->order['frl_id'], 
                @$this->order['emp_id']));
        }

        
        public function getId()
        {
            return $this->order['id'];
        }

        
        public function getPrice()
        {
            return $this->order['order_price'];
        }        
        
        
        public function getEmpId()
        {
            return $this->order['emp_id'];
        }

        
        public function getFrlId()
        {
            return $this->order['frl_id'];
        }

        

        /**
         * ��������� ���������� � �������� ����
         * ������ �� ��������� � ����� ��� � ������
         * 
         * @return boolean
         */
        public function isAllowArbitrageNew()
        {
            if(!$this->isReserve()) {
                return false;
            }

            return $this->isStatusWork() && 
                   $this->getReserve()->isAllowArbitrageNew();
        }
        
        
        /**
         * ��������� ������� ���������
         *
         * @return boolean
         */
        public function isArbitrage()
        {
            
            if(!$this->isReserve()) {
                return false;
            }
            
            return $this->getReserve()->isArbitrage();
        }
        
        /**
         * ��������� ������� ��������� ���������
         *
         * @return boolean
         */
        public function isArbitrageOpen()
        {
            
            if(!$this->isReserve()) {
                return false;
            }
            
            return $this->getReserve()->isArbitrageOpen();
        }

        
        
        /**
         * ��������� ������ �� ���������� ������ (���� ������ ��������, 
         * ��� ����� �����)
         * 
         * @return boolean
         */
        public function isDisallowFeedback()
        {
            require_once(ABS_PATH . '/tu/models/TServiceOrderFeedbackModel.php');
            $is_allow_feedback = TServiceOrderFeedbackModel::isAllowFeedback($this->order['close_date']);
            
            
            return $this->isArbitrageOpen() || 
                   $this->isStatusEmpClose() && !$is_allow_feedback;
        }
        
        
        
        /**
         * ������������ ��� ������������ � ����� ������ ���� ����������� ����� �����
         * 
         * @global type $DB
         * @param type $frl_id
         * @param type $emp_id
         * @return type
         */
        public static function hasSuccessfulOrder($frl_id, $emp_id) 
        {
            global $DB;
            return $DB->val('
                SELECT 1 FROM ' . self::$_TABLE . ' 
                WHERE status = ?i AND frl_id = ?i AND emp_id = ?i 
                LIMIT 1', self::STATUS_EMPCLOSE, $frl_id, $emp_id);
        }

        











        /**
         * ������� ���� ����
        * @return TServiceModel
        */
        public static function model()
        {
            $class = get_called_class();
            return new $class;
        }

}