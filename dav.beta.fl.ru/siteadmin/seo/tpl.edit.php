<?php
?>
<div class="b-layout">	
    <h2 class="b-layout__title b-layout__title_padbot_30">�������������� ������</h2>
     <?php if($is_update) {?>
     <div class="b-fon b-fon_width_full b-fon_padbot_17">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_35 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf">
            <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>��������� ���������. <a href="/siteadmin/seo">� ������</a>
        </div>
     </div>
    <?php }//if?>
   
    
    <form method="post" enctype="multipart/form-data" id="seo_form">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" value="<?= (int) $message['id']?>">
        
        
        
        
        <h3>������</h3>
        <?php 
        for ($i=1; $i<=SeoValues::SIZE_TITLE; $i++) {
            $name = 'tu_title_'.$i;
            echo Template::render('tpl.field.php', array(
                'label' => '��������� '.$i,
                'name' => $name,
                'value' => $card[$name]
            ));
        } 
        for ($i=1; $i<=SeoValues::SIZE_TEXT; $i++) {
            $name = 'tu_text_'.$i;
            echo Template::render('tpl.field.php', array(
                'label' => '����� '.$i,
                'name' => $name,
                'value' => $card[$name]
            ));
        } 
        ?>
        
        <h3>����������</h3>
        <?php 
        for ($i=1; $i<=SeoValues::SIZE_TITLE; $i++) {
            $name = 'f_title_'.$i;
            echo Template::render('tpl.field.php', array(
                'label' => '��������� '.$i,
                'name' => $name,
                'value' => $card[$name]
            ));
        } 
        for ($i=1; $i<=SeoValues::SIZE_TEXT; $i++) {
            $name = 'f_text_'.$i;
            echo Template::render('tpl.field.php', array(
                'label' => '����� '.$i,
                'name' => $name,
                'value' => $card[$name]
            ));
        } 
        ?>
        
        <h3>�������� �����</h3>
        <?php 
        for ($i=1; $i<=SeoValues::SIZE_KEY; $i++) {
            $name = 'key_'.$i;
            echo Template::render('tpl.field.php', array(
                'label' => '����� '.$i,
                'name' => $name,
                'value' => $card[$name]
            ));
        } 
        ?>

        <div class="b-buttons b-buttons_padtop_40 b-buttons_padleft_132">
            <a class="b-button b-button_rectangle_color_green"  href="javascript:void(0)" onClick="$('seo_form').submit();">
                <span class="b-button__b1">
                    <span class="b-button__b2">
                        <span class="b-button__txt">���������</span>
                    </span>
                </span>
            </a>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="/siteadmin/seo">�����</a>
        </div>
	</form>
</div>	