<?php
	/*	
	*	Goodlayers Item For Page Builder
	*/
	
	add_action('plugins_loaded', 'gdlr_core_personnel_add_pb_element');
	if( !function_exists('gdlr_core_personnel_add_pb_element') ){
		function gdlr_core_personnel_add_pb_element(){

			if( class_exists('gdlr_core_page_builder_element') ){
				gdlr_core_page_builder_element::add_element('personnel', 'gdlr_core_pb_element_personnel'); 
			}
			
		}
	}
	
	if( !class_exists('gdlr_core_pb_element_personnel') ){
		class gdlr_core_pb_element_personnel{
			
			// get the element settings
			static function get_settings(){
				return array(
					'icon' => 'fa-outdent',
					'title' => esc_html__('Personnel', 'goodlayers-core-personnel')
				);
			}
			
			// return the element options
			static function get_options(){
				global $gdlr_core_item_pdb;
				
				return apply_filters('gdlr_core_pb_element_personnel_options', array(					
					'general' => array(
						'title' => esc_html__('General', 'goodlayers-core-personnel'),
						'options' => array(
							'category' => array(
								'title' => esc_html__('Category', 'goodlayers-core-personnel'),
								'type' => 'multi-combobox',
								'options' => gdlr_core_get_term_list('personnel_category'),
								'description' => esc_html__('You can use Ctrl/Command button to select multiple items or remove the selected item. Leave this field blank to select all items in the list.', 'goodlayers-core-personnel'),
							),
							'num-fetch' => array(
								'title' => esc_html__('Num Fetch', 'goodlayers-core-personnel'),
								'type' => 'text',
								'default' => 9,
								'data-input-type' => 'number',
								'description' => esc_html__('The number of posts showing on the personnel item', 'goodlayers-core-personnel')
							),
							'filterer' => array(
								'title' => esc_html__('Category Filterer', 'goodlayers-core-personnel'),
								'type' => 'checkbox',
								'default' => 'disable',
								'description' => esc_html__('Filter is not supported and will be automatically disabled on "carousel layout".', 'goodlayers-core-personnel'),
							),
							'filterer-color' => array(
								'title' => esc_html__('Filterer Color', 'goodlayers-core-personnel'),
								'type' => 'colorpicker',
								'condition' => array( 'filterer' => 'enable' )
							),	
							'orderby' => array(
								'title' => esc_html__('Order By', 'goodlayers-core-personnel'),
								'type' => 'combobox',
								'options' => array(
									'date' => esc_html__('Publish Date', 'goodlayers-core-personnel'), 
									'title' => esc_html__('Title', 'goodlayers-core-personnel'), 
									'rand' => esc_html__('Random', 'goodlayers-core-personnel'), 
									'menu_order' => esc_html__('Menu Order', 'goodlayers-core-personnel'), 
								)
							),
							'order' => array(
								'title' => esc_html__('Order', 'goodlayers-core-personnel'),
								'type' => 'combobox',
								'options' => array(
									'desc'=>esc_html__('Descending Order', 'goodlayers-core-personnel'), 
									'asc'=> esc_html__('Ascending Order', 'goodlayers-core-personnel'), 
								)
							),
						),
					),				
					'settings' => array(
						'title' => esc_html__('Style', 'goodlayers-core-personnel'),
						'options' => array(
							'personnel-style' => array(
								'title' => esc_html__('Personnel Style', 'goodlayers-core-personnel'),
								'type' => 'radioimage',
								'options' => array(
									'grid' => plugins_url('', __FILE__) . '/images/grid.png',
									'grid-no-space' => plugins_url('', __FILE__) . '/images/grid-no-space.png',
									'grid-with-background' => plugins_url('', __FILE__) . '/images/grid-with-background.png',
									'grid-feature' => plugins_url('', __FILE__) . '/images/grid-feature.png',
									'modern' => plugins_url('', __FILE__) . '/images/modern.png',
									'modern-no-space' => plugins_url('', __FILE__) . '/images/modern-no-space.png',
									'medium' => plugins_url('', __FILE__) . '/images/medium.jpg',
									'widget' => plugins_url('', __FILE__) . '/images/widget.jpg',
								),
								'default' => 'grid',
								'wrapper-class' => 'gdlr-core-fullsize'
							),
							'text-align' => array(
								'title' => esc_html__('Text Align', 'goodlayers-core-personnel'),
								'type' => 'radioimage',
								'options' => 'text-align',
								'default' => 'left',
								'condition' => array( 'personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'grid-feature', 'modern', 'modern-no-space', 'medium') )
							),
							'always-show-overlay-content' => array(
								'title' => esc_html__('Always Show Overlay Content', 'goodlayers-core-personnel'),
								'type' => 'checkbox',
								'default' => 'enable',
								'condition' => array( 'personnel-style' => array('modern', 'modern-no-space') )
							),
							'enable-position' => array(
								'title' => esc_html__('Enable Position', 'goodlayers-core-personnel'),
								'type' => 'checkbox',
								'default' => 'enable',
								'condition' => array( 'personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'grid-feature', 'modern', 'modern-no-space', 'medium') )
							),
							'disable-link' => array(
								'title' => esc_html__('Disable Link To Single Personnel', 'goodlayers-core-personnel'),
								'type' => 'checkbox',
								'default' => 'disable',
								'condition' => array( 'personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'grid-feature', 'modern', 'modern-no-space', 'medium') )
							),
							'enable-divider' => array(
								'title' => esc_html__('Enable Divider', 'goodlayers-core-personnel'),
								'type' => 'checkbox',
								'default' => 'enable',
								'condition' => array('personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'medium'))
							),
							'enable-excerpt' => array(
								'title' => esc_html__('Enable Excerpt', 'goodlayers-core-personnel'),
								'type' => 'checkbox',
								'default' => 'enable',
								'condition' => array('personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'medium'))
							),
							'enable-social-shortcode' => array(
								'title' => esc_html__('Enable Social Shortcode', 'goodlayers-core-personnel'),
								'type' => 'combobox',
								'options' => array(
									'disable' => esc_html__('Disable', 'goodlayers-core-personnel'),
									'enable' => esc_html__('Bottom', 'goodlayers-core-personnel'),
									'before-excerpt' => esc_html__('Before Excerpt ( For Grid, Medium Style )', 'goodlayers-core-personnel'),
								),
								'default' => 'enable',
								'condition' => array( 'personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'grid-feature', 'modern', 'modern-no-space', 'medium') )
							),
							'column-size' => array(
								'title' => esc_html__('Column Size', 'goodlayers-core-personnel'),
								'type' => 'combobox',
								'options' => array( 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5 ),
								'default' => 3,
								'condition' => array('personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'grid-feature', 'modern', 'modern-no-space'))
							),
							'thumbnail-size' => array(
								'title' => esc_html__('Thumbnail Size', 'goodlayers-core-personnel'),
								'type' => 'combobox',
								'options' => 'thumbnail-size',
								'condition' => array( 'personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'grid-feature', 'modern', 'modern-no-space', 'medium') )
							),
							'enable-thumbnail-opacity-on-hover' => array(
								'title' => esc_html__('Thumbnail Opacity on Hover', 'goodlayers-core-personnel'),
								'type' => 'checkbox',
								'default' => 'enable',
								'condition' => array( 'personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'grid-feature', 'modern', 'modern-no-space', 'medium') )
							),
							'thumbnail-opacity' => array(
								'title' => esc_html__('Thumbnail Opacity', 'goodlayers-core-personnel'),
								'type' => 'text',
								'description' => esc_html__('Fill the number between 0.01 to 1', 'goodlayers-core-personnel'),
								'condition' => array( 'personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'grid-feature', 'modern', 'modern-no-space', 'medium'), 'enable-thumbnail-opacity-on-hover' => 'enable' )
							),
							'thumbnail-opacity-background' => array(
								'title' => esc_html__('Thumbnail Opacity Background', 'goodlayers-core-personnel'),
								'type' => 'colorpicker',
								'condition' => array( 'personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'grid-feature', 'modern', 'modern-no-space', 'medium'), 'enable-thumbnail-opacity-on-hover' => 'enable' )
							),
							'enable-thumbnail-zoom-on-hover' => array(
								'title' => esc_html__('Thumbnail Zoom on Hover', 'goodlayers-core-personnel'),
								'type' => 'checkbox',
								'default' => 'enable',
								'condition' => array( 'personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'grid-feature', 'modern', 'modern-no-space', 'medium') )
							),
							'enable-thumbnail-grayscale-effect' => array(
								'title' => esc_html__('Enable Thumbnail Grayscale Effect', 'goodlayers-core-personnel'),
								'type' => 'checkbox',
								'default' => 'disable',
								'description' => esc_html__('Only works with browser that supports css3 filter ( http://caniuse.com/#feat=css-filters ).', 'goodlayers-core-personnel'),
								'condition' => array( 'personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'grid-feature', 'modern', 'modern-no-space', 'medium') )
							),
							'hover-content' => array(
								'title' => esc_html__('Hover Content Position', 'goodlayers-core-personnel'),
								'type' => 'combobox',
								'options' => array(
									'center' => esc_html__('Center', 'goodlayers-core-personnel'),
									'bottom' => esc_html__('Bottom', 'goodlayers-core-personnel'),
								),
								'default' => 'center',
								'condition' => array( 'personnel-style' => array('grid','grid-no-space', 'grid-with-background', 'medium') )
							),
							'enable-hover-title' => array(
								'title' => esc_html__('Enable Hover Title', 'goodlayers-core-personnel'),
								'type' => 'checkbox',
								'default' => 'disable',
								'condition' => array( 'personnel-style' => array('grid','grid-no-space', 'grid-with-background', 'medium') )
							),
							'enable-hover-social' => array(
								'title' => esc_html__('Enable Hover Social', 'goodlayers-core-personnel'),
								'type' => 'combobox',
								'options' => array(
									'disable' => esc_html__('Disable', 'goodlayers-core-personnel'),
									'enable' => esc_html__('Plain Style', 'goodlayers-core-personnel'),
									'round-border' => esc_html__('Round Border', 'goodlayers-core-personnel'),
								),
								'default' => 'disable',
								'condition' => array( 'personnel-style' => array('grid','grid-no-space', 'grid-with-background', 'medium') )
							),
							'social-hover-color' => array(
								'title' => esc_html__('Social Hover Color', 'goodlayers-core-personnel'),
								'type' => 'colorpicker',
								'condition' => array( 'personnel-style' => array('grid','grid-no-space', 'grid-with-background', 'medium'), 'enable-hover-social' => 'round-border' )
							),
							'enable-hover-excerpt' => array(
								'title' => esc_html__('Enable Hover Excerpt', 'goodlayers-core-personnel'),
								'type' => 'checkbox',
								'default' => 'disable',
								'condition' => array( 'personnel-style' => array('grid','grid-no-space', 'grid-with-background', 'medium') )
							),

							'carousel' => array(
								'title' => esc_html__('Enable Carousel', 'goodlayers-core-personnel'),
								'type' => 'checkbox',
								'default' => 'disable',
								'condition' => array('personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'modern', 'modern-no-space'))
							),
							'carousel-start-at' => array(
								'title' => esc_html__('Carousel Start At', 'goodlayers-core-personnel'),
								'type' => 'text',
								'description' => esc_html__('Only fill number here', 'goodlayers-core-personnel'),
								'condition' => array( 'carousel' => 'enable' )
							),
							'carousel-item-margin' => array(
								'title' => esc_html__('Carousel Item Margin', 'goodlayers-core-personnel'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'condition' => array( 'carousel' => 'enable' )
							),
							'carousel-overflow' => array(
								'title' => esc_html__('Carousel Overflow', 'goodlayers-core-personnel'),
								'type' => 'combobox',
								'options' => array(
									'' => esc_html__('Hidden', 'goodlayers-core-personnel'),
									'visible' => esc_html__('Visible', 'goodlayers-core-personnel')
								),
								'condition' => array( 'carousel' => 'enable' )
							),
							'carousel-autoslide' => array(
								'title' => esc_html__('Autoslide Carousel', 'goodlayers-core-personnel'),
								'type' => 'checkbox',
								'default' => 'enable',
								'condition' => array( 'carousel' => 'enable', 'personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'modern', 'modern-no-space') )
							),
							'carousel-scrolling-item-amount' => array(
								'title' => esc_html__('Carousel Scrolling Item Amount', 'goodlayers-core-personnel'),
								'type' => 'text',
								'default' => '1',
								'condition' => array( 'carousel' => 'enable', 'personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'modern', 'modern-no-space') )
							),
							'carousel-navigation' => array(
								'title' => esc_html__('Carousel Navigation', 'goodlayers-core-personnel'),
								'type' => 'combobox',
								'options' => (function_exists('gdlr_core_get_flexslider_navigation_types')? gdlr_core_get_flexslider_navigation_types(): array()),
								'default' => 'navigation',
								'condition' => array( 'carousel' => 'enable', 'personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'modern', 'modern-no-space') )
							),
							'carousel-navigation-show-on-hover' => array(
								'title' => esc_html__('Carousel Navigation Display On Hover', 'goodlayers-core'),
								'type' => 'checkbox',
								'default' => 'disable',
								'condition' => array( 'carousel-navigation' => array('navigation-outer', 'navigation-inner') )
							),
							'carousel-navigation-align' => (function_exists('gdlr_core_get_flexslider_navigation_align')? gdlr_core_get_flexslider_navigation_align(): array()),
							'carousel-navigation-left-icon' => (function_exists('gdlr_core_get_flexslider_navigation_left_icon')? gdlr_core_get_flexslider_navigation_left_icon(): array()),
							'carousel-navigation-right-icon' => (function_exists('gdlr_core_get_flexslider_navigation_right_icon')? gdlr_core_get_flexslider_navigation_right_icon(): array()),
							'carousel-navigation-size' => (function_exists('gdlr_core_get_flexslider_navigation_icon_size')? gdlr_core_get_flexslider_navigation_icon_size(): array()),
							'carousel-navigation-icon-color' => (function_exists('gdlr_core_get_flexslider_navigation_icon_color')? gdlr_core_get_flexslider_navigation_icon_color(): array()),
							'carousel-navigation-icon-bg' => (function_exists('gdlr_core_get_flexslider_navigation_icon_background')? gdlr_core_get_flexslider_navigation_icon_background(): array()),
							'carousel-navigation-icon-padding' => (function_exists('gdlr_core_get_flexslider_navigation_icon_padding')? gdlr_core_get_flexslider_navigation_icon_padding(): array()),
							'carousel-navigation-icon-radius' => (function_exists('gdlr_core_get_flexslider_navigation_icon_radius')? gdlr_core_get_flexslider_navigation_icon_radius(): array()),
							'carousel-navigation-margin' => (function_exists('gdlr_core_get_flexslider_navigation_margin')? gdlr_core_get_flexslider_navigation_margin(): array()),
							'carousel-navigation-icon-margin' => (function_exists('gdlr_core_get_flexslider_navigation_icon_margin')? gdlr_core_get_flexslider_navigation_icon_margin(): array()),
							'carousel-bullet-style' => array(
								'title' => esc_html__('Carousel Bullet Style', 'goodlayers-core-personnel'),
								'type' => 'radioimage',
								'options' => (function_exists('gdlr_core_get_flexslider_bullet_itypes')? gdlr_core_get_flexslider_bullet_itypes(): array()),
								'condition' => array( 'carousel' => 'enable', 'carousel-navigation' => array('bullet','both'), 'personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'modern', 'modern-no-space') ),
								'wrapper-class' => 'gdlr-core-fullsize'
							),
							'carousel-bullet-top-margin' => array(
								'title' => esc_html__('Carousel Bullet Top Margin', 'goodlayers-core-personnel'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'condition' => array( 'carousel' => 'enable', 'carousel-navigation' => array('bullet','both'), 'personnel-style' => array('grid', 'grid-no-space', 'grid-with-background', 'modern', 'modern-no-space') )
							)
						)
					),
					'typography' => array(
						'title' => esc_html__('Typography', 'goodlayers-core-personnel'),
						'options' => array(
							'personnel-title-font-size' => array(
								'title' => esc_html__('Personnel Title Font Size', 'goodlayers-core-personnel'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'personnel-title-font-weight' => array(
								'title' => esc_html__('Personnel Title Font Weight', 'goodlayers-core-personnel'),
								'type' => 'text',
								'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800', 'goodlayers-core-personnel')
							),
							'personnel-title-letter-spacing' => array(
								'title' => esc_html__('Personnel Title Letter Spacing', 'goodlayers-core-personnel'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'personnel-title-text-transform' => array(
								'title' => esc_html__('Personnel Title Text Transform', 'goodlayers-core-personnel'),
								'type' => 'combobox',
								'data-type' => 'text',
								'options' => array(
									'none' => esc_html__('None', 'goodlayers-core-personnel'),
									'uppercase' => esc_html__('Uppercase', 'goodlayers-core-personnel'),
									'lowercase' => esc_html__('Lowercase', 'goodlayers-core-personnel'),
									'capitalize' => esc_html__('Capitalize', 'goodlayers-core-personnel'),
								),
								'default' => 'uppercase'
							),
							'personnel-position-font-size' => array(
								'title' => esc_html__('Personnel Position Font Size', 'goodlayers-core-personnel'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'personnel-position-font-weight' => array(
								'title' => esc_html__('Personnel Position Font Weight', 'goodlayers-core-personnel'),
								'type' => 'text',
								'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800', 'goodlayers-core-personnel')
							),
							'personnel-position-font-style' => array(
								'title' => esc_html__('Personnel Position Font Style', 'goodlayers-core-personnel'),
								'type' => 'combobox',
								'options' => array(
									'normal' => esc_html__('Normal', 'goodlayers-core-personnel'),
									'italic' => esc_html__('Italic', 'goodlayers-core-personnel'),
								),
								'default' => 'normal'
							),
							'personnel-position-letter-spacing' => array(
								'title' => esc_html__('Personnel Position Letter Spacing', 'goodlayers-core-personnel'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'personnel-position-text-transform' => array(
								'title' => esc_html__('Personnel Position Text Transform', 'goodlayers-core-personnel'),
								'type' => 'combobox',
								'data-type' => 'text',
								'options' => array(
									'none' => esc_html__('None', 'goodlayers-core-personnel'),
									'uppercase' => esc_html__('Uppercase', 'goodlayers-core-personnel'),
									'lowercase' => esc_html__('Lowercase', 'goodlayers-core-personnel'),
									'capitalize' => esc_html__('Capitalize', 'goodlayers-core-personnel'),
								),
								'default' => 'none'
							),
						)
					),
					'shadow' => array(
						'title' => esc_html__('Border/Shadow', 'goodlayers-core-personnel'),
						'options' => array(
							'border-width' => array(
								'title' => esc_html__('Border Width ( Frame Style )', 'goodlayers-core-personnel'),
								'type' => 'custom',
								'item-type' => 'padding',
								'data-input-type' => 'pixel',
								'default' => array( 'top'=>'', 'right'=>'', 'bottom'=>'', 'left'=>'', 'settings'=>'link' ),
							),
							'shadow-size' => array(
								'title' => esc_html__('Shadow Size ( Thumbnail, Frame Style )', 'goodlayers-core-personnel'),
								'type' => 'custom',
								'item-type' => 'padding',
								'options' => array('x', 'y', 'size'),
								'data-input-type' => 'pixel',
							),
							'shadow-color' => array(
								'title' => esc_html__('Shadow Color ( Thumbnail, Frame Style )', 'goodlayers-core-personnel'),
								'type' => 'colorpicker'
							),
							'shadow-opacity' => array(
								'title' => esc_html__('Shadow Opacity ( Thumbnail, Frame Style )', 'goodlayers-core-personnel'),
								'type' => 'text',
								'default' => '0.2',
								'description' => esc_html__('Fill the number between 0.01 to 1', 'goodlayers-core-personnel')
							),
						)
					),
					'spacing' => array(
						'title' => esc_html__('Spacing', 'goodlayers-core-personnel'),
						'options' => array(
							'personnel-border-radius' => array(
								'title' => esc_html__('Personnel Frame/Thumbnail Border Radius', 'goodlayers-core-personnel'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'personnel-thumbnail-bottom-margin' => array(
								'title' => esc_html__('Personnel Thumbnail Bottom ( Frame Top ) Margin', 'goodlayers-core-personnel'),
								'type' => 'text',
								'data-input-type' => 'pixel'
							),
							'personnel-modern-content-bottom' => array(
								'title' => esc_html__('Personnel Content Bottom Spaces ( For Image/Frame Style )', 'goodlayers-core-personnel'),
								'type' => 'text',
								'data-input-type' => 'pixel'
							),
							'personnel-title-bottom-margin' => array(
								'title' => esc_html__('Personnel Title Bottom Margin', 'goodlayers-core-personnel'),
								'type' => 'text',
								'data-input-type' => 'pixel'
							),
							'list-margin-bottom' => array(
								'title' => esc_html__('Margin Bottom ( List )', 'goodlayers-core-personnel'),
								'type' => 'text',
								'data-input-type' => 'pixel'
							),
							'padding-bottom' => array(
								'title' => esc_html__('Padding Bottom ( Item )', 'goodlayers-core-personnel'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'default' => $gdlr_core_item_pdb
							)
						)
					),
					'item-title' => array(
						'title' => esc_html__('Item Title', 'goodlayers-core-personnel'),
						'options' => gdlr_core_block_item_option()
					),	
				));
			}

			// get the preview for page builder
			static function get_preview( $settings = array() ){
				$content  = self::get_content($settings, true);
				$id = mt_rand(0, 9999);
				
				ob_start();
?><script type="text/javascript" id="gdlr-core-preview-personnel-<?php echo esc_attr($id); ?>" >
if( document.readyState == 'complete' ){
	jQuery(document).ready(function(){
		jQuery('#gdlr-core-preview-personnel-<?php echo esc_attr($id); ?>').parent().gdlr_core_flexslider();
	});
}else{
	jQuery(window).load(function(){
		jQuery('#gdlr-core-preview-personnel-<?php echo esc_attr($id); ?>').parent().gdlr_core_flexslider();
	});
}
</script><?php	
				$content .= ob_get_contents();
				ob_end_clean();
				
				return $content;
			}			
			
			// get the content from settings
			static function get_content( $settings = array(), $preview = false ){
				global $gdlr_core_item_pdb;
				
				// default variable
				if( empty($settings) ){
					$settings = array(
						'category' => '', 'num-fetch' => 9, 'thumbnail-size' => 'full', 'orderby' => 'date', 'order' => 'asc',
						'personnel-style' => 'grid', 'column-size' => 3, 'text-align' => 'left', 'carousel' => 'disable',
						'enable-social-shortcode' => 'enable', 'padding-bottom' => $gdlr_core_item_pdb
					);
				}

				$custom_style = '';
				if( !empty($settings['social-hover-color']) ){
					$custom_style .= '#custom_style_id .gdlr-core-personnel-thumbnail-hover-social.gdlr-core-round-border .gdlr-core-social-network-icon:hover{ background-color: ' . $settings['social-hover-color'] . '; border-color: ' . $settings['social-hover-color'] . '; }';
				}
				if( !empty($custom_style) ){
					if( empty($settings['id']) ){
						global $gdlr_core_personnel_id; 
						$gdlr_core_personnel_id = empty($gdlr_core_personnel_id)? array(): $gdlr_core_personnel_id;
						
						// generate unique id so it does not get overwritten in admin area
						$rnd_personnel_id = mt_rand(0, 99999);
						while( in_array($rnd_personnel_id, $gdlr_core_personnel_id) ){
							$rnd_personnel_id = mt_rand(0, 99999);
						}
						$gdlr_core_personnel_id[] = $rnd_personnel_id;
						$settings['id'] = 'gdlr-core-personnel-' . $rnd_personnel_id;
					}
					$custom_style = str_replace('custom_style_id', $settings['id'], $custom_style); 
					if( $preview ){
						$custom_style = '<style>' . $custom_style . '</style>';
					}else{
						gdlr_core_add_inline_style($custom_style);
						$custom_style = '';
					}
				}
				
				// default value
				$settings['personnel-style'] = empty($settings['personnel-style'])? 'grid': $settings['personnel-style'];
				if( in_array($settings['personnel-style'], array('grid', 'grid-no-space', 'grid-with-background')) ){
					$settings['style'] = 'grid';
				}else if( in_array($settings['personnel-style'], array('modern', 'modern-no-space')) ){
					$settings['style'] = 'modern';
				}else{
					$settings['style'] = $settings['personnel-style'];
				}
				$settings['carousel'] = empty($settings['carousel'])? 'disable': $settings['carousel'];
				$settings['disable-link'] = empty($settings['disable-link'])? 'disable': $settings['disable-link'];
				$settings['no-space'] = in_array($settings['personnel-style'], array('grid-no-space', 'modern-no-space'))? 'yes': 'no';

				$query = self::personnel_query($settings); 

				// start printing item
				$extra_class  = ' gdlr-core-' . (empty($settings['text-align'])? 'left': $settings['text-align']) . '-align';
				$extra_class .= ' gdlr-core-personnel-item-style-' . $settings['personnel-style'];
				$extra_class .= ' gdlr-core-personnel-style-' . $settings['style'];
				$extra_class .= ($settings['personnel-style'] == 'grid-with-background')? ' gdlr-core-with-background': '';
				$extra_class .= (empty($settings['enable-divider']) || $settings['enable-divider'] == 'enable')? ' gdlr-core-with-divider ': '';

				$title_settings = $settings;
				if( $settings['no-space'] == 'yes' || $settings['carousel'] == 'enable' ){
					$title_settings['pdlr'] = false;
					$extra_class .= ' gdlr-core-item-pdlr';
				}

				if( !empty($settings['column-size']) ){
					gdlr_core_set_container_multiplier(1 / intval($settings['column-size']), false);
				}

				$ret  = '<div class="gdlr-core-personnel-item gdlr-core-item-pdb clearfix ' . esc_attr($extra_class) . '" ';
				if( !empty($settings['padding-bottom']) && $settings['padding-bottom'] != $gdlr_core_item_pdb ){
					$ret .= gdlr_core_esc_style(array('padding-bottom'=>$settings['padding-bottom']));
				}
				if( !empty($settings['id']) ){
					$ret .= ' id="' . esc_attr($settings['id']) . '" ';
				}
				$ret .= ' >';

				// print title
				$ret .= gdlr_core_block_item_title($title_settings);
				
				// print grid item
				if( $query->have_posts() ){
					if( $settings['carousel'] == 'disable' || in_array($settings['style'], array('medium', 'widget')) ){

						if( $query->have_posts() ){

							if( !empty($settings['filterer']) && $settings['filterer'] != 'disable' ){
								$ret .= self::personnel_filterer($settings);
							} 
							
							gdlr_core_setup_admin_postdata();
							$ret .= '<div class="gdlr-core-personnel-item-holder clearfix" >';
							$ret .= self::personnel_list_content($query, $settings);
							$ret .= '</div>';
							wp_reset_postdata();
							gdlr_core_reset_admin_postdata();
						}

					// print carousel item
					}else{

						$slides = array();
						$flex_atts = array(
							'carousel' => true,
							'overflow' => empty($settings['carousel-overflow'])? '': $settings['carousel-overflow'],
							'start-at' => empty($settings['carousel-start-at'])? '': $settings['carousel-start-at'],
							'margin' => empty($settings['carousel-item-margin'])? '': $settings['carousel-item-margin'],
							'column' => empty($settings['column-size'])? '3': $settings['column-size'],
							'move' => empty($settings['carousel-scrolling-item-amount'])? '': $settings['carousel-scrolling-item-amount'],
							'navigation' => empty($settings['carousel-navigation'])? 'navigation': $settings['carousel-navigation'],
							'navigation-on-hover' => empty($settings['carousel-navigation-show-on-hover'])? 'disable': $settings['carousel-navigation-show-on-hover'],
							'navigation-align' => empty($settings['carousel-navigation-align'])? '': $settings['carousel-navigation-align'],
							'navigation-size' => empty($settings['carousel-navigation-size'])? '': $settings['carousel-navigation-size'],
							'navigation-icon-color' => empty($settings['carousel-navigation-icon-color'])? '': $settings['carousel-navigation-icon-color'],
							'navigation-icon-background' => empty($settings['carousel-navigation-icon-bg'])? '': $settings['carousel-navigation-icon-bg'],
							'navigation-icon-padding' => empty($settings['carousel-navigation-icon-padding'])? '': $settings['carousel-navigation-icon-padding'],
							'navigation-icon-radius' => empty($settings['carousel-navigation-icon-radius'])? '': $settings['carousel-navigation-icon-radius'],
							'navigation-margin' => empty($settings['carousel-navigation-margin'])? '': $settings['carousel-navigation-margin'],
							'navigation-icon-margin' => empty($settings['carousel-navigation-icon-margin'])? '': $settings['carousel-navigation-icon-margin'],
							'navigation-left-icon' => empty($settings['carousel-navigation-left-icon'])? '': $settings['carousel-navigation-left-icon'],
							'navigation-right-icon' => empty($settings['carousel-navigation-right-icon'])? '': $settings['carousel-navigation-right-icon'],
							'bullet-style' => empty($settings['carousel-bullet-style'])? '': $settings['carousel-bullet-style'],
							'nav-parent' => 'gdlr-core-personnel-item',
							'disable-autoslide' => (empty($settings['carousel-autoslide']) || $settings['carousel-autoslide'] == 'enable')? '': true,
							'controls-top-margin' => empty($settings['carousel-bullet-top-margin'])? '': $settings['carousel-bullet-top-margin']
						);
						if( $settings['no-space'] == 'yes' ){
							$flex_atts['mglr'] = false;
						}

						gdlr_core_setup_admin_postdata();
						while( $query->have_posts() ){ $query->the_post();
							$slides[] = self::get_tab_item($settings);
						}
						wp_reset_postdata();
						gdlr_core_reset_admin_postdata();

						$ret .= gdlr_core_get_flexslider($slides, $flex_atts);
					}
				}else{
					$ret .= '<div class="gdlr-core-external-plugin-message">' . esc_html__('No personnel found, please create the personnel post to use the item.', 'goodlayers-core-personnel') . '</div>';
				}

				$ret .= '</div>'; // gdlr-core-blog-item
				$ret .= $custom_style; 

				gdlr_core_set_container_multiplier(1, false);

				return $ret;
			}

			static function personnel_query( $settings ){ 

				// query
				$args = array( 'post_type' => 'personnel', 'post_status' => 'publish', 'suppress_filters' => false );

				if( !empty($settings['category']) ){
					$args['tax_query'] = array(array('terms'=>$settings['category'], 'taxonomy'=>'personnel_category', 'field'=>'slug'));
				}

				$args['posts_per_page'] = $settings['num-fetch'];
				$args['orderby'] = $settings['orderby'];
				$args['order'] = $settings['order'];	

				// $args['paged'] = (get_query_var('paged'))? get_query_var('paged') : get_query_var('page');
				// $args['paged'] = empty($args['paged'])? 1: $args['paged'];
				
				return new WP_Query( $args );
			}
				
			static function personnel_list_content( $query, $settings ){ 

				$p_column_count = 0;
				if( in_array($settings['style'], array('medium', 'widget')) ){
					$p_column = 60;
				}else{
					$p_column = 60 / intval($settings['column-size']);
				}

				$ret = '';
				while( $query->have_posts() ){ $query->the_post();
					$column_class  = ' gdlr-core-column-' . $p_column;
					$column_class .= ($p_column_count % 60 == 0)? ' gdlr-core-column-first': '';
					$column_class .= ($settings['no-space'] == 'yes')? '': ' gdlr-core-item-pdlr';
					$column_class .= ($settings['style'] == 'modern' && $settings['no-space'] == 'no')? ' gdlr-core-item-mgb': '';

					$ret .= '<div class="gdlr-core-item-list gdlr-core-personnel-list-column ' . esc_attr($column_class) . ' clearfix" ' . gdlr_core_esc_style(array(
						'margin-bottom' => empty($settings['list-margin-bottom'])? '': $settings['list-margin-bottom']
					)) . ' >';
					if( $settings['style'] == 'widget' ){
						$ret .= self::get_personnel_widget($settings);
					}else{
						$ret .= self::get_tab_item($settings);
					}
					$ret .= '</div>';

					$p_column_count += $p_column;
				}

				return $ret;
			}

			static function personnel_filterer( $settings ){ 

				// for all
				if( empty($settings['category']) ){
					$filters = array(
						'' => esc_html__('All', 'goodlayers-core-personnel')
					) + gdlr_core_get_term_list('personnel_category');
				// parent category
				}else if( sizeof($settings['category']) == 1 ){
					$term = get_term_by('slug', $settings['category'][0], $taxonomy);
					$filters = array(
						$term->slug => $term->name
					) + gdlr_core_get_term_list('personnel_category', $term->term_id);
				// multiple category select
				}else{
					$filters = array(
						'' => esc_html__('All', 'goodlayers-core-personnel')
					) + gdlr_core_get_term_list('personnel_category', $settings['category']);
				}

				if( !empty($filters) ){
					$ret  = '<div class="gdlr-core-personnel-filterer gdlr-core-item-pdlr gdlr-core-js" ';
					$ret .= 'data-ajax="gdlr_core_personnel_ajax" ';
					$ret .= 'data-settings="' . esc_attr(json_encode($settings)) . '" ';
					$ret .= 'data-target="gdlr-core-personnel-item-holder" ';
					$ret .= 'data-target-action="replace" ';
					$ret .= ' >';
					$ret .= '<div class="gdlr-core-head" >' . esc_html__('Filter By', 'goodlayers-core-personnel') . '</div>';
					$ret .= '<div class="gdlr-core-tail" >';
					$ret .= '<div class="gdlr-core-custom-dropdown gdlr-core-js" >';
					$ret .= '<div class="gdlr-core-custom-dropdown-current" ' . gdlr_core_esc_style(array(
						'color' => empty($settings['filterer-color'])? '': $settings['filterer-color'],
						'border-color' => empty($settings['filterer-color'])? '': $settings['filterer-color']
					)) . ' >';
					foreach( $filters as $slug => $name ){
						$ret .= gdlr_core_text_filter($name);
						break;
					}
					$ret .= '</div>';

					$first_child = true;
					$ret .= '<div class="gdlr-core-custom-dropdown-list" >';
					foreach( $filters as $slug => $name ){
						$ret .= '<div class="gdlr-core-custom-dropdown-list-item gdlr-core-ajax-link ' . ($first_child? 'gdlr-core-active': '') . '" data-ajax-name="category" data-ajax-value="' . esc_attr($slug) . '" >' . gdlr_core_text_filter($name) . '</div>'; 
						$first_child = false;
					}
					$ret .= '</div>';
					$ret .= '</div>';
					$ret .= '</div>';
					$ret .= '</div>';
				}

				return $ret; 
			}

			static function personnel_thumbnail( $settings, $post_meta ){ 
				$ret = '';
				$thumbnail_id = get_post_thumbnail_id();

				if( !empty($thumbnail_id) ){
					$thumbnail_size = empty($settings['thumbnail-size'])? 'full': $settings['thumbnail-size'];

					$additional_class  = '';
					if( empty($settings['enable-thumbnail-opacity-on-hover']) || $settings['enable-thumbnail-opacity-on-hover'] == 'enable' ){
						$additional_class .= ' gdlr-core-hover-element';
					}
					if( empty($settings['enable-thumbnail-zoom-on-hover']) || $settings['enable-thumbnail-zoom-on-hover'] == 'enable' ){
						$additional_class .= ' gdlr-core-zoom-on-hover';
					}
					if( !empty($settings['enable-thumbnail-grayscale-effect']) && $settings['enable-thumbnail-grayscale-effect'] == 'enable' ){
						$additional_class .= ' gdlr-core-grayscale-effect';
					}

					$css_atts = array();
					if( in_array($settings['personnel-style'], array('grid', 'grid-no-space', 'grid-feature', 'medium')) ){
						if( !empty($settings['personnel-border-radius']) ){
							$css_atts['border-radius'] = $settings['personnel-border-radius'];
							$css_atts['overflow'] = 'hidden';
						}
					}
					$ret .= '<div class="gdlr-core-personnel-list-image gdlr-core-media-image ' . esc_attr($additional_class) . '" ' . gdlr_core_esc_style($css_atts) . ' >';
					if( $settings['disable-link'] == 'enable' ){
						$ret .= gdlr_core_get_image($thumbnail_id, $thumbnail_size);
					}else{
						$ret .= '<a href="' . get_permalink() . '" >' . gdlr_core_get_image($thumbnail_id, $thumbnail_size) .  '</a>';
					}

					// hover
					$thumbnail_atts = array();
					if( !empty($settings['thumbnail-opacity-background']) || !empty($settings['thumbnail-opacity']) ){
						$thumbnail_atts['background'] = array();
						$thumbnail_atts['background'][] = empty($settings['thumbnail-opacity-background'])? '#000': $settings['thumbnail-opacity-background'];
						$thumbnail_atts['background'][] = empty($settings['thumbnail-opacity'])? '0.5': $settings['thumbnail-opacity'];
					}
					$ret .= '<div class="gdlr-core-hover-opacity" ' . gdlr_core_esc_style($thumbnail_atts) . ' >';

					$hover_content = '';
					if( !empty($settings['enable-hover-title']) && $settings['enable-hover-title'] == 'enable' ){
						$hover_content .= '<div class="gdlr-core-personnel-thumbnail-hover-title" ><a href="' . esc_attr(get_permalink()) . '" >' . get_the_title() . '</a></div>';
					}	
					if( !empty($settings['enable-hover-social']) && $settings['enable-hover-social'] != 'disable' ){
						if( in_array($settings['personnel-style'], array('grid','grid-no-space', 'grid-with-background', 'medium')) ){
							$hover_content .= '<div class="gdlr-core-personnel-thumbnail-hover-social ';
							$hover_content .= ($settings['enable-hover-social'] == 'enable')? '': ' gdlr-core-' . $settings['enable-hover-social'];
							$hover_content .= '" >' . gdlr_core_text_filter($post_meta['social-shortcode']) . '</div>';
						}
					}
					if( !empty($settings['enable-hover-excerpt']) && $settings['enable-hover-excerpt'] == 'enable' ){
						$hover_content .= '<div class="gdlr-core-personnel-thumbnail-hover-excerpt" >' . gdlr_core_text_filter($post_meta['excerpt']) . '</div>';
					}
					if( !empty($hover_content) ){
						$ret .= '<div class="gdlr-core-personnel-thumbnail-hover-content '; 
						$ret .= (empty($settings['hover-content']))? '': 'gdlr-core-' . esc_attr($settings['hover-content']);
						$ret .= '" >' . gdlr_core_text_filter($hover_content) . '</div>';
					}
					$ret .= '</div>';

					$ret .= '</div>';
				}

				return $ret;
			}

			static function get_personnel_widget( $settings = array() ){ 

				$thumbnail_id = get_post_thumbnail_id();

				$title_atts = array(
					'font-size' => empty($settings['personnel-title-font-size'])? '': $settings['personnel-title-font-size'],
					'font-weight' => empty($settings['personnel-title-font-weight'])? '': $settings['personnel-title-font-weight'],
					'letter-spacing' => empty($settings['personnel-title-letter-spacing'])? '': $settings['personnel-title-letter-spacing'],
					'text-transform' => (empty($settings['personnel-title-text-transform']) || $settings['personnel-title-text-transform'] == 'uppercase')? '': $settings['personnel-title-text-transform'],
					'margin-bottom' => empty($settings['personnel-title-bottom-margin'])? '': $settings['personnel-title-bottom-margin'],
				);

				$ret  = '<div class="gdlr-core-personnel-list-image gdlr-core-media-image" >';
				$ret .= '<a href="' . get_permalink() . '" >' . gdlr_core_get_image($thumbnail_id, 'thumbnail') .  '</a>';
				$ret .= '</div>';

				$ret .= '<div class="gdlr-core-personnel-list-content clearfix" >';
				$ret .= '<h3 class="gdlr-core-personnel-list-title" ' . gdlr_core_esc_style($title_atts) . ' >' . get_the_title() . '</h3>';
				$ret .= '<a class="gdlr-core-personnel-list-link gdlr-core-skin-caption" href="' . get_permalink() . '" >' . esc_html__('View Profile', 'goodlayers-core-personnel') . '<i class="arrow_right"></i></a>';
				$ret .= '</div>'; // gdlr-core-personnel-list-content

				return $ret;
			}

			static function get_tab_item( $settings = array() ){ 

				$post_meta = get_post_meta(get_the_ID(), 'gdlr-core-page-option', true);

				
				if( in_array($settings['personnel-style'], array('grid-with-background', 'modern', 'modern-no-space')) ){
					
					$item_css = array();

					if( $settings['personnel-style'] == 'grid-with-background' ){
						$item_css['border-width'] = (empty($settings['border-width']) || $settings['border-width'] == array( 'top'=>'', 'right'=>'', 'bottom'=>'', 'left'=>'', 'settings'=>'link' ))? '': $settings['border-width'];
					}
					if( !empty($settings['shadow-size']['size']) && !empty($settings['shadow-color']) && !empty($settings['shadow-opacity']) ){
						$item_css['background-shadow-size'] = empty($settings['shadow-size'])? '': $settings['shadow-size'];
						$item_css['background-shadow-color'] = empty($settings['shadow-color'])? '': $settings['shadow-color'];
						$item_css['background-shadow-opacity'] = empty($settings['shadow-opacity'])? '': $settings['shadow-opacity'];
					}
					if( !empty($settings['personnel-border-radius']) ){
						$item_css['border-radius'] = empty($settings['personnel-border-radius'])? '': $settings['personnel-border-radius'];
					}

					$ret  = '<div class="gdlr-core-personnel-list clearfix';
					if( !empty($item_css) ){
						$ret .= ' gdlr-core-outer-frame-element" ' . gdlr_core_esc_style($item_css) . ' >';
					}else{
						$ret .= '" >';
					}
				}else{
					$ret  = '<div class="gdlr-core-personnel-list clearfix" >';
				}
				
				$ret .= self::personnel_thumbnail($settings, $post_meta);

				$title_atts = array(
					'font-size' => empty($settings['personnel-title-font-size'])? '': $settings['personnel-title-font-size'],
					'font-weight' => empty($settings['personnel-title-font-weight'])? '': $settings['personnel-title-font-weight'],
					'letter-spacing' => empty($settings['personnel-title-letter-spacing'])? '': $settings['personnel-title-letter-spacing'],
					'text-transform' => (empty($settings['personnel-title-text-transform']) || $settings['personnel-title-text-transform'] == 'uppercase')? '': $settings['personnel-title-text-transform'],
					'margin-bottom' => empty($settings['personnel-title-bottom-margin'])? '': $settings['personnel-title-bottom-margin'],
				);
				$position_atts = array(
					'font-size' => empty($settings['personnel-position-font-size'])? '': $settings['personnel-position-font-size'],
					'font-weight' => empty($settings['personnel-position-font-weight'])? '': $settings['personnel-position-font-weight'],
					'font-style' => (empty($settings['personnel-position-font-style']) || $settings['personnel-position-font-style'] == 'italic')? '': $settings['personnel-position-font-style'],
					'letter-spacing' => empty($settings['personnel-position-letter-spacing'])? '': $settings['personnel-position-letter-spacing'],
					'text-transform' => (empty($settings['personnel-position-text-transform']) || $settings['personnel-position-text-transform'] == 'none')? '': $settings['personnel-position-text-transform'],
				);

				$content_class = '';
				if( in_array($settings['personnel-style'], array('modern', 'modern-no-space')) ){
					if( !empty($settings['always-show-overlay-content']) && $settings['always-show-overlay-content'] == 'disable' ){
						$content_class .= ' gdlr-core-hover-overlay-content';
					}
				} 
				$content_css = array(
					'padding-top' => empty($settings['personnel-thumbnail-bottom-margin'])? '': $settings['personnel-thumbnail-bottom-margin']
				);
				if( $settings['personnel-style'] == 'grid-with-background' ){
					$content_css['padding-bottom'] = empty($settings['personnel-modern-content-bottom'])? '': $settings['personnel-modern-content-bottom'];
				}else if( in_array($settings['personnel-style'], array('modern', 'modern-no-space')) ){
					$content_css['bottom'] = empty($settings['personnel-modern-content-bottom'])? '': $settings['personnel-modern-content-bottom'];
				}
				$ret .= '<div class="gdlr-core-personnel-list-content-wrap ' . esc_attr($content_class) . '" ' . gdlr_core_esc_style($content_css) . ' >';
				if( in_array($settings['style'], array('grid', 'medium')) ){
					$ret .= '<h3 class="gdlr-core-personnel-list-title" ' . gdlr_core_esc_style($title_atts) . ' >';
					if( $settings['disable-link'] == 'enable' ){
						$ret .= get_the_title();
					}else{
						$ret .= '<a href="' . get_permalink() . '" >' . get_the_title() . '</a>';
					}
					$ret .= '</h3>';
					$ret .= apply_filters('gdlr_core_personnel_item_after_title', '', $settings);
					if( (empty($settings['enable-position']) || $settings['enable-position'] == 'enable') && !empty($post_meta['position']) ){
						$ret .= '<div class="gdlr-core-personnel-list-position gdlr-core-info-font gdlr-core-skin-caption" ' . gdlr_core_esc_style($position_atts) . ' >' . gdlr_core_text_filter($post_meta['position']) . '</div>';
					}
					if( empty($settings['enable-divider']) || $settings['enable-divider'] == 'enable' ){
						$ret .= '<div class="gdlr-core-personnel-list-divider gdlr-core-skin-divider" ></div>';
					}
					if( (empty($settings['enable-social-shortcode']) || $settings['enable-social-shortcode'] == 'before-excerpt') && !empty($post_meta['social-shortcode']) ){
						$ret .= '<div class="gdlr-core-personnel-list-social" >' . gdlr_core_content_filter($post_meta['social-shortcode']) . '</div>';
					}
					if( (empty($settings['enable-excerpt']) || $settings['enable-excerpt'] == 'enable') && !empty($post_meta['excerpt']) ){
						$ret .= '<div class="gdlr-core-personnel-list-content" >' . gdlr_core_content_filter($post_meta['excerpt']) . '</div>';
					}
					if( (empty($settings['enable-social-shortcode']) || $settings['enable-social-shortcode'] == 'enable') && !empty($post_meta['social-shortcode']) ){
						$ret .= '<div class="gdlr-core-personnel-list-social" >' . gdlr_core_content_filter($post_meta['social-shortcode']) . '</div>';
					}
				}else{
					$ret .= '<div class="gdlr-core-personnel-list-title gdlr-core-title-font" ' . gdlr_core_esc_style($title_atts) . ' >';
					if( $settings['disable-link'] == 'enable' ){
						$ret .= get_the_title();
					}else{
						$ret .= '<a href="' . get_permalink() . '" >' . get_the_title() . '</a>';
					}
					$ret .= '</div>';
					$ret .= ($settings['style'] == 'grid-feature')? '<div class="gdlr-core-personnel-list-info-wrap" >': '';
					
					$ret .= apply_filters('gdlr_core_personnel_item_after_title', '', $settings);

					if( (empty($settings['enable-position']) || $settings['enable-position'] == 'enable') && !empty($post_meta['position']) ){
						$ret .= '<div class="gdlr-core-personnel-list-position gdlr-core-info-font" ' . gdlr_core_esc_style($position_atts) . ' >' . gdlr_core_text_filter($post_meta['position']) . '</div>';
					}
					if( (empty($settings['enable-social-shortcode']) || $settings['enable-social-shortcode'] == 'enable') && !empty($post_meta['social-shortcode']) ){
						$ret .= '<div class="gdlr-core-personnel-list-social" >' . gdlr_core_content_filter($post_meta['social-shortcode']) . '</div>';
					}
					$ret .= ($settings['style'] == 'grid-feature')? '</div>': '';
				}
				$ret .= '</div>'; // gdlr-core-personnel-list-content-wrap

				$ret .= '</div>'; // gdlr-core-personnel-list

				return $ret;
			}
			
		} // gdlr_core_pb_element_personnel
	} // class_exists	

	add_action('wp_ajax_gdlr_core_personnel_ajax', 'gdlr_core_personnel_ajax');
	add_action('wp_ajax_nopriv_gdlr_core_personnel_ajax', 'gdlr_core_personnel_ajax');
	if( !function_exists('gdlr_core_personnel_ajax') ){
		function gdlr_core_personnel_ajax(){

			if( !empty($_POST['settings']) ){

				$settings = $_POST['settings'];
				if( !empty($_POST['option']['name']) && !empty($_POST['option']['value']) ){	
					if( in_array($_POST['option']['name'], array('paged', 'category')) ){ 
						$settings[$_POST['option']['name']] = $_POST['option']['value'];

						if( $_POST['option']['name'] == 'category' ){
							$settings['paged'] = 1;
						}
					}
				}else{
					$settings['paged'] = 1;
				}

				$query = gdlr_core_pb_element_personnel::personnel_query($settings);	

				$ret = array(
					'status'=> 'success',
					'content'=> gdlr_core_pb_element_personnel::personnel_list_content($query, $settings)
				);

				die(json_encode($ret));
			}else{
				die(json_encode(array(
					'status'=> 'failed',
					'message'=> esc_html__('Settings variable is not defined.', 'goodlayers-core-personnel')
				)));
			}

		} // gdlr_core_post_load_more
	} // function_exists