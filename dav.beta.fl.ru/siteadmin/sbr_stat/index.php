<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr_meta.php");
    
session_start();
get_uid();
	
if ( !(hasPermissions('sbr') || hasPermissions('sbr_finance') || hasPermissions('tmppayments')) ) {
    header_location_exit("/404.php");
}
$css_file = array('moderation.css','/css/block/b-menu/_tabs/b-menu_tabs.css','nav.css');
$js_file = array('highcharts/mootools-adapter.js', 'highcharts/highcharts.js');

$show_results = __paramInit('bool', 'show_results', null, false);
$tab = __paramInit('string', 'tab', null, 'graph');


if ($show_results) {
    $period_param = __paramInit('string', 'period', null, 'today');
    $custom_period_from = __paramInit('string', 'custom_period_from', null, '');
    $custom_period_to = __paramInit('string', 'custom_period_to', null, '');
    $akkr_param = __paramInit('bool', 'akkr', null, false);
    $pdrd_param = __paramInit('bool', 'pdrd', null, false);

    $period = array();
    if ($period_param === 'today') {
        $period[0] = date("Y-m-d 00:00:00", time());
        $period[1] = date("Y-m-d 23:59:59", time());
        $groupBy = 'day';
        $periodText = "�� �������";
    } elseif ($period_param === 'week') {
        $period[0] = date("Y-m-d 00:00:00", time() - (3600 * 24 * 7));
        $period[1] = date("Y-m-d 23:59:59", time());
        $groupBy = 'day';
        $periodText = "�� ��������� ������";
    } elseif ($period_param === 'month') {
        $period[0] = date("Y-m-d 00:00:00", time() - (3600 * 24 * 30));
        $period[1] = date("Y-m-d 23:59:59", time());
        $groupBy = 'day';
        $periodText = "�� ��������� �����";
    } elseif ($period_param === 'year') {
        $period[0] = date("Y-m-d 00:00:00", time() - (3600 * 24 * 365));
        $period[1] = date("Y-m-d 23:59:59", time());
        $groupBy = 'month';
        $periodText = "�� ��������� ��� (���������� �� �������)";
    } elseif ($period_param === 'alltime') {
        $groupBy = 'year';
        $periodText = "�� ��� ����� (���������� �� �����)";
    } elseif ($period_param === 'custom') {
        $from = explode('.', $custom_period_from);
        $to = explode('.', $custom_period_to);
        $fromTime = mktime(0, 0, 0, $from[1], $from[0], $from[2]);
        $toTime = mktime(0, 0, 0, $to[1], $to[0], $to[2]);
        
        // ���� ������ ������� �� ������ ���� ����� ����� �������
        if ($fromTime > $toTime) {
            
            $tmpTime = $fromTime;
            $fromTime = $toTime;
            $toTime = $tmpTime;
            
            $custom_period_tmp = $custom_period_from;
            $custom_period_from = $custom_period_to;
            $custom_period_to = $custom_period_tmp;
            
        }
            
        $period[0] = date("Y-m-d 00:00:00", $fromTime);
        $period[1] = date("Y-m-d 23:59:59", $toTime);
        $groupBy = 'day';
        $periodText = "� $custom_period_from �� $custom_period_to";
    }
    
    
    $sbr_meta = new sbr_meta();
    $sbr_data = $sbr_meta->getSbrStats($period, $groupBy, $akkr_param, $pdrd_param);
    
    // �������������� ��� �������, ��� ��� ��� ��������� �������� ����� ���� ��������� ����
    // ������ � ���� �������� � ����������� �������
    $dates = array();
    foreach ($sbr_data as $type => $data) {
        foreach ($data as $date => $values) {
            if (!$dates[$date]) {
                if ($groupBy === 'day') {
                    $dates[$date] = substr($date, 6, 2) . '.' . substr($date, 4, 2);
                } elseif ($groupBy === 'month') {
                    $dates[$date] = substr($date, 4, 2) . '.' . substr($date, 0, 4);
                } elseif ($groupBy === 'year') {
                    $dates[$date] = substr($date, 0, 4);
                }
            }
        }
    }
    ksort($dates);
    
    // �������� ��������
    // ���� 'name'  - �������� �������
    // ���� 'index' - ���� �� ������� ����������� �� ������� sbr_meta::getStatsDaysLC
    // ���� 'value' - ����� ������ ������������
    // ���� 'unit'  - ������� ���������
    // ���� 'descr' - ��������
    // ���� 'color' - ���� �������
    // ���� 'type'  - 'normal' - ������� ������, 'ps' - ��������� ������ �� ������ ��������� �������, 'avg_perc' - ������� ������� �� ������
    $sbr_data_types = array (
        array('name' => '���������� ���������� ���',                            'index' => 1, 'value' => 'cnt', 'unit' => '���', 'descr' => '����������',   'color' => '#89A54E', 'type' => 'normal'),
        array('name' => '����� ���������� �������� ���',                        'index' => 2, 'value' => 'cnt', 'unit' => '���', 'descr' => '����������',   'color' => '#89A54E', 'type' => 'normal'),
        array('name' => '���������� �������� ��� ��� ������ ��',                'index' => 2, 'value' => 'cnt', 'unit' => '���', 'descr' => '����������',   'color' => '#89A54E', 'type' => 'ps'),
        array('name' => '����� ���������� �������� ���',                        'index' => 3, 'value' => 'cnt', 'unit' => '���', 'descr' => '����������',   'color' => '#89A54E', 'type' => 'normal'),
        array('name' => '���������� �������� ��� ��� ������ ��',                'index' => 3, 'value' => 'cnt', 'unit' => '���', 'descr' => '����������',   'color' => '#89A54E', 'type' => 'ps'),
        array('name' => '���������� ��������� �� ��� (������� ������������)',   'index' => 4, 'value' => 'cnt', 'unit' => '���', 'descr' => '����������',   'color' => '#89A54E', 'type' => 'normal'),
        array('name' => '����� ��������, �����',                                'index' => 2, 'value' => 'sum', 'unit' => '���', 'descr' => '�����',        'color' => '#4572A7', 'type' => 'normal'),
        array('name' => '����� �������� ��� ������ ��',                         'index' => 2, 'value' => 'sum', 'unit' => '���', 'descr' => '�����',        'color' => '#4572A7', 'type' => 'ps'),
        array('name' => '������� ������ �������� ������',                       'index' => 2, 'value' => 'avg', 'unit' => '���', 'descr' => '�����',        'color' => '#4572A7', 'type' => 'normal'),
        array('name' => '����� ��������, ����� (����������� ����������)',       'index' => 3, 'value' => 'sum', 'unit' => '���', 'descr' => '�����',        'color' => '#4572A7', 'type' => 'normal'),
        array('name' => '����� �������� ��� ������ �� (����������� ����������)','index' => 3, 'value' => 'sum', 'unit' => '���', 'descr' => '�����',        'color' => '#4572A7', 'type' => 'ps'),
        array('name' => '����� ��������� (������� ������������)',               'index' => 4, 'value' => 'sum', 'unit' => '���', 'descr' => '�����',        'color' => '#4572A7', 'type' => 'normal'),
        array('name' => '������� �� �������������',                             'index' => 5, 'value' => 'sum', 'unit' => '���', 'descr' => '�����',        'color' => '#4572A7', 'type' => 'normal'),
        array('name' => '������� �� ������������',                              'index' => 6, 'value' => 'sum', 'unit' => '���', 'descr' => '�����',        'color' => '#4572A7', 'type' => 'normal'),
        array('name' => '������� �������',                                      'index' => 5, 'value' => 'avg', 'unit' => '���', 'descr' => 'C����',        'color' => '#4572A7', 'type' => 'avg_perc'),
    );
    
    // �������� ����� � �������
    $sbr_table_types = array (
        array('type' => 1, 'value' => 'cnt',        'name' => '���-�� ����������'),
        array('type' => 2, 'value' => 'cnt',        'name' => '���-�� ��������, �����'),
        array('type' => 2, 'value' => 'cnt_wmr',    'name' => '���-�� ��������, WebMoney'),
        array('type' => 2, 'value' => 'cnt_yd',     'name' => '���-�� ��������, �.������'),
        array('type' => 2, 'value' => 'cnt_card',   'name' => '���-�� ��������, �������'),
        array('type' => 2, 'value' => 'cnt_bank',   'name' => '���-�� ��������, ����'),
        array('type' => 2, 'value' => 'cnt_ww',     'name' => '���-�� ��������, ���-�����.'),
        array('type' => 2, 'value' => 'cnt_fm',     'name' => '���-�� ��������, ���.'),
        array('type' => 3, 'value' => 'cnt',        'name' => '���-�� ��������, �����'),
        array('type' => 3, 'value' => 'cnt_wmr',    'name' => '���-�� ��������, WebMoney'),
        array('type' => 3, 'value' => 'cnt_yd',     'name' => '���-�� ��������, �.������'),
        array('type' => 3, 'value' => 'cnt_card',   'name' => '���-�� ��������, �������'),
        array('type' => 3, 'value' => 'cnt_bank',   'name' => '���-�� ��������, ����'),
        array('type' => 3, 'value' => 'cnt_ww',     'name' => '���-�� ��������, ���-�����.'),
        array('type' => 3, 'value' => 'cnt_fm',     'name' => '���-�� ��������, ���.'),
        array('type' => 4, 'value' => 'cnt',        'name' => '���-�� ���������'),
        array('type' => 2, 'value' => 'sum',        'name' => '����� ��������, �����'),
        array('type' => 2, 'value' => 'sum_wmr',    'name' => '����� ��������, WebMoney'),
        array('type' => 2, 'value' => 'sum_yd',     'name' => '����� ��������, �.������'),
        array('type' => 2, 'value' => 'sum_card',   'name' => '����� ��������, �������'),
        array('type' => 2, 'value' => 'sum_bank',   'name' => '����� ��������, ����'),
        array('type' => 2, 'value' => 'sum_ww',     'name' => '����� ��������, ���-�������'),
        array('type' => 2, 'value' => 'sum_fm',     'name' => '����� ��������, ���.'),
        array('type' => 3, 'value' => 'sum',        'name' => '����� ��������, �����'),
        array('type' => 3, 'value' => 'sum_wmr',    'name' => '����� ��������, WebMoney'),
        array('type' => 3, 'value' => 'sum_yd',     'name' => '����� ��������, �.������'),
        array('type' => 3, 'value' => 'sum_card',   'name' => '����� ��������, �������'),
        array('type' => 3, 'value' => 'sum_bank',   'name' => '����� ��������, ����'),
        array('type' => 3, 'value' => 'sum_ww',     'name' => '����� ��������, ���-�������'),
        array('type' => 3, 'value' => 'sum_fm',     'name' => '����� ��������, ���.'),
        array('type' => 4, 'value' => 'sum',        'name' => '����� ���������'),
        array('type' => 5, 'value' => 'sum',        'name' => '������� �� ���-���'),
        array('type' => 6, 'value' => 'sum',        'name' => '������� �� ���-���'),
    );
    
}

$content = "../content.php";


$inner_page = "inner_index.php";

$header = $rpath."header.php";
$footer = $rpath."footer.html";

$stretch_page = true;

include ($rpath."template.php");

?>
