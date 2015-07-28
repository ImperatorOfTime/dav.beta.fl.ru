<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } 
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
  $profs = professions::GetAllProfessions();
  if (!(hasPermissions('adm') && hasPermissions('adminspam'))) { header ("Location: /404.php"); exit; }
?>
<script type="text/javascript">
CKEDITOR.config.customConfig = '/scripts/ckedit/config_simple.js';

function checkexts() {
            var val = 0;
            var grp = document.getElementById('idForm')['attach[]'];
            if (typeof grp.length != 'undefined') {
                for (i=0; i<grp.length; i++) {
                    if (!allowedExt(grp[i].value)) return false;
                }
            } else {
                if (!allowedExt(grp.value)) return false;
            }
            return true;
        }
</script>
<style>
	.addButton INPUT { width: 28px; }
</style>


<strong>�������������</strong><br><br>
  <? if ($_GET['result']=='success') { ?>
    <div style="margin:10px 0 20px 0">
      <img src="/images/ico_ok.gif" alt="" border="0" height="18" width="19"/>&nbsp;��������� ����������!
    </div>
  <? } ?>
	<? if ($error) print(view_error($error));?>
<form id="idForm" action="/siteadmin/admin/" method="post" enctype="multipart/form-data" onSubmit="if(!checkexts()) return false; if(!warning()) return false; this.btn.value='���������'; beforeSubmit(); this.btn.disabled=true;">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr valign="bottom">
    <td>
      <script language="JavaScript">
        function addProfElm(itm)
        {
          var profSel = itm.previousSibling;
          if(!profSel || profSel.disabled || profSel.isDisabled)
            return;
          if(itm.innerHTML=='+') {
            var newProf = itm.parentNode.cloneNode(true);
            newProf = itm.parentNode.parentNode.appendChild(newProf);
            newProf.childNodes[0].options[0].text=" ------------------------";
            newProf.childNodes[0].options[0].value = 'empty';
            newProf.childNodes[1].innerHTML='&ndash;';
            newProf.childNodes[1].title = '������� �������';
          }
          else 
            itm.parentNode.parentNode.removeChild(itm.parentNode);
        }

        function beforeSubmit()
        {
          var toPro = document.getElementById('idToPro');
          var toFrl = document.getElementById('idToFrl');

          if(!toFrl.checked && (toPro.disabled || toPro.isDisabled))
          {
            var nToPro = document.createElement('INPUT');
            nToPro.type = 'hidden';
            nToPro.name = toPro.name;
            nToPro.value = toPro.checked ? 1 : 0;
            idForm.appendChild(nToPro);

            var toNotPro = document.getElementById('idToNotPro');
            var nToNotPro = document.createElement('INPUT');
            nToNotPro.type = 'hidden';
            nToNotPro.name = toNotPro.name;
            nToNotPro.value = toNotPro.checked ? 1 : 0;
            idForm.appendChild(nToNotPro);

            var profSels = document.getElementsByName('prof[]');
            var inp, prof, i, len = profSels.length;
            for(i=0;i<len;i++) {
              prof = profSels[i];
              inp = document.createElement('INPUT');
              inp.type = 'hidden';
              inp.name = prof.name;
              inp.value = prof.value;
              idForm.appendChild(inp);
            }
          }
        }

        function onOffFrlUI(frlRadioItm)
        {
          var toPro = document.getElementById('idToPro');
          var toNotPro = document.getElementById('idToNotPro');
          var profSels = document.getElementsByName('prof[]');
          var curDisabled = true;
          var addDelCOlor = '#c0c0c0';
          var addDelCursor = 'default';
          var addDelTitle = '';
          if(frlRadioItm.checked) {
            curDisabled = false;
            addDelCOlor = '#666';
            addDelCursor = 'hand';
          }
            
          toPro.disabled=curDisabled;
          toNotPro.disabled=curDisabled;
          var addDel, prof, i, len = profSels.length;
          for(i=0;i<len;i++) {
            prof = profSels[i];
            prof.disabled=curDisabled;
            addDel = prof.nextSibling;
            with (addDel.style) {
              color = addDelCOlor;
              cursor = addDelCursor;
            }
            addDel.title = (curDisabled==false ? (!i ? '��������' : '�������')+' �������' : '');
          }
        }
      </script>
      <table border="0" cellspacing="0" cellpadding="0" style="table-layout:fixed;width:100%;border:1px solid #c5c5c5;background:#f3f2f0;">
        <tr valign="top">
          <td>
            <table border="0" cellspacing="0" cellpadding="6"  style="table-layout:fixed;width:100%">
              <col style="width:200px"/>
              <col/>
              <tr valign="baseline">
                <td>
                  ��������� ���������:
                </td>
                <td align="right">
                 <input<?=($toEmail?' checked':'')?> id="idToEmail" name="toEmail" type="checkbox"/>
                 <label for="idToEmail">� ��������� �����������?</label>
                </td>
              </tr>
            </table>
            <br/>
            <div style="padding:6px;">
              <input<?=(($toWrk || $toFrl)?'':' checked')?> id="idToAll" type="radio" name="toAll" onclick="idToWrk.checked=false;idToFrl.checked=false;idToLogins.checked=false;onOffFrlUI(document.getElementById('idToFrl'));"/><LABEL for="idToAll">&nbsp;����</LABEL>
            </div>
            <div style="margin:0 6px;border-bottom:1px solid #c0c0c0;"></div>
            <div style="padding:6px;">
              <input<?=($toWrk?' checked':'')?> id="idToWrk" type="radio" name="toWrk" onclick="idToAll.checked=false;idToFrl.checked=false;idToLogins.checked=false;onOffFrlUI(document.getElementById('idToFrl'));"/><LABEL for="idToWrk">&nbsp;�������������</LABEL>
            </div>
            <div style="margin:0 6px;border-bottom:1px solid #c0c0c0;"></div>
            <div style="padding:6px;">
              <table border="0" cellspacing="0" cellpadding="0" style="width:100%;">
                <tr valign="baseline">
                  <td>
                    <input<?=($toFrl?' checked':'')?> id="idToFrl" type="radio" name="toFrl" onclick="onOffFrlUI(this);idToAll.checked=false; idToWrk.checked=false;idToLogins.checked=false;"/><LABEL for="idToFrl">&nbsp;�����������</LABEL>&nbsp;&nbsp;&nbsp;
                  </td>
                  <td>
                   <nobr>
                    <input <?=($toPro?' checked':'')?> <?=($toFrl?'':' disabled')?> id="idToPro" type="checkbox" name="toPro" onclick="if(!idToNotPro.checked) idToNotPro.checked=true;"/><LABEL for="idToPro">&nbsp;PRO</LABEL>&nbsp;&nbsp;
                    <input<?=($toNotPro?' checked':'')?> <?=($toFrl?'':' disabled')?> id="idToNotPro" type="checkbox" name="toNotPro" onclick="if(!idToPro.checked) idToPro.checked=true;"/><LABEL for="idToNotPro">&nbsp;���&nbsp;PRO</LABEL>&nbsp;&nbsp;&nbsp;&nbsp;
                    </nobr>
                  </td>
                  <td>
                    <? $i=0; foreach($selectedProfs as $selProf) { ?>
                    <div <?// ���������� ><SELECT, ����� �� ���� ������� ���������� Child-� ?>
                      ><select<?=($toFrl?'':' disabled')?> id="idProf" name="prof[]">
                        <option style='color:black'<?=($selProf=='NULL' || $selProf=='empty'?' selected':'')?> value="<?=($selProf=='empty'?'empty':'NULL')?>">&nbsp;<?=($i?'------------------------':'��� �������������')?></option>
                        <?
                          $curGroup = NULL;
                          foreach($profs as $prof)
                          { 
                            if($prof['groupid']!=$curGroup) {
                              $curGroup = $prof['groupid'];
                              print("<option style='color:black'".($selProf=="::{$prof['groupid']}" ? ' selected':'')." value='::{$prof['groupid']}'>&nbsp;{$prof['groupname']}</OPTION>");
                            }
                            print("<option".($selProf==$prof['id'] ? ' selected' : '')." value='{$prof['id']}'>&nbsp;{$prof['groupname']}::{$prof['profname']}</option>");
                          }
                        ?>
                      </select <?// ���������� ><SPAN, ����� �� ���� ������� ���������� Child-� ?>
                      ><span style="margin-left:5px;width:24px;<?=($toFrl?'cursor:hand;color:#666':'cursor:default;color:#c0c0c0')?>;font-size:18px;text-align:center; font-weight:bold"
                             title="<?=($toFrl?($i==0?'�������� �������':'������� �������'):'')?>" onclick="addProfElm(this)"><?=($i==0?'+':'&ndash;')?></SPAN>
                    </div>
                    <? $i++; } ?>
                  </td>
                  <td align="right" style="width:20px">
                    <a style="cursor:hand;color:#909090;font-size:18px;font-weight:bold" href="javascript:void(0)" onclick="alert(this.getAttribute('titl'))"
                          titl="
������� ���������� ��������� �������� ������������� ��� ����� �������.
���� ������ ������, �� ��������� ����� ���������� ���� �����������, � ������� ���������� ���� �� ���� �� ����������� ����� ������� �������������.

���� ���� �� � ����� �� ��������� ������ ����� '��� �������������', �� ��������� ����� ���������� ����, ���������� �� �������������.

����� ���������. �� ����� ������� ������� ��������� ��������� � ������� � ��� ���� � �� �� �������������.

�� ����� �������� � ��� ������� ������������� (��������, ���������� ������::���-���������������� � ����������������::���-����������������). ���� ������� ���� �� ����� �������������, �� ��������� ������ � ������������� ������ (�������) �������������.
"
                    >?</a>
                  </td>
                </tr>
              </table>
            </div>
            <div style="margin:0 6px;border-bottom:1px solid #c0c0c0;"></div>
            <div style="padding:6px;">
              <input<?=($toLogins?' checked':'')?> id="idToLogins" type="radio" name="toLogins" onclick="idToAll.checked=false;idToFrl.checked=false;idToWrk.checked=false;onOffFrlUI(document.getElementById('idToFrl'));"/><LABEL for="idToLogins" title="������ ������� ����� �������">&nbsp;���������:</LABEL>
              &nbsp;<input name="logins" style='width: 630px' type="text">
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr valign="top">
    <td style="padding:10px 0 10px 0">
      <textarea cols="10" rows="8" name="msg" class="ckeditor" id="msg_texarea" conf="admin"><?=($msg ? $msg : ($FROM == 'admin' ? '������������, %USER_NAME%!' : '') )?></textarea>
      <? if ($alert[2]) print(view_error($alert[2])) ?><br/>
      <?php if ( $FROM == 'admin' ): ?>
        � ������ ������ ����� ������������ ��������� ����������:<br/>
        &#37;USER_NAME&#37; - ��� ������������<br/>
        &#37;USER_SURNAME&#37; - ������� ������������<br/>
        &#37;USER_LOGIN&#37; - ����� ������������<br/>
      <?php endif; ?>
    </td>
  </tr>
  <tr>
    <td valign="top">
      ���������:
    </td>
  </tr>
  <tr>
    <td height="40">
	<div id="ad_button">
	<div>
		<div id="attaches" style="padding-bottom: 5px">
			<input type="file" name="attach[]" style="width: 95%">
			<span class="addButton" style="font-size: 16px;">&nbsp;</span>
		</div>
	</div>
	</div>
	<script type="text/javascript">
		new mAttach(document.getElementById('attaches'), <?=messages::MAX_FILES?>);
	</script>
	  <? if ($alert[1]) print(view_error($alert[1])) ?>
	  ����� ��������� �������� ��������� � ��������: <?=implode(', ', $GLOBALS['disallowed_array'])?>
	  <?php /*
      � ������� ����� ���� �������� ���������:
      <ul><li>��������.<br>
      �������� ����������� ���� �����������.<br>
      ������: gif, jpeg.<br>
      ������������ ������ ��������: 440x600 ��������; 300 ��.</li>
      <li>����.<br>
      ���� ����������� ��� ������� &laquo;���������&raquo; ���� �����������.<br>
      ������: zip, rar.</li></ul> */ ?>
    </td>
  </tr>
  <tr>
    <td align="right"><input type="hidden" name="MAX_FILE_SIZE" value="100000">
      <input type="hidden" name="action" value="post_msg">
      <input type="submit" name="btn" class="btn" value="���������">
    </td>
  </tr>
  <tr><td colspan="3">&nbsp;</td></tr>
</table>
</form>
