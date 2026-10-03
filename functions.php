<?php
/**
 * Funções do tema.
 *
 * @package the-classicpress-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configuração básica do tema.
 */
function tct_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	// Garante que o WordPress não injete atributos de lazy load
	// que possam conflitar com o nosso JS. O core já adiciona
	// loading="lazy" em imagens, mas iframes precisam de ajuda.
	add_filter( 'wp_lazy_loading_enabled', '__return_true' );

	register_nav_menus(
		array(
			'primary' => __( 'Menu principal', 'the-classicpress-theme' ),
		)
	);
}
add_action( 'after_setup_theme', 'tct_setup' );

/**
 * Enfileira o CSS do tema e o JS de rolagem infinita.
 */
function tct_scripts() {
	wp_enqueue_style(
		'tct-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);

	// Só carrega o infinite scroll na listagem/arquivo/busca.
	if ( tct_is_infinite_scroll_context() ) {
		wp_enqueue_script(
			'tct-infinite-scroll',
			get_template_directory_uri() . '/js/infinite-scroll.js',
			array(),
			wp_get_theme()->get( 'Version' ),
			true
		);

		wp_localize_script(
			'tct-infinite-scroll',
			'tctInfinite',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'tct_infinite_nonce' ),
				'query'    => wp_json_encode( tct_get_infinite_query_vars() ),
				'maxPages' => (int) $GLOBALS['wp_query']->max_num_pages,
				'loading'  => __( 'Carregando…', 'the-classicpress-theme' ),
				'noMore'   => __( 'Você chegou ao fim.', 'the-classicpress-theme' ),
				'error'    => __( 'Erro ao carregar. Tentando novamente…', 'the-classicpress-theme' ),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'tct_scripts' );

/**
 * Verifica se o contexto atual deve usar rolagem infinita.
 *
 * @return bool
 */
function tct_is_infinite_scroll_context() {
	return ( is_home() || is_archive() || is_search() ) && ! is_singular();
}

/**
 * Retorna as query vars relevantes para o AJAX.
 *
 * @return array
 */
function tct_get_infinite_query_vars() {
	$vars = $GLOBALS['wp_query']->query_vars;

	unset(
		$vars['paged'],
		$vars['page'],
		$vars['error'],
		$vars['preview'],
		$vars['sentence'],
		$vars['feed'],
		$vars['cache_results']
	);

	if ( isset( $vars['s'] ) && ! is_string( $vars['s'] ) ) {
		$vars['s'] = '';
	}

	return $vars;
}

/**
 * Handler AJAX: devolve os próximos posts da listagem.
 */
function tct_load_more_posts() {
	check_ajax_referer( 'tct_infinite_nonce', 'nonce' );

	$paged = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
	$raw   = isset( $_POST['query'] ) ? wp_unslash( $_POST['query'] ) : '';
	$args  = json_decode( $raw, true );

	if ( ! is_array( $args ) ) {
		$args = array();
	}

	// Sanitização básica: só permite chaves conhecidas/seguras.
	$allowed = array(
		'category_name', 'tag', 'cat', 'tag_id', 'author', 'author_name',
		'post_type', 'post_status', 's', 'year', 'monthnum', 'day',
		'order', 'orderby', 'posts_per_page', 'tax_query', 'meta_query',
		'post__in', 'post__not_in', 'p', 'name', 'pagename',
		'ignore_sticky_posts', 'post_parent', 'post_parent__in',
		'post_parent__not_in', 'fields', 'perm', 'comments_per_page',
	);
	$args = array_intersect_key( $args, array_flip( $allowed ) );

	// Normaliza tipos esperados.
	if ( isset( $args['s'] ) && ! is_string( $args['s'] ) ) {
		$args['s'] = '';
	}
	if ( isset( $args['posts_per_page'] ) ) {
		$args['posts_per_page'] = (int) $args['posts_per_page'];
	}
	if ( isset( $args['cat'] ) ) {
		$args['cat'] = (int) $args['cat'];
	}
	if ( isset( $args['tag_id'] ) ) {
		$args['tag_id'] = (int) $args['tag_id'];
	}

	$args['paged']         = max( 1, $paged );
	$args['post_status']   = 'publish';
	$args['no_found_rows'] = false;

	$query = new WP_Query( $args );

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/content', get_post_type() );
		}
	}

	wp_reset_postdata();
	wp_die();
}
add_action( 'wp_ajax_tct_load_more', 'tct_load_more_posts' );
add_action( 'wp_ajax_nopriv_tct_load_more', 'tct_load_more_posts' );

/**
 * Adiciona a classe "no-js" no <html> (fallback sem JS).
 */
function tct_no_js_class( $output ) {
	return $output . ' class="no-js"';
}
add_filter( 'language_attributes', 'tct_no_js_class' );

/**
 * Corrige links, imagens e iframes quebrados vindos da migração do Jekyll.
 *
 * @param string $content Conteúdo da publicação.
 * @return string Conteúdo com caminhos corrigidos.
 */
function tct_fix_migrated_media( $content ) {
	if ( empty( $content ) ) {
		return $content;
	}

	$upload_url = content_url( '/uploads' );

	// 1. Remove qualquer sintaxe Liquid do Jekyll remanescente: {{ '...' | relative_url }}
	$content = preg_replace_callback(
		'/\{\{\s*[\'"]?([^\'"}\s]+)[\'"]?\s*\|\s*relative_url\s*\}\}/i',
		function ( $matches ) {
			$path = ltrim( $matches[1], '/' );
			// Caso especial para celular. sem extensão
			if ( 'wp-content/uploads/imagens/celular.' === $path || 'assets/imagens/celular.' === $path ) {
				$path = 'wp-content/uploads/imagens/celular.webp';
			}
			return home_url( '/' . $path );
		},
		$content
	);

	// 2. Corrige caminhos de imagens em <img> (src e data-src)
	$content = preg_replace_callback(
		'/(<img[^>]+(?:src|data-src)=[\'"])(.*?)([\'"])/i',
		function ( $matches ) use ( $upload_url ) {
			$src = $matches[2];

			// Se já aponta para /wp-content/uploads/ (novos uploads do WordPress/ClassicPress), NÃO mexe!
			if ( strpos( $src, '/wp-content/uploads/' ) !== false ) {
				if ( substr( $src, -8 ) === 'celular.' ) {
					$src .= 'webp';
				}
				return $matches[1] . $src . $matches[3];
			}

			// Não alterar se for imagem externa de terceiros
			if ( preg_match( '#^https?://#i', $src ) && strpos( $src, home_url() ) === false ) {
				return $matches[0];
			}

			$filename = basename( parse_url( $src, PHP_URL_PATH ) );
			if ( 'celular.' === $filename ) {
				$filename = 'celular.webp';
			}

			if ( ! empty( $filename ) && preg_match( '/\.(webp|png|jpe?g|gif|svg)$/i', $filename ) ) {
				return $matches[1] . esc_url( $upload_url . '/imagens/' . $filename ) . $matches[3];
			}

			return $matches[0];
		},
		$content
	);

	// 3. Corrige iframes para apontarem para wp-content/uploads/graficos/
	$content = preg_replace_callback(
		'/(<iframe[^>]+(?:src|data-src)=[\'"])(.*?)([\'"])/i',
		function ( $matches ) use ( $upload_url ) {
			$src = $matches[2];

			if ( strpos( $src, '/wp-content/uploads/graficos/' ) !== false ) {
				return $matches[1] . $src . $matches[3];
			}

			if ( preg_match( '#^https?://#i', $src ) && strpos( $src, home_url() ) === false ) {
				return $matches[0];
			}

			$filename = basename( parse_url( $src, PHP_URL_PATH ) );
			if ( ! empty( $filename ) && preg_match( '/\.html?$/i', $filename ) ) {
				return $matches[1] . esc_url( $upload_url . '/graficos/' . $filename ) . $matches[3];
			}

			return $matches[0];
		},
		$content
	);

	// 4. Corrige links diretos em <a> para imagens ou gráficos
	$content = preg_replace_callback(
		'/(<a[^>]+href=[\'"])(.*?)([\'"])/i',
		function ( $matches ) use ( $upload_url ) {
			$href = $matches[2];

			if ( strpos( $href, '/wp-content/uploads/' ) !== false ) {
				return $matches[0];
			}

			if ( preg_match( '#^https?://#i', $href ) && strpos( $href, home_url() ) === false ) {
				return $matches[0];
			}

			$filename = basename( parse_url( $href, PHP_URL_PATH ) );
			if ( 'celular.' === $filename ) {
				$filename = 'celular.webp';
			}

			if ( ! empty( $filename ) && preg_match( '/\.(webp|png|jpe?g|gif|svg)$/i', $filename ) ) {
				return $matches[1] . esc_url( $upload_url . '/imagens/' . $filename ) . $matches[3];
			}

			if ( ! empty( $filename ) && preg_match( '/\.html?$/i', $filename ) ) {
				return $matches[1] . esc_url( $upload_url . '/graficos/' . $filename ) . $matches[3];
			}

			return $matches[0];
		},
		$content
	);

	// 5. Envolve imagens soltas em <a> para ativar o lightbox (Simple Lightbox).
	//    Usa tokenização: captura pares <a>...<img>...</a> (preserva) e <img> sozinhos (envolve).
	//    Classes ou URLs que indicam ícones/badges/emojis são ignoradas.
	$content = preg_replace_callback(
		'/<a\b[^>]*>.*?<\/a>|<img\b[^>]*>/is',
		function ( $matches ) {
			$token = $matches[0];

			// Já tem um <a> em volta: preserva exatamente como está.
			if ( stripos( $token, '<a ' ) === 0 || stripos( $token, '<a>' ) === 0 ) {
				return $token;
			}

			// É um <img> standalone: decide se deve envolver.

			// Extrai src/data-src para obter a URL real da imagem.
			if ( preg_match( '/\s(?:src|data-src)=["\']([^"\']+)["\']/i', $token, $src_m ) ) {
				$src = $src_m[1];
			} else {
				return $token; // Sem src, não faz nada.
			}

			// Ignora imagens externas que não sejam do próprio site.
			if ( preg_match( '#^https?://#i', $src ) && strpos( $src, home_url() ) === false ) {
				return $token;
			}

			// Ignora imagens que pareçam ícones, badges ou emojis:
			// – classes: emoji, icon, badge, avatar, logo
			// – dimensões pequenas via width/height (≤ 64px)
			// – nome do arquivo sugere emoji, icon, badge, logo
			if ( preg_match( '/\bclass=["\'][^"\']*\b(?:emoji|icon|badge|avatar|logo)\b/i', $token ) ) {
				return $token;
			}
			if ( preg_match( '/\s(?:width|height)=["\'](\d+)["\']/i', $token, $dim ) && (int) $dim[1] <= 64 ) {
				return $token;
			}
			$filename = strtolower( basename( parse_url( $src, PHP_URL_PATH ) ) );
			if ( preg_match( '/(?:emoji|icon|badge|logo|avatar)/i', $filename ) ) {
				return $token;
			}

			// Usa o src original como href para o lightbox.
			$href = esc_url( $src );
			return '<a href="' . $href . '">' . $token . '</a>';
		},
		$content
	);

	return $content;
}
add_filter( 'the_content', 'tct_fix_migrated_media', 20 );

