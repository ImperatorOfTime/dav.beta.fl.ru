<h2>�������������� ������</h2>

<? if($_POST['spec_sum']>0): ?>
�������������� �������������: <?=htmlspecialchars($_POST['spec_sum'])?> (<?=htmlspecialchars($_POST['spec_sum'])?> FM)<br/>
<? endif; ?>

<?if($_POST['rating_sum'] >0):?>
�������: +<?=htmlspecialchars($_POST['rating_sum'])?> (<?=htmlspecialchars($_POST['rating_sum'])?> FM)<br/>
<?endif;?>