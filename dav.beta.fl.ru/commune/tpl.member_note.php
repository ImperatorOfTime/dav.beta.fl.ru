<strong>�������:</strong>
<noindex>
<span id="view_note_<?= $member_id ?>" style="display:block">
    <p id="idNote<?= $member_id ?>"><?= reformat($note, 15, 0, 0, 1, 15) ?></p>
    <? if($admin) { ?><a id="idNoteEditLink<?= $member_id ?>" onclick="document.getElementById('view_note_<?= $member_id ?>').style.display = 'none'; document.getElementById('edit_note_<?= $member_id ?>').style.display = 'block'; return false;" href="#" class="lnk-dot-green">�������������</a> <?php }//if?>
</span>
<span id="edit_note_<?= $member_id ?>" style="display:none">
    <div class="cau-note-form" id="idNBox<?= $member_id ?>">
        <textarea id="idNTa<?= $member_id ?>" rel="500" rows="3" cols="10"><?= $note ?></textarea>
        <div class="form-btns">
            <input onclick="xajax_UpdateNote('<?= $member_id ?>', <?= $commune_id ?>, document.getElementById('idNTa<?= $member_id ?>').value)" type="button" value="���������"/>&nbsp; <a href="#" onclick="document.getElementById('view_note_<?= $member_id ?>').style.display = 'block'; document.getElementById('edit_note_<?= $member_id ?>').style.display = 'none'; return false;" class="lnk-dot-666">��������</a>
        </div>
    </div>
</span>
</noindex>
