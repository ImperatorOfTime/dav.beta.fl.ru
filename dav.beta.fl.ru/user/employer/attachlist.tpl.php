<?php
if($attach=$projects->GetAllAttach($prj['id'])) {          
              foreach ($attach as $a) 
              {?>
               <? if ($a["virus"][3] != 1)  {?>
              		<div class="flw_offer_attach">
              			<div style="width:250px; float:left">
	              			<a href="<?=WDCPREFIX.'/'.$a['path'].$a['name']?>" target="_blank">���������</a> 
	              			(<?=$a['ftype']?>; <?=ConvertBtoMB($a['size'])?> )
	              		</div>
	              			
              			<? 
              			switch ($a["virus"]) { 
							 case "":?> <span title="����������� ����������� �����, ����������� ����� 1 ���� 2011 ����" class="avs-nocheck">���� �� �������� �����������</span> <?
              				 break;
              				
              				 case "0000":?> <span title="����������� ����������� �����, ����������� ����� 1 ���� 2011 ����" class="avs-ok">��������� �����������</span> <?
              				 break;
              				 
              				 case "0010":?> <span title="����������� ����������� �����, ����������� ����� 1 ���� 2011 ����" class="avs-errcheck">���������� ���������</span> <?
              				 break;              				 
              			}
              			?>
              		</div>
              	<? }?>
            <?}
 }