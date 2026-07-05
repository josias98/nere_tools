<?php
	/*	
	*	Goodlayers Option
	*	---------------------------------------------------------------------
	*	This file store an array of theme options
	*	---------------------------------------------------------------------
	*/	

	// save the css/js file 
	add_action('gdlr_core_after_save_theme_option', 'financity_gdlr_core_after_save_theme_option');
	if( !function_exists('financity_gdlr_core_after_save_theme_option') ){
		function financity_gdlr_core_after_save_theme_option(){
			if( function_exists('gdlr_core_generate_combine_script') ){
				financity_clear_option();

				gdlr_core_generate_combine_script(array(
					'lightbox' => financity_gdlr_core_lightbox_type()
				));
			}
		}
	}

	if( !function_exists('financity_gdlr_core_get_privacy_options') ){
		function financity_gdlr_core_get_privacy_options( $type = 1 ){
			if( function_exists('gdlr_core_get_privacy_options') ){
				return gdlr_core_get_privacy_options( $type );
			}

			return array();
		} // financity_gdlr_core_get_privacy_options
	}	

	// add the option
	$financity_admin_option->add_element(array(
	
		// plugin head section
		'title' => esc_html__('Miscellaneous', 'financity'),
		'slug' => 'financity_plugin',
		'icon' => get_template_directory_uri() . '/include/options/images/plugin.png',
		'options' => array(
		
			// starting the subnav
			'thumbnail-sizing' => array(
				'title' => esc_html__('Thumbnail Sizing', 'financity'),
				'customizer' => false,
				'options' => array(
				
					'enable-srcset' => array(
						'title' => esc_html__('Enable Srcset', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable',
						'description' => esc_html__('Enable this option will improve the performance by resizing the image based on the screensize. Please be cautious that this will generate multiple images on your server.', 'financity')
					),
					'thumbnail-sizing' => array(
						'title' => esc_html__('Add Thumbnail Size', 'financity'),
						'type' => 'custom',
						'item-type' => 'thumbnail-sizing',
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					
				) // thumbnail-sizing-options
			), // thumbnail-sizing-nav

			'consent-settings' => array(
				'title' => esc_html__('Consent Settings', 'financity'),
				'options' => financity_gdlr_core_get_privacy_options(1)
			),
			'privacy-settings' => array(
				'title' => esc_html__('Privacy Style Settings', 'financity'),
				'options' => financity_gdlr_core_get_privacy_options(2)
			),

			'plugins' => array(
				'title' => esc_html__('Plugins', 'financity'),
				'options' => array(

					'font-icon' => array(
						'title' => esc_html__('Icon Type', 'financity'),
						'type' => 'multi-combobox',
						'options' => (function_exists('gdlr_core_get_icon_font_title')? gdlr_core_get_icon_font_title(): array()),
						'description' => esc_html__('You can use Ctrl/Command button to select multiple items or remove the selected item. Leave this field blank to select all items in the list.', 'financity'),
						'default' => array('font-awesome', 'elegant-font')
					),
					'lightbox' => array(
						'title' => esc_html__('Lightbox Type', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'ilightbox' => esc_html__('ilightbox', 'financity'),
							'lightGallery' => esc_html__('LightGallery', 'financity'),
							'strip' => esc_html__('Strip', 'financity'),
						)
					),
					'ilightbox-skin' => array(
						'title' => esc_html__('iLightbox Skin', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'dark' => esc_html__('Dark', 'financity'),
							'light' => esc_html__('Light', 'financity'),
							'mac' => esc_html__('Mac', 'financity'),
							'metro-black' => esc_html__('Metro Black', 'financity'),
							'metro-white' => esc_html__('Metro White', 'financity'),
							'parade' => esc_html__('Parade', 'financity'),
							'smooth' => esc_html__('Smooth', 'financity'),		
						),
						'condition' => array( 'lightbox' => 'ilightbox' )
					),
					'link-to-lightbox' => array(
						'title' => esc_html__('Turn Image Link To Open In Lightbox', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),
					
				) // plugin-options
			), // plugin-nav		
			'additional-script' => array(
				'title' => esc_html__('Custom Css/Js', 'financity'),
				'options' => array(
				
					'additional-css' => array(
						'title' => esc_html__('Additional CSS ( without <style> tag )', 'financity'),
						'type' => 'textarea',
						'data-type' => 'text',
						'selector' => '#gdlr#',
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'additional-mobile-css' => array(
						'title' => esc_html__('Mobile CSS ( screen below 767px )', 'financity'),
						'type' => 'textarea',
						'data-type' => 'text',
						'selector' => '@media only screen and (max-width: 767px){ #gdlr# }',
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'additional-head-script' => array(
						'title' => esc_html__('Additional Head Script ( without <script> tag )', 'financity'),
						'type' => 'textarea',
						'wrapper-class' => 'gdlr-core-fullsize',
						'descriptin' => esc_html__('Eg. For analytics', 'financity')
					),
					'additional-head-script2' => array(
						'title' => esc_html__('Additional Head Script ( with <script> tag )', 'financity'),
						'type' => 'textarea',
						'wrapper-class' => 'gdlr-core-fullsize',
						'descriptin' => esc_html__('Eg. For analytics', 'financity')
					),
					'additional-script' => array(
						'title' => esc_html__('Additional Script ( without <script> tag )', 'financity'),
						'type' => 'textarea',
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					
				) // additional-script-options
			), // additional-script-nav	
			'maintenance' => array(
				'title' => esc_html__('Maintenance Mode', 'financity'),
				'options' => array(		
					'enable-maintenance' => array(
						'title' => esc_html__('Enable Maintenance / Coming Soon Mode', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable'
					),					
					'maintenance-page' => array(
						'title' => esc_html__('Select Maintenance / Coming Soon Page', 'financity'),
						'type' => 'combobox',
						'options' => 'post_type',
						'options-data' => 'page'
					),

				) // maintenance-options
			), // maintenance
			'pre-load' => array(
				'title' => esc_html__('Preload', 'financity'),
				'options' => array(		
					'enable-preload' => array(
						'title' => esc_html__('Enable Preload', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable'
					),
					'preload-image' => array(
						'title' => esc_html__('Preload Image', 'financity'),
						'type' => 'upload',
						'data-type' => 'file', 
						'selector' => '.financity-page-preload{ background-image: url(#gdlr#); }',
						'condition' => array( 'enable-preload' => 'enable' ),
						'description' => esc_html__('Upload the image (.gif) you want to use as preload animation. You could search it online at https://www.google.com/search?q=loading+gif as well', 'financity')
					),
				)
			),
			'import-export' => array(
				'title' => esc_html__('Import / Export', 'financity'),
				'options' => array(

					'export' => array(
						'title' => esc_html__('Export Option', 'financity'),
						'type' => 'export',
						'action' => 'gdlr_core_theme_option_export',
						'options' => array(
							'all' => esc_html__('All Options(general/typography/color/miscellaneous) exclude widget, custom template', 'financity'),
							'financity_general' => esc_html__('General Option', 'financity'),
							'financity_typography' => esc_html__('Typography Option', 'financity'),
							'financity_color' => esc_html__('Color Option', 'financity'),
							'financity_plugin' => esc_html__('Miscellaneous', 'financity'),
							'widget' => esc_html__('Widget', 'financity'),
							'page-builder-template' => esc_html__('Custom Page Builder Template', 'financity'),
						),
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'import' => array(
						'title' => esc_html__('Import Option', 'financity'),
						'type' => 'import',
						'action' => 'gdlr_core_theme_option_import',
						'wrapper-class' => 'gdlr-core-fullsize'
					),

				) // import-options
			), // import-export
			
		
		) // plugin-options
		
	), 8);	