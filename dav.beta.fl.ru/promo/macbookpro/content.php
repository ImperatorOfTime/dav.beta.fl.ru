<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-verification">
    <tbody>
        <tr class="b-layout__tr">
            <td class="b-layout__right_padbot_5 b-layout__right_width_72ps b-promo" style="text-align: center">
                <h1 class="b-page__title">������ MacBook PRO 13" ��������� ������ fl.ru � ���� ���� ������</h1>
            </td>
        </tr>
        <tr class="b-layout__tr">
            <td class="b-layout__right_padbot_5 b-layout__right_width_72ps b-promo" style="text-align: center">
                <div class="b-layout__txt b-layout__txt_fontsize_15">              
                    <h3>��������� � �������� &mdash; <?=$macbook_top_10_total?> ���.</h3>
                </div>
            </td>
        </tr>        
        <tr class="b-layout__tr">
            <td class="b-layout__right_padbot_5 b-layout__right_width_72ps b-promo" style="text-align: center">
                <img class="b-pic" src="https://st.fl.ru/about/macbookpro.jpg?123">
            </td>
        </tr>
        <tr class="b-layout__tr">
            <td class="b-layout__right_padbot_5 b-layout__right_width_72ps b-promo" style="text-align: center">
                <div class="b-layout__txt b-layout__txt_fontsize_15">
                    <h3>������� ��������:</h3>
                </div>
            </td>
        </tr>
        <tr class="b-layout__tr">
            <td class="b-layout__right_padbot_5 b-layout__right_width_72ps b-promo">
                <div class="b-layout__txt b-layout__txt_fontsize_15">
                    <ol>
                        <li>� �������� ��������� ������� ������ ����������.</li>
                        <li>��� ������� ���������� � ������� ���� 2015 ���� ���������� ���� �� ������� ����� �� �����
                            <a href="https://www.fl.ru">FL.ru</a>:
                            <ul>
                                <li>������� <a href="/payed/" target="blank">PRO</a> ��� <a href="/profi/" target="blank">PROFI</a>;</li>
                                <li>����������� � �������� �����������;</li>
                                <li>����������� � �������� �����;</li>
                                <li>����������� � ����� ���������� ������ �� ������� ��������.</li>
                            </ul>
                        </li>
                        <li>
                            ����� ������ ������� ����������� ���-10 �����������, ���� ������ �������� � ���� 2015 ���� ��������������� ������. ��� �������� ����������� ���������� �������, � �� �� �����.
                        </li>
                        <li>
                            ����� ���, ��� �� ��������� �� 30 ���� ����� � ���-10 ��������� ������� ������������� MacBook PRO 13".
                        </li>
                        <li>
                            ����� ��������� ����� ��������� 6 ���� 2015 ����.
                        </li>
                        <li>
                            ���������� �������� ����� �������� ����������� � ����������� ���������� � ������ �������� �����.
                        </li>
                        <li>
                            ����, ������� ����� ����������� ����� ��������, ����������� � ���, ��� ��� ���, ������� � ������ ��������� � �� ����� ���� ������������ ������������� � �������� ������ ��� ��������� ��������������� �������� � ��� �������������� ����� � ����� �������������� � ����������� ���������� ��������, � ����� � ��������� �����.
                        </li>
                        <li>
                            ����������� �������� ��������� �� ����� ����� ������� ���������� � ��������� � ��������� �������� �� ����� ���������� ��������.
                        </li>
                    </ol>
                </div>  
            </td>
        </tr>
        <tr class="b-layout__tr">
            <td class="b-layout__right_padbot_5 b-layout__right_width_72ps b-promo" style="text-align: center">
                <div class="b-layout__txt b-layout__txt_fontsize_15">
                    <h2>������� (TOP-10)</h2>
                </div>

                <?php if ($macbook_top_10): ?>
                    <?php foreach ($macbook_top_10 as $data): ?>
                        <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_fontsize_15 b-layout__txt_lineheight_1 b-layout__txt_padbot_15">
                            <?php if ($data['user']->uname || $data['user']->usurname): ?>
                                <a class="b-layout__link b-layout__link_color_64 b-layout__link_bold b-layout_hover_link_decorated" href="<?=$data['user']->getProfileUrl()?>"><?=htmlspecialchars($data['user']->uname) . ' ' . htmlspecialchars($data['user']->usurname)?></a> 
                            <?php endif; ?>
                            [<a class="b-layout__link b-layout_h b-layout__link_color_64 b-layout__link_no-decorat" href="<?=$data['user']->getProfileUrl()?>"><?=$data['user']->login?></a>]
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </td>
        </tr>
    </tbody>
</table>
