<? if(name_page($path) == 'bill') { // ���� ������� �� �������� ������� �����?>
<div id="wallet_info<?= $service['id']?>" class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_padleft_70 b-layout__txt_fontsize_15 <?= $service['is_auto'] == 't' || $service['auto'] == 't' ? "": "b-layout__txt_hide"  ?>">
    ������ � �������
    <span class="b-fon b-fon_bg_f2 b-fon_pad_2_5 b-fon__border_radius_3 ">
        <?= WalletTypes::getNameWallet($wallet->data['type'], 1)?> &#160; <?= $wallet->getWalletBySecure()?>
    </span>
</div>
<? } else if(name_page($path) == 'payed' || name_page($path) == 'payed-emp') { // �������� ��� ������������ � ����������?>
<div class="b-layout__h3 b-layout__h3_padbot_5">
    �������������
    <span class="b-layout__txt b-layouyt__txt_weight_normal"> � �������
        <span class="b-fon b-fon_bg_f2 b-fon_pad_2_5 b-fon__border_radius_3 ">
            <?= WalletTypes::getNameWallet($wallet->data['type'], 1)?> &#160; <?= $wallet->getWalletBySecure()?>
        </span>
    </span>
</div>
<? } else { //elseif?>
<div class="b-layout__txt b-layout__txt_padtop_5">
    ������ � �������
    <span class="b-fon b-fon_bg_f2 b-fon_pad_2_5 b-fon__border_radius_3 ">
        <?= WalletTypes::getNameWallet($wallet->data['type'], 1)?> &#160; <?= $wallet->getWalletBySecure()?>
    </span>
</div>
<? }//else?>