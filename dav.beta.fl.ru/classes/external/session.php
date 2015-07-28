<?php

require_once(ABS_PATH.'/classes/external/base.php');

/**
 * ������ ����������������� API-�������. �������� � �������.
 * �� ��������� ��������� � ��������� ������� ������ �����������.
 */
class externalSession extends externalBase {
    
    const MEM_TIME  = 7200; // ����� ����� ������ � �������
    const MEM_GROUP = 'EXTERNAL_SESSIONS';
    const CACHE_TIME = 360; // ������ ���������� ���������� ������. (�� �������: 300 ����������� 301-599, �.�. ������
                            // ����������� ������� ��� � 5 �����, �� �� ���� ������� ������ 300, ��� ��������� ��� 299.)

    private $_mb;

    /**
     * ������ ��������� ������ ������ -- ��, � ������� ����� ������ ������.
     * @var array
     */
    public $public = array (
        'id' => NULL,
        'login' => NULL,
        'uname' => NULL,
        'usurname' => NULL,
        'role' => NULL,
        'is_pro' => NULL,
        'sum' => NULL,
        'new_msgs' => NULL,
        'sbr_count' => NULL,
        'new_sbr_events' => NULL,
    );

    /**
     * ������� ������ ������ -- ��, � ������� ����� ������ ������ ������.
     * @var array
     */
    public $private = array (
        'uid' => NULL,
        'last_updated' => NULL
    );

    /**
     * ���������, ���������� �� ��������� ������ �� ����� ������.
     * @var boolean
     */
    public $is_updated = false;


    
    /**
     * ������ ������ �� ����, ���� ������ ������� �������������.
     * @param string $id   ������������� ������
     */
    function __construct($id = NULL) {
        $this->_mb = new memBuff();
        if($id)
            $this->read($id);
    }

    /**
     * �������� �������� ������:
     *   $this->prop -- �������� ����� �������� � $this->public
     *   $this->_prop -- �������� ����� �������� � $this->private
     * @param string $f   ��� ��������
     * @return mixed   ��������
     */
    function __get($f) {
        $d = $this->public;
        if($f[0]=='_') {
            $d = $this->private;
            $f = substr($f, 1);
        }
        return ( isset($d[$f]) ? $d[$f] : NULL );
    }

    /**
     * ���������� �������� ������:
     *   $this->prop = 1 -- �������� ����� ����������� � $this->public
     *   $this->_prop = 1 -- �������� ����� ����������� � $this->private
     * @param string $f   ��� ��������
     * @param mixed $v   ��������
     */
    function __set($f, $v) {
        $d = &$this->public;
        if($f[0]=='_') {
            $d = &$this->private;
            $f = substr($f, 1);
        } else {
            if($d[$f] !== $v)
                $this->is_updated = true;
        }
        $d[$f] = $v;
    }
    

    private function _uidMemKey($uid) {
        return self::MEM_GROUP.'-'.$uid;
    }

    /**
     * ������ ������ �� �������, ��������� ������, ���� �������, ��� ��� ��������.
     * @param string $id   ��. ������.
     */
    function read($id) {
        if($id) {
            if($data = $this->_mb->get($id)) {
                list($this->public, $this->private) = $data;
                if(time() - $this->_last_updated >= self::CACHE_TIME) {
                    $this->refresh();
                }
                return;
            }
            $this->error( EXTERNAL_ERR_SESSION_EXPIRED );
        }
    }
        
    /**
     * ��������� ������ � ������.
     */
    function write() {
        if($this->id) {
            $this->_mb->set($this->id, array($this->public, $this->private), self::MEM_TIME);
            $this->_mb->set($this->_uidMemKey($this->_uid), $this->id, self::MEM_TIME);
        }
    }

    /**
     * ���������� ������ �� ����.
     */
    function destroy($uid = NULL) {
        if($uid) {
            $muk = $this->_uidMemKey($uid);
            $this->id = $this->_mb->get($muk);
        } else {
            $muk = $this->_uidMemKey($this->_uid);
        }
        if($this->id) {
            $this->_mb->delete($this->id);
            $this->_mb->delete($muk);
            $this->id = NULL;
        }
    }

    /**
     * ��������� ������ ������.
     */
    function refresh() {
        if($this->id) {
            $this->destroy();
            $this->fill($this->_uid);
        }
    }

    /**
     * �������������� ��� ������ � ������.
     * @param users $user   ����������������� ������ users.
     */
    function fillU($user) {
        if(!$user->uid)
            $this->error( EXTERNAL_ERR_USER_NOTFOUND );
        if($user->is_banned)
            $this->error( EXTERNAL_ERR_USER_BANNED );
        if($user->active != 't')
            $this->error( EXTERNAL_ERR_USER_NOTACTIVE );

        $data = get_object_vars($user);
        unset($user);

        require_once(ABS_PATH.'/classes/sbr.php');
        require_once(ABS_PATH.'/classes/sbr_meta.php');
        require_once(ABS_PATH.'/classes/messages.php');
        require_once(ABS_PATH.'/classes/account.php');

        $is_emp = is_emp($data['role']);

        $data['id'] = md5(self::MEM_GROUP.uniqid($data['uid']));
        $data['new_msgs'] = messages::GetNewMsgCount($data['uid']);
        $data['role'] = (int)$is_emp;
        $data['is_pro'] = $this->pg2ex($data['is_pro'], EXTERNAL_DT_BOOL);

        $sbr_cls = $is_emp ? 'sbr_emp' : 'sbr_frl';
        $sbr = new $sbr_cls($data['uid'], $data['login']);
        $data['sbr_count'] = $sbr->getActivesCount();
        $data['new_sbr_events'] = sbr_meta::getNewEventCount($data['uid']);
        
        $account = new account();
        $account->GetInfo($data['uid']);
        $data['sum'] = $account->sum;

        foreach($this->public as $f=>$v)
            $this->$f = $data[$f];

        $this->_uid = $data['uid'];
        $this->_last_updated = time();
    }

    /**
     * �������������� ��� ������ � ������.
     * @param integer $uid   ��. ������������.
     */
    function fill($uid) {
        require_once(ABS_PATH.'/classes/users.php');
        $user = new users();
        $user->GetUserByUID($uid);
        $this->fillU($user);
    }


    /**
     * ��������� ������ �� ���������� ������.
     */
    function __destruct() {
        $this->write();
    }
}

