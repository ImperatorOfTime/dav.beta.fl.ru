<? 
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/buttons/multi_buttons.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/LocalDateTime.php");

$multi = new multi_buttons();

if($stage->version != $stage->frl_version) {// ��������� ��� �� ����������, ��� ������� ����� ������ ����
    $frl_version = $stage->getVersion($stage->frl_version, $stage->data);
    $work_time = intval($frl_version['work_time']);
    $start_time = $frl_version['start_time'];
} else {
    $work_time = intval($stage->work_time);
    $start_time = $stage->start_time;
}
$work_time = $work_time < 0 ? 0 : $work_time; // ���� ������ ��� ��������� ���� ��� 5 ���� � ������� ������ ���������
$cdate = new LocalDateTime(date('d.m.Y', strtotime($start_time . ' + ' . $work_time . 'day')));
$cdate->getWorkForDay(pskb::PERIOD_EXP);
$days      = ($work_time + $cdate->getCountDays()) . "day";
$overtime  = strtotime($start_time . ' + ' . $days);

if($sbr->data['lc_id'] > 0) {
    $overtime = strtotime($sbr->data['dateEndLC'] . ' - ' . pskb::ARBITRAGE_PERIOD_DAYS . " day");
    // ��, �� �� ������� ���
    if(date('w', $overtime) == 0 || date('w', $overtime) == 6) {
        $d = date('w', $overtime) == 6 ? 1 : 2;
        $overtime = $overtime - ($d * 3600* 24);
    }
} else {
    $overtime = null;
}
// ���� � ���������, �������� ������ ������� ������, ���������� �� ������� ���
if($stage->data['status'] == sbr_stages::STATUS_INARBITRAGE || $stage->data['status'] == sbr_stages::STATUS_ARBITRAGED) return;

// ���������� ��� ��������� ������ ���� ��� @todo ����� ��� ���� ���������
$edit = new buttons('�������� �������', null, 'edit');
$edit->setLink("/" . sbr::NEW_TEMPLATE_SBR . "/?site=editstage&id={$stage->id}");
$edit->addEvent("onclick", "window.location = '/" . sbr::NEW_TEMPLATE_SBR . "/?site=editstage&id={$stage->id}';");

$cancel = new buttons('�������� ������', 'red', 'cancel');
$cancel->addEvent("onclick", "if(confirm('�������� ������?')) { submitForm($('actionSbrForm'), {action: 'status_action', cancel:1}); }");

$draft = new buttons('����������, ��������� ������ � ��������', 'red', 'action_stage');
$draft->addEvent("onclick", "submitForm($('actionStageForm'), {action:'draft'})");

$arbitrage = new buttons('���������� � ��������', 'red', 'arbitrage');
$arbitrage->addEvent("onclick", "toggle_arb();");

$complete = new buttons('������� ������', null, 'complete');
$complete->setLink("/" . sbr::NEW_TEMPLATE_SBR . "/?site=Stage&id={$stage->id}&event=complete");

$pause = new buttons('��������� �� �����', null, 'pause');
$pause->addEvent("onclick", "view_sbr_popup('pause_confirm');");
//$pause->addEvent("onclick", "submitForm($('actionStageForm'), {action: 'change_status', status:" . sbr_stages::STATUS_FROZEN . "});");
      
$inwork = new buttons('������� � ������', null, 'action_stage');
$inwork->addEvent("onclick", "submitForm($('actionStageForm'), {action: 'change_status', status:" . sbr_stages::STATUS_PROCESS . "});");

$resend = new buttons('��������� ������', null, 'action_stage');
$resend->addEvent("onclick", "submitForm($('actionStageForm'), {action: 'resolve_changes', resend:1});");

$rollback = new buttons('�������� ���������', 'red', 'action_stage');
$rollback->addEvent("onclick", "submitForm($('actionStageForm'), {action: 'resolve_changes', cancel:1});");
                
$reserved = new buttons('��������������� ������', null, 'reserved');
$reserved->setLink("/" . sbr::NEW_TEMPLATE_SBR . "/?site=reserve&id={$sbr->id}");

switch($sbr->status) {
    case sbr::STATUS_NEW:
        $draft->setName("��������� ������ � ��������");
        $multi->addButton($cancel);
        $multi->addButton($edit);
        $multi->addButton($draft);
        
        break;
    case sbr::STATUS_CHANGED:
        if($sbr->data['reserved_id']) { // ������ ���������������, ��� ��� ����������� �� �������� �����
            if($stage->data['status'] == sbr_stages::STATUS_NEW) {
                //if($stage->num > 0 && $sbr->stages[$stage->num-1]->data['status'] == sbr_stages::STATUS_INARBITRAGE) {
                //    $inwork->setName('��������� � ������');
                //    $multi->addButton($inwork);
                //}
                break; // ���� ���� �� ����� � ������ ��������������� � ���� ������ ������ ������ ������???
            }
            // �� ������� �� ����� - � ������ -- ����� �������� ������ 1 ���, ������ ���� ��������� �� ���������� ������ ����� ������ ������
            if($stage->data['status'] == sbr_stages::STATUS_PROCESS) { 
                
                $multi->addButton($arbitrage);
                
                if($stage->v_data['status'] != sbr_stages::STATUS_NEW) {
                    $multi->addButton($pause);
                }
                $multi->addButton($edit);
                
                break;
            }
            
            if($stage->data['status'] == sbr_stages::STATUS_FROZEN) { // ���� �� ����� ���� ���������� ����������� � ���� �����
                
                //$multi->addButton($inwork);
                $multi->addButton($arbitrage);
                $multi->addButton($edit);
            }
            
        } else { // ������ �� ���������������
            
            $multi->addButton($cancel);
            //$multi->addButton($draft);
            $multi->addButton($edit);
        }
        
        break;
    case sbr::STATUS_PROCESS:
        if($sbr->data['reserved_id']) { // ������ ���������������
            
            if($stage_changed) { // ���������� ������� ���������
                
                $multi->addButton($edit);
                $multi->addButton($resend);
                $multi->addButton($rollback);
                $multi->addButton($arbitrage);
                
                break;
            }
            
            // ���� ���� �� ����� � ������ ��������������� � ���� ������ ������ ������ ������??? ����� ���������� ����������� ����� �� ������������� ������ � ������
            if($stage->data['status'] == sbr_stages::STATUS_NEW) {
                //if($stage->num > 0 && $sbr->stages[$stage->num-1]->data['status'] == sbr_stages::STATUS_INARBITRAGE) {
                //    $inwork->setName('��������� � ������');
                //    $multi->addButton($inwork);
                //}
                break; 
            }
            if($stage->data['status'] == sbr_stages::STATUS_PROCESS) { // ���� � ������
                
                $multi->addButton($complete);
                $multi->addButton($arbitrage);
                $multi->addButton($pause);
                $multi->addButton($edit);
            }
            
            if($stage->data['status'] == sbr_stages::STATUS_FROZEN) { // ���� �� �����
                
                $multi->addButton($inwork);
                $multi->addButton($arbitrage);
                $multi->addButton($complete);
                $multi->addButton($edit);
            }
            
        } elseif($stage_changed) { // ����������� ��������� �� ���������
            $edit->setName('�������� ������� ������');
            
            $multi->addButton($edit);
            $multi->addButton($resend);
            $multi->addButton($rollback);
            //$multi->addButton($draft);
            $multi->addButton($cancel);

        } elseif($sbr_changed) { // ���� ����� �� ������ ������� ������� �� ������������, ���� ������ �� ���������������
            
            $multi->addButton($cancel);
            //$multi->addButton($draft);
            $multi->addButton($edit);
            
        } else { // ������ ��� �� ���������������
            
            $multi->addButton($reserved);
            //$multi->addButton($draft);
            $multi->addButton($cancel);
            $multi->addButton($edit);
        }
        break;
    case sbr::STATUS_REFUSED:
    case sbr::STATUS_CANCELED:
        $draft->setName('��������� ������ � ��������');
        
        $edit->setLink("/sbr/?site=edit&id={$sbr->id}");
        
        $multi->addButton($edit);
        $multi->addBUtton($draft);
        
        break;
}
// ���� ����� ����� #0020166 #0023680
if(time() > $overtime && $overtime !== null) {
    $multi->removeButton($arbitrage);
}
 
// ������� ��������������� ������
$multi->view();

// ���� ���� ������ ���������
if($multi->isButton('arbitrage')) {
    include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/arbitrage.php");
}

// ���� ���� ������ ������ ���
if($multi->isButton('cancel')) {
    ?>
    <form id="actionSbrForm" action="?id=<?= $sbr->id;?>" method="post">
    	<div>
            <input type="hidden" name="cancel" value="" />
            <input type="hidden" name="id" value="<?= $sbr->id;?>" />
            <input type="hidden" name="action" value="" />
        </div>
    </form>
    <?
}

// ���� ���� ������ ��������� �� �����
if($multi->isButton('pause') ) {
    $dateMaxLimit = "date_max_limit_" . date('Y_m_d', strtotime('+ 30 days'));
    $dateMinLimit = "date_min_limit_" . date('Y_m_d', strtotime('+ 1 day'));
    ?>
    <div class="i-shadow i-shadow_zindex_110" id="pause_confirm">
        <div class="b-shadow b-shadow_center b-shadow_width_350 b-shadow_hide">
            <div class="b-shadow__right">
                <div class="b-shadow__left">
                    <div class="b-shadow__top">
                        <div class="b-shadow__bottom">
                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
                                <h2 class="b-shadow__title b-shadow__title_padbot_10">����� �� �����</h2>
                                <div class="b-shadow__txt b-shadow__txt_padbot_20 b-shadow__txt_fontsize_11">���������� ���� ����� � ����� �� ����� ����</div>

                                <div class="b-layout__txt b-layout__txt_padtop_4 b-layout__txt_inline-block b-layout__txt_width_20">��</div>
                                <div class="b-combo b-combo_inline-block b-combo_padbot_20">
                                    <div class="b-combo__input b-combo__input_multi_dropdown show_all_records use_scroll b-combo__input_visible_height_200 b-combo__input_width_170 b-combo__input_arrow_yes b-combo__input_init_listPauseDays drop_down_default_7">
                                        <input class="b-combo__input-text" id="count_pause_days" name="count_pause_days" type="text" size="80" value="7 ����" onchange="changePauseDays();"/>
                                    </div>
                                </div>
                                <div class="b-layout__txt">
                                    <div class="b-layout__txt b-layout__txt_padtop_4 b-layout__txt_inline-block b-layout__txt_width_20">��</div>
                                    <div class="b-combo b-combo_inline-block">
                                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_170 b-combo__input_arrow-date_yes use_past_date <?= $dateMinLimit; ?> <?= $dateMaxLimit ?>">
                                            <input class="b-combo__input-text" id="pause_date" name="pause_date" type="text" size="80" value="<?= date("d.m.Y", strtotime('+7day')); ?>" onchange="changePauseDays(1);"/>
                                            <span class="b-combo__arrow-date"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="b-buttons b-buttons_padleft_25 b-buttons_padtb_20">
                                    <a class="b-button b-button_flat b-button_flat_green" href="javascript:void(0)" onclick="submitForm($('actionStageForm'), {action: 'change_status', status: '<?= sbr_stages::STATUS_FROZEN?>', days: $('count_pause_days_db_id').get('value')});" style=" overflow:visible;">�����</a>
                                </div>
                                <div class="b-shadow__txt b-shadow__txt_fontsize_11">������������ ������������ ����� &mdash; 30 ����������� ����. ������ �� ������ ����� ������������� ������������ �� ��������� ������������ �����.</div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <a href="javascript:void(0);" onclick="$('pause_confirm').getElement('.b-shadow').addClass('b-shadow_hide'); $('b-shadow_sbr__overlay').dispose(); return false;"><span class="b-shadow__icon b-shadow__icon_close"></span></a>
        </div>
    </div>
    <?
}

// ���� ���� ������ ������� ������
if($multi->isButton('complete') ) {
    ?>
    <div class="i-shadow i-shadow_zindex_110" id="completed_confirm">
        <div class="b-shadow b-shadow_hide b-shadow_center" >
            <div class="b-shadow__right">
                <div class="b-shadow__left">
                    <div class="b-shadow__top">
                        <div class="b-shadow__bottom">
                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20 b-layout">
                                <div class="b-shadow__txt b-shadow__txt_padbot_5">�� �������, ��� ������ ������� ������?</div> 
                                <div class="b-shadow__txt b-shadow__txt_padbot_10">�������� ��������: �������� �������� ����� ����������.<br />����������� ������� ������ �� ������ ���� ����������� ������.</div>
                                <div class="b-buttons ">
                                    <a href="javascript:void(0)" onclick="submitForm($('actionStageForm'), {action: 'change_status', status: '<?= sbr_stages::STATUS_COMPLETED?>'});" class="b-button b-button_flat b-button_flat_green">������� ������</a>
                                    <span class="b-buttons__txt">&#160;&#160;&#160;���</span>
                                    <a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript:void(0)" onclick="$('completed_confirm').getElement('.b-shadow').addClass('b-shadow_hide'); $('b-shadow_sbr__overlay').dispose(); return false;">��������</a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="b-shadow__tl"></div>
            <div class="b-shadow__tr"></div>
            <div class="b-shadow__bl"></div>
            <div class="b-shadow__br"></div>
            <a href="javascript:void(0);" onclick="$('completed_confirm').getElement('.b-shadow').addClass('b-shadow_hide'); $('b-shadow_sbr__overlay').dispose(); return false;"><span class="b-shadow__icon b-shadow__icon_close"></span></a>
        </div>
    </div>
    <!-- <div class="b-shadow__overlay b-shadow__overlay_bg_black" id="b-shadow_sbr__overlay"></div> -->
    <?
}

// ���� ���� ������ �� ����� �������� �� ����� ������
if($multi->isButton('action_stage') || $multi->isButton('complete') || $multi->isButton('pause')) {
    ?>
    <form id="actionStageForm" method="post">
    	<div>
            <input type="hidden" name="cancel" value="0" />
            <input type="hidden" name="resend" value="0" />
            <input type="hidden" name="id" value="<?=$stage->id?>" />
            <input type="hidden" name="site" value="<?=$site?>" />
            <input type="hidden" name="status" value="" />
            <input type="hidden" name="action" value="" /> 
            <input type="hidden" name="days" value="" />
        </div>
    </form>   
<? 
}
?>