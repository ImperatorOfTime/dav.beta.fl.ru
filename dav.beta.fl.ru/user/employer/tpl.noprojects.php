<div style="<? print $style?> color:#000;"><?php    
    if (!$kind) { 
        if (($conted_prj["all"] == 0)&&($conted_prj["open"] == 0)&&($conted_prj["closed"] == 0)) {
                print "� $entity ��� �� ������ �������";
        }elseif (($conted_prj["all"] != 0)&&($conted_prj["open"] == 0)) {
            print "� $entity ��� �������� ��������";
        }elseif (($conted_prj["closed"] == 0)&&($_GET["closed"] == 1)) {
            print "� $entity ��� �������� ��������";
        }
    }    
    if ($kind == 1) {
        if (($conted_prj["all"] == 0)&&($conted_prj["open"] == 0)&&($conted_prj["closed"] == 0)) {
            print "� $entity ��� �� ������ ������� ���-����";
        }elseif (($conted_prj["all"] != 0)&&($conted_prj["open"] == 0)) {
            print "� $entity ��� �������� �������� ���-����";
        }elseif (($conted_prj["closed"] == 0)&&($_GET["closed"] == 1)) {
            print "� $entity ��� �������� �������� ���-����";
        }
    }
    if ($kind == 2) {
        if (($conted_prj["all"] == 0)&&($conted_prj["open"] == 0)&&($conted_prj["closed"] == 0)) {
            print "� $entity ��� �� ������ ��������";
        }elseif (($conted_prj["all"] != 0)&&($conted_prj["open"] == 0)) {
            print "� $entity ��� �������� ���������";
        }elseif (($conted_prj["closed"] == 0)&&($_GET["closed"] == 1)) {
            print "� $entity ��� �������� ���������";
        }
    }
    if ($kind == 3) {
        if (($conted_prj["all"] == 0)&&($conted_prj["open"] == 0)&&($conted_prj["closed"] == 0)) {
            print "� $entity ��� �� ������ ������� � ����";
        }elseif (($conted_prj["all"] != 0)&&($conted_prj["open"] == 0)) {
            print "� $entity ��� �������� �������� � ����";
        }elseif (($conted_prj["closed"] == 0)&&($_GET["closed"] == 1)) {
            print "� $entity ��� �������� �������� � ����";
        }
    }?></div>