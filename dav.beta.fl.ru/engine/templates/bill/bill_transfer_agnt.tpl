<? require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/num_to_word.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML xmlns:math = "http://exslt.org/math" xmlns:date = "http://exslt.org/dates-and-times">
    <HEAD>
        <TITLE>Free-lance.ru: ����</TITLE>
        <META http-equiv=Content-Type content="text/html; charset=windows-1251">
        <link type="text/css" href="/css/block/style.css" rel="stylesheet" />
        <style type="text/css">
            @media print{
                .b-fon{ display:none;}
            }
        </style>
    </HEAD>
    <BODY text=#000000 bottomMargin=10 vLink=#0033cc aLink=#cc0033 link=#0033cc bgColor=#ffffff topMargin=10 marginheight="20" marginwidth="20">
        <TABLE class=operations cellSpacing=0 cellPadding=4 width="80%" border=0>
            <TBODY>
                <tr>
                    <TD vAlign=bottom><a href="/"><img src="/images/logo.png" width="197" height="28" alt="��������� ������, ���-����" class="logo" /></a></TD>
                    <TD vAlign=bottom align=left>&nbsp;</TD>
                    <TD vAlign=bottom align=right> </TD>
                </tr>
                <tr>
                    <td colspan="3">
                        <div class="b-fon b-fon_width_full b-fon_padtop_10 b-fon_padbot_10">
                            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf">
                                <a href="/sbr/?site=invoiced&id=<?=intval($_GET['id'])?>&print=1" target="_blank" class="b-button b-button_rectangle_color_green b-button_float_right">
                                    <span class="b-button__b1">
                                        <span class="b-button__b2">
                                            <span class="b-button__txt">�����������</span>
                                        </span>
                                    </span>
                                </a>
                                <div class="b-fon__txt b-fon__txt_padbot_5"><span class="b-fon__attent_pink"></span>������ ���� ���������� ����������� � ��������.</div> 
                                <div class="b-fon__txt">�������� ��� ������ ����� ��������������� ��������������� �� ����� ����������� ����� �� ��������� ��������� ����.</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr align=middle>
                    <td class=th colSpan=3>
                        <h2 class=title>����</h2>
                    </td>
                </tr>
                <tr>
                    <td colSpan=3>
                        <table cellSpacing=0 cellPadding=10 width="100%" border=0>
                            <TBODY>
                                <TR>
                                    <TD style="BORDER-RIGHT: #cccccc 1px solid; BORDER-TOP: #cccccc 1px solid; BORDER-LEFT: #cccccc 1px solid; BORDER-BOTTOM: #cccccc 1px solid">
                                        <br/>
                                        <TABLE width="100%" border=0 xmlns:str="http://exslt.org/strings">
                                            <TBODY>
                                                <TR>
                                                    <TD>&nbsp;</TD>
                                                    <TD vAlign=top align=right>
                                                        <DIV style="FONT-SIZE: 10pt"><B>129223, ������, �/� 33</B></DIV>
                                                    </TD>
                                                </TR>
                                            </TBODY>
                                        </TABLE>
                                        <DIV style="FONT-SIZE: 11pt" align=center xmlns:str="http://exslt.org/strings"><B>������� ���������� ���������� ���������</B></DIV>
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <TABLE class=invoice cellSpacing=0 cellPadding=3 width="100%" border=0 xmlns:str="http://exslt.org/strings">
                                            <TBODY>
                                                <TR>
                                                    <TD>����������<BR>��� 7805399430 / ��� 771401001 ��� &laquo;����&raquo;</TD>
                                                    <TD align=middle><BR>��. �</TD>
                                                    <TD><BR>40702810787880000803</TD></TR>
                                                <TR>
                                                    <TD rowSpan=2>���� ����������<BR>� ���������� ������ ��� ��� �������ʻ �. ������</TD>
                                                    <TD align=middle>���</TD>
                                                    <TD rowSpan=2>044583272<BR>30101810000000000272</TD>
                                                </TR>
                                                <TR>
                                                    <TD align=middle>��. �</TD>
                                                </TR>
                                            </TBODY>
                                        </TABLE>
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <DIV style="FONT-SIZE: 12pt" align=center xmlns:str="http://exslt.org/strings">
                                            <B>���� � <?=$billCode?> �� <?=(date("d ").strtolower(monthtostr(date("m"))).date(" Y �."))?></B>
                                        </DIV>
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <TABLE width="100%" border=0 xmlns:str="http://exslt.org/strings">
                                            <TBODY>
                                                <TR>
                                                    <TD width="50%">
                                                        <DIV style="FONT-SIZE: 10pt">��������: <?= reformat($reqv['full_name'], 28)?></DIV>
                                                    </TD>
                                                    <TD width="50%">
                                                        <DIV style="FONT-SIZE: 10pt">��������: <?= $reqv['phone']?></DIV>
                                                    </TD>
                                                </TR>
                                                <TR>
                                                    <TD width="50%">
                                                        <DIV style="FONT-SIZE: 10pt">������������� ���������: <?= reformat($reqv['fio']);?></DIV>
                                                    </TD>
                                                    <TD width="50%">
                                                        <DIV style="FONT-SIZE: 10pt">����: <?= reformat($reqv['fax'])?></DIV>
                                                    </TD>
                                                </TR>
                                            </TBODY>
                                        </TABLE>
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <TABLE class=invoice cellSpacing=0 cellPadding=3 width="100%" border=0>
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