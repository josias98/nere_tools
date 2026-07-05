<?php
	/* a template for displaying the header area */

	// header container
	$header_width = financity_get_option('general', 'header-width', 'boxed');
	
	if( $header_width == 'boxed' ){
		$header_container_class = ' financity-container';
	}else if( $header_width == 'custom' ){
		$header_container_class = ' financity-header-custom-container';
	}else{
		$header_container_class = ' financity-header-full';
	}

	$header_style = financity_get_option('general', 'header-boxed-style', 'menu-right');
	$navigation_offset = financity_get_option('general', 'fixed-navigation-anchor-offset', '');

	$header_wrap_class  = ' financity-style-' . $header_style;
	$header_wrap_class .= ' financity-sticky-navigation financity-style-slide';
?>	
<header class="financity-header-wrap financity-header-style-boxed <?php echo esc_attr($header_wrap_class); ?>" <?php
		if( !empty($navigation_offset) ){
			echo 'data-navigation-offset="' . esc_attr($navigation_offset) . '" ';
		}
	?> >
	<div class="financity-header-container clearfix <?php echo esc_attr($header_container_class); ?>">
		<div class="financity-header-container-inner clearfix">	

			<div class="financity-header-background  financity-item-mglr" ></div>
			<div class="financity-header-container-item clearfix">
				<?php

					if( $header_style == 'splitted-menu' ){
						add_filter('financity_center_menu_item', 'financity_get_logo');
					}else{
						echo financity_get_logo();
					}

					$navigation_class = '';
					if( financity_get_option('general', 'enable-main-navigation-submenu-indicator', 'disable') == 'enable' ){
						$navigation_class = 'financity-navigation-submenu-indicator ';
					}
				?>
				<div class="financity-navigation financity-item-pdlr clearfix <?php echo esc_attr($navigation_class); ?>" >
				<?php
					// print main menu
					if( has_nav_menu('main_menu') ){
						echo '<div class="financity-main-menu" id="financity-main-menu" >';
						wp_nav_menu(array(
							'theme_location'=>'main_menu', 
							'container'=> '', 
							'menu_class'=> 'sf-menu',
							'walker' => new financity_menu_walker()
						));
						$slide_bar = financity_get_option('general', 'navigation-slide-bar', 'enable');
						if( $slide_bar == 'enable' ){
							echo '<div class="financity-navigation-slide-bar" id="financity-navigation-slide-bar" ></div>';
						}
						echo '</div>';
					}

					// menu right side
					$menu_right_class = '';
					if( $header_style == 'center-menu' || $header_style == 'splitted-menu' ){
						$menu_right_class = ' financity-item-mglr financity-navigation-top financity-navigation-right';
					}

					$enable_search = (financity_get_option('general', 'enable-main-navigation-search', 'enable') == 'enable')? true: false;
					$enable_cart = (financity_get_option('general', 'enable-main-navigation-cart', 'enable') == 'enable' && class_exists('WooCommerce'))? true: false;
					$enable_right_button = (financity_get_option('general', 'enable-main-navigation-right-button', 'disable') == 'enable')? true: false;
					if( has_nav_menu('right_menu') || $enable_search || $enable_cart ){
						echo '<div class="financity-main-menu-right-wrap clearfix ' . esc_attr($menu_right_class) . '" >';

						// search icon
						if( $enable_search ){
							echo '<div class="financity-main-menu-search" id="financity-top-search" >';
							echo '<i class="fa fa-search" ></i>';
							echo '</div>';
							financity_get_top_search();
						}

						// cart icon
						if( $enable_cart ){
							echo '<div class="financity-main-menu-cart" id="financity-main-menu-cart" >';
							echo '<i class="fa fa-shopping-cart" ></i>';
							financity_get_woocommerce_bar();
							echo '</div>';
						}

						// menu right button
						if( $enable_right_button ){
							$button_link = financity_get_option('general', 'main-navigation-right-button-link', '');
							$button_link_target = financity_get_option('general', 'main-navigation-right-button-link-target', '_self');
							echo '<a class="financity-main-menu-right-button" href="' . esc_url($button_link) . '" target="' . esc_attr($button_link_target) . '" >';
							echo financity_get_option('general', 'main-navigation-right-button-text', '');
							echo '</a>';
						}

						// print right menu
						if( has_nav_menu('right_menu') && $header_style != 'splitted-menu' ){
							financity_get_custom_menu(array(
								'container-class' => 'financity-main-menu-right',
								'button-class' => 'financity-right-menu-button financity-top-menu-button',
								'icon-class' => 'fa fa-bars',
								'id' => 'financity-right-menu',
								'theme-location' => 'right_menu',
								'type' => financity_get_option('general', 'right-menu-type', 'right')
							));
						}

						echo '</div>'; // financity-main-menu-right-wrap

						if( has_nav_menu('right_menu') && $header_style == 'splitted-menu' ){
							echo '<div class="financity-main-menu-left-wrap clearfix financity-item-pdlr financity-navigation-top financity-navigation-left" >';
							financity_get_custom_menu(array(
								'container-class' => 'financity-main-menu-right',
								'button-class' => 'financity-right-menu-button financity-top-menu-button',
								'icon-class' => 'fa fa-bars',
								'id' => 'financity-right-menu',
								'theme-location' => 'right_menu',
								'type' => financity_get_option('general', 'right-menu-type', 'right')
							));
							echo '</div>';
						}
					}
				?>
				</div><!-- financity-navigation -->

			</div><!-- financity-header-container-inner -->
		</div><!-- financity-header-container-item -->
	</div><!-- financity-header-container -->
</header><!-- header -->