<?php
// Test grid functionality with real attributes

// Mock WordPress functions
function plugin_dir_path($file) {
    return dirname($file) . '/';
}

function plugin_dir_url($file) {
    return 'http://example.com/wp-content/plugins/' . basename(dirname($file)) . '/';
}

function wp_rand() { return rand(100000, 999999); }
function wp_register_style($handle, $src, $deps, $ver) { 
    echo "wp_register_style: $handle -> $src (v$ver)\n"; 
}
function wp_enqueue_style($handle) { 
    echo "wp_enqueue_style: $handle\n"; 
}
function get_block_wrapper_attributes($atts) {
    $class = isset($atts['class']) ? $atts['class'] : '';
    $data = isset($atts['data-attributes']) ? $atts['data-attributes'] : '';
    return 'class="wp-block-dss-jobbnorge ' . $class . '" data-attributes="' . $data . '"';
}
function esc_attr($text) { return htmlspecialchars($text, ENT_QUOTES); }
function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES); }
function esc_url($url) { return $url; }
function __($text, $domain = '') { return $text; }

// Test grid attributes
$test_attributes = [
    'blockLayout' => 'grid',
    'columns' => 3,
    'enablePagination' => false,
    'displayEmployer' => true,
    'displayDate' => true,
    'displayExcerpt' => true,
];

echo "=== GRID FUNCTIONALITY TEST ===\n";
echo "Test attributes: " . json_encode($test_attributes) . "\n\n";

// Test the add_classname function
function add_classname( &$classnames, $attributes, $key, $classname ) {
    if ( isset( $attributes[ $key ] ) && $attributes[ $key ] ) {
        $classnames[] = $classname;
    }
}

// Generate wrapper classes
$wrapper_classes = [];
add_classname( $wrapper_classes, $test_attributes, 'displayEmployer', 'has-employer' );
add_classname( $wrapper_classes, $test_attributes, 'displayDate', 'has-dates' );
add_classname( $wrapper_classes, $test_attributes, 'displayDeadline', 'has-deadline' );
add_classname( $wrapper_classes, $test_attributes, 'displayScope', 'has-scope' );
add_classname( $wrapper_classes, $test_attributes, 'displayExcerpt', 'has-excerpts' );

echo "Wrapper classes: " . implode(' ', $wrapper_classes) . "\n";

// Generate UL classes (the grid logic)
$ul_classes = [ 'wp-block-dss-jobbnorge' ];
if ( 'grid' === $test_attributes[ 'blockLayout' ] ) {
    $ul_classes[] = 'is-grid';
    $ul_classes[] = 'columns-' . $test_attributes[ 'columns' ];
}

echo "UL classes: " . implode(' ', $ul_classes) . "\n";

// Test wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes( [ 
    'class'           => implode( ' ', $wrapper_classes ),
    'data-attributes' => esc_attr( json_encode( $test_attributes ) ),
] );

echo "Wrapper attributes: " . $wrapper_attributes . "\n\n";

// Generate sample HTML output
$list_items = '<li class="wp-block-dss-jobbnorge__item">Test Job 1</li>';
$list_items .= '<li class="wp-block-dss-jobbnorge__item">Test Job 2</li>';
$list_items .= '<li class="wp-block-dss-jobbnorge__item">Test Job 3</li>';

$final_html = sprintf( 
    '<div %s><ul class="%s">%s</ul></div>', 
    $wrapper_attributes, 
    esc_attr( implode( ' ', $ul_classes ) ), 
    $list_items 
);

echo "=== FINAL HTML OUTPUT ===\n";
echo $final_html . "\n\n";

echo "=== EXPECTED CSS SELECTORS ===\n";
echo "ul.wp-block-dss-jobbnorge.is-grid { display: flex; flex-wrap: wrap; }\n";
echo "ul.wp-block-dss-jobbnorge.columns-3 li { width: calc(33.33333% - 1em); }\n\n";

echo "=== FRONTEND CSS ENQUEUE TEST ===\n";
// Load the plugin to get the function
require_once __DIR__ . '/wp-jobb-norge.php';
\DSS\Jobbnorge\dss_jobbnorge_enqueue_frontend_styles();

echo "\n=== CHECK CSS FILE ===\n";
$css_file = __DIR__ . '/build/style-init.css';
echo "CSS file exists: " . (file_exists($css_file) ? 'YES' : 'NO') . "\n";
if (file_exists($css_file)) {
    echo "CSS file size: " . filesize($css_file) . " bytes\n";
    $css_content = file_get_contents($css_file);
    echo "Contains grid CSS: " . (strpos($css_content, 'is-grid') !== false ? 'YES' : 'NO') . "\n";
}
?>
