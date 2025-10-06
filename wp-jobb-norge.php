<?php
/**
 * Plugin Name:       Jobbnorge Block
 * Plugin URI:        https://wordpress.org/plugins/jobbnorge-block/
 * Description:       Retrieve and display job listings from Jobbnorge.no
 * Requires at least: 6.5
 * Requires PHP:      8.2
 * Version:           2.2.4
 * Author:            PerS
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-jobbnorge-block
 * @package           wp-jobbnorge-block
 */

namespace DSS\Jobbnorge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Safety.
}

if ( ! defined( 'WP_JOBBNORGE_VERSION' ) ) {
	define( 'WP_JOBBNORGE_VERSION', '2.2.4' );
}

if ( ! \class_exists( 'Jobbnorge_CacheHandler' ) ) {
	require_once __DIR__ . '/class-jobbnorge-cachehandler.php';
}

add_action( 'init', __NAMESPACE__ . '\\dss_jobbnorge_init' );

/**
 * Init: register block + i18n + enqueue hooks.
 */
function dss_jobbnorge_init(): void {
	load_plugin_textdomain( 'wp-jobbnorge-block', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	register_block_type( __DIR__ . '/build', [ 'render_callback' => __NAMESPACE__ . '\\render_block_dss_jobbnorge' ] );

	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\dss_jobbnorge_enqueue_scripts' );
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\dss_jobbnorge_enqueue_frontend_styles' );
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_pagination_script' );
}

/**
 * Editor assets.
 */
function dss_jobbnorge_enqueue_scripts( string $hook_suffix ): void {
	if ( ! in_array( $hook_suffix, [ 'post.php', 'post-new.php', 'edit.php' ], true ) ) {
		return;
	}
	$deps_file = plugin_dir_path( __FILE__ ) . 'build/init.asset.php';
	$jsdeps    = [];
	$version   = WP_JOBBNORGE_VERSION;
	if ( file_exists( $deps_file ) ) {
		$file    = require $deps_file; // phpcs:ignore
		$jsdeps  = array_merge( $jsdeps, $file[ 'dependencies' ] );
		$version = $file[ 'version' ];
	}
	if ( is_admin() ) {
		wp_register_style( 'dss-jobbnorge-admin', plugin_dir_url( __FILE__ ) . 'build/init.css', [], $version );
		wp_enqueue_style( 'dss-jobbnorge-admin' );
	}
	wp_set_script_translations( 'dss-jobbnorge-editor-script', 'wp-jobbnorge-block', plugin_dir_path( __FILE__ ) . 'languages/' );
	$employers = apply_filters( 'jobbnorge_employers', false );
	if ( false !== $employers ) {
		if ( ! is_array( $employers ) ) {
			$employers = [];
		}
		wp_localize_script( 'dss-jobbnorge-editor-script', 'wpJobbnorgeBlock', [ 'employers' => $employers ] );
	}
}

/**
 * Frontend styles.
 */
function dss_jobbnorge_enqueue_frontend_styles(): void {
	$deps_file = plugin_dir_path( __FILE__ ) . 'build/init.asset.php';
	$version   = WP_JOBBNORGE_VERSION;
	if ( file_exists( $deps_file ) ) {
		$file    = require $deps_file; // phpcs:ignore
		$version = $file[ 'version' ];
	}
	wp_register_style( 'dss-jobbnorge', plugin_dir_url( __FILE__ ) . 'build/style-init.css', [], $version );
	wp_enqueue_style( 'dss-jobbnorge' );
}

/**
 * Render block frontend.
 */
function render_block_dss_jobbnorge( $attributes ): string {
	// Ensure attributes is always an array to avoid notices in core block supports handling.
	if ( ! is_array( $attributes ) ) {
		$attributes = [];
	}

	// Will hold a stale cache notice when applicable.
	$stale_notice = '';
	$attributes   = wp_parse_args( $attributes, [
		'employerID'        => '',
		'displayEmployer'   => false,
		'displayDate'       => true, // currently unused but kept for backward compat.
		'displayDeadline'   => false,
		'displayScope'      => false,
		'displayExcerpt'    => true,
		'excerptLength'     => 55,
		'blockLayout'       => 'list',
		'orderBy'           => 'Deadline',
		'columns'           => 3,
		'itemsToShow'       => 5,
		'enablePagination'  => true,
		'jobsPerPage'       => 10,
		'disableAutoScroll' => false,
	] );

	// Sanitize employer IDs.
	$arr_ids_raw = array_filter( array_map( 'trim', explode( ',', (string) $attributes[ 'employerID' ] ) ) );
	$arr_ids     = [];
	foreach ( $arr_ids_raw as $maybe ) {
		if ( ctype_digit( $maybe ) ) {
			$arr_ids[] = (string) absint( $maybe );
		}
	}
	if ( empty( $arr_ids ) && ! empty( $attributes[ 'employerID' ] ) ) {
		return '<div class="components-placeholder"><div class="notice notice-error">' . esc_html__( 'Invalid ID', 'wp-jobbnorge-block' ) . '</div></div>';
	}

	$current_page   = isset( $_GET[ 'jobbnorge_page' ] ) ? max( 1, absint( $_GET[ 'jobbnorge_page' ] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only.
	$items_per_page = $attributes[ 'enablePagination' ] ? (int) $attributes[ 'jobsPerPage' ] : (int) $attributes[ 'itemsToShow' ];

	// Build API URL.
	$jobbnorge_api_url = 'https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=' . rawurlencode( $attributes[ 'orderBy' ] );
	foreach ( $arr_ids as $id ) {
		$jobbnorge_api_url .= '&employer=' . absint( $id );
	}

	$cache_path    = apply_filters( 'jobbnorge_cache_path', WP_CONTENT_DIR . '/cache/jobbnorge' );
	$cache         = new \Jobbnorge_CacheHandler( $cache_path );
	$cache_key     = md5( $jobbnorge_api_url );
	$expiration    = (int) apply_filters( 'jobbnorge_cache_time', 30 * MINUTE_IN_SECONDS );
	$response_data = $cache->get( $cache_key, $expiration );
	if ( false === $response_data ) {
		// Perform remote request when no *fresh* cache. We'll attempt a stale cache fallback below if available.
		$response = wp_remote_get( $jobbnorge_api_url, [
			'timeout' => 10,
			'headers' => [
				'Accept'     => 'application/json',
				'User-Agent' => 'JobbnorgeBlock/' . WP_JOBBNORGE_VERSION . ' ' . home_url( '/' ),
			],
		] );

		$http_status = ! is_wp_error( $response ) ? wp_remote_retrieve_response_code( $response ) : 0;
		$body        = ! is_wp_error( $response ) ? wp_remote_retrieve_body( $response ) : '';
		$tmp         = $body ? json_decode( $body, true ) : null;
		$json_ok     = is_array( $tmp ) && json_last_error() === JSON_ERROR_NONE;

		if ( ! is_wp_error( $response ) && $http_status >= 200 && $http_status < 300 && $json_ok ) {
			// Happy path: cache and proceed.
			$response_data = $tmp;
			$cache->set( $cache_key, $response_data );
		} else {
			// Attempt stale cache fallback: read cache file ignoring expiration.
			$stale_data = null;
			$cache_file = apply_filters( 'jobbnorge_cache_path', WP_CONTENT_DIR . '/cache/jobbnorge' ) . '/' . $cache_key . '.php';
			if ( file_exists( $cache_file ) ) {
				// Suppress errors; include returns data array.
				$maybe_stale = @include $cache_file; // phpcs:ignore
				if ( is_array( $maybe_stale ) ) {
					$stale_data = $maybe_stale;
				}
			}

			$error_type = 'unknown';
			if ( is_wp_error( $response ) ) {
				$error_type = 'network';
			} elseif ( $http_status >= 500 ) {
				$error_type = 'server';
			} elseif ( $http_status === 404 ) {
				$error_type = 'not_found';
			} elseif ( $http_status >= 400 ) {
				$error_type = 'client';
			} elseif ( ! $json_ok ) {
				$error_type = 'invalid_json';
			}

			/**
			 * Fires when the Jobbnorge API request fails.
			 *
			 * @param string $error_type One of network|server|client|not_found|invalid_json|unknown.
			 * @param int    $http_status HTTP status code (0 if network error).
			 * @param string $api_url     Requested API URL.
			 */
			do_action( 'jobbnorge_api_request_failed', $error_type, $http_status, $jobbnorge_api_url );

			if ( $stale_data ) {
				// Provide gentle notice while still rendering stale data.
				$response_data = $stale_data;
				// Prepend a warning message that content may be outdated.
				$stale_notice = '<div class="notice notice-warning jobbnorge-stale" role="alert">' . esc_html__( 'Showing cached results due to a temporary connection issue.', 'wp-jobbnorge-block' ) . '</div>';
			} else {
				// User-facing message depending on error type.
				$human_msg = __( 'Error connecting to Jobbnorge.no', 'wp-jobbnorge-block' );
				if ( 'not_found' === $error_type ) {
					$human_msg = __( 'Job listings not found (404).', 'wp-jobbnorge-block' );
				} elseif ( 'server' === $error_type ) {
					$human_msg = __( 'Jobbnorge service temporarily unavailable.', 'wp-jobbnorge-block' );
				} elseif ( 'client' === $error_type ) {
					$human_msg = __( 'Request error retrieving jobs.', 'wp-jobbnorge-block' );
				} elseif ( 'invalid_json' === $error_type ) {
					$human_msg = __( 'Received invalid data from Jobbnorge.', 'wp-jobbnorge-block' );
				}
				return '<div class="components-placeholder"><div class="notice notice-error">' . esc_html( $human_msg ) . '</div></div>';
			}
		}
	}

	$all_items = isset( $response_data[ 'jobs' ] ) && is_array( $response_data[ 'jobs' ] ) ? $response_data[ 'jobs' ] : ( is_array( $response_data ) ? $response_data : [] );
	if ( ! is_array( $all_items ) ) {
		$all_items = [];
	}
	$total_jobs = count( $all_items );
	if ( 0 === $total_jobs ) {
		return '<div class="components-placeholder"><div class="notice notice-error">' . esc_html__( 'No jobs found', 'wp-jobbnorge-block' ) . '</div></div>';
	}

	if ( $attributes[ 'enablePagination' ] ) {
		$start_index = ( $current_page - 1 ) * $items_per_page;
		$items       = array_slice( $all_items, $start_index, $items_per_page );
	} else {
		$items      = array_slice( $all_items, 0, $items_per_page );
		$total_jobs = count( $items );
	}
	if ( empty( $items ) ) {
		return '<div class="components-placeholder"><div class="notice notice-error">' . esc_html__( 'No jobs found', 'wp-jobbnorge-block' ) . '</div></div>';
	}

	$wrapper_classes = [ 'wp-block-dss-jobbnorge__wrapper' ];
	if ( $attributes[ 'enablePagination' ] ) {
		$wrapper_classes[] = 'has-pagination';
	}

	// Stable instance id for multi-block pages; allow reuse if passed via AJAX.
	$instance_id = '';
	if ( isset( $attributes[ 'instanceId' ] ) && is_string( $attributes[ 'instanceId' ] ) ) {
		$instance_id = sanitize_html_class( $attributes[ 'instanceId' ] );
	}
	if ( '' === $instance_id ) {
		static $jobbnorge_instance_counter = 0;
		$jobbnorge_instance_counter++;
		$instance_id                = 'jobbnorge-' . $jobbnorge_instance_counter . '-' . wp_rand( 1000, 9999 );
		$attributes[ 'instanceId' ] = $instance_id; // Persist in JSON for JS.
	}

	$threshold  = (float) apply_filters( 'jobbnorge_autoscroll_threshold', 0.25 ); // Portion of viewport height.
	$extra_data = [
		'class'                     => implode( ' ', $wrapper_classes ),
		'aria-live'                 => 'polite',
		'data-block-instance'       => esc_attr( $instance_id ),
		'data-autoscroll-threshold' => esc_attr( $threshold ),
	];
	if ( ! empty( $attributes[ 'disableAutoScroll' ] ) ) {
		$extra_data[ 'data-no-autoscroll' ] = 'true';
	}
	$wrapper_attributes = get_block_wrapper_attributes( $extra_data );

	$ul_classes = [ 'wp-block-dss-jobbnorge' ];
	if ( 'grid' === $attributes[ 'blockLayout' ] ) {
		$ul_classes[] = 'is-grid';
		$ul_classes[] = 'columns-' . (int) $attributes[ 'columns' ];
	}

	$list_items = '';
	foreach ( $items as $item ) {
		// Deadline filtering: hide past deadlines if ordering by deadline.
		if ( isset( $item[ 'deadlineDate' ] ) && 'Deadline' === $attributes[ 'orderBy' ] ) {
			$deadline_ts = false;
			try {
				$deadline_ts = parse_date( $item[ 'deadlineDate' ] );
			} catch (\Throwable $t) {
				$deadline_ts = false;
			}
			if ( $deadline_ts && $deadline_ts < time() ) {
				continue; // Skip expired.
			}
		}
		$title     = isset( $item[ 'title' ] ) ? $item[ 'title' ] : '';
		$link      = isset( $item[ 'link' ] ) ? $item[ 'link' ] : '#';
		$deadline  = $attributes[ 'displayDeadline' ] && ! empty( $item[ 'deadlineDate' ] ) ? format_deadline( $item[ 'deadlineDate' ] ) : '';
		$excerpt   = format_excerpt( $attributes, $item );
		$employer  = format_attribute( $attributes, $item, 'employer', 'displayEmployer', 'wp-block-dss-jobbnorge__item-employer', __( 'Employer', 'wp-jobbnorge-block' ) );
		$scope     = format_attribute( $attributes, $item, 'jobScope', 'displayScope', 'wp-block-dss-jobbnorge__item-scope', __( 'Scope', 'wp-jobbnorge-block' ) );
		$meta_html = '';
		if ( $employer || $deadline || $scope ) {
			$meta_html = sprintf( '<div class="wp-block-dss-jobbnorge__item-meta">%s%s%s</div>', $employer, $deadline, $scope );
		}
		$list_items .= sprintf(
			'<li class="wp-block-dss-jobbnorge__item"><div class="wp-block-dss-jobbnorge__item-title"><a href="%s">%s</a></div>%s%s</li>',
			esc_url( $link ),
			esc_html( $title ),
			$meta_html,
			$excerpt
		);
	}

	if ( '' === $list_items ) {
		return '<div class="components-placeholder"><div class="notice notice-error">' . esc_html__( 'No jobs found', 'wp-jobbnorge-block' ) . '</div></div>';
	}

	$pagination_html = '';
	if ( $attributes[ 'enablePagination' ] && $total_jobs > $items_per_page ) {
		$pagination_html = generate_pagination_controls( $current_page, $total_jobs, $items_per_page, $attributes );
	}

	// Screen reader status region text (always present for consistent announcements).
	$total_pages   = $attributes[ 'enablePagination' ] ? (int) ceil( $total_jobs / $items_per_page ) : 1;
	$start_item    = ( ( $current_page - 1 ) * $items_per_page ) + 1;
	$end_item      = min( $current_page * $items_per_page, $total_jobs );
	$status_text   = sprintf(
		/* translators: 1: first item index, 2: last item index, 3: total, 4: current page, 5: total pages */
		__( 'Showing %1$d–%2$d of %3$d jobs. Page %4$d of %5$d.', 'wp-jobbnorge-block' ),
		$start_item,
		$end_item,
		$total_jobs,
		$current_page,
		$total_pages
	);
	$status_region = '<div class="jobbnorge-pagination-status screen-reader-text" aria-live="polite" role="status" data-status-for="' . esc_attr( $instance_id ) . '">' . esc_html( $status_text ) . '</div>';

	// Prepend stale notice if present. Data attributes now live on the UL for JS pagination script.
	$data_attr_json = esc_attr( wp_json_encode( $attributes ) );
	$inner_html     = $stale_notice . $status_region . sprintf( '<ul class="%1$s" data-attributes="%3$s">%2$s</ul>', esc_attr( implode( ' ', $ul_classes ) ), $list_items, $data_attr_json );

	return sprintf( '<div %1$s>%2$s%3$s</div>', $wrapper_attributes, $inner_html, $pagination_html );
}

/**
 * Formats the excerpt of an item.
 *
 * @param array $attributes     The attributes array to check the key in.
 * @param array $item           The item array to get the attribute from.
 * @return string The formatted excerpt.
 */
function format_excerpt( $attributes, $item ): string {
	if ( empty( $attributes[ 'displayExcerpt' ] ) || empty( $item[ 'summary' ] ) ) {
		return '';
	}
	$excerpt_raw = html_entity_decode( wp_strip_all_tags( (string) $item[ 'summary' ] ), ENT_QUOTES, get_option( 'blog_charset' ) );
	$excerpt     = wp_trim_words( $excerpt_raw, (int) $attributes[ 'excerptLength' ], '' );
	$read_more   = sprintf( ' <a href="%s">%s</a>', esc_url( $item[ 'link' ] ?? '#' ), esc_html__( 'Read more', 'wp-jobbnorge-block' ) );
	return sprintf( '<div class="wp-block-dss-jobbnorge__item-excerpt">%s%s</div>', esc_html( $excerpt ), $read_more );
}

/**
 * Formats an attribute of an item.
 *
 * @param array  $attributes    The attributes array to check the key in.
 * @param array  $item          The item array to get the attribute from.
 * @param string $attribute_key The key to get from the item array.
 * @param string $display_key   The key to check in the attributes array.
 * @param string $css_class     The class name to add to the div.
 * @param string $label         The label to display before the attribute.
 * @return string The formatted attribute.
 */
function format_attribute( $attributes, $item, $attribute_key, $display_key, $css_class, $label ) {
	// Initialize an empty string for the result.
	$result = '';

	// Check if the display_key attribute is true and the item has the attribute_key.
	if ( $attributes[ $display_key ] && isset( $item[ $attribute_key ] ) ) {
		// If so, format the result string with the CSS class, label, and attribute, escaping the attribute for safe use in HTML output.
		$result = sprintf(
			'<div class="%s">%s: %s</div>',
			$css_class,
			$label,
			esc_html( $item[ $attribute_key ] )
		);
	}

	// Return the result.
	return $result;
}

/**
 * Formats the deadline date.
 *
 * @param string $deadline_date The deadline date.
 *
 * @return string The formatted deadline date.
 */
function format_deadline( $deadline_date ): string {
	if ( ! $deadline_date ) {
		return '';
	}
	try {
		$date     = parse_date( $deadline_date );
		$str_date = date_i18n( get_option( 'date_format' ), $date );
	} catch (\Throwable $t) {
		$str_date = $deadline_date;
		$date     = false;
	}
	if ( $str_date ) {
		return sprintf(
			'<time datetime="%1$s" class="wp-block-dss-jobbnorge__item-deadline">%2$s %3$s</time>',
			$date ? esc_attr( wp_date( 'c', $date ) ) : '',
			esc_html__( 'Deadline:', 'wp-jobbnorge-block' ),
			esc_html( $str_date )
		);
	}
	return '';
}

/**
 * Parses the deadline date.
 *
 * @param string $deadline_date The deadline date.
 *
 * @return mixed The parsed deadline date.
 */
function parse_date( $deadline_date ) {
	// Check if the IntlDateFormatter class exists.
	if ( class_exists( '\IntlDateFormatter' ) ) {
		// If it does, use it to parse the deadline date.
		return parse_date_intl( $deadline_date );
	}

	// If the IntlDateFormatter class does not exist, use a fallback method to parse the deadline date.
	return parse_date_fallback( $deadline_date );
}

/**
 * Parses the deadline date using IntlDateFormatter.
 *
 * @param string $deadline_date The deadline date.
 *
 * @return mixed The parsed deadline date.
 */
function parse_date_intl( $deadline_date ) {
	// Create an instance of the IntlDateFormatter class for the Norwegian locale, using the short date and time styles.
	$formatter = \IntlDateFormatter::create( 'nb-NO', \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT, date_default_timezone_get() );

	// Set the pattern for the formatter to 'dd.MM.yyyy'.
	$formatter->setPattern( 'dd.MM.yyyy' );

	// Use the formatter to parse the deadline date and return the result.
	return $formatter->parse( $deadline_date );
}

/**
 * Parses the deadline date as a fallback.
 *
 * @param string $deadline_date The deadline date.
 *
 * @return mixed The parsed deadline date.
 */
function parse_date_fallback( $deadline_date ) {
	// Define an array of month names in Norwegian.
	$str_months = [
		'januar',
		'februar',
		'mars',
		'april',
		'mai',
		'juni',
		'juli',
		'august',
		'september',
		'oktober',
		'november',
		'desember',
	];

	// Define an array of month numbers.
	$num_months = [
		'01',
		'02',
		'03',
		'04',
		'05',
		'06',
		'07',
		'08',
		'09',
		'10',
		'11',
		'12',
	];

	// Replace single-digit day numbers in the deadline date with two-digit numbers.
	$dato = preg_replace( '/(\d{1})\./', '$1', $deadline_date );
	$dato = preg_replace( '/(\d{1})\./', '0$1.', $dato );

	// Replace the Norwegian month names in the date with month numbers.
	$dato = str_ireplace( $str_months, $num_months, $dato, $count );

	// Split the date into an array.
	$dato_arr = explode( ' ', $dato );

	// Return a Unix timestamp for the date.
	return mktime( 0, 0, 0, $dato_arr[ 2 ], $dato_arr[ 1 ], $dato_arr[ 3 ] );
}

/**
 * Adds a class name to the classnames array if the attribute key is set and truthy.
 *
 * @param array  $classnames The array to add the class name to.
 * @param array  $attributes  The attributes array to check the key in.
 * @param string $key         The key to check in the attributes array.
 * @param string $classname   The class name to add to the classnames array.
 */
function add_classname( &$classnames, $attributes, $key, $classname ) {
	// Check if the attribute key exists and is truthy in the attributes array.
	if ( isset( $attributes[ $key ] ) && $attributes[ $key ] ) {
		// If so, add the classname to the classnames array.
		$classnames[] = $classname;
	}
}

/**
 * Generates pagination controls for the job listings.
 *
 * @param int   $current_page The current page number.
 * @param int   $total_jobs   The total number of jobs.
 * @param int   $jobs_per_page The number of jobs per page.
 * @param array $attributes   The block attributes.
 * @return string The pagination HTML.
 */
function generate_pagination_controls( $current_page, $total_jobs, $jobs_per_page, $attributes ) {
	$total_pages = ceil( $total_jobs / $jobs_per_page );

	if ( $total_pages <= 1 ) {
		return '';
	}

	$prev_page = max( 1, $current_page - 1 );
	$next_page = min( $total_pages, $current_page + 1 );

	// Calculate result range
	$start_item = ( ( $current_page - 1 ) * $jobs_per_page ) + 1;
	$end_item   = min( $current_page * $jobs_per_page, $total_jobs );

	// Generate pagination HTML
	$pagination_html = '<nav class="wp-block-dss-jobbnorge__pagination" role="navigation" aria-label="' . esc_attr__( 'Job listings pagination', 'wp-jobbnorge-block' ) . '">';

	// Results info
	$pagination_html .= sprintf(
		'<div class="wp-block-dss-jobbnorge__pagination-info">%s</div>',
		sprintf(
			esc_html__( 'Showing %d-%d of %d jobs', 'wp-jobbnorge-block' ),
			$start_item,
			$end_item,
			$total_jobs
		)
	);

	// Pagination controls
	$pagination_html .= '<div class="wp-block-dss-jobbnorge__pagination-controls">';

	// Previous button
	if ( $current_page > 1 ) {
		$pagination_html .= sprintf(
			'<button type="button" class="wp-block-dss-jobbnorge__pagination-prev" data-page="%d">%s</button>',
			$prev_page,
			esc_html__( 'Previous', 'wp-jobbnorge-block' )
		);
	} else {
		$pagination_html .= sprintf(
			'<button type="button" class="wp-block-dss-jobbnorge__pagination-prev" disabled>%s</button>',
			esc_html__( 'Previous', 'wp-jobbnorge-block' )
		);
	}

	// Page info
	$pagination_html .= sprintf(
		'<span class="wp-block-dss-jobbnorge__pagination-info">%s</span>',
		sprintf(
			esc_html__( 'Page %d of %d', 'wp-jobbnorge-block' ),
			$current_page,
			$total_pages
		)
	);

	// Next button
	if ( $current_page < $total_pages ) {
		$pagination_html .= sprintf(
			'<button type="button" class="wp-block-dss-jobbnorge__pagination-next" data-page="%d">%s</button>',
			$next_page,
			esc_html__( 'Next', 'wp-jobbnorge-block' )
		);
	} else {
		$pagination_html .= sprintf(
			'<button type="button" class="wp-block-dss-jobbnorge__pagination-next" disabled>%s</button>',
			esc_html__( 'Next', 'wp-jobbnorge-block' )
		);
	}

	$pagination_html .= '</div>';
	$pagination_html .= '</nav>';

	return $pagination_html;
}

/**
 * Register AJAX endpoints for pagination.
 */
add_action( 'wp_ajax_jobbnorge_get_jobs', __NAMESPACE__ . '\\handle_ajax_get_jobs' );
add_action( 'wp_ajax_nopriv_jobbnorge_get_jobs', __NAMESPACE__ . '\\handle_ajax_get_jobs' );

/**
 * Handle AJAX request for paginated job listings.
 */
function handle_ajax_get_jobs(): void {
	if ( empty( $_POST[ 'nonce' ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'nonce' ] ) ), 'jobbnorge_pagination_nonce' ) ) {
		wp_send_json_error( [ 'message' => __( 'Security check failed', 'wp-jobbnorge-block' ) ], 403 );
	}
	$page       = isset( $_POST[ 'page' ] ) ? max( 1, absint( wp_unslash( $_POST[ 'page' ] ) ) ) : 1;
	$raw_attr   = isset( $_POST[ 'attributes' ] ) ? wp_unslash( $_POST[ 'attributes' ] ) : '';
	$attributes = json_decode( $raw_attr, true );
	if ( json_last_error() !== JSON_ERROR_NONE || empty( $attributes ) || ! is_array( $attributes ) ) {
		wp_send_json_error( [ 'message' => __( 'Invalid attributes', 'wp-jobbnorge-block' ) ], 400 );
	}
	$_GET[ 'jobbnorge_page' ] = $page; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$html                     = render_block_dss_jobbnorge( $attributes );
	wp_send_json_success( [ 'html' => $html ] );
}

/**
 * Enqueue frontend JavaScript for pagination.
 */
function enqueue_pagination_script() {
	// Check if the block is being used on the current page
	if ( ! has_block( 'dss/jobbnorge' ) ) {
		return;
	}

	// Define the path to the pagination dependencies file.
	$deps_file = plugin_dir_path( __FILE__ ) . 'build/pagination.asset.php';

	// Initialize an array for JavaScript dependencies and a random version number.
	$jsdeps  = [];
	$version = wp_rand();

	// Check if the dependencies file exists.
	if ( file_exists( $deps_file ) ) {
		// If it does, require it and merge its dependencies with the existing ones.
		$file   = require $deps_file;
		$jsdeps = array_merge( $jsdeps, $file[ 'dependencies' ] );
		// Also, set the version to the one from the file.
		$version = $file[ 'version' ];
	}

	wp_enqueue_script(
		'jobbnorge-pagination',
		plugin_dir_url( __FILE__ ) . 'build/pagination.js',
		$jsdeps,
		$version,
		true
	);

	// Localize script with AJAX URL and nonce
	wp_localize_script(
		'jobbnorge-pagination',
		'jobbnorgeAjax',
		[
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'jobbnorge_pagination_nonce' ),
		]
	);
}

// Hook into wp_enqueue_scripts to add pagination script
// Enqueue pagination script already hooked in init via dss_jobbnorge_init.
