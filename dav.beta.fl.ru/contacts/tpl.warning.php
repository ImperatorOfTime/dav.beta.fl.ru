<?php if(empty($_COOKIE['hack_warn_1']) || empty($_COOKIE['hack_warn_2']) || empty($_COOKIE['warning_sbr'])) { ?>

<script type="text/javascript">
function hideWarning(warning_code) {
    var exdate=new Date();
    exdate.setDate(exdate.getDate()+365);
    
    switch (warning_code) {
        case 'WARNING_SBR':
            $('warning_sbr').destroy();
            document.cookie="warning_sbr=1; expires="+exdate.toGMTString();
            break;
        case 'WARNING_HACK_VIRUS':
            $('hack_warn_1').destroy();
            document.cookie="hack_warn_1=1; expires="+exdate.toGMTString();
            break;
        case 'WARNING_HACK_MSG':
            $('hack_warn_2').destroy();
            document.cookie="hack_warn_2=1; expires="+exdate.toGMTString();
            break;
    }
    if($('hack_br') != undefined) $('hack_br').destroy();
    var child = $('hack_warn_all').getChildren();
    if (child.length == 0) {
        $('hack_warn_all').destroy();
    }
}
</script>

<div id="hack_warn_all">
    
   
    <? $temporaryHide = true; if (empty($_COOKIE['hack_warn_1']) && $temporaryHide == false) { //�������� ��������?>

<div id="hack_warn_1" class="b-fon b-fon_bg_fcc b-fon_pad_15_15_0">
    <b class="b-fon__b1"></b>
    <b class="b-fon__b2"></b>
    <div class="b-fon__body b-fon__body b-fon__body_pad_5_10">
                <a href="javascript:void(0)" onclick="hideWarning('WARNING_HACK_VIRUS')" title="�������"><img src="/images/btn-remove2.png" alt="�������" style="float:right;" /></a>
        <div class="b-fon__txt b-fon__txt_padbot_5 b-fon__txt_bold b-fon__txt_color_c10601 b-fon__txt_fontsize_11">�������� ��������!</div>
        <div class="b-fon__txt b-fon__txt_fontsize_11">
                    ���������� ������ ��������� � ����� �������� �������. ����������, ����������� ���������� ������� ������������ ��������� � ���������� ��� ����������� �������������� �����, � ��� ����� � �� �������� �������������.
                    ����������� <a class="b-fon__link" href="/blogs/view.php?tr=616605">�����</a>.
        </div>
    </div>
    <b class="b-fon__b2"></b>
    <b class="b-fon__b1"></b>
</div>
    <?php }//if?>
        
    <?php /*if (empty($_COOKIE['hack_warn_2'])) { ?>
    
<div id="hack_warn_2" class="b-fon b-fon_bg_eefee5 b-fon_pad_15_15_0">
    <b class="b-fon__b1"></b>
    <b class="b-fon__b2"></b>
    <div class="b-fon__body b-fon__body b-fon__body_pad_5_10">
                <a href="javascript:void(0)" onclick="hideWarning('WARNING_HACK_MSG')" title="�������"><img src="/images/btn-remove2.png" alt="�������" style="float:right;" /></a>
        <div class="b-fon__txt b-fon__txt_padbot_5 b-fon__txt_bold b-fon__txt_color_6db335 b-fon__txt_fontsize_11">�������� ��������!</div>
        <div class="b-fon__txt b-fon__txt_fontsize_11">
                    ���������� � ��� �� ������ �������� ������� ��� �����, ������� �����������, ������� ������� � ���������, � ����� ����������� �������� ��������� ��������� ����� ������ ���������� Free-lance.ru. 
                    �� ����� ����� ������ �� ������ <img src="/images/team.gif" alt="" style="margin-bottom:-2px" />, ������� ��������� ����� � ������ ������������, ����������� ��� ���������. 
                    ��������� <a class="b-fon__link" href="/blogs/view.php?tr=652908&b=5">�����</a>.
        </div>
    </div>
    <b class="b-fon__b2"></b>
    <b class="b-fon__b1"></b>
</div>
    <?php }//if*/?>
</div>
<?php }//if?>