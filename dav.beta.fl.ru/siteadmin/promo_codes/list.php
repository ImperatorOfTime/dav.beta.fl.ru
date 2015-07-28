<?php if (count($data)): ?>
<table>
    <thead>
        <tr>
            <th class="b-layout__td b-layout__td_pad_10"><strong>�����-���</strong></th>
            <th class="b-layout__td b-layout__td_pad_10"><strong>���� ������ � ��������� ��������</strong></th>
            <th class="b-layout__td b-layout__td_pad_10"><strong>������ ������, %</strong></th>
            <th class="b-layout__td b-layout__td_pad_10"><strong>������ ������, ���.</strong></th>
            <th class="b-layout__td b-layout__td_pad_10"><strong>�������</strong></th>
            <th class="b-layout__td b-layout__td_pad_10"><strong>������</strong></th>
        </tr>
    </thead>
    <tbody>
<?php foreach($data as $code): ?>
    <tr>
        <td class="b-layout__td b-layout__td_pad_10">
            <strong><?=$code['code']?></strong><br/>
            <a href="/siteadmin/promo_codes/?action=edit&id=<?=$code['id']?>">��������</a> ���
            <a href="/siteadmin/promo_codes/?action=delete&id=<?=$code['id']?>">�������</a>
        </td>
        <td class="b-layout__td b-layout__td_pad_10"><?=dateFormat('d.m.Y', $code['date_start'])?> &mdash; <?=dateFormat('d.m.Y', $code['date_end'])?></td>
        <td class="b-layout__td b-layout__td_pad_10"><?=$code['discount_percent']?></td>
        <td class="b-layout__td b-layout__td_pad_10"><?=$code['discount_price']?></td>
        <td class="b-layout__td b-layout__td_pad_10"><?=$code['count'] - $code['count_used']?> (�� <?=$code['count']?>)</td>
        <td class="b-layout__td b-layout__td_pad_10"><?=$code['service_string']?></td>
    </tr>
<?php endforeach; ?>
    <tbody>
</table>
<?php else: ?>
<p>�� ������ ���� �� �������</p>
<?php 
endif;

