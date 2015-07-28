<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
<div class="m-cl-bar-sort3">
    <a name="#tabs"></a>
    <a href="./" class="lnk-dot-666">��������� �������</a></strong>
    &nbsp;&nbsp;&nbsp;
    <a href="./?tab=filters" class="lnk-dot-666">�������</a>
    &nbsp;&nbsp;&nbsp;
    <a href="./?tab=rules" class="lnk-dot-666">������� ����������</a>
    &nbsp;&nbsp;&nbsp;
    <a href="./?tab=top" class="lnk-dot-666">��� ��������</a>
    &nbsp;&nbsp;&nbsp;
    <a href="javascript:void(0)" onclick="$('settingsBlock').toggle()" class="lnk-dot-666">���������</a>
</div>

<div id="settingsBlock" style="display: <?= $action == 'save_settings' ? 'block' : 'none'?>;">
    <br/>
    <form name="frm" method="post" action="">
        <input type="hidden" name="action" value="save_settings"/>
        <div class="form form-cnc">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <div class="form-block first">
                    <h3>���������</h3>
                    <div class="form-el">
                        <label class="form-l">�����:</label>
                        <div class="form-value">
                            <input type="text" name="min_cnt" class="sw205" value="<?= intval($settings['min_cnt']) ?>"/>
                        </div>
                    </div>
                    <div class="form-el">
                        � �������� ������� ����� �������� ������ �� �������, �������� ������������ (���-�� �������� * ���-�� ����������) ������� ������ ���������� ������.
                    </div>
                </div>
                <div class="form-block last">
                    <div class="form-el form-btns flm">
                        <button type="submit">���������</button>
                        <button onclick="$('settingsBlock').toggle();return false;">�������</button>
                    </div>
                </div>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
    </form>
</div>