(function(){
    window.addEvent('domready', function(){
        initTopPayed();
    });
    
    var
        mainCheckbox, catalogCheckbox, headCarusel, textCarusel, buyBtn, buyBtnText, errorMessage, errorMessageText,
        advert, advertFrm, advertPrompt, advertHead, advertText, advertImg, advertHeadDefault, advertTextDefault, advertImgDefaultSrc, advertImgDefaultWidth, advertImgDefaultHeight,
        advertNeedMoney, advertBill,
        adHeadSend, adTextSend,
        attachBlock, attachBlockInfoShow, attachBlockInfoHide, attachBlockInfo, attachImgPath, adLastImg;
        
    // ������������� ��������
    function initTopPayed () {
        mainCheckbox = $('top-payed-maincarusel');
        catalogCheckbox = $('top-payed-catalogcarusel');
        headCarusel = $('top-payed-headcarusel');
        textCarusel = $('top-payed-textcarusel');
        buyBtn = $('top-payed-buybtn');
        buyBtnText = $('top-payed-buybtn-text');
        errorMessage = $('top-payed-errormessage');
        errorMessageText = $('top-payed-errormessage-text');
        advertFrm = $('top-payed-frm');
        advertNeedMoney = $('top-payed-need-money');
        advertBill = $('top-payed-bill');
        
        advert = $('top-payed-advert');
        advertPrompt = $('top-payed-advertprompt');
        advertHead = $('top-payed-adverthead');
        advertText = $('top-payed-adverttext');
        advertImg = advert.getElement('img');
        
        advertHeadDefault = '��������� ����������';
        advertTextDefault = '����� ����������';
        advertImgDefaultSrc = advertImg.get('src');
        advertImgDefaultWidth = advertImg.get('width');
        advertImgDefaultHeight = advertImg.get('height');
        
        adHeadSend = $('ad_head');
        adTextSend = $('ad_text');
        adLastImg = $('ad_last_img');
        
        mainCheckbox.addEvent('change', checkAdvert);
        //catalogCheckbox.addEvent('change', checkAdvert);
        headCarusel.addEvent('change', checkAdvert);
        headCarusel.addEvent('keyup', checkAdvert);
        headCarusel.addEvent('focus', checkAdvert);
        textCarusel.addEvent('change', checkAdvert);
        textCarusel.addEvent('keyup', checkAdvert);
        textCarusel.addEvent('focus', checkAdvert);
        buyBtn.addEvent('click', saveAdvert);
        

        // ���� �������� �����
        attachBlock = $('attach_carusellogo');
        new attachedFiles2(
            attachBlock,
            {
                session: TopPayed.session,
                hiddenName: "carusellogo[]",
                files: TopPayed.attached,
                onComplete: function(obj, file){
                    setAdvertImg(file);
                },
                onDelete: function (obj) {
                    setAdvertImg('default');
                }
            },
            TopPayed.session
        );
        //attachBlockInfo = attachBlock.getElement('#attachedfiles_info');
        //attachBlockInfoShow = attachBlock.getElement('.b-fileinfo');
        //attachBlockInfoHide = attachBlock.getElement('.b-shadow__icon_close');
        //attachBlockInfoShow.addEvent('click', function(){
        //    attachBlockInfo.removeClass('b-shadow_hide');
        //});
        //attachBlockInfoHide.addEvent('click', function(){
        //    attachBlockInfo.addClass('b-shadow_hide');
        //});
        
        checkAdvert();
    }
    
    
    // ��������� ��������� ���������� � ����������/�������� ���
    // � ����� ������� ��������� �� ������� � ��������� ������
    function checkAdvert () {
        var head, text;
        head = headCarusel.get('value').trim() || advertHeadDefault;
        text = textCarusel.get('value').trim() || advertTextDefault;
        
        if (head.length > 22) {
            head = head.slice(0, 22) + '...';
        }
        
        head1 = TopPayedReplace(head);
        text1 = TopPayedReplace(text);

        advertHead.set('text', head1);
        advertText.set('html', text1.replace(/</gi, '&lt;').replace(/>/gi, '&gt;').replace(/\n/gi, '<br>'));
        
        var disableBtn = false;
        
        // ��� ���������� ����������
        if (mainCheckbox.get('checked') /*|| catalogCheckbox.get('checked')*/) {
            errorMessage.addClass('b-layout_hide');
        } else {
            errorMessage.removeClass('b-layout_hide');
            errorMessageText.set('text', '�������� �������� ����������.');
            disableBtn = true;
        }
        
        // ��������� ����������
        var price = mainCheckbox.get('checked') * TopPayed.adCost /*+ catalogCheckbox.get('checked') * TopPayed.adCost2*/;
        if(toppayed_c_btn==1) {
            buyBtnText.set('text', '������ �� ' + price + ending(price, ' �����', ' �����', ' ������'));
        }
//        if (TopPayed.accSum < price) {
//            var needMoney = price - TopPayed.accSum;
//            advertNeedMoney.set('text', '��� �� ������� ' + needMoney.toFixed(2) + ending(needMoney, ' �����', ' �����', ' ������'));
//            advertBill.setStyle('display', '');
//            disableBtn = true;
//        } else {
//            advertNeedMoney.set('text', '');
//            advertBill.setStyle('display', 'none');
//        }
        
        if (disableBtn) {
            disableBuyBtn();
        } else {
            activateBuyBtn();
        }
        
    }
    
    function disableBuyBtn () {
        buyBtn.addClass('b-button_disabled');
        disallowSendForm = true;
        
    }
    
    function activateBuyBtn () {
        buyBtn.removeClass('b-button_disabled');
        disallowSendForm = false;
    }
    
    // ���������� �������� � ���� ��� ����� ��������� ����������
    function setAdvertImg (file) {
        adLastImg.set('value', ''); // ����������� �� ����������� ���������� ��� �� �����
        if (file === 'default') { // ���������� ������� ��� ��������
            advertImg.set('src', advertImgDefaultSrc);
            advertImg.set('width', advertImgDefaultWidth);
            advertImg.set('height', advertImgDefaultHeight);
            attachImgPath = null;
        } else if (typeof file === 'string') { // ������ ���� � ����� ��������
            advertImg.set('src', file);
            advertImg.erase('width');
            advertImg.erase('height');
        } else { // ������ � ����������� � ����� ���������� �� attachedfiles
            var path = ___WDCPREFIX + '/' + file.path + file.name;
            advertImg.set('src', path);
            advertImg.erase('width');
            advertImg.erase('height');
            attachImgPath = path;
        }
    }
    
    var disallowSendForm = false; // ���� true �� ��������� ���������� �����, ������ ����� ��� ��� ������������ ������
    // ��������� (������) ����������
    function saveAdvert (event) {
        if (disallowSendForm) {
            return;
        }
        
        var head = headCarusel.get('value');
        var text = textCarusel.get('value');
        
        if (!head.trim() || !text.trim()) {
            errorMessage.removeClass('b-layout_hide');
            errorMessageText.set('text', '��������� ��������� � ����� ����������');
            disableBuyBtn();
            return;
        } else {
            errorMessage.addClass('b-layout_hide');
        }
        adHeadSend.set('value', head);
        adTextSend.set('value', text);
        
        if(toppayed_c_btn==1) {        
            quickCAR_show();
        } else {
            disableBuyBtn();
            advertFrm.submit();
        }
    }
    
})()