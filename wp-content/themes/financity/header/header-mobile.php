<?php
	// mobile menu template
	
	echo '<div class="financity-mobile-header-wrap" >';

	// top bar
	$top_bar = financity_get_option('general', 'enable-top-bar-on-mobile', 'disable');
	if( $top_bar == 'enable' ){
		get_template_part('header/header', 'top-bar');
	}

	// header
	echo '<div class="financity-mobile-header financity-header-background financity-style-slide" id="financity-mobile-header" >';
	echo '<div class="financity-mobile-header-container financity-container" >';
	echo financity_get_logo(array('mobile' => true));

	echo '<div class="financity-mobile-menu-right" >';

	// search icon
	$enable_search = (financity_get_option('general', 'enable-main-navigation-search', 'enable') == 'enable')? true: false;
	if( $enable_search ){
		echo '<div class="financity-main-menu-search" id="financity-mobile-top-search" >';
		echo '<i class="fa fa-search" ></i>';
		echo '</div>';
		financity_get_top_search();
	}

	// cart icon
	$enable_cart = (financity_get_option('general', 'enable-main-navigation-cart', 'enable') == 'enable' && class_exists('WooCommerce'))? true: false;
	if( $enable_cart ){
		echo '<div class="financity-main-menu-cart" id="financity-mobile-menu-cart" >';
		echo '<i class="fa fa-shopping-cart" ></i>';
		financity_get_woocommerce_bar();
		echo '</div>';
	}

	// mobile menu
	if( has_nav_menu('mobile_menu') ){
		financity_get_custom_menu(array(
			'type' => financity_get_option('general', 'right-menu-type', 'right'),
			'container-class' => 'financity-mobile-menu',
			'button-class' => 'financity-mobile-menu-button',
			'icon-class' => 'fa fa-bars',
			'id' => 'financity-mobile-menu',
			'theme-location' => 'mobile_menu'
		));
	}
	echo '</div>'; // financity-mobile-menu-right
	echo '</div>'; // financity-mobile-header-container
	echo '</div>'; // financity-mobile-header

	echo '</div>'; // financity-mobile-header-wrap