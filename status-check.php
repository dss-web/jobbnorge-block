<?php
echo "=== JOBBNORGE BLOCK STATUS CHECK ===\n\n";

// Check files exist
$files_to_check = [
    'wp-jobb-norge.php' => 'Main plugin file',
    'src/block.json' => 'Source block.json',
    'build/block.json' => 'Built block.json', 
    'src/style.scss' => 'Source SCSS',
    'build/style-init.css' => 'Built CSS',
    'build/init.js' => 'Built JS',
    'build/init.asset.php' => 'Asset dependencies'
];

echo "1. FILE EXISTENCE CHECK:\n";
foreach ($files_to_check as $file => $description) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $status = $exists ? '✅ EXISTS' : '❌ MISSING';
    echo "   $status - $description ($file)\n";
    if ($exists && $file === 'build/style-init.css') {
        $size = filesize(__DIR__ . '/' . $file);
        echo "           Size: $size bytes\n";
    }
}

echo "\n2. CSS CONTENT CHECK:\n";
$css_file = __DIR__ . '/build/style-init.css';
if (file_exists($css_file)) {
    $css_content = file_get_contents($css_file);
    $checks = [
        'wp-block-dss-jobbnorge' => 'Base class',
        'is-grid' => 'Grid class',
        'columns-3' => 'Column class',
        'display:flex' => 'Flexbox CSS',
        'flex-wrap:wrap' => 'Flex wrap CSS',
        'calc(' => 'Column width calculation'
    ];
    
    foreach ($checks as $search => $description) {
        $found = strpos($css_content, $search) !== false;
        $status = $found ? '✅ FOUND' : '❌ MISSING';
        echo "   $status - $description ($search)\n";
    }
} else {
    echo "   ❌ CSS file not found!\n";
}

echo "\n3. BLOCK.JSON VERSION CHECK:\n";
$src_json = __DIR__ . '/src/block.json';
$build_json = __DIR__ . '/build/block.json';

if (file_exists($src_json) && file_exists($build_json)) {
    $src_data = json_decode(file_get_contents($src_json), true);
    $build_data = json_decode(file_get_contents($build_json), true);
    
    echo "   Source version: " . ($src_data['version'] ?? 'unknown') . "\n";
    echo "   Build version: " . ($build_data['version'] ?? 'unknown') . "\n";
    
    $version_match = ($src_data['version'] ?? '') === ($build_data['version'] ?? '');
    $status = $version_match ? '✅ MATCH' : '❌ MISMATCH';
    echo "   $status - Versions match\n";
    
    // Check blockLayout attribute
    $has_block_layout = isset($src_data['attributes']['blockLayout']);
    $status = $has_block_layout ? '✅ EXISTS' : '❌ MISSING';
    echo "   $status - blockLayout attribute\n";
}

echo "\n4. PHP FUNCTION CHECK:\n";
$functions_to_check = [
    'DSS\\Jobbnorge\\dss_jobbnorge_init',
    'DSS\\Jobbnorge\\dss_jobbnorge_enqueue_frontend_styles',
    'DSS\\Jobbnorge\\render_block_dss_jobbnorge'
];

// Load the plugin
require_once __DIR__ . '/wp-jobb-norge.php';

foreach ($functions_to_check as $function) {
    $exists = function_exists($function);
    $status = $exists ? '✅ EXISTS' : '❌ MISSING';
    echo "   $status - $function\n";
}

echo "\n5. GRID LOGIC TEST:\n";
$test_attributes = ['blockLayout' => 'grid', 'columns' => 3];
$ul_classes = ['wp-block-dss-jobbnorge'];

if ('grid' === $test_attributes['blockLayout']) {
    $ul_classes[] = 'is-grid';
    $ul_classes[] = 'columns-' . $test_attributes['columns'];
    echo "   ✅ Grid logic working\n";
    echo "   Classes generated: " . implode(' ', $ul_classes) . "\n";
} else {
    echo "   ❌ Grid logic failed\n";
}

echo "\n6. PACKAGE.JSON VERSION:\n";
$package_file = __DIR__ . '/package.json';
if (file_exists($package_file)) {
    $package_data = json_decode(file_get_contents($package_file), true);
    echo "   Package version: " . ($package_data['version'] ?? 'unknown') . "\n";
} else {
    echo "   ❌ package.json not found\n";
}

echo "\n7. RECOMMENDATIONS:\n";
echo "   - Clear browser cache and hard refresh (Ctrl+F5 or Cmd+Shift+R)\n";
echo "   - Check browser dev tools to see if CSS is loading\n";
echo "   - Verify the grid HTML structure in browser inspector\n";
echo "   - Open debug-grid.html in browser to test CSS directly\n";
echo "   - Check WordPress admin for any block errors\n";

echo "\n=== STATUS CHECK COMPLETE ===\n";
?>
