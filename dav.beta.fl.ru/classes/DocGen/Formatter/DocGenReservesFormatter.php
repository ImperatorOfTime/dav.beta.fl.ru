<?php

require_once('DocGenFormatter.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesHelper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_helper.php");

class DocGenReservesFormatter extends DocGenFormatter 
{
    const FIO_JURI_TML  = '%s (� ���� __________________________________________, ����������� �� ��������� ______________)';
    const FIO_JURI_TML2 = '%s �%s� (� ���� __________________________________________, ����������� �� ��������� ______________)';
    
    const NUM_TMP       = '��#%07d';
    
    //--------------------------------------------------------------------------
    
    /**
     * ���� ������ ������� 
     * ���������� ��� ���������
     */
    const TEXT3_0_TML   = '
3. ��������� ����������� ������ ��������� %s%s.

4. ������ ��������� ���������� ������� � � ������������� ���� � ������� ���������� ��� ����������.';
    
    /**
     * ���� ������ ����� ������� 
     * � ������� 100% ����� �����������
     */
    const TEXT3_1_TML   = '
3. ��������� ����������� ������ ��������� %s%s.';
    
    /**
     * ���� ������ ����� ������� � ���������� 
     * ����� ����� ������������ � ����������
     */
    const TEXT3_2_TML   = '
3. ��������� ����������� ������ � ������ ������������ �� ���������� �� ��������� ������ � ������������ ��������� � %s �� %s ��������� %s%s.';
    
    
    //--------------------------------------------------------------------------
    
    /**
     * ���� ������ ����� ������� 
     * � ������� 100% ����� �����������
     */    
    const TEXT5_0_TML = '1.2. ��������� ������ ���������, ������������� � ���������� �� ������ �����, �� �� ���� ��������� ����������� � ����������� �������� �� ���������� ������������, �������� �� ���� ���������� ��� ���������� � ������������ ���������� ���������� ������ �/��� �������� ������ ��� ������������� ������ ������� ����������� ������, � ������: ����������� �� ������������ ������  �� ��������� ����������� ������������ ������.';
    
    /**
     * ���� ������ ����� ������� � ���������� 
     * ����� ����� ������������ � ����������
     */
    const TEXT5_1_TML = '1.2. ��������� ������ ���������, ������������� � ���������� �� ������ �����, �� �� ���� ��������� ����������� � ����������� �������� �� ���������� ������������, �������� �� ���� ���������� ��� ���������� � ������������ ���������� ���������� ������ �/��� �������� ������ ��� ������������� ������ ������� ����������� ������, � ������: ����������� �� ������������ ������  �� ��������� ����������� ������������ ������;
';

    const TEXT6_1_TML = '1.3. ������ �� ���������� ������������ � ������������ ������� ���������� ������ ��� ��������� � �������� ��������� � (���) ����������� � �������, ��������������� �������� 6 ��������.
';
    
    /**
     * ���� ������ ����� ������� 
     * � �������� 100% ����� ���������
     */
    const TEXT5_2_TML = '1.2. ������ �� ���������� ������������ � ������������ ������� ���������� ������ ��� ��������� � �������� ��������� � (���) ����������� � �������, ��������������� �������� 6 ��������.';
    
    
    /**
     * ���� ������ ������� ���������� 
     * ��� ���������
     */
    const TEXT5_3_TML = '1.2. ��������� ������ ���������, ������������� � ���������� �� ������ �����, �� �� ���� ��������� ����������� � ����������� �������� �� ���������� ������������, �������� �� ���� ���������� ��� ���������� � ������������ ���������� ���������� ������ �/��� �������� ������ ��� ������������� ������ ������� ����������� ������, � ������: ����������� �� ������������ ������  �� ��������� ����������� ������������ ������.';


    //--------------------------------------------------------------------------

    
    
    /**
     * ���� ������ ����� ������� 
     * � ������� 100% ����� �����������
     */
    const TEXT4_0_TML = '� ����� � �������������� ����� ���������� � ������������ ����������� �������� � ������������ � �������� 6 �������� ������� ����������� ������ ������������ ���������� ������ ����������, ������������ ������� � ��������. �������� �� ��������� ������������ ������� ������� �������, ��� ��������� ������ ������������� ������������ �������, ���������� � �������� � ��� ����������� ��������� � ����, ������������ ����������� �������. ������ ������� ���� ���������� � ������ � ������������ ��������� � {$num_bs} �� {$date_close}. � ����� � ���� �� ��������� �.�. 2.2.2, 4.3., 6.9.1, 6.13 �������� �������� ��� ����� ��������� ��������� �������� �� ���� �������������, � ������: ��������� ����������� � ����������� �������� �� ������������� ������� ��������� ������ ����������� � ������� {$price}{$ndfl_txt}.';
    

    /**
     * ���� ������ ����� ������� 
     * � ���������� ����� ����� 
     * ������������ � ����������
     */
    const TEXT4_1_TML = '� ����� � �������������� ����� ���������� � ������������ ����������� �������� � ������������ � �������� 6 �������� ������� ����������� ������ ������������ ���������� ������ ����������, ������������ ������� � ��������. �������� �� ��������� ������������ ������� ������� �������, ��� ��������� ������ �������� ������������� ������������ �������, ���������� � �������� � ����������� ��������� � ����, ������������ � ����������� �������. ������ ������� ���� ���������� � ������ � ������������ ��������� � {$num_bs} �� {$date_close}. � ����� � ���� �� ��������� �.�. 2.2.2, 4.2., 4.4., 6.9.2, 6.14, 7.1. �������� �������� ��� ����� ��������� ��������� �������� �� ���� �������������, ��������� ��������� ����������� � ����������� ��������: 
� �� ������������� ������� ���������� ����������� ��������� ������ ����������� � ������� {$price}{$ndfl_txt}, 
� �� ������������� �������� ��������� ����� ����������������� ����� � ������� {$emp_price}.';
    
    
    /**
     * ���� ������ ����� ������� 
     * � �������� 100% ����� ���������
     */
    const TEXT4_2_TML = '� ����� � �������������� ����� ���������� � ������������ ����������� �������� ������� ����������� ������ ������������ ���������� ������ ����������, ������������ ������� � ��������. �������� �� ��������� ������������ ������� ������� �������, ��� ��������� ������ ��������� �� ������������� ������������ ������� � (���) �� ����������� � ����, ������������ � ����������� �������. ������ ������� ���� ���������� � ������ � ������������ ��������� � {$num_bs} �� {$date_close}. � ����� � ���� �� ��������� �.�. 6.9.3, 6.15, 7.1. �������� �������� ��������� �������� �� ���� �������������, � ������: ��������� ����������� � ����������� �������� �� ������������� �������� ����������������� ����� � ������� {$emp_price} ���������.';
    

    /**
     * ���� ������ ������� 
     * ���������� ��� ���������
     */
    const TEXT4_3_TML = '� ������������ � �.�. 2.2.2., 4.1.1, 4.3 �������� ����� ����, ��� �������� ������� �������� � ���������� ���������� ������ ������������ � ����� ���������� ����� ������ ������������ � ������� ����������-����������� ������� �����, ��������, �������� ��� ����� ���������, ��������� �������� �� ���� �������������, � ������: ��������� ����������� � ����������� �������� �� ������������� ������� ����������� ��������� ������ � ������� {$price}{$nds_txt}{$ndfl_txt}.';
    
    const TEXT4_NDFL = '. ��� ���� �������� ��������� ���� ����������� ���������� ������ � �� ��������� ��. 226 ���������� ������� ���������� ���������, � ����� � ������������ � �. 3.6 �������� �������� �� ��������� ������ ����� �� ������ ���������� ��� �� ������ {$ndfl} ��������� � ������� {$ndfl_price}';
    
    
    
    const LETTER_NDFL = '�� ��������� ����.6 �.3 ��.208 ���������� ������� ��, ��������� ����� (�����) ����������� ��������� � ������� ���������� �� ���������� �� ��������� ���������� ��������� � �� ���������� ���� �� ���������� ��. ����������� �������������� ���������� ��� ���������� ������ �� ���������� ������ �����������.';

    const TEXT_NDFL_PRICE = '��� ���� �������� ��������� ���� ����������� ���������� ������ � �� ��������� ��. 226 ���������� ������� ���������� ���������, � ����� � ������������ � �. 3.6 �������� �������� �� ��������� ������ ����� �� ������ ���������� ��� �� ������ 13 ��������� � ������� %s.';
    
    const TEXT_NDS_PRICE = ', � ��� ����� ��� %s';
    
    //--------------------------------------------------------------------------
    
    /**
     * ���� �����������/�������� � 
     * ���������� ����, �������� ��
     */
    const DETAILS_FT_PHYS_RT_RU = '
{$fio}
����� �����������: {$address_reg}
�������� �����: {$address}
�������: {$idcard_ser} {$idcard}
�����: {$idcard_from} {$idcard_by}

E-mail: {$email}
�������: {$phone}';
    
    /**
     * ���� ����������� -
     * ���������� ����, �������
     */
    const DETAILS_FT_PHYS_RT_REFUGEE = '
{$fio}
����� �����������: {$address_reg}
�������� �����: {$address}
C������������ � �������������� ���������� ������� �� ���������� ��: {$idcard_ser} {$idcard}
������: {$idcard_from} {$idcard_by}

E-mail: {$email}
�������: {$phone}';    
    
    /**
     * ���� ����������� -
     * ���������� ����, ��� �� ���������� � ��
     */
    const DETAILS_FT_PHYS_RT_RESIDENCE = '
{$fio}
����� �����������: {$address_reg}
�������� �����: {$address}
��� �� ���������� � ��: {$idcard_ser} {$idcard}
�����: {$idcard_from} {$idcard_by}

E-mail: {$email}
�������: {$phone}';       
    
    /**
     * ���� �����������/�������� � 
     * ���������� ����, ���������� ��
     */
    const DETAILS_FT_PHYS_RT_UABYKZ = '
{$fio}
����� �����������: {$address_reg}
�������� �����: {$address}
�������: {$idcard_ser} {$idcard}
�����: {$idcard_from} {$idcard_by}
{$bank}
E-mail: {$email}
�������: {$phone}';
   
    /**
     * ���� ��������� ���������� ���������
     */
    const DETAILS_BANK = '
��������� ����: {$bank_rs}
� {$bank_name}

�������������� ����: {$bank_rf_name}
����������������� ����: {$bank_rf_ks}
���: {$bank_rf_bik}
���: {$bank_rf_inn}
';
    
    
    /**
     * ���� �����������/�������� � 
     * ����������� ����, ��, �������� ��
     */
    const DETAILS_FT_JURI_IP_RT_RU = '
{$full_name}
����������� �����: {$address_jry}
�������� �����: {$address}
���: {$inn}

��������� ����: {$bank_rs}
� {$bank_name}
����������������� ����: {$bank_ks}
���: {$bank_bik}
���: {$bank_inn}

E-mail: {$email}
�������: {$phone}';
    
    /**
     * ���� �����������/�������� � 
     * ����������� ����, �������� ��
     */
    const DETAILS_FT_JURI_RT_RU = '
{$full_name}
����������� �����: {$address_jry}
�������� �����: {$address}
���: {$inn}

��������� ����: {$bank_rs}
� {$bank_name}
����������������� ����: {$bank_ks}
���: {$bank_bik}
���: {$bank_inn}

E-mail: {$email}
�������: {$phone}';    
    
    /**
     * ���� �����������/�������� � 
     * ����������� ����, ���������� ��
     */
    const DETAILS_FT_JURI_RT_UABYKZ = '
{$full_name}
����������� �����: {$address_jry}
�������� �����: {$address}
���: {$rnn}

��������� ����: {$bank_rs}
� {$bank_name}

�������������� ����: {$bank_rf_name}
����������������� ����: {$bank_rf_ks}
���: {$bank_rf_bik}
���: {$bank_rf_inn}

E-mail: {$email}
�������: {$phone}';    
    

/**
 * ���� ������ ����� ������� 
 * � �������� 100% ����� ���������
 */    
    const TEXT2_1 = '
    �������� � ������������� � ������ ���� �� ������� ��������� ������, ��������� ��������������� ������������ �������.

    ����� �������, � ���������� ������������ ��������� � �� ��������� �������� ������� ������������� ������� � ������������ � �. 6.9.3. ��������:

    ��������� ���������������� ������������ ��������� �� ������������� ������������ ������� (���) �� ����������� � ����, ������������ � ����������� �������. 

    ������ ������� �������� �������� ���������� ��� �������� ��������� ����������������� ����� � ������ �������, � ������: � ����� {$price}, � ������� �. 6.15. � ������� 7 ��������.';
   
/**
 * ����� �������� ���� ��-�� ���������� ������ ���������
 */    
    const TEXT2_1_TOP_1 = '�������� � ������������� � ������ ���� �� ������� ��������� ������, ��������� ��������������� ������������ �������.';

    const TEXT2_1_TOP_2 = '����� �������, � ���������� ������������ ��������� � �� ��������� �������� ������� ������������� ������� � ������������ � �. 6.9.3. ��������:        ';
    
    
    const TEXT2_1_MID = '��������� ���������������� ������������ ��������� �� ������������� ������������ ������� (���) �� ����������� � ����, ������������ � ����������� �������. ';
  
    const TEXT2_1_BOT = '������ ������� �������� �������� ���������� ��� �������� ��������� ����������������� ����� � ������ �������, � ������: � ����� {$price}, � ������� �. 6.15. � ������� 7 ��������.';
    
    
    
    
    
/**
 * ���� ������ ����� ������� 
 * � ������� 100% ����� �����������
 */
    const TEXT2_2 = '
    �������� � ������������� � ������ ���� ������� ��������� ������, ��������� ��������������� ������������ �������.
	
    ����� �������, � ���������� ������������ ��������� � �� ��������� �������� ������� ������������� ������� � ������������ � �. 6.9.1. ��������:

    ������ ��������� ���������� ������� � � ���� � ������������ � ����������� � ����������� ��������.

    �������� �. 6.13 �������� ������ ������� �������� �������� ���������� ��� ������������� ������� ��������� ������ � ������ ������� ����������� � ������� {$price} � �������, ������������� � �. 4.3 ��������.';

    
    const TEXT2_2_TOP_1 = '�������� � ������������� � ������ ���� ������� ��������� ������, ��������� ��������������� ������������ �������.';
    
    const TEXT2_2_TOP_2 = '����� �������, � ���������� ������������ ��������� � �� ��������� �������� ������� ������������� ������� � ������������ � �. 6.9.1. ��������:';    
    
    const TEXT2_2_MID = '������ ��������� ���������� ������� � � ���� � ������������ � ����������� � ����������� ��������.';

    const TEXT2_2_BOT = '�������� �. 6.13 �������� ������ ������� �������� �������� ���������� ��� ������������� ������� ��������� ������ � ������ ������� ����������� � ������� {$price} � �������, ������������� � �. 4.3 ��������.';
    
    
    
/**
 * ���� ������ ����� ������� 
 * � ���������� ����� ����� ������������ � ����������
 */
    const TEXT2_3 = '
    �������� � ������������� � ������ ���� ������� ��������� ������, ���� �������� (�� {$persent}) ��������������� ������������ �������.
	
    ����� �������, � ���������� ������������ ��������� � �� ��������� �������� ������� ������������� ������� � ������������ � �. 6.9.2. ��������:

    ��������� ������ �������� ������������� ������������ ������� � ���������� � ����������� ��������� � ����, ������������ � ����������� �������. �������������� ������������ ��������� ������ ������������� ������������ ������� � ���������� �� {$persent}.

    ������ ������� �������� �������� ���������� ���:
        - ������������� ������� ���������� ����������� ��������� ������ � ������ ����������� � ������� {$frl_price} � �������, ��������������� �.�. 6.14, 4.2., 4.4 ��������;
        - ������������� �������� ��������� ����� ����������������� ����� � ������� {$emp_price} � �������, ��������������� �. 6.14, ������� 7 ��������.';
    
    
    const TEXT2_3_TOP_1 = '�������� � ������������� � ������ ���� ������� ��������� ������, ���� �������� (�� {$persent}) ��������������� ������������ �������.';
    
    const TEXT2_3_TOP_2 = '����� �������, � ���������� ������������ ��������� � �� ��������� �������� ������� ������������� ������� � ������������ � �. 6.9.2. ��������:';    
    
    
    const TEXT2_3_MID = '��������� ������ �������� ������������� ������������ ������� � ���������� � ����������� ��������� � ����, ������������ � ����������� �������. �������������� ������������ ��������� ������ ������������� ������������ ������� � ���������� �� {$persent}.';
    
    const TEXT2_3_BOT = '������ ������� �������� �������� ���������� ���:
- ������������� ������� ���������� ����������� ��������� ������ � ������ ����������� � ������� {$frl_price} � �������, ��������������� �.�. 6.14, 4.2., 4.4 ��������;
- ������������� �������� ��������� ����� ����������������� ����� � ������� {$emp_price} � �������, ��������������� �. 6.14, ������� 7 ��������.';
    
    
    const TEXT_TITLE_FRL_REQV = '���������';
    
    const TEXT_TITLE_FRL_DATA = '������';
    
    const TEXT_DATE_WORK = "%d %s �� %s.";
    
    
    /*
    public function prevdate($data)
    {
        $timestamp = strtotime($data);
        $timestamp = strtotime('- 1 day', $timestamp);
        
        if(in_array(idate('w', $timestamp),array(0,6))) {
            $timestamp = strtotime('- 1 day', $timestamp);
        }
        
        //���� ����� �������� �� ��� ��� ��������� ����
        if(in_array(idate('w', $timestamp),array(0,6))) {
            $timestamp = strtotime('+ 1 day', $timestamp);
        }
        
        return date('j.m.Y', $timestamp);
    }
    */


    public function datereqv(ReservesModel $reserveInstance)
    {
        $time = $reserveInstance->getLastCompleteDate(true);
        return date('j',$time) . ' ' . monthtostr(date('n',$time),true) . ' ' . date('Y',$time);
    }



    public function nds($value)
    {
        if(!$value) return false;
        
        $value = $value * 18 / 118;
        return $this->pricelong($value);
    }


    public function pricends($value)
    {
        if(!$value) return false;
        
        $value = $value * 18 / 118;
        return $this->price($value);
    }
    
    
    public function nonds($value)
    {
        if(!$value) return false;
        $value = $value/1.18;
        return $this->price($value);
    }

    
    public function nondstotal($options)
    {
        extract($options);
        $tax_price = $tax_price/1.18;
        return $this->price($price + $tax_price);
    }

    
    public function tuextra($options)
    {
        extract($options);
        
        $text = '';
        foreach($order_extra as $idx)
        {
            if(!isset($extra[$idx])) continue;
            $text .= $this->reformat30($extra[$idx]['title']) . PHP_EOL;
        }
        
        return $text;
    }

    

    public function text2top1($options)
    {
        extract($options);
        
        $data = array();
        
        if($arbitrage_price == 0) $text = self::TEXT2_1_TOP_1;
        elseif($arbitrage_price == $price) $text = self::TEXT2_2_TOP_1;
        else {
            $text = self::TEXT2_3_TOP_1;
            
            $persent = ($arbitrage_price / $price)*100;
            $data['persent'] = $this->price($persent) . ' ' . ending($persent,'�������','��������','���������');
        }
        
        $text = $this->template($text, $data);
        
        return $text;        
    }

    
    public function text2top2($options)
    {
        extract($options);
        
        $data = array();
        
        if($arbitrage_price == 0) $text = self::TEXT2_1_TOP_2;
        elseif($arbitrage_price == $price) $text = self::TEXT2_2_TOP_2;
        else {
            $text = self::TEXT2_3_TOP_2;
            
            $persent = ($arbitrage_price / $price)*100;
            $data['persent'] = $this->price($persent) . ' ' . ending($persent,'�������','��������','���������');
        }
        
        $text = $this->template($text, $data);
        
        return $text;        
    }
    
    
    public function text2mid($options)
    {
        extract($options);
        
        $data = array('price' => $this->pricelong($price));
        
        if($arbitrage_price == 0) $text = self::TEXT2_1_MID;
        elseif($arbitrage_price == $price) $text = self::TEXT2_2_MID;
        else {
            $text = self::TEXT2_3_MID;
            
            $persent = ($arbitrage_price / $price)*100;
            $data['persent'] = $this->price($persent) . ' ' . ending($persent,'�������','��������','���������');
        }
        
        $text = $this->template($text, $data);
        
        return $text;        
    }
    
    
    
    public function text2bot($options)
    {
        extract($options);
        
        $data = array('price' => $this->pricelong($price));
        
        if($arbitrage_price == 0) $text = self::TEXT2_1_BOT;
        elseif($arbitrage_price == $price) $text = self::TEXT2_2_BOT;
        else {
            $text = self::TEXT2_3_BOT;
            
            $emp_price = $price - $arbitrage_price;
            $data['frl_price'] = $this->pricelong($arbitrage_price);
            $data['emp_price'] = $this->pricelong($emp_price);
        }
        
        $text = $this->template($text, $data);
        
        return $text;        
    }
    
    

    public function text2($options)
    {
        extract($options);
        
        $data = array('price' => $this->pricelong($price));
        
        if($arbitrage_price == 0) $text = self::TEXT2_1;
        elseif($arbitrage_price == $price) $text = self::TEXT2_2;
        else {
            $text = self::TEXT2_3;
            
            $emp_price = $price - $arbitrage_price;
            $persent = ($arbitrage_price / $price)*100;
            
            $data['frl_price'] = $this->pricelong($arbitrage_price);
            $data['emp_price'] = $this->pricelong($emp_price);
            
            $data['persent'] = $this->price($persent) . ' ' . ending($persent,'�������','��������','���������');
        }
        
        $text = $this->template($text, $data);
        
        return $text;
    }

    

    public function text4(ReservesModel $reserveInstance)
    {
        
        
        $pricePay = $reserveInstance->getPayoutSum();
        $pricePayNDFL = $reserveInstance->getPayoutNDFL();
        $priceBack = $reserveInstance->getPayback();
        $src_id = $reserveInstance->getSrcId();
        
        $data = array(
            'num_bs' => $this->num($src_id)
        );        
        
        if (!$reserveInstance->isArbitrage()){
            $text = self::TEXT4_3_TML;
            $data['price'] = $this->pricelong($pricePay);
        } elseif ($pricePay == 0) {
            $text = self::TEXT4_2_TML;
            $data['emp_price'] = $this->pricelong($priceBack);
        } elseif ($priceBack == 0) {
            $text = self::TEXT4_0_TML;
            $data['price'] = $this->pricelong($pricePay);
        } else {
            $text = self::TEXT4_1_TML;
            $data['price'] = $this->pricelong($pricePay);
            $data['emp_price'] = $this->pricelong($priceBack);
        }
        
        if ($reserveInstance->isArbitrage()) {
            $data['date_close'] = $this->date($reserveInstance->getArbitrageDateClose());
        }
        
        $data['ndfl_txt'] = '';
        if ($pricePayNDFL > 0) {
            $ndfl_txt = $this->template(self::TEXT4_NDFL, array(
                'ndfl_price' => $this->pricelong($pricePayNDFL),
                'ndfl' => $reserveInstance::NDFL * 100
            ));
            $data['ndfl_txt'] = $ndfl_txt;
        }
        
        $text = $this->template($text, $data);
        return $text;
    }

    

    public function text5($options)
    {
        extract($options);
        
        if($arbitrage_price === null) $text = self::TEXT5_3_TML;
        elseif($arbitrage_price == 0) $text = self::TEXT5_2_TML; 
        elseif($arbitrage_price == $price) $text = self::TEXT5_0_TML;
        else $text = self::TEXT5_1_TML;
        
        return $text;
    }
    
    public function text6($options)
    {
        extract($options);
        
        $text = '';
        if ($arbitrage_price > 0 && $arbitrage_price < $price) {
            $text = self::TEXT6_1_TML;
        }
        return $text;
    }


    public function text3($options)
    {
        extract($options['reserve_data']);

        $pricePay = $options['reserve']->getPayoutSumWithOutNDFL();
        
        $ndfl_txt = $this->ndflprice($options['reserve']);
        
        if($arbitrage_price === null) $text = sprintf(self::TEXT3_0_TML, 
                $this->pricelong($pricePay), 
                $ndfl_txt);
        elseif($arbitrage_price == $price) $text = sprintf(self::TEXT3_1_TML, 
                $this->pricelong($pricePay), 
                $ndfl_txt);
        else $text = sprintf(self::TEXT3_2_TML, 
                $this->num($options['order_id']), 
                $this->date($arbitrage_date_close),
                $this->pricelong($pricePay),
                $ndfl_txt);
        
        return $text;
    }

    
    public function kpp($reqv)
    {
        if(!$reqv || !$reqv['form_type']) return false;
        return $reqv['kpp'];        
    }
    
    
    public function inn($reqv)
    {
        if(!$reqv || !$reqv['form_type']) return false;
        return $reqv['inn'];        
    }
    

    public function address($reqv)
    {
        if(!$reqv || !$reqv['form_type']) return false;
        return ($reqv['form_type'] == sbr::FT_PHYS)?$reqv['address']: $reqv['address_jry'];
    }
    

    public function details($options)
    {
        extract($options);
        
        if(!$reqv || !$reqv['form_type']) return false;
        
        $reqv['email'] = $email;
        $form_type = $reqv['form_type'];
        $rez_type  = $reqv['rez_type'];
        
        $is_ip = false;
        if(isset($reqv['type']) && $reqv['type'] !== null)
        {
            $is_ip = ($reqv['type'] == sbr_meta::TYPE_IP);
            if(!$is_ip) $reqv['full_name'] = '�' . $reqv['full_name'] . '�';
            $reqv['full_name'] = sbr_meta::$types_short[(int)$reqv['type']] . ' ' . $reqv['full_name'];
        }
        
        $reqv['bank'] = '';
        if(isset($reqv['bank_rs']))
        {
            $reqv['bank'] = $this->template(self::DETAILS_BANK, $reqv);
        }
        
        //@todo: https://beta.free-lance.ru/mantis/view.php?id=29233
        if (!empty($reqv['mob_phone'])) {
            $reqv['phone'] = $reqv['mob_phone']; 
        }
        
        $details = array(
            sbr::FT_PHYS . sbr::RT_RU => self::DETAILS_FT_PHYS_RT_RU,
            sbr::FT_PHYS . sbr::RT_RESIDENCE => self::DETAILS_FT_PHYS_RT_RESIDENCE,
            sbr::FT_PHYS . sbr::RT_REFUGEE => self::DETAILS_FT_PHYS_RT_REFUGEE,
            sbr::FT_PHYS . sbr::RT_UABYKZ => self::DETAILS_FT_PHYS_RT_UABYKZ,
            sbr::FT_JURI . sbr::RT_RU => ($is_ip)?self::DETAILS_FT_JURI_IP_RT_RU:
                                                  self::DETAILS_FT_JURI_RT_RU,
            sbr::FT_JURI . sbr::RT_UABYKZ => self::DETAILS_FT_JURI_RT_UABYKZ
        );
        
        $code = $form_type . $rez_type;
        if(!isset($details[$code])) return false;
        
        $txt = $this->template($details[$code], $reqv);
        $txt = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $txt);
        
        return $txt;
    }

    
    /*public function phone($uid)
    {
        $reqvs = ReservesHelper::getInstance()->getUserReqvs($uid);
        if(!$reqvs || !$reqvs['form_type']) return false;
        $reqv = $reqvs[$reqvs['form_type']];
        if(empty($reqv['phone'])) $reqv['phone'] = $reqv['mob_phone'];
        return $reqv['phone'];
    }*/

    
    public function fio($reqv)
    {
        if (!$reqv || !$reqv['form_type']) return false;
        
        $fio = $reqv['fio'];
        if ($reqv['form_type'] == sbr::FT_JURI) {
            if ($reqv['type'] === null) {
                $fio = sprintf(self::FIO_JURI_TML, $reqv['full_name']);
            } elseif ($reqv['type'] == sbr_meta::TYPE_IP) {
                $fio = sbr_meta::$types_short[(int)$reqv['type']] . ' ' . $reqv['full_name'];
            } else {
                $fio = sprintf(self::FIO_JURI_TML2, sbr_meta::$types_short[(int)$reqv['type']], $reqv['full_name']);
            }
        }
        
        return $fio;
    }
    
    
    
    public function name($reqv)
    {
        if(!$reqv || !$reqv['form_type']) return false;
        
        $fio = $reqv['fio'];
        
        if($reqv['form_type'] == sbr::FT_JURI)
        {
            if($reqv['type'] === null) $fio = $reqv['full_name'];
            elseif($reqv['type'] == sbr_meta::TYPE_IP) $fio = sbr_meta::$types_short[(int)$reqv['type']] . ' ' . $fio;
            else $fio = sprintf("%s �%s�", sbr_meta::$types_short[(int)$reqv['type']], $reqv['full_name']);            
        }
        
        return html_entity_decode($fio, ENT_QUOTES);
    }

    



    public function num($value)
    {
        return sprintf(self::NUM_TMP, $value);
    }
    
    
    public function orderurl($order_id)
    {
        return $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order_id);
    }
    
    
    public function daytext($value)
    {
        return tservices_helper::days_format($value);
    }
    
    
    public function reformat60($value)
    {
        return reformat($value, 60, 0, 0, 1);
    }
    
    
    public function reformat30($value)
    {
        return reformat($value, 30, 0, 1);
    }
    
    public function text7($reqv) 
    {
        if(!$reqv || !$reqv['form_type']) return '';
        
        if ($reqv['rez_type'] == sbr::RT_UABYKZ) {
            return self::LETTER_NDFL;
        }
        return '';
    }
    
    public function ndflprice(ReservesModel $reserveInstance) 
    {
        $pricePayNDFL = $reserveInstance->getPayoutNDFL();
        
        if ($pricePayNDFL > 0) {
            return $this->template(self::TEXT4_NDFL, array(
                'ndfl_price' => $this->pricelong($pricePayNDFL),
                'ndfl' => $reserveInstance::NDFL * 100
            ));
        }
        return '';
    }
    
    public function ndsprice($options) 
    {
        extract($options);
        
        if(!$reqv || !$reqv['form_type']) return '';
        
        if ($reqv['bank_nds'] == 1) {
            return sprintf(self::TEXT_NDS_PRICE, $this->nds($price));
        }
        return '';
    }
    
    public function dettitle($reqv)
    {
        if(!$reqv || !$reqv['form_type']) return false;
        
        //��������� ����� � ����� � � ������-������������, ����������� ���������� ���������
        $use_reqv = ($reqv['form_type'] == sbr::FT_JURI) || ($reqv['rez_type'] == sbr::RT_UABYKZ && isset($reqv['bank_rs']));
        
        return $use_reqv ? self::TEXT_TITLE_FRL_REQV : self::TEXT_TITLE_FRL_DATA;
    }
    
    public function worktime($options)
    {
        extract($options);
        
        $dateTime = new DateTime($date);
        $dateTime->add(new DateInterval("P" . $days . "D"));
        $date_formatted = $dateTime->format('d.m.Y, H:i');

        return sprintf(self::TEXT_DATE_WORK, 
                $days,
                ending($days, '����', '���', '����'), 
                $date_formatted);
    }
    
    public function country($options)
    {
        extract($options);
        
        if(!$reqv || !$reqv['form_type']) return false;
        
        if ($reqv['country']) {
            return $reqv['country'];
        } elseif (in_array($reqv['rez_type'], array(sbr::RT_RU, sbr::RT_REFUGEE, sbr::RT_RESIDENCE))) {
            return '������';
        } else {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/country.php';
            return country::GetCountryName($user_country_id);
        }
    }
}