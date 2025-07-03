<?php
/**
 * Test the updated API logic
 */

// Test with real employer ID from previous test
$real_employer_id = 617; // UiT Norges arktiske universitet
$api_url          = "https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=Deadline&page=1&results=50&employer={$real_employer_id}";

echo "Testing updated API logic...\n";
echo "URL: {$api_url}\n\n";

$response = file_get_contents( $api_url );
if ( $response !== false ) {
	$response_data = json_decode( $response, true );
	if ( json_last_error() === JSON_ERROR_NONE ) {
		echo "Response structure:\n";
		echo "- Jobs key exists: " . ( isset( $response_data[ 'jobs' ] ) ? 'YES' : 'NO' ) . "\n";
		echo "- Jobs count: " . ( isset( $response_data[ 'jobs' ] ) ? count( $response_data[ 'jobs' ] ) : 0 ) . "\n";
		echo "- Meta key exists: " . ( isset( $response_data[ 'meta' ] ) ? 'YES' : 'NO' ) . "\n";

		if ( isset( $response_data[ 'meta' ] ) ) {
			echo "- Total jobs from meta: " . $response_data[ 'meta' ][ 'jobCountTotal' ] . "\n";
		}

		// Test the logic from the updated code
		$items = isset( $response_data[ 'jobs' ] ) ? $response_data[ 'jobs' ] : $response_data;

		if ( isset( $response_data[ 'meta' ][ 'jobCountTotal' ] ) ) {
			$total_jobs = $response_data[ 'meta' ][ 'jobCountTotal' ];
		} else {
			$total_jobs = count( $items );
			if ( count( $items ) === 50 ) { // items_per_page
				$total_jobs = 50 * 10; // Conservative estimate
			}
		}

		echo "\nLogic results:\n";
		echo "- Items count: " . count( $items ) . "\n";
		echo "- Total jobs calculated: " . $total_jobs . "\n";
		echo "- Will show 'No jobs found': " . ( empty( $items ) ? 'YES' : 'NO' ) . "\n";

		if ( ! empty( $items ) ) {
			echo "- First job title: " . ( isset( $items[ 0 ][ 'title' ] ) ? $items[ 0 ][ 'title' ] : 'No title' ) . "\n";
			echo "- First job employer: " . ( isset( $items[ 0 ][ 'employer' ] ) ? $items[ 0 ][ 'employer' ] : 'No employer' ) . "\n";
		}
	} else {
		echo "JSON decode error: " . json_last_error_msg() . "\n";
	}
} else {
	echo "Failed to fetch API response\n";
}

// Also test with no employer filter to see if that works better
echo "\n" . str_repeat( "=", 50 ) . "\n";
echo "Testing without employer filter:\n";

$general_url = "https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=Deadline&page=1&results=5";
echo "URL: {$general_url}\n";

$response = file_get_contents( $general_url );
if ( $response !== false ) {
	$response_data = json_decode( $response, true );
	if ( json_last_error() === JSON_ERROR_NONE ) {
		$items = isset( $response_data[ 'jobs' ] ) ? $response_data[ 'jobs' ] : $response_data;
		echo "- Jobs found: " . count( $items ) . "\n";
		echo "- Meta exists: " . ( isset( $response_data[ 'meta' ] ) ? 'YES' : 'NO' ) . "\n";
		if ( isset( $response_data[ 'meta' ] ) ) {
			echo "- Total from meta: " . $response_data[ 'meta' ][ 'jobCountTotal' ] . "\n";
		}
	}
}
