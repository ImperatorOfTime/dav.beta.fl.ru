              <TBODY>
              <TR>
                <TD align=middle>
                  <DIV>�</DIV></TD>
                <TD align=middle>
                  <DIV>� ������</DIV></TD>
                <TD align=middle>
                  <DIV>������������</DIV></TD>
                <TD align=middle>
                  <DIV>�����, ���.</DIV></TD></TR>
              <TR>
                <TD align=middle>
                  <DIV>1</DIV></TD>
                <TD align=middle>
                  <DIV style="FONT-SIZE: 10pt"><?=$ord_num?></DIV></TD>
                <TD align=middle>
                  <DIV style="FONT-SIZE: 10pt">
                    <? if($contract_num) { ?>
                      ������ �� ��������-������ � <?=$contract_num?>
                    <? } else { ?>
                      ������ ����� www.Free-lance.ru
                    <? } ?>
                  </DIV>
                </TD>  
                <TD align=right>
                  <DIV style="FONT-SIZE: 10pt"><?=number_format($contract_num ? $sum-$sbr_nds : $sum-round($sum*18/118, 2), 2, ',', ' ')?></DIV>
                </TD>
              </TR>
              <TR>
                <TD 
                style="BORDER-RIGHT: medium none; BORDER-TOP: medium none; BORDER-LEFT: medium none; BORDER-BOTTOM: medium none" 
                align=right colSpan=3>
                  <DIV>�����:</DIV></TD>
                <TD align=right><DIV>
                  <?=number_format($contract_num ? $sum-$sbr_nds : $sum-round($sum*18/118, 2), 2, ',', ' ')?></DIV>
                </TD>
              </TR>
              <TR>
                <TD 
                style="BORDER-RIGHT: medium none; BORDER-TOP: medium none; BORDER-LEFT: medium none; BORDER-BOTTOM: medium none" 
                align=right colSpan=3>
                <? if($contract_num) { ?>
                  <DIV>���<?=$sbr_comm ? ' (� ���������� ��������������)' : ''?>:</DIV></TD>
                <TD align=right>
                  <DIV><?=number_format($sbr_nds, 2, ',', ' ')?></DIV></TD></TR>
                <? } else { ?>
                  <DIV>��� 18%:</DIV></TD>
                <TD align=right>
                  <DIV><?=number_format(round($sum*18/118, 2), 2, ',', ' ')?></DIV></TD></TR>
                <? } ?>
              <TR>
                <TD 
                style="BORDER-RIGHT: medium none; BORDER-TOP: medium none; BORDER-LEFT: medium none; BORDER-BOTTOM: medium none" 
                align=right colSpan=3>
                  <DIV><B>����� � ������:</B></DIV></TD>
                <TD align=right>
                  <DIV style="FONT-WEIGHT: bold"><?=number_format($sum, 2, ',', ' ')?></DIV></TD></TR></TBODY></TABLE><BR 
            xmlns:str="http://exslt.org/strings">
            <DIV style="FONT-SIZE: 10pt" 
            xmlns:str="http://exslt.org/strings"><I><B>� ������:
            <?=num2str($sum)?><?
               if($contract_num) { ?>. 
              <? if($sbr_nds) { ?>
                � ��� ����� ��� 18% &mdash; <?=num2str($sbr_nds)?>
                <? if($sbr_comm) { ?>
                  � ����� ���������� �������������� ��� "����" &mdash; <?=num2str($sbr_comm)?>.
                <? } ?>
              <? } else { ?>
              <? } ?>
            <? } ?>
            
            </B></I></DIV><BR 
            xmlns:str="http://exslt.org/strings">
            <DIV style="FONT-SIZE: 10pt" 
            xmlns:str="http://exslt.org/strings">������������ 
            �����������&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(�.�. ��������)</DIV><BR 
            xmlns:str="http://exslt.org/strings"><BR 
            xmlns:str="http://exslt.org/strings">
            <DIV style="FONT-SIZE: 10pt" class="org"><I><B><U>������� ��� 
            ��������:</U></B></I><BR>1. C��� ������������ � ������� <?=$contract_num ? '����' : '����'?>
            ����.<BR>2. � ���������� �������, ����������, ���������� 
            <? if($contract_num) { ?>
              <? if($sbr_nds) { ?>
                "<?=$billCode?>. � ��� ����� ��� 18% &mdash; <?=num2strL($sbr_nds)?><? if($sbr_comm) { ?> � ����� ���������� �������������� ��� "����" &mdash; <?=num2strL($sbr_comm)?><? } ?>".
              <? } else { ?>
                "<?=$billCode?>. ��� �� ����������".
              <? } ?>
            <? } else { ?>
               "<?=$billCode?>".
            <? } ?>
            <? if($$show_ex_code || $show_ex_code){ ?>
            <BR/>3. ������� ��� ��������: ��� �������� �������� ��� ���������� �� ������ - 35020
            <? } ?>
            </DIV>
            <DIV>
            </DIV><BR></TD></TR></TBODY>
</TABLE>
      <DIV><BR>
	</TD></TR>
</TBODY></TABLE></BODY></HTML>