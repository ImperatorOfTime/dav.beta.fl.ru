<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if(!(hasPermissions('adm') && hasPermissions('permissions'))) {
  header ("Location: /404.php");
  exit;
}
?>

<strong>����� �������. ������ ������</strong>

<br><br>

<a href="?action=group_add">�������� ����� ������</a>

<br><br>

<? if($groups) { ?>
    <table width="100%" cellpadding="5" cellspacing="5"  class="tbl-pad5">
        <tr style="background-color: #eeeeee;">
            <td>��������</td>
            <td>��������</td>
        </tr>
        <? foreach($groups as $group) { ?>
        <tr>
            <td><?=$group['name']?></td>
            <td>
                <? if($group['id']!=0) { ?>
                    [<a href="?action=group_edit&id=<?=$group['id']?>">�������������</a>]<? if($group['id']!=1) { ?>&nbsp;&nbsp;&nbsp;[<a id="del_group_<?=$group['id']?>" href="?action=group_delete&id=<?=$group['id']?>" onClick="return addTokenToLink('del_group_<?=$group['id']?>', '�� ������������� ������ ������� ������?')">�������</a>]<? } ?>
                <? } ?>
            </td>
        </tr>
        <? } ?>
    </table>
<? } ?>
