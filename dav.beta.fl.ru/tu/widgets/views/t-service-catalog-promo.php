<?php
if(!defined('IN_STDF')) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 * ������������� ��� ������� TServiceCatalogPromo
 *
 *
 * @var TServiceCatalogPromo $this
 * @var users $user
 */

//������� ������������ ���������?
$is_frl = (get_uid(false) && !is_emp());

?>
<?php /*
<div class="b-pic-tu-banner">
<div class="b-pic-tu-banner__img">
    <div class="b-pic-tu-banner__content">
      <?php if($is_frl){ ?>
        <p>
            ����� ������ FL.ru � �������������� �������� �� ������������� ����
        </p> 
        <br/>
        <a class="b-pic-tu-banner__content-btn" href="<?php echo tservices_helper::new_url()?>" onClick="yaCounter6051055.reachGoal('add_new_tu');">������������ ������</a>  
      <?php }else{ ?>
        <p class="b-txt_margbot_null">
            ����� ������ FL.ru � �������������� �������� �� ������������� ����
        </p>
      <?php } ?>
    </div>
    <div class="b-pic-tu-banner__cloud">
      ������ ����� ��� ��� ������ � ���� ������
      <p class="b-pic-tu-banner__cloud-price">�� 600 �. � 2 ���</p>
      <div class="b-pic-tu-banner__cloud-corner"></div>
    </div>
</div>
</div>
*/ ?>