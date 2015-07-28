<?php
require_once(ABS_PATH.'/classes/hh.php');

/**
 * API ��� ������ � �������� HeadHunter.
 */
class externalApi_Hh extends externalApi {

    // @todo php 5.3 ������� const
    protected $API_NAMESPACE      = 'http://www.free-lance.ru/external/api/hh';
    protected $API_DEFAULT_PREFIX = 'hh';

    protected $_methodsCfg = array (
        'getFrlCount' => array ()
    );

    protected function _authDenied($user) { return 0; }
    protected function _methodsDenied() { return 0; }


    /**
     * ���������� ���������� ���������� ����������� �� �������, ����������� � http://hh.ru/employer/resumesSearch.do
     * � ����� ���� �� ����������.
     *
     * @param array $filter
     *   <hh:getFrlCount>
     *     <v-p> <!-- ������, ������ -->
     *       <v k="kword">����,����������������, ���, Java Script</v> <!-- �������� �����, ����� ������� -->
     *       <v k="regions">1001,456</v> <!-- ��. ��-��������. ���� ������ ��������, �� ���������������, ��� ������ ���� �������� ��� ���� -->
     *       <v k="fields">1,2,3</v> <!-- ��. ��-������������. ���� �������, �� ���������� �������� ��� �������������, ����������� � ���� ������� -->
     *       <v k="specs">1,2,3</v> <!-- ��. ��������� ��-������������� -->
     *       <v k="cost">          <!-- �������� �������� -->
     *         <v k="from">12</v>       <!-- �� -->
     *         <v k="to">999</v>        <!-- �� -->
     *         <v k="currency">USD</v>  <!-- ������ -->
     *       </v>
     *       <v k="wo_cost">1</v> <!-- �������� �� � ����� �����, �� ��������� �� -->
     *       <v k="age">          <!-- ������� -->
     *         <v k="from">12</v>  <!-- �� -->
     *         <v k="to">999</v>   <!-- �� -->
     *       </v>
     *       <v k="wo_age">1</v>  <!-- �������� �� � ����� �����, �� ��������� ������� -->
     *     </v-p>
     *   </hh:getFrlCount>
     *
     * @return array 
     */
    protected function x____getFrlCount($args)
    {
        list($filter) = $args;
        require_once(ABS_PATH.'/classes/freelancer.php');
        require_once(ABS_PATH.'/classes/memBuff.php');

        if(!is_array($filter))
            $filter = array();
        
        $hh      = new hh();
        $memBuff = new memBuff();

        $memkey = md5($hh->packFilter($filter));
        if($mret = $memBuff->get($memkey)) {
            return $mret;
        }

        if($filter['kword']) {
            require_once(ABS_PATH.'/classes/kwords.php');
            $kwords = new kwords();
            $filter['orig_kwords'] = $filter['kword'];
            $filter['kword'] = $kwords->getKeys($filter['kword']);
        }
        if($filter['regions']) {
            list($filter['country'], $filter['city']) = $hh->getCCByHHRegions($filter['regions']);
            unset($filter['regions']);
        }

        $pp1 = $pp2 = array();
        if($filter['fields']) {
            if($ppx = $hh->getProfessionsByHHFields($filter['fields']))
                $pp1 = $ppx;
            unset($filter['fields']);
        }
        if($filter['specs']) {
            if($ppx = $hh->getProfessionsByHHSpecs($filter['specs']))
                $pp2 = $ppx;
            unset($filter['specs']);
        }
        if($pp1 || $pp2)
            $filter['prof'][1] = $pp1 + $pp2;

        if($filter['cost']) {
            $filter['cost']['type_date'] = 1;
            $filter['cost']['cost_from'] = $filter['cost']['from'];
            $filter['cost']['cost_to'] = $filter['cost']['to'];

            if( ($filter['cost']['cost_type'] = hh::$hh_currency2ex[strtoupper($filter['cost']['currency'])]) < 0 ) {
                $hhc = $hh->getHHCurrency($filter['cost']['currency']);
                $filter['cost']['cost_type'] = freelancer::RUR;
                $filter['cost']['cost_from'] /= $hhc['rate'];
                $filter['cost']['cost_to'] /= $hhc['rate'];
            }
            $filter['cost'] = array($filter['cost']);
        }
        if(isset($filter['wo_cost']))
            $filter['wo_cost'] = $this->ex2pg($filter['wo_cost'], EXTERNAL_DT_BOOL);

        if($filter['age']) {
            $filter['age'][0] = (int)$filter['age']['from'];
            $filter['age'][1] = (int)$filter['age']['to'];
        }
        if(isset($filter['wo_age']))
            $filter['wo_age'] = $this->ex2pg($filter['wo_age'], EXTERNAL_DT_BOOL);

        if($count = freelancer::getFrlCount($filter))
            $link = $hh->saveFilter($filter);

        $ret = array('count'=>$count, 'link'=>$GLOBALS['host'].'/freelancers/?hhf='.$link);
        $memBuff->set($memkey, $ret, 1800);
        return $ret;
    }
}
