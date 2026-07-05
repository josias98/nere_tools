<?php
	/*	
	*	Goodlayers Function File
	*	---------------------------------------------------------------------
	*	This file include all of important function and features of the theme
	*	---------------------------------------------------------------------
	*/
	
	// goodlayers core plugin function
	include_once(get_template_directory() . '/admin/core/sidebar-generator.php');
	include_once(get_template_directory() . '/admin/core/utility.php');
	include_once(get_template_directory() . '/admin/core/media.php' );
	
	// create admin page
	if( is_admin() ){
		include_once(get_template_directory() . '/admin/tgmpa/class-tgm-plugin-activation.php');
		include_once(get_template_directory() . '/admin/tgmpa/plugin-activation.php');
		include_once(get_template_directory() . '/admin/function/getting-start.php');	
	}
	
	// plugins
	include_once(get_template_directory() . '/plugins/wpml.php');
	include_once(get_template_directory() . '/plugins/revslider.php');
	
	/////////////////////
	// front end script
	/////////////////////
	
	include_once(get_template_directory() . '/include/utility.php' );
	include_once(get_template_directory() . '/include/function-regist.php' );
	include_once(get_template_directory() . '/include/navigation-menu.php' );
	include_once(get_template_directory() . '/include/include-script.php' );
	include_once(get_template_directory() . '/include/goodlayers-core-filter.php' );
	include_once(get_template_directory() . '/include/maintenance.php' );
	include_once(get_template_directory() . '/woocommerce/woocommerce-settings.php' );

	include_once(get_template_directory() . '/include/pb-element-testimonial.php' );
	
	/////////////////////
	// execute module 
	/////////////////////
	
	// initiate sidebar structure
	new gdlr_core_sidebar_generator( array(
		'before_widget' => '<div id="%1$s" class="widget %2$s financity-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="financity-widget-title">',
		'after_title'   => '</h3>' ) );
	
	// remove the core default action to enqueue the theme script
	remove_action('after_setup_theme', 'gdlr_init_goodlayers_core_elements');
	add_action('after_setup_theme', 'financity_init_goodlayers_core_elements');
	if( !function_exists('financity_init_goodlayers_core_elements') ){
		function financity_init_goodlayers_core_elements(){

			// create an admin option and customizer
			if( (is_admin() || is_customize_preview()) && class_exists('gdlr_core_admin_option') && class_exists('gdlr_core_theme_customizer') ){
				
				$financity_admin_option = new gdlr_core_admin_option(array(
					'filewrite' => financity_get_style_custom(true)
				));	
				
				include_once( get_template_directory() . '/include/options/general.php');
				include_once( get_template_directory() . '/include/options/typography.php');
				include_once( get_template_directory() . '/include/options/color.php');
				include_once( get_template_directory() . '/include/options/plugin-settings.php');
				include_once( get_template_directory() . '/include/options/customize-utility.php');

				if( is_customize_preview() ){
					new gdlr_core_theme_customizer($financity_admin_option);
				}

				// clear an option for customize page
				add_action('wp', 'financity_clear_option');
				
			}
			
			// add the script for page builder / page options / post option
			if( is_admin() ){
				
				if( class_exists('gdlr_core_revision') ){
					$revision_num = 5;
					new gdlr_core_revision($revision_num);
				}
				
				// create page option
				if( class_exists('gdlr_core_page_option') ){

					// for page post type
					new gdlr_core_page_option(array(
						'post_type' => array('page'),
						'options' => array(
							'layout' => array(
								'title' => esc_html__('Layout', 'financity'),
								'options' => array(
									'enable-header-area' => array(
										'title' => esc_html__('Enable Header Area', 'financity'),
										'type' => 'checkbox',
										'default' => 'enable'
									),
									'enable-page-title' => array(
										'title' => esc_html__('Enable Page Title', 'financity'),
										'type' => 'checkbox',
										'default' => 'enable',
										'condition' => array( 'enable-header-area' => 'enable' )
									),
									'page-caption' => array(
										'title' => esc_html__('Caption', 'financity'),
										'type' => 'textarea',
										'condition' => array( 'enable-header-area' => 'enable', 'enable-page-title' => 'enable' )
									),					
									'title-background' => array(
										'title' => esc_html__('Page Title Background', 'financity'),
										'type' => 'upload',
										'condition' => array( 'enable-header-area' => 'enable', 'enable-page-title' => 'enable' )
									),
									'enable-breadcrumbs' => array(
										'title' => esc_html__('Enable Breadcrumbs ( Breadcrumb NavXT Plugin )', 'financity'),
										'type' => 'combobox',
										'options' => array(
											'default' => esc_html__('Default', 'financity'),
											'enable' => esc_html__('Enable', 'financity'),
											'disable' => esc_html__('Disable', 'financity'),
										),
										'default' => 'default',
										'condition' => array( 'enable-header-area' => 'enable', 'enable-page-title' => 'enable' )
									),
									'body-background-type' => array(
										'title' => esc_html__('Body Background Type', 'financity'),
										'type' => 'combobox',
										'options' => array(
											'default' => esc_html__('Default', 'financity'),
											'image' => esc_html__('Image ( Only For Boxed Layout )', 'financity'),
										)
									),
									'body-background-image' => array(
										'title' => esc_html__('Body Background Image', 'financity'),
										'type' => 'upload',
										'data-type' => 'file', 
										'condition' => array( 'body-background-type' => 'image' )
									),
									'body-background-image-opacity' => array(
										'title' => esc_html__('Body Background Image Opacity', 'financity'),
										'type' => 'fontslider',
										'data-type' => 'opacity',
										'default' => '100',
										'condition' => array( 'body-background-type' => 'image' )
									),
									'show-content' => array(
										'title' => esc_html__('Show WordPress Editor Content', 'financity'),
										'type' => 'checkbox',
										'default' => 'enable',
										'description' => esc_html__('Disable this to hide the content in editor to show only page builder content.', 'financity'),
									),
									'sidebar' => array(
										'title' => esc_html__('Sidebar', 'financity'),
										'type' => 'radioimage',
										'options' => 'sidebar',
										'default' => 'none',
										'wrapper-class' => 'gdlr-core-fullsize'
									),
									'sidebar-left' => array(
										'title' => esc_html__('Sidebar Left', 'financity'),
										'type' => 'combobox',
										'options' => 'sidebar',
										'condition' => array( 'sidebar' => array('left', 'both') )
									),
									'sidebar-right' => array(
										'title' => esc_html__('Sidebar Right', 'financity'),
										'type' => 'combobox',
										'options' => 'sidebar',
										'condition' => array( 'sidebar' => array('right', 'both') )
									),
									'enable-footer' => array(
										'title' => esc_html__('Enable Footer', 'financity'),
										'type' => 'combobox',
										'options' => array(
											'default' => esc_html__('Default', 'financity'),
											'enable' => esc_html__('Enable', 'financity'),
											'disable' => esc_html__('Disable', 'financity'),
										)
									),
									'enable-copyright' => array(
										'title' => esc_html__('Enable Copyright', 'financity'),
										'type' => 'combobox',
										'options' => array(
											'default' => esc_html__('Default', 'financity'),
											'enable' => esc_html__('Enable', 'financity'),
											'disable' => esc_html__('Disable', 'financity'),
										)
									),

								)
							), // layout
							'title' => array(
								'title' => esc_html__('Title Style', 'financity'),
								'options' => array(

									'title-style' => array(
										'title' => esc_html__('Page Title Style', 'financity'),
										'type' => 'combobox',
										'options' => array(
											'default' => esc_html__('Default', 'financity'),
											'small' => esc_html__('Small', 'financity'),
											'medium' => esc_html__('Medium', 'financity'),
											'large' => esc_html__('Large', 'financity'),
											'custom' => esc_html__('Custom', 'financity'),
										),
										'default' => 'default'
									),
									'title-align' => array(
										'title' => esc_html__('Page Title Alignment', 'financity'),
										'type' => 'radioimage',
										'options' => 'text-align',
										'with-default' => true,
										'default' => 'default'
									),
									'title-spacing' => array(
										'title' => esc_html__('Page Title Padding', 'financity'),
										'type' => 'custom',
										'item-type' => 'padding',
										'data-input-type' => 'pixel',
										'options' => array('padding-top', 'padding-bottom', 'caption-top-margin'),
										'wrapper-class' => 'gdlr-core-fullsize gdlr-core-no-link gdlr-core-large',
										'condition' => array( 'title-style' => 'custom' )
									),
									'title-font-size' => array(
										'title' => esc_html__('Page Title Font Size', 'financity'),
										'type' => 'custom',
										'item-type' => 'padding',
										'data-input-type' => 'pixel',
										'options' => array('title-size', 'title-letter-spacing', 'caption-size', 'caption-letter-spacing'),
										'wrapper-class' => 'gdlr-core-fullsize gdlr-core-no-link gdlr-core-large',
										'condition' => array( 'title-style' => 'custom' )
									),
									'title-font-weight' => array(
										'title' => esc_html__('Page Title Font Weight', 'financity'),
										'type' => 'custom',
										'item-type' => 'padding',
										'options' => array('title-weight', 'caption-weight'),
										'wrapper-class' => 'gdlr-core-fullsize gdlr-core-no-link gdlr-core-large',
										'condition' => array( 'title-style' => 'custom' )
									),
									'title-font-transform' => array(
										'title' => esc_html__('Page Title Font Transform', 'financity'),
										'type' => 'combobox',
										'options' => array(
											'none' => esc_html__('None', 'financity'),
											'uppercase' => esc_html__('Uppercase', 'financity'),
											'lowercase' => esc_html__('Lowercase', 'financity'),
											'capitalize' => esc_html__('Capitalize', 'financity'),
										),
										'default' => 'uppercase',
										'condition' => array( 'title-style' => 'custom' )
									),
									'title-background-overlay-opacity' => array(
										'title' => esc_html__('Page Title Background Overlay Opacity', 'financity'),
										'type' => 'text',
										'description' => esc_html__('Fill the number between 0 - 1 ( Leave Blank For Default Value )', 'financity'),
										'condition' => array( 'title-style' => 'custom' )
									),
									'title-color' => array(
										'title' => esc_html__('Page Title Color', 'financity'),
										'type' => 'colorpicker',
									),
									'caption-color' => array(
										'title' => esc_html__('Page Caption Color', 'financity'),
										'type' => 'colorpicker',
									),
									'title-background-overlay-color' => array(
										'title' => esc_html__('Page Background Overlay Color', 'financity'),
										'type' => 'colorpicker',
									),

								)
							), // title
							'header' => array(
								'title' => esc_html__('Header', 'financity'),
								'options' => array(

									'header-slider' => array(
										'title' => esc_html__('Header Slider ( Above Navigation )', 'financity'),
										'type' => 'combobox',
										'options' => array(
											'none' => esc_html__('None', 'financity'),
											'layer-slider' => esc_html__('Layer Slider', 'financity'),
											'master-slider' => esc_html__('Master Slider', 'financity'),
											'revolution-slider' => esc_html__('Revolution Slider', 'financity'),
										),
										'description' => esc_html__('For header style plain / bar / boxed', 'financity'),
									),
									'layer-slider-id' => array(
										'title' => esc_html__('Choose Layer Slider', 'financity'),
										'type' => 'combobox',
										'options' => gdlr_core_get_layerslider_list(),
										'condition' => array( 'header-slider' => 'layer-slider' )
									),
									'master-slider-id' => array(
										'title' => esc_html__('Choose Master Slider', 'financity'),
										'type' => 'combobox',
										'options' => gdlr_core_get_masterslider_list(),
										'condition' => array( 'header-slider' => 'master-slider' )
									),
									'revolution-slider-id' => array(
										'title' => esc_html__('Choose Revolution Slider', 'financity'),
										'type' => 'combobox',
										'options' => gdlr_core_get_revolution_slider_list(),
										'condition' => array( 'header-slider' => 'revolution-slider' )
									),

								) // header options
							), // header
							'bullet-anchor' => array(
								'title' => esc_html__('Bullet Anchor', 'financity'),
								'options' => array(
									'bullet-anchor-description' => array(
										'type' => 'description',
										'description' => esc_html__('This feature is used for one page navigation. It will appear on the right side of page. You can put the id of element in \'Anchor Link\' field to let the bullet scroll the page to.', 'financity')
									),
									'bullet-anchor' => array(
										'title' => esc_html__('Add Anchor', 'financity'),
										'type' => 'custom',
										'item-type' => 'tabs',
										'options' => array(
											'title' => array(
												'title' => esc_html__('Anchor Link', 'financity'),
												'type' => 'text',
											),
											'anchor-color' => array(
												'title' => esc_html__('Anchor Color', 'financity'),
												'type' => 'colorpicker',
											),
											'anchor-hover-color' => array(
												'title' => esc_html__('Anchor Hover Color', 'financity'),
												'type' => 'colorpicker',
											)
										),
										'wrapper-class' => 'gdlr-core-fullsize'
									),
								)
							)
						)
					));

					// for post post type
					new gdlr_core_page_option(array(
						'post_type' => array('post'),
						'options' => array(
							'layout' => array(
								'title' => esc_html__('Layout', 'financity'),
								'options' => array(

									'show-content' => array(
										'title' => esc_html__('Show WordPress Editor Content', 'financity'),
										'type' => 'checkbox',
										'default' => 'enable',
										'description' => esc_html__('Disable this to hide the content in editor to show only page builder content.', 'financity'),
									),
									'sidebar' => array(
										'title' => esc_html__('Sidebar', 'financity'),
										'type' => 'radioimage',
										'options' => 'sidebar',
										'with-default' => true,
										'default' => 'default',
										'wrapper-class' => 'gdlr-core-fullsize'
									),
									'sidebar-left' => array(
										'title' => esc_html__('Sidebar Left', 'financity'),
										'type' => 'combobox',
										'options' => 'sidebar',
										'condition' => array( 'sidebar' => array('left', 'both') )
									),
									'sidebar-right' => array(
										'title' => esc_html__('Sidebar Right', 'financity'),
										'type' => 'combobox',
										'options' => 'sidebar',
										'condition' => array( 'sidebar' => array('right', 'both') )
									),
								)
							),
							'metro-layout' => array(
								'title' => esc_html__('Metro Layout', 'financity'),
								'options' => array(
									'metro-column-size' => array(
										'title' => esc_html__('Column Size', 'financity'),
										'type' => 'combobox',
										'options' => array( 'default'=> esc_html__('Default', 'financity'), 
											60 => '1/1', 30 => '1/2', 20 => '1/3', 40 => '2/3', 
											15 => '1/4', 45 => '3/4', 12 => '1/5', 24 => '2/5', 36 => '3/5', 48 => '4/5',
											10 => '1/6', 50 => '5/6'),
										'default' => 'default',
										'description' => esc_html__('Choosing default will display the value selected by the page builder item.', 'financity')
									),
									'metro-thumbnail-size' => array(
										'title' => esc_html__('Thumbnail Size', 'financity'),
										'type' => 'combobox',
										'options' => 'thumbnail-size',
										'with-default' => true,
										'default' => 'default',
										'description' => esc_html__('Choosing default will display the value selected by the page builder item.', 'financity')
									)
								)
							),						
							'title' => array(
								'title' => esc_html__('Title', 'financity'),
								'options' => array(

									'blog-title-style' => array(
										'title' => esc_html__('Blog Title Style', 'financity'),
										'type' => 'combobox',
										'options' => array(
											'default' => esc_html__('Default', 'financity'),
											'small' => esc_html__('Small', 'financity'),
											'large' => esc_html__('Large', 'financity'),
											'custom' => esc_html__('Custom', 'financity'),
											'inside-content' => esc_html__('Inside Content', 'financity'),
											'none' => esc_html__('None', 'financity'),
										),
										'default' => 'default'
									),
									'blog-title-padding' => array(
										'title' => esc_html__('Blog Title Padding', 'financity'),
										'type' => 'custom',
										'item-type' => 'padding',
										'data-input-type' => 'pixel',
										'options' => array('padding-top', 'padding-bottom'),
										'wrapper-class' => 'gdlr-core-fullsize gdlr-core-no-link gdlr-core-large',
										'condition' => array( 'blog-title-style' => 'custom' )
									),
									'blog-feature-image' => array(
										'title' => esc_html__('Blog Feature Image Location', 'financity'),
										'type' => 'combobox',
										'options' => array(
											'default' => esc_html__('Default', 'financity'),
											'content' => esc_html__('Inside Content', 'financity'),
											'title-background' => esc_html__('Title Background', 'financity'),
											'none' => esc_html__('None', 'financity'),
										)
									),
									'blog-title-background-image' => array(
										'title' => esc_html__('Blog Title Background Image', 'financity'),
										'type' => 'upload',
										'data-type' => 'file',
										'condition' => array( 
											'blog-title-style' => array('default', 'small', 'large', 'custom'),
											'blog-feature-image' => array('default', 'content', 'none')
										),
										'description' => esc_html__('Will be overridden by feature image if selected.', 'financity'),
									),
									'blog-title-background-overlay-opacity' => array(
										'title' => esc_html__('Blog Title Background Overlay Opacity', 'financity'),
										'type' => 'text',
										'description' => esc_html__('Fill the number between 0 - 1 ( Leave Blank For Default Value )', 'financity'),
										'condition' => array( 'blog-title-style' => 'custom' ),
									),
									'enable-breadcrumbs' => array(
										'title' => esc_html__('Enable Breadcrumbs ( Breadcrumb NavXT Plugin )', 'financity'),
										'type' => 'combobox',
										'options' => array(
											'default' => esc_html__('Default', 'financity'),
											'enable' => esc_html__('Enable', 'financity'),
											'disable' => esc_html__('Disable', 'financity'),
										),
										'default' => 'default',
										'condition' => array( 'blog-title-style' => array('default', 'small', 'large', 'custom') ),
									),
									
								) // options
							) // title
						)
					));
				}

			}
			
			// create page builder
			if( class_exists('gdlr_core_page_builder') ){
				new gdlr_core_page_builder(array(
					'style' => array(
						'style-custom' => financity_get_style_custom()
					)
				));
			}
			
		} // financity_init_goodlayers_core_elements
	} // function_exists
