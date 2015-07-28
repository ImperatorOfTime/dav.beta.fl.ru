<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesAdmin.php');

class ReservesAdminReestrFacturaModel extends ReservesAdmin
{
    
    /**
     * ������� ������ ����������� �������� ������
     */
    protected $TABLE = 'file_reserves_factura';
    
    /**
     * ���������� ��������� ������ �������� ������
     */
    public $path = '/reserves/factura/';


    /**
     * �����������
     * ����������� ����������� �����
     */
    public function __construct()
    {
        $filename = $this->saveUploadedFile('file','csv');
        
        if ($filename) {
            $this->parseFile($filename);
        }
    }
    
    
    /**
     * ������ ������ � ���������� ����-�������
     * 
     * @param type $filename
     */
    public function parseFile($filename) 
    {
        //@todo: ��� �� ������� :(
        ini_set('max_execution_time', 300);
        //ini_set('memory_limit', '512M');
        
        $uri = WDCPREFIX_LOCAL . $this->path . $filename;
        
        $list = array();
        $ids = array();
        $handle = fopen($uri, 'r');
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			if ($data[0] == 'order_id' || count($data) != 7) {
				continue;
			} 
            
            //order_id;sf_num;sf_date;sf_summa;pp_num;pp_date;pp_type
            $res = array(
                'id' => $this->getOrderId($data[0]), //����� ������,
                'sf_num' => $data[1], //����� �����-�������
                'sf_date' => $data[2], //���� ����� �������
                'sf_summa' => $data[3], //����� ����� �������
                'pp_num' => $data[4], //����� ���������� ���������
                'pp_date' => $data[5], //���� ���� ���������� ���������
                'pp_type' => $data[6] //��� ���������� ��������� (������ ��� ����)
            );
            $ids[] = $res['id'];
            $list[] = $res;
        }        
        fclose($handle);

        if ($list) {
           
           $reserveModel = ReservesModelFactory::getInstance(
                   ReservesModelFactory::TYPE_TSERVICE_ORDER); 
           
           $empData = $reserveModel->getEmpByReserveIds($ids);

           foreach ($list as $key => $data) {

                if (!isset($empData[$data['id']])) {
                    continue;
                }

                $data['employer']['login'] = $empData[$data['id']]['login'];
                $data['employer']['uid'] = $empData[$data['id']]['uid'];
                
                $reserveModel->getReserve($ids[$key]);
                $data['employer']['reqv'] = $reserveModel->getEmpReqv();

                try {
                    $doc = new DocGenReserves($data);
                    $doc->generateFactura();
                } catch (Exception $e) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/log.php');
                    $log = new log('reserves_docs/' . SERVER . '-%d%m%Y.log', 'a', "%d.%m.%Y %H:%M:%S: ");
                    $log->writeln(sprintf("Order Id = %s: %s", $data['id'], iconv('CP1251','UTF-8',$e->getMessage())));
                }
           } 
        }
    }
    
    
    
    public function getReestrs() 
    {
        $sql = "SELECT * FROM {$this->TABLE} ORDER BY id DESC";
        $sql = $this->_limit($sql);
        $files = $this->db()->rows($sql);
        return $files;
    }
    
    public function getReestrsCount() 
    {
        $sql = "SELECT reltuples FROM pg_class WHERE oid = 'public.{$this->TABLE}'::regclass;";
        return $this->db()->val($sql);
    }
    
    
}