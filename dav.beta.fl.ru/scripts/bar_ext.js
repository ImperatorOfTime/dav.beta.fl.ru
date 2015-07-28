/**
 * ���������� ����������� ����� �����
 * @todo: ��������� Mootools
 */
function Bar_Ext()
{
    Bar_Ext=this; // ie ������� ��� �����, ���� �� �����.

    
    //--------------------------------------------------------------------------
    
    //��������� �������������
    this.init = function() 
    {
        this.toggler({
            'antiuser':{
                //��� ����� �� ������ ������������
                'click':function(el){
                    var dd = el.getParent('[data-dropdown-descriptor]');
                    var is_open = dd.hasClass('b-opened-dropdown');
                    if(typeof toggleDropdown !== "undefined" && dd && is_open)
                    {
                       toggleDropdown(dd.get('data-dropdown-identificator'));     
                    }
                },
                //��� ������� � ���������� �������������
                'on':function(el){
                    el.show();
                    
                    var winSize = $(window).getSize();
                    if(winSize.x <= 800)
                    {
                        var is_auth = (el.get('data-dropdown-descriptor') == 'identification');
                        if(typeof toggleDropdown !== "undefined" && is_auth)
                        {
                            toggleDropdown(el.get('data-dropdown-identificator')); 
                        }  
                    }
                },
                //��� ������ � ���������� ��������������
                'off':function(el){
                    el.hide();
                }
            }
        });
        
        
        this.popuper();
        this.showOrHide();
        this.scroller();
        
        this.onLoginDataSaver();
    };
    

    //--------------------------------------------------------------------------
    
    /**
     * ������������� ������� ���������
     * 
     * @param object params
     * @returns boolean
     */
    this.toggler = function(params)
    {
        var togglers = $$('[data-toggle-action]');
        if(!togglers) return false;
        
        togglers.addEvent('click',function(){
            var id = this.get('data-toggle-action');
            if(typeof params[id] === "undefined") return false;
            params[id].click(this);
            
            var toggles = $$('[data-' + id + ']');
            if(toggles) toggles.each(function(e){
                var tg = e.get('data-' + id);
                if(tg == 'true') params[id].on(e);
                else params[id].off(e);
                e.set('data-' + id, (tg == 'true')?false:true); 
            });
            return false;
        });
        
        return true;
    };
    
    
    //--------------------------------------------------------------------------
    
    /**
     * ����������� �� ��������������� ����������� �������
     * 
     * @param obj form
     * @param string anti_login
     * @returns Boolean
     */
    this.antiuserSubmit = function(form, anti_login)
    {
        if(!form) return true;
        var qu = form.toQueryString();
        var _action = 'switch';
        var login = form.getElement('input[name=a_login]').get('value');
        if(login != anti_login) _action = 'change_au';
        
        form.getElements('.b-text-field input').addEvent('focus',function(){
            this.getParent().removeClass('b-text-field-error');
        });
        
        new Request.JSON({
	url: form.get('action'),
	data: qu + "&action=" + _action,
	onSuccess: function(resp){
            if(resp) 
            {
                if($chk(resp.redir)) document.location.href = resp.redir;
                else if(resp.success) document.location.reload();
                else resp=null;
            }
            
            if(!resp)
            {
                form.getElements('.b-text-field')
                    .addClass('b-text-field-error');    
            }
            
        }}).post();
    
        return false;
    };
    
    
    //--------------------------------------------------------------------------

    
    /**
     * �������������
     */
    this.logout = function()
    {
        var form = new Element('form', {'action':'/logout/','method':'post'});
        var action = new Element('input', {'type':'hidden', 'value':'logout','name':'action'});
        var token = new Element('input', {'type':'hidden','value':_TOKEN_KEY,'name':'u_token_key'});  
        
        form.adopt(action,token);
        form.setStyle('display','none').inject($(document.body), 'bottom');
        form.submit();
    };
    
    
    
    //--------------------------------------------------------------------------
    
    
    /**
     * ��������� ������ � ������������ ������� ������.
     * ��� ����� �� �������� ������� ���� ������� ����� � �� ������ ������� ��� ������
     * ��������� ��������� data-popu="popup_name" - ��� popup_name ��� id="popup_name"
     * ���� ������. �� ������ � ������ �� ������� ����� ������� � ���������� �����-���� ��������
     * ��������� data-popup-ok="true" � �� onclick ��� href="javascript: ..." ������ ��������.
     * 
     * ���� ����� ����������� ������� �� ������ �� ������� ����������� ����� �� �� ������ � ������
     * ����������� �������� data-popup-copy-attr="href" (href - ��������)
     * 
     * ���� ����� �������� ����� �� ������ �� �� ������ �������� �������
     * �������� POST ������� � ���������� �� ����� ������ �� data-popup
     * � �� ��������� ������ 1 �� ��� � data-url ������. ��� ����� ����
     * ������������ ��� �������� ������ ����� ����� ��������� ���� ��� �� ����.
     * 
     * 
     * @returns Boolean
     */
    this.popuper = function()
    {
        var popups = $$('[data-popup]');
        if(!popups) return false;  

        popups.each(function(link){
            
            Bar_Ext.bindPopup(link);
        });

        return true;
    };
    
    /**
     * ����������� ����� � ������
     * 
     * @param {type} link
     * @returns Boolean
     */
    this.bindPopup = function(link) {
        var id = link.get('data-popup');
        var popup = $(id);
        
        link.removeEvents();
        
        if(popup)
        {
            link.addEvent('click', function(event){
                popup.removeClass('b-shadow_hide').fireEvent('showpopup', this);
                var okBtns = popup.getElements('[data-popup-ok]');
                if(!okBtns) return false;
                okBtns.each(function(btn){
                    var attr = btn.get('data-popup-copy-attr');
                    if(!attr) return;
                    btn.set(attr,link.get(attr));
                });
                okBtns.addEvent('click',function(){popup.addClass('b-shadow_hide');});
                return false;
            });

            var cross = popup.getElement('.b-shadow__icon_close');
            if(cross) cross.addEvent('click', function(){popup.addClass('b-shadow_hide');});

            if(!popup.hasClass('b-shadow_hide')) link.fireEvent('click');
            popup.store('called_link',link);
        }
        else
        {
            link.addEvent('click',function(){
                var url = this.get('data-url');
                if(!url) return false;
                Bar_Ext.sendHideForm(url,id);
                return false;
            });        
        }
    };
    
    
    
    
    /**
     * �� ����� �� ������ ������������ �������� � ��������, ��������� � ��������
     * data-scrollto. ��� ��������� ����������� ������ ����� � �������. ���� ����
     * �� ������� �� ��������, ��������� ������� �� ������, ���������� � ���������
     * data-url
     * @todo ������� ������ �� popuper, � ����� �������������� � ������ ������ �����,
     * �� ������ �� ��� � ������ �������� �����? - ��������, ������ ����� ����� ���������
     * ������� ������ ����� ���� �������� �� ������� (commons ��� helpers ..) � �������� � class mootools
     */
    this.scroller = function() {
        var scrolls = $$('[data-scrollto]');
        if(!scrolls) return false;  

        scrolls.each(function(link){
            var id = link.get('data-scrollto');
            var target = $(id);
            
            if(target)
            {
                link.addEvent('click', function(){
                    var myFx = new Fx.Scroll(window, {
                        duration: 300,
                        wait: false,
                        offset: {
                            x: 0,
                            y: -80
                        }
                    }).toElement('form-block');
                    
                });
            
            }
            else
            {
                link.addEvent('click',function(){
                    var url = this.get('data-url');
                    if(!url) return;
                    Bar_Ext.sendHideForm(url,id);
                    return false;
                });        
            }
        });

        return true;
    };
    
    /**
     * ���������� ������� ����� POST �������� 
     * �� ��������� url c name ��������� ���������.
     * 
     * @param {type} url
     * @param {type} name
     * @returns {undefined}
     */
    this.sendHideForm = function(url, name)
    {
        var form = new Element('form', {'action':url,'method':'post'});
        var idx = new Element('input', {'type':'hidden','value':1,'name':name});
        var token = new Element('input', {'type':'hidden','value':_TOKEN_KEY,'name':'u_token_key'});
        
        form.adopt(idx,token);
        form.setStyle('display','none').inject($(document.body), 'bottom');
        form.submit();
    };
    
    
    //--------------------------------------------------------------------------
    
    
    
    /**
     * ������ ��� ���������/���������� ������� �� ����� �� �����-���� ��������
     * 
     * @returns {Boolean}
     */
    this.showOrHide = function()
    {
        var showAct = $$('[data-show-class]');
        var hideAct = $$('[data-hide-class]');
        if(!showAct && !hideAct) return false;
        
        showAct.addEvent('click', function(){
            var cls = this.get('data-show-class');
            var display = this.get('data-show-display');
            if (!display) display = 'block';
            if(cls) $$(cls).show(display);
        });
        
        hideAct.addEvent('click', function(){
            var cls = this.get('data-hide-class');
            if(cls) $$(cls).hide();
        });
        
        return true;
    };
    
    
    this.onLoginDataSaver = function()
    {
        var login_form = $('lfrm');
        if (login_form) {
            login_form.addEvent('submit', function(){
                var guestForms = $$('.form_guest');
                if (guestForms.length) {
                    var formQuerySting = guestForms[0].toQueryString();

                    new Element('input', {
                        'type': 'hidden',
                        'name': 'guest_query',
                        'value': formQuerySting
                    }).inject(this);
                }
            });
        }
    };
    
    
    //--------------------------------------------------------------------------
    
    
    
    //������ �������������
    this.init();    
}

window.addEvent('domready', function() {
    new Bar_Ext();
});