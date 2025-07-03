<?php
/**
 * Test the specific parameter that's causing the issue
 */

echo "<h3>Testing specific parameters causing the issue</h3>";

function test_parameter_combination($url, $description) {
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
        echo "<strong>First job location:</strong> " . htmlspecialchars($items[0]['locations'][0]['isDomestic'] ?? 'N/A') . "<br>";
    }
    
    echo "<hr>";
}

// Test the issue step by step
test_parameter_combination(
    'https://publicapi.jobbnorge.no/v3/Jobs?employer=2835&employer=2837',
    'Base query - WORKS (4 jobs)'
);

test_parameter_combination(
    'https://publicapi.jobbnorge.no/v3/Jobs?employer=2835&employer=2837&abroad=false',
    'Adding abroad=false - BREAKS IT'
);

test_parameter_combination(
    'https://publicapi.jobbnorge.no/v3/Jobs?employer=2835&employer=2837&abroad=true',
    'Testing abroad=true'
);

test_parameter_combination(
    'https://publicapi.jobbnorge.no/v3/Jobs?employer=2835&employer=2837&orderBy=deadline',
    'Adding orderBy=deadline without abroad'
);

test_parameter_combination(
    'https://publicapi.jobbnorge.no/v3/Jobs?employer=2835&employer=2837&page=1&results=10',
    'Adding pagination without abroad'
);

// Now let's check the jobs to see their location data
echo "<h4>Checking job location data</h4>";
$response = file_get_contents('https://publicapi.jobbnorge.no/v3/Jobs?employer=2835&employer=2837');
$data = json_decode($response, true);

if (isset($data['jobs'])) {
    foreach ($data['jobs'] as $index => $job) {
        echo "<strong>Job " . ($index + 1) . ":</strong> " . htmlspecialchars($job['title']) . "<br>";
        if (isset($job['locations'])) {
            foreach ($job['locations'] as $location) {
                echo "  - Location: " . htmlspecialchars($location['address'] ?? 'N/A') . "<br>";
                echo "  - isDomestic: " . ($location['isDomestic'] ?? 'N/A') . "<br>";
                echo "  - Country: " . htmlspecialchars($location['county'] ?? 'N/A') . "<br>";
            }
        }
        echo "<br>";
    }
}
?>
