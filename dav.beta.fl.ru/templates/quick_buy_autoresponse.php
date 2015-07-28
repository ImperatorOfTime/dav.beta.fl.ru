<style type="text/css">
    #quick_ar_win_main{ height:auto !important; min-width:300px !important;}
    @media screen and (max-width: 600px){
    #quick_ar_win_main_ok{ width:90% !important; min-width:300px !important;}
    }
</style>

<iframe name="quick_ar_iframe" id="quick_ar_iframe" style="display: none;"></iframe>

<div id="quick_ar_win_main" class="b-shadow b-shadow_center b-shadow_width_460 b-shadow_pad_15_20 b-shadow_zindex_11 b-shadow_hide" style="display:block;">
    <div class="b-fon b-fon_bg_soap">
        <div class="b-layout__title b-layout__title_padbot_5">
            <span class="b-icon b-icon__soap b-icon_float_left b-icon_top_4"></span>������� ����������� �� ��������
        </div>
    </div>

    <div id="quick_mas_div_main" class="b-layout">

        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_10">��������� �������</div>
        <div class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_bold">���������� ����������� � <span class="b-layout__txt b-layout__txt_color_6db335" id="quickar_total">0</span></div>

        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_10 b-layout__txt_padtop_20">�������� ������ ��������</div>

        <div class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_bold">������ �� � <span class="b-layout__txt b-layout__txt_color_6db335" id="quickar_budget"></span></div>
        <div class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_bold">������������� � <span class="b-layout__txt b-layout__txt_color_6db335" id="quickar_category"></span></div>


        <input type="hidden" id="quick_mas_f_account_sum" value="<?= round($_SESSION['ac_sum'], 2)<0 ? 0 : round($_SESSION['ac_sum'], 2) ?>"/>

        <div class="b-layout__txt b-layout__txt_padtb_10 b-layout__txt_fontsize_15">����� � ������ ������</div>

        <div id="quick_mas_div_error" class="b-fon b-fon_margbot_20 b-fon_marglr_20 b-layout_hide">
            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeee"> 
                <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>
                <span id="quick_mas_div_error_txt">� ���������, � �������� ������ ��������� ������, � ������ �� ��� ��������. ���������� �������� ������ ��� ���.</span>
            </div>
        </div>

        <div class="b-layout__txt b-layout__txt_padleft_20 b-layout__txt_fontsize_11">
            ����� � ������: <span id="quick_ar_sum_pay"></span> ���.<br>
        </div>


        <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_padleft_20 b-layout__txt_fontsize_11">
            <span id="quick_mas_sum_span_4">
            <span id="quick_mas_sum_span_2">����� ����� (<span id="quick_mas_sum_span_7"></span> ���.)</span><span id="quick_mas_sum_span_3">���</span> ����� ������� � ������� �����, �� ��� 
            <span id="quick_mas_sum_account1" class="b-layout__bold">
                <?php setlocale(LC_NUMERIC,'en_US');?>
                <? if (round($_SESSION['bn_sum'] + $_SESSION['ac_sum'], 2) > 0) { ?>
                    <?= number_format(round(zin($_SESSION['ac_sum']),2), 2, ",", " "); ?>
                <? } else { ?>
                    0
                <? } ?>
            </span>
             ���.<br>
             <span id="quick_mas_sum_span_5">
            ������� (<span id="quick_mas_sum_span_6"></span> ���.) ��� ����� �������� ����� �� ��������:
            </span>
            </span>
            <span id="quick_mas_sum_span_1">�� �� ������ �������� ����� �� ��������:</span>
            <span id="quick_mas_sum_account2"></span>
        </div>


        <div id="quick_mas_block_1">
            <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10"> 
                <a class="b-button b-button__pm  b-button__pm_card" href="#" onClick="quickAR_process('dolcard'); return false;"><span class="b-button__txt">�����������<br>�����</span></a> 
                <a class="b-button b-button__pm  b-button__pm_yd" href="#" onClick="quickAR_process('ya'); return false;"><span class="b-button__txt">������.������</span></a> 
                <a class="b-button b-button__pm  b-button__pm_wm" href="#" onClick="quickAR_process('webmoney'); return false;"><span class="b-button__txt">WebMoney</span></a> 
            </div>
        </div>

        <div id="quick_mas_block_2" class="b-buttons">
            <div class="b-buttons b-buttons_padleft_20 b-buttons_padbot_10"> <a id="quickar_process_btn" class="b-button b-button_flat b-button_flat_green" href="#" onClick="quickAR_process('account'); return false;"></a> </div>
        </div>

        <div id="quick_mas_div_wait" class="b-layout__wait b-layout__txt_fontsize_15 b-layout__txt_color_<?= is_emp() ? '6db335' : 'fd6c30'?> b-layout_hide">
            <span id="quick_mas_div_wait_txt"></span> 
            <div class="b-layout__txt b-layout__txt_center b-layout__txt_padtb_10"><img src="/images/<?= is_emp() ? 'Green' : 'Orange'?>_timer.gif" width="80" height="20"></div>
            <span id="timer"></span>
        </div>

    </div>

    <span class="b-shadow__icon b-shadow__icon_close" onclick="$('quick_ar_overlay').setStyle('display', 'none');"></span>
</div>


<div id="quick_ar_win_main_ok" class="b-shadow b-shadow_center b-shadow_width_460 b-shadow_pad_15_20 b-shadow_zindex_11 b-shadow_bg_eeffe5 <?= $_GET['quickmas_ok'] && $_SESSION['quickmass_ok'] ? '' : 'b-shadow_hide' ?>">
    <div class="b-fon b-fon_bg_soap">
        <div class="b-layout__title b-layout__title_padbot_5"><span class="b-icon b-icon__soap b-icon_float_left b-icon_top_4"></span>�������� ������� ���������
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_1">� ������� � ������������ ������ ������������</div>
        </div>
    </div>    
    <div class="b-layout__txt b-layout__txt_padbot_15">
    ���������� ����������� � �������� � <span class="b-layout__txt b-layout__txt_color_6db335"><?=$_SESSION['quickmas_count_u']?></span>
    </div>
    <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_11">������� �� �������.<br>������ ��� �������� ������ �� ����� � ������� ������������!</div>
    <div class="b-buttons b-buttons_padbot_10">
        <a class="b-button b-button_flat b-button_flat_green" href="" onClick="$('quick_ar_win_main_ok').addClass('b-shadow_hide'); return false;">�������</a>&nbsp;&nbsp;&nbsp;
   </div>
   <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>
<? unset($_SESSION['quickmass_ok']); ?> 

<div id="quick_ar_overlay" class="b-shadow__overlay b-shadow_zindex_3" style="display: none;"></div>

<script type="text/javascript">
    function quickAR_process_continue() 
    {
    }

    function quickAR_process(type) 
    {
        quickAR_save_type = type;
        $('frm').submit();
    }

    function quickAR_show() 
    {
        var $budget = $('el-filter_budget').get('value') < 1?'�� ������':$('el-filter_budget').get('value') + '&nbsp;���.';
        var $total = $('el-total').get('value');
        var $sum = $total * <?=autoresponse::$config['price']?>;

        $('quickar_total').set('html', $total);
        $('quickar_budget').set('html', $budget);
        $('quickar_category').set('html', $$('input[name=filter_category]')[0].get('value'));
        $('quickar_process_btn').set('html', '�������� ' + $sum + ' ���.');

        $('quick_ar_sum_pay').set('html', $sum);

        $('quick_ar_win_main').removeClass('b-shadow_hide');
        $('quick_ar_overlay').setStyle("display", "");

        $('frm').set('target', 'quick_ar_iframe');
    }
</script>