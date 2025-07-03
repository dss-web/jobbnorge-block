<?php
// Test that the functions are correctly defined

// Mock WordPress functions first
function plugin_dir_path($file) {
    return dirname($file) . '/';
}

function plugin_dir_url($file) {
    return 'http://example.com/wp-content/plugins/' . basename(dirname($file)) . '/';
}

function wp_rand() {
    return rand(100000, 999999);
}

function wp_register_style($handle, $src, $deps, $ver) {
    echo "wp_register_style called: $handle -> $src (v$ver)\n";
}

function wp_enqueue_style($handle) {
    echo "wp_enqueue_style called: $handle\n";
}

// Load the plugin file to get the functions
require_once __DIR__ . '/wp-jobb-norge.php';

// Check if the functions exist
echo "Function check:\n";
echo "dss_jobbnorge_enqueue_scripts exists: " . (function_exists('DSS\\Jobbnorge\\dss_jobbnorge_enqueue_scripts') ? 'YES' : 'NO') . "\n";
echo "dss_jobbnorge_enqueue_frontend_styles exists: " . (function_exists('DSS\\Jobbnorge\\dss_jobbnorge_enqueue_frontend_styles') ? 'YES' : 'NO') . "\n";

// Test the CSS path
$css_path = plugin_dir_path(__FILE__) . 'build/style-init.css';
echo "CSS file exists: " . (file_exists($css_path) ? 'YES' : 'NO') . "\n";
echo "CSS file path: " . $css_path . "\n";

if (file_exists($css_path)) {
    echo "CSS file size: " . filesize($css_path) . " bytes\n";
}

// Test the frontend enqueue function
echo "\nTesting frontend enqueue function:\n";
\DSS\Jobbnorge\dss_jobbnorge_enqueue_frontend_styles();
?>
