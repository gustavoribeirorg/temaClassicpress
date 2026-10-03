<?php
/**
 * Template de comentários.
 *
 * @package the-classicpress-theme
 */

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$count = get_comments_number();
			printf(
				/* translators: %s: número de comentários */
				esc_html( _n( '%s comentário', '%s comentários', $count, 'the-classicpress-theme' ) ),
				esc_html( number_format_i18n( $count ) )
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
			)
		);
		?>

	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="no-comments"><?php esc_html_e( 'Comentários estão fechados.', 'the-classicpress-theme' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply' => esc_html__( 'Deixe um comentário', 'the-classicpress-theme' ),
		)
	);
	?>

</div>
