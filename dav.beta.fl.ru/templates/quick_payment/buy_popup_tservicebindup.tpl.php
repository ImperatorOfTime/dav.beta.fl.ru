<?php
/**
 * ������ ����������� popup-���� "�������" ������
 */
?>
<div id="<?= @$popup_id ?>" data-quick-payment="<?=$unic_name ?>" class="b-shadow b-shadow_block b-shadow_center b-shadow_width_520 <?= (!@$is_show) ? 'b-shadow_hide' : '' ?> b-shadow__quick quick_payment_tservicebindup">
    <div class="b-shadow__body b-shadow__body_pad_15_20">

        <div class="b-fon <?= @$popup_title_class_bg ?>">
            <div class="b-layout__title b-layout__title_padbot_5">
                <span class="b-icon b-page__desktop b-page__ipad b-icon_float_left b-icon_top_4 <?= @$popup_title_class_icon ?>"></span>
                <?= @$popup_title ?>
            </div>
        </div>

        <form>
            <input type="hidden" name="kind" value="<?=$kind?>" />
            <input type="hidden" name="prof_id" value="<?=$prof_id?>" />
            <input type="hidden" name="redirect" class="input-redirect" value="" />
            <input type="hidden" name="tservice_text_db_id" value="<?=$tservices_cur?>" />
                            
            <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20">
                ������ <strong>"<?=$tservices_cur_text?>"</strong>
            </div>
        </form>
        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20">����� � ������ ������</div>

        <div class="b-layout <?php //b-layout_waiting  ?>">
            <div data-quick-payment-error-screen="true" class="b-fon b-fon_margbot_20 b-fon_marglr_20 b-layout_hide">
                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeee"> 
                    <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>
                    <span data-quick-payment-error-msg="true"></span>
                </div>
            </div>
            
            <?=$promo_code?>

            <div class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_padbot_20 b-layout__txt_fontsize_11">
                ����� � ������: <span class="b-layout__bold"><span class="quick_sum_pay"><?= $ammount ?></span> ���.</span><br/>
                <span id="pay_buffer" class="b-layout_hide">��� ����� ������� � ����� �������� ����� (<span id="buffer_sum"><?=$buffer?></span> ���.)</span>
                <span class="pay_none">��� ����� ������� � ������� �����, �� ��� <strong class="ac_sum"><?= $ac_sum ?></strong> ���.</span>
                <span class="pay_part">
                    ����� ����� (<?= $ac_sum ?> ���.) ���� �� ����� ������ �����.<br />
                    ������� (<span class="quick_sum_part"></span> ���.) ��� ����� �������� ����� �� ��������:
                </span>
                <span class="pay_full">�� �� ������ �������� ����� �� ��������:</span>
            </div>
            <?php
            if (!empty($payments)):
                ?>
                <div class="payments">
                    <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10"> 
                        <?php foreach ($payments as $key => $payment): ?>
                            <?php if (isset($payment['title'])): ?>
                                <a class="b-button b-button_margbot_5 b-button__pm <?= @$payment['class'] ?>" 
                                   href="javascript:void(0);" 
                                   <?=(isset($payment['data-maxprice']))?'data-maxprice="'.$payment['data-maxprice'].'"':''?> 
                                   <?= (isset($payment['wait'])) ? 'data-quick-payment-wait="' . $payment['wait'] . '"' : '' ?> 
                                   data-quick-payment-type="<?= $key ?>"><span class="b-button__txt"><?= @$payment['title'] ?></span></a> 
                                <?php if (isset($payment['content_after'])): ?>
                                <div class="<?=$key?>_text b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_valign_middle b-layout__txt_width_440">
                                    <?=$payment['content_after']?>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div data-quick-payment-wait-screen="true" class="b-layout__wait b-layout__txt_fontsize_15 b-layout__txt_color_6db335 b-layout_hide">
                    <span data-quick-payment-wait-msg="true"></span>
                    <div class="b-layout__txt b-layout__txt_center b-layout__txt_padtb_10">
                        <img width="80" height="20" src="/images/Green_timer.gif">
                    </div>
                </div>

                <div class="__quick_payment_form b-layout_hide"></div>
                <?php
            endif;
            ?>
            <div class="b-buttons payment_account">
                <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10">
                    <a class="b-button b-button_flat b-button_flat_green" 
                       href="javascript:void(0);" 
                       data-quick-payment-type="<?= $payment_account ?>">�������� <span class="quick_sum_pay_acc"></span> ���.</a> </div>
            </div>
                
            <div class="b-buttons b-layout_hide payment_buffer">
                <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10">
                    <a class="b-button b-button_flat b-button_flat_green" 
                       href="javascript:void(0);" 
                       data-quick-payment-type="buffer">�������</a>
                </div>
            </div>
        </div>
    </div>
    <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>
