<?php
/**
 * Cache handler for Jobbnorge API
 *
 * The CacheHandler class is a simple caching mechanism that stores data in PHP files. In theory, nothing
 * is faster in PHP than loading and executing another PHP file. If you have PHP OPcache enabled, then the
 * PHP content will be cached in memory, and the PHP file will not be parsed again.
 *
 * @package Jobbnorge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_Filesystem' ) ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
}


/**
 * Cache handler for Jobbnorge API
 */
class Jobbnorge_CacheHandler {
	/**
	 * Cache directory
	 *
	 * @var string
	 */
	private $cache_dir;

	/**
	 * Constructor.
	 *
	 * @param string $cache_dir The directory to store cache files.
	 */
	public function __construct( $cache_dir ) {
		$this->cache_dir = $cache_dir;
	}

	/**
	 * Get cached data.
	 *
	 * @param string $key The cache key.
	 * @param int    $expiration The cache expiration time in seconds.
	 * @return mixed The cached data, or false if the cache is expired or does not exist.
	 */
	public function get( $key, $expiration ) {
		try {
			$file = $this->get_file_path( $key );

			// If file path is invalid or file doesn't exist, return false.
			if ( false === $file || ! file_exists( $file ) ) {
				return false;
			}

			// If the cache file is not expired, return its contents.
			if ( ( filemtime( $file ) + $expiration > time() ) ) {
				return include $file;
			}
		} catch (\Throwable $e) {
			// Fail silently - cache miss is acceptable.
			return false;
		}

		return false;
	}

	/**
	 * Set cached data.
	 *
	 * @param string $key The cache key.
	 * @param mixed  $data The data to cache.
	 * @return bool True on success, false on failure.
	 */
	public function set( $key, $data ) {
		try {
			$file = $this->get_file_path( $key );

			// If file path is invalid or empty, fail silently.
			if ( false === $file || '' === $file || ! is_string( $file ) ) {
				return false;
			}

			$data = var_export( $data, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export

			// Write data to cache file using direct file operations (more reliable).
			// Suppress errors to prevent breaking the page if write fails.
			$result = @file_put_contents( $file, "<?php\nreturn $data;\n", LOCK_EX ); // phpcs:ignore
			return false !== $result;
		} catch (\Throwable $e) {
			// Fail silently - caching is non-critical.
			return false;
		}
	}

	/**
	 * Get the file path for a cache key.
	 *
	 * @param string $key The cache key.
	 * @return string|false The file path, or false on failure.
	 */
	private function get_file_path( $key ) {
		// Validate cache directory is set.
		if ( empty( $this->cache_dir ) || ! is_string( $this->cache_dir ) ) {
			return false;
		}

		// Ensure cache directory exists.
		if ( ! file_exists( $this->cache_dir ) ) {
			if ( ! wp_mkdir_p( $this->cache_dir ) ) {
				return false;
			}
		}

		// Verify directory is writable.
		if ( ! is_writable( $this->cache_dir ) ) {
			return false;
		}

		return $this->cache_dir . '/' . md5( $key ) . '.php';
	}
}
