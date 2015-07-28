<?php
    $new_project_url = $uid > 0?sprintf("/public/?step=1&kind=1"):"/welcome/customer/1/";
    $banner_promo_inline = true;
?>
<div class="b-land b-land_bg5 b-land_height_580 b-land_height_690_iphone">
    <div class="b-land__head b-land__head_padtop_48">
       
      <h1 class="b-page__title 
                 b-page__title_center 
                 b-page__title_color_fff 
                 b-page__title_uppercase 
                 b-page__title_padbot_17 
                 b-page__title_padbot_10_ipad 
                 b-layout__txt_padbot_null_iphone 
                 b-page__title_size55">
          ����� �����������<br/>� ����������� �� FL.RU
      </h1>
      <h2 class="b-page__title 
                 b-page__title_center 
                 b-page__title_size25 
                 b-page__title_color_fff 
                 b-page__title_padbot_45 
                 b-page__title_size18_iphone g-hide_iphone">
          ��� ����� ����������, ����� ��������� ������
      </h2>       
        
        
      <table class="b-layout__table b-layout__table_width_full b-layout__table_ipad">
          <tr class="b-layout__tr">
              <td class="b-layout__td 
                         b-layout__td_right  
                         b-layout__td_center_ipad 
                         b-layout__td_padtop_30
                         b-layout__td_padlr_0 
                         b-layout__td_width_half 
                         b-layout__td_ipad 
                         b-layout__td_block_iphone 
                         b-layout_width_full_iphone">
                  <div class="b-layout b-layout_text-center b-layout_inline-block b-layout_max-width_520 b-layout_width_full">
                        <a data-ga-event="{ec: 'user', ea: 'main_freelancerbutton_clicked',el: 'freelancer'}" 
                           href="/welcome/freelancer/1/" 
                           class="b-button b-button_flat 
                                   b-button_flat_orange 
                                   b-button_flat_sbig 
                                   b-button_flat_fs_25 
                                   b-button_flat_width_270 
                                   b-button_marglr_10_10">
                            � ���������
                        </a>
                        <div class="b-layout__txt b-layout__txt_lineheight_24 b-layout__txt_color_fff b-layout__txt_fontsize_20 b-layout__txt_pad_20 b-layout__txt_hide_iphone">
                            ������� ������ � ��������� ������<br/>
                            �� ������ ���������� ������, � �����������<br/>
                            �� ���������� ������ �������
                        </div>
                        <div class="b-layout__txt b-layout__txt_lineheight_24 b-layout__txt_color_fff b-layout__txt_fontsize_20 b-layout__txt_padtop_20 b-layout__txt_padbot_20 b-layout__txt_hide b-layout__txt_show_iphone">
                            ������� ������ � ��������� ������ 
                            �� ������ ���������� ������, � ����������� 
                            �� ���������� ������ �������
                        </div> 
                  </div>
              </td>
              <td class="b-layout__td 
                         b-layout__td_left  
                         b-layout__td_center_ipad 
                         b-layout__td_bordleft_fff 
                         b-layout__td_padtop_30 
                         b-layout__td_padlr_0 
                         b-layout__td_width_half 
                         b-layout__td_ipad 
                         b-layout__td_block_iphone 
                         b-layout__td_bord_null_iphone 
                         b-layout_width_full_iphone">
                 <div class="b-layout b-layout_text-center b-layout_inline-block b-layout_max-width_520 b-layout_width_full"> 
                        <h2 class="b-page__title 
                                   b-page__title_center 
                                   b-page__title_size25 
                                   b-page__title_color_fff 
                                   b-page__title_padbot_45 
                                   b-page__title_padbot_20_iphone 
                                   g-hidden g-show_iphone">
                            ��� ����� ����������, ����� ��������� ������
                        </h2> 

                        <a data-ga-event="{ec: 'user', ea: 'main_customerbutton_clicked',el: 'customer'}" 
                           href="<?=$new_project_url?>" 
                           class="b-button 
                                  b-button_flat 
                                  b-button_flat_green 
                                  b-button_flat_sbig 
                                  b-button_flat_fs_25 
                                  b-button_flat_width_270 
                                  b-button_marglr_10_10">
                            � ��������
                        </a>
                        <div class="b-layout__txt b-layout__txt_lineheight_24 b-layout__txt_color_fff b-layout__txt_fontsize_20 b-layout__txt_pad_20 b-layout__txt_hide_iphone">
                            ������ ����������� � ���������<br/>
                            ���������� ������ � ����<br/>
                            ����� ���������� ������
                        </div>
                        <div class="b-layout__txt b-layout__txt_lineheight_24 b-layout__txt_color_fff b-layout__txt_fontsize_20 b-layout__txt_padtop_20 b-layout__txt_padbot_20 b-layout__txt_hide b-layout__txt_show_iphone">
                            ������ ����������� � ��������� 
                            ���������� ������ � ���� 
                            ����� ���������� ������
                        </div> 
                 </div>
              </td>
          </tr>
      </table>
        
        
        <?php if (false): ?>

       
       <div class="b-layout b-layout_padleft_20 b-layout_padright_20 b-layout__txt_center b-layout_margbot_20">
           
           <div class="b-layout b-layout_inline-block b-layout_width_575 b-layout_pad_20 b-layout_overflow_hidden b-layout_width_auto_iphone">
               <a <?php if (!$uid): ?>data-ga-event="{ec: 'user', ea: 'main_customerbutton_clicked',el: 'customer'}"<?php endif; ?>
                  href="<?=$new_project_url?>" 
                  class="b-button b-button_flat b-button_flat_green b-button_flat_big b-button_flat_width_220 b-button_marglr_10_10 b-button_margbot_10_ipad">
                   ����� �����������
               </a>
               <?php if (!$uid): ?>
               <a  data-ga-event="{ec: 'user', ea: 'main_freelancerbutton_clicked',el: 'freelancer'}" 
                   href="/registration/" 
                   class="b-button b-button_flat b-button_flat_orange b-button_flat_big b-button_flat_width_220 b-button_marglr_10_10 b-button_margbot_10_ipad">
                   ����� ������
               </a>
               <?php endif; ?>
           </div>
           
       </div>
       
        <div class="b-layout b-layout_padleft_20 b-layout_padright_20 b-layout__txt_center">
            <div class="b-menu__banner b-menu__banner_ln1 b-menu__banner_inline">
                <a target="_blank" href="/promo/bezopasnaya-sdelka/" class="b-menu__link-banner b-menu__link-banner_margtopnull"><span class="b-icon b-icon__shield"></span>��������� � ��������� ����� ���������� ������</a>
            </div>
        </div>

        <?php endif; ?>
        
   </div>
</div>