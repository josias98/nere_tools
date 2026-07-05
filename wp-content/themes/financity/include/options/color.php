<?php
	/*	
	*	Goodlayers Option
	*	---------------------------------------------------------------------
	*	This file store an array of theme options
	*	---------------------------------------------------------------------
	*/	

	// register skin option slug
	add_filter('gdlr_core_skin_option_slug', 'financity_gdlr_core_skin_option_slug');
	if( !function_exists('financity_gdlr_core_skin_option_slug') ){
		function financity_gdlr_core_skin_option_slug( $option = '' ){
			return array('financity_color', 'skin');
		}
	}

	// register skin options
	add_filter('gdlr_core_skin_options', 'financity_gdlr_core_skin_options');
	if( !function_exists('financity_gdlr_core_skin_options') ){
		function financity_gdlr_core_skin_options( $options = array() ){

			$options = array_merge($options, array(
				'section-typography' => array(
					'title' => esc_html__('Typography', 'financity'),
					'type' => 'title'
				),
				'title' => array(
					'title' => esc_html__('Title Color', 'financity'),
					'selector' => '#gdlr-core-skin# h1, #gdlr-core-skin# h2, #gdlr-core-skin# h3, #gdlr-core-skin# h4, #gdlr-core-skin# h5, #gdlr-core-skin# h6, #gdlr-core-skin# .gdlr-core-skin-title, #gdlr-core-skin# .gdlr-core-skin-title a{ color: #gdlr# }'
				),
				'title-hover' => array(
					'title' => esc_html__('Title (Link) Hover Color', 'financity'),
					'selector' => '#gdlr-core-skin# .gdlr-core-skin-title a:hover{ color: #gdlr# }'
				),
				'caption' => array(
					'title' => esc_html__('Caption Color', 'financity'),
					'selector' => '#gdlr-core-skin# .gdlr-core-skin-caption, #gdlr-core-skin# .gdlr-core-skin-caption a, #gdlr-core-skin# .gdlr-core-skin-caption a:hover{ color: #gdlr# }'
				),
				'content' => array(
					'title' => esc_html__('Content Color', 'financity'),
					'selector' => '#gdlr-core-skin#, #gdlr-core-skin# .gdlr-core-skin-content{ color: #gdlr# }'
				),
				'icon' => array(
					'title' => esc_html__('Icon Color', 'financity'),
					'selector' => '#gdlr-core-skin# i, #gdlr-core-skin# .gdlr-core-skin-icon, #gdlr-core-skin# .gdlr-core-skin-icon:before, #gdlr-core-skin# .financity-widget ul li:before{ color: #gdlr# }' . 
						'#gdlr-core-skin# .gdlr-core-blog-modern.gdlr-core-with-image .gdlr-core-blog-info-wrapper i{ color: #gdlr#; }' . 
						'#gdlr-core-skin# .gdlr-core-flexslider-nav.gdlr-core-plain-circle-style li a{ border-color: #gdlr#; }'
				),
				'link' => array(
					'title' => esc_html__('Link Color', 'financity'),
					'selector' => '#gdlr-core-skin# a, #gdlr-core-skin# .gdlr-core-skin-link{ color: #gdlr# }'
				),
				'link-hover' => array(
					'title' => esc_html__('Link Hover Color', 'financity'),
					'selector' => '#gdlr-core-skin# a:hover, #gdlr-core-skin# .gdlr-core-skin-link:hover{ color: #gdlr# }'
				),
				'section-divider' => array(
					'title' => esc_html__('Divider & Elements', 'financity'),
					'type' => 'title'
				),
				'divider' => array(
					'title' => esc_html__('Divider Color', 'financity'),
					'selector' => '#gdlr-core-skin# .gdlr-core-skin-divider{ border-color: #gdlr#; column-rule-color: #gdlr#; -moz-column-rule-color: #gdlr#; -webkit-column-rule-color: #gdlr#; }' . 
						'#gdlr-core-skin# .gdlr-core-flexslider.gdlr-core-nav-style-middle-large .flex-direction-nav li a{ border-color: #gdlr# }'
				),
				'border' => array(
					'title' => esc_html__('Border Color', 'financity'),
					'selector' => '#gdlr-core-skin# *, #gdlr-core-skin# .gdlr-core-skin-border{ border-color: #gdlr# }' . 
						'#gdlr-core-skin# input:not([type="button"]):not([type="submit"]):not([type="reset"]){ border-color: #gdlr#; }'
				),
				'element-background' => array(
					'title' => esc_html__('Element Background Color', 'financity'),
					'selector' => '#gdlr-core-skin# .gdlr-core-skin-e-background{ background-color: #gdlr# }' . 
						'#gdlr-core-skin# .gdlr-core-flexslider-nav.gdlr-core-round-style li a, #gdlr-core-skin# .gdlr-core-flexslider-nav.gdlr-core-rectangle-style li a{ background-color: #gdlr#; }' .
						'#gdlr-core-skin# .gdlr-core-flexslider .flex-control-nav li a{ border-color: #gdlr#; }'.
						'#gdlr-core-skin# .gdlr-core-flexslider .flex-control-nav li a.flex-active{ background-color: #gdlr#; }' . 
						'#gdlr-core-skin# input:not([type="button"]):not([type="submit"]):not([type="reset"]), #gdlr-core-skin# textarea{ background-color: #gdlr#; }'
				),
				'element-content' => array(
					'title' => esc_html__('Element Content Color', 'financity'),
					'selector' => '#gdlr-core-skin# .gdlr-core-skin-e-content{ color: #gdlr# }'  . 
						'#gdlr-core-skin# .gdlr-core-flexslider-nav.gdlr-core-round-style li a i, #gdlr-core-skin# .gdlr-core-flexslider-nav.gdlr-core-rectangle-style li a i{ color: #gdlr#; }' .
						'#gdlr-core-skin# input:not([type="button"]):not([type="submit"]):not([type="reset"]), #gdlr-core-skin# textarea{ color: #gdlr#; }' .
						'#gdlr-core-skin# ::-webkit-input-placeholder{ color: #gdlr#; }' .
						'#gdlr-core-skin# ::-moz-placeholder{ color: #gdlr#; }' .
						'#gdlr-core-skin# :-ms-input-placeholder{ color: #gdlr#; }' .
						'#gdlr-core-skin# :-moz-placeholder{ color: #gdlr#; }'
				),
				'section-button' => array(
					'title' => esc_html__('Button', 'financity'),
					'type' => 'title'
				),
				'button-text' => array(
					'title' => esc_html__('Button Text', 'financity'),
					'selector' => '#gdlr-core-skin# .gdlr-core-button, #gdlr-core-skin# .gdlr-core-button-color{ color: #gdlr# }' .
						'#gdlr-core-skin# input[type="button"], #gdlr-core-skin# input[type="submit"]{ color: #gdlr#; }' .
					 	'#gdlr-core-skin# .gdlr-core-pagination a{ color: #gdlr# }'
				),
				'button-text-hover' => array(
					'title' => esc_html__('Button Text Hover', 'financity'),
					'selector' => '#gdlr-core-skin# .gdlr-core-button:hover, #gdlr-core-skin# .gdlr-core-button-color:hover, #gdlr-core-skin# .gdlr-core-button-color.gdlr-core-active{ color: #gdlr# }' .
					 	'#gdlr-core-skin# input[type="button"]:hover, #gdlr-core-skin# input[type="submit"]:hover{ color: #gdlr#; }' .
					 	'#gdlr-core-skin# .gdlr-core-pagination a:hover, #gdlr-core-skin# .gdlr-core-pagination a.gdlr-core-active, #gdlr-core-skin# .gdlr-core-pagination span{ color: #gdlr# }'
				),
				'button-background' => array(
					'title' => esc_html__('Button Background', 'financity'),
					'selector' => '#gdlr-core-skin# .gdlr-core-button, #gdlr-core-skin# .gdlr-core-button-color{ background-color: #gdlr# }'  .
					 	'#gdlr-core-skin# input[type="button"], #gdlr-core-skin# input[type="submit"]{ background-color: #gdlr#; }' .
					 	'#gdlr-core-skin# .gdlr-core-pagination a{ background-color: #gdlr# }'
				),
				'button-background-hover' => array(
					'title' => esc_html__('Button Bg Hover / Gradient', 'financity'),
					'selector' => '#gdlr-core-skin# .gdlr-core-button:hover, #gdlr-core-skin# .gdlr-core-button-color:hover, #gdlr-core-skin# .gdlr-core-button-color.gdlr-core-active{ background-color: #gdlr# }' .
					 	'#gdlr-core-skin# input[type="button"]:hover, #gdlr-core-skin# input[type="submit"]:hover{ background-color: #gdlr#; }' .
					 	'#gdlr-core-skin# .gdlr-core-pagination a:hover, #gdlr-core-skin# .gdlr-core-pagination a.gdlr-core-active, #gdlr-core-skin# .gdlr-core-pagination span{ background-color: #gdlr# }'
				),
				'button-border-color' => array(
					'title' => esc_html__('Button Border', 'financity'),
					'selector' => '#gdlr-core-skin# .gdlr-core-button, #gdlr-core-skin# .gdlr-core-button-color{ border-color: #gdlr# }' . 
						'#gdlr-core-skin# .gdlr-core-pagination a{ border-color: #gdlr# }'
				),
				'button-border-hover-color' => array(
					'title' => esc_html__('Button Border Hover', 'financity'),
					'selector' => '#gdlr-core-skin# .gdlr-core-button:hover, #gdlr-core-skin# .gdlr-core-button-color:hover, #gdlr-core-skin# .gdlr-core-button-color.gdlr-core-active{ border-color: #gdlr# }' .
			 			'#gdlr-core-skin# .gdlr-core-pagination a:hover, #gdlr-core-skin# .gdlr-core-pagination a.gdlr-core-active, #gdlr-core-skin# .gdlr-core-pagination span{ border-color: #gdlr# }'
				),

			));

			return $options;
		}
	}

	// for advance skin settings
	add_filter('gdlr_core_skin_css', 'financity_gdlr_core_skin_css', 10, 2);
	if( !function_exists('financity_gdlr_core_skin_css') ){
		function financity_gdlr_core_skin_css( $css, $skins ){
			$extra_css = '';

			foreach( $skins as $skin ){
				if( !empty($skin['name']) && !empty($skin['button-background']) && !empty($skin['button-background-hover']) ){
					$extra_css .= '.gdlr-core-page-builder-body [data-skin="' . $skin['name'] . '"] .gdlr-core-button.gdlr-core-button-gradient{ ' .
					    'background: -webkit-linear-gradient(' . $skin['button-background'] . ', ' . $skin['button-background-hover'] . '); ' . 
					    'background: -o-linear-gradient(' . $skin['button-background'] . ', ' . $skin['button-background-hover'] . ');  ' . 
					    'background: -moz-linear-gradient(' . $skin['button-background'] . ', ' . $skin['button-background-hover'] . '); ' . 
						'background: linear-gradient(' . $skin['button-background'] . ', ' . $skin['button-background-hover'] . '); } ';
					$extra_css .= "\n";

					$extra_css .= '.gdlr-core-page-builder-body [data-skin="' . $skin['name'] . '"] .gdlr-core-button.gdlr-core-button-gradient-v{ ' .
					    'background: -webkit-linear-gradient(to right, ' . $skin['button-background'] . ', ' . $skin['button-background-hover'] . '); ' . 
					    'background: -o-linear-gradient(to right, ' . $skin['button-background'] . ', ' . $skin['button-background-hover'] . ');  ' . 
					    'background: -moz-linear-gradient(to right, ' . $skin['button-background'] . ', ' . $skin['button-background-hover'] . '); ' . 
						'background: linear-gradient(to right, ' . $skin['button-background'] . ', ' . $skin['button-background-hover'] . '); } ';
					$extra_css .= "\n";
				}
			}
			return $css . $extra_css;
		}
	}
	
	$financity_admin_option->add_element(apply_filters('financity_color_options',array(
	
		// color head section
		'title' => esc_html__('Color', 'financity'),
		'slug' => 'financity_color',
		'icon' => get_template_directory_uri() . '/include/options/images/color.png',
		'options' => array(
		
			// starting the subnav
			'skin' => array(
				'title' => esc_html__('Skin', 'financity'),
				'customizer' => false,
				'options' => array(
				
					'skin' => array(
						'title' => esc_html__('Skin Settings', 'financity'),
						'type' => 'custom',
						'item-type' => 'skin-settings',
						'wrapper-class' => 'gdlr-core-fullsize',
						'options' => apply_filters('gdlr_core_skin_options', array())
					),		
					
				) // skin-options
			), // skin-nav

			'header-color' => array(
				'title' => esc_html__('Header', 'financity'),
				'options' => array(
					
					'page-preload-background-color' => array(
						'title' => esc_html__('Page Preload Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-page-preload{ background-color: #gdlr#; }'
					),
					'top-bar-background-color' => array(
						'title' => esc_html__('Top Bar Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#222222',
						'selector' => '.financity-top-bar-background{ background-color: #gdlr#; }'
					),
					'top-bar-language-background-color' => array(
						'title' => esc_html__('Top Bar Language Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '',
						'selector' => '.financity-dropdown-wpml-flag .financity-dropdown-wpml-flag-background{ background-color: #gdlr#; }'
					),
					'top-bar-divider-color' => array(
						'title' => esc_html__('Top Bar Divider Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#e3e3e3',
						'selector' => '.financity-top-bar-divider:before{ border-color: #gdlr#; }'
					),
					'top-bar-bottom-border-color' => array(
						'title' => esc_html__('Top Bar Bottom Border Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-body .financity-top-bar{ border-bottom-color: #gdlr#; }'
					),
					'top-bar-text-color' => array(
						'title' => esc_html__('Top Bar Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-top-bar{ color: #gdlr#; }'
					),
					'top-bar-link-color' => array(
						'title' => esc_html__('Top Bar Link Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-body .financity-top-bar a{ color: #gdlr#; }'
					),
					'top-bar-link-hover-color' => array(
						'title' => esc_html__('Top Bar Link Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-body .financity-top-bar a:hover{ color: #gdlr#; }'
					),
					'top-bar-social-color' => array(
						'title' => esc_html__('Top Bar Social Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-top-bar .financity-top-bar-right-social a{ color: #gdlr#; }'
					),
					'top-bar-social-hover-color' => array(
						'title' => esc_html__('Top Bar Social Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#e44444',
						'selector' => '.financity-top-bar .financity-top-bar-right-social a:hover{ color: #gdlr#; }'
					),
					'top-bar-dropdown-background' => array(
						'title' => esc_html__('Top Bar Dropdown Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f3f3f3',
						'selector' => '.financity-body .gdlr-core-dropdown-tab .gdlr-core-dropdown-tab-head-wrap, .financity-body .financity-dropdown-wpml-flag .financity-dropdown-wpml-list{ background-color: #gdlr#; }'
					),
					'top-bar-dropdown-text' => array(
						'title' => esc_html__('Top Bar Dropdown Text', 'financity'),
						'type' => 'colorpicker',
						'default' => '#333333',
						'selector' => '.financity-body .gdlr-core-dropdown-tab .gdlr-core-dropdown-tab-head-wrap, .financity-body .financity-dropdown-wpml-flag .financity-dropdown-wpml-list a, ' .
							'.financity-body .financity-dropdown-wpml-flag .financity-dropdown-wpml-list a:hover{ color: #gdlr#; }'
					),
					'header-background-color' => array(
						'title' => esc_html__('Header Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-header-background, .financity-sticky-menu-placeholder, .financity-header-style-boxed.financity-fixed-navigation{ background-color: #gdlr#; }'
					),
					'header-plain-bottom-border-color' => array(
						'title' => esc_html__('Header Bottom Border Color ( Header Plain Style )', 'financity'),
						'type' => 'colorpicker',
						'default' => '#e8e8e8',
						'selector' => '.financity-header-wrap.financity-header-style-plain{ border-color: #gdlr#; }'
					),
					'header-right-title-color' => array(
						'title' => esc_html__('Header Right Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-logo-right-block .financity-logo-right-block-icon, .financity-logo-right-block .financity-logo-right-block-title{ color: #gdlr#; }'
					),					
					'header-right-caption-color' => array(
						'title' => esc_html__('Header Right Caption Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#bacdff',
						'selector' => '.financity-logo-right-block .financity-logo-right-block-caption{ color: #gdlr#; }'
					),					
					'header-right-button-text-color' => array(
						'title' => esc_html__('Header Right Button Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-body .financity-header-right-button, .financity-body .financity-header-right-button:hover{ color: #gdlr#; }'
					),					
					'header-right-button-background-color' => array(
						'title' => esc_html__('Header Right Button Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#3039ac',
						'selector' => '.financity-body .financity-header-right-button{ background-color: #gdlr#; }'
					),					
					'navigation-bar-background-color' => array(
						'title' => esc_html__('Navigation Bar Background Color ( Header Bar Style )', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f4f4f4',
						'selector' => '.financity-navigation-background{ background-color: #gdlr#; }'
					),
					'navigation-slide-bar-color' => array(
						'title' => esc_html__('Navigation Slide Bar Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#2d9bea',
						'selector' => '.financity-navigation .financity-navigation-slide-bar{ border-color: #gdlr#; }' . 
							'.financity-navigation .financity-navigation-slide-bar:before{ border-bottom-color: #gdlr#; }'
					),
					'logo-background-color' => array(
						'title' => esc_html__('Logo Background Color ( Header Side Menu Toggle Style )', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-header-side-nav.financity-style-side-toggle .financity-logo{ background-color: #gdlr#; }'
					),
					'navigation-bar-right-icon-color' => array(
						'title' => esc_html__('Navigation Bar Right Icon Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#383838',
						'selector'=> '.financity-main-menu-search i, .financity-main-menu-cart i{ color: #gdlr#; }'
					),
					'woocommerce-cart-icon-number-background' => array(
						'title' => esc_html__('Woocommmerce Cart\'s Icon Number Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#bd584e',
						'selector'=> '.financity-main-menu-cart > .financity-top-cart-count{ background-color: #gdlr#; }'
					),
					'woocommerce-cart-icon-number-color' => array(
						'title' => esc_html__('Woocommmerce Cart\'s Icon Number Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector'=> '.financity-main-menu-cart > .financity-top-cart-count{ color: #gdlr#; }'
					),
					'secondary-menu-icon-color' => array(
						'title' => esc_html__('Secondary Menu Icon Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#383838',
						'selector'=> '.financity-top-menu-button i, .financity-mobile-menu-button i{ color: #gdlr#; }' . 
							'.financity-mobile-button-hamburger:before, ' . 
							'.financity-mobile-button-hamburger:after, ' . 
							'.financity-mobile-button-hamburger span{ background: #gdlr#; }'
					),
					'secondary-menu-border-color' => array(
						'title' => esc_html__('Secondary Menu Border Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#dddddd',
						'selector'=> '.financity-main-menu-right .financity-top-menu-button, .financity-mobile-menu .financity-mobile-menu-button{ border-color: #gdlr#; }'
					),
					'search-overlay-background-color' => array(
						'title' => esc_html__('Search Overlay Background Color', 'financity'),
						'type' => 'colorpicker',
						'data-type' => 'rgba',
						'default' => '#000000',
						'selector'=> '.financity-top-search-wrap{ background-color: #gdlr#; background-color: rgba(#gdlra#, 0.88); }'
					),
					'top-cart-background-color' => array(
						'title' => esc_html__('Top Cart Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#303030',
						'selector'=> '.financity-top-cart-content-wrap .financity-top-cart-content{ background-color: #gdlr#; }'
					),
					'top-cart-text-color' => array(
						'title' => esc_html__('Top Cart Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#b5b5b5',
						'selector'=> '.financity-top-cart-content-wrap .financity-top-cart-content span, ' .
							'.financity-top-cart-content-wrap .financity-top-cart-content span.woocommerce-Price-amount.amount{ color: #gdlr#; }'
					),
					'top-cart-view-cart-color' => array(
						'title' => esc_html__('Top Cart : View Cart Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector'=> '.financity-top-cart-content-wrap .financity-top-cart-button,' .
							'.financity-top-cart-content-wrap .financity-top-cart-button:hover{ color: #gdlr#; }'
					),
					'top-cart-checkout-color' => array(
						'title' => esc_html__('Top Cart : Checkout Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#bd584e',
						'selector'=> '.financity-top-cart-content-wrap .financity-top-cart-checkout-button, ' .
							'.financity-top-cart-content-wrap .financity-top-cart-checkout-button:hover{ color: #gdlr#; }'
					),
					'breadcrumbs-background-color' => array(
						'title' => esc_html__('Breadcrumbs ( Plugin ) Background Color', 'financity'),
						'type' => 'colorpicker',
						'data-type' => 'rgba',
						'default' => '#f3f3f3',
						'selector'=> '.financity-breadcrumbs{ background-color: #gdlr#; }'
					),
					'breadcrumbs-text-color' => array(
						'title' => esc_html__('Breadcrumbs ( Plugin ) Text Color', 'financity'),
						'type' => 'colorpicker',
						'data-type' => 'rgba',
						'default' => '#c0c0c0',
						'selector'=> '.financity-body .financity-breadcrumbs, .financity-body .financity-breadcrumbs a span{ color: #gdlr#; }' . 
							'.financity-body .gdlr-core-breadcrumbs-item, .financity-body .gdlr-core-breadcrumbs-item a span{ color: #gdlr#; }'
					),
					'breadcrumbs-text-active-color' => array(
						'title' => esc_html__('Breadcrumbs ( Plugin ) Text Active Color', 'financity'),
						'type' => 'colorpicker',
						'data-type' => 'rgba',
						'default' => '#777777',
						'selector'=> '.financity-body .financity-breadcrumbs span, .financity-body .financity-breadcrumbs a:hover span{ color: #gdlr#; }' . 
							'.financity-body .gdlr-core-breadcrumbs-item span, .financity-body .gdlr-core-breadcrumbs-item a:hover span{ color: #gdlr#; }' 

					),

				) // header-options
			), // header-color

			'navigation-menu-color' => array(
				'title' => esc_html__('Menu / Navigation', 'financity'),
				'options' => array(

					'main-menu-text-color' => array(
						'title' => esc_html__('Main Menu Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#999999',
						'selector' => '.sf-menu > li > a, .sf-vertical > li > a{ color: #gdlr#; }'
					),
					'main-menu-text-hover-color' => array(
						'title' => esc_html__('Main Menu Text Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#333333',
						'selector' => '.sf-menu > li > a:hover, ' . 
							'.sf-menu > li.current-menu-item > a, ' .
							'.sf-menu > li.current-menu-ancestor > a, ' .
							'.sf-vertical > li > a:hover, ' . 
							'.sf-vertical > li.current-menu-item > a, ' .
							'.sf-vertical > li.current-menu-ancestor > a{ color: #gdlr#; }'
					),
					'sub-menu-background-color' => array(
						'title' => esc_html__('Sub Menu Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#2e2e2e',
						'selector'=> '.sf-menu > .financity-normal-menu li, .sf-menu > .financity-mega-menu > .sf-mega, ' . 
							'.sf-vertical ul.sub-menu li, ul.sf-menu > .menu-item-language li{ background-color: #gdlr#; }'
					),
					'sub-menu-text-color' => array(
						'title' => esc_html__('Sub Menu Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#bebebe',
						'selector'=> '.sf-menu > li > .sub-menu a, .sf-menu > .financity-mega-menu > .sf-mega a, ' . 
							'.sf-vertical ul.sub-menu li a{ color: #gdlr#; }'
					),
					'sub-menu-text-hover-color' => array(
						'title' => esc_html__('Sub Menu Text Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector'=> '.sf-menu > li > .sub-menu a:hover, ' . 
							'.sf-menu > li > .sub-menu .current-menu-item > a, ' . 
							'.sf-menu > li > .sub-menu .current-menu-ancestor > a, '.
							'.sf-menu > .financity-mega-menu > .sf-mega a:hover, '.
							'.sf-menu > .financity-mega-menu > .sf-mega .current-menu-item > a, '.
							'.sf-vertical > li > .sub-menu a:hover, ' . 
							'.sf-vertical > li > .sub-menu .current-menu-item > a, ' . 
							'.sf-vertical > li > .sub-menu .current-menu-ancestor > a{ color: #gdlr#; }'
					),
					'sub-menu-text-hover-background-color' => array(
						'title' => esc_html__('Sub Menu Text Hover Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#393939',
						'selector'=> '.sf-menu > li > .sub-menu a:hover, ' . 
							'.sf-menu > li > .sub-menu .current-menu-item > a, ' . 
							'.sf-menu > li > .sub-menu .current-menu-ancestor > a, '.
							'.sf-menu > .financity-mega-menu > .sf-mega a:hover, '.
							'.sf-menu > .financity-mega-menu > .sf-mega .current-menu-item > a, '.
							'.sf-vertical > li > .sub-menu a:hover, ' . 
							'.sf-vertical > li > .sub-menu .current-menu-item > a, ' . 
							'.sf-vertical > li > .sub-menu .current-menu-ancestor > a{ background-color: #gdlr#; }'
					),
					'sub-mega-menu-title-color' => array(
						'title' => esc_html__('Sub Mega Menu Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector'=> '.financity-navigation .sf-menu > .financity-mega-menu .sf-mega-section-inner > a{ color: #gdlr#; }'
					),
					'sub-mega-menu-divider-color' => array(
						'title' => esc_html__('Sub Mega Menu Divider Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#424242',
						'selector'=> '.financity-navigation .sf-menu > .financity-mega-menu .sf-mega-section{ border-color: #gdlr#; }'
					),
					'side-menu-text-color' => array(
						'title' => esc_html__('Side Menu Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#979797',
						'selector'=> '.mm-navbar .mm-title, .mm-navbar .mm-btn, ul.mm-listview li > a, ul.mm-listview li > span{ color: #gdlr#; }' . 
							'ul.mm-listview li a{ border-color: #gdlr#; }' .
							'.mm-arrow:after, .mm-next:after, .mm-prev:before{ border-color: #gdlr#; }'
					),
					'side-menu-text-hover-color' => array(
						'title' => esc_html__('Side Menu Text Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector'=> '.mm-navbar .mm-title:hover, .mm-navbar .mm-btn:hover, ' .
							'ul.mm-listview li a:hover, ul.mm-listview li > span:hover, ' . 
							'ul.mm-listview li.current-menu-item > a, ul.mm-listview li.current-menu-ancestor > a, ul.mm-listview li.current-menu-ancestor > span{ color: #gdlr#; }'
					),
					'side-menu-background-color' => array(
						'title' => esc_html__('Side Menu Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#1f1f1f',
						'selector'=> '.mm-menu{ background-color: #gdlr#; }'
					),
					'side-menu-border-color' => array(
						'title' => esc_html__('Side Menu Border Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#626262',
						'selector'=> 'ul.mm-listview li{ border-color: #gdlr#; }'
					),
					'overlay-menu-background-color' => array(
						'title' => esc_html__('Overlay Menu Background Color', 'financity'),
						'type' => 'colorpicker',
						'data-type' => 'rgba',
						'default' => '#000000',
						'selector'=> '.financity-overlay-menu-content{ background-color: #gdlr#; background-color: rgba(#gdlra#, 0.88); }'
					),
					'overlay-menu-border-color' => array(
						'title' => esc_html__('Overlay Menu Border Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#424242',
						'selector'=> '.financity-overlay-menu-content ul.menu > li, .financity-overlay-menu-content ul.sub-menu ul.sub-menu{ border-color: #gdlr#; }'
					),
					'overlay-menu-text-color' => array(
						'title' => esc_html__('Overlay Menu Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector'=> '.financity-overlay-menu-content ul li a, .financity-overlay-menu-content .financity-overlay-menu-close{ color: #gdlr#; }'
					),
					'overlay-menu-text-hover-color' => array(
						'title' => esc_html__('Overlay Menu Text Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#a8a8a8',
						'selector'=> '.financity-overlay-menu-content ul li a:hover{ color: #gdlr#; }'
					),
					'anchor-bullet-background-color' => array(
						'title' => esc_html__('Anchor Bullet Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#777777',
						'selector'=> '.financity-bullet-anchor a:before{ background-color: #gdlr#; }'
					),
					'anchor-bullet-background-active-color' => array(
						'title' => esc_html__('Anchor Bullet Background Active', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector'=> '.financity-bullet-anchor a:hover, .financity-bullet-anchor a.current-menu-item{ border-color: #gdlr#; }' .
							'.financity-bullet-anchor a:hover:before, .financity-bullet-anchor a.current-menu-item:before{ background: #gdlr#; }'
					),
					'navigation-right-button-text-color' => array(
						'title' => esc_html__('Navigation Right Button Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#333333',
						'selector'=> '.financity-body .financity-main-menu-right-button{ color: #gdlr#; }'
					),
					'navigation-right-button-background-color' => array(
						'title' => esc_html__('Navigation Right Button Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '',
						'selector'=> '.financity-body .financity-main-menu-right-button{ background-color: #gdlr#; }'
					),
					'navigation-right-button-border-color' => array(
						'title' => esc_html__('Navigation Right Button Border Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#333333',
						'selector'=> '.financity-body .financity-main-menu-right-button{ border-color: #gdlr#; }'
					),
					'navigation-right-button-text-hover-color' => array(
						'title' => esc_html__('Navigation Right Button Text Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#555555',
						'selector'=> '.financity-body .financity-main-menu-right-button:hover{ color: #gdlr#; }'
					),
					'navigation-right-button-background-hover-color' => array(
						'title' => esc_html__('Navigation Right Button Background Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '',
						'selector'=> '.financity-body .financity-main-menu-right-button:hover{ background-color: #gdlr#; }'
					),
					'navigation-right-button-border-hover-color' => array(
						'title' => esc_html__('Navigation Right Button Border Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#555555',
						'selector'=> '.financity-body .financity-main-menu-right-button:hover{ border-color: #gdlr#; }'
					),
					
										
				) // navigation-menu-options
			), // navigation-menu-nav			
			
			'body-color' => array(
				'title' => esc_html__('Body', 'financity'),
				'options' => array(
				
					'body-background-color' => array(
						'title' => esc_html__('Body Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-body-outer-wrapper, body.financity-full .financity-body-wrapper, .financity-header-side-content{ background-color: #gdlr#; }'
					),
					'container-background-color' => array(
						'title' => esc_html__('Container Background Color ( For Boxed Layout )', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => 'body.financity-boxed .financity-body-wrapper, ' .
							'.gdlr-core-page-builder .gdlr-core-page-builder-body.gdlr-core-pb-livemode{ background-color: #gdlr#; }'
					),
					'page-title-color' => array(
						'title' => esc_html__('Page Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-page-title-wrap .financity-page-title{ color: #gdlr#; }'
					),
					'page-caption-color' => array(
						'title' => esc_html__('Page Caption Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-page-title-wrap .financity-page-caption{ color: #gdlr#; }' .
							'.financity-page-title-wrap .financity-page-caption .woocommerce-breadcrumb, .financity-page-title-wrap .financity-page-caption .woocommerce-breadcrumb a{ color: #gdlr#; }'
					),
					'page-title-background-overlay-color' => array(
						'title' => esc_html__('Page Title Background Overlay Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#000000',
						'selector' => '.financity-page-title-wrap .financity-page-title-overlay{ background-color: #gdlr#; }'
					),				
					'body-content-color' => array(
						'title' => esc_html__('Body Content Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#9b9b9b',
						'selector' => '.financity-body, .financity-body span.wpcf7-not-valid-tip{ color: #gdlr#; }'
					),				
					'heading-color' => array(
						'title' => esc_html__('Heading Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#383838',
						'selector' => '.financity-body h1, .financity-body h2, .financity-body h3, ' . 
							'.financity-body h4, .financity-body h5, .financity-body h6{ color: #gdlr#; }' . 
							'.woocommerce table.shop_attributes th, .woocommerce table.shop_table th, ' . 
							'.single-product.woocommerce div.product .product_meta .financity-head{ color: #gdlr#; }'
					),				
					'link-color' => array(
						'title' => esc_html__('Link Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#545454',
						'selector' => '.financity-body a{ color: #gdlr#; }'
					),				
					'link-hover-color' => array(
						'title' => esc_html__('Link Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#333333',
						'selector' => '.financity-body a:hover{ color: #gdlr#; }'
					),
					'divider-color' => array(
						'title' => esc_html__('Divider Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#e6e6e6',
						'selector' => '.financity-body *{ border-color: #gdlr#; }' . 
							'.financity-body .gdlr-core-portfolio-single-nav-wrap{ border-color: #gdlr#; }' .
							'.gdlr-core-columnize-item .gdlr-core-columnize-item-content{ column-rule-color: #gdlr#; -moz-column-rule-color: #gdlr#; -webkit-column-rule-color: #gdlr#; }'
					),
					'input-box-background-color' => array(
						'title' => esc_html__('Input Box Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-body input, ' .  
							'.financity-body textarea{ background-color: #gdlr#; }'
					),
					'input-box-border-color' => array(
						'title' => esc_html__('Input Box Border Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#d7d7d7',
						'selector' => '.financity-body input, ' .  
							'.financity-body textarea{ border-color: #gdlr#; }'
					),
					'input-box-text-color' => array(
						'title' => esc_html__('Input Box Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#4e4e4e',
						'selector' => '.financity-body input, ' .  
							'.financity-body textarea{ color: #gdlr#; }'
					),
					'input-box-placeholder-color' => array(
						'title' => esc_html__('Input Box Placeholder Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#4e4e4e',
						'selector' => '.financity-body ::-webkit-input-placeholder{  color: #gdlr#; }' .
							'.financity-body ::-moz-placeholder{  color: #gdlr#; }' .
							'.financity-body :-ms-input-placeholder{  color: #gdlr#; }' .
							'.financity-body :-moz-placeholder{  color: #gdlr#; }'
					),
					
				) // body-options
			), // body-nav	
					
			'404-color' => array(
				'title' => esc_html__('404 Page', 'financity'),
				'options' => array(		

					'404-content-background-color' => array(
						'title' => esc_html__('404 Content Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#23618e',
						'selector' => '.financity-not-found-wrap{ background-color: #gdlr#; }'
					),
					'404-head-color' => array(
						'title' => esc_html__('404 Head Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-not-found-wrap .financity-not-found-head{ color: #gdlr#; }'
					),
					'404-title-color' => array(
						'title' => esc_html__('404 Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-not-found-wrap .financity-not-found-title{ color: #gdlr#; }'
					),
					'404-caption-color' => array(
						'title' => esc_html__('404 Caption Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#c3e7ff',
						'selector' => '.financity-not-found-wrap .financity-not-found-caption{ color: #gdlr#; }'
					),
					'404-input-background-color' => array(
						'title' => esc_html__('404 Input Background Color', 'financity'),
						'type' => 'colorpicker',
						'data-type' => 'rgba',
						'default' => '#000000',
						'selector' => '.financity-not-found-wrap form.search-form input.search-field{ background-color: #gdlr#; background-color: rgba(#gdlra#, 0.4) }'
					),
					'404-input-text-color' => array(
						'title' => esc_html__('404 Input Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-not-found-wrap form.search-form input.search-field, ' . 
							'.financity-not-found-wrap .financity-top-search-submit{ color: #gdlr#; } ' . 
							'.financity-not-found-wrap input::-webkit-input-placeholder { color: #gdlr#; } ' . 
							'.financity-not-found-wrap input:-moz-placeholder{ color: #gdlr#; } ' . 
							'.financity-not-found-wrap input::-moz-placeholder{ color: #gdlr#; } ' . 
							'.financity-not-found-wrap input:-ms-input-placeholder{ color: #gdlr#; }'
					),
					'404-back-to-home-color' => array(
						'title' => esc_html__('404 Back To Home', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-not-found-wrap .financity-not-found-back-to-home a, .financity-not-found-wrap .financity-not-found-back-to-home a:hover{ color: #gdlr#; }'
					),

				)
			),

			'sidebar-color' => array(
				'title' => esc_html__('Sidebar / Widget', 'financity'),
				'options' => array(	

					'sidebar-title-color' => array(
						'title' => esc_html__('Sidebar Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#383838',
						'selector' => '.financity-sidebar-area .financity-widget-title{ color: #gdlr#; }'
					),
					'sidebar-link-color' => array(
						'title' => esc_html__('Sidebar Link Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#484848',
						'selector' => '.financity-sidebar-area a{ color: #gdlr#; }' . 
						 	'.widget_recent_entries ul li.current-menu-item > a:before, .widget_recent_comments ul li.current-menu-item > a:before, ' .
						 	'.widget_pages ul li.current-menu-item > a:before, .widget_rss ul li.current-menu-item > a:before, ' .
						 	'.widget_archive ul li.current-menu-item > a:before, .widget_categories ul li.current-menu-item > a:before, .widget_nav_menu ul li.current-menu-item > a:before, ' .
						 	'.widget_meta ul li.current-menu-item > a:before{ color: #gdlr#; }' .
						 	'.widget_recent_entries ul li > a:hover:before, .widget_recent_comments ul li > a:hover:before, ' .
						 	'.widget_pages ul li > a:hover:before, .widget_rss ul li > a:hover:before, ' .
						 	'.widget_archive ul li > a:hover:before, .widget_categories ul li > a:hover:before, .widget_nav_menu ul li > a:hover:before, ' .
						 	'.widget_meta ul li > a:hover:before{ color: #gdlr#; }'
					),
					'sidebar-link-hover-color' => array(
						'title' => esc_html__('Sidebar Link Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#323232',
						'selector' => '.financity-sidebar-area a:hover, .financity-sidebar-area .current-menu-item > a{ color: #gdlr#; }'
					),
					'recent-post-widget-info-icon-color' => array(
						'title' => esc_html__('Recent Post Widget Info Icon', 'financity'),
						'type' => 'colorpicker',
						'default' => '#9c9c9c',
						'selector' => '.gdlr-core-recent-post-widget .gdlr-core-blog-info i{ color: #gdlr#; }'
					),
					'recent-post-widget-info-link-color' => array(
						'title' => esc_html__('Recent Post Widget Info Link', 'financity'),
						'type' => 'colorpicker',
						'default' => '#a0a0a0',
						'selector' => '.gdlr-core-recent-post-widget .gdlr-core-blog-info, ' .
							'.gdlr-core-recent-post-widget .gdlr-core-blog-info a, ' .
							'.gdlr-core-recent-post-widget .gdlr-core-blog-info a:hover{ color: #gdlr#; }'
					),
					'post-slider-widget-title-color' => array(
						'title' => esc_html__('Post Slider Widget Title', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-post-slider-widget-overlay .gdlr-core-post-slider-widget-title{ color: #gdlr#; }'
					),
					'post-slider-widget-info-color' => array(
						'title' => esc_html__('Post Slider Widget Info', 'financity'),
						'type' => 'colorpicker',
						'default' => '#9c9c9c',
						'selector' => '.gdlr-core-post-slider-widget-overlay .gdlr-core-blog-info, ' .
							'.gdlr-core-post-slider-widget-overlay .gdlr-core-blog-info i, ' .
							'.gdlr-core-post-slider-widget-overlay .gdlr-core-blog-info a, ' .
							'.gdlr-core-post-slider-widget-overlay .gdlr-core-blog-info a:hover{ color: #gdlr#; }'
					),
					'search-box-text-color' => array(
						'title' => esc_html__('Search Box Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#b5b5b5',
						'selector' => '.widget_search input.search-field{ color: #gdlr#; }' .
							'.widget_search input::-webkit-input-placeholder { color: #gdlr#; }' . 
							'.widget_search input:-moz-placeholder{ color: #gdlr#; }' . 
							'.widget_search input::-moz-placeholder{ color: #gdlr#; }' . 
							'.widget_search input:-ms-input-placeholder{ color: #gdlr#; }'
					),
					'search-box-border-color' => array(
						'title' => esc_html__('Search Box Border Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#e0e0e0',
						'selector' => '.widget_search input.search-field{ border-color: #gdlr#; }'
					),
					'search-box-icon-color' => array(
						'title' => esc_html__('Search Box Icon Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#c7c7c7',
						'selector' => '.widget_search form:after{ border-color: #gdlr#; }'
					),
					'twitter-widget-icon-color' => array(
						'title' => esc_html__('Twitter Widget Icon Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#303030',
						'selector' => 'ul.gdlr-core-twitter-widget-wrap li:before{ color: #gdlr#; }'
					),
					'twitter-widget-date-color' => array(
						'title' => esc_html__('Twitter Widget Date Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#b5b5b5',
						'selector' => 'ul.gdlr-core-twitter-widget-wrap li .gdlr-core-twitter-widget-date a, ul.gdlr-core-twitter-widget-wrap li .gdlr-core-twitter-widget-date a:hover{ color: #gdlr#; }'
					),

				) // body-options
			), // body-nav	
			
			'footer-copyright-color' => array(
				'title' => esc_html__('Footer/Copyright', 'financity'),
				'options' => array(
				
					'footer-background-color' => array(
						'title' => esc_html__('Footer Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#202020',
						'selector' => '.financity-footer-wrapper{ background-color: #gdlr#; }'
					),
					'footer-title-color' => array(
						'title' => esc_html__('Footer Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-footer-wrapper .financity-widget-title{ color: #gdlr#; }' . 
							'.financity-footer-wrapper h1, .financity-footer-wrapper h3, .financity-footer-wrapper h3, ' . 
							'.financity-footer-wrapper h4, .financity-footer-wrapper h5, .financity-footer-wrapper h6{ color: #gdlr#; } ' 
					),
					'footer-content-color' => array(
						'title' => esc_html__('Footer Content Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ababab',
						'selector' => '.financity-footer-wrapper{ color: #gdlr#; }'
					),
					'footer-link-color' => array(
						'title' => esc_html__('Footer Link Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-footer-wrapper a{ color: #gdlr#; }' .  
							'.financity-footer-wrapper .widget_recent_entries ul li.current-menu-item > a:before, .financity-footer-wrapper .widget_recent_comments ul li.current-menu-item > a:before, ' .
						 	'.financity-footer-wrapper .widget_pages ul li.current-menu-item > a:before, .financity-footer-wrapper .widget_rss ul li.current-menu-item > a:before, ' .
						 	'.financity-footer-wrapper .widget_archive ul li.current-menu-item > a:before, .financity-footer-wrapper .widget_categories ul li.current-menu-item > a:before, .financity-footer-wrapper .widget_nav_menu ul li.current-menu-item > a:before, ' .
						 	'.financity-footer-wrapper .widget_meta ul li.current-menu-item > a:before{ color: #gdlr#; }' . 
							'.financity-footer-wrapper .widget_recent_entries ul li > a:hover:before, .financity-footer-wrapper .widget_recent_comments ul li > a:hover:before, ' .
						 	'.financity-footer-wrapper .widget_pages ul li > a:hover:before, .financity-footer-wrapper .widget_rss ul li > a:hover:before, ' .
						 	'.financity-footer-wrapper .widget_archive ul li > a:hover:before, .financity-footer-wrapper .widget_categories ul li > a:hover:before, .financity-footer-wrapper .widget_nav_menu ul li > a:hover:before, ' .
						 	'.financity-footer-wrapper .widget_meta ul li > a:hover:before{ color: #gdlr#; }'
					),
					'footer-link-hover-color' => array(
						'title' => esc_html__('Footer Link Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-footer-wrapper a:hover, .financity-footer-wrapper .current-menu-item > a{ color: #gdlr#; }'
					),
					'footer-divider-color' => array(
						'title' => esc_html__('Footer Divider Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#303030',
						'selector' => '.financity-footer-wrapper, .financity-footer-wrapper *{ border-color: #gdlr#; }'
					),
					'footer-input-background-color' => array(
						'title' => esc_html__('Footer Input Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#2e2e2e',
						'selector' => '.financity-footer-wrapper input, .financity-footer-wrapper textarea{ background-color: #gdlr#; }'
					),
					'footer-input-text-color' => array(
						'title' => esc_html__('Footer Input Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#a3a3a3',
						'selector' => '.financity-footer-wrapper input, .financity-footer-wrapper textarea{ color: #gdlr#; }' . 
							'.financity-footer-wrapper ::-webkit-input-placeholder{ color: #gdlr#; }' .
							'.financity-footer-wrapper ::-moz-placeholder{ color: #gdlr#; }' .
							'.financity-footer-wrapper :-ms-input-placeholder{ color: #gdlr#; }' .
							'.financity-footer-wrapper :-moz-placeholder{ color: #gdlr#; }'
					),
					
					'copyright-background-color' => array(
						'title' => esc_html__('Copyright Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#181818',
						'selector' => '.financity-copyright-wrapper{ background-color: #gdlr#; }'
					),
					'copyright-text-color' => array(
						'title' => esc_html__('Copyright Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#838383',
						'selector' => '.financity-copyright-wrapper{ color: #gdlr#; }'
					),
					'copyright-link-color' => array(
						'title' => esc_html__('Copyright Link Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#838383',
						'selector' => '.financity-copyright-wrapper a{ color: #gdlr#; }'
					),
					'copyright-link-hover-color' => array(
						'title' => esc_html__('Copyright Link Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#838383',
						'selector' => '.financity-copyright-wrapper a:hover{ color: #gdlr#; }'
					),
					'back-to-top-background-color' => array(
						'title' => esc_html__('Back To Top Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#dbdbdb',
						'selector' => '.financity-footer-back-to-top-button{ background-color: #gdlr#; }'
					),
					'back-to-top-text-color' => array(
						'title' => esc_html__('Back To Top Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#313131',
						'selector' => '.financity-body .financity-footer-back-to-top-button, .financity-body .financity-footer-back-to-top-button:hover{ color: #gdlr#; }'
					),
					
				) // footer-copyright-options
			), // footer-copyright-nav
		
			'single-blog' => array(
				'title' => esc_html__('Single Blog', 'financity'),
				'options' => array(		

					'single-blog-title-color' => array(
						'title' => esc_html__('Single Blog ( Main Title ) Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-body .financity-blog-title-wrap .financity-single-article-title{ color: #gdlr#; }'
					),
					'single-blog-info-color' => array(
						'title' => esc_html__('Single Blog ( Main Title )  Info Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-blog-title-wrap .financity-blog-info-wrapper, .financity-blog-title-wrap .financity-blog-info-wrapper a, ' . 
							'.financity-blog-title-wrap .financity-blog-info-wrapper a:hover, .financity-blog-title-wrap .financity-blog-info-wrapper i{ color: #gdlr#; }'
					),
					'single-blog-date-day-color' => array(
						'title' => esc_html__('Single Blog ( Main Title )  Date Day Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-blog-title-wrap .financity-single-article-date-day{ color: #gdlr#; }'
					),
					'single-blog-date-month-color' => array(
						'title' => esc_html__('Single Blog ( Main Title )  Date Month Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#b1b1b1',
						'selector' => '.financity-blog-title-wrap .financity-single-article-date-month, .financity-single-article-date-wrapper .financity-single-article-date-year{ color: #gdlr#; }'
					),
					'single-blog-divider-color' => array(
						'title' => esc_html__('Single Blog ( Main Title ) Date Divider Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-blog-title-wrap .financity-single-article-date-wrapper{ border-color: #gdlr#; }'
					),
					'single-blog-title-background-overlay-color' => array(
						'title' => esc_html__('Single Blog ( Main Title ) Background Overlay Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#000000',
						'selector' => '.financity-blog-title-wrap .financity-blog-title-overlay{ background-color: #gdlr#; }'
					),
					'single-blog-author-title-color' => array(
						'title' => esc_html__('Single Blog Author Title', 'financity'),
						'type' => 'colorpicker',
						'default' => '#464646',
						'selector' => '.financity-single-author .financity-single-author-title a, .financity-single-author .financity-single-author-title a:hover{ color: #gdlr#; }'
					),
					'single-blog-author-caption-color' => array(
						'title' => esc_html__('Single Blog Author Caption', 'financity'),
						'type' => 'colorpicker',
						'default' => '#b1b1b1',
						'selector' => '.financity-single-author .financity-single-author-caption{ color: #gdlr#; }'
					),
					'single-blog-navigation-color' => array(
						'title' => esc_html__('Single Blog Navigation Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#bcbcbc',
						'selector' => '.financity-single-nav a, .financity-single-nav a:hover{ color: #gdlr#; }'
					),
					'single-blog-comment-title-color' => array(
						'title' => esc_html__('Single Blog Comment Title', 'financity'),
						'type' => 'colorpicker',
						'default' => '#464646',
						'selector' => '.financity-comments-area .financity-comments-title, .financity-comments-area .comment-author, ' . 
							'.financity-comments-area .comment-reply-title{ color: #gdlr#; }'
					),
					'single-blog-comment-background-color' => array(
						'title' => esc_html__('Single Blog Comment Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f9f9f9',
						'selector' => '.financity-comments-area .comment-respond{ background-color: #gdlr#; }'
					),
					'single-blog-comment-reply-color' => array(
						'title' => esc_html__('Single Comment Reply Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#bcbcbc',
						'selector' => '.financity-comments-area .comment-reply a, .financity-comments-area .comment-reply a:hover{ color: #gdlr#; }'
					),
					'single-blog-comment-time-color' => array(
						'title' => esc_html__('Single Comment Time Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#b1b1b1',
						'selector' => '.financity-comments-area .comment-time a, .financity-comments-area .comment-time a:hover{ color: #gdlr#; }'
					),

				)
			), // single-blog

			'blog-color' => array(
				'title' => esc_html__('Blog / Pagination', 'financity'),
				'options' => array(

					'blog-title-color' => array(
						'title' => esc_html__('Blog Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#343434',
						'selector' => '.gdlr-core-blog-title a, .financity-body .financity-single-article-title, .financity-body .financity-single-article-title a{ color: #gdlr#; }'
					),
					'blog-title-hover-color' => array(
						'title' => esc_html__('Blog Title Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#343434',
						'selector' => '.gdlr-core-blog-title a:hover, .financity-body .financity-single-article-title a:hover{ color: #gdlr#; }'
					),
					'blog-sticky-banner-color' => array(
						'title' => esc_html__('Blog Sticky Banner Text', 'financity'),
						'type' => 'colorpicker',
						'default' => '#444444',
						'selector' => '.gdlr-core-sticky-banner, .financity-sticky-banner{ color: #gdlr#; }'
					),
					'blog-sticky-banner-background' => array(
						'title' => esc_html__('Blog Sticky Banner Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f3f3f3',
						'selector' => '.gdlr-core-sticky-banner, .financity-sticky-banner{ background-color: #gdlr#; }'
					),
					'blog-info-color' => array(
						'title' => esc_html__('Blog Info Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#959595',
						'selector' => '.financity-single-article .financity-blog-info-wrapper, .financity-single-article .financity-blog-info-wrapper a, ' . 
							'.financity-single-article .financity-blog-info-wrapper a:hover, .financity-single-article .financity-blog-info-wrapper i, ' .
							'.gdlr-core-blog-info-wrapper, .gdlr-core-blog-info-wrapper a, .gdlr-core-blog-info-wrapper a:hover, .gdlr-core-blog-info-wrapper i, ' .
							'.gdlr-core-blog-grid .gdlr-core-blog-info-date a{ color: #gdlr#; }'
					),
					'blog-date-day-color' => array(
						'title' => esc_html__('Blog Date Day Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#353535',
						'selector' => '.gdlr-core-blog-date-wrapper .gdlr-core-blog-date-day, .financity-single-article .financity-single-article-date-day{ color: #gdlr#; }'
					),
					'blog-date-month-color' => array(
						'title' => esc_html__('Blog Date Month Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#8a8a8a',
						'selector' => '.gdlr-core-blog-date-wrapper .gdlr-core-blog-date-month, .financity-single-article .financity-single-article-date-month{ color: #gdlr#; }'
					),
					'blog-frame-background-color' => array(
						'title' => esc_html__('Blog Frame Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f9f9f9',
						'selector' => '.gdlr-core-blog-grid.gdlr-core-blog-grid-with-frame, .gdlr-core-blog-full-frame, .gdlr-core-blog-list-frame, ' . 
							'.gdlr-core-blog-link-format{ background-color: #gdlr#; }'
					),
					'blog-modern-hover-background-color' => array(
						'title' => esc_html__('Blog Thumbnail ( Opacity ) Hover Background Color', 'financity'),
						'type' => 'colorpicker', 
						'default' => '#000',
						'selector' => '.gdlr-core-opacity-on-hover{ background: #gdlr#; }'
					),
					'blog-thumbnail-category-background' => array(
						'title' => esc_html__('Blog Thumbnail Category Background Color', 'financity'),
						'type' => 'colorpicker',
						'data-type' => 'rgba',
						'default' => '#343de2',
						'selector' => '.gdlr-core-style-2 .gdlr-core-blog-thumbnail .gdlr-core-blog-info-category{ background: #gdlr#; }' .
							'.gdlr-core-blog-feature .gdlr-core-blog-info-category{ background: #gdlr#; }'  . 
							'.gdlr-core-recent-post-widget-thumbnail .gdlr-core-blog-info-category{ background: #gdlr#; }'
					),
					'blog-modern-text-color' => array(
						'title' => esc_html__('Blog Modern/Metro Overlay Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-blog-modern.gdlr-core-with-image .gdlr-core-blog-info-wrapper, ' .
							'.gdlr-core-blog-modern.gdlr-core-with-image .gdlr-core-blog-info-wrapper a, ' .
							'.gdlr-core-blog-modern.gdlr-core-with-image .gdlr-core-blog-info-wrapper i, ' .
							'.gdlr-core-blog-modern.gdlr-core-with-image .gdlr-core-blog-title a{ color: #gdlr#; } ' . 
							'.gdlr-core-blog-modern.gdlr-core-with-image .gdlr-core-blog-content{ color: #gdlr#; }' .
							'.gdlr-core-blog-metro.gdlr-core-with-image .gdlr-core-blog-info-wrapper, ' .
							'.gdlr-core-blog-metro.gdlr-core-with-image .gdlr-core-blog-info-wrapper a, ' .
							'.gdlr-core-blog-metro.gdlr-core-with-image .gdlr-core-blog-info-wrapper i, ' .
							'.gdlr-core-blog-metro.gdlr-core-with-image .gdlr-core-blog-title a{ color: #gdlr#; }'
					),
					'blog-aside-background-color' => array(
						'title' => esc_html__('Blog Quote / Aside Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#2d9bea',
						'selector' => '.financity-blog-aside-format .financity-single-article-content, ' . 
							'.gdlr-core-blog-aside-format{ background-color: #gdlr#; }' . 
							'.financity-blog-quote-format .financity-single-article-content, ' . 
							'.gdlr-core-blog-quote-format{ background-color: #gdlr#; }'
					),
					'blog-aside-text-color' => array(
						'title' => esc_html__('Blog Quote / Aside Text', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.financity-blog-aside-format .financity-single-article-content, ' . 
							'.gdlr-core-blog-aside-format .gdlr-core-blog-content{ color: #gdlr#; }' . 
							'.financity-blog-quote-format .financity-single-article-content blockquote, ' . 
							'.financity-blog-quote-format .financity-single-article-content q, ' . 
							'.financity-blog-quote-format .financity-single-article-content, ' . 
							'.gdlr-core-blog-quote-format .gdlr-core-blog-content blockquote,' .
							'.gdlr-core-blog-quote-format .gdlr-core-blog-content q,' .
							'.gdlr-core-blog-quote-format .gdlr-core-blog-content{ color: #gdlr#; }'
					),
					'pagination-background-color' => array(
						'title' => esc_html__('Pagination Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f0f0f0',
						'selector' => '.gdlr-core-pagination a{ background-color: #gdlr#; }'
					),
					'pagination-text-color' => array(
						'title' => esc_html__('Pagination Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#929292',
						'selector' => '.gdlr-core-pagination a{ color: #gdlr#; }'
					),
					'pagination-background-hover-color' => array(
						'title' => esc_html__('Pagination Background Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#2f9cea',
						'selector' => '.gdlr-core-pagination a:hover, .gdlr-core-pagination a.gdlr-core-active, .gdlr-core-pagination span{ background-color: #gdlr#; }'
					),
					'pagination-text-hover-color' => array(
						'title' => esc_html__('Pagination Text Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-pagination a:hover, .gdlr-core-pagination a.gdlr-core-active, .gdlr-core-pagination span{ color: #gdlr#; }'
					),
					'pagination-plain-color' => array(
						'title' => esc_html__('Pagination ( Border / Plain ) Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#b4b4b4',
						'selector' => '.gdlr-core-pagination.gdlr-core-with-border a{ color: #gdlr#; border-color: #gdlr#; }' . 
							'.gdlr-core-pagination.gdlr-core-style-plain a, .gdlr-core-pagination.gdlr-core-style-plain a:before, .gdlr-core-pagination.gdlr-core-style-plain span:before{ color: #gdlr#; }'
					),
					'pagination-plain-hover-color' => array(
						'title' => esc_html__('Pagination ( Border / Plain ) Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#424242',
						'selector' => '.gdlr-core-pagination.gdlr-core-with-border a:hover, .gdlr-core-pagination.gdlr-core-with-border a.gdlr-core-active, .gdlr-core-pagination.gdlr-core-with-border span{ color: #gdlr#; border-color: #gdlr#; }' . 
							'.gdlr-core-pagination.gdlr-core-style-plain a:hover, .gdlr-core-pagination.gdlr-core-style-plain a.gdlr-core-active, .gdlr-core-pagination.gdlr-core-style-plain span{ color: #gdlr#; }'
					),

				) // footer-copyright-options
			), // footer-copyright-nav

			'port-color' => array(
				'title' => esc_html__('Portfolio Color', 'financity'),
				'options' => array(
				
					'single-portfolio-nav-color' => array(
						'title' => esc_html__('Single Portfolio Navigation Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#bcbcbc',
						'selector' => '.gdlr-core-portfolio-single-nav, .gdlr-core-portfolio-single-nav a, .gdlr-core-portfolio-single-nav a:hover{ color: #gdlr#; }'
					),
					'portfolio-frame-background-color' => array(
						'title' => esc_html__('Portfolio Frame Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f5f5f5',
						'selector' => '.gdlr-core-portfolio-grid.gdlr-core-style-with-frame .gdlr-core-portfolio-grid-frame, ' . 
							'.gdlr-core-portfolio-grid2{ background-color: #gdlr#; }'
					),
					'portfolio-title-color' => array(
						'title' => esc_html__('Portfolio Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#191919',
						'selector' => '.gdlr-core-portfolio-content-wrap .gdlr-core-portfolio-title a{ color: #gdlr#; }'
					),
					'portfolio-info-head-color' => array(
						'title' => esc_html__('Port Info Head Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#343434',
						'selector' => '.gdlr-core-port-info-item .gdlr-core-port-info-key, .gdlr-core-port-info2 .gdlr-core-port-info2-key{ color: #gdlr#; }'
					),
					'portfolio-info-color' => array(
						'title' => esc_html__('Portfolio Info Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#b1b1b1',
						'selector' => '.gdlr-core-portfolio-content-wrap .gdlr-core-portfolio-info, ' .
							'.gdlr-core-portfolio-content-wrap .gdlr-core-portfolio-info a, ' .
							'.gdlr-core-portfolio-content-wrap .gdlr-core-portfolio-info a:hover{ color: #gdlr#; }'
					),
					'portfolio-grid2-tag-background' => array(
						'title' => esc_html__('Portfolio Grid2 Tag Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#3d3ac2',
						'selector' => '.gdlr-core-portfolio-grid2 .gdlr-core-portfolio-content-wrap .gdlr-core-portfolio-info{ background-color: #gdlr#; }'
					),
					'portfolio-badge-text-color' => array(
						'title' => esc_html__('Portfolio Badge Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-portfolio-badge{ color: #gdlr#; }'
					),
					'portfolio-badge-background-color' => array(
						'title' => esc_html__('Portfolio Badge Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#e24a3b',
						'selector' => '.gdlr-core-portfolio-badge{ background-color: #gdlr#; }'
					),
					'portfolio-thumbnail-title-color' => array(
						'title' => esc_html__('Portfolio Thumbnail Title/Icon Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-portfolio-thumbnail .gdlr-core-portfolio-icon, ' .
							'.gdlr-core-portfolio-thumbnail .gdlr-core-portfolio-title a, ' .
							'.gdlr-core-portfolio-thumbnail .gdlr-core-portfolio-title a:hover{ color: #gdlr#; }'
					),
					'portfolio-thumbnail-info-color' => array(
						'title' => esc_html__('Portfolio Thumbnail Info Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#cecece',
						'selector' => '.gdlr-core-portfolio-thumbnail .gdlr-core-portfolio-info, ' .
							'.gdlr-core-portfolio-thumbnail .gdlr-core-portfolio-info a, ' .
							'.gdlr-core-portfolio-thumbnail .gdlr-core-portfolio-info a:hover{ color: #gdlr#; }'
					),
					'portfolio-text-filter-color' => array(
						'title' => esc_html__('Portfolio Filter Text Color( Text Style ) ', 'financity'),
						'type' => 'colorpicker',
						'default' => '#aaaaaa',
						'selector' => '.gdlr-core-filterer-wrap.gdlr-core-style-text a{ color: #gdlr#; }'
					),
					'portfolio-text-filter-active-color' => array(
						'title' => esc_html__('Portfolio Filter Text Active Color( Text Style ) ', 'financity'),
						'type' => 'colorpicker',
						'default' => '#747474',
						'selector' => '.gdlr-core-filterer-wrap.gdlr-core-style-text a:hover, .gdlr-core-filterer-wrap.gdlr-core-style-text a.gdlr-core-active{ color: #gdlr#; }' . 
							'.gdlr-core-filterer-wrap.gdlr-core-style-text .gdlr-core-filterer-slide-bar{ border-bottom-color: #gdlr# }'
					),
					'portfolio-button-filter-text-color' => array(
						'title' => esc_html__('Portfolio Filter Button Text Color ( Button Style ) ', 'financity'),
						'type' => 'colorpicker',
						'default' => '#838383',
						'selector' => '.gdlr-core-filterer-wrap.gdlr-core-style-button a{ color: #gdlr#; }'
					),
					'portfolio-button-filter-background-color' => array(
						'title' => esc_html__('Portfolio Filter Button Background Color ( Button Style ) ', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f1f1f1',
						'selector' => '.gdlr-core-filterer-wrap.gdlr-core-style-button a{ background-color: #gdlr#; }'
					),
					'portfolio-button-filter-text-active-color' => array(
						'title' => esc_html__('Portfolio Filter Button Text Active Color ( Button Style ) ', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-filterer-wrap.gdlr-core-style-button a:hover, .gdlr-core-filterer-wrap.gdlr-core-style-button a.gdlr-core-active{ color: #gdlr#; }'
					),
					'portfolio-button-filter-background-active-color' => array(
						'title' => esc_html__('Portfolio Filter Button Background Active Color ( Button Style ) ', 'financity'),
						'type' => 'colorpicker',
						'default' => '#2f9cea',
						'selector' => '.gdlr-core-filterer-wrap.gdlr-core-style-button a:hover, .gdlr-core-filterer-wrap.gdlr-core-style-button a.gdlr-core-active{ background-color: #gdlr#; }'
					),

				) // options
			), // port-color

			'price-table-color' => array(
				'title' => esc_html__('Price Table', 'financity'),
				'options' => array(

					'price-table-background-color' => array(
						'title' => esc_html__('Price Table Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f8f8f8',
						'selector' => '.gdlr-core-price-table-item .gdlr-core-price-table{ background-color: #gdlr#; }'
					),					
					'price-table-head-color' => array(
						'title' => esc_html__('Price Table Head', 'financity'),
						'type' => 'colorpicker',
						'default' => '#3e3e3e',
						'selector-extra' => true,
						'selector' => '.gdlr-core-price-table .gdlr-core-price-table-head{ background-color: #gdlr#; ' .
							'background: -webkit-linear-gradient(<price-table-head-top-gradient-color>, #gdlr#); ' . 
							'background: -o-linear-gradient(<price-table-head-top-gradient-color>, #gdlr#); ' . 
							'background: -moz-linear-gradient(<price-table-head-top-gradient-color>, #gdlr#); ' . 
							'background: linear-gradient(<price-table-head-top-gradient-color>, #gdlr#); }'
					),					
					'price-table-head-top-gradient-color' => array(
						'title' => esc_html__('Price Table Head Top Gradient', 'financity'),
						'type' => 'colorpicker',
						'default' => '#525252'
					),					
					'price-table-icon-color' => array(
						'title' => esc_html__('Price Table Icon', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-price-table .gdlr-core-price-table-icon{ color: #gdlr#; }'
					),					
					'price-table-title-color' => array(
						'title' => esc_html__('Price Table Title', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-price-table .gdlr-core-price-table-title{ color: #gdlr#; }'
					),					
					'price-table-caption-color' => array(
						'title' => esc_html__('Price Table Caption', 'financity'),
						'type' => 'colorpicker',
						'default' => '#acacac',
						'selector' => '.gdlr-core-price-table .gdlr-core-price-table-caption{ color: #gdlr#; }'
					),					
					'price-table-price-background-color' => array(
						'title' => esc_html__('Price Table Price Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ebebeb',
						'selector' => '.gdlr-core-price-table .gdlr-core-price-table-price{ background-color: #gdlr#; }'
					),					
					'price-table-price-color' => array(
						'title' => esc_html__('Price Table Price Text', 'financity'),
						'type' => 'colorpicker',
						'default' => '#323232',
						'selector' => '.gdlr-core-price-table .gdlr-core-price-table-price-number, .gdlr-core-price-table .gdlr-core-price-prefix{ color: #gdlr#; }'
					),					
					'price-table-price-suffix-color' => array(
						'title' => esc_html__('Price Table Price Suffix', 'financity'),
						'type' => 'colorpicker',
						'default' => '#acacac',
						'selector' => '.gdlr-core-price-table .gdlr-core-price-suffix{ color: #gdlr#; }'
					),
					'price-table-price-button-color' => array(
						'title' => esc_html__('Price Table Button Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-price-table .gdlr-core-price-table-button, .gdlr-core-price-table .gdlr-core-price-table-button:hover{ color: #gdlr#; }'
					),
					'price-table-button-background-color' => array(
						'title' => esc_html__('Price Table Button Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#575757',
						'selector-extra' => true,
						'selector' => '.gdlr-core-price-table .gdlr-core-price-table-button, .gdlr-core-price-table .gdlr-core-price-table-button:hover{ background-color: #gdlr#; ' .
							'background: -webkit-linear-gradient(<price-table-button-background-top-gradient-color>, #gdlr#); ' . 
							'background: -o-linear-gradient(<price-table-button-background-top-gradient-color>, #gdlr#); ' . 
							'background: -moz-linear-gradient(<price-table-button-background-top-gradient-color>, #gdlr#); ' . 
							'background: linear-gradient(<price-table-button-background-top-gradient-color>, #gdlr#); }'
					),
					'price-table-button-background-top-gradient-color' => array(
						'title' => esc_html__('Price Table Button Background Top Gradient', 'financity'),
						'type' => 'colorpicker',
						'default' => '#414141',
					),
					'price-table-list-border-color' => array(
						'title' => esc_html__('Price Table List Border', 'financity'),
						'type' => 'colorpicker',
						'default' => '#e5e5e5',
						'selector' => '.gdlr-core-price-table .gdlr-core-price-table-content *{ border-color: #gdlr#; }'
					),
					'price-table-active-head-color' => array(
						'title' => esc_html__('Price Table Head', 'financity'),
						'type' => 'colorpicker',
						'default' => '#329eec',
						'selector-extra' => true,
						'selector' => '.gdlr-core-price-table.gdlr-core-active .gdlr-core-price-table-head{ background-color: #gdlr#; ' .
							'background: -webkit-linear-gradient(<price-table-active-head-top-gradient-color>, #gdlr#); ' . 
							'background: -o-linear-gradient(<price-table-active-head-top-gradient-color>, #gdlr#); ' . 
							'background: -moz-linear-gradient(<price-table-active-head-top-gradient-color>, #gdlr#); ' . 
							'background: linear-gradient(<price-table-active-head-top-gradient-color>, #gdlr#); }'
					),					
					'price-table-active-head-top-gradient-color' => array(
						'title' => esc_html__('Price Table Head Top Gradient', 'financity'),
						'type' => 'colorpicker',
						'default' => '#59b9fe'
					),
					'price-table-active-icon-color' => array(
						'title' => esc_html__('Price Table Icon', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-price-table.gdlr-core-active .gdlr-core-price-table-icon{ color: #gdlr#; }'
					),					
					'price-table-active-title-color' => array(
						'title' => esc_html__('Price Table Title', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-price-table.gdlr-core-active .gdlr-core-price-table-title{ color: #gdlr#; }'
					),					
					'price-table-active-caption-color' => array(
						'title' => esc_html__('Price Table Caption', 'financity'),
						'type' => 'colorpicker',
						'default' => '#b1d8f5',
						'selector' => '.gdlr-core-price-table.gdlr-core-active .gdlr-core-price-table-caption{ color: #gdlr#; }'
					),
					'price-table-active-price-background-color' => array(
						'title' => esc_html__('Price Table Price Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-price-table.gdlr-core-active .gdlr-core-price-table-price{ background-color: #gdlr#; }'
					),					
					'price-table-active-price-color' => array(
						'title' => esc_html__('Price Table Price Text', 'financity'),
						'type' => 'colorpicker',
						'default' => '#323232',
						'selector' => '.gdlr-core-price-table.gdlr-core-active .gdlr-core-price-table-price-number, .gdlr-core-price-table .gdlr-core-price-prefix{ color: #gdlr#; }'
					),					
					'price-table-active-price-suffix-color' => array(
						'title' => esc_html__('Price Table Price Suffix', 'financity'),
						'type' => 'colorpicker',
						'default' => '#acacac',
						'selector' => '.gdlr-core-price-table.gdlr-core-active .gdlr-core-price-suffix{ color: #gdlr#; }'
					),
					'price-table-active-price-button-color' => array(
						'title' => esc_html__('Price Table Button Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-price-table.gdlr-core-active .gdlr-core-price-table-button, .gdlr-core-price-table .gdlr-core-price-table-button:hover{ color: #gdlr#; }'
					),
					'price-table-active-button-background-color' => array(
						'title' => esc_html__('Price Table Button Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#319dea',
						'selector-extra' => true,
						'selector' => '.gdlr-core-price-table.gdlr-core-active .gdlr-core-price-table-button, .gdlr-core-price-table .gdlr-core-price-table-button:hover{ background-color: #gdlr#; ' .
							'background: -webkit-linear-gradient(<price-table-active-button-background-top-gradient-color>, #gdlr#); ' . 
							'background: -o-linear-gradient(<price-table-active-button-background-top-gradient-color>, #gdlr#); ' . 
							'background: -moz-linear-gradient(<price-table-active-button-background-top-gradient-color>, #gdlr#); ' . 
							'background: linear-gradient(<price-table-active-button-background-top-gradient-color>, #gdlr#); }'
					),
					'price-table-active-button-background-top-gradient-color' => array(
						'title' => esc_html__('Price Table Button Background Top Gradient', 'financity'),
						'type' => 'colorpicker',
						'default' => '#52aae9',
					),					

				)
			), // price table

			'element-1-color' => array(
				'title' => esc_html__('Element A,B', 'financity'),
				'options' => array(
				
					'accordion-normal-icon-color' => array(
						'title' => esc_html__('Accordion / Toggle Box Icon Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#191919',
						'selector' => '.gdlr-core-accordion-style-icon .gdlr-core-accordion-item-icon, ' .
							'.gdlr-core-accordion-style-box-icon .gdlr-core-accordion-item-icon, ' .
							'.gdlr-core-toggle-box-style-icon .gdlr-core-toggle-box-item-icon, ' .
							'.gdlr-core-toggle-box-style-box-icon .gdlr-core-toggle-box-item-icon{ color: #gdlr#; }'
					),
					'accordion-normal-title-head-color' => array(
						'title' => esc_html__('Accordion / Toggle Box Title Head Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#191919',
						'selector' => '.gdlr-core-accordion-style-icon .gdlr-core-accordion-item-title .gdlr-core-head, ' . 
							'.gdlr-core-accordion-style-box-icon .gdlr-core-accordion-item-title .gdlr-core-head, ' . 
							'.gdlr-core-toggle-box-style-icon .gdlr-core-toggle-box-item-title .gdlr-core-head, ' . 
							'.gdlr-core-toggle-box-style-box-icon .gdlr-core-toggle-box-item-title .gdlr-core-head{ color: #gdlr#; }'
					),
					'accordion-normal-title-color' => array(
						'title' => esc_html__('Accordion / Toggle Box Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#191919',
						'selector' => '.gdlr-core-accordion-style-icon .gdlr-core-accordion-item-title, ' . 
							'.gdlr-core-accordion-style-box-icon .gdlr-core-accordion-item-title, ' . 
							'.gdlr-core-toggle-box-style-icon .gdlr-core-toggle-box-item-title, ' .
							'.gdlr-core-toggle-box-style-box-icon .gdlr-core-toggle-box-item-title{ color: #gdlr#; }'
					),
					'accordion-normal-icon-background-color' => array(
						'title' => esc_html__('Accordion / Toggle Box Icon Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f3f3f3',
						'selector' => '.gdlr-core-accordion-style-box-icon .gdlr-core-accordion-item-icon, ' . 
							'.gdlr-core-toggle-box-style-box-icon .gdlr-core-toggle-box-item-icon{ background-color: #gdlr#; }' . 
							'.gdlr-core-accordion-style-box-icon .gdlr-core-accordion-item-icon, ' .
							'.gdlr-core-toggle-box-style-box-icon .gdlr-core-toggle-box-item-icon{ border-color: #gdlr#; }' .
							'.gdlr-core-accordion-style-box-icon .gdlr-core-accordion-item-tab.gdlr-core-active .gdlr-core-accordion-item-icon,' .
							'.gdlr-core-toggle-box-style-box-icon .gdlr-core-toggle-box-item-tab.gdlr-core-active .gdlr-core-toggle-box-item-icon{ color: #gdlr#; }'
					),

					'accordion-icon-color' => array(
						'title' => esc_html__('Accordion / Toggle Box Icon Color ( Background Style )', 'financity'),
						'type' => 'colorpicker',
						'default' => '#191919',
						'selector' => '.gdlr-core-accordion-style-background-title-icon .gdlr-core-accordion-item-title:before, ' .
							'.gdlr-core-toggle-box-style-background-title-icon .gdlr-core-accordion-item-title:before{ color: #gdlr#; }'
					),
					'accordion-title-head-color' => array(
						'title' => esc_html__('Accordion / Toggle Box Title Head Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#191919',
						'selector' => '.gdlr-core-accordion-style-background-title .gdlr-core-accordion-item-title .gdlr-core-head, ' . 
							'.gdlr-core-accordion-style-background-title-icon .gdlr-core-accordion-item-title .gdlr-core-head, ' . 
							'.gdlr-core-toggle-box-style-background-title .gdlr-core-toggle-box-item-title .gdlr-core-head, ' . 
							'.gdlr-core-toggle-box-style-background-title-icon .gdlr-core-toggle-box-item-title .gdlr-core-head{ color: #gdlr#; }'
					),
					'accordion-title-color' => array(
						'title' => esc_html__('Accordion / Toggle Box Title Color ( Background Style )', 'financity'),
						'type' => 'colorpicker',
						'default' => '#191919',
						'selector' => '.gdlr-core-accordion-style-background-title .gdlr-core-accordion-item-title, ' . 
							'.gdlr-core-accordion-style-background-title-icon .gdlr-core-accordion-item-title, ' . 
							'.gdlr-core-toggle-box-style-background-title .gdlr-core-toggle-box-item-title, ' .
							'.gdlr-core-toggle-box-style-background-title-icon .gdlr-core-toggle-box-item-title{ color: #gdlr#; }'
					),
					'accordion-title-background-color' => array(
						'title' => esc_html__('Accordion / Toggle Box Title Background Color ( Background Style )', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f3f3f3',
						'selector' => '.gdlr-core-accordion-style-background-title .gdlr-core-accordion-item-title, ' . 
							'.gdlr-core-accordion-style-background-title-icon .gdlr-core-accordion-item-title, ' . 
							'.gdlr-core-toggle-box-style-background-title .gdlr-core-toggle-box-item-title, ' .
							'.gdlr-core-toggle-box-style-background-title-icon .gdlr-core-toggle-box-item-title{ background-color: #gdlr#; }'
					),				
					'accordion-icon-active-color' => array(
						'title' => esc_html__('Accordion / Toggle Box Icon Active Color ( Background Style )', 'financity'),
						'type' => 'colorpicker',
						'default' => '#191919',
						'selector' => '.gdlr-core-accordion-style-background-title-icon .gdlr-core-active .gdlr-core-accordion-item-title:before, ' . 
							'.gdlr-core-toggle-box-style-background-title-icon .gdlr-core-active .gdlr-core-accordion-item-title:before{ color: #gdlr#; }'
					),
					'accordion-title-active-color' => array(
						'title' => esc_html__('Accordion / Toggle Box Title Text Active Color ( Background Style )', 'financity'),
						'type' => 'colorpicker',
						'default' => '#191919',
						'selector' => '.gdlr-core-accordion-style-background-title .gdlr-core-active .gdlr-core-accordion-item-title, ' . 
							'.gdlr-core-accordion-style-background-title-icon .gdlr-core-active .gdlr-core-accordion-item-title, ' . 
							'.gdlr-core-toggle-box-style-background-title .gdlr-core-active .gdlr-core-toggle-box-item-title, ' .
							'.gdlr-core-toggle-box-style-background-title-icon .gdlr-core-active .gdlr-core-toggle-box-item-title{ color: #gdlr#; }'
					),
					'accordion-title-background-active-color' => array(
						'title' => esc_html__('Accordion / Toggle Box Title Background Active Color ( Background Style )', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f3f3f3',
						'selector' => '.gdlr-core-accordion-style-background-title .gdlr-core-active .gdlr-core-accordion-item-title, ' . 
							'.gdlr-core-accordion-style-background-title-icon .gdlr-core-active .gdlr-core-accordion-item-title, ' . 
							'.gdlr-core-toggle-box-style-background-title .gdlr-core-active .gdlr-core-toggle-box-item-title, ' .
							'.gdlr-core-toggle-box-style-background-title-icon .gdlr-core-active .gdlr-core-toggle-box-item-title{ background-color: #gdlr#; }'
					),
					'audio-background-color' => array(
						'title' => esc_html__('Audio Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#e7e7e7',
						'selector' => '.gdlr-core-audio, .gdlr-core-audio .mejs-container .mejs-controls{ background-color: #gdlr#; }'
					),
					'audio-text-color' => array(
						'title' => esc_html__('Audio Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#202020',
						'selector' => '.gdlr-core-audio .mejs-container .mejs-controls .mejs-volume-button:before, ' . 
							'.gdlr-core-audio .mejs-container .mejs-controls .mejs-playpause-button:before, ' . 
							'.gdlr-core-audio .mejs-container .mejs-controls .mejs-time{ color: #gdlr#; }'
					),
					'audio-content-background-color' => array(
						'title' => esc_html__('Audio Content Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#afafaf',
						'selector' => '.gdlr-core-audio .mejs-controls .mejs-time-rail .mejs-time-total, ' .
							'.gdlr-core-audio .mejs-controls .mejs-time-rail .mejs-time-loaded{ background-color: #gdlr#; }'
					),
					'audio-content-progress-color' => array(
						'title' => esc_html__('Audio Content Progress Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#2d9bea',
						'selector' => '.gdlr-core-audio .mejs-controls .mejs-time-rail .mejs-time-current{ background-color: #gdlr#; }'
					),
					'audio-volume-background-color' => array(
						'title' => esc_html__('Audio Volume Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#afafaf',
						'selector' => '.gdlr-core-audio .mejs-controls .mejs-horizontal-volume-slider .mejs-horizontal-volume-total{ background-color: #gdlr#; }'
					),
					'audio-volume-progress-color' => array(
						'title' => esc_html__('Audio Volume Progress Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#646464',
						'selector' => '.gdlr-core-audio .mejs-controls .mejs-horizontal-volume-slider .mejs-horizontal-volume-current{ background-color: #gdlr#; }'
					),
					'alert-box-item-background-color' => array(
						'title' => esc_html__('Alert Box Item Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ef5e68',
						'selector' => '.gdlr-core-alert-box-item .gdlr-core-alert-box-item-inner{ background-color: #gdlr#; }'
					),				
					'alert-box-item-border-color' => array(
						'title' => esc_html__('Alert Box Item Border Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#cd515a',
						'selector' => '.gdlr-core-alert-box-item .gdlr-core-alert-box-item-inner{ border-color: #gdlr#; }'
					),				
					'alert-box-item-content-color' => array(
						'title' => esc_html__('Alert Box Item Content Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-alert-box-item .gdlr-core-alert-box-item-inner{ color: #gdlr#; }'
					),				
					'alert-box-item-title-color' => array(
						'title' => esc_html__('Alert Box Item Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-alert-box-item .gdlr-core-alert-box-item-title{ color: #gdlr#; }'
					),	
					'blockquote-text-color' => array(
						'title' => esc_html__('Blockquote Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#777777',
						'selector' => 'blockquote, q{ color: #gdlr#; }'
					),	
					'blockquote-background-color' => array(
						'title' => esc_html__('Blockquote Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f5f5f5',
						'selector' => 'blockquote, q{ background-color: #gdlr#; }'
					),	
					'blockquote-border-color' => array(
						'title' => esc_html__('Blockquote Border Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#e2e2e2',
						'selector' => '.financity-body blockquote, .financity-body q{ border-color: #gdlr#; }'
					),	
					'blockquote-item-icon-color' => array(
						'title' => esc_html__('Blockquote Item Icon Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#4e4e4e',
						'selector' => '.gdlr-core-blockquote-item-quote{ color: #gdlr#; }'
					),		
					'blockquote-item-content-color' => array(
						'title' => esc_html__('Blockquote Item Content Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#4e4e4e',
						'selector' => '.gdlr-core-blockquote-item-content{ color: #gdlr#; }'
					),	
					'blockquote-item-author-color' => array(
						'title' => esc_html__('Blockquote Item Author Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#4e4e4e',
						'selector' => '.gdlr-core-blockquote-item .gdlr-core-blockquote-item-author{ color: #gdlr#; }'
					),
					'blockquote-item-author-position-color' => array(
						'title' => esc_html__('Blockquote Item Author Position Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#4e4e4e',
						'selector' => '.gdlr-core-blockquote-item .gdlr-core-blockquote-item-author-position{ color: #gdlr#; }'
					),

				) // options
			), // element-1-color

			'element-2-color' => array(
				'title' => esc_html__('Element B,C,D', 'financity'),
				'options' => array(

					'button-text-color' => array(
						'title' => esc_html__('Button Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-body .gdlr-core-load-more, .gdlr-core-body .gdlr-core-button, .financity-body .financity-button, ' .
							'.financity-body input[type="button"], .financity-body input[type="submit"]{ color: #gdlr#; }'
					),
					'button-text-hover-color' => array(
						'title' => esc_html__('Button Text Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-body .gdlr-core-load-more:hover, .gdlr-core-body .gdlr-core-button:hover{ color: #gdlr#; }'
					),
					'button-background-color' => array(
						'title' => esc_html__('Button Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#2F2F2F',
						'selector' => '.gdlr-core-body .gdlr-core-load-more, .gdlr-core-body .gdlr-core-button, .financity-body .financity-button, ' .
							'.financity-body input[type="button"], .financity-body input[type="submit"]{ background-color: #gdlr#; }'
					),
					'button-background-hover-color' => array(
						'title' => esc_html__('Button Background Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#2F2F2F',
						'selector' => '.gdlr-core-body .gdlr-core-load-more:hover, .gdlr-core-body .gdlr-core-button:hover{ background-color: #gdlr#; }'
					),
					'button-border-color' => array(
						'title' => esc_html__('Button Border Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#383838',
						'selector' => '.gdlr-core-body .gdlr-core-button-with-border{ border-color: #gdlr#; } ' .
							'.gdlr-core-body .gdlr-core-button-with-border.gdlr-core-button-transparent{ color: #gdlr#; }'
					),
					'button-border-hover-color' => array(
						'title' => esc_html__('Button Border Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#000000',
						'selector' => '.gdlr-core-body .gdlr-core-button-with-border:hover{ border-color: #gdlr#; }' . 
							'.gdlr-core-body .gdlr-core-button-with-border.gdlr-core-button-transparent:hover{ color: #gdlr#; }'
					),
					'button-gradient-background-color' => array(
						'title' => esc_html__('Button ( Gradient Style ) Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#309cea',
						'selector-extra' => true,
						'selector' => '.gdlr-core-body .gdlr-core-button.gdlr-core-button-gradient{ background-color: #gdlr#; ' .
    						'background: -webkit-linear-gradient(<button-top-gradient-background-color>, #gdlr#); ' .
    						'background: -o-linear-gradient(<button-top-gradient-background-color>, #gdlr#); ' .
    						'background: -moz-linear-gradient(<button-top-gradient-background-color>, #gdlr#); ' .
							'background: linear-gradient(<button-top-gradient-background-color>, #gdlr#); }' .
							'.gdlr-core-body .gdlr-core-button.gdlr-core-button-gradient{ background-color: #gdlr#; ' .
    						'background: -webkit-linear-gradient(to right, <button-top-gradient-background-color>, #gdlr#); ' .
    						'background: -o-linear-gradient(to right, <button-top-gradient-background-color>, #gdlr#); ' .
    						'background: -moz-linear-gradient(to right, <button-top-gradient-background-color>, #gdlr#); ' .
							'background: linear-gradient(to right, <button-top-gradient-background-color>, #gdlr#); }'
					),
					'button-top-gradient-background-color' => array(
						'title' => esc_html__('Button ( Gradient Style ) Top Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#52aae9'
					),
					'call-to-action-title-color' => array(
						'title' => esc_html__('Call To Action Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#2c2c2c',
						'selector' => '.gdlr-core-call-to-action-item-title{ color: #gdlr#; }'
					),			
					'call-to-action-caption-color' => array(
						'title' => esc_html__('Call To Action Caption Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#535353',
						'selector' => '.gdlr-core-call-to-action-item-caption{ color: #gdlr#; }'
					),
					'counter-item-top-text-color' => array(
						'title' => esc_html__('Counter Item Top Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#828282',
						'selector' => '.gdlr-core-counter-item-top-text{ color: #gdlr#; }'
					),		
					'counter-item-top-icon-color' => array(
						'title' => esc_html__('Counter Item Top Icon Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#393939',
						'selector' => '.gdlr-core-counter-item-top-icon{ color: #gdlr#; }'
					),		
					'counter-item-number-color' => array(
						'title' => esc_html__('Counter Item Number Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#393939',
						'selector' => '.gdlr-core-counter-item-number{ color: #gdlr#; }'
					),		
					'counter-item-divider-color' => array(
						'title' => esc_html__('Counter Item Divider Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#393939',
						'selector' => '.gdlr-core-counter-item-divider{ border-color: #gdlr#; }'
					),		
					'counter-item-bottom-text-color' => array(
						'title' => esc_html__('Counter Item Bottom Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#393939',
						'selector' => '.gdlr-core-counter-item-bottom-text{ color: #gdlr#; }'
					),
					'column-service-icon-color' => array(
						'title' => esc_html__('Column Service Icon Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#9d9d9d',
						'selector' => '.gdlr-core-column-service-item .gdlr-core-column-service-icon{ color: #gdlr#; }'
					),
					'column-service-icon-background' => array(
						'title' => esc_html__('Column Service Icon Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f3f3f3',
						'selector' => '.gdlr-core-column-service-item .gdlr-core-icon-style-round i{ background-color: #gdlr#; }'
					),
					'column-service-title-color' => array(
						'title' => esc_html__('Column Service Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#383838',
						'selector' => '.gdlr-core-column-service-item .gdlr-core-column-service-title{ color: #gdlr#; }'
					),
					'column-service-caption-color' => array(
						'title' => esc_html__('Column Service Caption Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#747474',
						'selector' => '.gdlr-core-column-service-item .gdlr-core-column-service-caption{ color: #gdlr#; }'
					),

				) // options
			), // element-2-color

			'element-3-color' => array(
				'title' => esc_html__('Element F,G,I,O,P', 'financity'),
				'options' => array(

					'flipbox-background-color' => array(
						'title' => esc_html__('Flipbox / Feature Box Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#2d9bea',
						'selector' => '.gdlr-core-flipbox-item .gdlr-core-flipbox-front, ' .
							'.gdlr-core-flipbox-item .gdlr-core-flipbox-back, ' .
							'.gdlr-core-feature-box-item .gdlr-core-feature-box{ background-color: #gdlr#; }'
					),
					'flipbox-border-color' => array(
						'title' => esc_html__('Flipbox / Feature Box Border', 'financity'),
						'type' => 'colorpicker',
						'default' => '#2a80be',
						'selector' => '.gdlr-core-flipbox-item .gdlr-core-flipbox-front, ' .
							'.gdlr-core-flipbox-item .gdlr-core-flipbox-back, ' .
							'.gdlr-core-flipbox-item .gdlr-core-flipbox-frame, ' .
							'.gdlr-core-feature-box-item .gdlr-core-feature-box, ' .
							'.gdlr-core-feature-box-item .gdlr-core-feature-box-frame{ border-color: #gdlr#; }'
					),
					'flipbox-icon-color' => array(
						'title' => esc_html__('Flipbox / Feature Box Icon', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-flipbox-item .gdlr-core-flipbox-item-icon, .gdlr-core-feature-box-item .gdlr-core-feature-box-item-icon{ color: #gdlr#; }'
					),
					'flipbox-title-color' => array(
						'title' => esc_html__('Flipbox / Feature Box Title', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-flipbox-item .gdlr-core-flipbox-item-title, .gdlr-core-feature-box-item .gdlr-core-feature-box-item-title{ color: #gdlr#; }'
					),
					'flipbox-caption-color' => array(
						'title' => esc_html__('Flipbox / Feature Box Caption', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-flipbox-item .gdlr-core-flipbox-item-caption, .gdlr-core-feature-box-item .gdlr-core-feature-box-item-caption{ color: #gdlr#; }'
					),
					'flipbox-content-color' => array(
						'title' => esc_html__('Flipbox / Feature Box Content', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-flipbox-item .gdlr-core-flipbox-item-content, .gdlr-core-feature-box-item .gdlr-core-feature-box-item-content{ color: #gdlr#; }'
					),
					'gallery-overlay-title' => array(
						'title' => esc_html__('Gallery Overlay Title', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-image-overlay.gdlr-core-gallery-image-overlay .gdlr-core-image-overlay-title{ color: #gdlr#; }'
					),
					'gallery-overlay-caption' => array(
						'title' => esc_html__('Gallery Overlay Title', 'financity'),
						'type' => 'colorpicker',
						'default' => '#cecece',
						'selector' => '.gdlr-core-image-overlay.gdlr-core-gallery-image-overlay .gdlr-core-image-overlay-caption{ color: #gdlr#; }'
					),
					'image-overlay-background-color' => array(
						'title' => esc_html__('Image Overlay Background', 'financity'),
						'type' => 'colorpicker',
						'data-type' => 'rgba',
						'default' => '#000000',
						'selector' => '.gdlr-core-image-overlay{ background-color: #gdlr#; background-color: rgba(#gdlra#, 0.6); }'
					),
					'image-overlay-icon-color' => array(
						'title' => esc_html__('Image Overlay Icon', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-image-overlay-content, .gdlr-core-image-overlay-content a, .gdlr-core-image-overlay-icon{ color: #gdlr#; }' .
							'.gdlr-core-page-builder-body [data-skin] .gdlr-core-image-overlay-icon, ' .
							'.gdlr-core-page-builder-body .gdlr-core-pbf-column[data-skin] .gdlr-core-image-overlay-icon{ color: #gdlr#; }'
					),
					'image-overlay-icon-background' => array(
						'title' => esc_html__('Image Overlay Icon Background ( Round Icon Style )', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-image-overlay.gdlr-core-round-icon .gdlr-core-image-overlay-icon{ background-color: #gdlr#; }'
					),
					'image-item-border-color' => array(
						'title' => esc_html__('Image Item Border', 'financity'),
						'type' => 'colorpicker',
						'default' => '#000000',
						'selector' => '.gdlr-core-body .gdlr-core-image-item-wrap{ border-color: #gdlr#; }'
					),
					'item-title-color' => array(
						'title' => esc_html__('Item\'s Title Color ( Blog / Portfolio / ... )', 'financity'),
						'type' => 'colorpicker',
						'default' => '#343434',
						'selector' => '.gdlr-core-block-item-title-wrap .gdlr-core-block-item-title{ color: #gdlr#; }'
					),
					'item-title-caption-color' => array(
						'title' => esc_html__('Item\'s Title Caption Color ( Blog / Portfolio / ... )', 'financity'),
						'type' => 'colorpicker',
						'default' => '#747474',
						'selector' => '.gdlr-core-block-item-title-wrap .gdlr-core-block-item-caption{ color: #gdlr#; }'
					),
					'item-title-link-color' => array(
						'title' => esc_html__('Item\'s Title Link Color ( Blog / Portfolio / ... )', 'financity'),
						'type' => 'colorpicker',
						'default' => '#747474',
						'selector' => '.gdlr-core-block-item-title-wrap a, .gdlr-core-block-item-title-wrap a:hover{ color: #gdlr#; }'
					),
					'icon-list-item-icon-color' => array(
						'title' => esc_html__('Icon List Item Icon Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#222222',
						'selector' => '.gdlr-core-icon-list-item i{ color: #gdlr#; }'
					),
					'icon-list-item-icon-background-color' => array(
						'title' => esc_html__('Icon List Item Icon Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f3f3f3',
						'selector' => '.gdlr-core-icon-list-with-background-round .gdlr-core-icon-list-icon-wrap, ' .
							'.gdlr-core-icon-list-with-background-circle .gdlr-core-icon-list-icon-wrap{ color: #gdlr#; }'
					),
					'opening-hour-day-color' => array(
						'title' => esc_html__('Opening Hour Day Text', 'financity'),
						'type' => 'colorpicker',
						'default' => '#a5a5a5',
						'selector' => '.gdlr-core-opening-hour-item .gdlr-core-opening-hour-day{ color: #gdlr#; }'
					),
					'opening-hour-open-color' => array(
						'title' => esc_html__('Opening Hour Open Text', 'financity'),
						'type' => 'colorpicker',
						'default' => '#262626',
						'selector' => '.gdlr-core-opening-hour-item .gdlr-core-opening-hour-open{ color: #gdlr#; }'
					),
					'opening-hour-close-color' => array(
						'title' => esc_html__('Opening Hour Close Text', 'financity'),
						'type' => 'colorpicker',
						'default' => '#c8c8c8',
						'selector' => '.gdlr-core-opening-hour-item .gdlr-core-opening-hour-close{ color: #gdlr#; }'
					),
					'opening-hour-icon-color' => array(
						'title' => esc_html__('Opening Hour Icon Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#8a8989',
						'selector' => '.gdlr-core-opening-hour-item .gdlr-core-opening-hour-time i{ color: #gdlr#; }'
					),
					'opening-hour-divider-color' => array(
						'title' => esc_html__('Opening Hour Divider Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#a6a6a6',
						'selector' => '.gdlr-core-opening-hour-item .gdlr-core-opening-hour-list-item{ border-color: #gdlr#; }'
					),
					'personnel-grid-title-color' => array(
						'title' => esc_html__('Personnel Grid Title', 'financity'),
						'type' => 'colorpicker',
						'default' => '#383838',
						'selector' => '.gdlr-core-personnel-style-grid .gdlr-core-personnel-list-title, .gdlr-core-personnel-style-grid .gdlr-core-personnel-list-title a{ color: #gdlr#; }'
					),
					'personnel-grid-position-color' => array(
						'title' => esc_html__('Personnel Grid Position', 'financity'),
						'type' => 'colorpicker',
						'default' => '#888888',
						'selector' => '.gdlr-core-personnel-style-grid .gdlr-core-personnel-list-position{ color: #gdlr#; }'
					),
					'personnel-grid-divider-color' => array(
						'title' => esc_html__('Personnel Grid Divider', 'financity'),
						'type' => 'colorpicker',
						'default' => '#cecece',
						'selector' => '.gdlr-core-personnel-style-grid .gdlr-core-personnel-list-divider{ color: #gdlr#; }'
					),
					'personnel-grid-frame-color' => array(
						'title' => esc_html__('Personnel Grid Frame Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f9f9f9',
						'selector' => '.gdlr-core-personnel-style-grid.gdlr-core-with-background .gdlr-core-personnel-list-content-wrap{ background-color: #gdlr#; }'
					),
					'personnel-modern-title-color' => array(
						'title' => esc_html__('Personnel Modern Title', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-personnel-style-modern .gdlr-core-personnel-list-title, .gdlr-core-personnel-style-modern .gdlr-core-personnel-list-title a{ color: #gdlr#; }'
					),
					'personnel-modern-position-color' => array(
						'title' => esc_html__('Personnel Modern Position', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-personnel-style-modern .gdlr-core-personnel-list-position{ color: #gdlr#; }'
					),

				)
			),

			'element-4-color' => array(
				'title' => esc_html__('Element P,R,S,T', 'financity'),
				'options' => array(
	
					'promo-box-item-title-color' => array(
						'title' => esc_html__('Promo Box Item Title', 'financity'),
						'type' => 'colorpicker',
						'default' => '#383838',
						'selector' => '.gdlr-core-promo-box-item .gdlr-core-promo-box-item-title{ color: #gdlr#; }'
					),
					'promo-box-content-border-color' => array(
						'title' => esc_html__('Promo Box Content Border', 'financity'),
						'type' => 'colorpicker',
						'default' => '#e8e7e7',
						'selector' => '.gdlr-core-promo-box-item .gdlr-core-promo-box-content-wrap{ border-color: #gdlr#; }'
					),
					'post-slider-title-color' => array(
						'title' => esc_html__('Post Slider Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-post-slider-item .gdlr-core-post-slider-title a{ color: #gdlr#; }'
					),
					'post-slider-info-color' => array(
						'title' => esc_html__('Post Slider Info Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#c5c5c5',
						'selector' => '.gdlr-core-post-slider-item .gdlr-core-blog-info, .gdlr-core-post-slider-item .gdlr-core-blog-info a{ color: #gdlr#; }'
					),
					'roadmap-title-color' => array(
						'title' => esc_html__('Roadmap Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#a6aafb',
						'selector' => '.gdlr-core-roadmap-item .gdlr-core-roadmap-item-head-title{ color: #gdlr#; }'
					),
					'roadmap-title-active-color' => array(
						'title' => esc_html__('Roadmap Title Active Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-roadmap-item-head.gdlr-core-active .gdlr-core-roadmap-item-head-title{ color: #gdlr#; }'
					),
					'roadmap-caption-color' => array(
						'title' => esc_html__('Roadmap Caption Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#c1caf6',
						'selector' => '.gdlr-core-roadmap-item .gdlr-core-roadmap-item-head-caption{ color: #gdlr#; }'
					),
					'roadmap-number-text' => array(
						'title' => esc_html__('Roadmap Number Text', 'financity'),
						'type' => 'colorpicker',
						'default' => '#c5c5c5',
						'selector' => '.gdlr-core-roadmap-item .gdlr-core-roadmap-item-head-count{ color: #gdlr#; }'
					),
					'roadmap-number-background' => array(
						'title' => esc_html__('Roadmap Number Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#223077',
						'selector' => '.gdlr-core-roadmap-item .gdlr-core-roadmap-item-head-count{ background-color: #gdlr#; }'
					),
					'roadmap-number-active-text' => array(
						'title' => esc_html__('Roadmap Number Active Text', 'financity'),
						'type' => 'colorpicker',
						'default' => '#223077',
						'selector' => '.gdlr-core-roadmap-item-head.gdlr-core-active .gdlr-core-roadmap-item-head-count{ color: #gdlr#; }'
					),
					'roadmap-number-active-background' => array(
						'title' => esc_html__('Roadmap Number Active Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-roadmap-item-head.gdlr-core-active .gdlr-core-roadmap-item-head-count{ color: #gdlr#; }'
					),
					'roadmap-number-divider' => array(
						'title' => esc_html__('Roadmap Number Divider', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-roadmap-item .gdlr-core-roadmap-item-head-divider{ border-color: #gdlr#; }'
					),
					'roadmap-content-title' => array(
						'title' => esc_html__('Roadmap Content Title', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-roadmap-item .gdlr-core-roadmap-item-content-title{ color: #gdlr#; }'
					),
					'roadmap-content-caption' => array(
						'title' => esc_html__('Roadmap Content Caption', 'financity'),
						'type' => 'colorpicker',
						'default' => '#c1caf6',
						'selector' => '.gdlr-core-roadmap-item .gdlr-core-roadmap-item-content-caption{ color: #gdlr#; }'
					),
					'roadmap-content-color' => array(
						'title' => esc_html__('Roadmap Content Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#babdff',
						'selector' => '.gdlr-core-roadmap-item .gdlr-core-roadmap-item-content{ color: #gdlr#; }'
					),
					'skill-bar-title-color' => array(
						'title' => esc_html__('Skill Bar Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#505050',
						'selector' => '.gdlr-core-skill-bar-item .gdlr-core-skill-bar-title, .gdlr-core-skill-bar-item .gdlr-core-skill-bar-right{ color: #gdlr#; }'
					),
					'skill-bar-icon-color' => array(
						'title' => esc_html__('Skill Bar Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#616161',
						'selector' => '.gdlr-core-skill-bar-item .gdlr-core-skill-bar-icon{ color: #gdlr#; }'
					),
					'skill-bar-background-color' => array(
						'title' => esc_html__('Skill Bar Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f3f3f3',
						'selector' => '.gdlr-core-skill-bar-item .gdlr-core-skill-bar-progress{ background-color: #gdlr#; }'
					),
					'skill-bar-progress-color' => array(
						'title' => esc_html__('Skill Bar Progress Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#2d9bea',
						'selector' => '.gdlr-core-skill-bar-item .gdlr-core-skill-bar-filled{ background-color: #gdlr#; }'
					),
					'slider-outer-navigation-color' => array(
						'title' => esc_html__('Slider Outer Navigation Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#a7a7a7',
						'selector' => '.gdlr-core-flexslider-nav .flex-direction-nav li a, ' .
							'.gdlr-core-flexslider.gdlr-core-nav-style-middle-large .flex-direction-nav li a{ color: #gdlr#; border-color: #gdlr#; }'
					),
					'slider-outer-navigation-background-color' => array(
						'title' => esc_html__('Slider Outer Navigation Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f1f1f1',
						'selector' => '.gdlr-core-flexslider-nav.gdlr-core-round-style li a, .gdlr-core-flexslider-nav.gdlr-core-rectangle-style li a{ background-color: #gdlr#; }'
					),
					'slider-control-navigation-border-color' => array(
						'title' => esc_html__('Slider Control Navigation Border Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#d9d9d9',
						'selector' => '.gdlr-core-flexslider .flex-control-nav li a{ border-color: #gdlr#; }' .
							'.gdlr-core-flexslider .flex-control-nav li a{ background-color: transparent; }'
					),
					'slider-control-navigation-color' => array(
						'title' => esc_html__('Slider Bullet Active Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#8a8a8a',
						'selector' => '.gdlr-core-flexslider .flex-control-nav li a.flex-active{ border-color: #gdlr#; }' .
							'.gdlr-core-flexslider .flex-control-nav li a.flex-active{ background-color: #gdlr#; }' .
							'.gdlr-core-flexslider.gdlr-core-bullet-style-cylinder .flex-control-nav li a.flex-active{ background-color: #gdlr#; }'
					),
					'slider-control-navigation-background-color' => array(
						'title' => esc_html__('Slider Bullet Background Color ( Cylinder Style )', 'financity'),
						'type' => 'colorpicker',
						'default' => '#dfdfdf',
						'selector' => '.gdlr-core-flexslider.gdlr-core-bullet-style-cylinder .flex-control-nav li a{ background-color: #gdlr#; }'
					),
					'social-share-icon-color' => array(
						'title' => esc_html__('Social Share Icon Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#202020',
						'selector' => '.gdlr-core-social-share-item a{ color: #gdlr#; }'
					),
					'social-share-divider-color' => array(
						'title' => esc_html__('Social Share Divider Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#e5e5e5',
						'selector' => '.gdlr-core-social-share-item .gdlr-core-divider{ border-color: #gdlr#; }'
					),
					'social-share-text-color' => array(
						'title' => esc_html__('Social Share Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#202020',
						'selector' => '.gdlr-core-social-share-item .gdlr-core-social-share-count{ color: #gdlr#; }'
					),
					'stunning-text-title-color' => array(
						'title' => esc_html__('Stunning Text Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#747474',
						'selector' => '.gdlr-core-stunning-text-item-caption{ color: #gdlr#; }'
					),
					'stunning-text-caption-color' => array(
						'title' => esc_html__('Stunning Text Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#383838',
						'selector' => '.gdlr-core-stunning-text-item-title{ color: #gdlr#; }'
					),
					'tab-title-color' => array(
						'title' => esc_html__('Tab Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#8d8d8d',
						'selector' => '.gdlr-core-tab-item-title{ color: #gdlr#; }'
					),
					'tab-title-background-color' => array(
						'title' => esc_html__('Tab Title Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f7f7f7',
						'selector' => '.gdlr-core-tab-style1-horizontal .gdlr-core-tab-item-title, ' .
							'.gdlr-core-tab-style1-vertical .gdlr-core-tab-item-title{ background-color: #gdlr#; }'
					),
					'tab-title-border-color' => array(
						'title' => esc_html__('Tab Title Border Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ebebeb',
						'selector' => '.gdlr-core-tab-item-title-wrap, .gdlr-core-tab-item-content-wrap, .gdlr-core-tab-item-title{ border-color: #gdlr#; }'
					),	
					'tab-title-hover-bar-color' => array(
						'title' => esc_html__('Tab Title Hover Bar Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#2d9bea',
						'selector' => '.gdlr-core-tab-item-title-line{ border-color: #gdlr#; }'
					),
					'tab-title-active-color' => array(
						'title' => esc_html__('Tab Title Active Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#464646',
						'selector' => '.gdlr-core-tab-item-title.gdlr-core-active{ color: #gdlr#; }'
					),
					'tab-title-active-background-color' => array(
						'title' => esc_html__('Tab Title Active Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-tab-style1-horizontal .gdlr-core-tab-item-title.gdlr-core-active, ' .
							'.gdlr-core-tab-style1-vertical .gdlr-core-tab-item-title.gdlr-core-active{ background-color: #gdlr#; }'
					),
					'table-head-background-color' => array(
						'title' => esc_html__('Table Head Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#36bddb',
						'selector'=> 'table tr th{ background-color: #gdlr#; }'
					),			
					'table-head-text-color' => array(
						'title' => esc_html__('Table Head Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector'=> 'table tr th, .financity-body table tr th a, .financity-body table tr th a:hover{ color: #gdlr#; }'
					),	
					'table-odd-background-color' => array(
						'title' => esc_html__('Table Odd Row Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f9f9f9',
						'selector'=> 'table tr:nth-child(odd){ background-color: #gdlr#; }'
					),			
					'table-odd-text-color' => array(
						'title' => esc_html__('Table Odd Row Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#949494',
						'selector'=> 'table tr:nth-child(odd){ color: #gdlr#; }'
					),
					'table-even-background' => array(
						'title' => esc_html__('Table Even Row Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f3f3f3',
						'selector'=> 'table tr:nth-child(even){ background-color: #gdlr#; }'
					),			
					'table-even-text' => array(
						'title' => esc_html__('Table Even Row Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#949494',
						'selector'=> 'table tr:nth-child(even){ color: #gdlr#; }'
					),		
					'testimonial-title-color' => array(
						'title' => esc_html__('Testimonial Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#232323',
						'selector'=> '.gdlr-core-testimonial-item .gdlr-core-testimonial-item-title{ color: #gdlr#; }'
					),		
					'testimonial-content-color' => array(
						'title' => esc_html__('Testimonial Content Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#707070',
						'selector'=> '.gdlr-core-testimonial-item .gdlr-core-testimonial-content{ color: #gdlr#; }'
					),		
					'testimonial-author-color' => array(
						'title' => esc_html__('Testimonial Author Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#3b3b3b',
						'selector'=> '.gdlr-core-testimonial-item .gdlr-core-testimonial-title{ color: #gdlr#; }'
					),		
					'testimonial-rating-color' => array(
						'title' => esc_html__('Testimonial Rating Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffa127',
						'selector'=> '.gdlr-core-testimonial-item .gdlr-core-testimonial-position .gdlr-core-rating i{ color: #gdlr#; }'
					),
					'testimonial-position-color' => array(
						'title' => esc_html__('Testimonial Position Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#3b3b3b',
						'selector'=> '.gdlr-core-testimonial-item .gdlr-core-testimonial-position{ color: #gdlr#; }'
					),		
					'testimonial-quote-color' => array(
						'title' => esc_html__('Testimonial Quote Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#282828',
						'selector'=> '.gdlr-core-testimonial-item .gdlr-core-testimonial-quote{ color: #gdlr#; }'
					),		
					'title-item-title-color' => array(
						'title' => esc_html__('Title Item\'s Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#383838',
						'selector'=> '.gdlr-core-title-item .gdlr-core-title-item-title, .gdlr-core-title-item .gdlr-core-title-item-title a{ color: #gdlr#; }'
					),		
					'title-item-caption-color' => array(
						'title' => esc_html__('Title Item\'s Caption Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#747474',
						'selector'=> '.gdlr-core-title-item .gdlr-core-title-item-caption{ color: #gdlr#; }'
					),

				)
			) , // element-3-color
			
			'product-color' => array(
				'title' => esc_html__('Woocommerce', 'financity'),
				'options' => array(
				
					'woocommerce-theme-color' => array(
						'title' => esc_html__('Woocommerce Theme Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#bd584e',
						'selector' => '.woocommerce .star-rating span, .single-product.woocommerce #review_form #respond p.stars a, ' . 
							'.single-product.woocommerce div.product .product_meta, .single-product.woocommerce div.product .product_meta a{ color: #gdlr#; }' . 
							'.woocommerce #respond input#submit.alt, .woocommerce a.button.alt, .woocommerce button.button.alt, .woocommerce input.button.alt, ' .
							'.woocommerce #respond input#submit, .woocommerce a.button, .woocommerce button.button, html .woocommerce input.button, html .woocommerce span.onsale{ background-color: #gdlr#; }'
					),
					'woocommerce-price-color' => array(
						'title' => esc_html__('Woocommerce Price Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#bd584e',
						'selector' => 'span.woocommerce-Price-amount.amount{ color: #gdlr#; }'
					),
					'woocommerce-price-linethrough-color' => array(
						'title' => esc_html__('Woocommerce Price Line Through Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#949494',
						'selector' => '.woocommerce .price del, .gdlr-core-product-price del, del span.woocommerce-Price-amount.amount{ color: #gdlr#; }'
					),
					'woocommerce-button-background-hover-color' => array(
						'title' => esc_html__('Woocommerce Button Background Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#d4786e',
						'selector' => '.woocommerce #respond input#submit.alt:hover, .woocommerce a.button.alt:hover, .woocommerce button.button.alt:hover, .woocommerce input.button.alt:hover, ' .
							'.woocommerce #respond input#submit:hover, .woocommerce a.button:hover, .woocommerce button.button:hover, .woocommerce input.button:hover{ background-color: #gdlr#; }'
					),
					'woocommerce-button-text-color' => array(
						'title' => esc_html__('Woocommerce Button Text Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.woocommerce #respond input#submit.alt, .woocommerce a.button.alt, .woocommerce button.button.alt, .woocommerce input.button.alt, ' .
							'.woocommerce #respond input#submit, .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, ' .
							'.woocommerce #respond input#submit.disabled, .woocommerce #respond input#submit:disabled, .woocommerce #respond input#submit:disabled[disabled], ' .
							'.woocommerce a.button.disabled, .woocommerce a.button:disabled, .woocommerce a.button:disabled[disabled], .woocommerce button.button.disabled, ' .
							'.woocommerce button.button:disabled, .woocommerce button.button:disabled[disabled], .woocommerce input.button.disabled, .woocommerce input.button:disabled, .woocommerce input.button:disabled[disabled]{ color: #gdlr#; }'
					),
					'woocommerce-button-text-hover-color' => array(
						'title' => esc_html__('Woocommerce Button Text Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.woocommerce #respond input#submit.alt:hover, .woocommerce a.button.alt:hover, .woocommerce button.button.alt:hover, .woocommerce input.button.alt:hover, ' .
							'.woocommerce #respond input#submit:hover, .woocommerce a.button:hover, .woocommerce button.button:hover, .woocommerce input.button:hover{ color: #gdlr#; }'
					),
					'woocommerce-input-background-color' => array(
						'title' => esc_html__('Woocommerce Input Background Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#f3f3f3',
						'selector' => '.single-product.woocommerce div.product .quantity .qty, #add_payment_method #payment, .woocommerce-checkout #payment, ' . 
							'.single-product.woocommerce #reviews #comments ol.commentlist li{ background-color: #gdlr#; }'
					),
					
					'product-item-title-color' => array(
						'title' => esc_html__('Product Item Title Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#191919',
						'selector' => '.gdlr-core-product-grid .gdlr-core-product-title a{ color: #gdlr#; }'
					),
					'product-item-title-hover-color' => array(
						'title' => esc_html__('Product Item Title Hover Color', 'financity'),
						'type' => 'colorpicker',
						'default' => '#434343',
						'selector' => '.gdlr-core-product-grid .gdlr-core-product-title a:hover{ color: #gdlr#; }'
					),
					'view-detail-text-color' => array(
						'title' => esc_html__('Product Item View Detail Text', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-product-thumbnail .gdlr-core-product-view-detail, ' .
							'.gdlr-core-product-thumbnail .gdlr-core-product-view-detail:hover{ color: #gdlr#; }'
					),
					'view-detail-background-color' => array(
						'title' => esc_html__('Product Item View Detail Background', 'financity'),
						'type' => 'colorpicker',
						'data-type' => 'rgba',
						'default' => '#000000',
						'selector' => '.gdlr-core-product-thumbnail .gdlr-core-product-view-detail{ background-color: #gdlr#; background-color: rgba(#gdlra#, 0.9); }'
					),
					'add-to-cart-text-color' => array(
						'title' => esc_html__('Product Item Add To Cart Text', 'financity'),
						'type' => 'colorpicker',
						'default' => '#ffffff',
						'selector' => '.gdlr-core-product-thumbnail .added_to_cart, ' . 
							'.gdlr-core-product-thumbnail .added_to_cart:hover, ' . 
							'.gdlr-core-product-thumbnail .gdlr-core-product-add-to-cart, ' . 
							'.gdlr-core-product-thumbnail .gdlr-core-product-add-to-cart:hover{ color: #gdlr#; }'
					),
					'add-to-cart-background-color' => array(
						'title' => esc_html__('Product Item Add To Cart Background', 'financity'),
						'type' => 'colorpicker',
						'data-type' => 'rgba',
						'default' => '#bd584e',
						'selector' => '.gdlr-core-product-thumbnail .added_to_cart, ' .
							'.gdlr-core-product-thumbnail .gdlr-core-product-add-to-cart{ background-color: #gdlr#; background-color: rgba(#gdlra#, 0.9); }'
					),

					'widget-price-filter-bar-background-color' => array(
						'title' => esc_html__('Widget Price Filter Bar Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#e6e6e6',
						'selector' => '.woocommerce .widget_price_filter .price_slider_wrapper .ui-widget-content{ background-color: #gdlr#; }'
					),
					'widget-price-filter-range-color' => array(
						'title' => esc_html__('Widget Price Filter Range Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#824141',
						'selector' => '.woocommerce .widget_price_filter .ui-slider .ui-slider-range{ background-color: #gdlr#; }'
					),
					'widget-price-filter-handle-color' => array(
						'title' => esc_html__('Widget Price Filter Handle Background', 'financity'),
						'type' => 'colorpicker',
						'default' => '#b3564e',
						'selector' => '.woocommerce .widget_price_filter .ui-slider .ui-slider-handle{ background-color: #gdlr#; }'
					),



				)
			),

		) // color-options
		
	)), 6);	