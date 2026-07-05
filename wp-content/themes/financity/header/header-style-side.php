<?php
	/* a template for displaying the header area */

	$header_side_style = financity_get_option('general', 'header-side-style', 'top-left');
	$header_class = 'financity-' . financity_get_option('general', 'header-side-align', 'left') . '-align';
?>	
<header class="financity-header-wrap financity-header-style-side <?php echo esc_attr($header_class); ?>" >
	<?php

		$logo_wrap_class = '';
		$navigation_class = '';
		if( financity_get_option('general', 'enable-main-navigation-submenu-indicator', 'disable') == 'enable' ){
			$navigation_class .= 'financity-navigation-submenu-indicator ';
		}
		if( in_array($header_side_style, array('middle-left-2', 'middle-right-2')) ){
			$logo_wrap_class .= 'financity-pos-middle ';
		}else if( in_array($header_side_style, array('middle-left', 'middle-right')) ){
			$navigation_class .= 'financity-pos-middle ';
		} 

		echo financity_get_logo(array('padding' => false, 'wrapper-class' => $logo_wrap_class));
	?>
	<div class="financity-navigation clearfix financity-pos-middle <?php echo esc_attr($navigation_class); ?>" >
	<?php
		// print main menu
		if( has_nav_menu('main_menu') ){
			echo '<div class="financity-main-menu" id="financity-main-menu" >';
			wp_nav_menu(array(
				'theme_location'=>'main_menu', 
				'container'=> '', 
				'menu_class'=> 'sf-vertical'
			));
			echo '</div>';
		}

		// menu right side
		$enable_search = (financity_get_option('general', 'enable-main-navigation-search', 'enable') == 'enable')? true: false;
		$enable_cart = (financity_get_option('general', 'enable-main-navigation-cart', 'enable') == 'enable' && class_exists('WooCommerce'))? true: false;
		if( $enable_search || $enable_cart ){
			echo '<div class="financity-main-menu-right-wrap clearfix" >';

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

			echo '</div>'; // financity-main-menu-right-wrap
		}
	?>
	</div><!-- financity-navigation -->
	<?php
		// social network
		$top_bar_social = financity_get_option('general', 'enable-top-bar-social', 'enable');
		if( $top_bar_social == 'enable' ){

			$top_bar_social_class = '';
			if( in_array($header_side_style, array('top-left', 'top-right', 'middle-left', 'middle-right')) ){
				$top_bar_social_class .= 'financity-pos-bottom ';
			}

			echo '<div class="financity-header-social ' . esc_attr($top_bar_social_class) . '" >';
			get_template_part('header/header', 'social');
			echo '</div>';
			
			financity_set_option('general', 'enable-top-bar-social', 'disable');
		}
	?>
</header><!-- header -->