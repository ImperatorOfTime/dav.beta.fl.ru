<? 
$reviewer = new StdClass();
$reviewer->uname = @$_POST["uname"];
$reviewer->usurname = @$_POST["usurname"];
$str = "��� �������� �� ����������";
if ( strlen($reviewer->uname) > 0 && strlen($reviewer->usurname) > 0 ) {
            //��� �������� �� <���_�������_�_������������_������>
            $consonants = "��������������������";
            $vowels = "���������";
            $vLetter = $letter = $reviewer->uname[ strlen($reviewer->uname) - 1 ];
            $uname = $reviewer->uname;
            if ( $letter == "�" ) {
                $uname[ strlen($uname) - 1 ] = '�';
            } elseif ( $letter == "�" ) {
                $uname[ strlen($uname) - 1 ] = '�';
            } elseif ( strpos($consonants, $letter ) !== false ) {
                $uname .= '�';
            } elseif ( strpos($vowels, $letter ) !== false ) {
                if ( $letter == '�' ) {
                    $prev = $reviewer->uname[ strlen($reviewer->uname) - 2 ];
                    if ( strpos($consonants, $prev) !== false) {
                        if ( $prev != '�' && $prev != '�' ) {
                            $uname[ strlen($uname) - 1 ] = '�';
                        } else {
                            $uname[ strlen($uname) - 1 ] = '�';
                        }
                    } else {
                        $uname[ strlen($uname) - 1 ] = '�';
                    }
                } else {
                    $uname[ strlen($uname) - 1 ] = '�';
                }
            }
            
            $usurname = $reviewer->usurname;
            $letter = $reviewer->usurname[ strlen($reviewer->usurname) - 1 ];
            if ( $letter == "�" && $reviewer->usurname[ strlen($reviewer->usurname) - 2 ] == "�" ) {
                $usurname[ strlen($usurname) - 2 ] = '�';
                $usurname[ strlen($usurname) - 1 ] = '�';
                $usurname .= '�';
            } elseif ( $letter == "�" && $reviewer->usurname[ strlen($reviewer->usurname) - 2 ] == "�" ) {
                $usurname[ strlen($usurname) - 2 ] = '�';
                $usurname[ strlen($usurname) - 1 ] = '�';
            } elseif ( strpos($consonants, $letter ) !== false && strpos($vowels, $vLetter ) === false ) {
                $usurname .= '�';
            } elseif ( strpos($vowels, $letter ) !== false ) {
                if ( $letter != '�' ) {$prev = $reviewer->uname[ strlen($reviewer->uname) - 2 ];
                    if ( $letter == '�' ) {
                        $usurname[ strlen($usurname) - 1 ] = '�';
                        $usurname .= '�';
                    } elseif ( $letter == '�' && strpos($vowels, $vLetter ) !== false ) {
                        ;
                    } else {
                        $usurname[ strlen($usurname) - 1 ] = '�';
                    }
                }
            }
            
            $str = "��� �������� �� $uname $usurname";
}
?>
<form method="POST">
Uname: <input type="text" value="<?=$reviewer->uname?>" name="uname"/><br>
USurname: <input type="text" value="<?=$reviewer->usurname?>" name="usurname"/><br>
<input type="submit" value="Send" />
</form>
<div style="color:red"><?=$str?></div>