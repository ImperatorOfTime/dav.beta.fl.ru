<? 
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
if(is_release()) {
    exit;
}
?>
<html>
<head>
    <title>����������� ����� ������ FF.RU</title>
</head>
<body>
    <table>
        <colgroup>
            <col width="200"/>
        </colgroup>
        <tr>
            <td>���</td>
            <td>������� ��� ��������</td>
        </tr>
        <tr>
            <td>���� ��������</td>
            <td>1950-01-01</td>
        </tr>
        <tr>
            <td>��������</td>
            <td>�������</td>
        </tr>
        <tr>
            <td>����� ��������</td>
            <td>1900 100001</td>
        </tr>
        <tr>
            <td>���� ������</td>
            <td>2000-01-01</td>
        </tr>
        <tr>
            <td>�����</td>
            <td>��� �. ������</td>
        </tr>
        <tr>
            <td>�������</td>
            <td>+79001000000</td>
        </tr>
    </table>
    <script>
        function ver_ff() {
            window.opener.location = '/income/ff.php?code=test';
            window.close();
        }
    </script>
    <input type="button" value="����������������" onclick="ver_ff()"/>
    
</body>
</html>