<?php
/**
 * SimplePie Sort on Jobbnorge Deadline
 *
 * @package wp-jobbnorge-block
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

			return $a_date >= $b_date;
		}
		/**
		 * Convert date to timestamp.
		 *
		 * @param string $date Date in Norwegian format.
		 * @return int
		 */
		private static function convert_date( string $date ) : int {

			if ( class_exists( '\IntlDateFormatter' ) ) {
				$format = 'd. MMM yyyy';// date format https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table.

				$formatter = \IntlDateFormatter::create( 'nb-NO', \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT, date_default_timezone_get() );
				$formatter->setPattern( $format ); // date format https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table.
				return $formatter->parse( $date );
			} else {
				/**
				 * Hacky way to get the timestamp from Norwegian deadline date.
				 */
				$str_months = [
					'januar',
					'februar',
					'mars',
					'april',
					'mai',
					'juni',
					'juli',
					'august',
					'september',
					'oktober',
					'november',
					'desember',
				];
				$num_months = [
					'01',
					'02',
					'03',
					'04',
					'05',
					'06',
					'07',
					'08',
					'09',
					'10',
					'11',
					'12',
				];

				$dato = preg_replace( '/(\d{1})\./', '$1', $date ); // remove . from day.
				$dato = preg_replace( '/(\d{1})\./', '0$1.', $dato ); // add 0 to day if needed.
				$dato = str_replace( $str_months, $num_months, $dato ); // replace month names with numbers.
				$dato = explode( ' ', $dato );// split into array.
				return mktime( 0, 0, 0, $dato[1], $dato[0], $dato[2] );// create timestamp.
			}
		}
	}
}
