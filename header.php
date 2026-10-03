<?php
/**
 * Cabeçalho.
 *
 * @package the-classicpress-theme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<a class="skip-link screen-reader-text" href="#content">
	<?php esc_html_e( 'Pular para o conteúdo', 'the-classicpress-theme' ); ?>
</a>
<div class="site">

