<?php
/**
 * The template for displaying pages
 */

	get_header();

	while( have_posts() ){ the_post();
	
		$post_option = financity_get_post_option(get_the_ID());
		$show_content = (empty($post_option['show-content']) || $post_option['show-content'] == 'enable')? true: false;

		if( empty($post_option['sidebar']) ){
			$sidebar_type = 'none';
			$sidebar_left = '';
			$sidebar_right = '';
		}else{
			$sidebar_type = empty($post_option['sidebar'])? 'none': $post_option['sidebar'];
			$sidebar_left = empty($post_option['sidebar-left'])? '': $post_option['sidebar-left'];
			$sidebar_right = empty($post_option['sidebar-right'])? '': $post_option['sidebar-right'];
		}

		if( $sidebar_type == 'none' ){

			// content from wordpress editor area
			ob_start();
			the_content();
			$content = ob_get_contents();
			ob_end_clean();

			if( ($show_content && trim($content) != "") || post_password_required() ){
				echo '<div class="financity-content-container financity-container">';
				echo '<div class="financity-content-area financity-item-pdlr financity-sidebar-style-none clearfix" >';
				echo gdlr_core_escape_content($content);
				echo '</div>'; // financity-content-area
				echo '</div>'; // financity-content-container
			}

			if( !post_password_required() ){
				do_action('gdlr_core_print_page_builder');
			}

			// comments template
			if( comments_open() || get_comments_number() ){
				echo '<div class="financity-page-comment-container financity-container" >';
				echo '<div class="financity-page-comments financity-item-pdlr" >';
				comments_template();
				echo '</div>';
				echo '</div>';
			}

		}else{

			echo '<div class="financity-content-container financity-container">';
			echo '<div class="' . financity_get_sidebar_wrap_class($sidebar_type) . '" >';

			// sidebar content
			echo '<div class="' . financity_get_sidebar_class(array('sidebar-type'=>$sidebar_type, 'section'=>'center')) . '" >';
			
			// content from wordpress editor area
			ob_start();
			the_content();
			$content = ob_get_contents();
			ob_end_clean();

			if( ($show_content && trim($content) != "") || post_password_required() ){
				echo '<div class="financity-content-area financity-item-pdlr" >' . $content . '</div>'; // financity-content-wrapper
			}

			if( !post_password_required() ){
				do_action('gdlr_core_print_page_builder');
			}

			// comments template
			if( comments_open() || get_comments_number() ){
				echo '<div class="financity-page-comments financity-item-pdlr" >';
				comments_template();
				echo '</div>';
			}

			echo '</div>'; // financity-get-sidebar-class

			// sidebar left
			if( $sidebar_type == 'left' || $sidebar_type == 'both' ){
				echo financity_get_sidebar($sidebar_type, 'left', $sidebar_left);
			}

			// sidebar right
			if( $sidebar_type == 'right' || $sidebar_type == 'both' ){
				echo financity_get_sidebar($sidebar_type, 'right', $sidebar_right);
			}

			echo '</div>'; // financity-get-sidebar-wrap-class
		 	echo '</div>'; // financity-content-container

		}
		
	} // while

	get_footer(); 
?>