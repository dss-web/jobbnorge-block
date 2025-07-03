<?php
/**
 * Examine the actual job structure from API v3
 */

echo "<h3>Examining job structure from API v3</h3>";

$jobbnorge_api_url = 'https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=deadline&page=1&results=3';

$response = file_get_contents($jobbnorge_api_url);
$response_data = json_decode($response, true);

if (isset($response_data['jobs']) && !empty($response_data['jobs'])) {
    echo "<h4>Full structure of first job:</h4>";
    echo "<pre>";
    print_r($response_data['jobs'][0]);
    echo "</pre>";
    
    echo "<h4>Keys in first job:</h4>";
    echo implode(', ', array_keys($response_data['jobs'][0])) . "<br>";
    
    // Check if there's any ID field
    foreach ($response_data['jobs'][0] as $key => $value) {
        if (stripos($key, 'id') !== false) {
            echo "<strong>ID field found:</strong> $key = $value<br>";
        }
    }
}
?>
