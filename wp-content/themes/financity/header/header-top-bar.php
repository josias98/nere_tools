<?php
	/* a template for displaying the top bar */

	if( financity_get_option('general', 'enable-top-bar', 'enable') == 'enable' ){

		$top_bar_width = financity_get_option('general', 'top-bar-width', 'boxed');
		$top_bar_divider = financity_get_option('general', 'enable-top-bar-divider', 'enable');
		$top_bar_container_class = '';

		if( $top_bar_width == 'boxed' ){
			$top_bar_container_class = 'financity-container ';
		}else if( $top_bar_width == 'custom' ){
			$top_bar_container_class = 'financity-top-bar-custom-container ';
		}else{
			$top_bar_container_class = 'financity-top-bar-full ';
		}

		if( $top_bar_divider == 'disable' ){
			$top_bar_container_class .= ' financity-no-divider';
		}
		
		echo '<div class="financity-top-bar" >';
		echo '<div class="financity-top-bar-background" ></div>';
		echo '<div class="financity-top-bar-container clearfix ' . esc_attr($top_bar_container_class) . '" >';

		$language_flag = financity_get_wpml_flag('dropdown');
		$left_text = financity_get_option('general', 'top-bar-left-text', '');
		if( !empty($left_text) || !empty($language_flag) ){
			echo '<div class="financity-top-bar-left financity-item-pdlr">';
			if( !empty($language_flag) ){
				echo gdlr_core_escape_content($language_flag);
				echo '<span class="financity-top-bar-divider financity-right-margin" ></span>';	
			}
			echo gdlr_core_escape_content(gdlr_core_text_filter($left_text));
			echo '</div>';
		}

		$right_text = financity_get_option('general', 'top-bar-right-text', '');
		$top_bar_social = financity_get_option('general', 'enable-top-bar-social', 'enable');
		if( !empty($right_text) || $top_bar_social == 'enable' ){
			echo '<div class="financity-top-bar-right financity-item-pdlr">';
			if( !empty($right_text) ){
				echo '<div class="financity-top-bar-right-text">';
				echo gdlr_core_escape_content(gdlr_core_text_filter($right_text));
				echo '</div>';
			}

			if( $top_bar_social == 'enable' ){
				echo '<span class="financity-top-bar-divider financity-left-margin" ></span>';
				echo '<div class="financity-top-bar-right-social" >';
				get_template_part('header/header', 'social');
				echo '</div>';	
			}
			echo '</div>';	
		}
		echo '</div>';
		echo '</div>';

	}  // top bar
?>