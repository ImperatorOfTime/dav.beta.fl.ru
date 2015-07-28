window.addEvent('domready', 
function() {
	
    // ������� � ���������
    $$('.b-bar__btn').getParent('li').addEvent('mouseover',function() {
        showTooltip(this);
    }).addEvent('mouseout',function() {
        hideTooltip(this);
    });
    // ������� � ��������� - ������ PRO
    $$('#b-bar__pro-btn').getParent('li').addEvent('mouseover',function() {
        showTooltip(this);
    }).addEvent('mouseout',function() {
        hideTooltip(this);
    });
    // ������� � ���������
    if ($('b-bar__drop')) {
        $('b-bar__drop').getElements('.b-shadow__txt').addEvent('mouseover',function() {
            showTooltip(this);
        }).addEvent('mouseout',function() {
            hideTooltip(this);
        });
    }
    
    /**
     * ���������� ������
     */
    function showTooltip (that) {
        var tooltip = that.getElement('.b-tooltip');
        // ���� ��������� ������, �� �� ������� ��
        if (!tooltip || !tooltip.getElement('.b-tooltip__txt') || tooltip.getElement('.b-tooltip__txt').get('text') === '') {
            return;
        }
        // ������� ������ ��� �������� �������
        clearTimeout(tooltip.retrieve('timer', null));
        // ��������� ��������
        var morph = new Fx.Morph(tooltip, {
            duration: 50
        });
        // ���� 100 �� �� ������ ��������
        if ((navigator.userAgent.toLowerCase().indexOf('msie') != -1)) {
            var timer = setTimeout(function() {
                tooltip.setStyle('opacity', '1').removeClass('b-tooltip_hide');
            }, 100);
        }
        else {
            var timer = setTimeout(function() {
                tooltip.morph({'opacity': [0, 1]}).removeClass('b-tooltip_hide')
            }, 50);
        }
        // ��������� ������ ��� �������� �������
        tooltip.store('timer', timer)
		
        that.getElements('.b-tooltip_small:not(.b-tooltip_marg_null)').setStyle('margin-left',-parseInt(that.getElement('.b-tooltip').getStyle('width'))/2-2+'px');
    }
    /**
     * �������� ������
     */
    function hideTooltip (that) {
        var tooltip = that.getElement('.b-tooltip');
        // ���� ������� ��� - �������
        if (!tooltip) {
            return;
        }
        // ������� ������ ��� �������� ������� (����� �� ������ �����������)
        clearTimeout(tooltip.retrieve('timer', null));
        
        // ������� �������� ������������
        // �����, ���� ����� ������ �������� ������ �� ��������
        var opacity = tooltip.getStyle('opacity');
        
        if ((navigator.userAgent.toLowerCase().indexOf('msie') != -1)) {
            var timer = setTimeout(function() {
                tooltip.setStyle('opacity', '0').addClass('b-tooltip_hide');
            }, 100);
        }
        else {
            // ��������� ��������
            var morph = new Fx.Morph(tooltip, {
                duration: 50,
                onComplete: function () {
                    tooltip.addClass('b-tooltip_hide');
                }
            });
            // ���� 200 ��
            var timer = setTimeout(function () {
                // �������� �������� � �������� �������� opacity
                tooltip.morph({'opacity': [opacity, 0]});
            }, 50 );
        }
        // ��������� ������
        tooltip.store('timer', timer);
    }
		
		/*$$('.b-bar__item').addEvent('click',function(){
			if(this.getElement('.b-bar__icon_arr')){
				this.getElement('.b-shadow').removeClass('b-shadow_hide');
				return false;
				}
			})*/
    
    // ������� ��� ie
    // ������������� ���� �������� opacity = 0
    if (Browser.ie && Browser.version <= 8) {
        $$('.b-bar__btn').each(function(that){
            var temp = that.getParent('li');
            if (!temp) return;
            temp = temp.getElement('.b-tooltip');
            if (!temp) return;
            temp.setStyle('opacity', '0');
        });
    }
    
    // ������� � ���������� ������
    $$('.b-bar__item').addEvent('mousedown', function(){
        this.addClass('b-bar__item_current');
    }).addEvent('mouseup', function(){
        this.removeClass('b-bar__item_current');
    }).addEvent('mouseout', function(){
        this.removeClass('b-bar__item_current');
    })
	
    if($('b-button-enter')) {
        $('b-button-enter').addEvent('click',function(){
            this.removeEvents('mouseleave');
            this.removeEvents('mousedown');
            this.removeEvents('mouseup');
            this.addClass('b-button_active');
            this.getNext('.b-shadow').removeClass('b-shadow_hide');
            if ($('login_form_overlay')) $('login_form_overlay').removeClass('b-shadow_hide');
            return false;
        });
    }
    
    $$('.b-bar-alternative-login').addEvent('click', function(){
        $$('.b-shadow__relogin').toggleClass('b-shadow_hide');
        if ($('asw_form_overlay')) $('asw_form_overlay').removeClass('b-shadow_hide');
        $('a_login').focus();
        return false;
    });
    
    // ��������� ������ �� �������
    if ($('asw_form_overlay')) {
        $('asw_form_overlay').addEvent('click', function(){ overlayHandler(this) })
    }
    if ($('login_form_overlay')) {
        $('login_form_overlay').addEvent('click', function(){ overlayHandler(this) })
    }
    function overlayHandler (that) {
        if(that.getParent('.b-bar__auth') != undefined) that.getParent('.b-bar__auth').getElement('.b-button ').removeClass('b-button_active');
        that.getPrevious('.b-shadow').addClass('b-shadow_hide');
        if ($('login_form_overlay')) $('login_form_overlay').addClass('b-shadow_hide');
        if ($('asw_form_overlay')) $('asw_form_overlay').addClass('b-shadow_hide');
    }
    
	$$('.b-shadow__icon_close').addEvent('click',function(){
		if(this.getParent('.b-bar__auth') != undefined) this.getParent('.b-bar__auth').getElement('.b-button ').removeClass('b-button_active');
        var parent = this.getParent('.b-shadow');
        if ( parent ) {
            parent.addClass('b-shadow_hide');
        }
        if ($('asw_form_overlay')) $('asw_form_overlay').addClass('b-shadow_hide');
        if ($('login_form_overlay')) $('login_form_overlay').addClass('b-shadow_hide');
    });
    
    // ������ �� enter
    if ($('asw_form')) {
        $('asw_form').getElements('input').addEvent('keypress', function(e){
            if (e.key === 'enter') change_au();
        })
    }
		
})

// �������� ������� �������� � ��������
function blinkSettingsButton () {
    var counter = 6, delay = 150;
    var el = $$('.b-bar__icon_tuning')[0].getParent('li');
    var timer = setInterval(function(){
        if (--counter <= 0) clearInterval(timer);
        el.toggleClass('b-bar__item_active');
    }, delay)
}

/**
 * �������� ������ ���������� ������� ��������
 * � ���������� ������ ��������
 */
	/*
(function(){
    // ������� ������� ��������� ��������
    var rules = ['draft', 'tuning', 'lenta', 'fm', 'mess', 'prj', 'sbr', 'pf', 'stat', 'adm', 'pro'];
    // ����������� ������ ������������
    var barMinReserve = 50;
    // ������������ ������ ������������
    var barMaxReserve = 50;
        
    window.addEvent('domready', function(){
        // ��� ������ ���������
        $$('#b-bar__drop_btn').addEvent('click', function(){
            syncDrop();
            $('b-bar__drop').getElements('.b-shadow__overlay').removeClass('b-shadow_hide');
            $('b-bar__drop_menu').removeClass('b-shadow_hide');
        });
        // ���� �� �������
        if ($('b-bar__drop')) {
            $('b-bar__drop').getElements('.b-shadow__overlay').addEvent('click', function(){
                closeDropdown();
            })                
        }
        $$('.b-layout__right .b-bar__link').addEvent('click', function(){
            closeDropdown();
        });
        // ������������� ����������� ������ ��������
        //$$('.b-bar').setStyle('min-width', '500px');
        // �������� ������ ��������
        if (Browser.opera) {
            setTimeout(function () { adaptUserBar(); }, 0.1 * 1000 );
        } else {
            adaptUserBar();
        }
    });
    
    window.addEvent('resize', function(){
        if (Browser.opera) {
            setTimeout(function () { adaptUserBar(); }, 0.1 * 1000 );
        } else {
            adaptUserBar();
        }
    });
    
    // �������� ������ ���� �, ���� ����, �������� ��� ������
    function adaptUserBar () {
        var i;
        var len = rules.length;
        if (detectWasteButtons() === -1) { // ���� ������� � ��������
            i = 0;
            do {
                btnToDropdown(rules[i]);
                i++
            } while (detectWasteButtons() === -1 && rules[i]);
        } else if (detectWasteButtons() === 1) { // ���� ��������� �� ���������
            i = len - 1;
            do {
                btnToUserbar(rules[i]);
                i--
            } while (detectWasteButtons() === 1 && rules[i]);
            // ���������, �� �������� �� ������� ��������� ����������� ������
            if (detectWasteButtons() === -1) {
                i++;
                btnToDropdown(rules[i]);
            }
        }
        showDropdown();
    }
    
    // ����������, ����� �� �������� ������
    // 1 - ����� ����������� � �������
    // 0 - ������ �� ������
    // -1 - ����������� � ��������
    function detectWasteButtons () {
        var barInner = $('b-bar__inner');
        if (!barInner) return;
        // ������ ������� ��������
        var barInnerWidth = barInner.getSize().x;
        if ( Browser.opera ) {
            barInnerWidth = document.documentElement.clientWidth;
        }
        // ������ ������������� ��������
        var barTableLeftWidth   = barInner.getElement('.b-layout__left ul').getSize().x;
        var barTableMiddleWidth = barInner.getElement('.b-layout__middle ul').getSize().x;
        var barTableRightWidth  = barInner.getElement('.b-layout__right ul').getSize().x;
        var barTableWidth = barTableLeftWidth + barTableMiddleWidth + barTableRightWidth;
        
        if (barInnerWidth < barTableWidth + barMinReserve) {
            return -1;
        } else if (barInnerWidth > barTableWidth + barMaxReserve) {
            return 1;
        } else {
            return 0;
        }
    }
    
    // ��������� ������ � �������
    function btnToUserbar (btn) {
        $$('#b-bar__' + btn).removeClass('b-bar__item_hide');
        $$('#b-bar__drop_' + btn).addClass('b-shadow__txt_hide');
    }
    
    // ��������� ������ � ��������
    function btnToDropdown (btn) {
        $$('#b-bar__' + btn).addClass('b-bar__item_hide');
        $$('#b-bar__drop_' + btn).removeClass('b-shadow__txt_hide');
    }
    
    // ������/�������� ������ ���������
    function showDropdown () {
        var drop = $('b-bar__drop');
        if (!drop) return;
        // ������� ������� �� ������� �������
        var items = drop.getElements('.b-shadow__txt:not(.b-shadow__txt_hide)').length;
        if (items) {
            $$('#b-bar__drop').removeClass('b-bar__item_hide');
            syncDrop();
        } else {
            $$('#b-bar__drop').addClass('b-bar__item_hide');
            closeDropdown();
        }
    }
    
    // ��������� ��������, �������� �������
    function closeDropdown () {
        $$('#b-bar__drop_menu').addClass('b-shadow_hide');
        $('b-bar__drop').getElements('.b-shadow__overlay').addClass('b-shadow_hide');
    }
    
    // �������������� �������� � ��������� (�������, �������� ������)
    function syncDrop () {
        // ���������� ������������ �� ������� ������ "������ �����������"
        var is_emp = !!$('b-bar__drop_pf');
        // ����� ��� ��������� ������
        var eventColor = is_emp ? 'b-shadow__txt_bg_f0ffe2' : 'b-shadow__txt_bg_ffebbf';
        // ���������� ��� ������, ������� ����� ���� � ����
        rules.each(function(btn){
            var barBtn, dropBtn, barTooltip, dropTooltip, text, active;
            // � ��������� ��� ��������� ������� ��������� �� �����
            if (['tuning', 'draft', 'stat', 'adm', 'pf'].contains(btn)) {
                return;
            }
            // ������ � ��������
            barBtn = $('b-bar__' + btn);
            // ������ � ���������
            dropBtn = $('b-bar__drop_' + btn);
            if (!barBtn || !dropBtn) return;
            // ������ ��������
            barTooltip = barBtn.getElement('.b-tooltip__txt')
            if (!barTooltip) return;
            text = barTooltip.get('text');
            // ������ ���������
            dropTooltip = $('b-bar__drop_' + btn).getElement('.b-tooltip__txt')
            if (!dropTooltip) return;
            dropTooltip.set('text', text);
            // ���������� ������
            active = barBtn.hasClass('b-bar__item_active');
            if (active) {
                dropBtn.addClass(eventColor);
                anyActive = true;
            } else {
                dropBtn.removeClass(eventColor);
            }
        });
        
        // ���� ���� ���� ���� �������� ����� ���� � ���������, �� �������� ������ ���������
        var activeItems = $('b-bar__drop').getElements('.b-shadow__txt.' + eventColor + ':not(.b-shadow__txt_hide)').length;
        if (activeItems) {
            $$('#b-bar__drop').addClass('b-bar__item_active');
        } else {
            $$('#b-bar__drop').removeClass('b-bar__item_active');
        }
    }
})()
 */