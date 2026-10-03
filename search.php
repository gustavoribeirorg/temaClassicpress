<?php
/**
 * Template de resultados de busca.
 *
 * @package the-classicpress-theme
 */

get_header();
get_sidebar();
?>

<main id="content" class="site-content">

<header class="page-header">
    <h1 class="entry-title">
        <?php
        printf(
            /* translators: %s: termo buscado */
            esc_html__( 'Resultados para: %s', 'the-classicpress-theme' ),
            '<span>' . esc_html( get_search_query() ) . '</span>'
        );
        ?>
    </h1>
</header>

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
        <p><?php esc_html_e( 'Nada encontrado. Tente outras palavras.', 'the-classicpress-theme' ); ?></p>
        <?php get_search_form(); ?>
    </article>

<?php endif; ?>

</main>

<?php
get_footer();