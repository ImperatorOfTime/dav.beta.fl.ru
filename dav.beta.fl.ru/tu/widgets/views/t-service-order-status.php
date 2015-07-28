<?php

    $fullname = "{$user['uname']} {$user['usurname']} [{$user['login']}]";
    //$data_url = tservices_helper::getOrderCardUrl($order_id);
    $hash = md5(TServiceOrderModel::SOLT . $order_id);
    
    //$emp_feedback = $employer['feedback'];
    //$is_emp_feedback = !empty($emp_feedback);
    //$emp_is_good = ($employer['rating'] > 0);
    $emp_color = ($emp_is_good)?'6db335':'c10600';
    $emp_feedback = reformat($emp_feedback, 30);    
    
    
    //$frl_feedback = $freelancer['feedback'];
    //$is_frl_feedback = !empty($frl_feedback);
    //$frl_is_good = ($freelancer['rating'] > 0);
    $frl_color = ($frl_is_good)?'6db335':'c10600';
    $frl_feedback = reformat($frl_feedback, 30); 
    
    $no_reserve_warning = '��� ������ ������ �� �������������� ����������� ��� ��������� �� ��������, ������ � ������ ����������� ������.';
    
    //@todo: ������������ ������ � ���������� ������� ��� ������
    //���� ��������� ��� ���� � ��� ���� ��� ���� ���.
    $frl_fullname = "";//(isset($freelancer))?"{$freelancer['uname']} {$freelancer['usurname']} [{$freelancer['login']}]":"";
    $emp_fullname = "";//(isset($employer))?"{$employer['uname']} {$employer['usurname']} [{$employer['login']}]":"";
    
    
    $order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order_id);

    $icon_prefix = $pay_type == TServiceOrderModel::PAYTYPE_RESERVE ? 'bs' : 'po';
    
    //--------------------------------------------------------------------------
    
    
    if($order_status == TServiceOrderModel::STATUS_NEW)
    {
        if($is_adm)
        {
//------------------------------------------------------------------------------
// ����� �����.
// ������ ��� ������.
//------------------------------------------------------------------------------            
?>
        <table class="b-layout__table">
            <tr class="b-layout__tr">
                <td class="b-layout__td">
                    <div class="b-layout__txt b-layout__txt_color_000">
                        ����������� <?php echo $frl_fullname ?> ������� ����������� � ���������� ������.<br/>
                        �������� <?php echo tservices_helper::cost_format($tax_price,true, false, false) ?> (<?php echo $tax*100 ?>% �� ����������� �������).
                    </div>
                </td>                
            </tr> 
        </table> 
<?php
        }
        elseif($is_emp)
        {
//------------------------------------------------------------------------------
// ����� �����. 
// ������ ��� ��������, �� ����� ��������
//------------------------------------------------------------------------------ 

            $icon_action = $pay_type == TServiceOrderModel::PAYTYPE_RESERVE && $is_reserve_accepted ? 'pay' : 'edit';
?>
     <table class="b-layout__table b-layout__table_width_full">
         <tr class="b-layout__tr">
         <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
             <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
         </td>
         <td class="b-layout__td b-layout__td_ipad">
<?php 
             if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): 
//------------------------------------------------------------------------------
// ����� ����� �� ����� ������� �������.
// ������ ��� ��������� - ������ �������.
//------------------------------------------------------------------------------                 
                 if($is_reserve_accepted):
                     $reserve_tax = $reserve_data['tax']*100;
                     $reserve_price = tservices_helper::cost_format($reserve_data['reserve_price'],true, false, false);
                     $fn_url = sprintf("/users/%s/setup/finance/", $employer['login']);
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
             </div>
             <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                ����� � ������� ����� ���������� ������ &mdash; �������������� �����
             </div>
             <div class="b-layout__txt b-layout__txt_padbot_10 b-fon_overflow_hidden">
                 ����������� ���������� ����� � ����� ��� ���������. ����������, �������������� ����� ������ (+<?=$reserve_tax?>% ��������) �� ����� &mdash; ����� ����� �������� ���������� ������ �� ������.
<?php 
                if(!$reserve->isEmpFinanceReqvsValid()): 
?>
                <br/><br/>
                �������� ��������: ����� ��������������� ��� ���������� ��������� ������ �� �������� "�������".
<?php                
                endif;
?>
             </div>
             <?php if($reserve->isStatusError()): ?>
             <div class="b-layout__txt b-layout__txt_padbot_10 b-fon_overflow_hidden">
<?php
                    if($reserve->getReasonReserve()): 
?>
                 <strong>�������������� �������������� �� �������: </strong>
                 <?=$reserve->getReasonReserve()?>
<?php
                    else:
?>
                 � ���������, ��� ������� ����� �������� ������. 
                 ����������, ���������, ���������� �� ��������� 
                 ������� �� �������� �������, 
                 � ��������� ������.
<?php
                    endif;
?>
             </div>
             <?php elseif($reserve->isEmpFinanceFailStatus()): ?>
             <div class="b-layout__txt b-layout__txt_padbot_10 b-fon_overflow_hidden">
                 � ���������, �� �������� ������� ������� ������������ 
                 ������<?php if($reason = $reserve->getEmpFinanceBlockedReason()): ?>: <?=$reason?>.<?php else: ?>.<? endif; ?>
                 <br/>��� �������� � �������� �������������� �������, ����������, ���������� ������.
             </div>
             <?php endif; ?>
             <div class="b-buttons">
<?php
                   if($reserve->isEmpAllowFinance()):
?>
                 <?php if($reserve->isStatusError()): ?>
                 <a href="javascript:void(0);" 
                    class="b-button b-button_flat b-button_flat_green" 
                    data-duplicate="1"
                    data-popup="quick_payment_reserve" 
                    data-url="<?=$order_url?>">
                     ��������� ������ <?=$reserve_price?>
                 </a>                 
                  <a href="<?=$fn_url?>" 
                     class="b-button b-button_flat b-button_flat_green" 
                     data-duplicate="1">
                     ������� �� �������� "�������"
                  </a>  
                 <?php else: ?>
                 <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                    data-url="<?=$order_url?>"
                    data-scrollto = "form-block"
                    href="javascript:void(0);"
                    onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                     �������� �����
                 </a>
                 <a href="javascript:void(0);" 
                    class="b-button b-button_flat b-button_flat_green" 
                    data-duplicate="1"
                    data-popup="quick_payment_reserve" 
                    data-url="<?=$order_url?>">
                     ��������������� <?=$reserve_price?>
                 </a>                 
                 <?php endif; ?>
<?php
                    elseif(!$reserve->isEmpFinanceValid()):
?>
                  <a href="<?=$fn_url?>" 
                     class="b-button b-button_flat b-button_flat_green" 
                     data-duplicate="1">
                     ������� �� �������� "�������"
                  </a>
<?php
                    else:
?>
                  <a href="javascript:void(0)" 
                    class="b-button b-button_flat b-button_flat_green b-button_disabled">
                    �������� ������ ����������� 
                  </a>
<?php
                    endif;
?>
                 <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; ��� &#160;</span>
                 <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                    href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'cancel'); ?>"
                    data-duplicate="2">
                     �������� �����
                 </a>
                 
             </div> 
<?php
                    if(!$is_list && $reserve->isEmpAllowFinance()):
                        $this->widget('quickPaymentPopupReserve', array(
                            'reserveInstance' => $reserve,
                            //@todo: ������� ������ ������� ����� ���������� ������ ��� ����
                            'reserve_id' => $reserve_data['id'],
                            'uid' => $reserve_data['emp_id'],
                            'opt' => array(
                                'price' => tservices_helper::cost_format($reserve_data['price'],true, false, false),
                                'reserve_price' => $reserve_price,
                                'tax' => $reserve_tax.'%',
                                'tax_price' => tservices_helper::cost_format($reserve_data['tax_price'],true, false, false),
                                'fn_url' => ($reserve->isAllowEditFinance($reserve_data['emp_id'], true))?$fn_url:false,
                                'order_url' => $order_url
                            )
                        ));
                    
                        if($reserve->isEmpJuri()):
?>
             <div class="b-layout__txt b-layout__txt_padtop_10 b-layout__txt_padbot_5">
                 <table class="b-layout__table">
                     <tr>
                         <td class="b-layout__td b-layout__td_padright_5">
                             <input type="checkbox" name="reserve_send_docs" id="reserve_send_docs" checked="checked" />
                         </td>
                         <td>
                            <label for="reserve_send_docs">
                                ����� ���������� ������ ��������� ��������� ����������� ���������� �� ������: <br/>
                                <?=$this->getEmpAddress()?>&nbsp;
                                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" href="<?=$fn_url?>">�������� �����</a>
                            </label>                             
                         </td>
                     </tr>
                 </table>
             </div>
<?php                    
                        endif;
                    endif;
                    
                else:
//------------------------------------------------------------------------------
// ����� ����� �� ����� ������� �������.
// ������ ��� ��������� - ������������� ������������.
//------------------------------------------------------------------------------                    
?>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
            </div>
             <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                ����� � ������� ����� ���������� ������ &mdash; ���������� �������
             </div>
             <div class="b-layout__txt b-layout__txt_padbot_10 b-fon_overflow_hidden">
                 ����������, �������� � ������������ ��� ������� ��������������, ���������� ����� � ��������� ������. ��� ������ ����������� ���������� ����� (���������� �� ��� ����������), �� ������� ��������������� ����� ������ (+��������) � ������ ��������������.
             </div>
             <div class="b-buttons b-buttons_padbot_10">
                 <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                    data-url="<?=$order_url?>"
                    data-scrollto = "form-block"
                    href="javascript:void(0);"
                    onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                     �������� �������
                 </a>
                 <a class="b-button b-button_flat b-button_flat_red" 
                    href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'cancel'); ?>"
                    data-duplicate="1">
                     �������� �����
                 </a>
                 <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; ��� &#160;</span>
                 <a class="b-button" 
                    data-url="<?=$order_url?>" 
                    data-popup="tu_edit_budjet" 
                    data-duplicate="2"
                    href="javascript:void(0);" 
                    onClick="yaCounter6051055.reachGoal('zakaz_change');$('tu_edit_budjet').removeClass('b-shadow_hide');">
                     <span class="b-button__txt_underline">
                         �������� ����, ����� ��� ��� ������
                     </span>
                 </a>
             </div>            
<?php
                endif;
            else: 
//------------------------------------------------------------------------------
// ����� ����� �� ������� �����.
// ������ ��� ���������.
//------------------------------------------------------------------------------                
?>
            <?php /*
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_right">
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/topic/483819-soglasovanie-uslovij/">
                    ��������� � ������������ ������
                </a>
            </div> 
				*/ ?>            
             <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">����� � ������ ������� &mdash; ���������� �������</div>
             <div class="b-layout__txt b-layout__txt_padbot_10">
                 ����������, �������� � ������������ ��� ������� ��������������, ���������� ����� � ��������� ������, � ����� ������� �� ������ � ��������� �����������. ��� ������ ����������� ���������� ����� (���������� �� ��� ����������), �� ������� ������ ��������������.
             </div>
             <div class="b-buttons b-buttons_padbot_10">
                 <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                    data-url="<?=$order_url?>"
                    data-scrollto = "form-block"
                    href="javascript:void(0);"
                    onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                     �������� �������
                 </a>
                 <a class="b-button b-button_flat b-button_flat_red" 
                    href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'cancel'); ?>"
                    data-duplicate="1">
                     �������� �����
                 </a>
                 <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; ��� &#160;</span>
                 <a class="b-button" 
                    data-url="<?=$order_url?>" 
                    data-popup="tu_edit_budjet" 
                    data-duplicate="2"
                    href="javascript:void(0);" 
                    onClick="yaCounter6051055.reachGoal('zakaz_change');$('tu_edit_budjet').removeClass('b-shadow_hide');">
                     <span class="b-button__txt_underline">
                        �������� ����, ����� ��� ��� ������
                     </span>
                 </a>
             </div>

            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span>
                <?=$no_reserve_warning?><br />
                ����� �������� ��������� � ���������� ������, ����������� ������������ 
                ����� "���������� ������" (�������� ��� ������ � ������ �� "���������� ������").
            </div>
<?php 
            endif; 
?>
         </td>
         </tr>
     </table>
<?php

        }
        else
        {
//------------------------------------------------------------------------------
// ����� �����.
// ������� ��� �����������.
//------------------------------------------------------------------------------  
            $icon_action = $pay_type == TServiceOrderModel::PAYTYPE_RESERVE && $is_reserve_accepted ? 'pay' : 'edit';
?>
    <table class="b-layout__table b-layout__table_width_full">
        <tr class="b-layout__tr">
        <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
            <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
        </td>
        <td class="b-layout__td b-layout__td_ipad ">  
<?php 
        if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
//------------------------------------------------------------------------------
// ����� ����� �� ����� ������� �������.
// ������� ��� ����������� - �������� ������������� � �������� ������� �������
//------------------------------------------------------------------------------            
            if($is_reserve_accepted):
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
             </div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                ����� � ������� ����� ���������� ������ &mdash; �������������� �����
            </div>
            <div class="b-layout__txt b-layout__txt_padbot_10">
                �� ����������� ����� � ���������� ��� ���������. ����� ��� ���������� ���������, ���� �������� ������������� �� ����� ����� ������, � ������ ����� ����� ������ ���������� ������ �� ������.
            </div>
            <div class="b-buttons b-buttons_padbot_10">
                 <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                    data-url="<?=$order_url?>"
                    data-scrollto = "form-block"
                    href="javascript:void(0);"
                    onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                     �������� �����
                 </a>
            </div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span>
                ����������, �� ��������� ���������� ������, ���� �������� �� ������������� ����� ������. 
                �� �������� �����������, ��� ������ ����� ����� ����������� �� ����.
            </div>
            
<?php 
            else: 
//------------------------------------------------------------------------------
// ����� ����� �� ����� ������� �������.
// ������� ��� ����������� - ������ �� ������������� ������.
//------------------------------------------------------------------------------                
?> 
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
            </div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                ����� � ������� ����� ���������� ������ &mdash; ���������� ������� 
            </div>            
            <div class="b-layout__txt b-layout__txt_padbot_20">
                ����������, �������� � ���������� ��� ������� ��������������, ���������� ����� � ��������� ������. ��� ������ �� ����������� ����� (����������� �� ��� ����������), �������� ������ ��������������� ����� ������ � ������ �������������� � ����.
            </div>    
<?php 
            endif;
        else: 
//------------------------------------------------------------------------------
// ����� ����� �� ������� �����.
// ������ ��� ����������� - �������������� ��� ����� �� ������.
//------------------------------------------------------------------------------            
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">����� � ������ ������� &mdash; ���������� �������</div>
            <div class="b-layout__txt b-layout__txt_padbot_20">
               ����������, �������� � ���������� ��� ������� ��������������, ���������� ����� � ��������� ������, � ����� ������� �� ������ � ��������� �����������. ��� ������ �� ����������� ����� (����������� �� ��� ����������), �������� ������ �������������� � ����.
            </div>     
<?php 
        endif;
        
        if(!$is_reserve_accepted):
?>               
            <div class="b-buttons b-buttons_padbot_10">
                 <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                    data-url="<?=$order_url?>"
                    data-scrollto = "form-block"
                    href="javascript:void(0);"
                    onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                     �������� �������
                 </a>
                <a href="javascript:void(0);" 
                   class="b-button b-button_flat b-button_flat_green" 
                   onclick="TServices_Order.showAcceptPopup(<?=$order_id?>);"
                   data-duplicate="1">
                    ����������� �����
                </a>
                <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; ��� &#160;
                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                   href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'decline'); ?>"
                   data-duplicate="2"
                   >
                    ���������� �� ����
                </a>
                </span>
            </div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span>
                <?php if ($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                    ��������� �����, ����� ��� ���� ������ � ������ �������� ������ �� ������� ���������.
                <?php else: ?>
                    <?=$no_reserve_warning?><br />
                    ����� �������� ��������� � ������ ��������, ����������� ������������ 
                    ����� "���������� ������" (��������� �������� ��� ������ � ������ �� "���������� ������").
                <?php endif; ?>
            </div>
                      
            <?php
                //�������� ����� ��� ����������
                $this->widget('TServiceOrderStatusPopup', array('data' => array(
                    'idx' => $order_id,
                    'title' => $order_title,
                    'price' => $order_price,
                    'tax' => $tax,
                    'days' => $order_days,
                    'pay_type' => $pay_type
                )));
            ?>
<?php
        endif;   
?>
        </td>
        </tr>
    </table>      
<?php

        }//ELSE
    }
    elseif($order_status == TServiceOrderModel::STATUS_CANCEL)
    {
        if($is_adm)
        {
//------------------------------------------------------------------------------
// ������ ������ ����������.
// ������ ��� ������.
//------------------------------------------------------------------------------            
?>
        <table class="b-layout__table">
            <tr class="b-layout__tr">
                <td class="b-layout__td">
                    <div class="b-layout__txt">
                        �������� <?php echo $emp_fullname ?> ������� ���� �����.
                    </div>
                </td>                
            </tr> 
        </table>  
<?php
        }
        elseif($is_emp)
        {
//------------------------------------------------------------------------------
// ������ ������ ����������.
// ������ ��� ���������.
//------------------------------------------------------------------------------             
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_cancel.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
                    <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                        <span class="b-icon b-icon_sbr_oask"></span>
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                            � ���������� �������
                        </a>
                     </div>
                    <?php endif; ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                            ����� � ������� ����� ���������� ������ 
                        <?php else: ?>
                            ����� � ������ �������
                        <?php endif; ?>
                        &mdash; ����� �������
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        �������������� �� ������ ��������. ��� ������������� �� ������ ���������� ����������� ����� ����� � ������ ��������� ��������������.
                    </div>
                </td>
            </tr>
        </table>
<?php 

        }
        else
        {
//------------------------------------------------------------------------------
// ������ ������ ����������.
// ������ ��� �����������.
//------------------------------------------------------------------------------                     
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_cancel.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
                    <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                        <span class="b-icon b-icon_sbr_oask"></span>
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                            � ���������� �������
                        </a>
                     </div>
                    <?php endif; ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                            ����� � ������� ����� ���������� ������ 
                        <?php else: ?>
                            ����� � ������ �������
                        <?php endif; ?>
                        &mdash; ����� �������
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_20">
                        � ���������, �������� ������� ���� �����. ��� ������������� 
                        �� ������ �������� � ���� ������� ������ � ���������� ����������� 
                        ������ ������ � ������ ��������� ��������������.
                    </div>
                </td>
            </tr>
        </table>
<?php 

        }//ELSE
    }
    elseif($order_status == TServiceOrderModel::STATUS_DECLINE)
    {
        if($is_adm)
        {
//------------------------------------------------------------------------------
// ����� �� ������ ������������.
// ������ ��� ������.
//------------------------------------------------------------------------------            
?>
        <table class="b-layout__table">
            <tr class="b-layout__tr">
                <td class="b-layout__td">                     
                    <div class="b-layout__txt">
                        ����������� <?php echo $frl_fullname ?> ��������� �� ���������� ������.
                    </div>
                </td>                
            </tr> 
        </table>            
<?php
        }
        elseif($is_emp)
        {
//------------------------------------------------------------------------------
// ����� �� ������ ������������.
// ������ ��� ���������.
//------------------------------------------------------------------------------            
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_cancel.png">
                </td>
                <td class="b-layout__td b-layout__td_ipad">
                    <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                        <span class="b-icon b-icon_sbr_oask"></span>
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                            � ���������� �������
                        </a>
                     </div>
                    <?php endif; ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                            ����� � ������� ����� ���������� ������ 
                        <?php else: ?>
                            ����� � ������ �������
                        <?php endif; ?>
                        &mdash; ����� �������
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        � ���������, ����������� ��������� �� ���������� ������ ������. 
                        ��� ������������� �� ������ �������� � ���� ������� ������ � ���������� 
                        ����� ����� � ������ ��������� ��������������.
                    </div>
                </td>
            </tr>
        </table>
<?php 

        }
        else
        {
//------------------------------------------------------------------------------
// ����� �� ������ ������������.
// ������ ��� �����������.
//------------------------------------------------------------------------------            
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_cancel.png">
                </td>
                <td class="b-layout__td b-layout__td_ipad">
                    <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                        <span class="b-icon b-icon_sbr_oask"></span>
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                            � ���������� �������
                        </a>
                     </div>
                    <?php endif; ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                            ����� � ������� ����� ���������� ������ 
                        <?php else: ?>
                            ����� � ������ �������
                        <?php endif; ?>
                        &mdash; ����� �������
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_20">
                        �������������� �� ������ ��������. ��� ������������� �� ������ 
                        ���������� � ���������� ����������� ������ ������ � ������ 
                        ��������� ��������������.
                    </div>
                </td>
            </tr>
        </table>        
<?php
        }
    }
    elseif($order_status == TServiceOrderModel::STATUS_ACCEPT)
    {
        if($is_adm)
        {
//------------------------------------------------------------------------------
// ����� � ������.
// ������ ��� ������.
//------------------------------------------------------------------------------            
?>
        <table class="b-layout__table">
            <tr class="b-layout__tr">
            <?php if (isset($reserve_data['arbitrage_id'])) { ?>
                <td class="b-layout__td">
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'��������':'�����������'?> ��������� � ��������
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    
                    <?php
                        //�������� ����� ��� �������
                        $this->widget('ReservesArbitrageForm', array('data' => array(
                            'order_id' => $order_id,
                            'price' => $reserve_data['price']
                        )));
                    ?>
                </td>
            <?php } else { ?>
                <td class="b-layout__td">
                    <div class="b-layout__txt">
                        ����������� <?php echo $frl_fullname ?> ���������� ����� � ��������� ���.
                    </div>
                </td>
            <?php } ?>
            </tr>
        </table>            
<?php
        }
        elseif($is_emp)
        { 
            $icon_action = $reserve_data['arbitrage_id'] > 0 ? 'arbitrage' : 'run';
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
<?php
//------------------------------------------------------------------------------
// ����� � ������ �� ����� ������� �������.
// ������ ��� ���������.
//------------------------------------------------------------------------------
                if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
                    
                    
                    //���� ������ �� ��������
                    if(isset($reserve_data['arbitrage_id'])):
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������� ����� ���������� ������ &mdash; ��������
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'�� ����������':'����������� ���������'?> � ��������, ������� ����� �� ������������ �������. � ������� ���������� ���� 
                        �� ������ �������� � ���������� ���� ��� �������������� ���������, ���������� ����� ���� � 
                        ������������. �� ��������� ������������ ������������ ����� �������� ����������� ������� 
                        (� �������, �������� ��� ���������� ����������������� �����). ��������, ����������.
                    </div>
                    <div class="b-layout__txt b-layout__txt_color_666 b-layout__txt_padbot_5 b-layout__txt_bold">
                        ������� ��������� � ��������:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_20 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             �������� �����
                         </a>
                    </div>          

<?php
                    else:
                        
                    
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������� ����� ���������� ������ &mdash; ���������� ������
                    </div>            
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        ����� ���������������, ����������� ��������� ������ �� ������. � �������� �������������� �� ������ ���������� ���������� ������, ������� � ��� � ���������� �� ����������� �����������. ��� ������ ������ ����� ��������� � ������� ����, ����������, ��������� ����� � �������� ����� � ��������������.
                    </div> 
                    <div class="b-buttons b-buttons_padbot_15">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             �������� �����
                         </a>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);"
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>"
                           data-duplicate="1">��������� ��������������</a>
                        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; ��� &#160;</span>
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                           href="javascript:void(0);" 
                           data-url="<?=$order_url?>"
                           data-popup="<?=ReservesArbitragePopup::getPopupId($order_id)?>"
                           data-duplicate="2">
                            ���������� � ��������
                        </a>
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11 b-layout__txt_padleft_20"><span class="b-icon b-icon_top_1 b-icon_sbr_oattent b-icon_margleft_-20"></span>���� � �������� �������������� � ��� ��������� �������� � ������������, ����������� ���������� � �������� � ������������� �������� � ������� �������.</div>
                    <?php $this->widget('ReservesArbitragePopup', array('data' => array(
                        'idx' => $order_id
                    ))) ?>
<?php
                    endif;
                else:
//------------------------------------------------------------------------------
// ����� � ������ �� ������� �����. 
// � ����� ����� �������� ����� ��������� ������ � �������� �����.
//------------------------------------------------------------------------------                    
?>
                    <?php /*
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_right">
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/topic/483824-vyipolnenie-rabotyi-sotrudnichestvo-i-perepiska/">
                            ��������� � �������� ��������������
                        </a>
                    </div> 
						  */ ?>                   
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������ ������� &mdash; ���������� ������
                    </div>                      
                    <div class="b-layout__txt b-layout__txt_padbot_15">
����������� ���������� ����� � ��������� � ��� ����������. � �������� �������������� �� ������ ���������� ���������� ������, ������� � ��� � ���������� �� ����������� �����������. ��� ������ ������ ����� ��������� � ������� ����, ����������, ��������� ����� � �������� ����� � ��������������. 
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                       <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                          data-url="<?=$order_url?>"
                          data-scrollto = "form-block"
                          href="javascript:void(0);"
                          onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                           �������� �����
                       </a>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>"
                           data-duplicate="1">
                            ��������� ��������������
                        </a>
                    </div> 
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        <span class="b-icon b-icon_top_1 b-icon_sbr_oattent b-icon_margleft_-20"></span>
                        <?=$no_reserve_warning?>
                    </div>
                    
                    
<?php
                endif;

                $this->widget('TServiceOrderFeedback', array('data' => array(
                    'idx' => $order_id,
                    'hash' => $hash,
                    'pay_type' => $pay_type,
                    'rating' => $frl_rating,
                    'is_close' => false
                )));
?>
                </td>
            </tr>
        </table>
<?php 

        }
        else
        {
  
            $icon_action = $reserve_data['arbitrage_id'] > 0 ? 'arbitrage' : 'run';
?> 
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
<?php
//------------------------------------------------------------------------------
// ����� � ������ �� ����� ������� �������.
// ������ ��� �����������.
//------------------------------------------------------------------------------
                if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
                    
                    //���� ������ �� ��������
                    if(isset($reserve_data['arbitrage_id'])):
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������� ����� ���������� ������ &mdash; ��������
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'�������� ���������':'�� ����������'?> � ��������, ������� ����� �� ������������ �������. 
                        � ������� ���������� ���� �� ������ �������� � ���������� ���� ��� �������������� ���������, 
                        ���������� ����� ���� � ����������. �� ��������� ������������ ������������ ����� �������� ����������� ������� 
                        (� �������, �������� ��� ���������� ����������������� �����). ��������, ����������.
                    </div>
                    <div class="b-layout__txt b-layout__txt_color_666 b-layout__txt_padbot_5 b-layout__txt_bold">
                        ������� ��������� � ��������:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_20 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             �������� �����
                         </a>
                    </div>          
<?php
                    else:
?> 
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������� ����� ���������� ������ &mdash; ���������� ������
                    </div>            
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        ����� ���������������, �������� ������� ���������� ������ �� ������. � �������� �������������� �� ������ ���������� ���������� ������, ������� � ��� � ������������ ���� �����������. ��� ������ ������ ����� ���������, ����������, �������� �� ���� ���������, ����� �� ��� ��������� ����� � ������������� ��� ����������������� ����� ������.
                    </div> 
                    <div class="b-buttons b-buttons_padbot_15">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             �������� �����
                         </a>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'done'); ?>"
                           data-duplicate="1">
                            ��������� � ����������� ������
                        </a>
                        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; ��� &#160;</span>
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                           href="javascript:void(0);" 
                           data-url="<?=$order_url?>"
                           data-popup="<?=ReservesArbitragePopup::getPopupId($order_id)?>"
                           data-duplicate="2">
                            ���������� � ��������
                        </a>                      
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span>���� � �������� �������������� � ��� ��������� �������� � ����������, ����������� ���������� � �������� � ������������� �������� � ������� �������.
                    </div>
                    <?php $this->widget('ReservesArbitragePopup', array('data' => array(
                        'idx' => $order_id
                    ))) ?>
<?php
                    endif;
                    
                else:
//------------------------------------------------------------------------------
// ����� � ������ �� ������� �����. 
// � ����� ����� ����������� ����� ��������� ��������� � ���������� ������.
//------------------------------------------------------------------------------
?>
                    <?php /*
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_right">
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/topic/483824-vyipolnenie-rabotyi-sotrudnichestvo-i-perepiska/">
                            ��������� � �������� ��������������
                        </a>
                    </div>
						  */ ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������ ������� &mdash; ���������� ������
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        �� ����������� �����, ��� ����� ��������� � ��� ����������. � �������� �������������� �� ������ ���������� ���������� ������, ������� � ��� � ������������ ���� �����������. ��� ������ ������ ����� ���������, ����������, �������� �� ���� ���������, ����� �� ��� ��������� ����� � �������� ����� � ��������������. 
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             �������� �����
                         </a>
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                           href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'done'); ?>"
                           data-duplicate="1">
                            ��������� � ����������� ������
                        </a>
                    </div> 
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span>
                        <?=$no_reserve_warning?>
                    </div>
<?php

                endif;
                
?>
                </td>
            </tr>  
        </table>
<?php   

        }
    }
    elseif($order_status == TServiceOrderModel::STATUS_FIX)
    {
//------------------------------------------------------------------------------
// �������� �������� �� ��������� �� ��������� ��������������.
// ������ ��� ������.
//------------------------------------------------------------------------------        
        if($is_adm)
        {  
?>
        <table class="b-layout__table">
            <tr class="b-layout__tr">
<?php 
            if (isset($reserve_data['arbitrage_id'])): 
?>
                <td class="b-layout__td">
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'��������':'�����������'?> ��������� � ��������
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    
                    <?php
                        //�������� ����� ��� �������
                        $this->widget('ReservesArbitrageForm', array('data' => array(
                            'order_id' => $order_id,
                            'price' => $reserve_data['price']
                        )));
                    ?>
                </td>
<?php 
            else: 
?>                
                <td class="b-layout__td">
                    �������� <?php echo $emp_fullname ?> ��������� ��������������.
                </td>
<?php 
            endif;
?>
            </tr>
        </table>
<?php
        }
        elseif($is_emp)
        {
//------------------------------------------------------------------------------
// �������� �������� �� ��������� �� ��������� ��������������.
// ������ ��� ���������.
//------------------------------------------------------------------------------            

            $icon_action = $reserve_data['arbitrage_id'] > 0 ? 'arbitrage' : 'run';
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
<?php
//------------------------------------------------------------------------------
// �������� �������� �� ��������� �� ��������� �������������� �� ����� ������� �������.
// ������ ��� ���������.
//------------------------------------------------------------------------------
                if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
                    //���� ������ �� ��������
                    if(isset($reserve_data['arbitrage_id'])):
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������� ����� ���������� ������ &mdash; ��������
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'�� ����������':'����������� ���������'?> � ��������, ������� ����� �� ������������ �������. � ������� ���������� ���� 
                        �� ������ �������� � ���������� ���� ��� �������������� ���������, ���������� ����� ���� � 
                        ������������. �� ��������� ������������ ������������ ����� �������� ����������� ������� 
                        (� �������, �������� ��� ���������� ����������������� �����). ��������, ����������.
                    </div>
                    <div class="b-layout__txt b-layout__txt_color_666 b-layout__txt_padbot_5 b-layout__txt_bold">
                        ������� ��������� � ��������:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_20 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             �������� �����
                         </a>
                    </div>          

<?php             
                    else:
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������� ����� ���������� ������ &mdash; ���������� ������
                    </div>            
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        ����� ��������� � ������, �� ������ ���������� �������������� � ������������. ��� ������ ������ ����� ��������� ��������� � ������� ����, �� �������� ��������� ����� � �������� ����� � ��������������.
                    </div> 
                    <div class="b-buttons b-buttons_padbot_15">
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);"
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            ��������� ��������������
                        </a>
                        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; ��� &#160;</span>
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                           href="javascript:void(0);" 
                           data-url="<?=$order_url?>"
                           data-duplicate="1"
                           data-popup="<?=ReservesArbitragePopup::getPopupId($order_id)?>">
                            ���������� � ��������
                        </a>                        
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span>���� � �������� �������������� � ��� ��������� �������� � ������������, ����������� ���������� � �������� � ������������� �������� � ������� �������.
                    </div>                    
                    <?php $this->widget('ReservesArbitragePopup', array('data' => array(
                        'idx' => $order_id
                    ))) ?>
<?php
                    endif;
                else:
//------------------------------------------------------------------------------
// �������� �������� �� ��������� �� ��������� �������������� �� ������� �����.
// ������ ��� ���������.
//------------------------------------------------------------------------------                    
?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������ ������� &mdash; ���������� ������
                    </div>                      
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        ����� ��������� � ������. ����������, �������� � ������������ ������� �������� � ����������� ������. ��� ������ ������ ����� ��������� ��������� � ������� ����, �� �������� ��������� �����, �������� ������ ����������� � �������� ����� � ��������������.
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            ��������� ��������������
                        </a>
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $no_reserve_warning ?>
                    </div>                    
<?php
                endif;

                $this->widget('TServiceOrderFeedback', array('data' => array(
                    'idx' => $order_id,
                    'hash' => $hash,
                    'pay_type' => $pay_type,
                    'rating' => $frl_rating,
                    'is_close' => false
                )));
?>                    
                </td>
            </tr>
        </table>
<?php
        }
        else
        {
//------------------------------------------------------------------------------
// �������� �������� �� ��������� �� ��������� ��������������.
// ������ ��� �����������.
//------------------------------------------------------------------------------            
            $icon_action = $reserve_data['arbitrage_id'] > 0 ? 'arbitrage' : 'run';
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
<?php
//------------------------------------------------------------------------------
// �������� �������� �� ��������� �� ��������� �������������� �� ����� ������� �������.
// ������ ��� �����������.
//------------------------------------------------------------------------------
                if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
                    //���� ������ �� ��������
                    if(isset($reserve_data['arbitrage_id'])):
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������� ����� ���������� ������ &mdash; ��������
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'�������� ���������':'�� ����������'?> � ��������, ������� ����� �� ������������ �������. 
                        � ������� ���������� ���� �� ������ �������� � ���������� ���� ��� �������������� ���������, 
                        ���������� ����� ���� � ����������. �� ��������� ������������ ������������ ����� �������� ����������� ������� 
                        (� �������, �������� ��� ���������� ����������������� �����). ��������, ����������.
                    </div>
                    <div class="b-layout__txt b-layout__txt_color_666 b-layout__txt_padbot_5 b-layout__txt_bold">
                        ������� ��������� � ��������:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_20 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             �������� �����
                         </a>
                    </div>          
<?php
                    else:
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������� ����� ���������� ������ &mdash; ���������� ������
                    </div>            
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        ����� ��������� � ������, �� ������ ���������� �������������� � ����������. ��� ������ ������ ����� ��������� ���������, �� �������� �������� �� ���� ��������, ����� �� ��� ��������� ����� � ������������� ��� ����������������� ����� ������.
                    </div> 
                    <div class="b-buttons b-buttons_padbot_15">
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'done'); ?>"
                           data-duplicate="1">
                            ��������� � ����������� ������
                        </a>
                        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; ��� &#160;</span>
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                           href="javascript:void(0);" 
                           data-url="<?=$order_url?>"
                           data-duplicate="2"
                           data-popup="<?=ReservesArbitragePopup::getPopupId($order_id)?>">
                            ���������� � ��������
                        </a>                       
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span>���� � �������� �������������� � ��� ��������� �������� � ����������, ����������� ���������� � �������� � ������������� �������� � ������� �������.
                    </div>    
<?php 

$this->widget('ReservesArbitragePopup', array('data' => array(
    'idx' => $order_id
))); 
                  endif;
                  
                else:
//------------------------------------------------------------------------------
// �������� �������� �� ��������� �� ��������� �������������� �� ������� �����. 
// � ����� ����� ����������� ����� ��������� ��������� � ���������� ������.
//------------------------------------------------------------------------------
?>                    
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������ ������� &mdash; ���������� ������
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        �������� ������ ����� � ������. ����������, �������� � ���������� ������� �������� � ������� ����������� ������. ��� ������ ������ ����� ��������� ���������, �� �������� �������� �� ���� ���������, ����� �� ��� ��������� ����� � �������� ���� ������.
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'done'); ?>">
                            ��������� � ����������� ������
                        </a>
                    </div> 
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $no_reserve_warning ?>
                    </div>    
<?php
                endif;
?>
                </td>
            </tr>
        </table>
<?php
        }
    }
    elseif($order_status == TServiceOrderModel::STATUS_EMPCLOSE)
    {
        
        /**
         * ����� ������������ ��� ����� ������������ �������� ��� ���������
         * ����������� �� �������� ����� ����� �������� � �������������!!!
         */
        
        
        if (isset($reserve_data['arbitrage_price'])) {
            $pricePay = $reserve->getArbitragePricePay();
            $priceBack = $reserve->getArbitragePriceBack();
            $pricePayFormatted = tservices_helper::cost_format($pricePay,true, false, false);
            $priceBackFormatted = tservices_helper::cost_format($priceBack,true, false, false);
            $icon_action = $reserve->isClosed() ? 'close' : 'pay';
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img src="/images/po/bs_<?=$icon_action?>.png" alt="" class="b-user__pic">
                </td>
                <td class="b-layout__td b-layout__td_ipad">
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������� ����� ���������� ������ &mdash; 
                        <?php if($reserve->isClosed()): ?>
                            ����� ������
                        <?php else: ?>
                            ������� ����
                        <?php endif; ?>
                    </div>
                    <?php if(!$reserve->isClosed()): ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        ����� ������������ �������� � �������� ��� �� �������������� ���������, ���������� ����� ���������� � ������������, �������� �������� �������:<br>
                    </div>
                    <?php endif; ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        
                        <?php if($pricePay) { ?>
                            � ������� ����������� <?=$pricePayFormatted?> &mdash; 
                            <b>
                                <?php if($reserve->isStatusPayPayed()): ?>����� ���������
                                <?php elseif($reserve->isStatusPayError()): ?>������� �������� ��������������
                                <?php else: ?>������� �����<?php endif; ?>
                            </b><br/>
                        <?php } ?>
                            
                        <?php if($priceBack) { ?>
                            � �������� ��������� <?=$priceBackFormatted?> &mdash; 
                            <b>
                                <?php if($reserve->isStatusBackPayed()): ?>����� ���������
                                <?php elseif($reserve->isStatusBackError()): ?>������� �������� ��������������
                                <?php else: ?>������� �����<?php endif; ?>
                            </b>
                            <?php if($is_emp): ?>
                            
                                <?php if($reserve->isStatusBackError()): ?>
                            
                            <?php if(false): ?>
                                    <?php if(!$reserve->isFrlPhis()): ?>
                            <div class="b-layout__txt">
                                � ���������, ��� ������� ����� �������� ������. 
                                ����������, ���������, ���������� �� ��������� 
                                ������� �� �������� �������, 
                                � ��������� ������ �� �������.
                            </div>
                                    <?php endif; ?>
                            <?php endif; ?>
                            
                                <?php elseif(!$reserve->isStatusBackPayed()): ?>
                            <div class="b-layout__txt">
                                ����������, ��������. � ��������� ����� ����� �������� 
                                ����� ����������� ��� �� ��� �� �������/����, 
                                � �������� ��� ���� ���������������.
                            </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php } ?>
                    </div>
                    
                    <?php if($is_emp && $reserve->isStatusBackError()): ?>
                        <?php if($reserve->getReasonPayback()): ?>
                        <div class="b-layout__txt b-layout__txt_padbot_10">
                            <strong>������� ������������� �� �������: </strong>
                            <?=$reserve->getReasonPayback()?>
                        </div>
                        <?php endif; ?>
                    <?php elseif(!$is_emp && $reserve->isStatusPayError()): ?>
                        <?php if($reserve->getReasonPayout()): ?>
                        <div class="b-layout__txt b-layout__txt_padbot_10">
                            <strong>������� �������������� �� �������: </strong>
                            <?=$reserve->getReasonPayout()?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if(!$is_adm && $is_emp) { ?>
                            <?php if (!$is_emp_feedback && $is_allow_feedback) { ?>
                            <div class="b-layout__txt b-layout__txt_padbot_15">
                                <?php if(!$reserve->isSubStatusError()): ?>
                                ������� ���������� ������ ��������, ������� �� ��������������!<br/>
                                <?php endif; ?>
                                �� �������� �� <?=$date_feedback?> �������� �����.
                            </div>
                            <div class="b-buttons b-buttons_padbot_15">
                                <a class="b-button b-button_flat b-button_flat_green" 
                                   href="javascript:void(0);" 
                                   data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                                    �������� �����
                                </a>  
                                <?php if($is_frl_feedback): ?>
                                <a class="b-button b-buttons_padleft_20" 
                                   href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id)?>">
                                    ����� �����������
                                </a>
                                <?php endif; ?>                                
                            </div>
<?php
                    $this->widget('TServiceOrderFeedback', array('data' => array(
                        'idx' => $order_id,
                        'hash' => $hash,
                        'pay_type' => $pay_type,
                        'rating' => $frl_rating,
                        'is_close' => true
                    ))); 
?>                       
                            <?php } else { ?>
                    
                            <?php if(!$reserve->isSubStatusError()): ?>
                            <div class="b-layout__txt b-layout__txt_padbot_15">
                                ������� ���������� ������ ��������, ������� �� ��������������!
                            </div>
                            <?php endif; ?>
                    
                            <div class="b-buttons b-buttons_padbot_15">
                                <?php if($is_emp_feedback): ?>
                                <a class="b-button" 
                                   href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id, $freelancer['login'])?>">
                                    ��� ����� �����������
                                </a>                        
                                <?php endif; ?>
                                <?php if($is_frl_feedback): ?>
                                <a class="b-button <?php if($is_emp_feedback): ?>b-buttons_padleft_20<?php endif; ?>" 
                                   href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id)?>">
                                    ����� �����������
                                </a>
                                <?php endif; ?>
                            </div>                    
                            <?php } ?>
<?php 
                        } 
                        elseif(!$is_adm && !$is_emp) 
                        { 

                            if($reserve->isStatusPayAllowPayout()) 
                            { 
                                $fn_url = sprintf("/users/%s/setup/finance/", $freelancer['login']);
?>
                                <?php if($reserve->isFrlAllowFinance()): ?>
                            <div class="b-layout__txt b-layout__txt_padbot_15">
                                �����������, ����������, ������� �����, ����� �� ����� ����������� �� ������� ��� ��������.
                            </div>
                            <div class="b-buttons b-buttons_padbot_15">
                                <a class="b-button b-button_flat b-button_flat_green" 
                                   data-popup="<?= ReservesPayoutPopup::getPopupId($order_id) ?>" 
                                   data-url="<?= $order_url ?>" 
                                   href="javascript:void(0);">
                                    ����������� ������� �����
                                </a>
                            </div>
<?php
                        //�������� ����� ��� ������������� ������
                        if(!$is_list):
                            
                            $ndfl = null;
                        
                            if ($reserve->getArbitrageNDFL()) 
                            {
                                $ndfl = tservices_helper::cost_format($reserve->getArbitrageNDFL(),true, false, false);
                            }
                            
                            $this->widget('ReservesPayoutPopup', array(
                                'price' => $reserve->getArbitragePricePay(),
                                'options' => array(
                                    'idx' => $order_id,
                                    'hash' => $hash,
                                    'is_feedback' => $is_frl_feedback,
                                    'is_allow_feedback' => $is_allow_feedback,
                                    'price' => $pricePayFormatted,
                                    'price_ndfl' =>$ndfl,
                                    'price_all' => tservices_helper::cost_format($reserve->getArbitragePriceWithOutNDFL(),true, false, false),
                                    'fn_url' => ($reserve->isAllowEditFinance($reserve_data['frl_id'], false))?$fn_url:false
                            )));
                        endif;
?>
                            <?php elseif(!$reserve->isFrlFinanceValid()): 
                            ?>
                                         
                                <?php if($reserve->isFrlFinanceFailStatus()): ?>
                            <div class="b-layout__txt b-layout__txt_padbot_10 b-fon_overflow_hidden">
                                � ���������, �� �������� ������� ������� ������������ 
                                ������<?php if($reason = $reserve->getFrlFinanceBlockedReason()): ?>: <?=$reason?>.<?php else: ?>.<? endif; ?>
                                <br/>��� �������� � �������� �������������� �������, ����������, ���������� ������.
                            </div>
                                <?php endif; ?>
                            <div class="b-buttons b-buttons_padbot_15">
                                <a href="<?=$fn_url?>" class="b-button b-button_flat b-button_flat_green">
                                    ������� �� �������� "�������"
                                </a> 
                            </div>
                            <?php else: ?>
                            <div class="b-buttons b-buttons_padbot_15">
                                <a href="javascript:void(0)" 
                                   class="b-button b-button_flat b-button_flat_green b-button_disabled">
                                    �������� ������ ����������� 
                                </a>     
                            </div>
                            <?php endif; ?>
                    
<?php 
                    } else { 
?>
                            <?php if (!$is_frl_feedback && $is_allow_feedback) { ?>
                            <div class="b-layout__txt b-layout__txt_padbot_15">
                                �� ������ �������� ����� ���������. ������� �� ��������������!
                            </div>
                            <div class="b-buttons b-buttons_padbot_15">
                                <a class="b-button b-button_flat b-button_flat_green" 
                                   href="javascript:void(0);" 
                                   data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                                    �������� �����
                                </a>
                                <?php if($is_emp_feedback): ?>
                                <a class="b-button b-buttons_padleft_20" 
                                   href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id)?>">
                                    ����� ���������
                                </a>
                                <?php endif; ?>                                
                            </div>            
<?php
                    $this->widget('TServiceOrderFeedback', array('data' => array(
                        'idx' => $order_id,
                        'hash' => $hash,
                        'pay_type' => $pay_type,
                        'rating' => $emp_rating,
                        'is_close' => true
                    )));
?>                    
                            <?php } else { ?>
                            <div class="b-layout__txt b-layout__txt_padbot_15">
                                ������� ���������� ������ ��������, ������� �� ��������������!
                            </div>
                            <div class="b-buttons b-buttons_padbot_15">
                                <?php if($is_frl_feedback): ?>
                                <a class="b-button" 
                                   href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id, $employer['login'])?>">
                                    ��� ����� ���������
                                </a>                        
                                <?php endif; ?>
                                <?php if($is_emp_feedback): ?>
                                <a class="b-button <?php if($is_frl_feedback): ?>b-buttons_padleft_20<?php endif; ?>" 
                                   href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id)?>">
                                    ����� ���������
                                </a>
                                <?php endif; ?>
                            </div>                     
                            <?php } ?>
                        <?php } ?>
                    
                    <?php } ?>
                    

                                  
                </td>
            </tr>  
        </table>
<?php
        } else {
        
        if($is_adm)
        {
?>
        <table class="b-layout__table">
            <tr class="b-layout__tr">
                <td class="b-layout__td">
                    <div class="b-layout__txt b-layout__txt_color_000 b-layout__txt_padbot_5">
                        �������� <?php echo $emp_fullname ?> �������� �������������� � ������ �����.
                    </div>
                    <?php if($is_emp_feedback){ ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        ����� ���������:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15  ">
                        <?=$emp_feedback?>
                    </div>
                    <?php } ?> 
                    <?php if($is_frl_feedback){ ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        ����� �����������:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15  ">
                        <?=$frl_feedback?>
                    </div>  
                    <?php } ?> 
                </td>
            </tr>
        </table>
<?php
        }
        elseif($is_emp)
        {
//------------------------------------------------------------------------------
// �������� ������ ����� � ������� ��� ���.
// ������ ��� ���������.
//------------------------------------------------------------------------------            
            $icon_action = $pay_type == TServiceOrderModel::PAYTYPE_RESERVE && !$reserve->isStatusPayPayed() ? 'pay' : 'close';
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
<?php
                if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
//------------------------------------------------------------------------------
// �������� ������ ����� � ������� ��� ��� ��� ����� � �������� �������.
// ������ ��� ���������.
//------------------------------------------------------------------------------
                 
                    if($reserve->isStatusPayPayed()):
//------------------------------------------------------------------------------
// �������� ������ ����� � ������� ��� ��� ��� ����� � �������� �������.
// ������ ��� ���������. ������ �������� ����������� � ������ ������.
//------------------------------------------------------------------------------ 
                        $price = tservices_helper::cost_format($reserve->getPrice(),true, false, false);
?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                        <span class="b-icon b-icon_sbr_oask"></span>
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                            � ���������� �������
                        </a>
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        �������������� ���������, ����� ������.
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        � ������� ����������� <?=$price?> <?=$reserve->isFrlPhis()?'(�� ������� 13% ����) ':''?>&mdash; <b>����� ���������</b>
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?php if($is_allow_feedback && !$is_emp_feedback): ?>
                            �� ������ �������� ����� �����������.<br/>
                        <?php endif; ?>
                        ������� ���������� ������ ��������, ������� �� ��������������!
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                        <?php if($is_emp_feedback): ?>
                        <a class="b-button" 
                           href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id, $freelancer['login'])?>">
                            ��� ����� �����������
                        </a>                        
                        <?php elseif($is_allow_feedback): ?>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            �������� �����
                        </a>
                        <?php endif; ?>
                        <?php if($is_frl_feedback): ?>
                        <a class="b-button <?php if($is_emp_feedback || $is_allow_feedback): ?>b-buttons_padleft_20<?php endif; ?>" 
                           href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id)?>">
                            ����� �����������
                        </a>
                        <?php endif; ?>
                    </div>
<?php                
                    else:
//------------------------------------------------------------------------------
// �������� ������ ����� � ������� ��� ��� ��� ����� � �������� �������.
// ������ ��� ���������. �������� ������� ����� �����������.
//------------------------------------------------------------------------------
                        $price = tservices_helper::cost_format($reserve->getPrice() ,true, false, false);
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������� ����� ���������� ������ &mdash; ������� ����
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        �� ������� � ����������� ��������� ���������� ������ �� ������. ����������� ����� ����������� ����������������� ���� ����� ������.
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        � ������� ����������� <?=$price?> &mdash; <b>������� �����</b><br>
                        ������� ���������� ������ ��������, ������� �� ��������������!<br>
                    </div>
                    <?php if(!$is_emp_feedback && $is_allow_feedback): ?>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        �� �������� �� <?=$date_feedback?> �������� �����.
                    </div>
                    <?php endif; ?>
                    <?php if((!$is_emp_feedback && $is_allow_feedback) || $is_frl_feedback): ?>
                    <div class="b-buttons b-buttons_padbot_15">
                        <?php if($is_emp_feedback): ?>
                        <a class="b-button" 
                           href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id, $freelancer['login'])?>">
                            ��� ����� �����������
                        </a>  
                        <?php elseif($is_allow_feedback): ?>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            �������� �����
                        </a>                        
                        <?php endif; ?>
                        <?php if($is_frl_feedback): ?>
                        <a class="b-button <?php if($is_emp_feedback || $is_allow_feedback): ?>b-buttons_padleft_20<?php endif; ?>" 
                           href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id)?>">
                            ����� �����������
                        </a>
                        <?php endif; ?>                        
                    </div>
                    <?php endif; ?>
<?php
                    endif;
                
                    if($is_allow_feedback && !$is_emp_feedback): 
                        $this->widget('TServiceOrderFeedback', array('data' => array(
                            'idx' => $order_id,
                            'hash' => $hash,
                            'pay_type' => $pay_type,
                            'rating' => $frl_rating,
                            'is_close' => true
                        )));
                    endif;
                
                else:
//------------------------------------------------------------------------------
// �������� ������ ����� � ������� ��� ��� ��� ������� �����.
// ������ ��� ���������.
//------------------------------------------------------------------------------
?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������ ������� &mdash; ����� ������
                    </div>
                    
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        ������� ���������� ������ ��������, ������� �� ��������������!
                        <?php if (!$is_emp_feedback && $is_allow_feedback): ?>
                            <br>�� �������� �� <?=$date_feedback?> �������� �����.
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($is_emp_feedback): ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        <a class="b-layout__link" 
                           href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id, $freelancer['login'])?>">
                            ��� �����
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($is_frl_feedback): ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        <a class="b-layout__link" href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id)?>">
                            ����� �� �����������
                        </a>
                    </div>
                    <?php endif ?>
                    
                    
                    <?php if (!$is_emp_feedback && $is_allow_feedback): ?>
                    
                    <div class="b-buttons b-buttons_padbot_15">
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            �������� �����
                        </a>
                    </div>
<?php
                $this->widget('TServiceOrderFeedback', array('data' => array(
                    'idx' => $order_id,
                    'hash' => $hash,
                    'pay_type' => $pay_type,
                    'rating' => $frl_rating,
                    'is_close' => true
                )));
?>
                    
                    <?php endif ?>
                    
                    
<?php
                endif;
?>
                </td>
            </tr>  
        </table>
<?php
        }
        else
        {
//------------------------------------------------------------------------------
// �������� ������ ����� � ������� ��� ���.
// ������ ��� �����������.
//------------------------------------------------------------------------------            

            $icon_action = $pay_type == TServiceOrderModel::PAYTYPE_RESERVE && !$reserve->isStatusPayPayed() ? 'pay' : 'close';
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic" alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>                
                <td class="b-layout__td b-layout__td_ipad">
<?php
//------------------------------------------------------------------------------
// �������� ������ ����� � ������� ��� ���.
// ������ ��� ����������� �� ����� ������� �������.
//------------------------------------------------------------------------------ 
                if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
                   
                    if($reserve->isStatusPayPayed()):
//------------------------------------------------------------------------------
// �������� ������ ����� � ������� ��� ���.
// ������ ��� ����������� �� ����� ������� �������.
// ������ �������� � ������ ������.
//------------------------------------------------------------------------------
                        $price = tservices_helper::cost_format($reserve->getPrice(), true, false, false);
?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                       <span class="b-icon b-icon_sbr_oask"></span>
                       <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                           � ���������� �������
                       </a>
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        �������������� ���������, ����� ������.
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        � ������� ����������� <?=$price?> <?=$reserve->isFrlPhis()?'(�� ������� 13% ����) ':''?>&mdash; <b>����� ���������</b>
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?php if(!$is_frl_feedback && $is_allow_feedback): ?>
                            �� ������ �������� ����� ���������. <br/>
                        <?php endif; ?>
                        ������� ���������� ������ ��������, ������� �� ��������������!
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                        <?php if($is_frl_feedback): ?>
                        <a class="b-button" 
                           href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id, $employer['login'])?>">
                            ��� ����� ���������
                        </a>                        
                        <?php elseif($is_allow_feedback): ?>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            �������� �����
                        </a>
                        <?php endif; ?>
                        <?php if($is_emp_feedback): ?>
                        <a class="b-button <?php if($is_frl_feedback || $is_allow_feedback): ?>b-buttons_padleft_20<?php endif; ?>" 
                           href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id)?>">
                            ����� ���������
                        </a>
                        <?php endif; ?>
                    </div>
<?php
                        if(!$is_frl_feedback && $is_allow_feedback):
                            $this->widget('TServiceOrderFeedback', array('data' => array(
                                'idx' => $order_id,
                                'hash' => $hash,
                                'pay_type' => $pay_type,
                                'rating' => $emp_rating,
                                'is_close' => true
                            )));
                        endif;
                        
                    elseif($reserve->isStatusPayInprogress()): 
//------------------------------------------------------------------------------
// �������� ������ ����� � ������� ��� ���.
// ������ ��� ����������� �� ����� ������� �������.
// � �������� �������. �������� ������ �������
//------------------------------------------------------------------------------   
                        $pay_type_txt = ReservesHelper::getInstance()->getPayoutType($reserve_data['id']);
                        $price = tservices_helper::cost_format($reserve->getPrice(),true, false, false);
?> 
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                       <span class="b-icon b-icon_sbr_oask"></span>
                       <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                           � ���������� �������
                       </a>
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������� ����� ���������� ������ &mdash; ������� ����
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        �������� ������ � ���������� ��������� ���������� ������ �� ������.<br />
                        � ������� ����������� <?=$price?> &mdash; <b>������� �����</b><br />
                        ��������, ����������. <?=$pay_type_txt?> 
                     <?php 
                        if($reserve->getNDFL()):
                            $tax_price = tservices_helper::cost_format($reserve->getNDFL(), true, false, false);
                     ?>  
                        �������� ��������, ��� �� ��� ����� ����� ������� ���� � ������� <?=$tax_price?>.
                     <?php
                        endif;
                     ?>
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                        <?php if($is_frl_feedback): ?>
                        <a class="b-button" 
                           href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id, $employer['login'])?>">
                            ��� ����� ���������
                        </a>                        
                        <?php elseif($is_allow_feedback): ?>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            �������� �����
                        </a>
                        <?php endif; ?>
                        <?php if($is_emp_feedback): ?>
                        <a class="b-button <?php if($is_frl_feedback || $is_allow_feedback): ?>b-buttons_padleft_20<?php endif; ?>" 
                           href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id)?>">
                            ����� ���������
                        </a>
                        <?php endif; ?>
                    </div>           
<?php 

                        if(!$is_frl_feedback && $is_allow_feedback):
                            $this->widget('TServiceOrderFeedback', array('data' => array(
                                'idx' => $order_id,
                                'hash' => $hash,
                                'pay_type' => $pay_type,
                                'rating' => $emp_rating,
                                'is_close' => true
                            )));
                        endif;

                    else:
//------------------------------------------------------------------------------
// �������� ������ ����� � ������� ��� ���.
// ������ ��� ����������� �� ����� ������� �������.
// ����������� ������� �����.
//------------------------------------------------------------------------------  
                        $is_reserve_error = $reserve->isStatusPayError();
                        $fn_url = sprintf("/users/%s/setup/finance/", $freelancer['login']);
                        $price = tservices_helper::cost_format($reserve->getPrice(),true, false, false); 
?> 
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������� ����� ���������� ������ &mdash; ������� ����
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        <?php echo $fullname ?> ������ � ���������� ��������� ���������� ������ �� ������.<br/>
                        � ������� ����������� <?=$price?> <?=$reserve->isFrlPhis()?'(�� ������� 13% ����) ':''?>
                        - <b>
                            <?php if($is_reserve_error): ?>
                                ������� �������� ��������������
                            <?php else: ?>
                                �������� ������� �����
                            <?php endif; ?>
                            </b><br/>
                        
                        <?php if(!$reserve->isFrlFinanceFailStatus()): ?>
                            <?php if($is_reserve_error): ?>
                                <?php if($reserve->getReasonPayout()): ?>
                                <strong>������� �������������� �� �������: </strong>
                                <?=$reserve->getReasonPayout()?>
                                <?php else: ?>
                                � ���������, ��� ������� ����� �������� ������. <br/>
                                ����������, ���������, ���������� �� ��������� ������� �� �������� �������, � ��������� ������ �� �������.
                                <?php endif; ?>
                            <?php else: ?>    
                                �����������, ����������, ������� �����, ����� �� ����� ����������� �� ������� ��� ��������.
                                <?php if(!$reserve->isFrlAllowFinance()): ?>
                                <br/><br/>
                                �������� ��������: ����� �������������� ������� ��� ���������� ��������� ������ �� �������� "�������".<br/>
                                <?=session::getFlashMessages('isValidUserReqvs')?>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>    
                    </div>
                    <?php if($reserve->isFrlFinanceFailStatus()): ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-fon_overflow_hidden">
                        � ���������, �� �������� ������� ������� ������������ 
                        ������<?php if($reason = $reserve->getFrlFinanceBlockedReason()): ?>: <?=$reason?>.<?php else: ?>.<? endif; ?>               
                        <br/>��� �������� � �������� ������� �������, ����������, ���������� ������.
                    </div>
                    <?php endif; ?>
                    <div class="b-buttons b-buttons_padbot_20">
                    <?php if($reserve->isFrlAllowFinance()): ?>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           data-popup="<?=ReservesPayoutPopup::getPopupId($order_id)?>" 
                           data-url="<?=$order_url?>" 
                           href="javascript:void(0);">
                            <?php if($is_reserve_error): ?>
                                �������� ����������� �������
                            <?php else: ?>
                                ����������� ������� �����
                            <?php endif; ?>
                        </a>
                        <?php if($is_reserve_error): ?>
                        <a href="<?=$fn_url?>" class="b-button b-button_flat b-button_flat_green">
                            ������� �� �������� "�������"
                        </a>                        
                        <?php endif; ?>
                    <?php elseif(!$reserve->isFrlFinanceValid()): ?>
                        <a href="<?=$fn_url?>" class="b-button b-button_flat b-button_flat_green">
                            ������� �� �������� "�������"
                        </a>        
                    <?php else: ?>
                        <a href="javascript:void(0)" 
                           class="b-button b-button_flat b-button_flat_green b-button_disabled">
                            �������� ������ ����������� 
                        </a>                        
                    <?php endif; ?>
                        
                        <?php if($is_emp_feedback): ?>
                        <a class="b-button b-buttons_padleft_20" 
                           href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id)?>">
                            ����� ���������
                        </a>
                        <?php endif; ?>
                    </div>
<?php
                        //�������� ����� ��� ������������� ������
                        if(!$is_list && $reserve->isFrlAllowFinance()) 
                        {
                            $ndfl = null;
                            if ($reserve->getNDFL()) 
                            {
                                $ndfl = tservices_helper::cost_format(
                                        $reserve->getNDFL(),true, false, false);
                            }
        
                            $this->widget('ReservesPayoutPopup', array(
                                'price' => $reserve->getPrice(),
                                'options' => array(
                                    'idx' => $order_id,
                                    'hash' => $hash,
                                    'is_feedback' => $is_frl_feedback,
                                    'is_allow_feedback' => $is_allow_feedback,
                                    'price' => $price,
                                    'price_ndfl' => $ndfl,
                                    'price_all' => tservices_helper::cost_format($reserve->getPriceWithOutNDFL(),true, false, false),
                                    'fn_url' => ($reserve->isAllowEditFinance($reserve_data['frl_id'], false))?$fn_url:false
                            )));
                        }
                        
                    endif;
                else:
?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������ ������� &mdash; ����� ������
                    </div>
                    
                    <div class="b-layout__txt b-layout__txt_padbot_5">
                        �������� �������� �����, ������� �� ��������������!
                        <?php if(!$is_emp_feedback && $is_allow_feedback): ?>
                            <br />�� �������� �� <?=$date_feedback?> �������� �����.
                        <?php endif; ?>
                    </div>
                    
                    
                    <?php if($is_frl_feedback): ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        <a class="b-layout__link" href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id, $employer['login'])?>">��� �����</a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($is_emp_feedback): ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        <a class="b-layout__link" href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id)?>">����� �� ���������</a>
                    </div>
                    <?php endif; ?>

                    <?php if(!$is_frl_feedback && $is_allow_feedback): ?>
                    <div class="b-buttons b-buttons_padbot_15">
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            �������� �����
                        </a>
                    </div>
<?php
                    $this->widget('TServiceOrderFeedback', array('data' => array(
                        'idx' => $order_id,
                        'hash' => $hash,
                        'pay_type' => $pay_type,
                        'rating' => $emp_rating,
                        'is_close' => true
                    )));
?>
                    <?php endif; ?>
<?php
            endif;
?>
                </td>
            </tr>  
        </table>       
<?php

        }
        }
?>

<?php

    } 
    elseif($order_status == TServiceOrderModel::STATUS_FRLCLOSE)
    {
//------------------------------------------------------------------------------
// ����������� �������� � ���������� ������
// ������ ��� ������.
//------------------------------------------------------------------------------        
        if($is_adm)
        {
?>
        <table class="b-layout__table">
            <tr class="b-layout__tr">
                <td class="b-layout__td">
<?php 
                if (isset($reserve_data['arbitrage_id'])): 

?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'��������':'�����������'?> ��������� � ��������
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
<?php
                        //�������� ����� ��� �������
                        $this->widget('ReservesArbitrageForm', array('data' => array(
                            'order_id' => $order_id,
                            'price' => $reserve_data['price']
                        )));

                else: 
?>                    
                    <div class="b-layout__txt b-layout__txt_color_000 b-layout__txt_padbot_5">
                        ����������� <?php echo $frl_fullname ?> �������� �������������� � ������ �����.
                    </div>
                    <?php if($is_emp_feedback){ ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        ����� ���������:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$emp_color?>">
                        <?=$emp_feedback?>
                    </div>
                    <?php } ?> 
                    <?php if($is_frl_feedback){ ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        ����� �����������:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?>">
                        <?=$frl_feedback?>
                    </div>  
                    <?php } ?> 
<?php
                endif;
?>
                </td>
            </tr>
        </table>
<?php
        }
        elseif($is_emp)
        {
//------------------------------------------------------------------------------
// ����������� �������� � ���������� ������. 
// ������ ��� ���������.
//------------------------------------------------------------------------------            
            $icon_action = $reserve_data['arbitrage_id'] > 0 ? 'arbitrage' : 'run';   
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
<?php
//------------------------------------------------------------------------------
// ����������� �������� � ���������� ������ �� ����� ������� �����. 
// ������ ��� ���������.
//------------------------------------------------------------------------------
                if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
                    
                    //���� ������ �� ��������
                    if(isset($reserve_data['arbitrage_id'])):
?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_right">
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/topic/483825-arbitrazh-v-zakazah-s-rezervirovaniem/">
                            ��������� �� ���������
                        </a>
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ���������, ���� ��� ������������
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'�� ����������':'����������� ���������'?> � ��������, ������� ����� �� ������������ �������. � ������� ���������� ���� 
                        �� ������ �������� � ���������� ���� ��� �������������� ���������, ���������� ����� ���� � 
                        ������������. �� ��������� ������������ ������������ ����� �������� ����������� ������� 
                        (� �������, �������� ��� ���������� ����������������� �����). ��������, ����������.
                    </div>
                    <div class="b-layout__txt b-layout__txt_color_666 b-layout__txt_padbot_5 b-layout__txt_bold">
                        ������� ��������� � ��������:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_20 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             �������� �����
                         </a>
                    </div>          

<?php
                    else:    
?>                    
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    � ���������� �������
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������� ����� ���������� ������ &mdash; ������ ���������
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">

                        ����������� �������� ���������� ������ � ������. ����������, ������� ��������� ������ � ��������� �������������� (� �������� ����������������� ����� �����������) ��� ������� ����� � ������, ���� �� �������� �� ���������.
                                
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             �������� �����
                         </a>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-duplicate="1"
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            ��������� ��������������
                        </a>
                        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; ��� &#160;
                        <a class="b-layout__link" 
                           href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'fix'); ?>" 
                           data-duplicate="2"
                           onClick="">
                            ������� ����� � ������
                        </a>
                        &#160; ��� &#160;
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                           href="javascript:void(0);" 
                           data-url="<?=$order_url?>"
                           data-popup="<?=ReservesArbitragePopup::getPopupId($order_id)?>"
                           data-duplicate="3">
                            ���������� � ��������
                        </a>
                        </span>
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20 b-layout__txt_padbot_10">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span>���� � �������� �������������� � ��� ��������� �������� � ������������, ����������� ���������� � �������� � ������������� �������� � ������� �������.
                    </div>                    
                    <?php $this->widget('ReservesArbitragePopup', array('data' => array(
                        'idx' => $order_id
                    ))) ?>
<?php
                    endif;
                else:
//------------------------------------------------------------------------------
// ����������� �������� � ���������� ������ �� ������� �����. 
// ������ ��� ���������.
//------------------------------------------------------------------------------                    
?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������ ������� &mdash; ������ ���������
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">

                        ����������� �������� ���������� ������ � ������. ����������, ������� ��������� ������ � ��������� �������������� (������� ������ �����������) ��� ������� ����� � ������, ���� �� �������� �� ���������.
                                
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             �������� �����
                         </a>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>"
                           data-duplicate="1"
                           data-url="<?=$order_url?>">
                            ��������� ��������������
                        </a>
                        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; ��� &#160;</span>
                        <a class="b-layout__link" 
                           href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'fix'); ?>" 
                           onClick="" data-duplicate="2">������� ����� � ������</a>
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20 b-layout__txt_padbot_10">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $no_reserve_warning ?>
                    </div>                    
<?php
                endif;

                $this->widget('TServiceOrderFeedback', array('data' => array(
                    'idx' => $order_id,
                    'hash' => $hash,
                    'pay_type' => $pay_type,
                    'rating' => $frl_rating,
                    'is_close' => false
                )));
?>                    
                </td>
            </tr>  
        </table>
<?php
        }
        else
        {
//------------------------------------------------------------------------------
// ����������� �������� � ���������� ������. 
// ������ ��� �����������.
//------------------------------------------------------------------------------             
            $icon_action = $reserve_data['arbitrage_id'] > 0 ? 'arbitrage' : 'run';
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
                    
<?php
    //------------------------------------------------------------------------------
    // ����������� �������� � ���������� ������ �� ����� ������� �����. 
    // ������ ��� �����������.
    //------------------------------------------------------------------------------
            if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
                        
                    //���� ������ �� ��������
                    if(isset($reserve_data['arbitrage_id'])):
?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_right">
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/topic/483825-arbitrazh-v-zakazah-s-rezervirovaniem/">
                            ��������� �� ���������
                        </a>
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ���������, ���� ��� ������������
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'�������� ���������':'�� ����������'?> � ��������, ������� ����� �� ������������ �������. 
                        � ������� ���������� ���� �� ������ �������� � ���������� ���� ��� �������������� ���������, 
                        ���������� ����� ���� � ����������. �� ��������� ������������ ������������ ����� �������� ����������� ������� 
                        (� �������, �������� ��� ���������� ����������������� �����). ��������, ����������.
                    </div>
                    <div class="b-layout__txt b-layout__txt_color_666 b-layout__txt_padbot_5 b-layout__txt_bold">
                        ������� ��������� � ��������:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_20 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             �������� �����
                         </a>
                    </div>          
<?php
                    else:                        
?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                       <span class="b-icon b-icon_sbr_oask"></span>
                       <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                           � ���������� �������
                       </a>
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                         ����� � ������� ����� ���������� ������ &mdash; ������ ���������
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        �������� ��������� � ���, ��� �� ��������� ���������� ������. ��������, ����������, ����� �������� ������ ��������� ������ � �������� �������������� (� �������� ��� ����������������� �����) ��� ������ ����� � ������, ���� �� �������� �� ���������.
                    </div>
                    
                    
                    
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             �������� �����
                         </a>
                        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; ��� &#160;
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                           href="javascript:void(0);" 
                           data-url="<?=$order_url?>"
                           data-duplicate="1"
                           data-popup="<?=ReservesArbitragePopup::getPopupId($order_id)?>">
                            ���������� � ��������
                        </a>
                        </span>
                    </div>  
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20 b-layout__txt_padbot_10">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span>
                        ���� � �������� �������������� � ��� ��������� �������� � ����������, 
                        ����������� ���������� � �������� � ������������� �������� � ������� �������.
                    </div>                    
<?php 
                    $this->widget('ReservesArbitragePopup', array('data' => array(
                        'idx' => $order_id
                    ))); 
                    
                 endif;     
                    
            else:
    //------------------------------------------------------------------------------
    // ����������� �������� � ���������� ������ �� ������� �����. 
    // ������ ��� �����������.
    //------------------------------------------------------------------------------                   
?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        ����� � ������ ������� &mdash; ������ ���������
                    </div>                        
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        �������� ��������� � ���, ��� �� ��������� ���������� ������. ��������, ����������, ����� �������� ������ ��������� ������ � �������� �������������� (������� ���� ������) ��� ������ ����� � ������, ���� �� �������� �� ���������.
                    </div>
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             �������� �����
                         </a>
                    </div>  
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20 b-layout__txt_padbot_10">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $no_reserve_warning ?>
                    </div>                    
<?php
            endif;
?>
                </td>
            </tr>
        </table>
<?php
        }
?>
<?php 
    }
