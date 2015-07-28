<?
/**
 * ����� ��������� �������
 * � ������� ����� ��� "%%���_�������(...)"
 */
class system_tpl_helper {
	/**
	 * ����� ��������
	 * @param object $timestamp ���������� ������ UNIX
	 * @return ������ �������� � �������
	 */
    function user_age_str($timestamp) {
        $ia = (int)time()-(int)$timestamp;
        
        $y = floor($ia/(31557600/*60*60*24*365.25*/));
        
        $int = ($y < 10 || $y > 20);
        $yi  = ($y%10);
        if($yi == 1 && $int) {
            $in = "���";    
        } elseif($yi >= 1 && $yi <=4 && $int) {
            $in = "����";    
        } else {    
            $in = "���";
        }
        
        return $y." ".$in;
    }
}
?>