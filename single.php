<?php
/**
 * Template para post único.
 *
 * @package the-classicpress-theme
 */

get_header();
get_sidebar();
?>

<main id="content" class="site-content">

<?php
while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry-header">
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<div class="entry-meta">
				<?php echo esc_html( get_the_date() ); ?>
			</div>
		</header>

		<div class="entry-content">
			<?php the_content(); ?>
		</div>

		<?php
		$tags = get_the_tags();
		if ( $tags ) :
			?>
			<div class="post-footer-tags">
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
	
<address>
  <p>
    Não tenho seção de comentários, mas você pode responder a esta publicação
    por
    <a href="mailto:falecom@gustavoribeiro.net?subject=<?php echo rawurlencode( 'Re: ' . get_the_title() ); ?>"
      >e-mail.</a
    >
  </p>
</address>
	<?php
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}

endwhile;
?>

</main>

<?php
get_footer();
