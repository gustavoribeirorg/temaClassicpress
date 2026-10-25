<?php
/**
 * Partial de conteúdo usado na listagem (home, arquivo, busca).
 *
 * Mostra o conteúdo completo do post via the_content(), sem truncar.
 *
 * @package the-classicpress-theme
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post' === get_post_type() ? 'h-entry' : '' ); ?>>
	<header class="entry-header">
		<h1 class="entry-title">
			<a class="u-url p-name" href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
		</h1>

		<?php if ( 'post' === get_post_type() ) : ?>
			<div class="entry-meta">
				<time class="dt-published" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
					<?php echo esc_html( get_the_date() ); ?>
				</time>
			</div>
			<div class="entry-meta p-author h-card">
				<a class="u-url p-name" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php echo esc_html( get_the_author() ); ?>
				</a>
			</div>
		<?php endif; ?>
	</header>

	<div class="entry-content<?php echo ( 'post' === get_post_type() ) ? ' e-content' : ''; ?>">
		<?php
		the_content(
			sprintf(
				/* translators: %s: post title */
				esc_html__( 'Continue lendo: %s', 'the-classicpress-theme' ),
				'<span class="screen-reader-text">' . get_the_title() . '</span>'
			)
		);

		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__( 'Páginas:', 'the-classicpress-theme' ),
				'after'  => '</div>',
			)
		);
		?>
	</div>
	<?php
		$tags = get_the_tags();
		if ( $tags ) :
			?>
			<div class="index-footer-tags">
				<span class="tags-label"><?php esc_html_e( 'Tags:', 'the-classicpress-theme' ); ?></span>
				<div class="tags-list">
					<?php foreach ( $tags as $tag ) : ?>
						<a class="tag-pill" href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>">
							<?php echo esc_html( $tag->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
			<?php
		endif;
		?>
</article>
