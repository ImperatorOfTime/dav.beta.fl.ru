<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/commune.common.php");
$xajax->printJavascript('/xajax/');

global $id, $comm, $site, $alert, $action, $request;

if (!$action) {
    if ($site == 'Create')
        $action = 'Create';
    else
        $action = 'Edit';
}

$name = '';
$descr = '';
$image = '';
$group_id = NULL;
$restrict_type = 0;
$author_id = NULL;    // ��������� ����������.
$author_login = NULL; // ���� � ���� ��������� ��������.

if (isset($request)) {        // do...
    $name = stripslashes($request['name']);
    $descr = stripslashes($request['descr']);
    $image = !empty($comm['image']) ? $comm['image'] : false;
    $group_id = $request['group_id'];
    $restrict_type = $request['restrict_type'];
    $author_id = $request['author_id'];
    $author_login = $request['author_login'];
} else if ($id) { // edit
    $name = $comm['name'];
    $descr = $comm['descr'];
    $image = $comm['image'];
    $restrict_type = bitStr2Int($comm['restrict_type']);
    $group_id = $comm['group_id'];
    $author_id = $comm['author_id'];
    $author_login = $comm['author_login'];
}

if (!($commune_groups = commune::GetGroups()))
    $commune_groups = array();

$action = str_replace('do.', '', $action);
$limit = commune::GetUserCommunesLimits(get_uid());
$count = $limit['user_communes_count'] ? "(� ��� ��� ���� {$limit['user_communes_count']})" : '';

$header = $action == 'Create' ? '�������� ������ ����������' : '<a style="color:#666" href="?id=' . $comm['id'] . '">���������� &laquo;' . $comm['name'] . '&raquo;</a>';

$aCommExts = explode( ',', commune::IMAGE_EXTENSIONS );
$sCommExts = "var aCommExts = ['". implode("','", $aCommExts) ."'];";
?>
<script type="text/javascript">
function commCreate( frm ) {
    <?=$sCommExts?>
    
    if($('ext_file') == undefined) {
        if (specificExt(frm['file'].value, aCommExts) ) {
            frm.submit();
        }
    } else {
        frm.submit();
    }
}
</script>
<? if (false) { ?>
    <table border="0" width="100%" cellpadding="0" cellspacing="0">
        <tr valign="middle">
            <td align="left">
                <h1><?= $header ?></h1>
            </td>
        </tr>
    </table>

    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr valign="top">
            <td height="500" bgcolor="#FFFFFF" class="box commune" style="padding:40px 40px 40px 35px">
                <form action=".?site=<?= $site ?>#new" method="POST" enctype="multipart/form-data" onsubmit="return specificExt(this['file'].value, aCommExts);">
                    <input type="hidden" name="id" value="<?= $id ?>"/>
                    <input type="hidden" name="author_login" value="<?= $author_login ?>"/>
                    <input type="hidden" name="author_id" value="<?= $author_id ?>"/>
                    <input type="hidden" name="action" value="do.<?= $action ?>"/>

                    <table border="0" cellspacing="0" cellpadding="0">
                        <col style="width:80px"/>
                        <col style="width:500px"/>
                        <col/>
                        <tr valign="top">
                            <td colspan="3" style="padding-bottom:25px">
                                - ���������� ����� ��������� ������ ������������ � ��������� <?= view_pro() ?><br/>
                                - ��������� ���������� ���������� �������.<br/>
                                - ����� ������� �� ����� 5-�� ���������.<br/>
                                - <a href="<?=WDCPREFIX?>/about/documents/appendix_2_regulations.pdf" class="blue">������� ��������� � �����������</a><br/>
                            </td>
                        </tr>
                        <tr valign="baseline">
                            <td>
                                <b>��������:</b>
                            </td>
                            <td>
                                <input type="text" maxlength="<?= commune::NAME_MAX_LENGTH ?>" size="50" name="name" value="<?= $name ?>" style="border: 1px solid #CCC; width: 500px; padding: 2px 1px;" />
<?= (isset($alert['name']) ? view_error($alert['name']) : '') ?>
                            </td>
                            <td style="padding:0 0 0 20px">
                                <b><?= commune::NAME_MAX_LENGTH ?> ��������</b>
                            </td>
                        </tr>
                        <tr valign="top">
                            <td style="padding-top:40px">
                                <b>��������:</b>
                            </td>
                            <td style="padding-top:40px">
                                <textarea name="descr" class="descr" style="border: 1px solid #CCC; width: 500px; height: 90px; padding: 2px 1px;"><?= $descr ?></textarea>
<?= (isset($alert['descr']) ? view_error($alert['descr']) : '') ?>
                        </td>
                        <td style="padding:40px 0 0 20px">
                            <b><?= commune::DESCR_MAX_LENGTH ?> ��������</b>
                        </td>
                    </tr>
                    <tr valign="baseline">
                        <td style="padding-top:40px">
                            <b>������:</b>
                        </td>
                        <td style="padding-top:40px">
                            <select style="height:17px;" name="group_id">
<? foreach ($commune_groups as $grp) { ?>
                                <option value="<?= $grp['id'] ?>" <?= ($grp['id'] == $group_id ? ' selected' : '') ?>>
<?= $grp['name'] ?>
                                </option>
                                <? } ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="baseline">
                        <td style="padding-top:40px">
                            <b>��������:</b>
                        </td>
                        <td style="padding-top:40px">

<? if ($image) { ?>
                            <div id="idImage_<?= $id ?>">
                                <input type="hidden" name="ext_file" id="ext_file" value="1">
                                <a href="<?= WDCPREFIX ?>/users/<?= $author_login ?>/upload/<?= $image ?>" target="_blank">�������������� ����</a>
                                &nbsp;&nbsp;(<a href='javascript:void(0)'
                                                onclick="xajax_DeleteAttach( 'idImage_<?= $id ?>',
                                                        '<?= $id ?>', '<?= $image ?>',
                                                        '<?= $author_login ?>', '__commPrntCommImgBox');">�������</a>)
                                </div>
<?
                            } else {
                                print(__commPrntCommImgBox($alert['image']));
                            }
?>
                        </td>
                    </tr>
                    <tr valign="baseline">
                        <td style="padding-top:40px">
                            <b>���:</b>
                        </td>
                        <td style="padding-top:40px">
                            <div>
                                <input type="radio" id="idJoin_0" name="restrict_join" value="0"
<?= (($restrict_type & commune::RESTRICT_JOIN_MASK) ? '' : ' checked') ?>
                                       />
                                <label for="idJoin_0"> ����� ����� ��������</label>
                            </div>
                            <div>
                                <input type="radio" id="idJoin_1" name="restrict_join" value="1"
<?= (($restrict_type & commune::RESTRICT_JOIN_MASK) ? ' checked' : '') ?>
                                       />
                                <label for="idJoin_1"> ��������� ����������, ��� ����� ��������</label>
                            </div>
                            <br/>
                            <div>
                                <input type="radio" id="idRead_0" name="restrict_read" value="0"
<?= (($restrict_type & commune::RESTRICT_READ_MASK) ? '' : ' checked') ?>
                                       />
                                <label for="idRead_0"> ����� ����� ������</label>
                            </div>
                            <div>
                                <input type="radio" id="idRead_1" name="restrict_read" value="1"
<?= (($restrict_type & commune::RESTRICT_READ_MASK) ? ' checked' : '') ?>
                                       />
                                <label for="idRead_1"> ������ ����� ������ ����� ����������</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" colspan="2" style="padding-top:50px">
                            <input style="width:100px" type="submit" value="<?= ($action == 'Edit' ? '���������' : '�������') ?>"/>
                        </td>
                    </tr>
                </table>
            </form>
<? if ($action == 'Edit' && hasPermissions('communes')) { ?>
            <div style="padding-top:50px">
                <form action="/commune/" method="POST" onsubmit="if(!warning(14)) return false;">
                    <input type="hidden" name="id" value="<?= $id ?>"/>
                                        <input type="hidden" name="site" value="<?= $site ?>"/>
                                        <input type="hidden" name="action" value="Delete"/>
                                        <input style="width:150px" type="submit" value="������� ����������"/>
                                    </form>
                                </div>
<? } ?>
                        </td>
                    </tr>
                </table>



<? } else { ?>
                <form id="form_add_comm" action=".?site=<?= $site ?>#new" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $id ?>"/>
                    <input type="hidden" name="author_login" value="<?= $author_login ?>"/>
                    <input type="hidden" name="author_id" value="<?= $author_id ?>"/>
                    <input type="hidden" name="action" value="do.<?= $action ?>"/>

                    <h2>�������� ������ ����������</h2>
                    <div class="page-commune-create c">
                        <div class="page-in">
                            <div class="form fs-o p-cc-inf">
                                <b class="b1"></b>
                                <b class="b2"></b>
                                <div class="form-in">
                                    <ul>
                                        <li>- <strong>���������� ����� ��������� ������ ������������ � ���������  <?= view_pro() ?></strong></li>
                                        <li>- <strong>����� ������� �� ����� 5-�� ���������</strong> <?= $count;?></li>
                                        <li>- <strong>��������� ���������� ���������� �������</strong></li>
                                        <li>- <strong><a href="<?=WDCPREFIX?>/about/documents/appendix_2_regulations.pdf">������� ��������� � �����������</a></strong></li>
                                    </ul>
                                </div>
                                <b class="b2"></b>
                                <b class="b1"></b>
                            </div>
                            <div class="form form-commune-create">
                                <b class="b1"></b>
                                <b class="b2"></b>
                                <div class="form-in">
                                    <div class="form-block first">
                                        <div class="form-el">
                                            <label class="form-label">��������</label>
                                            <div class="form-cntrl">
                                                <input type="text" maxlength="<?= commune::NAME_MAX_LENGTH ?>" size="50" name="name" value="<?= $name ?>" class="i-txt" />
<?= (isset($alert['name']) ? view_error($alert['name']) : '') ?>
                                            </div>
                                            <div class="form-hint">
                												�� 50 ��������
                                            </div>
                                        </div>
                                        <div class="form-el">
                                            <label class="form-label">��������</label>
                                            <div class="form-cntrl">
                                                <textarea name="descr" rows="7" cols="20"><?= $descr ?></textarea>
<?= (isset($alert['descr']) ? view_error($alert['descr']) : '') ?>
                                            </div>
                                            <div class="form-hint">
                												�� 1000 ��������
                                            </div>
                                        </div>
                                        <div class="form-el">
                                            <label class="form-label">��������</label>
                                            <div class="form-cntrl">
 <? if ($image) { ?>
                            <div id="idImage_<?= $id ?>">
                                <input type="hidden" name="ext_file" id="ext_file" value="1">
                                <a href="<?= WDCPREFIX ?>/users/<?= $author_login ?>/upload/<?= $image ?>" target="_blank">�������������� ����</a>
                                &nbsp;&nbsp;(<a href='javascript:void(0)'
                                                onclick="xajax_DeleteAttach( 'idImage_<?= $id ?>',
                                                        '<?= $id ?>', '<?= $image ?>',
                                                        '<?= $author_login ?>', '__commPrntCommImgBox');">�������</a>)
                                </div>
<?
                            } else {
                                print(__commPrntCommImgBox($alert['image']));
                            }
?>
                                            </div>
                                            <div class="form-hint">
         												<?=implode(', ', $aCommExts)?>. 200x400 ��������
                                            </div>
                                        </div>
                                        <div class="form-el">
                                            <label class="form-label">������</label>
                                            <div class="form-cntrl">
                                                <ul class="form-list">

                                                    <? foreach ($commune_groups as $grp) { ?>
                               <li><label><span class="i-radio"><input name="group_id" type="radio" value="<?= $grp['id'] ?>" <?= ($grp['id'] == $group_id ? ' checked' : '') ?>></span> <?= $grp['name'] ?></label></li>
                                                    <? } ?>
                                                </ul>
                                                <?= (isset($alert['group_id']) ? view_error($alert['group_id']) : '') ?>
                                            </div>
                                        </div>
                                        <div class="form-el">
                                            <label class="form-label">����������</label>
                                            <div class="form-cntrl">
                                                <ul class="form-list">
                                                    <li><label><span class="i-radio"><input type="radio" id="idJoin_0" name="restrict_join" value="0"
<?= (($restrict_type & commune::RESTRICT_JOIN_MASK) ? '' : ' checked') ?>
                                       /></span> ����� ����� ��������</label></li>
                                                    <li><label><span class="i-radio"><input type="radio" id="idJoin_1" name="restrict_join" value="1"
<?= (($restrict_type & commune::RESTRICT_JOIN_MASK) ? ' checked' : '') ?>
                                       /></span> ��������� ����������, ��� ����� ��������</label></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="form-el">
                                            <label class="form-label">��������</label>
                                            <div class="form-cntrl">
                                                <ul class="form-list">
                                                    <li><label><span class="i-radio"><input type="radio" id="idRead_0" name="restrict_read" value="0"
<?= (($restrict_type & commune::RESTRICT_READ_MASK) ? '' : ' checked') ?>
                                       /></span> ����� ����� ������</label></li>
                                                    <li><label><span class="i-radio"><input type="radio" id="idRead_1" name="restrict_read" value="1"
<?= (($restrict_type & commune::RESTRICT_READ_MASK) ? ' checked' : '') ?>
                                       /></span> ������ ����� ������ ����� ����������</label></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-block last">
                                        <div class="form-el form-btns">
                                            <a href="#" onclick="commCreate($('form_add_comm')); return false;" class="b-button b-button_flat b-button_flat_green"><?= $action == 'Edit' ? '��������' : '�������';?> ����������</a>
                                        </div>
                                    </div>
                                </div>
                                <b class="b2"></b>
                                <b class="b1"></b>
                            </div>
                        </div>
                    </div>
                </form>                
<? } ?>
