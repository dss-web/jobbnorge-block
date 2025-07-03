<?php
/**
 * Debug script to investigate the "No jobs found" issue
 */

// Simulate the API call with some common employer IDs
$test_employers = [
    '194', // A known employer ID
    '1',   // Another test
];

function debug_api_call($employer_ids) {
    echo "<h3>Testing API call with employers: " . implode(', ', $employer_ids) . "</h3>";
    
    // Construct the API URL exactly like the plugin does
    $jobbnorge_api_url = 'https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=deadline';
    $jobbnorge_api_url .= '&page=1&results=10'; // Always include page to get meta
    
    foreach ($employer_ids as $id) {
        $jobbnorge_api_url .= '&employer=' . $id;
    }
    
    echo "<strong>API URL:</strong> " . htmlspecialchars($jobbnorge_api_url) . "<br>";
    
    $response = file_get_contents($jobbnorge_api_url);
    
    if ($response === false) {
        echo "<strong>Error:</strong> Failed to fetch data from API<br>";
        return;
    }
    
    $response_data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<strong>JSON Error:</strong> " . json_last_error_msg() . "<br>";
        return;
    }
    
    echo "<strong>Response received successfully</strong><br>";
    echo "<strong>Response data type:</strong> " . gettype($response_data) . "<br>";
    
    if (is_array($response_data)) {
        echo "<strong>Response keys:</strong> " . implode(', ', array_keys($response_data)) . "<br>";
        
        // Check for jobs
        $items = isset($response_data['jobs']) ? $response_data['jobs'] : $response_data;
        echo "<strong>Items type:</strong> " . gettype($items) . "<br>";
        
        if (is_array($items)) {
            echo "<strong>Items count:</strong> " . count($items) . "<br>";
            
            // Check the condition that causes "No jobs found"
            $condition_result = !$items;
            echo "<strong>Condition (!items):</strong> " . ($condition_result ? 'TRUE (shows No jobs found)' : 'FALSE (shows jobs)') . "<br>";
            
            // Better condition check
            $better_condition = empty($items) || !is_array($items);
            echo "<strong>Better condition (empty or not array):</strong> " . ($better_condition ? 'TRUE' : 'FALSE') . "<br>";
            
            if (!empty($items) && isset($items[0])) {
                echo "<strong>First item keys:</strong> " . implode(', ', array_keys($items[0])) . "<br>";
                echo "<strong>First item title:</strong> " . htmlspecialchars($items[0]['title'] ?? 'N/A') . "<br>";
            }
        } else {
            echo "<strong>Items is not an array!</strong><br>";
        }
        
        // Check meta
        if (isset($response_data['meta'])) {
            echo "<strong>Meta keys:</strong> " . implode(', ', array_keys($response_data['meta'])) . "<br>";
            if (isset($response_data['meta']['jobCountTotal'])) {
                echo "<strong>Total jobs:</strong> " . $response_data['meta']['jobCountTotal'] . "<br>";
            }
        } else {
            echo "<strong>No meta data found</strong><br>";
        }
    } else {
        echo "<strong>Response is not an array!</strong><br>";
    }
    
    echo "<hr>";
}

// Test with different employer combinations
debug_api_call(['194']);
debug_api_call(['1']);
debug_api_call(['194', '1']);
debug_api_call(['999999']); // Non-existent employer
?>
