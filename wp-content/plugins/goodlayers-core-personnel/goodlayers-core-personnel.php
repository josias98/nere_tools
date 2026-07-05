<?php
	/*
	Plugin Name: Goodlayers Personnel Post Type
	Plugin URI: 
	Description: A custom post type plugin to use with "Goodlayers Core" plugin
	Version: 1.2.6
	Author: Goodlayers
	Author URI: http://www.goodlayers.com
	License: 
	*/
	include(dirname(__FILE__) . '/pb-element-personnel.php');

	// create post type
	add_action('init', 'gdlr_core_personnel_init');
	if( !function_exists('gdlr_core_personnel_init') ){
		function gdlr_core_personnel_init(){
			
			// custom post type
			$slug = apply_filters('gdlr_core_custom_post_slug', 'personnel', 'personnel');
			$supports = apply_filters('gdlr_core_custom_post_support', array('title', 'editor', 'thumbnail', 'revisions', 'custom-fields'), 'personnel');

			$labels = array(
				'name'               => esc_html__('Personnel', 'goodlayers-core-personnel'),
				'singular_name'      => esc_html__('Personnel', 'goodlayers-core-personnel'),
				'menu_name'          => esc_html__('Personnel', 'goodlayers-core-personnel'),
				'name_admin_bar'     => esc_html__('Personnel', 'goodlayers-core-personnel'),
				'add_new'            => esc_html__('Add New', 'goodlayers-core-personnel'),
				'add_new_item'       => esc_html__('Add New Personnel', 'goodlayers-core-personnel'),
				'new_item'           => esc_html__('New Personnel', 'goodlayers-core-personnel'),
				'edit_item'          => esc_html__('Edit Personnel', 'goodlayers-core-personnel'),
				'view_item'          => esc_html__('View Personnel', 'goodlayers-core-personnel'),
				'all_items'          => esc_html__('All Personnel', 'goodlayers-core-personnel'),
				'search_items'       => esc_html__('Search Personnel', 'goodlayers-core-personnel'),
				'parent_item_colon'  => esc_html__('Parent Personnel:', 'goodlayers-core-personnel'),
				'not_found'          => esc_html__('No personnel found.', 'goodlayers-core-personnel'),
				'not_found_in_trash' => esc_html__('No personnel found in Trash.', 'goodlayers-core-personnel')
			);
			$args = array(
				'labels'             => $labels,
				'description'        => esc_html__('Description.', 'goodlayers-core-personnel'),
				'public'             => true,
				'publicly_queryable' => true,
				'exclude_from_search'=> false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array('slug' => $slug),
				'capabilities'    	 => array(
			        'edit_post'          => 'edit_personnel',
			        'read_post'          => 'read_personnel',
			        'delete_post'        => 'delete_personnel',
			        'edit_posts'         => 'edit_personnels',
			        'edit_others_posts'  => 'edit_others_personnels',
			        'publish_posts'      => 'publish_personnels',
			        'read_private_posts' => 'read_private_personnels',
			        'delete_posts'       => 'delete_personnels',
			    ),
				'map_meta_cap'		 => false,
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => $supports
			);
			register_post_type('personnel', $args);

			// custom taxonomy
			$slug = apply_filters('gdlr_core_custom_post_slug', 'personnel_category', 'personnel_category');
			$args = array(
				'hierarchical'      => true,
				'label'             => esc_html__('Personnel Category', 'goodlayers-core-personnel'),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array('slug' => $slug),
				'capabilities' 		=> array(
					'manage_terms' => 'edit_personnel',
					'edit_terms' => 'edit_personnel',
					'delete_terms' => 'delete_personnel',
					'assign_terms' => 'edit_personnel',
				)
			);
			register_taxonomy('personnel_category', array('personnel'), $args);
			register_taxonomy_for_object_type('personnel_category', 'personnel');

			// apply single template filter
			add_filter('single_template', 'gdlr_core_personnel_template');
		}
	} // gdlr_core_personnel_init

	if( !function_exists('gdlr_core_personnel_template') ){
		function gdlr_core_personnel_template($template){

			if( get_post_type() == 'personnel' ){
				$template = dirname(__FILE__) . '/single-personnel.php';
			}

			return $template;
		}
	}

	// add capabilities on plugin activation
	register_activation_hook(__FILE__, 'gdlr_core_personnel_init_admin_cap');
	if( !function_exists('gdlr_core_personnel_init_admin_cap') ){
		function gdlr_core_personnel_init_admin_cap( $post_type ){

			$post_type_cap = array('edit_%s', 'read_%s', 'delete_%s',   
				'edit_%ss', 'edit_others_%ss', 'publish_%ss', 'read_private_%ss', 'delete_%ss');

			$admin = get_role('administrator');
			foreach( $post_type_cap as $cap ){
				$admin->add_cap(str_replace('%s', 'personnel', $cap));
			}

		} // gdlr_core_personnel_init_admin_cap
	}

	// add page builder to personnel
	if( is_admin() ){ add_filter('gdlr_core_page_builder_post_type', 'gdlr_core_personnel_add_page_builder'); }
	if( !function_exists('gdlr_core_personnel_add_page_builder') ){
		function gdlr_core_personnel_add_page_builder( $post_type ){
			$post_type[] = 'personnel';
			return $post_type;
		}
	}

	// inital page builder value
	if( is_admin() ){ add_filter('gdlr_core_personnel_page_builder_val_init', 'gdlr_core_personnel_page_builder_val_init'); }
	if( !function_exists('gdlr_core_personnel_page_builder_val_init') ){
		function gdlr_core_personnel_page_builder_val_init( $value ){
			$value = '[{"template":"wrapper","type":"background","value":{"id":"","content-layout":"full","max-width":"","hide-this-wrapper-in":"none","padding":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"unlink"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"animation":"none","animation-location":"0.8","background-type":"color","background-color":"","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","pattern-opacity":"1","parallax-speed":"0.8","skin":"","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid"},"items":[{"template":"element","type":"divider","value":{"id":"","type":"normal","icon-type":"icon","image":"","icon":"fa fa-film","style":"solid","icon-size":"15px","divider-size":"1px","padding-bottom":"0px"}}]},{"template":"wrapper","type":"background","value":{"id":"","content-layout":"boxed","max-width":"","hide-this-wrapper-in":"none","padding":{"top":"70px","right":"0px","bottom":"40px","left":"0px","settings":"unlink"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"animation":"none","animation-location":"0.8","background-type":"color","background-color":"","background-image":"165","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","pattern-opacity":"1","parallax-speed":"0.8","skin":"","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid"},"items":[{"template":"wrapper","type":"column","column":"20","value":{"id":"","max-width":"300px","hide-this-wrapper-in":"none","padding":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"animation":"none","animation-location":"0.8","background-type":"color","background-color":"","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","pattern-opacity":"1","parallax-speed":"0.8","skin":"","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid"},"items":[{"template":"element","type":"image","value":{"id":"","image":"540","thumbnail-size":"full","link-to":"lb-full-image","custom-image":"","video-url":"","page-id":"497","custom-url":"","custom-link-target":"_self","enable-caption":"enable","enable-shadow":"disable","frame-style":"circle","border-radius":"3px","border-width":"0px","border-color":"","overlay-color":"","padding-bottom":"30px"}},{"template":"element","type":"social-network","value":{"id":"","delicious":"","email":"#","deviantart":"","digg":"","facebook":"#","flickr":"","google-plus":"#","lastfm":"","linkedin":"","pinterest":"","rss":"","skype":"#","stumbleupon":"","tumblr":"","twitter":"#","vimeo":"","youtube":"","text-align":"center","icon-size":"16px","icon-color":"","padding-bottom":"30px"}}]},{"template":"wrapper","type":"column","column":"40","value":{"id":"","max-width":"","hide-this-wrapper-in":"none","padding":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"unlink"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"animation":"none","animation-location":"0.8","background-type":"color","background-color":"","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","pattern-opacity":"1","parallax-speed":"0.8","skin":"","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid"},"items":[{"template":"element","type":"title","value":{"id":"","title":"Alan Cooper","caption":"Vice President","caption-position":"bottom","title-link":"","title-link-target":"_self","text-align":"left","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"#ffffff","heading-tag":"h3","title-font-size":"54px","title-font-weight":"800","title-font-style":"normal","title-font-letter-spacing":"5px","title-font-uppercase":"enable","title-color":"","title-link-hover-color":"","caption-font-size":"20px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","caption-color":"","caption-spaces":"10px","padding-bottom":"60px"}},{"template":"element","type":"title","value":{"id":"","title":"Biography","caption":"","caption-position":"top","title-link":"","title-link-target":"_self","text-align":"left","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h3","title-font-size":"18px","title-font-weight":"600","title-font-style":"normal","title-font-letter-spacing":"2px","title-font-uppercase":"enable","title-color":"","title-link-hover-color":"","caption-font-size":"16px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","caption-color":"","caption-spaces":"10px","padding-bottom":"30px"}},{"template":"element","type":"text-box","value":{"id":"","content":"A wonderful serenity has taken possession of my entire soul, like these sweet mornings of spring which I enjoy with my whole heart. I am alone, and feel the charm of existence in this spot, which was created for the bliss of souls like mine. I am so happy, my dear friend, so absorbed in the exquisite sense of mere tranquil existence, that I neglect my talents. I should be incapable of drawing a single stroke at the present moment. I throw myself down among the tall grass by the trickling stream; and, as I lie close to the earth. Thousand unknown plants are noticed by me. When I hear the buzz of the little world among the stalks, and grow familiar with the countless.","text-align":"left","padding-bottom":"35px"}},{"template":"element","type":"title","value":{"id":"","title":"Chronology","caption":"","caption-position":"top","title-link":"","title-link-target":"_self","text-align":"left","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h3","title-font-size":"18px","title-font-weight":"600","title-font-style":"normal","title-font-letter-spacing":"2px","title-font-uppercase":"enable","title-color":"","title-link-hover-color":"","caption-font-size":"16px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","caption-color":"","caption-spaces":"10px","padding-bottom":"30px"}},{"template":"element","type":"title","value":{"id":"","title":"2013-Present","caption":"CTO, Senior Software Engineer of Apple Co.","caption-position":"bottom","title-link":"","title-link-target":"_self","text-align":"left","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h5","title-font-size":"15px","title-font-weight":"500","title-font-style":"normal","title-font-letter-spacing":"1px","title-font-uppercase":"disable","title-color":"#2d9bea","title-link-hover-color":"","caption-font-size":"15px","caption-font-weight":"500","caption-font-style":"normal","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","caption-color":"","caption-spaces":"10px","padding-bottom":"30px"}},{"template":"element","type":"title","value":{"id":"","title":"2008-2013","caption":"Senior Software Development, Project Manager, Cisco Network","caption-position":"bottom","title-link":"","title-link-target":"_self","text-align":"left","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h5","title-font-size":"15px","title-font-weight":"500","title-font-style":"normal","title-font-letter-spacing":"1px","title-font-uppercase":"disable","title-color":"#2d9bea","title-link-hover-color":"","caption-font-size":"15px","caption-font-weight":"500","caption-font-style":"normal","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","caption-color":"","caption-spaces":"10px","padding-bottom":"30px"}},{"template":"element","type":"title","value":{"id":"","title":"2000-2008","caption":"Team Leader, Software Design and Development, Asus","caption-position":"bottom","title-link":"","title-link-target":"_self","text-align":"left","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h5","title-font-size":"15px","title-font-weight":"500","title-font-style":"normal","title-font-letter-spacing":"1px","title-font-uppercase":"disable","title-color":"#2d9bea","title-link-hover-color":"","caption-font-size":"15px","caption-font-weight":"500","caption-font-style":"normal","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","caption-color":"","caption-spaces":"10px","padding-bottom":"30px"}},{"template":"element","type":"title","value":{"id":"","title":"1997-2000","caption":"Massachusetts Institute of Technology, Computer Engineering","caption-position":"bottom","title-link":"","title-link-target":"_self","text-align":"left","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h5","title-font-size":"15px","title-font-weight":"500","title-font-style":"normal","title-font-letter-spacing":"1px","title-font-uppercase":"disable","title-color":"#2d9bea","title-link-hover-color":"","caption-font-size":"15px","caption-font-weight":"500","caption-font-style":"normal","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","caption-color":"","caption-spaces":"10px","padding-bottom":"60px"}},{"template":"element","type":"title","value":{"id":"","title":"Skills","caption":"","caption-position":"top","title-link":"","title-link-target":"_self","text-align":"left","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h3","title-font-size":"18px","title-font-weight":"600","title-font-style":"normal","title-font-letter-spacing":"2px","title-font-uppercase":"enable","title-color":"","title-link-hover-color":"","caption-font-size":"16px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","caption-color":"","caption-spaces":"10px","padding-bottom":"45px"}},{"template":"element","type":"skill-bar","value":{"id":"","bar-size":"small","bar-type":"rectangle","tabs":[{"heading-text":"Photography","icon":"fa fa-photo","percent":"90","bar-text":""},{"heading-text":"Animation","icon":"fa fa-gear","percent":"70","bar-text":""},{"heading-text":"Coding Skill","icon":"fa fa-code","percent":"100","bar-text":""},{"heading-text":"WordPress ","icon":"fa fa-wordpress","percent":"85","bar-text":""}],"heading-text-color":"","icon-color":"","percent-color":"","bar-filled-color":"","bar-background-color":"","padding-bottom":"30px"}}]}]},{"template":"wrapper","type":"background","value":{"id":"","content-layout":"boxed","max-width":"","hide-this-wrapper-in":"none","padding":{"top":"120px","right":"0px","bottom":"60px","left":"0px","settings":"unlink"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"animation":"none","animation-location":"0.8","background-type":"color","background-color":"#f7f7f7","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","pattern-opacity":"1","parallax-speed":"0.8","skin":"","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid"},"items":[{"template":"wrapper","type":"column","column":"20","items":[{"template":"element","type":"text-box","value":{"id":"","content":"","text-align":"left","padding-bottom":"30px"}}]},{"template":"wrapper","type":"column","column":"40","items":[{"template":"element","type":"title","value":{"id":"","title":"Contact Info","caption":"","caption-position":"bottom","title-link":"","title-link-target":"_self","text-align":"left","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h3","title-font-size":"22px","title-font-weight":"700","title-font-style":"normal","title-font-letter-spacing":"3px","title-font-uppercase":"enable","title-color":"","title-link-hover-color":"","caption-font-size":"13px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","caption-color":"","caption-spaces":"10px","padding-bottom":"20px"}},{"template":"element","type":"text-box","value":{"id":"","content":"<p>Phone : (1)-1234-4444<br />\nEmail : Contact@GoodLayers.com</p>\n","text-align":"left","padding-bottom":"30px"}}]}]}]';
			
			return json_decode($value, true);
		}
	}

	// create an option
	if( is_admin() ){ add_action('after_setup_theme', 'gdlr_core_personnel_option_init'); }
	if( !function_exists('gdlr_core_personnel_option_init') ){
		function gdlr_core_personnel_option_init(){

			if( class_exists('gdlr_core_page_option') ){
				new gdlr_core_page_option(array(
					'post_type' => array('personnel'),
					'options' => apply_filters('gdlr_core_personnel_option', array(

						'general' => array( 
							'title' => esc_html__('General', 'goodlayers-core-personnel'),
							'options' => array(
								'enable-page-title' => array(
									'title' => esc_html__('Enable Page Title', 'goodlayers-core-personnel'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'page-caption' => array(
									'title' => esc_html__('Caption', 'goodlayers-core-personnel'),
									'type' => 'textarea',
									'condition' => array( 'enable-page-title' => 'enable' )
								),					
								'title-background' => array(
									'title' => esc_html__('Page Title Background', 'goodlayers-core-personnel'),
									'type' => 'upload',
									'condition' => array( 'enable-page-title' => 'enable' )
								),		
								'show-content' => array(
									'title' => esc_html__('Show WordPress Editor Content', 'goodlayers-core-personnel'),
									'type' => 'checkbox',
									'default' => 'enable',
									'description' => esc_html__('Disable this to hide the content in editor to show only page builder content.', 'goodlayers-core-personnel'),
								),							
								'position' => array(
									'title' => esc_html__('Position', 'goodlayers-core-personnel'),
									'type' => 'text',
								),
								'excerpt' => array(
									'title' => esc_html__('Excerpt', 'goodlayers-core-personnel'),
									'type' => 'textarea',
								),
								'social-shortcode' => array(
									'title' => esc_html__('Social Shortcode', 'goodlayers-core-personnel'),
									'type' => 'textarea',
								),
							)
						)
						
					))
				));
			}


		}
	}

	add_action('plugins_loaded', 'gdlr_core_personnel_load_textdomain');
	if( !function_exists('gdlr_core_personnel_load_textdomain') ){
		function gdlr_core_personnel_load_textdomain() {
		  load_plugin_textdomain('goodlayers-core-personnel', false, plugin_basename(dirname(__FILE__)) . '/languages'); 
		}
	}	