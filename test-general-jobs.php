<?php
/**
 * Test if there are any jobs at all in the API
 */

echo "<h3>Testing API without employer filter</h3>";

$jobbnorge_api_url = 'https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=deadline&page=1&results=10';

echo "<strong>API URL:</strong> " . htmlspecialchars($jobbnorge_api_url) . "<br>";

$response = file_get_contents($jobbnorge_api_url);

if ($response === false) {
    echo "<strong>Error:</strong> Failed to fetch data from API<br>";
    exit;
}

$response_data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "<strong>JSON Error:</strong> " . json_last_error_msg() . "<br>";
    exit;
}

echo "<strong>Response received successfully</strong><br>";
echo "<strong>Response data type:</strong> " . gettype($response_data) . "<br>";

if (is_array($response_data)) {
    echo "<strong>Response keys:</strong> " . implode(', ', array_keys($response_data)) . "<br>";
    
    $items = isset($response_data['jobs']) ? $response_data['jobs'] : $response_data;
    echo "<strong>Items count:</strong> " . count($items) . "<br>";
    
    if (isset($response_data['meta'])) {
        echo "<strong>Meta keys:</strong> " . implode(', ', array_keys($response_data['meta'])) . "<br>";
        if (isset($response_data['meta']['jobCountTotal'])) {
            echo "<strong>Total jobs available:</strong> " . $response_data['meta']['jobCountTotal'] . "<br>";
        }
    }
    
    if (!empty($items) && isset($items[0])) {
        echo "<strong>First job title:</strong> " . htmlspecialchars($items[0]['title'] ?? 'N/A') . "<br>";
        echo "<strong>First job employer:</strong> " . htmlspecialchars($items[0]['employer'] ?? 'N/A') . "<br>";
        echo "<strong>First job ID:</strong> " . htmlspecialchars($items[0]['employerId'] ?? 'N/A') . "<br>";
    }
    
    // List some employers to test with
    echo "<h4>Available employers (first 5):</h4>";
    for ($i = 0; $i < min(5, count($items)); $i++) {
        if (isset($items[$i]['employerId'])) {
            echo "Employer ID: " . $items[$i]['employerId'] . " - " . htmlspecialchars($items[$i]['employer'] ?? 'N/A') . "<br>";
        }
    }
}
?>
