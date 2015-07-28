<?php include('xajax.php');?>
<h2>������</h2>
<div class="docs-block c">
    <div class="docs-content c">
        <div class="docs-cnt">
            <div class="docs-breadcrumb">
                <a href="/service/docs/">��������� �� �������</a>
            </div>
            <h3><?= $section['name'];?></h3>
<? include('search_form.php');?>
            <?php if(is_array($search_results) && count($search_results)){ ?>
            <div class="help-search-res">
                <div class="help-search-info">������� <?= count($search_results).' '.getTermination(count($search_results), array('����������','����������','����������'));?></div>
            <ol start="1">
            <?php foreach($search_results as $res){ ?>


                <li>
            <h4><a href="/service/docs/document/?id=<?= $res['id'];?>"><?= $res['name'];?></a></h4>
            <p><?= $res['desc'];?></p>

        </li>
            <?} ?>
        </ol>
            </div>
           <?}else{ //if ?>
            <div class="help-search-fail"> 
				<strong>���, �� ������ ������� �� ������� ����������.</strong><br>
				����������, ���������� �������������� ������ ����� � ��������� �����. �� ������ ���������� � <a href="https://feedback.fl.ru/">������ ���������</a>.
            </div>
            <?php } ?>
        </div>
    </div>
</div>
