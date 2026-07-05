<?php
if ( !class_exists('WPSynchroPuc_v4p9_Update', false) ):

	/**
	 * A simple container class for holding information about an available update.
	 *
	 * @author Janis Elsts
	 * @access public
	 */
	abstract class WPSynchroPuc_v4p9_Update extends WPSynchroPuc_v4p9_Metadata {
		public $slug;
		public $version;
		public $download_url;
		public $translations = [];

		/**
		 * @return string[]
		 */
		protected function getFieldNames() {
			return array('slug', 'version', 'download_url', 'translations');
		}

		public function toWpFormat() {
			$update = new stdClass();

			$update->slug = $this->slug;
			$update->new_version = $this->version;
			$update->package = $this->download_url;

			return $update;
		}
	}

endif;
