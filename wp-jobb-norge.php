<?php
/**
 * Plugin Name:       Jobbnorge Block
 * Plugin URI:        https://wordpress.org/plugins/jobbnorge-block/
 * Description:       List jobs at jobbnorge.no
 * Requires at least: 5.9
 * Requires PHP:      7.0
 * Version:           2.1.0
 * Author:            PerS
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-jobbnorge-block
 *
 * @package           wp-jobbnorge-block
 */

namespace DSS\Jobbnorge;

if ( ! \class_exists( 'Jobbnorge_CacheHandler' ) ) {
	require_once __DIR__ . '/class-jobbnorge-cachehandler.php';
}


add_action( 'init', __NAMESPACE__ . '\dss_jobbnorge_init' );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function dss_jobbnorge_init() {
	// Add the 'dss_jobbnorge_enqueue_scripts' function to the 'admin_enqueue_scripts' action hook.
	// This function will be called when scripts and styles are enqueued for the admin panel.
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\dss_jobbnorge_enqueue_scripts' );

	// Add the 'dss_jobbnorge_enqueue_scripts' function to the 'wp_enqueue_scripts' action hook.
	// This function will be called when scripts and styles are enqueued for the front end of the site.
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\dss_jobbnorge_enqueue_scripts' );

	// Load the plugin's text domain for internationalization.
	// The second argument is set to false to not override the global locale.
	// The third argument is the path to the plugin's languages directory.
	load_plugin_textdomain( 'wp-jobbnorge-block', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	// Register the block type.
	// The first argument is the path to the block's build directory.
	// The second argument is an array of options for the block, including a render callback function.
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

	// Check if the current page is a post editing page.
	if ( 'post.php' !== $hook_suffix && 'post-new.php' !== $hook_suffix && 'edit.php' !== $hook_suffix ) {
		// If not, exit early.
		return;
	}

	// Define the path to the dependencies file.
	$deps_file = plugin_dir_path( __FILE__ ) . 'build/init.asset.php';

	// Initialize an array for JavaScript dependencies and a random version number.
	$jsdeps  = [];
	$version = wp_rand();

	// Check if the dependencies file exists.
	if ( file_exists( $deps_file ) ) {
		// If it does, require it and merge its dependencies with the existing ones.
		$file   = require $deps_file;
		$jsdeps = array_merge( $jsdeps, $file['dependencies'] );
		// Also, set the version to the one from the file.
		$version = $file['version'];
	}

	// Check if the current view is the admin dashboard.
	if ( is_admin() ) {
		// If it is, register and enqueue a CSS file for the admin view.
		wp_register_style( 'dss-jobbnorge-admin', plugin_dir_url( __FILE__ ) . 'build/init.css', [], $version );
		wp_enqueue_style( 'dss-jobbnorge-admin' );
	}

	// Register and enqueue a CSS file for the public view.
	wp_register_style( 'dss-jobbnorge', plugin_dir_url( __FILE__ ) . 'build/style-init.css', [], $version );
	wp_enqueue_style( 'dss-jobbnorge' );

	// Set translations for the script.
	wp_set_script_translations(
		'dss-jobbnorge-editor-script', // Handle = block.json "name" (replace / with -) + "-editor-script".
		'wp-jobbnorge-block',
		plugin_dir_path( __FILE__ ) . 'languages/'
	);

	// Apply filter to modify the employers list.
	$employers = apply_filters( 'jobbnorge_employers', false );

	// Proceed with localization if employers is not false.
	if ( false !== $employers ) {
		// Ensure employers is an array.
		if ( ! is_array( $employers ) ) {
			$employers = [];
		}

		// Localize the script to make employers data available.
		wp_localize_script(
			'dss-jobbnorge-editor-script',
			'wpJobbnorgeBlock',
			[
				'employers' => $employers,
			]
		);
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

	// Set default values for attributes.
	$attributes = wp_parse_args(
		$attributes,
		[
			'employerID'      => '',
			'displayEmployer' => false,
			'displayDate'     => true,
			'displayDeadline' => false,
			'displayScope'    => false,
			'displayExcerpt'  => true,
			'excerptLength'   => 55,
			'blockLayout'     => 'list',
			'orderBy'         => 'Deadline',
			'columns'         => 3,
			'itemsToShow'     => 5,
		]
	);

	// Convert employer IDs to an array and trim whitespace.
	$arr_ids = array_map( 'trim', explode( ',', $attributes['employerID'] ) );

	// Check if all IDs are numeric. If not, return an error message.
	if ( ! array_filter( $arr_ids, 'is_numeric' ) ) {
		return '<div class="components-placeholder"><div class="notice notice-error">' . __( 'Invalid ID', 'wp-jobbnorge-block' ) . '</div></div>';
	}

	// Construct the API URL.
	$jobbnorge_api_url = 'https://publicapi.jobbnorge.no/v2/Jobs?abroad=false&orderBy=' . $attributes['orderBy'];

	// Add each employer ID to the API URL.
	foreach ( $arr_ids as $id ) {
		$jobbnorge_api_url .= '&employer=' . $id;
	}

	$cache_path = apply_filters( 'jobbnorge_cache_path', WP_CONTENT_DIR . '/cache/jobbnorge' );
	$cache      = new \Jobbnorge_CacheHandler( $cache_path );

	$cache_key  = md5( $jobbnorge_api_url );
	$expiration = apply_filters( 'jobbnorge_cache_time', 30 * MINUTE_IN_SECONDS );
	$body       = $cache->get( $cache_key, $expiration );

	if ( false === $body ) {
		$response = wp_remote_get( $jobbnorge_api_url );

		if ( is_wp_error( $response ) ) {
			return '<div class="components-placeholder"><div class="notice notice-error">' . __( 'Error connecting to Jobbnorge.no', 'wp-jobbnorge-block' ) . '</div></div>';
		}

		$body = wp_remote_retrieve_body( $response );
		$cache->set( $cache_key, $body );
	}

	// Decode the JSON response and limit the number of items.
	$items = json_decode( $body, true );
	$items = array_slice( $items, 0, $attributes['itemsToShow'] );

	// If there are no items, return an error message.
	if ( ! $items ) {
		return '<div class="components-placeholder"><div class="notice notice-error">' . __( 'No jobs found', 'wp-jobbnorge-block' ) . '</div></div>';
	}

	// Initialize an empty string for the list items.
	$list_items = '';

	// Loop through each item.
	foreach ( $items as $item ) {
		// Sanitize and format the title.
		$title = esc_html( trim( wp_strip_all_tags( $item['title'] ) ) );
		$title = empty( $title ) ? __( '(no title)' ) : $title;

		// Sanitize the link.
		$link = esc_url( $item['link'] );
		// If there's a link, wrap the title in an anchor tag.
		$title = $link ? "<a href='{$link}'>{$title}</a>" : $title;

		// Wrap the title in a div.
		$title = "<div class='wp-block-dss-jobbnorge__item-title'>{$title}</div>";

		// Initialize an empty string for the deadline.
		$deadline = '';
		// If the displayDate attribute is true and the item has a deadline, format the deadline.
		if ( $attributes['displayDate'] && isset( $item['deadline'] ) ) {
			$deadline = format_deadline( $item['deadline'] );
		}

		// Format the excerpt.
		$excerpt = format_excerpt( $attributes, $item );

		// Format the employer and scope attributes.
		$employer = format_attribute( $attributes, $item, 'employer', 'displayEmployer', 'wp-block-dss-jobbnorge__item-employer', __( 'Employer', 'wp-jobbnorge-block' ) );
		$scope    = format_attribute( $attributes, $item, 'jobScope', 'displayScope', 'wp-block-dss-jobbnorge__item-scope', __( 'Scope', 'wp-jobbnorge-block' ) );

		// Initialize an empty string for the meta.
		$meta = '';
		// If there's an employer, deadline, or scope, wrap them in a div.
		if ( $employer || $deadline || $scope ) {
			$meta = '<div class="wp-block-dss-jobbnorge__item-meta">' . $employer . $deadline . $scope . '</div>';
		}

		// Add the item to the list items string.
		$list_items .= "<li class='wp-block-dss-jobbnorge__item'>{$title}{$meta}{$excerpt}</li>";
	}

	// Initialize an array for the classnames.
	$classnames = [];

	// If the blockLayout attribute is 'grid', add the 'is-grid' and 'columns-' classes.
	if ( 'grid' === $attributes['blockLayout'] ) {
		add_classname( $classnames, $attributes, 'blockLayout', 'is-grid' );
		add_classname( $classnames, $attributes, 'columns', 'columns-' . $attributes['columns'] );
	}

	// Add the 'has-' classes based on the display attributes.
	add_classname( $classnames, $attributes, 'displayEmployer', 'has-employer' );
	add_classname( $classnames, $attributes, 'displayDate', 'has-dates' );
	add_classname( $classnames, $attributes, 'displayDeadline', 'has-deadline' );
	add_classname( $classnames, $attributes, 'displayScope', 'has-scope' );
	add_classname( $classnames, $attributes, 'displayExcerpt', 'has-excerpts' );

	// Get the block wrapper attributes and add the classnames to it.
	$wrapper_attributes = get_block_wrapper_attributes( [ 'class' => implode( ' ', $classnames ) ] );

	// Return the final HTML string, wrapping the list items in an unordered list.
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
	// Initialize an empty string for the result.
	$result = '';

	// If the displayExcerpt attribute is true and the item has a summary, format the excerpt.
	if ( $attributes['displayExcerpt'] && isset( $item['summary'] ) ) {
		// Decode the HTML entities in the summary.
		$excerpt = html_entity_decode( $item['summary'], ENT_QUOTES, get_option( 'blog_charset' ) );
		// Trim the excerpt to the excerptLength and escape it for safe use in HTML output.
		$excerpt = esc_attr( wp_trim_words( $excerpt, $attributes['excerptLength'], '' ) );

		// Format the read more link.
		$read_more = sprintf( ' ... <a href="%s">%s</a>', esc_url( $item['link'] ), __( 'Read more', 'wp-jobbnorge-block' ) );

		// Add the excerpt and read more link to the result string, wrapped in a div.
		$result = sprintf( '<div class="wp-block-dss-jobbnorge__item-excerpt">%s%s</div>', esc_html( $excerpt ), $read_more );
	}

	// Return the result.
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
function format_deadline( $deadline_date ) {
	// If there's no deadline date, return an empty string.
	if ( ! $deadline_date ) {
		return '';
	}

	try {
		// Try to parse the deadline date.
		$date = parse_date( $deadline_date );
		// Format the date according to the site's date format.
		$str_date = date_i18n( get_option( 'date_format' ), $date );
	} catch ( \Exception $e ) {
		// If there's an exception, fallback to the original date.
		$str_date = $deadline_date;
		$date     = false;
	}

	// If there's a formatted date, return a time element with the date.
	if ( $str_date ) {
		return sprintf(
			'<time datetime="%1$s" class="wp-block-dss-jobbnorge__item-deadline">%2$s %3$s</time> ',
			// If there's a parsed date, use it for the datetime attribute. Otherwise, leave it empty.
			( $date ) ? esc_attr( wp_date( 'c', $date ) ) : '',
			// Translate the 'Deadline:' string.
			__( 'Deadline:', 'wp-jobbnorge-block' ),
			// Escape the formatted date for safe use in HTML output.
			esc_attr( $str_date )
		);
	}

	// If there's no formatted date, return an empty string.
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
	// Check if the attribute key exists and is truthy in the attributes array.
	if ( isset( $attributes[ $key ] ) && $attributes[ $key ] ) {
		// If so, add the classname to the classnames array.
		$classnames[] = $classname;
	}
}
