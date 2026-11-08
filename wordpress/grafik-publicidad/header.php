<?php
/**
 * Encabezado del tema.
 *
 * @package Grafik_Publicidad
 */

defined( 'ABSPATH' ) || exit;

$checkout = function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page();
$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/carrito/' );
$count    = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( $checkout ? 'grafik-checkout-body' : '' ); ?>>
<?php wp_body_open(); ?>
<?php if ( $checkout ) : ?>
	<header class="checkout-header">
		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php grafik_logo_content(); ?>
		</a>
		<span><?php esc_html_e( 'Pago seguro con Flow', 'grafik-publicidad' ); ?></span>
	</header>
<?php else : ?>
	<header class="header">
		<a class="logo" href="<?php echo esc_url( home_url( '/#inicio' ) ); ?>">
			<?php grafik_logo_content(); ?>
		</a>
		<nav aria-label="<?php esc_attr_e( 'Navegación principal', 'grafik-publicidad' ); ?>">
			<a href="<?php echo esc_url( home_url( '/#inicio' ) ); ?>">Inicio</a>
			<a href="<?php echo esc_url( grafik_configurator_url( 'pulseras' ) ); ?>">Pulseras</a>
			<a href="<?php echo esc_url( grafik_configurator_url( 'chapitas' ) ); ?>">Chapitas</a>
			<a href="<?php echo esc_url( grafik_products_url() ); ?>">Productos</a>
			<a href="<?php echo esc_url( home_url( '/#comprar' ) ); ?>">Cómo comprar</a>
			<a href="<?php echo esc_url( home_url( '/#contacto' ) ); ?>">Contacto</a>
		</nav>
		<div class="header-actions">
			<a href="<?php echo esc_url( grafik_instagram_url() ); ?>" target="_blank" rel="noopener">Instagram</a>
			<a class="grafik-cart-link" href="<?php echo esc_url( $cart_url ); ?>" aria-label="<?php esc_attr_e( 'Ver carrito', 'grafik-publicidad' ); ?>">
				<svg class="grafik-cart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 3h2l2.4 11.4A2 2 0 0 0 9.4 16H18a2 2 0 0 0 2-1.6L21 7H6"/><circle cx="10" cy="20" r="1.2"/><circle cx="18" cy="20" r="1.2"/></svg>
				<b class="grafik-cart-count"><?php echo esc_html( (string) $count ); ?></b>
			</a>
		</div>
	</header>
<?php endif; ?>
