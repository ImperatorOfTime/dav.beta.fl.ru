<div 
    id="order_arbitrage_popup_<?=$idx?>" 
    class="b-shadow b-shadow_center b-shadow_width_580 b-shadow_hide b-shadow__quick __tservices_orders_status_popup_hide" 
    style="display:block;"
    data-order-arbitrage="true">
    <div class="b-shadow__body b-shadow__body_pad_20">
        <h2 class="b-layout__title">��������� � ��������</h2>
        <div class="b-layout__txt b-layout__txt_padbot_10">
            ����������, �������, �� ����� ������ ������� �� ������ ���������� � ��������.
        </div>
        
        <div class="b-layout b-layout_padleft_20">
            <form action="" method="post">
                <input type="hidden" name="oid" value="<?=$idx?>" />
                <div class="b-textarea">
                    <textarea data-validators="maxLength:500 required" 
                              id="message" class="b-textarea__textarea b-textarea__textarea_italic" 
                              rows="5" cols="80" maxlength="500" name="message" 
                              placeholder="������� ����� �������"></textarea>
                    <div id="error_message" class="error-message i-shadow b-shadow_hide">
                        <div class="b-shadow b-shadow_m b-shadow_top_0 b-shadow_zindex_2">
                            <div class="b-shadow__body b-shadow__body_pad_5_10 b-shadow__body_bg_fff">
                                <div id="error_txt_message" class="b-txt_fs_11 b-layout__txt_color_c10600 b-txt"></div>
                            </div>
                            <span class="b-shadow__icon b-shadow__icon_nosik"></span>
                        </div>
                    </div>
                </div>
                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5">
                    �� ����� 500 ��������.
                </div>
                <div class="b-buttons b-buttons_padtop_20">
                    <a href="javascript:void(0);" data-order-arbitrage-submit="true" 
                       class="b-button b-button_flat b-button_flat_green __tservices_orders_arbitrage_submit_label">
                        ������ ��������
                    </a>
                    <span class="b-layout__txt b-layout__txt_fontsize_11">&#160; ��� 
                        <a class="b-layout__link" data-order-arbitrage-close="true" href="javascript:void(0);">���������� ��������������</a>
                    </span>
                </div>
            </form>
        </div>
    </div>
    <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>