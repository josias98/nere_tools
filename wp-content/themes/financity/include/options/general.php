<?php
	/*	
	*	Goodlayers Option
	*	---------------------------------------------------------------------
	*	This file store an array of theme options
	*	---------------------------------------------------------------------
	*/	

	// add custom css for theme option
	add_filter('gdlr_core_theme_option_top_file_write', 'financity_gdlr_core_theme_option_top_file_write', 10, 2);
	if( !function_exists('financity_gdlr_core_theme_option_top_file_write') ){
		function financity_gdlr_core_theme_option_top_file_write( $css, $option_slug ){
			if( $option_slug != 'goodlayers_main_menu' ) return;

			ob_start();
?>
.financity-body h1, .financity-body h2, .financity-body h3, .financity-body h4, .financity-body h5, .financity-body h6{ margin-top: 0px; margin-bottom: 20px; line-height: 1.2; font-weight: 700; }
#poststuff .gdlr-core-page-builder-body h2{ padding: 0px; margin-bottom: 20px; line-height: 1.2; font-weight: 700; }
#poststuff .gdlr-core-page-builder-body h1{ padding: 0px; font-weight: 700; }

.financity-body .gdlr-core-portfolio-item .gdlr-core-portfolio-read-more{ font-weight: 400; font-size: 16px; text-transform: none; }
.financity-body .gdlr-core-portfolio-item .gdlr-core-portfolio-read-more:after{ content: "\f178"; font-family: fontAwesome; margin-left: 14px; }

.financity-body .gdlr-core-flexslider .flex-control-nav li a { width: 32px; height: 6px; border-width: 2px;
    border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px; }
.financity-body .gdlr-core-call-to-action-item .gdlr-core-call-to-action-item-caption{ margin-top: 3px; }

.financity-body .gdlr-core-blockquote-item.gdlr-core-large-size .gdlr-core-blockquote-item-quote{ font-size: 263px; float: none; height: 100px; margin-top: -10px; margin-bottom: 15px; }
.financity-body .gdlr-core-blockquote-item.gdlr-core-large-size .gdlr-core-blockquote-item-content{ font-size: 28px; font-weight: 700; font-style: normal; }
.financity-body .gdlr-core-blockquote-item.gdlr-core-large-size .gdlr-core-blockquote-item-content p{ line-height: 1.4; }
.financity-body .gdlr-core-blockquote-item.gdlr-core-large-size .gdlr-core-blockquote-item-author{ font-size: 22px; font-style: normal; margin-top: 27px; }
.financity-body .gdlr-core-blockquote-item.gdlr-core-large-size .gdlr-core-blockquote-item-author:before{ display: none; }
.financity-body .gdlr-core-blockquote-item.gdlr-core-large-size .gdlr-core-blockquote-item-author-position{ display: block; font-size: 15px; }
.financity-body .gdlr-core-blockquote-item.gdlr-core-large-size .gdlr-core-blockquote-item-author-position:before { display: none; }
.financity-body .gdlr-core-blockquote-item.gdlr-core-large-size.gdlr-core-center-align .gdlr-core-blockquote-item-quote{ margin-bottom: 0px; }
.financity-body .gdlr-core-blockquote-item.gdlr-core-large-size.gdlr-core-left-align .gdlr-core-blockquote-item-quote{ margin-right: 0px; }
.financity-body .gdlr-core-blockquote-item.gdlr-core-large-size.gdlr-core-right-align .gdlr-core-blockquote-item-quote{ margin-left: 0px; }

.financity-body .gdlr-core-testimonial-quote.gdlr-core-quote-font.gdlr-core-skin-icon{ float: left; margin-right: 15px; padding-top: 12px; padding-bottom: 0px; margin-bottom: -40px; }
.financity-body .gdlr-core-testimonial-item .gdlr-core-testimonial-title{ font-size: 16px; text-transform: none; font-weight: 600; }
.financity-body .gdlr-core-testimonial-item .gdlr-core-testimonial-position{ font-style: normal; text-transform: uppercase; letter-spacing: 1px; }

.financity-body .gdlr-core-personnel-item .gdlr-core-personnel-list-title{ margin-bottom: 5px; }
.financity-body .gdlr-core-personnel-item .gdlr-core-personnel-list-divider{ display: none; }
.financity-body .gdlr-core-personnel-style-grid.gdlr-core-with-divider .gdlr-core-personnel-list-content-wrap{ padding-top: 24px; border-bottom-width: 3px; border-bottom-style: solid; padding-bottom: 4px; }
.financity-body .gdlr-core-personnel-style-grid .gdlr-core-personnel-list-content{ margin-top: 14px; }
.financity-body .gdlr-core-personnel-style-grid .gdlr-core-personnel-list-social{ margin-top: 14px; margin-bottom: 15px; }

.financity-body .gdlr-core-dropdown-tab .gdlr-core-dropdown-tab-title{ font-size: 15px; padding: 0px; float: none; background: transparent; color: inherit; font-weight: normal; margin-top: -13px; padding-bottom: 13px; }
.financity-body .gdlr-core-dropdown-tab .gdlr-core-dropdown-tab-head-wrap{ margin-top: 5px; border-radius: 0px; padding: 10px 19px 10px 19px; font-size: 15px; }
.financity-body .gdlr-core-dropdown-tab .gdlr-core-dropdown-tab-title .gdlr-core-head{ font-weight: bold; }
.financity-body .gdlr-core-dropdown-tab .gdlr-core-dropdown-tab-title:after{ float: none; content: "\f0d7"; font-size: 12px; margin-left: 12px; }
.financity-body .gdlr-core-dropdown-tab .gdlr-core-dropdown-tab-head{ display: block; white-space: nowrap; padding: 0px; }
.financity-body .gdlr-core-dropdown-tab .gdlr-core-dropdown-tab-head.gdlr-core-active{ display: none; }
.financity-body .gdlr-core-dropdown-tab .gdlr-core-dropdown-tab-content{ display: none; }
.financity-body .gdlr-core-dropdown-tab .gdlr-core-dropdown-tab-content.gdlr-core-active{ display: inline; }

.financity-body .gdlr-core-input-wrap [class^="gdlr-core-column-"]{ padding-left: 10px; padding-right: 10px; margin-bottom: 0px; }
.financity-body .gdlr-core-input-wrap.gdlr-core-with-column{ margin-left: -10px; margin-right: -10px; }
.financity-body .gdlr-core-input-wrap input[type="button"], 
.financity-body .gdlr-core-input-wrap input[type="submit"], 
.financity-body .gdlr-core-input-wrap input[type="reset"]{ text-transform: none; letter-spacing: 0px; font-size: 16px; font-weight: bold; padding: 20px 44px; min-width: 0px; }
.financity-body .gdlr-core-input-wrap.gdlr-core-large input:not([type="button"]):not([type="submit"]):not([type="file"]){ font-size: 15px; padding: 22px 25px; }
.financity-body .gdlr-core-input-wrap input:not([type="button"]):not([type="reset"]):not([type="submit"]):not([type="file"]):not([type="checkbox"]):not([type="radio"]), 
.financity-body .gdlr-core-input-wrap textarea{ margin-bottom: 20px; }
.financity-body .gdlr-core-input-wrap input[type="button"].gdlr-core-small, 
.financity-body .gdlr-core-input-wrap input[type="submit"].gdlr-core-small,
.financity-body .gdlr-core-input-wrap input[type="reset"].gdlr-core-small{ font-size: 15px; }
.financity-body .gdlr-core-input-wrap.gdlr-core-small input:not([type="button"]):not([type="submit"]):not([type="file"]){ font-size: 15px; }
.financity-body .gdlr-core-input-wrap.gdlr-core-small textarea{ font-size: 15px; }

.financity-body .gdlr-core-widget-box-shortcode{ padding-top: 35px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; }
.financity-body .gdlr-core-widget-box-shortcode .gdlr-core-widget-box-shortcode-title{ font-size: 19px; font-weight: 600; }

@media only screen and (max-width: 419px){
    .financity-body .gdlr-core-blockquote-item .gdlr-core-blockquote-item-quote{ display: none; }
}

/* Custom Style */
.large-widget-title{ font-size: 24px !important; }
.single .financity-comments-area input[type="text"], 
.single .financity-comments-area textarea{ background: #fff; }

body .gdlr-core-button{ padding: 12px 29px; }
body .gdlr-core-flexslider .flex-control-nav{ margin-top: 55px; }
body .gdlr-core-call-to-action-item .gdlr-core-button{ padding: 19px 36px; }
body .gdlr-core-blog-full .gdlr-core-excerpt-read-more{ border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; }
body .gdlr-core-blog-full .gdlr-core-blog-title{ margin-bottom: 5px; }
body .gdlr-core-accordion-style-box-icon .gdlr-core-accordion-item-title{ font-size: 19px; text-transform: none; letter-spacing: 0px; }
body .gdlr-core-accordion-item-content i{ margin-bottom: 17px; }
body .gdlr-core-counter-item .gdlr-core-counter-item-number{ font-weight: 600; margin-bottom: 15px; }
body .gdlr-core-counter-item-bottom-text{ font-weight: 600; }
body span.gdlr-core-port-info-value{ font-weight: 600; }
body .gdlr-core-port-info-item .gdlr-core-port-info{ font-size: 18px; }
body .gdlr-core-port-info-item .gdlr-core-port-info-key{ font-weight: 400; }
body .gdlr-core-portfolio-medium .gdlr-core-portfolio-content-wrap .gdlr-core-portfolio-info{ font-size: 15px; }
body .gdlr-core-portfolio-item-style-grid-no-space .gdlr-core-portfolio-content-wrap{ padding-left: 35px; padding-right: 20px; }
body .gdlr-core-portfolio-grid .gdlr-core-portfolio-content-wrap{ padding-top: 40px; }
body .gdlr-core-portfolio-grid .gdlr-core-portfolio-content-wrap .gdlr-core-portfolio-title{ margin-bottom: 12px; }
body .gdlr-core-portfolio-item .gdlr-core-portfolio-read-more{ font-weight: 400; font-size: 15px; text-transform: none; letter-spacing: 0; }
body .gdlr-core-portfolio-item .gdlr-core-portfolio-read-more-wrap{ margin-top: 15px; }
body .gdlr-core-personnel-style-grid .gdlr-core-personnel-list-content-wrap{ padding-top: 25px; }
body .gdlr-core-personnel-style-grid .gdlr-core-personnel-list-content{ margin-top: 15px; }
body .gdlr-core-personnel-style-grid .gdlr-core-personnel-list-position{ margin-bottom: 15px; }
body .gdlr-core-testimonial-item .gdlr-core-flexslider .flex-control-nav{ margin-top: 33px; }
body .gdlr-core-title-item a.gdlr-core-title-item-link{ font-size: 14px; text-transform: uppercase; letter-spacing: 2px; margin-top: 8px; }
body .gdlr-core-breadcrumbs-item{ font-size: 16px; padding: 19px 0px; }
body .gdlr-core-breadcrumbs-item span[property="itemListElement"]{ margin: 0px 17px; }
body .gdlr-core-input-wrap.gdlr-core-large textarea{ height: 160px; }
body .gdlr-core-input-wrap input[type="button"].gdlr-core-round-button, 
body .gdlr-core-input-wrap input[type="submit"].gdlr-core-round-button{ border-radius: 30px; -moz-border-radius: 30px; -webkit-border-radius: 30px; }
body .gdlr-core-recent-post-widget-wrap.gdlr-core-style-2 .gdlr-core-recent-post-widget-title{ font-size: 17px; font-weight: 500; }
body .gdlr-core-blockquote-item.gdlr-core-medium-size.gdlr-core-center-align .gdlr-core-blockquote-item-quote{ margin-bottom: -40px; }
body .gdlr-core-dropdown-tab .gdlr-core-dropdown-tab-head-wrap{ left: -27px; }
body .gdlr-core-hover-box .gdlr-core-hover-box-content{ font-size: 16px; }
body .gdlr-core-product-grid .gdlr-core-product-title{ font-size: 18px; }
<?php
			$css .= ob_get_contents();
			ob_end_clean(); 

			return $css;
		}
	}
	add_filter('gdlr_core_theme_option_bottom_file_write', 'financity_gdlr_core_theme_option_bottom_file_write', 10, 2);
	if( !function_exists('financity_gdlr_core_theme_option_bottom_file_write') ){
		function financity_gdlr_core_theme_option_bottom_file_write( $css, $option_slug ){
			if( $option_slug != 'goodlayers_main_menu' ) return;

			$general = get_option('financity_general');

			if( !empty($general['item-padding']) ){
				$margin = 2 * intval(str_replace('px', '', $general['item-padding']));
				if( !empty($margin) && is_numeric($margin) ){
					$css .= '.financity-item-mgb, .gdlr-core-item-mgb{ margin-bottom: ' . $margin . 'px; }';
				}
			}

			return $css;
		}
	}

	$financity_admin_option->add_element(array(
	
		// general head section
		'title' => esc_html__('General', 'financity'),
		'slug' => 'financity_general',
		'icon' => get_template_directory_uri() . '/include/options/images/general.png',
		'options' => array(
		
			'layout' => array(
				'title' => esc_html__('Layout', 'financity'),
				'options' => array(
					
					'layout' => array(
						'title' => esc_html__('Layout', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'full' => esc_html__('Full', 'financity'),
							'boxed' => esc_html__('Boxed', 'financity'),
						)
					),
					'boxed-layout-top-margin' => array(
						'title' => esc_html__('Box Layout Top/Bottom Margin', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '150',
						'data-type' => 'pixel',
						'default' => '0px',
						'selector' => 'body.financity-boxed .financity-body-wrapper{ margin-top: #gdlr#; margin-bottom: #gdlr#; }',
						'condition' => array( 'layout' => 'boxed' ) 
					),
					'body-margin' => array(
						'title' => esc_html__('Body Magin ( Frame Spaces )', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '100',
						'data-type' => 'pixel',
						'default' => '0px',
						'selector' => '.financity-body-wrapper.financity-with-frame, body.financity-full .financity-fixed-footer{ margin: #gdlr#; }',
						'condition' => array( 'layout' => 'full' ),
						'description' => esc_html__('This value will be automatically omitted for side header style.', 'financity'),
					),
					'background-type' => array(
						'title' => esc_html__('Background Type', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'color' => esc_html__('Color', 'financity'),
							'image' => esc_html__('Image', 'financity'),
							'pattern' => esc_html__('Pattern', 'financity'),
						),
						'condition' => array( 'layout' => 'boxed' )
					),
					'background-image' => array(
						'title' => esc_html__('Background Image', 'financity'),
						'type' => 'upload',
						'data-type' => 'file', 
						'selector' => '.financity-body-background{ background-image: url(#gdlr#); }',
						'condition' => array( 'layout' => 'boxed', 'background-type' => 'image' )
					),
					'background-image-opacity' => array(
						'title' => esc_html__('Background Image Opacity', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'opacity',
						'default' => '100',
						'condition' => array( 'layout' => 'boxed', 'background-type' => 'image' ),
						'selector' => '.financity-body-background{ opacity: #gdlr#; }'
					),
					'background-pattern' => array(
						'title' => esc_html__('Background Type', 'financity'),
						'type' => 'radioimage',
						'data-type' => 'text',
						'options' => 'pattern', 
						'selector' => '.financity-background-pattern .financity-body-outer-wrapper{ background-image: url(' . GDLR_CORE_URL . '/include/images/pattern/#gdlr#.png); }',
						'condition' => array( 'layout' => 'boxed', 'background-type' => 'pattern' ),
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'enable-boxed-border' => array(
						'title' => esc_html__('Enable Boxed Border', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable',
						'condition' => array( 'layout' => 'boxed', 'background-type' => 'pattern' ),
					),
					'item-padding' => array(
						'title' => esc_html__('Item Left/Right Spaces', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '40',
						'data-type' => 'pixel',
						'default' => '15px',
						'description' => 'Space between each page items',
						'selector' => '.financity-item-pdlr, .gdlr-core-item-pdlr{ padding-left: #gdlr#; padding-right: #gdlr#; }' . 
							'.financity-item-rvpdlr, .gdlr-core-item-rvpdlr{ margin-left: -#gdlr#; margin-right: -#gdlr#; }' .
							'.gdlr-core-metro-rvpdlr{ margin-top: -#gdlr#; margin-right: -#gdlr#; margin-bottom: -#gdlr#; margin-left: -#gdlr#; }' .
							'.financity-item-mglr, .gdlr-core-item-mglr, .financity-navigation .sf-menu > .financity-mega-menu .sf-mega{ margin-left: #gdlr#; margin-right: #gdlr#; }' . 
							'.financity-body .gdlr-core-personnel-item .gdlr-core-flexslider.gdlr-core-with-outer-frame-element .flex-viewport, ' . 
							'.financity-body .gdlr-core-hover-box-item .gdlr-core-flexslider.gdlr-core-with-outer-frame-element .flex-viewport,' . 
							'.financity-body .gdlr-core-blog-item .gdlr-core-flexslider.gdlr-core-with-outer-frame-element .flex-viewport{ padding-top: #gdlr#; margin-top: -#gdlr#; padding-right: #gdlr#; margin-right: -#gdlr#; padding-left: #gdlr#; margin-left: -#gdlr#; padding-bottom: #gdlr#; margin-bottom: -#gdlr#; }'
					),
					'container-width' => array(
						'title' => esc_html__('Container Width', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'data-input-type' => 'pixel',
						'default' => '1180px',
						'selector' => '.financity-container, .gdlr-core-container, body.financity-boxed .financity-body-wrapper, ' . 
							'body.financity-boxed .financity-fixed-footer .financity-footer-wrapper, body.financity-boxed .financity-fixed-footer .financity-copyright-wrapper{ max-width: #gdlr#; }' 
					),
					'container-padding' => array(
						'title' => esc_html__('Container Padding', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '100',
						'data-type' => 'pixel',
						'default' => '15px',
						'selector' => '.financity-body-front .gdlr-core-container, .financity-body-front .financity-container{ padding-left: #gdlr#; padding-right: #gdlr#; }'  . 
							'.financity-body-front .financity-container .financity-container, .financity-body-front .financity-container .gdlr-core-container, '.
							'.financity-body-front .gdlr-core-container .gdlr-core-container{ padding-left: 0px; padding-right: 0px; }'
					),
					'sidebar-width' => array(
						'title' => esc_html__('Sidebar Width', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'30' => '50%', '20' => '33.33%', '15' => '25%', '12' => '20%', '10' => '16.67%'
						),
						'default' => 20,
					),
					'both-sidebar-width' => array(
						'title' => esc_html__('Both Sidebar Width', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'30' => '50%', '20' => '33.33%', '15' => '25%', '12' => '20%', '10' => '16.67%'
						),
						'default' => 15,
					),
					
				) // header-options
			), // header-nav

			'top-bar' => array(
				'title' => esc_html__('Top Bar', 'financity'),
				'options' => array(

					'enable-top-bar' => array(
						'title' => esc_html__('Enable Top Bar', 'financity'),
						'type' => 'checkbox',
					),
					'enable-top-bar-on-mobile' => array(
						'title' => esc_html__('Enable Top Bar On Mobile', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable'
					),
					'enable-top-bar-divider' => array(
						'title' => esc_html__('Enable Top Bar Divider', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),
					'top-bar-width' => array(
						'title' => esc_html__('Top Bar Width', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'boxed' => esc_html__('Boxed ( Within Container )', 'financity'),
							'full' => esc_html__('Full', 'financity'),
							'custom' => esc_html__('Custom', 'financity'),
						),
						'condition' => array( 'enable-top-bar' => 'enable' )
					),
					'top-bar-width-pixel' => array(
						'title' => esc_html__('Top Bar Width Pixel', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'default' => '1140px',
						'condition' => array( 'enable-top-bar' => 'enable', 'top-bar-width' => 'custom' ),
						'selector' => '.financity-top-bar-container.financity-top-bar-custom-container{ max-width: #gdlr#; }'
					),
					'top-bar-full-side-padding' => array(
						'title' => esc_html__('Top Bar Full ( Left/Right ) Padding', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '100',
						'data-type' => 'pixel',
						'default' => '15px',
						'selector' => '.financity-top-bar-container.financity-top-bar-full{ padding-right: #gdlr#; padding-left: #gdlr#; }',
						'condition' => array( 'enable-top-bar' => 'enable', 'top-bar-width' => 'full' )
					),
					'top-bar-left-text' => array(
						'title' => esc_html__('Top Bar Left Text', 'financity'),
						'type' => 'textarea',
						'condition' => array( 'enable-top-bar' => 'enable' )
					),
					'top-bar-right-text' => array(
						'title' => esc_html__('Top Bar Right Text', 'financity'),
						'type' => 'textarea',
						'condition' => array( 'enable-top-bar' => 'enable' )
					),
					'top-bar-top-padding' => array(
						'title' => esc_html__('Top Bar Top Padding', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '200',
 						'default' => '10px',
						'selector' => '.financity-top-bar{ padding-top: #gdlr#; }' . 
							'.financity-top-bar-divider:before{ top: -#gdlr#; }',
						'condition' => array( 'enable-top-bar' => 'enable' )
					),
					'top-bar-bottom-padding' => array(
						'title' => esc_html__('Top Bar Bottom Padding', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '200',
						'default' => '10px',
						'selector' => '.financity-top-bar{ padding-bottom: #gdlr#; }' . 
							'.financity-top-bar-divider:before{ bottom: -#gdlr#; }',
						'condition' => array( 'enable-top-bar' => 'enable' )
					),
					'top-bar-text-size' => array(
						'title' => esc_html__('Top Bar Text Size', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'default' => '15px',
						'selector' => '.financity-top-bar{ font-size: #gdlr#; }',
						'condition' => array( 'enable-top-bar' => 'enable' )
					),
					'top-bar-bottom-border' => array(
						'title' => esc_html__('Top Bar Bottom Border', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '10',
						'default' => '0',
						'selector' => '.financity-top-bar{ border-bottom-width: #gdlr#; }',
						'condition' => array( 'enable-top-bar' => 'enable' )
					),

				)
			), // top bar

			'top-bar-social' => array(
				'title' => esc_html__('Top Bar Social', 'financity'),
				'options' => array(
					'enable-top-bar-social' => array(
						'title' => esc_html__('Enable Top Bar Social', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),
					'top-bar-social-delicious' => array(
						'title' => esc_html__('Top Bar Social Delicious Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-email' => array(
						'title' => esc_html__('Top Bar Social Email Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-deviantart' => array(
						'title' => esc_html__('Top Bar Social Deviantart Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-digg' => array(
						'title' => esc_html__('Top Bar Social Digg Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-facebook' => array(
						'title' => esc_html__('Top Bar Social Facebook Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-flickr' => array(
						'title' => esc_html__('Top Bar Social Flickr Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-google-plus' => array(
						'title' => esc_html__('Top Bar Social Google Plus Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-lastfm' => array(
						'title' => esc_html__('Top Bar Social Lastfm Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-linkedin' => array(
						'title' => esc_html__('Top Bar Social Linkedin Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-pinterest' => array(
						'title' => esc_html__('Top Bar Social Pinterest Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-rss' => array(
						'title' => esc_html__('Top Bar Social RSS Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-skype' => array(
						'title' => esc_html__('Top Bar Social Skype Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-stumbleupon' => array(
						'title' => esc_html__('Top Bar Social Stumbleupon Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-tumblr' => array(
						'title' => esc_html__('Top Bar Social Tumblr Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-twitter' => array(
						'title' => esc_html__('Top Bar Social Twitter Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-vimeo' => array(
						'title' => esc_html__('Top Bar Social Vimeo Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-youtube' => array(
						'title' => esc_html__('Top Bar Social Youtube Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-instagram' => array(
						'title' => esc_html__('Top Bar Social Instagram Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),
					'top-bar-social-snapchat' => array(
						'title' => esc_html__('Top Bar Social Snapchat Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-top-bar-social' => 'enable' )
					),

				)
			),			

			'header' => array(
				'title' => esc_html__('Header', 'financity'),
				'options' => array(

					'header-style' => array(
						'title' => esc_html__('Header Style', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'plain' => esc_html__('Plain', 'financity'),
							'bar' => esc_html__('Bar', 'financity'),
							'boxed' => esc_html__('Boxed', 'financity'),
							'side' => esc_html__('Side Menu', 'financity'),
							'side-toggle' => esc_html__('Side Menu Toggle', 'financity'),
						),
						'default' => 'plain',
					),
					'header-plain-style' => array(
						'title' => esc_html__('Header Plain Style', 'financity'),
						'type' => 'radioimage',
						'options' => array(
							'menu-right' => get_template_directory_uri() . '/images/header/plain-menu-right.jpg',
							'center-logo' => get_template_directory_uri() . '/images/header/plain-center-logo.jpg',
							'center-menu' => get_template_directory_uri() . '/images/header/plain-center-menu.jpg',
							'splitted-menu' => get_template_directory_uri() . '/images/header/plain-splitted-menu.jpg',
						),
						'default' => 'menu-right',
						'condition' => array( 'header-style' => 'plain' ),
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'header-plain-bottom-border' => array(
						'title' => esc_html__('Plain Header Bottom Border', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '10',
						'default' => '0',
						'selector' => '.financity-header-style-plain{ border-bottom-width: #gdlr#; }',
						'condition' => array( 'header-style' => array('plain') )
					),
					'header-bar-navigation-align' => array(
						'title' => esc_html__('Header Bar Style', 'financity'),
						'type' => 'radioimage',
						'options' => array(
							'left' => get_template_directory_uri() . '/images/header/bar-left.jpg',
							'center' => get_template_directory_uri() . '/images/header/bar-center.jpg',
							'center-logo' => get_template_directory_uri() . '/images/header/bar-center-logo.jpg',
						),
						'default' => 'center',
						'condition' => array( 'header-style' => 'bar' ),
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'header-background-style' => array(
						'title' => esc_html__('Header/Navigation Background Style', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'solid' => esc_html__('Solid', 'financity'),
							'transparent' => esc_html__('Transparent', 'financity'),
						),
						'default' => 'solid',
						'condition' => array( 'header-style' => array('plain', 'bar') )
					),
					'top-bar-background-opacity' => array(
						'title' => esc_html__('Top Bar Background Opacity', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'opacity',
						'default' => '50',
						'condition' => array( 'header-style' => array('plain', 'bar'), 'header-background-style' => 'transparent' ),
						'selector' => '.financity-header-background-transparent .financity-top-bar-background{ opacity: #gdlr#; }'
					),
					'header-background-opacity' => array(
						'title' => esc_html__('Header Background Opacity', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'opacity',
						'default' => '50',
						'condition' => array( 'header-style' => array('plain', 'bar'), 'header-background-style' => 'transparent' ),
						'selector' => '.financity-header-background-transparent .financity-header-background{ opacity: #gdlr#; }'
					),
					'navigation-background-opacity' => array(
						'title' => esc_html__('Navigation Background Opacity', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'opacity',
						'default' => '50',
						'condition' => array( 'header-style' => 'bar', 'header-background-style' => 'transparent' ),
						'selector' => '.financity-header-background-transparent .financity-navigation-bar-wrap .financity-navigation-background, ' . 
							'.financity-navigation-bar-wrap.financity-fixed-navigation .financity-show-in-sticky.financity-style-transparent{ opacity: #gdlr#; }'
					),
					'header-boxed-style' => array(
						'title' => esc_html__('Header Boxed Style', 'financity'),
						'type' => 'radioimage',
						'options' => array(
							'menu-right' => get_template_directory_uri() . '/images/header/boxed-menu-right.jpg',
							'center-menu' => get_template_directory_uri() . '/images/header/boxed-center-menu.jpg',
							'splitted-menu' => get_template_directory_uri() . '/images/header/boxed-splitted-menu.jpg',
						),
						'default' => 'menu-right',
						'condition' => array( 'header-style' => 'boxed' ),
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'boxed-top-bar-background-opacity' => array(
						'title' => esc_html__('Top Bar Background Opacity', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'opacity',
						'default' => '0',
						'condition' => array( 'header-style' => 'boxed' ),
						'selector' => '.financity-header-boxed-wrap .financity-top-bar-background{ opacity: #gdlr#; }'
					),
					'boxed-top-bar-background-extend' => array(
						'title' => esc_html__('Top Bar Background Extend ( Bottom )', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0px',
						'data-max' => '200px',
						'default' => '0px',
						'condition' => array( 'header-style' => 'boxed' ),
						'selector' => '.financity-header-boxed-wrap .financity-top-bar-background{ margin-bottom: -#gdlr#; }'
					),
					'boxed-header-top-margin' => array(
						'title' => esc_html__('Header Top Margin', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0px',
						'data-max' => '200px',
						'default' => '0px',
						'condition' => array( 'header-style' => 'boxed' ),
						'selector' => '.financity-header-style-boxed{ margin-top: #gdlr#; }'
					),
					'header-side-style' => array(
						'title' => esc_html__('Header Side Style', 'financity'),
						'type' => 'radioimage',
						'options' => array(
							'top-left' => get_template_directory_uri() . '/images/header/side-top-left.jpg',
							'middle-left' => get_template_directory_uri() . '/images/header/side-middle-left.jpg',
							'middle-left-2' => get_template_directory_uri() . '/images/header/side-middle-left-2.jpg',
							'top-right' => get_template_directory_uri() . '/images/header/side-top-right.jpg',
							'middle-right' => get_template_directory_uri() . '/images/header/side-middle-right.jpg',
							'middle-right-2' => get_template_directory_uri() . '/images/header/side-middle-right-2.jpg',
						),
						'default' => 'top-left',
						'condition' => array( 'header-style' => 'side' ),
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'header-side-align' => array(
						'title' => esc_html__('Header Side Text Align', 'financity'),
						'type' => 'radioimage',
						'options' => 'text-align',
						'default' => 'left',
						'condition' => array( 'header-style' => 'side' )
					),
					'header-side-toggle-style' => array(
						'title' => esc_html__('Header Side Toggle Style', 'financity'),
						'type' => 'radioimage',
						'options' => array(
							'left' => get_template_directory_uri() . '/images/header/side-toggle-left.jpg',
							'right' => get_template_directory_uri() . '/images/header/side-toggle-right.jpg',
						),
						'default' => 'left',
						'condition' => array( 'header-style' => 'side-toggle' ),
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'header-side-toggle-menu-type' => array(
						'title' => esc_html__('Header Side Toggle Menu Type', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'left' => esc_html__('Left Slide Menu', 'financity'),
							'right' => esc_html__('Right Slide Menu', 'financity'),
							'overlay' => esc_html__('Overlay Menu', 'financity'),
						),
						'default' => 'overlay',
						'condition' => array( 'header-style' => 'side-toggle' )
					),
					'header-side-toggle-display-logo' => array(
						'title' => esc_html__('Display Logo', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable',
						'condition' => array( 'header-style' => 'side-toggle' )
					),
					'header-width' => array(
						'title' => esc_html__('Header Width', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'boxed' => esc_html__('Boxed ( Within Container )', 'financity'),
							'full' => esc_html__('Full', 'financity'),
							'custom' => esc_html__('Custom', 'financity'),
						),
						'condition' => array('header-style'=> array('plain', 'bar', 'boxed'))
					),
					'header-width-pixel' => array(
						'title' => esc_html__('Header Width Pixel', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'default' => '1140px',
						'condition' => array('header-style'=> array('plain', 'bar', 'boxed'), 'header-width' => 'custom'),
						'selector' => '.financity-header-container.financity-header-custom-container{ max-width: #gdlr#; }'
					),
					'header-full-side-padding' => array(
						'title' => esc_html__('Header Full ( Left/Right ) Padding', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '100',
						'data-type' => 'pixel',
						'default' => '15px',
						'selector' => '.financity-header-container.financity-header-full{ padding-right: #gdlr#; padding-left: #gdlr#; }',
						'condition' => array('header-style'=> array('plain', 'bar', 'boxed'), 'header-width'=>'full')
					),
					'boxed-header-frame-radius' => array(
						'title' => esc_html__('Header Frame Radius', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'data-input-type' => 'pixel',
						'default' => '3px',
						'condition' => array( 'header-style' => array('boxed', 'bar') ),
						'selector' => '.financity-header-boxed-wrap .financity-header-background{ border-radius: #gdlr#; -moz-border-radius: #gdlr#; -webkit-border-radius: #gdlr#; }' . 
							'.financity-header-background-transparent .financity-navigation-bar-wrap .financity-navigation-background{ border-radius: #gdlr#; -moz-border-radius: #gdlr#; -webkit-border-radius: #gdlr#; }'
					),
					'boxed-header-content-padding' => array(
						'title' => esc_html__('Header Content ( Left/Right ) Padding', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '100',
						'data-type' => 'pixel',
						'default' => '30px',
						'selector' => '.financity-header-style-boxed .financity-header-container-item{ padding-left: #gdlr#; padding-right: #gdlr#; }' . 
							'.financity-navigation-right{ right: #gdlr#; } .financity-navigation-left{ left: #gdlr#; }',
						'condition' => array( 'header-style' => 'boxed' )
					),
					'navigation-text-top-margin' => array(
						'title' => esc_html__('Navigation Text Top Padding', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '200',
						'default' => '0px',
						'condition' => array( 'header-style' => 'plain', 'header-plain-style' => 'splitted-menu' ),
						'selector' => '.financity-header-style-plain.financity-style-splitted-menu .financity-navigation .sf-menu > li > a{ padding-top: #gdlr#; } ' .
							'.financity-header-style-plain.financity-style-splitted-menu .financity-main-menu-left-wrap,' .
							'.financity-header-style-plain.financity-style-splitted-menu .financity-main-menu-right-wrap{ padding-top: #gdlr#; }'
					),
					'navigation-text-top-margin-boxed' => array(
						'title' => esc_html__('Navigation Text Top Padding', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '200',
						'default' => '0px',
						'condition' => array( 'header-style' => 'boxed', 'header-boxed-style' => 'splitted-menu' ),
						'selector' => '.financity-header-style-boxed.financity-style-splitted-menu .financity-navigation .sf-menu > li > a{ padding-top: #gdlr#; } ' .
							'.financity-header-style-boxed.financity-style-splitted-menu .financity-main-menu-left-wrap,' .
							'.financity-header-style-boxed.financity-style-splitted-menu .financity-main-menu-right-wrap{ padding-top: #gdlr#; }'
					),
					'navigation-text-side-spacing' => array(
						'title' => esc_html__('Navigation Text Side ( Left / Right ) Spaces', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '30',
						'data-type' => 'pixel',
						'default' => '13px',
						'selector' => '.financity-navigation .sf-menu > li{ padding-left: #gdlr#; padding-right: #gdlr#; }',
						'condition' => array( 'header-style' => array('plain', 'bar', 'boxed') )
					),
					'navigation-slide-bar' => array(
						'title' => esc_html__('Navigation Slide Bar', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable',
						'condition' => array( 'header-style' => array('plain', 'bar', 'boxed') )
					),
					'side-header-width-pixel' => array(
						'title' => esc_html__('Header Width Pixel', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '600',
						'default' => '340px',
						'condition' => array('header-style' => array('side', 'side-toggle')),
						'selector' => '.financity-header-side-nav{ width: #gdlr#; }' . 
							'.financity-header-side-content.financity-style-left{ margin-left: #gdlr#; }' .
							'.financity-header-side-content.financity-style-right{ margin-right: #gdlr#; }'
					),
					'side-header-side-padding' => array(
						'title' => esc_html__('Header Side Padding', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '200',
						'default' => '70px',
						'condition' => array('header-style' => 'side'),
						'selector' => '.financity-header-side-nav.financity-style-side{ padding-left: #gdlr#; padding-right: #gdlr#; }' . 
							'.financity-header-side-nav.financity-style-left .sf-vertical > li > ul.sub-menu{ padding-left: #gdlr#; }' .
							'.financity-header-side-nav.financity-style-right .sf-vertical > li > ul.sub-menu{ padding-right: #gdlr#; }'
					),
					'navigation-text-top-spacing' => array(
						'title' => esc_html__('Navigation Text Top / Bottom Spaces', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '40',
						'data-type' => 'pixel',
						'default' => '16px',
						'selector' => ' .financity-navigation .sf-vertical > li{ padding-top: #gdlr#; padding-bottom: #gdlr#; }',
						'condition' => array( 'header-style' => array('side') )
					),

				)
			), // header
			
			'header-bar-right-text' => array(
				'title' => esc_html__('Header Bar Right Text', 'financity'),
				'options' => array(
					'logo-right-box1-icon' => array(
						'title' => esc_html__('Header Right Box 1 Icon', 'financity'),
						'type' => 'text',
						'description' => esc_html__('Only for header bar style.', 'financity') . 
							esc_html__('You can choose the icon from these following links. ', 'financity') . 
							'(<a href="http://support.goodlayers.com/document/icon-font-awesomes/">Font Awesome</a>, ' . 
							'<a href="http://support.goodlayers.com/document/icon-elegant-icons/">Elegant Icons</a>)'
					),
					'logo-right-box1-title' => array(
						'title' => esc_html__('Header Right Box 1 Title', 'financity'),
						'type' => 'text'
					),
					'logo-right-box1-caption' => array(
						'title' => esc_html__('Header Right Box 1 Caption', 'financity'),
						'type' => 'text'
					),
					'logo-right-box2-icon' => array(
						'title' => esc_html__('Header Right Box 2 Icon', 'financity'),
						'type' => 'text',
						'description' => esc_html__('You can choose the icon from these following links. ', 'financity') . 
							'(<a href="http://support.goodlayers.com/document/icon-font-awesomes/">Font Awesome</a>, ' . 
							'<a href="http://support.goodlayers.com/document/icon-elegant-icons/">Elegant Icons</a>)'
					),
					'logo-right-box2-title' => array(
						'title' => esc_html__('Header Right Box 2 Title', 'financity'),
						'type' => 'text'
					),
					'logo-right-box2-caption' => array(
						'title' => esc_html__('Header Right Box 2 Caption', 'financity'),
						'type' => 'text'
					),
					'logo-right-box3-icon' => array(
						'title' => esc_html__('Header Right Box 3 Icon', 'financity'),
						'type' => 'text',
						'description' => esc_html__('You can choose the icon from these following links. ', 'financity') . 
							'(<a href="http://support.goodlayers.com/document/icon-font-awesomes/">Font Awesome</a>, ' . 
							'<a href="http://support.goodlayers.com/document/icon-elegant-icons/">Elegant Icons</a>)'
					),
					'logo-right-box3-title' => array(
						'title' => esc_html__('Header Right Box 3 Title', 'financity'),
						'type' => 'text'
					),
					'logo-right-box3-caption' => array(
						'title' => esc_html__('Header Right Box 3 Caption', 'financity'),
						'type' => 'text'
					),
					'logo-right-text-top-padding' => array(
						'title' => esc_html__('Header Right Text Top Padding', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '200',
						'default' => '30px',
						'selector' => '.financity-header-style-bar .financity-logo-right-text{ padding-top: #gdlr#; }'
					),
					'enable-header-right-button' => array(
						'title' => esc_html__('Header Right Button', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable',
					),
					'header-right-button-text' => array(
						'title' => esc_html__('Header Right Button Text', 'financity'),
						'type' => 'text',
						'default' => esc_html__('Get A Quote', 'financity'),
						'condition' => array( 'enable-header-right-button' => 'enable' ) 
					),
					'header-right-button-link' => array(
						'title' => esc_html__('Header Right Button Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-header-right-button' => 'enable' ) 
					),
					'header-right-button-link-target' => array(
						'title' => esc_html__('Header Right Button Link Target', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'_self' => esc_html__('Current Screen', 'financity'),
							'_blank' => esc_html__('New Window', 'financity'),
						),
						'condition' => array( 'enable-header-right-button' => 'enable' ) 
					),					

				)
			),

			'logo' => array(
				'title' => esc_html__('Logo', 'financity'),
				'options' => array(
					'logo' => array(
						'title' => esc_html__('Logo', 'financity'),
						'type' => 'upload'
					),
					'mobile-logo' => array(
						'title' => esc_html__('Mobile Logo', 'financity'),
						'type' => 'upload',
						'description' => esc_html__('Leave this option blank to use the same logo.', 'financity'),
					),
					'logo-top-padding' => array(
						'title' => esc_html__('Logo Top Padding', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '200',
						'data-type' => 'pixel',
						'default' => '20px',
						'selector' => '.financity-logo{ padding-top: #gdlr#; }',
						'description' => esc_html__('This option will be omitted on splitted menu option.', 'financity'),
					),
					'logo-bottom-padding' => array(
						'title' => esc_html__('Logo Bottom Padding', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '200',
						'data-type' => 'pixel',
						'default' => '20px',
						'selector' => '.financity-logo{ padding-bottom: #gdlr#; }',
						'description' => esc_html__('This option will be omitted on splitted menu option.', 'financity'),
					),
					'logo-left-padding' => array(
						'title' => esc_html__('Logo Left Padding', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'data-input-type' => 'pixel',
						'selector' => '.financity-logo.financity-item-pdlr{ padding-left: #gdlr#; }',
						'description' => esc_html__('Leave this field blank for default value.', 'financity'),
					),
					'max-logo-width' => array(
						'title' => esc_html__('Max Logo Width', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'data-input-type' => 'pixel',
						'default' => '200px',
						'selector' => '.financity-logo-inner{ max-width: #gdlr#; }'
					),
					'max-mobile-logo-width' => array(
						'title' => esc_html__('Max Mobile Logo Width', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'data-input-type' => 'pixel',
						'selector' => '.financity-mobile-header .financity-logo-inner{ max-width: #gdlr#; }'
					),
				
				)
			),
			'navigation' => array(
				'title' => esc_html__('Navigation', 'financity'),
				'options' => array(
					'main-navigation-top-padding' => array(
						'title' => esc_html__('Main Navigation Top Padding', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '200',
						'data-type' => 'pixel',
						'default' => '25px',
						'selector' => '.financity-navigation{ padding-top: #gdlr#; }' . 
							'.financity-navigation-top{ top: #gdlr#; }'
					),
					'main-navigation-bottom-padding' => array(
						'title' => esc_html__('Main Navigation Bottom Padding', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '200',
						'data-type' => 'pixel',
						'default' => '20px',
						'selector' => '.financity-navigation .sf-menu > li > a{ padding-bottom: #gdlr#; }'
					),
					'main-navigation-item-right-padding' => array(
						'title' => esc_html__('Main Navigation Item Right Padding', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '200',
						'data-type' => 'pixel',
						'default' => '0px',
						'selector' => '.financity-navigation .financity-main-menu{ padding-right: #gdlr#; }'
					),
					'main-navigation-right-padding' => array(
						'title' => esc_html__('Main Navigation Wrap Right Padding', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'data-input-type' => 'pixel',
						'selector' => '.financity-navigation.financity-item-pdlr{ padding-right: #gdlr#; }',
						'description' => esc_html__('Leave this field blank for default value.', 'financity'),
					),
					'enable-main-navigation-submenu-indicator' => array(
						'title' => esc_html__('Enable Main Navigation Submenu Indicator', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable',
					),
					'enable-main-navigation-search' => array(
						'title' => esc_html__('Enable Main Navigation Search', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable',
					),
					'enable-main-navigation-cart' => array(
						'title' => esc_html__('Enable Main Navigation Cart ( Woocommerce )', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable',
						'description' => esc_html__('The icon only shows if the woocommerce plugin is activated', 'financity')
					),
					'enable-main-navigation-right-button' => array(
						'title' => esc_html__('Enable Main Navigation Right Button', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable',
						'description' => esc_html__('This option will be ignored on header side style', 'financity')
					),
					'main-navigation-right-button-text' => array(
						'title' => esc_html__('Main Navigation Right Button Text', 'financity'),
						'type' => 'text',
						'default' => esc_html__('Buy Now', 'financity'),
						'condition' => array( 'enable-main-navigation-right-button' => 'enable' ) 
					),
					'main-navigation-right-button-link' => array(
						'title' => esc_html__('Main Navigation Right Button Link', 'financity'),
						'type' => 'text',
						'condition' => array( 'enable-main-navigation-right-button' => 'enable' ) 
					),
					'main-navigation-right-button-link-target' => array(
						'title' => esc_html__('Main Navigation Right Button Link Target', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'_self' => esc_html__('Current Screen', 'financity'),
							'_blank' => esc_html__('New Window', 'financity'),
						),
						'condition' => array( 'enable-main-navigation-right-button' => 'enable' ) 
					),
					'right-menu-type' => array(
						'title' => esc_html__('Secondary/Mobile Menu Type', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'left' => esc_html__('Left Slide Menu', 'financity'),
							'right' => esc_html__('Right Slide Menu', 'financity'),
							'overlay' => esc_html__('Overlay Menu', 'financity'),
						),
						'default' => 'right'
					),
					'right-menu-style' => array(
						'title' => esc_html__('Secondary/Mobile Menu Style', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'hamburger-with-border' => esc_html__('Hamburger With Border', 'financity'),
							'hamburger' => esc_html__('Hamburger', 'financity'),
						),
						'default' => 'hamburger-with-border'
					),
					
				) // logo-options
			), // logo-navigation			
			
			'fixed-navigation' => array(
				'title' => esc_html__('Fixed Navigation', 'financity'),
				'options' => array(

					'enable-main-navigation-sticky' => array(
						'title' => esc_html__('Enable Fixed Navigation Bar', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable',
					),
					'enable-logo-on-main-navigation-sticky' => array(
						'title' => esc_html__('Enable Logo on Fixed Navigation Bar', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable',
						'condition' => array( 'enable-main-navigation-sticky' => 'enable' )
					),
					'fixed-navigation-max-logo-width' => array(
						'title' => esc_html__('Fixed Navigation Max Logo Width', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'data-input-type' => 'pixel',
						'default' => '',
						'condition' => array( 'enable-main-navigation-sticky' => 'enable' ),
						'selector' => '.financity-fixed-navigation.financity-style-slide .financity-logo-inner img{ max-height: none !important; }' .
							'.financity-animate-fixed-navigation.financity-header-style-plain .financity-logo-inner, ' . 
							'.financity-animate-fixed-navigation.financity-header-style-boxed .financity-logo-inner{ max-width: #gdlr#; }'
					),
					'fixed-navigation-logo-top-padding' => array(
						'title' => esc_html__('Fixed Navigation Logo Top Padding', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'data-input-type' => 'pixel',
						'default' => '20px',
						'condition' => array( 'enable-main-navigation-sticky' => 'enable' ),
						'selector' => '.financity-animate-fixed-navigation.financity-header-style-plain .financity-logo, ' . 
							'.financity-animate-fixed-navigation.financity-header-style-boxed .financity-logo{ padding-top: #gdlr#; }'
					),
					'fixed-navigation-logo-bottom-padding' => array(
						'title' => esc_html__('Fixed Navigation Logo Bottom Padding', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'data-input-type' => 'pixel',
						'default' => '20px',
						'condition' => array( 'enable-main-navigation-sticky' => 'enable' ),
						'selector' => '.financity-animate-fixed-navigation.financity-header-style-plain .financity-logo, ' . 
							'.financity-animate-fixed-navigation.financity-header-style-boxed .financity-logo{ padding-bottom: #gdlr#; }'
					),
					'fixed-navigation-top-padding' => array(
						'title' => esc_html__('Fixed Navigation Top Padding', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'data-input-type' => 'pixel',
						'default' => '30px',
						'condition' => array( 'enable-main-navigation-sticky' => 'enable' ),
						'selector' => '.financity-animate-fixed-navigation.financity-header-style-plain .financity-navigation, ' . 
							'.financity-animate-fixed-navigation.financity-header-style-boxed .financity-navigation{ padding-top: #gdlr#; }' . 
							'.financity-animate-fixed-navigation.financity-header-style-plain .financity-navigation-top, ' . 
							'.financity-animate-fixed-navigation.financity-header-style-boxed .financity-navigation-top{ top: #gdlr#; }'
					),
					'fixed-navigation-bottom-padding' => array(
						'title' => esc_html__('Fixed Navigation Bottom Padding', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'data-input-type' => 'pixel',
						'default' => '25px',
						'condition' => array( 'enable-main-navigation-sticky' => 'enable' ),
						'selector' => '.financity-animate-fixed-navigation.financity-header-style-plain .financity-navigation .sf-menu > li > a, ' . 
							'.financity-animate-fixed-navigation.financity-header-style-boxed .financity-navigation .sf-menu > li > a{ padding-bottom: #gdlr#; }'
					),
					'fixed-navigation-anchor-offset' => array(
						'title' => esc_html__('Fixed Navigation Anchor Offset ( Fixed Navigation Height )', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'data-input-type' => 'pixel',
						'default' => '75px',
						'condition' => array( 'enable-main-navigation-sticky' => 'enable' ),
					),
					'fixed-navigation-background-opacity' => array(
						'title' => esc_html__('Fixed Navigation Background Opacity', 'financity'),
						'type' => 'text',
						'data-type' => 'text',
						'description' => esc_html__('Fill number between 0 and 1. Leave blank for default value.', 'financity'),
						'selector' => '.financity-navigation-bar-wrap.financity-fixed-navigation .financity-show-in-sticky.financity-style-transparent{ opacity: #gdlr#; }'
					),

				)
			),

			'title-style' => array(
				'title' => esc_html__('Page Title Style', 'financity'),
				'options' => array(

					'default-title-style' => array(
						'title' => esc_html__('Default Page Title Style', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'small' => esc_html__('Small', 'financity'),
							'medium' => esc_html__('Medium', 'financity'),
							'large' => esc_html__('Large', 'financity'),
							'custom' => esc_html__('Custom', 'financity'),
						),
						'default' => 'small'
					),
					'default-title-align' => array(
						'title' => esc_html__('Default Page Title Alignment', 'financity'),
						'type' => 'radioimage',
						'options' => 'text-align',
						'default' => 'left'
					),
					'default-title-top-padding' => array(
						'title' => esc_html__('Default Page Title Top Padding', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '350',
						'default' => '93px',
						'selector' => '.financity-page-title-wrap.financity-style-custom .financity-page-title-content{ padding-top: #gdlr#; }',
						'condition' => array( 'default-title-style' => 'custom' )
					),
					'default-title-bottom-padding' => array(
						'title' => esc_html__('Default Page Title Bottom Padding', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '350',
						'default' => '87px',
						'selector' => '.financity-page-title-wrap.financity-style-custom .financity-page-title-content{ padding-bottom: #gdlr#; }',
						'condition' => array( 'default-title-style' => 'custom' )
					),
					'default-page-caption-top-margin' => array(
						'title' => esc_html__('Default Page Caption Top Margin', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '200',
						'default' => '13px',						
						'selector' => '.financity-page-title-wrap.financity-style-custom .financity-page-caption{ margin-top: #gdlr#; }',
						'condition' => array( 'default-title-style' => 'custom' )
					),
					'default-title-font-transform' => array(
						'title' => esc_html__('Default Page Title Font Transform', 'financity'),
						'type' => 'combobox',
						'data-type' => 'text',
						'options' => array(
							'' => esc_html__('Default', 'financity'),
							'none' => esc_html__('None', 'financity'),
							'uppercase' => esc_html__('Uppercase', 'financity'),
							'lowercase' => esc_html__('Lowercase', 'financity'),
							'capitalize' => esc_html__('Capitalize', 'financity'),
						),
						'default' => 'default',
						'selector' => '.financity-page-title-wrap .financity-page-title{ text-transform: #gdlr#; }'
					),
					'default-title-font-size' => array(
						'title' => esc_html__('Default Page Title Font Size', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'default' => '37px',
						'selector' => '.financity-page-title-wrap.financity-style-custom .financity-page-title{ font-size: #gdlr#; }',
						'condition' => array( 'default-title-style' => 'custom' )
					),
					'default-title-font-weight' => array(
						'title' => esc_html__('Default Page Title Font Weight', 'financity'),
						'type' => 'text',
						'data-type' => 'text',
						'selector' => '.financity-page-title-wrap .financity-page-title{ font-weight: #gdlr#; }',
						'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800. Leave this field blank for default value (700).', 'financity')					
					),
					'default-title-letter-spacing' => array(
						'title' => esc_html__('Default Page Title Letter Spacing', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '20',
						'default' => '0px',
						'selector' => '.financity-page-title-wrap.financity-style-custom .financity-page-title{ letter-spacing: #gdlr#; }',
						'condition' => array( 'default-title-style' => 'custom' )
					),
					'default-caption-font-size' => array(
						'title' => esc_html__('Default Page Caption Font Size', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'default' => '16px',
						'selector' => '.financity-page-title-wrap.financity-style-custom .financity-page-caption{ font-size: #gdlr#; }',
						'condition' => array( 'default-title-style' => 'custom' )
					),
					'default-caption-font-weight' => array(
						'title' => esc_html__('Default Page Caption Font Weight', 'financity'),
						'type' => 'text',
						'data-type' => 'text',
						'selector' => '.financity-page-title-wrap .financity-page-caption{ font-weight: #gdlr#; }',
						'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800. Leave this field blank for default value (400).', 'financity')					
					),
					'default-caption-letter-spacing' => array(
						'title' => esc_html__('Default Page Caption Letter Spacing', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '20',
						'default' => '0px',
						'selector' => '.financity-page-title-wrap.financity-style-custom .financity-page-caption{ letter-spacing: #gdlr#; }',
						'condition' => array( 'default-title-style' => 'custom' )
					),
					'default-caption-font-transform' => array(
						'title' => esc_html__('Default Page Caption Font Transform', 'financity'),
						'type' => 'combobox',
						'data-type' => 'text',
						'options' => array(
							'' => esc_html__('Default', 'financity'),
							'none' => esc_html__('None', 'financity'),
							'uppercase' => esc_html__('Uppercase', 'financity'),
							'lowercase' => esc_html__('Lowercase', 'financity'),
							'capitalize' => esc_html__('Capitalize', 'financity'),
						),
						'selector' => '.financity-page-title-wrap.financity-style-custom .financity-page-caption{ text-transform: #gdlr#; }'
					),
					'default-title-background-overlay-opacity' => array(
						'title' => esc_html__('Default Page Title Background Overlay Opacity', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'opacity',
						'default' => '80',
						'selector' => '.financity-page-title-wrap .financity-page-title-overlay{ opacity: #gdlr#; }'
					),
					'enable-default-breadcrumbs-on-page' => array(
						'title' => esc_html__('Enable Default Breadcrumbs on Page', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable',
						'description' => esc_html__('You need to install the "Breadcrumb NavXT" plugin before using this option.', 'financity')
					),
					'enable-default-breadcrumbs-on-portfolio' => array(
						'title' => esc_html__('Enable Default Breadcrumbs on Portfolio', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable',
						'description' => esc_html__('You need to install the "Breadcrumb NavXT" plugin before using this option.', 'financity')
					),
				) 
			), // title style

			'title-background' => array(
				'title' => esc_html__('Page Title Background', 'financity'),
				'options' => array(

					'default-title-background' => array(
						'title' => esc_html__('Default Page Title Background', 'financity'),
						'type' => 'upload',
						'data-type' => 'file',
						'selector' => '.financity-page-title-wrap{ background-image: url(#gdlr#); }'
					),
					'default-portfolio-title-background' => array(
						'title' => esc_html__('Default Portfolio Title Background', 'financity'),
						'type' => 'upload',
						'data-type' => 'file',
						'selector' => 'body.single-portfolio .financity-page-title-wrap{ background-image: url(#gdlr#); }'
					),
					'default-personnel-title-background' => array(
						'title' => esc_html__('Default Personnel Title Background', 'financity'),
						'type' => 'upload',
						'data-type' => 'file',
						'selector' => 'body.single-personnel .financity-page-title-wrap{ background-image: url(#gdlr#); }'
					),
					'default-search-title-background' => array(
						'title' => esc_html__('Default Search Title Background', 'financity'),
						'type' => 'upload',
						'data-type' => 'file',
						'selector' => 'body.search .financity-page-title-wrap{ background-image: url(#gdlr#); }'
					),
					'default-archive-title-background' => array(
						'title' => esc_html__('Default Archive Title Background', 'financity'),
						'type' => 'upload',
						'data-type' => 'file',
						'selector' => 'body.archive .financity-page-title-wrap{ background-image: url(#gdlr#); }'
					),
					'default-404-background' => array(
						'title' => esc_html__('Default 404 Background', 'financity'),
						'type' => 'upload',
						'data-type' => 'file',
						'selector' => '.financity-not-found-wrap .financity-not-found-background{ background-image: url(#gdlr#); }'
					),
					'default-404-background-opacity' => array(
						'title' => esc_html__('Default 404 Background Opacity', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'opacity',
						'default' => '27',
						'selector' => '.financity-not-found-wrap .financity-not-found-background{ opacity: #gdlr#; }'
					),

				) 
			), // title background

			'blog-title-style' => array(
				'title' => esc_html__('Blog Title Style', 'financity'),
				'options' => array(

					'default-blog-title-style' => array(
						'title' => esc_html__('Default Blog Title Style', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'small' => esc_html__('Small', 'financity'),
							'large' => esc_html__('Large', 'financity'),
							'custom' => esc_html__('Custom', 'financity'),
							'inside-content' => esc_html__('Inside Content', 'financity'),
							'none' => esc_html__('None', 'financity'),
						),
						'default' => 'small'
					),
					'default-blog-title-top-padding' => array(
						'title' => esc_html__('Default Blog Title Top Padding', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '400',
						'default' => '93px',
						'selector' => '.financity-blog-title-wrap.financity-style-custom .financity-blog-title-content{ padding-top: #gdlr#; }',
						'condition' => array( 'default-blog-title-style' => 'custom' )
					),
					'default-blog-title-bottom-padding' => array(
						'title' => esc_html__('Default Blog Title Bottom Padding', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'data-min' => '0',
						'data-max' => '400',
						'default' => '87px',
						'selector' => '.financity-blog-title-wrap.financity-style-custom .financity-blog-title-content{ padding-bottom: #gdlr#; }',
						'condition' => array( 'default-blog-title-style' => 'custom' )
					),
					'default-blog-feature-image' => array(
						'title' => esc_html__('Default Blog Feature Image Location', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'content' => esc_html__('Inside Content', 'financity'),
							'title-background' => esc_html__('Title Background', 'financity'),
							'none' => esc_html__('None', 'financity'),
						),
						'default' => 'content',
						'condition' => array( 'default-blog-title-style' => array('small', 'large', 'custom') )
					),
					'default-blog-title-background-image' => array(
						'title' => esc_html__('Default Blog Title Background Image', 'financity'),
						'type' => 'upload',
						'data-type' => 'file',
						'selector' => '.financity-blog-title-wrap{ background-image: url(#gdlr#); }',
						'condition' => array( 'default-blog-title-style' => array('small', 'large', 'custom') )
					),
					'default-blog-title-background-overlay-opacity' => array(
						'title' => esc_html__('Default Blog Title Background Overlay Opacity', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'opacity',
						'default' => '80',
						'selector' => '.financity-blog-title-wrap .financity-blog-title-overlay{ opacity: #gdlr#; }',
						'condition' => array( 'default-blog-title-style' => array('small', 'large', 'custom') )
					),
					'enable-default-breadcrumbs-on-post' => array(
						'title' => esc_html__('Enable Default Breadcrumbs on Post', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable',
						'description' => esc_html__('You need to install the "Breadcrumb NavXT" plugin before using this option.', 'financity')
					),
				) 
			), // post title style			

			'blog-style' => array(
				'title' => esc_html__('Blog Style', 'financity'),
				'options' => array(
					'blog-sidebar' => array(
						'title' => esc_html__('Single Blog Sidebar ( Default )', 'financity'),
						'type' => 'radioimage',
						'options' => 'sidebar',
						'default' => 'none',
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'blog-sidebar-left' => array(
						'title' => esc_html__('Single Blog Sidebar Left ( Default )', 'financity'),
						'type' => 'combobox',
						'options' => 'sidebar',
						'default' => 'none',
						'condition' => array( 'blog-sidebar'=>array('left', 'both') )
					),
					'blog-sidebar-right' => array(
						'title' => esc_html__('Single Blog Sidebar Right ( Default )', 'financity'),
						'type' => 'combobox',
						'options' => 'sidebar',
						'default' => 'none',
						'condition' => array( 'blog-sidebar'=>array('right', 'both') )
					),
					'blog-max-content-width' => array(
						'title' => esc_html__('Single Blog Max Content Width ( No sidebar layout )', 'financity'),
						'type' => 'text',
						'data-type' => 'text',
						'data-input-type' => 'pixel',
						'default' => '900px',
						'selector' => 'body.single-post .financity-sidebar-style-none, body.blog .financity-sidebar-style-none{ max-width: #gdlr#; }'
					),
					'blog-thumbnail-size' => array(
						'title' => esc_html__('Single Blog Thumbnail Size', 'financity'),
						'type' => 'combobox',
						'options' => 'thumbnail-size',
						'default' => 'full'
					),
					'blog-date-feature' => array(
						'title' => esc_html__('Enable Blog Date Feature', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),
					'blog-date-feature-year' => array(
						'title' => esc_html__('Enable Year on Blog Date Feature', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable',
						'condition' => array( 'blog-date-feature' => 'enable' )
					),
					'meta-option' => array(
						'title' => esc_html__('Meta Option', 'financity'),
						'type' => 'multi-combobox',
						'options' => array( 
							'date' => esc_html__('Date', 'financity'),
							'author' => esc_html__('Author', 'financity'),
							'category' => esc_html__('Category', 'financity'),
							'tag' => esc_html__('Tag', 'financity'),
							'comment' => esc_html__('Comment', 'financity'),
							'comment-number' => esc_html__('Comment Number', 'financity'),
						),
						'default' => array('author', 'category', 'tag', 'comment-number')
					),
					'blog-author' => array(
						'title' => esc_html__('Enable Single Blog Author', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),
					'blog-navigation' => array(
						'title' => esc_html__('Enable Single Blog Navigation', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),
					'pagination-style' => array(
						'title' => esc_html__('Pagination Style', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'plain' => esc_html__('Plain', 'financity'),
							'rectangle' => esc_html__('Rectangle', 'financity'),
							'rectangle-border' => esc_html__('Rectangle Border', 'financity'),
							'round' => esc_html__('Round', 'financity'),
							'round-border' => esc_html__('Round Border', 'financity'),
							'circle' => esc_html__('Circle', 'financity'),
							'circle-border' => esc_html__('Circle Border', 'financity'),
						),
						'default' => 'round'
					),
					'pagination-align' => array(
						'title' => esc_html__('Pagination Alignment', 'financity'),
						'type' => 'radioimage',
						'options' => 'text-align',
						'default' => 'right'
					),
				) // blog-style-options
			), // blog-style-nav

			'blog-social-share' => array(
				'title' => esc_html__('Blog Social Share', 'financity'),
				'options' => array(
					'blog-social-share' => array(
						'title' => esc_html__('Enable Single Blog Share', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),
					'blog-social-share-count' => array(
						'title' => esc_html__('Enable Single Blog Share Count', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),
					'blog-social-facebook' => array(
						'title' => esc_html__('Facebook', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),			
					'blog-social-linkedin' => array(
						'title' => esc_html__('Linkedin', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable'
					),			
					'blog-social-google-plus' => array(
						'title' => esc_html__('Google Plus', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),			
					'blog-social-pinterest' => array(
						'title' => esc_html__('Pinterest', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),			
					'blog-social-stumbleupon' => array(
						'title' => esc_html__('Stumbleupon', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable'
					),			
					'blog-social-twitter' => array(
						'title' => esc_html__('Twitter', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),			
					'blog-social-email' => array(
						'title' => esc_html__('Email', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable'
					),
				) // blog-style-options
			), // blog-style-nav
			
			'search-archive' => array(
				'title' => esc_html__('Search/Archive', 'financity'),
				'options' => array(
					'archive-blog-sidebar' => array(
						'title' => esc_html__('Archive Blog Sidebar', 'financity'),
						'type' => 'radioimage',
						'options' => 'sidebar',
						'default' => 'right',
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'archive-blog-sidebar-left' => array(
						'title' => esc_html__('Archive Blog Sidebar Left', 'financity'),
						'type' => 'combobox',
						'options' => 'sidebar',
						'default' => 'none',
						'condition' => array( 'archive-blog-sidebar'=>array('left', 'both') )
					),
					'archive-blog-sidebar-right' => array(
						'title' => esc_html__('Archive Blog Sidebar Right', 'financity'),
						'type' => 'combobox',
						'options' => 'sidebar',
						'default' => 'none',
						'condition' => array( 'archive-blog-sidebar'=>array('right', 'both') )
					),
					'archive-blog-style' => array(
						'title' => esc_html__('Archive Blog Style', 'financity'),
						'type' => 'radioimage',
						'options' => array(
							'blog-full' => GDLR_CORE_URL . '/include/images/blog-style/blog-full.png',
							'blog-full-with-frame' => GDLR_CORE_URL . '/include/images/blog-style/blog-full-with-frame.png',
							'blog-column' => GDLR_CORE_URL . '/include/images/blog-style/blog-column.png',
							'blog-column-with-frame' => GDLR_CORE_URL . '/include/images/blog-style/blog-column-with-frame.png',
							'blog-column-no-space' => GDLR_CORE_URL . '/include/images/blog-style/blog-column-no-space.png',
							'blog-image' => GDLR_CORE_URL . '/include/images/blog-style/blog-image.png',
							'blog-image-no-space' => GDLR_CORE_URL . '/include/images/blog-style/blog-image-no-space.png',
							'blog-left-thumbnail' => GDLR_CORE_URL . '/include/images/blog-style/blog-left-thumbnail.png',
							'blog-right-thumbnail' => GDLR_CORE_URL . '/include/images/blog-style/blog-right-thumbnail.png',
						),
						'default' => 'blog-full',
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'archive-blog-full-alignment' => array(
						'title' => esc_html__('Archive Blog Full Alignment', 'financity'),
						'type' => 'combobox',
						'default' => 'enable',
						'options' => array(
							'left' => esc_html__('Left', 'financity'),
							'center' => esc_html__('Center', 'financity'),
						),
						'condition' => array( 'archive-blog-style' => array('blog-full', 'blog-full-with-frame') )
					),
					'archive-thumbnail-size' => array(
						'title' => esc_html__('Archive Thumbnail Size', 'financity'),
						'type' => 'combobox',
						'options' => 'thumbnail-size'
					),
					'archive-show-thumbnail' => array(
						'title' => esc_html__('Archive Show Thumbnail', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable',
						'condition' => array( 'archive-blog-style' => array('blog-full', 'blog-full-with-frame', 'blog-column', 'blog-column-with-frame', 'blog-column-no-space', 'blog-left-thumbnail', 'blog-right-thumbnail') )
					),
					'archive-column-size' => array(
						'title' => esc_html__('Archive Column Size', 'financity'),
						'type' => 'combobox',
						'options' => array( 60 => 1, 30 => 2, 20 => 3, 15 => 4, 12 => 5 ),
						'default' => 20,
						'condition' => array( 'archive-blog-style' => array('blog-column', 'blog-column-with-frame', 'blog-column-no-space', 'blog-image', 'blog-image-no-space') )
					),
					'archive-excerpt' => array(
						'title' => esc_html__('Archive Excerpt Type', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'specify-number' => esc_html__('Specify Number', 'financity'),
							'show-all' => esc_html__('Show All ( use <!--more--> tag to cut the content )', 'financity'),
						),
						'default' => 'specify-number',
						'condition' => array('archive-blog-style' => array('blog-full', 'blog-full-with-frame', 'blog-column', 'blog-column-with-frame', 'blog-column-no-space', 'blog-left-thumbnail', 'blog-right-thumbnail'))
					),
					'archive-excerpt-number' => array(
						'title' => esc_html__('Archive Excerpt Number', 'financity'),
						'type' => 'text',
						'default' => 55,
						'data-input-type' => 'number',
						'condition' => array('archive-blog-style' => array('blog-full', 'blog-full-with-frame', 'blog-column', 'blog-column-with-frame', 'blog-column-no-space', 'blog-left-thumbnail', 'blog-right-thumbnail'), 'archive-excerpt' => 'specify-number')
					),
					'archive-date-feature' => array(
						'title' => esc_html__('Enable Blog Date Feature', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable',
						'condition' => array( 'archive-blog-style' => array('blog-full', 'blog-full-with-frame', 'blog-left-thumbnail', 'blog-right-thumbnail') )
					),
					'archive-meta-option' => array(
						'title' => esc_html__('Archive Meta Option', 'financity'),
						'type' => 'multi-combobox',
						'options' => array( 
							'date' => esc_html__('Date', 'financity'),
							'author' => esc_html__('Author', 'financity'),
							'category' => esc_html__('Category', 'financity'),
							'tag' => esc_html__('Tag', 'financity'),
							'comment' => esc_html__('Comment', 'financity'),
							'comment-number' => esc_html__('Comment Number', 'financity'),
						),
						'default' => array('date', 'author', 'category')
					),
					'archive-show-read-more' => array(
						'title' => esc_html__('Archive Show Read More Button', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable',
						'condition' => array('archive-blog-style' => array('blog-full', 'blog-full-with-frame', 'blog-left-thumbnail', 'blog-right-thumbnail'),)
					),
				)
			),

			'woocommerce-style' => array(
				'title' => esc_html__('Woocommerce Style', 'financity'),
				'options' => array(

					'woocommerce-archive-sidebar' => array(
						'title' => esc_html__('Woocommerce Archive Sidebar', 'financity'),
						'type' => 'radioimage',
						'options' => 'sidebar',
						'default' => 'right',
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'woocommerce-archive-sidebar-left' => array(
						'title' => esc_html__('Woocommerce Archive Sidebar Left', 'financity'),
						'type' => 'combobox',
						'options' => 'sidebar',
						'default' => 'none',
						'condition' => array( 'woocommerce-archive-sidebar'=>array('left', 'both') )
					),
					'woocommerce-archive-sidebar-right' => array(
						'title' => esc_html__('Woocommerce Archive Sidebar Right', 'financity'),
						'type' => 'combobox',
						'options' => 'sidebar',
						'default' => 'none',
						'condition' => array( 'woocommerce-archive-sidebar'=>array('right', 'both') )
					),
					'woocommerce-archive-column-size' => array(
						'title' => esc_html__('Woocommerce Archive Column Size', 'financity'),
						'type' => 'combobox',
						'options' => array( 60 => 1, 30 => 2, 20 => 3, 15 => 4, 12 => 5, 10 => 6, ),
						'default' => 15
					),
					'woocommerce-archive-thumbnail' => array(
						'title' => esc_html__('Woocommerce Archive Thumbnail Size', 'financity'),
						'type' => 'combobox',
						'options' => 'thumbnail-size',
						'default' => 'full'
					),
					'woocommerce-related-product-column-size' => array(
						'title' => esc_html__('Woocommerce Related Product Column Size', 'financity'),
						'type' => 'combobox',
						'options' => array( 60 => 1, 30 => 2, 20 => 3, 15 => 4, 12 => 5, 10 => 6, ),
						'default' => 15
					),
					'woocommerce-related-product-num-fetch' => array(
						'title' => esc_html__('Woocommerce Related Product Num Fetch', 'financity'),
						'type' => 'text',
						'default' => 4,
						'data-input-type' => 'number'
					),
					'woocommerce-related-product-thumbnail' => array(
						'title' => esc_html__('Woocommerce Related Product Thumbnail Size', 'financity'),
						'type' => 'combobox',
						'options' => 'thumbnail-size',
						'default' => 'full'
					),
				)
			),

			'portfolio-style' => array(
				'title' => esc_html__('Portfolio Style', 'financity'),
				'options' => array(
					'portfolio-slug' => array(
						'title' => esc_html__('Portfolio Slug (Permalink)', 'financity'),
						'type' => 'text',
						'default' => 'portfolio',
						'description' => esc_html__('Please save the "Settings > Permalink" area once after made a changes to this field.', 'financity')
					),
					'portfolio-category-slug' => array(
						'title' => esc_html__('Portfolio Category Slug (Permalink)', 'financity'),
						'type' => 'text',
						'default' => 'portfolio_category',
						'description' => esc_html__('Please save the "Settings > Permalink" area once after made a changes to this field.', 'financity')
					),
					'portfolio-tag-slug' => array(
						'title' => esc_html__('Portfolio Tag Slug (Permalink)', 'financity'),
						'type' => 'text',
						'default' => 'portfolio_tag',
						'description' => esc_html__('Please save the "Settings > Permalink" area once after made a changes to this field.', 'financity')
					),
					'enable-single-portfolio-navigation' => array(
						'title' => esc_html__('Enable Single Portfolio Navigation', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),
					'enable-single-portfolio-navigation-in-same-tag' => array(
						'title' => esc_html__('Enable Single Portfolio Navigation Within Same Tag', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable',
						'condition' => array( 'enable-single-portfolio-navigation' => 'enable' )
					),
					'portfolio-icon-hover-link' => array(
						'title' => esc_html__('Portfolio Hover Icon (Link)', 'financity'),
						'type' => 'radioimage',
						'options' => 'hover-icon-link',
						'default' => 'icon_link_alt'
					),
					'portfolio-icon-hover-video' => array(
						'title' => esc_html__('Portfolio Hover Icon (Video)', 'financity'),
						'type' => 'radioimage',
						'options' => 'hover-icon-video',
						'default' => 'icon_film'
					),
					'portfolio-icon-hover-image' => array(
						'title' => esc_html__('Portfolio Hover Icon (Image)', 'financity'),
						'type' => 'radioimage',
						'options' => 'hover-icon-image',
						'default' => 'icon_zoom-in_alt'
					),
					'portfolio-icon-hover-size' => array(
						'title' => esc_html__('Portfolio Hover Icon Size', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'default' => '22px',
						'selector' => '.gdlr-core-portfolio-thumbnail .gdlr-core-portfolio-icon{ font-size: #gdlr#; }' 
					),
					'enable-related-portfolio' => array(
						'title' => esc_html__('Enable Related Portfolio', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),
					'related-portfolio-style' => array(
						'title' => esc_html__('Related Portfolio Style', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'grid' => esc_html__('Grid', 'financity'),
							'modern' => esc_html__('Modern', 'financity'),
						),
						'condition' => array('enable-related-portfolio'=>'enable')
					),
					'related-portfolio-column-size' => array(
						'title' => esc_html__('Related Portfolio Column Size', 'financity'),
						'type' => 'combobox',
						'options' => array( 60 => 1, 30 => 2, 20 => 3, 15 => 4, 12 => 5, 10 => 6, ),
						'default' => 15,
						'condition' => array('enable-related-portfolio'=>'enable')
					),
					'related-portfolio-num-fetch' => array(
						'title' => esc_html__('Related Portfolio Num Fetch', 'financity'),
						'type' => 'text',
						'default' => 4,
						'data-input-type' => 'number',
						'condition' => array('enable-related-portfolio'=>'enable')
					),
					'related-portfolio-thumbnail-size' => array(
						'title' => esc_html__('Related Portfolio Thumbnail Size', 'financity'),
						'type' => 'combobox',
						'options' => 'thumbnail-size',
						'condition' => array('enable-related-portfolio'=>'enable'),
						'default' => 'medium'
					),
					'related-portfolio-num-excerpt' => array(
						'title' => esc_html__('Related Portfolio Num Excerpt', 'financity'),
						'type' => 'text',
						'default' => 20,
						'data-input-type' => 'number',
						'condition' => array('enable-related-portfolio'=>'enable', 'related-portfolio-style'=>'grid')
					),
				)
			),

			'portfolio-archive' => array(
				'title' => esc_html__('Portfolio Archive', 'financity'),
				'options' => array(
					'archive-portfolio-sidebar' => array(
						'title' => esc_html__('Archive Portfolio Sidebar', 'financity'),
						'type' => 'radioimage',
						'options' => 'sidebar',
						'default' => 'none',
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'archive-portfolio-sidebar-left' => array(
						'title' => esc_html__('Archive Portfolio Sidebar Left', 'financity'),
						'type' => 'combobox',
						'options' => 'sidebar',
						'default' => 'none',
						'condition' => array( 'archive-portfolio-sidebar'=>array('left', 'both') )
					),
					'archive-portfolio-sidebar-right' => array(
						'title' => esc_html__('Archive Portfolio Sidebar Right', 'financity'),
						'type' => 'combobox',
						'options' => 'sidebar',
						'default' => 'none',
						'condition' => array( 'archive-portfolio-sidebar'=>array('right', 'both') )
					),
					'archive-portfolio-style' => array(
						'title' => esc_html__('Archive Portfolio Style', 'financity'),
						'type' => 'radioimage',
						'options' => array(
							'modern' => get_template_directory_uri() . '/include/options/images/portfolio/modern.png',
							'modern-no-space' => get_template_directory_uri() . '/include/options/images/portfolio/modern-no-space.png',
							'grid' => get_template_directory_uri() . '/include/options/images/portfolio/grid.png',
							'grid-no-space' => get_template_directory_uri() . '/include/options/images/portfolio/grid-no-space.png',
							'modern-desc' => get_template_directory_uri() . '/include/options/images/portfolio/modern-desc.png',
							'modern-desc-no-space' => get_template_directory_uri() . '/include/options/images/portfolio/modern-desc-no-space.png',
							'medium' => get_template_directory_uri() . '/include/options/images/portfolio/medium.png',
						),
						'default' => 'medium',
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'archive-portfolio-thumbnail-size' => array(
						'title' => esc_html__('Archive Portfolio Thumbnail Size', 'financity'),
						'type' => 'combobox',
						'options' => 'thumbnail-size'
					),
					'archive-portfolio-grid-text-align' => array(
						'title' => esc_html__('Archive Portfolio Grid Text Align', 'financity'),
						'type' => 'radioimage',
						'options' => 'text-align',
						'default' => 'left',
						'condition' => array( 'archive-portfolio-style' => array( 'grid', 'grid-no-space' ) )
					),
					'archive-portfolio-grid-style' => array(
						'title' => esc_html__('Archive Portfolio Grid Content Style', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'normal' => esc_html__('Normal', 'financity'),
							'with-frame' => esc_html__('With Frame', 'financity'),
							'with-bottom-border' => esc_html__('With Bottom Border', 'financity'),
						),
						'default' => 'normal',
						'condition' => array( 'archive-portfolio-style' => array( 'grid', 'grid-no-space' ) )
					),
					'archive-enable-portfolio-tag' => array(
						'title' => esc_html__('Archive Enable Portfolio Tag', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable',
						'condition' => array( 'archive-portfolio-style' => array( 'grid', 'grid-no-space', 'modern-desc', 'modern-desc-no-space', 'medium' ) )
					),
					'archive-portfolio-medium-size' => array(
						'title' => esc_html__('Archive Portfolio Medium Thumbnail Size', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'small' => esc_html__('Small', 'financity'),
							'large' => esc_html__('Large', 'financity'),
						),
						'condition' => array( 'archive-portfolio-style' => 'medium' )
					),
					'archive-portfolio-medium-style' => array(
						'title' => esc_html__('Archive Portfolio Medium Thumbnail Style', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'left' => esc_html__('Left', 'financity'),
							'right' => esc_html__('Right', 'financity'),
							'switch' => esc_html__('Switch ( Between Left and Right )', 'financity'),
						),
						'default' => 'switch',
						'condition' => array( 'archive-portfolio-style' => 'medium' )
					),
					'archive-portfolio-hover' => array(
						'title' => esc_html__('Archive Portfolio Hover Style', 'financity'),
						'type' => 'radioimage',
						'options' => array(
							'title' => get_template_directory_uri() . '/include/options/images/portfolio/hover/title.png',
							'title-icon' => get_template_directory_uri() . '/include/options/images/portfolio/hover/title-icon.png',
							'title-tag' => get_template_directory_uri() . '/include/options/images/portfolio/hover/title-tag.png',
							'icon-title-tag' => get_template_directory_uri() . '/include/options/images/portfolio/hover/icon-title-tag.png',
							'icon' => get_template_directory_uri() . '/include/options/images/portfolio/hover/icon.png',
							'margin-title' => get_template_directory_uri() . '/include/options/images/portfolio/hover/margin-title.png',
							'margin-title-icon' => get_template_directory_uri() . '/include/options/images/portfolio/hover/margin-title-icon.png',
							'margin-title-tag' => get_template_directory_uri() . '/include/options/images/portfolio/hover/margin-title-tag.png',
							'margin-icon-title-tag' => get_template_directory_uri() . '/include/options/images/portfolio/hover/margin-icon-title-tag.png',
							'margin-icon' => get_template_directory_uri() . '/include/options/images/portfolio/hover/margin-icon.png',
							'none' => get_template_directory_uri() . '/include/options/images/portfolio/hover/none.png',
						),
						'default' => 'icon',
						'max-width' => '100px',
						'condition' => array( 'archive-portfolio-style' => array('modern', 'modern-no-space', 'grid', 'grid-no-space', 'medium') ),
						'wrapper-class' => 'gdlr-core-fullsize'
					),
					'archive-portfolio-column-size' => array(
						'title' => esc_html__('Archive Portfolio Column Size', 'financity'),
						'type' => 'combobox',
						'options' => array( 60=>1, 30=>2, 20=>3, 15=>4, 12=>5 ),
						'default' => 20,
						'condition' => array( 'archive-portfolio-style' => array('modern', 'modern-no-space', 'grid', 'grid-no-space', 'modern-desc', 'modern-desc-no-space') )
					),
					'archive-portfolio-excerpt' => array(
						'title' => esc_html__('Archive Portfolio Excerpt Type', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'specify-number' => esc_html__('Specify Number', 'financity'),
							'show-all' => esc_html__('Show All ( use <!--more--> tag to cut the content )', 'financity'),
							'none' => esc_html__('Disable Exceprt', 'financity'),
						),
						'default' => 'specify-number',
						'condition' => array( 'archive-portfolio-style' => array( 'grid', 'grid-no-space', 'modern-desc', 'modern-desc-no-space', 'medium' ) )
					),
					'archive-portfolio-excerpt-number' => array(
						'title' => esc_html__('Archive Portfolio Excerpt Number', 'financity'),
						'type' => 'text',
						'default' => 55,
						'data-input-type' => 'number',
						'condition' => array( 'archive-portfolio-style' => array( 'grid', 'grid-no-space', 'modern-desc', 'modern-desc-no-space', 'medium' ), 'archive-portfolio-excerpt' => 'specify-number' )
					),

				)
			),

			'personnel-style' => array(
				'title' => esc_html__('Personnel Style', 'financity'),
				'options' => array(
					'personnel-slug' => array(
						'title' => esc_html__('Personnel Slug (Permalink)', 'financity'),
						'type' => 'text',
						'default' => 'personnel',
						'description' => esc_html__('Please save the "Settings > Permalink" area once after made a changes to this field.', 'financity')
					),
					'personnel-category-slug' => array(
						'title' => esc_html__('Personnel Category Slug (Permalink)', 'financity'),
						'type' => 'text',
						'default' => 'personnel_category',
						'description' => esc_html__('Please save the "Settings > Permalink" area once after made a changes to this field.', 'financity')
					),
				)
			),

			'footer' => array(
				'title' => esc_html__('Footer/Copyright', 'financity'),
				'options' => array(

					'fixed-footer' => array(
						'title' => esc_html__('Fixed Footer', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable'
					),
					'enable-footer' => array(
						'title' => esc_html__('Enable Footer', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),
					'enable-footer-column-divider' => array(
						'title' => esc_html__('Enable Footer Column Divider', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable',
						'condition' => array( 'enable-footer' => 'enable' )
					),
					'footer-top-padding' => array(
						'title' => esc_html__('Footer Top Padding', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '300',
						'data-type' => 'pixel',
						'default' => '70px',
						'selector' => '.financity-footer-wrapper{ padding-top: #gdlr#; }',
						'condition' => array( 'enable-footer' => 'enable' )
					),
					'footer-bottom-padding' => array(
						'title' => esc_html__('Footer Bottom Padding', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '300',
						'data-type' => 'pixel',
						'default' => '50px',
						'selector' => '.financity-footer-wrapper{ padding-bottom: #gdlr#; }',
						'condition' => array( 'enable-footer' => 'enable' )
					),
					'footer-style' => array(
						'title' => esc_html__('Footer Style', 'financity'),
						'type' => 'radioimage',
						'wrapper-class' => 'gdlr-core-fullsize',
						'options' => array(
							'footer-1' => get_template_directory_uri() . '/include/options/images/footer-style1.png',
							'footer-2' => get_template_directory_uri() . '/include/options/images/footer-style2.png',
							'footer-3' => get_template_directory_uri() . '/include/options/images/footer-style3.png',
							'footer-4' => get_template_directory_uri() . '/include/options/images/footer-style4.png',
							'footer-5' => get_template_directory_uri() . '/include/options/images/footer-style5.png',
							'footer-6' => get_template_directory_uri() . '/include/options/images/footer-style6.png',
						),
						'default' => 'footer-2',
						'condition' => array( 'enable-footer' => 'enable' )
					),
					'enable-copyright' => array(
						'title' => esc_html__('Enable Copyright', 'financity'),
						'type' => 'checkbox',
						'default' => 'enable'
					),
					'copyright-style' => array(
						'title' => esc_html__('Copyright Style', 'financity'),
						'type' => 'combobox',
						'options' => array(
							'center' => esc_html__('Center', 'financity'),
							'left-right' => esc_html__('Left & Right', 'financity'),
						),
						'condition' => array( 'enable-copyright' => 'enable' )
					),
					'copyright-top-padding' => array(
						'title' => esc_html__('Copyright Top Padding', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '300',
						'data-type' => 'pixel',
						'default' => '38px',
						'selector' => '.financity-copyright-container{ padding-top: #gdlr#; }',
						'condition' => array( 'enable-copyright' => 'enable' )
					),
					'copyright-bottom-padding' => array(
						'title' => esc_html__('Copyright Bottom Padding', 'financity'),
						'type' => 'fontslider',
						'data-min' => '0',
						'data-max' => '300',
						'data-type' => 'pixel',
						'default' => '38px',
						'selector' => '.financity-copyright-container{ padding-bottom: #gdlr#; }',
						'condition' => array( 'enable-copyright' => 'enable' )
					),
					'copyright-font-letter-spacing' => array(
						'title' => esc_html__('Copyright Font Letter Spacing', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'data-input-type' => 'pixel',
						'default' => '2px',
						'selector' => '.financity-copyright-container{ letter-spacing: #gdlr#; }',
						'condition' => array( 'enable-copyright' => 'enable' )
					),
					'copyright-text-transform' => array(
						'title' => esc_html__('Copyright Text Transform', 'financity'),
						'type' => 'combobox',
						'data-type' => 'text',
						'options' => array(
							'uppercase' => esc_html__('Uppercase', 'financity'),
							'lowercase' => esc_html__('Lowercase', 'financity'),
							'capitalize' => esc_html__('Capitalize', 'financity'),
							'none' => esc_html__('None', 'financity'),
						),
						'default' => 'uppercase',
						'selector' => '.financity-copyright-container{ text-transform: #gdlr#; }',
						'condition' => array( 'enable-copyright' => 'enable' )
					),			
					'copyright-text' => array(
						'title' => esc_html__('Copyright Text', 'financity'),
						'type' => 'textarea',
						'wrapper-class' => 'gdlr-core-fullsize',
						'condition' => array( 'enable-copyright' => 'enable' )
					),
					'copyright-left' => array(
						'title' => esc_html__('Copyright Left', 'financity'),
						'type' => 'textarea',
						'wrapper-class' => 'gdlr-core-fullsize',
						'condition' => array( 'enable-copyright' => 'enable', 'copyright-style' => 'left-right' )
					),
					'copyright-right' => array(
						'title' => esc_html__('Copyright Right', 'financity'),
						'type' => 'textarea',
						'wrapper-class' => 'gdlr-core-fullsize',
						'condition' => array( 'enable-copyright' => 'enable', 'copyright-style' => 'left-right' )
					),
					'enable-back-to-top' => array(
						'title' => esc_html__('Enable Back To Top Button', 'financity'),
						'type' => 'checkbox',
						'default' => 'disable'
					),
				) // footer-options
			), // footer-nav	
		
		) // general-options
		
	), 2);