<?php

/**
 * ������ ����������� ��� ���� �����
 */

?>
<div class="b-page__desktop">
	<div class="l-outer w-outer">
		<header class="l-header">
			<div class="l-header-inside">
				<section class="l-header-section l-header-second-section">
					<div class="b-general-notification">                
<?php
    switch ($type):
        case SubBarNotificationHelper::TYPE_GUEST_NEW_ORDER:
?>
                        ����������� � �������� ������������ �� ����� � ��������� ������� ������! 
                        ��� �����: <span class="b-txt b-txt_color_000"><?=$login?></span>, 
                        ������: <span class="b-txt b-txt_color_000"><?=$password?></span>
                        <a class="b-general-notification-link b-general-notification-employer-link" href="/users/<?=$login?>/setup/pwd/">
                            �������� ������
                        </a>
<?php                        
            break;
        
        case SubBarNotificationHelper::TYPE_GUEST_NEW_PROJECT:
?>        
                        ����������� � �������� ������������ �� ����� � ����������� ������� �������!
                        ��� �����: <span class="b-txt b-txt_color_000"><?=$login?></span>, 
                        ������: <span class="b-txt b-txt_color_000"><?=$password?></span>
                        <a class="b-general-notification-link b-general-notification-employer-link" href="/users/<?=$login?>/setup/pwd/">
                            �������� ������
                        </a>
<?php        
            break;
        
        case SubBarNotificationHelper::TYPE_GUEST_NEW_VACANCY:
?>        
                        ����������� � �������� ������������ �� ����� � ����������� ������ ��������!
                        ��� �����: <span class="b-txt b-txt_color_000"><?=$login?></span>, 
                        ������: <span class="b-txt b-txt_color_000"><?=$password?></span>
                        <a class="b-general-notification-link b-general-notification-employer-link" href="/users/<?=$login?>/setup/pwd/">
                            �������� ������
                        </a>
<?php        
            break;
        
        case SubBarNotificationHelper::TYPE_RESERVE_PROMO:
?>        
                        ������ ������ ������ - <b>������ ������</b>. ����������� �������� �� 
                        <a class="b-general-notification-link b-general-notification-employee-link" href="<?=$url?>">
                            ���������� ������
                        </a>
<?php        
            break;
        
        case SubBarNotificationHelper::TYPE_USER_ACTIVATED:
?>
                        ��� ������� ������� �����������
<?php                        
            break;

        
        default:
            echo $text;
            
    endswitch;
?>
					</div>
				</section> 
			</div>
		</header>
	</div>
</div>