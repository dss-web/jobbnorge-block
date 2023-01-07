<?php
/**
 * Jobbnorge Item.
 *
 * @package dss-jobbnorge-block
 * @author Per Søderlind / DSS
 * @since 29/09/2019
 */

declare( strict_types = 1 );
require_once ABSPATH . WPINC . '/class-simplepie.php';

defined( 'SIMPLE_NAMESPACE_JOBBNORGE' ) || define( 'SIMPLE_NAMESPACE_JOBBNORGE', 'https://export.jobbnorge.no/xml/' );

if ( ! class_exists( 'Jobbnorge_Item' ) ) {

	/**
	 * Use this class to extend SimplePie_Item with custom methods.
	 */
	class Jobbnorge_Item extends \SimplePie_Item {

		/**
		 * Get the jobbnorge item createdon date.
		 *
		 * @return string
		 */
		public function get_jn_createdon() : string {
			$data = $this->get_item_tags( SIMPLE_NAMESPACE_JOBBNORGE, 'createdon' );
			return $data[0]['data'] ?? '';
		}

		/**
		 * Get the jobbnorge item deadline date.
		 *
		 * @return string
		 */
		public function get_jn_deadline() : string {
			$data = $this->get_item_tags( SIMPLE_NAMESPACE_JOBBNORGE, 'deadline' );
			return $data[0]['data'] ?? '';
		}

		/**
		 * Get the jobbnorge item title.
		 *
		 * @return string
		 */
		public function get_jn_title() : string {
			$data = $this->get_item_tags( SIMPLE_NAMESPACE_JOBBNORGE, 'title' );
			return $data[0]['data'] ?? '';
		}

		/**
		 * Get the jobbnorge item postion title.
		 *
		 * @return string
		 */
		public function get_jn_positiontitle() : string {
			$data = $this->get_item_tags( SIMPLE_NAMESPACE_JOBBNORGE, 'positiontitle' );
			return $data[0]['data'] ?? '';
		}

		/**
		 * Get the jobbnorge item employer name.
		 *
		 * @return string
		 */
		public function get_jn_employername() : string {
			$data = $this->get_item_tags( SIMPLE_NAMESPACE_JOBBNORGE, 'employername' );
			return $data[0]['data'] ?? '';
		}

		/**
		 * Get the jobbnorge item department name.
		 *
		 * @return string
		 */
		public function get_jn_departmentname() : string {
			$data = $this->get_item_tags( SIMPLE_NAMESPACE_JOBBNORGE, 'departmentname' );
			return $data[0]['data'] ?? '';
		}

		/**
		 * Get the jobbnorge item lead text.
		 *
		 * @return string
		 */
		public function get_jn_leadtext() : string {
			$data = $this->get_item_tags( SIMPLE_NAMESPACE_JOBBNORGE, 'leadtext' );
			return $data[0]['data'] ?? '';
		}

		/**
		 * Get the jobbnorge item location.
		 *
		 * @return string
		 */
		public function get_jn_location() : string {
			$data = $this->get_item_tags( SIMPLE_NAMESPACE_JOBBNORGE, 'location' );
			return $data[0]['data'] ?? '';
		}

		/**
		 * Get the jobbnorge item text.
		 *
		 * @return string
		 */
		public function get_jn_text() : string {
			$data = $this->get_item_tags( SIMPLE_NAMESPACE_JOBBNORGE, 'text' );
			return $data[0]['data'] ?? '';
		}

		/**
		 * Get the jobbnorge item reference code.
		 *
		 * @return string
		 */
		public function get_jn_refcode() : string {
			$data = $this->get_item_tags( SIMPLE_NAMESPACE_JOBBNORGE, 'refcode' );
			return $data[0]['data'] ?? '';
		}

		/**
		 * Get the jobbnorge item jobe scope.
		 *
		 * @return string
		 */
		public function get_jn_jobscope() : string {
			$data = $this->get_item_tags( SIMPLE_NAMESPACE_JOBBNORGE, 'jobscope' );
			return $data[0]['data'] ?? '';
		}

		/**
		 * Get the jobbnorge item job duration.
		 *
		 * @return string
		 */
		public function get_jn_jobduration() : string {
			$data = $this->get_item_tags( SIMPLE_NAMESPACE_JOBBNORGE, 'jobduration' );
			return $data[0]['data'] ?? '';
		}
	}
}
