<div id="attach">
    <div id="ad_button">
        <div>
            <div id="attaches" style="padding-bottom: 5px">
                <input type="file" name="attach[]" class="input-file" size="50">
                <span class="addButton" style="font-size: 16px;">&nbsp;</span>
            </div>
        </div>
        <? /*
          <input type="hidden" name="MAX_FILE_SIZE" value="<?=commune::MSG_FILE_MAX_SIZE?>"/>
          <input type="file" style="width:100%" name="file"/> */ ?>

        � ������� ����� ���� �������� ���������:
        <ul>
            <li>��������: <?= commune::MSG_IMAGE_MAX_WIDTH ?>x<?= commune::MSG_IMAGE_MAX_HEIGHT ?> ��������. <?= (commune::MSG_IMAGE_MAX_SIZE / 1024) ?> ��. </li>
            <li>
                �� ������ ���������� �� <?=commune::MAX_FILES ?> ������ ����� ������� �� ����� <?=(commune::MAX_FILE_SIZE / (1024*1024))?> ��.<br/>
                ����� ��������� �������� ��������� � ��������: <?=implode(', ', $GLOBALS['disallowed_array'])?>
            </li>
        </ul>
    </div>
    <script type="text/javascript">
        new mAttach(document.getElementById('attaches'), <?= (commune::MAX_FILES - $max) ?>);
    </script>
    <br/><?= ($error ? view_error($error) . '<br/>' : '') ?>
</div>