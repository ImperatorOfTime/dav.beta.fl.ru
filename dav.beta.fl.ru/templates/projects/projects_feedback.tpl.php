<?php

/**
 * Popup ������ ��� �������� ������ �/��� �������� �������
 */

?>
<div id="project_feedback_popup" class="b-shadow b-shadow_center b-shadow_width_520 b-shadow_hide b-shadow__quick b-shadow_zindex_110" style="display:block;">
  <div class="b-shadow__body b-shadow__body_pad_15_20">
   <h2 class="b-layout__title">
       ���������� ��������������
   </h2>
   <div id="project_feedback_label" class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_10">
       �������� ��������������, �� ������������� ����������� ������� ������ � ���������� ��������.
   </div>
   <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_10">
       ��� ����� � ��������������:
   </div>
   <div class="b-layout b-layout_padleft_20">
       <form action="" method="post">
       <input type="hidden" name="project_id" value="" />
       <input type="hidden" name="hash" value="" />
       <div class="b-radio b-radio_layout_horizontal">
          <div class="b-radio__item b-radio__item_padbot_20 b-radio__item_padright_20">
              <input data-validators="rating" type="radio" value="1" name="rating" class="b-radio__input" id="plus">
              <label for="plus" class="b-radio__label b-radio__label_fontsize_13 b-radio__label_color_6db335">�������������</label>
          </div>
          <div class="b-radio__item b-radio__item_padbot_20">
              <input data-validators="rating" type="radio" value="-1" name="rating" class="b-radio__input" id="minus">
              <label for="minus" class="b-radio__label b-radio__label_fontsize_13 b-radio__label_color_c10600">�������������</label>
          </div>
       </div>
       <div class="b-textarea">
           <textarea data-validators="maxLength:500" class="b-textarea__textarea b-textarea__textarea_italic" rows="5" cols="80" maxlength="500" name="feedback" placeholder="������� ����� ������"></textarea>
       </div> 
       <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5">������������ ����� ������ &mdash; 500 ��������.</div>      
       <div class="b-buttons b-buttons_padtop_20">
             <a href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green" onclick="ProjectsFeedback.submit();">
                 <span id="project_feedback_submit_label">������� ������</span>
             </a>
             <span class="b-layout__txt b-layout__txt_fontsize_11">&#160; ��� 
                 <a class="b-layout__link" href="javascript:void(0);" onclick="ProjectsFeedback.close();">���������� ��������������</a>
             </span>
       </div>
       </form>
   </div> 
   </div>    
   <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>