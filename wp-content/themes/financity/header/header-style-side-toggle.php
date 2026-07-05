<?php
	/* a template for displaying the header area */
?>	
<header class="financity-header-wrap financity-header-style-side-toggle" >
	<?php
		$display_logo = financity_get_option('general', 'header-side-toggle-display-logo', 'enable');
		if( $display_logo == 'enable' ){
			echo financity_get_logo(array('padding' => false));
		}

		$navigation_class = '';
		if( financity_get_option('general', 'enable-main-navigation-submenu-indicator', 'disable') == 'enable' ){
			$navigation_class = 'financity-navigation-submenu-indicator ';
		}
	?>
	<div class="financity-navigation clearfix <?php echo esc_attr($navigation_class); ?>" >
	<?php
		// print main menu
		if( has_nav_menu('main_menu') ){
			financity_get_custom_menu(array(
				'container-class' => 'financity-main-menu',
				'button-class' => 'financity-side-menu-icon',
				'icon-class' => 'fa fa-bars',
				'id' => 'financity-main-menu',
				'theme-location' => 'main_menu',
				'type' => financity_get_option('general', 'header-side-toggle-menu-type', 'overlay')
			));
		}
	?>
	</div><!-- financity-navigation -->
	<?php

		// menu right side
		$enable_search = (financity_get_option('general', 'enable-main-navigation-search', 'enable') == 'enable')? true: false;
		$enable_cart = (financity_get_option('general', 'enable-main-navigation-cart', 'enable') == 'enable' && class_exists('WooCommerce'))? true: false;
		if( $enable_search || $enable_cart ){ 
			echo '<div class="financity-header-icon financity-pos-bottom" >';

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
</header><!-- header -->