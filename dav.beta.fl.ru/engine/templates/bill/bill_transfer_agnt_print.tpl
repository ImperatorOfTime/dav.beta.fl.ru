<? require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/num_to_word.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><HEAD><TITLE>����</TITLE>
<META http-equiv=Content-Type content="text/html; charset=windows-1251">
<META content="MSHTML 6.00.2900.2963" name=GENERATOR></HEAD>
<BODY><BR><BR>
<TABLE width="90%" border=0 xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math">
  <TBODY>
  <TR>
    <TD><?= PrintSiteLogo(); ?></TD>
    <TD vAlign=top align=right>
      <DIV style="FONT-SIZE: 10pt"><B>129223, ������, �/� 33</B>
	  </DIV></TD></TR></TBODY></TABLE>
<DIV style="FONT-SIZE: 11pt" align=center xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math"><B>������� ���������� ���������� 
���������</B></DIV><BR xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math">
<TABLE class=invoice cellSpacing=0 cellPadding=3 width="90%" border=0 
xmlns:str="http://exslt.org/strings" xmlns:math="http://exslt.org/math">
<TBODY>
              <TR>
                <TD>����������<BR>��� 7805399430 / ��� 771401001 ��� &laquo;����&raquo;</TD>
                <TD align=middle><BR>��. �</TD>
                <TD><BR>40702810787880000803</TD></TR>
              <TR>
                <TD rowSpan=2>���� ����������<BR>� ���������� ������ ��� ��� �������ʻ �. ������
</TD>
                <TD align=middle>���</TD>
                <TD rowSpan=2>044583272<BR>30101810000000000272</TD></TR>
              <TR>
    <TD align=middle>��. �</TD></TR></TBODY></TABLE><BR 
xmlns:str="http://exslt.org/strings" xmlns:math="http://exslt.org/math"><BR 
xmlns:str="http://exslt.org/strings" xmlns:math="http://exslt.org/math">
<DIV style="FONT-SIZE: 12pt" align=center xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math"><B>���� � <?=$billCode?> �� <?=(date("d ",strtotime($reqv['invoiced_time'])).strtolower(monthtostr(date("m",strtotime($reqv['invoiced_time'])))).date(" Y �.",strtotime($reqv['invoiced_time'])))?></B></DIV><BR xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math">
<TABLE width="90%" border=0 xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math">
  <TBODY>
  <TR>
    <TD width="50%">
      <DIV style="FONT-SIZE: 10pt">��������: <?= reformat($reqv['full_name'], 28);?></DIV></TD>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">��������: <?= $reqv['phone']?></DIV></TD></TR>
              <TR>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">������������� ���������: <?=$reqv['fio']?>
</DIV></TD>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">����: </DIV></TD></TR></TBODY></TABLE>
                  
                  
                  <BR xmlns:str="http://exslt.org/strings">
                                        <TABLE class=invoice cellSpacing=0 cellPadding=3 width="90%" border=0>
                                            <TBODY>
                                                <TR>
                                                    <TD align=middle>
                                                        <DIV>�</DIV>
                                                    </TD>
                                                    <TD align=middle>
                                                        <DIV>� ������</DIV>
                                                    </TD>
                                                    <TD align=middle>
                                                        <DIV>������������</DIV>
                                                    </TD>
                                                    <TD align=middle>
                                                        <DIV>�����, ���.</DIV>
                                                    </TD>
                                                </TR>
                                                <TR>
                                                    <TD align=middle>
                                                        <DIV>1</DIV>
                                                    </TD>
                                                    <TD align=middle>
                                                        <DIV style="FONT-SIZE: 10pt"><?=$ord_num?></DIV>
                                                    </TD>
                                                    <TD align=middle>
                                                        <DIV style="FONT-SIZE: 10pt">
                                                            ������ ����� www.Free-lance.ru
                                                        </DIV>
                                                    </TD>  
                                                    <TD align=right>
                                                        <DIV style="FONT-SIZE: 10pt"><?=number_format($sum, 2, ',', ' ')?></DIV>
                                                    </TD>
                                                </TR>
                                                <TR>
                                                    <TD style="BORDER-RIGHT: medium none; BORDER-TOP: medium none; BORDER-LEFT: medium none; BORDER-BOTTOM: medium none" align=right colSpan=3>
                                                        <DIV>�����:</DIV>
                                                    </TD>
                                                    <TD align=right>
                                                        <DIV><?=number_format($sum, 2, ',', ' ')?></DIV>
                                                    </TD>
                                                </TR>
                                                <TR>
                                                    <TD 
                                                        style="BORDER-RIGHT: medium none; BORDER-TOP: medium none; BORDER-LEFT: medium none; BORDER-BOTTOM: medium none" 
                                                        align=right colSpan=3>
                                                        <DIV>��� 18%:</DIV>
                                                    </TD>
                                                    <TD align=right>
                                                        <DIV>0 ,00</DIV>
                                                    </TD>
                                                </TR>
                                                <TR>
                                                    <TD style="BORDER-RIGHT: medium none; BORDER-TOP: medium none; BORDER-LEFT: medium none; BORDER-BOTTOM: medium none" align=right colSpan=3>
                                                        <DIV><B>����� � ������:</B></DIV>
                                                    </TD>
                                                    <TD align=right>
                                                        <DIV style="FONT-WEIGHT: bold"><?=number_format($sum, 2, ',', ' ')?></DIV>
                                                    </TD>
                                                </TR>
                                            </TBODY>
                                        </TABLE>
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <DIV style="FONT-SIZE: 10pt" xmlns:str="http://exslt.org/strings">
                                            <I><B>� ������: <?= num2str($sum);?> </B></I>
                                        </DIV>
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <DIV style="FONT-SIZE: 10pt" xmlns:str="http://exslt.org/strings">
                                            ������������ �����������&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(�.�.��������)
                                        </DIV>
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <DIV style="FONT-SIZE: 10pt" class="org">
                                            <I><B><U>������� ��� ��������:</U></B></I>
                                            <BR>1. C��� ������������ � ������� ���� ����.
                                            <BR>2. � ���������� �������, ����������, ���������� "<?=$billCode?>".
                                        </DIV>
                                        <DIV></DIV>
                                        <BR>
                                    </TD>
                                </TR>
                            </TBODY>
                        </table>
                        <BR>
                    </td>
                </TR>         
             </TBODY>
         </TABLE>
    </BODY>
</HTML>