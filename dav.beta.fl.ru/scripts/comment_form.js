// ������ ��� ����� ���������� ������������ � �����
(function(){
    var max_answers = 10; // ������������ ���������� �������
    var question_max_length = 256; // ������������ ����� �������
    var youtube_default = '������ �� ���������� Youtube, Rutube ��� Vimeo'; // ��������� ����� � ���� ����� ������ �� �����
    var submit_flag = 1;
    // �������� ���� ��� ����� ������ �� �����
    function show_video_input () {
        $('add_yt_box').setStyle('display', 'none');
        $('yt_box').setStyle('display', 'block');
        youtube_set_default();
        return false;
    }
    // ������ ���� ��� ����� ������ �� �����
    function hide_video_input () {
        $('add_yt_box').setStyle('display', 'block');
        $('yt_box').setStyle('display', 'none');
        $('youtube_link').setProperty('value','');
        check_youtube();
        return false;
    }
    // ��� ������ �� ����
    function check_youtube () {
        var you = $('youtube_link');
        if (you.getProperty('value') == youtube_default) you.setProperty('value', '');
        you.removeClass('b-combo__input-text_color_a7');
    }
    // ��������� ��������� ��������
    function youtube_set_default () {
        var temp = $('youtube_link');
        if (temp.get('value') === '') {
            temp.set('value', youtube_default);
            temp.addClass('b-combo__input-text_color_a7');
        }
    }

    // �������� ���� ��� ���������� ������
    function show_poll () {
        $('pool_box').removeClass('b-form_hide');
        $('add_poll').addClass('b-form_hide');
        return false;
    }
    // ������ ���� ���������� ������
    function hide_poll () {
        $('pool_box').addClass('b-form_hide');
        $('add_poll').removeClass('b-form_hide');
        $('question').value = ''; // ������� ������
        // ������� ��� ������ ����� �������, ��� ������ �������, � ������ ������� input
        $each(
            $$('table[id^=poll-]'),
            function (el, index) {
                if (index === 0) {
                    el.getElement('input[type=text]').value = '';
                    el.getElement('a[id^=del_answer_btn_]').setStyle('display', 'none');
                    el.getElement('input[type=text]').addEvent('input', answer_changed);
                } else {
                    el.dispose();
                }
            }
        )
        return false;
    }
    // ������ ���� �����
    function one_answer () {
        $$( '#poll-radio').setStyle('display','none');
        $$( '#poll-check').setStyle('display','block');
        $$( '#multiple').setProperty('value', '1');
        $each($$('table[id^=poll-] input[type=radio]'), function(el){
            el.setStyle('display', 'none')
        });
        $each($$('table[id^=poll-] input[type=checkbox]'), function(el){
            el.setStyle('display', 'block')
        });
        return false;
    }
    // ��������� �������
    function many_answers () {
        $$( '#poll-check').setStyle('display','none');
        $$( '#poll-radio').setStyle('display','block');
        $$( '#multiple').setProperty('value', '0');
        $each($$('table[id^=poll-] input[type=radio]'), function(el){
            el.setStyle('display', 'block')
        });
        $each($$('table[id^=poll-] input[type=checkbox]'), function(el){
            el.setStyle('display', 'none')
        });
        return false;
    }
    // ��������/������ ������� ����� �������
    function show_question_counter () {
        var counter = $('poll_counter');
        var length = $('question').get('value').length;
        if (length > question_max_length) {
            counter.addClass('tawlr');
            counter.set('html', '<span>�������� ����� �������� ��� ���� (' + question_max_length + ' ��������)</span>');
        } else {
            if (counter) {
                if(counter.hasClass('tawlr')) {
                    counter.removeClass('tawlr');
                }
                counter.set('html', '<span>�������� ' + (question_max_length - length) + ' ��������</span>');
            }
        };
    }
    function hide_question_counter () {
        if ($('poll_counter')) {
            $('poll_counter').getElement('span') && $('poll_counter').getElement('span').dispose();
        }
    }

    // ����� ������� (��������)
    function answer_changed () {
        // ���������� ������ ������� �����
        this.getParent('table').getElement('a[id^=del_answer_btn_]').setStyle('display', '');
        add_new_answer(this);
    }
    // �������� ������ �������� �����
    function add_new_answer () {var answers = $$('table[id^=poll-]'); // ������ �������
    var s  = answers.length - 1; // ����� ���������� ������
    if (s + 1 >= max_answers) return;
    var input = answers[s].getElement('input[type=text]');
    input.removeEvent('input', answer_changed); // ������� ���������� �������
    input.removeEvent('keypress', answer_changed); // ������� ���������� �������
    var sr = answers[s]; // ��������� ����� �� ���������� (����� ���������� �������������)
    
    var clone = sr.cloneNode(true); // ��������� ��������� �����
    var id = +clone.id.match(/poll-(\d+)/)[1];
    var new_id = id + 1; // id ������ ������        
    clone.id = 'poll-' + new_id;
    clone.getElement('a[id^=del_answer_btn_]').setStyle('display', 'none'); // �������� ������ ������� �����
    sr.parentNode.appendChild(clone, sr.parentNode); // ��������� ���� �� ������ ������        
    var td = clone.getElement('td.b-layout__middle').set("html", '');
    ComboboxManager.append(td, "b-combo__input", 'answer_input_' + new_id);
    var clone_input = ComboboxManager.getInput('answer_input_' + new_id).b_input;
    clone_input.value = '';
    clone_input.name = 'answers[]';
    clone_input.tabIndex = '20' + s + 1;
    $(clone_input).set('maxlength', 100);
    var dr = $('poll-' + new_id); // ����� ��������� �����
    $(dr).getElement('input[type=text]').addEvent('input', answer_changed);
    $(dr).getElement('input[type=text]').addEvent('keypress', answer_changed);
    $(dr).getElement('a[id^=del_answer_btn_]').addEvent('click', del_answer);
    }
    // ������� �����
    function del_answer () {
        this.getParent('table').dispose();
        // ���� �� �������� ���� ������������ ���-�� ������� � ��������� ���� ���������
        var answers = $$('table[id^=poll-] input[type=text]');
        var length = answers.length;
        if (length === (max_answers - 1) && answers[length-1].get('value') !== '') {
            add_new_answer();
        }        
    }
    
    // ��������� ����
    function save_post_button () {
        if (submit_flag && this.get('disabled')!=true) {
            if (!check_post()) return;
            submit_flag = 0;
            this.getParent('form').submit();
        }
        return false;
    }
    
    // �������� �����
    function form_submit (event) {
        if((event.control) && ((event.code==10)||(event.code==13))) {
            if (!check_post()) return;
            this.submit();
        }
    }
    function form_keydown (event) {
        if(event.control && event.code==13 && submit_flag==1){
            if (!check_post()) return;
            submit_flag=0;
            this.submit();
        }
    }
    
    /**
     * �������� ����� �����
     * ���� ������ false, �� ���� �������� �� ������
     */    
    function check_post () {
        check_youtube();
        // ��������� ����� �������
        var length = $('question').get('value').length;
        if (length > question_max_length) {
            alert ('����� ������� ��������� ' + question_max_length + ' ��������');
            return false;
        }
        // �������� ��������
        return true;
    }
    
    // ��������� ��������
    function save_as_draft () {
        //href="javascript:DraftSave();" onclick="this.blur();"
        this.blur();
        DraftSave();
        return false;
    }
    
    // ������ error
    function hide_error (context) {
        var temp;
        if (context === 'youtube') {
            if (temp = $('youtube_link').getParent('div.b-combo__input_error')) temp.removeClass('b-combo__input_error');
            if (temp = $('msgtext_error_youtube')) temp.setStyle('display', 'none');
        } else if (context === 'question') {
            if (temp = $('question').getParent('div.b-textarea_error')) temp.removeClass('b-textarea_error');
            if (temp = $('msgtext_error_polls')) temp.setStyle('display', 'none');
        } else if (context === 'answer') {
            $$('table[id^=poll-] div.b-combo__input_error').removeClass('b-combo__input_error');
            if (temp = $('msgtext_error_polls_question')) temp.setStyle('display', 'none');
        }
    }
    
    // ���������� �������
    window.addEvent('domready', function() {
        var temp;
        // �����
        $('add_yt_box1').addEvent('click', show_video_input);
        $('add_yt_box2').addEvent('click', show_video_input);
        $('hide_yt_box').addEvent('click', hide_video_input);
        temp = $('youtube_link');
        temp.addEvent('focus', check_youtube);
        temp.addEvent('blur', youtube_set_default);
        
        // �����
        $('add_poll1').addEvent('click', show_poll);
        $('add_poll2').addEvent('click', show_poll);
        $('hide_poll').addEvent('click', hide_poll);
        $$('#poll-radio').getElement('.b-menu__link').addEvent('click', one_answer);
        $$('#poll-check').getElement('.b-menu__link').addEvent('click', many_answers);
        // ������� �������� � �������
        $('question').addEvent('focus', show_question_counter);
        $('question').addEvent('blur', hide_question_counter);
        $('question').addEvent('input', show_question_counter);
        $('question').addEvent('keyup', show_question_counter);
        // ������ ������
        var answers = $$('input[id^=answer_input]');
        var answers_count = answers.length
        // ���� ��� ���������� ������ ������ ����������� �� ������� ������
        answers[answers_count - 1].addEvent('input', answer_changed);
        answers[answers_count - 1].addEvent('keypress', answer_changed);
        // �������� ������ �������� ������
        $each( $$('table[id^=poll-]'), function (el) {
            if (el.getElement('input[id^=answer_input_]').value !== '') {
                el.getElement('a[id^=del_answer_btn_]').setStyle('display', '');
            }
            el.getElement('a[id^=del_answer_btn_]').addEvent('click', del_answer);
        })
        // ���� ��������� ���� ��� �������� �����, �� ��������� ����� ������
        if  (answers[answers_count - 1].value !== '') {
            add_new_answer(answers[answers_count - 1]);
            //$('add_poll').setStyle('display', 'none');
        }
        
        // ������ ��������� �����
        $('topic_form_submit').addEvent('click', save_post_button);
        // �����
        var form = $('idAlertedCommentForm') || $('msg_form');
        form.addEvent('submit', form_submit);
        form.addEvent('keydown', form_keydown);
        
        // ��������
        DraftInit(4);
        $('save_as_draft').addEvent('click', save_as_draft);
        $('save_as_draft').addEvent('keypress', save_as_draft);
        
        // ����� ��������� ������� ����� error
        if (temp = $('youtube_link')) temp.addEvent('focus', function(){hide_error('youtube')});
        if (temp = $('question')) temp.addEvent('focus', function(){hide_error('question')});
        if (temp = $$('input[id^=answer_input_]')) temp.addEvent('focus', function(){hide_error('answer')});
    });
    
})();
    
