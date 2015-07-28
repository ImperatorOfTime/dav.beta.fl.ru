var Finance = new Class({
    Implements: [Events, Options],
    
    options: {
        'popup_id':         'finance_popup',
        'form_id':          'financeFrm',
        'form_type_id':     'form_type',
        'errors':           [],
        'form_type':        1,
        'selector':    {
            'save':            '.finance-save',
            'popup_open':      '.finance-open',
            'popup_close':     '.finance-close',
            'block_expand':    '.finance-block',
            'menu_action':     'b-menu__item_active',
            'type':            '.select-type',
            'finance_delete':  '__finance_delete'
        }
    },
    
     initialize: function(options) {
        this.setOptions(options);
        

        var finance_delete = $(this.options.selector.finance_delete);
        if (finance_delete) {
            finance_delete.addEvent('click', function(){
                if(confirm('����� �������� ��� ����� ���������� ��� �������������� ���������� ����������. ��� �������������� ����� ����� ���������� � ������������.')) {
                    this.addClass('b-button_disabled');
                    Bar_Ext.sendHideForm('.', 'finance_delete');
                }
            });
        }
       
        if($$(this.options.selector.popup_open)) {
            
            $$(this.options.selector.popup_open).addEvent('click', function() {
                if(this.options.form_type == 2) {
                    this.switchReqvFT(1,2);
                } else {
                    this.switchReqvFT(2,1);
                }
                $(this.options.popup_id).setStyle('display', 'block');
                $(this.options.popup_id).removeClass('b-shadow_hide');
            }.bind(this));
        }
        
        if($$(this.options.selector.popup_close)) {
            $$(this.options.selector.popup_close).addEvent('click', function() {
                $(this.options.popup_id).setStyle('display', 'none');
            }.bind(this));
        }
        
        if($$(this.options.selector.block_expand)) {
            $$(this.options.selector.block_expand).addEvent('click', function() {
                var block_expand = this.getParent().getNext('span');
                if(block_expand.hasClass('block-hide')) {
                    block_expand.show();
                    block_expand.removeClass('block-hide');
                } else {
                    block_expand.hide();
                    block_expand.addClass('block-hide');
                }
            });
        }
        
        if($$(this.options.selector.type)) {
            $$(this.options.selector.type).addEvent('change', function() {
                var value = this.get('value');
                if (value == 1) {
                    var fio = $$("input[id$=i_2_fio]").get('value');
                    $$("input[id$=i_2_full_name]").set('value', fio);
                    $('i_2_kpp').getParent('table.b-layout__table').style.display='none';;
                } else {
                    $('i_2_kpp').getParent('table.b-layout__table').style.display='table';;
                }
            });
        }
        
        if($$(this.options.selector.save)) {
            $$(this.options.selector.save).addEvent('click', function() {
                $(this.options.form_id).submit();
            }.bind(this));
        }
        // ��������� ����� �������� �� ����� ������� � ������
        window.addEvent('domready', function(){
            $$("input[id$=_mob_phone]").addEvent('change', function(){
                var mobPhone = this.get('value');
                $$("input[id$=_mob_phone]").set('value', mobPhone);
            });
            
            $$("input[id$=_address_reg]").addEvent('change', function(){
                var addressReg = this.get('value');
                var address = $$("input[id$=_address]")[0].get('value');
                if (!address) $$("input[id$=_address]").set('value', addressReg);
            })
        })
    
        // ������������ ��������� ��������
        this.correctLines(this.options.form_type);
     },
     
     switchReqvRT: function(vrt) 
     {
        var vb,hb;
        
        //���� ����������
        if(vrt==2) {
            if($('i_1_bank_rs')) { $('i_1_bank_rs').set('maxlength', 40); }
            if($('i_2_bank_rs')) { $('i_2_bank_rs').set('maxlength', 40); }
            $$('.label-full_name').set('text', '������ �������� �����������');
            $$('.example-full_name').set('text', '��������: Ariston AG ��� Petersbrown Ltd.');
            $$('.label-bank_rs').set('text', '��������� ���� (IBAN)'); 
            $$('.example-idcard').set('text', '��������: UA63 � 123456');
            $$('.label-bank_name').set('text', '�������� ������ �����');
            $$('.example-bank_name').set('text', '��������: ������������� ��� ����, ������');
        } else {
            if($('i_1_bank_rs')) { $('i_1_bank_rs').set('maxlength', 22); }
            if($('i_2_bank_rs')) { $('i_2_bank_rs').set('maxlength', 22); }
            $$('.label-full_name').set('text', '�������� �����������');
            $$('.example-full_name').set('text', '��������: ������� ��� ������ ���� �������� (���� ��)');
            $$('.label-bank_rs').set('text', '��������� ����');
            if (vrt == 3) {
                $$('.example-idcard').set('text', '��������: UA63 � 123456');
            } else {
                $$('.example-idcard').set('text', '��������: 1234 � 567890');
            }
            $$('.label-bank_name').set('text', '�������� �����');
            $$('.example-bank_name').set('text', '��������: ��� ������-����, ������');
        }
        
        
        //���� ������� ��� ���� ��� �� ����������
        if (vrt == 3 || vrt == 4) {
            this.switchReqvFT(2,1);
            $('status-fiz').set('checked', true);
            $('block_status-ip').hide();
            
            if (vrt == 3) {
                $$('.label-idcard').set('text', '����� � ����� �������������');
                $$('.label-idcard_to').set('text', '������������� ��');
            } else {
                $$('.label-idcard').set('text', '����� � ����� ���� �� ���������� � ��');
                $$('.label-idcard_to').set('text', '������������ ��');
            }
        } else {
            $('block_status-ip').show('table-row');
            $$('.label-idcard').set('text', '����� � ����� ��������');
            $$('.label-idcard_to').set('text', '������������ ��');
        }
        
        
        if(vb=$$('.rez--itm'+vrt)) vb.each( function(e) { e.style.display=vsty(e.tagName); } );
        
        for(var itm = 1; itm < 5; itm++) {
            if (itm == vrt) {
                continue;
            }
            
            if(hb = $$('.rez--itm'+itm+':not(.rez--itm'+vrt+')')) { 
                hb.each( function(e) { e.style.display='none'; } ); 
            }
        }
        
     },
     
    switchReqvFT: function(etype, dtype) {
        $(this.options.form_type_id).set('value', dtype);
        
        var dsb = $$(".ft" + dtype + "_set");
        var enb = $$(".ft" + etype + "_set");
        if(dsb) dsb.show();
        if(enb) enb.hide();
        // ��������� ����� ��������
//        var mobPhone = enb.getElement("input[id$=_mob_phone]")[0].get("value");
//        var input = dsb.getElement("input[id$=_mob_phone]")[0];
//        if (mobPhone.length) {
//            input.set("value", mobPhone);
//            // �������� ���������
//            input.getParent('.b-combo__input').getSiblings('label').addClass('b-input-hint__label_hide');
//        }
        
        JSScroll($('fiz_yuri_tabs'));
        // ������������ ��������� �������� �����
        this.correctLines(dtype);
    },
    
    setErrors: function(errors) {
        this.options.errors = errors;
    },
    
    viewErrors: function() {
        for(var error in this.options.errors.sbr) {
            var IDElm = 'i_' + this.options.form_type + '_' + error;
            var node = $(IDElm);
            if (node != null) {
                $(IDElm).getParent().addClass('b-combo__input_error');
                if(scroll == undefined) { // ������� ������ � ������ ������.
                    var scroll = true;
                    JSScroll($(IDElm));
                }
            } else if (error == 'err_attach') {
                var attach_block = $('attach_block');
                if(scroll == undefined) { // ������� ������ � ������ ������.
                    var scroll = true;
                    JSScroll(attach_block);
                }
            }
        }
    },
    
    viewStringErrors: function() {

        for (var error in this.options.errors.sbr) {
            var IDElm = 'i_' + this.options.form_type + '_' + error;

            if (!$(IDElm)) {
                continue;
            }
            
            $(IDElm).addEvent('focus', function() {
                if($(IDElm + '_estr')) {
                    $(IDElm + '_estr').dispose();
                }
            });
            
            var elm   = $(IDElm).getParent('.b-combo');
            var string_error = this.options.errors.sbr[error];
            if (string_error != '') {
                string_error = '<span class="b-icon b-icon_top_2 b-icon_sbr_rattent"></span>' + string_error;
                var error_div = new Element('div', {'class': 'b-layout__txt b-layout__txt_padtop_5 b-layout__txt_color_c10600', 'html': string_error, 'id' : IDElm + '_estr'});
                elm.grab(error_div, 'after');
            }
        }
    },
    
    /**
     * ������������ ��������� �������� ����� ������� �������� 2 � ����� ������
     * ��������� ��� �������� ����� �������� ������ b-layout__txt_padbot_5
     * ������������ ���� ��� ��� ������� ����, ��� ����������� ���� ������ ����������� ��� �������������
     * 
     * @param faceType - ��� ���� �� ������� ��������� ������������ ��� � ������� �������� �����������
     */
    correctLines: function (faceType) {
        // ���������, �������� ��� ���� ����������� �������������
        if (faceType == 1) {
            if (this.fizCorrected) {
                return;
            } else {
                this.fizCorrected = true;
            }
        } else if (faceType == 2) {
            if (this.yurCorrected) {
                return;
            } else {
                this.yurCorrected = true;
            }
        } else {
            return;
        }
        // �������������
        window.addEvent('domready', function(){
            $$('form#financeFrm span.ft' + faceType + '_set div.b-layout__txt').each(function(el){
                var height = el.getSize().y;
                if (height > 40) { // ���� �������� � ��� ������, �� ������ ����� 41px
                    el.removeClass('b-layout__txt_padtop_5');
                }
            });
        });
    },
    /**
     * ���� �� ��� ������������� �������� correctLines()
     */
    fizCorrected: false, // ��� �������
    yurCorrected: false // ��� ������
});

window.addEvent('load', function(){
    init_fileinfo();
    bindLinkActivateAuth();
});

function bindLinkActivateAuth() {
    $$('.c_sms_main a.b-button').addEvent('click', function(){
        if($('sms_is_load')) { 
            $('sms_is_load').getParent().removeClass('b-shadow_hide'); 
        } else {
            var send  = $(this).getProperty('data-send');
            var phone = $(this).getProperty('data-phone');
            xajax_authSMS(false, send != null ? send : 'send', phone);
        }
    });
}

function savePhoneChage(obj) {
    if($('sms_is_load')) {
        $('sms_is_load').getParent().addClass('b-shadow_hide');
        $('sms_is_load').destroy();
    }
    
    $$('.c_sms_main a.b-button').set('data-phone', obj.value);
}

function bindLinkUnativateAuth(_uid) {
    if($$('.c_sms_main').getElement('a.b-layout__link') == false) return;
    $$('.c_sms_main').getElement('a.b-layout__link').addEvent('click', function(){
        if($('sms_is_load') && $('sms_is_load').get('data-action') == 'safety') { 
            $('sms_is_load').getParent().removeClass('b-shadow_hide'); 
        } else {
            xajax_unactivateAuth(_uid);
        }
    });
}

function a_sms_act(obj) {
    $(obj).getElement('.b-button__txt').addClass('b-button__txt_hide');
    $(obj).getElement('.b-button__load').show();
    var code = $('i_sms_code').get('value');

    $('sms_error').addClass('b-layout__txt_hide');
    $('i_sms_code').getParent().removeClass('b-combo__input_error');

    xajax_authCodeSMS(code);
}


function a_sms_act_safety(obj) {
    $(obj).getElement('.b-button__txt').addClass('b-button__txt_hide');
    $(obj).getElement('.b-button__load').show();
    $('sms_error').addClass('b-layout__txt_hide');
    $('i_sms_code').getParent().removeClass('b-combo__input_error');
    var code = $('i_sms_code').get('value');
    
    xajax_authCodeSMS(code, 'safety');
}

function a_sms_unact_safety(obj) {
    $(obj).getElement('.b-button__txt').addClass('b-button__txt_hide');
    $(obj).getElement('.b-button__load').show();
    $('sms_error').addClass('b-layout__txt_hide');
    $('i_sms_code').getParent().removeClass('b-combo__input_error');
    var code = $('i_sms_code').get('value');
    
    xajax_unauthCodeSMS(code, 'safety');
}

function a_sms_disabled_safety(obj) {
    $('smscode').set('value', $('i_sms_code').get('value'));
    $('safetyform').submit();
}

function safetyForm(n) {
    $('safetyform').submit();
}