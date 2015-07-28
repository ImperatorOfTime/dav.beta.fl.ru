<?php
/**
 * ����-�����. ������.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
?>

<h2 class="b-layout__title b-layout__title_padbot_20">���������������� ������� / ����-�����</h2>

<div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_padbot_5">
<?php /*
// ���� ����
echo $site == 'words' ? '<a href="/siteadmin/stop_words/?site=regex" class="lnk-dot-666">' : '';
echo '����������� ���������';
echo $site == 'words' ? '</a>' : '';
echo '&nbsp;|&nbsp;';
echo $site == 'regex' ? '<a href="/siteadmin/stop_words/?site=words" class="lnk-dot-666">' : '';
echo '�������������� �����';
echo $site == 'regex' ? '</a>' : '';
*/?>
</div>

<?php 
// ����� ��������� �� ������ ��� ������� ��� ����������
if ($_SESSION['admin_stop_words_success']) { 
    unset( $_SESSION['admin_stop_words_success'] );
?>
  <div>
    <img src="/images/ico_ok.gif" alt="" border="0" height="18" width="19"/>&nbsp;&nbsp;��������� �������.
  </div>
  <br/><br/>
<?php } if ($error) print(view_error($error).'<br/>'); ?>

  
<form id="form_stop_words" method="post">
    <input type="hidden" name="site" value="<?=$site?>">
    <input type="hidden" name="cmd" value="go">
<?php
if ( $site == 'regex' ) {
    /*
    // ����������� ���������
    
?>
    <input type="hidden" name="action" id="action" value="">
    <div class="b-textarea">
        <textarea class="b-textarea__textarea" name="regex" id="regex" cols="80" rows="5"><?=  $sStopRegex?></textarea>
    </div>

    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20">
        ������ ����� ��������� &mdash; � ����� ������. ��� ���������� ����� �������� �� <?=CENSORED?>.<br/>
        ������� ���������� ��������� ������ �� �������� ��������� �����.
    </div>
    
    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_padbot_5">�������� �����</div>
    
    <div class="b-textarea">
        <textarea class="b-textarea__textarea" name="test" id="test" cols="80" rows="5"><?=  $sTestText?></textarea>
    </div>
    
    <div class="b-layout__txt b-layout__txt_fontsize_11 <?php if ( !empty($sUserMode) || !empty($sAdminMode) ) { ?>b-layout__txt_padbot_20<?php } ?>">
        �� ��������� ������ ����� ����������� ����������� ��������� ������������� ��������� �� �� �������� ������ � ���������, ��� ������������ ��� ��������� ������.
        � ��������� ������ ������������ ����� ����� ����������� � ������ ����������.
    </div>
    
    <?php if ( !empty($sUserMode) ) { ?>
        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_padbot_5">������ ��� ������������:</div>
        <div class="b-layout__txt b-layout__txt_fontsize_11 <?php if ( !empty($sAdminMode) ) { ?>b-layout__txt_padbot_20<?php } ?>"><?=reformat( $sUserMode, 100, 0, 1 )?></div>
    <?php } ?>
    
    <?php if ( !empty($sAdminMode) ) { ?>
        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_padbot_5">������ ��� ��������������:</div>
        <div class="b-layout__txt b-layout__txt_fontsize_11"><?=reformat( $sAdminMode, 100, 0, 1 )?></div>
    <?php } ?>
    
    <div class="b-buttons b-buttons_padtop_40">
        <a onclick="stop_words.regexTest();" class="b-button b-button_rectangle_color_green" href="javascript:void(0);">
            <span class="b-button__b1">
                <span class="b-button__b2">
                    <span class="b-button__txt">�����������</span>
                </span>
            </span>
        </a>

        &nbsp;&nbsp;

        <a href="javascript:void(0);"  onclick="stop_words.regexSubmit();" class="b-button b-button_rectangle_color_green">
            <span class="b-button__b1">
                <span class="b-button__b2">
                    <span class="b-button__txt">���������</span>
                </span>
            </span>
        </a>
        <span class="b-buttons__txt b-buttons__txt_padleft_10">���</span>
        <a href="/siteadmin/stop_words/?site=<?=$site?>" class="b-buttons__link b-buttons__link_color_c10601">�������� ���������</a>
    </div>

    
<?php
*/
}
else {
    
    // �������������� �����
    
?>
    <div class="b-textarea">
        <textarea class="b-textarea__textarea" name="words" id="words" cols="80" rows="5"><?=  $sStopWords?></textarea>
    </div>

    <div class="b-layout__txt b-layout__txt_fontsize_11">����� �������. ��� ����� ����� �������� ������ ��� ������������� ����������������� ��������.</div>

    <div class="b-buttons b-buttons_padtop_40">
        <a href="javascript:void(0);" onclick="stop_words.wordsSubmit();" class="b-button b-button_flat b-button_flat_green">���������</a>
        <span class="b-buttons__txt b-buttons__txt_padleft_10">���</span>
        <a href="/siteadmin/stop_words/?site=<?=$site?>" class="b-buttons__link b-buttons__link_color_c10601">�������� ���������</a>
    </div>

<?php

}

?>
</form>