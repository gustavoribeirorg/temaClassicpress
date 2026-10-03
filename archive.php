<?php
/**
 * Template para arquivos (categorias, tags, datas, autor).
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
        <p><?php esc_html_e( 'Nada encontrado.', 'the-classicpress-theme' ); ?></p>
    </article>

<?php endif; ?>

</main>

<?php
get_footer();