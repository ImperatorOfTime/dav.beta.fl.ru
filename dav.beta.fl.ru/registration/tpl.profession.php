<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.common.php");
    $xajax->printJavascript('/xajax/');
?>
<div class="b-layout">
    <h1 class="b-page__title b-page__title_padbot_30">
        ������� ���� �������������
    </h1>
    
    <div class="b-layout__txt b-layout__txt_padbot_20">
        ������� ���� ������������� � �� ������ ������������ �� ������ � ����� ��������, �� � � �������� �� ����� �������������.<br/>
        �������� ������������� ������� ��������� ����� ����� ��� �� �����.
    </div>
    
    <form action="" method="post">

        <?php if($professions_data): ?>
<?php
                $items = array();
                foreach ($professions_data as $profession_data):
                    $gid = $profession_data['gid'];
                    $id = $profession_data['id'];
                    
                    if (!$id || $gid <= 0):
                        continue;
                    endif;
                    
                    if (!isset($items[$gid])):
                        $items[$gid] = $profession_data;
                        $items[$gid]['elements'] = array();
                    endif;
                    
                    $items[$gid]['elements'][$id] = $profession_data;
                endforeach; 
                
?>
        <div class="b-select b-select_margbot_20">
        <select class="b-select__select b-select__select_flat" name="profession">
            <option value="0">��� �������������</option>
<?php
        foreach ($items as $gkey => $item):
?>
         <optgroup label="<?=$item['gname']?>">
<?php
                    foreach ($item['elements'] as $key => $element):
?>              
           <option value="<?=$key?>"><?=$element['name']?></option>
<?php
                    endforeach;
?>
         </optgroup>
<?php
        endforeach;
?>
        </select>
        </div>
        <?php endif; ?>

        <button class="b-button b-button_flat b-button_flat_green" type="submit">
            <?php if ($is_other_redirect): ?>
            ����� &raquo;
            <?php else: ?>
            ������� � ������������ ������
            <?php endif; ?>
        </button>
        
    </form>
    
</div>