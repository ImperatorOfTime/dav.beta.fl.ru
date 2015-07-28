/* 
 * ���������� ������� ��� ��������� �������� ������������ 
 */

(function(){
    window.DelAcc = {
        /*
         * ��������� ������� �� ������� "� ��������"
         * � ���������� �����
         */
        deleteAccount: function () {
            var checkbox = $('b-check1');
            if (checkbox.getProperty('checked')) {
                $('del_acc_form').submit();
            }
            return false;
        }
    }
    // ��� ��������� ��������� ��������, � ����� ��� �����
    function changeCheckbox () {
        var chk = $('b-check1');
        if (chk.get('checked')) {
            $('del_acc').removeClass('b-button_disabled');
        } else {
            $('del_acc').addClass('b-button_disabled');
        }
    }
    window.addEvent('domready', function(){
        if ( $('del_acc') ) {
            $('del_acc').addEvent('click', function(){
                DelAcc.deleteAccount();
                return false;
            });
            $('b-check1').addEvent('change', changeCheckbox);
            $('b-check1').addEvent('click', changeCheckbox);
        }
    })
})();
