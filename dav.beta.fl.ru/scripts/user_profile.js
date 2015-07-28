
/**
 * ��������� ������� ������� ������������
 * 
 * @type Class
 */
var UserProfile = new Class({
    
    show_contacts_id: 'show_contacts',
    contacts_info_block_id: 'contacts_info_block',
    
    initialize: function()
    {
        //���������� �������� �� �������
        var contacts_info_block = $(this.contacts_info_block_id);
        var show_contacts = $(this.show_contacts_id);
        
        if (contacts_info_block && 
            show_contacts) {
            
            show_contacts.addEvent('click', function(){
                alert("�������� ���� ��������: ��� �������������� �������� (��� ������� \"���������� ������\") ������� ����� ��� ����� ��������������.");
                this.addClass('b-button_disabled');
                var login = this.get('data-login');
                var hash = this.get('data-hash');
                xajax_getContactsInfo(login, hash);
            });
        }
    }
});

window.addEvent('domready', function() {
    window.user_profile = new UserProfile();
});