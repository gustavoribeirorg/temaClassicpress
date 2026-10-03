<?php
/**
 * Template principal (listagem de posts).
 *
 * @package the-classicpress-theme
 */

get_header();
get_sidebar();
?>

<main id="content" class="site-content">

<?php if ( have_posts() ) : ?>

	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content', get_post_type() );
	endwhile;
	?>

	<?php // Fallback de paginação clássica: só aparece se o JS estiver desativado. ?>
	<noscript>
		<?php
		the_posts_pagination(
			array(
				'mid_size'  => 1,
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
			)
		);
		?>
	</noscript>

<?php else : ?>

	<article class="no-results">
		<h1 class="entry-title"><?php esc_html_e( 'Nada por aqui', 'the-classicpress-theme' ); ?></h1>
		<p><?php esc_html_e( 'Nenhum conteúdo encontrado.', 'the-classicpress-theme' ); ?></p>
	</article>

<?php endif; ?>

</main>

<?php
get_footer();
