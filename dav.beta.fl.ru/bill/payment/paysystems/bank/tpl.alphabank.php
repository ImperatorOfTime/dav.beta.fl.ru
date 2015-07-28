<h2 class="b-layout__title b-layout__title_padbot_30">������ ����� ������-����</h2>

<form id="<?= $type_payment ?>" name="<?= $type_payment ?>" action="<?= "/bill/payment/?type={$type_payment}"?>" accept-charset="UTF-8" method="post">  
    <input type="hidden" name="action" value="reserve" />
</form>

<table class="b-layout__table b-layout__table_width_full">
    <tbody>
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_padright_20 b-layout__td_center b-layout__td_width_120">
                <a class="b-layout__link" target="_blank" href="http://www.alfabank.ru/"><img class="b-layout__pic" width="130" height="97" alt="" src="/images/bill-alfa-big.png"></a>
            </td>
            <td class="b-layout__td b-layout__td_padleft_30">
                <h3 class="b-layout__h3">���������� ������� ����� � ������� ��������-����� ������-����</h3>
                <div class="b-fon b-fon_padbot_20">
                    <b class="b-fon__b1"></b>
                    <b class="b-fon__b2"></b>
                    <div class="b-fon__body b-fon__body_pad_10_20">
                        <div class="b-layout__txt b-layout__txt_padbot_5">1. � ��������-����� ������-���� � ������� ������� ����� � ������ ������� �������� ��������� ������� ������.</div>
                        <div class="b-layout__txt b-layout__txt_padbot_5">2. �� ������ ����������� �������� ������������: �Free-lance.ru�.</div>
                        <div class="b-layout__txt b-layout__txt_padbot_5">3. ������� ��� ����� �� Free-lance.ru � ���������� �������</div>
                        <div class="b-layout__txt">4. ����������� �������� ����������� �������, ������� ������ � SMS-���������.</div>
                    </div>
                    <b class="b-fon__b2"></b>
                    <b class="b-fon__b1"></b>
                </div>
                
                <div class="b-layout__txt b-layout__txt_padbot_15">�������� ���� �������� �� ��, ��� �������� ����������� <span class="b-layout__bold">� ������� ���������� �������� ��� ����� ���������� ��������</span>.</div>
                <div class="b-layout__txt b-layout__txt_padbot_15">�������� �� ������ �� ���������!</div>
                
                <? include ( $_SERVER['DOCUMENT_ROOT'] . "/bill/payment/paysystems/tpl.button_buy.php");?>
            </td>
        </tr>
    </tbody>
</table>