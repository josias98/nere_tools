<?php
	/*
	Plugin Name: Goodlayers Portfolio Post Type
	Plugin URI: 
	Description: A custom post type plugin to use with "Goodlayers Core" plugin
	Version: 1.3.3
	Author: Goodlayers
	Author URI: http://www.goodlayers.com
	License: 
	*/

	include(dirname(__FILE__) . '/portfolio-util.php');
	include(dirname(__FILE__) . '/portfolio-item.php');
	include(dirname(__FILE__) . '/portfolio-style.php');
	include(dirname(__FILE__) . '/pb-element-portfolio.php');
	include(dirname(__FILE__) . '/recent-portfolio-widget.php');
	include(dirname(__FILE__) . '/portfolio-slider-widget.php');

	// create post type
	add_action('init', 'gdlr_core_portfolio_init');
	if( !function_exists('gdlr_core_portfolio_init') ){
		function gdlr_core_portfolio_init() {
			
			// custom post type
			$slug = apply_filters('gdlr_core_custom_post_slug', 'portfolio', 'portfolio');
			$supports = apply_filters('gdlr_core_custom_post_support', array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions', 'custom-fields'), 'portfolio');
			
			$labels = array(
				'name'               => esc_html__('Portfolio', 'goodlayers-core-portfolio'),
				'singular_name'      => esc_html__('Portfolio', 'goodlayers-core-portfolio'),
				'menu_name'          => esc_html__('Portfolio', 'goodlayers-core-portfolio'),
				'name_admin_bar'     => esc_html__('Portfolio', 'goodlayers-core-portfolio'),
				'add_new'            => esc_html__('Add New', 'goodlayers-core-portfolio'),
				'add_new_item'       => esc_html__('Add New Portfolio', 'goodlayers-core-portfolio'),
				'new_item'           => esc_html__('New Portfolio', 'goodlayers-core-portfolio'),
				'edit_item'          => esc_html__('Edit Portfolio', 'goodlayers-core-portfolio'),
				'view_item'          => esc_html__('View Portfolio', 'goodlayers-core-portfolio'),
				'all_items'          => esc_html__('All Portfolio', 'goodlayers-core-portfolio'),
				'search_items'       => esc_html__('Search Portfolio', 'goodlayers-core-portfolio'),
				'parent_item_colon'  => esc_html__('Parent Portfolio:', 'goodlayers-core-portfolio'),
				'not_found'          => esc_html__('No portfolio found.', 'goodlayers-core-portfolio'),
				'not_found_in_trash' => esc_html__('No portfolio found in Trash.', 'goodlayers-core-portfolio')
			);
			$args = array(
				'labels'             => $labels,
				'description'        => esc_html__('Description.', 'goodlayers-core-portfolio'),
				'public'             => true,
				'publicly_queryable' => true,
				'exclude_from_search'=> false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array('slug' => $slug),
				'capabilities'    	 => array(
			        'edit_post'          => 'edit_portfolio',
			        'read_post'          => 'read_portfolio',
			        'delete_post'        => 'delete_portfolio',
			        'edit_posts'         => 'edit_portfolios',
			        'edit_others_posts'  => 'edit_others_portfolios',
			        'publish_posts'      => 'publish_portfolios',
			        'read_private_posts' => 'read_private_portfolios',
			        'delete_posts'       => 'delete_portfolios',
			    ),
				'map_meta_cap'		 => false,
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => $supports
			);
			register_post_type('portfolio', $args);

			// custom taxonomy
			$slug = apply_filters('gdlr_core_custom_post_slug', 'portfolio_category', 'portfolio_category');
			$args = array(
				'hierarchical'      => true,
				'label'             => esc_html__('Portfolio Category', 'goodlayers-core-portfolio'),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array('slug' => $slug),
				'capabilities' 		=> array(
					'manage_terms' => 'edit_portfolio',
					'edit_terms' => 'edit_portfolio',
					'delete_terms' => 'delete_portfolio',
					'assign_terms' => 'edit_portfolio',
				)
			);
			register_taxonomy('portfolio_category', array('portfolio'), $args);
			register_taxonomy_for_object_type('portfolio_category', 'portfolio');

			$slug = apply_filters('gdlr_core_custom_post_slug', 'portfolio_tag', 'portfolio_tag');
			$args = array(
				'hierarchical'      => false,
				'label'             => esc_html__('Portfolio Tag', 'goodlayers-core-portfolio'),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array('slug' => $slug),
				'capabilities' 		=> array(
					'manage_terms' => 'edit_portfolio',
					'edit_terms' => 'edit_portfolio',
					'delete_terms' => 'delete_portfolio',
					'assign_terms' => 'edit_portfolio',
				)
			);
			register_taxonomy('portfolio_tag', array('portfolio'), $args);
			register_taxonomy_for_object_type('portfolio_tag', 'portfolio');
			
			// apply single template filter
			add_filter('single_template', 'gdlr_core_portfolio_template');
		}
	} // gdlr_core_personnel_init

	if( !function_exists('gdlr_core_portfolio_template') ){
		function gdlr_core_portfolio_template($template){

			if( get_post_type() == 'portfolio' ){
				$template = dirname(__FILE__) . '/single-portfolio.php';
			}

			return $template;
		}
	}

	// add capabilities on plugin activation
	register_activation_hook(__FILE__, 'gdlr_core_portfolio_init_admin_cap');
	if( !function_exists('gdlr_core_portfolio_init_admin_cap') ){
		function gdlr_core_portfolio_init_admin_cap( $post_type ){

			$post_type_cap = array('edit_%s', 'read_%s', 'delete_%s',  
				'edit_%ss', 'edit_others_%ss', 'publish_%ss', 'read_private_%ss', 'delete_%ss');

			$admin = get_role('administrator');
			foreach( $post_type_cap as $cap ){
				$admin->add_cap(str_replace('%s', 'portfolio', $cap));
			}

		} // gdlr_core_portfolio_init_admin_cap
	}

	// add page builder to portfolio
	if( is_admin() ){ add_filter('gdlr_core_page_builder_post_type', 'gdlr_core_portfolio_add_page_builder'); }
	if( !function_exists('gdlr_core_portfolio_add_page_builder') ){
		function gdlr_core_portfolio_add_page_builder( $post_type ){
			$post_type[] = 'portfolio';
			return $post_type;
		}
	}

	// inital page builder value
	if( is_admin() ){ add_filter('gdlr_core_portfolio_page_builder_val_init', 'gdlr_core_portfolio_page_builder_val_init'); }
	if( !function_exists('gdlr_core_portfolio_page_builder_val_init') ){
		function gdlr_core_portfolio_page_builder_val_init( $value ){
			$value = '[{"template":"wrapper","type":"background","value":{"id":"","content-layout":"boxed","max-width":"","hide-this-wrapper-in":"none","padding":{"top":"60px","right":"0px","bottom":"60px","left":"0px","settings":"unlink"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"animation":"none","animation-location":"0.8","background-type":"color","background-color":"","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","pattern-opacity":"1","parallax-speed":"0.8","skin":"","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid"},"items":[{"template":"wrapper","type":"column","column":"40","items":[{"template":"element","type":"image","value":{"id":"","image":"614","thumbnail-size":"full","link-to":"lb-full-image","custom-image":"","video-url":"","page-id":"599","custom-url":"","custom-link-target":"_self","enable-caption":"disable","enable-shadow":"disable","frame-style":"rectangle","border-radius":"3px","border-width":"0px","border-color":"","overlay-color":"","padding-bottom":"30px"}},{"template":"element","type":"image","value":{"id":"","image":"911","thumbnail-size":"full","link-to":"lb-full-image","custom-image":"","video-url":"","page-id":"599","custom-url":"","custom-link-target":"_self","enable-caption":"disable","max-width":"","alignment":"center","enable-shadow":"disable","frame-style":"rectangle","border-radius":"3px","border-width":"0px","border-color":"","overlay-color":"","padding-bottom":"30px"}},{"template":"element","type":"image","value":{"id":"","image":"622","thumbnail-size":"full","link-to":"lb-full-image","custom-image":"","video-url":"","page-id":"599","custom-url":"","custom-link-target":"_self","enable-caption":"disable","enable-shadow":"disable","frame-style":"rectangle","border-radius":"3px","border-width":"0px","border-color":"","overlay-color":"","padding-bottom":"30px"}},{"template":"element","type":"image","value":{"id":"","image":"613","thumbnail-size":"full","link-to":"lb-full-image","custom-image":"","video-url":"","page-id":"599","custom-url":"","custom-link-target":"_self","enable-caption":"disable","enable-shadow":"disable","frame-style":"rectangle","border-radius":"3px","border-width":"0px","border-color":"","overlay-color":"","padding-bottom":"30px"}}]},{"template":"wrapper","type":"column","column":"20","value":{"id":"","max-width":"","hide-this-wrapper-in":"none","padding":{"top":"10px","right":"0px","bottom":"0px","left":"20px","settings":"unlink"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"animation":"none","animation-location":"0.8","background-type":"color","background-color":"","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","pattern-opacity":"1","parallax-speed":"0.8","skin":"","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid"},"items":[{"template":"element","type":"port-info","value":{"id":"","port-info":[{"key":"Client","value":"The Car Rental Co"},{"key":"Skills","value":"Photography / Media Production"},{"key":"Website","value":"Goodlayers.com"}],"enable-post-type-tax":"disable","taxonomy-head-text":"Tags","taxonomy":"portfolio_tag","padding-bottom":"50px","enable-social-share":"enable","social-facebook":"enable","social-linkedin":"enable","social-google-plus":"enable","social-pinterest":"enable","social-stumbleupon":"enable","social-twitter":"enable","social-email":"disable"}},{"template":"element","type":"title","value":{"id":"","title":"Project Title","caption":"","caption-position":"top","title-link":"","title-link-target":"_self","text-align":"left","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h4","title-font-size":"22px","title-font-weight":"700","title-font-style":"normal","title-font-letter-spacing":"1px","title-font-uppercase":"disable","title-color":"","title-link-hover-color":"","caption-font-size":"16px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","caption-color":"","caption-spaces":"10px","padding-bottom":"30px"}},{"template":"element","type":"text-box","value":{"id":"","content":"Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove right at the coast of the Semantics, a large language ocean. A small river named Duden flows by their place and supplies it with the necessary regelialia. It is a paradisematic country, in which roasted parts of sentences fly into your mouth.","text-align":"left","font-size":"","padding-bottom":"30px"}}]}]}]';

			return json_decode($value, true);
		}
	}

	// create an option
	if( is_admin() ){ add_action('after_setup_theme', 'gdlr_core_portfolio_option_init'); }
	if( !function_exists('gdlr_core_portfolio_option_init') ){
		function gdlr_core_portfolio_option_init(){

			if( class_exists('gdlr_core_page_option') ){
				new gdlr_core_page_option(array(
					'post_type' => array('portfolio'),
					'options' => apply_filters('gdlr_core_portfolio_options', array(
						'general' => array(
							'title' => esc_html__('General', 'goodlayers-core-portfolio'),
							'options' => array(
								'enable-page-title' => array(
									'title' => esc_html__('Enable Page Title', 'goodlayers-core-portfolio'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'page-caption' => array(
									'title' => esc_html__('Caption', 'goodlayers-core-portfolio'),
									'type' => 'textarea',
									'condition' => array( 'enable-page-title' => 'enable' )
								),					
								'title-background' => array(
									'title' => esc_html__('Page Title Background', 'goodlayers-core-portfolio'),
									'type' => 'upload',
									'condition' => array( 'enable-page-title' => 'enable' )
								),				
								'show-content' => array(
									'title' => esc_html__('Show WordPress Editor Content', 'goodlayers-core-portfolio'),
									'type' => 'checkbox',
									'default' => 'enable',
									'description' => esc_html__('Disable this to hide the content in editor to show only page builder content.', 'goodlayers-core-portfolio'),
								),				
								
								'thumbnail-type' => array(
									'title' => esc_html__('Thumbnail Type', 'goodlayers-core-portfolio'),
									'type' => 'combobox',
									'options' => array(
										'feature-image' => esc_html__('Feature Image', 'goodlayers-core-portfolio'),
										'video' => esc_html__('Video', 'goodlayers-core-portfolio'),
										'slider' => esc_html__('Slider', 'goodlayers-core-portfolio'),
										'gallery' => esc_html__('Gallery ( Use feature image as thumbnail )', 'goodlayers-core-portfolio'),
									)
								),
								'title-link-to' => array(
									'title' => esc_html__('Title ( If Available ) Link To', 'goodlayers-core-portfolio'),
									'type' => 'combobox',
									'options' => array(
										'title' => esc_html__('Single Portfolio', 'goodlayers-core-portfolio'),
										'icon' => esc_html__('Same As Icon', 'goodlayers-core-portfolio'),
									),
									'condition' => array( 'thumbnail-type' => array('feature-image', 'gallery') )
								),
								'hover-icon-link-to' => array(
									'title' => esc_html__('Hover Icon Link To', 'goodlayers-core-portfolio'),
									'type' => 'combobox',
									'options' => array(
										'lb-full-image' => esc_html__('Lightbox with full image', 'goodlayers-core-portfolio'),
										'lb-custom-image' => esc_html__('Lightbox with custom image', 'goodlayers-core-portfolio'),
										'lb-video' => esc_html__('Video Lightbox', 'goodlayers-core-portfolio'),
										'custom-url' => esc_html__('Custom Url', 'goodlayers-core-portfolio'),
										'portfolio' => esc_html__('Single Portfolio', 'goodlayers-core-portfolio')
									),
									'condition' => array( 'thumbnail-type' => 'feature-image' )
								),
								'hover-icon-custom-image' => array(
									'title' => esc_html__('Upload Custom Image', 'goodlayers-core-portfolio'),
									'type' => 'upload',
									'condition' => array( 
										'thumbnail-type' => 'feature-image',
										'hover-icon-link-to' => 'lb-custom-image' 
									)
								),
								'hover-icon-video-url' => array(
									'title' => esc_html__('Video Url ( Youtube / Vimeo )', 'goodlayers-core-portfolio'),
									'type' => 'text',
									'condition' => array( 
										'thumbnail-type' => 'feature-image',
										'hover-icon-link-to' => 'lb-video'
									)
								),
								'hover-icon-custom-url' => array(
									'title' => esc_html__('Custom Url', 'goodlayers-core-portfolio'),
									'type' => 'text',
									'condition' => array( 
										'thumbnail-type' => 'feature-image',
										'hover-icon-link-to' => 'custom-url'
									)
								),
								'hover-icon-custom-link-target' => array(
									'title' => esc_html__('Custom Link Target', 'goodlayers-core-portfolio'),
									'type' => 'combobox',
									'options' => array(
										'_self' => esc_html__('Current Screen', 'goodlayers-core-portfolio'),
										'_blank' => esc_html__('New Window', 'goodlayers-core-portfolio'),
									),
									'condition' => array( 
										'thumbnail-type' => 'feature-image',
										'hover-icon-link-to' => 'custom-url'
									)
								),			
								'video-url' => array(
									'title' => esc_html__('Video Url ( Youtube / Vimeo )', 'goodlayers-core-portfolio'),
									'type' => 'text',
									'condition' => array( 'thumbnail-type'=>'video' )
								), 
								'slider' => array(
									'title' => esc_html__('Slider Images', 'goodlayers-core-portfolio'),
									'type' => 'custom',
									'item-type' => 'gallery',
									'condition' => array( 'thumbnail-type'=>array('slider','gallery') ),
									'wrapper-class' => 'gdlr-core-fullsize',
									'options' => array(
										'link-to' => array(
											'title' => esc_html__('Link To', 'goodlayers-core-portfolio'),
											'type' => 'combobox',
											'options' => array(
												'lb-full-image' => esc_html__('Lightbox with full image', 'goodlayers-core-portfolio'),
												'lb-custom-image' => esc_html__('Lightbox with custom image', 'goodlayers-core-portfolio'),
												'lb-video' => esc_html__('Video Lightbox', 'goodlayers-core-portfolio'),
												'page-url' => esc_html__('Specific Page', 'goodlayers-core-portfolio'),
												'custom-url' => esc_html__('Custom Url', 'goodlayers-core-portfolio'),
												'none' => esc_html__('None', 'goodlayers-core-portfolio')
											)
										),
										'custom-image' => array(
											'title' => esc_html__('Upload Custom Image', 'goodlayers-core-portfolio'),
											'type' => 'upload',
											'condition' => array(
												'link-to' => 'lb-custom-image'
											)
										),
										'video-url' => array(
											'title' => esc_html__('Video Url ( Youtube / Vimeo )', 'goodlayers-core-portfolio'),
											'type' => 'text',
											'condition' => array(
												'link-to' => 'lb-video'
											)
										),
										'page-id' => array(
											'title' => esc_html__('Page Id', 'goodlayers-core-portfolio'),
											'type' => 'combobox',
											'options' => gdlr_core_get_post_list('page'),
											'condition' => array(
												'link-to' => 'page-url'
											)
										),
										'custom-url' => array(
											'title' => esc_html__('Custom Url', 'goodlayers-core-portfolio'),
											'type' => 'text',
											'condition' => array(
												'link-to' => 'custom-url'
											)
										),
										'custom-link-target' => array(
											'title' => esc_html__('Custom Link Target', 'goodlayers-core-portfolio'),
											'type' => 'combobox',
											'options' => array(
												'_self' => esc_html__('Current Screen', 'goodlayers-core-portfolio'),
												'_blank' => esc_html__('New Window', 'goodlayers-core-portfolio'),
											),
										)
									)
								) // slider
							) // options
						), // thumbnail-settings
						
						'title' => array(
							'title' => esc_html__('Title Style', 'goodlayers-core-portfolio'),
							'options' => array(
								'title-style' => array(
									'title' => esc_html__('Page Title Style', 'goodlayers-core-portfolio'),
									'type' => 'combobox',
									'options' => array(
										'default' => esc_html__('Default', 'goodlayers-core-portfolio'),
										'small' => esc_html__('Small', 'goodlayers-core-portfolio'),
										'medium' => esc_html__('Medium', 'goodlayers-core-portfolio'),
										'large' => esc_html__('Large', 'goodlayers-core-portfolio'),
										'custom' => esc_html__('Custom', 'goodlayers-core-portfolio'),
									),
									'default' => 'default'
								),
								'title-align' => array(
									'title' => esc_html__('Page Title Alignment', 'goodlayers-core-portfolio'),
									'type' => 'radioimage',
									'options' => 'text-align',
									'with-default' => true,
									'default' => 'default'
								),
								'title-spacing' => array(
									'title' => esc_html__('Page Title Padding', 'goodlayers-core-portfolio'),
									'type' => 'custom',
									'item-type' => 'padding',
									'data-input-type' => 'pixel',
									'options' => array('padding-top', 'padding-bottom', 'caption-top-margin'),
									'wrapper-class' => 'gdlr-core-fullsize gdlr-core-no-link gdlr-core-large',
									'condition' => array( 'title-style' => 'custom' )
								),
								'title-font-size' => array(
									'title' => esc_html__('Page Title Font Size', 'goodlayers-core-portfolio'),
									'type' => 'custom',
									'item-type' => 'padding',
									'data-input-type' => 'pixel',
									'options' => array('title-size', 'title-letter-spacing', 'caption-size', 'caption-letter-spacing'),
									'wrapper-class' => 'gdlr-core-fullsize gdlr-core-no-link gdlr-core-large',
									'condition' => array( 'title-style' => 'custom' )
								),
								'title-background-overlay-opacity' => array(
									'title' => esc_html__('Page Title Background Overlay Opacity', 'goodlayers-core-portfolio'),
									'type' => 'text',
									'description' => esc_html__('Fill the number between 0 - 1 ( Leave Blank For Default Value )', 'goodlayers-core-portfolio'),
									'condition' => array( 'title-style' => 'custom' )
								),
								'title-color' => array(
									'title' => esc_html__('Page Title Color', 'goodlayers-core-portfolio'),
									'type' => 'colorpicker',
								),
								'caption-color' => array(
									'title' => esc_html__('Page Caption Color', 'goodlayers-core-portfolio'),
									'type' => 'colorpicker',
								),
								'title-background-overlay-color' => array(
									'title' => esc_html__('Page Background Overlay Color', 'goodlayers-core-portfolio'),
									'type' => 'colorpicker',
								),
							)
						), // title

						'badge' => array(
							'title' => esc_html__('Badge', 'goodlayers-core-portfolio'),
							'options' => array(

								'enable-badge' => array(
									'title' => esc_html__('Enable Badge', 'goodlayers-core-portfolio'),
									'type' => 'checkbox',
									'default' => 'disable',
								),				
								'badge-text' => array(
									'title' => esc_html__('Badge Text', 'goodlayers-core-portfolio'),
									'type' => 'text',
									'default' => 'New',
									'condition' => array( 'enable-badge' => 'enable' )
								),				
								'badge-text-color' => array(
									'title' => esc_html__('Badge Text Color', 'goodlayers-core-portfolio'),
									'type' => 'colorpicker',
									'condition' => array( 'enable-badge' => 'enable' ),
									'descirption' => esc_html__('Leave this field blank to use default color', 'goodlayers-core-portfolio')
								),				
								'badge-background-color' => array(
									'title' => esc_html__('Badge Background Color', 'goodlayers-core-portfolio'),
									'type' => 'colorpicker',
									'condition' => array( 'enable-badge' => 'enable' ),
									'descirption' => esc_html__('Leave this field blank to use default color', 'goodlayers-core-portfolio')
								),
							)
						), // badge

						'metro-layout' => array(
							'title' => esc_html__('Metro Layout', 'goodlayers-core-portfolio'),
							'options' => array(
								'metro-column-size' => array(
									'title' => esc_html__('Column Size', 'goodlayers-core-portfolio'),
									'type' => 'combobox',
									'options' => array( 'default'=> esc_html__('Default', 'goodlayers-core-portfolio'), 
										60 => '1/1', 30 => '1/2', 20 => '1/3', 40 => '2/3', 
										15 => '1/4', 45 => '3/4', 12 => '1/5', 24 => '2/5', 36 => '3/5', 48 => '4/5',
										10 => '1/6', 50 => '5/6'),
									'default' => 'default',
									'description' => esc_html__('Choosing default will display the value selected by the page builder item.', 'goodlayers-core-portfolio')
								),
								'metro-thumbnail-size' => array(
									'title' => esc_html__('Thumbnail Size', 'goodlayers-core-portfolio'),
									'type' => 'combobox',
									'options' => 'thumbnail-size',
									'with-default' => true,
									'default' => 'default',
									'description' => esc_html__('Choosing default will display the value selected by the page builder item.', 'goodlayers-core-portfolio')
								)
							)
						)
					)) // apply_filters
				));
			}


		}
	}

	add_action('plugins_loaded', 'gdlr_core_portfolio_load_textdomain');
	if( !function_exists('gdlr_core_portfolio_load_textdomain') ){
		function gdlr_core_portfolio_load_textdomain() {
		  load_plugin_textdomain('goodlayers-core-portfolio', false, plugin_basename(dirname(__FILE__)) . '/languages'); 
		}
	}	