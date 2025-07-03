<?php
/**
 * Get some active employer IDs from the current jobs
 */

echo "<h3>Finding active employer IDs</h3>";

$jobbnorge_api_url = 'https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=deadline&page=1&results=50';

$response = file_get_contents($jobbnorge_api_url);
$response_data = json_decode($response, true);

if (isset($response_data['jobs'])) {
    $employers = [];
    
    foreach ($response_data['jobs'] as $job) {
        if (isset($job['employerId'])) {
            $employer_id = $job['employerId'];
            if (!isset($employers[$employer_id])) {
                $employers[$employer_id] = [
                    'name' => $job['employer'] ?? 'Unknown',
                    'count' => 0
                ];
            }
            $employers[$employer_id]['count']++;
        }
    }
    
    // Sort by job count
    arsort($employers);
    
    echo "<h4>Active employers with job counts:</h4>";
    $count = 0;
    foreach ($employers as $id => $data) {
        if ($count < 10) {
            echo "ID: <strong>$id</strong> - {$data['name']} ({$data['count']} jobs)<br>";
            $count++;
        }
    }
    
    // Test with a few active employer IDs
    $active_ids = array_keys($employers);
    $test_ids = array_slice($active_ids, 0, 3);
    
    echo "<h4>Testing with active employer IDs: " . implode(', ', $test_ids) . "</h4>";
    
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
    }
}
?>
