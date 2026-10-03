<?php
/**
 * Filtros para preparar imagens/iframes para lazy load.
 *
 * O WordPress já adiciona loading="lazy" em imagens do conteúdo.
 * Aqui convertemos src → data-src em posts da listagem (onde o nosso
 * JS assume o controle), deixando o navegador nativo cuidar do resto.
 *
 * Este arquivo fica em /js/ apenas por organização do pacote;
 * na verdade é um include PHP carregado pelo functions.php.
 * Se você não quiser usar, remova o require abaixo do functions.php.
 *
 * @package the-classicpress-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Converte src em data-src nas imagens do conteúdo quando estamos
 * em contexto de listagem (onde o infinite scroll roda). Isso evita
 * que o navegador baixe imagens que ainda não estão visíveis.
 *
 * Em posts individuais (is_singular) nada é alterado — o lazy load
 * nativo do WordPress continua funcionando normalmente.
 *
 * @param string $content Conteúdo do post.
 * @return string
 */
function tct_lazy_load_content_images( $content ) {
	// Só age em listagens. Em single/page, deixa o core cuidar.
	if ( is_singular() || is_admin() || is_feed() ) {
		return $content;
	}

	// Não processa se o conteúdo não tiver imagem ou iframe.
	if ( false === strpos( $content, '<img' ) && false === strpos( $content, '<iframe' ) ) {
		return $content;
	}

	// Substitui src= por data-src= em <img> e <iframe>.
	// Regex conservadora: só pega atributos src simples.
	$content = preg_replace_callback(
		'/<(img|iframe)\b([^>]*?)\ssrc=(["\'])([^"\']+)\3([^>]*)>/i',
		function ( $matches ) {
			$tag    = $matches[1];
			$before = $matches[2];
			$quote  = $matches[3];
			$src    = $matches[4];
			$after  = $matches[5];

			// Não mexe em data: URIs nem em placeholders vazios.
			if ( '' === $src || 0 === strpos( $src, 'data:' ) ) {
				return $matches[0];
			}

			// Adiciona loading="lazy" se ainda não existir.
			$has_loading = ( false !== stripos( $before . $after, 'loading=' ) );
			$loading_attr = $has_loading ? '' : ' loading="lazy"';

			return sprintf(
				'<%1$s%2$s data-src=%3$s%4$s%3$s%5$s%6$s>',
				$tag,
				$before,
				$quote,
				esc_attr( $src ),
				$after,
				$loading_attr
			);
		},
		$content
	);

	return $content;
}
add_filter( 'the_content', 'tct_lazy_load_content_images', 20 );
