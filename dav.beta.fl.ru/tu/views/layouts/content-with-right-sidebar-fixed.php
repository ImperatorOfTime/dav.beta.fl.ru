<?php
if(!defined('IN_STDF')) 
{
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 * �������� �������� � ������ ��������� ������������� ������
 */

// @var CController $this        ��������� ��������
// @var string $content          �������� ����� ��������

// @var int $g_page_id    ���������� ��� �������� ��� ���������� ������� ������� �� ���������
global $g_page_id; // ���������� ��� �������� ��� ���������� ������� ������� �� ���������

?>


<div class="b-layout b-layout__page">
    <div class="b-layout__right b-layout__right_float_right b-layout__right_width_240 b-page__desktop">
        <?php echo $this->renderClip('sidebar') ?>
    </div>
    <div class="b-layout__one b-layout__left_margright_260 b-layout__one_width_full_ipad">
        <?php echo $content ?>
    </div>
</div>