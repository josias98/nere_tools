<?php
	/* a template for displaying the single post title */
	
	$post_option = financity_get_post_option(get_the_ID());
	if( empty($post_option['blog-title-style']) || $post_option['blog-title-style'] == 'default' ){
		$title_style = financity_get_option('general', 'default-blog-title-style', 'small');
	}else{	
		$title_style = $post_option['blog-title-style'];

		if( $post_option['blog-title-style'] == 'custom' ){
			$title_spacing = empty($post_option['blog-title-padding'])? array(): $post_option['blog-title-padding'];
			$title_overlay_opacity = empty($post_option['blog-title-background-overlay-opacity'])? '': $post_option['blog-title-background-overlay-opacity'];
				
		}
	}

	$extra_class = ' financity-style-' . $title_style;

	if( $title_style != 'none' && $title_style != 'inside-content' ){	

		// background
		if( empty($post_option['blog-feature-image']) || $post_option['blog-feature-image'] == 'default' ){
			$feature_image_pos = financity_get_option('general', 'default-blog-feature-image', 'content');
		}else{
			$feature_image_pos = $post_option['blog-feature-image'];
		}

		if( $feature_image_pos == 'title-background' ){
			$title_background = wp_get_attachment_url(get_post_thumbnail_id());
			$extra_class .= ' financity-feature-image';

		}else if( !empty($post_option['blog-title-background-image']) ){
			if( is_numeric($post_option['blog-title-background-image']) ){
				$title_background = wp_get_attachment_url($post_option['blog-title-background-image']);
			}else{
				$title_background = $post_option['blog-title-background-image'];
			}
		}

		// start printing blog item
		echo '<div class="financity-blog-title-wrap ' . esc_attr($extra_class) . '" ' . gdlr_core_esc_style(array(
			'background-image' => empty($title_background)? '': $title_background
		)) . '>';
		echo '<div class="financity-header-transparent-substitute" ></div>';
		echo '<div class="financity-blog-title-top-overlay" ></div>';
		echo '<div class="financity-blog-title-overlay" ' . gdlr_core_esc_style(array(
			'opacity' => empty($title_overlay_opacity)? '': $title_overlay_opacity,
			'background-color' => empty($background_overlay_color)? '': $background_overlay_color
		)) . ' ></div>';
		echo '<div class="financity-blog-title-bottom-overlay" ></div>';
		echo '<div class="financity-blog-title-container financity-container" >';
		echo '<div class="financity-blog-title-content financity-item-pdlr" ' . gdlr_core_esc_style(array(
			'padding-top' => empty($title_spacing['padding-top'])? '': $title_spacing['padding-top'],
			'padding-bottom' => empty($title_spacing['padding-bottom'])? '': $title_spacing['padding-bottom']
		)) . ' >';

		get_template_part('content/content-single', 'title');

		echo '</div>'; // financity-page-title-content
		echo '</div>'; // financity-page-title-container
		echo '</div>'; // financity-page-title-wrap

		// breadcrumbs
		if( empty($post_option['enable-breadcrumbs']) || $post_option['enable-breadcrumbs'] == 'default' ){
			$breadcrumbs = financity_get_option('general', 'enable-default-breadcrumbs-on-post', 'enable');
		}else{
			$breadcrumbs = $post_option['enable-breadcrumbs'];
		}
		if( function_exists('bcn_display') ){
			echo '<div class="financity-breadcrumbs" >';
			echo '<div class="financity-breadcrumbs-container financity-container" >';
			echo '<div class="financity-breadcrumbs-item financity-item-pdlr" >';
       		bcn_display();
       		echo '</div>';
       		echo '</div>';
       		echo '</div>';
    	}

	}else if( $title_style == 'inside-content' ){
		echo '<div class="financity-header-transparent-substitute" ></div>';
	}
?>