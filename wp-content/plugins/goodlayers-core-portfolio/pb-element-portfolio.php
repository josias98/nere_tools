<?php
	/*	
	*	Goodlayers Item For Page Builder
	*/

	add_action('plugins_loaded', 'gdlr_core_portfolio_add_pb_element');
	if( !function_exists('gdlr_core_portfolio_add_pb_element') ){
		function gdlr_core_portfolio_add_pb_element(){

			if( class_exists('gdlr_core_page_builder_element') ){
				gdlr_core_page_builder_element::add_element('portfolio', 'gdlr_core_pb_element_portfolio'); 
			}
			
		}
	}
	
	if( !class_exists('gdlr_core_pb_element_portfolio') ){
		class gdlr_core_pb_element_portfolio{
			
			// get the element settings
			static function get_settings(){
				return array(
					'icon' => 'fa-outdent',
					'title' => esc_html__('Portfolio', 'goodlayers-core-portfolio')
				);
			}
			
			// return the element options
			static function get_options(){
				global $gdlr_core_item_pdb;
				
				return apply_filters('gdlr_core_portfolio_item_options', array(					
					'general' => array(
						'title' => esc_html__('General', 'goodlayers-core-portfolio'),
						'options' => array(

							'category' => array(
								'title' => esc_html__('Category', 'goodlayers-core-portfolio'),
								'type' => 'multi-combobox',
								'options' => gdlr_core_get_term_list('portfolio_category'),
								'description' => esc_html__('You can use Ctrl/Command button to select multiple items or remove the selected item. Leave this field blank to select all items in the list.', 'goodlayers-core-portfolio'),
							),
							'tag' => array(
								'title' => esc_html__('Tag', 'goodlayers-core-portfolio'),
								'type' => 'multi-combobox',
								'options' => gdlr_core_get_term_list('portfolio_tag')
							),
							'relation' => array(
								'title' => esc_html__('Relation (Category & Tag)', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => array(
									'or' => esc_html__('OR', 'goodlayers-core-portfolio'),
									'and' => esc_html__('AND', 'goodlayers-core-portfolio')
								)
							),
							'num-fetch' => array(
								'title' => esc_html__('Num Fetch', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'number',
								'default' => 9,
								'description' => esc_html__('The number of posts showing on the blog item', 'goodlayers-core-portfolio')
							),
							'orderby' => array(
								'title' => esc_html__('Order By', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => array(
									'date' => esc_html__('Publish Date', 'goodlayers-core-portfolio'), 
									'title' => esc_html__('Title', 'goodlayers-core-portfolio'), 
									'rand' => esc_html__('Random', 'goodlayers-core-portfolio'), 
									'menu_order' => esc_html__('Menu Order', 'goodlayers-core-portfolio'), 
								)
							),
							'order' => array(
								'title' => esc_html__('Order', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => array(
									'desc'=>esc_html__('Descending Order', 'goodlayers-core-portfolio'), 
									'asc'=> esc_html__('Ascending Order', 'goodlayers-core-portfolio'), 
								)
							),
							'filterer' => array(
								'title' => esc_html__('Category Filterer', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => array(
									'none'=>esc_html__('None', 'goodlayers-core-portfolio'), 
									'text'=>esc_html__('Filter Text Style', 'goodlayers-core-portfolio'), 
									'text-slide'=>esc_html__('Filter Text Style With Slide Bar', 'goodlayers-core-portfolio'), 
									'text-slide2'=>esc_html__('Filter Text Style With Slide Dot', 'goodlayers-core-portfolio'), 
									'button'=>esc_html__('Filter Button Style', 'goodlayers-core-portfolio'), 
								),
								'description' => esc_html__('Filter is not supported and will be automatically disabled on carousel layout.', 'goodlayers-core-portfolio'),
							),
							'filterer-align' => array(
								'title' => esc_html__('Filterer Alignment', 'goodlayers-core-portfolio'),
								'type' => 'radioimage',
								'options' => 'text-align',
								'default' => 'center',
								'condition' => array('filterer' => array('text', 'button', 'text-slide', 'text-slide2'))
							),
							'pagination' => array(
								'title' => esc_html__('Pagination', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => array(
									'none'=>esc_html__('None', 'goodlayers-core-portfolio'), 
									'page'=>esc_html__('Page', 'goodlayers-core-portfolio'), 
									'load-more'=>esc_html__('Load More', 'goodlayers-core-portfolio'), 
								),
								'description' => esc_html__('Pagination is not supported and will be automatically disabled on carousel layout.', 'goodlayers-core-portfolio'),
							),
							'pagination-style' => array(
								'title' => esc_html__('Pagination Style', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => array(
									'default' => esc_html__('Default', 'goodlayers-core-portfolio'),
									'plain' => esc_html__('Plain', 'goodlayers-core-portfolio'),
									'rectangle' => esc_html__('Rectangle', 'goodlayers-core-portfolio'),
									'rectangle-border' => esc_html__('Rectangle Border', 'goodlayers-core-portfolio'),
									'round' => esc_html__('Round', 'goodlayers-core-portfolio'),
									'round-border' => esc_html__('Round Border', 'goodlayers-core-portfolio'),
									'circle' => esc_html__('Circle', 'goodlayers-core-portfolio'),
									'circle-border' => esc_html__('Circle Border', 'goodlayers-core-portfolio'),
								),
								'default' => 'default',
								'condition' => array( 'pagination' => 'page' )
							),
							'pagination-align' => array(
								'title' => esc_html__('Pagination Alignment', 'goodlayers-core-portfolio'),
								'type' => 'radioimage',
								'options' => 'text-align',
								'with-default' => true,
								'default' => 'default',
								'condition' => array( 'pagination' => 'page' )
							),
							'view-all-works-button' => array(
								'title' => esc_html__('Enable View All Works Button', 'goodlayers-core-portfolio'),
								'type' => 'checkbox',
								'default' => 'disable',
								'description' => esc_html__('This button will also shows direction navigation for carousel style as well.', 'goodlayers-core-portfolio')
							),
							'view-all-works-text' => array(
								'title' => esc_html__('View All Works Text', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'default' => esc_html__('View All Works', 'goodlayers-core-portfolio'),
								'condition' => array( 'view-all-works-button' => 'enable' )
							),
							'view-all-works-link' => array(
								'title' => esc_html__('View All Works Link', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'condition' => array( 'view-all-works-button' => 'enable' )
							),
							
						),
					),
					'settings' => array(
						'title' => esc_html__('Portfolio Style', 'goodlayers-core-portfolio'),
						'options' => array(
							'portfolio-style' => array(
								'title' => esc_html__('Portfolio Style', 'goodlayers-core-portfolio'),
								'type' => 'radioimage',
								'options' => array(
									'modern' => plugins_url('', __FILE__) . '/images/modern.png',
									'modern-no-space' => plugins_url('', __FILE__) . '/images/modern-no-space.png',
									'grid' => plugins_url('', __FILE__) . '/images/grid.png',
									'grid-no-space' => plugins_url('', __FILE__) . '/images/grid-no-space.png',
									'grid2' => plugins_url('', __FILE__) . '/images/grid2.jpg',
									'modern-desc' => plugins_url('', __FILE__) . '/images/modern-desc.png',
									'modern-desc-no-space' => plugins_url('', __FILE__) . '/images/modern-desc-no-space.png',
									'metro' => plugins_url('', __FILE__) . '/images/metro.png',
									'metro-no-space' => plugins_url('', __FILE__) . '/images/metro-no-space.png',
									'fixed-metro' => plugins_url('', __FILE__) . '/images/fixed-metro.png',
									'medium' => plugins_url('', __FILE__) . '/images/medium.png',
									'medium-feature' => plugins_url('', __FILE__) . '/images/medium-feature.png',
								),
								'default' => 'modern',
								'wrapper-class' => 'gdlr-core-fullsize'
							),
							'portfolio-grid-text-align' => array(
								'title' => esc_html__('Text Align', 'goodlayers-core-portfolio'),
								'type' => 'radioimage',
								'options' => 'text-align',
								'default' => 'left',
								'condition' => array( 'portfolio-style' => array( 'grid', 'grid2', 'grid-no-space' ) )
							),
							'portfolio-grid-style' => array(
								'title' => esc_html__('Content Style', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => array(
									'normal' => esc_html__('Normal', 'goodlayers-core-portfolio'),
									'with-frame' => esc_html__('With Frame', 'goodlayers-core-portfolio'),
									'with-bottom-border' => esc_html__('With Bottom Border', 'goodlayers-core-portfolio'),
								),
								'default' => 'normal',
								'condition' => array( 'portfolio-style' => array( 'grid', 'grid-no-space' ) )
							),
							'portfolio-frame-opacity' => array(
								'title' => esc_html__('Frame Opacity', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'default' => '1',
								'description' => esc_html__('Fill the decimal number between 0.01 to 1', 'goodlayers-core-portfolio'),
								'condition' => array( 'portfolio-style' => array( 'grid', 'grid-no-space' ), 'portfolio-grid-style' => 'with-frame' )
							),
							'enable-portfolio-title' => array(
								'title' => esc_html__('Enable Portfolio Title', 'goodlayers-core-portfolio'),
								'type' => 'checkbox',
								'default' => 'enable',
								'condition' => array( 'portfolio-style' => array( 'grid', 'grid2', 'grid-no-space', 'medium', 'medium-feature' ) )
							),
							'enable-portfolio-tag' => array(
								'title' => esc_html__('Enable Portfolio Tag', 'goodlayers-core-portfolio'),
								'type' => 'checkbox',
								'default' => 'enable',
								'condition' => array( 'portfolio-style' => array( 'grid', 'grid2', 'grid-no-space', 'modern-desc', 'modern-desc-no-space', 'medium', 'medium-feature' ) )
							),
							'enable-portfolio-date' => array(
								'title' => esc_html__('Enable Portfolio Date', 'goodlayers-core-portfolio'),
								'type' => 'checkbox',
								'default' => 'disable',
								'condition' => array( 'portfolio-style' => array( 'grid', 'grid-no-space' ) )
							),
							'portfolio-medium-size' => array(
								'title' => esc_html__('Thumbnail Size', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => array(
									'small' => esc_html__('Small', 'goodlayers-core-portfolio'),
									'large' => esc_html__('Large', 'goodlayers-core-portfolio'),
								),
								'condition' => array( 'portfolio-style' => 'medium' )
							),
							'portfolio-medium-style' => array(
								'title' => esc_html__('Thumbnail Style', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => array(
									'left' => esc_html__('Left', 'goodlayers-core-portfolio'),
									'right' => esc_html__('Right', 'goodlayers-core-portfolio'),
									'switch' => esc_html__('Switch ( Between Left and Right )', 'goodlayers-core-portfolio'),
								),
								'condition' => array( 'portfolio-style' => 'medium' )
							),
							'hover' => array(
								'title' => esc_html__('Hover Style', 'goodlayers-core-portfolio'),
								'type' => 'radioimage',
								'options' => array(
									'title' => plugins_url('', __FILE__) . '/images/hover/title.png',
									'title-icon' => plugins_url('', __FILE__) . '/images/hover/title-icon.png',
									'title-tag' => plugins_url('', __FILE__) . '/images/hover/title-tag.png',
									'title-tag-bottom' => plugins_url('', __FILE__) . '/images/hover/title-tag-bottom.png',
									'title-tag-slide' => plugins_url('', __FILE__) . '/images/hover/title-tag-slide.png',
									'tag-title' => plugins_url('', __FILE__) . '/images/hover/tag-title.png',
									'title-date' => plugins_url('', __FILE__) . '/images/hover/title-date.jpg',
									'icon-title-tag' => plugins_url('', __FILE__) . '/images/hover/icon-title-tag.png',
									'icon-title_bottom' => plugins_url('', __FILE__) . '/images/hover/icon-title_bottom.jpg',
									'icon' => plugins_url('', __FILE__) . '/images/hover/icon.png',
									'margin-title' => plugins_url('', __FILE__) . '/images/hover/margin-title.png',
									'margin-title-icon' => plugins_url('', __FILE__) . '/images/hover/margin-title-icon.png',
									'margin-title-tag' => plugins_url('', __FILE__) . '/images/hover/margin-title-tag.png',
									'margin-icon-title-tag' => plugins_url('', __FILE__) . '/images/hover/margin-icon-title-tag.png',
									'margin-icon' => plugins_url('', __FILE__) . '/images/hover/margin-icon.png',
									'none' => plugins_url('', __FILE__) . '/images/hover/none.png',
								),
								'default' => 'title-icon',
								'max-width' => '100px',
								'condition' => array( 'portfolio-style' => array('modern', 'modern-no-space', 'grid', 'grid-no-space', 'fixed-metro', 'metro', 'metro-no-space', 'medium', 'medium-feature') ),
								'wrapper-class' => 'gdlr-core-fullsize'
							),
							'column-size' => array(
								'title' => esc_html__('Column Size', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => array( 60=>1, 30=>2, 20=>3, 15=>4, 12=>5, 10=>6 ),
								'default' => 20,
								'condition' => array( 'portfolio-style' => array('modern', 'modern-no-space', 'grid', 'grid2', 'grid-no-space', 'modern-desc', 'modern-desc-no-space', 'metro', 'metro-no-space') )
							),
							'thumbnail-size' => array(
								'title' => esc_html__('Thumbnail Size', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => 'thumbnail-size'
							),
							'enable-thumbnail-zoom-on-hover' => array(
								'title' => esc_html__('Thumbnail Zoom on Hover', 'goodlayers-core-portfolio'),
								'type' => 'checkbox',
								'default' => 'enable',
							),
							'enable-thumbnail-grayscale-effect' => array(
								'title' => esc_html__('Enable Thumbnail Grayscale Effect', 'goodlayers-core-portfolio'),
								'type' => 'checkbox',
								'default' => 'disable',
								'description' => esc_html__('Only works with browser that supports css3 filter ( http://caniuse.com/#feat=css-filters ).', 'goodlayers-core-portfolio')
							),
							'enable-badge' => array(
								'title' => esc_html__('Enable Badge', 'goodlayers-core-portfolio'),
								'type' => 'checkbox',
								'default' => 'enable',
								'description' => esc_html__('You can enable badge for each portfolio at the page option area.', 'goodlayers-core-portfolio')
							),
							'layout' => array(
								'title' => esc_html__('Layout', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => array( 
									'fitrows' => esc_html__('Fit Rows', 'goodlayers-core-portfolio'),
									'carousel' => esc_html__('Carousel', 'goodlayers-core-portfolio'),
									'masonry' => esc_html__('Masonry', 'goodlayers-core-portfolio'),
								),
								'default' => 'fitrows',
								'condition' => array( 'portfolio-style' => array('modern', 'modern-no-space', 'grid', 'grid2', 'grid-no-space', 'modern-desc', 'modern-desc-no-space', 'medium', 'medium-feature') )
							),
							'carousel-item-margin' => array(
								'title' => esc_html__('Carousel Item Margin', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'condition' => array( 'layout' => 'carousel' )
							),
							'carousel-overflow' => array(
								'title' => esc_html__('Carousel Overflow', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => array(
									'' => esc_html__('Hidden', 'goodlayers-core-portfolio'),
									'visible' => esc_html__('Visible', 'goodlayers-core-portfolio')
								),
								'condition' => array( 'carousel' => 'enable' )
							),
							'carousel-autoslide' => array(
								'title' => esc_html__('Autoslide Carousel', 'goodlayers-core-portfolio'),
								'type' => 'checkbox',
								'default' => 'enable',
								'condition' => array( 'portfolio-style' => array('modern', 'modern-no-space', 'grid', 'grid-no-space', 'modern-desc', 'modern-desc-no-space', 'medium', 'medium-feature'), 'layout' => 'carousel' )
							),
							'carousel-start-at' => array(
								'title' => esc_html__('Carousel Start At (Number)', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'default' => '',
								'condition' => array( 'portfolio-style' => array('modern', 'modern-no-space', 'grid', 'grid-no-space', 'modern-desc', 'modern-desc-no-space', 'medium', 'medium-feature'), 'layout' => 'carousel' )
							),
							'carousel-scrolling-item-amount' => array(
								'title' => esc_html__('Carousel Scrolling Item Amount', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'default' => '1',
								'condition' => array( 'portfolio-style' => array('modern', 'modern-no-space', 'grid', 'grid-no-space', 'modern-desc', 'modern-desc-no-space', 'medium', 'medium-feature'), 'layout' => 'carousel' )
							),
							'carousel-navigation' => array(
								'title' => esc_html__('Carousel Navigation', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => (function_exists('gdlr_core_get_flexslider_navigation_types')? gdlr_core_get_flexslider_navigation_types(): array()),
								'default' => 'navigation',
								'condition' => array( 'portfolio-style' => array('modern', 'modern-no-space', 'grid', 'grid-no-space', 'modern-desc', 'modern-desc-no-space', 'medium', 'medium-feature'), 'layout' => 'carousel' )
							),
							'carousel-navigation-show-on-hover' => array(
								'title' => esc_html__('Carousel Navigation Display On Hover', 'goodlayers-core-portfolio'),
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
								'title' => esc_html__('Carousel Bullet Style', 'goodlayers-core-portfolio'),
								'type' => 'radioimage',
								'options' => (function_exists('gdlr_core_get_flexslider_bullet_itypes')? gdlr_core_get_flexslider_bullet_itypes(): array()),
								'condition' => array( 'layout' => 'carousel', 'carousel-navigation' => array('bullet','both') ),
								'wrapper-class' => 'gdlr-core-fullsize'
							),
							'carousel-bullet-top-margin' => array(
								'title' => esc_html__('Carousel Bullet Top Margin', 'goodlayers-core'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'condition' => array( 'layout' => 'carousel', 'carousel-navigation' => array('bullet','both') )
							),
							'excerpt' => array(
								'title' => esc_html__('Excerpt Type', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => array(
									'specify-number' => esc_html__('Specify Number', 'goodlayers-core-portfolio'),
									'show-all' => esc_html__('Show All ( use <!--more--> tag to cut the content )', 'goodlayers-core-portfolio'),
									'none' => esc_html__('Disable Exceprt', 'goodlayers-core-portfolio'),
								),
								'default' => 'specify-number',
								'condition' => array( 'portfolio-style' => array( 'grid', 'grid-no-space', 'modern-desc', 'modern-desc-no-space', 'medium', 'medium-feature' ) )
							),
							'excerpt-number' => array(
								'title' => esc_html__('Excerpt Number', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'default' => 55,
								'condition' => array( 'portfolio-style' => array( 'grid', 'grid-no-space', 'modern-desc', 'modern-desc-no-space', 'medium', 'medium-feature' ), 'excerpt' => 'specify-number' )
							),
							'read-more-button' => array(
								'title' => esc_html__('Read More Text', 'goodlayers-core-portfolio'),
								'type' => 'checkbox',
								'default' => 'disable',
								'condition' => array( 'portfolio-style' => array( 'grid', 'grid-no-space', 'medium', 'medium-feature' ), 'excerpt' => 'specify-number' )
							),
						),
					),
					'typography' => array(
						'title' => esc_html__('Typography', 'goodlayers-core-portfolio'),
						'options' => array(
							'filter-font-size' => array(
								'title' => esc_html__('Portfolio Filter Font Size', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'filter-font-weight' => array(
								'title' => esc_html__('Portfolio Filter Font Weight', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800', 'goodlayers-core-portfolio')
							),
							'filter-letter-spacing' => array(
								'title' => esc_html__('Portfolio Filter Letter Spacing', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'filter-text-transform' => array(
								'title' => esc_html__('Portfolio Filter Text Transform', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'data-type' => 'text',
								'options' => array(
									'uppercase' => esc_html__('Uppercase', 'goodlayers-core-portfolio'),
									'lowercase' => esc_html__('Lowercase', 'goodlayers-core-portfolio'),
									'capitalize' => esc_html__('Capitalize', 'goodlayers-core-portfolio'),
									'none' => esc_html__('None', 'goodlayers-core-portfolio'),
								),
								'default' => 'uppercase'
							),
							'portfolio-title-font-size' => array(
								'title' => esc_html__('Portfolio Title Font Size', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'portfolio-title-font-weight' => array(
								'title' => esc_html__('Portfolio Title Font Weight', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800', 'goodlayers-core-portfolio')
							),
							'portfolio-title-letter-spacing' => array(
								'title' => esc_html__('Portfolio Title Letter Spacing', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'portfolio-title-text-transform' => array(
								'title' => esc_html__('Portfolio Title Text Transform', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'data-type' => 'text',
								'options' => array(
									'uppercase' => esc_html__('Uppercase', 'goodlayers-core-portfolio'),
									'lowercase' => esc_html__('Lowercase', 'goodlayers-core-portfolio'),
									'capitalize' => esc_html__('Capitalize', 'goodlayers-core-portfolio'),
									'none' => esc_html__('None', 'goodlayers-core-portfolio'),
								),
								'default' => 'uppercase'
							),
							'portfolio-tag-font-size' => array(
								'title' => esc_html__('Portfolio Tag Font Size', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'portfolio-tag-font-style' => array(
								'title' => esc_html__('Portfolio Tag Font Style', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'options' => array(
									'' => esc_html__('Default', 'goodlayers-core-portfolio'),
									'normal' => esc_html__('Normal', 'goodlayers-core-portfolio'),
									'italic' => esc_html__('Italic', 'goodlayers-core-portfolio'),
								),
								'default' => 'default'
							),
							'portfolio-tag-text-transform' => array(
								'title' => esc_html__('Portfolio Tag Text Transform', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'data-type' => 'text',
								'options' => array(
									'' => esc_html__('Default', 'goodlayers-core-portfolio'),
									'uppercase' => esc_html__('Uppercase', 'goodlayers-core-portfolio'),
									'lowercase' => esc_html__('Lowercase', 'goodlayers-core-portfolio'),
									'capitalize' => esc_html__('Capitalize', 'goodlayers-core-portfolio'),
									'none' => esc_html__('None', 'goodlayers-core-portfolio'),
								),
								'default' => ''
							),
							'portfolio-hover-title-font-size' => array(
								'title' => esc_html__('Portfolio Hover Title Font Size', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'portfolio-hover-title-font-weight' => array(
								'title' => esc_html__('Portfolio Hover Title Font Weight', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800', 'goodlayers-core-portfolio')
							),
							'portfolio-hover-title-letter-spacing' => array(
								'title' => esc_html__('Portfolio Hover Title Letter Spacing', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'portfolio-hover-title-text-transform' => array(
								'title' => esc_html__('Portfolio Hover Title Text Transform', 'goodlayers-core-portfolio'),
								'type' => 'combobox',
								'data-type' => 'text',
								'options' => array(
									'uppercase' => esc_html__('Uppercase', 'goodlayers-core-portfolio'),
									'lowercase' => esc_html__('Lowercase', 'goodlayers-core-portfolio'),
									'capitalize' => esc_html__('Capitalize', 'goodlayers-core-portfolio'),
									'none' => esc_html__('None', 'goodlayers-core-portfolio'),
								),
								'default' => 'uppercase'
							),
						)
					),
					'spacing' => array(
						'title' => esc_html__('Frame/Spacing', 'goodlayers-core-portfolio'),
						'options' => array(
							'filterer-top-margin' => array(
								'title' => esc_html__('Filter Top Margin ( If Any )', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'filterer-bottom-margin' => array(
								'title' => esc_html__('Filter Bottom Margin ( If Any )', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'description' => esc_html__('Leave this field blank for default value', 'goodlayers-core-portfolio')
							),
							'portfolio-frame-padding' => array(
								'title' => esc_html__('Portfolio Frame Padding', 'goodlayers-core-portfolio'),
								'type' => 'custom',
								'item-type' => 'padding',
								'data-input-type' => 'pixel',
								'default' => array( 'top'=>'', 'right'=>'', 'bottom'=>'', 'left'=>'', 'settings'=>'unlink' ),
							),
							'portfolio-border-radius' => array(
								'title' => esc_html__('Portfolio Frame/Thumbnail Border Radius', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'portfolio-frame-border-size' => array(
								'title' => esc_html__('Portfolio Frame Border Size', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'descripiton' => esc_html__('Only effects the "Column With Frame" style', 'goodlayers-core-portfolio')
							),
							'portfolio-frame-border-color' => array(
								'title' => esc_html__('Portfolio Frame Border Color', 'goodlayers-core-portfolio'),
								'type' => 'colorpicker',
								'descripiton' => esc_html__('Only effects the "Column With Frame" style', 'goodlayers-core-portfolio')
							),
							'portfolio-frame-hover-border-color' => array(
								'title' => esc_html__('Portfolio Frame Hover Border Color', 'goodlayers-core-portfolio'),
								'type' => 'colorpicker',
								'data-input-type' => 'pixel',
								'description' => esc_html__('Only For Blog Column With Frame Style', 'goodlayers-core-portfolio')
							),
							'portfolio-item-bottom-margin' => array(
								'title' => esc_html__('Portfolio Item Bottom Margin', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'description' => esc_html__('For Column / Left Thumbnail style', 'goodlayers-core-portfolio')
							),
							'portfolio-title-bottom-margin' => array(
								'title' => esc_html__('Portfolio Title Bottom Margin', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
							),
							'pagination-top-margin' => array(
								'title' => esc_html__('Load More/Pagination Top Margin', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'description' => esc_html__('Leave this field blank for default value', 'goodlayers-core-portfolio')
							),
							'padding-bottom' => array(
								'title' => esc_html__('Padding Bottom ( All )', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'default' => $gdlr_core_item_pdb
							)
						)
					),
					'color' => array(
						'title' => esc_html__('Color/Shadow', 'goodlayers-core-portfolio'),
						'options' => array(
							'overlay-color' => array(
								'title' => esc_html__('Image Overlay Color', 'goodlayers-core-portfolio'),
								'type' => 'colorpicker'
							),
							'overlay-opacity' => array(
								'title' => esc_html__('Image Overlay Opacity', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'description' =>  esc_html__('Fill the number between 0.01 to 1', 'goodlayers-core-portfolio') . '. ' .
									esc_html__('You need to specify the "Image Overlay Color" to use this option', 'goodlayers-core-portfolio')
							),
							'frame-shadow-size' => array(
								'title' => esc_html__('Shadow Size ( for image/frame )', 'goodlayers-core-portfolio'),
								'type' => 'custom',
								'item-type' => 'padding',
								'options' => array('x', 'y', 'size'),
								'data-input-type' => 'pixel',
							),
							'frame-shadow-color' => array(
								'title' => esc_html__('Shadow Color ( for image/frame )', 'goodlayers-core-portfolio'),
								'type' => 'colorpicker'
							),
							'frame-shadow-opacity' => array(
								'title' => esc_html__('Shadow Opacity ( for image/frame )', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'default' => '0.2',
								'description' => esc_html__('Fill the number between 0.01 to 1', 'goodlayers-core-portfolio')
							),
							'enable-move-up-shadow-effect' => array(
								'title' => esc_html__('Move Up Shadow Hover Effect', 'goodlayers-core-portfolio'),
								'type' => 'checkbox',
								'default' => 'disable',
								'descripiton' => esc_html__('Only effects the "Column With Frame" style', 'goodlayers-core-portfolio')
							),
							'move-up-effect-length' => array(
								'title' => esc_html__('Move Up Hover Effect Length', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'condition' => array( 'enable-move-up-shadow-effect' => 'enable' )
							),
							'frame-hover-shadow-size' => array(
								'title' => esc_html__('Shadow Hover Size ( for image/frame )', 'goodlayers-core-portfolio'),
								'type' => 'custom',
								'item-type' => 'padding',
								'options' => array('x', 'y', 'size'),
								'data-input-type' => 'pixel',
								'condition' => array( 'enable-move-up-shadow-effect' => 'enable' )
							),
							'frame-hover-shadow-color' => array(
								'title' => esc_html__('Shadow Hover Color ( for image/frame )', 'goodlayers-core-portfolio'),
								'type' => 'colorpicker',
								'condition' => array( 'enable-move-up-shadow-effect' => 'enable' )
							),
							'frame-hover-shadow-opacity' => array(
								'title' => esc_html__('Shadow Hover Opacity ( for image/frame )', 'goodlayers-core-portfolio'),
								'type' => 'text',
								'default' => '0.2',
								'description' => esc_html__('Fill the number between 0.01 to 1', 'goodlayers-core-portfolio'),
								'condition' => array( 'enable-move-up-shadow-effect' => 'enable' )
							),
						)
					),
					'item-title' => array(
						'title' => esc_html__('Item Title', 'goodlayers-core-portfolio'),
						'options' => gdlr_core_block_item_option()
					)
				));
			}

			// get the preview for page builder
			static function get_preview( $settings = array() ){
				$content  = self::get_content($settings, true);
				$id = mt_rand(0, 9999);
				
				ob_start();
?><script type="text/javascript" id="gdlr-core-preview-portfolio-<?php echo esc_attr($id); ?>" >
if( document.readyState == 'complete' ){
	jQuery(document).ready(function(){
		var portfolio_preview = jQuery('#gdlr-core-preview-portfolio-<?php echo esc_attr($id); ?>').parent();
			portfolio_preview.gdlr_core_lightbox().gdlr_core_flexslider().gdlr_core_isotope().gdlr_core_fluid_video().gdlr_core_ajax_slide_bar();

		new gdlr_core_sync_height(portfolio_preview);
	});
}else{
	jQuery(window).load(function(){
		var portfolio_preview = jQuery('#gdlr-core-preview-portfolio-<?php echo esc_attr($id); ?>').parent();
			portfolio_preview.gdlr_core_lightbox().gdlr_core_flexslider().gdlr_core_isotope().gdlr_core_fluid_video().gdlr_core_ajax_slide_bar();

		new gdlr_core_sync_height(portfolio_preview);
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
						'category' => '', 'tag' => '', 'num-fetch' => '9', 'thumbnail-size' => 'full', 'orderby' => 'date', 'order' => 'desc', 'pagination' => 'none',
						'portfolio-style' => 'modern', 'hover' => 'title-icon', 'excerpt' => 'specify-number', 'excerpt-number' => 55, 'show-read-more' => 'enable', 'column-size' => 20,
						'show-thumbnail' => 'enable',
						'padding-bottom' => $gdlr_core_item_pdb
					);
				}
				
				$settings['portfolio-style'] = empty($settings['portfolio-style'])? 'modern': $settings['portfolio-style'];
				$settings['no-space'] = (strpos($settings['portfolio-style'], 'no-space') !== false)? 'yes': 'no';
				$settings['hover-info'] = empty($settings['hover'])? array(): explode('-', $settings['hover']);
				$settings['layout'] = empty($settings['layout'])? 'fitrows': $settings['layout'];
				if( in_array($settings['portfolio-style'], array('modern', 'modern-no-space', 'grid', 'grid2', 'grid-no-space', 'modern-desc', 'modern-desc-no-space')) ){
					$settings['has-column'] = 'yes';
				}else if( in_array($settings['portfolio-style'], array('metro', 'metro-no-space')) ){
					$settings['has-column'] = 'yes';
					$settings['layout'] = 'masonry';
				}else if( $settings['portfolio-style'] == 'fixed-metro' ){
					$settings['has-column'] = 'yes';
					$settings['layout'] = 'fitrows';
				}else{
					$settings['has-column'] = 'no';
					$settings['column-size'] = 60;
					if( $settings['layout'] == 'masonry' ){
						$settings['layout'] = 'fitrows';
					}else if( $settings['layout'] == 'carousel' ){
						if( !in_array($settings['portfolio-style'], array('medium', 'medium-feature')) ){
							$settings['layout'] = 'fitrows';
						}
					}
				}
				
				// custom css
				$custom_style  = '';
				if( $settings['portfolio-style'] == 'grid2' ){
					if( !empty($settings['portfolio-frame-hover-border-color']) ){
						$custom_style .= '#custom_style_id .gdlr-core-portfolio-grid2:hover{ border-color: ' . $settings['portfolio-frame-hover-border-color'] . ' !important; }';
					}
					if( !empty($settings['enable-move-up-shadow-effect']) && $settings['enable-move-up-shadow-effect'] == 'enable' ){
						$custom_style_temp = gdlr_core_esc_style(array(
							'background-shadow-size' => empty($settings['frame-hover-shadow-size'])? '': $settings['frame-hover-shadow-size'],
							'background-shadow-color' => empty($settings['frame-hover-shadow-color'])? '': $settings['frame-hover-shadow-color'],
							'background-shadow-opacity' => empty($settings['frame-hover-shadow-opacity'])? '': $settings['frame-hover-shadow-opacity'],
						), false);

						if( !empty($settings['move-up-effect-length']) ){
							$custom_style_temp .= 'transform: translate3d(0, -' . $settings['move-up-effect-length'] . ', 0); ';
						}

						if( !empty($custom_style_temp) ){
							$custom_style .= '#custom_style_id .gdlr-core-move-up-with-shadow:hover{ ' . $custom_style_temp . ' }';
						}
					}
				}
				if( !empty($custom_style) ){
					if( empty($settings['id']) ){
						global $gdlr_core_portfolio_id; 
						$gdlr_core_portfolio_id = empty($gdlr_core_portfolio_id)? array(): $gdlr_core_portfolio_id;

						// generate unique id so it does not get overwritten in admin area
						$rnd_portfolio_id = mt_rand(0, 99999);
						while( in_array($rnd_portfolio_id, $gdlr_core_portfolio_id) ){
							$rnd_portfolio_id = mt_rand(0, 99999);
						}
						$gdlr_core_portfolio_id[] = $rnd_portfolio_id;
						$settings['id'] = 'gdlr-core-portfolio-' . $rnd_portfolio_id;
					}

					$custom_style = str_replace('custom_style_id', $settings['id'], $custom_style); 
					if( $preview ){
						$custom_style = '<style>' . $custom_style . '</style>';
					}else{
						gdlr_core_add_inline_style($custom_style);
						$custom_style = '';
					}
				}

				// process
				$extra_class  = ' gdlr-core-portfolio-item-style-' . $settings['portfolio-style'];

				$title_settings = $settings;
				if( $settings['no-space'] == 'yes' || $settings['layout'] == 'carousel' ){
					$title_settings['pdlr'] = false;
					$extra_class .= ' gdlr-core-item-pdlr';
				}

				// view all works button
				$view_all_works = '';
				if( !empty($settings['view-all-works-button']) && $settings['view-all-works-button'] == 'enable' &&
					!empty($settings['view-all-works-text']) && !empty($settings['view-all-works-link']) ){
					$settings['flexslider-nav-type'] = 'custom';

					$view_all_works  = '<div class="gdlr-core-portfolio-view-all-works" >';
					if( empty($settings['carousel-navigation']) || in_array($settings['carousel-navigation'], array('navigation', 'both')) ){
						$view_all_works .= '<i class="arrow_carrot-left flex-prev"></i>';
					}
					$view_all_works .= '<a class="gdlr-core-button gdlr-core-button-transparent gdlr-core-button-with-border" ';
					$view_all_works .= 'href="' . esc_url($settings['view-all-works-link']) . '" >';
					$view_all_works .= gdlr_core_text_filter($settings['view-all-works-text']);
					$view_all_works .= '</a>';
					if( empty($settings['carousel-navigation']) || in_array($settings['carousel-navigation'], array('navigation', 'both')) ){
						$view_all_works .= '<i class="arrow_carrot-right flex-next"></i>';
					}
					$view_all_works .= '</div>';
				}else{
					if( $settings['layout'] == 'carousel' ){
						if( empty($settings['carousel-navigation']) || in_array($settings['carousel-navigation'], array('navigation', 'both')) ){
							$title_settings['carousel'] = 'enable';
						}
					}
				}
				
				// start printing item
				$ret  = '<div class="gdlr-core-portfolio-item gdlr-core-item-pdb clearfix ' . esc_attr($extra_class) . '" ';
				if( !empty($settings['padding-bottom']) && $settings['padding-bottom'] != $gdlr_core_item_pdb ){
					$ret .= gdlr_core_esc_style(array('padding-bottom'=>$settings['padding-bottom']));
				}
				if( !empty($settings['id']) ){
					$ret .= ' id="' . esc_attr($settings['id']) . '" ';
				}
				$ret .= ' >';

				// print title
				$ret .= gdlr_core_block_item_title($title_settings);
				
				// print portfolio item
				$portfolio_item = new gdlr_core_portfolio_item($settings);
				$ret .= $portfolio_item->get_content();
				

				$ret .= $view_all_works;
				$ret .= '</div>'; // gdlr-core-portfolio-item
				$ret .= $custom_style;

				return $ret;
			}			
			
		} // gdlr_core_pb_element_portfolio
	} // class_exists	