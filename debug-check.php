<?php
/**
 * Debug file to check block registration
 * Add this to wp-config.php temporarily: include_once ABSPATH . 'wp-content/plugins/jobbnorge-block/debug-check.php';
 */

add_action( 'wp_loaded', function () {
	$registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();

	if ( isset( $registered_blocks[ 'dss/jobbnorge' ] ) ) {
		error_log( 'SUCCESS: Jobbnorge block is registered!' );
		error_log( 'Block attributes: ' . print_r( $registered_blocks[ 'dss/jobbnorge' ]->attributes, true ) );
	} else {
		error_log( 'ERROR: Jobbnorge block is NOT registered!' );
		error_log( 'Available blocks: ' . implode( ', ', array_keys( $registered_blocks ) ) );
	}
} );

add_action( 'enqueue_block_editor_assets', function () {
	error_log( 'Block editor assets being enqueued' );
} );
