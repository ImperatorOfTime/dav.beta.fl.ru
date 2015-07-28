   /**
    * ������� ��:
    * b-combo-dynamic-input.js
    * b-combo-multi_dropdown.js
    */
    var countryPhoneCodes = {0:'7:������:-660',1:'380:�������:-2002',2:'375:��������:-1100',3:'77:���������:-1210',4:'373:�������:-2685',5:'998:����������:-1001',6:'371:������:-1936',7:'49:��������:-2509',8:'1:���:-44',9:'972:�������:-341',10:'370:�����:-1122',11:'372:�������:-2410',12:'374:�������:-176',13:'994:�����������:-1243',14:'61:���������:-1716',15:'420:�����:-2256',16:'44:��������������:-55',17:'33:�������:-1012',18:'1:������:-1375',19:'996:����������:-1617',20:'995:������:-858',21:'43:�������:-1331',22:'359:��������:-2586',23:'34:�������:-1155',24:'48:������:-1177',25:'39:������:-143',27:'32:�������:0',28:'358:���������:-1903',29:'30:������:-165',30:'1876:������:-1727',31:'64:����� ��������:-1540',32:'504:��������:-2156',33:'90:������:-1606',34:'1441:�������:-1914',35:'54:���������:-2377',36:'81:������:-429',37:'1340:������������ ���������� �������:-1782',38:'297:�����:-792',39:'599:������, ����-�������� � ����:-2719',40:'1284:���������� ���������� �������:-1408',41:'226:�������-����:-726',42:'379:�������:-2322',43:'299:����������:-1760',44:'1671:����:-2366',45:'246:�����-������:-55',46:'1345:��������� �������:-308',47:'599:�������:-2729',48:'596:���������:-198',49:'692:���������� �������:-1144',50:'1664:���������:-583',51:'31:����������:-1441',52:'683:����:-2079',53:'687:����� ���������:-1276',54:'971:������������ �������� �������:-2223',55:'247:������ ����������:-55',56:'6723:������ �������:-209',57:'290:������ ������ �����:-495',58:'682:������� ����:-2267',59:'1649:������� Ҹ��� � ������:-1309',60:'970:���������:-1199',61:'1:�������� ���������� �������:-704',62:'248:�������:-1045',63:'590:���-���������:-1012',64:'590:���-������:-55',65:'1721:���-������ (������������� �����):-2773',66:'508:���-���� � �������:-1078',67:'1869:����-���� � �����:-99',68:'1758:����-�����:-1397',69:'690:�������:-2751',70:'681:������ � ������:-1012',71:'298:��������� �������:-1111',72:'500:������������ �������:-2762',73:'594:����������� ������:-2234',74:'689:����������� ���������:-1705',75:'236:����������-����������� ����������:-1837',78:'352:����������:-1474',79:'423:�����������:-979',81:'264:�������:-1881',82:'261:����������:-1287',83:'218:�����:-132',84:'960:��������:-616',85:'65:��������:-22',86:'1767:��������:-2432',87:'1868:�������� � ������:-440',88:'234:�������:-2476',89:'855:��������:-242',90:'964:����:-649',91:'973:�������:-1496',92:'82:����� �����:-2245',93:'686:��������:-374',94:'245:������-�����:-1925',95:'507:������:-847',96:'1473:�������:-2399',98:'213:�����:-528',99:'962:��������:-1463',100:'1784:����-������� � ���������:-2619',101:'95:������:-11',102:'291:�������:-715',103:'676:�����:-1089',105:'53:����:-748',106:'94:���-�����:-2641',107:'965:������:-2487',108:'1787:������-����:-473',109:'975:�����:-1848',110:'253:�������:-2101',111:'211:����� �����:-2741',112:'856:����:-451',113:'597:�������:-2663',114:'258:��������:-638',115:'212:�������:-2333',116:'503:���������:-1639',117:'240:�������������� ������:-1507',118:'231:�������:-2068',119:'225:���-�\�����:-1661',120:'267:��������:-2707',121:'93:����������:-2311',122:'84:�������:-968',124:'389:���������:-1353',125:'853:�����:-2597',126:'252:������:-1364',127:'967:�����:-1672',128:'263:��������:-2046',129:'254:�����:-2630',130:'968:����:-2454',131:'376:�������:-594',133:'691:����������:-1738',134:'1264:�������:-1980',135:'501:�����:-484',136:'60:��������:-1870',137:'266:������:-2190',138:'670:��������� �����:-2784',139:'243:�����, ��������������� ����������:-1518',140:'386:��������:-1221',141:'593:�������:-1188',142:'350:���������:-275',143:'880:���������:-1771',144:'378:���-������:-2123',145:'688:������:-286',146:'241:�����:-880',147:'92:��������:-2035',148:'63:���������:-1815',149:'222:����������:-253',150:'382:����������:-2167',151:'1268:������� � �������:-869',152:'260:������:-1595',153:'591:�������:-1650',154:'598:�������:-2608',155:'269:������ � �������:-1430',156:'502:���������:-935',157:'974:�����:-462',158:'62:���������:-1958',159:'257:�������:-1892',161:'421:��������:-2212',163:'674:�����:-1749',164:'220:������:-627',165:'223:����:-2520',166:'685:�����:-2300',167:'976:��������:-2553',168:'229:�����:-1298',170:'255:��������:-2289',171:'251:�������:-2443',172:'250:������:-2674',173:'216:�����:-539',174:'232:������-�����:-737',175:'221:�������:-2134',177:'233:����:-2112',178:'354:��������:-1991',179:'679:�����:-1859',180:'977:�����:-110',182:'268:���������:-2278',183:'58:���������:-1056',184:'966:���������� ������:-33',185:'1246:��������:-1573',186:'595:��������:-2344',187:'230:��������:-2179',188:'678:�������:-1265',189:'238:����-�����:-2652',190:'265:������:-2145',191:'244:������:-1947',192:'963:�����:-1826',193:'235:���:-814',194:'592:������:-803',195:'228:����:-605',196:'673:������:-1683',197:'57:��������:-330',198:'505:���������:-154',199:'387:������ � �����������:-1584',200:'237:�������:-2057',201:'677:���������� �������:-1067',202:'680:�����:-231',203:'239:���-���� � ��������:-2388',206:'262:�������:-264',207:'66:�������:-957',208:'86:�����:-825',209:'20:������:-2201',210:'355:�������:-1034',211:'1684:������������ �����:-1562',212:'1242:������:-363',213:'55:��������:-770',214:'36:�������:-682',215:'509:�����:-319',216:'590:���������:-407',217:'224:������:-2575',218:'852:�������:-2696',219:'45:�����:-1386',220:'1809:������������� ����������:-1529',221:'91:�����:-1694',222:'98:����:-2013',223:'353:��������:-1969',224:'357:����:-561',225:'242:�����:-1793',226:'506:�����-����:-2090',227:'225:���-�\'�����:-1661',228:'961:�����:-1254',229:'356:������:-1551',230:'52:�������:-2024',231:'377:������:-913',232:'227:�����:-550',233:'47:��������:-836',234:'675:����� � ����� ������:-1485',235:'51:����:-946',236:'351:����������:-517',237:'40:�������:-671',238:'850:�������� �����:-1804',239:'381:������:-2465',240:'249:�����:-352',241:'992:�����������:-187',242:'886:�������:-506',243:'66:�������:-957',244:'993:������������:-2542',245:'256:������:-1166',246:'385:��������:-902',247:'56:����:-1342',248:'41:���������:-1320',249:'46:������:-385',250:'27:���:-2355'};
     
    var countryPhoneCodesQiwi = {0:'7:������:-660',1:'77:���������:-1210'};
   //����������� ������  CPhoneCodesCountries
    /**
    *  �������� ������������� CMultiLevelDropDown  � ��������� ������������� ��� ������ ��������� ������
    * @param HtmlDivElement htmlDiv
    * @param Array           cssSelectors
    */
    function CPhoneCodesCountries(htmlDiv, cssSelectors) {
        this.initMultilevelDropDown(htmlDiv, cssSelectors);        //����������� �� CMultiLevelDropDown
        this.shadow.setStyle("width", "310px");
        this.countryCode = 7;
        this.outerDiv.getElements("input[type=hidden]").dispose();
        // �� ����� ���� ���������� � ��� ��� ��� ��� ������ ��������� �������� @todo ���������� ���������
        if(this.outerDiv.hasClass("b-combo__input_disabled")) {
            var code = this.parseCode(this.b_input.value);
            this.b_input.value = this.b_input.value.replace("+", "");
            var style = "background-position: 0px "+ code.data.split(":")[2] + "px; cursor: pointer;"
            this.setCode(style, '');
        }
    }
    CPhoneCodesCountries.prototype = new CMultiLevelDropDown();    //����������� �� CMultiLevelDropDown
    /**
     * �������� �� ������ ���
     * @param v - ���������� �����
     * @return mixed {idx: ��� ������, data: "���:��������������:������� ����� ������ �� ����������� ������"} or FALSE
     */
    CPhoneCodesCountries.prototype.parseCode = function(v) {
        var _countryPhoneCodes = window[ this.initVarName ];
        function getCountryPhoneCodes(N) {
            for (var i in _countryPhoneCodes) {
                if (String(_countryPhoneCodes[i]).indexOf(":") != -1) {
                    var j = _countryPhoneCodes[i].split(":")[0];
                    if (j == N) {
                        return _countryPhoneCodes[i];
                    }
                }
            }
            return -1;
        }
        var L = 4;
        if (v.indexOf("+") == 0) {
            L = 5;
        }
        var sbstr = v.substring(0, L);
        for (var i = L; i > -1; i--) {
            var idx = sbstr.substring(0, i);
            idx = idx.replace("+", "").trim();
            var data = getCountryPhoneCodes(idx);
            if (idx.length && data != -1) {
                return {data:data, idx:idx};
            }
        }
        return false;
    }
    
    /**
    * ����� ���� ��� ������ ������ ���������, ������������� ����������� ��������� �������
    * @param v - �������� �������� input.value
    * @param n - ����� �������
    */
    CPhoneCodesCountries.prototype.fillColumn = function(v, n) {
        var o = this.parseCode(v);
        if (!o) {
            return;
        }
        var idx = o.idx;
        var data = o.data;
        var a = data.split(":");
        this.setCode("background-position: 0px " + a[2] + "px; cursor: pointer;", idx, true, false);
        var re = new RegExp("^\\+?\\d{" + String(idx).length + "}\\s?");
        this.b_input.value = this.b_input.value.replace(re, "+" + this.countryCode + (this.lastKey == 8?'':""));
        var ls = this.columns[0].getElements("li");
        ls.removeClass(this.HOVER_CSS);
        for (var i = 0; i < ls.length; i++) {
            if ( ls[i].getElement("span").getProperty("dbid") == idx) {
                ls[i].addClass(this.HOVER_CSS);
                break;
            }
        }
        
    }
    /**
    * ����� ���� ��� ������ ������ ���������, ������������� ����������� ��������� �������
    */
    CPhoneCodesCountries.prototype.setEventListeners = function() {
        this.outerDiv.setProperty("valueContainer", "li");
        var toggler = this.outerDiv.getElement('span.b-combo__tel');
        if (!toggler) toggler = this.outerDiv;
        this.toggler = toggler;
        toggler.self = this;
        toggler.addEvent('click', this.onToggle);
        this.b_input.addEvent('click', function() {this.self.show(0);} );
        
        this.b_input.self = this;
        this.b_input.addEvent("keyup", this.onKeyUp);
        this.b_input.addEvent("blur", function ()  {
            var self = this.self;
            self.fillColumn(self.b_input.value, 0);
            if ( self.outerDiv.hasClass("b-combo__input_disabled") ) {
                return;
            }
            if (self.b_input.value == "" && !self.isEmpty() && (!self.ALLOW_CREATE_VALUE || self.selectors.indexOf(" disallow_null") != -1)) {
                self.err =1;
            }
        });
        toggler.self = this;
        
        if ( parseInt( this.b_input.value.replace(/[\D]/, '') ) ) {
            var v = this.b_input.value;
            this.onKeyUp({code:0});
            var a = [], L = 23, c = 0, n = 0;
            for (var i = v.length - 1; i > -1; i--, c++, n++) {
                a.push(v.charAt(i) );
                if (c == L && n < 11) {
                    a.push(' ');
                    c = 0;
                }
            }
            a = a.reverse();
            v = a.join('');
            this.b_input.value = "+" + v;
        }
    }
    
    /**
    *@param int index              - ������������� �������� �� ������� ��
    *@param String       value     - ������������ ��������
    *@param unsigned int column    - ������� �����������. 
    *@param int          parentId  - ������������� ������-�������� � ������� ��.
    *@param Bool         nocache    = false   - ���������� �� � this.columnsCache.
    *@param Bool         append     = true    - ��������� �� � ������.
    *@param Bool         clickable  = true    - ��������� �� ������� onclick.
    *@param String       extendsSelectors  = '' ���� �������� �� ����, ����� ��������� � ������� class
    */
    CPhoneCodesCountries.prototype.addItem = function (index, text, column, parentId, nocache, append, clickable, extendsSelectors) {
        index = parseInt(index);
        if (!index) {
            index = 0;
        }
        if (this.labels[column]) {
            if (this.labels[column].id  == index) text = this.labels[column].text;
        }
        if (this.exclude[column]) {
            if (this.exclude[column].id == index) return;
        }
        if (!extendsSelectors) extendsSelectors = '';
            else extendsSelectors = ' ' + extendsSelectors;
        var ul = this.columns[column];
        if (!ul) {         
            this.appendColumn(this.defaultColumnCss, this.row);
            ul = this.columns[column];
        }
        var td = ul.parentNode;
        td.style.display = "";
        if (String(append) == "undefined")    append = 1;
        if (String(clickable) == "undefined") clickable = 1;
        
        
        if (append) {
            var content = text.split(":");
            var li = new Element('li', {'class':'b-combo__item b-combo__txt b-combo__txt_tel', 'html':(content[1] + " +" + content[0])});
            li.inject(ul, 'bottom');
            var span = new Element('span', {'class':'b-combo__flag' + extendsSelectors, 'html':''});
            span.inject(li, 'top');
            if (extendsSelectors.indexOf(this.HOVER_CSS) != -1) {
                var w = this.outerDiv.getStyle("width");
                if (w) {
                    span.setStyle("min-width",  w);
                }
            }
            span.setStyle("background-position",  "0 " + content[2] + "px");
            span.setProperty('dbid' , content[0]);
            if (parentId) span.setProperty('dbprid' , parentId);
            span.self = this;
            li.self = this;
            if (clickable) {
                //span.addEvent('click', this.onItemClick);
                li.addEvent('click', this.onItemClick);
                li.addEvent('mouseover', this.onItemHover);
                span.setStyle("cursor", "pointer");
                li.setStyle("cursor", "pointer");
            }else {
                span.setStyle("cursor", "default");
                li.setStyle("cursor", "default");
            }
        }
    }
    
    /**
    * ������ ���������, � ������� ����� ������������� ������� ������
    */
    CPhoneCodesCountries.prototype.buildTable = function() {
     var p = this.extendElementPlace;
     this.columns      = new Array();
     this.columnsCache = new Array();
     var ul = new Element('ul', {'class':'b-combo__list b-combo__body_overflow-x_yes'});
     ul.inject(p, 'bottom');
     this.columns.push(ul);
     this.columnsCache.push(new Array());
     this.breadCrumbs.push(-1);
     this.defaultColumnCss = 'b-layout__right b-layout__right_bordleft_cdd1d3 b-layout__right_hide';
    }
    
    /**
     *@param Bool flag ����������, ������ �� ���������� ������������� ��� ������ ������
    * listener (callback)
    */
    CPhoneCodesCountries.prototype.onItemClick = function (span, flag) {
        var item = this;
        if (flag) {
            item = span;
        }
        if ( item.tagName.toLowerCase() == "li" ) {
            item = item.getElement("span");
        }
        var self = item.self;
        var style = item.getProperty("style");
        var code = item.getProperty("dbid");
        localStorage.setItem("phoneList_style", style);
        localStorage.setItem("phoneList_code", code);
        self.setCode(style, code);
        if (flag) {
            self.show(0);
        }
        try {
            self.onchangeHandler();
        }catch (e) {
            ;
        }
    }
    /**
     * ����������� ��� ������ �� ������, ������������� ���� �����. ������ �� �������
     * @param String style �������� �������� style ��� ������ �����
     * @param Number code �������� ���� ������
     * @param Bool key  true ���� �������������� ��� ��� ������ ������ � ���������� (� �� ������������ ���� ����)
     * @param Bool setFocus = true ���������, ���������� �� ����� �� �������� ����� ����� ����� ����
     */
    CPhoneCodesCountries.prototype.setCode = function (style, code, key, setFocus) {
        if (String(setFocus) == "undefined") {
            setFocus = true;
        }
        var _code = this.countryCode;
        if ( String(_code).indexOf(code) == 0 || String(code).indexOf(_code) == 0  ) {
            if ( code.length > _code.length && ( this.b_input.value.indexOf('+' + code) == 0 ) ) {
                _code = code;
            }
        }
        var re = new RegExp("^\\+" + _code + "\\s?");
        if ( this.b_input.value.indexOf("+" + this.countryCode) == -1 && key != true) {
            this.b_input.value = "+" + this.countryCode + (this.lastKey == 8?'':" ") + this.b_input.value;
        }
        this.countryCode = code;
        var s = this.b_input.value.replace(re, "+" + this.countryCode);
        if ( parseInt(code) && localStorage.getItem("phoneList_code") == code) {
            style = localStorage.getItem("phoneList_style");
        } 
        this.toggler.getElement("span.b-combo__flag").setProperty("style", style );
        this.b_input.value = s;
        if ( setFocus ) {
            this.b_input.focus();
        }
    }
    /**
     * ���� �� ����� ����� ����� ��� ��� � ��������� ��������
     * @return bool tru� ���� ��� �� ������� �����
     * */
    CPhoneCodesCountries.prototype.isInvalidCode =  function () {
        if ( !this.parseCode(this.b_input.value) ) {
            return true;
        }
        return false;
    }
    //����� ����������� ������ CPhoneCodesCountries
    
