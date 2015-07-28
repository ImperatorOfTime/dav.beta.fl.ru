<?php

    $status = @$project['status'];
    $offer_status = @$offer['status'];
    $is_emp = is_emp();
    $project_id = intval(@$project['id']);
 
    
    $emp_feedback = @$project['emp_feedback'];
    $is_emp_feedback = (!empty($emp_feedback));
    $emp_is_good = (@$project['emp_rating'] > 0);
    $emp_rating = intval(@$project['emp_rating']);
    
    $emp_color = ($emp_is_good)?'6db335':'c10600';
    $emp_feedback = reformat($emp_feedback, 30);      
    
    
    $frl_feedback = @$project['frl_feedback'];
    $is_frl_feedback = (!empty($frl_feedback));
    $frl_is_good = (@$project['frl_rating'] > 0);
    $frl_rating = intval(@$project['frl_rating']);    
    
    $frl_color = ($frl_is_good)?'6db335':'c10600';
    $frl_feedback = reformat($frl_feedback, 30); 
    
    
    $kind = @$project['kind'];
    
    
    $emp_warn_txt = '�������� ��������, ��� ��� �������������� �� �������������� ������ ��� �����, ��������� � ��������������� ��� �������������� ����������� ������ ��� ����������� ���������� ����������.';
    $frl_warn_txt = '�������� ��������, ��� ��� �������������� �� �������������� ������ ��� �����, ��������� � ������� ������ � ��������� �� ����������.';
    
?>
<table class="b-layout__table b-layout__table_width_full">
    <tr class="b-layout__tr">
        <td class="b-layout__td b-layout__td_width_60">
<?php
    $icon = 'tu/ico_po_offers.png';
    switch($status)
    {
        case projects_status::STATUS_NEW:
            if (!$project['exec_id']) {
                if((!$is_emp && $offer_status == projects_status::STATUS_DECLINE) || 
                   ($is_emp && $kind == 9 && $offer_status == projects_status::STATUS_CANCEL)) $icon = 'tu/ico_po_refuse.png';
                elseif((!$is_emp && $offer_status == projects_status::STATUS_CANCEL) || 
                   ($is_emp && $kind == 9 && $offer_status == projects_status::STATUS_DECLINE)) $icon = 'tu/ico_po_canceled.png';
            }   
            break;
        case projects_status::STATUS_ACCEPT:
            $icon = 'tu/ico_po_executor.png';
            break;
        case projects_status::STATUS_FRLCLOSE:
        case projects_status::STATUS_EMPCLOSE:
            $icon = 'tu/ico_po_executor.png';
            if($is_emp && $is_emp_feedback) $icon = ($emp_is_good)?'good.png':'bad.png';
            elseif(!$is_emp && $is_frl_feedback) $icon = ($frl_is_good)?'good.png':'bad.png';
            break;
    }
?>
             <img class="b-user__pic"  alt="" src="/images/<?=$icon?>"/>
        </td>
        <td class="b-layout__td">
<?php 

if($status == projects_status::STATUS_NEW)
{
     if($is_exec)
     {
         if($is_adm)
         {
?>             
             <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                ����������� ������, �� ��� �� ���������� ������� � �������.
            </div>  
<?php    } 
         elseif($is_emp)
         {           
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                ����������� ������, �� ��� �� ���������� ������� � �������.
            </div>            
            <div class="b-layout__txt b-layout__txt_padbot_10">
                 <?php echo $fullname ?> ������� ���� ����������� ����� ������������ �������.<br/> 
                 ��� ������ �� ���������� ���, �������� ���������� ������. ��������, ����������. 
            </div>
            <div class="b-buttons">
                <a class="b-button b-button_flat b-button_flat_red" 
                   href="javascript:void(0);" 
                   onClick="yaCounter6051055.reachGoal('proj_cancel'); ProjectsStatus.changeStatus(<?=projects_helper::getJsParams($project_id, 'cancel')?>);">
                   �������� �����������
                </a>
            </div>
<?php
         }
         else
         {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                �������� ���������� ��� ����� ������������ �������.
            </div>
            <div class="b-layout__txt b-layout__txt_padbot_10">
                �������� <?php echo $fullname ?> ���������� ��� ����� ������������ �������.<br/>
                �� ������ ����������� ��� ������� �������������� � ������ ���������� ������� ��� ���������� �� ����.
            </div>
            <div class="b-buttons b-buttons_padbot_10">
                <a href="javascript:void(0);" 
                   class="b-button b-button_flat b-button_flat_green" 
                   onClick="yaCounter6051055.reachGoal('proj_apply'); ProjectsStatus.changeStatus(<?=projects_helper::getJsParams($project_id, 'accept')?>);">
                    ������ ���������� �������
                </a>
                <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; ��� &#160;</span>
                <a class="b-button b-button_flat b-button_flat_red" 
                   href="javascript:void(0);" 
                   onClick="yaCounter6051055.reachGoal('proj_decline'); ProjectsStatus.changeStatus(<?=projects_helper::getJsParams($project_id, 'decline')?>);">
                    ���������� �� ����
                </a>
            </div>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_top_5 b-icon_margleft_-20"></span>������� ������ "������ ���������� �������", 
                �� ������������ ��������� ������, ���������� � �������, �� ������������� � ���������� ��������. <br/>�� �������������� ������ ��� �����, 
                ��������� � ������� ������, �������� �� ���������� � ���������� ���������������� ������.
            </div>            
<?php            
         }
     }
     else
     {
         if($is_adm)
         {
             //������������ ������ ������ ��� ������
             if($kind == 9 && $offer_status == projects_status::STATUS_CANCEL)
             {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_c7271e">
                �������� ������� ������.
            </div>
<?php                        
             }
             if($offer_status == projects_status::STATUS_DECLINE)
             {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_c7271e">
                ����������� ��������� �� �������.
            </div>
<?php            
             }
             else
             {
?>            
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                ����������� ������� ���� �� ������.
            </div>
<?php
             }  
         } 
         elseif($is_emp)
         {
             //������������ ������ �������� ���������
             if($kind == 9 && $offer_status == projects_status::STATUS_CANCEL)
             {
?>
            <div class="b-layout__txt">
                �� �������� ������.<br/>
                ����������� ������� � �������� ������ ����������� ��� ���������� ������ ������� �����������.
            </div>
<?php            
             }
             elseif($kind == 9 && $offer_status == projects_status::STATUS_DECLINE)
             {
?>
            <div class="b-layout__txt b-layout__txt_color_c7271e">
                � ���������, ����������� <?php echo $fullname ?> ��������� �� ���������� ������ �������.<br/>
                ����������� ���������� � �������� ������ ����������� ��� ���������� ������ ������� �����������.
            </div>            
<?php            
             }
             elseif($offer_status == projects_status::STATUS_DECLINE)
             {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_c7271e">
                ����������� ��������� �� �������.
            </div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_color_c7271e">
                � ���������, ����������� <?php echo $fullname ?> ��������� �� ���������� ������ �������.<br/>
                ����������� ���:<br/>
                <ol>
                    <li>������� ������� ����������� �� ����� ���������� �����������.</li>
                    <li>��� ������ ����������� ���������� ������� � �������, ������ � ��� ��������������.</li>
                    <li>�������� �� ����������� ��������� ������.</li>
                    <li>��������� ������ � �������� ��������.</li>
                </ol>
            </div>
<?php            
             }
             else
             {
?>            
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                ����������� ������� ���� �� ������.
            </div>
            <div class="b-layout__txt">
                ����������� ���:<br/>
                <ol>
                    <li>�� ����� ���������� ����������� ���������� ���������� ���������� (������������ �� ���������� ������).</li>
                    <li>�� ����������� ������� � ����������� ���������� ������ �����������.</li>
                    <li>��� ������ ����������� ���������� ������� � �������, ������ � ��� ��������������.</li>
                    <li>�������� �� ����������� ��������� ������.</li>
                    <li>��������� ������ � ���������� ��������.</li>
                </ol>
            </div>   
<?php
             }
         }
         else
         {
             if($offer_status == projects_status::STATUS_DECLINE)
             {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                �� ���������� �� �������.
            </div>
            <div class="b-layout__txt">
                �� ��������� ����������� ��������� ����� ������������ �������.<br/>
                <?php if($project['kind']!=9) { ?>��� ������ � ������� ������� � "�����������" �� "��������".<?php } ?>
            </div>  
<?php            
             }
             elseif($offer_status == projects_status::STATUS_CANCEL)
             {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_c7271e">
                �������� ������� ���� �����������.
            </div>
            <div class="b-layout__txt b-layout__txt_color_c7271e">
                � ���������, <?php echo $fullname ?> ������� ����������� ��� ����� ������������ �� �������.<br/>
				<?php if($project['kind']!=9) { ?>��� ������ � ������� ������� � "�����������" �� "��������".<?php } ?>
            </div>            
<?php               
             }           
         }
     }    
}
elseif($status == projects_status::STATUS_ACCEPT)
{
    if($is_adm)
    {
?>             
             <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_6db335">
                ������ � ������.
            </div>            
            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padbot_15">
                ����������� ���������� ������� � ������� � ��������� ���.<br/>
            </div> 
<?php    
    } 
    elseif($is_emp)
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_6db335">
                ������ � ������.
            </div>            
            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padbot_15">
                ����������� <?php echo $fullname ?> ���������� ������� � ������� � ��������� ���.<br/>
                � ����� ������ �� ������ ��������� �������������� � ������������, ���������� ������ ����������� ������ � �������� ����� (������������� ��� �������������).
            </div>
            <div class="b-buttons b-buttons_padbot_15">
                <a class="b-button b-button_flat b-button_flat_green" 
                   href="javascript:void(0);" 
                   onClick="ProjectsFeedback.open(<?=projects_helper::getJsCloseParams($project_id)?>);">
                    ��������� ��������������
                </a>
            </div> 
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $emp_warn_txt ?>
            </div>      
<?php
    }
    else
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_6db335">
                ������ � ������.
            </div>            
            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padbot_15">
                �� ����������� ������� � ������� � �������� �����������. 
                ����� ��� ���������� ��������� ������ � �������� ��������� ���������, ������� �� ���� ����� ������.<br/>
                � ����� ������ �� ������ ��������� �������������� �� ������� � �������� ����� (������������� ��� �������������).
            </div>
            
            <div class="b-buttons b-buttons_padbot_15">
                <a class="b-button b-button_flat b-button_flat_green" 
                   href="javascript:void(0);" 
                   onClick="ProjectsFeedback.open(<?=projects_helper::getJsCloseParams($project_id)?>);">
                    ��������� ��������������
                </a>
            </div>
            
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $frl_warn_txt ?>
            </div>
<?php            
    }
}
elseif($status == projects_status::STATUS_EMPCLOSE)
{
    if($is_adm)
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                ������ �������� � ������ ����������. 
            </div>
            <?php if($is_emp_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                ����� ���������:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$emp_color?> b-layout__txt_color_<?=$emp_color?>">
               <?=$emp_feedback?>
            </div>                    
             <?php } ?>
            <?php if($is_frl_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                ����� �����������:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?> b-layout__txt_color_<?=$frl_color?>">
               <?=$frl_feedback?>
            </div>
            <?php } ?>
<?php            
    }
    elseif($is_emp)
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_<?=$emp_color?>">
                ������ �������� � ������. 
            </div>
            <?php if($is_emp_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_color_<?=$emp_color?>">
                �� ��������� �������������� � ������������ � ������� ������.
            </div>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                ��� ����� �����������:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$emp_color?> b-layout__txt_color_<?=$emp_color?>">
               <?=$emp_feedback?>
            </div>                    
            <?php }elseif($is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_ b-layout__txt_color_6db335 b-layout__txt_padbot_10">
                �� ������ �������� ����� �� <?=$date_feedback?>
            </div>
            <div class="b-buttons b-buttons_padbot_15">
                <a class="b-button b-button_flat b-button_flat_green" 
                   href="javascript:void(0);" 
                   onclick="ProjectsFeedback.open(<?=projects_helper::getJsCloseParams($project_id, true, $frl_rating)?>);">
                    �������� �����
                </a>
            </div> 
            <?php }else{ ?>
            <div class="b-layout__txt b-layout__txt_color_ b-layout__txt_color_6db335 b-layout__txt_padbot_10">
                �� ��������� �������������� � ������������ � ������� ������.
            </div>                    
            <?php } ?>
            <?php if($is_frl_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                ����� �����������:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?> b-layout__txt_color_<?=$frl_color?>">
               <?=$frl_feedback?>
            </div>
            <?php } ?>
            <?php if(!$is_emp_feedback && $is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $emp_warn_txt ?>
            </div>
            <?php } ?>
<?php
    }
    else
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_<?=$frl_color?>">
                ������ �������� � ������. 
            </div>
            <?php if($is_frl_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_color_<?=$frl_color?>">
                �������� <?php echo $fullname ?> �������� �������������� � ������ ������.
            </div>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                ��� ����� ���������:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?> b-layout__txt_color_<?=$frl_color?>">
                <?=$frl_feedback?>
            </div>
            <?php if($is_emp_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                ����� ���������:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$emp_color?> b-layout__txt_color_<?=$emp_color?>">
                <?=$emp_feedback?>
            </div>  
            <?php } ?>                
            <?php }else{ ?>
            <?php if($is_emp_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_5">
                <?php echo $fullname ?> �������� �������������� � ������� ��� <?php if($emp_is_good){ ?>������������� �����.<?php }else{ ?>������������� �����.<?php } ?>
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$emp_color?> b-layout__txt_color_<?=$emp_color?>">
                <?=$emp_feedback?>
            </div>  
            <?php if($is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_10">
                �� ������ �������� �������� ����� �� <?=$date_feedback?>
            </div>
            <?php } ?>
            <?php }else{ ?>
            <div class="b-layout__txt b-layout__txt_color_6db335">
                �������� <?php echo $fullname ?> �������� �������������� � ������ ������.
            </div> 
            <?php if($is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padbot_10">
                �� ������ �������� ����� �� <?=$date_feedback?>
            </div>  
            <?php } ?>
            <?php } ?>
            <?php if($is_allow_feedback){ ?>
            <div class="b-buttons b-buttons_padbot_15">
                <a class="b-button b-button_flat b-button_flat_green" 
                   href="javascript:void(0);" 
                   onclick="ProjectsFeedback.open(<?=projects_helper::getJsCloseParams($project_id, true, $emp_rating)?>);">
                    �������� �����
                </a>
            </div>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $frl_warn_txt ?>
            </div> 
            <?php } ?>
            <?php } ?>            
<?php            
    }
}
elseif($status == projects_status::STATUS_FRLCLOSE)
{
    if($is_adm)
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                ������ �������� � ������ ������������. 
            </div>
            <?php if($is_emp_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                ����� ���������:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$emp_color?> b-layout__txt_color_<?=$emp_color?>">
               <?=$emp_feedback?>
            </div>                    
             <?php } ?>
            <?php if($is_frl_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                ����� �����������:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?> b-layout__txt_color_<?=$frl_color?>">
               <?=$frl_feedback?>
            </div>
            <?php } ?>
<?php            
    }
    elseif($is_emp)
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_<?=$emp_color?>">
                ������ �������� � ������. 
            </div>           
            <?php if($is_emp_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_color_<?=$emp_color?>">
                <?php echo $fullname ?> �������� �������������� � ������ ������.
            </div>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                ��� ����� �����������:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$emp_color?> b-layout__txt_color_<?=$emp_color?>">
                <?=$emp_feedback?>
            </div>
            <?php if($is_frl_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                ����� �����������:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?> b-layout__txt_color_<?=$frl_color?>">
                <?=$frl_feedback?>
            </div>  
            <?php } ?>                
            <?php }else{ ?>
            <?php if($is_frl_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_5">
                <?php echo $fullname ?> �������� �������������� � ������� ��� <?php if($frl_is_good){ ?>������������� �����.<?php }else{ ?>������������� �����.<?php } ?>
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?> b-layout__txt_color_<?=$frl_color?>">
                <?=$frl_feedback?>
            </div>
            <?php if($is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_10">
                �� ������ �������� �������� ����� �� <?=$date_feedback?>
            </div>
            <?php } ?>
            <?php }else{ ?>
            <div class="b-layout__txt b-layout__txt_color_6db335">
                <?php echo $fullname ?> �������� �������������� � ������ ������.
            </div> 
            <?php if($is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padbot_10">
                �� ������ �������� ����� �� <?=$date_feedback?>
            </div> 
            <?php } ?>
            <?php } ?>
            <?php if($is_allow_feedback){ ?>
            <div class="b-buttons b-buttons_padbot_15">
                <a class="b-button b-button_flat b-button_flat_green" 
                   href="javascript:void(0);" 
                   onclick="ProjectsFeedback.open(<?=projects_helper::getJsCloseParams($project_id, true, $frl_rating)?>);">
                    �������� �����
                </a>
            </div>                    
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $emp_warn_txt ?>
            </div>
            <?php } ?>
            <?php } ?>
<?php
    }
    else
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_<?=$frl_color?>">
                ������ �������� � ������. 
            </div> 
            <?php if($is_frl_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_color_<?=$frl_color?>">
                �� ��������� �������������� � ������� ������.
            </div>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                ��� ����� ���������:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?> b-layout__txt_color_<?=$frl_color?>">
                <?=$frl_feedback?>
            </div>
            <?php }else{ ?>
            <?php if($is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_ b-layout__txt_color_6db335 b-layout__txt_padbot_10">
                �� ������ �������� ����� �� <?=$date_feedback?>
            </div>
            <div class="b-buttons b-buttons_padbot_15">
                <a class="b-button b-button_flat b-button_flat_green" 
                   href="javascript:void(0);" 
                   onclick="ProjectsFeedback.open(<?=projects_helper::getJsCloseParams($project_id, true, $emp_rating)?>);">
                    �������� �����
                </a>
            </div> 
            <?php }else{ ?>
            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padbot_10">
                �� ��������� �������������� � ������� ������.
            </div>                    
            <?php } ?>
            <?php } ?>
            <?php if($is_emp_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                ����� ���������:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?php echo $emp_color ?> b-layout__txt_color_<?=$emp_color?>">
                <?=$emp_feedback?>
            </div>
            <?php } ?>
            <?php if(!$is_frl_feedback && $is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $frl_warn_txt ?>
            </div>
            <?php } ?>
<?php            
    }
}
?>            
        </td>                
    </tr> 
</table>