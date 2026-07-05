<?php
if ( !class_exists('WPSynchroPuc_v4p9_DebugBar_PluginExtension', false) ):

	class WPSynchroPuc_v4p9_DebugBar_PluginExtension extends WPSynchroPuc_v4p9_DebugBar_Extension {
		/** @var WPSynchroPuc_v4p9_Plugin_UpdateChecker */
		protected $updateChecker;

		public function __construct($updateChecker) {
			parent::__construct($updateChecker, 'WPSynchroPuc_v4p9_DebugBar_PluginPanel');

			add_action('wp_ajax_WPSynchroPuc_v4_debug_request_info', array($this, 'ajaxRequestInfo'));
		}

		/**
		 * Request plugin info and output it.
		 */
		public function ajaxRequestInfo() {
			if ( $_POST['uid'] !== $this->updateChecker->getUniqueName('uid') ) {
				return;
			}
			$this->preAjaxRequest();
			$info = $this->updateChecker->requestInfo();
			if ( $info !== null ) {
				echo 'Successfully retrieved plugin info from the metadata URL:';
				echo '<pre>', htmlentities(print_r($info, true)), '</pre>';
			} else {
				echo 'Failed to retrieve plugin info from the metadata URL.';
			}
			exit;
		}
	}

endif;
