<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
<h2>�����</h2>
<div class="admin">
<div class="lm-col">
    <div class="admin-menu">
        <h3>�����</h3>

        <? include ($rpath . "/siteadmin/leftmenu.php") ?>

    </div>
</div>
</div>
<div class="r-col">
    <div class="ban-razban">
        <h3>������� �������������</h3>
        <? include_once ('tpl.navigation.php'); ?>

        <br/>
        <div class="form form-nr-docs-sort ">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <div class="form-b">
                    <div class="form-block first">
                        <div class="form-el" style="text-transform: uppercase; font-size: 14px;">
                            <? foreach ($rus as $k) { ?>
                                <? if ($k != $start) { ?>
                                    <a href="?s=<?= $k ?>"><?= $k ?></a>
                                <? } elseif ($k == $start) { ?>
                                    <strong><?= $k ?></strong>
                                <? } else { ?>
                                    <?= $k ?>
                                <? } ?>
                            <? } ?>
                        </div>
                    </div>
                    <div class="form-block">
                        <div class="form-el" style="text-transform: uppercase; font-size: 14px;">
                            <? foreach ($eng as $k) { ?>
                                <? if ($k != $start) { ?>
                                    <a href="?s=<?= $k ?>"><?= $k ?></a>
                                <? } elseif ($k == $start) { ?>
                                    <strong><?= $k ?></strong>
                                <? } else { ?>
                                    <?= $k ?>
                                <? } ?>
                            <? } ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
                            <? if ($start != 'num') { ?>
                            <a href="?s=num">0 - 9</a>
                            <? } else { ?>
                            <strong>0 - 9</strong>
                            <? } ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
                            <? if ($start != 'others') { ?>
                            <a href="?s=others">������</a>
                            <? } else { ?>
                            <strong>������</strong>
                            <? } ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
                            <? if ($start != 'all') { ?>
                            <a href="?s=all">���</a>
                            <? } else { ?>
                            <strong>���</strong>
                            <? } ?>
                        </div>
                    </div>
                    <div class="form-block last">
                        <div class="form-el" style="text-transform: uppercase; font-size: 14px;">
                            <? if ($start != 'users') { ?>
                            <a href="?s=users">�� ������������</a>
                            <? } else { ?>
                            <strong>�� ������������</strong>
                            <? } ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
                            <? if ($start != 'projects') { ?>
                            <a href="?s=projects">�� ��������</a>
                            <? } else { ?>
                            <strong>�� ��������</strong>
                            <? } ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
                            <? if ($start != 'more') { ?>
                            <a href="?s=more">�� �������� �����</a>
                            <? } else { ?>
                            <strong>�� �������� �����</strong>
                            <? } ?>
                        </div>
                    </div>
                </div>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>

        <!-- ������� �������� � �������� -->
        <table class="tbl-cnc">
            <thead>
                <tr>
                    <th>
                        #
                    </th>
                    <th width="230">
                        ������ �������
                    </th>
                    <th>
                        ������
                    </th>
                    <th width="60">
                        ���-�� �������� (N)
                    </th>
                    <th width="60">
                        ���-�� ���������� (M)
                    </th>
                    <th width="80">
                        N*M
                    </th>
                    <th width="50">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($data as $row) { ?>
                    <tr id="query<?= $row['id'] ?>">
                        <td class="c-st">
                            &bull;
                        </td>
                        <td>
                            <?= change_q_x($row['query'], TRUE, FALSE) ?>
                        </td>
                        <td><?= $row['idname'] ?></td>
                        <td>
                            <?= $row['cnt'] ?>
                        </td>
                        <td>
                            <?= $row['match_cnt'] ?>
                        </td>
                        <td>
                            <strong><?= $row['cnt'] * $row['match_cnt'] ?></strong>
                        </td>

                        <td class="c-prd <?= $order == 'act' ? 'c-id' : '' ?>">
                            <a href="javascript:void(0)" onclick="deleteQuery(this)">�������</a>
                        </td>
                    </tr>
                <? } ?>
                    <tr id="deleteFrm" style="display:none;">
                        <td colspan="7">
                            <form name="frm" method="post" action="">
                                <input type="hidden" name="action" value="add_filter"/>
                                <input type="hidden" name="query" value=""/>
                                <div class="form form-cnc">
                                    <b class="b1"></b>
                                    <b class="b2"></b>
                                    <div class="form-in">
                                        <div class="form-block first">
                                            <div class="form-el">
                                                ����� ��������� ������ ��� ��� ����� ������ �� �������� � ��� �������, ����� ��������� ��� �����.
                                            </div>
                                            <div class="form-el">
                                                <label class="form-l">������� �����, �������:</label>
                                                <div class="form-value">
                                                    <select name="filter_rule" class="sw205">
                                                        <? foreach ($rules as $rule) { ?>
                                                        <option value="<?= $rule['id'] ?>"><?= $rule['rule_name'] ?></option>
                                                        <? } ?>
                                                    </select>
                                                    <input type="text" name="word" class="sw205"/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-block last">
                                            <div class="form-el form-btns flm">
                                                <button type="submit">������� ������ � ������� ������</button> ��� <button onclick="return deleteQueryOnly(this)">������ ������� ������</button> 
                                            </div>
                                        </div>
                                    </div>
                                    <b class="b2"></b>
                                    <b class="b1"></b>
                                </div>
                            </form>
                        </td>
                    </tr>
            </tbody>
        </table>

        <?= new_paginator2($page, $pages, 3, "%s?" . urldecode(url($_GET, array('p' => '%d'))) . "%s") ?>

    </div>
</div>
