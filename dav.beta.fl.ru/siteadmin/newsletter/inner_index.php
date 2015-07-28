<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
<h2>
    �������� ������� ��� ���������� �������� � ����� ��������
</h2>
<br/>
<h3 id="frl">
    ��� �����������
</h3>
<br/>
<form method="post" action="" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save"/>
    <input type="hidden" name="type" value="0"/>
    <div class="form form-cnc">
        <b class="b1"></b>
        <b class="b2"></b>
        <div class="form-in">
            <div class="form-block first">
                <div class="form-el">
                    <label class="form-l">
                        <b>����</b>
                    </label>
                    <div class="form-value fvs">
                        <input type="file" name="file"/>
                    </div>
                    <div class="form-hint">
                        GIF, JPG, PNG
                    </div>
                </div>
                <div class="form-el">
                    <label class="form-l">
                        <b>������</b>
                    </label>
                    <div class="form-value">
                        <textarea rows="" cols="100%" name="link"></textarea>
                    </div>
                </div>
            </div>
            <div class="form-block last">
                <div class="form-el form-btns">
                    <button type="submit">���������</button>
                </div>
            </div>
        </div>
        <b class="b2"></b>
        <b class="b1"></b>
    </div>
</form>
<?php if($newsletter_banner_file){ ?>
<form method="post" action="">
    <input type="hidden" name="action" value="delete"/>
    <input type="hidden" name="type" value="0"/>
    <div class="form form-cnc">
        <b class="b1"></b>
        <b class="b2"></b>
        <div class="form-in">
            <div class="form-block first">
                <div class="form-el">
                    <?php if($newsletter_banner_link){ ?>
                    <a href="<?=$newsletter_banner_link?>" target="_blank">
                        <img src="<?=$newsletter_banner_file?>" />
                    </a>
                    <?php }else{ ?>
                        <img src="<?=$newsletter_banner_file?>" />
                    <?php } ?>
                </div>
            </div>
            <div class="form-block last">
                <div class="form-el form-btns">
                    <button type="submit">�������</button>
                </div>
            </div>
        </div>
        <b class="b2"></b>
        <b class="b1"></b>
    </div>
</form>
<?php } ?>

<br/><br/>
<hr/>
<br/><br/>

<h3 id="emp">
    ��� �������������
</h3>
<br/>
<form method="post" action="" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save"/>
    <input type="hidden" name="type" value="1"/>
    <div class="form form-cnc">
        <b class="b1"></b>
        <b class="b2"></b>
        <div class="form-in">
            <div class="form-block first">
                <div class="form-el">
                    <label class="form-l">
                        <b>����</b>
                    </label>
                    <div class="form-value fvs">
                        <input type="file" name="file"/>
                    </div>
                    <div class="form-hint">
                        GIF, JPG, PNG
                    </div>
                </div>
                <div class="form-el">
                    <label class="form-l">
                        <b>������</b>
                    </label>
                    <div class="form-value">
                        <textarea rows="" cols="100%" name="link"></textarea>
                    </div>
                </div>
            </div>
            <div class="form-block last">
                <div class="form-el form-btns">
                    <button type="submit">���������</button>
                </div>
            </div>
        </div>
        <b class="b2"></b>
        <b class="b1"></b>
    </div>
</form>
<?php if($newsletter_emp_banner_file){ ?>
<form method="post" action="">
    <input type="hidden" name="action" value="delete"/>
    <input type="hidden" name="type" value="1"/>
    <div class="form form-cnc">
        <b class="b1"></b>
        <b class="b2"></b>
        <div class="form-in">
            <div class="form-block first">
                <div class="form-el">
                    <?php if($newsletter_emp_banner_link){ ?>
                    <a href="<?=$newsletter_emp_banner_link?>" target="_blank">
                        <img src="<?=$newsletter_emp_banner_file?>" />
                    </a>
                    <?php }else{ ?>
                        <img src="<?=$newsletter_emp_banner_file?>" />
                    <?php } ?>
                </div>
            </div>
            <div class="form-block last">
                <div class="form-el form-btns">
                    <button type="submit">�������</button>
                </div>
            </div>
        </div>
        <b class="b2"></b>
        <b class="b1"></b>
    </div>
</form>
<?php } ?>