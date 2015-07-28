<script type="text/javascript">
window.addEvent('domready', 
    function() {
        calcAmmountOfOption($$('.scalc-click'), $('scalc_result'));
    }
);
</script>

<div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
    <?php if($payed) { ?>
    <div class="b-layout__txt b-layout__txt_padbot_40">��� ���� ����� ���������� ��������� ���� ������, ���������� <a class="b-layout__link" href="/bill/" target="_blank">��������� ������ ����</a> �� �����.</div>				
        <h2 class="b-layout__title b-layout__title_padbot_20">�� �������� ������:</h2>
        <form method="POST" name="frm" id="frm">
            <input type="hidden" name="action" value="upd_pay_options">
            <?php 
            // ��� �� ������� ��� ������ ��� ���.
            $is_pro = is_pro();
            foreach($payed as $k=>$pay) { ?>
                <input type="hidden" name="options[<?=$pay['id']?>]" value="1">
                <? if ($pay['op_code'] == 15) {
                    $is_pro = true;
                }
            }

            // �������� ����� (��� ��� = 10, ��� ����� = 0)
            $proBonus = $is_pro ? new_projects::PRICE_ADDED : 0;
            ?>
            <input type="hidden" name="pro_bonus" value="<?= $proBonus; ?>">
            <input type="hidden" name="is_pro" value="<?= is_pro() ? 1 : 0 ?>">
        <?php foreach($payed as $k=>$pay) {
            // ���� ��� ������ ���, �� ��������� ������ - ���������
            if (is_pro() && $pay['op_code'] == 53 && $pay['option'] == 2) {
                if (!is_array($disabled)) {
                    $disabled = array(0);
                }
                $disabled[$pay['id']] = true;                    
            }
            if (!$disabled[$pay['id']]) {
                // ��������� ���������� ���� ��� "����������� ������� �� �����"
                if ((int)$pay['option'] === 1) {
                    $days = $pay['top_count'];
                } else {
                    $days = 1;
                }
                // ���������� ��������� ������� �������� ����� ��� ���
                $ammount = $pay['ammount'] - ($pay['op_code'] != 15 ? $proBonus * $days : 0);
                $sum += $ammount;
            } ?>
            <div class="b-check b-check_padbot_10">
                <?php if($disabled[$pay['id']]) { ?>
                <input id="def<?= (int)$pay['id']?>" type="hidden" value="1" name="default[<?= $pay['id']?>]" />
                <?php }//if?>
                <input id="pay<?= (int)$pay['id']?>" type="checkbox" value="1" name="pay_options[<?= $pay['id']?>]" class="b-check__input scalc-click" <?= ($disabled[$pay['id']] ? 'disab="1"':'')?> checked="checked" price="<?= round($pay['ammount'],2)?>" top_count="<?= (int)$pay['top_count']?>" op_code="<?= (int)$pay['op_code']?>" option="<?= (int)$pay['option']?>" <?= ($dis[$pay['id']])?"dis='{$dis[$pay['id']]}'":""?> pid="<?=$pay['id']?>"/>
                <label class="b-check__label b-check__label_fontsize_13" >
                    <?php switch($pay['op_code']) {
                        case 15:
                            ?>�������  <span class="b-icon b-icon__pro b-icon__pro_e"></span> �� <?= ($pay['ammount']/10)?> <?= ending($pay['ammount']/10, "�����", "������", "�������");?><?
                            break;
                        case 53:
                            switch($pay['option']) {
                                case 1:
                                    ?>����������� <?=$pay['type'] == 1?"��������":"�������"?> �<?=$pay['project_name']?>� ������� �����<?
                                    break;
                                case 2:
                                    ?>��������� ������ <?=$pay['type'] == 1?"��������":"�������"?> �<?=$pay['project_name']?>�<?
                                    break;
                                case 3:
                                    ?>��������� ������ <?=$pay['type'] == 1?"��������":"�������"?> �<?=$pay['project_name']?>�<?
                                    break;
                                case 4:
                                    ?>������� ��� <?=$pay['type'] == 1?"��������":"�������"?> �<?=$pay['project_name']?>�<?
                                    break;
                                default:
                                    ?>������� <?=$pay['type'] == 1?"�������":"������"?> �<?=$pay['project_name']?>�<?
                                    break;
                            }
                            break;
                        case 61:
                            ?><?=$pay['option']?> <?=ending($pay['option'], '�������','�������','�������')?> <?=ending($pay['option'], '�����', '������', '�������')?> �� <?= ending($pay['option'], '������', '�������', '�������')?><?
                            break;
                        case 76:
                            ?>�������  <span class="b-icon b-icon__pro b-icon__pro_f"></span> �� 1 ������<?
                            break;
                        case 48:
                            ?>�������  <span class="b-icon b-icon__pro b-icon__pro_f"></span> �� 1 �����<?
                            break;
                        case 49:
                            ?>�������  <span class="b-icon b-icon__pro b-icon__pro_f"></span> �� 3 ������<?
                            break;
                        case 50:
                            ?>�������  <span class="b-icon b-icon__pro b-icon__pro_f"></span> �� 6 �������<?
                            break;
                        case 51:
                            ?>�������  <span class="b-icon b-icon__pro b-icon__pro_f"></span> �� 1 ���<?
                            break;
                        case 9:
                        case 106:
                            ?>���������� �������� �<?=$pay['project_name']?>�<?
                            break;
                        default:
                            break;
                    }//?>
                </label>
            </div>	
        <?php }//foreach?>
        <?/*
        
            <div class="b-check b-check_padbot_10">
                <input type="checkbox" value="" name="" class="b-check__input" />
                <label class="b-check__label b-check__label_fontsize_13" >�������  <span class="b-icon b-icon__pro b-icon__pro_e"></span> �� 1 ������</label>
            </div>		
            <div class="b-check b-check_padbot_10">
                <input type="checkbox" value="" name="" class="b-check__input" />
                <label class="b-check__label b-check__label_fontsize_13" >���������� �������� ���������� ������ �������</label>
            </div>		
            <div class="b-check b-check_padbot_10">
                <input type="checkbox" value="" name="" class="b-check__input" />
                <label class="b-check__label b-check__label_fontsize_13" >��������� �������� ���������� ������ �������</label>
            </div>		
            <div class="b-check">
                <input type="checkbox" value="" name="" class="b-check__input" />
                <label class="b-check__label b-check__label_fontsize_13" >��������� ������� ����������� �������� � ����. ������</label>
            </div>	*/?>	
        </form>
        <h2 class="b-layout__title b-layout__title_padtop_30">��� ����� <span class="b-layout__txt b-layout__txt_fontsize_22 b-layout__txt_color_fd6c30"><span id="scalc_result"><?= round($sum,2)?></span> <?= ending(round($sum), '�����', '�����', '������');?></span></h2>
       <?/* <div class="b-layout__txt b-layout__txt_fontsize_11">FM &ndash; ��� ���������� ������ �����. 1 FM = 30 ���������� ������.</div> */?>
    <?php } else {//if?>
        <h2 class="b-layout__title">�� �� �������� �� ����� ������</h2>
        <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11">�� ����� ���� ������� ���������� �������������� �����, ������� ������� ���� ������ ����� � ����������. ���� �� �������� ��������������� ���, ��� ����������� <a class="b-layout__link" href="/bill/" target="_blank">��������� ������ ����</a>.</div>
        <?/* <div class="b-layout__txt b-layout__txt_padbot_40 b-layout__txt_fontsize_11">FM &ndash; ��� ���������� ������ �����. 1 FM = 30 ���������� ������.</div> */?>
    <?php }//else?>
    <form method="POST" name="frm" id="frm">
        <input type="hidden" name="action" value="upd_pay_options">
        <input type="hidden" name="dontpayed" value="1">
        <div class="b-buttons b-buttons_padtop_40 b-buttons_padbot_40">
            <a href="javascript:void(0)" onclick="$('frm').submit();" class="b-button b-button_rectangle_color_green">
                <span class="b-button__b1">
                    <span class="b-button__b2 b-button__b2_padlr_15">
                        <span class="b-button__txt">��������� ������</span>
                    </span>
                </span>
            </a>&#160;&#160;
            <span class="b-buttons__txt">&#160;���&#160;</span>
            <a href="/wizard/registration/?action=exit" class="b-buttons__link b-buttons__link_color_c10601">����� �� �������</a><span class="b-buttons__txt b-buttons__txt_color_ee1d16">&nbsp;�&nbsp;��������� ���� ������� ������ �� ����� ������������</span> 
        </div>
    </form>
    
    <div class="b-layout__txt ">�� ������ �� ��������� ���� ������, �� � ���� ������, ��� ���������� ���� ������ ��������. �� ������� �� ������� � ����� ������� ��� ��� ����� �� �������� &laquo;<a class="b-layout__link" href="/service/">������</a>&raquo;.</div>		
    
</div>