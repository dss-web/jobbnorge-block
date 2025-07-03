<?php
/**
 * Test different API query variations for employers 2835 and 2837
 */

echo "<h3>Testing different API query variations</h3>";

function test_api_variation($url, $description) {
    echo "<h4>$description</h4>";
    echo "<strong>URL:</strong> " . htmlspecialchars($url) . "<br>";
    
    $response = file_get_contents($url);
    
    if ($response === false) {
        echo "<strong>Error:</strong> Failed to fetch data<br>";
        return;
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<strong>JSON Error:</strong> " . json_last_error_msg() . "<br>";
        return;
    }
    
    $items = isset($data['jobs']) ? $data['jobs'] : [];
    echo "<strong>Jobs count:</strong> " . count($items) . "<br>";
    
    if (isset($data['meta'])) {
        echo "<strong>Meta jobCountTotal:</strong> " . ($data['meta']['jobCountTotal'] ?? 'N/A') . "<br>";
    }
    
    if (!empty($items)) {
        echo "<strong>First job:</strong> " . htmlspecialchars($items[0]['title'] ?? 'N/A') . "<br>";
    }
    
    echo "<hr>";
}

// Test different variations
test_api_variation(
    'https://publicapi.jobbnorge.no/v3/Jobs?employer=2835&employer=2837',
    'Minimal query - just employers'
);

test_api_variation(
    'https://publicapi.jobbnorge.no/v3/Jobs?employer=2835&employer=2837&results=50',
    'With results=50, no page'
);

test_api_variation(
    'https://publicapi.jobbnorge.no/v3/Jobs?employer=2835&employer=2837&page=1',
    'With page=1, no results limit'
);

test_api_variation(
    'https://publicapi.jobbnorge.no/v3/Jobs?employer=2835&employer=2837&orderBy=title',
    'Different orderBy (title instead of deadline)'
);

test_api_variation(
    'https://publicapi.jobbnorge.no/v3/Jobs?employer=2835&employer=2837&abroad=true',
    'With abroad=true instead of false'
);

test_api_variation(
    'https://publicapi.jobbnorge.no/v3/Jobs?employer=2835&employer=2837&orderBy=publicationDate',
    'OrderBy publicationDate'
);

// Test the exact URL the plugin would generate
echo "<h4>Testing exact plugin URL (current logic)</h4>";
$plugin_url = 'https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=deadline&page=1&results=10&employer=2835&employer=2837';
test_api_variation($plugin_url, 'Exact plugin URL');
?>
