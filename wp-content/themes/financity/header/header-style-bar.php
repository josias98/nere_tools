<?php
	/* a template for displaying the header area */

	// header container
	$body_layout = financity_get_option('general', 'layout', 'full');
	$body_margin = financity_get_option('general', 'body-margin', '0px');
	$header_width = financity_get_option('general', 'header-width', 'boxed');
	$header_style = financity_get_option('general', 'header-bar-navigation-align', 'center');
	$header_background_style = financity_get_option('general', 'header-background-style', 'solid');

	$header_wrap_class = '';
	if( $header_style == 'center-logo' ){
		$header_wrap_class .= ' financity-style-center';
	}else{
		$header_wrap_class .= ' financity-style-left';
	}

	$header_container_class = '';
	if( $header_width == 'boxed' ){
		$header_container_class .= ' financity-container';
	}else if( $header_width == 'custom' ){
		$header_container_class .= ' financity-header-custom-container';
	}else{
		$header_container_class .= ' financity-header-full';
	}

	$navigation_wrap_class  = ' financity-sticky-navigation financity-sticky-navigation-height';
	if( $header_style == 'center' || $header_style == 'center-logo' ){
		$navigation_wrap_class .= ' financity-style-center';
	}else{
		$navigation_wrap_class .= ' financity-style-left';
	}
	if( $body_layout == 'boxed' || $body_margin != '0px' ){
		$navigation_wrap_class .= ' financity-style-slide';
	}else{
		$navigation_wrap_class .= '  financity-style-fixed';
	}

?>	
<header class="financity-header-wrap financity-header-style-bar <?php echo esc_attr($header_wrap_class); ?>" >
	<div class="financity-header-background" ></div>
	<div class="financity-header-container clearfix <?php echo esc_attr($header_container_class); ?>">
		<div class="financity-header-container-inner">
		<?php
			echo financity_get_logo();

			// logo right section
			$logo_right = '';
			for( $i = 1; $i <= 3; $i++ ){
				$logo_right_icon = financity_get_option('general', 'logo-right-box' . $i . '-icon', '');
				$logo_right_title = financity_get_option('general', 'logo-right-box' . $i . '-title', '');
				$logo_right_caption = financity_get_option('general', 'logo-right-box' . $i . '-caption', '');

				if( !empty($logo_right_icon) || !empty($logo_right_title) || !empty($logo_right_caption) ){
					$logo_right .= '<div class="financity-logo-right-block" >';
					if( !empty($logo_right_icon) ){
						$logo_right .= '<i class="financity-logo-right-block-icon ' . esc_attr($logo_right_icon) . '" ></i>';
					}
					$logo_right .= '<div class="financity-logo-right-block-content" >';
					if( !empty($logo_right_title) ){
						$logo_right .= '<div class="financity-logo-right-block-title" >' . gdlr_core_escape_content($logo_right_title) . '</div>';
					}
					if( !empty($logo_right_caption) ){
						$logo_right .= '<div class="financity-logo-right-block-caption" >' . gdlr_core_escape_content($logo_right_caption) . '</div>';
					}
					$logo_right .= '</div>'; // block-content
					$logo_right .= '</div>'; // block
				}
			}

			// header right button
			$enable_right_button = (financity_get_option('general', 'enable-header-right-button', 'disable') == 'enable')? true: false;
			if( $enable_right_button ){
				$button_link = financity_get_option('general', 'header-right-button-link', '');
				$button_link_target = financity_get_option('general', 'header-right-button-link-target', '_self');
				
				$logo_right .= '<a class="financity-header-right-button" href="' . esc_url($button_link) . '" target="' . esc_attr($button_link_target) . '" >';
				$logo_right .= financity_get_option('general', 'header-right-button-text', '');
				$logo_right .= '</a>';
			}

			if( !empty($logo_right) ){
				echo '<div class="financity-logo-right-text financity-item-pdlr" >';
				echo gdlr_core_escape_content($logo_right);
				echo '</div>';
			}
		?>
		</div>
	</div>

	<div class="financity-navigation-bar-wrap <?php echo esc_attr($navigation_wrap_class); ?>" >
		<?php 
			if( $header_background_style == 'solid' ){
				echo '<div class="financity-navigation-background" ></div>';
			}else{
				echo '<div class="financity-navigation-background financity-show-in-sticky financity-style-' . esc_attr($header_background_style) . '" ></div>';
			}
		?>
		<div class="financity-navigation-container clearfix <?php echo esc_attr($header_container_class); ?>">
			<?php
				$navigation_class = '';
				if( financity_get_option('general', 'enable-main-navigation-submenu-indicator', 'disable') == 'enable' ){
					$navigation_class .= 'financity-navigation-submenu-indicator ';
				}
			?>
			<div class="financity-navigation financity-item-mglr clearfix <?php echo esc_attr($navigation_class); ?>" >
			<?php
				if( $header_background_style == 'transparent' ){
					echo '<div class="financity-navigation-background financity-hide-in-sticky" ></div>';
				}

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
				if( $header_style == 'center' || $header_style == 'center-logo' ){
					$menu_right_class = ' financity-item-mglr financity-navigation-top';
				}

				// menu right side
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
					if( has_nav_menu('right_menu') ){
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
				}
			?>
			</div><!-- financity-navigation -->

		</div><!-- financity-header-container -->
	</div><!-- financity-navigation-bar-wrap -->

</header><!-- header -->