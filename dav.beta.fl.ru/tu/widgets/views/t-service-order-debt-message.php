<?php

/**
 * ������ ������� TServiceOrderDebtMessage
 * ��������� � ��������� ���������� �� ��-�� �� ��������� ����� ��
 */

?>
<div class="b-fon">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_35 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
        <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span>
        <?php if($debt_info['is_blocked'] == 't') {?>
        
        ��������, �� �� ��������� ���� ������ ���� ������� ������ � �������� � �������, ��� ����������� ��������� �� � �������� ������.<br/>
        ��� ������ ������������� �� ������ ����� � �������� ����� ��������, �� ������������ ���� ������.
        
        <?php }else{ ?>
        
        �������� ��������, ��� <span class="b-layout__bold">�� <?php echo date('d.m.Y', strtotime($debt_info['date'])) ?></span> (������������) ��� ���������� �������� ������������� �� ������ ����� � ��������.<br/> 
        ����� �� ��������� ����� ������ ���� ������� ������ � �������� � �������, ��� ����������� ��������� �� � �������� ������.
        
        <?php } ?>

        <br/>
        <a class="b-layout__link" href="/bill/">��������� ���� � �������� �������������</a>
        
    </div>
</div>