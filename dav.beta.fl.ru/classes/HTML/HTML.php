<?php 

/**
 * ����� ��� ����������� ��������. ����� �������.
 *
 */
class HTML{
    
    
    /**
     * ����� ������������ ��������� �� ������ (������� ����������� + �����)
     *
     * @param string $error		����� ��� ��������� �� ������
     * @return string			html-��� ��������� �� ������
     */
    public function error($error){
        if ($error) {
            $error_str = "<div class=\"errorBox\"><img src=\"/images/ico_error.gif\" alt=\"\" width=\"22\" height=\"18\" border=\"0\"> &nbsp;$error</div>";
        }
        return $error_str;
    }
    
    /**
     * ����� ������ ��� ��� ������������ ��� ����������
     *
     * @param string $role			������� ������ � ����� �����
     * @param boolean $is_pro_test	���� ������� �������� ��� (true - ��������, false - ������)
     * @return string				html-��� ��� ������ ���
     */
    public function pro($role, $is_pro_test = false){
          if (is_emp($role)) {
              $img = 'icons/e-pro.png'; 
              $class =  'ac-epro';
              $href  = '/payed-emp/';
          } else {
              $img = ($is_pro_test) ? 'ico_pro_test.gif' : 'icons/f-pro.png';
              $class = 'ac-pro';
              $href  = '/payed/';
          }
          return "<a href=\"$href\" class=\"".$class."\"><img src=\"/images/".$img."\" alt=\"PRO\" /></a>";
    }
    
    /**
     * ����� ������ ���������� � ������������ � ���� "��� [�����]"
     *
     * @param string $role          ���������� � ������ �������
     * @param string $login         ����� ������������
     * @param string $username      ���
     * @param string @usersurname   �������
     * @return string			    html-��� 
     */
    public function userName($role, $login, $username, $usersurname){
        $class = (is_emp($role))? "empname11" : "frlname11";
        $out = "<font class=\"$class\">&nbsp;<a class=\"$class\" href=\"/users/$login\" title=\"$username $usersurname\">$username"
             . "$usersurname</a> [<a class=\"$class\" href=\"/users/$login\" title=\"$login\">$login</a>]</font>";
    }
    
}

