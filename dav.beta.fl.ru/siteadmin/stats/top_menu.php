<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
<?php if($mIndex) {?> �� ���� <?php } else { //if?><a href="/siteadmin/stats/">�� ����</a><?php }//else?> | 
<?php if($mFull) {?> ����� <?php } else { //if?><a href="?t=g">�����</a><?php }//else?> |
<?php if($mPro) {?> PRO <?php } else { //if?><a href="?t=p">PRO</a><?php }//else?> |
<?php if($mCountry) {?> ������, ������ � ������� <?php } else { //if?><a href="?t=c">������, ������ � �������</a><?php }//else?> |
<?php if($mUser) {?>�������� ������������<?php } else { //if?><a href="?t=u">�������� ������������</a><?php }//else?> |
<?php if($mVerify) {?>�����������<?php } else { //if?><a href="?t=v">�����������</a><?php }//else?>