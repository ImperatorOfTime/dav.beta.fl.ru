<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
	$uid = $user->GetUid($error, $login);
	if ($uid) {
	    $ban=$user->GetBan($uid);
?>
<div class="b-layout__right b-layout__right_float_right b-layout__right_width_240 b-page__desktop">
    <!-- Banner 240x400 -->
    <?= printBanner240(false); ?>
    <!-- end of Banner 240x400 -->
</div>
<div class="b-layout__one">
<? if ($ban['reason'] == 4)  {?>
	<h1 class="b-page__title">������� ������</h1>
   <div class="b-layout__txt b-layout__txt_padbot_20">���� � ��� ���� ������� &mdash; �������� � <a href="http://feedback.fl.ru" target="_blank">������ ���������</a></div>
<? } else { ?>
	<h1 class="b-page__title">������� ������������ <?=($ban["to"] ? "�� ".date("d.m.Y  H:i",strtotimeEx($ban["to"])) : "")?></h1>
    <div class="b-layout__txt b-layout__txt_padbot_20">
    <?
    switch ($ban["reason"]) {
        case 1:
            // print "<br/>�������: ������ ������������ ��������� �� �����";
            break;
        case 2:
            print "�������: ���� � ������";
            break;
        case 3:
            print "�������: ���� � ��������";
            break;
        default:
            break;
    }
    ?>
    </div>
    <div class="b-layout__txt b-layout__txt_padbot_20"><?=($ban["comment"] ? "����������� ��������������: ".$ban["comment"] : "")?></div>
    <div class="b-layout__txt b-layout__txt_padbot_20">������ ��������� <a href="http://feedback.fl.ru" target="_blank">http://feedback.fl.ru</a></div>
    
<?  } ?>
</div>
<? }?>
