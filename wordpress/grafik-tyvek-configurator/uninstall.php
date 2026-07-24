<?php
/**
 * Conserva pedidos y archivos al desinstalar.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'grafik_tyvek_needs_setup' );

