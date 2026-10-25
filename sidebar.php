<?php
/**
 * Coluna lateral esquerda.
 *
 * @package the-classicpress-theme
 */

// URL para a lista completa de posts (usa page_for_posts se existir).
$posts_page_id = (int) get_option( 'page_for_posts' );
$all_url       = $posts_page_id ? get_permalink( $posts_page_id ) : home_url( '/' );

// Categorias.
$categories  = get_categories( array( 'hide_empty' => true ) );
$current_cat = is_category() ? (int) get_queried_object_id() : 0;
?>
<aside id="secondary" class="site-sidebar" aria-label="<?php esc_attr_e( 'Barra lateral', 'the-classicpress-theme' ); ?>">

    <?php if ( ! empty( $categories ) ) : ?>
        <nav aria-label="<?php esc_attr_e( 'Categorias', 'the-classicpress-theme' ); ?>">
            <span class="sidebar-title"><?php esc_html_e( 'Categorias', 'the-classicpress-theme' ); ?></span>
            <ul class="category-list">
                <li>
                    <a class="category-link" href="<?php echo esc_url( $all_url ); ?>">
                        <?php esc_html_e( 'Ver todas', 'the-classicpress-theme' ); ?>
                    </a>
                </li>
                <?php foreach ( $categories as $cat ) : ?>
                    <li>
                        <a class="category-link<?php echo ( (int) $cat->term_id === $current_cat ) ? ' active' : ''; ?>"
                            href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>">
                            <?php echo esc_html( $cat->name ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <div class="h-card profile-card">
        <a class="u-url p-name profile-card-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
            <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
        </a>
        <a class="u-email profile-card-link" rel="me" href="mailto:falecom@gustavoribeiro.net">
            falecom@gustavoribeiro.net
        </a>
    </div>

    <?php
    $pages = get_pages();
    $current_page_id = get_queried_object_id();
    ?>
    <?php if ( ! empty( $pages ) ) : ?>
        <nav aria-label="<?php esc_attr_e( 'Páginas', 'the-classicpress-theme' ); ?>">
            <span class="sidebar-title"><?php esc_html_e( 'Páginas', 'the-classicpress-theme' ); ?></span>
            <ul class="category-list">
                <?php foreach ( $pages as $page ) : ?>
                    <li>
                        <a class="category-link<?php echo ( (int) $page->ID === (int) $current_page_id ) ? ' active' : ''; ?>"
                            href="<?php echo esc_url( get_permalink( $page->ID ) ); ?>">
                            <?php echo esc_html( $page->post_title ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    <?php endif; ?>

</aside>
