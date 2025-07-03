<?php
/**
 * Test with real employer IDs from the API
 */

echo "<h3>Testing with real employer IDs</h3>";

// First, get some real employer IDs
$jobbnorge_api_url = 'https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=deadline&page=1&results=20';
$response = file_get_contents($jobbnorge_api_url);
$response_data = json_decode($response, true);

$real_employer_ids = [];
if (isset($response_data['jobs'])) {
    foreach ($response_data['jobs'] as $job) {
        if (isset($job['employerID'])) {
            $real_employer_ids[] = $job['employerID'];
        }
    }
}

$real_employer_ids = array_unique($real_employer_ids);
$test_ids = array_slice($real_employer_ids, 0, 3);

echo "<strong>Real employer IDs found:</strong> " . implode(', ', $test_ids) . "<br>";

// Test with these real IDs
$test_url = 'https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=deadline&page=1&results=10';
foreach ($test_ids as $id) {
    $test_url .= '&employer=' . $id;
}

echo "<strong>Test URL:</strong> " . htmlspecialchars($test_url) . "<br>";

$test_response = file_get_contents($test_url);
$test_data = json_decode($test_response, true);

if (isset($test_data['jobs'])) {
    echo "<strong>Jobs found:</strong> " . count($test_data['jobs']) . "<br>";
    if (isset($test_data['meta']['jobCountTotal'])) {
        echo "<strong>Total jobs for these employers:</strong> " . $test_data['meta']['jobCountTotal'] . "<br>";
    }
    
    if (!empty($test_data['jobs'])) {
        echo "<strong>First job title:</strong> " . htmlspecialchars($test_data['jobs'][0]['title']) . "<br>";
        echo "<strong>First job employer:</strong> " . htmlspecialchars($test_data['jobs'][0]['employer']) . "<br>";
    }
    
    // Test the condition from the plugin
    $items = $test_data['jobs'];
    $condition_result = !$items;
    echo "<strong>Plugin condition (!items):</strong> " . ($condition_result ? 'TRUE (would show No jobs found)' : 'FALSE (would show jobs)') . "<br>";
}
?>
