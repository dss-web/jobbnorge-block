<?php
/**
 * Test script to check Jobbnorge API v3 response
 * Run this from command line: php test-api.php
 */

echo "Testing Jobbnorge API v3...\n\n";

// Test 1: General API call without filters
echo "=== Test 1: General API call ===\n";
$general_url = "https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=Deadline&results=3";
echo "URL: {$general_url}\n";

$response = file_get_contents( $general_url );
if ( $response !== false ) {
	$data = json_decode( $response, true );
	if ( json_last_error() === JSON_ERROR_NONE ) {
		echo "Response structure:\n";
		echo "- Jobs count: " . ( isset( $data[ 'jobs' ] ) ? count( $data[ 'jobs' ] ) : 'No jobs key' ) . "\n";
		echo "- Meta exists: " . ( isset( $data[ 'meta' ] ) ? 'YES' : 'NO' ) . "\n";
		if ( isset( $data[ 'meta' ] ) ) {
			echo "- Total jobs: " . ( isset( $data[ 'meta' ][ 'jobCountTotal' ] ) ? $data[ 'meta' ][ 'jobCountTotal' ] : 'No jobCountTotal' ) . "\n";
		}
		echo "- Available keys: " . implode( ', ', array_keys( $data ) ) . "\n";

		if ( ! empty( $data[ 'jobs' ] ) ) {
			echo "\nFirst job employerID: " . ( isset( $data[ 'jobs' ][ 0 ][ 'employerID' ] ) ? $data[ 'jobs' ][ 0 ][ 'employerID' ] : 'No employerID' ) . "\n";
			echo "First job employer: " . ( isset( $data[ 'jobs' ][ 0 ][ 'employer' ] ) ? $data[ 'jobs' ][ 0 ][ 'employer' ] : 'No employer' ) . "\n";
		}
	} else {
		echo "JSON decode error\n";
	}
} else {
	echo "Failed to fetch data\n";
}

// Test 2: With pagination
echo "\n=== Test 2: With pagination ===\n";
$paginated_url = "https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=Deadline&page=1&results=5";
echo "URL: {$paginated_url}\n";

$response = file_get_contents( $paginated_url );
if ( $response !== false ) {
	$data = json_decode( $response, true );
	if ( json_last_error() === JSON_ERROR_NONE ) {
		echo "- Jobs count: " . ( isset( $data[ 'jobs' ] ) ? count( $data[ 'jobs' ] ) : 'No jobs key' ) . "\n";
		echo "- Meta exists: " . ( isset( $data[ 'meta' ] ) ? 'YES' : 'NO' ) . "\n";
		if ( isset( $data[ 'meta' ] ) ) {
			echo "- Meta content: " . json_encode( $data[ 'meta' ] ) . "\n";
		}
	}
}

// Test 3: With a real employer ID from the first test
if ( ! empty( $data[ 'jobs' ] ) && isset( $data[ 'jobs' ][ 0 ][ 'employerID' ] ) ) {
	$real_employer_id = $data[ 'jobs' ][ 0 ][ 'employerID' ];
	echo "\n=== Test 3: With real employer ID ({$real_employer_id}) ===\n";
	$employer_url = "https://publicapi.jobbnorge.no/v3/Jobs?abroad=false&orderBy=Deadline&employer={$real_employer_id}&results=5";
	echo "URL: {$employer_url}\n";

	$response = file_get_contents( $employer_url );
	if ( $response !== false ) {
		$data = json_decode( $response, true );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			echo "- Jobs count: " . ( isset( $data[ 'jobs' ] ) ? count( $data[ 'jobs' ] ) : 'No jobs key' ) . "\n";
			echo "- Meta exists: " . ( isset( $data[ 'meta' ] ) ? 'YES' : 'NO' ) . "\n";
			if ( isset( $data[ 'meta' ] ) ) {
				echo "- Meta content: " . json_encode( $data[ 'meta' ] ) . "\n";
			}
		}
	}
}

// Test 4: Check if the old v2 API still works
echo "\n=== Test 4: Check v2 API for comparison ===\n";
$v2_url = "https://publicapi.jobbnorge.no/v2/Jobs?abroad=false&orderBy=Deadline";
echo "URL: {$v2_url}\n";

$response = file_get_contents( $v2_url );
if ( $response !== false ) {
	$data = json_decode( $response, true );
	if ( json_last_error() === JSON_ERROR_NONE ) {
		echo "- Response type: " . ( is_array( $data ) && isset( $data[ 0 ] ) ? 'Array of jobs' : 'Other' ) . "\n";
		echo "- Jobs count: " . ( is_array( $data ) ? count( $data ) : 'Not an array' ) . "\n";
		if ( is_array( $data ) && ! empty( $data ) ) {
			echo "- First job has employerID: " . ( isset( $data[ 0 ][ 'employerID' ] ) ? 'YES (' . $data[ 0 ][ 'employerID' ] . ')' : 'NO' ) . "\n";
		}
	}
}
