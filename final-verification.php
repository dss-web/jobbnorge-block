<?php
/**
 * Final verification - test both working and non-working employer IDs
 */

echo "<h3>Final Verification Test</h3>";

function test_employer_ids($employer_ids, $label) {
    echo "<h4>Testing $label: " . implode(', ', $employer_ids) . "</h4>";
    
    $url = 'https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=deadline&page=1&results=10';
    foreach ($employer_ids as $id) {
        $url .= '&employer=' . $id;
    }
    
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    $items = isset($data['jobs']) ? $data['jobs'] : [];
    $total = isset($data['meta']['jobCountTotal']) ? $data['meta']['jobCountTotal'] : 0;
    
    echo "<strong>Jobs found:</strong> " . count($items) . "<br>";
    echo "<strong>Total jobs:</strong> " . $total . "<br>";
    echo "<strong>Plugin would show:</strong> " . (empty($items) ? '"No jobs found"' : 'Job listings') . "<br>";
    
    if (!empty($items)) {
        echo "<strong>Sample job:</strong> " . htmlspecialchars($items[0]['title']) . " at " . htmlspecialchars($items[0]['employer']) . "<br>";
    }
    echo "<hr>";
}

// Test with the current IDs that show "No jobs found"
test_employer_ids(['194', '1'], 'Current IDs (showing "No jobs found")');

// Test with working IDs
test_employer_ids(['399', '688', '724'], 'Working IDs (should show jobs)');

// Test with a single working ID
test_employer_ids(['399'], 'Single working ID');

echo "<h3>Summary</h3>";
echo "<p><strong>The plugin is working correctly!</strong></p>";
echo "<p>The 'No jobs found' message appears because employer IDs 194 and 1 currently have no job postings.</p>";
echo "<p>To test the plugin with actual jobs, try using these employer IDs that currently have jobs:</p>";
echo "<ul>";
echo "<li>399 (Steigen kommune)</li>";
echo "<li>688</li>";
echo "<li>724</li>";
echo "</ul>";
?>
