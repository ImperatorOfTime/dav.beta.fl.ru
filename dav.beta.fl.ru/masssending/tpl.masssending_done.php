    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_center b-layout__left_padtop_80 b-layout__td_width_null_ipad">
                <span class="b-page__desktop"><img class="b-promo__pic" src="/images/promo-icons/big/3.png" alt="" /></span>
            </td>
            <td class="b-layout__td b-layout__td_width_72ps b-layout__td_width_full_ipad">

                <h1 class="b-page__title">�������� ������� � ������������</h1>

                <div class="b-fon b-fon_padbot_30">
                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                    <div class="b-fon__txt b-fon__txt_padbot_5"><span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>���� ������ �� �������� ���� ���������� �� ���������. �� ��������� �������� ��� ����� ������� �����������.</div>
                    <div class="b-fon__txt">���� � ��� ��������� �������, ����������� � <a class="b-fon__link" href="https://feedback.fl.ru/">������ ���������</a>.</div>
                </div>
                </div>                          
<?php 
$count = strval($count);
$lex = '�������';
$measure = '��������';
if ($count == 1 || (($count > 4) && ($count < 21))) {
    $lex = '�������';
    $measure = '�������';
}
if (strlen($count) > 1) {
	$cn = $count[strlen($count) - 2];
	$cm = $count[strlen($count) - 1];	
	if ( ($cm == 1) || ($cm == 0) ) {
        $lex = '�������';
        $measure = '�������';
    }
    if (($cn == 1) || ($cm > 4)) {
        $lex = '�������';
        $measure = '�������';
    } 
}


?>
                <div class="b-layout__txt b-layout__txt_padbot_5">���� �������� <?=$lex ?> <span class="b-layout__txt b-layout__txt_bold"><?= $count ?> <?=$measure ?></span></div>
                <div class="b-layout__txt">��� ����� ������ <span class="b-layout__txt b-layout__txt_bold b-layout__txt_color_fd6c30"><?= round($cost, 2) ?> ���.</span></div>


            </td>							
        </tr>
    </table>
