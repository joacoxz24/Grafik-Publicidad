<?php
/** Private, per-line-item designs. All persisted paths are relative to grafik-designs. */
defined( 'ABSPATH' ) || exit;

final class Grafik_Order_Files {
	public static function boot(): void {
		add_filter( 'woocommerce_hidden_order_itemmeta', static fn( $keys ) => array_unique( array_merge( $keys, array( '_grafik_files', '_grafik_item_uuid' ) ) ) );
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', static function ( $meta ) {
			return array_filter( $meta, static fn( $entry ) => ! in_array( $entry->key, array( '_grafik_files', '_grafik_item_uuid' ), true ) );
		} );
		add_action( 'admin_post_nopriv_grafik_download_design', static function () { auth_redirect(); exit; } );
		add_action( 'woocommerce_email_order_details', array( __CLASS__, 'email_files' ), 25, 4 );
	}

	public static function root(): string {
		return rtrim( wp_upload_dir()['basedir'], '/\\' ) . '/grafik-designs';
	}

	public static function decode( $value ): array {
		$files = is_array( $value ) ? $value : json_decode( (string) $value, true );
		return is_array( $files ) ? array_values( array_filter( $files, 'is_array' ) ) : array();
	}

	/** Reject traversal, absolute paths and symlinks leaving the expected directory. */
	public static function resolve( string $relative, string $prefix ): ?string {
		if ( ! str_starts_with( $relative, $prefix . '/' ) || preg_match( '~(?:^|/)\.{1,2}(?:/|$)|[\\\\\x00:]~', $relative ) ) { return null; }
		$root = realpath( self::root() );
		$base = realpath( self::root() . '/' . $prefix );
		$path = realpath( self::root() . '/' . $relative );
		if ( ! $root || ! $base || ! $path || ! is_file( $path ) ) { return null; }
		if ( ! str_starts_with( $base, $root . DIRECTORY_SEPARATOR ) || ! str_starts_with( $path, $base . DIRECTORY_SEPARATOR ) ) { return null; }
		return $path;
	}

	/** Convert legacy server-side cart records before they become order metadata. */
	public static function stage( array $files ): array {
		if ( ! $files ) { return array(); }
		$root = str_replace( '\\', '/', self::root() ) . '/';
		$result = array();
		foreach ( $files as $file ) {
			$path = str_replace( '\\', '/', (string) ( $file['path'] ?? '' ) );
			$relative = (string) ( $file['relative_path'] ?? ( str_starts_with( $path, $root ) ? substr( $path, strlen( $root ) ) : '' ) );
			if ( ! $relative || preg_match( '~(?:^|/)\.{1,2}(?:/|$)|[\\\\\x00:]~', $relative ) ) { continue; }
			$result[] = array(
				'original_name' => sanitize_file_name( $file['original_name'] ?? $file['name'] ?? basename( $relative ) ),
				'stored_name' => basename( $relative ), 'relative_path' => $relative,
				'mime_type' => sanitize_mime_type( $file['mime_type'] ?? $file['type'] ?? 'application/octet-stream' ),
				'file_size' => (int) ( $file['file_size'] ?? 0 ),
			);
		}
		return $result;
	}

	public static function mime( string $path ): string {
		$image = @getimagesize( $path );
		if ( $image && in_array( $image['mime'] ?? '', array( 'image/jpeg', 'image/png', 'image/webp' ), true ) ) { return $image['mime']; }
		if ( function_exists( 'finfo_open' ) ) {
			$info = finfo_open( FILEINFO_MIME_TYPE );
			$type = $info ? finfo_file( $info, $path ) : false;
			if ( $info ) { finfo_close( $info ); }
			if ( $type ) { return sanitize_mime_type( $type ); }
		}
		$handle = fopen( $path, 'rb' );
		$magic = $handle ? fread( $handle, 5 ) : '';
		if ( $handle ) { fclose( $handle ); }
		return '%PDF-' === $magic ? 'application/pdf' : 'application/octet-stream';
	}

	/** A resumed checkout may delete/recreate line items. Recover only unambiguous owners. */
	private static function recover_orphans( WC_Order_Item $item, string $dir ): void {
		$order_id = (int) $item->get_order_id();
		$order = wc_get_order( $order_id );
		if ( ! $order ) { return; }
		$items = $order->get_items();
		$parent = dirname( $dir );
		foreach ( new DirectoryIterator( $parent ) as $folder ) {
			$old_id = $folder->getFilename();
			if ( ! ctype_digit( $old_id ) || ! $folder->isDir() || $folder->isLink() || isset( $items[ (int) $old_id ] ) || (int) $old_id === (int) $item->get_id() ) { continue; }
			$manifest_path = $folder->getPathname() . '/.grafik-item.json';
			$manifest = is_file( $manifest_path ) && ! is_link( $manifest_path ) ? json_decode( file_get_contents( $manifest_path ), true ) : null;
			foreach ( new DirectoryIterator( $folder->getPathname() ) as $entry ) {
				$stored = $entry->getFilename();
				if ( ! $entry->isFile() || $entry->isLink() || str_starts_with( $stored, '.' ) || 'index.php' === $stored ) { continue; }
				$name = sanitize_file_name( preg_replace( '/^[A-Za-z0-9]{20}-/', '', $stored ) );
				$candidates = array();
				foreach ( $items as $candidate ) {
					if ( ! empty( $manifest['grafik_item_uuid'] ) ) {
						$matches = $manifest['grafik_item_uuid'] === $candidate->get_meta( '_grafik_item_uuid', true );
					} else {
						$names = array_map( 'trim', explode( '·', (string) $candidate->get_meta( 'Archivos', true ) ) );
						$matches = in_array( $name, $names, true );
					}
					if ( $matches ) { $candidates[] = (int) $candidate->get_id(); }
				}
				if ( array( (int) $item->get_id() ) !== $candidates ) { continue; }
				$source = self::resolve( 'orders/' . $order_id . '/' . $old_id . '/' . $stored, 'orders/' . $order_id . '/' . $old_id );
				if ( $source && ! file_exists( $dir . '/' . $stored ) ) { rename( $source, $dir . '/' . $stored ); }
			}
		}
	}

	/** Repeatable finalization and lazy recovery, using WC CRUD (including HPOS). */
	public static function recover( WC_Order_Item $item ): array {
		$order_id = (int) $item->get_order_id();
		$item_id = (int) $item->get_id();
		if ( ! $order_id || ! $item_id ) { return array(); }
		$prefix = 'orders/' . $order_id . '/' . $item_id;
		$dir = self::root() . '/' . $prefix;
		$old = self::decode( $item->get_meta( '_grafik_files', true ) );
		if ( ! $old && ! is_dir( $dir ) && ( ! is_dir( dirname( $dir ) ) || ! $item->get_meta( '_grafik_item_uuid', true ) ) ) { return array(); }
		if ( ! wp_mkdir_p( $dir ) ) { return array(); }
		$protection = self::root() . '/.htaccess';
		if ( ! file_exists( $protection ) ) {
			file_put_contents( $protection, "Options -Indexes\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" );
		}
		// A file lock also protects independent PHP workers. It is never removed while in use.
		$root = realpath( self::root() );
		$actual_dir = realpath( $dir );
		if ( ! $root || ! $actual_dir || ! str_starts_with( $actual_dir, $root . DIRECTORY_SEPARATOR ) || is_link( $dir ) ) { return array(); }
		$lock = fopen( $dir . '/.grafik-lock', 'c' );
		if ( ! $lock || ! flock( $lock, LOCK_EX ) ) { if ( $lock ) { fclose( $lock ); } return array(); }
		try {
			$uuid = (string) $item->get_meta( '_grafik_item_uuid', true );
			if ( ! $uuid ) { $uuid = wp_generate_uuid4(); $item->update_meta_data( '_grafik_item_uuid', $uuid ); }
			self::recover_orphans( $item, $dir );
			// The UUID survives WooCommerce's replacement of a line item during checkout retry.
			file_put_contents( $dir . '/.grafik-item.json', wp_json_encode( array( 'order_id' => $order_id, 'order_item_id' => $item_id, 'grafik_item_uuid' => $uuid ) ), LOCK_EX );
			$records = array();
			foreach ( self::stage( $old ) as $file ) {
				$relative = $file['relative_path'];
				$stored = $file['stored_name'];
				$final = $prefix . '/' . $stored;
				$target = self::resolve( $final, $prefix );
				if ( ! $target && str_starts_with( $relative, 'cart/' ) ) {
					$source = self::resolve( $relative, 'cart' );
					if ( $source && ! file_exists( $dir . '/' . $stored ) && rename( $source, $dir . '/' . $stored ) ) {
						$target = self::resolve( $final, $prefix );
					}
				}
				// Never import a different order/item's file, even if metadata was tampered with.
				if ( ! str_starts_with( $relative, 'cart/' ) && ! str_starts_with( $relative, $prefix . '/' ) ) { continue; }
				$file['order_id'] = $order_id;
				$file['order_item_id'] = $item_id;
				$file['grafik_item_uuid'] = $uuid;
				if ( $target ) {
					$file['relative_path'] = $final;
					$file['mime_type'] = self::mime( $target );
					$file['file_size'] = filesize( $target );
				}
				// Retain a missing/staged reference for diagnosis and retry; don't silently erase it.
				$records[ $file['relative_path'] ] = $file;
			}
			foreach ( new DirectoryIterator( $dir ) as $entry ) {
				$name = $entry->getFilename();
				if ( ! $entry->isFile() || $entry->isLink() || str_starts_with( $name, '.' ) || 'index.php' === $name ) { continue; }
				$relative = $prefix . '/' . $name;
				$path = self::resolve( $relative, $prefix );
				if ( ! $path || isset( $records[ $relative ] ) ) { continue; }
				$records[ $relative ] = array(
					'original_name' => sanitize_file_name( preg_replace( '/^[A-Za-z0-9]{20}-/', '', $name ) ),
					'stored_name' => $name, 'relative_path' => $relative,
					'mime_type' => self::mime( $path ), 'file_size' => filesize( $path ),
					'order_id' => $order_id, 'order_item_id' => $item_id, 'grafik_item_uuid' => $uuid,
				);
			}
			ksort( $records );
			$files = array_values( $records );
			if ( $files !== $old ) { $item->update_meta_data( '_grafik_files', wp_json_encode( $files ) ); $item->save(); }
			return $files;
		} finally { flock( $lock, LOCK_UN ); fclose( $lock ); }
	}

	public static function finalize( WC_Order $order ): void {
		foreach ( $order->get_items() as $item ) { self::recover( $item ); }
	}

	public static function can_manage(): bool {
		return current_user_can( 'manage_woocommerce' ) || current_user_can( 'edit_shop_orders' );
	}

	public static function url( array $file, bool $view = false ): string {
		// No expiring user nonce: email links are read-only and ALWAYS require an authorized login.
		return add_query_arg( array( 'action' => 'grafik_download_design', 'order_id' => $file['order_id'], 'item_id' => $file['order_item_id'], 'file_id' => hash( 'sha256', $file['relative_path'] ), 'view' => $view ? '1' : '0' ), admin_url( 'admin-post.php' ) );
	}

	public static function available( array $file ): ?string {
		return self::resolve( $file['relative_path'], 'orders/' . $file['order_id'] . '/' . $file['order_item_id'] );
	}

	public static function preview_type( array $file ): string {
		$ext = strtolower( pathinfo( $file['original_name'], PATHINFO_EXTENSION ) );
		if ( in_array( $ext, array( 'jpg', 'jpeg', 'png', 'webp' ), true ) && in_array( $file['mime_type'], array( 'image/jpeg', 'image/png', 'image/webp' ), true ) ) { return 'image'; }
		return 'pdf' === $ext && 'application/pdf' === $file['mime_type'] ? 'pdf' : '';
	}

	public static function admin( WC_Order_Item $item ): void {
		if ( ! is_admin() || ! self::can_manage() ) { return; }
		$files = self::recover( $item );
		if ( ! $files ) { return; }
		echo '<div class="grafik-admin-files" style="margin-top:16px"><strong>ARCHIVOS DEL CLIENTE</strong>';
		foreach ( $files as $file ) {
			echo '<div style="margin:12px 0">';
			$available = self::available( $file );
			$type = self::preview_type( $file );
			if ( $available && 'image' === $type ) { echo '<img loading="lazy" src="' . esc_url( self::url( $file, true ) ) . '" alt="" style="display:block;max-width:120px;max-height:100px;margin-bottom:6px">'; }
			echo '<span>' . esc_html( $file['original_name'] ) . '</span><br>';
			if ( $available ) {
				if ( $type ) { echo '<a class="button button-small" target="_blank" rel="noopener" href="' . esc_url( self::url( $file, true ) ) . '">' . ( 'pdf' === $type ? 'Ver PDF' : 'Ver' ) . '</a> '; }
				echo '<a class="button button-small" href="' . esc_url( self::url( $file ) ) . '">Descargar</a>';
			} else { echo '<em>Archivo pendiente de recuperación; referencia conservada.</em>'; }
			echo '</div>';
		}
		echo '</div>';
	}

	public static function download(): void {
		if ( ! self::can_manage() ) { wp_die( 'No tienes permiso para acceder a estos archivos.', '', array( 'response' => 403 ) ); }
		$order_id = absint( $_GET['order_id'] ?? 0 );
		$item_id = absint( $_GET['item_id'] ?? 0 );
		$order = wc_get_order( $order_id );
		$item = $order ? $order->get_item( $item_id ) : false;
		if ( ! $item || (int) $item->get_order_id() !== $order_id ) { wp_die( 'Archivo no encontrado.', '', array( 'response' => 404 ) ); }
		$file_id = is_string( $_GET['file_id'] ?? null ) ? $_GET['file_id'] : '';
		foreach ( self::recover( $item ) as $file ) {
			if ( ! hash_equals( hash( 'sha256', $file['relative_path'] ), $file_id ) ) { continue; }
			$path = self::available( $file );
			if ( ! $path ) { break; }
			$file['mime_type'] = self::mime( $path );
			$inline = '1' === ( $_GET['view'] ?? '' ) && self::preview_type( $file );
			nocache_headers();
			header( 'X-Content-Type-Options: nosniff' );
			header( "Content-Security-Policy: sandbox; default-src 'none';" );
			header( 'X-Frame-Options: SAMEORIGIN' );
			header( 'Content-Type: ' . ( $inline ? $file['mime_type'] : 'application/octet-stream' ) );
			header( 'Content-Disposition: ' . ( $inline ? 'inline' : 'attachment' ) . '; filename="' . rawurlencode( $file['original_name'] ) . '"; filename*=UTF-8\'\'' . rawurlencode( $file['original_name'] ) );
			header( 'Content-Length: ' . filesize( $path ) );
			readfile( $path ); exit;
		}
		wp_die( 'Archivo no encontrado.', '', array( 'response' => 404 ) );
	}

	public static function email_files( $order, $sent_to_admin, $plain_text, $email ): void {
		if ( ! $sent_to_admin || ! $email || 'new_order' !== $email->id || ! $order instanceof WC_Order ) { return; }
		foreach ( $order->get_items() as $item ) {
			$files = self::recover( $item );
			if ( ! $files ) { continue; }
			$lines = array( 'Producto: ' . $item->get_name(), 'Cantidad: ' . $item->get_quantity() );
			foreach ( array( 'Cantidad real', 'Formato', 'Color base', 'Detalles' ) as $key ) {
				$value = $item->get_meta( $key, true );
				if ( is_scalar( $value ) && '' !== (string) $value ) { $lines[] = $key . ': ' . $value; }
			}
			if ( $plain_text ) { echo "\n" . implode( "\n", $lines ) . "\nArchivos del cliente (requiere acceso de administración):\n"; }
			else { echo '<div><p>' . implode( '<br>', array_map( 'esc_html', $lines ) ) . '</p><strong>Archivos del cliente</strong><p><small>Requiere acceso de administración.</small></p>'; }
			foreach ( $files as $file ) {
				if ( ! self::available( $file ) ) { continue; }
				if ( $plain_text ) { echo $file['original_name'] . ' — Descargar: ' . self::url( $file ) . "\n"; }
				else { echo '<p>' . esc_html( $file['original_name'] ) . ' — <a href="' . esc_url( self::url( $file ) ) . '">Descargar</a></p>'; }
			}
			if ( ! $plain_text ) { echo '</div>'; }
		}
	}
}
