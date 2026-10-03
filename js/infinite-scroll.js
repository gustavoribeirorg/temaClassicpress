/**
 * Rolagem infinita + lazy load para The ClassicPress Theme.
 *
 * - Carrega a próxima página de posts via AJAX quando o sentinel
 *   entra no viewport (IntersectionObserver).
 * - Aplica lazy load em imagens e iframes (nativos onde houver suporte,
 *   fallback via IntersectionObserver para navegadores antigos).
 * - A paginação clássica permanece no HTML como fallback sem JS.
 */
(function () {
	'use strict';

	if ( typeof window.tctInfinite === 'undefined' ) {
		return;
	}

	var settings = window.tctInfinite;
	var container = document.querySelector( '.site-content' );

	if ( ! container ) {
		return;
	}

	var currentPage = 1;
	var maxPages = parseInt( settings.maxPages, 10 ) || 1;
	var isLoading = false;
	var isDone = false;
	var sentinel = null;
	var statusEl = null;
	var retryTimer = null;
	var supportsNativeLazy = 'loading' in HTMLImageElement.prototype;
	var supportsNativeIframeLazy = 'loading' in HTMLIFrameElement.prototype;

	/* ------------------------------------------------------------------ */
	/* Lazy load                                                          */
	/* ------------------------------------------------------------------ */

	var lazyObserver = null;

	/**
	 * Retorna um IntersectionObserver reutilizável para lazy load.
	 */
	function getLazyObserver() {
		if ( lazyObserver || ! ( 'IntersectionObserver' in window ) ) {
			return lazyObserver;
		}

		lazyObserver = new IntersectionObserver(
			function ( entries, observer ) {
				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting ) {
						return;
					}

					var el = entry.target;

					if ( el.tagName === 'IMG' ) {
						var src = el.getAttribute( 'data-src' );
						var srcset = el.getAttribute( 'data-srcset' );

						if ( src ) {
							el.src = src;
						}
						if ( srcset ) {
							el.srcset = srcset;
						}
					}

					if ( el.tagName === 'IFRAME' ) {
						var iframeSrc = el.getAttribute( 'data-src' );
						if ( iframeSrc ) {
							el.src = iframeSrc;
						}
					}

					el.classList.add( 'lazy-loaded' );
					observer.unobserve( el );
				} );
			},
			{ rootMargin: '300px 0px' }
		);

		return lazyObserver;
	}

	/**
	 * Aplica lazy load em imagens e iframes dentro de um container.
	 * Funciona tanto no carregamento inicial quanto nos posts via AJAX.
	 *
	 * @param {Element} root Elemento onde procurar imagens/iframes.
	 */
	function applyLazyLoad( root ) {
		if ( ! root ) {
			return;
		}

		var images = root.querySelectorAll( 'img[data-src], img[data-srcset]' );
		var iframes = root.querySelectorAll( 'iframe[data-src]' );
		var observer = getLazyObserver();

		// Fallback nativo: só é usado se o navegador suportar.
		if ( supportsNativeLazy && ! observer ) {
			images.forEach( function ( img ) {
				img.loading = 'lazy';
				if ( img.dataset.src ) {
					img.src = img.dataset.src;
				}
				if ( img.dataset.srcset ) {
					img.srcset = img.dataset.srcset;
				}
			} );
			iframes.forEach( function ( iframe ) {
				iframe.loading = 'lazy';
				if ( iframe.dataset.src ) {
					iframe.src = iframe.dataset.src;
				}
			} );
			return;
		}

		if ( observer ) {
			images.forEach( function ( img ) {
				// Se o navegador suporta nativo, não precisa observar.
				if ( supportsNativeLazy && img.dataset.src ) {
					img.loading = 'lazy';
					img.src = img.dataset.src;
					if ( img.dataset.srcset ) {
						img.srcset = img.dataset.srcset;
					}
					return;
				}
				observer.observe( img );
			} );

			iframes.forEach( function ( iframe ) {
				if ( supportsNativeIframeLazy && iframe.dataset.src ) {
					iframe.loading = 'lazy';
					iframe.src = iframe.dataset.src;
					return;
				}
				observer.observe( iframe );
			} );
		}
	}

	/* ------------------------------------------------------------------ */
	/* Rolagem infinita                                                   */
	/* ------------------------------------------------------------------ */

	function ensureSentinel() {
		if ( sentinel ) {
			return;
		}

		var existingPagination = container.querySelector( '.pagination, .navigation.pagination, .navigation.posts-navigation' );
		if ( existingPagination ) {
			existingPagination.remove();
		}

		sentinel = document.createElement( 'div' );
		sentinel.className = 'infinite-sentinel';
		sentinel.setAttribute( 'aria-hidden', 'true' );

		statusEl = document.createElement( 'div' );
		statusEl.className = 'infinite-status';
		statusEl.setAttribute( 'role', 'status' );
		statusEl.setAttribute( 'aria-live', 'polite' );

		container.appendChild( sentinel );
		container.appendChild( statusEl );
	}

	function setStatus( message, isError ) {
		if ( ! statusEl ) {
			return;
		}
		statusEl.textContent = message || '';
		statusEl.classList.toggle( 'is-error', !! isError );
	}

	function finish() {
		isDone = true;
		setStatus( settings.noMore );
	}

	function loadMore() {
		if ( isLoading || isDone ) {
			return;
		}

		if ( currentPage >= maxPages ) {
			finish();
			return;
		}

		if ( retryTimer ) {
			window.clearTimeout( retryTimer );
			retryTimer = null;
		}

		isLoading = true;
		setStatus( settings.loading );

		var nextPage = currentPage + 1;
		var body = new FormData();
		body.append( 'action', 'tct_load_more' );
		body.append( 'nonce', settings.nonce );
		body.append( 'page', nextPage );
		body.append( 'query', settings.query );

		fetch( settings.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body,
		} )
			.then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( 'HTTP ' + response.status );
				}
				return response.text();
			} )
			.then( function ( html ) {
				var trimmed = ( html || '' ).trim();

				if ( ! trimmed ) {
					finish();
					return;
				}

				// Insere antes do sentinel para preservar a ordem.
				sentinel.insertAdjacentHTML( 'beforebegin', trimmed );
				currentPage = nextPage;

				// Aplica lazy load nos novos posts.
				// O sentinel é o próximo nó irmão do último post inserido.
				var inserted = [];
				var node = sentinel.previousElementSibling;
				while ( node && node !== sentinel && ! node.classList.contains( 'infinite-status' ) ) {
					if ( node.tagName === 'ARTICLE' ) {
						inserted.push( node );
					}
					node = node.previousElementSibling;
				}
				inserted.forEach( applyLazyLoad );

				if ( currentPage >= maxPages ) {
					finish();
				} else {
					setStatus( '' );
				}
			} )
			.catch( function () {
				setStatus( settings.error, true );
				retryTimer = window.setTimeout( function () {
					retryTimer = null;
					isLoading = false;
					loadMore();
				}, 3000 );
			} )
			.finally( function () {
				if ( ! retryTimer ) {
					isLoading = false;
				}
			} );
	}

	function initScrollFallback() {
		var onScroll = function () {
			if ( isDone ) {
				window.removeEventListener( 'scroll', onScroll );
				return;
			}
			var rect = sentinel.getBoundingClientRect();
			if ( rect.top < window.innerHeight + 300 ) {
				loadMore();
			}
		};
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
	}

	function init() {
		ensureSentinel();

		// Lazy load nos posts já presentes no HTML inicial.
		applyLazyLoad( container );

		if ( ! ( 'IntersectionObserver' in window ) ) {
			initScrollFallback();
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				if ( entries[0].isIntersecting ) {
					loadMore();
				}
			},
			{ rootMargin: '300px 0px' }
		);

		observer.observe( sentinel );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
})();
