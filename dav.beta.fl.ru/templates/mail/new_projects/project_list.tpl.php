<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
    <tbody>
    <tr>
        <td  bgcolor="#ffffff" width="20" height="30" colspan="3"></td>
    </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  >
            <font color="#000000" size="2" face="arial">
            <?php if($spec_list){ ?>
                <b>������� �� ��������������:</b> <?=$spec_list?>
            <? }else{ ?>
                <b>������� �� ���� ��������������</b>
            <?php } ?>
            </font>
        </td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  >
           <font color="#000000" size="1" face="arial">
                <a href="<?=$setup_url?>" target="_blank">
                    <?php if($spec_list){ ?>
                        �������� ������ ������������� ��� ��������
                    <?php }else{ ?>
                        ��������� ������ ������������� ��� ��������
                    <?php } ?>
                </a>
           </font>
        </td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>

<?php if(isset($banner_file)){ ?>
<br/>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
    <tbody>
    <tr>
        <td>
    <?php if($banner_link) { ?>
            <a href="<?= $banner_link ?>" target="_blank"><img src="<?= $banner_file ?>" /></a>
    <?php } else { ?>
            <img border="0" src="<?= $banner_file ?>" />
    <?php } ?>
        </td>
    </tr>
</tbody>
</table>
<br/>
<?php } ?>                        

<?=$projects?>

<?php if ($other_count > 0): ?>
    <table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
        <tbody>
        <tr>
            <td  bgcolor="#ffffff" width="20" height="20" colspan="3"></td>
        </tr>
        <tr>
            <td  bgcolor="#ffffff" width="20"></td>
            <td  >
               <b>
                   <font color="#000000" size="4" face="arial">
                        <a href="<?=$more_url?>" target="_blank" style="color:#000; font-family:Arial, Helvetica, sans-serif; font-size:18px;">
                            � ��� <?=$other_count?> <?=plural_form($other_count, array('������', '�������', '��������'))?> �� ����� FL.ru
                        </a>
                   </font>
               </b>
            </td>
            <td  bgcolor="#ffffff" width="20"></td>
        </tr>
        <tr>
            <td  bgcolor="#ffffff" width="20" height="40" colspan="3"></td>
        </tr>
    </tbody>
    </table>
<?php endif; ?>