<?php
/**
 * Plugin Name:       Grafik Configurador Tyvek
 * Plugin URI:        https://odcpublicidad.cl
 * Description:       Configurador seguro de pulseras Tyvek para WooCommerce, con archivos por diseño, descuento y datos de despacho.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Requires Plugins:  woocommerce
 * Author:            Grafik Publicidad
 * Text Domain:       grafik-tyvek
 */

defined( 'ABSPATH' ) || exit;

define( 'GRAFIK_TYVEK_VERSION', '1.0.0' );
define( 'GRAFIK_TYVEK_FILE', __FILE__ );
define( 'GRAFIK_TYVEK_DIR', plugin_dir_path( __FILE__ ) );
define( 'GRAFIK_TYVEK_URL', plugin_dir_url( __FILE__ ) );

final class Grafik_Tyvek_Configurator {
	private const PRODUCT_OPTION   = 'grafik_tyvek_product_id';
	private const SETUP_OPTION     = 'grafik_tyvek_needs_setup';
	private const PRICE_OPTION     = 'grafik_tyvek_price_per_100';
	private const THRESHOLD_OPTION = 'grafik_tyvek_discount_threshold';
	private const DISCOUNT_OPTION  = 'grafik_tyvek_discount_percent';
	private const MAX_OPTION       = 'grafik_tyvek_max_units';
	private const CRON_HOOK        = 'grafik_tyvek_cleanup_uploads';

	private static ?self $instance = null;

	/**
	 * @var array<string,string>
	 */
	private array $colors = array(
		'Sin color específico' => 'sin-color',
		'Blanco'                => 'blanco',
		'Negro'                 => 'negro',
		'Rojo'                  => 'rojo',
		'Rosado'                => 'rosado',
		'Celeste'               => 'celeste',
		'Amarillo'              => 'amarillo',
		'Naranjo'               => 'naranjo',
		'Azul'                  => 'azul',
		'Verde'                 => 'verde',
		'Fucsia'                => 'fucsia',
	);

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'maybe_setup' ), 30 );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		add_shortcode( 'grafik_tyvek_configurator', array( $this, 'shortcode' ) );

		add_action( 'wp_ajax_grafik_tyvek_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_grafik_tyvek_add_to_cart', array( $this, 'ajax_add_to_cart' ) );

		add_action( 'woocommerce_before_calculate_totals', array( $this, 'apply_price' ), 20 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'require_configuration' ), 10, 6 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'cart_item_data' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_quantity', array( $this, 'cart_quantity_label' ), 10, 3 );
		add_filter( 'woocommerce_update_cart_validation', array( $this, 'validate_cart_quantity' ), 10, 4 );
		add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'sync_cart_units' ), 10, 4 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'create_order_item' ), 10, 4 );
		add_action( 'woocommerce_checkout_order_created', array( $this, 'secure_order_files' ) );

		add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'delivery_fields' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'validate_delivery_fields' ) );
		add_action( 'woocommerce_checkout_create_order', array( $this, 'save_delivery_fields' ) );
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'admin_delivery_fields' ) );
		add_filter( 'woocommerce_email_order_meta_fields', array( $this, 'email_delivery_fields' ), 10, 3 );

		add_action( 'woocommerce_after_order_itemmeta', array( $this, 'admin_file_links' ), 10, 3 );
		add_action( 'admin_post_grafik_download_design', array( $this, 'download_design' ) );
		add_action( self::CRON_HOOK, array( $this, 'cleanup_uploads' ) );

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 50 );
		add_action( 'admin_post_grafik_save_tyvek_settings', array( $this, 'save_settings' ) );
		add_action( 'admin_notices', array( $this, 'woocommerce_notice' ) );
	}

	public static function activate(): void {
		add_option( self::SETUP_OPTION, '1' );
		add_option( self::PRICE_OPTION, '10500' );
		add_option( self::THRESHOLD_OPTION, '1000' );
		add_option( self::DISCOUNT_OPTION, '20' );
		add_option( self::MAX_OPTION, '10000' );

		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK );
		}
	}

	public static function deactivate(): void {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	public function maybe_setup(): void {
		if ( ! class_exists( 'WooCommerce' ) || ! get_option( self::SETUP_OPTION ) ) {
			return;
		}

		$this->ensure_product();
		$this->prepare_classic_pages();
		delete_option( self::SETUP_OPTION );
	}

	private function ensure_product(): int {
		$product_id = absint( get_option( self::PRODUCT_OPTION ) );
		if ( $product_id && 'product' === get_post_type( $product_id ) ) {
			$product = wc_get_product( $product_id );
			if ( $product && 'hidden' !== $product->get_catalog_visibility() ) {
				$product->set_catalog_visibility( 'hidden' );
				$product->save();
			}
			return $product_id;
		}

		$sku_id = wc_get_product_id_by_sku( 'GRAFIK-TYVEK-100' );
		if ( $sku_id ) {
			update_option( self::PRODUCT_OPTION, $sku_id );
			$product = wc_get_product( $sku_id );
			if ( $product && 'hidden' !== $product->get_catalog_visibility() ) {
				$product->set_catalog_visibility( 'hidden' );
				$product->save();
			}
			return $sku_id;
		}

		$product = new WC_Product_Simple();
		$product->set_name( 'Pulseras Tyvek personalizadas' );
		$product->set_slug( 'pulseras-tyvek-personalizadas' );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'hidden' );
		$product->set_sku( 'GRAFIK-TYVEK-100' );
		$product->set_regular_price( (string) $this->price() );
		$product->set_price( (string) $this->price() );
		$product->set_manage_stock( false );
		$product->set_stock_status( 'instock' );
		$product->set_virtual( false );
		$product->set_sold_individually( false );
		$product->set_short_description( 'Pulseras Tyvek personalizadas para eventos. El precio corresponde a cada 100 unidades.' );
		$product->set_description( 'Selecciona la cantidad en tramos de 100, el color base y adjunta hasta tres archivos con tu logo o diseño.' );
		$product_id = $product->save();

		update_option( self::PRODUCT_OPTION, $product_id );
		return $product_id;
	}

	private function prepare_classic_pages(): void {
		$pages = array(
			'cart'     => '[woocommerce_cart]',
			'checkout' => '[woocommerce_checkout]',
		);

		foreach ( $pages as $page_key => $shortcode ) {
			$page_id = wc_get_page_id( $page_key );
			if ( $page_id <= 0 ) {
				continue;
			}

			$content = (string) get_post_field( 'post_content', $page_id );
			if ( '' === trim( $content ) || str_contains( $content, 'wp:woocommerce/' . $page_key ) ) {
				wp_update_post(
					array(
						'ID'           => $page_id,
						'post_content' => $shortcode,
					)
				);
			}
		}
	}

	public function woocommerce_notice(): void {
		if ( class_exists( 'WooCommerce' ) || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-error"><p>Grafik Configurador Tyvek necesita WooCommerce activo.</p></div>';
	}

	public function assets(): void {
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_style(
			'grafik-tyvek',
			GRAFIK_TYVEK_URL . 'assets/css/configurator.css',
			array(),
			GRAFIK_TYVEK_VERSION
		);
		wp_enqueue_script(
			'grafik-tyvek',
			GRAFIK_TYVEK_URL . 'assets/js/configurator.js',
			array( 'jquery' ),
			GRAFIK_TYVEK_VERSION,
			true
		);
		wp_localize_script(
			'grafik-tyvek',
			'grafikTyvek',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'cartUrl'   => wc_get_cart_url(),
				'maxFiles'  => 3,
				'fileTypes' => array( 'png', 'jpg', 'jpeg', 'pdf' ),
				'i18n'      => array(
					'adding'     => 'Agregando…',
					'add'        => 'Agregar al carrito',
					'added'      => 'Diseño agregado al carrito.',
					'viewCart'   => 'Ver carrito',
					'fileLimit'  => 'Puedes subir máximo 3 archivos.',
					'fileType'   => 'Usa solamente archivos PNG, JPG o PDF.',
					'connection' => 'No pudimos conectar con la tienda. Inténtalo nuevamente.',
				),
			)
		);
	}

	public function shortcode(): string {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return '<div class="grafik-plugin-required">WooCommerce debe estar activo.</div>';
		}

		$product_id = $this->ensure_product();
		$price      = $this->price();
		$threshold  = $this->threshold();
		$discount   = $this->discount();
		$max_units  = $this->max_units();

		ob_start();
		?>
		<div
			class="product-grid grafik-tyvek"
			data-price="<?php echo esc_attr( (string) $price ); ?>"
			data-threshold="<?php echo esc_attr( (string) $threshold ); ?>"
			data-discount="<?php echo esc_attr( (string) $discount ); ?>"
			data-max="<?php echo esc_attr( (string) $max_units ); ?>"
		>
			<div class="preview">
				<div class="flat-band sin-color"><i></i></div>
				<small>✦ Vista referencial únicamente del color base</small>
			</div>
			<form class="options grafik-tyvek-form" enctype="multipart/form-data">
				<input type="hidden" name="action" value="grafik_tyvek_add_to_cart">
				<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'grafik_tyvek_add' ) ); ?>">
				<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $product_id ); ?>">
				<input type="hidden" name="quantity_units" value="100" class="grafik-quantity-value">
				<input type="hidden" name="base_color" value="Sin color específico" class="grafik-color-value">

				<div class="option">
					<label>Cantidad</label>
					<div class="counter">
						<button type="button" class="grafik-quantity-minus" aria-label="Restar 100 unidades">−</button>
						<strong class="grafik-quantity-label">100 unidades</strong>
						<button type="button" class="grafik-quantity-plus" aria-label="Agregar 100 unidades">+</button>
					</div>
					<input class="grafik-quantity-range" type="range" min="100" max="<?php echo esc_attr( (string) $max_units ); ?>" step="100" value="100">
					<small class="grafik-discount-message">
						Agrega <?php echo esc_html( number_format_i18n( $threshold - 100 ) ); ?> unidades para obtener <?php echo esc_html( (string) $discount ); ?>% de descuento.
					</small>
				</div>

				<div class="option">
					<label>Color base <span class="optional">(Opcional)</span></label>
					<div class="swatches">
						<?php foreach ( $this->colors as $name => $class ) : ?>
							<button
								type="button"
								class="<?php echo esc_attr( $class . ( 'Sin color específico' === $name ? ' active' : '' ) ); ?>"
								data-color="<?php echo esc_attr( $name ); ?>"
								aria-label="<?php echo esc_attr( $name ); ?>"
								title="<?php echo esc_attr( $name ); ?>"
							><?php echo 'Sin color específico' === $name ? '×' : ''; ?></button>
						<?php endforeach; ?>
					</div>
					<small class="color-choice">Sin color específico</small>
				</div>

				<div class="option">
					<label for="grafik-design-details">Escribe los detalles de la pulsera</label>
					<textarea
						id="grafik-design-details"
						class="design-details"
						name="design_details"
						maxlength="500"
						rows="4"
						placeholder="Ej. Pulsera VIP fucsia, texto blanco, logo centrado y numeración."
					></textarea>
					<small class="field-note">Describe el texto, ubicación del logo, colores de impresión y cualquier indicación importante.</small>
				</div>

				<div class="option">
					<label>Logo o diseño de referencia</label>
					<label class="upload">
						<b>↑</b>
						<strong class="grafik-upload-label">Selecciona tus archivos</strong>
						<small>PNG, JPG o PDF · máximo 3 archivos</small>
						<input class="grafik-files" type="file" name="grafik_files[]" accept=".png,.jpg,.jpeg,.pdf" multiple>
					</label>
					<ul class="file-list grafik-file-list" hidden></ul>
					<p class="upload-help">Puedes subir tu logo, diseño de referencia o la plantilla completa de la pulsera en medidas 25 × 2 cm. Puedes subir máximo 3 archivos.</p>
				</div>

				<div class="price-card">
					<div><span>Subtotal</span><strong class="grafik-subtotal"><?php echo wp_kses_post( wc_price( $price ) ); ?></strong></div>
					<div class="saving grafik-saving" hidden><span>Descuento <?php echo esc_html( (string) $discount ); ?>%</span><strong></strong></div>
					<div class="total"><span>Total</span><strong class="grafik-total"><?php echo wp_kses_post( wc_price( $price ) ); ?></strong></div>
					<small>IVA incluido</small>
				</div>

				<div class="grafik-form-status" role="status" aria-live="polite"></div>
				<button class="cta full grafik-add-to-cart" type="submit">Agregar al carrito <b>→</b></button>
			</form>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	public function ajax_add_to_cart(): void {
		check_ajax_referer( 'grafik_tyvek_add', 'nonce' );

		if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => 'El carrito no está disponible.' ), 503 );
		}

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$units      = isset( $_POST['quantity_units'] ) ? absint( $_POST['quantity_units'] ) : 0;
		$color      = isset( $_POST['base_color'] ) ? sanitize_text_field( wp_unslash( $_POST['base_color'] ) ) : '';
		$details    = isset( $_POST['design_details'] ) ? sanitize_textarea_field( wp_unslash( $_POST['design_details'] ) ) : '';

		if ( $product_id !== absint( get_option( self::PRODUCT_OPTION ) ) || ! wc_get_product( $product_id ) ) {
			wp_send_json_error( array( 'message' => 'El producto seleccionado no es válido.' ), 400 );
		}
		if ( $units < 100 || $units > $this->max_units() || 0 !== $units % 100 ) {
			wp_send_json_error( array( 'message' => 'La cantidad debe avanzar de 100 en 100.' ), 400 );
		}
		if ( ! isset( $this->colors[ $color ] ) ) {
			wp_send_json_error( array( 'message' => 'El color seleccionado no es válido.' ), 400 );
		}
		$details_length = function_exists( 'mb_strlen' ) ? mb_strlen( $details ) : strlen( $details );
		if ( $details_length > 500 ) {
			wp_send_json_error( array( 'message' => 'Los detalles no pueden superar 500 caracteres.' ), 400 );
		}

		try {
			$files = $this->receive_files();
		} catch ( RuntimeException $exception ) {
			wp_send_json_error( array( 'message' => $exception->getMessage() ), 400 );
		}

		$packs     = (int) ( $units / 100 );
		$cart_data = array(
			'grafik_item_uuid' => wp_generate_uuid4(),
			'grafik_units'     => $units,
			'grafik_color'     => $color,
			'grafik_details'   => $details,
			'grafik_files'     => $files,
		);
		$cart_key  = WC()->cart->add_to_cart( $product_id, $packs, 0, array(), $cart_data );

		if ( ! $cart_key ) {
			$this->remove_received_files( $files );
			wp_send_json_error( array( 'message' => 'No pudimos agregar el diseño al carrito.' ), 500 );
		}

		WC()->cart->calculate_totals();
		WC()->cart->set_session();

		wp_send_json_success(
			array(
				'message'    => 'Diseño agregado al carrito.',
				'cartUrl'    => wc_get_cart_url(),
				'cartCount'  => WC()->cart->get_cart_contents_count(),
				'cartHash'   => WC()->cart->get_cart_hash(),
				'fragments'  => $this->cart_fragments(),
			)
		);
	}

	/**
	 * @return array<string,string>
	 */
	private function cart_fragments(): array {
		$fragments = array();
		ob_start();
		woocommerce_mini_cart();
		$fragments['div.widget_shopping_cart_content'] = '<div class="widget_shopping_cart_content">' . ob_get_clean() . '</div>';

		ob_start();
		echo '<b class="grafik-cart-count">' . esc_html( (string) WC()->cart->get_cart_contents_count() ) . '</b>';
		$fragments['b.grafik-cart-count'] = ob_get_clean();

		return apply_filters( 'woocommerce_add_to_cart_fragments', $fragments );
	}

	/**
	 * @return array<int,array{name:string,path:string,type:string}>
	 */
	private function receive_files(): array {
		if ( empty( $_FILES['grafik_files']['name'] ) ) {
			return array();
		}

		$input = $_FILES['grafik_files'];
		$names = is_array( $input['name'] ) ? $input['name'] : array( $input['name'] );
		$count = count( array_filter( $names ) );
		if ( $count > 3 ) {
			throw new RuntimeException( 'Puedes subir máximo 3 archivos.' );
		}

		$allowed = array(
			'png'      => 'image/png',
			'jpg|jpeg' => 'image/jpeg',
			'pdf'      => 'application/pdf',
		);
		$directory = $this->cart_upload_directory();
		$this->protect_directory( $directory );
		$saved = array();

		foreach ( $names as $index => $raw_name ) {
			if ( '' === $raw_name ) {
				continue;
			}

			$error = is_array( $input['error'] ) ? (int) $input['error'][ $index ] : (int) $input['error'];
			$size  = is_array( $input['size'] ) ? (int) $input['size'][ $index ] : (int) $input['size'];
			$tmp   = is_array( $input['tmp_name'] ) ? $input['tmp_name'][ $index ] : $input['tmp_name'];
			if ( UPLOAD_ERR_OK !== $error || ! is_uploaded_file( $tmp ) ) {
				$this->remove_received_files( $saved );
				throw new RuntimeException( 'Uno de los archivos no pudo subirse.' );
			}
			if ( $size > 10 * MB_IN_BYTES ) {
				$this->remove_received_files( $saved );
				throw new RuntimeException( 'Cada archivo puede pesar como máximo 10 MB.' );
			}

			$original = sanitize_file_name( wp_basename( $raw_name ) );
			$check    = wp_check_filetype_and_ext( $tmp, $original, $allowed );
			if ( empty( $check['ext'] ) || empty( $check['type'] ) ) {
				$this->remove_received_files( $saved );
				throw new RuntimeException( 'Usa solamente archivos PNG, JPG o PDF.' );
			}

			$random = wp_generate_password( 20, false, false );
			$target = trailingslashit( $directory ) . wp_unique_filename( $directory, $random . '-' . $original );
			if ( ! move_uploaded_file( $tmp, $target ) ) {
				$this->remove_received_files( $saved );
				throw new RuntimeException( 'No pudimos guardar uno de los archivos.' );
			}
			chmod( $target, 0640 );

			$saved[] = array(
				'name' => $original,
				'path' => $target,
				'type' => $check['type'],
			);
		}

		return $saved;
	}

	private function cart_upload_directory(): string {
		$uploads = wp_upload_dir();
		$session = WC()->session ? WC()->session->get_customer_id() : wp_generate_uuid4();
		$session = sanitize_file_name( (string) $session );
		return trailingslashit( $uploads['basedir'] ) . 'grafik-designs/cart/' . $session;
	}

	private function protect_directory( string $directory ): void {
		if ( ! wp_mkdir_p( $directory ) ) {
			throw new RuntimeException( 'El servidor no permite crear la carpeta segura de diseños.' );
		}

		$root = trailingslashit( wp_upload_dir()['basedir'] ) . 'grafik-designs';
		wp_mkdir_p( $root );
		if ( ! file_exists( trailingslashit( $root ) . '.htaccess' ) ) {
			file_put_contents(
				trailingslashit( $root ) . '.htaccess',
				"Options -Indexes\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n"
			);
		}
		if ( ! file_exists( trailingslashit( $root ) . 'index.php' ) ) {
			file_put_contents( trailingslashit( $root ) . 'index.php', "<?php\n// Silence is golden.\n" );
		}
	}

	/**
	 * @param array<int,array{name:string,path:string,type:string}> $files Files.
	 */
	private function remove_received_files( array $files ): void {
		foreach ( $files as $file ) {
			if ( ! empty( $file['path'] ) && is_file( $file['path'] ) ) {
				unlink( $file['path'] );
			}
		}
	}

	public function apply_price( WC_Cart $cart ): void {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item ) {
			if ( empty( $cart_item['grafik_item_uuid'] ) || empty( $cart_item['data'] ) ) {
				continue;
			}

			$units      = max( 100, (int) $cart_item['quantity'] * 100 );
			$unit_price = $this->price();
			if ( $units >= $this->threshold() ) {
				$unit_price *= ( 1 - ( $this->discount() / 100 ) );
			}
			$cart_item['data']->set_price( wc_format_decimal( $unit_price ) );
		}
	}

	public function require_configuration(
		bool $passed,
		int $product_id,
		int $quantity,
		int $variation_id = 0,
		array $variations = array(),
		array $cart_item_data = array()
	): bool {
		if ( $product_id !== absint( get_option( self::PRODUCT_OPTION ) ) ) {
			return $passed;
		}
		if ( ! empty( $cart_item_data['grafik_item_uuid'] ) ) {
			return $passed;
		}

		wc_add_notice( 'Personaliza primero la cantidad, color y diseño de tus pulseras en la página de inicio.', 'error' );
		return false;
	}

	public function cart_item_data( array $data, array $cart_item ): array {
		if ( empty( $cart_item['grafik_item_uuid'] ) ) {
			return $data;
		}

		$files = isset( $cart_item['grafik_files'] ) && is_array( $cart_item['grafik_files'] ) ? $cart_item['grafik_files'] : array();
		$names = wp_list_pluck( $files, 'name' );
		$units = max( 100, (int) $cart_item['quantity'] * 100 );

		$data[] = array( 'key' => 'Cantidad real', 'value' => number_format_i18n( $units ) . ' unidades' );
		$data[] = array( 'key' => 'Color base', 'value' => $cart_item['grafik_color'] ?? 'Sin color específico' );
		$data[] = array( 'key' => 'Detalles', 'value' => $cart_item['grafik_details'] ?: 'Sin indicaciones escritas' );
		$data[] = array( 'key' => 'Archivos', 'value' => $names ? implode( ' · ', $names ) : 'Sin archivos adjuntos' );
		if ( $units >= $this->threshold() ) {
			$data[] = array( 'key' => 'Descuento', 'value' => $this->discount() . '% aplicado' );
		}
		return $data;
	}

	public function cart_quantity_label( string $html, string $cart_item_key, array $cart_item ): string {
		if ( empty( $cart_item['grafik_item_uuid'] ) ) {
			return $html;
		}
		$units = max( 100, (int) $cart_item['quantity'] * 100 );
		return sprintf(
			'<span class="grafik-unit-quantity">%1$s unidades</span><input type="hidden" name="cart[%2$s][qty]" value="%3$s">',
			esc_html( number_format_i18n( $units ) ),
			esc_attr( $cart_item_key ),
			esc_attr( (string) $cart_item['quantity'] )
		);
	}

	public function validate_cart_quantity( bool $passed, string $cart_item_key, array $values, int $quantity ): bool {
		if ( empty( $values['grafik_item_uuid'] ) ) {
			return $passed;
		}
		if ( $quantity < 1 || ( $quantity * 100 ) > $this->max_units() ) {
			wc_add_notice( 'La cantidad de pulseras debe estar entre 100 y ' . number_format_i18n( $this->max_units() ) . '.', 'error' );
			return false;
		}
		return $passed;
	}

	public function sync_cart_units( string $cart_item_key, int $quantity, int $old_quantity, WC_Cart $cart ): void {
		if ( isset( $cart->cart_contents[ $cart_item_key ]['grafik_item_uuid'] ) ) {
			$cart->cart_contents[ $cart_item_key ]['grafik_units'] = $quantity * 100;
		}
	}

	public function create_order_item( WC_Order_Item_Product $item, string $cart_item_key, array $values, WC_Order $order ): void {
		if ( empty( $values['grafik_item_uuid'] ) ) {
			return;
		}

		$files = isset( $values['grafik_files'] ) && is_array( $values['grafik_files'] ) ? $values['grafik_files'] : array();
		$names = wp_list_pluck( $files, 'name' );
		$units = max( 100, (int) $item->get_quantity() * 100 );

		$item->add_meta_data( 'Cantidad real', number_format_i18n( $units ) . ' unidades', true );
		$item->add_meta_data( 'Color base', $values['grafik_color'] ?? 'Sin color específico', true );
		$item->add_meta_data( 'Detalles', $values['grafik_details'] ?: 'Sin indicaciones escritas', true );
		$item->add_meta_data( 'Archivos', $names ? implode( ' · ', $names ) : 'Sin archivos adjuntos', true );
		$item->add_meta_data( '_grafik_files', wp_json_encode( $files ), true );
		$item->add_meta_data( '_grafik_item_uuid', $values['grafik_item_uuid'], true );
	}

	public function secure_order_files( WC_Order $order ): void {
		$uploads = wp_upload_dir();
		$base    = trailingslashit( $uploads['basedir'] ) . 'grafik-designs/orders/' . $order->get_id();

		foreach ( $order->get_items() as $item_id => $item ) {
			$files = json_decode( (string) $item->get_meta( '_grafik_files', true ), true );
			if ( ! is_array( $files ) || ! $files ) {
				continue;
			}

			$directory = trailingslashit( $base ) . $item_id;
			$this->protect_directory( $directory );
			$moved = array();
			foreach ( $files as $file ) {
				if ( empty( $file['path'] ) || ! is_file( $file['path'] ) ) {
					continue;
				}
				$target = trailingslashit( $directory ) . wp_unique_filename( $directory, wp_basename( $file['path'] ) );
				if ( rename( $file['path'], $target ) ) {
					$file['path'] = $target;
				}
				$moved[] = $file;
			}
			$item->update_meta_data( '_grafik_files', wp_json_encode( $moved ) );
			$item->save();
		}
	}

	public function delivery_fields( WC_Checkout $checkout ): void {
		$regions = array(
			''                            => 'Selecciona una región',
			'Arica y Parinacota'          => 'Arica y Parinacota',
			'Tarapacá'                    => 'Tarapacá',
			'Antofagasta'                 => 'Antofagasta',
			'Atacama'                     => 'Atacama',
			'Coquimbo'                    => 'Coquimbo',
			'Valparaíso'                  => 'Valparaíso',
			'Metropolitana de Santiago'   => 'Metropolitana de Santiago',
			'O’Higgins'                   => 'O’Higgins',
			'Maule'                       => 'Maule',
			'Ñuble'                       => 'Ñuble',
			'Biobío'                      => 'Biobío',
			'La Araucanía'                => 'La Araucanía',
			'Los Ríos'                    => 'Los Ríos',
			'Los Lagos'                   => 'Los Lagos',
			'Aysén'                       => 'Aysén',
			'Magallanes'                  => 'Magallanes',
		);
		?>
		<section class="grafik-delivery-fields">
			<h3>Entrega</h3>
			<div class="grafik-delivery-choice">
				<label class="grafik-delivery-option">
					<input type="radio" name="grafik_delivery_method" value="pickup" checked>
					<strong>Retiro coordinado</strong>
					<small>Coordinaremos contigo el lugar y horario.</small>
				</label>
				<label class="grafik-delivery-option">
					<input type="radio" name="grafik_delivery_method" value="transport">
					<strong>Envío por transporte</strong>
					<small>Completa los datos necesarios para el despacho.</small>
				</label>
			</div>
			<div class="grafik-shipping-extra" hidden>
				<?php
				woocommerce_form_field(
					'grafik_shipping_rut',
					array( 'type' => 'text', 'label' => 'RUT', 'placeholder' => '12.345.678-9', 'required' => false ),
					$checkout->get_value( 'grafik_shipping_rut' )
				);
				woocommerce_form_field(
					'grafik_shipping_address',
					array( 'type' => 'text', 'label' => 'Dirección o sucursal', 'placeholder' => 'Calle, número, depto. o sucursal', 'required' => false ),
					$checkout->get_value( 'grafik_shipping_address' )
				);
				woocommerce_form_field(
					'grafik_shipping_region',
					array( 'type' => 'select', 'label' => 'Región', 'options' => $regions, 'required' => false ),
					$checkout->get_value( 'grafik_shipping_region' )
				);
				woocommerce_form_field(
					'grafik_shipping_city',
					array( 'type' => 'text', 'label' => 'Ciudad', 'required' => false ),
					$checkout->get_value( 'grafik_shipping_city' )
				);
				?>
			</div>
			<p class="form-row form-row-wide grafik-marketing-consent">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="grafik_marketing_consent" value="1">
					<span>Quiero recibir promociones y novedades por correo.</span>
				</label>
			</p>
		</section>
		<?php
	}

	public function validate_delivery_fields(): void {
		$method = isset( $_POST['grafik_delivery_method'] ) ? sanitize_key( wp_unslash( $_POST['grafik_delivery_method'] ) ) : 'pickup';
		if ( ! in_array( $method, array( 'pickup', 'transport' ), true ) ) {
			wc_add_notice( 'Selecciona una forma de entrega válida.', 'error' );
			return;
		}
		if ( 'transport' !== $method ) {
			return;
		}

		$required = array(
			'grafik_shipping_rut'     => 'Ingresa el RUT para el envío.',
			'grafik_shipping_address' => 'Ingresa la dirección o sucursal de envío.',
			'grafik_shipping_region'  => 'Selecciona la región de envío.',
			'grafik_shipping_city'    => 'Ingresa la ciudad de envío.',
		);
		foreach ( $required as $field => $message ) {
			if ( empty( $_POST[ $field ] ) ) {
				wc_add_notice( $message, 'error' );
			}
		}
	}

	public function save_delivery_fields( WC_Order $order ): void {
		$method = isset( $_POST['grafik_delivery_method'] ) ? sanitize_key( wp_unslash( $_POST['grafik_delivery_method'] ) ) : 'pickup';
		$order->update_meta_data( '_grafik_delivery_method', $method );

		$fields = array( 'grafik_shipping_rut', 'grafik_shipping_address', 'grafik_shipping_region', 'grafik_shipping_city' );
		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$order->update_meta_data( '_' . $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
			}
		}
		$order->update_meta_data( '_grafik_marketing_consent', isset( $_POST['grafik_marketing_consent'] ) ? 'yes' : 'no' );
	}

	public function admin_delivery_fields( WC_Order $order ): void {
		$method = $order->get_meta( '_grafik_delivery_method', true );
		echo '<div class="grafik-order-delivery"><h3>Entrega Grafik</h3>';
		echo '<p><strong>Método:</strong> ' . esc_html( 'transport' === $method ? 'Envío por transporte' : 'Retiro coordinado' ) . '</p>';
		if ( 'transport' === $method ) {
			echo '<p><strong>RUT:</strong> ' . esc_html( (string) $order->get_meta( '_grafik_shipping_rut', true ) ) . '<br>';
			echo '<strong>Dirección:</strong> ' . esc_html( (string) $order->get_meta( '_grafik_shipping_address', true ) ) . '<br>';
			echo '<strong>Región:</strong> ' . esc_html( (string) $order->get_meta( '_grafik_shipping_region', true ) ) . '<br>';
			echo '<strong>Ciudad:</strong> ' . esc_html( (string) $order->get_meta( '_grafik_shipping_city', true ) ) . '</p>';
		}
		echo '<p><strong>Promociones por correo:</strong> ' . esc_html( 'yes' === $order->get_meta( '_grafik_marketing_consent', true ) ? 'Aceptadas' : 'No aceptadas' ) . '</p>';
		echo '</div>';
	}

	public function email_delivery_fields( array $fields, bool $sent_to_admin, WC_Order $order ): array {
		$method = $order->get_meta( '_grafik_delivery_method', true );
		$fields['grafik_delivery_method'] = array(
			'label' => 'Entrega',
			'value' => 'transport' === $method ? 'Envío por transporte' : 'Retiro coordinado',
		);
		if ( 'transport' === $method ) {
			$fields['grafik_shipping_rut'] = array( 'label' => 'RUT de envío', 'value' => $order->get_meta( '_grafik_shipping_rut', true ) );
			$fields['grafik_shipping_address'] = array( 'label' => 'Dirección', 'value' => $order->get_meta( '_grafik_shipping_address', true ) );
			$fields['grafik_shipping_region'] = array( 'label' => 'Región', 'value' => $order->get_meta( '_grafik_shipping_region', true ) );
			$fields['grafik_shipping_city'] = array( 'label' => 'Ciudad', 'value' => $order->get_meta( '_grafik_shipping_city', true ) );
		}
		$fields['grafik_marketing_consent'] = array(
			'label' => 'Promociones por correo',
			'value' => 'yes' === $order->get_meta( '_grafik_marketing_consent', true ) ? 'Aceptadas' : 'No aceptadas',
		);
		return $fields;
	}

	public function admin_file_links( int $item_id, WC_Order_Item $item, $product ): void {
		if ( ! is_admin() || ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$files = json_decode( (string) $item->get_meta( '_grafik_files', true ), true );
		if ( ! is_array( $files ) || ! $files ) {
			return;
		}
		$order_id = $item->get_order_id();
		echo '<div class="grafik-admin-files"><strong>Diseños protegidos:</strong> ';
		foreach ( $files as $index => $file ) {
			$url = wp_nonce_url(
				admin_url(
					'admin-post.php?action=grafik_download_design&order_id=' . $order_id . '&item_id=' . $item_id . '&file=' . $index
				),
				'grafik_download_' . $order_id . '_' . $item_id . '_' . $index
			);
			echo '<a class="button button-small" href="' . esc_url( $url ) . '">' . esc_html( $file['name'] ?? 'Archivo' ) . '</a> ';
		}
		echo '</div>';
	}

	public function download_design(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'No tienes permiso para descargar estos archivos.', 403 );
		}

		$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
		$item_id  = isset( $_GET['item_id'] ) ? absint( $_GET['item_id'] ) : 0;
		$index    = isset( $_GET['file'] ) ? absint( $_GET['file'] ) : 0;
		check_admin_referer( 'grafik_download_' . $order_id . '_' . $item_id . '_' . $index );

		$order = wc_get_order( $order_id );
		$item  = $order ? $order->get_item( $item_id ) : false;
		if ( ! $item ) {
			wp_die( 'El archivo solicitado no existe.', 404 );
		}

		$files = json_decode( (string) $item->get_meta( '_grafik_files', true ), true );
		$file  = is_array( $files ) && isset( $files[ $index ] ) ? $files[ $index ] : null;
		if ( ! $file || empty( $file['path'] ) || ! is_file( $file['path'] ) ) {
			wp_die( 'El archivo solicitado no existe.', 404 );
		}

		$uploads = wp_upload_dir();
		$root    = realpath( trailingslashit( $uploads['basedir'] ) . 'grafik-designs' );
		$path    = realpath( $file['path'] );
		if ( ! $root || ! $path || ! str_starts_with( $path, $root . DIRECTORY_SEPARATOR ) ) {
			wp_die( 'Ruta de archivo no válida.', 403 );
		}

		nocache_headers();
		header( 'Content-Type: ' . sanitize_mime_type( $file['type'] ?? 'application/octet-stream' ) );
		header( 'Content-Disposition: attachment; filename="' . rawurlencode( sanitize_file_name( $file['name'] ?? wp_basename( $path ) ) ) . '"' );
		header( 'Content-Length: ' . filesize( $path ) );
		readfile( $path );
		exit;
	}

	public function cleanup_uploads(): void {
		$uploads = wp_upload_dir();
		$cart    = trailingslashit( $uploads['basedir'] ) . 'grafik-designs/cart';
		if ( ! is_dir( $cart ) ) {
			return;
		}

		$cutoff   = time() - ( 3 * DAY_IN_SECONDS );
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $cart, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ( $iterator as $entry ) {
			if ( $entry->isFile() && $entry->getMTime() < $cutoff ) {
				unlink( $entry->getPathname() );
			} elseif ( $entry->isDir() ) {
				@rmdir( $entry->getPathname() );
			}
		}
	}

	public function admin_menu(): void {
		$parent = class_exists( 'Grafik_Flow_Admin' ) ? 'grafik-dashboard' : 'woocommerce';
		add_submenu_page(
			$parent,
			'Configurador Tyvek',
			'Configurador Tyvek',
			'manage_woocommerce',
			'grafik-tyvek',
			array( $this, 'settings_page' )
		);
	}

	public function settings_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'No tienes permiso para administrar el configurador.' );
		}
		$product_id = $this->ensure_product();
		?>
		<div class="wrap grafik-tyvek-admin">
			<style>
				body{background:#f5f6f8}.grafik-tyvek-admin{max-width:1000px;margin:32px auto;padding-right:20px;color:#202223}
				.grafik-tyvek-admin *{box-sizing:border-box}.grafik-tyvek-admin h1{font-size:30px}.grafik-tyvek-admin__grid{display:grid;grid-template-columns:1.2fr .8fr;gap:18px}
				.grafik-tyvek-admin__card{padding:24px;border:1px solid #e1e3e5;border-radius:12px;background:#fff;box-shadow:0 1px 2px #0000000a}
				.grafik-tyvek-admin__card h2{margin-top:0}.grafik-tyvek-admin label{display:grid;gap:7px;margin-bottom:16px;color:#545a61;font-weight:600}
				.grafik-tyvek-admin input{width:100%;padding:11px;border:1px solid #babfc3;border-radius:8px}.grafik-tyvek-admin button{padding:12px 18px;border:0;border-radius:8px;color:#fff;background:#15171c;font-weight:700}
				.grafik-tyvek-admin a{color:#3b5ccc}.grafik-tyvek-admin__status{padding:14px;border-left:4px solid #2f8d55;background:#eefaf3}
				@media(max-width:760px){.grafik-tyvek-admin__grid{grid-template-columns:1fr}}
			</style>
			<p style="text-transform:uppercase;letter-spacing:.08em;color:#6d7175;font-weight:700">Grafik Publicidad</p>
			<h1>Configurador de pulseras Tyvek</h1>
			<?php if ( isset( $_GET['updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p>Configuración guardada.</p></div>
			<?php endif; ?>
			<div class="grafik-tyvek-admin__grid">
				<form class="grafik-tyvek-admin__card" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<input type="hidden" name="action" value="grafik_save_tyvek_settings">
					<?php wp_nonce_field( 'grafik_save_tyvek_settings' ); ?>
					<h2>Precios y cantidades</h2>
					<label>Precio por cada 100 unidades (CLP)<input type="number" min="1" name="price" value="<?php echo esc_attr( (string) $this->price() ); ?>"></label>
					<label>Aplicar descuento desde (unidades)<input type="number" min="100" step="100" name="threshold" value="<?php echo esc_attr( (string) $this->threshold() ); ?>"></label>
					<label>Porcentaje de descuento<input type="number" min="0" max="90" name="discount" value="<?php echo esc_attr( (string) $this->discount() ); ?>"></label>
					<label>Cantidad máxima por diseño<input type="number" min="100" step="100" name="max_units" value="<?php echo esc_attr( (string) $this->max_units() ); ?>"></label>
					<button type="submit">Guardar cambios</button>
				</form>
				<aside class="grafik-tyvek-admin__card">
					<h2>Estado</h2>
					<p class="grafik-tyvek-admin__status">Configurador activo y conectado a WooCommerce.</p>
					<p><strong>Producto:</strong><br><a href="<?php echo esc_url( get_edit_post_link( $product_id ) ); ?>">Editar Pulseras Tyvek</a></p>
					<p><strong>Archivos:</strong><br>Hasta 3 archivos PNG, JPG o PDF de 10 MB cada uno.</p>
					<p><strong>Pago:</strong><br>Se procesa con la pasarela activa de WooCommerce. Este plugin no guarda claves de Flow.</p>
				</aside>
			</div>
		</div>
		<?php
	}

	public function save_settings(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'No tienes permiso para cambiar esta configuración.', 403 );
		}
		check_admin_referer( 'grafik_save_tyvek_settings' );

		$price     = isset( $_POST['price'] ) ? max( 1, absint( $_POST['price'] ) ) : $this->price();
		$threshold = isset( $_POST['threshold'] ) ? max( 100, absint( $_POST['threshold'] ) ) : $this->threshold();
		$discount  = isset( $_POST['discount'] ) ? min( 90, max( 0, absint( $_POST['discount'] ) ) ) : $this->discount();
		$max_units = isset( $_POST['max_units'] ) ? max( 100, absint( $_POST['max_units'] ) ) : $this->max_units();

		$threshold = (int) ( ceil( $threshold / 100 ) * 100 );
		$max_units = (int) ( ceil( $max_units / 100 ) * 100 );
		$max_units = max( $threshold, $max_units );

		update_option( self::PRICE_OPTION, (string) $price );
		update_option( self::THRESHOLD_OPTION, (string) $threshold );
		update_option( self::DISCOUNT_OPTION, (string) $discount );
		update_option( self::MAX_OPTION, (string) $max_units );

		$product = wc_get_product( absint( get_option( self::PRODUCT_OPTION ) ) );
		if ( $product ) {
			$product->set_regular_price( (string) $price );
			$product->set_price( (string) $price );
			$product->save();
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'grafik-tyvek', 'updated' => '1' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	private function price(): int {
		return max( 1, absint( get_option( self::PRICE_OPTION, 10500 ) ) );
	}

	private function threshold(): int {
		return max( 100, absint( get_option( self::THRESHOLD_OPTION, 1000 ) ) );
	}

	private function discount(): int {
		return min( 90, max( 0, absint( get_option( self::DISCOUNT_OPTION, 20 ) ) ) );
	}

	private function max_units(): int {
		return max( $this->threshold(), absint( get_option( self::MAX_OPTION, 10000 ) ) );
	}
}

register_activation_hook( __FILE__, array( 'Grafik_Tyvek_Configurator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Grafik_Tyvek_Configurator', 'deactivate' ) );

add_action(
	'before_woocommerce_init',
	static function (): void {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, false );
		}
	}
);

add_action(
	'plugins_loaded',
	static function (): void {
		Grafik_Tyvek_Configurator::instance();
	}
);
