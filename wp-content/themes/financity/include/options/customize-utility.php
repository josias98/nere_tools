<?php

	// use to clear an option for customize page
	if( !function_exists('financity_clear_option') ){
		function financity_clear_option(){
			$options = array('general', 'typography', 'color', 'plugin');

			foreach( $options as $option ){
				unset($GLOBALS['financity_' . $option]);
			}
			
		}
	}