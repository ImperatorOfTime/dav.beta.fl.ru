// ������ ��� ������� ���� ����� �� �������
(function(){
window.addEvent('domready', function(){
    var
        $complainTypes = $('complain_types'),
        $complainTypesForm = $('complain_types_form'),
        $complainTypeTemplate = $('complain_type_template').getElement('div'),
        $addComplainType = $('add_complain_type'),
        $saveComplainTypes = $('save_complain_types'),
        $noComplains = $('no_complains');


    $addComplainType.addEvent('click', addComplainType);
    $complainTypes.addEvent('click', delComplainType);
    $complainTypes.addEvent('change', checkboxChanged);
    $saveComplainTypes.addEvent('click', saveComplainTypes);

    // �������� ����� ������ ��� ����� ���� ������
    function addComplainType () {
        $complainTypeTemplate.clone().inject($complainTypes, 'bottom');
        $noComplains.setStyle('display', 'none');
    }

    // ������� ��� ������
    function delComplainType (event) {
        // ��������� ��� ������ ������ ������ �������
        if (!event.target.hasClass('del_complain_type')) {
            return;
        }
        // �� �������, � �������� � �������� ��� ���������
        var $parent = event.target.getParent('div.complain-type');
        $parent.setStyle('display', 'none');
        $parent.addClass('complain-type-deleted');
        $parent.getElement('input[name="del[]"]').set('value', 1);
        
        // ���� ������� ��������� ������, �� ������� �������
        if ($complainTypes.getElements('div.complain-type:not(.complain-type-deleted)').length === 0) {
            $noComplains.setStyle('display', '');
        }
    }
    
    // �������� value ����� � ���������� checkbox'��
    // ���� ������� �������, �� value = 1, ����� 0
    // ��� �����, ��������� ���� ������� �� �������, �� �� �� ���������� �� ������
    function checkboxChanged (event) {
        var $checkbox = event.target;
        if ($checkbox.get('type') !== 'checkbox') {
            return;
        }
        
        var hiddenInput = $checkbox.getNext('input[type="hidden"]');
        hiddenInput && hiddenInput.set('value', +$checkbox.get('checked'));
    }
    
    // ��������� ��� ���������
    function saveComplainTypes () {
        $complainTypesForm.submit();
    }
    
    

});
}());