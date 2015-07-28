<form method="get" id="arbitrage_form" data-arbitrage-form="1">
    <input type="hidden" name="order_id" value="<?= $order_id ?>" />
    <div class="b-layout__txt b-layout__txt_padbot_15">
        <div class="b-layout__left b-layout__left_width_300 b-layout__left_float_left">
            <span class="b-layout__txt b-layout__txt_padtop_10">����� ������� �����������</span>
            <div class="b-combo b-combo_inline-block">
                <div class="b-combo__input b-combo__input_width_50">
                    <input class="b-combo__input-text validate-numeric" name="price" type="text" size="6" value="0" id="arbitrage_sum_frl">
                </div>
            </div>                        
            <span class="b-layout__txt b-layout__txt_padtop_10">���.</span>
        </div>
        <span class="b-layout__txt b-layout__txt_padtop_10">
            <input type="checkbox" name="allow_fb_frl" id="allow_fb_frl" checked="checked" />
            <label for="allow_fb_frl">����������� ����� �������� �����</label>
        </span>
        
    </div>
    <div class="b-layout__txt b-layout__txt_padbot_15">
        <div class="b-layout__left b-layout__left_width_300 b-layout__left_float_left">
            ����� �������� ��������� <strong id="arbitrage_sum_emp"><?= $price ?></strong> ���.
        </div>
        <span class="b-layout__txt b-layout__txt_padtop_10">
            <input type="checkbox" name="allow_fb_emp" id="allow_fb_emp" checked="checked" />
            <label for="allow_fb_emp">�������� ����� �������� �����</label>
        </span>
    </div>
    
    <div class="b-buttons b-buttons_padbot_15">
        <a class="b-button b-button_flat b-button_flat_green" 
           href="javascript:void(0);" 
           onclick="window.arbitrage_form.submit();" 
           id="arbitrage_apply">
            ������� �������
        </a>
        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; ��� &#160;</span>
        <a class="b-button" href="javascript:void(0);" id="arbitrage_cancel">�������� ��������</a>
    </div>
</form>