var coords;
var fixPoint;
var footPoint = 0;
var scrolled;

function fix_banner() {
    var banWrap = document.getElementById('banner_wrap');
    var bfix = document.getElementById('b-banner_fix');
    
    var spec = document.getElementById('specialis');
    var foot = document.getElementById('i-footer');
    var seo_block = document.getElementById('seo_block');
    
    if (spec) {
        footPoint = spec.getCoordinates().top;      // �������� �������� ��� ��������
    } else if (foot) {
        footPoint = foot.getCoordinates().top;		// ���������� �������
    }

    if (bfix) {
        bfixH = bfix.offsetHeight; // ������ ������
        coords = banWrap.getCoordinates().top - 80; // ��������� ����������, ��� ����� �����

        fixPoint = footPoint - bfixH - 80;  // ������� ����������, �� ������� ����� ����������� � �������

        function scrolBanner() {
            var offsetLeft = (banWrap.offsetWidth - 240) / 2;
            scrolled = window.pageYOffset || document.documentElement.scrollTop; // ������ ��������� �����
            if (coords < scrolled) {
                bfix.setStyle('margin-left', offsetLeft + 'px');
                if (fixPoint+9 > scrolled) {
                    if (fixPoint > coords+130) {
                        bfix.addClass('b-banner_fixed');
                        bfix.removeClass('b-banner_abs');
                        bfix.setStyle('top', '');
                        if (seo_block) seo_block.hide();
                    }
                } else if (fixPoint > coords+130) {
                    bfix.addClass('b-banner_abs');
                    bfix.removeClass('b-banner_fixed');
                    bfix.setStyle('top', fixPoint - coords-25);
                }
            } else {
                bfix.removeClass('b-banner_fixed');
                bfix.setStyle('top', '');
                bfix.setStyle('margin-left', '');
                if (seo_block) seo_block.show();
            }
        }
        scrolBanner();
    }
}

fix_banner();

window.addEvent('resize', fix_banner);




