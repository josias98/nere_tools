<?php
/**
 * The template for displaying the footer
 */
	
	$post_option = financity_get_post_option(get_the_ID());
	if( empty($post_option['enable-footer']) || $post_option['enable-footer'] == 'default' ){
		$enable_footer = financity_get_option('general', 'enable-footer', 'enable');
	}else{
		$enable_footer = $post_option['enable-footer'];
	}	
	if( empty($post_option['enable-copyright']) || $post_option['enable-copyright'] == 'default' ){
		$enable_copyright = financity_get_option('general', 'enable-copyright', 'enable');
	}else{
		$enable_copyright = $post_option['enable-copyright'];
	}

	$fixed_footer = financity_get_option('general', 'fixed-footer', 'disable');
	echo '</div>'; // financity-page-wrapper

	if( $enable_footer == 'enable' || $enable_copyright == 'enable' ){

		if( $fixed_footer == 'enable' ){
			echo '</div>'; // financity-body-wrapper

			echo '<footer class="financity-fixed-footer" id="financity-fixed-footer" >';
		}else{
			echo '<footer>';
		}

		if( $enable_footer == 'enable' ){

			$financity_footer_layout = array(
				'footer-1'=>array('financity-column-60'),
				'footer-2'=>array('financity-column-15', 'financity-column-15', 'financity-column-15', 'financity-column-15'),
				'footer-3'=>array('financity-column-15', 'financity-column-15', 'financity-column-30',),
				'footer-4'=>array('financity-column-20', 'financity-column-20', 'financity-column-20'),
				'footer-5'=>array('financity-column-20', 'financity-column-40'),
				'footer-6'=>array('financity-column-40', 'financity-column-20'),
			);
			$footer_style = financity_get_option('general', 'footer-style');
			$footer_style = empty($footer_style)? 'footer-2': $footer_style;

			$count = 0;
			$has_widget = false;
			foreach( $financity_footer_layout[$footer_style] as $layout ){ $count++;
				if( is_active_sidebar('footer-' . $count) ){ $has_widget = true; }
			}

			if( $has_widget ){ 	

				$footer_column_divider = financity_get_option('general', 'enable-footer-column-divider', 'enable');
				$extra_class  = ($footer_column_divider == 'enable')? ' financity-with-column-divider': '';

				echo '<div class="financity-footer-wrapper ' . esc_attr($extra_class) . '" >';
				echo '<div class="financity-footer-container financity-container clearfix" >';
				
				$count = 0;
				foreach( $financity_footer_layout[$footer_style] as $layout ){ $count++;
					echo '<div class="financity-footer-column financity-item-pdlr ' . esc_attr($layout) . '" >';
					if( is_active_sidebar('footer-' . $count) ){
						dynamic_sidebar('footer-' . $count); 
					}
					echo '</div>';
				}
				
				echo '</div>'; // financity-footer-container
				echo '</div>'; // financity-footer-wrapper 
			}
		} // enable footer

		if( $enable_copyright == 'enable' ){
			$copyright_style = financity_get_option('general', 'copyright-style', 'center');
			
			if( $copyright_style == 'center' ){
				$copyright_text = financity_get_option('general', 'copyright-text');

				if( !empty($copyright_text) ){
					echo '<div class="financity-copyright-wrapper" >';
					echo '<div class="financity-copyright-container financity-container">';
					echo '<div class="financity-copyright-text financity-item-pdlr">';
					echo gdlr_core_escape_content(gdlr_core_text_filter($copyright_text));
					echo '</div>';
					echo '</div>';
					echo '</div>'; // financity-copyright-wrapper
				}
			}else if( $copyright_style == 'left-right' ){
				$copyright_left = financity_get_option('general', 'copyright-left');
				$copyright_right = financity_get_option('general', 'copyright-right');

				if( !empty($copyright_left) || !empty($copyright_right) ){
					echo '<div class="financity-copyright-wrapper" >';
					echo '<div class="financity-copyright-container financity-container clearfix">';
					if( !empty($copyright_left) ){
						echo '<div class="financity-copyright-left financity-item-pdlr">';
						echo gdlr_core_escape_content(gdlr_core_text_filter($copyright_left));
						echo '</div>';
					}

					if( !empty($copyright_right) ){
						echo '<div class="financity-copyright-right financity-item-pdlr">';
						echo gdlr_core_escape_content(gdlr_core_text_filter($copyright_right));
						echo '</div>';
					}
					echo '</div>';
					echo '</div>'; // financity-copyright-wrapper
				}
			}
		}

		echo '</footer>';

		if( $fixed_footer == 'disable' ){
			echo '</div>'; // financity-body-wrapper
		}
		echo '</div>'; // financity-body-outer-wrapper

	// disable footer	
	}else{
		echo '</div>'; // financity-body-wrapper
		echo '</div>'; // financity-body-outer-wrapper
	}

	$header_style = financity_get_option('general', 'header-style', 'plain');
	
	if( $header_style == 'side' || $header_style == 'side-toggle' ){
		echo '</div>'; // financity-header-side-nav-content
	}

	$back_to_top = financity_get_option('general', 'enable-back-to-top', 'disable');
	if( $back_to_top == 'enable' ){
		echo '<a href="#financity-top-anchor" class="financity-footer-back-to-top-button" id="financity-footer-back-to-top-button"><i class="fa fa-angle-up" ></i></a>';
	}
?>

<?php wp_footer(); ?>

</body>
</html>