<?
/**
 * ����� ������ � ����������� ����������� �������
 */
class settings
{
	/**
	 * ���������� ���������� ������
	 *
	 * @param string $module		������, �������� ����������� ����������		
	 * @param string $variable		����������, ������� ���� �����
	 * @return string
	 */

	function GetVariable( $module, $variable ) {
	    global $DB;
		$sql = "SELECT value FROM settings WHERE module = ? AND variable = ? LIMIT 1";
		
		return $DB->val( $sql, $module, $variable );
	}

	/**
	 * ������������� ���������� ������
	 *
	 * @param string $module		������, �������� ����������� ����������		
	 * @param string $variable		����������, ������� ���� ����������
	 * @param string $value			�������� ����������
	 * @return string
	 */

	function SetVariable( $module, $variable, $value ) {
	    global $DB;
		$sql = "UPDATE settings SET value = ? WHERE module = ? AND variable = ?";
		
		return $DB->query( $sql, $value, $module, $variable );
	}
        
        /**
         * ���������� ��� �������� ����������
         * 
         * @global type $DB
         * @param type $module
         * @param type $variable
         * @param type $value
         * @return type
         */
        function AddVariable( $module, $variable, $value ) {
	    global $DB;
            
            $is_exist = $DB->val('
                SELECT id 
                FROM settings 
                WHERE module = ? AND variable = ? LIMIT 1',
                $module, 
                $variable );

            if($is_exist > 0) return $DB->update('settings',array('value' => $value),"id = ?i", $is_exist);

            return $DB->insert('settings',array(
                'id' => (int)$DB->val('SELECT MAX(id) FROM settings') + 1,
                'module' => $module,
                'variable' => $variable,
                'value' => $value
            ));
	}
}
?>