<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/promo.common.php");
$xajax->printJavascript('/xajax/');
?>
<div class="b-menu b-menu_crumbs b-layout__right b-layout__right_float_right b-layout__right_width_72ps b-menu_margbot_30">
    <ul class="b-menu__list">
        <li class="b-menu__item"><a href="/service/" class="b-menu__link">��� ������ �����</a>&nbsp;&rarr;&nbsp;</li>
    </ul>
</div>
<table class="b-layout__table b-layout__table_width_full b-layout__table_clear_both">
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_center" colspan="3">
            <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padleft_100 b-layout__txt_relative">
                <a class="b-layout__link" style="position:absolute; right:15px; top:55px;" href="/bezopasnaya-sdelka/?site=calc" target="_blank">�����������</a><img class="b-layout__pic" src="/images/bs/1.png" alt="" width="218" height="105" />
            </div>
            <h1 class="b-page__title">���������� ������</h1>
        </td>
    </tr>
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_center b-layout__one_width_33ps">
            <div class="b-layout__txt b-layout__txt_color_fd6c30 b-layout__txt_fontsize_46 b-layout__txt_bold"><?= $roleStr === 'frl' ? '0' : '9.9-13.9' ?>%</div>
            <div class="b-layout__txt"><?= $roleStr === 'frl' ? '�������� ��� ����������' : '�������� ��� ������������' ?></div>
            <? if($roleStr !== 'frl') { ?>
            <div class="b-layout__txt b-layout__txt_fontsize_11" style="color:#000;">(������� ��� �������� �� �������� �����������)</div>
            <? }//if?>
        </td>
        <td class="b-layout__one b-layout__one_center">
            <div class="b-layout__txt b-layout__txt_color_fd6c30 b-layout__txt_fontsize_46 b-layout__txt_bold"><?= sbr_stages::MIN_COST_RUR; ?> ���.</div>
            <div class="b-layout__txt">����������� ������ �������</div>
        </td>
        <td class="b-layout__one b-layout__one_center b-layout__one_valign_bot b-layout__one_width_33ps">
            <img class="b-layout__pic" src="/images/bs/wm.png" alt="Webmoney" title="Webmoney"  />&#160;&#160;&#160;
            <img class="b-layout__pic" src="/images/bs/pskb.png" alt="���-������� ����" title="���-������� ����"  />&#160;&#160;&#160;
            <img class="b-layout__pic" src="/images/bs/pk.png" alt="����������� �����" title="����������� �����"  />
            <div class="b-layout__txt b-layout__txt_padtop_10"><?= $roleStr === 'frl' ? '������� ����� �������� ������ �����' : '�������� ������ ������� ������' ?></div>
            <? if($roleStr !== 'frl') { ?><div class="b-layout__txt">&nbsp;</div><? }//if?>
        </td>
    </tr>
</table>
<div class="b-promo__bs-arrow"></div>
<? if ($roleStr === 'frl') { ?>
<table class="b-layout__table b-layout__table_width_full">
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_padright_20"><div class="b-promo__bs b-promo__bs_1"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">��� ���������� ������������?</h3>
            <div class="b-layout__txt">� ��� ���� ���������� ����, ����� �� ��������� ������, � ������ ��� � �� ��������?</div>
        </td>
        <td class="b-layout__one b-layout__one_padright_20 b-layout__one_padleft_50"><div class="b-promo__bs b-promo__bs_4"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">���������� �� ������</h3>
            <div class="b-layout__txt">���� �������� ���������� ��������, �� ��� ����� �������� ������ �� ����������� ������.</div>
        </td>
    </tr>
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_padright_20"><div class="b-promo__bs b-promo__bs_2"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">������� ����������� ��������?</h3>
            <div class="b-layout__txt">����������� ������� ������, ���������� ���������������� ����������� ����������?</div>
        </td>
        <td class="b-layout__one b-layout__one_padright_20 b-layout__one_padleft_50"><div class="b-promo__bs b-promo__bs_5"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">��������� ��������</h3>
            <div class="b-layout__txt">��� ��������� � �� �������� ������ �� ��������� ��������. ���� ��� �� ����� ���������� ����� �������, �������������� ����������� �� ������������� � ������ ��.</div>
        </td>
    </tr>
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_padright_20"><div class="b-promo__bs b-promo__bs_3"></div></td>
        <td class="b-layout__one b-layout__one_width_50ps">
            <h3 class="b-layout__h3">��� �� �������� �������?</h3>
            <div class="b-layout__txt">� ��� ��������� ������� � ������������ �������?</div>
        </td>
        <td class="b-layout__one b-layout__one_padright_20 b-layout__one_padleft_50"><div class="b-promo__bs b-promo__bs_6"></div></td>
        <td class="b-layout__one b-layout__one_width_50ps">
            <h3 class="b-layout__h3">�������� ��� �� ����� � ������</h3>
            <div class="b-layout__txt">���� �������������,������������ ���� �� �������������� ������, �������� ���� (���), ������� ����������� ����� � ������. ����� ������������� ��������� �������� ������.</div>
        </td>
    </tr>
</table>
<? } else { ?>
<table class="b-layout__table b-layout__table_width_full">
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_padright_20"><div class="b-promo__bs b-promo__bs_1"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">��� ���������� �����������?</h3>
            <div class="b-layout__txt">� ��� ���� ���������� ����, ����� �� ������ ������, � ������ ��� � �� ��������?</div>
        </td>
        <td class="b-layout__one b-layout__one_padright_20 b-layout__one_padleft_50"><div class="b-promo__bs b-promo__bs_4"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">���������� �� ������</h3>
            <div class="b-layout__txt">���� ��������� �� ��������� � ��������, �� �� ������ ��� ������.</div>
        </td>
    </tr>
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_padright_20"><div class="b-promo__bs b-promo__bs_2"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">������� ������� �� �������?</h3>
            <div class="b-layout__txt">������������, ��� ����� ����� ������� ����� �������?</div>
        </td>
        <td class="b-layout__one b-layout__one_padright_20 b-layout__one_padleft_50"><div class="b-promo__bs b-promo__bs_5"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">������������� ��������</h3>
            <div class="b-layout__txt">������, ����������� ����������� � � ����, &mdash; ������������ ������� ������ ����� ����������.</div>
        </td>
    </tr>
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_padright_20"><div class="b-promo__bs b-promo__bs_3"></div></td>
        <td class="b-layout__one b-layout__one_width_50ps">
            <h3 class="b-layout__h3">������������ � ��������?</h3>
            <div class="b-layout__txt">�� �������, ��� ������ ����� ��������������� �� � �����<br />���������?</div>
        </td>
        <td class="b-layout__one b-layout__one_padright_20 b-layout__one_padleft_50"><div class="b-promo__bs b-promo__bs_6"></div></td>
        <td class="b-layout__one b-layout__one_width_50ps">
            <h3 class="b-layout__h3">������� �� ���������</h3>
            <div class="b-layout__txt">��������� ������� ������� ������ ����� ����, ��� �� ������� ������. �������������� ����� �� ��������� ����� ��������� ������������ ���.</div>
        </td>
    </tr>
</table>
<? } ?>
<div class="b-promo__bs-arrow"></div>
<div id="promo-stats">
    <? include($_SERVER['DOCUMENT_ROOT'] . '/promo/sbr/new/tpl.stats.php') ?>
</div>
<div class="b-promo__bs-arrow"></div>
<table class="b-layout__table b-layout__table_width_400 b-layout__table_center">
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_center">
            <div class="b-layout__txt b-layout__txt_fontsize_34">��� ��� ��������</div>
            <div class="b-layout__txt b-layout__txt_padbot_30">���������� ������ &mdash; ��� ����� � �������.</div>
            <img class="b-layout__pic" src="/images/bs/3.png" alt="" width="461" height="79" />
            <? if ($roleStr === 'frl') { ?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_left b-layout__txt_padtop_30"><div class="b-promo__num">1</div>&#160;&#160;������������ ���������� ��� ��������� ������.</div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_left"><div class="b-promo__num">2</div>&#160;&#160;�� �������������� ������� �������������� � ����� ���� ��������.</div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_left"><div class="b-promo__num">3</div>&#160;&#160;�������� �������� ����������� ���� ������.</div>
            <div class="b-layout__txt b-layout__txt_left"><div class="b-promo__num">4</div>&#160;&#160;�� ��������� ��� �������.</div>
            <? } else { ?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_left b-layout__txt_padtop_30 b-layout__txt_padleft_100"><div class="b-promo__num">1</div>&#160;&#160;����������� ������ ��� ������� � �������� �����������.</div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_left b-layout__txt_padleft_100"><div class="b-promo__num">2</div>&#160;&#160;�������������� ������ �� ����������� ����� � �����.</div>
            <div class="b-layout__txt b-layout__txt_nowrap b-layout__txt_left b-layout__txt_padleft_100"><div class="b-promo__num">3</div>&#160;&#160;��������� ����������� ������. � ������ �������� �� ��������� ������� ����������.</div>
                <? /*if (get_uid(0)) { ?>
                <div class="b-buttons b-buttons_padtop_40 b-buttons_padbot_10 b-buttons_center">
                    <a class="b-button b-button_big_rectangle_color_green" href="/bezopasnaya-sdelka/?site=new">
                        <span class="b-button__b1">
                            <span class="b-button__b2">
                                <span class="b-button__txt">������� ������</span>
                            </span>
                        </span>
                    </a>
                </div>
                <? } */?>
            <? } ?>
        </td>
    </tr>
</table>
<div class="b-promo__bs-arrow"></div>
<? if ($feedbacksFromEmp || $feedbacksFromFrl) { ?>
<div id="promo-feedbacks">
    <? include($_SERVER['DOCUMENT_ROOT'] . '/promo/sbr/new/tpl.feedbacks.php') ?>
</div>
<script>
    (function () {
        
        var
            $newFeedbacksBtn, $feedbacks,
            needUpdate; // ���� true - ������ ���� �������� ������
        
        window.addEvent('domready', function() {
            
            $newFeedbacksBtn = $('new-feedbacks');
            $feedbacks = $('promo-feedbacks');
            
            window.PromoSBR = {};
            PromoSBR.newFeedbacksLoaded = newFeedbacksLoaded;

            $newFeedbacksBtn.addEvent('click', newFeedbacks);

        });
        
        function newFeedbacks () {
            // �������� ������������
            $feedbacks.set('morph', {duration: 500});
            $feedbacks.get('morph').addEvent('complete', hidingComplete);
            $feedbacks.morph({'opacity': 0});
            
            needUpdate = true;
        }
        
        function hidingComplete () {
            if (!needUpdate) {
                return;
            }
            xajax_getPromoFeedbacks();
            needUpdate = false;
        }
        
        function newFeedbacksLoaded () {
            // �������� ���������
            $feedbacks.morph({'opacity': 1});
        }
        
    })()
</script>
<div class="b-buttons b-buttons_center">
    <a href="javascript:void(0)" id="new-feedbacks" class="b-button b-button_flat b-button_flat_green">��� ������</a>          
</div>
<? } ?>
<div class="b-promo__bs-arrow"></div>
<table class="b-layout__table b-layout__table_width_400 b-layout__table_center">
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_center">
            <div class="b-promo__bs b-promo__bs_7"></div>
            <div class="b-layout__txt b-layout__txt_fontsize_34 b-layout__txt_padbot_20">�������� �������?</div>
            <? if ($roleStr === 'frl') { ?>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397488-kak-frilanseru-soglasitsya-na-bezopasnuyu-sdelku/">��� ����������� �� ������</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397433-kalkulyator-bezopasnoj-sdelki/">��� ���������� ���� �������</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397422-poryadok-dejstvij-pri-rabote-cherez-bezopasnuyu-sdelku/">����� ������� ������ ����� ���������� ������</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397435-dokumentooborot-bezopasnoj-sdelki/">��� �������� �������</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397431-chto-takoe-arbitrazh-servisa-bezopasnaya-sdelka-i-kak-k-nemu-obratitsya/">��� �������� ��������</a></div>
            <div class="b-layout__txt"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397425-zapolnenie-stranitsyi-finansyi-dlya-rabotyi-cherez-bezopasnuyu-sdelku/">����� ������ ����� ��� ������ ����� ���������� ������</a></div>
            <? } else { ?>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397434-kak-nachat-bezopasnuyu-sdelku/">��� ������ ���������� ������</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397433-kalkulyator-bezopasnoj-sdelki/">��� ���������� ������ ������</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397428-kak-zarezervirovat-dengi-dlya-bezopasnoj-sdelki/">��� ��������������� ������ ��� ������</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397432-limityi-na-rezervirovanie-deneg-bankovskoj-kartoj-po-bezopasnoj-sdelke/">������ �� ������ �� ���������� ������</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397435-dokumentooborot-bezopasnoj-sdelki/">��� �������� �������</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397431-chto-takoe-arbitrazh-servisa-bezopasnaya-sdelka-i-kak-k-nemu-obratitsya/">��� �������� ��������</a></div>
            <div class="b-layout__txt"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397440-upravlenie-bezopasnoj-sdelkoj-dlya-rabotodatelya/">��� ��������� �������</a></div>
            <? } ?>
        </td>
    </tr>
</table>
