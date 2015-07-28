/**
 * ������� ����� ��������� ������� ����� ����.
 * @todo: ��������� ������ ��� ���������� ������ �����������.
 * 
 * @type Class
 */
var CPopup = new Class({
 
    //Implements: Animal,
    //Extends: Animal,
    
    popup_name: '',
    popup: null,
    form: null,
    
    wait_screen: null,
    error_screen: null,
    success_screen: null,
    
    initialize: function(p)
    {
        if (!p) {
            return false;
        }
        
        var _this = this;
        this.popup = p;
        this.popup_name = p.get('id');
        this.form = p.getElement('form');
        this.wait_screen = p.getElement('[data-popup-wait-screen]');
        this.error_screen = p.getElement('[data-popup-error-screen]');
        this.success_screen = p.getElement('[data-popup-success-screen]');

        var close = p.getElements('[data-popup-close]');
        if (close) {
            close.addEvent('click', function() {
                _this.close_popup();
                return false;
            });
        }     
        
        p.addEvent('showpopup', function(link) {
            _this.onShowPopup(link);
        });        
        
        
        return true;
    },
      
      
    //--------------------------------------------------------------------------
      
      
    onShowPopup: function(link)
    {
        window.fireEvent('resize');
    },  
      
      
    //--------------------------------------------------------------------------
    
    /**
     * �������� ������� ��������
     */
    show_wait: function(msg)
    {
        if (!this.wait_screen) {
            return false;
        }
        
        this.hide_error();
        
        if (msg != 'true') { 
            this.wait_screen
                .getElement('[data-popup-wait-msg]')
                .set('html',msg);
        }
        
        this.wait_screen.getParent().addClass('b-layout_waiting');
        this.wait_screen.removeClass('b-layout_hide');
        
        return true;
    },        
    
    //--------------------------------------------------------------------------
    
    /**
     * ������ ������� ��������
     */
    hide_wait: function()
    {
        if (!this.wait_screen) {
            return false;
        }
        
        this.wait_screen.getParent().removeClass('b-layout_waiting');
        this.wait_screen.addClass('b-layout_hide');
        
        return true;
    },
    
    //--------------------------------------------------------------------------
    
    /**
     * ������� �����
     */
    close_popup: function()
    {
        this.hide_wait();
        this.hide_success();
        this.hide_error();
        this.popup.addClass('b-shadow_hide');
        
        return true;
    },
    
    //--------------------------------------------------------------------------
    
    /**
     * �������� ��������� �� ������
     */
    show_error: function(msg)
    {
        if (!this.error_screen) { 
            return false;
        }
        
        this.hide_wait();
        
        if (msg.length) {
            this.error_screen
                .getElement('[data-popup-error-msg]')
                .set('html', msg);
        }
        
        this.error_screen.removeClass('b-layout_hide');
        
        return true;
    },
    
    //--------------------------------------------------------------------------
    
    /**
     * ������ ��������� �� ������
     */
    hide_error: function()
    {
        if (!this.error_screen) {
            return false;
        }
        
        this.error_screen.addClass('b-layout_hide');
        
        return true;
    },
    
    //--------------------------------------------------------------------------
    
    /**
     * �������� ��������� �� �������� ��������
     */
    show_success: function(msg)
    {
        if (!this.success_screen) {
            return false;
        }
        
        this.hide_wait();
        
        if (msg.length) {
            this.success_screen
                .getElement('[data-popup-success-msg]')
                .set('html',msg);
        }
        
        this.success_screen.getParent().addClass('b-layout_waiting');
        this.success_screen.removeClass('b-layout_hide');
        
        return true;
    },
    
    //--------------------------------------------------------------------------
    
    /**
     * ������ ��������� �� �������� ��������
     */
    hide_success: function()
    {
        if (!this.success_screen) {
            return false;
        }
        
        this.success_screen.getParent().removeClass('b-layout_waiting');
        this.success_screen.addClass('b-layout_hide');
        
        return true;
    },
    
    //--------------------------------------------------------------------------
    
    isWait: function()
    {
        if (!this.wait_screen) {
            return false;
        }
        
        return !this.wait_screen.hasClass('b-layout_hide');
    }
    

});

//------------------------------------------------------------------------------

/**
 * ������� ������������� �������
 * 
 * @type Class
 */
var PopupsFactory = new Class({
    
    initialize: function()
    {
        var popups = $$('[data-popup-window]');
        
        if (!popups) {
            return false;
        }
        
        window.popups = {};
        popups.each(function(p){
            var name = p.get('id');
            if (typeof window[name] !== "undefined") {
                window.popups[name] = new window[name](p);
            }
        });
    },
     
    setPopup: function(name)
    {
        var popup = $(name);
        
        if (!popup) {
            return false;
        }
        
        if (typeof window[name] !== "undefined") {
            window.popups[name] = new window[name](popup);
        }
    },        
            
    getPopup: function(name)
    {
        return (typeof window.popups[name] !== "undefined")? window.popups[name]:false;
    }
});

//------------------------------------------------------------------------------

window.addEvent('domready', function() {
    window.popups_factory = new PopupsFactory();
});