<?php
/**
 * Template 404.
 *
 * @package the-classicpress-theme
 */

get_header();
get_sidebar();
?>

<main id="content" class="site-content">

<article class="error-404">
	<h1 class="entry-title"><?php esc_html_e( '404 — página não encontrada', 'the-classicpress-theme' ); ?></h1>
	<p><?php esc_html_e( 'O que você procura não está aqui.', 'the-classicpress-theme' ); ?></p>
</article>

</main>

<?php
get_footer();
