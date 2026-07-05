<?php

	// declare woocommerce support
	add_action('after_setup_theme', 'financity_woocommerce_support');
	if( !function_exists( 'financity_woocommerce_support' ) ){
		function financity_woocommerce_support(){
			add_theme_support( 'woocommerce' );
			add_theme_support( 'wc-product-gallery-zoom' );
			add_theme_support( 'wc-product-gallery-lightbox' );
			add_theme_support( 'wc-product-gallery-slider' );
		}
	}	

	// modify woocommerce wrapper
	remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
	remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

	add_action('woocommerce_before_main_content', 'financity_woocommerce_wrapper_start', 10);
	if( !function_exists( 'financity_woocommerce_wrapper_start' ) ){
		function financity_woocommerce_wrapper_start(){
			echo '<div class="financity-content-container financity-container">';
			echo '<div class="financity-content-area financity-item-pdlr financity-sidebar-style-none clearfix" >';
		}
	}

	add_action('woocommerce_after_main_content', 'financity_woocomemrce_wrapper_end', 10);
	if( !function_exists( 'financity_woocomemrce_wrapper_end' ) ){
		function financity_woocomemrce_wrapper_end(){
			echo '</div>'; // financity-content-area
			echo '</div>'; // financity-content-container
		}
	}

	// remove breadcrumbs on single product
	add_action('wp', 'financity_init_woocommerce_hook');
	if( !function_exists( 'financity_init_woocommerce_hook' ) ){
		function financity_init_woocommerce_hook(){
			if( is_single() && get_post_type() == 'product' ){ 
				add_filter('woocommerce_product_description_heading', 'financity_remove_woocommerce_tab_heading');
				add_filter('woocommerce_product_additional_information_heading', 'financity_remove_woocommerce_tab_heading');

				remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
				remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
				remove_action('woocommerce_review_before_comment_meta', 'woocommerce_review_display_rating', 10);

				add_action('woocommerce_review_after_comment_text', 'woocommerce_review_display_rating', 10);
			}
		}
	}
	
	if( !function_exists( 'financity_remove_woocommerce_tab_heading' ) ){
		function financity_remove_woocommerce_tab_heading( $title ){
			return '';
		}
	}

	add_filter('woocommerce_review_gravatar_size', 'financity_woocommerce_review_gravatar_size');
	if( !function_exists( 'financity_woocommerce_review_gravatar_size' ) ){
		function financity_woocommerce_review_gravatar_size( $size ){
			return 120;
		}
	}

	if( !function_exists('financity_get_woocommerce_bar') ){
		function financity_get_woocommerce_bar(){

			global $woocommerce;
			
			if(!empty($woocommerce)){
				
				echo '<span class="financity-top-cart-count">' . $woocommerce->cart->cart_contents_count . '</span>';
				echo '<div class="financity-top-cart-hover-area" ></div>';

				echo '<div class="financity-top-cart-content-wrap" >';
				echo '<div class="financity-top-cart-content" >';
				echo '<div class="financity-top-cart-count-wrap" >';
				echo '<span class="head">' . esc_html__('Items : ', 'financity') . ' </span>';
				echo '<span class="financity-top-cart-count">' . $woocommerce->cart->cart_contents_count . '</span>'; 
				echo '</div>';
				
				echo '<div class="financity-top-cart-amount-wrap" >';
				echo '<span class="head">' . esc_html__('Subtotal :', 'financity') . ' </span>';
				echo '<span class="financity-top-cart-amount">' . $woocommerce->cart->get_cart_total() . '</span>';
				echo '</div>';
				
				echo '<a class="financity-top-cart-button" href="' . esc_url($woocommerce->cart->get_cart_url()) . '" >';
				echo esc_html__('View Cart', 'financity');
				echo '</a>';

				echo '<a class="financity-top-cart-checkout-button" href="' . esc_url($woocommerce->cart->get_checkout_url()) . '" >';
				echo esc_html__('Check Out', 'financity');
				echo '</a>';
				echo '</div>';
				echo '</div>';
			}
		}
	}

	add_filter('woocommerce_add_to_cart_fragments', 'financity_woocommerce_cart_ajax');
	if( !function_exists('financity_woocommerce_cart_ajax') ){
		function financity_woocommerce_cart_ajax($fragments){
			global $woocommerce;

			$fragments['span.financity-top-cart-count'] = '<span class="financity-top-cart-count">' . $woocommerce->cart->cart_contents_count . '</span>'; 
			$fragments['span.financity-top-cart-amount'] = '<span class="financity-top-cart-amount">' . $woocommerce->cart->get_cart_total() . '</span>';

			return $fragments;
		}
	}	

	add_filter('woocommerce_output_related_products_args', 'financity_related_products_args');
	if( !function_exists('financity_related_products_args') ){
		function financity_related_products_args($args){
			if( class_exists('gdlr_core_pb_element_product') ){
				$num_fetch = financity_get_option('general', 'woocommerce-related-product-num-fetch', '4');
				$args['posts_per_page'] = $num_fetch;
			}
			
			return $args;
		}
	}