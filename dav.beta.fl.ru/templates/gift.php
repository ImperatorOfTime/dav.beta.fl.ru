<? foreach ($gifts as $i => $gift) {
    $link = '/users/' . $gift['login'] . '/';
?>
    <div class="b-fon b-fon_width_full b-fon_padbot_10 last-gift-block<?= $i > 0 ? " b-fon_hide" : "" ?>" id="last_gift<?= $gift['id'] ?>">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
            <div class="b-fon__txt b-fon__txt_center b-username">
                <? if ($gift['op_code'] == 23) { // ������� ?>
                    <span class="b-icon b-icon_mid_f b-icon_valign_middle"></span>
                    ������������ 
                    <a class="b-username__link" href="<?= $link ?>"><?= $gift['uname'] . ' ' . $gift['usurname'] ?></a> 
                    <span class="b-username__login b-username__login_color_fd6c30">[<a class="b-username__link b-username__link_color_fd6c30" href="<?= $link ?>"><?= $gift['login'] ?></a>]</span>
                    �������<?= $gift['sex'] == 'f' ? '�' : '' ?> ��� 
                    <span class="b-fon__txt b-fon__txt_bold"><?= round($gift['ammount'],2) ?> ���.</span> &nbsp;&nbsp;
                <? } else { // ������� ?>
                    <span class="b-icon b-icon_mid_gift b-icon_valign_middle"></span>
                    <?php if ( $gift['login'] != 'admin' ) {?>
                        ������������ 
                        <a class="b-username__link" href="<?= $link ?>"><?= $gift['uname'] . ' ' . $gift['usurname'] ?></a> 
                        <span class="b-username__login b-username__login_color_fd6c30">[<a class="b-username__link b-username__link_color_fd6c30" href="<?= $link ?>"><?= $gift['login'] ?></a>]</span> 
                        �������<?= $gift['sex'] == 'f' ? '�' : '' ?> ��� 
                    <?php }?>
                    <? switch ($gift['op_code']) {
                        case 16:
                        case 52:
                            $text1 = '������� PRO';
                            $count = - $gift['ammount_from'] / (is_emp() ? 10 : 19);
                            $text2 = ' �� ' . $count . ' ' . ending($count, '�����', '������', '�������') . '.';
                            break;
                        case 69:
                            $text1 = '������� ����� � ��������� �����';
                            break;
                        case 17:
                            $text1 = '������� ����� �� ������� ��������';
                            $count = - $gift['ammount_from'] / 150;
                            $text2 = ' �� ' . $count . ' ' . ending($count, '�����', '������', '�������') . '.';
                            break;
                        case 83:
                            $text1 = '������� ����� ������� ��������';
                            break;
                        case 84: // �� ���� ��������
                            $text1 = '������� ����� � ��������';
                            $count = - $gift['ammount_from'] / 25;
                            $text2 = ' �� ' . $count . ' ' . ending($count, '������', '������', '������') . '.';
                            break;
                        case 85: // � �����-�� �������
                            $text1 = '������� ����� � ��������';
                            $count = - $gift['ammount_from'] / 10;
                            $text2 = ' �� ' . $count . ' ' . ending($count, '������', '������', '������') . '.';
                            break;
                        case 115: // 
                            $text1 = '�� ������������ ������� - ���������������� ������� �� 1 ������. �������������� ������������ ������������� PRO.';
                            if ( is_emp() ) {
                                $text1 = '�� ������������ ������� - ���������������� ������� �� 1 �����. �������������� ������������ ������������� PRO';
                            }
                            $count = 1;
                            $text2 = '';
                            break;
                            //�������, ��� ��������������� ����������/WebMoney ��� ���������� �����. ��� ������� - ��� ������. 
                        case 95: 
                        case 96: 
                        case 97:
                        case 100:
                            include_once $_SERVER["DOCUMENT_ROOT"]."/classes/op_codes.php";
                            include_once $_SERVER["DOCUMENT_ROOT"]."/classes/payed.php";
                            $op_codes = new op_codes();
                            if ( $gift['op_code'] == 95 ) {
                                $n = $op_codes->GetField(is_emp() ? 15 : 48, $err, "sum") * 300;
                                if(is_emp()) $n = payed::PRICE_EMP_PRO;
                            } elseif ( $gift['op_code'] == 96 || $gift['op_code'] == 100) {
                                $n = $op_codes->GetField(is_emp() ? 15 : 48, $err, "sum") * 300;
                                if(is_emp()) $n = payed::PRICE_EMP_PRO;
                            } elseif ( $gift['op_code'] == 97 ) {
                                if ( !is_emp() ) {
                                    $n = $op_codes->GetField(17, $err, "sum") * 30;
                                } else {
                                    $n = 2550;
                                }
                            }
                            $text1 = "�������, ��� ��������������� ���������� ��������� ��� ���������� �����. ��� ������� - $n ������.";
                            $count = 1;
                            $text2 = '';
                            break;
                        case 91: 
                        case 93:
                            include_once $_SERVER["DOCUMENT_ROOT"]."/classes/op_codes.php";
                            $op_codes = new op_codes();
                            if ( $gift['op_code'] == 91 ) {
                                $n = $op_codes->GetField(48, $err, "sum") * 300;
                            } elseif ( $gift['op_code'] == 93 ) {
                                if ( !is_emp() ) {
                                    $n = $op_codes->GetField(17, $err, "sum") * 30;
                                } else {
                                    $n = 2550;
                                }
                            } 
                            $text1 = "�������, ��� ��������������� WebMoney ��� ���������� �����. ��� ������� - $n ������.";
                            $count = 1;
                            $text2 = '';
                            break;
                        default:
                            $text1 = $gift['op_name'];
                            break;
                    } ?>
                    <?php if ( $gift['op_code'] == 115 ) {?>
                    <span class="b-fon__txt b-fon__txt_bold"><?= $text1 ?></span><span class="b-fon__txt b-fon__txt_nowrap">
                    <?php } else {?>
                    <span class="b-fon__txt b-fon__txt_bold"><?= $text1 ?></span><?= $text2 ?> &nbsp;&nbsp;<span class="b-fon__txt b-fon__txt_nowrap">
                    <?php } ?>
                    <? if( in_array($gift['op_code'], array(16, 52, 91, 92, 95, 99, 96, 100)) ) { ?>
                    <a class="b-button b-button_rectangle_color_green"  href="javascript:void(0)" onclick="SetGiftResv(<?= $gift['id'] ?>)">
                        <span class="b-button__b1">
                            <span class="b-button__b2">
                                <span class="b-button__txt">�������</span>
                            </span>
                        </span>
                    </a>&nbsp;&nbsp;
                    <? }//if?>
                    <? if ( $gift['login'] != "admin" ) {?>
                        <a class="b-fon__link b-fon__link_fontsize_11" href="/bill/gift/">�������� �������</a> &nbsp;&nbsp;<? if( in_array($gift['op_code'], array(16, 52, 91, 92, 95, 99, 96, 100)) ) { ?></span><? }//if?>
                    <? } ?>
                <? } ?>
                        
                <? if( !in_array($gift['op_code'], array(16, 52, 91, 92, 95, 99, 96, 100)) ) { ?>
                <a class="b-fon__link b-fon__link_bordbot_dot_0f71c8 b-fon__link_fontsize_11" href="javascript:void(0)" onclick="SetGiftResv(<?= $gift['id'] ?>)">�������</a></span>
                <? }//if?>
            </div> 
        </div>
    </div>
<? } ?>