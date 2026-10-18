/**
 * Accessible mobile navigation toggle.
 */
( function () {
	'use strict';

	document.documentElement.classList.add( 'ma-js' );

	const button = document.querySelector( '.ma-menu-toggle' );
	const navigation = document.querySelector( '#site-navigation' );
	const buttonLabel = button ? button.querySelector( '.screen-reader-text' ) : null;

	if ( ! button || ! navigation ) {
		return;
	}

	const searchForm = document.querySelector( '.ma-search-form' );
	const searchToggle = document.querySelector( '.ma-search-submit' );
	const searchInput = document.querySelector( '.ma-search-input' );

	if ( searchForm && searchToggle && searchInput ) {
		const mobileSearchQuery = window.matchMedia( '(max-width: 760px)' );
		const updateSearchTabOrder = function () {
			if ( mobileSearchQuery.matches && ! searchForm.classList.contains( 'is-open' ) ) {
				searchInput.setAttribute( 'tabindex', '-1' );
			} else {
				searchInput.removeAttribute( 'tabindex' );
			}
		};
		updateSearchTabOrder();
		mobileSearchQuery.addEventListener( 'change', updateSearchTabOrder );

		searchForm.addEventListener( 'submit', function ( event ) {
			if ( window.matchMedia( '(max-width: 760px)' ).matches && ! searchForm.classList.contains( 'is-open' ) ) {
				event.preventDefault();
				searchForm.classList.add( 'is-open' );
				searchToggle.setAttribute( 'aria-expanded', 'true' );
				searchInput.removeAttribute( 'tabindex' );
				searchInput.focus();
			}
		} );
	}

	button.addEventListener( 'click', function () {
		const isExpanded = button.getAttribute( 'aria-expanded' ) === 'true';

		button.setAttribute( 'aria-expanded', String( ! isExpanded ) );
		navigation.classList.toggle( 'is-open', ! isExpanded );
		if ( buttonLabel ) {
			buttonLabel.textContent = isExpanded ? 'Menüyü aç' : 'Menüyü kapat';
		}
	} );

	navigation.addEventListener( 'click', function ( event ) {
		if ( event.target.closest( 'a' ) && window.matchMedia( '(max-width: 760px)' ).matches ) {
			button.setAttribute( 'aria-expanded', 'false' );
			navigation.classList.remove( 'is-open' );
			if ( buttonLabel ) {
				buttonLabel.textContent = 'Menüyü aç';
			}
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key === 'Escape' && searchForm && searchForm.classList.contains( 'is-open' ) ) {
			searchForm.classList.remove( 'is-open' );
			searchToggle.setAttribute( 'aria-expanded', 'false' );
			updateSearchTabOrder();
			searchToggle.focus();
		}

		if ( event.key === 'Escape' && navigation.classList.contains( 'is-open' ) ) {
			button.setAttribute( 'aria-expanded', 'false' );
			navigation.classList.remove( 'is-open' );
			if ( buttonLabel ) {
				buttonLabel.textContent = 'Menüyü aç';
			}
			button.focus();
		}
	} );

	const filterToggle = document.querySelector( '.ma-filter-toggle' );
	const filterPanel = document.querySelector( '#ma-product-filters' );

	if ( filterToggle && filterPanel ) {
		filterToggle.addEventListener( 'click', function () {
			const isExpanded = filterToggle.getAttribute( 'aria-expanded' ) === 'true';
			filterToggle.setAttribute( 'aria-expanded', String( ! isExpanded ) );
			filterPanel.classList.toggle( 'is-open', ! isExpanded );

			if ( ! isExpanded ) {
				const firstControl = filterPanel.querySelector( 'select, input, button' );
				if ( firstControl ) {
					firstControl.focus();
				}
			}
		} );

		filterPanel.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' && filterPanel.classList.contains( 'is-open' ) ) {
				filterPanel.classList.remove( 'is-open' );
				filterToggle.setAttribute( 'aria-expanded', 'false' );
				filterToggle.focus();
			}
		} );
	}

	const favoriteStorageKey = 'moda-atelier-favorites';
	const favoriteGrid = document.querySelector( '.ma-favorites-grid' );
	const favoriteEmpty = document.querySelector( '.ma-favorites-empty' );
	const favoriteFeedback = document.querySelector( '.ma-favorites-feedback' );
	const favoriteCounts = document.querySelectorAll( '.ma-favorite-count' );

	if ( ! favoriteGrid ) {
		return;
	}

	function reportStorageError() {
		if ( favoriteFeedback ) {
			favoriteFeedback.textContent = 'Favori listeniz bu tarayıcıya kaydedilemedi.';
		}
	}

	function getFavoriteIds() {
		const stored = window.localStorage.getItem( favoriteStorageKey );

		if ( ! stored ) {
			return [];
		}

		const parsed = JSON.parse( stored );
		return Array.isArray( parsed ) ? parsed.map( String ) : [];
	}

	function renderFavorites() {
		let favoriteIds;

		try {
			favoriteIds = getFavoriteIds();
		} catch ( error ) {
			reportStorageError();
			favoriteIds = [];
		}

		document.querySelectorAll( '.ma-product-card[data-product-id]' ).forEach( function ( card ) {
			const id = card.dataset.productId;
			const isFavorite = favoriteIds.includes( id );
			const toggle = card.querySelector( '.ma-favorite-toggle' );

			if ( toggle ) {
				toggle.setAttribute( 'aria-pressed', String( isFavorite ) );
				toggle.setAttribute( 'aria-label', isFavorite ? toggle.dataset.labelRemove : toggle.dataset.labelAdd );
			}
		} );

		favoriteGrid.replaceChildren();
		const seen = new Set();
		favoriteIds.forEach( function ( id ) {
			if ( seen.has( id ) ) {
				return;
			}
			seen.add( id );
			const original = Array.from( document.querySelectorAll( '.ma-product-card[data-product-id]' ) ).find( function ( card ) {
				return card.dataset.productId === id;
			} );
			if ( original ) {
				favoriteGrid.appendChild( original.cloneNode( true ) );
			}
		} );

		if ( favoriteEmpty ) {
			favoriteEmpty.hidden = favoriteGrid.childElementCount > 0;
		}

		favoriteCounts.forEach( function ( count ) {
			count.textContent = String(seen.size);
			count.closest( 'a' ).classList.toggle( 'has-items', seen.size > 0 );
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		const toggle = event.target.closest( '.ma-favorite-toggle' );

		if ( ! toggle ) {
			return;
		}

		const productId = toggle.dataset.productId;
		if ( ! productId ) {
			return;
		}

		let favoriteIds;
		try {
			favoriteIds = getFavoriteIds();
			favoriteIds = favoriteIds.filter( function ( id ) {
				return id !== productId;
			} );
			if ( toggle.getAttribute( 'aria-pressed' ) !== 'true' ) {
				favoriteIds.push( productId );
			}
			window.localStorage.setItem( favoriteStorageKey, JSON.stringify( favoriteIds ) );
		} catch ( error ) {
			reportStorageError();
			return;
		}

		renderFavorites();
	} );

	renderFavorites();
} )();
