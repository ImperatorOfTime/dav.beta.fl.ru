/*
---

name: Locale.ru-RU-unicode.Date

description: Date messages for Russian (utf-8).

license: MIT-style license

authors:
  - Evstigneev Pavel
  - Kuryanovich Egor

requires:
  - Locale

provides: [Locale.ru-RU.Date]

...
*/

(function(){

// Russian language pluralization rules, taken from CLDR project, http://unicode.org/cldr/
// one -> n mod 10 is 1 and n mod 100 is not 11;
// few -> n mod 10 in 2..4 and n mod 100 not in 12..14;
// many -> n mod 10 is 0 or n mod 10 in 5..9 or n mod 100 in 11..14;
// other -> everything else (example 3.14)
var pluralize = function (n, one, few, many, other){
	var modulo10 = n % 10,
		modulo100 = n % 100;

	if (modulo10 == 1 && modulo100 != 11){
		return one;
	} else if ((modulo10 == 2 || modulo10 == 3 || modulo10 == 4) && !(modulo100 == 12 || modulo100 == 13 || modulo100 == 14)){
		return few;
	} else if (modulo10 == 0 || (modulo10 == 5 || modulo10 == 6 || modulo10 == 7 || modulo10 == 8 || modulo10 == 9) || (modulo100 == 11 || modulo100 == 12 || modulo100 == 13 || modulo100 == 14)){
		return many;
	} else {
		return other;
	}
};

Locale.define('ru-RU', 'Date', {

	months: ['������', '�������', '����', '������', '���', '����', '����', '������', '��������', '�������', '������', '�������'],
	months_abbr: ['���', '����', '����', '���', '���','����','����','���','����','���','����','���'],
	days: ['�����������', '�����������', '�������', '�����', '�������', '�������', '�������'],
	days_abbr: ['��', '��', '��', '��', '��', '��', '��'],

	// Culture's date order: DD.MM.YYYY
	dateOrder: ['date', 'month', 'year'],
	shortDate: '%d.%m.%Y',
	shortTime: '%H:%M',
	AM: 'AM',
	PM: 'PM',
	firstDayOfWeek: 1,

	// Date.Extras
	ordinal: '',

	lessThanMinuteAgo: '������ ������ �����',
	minuteAgo: '������ �����',
	minutesAgo: function(delta){ return '{delta} ' + pluralize(delta, '������', '������', '�����') + ' �����'; },
	hourAgo: '��� �����',
	hoursAgo: function(delta){ return '{delta} ' + pluralize(delta, '���', '����', '�����') + ' �����'; },
	dayAgo: '�����',
	daysAgo: function(delta){ return '{delta} ' + pluralize(delta, '����', '���', '����') + ' �����'; },
	weekAgo: '������ �����',
	weeksAgo: function(delta){ return '{delta} ' + pluralize(delta, '������', '������', '������') + ' �����'; },
	monthAgo: '����� �����',
	monthsAgo: function(delta){ return '{delta} ' + pluralize(delta, '�����', '������', '�������') + ' �����'; },
	yearAgo: '��� �����',
	yearsAgo: function(delta){ return '{delta} ' + pluralize(delta, '���', '����', '���') + ' �����'; },

	lessThanMinuteUntil: '������ ��� ����� ������',
	minuteUntil: '����� ������',
	minutesUntil: function(delta){ return '����� {delta} ' + pluralize(delta, '������', '������', '�����') + ''; },
	hourUntil: '����� ���',
	hoursUntil: function(delta){ return '����� {delta} ' + pluralize(delta, '���', '����', '�����') + ''; },
	dayUntil: '������',
	daysUntil: function(delta){ return '����� {delta} ' + pluralize(delta, '����', '���', '����') + ''; },
	weekUntil: '����� ������',
	weeksUntil: function(delta){ return '����� {delta} ' + pluralize(delta, '������', '������', '������') + ''; },
	monthUntil: '����� �����',
	monthsUntil: function(delta){ return '����� {delta} ' + pluralize(delta, '�����', '������', '�������') + ''; },
	yearUntil: '�����',
	yearsUntil: function(delta){ return '����� {delta} ' + pluralize(delta, '���', '����', '���') + ''; }

});



})();