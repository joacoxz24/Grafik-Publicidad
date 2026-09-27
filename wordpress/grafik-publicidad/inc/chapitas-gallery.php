<?php
defined( 'ABSPATH' ) || exit;

add_action( 'customize_register', static function ( $customizer ) {
	$customizer->add_section( 'grafik_chapitas_gallery', array( 'title' => 'Fotos de chapitas', 'description' => 'Se muestran primero las promocionales y después las reales. Los espacios vacíos se omiten. Sin promociones seleccionadas se utiliza la imagen original.', 'priority' => 31 ) );
	foreach ( array( 'promo' => 3, 'real' => 6 ) as $group => $count ) {
		for ( $i = 1; $i <= $count; $i++ ) {
			$key = 'grafik_chapitas_' . $group . '_' . $i;
			$customizer->add_setting( $key, array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
			$customizer->add_control( new WP_Customize_Media_Control( $customizer, $key, array( 'section' => 'grafik_chapitas_gallery', 'label' => ( 'promo' === $group ? 'Promocional ' : 'Foto real ' ) . $i, 'mime_type' => 'image' ) ) );
		}
	}
} );

function grafik_chapitas_gallery_slides(): array {
	$slides = array();
	$seen = array();
	foreach ( array( 'promo' => 3, 'real' => 6 ) as $group => $count ) {
		for ( $i = 1; $i <= $count; $i++ ) {
			$id = absint( get_theme_mod( 'grafik_chapitas_' . $group . '_' . $i, 0 ) );
			if ( ! $id || isset( $seen[ $id ] ) || ! wp_attachment_is_image( $id ) || ! wp_get_attachment_image_url( $id, 'large' ) ) { continue; }
			$seen[ $id ] = true;
			$slides[] = array( 'id' => $id, 'caption' => 'promo' === $group ? 'Imagen promocional de chapitas personalizadas.' : 'Fotografía real del producto.' );
		}
		if ( 'promo' === $group && ! $slides ) {
			$slides[] = array( 'id' => 0, 'caption' => 'Imagen referencial de alfiler y llavero. El destapador llavero es una opción diferente.' );
		}
	}
	return $slides;
}
