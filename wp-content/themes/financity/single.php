<?php
/**
 * The template for displaying all single posts and attachments
 */

get_header(); 

	while( have_posts() ){ the_post();

		// print header title
		if( get_post_type() == 'post' ){
			get_template_part('header/header', 'title-blog');
		}

		$post_option = financity_get_post_option(get_the_ID());
		$post_option['show-content'] = empty($post_option['show-content'])? 'enable': $post_option['show-content']; 

		if( empty($post_option['sidebar']) || $post_option['sidebar'] == 'default' ){
			$sidebar_type = financity_get_option('general', 'blog-sidebar', 'none');
			$sidebar_left = financity_get_option('general', 'blog-sidebar-left');
			$sidebar_right = financity_get_option('general', 'blog-sidebar-right');
		}else{
			$sidebar_type = empty($post_option['sidebar'])? 'none': $post_option['sidebar'];
			$sidebar_left = empty($post_option['sidebar-left'])? '': $post_option['sidebar-left'];
			$sidebar_right = empty($post_option['sidebar-right'])? '': $post_option['sidebar-right'];
		}

		if( $sidebar_type != 'none' || $post_option['show-content'] == 'enable' ){
			echo '<div class="financity-content-container financity-container">';
			echo '<div class="' . financity_get_sidebar_wrap_class($sidebar_type) . '" >';

			// sidebar content
			echo '<div class="' . financity_get_sidebar_class(array('sidebar-type'=>$sidebar_type, 'section'=>'center')) . '" >';
			echo '<div class="financity-content-wrap financity-item-pdlr clearfix" >';

			// single content
			if( $post_option['show-content'] == 'enable' ){
				echo '<div class="financity-content-area" >';
				if( in_array(get_post_format(), array('aside', 'quote', 'link')) ){
					get_template_part('content/content', get_post_format());
				}else{
					get_template_part('content/content', 'single');
				}
				echo '</div>';
			}
		}

		if( !post_password_required() ){
			if( $sidebar_type != 'none' ){
				echo '<div class="financity-page-builder-wrap financity-item-rvpdlr" >';
				do_action('gdlr_core_print_page_builder');
				echo '</div>';

			// sidebar == 'none'
			}else{
				ob_start();
				do_action('gdlr_core_print_page_builder');
				$pb_content = ob_get_contents();
				ob_end_clean();

				if( !empty($pb_content) ){
					if( $post_option['show-content'] == 'enable' ){
						echo '</div>'; // financity-content-area
						echo '</div>'; // financity_get_sidebar_class
						echo '</div>'; // financity_get_sidebar_wrap_class
						echo '</div>'; // financity_content_container
					}
					echo gdlr_core_escape_content($pb_content);
					echo '<div class="financity-bottom-page-builder-container financity-container" >'; // financity-content-area
					echo '<div class="financity-bottom-page-builder-sidebar-wrap financity-sidebar-style-none" >'; // financity_get_sidebar_class
					echo '<div class="financity-bottom-page-builder-sidebar-class" >'; // financity_get_sidebar_wrap_class
					echo '<div class="financity-bottom-page-builder-content financity-item-pdlr" >'; // financity_content_container
				}
			}
		}

		// social share
		if( financity_get_option('general', 'blog-social-share', 'enable') == 'enable' ){
			if( class_exists('gdlr_core_pb_element_social_share') ){
				$share_count = (financity_get_option('general', 'blog-social-share-count', 'enable') == 'enable')? 'counter': 'none';

				echo '<div class="financity-single-social-share financity-item-rvpdlr" >';
				echo gdlr_core_pb_element_social_share::get_content(array(
					'social-head' => $share_count,
					'layout'=>'left-text', 
					'text-align'=>'center',
					'facebook'=>financity_get_option('general', 'blog-social-facebook', 'enable'),
					'linkedin'=>financity_get_option('general', 'blog-social-linkedin', 'enable'),
					'google-plus'=>financity_get_option('general', 'blog-social-google-plus', 'enable'),
					'pinterest'=>financity_get_option('general', 'blog-social-pinterest', 'enable'),
					'stumbleupon'=>financity_get_option('general', 'blog-social-stumbleupon', 'enable'),
					'twitter'=>financity_get_option('general', 'blog-social-twitter', 'enable'),
					'email'=>financity_get_option('general', 'blog-social-email', 'enable'),
					'padding-bottom'=>'0px'
				));
				echo '</div>';
			}
		}

		// author section
		$author_desc = get_the_author_meta('description');
		if( !empty($author_desc) && financity_get_option('general', 'blog-author', 'enable') == 'enable' ){
			echo '<div class="clear"></div>';
			echo '<div class="financity-single-author" >';
			echo '<div class="financity-single-author-wrap" >';
			echo '<div class="financity-single-author-avartar financity-media-image">' . get_avatar(get_the_author_meta('ID'), 90) . '</div>';
			
			echo '<div class="financity-single-author-content-wrap" >';
			echo '<div class="financity-single-author-caption financity-info-font" >' . esc_html__('About the author', 'financity') . '</div>';
			echo '<h4 class="financity-single-author-title">';
			the_author_posts_link();
			echo '</h4>';

			echo '<div class="financity-single-author-description" >' . gdlr_core_escape_content(gdlr_core_text_filter($author_desc)) . '</div>';
			echo '</div>'; // financity-single-author-content-wrap
			echo '</div>'; // financity-single-author-wrap
			echo '</div>'; // financity-single-author
		}

		// prev - next post navigation
		if( financity_get_option('general', 'blog-navigation', 'enable') == 'enable' ){
			$prev_post = get_previous_post_link(
				'<span class="financity-single-nav financity-single-nav-left">%link</span>',
				'<i class="arrow_left" ></i><span class="financity-text" >' . esc_html__( 'Prev', 'financity' ) . '</span>'
			);
			$next_post = get_next_post_link(
				'<span class="financity-single-nav financity-single-nav-right">%link</span>',
				'<span class="financity-text" >' . esc_html__( 'Next', 'financity' ) . '</span><i class="arrow_right" ></i>'
			);
			if( !empty($prev_post) || !empty($next_post) ){
				echo '<div class="financity-single-nav-area clearfix" >' . $prev_post . $next_post . '</div>';
			}
		}

		// comments template
		if( comments_open() || get_comments_number() ){
			comments_template();
		}

		echo '</div>'; // financity-content-area
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

	} // while

	get_footer(); 
?>