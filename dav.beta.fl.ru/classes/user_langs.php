<?php
/**
* ����� ��� ������ �� ������������ ������ 
**/
class user_langs {
    /**
     * �������� ����� �� �����������
     * @return  mixed bool|array
     */
    static public function getLanguages() {
        global $DB;
        $rows = $DB->cache(3600)->rows("SELECT id, name FROM languages ORDER BY name");
        return $rows;
    }
    /**
     * ��������� ����� ������������
     * @paramv uint  $uid        - ������������� ������������
     * @paramv array $user_langs - ������ ������������� ��������, ��� ������ �������:
     *  Array ( 'id'=> uint      - ������������� ����� �� ������� languages,
     *          'quality'=> uint - ������� ������ ����� ( 1 - ���������, 2 - �������, 3 - ������������, 4 - ������ )
     *  )
     */
    static public function updateUserLangs($uid, $user_langs) {
        $uid = (int)$uid;
        if ( $uid ) {
            global $DB;
            $DB->query("DELETE FROM user_langs WHERE uid = {$uid}");
            $values = array();
            foreach ( $user_langs as $lang ) {
                $values[] = "({$lang['id']}, {$uid}, {$lang['quality']})";
            }
            if ( count($values) ) {
                $values = join(",", $values);
                $DB->query("INSERT INTO user_langs (lang_id, uid, quality) VALUES $values");
            }
        }
    }
}
?>
