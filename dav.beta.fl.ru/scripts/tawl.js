/**
 * ����������� �� ������������ ���������� �������� ��������.
 * 
 * ����������:
 *       ���������� ���� ���� � /css/nav.css ��� �������
 *  
 * ������� ������:
 *       � ����� ��� ���������� ������ � textarea ����������� � �������� �������� ����� tawl � 
 *       ������� rel �� ��������� ����������� ����������� ���������� ��������, ��������
 *       
 *       <textarea class="tawl" rel="30" name="black">black</textarea>
 *       <textarea class="someclass tawl" rel="50" name="hawk">hawk</textarea>
 *
 *       � javascript ��������� ����� (���� ��� ����) �������� tawlFormValidation(this) 
 *       
 *       ����� ��� ������ js ����������, ������� ��������� (�� ���� ���������) ������ �� domready
 *       
 *       1) ���������� ���� ��� textarea � ������� tawl
 *       
 *       2) ��������� textare ������������ � span class="tawl-outer" � ��������� ����� textarea
 *          span class="tawl-limit"
 *       
 *       3) ������ �� textarea ���������� ������� ����� ������, ��������� ������, ����� � �.�., 
 *          �� ���� �������� ���������� �������� ���-�� ��������� �������� � ����� ��������� � ���������
 */

function tawlFormValidation( form ) {
    var bValid    = true;
    var aTextarea = $$('#'+form.id+' textarea[class~="tawl"]' );
    
    Array.each(aTextarea, function(field, index) {
        var len  = field.get('value').length;
        var max  = parseInt( field.get('rel'), 10 );
        
        if ( !isNaN(max) && field.get('value').length > field.get('rel') ) {
            bValid = false;
            field.fireEvent('focus');
        }
    });
    
    if ( !bValid ) {
        alert('�������� ����� ��������');
        return false;
    }
    
    return true;
}

function tawlTextareaShowSpan() {
    var len  = this.get('value').length;
    var max  = parseInt( this.get('rel'), 10 );
    
    if ( isNaN(max) ) {
        return;
    }
    
    var dif  = max - len;
    var span = this.getNext();
    
    span.removeClass( 'tawlr' );
    
    if ( len > max ) {
        span.addClass( 'tawlr' );
        span.set( 'html', '<span>�������� ����� �������� ��� ���� (' + max + ' ��������)</span>' );
    }
    else {
        span.set( 'html', '<span>�������� ' + dif + ' ��������</span>' );
    }
}

function tawlTextareaHideSpan() {
//    this.getParent('span').setStyle('height', this.getParent('span').getSize().y);
    var obj = this;
    setTimeout(function( ){
        var next = obj.getNext();
        if (next) {
            next.set('html', '') 
        }
    }, 400);
}

function tawlPaste() {
    this.fireEvent('focus', this, 100);
}

function tawlTextareaInit() {
    var aTextarea = $$("textarea[class~='tawl']");
    var bIsIE = ( navigator && navigator.userAgent && navigator.userAgent.indexOf('MSIE') != -1);
    
    Array.each(aTextarea, function(field, index) {
		var outer = field;
        if(field.getParent().hasClass('b-textarea')) {
            outer = field.getParent();
        } else {
            outer = field;
        }
        if(outer.getParent().hasClass('tawl-outer')) {
            return false;
        }
        var spanOuter = new Element('span', {'class': 'tawl-outer'});
        var spanInner = new Element('span', {'class': 'tawl-limit'});
        var dim          = outer.getSize();
        var borderLeft   = parseInt(outer.getStyle('border-left-width'));
        var borderRight  = parseInt(outer.getStyle('border-right-width'));
        var paddingLeft  = parseInt(outer.getStyle('padding-left'));
        var paddingRight = parseInt(outer.getStyle('padding-right'));
        var marginLeft   = parseInt(outer.getStyle('margin-left'));
        var marginRight  = parseInt(outer.getStyle('margin-right'));
        var styleWidth   = dim.x - borderLeft - borderRight - paddingLeft - paddingRight - marginLeft - marginRight;

        if (styleWidth >= 0) {
            outer.setStyle('width', styleWidth );
            spanOuter.setStyle('width', styleWidth );
        }
        spanOuter.wraps(outer);
        spanInner.inject(field, 'after');
        
        field.addEvent( 'focus', tawlTextareaShowSpan );
        field.addEvent( 'blur',  tawlTextareaHideSpan );
//        field.addEvent( 'mouseleave',  tawlTextareaHideSpan );
        
        if ( !bIsIE ) {
            if ( ('oninput' in field) ) {
                field.oninput = tawlTextareaShowSpan;
            }
            else {
                field.setAttribute( 'oninput', "var tawlBoundFunction=tawlTextareaShowSpan.bind(this);tawlBoundFunction();" );
            }
        }
        else {
            field.addEvent( 'keyup',    tawlTextareaShowSpan );
            field.addEvent( 'keydown',  tawlTextareaShowSpan );
            field.addEvent( 'keypress', tawlTextareaShowSpan );
            
            field.onpaste = tawlPaste;
        }
    });
    return false;
}

window.addEvent('domready', function() {
    tawlTextareaInit();
});
