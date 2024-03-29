<?php
/**
 * Uninstall script.
 *
 * @package wp-jobbnorge-block
 */

// If uninstall.php is not called by WordPress, abort.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	return;
}

// Include the base filesystem class from WordPress core.
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';

// Include the direct filesystem class from WordPress core.
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

// Create a new instance of the direct filesystem class.
$file_system_direct = new WP_Filesystem_Direct( false );

// Apply filter to get cache path, default is 'WP_CONTENT_DIR/cache/jobbnorge'.
$cache_path = apply_filters( 'jobbnorge_cache_path', WP_CONTENT_DIR . '/cache/jobbnorge' );

// If the cache directory exists, remove it and its contents.
if ( $file_system_direct->is_dir( $cache_path ) ) {
	$file_system_direct->rmdir( $cache_path, true );
}
