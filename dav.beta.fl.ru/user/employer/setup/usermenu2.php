<? // ������� ��� template2.php ?>
<div class="tabs">
	<ul class="clear">
  		<li class="tab1<?=($activ_tab==1 ? ' active' : '')?>"><span><a href="/users/<?=$user->login?>/setup/projects/">������� � ��������</a></span></li>
        <li class="tab3<?=($activ_tab==3 ? ' active' : '')?>"><span><a href="/users/<?=$user->login?>/setup/info/">����������</a></span></li>
		<li class="tab5<?=($activ_tab==5 ? ' active' : '')?>"><span><a href="/users/<?=$user->login?>/setup/finance/">�������</a></span></li>
	</ul>
</div>
