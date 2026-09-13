<?php
/** Chapitas de 58 mm: precios y cantidades siempre comprobados en servidor. */
defined( 'ABSPATH' ) || exit;
final class Grafik_Chapitas {
	public const TYPES = array(
		'alfiler' => array( 'name' => 'Chapita alfiler', 'minimum' => 10, 'price' => 500, 'threshold' => 101, 'bulk' => 400 ),
		'llavero' => array( 'name' => 'Chapita llavero', 'minimum' => 5, 'price' => 750, 'threshold' => 51, 'bulk' => 650 ),
		'destapador' => array( 'name' => 'Chapita destapador llavero', 'minimum' => 5, 'price' => 950, 'threshold' => 51, 'bulk' => 860 ),
	);
	public function __construct() {
		add_action( 'init', array( $this, 'setup' ), 35 );
		add_action( 'init', array( $this, 'upgrade_bulk_price' ), 36 );
		add_shortcode( 'grafik_chapitas_configurator', array( $this, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'wp_ajax_grafik_chapitas_add', array( $this, 'add' ) );
		add_action( 'wp_ajax_nopriv_grafik_chapitas_add', array( $this, 'add' ) );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add' ), 15, 6 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'prices' ), 25 );
		add_filter( 'woocommerce_update_cart_validation', array( $this, 'validate_update' ), 15, 4 );
		add_action( 'woocommerce_check_cart_items', array( $this, 'check_cart' ) );
		add_filter( 'woocommerce_get_item_data', array( $this, 'item_data' ), 15, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'order_item' ), 15, 4 );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'admin_fields' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save_fields' ) );
		add_action( 'template_redirect', array( $this, 'product_redirect' ) );
	}
	public static function id( string $kind ): int { return absint( get_option( 'grafik_chapita_' . $kind . '_id' ) ); }
	public static function kind( int $id ): string {
		foreach ( self::TYPES as $key => $rule ) { if ( self::id( $key ) === $id && $id > 0 ) { return $key; } }
		return '';
	}
	public static function rule( string $kind ): array {
		$rule = self::TYPES[ $kind ];
		$product = wc_get_product( self::id( $kind ) );
		if ( $product ) {
			$price = (float) $product->get_regular_price();
			if ( $price > 0 ) { $rule['price'] = $price; }
			foreach ( array( 'minimum', 'threshold', 'bulk' ) as $field ) {
				$value = (float) $product->get_meta( '_grafik_chapita_' . $field );
				if ( $value > 0 ) { $rule[ $field ] = 'bulk' === $field ? $value : (int) $value; }
			}
		}
		return $rule;
	}
	public static function unit_price( array $rule, int $quantity ): float {
		return (float) ( $quantity >= $rule['threshold'] ? $rule['bulk'] : $rule['price'] );
	}
	public function setup(): void {
		if ( ! class_exists( 'WooCommerce' ) || get_option( 'grafik_chapitas_installed' ) ) { return; }
		foreach ( self::TYPES as $kind => $rule ) {
			$existing = self::id( $kind ) ?: wc_get_product_id_by_sku( 'GRAFIK-CHAPITA-58-' . strtoupper( $kind ) );
			if ( $existing ) { update_option( 'grafik_chapita_' . $kind . '_id', $existing ); continue; }
			$product = new WC_Product_Simple();
			$product->set_name( $rule['name'] . ' personalizada 58 mm' );
			$product->set_slug( 'chapita-58mm-' . $kind );
			$product->set_sku( 'GRAFIK-CHAPITA-58-' . strtoupper( $kind ) );
			$product->set_status( 'publish' );
			$product->set_catalog_visibility( 'hidden' );
			$product->set_regular_price( (string) $rule['price'] );
			$product->set_price( (string) $rule['price'] );
			$product->set_virtual( false );
			$product->set_manage_stock( false );
			$product->set_stock_status( 'instock' );
			$product->set_short_description( 'Chapita publicitaria de 58 mm. Precio por unidad; personaliza tu pedido en la página Chapitas.' );
			foreach ( array( 'minimum', 'threshold', 'bulk' ) as $field ) { $product->update_meta_data( '_grafik_chapita_' . $field, $rule[ $field ] ); }
			$id = $product->save();
			if ( ! $id ) { return; }
			update_option( 'grafik_chapita_' . $kind . '_id', $id );
		}
		update_option( 'grafik_chapitas_installed', '1' );
	}
	/** Update the former default once, preserving later administrative prices. */
	public function upgrade_bulk_price(): void {
		if ( ! class_exists( 'WooCommerce' ) || get_option( 'grafik_chapitas_bulk_121' ) ) { return; }
		$product = wc_get_product( self::id( 'destapador' ) );
		if ( ! $product ) { return; }
		if ( 750.0 === (float) $product->get_meta( '_grafik_chapita_bulk' ) ) {
			$product->update_meta_data( '_grafik_chapita_bulk', 860 );
			if ( ! $product->save() ) { return; }
		}
		update_option( 'grafik_chapitas_bulk_121', '1' );
	}
	public function product_redirect(): void {
		if ( ! is_product() ) { return; }
		$id = get_queried_object_id();
		$family = self::kind( $id ) ? 'chapitas' : ( $id === absint( get_option( 'grafik_tyvek_product_id' ) ) ? 'pulseras' : '' );
		if ( $family && function_exists( 'grafik_configurator_url' ) ) { wp_safe_redirect( grafik_configurator_url( $family ) ); exit; }
	}
	public function assets(): void {
		wp_enqueue_script( 'grafik-chapitas', GRAFIK_TYVEK_URL . 'assets/js/chapitas.js', array( 'jquery' ), GRAFIK_TYVEK_VERSION, true );
	}
	public function shortcode(): string {
		if ( ! class_exists( 'WooCommerce' ) ) { return '<p>La personalización estará disponible pronto.</p>'; }
		$rules = array();
		foreach ( self::TYPES as $kind => $unused ) { $rules[ $kind ] = self::rule( $kind ); }
		$initial = $rules['alfiler'];
		ob_start(); ?>
		<div class="product-grid grafik-chapitas" data-rules="<?php echo esc_attr( wp_json_encode( $rules ) ); ?>">
			<div class="chapitas-photo"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/chapitas-catalogo.png' ); ?>" width="1024" height="1024" alt="Imagen referencial de chapitas alfiler y llavero" loading="lazy"><small>Imagen referencial de alfiler y llavero. El destapador llavero es una opción diferente.</small></div>
			<form class="options grafik-chapitas-form" enctype="multipart/form-data" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
				<input type="hidden" name="action" value="grafik_chapitas_add">
				<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'grafik_chapitas_add' ) ); ?>">
				<label class="option">Tipo de chapita<select name="kind"><?php foreach ( $rules as $kind => $rule ) : ?><option value="<?php echo esc_attr( $kind ); ?>"><?php echo esc_html( $rule['name'] ); ?></option><?php endforeach; ?></select></label>
				<label class="option">Cantidad<input name="quantity" type="number" min="<?php echo esc_attr( $initial['minimum'] ); ?>" max="10000" step="1" value="<?php echo esc_attr( $initial['minimum'] ); ?>" required><small class="chapita-minimum">Mínimo <?php echo esc_html( $initial['minimum'] ); ?> unidades. Puedes aumentar de una en una.</small></label>
				<div class="chapita-tiers"><p><span data-regular-range></span><strong data-regular-price></strong></p><p><span data-bulk-range></span><strong data-bulk-price></strong></p></div>
				<label class="option">Detalles de tus chapitas<textarea name="design_details" rows="4" maxlength="500" placeholder="Texto, colores, logo y detalles de tu diseño…"></textarea></label>
				<label class="option">Logo o diseño de referencia<input name="grafik_files[]" type="file" accept=".png,.jpg,.jpeg,.pdf" multiple><small>PNG, JPG o PDF · máximo 3 archivos · 10 MB por archivo. El diámetro terminado es de 58 mm; revisaremos el diseño contigo.</small></label>
				<div class="price-card" aria-live="polite"><div><span>Precio por unidad</span><strong data-unit-price></strong></div><div class="total"><span>Total</span><strong data-total></strong></div></div>
				<button class="cta full" type="submit">Agregar al carrito <b aria-hidden="true">→</b></button><p class="chapita-message" role="status"></p>
				<noscript>Activa JavaScript para cotizar y personalizar tus chapitas.</noscript>
			</form>
		</div>
		<?php return (string) ob_get_clean();
	}
	public function add(): void {
		check_ajax_referer( 'grafik_chapitas_add', 'nonce' );
		if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart ) { wp_send_json_error( array( 'message' => 'El carrito no está disponible.' ), 503 ); }
		foreach ( array( 'kind', 'quantity', 'design_details' ) as $field ) { if ( isset( $_POST[ $field ] ) && ! is_scalar( $_POST[ $field ] ) ) { wp_send_json_error( array( 'message' => 'Revisa los datos del pedido.' ), 400 ); } }
		$kind = sanitize_key( wp_unslash( $_POST['kind'] ?? '' ) );
		$raw_quantity = (string) wp_unslash( $_POST['quantity'] ?? '' );
		$quantity = ctype_digit( $raw_quantity ) ? (int) $raw_quantity : 0;
		if ( ! isset( self::TYPES[ $kind ] ) ) { wp_send_json_error( array( 'message' => 'Elige un tipo de chapita válido.' ), 400 ); }
		$rule = self::rule( $kind );
		if ( $quantity < $rule['minimum'] || $quantity > 10000 ) { wp_send_json_error( array( 'message' => 'Revisa la cantidad mínima del formato elegido (máximo 10.000 unidades).' ), 400 ); }
		$details = sanitize_textarea_field( wp_unslash( $_POST['design_details'] ?? '' ) );
		if ( ( function_exists( 'mb_strlen' ) ? mb_strlen( $details ) : strlen( $details ) ) > 500 ) { wp_send_json_error( array( 'message' => 'Usa hasta 500 caracteres en los detalles.' ), 400 ); }
		$product = wc_get_product( self::id( $kind ) );
		if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) { wp_send_json_error( array( 'message' => 'Este formato no está disponible en este momento.' ), 400 ); }
		$uploads = Grafik_Tyvek_Configurator::instance();
		try { $files = $uploads->receive_files(); } catch ( RuntimeException $error ) { wp_send_json_error( array( 'message' => $error->getMessage() ), 400 ); }
		$item = array( 'grafik_item_uuid' => wp_generate_uuid4(), 'grafik_chapita' => $kind, 'grafik_details' => $details, 'grafik_files' => $files );
		try { $key = WC()->cart->add_to_cart( $product->get_id(), $quantity, 0, array(), $item ); } catch ( Exception $error ) { $key = false; }
		if ( ! $key ) { $uploads->remove_received_files( $files ); wp_send_json_error( array( 'message' => 'No pudimos agregar el diseño. Revisa la disponibilidad e inténtalo otra vez.' ), 400 ); }
		WC()->cart->calculate_totals(); WC()->cart->set_session();
		wp_send_json_success( array( 'cartUrl' => wc_get_cart_url(), 'cartCount' => WC()->cart->get_cart_contents_count(), 'message' => 'Diseño agregado al carrito.' ) );
	}
	public function validate_add( bool $passed, int $product_id, $quantity, int $variation_id = 0, array $variations = array(), array $data = array() ): bool {
		$kind = self::kind( $product_id );
		if ( ! $kind ) { return $passed; }
		$rule = self::rule( $kind );
		if ( empty( $data['grafik_item_uuid'] ) || ( $data['grafik_chapita'] ?? '' ) !== $kind || (float) $quantity !== (float) (int) $quantity || $quantity < $rule['minimum'] || $quantity > 10000 ) { wc_add_notice( 'Personaliza tus chapitas y revisa la cantidad mínima antes de agregarlas.', 'error' ); return false; }
		return $passed;
	}
	public function validate_update( bool $passed, string $key, array $item, $quantity ): bool {
		$kind = self::kind( (int) $item['product_id'] );
		if ( ! $kind || 0 === (int) $quantity ) { return $passed; }
		$rule = self::rule( $kind );
		if ( (float) $quantity !== (float) (int) $quantity || $quantity < $rule['minimum'] || $quantity > 10000 ) { wc_add_notice( $rule['name'] . ': mínimo ' . $rule['minimum'] . ' unidades; máximo 10.000.', 'error' ); return false; }
		return $passed;
	}
	public function check_cart(): void {
		foreach ( WC()->cart->get_cart() as $key => $item ) {
			$kind = self::kind( (int) $item['product_id'] );
			if ( ! $kind ) { continue; }
			$this->validate_add( true, (int) $item['product_id'], $item['quantity'], 0, array(), $item );
		}
	}
	public function prices( WC_Cart $cart ): void {
		if ( is_admin() && ! wp_doing_ajax() ) { return; }
		foreach ( $cart->get_cart() as $item ) {
			$kind = self::kind( (int) $item['product_id'] );
			if ( $kind && ! empty( $item['data'] ) ) { $item['data']->set_price( wc_format_decimal( self::unit_price( self::rule( $kind ), (int) $item['quantity'] ) ) ); }
		}
	}
	public function item_data( array $data, array $item ): array {
		$kind = self::kind( (int) $item['product_id'] );
		if ( ! $kind ) { return $data; }
		$data[] = array( 'key' => 'Formato', 'value' => self::TYPES[ $kind ]['name'] . ' · 58 mm' );
		$data[] = array( 'key' => 'Detalles', 'value' => $item['grafik_details'] ?: 'Sin indicaciones escritas' );
		$names = wp_list_pluck( $item['grafik_files'] ?? array(), 'name' );
		$data[] = array( 'key' => 'Archivos', 'value' => $names ? implode( ' · ', $names ) : 'Sin archivos adjuntos' );
		return $data;
	}
	public function order_item( WC_Order_Item_Product $item, string $key, array $values, WC_Order $order ): void {
		if ( ! self::kind( (int) $values['product_id'] ) ) { return; }
		foreach ( $this->item_data( array(), $values ) as $field ) { $item->add_meta_data( $field['key'], $field['value'], true ); }
		$item->add_meta_data( '_grafik_files', wp_json_encode( $values['grafik_files'] ?? array() ), true );
		$item->add_meta_data( '_grafik_item_uuid', $values['grafik_item_uuid'], true );
	}
	public function admin_fields(): void {
		global $product_object;
		if ( ! $product_object || ! self::kind( $product_object->get_id() ) ) { return; }
		echo '<div class="options_group">';
		foreach ( array( 'minimum' => 'Cantidad mínima', 'threshold' => 'Descuento desde (unidades)', 'bulk' => 'Precio por unidad con descuento (CLP)' ) as $key => $label ) {
			woocommerce_wp_text_input( array( 'id' => '_grafik_chapita_' . $key, 'label' => $label, 'type' => 'number', 'custom_attributes' => array( 'min' => 1, 'step' => 1 ) ) );
		}
		echo '<p class="form-field">El precio normal usa el campo Precio normal de WooCommerce. Los descuentos se calculan por diseño, sin mezclar formatos.</p></div>';
	}
	public function save_fields( WC_Product $product ): void {
		if ( ! self::kind( $product->get_id() ) || ! current_user_can( 'edit_post', $product->get_id() ) ) { return; }
		// WooCommerce valida el nonce y los permisos antes de este hook.
		$rule = self::rule( self::kind( $product->get_id() ) );
		foreach ( array( 'minimum', 'threshold', 'bulk' ) as $key ) {
			$field = '_grafik_chapita_' . $key;
			if ( isset( $_POST[ $field ] ) && is_scalar( $_POST[ $field ] ) && ctype_digit( (string) wp_unslash( $_POST[ $field ] ) ) ) { $rule[ $key ] = (int) $_POST[ $field ]; }
		}
		if ( $rule['minimum'] < 1 || $rule['minimum'] > 10000 || $rule['threshold'] < $rule['minimum'] || $rule['threshold'] > 10000 || $rule['bulk'] < 1 || $rule['bulk'] > (float) $product->get_regular_price() ) { WC_Admin_Meta_Boxes::add_error( 'Revisa mínimos y precios de chapitas. No se guardaron las reglas de descuento.' ); return; }
		foreach ( array( 'minimum', 'threshold', 'bulk' ) as $key ) { $product->update_meta_data( '_grafik_chapita_' . $key, $rule[ $key ] ); }
	}
}
new Grafik_Chapitas();
