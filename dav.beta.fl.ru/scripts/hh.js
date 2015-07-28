window.addEvent( 'domready', function(){

	function getUserType()
	{ // ����������, ��� ������ ���� ������������ ������������ ������ ���� �������� �������
	  // emp - ������������
	  // frl - ���������
		return $$( '.fast-switch .selected' ).getProperty('class' ).toString().replace( 'selected', '' ).replace( ' ', '' ).replace( 'fs-', '' )
	}

	function switchFunc()
	{ // ����������� ����� � ������������ � ������� ������� ��������
		$$( '.fast-info' ).setProperty( 'class',
			'fast-info fai-' + $$( '.fast-funcs-' + getUserType() + ' .selected' ).getProperty( 'class' ).toString().replace( 'ff-', '' ).replace( ' ', '').replace( 'selected', '' )
		);
	}

	// ���� �� ������������, ���������� ��������� ������������ �������
	if ( ! $$( '.logged-in' ).length )
		switchFunc();

	// ������������ ����� ������� �������� ������� ������������� � ������������

	$$( '.fast-switch li span span' ).addEvent( 'click', function(){

		var li = this.getParent().getParent();

		// ���� ������ ����� �� ������
		if ( ! li.hasClass( 'selected' ) || $$( '.fast-content' ).getStyle( 'display' ) == 'none' ) {

			// ����������� ����� � ����� �������� �������
			$$( '.fast' ).removeClass('fast-emp').removeClass('fast-frl').addClass(
				'fast-' + li.getProperty( 'class' ).replace( 'fs-', '' )
			);

			// ������ ������ ����� ���� ���������
			li.getSiblings().removeClass( 'selected' );
			li.addClass( 'selected' );

			// ����������� ����� ������������ �������
			switchFunc();

			// ���������� ������� ����� (�� ������, ���� �� ��� �����)
			$$( '.fast-content' ).setStyle( 'display', 'block' );

		} else {

			// �������� ������� �����
			$$( '.fast-content' ).setStyle( 'display', 'none' );

		}

	});

	// ������������ ������� � ����� �������� �������

	$$( '.fast-funcs li' ).addEvent( 'click', function(){

		// ���� ������ ������� �� �������
		if ( ! this.hasClass( 'selected' ) ) {

			// ��������� ��� ������������?
			var userType = getUserType();

			// ����� �������?
			var newFunc = this.getProperty( 'class' ).replace( 'ff-', '' );

			// ����������� ��������������� ����
			$$( '.fi-layers-' + userType + ' .visible' ).removeClass( 'visible' );
			$$( '.fil-' + newFunc ).addClass( 'visible' );

			// ������ ������� ���������
			$$( '.fast-funcs-' + userType + ' .selected' ).removeClass( 'selected' );
			this.addClass( 'selected' );

			// ����������� ����� ������������ �������
			switchFunc();

		}

	});

	// ������� ����� �������� �������

	$$( '.fast-close' ).addEvent( 'click', function(){

		$$( '.fast' ).setStyle( 'display', 'none' );

	});

	// ������� �����

	var footerHeight = parseInt( $$( '.footer' ).getHeight() );
	$$( '.footer' ).setStyle( 'height', footerHeight );
	$$( '.footer-fantom' ).setStyle( 'height', footerHeight );
	$$( '.footer' ).setStyle( 'margin-top', '-' + ( footerHeight + 17 ) + 'px' );

	// ������������ ���� ��������

	$$( '.project-type em' ).addEvent( 'click', function(){

		var li = this.getParent( 'li' );

		// ���� ���� ����� �� ������
		if ( ! li.hasClass( 'selected' ) ) {

			// �������� ���� �����
			$$( '.project-type .selected' ).removeClass( 'selected' );
			li.addClass( 'selected' );

			// ���������� ��������������� ������ ��������
			$$( '.project-list.visible' ).removeClass( 'visible' )
			var index = $$( '.project-type li' ).indexOf( li );
			$$( '.project-list')[index].addClass( 'visible' )

		}
	});

	// ��������-�������� ����� �����

	$$( '.trigger-login' ).addEvent( 'click', function(){
		$$( '.login-form' ).toggleClass( 'lf-hide' );
	})

	// ��������� �������

	$$( '.logged-in .catalog-list ul em' ).addEvent( 'click', function(){

		var submenu = this.getNext( 'ul' );
		var visible = submenu.hasClass( 'visible' );

		$$( '.catalog-list ul' ).removeClass( 'visible' );
		submenu.toggleClass( 'visible', ! visible );

	});


});
















