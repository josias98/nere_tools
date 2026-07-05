<?php

if ( !class_exists('WPSynchroPuc_v4p9_DebugBar_ThemePanel', false) ):

	class WPSynchroPuc_v4p9_DebugBar_ThemePanel extends WPSynchroPuc_v4p9_DebugBar_Panel {
		/**
		 * @var WPSynchroPuc_v4p9_Theme_UpdateChecker
		 */
		protected $updateChecker;

		protected function displayConfigHeader() {
			$this->row('Theme directory', htmlentities($this->updateChecker->directoryName));
			parent::displayConfigHeader();
		}

		protected function getUpdateFields() {
			return array_merge(parent::getUpdateFields(), array('details_url'));
		}
	}

endif;
