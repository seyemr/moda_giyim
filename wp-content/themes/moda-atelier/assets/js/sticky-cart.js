( function () {
	'use strict';

	var bar = document.querySelector( '.ma-sticky-cart' );
	var form = document.querySelector( 'form.cart' );
	if ( ! bar || ! form || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	var button = bar.querySelector( '.ma-sticky-cart__button' );
	var originalButton = form.querySelector( '.single_add_to_cart_button' );
	var formVisible = true;

	function render() {
		bar.classList.toggle( 'is-visible', ! formVisible );
		bar.setAttribute( 'aria-hidden', formVisible ? 'true' : 'false' );
		button.tabIndex = formVisible ? -1 : 0;
	}

	new IntersectionObserver( function ( entries ) {
		formVisible = entries[ 0 ].isIntersecting;
		render();
	} ).observe( form );

	button.addEventListener( 'click', function () {
		var needsSelection = ! originalButton ||
			originalButton.disabled ||
			originalButton.classList.contains( 'disabled' ) ||
			originalButton.classList.contains( 'wc-variation-selection-needed' );
		if ( needsSelection ) {
			form.scrollIntoView( { behavior: window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 'auto' : 'smooth', block: 'center' } );
			return;
		}
		originalButton.click();
	} );

	render();
}() );
