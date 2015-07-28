<?php
/**
 * ������ ��� ��������� ������� "mail_simple" ���������� "mail_simple_cons".
 * ������ ������� ������������� ��� �������� ����������� �� e-mail ��
 * ������ ���� "��������� ��� ����� ������� � ��� ����� �������� ���������� �����������",
 * ��������, � ������, �����, �����������, ����� ���� ������� (����������� � �����, ��������)
 * ���������� �� �����, ��� �� 10 �����������.
 */
define('IS_PGQ', 1);

define('DEBUG_DAEMON', 0);
define('CONFIGURATION', dirname(__FILE__) . '/conf/mail_cons.php');
require(CONFIGURATION);
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pmail.php");

/**
 * �������� -- ���������� ������� ������� "mail_simple".
 * ������������ ������� ����� �������, �� ���� ����� ������� � ���,
 * ����� � process_event() ������� ��� ����������� ������ (���� -- ��. ���������)
 * � ������ $_lastBatch['���_������'], (� �������� ����� ������ ����� ������������
 * ��� ������ ������ smail, ����������� �� ��������� ������� � �������� �����������).
 * � ����� � finish_batch() ����� ����� ��������� ��� ��������� �� ���������.
 */
class PGQMailSimpleConsumer extends PGQConsumer
{
    /**
     * �������� � ���� ������ �� ������� ����� �������.
     * ������������ � NULL ����� ��������� �����.
     * 
     * @var array
     */
    private $_lastBatch = NULL;

    /**
     * ������ �������� ������ (��� ����������� ��������).
     * 
     * @var integer
     */
    private $_lastBatchSize = 0;
    
    
    private $_mainDb = NULL;


	/**
	 * ����� ����������� �� PGQConsumer � ����������� ��� start ��� restart �������.
	 */
	public function run() {
		if (DEBUG_DAEMON) {
			parent::run();
			return;
		}
		// $this->_reCreateCheck();
		parent::run();
	}
	
	/**
     * �������������� ����������� ��������� �� ������� $Config, ������������� �
     * ����� CONFIGURATION. ���������� ��� ������ ������, ��� �� ������� reload.
     */
    public function config()
    {
        unset($Config);
        if($this->log !== null)
            $this->log->notice("Reloading configuration (HUP) from '%s':", CONFIGURATION);
        global $Config;
        require CONFIGURATION;
        $this->loglevel = $Config["LOGLEVEL"];
        $this->logfile  = $Config["LOGFILE"];
        $this->delay    = $Config["DELAY"];
		$this->restart_events_interval = $Config["RESTART_EVENTS_INTERVAL"];
		$this->restart_events_count    = $Config["RESTART_EVENTS_COUNT"];
	}

    /**
     * ��������� ������ �������, ���������� �� PGOConsumer::process_event().
     * �������� ������ � $this->_lastBatch.
     */
    public function process_event(&$event)
    {
        if($event->type) {
            $this->_lastBatch[$event->type][] = count($event->data) > 1 ? $event->data : $event->data['id'];
            $this->_lastBatchSize++;
        }
        return PGQ_EVENT_OK;
    }

    /**
     * ��������� $this->_lastBatch � ���������� ������� �����.
     *
     * @overridden PGOConsumer::finish_batch()
     */
    protected function finish_batch($batch_id)
    {
        if($this->_lastBatch) {
            $this->log->notice('������� ����� (%d �������).', $this->_lastBatchSize);
            $sm = new pmail();
            $this->force_connect();
            
            foreach($this->_lastBatch as $sender=>$data) {
                // ������� �������� $sender: BlogNewComment, CommuneNewComment.
                if(!$data) continue;
                $this->log->notice('%s: %d ��������� �� �����.', $sender, count($data));
                $this->log->notice('%s: %d ����� ����������.', $sender, $sm->$sender($data, $this->pg_src_con));
            }
        }

        $this->_lastBatchSize = 0;
        $this->_lastBatch = NULL;

        return parent::finish_batch($batch_id);
    }
    
    
    protected  function force_connect() {
        global $DB;
        
        if (!$this->_mainDb) {
            //$this->log->notice('CONNECT: Force new connection to main DB');
            $this->_mainDb = $DB->connect(TRUE);
        }
        
        return $this->_mainDb;
    }
    
    
	/**
	 * ��� ������� ���������� ���������, ������� �� ��������� � ������� � ����� ������ pgq.
	 * ���� �������, �� ��������� �� �������� �� ���������� ������� "������������" ��������.
	 * ���� ���������� ��� ������� � ����������� �� ����������, �� ��� ���������.
	 *
	 * "�����������" �������� ���: ���� ����������� ���������� ������� � ������� ��������� �� ���������
	 * ������ $Config["RESTART_EVENTS_COUNT"] ��� ����� ����� ��������� ��������� �������� � ��������
	 * ��������� �� ��������� ������ $Config["RESTART_EVENTS_INTERVAL"]. ��������� conf/mail_cons.php
	 *
	 * ����� ����������� �� PGQConsumer � ����������� ��� start ��� restart �������.
	 */
    private function _reCreateCheck() {
		$restart = TRUE;
		if ($this->check()) {
		    if ($this->connect() === FALSE) return FALSE;
			$restart = FALSE;
			if ($this->restart_events_count > 0) {
				$sql = "
					SELECT
						COUNT(*)
					FROM
						pgq.event_template
					WHERE
						ev_time > (
							SELECT
								sub_active
							FROM
								pgq.subscription
							WHERE
								sub_queue = (SELECT queue_id FROM pgq.queue WHERE queue_name = '{$this->qname}')
						)
				";
				$res = pg_query($this->pg_src_con, $sql);
				$row = pg_fetch_row($res);
				$restart = ($row[0] > $this->restart_events_count);
			}
			if (!$restart && $this->restart_events_interval > 0) {
				$sql = "
					SELECT
						extract('epoch' from (NOW() - sub_active - interval '{$this->restart_events_interval} seconds'))
					FROM
						pgq.subscription
					WHERE
						sub_queue = (SELECT queue_id FROM pgq.queue WHERE queue_name = '{$this->qname}')
				";
				$res = pg_query($this->pg_src_con, $sql);
				$row = pg_fetch_row($res);
				if ($row[0] > 0) $restart = TRUE;
			}
			if ($restart) {
				$this->unregister();
				$this->drop_queue();
			}
		} else {
			if ($this->connect() === FALSE) return FALSE;
		}
		if ($restart) {
			$this->create_queue();
			$this->register();
			$sql = "
				UPDATE
					pgq.queue
				SET
					queue_ticker_max_count = 500,
					queue_ticker_max_lag = '00:00:05'::interval -- ���� ������, ����� ������ �������� ����������� :)
				WHERE 
					queue_name = '{$this->qname}'
			";
			pg_query($this->pg_src_con, $sql);
			$this->log->notice("mPGQMailSimpleConsumer.run(): ��������� {$this->cname} ������, ������� {$this->qname} �������.");
		} else {
			$this->log->notice("PGQMailSimpleConsumer.run(): �������� ������� {$this->qname} �� ���������.");
		}
		$this->disconnect();
    }

}

$daemon = new PGQMailSimpleConsumer('mail_simple_cons', 'mail_simple', $argc, $argv, PGQ_DB_CONN);
?>
