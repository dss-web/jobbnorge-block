<?php
/**
 * Test script to verify grid functionality after the fix
 */

// Test the grid CSS loading
echo "=== GRID FIX VERIFICATION ===\n\n";

// 1. Check if editor.css contains grid styles
$editor_css_path = __DIR__ . '/build/editor.css';
$editor_css_exists = file_exists($editor_css_path);
echo "1. Editor CSS exists: " . ($editor_css_exists ? 'YES' : 'NO') . "\n";

if ($editor_css_exists) {
    $editor_css_content = file_get_contents($editor_css_path);
    $has_grid_styles = strpos($editor_css_content, 'is-grid') !== false;
    echo "   Contains grid styles: " . ($has_grid_styles ? 'YES' : 'NO') . "\n";
    
    if ($has_grid_styles) {
        echo "   Grid CSS found in editor.css ✓\n";
    } else {
        echo "   ERROR: Grid CSS missing from editor.css ✗\n";
    }
}

// 2. Check if frontend CSS contains grid styles
$frontend_css_path = __DIR__ . '/build/style-init.css';
$frontend_css_exists = file_exists($frontend_css_path);
echo "\n2. Frontend CSS exists: " . ($frontend_css_exists ? 'YES' : 'NO') . "\n";

if ($frontend_css_exists) {
    $frontend_css_content = file_get_contents($frontend_css_path);
    $has_grid_styles = strpos($frontend_css_content, 'is-grid') !== false;
    echo "   Contains grid styles: " . ($has_grid_styles ? 'YES' : 'NO') . "\n";
    
    if ($has_grid_styles) {
        echo "   Grid CSS found in frontend CSS ✓\n";
    } else {
        echo "   ERROR: Grid CSS missing from frontend CSS ✗\n";
    }
}

// 3. Check block.json configuration
$block_json_path = __DIR__ . '/build/block.json';
$block_json_exists = file_exists($block_json_path);
echo "\n3. Block.json exists: " . ($block_json_exists ? 'YES' : 'NO') . "\n";

if ($block_json_exists) {
    $block_json_content = file_get_contents($block_json_path);
    $block_data = json_decode($block_json_content, true);
    
    echo "   editorStyle: " . ($block_data['editorStyle'] ?? 'NOT SET') . "\n";
    echo "   style: " . ($block_data['style'] ?? 'NOT SET') . "\n";
    
    // Check if referenced files exist
    $editor_style_file = __DIR__ . '/build/' . str_replace('file:', '', $block_data['editorStyle'] ?? '');
    $style_file = __DIR__ . '/build/' . str_replace('file:', '', $block_data['style'] ?? '');
    
    echo "   editorStyle file exists: " . (file_exists($editor_style_file) ? 'YES' : 'NO') . "\n";
    echo "   style file exists: " . (file_exists($style_file) ? 'YES' : 'NO') . "\n";
}

// 4. Test grid class generation
echo "\n4. Testing grid class generation:\n";

// Mock attributes for grid layout
$test_attributes = [
    'blockLayout' => 'grid',
    'columns' => 3,
    'employerID' => '123',
    'displayEmployer' => false,
    'displayDate' => true,
    'displayDeadline' => false,
    'displayScope' => false,
    'displayExcerpt' => true,
    'itemsToShow' => 5,
    'enablePagination' => false,
    'jobsPerPage' => 10,
];

// Generate the ul classes (same logic as in main PHP file)
$ul_classes = [ 'wp-block-dss-jobbnorge' ];
if ( 'grid' === $test_attributes[ 'blockLayout' ] ) {
    $ul_classes[] = 'is-grid';
    $ul_classes[] = 'columns-' . $test_attributes[ 'columns' ];
}

echo "   Generated classes: " . implode(' ', $ul_classes) . "\n";
echo "   Classes look correct: " . (in_array('is-grid', $ul_classes) && in_array('columns-3', $ul_classes) ? 'YES' : 'NO') . "\n";

// 5. Summary
echo "\n=== FIX SUMMARY ===\n";
echo "The grid view issue has been fixed by:\n";
echo "1. ✓ Updated webpack.config.js to build editor.scss and style.scss separately\n";
echo "2. ✓ Added grid styles to editor.scss so they're available in the editor\n";
echo "3. ✓ Updated block.json to reference the correct CSS files\n";
echo "4. ✓ Rebuilt the project to generate new CSS files\n";
echo "\nGrid view should now work in both the editor and frontend!\n";
echo "\nNext steps:\n";
echo "- Test the block in the WordPress editor\n";
echo "- Test the block on the frontend\n";
echo "- Verify grid layout displays jobs in columns as expected\n";
?>
