<div class="b-layuot b-layout_pad_20">
	<table class="b-layout__table b-layout__table_width_full">
		<tr class="b-layout__tr">
				<td class="b-layout__td b-layout__td_padtop_10 b-layout__td_width_full_ipad">
					<div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_10">
                        <span class="b-layout__txt_nowrap">
                        <?php if($kind == 0 && !$_GET["trash"]) { ?>
                            <b>���</b>
                        <?php } else { //if?>
                            <a class="blue" href="?kind=0"><b>���</b></a>
                        <?php } //else?>
                            (<?= $conted_prj["kind_all"]?>)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </span>
                        <span class="b-layout__txt_nowrap">    
                        <?php if($kind == 1) { ?>
                            <b>�������</b>
                        <?php } else { //if?>
                            <a class="blue" href="?kind=1"><b>�������</b></a>
                        <?php } //else?> 
                            (<?= intval($conted_prj['kind_prj']) ?>) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                         </span>
                         <span class="b-layout__txt_nowrap">   
                        <?php if($kind == 3) { ?> 
                            <b>��������</b>
                        <?php } else { //if?>
                            <a class="blue" href="?kind=3"><b>��������</b></a>
                        <?php } //else?>    
                            (<?= intval($conted_prj['kind_office']) ?>)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </span>
                        <span class="b-layout__txt_nowrap">    
                        <?php if($kind == 2) { ?>
                            <b>��������</b>
                        <?php } else { //if?>
                            <a class="blue" href="?kind=2"><b>��������</b></a>
                        <?php } //else?>     
                            (<?= intval($conted_prj['kind_contest']) ?>)
                        </span>
					</div>
				</td>
				<td class="b-layout__td b-layout__td_padtop_10 b-layout__td_padleft_30 b-layout__td_width_full_ipad b-layout__td_pad_null_ipad">
					<div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_10">
                        <span class="b-layout__txt_nowrap">
                        <?php if(!$_GET["all"] && !$_GET["closed"] && !$_GET["trash"]) {?>
                            <b>��������</b>
                        <?php } else {//if?>
                            <a class="blue" href="?kind=<?= $kind?>"><b>��������</b></a>
                        <?php }//else?>
                            (<?= $conted_prj["open"]?>)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </span>
                        <span class="b-layout__txt_nowrap">    
                        <?php if($_GET["closed"]) { ?>
                            <b>��������</b>
                        <?php } else { //if?>
                            <a class="blue" href="?kind=<?= $kind?>&closed=1"><b>��������</b></a>
                        <?php }//else?>
                            (<?= $conted_prj["closed"]?>)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </span>
                        <span class="b-layout__txt_nowrap">
                        <?php if($_GET["all"]) { ?>
                            <b>���</b>
                        <?php } else { //if?>
                            <a class="blue" href="?kind=<?= $kind?>&all=1"><b>���</b></a>
                        <?php }//else?>
                            (<?= $conted_prj["all"]?>)
                        </span>
                        <?/* (!$_GET["all"] && !$_GET["closed"] ? "<b>��������</b> (".$conted_prj["open"].")" : '<a class="blue" href="?"><b>��������</b></a> ('.$conted_prj["open"].')' )?>
                        <?=($_GET["closed"] ? "<b>��������</b> (".$conted_prj["closed"].")" : '<a class="blue" href="?closed=1"><b>��������</b></a> ('.$conted_prj["closed"].')' )?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <?= ($_GET["all"] ? "<b>���</b> (".$conted_prj["all"].")" : '<a class="blue" href="?all=1"><b>���</b></a> ('.$conted_prj["all"].')' )*/?>
					</div>
				</td>
                
                <?php if($is_owner || $is_adm): ?>
                <td class="b-layout__td b-layout__td_padtop_10 b-layout__td_padleft_30 b-layout__td_width_full_ipad b-layout__td_pad_null_ipad">
					<div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_10">
                        <span class="b-layout__txt_nowrap">
                        <?php if($_GET["trash"]):?>
                            <b>�������</b>
                        <?php else: ?>
                            <a class="b-layout__link b-layout__link_color_c10600" href="?trash=1"><b>�������</b></a>
                        <?php endif; ?>
                            (<?= $conted_prj["trash"]?>)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </span>
					</div>
				</td>
                <?php endif; ?>

                <?php if(is_emp()||!get_uid()){ ?>
				<td class="b-layout__td b-layout__td_right b-layout__td_width_full_ipad">
                    <?php if($kind == 3) { ?> 
                        <a class="b-button b-button_flat b-button_flat_orange b-button_nowrap"  href="/public/?step=1&kind=4" title="����������� ��������">������������ ��������</a>
                    <?php } elseif($kind == 2) {?>    
                        <a class="b-button b-button_flat b-button_flat_orange b-button_nowrap"  href="/public/?step=1&kind=7" title="����������� �������">������������ �������</a>
                    <?php } else { ?>
                        <a class="b-button b-button_flat b-button_flat_orange b-button_nowrap"  href="/public/?step=1&kind=1" title="����������� ��� ������">������������ ������</a>
                    <?php } ?>
                </td>
                    <?php } ?>
			</tr>
	</table>
</div>