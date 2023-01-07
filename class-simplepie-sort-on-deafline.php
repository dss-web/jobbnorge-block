<?php
/**
 * SimplePie Sort on Jobbnorge Deadline
 *
 * @package dss-jobbnorge-block
 */

if ( ! class_exists( 'SimplePieSortOnDeadline' ) && defined( 'SIMPLE_NAMESPACE_JOBBNORGE' ) ) {
	/**
	 * Override the sort_items method.
	 *
	 * @link https://wordpress.stackexchange.com/a/87099/14546
	 */
	class SimplePieSortOnDeadline extends \SimplePie {
		/**
		 * Sort on deadline, closest first.
		 *
		 * @param \SimplePie $a Jobbnorge item.
		 * @param \SimplePie $b Jobbnorge item.
		 * @return boolean
		 */
		public static function sort_items( $a, $b ) {
			$a_date = self::convert_date( $a->get_item_tags( SIMPLE_NAMESPACE_JOBBNORGE, 'deadline' )[0]['data'] ) ?? '';
			$b_date = self::convert_date( $b->get_item_tags( SIMPLE_NAMESPACE_JOBBNORGE, 'deadline' )[0]['data'] ) ?? '';

			return $a_date < $b_date;
		}
		/**
		 * Convert date to timestamp.
		 *
		 * @param string $date Date in Norwegian format.
		 * @return int
		 */
		private static function convert_date( string $date ) : int {
			$format = 'd. MMM yyyy';// date format https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table.

			$formatter = \IntlDateFormatter::create( 'nb-NO', \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT, date_default_timezone_get() );
			return $formatter->setPattern( 'd. MMM yyyy' );
		}
	}
}
