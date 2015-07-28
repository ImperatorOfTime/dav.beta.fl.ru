/* 
 * ���� ������ ������� ����������� � ����������� ������� ���������� ������������ ��� ��� ��� �������
 * ����� ���������� ���� �� ������!
 */

/**
 * @todo: ����� ��� ����� �� /scripts/wizard/wizard.js
 * 
 * ������ type ��� ���� ������ (text/password)
 * @param string id - id �������� input ��� ����� ������
 */
function show_password(id) 
{
    // ������� ����������� �������� ���� id (�� ������ ���� �� �������� ��������� �������)
    var v = id ? $(id) : $('reg_password');
    if (!v) return;
    
    if (Browser.ie) {
        if(v.type == 'password') {
            var inputText = new Element('input', {'class'  :'b-combo__input-text', 
                                                'value'  : v.value, 
                                                'name'   : 'password',
                                                'size'   : '80',
                                                'type'   : 'text',
                                                'id'     : 'reg_password'});
            var parent = v.getParent();
            v.dispose();
            parent.adopt(inputText);
        } else {
            var inputText = new Element('input', {'class'  :'b-combo__input-text', 
                                                'value'  : v.value, 
                                                'name'   : 'password',
                                                'size'   : '80',
                                                'type'   : 'password',
                                                'id'     : 'reg_password'});
            var parent = v.getParent();
            v.dispose();
            parent.adopt(inputText);
        }
    } else {
        if(v.getProperty('type') == 'password') {
            v.setProperty('type', 'text');
        } else {
            v.setProperty('type', 'password');
        }
    }
}