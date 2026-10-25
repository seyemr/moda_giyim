( function () {
	'use strict';

	const settings = window.modaCommerceTools || {};
	const storageKey = 'moda-atelier-favorites';
	const feedbackSelector = '.moda-commerce-feedback';

	function getStoredIds() {
		try {
			const value = JSON.parse( window.localStorage.getItem( storageKey ) || '[]' );
			return Array.isArray( value ) ? Array.from( new Set( value.map( String ).filter( function ( id ) {
				return /^\d+$/.test( id ) && Number( id ) > 0;
			} ) ) ) : [];
		} catch ( error ) {
			return [];
		}
	}

	function saveIds( ids ) {
		try {
			window.localStorage.setItem( storageKey, JSON.stringify( Array.from( new Set( ids.map( String ) ) ) ) );
			return true;
		} catch ( error ) {
			return false;
		}
	}

	function updateButtons( ids ) {
		document.querySelectorAll( '.ma-favorite-toggle[data-product-id]' ).forEach( function ( button ) {
			const selected = ids.includes( String( button.dataset.productId ) );
			button.setAttribute( 'aria-pressed', String( selected ) );
			button.setAttribute( 'aria-label', selected ? button.dataset.labelRemove : button.dataset.labelAdd );
		} );
		document.querySelectorAll( '.ma-favorite-count' ).forEach( function ( badge ) {
			badge.textContent = String( ids.length );
			const link = badge.closest( 'a' );
			if ( link ) {
				link.classList.toggle( 'has-items', ids.length > 0 );
			}
		} );
	}

	function showFeedback( message ) {
		let feedback = document.querySelector( feedbackSelector );
		if ( ! feedback ) {
			feedback = document.createElement( 'p' );
			feedback.className = 'moda-commerce-feedback';
			feedback.setAttribute( 'role', 'status' );
			const product = document.querySelector( '.product .summary' ) || document.querySelector( '.woocommerce-MyAccount-content' );
			if ( product ) {
				product.prepend( feedback );
			}
		}
		if ( feedback ) {
			feedback.textContent = message;
		}
	}

	async function persistWishlist( productId, enabled ) {
		if ( ! settings.loggedIn ) {
			return true;
		}
		const body = new URLSearchParams( {
			action: 'moda_commerce_wishlist',
			nonce: settings.nonce,
			product_id: productId,
			enabled: enabled ? '1' : '0',
		} );
		const response = await fetch( settings.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString(),
		} );
		const result = await response.json();
		if ( ! response.ok || ! result.success ) {
			throw new Error( result.data && result.data.message ? result.data.message : settings.i18n.error );
		}
		return true;
	}

	function initializeWishlist() {
		const localIds = getStoredIds();
		const serverIds = Array.isArray( settings.wishlistIds ) ? settings.wishlistIds.map( String ).filter( function ( id ) {
			return /^\d+$/.test( id ) && Number( id ) > 0;
		} ) : [];
		const ids = Array.from( new Set( localIds.concat( serverIds ) ) );
		saveIds( ids );
		updateButtons( ids );
		if ( settings.loggedIn ) {
			ids.forEach( function ( id ) {
				if ( ! serverIds.includes( id ) ) {
					persistWishlist( id, true ).catch( function () {
						showFeedback( settings.i18n.error );
					} );
				}
			} );
		}
	}

	document.addEventListener( 'click', function ( event ) {
		const removeButton = event.target.closest( '.moda-wishlist-remove' );
		if ( removeButton ) {
			const productId = String( removeButton.dataset.productId || '' );
			const ids = getStoredIds().filter( function ( id ) { return id !== productId; } );
			saveIds( ids );
			persistWishlist( productId, false ).then( function () {
				const row = removeButton.closest( 'li' );
				if ( row ) {
					row.remove();
				}
				updateButtons( ids );
				const list = document.querySelector( '.moda-commerce-wishlist ul' );
				if ( list && ! list.children.length ) {
					list.outerHTML = '<p>Henüz favori ürününüz yok.</p>';
				}
			} ).catch( function ( error ) {
				showFeedback( error.message );
			} );
			return;
		}

		const button = event.target.closest( '.ma-favorite-toggle[data-product-id]' );
		if ( ! button ) {
			return;
		}
		const productId = String( button.dataset.productId || '' );
		const themeFavoritesGrid = document.querySelector( '.ma-favorites-grid' );
		window.setTimeout( function () {
			let ids = getStoredIds();
			let enabled = button.getAttribute( 'aria-pressed' ) === 'true';
			if ( ! themeFavoritesGrid ) {
				enabled = ! ids.includes( productId );
				ids = enabled ? ids.concat( productId ) : ids.filter( function ( id ) { return id !== productId; } );
				saveIds( ids );
				updateButtons( ids );
			}
			persistWishlist( productId, enabled ).then( function () {
				showFeedback( enabled ? settings.i18n.added : settings.i18n.removed );
			} ).catch( function ( error ) {
				showFeedback( error.message );
			} );
		}, 0 );
	} );

	function initializeFreeShipping() {
		const wrap = document.querySelector( '.moda-free-shipping' );
		if ( ! wrap || ! window.wp || ! window.wp.data ) {
			return;
		}
		const threshold = parseFloat( wrap.dataset.threshold );
		const text = wrap.querySelector( '.moda-free-shipping__text' );
		const track = wrap.querySelector( '.moda-free-shipping__track' );
		const bar = track.querySelector( 'span' );
		let lastAmount = null;
		window.wp.data.subscribe( function () {
			const store = window.wp.data.select( 'wc/store/cart' );
			const totals = store && store.getCartTotals ? store.getCartTotals() : null;
			if ( ! totals || ! totals.total_items ) {
				return;
			}
			const divisor = Math.pow( 10, totals.currency_minor_unit || 0 );
			const amount = Math.max( 0, ( Number( totals.total_items ) - Number( totals.total_discount || 0 ) + Number( totals.total_items_tax || 0 ) - Number( totals.total_discount_tax || 0 ) ) / divisor );
			if ( amount === lastAmount ) {
				return;
			}
			lastAmount = amount;
			const remaining = Math.max( 0, threshold - amount );
			const percent = Math.min( 100, Math.floor( amount / threshold * 100 ) );
			const formatted = new Intl.NumberFormat( document.documentElement.lang || 'tr-TR', { style: 'currency', currency: totals.currency_code || 'TRY' } ).format( remaining );
			text.textContent = remaining > 0 ? 'Ücretsiz kargo için ' + formatted + ' daha ekleyin.' : 'Tebrikler, kargo ücretsiz!';
			track.setAttribute( 'aria-valuenow', String( percent ) );
			bar.style.width = percent + '%';
		} );
	}

	function initialize() {
		initializeWishlist();
		initializeFreeShipping();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initialize );
	} else {
		initialize();
	}
} )();
