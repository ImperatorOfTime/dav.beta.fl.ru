var stop_words = {
    regexTest: function() {
        if ( !this.regexEmpty() ) {
            return false;
        }
        
        if ( $('test').get('value') == '' ) {
            alert('���� �������� ����� �� ������ ���� ������');
            return false;
        }
        
        $('action').set('value', 'test');
        
        $('form_stop_words').submit();
        return true;
    },
    
    regexSubmit: function() {
        if ( !this.regexEmpty() ) {
            return false;
        }
        
        $('action').set('value', 'update');
        
        $('form_stop_words').submit();
        return true;
    },
    
    regexEmpty: function() {
        if ( $('regex').get('value') == '' ) {
            if ( !confirm('�������� ������������ ��������� �������� � ����������� ��������� �� �� ������������� � ������������ �� ��������� ��� �������������.\n�� ������������� ������ ������� ��� ����������� ���������?') ) {
                return false;
            }
        }
        
        return true;
    },

    wordsSubmit: function() {
        if ( $('words').get('value') == '' ) {
            if ( !confirm('�������� �������������� ���� �������� � ������������ �� ��������� ��� �������������.\n�� ������������� ������ ������� ��� �������������� �����?') ) {
                return false;
            }
        }
        
        $('form_stop_words').submit();
        return true;
    },

    cancel: function(site) {
        window.location = '/siteadmin/stop_words/?site='+site;
    }
};