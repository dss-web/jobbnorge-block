<?php
/**
 * Plugin Name:       DSS Jobbnorge Block
 * Plugin URI:        https://github.com/dss-web/dss-jobbnorge-block
 * Description:       Lorem ipsum
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            PerS
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dss-jobbnorge-block
 *
 * @package           dss-jobbnorge-block
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function dss_jobbnorge_block_dss_jobbnorge_block_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'dss_jobbnorge_block_dss_jobbnorge_block_block_init' );
