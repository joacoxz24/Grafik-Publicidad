<?php
/** Private merchant photos/receipts and tracking. Separate from customer design uploads. */
defined( 'ABSPATH' ) || exit;

final class Grafik_Order_Updates {
	public const EMAILS = array( 'grafik_order_ready' => 'Pedido listo', 'customer_completed_order' => 'Pedido completado', 'grafik_order_shipped' => 'Pedido enviado' );
	public const META = '_grafik_order_updates';
	public const MAX_FILES = 20;
	public const MAX_BYTES = 10485760;
	private static array $locks = array();
	private static bool $manual = false;

	public static function boot(): void {
		add_action( 'add_meta_boxes', array( __CLASS__, 'boxes' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'wp_ajax_grafik_order_updates', array( __CLASS__, 'ajax' ) );
		add_action( 'admin_post_grafik_order_media', array( __CLASS__, 'download' ) );
		add_action( 'admin_post_nopriv_grafik_order_media', array( __CLASS__, 'download' ) );
		add_action( 'woocommerce_email_order_details', array( __CLASS__, 'email_content' ), 30, 4 );
		foreach ( self::EMAILS as $id => $label ) {
			add_filter( 'woocommerce_email_enabled_' . $id, array( __CLASS__, 'allow_email' ), 99, 3 );
			add_action( 'woocommerce_order_action_grafik_resend_' . $id, static function ( $order ) use ( $id ) { self::resend( $order, $id ); } );
		}
		add_filter( 'woocommerce_order_actions', array( __CLASS__, 'actions' ), 10, 2 );
		add_action( 'woocommerce_email_sent', array( __CLASS__, 'email_sent' ), 10, 3 );
		add_action( 'shutdown', array( __CLASS__, 'unlock_all' ) );
	}

	public static function can_manage( WC_Order $order ): bool {
		return $order->get_id() > 0 && ( current_user_can( 'manage_woocommerce' ) || current_user_can( 'edit_shop_orders' ) ) && current_user_can( 'edit_shop_order', $order->get_id() );
	}

	public static function data( WC_Order $order ): array {
		$data = $order->get_meta( self::META, true );
		return array_replace( array( 'files' => array(), 'tracking' => array(), 'revision' => '' ), is_array( $data ) ? $data : array() );
	}

	/** Never write into uploads. On unknown server layouts fail closed until configured. */
	public static function root(): string {
		$document_path = $_SERVER['DOCUMENT_ROOT'] ?? '';
		// Scheduled WooCommerce emails can run via CLI without DOCUMENT_ROOT.
		$document = realpath( $document_path ?: get_option( 'grafik_media_document_root', '' ) );
		if ( ! $document || ! ( $document_path ?: get_option( 'grafik_media_document_root', '' ) ) ) { throw new RuntimeException( 'No se pudo verificar la carpeta privada. Abre un pedido desde WordPress para inicializarla.' ); }
		$dir = defined( 'GRAFIK_ORDER_MEDIA_DIR' ) ? GRAFIK_ORDER_MEDIA_DIR : dirname( $document ) . '/.grafik-order-media-' . substr( hash( 'sha256', home_url() ), 0, 12 );
		if ( ! path_is_absolute( $dir ) || ! wp_mkdir_p( $dir ) ) { throw new RuntimeException( 'No se pudo crear la carpeta privada fuera del sitio. Solicita al hosting configurar GRAFIK_ORDER_MEDIA_DIR.' ); }
		$root = realpath( $dir );
		foreach ( array_filter( array( $document, realpath( ABSPATH ), defined( 'WP_CONTENT_DIR' ) ? realpath( WP_CONTENT_DIR ) : false ) ) as $public ) {
			if ( self::within( $root, $public ) ) { throw new RuntimeException( 'La carpeta de comprobantes debe estar fuera del directorio público.' ); }
		}
		if ( $document_path && get_option( 'grafik_media_document_root' ) !== $document ) { update_option( 'grafik_media_document_root', $document, false ); }
		return $root;
	}

	private static function within( string $path, string $base ): bool {
		$path = strtolower( str_replace( '\\', '/', $path ) );
		$base = rtrim( strtolower( str_replace( '\\', '/', $base ) ), '/' );
		return $path === $base || str_starts_with( $path, $base . '/' );
	}

	public static function resolve( WC_Order $order, array $file ): ?string {
		if ( ! preg_match( '/^[a-f0-9]{32}$/D', $file['id'] ?? '' ) || ! in_array( $file['ext'] ?? '', array( 'jpg', 'png', 'pdf' ), true ) ) { return null; }
		try { $root = self::root(); } catch ( RuntimeException $e ) { return null; }
		$dir = $root . '/' . $order->get_id();
		$path = $dir . '/' . $file['id'] . '.' . $file['ext'];
		$real = realpath( $path );
		return $real && ! is_link( $dir ) && ! is_link( $path ) && self::within( $real, $root ) && self::within( $real, $dir ) && is_file( $real ) ? $real : null;
	}

	public static function validate_file( array $upload ): array {
		if ( ! class_exists( 'finfo' ) ) { throw new RuntimeException( 'El hosting debe activar la extensión PHP fileinfo para validar archivos.' ); }
		if ( UPLOAD_ERR_OK !== ( $upload['error'] ?? -1 ) || ! is_file( $upload['tmp_name'] ?? '' ) ) { throw new RuntimeException( 'No se pudo recibir el archivo. Revisa el límite de subida del hosting.' ); }
		$size = filesize( $upload['tmp_name'] );
		if ( $size < 1 || $size > self::MAX_BYTES ) { throw new RuntimeException( 'Cada archivo debe pesar entre 1 byte y 10 MB.' ); }
		$name = sanitize_file_name( $upload['name'] ?? '' );
		$extension = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		$allowed = array( 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf' );
		$mime = ( new finfo( FILEINFO_MIME_TYPE ) )->file( $upload['tmp_name'] );
		if ( ! isset( $allowed[ $extension ] ) || $allowed[ $extension ] !== $mime ) { throw new RuntimeException( 'Usa archivos JPG, PNG o PDF reales; la extensión debe coincidir con el contenido.' ); }
		if ( 'application/pdf' !== $mime && ! @getimagesize( $upload['tmp_name'] ) ) { throw new RuntimeException( 'La imagen no es válida.' ); }
		return array( 'id' => bin2hex( random_bytes( 16 ) ), 'name' => $name, 'ext' => 'jpeg' === $extension ? 'jpg' : $extension, 'mime' => $mime, 'size' => $size, 'secret' => bin2hex( random_bytes( 32 ) ), 'emails' => array() );
	}

	public static function tracking( array $input ): array {
		$result = array();
		foreach ( array( 'carrier', 'code', 'url', 'date' ) as $field ) {
			$value = is_string( $input[ $field ] ?? null ) ? trim( wp_unslash( $input[ $field ] ) ) : '';
			if ( '' === $value ) { continue; }
			if ( 'carrier' === $field && ! in_array( $value, array( 'Starken', 'Blue Express', 'Otro' ), true ) ) { throw new RuntimeException( 'Selecciona un transportista válido.' ); }
			if ( 'url' === $field ) {
				$parts = wp_parse_url( $value );
				if ( ! $parts || ! in_array( $parts['scheme'] ?? '', array( 'http', 'https' ), true ) || empty( $parts['host'] ) || isset( $parts['user'] ) || isset( $parts['pass'] ) ) { throw new RuntimeException( 'El enlace de seguimiento debe ser una URL http o https completa.' ); }
				$value = esc_url_raw( $value, array( 'http', 'https' ) );
			} elseif ( 'date' === $field ) {
				$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $value );
				if ( ! $date || $date->format( 'Y-m-d' ) !== $value ) { throw new RuntimeException( 'La fecha de despacho no es válida.' ); }
			} else { $value = mb_substr( sanitize_text_field( $value ), 0, 160 ); }
			$result[ $field ] = $value;
		}
		return $result;
	}

	/** Save metadata only. File removal immediately revokes all previously mailed URLs. */
	public static function save( WC_Order $order, array $input ): void {
		$data = self::data( $order );
		if ( ! is_string( $input['revision'] ?? null ) || ! hash_equals( $data['revision'], $input['revision'] ) ) { throw new RuntimeException( 'El pedido cambió en otra ventana. Recarga antes de guardar.' ); }
		$tracking = self::tracking( is_array( $input['tracking'] ?? null ) ? $input['tracking'] : array() );
		$files = array(); $removed = array();
		foreach ( $data['files'] as $file ) {
			if ( in_array( $file['id'], (array) ( $input['remove'] ?? array() ), true ) ) { $removed[] = $file; continue; }
			$selected = array_filter( (array) ( $input['selection'][ $file['id'] ] ?? array() ), 'is_string' );
			$file['emails'] = array_values( array_intersect( array_keys( self::EMAILS ), $selected ) );
			$files[] = $file;
		}
		$order->update_meta_data( self::META, array( 'files' => $files, 'tracking' => $tracking, 'revision' => bin2hex( random_bytes( 16 ) ) ) );
		$order->save_meta_data();
		foreach ( $removed as $file ) { $path = self::resolve( $order, $file ); if ( $path ) { wp_delete_file( $path ); } }
	}

	public static function signature( WC_Order $order, array $file, int $expires ): string {
		return hash_hmac( 'sha256', $order->get_id() . '|' . $file['id'] . '|' . $file['secret'] . '|' . $expires, wp_salt( 'auth' ) );
	}

	public static function url( WC_Order $order, array $file, bool $customer = false ): string {
		$args = array( 'action' => 'grafik_order_media', 'order' => $order->get_id(), 'file' => $file['id'] );
		if ( $customer ) { $args['expires'] = time() + 90 * DAY_IN_SECONDS; $args['signature'] = self::signature( $order, $file, $args['expires'] ); }
		return add_query_arg( $args, admin_url( 'admin-post.php' ) );
	}

	public static function authorized_file( WC_Order $order, array $request, bool $admin ): ?array {
		if ( ! is_string( $request['file'] ?? null ) ) { return null; }
		foreach ( self::data( $order )['files'] as $file ) {
			if ( ! hash_equals( $file['id'], $request['file'] ) ) { continue; }
			$expires = (int) ( $request['expires'] ?? 0 );
			$signature = is_string( $request['signature'] ?? null ) ? $request['signature'] : '';
			if ( $admin || ( ! empty( $file['emails'] ) && $expires >= time() && $expires <= time() + 90 * DAY_IN_SECONDS + 60 && hash_equals( self::signature( $order, $file, $expires ), $signature ) ) ) { return $file; }
		}
		return null;
	}

	public static function download(): void {
		$order = wc_get_order( absint( $_GET['order'] ?? 0 ) );
		$file = $order instanceof WC_Order ? self::authorized_file( $order, $_GET, self::can_manage( $order ) ) : null;
		$path = $file ? self::resolve( $order, $file ) : null;
		if ( ! $path ) { wp_die( 'El enlace no es válido o expiró. Solicita un nuevo aviso a Grafik.', '', array( 'response' => 404 ) ); }
		nocache_headers();
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Robots-Tag: noindex, nofollow, noarchive' );
		header( 'Referrer-Policy: no-referrer' );
		header( "Content-Security-Policy: sandbox; default-src 'none'" );
		header( 'Content-Type: ' . $file['mime'] );
		header( 'Content-Disposition: inline; filename="' . rawurlencode( $file['name'] ) . '"; filename*=UTF-8\'\'' . rawurlencode( $file['name'] ) );
		header( 'Content-Length: ' . filesize( $path ) );
		readfile( $path ); exit;
	}

	public static function boxes(): void {
		foreach ( array_unique( array( 'shop_order', function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'woocommerce_page_wc-orders' ) ) as $screen ) {
			add_meta_box( 'grafik-order-updates', 'Fotos, comprobantes y seguimiento', array( __CLASS__, 'box' ), $screen, 'normal', 'default' );
		}
	}

	public static function assets(): void {
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->id, array( 'shop_order', function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'woocommerce_page_wc-orders' ), true ) ) { return; }
		wp_enqueue_style( 'grafik-order-updates', GRAFIK_TYVEK_URL . 'assets/css/order-updates.css', array(), GRAFIK_TYVEK_VERSION );
		wp_enqueue_script( 'grafik-order-updates', GRAFIK_TYVEK_URL . 'assets/js/order-updates.js', array(), GRAFIK_TYVEK_VERSION, true );
	}

	public static function box( $object ): void {
		$order = $object instanceof WC_Order ? $object : wc_get_order( $object->ID ?? 0 );
		if ( ! $order || ! self::can_manage( $order ) ) { return; }
		$data = self::data( $order );
		echo '<div class="grafik-order-updates" data-order="' . esc_attr( $order->get_id() ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'grafik_order_updates_' . $order->get_id() ) ) . '" data-url="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '" data-revision="' . esc_attr( $data['revision'] ) . '">';
		echo '<p>Guarda este apartado antes de cambiar el estado del pedido. Guardar o subir archivos no envía correos ni cambia el estado.</p><p>JPG, PNG o PDF · hasta 20 archivos por pedido · 10 MB por archivo.</p>';
		try { self::root(); } catch ( RuntimeException $e ) { echo '<p class="notice notice-error">' . esc_html( $e->getMessage() ) . '</p>'; }
		echo '<div data-media-list>' . self::file_rows( $order ) . '</div><label class="grafik-update-upload">Agregar fotos o comprobantes <input type="file" accept=".jpg,.jpeg,.png,.pdf" multiple data-media-upload></label>';
		echo '<h4>Seguimiento para Pedido completado y Pedido enviado</h4><p>Opcional. Solo se muestra lo rellenado y se oculta para retiro presencial.</p><div class="grafik-tracking-fields">';
		echo '<label>Transportista<select data-tracking="carrier"><option value="">Sin indicar</option>';
		foreach ( array( 'Starken', 'Blue Express', 'Otro' ) as $carrier ) { echo '<option ' . selected( $data['tracking']['carrier'] ?? '', $carrier, false ) . '>' . esc_html( $carrier ) . '</option>'; }
		echo '</select></label>';
		foreach ( array( 'code' => array( 'Número de seguimiento', 'text' ), 'url' => array( 'Enlace de seguimiento', 'url' ), 'date' => array( 'Fecha de despacho', 'date' ) ) as $key => $field ) {
			echo '<label>' . esc_html( $field[0] ) . '<input type="' . esc_attr( $field[1] ) . '" data-tracking="' . esc_attr( $key ) . '" value="' . esc_attr( $data['tracking'][ $key ] ?? '' ) . '"></label>';
		}
		echo '</div><p><button type="button" class="button button-primary" data-media-save>Guardar fotos y seguimiento</button></p><p role="status" aria-live="polite" data-media-status></p><p>Para reenviar, guarda primero y elige «Reenviar: Pedido listo/completado/enviado (Grafik)» en Acciones del pedido. Los correos deben estar activados en WooCommerce.</p></div>';
	}

	private static function file_rows( WC_Order $order ): string {
		ob_start();
		foreach ( self::data( $order )['files'] as $file ) {
			echo '<div class="grafik-update-file" data-media-id="' . esc_attr( $file['id'] ) . '">';
			if ( 'application/pdf' !== $file['mime'] ) { echo '<img alt="" loading="lazy" src="' . esc_url( self::url( $order, $file ) ) . '">'; }
			echo '<div><a target="_blank" rel="noopener noreferrer" href="' . esc_url( self::url( $order, $file ) ) . '">' . esc_html( $file['name'] ) . '</a><div class="grafik-update-choices">';
			foreach ( self::EMAILS as $id => $label ) { echo '<label><input type="checkbox" value="' . esc_attr( $id ) . '" ' . checked( in_array( $id, $file['emails'], true ), true, false ) . '> ' . esc_html( $label ) . '</label>'; }
			echo '</div><label><input type="checkbox" data-media-remove> Quitar del pedido al guardar</label></div></div>';
		}
		return (string) ob_get_clean();
	}

	/** AJAX uses a per-order nonce, capability and exclusive storage lock. No email calls. */
	public static function ajax(): void {
		$order_id = absint( $_POST['order'] ?? 0 );
		check_ajax_referer( 'grafik_order_updates_' . $order_id, 'nonce' );
		$order = wc_get_order( $order_id );
		if ( ! $order || ! self::can_manage( $order ) ) { wp_send_json_error( array( 'message' => 'No tienes permiso para editar este pedido.' ), 403 ); }
		$lock = null;
		try {
			$root = self::root();
			$lock = fopen( $root . '/order-' . $order_id . '.lock', 'c' );
			if ( ! $lock || ! flock( $lock, LOCK_EX | LOCK_NB ) ) { throw new RuntimeException( 'Otro guardado está en curso. Intenta nuevamente.' ); }
			$order->read_meta_data( true );
			$input = json_decode( wp_unslash( $_POST['payload'] ?? '' ), true );
			if ( ! is_array( $input ) ) { throw new RuntimeException( 'Datos inválidos.' ); }
			if ( 'upload' === ( $_POST['operation'] ?? '' ) ) {
				$data = self::data( $order );
				if ( count( $data['files'] ) >= self::MAX_FILES ) { throw new RuntimeException( 'Máximo 20 archivos por pedido.' ); }
				$upload = $_FILES['file'] ?? array();
				$file = self::validate_file( $upload );
				$dir = $root . '/' . $order_id;
				if ( is_link( $dir ) || ! wp_mkdir_p( $dir ) || ! self::within( realpath( $dir ), $root ) ) { throw new RuntimeException( 'No se pudo preparar la carpeta privada.' ); }
				$path = $dir . '/' . $file['id'] . '.' . $file['ext'];
				if ( ! move_uploaded_file( $upload['tmp_name'], $path ) ) { throw new RuntimeException( 'No se pudo guardar el archivo.' ); }
				@chmod( $path, 0600 );
				$data['files'][] = $file; $data['revision'] = bin2hex( random_bytes( 16 ) );
				$order->update_meta_data( self::META, $data );
				try { $order->save_meta_data(); } catch ( Throwable $e ) { wp_delete_file( $path ); throw $e; }
			} else { self::save( $order, $input ); }
			$response = array( 'html' => self::file_rows( $order ), 'revision' => self::data( $order )['revision'] );
		} catch ( Throwable $e ) {
			if ( $lock ) { flock( $lock, LOCK_UN ); fclose( $lock ); }
			wp_send_json_error( array( 'message' => $e instanceof RuntimeException ? $e->getMessage() : 'No se pudo guardar. Revisa el registro de WooCommerce.' ), 400 );
		}
		if ( $lock ) { flock( $lock, LOCK_UN ); fclose( $lock ); }
		wp_send_json_success( $response );
	}

	public static function email_content( $order, $admin, $plain, $email ): void {
		if ( $admin || ! $order instanceof WC_Order || ! isset( self::EMAILS[ $email->id ?? '' ] ) ) { return; }
		$data = self::data( $order ); $lines = array();
		$delivery = self::delivery( $order );
		if ( 'grafik_order_ready' === $email->id ) {
			$method = $delivery['method'];
			$label = $delivery['label'];
			$lines['Entrega'] = 'transport' === $method ? 'Tu pedido está listo para despacho. Te avisaremos cuando sea enviado.' : ( $label ? 'Tu pedido está listo. ' . $label . ': coordinaremos contigo el lugar y horario de entrega.' : 'Tu pedido está listo. Coordinaremos contigo la entrega según lo acordado.' );
		}
		if ( 'grafik_order_ready' !== $email->id && 'transport' === $delivery['method'] ) {
			foreach ( array( 'carrier' => 'Transportista', 'code' => 'Número de seguimiento', 'date' => 'Fecha de despacho', 'url' => 'Seguir mi envío' ) as $field => $label ) {
				if ( ! empty( $data['tracking'][ $field ] ) ) { $lines[ $label ] = 'date' === $field ? wp_date( 'd/m/Y', strtotime( $data['tracking'][ $field ] . ' 12:00:00 UTC' ) ) : $data['tracking'][ $field ]; }
			}
		}
		$files = array_filter( $data['files'], static fn( $file ) => in_array( $email->id, $file['emails'], true ) && self::resolve( $order, $file ) );
		if ( ! $lines && ! $files ) { return; }
		if ( ! $plain ) { echo '<div style="margin:24px 0;padding:20px;border:1px solid #21d4e8;border-radius:12px"><h2 style="font-size:20px">Fotos, comprobantes y seguimiento</h2>'; }
		foreach ( $lines as $label => $value ) {
			if ( $plain ) { echo $label . ': ' . $value . "\n"; }
			else { echo '<p><strong>' . esc_html( $label ) . ':</strong> ' . ( 'Seguir mi envío' === $label ? '<a href="' . esc_url( $value ) . '">Ver seguimiento</a>' : esc_html( $value ) ) . '</p>'; }
		}
		foreach ( $files as $file ) {
			$url = self::url( $order, $file, true );
			if ( $plain ) { echo $file['name'] . ': ' . $url . "\n"; continue; }
			echo '<p style="margin:20px 0;overflow-wrap:anywhere">';
			if ( 'application/pdf' !== $file['mime'] ) { echo '<a href="' . esc_url( $url ) . '"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $file['name'] ) . '" width="480" style="display:block;width:100%;max-width:480px;height:auto;border-radius:8px;margin-bottom:8px"></a>'; }
			echo esc_html( $file['name'] ) . '<br><a href="' . esc_url( $url ) . '">' . ( 'application/pdf' === $file['mime'] ? 'Ver comprobante' : 'Ver foto' ) . '</a></p>';
		}
		if ( ! $plain ) { echo '</div>'; } else { echo "\n"; }
	}

	/** Prefer the existing Grafik choice; fall back to WooCommerce shipping for other orders. */
	public static function delivery( WC_Order $order ): array {
		$method = $order->get_meta( '_grafik_delivery_method', true );
		if ( isset( Grafik_Tyvek_Configurator::DELIVERY_LABELS[ $method ] ) ) { return array( 'method' => $method, 'label' => Grafik_Tyvek_Configurator::DELIVERY_LABELS[ $method ] ); }
		foreach ( $order->get_shipping_methods() as $shipping ) {
			$id = $shipping->get_method_id();
			if ( in_array( $id, array( 'local_pickup', 'pickup_location' ), true ) ) { return array( 'method' => 'pickup', 'label' => $shipping->get_name() ); }
		}
		return array( 'method' => $order->get_shipping_methods() ? 'transport' : '', 'label' => '' );
	}

	/** Successful delivery to the mail transport is remembered; failures may be retried. */
	public static function allow_email( $enabled, $order, $email ): bool {
		if ( ! $enabled || ! $order instanceof WC_Order || ! isset( self::EMAILS[ $email->id ] ) || ! $order->get_billing_email() ) { return (bool) $enabled; }
		$key = 'grafik_email_' . $order->get_id() . '_' . $email->id;
		if ( isset( self::$locks[ $key ] ) ) { return false; }
		// Atomic option acquisition prevents concurrent transition/retry workers sending twice.
		$old = get_option( $key );
		if ( $old && (int) $old < time() - 300 ) { delete_option( $key ); }
		if ( ! add_option( $key, time(), '', false ) ) { return false; }
		$order->read_meta_data( true );
		$sent = $order->get_meta( '_grafik_notice_' . $email->id, true );
		if ( ! self::$manual && is_array( $sent ) && ( $sent['recipient'] ?? '' ) === hash( 'sha256', strtolower( $order->get_billing_email() ) ) ) { delete_option( $key ); return false; }
		self::$locks[ $key ] = true;
		return true;
	}

	public static function email_sent( $success, $id, $email ): void {
		$order = $email->object ?? null;
		if ( ! isset( self::EMAILS[ $id ] ) || ! $order instanceof WC_Order ) { return; }
		if ( $success ) {
			$order->update_meta_data( '_grafik_notice_' . $id, array( 'time' => time(), 'recipient' => hash( 'sha256', strtolower( $order->get_billing_email() ) ) ) );
			$order->save_meta_data();
		}
		$key = 'grafik_email_' . $order->get_id() . '_' . $id;
		if ( isset( self::$locks[ $key ] ) ) { delete_option( $key ); unset( self::$locks[ $key ] ); }
	}

	public static function unlock_all(): void {
		foreach ( self::$locks as $key => $value ) { delete_option( $key ); }
		self::$locks = array();
	}

	public static function actions( array $actions, $order = null ): array {
		if ( ! $order instanceof WC_Order || ! self::can_manage( $order ) ) { return $actions; }
		foreach ( self::EMAILS as $id => $label ) { $actions[ 'grafik_resend_' . $id ] = 'Reenviar: ' . $label . ' (Grafik)'; }
		return $actions;
	}

	public static function resend( WC_Order $order, string $id ): void {
		if ( ! self::can_manage( $order ) || ! isset( self::EMAILS[ $id ] ) ) { return; }
		self::$manual = true;
		try {
			foreach ( WC()->mailer()->get_emails() as $email ) {
				if ( $id === $email->id ) { $email->trigger( $order->get_id(), $order ); break; }
			}
		} finally { self::$manual = false; }
	}
}
