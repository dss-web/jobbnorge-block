<?php
/**
 * Plugin Name:       Jobbnorge Block
 * Plugin URI:        https://github.com/dss-web/dss-jobbnorge-block
 * Description:       Viser jobber fra jobbnorge.no
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.1
 * Author:            PerS
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dss-jobbnorge-block
 *
 * @package           dss-jobbnorge-block
 */

namespace DSS\Jobbnorge;

add_action( 'init', __NAMESPACE__ . '\dss_jobbnorge_init' );
add_action( 'wp_feed_options', __NAMESPACE__ . '\action_reference_wp_feed_options', 9, 2 );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function dss_jobbnorge_init() {
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\action_enqueue_scripts' );
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\action_enqueue_scripts' );
	load_plugin_textdomain( 'dss-jobbnorge-block', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	register_block_type(
		__DIR__ . '/build',
		[
			'render_callback' => __NAMESPACE__ . '\render_block_dss_jobbnorge',
		]
	);
}

/**
 * Enqueue block editor only JavaScript and CSS
 *
 * @param string $hook_suffix The current admin page.
 * @return void
 */
function action_enqueue_scripts( string $hook_suffix ) : void {

	$deps_file = plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

	$jsdeps  = [];
	$version = wp_rand();
	if ( file_exists( $deps_file ) ) {
		$file    = require $deps_file;
		$jsdeps  = array_merge( $jsdeps, $file['dependencies'] );
		$version = $file['version'];
	}
	wp_register_style( 'dss-jobbnorge', plugin_dir_url( __FILE__ ) . 'build/style-init.css', [], $version );
	wp_enqueue_style( 'dss-jobbnorge' );
	wp_set_script_translations(
		'dss-jobbnorge-editor-script', // Handle = block.json name (replxce / with -) + "-editor-script".
		'dss-jobbnorge-block',
		plugin_dir_path( __FILE__ ) . 'languages/'
	);
}


/**
 * Fires just before processing the SimplePie feed object.
 *
 * @param \SimplePie      $feed SimplePie feed object (passed by reference).
 * @param string|string[] $url  URL of feed or array of URLs of feeds to retrieve.
 */
function action_reference_wp_feed_options( \SimplePie &$feed, $url = null ) : void {
	if ( ! $url ) {
		$url = $feed->feed_url;
	}

	if ( false !== strstr( $url, 'jobbnorge' ) ) {

		require_once 'class-simplepie-sort-on-deafline.php';

		$feed = new \SimplePieSortOnDeadline();

		$feed->set_sanitize_class( '\WP_SimplePie_Sanitize_KSES' );
		// We must manually overwrite $feed->sanitize because SimplePie's
		// constructor sets it before we have a chance to set the sanitization class.
		$feed->sanitize = new \WP_SimplePie_Sanitize_KSES();

		if ( method_exists( '\SimplePie_Cache', 'register' ) ) {
			\SimplePie_Cache::register( 'wp_transient', 'WP_Feed_Cache_Transient' );
			$feed->set_cache_location( 'wp_transient' );
		} else {
			// Back-compat for SimplePie 1.2.x.
			require_once ABSPATH . WPINC . '/class-wp-feed-cache.php';
			$feed->set_cache_class( 'WP_Feed_Cache' );
		}

		$feed->set_cache_duration( apply_filters( 'wp_feed_cache_transient_lifetime', 12 * HOUR_IN_SECONDS, $url ) );
		$feed->set_file_class( '\WP_SimplePie_File' );
		$feed->set_feed_url( $url );
	}
}



/**
 * Server-side rendering of the `jobbnorge` block.
 *
 * @package WordPress
 */

/**
 * Renders the `jobbnorge` block on server.
 *
 * @param array $attributes The block attributes.
 *
 * @return string Returns the block content with received rss items.
 */
function render_block_dss_jobbnorge( $attributes ) {
	if ( in_array( untrailingslashit( $attributes['feedURL'] ), [ site_url(), home_url() ], true ) ) {
		return '<div class="components-placeholder"><div class="notice notice-error">' . __( 'Adding an Jobbnorge feed to this site’s homepage is not supported, as it could lead to a loop that slows down your site. Try using another block, like the <strong>Latest Posts</strong> block, to list posts from the site.' ) . '</div></div>';
	}

	require_once ABSPATH . WPINC . '/feed.php';
	require_once 'class-jobbnorge-item.php';

	$feed = fetch_feed( $attributes['feedURL'] );

	if ( is_wp_error( $feed ) ) {
		return '<div class="components-placeholder"><div class="notice notice-error"><strong>' . __( 'Jobbnorge Error:' ) . '</strong> ' . esc_html( $feed->get_error_message() ) . '</div></div>';
	}

	$feed->set_item_class( '\Jobbnorge_Item' );
	$feed->init();
	$feed->handle_content_type();
	if ( ! $feed->get_item_quantity() ) {
		return '<div class="components-placeholder"><div class="notice notice-error">' . __( 'An error has occurred, which probably means the feed is down. Try again later.' ) . '</div></div>';
	}

	$items      = $feed->get_items( 0, $attributes['itemsToShow'] );
	$list_items = '';
	foreach ( $items as $item ) {
		$title = esc_html( trim( wp_strip_all_tags( $item->get_title() ) ) );
		if ( empty( $title ) ) {
			$title = __( '(no title)' );
		}
		$link = $item->get_link();
		$link = esc_url( $link );
		if ( $link ) {
			$title = "<a href='{$link}'>{$title}</a>";
		}
		$title = "<div class='wp-block-dss-jobbnorge__item-title'>{$title}</div>";

		$date = '';
		if ( $attributes['displayDate'] ) {

			$formatter = \IntlDateFormatter::create( 'nb-NO', \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT, date_default_timezone_get() );
			$formatter->setPattern( 'd. MMM yyyy' ); // date format https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table.
			$date = $formatter->parse( $item->get_jn_deadline() );

			if ( $date ) {
				$date = sprintf(
					'<time datetime="%1$s" class="wp-block-dss-jobbnorge__item-publish-date">%2$s %3$s</time> ',
					esc_attr( date_i18n( get_option( 'c' ), $date ) ),
					__( 'Deadline:', 'dss-jobbnorge-block' ),
					esc_attr( date_i18n( get_option( 'date_format' ), $date ) )
				);
			}
		}

		$excerpt = '';
		if ( $attributes['displayExcerpt'] ) {
			$excerpt = html_entity_decode( $item->get_description(), ENT_QUOTES, get_option( 'blog_charset' ) );
			$excerpt = esc_attr( wp_trim_words( $excerpt, $attributes['excerptLength'], '' ) );

			$read_more = sprintf( ' ... <a href="%s">%s</a>', esc_url( $item->get_permalink() ), __( 'Read more', 'dss-jobbnorge-block' ) );

			$excerpt = '<div class="wp-block-dss-jobbnorge__item-excerpt">' . esc_html( $excerpt ) . $read_more . '</div>';
		}

		$scope = '';
		if ( $attributes['displayScope'] ) {
			$scope = $item->get_jn_jobscope();
			$scope = sprintf(
				'<div class="wp-block-dss-jobbnorge__item-scope">%s: %s</div>',
				__( 'Scope', 'dss-jobbnorge-block' ),
				esc_html( $scope )
			);
		}
		$duration = '';
		if ( $attributes['displayDuration'] ) {
			$duration = $item->get_jn_jobduration();
			$duration = sprintf(
				'<div class="wp-block-dss-jobbnorge__item-duration">%s: %s</div>',
				__( 'Duration', 'dss-jobbnorge-block' ),
				esc_html( $duration )
			);
		}

		$list_items .= "<li class='wp-block-dss-jobbnorge__item'>{$date}{$title}{$excerpt}{$scope}{$duration}</li>";
	}

	$classnames = [];
	if ( isset( $attributes['blockLayout'] ) && 'grid' === $attributes['blockLayout'] ) {
		$classnames[] = 'is-grid';
	}
	if ( isset( $attributes['columns'] ) && 'grid' === $attributes['blockLayout'] ) {
		$classnames[] = 'columns-' . $attributes['columns'];
	}
	if ( $attributes['displayDate'] ) {
		$classnames[] = 'has-dates';
	}
	if ( $attributes['displayDeadline'] ) {
		$classnames[] = 'has-authors';
	}
	if ( $attributes['displayExcerpt'] ) {
		$classnames[] = 'has-excerpts';
	}

	$wrapper_attributes = get_block_wrapper_attributes( [ 'class' => implode( ' ', $classnames ) ] );

	return sprintf( '<ul %s>%s</ul>', $wrapper_attributes, $list_items );
}
