<?php
/**
 * Test the fixed plugin logic
 */

// Simulate the plugin's logic
$test_attributes = [
    'employerID' => '2835,2837',
    'enablePagination' => true,
    'jobsPerPage' => 10,
    'itemsToShow' => 5,
    'orderBy' => 'deadline'
];

$current_page = 1;

// Convert employer IDs to an array and trim whitespace.
$arr_ids = array_map('trim', explode(',', $test_attributes['employerID']));

// Construct the API URL (fixed version - no pagination parameters)
$jobbnorge_api_url = 'https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=' . $test_attributes['orderBy'];

// Add each employer ID to the API URL.
foreach ($arr_ids as $id) {
    $jobbnorge_api_url .= '&employer=' . $id;
}

echo "<h3>Testing fixed plugin logic</h3>";
echo "<strong>API URL:</strong> " . htmlspecialchars($jobbnorge_api_url) . "<br>";

$response = file_get_contents($jobbnorge_api_url);
$response_data = json_decode($response, true);

// Handle v3 API response structure
$all_items = isset($response_data['jobs']) ? $response_data['jobs'] : $response_data;
$total_jobs = count($all_items);

echo "<strong>Total jobs found:</strong> " . $total_jobs . "<br>";

// Implement pagination in PHP
if ($test_attributes['enablePagination'] && $total_jobs > 0) {
    $start_index = ($current_page - 1) * $test_attributes['jobsPerPage'];
    $items = array_slice($all_items, $start_index, $test_attributes['jobsPerPage']);
    echo "<strong>Jobs for page 1:</strong> " . count($items) . "<br>";
} else {
    $items = array_slice($all_items, 0, $test_attributes['itemsToShow']);
    echo "<strong>Jobs (non-paginated):</strong> " . count($items) . "<br>";
}

echo "<strong>Plugin would show:</strong> " . (empty($items) ? '"No jobs found"' : 'Job listings') . "<br>";

if (!empty($items)) {
    echo "<h4>Sample jobs:</h4>";
    foreach ($items as $index => $item) {
        echo ($index + 1) . ". " . htmlspecialchars($item['title']) . " at " . htmlspecialchars($item['employer']) . "<br>";
    }
}

// Test pagination controls
if ($test_attributes['enablePagination'] && $total_jobs > $test_attributes['jobsPerPage']) {
    echo "<h4>Pagination would be shown</h4>";
    echo "Total pages: " . ceil($total_jobs / $test_attributes['jobsPerPage']) . "<br>";
} else {
    echo "<h4>No pagination needed</h4>";
}
?>
