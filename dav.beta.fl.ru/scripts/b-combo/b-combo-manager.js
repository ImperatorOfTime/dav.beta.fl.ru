/**
* ������� ��:
* b-combo-dynamic-input.js
* b-combo-multidropdown.js
* b-combo-calendar.js
*/
var B_COMBO_AJAX_SCRIPT = '/b_combo_ajax.php';
var CComboboxManager = new function() {        
		/*��� ���������� ������ ���� ���������� ���������� �������� � ������ ��������������� ����, ���
		 ����     - css ��������, ������������ � html ������� ��� ��� ���������� (��� "-" � ��������� �������� �� "_"), 
		 �������� - JavaScript �����, ����������� �������� � ������� ���������� ������ ����
		*/
		var FactoryConfig = {
			b_combo__input_multi_dropdown : 'CMultiLevelDropDown', 
			b_combo__input_calendar       : 'CCalendarInput',
			b_combo__input_dropdown	      : 'CAutocompleteInput',
			b_combo__input_phone_countries_dropdown : 'CPhoneCodesCountries'
		}
		var BASIS_COMBO_CLASS = 'b-combo__input';                       //������� ����� css ��������� ���������� ����������, ������������ �� ������� ��� ��������� (��. ����������� ������������)
		var basisTemplate = '<div class="b-combo__input"> \
								<input class="b-combo__input-text" type="text" /> \
								<label class="b-combo__label" ></label> \
							</div>';
		//private: 
		var list = new Array();	//������ �����������, ��� ��������� �� �������� ���������� ���������� � ����
		var instance; 			//��� ���������� singleton
        // �����������
        function CComboboxManager() {
                if ( !document.instance ) {
                        document.instance = instance = this;                        
						window.addEvent("domready", initInputs);
				}
                else return instance;                 
        } 		
		//public:		
		//type ����� ��������� �������� css ��������� (����������), ������������ ���  � �������� ����������
		//(��. ����������� ������������)
		/**
		*  @param     DOMNode parentDOMNode
		*  @param     String   type
		*  @param     String   id = undefined ���������� ������� id ������������ input
		*  @return  CDynamicInput   //��� ��� �����������
		**/
        CComboboxManager.prototype.append = function(parentDOMNode, type, id) {
			return injectElement(parentDOMNode, type, 'bottom', id);			
		};
		
		/**
		*  @param     DOMNode parentDOMNode
		*  @param     String   type
		*  @param     String   id = undefined ���������� ������� id ������������ input
		*  @return  CDynamicInput   //��� ��� �����������
		**/
        CComboboxManager.prototype.prepend = function(parentDOMNode, type, id) {
			return injectElement(parentDOMNode, type, 'top', id);
		};

		/**
		*@param     String id - ������������� �������� input. 
		* �������� combobox � ��� ������, ���� input �������� � div � class="b-combo__input" ������� � ���� ������� 
		*������ � div � class = "b-combo" 
		**/
        CComboboxManager.prototype.remove = function(id) {
			var o   = $(id);
			var div = o.getParent(".b-combo__input");
			if (div) {
				for (var i = 0; list.length; i++) {
					if (list[i].outerDiv === div) {
						list.splice(i, 1);
						break;
					}
				}
			}
			var div = div.getParent('.b-combo');
			if (div) {
				div.dispose();
			}
		}
		
		/**
		* ���������� ������ ����������
		**/
        CComboboxManager.prototype.getList = function() {
			return list;
		}
		
		/**
		* ���������� ������������ ���������
		* @param id - id ������, ��������� �������� ����� ��������
		**/
        CComboboxManager.prototype.getInput = function(id) {
			for  (var i = 0; i < list.length; i++) {
				if (list[i].b_input.id == id) return list[i];
			}
			return false;
		}
		
		/**
		* ������� ���������� ���������� ����� 
		*/
        CComboboxManager.prototype.setDefaultValue = function(id, value, tableId) {
			for (var i = 0; i < list.length; i++) {
				if (list[i].id() == id) {
					list[i].setDefaultValue(value, tableId);
					break;
				}
			}
		}
		
		CComboboxManager.prototype.createCombobox = function (div) {
			var ls = getListCssSelectors(div);			
			for (var i = 0; i < ls.length; i++) {
				if (String(FactoryConfig[ls[i]]) != "undefined") {
					return new FactoryConfig[ls[i]](div, ls);					
				}
			}			
			return new CDynamicInput(div, ls);			
		}
        
        CComboboxManager.prototype.initCombobox = function(ls) {		
			for (var i = 0; i < ls.length; i++) {
				list.push(createCombobox(ls[i]));
			}
        }

        CComboboxManager.prototype.initInputs = function() {
            list = new Array();		
	        initInputs();
        }
		
		//private:
		function initInputs() {
			var ls = $$('.' + BASIS_COMBO_CLASS);			
			for (var i = 0; i < ls.length; i++) {
				list.push(createCombobox(ls[i]));
			}
		}

		function createCombobox(div) {
			var ls = getListCssSelectors(div);			
			for (var i = 0; i < ls.length; i++) {
				if (window[FactoryConfig[ls[i]]] instanceof Function) {
					return new window[FactoryConfig[ls[i]]](div, ls);
				}
			}			
			return new CDynamicInput(div, ls);			
		}
		
		function getListCssSelectors(HtmlDivElement) {
			var s = HtmlDivElement.getProperty('class');
			s = s.replace(/\-/gi, "_");
			return s.split(new RegExp('\\s+', 'gi'));			
		}
				
		function injectElement(parentDOMNode, type, place, id) {
			var tpl = basisTemplate;
			type = type.replace(new RegExp('\\b' + BASIS_COMBO_CLASS + '\\b', 'gi'), '');
			//tpl  = tpl.replace('{extend}', ' ' + type);				
			var div = new Element('div', {'class': 'b-combo'});
			//div.set('html', tpl);
			div.inject(parentDOMNode, place);
			var b_div = new Element('div', {'class': BASIS_COMBO_CLASS});
			b_div.inject(div, 'top');
			b_div.addClass(type);
			var i = new Element('input', {'class': 'b-combo__input-text', 'type':'text'});
			i.inject(b_div, 'top');
			if (id) i.setProperty('id', id);
			var l = new Element('label', {'class': 'b-combo__label'});
			l.inject(i, 'after');			
			list.push(createCombobox(b_div));
			return list[list.length - 1];
		}     
		
        return CComboboxManager;
}

var ComboboxManager = new CComboboxManager();

//=======================================================
//��������� append / prepend / remove
function prepend() {
	d = $('container');
	ComboboxManager.prepend(d, 'b-combo__input_width_100 b-combo__input_max-width_400 b-combo__input_resize');
}

function append() {
	d = $('container');
	ComboboxManager.append(d, 'b-combo__input_width_100 b-combo__input_max-width_400 b-combo__input_resize');
}

function remove() {
	ComboboxManager.remove('c1');
}

//�������� ���� ���������� ����� ���������������� ���� � ������� 
//(� ��������������� div �������� ��������� b_combo__input_init_specdata)
/*
* ������ ����������� JavaScript �������������� ������  �� ���� �������
*/
var threeData = {1:"���������",  2:"������", 
				 3: {0:"������", //������ ������� � ������ ���������� ������� ���������� ������� ������������� ����
                                                  //�� ���� id ������ = 3, parentId ������ = 0 ��� ��� � ������� ����������� ����������� ��� 
					  31:{     //31 - ��� ������������� ������ ������.
						   3:"������", // ������ ������� � ������ ���������� ������� ���������� ������� ������������� 
                                                               // ���� ��� ���� - ��� parentId, � ������ ������ id ������
						   311:"��.��������",
						   312:"��.�������",
						   313:"��.�������",
						   314:"��.������"
					   }, 
					   32:"�����-���������", 
					   33: "���������",
					   34: "������",
					   35: "�������"
					},
				4: {  0: "������",
					  41: "�������",
					  42: "������"
					}
};
