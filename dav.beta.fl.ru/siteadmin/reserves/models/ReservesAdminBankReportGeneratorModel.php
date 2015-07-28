<?php

//require_once 'Spreadsheet/Excel/Writer.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DocGen/Formatter/DocGenReservesFormatter.php');

/**
 * ���������� xls-���� ������ �� ��������
 */

class ReservesAdminBankReportGeneratorModel {

    const TPM_DIR = '/temp/upload/';
    
    private $temp_file_dir;
    private $path_payout = '/payout_summary/';
    
    protected $TABLE = 'file_reestr_1c';
    
    private $formatter;

    public function __construct()
    {
        $this->temp_file_dir = ABS_PATH . self::TPM_DIR;
        $this->formatter = new DocGenReservesFormatter();
    }

    /**
     * @deprecated since version 70171
     * �������� � Office 2010 - #0028695, ������ ���������� generate2
     * �������� ����� ��������� �����
     * @param type $payouts ������ � ������� �� ��������
     * @param type $paybacks ������ � ������� �� ���������
     * @return type
     */
    public function generate($payouts, $paybacks)
    {
        $file_name = $this->getTempFileName();
        
        $workbook = new Spreadsheet_Excel_Writer($this->temp_file_dir . $file_name);
        $workbook->setVersion(8);
        $worksheet = $workbook->addWorksheet('');
        $worksheet->setInputEncoding('windows-1251');

        $fmtT = &$workbook->addFormat(array(
            'VAlign' => 'top',
            'Align' => 'center',
            'Size' => '10',
            'Bold' => 1
        ));
        $fmtT->setTextWrap();
        
        $fmtM = &$workbook->addFormat(array(
            'VAlign' => 'top',
            'Align' => 'left',
            'Size' => '10'
        ));
        $fmtM->setTextWrap();

        $worksheet->write(0, 0, "�", $fmtT);
        $worksheet->write(0, 1, "����� ������", $fmtT);
        $worksheet->write(0, 2, "����������� (���)", $fmtT);
        $worksheet->write(0, 3, "����� �������", $fmtT);
        $worksheet->write(0, 4, "���������", $fmtT);
        $worksheet->write(0, 5, "�������� (���)", $fmtT);
        $worksheet->write(0, 6, "�������������� ������\n�����������", $fmtT);
        $worksheet->setRow(0, 30);
        
        $worksheet->setColumn(0, 0, 5);
        $worksheet->setColumn(1, 1, 20);
        $worksheet->setColumn(2, 2, 40);
        $worksheet->setColumn(3, 3, 25);
        $worksheet->setColumn(4, 4, 70);
        $worksheet->setColumn(5, 5, 40);
        $worksheet->setColumn(6, 6, 30);

        $n = 1;
        foreach ($payouts as $key => $el) {
            $reqv = $this->formatter->details(array('uid' => $el['frl_id'], 'email' => $el['email']));
            $lines = substr_count($reqv, "\n");
            $url = $this->getUrl($el['path'], $el['fname']);
            
            $worksheet->write($n, 0, ($key + 1), $fmtM);
            $worksheet->write($n, 1, $this->formatOrderName($el['order_id']), $fmtM);
            $worksheet->write($n, 2, $el['frl_fio'], $fmtM);
            $worksheet->write($n, 3, $el['price'], $fmtM);
            $worksheet->write($n, 4, $reqv, $fmtM);
            $worksheet->write($n, 5, $el['emp_fio'], $fmtM);
            $worksheet->writeUrl($n, 6, $url, $url, $fmtM);
            $worksheet->setRow($n, $lines * 12);
            $n++;
        }
        
        $n += 3;
        
        $worksheet->write($n, 0, "�", $fmtT);
        $worksheet->write($n, 1, "����� ������", $fmtT);
        $worksheet->write($n, 2, "����������� (���)", $fmtT);
        $worksheet->write($n, 3, "����� �������", $fmtT);
        $worksheet->write($n, 4, "���������", $fmtT);
        $worksheet->write($n, 5, "�������� (���)", $fmtT);
        $worksheet->write($n, 6, "����� �� ����������� ������������", $fmtT);
        $worksheet->setRow($n, 30);
        
        $n++;
        foreach ($paybacks as $key => $el) {
            $reqv = $this->formatter->details(array('uid' => $el['emp_id'], 'email' => $el['email']));
            $lines = substr_count($reqv, "\n");
            $url = $this->getUrl($el['path'], $el['fname']);
            
            $worksheet->write($n, 0, ($key + 1), $fmtM);
            $worksheet->write($n, 1, $this->formatOrderName($el['order_id']), $fmtM);
            $worksheet->write($n, 2, $el['frl_fio'], $fmtM);
            $worksheet->write($n, 3, $el['price'], $fmtM);
            $worksheet->write($n, 4, $reqv, $fmtM);
            $worksheet->write($n, 5, $el['emp_fio'], $fmtM);
            $worksheet->writeUrl($n, 6, $url, $url, $fmtM);
            $worksheet->setRow($n, $lines * 12);
            $n++;
        }
        
        $workbook->close();
        
        return $this->path_payout . $this->uploadFile($file_name);
    }
    
    /**
     * �������� ����� ��������� �����
     * @param type $payouts ������ � ������� �� ��������
     * @param type $paybacks ������ � ������� �� ���������
     * @return string ���� � �����
     */
    public function generate2($payouts, $paybacks)
    {
        $file_name = $this->getTempFileName();
        
        $xls = new PHPExcel();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        
        //����� ������ �������
        $row = 1;
        $sheet->setCellValueByColumnAndRow(0, $row, iconv("windows-1251", "utf-8", "�"));
        $sheet->setCellValueByColumnAndRow(1, $row, iconv("windows-1251", "utf-8", "����� ������"));
        $sheet->setCellValueByColumnAndRow(2, $row, iconv("windows-1251", "utf-8", "����������� (���)"));
        $sheet->setCellValueByColumnAndRow(3, $row, iconv("windows-1251", "utf-8", "����� �������"));
        $sheet->setCellValueByColumnAndRow(4, $row, iconv("windows-1251", "utf-8", "���������"));
        $sheet->setCellValueByColumnAndRow(5, $row, iconv("windows-1251", "utf-8", "�������� (���)"));
        $sheet->setCellValueByColumnAndRow(6, $row, iconv("windows-1251", "utf-8", "�������������� ������ �����������"));
        $sheet->getRowDimension($row)->setRowHeight(20);
        
        //���������� ������ �������
        foreach ($payouts as $key => $el) {
            $row++;
            
            $reqv = $this->formatter->details(array('uid' => $el['frl_id'], 'email' => $el['email']));
            $reqv = html_entity_decode(str_replace('\n', '\r', $reqv), ENT_QUOTES);
            $frl_fio = html_entity_decode($el['frl_fio'], ENT_QUOTES);
            $emp_fio = html_entity_decode($el['emp_fio'], ENT_QUOTES);
            $url = $this->getUrl($el['path'], $el['fname']);
            
            $sheet->setCellValueByColumnAndRow(0, $row, ($key + 1));
            $sheet->setCellValueByColumnAndRow(1, $row, iconv("windows-1251", "utf-8", $this->formatOrderName($el['order_id'])));
            $sheet->setCellValueByColumnAndRow(2, $row, iconv("windows-1251", "utf-8", $frl_fio));
            $sheet->setCellValueByColumnAndRow(3, $row, $el['price']);
            $sheet->setCellValueByColumnAndRow(4, $row, iconv("windows-1251", "utf-8", $reqv));
            $sheet->setCellValueByColumnAndRow(5, $row, iconv("windows-1251", "utf-8", $emp_fio));
            $sheet->setCellValueByColumnAndRow(6, $row, $url);
            $sheet->getRowDimension($row)->setRowHeight(15);
        }

        $row += 3;

        //����� ������ �������
        $sheet->setCellValueByColumnAndRow(0, $row, iconv("windows-1251", "utf-8", "�"));
        $sheet->setCellValueByColumnAndRow(1, $row, iconv("windows-1251", "utf-8", "����� ������"));
        $sheet->setCellValueByColumnAndRow(2, $row, iconv("windows-1251", "utf-8", "����������� (���)"));
        $sheet->setCellValueByColumnAndRow(3, $row, iconv("windows-1251", "utf-8", "����� �������"));
        $sheet->setCellValueByColumnAndRow(4, $row, iconv("windows-1251", "utf-8", "���������"));
        $sheet->setCellValueByColumnAndRow(5, $row, iconv("windows-1251", "utf-8", "�������� (���)"));
        $sheet->setCellValueByColumnAndRow(6, $row, iconv("windows-1251", "utf-8", "����� �� ����������� ������������"));
        $sheet->getRowDimension($row)->setRowHeight(20);
        
        //���������� ������ �������
        foreach ($paybacks as $key => $el) {
            $row++;
            
            $reqv = $this->formatter->details(array('uid' => $el['emp_id'], 'email' => $el['email']));
            $reqv = html_entity_decode(str_replace('\n', '\r', $reqv), ENT_QUOTES);
            $frl_fio = html_entity_decode($el['frl_fio'], ENT_QUOTES);
            $emp_fio = html_entity_decode($el['emp_fio'], ENT_QUOTES);
            $url = $this->getUrl($el['path'], $el['fname']);
             
            $sheet->setCellValueByColumnAndRow(0, $row, ($key + 1));
            $sheet->setCellValueByColumnAndRow(1, $row, iconv("windows-1251", "utf-8", $this->formatOrderName($el['order_id'])));
            $sheet->setCellValueByColumnAndRow(2, $row, iconv("windows-1251", "utf-8", $frl_fio));
            $sheet->setCellValueByColumnAndRow(3, $row, $el['price']);
            $sheet->setCellValueByColumnAndRow(4, $row, iconv("windows-1251", "utf-8", $reqv));
            $sheet->setCellValueByColumnAndRow(5, $row, iconv("windows-1251", "utf-8", $emp_fio));
            $sheet->setCellValueByColumnAndRow(6, $row, $url);
            $sheet->getRowDimension($row)->setRowHeight(15);
        }
        
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
        
        $objWriter->save($this->temp_file_dir . $file_name);
        
        return $this->path_payout . $this->uploadFile($file_name);
    }

    /**
     * ���������� ��� �����
     * @return string
     */
    private function getTempFileName()
    {
        $suffix = time();
        return "bank_report_{$suffix}.xls";
    }
    
    /**
     * ��������� ���� � ���� � ���������� ����������
     * @param type $filename
     * @return boolean
     */
    private function uploadFile($filename) {
        $file = array(
            'tmp_name' => $this->temp_file_dir . $filename,
            'size' => filesize($this->temp_file_dir . $filename),
            'name' => $filename
        );
        $cf = new CFile($file, $this->TABLE);
		if ($cf) {
			$cf->server_root = true;
			$cf->max_size = 104857600; //100Mb
			if($filename = $cf->MoveUploadedFile($this->path_payout, true, $filename)) {
				return $filename;
			}
		}
        return false;
    }
    
    /**
     * ����������� ����� ������
     * @param type $oid
     * @return type
     */
    protected function formatOrderName($oid) {
        return '��#'.str_pad($oid, 7, '0', STR_PAD_LEFT);        
    }
    
    /**
     * ���������� ������ ���� � ���������
     * @param type $dir
     * @param type $filename
     * @return type
     */
    private function getUrl($dir, $filename)
    {
        if ($dir && $filename) {
            return WDCPREFIX . '/'. $dir . $filename;
        }
    }

}
