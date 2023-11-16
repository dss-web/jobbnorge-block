<?php
/**
 * Plugin Name:       Jobbnorge Block
 * Plugin URI:        https://wordpress.org/plugins/jobbnorge-block/
 * Description:       List jobs at jobbnorge.no
 * Requires at least: 5.9
 * Requires PHP:      7.0
 * Version:           2.0.0
 * Author:            PerS
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-jobbnorge-block
 *
 * @package           wp-jobbnorge-block
 */

namespace DSS\Jobbnorge;

add_action( 'init', __NAMESPACE__ . '\dss_jobbnorge_init' );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function dss_jobbnorge_init() {
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\dss_jobbnorge_enqueue_scripts' );
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\dss_jobbnorge_enqueue_scripts' );
	load_plugin_textdomain( 'wp-jobbnorge-block', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
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
function dss_jobbnorge_enqueue_scripts( string $hook_suffix ): void {


	if ( 'post.php' !== $hook_suffix && 'post-new.php' !== $hook_suffix && 'edit.php' !== $hook_suffix ) {
		return;
	}

	$deps_file = plugin_dir_path( __FILE__ ) . 'build/init.asset.php';

	$jsdeps  = [];
	$version = wp_rand();
	if ( file_exists( $deps_file ) ) {
		$file    = require $deps_file;
		$jsdeps  = array_merge( $jsdeps, $file['dependencies'] );
		$version = $file['version'];
	}
	if ( is_admin() ) {
		wp_register_style( 'dss-jobbnorge-admin', plugin_dir_url( __FILE__ ) . 'build/init.css', [], $version );
		wp_enqueue_style( 'dss-jobbnorge-admin' );
	}
	wp_register_style( 'dss-jobbnorge', plugin_dir_url( __FILE__ ) . 'build/style-init.css', [], $version );
	wp_enqueue_style( 'dss-jobbnorge' );
	wp_set_script_translations(
		'dss-jobbnorge-editor-script', // Handle = block.json "name" (replace / with -) + "-editor-script".
		'wp-jobbnorge-block',
		plugin_dir_path( __FILE__ ) . 'languages/'
	);
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

	// set default values for attributes.
	$attributes = wp_parse_args(
		$attributes,
		[
			'employerID'      => '',
			'displayEmployer' => false,
			'displayDate'     => true,
			'displayDeadline' => false,
			'displayScope'    => false,
			'displayDuration' => false,
			'displayExcerpt'  => true,
			'excerptLength'   => 55,
			'blockLayout'     => 'list',
			'orderBy'         => 'Deadline',
			'columns'         => 3,
			'itemsToShow'     => 5,
		]
	);

	$arr_ids = array_map( 'trim', explode( ',', $attributes['employerID'] ) );

	if ( ! array_filter( $arr_ids, 'is_numeric' ) ) {
		return '<div class="components-placeholder"><div class="notice notice-error">' . __( 'Invalid ID', 'wp-jobbnorge-block' ) . '</div></div>';
	}

	$jobbnorge_api_url = 'https://publicapi.jobbnorge.no/v2/Jobs?abroad=false&orderBy=' . $attributes['orderBy'];

	foreach ( $arr_ids as $id ) {
		$jobbnorge_api_url .= '&employer=' . $id;
	}

	$transient_key = md5( $jobbnorge_api_url );
	$body          = get_transient( $transient_key );
	if ( false === $body ) {
		$response = wp_remote_get( $jobbnorge_api_url );

		if ( is_wp_error( $response ) ) {
			return '<div class="components-placeholder"><div class="notice notice-error">' . __( 'Error connecting to Jobbnorge.no', 'wp-jobbnorge-block' ) . '</div></div>';

		}

		$body = wp_remote_retrieve_body( $response );
		set_transient( $transient_key, $body, 5 * MINUTE_IN_SECONDS );
	}

	$items = json_decode( $body, true );
	$items = array_slice( $items, 0, $attributes['itemsToShow'] );

	if ( ! $items ) {
		return '<div class="components-placeholder"><div class="notice notice-error">' . __( 'No jobs found', 'wp-jobbnorge-block' ) . '</div></div>';
	}

	$list_items = '';
	foreach ( $items as $item ) {
		$title = esc_html( trim( wp_strip_all_tags( $item['title'] ) ) );
		$title = empty( $title ) ? __( '(no title)' ) : $title;

		$link  = esc_url( $item['link'] );
		$title = $link ? "<a href='{$link}'>{$title}</a>" : $title;

		$title = "<div class='wp-block-dss-jobbnorge__item-title'>{$title}</div>";

		$deadline = '';
		if ( $attributes['displayDate'] && isset( $item['deadline'] ) ) {
			$deadline = format_deadline( $item['deadline'] );
		}

		$excerpt = format_excerpt( $attributes, $item );

		$employer = format_attribute( $attributes, $item, 'employer', 'displayEmployer', 'wp-block-dss-jobbnorge__item-employer', __( 'Employer', 'wp-jobbnorge-block' ) );
		$scope    = format_attribute( $attributes, $item, 'jobScope', 'displayScope', 'wp-block-dss-jobbnorge__item-scope', __( 'Scope', 'wp-jobbnorge-block' ) );
		// $duration = format_attribute( $attributes, $item, 'jobDuration', 'displayDuration', 'wp-block-dss-jobbnorge__item-duration', __( 'Duration', 'wp-jobbnorge-block' ) );

		$meta = '';
		if ( $employer || $deadline || $scope ) {
			$meta = '<div class="wp-block-dss-jobbnorge__item-meta">' . $employer . $deadline . $scope . '</div>';
		}

		$list_items .= "<li class='wp-block-dss-jobbnorge__item'>{$title}{$meta}{$excerpt}</li>";
	}

	$classnames = [];
	if ( 'grid' === $attributes['blockLayout'] ) {
		add_classname( $classnames, $attributes, 'blockLayout', 'is-grid' );
		add_classname( $classnames, $attributes, 'columns', 'columns-' . $attributes['columns'] );
	}

	add_classname( $classnames, $attributes, 'displayEmployer', 'has-employer' );
	add_classname( $classnames, $attributes, 'displayDate', 'has-dates' );
	add_classname( $classnames, $attributes, 'displayDeadline', 'has-deadline' );
	add_classname( $classnames, $attributes, 'displayScope', 'has-scope' );
	// add_classname( $classnames, $attributes, 'displayDuration', 'has-duration' );
	add_classname( $classnames, $attributes, 'displayExcerpt', 'has-excerpts' );

	$wrapper_attributes = get_block_wrapper_attributes( [ 'class' => implode( ' ', $classnames ) ] );

	return sprintf( '<ul %s>%s</ul>', $wrapper_attributes, $list_items );
}

/**
 * Formats the excerpt of an item.
 *
 * @param array $attributes     The attributes array to check the key in.
 * @param array $item           The item array to get the attribute from.
 * @return string The formatted excerpt.
 */
function format_excerpt( $attributes, $item ) {
	$result = '';
	if ( $attributes['displayExcerpt'] && isset( $item['summary'] ) ) {
		$excerpt = html_entity_decode( $item['summary'], ENT_QUOTES, get_option( 'blog_charset' ) );
		$excerpt = esc_attr( wp_trim_words( $excerpt, $attributes['excerptLength'], '' ) );

		$read_more = sprintf( ' ... <a href="%s">%s</a>', esc_url( $item['link'] ), __( 'Read more', 'wp-jobbnorge-block' ) );

		$result = sprintf( '<div class="wp-block-dss-jobbnorge__item-excerpt">%s%s</div>', esc_html( $excerpt ), $read_more );
	}
	return $result;
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
	$result = '';
	if ( $attributes[ $display_key ] && isset( $item[ $attribute_key ] ) ) {
		$result = sprintf(
			'<div class="%s">%s: %s</div>',
			$css_class,
			$label,
			esc_html( $item[ $attribute_key ] )
		);
	}
	return $result;
}

/**
 * Formats the deadline date.
 *
 * @param string $deadline_date The deadline date.
 *
 * @return string The formatted deadline date.
 */
function format_deadline( $deadline_date ) {
	if ( ! $deadline_date ) {
		return '';
	}

	try {
		$date     = parse_date( $deadline_date );
		$str_date = date_i18n( get_option( 'date_format' ), $date );
	} catch ( \Exception $e ) {
		$str_date = $deadline_date; // fallback to original date.
		$date     = false;
	}

	if ( $str_date ) {
		return sprintf(
			'<time datetime="%1$s" class="wp-block-dss-jobbnorge__item-deadline">%2$s %3$s</time> ',
			( $date ) ? esc_attr( wp_date( 'c', $date ) ) : '',
			__( 'Deadline:', 'wp-jobbnorge-block' ),
			esc_attr( $str_date )
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
	if ( class_exists( '\IntlDateFormatter' ) ) {
		return parse_date_intl( $deadline_date );
	}

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
	$formatter = \IntlDateFormatter::create( 'nb-NO', \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT, date_default_timezone_get() );
	$formatter->setPattern( 'dd.MM.yyyy' );
	// $formatter->setPattern( 'EEEE d. MMMM y' );
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
	$dato       = preg_replace( '/(\d{1})\./', '$1', $deadline_date );
	$dato       = preg_replace( '/(\d{1})\./', '0$1.', $dato );
	$dato       = str_ireplace( $str_months, $num_months, $dato, $count );
	$dato_arr   = explode( ' ', $dato );
	return mktime( 0, 0, 0, $dato_arr[2], $dato_arr[1], $dato_arr[3] );
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
	if ( isset( $attributes[ $key ] ) && $attributes[ $key ] ) {
		$classnames[] = $classname;
	}
}

