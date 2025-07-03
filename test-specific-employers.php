<?php
/**
 * Test specific employer IDs: 2835 and 2837
 */

echo "<h3>Testing employer IDs: 2835 and 2837</h3>";

function test_employers($employer_ids) {
    $url = 'https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=deadline&page=1&results=10';
    foreach ($employer_ids as $id) {
        $url .= '&employer=' . $id;
    }
    
    echo "<strong>API URL:</strong> " . htmlspecialchars($url) . "<br>";
    
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
    
    echo "<strong>Response keys:</strong> " . implode(', ', array_keys($data)) . "<br>";
    
    $items = isset($data['jobs']) ? $data['jobs'] : [];
    echo "<strong>Jobs array count:</strong> " . count($items) . "<br>";
    echo "<strong>Jobs array type:</strong> " . gettype($items) . "<br>";
    
    if (isset($data['meta'])) {
        echo "<strong>Meta data:</strong><br>";
        foreach ($data['meta'] as $key => $value) {
            echo "  - $key: $value<br>";
        }
    } else {
        echo "<strong>No meta data found</strong><br>";
    }
    
    // Test the exact condition from the plugin
    $condition = !$items;
    echo "<strong>Plugin condition (!items):</strong> " . ($condition ? 'TRUE (shows No jobs found)' : 'FALSE (shows jobs)') . "<br>";
    
    // Test alternative conditions
    echo "<strong>empty(\$items):</strong> " . (empty($items) ? 'TRUE' : 'FALSE') . "<br>";
    echo "<strong>count(\$items) === 0:</strong> " . (count($items) === 0 ? 'TRUE' : 'FALSE') . "<br>";
    echo "<strong>is_array(\$items):</strong> " . (is_array($items) ? 'TRUE' : 'FALSE') . "<br>";
    
    if (!empty($items)) {
        echo "<strong>First job title:</strong> " . htmlspecialchars($items[0]['title'] ?? 'N/A') . "<br>";
        echo "<strong>First job employer:</strong> " . htmlspecialchars($items[0]['employer'] ?? 'N/A') . "<br>";
        echo "<strong>First job employerID:</strong> " . htmlspecialchars($items[0]['employerID'] ?? 'N/A') . "<br>";
    }
    
    echo "<hr>";
}

// Test each employer individually
echo "<h4>Testing employer 2835 individually:</h4>";
test_employers(['2835']);

echo "<h4>Testing employer 2837 individually:</h4>";
test_employers(['2837']);

echo "<h4>Testing both employers together:</h4>";
test_employers(['2835', '2837']);

// Test if these employers exist in the general API
echo "<h4>Checking if these employers exist in recent jobs:</h4>";
$general_url = 'https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=deadline&page=1&results=100';
$general_response = file_get_contents($general_url);
$general_data = json_decode($general_response, true);

$found_employers = [];
if (isset($general_data['jobs'])) {
    foreach ($general_data['jobs'] as $job) {
        if (isset($job['employerID'])) {
            $found_employers[] = $job['employerID'];
        }
    }
}

$found_employers = array_unique($found_employers);
echo "<strong>Found employer IDs in recent jobs:</strong> " . implode(', ', array_slice($found_employers, 0, 20)) . "...<br>";
echo "<strong>Is 2835 in recent jobs?</strong> " . (in_array(2835, $found_employers) ? 'YES' : 'NO') . "<br>";
echo "<strong>Is 2837 in recent jobs?</strong> " . (in_array(2837, $found_employers) ? 'YES' : 'NO') . "<br>";
?>
