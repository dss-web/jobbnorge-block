/**
 * Jobbnorge Block Pagination JavaScript
 *
 * Handles AJAX pagination for job listings.
 */

/* eslint-env browser */
( function () {
	'use strict';

	// Initialize pagination when DOM is ready
	document.addEventListener( 'DOMContentLoaded', function () {
		initializePagination();
	} );

	/**
	 * Initialize pagination functionality
	 */
	function initializePagination() {
		// We look for each wrapper that contains a UL with data-attributes.
		const wrappers = document.querySelectorAll(
			'.wp-block-dss-jobbnorge__wrapper'
		);

		wrappers.forEach( function ( wrapper ) {
			const instanceId = wrapper.getAttribute( 'data-block-instance' );
			const listEl = wrapper.querySelector(
				'ul.wp-block-dss-jobbnorge[data-attributes]'
			);
			const paginationEl = wrapper.querySelector(
				'.wp-block-dss-jobbnorge__pagination'
			);
			if ( ! listEl || ! paginationEl ) return;

			// Get block attributes from data attribute on UL
			const attributesData = listEl.getAttribute( 'data-attributes' );
			if ( ! attributesData ) return;

			let attributes;
			try {
				attributes = JSON.parse( attributesData );
			} catch ( e ) {
				// eslint-disable-next-line no-console
				console.error( 'Failed to parse block attributes:', e );
				return;
			}

			// Add event listeners to pagination buttons
			const prevButton = paginationEl.querySelector(
				'.wp-block-dss-jobbnorge__pagination-prev'
			);
			const nextButton = paginationEl.querySelector(
				'.wp-block-dss-jobbnorge__pagination-next'
			);

			if ( prevButton && ! prevButton.disabled ) {
				prevButton.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					const page = parseInt( this.getAttribute( 'data-page' ) );
					loadPage( page, attributes, wrapper, instanceId );
				} );
			}

			if ( nextButton && ! nextButton.disabled ) {
				nextButton.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					const page = parseInt( this.getAttribute( 'data-page' ) );
					loadPage( page, attributes, wrapper, instanceId );
				} );
			}
		} );
	}

	/**
	 * Load a specific page via AJAX
	 *
	 * @param {number}  page       - Page number to load
	 * @param {Object}  attributes - Block attributes
	 * @param {Element} container  - Block container element
	 */
	function loadPage( page, attributes, container, instanceId ) {
		// Show loading state
		container.classList.add( 'wp-block-dss-jobbnorge__loading' );

		// Disable pagination buttons during loading
		const buttons = container.querySelectorAll(
			'.wp-block-dss-jobbnorge__pagination button'
		);
		buttons.forEach( function ( button ) {
			button.disabled = true;
		} );

		// Prepare AJAX data
		const formData = new FormData();
		formData.append( 'action', 'jobbnorge_get_jobs' );
		formData.append( 'page', page );
		formData.append( 'attributes', JSON.stringify( attributes ) );
		formData.append( 'nonce', window.jobbnorgeAjax?.nonce || '' );

		// Make AJAX request
		fetch( window.jobbnorgeAjax?.ajaxUrl || window.ajaxurl || '', {
			method: 'POST',
			body: formData,
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( data ) {
				if ( data.success ) {
					// Replace wrapper
					container.outerHTML = data.data.html;
					// Query the updated instance
					const selector = '.wp-block-dss-jobbnorge__wrapper[data-block-instance="' + instanceId + '"]';
					const newWrapper = document.querySelector( selector );
					if ( newWrapper ) {
						// If consumer explicitly disables auto scroll on this instance, skip.
						if ( newWrapper.hasAttribute( 'data-no-autoscroll' ) ) {
							initializePagination();
							updateURL( page );
							return;
						}
						// Update status region announcement if present
						const statusRegion = newWrapper.querySelector( '.jobbnorge-pagination-status' );
						if ( statusRegion ) {
							// Force polite announcement by briefly clearing then resetting text (some AT need mutation)
							const current = statusRegion.textContent;
							statusRegion.textContent = '';
							statusRegion.textContent = current;
						}
						// Scroll near this wrapper only if it is not already mostly in view
						try {
							const rect = newWrapper.getBoundingClientRect();
							const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
							const thresholdAttr = parseFloat( newWrapper.getAttribute( 'data-autoscroll-threshold' ) || '0.25' );
							const threshold = isNaN( thresholdAttr ) ? 0.25 : thresholdAttr;
							const mostlyVisible = rect.top >= 0 && rect.top < viewportHeight * threshold;
							if ( ! mostlyVisible ) {
								const twoEm = parseFloat( window.getComputedStyle( document.documentElement ).fontSize ) * 2;
								window.scrollTo( {
									top: window.pageYOffset + rect.top - twoEm,
									behavior: 'smooth',
								} );
							}
						} catch ( _e ) {
							// Fail silently if measurements are not available.
						}
					}
					// Reinitialize others (bind new pagination controls for this wrapper)
					initializePagination();
					updateURL( page );
				} else {
					// eslint-disable-next-line no-console
					console.error( 'AJAX request failed:', data.data );
					showError(
						container,
						'Failed to load page. Please try again.'
					);
				}
			} )
			.catch( function ( error ) {
				// eslint-disable-next-line no-console
				console.error( 'AJAX request error:', error );
				showError( container, 'An error occurred while loading the page.' );
			} )
			.finally( function () {
				// Remove loading state
				container.classList.remove( 'wp-block-dss-jobbnorge__loading' );
			} );
	}

	/**
	 * Update URL with page parameter
	 *
	 * @param {number} page - Current page number
	 */
	function updateURL( page ) {
		if ( window.history.pushState ) {
			const url = new URL( window.location );
			if ( page > 1 ) {
				url.searchParams.set( 'jobbnorge_page', page );
			} else {
				url.searchParams.delete( 'jobbnorge_page' );
			}
			window.history.pushState( { page }, '', url );
		}
	}

	/**
	 * Show error message
	 *
	 * @param {Element} container - Block container
	 * @param {string}  message   - Error message
	 */
	function showError( container, message ) {
		const errorDiv = document.createElement( 'div' );
		errorDiv.className =
			'wp-block-dss-jobbnorge__error notice notice-error';
		errorDiv.innerHTML = '<p>' + message + '</p>';

		// Insert error message at the top of the container
		container.insertBefore( errorDiv, container.firstChild );

		// Remove error message after 5 seconds
		setTimeout( function () {
			if ( errorDiv.parentNode ) {
				errorDiv.parentNode.removeChild( errorDiv );
			}
		}, 5000 );
	}

	// Handle browser back/forward buttons
	window.addEventListener( 'popstate', function ( event ) {
		if ( event.state && event.state.page ) {
			// Reload the page with the correct page number
			window.location.reload();
		}
	} );
} )();
